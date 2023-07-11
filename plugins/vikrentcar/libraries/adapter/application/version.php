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

/**
 * Version information class for the Joomla CMS.
 *
 * @since 10.1.16
 */
final class JVersion
{
	/**
	 * Gets a "PHP standardized" version string for the current WordPress.
	 *
	 * @return  string  Version string.
	 *
	 * @uses 	getLongVersion()
	 */
	public function getShortVersion()
	{
		/**
		 * Try to extract the short version from the default one.
		 *
		 * @since 10.1.26
		 */
		$version = $this->getLongVersion();

		if (preg_match("/^[\d\.]+/", $version, $match))
		{
			// return initial version only (strip all nightly information)
			return $match[0];
		}
		
		// return default version
		return $version;
	}

	/**
	 * Gets a version string for the current WordPress with all release information.
	 *
	 * @return  string  Complete version string.
	 */
	public function getLongVersion()
	{
		// obtain WP version from global var and return it
		global $wp_version;

		return $wp_version;
	}
}
