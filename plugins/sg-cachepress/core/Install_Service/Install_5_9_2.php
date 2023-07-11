<?php
namespace SiteGround_Optimizer\Install_Service;
use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Helper\Helper;

class Install_5_9_2 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.7.4
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.9.2';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.7.4
	 */
	public function install() {
		delete_option( 'siteground_optimizer_smart_cache_purge_queue' );
	}

}