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

class VikRentCarViewManagecron extends JViewVikRentCar
{
	function display($tpl = null)
	{
		$id = VikRequest::getVar('cid', []);

		if ($id)
		{
			// fetch cron job data on update
			$this->row = (array) VRCMvcModel::getInstance('cronjob')->getItem((int) $id[0]);
		}
		else
		{
			$this->row = [];
		}

		// fetch all the supported cron job drivers
		$this->supportedDrivers = VRCFactory::getCronFactory()->getInstances();

		// Set the toolbar
		$this->addToolBar();
		
		// display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar()
	{	
		if ($this->row)
		{
			// edit
			JToolBarHelper::title(JText::_('VRCMAINCRONSTITLE'), 'vikrentcar');
		}
		else
		{
			// new
			JToolBarHelper::title(JText::_('VRCMAINCRONSTITLE'), 'vikrentcar');
		}

		$user = JFactory::getUser();

		if (($user->authorise('core.edit', 'com_vikrentcar') && !empty($this->row['id'])) || $user->authorise('core.create', 'com_vikrentcar'))
		{
			JToolBarHelper::apply('cronjob.save', JText::_('VRSAVE'));
			JToolBarHelper::save('cronjob.saveclose', JText::_('VRSAVECLOSE'));
		}

		JToolBarHelper::cancel('cronjob.cancel', JText::_('VRBACK'));
	}
}
