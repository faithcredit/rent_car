<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Options\Options;
/**
 * WP-CLI: wp sg memcached enable/disable.
 *
 * Run the `wp sg memcached enable/disable` command to enable/disable specific plugin functionality.
 *
 * @since 5.0.0
 * @package Cli
 * @subpackage Cli/Cli_Memcache
 */

/**
 * Define the {@link Cli_Memcache} class.
 *
 * @since 5.0.0
 */
class Cli_Memcache {
	/**
	 * Allow you to enable/disable memcached.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : The action: enable\disable.
     * ---
     * options:
     *   - enable
     *   - disable
     * ---
	 */
	public function __invoke( $args, $assoc_args ) {
		$memcache = new Memcache();

		if ( 'enable' === $args[0] ) {
			$port = $memcache->is_connection_working();

			if ( empty( $port ) ) {
				return \WP_CLI::error( 'SiteGround Optimizer was unable to connect to the Memcached server and it was disabled. Please, check your SiteGround control panel and turn it on if disabled.' );
			}

			// First enable the option.
			Options::enable_option( 'siteground_optimizer_enable_memcached' );

			// Send success if the dropin has been created.
			if ( $memcache->create_memcached_dropin() ) {
				return \WP_CLI::success( 'Memcached Enabled!' );
			}

			// Dropin cannot be created.
			return \WP_CLI::error( 'Could Not Enable Memcached!' );
		} else {
			// First disable the option.
			Options::disable_option( 'siteground_optimizer_enable_memcached' );

			// Send success if the option has been disabled and the dropin doesn't exist.
			if ( ! $memcache->dropin_exists() ) {
				return \WP_CLI::success( 'Memcached Disabled!' );
			}

			// Try to remove the dropin.
			$is_dropin_removed = $memcache->remove_memcached_dropin();

			// Send success if the droping has been removed.
			if ( $is_dropin_removed ) {
				return \WP_CLI::success( 'Memcached Disabled!' );
			}

			// The dropin cannot be removed.
			return \WP_CLI::error( 'Could Not Disable Memcached!' );
		}
	}
}
