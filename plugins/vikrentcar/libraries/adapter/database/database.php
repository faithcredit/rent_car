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
 * This adapter is required to wrap the Wordpress DB
 * functions using the Joomla DB interface.
 * This is helpful to improve the portability between Joomla and Wordpress.
 *
 * @since 10.0
 */
class JDatabase
{
	/**
	 * The singleton instance of the database.
	 *
	 * @var JDatabase
	 */
	protected static $instance = null;

	/**
	 * The global $wpdb instance.
	 *
	 * @var $wpdb
	 */
	protected $db;

	/**
	 * The query set for the execution.
	 *
	 * @var string
	 */
	protected $q;

	/**
	 * The last result fetched.
	 *
	 * @var mixed
	 */
	protected $result;

	/**
	 * The query offset (start).
	 *
	 * @var   integer
	 * @since 10.1.15
	 */
	protected $offset;

	/**
	 * The query limit (max number of records).
	 *
	 * @var   integer
	 * @since 10.1.15
	 */
	protected $limit;

	/**
	 * The common database table prefix.
	 *
	 * @var    string
	 * @since  10.1.37
	 */
	protected $tablePrefix;

	/**
	 * Returns the global database adapter object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param 	$wpdb 	$db  The wordpress database handler.
	 *
	 * @return 	self 	A new instance of this class.
	 */
	public static function getInstance($db)
	{
		if (static::$instance === null)
		{
			static::$instance = new static($db);
		}

		return static::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @param 	$wpdb 	$db  The wordpress db handler.
	 */
	protected function __construct($db)
	{
		$this->db = $db;

		/**
		 * Hook used to suppress/enable database errors.
		 *
		 * @param 	boolean  True to suppress the errors, false otherwise (false by default).
		 *
		 * @since 	10.1.13
		 */
		$this->db->suppress_errors(apply_filters('vik_db_suppress_errors', false));

		/**
		 * Hook used to show/hide database errors.
		 * In case errors are suppressed, this hook would result useless.
		 *
		 * @param 	boolean  True to show the errors, false otherwise (true by default).
		 *
		 * @since 	10.1.13
		 */
		$this->db->show_errors(apply_filters('vik_db_show_errors', true));
	}

	/**
	 * Magic method to proxy the functions in the $wpdb wrapped instance.
	 *
	 * @param   string  $method  The called method.
	 * @param   array   $args    The array of arguments passed to the method.
	 *
	 * @return  mixed   The value returned by the dispatched method.
	 * 					Null if the method doesn't exist.
	 */
	public function __call($name, $args)
	{
		if (method_exists($this->db, $name))
		{
			return call_user_func_array(array($this->db, $name), $args);
		}

		throw new RuntimeException('Call to undefined method ' . __CLASS__ . '::' . $name . '()', 500);
	}

	/**
	 * This function replaces a string placeholder with the real database prefix.
	 *
	 * @param 	string  $sql     The SQL statement to prepare.
	 * @param 	string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		// generate a random placeholder
		$placeholder = md5($prefix . uniqid());	

		// Replace all prefixes between the single/double quotes
		// with the placeholder generated previously.
		// This avoids to affect also strings that contains the actual prefix.
		$sql = preg_replace_callback(
			// "/('.*($prefix).*')|(\".*($prefix).*\")/", 
			// get all the strings contained between single and double quotes,
			// even if they don't contain the prefix
			"/('.*?')|(\".*?\")/",
			function($match) use ($prefix, $placeholder)
			{
				// if contained, replace the prefix with the placeholder
				return str_replace($prefix, $placeholder, $match[0]);
			},
			$sql
		);

		// get the prefix to use
		$wp_prefix = $this->getPrefix();

		// replace remaining prefixes (e.g. within backticks) with the real db prefix
		$sql = str_replace($prefix, $wp_prefix, $sql);

		// replace random placeholders with the original escaped prefix
		$sql = str_replace($placeholder, $prefix, $sql);

		return $sql;
	}

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   mixed    $q   		The SQL statement to set as string.
	 * @param   integer  $offset  	The affected row offset to set.
	 * @param   integer  $limit   	The maximum affected rows to set.
	 *
	 * @return  self  	 This object to support chaining.
	 */
	public function setQuery($q, $offset = 0, $limit = 0)
	{
		$this->result = null;

		/**
		 * If we are accessing #__users table, we need to route all
		 * the specified columns that belong to Joomla framework.
		 *
		 * @since 10.1.16
		 */
		$q = static::adjustJoomlaQuery2WP($q);

		// save the query with the real db prefix
		$this->q = $this->replacePrefix($q);

		// register offset and limit (always override previous values)
		$this->offset = abs($offset);
		$this->limit  = abs($limit);

		return $this;
	}

	/**
	 * Gets the current query object or a new JDatabaseQuery object.
	 *
	 * @param   boolean  $new   False to return the current query object, True to return a new JDatabaseQuery object.
	 *
	 * @return  mixed  	 The JDatabaseQuery object or a SQL plain string.
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			JLoader::import('adapter.database.query');

			return new JDatabaseQuery($this);
		}
		
		return $this->q;
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 */
	public function execute()
	{
		$sql = trim((string) $this->q);

		// try to limit the query
		if ($this->limit > 0 && $this->offset > 0)
		{
			$sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}
		elseif ($this->limit > 0)
		{
			$sql .= ' LIMIT ' . $this->limit;
		}

		// if we are executing a SELECT query we need to 
		// load directly all the results fetched
		if (preg_match("/^(SELECT|SHOW)/i", $sql))
		{
			// result should contain an array
			$this->result = $this->db->get_results($sql);
		}
		// otherwise we can launch a generic query
		else
		{
			// result should contain an integer
			$this->result = $this->db->query($sql);
		}

		return (bool) $this->result;
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 * This command is only valid for statements like SELECT or SHOW that return an actual result set.
	 *
	 * @return  integer  The number of returned rows.
	 */
	public function getNumRows()
	{
		if (is_array($this->result))
		{
			return count($this->result);
		}

		return 0;
	}

	/**
	 * Get the number of affected rows by the last INSERT, UPDATE, REPLACE or DELETE 
	 * for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 */
	public function getAffectedRows()
	{
		if (is_numeric($this->result))
		{
			return $this->result;
		}

		return 0;
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  mixed  	The value of the auto-increment field from the last inserted row.
	 */
	public function insertid()
	{
		return $this->db->insert_id;
	}

	/**
	 * Method to get an array of the result set rows from the database query 
	 * where each row is an object.
	 *
	 * @return 	array 	The object list.
	 *
	 * @uses 	execute()
	 */
	public function loadObjectList()
	{
		if (is_null($this->result))
		{
			$this->execute();
		}

		if (is_array($this->result))
		{
			return $this->result;
		}

		return array();
	}

	/**
	 * Method to get an array of the result set rows from the database query 
	 * where each row is an associative array of ['field_name' => 'row_value'].  
	 *
	 * @return  array 	The associative arrays list.
	 *
	 * @uses 	loadObjectList()
	 */
	public function loadAssocList()
	{
		$app = array();

		foreach ($this->loadObjectList() as $obj)
		{
			$app[] = (array) $obj;
		}

		return $app;
	}

	/**
	 * Method to get the first row of the result set from the database query as an object.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @uses 	loadObjectList()
	 */
	public function loadObject()
	{
		$list = $this->loadObjectList();

		if (count($list))
		{
			return $list[0];
		}

		return null;
	}

	/**
	 * Method to get the first row of the result set from the database query 
	 * as an associative array of ['field_name' => 'row_value'].
	 *
	 * @return  mixed 	The return value or null if the query failed.
	 *
	 * @uses 	loadObject()
	 */
	public function loadAssoc()
	{
		$obj = $this->loadObject();

		if ($obj !== null)
		{
			return (array) $obj;
		}

		return null;
	}

	/**
	 * Method to get the first field of the first row of the result set from the database query.
	 *
	 * @return  mixed 	The return value or null if the query failed.
	 *
	 * @uses 	loadAssoc()
	 */
	public function loadResult()
	{
		$arr = $this->loadAssoc();

		if (is_array($arr))
		{
			$keys = array_keys($arr);

			return $arr[$keys[0]];
		}

		return null;
	}

	/**
	 * Method to get the first row of the result set from the database query as an array.
	 *
	 * Columns are indexed numerically so the first column in the result set would be accessible via <var>$row[0]</var>, etc.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   10.1.37
	 */
	public function loadRow()
	{
		$arr = $this->loadAssoc();

		if (is_array($arr))
		{
			return array_values($arr);
		}

		return null;
	}

	/**
	 * Method to get an array of values from the <var>$offset</var> field in each row 
	 * of the result set from the database query.
	 *
	 * @param   integer  $offset  The row offset to use to build the result array.
	 *
	 * @return  array    A list containing the columns.
	 *
	 * @uses 	loadAssocList()
	 */
	public function loadColumn($offset = 0)
	{
		$column = array();

		foreach ($this->loadAssocList() as $arr)
		{
			$keys = array_keys($arr);

			$column[] = $arr[$keys[$offset]];
		}

		return $column;
	}

	/**
	 * Quotes and optionally escapes a string to database requirements for use in database queries.
	 *
	 * @param   mixed    $text    A string or an array of strings to quote.
	 * @param   boolean  $escape  True (default) to escape the string, false to leave it unchanged.
	 *
	 * @return  mixed  	 The quoted input.
	 */
	public function quote($text, $escape = true)
	{
		if (is_array($text))
		{
			return esc_sql($text);
		}

		return '\'' . ($escape ? esc_sql((string) $text) : $text) . '\'';
	}

	/**
	 * Shorten alias for quote() method.
	 *
	 * @see 	quote()
	 */
	public function q($text, $escape = true)
	{
		return $this->quote($text, $escape);
	}

	/**
	 * Wraps an SQL statement identifier name such as column, table or database names 
	 * in quotes to prevent injection risks and reserved word conflicts.
	 *
	 * @param   mixed  	$name  	The identifier name to wrap in quotes, or an array of identifier
	 * 							names to wrap in quotes. Each type supports dot-notation name.
	 * @param   mixed  	$as    	The AS query part associated to $name. It can be string or array.
	 *
	 * @return  string  The quote wrapped name.
	 *
	 * @uses 	_quoteName()
	 */
	public function quoteName($name, $as = null)
	{
		// define an empty array 
		$arr = array();

		// fill $arr recursively with quoted names
		$this->_quoteName($arr, $name, $as);

		// concat the list using a comma separator
		return implode(', ', $arr);
	}

	/**
	 * Shorten alias for quoteName() method.
	 *
	 * @see 	quoteName()
	 */
	public function qn($str, $as = null)
	{
		return $this->quoteName($str, $as);
	}

	/**
	 * Recursive method to quote a list of names.
	 *
	 * @param 	array 	&$arr 	A list containing all the quotes names.
	 * @param   mixed  	$name  	The identifier name to wrap in quotes, or an array of identifier
	 * 							names to wrap in quotes. Each type supports dot-notation name.
	 * @param   mixed  	$as    	The AS query part associated to $name. It can be string or array.
	 *
	 * @return 	void
	 */
	protected function _quoteName(array &$arr, $name, $as = null)
	{
		// if the name is (still) an array, quote it recursively
		// until we have a scalar value
		if (is_array($name))
		{
			// iterate the names contained in the list
			foreach ($name as $i => $inner)
			{
				// obtain the AS only if it exists
				$_as = !is_null($as) && is_array($as) && isset($as[$i]) ? $as[$i] : null;

				$this->_quoteName($arr, $inner, $_as);
			}
		}
		// quote the scalar value
		else
		{
			// explode the name for dot-notation
			$exp = explode('.', $name);

			$name = "`{$exp[0]}`";
			if (count($exp) > 1)
			{
				$name .= ".`{$exp[1]}`";
			}

			if (!is_null($as))
			{
				$name .= " AS `$as`";
			}

			$arr[] = $name;
		}
	}

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string   $table    The name of the database table to insert into.
	 * @param   object   &$object  A reference to an object whose public properties match the table fields.
	 * @param   string   $key      The name of the primary key. If provided the object property is updated.
	 *
	 * @return  boolean  True on success.
	 */
	public function insertObject($table, &$object, $key = null)
	{
		$data = array();

		foreach (get_object_vars($object) as $k => $v)
		{
			// exclude primary key, not null values, arrays, objects and
			// internal properties (prefixed with an underscore)
			if ($k != $key && $v !== null && is_scalar($v) && $k[0] != '_')
			{
				$data[$k] = $v;
			}
		}

		// insert the new record
		if (!$this->db->insert($this->replacePrefix($table), $data))
		{
			return false;
		}

		// update the primary key if it exists
		$id = $this->db->insert_id;

		// store affected row
		$this->result = $id;

		if ($key && $id && is_string($key))
		{
			$object->{$key} = $id;
		}

		return true;
	}

	/**
	 * Updates a row in a table based on an object's properties.
	 *
	 * @param   string   $table    The name of the database table to update.
	 * @param   object   &$object  A reference to an object whose public properties match the table fields.
	 * @param   mixed    $key      The name (or a list of names) of the primary key.
	 * @param   boolean  $nulls    True to update null fields or false to ignore them.
	 *
	 * @return  boolean  True on success.
	 */
	public function updateObject($table, &$object, $key, $nulls = false)
	{
		$set 	= array();
		$where 	= array();

		if (is_string($key))
		{
			$key = array($key);
		}

		if (is_object($key))
		{
			$key = (array) $key;
		}

		foreach (get_object_vars($object) as $k => $v)
		{
			// exclude arrays, objects and internal properties (prefixed with an underscore)
			if (is_array($v) || is_object($v) || $k[0] == '_')
			{
				continue;
			}

			// set the primary key to the WHERE clause instead of a field to update
			if (in_array($k, $key))
			{
				$where[$k] = $v;
				continue;
			}

			// update field only if not null or if nulls values are allowed
			if ($v !== null || $nulls)
			{
				$set[$k] = $v;
			}
		}

		// we don't have any fields to update
		if (empty($set))
		{
			return true;
		}

		// update the specified record
		$affected = $this->db->update($this->replacePrefix($table), $set, $where);

		// store affected rows
		$this->result = (int) $affected;

		return $affected !== false;
	}

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * @return  string  The format string.
	 */
	public function getDateFormat()
	{
		return 'Y-m-d H:i:s';
	}

	/**
	 * Returns the null date in the format of the database driver.
	 *
	 * @return  string  The null date string.
	 * 
	 * @since 	10.1.5
	 */
	public function getNullDate()
	{
		return '0000-00-00 00:00:00';
	}

	/**
	 * Get the common table prefix for the database driver.
	 *
	 * @return  string  The common database table prefix.
	 *
	 * @since   10.1.37
	 */
	public function getPrefix()
	{
		if (is_null($this->tablePrefix))
		{
			/**
			 * Hook used to filter the default WP database prefix before it is used.
			 *
			 * @param 	string 	The database prefix to use for queries.
			 *
			 * @since 	10.1.1
			 */
			$this->tablePrefix = apply_filters('vik_get_db_prefix', $this->db->prefix);
		}

		return $this->tablePrefix;
	}

	/**
	 * Retrieves field information about a given table.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True to only return field types.
	 *
	 * @return  array 	 An array of fields for the database table.
	 *
	 * @since   10.1.19
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$q = "SHOW FULL COLUMNS FROM " . $this->qn($table);

		// set the query to get the table fields statement
		$this->setQuery($q);
		$this->execute();

		$fields = $this->loadObjectList();

		// if we only want the type as the value add just that to the list.
		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = preg_replace('/[(0-9)]/', '', $field->Type);
			}
		}
		// if we want the whole field data object add that to the list.
		else
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = $field;
			}
		}

		return $result;
	}

	/**
	 * Method to get an array containing all the database tables.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   10.1.37
	 */
	public function getTableList()
	{
		// set the query to get the tables statement
		$this->setQuery('SHOW TABLES');
		$this->execute();

		return $this->loadColumn();
	}

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @since   10.1.37
	 */
	public function getTableCreate($tables)
	{
		$result = [];

		// sanitize input to an array and iterate over the list
		$tables = (array) $tables;

		foreach ($tables as $table)
		{
			// set the query to get the table CREATE statement
			$this->setQuery('SHOW CREATE TABLE ' . $this->qn($table));
			$this->execute();

			$row = $this->loadRow();

			// populate the result array based on the create statements
			$result[$table] = $row[1];
		}

		return $result;
	}

	/**
	 * Adjusts a query built for Joomla to WordPress needs.
	 *
	 * @param 	mixed 	$query  The SQL query string or a query builder.
	 *
	 * @return 	void
	 *
	 * @since 	10.1.16
	 */
	public static function adjustJoomlaQuery2WP($query)
	{
		// always cast to string
		$query = (string) $query;

		// check if the query contains `#__users` and an optional alias
		if (preg_match("/`#__users`(?:\s+AS\s+`([a-z0-9_]+)`)?/i", $query, $match))
		{
			$userTable = !empty($match[1]) ? $match[1] : null;
			
			// check whether an alias should be used
			$tableAlias = $userTable ? "`{$userTable}`\." : "";
			$lookup     = array();

			// replace all the columns that match the regex
			$query = preg_replace_callback("/{$tableAlias}`([a-z0-9_]+)`(?:\s*AS\s*`([a-z0-9_]+)`)?/i", function($match) use ($userTable, $tableAlias, $query, &$lookup)
			{
				// get current column and alias
				$col   = $match[1];
				$alias = isset($match[2]) ? $match[2] : $match[1];

				switch (strtolower($col))
				{
					case 'name':
						$col = 'display_name';
						break;

					case 'username':
						$col = 'user_login';
						break;

					case 'email':
						$col = 'user_email';
						break;
				}

				// rebuild column without using ALIAS
				$str = ($userTable ? "`{$userTable}`." : "") . "`{$col}`";

				$sign = ($tableAlias ? $tableAlias . '.' : '') . $col;

				// check if lookup doesn't contain this column and the query is a select
				if (!isset($lookup[$sign]) && preg_match("/^\s*SELECT/i", $query))
				{
					// obtain position of current column and position of FROM statement and
					// make sure the chunk position is displayed before FROM
					if (preg_match("/{$match[0]}/i", $query, $token, PREG_OFFSET_CAPTURE)
						&& preg_match("/\sFROM\s/i", $query, $from, PREG_OFFSET_CAPTURE)
						&& $token[0][1] < $from[0][1])
					{
						// add alias for column within SELECT
						$str .= " AS `{$alias}`";
					}
				}

				// mark column as registered within the lookup in order
				// to avoid adding alias again outside the SELECT
				$lookup[$sign] = 1;

				return $str;
			}, $query);

			/**
			 * In case of multi-site, always use the base prefix when
			 * querying the users database table.
			 *
			 * @since 10.1.31
			 */
			if (is_multisite())
			{
				global $wpdb;
				$query = preg_replace("/`#__users`/", "`{$wpdb->base_prefix}users`", $query);
			}
		}

		return $query;
	}
}
