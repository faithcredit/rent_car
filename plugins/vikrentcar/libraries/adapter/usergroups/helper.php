<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.user
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Helper to deal with user groups.
 *
 * @since 10.1.30
 */
final class JHelperUsergroups
{
	/**
	 * Singleton instance.
	 *
	 * @var UserGroupsHelper
	 */
	private static $instance = null;

	/**
	 * Available user groups
	 *
	 * @var array
	 */
	private $groups = array();

	/**
	 * Get the helper instance.
	 *
	 * @return  self
	 */
	public static function getInstance()
	{
		if (static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Get the list of existing user groups.
	 *
	 * @return  array
	 */
	public function getAll()
	{
		$this->groups = array();

		foreach (wp_roles()->roles as $slug => $role)
		{
			$tmp = new stdClass;

			$tmp->id    = $slug;
			$tmp->title = $role['name'];

			$this->groups[$slug] = $tmp;
		}

		return $this->groups;
	}
}
