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
 * Revenue child Class of VikRentCarReport
 */
class VikRentCarReportRevenue extends VikRentCarReport
{
	/**
	 * Property 'defaultKeySort' is used by the View that renders the report.
	 */
	public $defaultKeySort = 'day';
	/**
	 * Property 'defaultKeyOrder' is used by the View that renders the report.
	 */
	public $defaultKeyOrder = 'ASC';
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

		// get minimum pick-up and maximum drop-off for dates filters
		$df = $this->getDateFormat();
		$minpickup = 0;
		$maxdropoff = 0;
		$q = "SELECT MIN(`ritiro`) AS `minpickup`, MAX(`consegna`) AS `maxdropoff` FROM `#__vikrentcar_orders` WHERE `status`='confirmed';";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			$data = $this->dbo->loadAssoc();
			if (!empty($data['minpickup']) && !empty($data['maxdropoff'])) {
				$minpickup = $data['minpickup'];
				$maxdropoff = $data['maxdropoff'];
			}
		}
		//

		//jQuery code for the datepicker calendars and select2
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$js = 'jQuery(document).ready(function() {
			jQuery(".vrc-report-datepicker:input").datepicker({
				'.(!empty($minpickup) ? 'minDate: "'.date($df, $minpickup).'", ' : '').'
				'.(!empty($maxdropoff) ? 'maxDate: "'.date($df, $maxdropoff).'", ' : '').'
				'.(!empty($minpickup) && !empty($maxdropoff) ? 'yearRange: "'.(date('Y', $minpickup)).':'.date('Y', $maxdropoff).'", changeMonth: true, changeYear: true, ' : '').'
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
		$q = "SELECT `o`.`id`,`o`.`ts`,`o`.`days`,`o`.`ritiro`,`o`.`consegna`,`o`.`totpaid`,`o`.`order_total`,`o`.`country`,".
			"`o`.`optionals`,`o`.`cust_cost`,`o`.`cust_idiva`,`o`.`extracosts`,`o`.`tot_taxes` ".
			"FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` AS `b` ON `o`.`idbusy`=`b`.`id` ".
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
			//date
			array(
				'key' => 'day',
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEDAY')
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
			//% occupancy
			array(
				'key' => 'occupancy',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEPOCC')
			),
			//ADR
			array(
				'key' => 'adr',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEADR'),
				'tip' => JText::_('VRCREPORTREVENUEADRHELP')
			),
			//RevPAC
			array(
				'key' => 'revpar',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VRCREPORTREVENUEREVPAR'),
				'tip' => JText::_('VRCREPORTREVENUEREVPARH')
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

		$total_cars_units = $this->countCars($pidcar);

		//loop over the dates of the report to build the rows
		$from_info = getdate($from_ts);
		$to_info = getdate($to_ts);
		while ($from_info[0] <= $to_info[0]) {
			//prepare default fields for this row
			$day_ts = $from_info[0];
			$curwday = $this->getWdayString($from_info['wday'], 'short');
			$cars_sold = 0;
			$tot_bookings = 0;
			$occupancy = 0;
			$adr = 0;
			$revpar = 0;
			$taxes = 0;
			$revenue = 0;
			//calculate the report details for this day
			foreach ($bookings as $gbook) {
				if ($from_info[0] >= $gbook[0]['from_ts'] && $from_info[0] <= $gbook[0]['to_ts']) {
					//this booking affects the current day
					if (!empty($pidcar)) {
						$cars_booked = 0;
						foreach ($gbook as $sgbook) {
							if ((int)$sgbook['idcar'] == $pidcar) {
								$cars_sold++;
							}
						}
					} else {
						$cars_sold += 1;
					}
					$tot_bookings++;
					//calculate net revenue and taxes
					$tot_net = $gbook[0]['order_total'] - (float)$gbook[0]['tot_taxes'];
					$tot_net = $tot_net / (int)$gbook[0]['days'];
					$revenue += $tot_net;
					$taxes += (float)$gbook[0]['tot_taxes'] / (int)$gbook[0]['days'];
				}
			}
			$occupancy = round(($cars_sold * 100 / $total_cars_units), 2);
			$adr = $cars_sold > 0 ? $revenue / $cars_sold : 0;
			$revpar = $revenue / $total_cars_units;
			//push fields in the rows array as a new row
			array_push($this->rows, array(
				array(
					'key' => 'day',
					'callback' => function ($val) use ($df, $curwday) {
						return $curwday.', '.date($df, $val);
					},
					'value' => $day_ts
				),
				array(
					'key' => 'cars_sold',
					'attr' => array(
						'class="center"'
					),
					'value' => $cars_sold
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
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikRentCar::numberFormat($val);
					},
					'value' => $adr
				),
				array(
					'key' => 'revpar',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikRentCar::numberFormat($val);
					},
					'value' => $revpar
				),
				array(
					'key' => 'taxes',
					'attr' => array(
						'class="center"'
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
			//next day iteration
			$from_info = getdate(mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] + 1), $from_info['year']));
		}

		//sort rows
		$this->sortRows($pkrsort, $pkrorder);

		//loop over the rows to build the footer row with the totals
		$foot_cars_sold = 0;
		$foot_tot_bookings = 0;
		$foot_taxes = 0;
		$foot_revenue = 0;
		foreach ($this->rows as $row) {
			$foot_cars_sold += $row[1]['value'];
			$foot_tot_bookings += $row[2]['value'];
			$foot_taxes += $row[6]['value'];
			$foot_revenue += $row[7]['value'];
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
				'value' => $foot_tot_bookings
			),
			array(
				'value' => ''
			),
			array(
				'value' => ''
			),
			array(
				'value' => ''
			),
			array(
				'attr' => array(
					'class="center"'
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

		//Debug
		if ($this->debug) {
			$this->setWarning('path to report file = '.urlencode(dirname(__FILE__)).'<br/>');
			$this->setWarning('$total_cars_units = '.$total_cars_units.'<br/>');
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
				array_push($csvrow, (isset($field['callback']) && is_callable($field['callback']) ? $field['callback']($field['value']) : $field['value']));
			}
			array_push($csvlines, $csvrow);
		}

		//Force CSV download
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

}
