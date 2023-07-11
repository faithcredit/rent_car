<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_cars
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2019 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

jimport('adapter.module.widget');

/**
 * Cars Module implementation for WP
 *
 * @see 	JWidget
 * @since 	1.0
 */
class ModVikrentcarCars_Widget extends JWidget
{
	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		// attach the absolute path of the module folder
		parent::__construct(dirname(__FILE__));
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param 	array 	$new_instance 	Values just sent to be saved.
	 * @param 	array 	$old_instance 	Previously saved values from database.
	 *
	 * @return 	array 	Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance)
	{
		$new_instance['title'] = !empty($new_instance['title']) ? strip_tags($new_instance['title']) : '';
		$new_instance['numb']  = intval($new_instance['numb']);
		$new_instance['show_desc']  = intval($new_instance['show_desc']);
		$new_instance['showcatname']  = intval($new_instance['showcatname']);
		$new_instance['layoutlist']  = intval($new_instance['layoutlist']);
		$new_instance['numb_carrow']  = intval($new_instance['numb_carrow']);
		$new_instance['autoplay']  = intval($new_instance['autoplay']);
		$new_instance['pagination'] = intval($new_instance['pagination']);
		$new_instance['navigation'] = intval($new_instance['navigation']);
		$new_instance['navigation'] = intval($new_instance['navigation']);

		return $new_instance;
	}
}
