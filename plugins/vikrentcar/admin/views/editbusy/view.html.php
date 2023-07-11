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

class VikRentCarViewEditbusy extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		$ido = $cid[0];
		$dbo = JFactory::getDbo();
		$cpin = VikRentCar::getCPinIstance();
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".$dbo->quote($ido).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() != 1) {
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
		}
		$row = $dbo->loadAssoc();
		// check if it's a closure (stop_sales)
		$row['closure'] = 0;
		if (!empty($row['idbusy'])) {
			$q = "SELECT `stop_sales` FROM `#__vikrentcar_busy` WHERE `id`=".(int)$row['idbusy'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$row['closure'] = (int)$dbo->loadResult();
			}
		}
		//
		$q = "SELECT * FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$all_cars = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
		$car = array();
		foreach ($all_cars as $c) {
			if ($c['id'] == $row['idcar']) {
				$car = $c;
				break;
			}
		}
		$busy = array();
		if (!empty($row['idbusy'])) {
			$q = "SELECT * FROM `#__vikrentcar_busy` WHERE `id`=".(int)$row['idbusy'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$busy = $dbo->loadAssoc();
			}
		}
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$locations = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
		$cpin = VikRentCar::getCPinIstance();
		$customer = $cpin->getCustomerFromBooking($row['id']);
		if (count($customer) && !empty($customer['country'])) {
			if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$customer['country'].'.png')) {
				$customer['country_img'] = '<img src="'.VRC_ADMIN_URI.'resources/countries/'.$customer['country'].'.png'.'" title="'.$customer['country'].'" class="vrc-country-flag vrc-country-flag-left"/>';
			}
		}
		
		$this->row = &$row;
		$this->all_cars = &$all_cars;
		$this->car = &$car;
		$this->busy = &$busy;
		$this->locations = &$locations;
		$this->customer = &$customer;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINEBUSYTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
			JToolBarHelper::apply( 'updatebusy', JText::_('VRSAVE'));
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentcar')) {
			JToolBarHelper::custom( 'removebusy', 'delete', 'delete', JText::_('VRMAINEBUSYDEL'), false, false);
		}
		$pgoto = VikRequest::getString('goto', '', 'request');
		if ($pgoto == 'overv') {
			JToolBarHelper::custom( 'cancelbusy', 'back', 'back', JText::_('VRCVIEWBOOKINGDET'), false, false);
		}
		JToolBarHelper::cancel( ($pgoto == 'overv' ? 'canceloverv' : 'cancelbusy'), JText::_('VRBACK'));
	}

}
