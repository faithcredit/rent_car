<?php
namespace SiteGround_Optimizer\Helper;

/**
 * Trait used for factory pattern in the plugin.
 */
trait File_Cacher_Trait {

	/**
	 * Checks if the current request is cacheable
	 *
	 * @since 7.0.0
	 *
	 * @return bool Returns true if the request is cacheable, false if not.
	 */
	public function is_cacheable() {
		if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) { //phpcs:ignore
			return;
		}

		// Bail if no cache headers are presented.
		if ( $this->has_nocache_headers() ) {
			return false;
		}

		// Check if this is an ajax request.
		if ( $this->doing_ajax() ) {
			return false;
		}

		// Check if this is an cron request.
		if ( $this->doing_cron() ) {
			return false;
		}

		if ( $this->is_content_type_not_supported() ) {
			return false;
		}

		if ( $this->logged_in_cache ) {
			$this->bypass_cookies = array_diff( $this->bypass_cookies, array( 'wordpress_logged_in_' ) );
		}

		if ( $this->has_bypass_cookies() ) {
			return false;
		}

		if ( $this->has_skip_cache_query_params() ) {
			return false;
		}

		return true;
	}

	/**
	 * Check for query args that shoundn't be cached.
	 *
	 * @since  7.0.0
	 *
	 * @return boolean True/False.
	 */
	public function has_skip_cache_query_params() {
		// Iterate through the query array and check for skip cache params.
		foreach ( $_GET as $param => $value ) {
			if ( in_array( $param, $this->bypass_query_params, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the request contains bypass cookies.
	 *
	 * @since  7.0.0
	 *
	 * @return boolean True/False
	 */
	public function has_bypass_cookies() {
		if ( empty( $_COOKIE ) ) {
			return false;
		}

		// Check if any of the users' cookies are one of the bypass ones.
		foreach ( $this->bypass_cookies as $bypass_cookie ) {
			foreach ( array_keys( $_COOKIE ) as $cookie ) {
				// Bail if a bypass cookie was found.
				if ( substr( $cookie, 0, strlen( $bypass_cookie ) ) === $bypass_cookie ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if the content is supported.
	 *
	 * @since  7.0.0
	 *
	 * @return boolean Tru/False
	 */
	public function is_content_type_not_supported() {
		if (
			empty( $_SERVER['HTTP_ACCEPT'] ) ||
			false === strpos( $_SERVER['HTTP_ACCEPT'], 'text/html' ) // phpcs:ignore
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get the current url.
	 *
	 * @since  7.0.0
	 *
	 * @return string The current url.
	 */
	public static function get_current_url() {
		$protocol = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http'; // phpcs:ignore

		// Build the current url.
		return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; //phpcs:ignore
	}

	/**
	 * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
	 *
	 * @since  5.9.0
	 *
	 * @return boolean
	 */
	public static function is_mobile() {
		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$is_mobile = false;
		} elseif ( @strpos( $_SERVER['HTTP_USER_AGENT'], 'Mobile' ) !== false // many mobile devices (all iPhone, iPad, etc.)
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Android' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Silk/' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Kindle' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'BlackBerry' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mini' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mobi' ) !== false ) {
				$is_mobile = true;
		} else {
			$is_mobile = false;
		}

		return $is_mobile;
	}


	/**
	 * Gets the GET parameters from the request, filters out the whitelisted ones ( that won't be taken into account ), takes their values and hashes the string with the serialized data
	 *
	 * @since 7.0.0
	 *
	 * @return string The hashed serialized query string, empty if none
	 */
	public function get_filename() {
		$base_filename  = self::is_mobile() ? 'index-mobile' : 'index';

		// Retrieve the GET parameters.
		$get_params = $_GET; //phpcs:ignore

		// Iterate through the query array and unset the unneeded params.
		foreach ( $get_params as $param => $value ) {
			if ( ! in_array( $param, $this->ignored_query_params, true ) ) {
				continue;
			}

			unset( $get_params[ $param ] );
		}

		// Check if any query params are left.
		if ( empty( $get_params ) ) {
			return $base_filename . '.html';
		}

		// Stringify the array and return the value.
		return $base_filename . '-' . md5( implode( '', $get_params ) ) . '.html';
	}

	/**
	 * Custom implementations of doing_ajax function
	 *
	 * @since  7.0.0
	 *
	 * @return bool True/false
	 */
	public function doing_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * Custom implementations of doing_cron function
	 *
	 * @since  7.0.0
	 *
	 * @return bool True/false
	 */
	public function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}

	/**
	 * Check for nocache headers.
	 *
	 * @since  7.0.0
	 *
	 * @return boolean True if nocache headers exist, false otherwise.
	 */
	public function has_nocache_headers() {
		// Bail if the method is not supported.
		if ( ! function_exists( 'apache_response_headers' ) ) {
			return false;
		}

		// Define the ignore cache headers.
		$ignore_headers = apply_filters(
			'sgo_file_cache_ignore_headers',
			array(
				'cache-control' => 'no-cache',
			)
		);

		foreach ( \apache_response_headers() as $header => $value ) {
			$lowercase_header = strtolower( $header );

			// Do not cache if any of the ignore headers exists and matche the ignore header value.
			if (
				array_key_exists( $lowercase_header, $ignore_headers ) &&
				is_int( stripos( trim( $value ), $ignore_headers[ $lowercase_header ] ) )
			) {
				return true;
			}
		}

		// We can cache the page.
		return false;
	}

}
