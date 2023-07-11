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
 * Implements the mailer interface for the Wordpress platform.
 * 
 * @since 1.3
 */
class VRCPlatformOrgWordpressMailer implements VRCPlatformMailerInterface
{
	/**
	 * Sends an e-mail through the pre-installed mailing system.
	 * 
	 * @param 	VRCMailWrapper  $mail  The e-mail encapsulation.
	 * 
	 * @return 	boolean         True on success, false otherwise.
	 */
	public function send(VRCMailWrapper $mail)
	{
		// sends through PHP mailer
		$service = new VRCMailServicePhpmailer();

		// prepare email content
		$this->prepare($mail);

		// send the e-mail
		return $service->send($mail);
	}

	/**
	 * Prepares the email content for the current platform.
	 * 
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function prepare(VRCMailWrapper $mail)
	{
		// get mail full content and replace wrapper symbols
		$mail_content = VRCMailParser::checkWrapperSymbols($mail->getContent());

		// parse conditional text rules (properties should be set by who calls this method)
		VikRentCar::getConditionalRulesInstance()->parseTokens($mail_content);

		// interpretes shortcodes contained within the full text
		$mail_content = do_shortcode($mail_content);

		// set manipulated content
		$mail->setContent($mail_content);

		// return the prepared email content
		return $mail_content;
	}
}
