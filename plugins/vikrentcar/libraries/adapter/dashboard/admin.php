<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.dashboard
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.dashboard.widget');

/**
 * Helper class used to manage the widgets that can be included and
 * configured within the admin dashboard of WordPress.
 *
 * @since 10.1.31
 * @link  https://developer.wordpress.org/apis/handbook/dashboard-widgets/
 */
class JDashboardAdmin
{
	/**
	 * Loads all the widgets under the specified folder/file.
	 *
	 * @param  	mixed   $folder  Either a file or a folder that contains the widgets.
	 * @param 	string 	$prefix  The prefix of the classname. If not specified
	 * 							 `JDashboardWidget` is assumed.
	 *
	 * @return 	void
	 */
	public static function load($folder, $prefix = null)
	{
		if (is_null($prefix))
		{
			// use default prefix
			$prefix = 'JDashboardWidget';
		}

		// check if we have a directory or a file
		if (is_dir($folder))
		{
			JLoader::import('adapter.filesystem.folder');

			// load list of files contained within the specified folder
			$folder = JFolder::files($folder, '\.php$', $recursive = false, $full = true);
		}
		else
		{
			// cast file to array
			$folder = (array) $folder;

			// iterate specified files
			foreach ($folder as $file)
			{
				// make sure the file exists and is a PHP file
				if (!is_file($file) || !preg_match("/\.php$/", $file))
				{
					// file not found, raise error
					throw new RuntimeException(sprintf('Widget file [%s] not found', $file), 404);
				}
			}
		}

		// at this point, all the specified files exist
		foreach ($folder as $file)
		{
			// load the file
			require_once $file;

			// extract file name from path
			$filename = preg_replace("/\.php$/", '', basename($file));
			// remove any unexpected character and make the next character uppercase
			$filename = preg_replace_callback("/[^a-zA-Z0-9]+([a-zA-Z0-9])/", function($match)
			{
				return strtoupper(end($match));
			}, $filename);

			// prepare widget class name
			$classname = $prefix . ucfirst($filename);

			// make sure the class exists
			if (!class_exists($classname))
			{
				// missing class, raise error
				throw new RuntimeException(sprintf('Class [%s] not found', $classname), 404);
			}

			// instantiate class
			$widget = new $classname();

			// make sure the class is a valid instance
			if (!$widget instanceof JDashboardWidget)
			{
				// the widget doesn't inherit the correct class, raise error
				throw new RuntimeException(sprintf('Class [%s] is not a valid instance', $classname), 500);
			}

			// register class within the WordPress dashboard
			static::register($widget);
		}
	}

	/**
	 * Registers a widget within the dashboard.
	 *
	 * @param 	JDashboardWidget  $widget  The instance of the widget to register.
	 *
	 * @return 	void
	 */
	public static function register(JDashboardWidget $widget)
	{
		if (!$widget->canAccess())
		{
			// do not display widget in case the user is not authorized
			return false;
		}

		$args = array();

		// set widget ID as first argument
		$args[] = $widget->getID();
		// set widget name as second argument
		$args[] = $widget->getName();
		// set callback to display the widget contents
		$args[] = array($widget, 'getHtml');

		// check if the widget supports a configuration
		if ($widget->getForm())
		{
			// set callback to display the configuration form
			$args[] = array($widget, 'config');
			// set configuration settings as last argument
			$args[] = $widget->getConfig();
		}

		// call function to register the widget
		call_user_func_array('wp_add_dashboard_widget', $args);
	}
}
