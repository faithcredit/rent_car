<?php
namespace SiteGround_Optimizer\Install_Service;

class Install_5_6_7 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.6.7
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.6.7';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.6.7
	 */
	public function install() {
		wp_clear_scheduled_hook( 'siteground_optimizer_check_assets_dir' );
		wp_clear_scheduled_hook( 'siteground_delete_assets' );
	}

}