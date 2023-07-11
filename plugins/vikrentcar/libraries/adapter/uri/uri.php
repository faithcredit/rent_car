<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.uri
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * URI adapter class.
 *
 * @since 10.0
 */
class JUri
{
	/**
	 * A list of URI instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * The base URI.
	 *
	 * @var array
	 */
	protected static $base = array();

	/**
	 * The root URI.
	 *
	 * @var array
	 */
	protected static $root = array();

	/**
	 * The current URI.
	 *
	 * @var string
	 */
	protected static $current = null;

	/**
	 * The full URI string.
	 *
	 * @var string
	 */
	protected $uri = null;

	/**
	 * The URI scheme.
	 *
	 * @var string
	 */
	protected $scheme = null;

	/**
	 * The URI host.
	 *
	 * @var string
	 */
	protected $host = null;

	/**
	 * The URI port.
	 *
	 * @var integer
	 */
	protected $port = null;

	/**
	 * The URI username.
	 *
	 * @var string
	 */
	protected $user = null;

	/**
	 * The URI password.
	 *
	 * @var string
	 */
	protected $pass = null;

	/**
	 * The URI path.
	 *
	 * @var string
	 */
	protected $path = null;

	/**
	 * The URI anchor.
	 *
	 * @var string
	 */
	protected $fragment = null;

	/**
	 * The URI query.
	 *
	 * @var array
	 */
	protected $query = array();

	/**
	 * Returns the base URI for the request.
	 *
	 * @param   boolean  $pathonly  If false, prepend the scheme, host and port information.
	 *
	 * @return  string   The base URI string
	 */
	public static function base($pathonly = false)
	{
		$sign = serialize($pathonly);

		if (!isset(static::$base[$sign]))
		{
			static::$base[$sign] = new static(rtrim(home_url('', $pathonly ? 'relative' : null), '/') . '/');
		}

		// MUST return a string to avoid the manipulation of the base constant
		return (string) static::$base[$sign];
	}

	/**
	 * Returns the root URI for the request.
	 *
	 * @param   boolean  $pathonly  If false, prepend the scheme, host and port information.
	 * @param   string   $path      The URI path.
	 *
	 * @return  string   The root URI string.
	 */
	public static function root($pathonly = false, $path = null)
	{
		$sign = serialize(array($pathonly, $path));

		if (!isset(static::$root[$sign]))
		{
			// get base URI
			$uri = static::base($pathonly);

			// create a new URI object
			static::$root[$sign] = new static($uri);

			if ($path)
			{
				// concat the path to the uri object
				$path = '/' . ltrim($path, '/');
				static::$root[$sign]->path = $path;
			}
		}

		// MUST return a string to avoid the manipulation of the base constant
		return (string) static::$root[$sign];
	}

	/**
	 * Returns the URL for the request, minus the query.
	 *
	 * @return  string 	The current URI string.
	 */
	public static function current()
	{
		if (static::$current === null)
		{
			/**
			 * Do not use any filters to prevent issues with sanitizing.
			 * The string-type filter was stripping url-encoded characters.
			 * 
			 * @since 10.1.38
			 */
			$path = JFactory::getApplication()->input->server->getRaw('REQUEST_URI', '');
			
			static::$current = (string) static::root(false, $path);
		}

		return static::$current;
	}

	/**
	 * Returns the global JUri object, only creating it if it doesn't already exist.
	 *
	 * @param   string  $uri  The URI to parse (by default current URI).
	 *
	 * @return  JUri  	The URI object.
	 */
	public static function getInstance($uri = null)
	{
		if (is_null($uri))
		{
			$uri = static::current();
		}

		// cast to string because we may access a JUri object
		$uri = (string) $uri;

		if (!isset(static::$instances[$uri]))
		{
			static::$instances[$uri] = new static($uri);
		}

		return static::$instances[$uri];
	}

	/**
	 * Class constructor.
	 *
	 * @param 	string 	$uri 	The URI.
	 *
	 * @uses 	parse()
	 */
	public function __construct($uri)
	{
		$this->parse($uri);
	}

	/**
	 * Parse a given URI and populate the class fields.
	 *
	 * @param   string   $uri  The URI string to parse.
	 *
	 * @return  boolean  True on success.
	 */
	protected function parse($uri)
	{
		// set the original URI to fall back on
		$this->uri = $uri;

		// parse the URI and populate the object fields
		$parts = parse_url($uri);

		// we need to replace &amp; with & for parse_str to work right
		if (isset($parts['query']) && strpos($parts['query'], '&amp;'))
		{
			$parts['query'] = str_replace('&amp;', '&', $parts['query']);
		}

		$this->scheme   = isset($parts['scheme']) 	? $parts['scheme'] 	 : null;
		$this->user     = isset($parts['user']) 	? $parts['user'] 	 : null;
		$this->pass     = isset($parts['pass']) 	? $parts['pass'] 	 : null;
		$this->host     = isset($parts['host']) 	? $parts['host'] 	 : null;
		$this->port     = isset($parts['port']) 	? $parts['port'] 	 : null;
		$this->path     = isset($parts['path']) 	? $parts['path'] 	 : null;
		$this->fragment = isset($parts['fragment']) ? $parts['fragment'] : null;

		// parse the query
		if (isset($parts['query']))
		{
			parse_str($parts['query'], $this->query);
		}

		return (bool) $parts;
	}

	/**
	 * Magic method to get the string representation of the URI object.
	 *
	 * @return 	string 	The URI.
	 *
	 * @uses 	toString()
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Returns full URI string.
	 *
	 * @param   array   $parts  An array specifying the parts to get.
	 *
	 * @return  string  The rendered URI string.
	 *
	 * @uses 	getQuery()
	 */
	public function toString(array $parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
	{
		// make sure the query is created
		$query = $this->getQuery();

		$uri = '';
		$uri .= in_array('scheme', $parts) 	 ? (!empty($this->scheme) ? $this->scheme . '://' : '') : '';
		$uri .= in_array('user', $parts) 	 ? $this->user : '';
		$uri .= in_array('pass', $parts) 	 ? (!empty($this->pass) ? ':' : '') . $this->pass . (!empty($this->user) ? '@' : '') : '';
		$uri .= in_array('host', $parts) 	 ? $this->host : '';
		$uri .= in_array('port', $parts) 	 ? (!empty($this->port) ? ':' : '') . $this->port : '';
		$uri .= in_array('path', $parts) 	 ? $this->path : '';
		$uri .= in_array('query', $parts) 	 ? (!empty($query) ? '?' . $query : '') : '';
		$uri .= in_array('fragment', $parts) ? (!empty($this->fragment) ? '#' . $this->fragment : '') : '';

		return $uri;
	}

	/**
	 * Sets the URI host.
	 *
	 * @param   string  $host  The URI host.
	 *
	 * @return  void
	 *
	 * @since   10.1.35
	 */
	public function setHost($host)
	{
		$this->host = $host;
	}

	/**
	 * Checks if a query variable exists.
	 *
	 * @param   string   $name  Name of the query variable to check.
	 *
	 * @return  boolean  True if the variable exists.
	 */
	public function hasVar($name)
	{
		return array_key_exists($name, $this->query);
	}

	/**
	 * Returns a query variable by name.
	 *
	 * @param   string  $name     Name of the query variable to get.
	 * @param   string  $default  Default value to return if the variable is not set.
	 *
	 * @return  array   Query variables.
	 *
	 * @uses 	hasVar()
	 */
	public function getVar($name, $default = null)
	{
		if ($this->hasVar($name))
		{
			return $this->query[$name];
		}

		return $default;
	}

	/**
	 * Adds a query variable and value, replacing the value if it
	 * already exists and returning the old value.
	 *
	 * @param   string  $name   Name of the query variable to set.
	 * @param   string  $value  Value of the query variable.
	 *
	 * @return  string  Previous value for the query variable.
	 *
	 * @uses 	getVar()
	 * @uses 	delVar()
	 */
	public function setVar($name, $value)
	{
		$tmp = $this->getVar($name, null);

		if (is_null($value))
		{
			$this->delVar($name);
		}
		else
		{
			$this->query[$name] = $value;
		}

		return $tmp;
	}

	/**
	 * Removes an item from the query string variables if it exists.
	 *
	 * @param   string  $name  Name of variable to remove.
	 *
	 * @return  void
	 *
	 * @uses 	hasVar()
	 */
	public function delVar($name)
	{
		if ($this->hasVar($name))
		{
			unset($this->query[$name]);
		}
	}

	/**
	 * Returns the URI query as string.
	 *
	 * @param 	boolean  $to_array 	True to return the query as associative array.
	 * 								False to return an HTTP query.
	 *
	 * @return 	mixed 	 The query string or array.
	 */
	public function getQuery($to_array = false)
	{
		if ($to_array)
		{
			return $this->query;
		}

		/**
		 * Decode special characters after building the
		 * query string.
		 *
		 * @since 10.1.29
		 */
		return urldecode(http_build_query($this->query, '', '&'));
	}

	/**
	 * Sets the query to a supplied string in format:
	 * foo=bar&x=y
	 *
	 * @param   mixed  $query  The query string or array.
	 *
	 * @return  void
	 *
	 * @since   10.1.29
	 */
	public function setQuery($query)
	{
		if (is_array($query))
		{
			$this->query = $query;
		}
		else
		{
			if (strpos($query, '&amp;') !== false)
			{
				$query = str_replace('&amp;', '&', $query);
			}

			parse_str($query, $this->query);
		}
	}

	/**
	 * Get URI scheme (protocol).
	 * ie. http, https, ftp, etc...
	 *
	 * @return  string  The URI scheme.
	 *
	 * @since   10.1.7
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * Gets URI username.
	 *
	 * @return  string  The URI username (null if not specified).
	 *
	 * @since   10.1.7
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Gets URI password.
	 *
	 * @return  string  The URI password (null if not specified).
	 *
	 * @since   10.1.7
	 */
	public function getPass()
	{
		return $this->pass;
	}

	/**
	 * Gets URI host.
	 *
	 * @return  string  The URI hostname or ip (null if not specified).
	 *
	 * @since   10.1.7
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Gets URI port. 
	 *
	 * @return  integer  The URI port number (null if not specified).
	 *
	 * @since   10.1.7
	 */
	public function getPort()
	{
		return (isset($this->port)) ? $this->port : null;
	}

	/**
	 * Gets the URI path string.
	 *
	 * @return  string  The URI path string.
	 *
	 * @since   10.1.7
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Gets the URI anchor string (everything after the "#").
	 *
	 * @return  string  The URI anchor string.
	 *
	 * @since   10.1.7
	 */
	public function getFragment()
	{
		return $this->fragment;
	}

	/**
	 * Checks whether the current URI is using HTTPS.
	 *
	 * @return  boolean  True if using SSL via HTTPS, otherwise false.
	 *
	 * @uses 	getScheme()
	 *
	 * @since   10.1.7
	 */
	public function isSsl()
	{
		return $this->getScheme() == 'https' ? true : false;
	}
}
