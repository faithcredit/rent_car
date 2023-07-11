<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.rss
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Exception used to detect whether the customer didn't
 * explicitly choose to opt in to the RSS service.
 *
 * @since 10.1.31
 */
class JRssOptInException extends Exception
{
	/**
	 * Class constructor.
	 *
	 * @param 	string     $message   The error message.
	 * @param 	integer    $code      The error code.
	 * @param 	Exception  $previous  The previous stack exception.
	 */
	public function __construct($message = null, $code = 0, Exception $previous = null)
	{
		if (!$message)
		{
			// use default error message if not specified
			$message = 'Missing RSS opt in';
		}

		if (!$code)
		{
			// use default erro code if not specified
			$message = 400;
		}

		// construct exception
		parent::__construct($message, $code, $previous);
	}
}
