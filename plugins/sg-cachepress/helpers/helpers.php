<?php

use SiteGround_Optimizer\Options\Options;
/**
 * Public function to purge cache.
 *
 * @since  5.0.0
 *
 * @param  string|bool $url The URL.
 *
 * @return bool True if the cache is deleted, false otherwise.
 */
function sg_cachepress_purge_cache( $url = false ) {
	// Bail if Dynamic cache is disabled.
	if (
		! Options::is_enabled( 'siteground_optimizer_enable_cache' ) &&
		! Options::is_enabled( 'siteground_optimizer_file_caching' )
	) {
		return;
	}

	global $siteground_optimizer_loader;

	$url = empty( $url ) ? get_home_url( '/' ) : $url;

	do_action( 'siteground_optimizer_flush_cache', $url );

	$response = $siteground_optimizer_loader->supercacher->purge_cache_request( $url );

	if ( Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
		if ( $url === get_home_url( '/' ) ) {
			$response = $siteground_optimizer_loader->file_cacher->purge_everything();
		} else {
			$response = $siteground_optimizer_loader->file_cacher->purge_cache_request( $url, true );
		}
	}

	return $response;
}

/**
 * Public function to purge the entire cache.
 *
 * @since  5.7.14
 */
function sg_cachepress_purge_everything() {
	global $siteground_optimizer_loader;

	// Purge Dynamic cache if enabled.
	if ( Options::is_enabled( 'siteground_optimizer_enable_cache' ) ) {
		$siteground_optimizer_loader->supercacher->purge_cache();
	}

	// Purge Memcached if enabled.
	if ( Options::is_enabled( 'siteground_optimizer_enable_memcached' ) ) {
		$siteground_optimizer_loader->supercacher->flush_memcache();
	}

	if ( Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
		$siteground_optimizer_loader->file_cacher->purge_everything();
	}

	$siteground_optimizer_loader->supercacher->delete_assets();
}
