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
 * This class provides a common interface for
 * Path handling in the Wordpress CMS plugins.
 *
 * @since 10.0
 */
class JPath
{
	/**
	 * Chmods files and directories recursively to given permissions.
	 *
	 * @param   string   $path        Root path to begin changing mode [without trailing slash].
	 * @param   string   $filemode    Octal representation of the value to change file mode to [null = no change].
	 * @param   string   $foldermode  Octal representation of the value to change folder mode to [null = no change].
	 *
	 * @return  boolean  True if successful (one fail means the whole operation failed).
	 */
	public static function setPermissions($path, $filemode = '0644', $foldermode = '0755')
	{
		// initialise return value
		$ret = true;

		if (is_dir($path))
		{
			$dh = opendir($path);

			while ($file = readdir($dh))
			{
				if ($file != '.' && $file != '..')
				{
					$fullpath = $path . '/' . $file;

					if (is_dir($fullpath))
					{
						if (!self::setPermissions($fullpath, $filemode, $foldermode))
						{
							$ret = false;
						}
					}
					else
					{
						if (isset($filemode))
						{
							if (!@chmod($fullpath, octdec($filemode)))
							{
								$ret = false;
							}
						}
					}
				}
			}

			closedir($dh);

			if (isset($foldermode))
			{
				if (!@chmod($path, octdec($foldermode)))
				{
					$ret = false;
				}
			}
		}
		else
		{
			if (isset($filemode))
			{
				$ret = @chmod($path, octdec($filemode));
			}
		}

		return $ret;
	}

	/**
	 * Function to strip additional / or \ in a path name.
	 *
	 * @param   string  $path  The path to clean.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  The cleaned path.
	 */
	public static function clean($path, $ds = DIRECTORY_SEPARATOR)
	{
		if (!is_string($path) && !empty($path))
		{
			$path = '';
		}

		$path = trim($path);

		if (empty($path))
		{
			throw new Exception('JPath::clean() does not accept empty paths.', 500);
		}

		// Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR.
		// If dealing with a UNC path don't forget to prepend the path with a backslash.
		if (($ds == '\\') && substr($path, 0, 2) == '\\\\')
		{
			$path = "\\" . preg_replace('#[/\\\\]+#', $ds, $path);
		}
		else
		{
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

	/**
	 * Searches the directory paths for a given file.
	 *
	 * @param   mixed   $paths  A path string or array of path strings to search in.
	 * @param   string  $file   The file name to look for.
	 *
	 * @return  mixed   The full path and file name for the target file,
	 * 					or boolean false if the file is not found in any of the paths.
	 *
	 * @since   10.1.18
	 */
	public static function find($paths, $file)
	{
		// force to array
		if (!is_array($paths) && !($paths instanceof Iterator))
		{
			settype($paths, 'array');
		}

		// start looping through the path set
		foreach ($paths as $path)
		{
			// make path safe
			$path = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
			// get the path to the file
			$fullname = $path . DIRECTORY_SEPARATOR . $file;

			// is the path based on a stream?
			if (strpos($path, '://') === false)
			{
				// not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.

				// needed for substr() later
				$path = realpath($path);
				$fullname = realpath($fullname);
			}

			/*
			 * The substr() check added to make sure that the realpath()
			 * results in a directory registered so that
			 * non-registered directories are not accessible via directory
			 * traversal attempts.
			 */
			if (file_exists($fullname) && substr($fullname, 0, strlen($path)) == $path)
			{
				return $fullname;
			}
		}

		// could not find the file in the set of paths
		return false;
	}
}
