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
 * Class to create and parse routes.
 *
 * @since  10.1.19
 */
abstract class JRouter
{
	/**
	 * Router instances container.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * A configuration array.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Class constructor.
	 *
	 * @param   array  $options  Array of options.
	 */
	public function __construct($options = array())
	{
		$this->options = (array) $options;
	}

	/**
	 * Returns the global Router object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   string  $client   The name of the client.
	 * @param   array   $options  An associative array of options.
	 *
	 * @return  Router  A Router object.
	 *
	 * @throws  Exception
	 */
	public static function getInstance($client, $options = array())
	{
		$client = strtolower($client);

		if (empty(self::$instances[$client]))
		{
			// try to search for a router within the plugin folder
			if (!JLoader::import($client . '.router', WP_PLUGIN_DIR))
			{
				// try to load a native file
				if (!JLoader::import('adapter.router.classes.' . $client))
				{
					throw new Exception(sprintf('Router [%s] not found', $client), 404);
				}
			}

			// create a Menu object
			$classname = 'JRouter' . ucfirst($client);

			// make sure the class exists and it is valid
			if (!class_exists($classname) || !is_subclass_of($classname, 'JRouter'))
			{
				throw new Exception(sprintf('Invalid router [%s] class', $classname), 500);
			}

			// instantiate the class and cache it
			self::$instances[$client] = new $classname($options);
		}

		return self::$instances[$client];
	}

	/**
	 * Function to convert an internal URI to a route.
	 *
	 * @param   mixed 	$url  The internal URL or an associative array.
	 *
	 * @return  mixed 	The absolute search engine friendly URL object.
	 */
	abstract public function build($url);
}
