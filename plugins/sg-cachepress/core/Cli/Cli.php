<?php
namespace SiteGround_Optimizer\Cli;

/**
 * SG CachePress Cli main plugin class
 */
class Cli {
	/**
	 * Init supercacher children.
	 *
	 * @since  5.0.0
	 */
	public function register_commands() {
		// Purge commands.
		\WP_CLI::add_command( 'sg purge', 'SiteGround_Optimizer\Cli\Cli_Purge' );

		// Memcache.
		\WP_CLI::add_command( 'sg memcached', 'SiteGround_Optimizer\Cli\Cli_Memcache' );

		// Optimize.
		\WP_CLI::add_command( 'sg optimize', 'SiteGround_Optimizer\Cli\Cli_Optimizer' );

		// HTTPS.
		\WP_CLI::add_command( 'sg forcehttps', 'SiteGround_Optimizer\Cli\Cli_Https' );

		// Status.
		\WP_CLI::add_command( 'sg status', 'SiteGround_Optimizer\Cli\Cli_Status' );

		// Heartbeat.
		\WP_CLI::add_command( 'sg heartbeat', 'SiteGround_Optimizer\Cli\Cli_Heartbeat' );

		\WP_CLI::add_command( 'sg images', 'SiteGround_Optimizer\Cli\Cli_Images' );

		// DNS Prefetch.
		\WP_CLI::add_command( 'sg dns-prefetch', 'SiteGround_Optimizer\Cli\Cli_DNS_Prefetch' );

		// Import/Export settings.
		\WP_CLI::add_command( 'sg settings', 'SiteGround_Optimizer\Cli\Cli_Settings' );

		// Database Optimizer.
		\WP_CLI::add_command( 'sg database-optimization', 'SiteGround_Optimizer\Cli\Cli_Database_Optimizer' );
	}

}
