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
 * Reports parent Class of all sub-classes
 */
abstract class VikRentCarReport
{
	protected $reportName = '';
	protected $reportFile = '';
	protected $reportFilters = array();
	protected $reportScript = '';
	protected $warning = '';
	protected $error = '';
	protected $dbo;

	protected $cols = array();
	protected $rows = array();
	protected $footerRow = array();

	/**
	 * Class constructor should define the name of
	 * the report and the filters to be displayed.
	 */
	public function __construct() {
		$this->dbo = JFactory::getDbo();
	}

	/**
	 * Extending Classes should define this method
	 * to get the name of the report.
	 */
	abstract public function getName();

	/**
	 * Extending Classes should define this method
	 * to get the name of class file.
	 */
	abstract public function getFileName();

	/**
	 * Extending Classes should define this method
	 * to get the filters of the report.
	 */
	abstract public function getFilters();

	/**
	 * Extending Classes should define this method
	 * to generate the report data (cols and rows).
	 */
	abstract public function getReportData();

	/**
	 * Loads a specific report class and returns its instance.
	 * Should be called for instantiating any report sub-class.
	 * 
	 * @param 	string 	$report 	the report file name (i.e. "revenue").
	 * 
	 * @return 	mixed 	false or requested report object.
	 */
	public static function getInstanceOf($report)
	{
		if (empty($report) || !is_string($report)) {
			return false;
		}

		if (substr($report, -4) != '.php') {
			$report .= '.php';
		}

		$report_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $report;

		if (!is_file($report_path)) {
			return false;
		}

		require_once $report_path;

		$classname = 'VikRentCarReport' . str_replace(' ', '', ucwords(str_replace('.php', '', str_replace('_', ' ', $report))));
		if (!class_exists($classname)) {
			return false;
		}

		return new $classname;
	}

	/**
	 * Injects request parameters for the report like if some filters were set.
	 * 
	 * @param 	array 	$params 	associative list of request vars to inject.
	 *
	 * @return 	self
	 */
	public function injectParams($params)
	{
		if (is_array($params) && count($params)) {
			foreach ($params as $key => $value) {
				/**
				 * For more safety across different platforms and versions (J3/J4 or WP)
				 * we inject values in the super global array as well as in the input object.
				 */
				VikRequest::setVar($key, $value, 'request');
				VikRequest::setVar($key, $value);
			}
		}

		return $this;
	}

	/**
	 * Loads the jQuery UI Datepicker.
	 * Method used only by sub-classes.
	 *
	 * @return 	self
	 */
	protected function loadDatePicker()
	{
		$vrc_app = new VrcApplication();
		$vrc_app->loadDatePicker();

		return $this;
	}

	/**
	 * Loads Charts CSS/JS assets.
	 *
	 * @return 	self
	 */
	public function loadChartsAssets()
	{
		$document = JFactory::getDocument();
		$document->addScript(VRC_ADMIN_URI . 'resources/Chart.min.js', array('version' => VIKRENTCAR_SOFTWARE_VERSION));

		return $this;
	}

	/**
	 * Loads all the cars in VRC and returns the array.
	 *
	 * @return 	array
	 */
	protected function getCars()
	{
		$cars = array();
		$q = "SELECT * FROM `#__vikrentcar_cars` ORDER BY `name` ASC;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$cars = $this->dbo->loadAssocList();
		}

		return $cars;
	}

	/**
	 * Returns the number of total units for all cars, or for a specific car.
	 * By default, the cars unpublished are skipped, and all cars are used.
	 * 
	 * @param 	[mixed] $idcar 		int or array.
	 * @param 	[int] 	$published 	true or false.
	 *
	 * @return 	int
	 */
	protected function countCars($idcar = 0, $published = 1)
	{
		$totcars = 0;
		$clauses = array();
		if (is_int($idcar) && $idcar > 0) {
			$clauses[] = "`id`=".(int)$idcar;
		} elseif (is_array($idcar) && count($idcar)) {
			$clauses[] = "`id` IN (" . implode(', ', $idcar) . ")";
		}
		if ($published) {
			$clauses[] = "`avail`=1";
		}
		$q = "SELECT SUM(`units`) FROM `#__vikrentcar_cars`".(count($clauses) ? " WHERE ".implode(' AND ', $clauses) : "").";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$totcars = (int)$this->dbo->loadResult();
		}

		return $totcars;
	}

	/**
	 * Concatenates the JavaScript rules.
	 * Method used only by sub-classes.
	 *
	 * @param 	string 		$str
	 *
	 * @return 	self
	 */
	protected function setScript($str)
	{
		$this->reportScript .= $str."\n";

		return $this;
	}

	/**
	 * Gets the current script string.
	 *
	 * @return 	string
	 */
	public function getScript()
	{
		return rtrim($this->reportScript, "\n");
	}

	/**
	 * Returns the date format in VRC for date, jQuery UI, Joomla.
	 *
	 * @param 	string 		$type
	 *
	 * @return 	string
	 */
	public function getDateFormat($type = 'date')
	{
		$nowdf = VikRentCar::getDateFormat();
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
			$juidf = 'dd/mm/yy';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
			$juidf = 'mm/dd/yy';
		} else {
			$df = 'Y/m/d';
			$juidf = 'yy/mm/dd';
		}

		switch ($type) {
			case 'jui':
				return $juidf;
			case 'joomla':
				return $nowdf;
			default:
				return $df;
		}
	}

	/**
	 * Returns the translated weekday.
	 * Uses the back-end language definitions.
	 *
	 * @param 	int 	$wday
	 * @param 	string 	$type 	use 'long' for the full name of the week, short for the 3-char version
	 *
	 * @return 	string
	 */
	protected function getWdayString($wday, $type = 'long')
	{
		$wdays_map_long = array(
			JText::_('VRWEEKDAYZERO'),
			JText::_('VRWEEKDAYONE'),
			JText::_('VRWEEKDAYTWO'),
			JText::_('VRWEEKDAYTHREE'),
			JText::_('VRWEEKDAYFOUR'),
			JText::_('VRWEEKDAYFIVE'),
			JText::_('VRWEEKDAYSIX')
		);

		$wdays_map_short = array(
			JText::_('VRSUN'),
			JText::_('VRMON'),
			JText::_('VRTUE'),
			JText::_('VRWED'),
			JText::_('VRTHU'),
			JText::_('VRFRI'),
			JText::_('VRSAT')
		);

		if ($type != 'long') {
			return isset($wdays_map_short[(int)$wday]) ? $wdays_map_short[(int)$wday] : '';
		}

		return isset($wdays_map_long[(int)$wday]) ? $wdays_map_long[(int)$wday] : '';
	}

	/**
	 * Sets the columns for this report.
	 *
	 * @param 	array 	$arr
	 *
	 * @return 	self
	 */
	protected function setReportCols($arr)
	{
		$this->cols = $arr;

		return $this;
	}

	/**
	 * Returns the columns for this report.
	 * Should be called after getReportData()
	 * or the returned array will be empty.
	 *
	 * @return 	array
	 */
	public function getReportCols()
	{
		return $this->cols;
	}

	/**
	 * Sorts the rows of the report by key.
	 *
	 * @param 	string 		$krsort 	the key attribute of the array pairs
	 * @param 	string 		$krorder 	ascending (ASC) or descending (DESC)
	 *
	 * @return 	void
	 */
	protected function sortRows($krsort, $krorder)
	{
		if (empty($krsort) || !(count($this->rows))) {
			return;
		}

		$map = array();
		foreach ($this->rows as $k => $row) {
			foreach ($row as $kk => $v) {
				if (isset($v['key']) && $v['key'] == $krsort) {
					$map[$k] = $v['value'];
				}
			}
		}
		if (!(count($map))) {
			return;
		}

		if ($krorder == 'ASC') {
			asort($map);
		} else {
			arsort($map);
		}

		$sorted = array();
		foreach ($map as $k => $v) {
			$sorted[$k] = $this->rows[$k];
		}

		$this->rows = $sorted;
	}

	/**
	 * Sets the rows for this report.
	 *
	 * @param 	array 	$arr
	 *
	 * @return 	self
	 */
	protected function setReportRows($arr)
	{
		$this->rows = $arr;

		return $this;
	}

	/**
	 * Returns the rows for this report.
	 * Should be called after getReportData()
	 * or the returned array will be empty.
	 *
	 * @return 	array
	 */
	public function getReportRows()
	{
		return $this->rows;
	}

	/**
	 * This method returns one or more rows (given the depth) generated by
	 * the current report invoked. It is useful to clean up the callbacks
	 * of the various cell-rows, to obtain a parsable result.
	 * Can be called as first method, by skipping also getReportData(). 
	 * 
	 * @param 	int 	$depth 	how many records to obtain, null for all.
	 *
	 * @return 	array 	the queried report value in the given depth.
	 * 
	 * @uses 	getReportData()
	 */
	public function getReportValues($depth = null)
	{
		if (!count($this->rows) && !$this->getReportData()) {
			return array();
		}

		$report_values = array();

		foreach ($this->rows as $rk => $row) {
			$report_values[$rk] = array();
			foreach ($row as $col => $coldata) {
				$display_value = $coldata['value'];
				if (isset($coldata['callback']) && is_callable($coldata['callback'])) {
					// launch callback
					$display_value = $coldata['callback']($coldata['value']);
				}
				// push column value
				$report_values[$rk][$coldata['key']] = array(
					'value' 		=> $coldata['value'],
					'display_value' => $display_value,
				);
			}
		}

		if (!count($report_values)) {
			return array();
		}

		if ($depth === 1) {
			// get an associative array with the first row calculated
			return $report_values[0];
		}

		if (is_int($depth) && $depth > 0 && count($report_values) >= $depth) {
			// get the requested portion of the array
			return array_slice($report_values, 0, $depth);
		}

		return $report_values;
	}

	/**
	 * Maps the columns labels to an associative array to be used for the values.
	 * 
	 * @return 	array 	associative list of column keys and labels+tips.
	 */
	public function getColumnsValues()
	{
		if (!count($this->cols)) {
			return array();
		}

		$col_values = array();

		foreach ($this->cols as $col) {
			$col_values[$col['key']] = array(
				'label' => $col['label'],
			);
			if (isset($col['tip'])) {
				$col_values[$col['key']]['tip'] = $col['tip'];
			}
		}

		return $col_values;
	}

	/**
	 * Gets a property defined by the report. Useful to get custom
	 * properties set up by a specific report maybe for the Chart.
	 * 
	 * @param 	string 	$property 	the name of the property needed.
	 * 
	 * @return 	mixed 	false on failure, property requested otherwise.
	 */
	public function getProperty($property)
	{
		if (isset($this->{$property})) {
			return $this->{$property};
		}

		return false;
	}

	/**
	 * Counts the number of days of difference between two timestamps.
	 * 
	 * @param 	int 	$to_ts 		the target end date timestamp.
	 * @param 	int 	$from_ts 	the starting date timestamp.
	 * 
	 * @return 	int 	the days of difference between from and to timestamps.
	 */
	public function countDaysTo($to_ts, $from_ts = 0)
	{
		if (empty($from_ts)) {
			$from_ts = time();
		}

		// whether DateTime can be used
		$usedt = false;

		if (class_exists('DateTime')) {
			$from_date = new DateTime(date('Y-m-d', $from_ts));
			if (method_exists($from_date, 'diff')) {
				$usedt = true;
			}
		}

		if ($usedt) {
			$to_date = new DateTime(date('Y-m-d', $to_ts));
			$daysdiff = (int)$from_date->diff($to_date)->format('%a');
			if ($to_ts < $from_ts) {
				// we need a negative integer number
				$daysdiff = $daysdiff - ($daysdiff * 2);
			}
			return $daysdiff;
		}

		return (int)round(($to_ts - $from_ts) / 86400);
	}

	/**
	 * Counts the average difference between two integers.
	 * 
	 * @param 	int 	$in_days_from 	days to the lowest timestamp.
	 * @param 	int 	$in_days_to 	days to the highest timestamp.
	 * 
	 * @return 	int 	the average number between the two values.
	 */
	public function countAverageDays($in_days_from, $in_days_to)
	{
		return (int)floor(($in_days_from + $in_days_to) / 2);
	}

	/**
	 * Sets the footer row (the totals) for this report.
	 *
	 * @param 	array 	$arr
	 *
	 * @return 	self
	 */
	protected function setReportFooterRow($arr)
	{
		$this->footerRow = $arr;

		return $this;
	}

	/**
	 * Returns the footer row for this report.
	 * Should be called after getReportData()
	 * or the returned array will be empty.
	 *
	 * @return 	array
	 */
	public function getReportFooterRow()
	{
		return $this->footerRow;
	}

	/**
	 * Sub-classes can extend this method to define the
	 * the canvas HTML tag for rendenring the Chart.
	 * Any necessary script shall be set within this method.
	 * Data can be passed as a mixed value through the argument.
	 * This is the first method to be called when working with the Chart.
	 * 
	 * @param 	mixed 	$data 	any necessary value to render the Chart.
	 *
	 * @return 	string 	the HTML of the canvas element.
	 */
	public function getChart($data = null)
	{
		return '';
	}

	/**
	 * Sub-classes can extend this method to define the
	 * the title of the Chart to be rendered.
	 *
	 * @return 	string 	the title of the Chart.
	 */
	public function getChartTitle()
	{
		return '';
	}

	/**
	 * Sub-classes can extend this method to define
	 * the meta data for the Chart containing stats.
	 * An array for each meta-data should be returned.
	 * 
	 * @param 	mixed 	$position 	string for the meta-data position
	 * 								in the Chart (top, right, bottom).
	 * @param 	mixed 	$data 		some arguments to be passed.
	 *
	 * @return 	array
	 */
	public function getChartMetaData($position = null, $data = null)
	{
		return array();
	}

	/**
	 * Sets warning messages by concatenating the existing ones.
	 * Method used only by sub-classes.
	 *
	 * @param 	string 		$str
	 *
	 * @return 	self
	 */
	protected function setWarning($str)
	{
		$this->warning .= $str."\n";

		return $this;
	}

	/**
	 * Gets the current warning string.
	 *
	 * @return 	string
	 */
	public function getWarning()
	{
		return rtrim($this->warning, "\n");
	}

	/**
	 * Sets errors by concatenating the existing ones.
	 * Method used only by sub-classes.
	 *
	 * @param 	string 		$str
	 *
	 * @return 	self
	 */
	protected function setError($str)
	{
		$this->error .= $str."\n";

		return $this;
	}

	/**
	 * Gets the current error string.
	 *
	 * @return 	string
	 */
	public function getError()
	{
		return rtrim($this->error, "\n");
	}
}
