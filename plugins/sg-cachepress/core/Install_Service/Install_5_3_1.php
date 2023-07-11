<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Ssl\Ssl;
use SiteGround_Optimizer\Options\Options;

class Install_5_3_1 extends Install {
	/**
	 * Local variables
	 *
	 * @var Ssl
	 */
	public $ssl_service;

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.3.1
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.3.1';

	public function __construct() {
		$this->ssl_service = new Ssl();
	}
	/**
	 * Run the install procedure.
	 *
	 * @since 5.3.1
	 */
	public function install() {
		if ( Options::is_enabled( 'siteground_optimizer_ssl_enabled' ) ) {
			$this->ssl_service->enable();
		}

	}

}