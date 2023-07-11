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
 * Utility class for all HTML drawing classes.
 *
 * @since 10.0
 */
abstract class JHtml
{
	/**
	 * A list of callback functions.
	 *
	 * @var Callable[]
	 */
	protected static $callbacks = array();

	/**
	 * A list of loaded classes.
	 *
	 * @var array
	 */
	protected static $loaded = array();

	/**
	 * An array to hold included paths.
	 *
	 * @var   array
	 * @since 10.1.29
	 */
	protected static $includePaths = array();

	/**
	 * The base folder containing the classes to load.
	 *
	 * @var string
	 */
	public static $base = '';

	/**
	 * Option values related to the generation of HTML output.
	 * Recognized options are:
	 *
	 * @property integer  fmtDepth   The current indent depth.
	 * @property string   fmtEol     The end of line string, default is linefeed.
	 * @property string   fmtIndent  The string to use for indentation, default is tab.
	 *
	 * @var    array
	 * @since  10.1.16
	 */
	public static $formatOptions = array(
		'format.depth' 	=> 0,
		'format.eol' 	=> "\n",
		'format.indent' => "\t",
	);

	/**
	 * Helper method to call a function using a specific notation:
	 * [prefix].[filename].[function]
	 *
	 * For example: JView.helper.doSomething will call JViewHelper::doSomething().
	 * In case the prefix is missing, it will be used the default one: JHtml.
	 * In case also the file is missing, the system will try to call a 
	 * function contained in the current class.
	 *
	 * @uses 	isRegistered()
	 * @uses 	extract()
	 * @uses 	loadFile()
	 * @uses 	register()
	 * @uses 	call()
	 */
	public static function _($key)
	{
		// check if the key already owns a callback
		if (!static::isRegistered($key))
		{
			// extract the data from the key
			list($key, $prefix, $file, $func) = static::extract($key);

			// the classname begins with the specified prefix (JHtml if not given)
			$classname = ucwords($prefix);

			// check if the file is set
			if ($file)
			{
				// prepend the filename to the classname and load the file
				$classname .= ucwords($file);

				static::loadFile($file);
			}

			// once the file is loaded, we need to register the callback
			static::register($key, array($classname, $func));
		}

		// obtain the list of specified arguments
		$args = func_get_args();
		// remove the $key from the arguments list
		array_shift($args);

		// dispatch the callback
		return static::call($key, $args);
	}

	/**
	 * Proxy for underscore method.
	 * Needed to bypass the issue reported here:
	 * @link https://meta.trac.wordpress.org/ticket/3601
	 *
	 * @since 10.1.32  Renamed from `u`.
	 */
	public static function fetch($key)
	{
		return call_user_func_array(array('JHtml', '_'), func_get_args());
	}

	/**
	 * Checks if there is a callback registered to the specified key.
	 *
	 * @param 	string 	 $key 	The key to check for.
	 *
	 * @return 	boolean  True if there is a callback, otherwise false. 	
	 */
	public static function isRegistered($key)
	{
		return isset(static::$callbacks[$key]);
	}

	/**
	 * Method used to extract the prefix, the file and the function to call
	 * from a specified key (in dot notation).
	 *
	 * @param 	string 	$key 	The key describing the method to launch.
	 *
	 * @return 	array 	The array containing the prefix, the file and the function.
	 */
	public static function extract($key)
	{
		// strip unexpected characters
		$key = preg_replace("/[^a-z0-9_.]+/i", '', $key);

		// check to see whether we need to load a helper file
		$parts = explode('.', $key);

		$prefix = count($parts) === 3 ? array_shift($parts) : 'JHtml';
		$file   = count($parts) === 2 ? array_shift($parts) : '';
		$func   = array_shift($parts);

		return array(strtolower($prefix . '.' . $file . '.' . $func), $prefix, $file, $func);
	}

	/**
	 * Add a directory where JHtml should search for helpers.
	 * You may either pass a string or an array of directories.
	 *
	 * @param   string  $path  A path to search.
	 *
	 * @return  array   An array with directory elements.
	 *
	 * @since   10.1.29
	 */
	public static function addIncludePath($path = '')
	{
		// loop through the path directories
		foreach ((array) $path as $dir)
		{
			if (!empty($dir) && !in_array($dir, static::$includePaths))
			{
				array_unshift(static::$includePaths, JPath::clean($dir));
			}
		}

		return static::$includePaths;
	}

	/**
	 * Tries to load the given filename.
	 *
	 * @param 	string 	$file 	The file to load.
	 *
	 * @return 	void
	 */
	public static function loadFile($file)
	{
		// search on default paths
		$base = empty(static::$base) ? dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' : $base;

		// merge base path with include paths
		$paths = array_merge(array($base), static::addIncludePath());

		$found = false;

		/**
		 * Since different plugins might include files with
		 * the same name, it is needed to scan and load all
		 * the files with the matching name. 
		 *
		 * @since 10.1.30
		 */
		foreach ($paths as $path)
		{
			// create path
			$path = JPath::clean($path . DIRECTORY_SEPARATOR . $file . '.php');

			// check whether we already loaded this file
			if (!isset(static::$loaded[$path]))
			{
				// make sure the file exists
				if (is_file($path))
				{
					// cache path as loaded
					static::$loaded[$path] = 1;

					// require the file
					$found = (bool) require_once $path;
				}
			}
			else
			{
				// already loaded
				$found = true;
			}
		}

		if (!$found)
		{
			// file not found, raise error
			throw new Exception("JHtml [" . $file . "] helper class not found.", 404);
		}
	}

	/**
	 * Registers a callback for the given key.
	 *
	 * @param 	string 			The key used to trigger the callback.
	 * @param 	Callable|array 	The callback function to run.
	 */
	public static function register($key, $callback)
	{
		if (!is_callable($callback))
		{
			if (!is_array($callback))
			{
				$callback = array($callback);
			}

			throw new Exception('The callback [' . implode('::', $callback) . '()] is not callable.', 500);
		}

		static::$callbacks[$key] = $callback;
	}

	/**
	 * Executes the callback assigned to the specified key.
	 *
	 * @param 	string 	$key 	The callback key.
	 * @param 	array 	$args 	A list of arguments to pass.
	 *
	 * @param 	mixed 	The value returned by the callback.
	 *
	 * @uses 	isRegistered()
	 */
	public static function call($key, array &$args = array())
	{
		if (!static::isRegistered($key))
		{
			return null;
		}

		return call_user_func_array(static::$callbacks[$key], $args);
	}

	/**
	 * Includes the specified script URI in the document <head>.
	 *
	 * @param 	string 	$uri 	  The script URI.
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9').
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'scriptid', 'async' => 'async', 'data-test' => 1).
	 *
	 * @return 	void
	 */
	public static function script($uri, $options = array(), $attribs = array())
	{
		$document = JFactory::getDocument();

		// in case we don't have an array of options, create it
		if (!is_array($options))
		{
			$options = array();
		}

		// in case we don't have an array of attributes, create it
		if (!is_array($attribs))
		{
			$attribs = array();
		}

		// check if the script should be loaded as dependency
		if (preg_match("/jquery-ui[0-9.\-]*\.min\.js$/", $uri))
		{
			$attribs['id'] = 'jquery-ui-datepicker';
			// load datepicker jQuery UI add-on
			$document->addScript(null, $options, $attribs);

			$attribs['id'] = 'jquery-ui-dialog';
			// load dialog jQuery UI add-on
			$document->addScript(null, $options, $attribs);

			if (JFactory::getApplication()->isSite())
			{
				$attribs['id'] = 'jquery-ui-tooltip';
				// load tooltip jQuery UI add-on (site only)
				$document->addScript(null, $options, $attribs);
			}
		}
		else if (preg_match("/jquery[0-9.\-]*\.min\.js$/", $uri))
		{
			// do nothing, jQuery core is always loaded
		}
		else if (preg_match("/jquery-ui\.sortable\.min\.js$/", $uri))
		{
			$attribs['id'] = 'jquery-ui-sortable';
			// load jQuery Sortable add-on
			$document->addScript(null, $options, $attribs);
		}
		else if (preg_match("/jquery-ui\.slider\.min\.js$/", $uri))
		{
			$attribs['id'] = 'jquery-ui-slider';
			// load jQuery Slider add-on
			$document->addScript(null, $options, $attribs);
		}
		else
		{
			if (empty($attribs['id']) && preg_match("/maps\.googleapis\.com/", $uri))
			{
				$attribs['id'] = 'gmaps';
			}

			if (empty($attribs['id']))
			{
				/**
				 * Implemented lookup to avoid generating the same ID
				 * for files with the same name but placed in different
				 * positions for the plugin (e.g. site/admin).
				 *
				 * @since 10.1.30
				 */
				static $lookup = array();

				// extract last folder and file name from URI
				if (preg_match("/([^\/\\\\]+)[\/\\\\]([^\/\\\\]+)\.js$/i", $uri, $match))
				{
					// make ID as unique as possible and strip unsupported chars
					$attribs['id'] = preg_replace("/[^a-z0-9_-]/i", '', $match[1] . '-' . $match[2]);
				}
				else
				{
					// use uniq ID otherwise
					$attribs['id'] = uniqid();
				}

				$cont = 1;

				// iterate as long as the ID has been already registered
				// and belongs to a different source URI
				do
				{
					// create temporary ID
					$tmp = $attribs['id'];

					if ($cont > 1)
					{
						// append counter from 2 on
						$tmp .= '-' . $cont;
					}

					// increase counter
					$cont++;

				} while (isset($lookup[$tmp]) && $lookup[$tmp] != $uri);

				// re-assign ID
				$attribs['id'] = $tmp;

				// register ID within the cache lookup
				$lookup[$attribs['id']] = $uri;
			}

			// load the custom script
			$document->addScript($uri, $options, $attribs);
		}
	}

	/**
	 * Includes the specified stylesheet URI in the document <head>.
	 *
	 * @param 	string 	$uri 	The style URI.
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9').
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'scriptid', 'async' => 'async', 'data-test' => 1).
	 *
	 * @return 	void
	 */
	public static function stylesheet($uri, $options = array(), $attribs = array())
	{
		if (empty($attribs['id']))
		{
			/**
			 * Implemented lookup to avoid generating the same ID
			 * for files with the same name but placed in different
			 * positions for the plugin (e.g. site/admin).
			 *
			 * @since 10.1.30
			 */
			static $lookup = array();

			// use file basename as ID if not specified
			$attribs['id'] = basename($uri);

			// normalize ID attribute
			$attribs['id'] = preg_replace("/[^a-zA-Z0-9\-_]+/", '-', $attribs['id']);

			$cont = 1;

			// iterate as long as the ID has been already registered
			// and belongs to a different source URI
			do
			{
				// create temporary ID
				$tmp = $attribs['id'];

				if ($cont > 1)
				{
					// append counter from 2 on
					$tmp .= '-' . $cont;
				}

				// increase counter
				$cont++;

			} while (isset($lookup[$tmp]) && $lookup[$tmp] != $uri);

			// re-assign ID
			$attribs['id'] = $tmp;

			// register ID within the cache lookup
			$lookup[$attribs['id']] = $uri;
		}

		/**
		 * Added support for options and attributes.
		 *
		 * @since 10.1.27
		 */
		JFactory::getDocument()->addStyleSheet($uri, $options, $attribs);
	}

	/**
	 * Displays a calendar control field.
	 *
	 * @param   mixed   $value    The date value.
	 * @param   string  $name     The name of the text field.
	 * @param   string  $id       The id of the text field.
	 * @param   string  $format   The date format.
	 * @param   mixed   $attribs  Additional HTML attributes.
	 *                            The array can have the following keys:
	 *                            readonly      Sets the readonly parameter for the input tag.
	 *                            disabled      Sets the disabled parameter for the input tag.
	 *                            autofocus     Sets the autofocus parameter for the input tag.
	 *                            autocomplete  Sets the autocomplete parameter for the input tag.
	 *                            filter        Sets the filter for the input tag.
	 *
	 * @return  string  HTML markup for a calendar field.
	 */
	public static function calendar($value, $name, $id, $format = 'Y-m-d', $attribs = array())
	{
		$class    = isset($attribs['class']) ? $attribs['class'] : '';
		$showTime = isset($attribs['showTime']) ? $attribs['showTime'] : false;

		unset($attribs['name']);
		unset($attribs['id']);
		unset($attribs['value']);
		unset($attribs['class']);
		unset($attribs['showTime']);

		$str = '';
		foreach ($attribs as $k => $v)
		{
			if (!strlen($v) || $v === true)
			{
				$v = $k;
			}

			$str .= $k . '="' . str_replace('"', '&quot;', $v) . '" ';
		}

		// make sure the format is compatible with the jQuery UI Datepicker
		$format = str_replace('%', '', $format);

		/**
		 * Add support for timestamp and JDate instances.
		 * 
		 * @since 10.1.16
		 */
		if (preg_match("/^\d+$/", $value))
		{
			// fetch timestamp
			$value = date($format, $value);
		}
		else if ($value instanceof JDate)
		{
			/**
			 * Format string from JDate.
			 *
			 * @since 10.1.35  Always adjust to local timezone.
			 */
			$value = $value->format($format, $locale = true);
		}

		$data = array();
		$data['value'] 	  = $value;
		$data['name']	  = $name;
		$data['id'] 	  = $id;
		$data['class']    = $class;
		$data['format']	  = $format;
		$data['attr']	  = $str;
		$data['showTime'] = $showTime;

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {

	var sel_format = "$format";
	var df_separator = sel_format[1];

	sel_format = sel_format.replace(new RegExp("\\\\"+df_separator, 'g'), "");

	if (sel_format.match(/^Ymd/)) {
		sel_format = "yy" + df_separator + "mm" + df_separator + "dd";
	} else if (sel_format.match(/^mdY/)) {
		sel_format = "mm" + df_separator + "dd" + df_separator + "yy";
	} else {
		sel_format = "dd" + df_separator + "mm" + df_separator + "yy";
	}

	jQuery('input[name="$name"]:input').datepicker({
		dateFormat: sel_format,
	});

	jQuery('input[name="$name"]:input').next('i').click(function() {
		jQuery('input[name="$name"]:input').focus();
	});
});
JS
		);

		/**
		 * Render calendar layout.
		 * @note: this method should be registered by the plugin
		 * in order to use a custom layout.
		 */
		return static::fetch('renderCalendar', $data);
	}

	/**
	 * Returns formatted date according to a given format and time zone.
	 *
	 * @param   string   $input      String in a format accepted by date(), defaults to "now".
	 * @param   string   $format     The date format specification string (see {@link PHP_MANUAL#date}).
	 * @param   mixed    $tz         Time zone to be used for the date.  Special cases: boolean true for global
	 *                               setting, boolean false for server setting.
	 *
	 * @return  string   A date translated by the given format and time zone.
	 */
	public static function date($input = 'now', $format = null, $tz = true)
	{
		// UTC date converted to user time zone.
		if ($tz === true)
		{
			// get a date object based on UTC
			$date = JFactory::getDate($input, 'UTC');

			// get global timezone setting
			$global_tz = JFactory::getApplication()->get('offset', 'UTC');

			// set the correct time zone based on the global configuration
			$date->setTimezone(new DateTimeZone($global_tz));
		}
		// no date conversion (server timezone)
		else if ($tz === null)
		{
			$date = JFactory::getDate($input);
		}
		// UTC date converted to given time zone.
		else
		{
			// get a date object based on UTC.
			$date = JFactory::getDate($input, 'UTC');

			// set the correct time zone based on the specified string
			$date->setTimezone(new DateTimeZone($tz));
		}

		// if no format is given use the default one
		if (!$format)
		{
			$format = 'Y-m-d H:i:s';
		}
		/**
		 * Check whether the supplied format is a language definition.
		 *
		 * @since 10.1.35
		 */
		else if (JFactory::getLanguage()->hasKey($format))
		{
			$format = JText::_($format);
		}

		return $date->format($format, true);	
	}
}
