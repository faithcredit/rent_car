<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.input
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.input.input');

/**
 * This is an abstracted input class used to handle retrieving data 
 * from the COOKIE environment.
 *
 * @since 10.0
 *
 * @property-read    Input   $get
 * @property-read    Input   $post
 * @property-read    Input   $request
 * @property-read    Input   $server
 * @property-read    Files   $files
 * @property-read    Cookie  $cookie
 *
 * @method      integer  getInt($name, $default = null)       Get a signed integer.
 * @method      integer  getUint($name, $default = null)      Get an unsigned integer.
 * @method      float    getFloat($name, $default = null)     Get a floating-point number.
 * @method      boolean  getBool($name, $default = null)      Get a boolean value.
 * @method      string   getWord($name, $default = null)      Get a word.
 * @method      string   getAlnum($name, $default = null)     Get an alphanumeric string.
 * @method      string   getCmd($name, $default = null)       Get a CMD filtered string.
 * @method      string   getBase64($name, $default = null)    Get a base64 encoded string.
 * @method      string   getString($name, $default = null)    Get a string.
 * @method      string   getHtml($name, $default = null)      Get a HTML string.
 * @method      string   getPath($name, $default = null)      Get a file path.
 * @method      string   getUsername($name, $default = null)  Get a username.
 */
class JInputCookie extends JInput
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $source   Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   array  $options  An optional associative array of configuration parameters.
	 */
	public function __construct($source = null, array $options = array())
	{
		parent::__construct($_COOKIE, $options);
	}

	/**
	 * Sets a value
	 *
	 * @param   string   $name      Name of the value to set.
	 * @param   mixed    $value     Value to assign to the input.
	 * @param   array    $options   An associative array which may have any of the keys expires, path, domain,
	 *                              secure, httponly and samesite. The values have the same meaning as described
	 *                              for the parameters with the same name. The value of the samesite element
	 *                              should be either Lax or Strict. If any of the allowed options are not given,
	 *                              their default values are the same as the default values of the explicit
	 *                              parameters. If the samesite element is omitted, no SameSite cookie attribute
	 *                              is set.
	 *
	 * @return  void
	 *
	 * @link    https://www.ietf.org/rfc/rfc2109.txt
	 * @link    https://php.net/manual/en/function.setcookie.php
	 *
	 * @since 	10.1.30  Changed parameters signature.
	 */
	public function set($name, $value, $options = array())
	{
		// BC layer to convert old method parameters
		if (is_array($options) === false)
		{
			$argList = func_get_args();

			$options = array(
				'expires'  => isset($argList[2]) === true ? $argList[2] : 0,
				'path'     => isset($argList[3]) === true ? $argList[3] : '',
				'domain'   => isset($argList[4]) === true ? $argList[4] : '',
				'secure'   => isset($argList[5]) === true ? $argList[5] : false,
				'httponly' => isset($argList[6]) === true ? $argList[6] : false,
			);
		}

		if (!headers_sent())
		{
			// set the cookie
			if (version_compare(PHP_VERSION, '7.3', '>='))
			{
				setcookie($name, $value, $options);
			}
			else
			{
				// using the setcookie function before php 7.3, make sure we have default values
				if (array_key_exists('expires', $options) === false)
				{
					$options['expires'] = 0;
				}

				if (array_key_exists('path', $options) === false)
				{
					$options['path'] = '';
				}

				if (array_key_exists('domain', $options) === false)
				{
					$options['domain'] = '';
				}

				if (array_key_exists('secure', $options) === false)
				{
					$options['secure'] = false;
				}

				if (array_key_exists('httponly', $options) === false)
				{
					$options['httponly'] = false;
				}

				setcookie($name, $value, $options['expires'], $options['path'], $options['domain'], $options['secure'], $options['httponly']);
			}
		} 
		else
		{
			// headers already sent, register cookie via javascript
			$js = 'document.cookie="' . $name . '=' . $value
				. (!empty($options['expires']) ? '; expires=' . date('r', $expire) : '')
				. (!empty($options['path']) ? '; path=' . $path : '')
				. (!empty($options['samesite']) ? '; SameSite=' . $options['samesite'] : '')
				. (!empty($options['secure']) ? '; Secure' : '');

			// register script
			JFactory::getDocument()->addScriptDeclaration($js);
		}

		$this->data[$name] = $value;
	}
}
