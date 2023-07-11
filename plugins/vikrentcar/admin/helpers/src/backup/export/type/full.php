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
 * FULL Backup export type.
 * 
 * @since 1.3
 */
class VRCBackupExportTypeFull implements VRCBackupExportType
{
	/**
	 * Returns a readable name of the export type.
	 * 
	 * @return 	string
	 */
	public function getName()
	{
		return JText::_('VRC_BACKUP_EXPORT_TYPE_FULL');
	}

	/**
	 * Returns a readable description of the export type.
	 * 
	 * @return 	string
	 */
	public function getDescription()
	{
		return JText::_('VRC_BACKUP_EXPORT_TYPE_FULL_DESCRIPTION');
	}

	/**
	 * Configures the backup director.
	 * 
	 * @param 	VRCBackupExportDirector  $director
	 * 
	 * @return 	void
	 */
	public function build(VRCBackupExportDirector $director)
	{
		// fetch database tables to export
		$tables = $this->getDatabaseTables();

		// iterate all database tables
		foreach ($tables as $table)
		{
			// create SQL export rule
			$director->createRule('sqlfile', $table);
		}

		// register the UPDATE queries for the configuration table
		$director->createRule('sqlplain', $this->getConfigSQL());

		// fetch folders to export
		$folders = $this->getFolders();

		// iterate all folders to copy
		foreach ($folders as $folder)
		{
			// create FOLDER export rule
			$director->createRule('folder', $folder);
		}
	}

	/**
	 * Returns an array of database tables to export.
	 * 
	 * @return 	array
	 */
	protected function getDatabaseTables()
	{
		$dbo = JFactory::getDbo();

		// load all the installed database tables
		$tables = $dbo->getTableList();

		// get current database prefix
		$prefix = $dbo->getPrefix();

		// replace prefix with placeholder
		$tables = array_map(function($table) use ($prefix)
		{
			return preg_replace("/^{$prefix}/", '#__', $table);
		}, $tables);

		// remove all the tables that do not belong to VikRentCar
		$tables = array_values(array_filter($tables, function($table)
		{
			if (preg_match("/^#__vikrentcar_config$/", $table))
			{
				// exclude the configuration table, which will be handled in a different way
				return false;
			}

			return preg_match("/^#__vikrentcar_/", $table);
		}));

		return $tables;
	}

	/**
	 * Returns an associative array of folders to export, where the key is equals
	 * to the path to copy and the value is the relative destination path.
	 * 
	 * @return 	array
	 */
	protected function getFolders()
	{
		$folders = [
			'media' => [
				'source'      => JPath::clean(VRC_ADMIN_PATH . '/resources'),
				'destination' => 'media',
				'target'      => ['VRC_ADMIN_PATH', 'resources'],
			],
			'invoices' => [
				'source'      => JPath::clean(VRC_SITE_PATH . '/helpers/invoices/generated'),
				'destination' => 'invoices',
				'target'      => ['VRC_SITE_PATH', 'helpers/invoices/generated'],
			],
			'idscans' => [
				'source'      => JPath::clean(VRC_ADMIN_PATH . '/resources/idscans'),
				'destination' => 'idscans',
				'target'      => ['VRC_ADMIN_PATH', 'resources/idscans'],
			],
			'mailtmpl' => [
				'source'      => JPath::clean(VRC_SITE_PATH . '/helpers/email_tmpl.php'),
				'destination' => 'tmpl',
				'target'      => ['VRC_SITE_PATH', 'helpers'],
			],
			'errorform' => [
				'source'      => JPath::clean(VRC_SITE_PATH . '/helpers/error_form.php'),
				'destination' => 'tmpl',
				'target'      => ['VRC_SITE_PATH', 'helpers'],
			],
			'invoicetmpl' => [
				'source'      => JPath::clean(VRC_SITE_PATH . '/helpers/invoices/invoice_tmpl.php'),
				'destination' => 'tmpl',
				'target'      => ['VRC_SITE_PATH', 'helpers/invoices'],
			],
			'pdftmpl' => [
				'source'      => JPath::clean(VRC_SITE_PATH . '/helpers/pdf_tmpl.php'),
				'destination' => 'tmpl',
				'target'      => ['VRC_SITE_PATH', 'helpers'],
			],
			'checkintmpl' => [
				'source'      => JPath::clean(VRC_SITE_PATH . '/helpers/checkin_pdf_tmpl.php'),
				'destination' => 'tmpl',
				'target'      => ['VRC_SITE_PATH', 'helpers'],
			],
			'customerdocs' => [
				'source'      => VRC_CUSTOMERS_PATH,
				'destination' => 'customerdocs',
				'target'      => 'VRC_CUSTOMERS_PATH',
				'recursive'   => true,
			],
			'visualeditor' => [
				'source'      => VRC_MEDIA_PATH,
				'destination' => 'visualeditor',
				'target'      => 'VRC_MEDIA_PATH',
			],
		];

		if ($sitelogo = VRCFactory::getConfig()->get('sitelogo'))
		{
			$folders['sitelogo'] = [
				'source'      => JPath::clean(VRC_ADMIN_PATH . '/resources/' . $sitelogo),
				'destination' => 'logos',
				'target'      => ['VRC_ADMIN_PATH', 'resources'],
			];
		}

		if ($backlogo = VRCFactory::getConfig()->get('backlogo'))
		{
			$folders['backlogo'] = [
				'source'      => JPath::clean(VRC_ADMIN_PATH . '/resources/' . $backlogo),
				'destination' => 'logos',
				'target'      => ['VRC_ADMIN_PATH', 'resources'],
			];
		}

		return $folders;
	}

	/**
	 * Returns an array of queries used to keep the configuration up-to-date.
	 * 
	 * @return 	array
	 */
	protected function getConfigSQL()
	{
		$dbo = JFactory::getDbo();

		$sql = [];

		// define list of parameters to ignore
		$lookup = [
			'vikrentcar' => [
				'update_extra_fields',
				'backupfolder',
			],
		];

		foreach ($lookup as $table => $exclude)
		{
			// prepare update statement
			$update = $dbo->getQuery(true)->update($dbo->qn('#__' . $table . '_config'));

			// fetch all configuration settings
			$q = $dbo->getQuery(true)
				->select($dbo->qn(['param', 'setting']))
				->from($dbo->qn('#__' . $table . '_config'));

			if ($exclude)
			{
				$q->where($dbo->qn('param') . ' NOT IN (' . implode(',', array_map([$dbo, 'q'], $exclude)) . ')');
			}

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// iterate all settings
				foreach ($dbo->loadObjectList() as $row)
				{
					// clear update
					$update->clear('set')->clear('where');
					// define value to set
					$update->set($dbo->qn('setting') . ' = ' . $dbo->q($row->setting));
					// define parameter to update
					$update->where($dbo->qn('param') . ' = ' . $dbo->q($row->param));

					$sql[] = (string) $update;
				}
			}
		}

		return $sql;
	}
}
