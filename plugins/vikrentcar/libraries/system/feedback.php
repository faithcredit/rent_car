<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	system
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Helper class used to collect the feedback of the users.
 * Should be attached to `plugin_action_links` WordPress filter.
 *
 * @since 1.0
 */
class VikRentCarFeedback
{
	/**
	 * Attaches a feeback modal to the deactivation button.
	 *
	 * @param 	array 	$actions 	An array of plugin action links.
	 * @param 	string 	$plugin 	Path to the plugin file relative to the plugins directory.
	 *
	 * @return 	array 	The filtered actions.
	 */
	public static function deactivate($actions, $plugin)
	{
		// make sure the plugin is recaptcha and the deactivation link is available
		if ($plugin != 'vikrentcar/vikrentcar.php' || !isset($actions['deactivate']))
		{
			return $actions;
		}

		$input = JFactory::getApplication()->input;

		// check whether the safe-word is set in the request
		if ($input->getUint('feedback', 1) === 0)
		{
			// skip deactivation feedback
			return $actions;
		}

		// check if the user already submitted the feedback in the last week
		if ($input->cookie->getBool('vikrentcar_feedback'))
		{
			// feedback already done, avoid to ask for it one more time
			return $actions;
		}

		// extract deactivation URL from link
		if (!preg_match("/href=\"([^\"]*)\"/i", $actions['deactivate'], $match))
		{
			// unable to extract URL from deactivation link
			return $actions;
		}

		$deactivate_url = end($match);

		// define URL to popup thickbox
		$url = '#TB_inline?width=500&height=400&inlineId=vikrentcar-feedback';

		$__title = __('Feedback', 'vikrentcar');

		// add support for feedback
		$actions['deactivate'] = sprintf(
			'<a href="%s" class="thickbox" aria-label="%s" data-name="%s">%s</a>',
			esc_attr($url),
			esc_attr(sprintf(__('Deactivate %s', 'plugin'), 'VikRentCar')),
			esc_attr($__title),
			__('Deactivate')
		);

		// append thickbox to admin footer
		add_action('admin_footer', function() use ($deactivate_url)
		{
			// make URL safe for JS
			$deactivate_url_js = str_replace('&amp;', '&', $deactivate_url);

			VikRentCarLoader::import('update.license');

			$data = array(
				'url' => $deactivate_url_js,
				'pro' => (bool) VikRentCarLicense::isPro(),
			);

			// display feedback thickbox
			echo JLayoutHelper::render(
				'html.feedback.thickbox',
				$data,
				null,
				array('component' => 'com_vikrentcar')
			);
		});

		return $actions;
	}
}
