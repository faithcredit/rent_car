<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Options\Options;

class Install_5_0_10 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.0.10
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.0.10';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.0.10
	 */
	public function install() {
		$exclude_list = get_option( 'siteground_optimizer_excluded_urls', array() );

		// Bail ifthe exclude list is empty.
		if ( empty( $exclude_list ) ) {
			return;
		}

		// Add slash before each url part.
		$new_exclude_list = array_map(
			function( $item ) {
				if ( substr( $item, 0, 1 ) !== '/' ) {
					$item = '/' . $item;
				}
				return $item;
			}, $exclude_list
		);

		// Update the exclude list.
		update_option( 'siteground_optimizer_excluded_urls', $new_exclude_list );

	}

}