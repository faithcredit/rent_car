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

JLoader::import('adapter.application.object');

/**
 * User adapter class.
 *
 * @since 	10.0
 * @link 	https://codex.wordpress.org/Class_Reference/WP_User
 */
class JUser extends JObject
{
	/**
	 * The wordpress user wrapper.
	 *
	 * @var WP_User
	 */
	private $user;

	/**
	 * Data object to save the user.
	 *
	 * @var object
	 */
	private $data = null;

	/**
	 * A list of user instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Class constructor.
	 *
	 * @param 	integer  $id 	The primary key value of the user to load.
	 */
	public function __construct($id = 0)
	{
		// load current logged user when the ID is not provided
		if (is_null($id))
		{
			if (!function_exists('wp_get_current_user'))
			{
				// it is not yet possible to access current user details
				throw new RuntimeException('It is not yet possible to access current user details', 500);
			}

			$this->user = wp_get_current_user();
		}
		// otherwise load user by id
		else
		{
			$this->user = new WP_User($id);
		}
	}

	/**
	 * Returns the global user object, only creating it if it doesn't already exist.
	 *
	 * @param   integer  $identifier  The primary key of the user to load (optional).
	 *
	 * @return 	self 	 The user instance.
	 */
	public static function getInstance($id = null)
	{
		// always try to retrieve user data
		$user = new static($id);

		// dispatch parent method to check if the user is empty
		if (!$user->exists())
		{
			// do not cache
			return $user;
		}

		// cache user object
		if (!isset(static::$instances[$id]))
		{
			static::$instances[$id] = $user;
		}

		return static::$instances[$id];
	}

	/**
	 * Magic method to access internal properties.
	 *
	 * @param 	string 	$name 	The property to read.
	 *
	 * @return 	mixed 	The property value.
	 */
	public function __get($name)
	{
		// property switch for Joomla portability
		switch ($name)
		{
			case 'guest':
				return $this->user->exists() ? 0 : 1;

			case 'username':
				return $this->user->user_login;

			case 'name':
				return $this->user->display_name;

			case 'email':
				return $this->user->user_email;

			/**
			 * It is now possible to retrieve the roles
			 * to which the user is assigned.
			 *
			 * @since 10.1.24
			 */
			case 'groups':
				return (array) $this->user->roles;

			case 'id':
				/**
				 * WP_User->id is deprecated since wp 2.1.0.
				 * Use uppercase ID instead.
				 */
				return $this->user->ID;
		}

		// otherwise try to get the property from the wrapped object
		if (isset($this->user->{$name}))
		{
			return $this->user->{$name};
		}

		return null;
	}

	/**
	 * Magic method to check whether a property is set.
	 *
	 * @param 	string 	 $name 	The property to check.
	 *
	 * @return 	boolesn  True if set, false otherwise.
	 *
	 * @since 	10.1.30
	 */
	public function __isset($name)
	{
		return $this->{$name} !== null;
	}

	/**
	 * Magic method to access internal methods.
	 *
	 * @param 	string 	$name 	The method to execute.
	 * @param 	array 	$args 	A list of arguments.
	 *
	 * @return 	mixed 	The method result.
	 */
	public function __call($name, $args)
	{
		// dispatch wrapped object like a proxy
		if (method_exists($this->user, $name))
		{
			return call_user_func_array(array($this->user, $name), $args);
		}
	}

	/**
	 * Method to bind an associative array of data to a user object.
	 *
	 * @param   array  	 &$array  The associative array to bind to the object.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 */
	public function bind(array &$array)
	{
		$reg = new JObject($array);

		$this->data = new stdClass;

		$group = (array) $reg->get('groups');

		$this->data->user_nicename 	= $reg->get('name');
		$this->data->display_name 	= $reg->get('name');
		$this->data->user_login 	= $reg->get('username');
		$this->data->user_email 	= $reg->get('email');
		$this->data->user_pass 		= $reg->get('password');
		$this->data->role 			= array_shift($group);

		/**
		 * Skip notification only in case 'sendEmail' attribute
		 * has been specified with a false boolean.
		 *
		 * @since 10.1.24
		 */
		$this->data->notify = $reg->get('sendEmail', true);

		return true;
	}

	/**
	 * Method to save the user object to the database.
	 *
	 * @param   boolean  $updateOnly  Save the object only if not a new user.
	 *
	 * @return  boolean  True on success, otherwise false.
	 */
	public function save($updateOnly = false)
	{
		// never called bind() method to fill $data property
		if (is_null($this->data))
		{
			return false;
		}

		// if the user exists, proceed with the update
		if ($this->user->exists())
		{
			$id = wp_update_user($this->data);
		}
		// otherwise make sure we can insert the user
		else if (!$updateOnly)
		{
			$id = wp_insert_user($this->data);
		}

		// check if the insert/update raised an error
		if (is_wp_error($id))
		{
			// push the error in the list
			$this->setError($id);
			
			return false;
		}

		// reload the user object on success
		$this->user = new WP_User($id);

		/**
		 * Evaluates if the user should be notified.
		 * 
		 * @since 10.1.24
		 */
		if (!empty($this->data->notify))
		{
			// 'user' for sending notification only to the user created
			wp_send_new_user_notifications($id, 'user');
		}

		return true;
	}

	/**
	 * Method to check User object authorisation against an access control
	 * object and optionally an access extension object.
	 *
	 * @param   string   $action 	The name of the action to check for permission.
	 * @param   string   $asset  	The name of the asset on which to perform the action.
	 *
	 * @return  boolean  True if authorised.
	 */
	public function authorise($action, $asset = null)
	{
		JLoader::import('adapter.acl.access');
		
		// $groups = JAccess::getGroupsByUser($this->id);
		$groups = $this->roles;

		$cap = JAccess::adjustCapability($action, $asset);

		foreach ($groups as $group)
		{
			if (JAccess::checkGroup($group, $action, $asset))
			{
				return true;
			}

			/**
			 * This statement is used to check the permissions of an administrator
			 * that shares the same account on a multi-site instance.
			 * In this way, WP_User should check (on cascade) all the permissions
			 * related to the similar accounts.
			 *
			 * WP_User::has_cap() is used by current_user_can().
			 *
			 * @since 10.1.1
			 */
			if ($this->has_cap($cap))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Method to get the user timezone.
	 *
	 * @return 	DateTimeZone
	 *
	 * @since 	10.1.35
	 */
	public function getTimezone()
	{
		// get default system timezone, since WordPress doesn't support
		// a feature to differentiate the timezone per user
		$timezone = JFactory::getApplication()->get('offset', 'UTC');

		return new DateTimeZone($timezone);
	}

	/**
	 * Gets an array of the authorised access levels for the user.
	 *
	 * @return  array
	 *
	 * @since   10.1.39
	 */
	public function getAuthorisedViewLevels()
	{
		$levels = [];

		if (in_array('administrator', $this->user->roles))
		{
			// super user
			$levels = [1, 2, 3, 6];
		}
		else if (in_array('editor', $this->user->roles) || in_array('author', $this->user->roles))
		{
			// special
			$levels = [1, 2, 3];
		}
		else if ($this->user->roles)
		{
			// registered
			$levels = [1, 2];
		}
		else
		{
			// guest user
			$levels = [1, 5];
		}

		return $levels;
	}
}
