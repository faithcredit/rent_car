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
 * Form field class to handle number fields.
 *
 * @since 10.0
 */
class JFormFieldNumber extends JFormField
{
	/**
	 * The layout identifier for number fields.
	 *
	 * @var   string
	 * @since 10.1.20
	 */
	protected $layoutId = 'html.form.fields.number';

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
		$data['readonly']	= $this->readonly === "true" || $this->readonly === true ? true : false;
		$data['step']		= $this->step ? $this->step : 1;

		if (isset($this->min))
		{
			$data['min'] = $this->min;
		}

		if (isset($this->max))
		{
			$data['max'] = $this->max;
		}

		return $data;
	}
}
