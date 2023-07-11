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
 * MANAGEMENT backup export type.
 * 
 * @since 1.3
 */
class VRCBackupExportTypeManagement extends VRCBackupExportTypeFull
{
	/**
	 * Returns a readable name of the export type.
	 * 
	 * @return 	string
	 */
	public function getName()
	{
		return JText::_('VRC_BACKUP_EXPORT_TYPE_MANAGEMENT');
	}

	/**
	 * Returns a readable description of the export type.
	 * 
	 * @return 	string
	 */
	public function getDescription()
	{
		return JText::_('VRC_BACKUP_EXPORT_TYPE_MANAGEMENT_DESCRIPTION');
	}

	/**
	 * Returns an array of database tables to export.
	 * 
	 * @return 	array
	 */
	protected function getDatabaseTables()
	{
		// get database tables from parent
		$tables = parent::getDatabaseTables();

		// define list of database tables to exclude
		$exclude = [
			'#__vikrentcar_busy',
			'#__vikrentcar_customers',
			'#__vikrentcar_customers_orders',
			'#__vikrentcar_orders',
			'#__vikrentcar_orderhistory',
		];

		// remove the specified tables from the list
		$tables = array_values(array_diff($tables, $exclude));

		return $tables;
	}

	/**
	 * Returns an array of files to export.
	 * 
	 * @return 	array
	 */
	protected function getFolders()
	{
		// get folders from parent
		$folders = parent::getFolders();

		// unset some folders
		unset($folders['invoices']);
		unset($folders['idscans']);
		unset($folders['customerdocs']);
		unset($folders['visualeditor']);

		return $folders;
	}
}
