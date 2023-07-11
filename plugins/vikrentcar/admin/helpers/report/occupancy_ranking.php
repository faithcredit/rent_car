<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Occupancy Ranking child Class of VikRentCarReport
 */
class VikRentCarReportOccupancyRanking extends VikRentCarReport
{
	/**
	 * Property 'defaultKeySort' is used by the View that renders the report.
	 */
	public $defaultKeySort = 'occupancy';
	/**
	 * Property 'defaultKeyOrder' is used by the View that renders the report.
	 */
	public $defaultKeyOrder = 'DESC';
	/**
	 * Property 'exportAllowed' is used by the View to display the export button.
	 */
	public $exportAllowed = 1;
	/**
	 * The script to render the Chart
	 */
	protected $chartScript;
	/**
	 * The current Chart title
	 */
	protected $chartTitle;
	/**
	 * The Chart meta data
	 */
	protected $chartMetaData;
	/**
	 * The Chart labels
	 */
	protected $chartJsLabels;
	/**
	 * The Chart Dataset label(s)
	 */
	protected $chartJsDataSetLabel;
	/**
	 * The Chart colors
	 */
	protected $chartJsColors;
	/**
	 * The Chart Data
	 */
	protected $chartJsData;
	/**
	 * Debug mode is activated by passing the value 'e4j_debug' > 0
	 */
	private $debug;

	/**
	 * Class constructor should define the name of the report and
	 * other vars. Call the parent constructor to define the DB object.
	 */
	function __construct()
	{
		$this->reportFile = basename(__FILE__, '.php');
		$this->reportName = JText::_('VRCREPORT'.strtoupper(str_replace('_', '', $this->reportFile)));
		$this->reportFilters = array();

		$this->cols = array();
		$this->rows = array();
		$this->footerRow = array();

		$this->chartScript = '';
		$this->chartTitle  = '';
		$this->chartMetaData = array();
		$this->chartJsLabels = array();
		$this->chartJsDataSetLabel = '';
		$this->chartJsColors = array();
		$this->chartJsData = array();

		$this->debug = (VikRequest::getInt('e4j_debug', 0, 'request') > 0);

		parent::__construct();
	}

	/**
	 * Returns the name of this report.
	 *
	 * @return 	string
	 */
	public function getName()
	{
		return $this->reportName;
	}

	/**
	 * Returns the name of this file without .php.
	 *
	 * @return 	string
	 */
	public function getFileName()
	{
		return $this->reportFile;
	}

	/**
	 * Returns the filters of this report.
	 *
	 * @return 	array
	 */
	public function getFilters()
	{
		if (count($this->reportFilters)) {
			// do not run this method twice, as it could load JS and CSS files.
			return $this->reportFilters;
		}

		// get VRC Application Object
		$vrc_app = new VrcApplication();

		// load the jQuery UI Datepicker
		$this->loadDatePicker();

		// load Charts assets
		$this->loadChartsAssets();

		// from Date Filter
		$filter_opt = array(
			'label' => '<label for="fromdate">'.JText::_('VRCREPORTSDATEFROM').'</label>',
			'html' => '<input type="text" id="fromdate" name="fromdate" value="" class="vrc-report-datepicker vrc-report-datepicker-from" />',
			'type' => 'calendar',
			'name' => 'fromdate'
		);
		array_push($this->reportFilters, $filter_opt);

		// to Date Filter
		$filter_opt = array(
			'label' => '<label for="todate">'.JText::_('VRCREPORTSDATETO').'</label>',
			'html' => '<input type="text" id="todate" name="todate" value="" class="vrc-report-datepicker vrc-report-datepicker-to" />',
			'type' => 'calendar',
			'name' => 'todate'
		);
		array_push($this->reportFilters, $filter_opt);

		// period type filter
		$pperiod = VikRequest::getString('period', 'month', 'request');
		$periods = array(
			'month' => JText::_('VRPVIEWRESTRICTIONSTWO'),
			'week' => 'Week',
			'day' => JText::_('VRDAY'),
		);
		$periods_sel_html = $vrc_app->getNiceSelect($periods, $pperiod, 'period', '', '', '', '', 'period');
		$filter_opt = array(
			'label' => '<label for="period">'.JText::_('VRROVWSELPERIOD').'</label>',
			'html' => $periods_sel_html,
			'type' => 'select',
			'name' => 'period'
		);
		array_push($this->reportFilters, $filter_opt);

		// car ID filter
		$pidcar = VikRequest::getInt('idcar', '', 'request');
		$all_cars = $this->getCars();
		$cars = array();
		foreach ($all_cars as $car) {
			$cars[$car['id']] = $car['name'];
		}
		if (count($cars)) {
			$cars_sel_html = $vrc_app->getNiceSelect($cars, $pidcar, 'idcar', JText::_('VRCSTATSALLCARS'), JText::_('VRCSTATSALLCARS'), '', '', 'idcar');
			$filter_opt = array(
				'label' => '<label for="idcar">'.JText::_('VRCREPORTSCARFILT').'</label>',
				'html' => $cars_sel_html,
				'type' => 'select',
				'name' => 'idcar'
			);
			array_push($this->reportFilters, $filter_opt);
		}

		// get minimum check-in and maximum check-out for dates filters
		$df = $this->getDateFormat();
		$mincheckin = 0;
		$maxcheckout = 0;
		$q = "SELECT MIN(`ritiro`) AS `mincheckin`, MAX(`consegna`) AS `maxcheckout` FROM `#__vikrentcar_orders` WHERE `status`='confirmed';";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			$data = $this->dbo->loadAssoc();
			if (!empty($data['mincheckin']) && !empty($data['maxcheckout'])) {
				$mincheckin = $data['mincheckin'];
				$maxcheckout = $data['maxcheckout'];
			}
		}
		//

		// jQuery code for the datepicker calendars and select2
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$pfromdate = empty($pfromdate) && !empty($mincheckin) ? date($df, $mincheckin) : $pfromdate;
		$ptodate = VikRequest::getString('todate', '', 'request');
		$ptodate = empty($ptodate) && !empty($maxcheckout) ? date($df, $maxcheckout) : $ptodate;
		$js = 'jQuery(document).ready(function() {
			jQuery(".vrc-report-datepicker:input").datepicker({
				'.(!empty($mincheckin) ? 'minDate: "'.date($df, $mincheckin).'", ' : '').'
				'.(!empty($maxcheckout) ? 'maxDate: "'.date($df, $maxcheckout).'", ' : '').'
				'.(!empty($mincheckin) && !empty($maxcheckout) ? 'yearRange: "'.(date('Y', $mincheckin)).':'.date('Y', $maxcheckout).'", changeMonth: true, changeYear: true, ' : '').'
				dateFormat: "'.$this->getDateFormat('jui').'",
				onSelect: vrcReportCheckDates
			});
			'.(!empty($pfromdate) ? 'jQuery(".vrc-report-datepicker-from").datepicker("setDate", "'.$pfromdate.'");' : '').'
			'.(!empty($ptodate) ? 'jQuery(".vrc-report-datepicker-to").datepicker("setDate", "'.$ptodate.'");' : '').'
		});
		function vrcReportCheckDates(selectedDate, inst) {
			if (selectedDate === null || inst === null) {
				return;
			}
			var cur_from_date = jQuery(this).val();
			if (jQuery(this).hasClass("vrc-report-datepicker-from") && cur_from_date.length) {
				var nowstart = jQuery(this).datepicker("getDate");
				var nowstartdate = new Date(nowstart.getTime());
				jQuery(".vrc-report-datepicker-to").datepicker("option", {minDate: nowstartdate});
			}
		}';
		$this->setScript($js);

		return $this->reportFilters;
	}

	/**
	 * Loads the report data from the DB.
	 * Returns true in case of success, false otherwise.
	 * Sets the columns and rows for the report to be displayed.
	 *
	 * @return 	boolean
	 */
	public function getReportData()
	{
		if (strlen($this->getError())) {
			// export functions may set errors rather than exiting the process, and the View may continue the execution to attempt to render the report.
			return false;
		}
		// input fields and other vars
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$pperiod = VikRequest::getString('period', 'month', 'request');
		// idcar can be an array of IDs or just one ID as int/string
		$pidcar = VikRequest::getVar('idcar', null, 'request');
		//
		$pkrsort = VikRequest::getString('krsort', $this->defaultKeySort, 'request');
		$pkrsort = empty($pkrsort) ? $this->defaultKeySort : $pkrsort;
		$pkrorder = VikRequest::getString('krorder', $this->defaultKeyOrder, 'request');
		$pkrorder = empty($pkrorder) ? $this->defaultKeyOrder : $pkrorder;
		$pkrorder = $pkrorder == 'DESC' ? 'DESC' : 'ASC';
		// bookings max creation date
		$pmaxdate = VikRequest::getString('maxdate', '', 'request');
		$pmaxdate = !empty($pmaxdate) ? VikRentCar::getDateTimestamp($pmaxdate, 23, 59, 59) : $pmaxdate;
		//
		$currency_symb = VikRentCar::getCurrencySymb();
		$df = $this->getDateFormat();
		if (empty($ptodate)) {
			$ptodate = $pfromdate;
		}
		// get dates timestamps
		$from_ts = VikRentCar::getDateTimestamp($pfromdate, 0, 0);
		$to_ts = VikRentCar::getDateTimestamp($ptodate, 23, 59, 59);
		if (empty($pfromdate) || empty($from_ts) || empty($to_ts)) {
			$this->setError(JText::_('VRCREPORTSERRNODATES'));
			return false;
		}

		// months map
		$months_map = array(
			JText::_('VRMONTHONE'),
			JText::_('VRMONTHTWO'),
			JText::_('VRMONTHTHREE'),
			JText::_('VRMONTHFOUR'),
			JText::_('VRMONTHFIVE'),
			JText::_('VRMONTHSIX'),
			JText::_('VRMONTHSEVEN'),
			JText::_('VRMONTHEIGHT'),
			JText::_('VRMONTHNINE'),
			JText::_('VRMONTHTEN'),
			JText::_('VRMONTHELEVEN'),
			JText::_('VRMONTHTWELVE'),
		);

		// query to obtain the records
		$records = array();
		$q = "SELECT `o`.`id`,`o`.`ts`,`o`.`days`,`o`.`ritiro`,`o`.`consegna`,`o`.`totpaid`,`o`.`order_total`,`o`.`country`,`o`.`tot_taxes`," .
			"`o`.`idcar`,`o`.`optionals`,`o`.`cust_cost`,`o`.`cust_idiva`,`o`.`extracosts`,`o`.`car_cost` " .
			"FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` AS `b` ON `o`.`idbusy`=`b`.`id` " .
			"WHERE ".(!empty($pmaxdate) ? "`o`.`ts`<={$pmaxdate} " : "")."`o`.`status`='confirmed' AND `b`.`stop_sales`=0 AND `o`.`consegna`>={$from_ts} AND `o`.`ritiro`<={$to_ts} " .
			(!empty($pidcar) && !is_array($pidcar) ? "AND `o`.`idcar`=" . (int)$pidcar . " " : (is_array($pidcar) && count($pidcar) ? "AND `o`.`idcar` IN (" . implode(', ', $pidcar) . ") " : '')) .
			"ORDER BY `o`.`ritiro` ASC, `o`.`id` ASC;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			$records = $this->dbo->loadAssocList();
		}
		
		$dummy_values = false;
		if (!count($records)) {
			if ($pperiod != 'full') {
				// when using the regular report interface, we display an error in case of no bookings found
				$this->setError(JText::_('VRCREPORTSERRNORESERV'));
				return false;
			}

			// layout file building the chart prefers empty values rather than an error
			$dummy_values = true;
			// populate array with one dummy booking with empty values
			$records = array(
				array(
					'id' => -1,
					'ts' => time(),
					'days' => 1,
					'ritiro' => $from_ts,
					'consegna' => strtotime("+1 day", $from_ts),
					'totpaid' => 0,
					'order_total' => 0,
					'country' => null,
					'tot_taxes' => 0,
					'idorder' => -1,
					'idcar' => -1,
					'optionals' => null,
					'cust_cost' => null,
					'cust_idiva' => null,
					'extracosts' => null,
					'car_cost' => 0,
				),
			);
		}

		// nest records with multiple cars booked inside sub-array
		$bookings = array();
		foreach ($records as $v) {
			if (!isset($bookings[$v['id']])) {
				$bookings[$v['id']] = array();
			}
			// calculate the from_ts and to_ts values for later comparison
			$in_info = getdate($v['ritiro']);
			$out_info = getdate($v['consegna']);
			// these two properties are necessary for many other controls below
			$v['from_ts'] = mktime(0, 0, 0, $in_info['mon'], $in_info['mday'], $in_info['year']);
			$v['to_ts'] = mktime(23, 59, 59, $out_info['mon'], $out_info['mday'], $out_info['year']);
			//
			array_push($bookings[$v['id']], $v);
		}

		// first day of the week for weekly periods (0 for Sunday till 6 for Saturday)
		$firstwday = (int)VikRentCar::getFirstWeekDay();
		// we make it end to the day before as weeks should start on this weekday
		$firstwday -= 1;
		$firstwday = $firstwday < 0 ? 6 : $firstwday;

		// build ranges of periods by looping over the dates of the report
		$ranges 	= array();
		$from_info 	= getdate($from_ts);
		$to_info 	= getdate($to_ts);
		$cur_month 	= array('from_ts' => $from_info[0]);
		$cur_week 	= array('from_ts' => $from_info[0]);
		while ($from_info[0] <= $to_info[0]) {
			if ($pperiod == 'month') {
				if (date('n', $from_info[0]) != date('n', $cur_month['from_ts'])) {
					// month has changed, set to_ts to previous day at midnight
					$cur_month['to_ts'] = mktime(23, 59, 59, $from_info['mon'], ($from_info['mday'] - 1), $from_info['year']);
					// push month delimiter to ranges
					array_push($ranges, array(
						'from_ts' 	=> $cur_month['from_ts'],
						'to_ts' 	=> $cur_month['to_ts'],
					));
					// reset current month handler to current day (1st of the new month)
					$cur_month = array(
						'from_ts' => $from_info[0]
					);
				}
			} elseif ($pperiod == 'week') {
				if (!isset($cur_week['from_ts'])) {
					// 1st day of the new week
					$cur_week['from_ts'] = $from_info[0];
				}
				if ($from_info[0] != $cur_week['from_ts'] && (int)$from_info['wday'] == $firstwday) {
					// not the first day of the loop, but same weekday, so it's the week after
					$cur_week['to_ts'] = mktime(23, 59, 59, $from_info['mon'], $from_info['mday'], $from_info['year']);
					// push week delimiter to ranges
					array_push($ranges, array(
						'from_ts' 	=> $cur_week['from_ts'],
						'to_ts' 	=> $cur_week['to_ts'],
					));
					// reset current week handler
					$cur_week = array();
				}
			} elseif ($pperiod == 'day') {
				// push the range until the end of the current day
				array_push($ranges, array(
					'from_ts' 	=> $from_info[0],
					'to_ts' 	=> mktime(23, 59, 59, $from_info['mon'], $from_info['mday'], $from_info['year']),
				));
			} else {
				// (full) push the range until the "to date"
				array_push($ranges, array(
					'from_ts' 	=> $from_info[0],
					'to_ts' 	=> $to_info[0],
				));
				// do not loop any further date as we need the entire range requested
				break;
			}

			// next day iteration
			$from_info = getdate(mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] + 1), $from_info['year']));
		}

		// finalize ranges of period delimiters in case the loop ended on a non-precise date
		if ($pperiod == 'month' && date('Y-m-d', $cur_month['from_ts']) != date('Y-m-d', $to_info[0])) {
			// push last month delimiter to ranges
			array_push($ranges, array(
				'from_ts' 	=> $cur_month['from_ts'],
				'to_ts' 	=> $to_info[0],
			));
		} elseif ($pperiod == 'week' && isset($cur_week['from_ts'])) {
			// push last week delimiter to ranges
			array_push($ranges, array(
				'from_ts' 	=> $cur_week['from_ts'],
				'to_ts' 	=> $to_info[0],
			));
		}

		// total number of cars
		$total_cars_units = $this->countCars($pidcar);

		// define the columns of the report
		$this->cols = array(
			// date
			array(
				'key' => 'day',
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEDAY')
			),
			// cars sold
			array(
				'key' => 'cars_sold',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUERSOLD'),
				'tip' => JText::sprintf('VRCREPORTTOTCARSHELP', $total_cars_units)
			),
			// days booked
			array(
				'key' => 'days_booked',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCGRAPHTOTNIGHTSLBL')
			),
			// total bookings
			array(
				'key' => 'tot_bookings',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUETOTB')
			),
			// % occupancy
			array(
				'key' => 'occupancy',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEPOCC')
			),
			// ADR
			array(
				'key' => 'adr',
				'attr' => array(
					'class="center vrc-report-col-hideable"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEADR'),
				'tip' => JText::_('VRCREPORTREVENUEADRHELP')
			),
			// RevPAC
			array(
				'key' => 'revpar',
				'attr' => array(
					'class="center vrc-report-col-hideable"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEREVPAR'),
				'tip' => JText::_('VRCREPORTREVENUEREVPARH')
			),
			// Taxes
			array(
				'key' => 'taxes',
				'attr' => array(
					'class="center vrc-report-col-hideable"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUETAX')
			),
			// Revenue
			array(
				'key' => 'revenue',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEREV')
			)
		);

		// loop over the ranges to build the rows
		foreach ($ranges as $ind => $range) {
			// prepare default fields for this row
			$range_ts_from 	= $range['from_ts'];
			$info_ts_from 	= getdate($range['from_ts']);
			$range_ts_to 	= $range['to_ts'];
			$info_ts_to 	= getdate($range['to_ts']);
			$curwday_from 	= $this->getWdayString($info_ts_from['wday'], 'short');
			$curwday_to 	= $this->getWdayString($info_ts_to['wday'], 'short');
			$range_same_day = (date('Y-m-d', $range_ts_from) == date('Y-m-d', $range_ts_to));
			$cars_sold 		= 0;
			$days_booked 	= 0;
			$tot_bookings 	= 0;
			$occupancy 		= 0;
			$adr 			= 0;
			$revpar 		= 0;
			$taxes 			= 0;
			$revenue 		= 0;
			// count the days in this range
			if (!isset($ranges[$ind]['days'])) {
				$ranges[$ind]['days'] = $this->countDaysInRange($info_ts_from, $info_ts_to);
			}
			// maximum occupancy of this range is given by the days in the range times the total cars units
			$range_max_occupancy = $ranges[$ind]['days'] * $total_cars_units;
			// calculate the report details for this day
			foreach ($bookings as $gbook) {
				if ($dummy_values) {
					// we need all values to be left as 0
					break;
				}
				if ( // range start date is between the check-in and check-out of this booking
					$range['from_ts'] >= $gbook[0]['from_ts'] && $range['from_ts'] <= $gbook[0]['to_ts'] || 
					// range end date is between the check-in and check-out of this booking
					$range['to_ts'] >= $gbook[0]['from_ts'] && $range['to_ts'] <= $gbook[0]['to_ts'] || 
					// range start and end dates include this booking (probably a long period or a short booking)
					$range['from_ts'] <= $gbook[0]['from_ts'] && $range['to_ts'] >= $gbook[0]['to_ts']
				) {
					// this booking affects the current range of dates
					if (!isset($ranges[$ind]['bookings'])) {
						$ranges[$ind]['bookings'] = array();
					}
					array_push($ranges[$ind]['bookings'], $gbook[0]['id']);
					// increase values
					$cars_sold += 1;
					// nights booked is per cars booked, but $booking_nights is the total nights booked per booking, not per car
					$booking_nights = $this->countNightsBookedRange($info_ts_from, $info_ts_to, $gbook[0]);
					$days_booked += $booking_nights * 1;
					$tot_bookings++;
					// calculate net revenue and taxes
					$tot_net = $gbook[0]['order_total'] - (float)$gbook[0]['tot_taxes'];
					$tot_net = $tot_net / $gbook[0]['days'] * $booking_nights;
					$revenue += $tot_net;
					$tot_taxes = (float)$gbook[0]['tot_taxes'] / $gbook[0]['days'] * $booking_nights;
					$taxes += $tot_taxes;
				}
			}
			$occupancy = round(($days_booked * 100 / $range_max_occupancy), 2);
			$adr = $cars_sold > 0 ? $revenue / $cars_sold : 0;
			$revpar = $revenue / $total_cars_units;
			// push fields in the rows array as a new row
			array_push($this->rows, array(
				array(
					'key' => 'day',
					'callback' => function ($val) use ($range_ts_to, $df, $curwday_from, $curwday_to, $pperiod, $months_map, $range_same_day) {
						if ($pperiod == 'day' || $range_same_day) {
							return $curwday_from . ', ' . date($df, $val);
						}
						if (($pperiod == 'month' || $pperiod == 'full') && date('d', $val) == '1' && date('t', $range_ts_to) == date('d', $range_ts_to) && date('m', $val) == date('m', $range_ts_to)) {
							// full month
							return $months_map[((int)date('m', $val) - 1)] . ' ' . date('Y', $val);
						}
						return $curwday_from . ', ' . date($df, $val) . ' - ' . $curwday_to . ', ' . date($df, $range_ts_to);
					},
					'value' => $range_ts_from
				),
				array(
					'key' => 'cars_sold',
					'attr' => array(
						'class="center"'
					),
					'value' => $cars_sold
				),
				array(
					'key' => 'days_booked',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($range_max_occupancy) {
						return $val . ' / ' . $range_max_occupancy;
					},
					'value' => $days_booked
				),
				array(
					'key' => 'tot_bookings',
					'attr' => array(
						'class="center"'
					),
					'value' => $tot_bookings
				),
				array(
					'key' => 'occupancy',
					'attr' => array(
						'class="center"'
					),
					'value' => $occupancy
				),
				array(
					'key' => 'adr',
					'attr' => array(
						'class="center vrc-report-col-hideable"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikRentCar::numberFormat($val);
					},
					'value' => $adr
				),
				array(
					'key' => 'revpar',
					'attr' => array(
						'class="center vrc-report-col-hideable"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikRentCar::numberFormat($val);
					},
					'value' => $revpar
				),
				array(
					'key' => 'taxes',
					'attr' => array(
						'class="center vrc-report-col-hideable"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikRentCar::numberFormat($val);
					},
					'value' => $taxes
				),
				array(
					'key' => 'revenue',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikRentCar::numberFormat($val);
					},
					'value' => $revenue
				)
			));
		}

		// sort rows
		$this->sortRows($pkrsort, $pkrorder);

		// update sorting and ordering key
		$this->defaultKeySort  = $pkrsort;
		$this->defaultKeyOrder = $pkrorder;

		// loop over the rows to build the footer row with the totals
		$foot_cars_sold = 0;
		$foot_days_booked = 0;
		$foot_tot_bookings = 0;
		$foot_taxes = 0;
		$foot_revenue = 0;
		foreach ($this->rows as $row) {
			$foot_cars_sold += $row[1]['value'];
			$foot_days_booked += $row[2]['value'];
			$foot_tot_bookings += $row[3]['value'];
			$foot_taxes += $row[7]['value'];
			$foot_revenue += $row[8]['value'];
		}
		array_push($this->footerRow, array(
			array(
				'attr' => array(
					'class="vrc-report-total"'
				),
				'value' => '<h3>'.JText::_('VRCREPORTSTOTALROW').'</h3>'
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'value' => $foot_cars_sold
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'value' => $foot_days_booked
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'value' => $foot_tot_bookings
			),
			array(
				'value' => ''
			),
			array(
				'attr' => array(
					'class="center vrc-report-col-hideable"'
				),
				'value' => ''
			),
			array(
				'attr' => array(
					'class="center vrc-report-col-hideable"'
				),
				'value' => ''
			),
			array(
				'attr' => array(
					'class="center vrc-report-col-hideable"'
				),
				'callback' => function ($val) use ($currency_symb) {
					return $currency_symb.' '.VikRentCar::numberFormat($val);
				},
				'value' => $foot_taxes
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'callback' => function ($val) use ($currency_symb) {
					return $currency_symb.' '.VikRentCar::numberFormat($val);
				},
				'value' => $foot_revenue
			)
		));

		// debug data
		$debug_str = 'Periods COUNT = ' . count($ranges) . '<br/>';
		foreach ($ranges as $range) {
			$debug_str .= date('D, Y-m-d', $range['from_ts']) . ' - ' . date('D, Y-m-d', $range['to_ts']) . (isset($range['days']) ? ' ('.$range['days'].'d)' : '') . (isset($range['bookings']) ? ' - ' . implode(', ', $range['bookings']) : '') . '<br/>';
		}
		$debug_str .= '<br/><pre>' . print_r($bookings, true) . '</pre><br/>';
		$debug_str .= 'Total Cars Units = ' . $total_cars_units . '<br/>';
		if ($this->debug) {
			$this->setWarning($debug_str);
			$this->setWarning('path to report file = '.urlencode(dirname(__FILE__)).'<br/>');
			$this->setWarning('$total_cars_units = '.$total_cars_units.'<br/>');
			$this->setWarning('$bookings:<pre>'.print_r($bookings, true).'</pre><br/>');
		}

		return true;
	}

	/**
	 * Counts the number of nights booked by the
	 * given booking in the given range of dates.
	 * 
	 * @param 	array 	$from_info 	the getdate() info of the range start date timestamp.
	 * @param 	array 	$to_info 	the getdate() info of the range end date timestamp.
	 * @param 	array 	$booking 	the booking array (one car record).
	 *
	 * @return 	int 	the total number of nights booked in the given range.
	 */
	private function countNightsBookedRange($from_info, $to_info, $booking)
	{
		$tot_nights = 0;
		if (!is_array($from_info) || !is_array($to_info) || $from_info[0] > $to_info[0]) {
			return 1;
		}

		$checkout_ymd = date('Y-m-d', $booking['consegna']);

		while ($from_info[0] <= $to_info[0]) {
			if ($from_info[0] >= $booking['from_ts'] && $from_info[0] <= $booking['to_ts']) {
				// range day is inside booking dates
				if (date('Y-m-d', $from_info[0]) == $checkout_ymd) {
					// this is the check-out day, so it is not a night booked
					return 1;
				}
				$tot_nights++;
			}
			// next date
			$from_info = getdate(mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] + 1), $from_info['year']));
		}

		return $tot_nights > 0 ? $tot_nights : 1;
	}

	/**
	 * Counts the number of nights in the given range of dates.
	 * End date of range is always inclusive for the bookings.
	 * 
	 * @param 	array 	$from_info 	the getdate() info of the range start date timestamp.
	 * @param 	array 	$to_info 	the getdate() info of the range end date timestamp.
	 *
	 * @return 	int 	the total number of days in the given range.
	 */
	private function countDaysInRange($from_info, $to_info)
	{
		$tot_days = 0;
		if (!is_array($from_info) || !is_array($to_info) || $from_info[0] > $to_info[0]) {
			return $tot_days;
		}

		if (date('Y-m-d', $from_info[0]) == date('Y-m-d', $to_info[0])) {
			return 1;
		}

		while ($from_info[0] <= $to_info[0]) {
			$tot_days++;

			// next date
			$from_info = getdate(mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] + 1), $from_info['year']));
		}

		return $tot_days;

	}

	/**
	 * Generates the report columns and rows, then it outputs a CSV file
	 * for download. In case of errors, the process is not terminated (exit)
	 * to let the View display the error message.
	 *
	 * @return 	mixed 	void on success with script termination, false otherwise.
	 */
	public function exportCSV()
	{
		if (!count($this->rows) && !$this->getReportData()) {
			return false;
		}
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');

		$csvlines = array();

		// push the head of the CSV file
		$csvcols = array();
		foreach ($this->cols as $col) {
			array_push($csvcols, $col['label']);
		}
		array_push($csvlines, $csvcols);

		// push the rows of the CSV file
		foreach ($this->rows as $row) {
			$csvrow = array();
			foreach ($row as $field) {
				array_push($csvrow, (isset($field['callback']) && is_callable($field['callback']) ? $field['callback']($field['value']) : $field['value']));
			}
			array_push($csvlines, $csvrow);
		}

		// force CSV download
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="' . $this->reportName . '-' . str_replace('/', '_', $pfromdate) . '-' . str_replace('/', '_', $ptodate) . '.csv"');
		$outstream = fopen("php://output", 'w');
		foreach ($csvlines as $csvline) {
			fputcsv($outstream, $csvline);
		}
		fclose($outstream);
		exit;
	}

	/**
	 * Returns the Chart title.
	 * 
	 * @return 	string 	the title of the Chart
	 */
	public function getChartTitle()
	{
		$name_parts = array($this->reportName, $this->chartTitle);

		return implode(' - ', $name_parts);
	}

	/**
	 * This report can render a Chart. Main method called to whoever
	 * needs to access the Chart data rendered by this report.
	 * Defines some properties and accepts instructions through the arg.
	 * 
	 * @param 	mixed 	$data 	null or mixed for requested Chart data.
	 *
	 * @return 	string 	the HTML of the canvas element.
	 */
	public function getChart($data = null)
	{
		if (!count($this->rows) && !$this->getReportData()) {
			return '';
		}

		// find the dataset label depending on the active sorting (i.e. "occupancy")
		$dataset_label = '';
		foreach ($this->cols as $col) {
			if ($col['key'] == $this->defaultKeySort) {
				$dataset_label = $col['label'];
				break;
			}
		}
		if (empty($dataset_label)) {
			// ordering column label not found
			return '';
		}
		// set Chart title
		$this->chartTitle = $dataset_label;

		// push data values for the requested key depending on the active sorting
		$chart_labels   = array();
		$chart_data 	= array();
		$chart_indexes  = array();
		$counter 		= 0;
		$max_points 	= 10;
		foreach ($this->rows as $row) {
			foreach ($row as $ind => $field) {
				if ($counter >= $max_points) {
					break 2;
				}
				if ($ind === 0) {
					// make sure this field is not the same as the active sorting
					if ($field['key'] == $this->defaultKeySort) {
						// we cannot build the Chart, or the X and Y would have the same values
						return '';
					}
					// the first column is the label
					array_push($chart_labels, (isset($field['callback']) && is_callable($field['callback']) ? $field['callback']($field['value']) : $field['value']));
					// save the raw value for this index
					array_push($chart_indexes, $field['value']);
				}
				if ($field['key'] != $this->defaultKeySort) {
					// we do not care about this column
					continue;
				}
				array_push($chart_data, $field['value']);
				$counter++;
			}
		}
		if (!count($chart_labels) || count($chart_labels) != count($chart_data)) {
			// missing or invalid chart values
			return '';
		}

		// check whether a slice of the data was requested through the depth property
		if (is_array($data) && isset($data['depth']) && $data['depth'] > 0 && count($chart_labels) >= $data['depth']) {
			// get a slice of the labels and data
			$chart_labels = array_slice($chart_labels, 0, $data['depth']);
			$chart_data = array_slice($chart_data, 0, $data['depth']);
		} else {
			// sort labels and data by time ascending, to obtain a readable line chart
			asort($chart_indexes);
			$sorted_labels  = array();
			$sorted_data 	= array();
			foreach ($chart_indexes as $k => $v) {
				array_push($sorted_labels, $chart_labels[$k]);
				array_push($sorted_data, $chart_data[$k]);
			}
			$chart_labels 	= $sorted_labels;
			$chart_data 	= $sorted_data;
		}

		// the canvas element ID and tag
		$canvas_id 	 = 'vrc-report-chart-canvas';
		$canvas_html = '<canvas id="' . $canvas_id . '"></canvas>';

		// additional Chart properties
		$chart_type = is_array($data) && !empty($data['type']) ? $data['type'] : 'line';
		$chart_colors = array(
			'backgroundColor' => 'rgba(34,72,93,0.2)',
			'borderColor' => 'rgba(34,72,93,1)',
			'pieBackgroundColor' => '["rgba(34,72,93,1)"]',
			'pieHoverBorderColor' => '["rgba(34,72,93,0.9)"]',
		);

		// add a new label for the "free cars" if pie, count = 1 and occupancy filtering
		$pie_tooltip_format = '';
		if ($chart_type != 'line' && count($chart_data) === 1 && is_array($data) && isset($data['keys'])) {
			if (((is_array($data['keys']) && in_array('occupancy', $data['keys'])) || $data['keys'] == 'occupancy')) {
				// push "free cars" label and data to complete the doughnut chart
				$chart_labels = array(
					JText::_('VRCCARSOCCUPANCY'),
					JText::_('VRCCARSUNSOLD'),
				);
				array_push($chart_data, round((100 - $chart_data[0]), 2));
				$chart_colors['pieBackgroundColor'] = json_encode(array(
					'rgba(34,72,93,1)',
					'rgba(77,152,198,1)',
				));
				$chart_colors['pieHoverBorderColor'] = json_encode(array(
					'rgba(34,72,93,0.9)',
					'rgba(77,152,198,0.9)',
				));
				$pie_tooltip_format = ' + " %"';
			}
		}

		/**
		 * Set some Chart properties that can be accessed through getProperty()
		 * 
		 * @see 	getProperty()
		 */
		$this->chartJsLabels = $chart_labels;
		$this->chartJsDataSetLabel = $dataset_label;
		$this->chartJsColors = $chart_colors;
		$this->chartJsData = $chart_data;
		//

		if (!empty($this->chartScript)) {
			// the script has already been set, return just the HTML
			return $canvas_html;
		}

		// prepare the necessary script to render the Chart
		$this->chartScript .= 'var vrc_report_ctx = document.getElementById("' . $canvas_id . '").getContext("2d");' . "\n";
		$this->chartScript .= '
var vrcReportType = "' . $chart_type . '";
var vrcReportLineData = {
	labels: ' . json_encode($this->chartJsLabels) . ',
	datasets: [{
		label: "' . addslashes($this->chartJsDataSetLabel) . '",
		backgroundColor: "' . $this->chartJsColors['backgroundColor'] . '",
		borderColor: "' . $this->chartJsColors['borderColor'] . '",
		data: ' . json_encode($this->chartJsData) . ',
	}],
};
var vrcReportPieData = {
	labels: ' . json_encode($this->chartJsLabels) . ',
	datasets: [{
		label: "' . addslashes($this->chartJsDataSetLabel) . '",
		backgroundColor: ' . $this->chartJsColors['pieBackgroundColor'] . ',
		hoverBorderColor: ' . $this->chartJsColors['pieHoverBorderColor'] . ',
		data: ' . json_encode($this->chartJsData) . ',
	}],
};
var vrcReportLineOptions = {
	responsive: true,
	plugins: {
		legend: {
			display: true,
			position: "bottom",
		},
		legendCallback: function (chart) {
			// Return the HTML string here.
			var text = [];
			text.push("<ul class=\"chart-line-legend\">");
			for (var i = 0; i < chart.data.datasets.length; i++) {
				text.push("<li>");
				text.push("<span class=\"legend-entry\" style=\"background-color: " + chart.data.datasets[i].backgroundColor + "\"></span>");
				text.push("<span class=\"legend-label\">" + chart.data.datasets[i].label + "</span>");
				text.push("</li>");
			}
			text.push("</ul>");
			return text.join("");
		},
	},
};
var vrcReportPieOptions = {
	responsive: true,
	plugins: {
		legend: {
			display: true,
			position: "bottom",
		},
		legendCallback: function (chart) {
			// Return the HTML string here.
			var text = [];
			text.push("<ul class=\"chart-line-legend chart-pie-legend\">");
			for (var i = 0; i < chart.data.labels.length; i++) {
				text.push("<li>");
				text.push("<span class=\"legend-entry\" style=\"background-color: " + chart.data.datasets[0].backgroundColor[i] + "\"></span>");
				text.push("<span class=\"legend-label\">" + chart.data.labels[i] + "</span>");
				text.push("</li>");
			}
			text.push("</ul>");
			return text.join("");
		},
		tooltip: {
			callbacks: {
				// format the tooltip text displayed when hovering a point
				label: function(context) {
					// keep default label
					var label = context.label || "";
					if (label) {
						label += ": ";
					}
					var parsed = context.parsed || "";
					label += parsed' . $pie_tooltip_format . ';
					return " " + label;
				},
			},
		},
	},
};
var vrcReportChart = new Chart(vrc_report_ctx, {
	type: vrcReportType,
	data: (vrcReportType == "line" ? vrcReportLineData : vrcReportPieData),
	options: (vrcReportType == "line" ? vrcReportLineOptions : vrcReportPieOptions),
});
jQuery("#' . $canvas_id . '").on("vrc_update_report_chart", function() {
	jQuery(".chart-line-legend").remove();
	vrcReportChart.update();
});';

		// set the necessary script
		$this->setScript($this->chartScript);

		// return the HTML to render the chart
		return $canvas_html;
	}

	/**
	 * Returns an array of information (meta boxes) about the Chart.
	 * Information can be filtered by different positions.
	 * 
	 * @param 	mixed 	$position 	null, top, right or bottom.
	 * @param 	mixed 	$data 		null or an associative array of values.
	 * 
	 * @return 	array 	the list of meta data for the position.
	 */
	public function getChartMetaData($position = null, $data = null)
	{
		if (!count($this->rows) && !$this->getReportData()) {
			return array();
		}

		if (!count($this->chartMetaData)) {
			// prepare the meta data only once
			$this->generateChartMetaData($data);
		}

		if (!empty($position)) {
			return isset($this->chartMetaData[$position]) ? $this->chartMetaData[$position] : array();
		}

		if (!count($this->chartMetaData['top']) && !count($this->chartMetaData['right']) && !count($this->chartMetaData['bottom'])) {
			// no positions requested and no count, return an empty array
			return array();
		}

		return $this->chartMetaData;
	}

	/**
	 * Prepares the Chart meta data.
	 * 
	 * @param 	mixed 	$data 		null or an associative array of values.
	 * 
	 * @return 	void
	 */
	private function generateChartMetaData($data = null)
	{
		// reset container
		$this->chartMetaData = array();

		if (!count($this->rows)) {
			return;
		}

		// whether custom data has been requested
		$is_custom_data = (is_array($data) && isset($data['keys']) && is_array($data['keys']));
		$good_threshold = is_array($data) && isset($data['threshold']) ? (int)$data['threshold'] : 60;

		// currency symbol to format some data
		$currency_symb = VikRentCar::getCurrencySymb();

		// all meta box containers
		$meta_top 	 = array();
		$meta_right  = array();
		$meta_bottom = array();

		// collect data
		$dates_pool 	= array();
		$revenue_pool 	= array();
		$tbookings_pool = array();
		$occupancy_pool = array();
		$nightsbkd_pool = array();
		$nightsbkd_totl = array();
		foreach ($this->rows as $ind => $row) {
			foreach ($row as $field) {
				if ($field['key'] == 'day') {
					$dates_pool[$ind] = (isset($field['callback']) && is_callable($field['callback']) ? $field['callback']($field['value']) : $field['value']);
				} elseif ($field['key'] == 'revenue') {
					$revenue_pool[$ind] = $field['value'];
				} elseif ($field['key'] == 'tot_bookings') {
					$tbookings_pool[$ind] = $field['value'];
				} elseif ($field['key'] == 'occupancy') {
					$occupancy_pool[$ind] = $field['value'];
				} elseif ($field['key'] == 'days_booked') {
					$nightsbkd_pool[$ind] = $field['value'];
					$nightsbkd_totl[$ind] = (isset($field['callback']) && is_callable($field['callback']) ? $field['callback']($field['value']) : $field['value']);
				}
			}
		}
		
		// get min/max data
		$min_occ = min($occupancy_pool);
		$max_occ = max($occupancy_pool);
		$min_occ_dt = $dates_pool[array_search($min_occ, $occupancy_pool)];
		$max_occ_dt = $dates_pool[array_search($max_occ, $occupancy_pool)];
		$min_tbook = min($tbookings_pool);
		$max_tbook = max($tbookings_pool);
		$min_tbook_dt = $dates_pool[array_search($min_tbook, $tbookings_pool)];
		$max_tbook_dt = $dates_pool[array_search($max_tbook, $tbookings_pool)];
		$max_revenue = max($revenue_pool);
		$max_revenue_dt = $dates_pool[array_search($max_revenue, $revenue_pool)];
		$max_nightsbk = max($nightsbkd_pool);
		$max_nightsbk_dt = $nightsbkd_pool[array_search($max_nightsbk, $nightsbkd_pool)];
		// override max nights booked with value formatted by the callback
		$max_nightsbk = $nightsbkd_totl[array_search($max_nightsbk, $nightsbkd_pool)];
		//

		// populate Chart meta boxes
		$occ_lbl = trim(str_replace('%', '', JText::_('VRCREPORTREVENUEPOCC')));
		array_push($meta_top, array(
			'key' 	=> 'occupancy',
			'label' => $occ_lbl,
			'value' => $max_occ . ' %',
			'class' => ($is_custom_data && $max_occ < $good_threshold ? 'vrc-report-chart-meta-min' : 'vrc-report-chart-meta-max'),
			'descr' => ($is_custom_data ? '' : $max_occ_dt),
		));
		if ($min_occ != $max_occ) {
			array_push($meta_top, array(
				'key' 	=> 'occupancy',
				'label' => $occ_lbl,
				'value' => $min_occ . ' %',
				'class' => 'vrc-report-chart-meta-min',
				'descr' => ($is_custom_data ? '' : $min_occ_dt),
			));
		}
		array_push($meta_right, array(
			'key' 	=> 'tot_bookings',
			'label' => JText::_('VRCREPORTREVENUETOTB'),
			'value' => $max_tbook,
			'class' => ($is_custom_data && $max_occ < $good_threshold ? 'vrc-report-chart-meta-min' : 'vrc-report-chart-meta-max'),
			'descr' => ($is_custom_data ? '' : $max_tbook_dt),
		));
		if ($min_tbook != $max_tbook) {
			array_push($meta_right, array(
				'key' 	=> 'tot_bookings',
				'label' => JText::_('VRCREPORTREVENUETOTB'),
				'value' => $min_tbook,
				'class' => 'vrc-report-chart-meta-min',
				'descr' => ($is_custom_data ? '' : $min_tbook_dt),
			));
		}
		if (!is_array($data) || ($is_custom_data && in_array('revenue', $data['keys']))) {
			array_push($meta_bottom, array(
				'key' 	=> 'revenue',
				'label' => JText::_('VRCREPORTREVENUEREV'),
				'value' => $currency_symb . ' ' . VikRentCar::numberFormat($max_revenue),
				'class' => 'vrc-report-chart-meta-max',
				'descr' => ($is_custom_data ? '' : $max_revenue_dt),
			));
		}
		if ($is_custom_data && in_array('days_booked', $data['keys'])) {
			array_push($meta_bottom, array(
				'key' 	=> 'days_booked',
				'label' => JText::_('VRCGRAPHTOTNIGHTSLBL'),
				'value' => $max_nightsbk,
				'class' => ($is_custom_data && $max_occ < $good_threshold ? 'vrc-report-chart-meta-min' : 'vrc-report-chart-meta-max'),
				'descr' => ($is_custom_data ? '' : $max_nightsbk_dt),
			));
		}

		// build container for all positions
		$this->chartMetaData = array(
			'top' 	 => $meta_top,
			'right'  => $meta_right,
			'bottom' => $meta_bottom,
		);
	}

}
