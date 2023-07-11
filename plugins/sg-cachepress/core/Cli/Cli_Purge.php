<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\File_Cacher\File_Cacher;
use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Helper\Helper_Service;

/**
 * WP-CLI: wp sg purge.
 *
 * Run the `wp sg purge` command to purge all the cache.
 *
 * @since 5.0.0
 * @package Cli
 * @subpackage Cli/Cli_Purge
 */

/**
 * Define the {@link Cli_Purge} class.
 *
 * @since 5.0.0
 */
class Cli_Purge {
	/**
	 * Purge all caches - static, dynamic, memcached and PHP opcache
	 */
	public function __invoke( $args, $assoc_args ) {
		$this->supercacher = new Supercacher();
		$this->file_cacher = new File_Cacher();

		if ( empty( $args[0] ) ) {
			return $this->purge_everything();
		}

		if ( 'memcached' === $args[0] ) {
			return $this->purge_memcached();
		}

		if ( filter_var( $args[0], FILTER_VALIDATE_URL ) ) {
			return $this->purge_url( $args[0] );
		}

		\WP_CLI::error( 'Incorrect URL!' );
	}

	/**
	 * Purges all cache.
	 *
	 * @since 5.0.0
	 */
	public function purge_everything() {
		// Purge the assets dir.
		$this->supercacher->delete_assets();
		// Print successful assets dir cleanup.
		\WP_CLI::success( 'SiteGround Optimizer assets folder purged successfully.' );

		// Check if the File caching is enabled and purge file cache.
		if ( Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
			// Purge the file cache.
			$this->file_cacher->purge_everything();
			// Print message.
			\WP_CLI::success( 'File Cache Successfully Purged.' );
		} else {
			// Set warning message so customer knows that file cache is disabled.
			\WP_CLI::warning( 'Unable to Purge File Cache. Please make sure it is enabled.' );
		}

		// Check if it is a SiteGround user.
		if ( ! Helper_Service::is_siteground() ) {
			\WP_CLI::halt( 0 );
		}

		// Check if dynamic caching is enabled and purge it.
		if ( ! Options::is_enabled( 'siteground_optimizer_enable_cache' ) ) {
			\WP_CLI::warning( 'Unable to Purge Dynamic Cache. Please make sure it is enabled.' );
		}

		$this->supercacher->purge_everything();
		\WP_CLI::success( 'Dynamic Cache Successfully Purged.' );
		return \WP_CLI::halt( 0 );
	}

	/**
	 * Purge memcache.
	 *
	 * @since  5.0.0
	 */
	public function purge_memcached() {
		$response = $this->supercacher->flush_memcache();

		if ( true == $response ) {
			return \WP_CLI::success( 'Memcached Successfully Purged' );
		}

		return \WP_CLI::error( 'Unable to Purge Memcached.' );
	}

	/**
	 * Purge url cache.
	 *
	 * @since 5.0.0
	 * @param string $url - The URL that has to be purged.
	 */
	public function purge_url( $url ) {
		// Check if file caching is enabled and purge it.
		if ( Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
			// Maybe purge file cache.
			true === $this->file_cacher->purge_cache_request( $url )
				? \WP_CLI::success( 'File Cache Successfully Purged.' )
				: \WP_CLI::warning( 'Unable to Purge File Cache. Тhe specific URL may be excluded.' );
		} else {
			// Print message so customer knows that file cache is disabled.
			\WP_CLI::warning( 'Unable to Purge File Cache. Please make sure it is enabled.' );
		}

		// Check if it is a SiteGround user.
		if ( ! Helper_Service::is_siteground() ) {
			\WP_CLI::halt( 0 );
		}

		// Check if dynamic caching is disabled and bail if it is.
		if ( ! Options::is_enabled( 'siteground_optimizer_enable_cache' ) ) {
			\WP_CLI::warning( 'Unable to Purge Dynamic Cache. Please make sure it is enabled.' );
			\WP_CLI::halt( 0 );
		}

		// Maybe purge Dynamic Cache.
		true === $this->supercacher->purge_cache_request( $url )
			? \WP_CLI::success( 'URL Cache Successfully Purged.' )
			: \WP_CLI::warning( 'Unable to Purge URL Cache. The specific URL may be excluded.' );

		return \WP_CLI::halt( 0 );
	}
}
