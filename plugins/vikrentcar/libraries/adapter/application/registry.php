<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.application
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.application.object');

/**
 * Create an alias for JObject class.
 *
 * @since 10.1.18
 * @since 10.1.35  Implements ArrayAccess interface.
 */
class JRegistry extends JObject implements ArrayAccess
{
	/**
	 * Proxy used to access the internal properties.
	 *
	 * @return 	array
	 *
	 * @since 	10.1.35
	 */
	public function toArray()
	{
		return $this->getProperties();
	}

	/**
	 * Checks whether an offset exists in the iterator.
	 *
	 * @param   mixed    $offset  The array offset.
	 *
	 * @return  boolean  True if the offset exists, false otherwise.
	 *
	 * @see 	ArrayAccess
	 *
	 * @since   10.1.35
	 */
	#[ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return (boolean) ($this->get($offset) !== null);
	}

	/**
	 * Gets an offset in the iterator.
	 *
	 * @param   mixed  $offset  The array offset.
	 *
	 * @return  mixed  The array value if it exists, null otherwise.
	 *
	 * @see 	ArrayAccess
	 *
	 * @since   10.1.35
	 */
	#[ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Sets an offset in the iterator.
	 *
	 * @param   mixed  $offset  The array offset.
	 * @param   mixed  $value   The array value.
	 *
	 * @return  void
	 *
	 * @see 	ArrayAccess
	 *
	 * @since   10.1.35
	 */
	#[ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * Unsets an offset in the iterator.
	 *
	 * @param   mixed  $offset  The array offset.
	 *
	 * @return  void
	 *
	 * @see 	ArrayAccess
	 *
	 * @since   10.1.35
	 */
	#[ReturnTypeWillChange]
	public function offsetUnset($offset)
	{
		$this->set($offset, null);
	}
}
