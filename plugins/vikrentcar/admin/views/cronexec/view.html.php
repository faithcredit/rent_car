<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikRentCarViewCronexec extends JViewVikRentCar
{
	function display($tpl = null)
	{
		// This view is usually called within a modal box, so it does not require the toolbar or page title

		$app = JFactory::getApplication();

		$id_cron = $app->input->getUint('cron_id', 0);
		$key     = $app->input->getString('cronkey', '');

		$this->cronModel = VRCMvcModel::getInstance('cronjob');

		$this->cron = $this->cronModel->getItem($id_cron);

		// Dispatch the cron job by injecting the cron key within the
		// configuration array, in order to make sure that the execution
		// of the job has been requested by a reliable caller.
		// Enable the debug mode to catch the output buffering and disable
		// the strict model to allow the execution of unpublished jobs.
		$this->response = $this->cronModel->dispatch($id_cron, [
			'key'    => $key,
			'debug'  => true,
			'strict' => false,
		]);

		if ($this->response === false)
		{
			// an error has occurred
			$error = $this->cronModel->getError();

			if (!$error instanceof Exception)
			{
				// wrap error message in an exception for a better ease of use
				$error = new Exception($error ?: 'Error', 500);
			}
			
			// terminate session with an error
			VRCHttpDocument::getInstance($app)->close($error->getCode(), $error->getMessage());
		}

		parent::display();
	}
}
