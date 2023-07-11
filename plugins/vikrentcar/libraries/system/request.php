<?php
/** 
 * @package   	VikWP - Libraries
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

defined('VIKREQUEST_ALLOWRAW') or define('VIKREQUEST_ALLOWRAW', 2);
defined('VIKREQUEST_ALLOWHTML') or define('VIKREQUEST_ALLOWHTML', 4);

if (!class_exists('VikRequest')) {
	/**
	 * Request helper class.
	 *
	 * @since 	June 2021
	 * @see 	JInput
	 */
	abstract class VikRequest
	{
		/**
		 * Fetches and returns a given variable.
		 *
		 * The default behaviour is fetching variables depending on the
		 * current request method: GET and HEAD will result in returning
		 * an entry from $_GET, POST and PUT will result in returning an
		 * entry from $_POST.
		 *
		 * You can force the source by setting the $hash parameter:
		 *
		 * post    $_POST
		 * get     $_GET
		 * files   $_FILES
		 * cookie  $_COOKIE
		 * env     $_ENV
		 * server  $_SERVER
		 * method  via current $_SERVER['REQUEST_METHOD']
		 * default $_REQUEST
		 *
		 * @param   string   $name     	Variable name.
		 * @param   string   $default  	Default value if the variable does not exist.
		 * @param   string   $hash     	Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
		 * @param   string   $type     	The return type for the variable:
		 * 								INT:		An integer, or an array of integers;
		 *                           	UINT:		An unsigned integer, or an array of unsigned integers;
		 *                           	FLOAT:		A floating point number, or an array of floating point numbers;
		 *                           	BOOLEAN:	A boolean value;
		 *                           	WORD:		A string containing A-Z or underscores only (not case sensitive);
		 *                           	ALNUM:		A string containing A-Z or 0-9 only (not case sensitive);
		 *                           	CMD:		A string containing A-Z, 0-9, underscores, periods or hyphens (not case sensitive);
		 *                           	BASE64:		A string containing A-Z, 0-9, forward slashes, plus or equals (not case sensitive);
		 *                           	STRING:		A fully decoded and sanitised string (default);
		 *                           	HTML:		A sanitised string;
		 *                           	ARRAY:		An array;
		 *                           	PATH:		A sanitised file path, or an array of sanitised file paths;
		 *                           	TRIM:		A string trimmed from normal, non-breaking and multibyte spaces;
		 *                           	USERNAME:	Do not use (use an application specific filter);
		 *                           	RAW:		The raw string is returned with no filtering;
		 *                           	unknown:	An unknown filter will act like STRING. If the input is an array it will return an
		 *                              			array of fully decoded and sanitised strings.
		 * @param   integer  $mask 		Filter mask for the variable.
		 *
		 * @return  mixed  	 Requested variable.
		 */
		public static function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
		{
			$input = &JFactory::getApplication()->input;

			// ensure hash is uppercase
			$hash = strtoupper($hash);

			if ($hash === 'METHOD')
			{
				$hash = strtoupper($input->server->get('REQUEST_METHOD'));
			}

			// get the input hash
			switch ($hash)
			{
				case 'GET':
					$input = &$input->get;
					break;

				case 'POST':
					$input = &$input->post;
					break;

				case 'REQUEST':
					$input = &$input->request;
					break;

				case 'FILES':
					$input = &$input->files;
					break;

				case 'COOKIE':
					$input = &$input->cookie;
					break;

				case 'SERVER':
					$input = &$input->server;
					break;

				default:
					// do not alter default source
			}

			if ($mask == VIKREQUEST_ALLOWRAW || $mask == VIKREQUEST_ALLOWHTML)
			{
				// set type to obtain the raw value.
				$type = 'raw';
			}

			if ($hash === 'FILES')
			{
				/**
				 * Adapter for multi-file upload to keep the PHP native structure.
				 * 
				 * @since 	10.1.16
				 */
				$arr = $input->get($name, $default, $type);
				if (count($arr) && isset($arr[0]))
				{
					// re-arrange the array like before for code compatibility
					/*
					Array
					(
					    [name] => Array
					        (
					            [0] => x.png
					            [1] => y.jpg
					        )
					    [type] => Array
					        (
					            [0] => image/png
					            [1] => image/jpeg
					        )
					)
					*/
					$legacy_map = array();
					foreach ($arr as $ak => $av)
					{
						foreach ($av as $updk => $updv)
						{
							if (!isset($legacy_map[$updk]))
							{
								$legacy_map[$updk] = array();
							}
							$legacy_map[$updk][] = $updv;
						}
					}
					return $legacy_map;
				}
			}

			$value = $input->get($name, $default, $type);

			if ($mask == VIKREQUEST_ALLOWHTML) {
				// html will be sanitized recursively
				self::filterHtml($value);
			}

			return $value;
		}

		/**
		 * Fetches and returns a given filtered variable. The integer
		 * filter will allow only digits and the - sign to be returned. This is currently
		 * only a proxy function for getVar().
		 *
		 * @param   string   $name     Variable name.
		 * @param   string   $default  Default value if the variable does not exist.
		 * @param   string   $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
		 *
		 * @return  integer  Requested variable.
		 */
		public static function getInt($name, $default = 0, $hash = 'default')
		{
			return self::getVar($name, (int) $default, $hash, 'int');
		}

		/**
		 * Fetches and returns a given filtered variable. The unsigned integer
		 * filter will allow only digits to be returned. This is currently
		 * only a proxy function for getVar().
		 *
		 * @param   string   $name     Variable name.
		 * @param   string   $default  Default value if the variable does not exist.
		 * @param   string   $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
		 *
		 * @return  integer  Requested variable.
		 */
		public static function getUInt($name, $default = 0, $hash = 'default')
		{
			return self::getVar($name, abs((int) $default), $hash, 'uint');
		}

		/**
		 * Fetches and returns a given filtered variable.  The float
		 * filter only allows digits and periods.  This is currently
		 * only a proxy function for getVar().
		 *
		 * @param   string  $name     Variable name.
		 * @param   string  $default  Default value if the variable does not exist.
		 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
		 *
		 * @return  float  	Requested variable.
		 */
		public static function getFloat($name, $default = 0.0, $hash = 'default')
		{
			return self::getVar($name, (float) $default, $hash, 'float');
		}

		/**
		 * Fetches and returns a given filtered variable. The bool
		 * filter will only return true/false bool values. This is
		 * currently only a proxy function for getVar().
		 *
		 * @param   string   $name     Variable name.
		 * @param   string   $default  Default value if the variable does not exist.
		 * @param   string   $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
		 *
		 * @return  boolean  Requested variable.
		 */
		public static function getBool($name, $default = false, $hash = 'default')
		{
			return self::getVar($name, (bool) $default, $hash, 'bool');
		}

		/**
		 * Fetches and returns a given filtered variable. The word
		 * filter only allows the characters [A-Za-z_]. This is currently
		 * only a proxy function for getVar().
		 *
		 * @param   string  $name     Variable name.
		 * @param   string  $default  Default value if the variable does not exist.
		 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
		 *
		 * @return  string  Requested variable.
		 */
		public static function getWord($name, $default = '', $hash = 'default')
		{
			return self::getVar($name, $default, $hash, 'word');
		}

		/**
		 * Cmd (Word and Integer) filter.
		 *
		 * Fetches and returns a given filtered variable. The cmd
		 * filter only allows the characters [A-Za-z0-9.-_]. This is
		 * currently only a proxy function for getVar().
		 *
		 * @param   string  $name     Variable name
		 * @param   string  $default  Default value if the variable does not exist
		 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
		 *
		 * @return  string  Requested variable
		 */
		public static function getCmd($name, $default = '', $hash = 'default')
		{
			return self::getVar($name, $default, $hash, 'cmd');
		}

		/**
		 * Fetches and returns a given filtered variable. The string
		 * filter deletes 'bad' HTML code, if not overridden by the mask.
		 * This is currently only a proxy function for getVar().
		 *
		 * @param   string   $name     Variable name
		 * @param   string   $default  Default value if the variable does not exist
		 * @param   string   $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
		 * @param   integer  $mask     Filter mask for the variable
		 *
		 * @return  string   Requested variable
		 */
		public static function getString($name, $default = '', $hash = 'default', $mask = 0)
		{
			return self::getVar($name, $default, $hash, 'string', $mask);
		}

		/**
		 * Set a variable in one of the request variables.
		 *
		 * @param   string   $name       Name
		 * @param   string   $value      Value
		 * @param   string   $hash       Hash
		 * @param   boolean  $overwrite  Boolean
		 *
		 * @return  string   Previous value.
		 */
		public static function setVar($name, $value = null, $hash = 'default', $overwrite = true)
		{
			$input = &JFactory::getApplication()->input;

			// ensure hash is uppercase
			$hash = strtoupper($hash);

			if ($hash === 'METHOD')
			{
				$hash = strtoupper($input->server->get('REQUEST_METHOD'));
			}

			// get the input hash
			switch ($hash)
			{
				case 'GET':
					$input = &$input->get;
					break;

				case 'POST':
					$input = &$input->post;
					break;

				case 'REQUEST':
					$input = &$input->request;
					break;

				case 'FILES':
					$input = &$input->files;
					break;

				case 'COOKIE':
					$input = &$input->cookie;
					break;

				case 'SERVER':
					$input = &$input->server;
					break;

				default:
					// do not alter default source
			}

			$prev = $input->get($name, null, 'raw');

			// if overwrite is false, make sure the variable hasn't been set yet
			if ($overwrite || $prev === null)
			{
				$input->set($name, $value);	
			}

			return $prev;
		}

		/**
		 * Adapter method to safely send a cookie to the browser depending on the current PHP version,
		 * by also supporting the old function's signature before PHP 7.3 that will be adjusted:
		 * (name, value, expire, path, domain, secure, httpOnly)
		 * 
		 * @param   string   $name      The name of the value to set for the cookie.
		 * @param   mixed    $value     The value to assign to the cookie.
		 * @param   mixed    $options   An associative array which may have any of the keys expires, path, domain,
		 * 								secure, httponly and samesite. The values have the same meaning as described
		 * 								for the parameters with the same name. The value of the samesite element should
		 * 								be either None, Lax or Strict. 
		 * 								If the samesite element is omitted, SameSite cookie attribute will default
		 * 								to Lax. If the current PHP version supports this element the new signature will
		 * 								be used, otherwise we will use the headers to set the Lax cookie in the browser.
		 * @return 	void
		 * 
		 * @since 	10.1.30
		 */
		public static function setCookie($name, $value, $options = array())
		{
			// BC layer to convert old method parameters
			if (!is_array($options))
			{
				$argList = func_get_args();

				$options = array(
					'expires'  => isset($argList[2]) ? $argList[2] : 0,
					'path'     => isset($argList[3]) ? $argList[3] : '',
					'domain'   => isset($argList[4]) ? $argList[4] : '',
					'secure'   => isset($argList[5]) ? $argList[5] : false,
					'httponly' => isset($argList[6]) ? $argList[6] : false,
				);
			}

			// we make the samesite element default to Lax if no value given
			if (!isset($options['samesite']))
			{
				// Mozilla is going to deprecate/penalise the use of SameSite = None,
				// which is used by default if no element is set for samesite
				$options['samesite'] = 'Lax';
			}

			// samesite attribute validation
			$samesite_types = array(
				'None',
				'Lax',
				'Strict',
			);

			if (!empty($options['samesite']) && !in_array((string) $options['samesite'], $samesite_types))
			{
				// default to Lax after validation
				$options['samesite'] = 'Lax';
			}

			// set the cookie
			if (version_compare(PHP_VERSION, '7.3', '>='))
			{
				// Most recent PHP versions will always pass the attribute samesite for the cookie.
				// This is the new function's signature to ensure cookies will not be rejected.
				@setcookie($name, $value, $options);
			}
			else
			{
				// using the setcookie function on PHP < 7.3, make sure we have the default values
				if (!isset($options['expires']))
				{
					$options['expires'] = 0;
				}

				if (!isset($options['path']))
				{
					$options['path'] = '';
				}

				if (!isset($options['domain']))
				{
					$options['domain'] = '';
				}

				if (!isset($options['secure']))
				{
					$options['secure'] = false;
				}

				if (!isset($options['httponly']))
				{
					$options['httponly'] = false;
				}

				if (!headers_sent())
				{
					// we use the headers to send the cookie to the browser to support the samesite attribute
					header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
						. ($options['expires'] ? '; expires=' . gmdate('D, d-M-Y H:i:s', $options['expires']) . ' GMT' : '')
						. ($options['path'] ? '; path=' . $options['path'] : '')
						. ($options['domain'] ? '; domain=' . $options['domain'] : '')
						. ($options['secure'] ? '; secure' : '')
						. ($options['httponly'] ? '; HttpOnly' : '')
						. ($options['samesite'] ? '; SameSite=' . $options['samesite'] : '')
					, false);
				}
			}
		}

		/**
		 * Applies sanitification recursively to get clean HTML contents.
		 * 
		 * @param 	mixed 	$value 	reference to array or string to be sanitized.
		 * 
		 * @return 	mixed 			sanitized array or string.
		 * 
		 * @since 	June 2021
		 */
		public static function filterHtml(&$value)
		{
			if (is_array($value))
			{
				foreach ($value as $k => $content)
				{
					self::filterHtml($value[$k]);
				}
			}
			else
			{
				$value = JComponentHelper::filterText($value);
			}
		}
	}
}
