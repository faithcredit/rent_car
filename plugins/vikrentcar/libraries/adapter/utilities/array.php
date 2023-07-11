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
 * Utility class for doing all sorts of odds and ends with arrays.
 *
 * @since 10.1.16
 */
final class ArrayHelper
{
	/**
	 * Private constructor to prevent instantiation of this class.
	 */
	private function __construct()
	{

	}

	/**
	 * Utility function to map an array to a string.
	 *
	 * @param   array    $array         The array to map.
	 * @param   string   $innerGlue     The glue (optional, defaults to '=') between the key and the value.
	 * @param   string   $outerGlue     The glue (optional, defaults to ' ') between array elements.
	 * @param   boolean  $keepOuterKey  True if final key should be kept.
	 *
	 * @return  string
	 */
	public static function toString(array $array, $innerGlue = '=', $outerGlue = ' ', $keepOuterKey = false)
	{
		$output = array();

		foreach ($array as $key => $item)
		{
			if (is_array($item))
			{
				if ($keepOuterKey)
				{
					$output[] = $key;
				}

				// This is value is an array, go and do it again!
				$output[] = static::toString($item, $innerGlue, $outerGlue, $keepOuterKey);
			}
			else
			{
				$output[] = $key . $innerGlue . '"' . htmlspecialchars($item, ENT_COMPAT, 'UTF-8') . '"';
			}
		}

		return implode($outerGlue, $output);
	}

	/**
	 * Method to determine if an array is an associative array.
	 *
	 * @param   array    $array  An array to test.
	 *
	 * @return  boolean
	 */
	public static function isAssociative($array)
	{
		if (is_array($array))
		{
			foreach (array_keys($array) as $k => $v)
			{
				if ($k !== $v)
				{
					return true;
				}
			}
		}

		return false;
	}
}
