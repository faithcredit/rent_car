<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.layout
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.layout.file');

/**
 * Helper to render a Layout object, storing a base path.
 *
 * @since 10.1.18
 */
class JLayoutHelper
{
	/**
	 * A default base path that will be used if none is provided when calling the render method.
	 *
	 * @var string
	 */
	public static $defaultBasePath = '';

	/**
	 * Method to render a layout with debug info.
	 *
	 * @param   string  $layoutFile   Dot separated path to the layout file, relative to base path.
	 * @param   mixed   $displayData  Object which properties are used inside the layout file to build displayed output.
	 * @param   string  $basePath     Base path to use when loading layout files.
	 * @param   mixed   $options      Optional custom options to load. Registry or array format.
	 *
	 * @return  string  The layout HTML.
	 */
	public static function debug($layoutFile, $displayData = null, $basePath = '', $options = null)
	{
		$basePath = empty($basePath) ? self::$defaultBasePath : $basePath;

		// make sure we send null to JLayoutFile if no path set
		$basePath = empty($basePath) ? null : $basePath;
		$layout = new JLayoutFile($layoutFile, $basePath, $options);

		return $layout->debug($displayData);
	}

	/**
	 * Method to render the layout.
	 *
	 * @param   string  $layoutFile   Dot separated path to the layout file, relative to base path.
	 * @param   mixed   $displayData  Object which properties are used inside the layout file to build displayed output.
	 * @param   string  $basePath     Base path to use when loading layout files.
	 * @param   mixed   $options      Optional custom options to load. Registry or array format.
	 *
	 * @return  string  The layout HTML.
	 */
	public static function render($layoutFile, $displayData = null, $basePath = '', $options = null)
	{
		$basePath = empty($basePath) ? self::$defaultBasePath : $basePath;

		// make sure we send null to JLayoutFile if no path set
		$basePath = empty($basePath) ? null : $basePath;
		$layout = new JLayoutFile($layoutFile, $basePath, $options);

		return $layout->render($displayData);
	}
}
