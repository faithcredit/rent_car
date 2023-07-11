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

class VikRentCarViewCars extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$mainframe = JFactory::getApplication();
		$pmodtar = VikRequest::getString('tarmod', '', 'request');
		//vikrentcar 1.5
		$pmodtarhours = VikRequest::getString('tarmodhours', '', 'request');
		//
		//vikrentcar 1.6
		$pmodtarhourscharges = VikRequest::getString('tarmodhourscharges', '', 'request');
		//
		$pcarid = VikRequest::getString('carid', '', 'request');
		$dbo = JFactory::getDbo();
		if (!empty($pmodtar) && !empty($pcarid)) {
			$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `idcar`=".$dbo->quote($pcarid).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				foreach ($tars as $tt) {
					$tmpcost = VikRequest::getString('cost'.$tt['id'], '', 'request');
					$tmpattr = VikRequest::getString('attr'.$tt['id'], '', 'request');
					if (strlen($tmpcost)) {
						$q = "UPDATE `#__vikrentcar_dispcost` SET `cost`='".$tmpcost."'".(strlen($tmpattr) ? ", `attrdata`=".$dbo->quote($tmpattr)."" : "")." WHERE `id`=".(int)$tt['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=tariffs&cid[]=".$pcarid);
			exit;
		} elseif (!empty($pmodtarhours) && !empty($pcarid)) {
			//vikrentcar 1.5 fares for hours
			$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `idcar`=".$dbo->quote($pcarid).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				foreach ($tars as $tt) {
					$tmpcost = VikRequest::getString('cost'.$tt['id'], '', 'request');
					$tmpattr = VikRequest::getString('attr'.$tt['id'], '', 'request');
					if (strlen($tmpcost)) {
						$q = "UPDATE `#__vikrentcar_dispcosthours` SET `cost`='".$tmpcost."'".(strlen($tmpattr) ? ", `attrdata`=".$dbo->quote($tmpattr)."" : "")." WHERE `id`=".(int)$tt['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=tariffshours&cid[]=".$pcarid);
			exit;
			//
		} elseif (!empty($pmodtarhourscharges) && !empty($pcarid)) {
			//vikrentcar 1.6 extra hours charges
			$q = "SELECT * FROM `#__vikrentcar_hourscharges` WHERE `idcar`=".$dbo->quote($pcarid).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				foreach ($tars as $tt) {
					$tmpcost = VikRequest::getString('cost'.$tt['id'], '', 'request');
					if (strlen($tmpcost)) {
						$q = "UPDATE `#__vikrentcar_hourscharges` SET `cost`='".$tmpcost."' WHERE `id`=".(int)$tt['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=hourscharges&cid[]=".$pcarid);
			exit;
			//
		}

		$rows = [];
		$navbut = "";
		$dbo = JFactory::getDbo();
		$lim = $mainframe->getUserStateFromRequest("com_vikrentcar.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$session = JFactory::getSession();
		$pvrcorderby = VikRequest::getString('vrcorderby', '', 'request');
		$pvrcordersort = VikRequest::getString('vrcordersort', '', 'request');
		$validorderby = array('id', 'name', 'units');
		$orderby = $session->get('vrcViewCarsOrderby', 'id');
		$ordersort = $session->get('vrcViewCarsOrdersort', 'DESC');
		if (!empty($pvrcorderby) && in_array($pvrcorderby, $validorderby)) {
			$orderby = $pvrcorderby;
			$session->set('vrcViewCarsOrderby', $orderby);
			if (!empty($pvrcordersort) && in_array($pvrcordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvrcordersort;
				$session->set('vrcViewCarsOrdersort', $ordersort);
			}
		}

		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`".$orderby."` ".$ordersort;
		$dbo->setQuery($q, $lim0, $lim);
		$rows = $dbo->loadAssocList();
		if ($rows) {
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}

		$this->rows = $rows;
		$this->lim0 = $lim0;
		$this->navbut = $navbut;
		$this->orderby = $orderby;
		$this->ordersort = $ordersort;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINDEAFULTTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
			JToolBarHelper::addNew('newcar', JText::_('VRMAINDEFAULTNEW'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
			JToolBarHelper::editList('editcar', JText::_('VRMAINDEFAULTEDITC'));
			JToolBarHelper::spacer();
			JToolBarHelper::editList('tariffs', JText::_('VRMAINDEFAULTEDITT'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::custom( 'calendar', 'edit', 'edit', JText::_('VRMAINDEFAULTCAL'), true, false);
		JToolBarHelper::spacer();
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentcar')) {
			JToolBarHelper::deleteList(JText::_('VRJSDELCAR') . '?', 'removecar', JText::_('VRMAINDEFAULTDEL'));
			JToolBarHelper::spacer();
		}
	}

}
