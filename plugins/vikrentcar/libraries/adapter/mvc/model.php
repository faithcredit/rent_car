<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.mvc
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.application.object');

/**
 * The model class used by the MVC framework.
 * A model can be used by a controller or a view to handle 
 * a certain entity of the plugin.
 *
 * The model can be invoked when the value contained 
 * in $_REQUEST['task'] is equals to 'ComponentModel' + $_REQUEST['task'].
 *
 * e.g. $_REQUEST['task'] = 'groups.save' -> ComponentModelGroups
 *
 * @since 10.0
 */
abstract class JModel extends JObject
{
	/**
	 * A list of model instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * A list of included paths.
	 *
	 * @var   array
	 * @since 10.1.24
	 */
	protected static $paths = array();

	/**
	 * The model name.
	 * e.g. ComponentModel[NAME]
	 *
	 * @var string
	 */
	protected $_name = null;

	/**
	 * The component name.
	 * e.g. [COMPONENT]ModelName
	 *
	 * @var string
	 */
	protected $_component = null;

	/**
	 * The application client.
	 *
	 * @var string
	 */
	protected $_client = null;

	/**
	 * The database table name.
	 *
	 * @var   array
	 * @since 10.1.35
	 */
	protected $_config;

	/**
	 * Tries to load the specified model, only creating it if 
	 * it doesn't exist yet.
	 *
	 * @param   string  $type    The model type to instantiate
	 * @param   string  $prefix  Prefix for the model class name.
	 * @param   array   $config  Configuration array for model.
	 *
	 * @return 	mixed 	The model instance if found, otherwise null.
	 *
	 * @since   10.1.35  Switch the ordering of $type and $prefix argument.
	 * @since   10.1.35  Added $config argument.
	 * @since   10.1.35  Removed $client, $table and $pk arguments.
	 */
	public static function getInstance($type, $prefix = '', $config = array())
	{
		/**
		 * For backward compatibility, we should check whether the 3rd argument is passed,
		 * meaning that we need to force a client section in which to load the model.
		 *
		 * @since 10.1.35
		 */
		if (is_string($config))
		{
			$config = ['client' => $config];
		}
		else
		{
			$config = (array) $config;
		}

		if (empty($config['client']))
		{
			$config['client'] = JFactory::getApplication()->isAdmin() ? 'admin' : 'site';
		}

		/**
		 * The current version of JModel is not using the same arguments as 
		 * specified by Joomla, since the model name is always passed as
		 * first argument.
		 *
		 * For this reason, we need to swap the prefix and the name when
		 * passed using Joomla standards. This can be done when at least one
		 * of the following conditions is verified:
		 *
		 * - the name contains 'model' word
		 * - the name is actually the folder name and the prefix isn't
		 *
		 * @since 10.1.24
		 */
		if (preg_match("/model/i", $type)
			|| (is_dir(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $type) && !is_dir(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $prefix)))
		{
			// swap name with prefix
			$tmp    = $type;
			$type   = $prefix;
			$prefix = $tmp;
		}

		// remove 'model' from prefix (if any) and make it lowercase
		$prefix = strtolower(preg_replace("/model/i", '', $prefix));

		$sign = serialize(array($prefix, $type, $config['client']));

		if (!isset(static::$instances[$sign]))
		{
			static::$instances[$sign] = false;

			// create classname
			$classname = ucfirst($prefix) . 'Model' . ucfirst($type);

			/**
			 * Merge default include path (the fallback) with specified directories.
			 * Then iterate the list to find the first available model.
			 *
			 * @since 10.1.24
			 */
			$paths = array_merge(
				// default include path
				array(
					/**
					 * Search also inside the libraries folder of the current plugin.
					 * Prioritize this folder to avoid conflicts with deprecated files.
					 *
					 * @since 10.1.35
					 */
					implode(DIRECTORY_SEPARATOR, array(WP_PLUGIN_DIR, $prefix, 'libraries', 'mvc', $config['client'], 'models')),
					implode(DIRECTORY_SEPARATOR, array(WP_PLUGIN_DIR, $prefix, $config['client'], 'models')),
				),
				// specified include paths
				self::addIncludePath()
			);

			// iterate paths until we find an existing file (or till the list is empty)
			for ($i = 0, $path = null; $i < count($paths) && !$path; $i++)
			{
				// create path
				$path = $paths[$i] . DIRECTORY_SEPARATOR . strtolower($type) . '.php';

				// make sure the file exists
				if (!is_file($path))
				{
					// unset path to keep iterating
					$path = null;
				}
			}

			// make sure we have a path
			if ($path)
			{
				// include model
				require_once $path;

				// make sure the class exists
				if (class_exists($classname))
				{
					// cache model instance
					static::$instances[$sign] = new $classname($config);
				}
			}
		}

		return static::$instances[$sign];
	}

	/**
	 * Add a directory where the class should search for models.
	 * You may either pass a string or an array of directories.
	 *
	 * @param   mixed   $path    A path or array of paths to search.
	 *
	 * @return  array 	An array with directory elements.
	 *
	 * @since   10.1.24
	 */
	public static function addIncludePath($path = null)
	{
		if (!empty($path))
		{
			JLoader::import('adapter.filesystem.path');

			foreach ((array) $path as $includePath)
			{
				$includePath = JPath::clean($includePath);

				// check if the path is a dir
				if (!is_dir($includePath))
				{
					// extract name between component and models
					if (preg_match("/components[\/\\\\]com_([a-z0-9_]+)[\/\\\\]models/", $includePath, $match))
					{
						// access adapters of current plugin
						$option = JFactory::getApplication()->input->get('option');
						// strip initial com_ from option name
						$option = preg_replace("/^com_/", '', $option);

						// rewrite include path
						$includePath = JPath::clean(WP_PLUGIN_DIR . '/' . $option . '/libraries/adapter/mvc/models/' . end($match));
					}
				}

				// make sure the folder is not already in the list
				if (!in_array($includePath, static::$paths))
				{
					// push directory as first
					array_unshift(static::$paths, $includePath);
				}
			}
		}

		return static::$paths;
	}

	/**
	 * Class constructor.
	 *
	 * @param   array   $config   An array of configuration options.
	 *
	 * @since 	10.1.35  Added $config argument.
	 * @since   10.1.35  Removed $table and $pk arguments.
	 */
	public function __construct($config = array())
	{
		$option = $this->getComponentName();

		if (!array_key_exists('table_path', $config) && $option)
		{
			$config['table_path'] = [
				JPath::clean(WP_PLUGIN_DIR . '/' . $option . '/libraries/mvc/' . $config['client'] . '/tables'),
				JPath::clean(WP_PLUGIN_DIR . '/' . $option . '/' . $config['client'] . '/tables'),
			];
		}

		// set the default view search path
		if (!empty($config['table_path']))
		{
			$this->addTablePath($config['table_path']);
		}

		$this->_config = $config;
	}

	/**
	 * Adds to the stack of model table paths in LIFO order.
	 *
	 * @param   mixed  $path  The directory as a string or directories as an array to add.
	 *
	 * @return  void
	 *
	 * @since   10.1.35
	 */
	public function addTablePath($path)
	{
		JTable::addIncludePath($path);
	}

	/**
	 * Method to get a table object.
	 *
	 * @param   string  $name     The table name.
	 * @param   string  $prefix   The class prefix.
	 * @param   array   $options  Configuration array for table.
	 *
	 * @return  JTable  A table object.
	 *
	 * @since   10.1.35
	 */
	public function getTable($name = '', $prefix = 'JTable', $options = array())
	{
		if (!$name)
		{
			$name = $this->getName();
		}

		return JTable::getInstance($name, $prefix, $options);
	}

	/**
	 * Returns the model name.
	 *
	 * @return 	string 	Model name.
	 */
	public function getModelName()
	{
		if (is_null($this->_name))
		{
			$class = get_class($this);

			if (preg_match("/Model(.*?)$/", $class, $match))
			{
				$this->_name = strtolower($match[1]);
			}
			else
			{
				$this->_name = $class;
			}	
		}

		return $this->_name;
	}

	/**
	 * Returns the model name.
	 * Proxy for getModelName() method.
	 *
	 * @return 	string 	Model name.
	 *
	 * @since   1.0.1.35
	 */
	public function getName()
	{
		return $this->getModelName();
	}

	/**
	 * Returns the component name.
	 *
	 * @return 	string 	Component name.
	 */
	public function getComponentName()
	{
		if (is_null($this->_component))
		{
			$class = get_class($this);

			if (preg_match("/(.*?)Model/", $class, $match))
			{
				$this->_component = strtolower($match[1]);
			}
			else
			{
				$this->_component = $class;
			}	
		}

		return $this->_component;
	}

	/**
	 * Returns the application client folder (admin or site).
	 *
	 * @return 	string 	The client.
	 */
	public function getClientFolder()
	{
		if (is_null($this->_client))
		{
			$this->_client = JFactory::getApplication()->isAdmin() ? 'admin' : 'site';
		}

		return $this->_client;
	}
}

/**
 * Alias for JModel, which is still used by the components.
 *
 * @since 10.1.24
 */
class_alias('JModel', 'JModelLegacy');
