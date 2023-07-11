<?php
namespace SiteGround_Optimizer\Images_Optimizer;

use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\Options\Options;

/**
 * SG Abstract_Images_Optimizer main plugin class.
 *
 * @since 5.9.0
 */
abstract class Abstract_Images_Optimizer {
	/**
	 * The batch limit.
	 *
	 * @since 5.0.0
	 *
	 * @var int The batch limit.
	 */
	const BATCH_LIMIT = 200;

	/**
	 * The png image size limit. Bigger images won't be optimized.
	 *
	 * @since 5.0.0
	 *
	 * @var int The png image size limit.
	 */
	const PNGS_SIZE_LIMIT = 1048576;

	/**
	 * Start the optimization.
	 *
	 * @since  5.9.0
	 */
	public function initialize() {
		// Flush the cache, to avoid stucked optimizations.
		Supercacher::purge_cache();

		foreach ( $this->options_map as $reset_option ) {
			// Reset the status.
			update_option( $reset_option, 0, false );
		}

		update_option(
			$this->non_optimized,
			Options::check_for_unoptimized_images( $this->type ),
			false
		);

		// Fork the process in background.
		$args = array(
			'timeout'   => 0.01,
			'cookies'   => $_COOKIE,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		);

		$response = wp_remote_post(
			add_query_arg( 'action', $this->action, admin_url( 'admin-ajax.php' ) ),
			$args
		);
	}

	/**
	 * Get images batch.
	 *
	 * @since  5.9.0
	 *
	 * @return array Array containing all images ids that are not optimized.
	 */
	public function get_batch() {
		// Flush the cache before prepare a new batch.
		wp_cache_flush();
		// Get the images.
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => self::BATCH_LIMIT,
				'fields'         => 'ids',
				'meta_query'     => array(
					// Skip optimized images.
					array(
						'key'     => $this->batch_skipped,
						'compare' => 'NOT EXISTS',
					),
					// Also skip failed optimizations.
					array(
						'key'     => $this->process_map['failed'],
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		return $images;
	}

	/**
	 * Optimize the images.
	 *
	 * @since  5.9.0
	 */
	public function start_optimization() {
		$started = time();
		// Get image ids.
		$ids = $this->get_batch();
		// There are no more images to process, so complete the optimization.
		if ( empty( $ids ) ) {
			// Clear the scheduled cron and update the optimization status.
			$this->complete();
			return;
		}

		/**
		 * Allow users to change the default timeout.
		 * On SiteGround servers the default timeout is 120 seconds
		 *
		 * @since 5.0.0
		 *
		 * @param int $timeout The timeout in seconds.
		 */
		$timeout = apply_filters( $this->process_map['filter'], 120 );

		// Try to lock the process if there is a timeout.
		if ( false === $this->maybe_lock( $timeout ) ) {
			return;
		}

		// Schedule next event right after the current one is completed.
		if ( 0 !== $timeout ) {
			wp_schedule_single_event( time() + $timeout, $this->cron_type );
		}

		// Loop through all images and optimize them.
		foreach ( $ids as $id ) {
			// Keep track of the number of times we've attempted to optimize the image.
			$count = (int) get_post_meta( $id, $this->process_map['attempts'], true );

			if ( $count > 1 ) {
				update_post_meta( $id, $this->process_map['failed'], 1 );
				continue;
			}

			update_post_meta( $id, $this->process_map['attempts'], $count + 1 );

			// Get attachment metadata.
			$metadata = wp_get_attachment_metadata( $id );

			// Optimize the main image and the other image sizes.
			$status = $this->optimize( $id, $metadata );

			// Mark image if the optimization failed.
			if ( false === $status ) {
				update_post_meta( $id, $this->process_map['failed'], 1 );
			}

			// Break script execution before we hit the max execution time.
			if ( ( $started + $timeout - 5 ) < time() ) {
				break;
			}
		}
	}

	/**
	 * Delete the scheduled cron and update the status of optimization.
	 *
	 * @since  5.9.0
	 */
	public function complete() {

		// Clear the scheduled cron after the optimization is completed.
		wp_clear_scheduled_hook( $this->cron_type );

		// Update the status to finished.
		update_option( $this->options_map['completed'], 1, false );
		update_option( $this->options_map['status'], 1, false );
		update_option( $this->options_map['stopped'], 0, false );

		// Delete the lock.
		delete_option( $this->process_lock );
		delete_option( $this->non_optimized );

		// Finally purge the cache.
		Supercacher::purge_cache();
	}

	/**
	 * Lock the currently running process if the timeout is set.
	 *
	 * @since  5.9.0
	 *
	 * @param  int $timeout The max_execution_time value.
	 *
	 * @return bool         True if the timeout is not set or if the lock has been created.
	 */
	public function maybe_lock( $timeout ) {
		// No reason to lock if there's no timeout.
		if ( 0 === $timeout ) {
			return true;
		}

		// Try to lock.
		$lock_result = add_option( $this->process_lock, time(), '', 'no' );

		if ( ! $lock_result ) {

			$lock_result = get_option( $this->process_lock );

			// Bail if we were unable to create a lock, or if the existing lock is still valid.
			if ( ! $lock_result || ( $lock_result > ( time() - $timeout ) ) ) {
				$timestamp = wp_next_scheduled( $this->cron_type );

				if ( false === (bool) $timestamp ) {
					$response = wp_schedule_single_event( time() + $timeout, $this->cron_type );

				}
				return false;
			}
		}

		update_option( $this->process_lock, time(), false );

		return true;
	}

	/**
	 * Optimize newly uploaded images.
	 *
	 * @since  5.9.0
	 *
	 * @param  array $data          Array of updated attachment meta data.
	 * @param  int   $attachment_id Attachment post ID.
	 */
	public function optimize_new_image( $data, $attachment_id ) {
		// Optimize the image.
		$this->optimize( $attachment_id, $data );

		// Return the attachment data.
		return $data;
	}

	/**
	 * Update the total unoptimized images count.
	 *
	 * @since  5.4.0
	 *
	 * @param  array $data          Array of updated attachment meta data.
	 */
	public function maybe_update_total_unoptimized_images( $data ) {
		if ( Options::is_enabled( $this->options_map['status'] ) ) {
			return $data;
		}

		update_option(
			$this->non_optimized,
			get_option( $this->non_optimized, 0 ) + 1
		);

		// Return the attachment data.
		return $data;
	}

	/**
	 * Deletes images meta_key flag to allow reoptimization.
	 *
	 * @since  5.9.0
	 */
	public function reset_image_optimization_status() {
		global $wpdb;

		$wpdb->query(
			"
				DELETE FROM $wpdb->postmeta
				WHERE `meta_key` = '" . $this->batch_skipped . "'
				OR `meta_key` = '" . $this->process_map['attempts'] . "'
				OR `meta_key` = '" . $this->process_map['failed'] . "'
				OR `meta_key` = 'siteground_optimizer_original_filesize'
			"
		);
	}
}
