<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Helper\Helper_Service;

class Install_5_3_10 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.3.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.3.10';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.3.0
	 */
	public function install() {

		if ( Helper_Service::is_siteground() ) {

			$this->htaccess_service = new Htaccess();
			$this->options_service = new Options();

			$this->options_service->disable_option( 'siteground_optimizer_enable_browser_caching' );

			$this->htaccess_service->disable( 'browser-caching' );
		}

	}

}