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

/**
 * Abstract base toolbar button.
 *
 * @since 10.0
 */
abstract class JToolbarButtonBase
{
	/**
	 * The name/type of the button.
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * The layout id for the rendering of the button.
	 *
	 * @var string
	 */
	protected $_layoutId;

	/**
	 * Class constructor.
	 * This method accepts an undefined number of arguments.
	 *
	 * @uses 	setup()
	 */
	public function __construct()
	{
		$args = func_get_args();

		if (count($args))
		{
			$this->_name = array_shift($args);
		}

		call_user_func_array(array($this, 'setup'), $args);
	}

	/**
	 * Provides a new instance of the specified button.
	 * This method accepts an undefined number of arguments.
	 *
	 * @return 	self 	A new button instance.
	 */
	public static function getInstance()
	{
		$args = func_get_args();

		if (!count($args))
		{
			return null;
		}

		// In case the first argument is an array it means that
		// this method has been called directly. We need to replace
		// the args list with the first element in the array.
		if (is_array($args[0]))
		{
			$args = $args[0];
		}

		$name = strtolower($args[0]);

		if (!JLoader::import('adapter.toolbar.button.' . $name))
		{
			return null;
		}

		$classname = 'JToolbarButton' . ucwords($name);

		if (!class_exists($classname))
		{
			return null;
		}

		$reflect = new ReflectionClass($classname);

		$button = $reflect->newInstanceArgs($args);

		return ($button instanceof JToolbarButtonBase ? $button : null);
	}

	/**
	 * Returns the name-identifier of the button.
	 *
	 * @return 	string 	The button name-id.
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Returns the layout ID for the rendering of the button.
	 *
	 * @return 	string 	The layout ID.
	 */
	public function getLayoutId()
	{
		return $this->_layoutId;
	}

	/**
	 * Abstract method to setup the button.
	 * This method accepts an undefined number of arguments.
	 *
	 * @return 	void
	 */
	abstract protected function setup();

	/**
	 * Returns an array containing the data to use for the button rendering.
	 *
	 * @return 	array 	Display data array.
	 */
	abstract public function getDisplayData();
}
