<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.date
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * JDate is a class that stores a date and provides logic to manipulate
 * and render that date in a variety of formats.
 *
 * @since 10.0
 */
class JDate extends DateTime
{
	const DAY_ABBR = "\x021\x03";
	const DAY_NAME = "\x022\x03";
	const MONTH_ABBR = "\x023\x03";
	const MONTH_NAME = "\x024\x03";

	/**
	 * The format string to be applied when using the __toString() magic method.
	 *
	 * @var   string
	 * @since 10.1.19
	 */
	public static $format = 'Y-m-d H:i:s';

	/**
	 * Placeholder for a DateTimeZone object with GMT as the time zone.
	 *
	 * @var object
	 */
	protected static $gmt;

	/**
	 * Placeholder for a DateTimeZone object with the default server
	 * time zone as the time zone. This timezone might be altered
	 * using the `vik_date_default_timezone` hook.
	 *
	 * @var object
	 */
	protected static $stz;

	/**
	 * The default server timezone.
	 *
	 * @var   string
	 * @since 10.1.21
	 */
	protected static $default;

	/**
	 * The DateTimeZone object for usage in reading dates as strings.
	 *
	 * @var DateTimeZone
	 */
	protected $tz;

	/**
	 * Class constructor.
	 *
	 * @param   string  $date  String in a format accepted by strtotime(), defaults to "now".
	 * @param   mixed   $tz    Time zone to be used for the date. Might be a string or a DateTimeZone object.
	 */
	public function __construct($date = 'now', $tz = null)
	{
		// attempt to initialize static properties
		static::getDefaultTimezone();

		// if the time zone object is not set, attempt to build it
		if (!($tz instanceof DateTimeZone))
		{
			if ($tz === null)
			{
				$tz = self::$gmt;
			}
			else if (is_string($tz))
			{
				$tz = new DateTimeZone($tz);
			}
		}

		// if the date is numeric assume a unix timestamp and convert it
		date_default_timezone_set('UTC');
		$date = is_numeric($date) ? date('c', $date) : $date;

		// call the DateTime constructor
		parent::__construct($date, $tz);

		// reset the timezone for 3rd party libraries/extension that does not use JDate
		date_default_timezone_set(self::$stz->getName());

		// Set the timezone object for access later.
		$this->tz = $tz;
	}

	/**
	 * Proxy for new JDate().
	 *
	 * @param   string  $date  String in a format accepted by strtotime(), defaults to "now".
	 * @param   mixed   $tz    Time zone to be used for the date.
	 *
	 * @return  JDate
	 */
	public static function getInstance($date = 'now', $tz = null)
	{
		return new JDate($date, $tz);
	}

	/**
	 * Method to wrap the setTimezone() function and set the internal time zone object.
	 *
	 * @param   DateTimeZone  $tz  The new DateTimeZone object.
	 *
	 * @return  JDate
	 *
	 * @note    DO NOT type hint $tz due to a PHP bug: https://bugs.php.net/bug.php?id=61483
	 */
	#[ReturnTypeWillChange]
	public function setTimezone($tz)
	{
		$this->tz = $tz;

		return parent::setTimezone($tz);
	}

	/**
	 * Gets the date as an ISO 8601 string.  IETF RFC 3339 defines the ISO 8601 format
	 * and it can be found at the IETF Web site.
	 *
	 * @param   boolean  $local  True to return the date string in the local time zone, false to return it in GMT.
	 *
	 * @return  string  The date string in ISO 8601 format.
	 *
	 * @link    http://www.ietf.org/rfc/rfc3339.txt
	 *
	 * @since   10.1.35
	 */
	public function toISO8601($local = false)
	{
		return $this->format(DateTime::RFC3339, $local, false);
	}

	/**
	 * Gets the date as an SQL datetime string.
	 *
	 * @param   boolean  $local  True to return the date string in the local time zone, false to return it in GMT.
	 * @param   mixed  	 $db     The database driver or null to use JFactory::getDbo().
	 *
	 * @return  string   The date string in SQL datetime format.
	 *
	 * @link    http://dev.mysql.com/doc/refman/5.0/en/datetime.html
	 *
	 * @uses 	format()
	 */
	public function toSql($local = false, $dbo = null)
	{
		if ($dbo === null)
		{
			$dbo = JFactory::getDbo();
		}

		return $this->format($dbo->getDateFormat(), $local, false);
	}

	/**
	 * Gets the date as a formatted string.
	 *
	 * @param   string   $format     The date format specification string (see {@link PHP_MANUAL#date}).
	 * @param   boolean  $local      True to return the date string in the local time zone, false to return it in GMT.
	 * @param   boolean  $translate  True to translate localised strings.
	 *
	 * @return  string   The date string in the specified format format.
	 */
	#[ReturnTypeWillChange]
	public function format($format, $local = false, $translate = true)
	{
		/**
		 * Translate names of days and months.
		 *
		 * @since 10.1.28
		 */
		if ($translate)
		{
			// Do string replacements for date format options that can be translated.
			$format = preg_replace('/(^|[^\\\])D/', "\\1" . self::DAY_ABBR, $format);
			$format = preg_replace('/(^|[^\\\])l/', "\\1" . self::DAY_NAME, $format);
			$format = preg_replace('/(^|[^\\\])M/', "\\1" . self::MONTH_ABBR, $format);
			$format = preg_replace('/(^|[^\\\])F/', "\\1" . self::MONTH_NAME, $format);
		}

		// if the returned time should not be local use GMT
		if ($local == false && !empty(self::$gmt))
		{
			parent::setTimezone(self::$gmt);
		}

		// format the date
		$return = parent::format($format);

		if ($translate)
		{
			// Manually modify the month and day strings in the formatted time.
			if (strpos($return, self::DAY_ABBR) !== false)
			{
				$return = str_replace(self::DAY_ABBR, $this->dayToString(parent::format('w'), true), $return);
			}

			if (strpos($return, self::DAY_NAME) !== false)
			{
				$return = str_replace(self::DAY_NAME, $this->dayToString(parent::format('w')), $return);
			}

			if (strpos($return, self::MONTH_ABBR) !== false)
			{
				$return = str_replace(self::MONTH_ABBR, $this->monthToString(parent::format('n'), true), $return);
			}

			if (strpos($return, self::MONTH_NAME) !== false)
			{
				$return = str_replace(self::MONTH_NAME, $this->monthToString(parent::format('n')), $return);
			}
		}

		if ($local == false && !empty($this->tz))
		{
			parent::setTimezone($this->tz);
		}

		return $return;
	}

	/**
	 * Translates day of week number to a string.
	 *
	 * @param   integer  $day   The numeric day of the week.
	 * @param   boolean  $abbr  True to return the abbreviated day string.
	 *
	 * @return  string  The day of the week.
	 *
	 * @since   10.1.16
	 */
	public function dayToString($day, $abbr = false)
	{
		switch ($day)
		{
			case 0:
				return $abbr ? __('Sun') : __('Sunday');
			case 1:
				return $abbr ? __('Mon') : __('Monday');
			case 2:
				return $abbr ? __('Tue') : __('Tuesday');
			case 3:
				return $abbr ? __('Wed') : __('Wednesday');
			case 4:
				return $abbr ? __('Thu') : __('Thursday');
			case 5:
				return $abbr ? __('Fri') : __('Friday');
			case 6:
				return $abbr ? __('Sat') : __('Saturday');
		}
	}

	/**
	 * Translates month number to a string.
	 *
	 * @param   integer  $month  The numeric month of the year.
	 * @param   boolean  $abbr   If true, return the abbreviated month string
	 *
	 * @return  string  The month of the year.
	 *
	 * @since   10.1.28
	 */
	public function monthToString($month, $abbr = false)
	{
		switch ($month)
		{
			case 1:
				return $abbr ? _x('Jan', 'January abbreviation') : __('January');
			case 2:
				return $abbr ? _x('Feb', 'February abbreviation') : __('February');
			case 3:
				return $abbr ? _x('Mar', 'March abbreviation') : __('March');
			case 4:
				return $abbr ? _x('Apr', 'April abbreviation') : __('April');
			case 5:
				return $abbr ? _x('May', 'May abbreviation') : __('May');
			case 6:
				return $abbr ? _x('Jun', 'June abbreviation') : __('June');
			case 7:
				return $abbr ? _x('Jul', 'July abbreviation') : __('July');
			case 8:
				return $abbr ? _x('Aug', 'August abbreviation') : __('August');
			case 9:
				return $abbr ? _x('Sep', 'September abbreviation') : __('September');
			case 10:
				return $abbr ? _x('Oct', 'October abbreviation') : __('October');
			case 11:
				return $abbr ? _x('Nov', 'November abbreviation') : __('November');
			case 12:
				return $abbr ? _x('Dec', 'December abbreviation') : __('December');
		}
	}

	/**
	 * Keeps the current standard timezone if not set and returns it.
	 * 
	 * @return 	string 	The standard timezone name.
	 * 
	 * @since 	10.1.3
	 */
	public static function getDefaultTimezone()
	{
		// create the base GMT and server time zone objects
		if (empty(self::$gmt) || empty(self::$stz))
		{
			self::$default = @date_default_timezone_get();

			/**
			 * Hook used to set a default timezone, instead of using the one defined by the server.
			 *
			 * @param 	string  The timezone string.
			 *
			 * @since 	10.1.21
			 */
			self::$stz = apply_filters('vik_date_default_timezone', self::$default);

			if (!self::$stz instanceof DateTimeZone)
			{
				self::$stz = new DateTimeZone(self::$stz);
			}

			self::$gmt = new DateTimeZone('GMT');
		}

		return self::$default;
	}

	/**
	 * Magic method to render the date object in the format specified in the public
	 * static member Date::$format.
	 *
	 * @return  string  The date as a formatted string.
	 *
	 * @since   10.1.19
	 */
	public function __toString()
	{
		return (string) parent::format(self::$format);
	}
}
