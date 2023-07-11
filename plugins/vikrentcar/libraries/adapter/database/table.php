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
 * Generic table class for database objects.
 *
 * @since 10.1.19
 */
class JTable extends JObject
{
	/**
	 * Include paths for searching for Table classes.
	 *
	 * @var array
	 */
	private static $_includePaths = array();

	/**
	 * Table fields cache to prevent the static changed applied by PHP 8.1.
	 * @link https://bugs.php.net/bug.php?id=81686
	 *
	 * @var array
	 * @since 10.1.41
	 */
	protected static $_tableFields = [];

	/**
	 * Name of the database table to model.
	 *
	 * @var string
	 */
	protected $_table = '';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var array
	 */
	protected $_tableKeys = array();

	/**
	 * Indicates that the primary keys autoincrement.
	 *
	 * @var boolean
	 */
	protected $_autoincrement = true;

	/**
	 * Array with alias for "special" columns such as ordering, hits etc etc
	 *
	 * @var    array
	 * @since  10.1.30
	 */
	protected $_columnAlias = array();

	/**
	 * Static method to get an instance of a Table class if it
	 * can be found in the table include paths.
	 *
	 * @param   string  $type    The type (name) of the Table class to get an instance of.
	 * @param   string  $prefix  An optional prefix for the table class name.
	 * @param   array   $config  An optional array of configuration values for the Table object.
	 *
	 * @return  mixed   A Table object if found, false otherwise.
	 *
	 * @see 	JTable::addIncludePath() to add include paths for searching for Table classes.
	 */
	public static function getInstance($type, $prefix = 'JTable', $config = array())
	{
		// sanitize and prepare the table class name
		$type       = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix . ucfirst($type);

		// only try to load the class if it doesn't already exist
		if (!class_exists($tableClass))
		{
			// search for the class file in the JTable include paths.
			$paths = static::addIncludePath();
			$pathIndex = 0;

			// iterate until the class is loaded
			while (!class_exists($tableClass) && $pathIndex < count($paths))
			{
				if ($tryThis = JPath::find($paths[$pathIndex++], strtolower($type) . '.php'))
				{
					// import the class file
					include_once $tryThis;
				}
			}

			if (!class_exists($tableClass))
			{
				throw new Exception(sprintf('Table [%s] not found', $tableClass), 404);
			}
		}

		// instantiate a new table class and return it
		return new $tableClass($type);
	}

	/**
	 * Adds a filesystem path where Table should search for table class files.
	 *
	 * @param   mixed  $path  A filesystem path or array of filesystem paths to add.
	 *
	 * @return  array  An array of filesystem paths to find Table classes in.
	 */
	public static function addIncludePath($path = null)
	{
		// if the internal paths have not been initialised, do so with the base table path
		if (empty(self::$_includePaths))
		{
			self::$_includePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tables';
		}

		if ($path)
		{
			// convert the passed path(s) to add to an array.
			$path = (array) $path;

			// add each individual new path
			foreach ($path as $dir)
			{
				// sanitize path
				$dir = trim($dir);

				// add to the front of the list so that custom paths are searched first
				if (!in_array($dir, self::$_includePaths))
				{
					array_unshift(self::$_includePaths, $dir);
				}
			}
		}

		return self::$_includePaths;
	}

	/**
	 * Object constructor to set table and key fields.
	 * In most cases this will be overridden by child classes to explicitly
	 * set the table and key fields for a particular database table.
	 *
	 * @param   string 	$table  Name of the table to model.
	 * @param   mixed 	$key    Name of the primary key field in the table 
	 * 							or array of field names that compose the primary key.
	 */
	public function __construct($table, $key = 'id')
	{
		// set internal variables
		$this->_table = $table;

		// set the key to be an array.
		if (is_string($key))
		{
			$key = array($key);
		}
		else if (is_object($key))
		{
			$key = (array) $key;
		}

		$this->_tableKeys = $key;

		if (count($key) == 1)
		{
			$this->_autoincrement = true;
		}
		else
		{
			$this->_autoincrement = false;
		}

		// initialise the table properties
		$fields = $this->getFields();

		if ($fields)
		{
			foreach ($fields as $name => $v)
			{
				if (is_int($name))
				{
					// use the value in case we have a linear array
					$name = $v;
				}

				// add the field if it is not already present
				if (!property_exists($this, $name))
				{
					$this->{$name} = null;
				}
			}
		}
	}

	/**
	 * Gets the columns from database table.
	 *
	 * @param   boolean  $reload  Flag to reload cache.
	 *
	 * @return  mixed  	 An array of the field names, or false if an error occurs.
	 *
	 * @throws  Exception
	 */
	public function getFields($reload = false)
	{
		$key = $this->getTableName();

		if (!isset(static::$_tableFields[$key]) || $reload)
		{
			$dbo = JFactory::getDbo();

			// lookup the fields for this table only once
			$fields = $dbo->getTableColumns($this->_table, false);

			if (empty($fields))
			{
				throw new Exception(sprintf('No columns found for [%s] table', $this->_table));
			}

			static::$_tableFields[$key] = $fields;
		}

		return static::$_tableFields[$key];
	}

	/**
	 * Method to get the database table name for the class.
	 *
	 * @return  string  The name of the database table being modeled.
	 *
	 * @since   10.1.30
	 */
	public function getTableName()
	{
		return $this->_table;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @param   boolean  $multiple  True to return all primary keys (as an array) or false to return
	 *                              just the first one (as a string).
	 *
	 * @return  mixed    Array of primary key field names or string containing the first primary key field.
	 *
	 * @since   10.1.35
	 */
	public function getKeyName($multiple = false)
	{
		// Count the number of keys
		if (count($this->_tableKeys))
		{
			if ($multiple)
			{
				// If we want multiple keys, return the raw array.
				return $this->_tableKeys;
			}
			else
			{
				// If we want the standard method, just return the first key.
				return $this->_tableKeys[0];
			}
		}

		return '';
	}

	/**
	 * Validate that the primary key has been set.
	 *
	 * @return  boolean  True if the primary key(s) have been set.
	 *
	 * @since   10.1.30
	 */
	public function hasPrimaryKey()
	{
		if ($this->_autoincrement)
		{
			$empty = true;

			foreach ($this->_tableKeys as $key)
			{
				$empty = $empty && empty($this->$key);
			}
		}
		else
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select('COUNT(*)')
				->from($this->_table);

			$this->appendPrimaryKeys($q);

			$dbo->setQuery($q);

			$count = $dbo->loadResult();

			if ($count == 1)
			{
				$empty = false;
			}
			else
			{
				$empty = true;
			}
		}

		return !$empty;
	}

	/**
	 * Method to append the primary keys for this table to a query.
	 *
	 * @param   mixed  $query  A query object to append.
	 * @param   mixed  $pk     Optional primary key parameter.
	 *
	 * @return  void
	 *
	 * @since   10.1.30
	 */
	public function appendPrimaryKeys($query, $pk = null)
	{
		$dbo = JFactory::getDbo();

		if (is_null($pk))
		{
			foreach ($this->_tableKeys as $k)
			{
				$query->where($dbo->qn($k) . ' = ' . $dbo->q($this->{$k}));
			}
		}
		else
		{
			if (is_string($pk))
			{
				$pk = array($this->_tableKeys[0] => $pk);
			}

			$pk = (object) $pk;

			foreach ($this->_tableKeys as $k)
			{
				$query->where($dbo->qn($k) . ' = ' . $dbo->q($pk->{$k}));
			}
		}
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @return  void
	 *
	 * @since   10.1.30
	 */
	public function reset()
	{
		// get the default values for the class from the table
		foreach ($this->getFields() as $k => $v)
		{
			// if the property is not the primary key or private, reset it
			if (!in_array($k, $this->_tableKeys) && (strpos($k, '_') !== 0))
			{
				$this->{$k} = $v->Default;
			}
		}

		// reset table errors
		$this->_errors = array();
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed    $src     An associative array or object to bind to the Table instance.
	 * @param   mixed    $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   10.1.30
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		// check if the source value is an array or object
		if (!is_object($src) && !is_array($src))
		{
			throw new InvalidArgumentException(
				sprintf(
					'Could not bind the data source in %s::bind(), the source must be an array or object but a "%s" was given.',
					get_class($this),
					gettype($src)
				)
			);
		}

		// if the source value is an object, get its accessible properties
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		$ignore = (array) $ignore;

		// bind the source value, excluding the ignored fields
		foreach ($this->getProperties() as $k => $v)
		{
			// only process fields not in the ignore array
			if (!in_array($k, $ignore))
			{
				if (isset($src[$k]))
				{
					$this->{$k} = $src[$k];
				}
			}
		}

		return true;
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to ensure they are safe to store in the database.
	 *
	 * Child classes should override this method to make sure the data they are storing in the database is safe and as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   10.1.30
	 */
	public function check()
	{
		// inherit in children classes
		return true;
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   10.1.30
	 */
	public function store($updateNulls = false)
	{
		$dbo   = JFactory::getDbo();
		$event = JEventDispatcher::getInstance();

		$k = $this->_tableKeys;

		// pre-processing by observers
		$event->trigger('onBeforeStore', array($updateNulls, $k));

		// If a primary key exists update the object, otherwise insert it.
		if ($this->hasPrimaryKey())
		{
			$result = $dbo->updateObject($this->_table, $this, $this->_tableKeys, $updateNulls);
		}
		else
		{
			$result = $dbo->insertObject($this->_table, $this, $this->_tableKeys[0]);
		}

		// post-processing by observers
		$event->trigger('onAfterStore', array(&$result));

		return $result;
	}

	/**
	 * Method to provide a shortcut to binding, checking and storing a Table instance to the database table.
	 *
	 * The method will check a row in once the data has been stored and if an ordering filter is present will attempt to reorder
	 * the table rows based on the filter.  The ordering filter is an instance property name.  The rows that will be reordered
	 * are those whose value matches the Table instance for the property specified.
	 *
	 * @param   mixed    $src             An associative array or object to bind to the Table instance.
	 * @param   string   $orderingFilter  Filter for the order updating
	 * @param   mixed    $ignore          An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   10.1.30
	 */
	public function save($src, $orderingFilter = '', $ignore = '')
	{
		// attempt to bind the source to the instance
		if (!$this->bind($src, $ignore))
		{
			return false;
		}

		// run any sanity checks on the instance and verify that it is ready for storage
		if (!$this->check())
		{
			return false;
		}

		// attempt to store the properties to the database table
		if (!$this->store())
		{
			return false;
		}

		// ff an ordering filter is set, attempt reorder the rows in the table based on the filter and value.
		if ($orderingFilter)
		{
			$dbo = JFactory::getDbo();

			$filterValue = $this->{$orderingFilter};
			$this->reorder($orderingFilter ? $dbo->qn($orderingFilter) . ' = ' . $dbo->q($filterValue) : '');
		}

		return true;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed    $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   10.1.30
	 *
	 * @throws  UnexpectedValueException
	 */
	public function delete($pk = null)
	{
		if (is_null($pk))
		{
			$pk = array();

			foreach ($this->_tableKeys as $key)
			{
				$pk[$key] = $this->{$key};
			}
		}
		else if (!is_array($pk))
		{
			$pk = array($this->_tableKeys[0] => $pk);
		}

		foreach ($this->_tableKeys as $key)
		{
			$pk[$key] = is_null($pk[$key]) ? $this->{$key} : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new UnexpectedValueException('Null primary key not allowed.');
			}

			$this->{$key} = $pk[$key];
		}

		$dbo   = JFactory::getDbo();
		$event = JEventDispatcher::getInstance();

		// pre-processing by observers
		$event->trigger('onBeforeDelete', array($pk));

		// delete the row by primary key
		$q = $dbo->getQuery(true)
			->delete($this->_table);

		$this->appendPrimaryKeys($q, $pk);

		$dbo->setQuery($q);

		// check for a database error
		$dbo->execute();

		// post-processing by observers
		$event->trigger('onAfterDelete', array($pk));

		return true;
	}

	/**
	 * Method to get the next ordering value for a group of rows defined by an SQL WHERE clause.
	 *
	 * This is useful for placing a new item last in a group of items in the table.
	 *
	 * @param   string   $where  WHERE clause to use for selecting the MAX(ordering) for the table.
	 *
	 * @return  integer  The next ordering value.
	 *
	 * @since   10.1.30
	 *
	 * @throws  UnexpectedValueException
	 */
	public function getNextOrder($where = '')
	{
		// check if there is an ordering field set
		$orderingField = $this->getColumnAlias('ordering');

		if (!property_exists($this, $orderingField))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', get_class($this)));
		}

		$dbo = JFactory::getDbo();

		// get the largest ordering value for a given where clause
		$q = $dbo->getQuery(true)
			->select('MAX(' . $dbo->qn($orderingField) . ')')
			->from($this->_table);

		if ($where)
		{
			$q->where($where);
		}

		$dbo->setQuery($q);
		$max = (int) $dbo->loadResult();

		// return the largest ordering value + 1
		return $max + 1;
	}

	/**
	 * Method to compact the ordering values of rows in a group of rows defined by an SQL WHERE clause.
	 *
	 * @param   string  $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 *
	 * @return  mixed  Boolean  True on success.
	 *
	 * @since   10.1.30
	 *
	 * @throws  UnexpectedValueException
	 */
	public function reorder($where = '')
	{
		// check if there is an ordering field set
		$orderingField = $this->getColumnAlias('ordering');

		if (!property_exists($this, $orderingField))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', get_class($this)));
		}

		$dbo = JFactory::getDbo();

		$quotedOrderingField = $dbo->qn($orderingField);

		$subquery = $dbo->getQuery(true)
			->from($this->_table)
			->selectRowNumber($quotedOrderingField, 'new_ordering');

		$query = $dbo->getQuery(true)
			->update($this->_table)
			->set($quotedOrderingField . ' = sq.new_ordering');

		$innerOn = array();

		// get the primary keys for the selection
		foreach ($this->_tableKeys as $i => $k)
		{
			$subquery->select($dbo->qn($k, 'pk__' . $i));
			$innerOn[] = $dbo->qn($k) . ' = sq.' . $dbo->qn('pk__' . $i);
		}

		// setup the extra where and ordering clause data
		if ($where)
		{
			$subquery->where($where);
			$query->where($where);
		}

		$subquery->where($quotedOrderingField . ' >= 0');
		$query->where($quotedOrderingField . ' >= 0');

		$query->innerJoin('(' . (string) $subquery . ') AS sq ON ' . implode(' AND ', $innerOn));

		$dbo->setQuery($query);
		$dbo->execute();
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database table.
	 *
	 * The method respects checked out rows by other users and will attempt to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update. If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user ID of the user performing the operation.
	 *
	 * @return  boolean  True on success; false if $pks is empty.
	 *
	 * @since   10.1.30
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// sanitize input
		$userId = (int) $userId;
		$state  = (int) $state;

		if (!is_null($pks))
		{
			if (!is_array($pks))
			{
				$pks = array($pks);
			}

			foreach ($pks as $key => $pk)
			{
				if (!is_array($pk))
				{
					$pks[$key] = array($this->_tableKeys[0] => $pk);
				}
			}
		}

		// if there are no primary keys set check to see if the instance key is set
		if (empty($pks))
		{
			$pk = array();

			foreach ($this->_tableKeys as $key)
			{
				if ($this->{$key})
				{
					$pk[$key] = $this->{$key};
				}
				// we don't have a full primary key - return false
				else
				{
					$this->setError('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED');

					return false;
				}
			}

			$pks = array($pk);
		}

		$dbo = JFactory::getDbo();

		$publishedField = $this->getColumnAlias('published');

		foreach ($pks as $pk)
		{
			// update the publishing state for rows with the given primary keys
			$query = $dbo->getQuery(true)
				->update($this->_table)
				->set($dbo->qn($publishedField) . ' = ' . (int) $state);

			// build the WHERE clause for the primary keys
			$this->appendPrimaryKeys($query, $pk);

			$dbo->setQuery($query);

			try
			{
				$dbo->execute();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			// if the Table instance value is in the list of primary keys that were set, set the instance
			$ours = true;

			foreach ($this->_tableKeys as $key)
			{
				if ($this->{$key} != $pk[$key])
				{
					$ours = false;
				}
			}

			if ($ours)
			{
				$this->{$publishedField} = $state;
			}
		}

		return true;
	}

	/**
	 * Method to return the real name of a "special" column such as ordering, hits, published
	 * etc etc. In this way you are free to follow your db naming convention and use the
	 * built in \Joomla functions.
	 *
	 * @param   string  $column  Name of the "special" column (ie ordering, hits).
	 *
	 * @return  string  The string that identify the special.
	 *
	 * @since   10.1.30
	 */
	public function getColumnAlias($column)
	{
		// get the column data if set
		if (isset($this->_columnAlias[$column]))
		{
			$return = $this->_columnAlias[$column];
		}
		else
		{
			$return = $column;
		}

		// sanitize the name
		$return = preg_replace('#[^A-Z0-9_]#i', '', $return);

		return $return;
	}

	/**
	 * Method to register a column alias for a "special" column.
	 *
	 * @param   string  $column       The "special" column (i.e. ordering).
	 * @param   string  $columnAlias  The real column name (i.e. foo_ordering).
	 *
	 * @return  void
	 *
	 * @since   10.1.30
	 */
	public function setColumnAlias($column, $columnAlias)
	{
		// santize the column name alias
		$column = strtolower($column);
		$column = preg_replace('#[^A-Z0-9_]#i', '', $column);

		// set the column alias internally
		$this->_columnAlias[$column] = $columnAlias;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields to the Table instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.
	 *                           If not set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @since   10.1.35
	 */
	public function load($keys = null, $reset = true)
	{
		if (empty($keys))
		{
			$empty = true;
			$keys  = array();

			// If empty, use the value of the current key
			foreach ($this->_tableKeys as $key)
			{
				$empty      = $empty && empty($this->$key);
				$keys[$key] = $this->$key;
			}

			// If empty primary key there's is no need to load anything
			if ($empty)
			{
				return true;
			}
		}
		else if (!is_array($keys))
		{
			// Load by primary key.
			$keyCount = count($this->_tableKeys);

			if ($keyCount)
			{
				if ($keyCount > 1)
				{
					throw new InvalidArgumentException('Table has multiple primary keys specified, only one primary key value provided.');
				}

				$keys = array($this->getKeyName() => $keys);
			}
			else
			{
				throw new RuntimeException('No table keys defined.');
			}
		}

		if ($reset)
		{
			$this->reset();
		}

		$dbo = JFactory::getDbo();

		// Initialise the query.
		$query = $dbo->getQuery(true)
			->select('*')
			->from($this->_table);
		$fields = array_keys($this->getProperties());

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.
			if (!in_array($field, $fields))
			{
				throw new UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
			}

			// Add the search tuple to the query.
			$query->where($dbo->quoteName($field) . ' = ' . $dbo->quote($value));
		}

		$dbo->setQuery($query);

		$row = $dbo->loadAssoc();

		// check that we have a result
		if (empty($row))
		{
			$result = false;
		}
		else
		{
			// Bind the object with the row and return.
			$result = $this->bind($row);
		}

		return $result;
	}
}
