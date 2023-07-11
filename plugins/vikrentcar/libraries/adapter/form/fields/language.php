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
 * The languages form field type provides a drop down list of the installed languages.
 *
 * @since 10.1.9
 */
class JFormFieldLanguage extends JFormFieldList
{
	/**
	 * @override
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return 	array 	An associative array of display data.
	 */
	public function getLayoutData()
	{
		// get languages metadata
		$languages = JLanguage::getKnownLanguages();

		foreach ($languages as $tag => $lang)
		{
			// push lang tag / name within the options array
			$this->option[$tag] = $lang['nativeName'];
		}

		return parent::getLayoutData();
	}
}
