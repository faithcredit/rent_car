<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.database
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Helper class to run some useful functions for the database framework.
 *
 * @since 10.0
 */
abstract class JDatabaseHelper
{
	/**
	 * Splits the given SQL statement in single queries.
	 *
	 * @param 	string 	$sql 	A staring containing multiple queries.
	 *
	 * @return 	array 	A list of queries.
	 */
	public static function splitSql($sql)
	{
		$start = 0;
		$open = false;
		$comment = false;
		$endString = '';
		$end = strlen($sql);
		$queries = array();
		$query = '';

		for ($i = 0; $i < $end; $i++)
		{
			$current = substr($sql, $i, 1);
			$current2 = substr($sql, $i, 2);
			$current3 = substr($sql, $i, 3);
			$lenEndString = strlen($endString);
			$testEnd = substr($sql, $i, $lenEndString);

			if ($current == '"' || $current == "'" || $current2 == '--'
				|| ($current2 == '/*' && $current3 != '/*!' && $current3 != '/*+')
				|| ($current == '#' && $current3 != '#__')
				|| ($comment && $testEnd == $endString))
			{
				// check if quoted with previous backslash
				$n = 2;

				while (substr($sql, $i - $n + 1, 1) == '\\' && $n < $i)
				{
					$n++;
				}

				// not quoted
				if ($n % 2 == 0)
				{
					if ($open)
					{
						if ($testEnd == $endString)
						{
							if ($comment)
							{
								$comment = false;
								if ($lenEndString > 1)
								{
									$i += ($lenEndString - 1);
									$current = substr($sql, $i, 1);
								}
								$start = $i + 1;
							}
							$open = false;
							$endString = '';
						}
					}
					else
					{
						$open = true;
						if ($current2 == '--')
						{
							$endString = "\n";
							$comment = true;
						}
						elseif ($current2 == '/*')
						{
							$endString = '*/';
							$comment = true;
						}
						elseif ($current == '#')
						{
							$endString = "\n";
							$comment = true;
						}
						else
						{
							$endString = $current;
						}
						if ($comment && $start < $i)
						{
							$query = $query . substr($sql, $start, ($i - $start));
						}
					}
				}
			}

			if ($comment)
			{
				$start = $i + 1;
			}

			if (($current == ';' && !$open) || $i == $end - 1)
			{
				if ($start <= $i)
				{
					$query = $query . substr($sql, $start, ($i - $start + 1));
				}
				$query = trim($query);

				if ($query)
				{
					if (($i == $end - 1) && ($current != ';'))
					{
						$query = $query . ';';
					}
					$queries[] = $query;
				}

				$query = '';
				$start = $i + 1;
			}
		}

		return $queries;
	}
}
