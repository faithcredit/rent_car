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

/**
 * The view class used by the MVC framework to render a layout.
 * The view is dispatched by the main controller if the value contained 
 * in $_REQUEST['task'] is equals to 'ComponentView' + $_REQUEST['task'].
 *
 * e.g. $_REQUEST['task'] = 'groups' -> ComponentViewGroups
 *
 * A view can be related to a model and/or to a controller (both optional).
 *
 * It is possible to create theme overrides for the views by adding the specific
 * file into a path built as follows:
 * /wp-content/uploads/[PLUGIN_NAME]/overrides/[CLIENT]/[VIEW_NAME]/[LAYOUT].php
 *
 * For example, in case we need to override the default.php file of the site 'groups' view
 * that belong to the 'vik' plugin, the path will look like:
 * /wp-content/uploads/vik/overrides/site/groups/default.php
 *
 * The client can assume only 2 values: site or admin.
 *
 * @since 10.0
 */
#[\AllowDynamicProperties]
abstract class JView
{
	/**
	 * The view base path.
	 *
	 * @var string
	 */
	protected $_basePath;

	/**
	 * The name of the default template source file.
	 *
	 * @var string
	 */
	protected $_template = null;

	/**
	 * The name of the layout source file.
	 *
	 * @var string
	 */
	protected $_layout = null;

	/**
	 * The view model.
	 *
	 * @var JModel
	 */
	protected $_model = null;

	/**
	 * The view name.
	 *
	 * @var   string
	 * @since 10.1.15
	 */
	protected $_name = null;

	/**
	 * The document instance.
	 *
	 * @var   JDocument
	 * @since 10.1.19
	 */
	public $document = null;

	/**
	 * Class constructor.
	 *
	 * @param 	string 	$base 	The view base path.
	 */
	public function __construct($base)
	{
		$this->_basePath = $base;

		$this->document = JFactory::getDocument();
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  	The name of the template file to parse;
	 * 							automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @uses 	loadTemplate()
	 */
	public function display($tpl = null)
	{
		$str = $this->loadTemplate($tpl);

		if (is_string($str))
		{
			echo $str;
		}
	}

	/**
	 * Sets a specific layout to use.
	 *
	 * @param 	string 	$layout  The layout name.
	 *
	 * @return 	void
	 */
	public function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	/**
	 * Load a template file within the /tmpl folder of the view.
	 *
	 * @param   string  $tpl  	The name of the template source file;
	 * 							automatically searches the template paths and compiles as needed.
	 *
	 * @return  string  The output of the template script.
	 *
	 * @throws  Exception
	 *
	 * @uses 	_getTemplateBasePath()
	 */
	public function loadTemplate($tpl = null)
	{
		$file = 'default';

		/**
		 * Custom layout should be evaluated first in order to
		 * prevent URL injection when setLayout() is called.
		 *
		 * @since 10.1.18
		 */
		if (!is_null($this->_layout))
		{
			$file = $this->_layout;
		}
		
		/**
		 * Do not use an ELSEIF statement (as it was earlier) because we have to append the given
		 * template also in case the configured layout is different than "default", otherwise we
		 * risk to enter in an infinite loop.
		 * 
		 * @since 10.1.41
		 */
		if (!is_null($tpl))
		{
			$file .= '_' . $tpl;
		}

		/**
		 * Try to search for an override of this view.
		 *
		 * @since 10.1.2
		 */
		$this->_template = $this->_getTemplateBasePath($this->_basePath, $file);

		if (!$this->_template)
		{
			/**
			 * It doesn't exist an override of this view.
			 * Take the default one declared by the plugin.
			 *
			 * @note 	Use _template property to avoid injecting a useless variable.
			 */
			$this->_template = $this->_basePath . DIRECTORY_SEPARATOR . 'tmpl';
		}

		// concat the layout file to the template path
		$this->_template .=  DIRECTORY_SEPARATOR . $file . '.php';

		if (!is_file($this->_template))
		{
			$err = JText::_('TEMPLATE_VIEW_NOT_FOUND_ERR');

			if (WP_DEBUG)
			{
				$err .= "\nFile: [" . $file . ".php].";
			}

			throw new Exception($err, 404);
		}
		// unset method vars to not introduce them in the template
		unset($tpl, $file);

		// start capturing output into a buffer
		ob_start();

		// include the requested template filename in the local scope
		include $this->_template;

		// obtain the requested template
		$output = ob_get_contents();

		// get the buffer and clear it
		ob_end_clean();

		return $output;
	}

	/**
	 * Sets the view model.
	 *
	 * @param 	JModel 	$model 	The view model to set.
	 *
	 * @return 	void
	 */
	public function setModel(JModel $model)
	{
		$this->_model = $model;
	}

	/**
	 * Returns the view model.
	 *
	 * @return 	JModel 	The view model, if any.
	 */
	public function getModel()
	{
		return $this->_model;
	}

	/**
	 * Searches for an override of the specified view and layout.
	 *
	 * @param 	string 	$base 	 The default basepath containing the plugin and view name.
	 * @param 	string 	$layout  The layout name.
	 *
	 * @return 	mixed 	The override base path if exists, otherwise false.
	 *
	 * @since 	10.1.2
	 */
	protected function _getTemplateBasePath($base, $layout)
	{
		/**
		 * Make sure the base path contains the "plugin" name and the "client" section.
		 *
		 * @since 10.1.27  Added support for Windows backslash.
		 */
		if (!preg_match("/[\/\\\\]plugins[\/\\\\](.*?)[\/\\\\](.*?)[\/\\\\]/", $base, $match))
		{
			// malformed base path, don't proceed
			return false;
		}

		$upload = wp_upload_dir();

		$parts = array();
		// push base upload path
		$parts[] = $upload['basedir'];
		// push plugin name
		$parts[] = $match[1];
		// push default overrides folder
		$parts[] = 'overrides';
		// push client dir (site or admin)
		$parts[] = $match[2];
		// push view name
		$parts[] = basename($base);

		// Implode parts to build the template base path.
		// DO NOT concat the file name as this method must
		// return only the base path.
		$template = implode(DIRECTORY_SEPARATOR, $parts);

		if (is_file($template . DIRECTORY_SEPARATOR . $layout . '.php'))
		{
			// the resulting override exists, return the updated base path
			return $template;
		}

		// override not found
		return false;
	}

	/**
	 * Method to get the view name.
	 *
	 * @return  string 	The name of the view.
	 *
	 * @since 	10.1.15
	 */
	public function getName()
	{
		if (is_null($this->_name))
		{
			$class = get_class($this);

			if (preg_match("/View(.*?)$/", $class, $match))
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
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @since 	10.1.20
	 */
	public function escape($output)
	{
		/**
		 * Attributes are now escaped by using the built-in WP function.
		 *
		 * @since 10.1.33
		 */
		return esc_attr($output);
	}
}
