<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.loader
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Plugin smart loader class.
 *
 * @since 10.0
 */
abstract class JLoader
{
	/**
	 * The list containing all the resources loaded.
	 *
	 * @var array
	 */
	protected static $includes = array();

	/**
	 * The list containing all the filename aliases.
	 *
	 * @var array
	 */
	protected static $aliases = array();

	/**
	 * Base path to load resources.
	 *
	 * @var string
	 */
	public static $base = '';

	/**
	 * Container for namespace => path map.
	 *
	 * @var   array
	 * @since 10.1.34
	 */
	protected static $namespaces = array();

	/**
	 * Loads the specified file.
	 *
	 * @param   string  $key   The class name to look for (dot notation).
	 * @param   string  $base  Search this directory for the class.
	 *
	 * @return  boolean  True on success, otherwise false.
	 */
	public static function import($key, $base = null)
	{
		// if no base provided, use the default one
		if (empty($base))
		{
			$base = static::$base;
		}

		$sign = serialize(array($key, $base));

		// if the resource is not loaded, try to do it
		if (!isset(static::$includes[$sign]))
		{
			$success = false;

			// remove trailing slash (if any)
			$base = rtrim($base, DIRECTORY_SEPARATOR);

			$parts = explode('.', $key);
			$class = array_pop($parts);

			// if the file has been registered with an alias, replace it with the original one
			if (isset(static::$aliases[$class]))
			{
				$class = static::$aliases[$class];
			}

			// re-insert class to build the relative path
			$parts[] = $class;

			// build the path
			$path = implode(DIRECTORY_SEPARATOR, $parts);

			// if the file exists, load it
			if (is_file($base . DIRECTORY_SEPARATOR . $path . '.php'))
			{
				$success = (bool) include_once $base . DIRECTORY_SEPARATOR . $path . '.php';
			}

			// cache the loading status
			static::$includes[$sign] = $success;
		}

		return static::$includes[$sign];
	}

	/**
	 * Register an alias of a given class filename.
	 * This is useful for those files that contain a dot in their name.
	 *
	 * @param 	string 	$name 	The filename to register.
	 * @param 	string 	$alias 	The alias to use.
	 */
	public static function registerAlias($name, $alias)
	{	
		if (!isset(static::$aliases[$alias]))
		{
			static::$aliases[$alias] = $name;
		}
	}

	/**
	 * Register a namespace to the autoloader. When loaded, namespace paths are searched in a "last in, first out" order.
	 *
	 * @param   string   $namespace  A case sensitive Namespace to register.
	 * @param   string   $path       A case sensitive absolute file path to the library root where classes of the given namespace can be found.
	 * @param   boolean  $reset      True to reset the namespace with only the given lookup path.
	 * @param   boolean  $prepend    If true, push the path to the beginning of the namespace lookup paths array.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   10.1.41
	 */
	public static function registerNamespace($namespace, $path, $reset = false, $prepend = false)
	{
		// make sure the library path exists
		if (!is_dir($path))
		{
			throw new RuntimeException('Library path ' . $path . ' cannot be found.', 500);
		}

		// trim leading and trailing backslashes from namespace, allowing "\Parent\Child", "Parent\Child\"
		// and "\Parent\Child\" to be treated the same way
		$namespace = trim($namespace, '\\');

		// if the namespace is not yet registered or we have an explicit reset flag then set the path
		if ($reset || !isset(self::$namespaces[$namespace]))
		{
			self::$namespaces[$namespace] = array($path);
		}
		// otherwise we want to simply add the path to the namespace
		else
		{
			if ($prepend)
			{
				array_unshift(self::$namespaces[$namespace], $path);
			}
			else
			{
				self::$namespaces[$namespace][] = $path;
			}
		}
	}

	/**
	 * Method to setup the autoloaders for the WordPress Platform.
	 *
	 * @return  void
	 *
	 * @since   10.1.41
	 */
	public static function setup()
	{
		// register the framework auto-loader
		spl_autoload_register(array('JLoader', 'loadFramework'));

		// register the PSR based autoloader
		spl_autoload_register(array('JLoader', 'loadByPsr'));
	}

	/**
	 * Auto-loads framework classes at runtime.
	 * 
	 * @param   string   $class  The fully qualified class name to autoload.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 * 
	 * @since   10.1.41
	 */
	public static function loadFramework($class)
	{
		$original = $class;

		// add support for VikWP class prefix
		$class = preg_replace("/^VikWP/", 'J', $class);

		// observe only the classes that starts with "J"
		if (!preg_match("/^J/", $class))
		{
			return false;
		}

		switch ($class)
		{
			case 'JPagination':
				$result = JLoader::import('adapter.pagination.pagination');
				break;

			case 'JView':
			case 'JViewLegacy':
				$result = JLoader::import('adapter.mvc.view');
				break;

			case 'JController':
			case 'JControllerLegacy':
				$result = JLoader::import('adapter.mvc.controller');
				break;

			case 'JControllerAdmin':
				$result = JLoader::import('adapter.mvc.controllers.admin');
				break;

			case 'JComponentHelper':
				$result = JLoader::import('adapter.component.helper');
				break;

			case 'JModuleHelper':
				$result = JLoader::import('adapter.module.helper');
				break;

			case 'JPath':
				$result = JLoader::import('adapter.filesystem.path');
				break;

			case 'JFile':
				$result = JLoader::import('adapter.filesystem.file');
				break;

			case 'JArchive':
				$result = JLoader::import('adapter.filesystem.archive');
				break;

			case 'JFolder':
				$result = JLoader::import('adapter.filesystem.folder');
				break;

			case 'JForm':
				$result = JLoader::import('adapter.form.form');
				break;

			case 'JFormField':
				$result = JLoader::import('adapter.form.field');
				break;

			case 'JRegistry':
				$result = JLoader::import('adapter.application.registry');
				break;

			case 'JVersion':
				$result = JLoader::import('adapter.application.version');
				break;

			default:
				$result = false;
		}

		// in case the loaded class exists and the requested on starts with VikWP,
		// create an alias to support a more appropriate notation
		if (class_exists($class) && preg_match("/^VikWP/", $original))
		{
			class_alias($class, $original);
			$result = true;
		}

		return $result;
	}

	/**
	 * Method to autoload classes that are namespaced to the PSR-4 standard.
	 *
	 * @param   string   $class  The fully qualified class name to autoload.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   10.1.41
	 */
	public static function loadByPsr($class)
	{
		$class = self::stripFirstBackslash($class);

		// find the location of the last NS separator
		$pos = strrpos($class, '\\');

		// If one is found, we're dealing with a NS'd class.
		if ($pos !== false)
		{
			$classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
			$className = substr($class, $pos + 1);
		}
		// if not, no need to parse path
		else
		{
			$classPath = null;
			$className = $class;
		}

		$classPath .= $className . '.php';

		// loop through registered namespaces until we find a match
		foreach (self::$namespaces as $ns => $paths)
		{
			if (strpos($class, "{$ns}\\") === 0)
			{
				$nsPath = trim(str_replace('\\', DIRECTORY_SEPARATOR, $ns), DIRECTORY_SEPARATOR);

				// loop through paths registered to this namespace until we find a match
				foreach ($paths as $path)
				{
					$classFilePath = realpath($path . DIRECTORY_SEPARATOR . substr_replace($classPath, '', 0, strlen($nsPath) + 1));

					// we do not allow files outside the namespace root to be loaded
					if (strpos($classFilePath, realpath($path)) !== 0)
					{
						continue;
					}

					// we check for class_exists to handle case-sensitive file systems
					if (is_file($classFilePath) && !class_exists($class, false))
					{
						return (bool) include_once $classFilePath;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Strips the first backslash from the given class if present.
	 *
	 * @param   string  $class  The class to strip the first prefix from.
	 *
	 * @return  string  The striped class name.
	 *
	 * @since   10.1.41
	 */
	private static function stripFirstBackslash($class)
	{
		return $class && $class[0] === '\\' ? substr($class, 1) : $class;
	}
}
