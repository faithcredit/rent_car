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
 * Backup Folder import rule.
 * 
 * @since 1.3
 */
class VRCBackupImportRuleFolder extends VRCBackupImportRule
{
	/**
	 * Executes the backup import command.
	 * 
	 * @param 	mixed  $data  The import rule instructions.
	 * 
	 * @return 	void
	 */
	public function execute($data)
	{
		if (empty($data->destination))
		{
			// destination path is missing
			throw new Exception('Invalid Folder import rule, missing destination path', 404);
		}

		if (!isset($data->files))
		{
			// source files are missing
			throw new Exception('Invalid Folder import rule, missing source files', 404);
		}

		// support the array notation for the destination
		if (is_array($data->destination))
		{
			// removed first element from destination
			$fixed = $data->destination;
			$data->destination = array_shift($fixed);
		}
		else
		{
			$fixed = null;
		}

		// check if we have a constant
		if (defined($data->destination))
		{
			// use the path defined by the plugin
			$destination = constant($data->destination);
		}
		else
		{
			// use a path relative to the system (according to the platform in use)
			$destination = JPath::clean((defined('ABSPATH') ? ABSPATH : JPATH_SITE) . '/' . $data->destination);
		}

		if ($fixed)
		{
			// re-append fixed path to destination
			$destination = JPath::clean($destination . '/' . implode('/', $fixed));
		}

		// iterate all source files
		foreach ((array) $data->files as $file)
		{
			// build source path
			$src = JPath::clean($this->path . '/' . $file);

			// make sure the source file exists
			if (!JFile::exists($src))
			{
				// source file is missing
				throw new Exception(sprintf('File to copy [%s] not found', $src), 404);
			}

			if (!empty($data->full))
			{
				// use the full relative path
				$rel = $file;
			}
			else if (!empty($data->recursive) && !empty($data->relativePath))
			{
				// get rid of the relative path
				$rel = substr($file, strlen($data->relativePath . '/'));
			}
			else
			{
				// use only the file name
				$rel = basename($file);
			}

			// build full destination path
			$fd = JPath::clean($destination . '/' . $rel);

			// get path of parent folder
			$parentDir = dirname($fd);

			// make sure the destination folder exists, otherwise create it first
			if (!JFolder::exists($parentDir))
			{
				JFolder::create($parentDir);
			}

			// try to copy the file
			if (!JFile::copy($src, $fd))
			{
				// unable to perform file copy
				throw new Exception(sprintf('Unable to copy [%s] into [%s]', $src, $fd), 500);
			}
		}
	}
}
