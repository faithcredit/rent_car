<?php
namespace SiteGround_Optimizer\Deactivator;

use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\File_Cacher\File_Cacher;

class Deactivator {
	/**
	 * Run on plugin deactivation.
	 *
	 * @since 5.0.5
	 */
	public function deactivate() {
		$memcached = new Memcache();
		$memcached->remove_memcached_dropin();

		// Flush dynamic and memcache.
		Supercacher::purge_cache();
		Supercacher::flush_memcache();
		File_Cacher::cleanup();
	}

}