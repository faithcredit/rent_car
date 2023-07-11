<?php
/** File Cacher class
 *
 * @category Class
 * @package SG_File_Cacher
 * @author SiteGround
 */

namespace SiteGround_Optimizer\File_Cacher;

use SiteGround_Optimizer\Helper\File_Cacher_Trait;
/**
 * SG File Cacher main class
 */
class Cache {
	use File_Cacher_Trait;
	/**
	 * The file configuration.
	 *
	 * @since 7.0.0
	 *
	 * @var $config
	 */
	private $config;

	/**
	 * The constructor
	 *
	 * @since 7.0.0
	 *
	 * @param string $config_path Path to the config file.
	 */
	public function __construct( $config_path ) {
		$this->parse_config( $config_path );
	}

	/**
	 * Parse the config
	 *
	 * @since  7.0.0
	 *
	 * @param  string $path The path to the config.
	 */
	public function parse_config( $path ) {

		if ( ! file_exists( $path ) ) {
			return;
		}

		include $path;

		foreach ( $config as $setting => $entry_value ) {
			$this->$setting = $entry_value;
		}
	}

	/**
	 * Checks if the cache path exists.
	 *
	 * @since  7.0.0
	 *
	 * @return bool True if the path exists, false otherwise.
	 */
	public function cache_exists() {
		return file_exists( $this->get_cache_path() );
	}

	/**
	 * Get the cache path.
	 *
	 * @since  7.0.0
	 *
	 * @return string The cache path.
	 */
	public function get_cache_path( $url = '', $include_user = true ) {
		// Get the current url if the url params is missing.
		$url = empty( $url ) ? self::get_current_url() : $url;

		// Parse the url.
		$parsed_url = parse_url( $url );

		// Prepare the path.
		$path = $parsed_url['host'];

		if (
			true === $include_user &&
			$this->is_logged_in() &&
			$this->logged_in_cache
		) {
			$path .= '-' . $this->get_user_login();
		}

		$path .= '-' . $this->cache_secret_key;

		$path .= $parsed_url['path']; // phpcs:ignore

		return $this->output_dir . $path;
	}

	/**
	 * Check if user is logged in.
	 *
	 * @since  7.0.0
	 *
	 * @return boolean True if the user is logged in, false otherwise.
	 */
	public function is_logged_in() {
		return in_array( $this->logged_in_cookie, array_keys( $_COOKIE ) );
	}

	/**
	 * Get the user login from the cookie.
	 *
	 * @since  7.0.0
	 *
	 * @return string The user login.
	 */
	public function get_user_login() {
		$logged_in_cookie_parsed = explode( '|', $_COOKIE[ $this->logged_in_cookie ] ); // phpcs:ignore

		return $logged_in_cookie_parsed[0];
	}

	/**
	 * Unserilizes the content and checks the cache timestamp
	 *
	 * @since 7.0.0
	 *
	 * @return string|bool     Returns the HTML in a string format, if cache expired or invalid - returns false
	 */
	public function get_cache() {
		$should_send_miss = true;

		if (
			( @file_exists( '/etc/yum.repos.d/baseos.repo' ) && @file_exists( '/Z' ) ) &&
			empty( $_COOKIE[ $this->logged_in_cookie ] )
		) {
			$should_send_miss = false;
		}
		// Bail if the page is excluded from the cache.
		if ( ! $this->is_cacheable() ) {
			header( 'SG-F-Cache: BYPASS' );
			return;
		}

		$cache_file = $this->get_cache_path() . $this->get_filename( $this->ignored_query_params );

		if ( ! file_exists( $cache_file ) ) {
			if ( $should_send_miss ) {
				header( 'SG-F-Cache: MISS' );
			}
			return;
		}

		$content = file_get_contents( $cache_file );

		// Check for non-existing data or non-existing file.
		if ( empty( $content ) ) {
			if ( $should_send_miss ) {
				header( 'SG-F-Cache: MISS' );
			}
			return false;
		}

		// Bail if the cache is stale.
		if ( filemtime( $cache_file ) < ( time() - WEEK_IN_SECONDS ) ) {
			if ( $should_send_miss ) {
				header( 'SG-F-Cache: MISS' );
			}
			return false;
		}

		header( 'SG-F-Cache: HIT' );

		echo $content; // phpcs:ignore
		exit;
	}
}
