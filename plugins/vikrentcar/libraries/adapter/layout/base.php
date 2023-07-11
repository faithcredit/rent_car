<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.layout
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Base class for rendering a display layout
 *
 * @since 10.1.18
 */
class JLayoutBase
{
	/**
	 * Options object.
	 *
	 * @var JRegistry
	 */
	protected $options = null;

	/**
	 * Data for the layout.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Debug information messages.
	 *
	 * @var array
	 */
	protected $debugMessages = array();

	/**
	 * Set the options.
	 *
	 * @param   mixed 	$options  Array / JRegistry object with the options to load.
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function setOptions($options = null)
	{
		// registry received
		if ($options instanceof JRegistry)
		{
			$this->options = $options;
		}
		// array received
		else if (is_array($options))
		{
			$this->options = new JRegistry($options);
		}
		else
		{
			$this->options = new JRegistry;
		}

		return $this;
	}

	/**
	 * Get the options.
	 *
	 * @return  JRegistry 	Object with the options.
	 *
	 * @uses 	resetOptions()
	 */
	public function getOptions()
	{
		// Always return a JRegistry instance
		if (!($this->options instanceof JRegistry))
		{
			$this->resetOptions();
		}

		return $this->options;
	}

	/**
	 * Function to empty all the options.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	resetOptions()
	 */
	public function resetOptions()
	{
		return $this->setOptions(null);
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 */
	public function escape($output)
	{
		/**
		 * Attributes are now escaped by using the built-in WP function.
		 *
		 * @since 10.1.33
		 */
		return esc_attr($output);
	}

	/**
	 * Gets the debug messages array.
	 *
	 * @return  array
	 */
	public function getDebugMessages()
	{
		return $this->debugMessages;
	}

	/**
	 * Method to render the layout.
	 *
	 * @param   array   $displayData  Array of properties available for use inside
	 * 								  the layout file to build the displayed output.
	 *
	 * @return  string  The necessary HTML to display the layout.
	 */
	public function render($displayData)
	{
		// automatically merge any previously data set if $displayData is an array
		if (is_array($displayData))
		{
			$displayData = array_merge($this->data, $displayData);
		}

		return '';
	}

	/**
	 * Renders the list of debug messages.
	 *
	 * @return  string  Output text/HTML code.
	 */
	public function renderDebugMessages()
	{
		return implode($this->debugMessages, "\n");
	}

	/**
	 * Adds a debug message to the debug messages array.
	 *
	 * @param   string  $message  Message to save.
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function addDebugMessage($message)
	{
		$this->debugMessages[] = $message;

		return $this;
	}

	/**
	 * Clears the debug messages array.
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function clearDebugMessages()
	{
		$this->debugMessages = array();

		return $this;
	}

	/**
	 * Renders a layout with debug info.
	 *
	 * @param   mixed   $data  Data passed to the layout.
	 *
	 * @return  string 	The necessary HTML to display the layout.
	 *
	 * @uses 	setDebug()
	 * @uses 	render()
	 */
	public function debug($data = array())
	{
		$this->setDebug(true);

		$output = $this->render($data);

		$this->setDebug(false);

		return $output;
	}

	/**
	 * Method to get the value from the data array.
	 *
	 * @param   string  $key           Key to search for in the data array.
	 * @param   mixed   $defaultValue  Default value to return if the key is not set.
	 *
	 * @return  mixed   The data value if set, otherwise the default key.
	 */
	public function get($key, $defaultValue = null)
	{
		return isset($this->data[$key]) ? $this->data[$key] : $defaultValue;
	}

	/**
	 * Gets the data being rendered.
	 *
	 * @return  array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Check if debug mode is enabled.
	 *
	 * @return  boolean  True if enabled, false otherwise.
	 *
	 * @uses 	getOptions()
	 */
	public function isDebugEnabled()
	{
		return $this->getOptions()->get('debug', false) === true;
	}

	/**
	 * Method to set a value in the data array.
	 * Example: $layout->set('items', $items);
	 *
	 * @param   string  $key    Key for the data array.
	 * @param   mixed   $value  Value to assign to the key.
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function set($key, $value)
	{
		$this->data[(string) $key] = $value;

		return $this;
	}

	/**
	 * Sets the the data passed the layout.
	 *
	 * @param   array 	$data  Array with the data for the layout.
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function setData(array $data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * Changes the debug mode.
	 *
	 * @param   boolean  $debug  Enable or disable the debug flag.
	 *
	 * @return  self 	 This object to support chaining.
	 *
	 * @uses 	getOptions()
	 */
	public function setDebug($debug)
	{
		$this->getOptions()->set('debug', (boolean) $debug);

		return $this;
	}
}
