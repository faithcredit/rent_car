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
 * This adapter is required to wrap common wordpress functions
 * within the CMSApplication Joomla interface.
 * This is helpful to improve the portability between Joomla and Wordpress.
 *
 * @since 10.0
 */
class JApplication
{
	/**
	 * Input handler for REQUEST manipulation.
	 *
	 * @var JInput
	 */
	private $input = null;

	/**
	 * The application client name.
	 *
	 * @var string
	 */
	private $name = null;

	/**
	 * Flag to check if the redirect is going to be made via javascript.
	 *
	 * @var boolean
	 */
	private $jsRedirect = false;

	/**
	 * The HTTP header status code.
	 * 
	 * @var integer
	 * @since 10.1.40
	 */
	private $status = null;

	/**
	 * Class constructor.
	 *
	 * @param 	JInput 	$input 	The input handler.
	 */
	public function __construct($input = null)
	{
		// setup application input
		if (is_null($input))
		{
			$this->input = new JInput;
		}
		else
		{
			$this->input = $input;
		}

		// get current page
		global $pagenow;

		if (!$pagenow)
		{
			/**
			 * Current page not yet registered, we are probably under a multi-site,
			 * because the plugins_loaded hook may run before loading vars.php file.
			 *
			 * For this reason, we should auto-fill this global variable in advance
			 * by ourselves, simply by checking whether the request URL ends with
			 * admin-ajax.php
			 *
			 * @since 10.1.33
			 */
			$self = $this->input->server->getString('PHP_SELF');

			if (preg_match("/\/admin-ajax\.php$/i", $self))
			{
				// we reached the admin-ajax.php file, flag it
				$pagenow = 'admin-ajax.php';
			}
		}
		
		/**
		 * Set application client according to the side we
		 * are located. In case of AJAX end-point, we should
		 * fetch the client according to a reserved key
		 * that should be set in request.
		 *
		 * @since 10.1.31
		 */
		if ($pagenow !== 'admin-ajax.php')
		{
			// rely on the location of the file
			$this->name = is_admin() && $pagenow != 'admin-post.php' ? 'administrator' : 'site';
		}
		else
		{
			// rely on the AJAX reserved key
			$this->name = $this->input->get('vik_ajax_client', 'administrator');
		}
	}

	/**
	 * Magic method to access private properties.
	 *
	 * @param 	string 	$name 	The property to access.
	 *
	 * @return 	mixed 	The property.
	 */
	public function __get($name)
	{
		if ($name == 'input')
		{
			return $this->input;	
		}

		return null;
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param 	string  $key 	The name of the property.
	 * @param 	mixed   $def 	The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 */
	public function get($key, $def = null)
	{
		/**
		 * The configuration is now parsed within a separated object.
		 *
		 * @see 	JConfig
		 * @since 	10.1.4
		 */
		return JFactory::getConfig()->get($key, $def);
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @uses 	get()
	 */
	public function getCfg($key, $def = null)
	{
		return $this->get($key, $def);
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param 	string 	$key 	  The name of the property.
	 * @param 	mixed 	$val 	  The value of the property to set (optional).
	 * @param 	mixed 	$network  An optional flag to check whether the option
	 * 							  should be updated for the current blog or for
	 * 							  a different one (@since 10.1.31).
	 * 							  - false  only the current blog will be updated;
	 * 							  - true   all the network blogs will be updated;
	 * 							  - int    only the specified blog will be updated.
	 *
	 * @return  mixed   Previous value of the property.
	 *
	 * @uses 	get()
	 */
	public function set($key, $val = null, $network = false)
	{
		$prev = $this->get($key);

		if ($network === false || !is_multisite())
		{
			// use default function
			update_option($key, $val);
		}
		else
		{
			if ($network === true)
			{
				// get all network sites
				$sites = array_map(function($site)
				{
					// take only the blog ID
					return $site->blog_id;
				}, get_sites());
			}
			else
			{
				// create a list with the specified blog ID
				$sites = array((int) $network);
			}

			// update all existing networks
			foreach ($sites as $blog_id)
			{
				// switch to blog
				switch_to_blog($blog_id);

				// update network option
				update_option($key, $val);

				// restore previous blog
				restore_current_blog();
			}
		}

		return $prev;
	}

	/**
	 * Is admin interface?
	 *
	 * @return  boolean  True if this application is administrator.
	 *
	 * @uses 	isClient()
	 */
	public function isAdmin()
	{
		return $this->isClient('administrator');
	}

	/**
	 * Is site interface?
	 *
	 * @return  boolean  True if this application is site.
	 *
	 * @uses 	isClient()
	 */
	public function isSite()
	{
		return $this->isClient('site');
	}

	/**
	 * Check the client interface by name.
	 *
	 * @param   string   $identifier  String identifier for the application interface.
	 *
	 * @return  boolean  True if this application is of the given type client interface.
	 */
	public function isClient($identifier)
	{
		return $this->name === $identifier;
	}

	/**
	 * Forces the application to run under the specified client.
	 *
	 * @param   string   $identifier  String identifier for the application interface.
	 *
	 * @return  void
	 * 
	 * @since 	10.1.39
	 */
	public function setClient($identifier)
	{
		$this->name = $identifier;
	}

	/**
	 * Gets a user state.
	 *
	 * @param 	string 	$key 	  The key of the user state.
	 * @param 	mixed 	$default  The default value for the state if not found.
	 *
	 * @return  mixed 	The user state.
	 *
	 * @since 	10.1.15
	 */
	public function getUserState($key, $default = null)
	{
		$session = JFactory::getSession();

		// extract user state from session
		return $session->get($key, $default, 'jsession.userstate');
	}

	/**
	 * Sets the value of a user state variable.
	 *
	 * @param   string  $key    The key of the user state.
	 * @param   mixed   $value  The value of the variable.
	 *
	 * @return  mixed   The previous state, if one existed.
	 *
	 * @since   10.1.15
	 */
	public function setUserState($key, $value)
	{
		// get previous state, if any
		$old = $this->getUserState($key);

		$session = JFactory::getSession();

		// update session with specified state
		$session->set($key, $value, 'jsession.userstate');

		// return previous state
		return $old;
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @param 	string 	$key 	  The key of the user state variable.
	 * @param 	string 	$request  The name of the variable passed in a request.
	 * @param 	mixed 	$default  The default value for the variable if not found.
	 * @param 	string 	$type 	  Filter for the variable.
	 *
	 * @return  mixed 	The request user state.
	 *
	 * @uses 	getUserState()
	 * @uses 	setUserState()
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none')
	{
		// try to get value from the request
		$val = $this->input->get($request, null, $type);

		if (!is_null($val))
		{
			// the value exists, register the user state for later use and return it
			$this->setUserState($key, $val);

			return $val;
		}

		// Otherwise try to access the current user state.
		// Returns default value if user state was not previously set.
		return $this->getUserState($key, $default);
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type (success, notice, warning or error).
	 *
	 * @return  void
	 */
	public function enqueueMessage($msg, $type = 'success')
	{
		$session = JFactory::getSession();

		// use a different namespace for each application client
		$namespace = 'jsession.' . $this->name . '.system';

		// create system message object
		$obj = new stdClass;
		$obj->message = $msg;
		$obj->type 	  = $type;

		// get queue from the session (an empty array if not set)
		$queue = $session->get('messagesqueue', array(), $namespace);

		// push the object only if it is not already in the queue
		if (!in_array($obj, $queue))
		{
			$queue[] = $obj;
			$session->set('messagesqueue', $queue, $namespace);
		}
	}

	/**
	 * Returns the queue containing the system messages.
	 *
	 * @return 	array 	The messages list.
	 */
	public function getMessagesQueue()
	{
		$session = JFactory::getSession();

		// use a different namespace for each application client
		$namespace = 'jsession.' . $this->name . '.system';

		// get queue from the session (an empty array if not set)
		$queue = $session->get('messagesqueue', array(), $namespace);

		// flush the system queue to avoid displaying duplicated messages
		$session->clear('messagesqueue', $namespace);

		return $queue;
	}

	/**
	 * Returns the application JMenu object.
	 *
	 * @param   string  $name     The name of the application/client.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JMenu|null
	 *
	 * @since   10.1.19
	 */
	public function getMenu($name = null, array $options = array())
	{
		if (!isset($name))
		{
			$name = $this->name;
		}

		// inject this application object into the JMenu tree if one isn't already specified
		if (!isset($options['app']))
		{
			$options['app'] = $this;
		}

		try
		{
			// load JMenu file
			JLoader::import('adapter.menu.menu');

			// try to obtain a valid menu instance
			$menu = JMenu::getInstance($name, $options);
		}
		catch (Exception $e)
		{
			return null;
		}

		return $menu;
	}

	/**
	 * Returns the application JPathway object.
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JPathway|null
	 *
	 * @since   10.1.19
	 */
	public function getPathway($name = null, $options = array())
	{
		if (!isset($name))
		{
			$name = $this->name;
		}

		try
		{
			// load JPathway file
			JLoader::import('adapter.pathway.pathway');

			// try to obtain a valid pathway
			$pathway = JPathway::getInstance($name, $options);
		}
		catch (Exception $e)
		{
			return null;
		}

		return $pathway;
	}

	/**
	 * Returns the application JRouter object.
	 *
	 * @param   string   $name     The name of the application.
	 * @param   array    $options  An optional associative array of configuration settings.
	 *
	 * @return  JRouter  A JRouter object.
	 *
	 * @throws  Exception
	 *
	 * @since 	10.1.19
	 */
	public static function getRouter($name = null, array $options = array())
	{
		try
		{
			// load JRouter file
			JLoader::import('adapter.router.router');

			// try to obtain a valid router, if any
			$router = JRouter::getInstance($name ?: 'site', $options);
		}
		catch (Exception $e)
		{
			return null;
		}

		return $router;
	}

	/**
	 * Registers a handler to a particular event group.
	 *
	 * @param   string    $event    The event name.
	 * @param   callable  $handler  The handler, a function or an instance of an event object.
	 *
	 * @return  self  	  This object to support chaining.
	 *
	 * @since   10.1.30
	 */
	public function registerEvent($event, $handler)
	{
		// proxy for JEventDispatcher
		return JEventDispatcher::getInstance()->register($event, $handler);
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @param   string  $event  The event name.
	 * @param   array   $args   An array of arguments (optional).
	 *
	 * @return  array   An array of results from each function call, or null if no dispatcher is defined.
	 *
	 * @since   10.1.30
	 */
	public function triggerEvent($event, array $args = null)
	{
		// proxy for JEventDispatcher
		return JEventDispatcher::getInstance()->trigger($event, $args);
	}

	/**
	 * Redirect to another URL.
	 *
	 * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * or "303 See Other" code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param   string   $url     The URL to redirect to. Can only be http/https URL.
	 * @param   integer  $status  The HTTP 1.1 status code to be provided. 303 is assumed by default.
	 *
	 * @return  void
	 *
	 * @uses 	shouldRedirect()
	 *
	 * @link 	https://developer.wordpress.org/reference/functions/wp_redirect/
	 */
	public function redirect($url, $status = 303)
	{
		if ($this->isAdmin())
		{
			// if the URL starts with index.php, replace it into admin.php
			if (strpos($url, 'index.php?') === 0)
			{
				$url = str_replace('index.php?', 'admin.php?', $url);
			}
			// else if the URL starts with ?, prepend admin.php
			else if (strpos($url, '?') === 0)
			{
				$url = 'admin.php' . $url;
			}

			// change end-point in case we are doing AJAX
			if (wp_doing_ajax())
			{
				/**
				 * @note 	this is not really used for AJAX calls,
				 * 			but for redirects between "iframe" pages
				 * 			(contents rendered via AJAX in modal boxes).
				 */
				$url = str_replace('admin.php', 'admin-ajax.php', $url);
			}
		}
		else
		{
			// if the URL is "index.php", we probably need to visit the home page
			if ($url == 'index.php')
			{
				$url = JUri::root();
			}

			// we don't need to route URLs that start with "index.php" because
			// we are (probably) already under a rewritten page and this means
			// that the plugin is going to be processed correctly.
		}

		// redirect is allowed only if the headers haven't been sent yet
		if (!headers_sent())
		{
			wp_redirect($url, $status);
			exit;
		}

		// JS redirect only once
		if (!$this->shouldRedirect())
		{
			// register JS redirect
			$this->jsRedirect = true;

			// otherwise redirect using javascript
			echo "<script>document.location.href='" . str_replace("'", '&apos;', $url) . "';</script>\n";
		}
	}

	/**
	 * Checks if the application is going to do a JS redirect.
	 * The javascript redirect is applied when the headers have been already sent.
	 *
	 * @return 	boolean  True if JS redirect, otherwise false.
	 */
	public function shouldRedirect()
	{
		return $this->jsRedirect;
	}

	/**
	 * Login authentication function.
	 *
	 * @param   array 	 $credentials  Array('username' => string, 'password' => string)
	 * @param   array 	 $options      Array('remember' => boolean)
	 *
	 * @return  boolean  True on success, false if failed.
	 */
	public function login($credentials, $options = array())
	{
		$login = array();
		$login['user_login'] 	= $credentials['username'];
		$login['user_password'] = $credentials['password'];
		$login['remember']		= isset($options['remember']) ? $options['remember'] : false;

		$options['redirect']	= isset($options['redirect']) ? $options['redirect'] : '';

		// direct login only if the headers haven't been sent
		if (!headers_sent())
		{
			$res = wp_signon($login);

			return $res instanceof WP_User;
		}
		// otherwise use <form> workaround to dispatch wp-login.php
		else
		{
			$url = wp_login_url($options['redirect']);
			$url .= (strpos($url, '?') !== false ? '&' : '?') . 'action=login';

			?>

			<form action="<?php echo $url; ?>" method="post" name="loginform" id="loginform">
				<input type="hidden" name="log" value="<?php echo $login['user_login']; ?>" />
				<input type="hidden" name="pwd" value="<?php echo $login['user_password']; ?>" />
				<input type="hidden" name="rememberme" value="<?php echo $login['remember'] ? '1' : ''; ?>" />
			</form>

			<script>
				document.loginform.submit();
			</script>

			<?php
		}
	}

	/**
	 * Log the current user out, by destroying the current user session.
	 * It takes a non-used parameter for compatibility with other CMS.
	 * 
	 * @param 	int 	$uid 		the id of the current user logged in
	 *
	 * @return 	void
	 * 
	 * @since 	10.1.5
	 */
	public function logout($uid)
	{
		wp_logout();
	}

	/**
	 * Method to close the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @since   10.1.33
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Method to set a response header. If the replace flag is set then all headers
	 * with the given name will be replaced by the new one. The headers are stored
	 * in an internal array to be sent when the site is dispatched to the browser.
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  self     This object to support chaining.
	 *
	 * @since   10.1.33
	 */
	public function setHeader($name, $value, $replace = false)
	{
		/**
		 * We have to treat the status in a slightly different way because WordPress is unable
		 * to properly send this header. All the headers registered through the "wp_headers"
		 * hook are always sent with the "{KEY}: {VALUE}" format, while the status should be
		 * sent as {PROTOCOL} {CODE} {DESCRIPTION}.
		 * 
		 * @since 10.1.40
		 */
		if (strtolower($name) === 'status')
		{
			if (is_null($this->status) || $replace)
			{
				// update HTTP status
				$this->status = (int) $value;
			}
		}
		else
		{
			// register filter to attach the given header into the WP pool
			add_filter('wp_headers', function($headers) use ($name, $value, $replace)
			{
				if (!isset($headers[$name]) || $replace)
				{
					// set/replace header with the given value
					$headers[$name] = $value;
				}

				return $headers;
			});
		}

		return $this;
	}

	/**
	 * Method to get the array of response headers to be sent when
	 * the response is dispatched to the client.
	 *
	 * @return  array
	 *
	 * @since   10.1.33
	 */
	public function getHeaders()
	{
		global $wp;

		$headers = [];

		if (!is_null($this->status))
		{
			// manually register the status header within the pool
			$headers['status'] = $this->status;
		}

		/**
		 * Filters the HTTP headers before they're sent to the browser.
		 *
		 * @since 2.8.0
		 *
		 * @param string[] $headers Associative array of headers to be sent.
		 * @param WP       $this    Current WordPress environment instance.
		 */
		return apply_filters('wp_headers', $headers, $wp);
	}

	/**
	 * Method to clear any set response headers.
	 *
	 * @return  self  This object to support chaining.
	 *
	 * @since   10.1.33
	 */
	public function clearHeaders()
	{
		// remove all the callbacks assigned to this hook
		remove_filter('wp_headers');

		// clear the previously registered HTTP status code too
		$this->status = null;

		return $this;
	}

	/**
	 * Sends the response headers.
	 *
	 * @return  self  This object to support chaining.
	 *
	 * @since   10.1.33
	 */
	public function sendHeaders()
	{
		global $wp;

		// send headers through WP
		$wp->send_headers();

		/**
		 * In case we have a registered HTTP status code, send this header.
		 * 
		 * @since 10.1.40
		 */
		if (!is_null($this->status))
		{
			status_header($this->status);
		}

		return $this;
	}
}
