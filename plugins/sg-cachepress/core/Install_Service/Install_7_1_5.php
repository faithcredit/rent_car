<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Options\Options;

class Install_7_1_5 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 7.1.5
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '7.1.5';

	/**
	 * Run the install procedure.
	 *
	 * @since 7.1.5
	 */
	public function install() {

		if ( Options::is_enabled( 'siteground_optimizer_enable_memcached' ) ) {
			$memcached = new Memcache();
			$memcached->remove_memcached_dropin();
			$memcached->create_memcached_dropin();
		}
	}

}