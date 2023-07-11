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
 * Implements the URI interface for the WordPress platform.
 * 
 * @since 1.3
 */
class VRCPlatformOrgWordpressUri extends VRCPlatformUriAware
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

		// check if the query already specifies the Itemid
		if (!preg_match("/&Itemid=[\d]*/", $query))
		{
			// try to extract view from query
			if (preg_match("/&view=([a-z0-9_]+)(?:&|$)/", $query, $match))
			{
				$view = end($match);
			}
			else
			{
				$view = null;
			}

			// import shortcodes model
			$model 	= JModel::getInstance('vikrentcar', 'shortcodes', 'admin');
			$itemid = $model->best($view);

			if ($itemid)
			{
				// update query with Itemid found
				$query .= (strpos($query, '?') === false ? '?' : '&') . 'Itemid=' . $itemid;
			}
		}

		// route URL
		return JRoute::_($query, false);
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

		// replace initial index.php with admin.php
		$query = preg_replace("/^index\.php/", 'admin.php', $query);
		// replace option=com_vikrentcar with page=vikrentcar
		$query = preg_replace("/(&|\?)option=com_vikrentcar/", '$1page=vikrentcar', $query);

		// finalise admin URI
		$uri = admin_url($query);

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
		// instantiate path based on specified query
		$path = new JUri($query);

		// delete option var from query
		$path->delVar('option');

		// force action in query
		$plugin_action = strpos($query, 'vikchannelmanager') !== false ? 'vikchannelmanager' : 'vikrentcar';
		$path->setVar('action', $plugin_action);

		// force application client in case of front-end
		if (JFactory::getApplication()->isClient('site'))
		{
			$path->setVar('vik_ajax_client', 'site');
		}

		// create AJAX URI
		$uri = admin_url('admin-ajax.php') . '?' . $path->getQuery();

		if ($xhtml)
		{
			// try to make "&" XML safe
			$uri = preg_replace("/&(?!amp;)/", '&amp;', $uri);
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
		JLoader::import('adapter.session.session');

		// safely append the CSRF token within the query string
		$uri = JUri::getInstance($query);
		$uri->setVar(JSession::getFormTokenName(), JSession::getFormToken());

		if ($xhtml)
		{
			// try to make "&" XML safe
			$uri = preg_replace("/&(?!amp;)/", '&amp;', (string) $uri);
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
		return ABSPATH;
	}
}
