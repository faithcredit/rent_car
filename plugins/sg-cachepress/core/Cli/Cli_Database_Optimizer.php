<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Options\Options;

/**
 * WP-CLI: wp sg database-optimization {setting} value.
 *
 * Run the `wp sg database-optimization {setting}` to modify the database optimization settings.
 *
 * @since 7.2.2
 * @package Cli
 * @subpackage Cli/Database_Optimizer
 */

/**
 * Define the {@link Cli_Database_Optimizer} class.
 *
 * @since 7.2.2
 */
class Cli_Database_Optimizer {
	/**
	 * Enable specific setting for SiteGround Optimizer plugin.
	 *
	 * ## OPTIONS
	 *
	 * options:
	 *  - enable
	 *  - disable
	 *  - status
	 *  - update
	 * ---
	 * <action>
	 * : Setting name.
	 * [--options=<string>]
	 * : Settings hash.
	 */
	public function __invoke( $args, $assoc_args ) {
		$this->options_service = new Options();

		// Check the type of operation.
		if ( 'enable' === $args[0] ) {
			// Enable all optimizations.
			return $this->enable_all();
		}

		if ( 'disable' === $args[0] ) {
			// Disable the optimization.
			return $this->disable_all();
		}

		if ( 'status' === $args[0] ){
			// Return the optimization status.
			return $this->get_status();
		}

		// Check if we have the import string.
		if ( empty( $assoc_args['options'] ) ) {
			\WP_CLI::error( 'Please, use the update command with a `options` parameter - wp sg database-optimization ' . $args[0] . ' --options=options,separated,with,comma' );
		}

		// Start the import.
		return $this->update_methods( $assoc_args['options'] );
	}

	/**
	 * Prepare the accepted methods and their titles.
	 *
	 * @since  7.2.2
	 *
	 * @return array $options Array containing all accepted methods and their titles.
	 */
	public function prepare_methods() {
		// Get the database supported options and the titles.
		$methods_data = $this->options_service->get_database_optimization_defaults();

		$options = array();

		// Loop and prepare methods for db update and the table for printing the message.
		foreach ( $methods_data as $method ) {
			$options[ $method['value'] ] = $method['title'];
		}

		return $options;
	}

	/**
	 * Enable all database optimizations.
	 *
	 * @since  7.2.2
	 *
	 * @return  WP_CLI Success.
	 */
	public function enable_all() {
		$methods = $this->prepare_methods();

		// Check if there is a scheduled event.
		if ( ! wp_next_scheduled( 'siteground_optimizer_database_optimization_cron' ) ) {
			// Set the event if it is not running.
			$response = wp_schedule_event( time(), 'weekly', 'siteground_optimizer_database_optimization_cron' );
		}

		// Update the selected options in the database.
		update_option( 'siteground_optimizer_database_optimization', array_keys( $methods ) );

		$table_data = array();

		foreach ( $methods as $method ) {
			$table_data[] = array(
				'Enabled Optimizations' => $method,
			);
		}

		// Print the table.
		\WP_CLI\Utils\format_items( 'table', $table_data, array( 'Enabled Optimizations' ) );

		// Return the success message.
		return \WP_CLI::success( 'Database Optimization Enabled.' );
	}

	/**
	 * Disable all database optimizations.
	 *
	 * @since  7.2.2
	 *
	 * @return  WP_CLI Success.
	 */
	public function disable_all() {
		$methods = $this->prepare_methods();

		// Remove the scheduled event.
		wp_clear_scheduled_hook( 'siteground_optimizer_database_optimization_cron' );

		// Update the selected options in the database.
		update_option( 'siteground_optimizer_database_optimization', array() );

		$table_data = array();

		foreach ( $methods as $method ) {
			$table_data[] = array(
				'Disabled Optimizations' => $method,
			);
		}

		// Print the table.
		\WP_CLI\Utils\format_items( 'table', $table_data, array( 'Disabled Optimizations' ) );

		// Return the success message.
		return \WP_CLI::success( 'Database Optimization Disabled.' );
	}

	/**
	 * Get the current optimization status
	 *
	 * @since  7.2.2
	 *
	 * @return WP_CLI Success.
	 */
	public function get_status() {
		$methods = $this->prepare_methods();

		$current = get_option( 'siteground_optimizer_database_optimization', array() );

		if ( empty( $current ) ) {
			return \WP_CLI::success( 'Database Optimization is currently Disabled.' );
		}

		$table_data = array();

		foreach ( $current as $method ) {
			$table_data[] = array(
				'Currently Active Database Optimizations' => $methods[ $method ],
			);
		}

		// Print the table.
		\WP_CLI\Utils\format_items( 'table', $table_data, array( 'Currently Active Database Optimizations' ) );

		return \WP_CLI::success( 'Database Optimization is Enabled.' );
	}

	/**
	 * Update specific optimizations only.
	 *
	 * @since  7.2.2
	 *
	 * @param  string $args String containing all optimization methods set by the user.
	 *
	 * @return object  WP_CLI success or error message.
	 */
	public function update_methods( $args ) {
		$methods = $this->prepare_methods();

		$options = explode( ',', $args );

		$sanitized_methods = array();
		$table_data        = array();

		// Loop the user options.
		foreach ( $options as $option ) {
			// Add them to the sanitized array if they are allowed.
			if ( array_key_exists( $option, $methods ) ) {
				// Add the method to the sanitized array.
				$sanitized_methods[] = $option;

				// Add the optimization name to the table array.
				$table_data[] = array(
					'Updated Optimizations' => $methods[ $option ],
				);
			}
		}

		// Bail if the sanitized array is empty.
		if ( empty( $sanitized_methods ) ) {
			return \WP_CLI::error( 'Non-supported database optimizations.' );
		}

		// Check if there is a scheduled event.
		if ( ! wp_next_scheduled( 'siteground_optimizer_database_optimization_cron' ) ) {
			// Set the event if it is not running.
			$response = wp_schedule_event( time(), 'weekly', 'siteground_optimizer_database_optimization_cron' );
		}

		// Update the selected options in the database.
		update_option( 'siteground_optimizer_database_optimization', $sanitized_methods );

		\WP_CLI\Utils\format_items( 'table', $table_data, array( 'Updated Optimizations' ) );

		// Return the success message.
		return \WP_CLI::success( 'Database Optimization Updated.' );
	}
}
