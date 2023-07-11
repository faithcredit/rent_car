<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      E4J srl
 * @copyright   Copyright (C) e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.models.form');

/**
 * VikRentCar plugin Shortcode model.
 *
 * @since 	1.0
 * @see 	JModelForm
 */
class VikRentCarModelShortcode extends JModelForm
{
	/**
	 * @override
	 * This method should be used to pre-load an item considering
	 * the data set in the request.
	 * 
	 * For example, if the request owns an ID, this method may try 
	 * to retrieve the item from the database.
	 * Otherwise it may return an empty object.
	 *
	 * @return 	object  The object found.
	 */
	public function loadFormData()
	{
		$input = JFactory::getApplication()->input;

		$id = $input->getUint('cid', array(0));

		return $this->getItem(array_shift($id));
	}

	/**
	 * This method should be used to retrieve the posted data
	 * after the form submission.
	 *
	 * @return 	object  The data object.
	 */
	public function getFormData()
	{
		$input = JFactory::getApplication()->input;

		// get data from request
		$data = new stdClass;

		$data->id 		 = $input->getInt('id', 0);
		$data->title 	 = '';
		$data->name  	 = $input->getString('name');
		$data->type 	 = $input->getString('type');
		$data->lang 	 = $input->getString('lang');
		$data->json  	 = array();
		$data->shortcode = '';

		// only if we are creating the shortcode, set the creation date and user
		if ($data->id <= 0)
		{
			$data->createdby = JFactory::getUser()->id;
			$data->createdon = JFactory::getDate()->toSql();
		}

		// get layout path
		$path = implode(DIRECTORY_SEPARATOR, array(VRC_SITE_PATH, 'views', $data->type, 'tmpl', 'default.xml'));

		// if the file doesn't exist, raise an exception
		if (!is_file($path))
		{
			throw new Exception("Missing XML [{$data->type}] view type.", 404);
		}

		// load XML form
		$form = JForm::getInstance($data->type, $path);

		// obtain view title
		$data->title = (string) $form->getXml()->layout->attributes()->title;

		// iterate the layout fields
		foreach ($form->getFields() as $field)
		{
			$attrs = $field->attributes();

			$name 	= (string) $attrs->name;
			$filter = (string) $attrs->filter;
			$req 	= (string) $attrs->required;

			// use string if no filter
			if (empty($filter))
			{
				$filter = 'string';
			}

			// get field from request
			$data->json[$name] = $input->get($name, '', $filter);

			// raise an exception if a mandatory field is empty
			if (empty($data->json[$name]) && $req == 'true')
			{
				throw new Exception("Missing required [$name] field.", 400);
			}
		}

		$viewData = array();
		$viewData['view'] = $data->type;
		$viewData['lang'] = $data->lang;

		// merge VIEW name and LANG TAG with JSON params 
		$args = array_merge($data->json, $viewData);
		// generate shortcode string
		$data->shortcode = JFilterOutput::shortcode('vikrentcar', $args);

		// finally encode the params in JSON
		$data->json = json_encode($data->json);

		return $data;
	}

	/**
	 * @override
	 * Retrieves the specified item.
	 *
	 * @param 	mixed 	 $pk 	  The primary key value or a list of keys.
	 * @param 	boolean  $create  True to create an empty object.
	 *
	 * @return 	object 	 The item found if exists, otherwise an empty object.
	 */
	public function getItem($pk, $create = true)
	{
		$item = parent::getItem($pk);

		if (!$item && $create)
		{
			$item = new stdClass;
			$item->id 	= 0;
			$item->name = '';
			$item->type = '';
			$item->lang = '*';
			$item->json = '{}';
		}

		return $item;
	}

	/**
	 * @override
	 * Creates or updates the specified record.
	 *
	 * @param 	object 	 &$data  The record to insert.
	 *
	 * @return 	boolean  True if the record has been inserted/updated, otherwise false.
	 */
	public function save(&$data)
	{
		// get old item to get previous shortcode
		$old = $this->getItem($data->id);

		// save shortcode
		$res = parent::save($data);

		if ($res && $old)
		{
			// get saved item to access post ID property
			$item = $this->getItem($data->id);

			// get the post object
			$post = get_post($item->post_id);

			/**
			 * Proceed only in case the post exists.
			 *
			 * @since 1.1.7
			 */
			if ($post)
			{
				/**
				 * Do not proceed in case the post already contains the shortcode.
				 * Otherwise we would fall in a loop as wp_update_post() triggers
				 * the action that invoked the current method.
				 *
				 * @see 	vikrentcar.php @ action:save_post
				 *
				 * @since 	1.0.17
				 */
				if (strpos($post->post_content, $item->shortcode) === false)
				{
					// replace old shortcode with the new one from the post contents
					$post->post_content = str_replace($old->shortcode, $item->shortcode, $post->post_content);

					// finalize the update
					wp_update_post($post);
				}
			}
		}

		return $res;
	}

	/**
	 * Obtains the JForm object related to the model view.
	 *
	 * @param 	object 	$item 	The data to bind.
	 *
	 * @return 	JForm 	The form object.
	 */
	public function getTypeForm($item)
	{
		$item = (object) $item;

		if (!$item->type)
		{
			return null;
		}

		// inject custom vars to access the layout
		// file of a site view (change model name and client)
		$this->_name   = $item->type;
		$this->_client = 'site';

		// get the form
		$form = parent::getForm();

		// reset the vars (it will be taken later if needed)
		$this->_name   = null;
		$this->_client = null;

		return $form;
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
	 * @since   1.2.2
	 */
	public function getTable($name = '', $prefix = 'JTable', $options = array())
	{
		if (!$name)
		{
			$name 	= 'shortcode';
			$prefix = 'VRCTable';
		}

		return parent::getTable($name, $prefix, $options);
	}
}
