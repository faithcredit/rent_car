<?php
namespace SiteGround_Optimizer\Supercacher;

use SiteGround_Optimizer\Helper\Update_Queue_Trait;

/**
 * SG CachePress class that handle term actions and purge the cache.
 */
class Supercacher_Terms {
	use Update_Queue_Trait;
	/**
	 * Array of all taxonomies that should be ignored.
	 *
	 * @var array $ignored_taxonomies Array of all taxonomies that should be ignored.
	 */
	private $ignored_taxonomies = array(
		'product_type',
		'product_visibility',
	);

	/**
	 * Purge single term cache.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $term_id The term id.
	 *
	 * @return bool         True on success, false on failure.
	 */
	public function get_term_url( $term_id ) {
		// Get the term.
		$term = \get_term( $term_id );

		// Bail if we should ignore the taxonomy.
		if ( 
			NULL === $term ||
			is_wp_error( $term ) ||
			in_array( $term->taxonomy, $this->ignored_taxonomies ) 
		) {
			return;
		}

		// Get term link.
		$term_url = \get_term_link( $term_id );

		if ( empty( $term_url ) ) {
			return;
		}

		return $term_url;
	}

	/**
	 * Purge the term and index.php cache.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $term_id The term id.
	 */
	public function purge_term_and_index_cache( $term_id ) {
		$this->update_queue( array(
			get_rest_url(),
			get_home_url( null, '/' ),
			$this->get_term_url( $term_id ),
		) );
	}
}
