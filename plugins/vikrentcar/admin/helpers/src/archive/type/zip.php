<?php
/** 
 * @package     VikRentCar
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Implementor used to compress and extract ZIP archives.
 * 
 * @since 1.3
 */
class VRCArchiveTypeZip extends VRCArchiveAdapter
{
	/**
	 * Compresses the specified folder into an archive.
	 * 
	 * @param 	string  $source       The path of the folder to compress.
	 * @param 	string 	$destination  The path of the resulting archive.
	 * 
	 * @return 	bool    True on success, false otherwise.
	 */
	public function compress($source, $destination)
	{
		if (!class_exists('ZipArchive'))
		{
			// ZipArchive class is mandatory to create a package
			throw new Exception('The ZipArchive class is not installed on this server.', 500);
		}

		// in case the destination path is already occupied, delete it first
		if (JFile::exists($destination))
		{
			JFile::delete($destination);
		}

		// scan all the files contained in the specified folder
		$files = JFolder::files($source, $filter = '.', $recursive = true, $full = true);

		// sanitize source path
		$source = rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		// init package
		$zip = new ZipArchive;
		$zip->open($destination, ZipArchive::CREATE);

		foreach ($files as $file)
		{
			// get rid of base path from file
			$file = str_replace($source, '', $file);

			// extract directories from file path
			$chunks = preg_split("/[\/\\\\]+/", $file);
			// and ignore the file name
			array_pop($chunks);

			$folder = '';

			foreach ($chunks as $dir)
			{
				$folder .= $dir . '/';

				// check whether the folder exists
				if (!$zip->locateName($folder))
				{
					// nope, create it
					$zip->addEmptyDir($folder);
				}
			}

			// attach file to zip
			$zip->addFile($source . $file, $file);
		}

		// complete compression
		return $zip->close();
	}

	/**
	 * Downloads the specified archive.
	 * 
	 * @param 	string  $source  The path of the folder to compress.
	 * 
	 * @return 	void
	 * 
	 * @throws 	Exception
	 */
	public function download($source)
	{
		// set header content type for a correct download
		JFactory::getApplication()->setHeader('Content-Type', 'application/zip');

		// invoke parent to start downloading the archive
		parent::download($source);
	}
}
