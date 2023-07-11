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
 * Implements the event dispatcher interface for the Joomla platform.
 * 
 * @since 1.15.4
 */
class VRCPlatformOrgJoomlaDispatcher implements VRCPlatformDispatcherInterface
{
	/**
	 * Make sure to load all the plugins attached to VikRentCar or E4J.
	 * This is necessary with Joomla in order to let plugins work.
	 */
	public function __construct()
	{
		JPluginHelper::importPlugin('vikrentcar');
		JPluginHelper::importPlugin('e4j');
	}

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
		$this->filter($event, $args);
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
		return JFactory::getApplication()->triggerEvent($event, $args);
	}
}
