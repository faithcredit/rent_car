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
 * Class used to provide support for Gutenberg editor.
 *
 * @since 1.0.17
 */
class VikRentCarGutenberg
{
	/**
	 * Attaches the necessary scripts to handle the shortcode event.
	 * 
	 * @return 	void
	 */
	public static function registerShortcodesScript()
	{
		/**
		 * Make sure Gutenberg is up and running to avoid
		 * any Fatal Error, as the register_block_type()
		 * function may be not available on old instances.
		 */
		if (!function_exists('register_block_type'))
		{
			return false;
		}

		// register the script that contains all the JS functions used
		// to implement a new block for Gutenberg editor
		wp_register_script(
			'vikrentcar-gutenberg-shortcodes',
			VIKRENTCAR_ADMIN_ASSETS_URI . 'js/gutenberg-shortcodes.js',
			array('wp-blocks', 'wp-element', 'wp-i18n')
		);

		// register the style that contains all the CSS rules used
		// to stylize the blocks for Gutenberg editor
		wp_register_style(
			'vikrentcar-gutenberg-shortcodes',
			VIKRENTCAR_ADMIN_ASSETS_URI . 'css/gutenberg-shortcodes.css',
			array()
		);

		// create a new block type, which must provide the script and the
		// style we defined in the previous piece of code (script/style ID)
		register_block_type('vikrentcar/gutenberg-shortcodes', array(
			'editor_script' => 'vikrentcar-gutenberg-shortcodes',
			'editor_style'  => 'vikrentcar-gutenberg-shortcodes',
		));

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

		// register script to access JSON object
		JFactory::getDocument()->addScriptDeclaration("var VIKRENTCAR_SHORTCODES_BLOCK = " . json_encode($shortcodes) . ";");
	}
}
