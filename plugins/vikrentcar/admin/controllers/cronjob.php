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
 * VikRentCar cron jobs controller.
 *
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarControllerCronjob extends JControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// check user permissions
		if (!$user->authorise('core.create', 'com_vikrentcar') || !$user->authorise('core.vrc.management', 'com_vikrentcar'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrentcar&view=managecron');

		return true;
	}

	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return 	boolean
	 */
	public function edit()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// check user permissions
		if (!$user->authorise('core.edit', 'com_vikrentcar') || !$user->authorise('core.vrc.management', 'com_vikrentcar'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrentcar&view=managecron&cid[]=' . $cid[0]);

		return true;
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the main list.
	 *
	 * @return 	void
	 */
	public function saveclose()
	{
		if ($this->save())
		{
			$this->cancel();
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	boolean
	 */
	public function save()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!JSession::checkToken())
		{
			// back to main list, missing CSRF-proof token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$this->cancel();

			return false;
		}

		$data = [];
		$data['id']         = $app->input->get('id', 0, 'uint');
		$data['cron_name']  = $app->input->get('cron_name', '', 'string');
		$data['class_file'] = $app->input->get('class_file', '', 'string');
		$data['published']  = $app->input->get('published', 0, 'uint');
		$data['params']     = $app->input->get('vikcronparams', [], 'array');

		$rule = 'core.' . ($data['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrentcar') || !$user->authorise('core.vrc.management', 'com_vikrentcar'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$model = VRCMvcModel::getInstance('cronjob');

		// try to save data
		$id = $model->save($data);

		if (!$id)
		{
			$error = $model->getError(null, true) ?: 'Unknown error.';

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrentcar&view=managecron';

			if ($data['id'])
			{
				$url .= '&cid[]=' . $data['id'];
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrentcar&task=cronjob.edit&cid[]=' . $id);

		return true;
	}

	/**
	 * Deletes a list of records set in the request.
	 *
	 * @return 	boolean
	 */
	public function delete()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!JSession::checkToken() && !JSession::checkToken('get'))
		{
			// back to main list, missing CSRF-proof token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->get('cid', array(), 'uint');

		// check user permissions
		if (!$user->authorise('core.delete', 'com_vikrentcar') || !$user->authorise('core.vrc.management', 'com_vikrentcar'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// delete selected records
		VRCMvcModel::getInstance('cronjob')->delete($cid);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrentcar&view=crons');
	}
}
