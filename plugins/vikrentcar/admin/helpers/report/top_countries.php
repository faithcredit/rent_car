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
 * Top Countries child Class of VikRentCarReport
 */
class VikRentCarReportTopCountries extends VikRentCarReport
{
	/**
	 * Property 'defaultKeySort' is used by the View that renders the report.
	 */
	public $defaultKeySort = 'revenue';
	/**
	 * Property 'defaultKeyOrder' is used by the View that renders the report.
	 */
	public $defaultKeyOrder = 'DESC';
	/**
	 * Property 'exportAllowed' is used by the View to display the export button.
	 */
	public $exportAllowed = 1;
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
			//do not run this method twice, as it could load JS and CSS files.
			return $this->reportFilters;
		}

		//get VRC Application Object
		$vrc_app = new VrcApplication();

		//load the jQuery UI Datepicker
		$this->loadDatePicker();

		//From Date Filter
		$filter_opt = array(
			'label' => '<label for="fromdate">'.JText::_('VRCREPORTSDATEFROM').'</label>',
			'html' => '<input type="text" id="fromdate" name="fromdate" value="" class="vrc-report-datepicker vrc-report-datepicker-from" />',
			'type' => 'calendar',
			'name' => 'fromdate'
		);
		array_push($this->reportFilters, $filter_opt);

		//To Date Filter
		$filter_opt = array(
			'label' => '<label for="todate">'.JText::_('VRCREPORTSDATETO').'</label>',
			'html' => '<input type="text" id="todate" name="todate" value="" class="vrc-report-datepicker vrc-report-datepicker-to" />',
			'type' => 'calendar',
			'name' => 'todate'
		);
		array_push($this->reportFilters, $filter_opt);

		//Car ID filter
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

		//jQuery code for the datepicker calendars and select2
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$js = 'jQuery(document).ready(function() {
			jQuery(".vrc-report-datepicker:input").datepicker({
				maxDate: 0,
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
			//Export functions may set errors rather than exiting the process, and the View may continue the execution to attempt to render the report.
			return false;
		}
		//Input fields and other vars
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$pidcar = VikRequest::getInt('idcar', '', 'request');
		$pkrsort = VikRequest::getString('krsort', $this->defaultKeySort, 'request');
		$pkrsort = empty($pkrsort) ? $this->defaultKeySort : $pkrsort;
		$pkrorder = VikRequest::getString('krorder', $this->defaultKeyOrder, 'request');
		$pkrorder = empty($pkrorder) ? $this->defaultKeyOrder : $pkrorder;
		$pkrorder = $pkrorder == 'DESC' ? 'DESC' : 'ASC';
		$currency_symb = VikRentCar::getCurrencySymb();
		$df = $this->getDateFormat();
		if (empty($ptodate)) {
			$ptodate = $pfromdate;
		}
		//Get dates timestamps
		$from_ts = VikRentCar::getDateTimestamp($pfromdate, 0, 0);
		$to_ts = VikRentCar::getDateTimestamp($ptodate, 23, 59, 59);
		if (empty($pfromdate) || empty($from_ts) || empty($to_ts)) {
			$this->setError(JText::_('VRCREPORTSERRNODATES'));
			return false;
		}

		//Query to obtain the records
		$records = array();
		$q = "SELECT `o`.*,".
			"`co`.`idcustomer`,`c`.`country` AS `customer_country` ".
			"FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` AS `b` ON `o`.`idbusy`=`b`.`id` ".
			"LEFT JOIN `#__vikrentcar_customers_orders` AS `co` ON `co`.`idorder`=`o`.`id` LEFT JOIN `#__vikrentcar_customers` AS `c` ON `c`.`id`=`co`.`idcustomer` ".
			"WHERE `o`.`status`='confirmed' AND `b`.`stop_sales`=0 AND `o`.`consegna`>=".$from_ts." AND `o`.`ritiro`<=".$to_ts." ".(!empty($pidcar) ? "AND `o`.`idcar`=".(int)$pidcar." " : "").
			"ORDER BY `o`.`ritiro` ASC, `o`.`id` ASC;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$records = $this->dbo->loadAssocList();
		}
		if (!count($records)) {
			$this->setError(JText::_('VRCREPORTSERRNORESERV'));
			return false;
		}

		//nest records with multiple cars booked inside sub-array
		$bookings = array();
		foreach ($records as $v) {
			if (!isset($bookings[$v['id']])) {
				$bookings[$v['id']] = array();
			}
			//calculate the from_ts and to_ts values for later comparison
			$in_info = getdate($v['ritiro']);
			$out_info = getdate($v['consegna']);
			$v['from_ts'] = mktime(0, 0, 0, $in_info['mon'], $in_info['mday'], $in_info['year']);
			$v['to_ts'] = mktime(23, 59, 59, $out_info['mon'], $out_info['mday'], $out_info['year']);
			//
			array_push($bookings[$v['id']], $v);
		}

		//define the columns of the report
		$this->cols = array(
			//country
			array(
				'key' => 'country',
				'sortable' => 1,
				'label' => JText::_('VRCREPORTTOPCOUNTRIESC')
			),
			//cars sold
			array(
				'key' => 'cars_sold',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUERSOLD')
			),
			//total bookings
			array(
				'key' => 'tot_bookings',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUETOTB')
			),
			//Options/Extras
			array(
				'key' => 'opts',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTOPTIONSEXTRAS'),
				'tip' => JText::_('VRCREPORTOPTIONSEXTRASHELP')
			),
			//Taxes
			array(
				'key' => 'taxes',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUETAX')
			),
			//Revenue
			array(
				'key' => 'revenue',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEREV')
			)
		);

		//loop over the bookings to build the top countries
		$to_info = getdate($to_ts);
		$to_ts_midnight = mktime(0, 0, 0, $to_info['mon'], $to_info['mday'], $to_info['year']);
		$top_countries = array();
		$country_stats = array(
			'cars_sold' => 0,
			'tot_bookings' => 0,
			'opts' => 0,
			'taxes' => 0,
			'revenue' => 0
		);
		foreach ($bookings as $gbook) {
			$useful_nights = $gbook[0]['days'];
			if ($gbook[0]['from_ts'] < $from_ts || $gbook[0]['to_ts'] > $to_ts_midnight) {
				//the dates of the booking exceed the filter, so we need to calculate the useful nights between the date interval filter
				$useful_nights = 0;
				$book_from_info = getdate($gbook[0]['from_ts']);
				for ($i = 0; $i < $gbook[0]['days']; $i++) {
					$book_night_ts = mktime(0, 0, 0, $book_from_info['mon'], ($book_from_info['mday'] + $i), $book_from_info['year']);
					if ($book_night_ts >= $from_ts && $book_night_ts <= $to_ts_midnight) {
						$useful_nights++;
					}
				}
			}
			if ($useful_nights < 1) {
				continue;
			}
			$country = 'unknown';
			if (!empty($gbook[0]['country'])) {
				$country = $gbook[0]['country'];
			} elseif (!empty($gbook[0]['customer_country'])) {
				$country = $gbook[0]['customer_country'];
			}
			if (!isset($top_countries[$country])) {
				$top_countries[$country] = $country_stats;
			}
			$top_countries[$country]['cars_sold'] += 1;
			$top_countries[$country]['tot_bookings']++;
			//calculate net revenue and taxes
			$tot_net = $gbook[0]['order_total'] - (float)$gbook[0]['tot_taxes'];
			$tot_net = $tot_net / (int)$gbook[0]['days'] * $useful_nights;
			$get_car_costs = 0;
			//loop over the cars booked to sum up the cars costs
			foreach ($gbook as $b) {
				$get_car_costs += !empty($b['cust_cost']) ? (float)$b['cust_cost'] : (!empty($b['car_cost']) ? (float)$b['car_cost'] : 0);
			}
			//if there are no cars costs, we set the options/extras to 0 or we may give an invalid result
			$tot_opts = $get_car_costs > 0 && $gbook[0]['order_total'] > $get_car_costs ? ($gbook[0]['order_total'] - $get_car_costs) : 0;
			$tot_opts = $tot_opts >= 0 ? $tot_opts : 0;
			$top_countries[$country]['opts'] += $tot_opts;
			//
			$top_countries[$country]['taxes'] += (float)$gbook[0]['tot_taxes'] / (int)$gbook[0]['days'] * $useful_nights;
			$top_countries[$country]['revenue'] += $tot_net;
		}

		$countries_map = $this->getCountriesMap(array_keys($top_countries));

		//loop over the top countries to build the rows of the report
		foreach ($top_countries as $country => $data) {
			//push data in the rows array as a new row
			array_push($this->rows, array(
				array(
					'key' => 'country',
					'attr' => array(
						'class="vrc-report-topcountries-countryname"'
					),
					'callback' => function ($val) use ($country) {
						if (is_file(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$country.'.png')) {
							return $val.'<img src="'.VRC_ADMIN_URI.'resources/countries/'.$country.'.png" title="'.$country.'" class="vrc-country-flag vrc-country-flag-left" />';
						}
						return $val;
					},
					'no_csv_callback' => 1,
					'value' => (isset($countries_map[$country]) ? $countries_map[$country] : $country)
				),
				array(
					'key' => 'cars_sold',
					'attr' => array(
						'class="center"'
					),
					'value' => $data['cars_sold']
				),
				array(
					'key' => 'tot_bookings',
					'attr' => array(
						'class="center"'
					),
					'value' => $data['tot_bookings']
				),
				array(
					'key' => 'opts',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikRentCar::numberFormat($val);
					},
					'value' => $data['opts']
				),
				array(
					'key' => 'taxes',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikRentCar::numberFormat($val);
					},
					'value' => $data['taxes']
				),
				array(
					'key' => 'revenue',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikRentCar::numberFormat($val);
					},
					'value' => $data['revenue']
				)
			));
		}

		//sort rows
		$this->sortRows($pkrsort, $pkrorder);

		//Debug
		if ($this->debug) {
			$this->setWarning('path to report file = '.urlencode(dirname(__FILE__)).'<br/>');
			$this->setWarning('$bookings:<pre>'.print_r($bookings, true).'</pre><br/>');
		}
		//

		return true;
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
		if (!$this->getReportData()) {
			return false;
		}
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');

		$csvlines = array();

		//Push the head of the CSV file
		$csvcols = array();
		foreach ($this->cols as $col) {
			array_push($csvcols, $col['label']);
		}
		array_push($csvlines, $csvcols);

		//Push the rows of the CSV file
		foreach ($this->rows as $row) {
			$csvrow = array();
			foreach ($row as $field) {
				array_push($csvrow, (isset($field['callback']) && is_callable($field['callback']) && !isset($field['no_csv_callback']) ? $field['callback']($field['value']) : $field['value']));
			}
			array_push($csvlines, $csvrow);
		}

		//Force CSV download
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="'.$this->reportName.'-'.str_replace('/', '_', $pfromdate).'-'.str_replace('/', '_', $ptodate).'.csv"');
		$outstream = fopen("php://output", 'w');
		foreach ($csvlines as $csvline) {
			fputcsv($outstream, $csvline);
		}
		fclose($outstream);
		exit;
	}

	/**
	 * Maps the 3-char country codes to their full names.
	 * Translates also the 'unknown' country.
	 *
	 * @param 	array  		$countries
	 *
	 * @return 	array
	 */
	private function getCountriesMap($countries)
	{
		$map = array();

		if (in_array('unknown', $countries)) {
			$map['unknown'] = JText::_('VRCREPORTTOPCUNKNC');
			foreach ($countries as $k => $v) {
				if ($v == 'unknown') {
					unset($countries[$k]);
				}
			}
		}

		if (count($countries)) {
			$clauses = array();
			foreach ($countries as $country) {
				array_push($clauses, $this->dbo->quote($country));
			}
			$q = "SELECT `country_name`,`country_3_code` FROM `#__vikrentcar_countries` WHERE `country_3_code` IN (".implode(', ', $clauses).");";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			if ($this->dbo->getNumRows() > 0) {
				$records = $this->dbo->loadAssocList();
				foreach ($records as $v) {
					$map[$v['country_3_code']] = $v['country_name'];
				}
			}
		}

		return $map;
	}

}
