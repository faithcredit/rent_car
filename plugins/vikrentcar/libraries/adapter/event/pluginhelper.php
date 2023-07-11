<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.event
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Abstract plugins helper.
 *
 * @since 10.1.11
 */
abstract class JPluginHelper
{
	/**
	 * Loads all the plugin files for a particular type if no specific plugin is specified
	 * otherwise only the specific plugin is loaded.
	 *
	 * @param   string 	 $type 	  The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string 	 $plugin  The plugin name.
	 *
	 * @return  boolean  True on success.
	 */
	public static function importPlugin($type, $plugin = null)
	{
		// do nothing here as all the plugins are always loaded by default

		return true;
	}
}
