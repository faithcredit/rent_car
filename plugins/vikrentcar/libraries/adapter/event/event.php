<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.event
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Abstract event class.
 * Mainly used to avoid attaching useless events.
 *
 * @since 10.1.29
 */
abstract class JEvent
{
	/**
	 * Class constructor.
	 *
	 * @param   mixed   $config    An optional plugin configuration.
	 */
	public function __construct($config = array())
	{
		if (is_array($config) || $config instanceof stdClass)
		{
			// wrap configuration in a registry
			$config = new JRegistry($config);
		}

		// register configuration
		$this->params = $config;
	}
}
