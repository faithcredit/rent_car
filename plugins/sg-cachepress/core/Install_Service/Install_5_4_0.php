<?php
namespace SiteGround_Optimizer\Install_Service;

class Install_5_4_0 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.4.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.4.0';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.4.0
	 */
	public function install() {

		$lazyload_classes = get_option( 'siteground_optimizer_excluded_lazy_load_classes', array() );

		update_option( 'siteground_optimizer_excluded_lazy_load_classes', array_merge( $lazyload_classes, array( 'skip-lazy' ) ) );
	}

}