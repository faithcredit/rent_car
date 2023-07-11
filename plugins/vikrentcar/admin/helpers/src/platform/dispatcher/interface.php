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
 * Declares all the event dispatcher methods that may differ between every supported platform.
 * 
 * @since 1.3.0
 */
interface VRCPlatformDispatcherInterface
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
	public function trigger($event, array $args = []);

	/**
	 * Triggers the specified event by passing the given argument.
	 * At least a return value is expected here.
	 * 
	 * @param   string  $event  The event to trigger.
	 * @param   array   $args   The event arguments.
	 * 
	 * @return  array   A list of returned values.
	 */
	public function filter($event, array $args = []);
}
