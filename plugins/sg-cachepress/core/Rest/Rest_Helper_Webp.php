<?php
namespace SiteGround_Optimizer\Rest;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Images_Optimizer\Images_Optimizer_Webp;

/**
 * Rest Helper class that process all rest requests and provide json output for react app.
 */
class Rest_Helper_Webp extends Rest_Helper {
	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $options;
	public $webp_images_optimizer;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->options               = new Options();
		$this->webp_images_optimizer = new Images_Optimizer_Webp();
	}

	/**
	 * Initialize images optimization.
	 *
	 * @since  5.4.0
	 */
	public function optimize_webp_images( $request ) {
		// Validate rest request and prepare data.
		$selected = $this->validate_and_get_option_value( $request, 'webp_support' );

		update_option( 'siteground_optimizer_webp_support', $selected );
		update_option( 'siteground_optimizer_webp_conversion_completed', 0 );

		if ( 0 === $selected ) {
			$this->webp_images_optimizer->delete_webp_files();

			// Send the response.
			self::send_json_success(
				'',
				array(
					'webp_support'              => 0,
					'image_optimization_status' => 1,
				)
			);
		}

		$this->webp_images_optimizer->reset_image_optimization_status();

		// Init the optimization.
		$this->webp_images_optimizer->initialize();

		// Send the response.
		self::send_json_success(
			'',
			array(
				'webp_support'               => 1,
				'webp_conversion_status'     => 0,
				'has_images_for_conversion'  => intval( get_option( 'siteground_optimizer_total_non_converted_images', 0 ) - 1 ),
				'total_non_converted_images' => intval( get_option( 'siteground_optimizer_total_non_converted_images', 0 ) ),
			)
		);
	}

	/**
	 * Stops images optimization.
	 *
	 * @since  5.0.8
	 */
	public function reset_webp_conversion() {
		// Clear the scheduled cron after the optimization is completed.
		wp_clear_scheduled_hook( 'siteground_optimizer_start_webp_conversion_cron' );

		// Update the status to finished.
		update_option( 'siteground_optimizer_webp_conversion_completed', 1, false );
		update_option( 'siteground_optimizer_webp_conversion_status', 1, false );
		update_option( 'siteground_optimizer_webp_support', 0 );

		// Delete the lock.
		delete_option( 'siteground_optimizer_webp_conversion_lock' );

		$this->webp_images_optimizer->delete_webp_files();
		$this->webp_images_optimizer->reset_image_optimization_status();

		// Send the response.
		self::send_json_success(
			'',
			array(
				'webp_conversion_status'     => 1,
				'webp_support'               => 0,
				'has_images_for_conversion'  => intval( $this->options->check_for_unoptimized_images( 'webp' ) ),
				'total_non_converted_images' => intval( get_option( 'siteground_optimizer_total_non_converted_images', 0 ) ),
			)
		);
	}

	/**
	 * Return the status of current compatibility check.
	 *
	 * @since  5.4.0
	 */
	public function check_webp_conversion_status() {
		$non_converted_images = $this->options->check_for_unoptimized_images( 'webp' );

		if ( 0 === $non_converted_images ) {
			$this->webp_images_optimizer->complete();
		}

		$status = (int) get_option( 'siteground_optimizer_webp_conversion_status', 1 );

		// Send the response.
		self::send_json_success(
			'',
			array(
				'webp_conversion_status'     => $status,
				'webp_support'               => intval( get_option( 'siteground_optimizer_webp_support', 0 ) ),
				'has_images_for_conversion'  => intval( $non_converted_images - 1 ),
				'total_non_converted_images' => intval( get_option( 'siteground_optimizer_total_non_converted_images', 0 ) ),
			)
		);
	}
}
