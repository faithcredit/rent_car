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
 * Trait for VikRentCar classes that may creates sub instances.
 *
 * @since 1.3.0
 */
trait VRCFactoryAware
{
	/**
	 * A list of paths to folders containing all the available cron jobs.
	 *
	 * @var array
	 */
	private $includePaths = array();

	/**
	 * Defines the class prefix of the instances that should be created
	 * through this factory.
	 * 
	 * @var string
	 */
	protected $instanceClassPrefix = '';

	/**
	 * Defines the namespace separator of the class names, such as:
	 * - foo_bar_baz
	 * - foo.bar.baz
	 * 
	 * @var string
	 */
	protected $instanceNamespaceSeparator = '';

	/**
	 * Gets a list of supported include paths.
	 *
	 * @return  array
	 */
	final public function getIncludePaths()
	{
		return $this->includePaths;
	}

	/**
	 * Adds one path to include in cron jobs search.
	 * Proxy of addIncludePaths().
	 *
	 * @param   string  $path  The path to search for cron jobs.
	 *
	 * @return  self 	This object to support chaining.
	 */
	final public function addIncludePath($path)
	{
		return $this->addIncludePaths($path);
	}

	/**
	 * Adds one or more paths to include in cron jobs search.
	 *
	 * @param   mixed  $paths  The path or array of paths to search for cron jobs.
	 *
	 * @return  self   This object to support chaining.
	 */
	final public function addIncludePaths($paths)
	{
		if (empty($paths))
		{
			return $this;
		}

		$includePaths = $this->getIncludePaths();

		// in case the path is an array, 
		if (!is_array($paths))
		{
			// always treat the given path as an array for a correct merging
			$paths = [$paths];
		}

		// merge all the paths and make sure we have no duplicates
		$includePaths = array_unique(array_merge($includePaths, $paths));

		// update include paths
		$this->setIncludePaths($includePaths);

		return $this;
	}

	/**
	 * Sets the include paths to search for cron jobs.
	 *
	 * @param   array  $paths  Array with paths to search in.
	 *
	 * @return  self   This object to support chaining.
	 */
	final public function setIncludePaths($paths)
	{
		$this->includePaths = (array) $paths;

		return $this;
	}

	/**
	 * Return all the installed instances that matches the specified query.
	 *
	 * @return 	array  A list of the found instances.
	 */
	final public function getInstances()
	{
		$paths = array();

		foreach ($this->getIncludePaths() as $dir)
		{
			// recursively scan the directory in search of PHP files
			$files = JFolder::files($dir, '.php$', $recursive = true, $full = true);

			foreach ($files as $filePath)
			{
				$element = [];

				// register file path
				$element['path'] = $filePath;

				// register element name
				$element['name'] = ltrim(substr($filePath, strlen($dir)), DIRECTORY_SEPARATOR);

				if ($this->instanceNamespaceSeparator)
				{
					// replace directory separators with namespace
					$element['name'] = str_replace(DIRECTORY_SEPARATOR, $this->instanceNamespaceSeparator, $element['name']);
				}

				// get rid of PHP file extension
				$element['name'] = basename($element['name'], '.php');

				// inject element within the list
				$paths[] = $element;
			}
		}

		$list = array();

		foreach ($paths as $p)
		{
			// require element file
			require_once $p['path'];

			try
			{
				// try to create the element object
				$list[$p['name']] = $this->createInstance($p['name'], [], $autoload = false);
			}
			catch (Exception $e)
			{
				// unable to create the instance, go ahead
			}
		}

		// sort elements by name since they might be located on different folders
		$this->rearrangeInstances($list);

		return $list;
	}

	/**
	 * Creates a new instance of the requested element.
	 * 
	 * @param 	string   $element   The element name.
	 * @param   array    $args      The class constructor arguments.
	 * @param 	boolean  $autoload  Whether to autoload the file or not.
	 * 
	 * @return 	mixed
	 * 
	 * @throws 	Exception
	 */
	final public function createInstance($element, $args = [], $autoload = true)
	{
		// get rid of the file extension
		$element = basename($element, '.php');

		// convert the filename in classname
		$classname = preg_replace("/[^a-zA-Z0-9]+/", ' ', $element);
		$classname = str_replace(' ', '', ucwords($classname));
		$classname = $this->instanceClassPrefix . $classname;

		if (!class_exists($classname))
		{
			if ($autoload)
			{
				// detect file
				$file = $this->findPath($element);

				if (!$file)
				{
					// element file not found
					throw new Exception(sprintf('The instance [%s] does not exist.', $element), 404);
				}

				// possible match detected, require the file
				require_once $file;
			}

			if (!class_exists($classname))
			{
				// unable to detect the class file
				throw new Exception(sprintf('Instance [%s] class not found.', $classname), 404);
			}
		}

		if (!is_array($args))
		{
			// wrap arguments into an array for a correct instantiation
			$args = [$args];
		}

		// create reflection of the element
		$class = new ReflectionClass($classname);
		// instantiate element class with the given arguments
		$obj = $class->newInstanceArgs($args);

		// make sure the object is a valid instance
		if (!$this->isInstanceValid($obj))
		{
			throw new Exception(sprintf('The object [%s] is not a valid instance.', $classname), 406);
		}

		return $obj;
	}

	/**
	 * Gets the path of the specified element.
	 * 
	 * @param 	string  $element  The element name.
	 *
	 * @return 	mixed   The element path if exists, false otherwise.
	 */
	final protected function findPath($element)
	{
		// convert element into a subfolder
		if ($this->instanceNamespaceSeparator)
		{
			$element = str_replace($this->instanceNamespaceSeparator, DIRECTORY_SEPARATOR, $element);
		}

		foreach ($this->getIncludePaths() as $dir)
		{
			// build full path
			$path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $element . '.php';

			// check whether the file exists
			if (JFile::exists($path))
			{
				// match found, return file path
				return $path;
			}
		}

		// no match found
		return false;
	}

	/**
	 * Children classes can override this method to rearrange the ordering
	 * of the elements created through this factory class.
	 * 
	 * @param   array  &$list  The list of instances.
	 * 
	 * @return  void
	 */
	protected function rearrangeInstances(&$list)
	{
		// not enough information to rearrange the list...
	}

	/**
	 * Children classes can override this method to make sure that the
	 * created instance is compliant with the factory requirements.
	 * 
	 * @param   mixed    $object  The object to validate.
	 * 
	 * @return  boolean  True if valid, false otherwise.
	 */
	protected function isInstanceValid($object)
	{
		// the validation should be delegated to the class that
		// inherits this trait methods
		return true;
	}
}
