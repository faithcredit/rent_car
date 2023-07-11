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
interface VRCPlatformInterface
{
	/**
	 * Returns the URI helper instance.
	 *
	 * @return 	VRCPlatformUriInterface
	 */
	public function getUri();

	/**
	 * Returns the mail sender instance.
	 * 
	 * @return  VRCPlatformMailerInterface
	 */
	public function getMailer();

	/**
	 * Returns the event dispatcher instance.
	 * 
	 * @return  VRCPlatformDispatcherInterface
	 * 
	 * @since   1.3.10
	 */
	public function getDispatcher();
}
