<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.module
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.form.form');

/**
 * Adapter class to extend WP widget functionalities.
 *
 * @see 	WP_Widget
 * @since 	10.0
 */
class JWidget extends WP_Widget
{
	/**
	 * Absolute module path.
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	protected $_id;

	/**
	 * Widget form data.
	 *
	 * @var JForm
	 */
	protected $_form = null;

	/**
	 * The name of the plugin that owns the module.
	 *
	 * @var string
	 */
	protected $_option = null;

	/**
	 * Whether or not the widget has been registered yet.
	 *
	 * @var   boolean
	 * @since 10.1.38
	 */
	protected $registered = false;

	/**
	 * Class constructor.
	 *
	 * @param 	string 	$path  The widget absolute path.
	 *
	 * @uses 	loadLanguage()
	 * @uses 	loadXml()
	 */
	public function __construct($path)
	{
		// widget ID
		$id = basename($path);

		$this->_path = $path;
		$this->_id 	 = $id;

		/**
		 * Extract component name from path.
		 *
		 * @since 10.1.20
		 */
		if (preg_match("/plugins[\/\\\\]([a-z0-9_]+)[\/\\\\]modules/i", $this->_path, $match))
		{
			$this->_option = end($match);
		}

		// load text domain
		$this->loadLanguage($path, $id);

		/**
		 * @note: translations will be available only from here.
		 */

		// load widget data from XML
		$data = $this->loadXml($path, $id);

		// translate widget name
		$name = JText::_((string) $data->name);

		// build arguments
		$args = array();
		$args['description'] = JText::_((string) $data->description);
		// $args['version']	 = $data->version;

		/**
		 * Since the widget description is displayed by escaping HTML tags,
		 * we should strip them in order to display a plain text.
		 *
		 * @since 10.1.21
		 */
		$args['description'] = strip_tags($args['description']);

		parent::__construct($id, $name, $args);

		/**
		 * Add support for jQuery in page head every time 
		 * a widget is instantiated. Proceed only in case the
		 * headers haven't been sent yet.
		 *
		 * @since 10.1.22
		 */
		if (!headers_sent())
		{
			add_filter('wp_enqueue_scripts', function()
			{
				wp_enqueue_script('jquery', null, array(), false, false);
			});
		}
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param 	array 	$args    Widget arguments.
	 * @param 	array 	$config  Saved values from database.
	 *
	 * @return 	void
	 */
	public function widget($args, $config)
	{
		// make the module helper accessible
		JLoader::import('adapter.module.helper');
		JModuleHelper::setPath($this->_path);

		/**
		 * Include system.js file to support JFormValidator.
		 *
		 * @todo 	Check whether this callback is actually needed.
		 * 			The comment below mentions about the support for
		 * 			the validator, but it shouldn't be used in the
		 * 			front-end. In case the system.js file is required
		 * 			we could consider to load Bootstrap only in case
		 * 			we are in the site section.
		 */
		JHtml::_('system.js');

		/**
		 * Added support for module class suffix.
		 *
		 * @since 10.1.21
		 */
		if (!empty($config['moduleclass_sfx']))
		{
			// extract class from wrapper
			if (preg_match("/class=\"([a-z0-9_\-\s]*)\"/i", $args['before_widget'], $match))
			{
				// replace class attribute with previous classes and the custom suffix
				$args['before_widget'] = str_replace($match[0], 'class="' . $match[1] . ' ' . $config['moduleclass_sfx'] . '"', $args['before_widget']);
			}
		}
		
		// begin widget
		echo $args['before_widget'];

		// display the title if set
		if (!empty($config['title']))
		{
			echo $args['before_title'] . apply_filters('widget_title', $config['title']) . $args['after_title'];
		}

		$layout = $this->_path . DIRECTORY_SEPARATOR . $this->_id . '.php';

		// check if the widget owns a layout
		if (!is_file($layout))
		{
			return;
		}

		// wrap the $config in a registry
		$params = new JObject($config);

		/**
		 * Create $module object for accessing the widget ID.
		 *
		 * @since 10.1.30
		 */
		$module = new stdClass;
		$module->id = $this->number;

		/**
		 * Plugins can manipulate the configuration of the widget at runtime.
		 * Fires before dispatching the widget in the front-end.
		 *
		 * @param 	string   $id       The widget ID (path name).
		 * @param 	JObject  &$params  The widget configuration registry.
		 *
		 * @since 	10.1.28
		 */
		do_action_ref_array('vik_widget_before_dispatch_site', array($this->_id, &$params));

		// start buffer
		ob_start();
		// include layout file
		include $layout;
		// get contents
		$html = ob_get_contents();
		// clear buffer
		ob_end_clean();

		/**
		 * Plugins can manipulate here the fetched HTML of the widget.
		 * Fires before displaying the HTML of the widget in the front-end.
		 *
		 * @param 	string  $id     The widget ID (path name).
		 * @param 	string  &$html  The HTML of the widget to display.
		 *
		 * @since   10.1.28
		 */
		do_action_ref_array('vik_widget_after_dispatch_site', array($this->_id, &$html));

		// display the widget HTML
		echo $html;

		// terminate widget
		echo $args['after_widget'];

		// print JSON configuration
		JHtml::_('behavior.core');

		// add support for Joomla JS variable
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
if (typeof Joomla === 'undefined') {
	var Joomla = new JoomlaCore();
} else {
	// reload options
	JoomlaCore.loadOptions();
}
JS
		);
	}

	/**
	 * Loads widget text domain.
	 *
	 * @param 	string 	$path  	The widget path.
	 * @param 	string 	$id 	The domain name.
	 *
	 * @return 	void
	 */
	private function loadLanguage($path, $id)
	{
		// init language
		$lang = JFactory::getLanguage();
		
		// search for a language handler (/language/handler.php)
		$handler = $path . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'handler.php';

		if (!is_file($handler))
		{
			/**
			 * Try also to search within "languages" folder.
			 *
			 * @since 10.1.21
			 */
			$handler = $path . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . 'handler.php';
		}

		if (is_file($handler))
		{
			// attach handler
			$lang->attachHandler($handler, $id);
		}

		/**
		 * @since 10.0.1 	It is no more needed to load the language
		 * 					file (.mo) of the widget as all the translations
		 * 					are contained within the main language file
		 * 					of the plugin.
		 */
	}

	/**
	 * Loads widget data from the XML installation file.
	 *
	 * @param 	string 	$path  	The widget path.
	 * @param 	string 	$id 	The widget name.
	 *
	 * @return 	object 	The XML data.
	 */
	private function loadXml($path, $id)
	{
		$file = $path . DIRECTORY_SEPARATOR . $id . '.xml';

		// make sure the installation file exists
		if (!is_file($file))
		{
			throw new Exception('Missing installation file [' . $id . '.xml].', 404);
		}

		// load form data
		$this->_form = JForm::getInstance($id, $file, array('client' => $this->_option));

		// get XML element
		$xml = $this->_form->getXml();

		$data = new stdClass;

		// iterate the args and assign them to the $data object
		foreach (array('name', 'description') as $k)
		{
			$data->{$k} = (string) $xml->{$k};
		}

		return $data;
	}

	/**
	 * Back-end widget form.
	 *
	 * @param 	array 	$instance 	Previously saved values from database.
	 *
	 * @return 	void
	 */
	public function form($instance)
	{
		// get form fields
		$fields = $this->_form->getFields();

		/**
		 * Add support for title field by creating a custom XML field,
		 * only if the XML of the module doesn't declare it.
		 *
		 * @since 10.1.21
		 */
		if (!$this->_form->getField('title'))
		{
			// create title field
			$title = simplexml_load_string('<field name="title" type="text" default="" label="TITLE" />');
			// push title at the beginning of the list
			array_unshift($fields, $title);
		}

		/**
		 * Filter the fields by removing useless settings.
		 *
		 * @since 10.1.31
		 */
		$fields = array_filter($fields, function($field)
		{
			// exclude field in case it starts with "loadjquery"
			return preg_match("/^loadjquery/", (string) $field->attributes()->name) == false;
		});

		// create layout file
		$file = new JLayoutFile('html.widget.fieldset.open');

		if ($this->_option)
		{
			// we found an option, add an include path to make sure layouts are accessible
			$file->addIncludePath(implode(DIRECTORY_SEPARATOR, array(WP_PLUGIN_DIR, $this->_option, 'libraries')));
		}

		// open fieldset
		echo $file->render();

		foreach ($fields as $field)
		{
			$attrs  = $field->attributes();
			$name 	= (string) $attrs->name;

			$data = array();
			$data['id'] 		 = $this->get_field_id($name);
			$data['label'] 		 = (string) $attrs->label;
			$data['description'] = (string) $attrs->description;
			$data['name']		 = $this->get_field_name($name);
			$data['required'] 	 = ((string) $attrs->required) === 'true';

			/**
			 * Open control only in case the input shouldn't be hidden.
			 *
			 * @since 10.1.21
			 */
			if ($attrs->type != 'hidden' && $attrs->type != 'spacer' && empty($attrs->hidden))
			{
				// open control
				$file->setLayoutId('html.widget.control.open');
				echo $file->render($data);
			}

			if (isset($instance[$name]))
			{
				$data['value'] = $instance[$name];
			}

			// attach module path (useful to obtain the available layouts)
			$data['modpath']  = $this->_path;
			$data['modowner'] = $this->_option;

			// obtain field class and display input layout
			echo $this->_form->renderField($field, $data);

			/**
			 * Close control only in case the input shouldn't be hidden.
			 *
			 * @since 10.1.21
			 */
			if ($attrs->type != 'hidden' && $attrs->type != 'spacer' && empty($attrs->hidden))
			{
				// close control
				$file->setLayoutId('html.widget.control.close');
				echo $file->render();
			}
		}

		// close fieldset
		$file->setLayoutId('html.widget.fieldset.close');
		echo $file->render();

		// include form scripts
		// $this->useScript();
	}

	/**
	 * Includes the scripts used by the form.
	 *
	 * @return 	void
	 */
	protected function useScript()
	{
		if (wp_doing_ajax())
		{
			return;
		}

		$document = JFactory::getDocument();

		/**
		 * Include system.js file to support JFormValidator.
		 * 
		 * Since WP 5.9, the widgets resources must be loaded through the
		 * _register_one method, which seems to be invoked on every page.
		 * So, we should load them only if we are under widgets.php.
		 */
		global $pagenow;
		if ($pagenow === 'widgets.php')
		{
			JHtml::_('system.js');
		}

		JHtml::_('formbehavior.chosen');

		static $loaded = 0;

		// load only once
		if (!$loaded)
		{
			// override getLabel() method to attach invalid
			// class to the correct form structure
			$document->addScriptDeclaration(
<<<JS
if (typeof JFormValidator !== 'undefined') {
	JFormValidator.prototype.getLabel = function(input) {
		var name = jQuery(input).attr('name');	

		if (this.labels.hasOwnProperty(name)) {
			return jQuery(this.labels[name]);
		}

		return jQuery(input).parent().find('label').first();
	}
}
JS
			);
		}

		// load form validation
		$document->addScriptDeclaration(
<<<JS
if (typeof VIK_WIDGET_SAVE_LOOKUP === 'undefined') {
	var VIK_WIDGET_SAVE_LOOKUP = {};
}

(function($) {
	$(document).on('widget-added', function(event, control) {
		registerWidgetScripts($(control).find('form'));
	});

	function registerWidgetScripts(form) {
		if (!form) {
			// if the form was not provided, find it using the widget ID (before WP 5.8)
			form = $('div[id$="{$this->id}"] form');
		}

		if (typeof JFormValidator !== 'undefined') {
			// init internal validator
			var validator = new JFormValidator(form);

			// validate fields every time the SAVE button is clicked
			form.find('input[name="savewidget"]').on('click', function(event) {
				return validator.validate();
			});
		}

		// init select2 on dropdown with multiple selection
		if (jQuery.fn.select2) {
			form.find('select[multiple]').select2({
				width: '100%'
			});
		}

		// initialize popover within the form
		if (jQuery.fn.popover) {
			form.find('.inline-popover').popover({sanitize: false, container: 'body'});
		}
	}

	$(function() {
		// If the widget is not a template, register the scripts.
		// A widget template ID always ends with "__i__"
		if (!"{$this->id}".match(/__i__$/)) {
			registerWidgetScripts();
		}

		// Attach event to the "ADD WIDGET" button
		$('.widgets-chooser-add').on('click', function(e) {
			// find widget parent of the clicked button
			var parent = this.closest('div[id$="{$this->id}"]');

			if (!parent) {
				return;
			}

			// extract ID from the template parent (exclude "__i__")
			var id = $(parent).attr('id').match(/(.*?)__i__$/);

			if (!id) {
				return;
			}

			// register scripts with a short delay to make sure the
			// template has been moved on the right side
			setTimeout(function() {
				// obtain the box that has been created
				var createdForm = $('div[id^="' + id.pop() + '"]').last();

				// find form within the box
				var _form = $(createdForm).find('form');

				// register scripts at runtime
				registerWidgetScripts(_form);
			}, 32);
		});

		// register save callback for this kind of widget only once
		if (!VIK_WIDGET_SAVE_LOOKUP.hasOwnProperty('{$this->_id}')) {
			// flag as loaded
			VIK_WIDGET_SAVE_LOOKUP['{$this->_id}'] = 1;

			// Attach event to SAVE callback
			$(document).ajaxSuccess(function(event, xhr, settings) {
				// make sure the request was used to save the widget settings
				if (!settings.data || typeof settings.data !== 'string' || settings.data.indexOf('action=save-widget') === -1) {
					// wrong request
					return;
				}

				// extract widget ID from request
				var widget_id = settings.data.match(/widget-id=([a-z0-9_-]+)(?:&|$)/i);

				// make sure this is the widget that was saved
				if (!widget_id) {
					// wrong widget
					return;
				}

				// get cleansed widget ID
				widget_id = widget_id.pop();

				// make sure the widget starts with this ID
				if (widget_id.indexOf('{$this->_id}') !== 0) {
					// wrong widget
					return;
				}

				// obtain the box that has been updated
				var updatedForm = $('div[id$="' + widget_id + '"]').find('form');

				// register scripts at runtime
				registerWidgetScripts(updatedForm);
			});
		}
	});
})(jQuery);
JS
		);
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param 	array 	$new_instance 	Values just sent to be saved.
	 * @param 	array 	$old_instance 	Previously saved values from database.
	 *
	 * @return 	array 	Updated safe values to be saved.
	 *
	 * @since 	10.1.21
	 */
	public function update($new_instance, $old_instance)
	{
		if (!empty($new_instance['moduleclass_sfx']))
		{
			// make mod class suffix safe
			$new_instance['moduleclass_sfx'] = preg_replace("/[^a-zA-Z0-9_\-\s]+/", '', $new_instance['moduleclass_sfx']);
		}

		return $new_instance;
	}

	/**
	 * Add hooks for enqueueing assets when registering all widget instances of this widget class.
	 *
	 * @param 	integer  $number  Optional. The unique order number of this widget instance
	 *                            compared to other instances of the same class. Default -1.
	 * 
	 * @return 	void
	 * 
	 * @since 	10.1.38
	 */
	public function _register_one($number = -1)
	{
		// invoke parent
		parent::_register_one($number);

		if (!$this->registered)
		{
			// load required resources
			$this->useScript();

			// flag as already registered
			$this->registered = true;
		}		
	}
}
