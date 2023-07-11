<?php
namespace SiteGround_Optimizer\Options;

use SiteGround_Optimizer\Supercacher\Supercacher;

/**
 * Handle PHP compatibility checks.
 */
class Options {
	/**
	 * Check if a single boolean setting is enabled.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $key          Setting field key.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return boolean True if the setting is enabled, false otherwise.
	 */
	public static function is_enabled( $key, $is_multisite = false ) {
		$value = false === $is_multisite ? get_option( $key ) : get_site_option( $key );

		if ( 1 === (int) $value ) {
			return true;
		}

		return false;
	}

	/**
	 * Enable a single boolean setting.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $key          Setting field key.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function enable_option( $key, $is_multisite = false ) {
		// Don't try to enable already enabled option.
		if ( self::is_enabled( $key, $is_multisite ) ) {
			return true;
		}

		// Update the option.
		$result = false === $is_multisite ? update_option( $key, 1 ) : update_site_option( $key, 1 );
		// Purge the cache.
		Supercacher::purge_cache();
		// Return the result.
		return $result;
	}

	/**
	 * Disable a single boolean setting.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $key Setting field key.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function disable_option( $key, $is_multisite = false ) {
		// Don't try to disable already disabled option.
		if ( ! self::is_enabled( $key, $is_multisite ) ) {
			return true;
		}

		// Update the option.
		$result = false === $is_multisite ? update_option( $key, 0 ) : update_site_option( $key, 0 );

		// Purge the cache.
		Supercacher::purge_cache();
		// Return the result.
		return $result;
	}

	/**
	 * Change an option.
	 *
	 * @since 5.5.0
	 *
	 * @param  string $key Setting field key.
	 * @param  string $value Setting value.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function change_option( $key, $value, $is_multisite = false ) {
		// Update the option.
		$result = false === $is_multisite ? update_option( $key, $value ) : update_site_option( $key, $value );

		// Purge the cache.
		Supercacher::purge_cache();
		// Return the result.
		return $result;
	}

	/**
	 * Check if a single boolean setting is enabled for single site in a network.
	 *
	 * @since 5.0.0
	 *
	 * @param  int    $blog_id The blog id.
	 * @param  string $key     Setting field key.
	 *
	 * @return boolean True if the setting is enabled, false otherwise.
	 */
	public static function is_mu_enabled( $blog_id, $key ) {
		$value = get_blog_option( $blog_id, $key );

		if ( 1 === (int) $value ) {
			return true;
		}

		return false;
	}

	/**
	 * Enable a single boolean setting for single site in a network.
	 *
	 * @since 5.0.0
	 *
	 * @param  int    $blog_id The blog id.
	 * @param  string $key     Setting field key.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function enable_mu_option( $blog_id, $key ) {
		// Don't try to enable already enabled option.
		if ( self::is_mu_enabled( $blog_id, $key ) ) {
			return true;
		}

		// Update the option.
		$result = update_blog_option( $blog_id, $key, 1 );
		// Purge the cache.
		Supercacher::purge_cache();
		// Return the result.
		return $result;
	}

	/**
	 * Disable a single boolean setting for single site in a network.
	 *
	 * @since 5.0.0
	 *
	 * @param  int    $blog_id The blog id.
	 * @param  string $key     Setting field key.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function disable_mu_option( $blog_id, $key ) {
		// Don't try to disable already disabled option.
		if ( ! self::is_mu_enabled( $blog_id, $key ) ) {
			return true;
		}

		// Update the option.
		$result = update_blog_option( $blog_id, $key, 0 );

		// Purge the cache.
		Supercacher::purge_cache();
		// Return the result.
		return $result;
	}

	/**
	 * Checks if the `option_key` paramether exists in rest data.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 *
	 * @return string          The option key.
	 */
	private function validate_key( $request ) {
		$data = json_decode( $request->get_body(), true );

		// Bail if the option key is not set.
		if ( empty( $data['option_key'] ) ) {
			wp_send_json_error();
		}

		return $data['option_key'];
	}

	/**
	 * Provide all plugin options.
	 *
	 * @since  5.0.0
	 */
	public function fetch_options() {
		global $wpdb;
		global $blog_id;

		$prefix = $wpdb->get_blog_prefix( $blog_id );

		$options = array();

		$site_options = $wpdb->get_results(
			"
			SELECT REPLACE( option_name, 'siteground_optimizer_', '' ) AS name, option_value AS value
			FROM {$prefix}options
			WHERE option_name LIKE '%siteground_optimizer_%'
		"
		);

		if ( is_multisite() ) {
			$sitemeta_options = $wpdb->get_results(
				"
				SELECT REPLACE( meta_key, 'siteground_optimizer_', '' ) AS name, meta_value AS value
				FROM $wpdb->sitemeta 
				WHERE meta_key LIKE '%siteground_optimizer_%'
			"
			);

			$site_options = array_merge(
				$site_options,
				$sitemeta_options
			);
		}

		foreach ( $site_options as $option ) {
			// Try to unserialize the value.
			$value = maybe_unserialize( $option->value );

			if (
				! is_array( $value ) &&
				null !== filter_var( $value, FILTER_VALIDATE_BOOLEAN )
			) {
				$value = intval( $value );
			}

			$options[ $option->name ] = $value;
		}

		return $options;
	}

	/**
	 * Checks if there are unoptimized images.
	 *
	 * @since  5.9.0
	 *
	 * @return int The count of unoptimized images.
	 */
	public static function check_for_unoptimized_images( $type ) {

		$meta = array(
			'image' => array(
				'siteground_optimizer_is_optimized',
				'siteground_optimizer_optimization_failed',
			),
			'webp'  => array(
				'siteground_optimizer_is_converted_to_webp',
				'siteground_optimizer_webp_conversion_failed',
			),
		);

		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					// Skip optimized images.
					array(
						'key'     => $meta[ $type ][0],
						'compare' => 'NOT EXISTS',
					),
					// Also skip failed optimizations.
					array(
						'key'     => $meta[ $type ][1],
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		return count( $images );
	}

	/**
	 * Checks if there are any images in the library.
	 *
	 * @since  5.3.5
	 *
	 * @return int 1 if thre are any images in the lib, 0 otherwise.
	 */
	public function check_for_images() {
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 1,
			)
		);

		return count( $images );
	}

	/**
	 * Get all post types.
	 *
	 * @since  5.7.0
	 *
	 * @return array $post_types All post types and their names.
	 */
	public function get_post_types() {
		// Get the post types object.
		$post_types_result = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'object'
		);

		// Set the default ones.
		$post_types = array(
			array(
				'value' => 'post',
				'title' => 'Post',
			),
			array(
				'value' => 'page',
				'title' => 'Page',
			),
		);

		// Add the custom types to the default ones.
		foreach ( $post_types_result as $type ) {
			$post_types[] = array(
				'value' => $type->name,
				'title' => $type->label,
			);
		}

		return $post_types;
	}

	/**
	 * Retrieves the possible options for the exclusion of media types from the lazy load logic.
	 *
	 * @since 6.0.0
	 *
	 * @return array The possible for media types to be lazy loaded.
	 */
	public function get_excluded_lazy_load_media_types() {
		$lazy_load_types = array(
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

		$result = array();

		foreach ( $lazy_load_types as $type ) {
			$title = ucfirst( str_replace( 'lazyload_', '', $type ) );

			if ( 'lazyload_textwidgets' === $type ) {
				$title = 'Text Widgets';
			}

			$result[] = array(
				'title' => $title,
				'value' => $type,
			);
		}

		return $result;
	}

	/**
	 * Prepare the defaults for Database optimization menu.
	 *
	 * @since  7.2.2
	 *
	 * @return array $result Array containing the title and value pair for the FE pop-up.
	 */
	public function get_database_optimization_defaults() {
		// List of default supported options and their title.
		$defaults = array(
			'optimize_tables'       => 'Perform Database Optimization for MyISAM tables',
			'delete_auto_drafts'    => 'Delete all automatically created post and page drafts',
			'delete_revisions'      => 'Delete all page and post revisions',
			'delete_trashed_posts'  => 'Delete all posts and pages in your Trash',
			'delete_spam_comments'  => 'Delete all comments marked as Spam',
			'delete_trash_comments' => 'Delete all comments in your Trash',
			'expired_transients'    => 'Delete all expired Transients'
		);

		$result = array();

		// Loop trough all methods and prepare the array to be sent to the FE App.
		foreach ( $defaults as $method => $title ) {
			$result[] = array(
				'title' => $title,
				'value' => $method
			);
		}

		return $result;
	}
}
