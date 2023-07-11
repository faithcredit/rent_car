<?php
/**
 * @package     VikStripe
 * @subpackage  core
 * @author      Matteo Galletti - E4J s.r.l.
 * @copyright   Copyright (C) 2018 VikWP All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// Define plugin base path
define('VIKSTRIPE_DIR', dirname(__FILE__));
// Define plugin base URI
define('VIKSTRIPE_URI', plugin_dir_url(__FILE__));

/**
 * Imports the file of the gateway and returns the classname
 * of the file that will be instantiated by the caller.
 *
 * @param 	string 	$plugin  The name of the caller.
 *
 * @return 	mixed 	The classname of the payment if exists, otherwise false.
 */
function vikstripe_load_payment($plugin)
{
	if (!JLoader::import("{$plugin}.stripe", VIKSTRIPE_DIR))
	{
		// there is not a version available for the given plugin
		return false;
	}

	return ucwords($plugin) . 'StripePayment';
}

/**
 * Returns the path in which the payment is located.
 *
 * @param 	string 	$plugin  The name of the caller.
 *
 * @return 	mixed 	The path if exists, otherwise false.
 */
function vikstripe_get_payment_path($plugin)
{
	$path = VIKSTRIPE_DIR . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . 'stripe.php';

	if (!is_file($path))
	{
		// there is not a version available for the given plugin
		return false;
	}

	return $path;
}
