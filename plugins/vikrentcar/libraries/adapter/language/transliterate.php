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
 * Class to transliterate strings.
 *
 * @since 10.0
 */
class Transliterate
{
	/**
	 * Returns strings transliterated from UTF-8 to Latin.
	 *
	 * @param   string   $string  String to transliterate.
	 * @param   integer  $case    Optionally specify upper or lower case. Default to null.
	 *
	 * @return  string  Transliterated string.
	 */
	public static function utf8_latin_to_ascii($string, $case = 0)
	{
		return sanitize_title($string);
	}
}
