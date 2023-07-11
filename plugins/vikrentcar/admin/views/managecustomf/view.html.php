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

class VikRentCarViewManagecustomf extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$row = array();
		$dbo = JFactory::getDbo();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikrentcar_custfields` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				VikError::raiseWarning('', 'Not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
				exit;
			}
			$row = $dbo->loadAssoc();
		}
		
		$this->row = &$row;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		$cid = VikRequest::getVar('cid', array(0));
		
		if (!empty($cid[0])) {
			//edit
			JToolBarHelper::title(JText::_('VRMAINCUSTOMFTITLE'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
				JToolBarHelper::save( 'updatecustomf', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcustomf', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VRMAINCUSTOMFTITLE'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
				JToolBarHelper::save( 'createcustomf', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcustomf', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
