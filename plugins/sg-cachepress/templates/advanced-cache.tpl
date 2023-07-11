<?php

$files = array(
	'/plugins/sg-cachepress/core/Helper/File_Cacher_Trait.php',
	'/plugins/sg-cachepress/core/File_Cacher/Cache.php',
);


$config_path = WP_CONTENT_DIR . '/sgo-config.php';

foreach ( $files as $filename ) {
	$path = WP_CONTENT_DIR . $filename;

	if ( ! file_exists( $path ) ) {
		return;
	}

	include_once( $path );
}

use SiteGround_Optimizer\Helper\File_Cacher_Trait;
use SiteGround_Optimizer\File_Cacher\Cache;

$cache = new Cache( $config_path );
$cache->get_cache();
