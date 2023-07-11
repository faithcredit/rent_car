<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Helper\Helper_Service;

class Install_6_0_0 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.7.4
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '6.0.0';

	/**
	 * The options db prefix.
	 *
	 * @var string
	 */
	public $prefix = 'siteground_optimizer_';

	/**
	 * Legacy options list.
	 *
	 * @var array
	 */
	public $legacy_options = array(
		// Heartbeat options.
		'heartbeat_control',
		'heartbeat_post_status',
		'heartbeat_dashboard_status',
		'heartbeat_frontend_status',
		// Lazyload options.
		'lazyload_mobile',
		'lazyload_iframes',
		'lazyload_videos',
		'lazyload_gravatars',
		'lazyload_thumbnails',
		'lazyload_responsive',
		'lazyload_textwidgets',
		'lazyload_shortcodes',
		'lazyload_woocommerce',
	);

	/**
	 * Run the install procedure.
	 *
	 * @since 5.7.4
	 */
	public function install() {
		// Manage the old heartbeat related processes.
		$this->manage_heartbeat_control();
		// Manage the lazyload settings.
		$this->manage_lazyload();

		// Loop legacy options and delete them.
		foreach ( $this->legacy_options as $option ) {
			delete_option( $this->prefix . $option );
		}

		// Automatically enable Dynamic Cache for SiteGround users.
		if ( Helper_Service::is_siteground() ) {
			update_option( 'siteground_optimizer_enable_cache', 1 );
			update_option( 'siteground_optimizer_autoflush_cache', 1 );
		}
	}

	/**
	 * Managa the Heartbeat options. Modify options.
	 *
	 * @since  6.0.0
	 */
	public function manage_heartbeat_control() {
		// Get the current status.
		$curent_optimization_status = get_option( $this->prefix . 'heartbeat_control', 0 );

		// Set the new options if the optimization is disabled and the old option is present.
		if ( 0 === $curent_optimization_status ) {
			// Update the new options.
			update_option( $this->prefix . 'heartbeat_post_interval', 120 );
			update_option( $this->prefix . 'heartbeat_dashboard_interval', 0 );
			update_option( $this->prefix . 'heartbeat_frontend_interval', 0 );

			return;
		}

		// New vlaues which will be added.
		$new_heartbeat_values = array(
			'heartbeat_post_interval',
			'heartbeat_dashboard_interval',
			'heartbeat_frontend_interval',
		);

		// Loop the "new" options and re-write their values if necesary.
		foreach ( $new_heartbeat_values as $option ) {
			// Get the current calue of the options.
			$current_value = intval( get_option( $this->prefix . $option, 0 ) );

			// If the value is between 5 and 15 seconds give it the new default.
			if ( 0 < $current_value && 15 >= $current_value ) {
				update_option( $this->prefix . $option, 15 );
				continue;
			}

			// Set the value to the highest possible one.
			if ( 120 < $current_value ) {
				update_option( $this->prefix . $option, 120 );
				continue;
			}

			// If we have old values, write to the closest one from the new defaults.
			$step      = 30;
			$new_value = ( 0 === round( $current_value ) % $step ) ? round( $current_value ) : round( ( $current_value + $step / 2 ) / $step ) * $step;

			// Update the option with the new values.
			update_option( $this->prefix . $option, $new_value );
		}
	}

	/**
	 * Manage the lazyload functionality. Modify the new options.
	 *
	 * @since  6.0.0
	 */
	public function manage_lazyload() {
		// Old options.
		$legacy_options = array(
			'lazyload_mobile',
			'lazyload_iframes',
			'lazyload_videos',
			'lazyload_gravatars',
			'lazyload_thumbnails',
			'lazyload_responsive',
			'lazyload_textwidgets',
			'lazyload_shortcodes',
			'lazyload_woocommerce',
		);

		$excludes = array();

		// Loop the legacy options.
		foreach ( $legacy_options as $option ) {
			// If the option is disabled or was never used, add them to the excludes list.
			if ( 0 === intval( get_option( $this->prefix . $option, 0 ) ) ) {
				$excludes[] = $option;
			}
		}

		// Update the new option with the excludes.
		update_option( $this->prefix . 'excluded_lazy_load_media_types', $excludes );
	}
}
