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
 * Implements the URI interface for the Joomla platform.
 * 
 * @since 1.3
 */
class VRCPlatformOrgJoomlaUri extends VRCPlatformUriAware
{
	/**
	 * Rewrites an internal URI that needs to be used outside of the website.
	 * This means that the routed URI MUST start with the base path of the site.
	 *
	 * @param 	mixed    $query   The query string or an associative array of data.
	 * @param 	boolean  $xhtml   Replace & by &amp; for XML compliance.
	 * @param 	mixed    $itemid  The itemid to use. If null, the current one will be used.
	 *
	 * @return 	string   The complete routed URI.
	 */
	public function route($query = '', $xhtml = true, $itemid = null)
	{
		$app = JFactory::getApplication();

		if (is_array($query))
		{
			// make sure the array is not empty
			if ($query)
			{
				$query = '?' . http_build_query($query);
			}
			else
			{
				$query = '';
			}

			// the query is an array, build the query string
			$query = 'index.php' . $query;
		}

		if (is_null($itemid) && $app->isClient('site'))
		{
			// no item id, get it from the request
			$itemid = $app->input->getInt('Itemid', 0);
		}

		if ($itemid)
		{
			if ($query)
			{
				// check if the query string contains a '?'
				if (strpos($query, '?') !== false)
				{
					// the query already starts with 'index.php?' or '?'
					$query .= '&';
				}
				else
				{
					// the query string is probably equals to 'index.php'
					$query .= '?';
				}
			}
			else
			{
				// empty query, create the default string
				$query = 'index.php?';
			}

			// the item id is set, append it at the end of the query string
			$query .= 'Itemid=' . $itemid;
		}

		// get base path
		$uri  = JUri::getInstance();
		$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		if (method_exists('JRoute', 'link') && $app->isClient('administrator'))
		{
			/**
			 * Rewrite site URL also from the back-end.
			 * Available starting from Joomla! 3.9.0.
			 */
			$uri = $base . JRoute::link('site', $query, $xhtml);
		}
		else
		{
			// route the query string and append it to the base path to create the final URI
			$uri = $base . JRoute::_($query, $xhtml);
		}

		// remove administrator/ from URL in case this method is called from admin
		if ($app->isClient('administrator') && preg_match("/\/administrator\//i", $uri))
		{
			$adminPos = strrpos($uri, 'administrator/');
			$uri      = substr_replace($uri, '', $adminPos, 14);
		}

		return $uri;
	}

	/**
	 * Routes an admin URL for being used outside from the website (complete URI).
	 *
	 * @param 	mixed    $query  The query string or an associative array of data.
	 * @param 	boolean  $xhtml  Replace & by &amp; for XML compliance.
	 *
	 * @return 	string   The complete routed URI. 
	 */
	public function admin($query = '', $xhtml = true)
	{
		$app = JFactory::getApplication();

		if (is_array($query))
		{
			// make sure the array is not empty
			if ($query)
			{
				$query = '?' . http_build_query($query);
			}
			else
			{
				$query = '';
			}

			// the query is an array, build the query string
			$query = 'index.php' . $query;
		}

		// finalise admin URI
		$uri = JUri::root() . 'administrator/' . $query;

		if ($xhtml)
		{
			// try to make "&" XML safe
			$uri = preg_replace("/&(?!amp;)/", '&amp;', $uri);
		}

		return $uri;
	}

	/**
	 * Prepares a plain/routed URL to be used for an AJAX request.
	 *
	 * @param 	mixed    $query  The query string or a routed URL.
	 * @param 	boolean  $xhtml  Replace & by &amp; for XML compliance.
	 *
	 * @return 	string   The AJAX end-point URI.
	 */
	public function ajax($query = '', $xhtml = false)
	{
		if (preg_match("/^index\.php/", $query) && JFactory::getApplication()->isClient('site'))
		{
			// rewrite plain URL
			$uri = JRoute::_($query, $xhtml);
		}
		else
		{
			// routed URL given, use it directly
			$uri = $query;

			if ($xhtml)
			{
				// try to make "&" XML safe
				$uri = preg_replace("/&(?!amp;)/", '&amp;', $uri);
			}
		}

		return $uri;
	}

	/**
	 * Includes the CSRF-proof token within the specified query string/URL.
	 *
	 * @param 	mixed 	 $query  The query string or a routed URL.
	 * @param 	boolean  $xhtml  Replace & by &amp; for XML compliance.
	 *
	 * @return 	string 	 The resulting path.
	 */
	public function addCSRF($query = '', $xhtml = false)
	{
		// safely append the CSRF token within the query string
		$uri = JUri::getInstance($query);
		$uri->setVar(JSession::getFormToken(), 1);

		if ($xhtml)
		{
			// try to make "&" XML safe
			$uri = str_replace('&', '&amp;', (string) $uri);
		}

		return (string) $uri;
	}

	/**
	 * Returns the platform base path.
	 *
	 * @return 	string
	 */
	protected function getAbsolutePath()
	{
		return JPATH_SITE;
	}
}
