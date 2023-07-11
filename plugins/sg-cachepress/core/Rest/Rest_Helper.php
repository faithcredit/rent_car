<?php
namespace SiteGround_Optimizer\Rest;

use SiteGround_Optimizer\Message_Service\Message_Service;
use SiteGround_Optimizer\Rest\Rest;
/**
 * Rest Helper class that process all rest requests and provide json output for react app.
 */
abstract class Rest_Helper {
	/**
	 * List of recommended optimizations
	 *
	 * @var array
	 */
	public $recommended_optimizations = array(
		'environment' => array(
			'ssl_enabled',
			'heartbeat_control',
			'database_optimization',
		),
		'frontend'    => array(
			'optimize_css',
			'combine_css',
			'optimize_javascript',
			'combine_javascript',
			'optimize_javascript_async',
			'optimize_html',
		),
		'media'       => array(
			'webp_support',
			'lazyload_images',
			'compression_level',
		),
		'caching'     => array(
			'enable_cache',
			'enable_memcached',
			'autoflush_cache',
			'file_caching',
		),
	);

	/**
	 * Checks if the `option_key` paramether exists in rest data.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 * @param  string $key     The option key.
	 * @param  bool   $bail    Whether to send json error or to return a response.
	 *
	 * @return string          The option value.
	 */
	public function validate_and_get_option_value( $request, $key, $bail = true ) {
		$data = json_decode( $request->get_body(), true );

		// Bail if the option key is not set.
		if ( ! isset( $data[ $key ] ) ) {
			return true === $bail ? self::send_json_error( __( 'Incorrect params used.', 'sg-cachepress' ) ) : false;
		}

		return $data[ $key ];
	}

	/**
	 * Response result check and return the respective json method.
	 *
	 * @since  6.0.0
	 *
	 * @param  bool   $result  True for success, false for failure.
	 * @param  string $message The response message.
	 * @param  array  $data    Additional data to be send.
	 */
	public function send_json_response( $result, $message = '', $data = array() ) {
		// Return json 400 error response on false.
		if ( false === boolval( $result ) ) {
			self::send_json_error( $message, $data );
		}

		// Return json 200 response on true.
		self::send_json_success( $message, $data );
	}

	/**
	 * Json 400 error response.
	 *
	 * @since  6.0.0
	 *
	 * @param  string $message The response message.
	 * @param  array  $data    Additional data to be send.
	 */
	public function send_json_error( $message = '', $data = array() ) {
		self::send_json( 400, $message, $data );
	}

	/**
	 * Json 200 success response.
	 *
	 * @since  6.0.0
	 *
	 * @param  string $message The response message.
	 * @param  array  $data    Additional data to be send.
	 */
	public function send_json_success( $message = '', $data = array() ) {
		self::send_json( 200, $message, $data );
	}

	/**
	 * Custom json response.
	 *
	 * @since  6.0.0
	 *
	 * @param  int    $status_code The status code.
	 * @param  string $message     The response message.
	 * @param  array  $data        Additional data to be send.
	 */
	public static function send_json( $status_code, $message = '', $data = array() ) {
		if ( ! headers_sent() ) {
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

			if ( null !== $status_code ) {
				status_header( $status_code );
			}
		}

		// Return status code.
		$response = array(
			'status' => $status_code,
			'data'   => $data,
		);

		// Return message only if it is not empty.
		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		echo wp_json_encode( $response );
		exit;
	}

	/**
	 * Prepare the response message for the plugin interface.
	 *
	 * @since  6.0.0
	 *
	 * @param  int    $result The result of the optimization.
	 * @param  string $option The option name.
	 * @param  bool   $type   True for enable, false for disable option.
	 *
	 * @return string         The response message.
	 */
	public function get_response_message( $result, $option, $type = '' ) {
		return Message_Service::get_response_message( $result, $option, $type );
	}

	/**
	 * Validate rest request and prepare data.
	 *
	 * @since  6.0.0
	 *
	 * @param  object $request Request data.
	 *
	 * @return array The prepared data.
	 */
	public function validate_rest_request( $request, $additional_arg = array() ) {
		$body       = json_decode( $request->get_body(), true );

		$is_network = $this->validate_and_get_option_value( $request, 'is_multisite', false );

		$key = key( $body );

		// Check if the data key is matching the rest route.
		if ( ! in_array( $key, array_merge( Rest::$toggle_options, $additional_arg ) ) ) {
			self::send_json_error();
		}

		return array(
			'key'    => $key,
			'value'  => intval( $body[ $key ] ),
			'option' => 'siteground_optimizer_' . $key,
		);
	}
}
