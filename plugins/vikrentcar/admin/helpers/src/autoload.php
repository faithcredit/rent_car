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

/**
 * Register CMS HTML helpers.
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
JHtml::addIncludePath(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html');

/**
 * Libraries autoloader.
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
spl_autoload_register(function($class)
{
	// get the class prefix and base path
	$class_prefix = 'VRC';
	$class_bpath  = dirname(__FILE__);

	if (stripos($class, 'VRC') !== 0)
	{
		// ignore if we are loading an outsider
		return false;
	}

	// remove prefix from class
	$tmp = preg_replace("/^$class_prefix/", '', $class);
	// separate camel-case intersections
	$tmp = preg_replace("/([a-z])([A-Z])/", addslashes('$1' . DIRECTORY_SEPARATOR . '$2'), $tmp);

	// build path from which the class should be loaded
	$path = $class_bpath . DIRECTORY_SEPARATOR . strtolower($tmp) . '.php';

	// make sure the file exists
	if (is_file($path))
	{
		// include file and check if the class is now available
		if ((include_once $path) && (class_exists($class) || interface_exists($class) || trait_exists($class)))
		{
			return true;
		}
	}

	return false;
});
