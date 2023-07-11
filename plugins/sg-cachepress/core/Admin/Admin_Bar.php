<?php
namespace SiteGround_Optimizer\Admin;

use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\File_Cacher\File_Cacher;
use SiteGround_Optimizer\Options\Options;

/**
 * Add purge button functionality to admin bar.
 */
class Admin_Bar {

	/**
	 * Checks the current user capabilities.
	 *
	 * @since  5.8.3
	 *
	 * @return True/False.
	 */
	public function check_capabilities() {
		// Merged capabilities.
		$default_capabilities = array_merge(
			// Adding the capabilities added by the filter.
			apply_filters(
				'sgo_purge_button_capabilities',
				array()
			),
			// Adding administrator as a default capability.
			array(
				'manage_options',
			)
		);

		// Check if the current user have a capability to access the "Purge SG Cache" button.
		foreach ( $default_capabilities as $cap ) {
			// Return true if the user has any of the caps.
			if ( current_user_can( $cap ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Adds a purge buttion in the admin bar menu.
	 *
	 * @param (WP_Admin_Bar) $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @since 5.0.0
	 */
	public function add_admin_bar_purge( $wp_admin_bar ) {
		// Bail if user does not have capabilities.
		if ( ! $this->check_capabilities() ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'SG_CachePress_Supercacher_Purge',
				'title' => __( 'Purge SG Cache', 'sg-cachepress' ),
				'href'  => wp_nonce_url( admin_url( 'admin-ajax.php?action=admin_bar_purge_cache' ), 'sg-cachepress-purge' ),
				'meta'  => array( 'class' => 'sg-cachepress-admin-bar-purge' ),
			)
		);

	}

	/**
	 * Purges the cache and redirects to referrer (admin bar button)
	 *
	 * @since 5.0.0
	 */
	public function purge_cache() {
		// Bail if the nonce is not set.
		if ( empty( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'sg-cachepress-purge' ) ) {
			return;
		}

		Supercacher::purge_cache();
		Supercacher::flush_memcache();
		Supercacher::delete_assets();

		// Flush File-Based cache if enabled.
		if ( Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
			File_Cacher::get_instance()->purge_everything();
		}

		wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
		exit;
	}
}
