<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.loader
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// I hope JImport class is always loaded...

/**
 * Loads the specified file from the plugin libraries folder.
 * Since multiple plugins may declare the same function, it is 
 * suggested to use it only to load files contained in the adapter folder.
 *
 * @param   string   $key   The class name to look for (dot notation).
 *
 * @return  boolean  True on success, otherwise false.
 *
 * @since 	10.0
 */
if (!function_exists('jimport'))
{
	function jimport($key)
	{
		switch ($key)
		{
			case 'joomla.html.pagination':
				$key = 'adapter.pagination.pagination';
				break;

			case 'joomla.application.component.view':
				$key = 'adapter.mvc.view';
				break;

			case 'joomla.application.component.controller':
				$key = 'adapter.mvc.controller';
				break;

			case 'joomla.application.component.controlleradmin':
				$key = 'adapter.mvc.controllers.admin';
				break;

			case 'joomla.application.component.helper':
				$key = 'adapter.component.helper';
				break;

			case 'joomla.application.module.helper':
				$key = 'adapter.module.helper';
				break;

			case 'joomla.filesystem.file':
				$key = 'adapter.filesystem.file';
				break;

			case 'joomla.form.formfield':
				$key = 'adapter.form.field';
				break;

			case 'joomla.filesystem.archive':
				$key = 'adapter.filesystem.archive';
				break;

			case 'joomla.filesystem.folder':
				$key = 'adapter.filesystem.folder';
				break;

			/**
			 * Route version when directly loaded.
			 *
			 * @since 10.1.26
			 */
			case 'joomla.version':
				$key = 'adapter.application.version';
				break;
		}

		return JLoader::import($key);
	}
}
