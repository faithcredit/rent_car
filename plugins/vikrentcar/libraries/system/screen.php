<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	system
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Helper class to setup the WordPress Screen.
 *
 * @since 1.2.5
 */
class VikRentCarScreen
{
	/**
	 * Creates the option section within the WP Screen for VikRentCar.
	 *
	 * @return 	void
	 */
	public static function options()
	{
		$app = JFactory::getApplication();

		// make sure we are in VikRentCar (back-end)
		if (!$app->isAdmin() || $app->input->get('page') != 'vikrentcar')
		{
			// abort
			return;
		}

		// extract view from request
		$view = $app->input->get('view', null);

		if (empty($view))
		{
			// no view, try to check 'task'
			$view = $app->input->get('task', 'dashboard');
		}

		// allowed views to display screen options
		$allowed_views = array(
			'dashboard',
			'orders',
			'seasons',
			'restrictions',
			'trackings',
			'rooms',
		);

		if (!in_array($view, $allowed_views))
		{
			// abort
			return;
		}
 	
 		// create pagination option
	    $args = array(
	        'label'   => __('Number of items per page:'),
	        'default' => 20,
	        'option'  => 'vikrentcar_list_limit',
	    );
	 
	    add_screen_option('per_page', $args);
	}

	/**
	 * Filters a screen option value before it is set.
	 *
	 * @param 	boolean  $skip    Whether to save or skip saving the screen option value. Default false.
	 * @param 	string   $option  The option name.
	 * @param 	mixed    $value   The option value.
	 *
	 * @return  mixed    Returning false to the filter will skip saving the current option.
	 */
	public static function saveOption($skip, $option, $value)
	{
		$lookup = array(
			'vikrentcar_list_limit',
		);

		if (in_array($option, $lookup))
		{
			/**
			 * We also update the global list limit fallback option
			 * 
			 * @since 	1.1.4
			 */
			if ($option == 'vikrentcar_list_limit')
			{
				// cannot have a value lower than 1
				$value = max(array(1, (int) $value));
				// refresh cached value
				JFactory::getApplication()->setUserState('com_vikrentcar.limit', $value);

				update_option($option, (int) $value);
			}

			// return value to save it
			return $value;
		}

		// skip otherwise
		return $skip;
	}

	/**
	 * Creates the Help tabs within the WP Screen for VikRentCar.
	 *
	 * @param 	WP_Screen  $screen  The current screen instance.
	 *
	 * @return 	void
	 */
	public static function help($screen = null)
	{
		$app = JFactory::getApplication();

		// make sure we are in VikRentCar (back-end)
		if (!$app->isAdmin() || $app->input->get('page') != 'vikrentcar')
		{
			// abort
			return;
		}

		// make sure $screen is a valid instance
		if (!class_exists('WP_Screen') || !$screen instanceof WP_Screen)
		{
			if (VIKRENTCAR_DEBUG)
			{
				// trigger warning in case debug is enabled
				trigger_error('Method ' . __METHOD__ . ' has been called too early', E_USER_WARNING);
			}
			// abort
			return;
		}

		// extract view from request
		$view = $app->input->get('view', null);

		if (empty($view))
		{
			// no view, try to check 'task'
			$view = $app->input->get('task', 'dashboard');
		}

		// make sure the view is supported
		if (!isset(static::$lookup[$view]))
		{
			// view not supported
			return;
		}

		// check if we have a link to an existing item
		if (is_string(static::$lookup[$view]))
		{
			// use the linked element
			$view = static::$lookup[$view];
		}

		// check if the view documentation has been already cached
		$doc = get_transient('vikrentcar_screen_' . $view);

		if (!$doc)
		{
			// evaluate if we should stop using HELP tabs after 3 failed attempts
			$fail = (int) get_option('vikrentcar_screen_failed_attempts', 0);

			if ($fail >= 5)
			{
				// Do not proceed as we hit too many failure attempts contiguously.
				// Reset 'vikrentcar_screen_failed_attempts' option to restart using HELP tabs.
				return;
			}

			// create POST arguments
			$args = array(
				'documentation_alias' => 'vik-rent-car',
				'lang'                => substr(JFactory::getLanguage()->getTag(), 0, 2),
			);

			$args = array_merge($args, static::$lookup[$view]);

			$http = new JHttp();

			// make HTTP post
			$response = $http->post('https://vikwp.com/index.php?option=com_vikhelpdesk&format=json', $args);

			if ($response->code != 200)
			{
				// increase total number of failed attempts
				update_option('vikrentcar_screen_failed_attempts', $fail + 1);

				return;
			}

			// try to decode JSON
			$doc = json_decode($response->body);

			if (!is_array($doc))
			{
				// increase total number of failed attempts
				update_option('vikrentcar_screen_failed_attempts', $fail + 1);

				return;
			}

			// reset total number of failed attempts
			update_option('vikrentcar_screen_failed_attempts', 0);

			// cache retrieved documentation (for one week only)
			set_transient('vikrentcar_screen_' . $view, json_encode($doc), WEEK_IN_SECONDS);
		}
		else
		{
			// JSON decode the cached documentation
			$doc = json_decode($doc);
		}

		// iterate category sections
		foreach ($doc as $i => $cat)
		{
			// add subcategory as help tab
			$screen->add_help_tab(array(
				'id'       => 'vikrentcar-' . $view . '-' . ($i + 1),
				'title'    => $cat->contentTitle,
				'content'  => $cat->content,
			));
		}

		// add help sidebar
		$screen->set_help_sidebar(
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p><a href="https://vikwp.com/documentation/vik-rent-car/" target="_blank">VikWP.com</a></p>'
		);
	}

	/**
	 * Clears the cache for the specified view, if specified.
	 *
	 * @param 	string|null  $view  Clear the cache for the specified view (if specified)
	 * 								or for all the existing views.
	 *
	 * @return 	void
	 */
	public static function clearCache($view = null)
	{
		if ($view)
		{
			delete_transient('vikrentcar_screen_' . $view);
		}
		else
		{
			foreach (static::$lookup as $view => $args)
			{
				if (is_array($args))
				{
					delete_transient('vikrentcar_screen_' . $view);
				}
			}

			// delete settings too
			delete_option('vikrentcar_screen_failed_attempts');
			delete_option('vikrentcar_list_limit');
		}
	}

	/**
	 * Lookup used to retrieve the arguments for the HTTP request.
	 *
	 * @var array
	 */
	protected static $lookup = array(
		/**
		 * @todo define documentation lookup here
		 */	
	);
}
