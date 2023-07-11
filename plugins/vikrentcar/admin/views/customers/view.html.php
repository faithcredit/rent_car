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

class VikRentCarViewCustomers extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$lim = $mainframe->getUserStateFromRequest("com_vikrentcar.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$session = JFactory::getSession();
		$pvrorderby = VikRequest::getString('vrorderby', '', 'request');
		$pvrordersort = VikRequest::getString('vrordersort', '', 'request');
		$validorderby = array('id','first_name', 'last_name', 'email', 'phone', 'country', 'pin', 'tot_bookings');
		$orderby = $session->get('vrViewCustomersOrderby', 'last_name');
		$ordersort = $session->get('vrViewCustomersOrdersort', 'ASC');
		if (!empty($pvrorderby) && in_array($pvrorderby, $validorderby)) {
			$orderby = $pvrorderby;
			$session->set('vrViewCustomersOrderby', $orderby);
			if (!empty($pvrordersort) && in_array($pvrordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvrordersort;
				$session->set('vrViewCustomersOrdersort', $ordersort);
			}
		}
		$rows = "";
		$navbut = '';
		$pfiltercustomer = VikRequest::getString('filtercustomer', '', 'request');
		$whereclause = '';
		if (!empty($pfiltercustomer)) {
			$whereclause = " WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$pfiltercustomer."%")." OR `email` LIKE ".$dbo->quote("%".$pfiltercustomer."%")." OR `pin` LIKE ".$dbo->quote("%".$pfiltercustomer."%")."";
		}
		//this query below is safe with the error #1055 when sql_mode=only_full_group_by because there is no GROUP BY clause
		$q = "SELECT SQL_CALC_FOUND_ROWS *,(SELECT COUNT(*) FROM `#__vikrentcar_customers_orders` WHERE `#__vikrentcar_customers_orders`.`idcustomer`=`#__vikrentcar_customers`.`id`) AS `tot_bookings`,(SELECT `country_name` FROM `#__vikrentcar_countries` WHERE `#__vikrentcar_countries`.`country_3_code`=`#__vikrentcar_customers`.`country`) AS `country_full_name` FROM `#__vikrentcar_customers`".$whereclause." ORDER BY `".$orderby."` ".$ordersort;
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
		JToolBarHelper::title(JText::_('VRMAINCUSTOMERSTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
			JToolBarHelper::addNew('newcustomer', JText::_('VRMAINCUSTOMERNEW'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
			JToolBarHelper::editList('editcustomer', JText::_('VRMAINCUSTOMEREDIT'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.vrc.management', 'com_vikrentcar')) {
			JToolBarHelper::custom( 'exportcustomers', 'file-2', 'file-2', JText::_('VRCCSVEXPCUSTOMERS'), false);
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentcar')) {
			JToolBarHelper::deleteList(JText::_('VRCDELCONFIRM'), 'removecustomers', JText::_('VRMAINCUSTOMERDEL'));
			JToolBarHelper::spacer();
		}
	}

}
