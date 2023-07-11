<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.mvc
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.menu.menu');

/**
 * Menu class for site client.
 *
 * @since  10.1.19
 */
class JMenuSite extends JMenu
{
	/**
	 * @override
	 * Loads the entire menu table into memory.
	 *
	 * @return  boolean  True on success, false on failure
	 */
	public function load()
	{
		// do nothing for the moment as all the functions
		// related to this class seems to be almost useless

		return true;
	}
}
