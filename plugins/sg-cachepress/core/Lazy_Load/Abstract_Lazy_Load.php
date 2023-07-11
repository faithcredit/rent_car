<?php
namespace SiteGround_Optimizer\Lazy_Load;
use SiteGround_Optimizer\Helper\Helper;

/**
 * SG Abstract_Lazy_load main plugin class.
 */
abstract class Abstract_Lazy_Load {
	/**
	 * Regex for class matching.
	 *
	 * @var string
	 */
	public $regex_classes = '/class=["\'](.*?)["\']/is';

	/**
	 * Check if it's feed, the content is empty or there are conflicting plugins.
	 *
	 * @since  5.6.0
	 *
	 * @param  string $content The page content.
	 *
	 * @return bool            Whether should proceed with replacements.
	 */
	public function should_process( $content ) {
		if (
			$this->is_lazy_url_excluded() ||
			$this->is_lazy_post_type_excluded() ||
			is_feed() ||
			empty( $content ) ||
			is_admin() ||
			( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) ||
			( method_exists( 'FLBuilderModel', 'is_builder_enabled' ) && true === \FLBuilderModel::is_builder_enabled() )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Filter the html output.
	 *
	 * @since  5.4.3
	 *
	 * @param  string $content The content.
	 *
	 * @return string          Modified content.
	 */
	public function filter_html( $content ) {
		// Bail if it's feed or if the content is empty.
		if ( $this->should_process( $content ) ) {
			return $content;
		}

		 // Check for items.
		preg_match_all( $this->regexp, $content, $matches );

		$search  = array();
		$replace = array();

		// Check for specific asset being excluded.
		$excluded_assets = apply_filters( $this->exclude_assets_filter, array() );

		foreach ( $matches[0] as $item ) {
			// Skip already replaced item.
			if ( preg_match( $this->regex_replaced, $item ) ) {
				continue;
			}

			// Check if we have a filter for excluding specific asset from being lazy loaded.
			if ( ! empty( $excluded_assets ) ) {
				// Match the url of the asset.
				preg_match( '~(?:src=")([^"]*)"~', $item, $src_match );

				// If we have a match and the array is part of the excluded assets bail from lazy loading.
				if ( ! empty( $src_match ) && in_array( $src_match[1], $excluded_assets ) ) {
					continue;
				}
			}

			// Do some checking if there are any class matches.
			preg_match( $this->regex_classes, $item, $class_matches );

			if ( ! empty( $class_matches[1] ) ) {
				$classes = $class_matches[1];

				// Load the ignored item classes.
				$ignored_classes = apply_filters(
					'sgo_lazy_load_exclude_classes',
					get_option( 'siteground_optimizer_excluded_lazy_load_classes', array() )
				);

				// Convert all classes to array.
				$item_classes = explode( ' ', $class_matches[1] );

				// Check if the item has ignored class and bail if has.
				if ( array_intersect( $item_classes, $ignored_classes ) ) {
					continue;
				}

				$orig_item = str_replace( $classes, $classes . ' lazyload', $item );
			} else {
				$orig_item = $this->add_lazyload_class( $item );

			}

			// Finally do the search/replace and return modified content.
			$new_item = preg_replace(
				$this->patterns,
				$this->replacements,
				$orig_item
			);

			array_push( $search, $item );
			array_push( $replace, $new_item );
		}

		return str_replace( $search, $replace, $content );
	}

	/**
	 * Check if the specific url has been excluded from lazy loading.
	 *
	 * @since  7.1.3
	 *
	 * @return boolean True if it is excluded, false otherwise.
	 */
	public function is_lazy_url_excluded() {
		// Get the urls where lazy load is excluded.
		$excluded_urls = apply_filters( 'sgo_lazy_load_exclude_urls', array() );

		// Bail if no excludes are found or we do not have a match.
		if ( empty( $excluded_urls ) && ! in_array( Helper::get_current_url(), $excluded_urls ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if a specific post type is excluded for lazyloading.
	 *
	 * @since  7.1.3
	 *
	 * @return boolean True if it is excluded, false otherwise.
	 */
	public function is_lazy_post_type_excluded() {
		// Get the post_types with excluded lazy load.
		$excluded_post_types = apply_filters( 'sgo_lazy_load_exclude_post_types', array() );

		// Bail if no excludes are found or we do not have a match.
		if ( empty( $excluded_post_types ) && ! in_array( get_post_type(), $excluded_post_types ) ) {
			return false;
		}

		return true;
	}
}
