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
 * VikRentCar mail controller.
 *
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarControllerMail extends JControllerAdmin
{
	/**
	 * Given the content received through the AJAX request,
	 * parses the visual editor wrapper symbols and contents.
	 * 
	 * @return 	void
	 */
	public function preview_visual_editor()
	{
		$dbo   = JFactory::getDbo();
		$app   = JFactory::getApplication();
		$input = $app->input;

		// the raw email content
		$content = $input->get('content', '', 'raw');

		// an optional booking ID to use for the simulation
		$bid = $input->getInt('bid', 0);

		// replace visual editor placeholders for special tags and conditional text rules
		$content = preg_replace_callback("/(<strong class=\"vrc-editor-hl-specialtag\">)([^<]+)(<\/strong>)/", function($match) {
			return $match[2];
		}, $content);

		// grab the latest confirmed reservation
		$clauses = [
			"`status`='confirmed'",
		];
		if (!empty($bid)) {
			$clauses[] = "`id`=" . $bid;
		}
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE " . implode(' AND ', $clauses) . " ORDER BY `id` DESC";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$booking = $dbo->loadAssoc();
			// inject properties for parsing the conditional text rules later
			VikRentCar::getConditionalRulesInstance()->set(['booking'], [$booking]);
		}

		// wrap dummy mail data with proper content
		$mail_data = new VRCMailWrapper([
			'sender'      => ['dummy@email.com', __METHOD__],
			'recipient'   => 'dummy@email.com',
			'bcc'         => [],
			'reply'       => null,
			'subject'     => __METHOD__,
			'content'     => $content,
			'attachments' => null,
		]);

		// prepare the final email content
		$mail_content = VRCFactory::getPlatform()->getMailer()->prepare($mail_data);

		// send JSON response to output
		VRCHttpDocument::getInstance($app)->json([$mail_content]);
	}

	/**
	 * AJAX request made by the configuration page when updating the
	 * visual editor mail content wrapper symbols (HTML code). From
	 * a whole HTML string, we need to be able to detect the opening
	 * and closing HTML tags, usually DIV tags with inline styles.
	 * 
	 * @return 	void
	 */
	public function update_ve_contwraper()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// the raw wrapper content HTML code
		$wrapper_content = $input->get('wrapper_content', '', 'raw');

		if (empty($wrapper_content)) {
			VRCHttpDocument::getInstance()->close(500, 'Empty mail content wrapper HTML code');
		}

		// regex pattern to match only HTML tags, inclusive of tab and new line feeds
		$rgx_pattern = '/(\t*<\/?[a-z]+\s?[A-Za-z0-9=:"%;#\- ]*?>\n?)/';

		// find occurrences
		preg_match_all($rgx_pattern, $wrapper_content, $matches);

		if (empty($matches[1]) || (count($matches[1]) % 2) !== 0) {
			// no matches or odd matches count, this is an error
			VRCHttpDocument::getInstance()->close(500, 'Invalid HTML code detected. Make sure to open and close all the HTML tags.');
		}

		// split the HTML tags in half to get the opening and closing content wrapper code
		$tags_per_layout = floor(count($matches[1]) / 2);

		$opening_tags = array_slice($matches[1], 0, $tags_per_layout);
		$closing_tags = array_slice($matches[1], $tags_per_layout, $tags_per_layout);

		$opening_layout = implode('', $opening_tags);
		$closing_layout = implode('', $closing_tags);

		// access the configuration object
		$config = VRCFactory::getConfig();

		// update opening and closing layouts
		$config->set('mail_wrapper_layout_opening', $opening_layout);
		$config->set('mail_wrapper_layout_closing', $closing_layout);

		// send JSON confirmation response to output
		VRCHttpDocument::getInstance($app)->json([$opening_layout, $closing_layout]);
	}

	/**
	 * AJAX endpoint for the visual editor to get the default logo URL.
	 * 
	 * @return 	void
	 */
	public function get_default_logo()
	{
		$app = JFactory::getApplication();
		$logo_info = new stdClass;
		$logo_info->url = null;

		$sitelogo = VikRentCar::getSiteLogo();
		$backlogo = VikRentCar::getBackendLogo();
		if (!empty($sitelogo) && is_file(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources'. DIRECTORY_SEPARATOR . $sitelogo)) {
			$logo_info->url = VRC_ADMIN_URI . 'resources/' . $sitelogo;
		} elseif (!empty($backlogo) && is_file(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources'. DIRECTORY_SEPARATOR . $backlogo)) {
			$logo_info->url = VRC_ADMIN_URI . 'resources/' . $backlogo;
		} else {
			// default logo
			$logo_info->url = VRC_ADMIN_URI . 'vikrentcar.png';
		}

		// send JSON response to output
		VRCHttpDocument::getInstance($app)->json($logo_info);
	}
}
