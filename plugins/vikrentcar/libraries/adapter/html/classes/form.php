<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.html
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Utility class for Form behaviors.
 *
 * @since 10.0
 */
abstract class JHtmlForm
{
	/**
	 * Generates an input hidden containing a form token.
	 *
	 * @return 	string 	The form token.
	 */
	public static function token()
	{
		/**
		 * Make sure the session class is loaded.
		 *
		 * @since 10.1.33
		 */
		JLoader::import('adapter.session.session');
		$action = JSession::getFormTokenAction();
		$name   = JSession::getFormTokenName();

		// display nonce field
		return wp_nonce_field($action, $name, $referer = true, $echo = false);
	}
}
