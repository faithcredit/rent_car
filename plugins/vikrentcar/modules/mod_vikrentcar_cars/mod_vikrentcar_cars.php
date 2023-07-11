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

require_once VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikrentcar.php';

if (method_exists('VikRentCar', 'loadPreferredColorStyles')) {
	VikRentCar::loadPreferredColorStyles();
}

// Include the syndicate functions only once
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php';

// get widget id
$randid = str_replace('mod_vikrentcar_cars-', '', $params->get('widget_id', rand(1, 999)));
// get widget base URL
$baseurl = VRC_MODULES_URI;

$document = JFactory::getDocument();

$params->def('numb', 4);
$params->def('query', 'price');
$params->def('order', 'asc');
$params->def('catid', 0);
$params->def('querycat', 'price');
$params->def('currency', '&euro;');
$params->def('showcatname', 1);
$showcatname = intval($params->get('showcatname')) == 1 ? true : false;

$cars = Modvikrentcar_carsHelper::getCars($params);
$cars = Modvikrentcar_carsHelper::limitRes($cars, $params);

require JModuleHelper::getLayoutPath('mod_vikrentcar_cars', $params->get('layout', 'default'));

