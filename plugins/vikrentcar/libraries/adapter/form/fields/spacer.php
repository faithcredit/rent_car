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
 * Form field class to handle separators.
 *
 * @since 10.1.21
 */
class JFormFieldSpacer extends JFormField
{
	/**
	 * The layout identifier for separator fields.
	 *
	 * @var string
	 */
	protected $layoutId = 'html.form.fields.spacer';

	/**
	 * @override
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return 	array 	An associative array of display data.
	 */
	public function getLayoutData()
	{
		$data = array();
		$data['name']  = $this->name;
		$data['label'] = $this->label;
		$data['class'] = $this->class;
		$data['id']    = $this->id;

		return $data;
	}
}
