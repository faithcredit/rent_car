<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Htaccess\Htaccess;

class Install_5_0_8 extends Install {

	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $htaccess;
	public $options;

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.0.8
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.0.8';

	public function __construct() {
		$this->htaccess = new Htaccess();
		$this->options = new Options();
	}
	/**
	 * Run the install procedure.
	 *
	 * @since 5.0.5
	 */
	public function install() {

		if ( $this->htaccess->is_enabled( 'ssl' ) ) {
			$this->options->enable_option( 'siteground_optimizer_ssl_enabled' );
		}
	}

}