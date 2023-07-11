<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Images_Optimizer\Images_Optimizer;
/**
 * WP-CLI: wp sg images value.
 *
 * Run the `wp sg images {compression-level} {frequency}` command to change the settgins of specific plugin functionality.
 *
 * @since 6.0.0
 * @package Cli
 * @subpackage Cli/Images
 */

/**
 * Define the {@link Cli_Images} class.
 *
 * @since 6.0.0
 */
class Cli_Images {
	/**
	 * Enable specific setting for SiteGround Optimizer plugin.
	 *
	 * ## OPTIONS
	 *
	 * [--compression-level=<compression_level>]
	 * : Compression Level of the image optimization.
	 * ---
	 * options:
	 *  - 0
	 *  - 1
	 *  - 2
     *  - 3
	 */
	public function __invoke( $args, $assoc_args ) {
        $possible_entries = array( 1, 2, 3 );
		if ( ! isset ( $assoc_args['compression-level'] ) || ! in_array( intval( $assoc_args['compression-level'] ), $possible_entries ) ) {
			return \WP_CLI::error( 'Please specify the compression level' );
		}
		$image_optimizer  = new Images_Optimizer();

		\WP_CLI::log( 'Start Image Optimization');

		// Add empty line.
		\WP_CLI::log( '' );

		update_option( 'siteground_optimizer_compression_level_old', get_option( 'siteground_optimizer_compression_level', 1 ) );
		update_option( 'siteground_optimizer_compression_level', $assoc_args['compression-level'] );

		$image_optimizer->reset_image_optimization_status();

		// Init the optimization.
		$image_optimizer->initialize();

		return \WP_CLI::success( 'Images optimization completed.' );
	}
}
