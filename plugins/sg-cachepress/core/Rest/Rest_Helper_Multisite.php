<?php
namespace SiteGround_Optimizer\Rest;

use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\Multisite\Multisite;

/**
 * Rest Helper class that manages multisite settings.
 */
class Rest_Helper_Multisite {
	/**
	 * Local variables
	 *
	 * @var Multisite
	 */
	public $multisite;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->multisite = new Multisite();
	}

	/**
	 * Enable specific optimizations for a blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function enable_multisite_optimization( $request ) {
		$setting = $this->validate_and_get_option_value( $request, 'setting' );
		$blog_id = $this->validate_and_get_option_value( $request, 'blog_id' );

		foreach ( $blog_id as $id ) {
			$result = call_user_func( array( $this->multisite, $setting ), $id );
		}

		// Purge the cache.
		Supercacher::purge_cache();

		// Send the response.
		self::send_json_response(
			$result,
			'',
			array(
				'success' => $result,
			)
		);
	}

	/**
	 * Disable specific optimizations for a blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function disable_multisite_optimization( $request ) {
		$setting = $this->validate_and_get_option_value( $request, 'setting' );
		$blog_id = $this->validate_and_get_option_value( $request, 'blog_id' );

		foreach ( $blog_id as $id ) {
			$result = call_user_func( array( $this->multisite, $setting ), $id );
		}

		// Purge the cache.
		Supercacher::purge_cache();

		// Send the response.
		self::send_json_response(
			$result,
			'',
			array(
				'success' => $result,
			)
		);
	}
}