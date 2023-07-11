<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

 // No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class is used to handle the extra query field for the extension updates.
 *
 * USAGE:
 *
 * $update = new UriUpdateHandler(); // or new UriUpdateHandler('com_example')
 *
 * $update->addExtraField('order_number', $order_number);
 * $update->addExtraField('domain', $domain);
 *
 * // OR
 *
 * $update->setExtraFields(array(
 * 		'order_number' 	=> $order_number,
 * 		'domain' 		=> $domain,
 * ));
 *
 * $update->register();
 *
 * @since 1.0
 */
class UriUpdateHandler
{
	/**
	 * The component (or plugin/module) instance that need to be updated.
	 *
	 * @var mixed
	 */
	private $component = null;

	/**
	 * The query string containing the extra fields to append to the update URI.
	 *
	 * @var string
	 */
	private $extraFields = '';

	/**
	 * Class constructor.
	 * @param 	mixed 	$element 	The element to load. Null to load the current component.
	 *
	 * @uses 	getComponent() 	Load the specified/current component.
	 */
	public function __construct($element = null)
	{
		$this->getComponent($element);
	}

	/**
	 * Load the specified/current component.
	 *
	 * @param 	mixed 	$element 	The element to load. Null to load the current component.
	 *
	 * @return 	mixed 	The loaded component.
	 */
	public function getComponent($element = null)
	{
		if ($element === null) {
			$element = JFactory::getApplication()->input->get('option');
		}

		JLoader::import('joomla.application.component.helper');
		$this->component = JComponentHelper::getComponent($element);

		return $this->component;
	}

	/**
	 * Set the parameters into the additional query string.
	 *
	 * @param 	array 	$params 	The associative array to push.
	 *
	 * @return 	UriUpdateHandler 	This object to support chaining.
	 *
	 * @uses 	addExtraField() 	Append a single parameter to the query string.
	 */
	public function setExtraFields(array $params = array())
	{
		$this->extraFields = '';

		foreach ($params as $key => $val ) {
			$this->addExtraField($key, $val);
		}

		return $this;
	}

	/**
	 * Push a single value into the additional query string.
	 *
	 * @param 	string 	$key 	The name of the query param.
	 * @param 	string 	$val 	The value of the query param.
	 *
	 * @return 	UriUpdateHandler 	This object to support chaining.
	 */
	public function addExtraField($key, $val)
	{
		if (is_scalar($val)) {
			$this->extraFields .= (empty($this->extraFields) ? '' : '&amp;') . $key."=".urlencode($val);
		}

		return $this;
	}

	/**
	 * Commit the changes by updating the extra_fields column of the 
	 * #__update_sites_extensions database table.
	 *
	 * @return 	boolean 	True on success, otherwise false.
	 */
	public function register()
	{
		if (!$this->component) {
			return false;
		}

		$dbo = JFactory::getDbo();

		// load the update site record, if it exists
		$q = $dbo->getQuery(true);

		$q->select('update_site_id')
			->from('#__update_sites_extensions')
			->where('extension_id = '.$dbo->q($this->component->id));

		$dbo->setQuery($q);
		$dbo->execute();

		$updateSite = $dbo->loadResult();

		$success = false;

		if ($updateSite) {
			// update the update site record
			$q = $dbo->getQuery(true);

			$q->update($dbo->qn('#__update_sites'))
				->set($dbo->qn('extra_query') . ' = ' . $dbo->q($this->extraFields))
				->set($dbo->qn('enabled') . ' = 1')
				->set($dbo->qn('last_check_timestamp') . ' = 0')
				->where($dbo->qn('update_site_id') .' = '.$dbo->q($updateSite));

			$dbo->setQuery($q);
			$dbo->execute();

			$success = (bool) $dbo->getAffectedRows();

			// Delete any existing updates (essentially flushes the updates cache for this update site)
			$q = $dbo->getQuery(true);

			$q->delete('#__updates')
				->where('update_site_id = '.$dbo->q($updateSite));
			
			$dbo->setQuery($q);
			$dbo->execute();
		}

		return $success;
	}

	/**
	 * Check the schema of the extension to make sure the system will use
	 * the current version.
	 *
	 * @param 	string 	$version 	The current version of the component.
	 *
	 * @return 	UriUpdateHandler 	This object to support chaining.
	 */
	public function checkSchema($version)
	{
		if (!$this->component) {
			return false;
		}

		$ok = false;

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select('version_id')
			->from('#__schemas')
			->where('extension_id = ' . $this->component->id);
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows()) {

			if ($dbo->loadResult() == $version) {
				$ok = true;
			} else {
				$q->clear()
					->delete('#__schemas')
					->where('extension_id = ' . $this->component->id);

				$dbo->setQuery($q);
				$dbo->execute();
			}

		}

		if (!$ok) {
			$q->clear()
				->insert('#__schemas')
				->columns(array('extension_id', 'version_id'))
				->values($this->component->id . ', ' . $dbo->q($version));

			$dbo->setQuery($q);
			$ok = (bool) $dbo->execute();
		}

		return $ok;
	}

} 
