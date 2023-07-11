<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.form
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.form.field');

/**
 * Form class to handle XML forms.
 *
 * This class implements a robust API for constructing, populating, filtering, and validating forms.
 * It uses XML definitions to construct form fields and a variety of field and rule classes to
 * render and validate the form.
 *
 * @since 10.0
 */
class JForm
{
	/**
	 * A list of JForm instances
	 *
	 * @var array
	 */
	protected static $forms = array();

	/**
	 * The form XML definition.
	 *
	 * @var SimpleXMLElement
	 */
	protected $xml;

	/**
	 * The form name.
	 *
	 * @var   string
	 * @since 10.1.20
	 */
	protected $name;

	/**
	 * The form options.
	 *
	 * @var   array
	 * @since 10.1.20
	 */
	protected $options;

	/**
	 * Method to get an instance of a form.
	 *
	 * @param   string 	$name     The name of the form.
	 * @param   string  $data     The name of an XML file or string to load as the form definition.
	 * @param   array   $options  An array of form options.
	 *
	 * @return  JForm 	A new JForm instance.
	 *
	 * @throws  InvalidArgumentException 	if no data provided.
	 * @throws  RuntimeException 			if the form could not be loaded.
	 */
	public static function getInstance($name, $data = null, $options = array())
	{
		// only instantiate the form if it does not already exist
		if (!isset(static::$forms[$name]))
		{
			if (is_string($data))
			{
				$data = trim($data);
			}

			if (empty($data))
			{
				// no provided data, throw an exception
				throw new InvalidArgumentException(
					sprintf('JForm::getInstance(%s, *%s*)',
						$name,
						gettype($data)
					),
					400
				);
			}

			// instantiate the form.
			static::$forms[$name] = new static($name, $options);

			// if the string starts with '<' load the XML as string
			if ($data instanceof SimpleXMLElement || substr($data, 0, 1) == '<')
			{
				if (static::$forms[$name]->load($data) == false)
				{
					throw new RuntimeException('JForm::getInstance() could not load form.', 500);
				}
			}
			else
			{
				if (static::$forms[$name]->loadFile($data) == false)
				{
					throw new RuntimeException(sprintf('JForm::getInstance() could not load file [%s].', $data), 500);
				}
			}
		}

		return static::$forms[$name];
	}

	/**
	 * Class constructor.
	 *
	 * @param   string 	$name     The name of the form.
	 * @param   array   $options  An array of form options.
	 *
	 * @since 	10.1.20
	 */
	public function __construct($name, $options = array())
	{
		$this->name    = $name;
		$this->options = (array) $options;
	}

	/**
	 * Returns a list of fieldsets.
	 * If the name is provided, returns only the match.
	 *
	 * @param 	string 	$set 	The fieldset name.
	 *
	 * @return 	array 	A list of fieldsets.
	 */
	public function getFieldset($set = null)
	{
		if (is_null($set))
		{
			// return all fieldsets
			return $this->xml->xpath('//fieldset');
		}

		return $this->xml->xpath('//fieldset[@name="' . $set . '"]');
	}

	/**
	 * Returns a list of fields that match the query.
	 *
	 * @param 	string 	$val 	The field key value.
	 * @param 	string 	$key 	The field key in which to search (name by default).
	 *
	 * @return 	array 	The matching XML elements.
	 */
	public function getFields($val = null, $key = 'name')
	{
		if (is_null($val))
		{
			// do not filter fields
			return $this->xml->xpath('//field');
		}

		return $this->xml->xpath('//field[@' . $key . '="' . $val . '"]');
	}

	/**
	 * Returns the specified field.
	 *
	 * @param 	string 	$val 	The field key value.
	 * @param 	string 	$key 	The field key in which to search (name by default).
	 *
	 * @return 	mixed 	The field XML element on success, otherwise null.
	 *
	 * @uses 	getFields()
	 */
	public function getField($val, $key = 'name')
	{
		$fields = $this->getFields($val, $key);

		// return first element if any, otherwise null
		return array_shift($fields);
	}

	/**
	 * Returns the loaded XML object.
	 *
	 * @return 	SimpleXMLElement
	 */
	public function getXml()
	{
		return $this->xml;
	}

	/**
	 * Method to load the form description from an XML string or object.
	 *
	 * @param   string   $data 	The name of an XML string or object.
	 *
	 * @return  boolean  True on success, otherwise false.
	 */
	public function load($data)
	{
		// if the data to load isn't already an XML element or string return false
		if (!($data instanceof SimpleXMLElement) && !is_string($data))
		{
			return false;
		}

		// attempt to load the XML if a string
		if (is_string($data))
		{
			$data = new SimpleXMLElement($data);

			// make sure the XML loaded correctly
			if (!$data)
			{
				return false;
			}
		}

		// if we have no XML definition at this point let's make sure we get one
		if (empty($this->xml))
		{
			$this->xml = $data;
		}

		// search for any fieldset that mentions "addfieldpath"
		$nodes = $this->xml->xpath('//fieldset[@addfieldpath!=""]');
		
		// iterate the nodes found
		foreach ($nodes as $node)
		{
			$path = (string) $node->attributes()->addfieldpath;

			/**
			 * Check if the [addfieldpath] attribute is defined using a Joomla path.
			 * For example:
			 * - /administrator/components/com_[option]/[path]
			 * - /components/com_[option]/[path]
			 *
			 * @since 10.1.16
			 */
			if (preg_match("/^\/(administrator)?\/components\/com_([a-z0-9_]+)\/?(.*)/i", $path, $parts))
			{
				// starts with option name
				$path = $parts[2] . '/';

				if ($parts[1] === 'administrator')
				{
					// use admin folder
					$path .= 'admin';
				}
				else
				{
					// use site folder
					$path .= 'site';
				}

				// concat remaining path
				$path .= '/' . $parts[3];
			}
			else if (!empty($this->options['client']))
			{
				/**
				 * Try to check if we have a caller within the options array.
				 *
				 * @since 10.1.20
				 */
				$path = $this->options['client'] . '/' . ltrim($path, '/');
			}

			$path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));

			// update form fields paths with a new path in which to search for custom handlers
			JFormField::addIncludePath($path);
		}

		return true;
	}

	/**
	 * Method to load the form description from an XML file.
	 *
	 * @param   string   $file 	The filesystem path of an XML file.
	 *
	 * @return  boolean  True on success, otherwise false.
	 *
	 * @uses 	load()
	 */
	public function loadFile($file)
	{
		// make sure the file exists
		if (!is_file($file))
		{
			return false;
		}

		// attempt to load the XML file
		$xml = simplexml_load_file($file);

		return $this->load($xml);
	}

	/**
	 * Renders the form layout.
	 *
	 * @param 	object 	$data 	The object data to bind:
	 * 							Property name = Field name;
	 * 							Property value = Field value.
	 *
	 * @return 	string 	The HTML form layout.
	 *
	 * @uses 	renderFieldset()
	 */
	public function renderForm($data = null)
	{
		return $this->renderFieldset(null, $data);
	}

	/**
	 * Renders the layout of the given form fieldset.
	 * If fieldset is not given, renders all the fieldsets.
	 *
	 * @param 	string 	$set 	The fielset name.
	 * @param 	object 	$data 	The object data to bind:
	 * 							Property name = Field name;
	 * 							Property value = Field value.
	 *
	 * @return 	string 	The HTML fieldset(s) layout.
	 *
	 * @uses 	getFieldset()
	 * @uses 	renderField()
	 */
	public function renderFieldset($set = null, $data = null)
	{
		// get the fieldsets
		$fieldsets = $this->getFieldset($set);

		$html = '';

		// iterate the fieldsets
		foreach ($fieldsets as $fieldset)
		{
			$setname = (string) $fieldset->attributes()->name;
			$setname = 'COM_MENUS_' . strtoupper($setname) . '_FIELDSET_LABEL';

			/**
			 * Do not use a fieldset name in case the title should not be
			 * displayed or in case the translation is missing.
			 *
			 * @since 10.1.29
			 */
			if ($fieldset->attributes()->hidden || JText::_($setname) == $setname)
			{
				$setname = '';
			}

			// render fieldset opening
			$html .= JHtml::_('layoutfile', 'html.form.fieldset.open')->render(array('name' => $setname));

			// iterate the fieldset children
			foreach ($fieldset->field as $field)
			{
				$attrs  = $field->attributes();
				$name 	= (string) $attrs->name;

				$args = array();
				$args['id']	         = (string) $attrs->id;
				$args['label'] 		 = (string) $attrs->label;
				$args['description'] = (string) $attrs->description;
				$args['required'] 	 = ((string) $attrs->required) === 'true';

				/**
				 * Open control only in case the input shouldn't be hidden.
				 *
				 * @since 10.1.21
				 */
				if ($attrs->type != 'hidden' && $attrs->type != 'spacer' && empty($attrs->hidden))
				{
					// open control
					$html .= JHtml::_('layoutfile', 'html.form.control.open')->render($args);
				}

				// try to check if the value should be bound
				$val = isset($data->{$name}) ? $data->{$name} : null;

				// render field
				$html .= $this->renderField($field, array('value' => $val));

				/**
				 * Close control only in case the input shouldn't be hidden.
				 *
				 * @since 10.1.21
				 */
				if ($attrs->type != 'hidden' && $attrs->type != 'spacer' && empty($attrs->hidden))
				{
					// close control
					$html .= JHtml::_('layoutfile', 'html.form.control.close')->render();
				}
			}
			
			// render fieldset closing
			$html .= JHtml::_('layoutfile', 'html.form.fieldset.close')->render();
		}

		return $html;
	}

	/**
	 * Renders the specified field.
	 *
	 * @param 	mixed 	$field 	The field (or its name) to render.
	 * @param 	mixed 	$data 	The value (or a list of data) to bind.
	 *
	 * @return 	string 	The rendered field.
	 *
	 * @uses 	getField()
	 */
	public function renderField($field, $data = null)
	{
		// get field XML element if the name was provided
		if (is_string($field))
		{
			$field = $this->getField($field);
		}

		// get form field
		$field = JFormField::getInstance($field);

		/**
		 * Assign field to this form.
		 *
		 * @since 10.1.31
		 */
		$field->setForm($this);

		// bind data if set
		if ($data)
		{
			// if scalar value, setup value array 
			if (is_scalar($data))
			{
				$data = array('value' => $data);
			}

			// iterate the data to bind
			foreach ($data as $k => $v)
			{
				// bind attribute only if NOT NULL
				if (!is_null($v))
				{
					$field->bind($v, $k);
				}
			}
		}

		/**
		 * When specified, instruct the field that the layout
		 * should be drawn from the given client (plugin name).
		 *
		 * @since 10.1.31
		 */
		if (isset($this->options['client']))
		{
			$field->modowner = $this->options['client'];
		}

		// get the field class and do the rendering
		return $field->render();
	}

	/**
	 * Method used to bind data to the form.
	 *
	 * @param   mixed    $data  An array or object of data to bind to the form.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   10.1.27
	 */
	public function bind($data)
	{
		// Make sure there is a valid JForm XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			return false;
		}

		// The data must be an object or array.
		if (!is_object($data) && !is_array($data))
		{
			return false;
		}

		// iterate field by field
		foreach ($data as $name => $value)
		{
			/**
			 * Update only in case the value is NOT NULL.
			 *
			 * @since 10.1.29
			 */
			if (!is_null($value))
			{
				// find field by name
				$field = $this->getField($name);

				if ($field)
				{
					// field found, update XML element by injecting
					// the specified value
					$field['value'] = $value;
				}
			}
		}

		return true;
	}

	/**
	 * Method to get the form control. This string serves as a container for all form fields. For
	 * example, if there is a field named 'foo' and a field named 'bar' and the form control is
	 * empty the fields will be rendered like: `<input name="foo" />` and `<input name="bar" />`.  If
	 * the form control is set to 'jform' however, the fields would be rendered like:
	 * `<input name="jform[foo]" />` and `<input name="jform[bar]" />`.
	 *
	 * @return  string  The form control string.
	 *
	 * @since   10.1.31
	 */
	public function getFormControl()
	{
		return (string) isset($this->options['control']) ? $this->options['control'] : '';
	}

	/**
	 * Method to set the form control.
	 *
	 * @param 	string 	$control  The form control.
	 *
	 * @return  void
	 *
	 * @since   10.1.31
	 */
	public function setFormControl($control)
	{
		$this->options['control'] = $control;
	}
}
