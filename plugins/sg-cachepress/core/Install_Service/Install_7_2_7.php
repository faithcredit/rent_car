<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Options\Options;

class Install_7_2_7 extends Install {
	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 7.2.7
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '7.2.7';

	/**
	 * Run the install procedure.
	 *
	 * @since 7.2.7
	 */
	public function install() {
		$this->fb_cache_cleanup();
		$this->update_memcached_dropin();
	}

	/**
	 * Fix FileBased cache cleanup interval.
	 */
	public function fb_cache_cleanup() {
		if ( ! Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
			return;
		}

		// Get the cleanup interval set.
		$interval = intval( get_option( 'siteground_optimizer_file_caching_interval_cleanup', 604800 ) );

		// Bail if cleanup interval is disabled.
		if ( 0 === $interval ) {
			return;
		}

		// Schedule the cleanup.
		wp_schedule_single_event( time() + $interval, 'siteground_optimizer_clear_cache_dir' );
	}

	/**
	 * Update Memcached dropin
	 */
	public function update_memcached_dropin() {
		if ( Options::is_enabled( 'siteground_optimizer_enable_memcached' ) ) {
			$memcached = new Memcache();
			$memcached->remove_memcached_dropin();
			$memcached->create_memcached_dropin();
		}
	}
}
