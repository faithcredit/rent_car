<?php
/**
 * SG Security
 *
 * @package           SG_Security
 * @author            SiteGround
 * @link              http://www.siteground.com/
 *
 * @wordpress-plugin
 * Plugin Name:       SiteGround Security
 * Plugin URI:        https://siteground.com
 * Description:       SiteGround Security is the all-in-one security solution for your WordPress website. Protect login & limit login attempts. User activity log. Lock system folders & more.
 * Version:           1.4.5
 * Author:            SiteGround
 * Author URI:        https://www.siteground.com
 * Text Domain:       sg-security
 * Domain Path:       /languages
 */

// Our namespace.
namespace SG_Security;

use SG_Security\Loader\Loader;
use SG_Security\Activator\Activator;
use SG_Security\Deactivator\Deactivator;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define version constant.
if ( ! defined( __NAMESPACE__ . '\VERSION' ) ) {
	define( __NAMESPACE__ . '\VERSION', '1.4.5' );
}

// Define slug constant.
if ( ! defined( __NAMESPACE__ . '\PLUGIN_SLUG' ) ) {
	define( __NAMESPACE__ . '\PLUGIN_SLUG', 'sg-security' );
}

// Define root directory.
if ( ! defined( __NAMESPACE__ . '\DIR' ) ) {
	define( __NAMESPACE__ . '\DIR', __DIR__ );
}

// Define root URL.
if ( ! defined( __NAMESPACE__ . '\URL' ) ) {
	$root_url = \trailingslashit( DIR );

	// Sanitize directory separator on Windows.
	$root_url = str_replace( '\\', '/', $root_url );

	$wp_plugin_dir = str_replace( '\\', '/', WP_PLUGIN_DIR );
	$root_url = str_replace( $wp_plugin_dir, \plugins_url(), $root_url );

	define( __NAMESPACE__ . '\URL', \untrailingslashit( $root_url ) );

	unset( $root_url );
}

require_once( \SG_Security\DIR . '/vendor/autoload.php' );

register_activation_hook( __FILE__, array( new Activator(), 'activate' ) );
register_deactivation_hook( __FILE__, array( new Deactivator(), 'deactivate' ) );

// Initialize helper.
global $sg_security_loader;

if ( ! isset( $sg_security_loader ) ) {
	$sg_security_loader = new Loader();
}
