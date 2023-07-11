<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Message_Service\Message_Service;
use SiteGround_Optimizer\File_Cacher\File_Cacher;

/**
 * WP-CLI: wp sg optimize {option} enable/disable.
 *
 * Run the `wp sg optimize {option} enable/disable` command to enable/disable specific plugin functionality.
 *
 * @since 5.0.0
 * @package Cli
 * @subpackage Cli/Cli_Optimizer
 */

/**
 * Define the {@link Cli_Optimizer} class.
 *
 * @since 5.0.0
 */
class Cli_Optimizer {
	/**
	 * Enable specific optimization for SiteGround Optimizer plugin.
	 *
	 * ## OPTIONS
	 *
	 * <optimization>
	 * : Optimization name.
	 * ---
	 * options:
	 *  - dynamic-cache
	 *  - file-cache
	 *  - autoflush-cache
	 *  - purge-rest-cache
	 *  - mobile-cache
	 *  - html
	 *  - js
	 *  - js-async
	 *  - combine-js
	 *  - css
	 *  - combine-css
	 *  - querystring
	 *  - emojis
	 *  - backup-media
	 *  - lazyload
	 *  - webp
	 *  - resize-images
	 *  - web-fonts
	 *  - fix-insecure-content
	 *  - preload-combined-css
	 * ---
	 * <action>
	 * : The action: enable\disable.
	 * Whether to enable or disable the optimization.
	 *
	 * [--blog_id=<blog_id>]
	 * : Blod id for multisite optimizations
	 */
	public function __invoke( $args, $assoc_args ) {
		$this->option_service   = new Options();
		$this->htaccess_service = new Htaccess();

		$blog_id = ! empty( $assoc_args['blog_id'] ) ? $assoc_args['blog_id'] : false;

		switch ( $args[0] ) {
			case 'dynamic-cache':
			case 'autoflush-cache':
			case 'purge-rest-cache':
			case 'html':
			case 'js':
			case 'css':
			case 'querystring':
			case 'emojis':
			case 'js-async':
			case 'combine-js':
			case 'combine-css':
			case 'web-fonts':
			case 'webp':
			case 'backup-media':
			case 'resize-images':
			case 'fix-insecure-content':
			case 'lazyload':
			case 'preload-combined-css':
				return $this->optimize( $args[1], $args[0], $blog_id );
			case 'mobile-cache':
				return $this->optimize_mobile_cache( $args[1] );
			case 'file-cache':
				return $this->optimize_file_cache( $args[1] );
		}
	}

	public function validate_multisite( $option, $blog_id = false ) {
		if (
			! \is_multisite() &&
			false !== $blog_id
		) {
			\WP_CLI::error( 'Blog id should be passed to multisite setup only!' );
		}

		if (
			\is_multisite() &&
			false === $blog_id
		) {
			\WP_CLI::error( "Blog id is required for optimizing $option on multisite setup!" );
		}

		if ( function_exists( 'get_sites' ) ) {
			$site = \get_sites( array( 'site__in' => $blog_id ) );

			if ( empty( $site ) ) {
				\WP_CLI::error( 'There is no existing site with id: ' . $blog_id );
			}
		}
	}

	public function optimize( $action, $option, $blog_id = false ) {

		$this->validate_multisite( $option, $blog_id );

		$mapping = array(
			'dynamic-cache'        => 'siteground_optimizer_enable_cache',
			'autoflush-cache'      => 'siteground_optimizer_autoflush_cache',
			'purge-rest-cache'     => 'siteground_optimizer_purge_rest_cache',
			'mobile-cache'         => 'siteground_optimizer_user_agent_header',
			'html'                 => 'siteground_optimizer_optimize_html',
			'js'                   => 'siteground_optimizer_optimize_javascript',
			'js-async'             => 'siteground_optimizer_optimize_javascript_async',
			'css'                  => 'siteground_optimizer_optimize_css',
			'combine-css'          => 'siteground_optimizer_combine_css',
			'web-fonts'            => 'siteground_optimizer_optimize_web_fonts',
			'combine-js'           => 'siteground_optimizer_combine_javascript',
			'querystring'          => 'siteground_optimizer_remove_query_strings',
			'emojis'               => 'siteground_optimizer_disable_emojis',
			'backup-media'         => 'siteground_optimizer_backup_media',
			'webp'                 => 'siteground_optimizer_webp_support',
			'resize-images'        => 'siteground_optimizer_resize_images',
			'fix-insecure-content' => 'siteground_optimizer_fix_insecure_content',
			'preload-combined-css' => 'siteground_optimizer_preload_combined_css',
			'lazyload'             => 'siteground_optimizer_lazyload_images',
		);

		switch ( $action ) {
			case 'enable':
				if ( false === $blog_id ) {
					$result = $this->option_service::enable_option( $mapping[ $option ] );
				} else {
					$result = $this->option_service::enable_mu_option( $blog_id, $mapping[ $option ] );
				}
				$type = 1;
				break;

			case 'disable':
				if ( false === $blog_id ) {
					$result = $this->option_service::disable_option( $mapping[ $option ] );
				} else {
					$result = $this->option_service::disable_mu_option( $blog_id, $mapping[ $option ] );
				}

				$type = 0;
				break;
		}

		if ( ! isset( $result ) ) {
			\WP_CLI::error( 'Please specify action' );
		}

		$message = Message_Service::get_response_message( $result, str_replace( 'siteground_optimizer_', '', $mapping[ $option ] ), $type );

		return true === $result ? \WP_CLI::success( $message ) : \WP_CLI::error( $message );

	}

	public function optimize_mobile_cache( $action ) {
		if ( 'enable' === $action ) {
			$result = $this->htaccess_service->disable( 'user-agent-vary' );
			true === $result ? Options::enable_option( 'siteground_optimizer_user_agent_header' ) : '';
			$type = true;
		} else {
			$result = $this->htaccess_service->enable( 'user-agent-vary' );
			true === $result ? Options::disable_option( 'siteground_optimizer_user_agent_header' ) : '';
			$type = false;
		}

		$message = Message_Service::get_response_message( $result, 'user_agent_header', $type );

		return true === $result ? \WP_CLI::success( $message ) : \WP_CLI::error( $message );
	}

	/**
	 * Enable/Disable File Caching feature.
	 *
	 * @since  7.0.0
	 *
	 * @param  string $action String, containing the action that is to be used, "enable" or "disable"
	 *
	 * @return void
	 */
	public function optimize_file_cache( $action ) {
			// Check if the option should be enabled or disabled.
			$value = 'enable' === $action ? 1 : 0;

			// Invoke managment method and try disabling/enabling the option.
			$result = File_Cacher::toggle_file_cache( $value );

			// Get the correct message for the user based on the result.
			$message = Message_Service::get_response_message( $result['status'], 'file_caching', $value );

			return true === $result['status'] ? \WP_CLI::success( $message ) : \WP_CLI::error( $message );
	}
}
