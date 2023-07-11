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
 * VikRentCar backup controller.
 *
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarControllerBackup extends JControllerAdmin
{
	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	boolean
	 */
	public function save()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();

		$ajax = $input->getBool('ajax');

		if (!JSession::checkToken())
		{
			if ($ajax)
			{
				// missing CSRF-proof token
				VRCHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
			}
			else
			{
				// back to main list, missing CSRF-proof token
				$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
				$this->cancel();

				return false;
			}
		}
		
		// fetch requested action
		$args = [];
		$args['action'] = $input->get('backup_action');

		if ($args['action'] === 'create')
		{
			// get requested backup type
			$args['type'] = $input->get('type');
		}
		elseif ($args['action'] === 'upload')
		{
			/**
			 * Take uploaded file.
			 * Use "raw" filter because Joomla seems to block the attachments
			 * containing PHP files.
			 */
			$args['file'] = $input->files->get('file', null, 'raw');
		}
		else
		{
			VRCHttpDocument::getInstance($app)->close(400, 'Missing request values. Request entity may be too large.');
		}

		// check user permissions
		if (!$user->authorise('core.create', 'com_vikrentcar') || !$user->authorise('core.admin', 'com_vikrentcar'))
		{
			if ($ajax)
			{
				// not allowed
				VRCHttpDocument::getInstance($app)->close(403, JText::_('JERROR_ALERTNOAUTHOR'));
			}
			else
			{
				// back to main list, not authorised to create/edit records
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				$this->cancel();

				return false;
			}
		}

		// get backup model
		$backup = new VRCModelBackup();

		// try to save arguments
		$id = $backup->save($args);

		if ($id === false)
		{
			// get string error
			$error = $backup->getError(null, true);

			if ($ajax)
			{
				VRCHttpDocument::getInstance($app)->close(500, $error);
			}
			else
			{
				// display error message
				$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

				// redirect to list page
				$this->cancel();
					
				return false;
			}
		}

		if ($ajax)
		{
			// send the details of the created backup
			VRCHttpDocument::getInstance($app)->json($backup->getItem($id));
		}
		else
		{
			// display generic successful message
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

			// redirect to list page
			$this->cancel();

			return true;
		}
	}

	/**
	 * Deletes a list of records set in the request.
	 *
	 * @return 	boolean
	 */
	public function delete()
	{
		$app  = JFactory::getApplication();
		$cid  = $app->input->get('cid', array(), 'string');

		/**
		 * Added token validation.
		 * Both GET and POST are supported.
		 */
		if (!JSession::checkToken() && !JSession::checkToken('get'))
		{
			// back to main list, missing CSRF-proof token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$this->cancel();

			return false;
		}

		// check user permissions
		if (!JFactory::getUser()->authorise('core.delete', 'com_vikrentcar') || !JFactory::getUser()->authorise('core.admin', 'com_vikrentcar'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// delete selected records
		$res = (new VRCModelBackup)->delete($cid);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Restores the specified backup.
	 *
	 * @return 	boolean
	 */
	public function restore()
	{
		$app  = JFactory::getApplication();
		$cid  = $app->input->get('cid', array(), 'string');

		// take only the first backup
		$cid = array_shift($cid);

		/**
		 * Added token validation.
		 * Both GET and POST are supported.
		 */
		if (!JSession::checkToken() && !JSession::checkToken('get'))
		{
			// back to main list, missing CSRF-proof token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$this->cancel();

			return false;
		}

		// check user permissions
		if (!JFactory::getUser()->authorise('core.admin', 'com_vikrentcar'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$model = new VRCModelBackup();

		// restore backup
		$res = $model->restore($cid);

		if (!$res)
		{
			// get last error
			$error = $model->getError(null, true);

			if ($error)
			{
				$app->enqueueMessage($error, 'error');
			}
		}
		else
		{
			$app->enqueueMessage(JText::_('VRCBACKUPRESTORED'));
		}

		// back to main list
		$this->cancel();

		return $res;
	}

	/**
	 * End-point used to download a backuo archive.
	 * 
	 * @return 	boolean
	 */
	public function download()
	{
		$app  = JFactory::getApplication();
		$cid  = $app->input->get('cid', array(), 'string');

		// take only the first backup
		$cid = array_shift($cid);

		/**
		 * Added token validation.
		 * Both GET and POST are supported.
		 */
		if (!JSession::checkToken() && !JSession::checkToken('get'))
		{
			// back to main list, missing CSRF-proof token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$this->cancel();

			return false;
		}

		// check user permissions
		if (!JFactory::getUser()->authorise('core.admin', 'com_vikrentcar'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// fetch backup details
		$item = (new VRCModelBackup)->getItem($cid);

		if (!$item)
		{
			// backup not found
			$app->enqueueMessage(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'error');
			$this->cancel();

			return false;
		}

		// execute archive download
		VRCArchiveFactory::download($item->path);

		$app->close();
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrentcar&view=backups');
	}
}
