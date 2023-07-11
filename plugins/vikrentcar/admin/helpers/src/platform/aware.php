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
 * Declares all the helper methods that may differ between every supported platform.
 * 
 * @since 1.3
 */
abstract class VRCPlatformAware implements VRCPlatformInterface
{
	/**
	 * The platform URI handler.
	 * 
	 * @var VRCPlatformUriInterface
	 */
	private $uri;

	/**
	 * The platform mailer handler.
	 * 
	 * @var VRCPlatformMailerInstance
	 */
	private $mailer;

	/**
	 * The event dispatcher handler.
	 * 
	 * @var VRCPlatformDispatcherInstance
	 */
	private $dispatcher;

	/**
	 * Returns the URI helper instance.
	 *
	 * @return 	VRCPlatformUriInterface
	 */
	public function getUri()
	{
		if (is_null($this->uri))
		{
			// lazy creation
			$this->uri = $this->createUri();
		}

		// make sure we have a valid instance
		if (!$this->uri instanceof VRCPlatformUriInterface)
		{
			if (is_object($this->uri))
			{
				// extract class name from object
				$t = get_class($this->uri);
			}
			else
			{
				// fetch the type of the property
				$t = gettype($this->uri);
			}

			// nope, throw a "Not acceptable" 406 error
			throw new UnexpectedValueException(sprintf('The [%s] object is not a valid URI instance', $t), 406);
		}

		return $this->uri;
	}

	/**
	 * Returns the mail sender instance.
	 * 
	 * @return  VRCPlatformMailerInterface
	 */
	public function getMailer()
	{
		if (is_null($this->mailer))
		{
			// lazy creation
			$this->mailer = $this->createMailer();
		}

		// make sure we have a valid instance
		if (!$this->mailer instanceof VRCPlatformMailerInterface)
		{
			if (is_object($this->mailer))
			{
				// extract class name from object
				$t = get_class($this->mailer);
			}
			else
			{
				// fetch the type of the property
				$t = gettype($this->mailer);
			}

			// nope, throw a "Not acceptable" 406 error
			throw new UnexpectedValueException(sprintf('The [%s] object is not a valid mailer instance', $t), 406);
		}

		return $this->mailer;
	}

	/**
	 * Returns the event dispatcher instance.
	 * 
	 * @return  VRCPlatformDispatcherInterface
	 * 
	 * @since   1.3.0
	 */
	public function getDispatcher()
	{
		if (is_null($this->dispatcher))
		{
			// lazy creation
			$this->dispatcher = $this->createDispatcher();
		}

		// make sure we have a valid instance
		if (!$this->dispatcher instanceof VRCPlatformDispatcherInterface)
		{
			if (is_object($this->dispatcher))
			{
				// extract class name from object
				$t = get_class($this->dispatcher);
			}
			else
			{
				// fetch the type of the property
				$t = gettype($this->dispatcher);
			}

			// nope, throw a "Not acceptable" 406 error
			throw new UnexpectedValueException(sprintf('The [%s] object is not a valid dispatcher instance', $t), 406);
		}

		return $this->dispatcher;
	}

	/**
	 * Creates a new URI helper instance.
	 *
	 * @return  VRCPlatformUriInterface
	 */
	abstract protected function createUri();

	/**
	 * Creates a new mailer instance.
	 *
	 * @return  VRCPlatformMailerInterface
	 */
	abstract protected function createMailer();

	/**
	 * Creates a new event dispatcher instance.
	 * 
	 * @return  VRCPlatformDispatcherInterface
	 * 
	 * @since   1.3.0
	 */
	abstract protected function createDispatcher();
}
