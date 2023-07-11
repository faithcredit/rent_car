<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.application
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class to maintain a pathway.
 *
 * The user's navigated path within the application.
 *
 * @since  10.1.19
 */
class JPathway
{
	/**
	 * JPathway instances container.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Array to hold the pathway item objects.
	 *
	 * @var array
	 */
	protected $pathway = array();

	/**
	 * Class constructor.
	 *
	 * @param   array  $options  The class options.
	 */
	public function __construct($options = array())
	{

	}

	/**
	 * Returns a Pathway object.
	 *
	 * @param   string  $client   The name of the client.
	 * @param   array   $options  An associative array of options.
	 *
	 * @return  self  	A Pathway object.
	 *
	 * @throws  RuntimeException
	 */
	public static function getInstance($client, $options = array())
	{
		$client = strtolower($client);

		if (empty(self::$instances[$client]))
		{
			// try to load file
			if (!JLoader::import('adapter.pathway.classes.' . $client))
			{
				throw new Exception(sprintf('Pathway [%s] not found', $client), 404);
			}

			// create a Pathway object
			$classname = 'JPathway' . ucfirst($client);

			// make sure the class exists and it is valid
			if (!class_exists($classname) || !is_subclass_of($classname, 'JPathway'))
			{
				throw new Exception(sprintf('Invalid pathway [%s] class', $classname), 500);
			}

			// instantiate the class and cache it
			self::$instances[$client] = new $classname($options);
		}

		return self::$instances[$client];
	}

	/**
	 * Returns the Pathway items array.
	 *
	 * @return  array  Array of pathway items.
	 */
	public function getPathway()
	{
		$pw = $this->pathway;

		// use array_values to reset the array keys numerically
		return array_values($pw);
	}

	/**
	 * Sets the Pathway items array.
	 *
	 * @param   array  $pathway  An array of pathway objects.
	 *
	 * @return  array  The previous pathway data.
	 */
	public function setPathway($pathway)
	{
		$oldPathway = $this->pathway;

		// set the new pathway
		$this->pathway = array_values((array) $pathway);

		return array_values($oldPathway);
	}

	/**
	 * Creates and return an array of the pathway names.
	 *
	 * @return  array  Array of names of pathway items.
	 */
	public function getPathwayNames()
	{
		$names = array();

		// build the names array using just the names of each pathway item
		foreach ($this->pathway as $item)
		{
			$names[] = $item->name;
		}

		// use array_values to reset the array keys numerically
		return array_values($names);
	}

	/**
	 * Creates and adds an item to the pathway.
	 *
	 * @param   string   $name  The name of the item.
	 * @param   string   $link  The link to the item.
	 *
	 * @return  boolean  True on success.
	 */
	public function addItem($name, $link = '')
	{
		$item = $this->makeItem($name, $link);

		if ($item)
		{
			$this->pathway[] = $item;

			return true;
		}

		return false;
	}

	/**
	 * Sets the item name.
	 *
	 * @param   integer  $id    The id of the item on which to set the name.
	 * @param   string   $name  The name to set.
	 *
	 * @return  boolean  True on success
	 */
	public function setItemName($id, $name)
	{
		if (isset($this->pathway[$id]))
		{
			$this->pathway[$id]->name = $name;

			return true;
		}

		return false;
	}

	/**
	 * Creates and returns a new pathway object.
	 *
	 * @param   string  $name  Name of the item.
	 * @param   string  $link  Link to the item.
	 *
	 * @return  object  Pathway item object.
	 */
	protected function makeItem($name, $link)
	{
		$item = new stdClass;
		$item->name = html_entity_decode($name, ENT_COMPAT, 'UTF-8');
		$item->link = $link;

		return $item;
	}
}
