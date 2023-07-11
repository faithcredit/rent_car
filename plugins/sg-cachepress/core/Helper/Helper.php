<?php
namespace SiteGround_Optimizer\Helper;

use SiteGround_Helper\Helper_Service;

/**
 * Helper functions and main initialization class.
 */
class Helper {

	/**
	 * The ajax actions that should bypass the update_queue ajax check.
	 *
	 * @var array
	 */
	public static $whitelisted_ajax_actions = array(
		'et_fb_ajax_save',
		'elementor_ajax',
	);

	/**
	 * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
	 *
	 * @since  5.9.0
	 *
	 * @return boolean
	 */
	public static function is_mobile() {
		if ( function_exists( 'wp_is_mobile' ) ) {
			return wp_is_mobile();
		}

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$is_mobile = false;
		} elseif ( @strpos( $_SERVER['HTTP_USER_AGENT'], 'Mobile' ) !== false // many mobile devices (all iPhone, iPad, etc.)
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Android' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Silk/' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Kindle' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'BlackBerry' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mini' ) !== false
			|| @strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mobi' ) !== false ) {
				$is_mobile = true;
		} else {
			$is_mobile = false;
		}

		return $is_mobile;
	}

	/**
	 * Checks if the page is being rendered via page builder.
	 *
	 * @since  5.9.0
	 *
	 * @return bool True/false.
	 */
	public static function check_for_builders() {

		$builder_paramas = apply_filters(
			'sgo_pb_params',
			array(
				'fl_builder',
				'vcv-action',
				'et_fb',
				'ct_builder',
				'tve',
				'preview',
				'elementor-preview',
				'uxb_iframe',
				'trp-edit-translation',
			)
		);

		foreach ( $builder_paramas as $param ) {
			if ( isset( $_GET[ $param ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the plugin is installed.
	 *
	 * @since  5.0.0
	 */
	public function is_plugin_installed() {
		if (
			isset( $_GET['sgCacheCheck'] ) &&
			md5( 'wpCheck' ) === $_GET['sgCacheCheck']
		) {
			die( 'OK' );
		}
	}

	/**
	 * Checks what are the upload dir permissions.
	 *
	 * @since  5.7.11
	 *
	 * @return boolean True/false
	 */
	public static function check_upload_dir_permissions() {
		// If the function does not exist the file permissions are correct.
		if ( ! function_exists( 'fileperms' ) ) {
			return true;
		}

		// Check if directory permissions are set accordingly.
		if ( 700 <= intval( substr( sprintf( '%o', fileperms( Helper_Service::get_uploads_dir() ) ), -3 ) ) ) {
			return true;
		}

		// Return false if permissions are below 700.
		return false;
	}

	/**
	 * Remove the https module from Site Heatlh, because our plugin provide the same functionality.
	 *
	 * @since  5.7.17
	 *
	 * @param  array $tests An associative array, where the $tests is either direct or async, to declare if the test should run via Ajax calls after page load.
	 *
	 * @return array        Tests with removed https_status module.
	 */
	public function sitehealth_remove_https_status( $tests ) {
		unset( $tests['async']['https_status'] );
		return $tests;
	}

	/**
	 * Get the current url.
	 *
	 * @since  7.0.0
	 *
	 * @return string The current url.
	 */
	public static function get_current_url() {
		// Return empty string if it is not an HTTP request.
		if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
			return '';
		}

		$protocol = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http'; // phpcs:ignore

		// Build the current url.
		return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; //phpcs:ignore
	}

	/**
	 * Check if the request is AJAX based and if it's whitelisted.
	 *
	 * @since  7.0.2
	 *
	 * @return bool The result of the check, true or false.
	 */
	public static function sg_doing_ajax() {
		// Check if the request is ajax-based.
		if ( ! wp_doing_ajax() ) {
			return false;
		}

		// Check if action is set and if it's set, check if it exists in the whitelist.
		if (
			empty( $_POST['action'] ) || // phpcs:ignore
			! empty( $_POST['action'] ) && ! in_array( $_POST['action'], Helper::$whitelisted_ajax_actions )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the passed content is xml.
	 *
	 * @since  7.0.2
	 *
	 * @param  string $content       The page content.
	 *
	 * @return bool   $run_xml_check Wheter the page xml sitemap.
	 */
	public static function is_xml( $content ) {
		// Get the first 200 chars of the file to make the preg_match check faster.
		$xml_part = substr( $content, 0, 20 );

		return preg_match( '/<\?xml version="/', $xml_part );
	}


	/**
	 * Get script handle by substring
	 *
	 * @since  7.1.0
	 *
	 * @param  string $regex           Substring that is searched for.
	 * @param  array  $scripts         Array of strings, containing all script handles.
	 *
	 * @return array  $matched_handles Array with all matching handles.
	 */
	public static function get_script_handle_regex( $regex, $scripts ) {
		// Bail if regex or scripts are empty.
		if ( empty( $regex ) || empty( $scripts ) ) {
			return array();
		}

		$matched_handles = array();

		// Go through all scripts and check for substring in each item.
		foreach ( $scripts as $handle ) {
			if ( false !== strpos( $handle, $regex ) ) {
				$matched_handles[] = $handle;
			}
		}

		return $matched_handles;
	}
}
