<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( file_exists( WP_PLUGIN_DIR . '/sg-security/sg-security.php' ) ) {
	return;
}

require_once dirname( __FILE__ ) . '/vendor/siteground/siteground-data/src/Settings.php';

use SiteGround_Data\Settings;

$settings = new Settings();

$settings->stop_collecting_data();
