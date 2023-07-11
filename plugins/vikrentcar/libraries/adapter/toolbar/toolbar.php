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
 * Plugin main toolbar handler.
 *
 * @since 10.0
 */
class JToolbar
{
	/**
	 * The singleton toolbar instance.
	 *
	 * @var JToolbar
	 */
	protected static $instance = array();

	/**
	 * The toolbar ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The toolbar title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * A list of buttons.
	 *
	 * @var array
	 */
	protected $bar;

	/**
	 * Class constructor.
	 */
	protected function __construct($id)
	{
		// this method is not accessible

		$this->id  = empty($id) ? uniqid() : $id;
		$this->bar = array();
	}

	/**
	 * Class cloner.
	 */
	protected function __clone()
	{
		// this method is not accessible
	}

	/**
	 * Proxy to access the JToolbar singleton.
	 *
	 * @return 	self 	The singleton.
	 */
	public static function getInstance($id = 'jtoolbar')
	{
		if (!isset(static::$instance[$id]))
		{
			static::$instance[$id] = new static($id);
		}

		return static::$instance[$id];
	}

	/**
	 * The toolbar title.
	 *
	 * @param 	string 	$title 	The title.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setTitle($title)
	{
		$this->title = (string) $title;

		return $this;
	}

	/**
	 * Returns the toolbar title.
	 * If missing, it will be used the default WP page title.
	 *
	 * @return 	string 	The title.
	 */
	public function getTitle()
	{
		if (empty($this->title))
		{
			if (JFactory::getApplication()->isAdmin())
			{
				return get_admin_page_title();
			}
		}

		return $this->title;
	}

	/**
	 * Checks if the title has been set.
	 *
	 * @return 	boolean  True if set, otherwise false.
	 */
	public function hasTitle()
	{
		return !empty($this->title);
	}

	/**
	 * Returns the toolbar buttons.
	 *
	 * @return 	array 	The buttons in the bar.
	 */
	public function getButtons()
	{
		return $this->bar;
	}

	/**
	 * Checks if the toolbar contains any buttons.
	 *
	 * @return 	boolean  True if set, otherwise false.
	 */
	public function hasButtons()
	{
		return (bool) count($this->bar);
	}

	/**
	 * Appends a button to the toolbar.
	 * This method accept an undefined number of arguments.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @uses 	createButton()
	 */
	public function appendButton()
	{
		$args = func_get_args();
		$btn  = $this->createButton($args);

		if ($btn)
		{
			array_push($this->bar, $btn);
		}

		return $this;
	}

	/**
	 * Adds a button at the beginning of the toolbar.
	 * This method accept an undefined number of arguments.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @uses 	createButton()
	 */
	public function prependButton()
	{
		$args = func_get_args();
		$btn  = $this->createButton($args);

		if ($btn)
		{
			array_unshift($this->bar, $btn);
		}

		return $this;
	}

	/**
	 * Removes a button from the toolbar.
	 * 
	 * @param 	mixed  $btn  Either the position of the button or the
	 *                       button reference to delete.
	 * 
	 * @return 	mixed  The deleted button on success, false otherwise.
	 * 
	 * @since 	10.1.36
	 */
	public function removeButton($btn)
	{
		if (!is_scalar($btn))
		{
			// button instance specified, search it insider the bar
			$btn = array_search($btn, $this->bar);

			// make sure the button exists
			if ($btn === false)
			{
				return false;
			}
		}

		// detach button from array
		$deleted = array_splice($this->bar, (int) $btn, 1);

		if (!$deleted)
		{
			return false;
		}

		return $deleted[0];
	}

	/**
	 * Creates a button depending on the specified arguments.
	 *
	 * @param 	array 	$args 	The button options.
	 * 
	 * @return 	mixed 	The new button on success, otherwise null.
	 */
	protected function createButton(array $args = array())
	{
		if (!count($args))
		{
			return null;
		}

		return JToolbarButtonBase::getInstance($args);
	}
}
