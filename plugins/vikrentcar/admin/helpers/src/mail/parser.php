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
 * Parses the email content for special elements.
 *
 * @since 	1.3.0
 */
final class VRCMailParser
{
	/**
	 * @var  string  the default mail wrapper regex pattern (HTML tag in visual editor)
	 */
	private static $wrapper_pattern = "/<hr class=\"vrc-editor-hl-mailwrapper\"\s*\/?>/";

	/**
	 * Checks for any content wrapper HTML tags applied through
	 * the Visual Editor during the composing of an email message.
	 * 
	 * @param 	string 	$content 	the full email content.
	 * 
	 * @return 	string
	 */
	public static function checkWrapperSymbols($content)
	{
		$chunks = preg_split(static::$wrapper_pattern, (string)$content);

		if (count($chunks) < 2) {
			// no content wrapper used in email message
			return $content;
		}

		$layout_opening = self::getWrapperLayout(true);
		$layout_closing = self::getWrapperLayout(false);

		$final_content = '';
		foreach ($chunks as $piece => $part) {
			$is_even = ($piece === 0 || (($piece % 2) === 0));
			if (strlen(trim($part)) && !$is_even) {
				$final_content .= $layout_opening . $part . $layout_closing;
			} else {
				$final_content .= $part;
			}
		}

		return $final_content;
	}

	/**
	 * Returns the current mail wrapper layout HTML code.
	 * 
	 * @param 	bool 	$opening 	whether to get the opening or closing HTML.
	 * 
	 * @return 	string
	 */
	public static function getWrapperLayout($opening = true)
	{
		// configuration field name
		$opt_name = 'mail_wrapper_layout_' . ($opening ? 'opening' : 'closing');

		// access the configuration object
		$config = VRCFactory::getConfig();

		// get the current configuration value
		$layout = $config->get($opt_name);

		if (!$layout) {
			// set and get default layout
			$layout = self::getWrapperDefaultLayout($opening);
			$config->set($opt_name, $layout);
		}

		return $layout;
	}

	/**
	 * Returns the default mail wrapper layout HTML code.
	 * 
	 * @param 	bool 	$opening 	whether to get the opening or closing HTML.
	 * 
	 * @return 	string
	 */
	private static function getWrapperDefaultLayout($opening = true)
	{
		$layout_opening = "\n";
		$layout_opening .= "<div style=\"background: #fdfdfd;padding: 30px 0;\">";
		$layout_opening .= "\n\t";
		$layout_opening .= "<div style=\"max-width: 600px;margin: 0 auto;background: #fff;padding: 30px;border: 1px solid #eee;border-radius: 6px\">";
		$layout_opening .= "\n";

		$layout_closing = "\n";
		$layout_closing .= "\t</div>\n";
		$layout_closing .= "</div>\n";

		return $opening ? $layout_opening : $layout_closing;
	}
}
