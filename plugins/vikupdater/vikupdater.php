<?php
/*
Plugin Name:  VikUpdater
Plugin URI:   https://wordpress.org/plugins/vikupdater/
Description:  Plugin used to update VikWP plugins and themes.
Version:      1.4.4
Author:       E4J s.r.l.
Author URI:   https://vikwp.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  vikupdater
Domain Path:  /languages
*/

defined('ABSPATH') or die('No script kiddies please!');


require dirname(__FILE__).DIRECTORY_SEPARATOR."autoload.php";

register_activation_hook(__FILE__, array('VikUpdaterInstall', 'activatePlugin'));
register_deactivation_hook(__FILE__, array('VikUpdaterInstall', 'deactivatePlugin'));
add_action('init', array('VikUpdaterInstall', 'init'));
add_action('init', array('VikUpdaterLibrary', 'checkUpdates'));
