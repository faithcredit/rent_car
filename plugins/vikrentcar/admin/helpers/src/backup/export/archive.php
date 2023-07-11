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
 * Manager used to handle the backup archive into the file system.
 * 
 * @since 1.3
 */
class VRCBackupExportArchive
{
	/**
	 * The archive path.
	 * 
	 * @var string
	 */
	private $path;

	/**
	 * Class constructor.
	 * 
	 * @param 	$path  The relative path in which the archive should be created.
	 */
	public function __construct($path)
	{
		$this->path = $path;

		if (JFolder::exists($this->path))
		{
			// clean path if already occupied
			JFolder::delete($this->path);
		}

		// create archive folder
		if (!JFolder::create($this->path))
		{
			throw new Exception(sprintf('Unable to create the backup folder: %s', $this->path), 500);
		}
	}

	/**
	 * Returns the archive path.
	 * 
	 * @return 	string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Creates a new empty folder, only if not exists.
	 * 
	 * @return 	boolean
	 */
	public function addEmptyFolder($folder)
	{
		if (!JFolder::exists($folder))
		{
			// in case the folder doesn't exist, create it
			return JFolder::create($folder);
		}

		// the folder already exists
		return true;
	}

	/**
	 * Copies the specified file into the archive.
	 * 
	 * @param 	string 	 $src   The source absolute path.
	 * @param 	string 	 $dest  The destination relative path.
	 * 
	 * @return 	boolean  True on success, false otherwise.
	 */
	public function addFile($src, $dest)
	{
		// build the destination path
		$dest = JPath::clean($this->path . '/' . $dest);

		// create directory in which the file should be placed
		if (!$this->addEmptyFolder(dirname($dest)))
		{
			throw new Exception(sprintf('Cannot create folder: %s', $folder), 500);
		}

		// copy the source file into the archive
		return JFile::copy($src, $dest);
	}

	/**
	 * Opens a new file in append mode and writes there the given buffer.
	 * 
	 * @param 	string 	$buffer  The buffer to write.
	 * @param 	string 	$dest    The destination relative path.
	 * 
	 * @return 	mixed   The written bytes on success, false otherwise.
	 */
	public function addBuffer($buffer, $dest)
	{
		// build the destination path
		$dest = JPath::clean($this->path . '/' . $dest);

		// create directory in which the file should be placed
		if (!$this->addEmptyFolder(dirname($dest)))
		{
			throw new Exception(sprintf('Cannot create folder: %s', $folder), 500);
		}

		// open file in append mode
		$fp = fopen($dest, 'a');
		// attempt to write the buffer into the file
		$bytes = fwrite($fp, $buffer);
		// close file pointer
		fclose($fp);

		return $bytes;
	}

	/**
	 * Compresses the archive.
	 * 
	 * @param 	string 	$name  An optional name to use for the archive. If not specified, it will be 
	 *                         equals to the folder to compress.
	 * 
	 * @return 	string  The archive path.
	 */
	public function compress($name = null)
	{
		$destination = $this->path;

		if ($name)
		{
			// in case a name is specified, go to the parent folder and
			// append the file name at the end
			$destination = dirname($destination) . DIRECTORY_SEPARATOR . $name;
		}

		if (!preg_match("/\.zip$/i", $destination))
		{
			// concatenate the file extension if missing
			$destination .= '.zip';
		}

		// try to compress the archive
		$status = VRCArchiveFactory::compress($this->path, $destination);

		if (!$status)
		{
			throw new Exception(sprintf('Unable to compress the archive: %s', $destination), 500);
		}

		return $destination;
	}
}
