<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	wordpress
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// do nothing if the class already exists
if (!class_exists('VersionListener'))
{
	/**
	 * Version recognizer class to identify which platform version is running.
	 *
	 * @see 	JVersion 	Used to identify the installed platform version.
	 *
	 * @since  	1.6
	 */
	class VersionListener
	{
		/**
		 * The platform name.
		 *
		 * @var   string
		 * @since 1.6.3
		 */
		private static $platform = null;

		/**
		 * The platform version.
		 *
		 * @var   string
		 * @since 1.6.3
		 */
		private static $version = null;

		/**
		 * The identifier of the platform version.
		 *
		 * @var integer
		 */
		private static $id = null;
		
		/**
		 * Class constructor.
		 */
		private function __construct()
		{
			// not accessible
		}

		/**
		 * Class cloner.
		 */
		private function __clone()
		{
			// not accessible
		}

		/**
		 * Returns the current platform name.
		 *
		 * @return 	string 	The platform name.
		 *
		 * @since 	1.6.3
		 */
		public static function getPlatform()
		{
			if (self::$platform === null)
			{
				if (defined('WPINC'))
				{
					// wordpress is installed
					self::$platform = 'wordpress';
				}
				else if (defined('_JEXEC'))
				{
					// joomla is installed
					self::$platform = 'joomla';
				}
				else
				{
					// platform not supported
					$self::$platform = false;
				}
			}

			return self::$platform;
		}

		/**
		 * Checks if the current platform is Wordpress.
		 *
		 * @return 	boolean
		 *
		 * @since 	1.6.3
		 */
		public static function isWordpress()
		{
			return self::getPlatform() === 'wordpress';
		}

		/**
		 * Checks if the current platform is Wordpress.
		 *
		 * @return 	boolean
		 *
		 * @since 	1.6.3
		 */
		public static function isJoomla()
		{
			return self::getPlatform() === 'joomla';
		}

		/**
		 * Returns the current platform version.
		 *
		 * @return 	string 	The platform version.
		 *
		 * @since 	1.6.3
		 */
		public static function getVersion()
		{
			if (self::$version === null)
			{
				$version = new JVersion();
				self::$version = $version->getShortVersion();
			}

			return self::$version;
		}
		
		/**
		 * Recognizes the Joomla version and return the respective indetifier.
		 *
		 * @return 	integer  The identifier of the Joomla version.
		 */
		public static function getID()
		{	
			if (self::$id === null)
			{
				// UNSUPPORTED flag will be overridden (if supported)
				self::$id = self::UNSUPPORTED;

				if (class_exists('JVersion'))
				{
					// get platform version
					$v = static::getVersion();

					if (static::isJoomla())
					{
						// get Joomla version identifier
						self::$id = static::getJoomlaID($v);
					}
					else if (static::isWordpress())
					{
						// get Wordpress version identifier
						self::$id = static::getWordpressID($v);
					}
				}
			}

			return self::$id;
		}

		/**
		 * Detects the current Wordpress version constant.
		 *
		 * @param 	string 	 $v  The platform short version.
		 *
		 * @return 	integer  The version constant.
		 *
		 * @since 	1.6.3
		 */
		protected static function getWordpressID($v)
		{
			if (version_compare($v, '4.0') >= 0 && version_compare($v, '5.0') < 0)
			{
				// wordpress 4.0
				return self::WP4;
			}
			else if (version_compare($v, '5.0') >= 0)
			{
				// wordpress 5.0
				return self::WP5;
			}
			
			// fallback to Wordpress 3.0 or lower
			return self::WP3;	
		}

		/**
		 * Detects the current Joomla! version constant.
		 *
		 * @param 	string 	 $v  The platform short version.
		 *
		 * @return 	integer  The version constant.
		 *
		 * @since 	1.6.3
		 */
		protected static function getJoomlaID($v)
		{
			if (version_compare($v, '2.5') >= 0 && version_compare($v, '3.0') < 0)
			{
				// joomla 2.5
				return self::J25;
			}
			else if (version_compare($v, '3.0') >= 0 && version_compare($v, '3.5') < 0)
			{
				// joomla 3.0, 3.1, 3.2, 3.3, 3.4
				return self::J30;
			}
			else if (version_compare($v, '3.5') >= 0 && version_compare($v, '3.7') < 0)
			{
				// joomla 3.5, 3.6
				return self::J35;
			}
			else if (version_compare($v, '3.7') >= 0 && version_compare($v, '4.0') < 0)
			{
				// joomla 3.7, 3.8, 3.9
				return self::J37;
			}
			else if (version_compare($v, '4.0') >= 0)
			{
				// joomla 4.0
				return self::J40;
			}
			
			// fallback to Joomla 1.5
			return self::J15;
		}

		/**
		 * Checks if the installed Joomla is 1.5 or 1.6.
		 *
		 * @return 	boolean  True if Joomla is 1.5 or 1.6, otherwise false.
		 */
		public static function isJoomla15()
		{
			return self::getID() == self::J15;
		}

		/**
		 * Checks if the installed Joomla is 2.5.
		 *
		 * @return 	boolean  True if Joomla is 2.5, otherwise false.
		 */
		public static function isJoomla25()
		{
			return self::getID() == self::J25;
		}

		/**
		 * Checks if the installed Joomla is between 3.0 and 3.4.
		 *
		 * @return 	boolean  True if Joomla is between 3.0 and 3.4, otherwise false.
		 */
		public static function isJoomla30()
		{
			return self::getID() == self::J30;
		}

		/**
		 * Checks if the installed Joomla is between 3.5 and 4.0 (excluded).
		 *
		 * @return 	boolean  True if Joomla is between 3.0 and 4.0 (excluded), otherwise false.
		 */
		public static function isJoomla35()
		{
			return self::getID() == self::J35;
		}

		/**
		 * Checks if the installed Joomla is 3.7 or higher.
		 *
		 * @return 	boolean  True if Joomla is 3.7 or higher, otherwise false.
		 */
		public static function isJoomla37()
		{
			return self::getID() == self::J37;
		}

		/**
		 * Checks if the installed Joomla is 4.0 or higher.
		 *
		 * @return 	boolean  True if Joomla is 4.0 or higher, otherwise false.
		 */
		public static function isJoomla40()
		{
			return self::getID() == self::J40;
		}

		/**
		 * Checks if the installed Wordpress is 4.0 or higher.
		 *
		 * @return 	boolean  True if Wordpress is 4.0 or higher, otherwise false.
		 */
		public static function isWordpress4()
		{
			return self::getID() == self::WP4;
		}

		/**
		 * Checks if the installed Wordpress is 5.0 or higher.
		 *
		 * @return 	boolean  True if Wordpress is 5.0 or higher, otherwise false.
		 */
		public static function isWordpress5()
		{
			return self::getID() == self::WP5;
		}

		/**
		 * Checks if the installed Joomla is supported.
		 * The Joomla version is not supported when the class is not able
		 * to recognize the installed version.
		 *
		 * @return 	boolean  True if the Joomla version is supported, otherwise false.
		 */
		public static function isSupported()
		{
			return self::getID() != self::UNSUPPORTED;
		}

		/**
		 * Check if the current Joomla version is higher than the provided one.
		 *
		 * @param 	mixed    The version to check.
		 * 					 - int     The platform ID will be compared;
		 * 					 - string  The platform version will be compared.
		 *
		 * @return 	boolean  True if the current version is higher, otherwise false.
		 */
		public static function isHigherThan($version)
		{
			if (is_int($version))
			{
				// compare platform ID only if an int was passed
				return self::getID() > $version;
			}

			// compare current version with provided version
			return version_compare(static::getVersion(), $version, '>');
		}

		/**
		 * Check if the current Joomla version is lower than the provided one.
		 *
		 * @param 	mixed    The version to check.
		 * 					 - int     The platform ID will be compared;
		 * 					 - string  The platform version will be compared.
		 *
		 * @return 	boolean  True if the current version is lower, otherwise false.
		 */
		public static function isLowerThan($version)
		{
			if (is_int($version))
			{
				return self::getID() < $version;
			}

			// compare current version with provided version
			return version_compare(static::getVersion(), $version, '<');
		}

		/**
		 * The UNSUPPORTED version identifier.
		 *
		 * @var integer
		 */
		const UNSUPPORTED = -1;

		/**
		 * The Joomla 1.5 version identifier.
		 *
		 * @var integer
		 */
		const J15 = 0;

		/**
		 * The Joomla 2.5 version identifier.
		 *
		 * @var integer
		 */
		const J25 = 1;

		/**
		 * The Joomla 3.0 to 3.4 version identifier.
		 *
		 * @var integer
		 */
		const J30 = 2;

		/**
		 * The Joomla 3.5 to 4.0 (excluded) version identifier.
		 *
		 * @var integer
		 */
		const J35 = 3;

		/**
		 * The Joomla 3.7 version identifier.
		 *
		 * @var integer
		 */
		const J37 = 4;

		/**
		 * The Joomla 4.0 version identifier.
		 *
		 * @var integer
		 */
		const J40 = 5;

		/**
		 * The Wordpress 3.0 version identifier.
		 *
		 * @var   integer
		 * @since 1.6.3
		 */
		const WP3 = 0;

		/**
		 * The Wordpress 4.0 version identifier.
		 *
		 * @var   integer
		 * @since 1.6.3
		 */
		const WP4 = 1;

		/**
		 * The Wordpress 5.0 version identifier.
		 *
		 * @var   integer
		 * @since 1.6.3
		 */
		const WP5 = 2;
	}
}
