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
 * Implements the e-mail sending function through the PHPMailer lib.
 * 
 * @since 1.3
 */
class VRCMailServicePhpmailer implements VRCMailService
{
	/**
	 * Sends an e-mail through the pre-installed mailing system.
	 * 
	 * @param   VRCMailWrapper  $mail  The e-mail encapsulation.
	 * 
	 * @return  boolean         True on success, false otherwise.
	 */
	public function send(VRCMailWrapper $mail)
	{
		$is_html = false;

		$content = $mail->getContent();
		
		// check if we have an HTML document
		if ($mail->isHtml())
		{
			// wrap content into a valid HTML document
			$content = "<html>\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head>\n<body>$content</body>\n</html>";

			$is_html = true;
		}

		// create PHPMailer
		$mailer = JFactory::getMailer();

		// set up e-mail sender
		$mailer->setSender([$mail->getSenderMail(), $mail->getSenderName()]);

		// set up recipient
		$mailer->addRecipient($mail->getRecipient());

		if ($mail->getReply())
		{
			// set up reply-to
			$mailer->addReplyTo($mail->getReply());
		}

		if ($bcc = $mail->getBcc())
		{
			// set BCC(s)
			$mailer->addBcc($bcc);
		}

		// set up e-mail subject
		$mailer->setSubject($mail->getSubject());

		// set up e-mail content
		$mailer->setBody($content);

		// set up HTML tag
		$mailer->isHTML($is_html);

		// set up attachments
		foreach ($mail->getAttachments() as $file)
		{
			$mailer->addAttachment($file);
		}

		// always use Base64 encoding
		$mailer->Encoding = 'base64';

		// send e-mail address
		return $mailer->Send();
	}
}
