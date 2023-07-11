<?php
namespace SiteGround_Optimizer\Helper;

use SiteGround_Optimizer\Helper\Helper;
/**
 * Trait used for factory pattern in the plugin.
 */
trait Update_Queue_Trait {

	/**
	 * Update the purge queue.
	 *
	 * @since 5.9.0
	 *
	 * @param string $urls The URLs to purge.
	 */
	public function update_queue( $urls ) {
		// Get the current purge queue.
		$queue = get_option( 'siteground_optimizer_smart_cache_purge_queue', array() );

		// If there is already a data present on it, update the value.
		$queue = array_unique( array_merge( $queue, $urls ) );
		// Do not update the queue if a cronjob or ajax request is made.
		if ( wp_doing_cron() || Helper::sg_doing_ajax() ) {
			// Schedule a cron job that will delete all assets (minified js and css files) every 30 days.
			if ( wp_next_scheduled( 'siteground_optimizer_purge_cron_cache' ) ) {
				wp_clear_scheduled_hook( 'siteground_optimizer_purge_cron_cache' );
			}

			// Schedule a full cache purge cron.
			wp_schedule_single_event( time() + 90, 'siteground_optimizer_purge_cron_cache' );
			return;
		}

		update_option( 'siteground_optimizer_smart_cache_purge_queue', $queue );
	}
}
