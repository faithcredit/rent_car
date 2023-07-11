<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Helper\Helper_Service;

class Install_5_3_0 extends Install {
	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $htaccess_service;
	public $options_service;

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.3.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.3.0';

	public function __construct() {
		$this->htaccess_service = new Htaccess();
		$this->options_service = new Options();
	}
	/**
	 * Run the install procedure.
	 *
	 * @since 5.3.0
	 */
	public function install() {
		$this->htaccess_service->disable( 'browser-caching' );
		$this->htaccess_service->enable( 'browser-caching' );

		if ( Helper_Service::is_siteground() ) {
			// $this->options_service->disable_option( 'siteground_optimizer_enable_browser_caching' );
			$this->options_service->disable_option( 'siteground_optimizer_enable_gzip_compression' );

			$this->htaccess_service->disable( 'gzip' );
		}

	}

}