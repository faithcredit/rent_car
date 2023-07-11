<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Ssl\Ssl;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Message_Service\Message_Service;
/**
 * WP-CLI: wp sg memcached enable/disable.
 *
 * Run the `wp sg memcached enable/disable` command to enable/disable specific plugin functionality.
 *
 * @since 5.0.0
 * @package Cli
 * @subpackage Cli/Cli_Https
 */

/**
 * Define the {@link Cli_Https} class.
 *
 * @since 5.0.0
 */
class Cli_Https {
	/**
	 * Allow you to enable/disable https.
	 *
	 * ## OPTIONS
	 * <action>
	 * : The action: enable\disable.
	 * Whether to enable or disable the https.
	 */
	public function __invoke( $args, $assoc_args ) {
		$this->ssl            = new Ssl();
		$this->option_service = new Options();

		if ( empty( $args[0] ) ) {
			return \WP_CLI::error( 'Please provide action: enable/disable or add the subcommand `fix`' );
		}

		switch ( $args[0] ) {
			case 'enable':
				$result = $this->ssl->enable();
				true === $result ? Options::enable_option( 'siteground_optimizer_ssl_enabled' ) : '';
				$type = 1;
				break;

			case 'disable':
				$result = $this->ssl->disable();
				true === $result ? Options::disable_option( 'siteground_optimizer_ssl_enabled' ) : '';
				$type = 0;
				break;
			default:
				\WP_CLI::error( 'Please specify action' );
				break;
		}

		$message = Message_Service::get_response_message( $result, 'ssl', $type );

		return true === $result ? \WP_CLI::success( $message ) : \WP_CLI::error( $message );
	}
}
