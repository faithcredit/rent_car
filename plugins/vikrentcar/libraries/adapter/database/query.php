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

JLoader::import('adapter.database.query.element');

/**
 * Query Building Class.
 *
 * @since 10.0
 */
class JDatabaseQuery
{
	/**
	 * The database driver.
	 *
	 * @var JDatabase
	 */
	protected $dbo = null;

	/**
	 * The query type.
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * The select element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $select = null;

	/**
	 * The delete element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $delete = null;

	/**
	 * The update element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $update = null;

	/**
	 * The insert element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $insert = null;

	/**
	 * The from element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $from = null;

	/**
	 * The join element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $join = null;

	/**
	 * The set element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $set = null;

	/**
	 * The where element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $where = null;

	/**
	 * The group by element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $group = null;

	/**
	 * The having element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $having = null;

	/**
	 * The column list for an INSERT statement.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $columns = null;

	/**
	 * The values list for an INSERT statement.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $values = null;

	/**
	 * The order element.
	 *
	 * @var JDatabaseQueryElement
	 */
	protected $order = null;

	/**
	 * Details of window function.
	 *
	 * @var    array
	 * @since  10.1.30
	 */
	protected $selectRowNumber = null;

	/**
	 * Class constructor.
	 *
	 * @param   JDatabase  $db  The database driver.
	 */
	public function __construct(JDatabase $dbo = null)
	{
		if (is_null($dbo))
		{
			$dbo = JFactory::getDbo();
		}

		$this->dbo = $dbo;
	}

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string	The completed query.
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->type)
		{
			case 'select':
				$query .= (string) $this->select;
				$query .= (string) $this->from;

				if ($this->join)
				{
					// special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				if ($this->selectRowNumber === null)
				{
					if ($this->group)
					{
						$query .= (string) $this->group;
					}

					if ($this->having)
					{
						$query .= (string) $this->having;
					}
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				break;

			case 'delete':
				$query .= (string) $this->delete;
				$query .= (string) $this->from;

				if ($this->join)
				{
					// special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				break;

			case 'update':
				$query .= (string) $this->update;

				if ($this->join)
				{
					// special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}

				$query .= (string) $this->set;

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				break;

			case 'insert':
				$query .= (string) $this->insert;

				if ($this->values)
				{
					if ($this->columns)
					{
						$query .= (string) $this->columns;
					}

					$elements = $this->values->getElements();

					if (!($elements[0] instanceof $this))
					{
						$query .= ' VALUES ';
					}

					$query .= (string) $this->values;
				}

				break;
		}

		/**
		 * If we are accessing #__users table, we need to route all
		 * the specified columns that belong to Joomla framework.
		 *
		 * By adjusting the query here we can support inner queries
		 * that have been invoked after FROM statment.
		 *
		 * @since 10.1.16
		 */
		$query = JDatabase::adjustJoomlaQuery2WP($query);

		return $query;
	}

	/**
	 * Magic function to get protected variable value.
	 *
	 * @param   string  $name  The name of the variable.
	 *
	 * @return  mixed 	The property value if set, otherwise null.
	 */
	public function __get($name)
	{
		return isset($this->$name) ? $this->$name : null;
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function clear($clause = null)
	{
		if (is_null($clause))
		{
			/**
			 * Clear all only in case the clause is not specified,
			 * because the query would be cleared by passing an
			 * unsupported clause (e.g. 'limit').
			 *
			 * @since 10.1.23
			 */
			$this->type 	= null;
			$this->select 	= null;
			$this->delete 	= null;
			$this->update 	= null;
			$this->insert 	= null;
			$this->from 	= null;
			$this->join 	= null;
			$this->set 		= null;
			$this->where 	= null;
			$this->group 	= null;
			$this->having 	= null;
			$this->order 	= null;
			$this->columns 	= null;
			$this->values 	= null;

			$this->selectRowNumber = null;
		}
		else
		{
			switch ($clause)
			{
				case 'select':
					$this->select 	= null;
					$this->type 	= null;
					
					$this->selectRowNumber = null;
					break;

				case 'delete':
					$this->delete 	= null;
					$this->type 	= null;
					break;

				case 'update':
					$this->update 	= null;
					$this->type 	= null;
					break;

				case 'insert':
					$this->insert 	= null;
					$this->type 	= null;
					break;

				case 'from':
					$this->from 	= null;
					break;

				case 'join':
					$this->join 	= null;
					break;

				case 'set':
					$this->set 		= null;
					break;

				case 'where':
					$this->where 	= null;
					break;

				case 'group':
					$this->group 	= null;
					break;

				case 'having':
					$this->having 	= null;
					break;

				case 'order':
					$this->order 	= null;
					break;

				case 'columns':
					$this->columns 	= null;
					break;

				case 'values':
					$this->values 	= null;
					break;
			}
		}

		return $this;
	}

	/**
	 * Adds a column, or array of column names that would be used for an INSERT INTO statement.
	 *
	 * @param   mixed  $columns  A column name, or array of column names.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function columns($columns)
	{
		if (is_null($this->columns))
		{
			// don't name the element to wrap the columns between the parentheses
			$this->columns = new JDatabaseQueryElement('()', $columns);
		}
		else
		{
			$this->columns->append($columns);
		}

		return $this;
	}

	/**
	 * Add a table name to the DELETE clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->delete('#__a')->where('id = 1');
	 *
	 * @param   string  $table  The name of the table to delete from.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function delete($table = null)
	{
		$this->type   = 'delete';
		$this->delete = new JDatabaseQueryElement('DELETE', null);

		if (!empty($table))
		{
			$this->from($table);
		}

		return $this;
	}

	/**
	 * Add a table to the FROM clause of the query.
	 *
	 * Note that while an array of tables can be provided, it is recommended you use explicit joins.
	 *
	 * Usage:
	 * $query->select('*')->from('#__a');
	 *
	 * @param   mixed   $tables         A string or array of table names.
	 *                                  This can be a JDatabaseQuery object (or a child of it) when used
	 *                                  as a subquery in FROM clause along with a value for $subQueryAlias.
	 * @param   string  $subQueryAlias  Alias used when $tables is a JDatabaseQuery.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function from($tables, $subQueryAlias = null)
	{
		if ($tables instanceof $this)
		{
			if (is_null($subQueryAlias))
			{
				throw new RuntimeException('Missing alias for sub-query.', 500);
			}

			$tables = '( ' . (string) $tables . ' ) AS ' . $this->dbo->qn($subQueryAlias);
		}

		if (is_null($this->from))
		{
			$this->from = new JDatabaseQueryElement('FROM', $tables);
		}
		else
		{
			$this->from->append($tables);
		}

		return $this;
	}

	/**
	 * Add a grouping column to the GROUP clause of the query.
	 *
	 * Usage:
	 * $query->group('id');
	 *
	 * @param   mixed 	$columns  A string or array of ordering columns.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function group($columns)
	{
		if (is_null($this->group))
		{
			$this->group = new JDatabaseQueryElement('GROUP BY', $columns);
		}
		else
		{
			$this->group->append($columns);
		}

		return $this;
	}

	/**
	 * A conditions to the HAVING clause of the query.
	 *
	 * Usage:
	 * $query->group('id')->having('COUNT(id) > 5');
	 *
	 * @param   mixed   $conditions  A string or array of columns.
	 * @param   string  $glue        The glue by which to join the conditions. Defaults to AND.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function having($conditions, $glue = 'AND')
	{
		if (is_null($this->having))
		{
			$glue = strtoupper($glue);
			$this->having = new JDatabaseQueryElement('HAVING', $conditions, " $glue ");
		}
		else
		{
			$this->having->append($conditions);
		}

		return $this;
	}

	/**
	 * Add an INNER JOIN clause to the query.
	 *
	 * Usage:
	 * $query->innerJoin('b ON b.id = a.id')->innerJoin('c ON c.id = b.id');
	 *
	 * @param   string  $condition  The join condition.
	 *
	 * @return  self  	This object to support chaining.
	 *
	 * @uses 	join()
	 */
	public function innerJoin($condition)
	{
		$this->join('INNER', $condition);

		return $this;
	}

	/**
	 * Add a table name to the INSERT clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->insert('#__a')->columns('id, title')->values('1,2')->values('3,4');
	 * $query->insert('#__a')->columns('id, title')->values(array('1,2', '3,4'));
	 *
	 * @param   mixed 	$table 	The name of the table to insert data into.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function insert($table)
	{
		$this->type   = 'insert';
		$this->insert = new JDatabaseQueryElement('INSERT INTO', $table);

		return $this;
	}

	/**
	 * Add a JOIN clause to the query.
	 *
	 * Usage:
	 * $query->join('INNER', 'b ON b.id = a.id);
	 *
	 * @param   string  $type        The type of join. This string is prepended to the JOIN keyword.
	 * @param   string  $conditions  A string or array of conditions.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function join($type, $conditions)
	{
		if (is_null($this->join))
		{
			$this->join = array();
		}

		$this->join[] = new JDatabaseQueryElement(strtoupper($type) . ' JOIN', $conditions);

		return $this;
	}

	/**
	 * Add a LEFT JOIN clause to the query.
	 *
	 * Usage:
	 * $query->leftJoin('b ON b.id = a.id')->leftJoin('c ON c.id = b.id');
	 *
	 * @param   string  $condition  The join condition.
	 *
	 * @return  self  	This object to support chaining.
	 *
	 * @uses 	join()
	 */
	public function leftJoin($condition)
	{
		$this->join('LEFT', $condition);

		return $this;
	}

	/**
	 * Add an ordering column to the ORDER clause of the query.
	 *
	 * Usage:
	 * $query->order('foo')->order('bar');
	 * $query->order(array('foo','bar'));
	 *
	 * @param   mixed 	$columns  A string or array of ordering columns.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function order($columns)
	{
		if (is_null($this->order))
		{
			$this->order = new JDatabaseQueryElement('ORDER BY', $columns);
		}
		else
		{
			$this->order->append($columns);
		}

		return $this;
	}

	/**
	 * Add an OUTER JOIN clause to the query.
	 *
	 * Usage:
	 * $query->outerJoin('b ON b.id = a.id')->outerJoin('c ON c.id = b.id');
	 *
	 * @param   string  $condition  The join condition.
	 *
	 * @return  self  	This object to support chaining.
	 *
	 * @uses 	join()
	 */
	public function outerJoin($condition)
	{
		$this->join('OUTER', $condition);

		return $this;
	}

	/**
	 * Add a RIGHT JOIN clause to the query.
	 *
	 * Usage:
	 * $query->rightJoin('b ON b.id = a.id')->rightJoin('c ON c.id = b.id');
	 *
	 * @param   string  $condition  The join condition.
	 *
	 * @return  self  	This object to support chaining.
	 *
	 * @uses 	join()
	 */
	public function rightJoin($condition)
	{
		$this->join('RIGHT', $condition);

		return $this;
	}

	/**
	 * Add a single column, or array of columns to the SELECT clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The select method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->select('a.*')->select('b.id');
	 * $query->select(array('a.*', 'b.id'));
	 *
	 * @param   mixed  $columns  A string or an array of field names.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function select($columns)
	{
		$this->type = 'select';

		if (is_null($this->select))
		{
			$this->select = new JDatabaseQueryElement('SELECT', $columns);
		}
		else
		{
			$this->select->append($columns);
		}

		return $this;
	}

	/**
	 * Add a single condition string, or an array of strings to the SET clause of the query.
	 *
	 * Usage:
	 * $query->set('a = 1')->set('b = 2');
	 * $query->set(array('a = 1', 'b = 2');
	 *
	 * @param   mixed   $conditions  A string or array of string conditions.
	 * @param   string  $glue        The glue by which to join the condition strings. Defaults to ,.
	 *                               Note that the glue is set on first use and cannot be changed.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function set($conditions, $glue = ',')
	{
		if (is_null($this->set))
		{
			$glue = strtoupper($glue);
			$this->set = new JDatabaseQueryElement('SET', $conditions, "\n\t$glue ");
		}
		else
		{
			$this->set->append($conditions);
		}

		return $this;
	}

	/**
	 * Add a table name to the UPDATE clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->update('#__foo')->set(...);
	 *
	 * @param   string  $table  A table to update.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function update($table)
	{
		$this->type   = 'update';
		$this->update = new JDatabaseQueryElement('UPDATE', $table);

		return $this;
	}

	/**
	 * Adds a tuple, or array of tuples that would be used as values for an INSERT INTO statement.
	 *
	 * Usage:
	 * $query->values('1,2,3')->values('4,5,6');
	 * $query->values(array('1,2,3', '4,5,6'));
	 *
	 * @param   string  $values  A single tuple, or array of tuples.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function values($values)
	{
		if (is_null($this->values))
		{
			// don't name the element to wrap the values between the parentheses
			$this->values = new JDatabaseQueryElement('()', $values, '),(');
		}
		else
		{
			$this->values->append($values);
		}

		return $this;
	}

	/**
	 * Add a single condition, or an array of conditions to the WHERE clause of the query.
	 *
	 * Usage:
	 * $query->where('a = 1')->where('b = 2');
	 * $query->where(array('a = 1', 'b = 2'));
	 *
	 * @param   mixed   $conditions  A string or array of where conditions.
	 * @param   string  $glue        The glue by which to join the conditions. Defaults to AND.
	 *                               Note that the glue is set on first use and cannot be changed.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function where($conditions, $glue = 'AND')
	{
		if (is_null($this->where))
		{
			$glue = strtoupper($glue);
			$this->where = new JDatabaseQueryElement('WHERE', $conditions, " $glue ");
		}
		else
		{
			$this->where->append($conditions);
		}

		return $this;
	}

	/**
	 * Extend the WHERE clause with a single condition or an array of conditions, with a potentially
	 * different logical operator from the one in the current WHERE clause.
	 *
	 * Usage:
	 * $query->where(array('a = 1', 'b = 2'))->extendWhere('XOR', array('c = 3', 'd = 4'));
	 * will produce: WHERE ((a = 1 AND b = 2) XOR (c = 3 AND d = 4)
	 *
	 * @param   string  $outerGlue   The glue by which to join the conditions to the current WHERE conditions.
	 * @param   mixed   $conditions  A string or array of WHERE conditions.
	 * @param   string  $innerGlue   The glue by which to join the conditions. Defaults to AND.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function extendWhere($outerGlue, $conditions, $innerGlue = 'AND')
	{
		// Replace the current WHERE with a new one which has the old one as an unnamed child.
		// Use "()" as name to wrap the existing WHERE between the parentheses.
		$this->where = new JDatabaseQueryElement('WHERE', $this->where->setName('()'), " $outerGlue ");

		// append the new conditions as a new unnamed child
		$this->where->append(new JDatabaseQueryElement('()', $conditions, " $innerGlue "));

		return $this;
	}

	/**
	 * Extend the WHERE clause with an OR and a single condition or an array of conditions.
	 *
	 * Usage:
	 * $query->where(array('a = 1', 'b = 2'))->orWhere(array('c = 3', 'd = 4'));
	 * will produce: WHERE ((a = 1 AND b = 2) OR (c = 3 AND d = 4)
	 *
	 * @param   mixed   $conditions  A string or array of WHERE conditions.
	 * @param   string  $glue        The glue by which to join the conditions. Defaults to AND.
	 *
	 * @return  self  	This object to support chaining.
	 *
	 * @uses 	extendWhere()
	 */
	public function orWhere($conditions, $glue = 'AND')
	{
		return $this->extendWhere('OR', $conditions, $glue);
	}

	/**
	 * Extend the WHERE clause with an AND and a single condition or an array of conditions.
	 *
	 * Usage:
	 * $query->where(array('a = 1', 'b = 2'))->andWhere(array('c = 3', 'd = 4'));
	 * will produce: WHERE ((a = 1 AND b = 2) AND (c = 3 OR d = 4)
	 *
	 * @param   mixed   $conditions  A string or array of WHERE conditions.
	 * @param   string  $glue        The glue by which to join the conditions. Defaults to OR.
	 *
	 * @return  self  	This object to support chaining.
	 *
	 * @uses 	extendWhere()
	 */
	public function andWhere($conditions, $glue = 'OR')
	{
		return $this->extendWhere('AND', $conditions, $glue);
	}

	/**
	 * Return the number of the current row.
	 *
	 * Usage:
	 * $query->select('id');
	 * $query->selectRowNumber('ordering,publish_up DESC', 'new_ordering');
	 * $query->from('#__content');
	 *
	 * @param   string  $orderBy           An expression of ordering for window function.
	 * @param   string  $orderColumnAlias  An alias for new ordering column.
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 *
	 * @since   10.1.30
	 * @throws  RuntimeException
	 */
	public function selectRowNumber($orderBy, $orderColumnAlias)
	{
		if ($this->selectRowNumber)
		{
			throw new RuntimeException("Method 'selectRowNumber' can be called only once per instance.");
		}

		$this->type = 'select';

		$this->selectRowNumber = array(
			'orderBy'          => $orderBy,
			'orderColumnAlias' => $orderColumnAlias,
		);

		$this->select("ROW_NUMBER() OVER (ORDER BY $orderBy) AS $orderColumnAlias");

		return $this;
	}
}
