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
 * Trait for VikRentCar classes that may hold logs.
 *
 * @since 1.3.0
 */
trait VRCLoggerAware
{
	/**
	 * A string used to hold the cron registered logs.
	 * 
	 * @var string
	 */
	private $logs = '';

	/**
	 * Sets the internal logs with the given value.
	 * In case of an object or an array, the system will encode the
	 * resulting value in JSON format.
	 * 
	 * @param   mixed  $log
	 * 
	 * @return  self
	 */
	final protected function setLog($log)
	{
		$this->logs = trim($this->format($log));

		return $this;
	}

	/**
	 * Appends the specified value to the existing logs.
	 * 
	 * @param   mixed   $log        The value to append.
	 * @param   string  $separator  The separator to use (new line by default).
	 * 
	 * @return  self
	 */
	final protected function appendLog($log, $separator = "\n")
	{
		$prev = $this->getLog();

		$this->setLog($log);

		if ($prev)
		{
			$this->setLog($prev . $separator . $this->getLog());
		}
		
		return $this;
	}

	/**
	 * Prepends the specified value to the existing logs.
	 * 
	 * @param   mixed   $log        The value to prepend.
	 * @param   string  $separator  The separator to use (new line by default).
	 * 
	 * @return  self
	 */
	final protected function prependLog($log, $separator = "\n")
	{
		$prev = $this->getLog();

		$this->setLog($log);

		if ($prev)
		{
			$this->setLog($this->getLog() . $separator . $prev);
		}
		
		return $this;
	}

	/**
	 * Returns the internal logs.
	 * 
	 * @return  string
	 */
	final public function getLog()
	{
		return $this->logs;
	}

	/**
	 * Helper method used to stringify non-scalar values.
	 * 
	 * @param   mixed   $data  The value to stringify.
	 * 
	 * @return  string  The value as a string.
	 */
	protected function format($data)
	{
		if (is_array($data) || is_object($data))
		{
			// encode given object by using JSON pretty print mask
			$data = json_encode($data, defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0);
		}

		return $data;
	}
}
