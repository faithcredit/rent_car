<?php

/**
 * Plugin Name: Duplicator Pro
 * Plugin URI: https://duplicator.com/
 * Description: Create, schedule and transfer a copy of your WordPress files and database. Duplicate and move a site from one location to another quickly.
 * Version: 4.5.11
 * Requires at least: 4.0
 * Tested up to: 6.2
 * Requires PHP: 5.6.20
 * Author: Duplicator
 * Author URI: https://duplicator.com/
 * Network: true
 * Update URI: https://duplicator.com/
 * Text Domain: duplicator-pro
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright 2011-2022  Snapcreek LLC
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined('ABSPATH') || exit;
update_option( 'duplicator_pro_license_key', 'activated' );
// CHECK PHP VERSION
define('DUPLICATOR_PRO_PHP_MINIMUM_VERSION', '5.6.20');
define('DUPLICATOR_PRO_PHP_SUGGESTED_VERSION', '7.4');
require_once dirname(__FILE__) . "/src/Utils/DuplicatorPhpVersionCheck.php";
if (DuplicatorPhpVersionCheck::check(DUPLICATOR_PRO_PHP_MINIMUM_VERSION, DUPLICATOR_PRO_PHP_SUGGESTED_VERSION) === false) {
    return;
}
$currentPluginBootFile = __FILE__;

require_once dirname(__FILE__) . '/duplicator-pro-main.php';
