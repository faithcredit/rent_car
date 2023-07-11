<?php
/** 
 * @package   	VikRentCar
 * @subpackage 	core
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// Software version
define('VIKRENTCAR_SOFTWARE_VERSION', '1.3.1');

// Base path
define('VIKRENTCAR_BASE', dirname(__FILE__));

// Libraries path
define('VIKRENTCAR_LIBRARIES', VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . 'libraries');

// Languages path
define('VIKRENTCAR_LANG', basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'languages');
/**
 * The admin and site languages are no more used by the plugin.
 *
 * @deprecated 1.0.0
 */
define('VIKRENTCAR_SITE_LANG', basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'language');
define('VIKRENTCAR_ADMIN_LANG', basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'language');

// Assets URI
define('VIKRENTCAR_SITE_ASSETS_URI', plugin_dir_url(__FILE__) . 'site/resources/');
define('VIKRENTCAR_ADMIN_ASSETS_URI', plugin_dir_url(__FILE__) . 'admin/resources/');

// Debug flag
define('VIKRENTCAR_DEBUG', false);

// URI Constants for admin and site sections (with trailing slash)
defined('VRC_ADMIN_URI') or define('VRC_ADMIN_URI', plugin_dir_url(__FILE__).'admin/');
defined('VRC_SITE_URI') or define('VRC_SITE_URI', plugin_dir_url(__FILE__).'site/');
defined('VRC_BASE_URI') or define('VRC_BASE_URI', plugin_dir_url(__FILE__));
defined('VRC_MODULES_URI') or define('VRC_MODULES_URI', plugin_dir_url(__FILE__));
defined('VRC_ADMIN_URI_REL') or define('VRC_ADMIN_URI_REL', plugin_dir_url(__FILE__).'admin/');
defined('VRC_SITE_URI_REL') or define('VRC_SITE_URI_REL', plugin_dir_url(__FILE__).'site/');

// Path Constants for admin and site sections (with NO trailing directory separator)
defined('VRC_ADMIN_PATH') or define('VRC_ADMIN_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'admin');
defined('VRC_SITE_PATH') or define('VRC_SITE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'site');

// Other Constants that may not be available in the framework
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

/**
 * We define the base path constant for the upload dir
 * used to upload the customer documents onto the sub-dirs.
 * 
 * @since 	1.2.0
 */
$customer_upload_base_path = VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources';
$customer_upload_base_uri = VRC_ADMIN_URI . 'resources/';
$media_upload_base_path    = $customer_upload_base_path;
$media_upload_base_uri 	   = $customer_upload_base_uri;
$upload_dir = wp_upload_dir();
if (is_array($upload_dir) && !empty($upload_dir['basedir']) && !empty($upload_dir['baseurl'])) {
	$customer_upload_base_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar';
	$customer_upload_base_uri = rtrim($upload_dir['baseurl'], '/') . '/' . 'vikrentcar' . '/';
	// define proper values for the media directory
	$media_upload_base_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar' . DIRECTORY_SEPARATOR . 'media';
	$media_upload_base_uri 	= rtrim($upload_dir['baseurl'], '/') . '/' . 'vikrentcar' . '/' . 'media' . '/';
}
defined('VRC_CUSTOMERS_PATH') or define('VRC_CUSTOMERS_PATH', $customer_upload_base_path);
defined('VRC_CUSTOMERS_URI') or define('VRC_CUSTOMERS_URI', $customer_upload_base_uri);

/**
 * We define the base path and URI for the media dir.
 * 
 * @since 	1.3.0
 */
defined('VRC_MEDIA_PATH') or define('VRC_MEDIA_PATH', $media_upload_base_path);
defined('VRC_MEDIA_URI') or define('VRC_MEDIA_URI', $media_upload_base_uri);

/**
 * Site pre-process flag.
 * When this flag is enabled, the plugin will try to dispatch the
 * site controller within the "init" action. This is made by 
 * fetching the shortcode assigned to the current URI.
 *
 * By disabling this flag, the site controller will be dispatched 
 * with the headers already sent.
 */
define('VIKRENTCAR_SITE_PREPROCESS', true);
