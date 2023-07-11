<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.config
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * System configuration class.
 * This class acts as a bridge between the Joomla configuration and
 * the settings provided by WordPress. In fact, by requesting "offset"
 * setting, the class will return the "timezone_string" option.
 *
 * @since 10.1.4
 */
class JConfig
{
	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param 	string  $key 	The name of the property.
	 * @param 	mixed   $def 	The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 */
	public function get($key, $def = null)
	{
		// check for the global timezone
		if ($key == 'offset')
		{
			$offset = get_option('timezone_string');

			// remove any unwanted char (used to ignore manual offsets)
			$offset = preg_replace("/[^a-zA-Z_\/]/", "", $offset);

			return $offset ? $offset : $def;
		}

		// switch $key to check if the name of a Joomla setting
		// should be changed to be used in wordpress
		switch ($key)
		{
			case 'list_limit':
				$input = JFactory::getApplication()->input;

				/**
				 * Try to use the list limit that belong to the current plugin.
				 *
				 * @since 10.1.23
				 */
				$page = $input->get('page');

				if ($page)
				{
					// use setting name for current plugin
					$key = $page . '_list_limit';
					// access user meta (user ID, setting key, true to return a scalar value)
					$val = get_user_meta(JFactory::getUser()->id, $key, true);

					if ((int) $val > 0)
					{
						// return pagination if higher than 0
						return $val;
					}
				}
				else
				{
					/**
					 * No page set in request, we are probably in the front-end.
					 * Try to look for a global option for the list limit of the current plugin.
					 *
					 * @since 10.1.33
					 */
					$plugin = preg_replace("/^com_/i", '', $input->get('option', ''));
					$key 	= $plugin . '_list_limit';
				}

				$def = abs(is_null($def) ? 20 : $def);
				break;

			case 'editor':
				$key = 'system.editor';
				$def = is_null($def) ? 'tinymce' : $def;
				break;

			case 'sitename':
				$key = 'blogname';
				break;

			/**
			 * Added support for setting used to fetch the system logging path.
			 *
			 * @since 10.1.35
			 */
			case 'log_path':
				if (is_string(WP_DEBUG_LOG))
				{
					$log_path = WP_DEBUG_LOG;
				}
				else
				{
					$log_path = WP_CONTENT_DIR;
				}
				
				return $log_path;

			/**
			 * Added support for setting used to fetch the system tmp path.
			 *
			 * @since 10.1.35
			 */
			case 'tmp_path':
				return get_temp_dir();

			case 'users.new_usertype':
				$key = 'default_role';
				$def = is_int($def) ? 'subscriber' : $def;
				break;

			/**
			 * Added support for setting used to check whether a user
			 * can register a new account.
			 *
			 * @since 10.1.24
			 */
			case 'users.allowUserRegistration':
				$key = 'users_can_register';
				break;

			case 'languages.admin':
			case 'languages.site':
				$lang = get_option('WPLANG');
				return str_replace('_', '-', $lang ? $lang : 'en-US');
		}

		return get_option($key, $def);
	}

	/**
	 * Magic method used to invoke the get() method
	 * by accessing the property name directly.
	 *
	 * @param 	string 	$name 	The property name.
	 *
	 * @return 	mixed 	The configuration value.
	 *
	 * @uses 	get()
	 */
	public function __get($name)
	{
		return $this->get($name);
	}
}
