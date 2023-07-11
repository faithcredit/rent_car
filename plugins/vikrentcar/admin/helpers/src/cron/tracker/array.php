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
 * VikRentCar cron job scalar-elements array tracker.
 *
 * @since 1.3.0
 */
trait VRCCronTrackerArray
{
	/**
	 * Defines the maximum number of characters that can occupy the list
	 * containing all the tracked elements. Children classes can alter
	 * this value according to their needs.
	 * 
	 * @var int
	 */
	protected $maximumCharsTrackableElements = 10000;

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
	protected function track($element)
	{
		if ($this->isTracked($element) || !is_scalar($element))
		{
			// element already tracked, avoid duplicate registration
			return false;
		}

		if (is_numeric($element))
		{
			// cast to number to save some space occupied by json_encode for strings
			$element = is_float($element) ? (float) $element : (int) $element;
		}

		// make sure this trait has been attached only to a cron job instance
		if (!$this instanceof VRCCronJob)
		{
			throw new Exception('This tracker can be attached only to a VRCCronJob instance', 500);
		}

		// register the element at the beginning of the list
		$data = $this->getData();
		array_unshift($data->flag_char, $element);

		// sanitize the elements contained within the cache in order
		// avoid a length exceeding the maximum threshold
		$this->sanitizeFlagCache($data);

		return true;
	}

	/**
	 * Checks whether the specified element has been already processed.
	 * 
	 * @param   mixed    $element  The element to check.
	 * 
	 * @return  boolean  True if already processed, false otherwise.
	 */
	protected function isTracked($element)
	{
		// make sure this trait has been attached only to a cron job instance
		if (!$this instanceof VRCCronJob)
		{
			throw new Exception('This tracker can be attached only to a VRCCronJob instance', 500);
		}
		
		return in_array($element, $this->getData()->flag_char);
	}

	/**
	 * Ensures the array with the tracked elements doesn't exceed the maximum limit.
	 * 
	 * @param   object  $data  The cron job data.
	 * 
	 * @return  void
	 */
	private function sanitizeFlagCache($data)
	{
		// count the total number of characters that occupy the cache
		$len = 0;

		foreach ($data->flag_char as $element)
		{
			// sum value with the total number of characters
			$len += strlen((string) $element);

			if (!is_numeric($element))
			{
				// we have a string element, we need to include other 2 characters reserved for wrapping
				$len += 2;
			}

			// increase the length by 1 to include also the elements separator (,)
			$len++;
		}

		if ($data->flag_char)
		{
			// remove the trailing comma separator
			$len--;
		}

		// sum also the initial and trailing brackets
		$len += 2;

		// even if the maximum number of allowed characters is 65535, we prefer to always stay under
		// 10000 elements, since json_encode may use a specific encoding for some characters
		if ($len > $this->maximumCharsTrackableElements)
		{
			// it is enough to pop the last 2 items from the list in order to stay under the desired limit,
			// since this method always triggers while registering a new element
			array_pop($data->flag_char);
			array_pop($data->flag_char);
		}
	}
}
