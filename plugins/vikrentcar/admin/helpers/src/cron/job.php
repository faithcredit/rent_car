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
 * VikRentCar cron job abstraction.
 *
 * @since 1.3.0
 */
abstract class VRCCronJob
{
	use VRCLoggerAware;

	/**
	 * The cron object data.
	 * 
	 * @var object
	 */
	protected $data = null;

	/**
	 * The cron job driver parameters.
	 * 
	 * @var JObject
	 */
	protected $params;

	/**
	 * Debug mode flag. Always disabled by default.
	 * 
	 * @var bool
	 */
	private $debug = false;

	/**
	 * Class constructor.
	 * 
	 * @param   object  $data  The cron data.
	 */
	public function __construct($data = null)
	{
		if ($data)
		{
			if (is_numeric($data))
			{
				// ID given, fetch cron job data
				$this->data = VRCMvcModel::getInstance('cronjob')->getItem((int) $data);
			}
			else
			{
				// create a clone to avoid affecting original argument too
				$this->data = clone $data;
			}

			// wrap parameters in a registry for a better ease of use
			$this->params = new JObject($data->params);

			// avoid keeping duplicate information
			unset($this->data->params);
		}
		else
		{
			// use an empty object to avoid errors in child classes
			$this->params = new JObject();
		}
	}

	/**
	 * Returns the cron job details.
	 * 
	 * @return  object
	 */
	final public function getData()
	{
		return $this->data;
	}

	/**
	 * Returns the title of the cron job.
	 * 
	 * @return  string
	 */
	abstract public function getTitle();

	/**
	 * This method should return all the form fields required to collect the information
	 * needed for the execution of the cron job.
	 * 
	 * @return  array  An associative array of form fields.
	 */
	public function getForm()
	{
		return [];
	}

	/**
	 * Launches the process declared by the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	final public function run()
	{
		$result = $this->execute();

		$this->postflight();

		return (bool) $result;
	}

	/**
	 * Sets the debug mode.
	 * 
	 * @param   boolean  $mode  True to enable the debug mode, false otherwise.
	 * 
	 * @return  self
	 */
	final public function setDebug($mode = true)
	{
		$this->debug = (bool) $mode;

		return $this;
	}

	/**
	 * Checks whether the debug mode is on or off.
	 * 
	 * @return  boolean
	 */
	final public function isDebug()
	{
		return $this->debug;
	}

	/**
	 * Outputs the given string if we are in debug mode.
	 * 
	 * @param   string  $str        The string to output.
	 * @param   string  $separator  A separator to append.
	 * 
	 * @return  void
	 */
	protected function output($str, $separator = "\n")
	{
		if (!$this->isDebug())
		{
			// suppress output if we are not in debug mode
			return;
		}

		if (is_array($str) || is_object($str))
		{
			// convert object into a readable string
			$str = print_r($str, true);
		}

		// output string
		echo $str . $separator;
	}

	/**
	 * Helper method used to flag the specified element as tracked. This is really helpful to easily
	 * check whether a specific record has been already parsed, which can be done by passing the same
	 * element argument to the `isTracked` method.
	 * 
	 * It is recommended to register only scalar values in order to prevent an uncontrolled increase of the
	 * total length, which can arrive up to 2^16-1 characters (65535). It's up to the sub-classes to take care of 
	 * this limit, which should clean the flag_char property in order to always have less than 65536 characters.
	 * 
	 * @param   mixed    $element  The element to track.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	abstract protected function track($element);

	/**
	 * Checks whether the specified element has been already processed.
	 * 
	 * @param   mixed    $element  The element to check.
	 * 
	 * @return  boolean  True if already processed, false otherwise.
	 */
	abstract protected function isTracked($element);

	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	abstract protected function execute();

	/**
	 * Executes after processing the cron job.
	 * 
	 * @return  void
	 */
	protected function postflight()
	{
		// children classes can override this method to perform some extra queries
	}
}
