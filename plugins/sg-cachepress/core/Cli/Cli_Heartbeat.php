<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Options\Options;
/**
 * WP-CLI: wp sg heartbeat {setting} value.
 *
 * Run the `wp sg heartbeat {setting} {option} {frequency}` command to change the settgins of specific plugin functionality.
 *
 * @since 5.6.1
 * @package Cli
 * @subpackage Cli/Heartbeat
 */

/**
 * Define the {@link Cli_Heartbeat} class.
 *
 * @since 5.6.1
 */
class Cli_Heartbeat {
	/**
	 * Enable specific setting for SiteGround Optimizer plugin.
	 *
	 * ## OPTIONS
	 *
	 * <location>
	 * : Setting name.
	 * ---
	 * options:
	 *  - frontend
	 *  - dashboard
	 *  - post
	 * ---
	 * <frequency>
	 * : Frequency for the Heartbeat.
	 * ---
	 * options:
	 *  - 0
	 *  - 15
	 *  - 30
	 *  - 60
	 *  - 90
	 *  - 120
	 */
	public function __invoke( $args ) {
		// Set location based on cli command.
		$interval_option = 'siteground_optimizer_heartbeat_' . $args[0] . '_interval';

		// Set the interval frequency.
		update_option( $interval_option, $args[1] );

		\WP_CLI::success( 'Heartbeat optimization interval for ' . $args[0] . ' was set successfully.' );
	}
}
