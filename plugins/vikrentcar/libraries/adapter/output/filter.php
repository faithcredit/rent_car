<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.output
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Output filter for strings.
 *
 * @since 10.0
 */
class JFilterOutput
{
	/**
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents", whitespaces are replaced by hyphens and the string is lowercase.
	 *
	 * @param   string  $string    The string to process.
	 * @param   string  $language  The language to use for transliteration.
	 *
	 * @return  string  The processed string.
	 */
	public static function stringURLSafe($string, $language = '')
	{
		// remove any '-' from the string since they will be used as concatenaters
		$str = str_replace('-', ' ', $string);

		// transliterate on the language requested (fallback to current language if not specified)
		$lang 	= $language == '' || $language == '*' ? JFactory::getLanguage() : JLanguage::getInstance($language);
		$str 	= $lang->transliterate($str);

		// trim white spaces at beginning and end of alias and make lowercase
		$str = trim(mb_strtolower($str));

		// remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);

		// trim dashes at beginning and end of alias
		$str = trim($str, '-');

		return $str;
	}

	/**
	 * Helper method to generate a WP shortcode.
	 *
	 * @param 	string 	$component 	The shortcode name (component).
	 * @param 	array 	$attrs 		The shortcode args.
	 * @param 	string 	$content 	The shortcode contents.
	 */
	public static function shortcode($component, $attrs = array(), $content = null)
	{
		$shortcode = '';

		foreach ($attrs as $k => $v)
		{
			if (is_array($v))
			{
				$v = implode(',', $v);
			}

			if (is_scalar($v))
			{
				// encode single and double quotes
				$v = htmlentities($v, ENT_QUOTES);
				// encode left brackets
				$v = str_replace('[', '&#91;', $v);
				// encode right brackets
				$v = str_replace(']', '&#93;', $v);

				$shortcode .= " $k=\"$v\"";
			}
		}

		if ($content && is_string($content))
		{
			$v = $content;

			// encode single and double quotes
			$v = htmlentities($v, ENT_QUOTES);
			// encode left brackets
			$v = str_replace('[', '&#91;', $v);
			// encode right brackets
			$v = str_replace(']', '&#93;', $v);

			$shortcode = " \"$v\"";
		}

		return "[{$component}$shortcode]";
	}
}
