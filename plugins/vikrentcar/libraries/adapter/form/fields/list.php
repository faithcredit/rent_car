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

jimport('joomla.form.formfield');

/**
 * Form field class to handle dropdown fields.
 *
 * @since 10.0
 */
class JFormFieldList extends JFormField
{
	/**
	 * The layout identifier for list fields.
	 *
	 * @var   string
	 * @since 10.1.20
	 */
	protected $layoutId = 'html.form.fields.list';

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   10.1.36
	 */
	public function getOptions()
	{
		return isset($this->option) ? (array) $this->option : [];
	}

	/**
	 * @override
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return 	array 	An associative array of display data.
	 */
	public function getLayoutData()
	{
		$data = array();
		$data['name'] 		= $this->name;
		$data['class'] 		= $this->class;
		$data['id'] 		= $this->id;
		$data['value'] 		= is_null($this->value) ? $this->default : $this->value;
		$data['required']	= $this->required === "true" || $this->required === true ? true : false;
		$data['multiple']	= !is_null($this->multiple) && $this->multiple != "false" ? true : false;
		$data['disabled']	= $this->disabled === "true" || $this->disabled === true ? true : false;
		$data['options']	= $this->getOptions();

		return $data;
	}
}
