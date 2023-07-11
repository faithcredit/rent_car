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

/**
 * Model used to handle user registration functionalities.
 *
 * This model can be accessed by using the code below:
 * JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_users/models');
 * $model = JModelLegacy::getInstance('registration', 'UsersModel');
 *
 * @since 10.1.24
 */
class UsersModelRegistration extends JModel
{
	/**
	 * Creates a new user.
	 *
	 * @param   array 	$data  The user data.
	 *
	 * @return  mixed 	The user id on success, false on failure.
	 */
	public function register(array $data)
	{
		// create new empty user
		$user = new JUser();

		// bind user data
		$user->bind($data);

		// save user
		$res = $user->save();

		if (!$res)
		{
			// retrieve error from user instance and set it here
			// for being used by the subject that called this model
			$this->setError($user->getError());

			return false;
		}

		// registration successful, return ID
		return $user->id;
	}
}
