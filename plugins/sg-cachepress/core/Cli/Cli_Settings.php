<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Settings\Settings;
use SiteGround_Optimizer\Options\Options;

/**
 * WP-CLI: wp sg settings {setting} value.
 *
 * Run the `wp sg settings {type}` command to change the settgins of specific plugin functionality.
 *
 * @since 5.7.13
 * @package Cli
 * @subpackage Cli/Settings
 */

/**
 * Define the {@link Cli_Settings} class.
 *
 * @since 5.7.13
 */
class Cli_Settings {
	/**
	 * Enable specific setting for SiteGround Optimizer plugin.
	 *
	 * ## OPTIONS
	 *
	 * options:
	 *  - import
	 *  - export
	 * ---
	 * <action>
	 * : Setting name.
	 * [--hash=<string>]
	 * : Settings hash.
	 */
	public function __invoke( $args, $assoc_args ) {
		// Call the Settings class.
		$this->settings_service = new Settings();
		$this->options_service  = new Options();

		// Check the type of operation.
		if ( 'export' === $args[0] ) {
			// Start the export.
			return $this->export();
		}

		// Check if we have the import string.
		if ( empty( $assoc_args['hash'] ) ) {
			\WP_CLI::error( 'Please, use the import command with a `hash` parameter - wp sg settings ' . $args[0] . ' --hash=<The hash string.>' );
		}

		// Start the import.
		return $this->import( $assoc_args['hash'] );
	}

	/**
	 * Call the Import Settings method
	 *
	 * @since  5.7.13
	 *
	 * @param  string $hash_import The hash string.
	 *
	 * @return array               Options that were updated.
	 */
	public function import( $hash_import ) {
		// Start the import.
		$result = $this->settings_service->import( $hash_import );

		// Check if we have a valid response from the import.
		if ( false === $result ) {
			return \WP_CLI::error( 'The import was unsuccessful, please make sure your hash is correct.' );
		}


		if ( ! empty( $result ) ) {
			\WP_CLI::error( 'We\'ve imported everything except the following settings: ' . implode( $result, ', ' ) );
		}

		return \WP_CLI::success( 'Import Completed.' );
	}

	/**
	 * Call the Export Settings method.
	 *
	 * @since  5.7.13
	 *
	 * @return string The filepath or the export string.
	 */
	public function export() {
		// Start the export.
		$result = $this->settings_service->export();

		// Check if we have a valid response from the export.
		if ( ! $result ) {
			return \WP_CLI::error( 'The export was unsuccessful, please try again.' );
		}

		// Return the export string.
		return \WP_CLI::success( "The export is completed, please use the following string:\n" . $result );
	}
}
