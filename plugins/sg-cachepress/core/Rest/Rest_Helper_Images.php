<?php
namespace SiteGround_Optimizer\Rest;

use SiteGround_Optimizer\Images_Optimizer\Images_Optimizer;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Message_Service\Message_Service;
/**
 * Rest Helper class that manages image optimisation  settings.
 */
class Rest_Helper_Images extends Rest_Helper {
	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $options;
	public $images_optimizer;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->options          = new Options();
		$this->images_optimizer = new Images_Optimizer();
	}

	/**
	 * Manage image optimization
	 *
	 * @since  @version
	 *
	 * @param  object $request Request data.
	 */
	public function manage_image_optimization( $request ) {
		// Validate rest request and prepare data.
		$selected                = $this->validate_and_get_option_value( $request, 'compression_level' );
		$maybe_backup            = $this->validate_and_get_option_value( $request, 'backup_media' );
		$maybe_compress_existing = $this->validate_and_get_option_value( $request, 'compress_existing' );
		$maybe_overwrite_custom  = $this->validate_and_get_option_value( $request, 'overwrite_custom' );

		update_option( 'siteground_optimizer_compression_level_old', get_option( 'siteground_optimizer_compression_level', 1 ) );
		update_option( 'siteground_optimizer_compression_level', $selected );
		update_option( 'siteground_optimizer_backup_media', $maybe_backup );
		update_option( 'siteground_optimizer_compress_existing', $maybe_compress_existing );
		update_option( 'siteground_optimizer_overwrite_custom', $maybe_overwrite_custom );


		$response_data = array(
			'compression_level'         => intval( $selected ),
			'backup_media'              => $maybe_backup,
			'compress_existing'         => $maybe_compress_existing,
			'image_optimization_status' => 1,
		);

		// Restore backups if compress_existing and compression_level 0 are selected.
		if ( 0 === $selected && 0 !== intval( $maybe_compress_existing ) ) {
			$this->images_optimizer->restore_originals();

			// Send the response.
			self::send_json_success( Message_Service::get_response_message( 1, 'image_compression_settings', null ), $response_data );
		}

		// Proceed with compressing the images, if compress_existing is set.
		if ( 0 !== intval( $maybe_compress_existing ) ) {
			$this->images_optimizer->reset_image_optimization_status();

			// Init the optimization.
			$this->images_optimizer->initialize();

			$response_data = array_merge(
				$response_data,
				array(
					'image_optimization_status'   => 0,
					'has_images_for_optimization' => (int) get_option( 'siteground_optimizer_total_unoptimized_images', 0 ) - 1,
					'total_unoptimized_images'    => (int) get_option( 'siteground_optimizer_total_unoptimized_images', 0 ),
				)
			);
		}

		// Send the response.
		self::send_json_success( Message_Service::get_response_message( 1, 'image_compression_settings', null ), $response_data );
	}

	/**
	 * Return the status of current compatibility check.
	 *
	 * @since  5.0.0
	 */
	public function check_image_optimizing_status() {
		$unoptimized_images = $this->options->check_for_unoptimized_images( 'image' );

		if ( 0 === $unoptimized_images ) {
			$this->images_optimizer->complete();
		}

		// Send the response.
		self::send_json_success(
			'',
			array(
				'compression_level'           => (int) get_option( 'siteground_optimizer_compression_level', 0 ),
				'backup_media'                => (int) get_option( 'siteground_optimizer_backup_media' ),
				'compress_existing'           => (int) get_option( 'siteground_optimizer_compress_existing' ),
				'overwrite_custom'            => (int) get_option( 'siteground_optimizer_overwrite_custom' ),
				'image_optimization_status'   => (int) get_option( 'siteground_optimizer_image_optimization_completed', 1 ),
				'has_images_for_optimization' => (int) $unoptimized_images - 1,
				'total_unoptimized_images'    => (int) get_option( 'siteground_optimizer_total_unoptimized_images' ),
			)
		);
	}

	/**
	 * Deletes images meta_key flag to allow reoptimization.
	 *
	 * @since  5.0.0
	 */
	public function reset_images_optimization() {
		// Disable the optimization.
		// Clear the scheduled cron after the optimization is completed.
		wp_clear_scheduled_hook( 'siteground_optimizer_start_image_optimization_cron' );

		// Update the status to finished.
		update_option( 'siteground_optimizer_image_optimization_completed', 1, false );
		update_option( 'siteground_optimizer_image_optimization_status', 1, false );
		update_option( 'siteground_optimizer_image_optimization_stopped', 1, false );

		// Delete the lock.
		delete_option( 'siteground_optimizer_image_optimization_lock' );

		// Send the response.
		self::send_json_success(
			'',
			array(
				'compression_level'           => (int) get_option( 'siteground_optimizer_compression_level_old', 1 ),
				'backup_media'                => (int) get_option( 'siteground_optimizer_backup_media' ),
				'compress_existing'           => (int) get_option( 'siteground_optimizer_compress_existing' ),
				'overwrite_custom'            => (int) get_option( 'siteground_optimizer_overwrite_custom' ),
				'image_optimization_status'   => 1,
				'image_optimization_stopped'  => 1,
				'has_images_for_optimization' => (int) $this->options->check_for_unoptimized_images( 'image' ),
			)
		);
		$this->images_optimizer->reset_image_optimization_status();

		// Send the response.
		self::send_json_success();
	}

	/**
	 * Optimizes the preview image and returns the URLs for both the optimized and the original images.
	 *
	 * @since @version
	 *
	 * @param  object $request Request data.
	 */
	public function get_preview_images( $request ) {
		// Prepare data.
		$id = ! empty( $request->get_params()['id'] ) ? $request->get_params()['id'] : false;

		// Check if body of the request is empty, if so - send default response.
		self::send_json_success(
			'',
			array(
				'images' => $this->images_optimizer->get_preview_images( $id ),
			)
		);
	}

	/**
	 * Sets the maximum image width.
	 *
	 * @since 7.0.10
	 *
	 * @param  object $request Request data.
	 */
	public function manage_resize_images( $request ) {
		// Retrieve the value from the request.
		$value = $this->validate_and_get_option_value( $request, 'image_resize' );

		// Update the option in the DB.
		update_option( 'siteground_optimizer_resize_images', intval( $value ) );

		// Check if body of the request is empty, if so - send default response.
		self::send_json_success(
			Message_Service::get_response_message( 1, 'resize_images' ),
			$this->images_optimizer->prepare_max_width_sizes()
		);
	}
}
