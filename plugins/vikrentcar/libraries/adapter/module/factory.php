<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.module
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Factory class to handle the internal modules.
 *
 * @since 10.0
 */
class JModuleFactory
{
	/**
	 * An array containing the list of registered modules.
	 *
	 * @var array
	 */
	protected static $registered = array();

	/**
	 * Loads all the modules contained in the specified path.
	 *
	 * @param 	string 	 $path 	The modules path.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 *
	 * @uses 	loadModule()
	 */
	public static function load($path)
	{
		// get all the modules (folders)
		$folders = glob($path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

		// do not proceed if the folder is empty
		if (!count($folders))
		{
			return false;
		}

		$loaded = false;

		// iterate the folders
		foreach ($folders as $folder)
		{
			// load the module and update (on success only) the result
			$loaded = self::loadModule($folder) || $loaded;
		}

		return $loaded;
	}

	/**
	 * Loads the module contained in the given path.
	 * The folder must contain at least 2 files:
	 * - widget.php 	 to extend the module functions.
	 * - [mod_name].php  to render the module layout.
	 *
	 * @param 	string 	 $path 	The module path.
	 *
	 * @return 	boolean  True if loaded, otherwise false.
	 */
	public static function loadModule($path)
	{
		// make sure the module hasn't been yet registered
		if (isset(static::$registered[$path]))
		{
			return static::$registered[$path];
		}

		// before all, flag the module as not loaded
		static::$registered[$path] = 0;

		// check if the widget file exists
		$widget = $path . DIRECTORY_SEPARATOR . 'widget.php';

		if (!is_file($widget))
		{
			return false;
		}

		include $widget;

		// build widget classname
		$classname = basename($path);
		$classname = str_replace('_', ' ', $classname);
		$classname = preg_replace("/\s+/", '', ucwords($classname)) . '_Widget';

		// make sure the widget handler exists
		if (!class_exists($classname))
		{
			return false;
		}

		// register the widget in WP pool
		register_widget($classname);

		// mark the module as loaded
		static::$registered[$path] = 1;

		return true;
	}
}
