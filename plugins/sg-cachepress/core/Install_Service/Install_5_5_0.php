<?php
namespace SiteGround_Optimizer\Install_Service;
use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Helper\Helper_Service;

class Install_5_5_0 extends Install {

	/**
	 * Htaccess instance.
	 *
	 * @var Htaccess
	 */
	public $htaccess_service;

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.5.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.5.0';

	public function __construct() {
		$this->htaccess_service = new Htaccess();
	}

	/**
	 * Run the install procedure.
	 *
	 * @since 5.5.0
	 */
	public function install() {

		if (
			Options::is_enabled( 'siteground_optimizer_enable_browser_caching' ) &&
			! Helper_Service::is_siteground()
		) {
			$this->htaccess_service->enable( 'browser-caching' );
		}

		update_option( 'siteground_optimizer_whats_new', array(
			array(
				'type'         => 'default',
				'title'        => __( 'JavaScript Files Combination', 'sg-cachepress' ),
				'text'         => __( 'We are combining all the JS files that your theme and plugins include in your site header and footer in order to lower the number of requests it is producing. We will keep the scripts in their original location after the combination for best compatibility with other plugins. Note, that in some cases you may need to exclude a script or two from combination so make sure you check your site after enabling the JavaScript Files Combination!', 'sg-cachepress' ),
				'icon'         => 'presentational-javascript-files',
				'icon_color'   => 'ocean',
				'optimization' => 'combine_javascript',
				'button' => array(
					'text'  => __( 'Enable Now', 'sg-cachepress' ),
					'color' => 'primary',
					'link'  => 'frontend',
				),
			),
		) );

		update_option( 'siteground_optimizer_quality_webp', 85 );
		update_option( 'siteground_optimizer_quality_type', 'lossy' );
	}
}
