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

class VikRentCarViewManagecarat extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$dbo = JFactory::getDbo();
		$row = array();
		$allcars = array();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikrentcar_caratteristiche` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				VikError::raiseWarning('', 'Not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
				exit;
			}
			$row = $dbo->loadAssoc();
		}

		// read all cars
		$q = "SELECT `id`, `name`, `idcarat` FROM `#__vikrentcar_cars`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$records = $dbo->loadAssocList();
			foreach ($records as $r) {
				$r['idcarat'] = empty($r['idcarat']) ? array() : explode(';', rtrim($r['idcarat'], ';'));
				$allcars[$r['id']] = $r;
			}
		}
		
		$this->row = &$row;
		$this->allcars = &$allcars;
		
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
			JToolBarHelper::title(JText::_('VRMAINCARATTITLEEDIT'), 'vikrentcar');
			JToolBarHelper::save( 'updatecarat', JText::_('VRSAVE'));
			JToolBarHelper::spacer();
			JToolBarHelper::cancel( 'cancelcarat', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VRMAINCARATTITLENEW'), 'vikrentcar');
			JToolBarHelper::save( 'createcarat', JText::_('VRSAVE'));
			JToolBarHelper::spacer();
			JToolBarHelper::cancel( 'cancelcarat', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
