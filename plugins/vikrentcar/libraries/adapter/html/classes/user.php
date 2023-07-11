<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.html
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Utility class working with users.
 *
 * @since 10.1.16
 */
abstract class JHtmlUser
{
	/**
	 * Displays a list of user groups.
	 *
	 * @param   boolean  $includeSuperAdmin  True to include super admin groups, false to exclude them.
	 *
	 * @return  array  	 An array containing a list of user groups.
	 */
	public static function groups($includeSuperAdmin = false)
	{
		JLoader::import('adapter.acl.access');

		$groups = array();

		foreach (wp_roles()->roles as $slug => $role)
		{
			// make sure Super Admin usergroup should be included or
			// whether the current group DOES NOT support super admin caps
			if ($includeSuperAdmin || !JAccess::checkGroup($slug, 'core.admin'))
			{
				$groups[] = JHtml::_('select.option', $slug, $role['name']);
			}
		}

		/**
		 * @todo should we push also a record to support "Guest" users?
		 */

		// reverse the roles (from the lowest to the highest)
		$groups = array_reverse($groups);

		return $groups;
	}
}
