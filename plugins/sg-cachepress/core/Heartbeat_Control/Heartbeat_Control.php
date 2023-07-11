<?php
namespace SiteGround_Optimizer\Heartbeat_Control;

/**
 * SG Heartbeat_Control main plugin class
 */
class Heartbeat_Control {

	/**
	 * Interval options
	 *
	 * @var array
	 */
	public $options;

	/**
	 * The default interval steps
	 */
	const INTERVAL_STEP = 30;

	/**
	 * The default interval limit.
	 */
	const INTERVAL_LIMIT = 120;

	/**
	 * The default intervals we set.
	 *
	 * @var array
	 */
	public $default_intervals = array(
		array(
			'label'    => 'Disabled',
			'value'    => 0,
			'selected' => 0,
		),
		array(
			'label'    => '15s',
			'value'    => 15,
			'selected' => 0,
		),
	);

	/**
	 * The Heartbeat Control Contructor
	 *
	 * @since  6.0.0
	 */
	public function __construct() {
		// Get the options status and interval and set them for usage.
		$this->set_intervals();
	}

	/**
	 * The intervals setter method.
	 *
	 * @since 6.0.0
	 */
	public function set_intervals() {
		$this->options = array(
			'post'      => array(
				'selected' => intval( get_option( 'siteground_optimizer_heartbeat_post_interval', 120 ) ),
				'default'  => 120,
			),
			'dashboard' => array(
				'selected' => get_option( 'siteground_optimizer_heartbeat_dashboard_interval', false ),
				'default'  => 0,
			),
			'frontend'  => array(
				'selected' => get_option( 'siteground_optimizer_heartbeat_frontend_interval', false ),
				'default'  => 0,
			),
		);
	}

	/**
	 * Check if the heartbeat is disabled for a specific location.
	 *
	 * @since  5.6.0
	 */
	public function maybe_disable() {
		foreach ( $this->options as $location => $interval_data ) {

			// Bail if the location doesn't match the specific location.
			if (
				$this->check_location( $location ) &&
				0 == $interval_data['selected'] &&
				false !== $interval_data['selected']
			) {
				// Deregiter the script.
				wp_deregister_script( 'heartbeat' );
				return;
			}
		}
	}

	/**
	 * Check if the heartbeat should be modified for specific location
	 *
	 * @since  5.6.0
	 *
	 * @param  array $settings Heartbeat settings array.
	 *
	 * @return array           Modified heartbeat settings array.
	 */
	public function maybe_modify( $settings ) {
		foreach ( $this->options as $location => $interval_data ) {
			// Bail if the location doesn't match the specific location.
			if (
				$this->check_location( $location ) &&
				1 < $interval_data['selected']
			) {
				// Change the interval.
				$settings['interval'] = intval( $interval_data['selected'] );

				// Return the modified settgins.
				return $settings;
			}
		}

		return $settings;
	}

	/**
	 * Prepare the interval options dropdown menu data.
	 *
	 * @since  6.0.0
	 *
	 * @return array $intervals The intervals and the user selected interval or default one.
	 */
	public function prepare_intervals() {
		$intervals = array();

		// Build the default intervals.
		$default_intervals = array_merge(
			$this->default_intervals,
			$this->build_default_dropdown()
		);

		// Loop trough the options and create the intervals dropdown data.
		foreach ( $this->options as $location => $interval_data ) {
			// Set the default intervals.
			$intervals[ $location ] = $default_intervals;

			if ( false !== $interval_data['selected'] ) {
				// Set the user defined interval or defaut ones for the selected option.
				$intervals[ $location ][ array_search( $interval_data['selected'], array_column( $intervals[ $location ], 'value' ) ) ]['selected'] = 1;
				$intervals[ $location ][ array_search( $interval_data['default'], array_column( $intervals[ $location ], 'value' ) ) ]['label'] .= ' - Recommended';
			}
		}

		return $intervals;
	}

	/**
	 * Loop and add the default intervals.
	 *
	 * @since  6.0.0
	 *
	 * @return array The default dropwdown values.
	 */
	public function build_default_dropdown() {
		$default_intervals = array();
		// Loop and add the additional intervals.
		for ( $i = self::INTERVAL_STEP; $i <= self::INTERVAL_LIMIT; $i += self::INTERVAL_STEP ) {

			// Merge the new intervals to the defailt ones.
			$default_intervals = array_merge(
				$default_intervals,
				array(
					array(
						'label'    => $i . 's',
						'value'    => $i,
						'selected' => 0,
					),
				)
			);
		}

		return $default_intervals;
	}

	/**
	 * Check the current location and if the heartbeat should be modified/disabled.
	 *
	 * @since  5.6.0
	 *
	 * @param  string $location The location id.
	 *
	 * @return bool             True if the heartbead should be modified/disabled for the specific location, false otherwise.
	 */
	public function check_location( $location ) {

		switch ( $location ) {
			case 'dashboard':
				return ( is_admin() && false === @strpos( $_SERVER['REQUEST_URI'], '/wp-admin/post.php' ) );
				break;

			case 'frontend':
				return ! is_admin();
				break;

			case 'post':
				return @strpos( $_SERVER['REQUEST_URI'], '/wp-admin/post.php' );
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Check if any of the heartbeat intervals are enabled.
	 *
	 * @since  6.0.0
	 *
	 * @return int 1/0
	 */
	public function is_enabled() {
		foreach ( $this->options as $location => $value ) {
			if ( false === $value || 0 === intval( $value ) ) {
				continue;
			}

			return 1;
		}

		return 0;
	}
}
