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
 * Helper class for conversion-tracking (measurments).
 *
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
final class VRCConversionFactory
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var VRCConversionFactory
	 */
	protected static $instance = null;

	/**
	 * The database handler instance.
	 *
	 * @var object
	 */
	protected $dbo;

	/**
	 * The list of drivers instances loaded.
	 *
	 * @var array
	 */
	protected $drivers = [];

	/**
	 * Class constructor is protected.
	 *
	 * @see 	getInstance()
	 */
	protected function __construct()
	{
		$this->dbo = JFactory::getDbo();
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
	 * Loads a list of all available conversion tracking drivers.
	 *
	 * @return 	self
	 */
	protected function load()
	{
		$drivers_base  = VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'measurments' . DIRECTORY_SEPARATOR;
		$drivers_files = glob($drivers_base . '*.php');

		/**
		 * Trigger event to let other plugins register additional drivers.
		 *
		 * @return 	array 	A list of supported drivers.
		 */
		$list = JFactory::getApplication()->triggerEvent('onLoadMeasurmentDrivers');
		foreach ($list as $chunk) {
			// merge default driver files with the returned ones
			$drivers_files = array_merge($drivers_files, (array)$chunk);
		}

		foreach ($drivers_files as $df) {
			try {
				// require driver class file
				if (is_file($df)) {
					require_once($df);
				}

				// instantiate driver object
				$classname  = 'VRCConversionTracker' . str_replace(' ', '', ucwords(str_replace('_', ' ', basename($df, '.php'))));
				if (class_exists($classname)) {
					$driver = new $classname();
					// push driver object
					array_push($this->drivers, $driver);
				}
			} catch (Exception $e) {
				// do nothing
			}
		}

		return $this;
	}

	/**
	 * Gets the list of conditional drivers instantiated.
	 *
	 * @return 	array 	list of conditional drivers objects.
	 */
	public function getDrivers()
	{
		return $this->drivers;
	}

	/**
	 * Gets a single conditional driver instantiated.
	 * 
	 * @param 	string 	$id 	the driver identifier.
	 *
	 * @return 	mixed 	the conditional driver object, false otherwise.
	 */
	public function getDriver($id)
	{
		foreach ($this->drivers as $driver) {
			if ($driver->getIdentifier() != $id) {
				continue;
			}
			return $driver;
		}

		return false;
	}

	/**
	 * Gets a list of sorted driver names, ids and descriptions.
	 *
	 * @return 	array 	associative and sorted drivers list.
	 */
	public function getDriverNames()
	{
		$names = [];
		$pool  = [];

		foreach ($this->drivers as $driver) {
			$id 	= $driver->getIdentifier();
			$name 	= $driver->getName();
			$descr 	= $driver->getDescription();
			$drdata = new stdClass;
			$drdata->id 	= $id;
			$drdata->name 	= $name;
			$drdata->descr 	= $descr;
			$names[$name] 	= $drdata;
		}

		// apply sorting by name
		ksort($names);

		// push sorted drivers to pool
		foreach ($names as $drdata) {
			array_push($pool, $drdata);
		}

		return $pool;
	}

	/**
	 * Renders the driver params to display them within a form.
	 * 
	 * @param 	string 	$driver_id 	the driver identifier.
	 * @param 	array 	$params 	the current driver params.
	 * 
	 * @return 	string
	 */
	public function displayParams($driver_id, $params = [])
	{
		$driver = $this->getDriver($driver_id);

		if (!$driver) {
			return '<p>Driver not found.</p>';
		}

		// get admin parameters
		$config = $driver->renderParams();

		if (!is_array($config) || !count($config)) {
			return '<p>---------</p>';
		}

		// set up current params
		$params = !empty($params) && !is_array($params) ? json_decode($params, true) : $params;
		$params = !is_array($params) ? [] : $params;

		// flags for JS helpers
		$js_helpers = array();

		$html = '';
		foreach ($config as $value => $cont) {
			if (empty($value)) {
				continue;
			}
			$inp_attr = '';
			if (isset($cont['attributes'])) {
				foreach ($cont['attributes'] as $inpk => $inpv) {
					$inp_attr .= $inpk.'="'.$inpv.'" ';
				}
				$inp_attr = ' ' . rtrim($inp_attr);
			}
			$labelparts = explode('//', (isset($cont['label']) ? $cont['label'] : ''));
			$label = $labelparts[0];
			$labelhelp = isset($labelparts[1]) ? $labelparts[1] : '';
			if (!empty($cont['help'])) {
				$labelhelp = $cont['help'];
			}
			$default_paramv = isset($cont['default']) ? $cont['default'] : null;
			$html .= '<div class="vrc-param-container' . (in_array($cont['type'], array('textarea', 'visual_html')) ? ' vrc-param-container-full' : '') . '">';
			if (strlen($label) > 0 && (!isset($cont['hidden']) || $cont['hidden'] != true)) {
				$html .= '<div class="vrc-param-label">'.$label.'</div>';
			}
			$html .= '<div class="vrc-param-setting">';
			switch ($cont['type']) {
				case 'custom':
					$html .= $cont['html'];
					break;
				case 'select':
					$options = isset($cont['options']) && is_array($cont['options']) ? $cont['options'] : array();
					$is_assoc = (array_keys($options) !== range(0, count($options) - 1));
					if (isset($cont['multiple']) && $cont['multiple']) {
						$html .= '<select name="vikparams['.$value.'][]" multiple="multiple"' . $inp_attr . '>';
					} else {
						$html .= '<select name="vikparams['.$value.']"' . $inp_attr . '>';
					}
					foreach ($options as $optkey => $poption) {
						$checkval = $is_assoc ? $optkey : $poption;
						$selected = false;
						if (isset($params[$value])) {
							if (is_array($params[$value])) {
								$selected = in_array($checkval, $params[$value]);
							} else {
								$selected = ($checkval == $params[$value]);
							}
						} elseif (isset($default_paramv)) {
							if (is_array($default_paramv)) {
								$selected = in_array($checkval, $default_paramv);
							} else {
								$selected = ($checkval == $default_paramv);
							}
						}
						$html .= '<option value="' . ($is_assoc ? $optkey : $poption) . '"'.($selected ? ' selected="selected"' : '').'>'.$poption.'</option>';
					}
					$html .= '</select>';
					break;
				case 'password':
					$html .= '<div class="btn-wrapper input-append">';
					$html .= '<input type="password" name="vikparams['.$value.']" value="'.(isset($params[$value]) ? JHtml::_('esc_attr', $params[$value]) : JHtml::_('esc_attr', $default_paramv)).'" size="20"' . $inp_attr . '/>';
					$html .= '<button type="button" class="btn btn-primary" onclick="vikParamTogglePwd(this);"><i class="' . VikRentCarIcons::i('eye') . '"></i></button>';
					$html .= '</div>';
					// set flag for JS helper
					$js_helpers[] = $cont['type'];
					break;
				case 'number':
					$number_attr = array();
					if (isset($cont['min'])) {
						$number_attr[] = 'min="' . JHtml::_('esc_attr', $cont['min']) . '"';
					}
					if (isset($cont['max'])) {
						$number_attr[] = 'max="' . JHtml::_('esc_attr', $cont['max']) . '"';
					}
					if (isset($cont['step'])) {
						$number_attr[] = 'step="' . JHtml::_('esc_attr', $cont['step']) . '"';
					}
					$html .= '<input type="number" name="vikparams['.$value.']" value="'.(isset($params[$value]) ? JHtml::_('esc_attr', $params[$value]) : JHtml::_('esc_attr', $default_paramv)).'" ' . implode(' ', $number_attr) . $inp_attr . '/>';
					break;
				case 'textarea':
					$html .= '<textarea name="vikparams['.$value.']"' . $inp_attr . '>'.(isset($params[$value]) ? JHtml::_('esc_textarea', $params[$value]) : JHtml::_('esc_textarea', $default_paramv)).'</textarea>';
					break;
				case 'visual_html':
					$tarea_cont = isset($params[$value]) ? JHtml::_('esc_textarea', $params[$value]) : JHtml::_('esc_textarea', $default_paramv);
					$tarea_attr = isset($cont['attributes']) && is_array($cont['attributes']) ? $cont['attributes'] : array();
					$editor_opts = isset($cont['editor_opts']) && is_array($cont['editor_opts']) ? $cont['editor_opts'] : array();
					$editor_btns = isset($cont['editor_btns']) && is_array($cont['editor_btns']) ? $cont['editor_btns'] : array();
					$html .= VikRentCar::getVrcApplication()->renderVisualEditor('vikparams[' . $value . ']', $tarea_cont, $tarea_attr, $editor_opts, $editor_btns);
					break;
				case 'hidden':
					$html .= '<input type="hidden" name="vikparams['.$value.']" value="'.(isset($params[$value]) ? JHtml::_('esc_attr', $params[$value]) : JHtml::_('esc_attr', $default_paramv)).'"' . $inp_attr . '/>';
					break;
				case 'checkbox':
					// always display a hidden input value turned off before the actual checkbox to support the "off" (0) status
					$html .= '<input type="hidden" name="vikparams['.$value.']" value="0" />';
					$html .= VikRentCar::getVrcApplication()->printYesNoButtons('vikparams['.$value.']', JText::_('VBYES'), JText::_('VBNO'), (isset($params[$value]) ? (int)$params[$value] : (int)$default_paramv), 1, 0);
					break;
				default:
					$html .= '<input type="text" name="vikparams['.$value.']" value="'.(isset($params[$value]) ? JHtml::_('esc_attr', $params[$value]) : JHtml::_('esc_attr', $default_paramv)).'" size="20"' . $inp_attr . '/>';
					break;
			}
			if (strlen($labelhelp) > 0) {
				$html .= '<span class="vrc-param-setting-comment">'.$labelhelp.'</span>';
			}
			$html .= '</div>';
			$html .= '</div>';
		}

		// JS helper functions
		if (in_array('password', $js_helpers)) {
			// toggle the password fields
			$html .= "\n" . '<script>' . "\n";
			$html .= 'function vikParamTogglePwd(elem) {' . "\n";
			$html .= '	var btn = jQuery(elem), inp = btn.parent().find("input").first();' . "\n";
			$html .= '	if (!inp || !inp.length) {return false;}' . "\n";
			$html .= '	var inp_type = inp.attr("type");' . "\n";
			$html .= '	inp.attr("type", (inp_type == "password" ? "text" : "password"));' . "\n";
			$html .= '}' . "\n";
			$html .= "\n" . '</script>' . "\n";
		}

		return $html;
	}
}
