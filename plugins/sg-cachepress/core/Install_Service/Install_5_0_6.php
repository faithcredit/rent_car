<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Memcache\Memcache;

class Install_5_0_6 extends Install {

	/**
	 * Memcache instance.
	 *
	 * @var Memcache
	 */
	public $memcache;

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.0.5
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.0.6';

	public function __construct() {
		$this->memcache = new Memcache();
	}
	/**
	 * Run the install procedure.
	 *
	 * @since 5.0.5
	 */
	public function install() {

		if (
			Options::is_enabled( 'siteground_optimizer_enable_memcached' )
		) {
			$this->memcache->remove_memcached_dropin();
			$this->memcache->create_memcached_dropin();
		}
	}

}