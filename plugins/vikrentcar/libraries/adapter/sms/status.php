<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.sms
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class is used to wrap the details of
 * a sms response.
 *
 * @since 10.1.30
 */
class JSmsStatus
{
	/**
	 * The sms status.
	 *
	 * @var boolean
	 */
	protected $status = false;

	/**
	 * Property used to track what is happening
	 * during the validation of the dispatch.
	 *
	 * @var string
	 */
	protected $log = '';

	/**
	 * Additional data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Magic method to access the property of the object.
	 *
	 * @param 	string 	$name 	The property name.
	 *
	 * @return 	mixed 	The property value.
	 */
	public function __get($name)
	{
		if (isset($this->{$name}))
		{
			return $this->{$name};
		}
		else if (isset($this->data[$name]))
		{
			return $this->data[$name];
		}

		return null;
	}

	/**
	 * Checks whether the notification was successful.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 */
	public function isVerified()
	{
		return $this->status;
	}

	/**
	 * Marks the notification status as verified or failed.
	 *
	 * @param 	boolean  $status  True if verified (default).
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function verified($status = true)
	{
		$this->status = (bool) $status;

		return $this;
	}

	/**
	 * Tracks the given log.
	 *
	 * @param 	mixed 	$log 	A string or a non-scalar value.
	 * 							An array will be logged using print_r.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setLog($log)
	{
		if (!is_scalar($log))
		{
			// use print_r for non scalar logs
			$log = print_r($log, true);
		}

		$this->log = $log;

		return $this;
	}

	/**
	 * Appends the given log to the existing string.
	 *
	 * @param 	mixed 	$log 	A string or a non-scalar value.
	 * 							An array will be logged using print_r.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @uses 	setLog()
	 */
	public function appendLog($log, $separator = "\n")
	{
		// keep current log
		$current = $this->log;

		// set log using the proper method
		$this->setLog($log);

		// re-build the log by prepending the existing logs
		$this->log = $current . $separator . $this->log;

		return $this;
	}

	/**
	 * Prepends the given log to the existing string.
	 *
	 * @param 	mixed 	$log 	A string or a non-scalar value.
	 * 							An array will be logged using print_r.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @uses 	setLog()
	 */
	public function prependLog($log, $separator = "\n")
	{
		// keep current log
		$current = $this->log;

		// set log using the proper method
		$this->setLog($log);

		// re-build the log by appending the existing logs
		$this->log .= $separator . $current;

		return $this;
	}

	/**
	 * Registers an additional information.
	 *
	 * @param 	string 	$key  The data key.
	 * @param 	mixed 	$val  The data value.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setData($key, $val)
	{
		$this->data[$key] = $val;

		return $this;
	}
}
