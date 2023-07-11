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
 * Implements the WordPress platform interface.
 * 
 * @since 1.3
 */
class VRCPlatformOrgWordpress extends VRCPlatformAware
{
	/**
	 * Creates a new URI helper instance.
	 *
	 * @return  VRCPlatformUriInterface
	 */
	protected function createUri()
	{
		return new VRCPlatformOrgWordpressUri;
	}

	 /**
	 * Creates a new mailer instance.
	 *
	 * @return  VRCPlatformMailerInterface
	 */
	protected function createMailer()
	{
		return new VRCPlatformOrgWordpressMailer;
	}

	/**
	 * Creates a new event dispatcher instance.
	 * 
	 * @return  VRCPlatformDispatcherInterface
	 * 
	 * @since   1.3.0
	 */
	protected function createDispatcher()
	{
		return new VRCPlatformOrgWordpressDispatcher;
	}
}
