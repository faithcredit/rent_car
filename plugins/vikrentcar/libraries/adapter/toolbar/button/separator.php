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
 * Plugin separator toolbar handler.
 *
 * @since 10.0
 */
class JToolbarButtonSeparator extends JToolbarButtonBase
{
	/**
	 * @override
	 * The name/type of the button.
	 *
	 * @var string
	 */
	protected $_name = 'Separator';

	/**
	 * @override
	 * The layout id for the rendering of the button.
	 *
	 * @var string
	 */
	protected $_layoutId = 'html.toolbar.button.';

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
	 *
	 * @uses 	getCommand()
	 */
	protected function setup($type = '', $width = 0)
	{
		$this->_layoutId .= $type;

		$this->options['width'] = $width;
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
}
