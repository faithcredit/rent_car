<?php
namespace SG_Security\Rest;

use SG_Security;
use SiteGround_Helper\Helper_Service;

/**
 * Rest Helper class that manages the plugin dashboard.
 */
class Rest_Helper_Dashboard extends Rest_Helper {

	/**
	 * Sends notifications info.
	 *
	 * @since  1.0.0
	 */
	public function notifications() {
		// Prepare the response array.
		$response = array();

		// Add notification if we have updates available.
		if ( true === Helper_Service::has_updates() ) {
			$response = array(
				array(
					'title'       => __( 'YOUR WORDPRESS NEEDS ATTENTION', 'sg-security' ),
					'text'        => __( 'There are new updates for your website. Keeping your WordPress updated is crucial for your website security', 'sg-security' ),
					'button_text' => __( 'Update', 'sg-security' ),
					'button_link' => admin_url( 'update-core.php' ),
				),
			);
		}

		// Send the response.
		return self::send_response(
			'',
			1,
			$response
		);
	}

	/**
	 * Send information about the security hardening boxes.
	 *
	 * @since  1.0.0
	 */
	public function hardening() {
		return self::send_response(
			'',
			1,
			array(
				array(
					'icon'        => 'product-ssl-wildcard',
					'icon_color'  => 'royal',
					'text'        => __( 'Set up rules to harden your website security and prevent malware, bruteforce and other security issues.', 'sg-security' ),
					'button_text' => __( 'Manage Security', 'sg-security' ),
					'button_link' => admin_url( 'admin.php?page=site-security' ),
					'title'       => __( 'Site Security', 'sg-security' ),
				),
				array(
					'icon'        => 'product-ssl-encryption',
					'icon_color'  => 'grassy',
					'text'        => __( 'Protect your login from unauthorised visitors, bots and other human or automated attacks.', 'sg-security' ),
					'button_text' => __( 'Manage Login', 'sg-security' ),
					'button_link' => admin_url( 'admin.php?page=login-settings' ),
					'title'       => __( 'Login Security', 'sg-security' ),
				),
			)
		);
	}

	/**
	 * Sends information about the free ebook.
	 *
	 * @since  1.0.2
	 */
	public function ebook() {
		return self::send_response(
			'',
			1,
			$this->get_remote_banners()
		);
	}

	/**
	 * Sends information whether we should display the rating box
	 *
	 * @since  1.0.2
	 *
	 * @param  object $request Request data.
	 */
	public function rate( $request ) {
		$show = $this->validate_and_get_option_value( $request, 'show', false );

		if ( false === $show ) {
			return self::send_response(
				'',
				1,
				array(
					'show' => intval( get_option( 'sg_security_show_rating', 1 ) ),
				)
			);
		}

		update_option( 'sg_security_show_rating', $show );

		return self::send_response(
			'',
			1,
			array(
				'show' => intval( $show ),
			)
		);
	}

	/**
	 * Make a remote request to fetch the banner assets and texts in the dashboard.
	 *
	 * @since  1.2.0
	 *
	 * @return array $result The array containing the link and titles.
	 */
	public function get_remote_banners() {
		// Get the banner content.
		$response = wp_remote_get( 'https://sgwpdemo.com/jsons/sg-security-banners.json' );

		// Bail if the request fails.
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return self::send_response( 'Error fetching banners', 0, array() );
		}

		// Get the locale.
		$locale = get_locale();

		// Determine the type of asset we are going to show.
		$type = Helper_Service::is_siteground() ? 'ebook' : 'banners';

		// Get the body of the response.
		$body = wp_remote_retrieve_body( $response );

		// Decode the json response.
		$banners = json_decode( $body, true );

		// Return the correct assets, title and marketing urls.
		return array_key_exists( $locale, $banners[ $type ] ) ? $banners[ $type ][ $locale ] : $banners[ $type ]['default'];
	}
}
