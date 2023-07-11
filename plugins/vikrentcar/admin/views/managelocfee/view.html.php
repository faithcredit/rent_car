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

class VikRentCarViewManagelocfee extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$row = array();
		$wsel = '';
		$wseltwo = '';
		$dbo = JFactory::getDbo();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikrentcar_locfees` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				VikError::raiseWarning('', 'Not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikrentcar&task=locfees");
				exit;
			}
			$row = $dbo->loadAssoc();
		}

		$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$wsel .= "<select name=\"from\">\n<option value=\"\"></option>\n";
			$wseltwo .= "<select name=\"to\">\n<option value=\"\"></option>\n";
			$data = $dbo->loadAssocList();
			foreach ($data as $d) {
				$wsel .= "<option value=\"".$d['id']."\"".(count($row) && $d['id'] == $row['from'] ? " selected=\"selected\"" : "").">".$d['name']."</option>\n";
				$wseltwo .= "<option value=\"".$d['id']."\"".(count($row) && $d['id'] == $row['to'] ? " selected=\"selected\"" : "").">".$d['name']."</option>\n";
			}
			$wsel .= "</select>\n";
			$wseltwo .= "</select>\n";
		}
		
		$this->row = $row;
		$this->wsel = $wsel;
		$this->wseltwo = $wseltwo;
		
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
			JToolBarHelper::title(JText::_('VRMAINLOCFEETITLEEDIT'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
				JToolBarHelper::save( 'updatelocfee', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancellocfee', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VRMAINLOCFEETITLENEW'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
				JToolBarHelper::save( 'createlocfee', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancellocfee', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
