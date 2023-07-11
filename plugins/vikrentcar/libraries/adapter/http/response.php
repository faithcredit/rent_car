<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.application
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * HTTP response data object class.
 *
 * @since 10.1.23
 */
class JHttpResponse
{
	/**
	 * The server response code.
	 *
	 * @var integer
	 */
	public $code;

	/**
	 * Response headers.
	 *
	 * @var array
	 */
	public $headers = array();

	/**
	 * Server response body.
	 *
	 * @var string
	 */
	public $body;

	/**
	 * Class constructor.
	 *
	 * @param 	mixed 	$response  The response in WP format.
	 */
	public function __construct($response)
	{
		// check if we have a response error
		if (is_wp_error($response))
		{
			$this->code = (int) $response->get_error_code();
			$this->body = (string) $response->get_error_message();
		}
		else
		{
			// cast response to array
			$response = (array) $response;

			// look for an HTTP code
			if (isset($response['response']['code']))
			{
				$this->code = (int) $response['response']['code'];
			}

			// look for the response body
			if (isset($response['body']))
			{
				$this->body = (string) $response['body'];
			}
			else
			{
				// otherwise use a stringified version of the whole response
				$this->body = print_r($response, true);
			}

			// look for the response headers
			if (isset($response['headers']))
			{
				// check if we have an array or a traversable object
				if (is_array($response['headers']) || (is_object($response['headers']) && $response['headers'] instanceof Traversable))
				{
					// iterate all the headers keys and copy them within the internal property
					foreach ($response['headers'] as $k => $v)
					{
						$this->headers[$k] = $v;
					}
				}
			}
		}
	}
}
