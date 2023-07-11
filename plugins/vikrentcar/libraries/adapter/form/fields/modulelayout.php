<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.form
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.form.fields.list');

/**
 * Form field class to handle dropdown fields containing
 * the available module layouts.
 *
 * @since 10.0
 */
class JFormFieldModuleLayout extends JFormFieldList
{
	/**
	 * @override
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return 	array 	An associative array of display data.
	 */
	public function getLayoutData()
	{
		$path = $this->modpath . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . '*.php';

		$this->option = array();

		// get default layouts contained in the plugin directory
		foreach (glob($path) as $layout)
		{
			$value = basename($layout);
			$value = substr($value, 0, strrpos($value, '.'));
			$text  = ucwords(preg_replace("/[_-]/", ' ', $value));

			$this->option[$value] = $text;
		}

		/**
		 * Get existing overrides (if any).
		 *
		 * @since 10.1.2
		 */
		if (preg_match("/[\/\\\\]plugins[\/\\\\](.*?)[\/\\\\](.*?)[\/\\\\](.*?)$/", $this->modpath, $match))
		{
			$upload = wp_upload_dir();

			$parts = array();
			// push base upload path
			$parts[] = $upload['basedir'];
			// push plugin name
			$parts[] = $match[1];
			// push default overrides folder
			$parts[] = 'overrides';
			// push client dir (modules)
			$parts[] = $match[2];
			// push widget name
			$parts[] = $match[3];

			$layoutsPath = implode(DIRECTORY_SEPARATOR, $parts);

			// make sure the module override folder exists
			if (is_dir($layoutsPath))
			{
				// get all layouts contained in the override directory
				foreach (glob($layoutsPath . DIRECTORY_SEPARATOR . '*.php') as $layout)
				{
					// prettify the layout name
					$text = basename($layout);
					$text = substr($text, 0, strrpos($text, '.'));
					$text = ucwords(preg_replace("/[_-]/", ' ', $text));

					// use full path as value and concat "override" to the layout
					// name to allow the users to detect the custom files
					$this->option[$layout] = $text . ' (override)';
				}
			}
		}

		return parent::getLayoutData();
	}
}
