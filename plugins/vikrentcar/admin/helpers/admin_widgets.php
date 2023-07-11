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
 * Helper class for the administrator widgets.
 * 
 * @since 	1.2.0
 */
class VikRentCarHelperAdminWidgets
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var VikRentCarHelperAdminWidgets
	 */
	protected static $instance = null;

	/**
	 * An array to store some cached/static values.
	 *
	 * @var array
	 */
	protected static $helper = null;

	/**
	 * The database handler instance.
	 *
	 * @var object
	 */
	protected $dbo;

	/**
	 * The list of widget instances loaded.
	 *
	 * @var array
	 */
	protected $widgets;

	/**
	 * Class constructor is protected.
	 *
	 * @see 	getInstance()
	 */
	protected function __construct()
	{
		static::$helper = array();
		$this->dbo = JFactory::getDbo();
		$this->widgets = array();
		$this->load();
	}

	/**
	 * Returns the global object, either
	 * a new instance or the existing instance
	 * if the class was already instantiated.
	 *
	 * @return 	self 	A new instance of the class.
	 */
	public static function getInstance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Loads a list of all available admin widgets.
	 *
	 * @return 	self
	 */
	protected function load()
	{
		// require main/parent admin-widget class
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'admin_widget.php');

		$widgets_base  = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR;
		$widgets_files = glob($widgets_base . '*.php');

		/**
		 * Trigger event to let other plugins register additional widgets.
		 *
		 * @return 	array 	A list of supported widgets.
		 */
		$list = JFactory::getApplication()->triggerEvent('onLoadAdminWidgets');
		foreach ($list as $chunk) {
			// merge default widget files with the returned ones
			$widgets_files = array_merge($widgets_files, (array)$chunk);
		}

		foreach ($widgets_files as $wf) {
			try {
				// require widget class file
				if (is_file($wf)) {
					require_once($wf);
				}

				// instantiate widget object
				$classname  = 'VikRentCarAdminWidget' . str_replace(' ', '', ucwords(str_replace('_', ' ', basename($wf, '.php'))));
				if (class_exists($classname)) {
					$widget = new $classname();
					// push widget object
					array_push($this->widgets, $widget);
				}
			} catch (Exception $e) {
				// do nothing
			}
		}

		return $this;
	}

	/**
	 * Gets the default map of admin widgets.
	 *
	 * @return 	object 	the associative map of sections,
	 * 					containers and widgets.
	 */
	protected function getDefaultWidgetsMap()
	{
		$sections = array();

		// build default sections
		
		// top section
		$section = new stdClass;
		$section->name = 'Top';
		$section->containers = array();
		// start container
		$container = new stdClass;
		$container->size = 'small';
		$container->widgets = array(
			'reminders.php',
		);
		// push container
		array_push($section->containers, $container);
		// start container
		$container = new stdClass;
		$container->size = 'large';
		$container->widgets = array(
			'collecting_today.php',
			'returning_today.php',
			'sticky_notes.php',
		);
		// push container
		array_push($section->containers, $container);
		// push section
		array_push($sections, $section);

		// second section
		$section = new stdClass;
		$section->name = 'Middle';
		$section->containers = array();
		// start container
		$container = new stdClass;
		$container->size = 'full';
		$container->widgets = array(
			'next_rentals.php',
		);
		// push container
		array_push($section->containers, $container);
		// push section
		array_push($sections, $section);

		// third section
		$section = new stdClass;
		$section->name = 'Bottom';
		$section->containers = array();
		// start container
		$container = new stdClass;
		$container->size = 'medium';
		$container->widgets = array(
			'cars_locked.php',
		);
		// push container
		array_push($section->containers, $container);
		// start container
		$container = new stdClass;
		$container->size = 'small';
		$container->widgets = array(
			'report.php',
		);
		// push container
		array_push($section->containers, $container);
		// start container
		$container = new stdClass;
		$container->size = 'small';
		$container->widgets = array(
			'visitors_counter.php',
		);
		// push container
		array_push($section->containers, $container);
		// push section
		array_push($sections, $section);

		// compose the final map object
		$map = new stdClass;
		$map->sections = $sections;
		
		return $map;
	}

	/**
	 * Gets the list of admin widgets instantiated.
	 *
	 * @return 	array 	list of admin widget objects.
	 */
	public function getWidgets()
	{
		return $this->widgets;
	}

	/**
	 * Gets a single admin widget instantiated.
	 * 
	 * @param 	string 	$id 	the widget identifier.
	 *
	 * @return 	mixed 	the admin widget object, false otherwise.
	 */
	public function getWidget($id)
	{
		foreach ($this->widgets as $widget) {
			if ($widget->getIdentifier() != $id) {
				continue;
			}
			return $widget;
		}

		return false;
	}

	/**
	 * Gets a list of sorted widget names, ids and descriptions.
	 *
	 * @return 	array 	associative and sorted widgets list.
	 */
	public function getWidgetNames()
	{
		$names = array();
		$pool  = array();

		foreach ($this->widgets as $widget) {
			$id 	= $widget->getIdentifier();
			$name 	= $widget->getName();
			$descr 	= $widget->getDescription();
			$wtdata = new stdClass;
			$wtdata->id 	= $id;
			$wtdata->name 	= $name;
			$wtdata->descr 	= $descr;
			$names[$name] 	= $wtdata;
		}

		// apply sorting by name
		ksort($names);

		// push sorted widgets to pool
		foreach ($names as $wtdata) {
			array_push($pool, $wtdata);
		}

		return $pool;
	}

	/**
	 * Gets the current or default map of admin widgets.
	 * If no map currently sets, stores the default map.
	 *
	 * @return 	array 	the associative map of sections,
	 * 					containers and widgets.
	 */
	public function getWidgetsMap()
	{
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='admin_widgets_map';";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			$map = json_decode($this->dbo->loadResult());
			return is_object($map) && isset($map->sections) && count($map->sections) ? $map : $this->getDefaultWidgetsMap();
		}

		$default_map = $this->getDefaultWidgetsMap();
		$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('admin_widgets_map', " . $this->dbo->quote(json_encode($default_map)) . ");";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		return $default_map;
	}

	/**
	 * Updates the map of admin widgets.
	 * 
	 * @param 	array 	$sections 	the list of sections for the map.
	 *
	 * @return 	bool 	True on success, false otherwise.
	 */
	public function updateWidgetsMap($sections)
	{
		if (!is_array($sections) || !count($sections)) {
			return false;
		}

		// prepare new map object
		$map = new stdClass;
		$map->sections = $sections;

		$q = "UPDATE `#__vikrentcar_config` SET `setting`=" . $this->dbo->quote(json_encode($map)) . " WHERE `param`='admin_widgets_map';";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		return true;
	}

	/**
	 * Restores the default admin widgets map.
	 * First it resets the settings of each widget.
	 *
	 * @return 	bool 	True on success, false otherwise.
	 */
	public function restoreDefaultWidgetsMap()
	{
		foreach ($this->widgets as $widget) {
			$widget->resetSettings();
		}

		$default_map = $this->getDefaultWidgetsMap();

		return $this->updateWidgetsMap($default_map->sections);
	}

	/**
	 * Forces the rendering of a specific widget identifier.
	 * 
	 * @param 	string 	$id 	the widget identifier.
	 * @param 	mixed 	$data 	anything to pass to the widget.
	 *
	 * @return 	mixed 	void on success, false otherwise.
	 */
	public function renderWidget($id, $data = null)
	{
		foreach ($this->widgets as $widget) {
			if ($widget->getIdentifier() != $id) {
				continue;
			}
			return $widget->render($data);
		}

		return false;
	}

	/**
	 * Maps the size identifier to a CSS class.
	 * 
	 * @param 	string 	$size 	the container size identifier.
	 *
	 * @return 	string 	the full CSS class for the container.
	 */
	public function getContainerCssClass($size)
	{
		$css_size_map = array(
			'small' => 'vrc-admin-widgets-container-small',
			'medium' => 'vrc-admin-widgets-container-medium',
			'large' => 'vrc-admin-widgets-container-large',
			'full' => 'vrc-admin-widgets-container-fullwidth',
		);

		return isset($css_size_map[$size]) ? $css_size_map[$size] : $css_size_map['full'];
	}

	/**
	 * Returns an associative array with the class names for the containers.
	 *
	 * @return 	array 	a text representation list of all sizes.
	 */
	public function getContainerClassNames()
	{
		return array(
			'full' => array(
				'name' => JText::_('VRC_WIDGETS_CONTFULL'),
				'css' => $this->getContainerCssClass('full'),
			),
			'large' => array(
				'name' => JText::_('VRC_WIDGETS_CONTLARGE'),
				'css' => $this->getContainerCssClass('large'),
			),
			'medium' => array(
				'name' => JText::_('VRC_WIDGETS_CONTMEDIUM'),
				'css' => $this->getContainerCssClass('medium'),
			),
			'small' => array(
				'name' => JText::_('VRC_WIDGETS_CONTSMALL'),
				'css' => $this->getContainerCssClass('small'),
			),
		);
	}

	/**
	 * Maps the size identifier to the corresponding name.
	 * 
	 * @param 	string 	$size 	the container size identifier.
	 *
	 * @return 	string 	the size name for the container.
	 */
	public function getContainerName($size)
	{
		$names = $this->getContainerClassNames();

		return isset($names[$size]) ? $names[$size]['name'] : $names['full']['name'];
	}

	/**
	 * The first time the widget's customizer is open, the welcome is displayed.
	 * Congig value >= 1 means hide the welcome text, 0 or lower means show it.
	 * 
	 * @return 	bool
	 */
	public function showWelcome()
	{
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='admin_widgets_welcome';";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			return ((int)$this->dbo->loadResult() < 1);
		}

		$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('admin_widgets_welcome', '0');";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		return true;
	}

	/**
	 * Updates the status of the welcome message for the widget's customizer.
	 * Congig value >= 1 means hide the welcome text, 0 or lower means show it.
	 * 
	 * @param 	int 	$val 	the new value to set in the configuration.
	 * 
	 * @return 	void
	 */
	public function updateWelcome($val)
	{
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='admin_widgets_welcome';";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=" . $this->dbo->quote((int)$val) . " WHERE `param`='admin_widgets_welcome';";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			return;
		}

		$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('admin_widgets_welcome', " . $this->dbo->quote((int)$val) . ");";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		return;
	}
}
