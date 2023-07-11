<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.dashboard
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class used to encapsulate the information of
 * a widget that can be published within the
 * dashboard of WordPress.
 *
 * @since 10.1.31
 */
abstract class JDashboardWidget
{
	/**
	 * Returns the name of the widget.
	 *
	 * @return 	string
	 */
	abstract public function getName();

	/**
	 * Returns the string that will be used within
	 * the ID and CLASS attributes of the widget.
	 *
	 * @return 	string
	 */
	public function getID()
	{
		// by default generate the ID starting from the class name
		$id = get_class($this);

		// remove base class name, if specified
		$id = preg_replace("/^JDashboardWidget/i", '', $id);

		// add an underscore before every camel case
		$id = preg_replace("/([a-z])([A-Z])/", '$1_$2', $id);

		// always use lower-case letters
		return strtolower($id);
	}

	/**
	 * Returns an optional description of the widget.
	 * Implement this method in order to avoid forcing
	 * its declaration in subclasses.
	 *
	 * @return 	string
	 */
	public function getDescription()
	{
		return '';
	}

	/**
	 * Checks whether the specified user is able to access
	 * this widget. Implement this method in order to avoid
	 * forcing its declaration in subclasses.
	 *
	 * @param 	mixed 	 $user  The user to check.
	 *
	 * @return 	boolean
	 */
	public function canAccess($user = null)
	{
		return true;
	}

	/**
	 * Returns the HTML that will be used to display the
	 * contents of the widget.
	 *
	 * @param 	mixed  $object  Gets passed to the meta box callback function as the first parameter.
	 *                          Often this is the object that's the focus of the current screen, for
	 *                          example a `WP_Post` or `WP_Comment` object.
	 * @param 	array  $widget  The arguments passed to the `wp_add_dashboard_widget` function.
	 *
	 * @return 	void
	 */
	public function getHtml($object, $widget)
	{
		// render contents by passing the widget settings, if any
		echo $this->renderWidget($widget['args']);
	}

	/**
	 * Renders the HTML to display within the contents of the widget.
	 * 
	 * @param 	mixed 	$args  A registry of settings.
	 *
	 * @return 	string  The HTML to display.
	 */
	abstract protected function renderWidget($args);

	/**
	 * Returns an instance of the form, if supported.
	 * Implement this method in order to avoid forcing
	 * its declaration in subclasses.
	 *
	 * @return 	null|JForm
	 */
	public function getForm()
	{
		return null;
	}

	/**
	 * Returns the configuration of the widget.
	 *
	 * @return 	JRegistry  A registry of the configuration.
	 */
	public function getConfig()
	{
		// fetch all dashboard widget options stored within the database
		$options = get_option('dashboard_widget_options', array());
		
		// get widget ID
		$widget_id = $this->getID();

		$config = new JRegistry();

		// make sure the configuration of the widget exists
		if (isset($options[$widget_id]))
		{
			// set configuration settings within registry
			$config->setProperties($options[$widget_id]);
		}
	  
		return $config;
	}

	/**
	 * Returns the HTML that will be used to display the
	 * configuration form of the widget.
	 * Dispatches also the save method when the form
	 * is submitted.
	 *
	 * @param 	mixed  $object  Gets passed to the meta box callback function as the first parameter.
	 *                          Often this is the object that's the focus of the current screen, for
	 *                          example a `WP_Post` or `WP_Comment` object.
	 * @param 	array  $widget  The arguments passed to the `wp_add_dashboard_widget` function.
	 *
	 * @return 	void
	 */
	public function config($object, $widget)
	{
		$input = JFactory::getApplication()->input;

		if ($input->getBool('submit'))
		{
			// save the widget settings
			$this->save();
		}
		else
		{	
			// display the widget configuration
			echo $this->renderForm($this->getConfig());
		}
	}

	/**
	 * Renders the HTML to display within the configuration of the widget.
	 * Implement this method in order to avoid forcing its declaration in subclasses.
	 * 
	 * @param 	mixed 	$args  A registry of settings.
	 *
	 * @return 	string  The HTML to display.
	 */
	protected function renderForm($args)
	{
		// try to get XML form
		$form = $this->getForm();

		$html = '';

		if ($form)
		{
			// render form
			$html = $form->renderForm($args ? (object) $args->getProperties() : array());
		}

		return $html;
	}

	/**
	 * Saves the configuration of the widget.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	public function save()
	{
		// fetch all dashboard widget options stored within the database
		$options = get_option('dashboard_widget_options', array());
		
		// get widget ID
		$widget_id = $this->getID();

		// bind settings
		$options[$widget_id] = $this->bind();

		// update db option
		update_option('dashboard_widget_options', $options);

		return true;
	}

	/**
	 * Binds the configuration settings that will be saved.
	 * Implement this method in order to avoid forcing its declaration in subclasses.
	 *
	 * @return 	array  The associative array to save.
	 */
	protected function bind()
	{
		// try to get XML form
		$form = $this->getForm();

		$data = array();

		if ($form)
		{
			$input = JFactory::getApplication()->input;

			// get form data
			$formData = $input->get('jform', array(), 'array');

			// get input filter
			$inputFilter = JInputFilter::getInstance();

			// iterate the layout fields
			foreach ($form->getFields() as $field)
			{
				$attrs = $field->attributes();

				$name 	= (string) $attrs->name;
				$filter = (string) $attrs->filter;

				// use string if no filter
				if (empty($filter))
				{
					$filter = 'string';
				}

				if (isset($formData[$name]))
				{
					// clean filter in request
					$value = $inputFilter->clean($formData[$name], $filter);
				}
				else
				{
					// use NULL
					$value = null;
				}

				// save value only if not NULL
				if ($value !== null)
				{
					$data[$name] = $value;
				}
			}
		}

		return $data;
	}
}
