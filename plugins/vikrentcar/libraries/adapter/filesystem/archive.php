<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.filesystem
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * An Archive handling class.
 *
 * @since 10.0
 */
class JArchive
{
	/**
	 * Extracts a zip file to a specific destination.
	 * 
	 * @param 	string 	 $src 	The source path to zip file.
	 * @param 	string 	 $dest 	The destination path for exctracted folder.
	 * 
	 * @return 	boolean  True on success, false otherwise.
	 */
	public static function extract($src, $dest)
	{
		if (!function_exists('WP_Filesystem'))
		{
			// include this file that declares the functions WP_Filesystem and unzip_file
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		
		/**
		 * @link https://codex.wordpress.org/Function_Reference/unzip_file
		 */
		WP_Filesystem();

		return unzip_file($src, $dest) === true;
	}
}
