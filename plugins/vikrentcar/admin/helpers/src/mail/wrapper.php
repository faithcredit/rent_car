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
 * Encapsulation of the e-mail data.
 * 
 * - Bind from array/object:
 * new VRCMailWrapper([
 *     'sender' => 'mail@domain.com',
 *     'recipient' => 'client@domain.com',
 *     'subject' => 'E-mail subject',
 *     'content' => '<p>This is the e-mail message.</p>',
 * ]);
 * 
 * - or:
 * VRCMailWrapper::getInstance()->bind([...]);
 * 
 * - Construct through setters:
 * VRCMailWrapper::getInstance() 
 *     ->setSender('mail@domain.com', 'Sender Name')
 *     ->addRecipient('client@domain.com')
 *     ->setSubject('E-mail subject')
 *     ->setContent('<p>This is the e-mail message.</p>');
 *
 * @since 1.3
 */
final class VRCMailWrapper
{
	/**
	 * Holds the sender name and e-mail address.
	 * 
	 * @var array|null
	 */
	private $sender;
	
	/**
	 * A list of recipients e-mail addresses.
	 * 
	 * @var array
	 */
	private $recipient = [];

	/**
	 * The reply-to e-mail address.
	 * 
	 * @var string|null
	 */
	private $reply;

	/**
	 * The BCC e-mail addresses.
	 * 
	 * @var array
	 */
	private $bcc = [];

	/**
	 * The e-mail subject.
	 * 
	 * @var string
	 */
	private $subject;

	/**
	 * The e-mail content.
	 * 
	 * @var string
	 */
	private $content;
	
	/**
	 * A list of attachments.
	 * 
	 * @var array
	 */
	private $attachments = [];

	/**
	 * Flag used to check whether the e-mail is HTML or plain text.
	 * 
	 * @var bool|null
	 */
	private $isHtml;

	/**
	 * Proxy used to immediately support chaining.
	 * 
	 * @param 	array|object  $data  The e-mail data to bind.
	 * 
	 * @return 	self
	 */
	public static function getInstance($data = [])
	{
		return new static($data);
	}

	/**
	 * Class constructor.
	 * 
	 * @param 	array|object  $data  The e-mail data to bind.
	 */
	public function __construct($data = [])
	{
		// create the e-mail wrapper
		$this->bind($data);
	}

	/**
	 * Binds the e-mail data.
	 * 
	 * @param 	array|object  $data  The e-mail data to bind.
	 * 
	 * @return 	self  This object to support chaining.
	 */
	public function bind($data)
	{
		if (!is_array($data) && !is_object($data))
		{
			// cannot accept the received argument
			throw new InvalidArgumentException(sprintf('Mail wrapper only accepts arrays or objects, %s given', gettype($data)), 406);
		}

		foreach ($data as $k => $v)
		{
			$k = strtolower($k);

			switch ($k)
			{
				case 'ishtml':
				case 'is_html':
					$method = 'setHtml';
					break;

				default:
					$method = 'set' . ucfirst($k);
			}

			if (method_exists($this, $method))
			{
				// invoke default setter
				call_user_func_array([$this, $method], [$v]);
			}
		}

		return $this;
	}

	/**
	 * Sets the e-mail sender.
	 * 
	 * @param 	string 	$address  The sender e-mail address.
	 * @param 	string 	$name     The sender name.
	 * 
	 * @return 	self    This object to support chaining.
	 */
	public function setSender($address, $name = null)
	{
		if (is_array($address))
		{
			if (count($address) > 1)
			{
				// extract name from array
				$name = array_pop($address);
			}

			// extract address from array
			$address = array_shift($address);
		}
		
		$this->sender = [
			'address' => $address,
			'name'    => $name,
		];

		return $this;
	}

	/**
	 * Returns the sender e-mail address.
	 * 
	 * @return 	string|null
	 */
	public function getSenderMail()
	{
		return $this->sender && !empty($this->sender['address']) ? $this->sender['address'] : VRCFactory::getConfig()->get('senderemail');
	}

	/**
	 * Returns the sender name.
	 * 
	 * @return 	string|null
	 */
	public function getSenderName()
	{
		return $this->sender ? $this->sender['name'] : null;
	}

	/**
	 * Sets the e-mail recipient(s).
	 * 
	 * @param 	string|array  $address  Either an array or a string.
	 * 
	 * @return 	self  This object to support chaining.
	 */
	public function setRecipient($address)
	{
		// reset recipient
		$this->recipient = [];

		// add recipient(s)
		return $this->addRecipient($address);
	}

	/**
	 * Adds a new recipient.
	 * 
	 * @param 	string|array  $address  Either an array or a string.
	 * 
	 * @return 	self  This object to support chaining.
	 */
	public function addRecipient($address)
	{
		foreach ((array) $address as $addr)
		{
			if ($addr)
			{
				$this->recipient[] = $addr;
			}
		}

		return $this;
	}

	/**
	 * Returns the list of recipients.
	 * 
	 * @return 	array
	 */
	public function getRecipient()
	{
		return $this->recipient;
	}

	/**
	 * Sets the reply-to e-mail address.
	 * 
	 * @param 	string 	$address  The reply-to e-mail address.
	 * 
	 * @return 	self    This object to support chaining.
	 */
	public function setReply($address)
	{
		$this->reply = $address;

		return $this;
	}

	/**
	 * Returns the reply-to e-mail address.
	 * 
	 * @return 	string|null
	 */
	public function getReply()
	{
		return $this->reply;
	}

	/**
	 * Sets the e-mail BCC(s).
	 * 
	 * @param 	string|array  $address  Either an array or a string.
	 * 
	 * @return 	self  This object to support chaining.
	 */
	public function setBcc($address)
	{
		// reset BCC
		$this->bcc = [];

		// add BCC(s)
		return $this->addBcc($address);
	}

	/**
	 * Adds a new BCC e-mail address.
	 * 
	 * @param 	string|array  $address  Either an array or a string.
	 * 
	 * @return 	self  This object to support chaining.
	 */
	public function addBcc($address)
	{
		foreach ((array) $address as $addr)
		{
			if ($addr)
			{
				$this->bcc[] = $addr;
			}
		}

		return $this;
	}

	/**
	 * Returns the list of BCC(s).
	 * 
	 * @return 	array
	 */
	public function getBcc()
	{
		return $this->bcc;
	}

	/**
	 * Sets the e-mail subject.
	 * 
	 * @param 	string 	$subject  The e-mail subject.
	 * 
	 * @return 	self    This object to support chaining.
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Returns the e-mail subject.
	 * 
	 * @return 	string|null
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * Sets the e-mail content.
	 * 
	 * @param 	string 	$content  The e-mail content.
	 * 
	 * @return 	self    This object to support chaining.
	 */
	public function setContent($content)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Returns the e-mail content.
	 * 
	 * @return 	string|null
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Sets the e-mail attachments.
	 * 
	 * @param 	array  $attachments  An array of files.
	 * 
	 * @return 	self   This object to support chaining.
	 */
	public function setAttachments($attachments = null)
	{
		// reset attachments
		$this->attachments = [];

		if (empty($attachments))
		{
			return $this;
		}

		// add attachments
		return $this->addAttachment($attachments);
	}

	/**
	 * Registers a new attachment file.
	 * 
	 * @param 	string|array  $attachment  Either an array or a string.
	 * 
	 * @return 	self  This object to support chaining.
	 */
	public function addAttachment($attachment)
	{
		foreach ((array) $attachment as $file)
		{
			if ($file && is_file($file) && !in_array($file, $this->attachments))
			{
				$this->attachments[] = $file;
			}
		}

		return $this;
	}

	/**
	 * Returns the list of attachments.
	 * 
	 * @return 	array
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}

	/**
	 * Sets the document type of the e-mail.
	 * 
	 * @param 	bool|null  $is  True to use text/html, false to use
	 *                          text/plain, null for auto-detection.
	 * 
	 * @return 	self  This object to support chaining.
	 */
	public function setHtml($is = true)
	{
		$this->isHtml = is_null($is) ? null : (bool) $is;

		return $this;
	}

	/**
	 * Checks whether the document type is text/html or not.
	 * 
	 * @return 	bool  True for text/html, false for text/plain.
	 */
	public function isHtml()
	{
		if (is_null($this->isHtml))
		{
			// auto-detect whether the content supports HTML tags
			return preg_match("/<\/[a-z0-9_\-]+>/i", (string) $this->content);
		}

		return $this->isHtml;
	}
}
