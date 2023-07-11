<?php
namespace SiteGround_Optimizer\Supercacher;

use SiteGround_Optimizer\Helper\Update_Queue_Trait;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\File_Cacher\File_Cacher;
use WP_Rewrite;
/**
 * SG CachePress main plugin class
 */
class Supercacher_Posts {
	use Update_Queue_Trait;

	/**
	 * List of post types excluded from smart cache purge.
	 *
	 * @since 7.0.5
	 *
	 * @var array.
	 */
	public $excluded_post_types = array(
		// eventprime-guest-bookings
		'em_booking',
	);

	/**
	 * List of post statuses excluded from smart cache purge.
	 *
	 * @since 7.0.0
	 *
	 * @var   array.
	 */
	public $excluded_post_status = array(
		// Drafts
		'draft',
		'auto-draft',
		// Trash
		'trash'
	);

	/**
	 * Get all parent pages of the certain post.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $post_id The post id.
	 */
	public function get_parents_urls( $post_id ) {
		// Get post parents.
		$parents = get_ancestors(
			$post_id,
			get_post_type( $post_id ),
			'post_type'
		);

		$parents_urls = array();

		// Bail if the post top level post.
		if ( empty( $parents ) ) {
			return $parents_urls;
		}

		// Adds all parents to the purge queue.
		foreach ( $parents as $id ) {
			$parents_urls[] = get_permalink( $id );
		}

		// Return an array with URLs of all post parent pages.
		return $parents_urls;
	}

	/**
	 * Get all post terms.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $post_id The post id.
	 */
	public function get_post_terms( $post_id ) {
		// Get all post taxonomies.
		$taxonomies = get_post_taxonomies( $post_id );

		// Get term ids.
		$term_ids = wp_get_object_terms(
			$post_id,
			$taxonomies,
			array(
				'fields' => 'ids',
			)
		);

		$term_urls = array();

		// Bail if there are no term_ids.
		if ( empty( $term_ids ) ) {
			return $term_urls;
		}

		// Init the terms cacher.
		$supercacher_terms = new Supercacher_Terms();

		// Loop through all terms ids and purge the cache.
		foreach ( $term_ids as $id ) {
			$term_urls[] = $supercacher_terms->get_term_url( $id );
		}

		// Return an array with all post term URLs.
		return $term_urls;
	}

	/**
	 * Get the Blog Page URL.
	 *
	 * @since  5.7.20
	 */
	public function get_blog_page() {
		// Check if a blog page is set.
		$blog_id = (int) get_option( 'page_for_posts' );

		// Bail if home page is set for blog page.
		if ( empty( $blog_id ) ) {
			return get_home_url( null, '/' );
		}

		// Purge the cache for that post.
		return get_permalink( $blog_id );
	}

	/**
	 * Adds the post that has been changed and it's parents,
	 * the index cache, and the post categories to the purge cache queue.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $post_id The post id.
	 */
	public function purge_all_post_cache( $post_id ) {
		// Get the post.
		$post = get_post( $post_id );

		// Bail if the current hook is save_post and the post is scheduled.
		if ( 'save_post' === current_action() && 'future' === get_post_status( $post_id ) ) {
			return;
		}

		// Bail if the current hook is publish_post and the post isn't scheduled.
		if ( 'publish_post' === current_action() && 'future' !== get_post_status( $post_id ) ) {
			return;
		}

		// Bail if post type is excluded from cache purge.
		if ( true === $this->is_post_excluded_from_cache_purge( $post ) ) {
			return;
		}

		// Purge all cache if the WPML plugin is active.
		if ( class_exists( 'SitePress' ) ) {
			if ( Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
				File_Cacher::get_instance()->purge_everything();
			}

			return Supercacher::get_instance()->purge_everything();
		}

		// Delete the index page only if this is the front page.
		if ( (int) get_option( 'page_on_front' ) === $post_id ) {
			// Add the index page to the cache purge queue.
			$this->update_queue( array( get_home_url( null, '/' ) ) );
			return;
		}

		// Init the WP Rewrite Class.
		global $wp_rewrite;

		$wp_rewrite = is_null( $wp_rewrite ) ? new WP_Rewrite() : $wp_rewrite; //phpcs:ignore

		// Add the URLs to the purge cache queue.
		$this->update_queue(
			array_merge(
				// The post parent URLs.
				$this->get_parents_urls( $post_id ),
				// The post term URLs.
				$this->get_post_terms( $post_id ),
				// The default URLs.
				array(
					get_rest_url(), // The rest api URL.
					get_permalink( $post_id ), // The post URL.
					$this->get_blog_page(), // The blog page URL.
					get_home_url( null, '/' ), // The home URL.
					get_home_url( null, '/feed' ), // The Feed URL.
				)
			)
		);
	}

	/**
	 * Check if post is excluded from cache purge.
	 *
	 * @since  7.0.0
	 *
	 * @param  object $post The WP_Post Object.
	 * @return bool         True if post is excluded, false if not.
	 */
	public function is_post_excluded_from_cache_purge( $post ) {
		// Get Post Type object
		$post_type = get_post_type_object( $post->post_type );

		// Return true if post type is not an object. This check is needed for initial post type registration.
		if ( ! is_object( $post_type ) ) {
			return true;
		}

		// True if post type/status is excluded or post type is not public.
		if (
			in_array( $post_type->name, $this->excluded_post_types ) || // Post type is excluded
			in_array( $post->post_status, $this->excluded_post_status ) || // Post status is excluded
			false === $post_type->public // Post type is not public
		) {
			// Flush only rest cache if post type is excluded but visible in rest.
			if ( true === $post_type->show_in_rest ) {
				Supercacher::get_instance()->purge_rest_cache();
			}
			return true;
		}

		return false;
	}
}
