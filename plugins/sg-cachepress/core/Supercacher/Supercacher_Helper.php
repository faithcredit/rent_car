<?php
namespace SiteGround_Optimizer\Supercacher;

use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Helper\Helper_Service;

/**
 * SG CachePress class that help to split the logic in Supercacher.
 */
class Supercacher_Helper {

	/**
	 * Check if we need to change the cache header of rest requests.
	 *
	 * @since 6.0.6
	 *
	 * @param WP_HTTP_Respons $result Result to send to the client.
	 */
	public function set_rest_cache_headers( $result ) {
		// Get the optimization status.
		$is_cache_enabled = (int) get_option( 'siteground_optimizer_enable_cache', 0 );

		// Prepare the url.
		$url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https://' : 'http://';
		$url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		// Set the cache header to false so it's skipped from caching.
		if (
			0 === $is_cache_enabled ||
			self::is_url_excluded( trailingslashit( $url ) )
		) {
			$result->header( 'X-Cache-Enabled', 'False' );
		}

		return $result;
	}
	/**
	 * Set headers cookie.
	 *
	 * @since 5.0.0
	 */
	public function set_cache_headers( $headers ) {
		if ( defined( 'WP_CLI' ) || php_sapi_name() === 'cli' ) {
			return;
		}

		$is_cache_enabled   = (int) get_option( 'siteground_optimizer_enable_cache', 0 );
		$file_cache_enabled = (int) get_option( 'siteground_optimizer_file_caching', 0 );
		$vary_user_agent    = (int) get_option( 'siteground_optimizer_user_agent_header', 0 );

		$url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https://' : 'http://';
		$url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		// Bail if the cache is not enabled or if the url is excluded from cache.
		if (
			( 0 === $is_cache_enabled && 0 === $file_cache_enabled ) ||
			self::is_url_excluded( $url ) ||
			self::is_post_type_excluded( $url )
		) {
			$headers['X-Cache-Enabled'] = 'False';
			return $headers;
		}

		// Add user agent header.
		if ( 1 === $vary_user_agent ) {
			$headers['Vary'] = 'User-Agent';
		}

		// Set cache header.
		$headers['X-Cache-Enabled'] = 'True';

		return $headers;
	}

	/**
	 * Check if the current url has been excluded.
	 *
	 * @since  5.0.0
	 *
	 * @param string $url The url to test.
	 *
	 * @return boolean True if it was excluded, false otherwise.
	 */
	public static function is_url_excluded( $url ) {
		// Get excluded urls.
		$parts = apply_filters( 'sgo_exclude_urls_from_cache', \get_option( 'siteground_optimizer_excluded_urls', array() ) );

		// Bail if there are no excluded urls.
		if ( empty( $parts ) ) {
			return false;
		}

		// Prepare the url parts for being used as regex.
		$prepared_parts = array_map(
			function( $item ) {
				return str_replace( '\*', '.*', preg_quote( $item, '/' ) );
			}, $parts
		);

		// Build the regular expression.
		$regex = sprintf(
			'/%s(%s)$/i',
			preg_quote( home_url(), '/' ), // Add the home url in the beginning of the regex.
			implode( '|', $prepared_parts ) // Then add each part.
		);

		// Check if the current url matches any of the excluded urls.
		preg_match( $regex, $url, $matches );

		// The url is excluded if matched the regular expression.
		return ! empty( $matches ) ? true : false;
	}

	/**
	 * Check if the curent url's post type has been excluded.
	 *
	 * @since  5.7.0
	 *
	 * @param  string $url The url to check.
	 *
	 * @return boolean True if the url matches an excluded type, false otherwise.
	 */
	public static function is_post_type_excluded( $url ) {
		// Get excluded post_types.
		$post_types = \get_option( 'siteground_optimizer_post_types_exclude', array() );

		// Bail if there are no excluded post types.
		if ( empty( $post_types ) ) {
			return false;
		}

		// We don't want to cache page builder edit page.
		if ( Helper::check_for_builders() ) {
			return false;
		}

		// Get the post/page ID.
		$post_id = url_to_postid( $url );

		// Bail if the page is not found.
		if ( 0 === $post_id ) {
			return false;
		}

		// Bail if we are on the home page.
		if ( get_option( 'page_on_front' ) === $post_id ) {
			return false;
		}

		// Get the post type.
		$post_type = get_post_type( $post_id );

		// Check if the post type is in the exclude list.
		if ( in_array( $post_type, $post_types ) ) {
			return true;
		}

		return false;
	}

}
