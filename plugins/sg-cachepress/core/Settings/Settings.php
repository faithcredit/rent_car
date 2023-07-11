<?php
namespace SiteGround_Optimizer\Settings;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\Ssl\Ssl;
use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Helper\Helper_Service;

class Settings {
	/**
	 * The array containing all settings that can be imported/exported.
	 *
	 * @var array
	 */
	public $options = array(
		'enable_cache',
		'autoflush_cache',
		'user_agent_header',
		'enable_memcached',
		'ssl_enabled',
		'fix_insecure_content',
		'enable_gzip_compression',
		'enable_browser_caching',
		'optimize_html',
		'optimize_javascript',
		'optimize_javascript_async',
		'optimize_css',
		'combine_css',
		'combine_javascript',
		'optimize_web_fonts',
		'remove_query_strings',
		'disable_emojis',
		'resize_images',
		'lazyload_images',
		'webp_support',
		'supercacher_permissions',
		'frontend_permissions',
		'images_permissions',
		'environment_permissions',
		'heartbeat_control',
		'database_optimization',
		'dns_prefetch',
		'async_javascript_exclude',
		'excluded_lazy_load_classes',
		'post_types_exclude',
		'dns_prefetch_urls',
		'combine_javascript_exclude',
		'minify_javascript_exclude',
		'excluded_lazy_load_media_types',
	);

	/**
	 * Additional actions that need to be done when importing a file or hash code.
	 *
	 * @var array
	 */
	public $complex_options = array(
		'ssl_enabled' => array(
			'class'              => 'SiteGround_Optimizer\Ssl\Ssl',
			'enable'             => 'enable',
			'disable'            => 'disable',
		),
		'enable_memcached' => array(
			'class'              => 'SiteGround_Optimizer\Memcache\Memcache',
			'enable'             => 'create_memcached_dropin',
			'disable'            => 'remove_memcached_dropin',
		),
		'enable_gzip_compression' => array(
			'class'              => 'SiteGround_Optimizer\Htaccess\Htaccess',
			'enable'             => 'enable',
			'disable'            => 'disable',
			'disabled_on_avalon' => 1,
			'argument'           => 'gzip',
		),
		'enable_browser_caching' => array(
			'class'              => 'SiteGround_Optimizer\Htaccess\Htaccess',
			'enable'             => 'enable',
			'disable'            => 'disable',
			'disabled_on_avalon' => 1,
			'argument'           => 'browser-caching',
		),
	);

	/**
	 * Create export file or hash.
	 *
	 * @since  5.7.13
	 *
	 * @return string/filesource  String containing the hashed json or a json file.
	 */
	public function export() {
		// Init the Options Service.
		$this->options_service = new Options();

		// Prepare the settings array.
		$settings = array();
		// Get the options from database.
		$options = $this->options_service->fetch_options();

		foreach ( $options as $option => $value ) {
			if ( ! in_array( $option, $this->options ) ) {
				continue;
			}

			$settings[ $option ] = $value;
		}

		// Return the string if we are not writing it to a file.
		return base64_encode( json_encode( $settings ) );
	}

	/**
	 * Prepare the json for import.
	 *
	 * @since  5.7.13
	 *
	 * @param  string $source The hashed export.
	 *
	 * @return array The options updated, their values and errors
	 */
	public function import( $source ) {
		// Get the content of the hash, decode the import and map the available options for import.
		$options = json_decode( base64_decode( $source ), true );

		// Check if the provided settings are ok.
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return false;
		}

		$failed_options = array();

		foreach ( $options as $option => $value ) {
			// Bail if the option doesn't exists in the predefined options.
			if ( ! in_array( $option, $this->options ) ) {
				continue;
			}

			// Update the option if we don't need to do additional optimizations.
			if ( ! array_key_exists( $option, $this->complex_options ) ) {
				// Update the option.
				update_option( 'siteground_optimizer_' . $option, $value );
				continue;
			}

			// Bail if the option is active by default on avalon server.
			if (
				! empty( $this->complex_options[ $option ]['disabled_on_avalon'] ) &&
				Helper_Service::is_siteground()
			) {
				continue;
			}

			// Impor the complex option.
			$result = $this->import_complex_option( $option, $value );

			// Add the option to the failed option if the optimization is not successful.
			if ( false === $result ) {
				$failed_options[] = $option;
			}
		}

		// Flush the cache after the import.
		Supercacher::purge_cache();

		// Return the failed options.
		return $failed_options;
	}

	/**
	 * Import complex option
	 *
	 * @since  5.7.13
	 *
	 * @param  string $option The option name.
	 * @param  mixed  $value  The option value.
	 *
	 * @return boolean        True on success, false otherwise.
	 */
	public function import_complex_option( $option, $value ) {
		// Get the complex option details.
		$optimization_details = $this->complex_options[ $option ];
		// Init the class responsble for option functionality.
		$class = new $optimization_details['class']();

		// Get the method to call.
		$method = ( 0 === $value ) ? $optimization_details['disable'] : $optimization_details['enable'];

		// Do the optimization and the the result.
		$result = ! empty( $optimization_details['argument'] ) ? $class->$method( $optimization_details['argument'] ) : $class->$method();

		// Update the option if the optimization is successful.
		if ( true === $result ) {
			update_option( 'siteground_optimizer_' . $option, $value );
		}

		// return the result.
		return $result;
	}
}