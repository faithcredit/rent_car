<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.html
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Utility class working with content language select lists.
 *
 * @since  10.1.30
 */
abstract class JHtmlContentLanguage
{
	/**
	 * Cached array of the content language items.
	 *
	 * @var array
	 */
	protected static $items = null;

	/**
	 * Get a list of the available content language items.
	 *
	 * @param   boolean  $all        True to include All (*).
	 * @param   boolean  $translate  True to translate All.
	 *
	 * @return  string
	 */
	public static function existing($all = false, $translate = false)
	{
		if (static::$items === null)
		{
			static::$items = array();

			// load all the existing languages
			foreach (JFactory::getLanguage()->getKnownLanguages() as $lang)
			{
				// create language option
				static::$items[] = JHtml::_('select.option', $lang['tag'], $lang['name']);
			}
		}

		if ($all)
		{
			// create "all languages" option
			$option = array(
				JHtml::_('select.option', '*', $translate ? JText::_('JALL') : 'JALL'),
			);

			// merge first option with other ones
			return array_merge($option, static::$items);
		}
		else
		{
			// return cached list
			return static::$items;
		}
	}
}
