<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Options\Options;

class Install_5_0_9 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.0.9
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.0.9';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.0.9
	 */
	public function install() {

		if ( Options::is_enabled( 'siteground_optimizer_enable_memcached' ) ) {
			$memcached = new Memcache();
			$memcached->remove_memcached_dropin();
			$memcached->create_memcached_dropin();
		}
	}

}