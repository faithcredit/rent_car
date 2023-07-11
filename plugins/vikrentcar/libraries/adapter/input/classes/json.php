<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.input
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.input.input');

/**
 * Abstract Input handler to access JSON requests.
 *
 * @since 10.0
 */
class JInputJSON extends JInput
{
	/**
	 * The raw JSON string from the request.
	 *
	 * @var string
	 */
	private $_raw;

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is the raw HTTP input decoded from JSON)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 */
	public function __construct(array $source = null, array $options = array())
	{
		if (isset($options['filter']))
		{
			$this->filter = $options['filter'];
		}
		else
		{
			$this->filter = new JInputFilter;
		}

		if (is_null($source))
		{
			$this->_raw = file_get_contents('php://input');
			$this->data = json_decode($this->_raw, true);
		}
		else
		{
			$this->data = & $source;
		}

		// Set the options for the class.
		$this->options = $options;
	}

	/**
	 * Gets the raw JSON string from the request.
	 *
	 * @return  string  The raw JSON string from the request.
	 */
	public function getRaw()
	{
		return $this->_raw;
	}
}
