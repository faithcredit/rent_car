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

JLoader::import('adapter.filesystem.path');
JLoader::import('adapter.filesystem.folder');

/**
 * This class provides a common interface for
 * Files handling in the Wordpress CMS plugins.
 *
 * @since 10.0
 */
class JFile
{
	/**
	 * Makes file name safe to use.
	 *
	 * @param   string  $file  The name of the file (not full path).
	 *
	 * @return  string  The sanitised string.
	 */
	public static function makeSafe($file)
	{
		// remove any trailing dots, as those aren't ever valid file names.
		$file = rtrim($file, '.');

		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');

		return trim(preg_replace($regex, '', $file));
	}

	/**
	 * Moves an uploaded file to a destination folder.
	 *
	 * @param   string   $src 	The name of the php (temporary) uploaded file.
	 * @param   string   $dest 	The path (including filename) to move the uploaded file to.
	 *
	 * @return  boolean  True on success.
	 */
	public static function upload($src, $dest)
	{
		$ret = false;

		// ensure that the paths are valid and clean
		$src  = JPath::clean($src);
		$dest = JPath::clean($dest);

		// create the destination directory if it does not exist
		$baseDir = dirname($dest);

		if (!file_exists($baseDir))
		{
			JFolder::create($baseDir);
		}

		if (is_writeable($baseDir) && move_uploaded_file($src, $dest))
		{
			// short circuit to prevent file permission errors
			if (JPath::setPermissions($dest))
			{
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Write contents to a file.
	 *
	 * @param   string   $file    The full file path.
	 * @param   string   $buffer  The buffer to write.
	 *
	 * @return  boolean  True on success.
	 */
	public static function write($file, $buffer)
	{
		@set_time_limit(ini_get('max_execution_time'));

		$file = JPath::clean($file);

		// If the destination directory doesn't exist we need to create it
		if (!file_exists(dirname($file)))
		{
			if (JFolder::create(dirname($file)) == false)
			{
				return false;
			}
		}
		
		return is_int(file_put_contents($file, $buffer));
	}

	/**
	 * Copy a source file to a destination.
	 *
	 * @param   string   $src   The full file path.
	 * @param   string   $dest 	The full destination path.
	 *
	 * @return  boolean  True on success.
	 */
	public static function copy($src, $dest)
	{
		return @copy(JPath::clean($src), JPath::clean($dest));
	}

	/**
	 * Delete one or multiple files.
	 *
	 * @param   mixed 	 $file 	The file path-name or array of file path-names.
	 *
	 * @return  boolean  True on success.
	 */
	public static function delete($file)
	{
		if (is_array($file))
		{
			$files = $file;
		}
		else
		{
			$files = array($file);
		}

		foreach ($files as $file)
		{
			$file = JPath::clean($file);

			if (is_file($file))
			{
				// Try making the file writable first. If it's read-only, it can't be deleted
				// on Windows, even if the parent folder is writable
				@chmod($file, 0777);

				// The file should be removable as long as the owner is www-data
				if (!@unlink($file))
				{
					// impossible to remove the file, stop the process immediatelly
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param   string   $file  File path
	 *
	 * @return  boolean  True if path is a file.
	 *
	 * @since   10.1.23
	 */
	public static function exists($file)
	{
		return is_file(JPath::clean($file));
	}
}
