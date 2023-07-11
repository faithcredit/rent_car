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

/**
 * Class used for filtering input from any data source.
 *
 * @since 10.0
 */
class JInputFilter
{
	/**
	 * Creates a new instance.
	 *
	 * @return 	self
	 *
	 * @since 	10.1.23
	 */
	public static function getInstance()
	{
		return new static();
	}

	/**
	 * Method used to strip bad code from the specified source.
	 *
	 * @param   mixed   $source  Input string/array-of-string to be 'cleaned'.
	 * @param   string  $type    The return type for the variable:
	 *                           INT:       An integer, or an array of integers;
	 *                           UINT:      An unsigned integer, or an array of unsigned integers;
	 *                           FLOAT:     A floating point number, or an array of floating point numbers;
	 *                           BOOLEAN:   A boolean value;
	 *                           WORD:      A string containing A-Z or underscores only (not case sensitive);
	 *                           ALNUM:     A string containing A-Z or 0-9 only (not case sensitive);
	 *                           CMD:       A string containing A-Z, 0-9, underscores, periods or hyphens (not case sensitive);
	 *                           BASE64:    A string containing A-Z, 0-9, forward slashes, plus or equals (not case sensitive);
	 *                           STRING:    A fully decoded and sanitised string (default);
	 *                           HTML:      A sanitised string;
	 *                           ARRAY:     An array;
	 *                           PATH:      A sanitised file path, or an array of sanitised file paths;
	 *                           TRIM:      A string trimmed from normal, non-breaking and multibyte spaces;
	 *                           USERNAME:  Do not use (use an application specific filter);
	 *                           RAW:       The raw string is returned with no filtering;
	 *                           unknown:   An unknown filter will act like STRING. If the input is an array it will return an
	 *                                      array of fully decoded and sanitised strings.
	 *
	 * @return  mixed 	'Cleaned' version of input parameter.
	 */
	public function clean($source, $type = 'string')
	{
		// handle the type constraint cases
		switch (strtoupper($type))
		{
			case 'INT':
			case 'INTEGER':
				$pattern = '/[-+]?[0-9]+/';

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						preg_match($pattern, (string) $eachString, $matches);
						$result[] = isset($matches[0]) ? (int) $matches[0] : 0;
					}
				}
				else
				{
					preg_match($pattern, (string) $source, $matches);
					$result = isset($matches[0]) ? (int) $matches[0] : 0;
				}

				break;

			case 'UINT':
				$pattern = '/[-+]?[0-9]+/';

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						preg_match($pattern, (string) $eachString, $matches);
						$result[] = isset($matches[0]) ? abs((int) $matches[0]) : 0;
					}
				}
				else
				{
					preg_match($pattern, (string) $source, $matches);
					$result = isset($matches[0]) ? abs((int) $matches[0]) : 0;
				}

				break;

			case 'FLOAT':
			case 'DOUBLE':
				$pattern = '/[-+]?[0-9]+(\.[0-9]+)?([eE][-+]?[0-9]+)?/';

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						preg_match($pattern, (string) $eachString, $matches);
						$result[] = isset($matches[0]) ? (float) $matches[0] : 0;
					}
				}
				else
				{
					preg_match($pattern, (string) $source, $matches);
					$result = isset($matches[0]) ? (float) $matches[0] : 0;
				}

				break;

			case 'BOOL':
			case 'BOOLEAN':

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (bool) $eachString;
					}
				}
				else
				{
					$result = (bool) $source;
				}

				break;

			case 'WORD':
				$pattern = '/[^A-Z_]/i';

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) preg_replace($pattern, '', $eachString);
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
				}

				break;

			case 'ALNUM':
				$pattern = '/[^A-Z0-9]/i';

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) preg_replace($pattern, '', $eachString);
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
				}

				break;

			case 'CMD':
				$pattern = '/[^A-Z0-9_\.-]/i';

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						$cleaned  = (string) preg_replace($pattern, '', $eachString);
						$result[] = ltrim($cleaned, '.');
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
					$result = ltrim($result, '.');
				}

				break;

			case 'BASE64':
				$pattern = '/[^A-Z0-9\/+=]/i';

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) preg_replace($pattern, '', $eachString);
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
				}

				break;

			case 'STRING':
				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) $this->remove($this->decode((string) $eachString));
					}
				}
				else
				{
					$result = (string) $this->remove($this->decode((string) $source));
				}

				break;

			case 'HTML':
				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = $this->safeHtml((string) $eachString);
					}
				}
				else
				{
					$result = $this->safeHtml((string) $source);
				}

				break;

			case 'ARRAY':
				/**
				 * Unslash array elements as they might contain
				 * escaped values, such as \'.
				 *
				 * @since 10.1.27
				 */
				$result = $this->unslashArray((array) $source);
				
				break;

			case 'PATH':
				$pattern = '/^[A-Za-z0-9_\/-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						preg_match($pattern, (string) $eachString, $matches);
						$result[] = isset($matches[0]) ? (string) $matches[0] : '';
					}
				}
				else
				{
					preg_match($pattern, $source, $matches);
					$result = isset($matches[0]) ? (string) $matches[0] : '';
				}

				break;

			case 'TRIM':
				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) trim($eachString);
					}
				}
				else
				{
					$result = (string) trim($source);
				}

				break;

			case 'USERNAME':
				$pattern = '/[\x00-\x1F\x7F<>"\'%&]/';

				if (is_array($source))
				{
					$result = array();

					// iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) preg_replace($pattern, '', $eachString);
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
				}

				break;

			case 'RAW':
				// unslash escaped quotes
				$result = wp_unslash($source);

				break;

			default:
				// are we dealing with an array?
				if (is_array($source))
				{
					// iterate through the array
					foreach ($source as $key => $value)
					{
						// filter element for XSS and other 'bad' code etc.
						if (is_string($value))
						{
							$source[$key] = $this->remove($this->decode($value));
						}
					}

					$result = $source;
				}
				// or a string?
				else if (is_string($source) && !empty($source))
				{
					// filter source for XSS and other 'bad' code etc.
					$result = $this->remove($this->decode($source));
				}
				// not an array or string, return the passed parameter
				else
				{
					$result = $source;
				}
		}

		return $result;
	}

	/**
	 * Internal method to remove all unwanted tags and attributes.
	 *
	 * @param   string  $source  Input string to be cleaned.
	 *
	 * @return  string  Cleaned version of input parameter.
	 */
	protected function remove($source)
	{
		// escape any new line feed
		$source = str_replace(array("\r\n", "\n", "\r"), array("\\r\\n", "\\n", "\\r"), $source);
		// sanitize the string
		$source = sanitize_text_field($source);
		// restore any new line feed
		$source = str_replace(array("\\r\\n", "\\n", "\\r"), array("\r\n", "\n", "\r"), $source);

		// unslash escaped quotes
		return wp_unslash($source);
	}

	/**
	 * Try to convert to plaintext.
	 *
	 * @param   string  $source  The source string.
	 *
	 * @return  string  Plaintext string.
	 */
	protected function decode($source)
	{
		return html_entity_decode($source, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Try to unslash the elements of an array.
	 *
	 * @param   array  $source  The source array.
	 *
	 * @return  array  The decoded array.
	 *
	 * @since 	10.1.27
	 */
	protected function unslashArray($source)
	{
		foreach ($source as &$elem)
		{
			if (!is_scalar($elem))
			{
				// recursive self call
				$elem = $this->unslashArray((array) $elem);
			}
			else
			{
				// unslash escaped quotes
				$elem = wp_unslash($elem);
			}
		}

		return $source;
	}

	/**
	 * Sanitizes the given string by removing all the tags and attributes
	 * that are not supported by WordPress KSES.
	 *
	 * @param 	string 	$source  The string to sanitize.
	 *
	 * @return 	string 	The sanitized string.
	 *
	 * @since 	10.1.33
	 */
	protected function safeHtml($source)
	{
		JLoader::import('adapter.component.helper');
		return JComponentHelper::filterText($source);
	}
}

/**
 * Alias for JInputFilter, which is still used by the components.
 *
 * @since 10.1.23
 */
class JFilterInput extends JInputFilter
{

}
