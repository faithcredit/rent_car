<?php
namespace SiteGround_Optimizer\Install_Service;
use SiteGround_Optimizer\Options\Options;

class Install_5_4_3 extends Install {

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	public $options;

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.4.3
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.4.3';

	public function __construct() {
		$this->options = new Options();
	}

	/**
	 * Run the install procedure.
	 *
	 * @since 5.4.3
	 */
	public function install() {
		$this->options->enable_option( 'siteground_optimizer_lazyload_iframes', 1 );
		$this->options->enable_option( 'siteground_optimizer_lazyload_videos', 1 );
	}

}