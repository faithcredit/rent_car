<?php
namespace SiteGround_Optimizer\Install_Service;
use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Options\Options;

class Install_5_5_4 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.5.4
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.5.4';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.5.4
	 */
	public function install() {
		if (
			Options::is_enabled( 'siteground_optimizer_enable_memcached' )
		) {
			$memcache = new Memcache();
			$memcache->remove_memcached_dropin();
			$memcache->create_memcached_dropin();
		}
	}
}
