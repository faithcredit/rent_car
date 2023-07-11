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

class VikRentCarViewConfig extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cookie = JFactory::getApplication()->input->cookie;
		$curtabid = $cookie->get('vrcConfPt', '', 'string');
		$curtabid = empty($curtabid) ? 1 : (int)$curtabid;

		/**
		 * Pre-select one specific tab via query string.
		 * 
		 * @since 	1.15.0 (J) - 1.3.0 (WP)
		 */
		$tab = VikRequest::getInt('tab', 0, 'request');
		if (!empty($tab)) {
			$curtabid = $tab;
		}

		$this->curtabid = $curtabid;

		/**
		 * Fetch all the supported backup export types.
		 * 
		 * @since 	1.15.0 (J) - 1.3.0 (WP)
		 */
		$this->backupExportTypes = VRCBackupManager::getExportTypes();
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINCONFIGTITLE'), 'vikrentcarconfig');
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
			JToolBarHelper::apply( 'saveconfig', JText::_('VRSAVE'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::cancel( 'cancel', JText::_('VRANNULLA'));
		JToolBarHelper::spacer();
	}

}
