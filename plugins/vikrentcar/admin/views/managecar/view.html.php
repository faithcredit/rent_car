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

class VikRentCarViewManagecar extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$row = array();
		$cats = $carats = $optionals = $places = '';

		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikrentcar_cars` WHERE `id`=" . (int)$id . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				VikError::raiseWarning('', 'Not found.');
				$app->redirect("index.php?option=com_vikrentcar&task=optionals");
				exit;
			}
			$row = $dbo->loadAssoc();
		}

		$q = "SELECT * FROM `#__vikrentcar_places`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$places = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : '';
		$q = "SELECT * FROM `#__vikrentcar_categories`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$cats = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : '';
		$q = "SELECT * FROM `#__vikrentcar_caratteristiche` ORDER BY `#__vikrentcar_caratteristiche`.`ordering` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$carats = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : '';
		$q = "SELECT * FROM `#__vikrentcar_optionals`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$optionals = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : '';
		
		$importCalendars = [];
		if (count($row)) {
			$q = "SELECT * FROM `#__vikrentcar_cars_icals` WHERE `idcar`=" . (int)$row['id'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$importCalendars = $dbo->loadAssocList();
			}
		}
		
		$this->row = $row;
		$this->cats = $cats;
		$this->carats = $carats;
		$this->optionals = $optionals;
		$this->places = $places;
		$this->importCalendars = $importCalendars;
		
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
			JToolBarHelper::title(JText::_('VRMAINCARTITLEEDIT'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
				JToolBarHelper::apply( 'updatecarapply', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::save( 'updatecar', JText::_('VRSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
				JToolBarHelper::save('clone_car', JText::_('VRC_SAVE_COPY'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancel', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VRMAINCARTITLENEW'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
				JToolBarHelper::save( 'createcar', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancel', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
