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
 * Defines an abstract adapter able to extract the archives by using the
 * native tools provided by Joomla! CMS.
 * 
 * @since 1.3
 */
abstract class VRCArchiveAdapter implements VRCArchiveType
{
	/**
	 * Extracts the file from a package.
	 * 
	 * @param 	string  $source       The path of the package.
	 * @param 	string 	$destination  The folder in which the files should be extracted.
	 * 
	 * @return 	bool    True on success, false otherwise.
	 */
	public function extract($source, $destination)
	{
		if (!class_exists('JArchive'))
		{
			// get temporary path
			$tmp_path = JFactory::getApplication()->get('tmp_path');

			// instantiate archive class
			$archive = new Joomla\Archive\Archive(array('tmp_path' => $tmp_path));

			// extract the archive
			return $archive->extract($source, $destination);
		}

		// backward compatibility
		return JArchive::extract($source, $destination);
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
		if (!JFile::exists($source))
		{
			// the selected archive doesn't exist
			throw new Exception(sprintf('Archive [%s] not found', $source), 404);
		}

		$app = JFactory::getApplication();

		// prepare headers
		$app->setHeader('Content-Disposition', 'attachment; filename=' . basename($source));
		$app->setHeader('Content-Length', filesize($source));

		// send headers
		$app->sendHeaders();

		// use fopen to properly download large files
		$handle = fopen($source, 'rb');

		// read 1MB per cycle
		$chunk_size = 1024 * 1024;

		while (!feof($handle))
		{
			echo fread($handle, $chunk_size);
			ob_flush();
			flush();
		}

		fclose($handle);
	}

	/**
	 * Compresses the specified folder into an archive.
	 * 
	 * @param 	string  $source       The path of the folder to compress.
	 * @param 	string 	$destination  The path of the resulting archive.
	 * 
	 * @return 	bool    True on success, false otherwise.
	 */
	abstract public function compress($source, $destination);
}
