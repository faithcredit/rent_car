<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.input
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.input.filter');

/**
 * This is an abstracted input class used to handle retrieving data 
 * from the application environment.
 *
 * @since 10.0
 *
 * @property-read  Input   $get
 * @property-read  Input   $post
 * @property-read  Input   $request
 * @property-read  Input   $server
 * @property-read  Input   $env
 * @property-read  Files   $files
 * @property-read  Cookie  $cookie
 *
 * @method  integer  getInt($name, $default = null)       Get a signed integer.
 * @method  integer  getUint($name, $default = null)      Get an unsigned integer.
 * @method  float    getFloat($name, $default = null)     Get a floating-point number.
 * @method  boolean  getBool($name, $default = null)      Get a boolean value.
 * @method  string   getWord($name, $default = null)      Get a word.
 * @method  string   getAlnum($name, $default = null)     Get an alphanumeric string.
 * @method  string   getCmd($name, $default = null)       Get a CMD filtered string.
 * @method  string   getBase64($name, $default = null)    Get a base64 encoded string.
 * @method  string   getString($name, $default = null)    Get a string.
 * @method  string   getHtml($name, $default = null)      Get a HTML string.
 * @method  string   getPath($name, $default = null)      Get a file path.
 * @method  string   getUsername($name, $default = null)  Get a username.
 */
class JInput
{
	/**
	 * A list containing all the allowed superglobals.
	 *
	 * @var    array
	 * @since  10.1.24
	 */
	private static $allowedGlobals = array('REQUEST', 'GET', 'POST', 'FILES', 'SERVER', 'ENV');

	/**
	 * The input data source.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * An handler to filter data.
	 *
	 * @var JInputFilter
	 */
	protected $filter;

	/**
	 * An array of options.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * A list containing all the input loaded.
	 *
	 * @var array
	 */
	protected $inputs = array();

	/**
	 * Class constructor.
	 *
	 * @param   array  $source   Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   array  $options  An optional associative array of configuration parameters.
	 *                           filter: An instance of JInputFilter. If omitted, a default filter is initialised.
	 */
	public function __construct(&$source = null, array $options = array())
	{
		if (isset($options['filter']) && $options['filter'] instanceof JInputFilter)
		{
			$this->filter = $options['filter'];
		}
		else
		{
			$this->filter = new JInputFilter;
		}

		if (is_null($source))
		{
			$this->data = &$_REQUEST;
		}
		else
		{
			$this->data = &$source;
		}

		$this->options = $options;
	}

	/**
	 * Magic method to get an input object.
	 *
	 * @param   mixed   $name  Name of the input object to retrieve.
	 *
	 * @return  JInput  The request input object.
	 */
	public function __get($name)
	{
		// always use UPPERCASE notation
		$name = strtoupper($name);

		// check if the input has been already loaded
		if (isset($this->inputs[$name]))
		{
			return $this->inputs[$name];
		}

		$className = 'JInput' . ucfirst($name);

		// otherwise try to load the file and check if the class handler exists
		if (JLoader::import('adapter.input.classes.' . strtolower($name)) && class_exists($className))
		{
			$this->inputs[$name] = new $className(null, $this->options);

			return $this->inputs[$name];
		}

		// otherwise check for an existing superglobal
		$superGlobal = '_' . $name;

		/**
		 * Make sure the superglobal is allowed before accessing it.
		 *
		 * @since 10.1.24
		 */
		if (in_array($name, static::$allowedGlobals, true) && isset($GLOBALS[$superGlobal]))
		{
			$this->inputs[$name] = new JInput($GLOBALS[$superGlobal], $this->options);

			return $this->inputs[$name];
		}

		throw new Exception('The input handler [' . $name . '] does not exist', 404);
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   string  $name       Name of the filter type prefixed with 'get'.
	 * @param   array   $arguments  [0] The name of the variable [1] The default value.
	 *
	 * @return  mixed   The filtered input value.
	 *
	 * @uses 	get()
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get')
		{
			$filter = substr($name, 3);

			return $this->get($arguments[0], isset($arguments[1]) ? $arguments[1] : null, $filter);
		}

		throw new Exception('Call to undefined method ' . __CLASS__ . '::' . $name . '()', 404);
	}

	/**
	 * Get the number of variables.
	 *
	 * @return  integer  The number of variables in the input.
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  mixed 	The filtered input value.
	 */
	public function get($name, $default = null, $filter = 'cmd')
	{
		if (isset($this->data[$name]))
		{
			return $this->filter->clean($this->data[$name], $filter);
		}
		else if ($name == 'Itemid')
		{
			return (int) url_to_postid(JUri::current());
		}

		return $default;
	}

	/**
	 * Gets the original array of values from the request.
	 *
	 * @return 	array 	The values array.
	 */
	public function getArray()
	{
		return $this->data;
	}

	/**
	 * Sets a value into the input data.
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 */
	public function set($name, $value)
	{
		if ($value !== null)
		{
			$this->data[$name] = $value;
		}
		else
		{
			/**
			 * Delete argument directly when value is NULL.
			 *
			 * @since 10.1.24
			 */
			$this->delete($name);
		}
	}

	/**
	 * Defines a value. The value will only be set if there's 
	 * no value for the name or if it is null.
	 *
	 * @param 	string 	$name 	Name of the value to define.
	 * @param 	mixed 	$value 	Value to assign to the input.
	 *
	 * @return 	void
	 *
	 * @uses 	exists()
	 * @uses 	set()
	 */
	public function def($name, $value)
	{
		if (!$this->exists($name))
		{
			$this->set($name, $value);
		}
	}

	/**
	 * Deletes a value from the input data, if any.
	 *
	 * NOTE: this method is not available in Joomla!
	 *
	 * @param   string  $name   Name of the value to unset.
	 *
	 * @return  void
	 *
	 * @uses 	exists()
	 *
	 * @since 	10.1.24
	 */
	public function delete($name)
	{
		if ($this->exists($name))
		{
			unset($this->data[$name]);
		}
	}

	/**
	 * Checks if a value name exists.
	 *
	 * @param   string  $name  The value name.
	 *
	 * @return 	boolean
	 */
	public function exists($name)
	{
		return isset($this->data[$name]);
	}
}
