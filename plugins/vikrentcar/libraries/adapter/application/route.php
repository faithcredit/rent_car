<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.application
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Route adapter class.
 *
 * @since 10.0
 */
class JRoute
{
	/**
	 * A list of component shortcodes.
	 *
	 * @var array
	 */
	protected static $shortcodes = array();

	/**
	 * Translates an internal URL to a human-readable URL.
	 *
	 * @param 	string 	 $url 	 Absolute or Relative URI.
	 * @param 	boolean  $xhtml  Replace & by &amp; for XML compliance.
	 * @param 	integer  $ssl    Secure state for the resolved URI.
	 *                             0: (default) No change, use the protocol currently used in the request.
	 *                             1: Make URI secure using global secure site URI.
	 *                             2: Make URI unsecure using the global unsecure site URI.
	 *
	 * @return 	string 	 The translated humanly readable URL.
	 *
	 * @uses 	getPermalink()
	 * @uses 	replace()
	 */
	public static function _($url, $xhtml = true, $ssl = null)
	{
		// parse URL to obtain query args (true: as array)
		$args = JUri::getInstance($url)->getQuery(true);

		// prepend current URI if starts with 'index.php'
		if (strpos($url, 'index.php') === 0)
		{
			$url = static::getPermalink();
			$url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($args);
		}

		// if 'option' and 'view' arguments are not empty, try to route URLs
		if (!empty($args['option']) && (!empty($args['view']) || !empty($args['Itemid'])))
		{
			$url = static::replace($args, $url);
		}

		/**
		 * Prepend base URL in case the system
		 * was not able to find a permalink.
		 *
		 * @since 10.1.29
		 */
		if (preg_match("/^\?/", $url))
		{
			$url = JUri::root() . $url;
		}

		// replace & with &amp; for XML compliance
		if ($xhtml)
		{
			$url = str_replace('&', '&amp;', $url);
		}

		if ($ssl == 1)
		{
			// force HTTPS
			$url = str_replace('http://', 'https://', $url);
		}
		else if ($ssl == 2)
		{
			// force HTTP
			$url = str_replace('https://', 'http://', $url);
		}

		return $url;
	}

	/**
	 * Proxy for underscore method.
	 * Needed to bypass the issue reported here:
	 * @link https://meta.trac.wordpress.org/ticket/3601
	 *
	 * @since 10.1.32  Renamed from `u`.
	 */
	public static function rewrite($url, $xhtml = true, $ssl = null)
	{
		return static::_($url, $xhtml, $ssl);
	}

	/**
	 * Replaces the plain URL with the rewritten one.
	 *
	 * @param 	array 	$args  The URL parts.
	 * @param 	string 	$url   The URL to rewrite.
	 *
	 * @return 	string  The rewritten URL, if possible.
	 *
	 * @uses 	getPermalink()
	 * @uses 	matchShortcode()
	 * @uses 	withQueryString()
	 */
	protected static function replace(array $args, $url)
	{
		// make safe by replacing initial 'com_' (if any)
		$option = preg_replace("/^com_/", "", $args['option']);
		unset($args['option']);

		// check if the shortcodes have been already cached
		if (!isset(static::$shortcodes[$option]))
		{
			// get model to access shortcodes
			$model = JModel::getInstance($option, 'shortcodes', 'admin');

			/**
			 * Make sure the model exists for the requested option.
			 *
			 * @since 10.1.19
			 */
			if ($model)
			{
				// cache the component shortcodes
				static::$shortcodes[$option] = $model->all();
			}
			else
			{
				// create an empty list
				static::$shortcodes[$option] = array();
			}
		}

		$post_id = false;

		if (!empty($args['view']))
		{
			// try to rewrite the URL with a matching shortcode
			$post_id = static::matchShortcode(static::$shortcodes[$option], $args);
		}

		if (!$post_id && !empty($args['Itemid']))
		{
			// if the Itemid is set, get the POST permalink directly
			$post_id = (int) $args['Itemid'];
		}

		if ($post_id)
		{
			$url = static::getPermalink($post_id);
			$url = static::withQueryString($url, $post_id, static::$shortcodes[$option], $args);
		}

		/**
		 * Try to instantiate an external router.
		 *
		 * @since 10.1.19
		 */
		$router = JFactory::getApplication()->getRouter($option);
		
		if ($router)
		{
			// route URL using the specified arguments
			$url = $router->build($url);
		}

		return $url;
	}

	/**
	 * Wrapper method to extend the function used to obtain
	 * the current permalink or the post link.
	 *
	 * @param 	mixed 	$post_id 	The post ID. Null to obtain the current link.
	 *
	 * @return 	string 	The permalink.
	 */
	protected static function getPermalink($post_id = null)
	{
		// if the post ID is set, get the permalink directly
		if ($post_id)
		{
			return get_permalink($post_id);
		}

		// otherwise get the current permalink
		$link = get_permalink();

		// return the permalink only if it is not empty
		if ($link)
		{
			return $link;
		}

		// obtain the post ID from the current URL
		$post_id = url_to_postid(JUri::current());

		// if the current URL doesn't match any post, return an empty string
		if (!$post_id)
		{
			return '';
		}

		// recursive call to obtain the permalink of the post found
		return static::getPermalink($post_id);
	}

	/**
	 * Returns a URL containing the specified query string.
	 *
	 * @param 	string 	 $url 		  The URL to which append the query string.
	 * @param 	integer  $post_id 	  The post ID.
	 * @param 	array 	 $shortcodes  The shortcodes list.
	 * @param 	array  	 $args 		  The args to build the query string.
	 *
	 * @return 	string 	The URL with the query string.
	 */
	protected static function withQueryString($url, $post_id, array $shortcodes, array $args = array())
	{
		// unset always the Item ID
		unset($args['Itemid']);

		// iterate the shortcodes
		foreach ($shortcodes as $shortcode)
		{
			// process only if the given post ID matches the one assigned to the shortcode
			if ($post_id == $shortcode->post_id)
			{
				// get shortcodes parameters, view and language
				$json = (array) json_decode($shortcode->json, true);
				$json['view'] = $shortcode->type;
				$json['lang'] = $shortcode->lang;

				// get the keys that the shortcode doesn't contain
				$diff = self::diff($args, $json);

				// if there are differences between the args, append them to the URL
				if (count($diff))
				{
					$url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($diff);
				}

				// return the URL with the query string (do not proceed)
				return $url;
			}
		}

		if (count($args))
		{
			/**
			 * if no shortcodes found, attach the whole query string
			 * 
			 * @since 	10.1.8
			 */
			$url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($args);
		}

		// nothing found, return plain URL
		return $url;
	}

	/**
	 * Checks if there is a shortcode object that matches the given URL parts.
	 * In addition, the shortcode MUST own an existing post ID.
	 *
	 * @param 	array 	$shortcodes  The shortcode objects list.
	 * @param 	array 	$args 		 The URL query parts.
	 *
	 * @return 	mixed 	The post ID found, false on failure.
	 */
	protected static function matchShortcode($shortcodes, array $args)
	{
		$post_id 	= false;
		$last_count = 0;

		if (empty($args['lang']))
		{
			// inject current langtag within URL arguments, only if not specified
			// by query string
			$args['lang'] = JFactory::getLanguage()->getTag();
		}

		foreach ($shortcodes as $shortcode)
		{
			$count = 0;

			// decode JSON data
			$json = json_decode($shortcode->json, true);
			// inject VIEW name
			$json['view'] = $shortcode->type;
			// inject shortcode LANGTAG
			$json['lang'] = $shortcode->lang;

			// proceed only if the view is the same
			if ($json['view'] == $args['view'])
			{
				// iterate the URL arguments
				foreach ($args as $k => $v)
				{
					// the property should be matched only if the shortcode
					// owns a parameter with the same name (defined in xml view)
					// and value.
					if (isset($json[$k]))
					{
						/**
						 * Improved the comparison of the language tag, which might be
						 * handled in different ways from different sections of WP.
						 * 
						 * @since 10.1.38
						 */
						if ($k === 'lang')
						{
							// check whether at least a language code doesn't mention the country code
							if (!preg_match("/[_\-]/", $json[$k]) || !preg_match("/[_\-]/", $v))
							{
								// remove country code from locale assigned to the shortcode
								$json[$k] = preg_split("/[_\-]/", $json[$k]);
								$json[$k] = array_shift($json[$k]);

								// remove country code from URL locale
								$v = preg_split("/[_\-]/", $v);
								$v = array_shift($v);
							}

							// normalize locale separators
							$json[$k] = str_replace('-', '_', $json[$k]);
							$v        = str_replace('-', '_', $v);
						}

						// make sure both the values are scalar
						if (is_scalar($json[$k]) && is_scalar($v))
						{
							if (!strcmp($json[$k], $v))
							{
								// if the shortcode owns the parameter and it is equals
								// to the one specified in the URL, increase the counter
								$count++;
							}
						}
						/**
						 * Added support for arrays in query string, such as cid[].
						 * 
						 * @since 10.1.27
						 */
						else
						{
							// treat both the arguments as arrays
							$json[$k] = (array) $json[$k];
							$v        = (array) $v;

							// sort them to maximize the possibility of having matching URLs
							sort($json[$k]);
							sort($v);

							if ($json[$k] == $v)
							{
								// increase the counter as we have a matching parameter
								$count++;
							}
						}
					}
				}

				// if the count of matches is higher than the last one,
				// update the flags with the shortcode post ID and the current count
				if ($count > $last_count)
				{
					$post_id 	= $shortcode->post_id;
					$last_count = $count;
				}
			}
		}

		return $post_id;
	}

	/**
	 * Implementation of array_diff_assoc with recursion.
	 *
	 * @param 	array  array1  The array to compare from.
	 * @param 	array  array2  An array to compare against.
	 *
	 * @return 	array  Returns an array containing all the values from array1
	 * 				   that are not present in the second array.
	 *
	 * @since 	10.1.27
	 */
	private static function diff($array1, $array2)
	{
		$difference = array();

		foreach ($array1 as $key => $value)
		{
			if (is_array($value))
			{
				if (!isset($array2[$key]) || !is_array($array2[$key]))
				{
					$difference[$key] = $value;
				}
				else
				{
					$new_diff = self::diff($value, $array2[$key]);

					if (!empty($new_diff))
					{
						$difference[$key] = $new_diff;
					}
				}
			}
			else if (!array_key_exists($key, $array2) || $array2[$key] !== $value)
			{
				$difference[$key] = $value;
			}
		}

		return $difference;
	}
}
