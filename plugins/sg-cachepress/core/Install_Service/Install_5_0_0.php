<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Install_Service\Install;
use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Ssl\Ssl;

class Install_5_0_0 extends Install {
	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $htaccess;
	public $ssl;

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.0.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.0.0';

	public function __construct() {
		$this->htaccess = new Htaccess();
		$this->ssl      = new Ssl();
	}

	/**
	 * Run the install procedure.
	 *
	 * @since 5.0.0
	 */
	public function install() {
		// Try to update multisite options.
		$this->maybe_update_multisite_options();

		// Bail if this is the network admin.
		if ( is_network_admin() ) {
			return;
		}

		// Get the options.
		$options = $this->get_options();

		// Prepare a map between old and the new option names.
		$map = array(
			'enable_cache'              => 'siteground_optimizer_enable_cache',
			'autoflush_cache'           => 'siteground_optimizer_autoflush_cache',
			'enable_memcached'          => 'siteground_optimizer_enable_memcached',
			'show_notice'               => 'siteground_optimizer_show_notice',
			'is_nginx'                  => 'siteground_optimizer_is_nginx',
			'checked_nginx'             => 'siteground_optimizer_checked_nginx',
			'first_run'                 => 'siteground_optimizer_first_run',
			'last_fail'                 => 'siteground_optimizer_last_fail',
			'sg_cachepress_ssl_enabled' => 'siteground_optimizer_ssl_enabled',
			'fix_insecure_content'      => 'siteground_optimizer_fix_insecure_content',
			'optimize_html'             => 'siteground_optimizer_optimize_html',
			'optimize_javascript'       => 'siteground_optimizer_optimize_javascript',
			'optimize_javascript_async' => 'siteground_optimizer_optimize_javascript_async',
			'optimize_css'              => 'siteground_optimizer_optimize_css',
			'combine_css'               => 'siteground_optimizer_combine_css',
			'remove_query_strings'      => 'siteground_optimizer_remove_query_strings',
			'disable_emojis'            => 'siteground_optimizer_disable_emojis',
			'lazyload_images'           => 'siteground_optimizer_lazyload_images',
			'lazyload_gravatars'        => 'siteground_optimizer_lazyload_gravatars',
			'lazyload_thumbnails'       => 'siteground_optimizer_lazyload_thumbnails',
			'lazyload_responsive'       => 'siteground_optimizer_lazyload_responsive',
			'lazyload_textwidgets'      => 'siteground_optimizer_lazyload_textwidgets',
		);

		// Add the new options.
		foreach ( $options as $name => $value ) {
			// Log and error and proceed with other options if the current option is not mapped.
			if ( ! array_key_exists( $name, $map ) ) {
				error_log( "Option $name doens't exists in options mapping" );
				continue;
			}

			add_option( $map[ $name ], $value );
		}

		if ( ! empty( $options['blacklist'] ) ) {
			$this->update_exclude_list_option( $options['blacklist'] );
		}

		// Enable ssl if the network option is enabled.
		$this->maybe_enable_ssl();

		// Set flag to show the conflicting plugin notices.
		add_option( 'disable_conflicting_modules', 1 );
	}

	/**
	 * Enable ssl if the global option is set.
	 *
	 * @since  5.0.0
	 */
	public function maybe_enable_ssl() {
		// Bail if the default ssl is not enabled.
		if ( 1 !== (int) get_site_option( 'siteground_optimizer_default_ssl_enabled', 0 ) ) {
			return;
		}

		$this->ssl->enable();
	}

	/**
	 * Get the plugin options from database.
	 *
	 * @since  5.0.0
	 *
	 * @return array Array containing the plugin options.
	 */
	private function get_options() {
		// Get the options.
		$options = get_option( 'sg_cachepress' );

		// If the old options have been converted, return them.
		if ( empty( $options ) ) {
			// The plugin version is old, so return the old options.
			$options = array(
				'enable_cache'     => get_option( 'SGCP_Use_SG_Cache', 1 ),
				'autoflush_cache'  => get_option( 'SGCP_Autoflush', 1 ),
				'enable_memcached' => get_option( 'SGCP_Memcached', 0 ),
				'show_notice'      => get_option( 'SGCP_ShowNotice', 0 ),
				'is_nginx'         => get_option( 'SGCP_IsNginx', 0 ),
				'checked_nginx'    => get_option( 'SGCP_CheckedNginx', 0 ),
				'first_run'        => get_option( 'SGCP_FristRun', 0 ),
				'last_fail'        => get_option( 'SGCP_LastFail', 0 ),
			);

			// Backward compatibility.
			add_option( 'sg_cachepress', $options );

			$this->htaccess->enable( 'gzip' );
			$this->htaccess->enable( 'browser-caching' );
		}

		// Add the ssl option to other options.
		$options['sg_cachepress_ssl_enabled'] = get_option( 'sg_cachepress_ssl_enabled', 0 );

		$options = $this->apply_new_options( $options );

		// Apply multisite default options.
		if ( is_multisite() ) {
			$options = $this->apply_multisite_options( $options );
		}

		return $options;
	}

	public function apply_new_options( $options ) {
		return array_merge(
			$options,
			array(
				'optimize_html'             => get_option( 'siteground_optimizer_optimize_html', 0 ),
				'optimize_javascript'       => get_option( 'siteground_optimizer_optimize_javascript', 0 ),
				'optimize_javascript_async' => get_option( 'siteground_optimizer_optimize_javascript_async', 0 ),
				'optimize_css'              => get_option( 'siteground_optimizer_optimize_css', 0 ),
				'combine_css'               => get_option( 'siteground_optimizer_combine_css', 0 ),
				'remove_query_strings'      => get_option( 'siteground_optimizer_remove_query_strings', 0 ),
				'disable_emojis'            => get_option( 'siteground_optimizer_disable_emojis', 0 ),
			)
		);
	}

	/**
	 * Apply multisite default options.
	 *
	 * @since  5.0.0
	 *
	 * @param  array $options Old options.
	 *
	 * @return array          Modified options.
	 */
	private function apply_multisite_options( $options ) {
		// Map between default site options and multisite configuration.
		$mu_options = array(
			'enable_cache',
			'autoflush_cache',
			'optimize_html',
			'optimize_javascript',
			'optimize_javascript_async',
			'optimize_css',
			'combine_css',
			'remove_query_strings',
			'disable_emojis',
			'optimize_images',
			'lazyload_images',
			'lazyload_gravatars',
			'lazyload_thumbnails',
			'lazyload_responsive',
			'lazyload_textwidgets',
			'fix_insecure_content',
		);

		$multisite_options = array();

		foreach ( $mu_options as $option ) {
			$multisite_options[ $option ] = get_site_option( 'siteground_optimizer_default_' . $option, 0 );
		}

		// Merge multisite and default options.
		return array_merge( $options, $multisite_options );
	}

	/**
	 * Update the exclude list option.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $exclude_list The exclude list.
	 */
	private function update_exclude_list_option( $exclude_list ) {
		$exclude_list_array = explode( "\n", $exclude_list );

		// Prepare the url parts for being used as regex.
		$new_exclude_list = array_map(
			function( $item ) {
				if ( substr( $item, 0, 1 ) !== '/' ) {
					$item = '/' . $item;
				}

				return preg_replace( '~/$~', '/*', $item );
			}, $exclude_list_array
		);

		update_option( 'siteground_optimizer_excluded_urls', $new_exclude_list );
	}

	/**
	 * Update multisite options.
	 *
	 * @since  5.0.0
	 */
	private function maybe_update_multisite_options() {
		// Options mapping between old and new option names.
		$network_options = array(
			'sg-cachepress-default-enable-cache'    => 'siteground_optimizer_default_enable_cache',
			'sg-cachepress-default-autoflush-cache' => 'siteground_optimizer_default_autoflush_cache',
		);

		// Loop through all options and migrate the values between the old and new options.
		foreach ( $network_options as $old_option => $new_option ) {
			// Check if the option already has been set.
			$option_value = get_site_option( $new_option );

			// If not, update the new option using the old option value.
			if ( false === $option_value ) {
				add_site_option( $new_option, get_site_option( $old_option, 0 ) );
			}
		}

		// Check if gzip compression is enable and update the network option.
		if ( 1 === (int) $this->htaccess->is_enabled( 'gzip' ) ) {
			add_site_option( 'siteground_optimizer_enable_gzip_compression', 1 );
		}

		// Check if browser caching is enable and update the network option.
		if ( 1 === (int) $this->htaccess->is_enabled( 'browser-caching' ) ) {
			add_site_option( 'siteground_optimizer_enable_browser_caching', 1 );
		}

		// Update the permissions option to show the supercacher settings for subsites.
		add_site_option( 'siteground_optimizer_supercacher_permissions', 1 );
	}

}