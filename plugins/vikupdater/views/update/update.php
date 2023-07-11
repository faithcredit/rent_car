<?php
/**
 * @package     VikUpdater
 * @subpackage  views
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

require dirname(__FILE__).DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'default.php';

function vikupdater_add_update_page() {
	add_options_page( __('VikUpdater', 'vikupdater'), __('VikUpdater', 'vikupdater'), 'manage_options', 'vikupdater', 'vikupdater_render_update_page');
}

add_action('admin_menu', 'vikupdater_add_update_page');

function insert_jquery() {
    wp_enqueue_script('jquery');
}

add_action( 'wp_enqueue_scripts', 'insert_jquery' );


function vikupdater_add_update_style() {
	if (get_current_screen()->base == 'settings_page_vikupdater') {
		wp_register_style('vikupdater_updater_update_css', VIKUPDATER_ASSETS_URI . 'css/vikupdater.css', false, 1.0, 'all');
		wp_enqueue_style('vikupdater_updater_update_css');
	}
}
add_action('admin_enqueue_scripts', 'vikupdater_add_update_style'); 
