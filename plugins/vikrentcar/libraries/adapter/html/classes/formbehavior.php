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
 * Utility class for form related behaviors.
 *
 * @since 10.1.16
 */
abstract class JHtmlFormbehavior
{
	/**
	 * Method to load the Chosen JavaScript framework and supporting CSS into the document head.
	 *
	 * @param   string  $selector  Class for Chosen elements.
	 * @param   mixed   $debug     Is debugging mode on? [optional].
	 * @param   array   $options   The possible Chosen options as name => value [optional].
	 *
	 * @return  void
	 */
	public static function chosen($selector = '.btn-toolbar', $debug = null, $options = array())
	{
		static $loaded = 0;

		// register only once
		if ($loaded)
		{
			return;
		}

		$loaded = 1;

		$version = new JVersion();

		/**
		 * Use select2 rendering only in case of WordPress 5.2 or lower.
		 * Starting from WordPress 5.3 the dropdowns are rendered properly
		 * and there is no need to use a different layout.
		 * Contrarily, we will lose the possibility of searching the options.
		 *
		 * @since 10.1.30
		 */
		// $use_select2 = version_compare($version->getShortVersion(), '5.3', '<') ? 1 : 0;
		$use_select2 = 0;

		// load always select2 plugin
		// JHtml::_('select2');
		JText::script('JGLOBAL_SELECT_AN_OPTION');

		// add support for chosen plugin
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
if (jQuery.fn.chosen === undefined) {
	jQuery.fn.chosen = function(data) {
		// iterate all selected elements
		jQuery(this).each(function() {
			// check is we have a multiple select
			var isMultiple = jQuery(this).prop('multiple');

			if (!$use_select2 && !isMultiple) {
				// do not go ahead in case we don't need a custom plugin
				// to handle standard dropdowns
				return this;
			}

			if (data !== undefined) {
				// invoke requested method (e.g. destroy)
				jQuery(this).select2(data);
			} else {
				data = {};
				data.width       = isMultiple ? 300 : 200;
				data.allowClear  = jQuery(this).hasClass('required') ? false : true;
				data.placeholder = Joomla.JText._('JGLOBAL_SELECT_AN_OPTION');

				var firstOption = jQuery(this).find('option').first();

				// in case we don't have an empty option, unset placeholder
				if (!isMultiple && firstOption.length && firstOption.val().length > 0) {
					data.allowClear  = false;
					data.placeholder = null;
				}

				// turn off search when there are 5 options or less
				if (jQuery(this).find('option').length <= 5) {
					data.minimumResultsForSearch = -1;
				}

				// init select2 plugin
				jQuery(this).select2(data);
			}
		});

		return this;
	}
}

jQuery(document).ready(function() {
	jQuery('{$selector}')
		.find('select')
			.chosen()
				.on('chosen:updated', function() {
					// refresh select2 value when triggered
					// jQuery(this).select2('val', jQuery(this).val());
				});
});
JS
		);
	}
}
