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
 * Utility class for script behaviors.
 *
 * @since 10.0
 */
abstract class JHtmlBehavior
{
	/**
	 * Includes TinyMCE editor assets.
	 *
	 * @return 	void
	 */
	public static function tinyMCE()
	{
		wp_enqueue_editor();
	}

	/**
	 * Includes CodeMirror editor assets.
	 *
	 * @param 	mixed 	$type 	An array of argument of the language type string.
	 *
	 * @return 	void
	 */
	public static function codeMirror($type = 'php')
	{
		if (is_string($type))
		{
			$type = array('type' => $type);
		}

		wp_enqueue_code_editor($type);
	}

	/**
	 * Loads the datepicker JS plugin.
	 *
	 * @return 	void
	 */
	public static function calendar()
	{
		static $loaded = 0;

		if ($loaded)
		{
			return;
		}

		$loaded = 1;

		// Labels
		$done 	= __('Done');
		$prev 	= __('Previous');
		$next 	= __('Next');
		$today 	= __('Today');
		$wk 	= 'Wk';

		// Months
		$months = array(
			__('January'),
			__('February'),
			__('March'),
			__('April'),
			__('May'),
			__('June'),
			__('July'),
			__('August'),
			__('September'),
			__('October'),
			__('November'),
			__('December'),
		);

		$months_short = array(
			_x('Jan', 'January abbreviation'),
			_x('Feb', 'February abbreviation'),
			_x('Mar', 'March abbreviation'),
			_x('Apr', 'April abbreviation'),
			_x('May', 'May abbreviation'),
			_x('Jun', 'June abbreviation'),
			_x('Jul', 'July abbreviation'),
			_x('Aug', 'August abbreviation'),
			_x('Sep', 'September abbreviation'),
			_x('Oct', 'October abbreviation'),
			_x('Nov', 'November abbreviation'),
			_x('Dec', 'December abbreviation'),
		);

		$months 		= json_encode($months);
		$months_short 	= json_encode($months_short);

		// Days
		$days = array(
			__('Sunday'),
			__('Monday'),
			__('Tuesday'),
			__('Wednesday'),
			__('Thursday'),
			__('Friday'),
			__('Saturday'),
		);

		$days_short_3 = array(
			__('Sun'),
			__('Mon'),
			__('Tue'),
			__('Wed'),
			__('Thu'),
			__('Fri'),
			__('Sat'),
		);

		$days_short_2 = array();
		foreach ($days_short_3 as $d)
		{
			$days_short_2[] = mb_substr($d, 0, 2, 'UTF-8');
		}

		// snippet used to make sure the substring of
		// the week days doesn't return the same value (see Hebrew)
		// for all the elements
		$days_short_2 = array_unique($days_short_2);

		if (count($days_short_2) != count($days_short_3))
		{
			// the count doesn't match, use the 3 chars days
			$days_short_2 = $days_short_3;
		}

		$days 			= json_encode($days);
		$days_short_3 	= json_encode($days_short_3);
		$days_short_2 	= json_encode($days_short_2);

		// should return a value between 0-6 (1: Monday, 0: Sunday)
		$start_of_week = (int) get_option('start_of_week', 0);

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(function($){
	$.datepicker.regional["wp-datepicker"] = {
		closeText: "$done",
		prevText: "$prev",
		nextText: "$next",
		currentText: "$today",
		monthNames: $months,
		monthNamesShort: $months_short,
		dayNames: $days,
		dayNamesShort: $days_short_3,
		dayNamesMin: $days_short_2,
		weekHeader: "$wk",
		firstDay: $start_of_week,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ""
	};

	$.datepicker.setDefaults($.datepicker.regional["wp-datepicker"]);
});
JS
		);
	}

	/**
	 * Loads a tooltip.
	 *
	 * @return 	void
	 */
	public static function tooltip()
	{
		// do nothing for the moment
	}

	/**
	 * Keep session alive, for example, while editing or creating an article.
	 *
	 * @return  void
	 */
	public static function keepalive()
	{
		// do nothing for the moment
	}

	/**
	 * Loads a modal.
	 *
	 * @return  void
	 */
	public static function modal()
	{
		static $loaded = 0;

		if ($loaded)
		{
			return;
		}

		$loaded = 1;

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	jQuery('.wrap a.modal[target="_blank"]').on('click', function(e) {
		// get link HREF
		var href = jQuery(this).attr('href');

		// make sure we have an image
		if (href.match(/\.(png|jpe?g|gif|bmp)$/i)) {
			// prevent default link action
			e.preventDefault();

			// open modal containing image preview
			wpOpenJModal('wpmodal', jQuery(this).attr('href'));

			return false;
		}

		// otherwise fallback to default browser opening
	});
});
JS
		);

		// display modal to preview the images
		echo JHtml::_(
			'bootstrap.renderModal',
			'jmodal-wpmodal',
			array(
				'title'       => JText::_('JMEDIA_PREVIEW_TITLE'),
				'closeButton' => true,
				'keyboard'    => true, 
				'bodyHeight'  => 80,
			)
		);
	}

	/**
	 * Enhance the compatibility with Wordpress via javascript.
	 *
	 * When "tmpl" var is equals to "component", tries to remove the contents
	 * displayed by the theme.
	 *
	 * Provides a script to replace all the "index.php" occurrences into "admin.php".
	 *
	 * @return 	void
	 */
	public static function component()
	{
		static $loaded = 0;

		if ($loaded)
		{
			return;
		}

		$loaded = 1;

		$app 	  = JFactory::getApplication();
		$input 	  = $app->input;
		$document = JFactory::getDocument();

		// check if tmpl component
		if ($input->get('tmpl') === 'component')
		{
			$document->addScriptDeclaration(
<<<JS
(function($) {
	'use strict';

	$(function() {
		// clone DOM wrapper
		var clone = $('.wrap.plugin-container').detach();

		// remove all body elements and attach the wrapper
		$('body').children().not('script,style,link').remove();
		$('body').append(clone);

		// adjust wrapper margin
		$('.wrap.plugin-container').css('margin', '10px');

		// remove padding from WP toolbar
		$('html.wp-toolbar').css('padding', 0);
	});
})(jQuery);
JS
			);
		}

		// script to change all <form> and <a> tags from "index.php" to "admin.php"
		if ($app->isAdmin())
		{
			$document->addScriptDeclaration(
<<<JS
(function($) {
	'use strict';

	$(function() {
		routePageTargets('.wrap.plugin-container');
	});
})(jQuery);
JS
			);
		}
	}

	/**
	 * Loads the core functionalities that need to be used
	 * every time a page is requested.
	 *
	 * @return 	void
	 *
	 * @since 	10.1.14
	 */
	public static function core()
	{
		/**
		 * Core usage shouldn't be cached because
		 * widgets might declare their own options.
		 * 
		 * @since 10.1.21
		 */

		$document = JFactory::getDocument();

		/**
		 * Generate scripts options.
		 *
		 * @since 10.1.14
		 */
		$scriptOptions = $document->getScriptOptions();

		if (!empty($scriptOptions))
		{
			// encode options in JSON format (evaluate PRETTY_PRINT usage)
			$prettyPrint = (WP_DEBUG && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
			$jsonOptions = json_encode($scriptOptions, $prettyPrint);
			$jsonOptions = $jsonOptions ? $jsonOptions : '{}';

			// add application/json declaration to document
			$document->addScriptDeclaration($jsonOptions, 'application/json');
		}
	}
}
