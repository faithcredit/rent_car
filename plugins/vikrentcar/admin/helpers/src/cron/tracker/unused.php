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
 * VikRentCar cron job adapter for those classes that doesn't need to track elements.
 *
 * @since 1.3.0
 */
trait VRCCronTrackerUnused
{
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
		// do not track elements
		return false;
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
		// unable to track elements
		return false;
	}
}
