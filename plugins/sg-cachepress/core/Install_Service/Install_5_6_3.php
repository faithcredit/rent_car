<?php
namespace SiteGround_Optimizer\Install_Service;

class Install_5_6_3 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.6.3
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.6.3';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.6.3
	 */
	public function install() {
		// Get the async excludes.
		$handles = get_option( 'siteground_optimizer_async_javascript_exclude', array() );

		if ( ! in_array( 'jquery', $handles ) ) {
			// Add the new handle to exclude.
			array_push( $handles, 'jquery' );
		}

		$handles = array_values( $handles );

		// Update the option.
		$result = update_option( 'siteground_optimizer_async_javascript_exclude', $handles );
	}

}