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
 * VikRentCar HTML scripts helper.
 *
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
abstract class VRCHtmlScripts
{
	/**
	 * Auto set CSRF token to ajaxSetup so all jQuery ajax call will contains CSRF token.
	 *
	 * @return  void
	 *
	 * @see 	JHtmlJquery::csrf()
	 */
	public static function ajaxcsrf($name = 'csrf.token')
	{
		static $loaded = 0;

		if ($loaded)
		{
			// do not load again
			return;
		}

		$loaded = 1;

		try
		{
			// rely on system helper
			JHtml::_('jquery.token');
		}
		catch (Exception $e)
		{
			// Helper not declared, installed CMS too old (lower than J3.8).
			// Fallback to our internal helper.
			$csrf = addslashes(JSession::getFormToken());

			JFactory::getDocument()->addScriptDeclaration(
<<<JS
;(function($) {
	$.ajaxSetup({
		headers: {
			'X-CSRF-Token': '{$csrf}',
		},
	});
})(jQuery);
JS
			);
		}
	}
}
