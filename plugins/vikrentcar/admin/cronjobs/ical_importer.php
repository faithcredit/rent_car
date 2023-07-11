<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Cron Job - iCal Importer
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarCronJobIcalImporter extends VRCCronJob
{
	// do not need to track the elements
	use VRCCronTrackerUnused;

	/**
	 * This method should return all the form fields required to collect the information
	 * needed for the execution of the cron job.
	 * 
	 * @return  array  An associative array of form fields.
	 */
	public function getForm()
	{
		return [
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4><i class="' . VikRentCarIcons::i('calendar') . '"></i> <i class="' . VikRentCarIcons::i('download') . '"></i>&nbsp;' . $this->getTitle() . '</h4>',
			],
			'cancellations' => [
				'type'    => 'select',
				'label'   => JText::_('VRC_CRON_ICAL_CANC'),
				'help'    => JText::_('VRC_CRON_ICAL_CANC_HELP'),
				'default' => 1,
				'options' => [
					1 => JText::_('VRYES'),
					0 => JText::_('VRNO'),
				],
			],
			'test' => [
				'type'    => 'select',
				'label'   => JText::_('VRCCRONSMSREMPARAMTEST'),
				'help'    => JText::_('VRC_CRON_ICAL_TEST_HELP'),
				'default' => 0,
				'options' => [
					1 => JText::_('VRYES'),
					0 => JText::_('VRNO'),
				],
			],
		];
	}

	/**
	 * Returns the title of the cron job.
	 * 
	 * @return  string
	 */
	public function getTitle()
	{
		return JText::_('VRC_CRON_ICAL_IMPORTER_TITLE');
	}

	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function execute()
	{
		$do_cancellations = $this->params->get('cancellations', 1);
		$test_mode 		  = (bool)$this->params->get('test', 0);

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select($db->qn('i.id'));
		$query->select($db->qn('i.idcar'));
		$query->select($db->qn('i.name', 'calendar_name'));
		$query->select($db->qn('i.url'));
		$query->select($db->qn('c.name', 'car_name'));

		$query->from($db->qn('#__vikrentcar_cars_icals', 'i'));
		$query->leftjoin($db->qn('#__vikrentcar_cars', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('i.idcar'));

		$db->setQuery($query);
		$calendars = $db->loadAssocList();

		if (!$calendars) {
			$this->output('<span>No iCal calendars set up.</span>');
			return true;
		}

		$this->output('<p>Calendars to fetch: ' . count($calendars) . '</p>');

		// prepare counter array for execution logs
		$counter = [];

		foreach ($calendars as $ical_data) {
			// make sure to be using an HTTP protocol
			if (strpos($ical_data['url'], 'webcal://') === 0) {
				$ical_data['url'] = str_replace('webcal://', 'https://', $ical_data['url']);
			}

			// determine calendar name to be used
			$cal_name = !empty($ical_data['calendar_name']) ? $ical_data['calendar_name'] : $ical_data['id'];

			// read the active events from the remote calendar
			$events = $this->fetchRemoteCalendarEvents($ical_data, $skip_past = true);

			if (!is_array($events)) {
				$this->output('<p>Could not read events from calendar (' . $ical_data['calendar_name'] . ' - ' . $ical_data['car_name'] . '): ' . $ical_data['url'] . '</p>');
				continue;
			}

			if (!count($events)) {
				$this->output('<p>No future events found in calendar (' . $ical_data['calendar_name'] . ' - ' . $ical_data['car_name'] . '): ' . $ical_data['url'] . '</p>');
				if ($do_cancellations && !$test_mode) {
					// perform cancellations
					$cancelled_ids = $this->cancelCalendarBookings($events, $ical_data);
					// update counter
					if (count($cancelled_ids)) {
						if (!isset($counter[$ical_data['car_name']])) {
							$counter[$ical_data['car_name']] = [];
						}
						if (!isset($counter[$ical_data['car_name']][$cal_name])) {
							$counter[$ical_data['car_name']][$cal_name] = [];
						}
					}
					foreach ($cancelled_ids as $cancelled_id) {
						$counter[$ical_data['car_name']][$cal_name][] = $cancelled_id . ' (Cancelled)';
					}
				}
				continue;
			}

			// calendar events were fetched
			$this->output('<p>Number of future events found in calendar (' . $ical_data['calendar_name'] . ' - ' . $ical_data['car_name'] . '): ' . count($events) . '</p>');

			if ($test_mode) {
				$this->output('<pre>' . print_r($events, true) . '</pre>');
				continue;
			}

			foreach ($events as $event) {
				// check if this booking was previously imported
				$prev_booking = $this->getCalendarBooking($event, $ical_data);

				if (!$prev_booking) {
					// store new rental order
					$new_oid = $this->storeCalendarBooking($event, $ical_data);
					if (!$new_oid) {
						$this->appendLog('Could not store new booking from calendar (' . $ical_data['calendar_name'] . ' - ' . $ical_data['car_name'] . ')<pre>' . print_r($event, true) . '</pre>');
						continue;
					}
					// update counter
					if (!isset($counter[$ical_data['car_name']])) {
						$counter[$ical_data['car_name']] = [];
					}
					if (!isset($counter[$ical_data['car_name']][$cal_name])) {
						$counter[$ical_data['car_name']][$cal_name] = [];
					}
					$counter[$ical_data['car_name']][$cal_name][] = $event['UID'] . ' (New)';
				} else {
					// update rental order unless modified later on the website
					$ev_last_mod   = !empty($event['TS_LAST_MODIFIED']) ? $event['TS_LAST_MODIFIED'] : 0;
					$book_last_mod = 0;
					$book_history  = VikRentCar::getOrderHistoryInstance()->setBid($prev_booking['id'])->loadHistory();
					if (count($book_history)) {
						$book_last_mod = strtotime(JHtml::_('date', $book_history[0]['dt']));
					}
					// $this->output('<p>Debug: event last mod: ' . date('Y-m-d H:i:s', $ev_last_mod) . ' - booking last mod: ' . date('Y-m-d H:i:s', $book_last_mod) . '</p>');
					if ($book_last_mod <= $ev_last_mod) {
						/**
						 * Update rental order only if website last modification date is older
						 * than the event last modification date, or if a value is empty.
						 */
						$mod_oid = $this->updateCalendarBooking($event, $ical_data, $prev_booking);
						if (!$mod_oid) {
							$this->appendLog('Could not update existing booking from calendar (' . $ical_data['calendar_name'] . ' - ' . $ical_data['car_name'] . ')<pre>' . print_r($event, true) . '</pre>');
							continue;
						}
						// update counter
						if (!isset($counter[$ical_data['car_name']])) {
							$counter[$ical_data['car_name']] = [];
						}
						if (!isset($counter[$ical_data['car_name']][$cal_name])) {
							$counter[$ical_data['car_name']][$cal_name] = [];
						}
						$counter[$ical_data['car_name']][$cal_name][] = $event['UID'] . ' (Updated)';
					}
				}
			}

			if ($do_cancellations) {
				// perform cancellations
				$cancelled_ids = $this->cancelCalendarBookings($events, $ical_data);
				// update counter
				if (count($cancelled_ids)) {
					if (!isset($counter[$ical_data['car_name']])) {
						$counter[$ical_data['car_name']] = [];
					}
					if (!isset($counter[$ical_data['car_name']][$cal_name])) {
						$counter[$ical_data['car_name']][$cal_name] = [];
					}
				}
				foreach ($cancelled_ids as $cancelled_id) {
					$counter[$ical_data['car_name']][$cal_name][] = $cancelled_id . ' (Cancelled)';
				}
			}
		}

		// store the execution logs (if anything to store)
		foreach ($counter as $car_name => $cals) {
			foreach ($cals as $cal_name => $uids) {
				foreach ($uids as $uid) {
					$this->appendLog(sprintf('%s (%s): %s', $car_name, $cal_name, $uid));
				}
			}
		}

		return true;
	}

	/**
	 * Downloads a remote calendar and fetches the future events in it.
	 * 
	 * @param 	mixed 	$ical 		either an iCal record (array) or URL (string).
	 * @param 	bool 	$skip_past 	true for skipping events with an end date in the past.
	 * 
	 * @return 	mixed 				false in case of failure, array otherwise (even empty if no events).
	 */
	private function fetchRemoteCalendarEvents($ical, $skip_past = true)
	{
		if (empty($ical)) {
			return false;
		}

		$calendar_url = is_string($ical) ? $ical : $ical['url'];

		// init HTTP transport
		$http = new JHttp();

		// build request headers
		$headers = array(
			// disable the SSL peer verification
			'sslverify' => false,
		);

		// fetch remote calendar
		$response = $http->get($calendar_url, $headers, 10);

		if ($response->code != 200) {
			// register the request error
			$this->appendLog(sprintf('Could not fetch remote calendar (%s) - Error (%d): %s', $calendar_url, $response->code, $response->body));

			return false;
		}

		if (empty($response->body)) {
			// invalid response body
			$this->appendLog(sprintf('Invalid content fetched from remote calendar (%s)', $calendar_url));

			return false;
		}

		// parse calendar events
		$parser = new VRCCalendarIcal($response->body);
		$events = $parser ? $parser->events() : [];

		if (!$parser) {
			return $events;
		}

		// limit for past events (midnight of the current day)
		$lim_past_ts = mktime(0, 0, 0, date('n'), date('j'), date('Y'));

		foreach ($events as $k => $event) {
			if (empty($event['DTSTART']) || empty($event['DTEND']) || empty($event['UID'])) {
				// invalid event structure
				unset($events[$k]);
				continue;
			}
			$last_mod_ts = null;
			// convert iCal event dates into timestamps
			$events[$k]['TS_START'] = $parser->iCalDateToUnixTimestamp($event['DTSTART']);
			$events[$k]['TS_END'] 	= $parser->iCalDateToUnixTimestamp($event['DTEND']);
			if ($events[$k]['TS_START'] >= $events[$k]['TS_END']) {
				// invalid dates
				unset($events[$k]);
				continue;
			}
			// check last-modified property for updates
			if (!empty($event['LAST-MODIFIED'])) {
				$last_mod_ts = $parser->iCalDateToUnixTimestamp($event['LAST-MODIFIED']);
				if ($last_mod_ts) {
					$events[$k]['TS_LAST_MODIFIED']  = $last_mod_ts;
				}
			}
			// set readable dates
			$events[$k]['YMD_START'] = date('Y-m-d H:i:s', $events[$k]['TS_START']);
			$events[$k]['YMD_END'] 	 = date('Y-m-d H:i:s', $events[$k]['TS_END']);
			if ($last_mod_ts) {
				$events[$k]['YMD_LAST_MODIFIED'] = date('Y-m-d H:i:s', $last_mod_ts);
			}
			// make sure the event is not in the past
			if ($skip_past === true && $events[$k]['TS_START'] < $lim_past_ts) {
				// this event is in the past
				unset($events[$k]);
				continue;
			}
		}

		return array_values($events);
	}

	/**
	 * Checks if the given iCal event was previously stored.
	 * 
	 * @param 	array 	$event 		the iCal parsed event array.
	 * @param 	array 	$ical_data 	the current iCal record.
	 * 
	 * @return 	mixed 				false if no previous booking found, order array otherwise.
	 */
	private function getCalendarBooking(array $event, array $ical_data)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->qn('#__vikrentcar_orders'));
		$query->where([
			$db->qn('idcar') . ' = ' . (int)$ical_data['idcar'],
			$db->qn('id_ical') . ' = ' . (int)$ical_data['id'],
			$db->qn('idorder_ical') . ' = ' . $db->q($event['UID']),
		]);

		$db->setQuery($query);
		$prev_booking = $db->loadAssoc();

		if (!$prev_booking) {
			return false;
		}

		return $prev_booking;
	}

	/**
	 * Parses an iCal booking to check if any custom property is available.
	 * The supported X-properties will be standardized to a default format.
	 * 
	 * @param 	array 	$booking 	the raw iCal booking array.
	 * 
	 * @return 	array 				the iCal booking array with adjusted keys.
	 */
	private function extractCustomXProperties(array $booking = [])
	{
		$x_props = array_filter(array_keys($booking), function($prop) {
			return substr(strtoupper($prop), 0, 1) === 'X';
		});
		
		if (!count($x_props)) {
			// no custom properties found
			return $booking;
		}

		// map of internal key and regex pattern(s) (string or array of strings)
		$reserved_keys = [
			'X-Booking-Ref' 		=> "/X-.*?(Booking-Ref)/i",
			'X-Name' 				=> "/X-.*?(Name)/i",
			'X-Total-Booking-Value' => [
											"/X-.*?(Total-Booking-Value)/i",
											"/X-.*?(Total)/i",
											"/X-.*?(Gross-Price)/i",
										],
			'X-Email' 				=> "/X-.*?(Email)/i",
			'X-Address' 			=> "/X-.*?(Address)/i",
			'X-Postcode' 			=> [
											"/X-.*?(Postcode)/i",
											"/X-.*?(Postalcode)/i",
											"/X-.*?(Postal-Code)/i",
											"/X-.*?(Zip)/i",
											"/X-.*?(Zipcode)/i",
											"/X-.*?(Zip-Code)/i",
										],
			'X-Town' 				=> "/X-.*?(Town)/i",
			'X-City' 				=> "/X-.*?(City)/i",
			'X-Country' 			=> "/X-.*?(Country)/i",
			'X-Telephone' 			=> [
											"/X-.*?(Telephone)/i",
											"/X-.*?(Phone)/i",
										],
		];

		// check for matching against the custom iCal properties
		foreach ($x_props as $x_prop) {
			foreach ($reserved_keys as $res_key => $key_patterns) {
				// always convert the regex pattern(s) to an array
				$match_pattners = is_string($key_patterns) ? [$key_patterns] : $key_patterns;
				// check for a possible conversion value
				foreach ($match_pattners as $rpattern) {
					if (preg_match($rpattern, $x_prop)) {
						$key_value = $booking[$x_prop];
						unset($booking[$x_prop]);
						$booking[$res_key] = $key_value;
						break 2;
					}
				}
			}
		}

		return $booking;
	}

	/**
	 * Tries to find the country 3-char code from the given identifier.
	 * 
	 * @param 	string 	$country 	the country name or code identifier.
	 * 
	 * @param 	string 				the country 3-char code or null.
	 */
	private function matchCountryCodeName($country)
	{
		if (empty($country)) {
			return null;
		}

		// fetch values from db
		$dbo = JFactory::getDbo();

		$q = "SELECT `country_name`, `country_3_code`, `country_2_code` FROM `#__vikrentcar_countries` WHERE `country_name`=" . $dbo->quote($country) . " OR `country_3_code`=" . $dbo->quote($country) . " OR `country_2_code`=" . $dbo->quote($country);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return null;
		}
		$country_record = $dbo->loadAssoc();

		return $country_record['country_3_code'];
	}

	/**
	 * Given a 3-char country ISO code, tries to guess the best
	 * available language to assign to the current rental order.
	 * 
	 * @param 	string 	$three_char_ccode 	the country 3-char code fetched.
	 * 
	 * @return 	string 						the language tag to use or null.
	 */
	private function guessLangFromCountry($three_char_ccode)
	{
		if (empty($three_char_ccode)) {
			return null;
		}

		$dbo = JFactory::getDbo();

		// get all the available languages
		$known_langs = VikRentCar::getVrcApplication()->getKnownLanguages();
		if (!is_array($known_langs) || !count($known_langs)) {
			return null;
		}

		// build similarities with country-languages
		$similarities = array(
			'AU' => 'en',
			'GB' => 'en',
			'IE' => 'en',
			'NZ' => 'en',
			'US' => 'en',
			'CA' => array(
				'en',
				'fr',
			),
			'CL' => 'es',
			'AR' => 'es',
			'PE' => 'es',
			'MX' => 'es',
			'CR' => 'es',
			'CO' => 'es',
			'EC' => 'es',
			'BO' => 'es',
			'CU' => 'es',
			'VE' => 'es',
			'BE' => 'fr',
			'LU' => 'fr',
			'CH' => array(
				'de',
				'it',
				'fr',
			),
			'AT' => 'de',
			'GR' => 'el',
			'GL' => 'dk',
		);

		// fetch values from db
		$q = "SELECT `country_name`, `country_3_code`, `country_2_code` FROM `#__vikrentcar_countries` WHERE `country_3_code`=" . $dbo->quote($three_char_ccode);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return null;
		}
		$country_record = $dbo->loadAssoc();

		// assign country name/code versions
		$country_name  = $country_record['country_name'];
		$country_3char = strtoupper($country_record['country_3_code']);
		$country_2char = strtoupper($country_record['country_2_code']);

		// build an associative array of language tags and related match-score
		$langtags_score = array();
		foreach ($known_langs as $ltag => $ldet) {
			// default language tag score is 0 for no matches
			$langtags_score[$ltag] = 0;
			// get language and country codes
			$lang_country_codes = explode('-', str_replace('_', '-', strtoupper($ltag)));
			
			// check matches with the installed language details
			if ($lang_country_codes[0] == $country_2char || $lang_country_codes[0] == $country_3char) {
				// increase language tag score
				$langtags_score[$ltag]++;
			}
			if (!empty($lang_country_codes[1]) && ($lang_country_codes[1] == $country_2char || $lang_country_codes[1] == $country_3char)) {
				// increase language tag score
				$langtags_score[$ltag]++;
			}
			if (!empty($ldet['locale'])) {
				// sanitize locale for matching the 2-char code safely
				$ldet['locale'] = str_replace(array('standard', 'euro', 'iso', 'utf'), '', strtolower($ldet['locale']));
				if (stripos($ldet['locale'], $country_2char) !== false || stripos($ldet['locale'], $country_name) !== false) {
					// increase language tag score
					$langtags_score[$ltag]++;
				}
			}
			if (!empty($ldet['name']) && stripos($ldet['name'], $country_name) !== false) {
				// increase language tag score
				$langtags_score[$ltag]++;
			}
			if (!empty($ldet['nativeName']) && stripos($ldet['nativeName'], $country_name) !== false) {
				// increase language tag score
				$langtags_score[$ltag]++;
			}

			// check language similarities between countries
			if (isset($similarities[$country_2char])) {
				$spoken_tags = !is_array($similarities[$country_2char]) ? array($similarities[$country_2char]) : $similarities[$country_2char];
				// check if language tag(s) is available for this spoken language
				foreach ($spoken_tags as $spoken_tag) {
					if ($lang_country_codes[0] == strtoupper($spoken_tag)) {
						// increase language tag score
						$langtags_score[$ltag]++;
					}
				}
			}
		}

		// make sure at least one language tag has got some points
		if (max($langtags_score) === 0) {
			// no languages installed to honor this country
			return null;
		}

		// sort language tag scores
		arsort($langtags_score);

		// reset array pointer to the first (highest) element
		reset($langtags_score);

		// return the language tag with the highest score
		return key($langtags_score);
	}

	/**
	 * Calculates the duration of the rental in hours or days.
	 * 
	 * @param 	int 	$start_ts 	the pick up unix timestamp.
	 * @param 	int 	$end_ts 	the drop off unix timestamp.
	 * 
	 * @return 	array 				associative array of duration.
	 */
	private function calculateRentalDuration($start_ts, $end_ts)
	{
		$secdiff = $end_ts - $start_ts;

		$hoursdiff = 0;
		$daysdiff = $secdiff / 86400;

		if (is_int($daysdiff)) {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			}
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
				$ophours = $secdiff / 3600;
				$hoursdiff = intval(round($ophours));
				if ($hoursdiff < 1) {
					$hoursdiff = 1;
				}
			} else {
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}

		return [
			'type'  => ($hoursdiff > 0 ? 'hours' : 'days'),
			'value' => ($hoursdiff > 0 ? $hoursdiff : $daysdiff),
		];
	}

	/**
	 * Builds the order object to store or update a record.
	 * 
	 * @param 	array 	$event 		the iCal parsed event array.
	 * @param 	array 	$ical_data 	the current iCal record.
	 * @param 	int 	$prev_id 	the order id to update (if any).
	 * 
	 * @return 	object 				the composed order object.
	 */
	private function buildOrderObject(array $event, array $ical_data, $prev_id = null)
	{
		// check for custom iCal properties
		$event = $this->extractCustomXProperties($event);

		// count duration
		$duration = $this->calculateRentalDuration($event['TS_START'], $event['TS_END']);

		// check if the country was passed
		$use_country = null;
		if (!empty($event['X-Country'])) {
			if (strlen($event['X-Country']) === 3) {
				$use_country = strtoupper($event['X-Country']);
			} else {
				// try to guess the given country
				$use_country = $this->matchCountryCodeName($event['X-Country']);
			}
		}

		// detect the language to assign
		$use_lang = null;
		if (!empty($use_country)) {
			$use_lang = $this->guessLangFromCountry($use_country);
		}

		// create the rental order object
		$order = new stdClass;
		if (!empty($prev_id)) {
			$order->id = (int)$prev_id;
		}
		if (!empty($ical_data['idbusy'])) {
			$order->idbusy = (int)$ical_data['idbusy'];
		}
		$order->custdata 	 = $event['SUMMARY'];
		$order->ts 			 = time();
		$order->status 		 = 'confirmed';
		$order->idcar 		 = (int)$ical_data['idcar'];
		$order->days 		 = ($duration['type'] == 'days' ? $duration['value'] : 1);
		$order->ritiro 		 = $event['TS_START'];
		$order->consegna 	 = $event['TS_END'];
		if (!empty($event['X-Email'])) {
			$order->custmail = $event['X-Email'];
		}
		if (empty($prev_id)) {
			// do not update the SID of the order
			$order->sid = VikRentCar::getSecretLink();
		}
		$order->hourly 		 = ($duration['type'] == 'hours' ? $duration['value'] : 0);
		if (empty($prev_id)) {
			// set the order total to an empty value only if creating a new record
			$order->order_total = !empty($event['X-Total-Booking-Value']) ? (float)$event['X-Total-Booking-Value'] : 0;
		}
		if (empty($prev_id)) {
			if (!empty($use_lang)) {
				$order->lang = $use_lang;
			}
			if (!empty($use_country)) {
				$order->country = $use_country;
			}
		}
		if (!empty($event['X-Telephone'])) {
			$order->phone = $event['X-Telephone'];
		}
		if (empty($prev_id)) {
			// check for nominative when creating a new rental order
			$use_nominative = null;
			if (!empty($event['X-Name'])) {
				$use_nominative = $event['X-Name'];
			} elseif (strlen($event['SUMMARY']) < 64 && !preg_match("/\r\n|\n|\r/", $event['SUMMARY'])) {
				$use_nominative = $event['SUMMARY'];
			}
			if (!empty($use_nominative) && strlen($use_nominative) > 64) {
				if (function_exists('mb_substr')) {
					$use_nominative = mb_substr($use_nominative, 0, 64);
				} else {
					$use_nominative = substr($use_nominative, 0, 64);
				}
			}
			$order->nominative = $use_nominative;
		}
		$order->id_ical 	 = (int)$ical_data['id'];
		$order->idorder_ical = strlen($event['UID']) > 128 ? substr($event['UID'], 0, 128) : $event['UID'];

		return $order;
	}

	/**
	 * Creates a new rental order for the given event and iCal calendar.
	 * 
	 * @param 	array 	$event 		the iCal parsed event array.
	 * @param 	array 	$ical_data 	the current iCal record.
	 * 
	 * @return 	mixed 				false on failure, new order ID otherwise.
	 */
	private function storeCalendarBooking(array $event, array $ical_data)
	{
		$db = JFactory::getDbo();

		// extra seconds after drop off
		$turnover_scs = VikRentCar::getHoursCarAvail() * 3600;

		// occupy the vehicle
		$busy = new stdClass;
		$busy->idcar 	= (int)$ical_data['idcar'];
		$busy->ritiro 	= $event['TS_START'];
		$busy->consegna = $event['TS_END'];
		$busy->realback = $event['TS_END'] + $turnover_scs;

		$db->insertObject('#__vikrentcar_busy', $busy, 'id');

		if (!isset($busy->id)) {
			return false;
		}

		// create the rental order
		$order = $this->buildOrderObject($event, array_merge($ical_data, ['idbusy' => $busy->id]));

		$db->insertObject('#__vikrentcar_orders', $order, 'id');

		if (!isset($order->id)) {
			return false;
		}

		// store Booking History event
		VikRentCar::getOrderHistoryInstance()->setBid($order->id)->store('IN', $ical_data['calendar_name'] . " \n" . $ical_data['url']);

		return $order->id;
	}

	/**
	 * Updates an existing order with the new given event and iCal calendar.
	 * 
	 * @param 	array 	$event 			the iCal parsed event array.
	 * @param 	array 	$ical_data 		the current iCal record.
	 * @param 	array 	$prev_booking 	the rental order record to update.
	 * 
	 * @return 	mixed 					false on failure, updated order ID otherwise.
	 */
	private function updateCalendarBooking(array $event, array $ical_data, array $prev_booking)
	{
		$db = JFactory::getDbo();

		if (!empty($prev_booking['idbusy'])) {
			// remove previously occupied record
			$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id`=" . (int)$prev_booking['idbusy'] . ";";
			$db->setQuery($q);
			$db->execute();
		}

		// extra seconds after drop off
		$turnover_scs = VikRentCar::getHoursCarAvail() * 3600;

		// occupy the vehicle on the possibly new dates
		$busy = new stdClass;
		$busy->idcar 	= (int)$ical_data['idcar'];
		$busy->ritiro 	= $event['TS_START'];
		$busy->consegna = $event['TS_END'];
		$busy->realback = $event['TS_END'] + $turnover_scs;

		$db->insertObject('#__vikrentcar_busy', $busy, 'id');

		if (!isset($busy->id)) {
			return false;
		}

		// update the rental order
		$order = $this->buildOrderObject($event, array_merge($ical_data, ['idbusy' => $busy->id]), $prev_booking['id']);

		if (empty($order->id) || !$db->updateObject('#__vikrentcar_orders', $order, 'id')) {
			return false;
		}

		// store Booking History event
		VikRentCar::getOrderHistoryInstance()->setBid($order->id)->store('IM', $ical_data['calendar_name'] . " \n" . $ical_data['url'] . " \n" . VikRentCar::getLogBookingModification($prev_booking));

		return $prev_booking['id'];
	}

	/**
	 * Cancels the active and previously imported orders from this
	 * calendar that are no longer available.
	 * 
	 * @param 	array 	$events			the iCal parsed events list.
	 * @param 	array 	$ical_data 		the current iCal record.
	 * 
	 * @return 	array 					list of rental order IDs set to cancelled.
	 */
	private function cancelCalendarBookings(array $events, array $ical_data)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select($db->qn('id'));
		$query->select($db->qn('idbusy'));
		$query->select($db->qn('idorder_ical'));
		$query->from($db->qn('#__vikrentcar_orders'));
		$query->where([
			$db->qn('status') . ' = ' . $db->q('confirmed'),
			$db->qn('idcar') . ' = ' . (int)$ical_data['idcar'],
			$db->qn('consegna') . ' >= ' . time(),
			$db->qn('id_ical') . ' = ' . (int)$ical_data['id'],
		]);

		$db->setQuery($query);
		$cal_bookings = $db->loadAssocList();

		if (!$cal_bookings) {
			return [];
		}

		// list of cancelled orders
		$cancelled_ids = [];

		// gather all the active event UIDs in this calendar
		$active_uids = [];
		foreach ($events as $event) {
			$active_uids[] = $event['UID'];
		}

		// check what confirmed orders are no longer available
		foreach ($cal_bookings as $cal_booking) {
			if (in_array($cal_booking['idorder_ical'], $active_uids)) {
				// this active rental order is still in the calendar
				continue;
			}

			// proceed with the cancellation of this active rental order
			$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id`=" . (int)$cal_booking['idbusy'] . ";";
			$db->setQuery($q);
			$db->execute();

			// update the record
			$order = new stdClass;
			$order->id 	   = $cal_booking['id'];
			$order->status = 'cancelled';

			if (!$db->updateObject('#__vikrentcar_orders', $order, 'id')) {
				continue;
			}

			// store Booking History event
			VikRentCar::getOrderHistoryInstance()->setBid($order->id)->store('IC', $ical_data['calendar_name'] . " \n" . $ical_data['url']);

			// push cancelled ID
			$cancelled_ids[] = $order->id;
		}

		return $cancelled_ids;
	}
}
