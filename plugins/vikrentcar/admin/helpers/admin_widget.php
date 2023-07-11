<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Admin Widget parent Class of all sub-classes.
 * 
 * @since 	1.2.0
 */
abstract class VikRentCarAdminWidget
{
	/**
	 * The name of the widget.
	 *
	 * @var 	string
	 */
	protected $widgetName = null;

	/**
	 * The description of the widget.
	 *
	 * @var 	string
	 */
	protected $widgetDescr = '';

	/**
	 * The widget settings.
	 *
	 * @var 	mixed
	 */
	protected $widgetSettings = null;

	/**
	 * The VRC application object.
	 *
	 * @var 	object
	 */
	protected $vrc_app = null;

	/**
	 * The date format.
	 *
	 * @var 	string
	 */
	protected $df = '';

	/**
	 * The time format.
	 *
	 * @var 	string
	 */
	protected $tf = '';

	/**
	 * The widget identifier.
	 *
	 * @var 	string
	 */
	private $widgetId = null;

	/**
	 * Class constructors should define some vars for the widget in use.
	 */
	public function __construct()
	{
		$this->vrc_app = VikRentCar::getVrcApplication();
		$nowdf = VikRentCar::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$this->df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$this->df = 'm/d/Y';
		} else {
			$this->df = 'Y/m/d';
		}
		$this->tf = VikRentCar::getTimeFormat(true);
	}

	/**
	 * Gets the name of the current widget.
	 * 
	 * @return 	string 	the widget name.
	 */
	public function getName()
	{
		return $this->widgetName;
	}

	/**
	 * Gets the description for the current widget.
	 * 
	 * @return 	string 	the widget description.
	 */
	public function getDescription()
	{
		return $this->widgetDescr;
	}

	/**
	 * Gets the identifier of the current widget.
	 * 
	 * @return 	string 	the widget identifier.
	 */
	public function getIdentifier()
	{
		if (!$this->widgetId) {
			// fetch widget ID from class name
			$this->widgetId = preg_replace("/^VikRentCarAdminWidget/i", '', get_class($this));
			// place an underscore between each camelCase
			$this->widgetId = strtolower(preg_replace("/([a-z])([A-Z])/", '$1_$2', $this->widgetId));
			$this->widgetId = strtolower($this->widgetId) . '.php';
		}

		return $this->widgetId;
	}

	/**
	 * Extending Classes should define this method
	 * to render the actual output of the widget.
	 * 
	 * @param 	mixed 	$data 	anything to pass to the widget.
	 */
	abstract public function render($data = null);

	/**
	 * Tells the widget if its being rendered via AJAX.
	 * 
	 * @return 	string 	the widget identifier.
	 */
	protected function isAjaxRendering()
	{
		$widget_id  = VikRequest::getString('widget_id', '', 'request');
		$call 		= VikRequest::getString('call', '', 'request');

		return $widget_id == $this->getIdentifier() && $call == 'render';
	}

	/**
	 * Gets the configuration parameter name for the widget's settings.
	 * 
	 * @return 	string 	the param name of the settings record.
	 */
	protected function getSettingsParamName()
	{
		return 'admin_widget_' . $this->getIdentifier();
	}

	/**
	 * Loads the widget's settings from the configuration table, if any.
	 * If no record found for this widget, an empty record will be inserted.
	 * 
	 * @return 	mixed 	the widget identifier.
	 */
	protected function loadSettings()
	{
		$dbo = JFactory::getDbo();

		$param_name = $this->getSettingsParamName();

		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`=" . $dbo->quote($param_name) . ";";
		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows()) {
			// insert settings record for the widget & return null
			$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES (" . $dbo->quote($param_name) . ", '');";
			$dbo->setQuery($q);
			$dbo->execute();

			return null;
		}

		$settings = $dbo->loadResult();

		if (!strlen($settings)) {
			return null;
		}

		if (in_array(substr($settings, 0, 1), array('{', '['))) {
			// we have detected a JSON string, try to decoded it
			$decoded = json_decode($settings);
			if (function_exists('json_last_error') && json_last_error()) {
				// json is broken, reset settings and return null
				$this->resetSettings();
				return null;
			}
			// return the decoded settings
			return $decoded;
		}

		// return the plain db value otherwise
		return $settings;
	}

	/**
	 * Updates the widget's settings in the configuration table.
	 * 
	 * @param 	mixed 	$data 	the settings to store, must be a scalar.
	 * 
	 * @return 	bool 	true on success, false otherwise.
	 */
	protected function updateSettings($data)
	{
		if ($data === null || !is_scalar($data)) {
			return false;
		}

		$dbo = JFactory::getDbo();

		$param_name = $this->getSettingsParamName();

		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`=" . $dbo->quote($param_name) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// settings should be loaded first
			return false;
		}

		$q = "UPDATE `#__vikrentcar_config` SET `setting`=" . $dbo->quote($data) . " WHERE `param`=" . $dbo->quote($param_name) . ";";
		$dbo->setQuery($q);
		$dbo->execute();

		return true;
	}

	/**
	 * Resets the settings of the widget.
	 * 
	 * @return 	bool
	 */
	public function resetSettings()
	{
		$dbo = JFactory::getDbo();

		$param_name = $this->getSettingsParamName();

		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`=" . $dbo->quote($param_name) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// settings never used
			return false;
		}

		$q = "DELETE FROM `#__vikrentcar_config` WHERE `param`=" . $dbo->quote($param_name) . ";";
		$dbo->setQuery($q);
		$dbo->execute();

		return true;
	}

	/**
	 * Method invoked during AJAX requests for removing an instance of this widget.
	 * By default we try to unset the passed instance index from the settings array.
	 * If the widget does not use settings, nothing is done. Widgets could override this method.
	 * 
	 * @return 	bool 	true if settings needed to be updated, false otherwise.
	 */
	public function removeInstance()
	{
		$settings = $this->loadSettings();
		if ($settings === null || !is_array($settings)) {
			return false;
		}

		$widget_instance = VikRequest::getInt('widget_instance', -1, 'request');

		if (!isset($settings[$widget_instance])) {
			// settings index not found
			return false;
		}

		// splice the array to remove the requested settings instance
		array_splice($settings, $widget_instance, 1);

		// update widget's settings
		$this->updateSettings(json_encode($settings));

		return true;
	}

	/**
	 * Method invoked during AJAX requests for moving an instance of this widget.
	 * This occurs when dragging and dropping the same type of widget to a different position.
	 * If the widget does not use settings, nothing is done. Widgets could override this method.
	 * 
	 * @return 	bool 	true if settings needed to be updated, false otherwise.
	 */
	public function sortInstance()
	{
		$settings = $this->loadSettings();
		if ($settings === null || !is_array($settings)) {
			return false;
		}

		$widget_index_old = VikRequest::getInt('widget_index_old', -1, 'request');
		$widget_index_new = VikRequest::getInt('widget_index_new', -1, 'request');

		if (!isset($settings[$widget_index_old]) || !isset($settings[$widget_index_new])) {
			// settings index not found
			return false;
		}

		// move the settings requested from the old index to the new index
		$extracted = array_splice($settings, $widget_index_old, 1);
		array_splice($settings, $widget_index_new, 0, $extracted);

		// update widget's settings
		$this->updateSettings(json_encode($settings));

		return true;
	}

	/**
	 * Returns the name of the user currently logged in.
	 * 
	 * @return 	string 	the name of the current user.
	 */
	protected function getLoggedUserName()
	{
		$user = JFactory::getUser();
		$name = $user->name;

		return $name;
	}
}
