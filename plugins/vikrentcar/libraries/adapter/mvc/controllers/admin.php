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

JLoader::import('adapter.mvc.controller');

/**
 * The controller used by the views on the MVC framework.
 * An admin-controller is used to handle the actions requested
 * by the related view.
 *
 * The controller can be invoked when the value contained 
 * in $_REQUEST['task'] is equals to 'MyPluginController' + $_REQUEST['task'].
 *
 * e.g. $_REQUEST['task'] = 'groups.save' -> MyPluginControllerGroups
 *
 * @since 10.0
 */
abstract class JControllerAdmin extends JController
{
	/**
	 * The controller model.
	 *
	 * @var JModel
	 */
	private $_model = null;

	/**
	 * Magic method to access private properties.
	 *
	 * @param 	string 	$name 	The property name to access.
	 *
	 * @return 	mixed 	The property value if exists, otherwise null.
	 */
	public function __get($name)
	{
		if ($name == 'model' && !isset($this->_model))
		{
			$this->_model = $this->getModel();
		}

		$name = '_' . $name;

		if (property_exists($this, $name))
		{
			return $this->{$name};
		}

		return null;
	}
}
