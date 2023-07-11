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

JLoader::import('adapter.http.response');

/**
 * HTTP client class.
 *
 * @since 10.1.23
 */
class JHttp
{
	/**
	 * Options for the HTTP client.
	 *
	 * @var JRegistry
	 */
	protected $options;

	/**
	 * Class constructor.
	 *
	 * @param 	JRegistry  $options   Client options object. If the registry contains any headers.* elements,
	 *                                these will be added to the request headers.
	 */
	public function __construct(JRegistry $options = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
	}

	/**
	 * Gets an option from the HTTP client.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed   The option value.
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Sets an option for the HTTP client.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}

	/**
	 * Method to send the OPTIONS command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since 	10.1.29
	 *
	 * @uses 	request()
	 */
	public function options($url, array $headers = null, $timeout = null)
	{
		if (is_null($headers))
		{
			$headers = array();
		}

		// use OPTIONS method
		$headers['method'] = 'OPTIONS';

		return $this->request('wp_remote_request', $url, $data, $headers, $timeout);
	}

	/**
	 * Method to send the HEAD command to the server.
	 *
	 * @param   mixed    $url      Path to the resource.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  JHttpResponse
	 *
	 * @uses 	request()
	 */
	public function head($url, array $headers = array(), $timeout = null)
	{
		return $this->request('wp_remote_head', $url, null, $headers, $timeout);
	}

	/**
	 * Method to send the GET command to the server.
	 *
	 * @param   mixed    $url      Path to the resource.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  JHttpResponse
	 *
	 * @uses 	request()
	 */
	public function get($url, array $headers = array(), $timeout = null)
	{
		return $this->request('wp_remote_get', $url, null, $headers, $timeout);
	}

	/**
	 * Method to send the POST command to the server.
	 *
	 * @param   mixed    $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  JHttpResponse
	 *
	 * @uses 	request()
	 */
	public function post($url, $data, array $headers = array(), $timeout = null)
	{
		return $this->request('wp_remote_post', $url, $data, $headers, $timeout);
	}

	/**
	 * Method to send the PUT command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   10.1.29
	 *
	 * @uses 	request()
	 */
	public function put($url, $data, array $headers = null, $timeout = null)
	{
		if (is_null($headers))
		{
			$headers = array();
		}

		// use PUT method
		$headers['method'] = 'PUT';

		return $this->request('wp_remote_request', $url, $data, $headers, $timeout);
	}

	/**
	 * Method to send the DELETE command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   10.1.29
	 *
	 * @uses 	request()
	 */
	public function delete($url, array $headers = null, $timeout = null)
	{
		if (is_null($headers))
		{
			$headers = array();
		}

		// use DELETE method
		$headers['method'] = 'DELETE';

		return $this->request('wp_remote_request', $url, $data, $headers, $timeout);
	}

	/**
	 * Method to send the TRACE command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @since   10.1.29
	 *
	 * @uses 	request()
	 */
	public function trace($url, array $headers = null, $timeout = null)
	{
		if (is_null($headers))
		{
			$headers = array();
		}

		// use DELETE method
		$headers['method'] = 'TRACE';

		return $this->request('wp_remote_request', $url, $data, $headers, $timeout);
	}

	/**
	 * Method to send the PATCH command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   10.1.29
	 *
	 * @uses 	request()
	 */
	public function patch($url, $data, array $headers = null, $timeout = null)
	{
		if (is_null($headers))
		{
			$headers = array();
		}

		// use PATCH method
		$headers['method'] = 'PATCH';

		return $this->request('wp_remote_request', $url, $data, $headers, $timeout);
	}

	/**
	 * Sends a request to the server and returns a HTTP response object.
	 *
	 * @param   string   $method     The HTTP method for sending the request.
	 * @param   mixef    $uri        The URI to the resource to request.
	 * @param   mixed    $data       Either an associative array or a string to be sent with the request.
	 * @param   array    $headers    An array of request headers to send with the request.
	 * @param   integer  $timeout    Read timeout in seconds.
	 *
	 * @return  JHttpResponse
	 *
	 * @throws  RuntimeException
	 *
	 * @uses 	fixHeaders()
	 */
	protected function request($method, $uri, $data = null, array $headers = array(), $timeout = null)
	{
		// look for headers set in the options
		$temp = (array) $this->options->get('headers');

		foreach ($temp as $key => $val)
		{
			if (!isset($headers[$key]))
			{
				$headers[$key] = $val;
			}
		}

		$args = array();

		// overwrite timeout value if specified
		if ($timeout)
		{
			$args['timeout'] = (int) $timeout;
		}
		else if (isset($headers['timeout']))
		{
			/**
			 * Use timeout specified within the headers instead.
			 *
			 * @since 10.1.31
			 */
			$args['timeout'] = (int) $headers['timeout'];
		}

		// add body to headers
		if ($data)
		{
			$args['body'] = $data;
		}

		if (!function_exists($method))
		{
			// throw exception as the requested method is not supported
			throw new RuntimeException(sprintf('HTTP method [%s] not found', $method), 404);
		}

		// adjust the headers from Joomla standards to WP
		$this->fixHeaders($headers);

		/**
		 * Copy headers within the top-level of the array.
		 *
		 * @since 10.1.29
		 */
		$headersLookup = array(
			'redirection',
			'httpversion',
			'blocking',
			'headers',
			'cookies',
			'body',
			'compress',
			'decompress',
			'sslverify',
			'sslcertificates',
			'stream',
			'filename',
			'limit_response_size',
		);

		// iterate directives
		foreach ($headersLookup as $directive)
		{
			// check if the headers use it
			if (isset($headers[$directive]))
			{
				// copy directive
				$args[$directive] = $headers[$directive];
			}
		}

		/**
		 * Put headers within the proper key.
		 *
		 * @since 10.1.29
		 */
		$args['headers'] = $headers;

		// invoke method
		$response = $method($uri, $args);

		// convert response
		return new JHttpResponse($response);
	}

	/**
	 * Adjusts the headers properties from Joomla standards for
	 * being used with WordPress framework.
	 * @link https://codex.wordpress.org/HTTP_API#Other_Arguments
	 *
	 * @param 	array  &$headers  The headers to fix.
	 *
	 * @return 	void
	 */
	protected function fixHeaders(array &$headers)
	{
		// lookup to adjust the headers, where the key is the
		// Joomla notation and the value is the correct attribute
		// that should be used on WordPress.
		$lookup = array(
			'userAgent' => 'user-agent',
		);

		foreach ($headers as $k => $v)
		{
			// check if the key is set
			if (isset($lookup[$k]))
			{
				// copy the value within the correct attribute
				$headers[$lookup[$k]] = $v;
				// unset the Joomla notation value
				unset($headers[$k]);
			}
		}
	}
}
