<?php
namespace SiteGround_Optimizer\Install_Service;

class Install_5_0_5 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.0.5
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.0.5';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.0.5
	 */
	public function install() {
		// Reset endless running compatibility checks and images optimization.
		wp_clear_scheduled_hook( 'siteground_optimizer_start_test_cron' );
		wp_clear_scheduled_hook( 'siteground_optimizer_start_image_optimization_cron' );

		// Update the status to finished.
		update_option( 'siteground_optimizer_phpcompat_status', 1 );
		update_option( 'siteground_optimizer_phpcompat_progress', 0 );
		update_option( 'siteground_optimizer_phpcompat_is_compatible', 0 );
		update_option( 'siteground_optimizer_phpcompat_result', array() );
		update_option( 'siteground_optimizer_image_optimization_completed', 1, false );

		// Delete the lock options.
		delete_option( 'siteground_optimizer_image_optimization_lock' );
		delete_option( 'siteground_optimizer_lock' );
	}

}