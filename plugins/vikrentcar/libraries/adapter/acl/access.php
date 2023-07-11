<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.acl
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class that handles all access authorisation routines.
 *
 * @since 10.0
 */
class JAccess
{
	/**
	 * A list used to cache the users groups found.
	 *
	 * @var array
	 */
	protected static $usersGroups = array();

	/**
	 * Adjusts the default notation used by Joomla to the Wordpress needs.
	 *
	 * @param   integer  $groupId   The path to the group for which to check authorisation.
	 * @param   string   $assetKey  The asset name. Null fallback to root asset.
	 *
	 * @return  string   The rule name.
	 */
	public static function adjustCapability($action, $assetKey = null)
	{
		/**
		 * In case the $assetKey is not provided, we should recover it from the
		 * request, because Joomla typical rules are not compatible with
		 * Wordpress capabilities. So, rules like "core.admin" must be converted
		 * into "com_[PLUGIN]_admin".
		 *
		 * @since 10.1.16
		 */
		if (!$assetKey)
		{
			$assetKey = JFactory::getApplication()->input->get('option');
		}

		if ($assetKey)
		{
			$action = preg_replace("/^core/", $assetKey, $action);
		}
		
		$action = preg_replace("/\./", '_', $action);

		return $action;
	}

	/**
	 * Method to check if a group is authorised to perform an action, optionally on an asset.
	 *
	 * @param   string   $groupId   The slug of the user group.
	 * @param   string   $action    The name of the action to authorise.
	 * @param   string   $assetKey  The asset name. Null fallback to root asset.
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @uses 	adjustCapability()
	 */
	public static function checkGroup($groupId, $action, $assetKey = null)
	{
		$action = static::adjustCapability($action, $assetKey);

		$role = get_role($groupId);

		if (!$role)
		{
			return false;
		}

		return $role->has_cap($action);
	}

	/**
	 * Method to return a list of user groups mapped to a user. The returned list can optionally hold
	 * only the groups explicitly mapped to the user or all groups both explicitly mapped and inherited
	 * by the user.
	 *
	 * @param   integer  $userId  Id of the user for which to get the list of groups.
	 *
	 * @return  array    List of user group ids to which the user is mapped.
	 */
	public static function getGroupsByUser($userId)
	{
		if (!isset(static::$usersGroups[$userId]))
		{
			$user = get_user_by('id', $userId);

			if ($user)
			{
				static::$usersGroups[$userId] = $user->roles;
			}
			else
			{
				static::$usersGroups[$userId] = array();
			}
		}

		return static::$usersGroups[$userId];
	}

	/**
	 * Method to return a list of actions for which permissions can be set given a component and section.
	 *
	 * @param   string  $component  The component from which to retrieve the actions.
	 * @param   string  $section    The name of the section within the component from which to retrieve the actions.
	 *
	 * @return  array  	List of actions available for the given component and section.
	 *
	 * @uses 	getActionsFromFile()
	 */
	public static function getActions($component, $section = 'component')
	{
		$path = str_replace('/', DIRECTORY_SEPARATOR, WP_PLUGIN_DIR . "/$component/admin/access.xml");

		return static::getActionsFromFile($path);
	}

	/**
	 * Method to return a list of actions from a file for which permissions can be set.
	 *
	 * @param   string  $file   The path to the XML file.
	 *
	 * @return  boolean|array   False in case of error or the list of actions available.
	 *
	 * @uses 	getActionsFromData()
	 */
	public static function getActionsFromFile($file)
	{
		// if unable to find the file return false
		if (!is_file($file) || !is_readable($file))
		{
			return false;
		}

		// otherwise return the actions from the xml
		$xml = simplexml_load_file($file);

		return static::getActionsFromData($xml);
	}

	/**
	 * Method to return a list of actions from a string or from an xml for which permissions can be set.
	 *
	 * @param   string|SimpleXMLElement  $data   The XML string or an XML element.
	 *
	 * @return  boolean|array   False in case of error or the list of actions available.
	 */
	public static function getActionsFromData($data)
	{
		// if the data to load isn't already an XML element or string return false
		if (!$data instanceof SimpleXMLElement && !is_string($data))
		{
			return false;
		}

		// attempt to load the XML if a string
		if (is_string($data))
		{
			try
			{
				$data = new SimpleXMLElement($data);
			}
			catch (Exception $e)
			{
				return false;
			}

			// make sure the XML loaded correctly
			if (!$data)
			{
				return false;
			}
		}

		$list = array();

		// $plugin = (string) $data->attributes()->component;

		if (isset($data->section))
		{
			foreach ($data->section->action as $action)
			{
				$attrs = $action->attributes();

				$rule = new stdClass;
				$rule->name 		= (string) $attrs->name;
				$rule->title 		= (string) $attrs->title;
				$rule->description 	= (string) $attrs->description;

				if (!empty($rule->title))
				{
					$rule->title = JText::_($rule->title);
				}
				else
				{
					$rule->title = $rule->name;
				}

				if (!empty($rule->description))
				{
					$rule->description = JText::_($rule->description);
				}

				if (!empty($rule->name))
				{
					$list[] = $rule;
				}
			}
		}

		return $list;
	}
}
