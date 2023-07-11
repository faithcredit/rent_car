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
 * Backups director class.
 * 
 * @since 1.3
 */
class VRCBackupManager
{
	/**
	 * Indicates the minimum required version while creating a
	 * new backup on Joomla. This value should be changed everytime
	 * something in the database structure is altered.
	 * 
	 * @var string
	 */
	const MINIMUM_REQUIRED_VERSION_JOOMLA = '1.15.0';

	/**
	 * Indicates the minimum required version while creating a
	 * new backup on WordPress. This value should be changed everytime
	 * something in the database structure is altered.
	 * 
	 * @var string
	 */
	const MINIMUM_REQUIRED_VERSION_WORDPRESS = '1.3.0';

	/**
	 * An associative array containing the supported export types, where the
	 * key is equals to the type ID and the value is the type instance.
	 * 
	 * @var array
	 */
	protected static $exportTypes = null;

	/**
	 * Creates a new backup.
	 * 
	 * @param 	string  $type     The type of backup to execute.
	 * @param 	array   $options  A configuration array.
	 *                            - folder     string  The path in which the archive should be saved.
	 *                                                 if not specified, the system temporary path will be used.
	 *                            - filename   string  An optional filename to use for the archive. If not specified
	 *                                                 the filename will be equals to the current time.
	 *                            - prefix     string  An optional prefix to prepend to the filename.
	 * 
	 * @return 	string  The path of the backup (a ZIP archive).
	 * 
	 * @throws 	Exception
	 */
	public static function create($type, array $options = [])
	{
		// ignore the maximum execution time
		set_time_limit(0);

		$app = JFactory::getApplication();

		if (empty($options['folder']))
		{
			// before starting the export, make sure the temporary folder is supported
			$options['folder'] = $app->get('tmp_path');

			if (!$options['folder'] || !JFolder::exists($options['folder']))
			{
				throw new Exception('The temporary folder seems to be not set', 500);
			}

			// remove trailing directory separator
			$options['folder'] = preg_replace("/[\/\\\\]$/", '', $options['folder']);
		}

		if (empty($options['filename']))
		{
			// use the current date and time as file name
			$options['filename'] = 'backup_' . $type . '_' . JFactory::getDate()->format('Y-m-d H:i:s');
		}

		if (!empty($options['prefix']))
		{
			// include a prefix before the file name
			$options['filename'] = $options['prefix'] . $options['filename'];
		}

		// build archive path
		$path = $options['folder'] . DIRECTORY_SEPARATOR . $options['filename'];
		
		// create backup export director
		$director = new VRCBackupExportDirector($path);

		// set the manifest version equals to the minimum required one
		$director->setVersion(static::MINIMUM_REQUIRED_VERSION_JOOMLA, 'joomla');
		$director->setVersion(static::MINIMUM_REQUIRED_VERSION_WORDPRESS, 'wordpress');

		// fetch all the supported export types
		$exportTypes = static::getExportTypes();

		// check whether the requested support type exists
		if (!isset($exportTypes[$type]))
		{
			// type not found
			throw new Exception(sprintf('Cannot import [%s] export type', $type), 404);
		}

		// get export type instance
		$handler = $exportTypes[$type];

		$error = null;

		try
		{
			// build the installers manifest
			$handler->build($director);

			/**
			 * Trigger event to allow third party plugins to extend the backup feature.
			 * This hook is useful to include third-party tables and files into the
			 * backup archive.
			 * 
			 * It is possible to attach a database table into the backup by using:
			 * $director->attachRule('sqlfile', '#__extensions');
			 * 
			 * @param 	string 	                  $type      The type of backup to execute.
			 * @param 	VRCBackupExportDirector   $director  The instance used to manage the backup.
			 * @param 	array                     $options   An array of options.
			 * 
			 * @return 	void
			 * 
			 * @since 	1.3
			 */
			$app->triggerEvent('onBuildBackupVikRentCar', [$type, $director, $options]);

			// compress the archive and obtain the full path
			$archivePath = $director->compress();
		}
		catch (Exception $e)
		{
			// catch any error
			$error = $e;
		}

		// always delete archive folder
		JFolder::delete($path);

		if ($error)
		{
			// in case of error, propagate it only after cleaning the dump
			throw $error;
		}

		return $archivePath;
	}

	/**
	 * Restores an existing backup.
	 * 
	 * @param 	string 	$path  The path of the backup to restore.
	 * 
	 * @return 	void
	 * 
	 * @throws 	Exception
	 */
	public static function restore($path)
	{
		// ignore the maximum execution time
		set_time_limit(0);

		// make sure the archive exists
		if (!JFile::exists($path))
		{
			// unable to find the specified archive
			throw new Exception(sprintf('Backup [%s] not found', $path), 404);
		}

		// create a unique extraction folder
		$extractdir = dirname($path) . DIRECTORY_SEPARATOR . uniqid();

		// extract the given backup
		$status = VRCArchiveFactory::extract($path, $extractdir);

		if (!$status)
		{
			// cannot extract the archive
			throw new Exception(sprintf('Unable to extract [%s] into [%s]', $path, $extractdir), 500);
		}

		// create backup import director
		$director = new VRCBackupImportDirector($extractdir);

		// set the manifest version equals to the minimum required one, according to the CMS in use
		if (defined('ABSPATH'))
		{
			$director->setVersion(static::MINIMUM_REQUIRED_VERSION_WORDPRESS);
		}
		else
		{
			$director->setVersion(static::MINIMUM_REQUIRED_VERSION_JOOMLA);
		}

		$error = null;

		try
		{
			// process the backup
			$director->process();
		}
		catch (Exception $e)
		{
			$error = $e;
		}

		// always delete extracted folder
		JFolder::delete($extractdir);

		if ($error)
		{
			// in case of error, propagate it only after cleaning the dump
			throw $error;
		}
	}

	/**
	 * Returns a list of supported export types.
	 * 
	 * @return 	array
	 */
	public static function getExportTypes()
	{
		if (!is_null(static::$exportTypes))
		{
			// export types already fetched
			return static::$exportTypes;
		}

		// register default include paths
		$includePaths = [
			dirname(__FILE__) . DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR . 'type',
		];	

		/**
		 * Trigger event to allow third party plugins to register additional include paths,
		 * from which the system can load other backup handlers.
		 * 
		 * @return 	mixed  An array of include paths or a string.
		 * 
		 * @since 	1.3
		 */
		$paths = JFactory::getApplication()->triggerEvent('onLoadBackupExportTypesVikRentCar');

		// merge returned paths with the existing ones
		foreach ($paths as $path)
		{
			if (is_string($path))
			{
				$includePaths[] = $path;
			}
			else if (is_array($path))
			{
				$includePaths = array_merge($includePaths, $path);
			}
		}

		static::$exportTypes = [];

		// iterate include paths to fetch all the supported export types
		foreach ($includePaths as $path)
		{
			// get all PHP files inside the folder
			$files = JFolder::files($path, '\.php$', $recurse = false, $fullpath = true);

			// iterate all PHP files
			foreach ($files as $file)
			{
				// get file name without extension
				$type = basename($file, '.php');

				// load the file
				require_once $file;

				// build class name
				$classname = 'VRCBackupExportType' . ucfirst($type);

				// check whether the class exists
				if (!class_exists($classname))
				{
					throw new Exception(sprintf('Cannot find [%s] export type class', $classname), 404);
				}

				// instantiate and register export type handler
				static::$exportTypes[$type] = new $classname();
			}
		}

		return static::$exportTypes;
	}
}
