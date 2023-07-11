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

class VikRentCarViewManageoohfee extends JViewVikRentCar {
	
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
			$q = "SELECT * FROM `#__vikrentcar_oohfees` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				VikError::raiseWarning('', 'Not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
				exit;
			}
			$row = $dbo->loadAssoc();
		}

		$nowlocations = array();
		$wselcars = "";
		$wselplaces = "";
		if (count($row)) {
			$q = "SELECT * FROM `#__vikrentcar_oohfees_locxref` WHERE `idooh`=".(int)$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$oohfee_locxref = $dbo->loadAssocList();
			foreach ($oohfee_locxref as $locxref) {
				$nowlocations[$locxref['idlocation']] = $locxref['idlocation'];
			}
		}
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cars = $dbo->loadAssocList();
			$nowcars = array();
			if (count($row) && !empty($row['idcars'])) {
				$oohcars = explode(',', $row['idcars']);
				foreach ($oohcars as $idcar) {
					$idcar = intval(str_replace("-", '', trim($idcar)));
					if (empty($idcar)) {
						continue;
					}
					$nowcars[$idcar] = $idcar;
				}
			}
			$wselcars = "<select id=\"idcars\" name=\"idcars[]\" multiple=\"multiple\" size=\"5\">\n";
			foreach ($cars as $c) {
				$wselcars .= "<option value=\"".$c['id']."\"".(in_array($c['id'], $nowcars) ? ' selected="selected"' : '').">".$c['name']."</option>\n";
			}
			$wselcars .= "</select>\n";
		}
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$locations = $dbo->loadAssocList();
			$wselplaces = "<select id=\"idplace\" name=\"idplace[]\" multiple=\"multiple\" size=\"5\">\n";
			foreach ($locations as $l) {
				$wselplaces .= "<option value=\"".$l['id']."\"".(in_array($l['id'], $nowlocations) ? ' selected="selected"' : '').">".$l['name']."</option>\n";
			}
			$wselplaces .= "</select>\n";
		}
		
		$this->row = &$row;
		$this->wselcars = &$wselcars;
		$this->wselplaces = &$wselplaces;
		
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
			JToolBarHelper::title(JText::_('VRMAINOOHFEESTITLE'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
				JToolBarHelper::save( 'updateoohfee', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'canceloohfee', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VRMAINOOHFEESTITLE'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
				JToolBarHelper::save( 'createoohfee', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'canceloohfee', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
