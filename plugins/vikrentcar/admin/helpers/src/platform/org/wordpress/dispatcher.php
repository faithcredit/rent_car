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
 * Implements the event dispatcher interface for the WordPress platform.
 * 
 * @since 1.3.0
 */
class VRCPlatformOrgWordpressDispatcher implements VRCPlatformDispatcherInterface
{
	/**
	 * Triggers the specified event by passing the given argument.
	 * No return value is expected here.
	 * 
	 * @param   string  $event  The event to trigger.
	 * @param   array   $args   The event arguments.
	 * 
	 * @return  void
	 */
	public function trigger($event, array $args = [])
	{
		do_action_ref_array($this->getHook($event), $args);
	}

	/**
	 * Triggers the specified event by passing the given argument.
	 * At least a return value is expected here.
	 * 
	 * @param   string  $event  The event to trigger.
	 * @param   array   $args   The event arguments.
	 * 
	 * @return  array   A list of returned values.
	 */
	public function filter($event, array $args = [])
	{
		// inject argument at the beginning of the list, which will be
		// used as return value by the WordPress filtering technique
		array_unshift($args, null);

		$return = apply_filters_ref_array($this->getHook($event), $args);

		if (is_null($return))
		{
			// no attached hooks
			return [];
		}

		// wrap returned value into an array
		return [$return];
	}

	/**
	 * Checks whether the specified event uses the Joomla notation.
	 * In that case, rebuild the event to look more similar to
	 * WordPress hooks.
	 *
	 * @param 	string  $event  The event to check.
	 *
	 * @return 	string  The modified event, if needed.
	 *
	 * @since 	1.3.2
	 */
	protected function getHook($event)
	{
		// make sure it starts with "on"
		if (preg_match("/^on[A-Z]/", $event))
		{
			// remove initial "on"
			$event = preg_replace("/^on/", '', $event);

			// remove plugin name from event and prepend it at the beginning
			$event = 'vikrentcar' . preg_replace("/vikrentcar/i", '', $event);

			// place an underscore between each camelCase
			$event = strtolower(preg_replace("/([a-z])([A-Z])/", '$1_$2', $event));
		}

		return $event;
	}
}
