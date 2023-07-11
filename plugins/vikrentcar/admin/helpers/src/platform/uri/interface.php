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
 * Declares all the URI helper methods that may differ between every supported platform.
 * 
 * @since 1.3
 */
interface VRCPlatformUriInterface
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
	public function route($query = '', $xhtml = true, $itemid = null);

	/**
	 * Routes an admin URL for being used outside from the website (complete URI).
	 *
	 * @param 	mixed    $query  The query string or an associative array of data.
	 * @param 	boolean  $xhtml  Replace & by &amp; for XML compliance.
	 *
	 * @return 	string   The complete routed URI. 
	 */
	public function admin($query = '', $xhtml = true);

	/**
	 * Prepares a plain/routed URL to be used for an AJAX request.
	 *
	 * @param 	mixed    $query  The query string or a routed URL.
	 * @param 	boolean  $xhtml  Replace & by &amp; for XML compliance.
	 *
	 * @return 	string   The AJAX end-point URI.
	 */
	public function ajax($query = '', $xhtml = false);

	/**
	 * Includes the CSRF-proof token within the specified query string/URL.
	 *
	 * @param 	mixed 	 $query  The query string or a routed URL.
	 * @param 	boolean  $xhtml  Replace & by &amp; for XML compliance.
	 *
	 * @return 	string 	 The resulting path.
	 */
	public function addCSRF($query = '', $xhtml = false);

	/**
	 * Converts the given absolute path into a reachable URL.
	 *
	 * @param 	string   $path      The absolute path.
	 * @param 	boolean  $relative  True to receive a relative path.
	 *
	 * @return 	mixed    The resulting URL on success, null otherwise.
	 */
	public function getUrlFromPath($path, $relative = false);
}
