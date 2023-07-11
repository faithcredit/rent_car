<?php
/** 
 * @package     VikRentCar
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * VikRentCar conversion-tracker abstraction.
 *
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
abstract class VRCConversionTracker
{
	/**
	 * The name of the driver.
	 *
	 * @var 	string
	 */
	protected $driverName = null;

	/**
	 * The description of the driver.
	 *
	 * @var 	string
	 */
	protected $driverDescr = '';

	/**
	 * The driver identifier.
	 *
	 * @var 	string
	 */
	protected $driverId = null;

	/**
	 * The driver params.
	 *
	 * @var 	mixed
	 */
	protected $driverParams = null;

	/**
	 * An extra data array.
	 *
	 * @var 	array
	 */
	protected $extra_data = [];

	/**
	 * Gets the name of the current driver.
	 * 
	 * @return 	string 	the driver name.
	 */
	public function getName()
	{
		return $this->driverName;
	}

	/**
	 * Gets the description for the current driver.
	 * 
	 * @return 	string 	the driver description.
	 */
	public function getDescription()
	{
		return $this->driverDescr;
	}

	/**
	 * Gets the identifier of the current driver.
	 * 
	 * @return 	string 	the driver identifier.
	 */
	public function getIdentifier()
	{
		return $this->driverId;
	}

	/**
	 * Sets the driver params.
	 * 
	 * @param 	mixed 	$params 	the current params of the driver.
	 * 
	 * @return 	self
	 */
	public function setParams($params = null)
	{
		$this->driverParams = $params;

		return $this;
	}

	/**
	 * Returns the driver params.
	 * 
	 * @return 	mixed 	the current params of the driver.
	 */
	public function getParams()
	{
		return $this->driverParams;
	}

	/**
	 * Gets the requested param.
	 * 
	 * @param 	string 	$key 	the param's property.
	 * @param 	mixed 	$def 	the default value to return.
	 * 
	 * @return 	mixed 	the requested param value.
	 */
	public function getParam($key, $def = null)
	{
		if (is_null($this->driverParams)) {
			return $def;
		}

		if (is_object($this->driverParams) && isset($this->driverParams->{$key})) {
			return $this->driverParams->{$key};
		}

		if (is_array($this->driverParams) && isset($this->driverParams[$key])) {
			return $this->driverParams[$key];
		}

		return $def;
	}

	/**
	 * Sets a param value.
	 * 
	 * @param 	string 	$key 	the param's property.
	 * @param 	string 	$val 	the param's value.
	 * 
	 * @return 	self
	 */
	public function setParam($key, $val)
	{
		if (is_null($this->driverParams)) {
			$this->driverParams = new stdClass;
		}

		if (is_object($this->driverParams)) {
			$this->driverParams->{$key} = $val;
		}

		if (is_array($this->driverParams)) {
			$this->driverParams[$key] = $val;
		}

		return $this;
	}

	/**
	 * Builds a unique input field name for the driver.
	 * 
	 * @param 	string 	$name 	the param property (input) name.
	 * @param 	bool 	$multi 	whether the param is an array.
	 * 
	 * @return 	string 	the name of the input field to use.
	 */
	public function inputName($name, $multi = false)
	{
		return basename($this->getIdentifier(), '.php') . "[{$name}]" . ($multi ? '[]' : '');
	}

	/**
	 * Builds a unique input field ID for the driver.
	 * 
	 * @param 	string 	$name 	the param property (input) name/id.
	 * 
	 * @return 	string 	the name of the input field to use.
	 */
	public function inputID($name)
	{
		return basename($this->getIdentifier(), '.php') . "_{$name}";
	}

	/**
	 * Helper method to populate internal properties.
	 * 
	 * @param 	mixed 	$key 	the key or array of keys to set.
	 * @param 	mixed 	$val 	the value or array of values to set.
	 * 
	 * @return 	self
	 */
	public function setProperties($key, $val = null)
	{
		if (!is_string($key) && !is_array($key)) {
			return $this;
		}

		if (is_string($key)) {
			$key = array($key);
		}
		if (!is_array($val)) {
			$val = array($val);
		}

		foreach ($key as $i => $prop) {
			if (!isset($val[$i])) {
				continue;
			}
			if (property_exists($this, $prop)) {
				$this->{$prop} = $val[$i];
			}
			$this->extra_data[$prop] = $val[$i];
		}

		return $this;
	}

	/**
	 * Gets the requested property.
	 * 
	 * @param 	string 	$key 	the param's property.
	 * @param 	mixed 	$def 	the default value to return.
	 * 
	 * @return 	mixed 			the requested param value.
	 */
	public function getProperty($key, $def = null)
	{
		if (property_exists($this, $key)) {
			return $this->{$key};
		}

		if (is_array($this->extra_data) && isset($this->extra_data[$key])) {
			return $this->extra_data[$key];
		}

		return $def;
	}

	/**
	 * Gets the requested property value.
	 * Like $this->booking['ts'] for getting the timestamp.
	 * 
	 * @param 	string 	$key 	the param's property.
	 * @param 	string 	$val 	the property value.
	 * @param 	mixed 	$def 	the default value to return.
	 * 
	 * @return 	mixed 			the requested param value.
	 */
	public function getPropVal($key, $val, $def = null)
	{
		$prop = $this->getProperty($key, $def);
		if ($prop === $def) {
			return $def;
		}

		if (is_array($prop) && isset($prop[$val])) {
			return $prop[$val];
		}

		if (is_object($prop) && isset($prop->{$val})) {
			return $prop->{$val};
		}

		return $def;
	}

	/**
	 * Extending Classes should define this method
	 * to render the params of the driver.
	 * 
	 * @return 	void
	 */
	abstract public function renderParams();

	/**
	 * Extending Classes should define this method
	 * to track a specific user event.
	 * 
	 * @param 	string 	$event 	the name of the current view/task.
	 * 
	 * @return 	void
	 */
	abstract public function trackEvent($event = null);

	/**
	 * Extending Classes should define this method
	 * to track a booking conversion.
	 * 
	 * @param 	array 	$booking 	the new booking record.
	 * 
	 * @return 	void
	 */
	abstract public function doConversion(array $booking = null);
}
