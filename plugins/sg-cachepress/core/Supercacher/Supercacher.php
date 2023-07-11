<?php
namespace SiteGround_Optimizer\Supercacher;

use SiteGround_Optimizer\File_Cacher\File_Cacher;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Helper\Helper_Service;
use SiteGround_Optimizer\CDN\Cdn;
use SiteGround_Optimizer\Site_Tools_Client\Site_Tools_Client;

/**
 * SG CachePress main plugin class
 */
class Supercacher {
	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $supercacher_comments;
	public $supercacher_posts;
	public $supercacher_terms;

	/**
	 * The children classes and their hooks and options.
	 *
	 * @var array
	 */
	public $children = array(
		'supercacher_posts'    => array(
			array(
				'option'   => 'purge_all_post_cache',
				'hook'     => 'save_post',
				'priority' => 1,
			),
			array(
				'option'   => 'purge_all_post_cache',
				'hook'     => 'pll_save_post',
				'priority' => 1,
			),
			array(
				'option'   => 'purge_all_post_cache',
				'hook'     => 'wp_trash_post',
				'priority' => 1,
			),
			array(
				'option'   => 'purge_all_post_cache',
				'hook'     => 'publish_post',
				'priority' => 1,
			),
		),
		'supercacher_terms'    => array(
			array(
				'option'   => 'purge_term_and_index_cache',
				'hook'     => 'edit_term',
				'priority' => 1,
			),
		),
		'supercacher_comments' => array(
			array(
				'option'   => 'purge_comment_post',
				'hook'     => 'edit_comment',
				'priority' => 1,
			),
			array(
				'option'   => 'purge_comment_post',
				'hook'     => 'delete_comment',
				'priority' => 1,
			),
			array(
				'option'   => 'purge_comment_post',
				'hook'     => 'wp_set_comment_status',
				'priority' => 1,
			),
			array(
				'option'   => 'purge_comment_post',
				'hook'     => 'wp_insert_comment',
				'priority' => 1,
			),
		),
	);

	/**
	 * Hooks which will be used to purge the queued URLs cache.
	 *
	 * @var array
	 *
	 * @since 5.9.0
	 */
	public $purge_hooks = array(
		'purge_queue'      => array(
			'edit_comment',
			'delete_comment',
			'wp_set_comment_status',
			'wp_insert_comment',
			'save_post',
			'pll_save_post',
			'wp_trash_post',
			'edit_term',
		),
		'purge_everything' => array(
			'automatic_updates_complete',
			'_core_updated_successfully',
			'update_option_permalink_structure',
			'update_option_tag_base',
			'update_option_category_base',
			'wp_update_nav_menu',
			'update_option_siteground_optimizer_enable_cache',
			'update_option_siteground_optimizer_autoflush_cache',
			'update_option_siteground_optimizer_enable_memcached',
			'deactivate_plugin',
			'activate_plugin',
			'upgrader_process_complete',
			'revslider_slide_updateSlideFromData_post',
			'switch_theme',
			'customize_save',
			'edd_login_form_logged_in',
			'create_term',
			'delete_term',
		),
	);

	/**
	 * The singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @var \Supercacher The singleton instance.
	 */
	private static $instance;

	public function __construct() {
		$this->supercacher_comments = new Supercacher_Comments();
		$this->supercacher_posts    = new Supercacher_Posts();
		$this->supercacher_terms    = new Supercacher_Terms();
	}
	/**
	 * Get the singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @return \Supercacher The singleton instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Purge the dynamic cache.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function purge_cache() {
		return self::get_instance()->purge_everything();
	}

	/**
	 * Purge everything from cache.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function purge_everything() {
		return $this->purge_cache_request( get_home_url( null, '/' ) );
	}

	/**
	 * Purge index.php from cache.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function purge_index_cache() {
		return $this->purge_cache_request( get_home_url( null, '/' ), false );
	}

	/**
	 * Purge rest api cache.
	 *
	 * @since  5.7.18
	 *
	 * @return bool True on success, false on failure.
	 */
	public function purge_rest_cache() {
		if ( ! Options::is_enabled( 'siteground_optimizer_purge_rest_cache' ) ) {
			return;
		}

		return $this->purge_cache_request( get_rest_url() );
	}

	/**
	 * Perform a delete request.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $url                 The url to purge.
	 * @param  bool   $include_child_paths Whether to purge child paths too.
	 *
	 * @return bool True if the cache is deleted, false otherwise.
	 */
	public static function purge_cache_request( $url, $include_child_paths = true ) {
		// Check if the user has file cache enabled, clear the file cache and bail if the user is not hosted on SiteGround.
		if ( Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
			File_Cacher::get_instance()->purge_cache_request( $url, $include_child_paths );
		}

		if ( ! Helper_Service::is_siteground() ) {
			return true;
		}

		// Bail if the url is empty.
		if ( empty( $url ) ) {
			return;
		}

		$hostname   = str_replace( 'www.', '', wp_parse_url( home_url(), PHP_URL_HOST ) );
		$parsed_url = wp_parse_url( $url );
		$main_path  = wp_parse_url( $url, PHP_URL_PATH );

		if ( empty( $main_path ) ) {
			$main_path = '/';
		}

		// Bail if the url has get params, but it matches the home url.
		// We don't want to purge the entire cache.
		if (
			isset( $parsed_url['query'] ) &&
			wp_parse_url( home_url( '/' ), PHP_URL_PATH ) === $main_path
		) {
			return;
		}

		// Change the regex if we have to delete the child paths.
		if ( true === $include_child_paths ) {
			$main_path .= '(.*)';
		}

		// Flush the cache for the URL.
		return self::flush_dynamic_cache( $hostname, $main_path, $url );
	}

	/**
	 * Call site tools client to purge the cache.
	 *
	 * @param      string $hostname  The domain which cache will be flushed.
	 * @param      string $main_path The path to be flushed.
	 * @param      string $main_path The URL to be flushed.
	 *
	 * @return     bool              True if the cache is purged successfully, false otherwise.
	 */
	public static function flush_dynamic_cache( $hostname, $main_path, $url ) {
		// Build the request params.
		$args = array(
			'api'      => 'domain-all',
			'cmd'      => 'update',
			'settings' => array( 'json' => 1 ),
			'params'   => array(
				'flush_cache' => '1',
				'id'          => $hostname,
				'path'        => $main_path,
			),
		);

		$site_tools_result = Site_Tools_Client::call_site_tools_client( $args, true );

		do_action( 'siteground_optimizer_flush_cache', $url );

		if ( false === $site_tools_result ) {
			return false;
		}

		if ( isset( $site_tools_result['err_code'] ) ) {
			error_log( 'There was an issue purging the cache for this URL: ' . $url . '. Error code: ' . $site_tools_result['err_code'] . '. Message: ' . $site_tools_result['message'] . '.' );
			return false;
		}

		return true;
	}

	/**
	 * Flush Memcache or Memcached.
	 *
	 * @since 5.0.0
	 */
	public static function flush_memcache() {
		return wp_cache_flush();
	}

	/**
	 * Purge the cache when the options are saved.
	 *
	 * @since  5.0.0
	 */
	public function purge_on_options_save() {

		if (
			isset( $_POST['action'] ) && // WPCS: CSRF ok.
			isset( $_POST['option_page'] ) && // WPCS: CSRF ok.
			'update' === $_POST['action'] // WPCS: CSRF ok.
		) {
			$this->purge_everything();
		}
	}

	/**
	 * Purge the cache for other events.
	 *
	 * @since  5.0.0
	 */
	public function purge_on_other_events() {
		if (
			isset( $_POST['save-header-options'] ) || // WPCS: CSRF ok.
			isset( $_POST['removeheader'] ) || // WPCS: CSRF ok.
			isset( $_POST['skip-cropping'] ) || // WPCS: CSRF ok.
			isset( $_POST['remove-background'] ) || // WPCS: CSRF ok.
			isset( $_POST['save-background-options'] ) || // WPCS: CSRF ok.
			( isset( $_POST['submit'] ) && 'Crop and Publish' == $_POST['submit'] ) || // WPCS: CSRF ok.
			( isset( $_POST['submit'] ) && 'Upload' == $_POST['submit'] ) // WPCS: CSRF ok.
		) {
			$this->purge_everything();
		}
	}

	/**
	 * Check if cache header is enabled for url.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $url           The url to test.
	 * @param  bool   $maybe_dynamic Wheather to make additional request to check the cache again.
	 *
	 * @return bool                  True if the cache is enabled, false otherwise.
	 */
	public static function test_cache( $url, $maybe_dynamic = true ) {
		// Bail if the url is empty.
		if ( empty( $url ) ) {
			return;
		}

		// Add slash at the end of the url if it does not have get parameters.
		if ( ! strpos( $url, '?' ) ) {
			$url = trailingslashit( $url );
		}

		// Bail if the url is excluded.
		if ( SuperCacher_Helper::is_url_excluded( $url ) ) {
			return false;
		}

		// Define the arguments. No arguments necessary by default.
		$args = array();

		// Check if SiteGround CDN is active.
		if (
			Cdn::is_siteground_cdn() &&
			! Cdn::is_siteground_cdn_premium() ||
			Cdn::is_siteground_cdn_premium_pending()
		) {
			// Get the proper domain.
			$matches = Site_Tools_Client::get_site_tools_matching_domain();

			// Set the Host header so we can test the cache status locally from the server.
			$args['headers'] = array(
				'Host' => $matches[1],
			);

			// Store the original url.
			$initial_url = $url;

			// Set the url to be the one of the host.
			$url = 'https://' . \gethostname() . str_replace( $matches[0], '', $url );
		}

		// Make the request.
		$response = wp_remote_get( $url, $args );

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// Get response headers.
		$headers = wp_remote_retrieve_headers( $response );

		if ( empty( $headers ) ) {
			return false;
		}

		// Check if the url has a cache header.
		if (
			isset( $headers['x-proxy-cache'] ) &&
			'HIT' === strtoupper( $headers['x-proxy-cache'] )
		) {
			return true;
		}

		if ( $maybe_dynamic ) {
			// Restore the url in cases of CDN check's second attempt to get the proper caching status.
			$url = isset( $initial_url ) ? $initial_url : $url;
			return self::test_cache( $url, false );
		}

		// The header doesn't exists.
		return false;
	}

	/**
	 * Delete plugin assets
	 *
	 * @since  5.1.0
	 *
	 * @param bool|string $dir Directory to clean up.
	 */
	public static function delete_assets( $dir = false ) {
		if ( false === $dir ) {
			$dir = Front_End_Optimization::get_instance()->assets_dir;
		}

		// Scan the assets dir.
		$all_files = scandir( $dir );

		// Get only files and directories.
		$files = array_diff( $all_files, array( '.', '..' ) );

		foreach ( $files as $filename ) {
			// Build the filepath.
			$maybe_file = trailingslashit( $dir ) . $filename;

			// Bail if the file is not a file.
			if ( ! is_file( $maybe_file ) ) {
				self::delete_assets( $maybe_file );
				continue;
			}

			// Delete the file.
			unlink( $maybe_file ); // phpcs:ignore
		}
	}

	/**
	 * Purge the cache for all elements in the queue.
	 *
	 * @since 5.8.3
	 */
	public function purge_queue() {
		// Get the current purge queue.
		$queue = get_option( 'siteground_optimizer_smart_cache_purge_queue', array() );

		// Bail if the queue is empty.
		if ( empty( $queue ) ) {
			return;
		}

		if ( 10 > count( $queue ) ) {

			if ( ! Options::is_enabled( 'siteground_optimizer_purge_rest_cache' ) ) {
				$key = array_search( get_rest_url(), $queue );

				if ( is_int( $key ) ) {
					unset( $queue[ $key ] );
				}
			}

			// Purge the cache for all URLs in the queue.
			foreach ( $queue as $url ) {
				$this->purge_cache_request(
					$url,
					get_home_url( null, '/' ) === $url ? false : true
				);
			}
		} else {
			$this->purge_everything();
		}

		// Empty the purge queue after cache is cleared.
		update_option( 'siteground_optimizer_smart_cache_purge_queue', array() );
	}
}
