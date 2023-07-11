<?php
namespace SiteGround_Optimizer\Supercacher;

use SiteGround_Optimizer\Helper\Update_Queue_Trait;

/**
 * SG CachePress class that handle comment updates and purge the cache.
 */
class Supercacher_Comments {
	use Update_Queue_Trait;
	/**
	 * Purge comment post cache.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $comment_id The comment ID.
	 */
	public function purge_comment_post( $comment_id ) {
		// Get the comment data.
		$commentdata = get_comment( $comment_id, OBJECT );

		// Check if the comment moderation is turned on or if the comment is marked as spam and what the current hook is.
		if (
			'wp_insert_comment' === current_action() &&
			( 'spam' === $commentdata->comment_approved || 1 === intval( get_option( 'comment_moderation', 0 ) ) )
		) {
			return;
		}

		// Purge the rest api cache.
		$this->update_queue(
			array(
				get_rest_url(),
				get_permalink( intval( $commentdata->comment_post_ID ) ),
			)
		);
	}
}
