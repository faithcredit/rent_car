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
 * Utility class for Grid behaviors.
 *
 * @since 10.0
 */
abstract class JHtmlGrid
{
	/**
	 * Method to sort a column in a grid.
	 *
	 * @param   string  $title          The link title.
	 * @param   string  $order          The order field for the column.
	 * @param   string  $direction      The current direction.
	 * @param   string  $selected       The selected ordering.
	 * @param   string  $task           An optional task override.
	 * @param   string  $new_direction  An optional direction for the new column.
	 * @param   string  $tip            An optional text shown as tooltip title instead of $title.
	 * @param   string  $form           An optional form selector.
	 *
	 * @return  string 	The HTML grid column.
	 */
	public static function sort($title, $order, $direction = 'asc', $selected = '', $task = null, $new_direction = 'asc', $tip = '', $form = null)
	{
		$direction = strtolower($direction);
		$icon 	= array('sort-up', 'sort-down');
		$index 	= (int) ($direction === 'desc');

		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$direction = $direction === 'desc' ? 'asc' : 'desc';
		}

		if ($form)
		{
			$form = ', document.getElementById(\'' . $form . '\')';
		}

		$html = '<a href="#" onclick="Joomla.tableOrdering(\'' . $order . '\',\'' . $direction . '\',\'' . $task . '\'' . $form . ');return false;"'
			. ' title="' . htmlspecialchars(JText::_($tip ? $tip : '')) . '"'
			. ($order == $selected ? 'class="activesort"' : '')
			. '">';
		
		$html .= JText::_($title);

		if ($order == $selected)
		{
			$html .= '<i class="fas fa-' . $icon[$index] . '"></i>';
		}
		else if (substr($title, 0, 1) !== '<')
		{
			// include sortable icon only in case the column title doesn't start with
			// a tag opening, which could mean that the th displays another icon
			$html .= '<i class="fas fa-sort"></i>';
		}

		$html .= '</a>';

		return $html;
	}
}
