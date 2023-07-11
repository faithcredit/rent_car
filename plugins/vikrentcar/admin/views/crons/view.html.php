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

class VikRentCarViewCrons extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$lim = $mainframe->getUserStateFromRequest("com_vikrentcar.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$session = JFactory::getSession();
		$pvrcorderby = VikRequest::getString('vrcorderby', '', 'request');
		$pvrcordersort = VikRequest::getString('vrcordersort', '', 'request');
		$validorderby = array('id', 'cron_name', 'last_exec');
		$orderby = $session->get('vrcViewCronsOrderby', 'id');
		$ordersort = $session->get('vrcViewCronsOrdersort', 'DESC');
		if (!empty($pvrcorderby) && in_array($pvrcorderby, $validorderby)) {
			$orderby = $pvrcorderby;
			$session->set('vrcViewCronsOrderby', $orderby);
			if (!empty($pvrcordersort) && in_array($pvrcordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvrcordersort;
				$session->set('vrcViewCronsOrdersort', $ordersort);
			}
		}
		$rows = "";
		$navbut = "";
		$q = "SELECT SQL_CALC_FOUND_ROWS `c`.* FROM `#__vikrentcar_cronjobs` AS `c` ORDER BY `c`.`".$orderby."` ".$ordersort;
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut="<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}
		
		$this->rows = &$rows;
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
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('VRCMAINCRONSTITLE'), 'vikrentcar');

		if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
			JToolBarHelper::addNew('cronjob.add', JText::_('VRCMAINCRONNEW'));
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
			JToolBarHelper::editList('cronjob.edit', JText::_('VRCMAINCRONEDIT'));
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentcar')) {
			JToolBarHelper::deleteList(JText::_('VRCDELCONFIRM'), 'cronjob.delete', JText::_('VRCMAINCRONDEL'));
		}
	}
}
