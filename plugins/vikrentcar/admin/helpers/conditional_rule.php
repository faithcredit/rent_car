<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2022 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Conditional Rule parent Class of all sub-classes.
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
abstract class VikRentCarConditionalRule
{
	/**
	 * The name of the rule.
	 *
	 * @var 	string
	 */
	protected $ruleName = null;

	/**
	 * The description of the rule.
	 *
	 * @var 	string
	 */
	protected $ruleDescr = '';

	/**
	 * The rule identifier.
	 *
	 * @var 	string
	 */
	protected $ruleId = null;

	/**
	 * The rule params.
	 *
	 * @var 	mixed
	 */
	protected $ruleParams = null;

	/**
	 * The booking array.
	 *
	 * @var 	array
	 */
	protected $booking = null;

	/**
	 * The car array.
	 *
	 * @var 	array
	 */
	protected $car = null;

	/**
	 * The rates array.
	 *
	 * @var 	array
	 */
	protected $rates = null;

	/**
	 * The options array.
	 *
	 * @var 	array
	 */
	protected $options = null;

	/**
	 * An extra data array.
	 *
	 * @var 	array
	 */
	protected $extra_data = null;

	/**
	 * The VRC application object.
	 *
	 * @var 	object
	 */
	protected $vrc_app = null;

	/**
	 * The date format with wildcards.
	 *
	 * @var 	string
	 */
	protected $wdf = '';

	/**
	 * The date format.
	 *
	 * @var 	string
	 */
	protected $df = '';

	/**
	 * Class constructors should define some vars for the rule in use.
	 */
	public function __construct()
	{
		$this->vrc_app = VikRentCar::getVrcApplication();
		$this->wdf = VikRentCar::getDateFormat(true);
		if ($this->wdf == "%d/%m/%Y") {
			$this->df = 'd/m/Y';
		} elseif ($this->wdf == "%m/%d/%Y") {
			$this->df = 'm/d/Y';
		} else {
			$this->df = 'Y/m/d';
		}

		$this->booking 	  = [];
		$this->car 		  = [];
		$this->rates 	  = [];
		$this->options 	  = [];
		$this->extra_data = [];
	}

	/**
	 * Gets the name of the current rule.
	 * 
	 * @return 	string 	the rule name.
	 */
	public function getName()
	{
		return $this->ruleName;
	}

	/**
	 * Gets the description for the current rule.
	 * 
	 * @return 	string 	the rule description.
	 */
	public function getDescription()
	{
		return $this->ruleDescr;
	}

	/**
	 * Gets the identifier of the current rule.
	 * 
	 * @return 	string 	the rule identifier.
	 */
	public function getIdentifier()
	{
		return $this->ruleId;
	}

	/**
	 * Sets the rule params.
	 * 
	 * @param 	mixed 	$params 	the current params of the rule.
	 * 
	 * @return 	self
	 */
	public function setParams($params = null)
	{
		$this->ruleParams = $params;

		return $this;
	}

	/**
	 * Returns the rule params.
	 * 
	 * @return 	mixed 	the current params of the rule.
	 */
	public function getParams()
	{
		return $this->ruleParams;
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
		if (is_null($this->ruleParams)) {
			return $def;
		}

		if (is_object($this->ruleParams) && isset($this->ruleParams->{$key})) {
			return $this->ruleParams->{$key};
		}

		if (is_array($this->ruleParams) && isset($this->ruleParams[$key])) {
			return $this->ruleParams[$key];
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
		if (is_null($this->ruleParams)) {
			$this->ruleParams = new stdClass;
		}

		if (is_object($this->ruleParams)) {
			$this->ruleParams->{$key} = $val;
		}

		if (is_array($this->ruleParams)) {
			$this->ruleParams[$key] = $val;
		}

		return $this;
	}

	/**
	 * Builds a unique input field name for the rule.
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
	 * Builds a unique input field ID for the rule.
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
	 * Sets the booking information array.
	 * 
	 * @param 	mixed 	$data 	the booking information array or booking ID.
	 * 
	 * @return 	self
	 */
	public function setBooking($data)
	{
		if (!is_scalar($data) && !is_array($data)) {
			$data = (array)$data;
		}

		if (is_scalar($data)) {
			// booking ID expected, fetch the booking information
			$data = VikRentCar::getBookingInfoFromID($data);
		}

		if (!is_array($data)) {
			$data = array();
		}

		$this->booking = $data;

		return $this;
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
	 * to render the params of the rule.
	 * 
	 * @return 	void
	 */
	abstract public function renderParams();

	/**
	 * Extending Classes should define this method
	 * to tell whether the rule is compliant.
	 * 
	 * @return 	bool 	True on success, false otherwise.
	 */
	abstract public function isCompliant();

	/**
	 * Extending Classes could implement this method
	 * to override it, and to execute some actions.
	 * By default, rules that do not override this method
	 * will produce no actions, they will serve as filters.
	 * 
	 * @return 	void
	 */
	public function callbackAction()
	{
		return;
	}

	/**
	 * Extending Classes could implement this method
	 * to override it, and to manipulate the message.
	 * 
	 * @param 	string 	$msg 	the conditional text message.
	 * 
	 * @return 	string
	 */
	public function manipulateMessage($msg)
	{
		return $msg;
	}
}
