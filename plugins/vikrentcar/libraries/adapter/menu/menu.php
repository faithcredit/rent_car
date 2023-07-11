<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.mvc
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Menu class.
 *
 * @since  10.1.19
 */
abstract class JMenu
{
	/**
	 * Menu instances container.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Array to hold the menu items.
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Identifier of the default menu item (one for each language).
	 *
	 * @var array
	 */
	protected $default = null;

	/**
	 * Identifier of the active menu item.
	 *
	 * @var integer
	 */
	protected $active = 0;

	/**
	 * User object to check access levels for.
	 *
	 * @var JUser
	 */
	protected $user;

	/**
	 * Application object.
	 *
	 * @var JApplication
	 */
	protected $app;

	/**
	 * Database driver.
	 *
	 * @var JDatabase
	 */
	protected $db;

	/**
	 * Language object.
	 *
	 * @var JLanguage
	 */
	protected $language;

	/**
	 * Returns a Menu object.
	 *
	 * @param   string  $client   The name of the client.
	 * @param   array   $options  An associative array of options.
	 *
	 * @return  self    A menu object.
	 *
	 * @throws  Exception
	 */
	public static function getInstance($client, $options = array())
	{
		$client = strtolower($client);

		if (empty(self::$instances[$client]))
		{
			// try to load file
			if (!JLoader::import('adapter.menu.classes.' . $client))
			{
				throw new Exception(sprintf('Menu [%s] not found', $client), 404);
			}

			// create a Menu object
			$classname = 'JMenu' . ucfirst($client);

			// make sure the class exists and it is valid
			if (!class_exists($classname) || !is_subclass_of($classname, 'JMenu'))
			{
				throw new Exception(sprintf('Invalid menu [%s] class', $classname), 500);
			}

			// instantiate the class and cache it
			self::$instances[$client] = new $classname($options);
		}

		return self::$instances[$client];
	}

	/**
	 * Class constructor.
	 *
	 * @param   array  $options  An array of configuration options.
	 *
	 * @uses 	load()
	 */
	public function __construct($options = array())
	{
		// load the menu items
		$this->load();

		foreach ($this->items as $item)
		{
			if ($item->home)
			{
				$this->default[trim($item->language)] = $item->id;
			}
		}

		// retrieve user from options
		if (isset($options['user']) && $options['user'] instanceof JUser)
		{
			$this->user = $options['user'];
		}
		else
		{
			$this->user = JFactory::getUser();
		}

		// retrieve app from options
		if (isset($options['app']) && $options['app'] instanceof JApplication)
		{
			$this->app = $options['app'];
		}
		else
		{
			$this->app = JFactory::getApplication();
		}

		// retrieve database from options
		if (isset($options['db']) && $options['db'] instanceof JDatabase)
		{
			$this->db = $options['db'];
		}
		else
		{
			$this->db = JFactory::getDbo();
		}

		// retrieve language from options
		if (isset($options['app']) && $options['app'] instanceof JLanguage)
		{
			$this->language = $options['language'];
		}
		else
		{
			$this->language = JFactory::getLanguage();
		}
	}

	/**
	 * Gets menu item by id.
	 *
	 * @param   integer  $id  The item id.
	 *
	 * @return  mixed 	 The item object if the ID exists or null if not found.
	 */
	public function getItem($id)
	{
		$result = null;

		if (isset($this->items[$id]))
		{
			$result = &$this->items[$id];
		}

		return $result;
	}

	/**
	 * Sets the default item by id and language code.
	 *
	 * @param   integer  $id        The menu item id.
	 * @param   string   $language  The language code.
	 *
	 * @return  boolean  True if a menu item with the given ID exists.
	 */
	public function setDefault($id, $language = '*')
	{
		if (isset($this->items[$id]))
		{
			$this->default[$language] = $id;

			return true;
		}

		return false;
	}

	/**
	 * Gets the default item by language code.
	 *
	 * @param   string  $language  The language code, default value of * means all.
	 *
	 * @return  mixed 	The item object or null when not found for given language.
	 */
	public function getDefault($language = '*')
	{
		if (array_key_exists($language, $this->default))
		{
			return $this->items[$this->default[$language]];
		}

		if (array_key_exists('*', $this->default))
		{
			return $this->items[$this->default['*']];
		}

		return null;
	}

	/**
	 * Sets the default item by id.
	 *
	 * @param   integer  $id  	The item id.
	 *
	 * @return  mixed 	 The menu item representing the given ID if present or null otherwise.
	 */
	public function setActive($id)
	{
		if (isset($this->items[$id]))
		{
			$this->active = $id;

			return $this->items[$id];
		}

		return;
	}

	/**
	 * Gets menu item by id.
	 *
	 * @return  mixed  The item object if an active menu item has been set or null.
	 */
	public function getActive()
	{
		if ($this->active)
		{
			return $this->items[$this->active];
		}

		return null;
	}

	/**
	 * Gets menu items by attribute.
	 *
	 * @param   mixed    $attributes  The field name(s).
	 * @param   mixed    $values      The value(s) of the field. If an array, need to match field names
	 *                                each attribute may have multiple values to lookup for.
	 * @param   boolean  $firstonly   If true, only returns the first item found.
	 *
	 * @return  mixed 	 An array of menu items or a single object if the $firstonly parameter is true.
	 */
	public function getItems($attributes, $values, $firstonly = false)
	{
		$items = array();
		$attributes = (array) $attributes;
		$values = (array) $values;
		$count = count($attributes);

		foreach ($this->items as $item)
		{
			if (!is_object($item))
			{
				continue;
			}

			$test = true;

			for ($i = 0; $i < $count; $i++)
			{
				if (is_array($values[$i]))
				{
					if (!in_array($item->{$attributes[$i]}, $values[$i]))
					{
						$test = false;
						break;
					}
				}
				else
				{
					if ($item->{$attributes[$i]} != $values[$i])
					{
						$test = false;
						break;
					}
				}
			}

			if ($test)
			{
				if ($firstonly)
				{
					return $item;
				}

				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Gets the parameter object for a certain menu item.
	 *
	 * @param   integer  $id  The item id.
	 *
	 * @return  JRegistry
	 */
	public function getParams($id)
	{
		if ($menu = $this->getItem($id))
		{
			return $menu->params;
		}

		return new Registry();
	}

	/**
	 * Getter for the menu array.
	 *
	 * @return  array
	 */
	public function getMenu()
	{
		return $this->items;
	}

	/**
	 * Method to check Menu object authorization against an access control
	 * object and optionally an access extension object.
	 *
	 * @param   integer  $id  The menu id.
	 *
	 * @return  boolean
	 */
	public function authorise($id)
	{
		$menu = $this->getItem($id);

		if ($menu)
		{
			return in_array((int) $menu->access, (array) $this->user->getAuthorisedViewLevels());
		}

		return true;
	}

	/**
	 * Loads the menu items.
	 *
	 * @return  array
	 */
	abstract public function load();
}
