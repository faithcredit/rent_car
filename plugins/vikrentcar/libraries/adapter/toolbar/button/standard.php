<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.toolbar
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.toolbar.button.base');

/**
 * Plugin standard button toolbar handler.
 *
 * @since 10.0
 */
class JToolbarButtonStandard extends JToolbarButtonBase
{
	/**
	 * @override
	 * The name/type of the button.
	 *
	 * @var string
	 */
	protected $_name = 'Standard';

	/**
	 * @override
	 * The layout id for the rendering of the button.
	 *
	 * @var string
	 */
	protected $_layoutId = 'html.toolbar.button.standard';

	/**
	 * A list of button options.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * @override
	 * Method to setup the button.
	 *
	 * @param 	string 	 $icon 	 The button icon.
	 * @param 	string 	 $alt 	 The button text.
	 * @param 	string 	 $task 	 The form task to launch.
	 * @param 	boolean  $check  If true, it is mandatory the selection of the rows.
	 *
	 * @return 	void
	 */
	protected function setup($icon = '', $alt = '', $task = '', $check = false)
	{
		$this->options['icon']   = $icon;
		$this->options['text']   = JText::_($alt);
		$this->options['class']  = strtolower(preg_replace("/[^a-zA-Z0-9_\-]+/", '-', $icon));
		$this->options['id'] 	 = 'jbutton-' . strtolower(preg_replace("/[^a-zA-Z0-9_\-]+/", '-', $task));
		$this->options['action'] = $this->getCommand($task, $check);
	}

	/**
	 * @override
	 * Returns an array containing the data to use for the button rendering.
	 *
	 * @return 	array 	Display data array.
	 */
	public function getDisplayData()
	{
		return $this->options;
	}

	/**
	 * Returns the javascript onclick event to launch.
	 *
	 * @param 	string 	 $task 	 The form task to launch.
	 * @param 	boolean  $check  If true, it is mandatory the selection of the rows.
	 * 
	 * @return 	string 	 The onclick event.
	 */
	protected function getCommand($task, $check = false)
	{
		$cmd = "Joomla.submitbutton('{$task}');";

		if ($check)
		{
			$cmd = "if (Joomla.hasChecked()) { $cmd } else { alert('" . addslashes(JText::_('PLEASE_MAKE_A_SELECTION')) . "'); }";
		}

		return $cmd;
	}
}
