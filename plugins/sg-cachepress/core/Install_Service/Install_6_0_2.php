<?php
namespace SiteGround_Optimizer\Install_Service;
use SiteGround_Optimizer\Options\Options;

class Install_6_0_2 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 6.0.2
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '6.0.2';

	/**
	 * Run the install procedure.
	 *
	 * @since 6.0.2
	 */
	public function install() {
		if (
			false === get_option( 'siteground_optimizer_compression_level', false ) &&
			Options::is_enabled( 'siteground_optimizer_optimize_images' )
		) {
			update_option( 'siteground_optimizer_compression_level', 1 );
		}
	}
}