<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	update
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class used to handle the software license.
 *
 * @since 1.0
 */
class VikRentCarLicense
{
	/**
	 * Gets the current License Key.
	 *
	 * @return 	string
	 */
	public static function getKey()
	{
		return get_option('vikrentcar_license_key', '');
	}

	/**
	 * Updates the current License Key.
	 *
	 * @param 	string 	$key
	 *
	 * @return 	void
	 */
	public static function setKey($key)
	{
		update_option('vikrentcar_license_key', (string) $key);
	}

	/**
	 * Gets the current License Expiration Timestamp.
	 *
	 * @return 	int
	 */
	public static function getExpirationDate()
	{
		return (int)get_option('vikrentcar_license_expdate', 0);
	}

	/**
	 * Updates the current License Expiration Timestamp.
	 *
	 * @param 	int 	$time
	 *
	 * @return 	void
	 */
	public static function setExpirationDate($time)
	{
		update_option('vikrentcar_license_expdate', (int) $time);
	}

	/**
	 * Checks whether the software version is Pro.
	 *
	 * @return 	boolean
	 */
	public static function isPro()
	{
		return (strlen(self::getKey()) && !self::isExpired());
	}

	/**
	 * Checks whether the License Key is expired.
	 *
	 * @return 	boolean
	 */
	public static function isExpired()
	{
		return self::getExpirationDate() < time();
	}

	/**
	 * Gets the current License Hash.
	 *
	 * @return 	string
	 */
	public static function getHash()
	{
		$hash = get_option('vikrentcar_license_hash', '');
		
		if (empty($hash))
		{
			$hash = self::setHash();
		}

		return $hash;
	}

	/**
	 * Sets and returns the License Hash.
	 *
	 * @return 	string
	 */
	public static function setHash()
	{
		$hash = md5(JUri::root() . uniqid());
		update_option('vikrentcar_license_hash', $hash);

		return $hash;
	}

	/**
	 * Registers some options upon installation of the plugin.
	 *
	 * @return 	void
	 */
	public static function install()
	{
		update_option('vikrentcar_license_key', '');
		update_option('vikrentcar_license_expdate', 0);
		update_option('vikrentcar_license_hash', '');
	}

	/**
	 * Deletes all the options upon uninstallation of the plugin.
	 *
	 * @return 	void
	 */
	public static function uninstall()
	{
		delete_option('vikrentcar_license_key');
		delete_option('vikrentcar_license_expdate');
		delete_option('vikrentcar_license_hash');
	}
}
