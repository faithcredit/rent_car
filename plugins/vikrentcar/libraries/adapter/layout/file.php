<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.layout
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.layout.base');
JLoader::import('adapter.filesystem.path');

/**
 * Base class for rendering a display layout loaded from from a layout file.
 *
 * It is possible to create theme overrides for the layouts by adding the specific
 * file into a path built as follows:
 * /wp-content/uploads/[PLUGIN_NAME]/layouts/[CLIENT]/[LAYOUT_PATH].php
 *
 * For example, in case we need to override the site 'html.user.login' layout
 * that belong to the 'vik' plugin, the path will look like:
 * /wp-content/uploads/vik/layouts/site/html/user/login.php
 *
 * The client can assume only 2 values: site or admin.
 *
 * @since 10.0
 */
class JLayoutFile extends JLayoutBase
{
	/**
	 * A list containing the cached layout paths.
	 *
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * The identifier of the layout file to render.
	 *
	 * @var string
	 */
	protected $layoutId = '';

	/**
	 * The base path of the files to include.
	 *
	 * @var string
	 */
	protected $basePath = null;

	/**
	 * Full path to actual layout files, after possible template override check.
	 *
	 * @var   string
	 * @since 10.1.18
	 */
	protected $fullPath = null;

	/**
	 * Paths to search for layouts.
	 *
	 * @var   array
	 * @since 10.1.18
	 */
	protected $includePaths = array();

	/**
	 * Method to instantiate the file-based layout.
	 *
	 * @param   string  $layoutId  Dot separated path to the layout file, relative to base path.
	 * @param   string  $basePath  Base path to use when loading layout files.
	 * @param   mixed   $options   Optional custom options to load. Registry or array format.
	 */
	public function __construct($layoutId, $basePath = null, $options = null)
	{
		// initialise / load options
		$this->setOptions($options);

		// main properties
		$this->setLayoutId($layoutId);
		$this->basePath = $basePath;

		// init enviroment
		$this->setComponent($this->options->get('component', 'auto'));
		$this->setClient($this->options->get('client', 'auto'));
	}

	/**
	 * Method to render the layout.
	 *
	 * @param   array   $displayData  Array of properties available for use inside
	 * 								  the layout file to build the displayed output.
	 *
	 * @return  string  The necessary HTML to display the layout.
	 *
	 * @uses 	clearDebugMessages()
	 * @uses 	getPath()
	 * @uses 	isDebugEnabled()
	 * @uses 	renderDebugMessages()
	 */
	public function render($displayData = array())
	{
		$this->clearDebugMessages();

		// inherit base output from parent class
		$layoutOutput = '';

		// automatically merge any previously data set if $displayData is an array
		if (is_array($displayData))
		{
			$displayData = array_merge($this->data, $displayData);
		}

		// check possible overrides, and build the full path to layout file
		$path = $this->getPath();

		if ($this->isDebugEnabled())
		{
			echo '<pre>' . $this->renderDebugMessages() . '</pre>';
		}

		// nothing to show
		if (empty($path))
		{
			return $layoutOutput;
		}

		// start buffer
		ob_start();
		// include the file
		include $path;
		// push the buffer data in a variable
		$layoutOutput = ob_get_contents();
		// end buffer end clean
		ob_end_clean();

		return $layoutOutput;
	}

	/**
	 * Method to find the full real file path, checking possible overrides.
	 *
	 * @return  string  The full path to the layout file.
	 *
	 * @uses 	getLayoutId()
	 * @uses 	getIncludePaths()
	 * @uses 	addDebugMessage()
	 */
	protected function getPath()
	{
		$layoutId     = $this->getLayoutId();
		$includePaths = $this->getIncludePaths();

		$this->addDebugMessage('<strong>Layout:</strong> ' . $this->layoutId);

		// make sure we have something to render
		if (!$layoutId)
		{
			$this->addDebugMessage('<strong>There is no active layout</strong>');

			return;
		}

		// make sure some paths have been specified
		if (!$includePaths)
		{
			$this->addDebugMessage('<strong>There are no folders to search for layouts:</strong> ' . $layoutId);

			return;
		}

		// create signature key
		$hash = md5(json_encode($includePaths));

		// check if the same layout has been already cached
		if (!empty(static::$cache[$layoutId][$hash]))
		{
			$this->addDebugMessage('<strong>Cached path:</strong> ' . static::$cache[$layoutId][$hash]);

			return static::$cache[$layoutId][$hash];
		}

		$this->addDebugMessage('<strong>Include Paths:</strong> ' . print_r($includePaths, true));

		// standard version
		$rawPath  = str_replace('.', '/', $this->layoutId) . '.php';
		$this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

		// search for a layout file
		$foundLayout = JPath::find($this->includePaths, $rawPath);

		if (!$foundLayout)
		{
			// impossible to find any layouts
			$this->addDebugMessage('<strong>Unable to find layout: </strong> ' . $layoutId);

			return;
		}

		$this->addDebugMessage('<strong>Found layout:</strong> ' . $foundLayout);

		// cache the layout found for later uses
		static::$cache[$layoutId][$hash] = $foundLayout;

		return static::$cache[$layoutId][$hash];
	}

	/**
	 * Adds one path to include in layout search.
	 * Proxy of addIncludePaths().
	 *
	 * @param   string  $path  The path to search for layouts.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @since 	10.1.18
	 *
	 * @uses 	addIncludePaths()
	 */
	public function addIncludePath($path)
	{
		$this->addIncludePaths($path);

		return $this;
	}

	/**
	 * Adds one or more paths to include in layout search.
	 *
	 * @param   mixed  $paths  The path or array of paths to search for layouts.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @since 	10.1.18
	 *
	 * @uses 	getIncludePaths()
	 * @uses 	setIncludePaths()
	 */
	public function addIncludePaths($paths)
	{
		if (empty($paths))
		{
			return $this;
		}

		$includePaths = $this->getIncludePaths();

		// in case the path is an array, merge all the paths and make sure we have no duplicated
		if (is_array($paths))
		{
			$includePaths = array_unique(array_merge($paths, $includePaths));
		}
		// otherwise add the path as first element
		else
		{
			array_unshift($includePaths, $paths);
		}

		// update include paths
		$this->setIncludePaths($includePaths);

		return $this;
	}

	/**
	 * Clears the include paths.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @since 	10.1.18
	 */
	public function clearIncludePaths()
	{
		$this->includePaths = array();

		return $this;
	}

	/**
	 * Gets the active include paths.
	 *
	 * @return  array
	 *
	 * @since 	10.1.18
	 *
	 * @uses 	getDefaultIncludePaths()
	 */
	public function getIncludePaths()
	{
		if (empty($this->includePaths))
		{
			$this->includePaths = $this->getDefaultIncludePaths();
		}

		return $this->includePaths;
	}

	/**
	 * Gets the active layout id.
	 *
	 * @return  string
	 *
	 * @since 	10.1.18
	 */
	public function getLayoutId()
	{
		return $this->layoutId;
	}

	/**
	 * Removes one path from the layout search.
	 * Proxy of removeIncludePaths().
	 *
	 * @param   string  $path  The path to remove from the layout search.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @since   10.1.18
	 *
	 * @uses 	removeIncludePaths()
	 */
	public function removeIncludePath($path)
	{
		$this->removeIncludePaths($path);

		return $this;
	}

	/**
	 * Removes one or more paths to exclude in layout search.
	 *
	 * @param   mixed 	$paths  The path or array of paths to remove for the layout search.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @since   10.1.18
	 */
	public function removeIncludePaths($paths)
	{
		if (!empty($paths))
		{
			// always use an array of paths
			$paths = (array) $paths;

			// obtain a list of paths by excluding the specified ones
			$this->includePaths = array_diff($this->includePaths, $paths);
		}

		return $this;
	}

	/**
	 * Validates that the active component is valid.
	 *
	 * @param   string   $option  URL Option of the component (e.g. com_xxx).
	 *
	 * @return  boolean  True if valid, false otherwise.
	 *
	 * @since   10.1.18
	 */
	protected function validComponent($option = null)
	{
		// by default we will validate the active component
		$component = ($option !== null) ? $option : $this->options->get('component', null);

		// validate option format
		return !empty($component) && preg_match("/^com_/", $component);
	}

	/**
	 * Method to change the component where search for layouts.
	 *
	 * @param   string 	$option  URL Option of the component (e.g. com_xxx).
	 *
	 * @return  void
	 *
	 * @since   10.1.18
	 *
	 * @uses 	validComponent()
	 * @uses 	clearIncludePaths()
	 */
	public function setComponent($option)
	{
		$component = null;

		switch ((string) $option)
		{
			case 'none':
				$component = null;
				break;

			case 'auto':
				// recover component name from request
				$component = JFactory::getApplication()->input->get('option', null);
				break;

			default:
				$component = $option;
				break;
		}

		// extra checks
		if (!$this->validComponent($component))
		{
			$component = null;
		}
		else
		{
			$component = preg_replace("/^com_/", '', $component);
		}

		// update component option
		$this->options->set('component', $component);

		// refresh include paths
		$this->clearIncludePaths();
	}

	/**
	 * Function to initialise the application client.
	 *
	 * @param   mixed  $client  The application client:
	 * 							- 0 or 'site':  front-end;
	 * 							- 1 or 'admin': back-end.
	 *
	 * @return  void
	 *
	 * @since   10.1.18
	 *
	 * @uses 	clearIncludePaths()
	 */
	public function setClient($client)
	{
		// force string conversion to avoid unexpected states
		switch ((string) $client)
		{
			case 'site':
			case '0':
				$client = 0;
				break;

			case 'admin':
			case '1':
				$client = 1;
				break;

			default:
				$client = (int) JFactory::getApplication()->isAdmin();
				break;
		}

		// update client option
		$this->options->set('client', $client);

		// refresh include paths
		$this->clearIncludePaths();
	}

	/**
	 * Sets the active layout id.
	 *
	 * @param   string  $layoutId  Layout identifier.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @since   10.1.18
	 */
	public function setLayoutId($layoutId)
	{
		$this->layoutId = $layoutId;
		$this->fullPath = null;

		return $this;
	}

	/**
	 * Gets the default array of include paths.
	 *
	 * @return  array
	 *
	 * @since   10.1.18
	 */
	public function getDefaultIncludePaths()
	{
		$paths = array();

		// (1 - highest priority) Received a custom high priority path
		if ($this->basePath !== null)
		{
			$paths[] = rtrim($this->basePath, DIRECTORY_SEPARATOR);
		}

		// component layouts & overrides if exist
		$component = $this->options->get('component', null);

		if (!empty($component))
		{
			// get upload dir
			$uploads = wp_upload_dir();

			if ($this->options->get('client') == 0)
			{
				// (2) build component override site layouts path
				$paths[] = rtrim($uploads['basedir'], DIRECTORY_SEPARATOR) . '/' . $component . '/layouts/site';

				// (3) build component site layouts path
				$paths[] = WP_PLUGIN_DIR . '/' . $component . '/site/layouts';
			}
			else
			{
				// (2) build component override admin layouts path
				$paths[] = rtrim($uploads['basedir'], DIRECTORY_SEPARATOR) . '/' . $component . '/layouts/admin';

				// (3) build component admin layouts path
				$paths[] = WP_PLUGIN_DIR . '/' . $component . '/admin/layouts';
			}

			// (4) build libraries layouts path
			$paths[] = WP_PLUGIN_DIR . '/' . $component . '/libraries';
		}

		return $paths;
	}

	/**
	 * Sets the include paths to search for layouts.
	 *
	 * @param   array 	$paths  Array with paths to search in.
	 *
	 * @return  self 	This object to support chaining.
	 *
	 * @since   10.1.18
	 */
	public function setIncludePaths($paths)
	{
		$this->includePaths = (array) $paths;

		return $this;
	}

	/**
	 * Render a layout with the same include paths & options.
	 *
	 * @param   string  $layoutId     The identifier for the sublayout to be searched in a
	 * 								  subfolder with the name of the current layout.
	 * @param   mixed   $displayData  Data to be rendered.
	 *
	 * @return  string  The necessary HTML to display the layout.
	 *
	 * @since   10.1.18
	 */
	public function sublayout($layoutId, $displayData = array())
	{
		// sublayouts are searched in a subfolder with the name of the current layout
		if (!empty($this->layoutId))
		{
			$layoutId = $this->layoutId . '.' . $layoutId;
		}

		// instantiate new layout file
		$sublayout = new static($layoutId, $this->basePath, $this->options);
		$sublayout->includePaths = $this->includePaths;

		// render sublayout
		return $sublayout->render($displayData);
	}
}
