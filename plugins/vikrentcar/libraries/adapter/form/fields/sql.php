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
 * the values obtained by a SQL command.
 *
 * @since 10.0
 */
class JFormFieldSql extends JFormFieldList
{
	/**
	 * @override
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return 	array 	An associative array of display data.
	 */
	public function getLayoutData()
	{
		$key = $this->key_field   ? $this->key_field   : 'id';
		$val = $this->value_field ? $this->value_field : 'value';

		$dbo = JFactory::getDbo();

		$dbo->setQuery($this->query);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectlist() as $obj)
			{
				$this->option[$obj->{$key}] = $obj->{$val};
			}
		}

		return parent::getLayoutData();
	}
}
