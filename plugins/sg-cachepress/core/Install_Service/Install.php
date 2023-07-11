<?php
namespace SiteGround_Optimizer\Install_Service;

abstract class Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.0.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '0.0.0';

	/**
	 * Return the current version of the installation.
	 *
	 * @since 5.0.0
	 */
	public function get_version() {
		return static::$version;
	}

	/**
	 * Run the install procedure. This function must be implemented by superclasses.
	 *
	 * @since 5.0.0
	 *
	 * @return mixed The result.
	 */
	abstract public function install();

}