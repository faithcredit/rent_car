<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.component
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Component registry class
 *
 * @since 10.0
 */
class JComponentRegistry
{
	/**
	 * The component name.
	 *
	 * @var string
	 */
	private $option;

	/**
	 * Class constructor.
	 *
	 * @param 	string 	$option  The component name.
	 */
	public function __construct($option)
	{
		$this->option = preg_replace("/^com_/", '', $option);
	}

	/**
	 * Getter to access component settings.
	 *
	 * @param 	string  $key 	The name of the property.
	 * @param 	mixed   $def 	The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 */
	public function get($key, $def = null)
	{
		return JFactory::getApplication()->get($this->option . '.' . $key, $def);
	}

	/**
	 * Setter to insert or update component settings.
	 *
	 * @param 	string 	$key 	The name of the property.
	 * @param 	mixed 	$val 	The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property.
	 */
	public function set($key, $val)
	{
		return JFactory::getApplication()->set($this->option . '.' . $key, $val);
	}
}
