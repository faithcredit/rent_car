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
 * Backup import rule abstraction.
 * 
 * @since 1.3
 */
abstract class VRCBackupImportRule
{
	/**
	 * The path to the folder of the extracted backup archive.
	 * 
	 * @var string
	 */
	protected $path;

	/**
	 * Class constructor.
	 * 
	 * @param   string  $path  The archive path.
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * Executes the backup import command.
	 * 
	 * @param 	mixed  $data  The import rule instructions.
	 * 
	 * @return 	void
	 */
	abstract public function execute($data);
}
