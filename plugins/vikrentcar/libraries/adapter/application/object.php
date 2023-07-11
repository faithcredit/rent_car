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
 * Object adapter class.
 * This class handles simple and smart functions for objects.
 *
 * @since 10.0
 */
#[\AllowDynamicProperties]
class JObject
{
	/**
	 * An array of error messages or Exception objects.
	 *
	 * @var array
	 */
	protected $_errors = [];

	/**
	 * Class constructor, overridden in descendant classes.
	 *
	 * @param   mixed  $properties  Either an associative array or another
	 *                              object to set the initial properties of the object.
	 */
	public function __construct($properties = null)
	{
		if ($properties !== null)
		{
			$this->setProperties($properties);
		}
	}

	/**
	 * Sets a default value if not already assigned
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed   Previous value of the property.
	 *
	 * @uses 	get()
	 * @uses 	set()
	 */
	public function def($property, $default = null)
	{
		$value = $this->get($property, $default);

		return $this->set($property, $value);
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed   The value of the property.
	 */
	public function get($property, $default = null)
	{
		/**
		 * Return default value also in case the property is set
		 * and owns a NULL value or an empty string.
		 *
		 * @since 10.1.27
		 */
		if (isset($this->$property) && !is_null($this->$property) && $this->$property !== '')
		{
			return $this->$property;
		}

		return $default;
	}

	/**
	 * Returns an associative array of object properties.
	 *
	 * @param   boolean  $public  If true, returns only the public properties.
	 *
	 * @return  array 	 The internal properties.
	 */
	public function getProperties($public = true)
	{
		$vars = get_object_vars($this);

		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if ('_' == substr($key, 0, 1))
				{
					unset($vars[$key]);
				}
			}
		}

		return $vars;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set.
	 *
	 * @return  mixed   Previous value of the property.
	 *
	 * @uses 	get()
	 */
	public function set($property, $value = null)
	{
		$previous = $this->get($property, null);
		$this->$property = $value;

		return $previous;
	}

	/**
	 * Set the object properties based on a named array/hash.
	 *
	 * @param   mixed  	 $properties  Either an associative array or another object.
	 *
	 * @return  boolean  True on success, otherwise false.
	 *
	 * @uses 	set()
	 */
	public function setProperties($properties)
	{
		if (is_array($properties) || is_object($properties))
		{
			foreach ((array) $properties as $k => $v)
			{
				// Use the set function which might be overridden.
				$this->set($k, $v);
			}

			return true;
		}

		return false;
	}

	/**
	 * Get the most recent error message.
	 *
	 * @param   integer  $i         Option error index.
	 * @param   boolean  $toString  Indicates if the exception should return the error message.
	 *
	 * @return  mixed    The error message or the exception object.
	 */
	public function getError($i = null, $toString = true)
	{
		// find the error
		if ($i === null)
		{
			// default, return the last message
			$error = end($this->_errors);
		}
		else if (!array_key_exists($i, $this->_errors))
		{
			// if $i has been specified but does not exist, return false
			return false;
		}
		else
		{
			$error = $this->_errors[$i];
		}

		// check if only the string is requested
		if ($error instanceof Exception && $toString)
		{
			return $error->getMessage();
		}

		return $error;
	}

	/**
	 * Return all errors, if any.
	 *
	 * @return 	array 	Array of error messages or exceptions.
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * Pushes an error message.
	 *
	 * @param 	mixed 	$error  The error string or an exception.
	 * 							If $error is an instance of WP_Error, it will
	 * 							be adapted to a generic exception.
	 *
	 * @return  void
	 */
	public function setError($error)
	{
		if ($error instanceof WP_Error)
		{
			// adapt WP_Error object
			$error = new Exception($error->get_error_message(), (int) $error->get_error_code());
		}

		$this->_errors[] = $error;
	}
}
