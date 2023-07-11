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
JLoader::import('adapter.filesystem.file');

/**
 * This class provides a common interface for
 * Folder handling in the Wordpress CMS plugins.
 *
 * @since 10.0
 */
class JFolder
{
	/**
	 * Checks whether the given path exists and is a directory.
	 *
	 * @param   string   $path  Folder name.
	 *
	 * @return  boolean  True if path is a folder.
	 */
	public static function exists($path)
	{
		return is_dir(JPath::clean($path));
	}

	/**
	 * Create a folder and all necessary parent folders.
	 *
	 * @param   string   $path  A path to create from the base path.
	 * @param   integer  $mode  Directory permissions to set for folders created. 0755 by default.
	 *
	 * @return  boolean  True if successful.
	 */
	public static function create($path = '', $mode = 0755)
	{
		static $nested = 0;

		$path = JPath::clean($path);

		// check if parent dir exists
		$parent = dirname($path);

		if (!self::exists($parent))
		{
			// prevent infinite loops
			$nested++;

			if (($nested > 20) || ($parent == $path))
			{
				$nested--;

				return false;
			}

			// create the parent directory
			if (self::create($parent, $mode) !== true)
			{
				$nested--;

				return false;
			}

			// OK, parent directory has been created
			$nested--;
		}

		// check if dir already exists
		if (self::exists($path))
		{
			return true;
		}

		// first set umask (no permissions to revoke)
		$origmask = @umask(0);

		// try to create the path
		$ret = @mkdir($path, $mode);

		// reset original umask
		@umask($origmask);

		// return whether the folder has been created or not
		return $ret;
	}

	/**
	 * Utility function to read the files in a folder.
	 *
	 * @param   string   $path           The path of the folder to read.
	 * @param   string   $filter         A filter for file names.
	 * @param   mixed    $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
	 * @param   boolean  $full           True to return the full path to the file.
	 * @param   array    $exclude        Array with names of files which should not be shown in the result.
	 * @param   array    $excludefilter  Array of filter to exclude.
	 * @param   boolean  $naturalSort    False for asort, true for natsort.
	 *
	 * @return  array  	 Files in the given folder.
	 *
	 * @uses 	_items()
	 */
	public static function files($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
		$excludefilter = array('^\..*', '.*~'), $naturalSort = false)
	{
		// make sure the path is a folder
		if (!static::exists($path))
		{
			return false;
		}

		// compute the excludefilter string
		if (count($excludefilter))
		{
			$excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
		}
		else
		{
			$excludefilter_string = '';
		}

		// get the files
		$arr = self::_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, true);

		// sort the files based on either natural or alpha method
		if ($naturalSort)
		{
			natsort($arr);
		}
		else
		{
			asort($arr);
		}

		return array_values($arr);
	}

	/**
	 * Utility function to read the folders in a folder.
	 *
	 * @param   string   $path           The path of the folder to read.
	 * @param   string   $filter         A filter for folder names.
	 * @param   mixed    $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
	 * @param   boolean  $full           True to return the full path to the folders.
	 * @param   array    $exclude        Array with names of folders which should not be shown in the result.
	 * @param   array    $excludefilter  Array with regular expressions matching folders which should not be shown in the result.
	 *
	 * @return  array  	 Folders in the given folder.
	 *
	 * @uses 	_items()
	 */
	public static function folders($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
		$excludefilter = array('^\..*'))
	{
		// make sure the path is a folder
		if (!static::exists($path))
		{
			return false;
		}

		// Compute the excludefilter string
		if (count($excludefilter))
		{
			$excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
		}
		else
		{
			$excludefilter_string = '';
		}

		// Get the folders
		$arr = self::_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, false);

		// Sort the folders
		asort($arr);

		return array_values($arr);
	}

	/**
	 * Function to read the files/folders in a folder.
	 *
	 * @param   string   $path                  The path of the folder to read.
	 * @param   string   $filter                A filter for file names.
	 * @param   mixed    $recurse               True to recursively search into sub-folders, or an integer to specify the maximum depth.
	 * @param   boolean  $full                  True to return the full path to the file.
	 * @param   array    $exclude               Array with names of files which should not be shown in the result.
	 * @param   string   $excludefilter_string  Regexp of files to exclude.
	 * @param   boolean  $findfiles             True to read the files, false to read the folders.
	 *
	 * @return  array    Search for recursive files.
	 */
	protected static function _items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles)
	{
		@set_time_limit(ini_get('max_execution_time'));

		$path = JPath::clean($path);

		$arr = array();

		// Read the source directory
		if (!($handle = @opendir($path)))
		{
			return $arr;
		}

		while (($file = readdir($handle)) !== false)
		{
			if ($file != '.' && $file != '..' && !in_array($file, $exclude)
				&& (empty($excludefilter_string) || !preg_match($excludefilter_string, $file)))
			{
				// Compute the fullpath
				$fullpath = $path . '/' . $file;

				// Compute the isDir flag
				$isDir = is_dir($fullpath);

				if (($isDir xor $findfiles) && preg_match("/$filter/", $file))
				{
					// (fullpath is dir and folders are searched or fullpath is not dir and files are searched) and file matches the filter
					if ($full)
					{
						// Full path is requested
						$arr[] = $fullpath;
					}
					else
					{
						// Filename is requested
						$arr[] = $file;
					}
				}

				if ($isDir && $recurse)
				{
					// Search recursively
					if (is_int($recurse))
					{
						// Until depth 0 is reached
						$arr = array_merge($arr, self::_items($fullpath, $filter, $recurse - 1, $full, $exclude, $excludefilter_string, $findfiles));
					}
					else
					{
						$arr = array_merge($arr, self::_items($fullpath, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles));
					}
				}
			}
		}

		closedir($handle);

		return $arr;
	}

	/**
	 * Copy a source folder to a destination.
	 *
	 * @param   string   $src 	 The full folder path.
	 * @param   string   $dest 	 The full destination path.
	 * @param   string   $path 	 An optional base path to use as prefix.
	 * @param   boolean  $force  Copy the folder even if it exists.
	 *
	 * @return  boolean  True on success.
	 *
	 * @uses 	exists()
	 * @uses 	create()
	 */
	public static function copy($src, $dest, $path = '', $force = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		if ($path)
		{
			$src  = $path . '/' . $src;
			$dest = $path . '/' . $dest;
		}

		$src  = JPath::clean($src);
		$dest = JPath::clean($dest);

		// Get rid of trailing directory separators, if any
		$src = rtrim($src, DIRECTORY_SEPARATOR);
		$dest = rtrim($dest, DIRECTORY_SEPARATOR);

		if (!self::exists($src))
		{
			return false;
		}

		if (self::exists($dest) && !$force)
		{
			return false;
		}

		// Make sure the destination exists
		if (!self::create($dest))
		{
			return false;
		}

		if (!($dh = @opendir($src)))
		{
			return false;
		}
		// Walk through the directory copying files and recursing into folders.
		while (($file = readdir($dh)) !== false)
		{
			$sfid = $src . '/' . $file;
			$dfid = $dest . '/' . $file;

			switch (filetype($sfid))
			{
				case 'dir':
					if ($file != '.' && $file != '..')
					{
						$ret = self::copy($sfid, $dfid, null, $force);

						if ($ret !== true)
						{
							return $ret;
						}
					}
					break;

				case 'file':
					if (!JFile::copy($sfid, $dfid))
					{
						return false;
					}
					break;
			}
		}

		return true;
	}

	/**
	 * Delete a folder with all its sub-folders and files.
	 *
	 * @param   string   $path 	The path to the folder to remove.
	 *
	 * @return  boolean  true on success.
	 *
	 * @uses 	files()
	 * @uses 	folders()
	 */
	public static function delete($path)
	{
		@set_time_limit(ini_get('max_execution_time'));

		if (!$path)
		{
			return false;
		}

		// ensure path gets cleaned and is valid
		$path = JPath::clean($path);
		
		if (!static::exists($path))
		{
			return false;
		}

		// remove all the files in the current folder
		$files = self::files($path, '.', false, true, array(), array());

		if (!empty($files))
		{
			if (JFile::delete($files) !== true)
			{
				return false;
			}
		}

		// remove sub-folders of the current folder
		$folders = self::folders($path, '.', false, true, array(), array());

		foreach ($folders as $folder)
		{
			if (is_link($folder))
			{
				// we don't follow links
				if (JFile::delete($folder) !== true)
				{
					return false;
				}
			}
			else if (self::delete($folder) !== true)
			{
				return false;
			}
		}

		// folder should now be removed as long as the owner is www-data
		if (@rmdir($path))
		{
			return true;
		}
		
		return false;
	}
}
