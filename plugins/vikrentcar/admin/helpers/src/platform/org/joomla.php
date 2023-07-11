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
 * Implements the Joomla platform interface.
 * 
 * @since 1.15
 */
class VRCPlatformOrgJoomla extends VRCPlatformAware
{
	/**
	 * Creates a new URI helper instance.
	 *
	 * @return  VRCPlatformUriInterface
	 */
	protected function createUri()
	{
		return new VRCPlatformOrgJoomlaUri;
	}

	/**
	 * Creates a new mailer instance.
	 *
	 * @return  VRCPlatformMailerInterface
	 */
	protected function createMailer()
	{
		return new VRCPlatformOrgJoomlaMailer;
	}

	/**
	 * Creates a new event dispatcher instance.
	 * 
	 * @return  VRCPlatformDispatcherInterface
	 * 
	 * @since   1.15.4
	 */
	protected function createDispatcher()
	{
		return new VRCPlatformOrgJoomlaDispatcher;
	}
}
