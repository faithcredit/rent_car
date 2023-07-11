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
 * Utility class for jQuery behaviors.
 *
 * @since 10.0
 */
abstract class JHtmlJQuery
{
	/**
	 * Includes jQuery framework.
	 *
	 * @return 	void
	 */
	public static function framework()
	{
		// Do nothing, jQuery framework is included by default.
		// Used to avoid portability errors.
	}

	/**
	 * Auto set CSRF token to ajaxSetup so all jQuery ajax call will contains CSRF token.
	 *
	 * @return  void
	 *
	 * @since   10.1.33
	 */
	public static function token()
	{
		static $loaded = false;

		// Only load once
		if ($loaded)
		{
			return;
		}

		// get session token
		JLoader::import('adapter.session.session');
		$token = JSession::getFormToken();

		// escape token for JS usage
		$token = esc_js($token);

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
(function($) {
	$.ajaxSetup({
		headers: {
			'X-CSRF-Token': '$token'
		}
	});
})(jQuery);
JS
		);

		$loaded = true;
	}
}
