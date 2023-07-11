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
 * Helper class to parse iCal calendars.
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VRCCalendarIcal
{
	public $todo_count = 0;
	public $event_count = 0;
	public $cal = [];
	private $_lastKeyWord;

	private $site_tz_id;

	/**
	 * Helper static method to quickly fetch the name of an iCal calendar.
	 * 
	 * @param 	int 	$ical_id 	the ID of the calendar.
	 * 
	 * @return 	array 				the record array or an empty array.
	 */
	public static function getCalendarName($ical_id)
	{
		$db = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikrentcar_cars_icals` WHERE `id`=" . (int)$ical_id;
		$db->setQuery($q, 0, 1);
		$calendar = $db->loadAssoc();

		if (!$calendar) {
			return [];
		}

		return $calendar;
	}

	/**
	 * Helper static method to get a list of iCal calendars used.
	 * 
	 * @return 	array 	associative list of calendars used or not.
	 */
	public static function getAllCalendarsUsed()
	{
		$db = JFactory::getDbo();

		$used_calendars = [];

		$q = "SELECT `id_ical` FROM `#__vikrentcar_orders` WHERE `id_ical` IS NOT NULL GROUP BY `id_ical`;";
		$db->setQuery($q);
		$order_calendars = $db->loadAssocList();

		foreach ($order_calendars as $ocal) {
			$used_calendars[] = $ocal['id_ical'];
		}

		$q = "SELECT * FROM `#__vikrentcar_cars_icals` ORDER BY `name` ASC;";
		$db->setQuery($q);
		$calendars = $db->loadAssocList();

		foreach ($calendars as $calendar) {
			if (in_array($calendar['id'], $used_calendars)) {
				unset($used_calendars[array_search($calendar['id'], $used_calendars)]);
			}
		}

		foreach ($used_calendars as $used_calendar) {
			// push the no longer existing calendar to the list
			$calendars[] = [
				'id' 	=> $used_calendar,
				'idcar' => null,
				'name' 	=> ('#' . $used_calendar),
				'url' 	=> null,
			];
		}

		return $calendars;
	}

	/**
	 * Creates the iCal-Object
	 *
	 * @param string $filename The path to the iCal-file or the iCal buffer
	 *
	 * @return Object The iCal-Object
	 */
	public function __construct($filename)
	{
		// get the website configured timezone offset 
		$this->site_tz_id = JFactory::getApplication()->get('offset');

		if (empty($filename)) {
			return false;
		}

		if (is_file($filename)) {
			// path to local file given
			$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		} else {
			// iCal buffer given
			$lines = preg_split("/\r\n|\n|\r/", $filename);
		}

		// unfold lines
		$lines = $this->unfoldLines($lines);

		if (stristr($lines[0], 'BEGIN:VCALENDAR') === false) {
			return false;
		}

		foreach ($lines as $k => $line) {
			$add = true;
			if ($line[0] == " ") {
				$add = false;
			}
			$line = trim($line);
			if ($add) {
				$add = $this->keyValueFromString($line);
			}
			if ($add === false) {
				$this->addCalendarComponentWithKeyAndValue($type, false, $line);
				continue;
			}

			list($keyword, $value) = $add;

			switch ($line) {
				case "BEGIN:VTODO":
					$this->todo_count++;
					$type = "VTODO";
					break;
				case "BEGIN:VEVENT":
					$this->event_count++;
					$type = "VEVENT";
					break;
				case "BEGIN:VCALENDAR":
				case "BEGIN:DAYLIGHT":
				case "BEGIN:VTIMEZONE":
				case "BEGIN:STANDARD":
					$type = $value;
					break;
				case "END:VTODO":
				case "END:VEVENT":
				case "END:VCALENDAR":
				case "END:DAYLIGHT":
				case "END:VTIMEZONE":
				case "END:STANDARD":
					$type = "VCALENDAR";
					break;
				default:
					$this->addCalendarComponentWithKeyAndValue($type, $keyword, $value);
					break;
			}
		}

		return $this->cal;
	}

	/**
	 * Add to $this->ical array one value and key.
	 *
	 * @param string $component This could be VTODO, VEVENT, VCALENDAR, ...
	 * @param string $keyword   The keyword, for example DTSTART
	 * @param string $value     The value, for example 20110105T090000Z
	 *
	 * @return void
	 */
	public function addCalendarComponentWithKeyAndValue($component, $keyword, $value)
	{
		if ($keyword == false) {
			$keyword = $this->last_keyword;
			switch ($component) {
				case 'VEVENT':
					$value = $this->cal[$component][$this->event_count - 1][$keyword] . $value;
					break;
				case 'VTODO' :
					$value = $this->cal[$component][$this->todo_count - 1][$keyword] . $value;
					break;
			}
		}

		if (stristr($keyword, "DTSTART") or stristr($keyword, "DTEND")) {
			$keyword = explode(";", $keyword);
			$keyword = $keyword[0];
		}

		switch ($component) {
			case "VTODO":
				$this->cal[$component][$this->todo_count - 1][$keyword] = $value;
				break;
			case "VEVENT":
				$this->cal[$component][$this->event_count - 1][$keyword] = $value;
				break;
			default:
				$this->cal[$component][$keyword] = $value;
				break;
		}

		$this->last_keyword = $keyword;
	}

	/**
	 * Get a key-value pair of a string.
	 *
	 * @param string $text which is like "VCALENDAR:Begin" or "LOCATION:"
	 *
	 * @return array array("VCALENDAR", "Begin")
	 */
	public function keyValueFromString($text)
	{
		preg_match("/([^:]+)[:]([\w\W]*)/", $text, $matches);

		if (!count($matches)) {
			return false;
		}

		$matches = array_splice($matches, 1, 2);

		return $matches;
	}

	/**
	 * Return Unix timestamp from ical date time format
	 *
	 * @param string $icalDate A Date in the format YYYYMMDD[T]HHMMSS[Z] or
	 *                           YYYYMMDD[T]HHMMSS
	 *
	 * @return int
	 */
	public function iCalDateToUnixTimestamp($icalDate)
	{
		if (strpos($icalDate, 'Z') !== false) {
			// date-time string is in UTC
			$dtime = new DateTime($icalDate);
			$dtime->setTimezone(new DateTimeZone($this->site_tz_id));
			return $dtime->getTimestamp();
		}

		$icalDate = str_replace('T', '', $icalDate);
		$icalDate = str_replace('Z', '', $icalDate);

		$pattern  = '/([0-9]{4})';   // 1: YYYY
		$pattern .= '([0-9]{2})';    // 2: MM
		$pattern .= '([0-9]{2})';    // 3: DD
		$pattern .= '([0-9]{0,2})';  // 4: HH
		$pattern .= '([0-9]{0,2})';  // 5: MM
		$pattern .= '([0-9]{0,2})/'; // 6: SS
		preg_match($pattern, $icalDate, $date);

		// Unix timestamp can't represent dates before 1970
		if ($date[1] <= 1970) {
			return false;
		}
		// Unix timestamps after 03:14:07 UTC 2038-01-19 might cause an overflow
		// if 32 bit integers are used.
		$timestamp = mktime((int)$date[4], (int)$date[5], (int)$date[6], (int)$date[2], (int)$date[3], (int)$date[1]);

		return $timestamp;
	}

	/**
	 * Returns an array of arrays with all events. Every event is an associative
	 * array and each property is a key-element with the related value.
	 *
	 * @return array
	 */
	public function events()
	{
		$pool = !empty($this->cal) ? $this->cal : [];
		return array_key_exists('VEVENT', $pool) ? $pool['VEVENT'] : [];
	}

	/**
	 * Returns a boolean value whether thr current calendar has events or not
	 *
	 * @return boolean
	 */
	public function hasEvents()
	{
		return (count($this->events()) > 0);
	}

	/**
	 * Returns false when the current calendar has no events in range, else the
	 * events.
	 *
	 * Note that this function makes use of a UNIX timestamp. This might be a
	 * problem on January the 29th, 2038.
	 * See http://en.wikipedia.org/wiki/Unix_time#Representing_the_number
	 *
	 * @param boolean $rangeStart Either true or false
	 * @param boolean $rangeEnd   Either true or false
	 *
	 * @return mixed
	 */
	public function eventsFromRange($rangeStart = false, $rangeEnd = false)
	{
		$events = $this->sortEventsWithOrder($this->events(), SORT_ASC);

		if (!$events) {
			return false;
		}

		$extendedEvents = array();

		if ($rangeStart !== false) {
			$rangeStart = new DateTime();
		}

		if ($rangeEnd !== false or $rangeEnd <= 0) {
			$rangeEnd = new DateTime('2038/01/18');
		} else {
			$rangeEnd = new DateTime($rangeEnd);
		}

		$rangeStart = $rangeStart->format('U');
		$rangeEnd   = $rangeEnd->format('U');

		// loop through all events by adding two new elements
		foreach ($events as $anEvent) {
			$timestamp = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
			if ($timestamp >= $rangeStart && $timestamp <= $rangeEnd) {
				$extendedEvents[] = $anEvent;
			}
		}

		return $extendedEvents;
	}

	/**
	 * Returns a boolean value whether thr current calendar has events or not
	 *
	 * @param array $events    An array with events.
	 * @param array $sortOrder Either SORT_ASC, SORT_DESC, SORT_REGULAR,
	 *                           SORT_NUMERIC, SORT_STRING
	 *
	 * @return boolean
	 */
	public function sortEventsWithOrder($events, $sortOrder = SORT_ASC)
	{
		$extendedEvents = array();

		// loop through all events by adding two new elements
		foreach ($events as $anEvent) {
			if (!array_key_exists('UNIX_TIMESTAMP', $anEvent)) {
				$anEvent['UNIX_TIMESTAMP'] = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
			}

			if (!array_key_exists('REAL_DATETIME', $anEvent)) {
				$anEvent['REAL_DATETIME'] = date("d.m.Y", $anEvent['UNIX_TIMESTAMP']);
			}
				
			$extendedEvents[] = $anEvent;
		}

		foreach ($extendedEvents as $key => $value) {
			$timestamp[$key] = $value['UNIX_TIMESTAMP'];
		}
		array_multisort($timestamp, $sortOrder, $extendedEvents);

		return $extendedEvents;
	}

	/**
	 * Unfolds the iCal properties that were spread into multiple lines
	 * after 75 chars by converting them into one single line.
	 * 
	 * @param 	array 	$lines 	the parsed iCal lines.
	 * 
	 * @return 	array 			the unfolded iCal lines.
	 */
	protected function unfoldLines($lines = [])
	{
		if (!is_array($lines) || !count($lines)) {
			return [];
		}

		$string = implode(PHP_EOL, $lines);
		$string = preg_replace('/' . PHP_EOL . '[ \t]/', '', $string);

		$lines = explode(PHP_EOL, $string);

		return $lines;
	}
}
