<?php
namespace SiteGround_Optimizer\Multisite;

use SiteGround_Optimizer\Rest\Rest;
use SiteGround_Optimizer\Options\Options;

/**
 * Provide data for multisite installations.
 */
class Multisite {

	/**
	 * Return permissions granted by site admin to subsites.
	 *
	 * @since  5.0.0
	 *
	 * @return array Array containing permissions for subsites.
	 */
	public static function get_permissions() {
		return array(
			'supercacher' => (int) get_site_option( 'siteground_optimizer_supercacher_permissions', 1 ),
			'frontend'    => (int) get_site_option( 'siteground_optimizer_frontend_permissions', 1 ),
			'images'      => (int) get_site_option( 'siteground_optimizer_images_permissions', 1 ),
			'environment' => (int) get_site_option( 'siteground_optimizer_environment_permissions', 0 ),
			'analytics'   => (int) get_site_option( 'siteground_optimizer_analytics_permissions', 1 ),
		);
	}

	/**
	 * Retrieve information about the optimization settings for each site.
	 *
	 * @since  5.0.0
	 *
	 * @return array Array containing data for each subsite.
	 */
	public function get_sites_info() {
		$sites_info = array();
		// Get all subsites.
		$sites = get_sites();

		// Loop through all sites and retrieve the data for each one.
		foreach ( $sites as $site ) {
			$site_info = array(
				'blog_id'               => $site->blog_id,
				'rest_url'              => get_rest_url( $site->blog_id, Rest::REST_NAMESPACE ),
				'site_url'              => get_site_url( $site->blog_id ),
				'supercacher'           => (int) $this->get_supercacher_status( $site->blog_id ),
				'forcessl'              => (int) get_blog_option( $site->blog_id, 'siteground_optimizer_fix_insecure_content', 0 ),
				'frontend_optimization' => (int) $this->get_frontend_optimization_status( $site->blog_id ),
				'images_optimization'   => (int) $this->get_images_optimization_status( $site->blog_id ),
			);

			// Push the site data to other sites data.
			array_push( $sites_info, $site_info );

		}

		// Finally return the sites data.
		return $sites_info;
	}

	/**
	 * Checks whether the cache settings are enabled for subsites.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True if the options are enabled, false otherwise.
	 */
	public function get_supercacher_status( $blog_id ) {
		if (
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_enable_cache', 0 ) &
			0 === (int) get_site_option( 'siteground_optimizer_enable_memcached', 0 )
		) {
			// All options are disabled.
			return 0;
		}

		// One or more options are enabled.
		return 1;
	}

	/**
	 * Check if the frontend optimization is enabled.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True if any of frontend optimizations is enabled, false otherwise.
	 */
	public function get_frontend_optimization_status( $blog_id ) {
		if (
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_optimize_html', 0 ) &&
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_optimize_javascript', 0 ) &&
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_optimize_javascript_async', 0 ) &&
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_optimize_css', 0 ) &&
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_combine_css', 0 ) &&
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_remove_query_strings', 0 ) &&
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_disable_emojis', 0 )
		) {
			return 0;
		}

		return 1;
	}

	/**
	 * Check if the image optimization is enabled.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True if any of image optimizations is enabled, false otherwise.
	 */
	public function get_images_optimization_status( $blog_id ) {
		if (
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_optimize_images', 0 ) &&
			0 === (int) get_blog_option( $blog_id, 'siteground_optimizer_lazyload_images', 0 )
		) {
			return 0;
		}

		return 1;
	}

	/**
	 * Disable cache optimization for blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function enable_supercacher_optimization( $blog_id ) {
		if (
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_enable_cache' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_autoflush_cache' )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Disable cache optimization for blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function disable_supercacher_optimization( $blog_id ) {
		if (
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_enable_cache' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_autoflush_cache' )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Enable ssl for blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function enable_forcessl_optimization( $blog_id ) {
		return Options::enable_mu_option( $blog_id, 'siteground_optimizer_fix_insecure_content' );
	}

	/**
	 * Enable ssl for blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function disable_forcessl_optimization( $blog_id ) {
		return Options::disable_mu_option( $blog_id, 'siteground_optimizer_fix_insecure_content' );
	}

	/**
	 * Enable frontend optimization for blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function enable_frontend_optimization( $blog_id ) {
		if (
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_optimize_html' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_optimize_javascript' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_optimize_css' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_remove_query_strings' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_disable_emojis' )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Disable frontend optimization for blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function disable_frontend_optimization( $blog_id ) {
		if (
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_optimize_html' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_optimize_javascript' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_optimize_css' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_remove_query_strings' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_disable_emojis' )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Enable images optimization for blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function enable_images_optimization( $blog_id ) {
		if (
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_optimize_images' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_lazyload_images' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_lazyload_gravatars' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_lazyload_thumbnails' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_lazyload_responsive' ) &&
			true === Options::enable_mu_option( $blog_id, 'siteground_optimizer_lazyload_textwidgets' )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Disable images optimization for blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $blog_id The blog id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function disable_images_optimization( $blog_id ) {
		if (
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_optimize_images' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_lazyload_images' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_lazyload_gravatars' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_lazyload_thumbnails' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_lazyload_responsive' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_lazyload_iframes' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_lazyload_woocommerce' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_lazyload_videos' ) &&
			true === Options::disable_mu_option( $blog_id, 'siteground_optimizer_lazyload_textwidgets' )
		) {
			return true;
		}

		return false;
	}
}
