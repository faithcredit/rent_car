<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	system
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class used to provide support for TinyMCE editor.
 *
 * @since 1.0
 */
class VikRentCarTinyMCE
{
	/**
	 * Adds a button to the array of buttons for TinyMCE
	 * 
	 * @param  array 	$buttons
	 * 
	 * @return array
	 */
	public static function addShortcodesButton($buttons)
	{
		$buttons[] = 'vrc-shortcodes';
		
		return $buttons;
	}

	/**
	 * Attaches the necessary scripts to handle the shortcode event
	 * 
	 * @param 	array 	$plugin_array
	 * 
	 * @return 	array
	 */
	public static function registerShortcodesScript($plugin_array)
	{
		// get shortcode model
		$model = JModel::getInstance('vikrentcar', 'shortcodes', 'admin');

		// obtain a categorized shortcodes list 
		$shortcodes = array();

		foreach ($model->all() as $s)
		{
			$title = JText::_($s->title);

			if (!isset($shortcodes[$title]))
			{
				$shortcodes[$title] = array();
			}

			$shortcodes[$title][] = $s;
		}

		$document = JFactory::getDocument();

		// register script to access JSON object
		$document->addScriptDeclaration("var VIKRENTCAR_SHORTCODES = " . json_encode($shortcodes) . ";");
		$document->addStyleSheet(VIKRENTCAR_ADMIN_ASSETS_URI . 'css/tinymce-shortcodes.css');

		$plugin_array['vrc-shortcodes'] = VIKRENTCAR_ADMIN_ASSETS_URI . 'js/tinymce-shortcodes.js';

		return $plugin_array;
	}
}
