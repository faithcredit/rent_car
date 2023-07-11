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
 * Utility class working with a physical configuration stored into the Joomla database.
 *
 * @since  1.3
 */
class VRCConfigRegistryDatabase extends VRCConfigRegistry
{
	/**
	 * Class constructor.
	 *
	 * @param  array  $options  An array of options.
	 */
	public function __construct(array $options = [])
	{
		if (!isset($options['table']))
		{
			// use default plugin database table
			$options['table'] = '#__vikrentcar_config';
		}

		if (!isset($options['key']))
		{
			// use default plugin "param" column
			$options['key'] = 'param';
		}

		if (!isset($options['value']))
		{
			// use default plugin "setting" column
			$options['value'] = 'setting';
		}

		if (!isset($options['db']))
		{
			// use default CMS database driver
			$options['db'] = JFactory::getDbo();
		}

		// delegate construction to parent
		parent::__construct($options);
	}

	/**
	 * @override
	 * Retrieves the value of the setting stored in the Joomla database.
	 *
	 * @param   string 	$key  The name of the setting.
	 *
	 * @return  mixed   The value of the setting if exists, otherwise false.
	 */
	protected function retrieve($key)
	{
		$db = $this->options['db'];

		$q = $db->getQuery(true);

		$q->select($db->qn($this->options['value']))
			->from($db->qn($this->options['table']))
			->where($db->qn($this->options['key']) . ' = ' . $db->q($key));

		$db->setQuery($q, 0, 1);
		$db->execute();

		if ($db->getNumRows())
		{
			return $db->loadResult();
		}

		return false;
	}

	/**
	 * @override
	 * Registers the value of the setting into the Joomla database.
	 * All the array and objects will be stringified in JSON.
	 *
	 * @param   string   $key  The name of the setting.
	 * @param   mixed    $val  The value of the setting.
	 *
	 * @return  boolean  True in case of success, otherwise false.
	 */
	protected function register($key, $val)
	{
		$db = $this->options['db'];

		if (is_array($val) || is_object($val))
		{
			// stringify array/object
			$val = json_encode($val);
		}

		// prepare object to save
		$data = new stdClass;
		$data->{$this->options['key']} = $key;
		$data->{$this->options['value']} = $val;

		// check whether the setting already exists
		if ($this->has($key))
		{
			// update existing record
			$result = $db->updateObject($this->options['table'], $data, 'param');
		}
		else
		{
			// insert new record
			$data->id = 0;
			$result = $db->insertObject($this->options['table'], $data, 'id');
		}

		return $result;
	}
}
