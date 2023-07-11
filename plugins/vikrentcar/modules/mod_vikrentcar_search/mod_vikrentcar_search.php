<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_search
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

// require helper class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php';

// module main file

VikRentCar::loadFontAwesome();
if (method_exists('VikRentCar', 'getTracker')) {
	// invoke the Tracker Class
	VikRentCar::getTracker();
}
// get widget id
$randid = str_replace('mod_vikrentcar_search-', '', $params->get('widget_id', rand(1, 999)));
// get widget base URL
$baseurl = VRC_MODULES_URI;

require JModuleHelper::getLayoutPath('mod_vikrentcar_search', $params->get('layout', 'default'));
