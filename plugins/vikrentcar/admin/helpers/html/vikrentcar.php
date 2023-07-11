<?php
/** 
 * @package     VikRentCar
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * VikRentCar HTML main helper.
 *
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
abstract class JHtmlVikrentcar
{
	/**
	 * Calculates the maximum upload file size and returns string with unit or the size in bytes.
	 *
	 * @param   bool          $unitOutput  This parameter determines whether the return value
	 *                                     should be a string with a unit.
	 *
	 * @return  float|string  The maximum upload size of files with the appropriate unit or in bytes.
	 */
	public static function maxuploadsize($unitOutput = true)
	{
		static $max_size = false;
		
		if ($max_size === false)
		{
			$max_size   = self::parseSize(ini_get('post_max_size'));
			$upload_max = self::parseSize(ini_get('upload_max_filesize'));

			// check what is the highest value between post and upload max sizes
			if ($upload_max > 0 && ($upload_max < $max_size || $max_size == 0))
			{
				$max_size = $upload_max;
			}
		}

		if (!$unitOutput)
		{
			// return numerical max size
			return $max_size;
		}

		// format max size
		return JHtml::_('number.bytes', $max_size, 'auto', 0);
	}

	/**
	 * Returns the size in bytes without the unit for the comparison.
	 *
	 * @param   string  $size  The size which is received from the PHP settings.
	 *
	 * @return  float   The size in bytes without the unit.
	 */
	private static function parseSize($size)
	{
		// extract the size unit
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
		// take only the size numbers
		$size = preg_replace('/[^0-9\.]/', '', $size);

		$return = round($size);

		if ($unit)
		{
			// calculate the correct size according to the specified unit
			$return = round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		}

		return $return;
	}
}
