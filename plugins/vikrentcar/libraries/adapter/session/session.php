<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.session
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class adapter for managing HTTP sessions using the Joomla standard interface.
 *
 * @since 10.0
 */
class JSession
{
	/**
	 * The session adapter instance.
	 *
	 * @var JSession
	 */
	private static $instance = null;

	/**
	 * Session data pool.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		$this->data = &$_SESSION;
	}

	/**
	 * Returns the global Session object, only creating it if it doesn't already exist.
	 *
	 * @return 	self 	The session object.
	 */
	public static function getInstance()
	{
		if (static::$instance === null)
		{
			static::$instance = new JSession();
		}

		return static::$instance;
	}

	/**
	 * Gets data from the session store.
	 *
	 * @param 	string 	$name 		Name of a variable.
	 * @param 	mixed 	$default 	Default value of a variable if not set.
	 * @param 	string 	$namespace 	Namespace to use.
	 *
	 * @return 	mixed 	Value of a variable.
	 */
	public function get($name, $default = null, $namespace = 'default')
	{
		// add prefix and namespace to avoid collisions
		$key = '__' . $namespace . '.' . $name;

		// check if the key is contained in the SESSION
		if (isset($this->data[$key]))
		{
			return $this->data[$key];
		}

		return $default;
	}

	/**
	 * Sets data into the session store.
	 *
	 * @param 	string 	$name 		Name of a variable.
	 * @param 	mixed 	$value 		Value of a variable.
	 * @param 	string 	$namespace 	Namespace to use.
	 *
	 * @return 	mixed 	Old value of a variable.
	 *
	 * @uses 	get()
	 */
	public function set($name, $value = null, $namespace = 'default')
	{
		$prev = $this->get($name, null, $namespace);

		// add prefix and namespace to avoid collisions
		$key = '__' . $namespace . '.' . $name;

		// push the value in the session
		$this->data[$key] = $value;

		return $prev;
	}

	/**
	 * Checks whether data exists in the session store.
	 *
	 * @param 	string 	 $name 		 Name of variable.
	 * @param 	string 	 $namespace  Namespace to use.
	 *
	 * @return  boolean  True if the variable exists.
	 *
	 * @uses 	get()
	 */
	public function has($name, $namespace = 'default')
	{
		return !is_null($this->get($name, null, $namespace));
	}

	/**
	 * Unsets data from the session store.
	 *
	 * @param 	string 	$name 		Name of variable.
	 * @param 	string 	$namespace 	Namespace to use.
	 *
	 * @return 	mixed 	The value from session or NULL if not set.
	 *
	 * @uses 	set()
	 */
	public function clear($name, $namespace = 'default')
	{
		return $this->set($name, null, $namespace);
	}

	/**
	 * Returns our internal nonce identifier.
	 * 
	 * @param   boolean  $forceNew  If true, the action will be randomly changed,
	 *                              instructing WP to use a different token.
	 *
	 * @return 	string   The action name that will be used by WordPress to create
	 *                   a matching token hash.
	 *
	 * @since 	10.1.33
	 */
	public static function getFormTokenAction($forceNew = false)
	{
		// create initial identifier
		static $id = 1;

		if ($forceNew)
		{
			// Refresh identifier when requested.
			// Use a random ID to prevent predictability.
			$id = uniqid();
		}

		// merge method name with our unique identifier
		$action = __METHOD__ . '.' . $id;

		/**
		 * Plugins can use this hook to change at runtime the action to
		 * use while creating/validating a WordPress nonce.
		 *
		 * @param 	string   $action  The action to filter.
		 *
		 * @since 	10.1.33
		 */
		return apply_filters('vik_csrf_token_action', $action);
	}

	/**
	 * Returns the name that will be used while generating the token input
	 * and during its validation.
	 *
	 * @return 	string  The input name.
	 */
	public static function getFormTokenName()
	{
		/**
		 * Plugins can use this hook to change at runtime the name to
		 * use while creating/validating a WordPress nonce.
		 *
		 * @param 	string   $name  The name to filter.
		 *
		 * @since 	10.1.33
		 */
		return apply_filters('vik_csrf_token_name', 'vikwp_nonce');
	}

	/**
	 * Method to determine a hash for anti-spoofing variable names.
	 *
	 * @param   boolean  $forceNew  If true, force a new token to be created.
	 *
	 * @return  string   Hashed var name.
	 *
	 * @uses 	getToken()
	 */
	public static function getFormToken($forceNew = false)
	{
		// create nonce by using our internal action
		return wp_create_nonce(static::getFormTokenAction($forceNew));
	}

	/**
	 * Checks for a form token in the request.
	 * Use with JHtml::_('form.token') or Session::getFormToken().
	 *
	 * @param   string   $method  The request method in which to look for the token key.
	 *
	 * @return  boolean  True if found and valid, false otherwise.
	 */
	public static function checkToken($method = 'post')
	{
		/**
		 * Plugins can use this hook to change the default behavior used to validate
		 * the CSRF tokens.
		 *
		 * @param 	boolean|null   $valid  True whether the token is valid, false in case
		 *                                 it is invalid, null to let the system uses its
		 *                                 own validation.
		 *
		 * @since 	10.1.33
		 */
		$validated = apply_filters('vik_csrf_token_check', null);
		
		if (!is_null($validated))
		{
			// A plugin validated the token itself.
			// Even if we cannot trust that validation, we have to return the status
			// fetched by the attached plugins.
			return (bool) $validated;
		}

		$action = static::getFormTokenAction();
		$app    = JFactory::getApplication();

		// check from header first, since AJAX request might specify the
		// X-CSRF-Token directive within the server headers
		$nonce = $app->input->server->get('HTTP_X_CSRF_TOKEN', '', 'alnum');
		
		if ($nonce && wp_verify_nonce($nonce, $action))
		{
			return true;
		}

		// get the name of the data set in request
		$name = static::getFormTokenName();

		// then fallback to HTTP query
		$nonce = $app->input->$method->get($name, '', 'alnum');

		if ($nonce && wp_verify_nonce($nonce, $action))
		{
			return true;
		}

		return false;
	}
}
