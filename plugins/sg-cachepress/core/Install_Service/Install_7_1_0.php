<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Helper\Helper_Service;

class Install_7_1_0 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 7.1.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '7.1.0';

	/**
	 * Run the install procedure.
	 *
	 * @since 7.1.0
	 */
	public function install() {
		// Check user status.
		if (
			! Helper_Service::is_siteground() &&
			false === get_option( 'siteground_settings_optimizer', false ) &&
			false === get_option( 'siteground_settings_optimizer_hello', false )
		) {
			update_option( 'siteground_settings_optimizer_hello', 1 );
		}
	}
}
