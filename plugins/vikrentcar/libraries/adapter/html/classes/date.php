<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.html
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Extended Utility class for handling date display.
 *
 * @since 10.1.19
 */
abstract class JHtmlDate
{
	/**
	 * Function to convert a static time into a relative measurement.
	 *
	 * @param   mixed   $date    The date to convert.
	 * @param   string  $unit    The optional unit of measurement to return
	 *                           if the value of the diff is greater than one.
	 * @param   string  $time    An optional time to compare to, defaults to now.
	 * @param   string  $format  An optional format for the JHtml::date output.
	 *
	 * @return  string  The converted time string.
	 */
	public static function relative($date, $unit = null, $time = null, $format = null)
	{
		if ($time === null)
		{
			// get now
			$time = new JDate('now');
		}

		// get the difference in seconds between now and the time
		$diff = strtotime($time) - strtotime($date);

		// less than a minute
		if ($diff < 60)
		{
			return JText::_('JLIB_HTML_DATE_RELATIVE_LESSTHANAMINUTE');
		}

		// round to minutes
		$diff = round($diff / 60);

		// 1 to 59 minutes
		if ($diff < 60 || $unit === 'minute')
		{
			return JText::plural('JLIB_HTML_DATE_RELATIVE_MINUTES', $diff);
		}

		// round to hours
		$diff = round($diff / 60);

		// 1 to 23 hours
		if ($diff < 24 || $unit === 'hour')
		{
			return JText::plural('JLIB_HTML_DATE_RELATIVE_HOURS', $diff);
		}

		// round to days
		$diff = round($diff / 24);

		// 1 to 6 days
		if ($diff < 7 || $unit === 'day')
		{
			return JText::plural('JLIB_HTML_DATE_RELATIVE_DAYS', $diff);
		}

		// round to weeks
		$diff = round($diff / 7);

		// 1 to 4 weeks
		if ($diff <= 4 || $unit === 'week')
		{
			return JText::plural('JLIB_HTML_DATE_RELATIVE_WEEKS', $diff);
		}

		// over a month, return the absolute time
		return JHtml::_('date', $date, $format);
	}
}
