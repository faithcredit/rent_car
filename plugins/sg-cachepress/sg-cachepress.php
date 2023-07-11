<?php
/**
 * SG CachePress
 *
 * @package           SG_CachePress
 * @author            SiteGround
 * @link              http://www.siteground.com/
 *
 * @wordpress-plugin
 * Plugin Name:       SiteGround Optimizer
 * Plugin URI:        https://siteground.com
 * Description:       This plugin will link your WordPress application with all the performance optimizations provided by SiteGround
 * Version:           7.3.3
 * Author:            SiteGround
 * Author URI:        https://www.siteground.com
 * Text Domain:       sg-cachepress
 * Domain Path:       /languages
 */

// Our namespace.
namespace SiteGround_Optimizer;

use SiteGround_Optimizer\Loader\Loader;
use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Activator\Activator;
use SiteGround_Optimizer\Deactivator\Deactivator;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define version constant.
if ( ! defined( __NAMESPACE__ . '\VERSION' ) ) {
	define( __NAMESPACE__ . '\VERSION', '7.3.3' );
}

// Define slug constant.
if ( ! defined( __NAMESPACE__ . '\PLUGIN_SLUG' ) ) {
	define( __NAMESPACE__ . '\PLUGIN_SLUG', 'sg-cachepress' );
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

require_once( \SiteGround_Optimizer\DIR . '/vendor/autoload.php' );

register_activation_hook( __FILE__, array( new Activator(), 'activate' ) );
register_deactivation_hook( __FILE__, array( new Deactivator(), 'deactivate' ) );

// Initialize the loader.
global $siteground_optimizer_loader;

if ( ! isset( $siteground_optimizer_loader ) ) {
	$siteground_optimizer_loader = new Loader();
}
