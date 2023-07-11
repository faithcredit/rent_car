<?php
namespace SiteGround_Optimizer\CDN;

use SiteGround_Optimizer\Site_Tools_Client\Site_Tools_Client;

/**
 * CDN class.
 */
class Cdn {
	/**
	 * Get the CDN type.
	 *
	 * @since 7.2.7
	 *
	 * @return array $result The CDN check result.
	 */
	public static function is_siteground_cdn() {
		// Prepare arguments.
		$args = array(
			'api'      => 'domain-all',
			'cmd'      => 'list',
			'params'   => (object) array(),
			'settings' => array(
				'json'        => 1,
				'show_fields' => array(
					'settings.cdn_enabled',
					'name',
				),
			),
		);

		// Connect to the socket.
		$result = Site_Tools_Client::call_site_tools_client( $args );

		// Bail if we do not get the result.
		if ( ! $result ) {
			return false;
		}

		// Loop the result and check if the CDN is enabled for the current site.
		foreach ( $result['json'] as $site ) {
			$matches = Site_Tools_Client::get_site_tools_matching_domain();

			if ( $matches[1] !== $site['name'] ) {
				continue;
			}

			if ( 1 === (int) $site['settings']['cdn_enabled'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if site is using CDN Premium.
	 *
	 * @since 7.2.7
	 *
	 * @return boolean If the site is using CDN Premium.
	 */
	public static function is_siteground_cdn_premium() {
		// Prepare the arguments.
		$args = array(
			'api'      => 'site',
			'cmd'      => 'list',
			'params'   => (object) array(),
			'settings' => array(
				'json'        => 1,
				'show_fields' => array(
					'features',
				),
			),
		);

		// Connect to the socket.
		$result = Site_Tools_Client::call_site_tools_client( $args );

		// Bail if we do not get the result.
		if ( ! $result ) {
			return false;
		}

		// Check if it is premium CDN.
		if ( isset( $result['json']['features']['cdn_cache_type'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the CDN Premium has pending status.
	 *
	 * @since 7.2.7
	 *
	 * @return boolean True or false if premium is pending.
	 */
	public static function is_siteground_cdn_premium_pending() {
		// Prepare arguments.
		$args = array(
			'api'      => 'domain-all',
			'cmd'      => 'list',
			'params'   => (object) array(),
			'settings' => array(
				'json'        => 1,
				'show_fields' => array(
					'settings.cdn_enabled',
					'name',
					'sg_cdn',
				),
			),
		);

		// Connect to the socket.
		$result = Site_Tools_Client::call_site_tools_client( $args );

		// Bail if we do not get the result.
		if ( ! $result ) {
			return false;
		}

		// Loop the result and check if the CDN is enabled for the current site.
		foreach ( $result['json'] as $site ) {
			$matches = Site_Tools_Client::get_site_tools_matching_domain();

			if ( $matches[1] !== $site['name'] ) {
				continue;
			}

			if (
				1 === (int) $site['settings']['cdn_enabled'] &&
				0 === (int) $site['sg_cdn']
			) {
				return true;
			}
		}

		return false;
	}
}
