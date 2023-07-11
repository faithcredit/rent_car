<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.database
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Table class for database contents.
 *
 * @since 10.1.19
 */
class JTableContent extends JTable
{
	/**
	 * @override
	 * Gets the columns from database table.
	 *
	 * @param   boolean  $reload  Flag to reload cache.
	 *
	 * @return  mixed  	 An array of the field names, or false if an error occurs.
	 */
	public function getFields($reload = false)
	{
		return array(
			'id',
			'text',
			'introtext',
			'fulltext',
		);
	}
}
