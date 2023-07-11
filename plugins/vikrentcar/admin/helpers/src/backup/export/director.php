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
 * Wraps the instructions used to create a backup.
 * 
 * @since 1.3
 */
class VRCBackupExportDirector
{
	/**
	 * An array of export rules.
	 * 
	 * @var VRCBackupExportRule[]
	 */
	private $rules = [];

	/**
	 * The instance used to manage the archive.
	 * 
	 * @var VRCBackupExportArchive
	 */
	private $archive;

	/**
	 * The version to use for the backup manifest.
	 * 
	 * @var string
	 */
	private $version;

	/**
	 * A lookup used to register the compatible version for each CMS.
	 * 
	 * @var array
	 */
	private $platforms = [];

	/**
	 * Class constructor.
	 * 
	 * @param 	string 	$path  The archive path.
	 */
	public function __construct($path)
	{
		// init archive manager
		$this->archive = new VRCBackupExportArchive($path);
	}

	/**
	 * Sets the version of the manifest.
	 * 
	 * @param 	string  $version
	 * 
	 * @return 	self    This object to support chaining.
	 */
	public function setVersion($version, $platform = null)
	{
		if (is_null($platform))
		{
			// register generic version
			$this->version = $version;
		}
		else
		{
			// register platform version
			$this->platforms[$platform] = $version;
		}

		return $this;
	}

	/**
	 * Creates a new export rule.
	 * 
	 * @param 	string 	$rule  The identifier of the rule to create.
	 * @param 	mixed 	$data  The instructions used for the rule setup.
	 * 
	 * @return 	self 	This object to support chaining.
	 * 
	 * @throws 	Exception
	 */
	public function createRule($rule, $data)
	{
		// build class name
		$classname = preg_replace("/_/", ' ', $rule);
		$classname = preg_replace("/\s+/", '', ucwords($classname));
		$classname = 'VRCBackupExportRule' . $classname;

		// make sure the rule class exists
		if (!class_exists($classname))
		{
			// class not found
			throw new Exception(sprintf('Cannot find [%s] export rule class', $classname), 404);
		}

		// attach the rule
		return $this->attachRule(new $classname($this->archive, $data));
	}

	/**
	 * Attaches the specified rule as export instruction.
	 * 
	 * @param 	VRCBackupExportRule  $rule  The rule to attach.
	 * 
	 * @return 	self  This object to support chaining.
	 */
	public function attachRule(VRCBackupExportRule $rule)
	{
		// register rule only if there is some data to export
		if ($rule->getData())
		{
			$this->rules[] = $rule;
		}

		return $this;
	}

	/**
	 * Returns an array of registered installer rules.
	 * 
	 * @return 	VRCBackupExportRule[]
	 */
	public function getRules()
	{
		return $this->rules;
	}

	/**
	 * Compresses the archive.
	 * 
	 * @return 	string  The archive path.
	 */
	public function compress()
	{
		// create manifest
		$manifest = $this->createManifest();

		if (defined('JSON_PRETTY_PRINT'))
		{
			$flag = JSON_PRETTY_PRINT;
		}
		else
		{
			$flag = 0;
		}

		// add manifest file into the root of the archive
		$this->archive->addBuffer(json_encode($manifest, $flag), 'manifest.json');

		// complete the backup process by creating the archive
		return $this->archive->compress();
	}

	/**
	 * Creates the backup manifest.
	 * 
	 * @return 	object 	The backup manifest to be encoded in JSON format.
	 */
	protected function createManifest()
	{
		// before to compress the archive, we need to create the installation manifest
		$manifest = new stdClass;
		$manifest->title       = basename($this->archive->getPath());
		$manifest->version     = $this->version ?: VIKRENTCAR_SOFTWARE_VERSION;
		$manifest->application = 'Vik Rent Car';
		$manifest->signature   = md5($manifest->application . ' ' . $manifest->version);
		$manifest->langtag     = '*';
		$manifest->dateCreated = JFactory::getDate()->toSql();
		$manifest->installers  = $this->getRules();

		if ($this->platforms)
		{
			$manifest->platforms = new stdClass;

			foreach ($this->platforms as $id => $version)
			{
				$manifest->platforms->{$id} = new stdClass;
				$manifest->platforms->{$id}->version   = $version;
				$manifest->platforms->{$id}->signature = md5($manifest->application . ' ' . $id . ' ' . $version);
			}
		}

		$app = JFactory::getApplication();

		/**
		 * Trigger event to allow third party plugins to manipulate the backup manifest.
		 * Fires just before performing the compression of the archive.
		 * 
		 * @param 	object                  $manifest  The backup manifest.
		 * @param 	VRCBackupExportArchive  $archive   The instance used to manage the archive.
		 * 
		 * @return 	void
		 * 
		 * @since 	1.3
		 */
		$app->triggerEvent('onCreateBackupManifestVikRentCar', [$manifest, $this->archive]);

		return $manifest;
	}
}
