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
 * Utility class for list behaviors.
 *
 * @since 10.0
 */
abstract class JHtmlList
{
	/**
	 * Select list of active users.
	 *
	 * @param   string   $name        The name of the field.
	 * @param   string   $active      The active user.
	 * @param   integer  $nouser      If set include an option to select no user.
	 * @param   string   $javascript  Custom javascript.
	 * @param   string   $order       Specify a field to order by.
	 *
	 * @return  string   The HTML for a list of users list of users.
	 */
	public static function users($name, $active, $nouser = 0, $javascript = null, $order = 'display_name')
	{
		if (empty($order))
		{
			$order = 'name';
		}

		$select = '';

		if ($nouser)
		{
			$select = $nouser ? "<option value=\"0\">--</option>" : '';
		}

		$dbo = JFactory::getDbo();

		$q = "SELECT `id`, `display_name`
		FROM `#__users`
		ORDER BY `{$order}` ASC";

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectlist() as $u)
			{
				$status  = $active == $u->id ? ' selected="selected"' : '';
				$select .= "<option value=\"{$u->id}\"{$status}>{$u->display_name}</option>";
			}
		}

		return "<select name=\"{$name}\" {$javascript}>{$select}</select>";
	}
}
