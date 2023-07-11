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
 * Document class, provides an easy interface to parse and display a document
 * using the Joomla standard functions.
 *
 * @since 10.0
 */
class JDocument
{
	/**
	 * An array to cache the meta data set using this class.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * A list containing all the style declarations loaded.
	 *
	 * @var array
	 */
	protected $styleDeclarations = [];

	/**
	 * Array of scripts options.
	 *
	 * @var    array
	 * @since  10.1.14
	 */
	protected $scriptOptions = [];

	/**
	 * Array of scripts declarations to be
	 * appended after the body.
	 *
	 * @var   array
	 * @since 10.1.29
	 */
	protected $ajaxScripts = [];

	/**
	 * Sets a meta tag in the front-end only.
	 *
	 * @param   string  $name       Name of the meta HTML tag.
	 * @param   mixed   $content    Value of the meta HTML tag as array or string.
	 * @param   string  $attribute  Attribute to use in the meta HTML tag.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	attachToHead()
	 */
	public function setMetaData($name, $content, $attribute = 'name')
	{
		$attribute = empty($attribute) || !is_string($attribute) ? 'name' : $attribute;

		if (is_scalar($content))
		{
			$content = array($content);
		}

		$this->attachToHead(function() use ($name, $content, $attribute)
		{
			foreach ($content as $cont)
			{
				echo "<meta {$attribute}=\"{$name}\" content=\"" . esc_attr($cont) . "\" />\n";
			}
		}, true);

		$this->data[$name] = $content;

		return $this;
	}

	/**
	 * Gets a meta tag.
	 * Since Wordpress doesn't own a system to handle meta data,
	 * we can only return a cached version of the data set using this class.
	 *
	 * @param   string  $name 	Name of the meta HTML tag.
	 *
	 * @return  string
	 */
	public function getMetaData($name)
	{
		if (isset($this->data[$name]))
		{
			return $this->data[$name];
		}

		return '';
	}

	/**
	 * Sets the title of the document in the front-end only.
	 *
	 * @param 	string  $title  The title to be set.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @link 	https://developer.wordpress.org/reference/hooks/wp_title/ (Wordpress < 4.4)
	 * @link 	https://developer.wordpress.org/reference/functions/wp_get_document_title/ (Wordpress 4.4+)
	 */
	public function setTitle($title)
	{
		/**
		 * We should use the hook 'pre_get_document_title' (available from WP >= v4.4),
		 * instead of 'wp_title', or the title won't actually be set.
		 *
		 * @since 10.1.23
		 */
		add_filter('pre_get_document_title', function() use ($title)
		{
			return $title;
		});

		return $this;
	}

	/**
	 * Return the title of the document.
	 *
	 * @return 	string
	 *
	 * @link 	https://developer.wordpress.org/reference/hooks/wp_title/ (Wordpress < 4.4)
	 * @link 	https://developer.wordpress.org/reference/functions/wp_get_document_title/ (Wordpress 4.4+)
	 */
	public function getTitle()
	{
		global $wp_version;

		if (version_compare($wp_version, '4.4', '>='))
		{
			return wp_get_document_title();
		}
		
		return wp_title('&raquo;', false);
	}

	/**
	 * Sets the description of the document.
	 *
	 * @param 	string  $desc  The description to be set.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @uses 	setMetaData()
	 */
	public function setDescription($desc)
	{
		return $this->setMetaData('description', (string) $desc);
	}

	/**
	 * Return the description of the document.
	 *
	 * @return 	string
	 *
	 * @uses 	getMetaData()
	 */
	public function getDescription()
	{
		return $this->getMetaData('description');
	}

	/**
	 * Adds a linked script to the page.
	 *
	 * @param   string  $url      URL to the linked script.
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9')
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'scriptid', 'async' => 'async', 'data-test' => 1)
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @link 	https://developer.wordpress.org/reference/functions/wp_register_script/
	 */
	public function addScript($url, $options = [], $attribs = [])
	{
		// check if the script should be loaded using WP native libs
		$version = array_key_exists('version', $options) ? $options['version'] : null;
		$footer  = array_key_exists('footer', $options) ? (bool) $options['footer'] : false;
		$id 	 = empty($attribs['id']) ? md5($url) : $attribs['id'];

		/**
		 * Use filter to approve/deny the loading of the given script.
		 *
		 * @param 	boolean  $load     The recursive filtered value (default 'true').
		 * @param 	string   $url      The resource URL.
		 * @param 	string   $id       The script ID attribute.
		 * @param 	string   $version  The script version, if specified.
		 * @param 	boolean  $footer   True whether the script is going to be loaded in the footer.
		 *
		 * @return 	boolean  True to load the resource, false to ignore it.
		 *
		 * @since 	10.1.25
		 */
		$load = apply_filters('vik_before_include_script', true, $url, $id, $version, $footer);

		if ($load)
		{
			// make sure this is not an AJAX call
			if (!wp_doing_ajax())
			{
				// if the headers have been sent, the script must be registered in the footer.
				if (headers_sent())
				{
					$footer = true;
				}

				// the default array of dependencies
				$deps = [
					'jquery-core',
					'jquery-ui-core',
				];

				/**
				 * Added support to custom dependencies.
				 * 
				 * @since 10.1.38
				 */
				if (!empty($options['dependencies']))
				{
					// join default dependencies with the given ones
					$deps = array_merge($deps, (array) $options['dependencies']);
					// get rid of duplicates
					$deps = array_values(array_unique($deps));
				}

				// loads scripts always after jQuery Core (included by Wordpress)
				wp_register_script($id, $url, $deps, $version, $footer);
				wp_enqueue_script($id);
			}
			// since the footer is already printed, we need to each our script directly
			else if ($url)
			{
				if ($version)
				{
					$url .= '?ver=' . $version;
				}

				echo '<script type="text/javascript" src="' . $url . '"></script>';
			}
		}

		return $this;
	}

	/**
	 * Adds a script to the page.
	 *
	 * @param   string  $content  Script snippet.
	 * @param   string  $type     Scripting mime (defaults to 'text/javascript').
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	attachToHead()
	 */
	public function addScriptDeclaration($content, $type = 'text/javascript')
	{
		/**
		 * Always use the script declaration without checking
		 * if it has been already loaded.
		 *
		 * @since 10.1.17
		 */
		$this->attachToHead(function() use ($content, $type)
		{
			/**
			 * Use "joomla-options new" class in case we are attaching a JSON string.
			 * The "new" word means that the options has to be loaded.
			 *
			 * @since 10.1.14
			 */
			$class = $type == 'application/json' ? ' class="joomla-options new"' : '';

			echo "<script type=\"{$type}\"{$class}>\n{$content}\n</script>\n";
		});

		return $this;
	}

	/**
	 * Adds a linked stylesheet to the page.
	 *
	 * @param   string  $url      URL to the linked style sheet.
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9')
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'stylesheet', 'data-test' => 1)
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @link 	https://codex.wordpress.org/Function_Reference/wp_register_style
	 */
	public function addStyleSheet($url, $options = [], $attribs = [])
	{
		$version = array_key_exists('version', $options) ? $options['version'] : null;
		$media 	 = array_key_exists('media', $options) ? (string) $options['media'] : 'all';
		$id 	 = empty($attribs['id']) ? md5($url) : $attribs['id'];

		/**
		 * Use filter to approve/deny the loading of the given stylesheet.
		 *
		 * @param 	boolean  $load     The recursive filtered value (default 'true').
		 * @param 	string   $url      The resource URL.
		 * @param 	string   $id       The stylesheet ID attribute.
		 * @param 	string   $version  The stylesheet version, if specified.
		 *
		 * @return 	boolean  True to load the resource, false to ignore it.
		 *
		 * @since 	10.1.25
		 */
		$load = apply_filters('vik_before_include_style', true, $url, $id, $version);

		if ($load)
		{
			// attach the style to the <head> only if the headers haven't been sent
			// and if we are not doing an AJAX call
			if (!headers_sent() && !wp_doing_ajax())
			{
				$deps = [];

				/**
				 * Added support to custom dependencies.
				 * 
				 * @since 10.1.38
				 */
				if (!empty($options['dependencies']))
				{
					// join default dependencies with the given ones
					$deps = array_merge($deps, (array) $options['dependencies']);
					// get rid of duplicates
					$deps = array_values(array_unique($deps));
				}

				wp_register_style($id, $url, $deps, $version, $media);
				wp_enqueue_style($id);
			}
			// otherwise print the style in the document <body>
			else
			{
				if ($version)
				{
					$url .= '?ver=' . $version;
				}

				echo '<link rel="stylesheet" id="' . $id . '" href="' . $url . '" type="text/css" media="' . $media . '">';
			}
		}

		return $this;
	}

	/**
	 * Adds a stylesheet declaration to the page.
	 *
	 * @param   string  $content  Style declaration.
	 * @param   string  $type     Type of stylesheet (defaults to 'text/css').
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @uses 	attachToHead()
	 */
	public function addStyleDeclaration($content, $type = 'text/css')
	{
		if (!in_array($content, $this->styleDeclarations))
		{
			$this->attachToHead(function() use ($content, $type)
			{
				echo "<style type=\"{$type}\">\n{$content}\n</style>\n";
			});

			$this->styleDeclarations[] = $content;
		}

		return $this;
	}

	/**
	 * Internal method execute a callback within the <head> tags.
	 * The callback will be attached to the wp_head or admin_head hooks
	 * depending on the section we are currently using.
	 *
	 * @param 	mixed  	 $callback 	 The callback or the function name to attach.
	 * @param 	boolean  $frontOnly  True to attach the method only in the front-end.
	 *
	 * @return 	void
	 *
	 * @link 	https://codex.wordpress.org/Plugin_API/Action_Reference/wp_head
	 * @link 	https://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	 * @link 	https://developer.wordpress.org/reference/hooks/wp_print_footer_scripts/
	 * @link 	https://developer.wordpress.org/reference/hooks/admin_print_footer_scripts/
	 */
	protected function attachToHead($callback, $frontOnly = false)
	{
		$app = JFactory::getApplication();

		// make sure we are in the front-end or the back-end is allowed
		if ($app->isSite() || !$frontOnly)
		{
			// make sure we are not doing an AJAX call
			if (!wp_doing_ajax())
			{
				$head_hook = $app->isAdmin() ? 'admin_head' : 'wp_head';

				// make sure that the head hook that we are using haven't been called yet,
				// otherwise our script will never be executed
				if (!headers_sent() && !did_action($head_hook))
				{
					// admin_head hook for the back-end <head>
					// wp_head hook for the front-end <head>
					add_action($head_hook, $callback);
				}
				else
				{
					// admin_print_footer_scripts hook for the back-end <footer>
					// wp_footer wp_print_footer_scripts for the front-end <footer>
					add_action($app->isAdmin() ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts', $callback);

					/**
					 * NOTE: do not use 'wp_footer' hook because it prints the script declarations
					 * 		 always before the <script> tags, causing errors for missing resources.
					 */
				}
			}
			// we need to invoke our callback directly
			else
			{
				// push scripts within the AJAX callbacks
				$this->ajaxScripts[] = $callback;
			}
		}
	}

	/**
	 * Returns the AJAX scripts that should be included after
	 * the body in order to properly access all the needed elements.
	 *
	 * @return 	string
	 *
	 * @since 	10.1.29
	 */
	public function getAjaxScripts()
	{
		// start buffering
		ob_start();

		// iterate registered scripts
		foreach ($this->ajaxScripts as $callback)
		{
			// invoke the callback to print the script
			call_user_func($callback);
		}

		// catch buffer
		$js = ob_get_contents();
		// clear buffer
		ob_end_clean();

		// empty list
		$this->ajaxScripts = [];

		return $js;
	}

	/**
	 * Add options for script.
	 *
	 * @param   string   $key      Name in Storage.
	 * @param   mixed    $options  Scrip options as array or string.
	 * @param   boolean  $merge    Whether merge with existing (true) or replace (false).
	 *
	 * @return  self 	 This object to support chaining.
	 *
	 * @since   10.1.14
	 */
	public function addScriptOptions($key, $options, $merge = true)
	{
		if (empty($this->scriptOptions[$key]))
		{
			$this->scriptOptions[$key] = [];
		}

		if ($merge && is_array($options))
		{
			$this->scriptOptions[$key] = array_replace_recursive($this->scriptOptions[$key], $options);
		}
		else
		{
			$this->scriptOptions[$key] = $options;
		}

		return $this;
	}

	/**
	 * Get script(s) options.
	 *
	 * @param   string  $key  Name in Storage.
	 *
	 * @return  array   Options for given $key, or all script options.
	 *
	 * @since   10.1.14
	 */
	public function getScriptOptions($key = null)
	{
		if ($key)
		{
			return (empty($this->scriptOptions[$key])) ? [] : $this->scriptOptions[$key];
		}
		else
		{
			return $this->scriptOptions;
		}
	}

	/**
	 * Returns the document charset encoding.
	 *
	 * @return  string
	 *
	 * @since   10.1.20
	 */
	public function getCharset()
	{
		$output = get_option('blog_charset');

        if (!$output)
        {
            $output = 'UTF-8';
        }

        return $output;
	}

	/**
	 * Returns the document language.
	 *
	 * @return  string
	 *
	 * @since   10.1.20
	 */
	public function getLanguage()
	{
		return strtolower(JFactory::getLanguage()->getTag());
	}

	/**
	 * Returns the document direction declaration.
	 *
	 * @return  string
	 *
	 * @since   10.1.20
	 */
	public function getDirection()
	{
		if (function_exists('is_rtl'))
		{
            $output = is_rtl() ? 'rtl' : 'ltr';
        }
        else
        {
            $output = 'ltr';
        }

        return $output;
	}
}
