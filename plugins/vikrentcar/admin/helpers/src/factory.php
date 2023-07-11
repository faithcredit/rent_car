<?php
/** 
 * @package     VikRentCar
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JPluginHelper::importPlugin('vikrentcar');

/**
 * Factory application class.
 *
 * @since 1.3
 */
final class VRCFactory
{
	/**
	 * Application configuration handler.
	 *
	 * @var VRCConfigRegistry
	 */
	private static $config;

	/**
	 * Application platform handler.
	 *
	 * @var VRCPlatformInterface
	 */
	private static $platform;

	/**
	 * Cron jobs factory instance.
	 * 
	 * @var VRCCronFactory
	 */
	private static $cronFactory;

	/**
	 * Class constructor.
	 * @private This object cannot be instantiated. 
	 */
	private function __construct()
	{
		// never called
	}

	/**
	 * Class cloner.
	 * @private This object cannot be cloned.
	 */
	private function __clone()
	{
		// never called
	}

	/**
	 * Returns the current configuration object.
	 *
	 * @return 	VRCConfigRegistry
	 */
	public static function getConfig()
	{
		// check if config class is already instantiated
		if (is_null(static::$config))
		{
			// cache instantiation
			static::$config = new VRCConfigRegistryDatabase([
				'db' => JFactory::getDbo(),
			]);
		}

		return static::$config;
	}

	/**
	 * Returns the current platform handler.
	 *
	 * @return 	VRCPlatformInterface
	 */
	public static function getPlatform()
	{
		// check if platform class is already instantiated
		if (is_null(static::$platform))
		{
			if (defined('ABSPATH') && function_exists('wp_die'))
			{
				// running WordPress platform
				static::$platform = new VRCPlatformOrgWordpress();
			}
			else
			{
				// running Joomla platform
				static::$platform = new VRCPlatformOrgJoomla();
			}
		}

		return static::$platform;
	}

	/**
	 * Returns the current cron factory.
	 *
	 * @return 	VRCCronFactory
	 * 
	 * @since   1.3.0
	 */
	public static function getCronFactory()
	{
		// check if cron factory class is already instantiated
		if (is_null(static::$cronFactory))
		{
			// create cron factory class and register the default folder
			static::$cronFactory = new VRCCronFactory;
			static::$cronFactory->setIncludePaths(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'cronjobs');

			/**
			 * Trigger hook to allow third-party plugin to register custom folders in which
			 * VikRentCar should look for the creation of new cron job instances.
			 * 
			 * In example:
			 * $factory->addIncludePath($path);
			 * $factory->addIncludePaths([$path1, $path2, ...]);
			 * 
			 * @param   VRCCronFactory  $factory  The cron jobs factory.
			 * 
			 * @return  void
			 * 
			 * @since   1.3.0
			 */
			JFactory::getApplication()->triggerEvent('onCreateCronJobsFactoryVikRentCar', [static::$cronFactory]);
		}

		return static::$cronFactory;
	}
}
