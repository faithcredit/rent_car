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

JLoader::import('adapter.router.router');

/**
 * Class to create and parse routes for com_users.
 *
 * @since  10.1.19
 */
class JRouterUsers extends JRouter
{
	/**
	 * @override
	 * Function to convert an internal URI to a route.
	 *
	 * @param   mixed 	$url  The internal URL or an associative array.
	 *
	 * @return  mixed 	The absolute search engine friendly URL object.
	 */
	public function build($url)
	{
		// search for option=com_users&view=reset
		if (preg_match("/&view=reset/i", $url))
		{
			// return WordPress password reset URL
			return wp_lostpassword_url();
		}

		// search for option=com_users&view=remind
		if (preg_match("/&view=remind/i", $url))
		{
			// WordPress doesn't support a page to remind the username, 
			// we should just return an empty link
			return '#';
		}

		return $url;
	}
}
