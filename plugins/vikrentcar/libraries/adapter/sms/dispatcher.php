<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.sms
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.sms.driver');

/**
 * Abstract factory used to instantiate different SMS drivers.
 * The example below describes how to hook the drivers to this dispatcher.
 *
 * add_action('load_sms_driver_myplugin', function(&$drivers, $driver)
 * {
 *		if ($driver == 'clickatell')
 * 		{
 *			JLoader::import('driver.clickatell', MYPLUGIN_BASE);
 *			$drivers[] = 'MyPluginSmsClickatell';
 *		}
 * }, 10, 2);
 * 
 * It is mandatory to indicate also the number of accepted arguments.
 *
 * @since 10.1.30
 */
class JSmsDispatcher
{
	/**
	 * A list containing all the driver instances.
	 *
	 * @var JSmsDriver[]
	 */
	protected static $instances = array();

	/**
	 * Provides a new instance for the specified arguments.
	 *
	 * @param 	string 	  $plugin 	The name of the plugin that requested the driver.
	 * @param 	string 	  $driver 	The name of the driver that should be instantiated.
	 * @param 	mixed 	  $order 	The details of the order that has to be notified.
	 * @param 	mixed 	  $config 	The driver configuration array or a JSON string.
	 *
	 * @return 	JSmsDriver
	 *
	 * @throws 	RuntimeException In case the requested driver doesn't exist.
	 */
	public static function getInstance($plugin, $driver, $order = array(), $config = array())
	{
		if (substr($driver, -4) == '.php')
		{
			// make sure the driver doesn't contain PHP extension
			$driver = substr($driver, 0, -4);
		}

		// create unique identifier
		$sign = $plugin . '.' . $driver;

		// check if the driver was already instantiated
		if (!isset(static::$instances[$sign]))
		{
			$classname = null;
			$drivers   = array();

			/**
			 * Trigger action to obtain a list of classnames of the sms driver.
			 * The action should autoload the file that contains the classname.
			 * In case the sms driver should be loaded, the classname MUST be
			 * pushed within the &$drivers array.
			 * Fires before the instantiation of the returned classname.
			 *
			 * @param 	array 	A reference to the list of available drivers.
			 * @param	string	The name of the driver to load.
			 *
			 * @since 	10.1.30
			 */
			do_action_ref_array('load_sms_driver_' . $plugin, array(&$drivers, $driver));

			// use the last driver in the list
			$classname = array_pop($drivers);

			if (!$classname || !class_exists($classname))
			{
				// driver not found, raise an exception
				throw new RuntimeException('The SMS driver [' . $driver . '] for [' . $plugin . '] does not exist.', 404);
			}

			// instantiate the driver
			$driver = new $classname($plugin, $order, $config);

			if (!$driver instanceof JSmsDriver)
			{
				// the class is not an instance of JSmsDriver, raise an exception
				throw new RuntimeException('The SMS driver [' . $classname . '] is not a valid instance.', 500);
			}

			// cache the driver
			static::$instances[$sign] = $driver;
		}

		return static::$instances[$sign];
	}

	/**
	 * Returns a list of all the drivers supported by the specified plugin. 
	 * The SMS drivers will be returned in ascending order.
	 *
	 * @param 	string 	$plugin  The name of the plugin.
	 *
	 * @return 	array 	A list of paths.
	 */
	public static function getSupportedDrivers($plugin)
	{
		// init drivers array
		$drivers = array();

		/**
		 * Hook used to filter the list of all the supported drivers.
		 * Every plugin attached to this filter will be able to push one
		 * or more drivers within the $drivers array.
		 *
		 * @param 	array 	An array containing the list of the supported drivers.
		 *
		 * @since 	10.1.30
		 */
		$drivers = apply_filters('get_supported_sms_drivers_' . $plugin, $drivers);

		// remove duplicated records
		$drivers = array_unique($drivers);

		// sort by ascending driver name
		sort($drivers);

		return $drivers;
	}
}
