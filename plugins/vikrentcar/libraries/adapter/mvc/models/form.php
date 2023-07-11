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

JLoader::import('adapter.mvc.model');
JLoader::import('adapter.form.form');

/**
 * The form model class used by the MVC framework.
 * A form model can be used by a controller or a view to handle 
 * a FORM entity of the plugin.
 *
 * The model can be invoked when the value contained 
 * in $_REQUEST['task'] is equals to 'ComponentModel' + $_REQUEST['task'].
 *
 * e.g. $_REQUEST['task'] = 'groups.save' -> ComponentModelGroups
 *
 * @since 10.0
 */
abstract class JModelForm extends JModel
{
	/**
	 * Creates or updates the specified record.
	 *
	 * @param 	mixed  $data  The record to insert.
	 *
	 * @return 	mixed  The ID of the inserted record on success, 
	 */
	public function save(&$data)
	{
		/**
		 * Rely on JTable instance to complete the saving process.
		 *
		 * @since 10.1.35
		 */
		$table = $this->getTable();

		// attempt to save data
		if ($table->save($data))
		{
			// saved successfully, return PK
			return $table->{$table->getKeyName()};
		}

		// something went wrong, try to obtain an error
		$error = $table->getError($get_last = null, $string = true);

		if ($error)
		{
			// error found, register it within the model
			$this->setError($error);
		}

		return false;
	}

	/**
	 * Deletes the specified records.
	 *
	 * @param 	mixed 	 $ids 	The PK value (or a list of values) of the record(s) to remove.
	 *
	 * @return 	boolean  True if at least a record has been removed, otherwise false.
	 */
	public function delete($ids)
	{
		/**
		 * Rely on JTable instance to complete the deleting process.
		 *
		 * @since 10.1.35
		 */
		$table = $this->getTable();

		$return = false;

		foreach ((array) $ids as $id)
		{
			if ($table->delete($id))
			{
				$return = true;
			}
			else
			{
				// something went wrong, try to obtain an error
				$error = $table->getError($get_last = null, $string = true);

				if ($error)
				{
					// error found, register it within the model
					$this->setError($error);
				}
			}
		}

		return $return;
	}

	/**
	 * Basic item loading implementation.
	 *
	 * @param   mixed    $pk   An optional primary key value to load the row by, or an array of fields to match.
	 *                         If not set the instance property value is used.
	 * @param   boolean  $new  True to return an empty object if missing.
	 *
	 * @return 	mixed    The record object on success, null otherwise.
	 *
	 * @since   10.1.35  Added support for $new argument.
	 */
	public function getItem($pk, $new = false)
	{
		/**
		 * Rely on JTable instance to complete the fetching process.
		 *
		 * @since 10.1.35
		 */
		$table = $this->getTable();
		
		// reset table to make sure we obtain valid values
		$table->reset();

		// attempt to load record
		$loaded = ($pk && $table->load($pk));
		
		if ($loaded || $new === true)
		{
			// loaded successfully or requested an empty object
			return (object) $table->getProperties();
		}

		// something went wrong, try to obtain an error
		$error = $table->getError($get_last = null, $string = true);

		if ($error)
		{
			// error found, register it within the model
			$this->setError($error);
		}

		return null;
	}

	/**
	 * Obtains the JForm object related to the model view.
	 *
	 * @return 	JForm 	The form object.
	 */
	public function getForm()
	{
		$comp   = $this->getComponentName();
		$name   = $this->getModelName();
		$client = $this->getClientFolder();

		$id   = serialize(array($comp, $name, $client));
		$path = implode(DIRECTORY_SEPARATOR, array(WP_PLUGIN_DIR, $comp, $client, 'views', $name, 'tmpl', 'default.xml'));

		try
		{
			$form = JForm::getInstance($id, $path);
		}
		catch (Exception $e)
		{
			$form = null;
		}

		return $form;
	}

	/**
	 * This method should be used to pre-load an item considering
	 * the data set in the request.
	 * 
	 * For example, if the request owns an ID, this method may try 
	 * to retrieve the item from the database.
	 * Otherwise it may return an empty object.
	 *
	 * @return 	array|object  The object found.
	 */
	public function loadFormData()
	{
		return array();
	}

	/**
	 * This method should be used to retrieve the posted data
	 * after the form submission.
	 *
	 * @return 	array|object  The data object.
	 */
	public function getFormData()
	{
		return array();
	}
}
