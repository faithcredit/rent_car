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
 * SQL Backup export rule.
 * 
 * @since 1.3
 */
class VRCBackupExportRuleSql extends VRCBackupExportRule
{
	/**
	 * An array of SQL statements.
	 * 
	 * @var array
	 */
	protected $queries = [];

	/**
	 * The database table.
	 * 
	 * @var string
	 */
	protected $table;

	/**
	 * The information of the database columns.
	 * 
	 * @var object
	 */
	private $columns;

	/**
	 * Indicates the maximum number of rows under the same INSERT.
	 * 
	 * @var integer
	 */
	private $maxRowsPerInsert = 100;

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
	 * @param 	string 	$data  The database table name.
	 * 
	 * @return 	void
	 */
	protected function setup($data)
	{
		$dbo = JFactory::getDbo();

		// register table
		$this->table = $data;

		// get all the columns of the table to export
		$this->columns = $dbo->getTableColumns($this->table, $typeOnly = false);

		// get total count of records
		$count = $this->getCount();

		if (!$count)
		{
			// nothing to export
			return;
		}

		// get current database prefix
		$prefix = $dbo->getPrefix();

		// shows the table CREATE statement that creates the given table
		$createLookup = $dbo->getTableCreate($this->table);

		// check whether the current drivers supports a tool to return the statement that
		// was used to create the database table
		if (isset($createLookup[$this->table]))
		{
			// extract statement from create lookup and replace prefix
			$create = preg_replace("/`{$prefix}(vikrentcar_(?:[a-z0-9_]+))`/i", '`#__$1`', $createLookup[$this->table]);

			// register query to recreate the table from scratch
			$this->registerQuery("DROP TABLE IF EXISTS `{$this->table}`");
			$this->registerQuery($create);

			// we don't need to alter the auto increment because it is already included
			// within the create table statement
			$alter_auto_increment = false;
		}
		else
		{
			// cannot fetch create table statment, truncate the table and assume (or "hope")
			// that the database structure is the same
			$this->registerQuery("TRUNCATE TABLE `$this->table`");

			// update auto increment after copying all the records
			$alter_auto_increment = true;
		}

		$insertQuery = $dbo->getQuery(true);

		// prepare INSERT query
		$insertQuery->insert($dbo->qn($this->table));

		$app = JFactory::getApplication();

		/**
		 * Trigger event to allow third party plugins to choose what are the columns to dump
		 * and whether the table should be skipped or not.
		 * 
		 * Fires while attaching a rule to dump some SQL statements.
		 * 
		 * @param 	array    &$columns  An associative array of supported database table columns,
		 *                              where the key is the column name and the value is a nested
		 *                              array holding the column information.
		 * @param 	string   $table     The name of the database table.
		 * 
		 * @return 	boolean  False to avoid including the table into the backup.
		 * 
		 * @since 	1.3
		 */
		$results = $app->triggerEvent('onBeforeBackupDumpSqlVikRentCar', [&$this->columns, $this->table]);

		if (in_array(false, $results, true))
		{
			// a third-party plugin decided to skip the table
			return;
		}

		// iterate the columns
		foreach ($this->columns as $column => $type)
		{
			$insertQuery->columns($dbo->qn($column));
		}

		// create SELECT query
		$selectQuery = $dbo->getQuery(true)->select('*')->from($dbo->qn($this->table));

		$offset = 0;

		while ($offset < $count)
		{
			$dbo->setQuery($selectQuery, $offset, $this->maxRowsPerInsert);
			$dbo->execute();

			$rows = $dbo->loadObjectList();

			// clear previous values
			$insertQuery->clear('values');

			foreach ($rows as $row)
			{
				$values = array();

				foreach ($this->columns as $k => $type)
				{
					if (!isset($row->{$k}))
					{
						// use NULL operator
						$values[] = 'NULL';
					}
					else
					{
						// escape the value
						$values[] = $dbo->q($row->{$k});
					}
				}

				$insertQuery->values(implode(',', $values));
			}

			// register query
			$this->registerQuery((string) $insertQuery);

			// increase offset
			$offset += $this->maxRowsPerInsert;

			// free space
			unset($rows);
		}

		// fetch table auto increment
		if ($alter_auto_increment && ($ai = $this->getAutoIncrement($this->table)))
		{
			$this->registerQuery("ALTER TABLE `{$this->table}` AUTO_INCREMENT = {$ai}");
		}
	}

	/**
	 * Helper method used to register the query inside the buffer.
	 * 
	 * @param 	string 	$query  The query to register.
	 * 
	 * @return 	void
	 */
	protected function registerQuery($query)
	{
		if (!preg_match("/;$/", $query))
		{
			$query .= ';';
		}

		$this->queries[] = $query;
	}

	/**
	 * Counts the total number of rows inside the table.
	 * 
	 * @return 	integer
	 */
	private function getCount()
	{
		$dbo = JFactory::getDbo();

		// count rows
		$q = $dbo->getQuery(true)->select('COUNT(1)')->from($dbo->qn($this->table));

		$dbo->setQuery($q);
		$dbo->execute();

		return (int) $dbo->loadResult();
	}

	/**
	 * Fetches the correct auto increment to set for the given table.
	 * 
	 * @return 	mixed   The auto increment on success, null otherwise.
	 */
	private function getAutoIncrement()
	{
		$dbo = JFactory::getDbo();

		$pk = null;

		// look for the primary key
		foreach ($this->columns as $column => $info)
		{
			if ($info->Extra === 'auto_increment')
			{
				$pk = $column;
			}
		}

		if (!$pk)
		{
			// no primary keys with auto increment
			return null;
		}

		// fetch highest ID
		$q = $dbo->getQuery(true)->select('MAX(' . $dbo->qn($pk) . ')')->from($dbo->qn($this->table));

		$dbo->setQuery($q);
		$dbo->execute();

		return (int) $dbo->loadResult() + 1;
	}
}
