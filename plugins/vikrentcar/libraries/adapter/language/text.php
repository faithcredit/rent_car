<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.language
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Text adapter class.
 *
 * @since 10.0
 */
class JText
{
	/**
	 * JavaScript strings store.
	 *
	 * @var    array
	 * @since  10.1.14
	 */
	protected static $strings = array();

	/**
	 * Translates a string into the current language.
	 *
	 * @param   string   $string                The string to translate.
	 * @param   boolean  $jsSafe                Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  True to interpret backslashes (\\=\, \n=carriage return, \t=tabulation).
	 *
	 * @return  string   The translated string.
	 */
	public static function _($string, $jsSafe = false, $interpretBackSlashes = true)
	{
		// get current language
		$lang = JFactory::getLanguage();

		// translate the string
		return $lang->_($string, $jsSafe, $interpretBackSlashes);
	}

	/**
	 * Proxy for underscore method.
	 * Needed to bypass the issue reported here:
	 * @link https://meta.trac.wordpress.org/ticket/3601 
	 *
	 * @since 10.1.32
	 */
	public static function translate($string, $jsSafe = false, $interpretBackSlashes = true)
	{
		return static::_($string, $jsSafe, $interpretBackSlashes);
	}

	/**
	 * Passes a string thru a sprintf.
	 * Note that this method can take a mixed number of arguments as for the sprintf function.
	 *
	 * The last argument can take an array of options:
	 * array('jsSafe'=>boolean, 'interpretBackSlashes'=>boolean)
	 *
	 * where:
	 *
	 * 'jsSafe' is a boolean to generate a javascript safe strings.
	 * 'interpretBackSlashes' is a boolean to interpret backslashes \\->\, \n->new line, \t->tabulation.
	 *
	 * @param 	string 	$string  The string to translate.
	 *
	 * @return 	string 	The translated strings.
	 */
	public static function sprintf($string)
	{
		$args = func_get_args();
		// pop the key to translate from the array 
		array_shift($args);

		// check if the last element is an array (to check for custom options)
		if (is_array(end($args)))
		{
			// pop the options from the args list
			$options = array_pop($args);

			// check for js safe
			$jsSafe = isset($options['jsSafe']) 
				? $options['jsSafe'] 
				: false;

			// check for interpret backslashes
			$interpretBackSlashes = isset($options['interpretBackSlashes'])
				? $options['interpretBackSlashes']
				: true;
		}
		// otherwise set default options
		else
		{
			$jsSafe = false;
			$interpretBackSlashes = true;
		}

		// get language
		$lang = JFactory::getLanguage();

		// translate the string
		$t = $lang->_($string, $jsSafe, $interpretBackSlashes);

		// replace custom named placeholders with sprintf style placeholders
		$t = preg_replace('/\[\[%([0-9]+):[^\]]*\]\]/', '%\1$s', $t);

		// push the translated string at the beginning of the args list
		array_unshift($args, $t);

		// invoke sprintf
		return call_user_func_array('sprintf', $args);
	}

	/**
	 * Like Text::sprintf but tries to pluralise the string.
	 *
	 * Note that this method can take a mixed number of arguments as for the sprintf function.
	 *
	 * The last argument can take an array of options:
	 *
	 * array('jsSafe'=>boolean, 'interpretBackSlashes'=>boolean, 'script'=>boolean)
	 *
	 * where:
	 *
	 * jsSafe is a boolean to generate a javascript safe strings.
	 * interpretBackSlashes is a boolean to interpret backslashes \\->\, \n->new line, \t->tabulation.
	 * script is a boolean to indicate that the string will be push in the javascript language store.
	 *
	 * Examples:
	 * `<script>alert(Joomla.JText._('<?php echo Text::plural("COM_PLUGINS_N_ITEMS_UNPUBLISHED", 1, array("script"=>true)); ?>'));</script>`
	 * will generate an alert message containing '1 plugin successfully disabled'
	 * `<?php echo Text::plural('COM_PLUGINS_N_ITEMS_UNPUBLISHED', 1); ?>` will generate a '1 plugin successfully disabled' string
	 *
	 * @param   string   $string  The format string.
	 * @param   integer  $n       The number of items.
	 *
	 * @return  string   The translated strings or the key if 'script' is true in the array of options.
	 *
	 * @since   10.1.19
	 */
	public static function plural($string, $n)
	{
		// obtain arguments list
		$args = func_get_args();

		// evaluate pluralization
		switch ((int) $n)
		{
			case '0':
				$string .= '_0';
				break;

			case '1':
				$string .= '_1';
				break;
		}

		/**
		 * Use plural string only if supported, otherwise rely on default string.
		 *
		 * @since 10.1.35
		 */
		if ($string !== $args[0] && JFactory::getLanguage()->hasKey($string))
		{
			// replace updated string
			$args[0] = $string;
		}

		// invoke JText::sprintf() to complete translation
		return call_user_func_array(array('JText', 'sprintf'), $args);
	}

	/**
	 * Translate a string into the current language and registers it in the JavaScript language store.
	 *
	 * @param   string   $string                The Text key.
	 * @param   boolean  $jsSafe                Ensure the output is JavaScript safe.
	 * @param   boolean  $interpretBackSlashes  True to interpret backslashes (\\=\, \n=carriage return, \t=tabulation).
	 *
	 * @return  array 	 The JavaScript language store.
	 *
	 * @uses 	_()
	 * @uses 	getScriptStrings()
	 *
	 * @since   10.1.14
	 */
	public static function script($string, $jsSafe = false, $interpretBackSlashes = true)
	{
		// normalize the key and translate the string.
		static::$strings[strtoupper($string)] = static::_($string, $jsSafe, $interpretBackSlashes);

		// Update Joomla.JText script options
		JFactory::getDocument()->addScriptOptions('joomla.jtext', static::$strings, $merge = false);

		return static::getScriptStrings();
	}

	/**
	 * Get the strings that have been loaded to the JavaScript language store.
	 *
	 * @return  array 	The JavaScript language store.
	 *
	 * @since   10.1.14
	 */
	public static function getScriptStrings()
	{
		return static::$strings;
	}
}
