<?php
namespace SiteGround_Optimizer\Config;

use SiteGround_Helper\Helper_Service;

/**
 * Config functions and main initialization class.
 */
class Config {
	/**
	 * The config filename.
	 *
	 * @since 7.3.0
	 */
	const SGO_CONFIG = \SiteGround_Optimizer\DIR . '/sg-config.json';

	/**
	 * List of all optimization that we want to keep in the config.
	 *
	 * @access public
	 *
	 * @since 7.3.0
	 * 
	 * @var array $config_options List of all options.
	 */
	public $config_options = array(
		'version'                   => 'siteground_optimizer_current_version',
		'enable_cache'              => 'siteground_optimizer_enable_cache',
		'file_caching'              => 'siteground_optimizer_file_caching',
		'preheat_cache'             => 'siteground_optimizer_preheat_cache',
		'logged_in_cache'           => 'siteground_optimizer_logged_in_cache',
		'enable_memcached'          => 'siteground_optimizer_enable_memcached',
		'autoflush_cache'           => 'siteground_optimizer_autoflush_cache',
		'user_agent_header'         => 'siteground_optimizer_user_agent_header',
		'purge_rest_cache'          => 'siteground_optimizer_purge_rest_cache',
		'ssl_enabled'               => 'siteground_optimizer_ssl_enabled',
		'fix_insecure_content'      => 'siteground_optimizer_fix_insecure_content',
		'optimize_css'              => 'siteground_optimizer_optimize_css',
		'combine_css'               => 'siteground_optimizer_combine_css',
		'preload_combined_css'      => 'siteground_optimizer_preload_combined_css',
		'optimize_javascript'       => 'siteground_optimizer_optimize_javascript',
		'combine_javascript'        => 'siteground_optimizer_combine_javascript',
		'optimize_javascript_async' => 'siteground_optimizer_optimize_javascript_async',
		'optimize_html'             => 'siteground_optimizer_optimize_html',
		'optimize_web_fonts'        => 'siteground_optimizer_optimize_web_fonts',
		'remove_query_strings'      => 'siteground_optimizer_remove_query_strings',
		'disable_emojis'            => 'siteground_optimizer_disable_emojis',
		'lazyload_images'           => 'siteground_optimizer_lazyload_images',
		'webp_support'              => 'siteground_optimizer_webp_support',
		'backup_media'              => 'siteground_optimizer_backup_media',
	);

	/**
	 * Check if the config file needs to be updated.
	 *
	 * @since 7.3.1
	 *
	 * @param string $option Name of the option to add/update.
	 */
	public function update_config_check( $option ) {
		// Check if the option matches the once we are setting in the config.
		if ( ! in_array( $option, $this->config_options, true ) ) {
			return;
		}

		// Update the config file.
		$this->update_config();
	}

	/**
	 * Update the config.
	 *
	 * @since 7.3.0
	 */
	public function update_config() {
		// Check for the helper service method.
		if (
			! method_exists( 'SiteGround_Helper\\Helper_Service', 'update_file' ) ||
			! method_exists( 'SiteGround_Helper\\Helper_Service', 'build_config_content' )
		) {
			return;
		}

		// Update the config file.
		Helper_Service::update_file( self::SGO_CONFIG, Helper_Service::build_config_content( $this->config_options ) );
	}

	/**
	 * Check the current plugin version and update config if needed.
	 *
	 * @since 7.3.1
	 */
	public function check_current_version() {
		// Bail if we have the latest version.
		if ( version_compare( get_option( 'siteground_optimizer_current_version', false ), \SiteGround_Optimizer\VERSION, '==' ) ) {
			return;
		}

		// Update the option in the db.
		update_option( 'siteground_optimizer_current_version', \SiteGround_Optimizer\VERSION );
	}
}
