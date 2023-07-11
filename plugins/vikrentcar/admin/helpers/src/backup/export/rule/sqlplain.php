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
 * Plain SQL Backup export rule.
 * 
 * @since 1.3
 */
class VRCBackupExportRuleSqlplain extends VRCBackupExportRule
{
	/**
	 * An array of SQL statements.
	 * 
	 * @var array
	 */
	protected $queries = [];

	/**
	 * Returns the rule identifier.
	 * 
	 * @return 	string
	 */
	public function getRule()
	{
		// treat as SQL role
		return 'sql';
	}

	/**
	 * Returns the rules instructions.
	 * 
	 * @return 	mixed
	 */
	public function getData()
	{
		return $this->queries;
	}

	/**
	 * Configures the rule to work according to the specified data.
	 * 
	 * @param 	mixed 	$data  Either a query string or an array.
	 * 
	 * @return 	void
	 */
	protected function setup($data)
	{
		$this->queries = (array) $data;
	}
}
