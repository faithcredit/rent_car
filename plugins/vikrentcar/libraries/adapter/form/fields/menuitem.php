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
 * the pages assigned to a specific shortcode of the plugin.
 *
 * @since 10.0
 */
class JFormFieldMenuItem extends JFormFieldList
{
	/**
	 * @override
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return 	array 	An associative array of display data.
	 */
	public function getLayoutData()
	{
		$this->option = array();
		$this->option[''] = '--';

		if (!empty($this->prefix))
		{
			// set modowner with custom prefix
			$this->modowner = $this->prefix;
		}

		/**
		 * Force the client path because we need to load the shortcodes model
		 * always from the back-end folder.
		 *
		 * An issue occurred since WP 5.8, after the refactoring of the widgets 
		 * management page, where the client now results to be "site" in place
		 * of "admin".
		 *
		 * @since 10.1.34
		 */
		$model = JModel::getInstance($this->modowner, 'shortcodes', 'admin');

		if ($model)
		{
			foreach ($model->all() as $item)
			{
				if ($item->post_id)
				{
					$this->option[$item->post_id] = get_the_title($item->post_id) . " - {$item->type}";
				}
			}
		}

		return parent::getLayoutData();
	}
}
