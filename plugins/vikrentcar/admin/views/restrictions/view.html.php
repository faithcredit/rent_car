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

class VikRentCarViewRestrictions extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		$lim = $mainframe->getUserStateFromRequest("com_vikrentcar.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$get_rooms = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
		$all_cars = array();
		if (count($get_rooms)) {
			foreach ($get_rooms as $rk => $rv) {
				$all_cars[$rv['id']] = $rv['name'];
			}
		}
		$pvrorderby = VikRequest::getString('vrorderby', '', 'request');
		$pvrordersort = VikRequest::getString('vrordersort', '', 'request');
		$validorderby = array('id', 'name', 'minlos', 'maxlos');
		$orderby = $session->get('vrViewRestrictionsOrderby', 'id');
		$ordersort = $session->get('vrViewRestrictionsOrdersort', 'DESC');
		if (!empty($pvrorderby) && in_array($pvrorderby, $validorderby)) {
			$orderby = $pvrorderby;
			$session->set('vrViewRestrictionsOrderby', $orderby);
			if (!empty($pvrordersort) && in_array($pvrordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvrordersort;
				$session->set('vrViewRestrictionsOrdersort', $ordersort);
			}
		}
		$rows = "";
		$navbut = "";
		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikrentcar_restrictions` ORDER BY `".$orderby."` ".$ordersort;
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}
		
		$this->rows = &$rows;
		$this->all_cars = &$all_cars;
		$this->lim0 = &$lim0;
		$this->navbut = &$navbut;
		$this->orderby = &$orderby;
		$this->ordersort = &$ordersort;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINRESTRICTIONSTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
			JToolBarHelper::addNew('newrestriction', JText::_('VRMAINRESTRICTIONNEW'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
			JToolBarHelper::editList('editrestriction', JText::_('VRMAINRESTRICTIONEDIT'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentcar')) {
			JToolBarHelper::deleteList(JText::_('VRCDELCONFIRM'), 'removerestrictions', JText::_('VRMAINRESTRICTIONDEL'));
			JToolBarHelper::spacer();
		}
	}

}
