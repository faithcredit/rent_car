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

JLoader::import('adapter.input.input');

/**
 * This is an abstracted input class used to handle retrieving data 
 * from the FILES environment.
 *
 * @since 10.0
 *
 * @property-read    Input   $get
 * @property-read    Input   $post
 * @property-read    Input   $request
 * @property-read    Input   $server
 * @property-read    Files   $files
 * @property-read    Cookie  $cookie
 *
 * @method      integer  getInt($name, $default = null)       Get a signed integer.
 * @method      integer  getUint($name, $default = null)      Get an unsigned integer.
 * @method      float    getFloat($name, $default = null)     Get a floating-point number.
 * @method      boolean  getBool($name, $default = null)      Get a boolean value.
 * @method      string   getWord($name, $default = null)      Get a word.
 * @method      string   getAlnum($name, $default = null)     Get an alphanumeric string.
 * @method      string   getCmd($name, $default = null)       Get a CMD filtered string.
 * @method      string   getBase64($name, $default = null)    Get a base64 encoded string.
 * @method      string   getString($name, $default = null)    Get a string.
 * @method      string   getHtml($name, $default = null)      Get a HTML string.
 * @method      string   getPath($name, $default = null)      Get a file path.
 * @method      string   getUsername($name, $default = null)  Get a username.
 */
class JInputFiles extends JInput
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $source   Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   array  $options  An optional associative array of configuration parameters.
	 */
	public function __construct($source = null, array $options = array())
	{
		parent::__construct($_FILES, $options);
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     The name of the input property (usually the name of the files INPUT tag) to get.
	 * @param   mixed   $default  The default value to return if the named property does not exist.
	 *
	 * @return  mixed   The filtered input value.
	 */
	public function get($name, $default = null, $filter = 'array')
	{
		if (isset($this->data[$name]))
		{
			return $this->decodeData(
				array(
					$this->data[$name]['name'],
					$this->data[$name]['type'],
					$this->data[$name]['tmp_name'],
					$this->data[$name]['error'],
					$this->data[$name]['size'],
				)
			);
		}

		return $default;
	}

	/**
	 * Method to decode a data array.
	 *
	 * @param   array  $data  The data array to decode.
	 *
	 * @return  array
	 *
	 * @since   10.1.16
	 */
	protected function decodeData(array $data)
	{
		$result = array();

		if (is_array($data[0]))
		{
			foreach ($data[0] as $k => $v)
			{
				$result[$k] = $this->decodeData(array($data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]));
			}

			return $result;
		}

		return array('name' => $data[0], 'type' => $data[1], 'tmp_name' => $data[2], 'error' => $data[3], 'size' => $data[4]);
	}

	/**
	 * Sets a value.
	 *
	 * @param   string  $name   The name of the input property to set.
	 * @param   mixed   $value  The value to assign to the input property.
	 *
	 * @return  void
	 */
	public function set($name, $value)
	{
		// restricts the usage of parent's set method
	}
}
