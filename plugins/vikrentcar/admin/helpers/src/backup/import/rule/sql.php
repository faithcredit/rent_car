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
 * Backup SQL import rule.
 * 
 * @since 1.3
 */
class VRCBackupImportRuleSql extends VRCBackupImportRule
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
		$dbo = JFactory::getDbo();

		// iterate all specified queries
		foreach ((array) $data as $q)
		{
			try
			{
				$dbo->setQuery($q);
				$dbo->execute();
			}
			catch (Exception $e)
			{
				// catch and go ahead silently
			}
		}
	}
}
