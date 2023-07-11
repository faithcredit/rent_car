<?php
namespace SiteGround_Optimizer\Install_Service;

class Install_5_3_4 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.3.4
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.3.4';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.3.4
	 */
	public function install() {
		$new_handles = array(
			'jquery-core',
			'jquery-migrate',
		);

		// Get the async excludes.
		$handles = get_option( 'siteground_optimizer_async_javascript_exclude', array() );

		foreach ( $new_handles as $handle ) {
			// Check if the handle already exists in the exclude list.
			$key = array_search( $handle, $handles );

			// Bail if the handle exists.
			if ( in_array( $handle, $handles ) ) {
				return;
			}

			// Add the new handle to exclude.
			array_push( $handles, $handle );
		}

		$handles = array_values( $handles );

		// Update the option.
		$result = update_option( 'siteground_optimizer_async_javascript_exclude', $handles );
	}

}