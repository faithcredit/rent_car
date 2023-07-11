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
 * Helper class to handle locations.
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VRCLocationHelper
{
	/**
	 * Tells whether a location is open/closed on the requested date and time.
	 * 
	 * @param 	int 	$location_id 	the ID of the location.
	 * @param 	int 	$ts 			the requested timestamp.
	 * 
	 * @return 	mixed 	false if location is found open, error string otherwise.
	 */
	public static function isTimeClosed($location_id, $ts)
	{
		if (empty($location_id) || empty($ts)) {
			return false;
		}

		$dbo = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikrentcar_places` WHERE `id`=" . (int)$location_id;
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return false;
		}
		$location = $dbo->loadAssoc();

		if (empty($location['opentime']) && empty($location['wopening'])) {
			return false;
		}

		// time format
		$nowtf = VikRentCar::getTimeFormat();

		// requested date
		$rq_time_info = getdate($ts);
		$collect_ts = ($rq_time_info['hours'] * 3600) + ($rq_time_info['minutes'] * 60);
		$day_base_ts = mktime(0, 0, 0, $rq_time_info['mon'], $rq_time_info['mday'], $rq_time_info['year']);

		$wopening = json_decode($location['wopening'], true);
		$wopening = is_array($wopening) ? $wopening : [];
		if (!empty($wopening[$rq_time_info['wday']]) && isset($wopening[$rq_time_info['wday']]['fh'])) {
			// validate opening time for this week-day
			$open_from_secs = ($wopening[$rq_time_info['wday']]['fh'] * 3600) + ($wopening[$rq_time_info['wday']]['fm'] * 60);
			$open_to_secs = ($wopening[$rq_time_info['wday']]['th'] * 3600) + ($wopening[$rq_time_info['wday']]['tm'] * 60);
			if ($open_from_secs > 0 || $open_to_secs > 0) {
				if ($open_from_secs < $open_to_secs && ($collect_ts < $open_from_secs || $collect_ts > $open_to_secs)) {
					return JText::sprintf('VRC_LOCATION_OPEN_FROM_TO', $location['name'], date($nowtf, ($day_base_ts + $open_from_secs)), date($nowtf, ($day_base_ts + $open_to_secs)));
				}
				if ($open_from_secs > $open_to_secs && $collect_ts < $open_from_secs && $collect_ts > $open_to_secs) {
					// overnight
					return JText::sprintf('VRC_LOCATION_OPEN_FROM_TO', $location['name'], date($nowtf, ($day_base_ts + $open_from_secs)), date($nowtf, ($day_base_ts + $open_to_secs)));
				}
			}
			// check breaks
			if (!empty($wopening[$rq_time_info['wday']]['breaks'])) {
				foreach ($wopening[$rq_time_info['wday']]['breaks'] as $break) {
					if (!isset($break['fh']) || !isset($break['th'])) {
						continue;
					}
					$break_from_secs = ($break['fh'] * 3600) + ($break['fm'] * 60);
					$break_to_secs = ($break['th'] * 3600) + ($break['tm'] * 60);
					if ($collect_ts > $break_from_secs && $collect_ts < $break_to_secs) {
						// the location is on break at this time
						return JText::sprintf('VRC_LOCATION_BREAK_FROM_TO', $location['name'], date($nowtf, ($day_base_ts + $break_from_secs)), date($nowtf, ($day_base_ts + $break_to_secs)));
					} else if ($break['fh'] > $break['th'] && $collect_ts < $break_from_secs && $collect_ts < $break_to_secs) {
						// overnight break, with time after midnight
						return JText::sprintf('VRC_LOCATION_BREAK_FROM_TO', $location['name'], date($nowtf, ($day_base_ts + $break_from_secs)), date($nowtf, ($day_base_ts + $break_to_secs)));
					} else if ($break['fh'] > $break['th'] && $collect_ts > $break_from_secs && $collect_ts > $break_to_secs) {
						// overnight break, with time before midnight
						return JText::sprintf('VRC_LOCATION_BREAK_FROM_TO', $location['name'], date($nowtf, ($day_base_ts + $break_from_secs)), date($nowtf, ($day_base_ts + $break_to_secs)));
					}
				}
			}

			// selected location is open on the requested date-time
			return false;
		}

		if (!empty($location['opentime'])) {
			// validate regular opening time
			$time_parts = explode('-', $location['opentime']);
			if ($time_parts[0] > 0 || $time_parts[1] > 0) {
				if ($time_parts[0] < $time_parts[1] && ($collect_ts < $time_parts[0] || $collect_ts > $time_parts[1])) {
					return JText::sprintf('VRC_LOCATION_OPEN_FROM_TO', $location['name'], date($nowtf, ($day_base_ts + $time_parts[0])), date($nowtf, ($day_base_ts + $time_parts[1])));
				}
				if ($time_parts[0] > $time_parts[1] && $collect_ts < $time_parts[0] && $collect_ts > $time_parts[1]) {
					// overnight
					return JText::sprintf('VRC_LOCATION_OPEN_FROM_TO', $location['name'], date($nowtf, ($day_base_ts + $time_parts[0])), date($nowtf, ($day_base_ts + $time_parts[1])));
				}
			}
		}

		return false;
	}
}
