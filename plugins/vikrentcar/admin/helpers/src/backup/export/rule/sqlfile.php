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
 * SQL Backup export rule, apposite for huge queries.
 * 
 * @since 1.3
 */
class VRCBackupExportRuleSqlfile extends VRCBackupExportRuleSql
{
	/**
	 * The path of the file containing the export queries.
	 * 
	 * @var string
	 */
	private $path;

	/**
	 * Returns the rules instructions.
	 * 
	 * @return 	mixed
	 */
	public function getData()
	{
		// check whether the file has been saved
		if ($this->path)
		{
			// return an associative array specifying the file path
			// that contains all the export queries
			return [
				'path' => $this->path,
			];
		}

		// do not import empty files
		return null;
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
		// register query through parent
		parent::registerQuery($query);

		if (!$this->queries)
		{
			// nothing to export
			return;
		}
		
		if (!$this->path)
		{
			// build file path only once
			$this->path = 'database/' . $this->table . '.sql';
		}

		// create buffer to save
		$buffer = trim(implode("\n\n", $this->queries));
		$buffer = preg_replace("/\)\s*,\s*\(/", "),\n(", $buffer);
			
		// register SQL buffer into the archive
		$saved = $this->archive->addBuffer($buffer . "\n\n", $this->path);

		if (!$saved)
		{
			// an error occurred while writing the dump files
			throw new Exception(sprintf('Unable to write dump into: %s', $this->path), 500);
		}

		// reset queries list to avoid duplicates
		$this->queries = [];
	}
}
