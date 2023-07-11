<?php
namespace SiteGround_Optimizer\Rest;

use SiteGround_Optimizer\Ssl\Ssl;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Heartbeat_Control\Heartbeat_Control;
use SiteGround_Optimizer\File_Cacher\File_Cacher;

/**
 * Rest Helper class that manages enviroment optimisation settings.
 */
class Rest_Helper_Environment extends Rest_Helper {

	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $ssl;
	public $options;
	public $heartbeat_control;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->ssl               = new Ssl();
		$this->options           = new Options();
		$this->heartbeat_control = new Heartbeat_Control();
	}

	/**
	 * Enable/Disable HTTPS Enforce.
	 *
	 * @since 6.0.0
	 *
	 * @param object $request Request data.
	 */
	public function ssl( $request ) {
		// Validate rest request and prepare data.
		$data = $this->validate_rest_request( $request, array( 'ssl_enabled' ) );

		File_Cacher::get_instance()->refresh_config();

		0 === $data['value'] ? $this->ssl_disable() : $this->ssl_enable( $data );
	}

	/**
	 * Enable/Disable Fix Insecure Content.
	 *
	 * @since 6.0.0
	 *
	 * @param object $request Request data.
	 */
	public function fix_insecure_content( $request ) {
		// Data which will be included in the Json response.
		$response_data = array();

		// Validate rest request and prepare data.
		$data = $this->validate_rest_request( $request, array( 'fix_insecure_content' ) );

		// On Disable - disable Fix Insecure Content only.
		if ( 0 === $data['value'] ) {
			Options::disable_option( 'siteground_optimizer_fix_insecure_content' );

			self::send_json_success(
				self::get_response_message( true, 'fix_insecure_content', 0 ),
				array(
					'fix_insecure_content' => 0,
				)
			);
		}

		// On Enable - enable HTTPS Enforce if disabled.
		if ( ! Options::is_enabled( 'siteground_optimizer_enable_ssl' ) ) {
			if ( false === $this->ssl->enable() ) {
				self::send_json_error(
					self::get_response_message( false, 'ssl', 1 ),
					array(
						'fix_insecure_content' => 0,
						'ssl_enabled'           => 0,
					)
				);
			}

			$response_data['ssl_enabled'] = 1;
		}

		// Enable HTTPS Enforce.
		$result = Options::enable_option( 'siteground_optimizer_fix_insecure_content' );

		$response_data['fix_insecure_content'] = 1;

		// Send the response.
		self::send_json_response(
			$result,
			self::get_response_message( $result, 'fix_insecure_content', $data['value'] ),
			$response_data
		);
	}

	/**
	 * Enable/disable db optimization.
	 *
	 * @since  6.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function manage_database_optimization( $request ) {
		// Get the Selected list values.
		$selected = $this->validate_and_get_option_value( $request, 'selected' );
		$default  = $this->validate_and_get_option_value( $request, 'default' );

		// Get the previous state of the record.
		$previously_selected = get_option( 'siteground_optimizer_database_optimization', array() );

		// Update the option in the database.
		update_option( 'siteground_optimizer_database_optimization', $selected );

		// Remove the cron job.
		wp_clear_scheduled_hook( 'siteground_optimizer_database_optimization_cron' );

		// Enable the optimization.
		if ( ! empty( $selected ) ) {
			// Check if the event is currently runing.
			wp_schedule_event( time(), 'weekly', 'siteground_optimizer_database_optimization_cron' );
		}

		// Default message for enable/disable
		$message = 'database_optimization';
		$type    = empty( $selected ) ? 0 : 1;

		// Check if we need to modify the message.
		if (
			1 === $type &&
			! empty( $previously_selected ) && 
			count( $selected ) !== count( $previously_selected )
		) {
			// Modify for updated message.
			$message = 'database_optimization_updated';
			$type    = null;
		}

		// Send the response.
		self::send_json_success(
			self::get_response_message( true, $message, $type ),
			array(
				'default'  => $default,
				'selected' => array_values( $selected ),
			)
		);
	}

	/**
	 * Enable HTTPS Enforce.
	 *
	 * @since 6.0.0
	 *
	 * @param object $data Request data.
	 */
	public function ssl_enable( $data ) {
		// Bail if the domain does not have an SSL Certificate.
		if ( ! $this->ssl->has_certificate() ) {
			// Send the response.
			self::send_json_error(
				self::get_response_message( false, 'enable_ssl_no_certificate', null ),
				array(
					'ssl_enabled' => 0,
				)
			);
		}

		// Enable the option.
		$result = $this->ssl->enable();

		// Send the response.
		self::send_json_response(
			$result,
			self::get_response_message( $result, 'ssl', 1 ),
			array(
				'ssl_enabled' => 1 === intval( $result ) ? $data['value'] : intval( ! $data['value'] ),
			)
		);
	}

	/**
	 * Disable HTTPS Enforce.
	 *
	 * @since 6.0.0
	 */
	public function ssl_disable() {
		// Disable HTTPS Enforce.
		$result = $this->ssl->disable();

		if ( false === $result ) {
			self::send_json_error(
				self::get_response_message( false, 'ssl_enabled', 0 ),
				array(
					'ssl_enabled' => 1,
				)
			);
		}

		// Disable "Fix Insecure Content" option as well.
		Options::disable_option( 'siteground_optimizer_fix_insecure_content' );

		// Send the response.
		self::send_json_response(
			$result,
			self::get_response_message( $result, 'ssl', 0 ),
			array(
				'ssl_enabled'           => 0,
				'fix_insecure_content' => 0,
			)
		);
	}

	/**
	 * Change the Heartbeat Optimization interval.
	 *
	 * @since  6.0.0
	 *
	 * @param object $request Request data.
	 */
	public function manage_heartbeat_optimization( $request ) {
		$params = $request->get_params( $request );

		// Get the default and selected.
		$data = $this->validate_and_get_option_value( $request, 'selected' );

		// Update the option.
		update_option( 'siteground_optimizer_heartbeat_' . $params['location'] . '_interval', $data );

		// Invoke the setter so we get the freshly updated options.
		$this->heartbeat_control->set_intervals();

		// Send the response.
		self::send_json_success(
			__( 'WordPress Heartbeat Location Interval updated', 'sg-cachepress' ),
			array(
				'heartbeat_dropdowns' => $this->heartbeat_control->prepare_intervals(),
				'heartbeat_control'   => $this->heartbeat_control->is_enabled(),
			)
		);
	}
}
