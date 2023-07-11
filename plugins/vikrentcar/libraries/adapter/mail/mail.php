<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.mail
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Email Class.
 * Provides a common interface to send emails.
 *
 * @since 10.0
 */
class JMail
{
	/**
	 * A list of mail instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * The e-mail from address/name.
	 *
	 * @var string
	 */
	protected $from = null;

	/**
	 * The e-mail subject.
	 *
	 * @var string
	 */
	protected $subject = null;

	/**
	 * The e-mail body message.
	 *
	 * @var string
	 */
	protected $body = null;

	/**
	 * The e-mail recipients list.
	 *
	 * @var array
	 */
	protected $recipients = array();

	/**
	 * The e-mail carbon copy list.
	 *
	 * @var array
	 */
	protected $cc = array();

	/**
	 * The e-mail blind carbon copy list.
	 *
	 * @var array
	 */
	protected $bcc = array();

	/**
	 * The e-mail reply-to list.
	 *
	 * @var array
	 */
	protected $reply_to = array();

	/**
	 * A list of attachments file.
	 *
	 * @var array
	 */
	protected $attachments = array();

	/**
	 * Flag to define if the content should be Plain Text or HTML.
	 *
	 * @var boolean
	 */
	protected $isHtml = false;

	/**
	 * Class constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * Magic method set to define certain object properties.
	 * 
	 * @param 	string 	$name 	the property name
	 * @param 	mixed 	$value 	the value for the property
	 * 
	 * @return 	void
	 * 
	 * @since 	10.1.6
	 */
	public function __set($name, $value)
	{
		if (strcasecmp($name, 'SMTPOptions'))
		{
			return;
		}

		// hook to the PHPMailer init action
		add_action('phpmailer_init', function(&$phpmailer) use ($value)
		{	
			if (!$phpmailer instanceof PHPMailer)
			{
				// safety check
				return $phpmailer;
			}

			$phpmailer->SMTPOptions = $value;
		});
	}

	/**
	 * Returns the global email object, only creating it if it doesn't already exist.
	 *
	 * @param   string  $id  The id string for the Mail instance.
	 *
	 * @return  JMail  	The global Mail object.
	 */
	public static function getInstance($id = 'wordpress')
	{
		if (empty(static::$instances[$id]))
		{
			static::$instances[$id] = new static();
		}

		return static::$instances[$id];
	}

	/**
	 * Send the mail.
	 *
	 * @return  boolean  True on success, otherwise false.
	 */
	public function Send()
	{
		$to = implode(',', $this->recipients);

		$headers = array();

		if (!empty($this->from))
		{
			$headers[] = "From: {$this->from}";
		}

		if (!empty($this->reply_to))
		{
			foreach ($this->reply_to as $replyto)
			{
				$headers[] = "Reply-to: {$replyto}";
			}
		}

		if (!empty($this->cc))
		{
			foreach ($this->cc as $cc)
			{
				$headers[] = "Cc: {$cc}";
			}
		}

		if (!empty($this->bcc))
		{
			foreach ($this->bcc as $bcc)
			{
				$headers[] = "Bcc: {$bcc}";
			}
		}

		if ($this->isHtml)
		{
			$headers[] = "Content-Type: text/html; charset=UTF-8";
		}

		return wp_mail($to, $this->subject, $this->body, $headers, $this->attachments);
	}

	/**
	 * Set the From and FromName properties.
	 *
	 * @param   string   $address  The sender email address.
	 * @param   string   $name     The sender name.
	 * @param   boolean  $auto     Whether to also set the Sender address (@unused).
	 *
	 * @return  boolean
	 */
	public function setFrom($address, $name = '', $auto = true)
	{
		$this->from = $address;

		if ($name)
		{
			$this->from = "$name <{$this->from}>";
		}

		return true;
	}

	/**
	 * Sets the email sender.
	 *
	 * @param   mixed  	$from  The e-mail address or an array containing sender address and name.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	setFrom()
	 */
	public function setSender($from)
	{
		if (!is_array($from))
		{
			$from = array($from);
		}

		call_user_func_array(array($this, 'setFrom'), $from);

		return $this;
	}

	/**
	 * Sets the email subject.
	 *
	 * @param   string  $subject  Subject of the email.
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Sets the email body.
	 *
	 * @param   string  $content  Body of the email.
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function setBody($content)
	{
		$this->body = $content;

		return $this;
	}

	/**
	 * Adds elements to the email.
	 *
	 * @param   mixed   $recipient  Either a string or array of strings [email address(es)].
	 * @param   mixed   $name       Either a string or array of strings [name(s)].
	 * @param 	string 	$property 	The property name to which attach the element.
	 *
	 * @return  self 	This object to support chaining.
	 */
	protected function add($recipient, $name = '', $prop = 'recipients')
	{
		if (is_array($recipient))
		{
			for ($i = 0; $i < count($recipient); $i++)
			{
				$this->add($recipient[$i], (is_array($name) ? $name[$i] : $name), $prop);
			}
		}
		else
		{
			if ($name)
			{
				$recipient = "{$name} <{$recipient}>";
			}

			$this->{$prop}[] = $recipient;
		}

		return $this;
	}

	/**
	 * Adds recipients to the email.
	 *
	 * @param   mixed   $recipient  Either a string or array of strings [email address(es)].
	 * @param   mixed   $name       Either a string or array of strings [name(s)].
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	add()
	 */
	public function addRecipient($recipient, $name = '')
	{
		$this->add($recipient, $name, 'recipients');
	}

	/**
	 * Adds carbon copy recipients to the email
	 *
	 * @param   mixed   $cc    Either a string or array of strings [email address(es)].
	 * @param   mixed   $name  Either a string or array of strings [name(s)].
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	add()
	 */
	public function addCc($cc, $name = '')
	{
		return $this->add($cc, $name, 'cc');
	}

	/**
	 * Adds blind carbon copy recipients to the email
	 *
	 * @param   mixed   $bcc   Either a string or array of strings [email address(es)]
	 * @param   mixed   $name  Either a string or array of strings [name(s)]
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	add()
	 */
	public function addBcc($bcc, $name = '')
	{
		return $this->add($bcc, $name, 'bcc');
	}

	/**
	 * Adds file attachment to the email
	 *
	 * @param   mixed   $path  	Either a string or array of strings [filenames].
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function addAttachment($path)
	{
		if (!is_array($path))
		{
			$path = array($path);
		}

		foreach ($path as $file)
		{
			$this->attachments[] = $file;
		}

		return $this;
	}

	/**
	 * Adds Reply to email address(es) to the email.
	 *
	 * @param   mixed 	$replyto  Either a string or array of strings [email address(es)].
	 * @param   mixed 	$name     Either a string or array of strings [name(s)].
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	add()
	 */
	public function addReplyTo($replyto, $name = '')
	{
		return $this->add($replyto, $name, 'reply_to');
	}

	/**
	 * Sets message type to HTML.
	 *
	 * @param   boolean  $ishtml  Boolean true or false.
	 *
	 * @return  self 	 This object to support chaining.
	 *
	 */
	public function isHtml($ishtml = true)
	{
		$this->isHtml = $ishtml;

		return $this;
	}

	/**
	 * Use SMTP for sending the email
	 *
	 * @param   string   $auth    SMTP Authentication [optional]
	 * @param   string   $host    SMTP Host [optional]
	 * @param   string   $user    SMTP Username [optional]
	 * @param   string   $pass    SMTP Password [optional]
	 * @param   string   $secure  Use secure methods
	 * @param   integer  $port    The SMTP port
	 *
	 * @return  boolean  True on success
	 *
	 * @since   10.1.6
	 */
	public function useSmtp($auth = null, $host = null, $user = null, $pass = null, $secure = null, $port = 25)
	{
		// hook to the PHPMailer init action
		add_action('phpmailer_init', function(&$phpmailer) use ($auth, $host, $user, $pass, $secure, $port)
		{
			if (!$phpmailer instanceof PHPMailer)
			{
				// safety check
				return $phpmailer;
			}

			if ($auth === null || $host === null || $user === null || $pass === null)
			{
				// do not interfere if the plugin parameters are null
				return $phpmailer;
			}

			// Tell PHPMailer to use SMTP
			$phpmailer->isSMTP();

			// Set the hostname of the mail server
			$phpmailer->Host = $host;

			// Set the SMTP port number
			$phpmailer->Port = (int)$port;

			// Whether to use SMTP authentication
			$phpmailer->SMTPAuth = true;

			// Username to use for SMTP authentication
			$phpmailer->Username = $user;

			//Password to use for SMTP authentication
			$phpmailer->Password = $pass;

			if ($secure == 'ssl' || $secure == 'tls')
			{
				$phpmailer->SMTPSecure = $secure;
			} else {
				$phpmailer->SMTPAutoTLS = false;
			}

			return $phpmailer;
		});
		
		return true;
	}
}
