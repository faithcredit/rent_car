<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Options\Options;
/**
 * WP-CLI: wp sg dns-prefetch {setting} value.
 *
 * Run the `wp sg dns-prefetch {setting} {option} {value}` command to change the settgins of specific plugin functionality.
 *
 * @since 5.6.1
 * @package Cli
 * @subpackage Cli/Cli_DNS_Prefetch
 */

/**
 * Define the {@link Cli_DNS_Prefetch} class.
 *
 * @since 5.6.1
 */
class Cli_DNS_Prefetch {
	/**
	 * Enable specific setting for SiteGround Optimizer plugin.
	 *
	 * ## OPTIONS
	 *
	 * <setting>
	 * : Setting name.
	 * ---
	 * options:
	 *  - urls
	 *  - add
	 *  - remove
	 * ---
	 * <value>
	 * : The url or urls list.
	 */
	public function __invoke( $args ) {
		// Bail if the DNS Prefetch is disabled..
		$url = ! empty( $args[1] ) ? preg_replace( '~(?:(?:https?:)?(?:\/\/)(?:www\.|(?!www)))?((?:.*?)\.(?:.*))~', '//$1', $args[1] ) : '';

		switch ( $args[0] ) {
			case 'urls':
				return $this->list_prefetch();
			case 'add':
				return $this->add_url( $url );
			case 'remove':
				return $this->remove_url( $url );
		}
	}

	/**
	 * Get all urls for prefetching.
	 *
	 * @since  5.6.1
	 */
	public function list_prefetch() {
		// Get all urls set for DNS-Prefetch.
		$urls = get_option( 'siteground_optimizer_dns_prefetch_urls', false );

		$message = 'The following urls are added for DNS Prefetching:';

		if ( empty( $urls ) ) {
			return \WP_CLI::warning( 'There are no urls added for dns-prefetching' );
		}

		foreach ( $urls as $url ) {
			$message .= "\n" . $url;
		}

		return \WP_CLI::success( $message );
	}

	/**
	 * Add url to DNS-Prefetch list.
	 *
	 * @since 5.6.1
	 *
	 * @param string $url URL to add.
	 */
	public function add_url( $url ) {
		// Remove protocols.
		$urls = get_option( 'siteground_optimizer_dns_prefetch_urls', array() );

		// Check if the url is in the list.
		if ( in_array( $url, $urls ) ) {
			return \WP_CLI::warning( $url . ' is already added for DNS-Prefetching.' );
		}

		// Add the new url to the other urls.
		array_push( $urls, $url );

		// Update the option.
		update_option( 'siteground_optimizer_dns_prefetch_urls', $urls );

		return \WP_CLI::success( $url . ' was added to DNS-Prefetching list.' );
	}

	/**
	 * Remove url from prefetching list.
	 *
	 * @since  5.6.1
	 *
	 * @param string $url URL to remove.
	 */
	public function remove_url( $url ) {
		$urls = get_option( 'siteground_optimizer_dns_prefetch_urls', array() );

		// Check if url is in the list and remove it.
		if ( ! in_array( $url, $urls ) ) {
			return \WP_CLI::warning( $url . ' is not added in DNS-Prefetching list.' );
		}

		// Remove the url from urls.
		$key = array_search( $url, $urls );
		unset( $urls[ $key ] );

		// Update the option.
		update_option( 'siteground_optimizer_dns_prefetch_urls', $urls );

		return \WP_CLI::success( $url . ' was removed from DNS-Prefetching list' );
	}

}
