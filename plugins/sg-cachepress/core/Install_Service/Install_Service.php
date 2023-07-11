<?php

namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Install_Service\Install_5_0_0;
use SiteGround_Optimizer\Install_Service\Install_5_0_5;
use SiteGround_Optimizer\Install_Service\Install_5_0_6;
use SiteGround_Optimizer\Install_Service\Install_5_0_8;
use SiteGround_Optimizer\Install_Service\Install_5_0_9;
use SiteGround_Optimizer\Install_Service\Install_5_0_10;
use SiteGround_Optimizer\Install_Service\Install_5_0_12;
use SiteGround_Optimizer\Install_Service\Install_5_0_13;
use SiteGround_Optimizer\Install_Service\Install_5_2_0;
use SiteGround_Optimizer\Install_Service\Install_5_2_5;
use SiteGround_Optimizer\Install_Service\Install_5_3_0;
use SiteGround_Optimizer\Install_Service\Install_5_3_1;
use SiteGround_Optimizer\Install_Service\Install_5_3_2;
use SiteGround_Optimizer\Install_Service\Install_5_3_4;
use SiteGround_Optimizer\Install_Service\Install_5_3_6;
use SiteGround_Optimizer\Install_Service\Install_5_3_10;
use SiteGround_Optimizer\Install_Service\Install_5_4_0;
use SiteGround_Optimizer\Install_Service\Install_5_4_3;
use SiteGround_Optimizer\Install_Service\Install_5_5_0;
use SiteGround_Optimizer\Install_Service\Install_5_5_2;
use SiteGround_Optimizer\Install_Service\Install_5_5_4;
use SiteGround_Optimizer\Install_Service\Install_5_6_3;
use SiteGround_Optimizer\Install_Service\Install_5_6_7;
use SiteGround_Optimizer\Install_Service\Install_5_7_0;
use SiteGround_Optimizer\Install_Service\Install_5_7_4;
use SiteGround_Optimizer\Install_Service\Install_5_7_14;
use SiteGround_Optimizer\Install_Service\Install_5_9_2;
use SiteGround_Optimizer\Install_Service\Install_6_0_0;
use SiteGround_Optimizer\Install_Service\Install_6_0_2;
use SiteGround_Optimizer\Install_Service\Install_6_0_3;
use SiteGround_Optimizer\Install_Service\Install_7_1_0;
use SiteGround_Optimizer\Install_Service\Install_7_1_5;
use SiteGround_Optimizer\Install_Service\Install_7_2_2;
use SiteGround_Optimizer\Install_Service\Install_7_2_7;
use SiteGround_Optimizer\Install_Service\Install_Cleanup;
use SiteGround_Optimizer\Supercacher\Supercacher;

/**
 * Define the Install interface.
 *
 * @since  5.0.0
 */
class Install_Service {
	/**
	 * Array, containing all installs.
	 *
	 * @var array
	 */
	public $installs;

	public function __construct() {
		// Get the install services.
		$this->installs = array(
			new Install_5_0_0(),
			new Install_5_0_5(),
			new Install_5_0_6(),
			new Install_5_0_8(),
			new Install_5_0_9(),
			new Install_5_0_10(),
			new Install_5_0_12(),
			new Install_5_0_13(),
			new Install_5_2_0(),
			new Install_5_2_5(),
			new Install_5_3_0(),
			new Install_5_3_1(),
			new Install_5_3_2(),
			new Install_5_3_4(),
			new Install_5_3_6(),
			new Install_5_3_10(),
			new Install_5_4_0(),
			new Install_5_4_3(),
			new Install_5_5_0(),
			new Install_5_5_2(),
			new Install_5_5_4(),
			new Install_5_6_3(),
			new Install_5_6_7(),
			new Install_5_7_0(),
			new Install_5_7_4(),
			new Install_5_7_14(),
			new Install_5_9_2(),
			new Install_6_0_0(),
			new Install_6_0_2(),
			new Install_6_0_3(),
			new Install_7_1_0(),
			new Install_7_1_5(),
			new Install_7_2_2(),
			new Install_7_2_7(),
		);
	}

	/**
	 * Loop through all versions and install the updates.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function install() {
		// Use a transient to avoid concurrent installation calls.
		if ( $this->install_required() && false === get_transient( '_siteground_optimizer_installing' ) ) {
			set_transient( '_siteground_optimizer_installing', true, 5 * MINUTE_IN_SECONDS );

			// Do the install.
			$this->do_install();

			// Delete the transient after the install.
			delete_transient( '_siteground_optimizer_installing' );
		}

		Install_Cleanup::cleanup();

		// Flush dynamic and memcache.
		Supercacher::purge_cache();
		Supercacher::flush_memcache();
	}

	/**
	 * Perform the actual installation.
	 *
	 * @since 5.0.0
	 */
	private function do_install() {

		$version = null;

		foreach ( $this->installs as $install ) {
			// Get the install version.
			$version = $install->get_version();

			if ( version_compare( $version, $this->get_current_version(), '>' ) ) {
				// Install version.
				$install->install();

				// Bump the version.
				update_option( 'siteground_optimizer_version', $version );

				update_option( 'siteground_optimizer_update_timestamp', time() );
			}
		}
	}

	/**
	 * Retrieve the current version.
	 *
	 * @return type
	 */
	private function get_current_version() {
		return get_option( 'siteground_optimizer_version', '0.0.0' );
	}

	/**
	 * Checks whether update is required.
	 *
	 * @since  5.4.7
	 *
	 * @return bool True/false/
	 */
	private function install_required() {
		foreach ( $this->installs as $install ) {
			// Get the install version.
			$version = $install->get_version();

			if ( version_compare( $version, $this->get_current_version(), '>' ) ) {
				return true;
			}
		}

		return false;
	}
}
