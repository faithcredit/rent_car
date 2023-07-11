<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.language
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.language.handler');

/**
 * Languages/translation adapter class.
 *
 * @since 10.0
 */
#[\AllowDynamicProperties]
class JLanguage
{
	/**
	 * Array of Language objects.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * The current language tag.
	 *
	 * @var string
	 */
	private $tag = null;

	/**
	 * A list of handlers to switch the lang keys.
	 *
	 * @var array
	 */
	private $handlers = array();

	/**
	 * Name of the transliterator function for this language.
	 *
	 * @var Callable
	 */
	private $transliterator = null;

	/**
	 * Class constructor.
	 *
	 * @param 	string  $lang   The language to use.
	 *
	 * @uses 	load()
	 */
	public function __construct($lang = null)
	{
		$this->tag = $lang;
	}

	/**
	 * Returns a language object.
	 *
	 * @param 	string  $lang   The language to use.
	 *
	 * @return  self  	The Language object.
	 */
	public static function getInstance($lang = null)
	{
		if (!isset(static::$instances[$lang]))
		{
			static::$instances[$lang] = new static($lang);
		}

		return static::$instances[$lang];
	}

	/**
	 * Loads a single language file. This method doesn't update the
	 * current user locale.
	 *
	 * @param   string   $extension  The extension for which a language file should be loaded.
	 * @param   string   $basePath   The basepath to use.
	 * @param   string   $lang       The language to load, default null for the current language.
	 *
	 * @return  boolean  True if the file has been successfully loaded.
	 *
	 * @uses 	getLocaleFilter()
	 */
	public function load($extension, $basePath = '', $lang = null)
	{
		// make sure we are not using the Joomla tag standards
		$lang = is_null($lang) ? null : str_replace('-', '_', $lang);
		
		// make sure the name of the plugin is correct
		$extension = preg_replace("/^com_/", '', $extension);

		$this->localeFilter = $lang;

		/**
		 * Action triggered before loading the text domain.
		 *
		 * @param 	string 	$extension  The plugin text domain to look for.
		 * @param 	string 	$basePath   The base path containing the languages.
		 * @param 	mixed   $lang       An optional language tag to use.
		 *
		 * @return 	void
		 *
		 * @since 	10.1.30
		 */
		do_action('vik_plugin_before_load_language', $extension, $basePath, $lang);

		/**
		 * In case the base path was not provided, or a default Joomla constant was passed,
		 * we need to try to auto-detect the standard folder where the language files 
		 * should be placed.
		 *
		 * @since 10.1.24
		 */
		if (!$basePath || $basePath == 'JPATH_ADMINISTRATOR' || $basePath == 'JPATH_SITE')
		{
			// create constant name to retrieve standard languages folder
			$const = strtoupper($extension) . '_LANG';

			// try to retrieve language path from constant, if defined
			$basePath = defined($const) ? constant($const) : '';
		}

		// check if the lang tag has been specified,
		// otherwise use the default locale
		if (!is_null($lang))
		{	
			// create a filter to override the 'plugin_locale'
			add_filter('plugin_locale', array($this, 'getLocaleFilter'));
		}

		// we need to attach an action to 'load_textdomain' to unset the 
		// cache related to the existing language, if any
		add_action('load_textdomain', array($this, 'refreshDomain'));

		/**
		 * Register 2 hooks to prevent the hack defined by Polylang to support the
		 * lazy loading of the translations. Since our plugin might run before
		 * Polylang, we need to by pass this limitation and always load the translations
		 * without waiting the latter is ready.
		 *
		 * @since 10.1.33
		 */
		add_filter('load_textdomain_mofile', array($this, 'storeDefaultMofile'), 1, 2);
		add_filter('load_textdomain_mofile', array($this, 'preventPolylangHack'), 100, 2);

		// init language
		$loaded = load_plugin_textdomain($extension, false, $basePath);

		/**
		 * Hook used to load plugin translations from different folders.
		 *
		 * @param 	boolean  $loaded     True if a language translation has been already loaded.
		 * @param 	string 	 $extension  The plugin text domain to look for.
		 *
		 * @return 	boolean  True if a new translation is loaded.
		 *
		 * @since 	10.1.28
		 */
		$loaded = apply_filters('vik_plugin_load_language', $loaded, $extension);

		// remove the action to avoid affecting other plugins
		remove_action('load_textdomain', array($this, 'refreshDomain'));

		// remove the filter to avoid affecting other plugins
		remove_filter('plugin_locale', array($this, 'getLocaleFilter'));

		return $loaded;
	}

	/**
	 * Returns the lang tag to load for 'locale' filter.
	 *
	 * @return 	string 	The locale.
	 */
	public function getLocaleFilter()
	{
		/**
		 * @var $localeFilter is declared in self::load()
		 */
		return isset($this->localeFilter) ? $this->localeFilter : null;
	}

	/**
	 * Used to unset the cache related to a language already loaded.
	 *
	 * @param 	string 	$domain 	The language domain to unset.
	 *
	 * @return 	void
	 */
	public function refreshDomain()
	{
		$args 	= func_get_args();
		$domain = array_shift($args);

		if (!$domain)
		{
			return;
		}

		// the global $l10n var contains all the cached languages
		global $l10n;
		
		// if the domain is set in the cache, unset it
		if (isset($l10n[$domain]))
		{
			unset($l10n[$domain]);
		}
	}

	/**
	 * Every time Gettext tries to fetch the path of a MO file,
	 * we need to internally save the default given path.
	 *
	 * This method should be executed as soon as possible.
	 *
	 * @param 	string 	$mofile  Path to the MO file.
	 * @param 	string 	$domain  Unique identifier for retrieving translated strings.
	 *
	 * @return 	string  The updated MO file.
	 *
	 * @since 	10.1.33
	 */
	public function storeDefaultMofile($mofile, $domain)
	{
		// always track the default MO file path
		$this->defaultMoFile = $mofile;

		return $mofile;
	}

	/**
	 * Polylang always unset the given MO files to load them all together when 
	 * this latter already loaded all the resources. In order to prevent this
	 * behavior/hack, we need to reset the empty path with the previously 
	 * registered one (@see storeDefaultMofile).
	 *
	 * This method should be executed as late as possible.
	 *
	 * @param 	string 	$mofile  Path to the MO file.
	 * @param 	string 	$domain  Unique identifier for retrieving translated strings.
	 *
	 * @return 	string  The updated MO file.
	 *
	 * @since 	10.1.33
	 */
	public function preventPolylangHack($mofile, $domain)
	{
		if (!$mofile)
		{
			// the path has been probably emptied by Polylang, reset it
			$mofile = $this->defaultMoFile;
		}

		return $mofile;
	}

	/**
	 * Translates a string into the current language.
	 *
	 * @param   string   $string                The string to translate.
	 * @param   boolean  $jsSafe                Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  To interpret backslashes (\\=\, \n=carriage return, \t=tabulation)
	 *
	 * @return  string   The translated string.
	 *
	 * @uses 	findTranslation()
	 */
	public function _($string, $jsSafe = false, $interpretBackSlashes = true)
	{
		$return = $this->findTranslation($string);

		if (empty($return))
		{
			return $string;
		}

		if ($jsSafe)
		{
			// javascript filter
			$return = addslashes($return);
		}
		else if ($interpretBackSlashes)
		{
			if (strpos($return, '\\') !== false)
			{
				// interpret \n and \t characters
				$return = str_replace(array('\\\\', '\t', '\n'), array("\\", "\t", "\n"), $return);
			}
		}

		return $return;
	}

	/**
	 * Dispatches the attached handlers to find the specified string.
	 *
	 * @param   string 	$string  The string to translate.
	 *
	 * @return 	string 	The translated string, otherwise null.
	 */
	protected function findTranslation($string)
	{
		foreach ($this->handlers as $handler)
		{
			$result = $handler->translate($string);

			if ($result !== null)
			{
				return $result;
			}
		}

		return null;
	}

	/**
	 * Getter for the language tag (as defined in RFC 3066).
	 *
	 * @return 	string 	The language tag.
	 */
	public function getTag()
	{
		$tag = $this->tag;

		// if no tag set, return the current one
		if (is_null($tag))
		{
			/**
			 * Take the locale specified by the user.
			 * In case of missing locale, the function
			 * always fallback to the default one.
			 *
			 * @since 10.1.31
			 */
			$tag = get_user_locale();
		}

		// replace the underscore with an hyphen
		return str_replace('_', '-', $tag);
	}

	/**
	 * Determines is a key exists.
	 *
	 * @param   string   $string 	The key to check.
	 *
	 * @return  boolean  True if the key exists, otherwise false.
	 *
	 * @uses 	_()
	 */
	public function hasKey($string)
	{
		/**
		 * Fixed return value, which was exactly a negation of
		 * the expected boolean.
		 *
		 * @since 10.1.35
		 */
		return strcmp($this->_($string), $string) === 0 ? false : true;
	}

	/**
	 * Attaches an handler to evaluate the key to translate.
	 *
	 * @param 	string 	$file 	 The file containing the handler.
	 * @param 	string 	$domain  The plugin domain name.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function attachHandler($file, $domain)
	{
		/**
		 * Strip any unexpected characters from domain.
		 *
		 * @since 10.1.29
		 */
		$domain = preg_replace("/[^a-z0-9_]+/i", '', $domain);

		$sign = serialize(array($file, $domain));

		if (!isset($this->handlers[$sign]))
		{
			require_once $file;

			$name = basename($file);
			$name = substr($name, 0, strrpos($name, '.'));

			$classname = ucwords($domain) . 'Language' . ucwords($name);
			$handler   = new $classname();

			if ($handler instanceof JLanguageHandler)
			{
				$this->handlers[$sign] = $handler;
			}
		}

		return $this;
	}

	/**
	 * Transliterate function.
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents".
	 *
	 * @param   string  $string  The string to transliterate.
	 *
	 * @return  string  The transliteration of the string.
	 */
	public function transliterate($string)
	{
		if ($this->transliterator !== null)
		{
			return call_user_func($this->transliterator, $string);
		}

		JLoader::import('adapter.language.transliterate');

		$string = Transliterate::utf8_latin_to_ascii($string);

		return $string;
	}

	/**
	 * Getter for transliteration function.
	 *
	 * @return  callable  The transliterator function.
	 */
	public function getTransliterator()
	{
		return $this->transliterator;
	}

	/**
	 * Set the transliteration function.
	 *
	 * @param   callable  $function  Function name or the actual function.
	 *
	 * @return  callable  The previous function.
	 */
	public function setTransliterator($function)
	{
		$previous = $this->transliterator;
		$this->transliterator = $function;

		return $previous;
	}

	/**
	 * Get the first day of the week for this language.
	 *
	 * @return  integer  The first day of the week according to the language
	 *
	 * @since   10.1.28
	 */
	public function getFirstDay()
	{
		// since we have not enough information to know
		// the first day of the week for this region, 
		// we should rely on the global configuration
		return get_option('start_of_week', 0);
	}

	/**
	 * Get the RTL property.
	 *
	 * @return  boolean  True is it an RTL language.
	 *
	 * @since   10.1.28
	 */
	public function isRtl()
	{
		// checks whether the current locale is RTL
		return is_rtl();
	}

	/**
	 * Returns a list of known languages.
	 *
	 * @return  array  Key/value pair with the language file and related metadata.
	 *
	 * @since   10.1.9
	 */
	public static function getKnownLanguages()
	{
		// get installed languages
		$list = get_available_languages();

		// if the default US lang is in the array, remove it
		if ($index = array_search('en_US', $list))
		{
			array_splice($list, $index, 1);
		}

		// insert en_US default lang at the beginning of the list
		array_unshift($list, 'en_US');

		// replace all the underscores with an hyphen
		$list = array_map(function($elem)
		{
			return str_replace('_', '-', $elem);
		}, $list);

		// obtain WP available translations
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		$translations = wp_get_available_translations();

		$map = array();

		foreach ($list as $lang)
		{
			// get the original locale
			$key = str_replace('-', '_', $lang);

			if (isset($translations[$key]))
			{
				$name 	= $translations[$key]['english_name'];
				$native = $translations[$key]['native_name'];
				$locale = implode(', ', $translations[$key]['iso']);

				unset($translations[$key]);
			}
			else
			{
				$name 	= $native = $lang;
				$locale = substr($lang, 0, 2);
			}

			$map[$lang] = array(
				'name' 			=> $name,
				'nativeName' 	=> $native,
				'tag' 			=> $lang,
				'locale' 		=> $locale,
				'rtl'			=> 0,
				'firstDay'		=> 0,
			);
		}

		// fix en_US property because its details don't exist
		$map['en-US']['name'] 		= 'English (United States)';
		$map['en-US']['nativeName'] = 'English (United States)';

		return $map;
	}
}
