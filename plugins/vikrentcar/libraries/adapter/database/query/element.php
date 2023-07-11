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
 * Query Element Class.
 *
 * @since 10.0
 */
class JDatabaseQueryElement
{
	/**
	 * The name of the element.
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * An array of elements.
	 *
	 * @var array
	 */
	protected $elements = null;

	/**
	 * Glue piece.
	 *
	 * @var string
	 */
	protected $glue = null;

	/**
	 * Class constructor.
	 *
	 * @param   string  $name      The name of the element.
	 * @param   mixed   $elements  String or array.
	 * @param   string  $glue      The glue for elements.
	 *
	 * @uses 	append()
	 */
	public function __construct($name, $elements, $glue = ',')
	{
		$this->name = $name;
		$this->glue = $glue;

		$this->elements = array();

		$this->append($elements);
	}

	/**
	 * Magic function to convert the query element to a string.
	 *
	 * @return  string 	The query element as string.
	 */
	public function __toString()
	{
		// check if the element is a function
		if (substr($this->name, -2) == '()')
		{
			// implode the elements between the parentheses, i.e. CONCAT_WS(' ', `a`, `b`)
			return PHP_EOL . substr($this->name, 0, -2) . '(' . implode($this->glue, $this->elements) . ')';
		}
		else
		{
			// implode the elements after the element, i.e. SELECT `a`, `b`
			return PHP_EOL . $this->name . ' ' . implode($this->glue, $this->elements);
		}
	}

	/**
	 * Appends element parts to the internal list.
	 *
	 * @param   mixed  $elements  String or array.
	 *
	 * @return  void
	 */
	public function append($elements)
	{
		if (is_array($elements))
		{
			$this->elements = array_merge($this->elements, $elements);
		}
		else
		{
			$this->elements[] = $elements;
		}
	}

	/**
	 * Gets the elements of this element.
	 *
	 * @return  array 	The elements.
	 */
	public function getElements()
	{
		return $this->elements;
	}

	/**
	 * Sets the name of this element.
	 *
	 * @param   string  $name  Name of the element.
	 *
	 * @return  self  	This object to support chaining.
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}
}
