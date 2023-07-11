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

class VikRentCarViewSeasons extends JViewVikRentCar {

	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$rows = "";
		$navbut = "";
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		//clean expired special prices
		$lastclean = $session->get('vrcShowSeasonsClean', '');
		if (empty($lastclean)) {
			$session->set('vrcShowSeasonsClean', date('Y-m-d'));
			$nowinfo = getdate();
			$baseone = mktime(0, 0, 0, 1, 1, $nowinfo['year']);
			$tomidnightone = intval($nowinfo['hours']) * 3600;
			$tomidnightone += intval($nowinfo['minutes']) * 60;
			$tomidnightone += intval($nowinfo['seconds']);
			$season_secs = $nowinfo[0] - $baseone - $tomidnightone;
			$isleap = ($nowinfo['year'] % 4 == 0 && ($nowinfo['year'] % 100 != 0 || $nowinfo['year'] % 400 == 0) ? true : false);
			if ($isleap) {
				$leapts = mktime(0, 0, 0, 2, 29, $nowinfo['year']);
				if ($nowinfo[0] >= $leapts) {
					$season_secs -= 86400;
				}
			}
			$q = "SELECT `id`,`spname` FROM `#__vikrentcar_seasons` WHERE `from`<".$season_secs." AND `to`<".$season_secs." AND `from`<`to` AND `from`>0 AND `to`>0 AND `year`=".$nowinfo['year'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$expired_s = $dbo->loadAssocList();
				$expired_ids = array();
				foreach ($expired_s as $exps) {
					$expired_ids[] = $exps['id'];
				}
				$q = "DELETE FROM `#__vikrentcar_seasons` WHERE `id` IN (".implode(', ', $expired_ids).");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		//
		$pidcar = VikRequest::getInt('idcar', '', 'request');
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$all_cars = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
		$carsel = '<select id="idcar" name="idcar" onchange="document.seasonsform.submit();"><option value="">'.JText::_('VRCAFFANYCAR').'</option>';
		if (count($all_cars) > 0) {
			foreach ($all_cars as $car) {
				$carsel .= '<option value="'.$car['id'].'"'.($car['id'] == $pidcar ? ' selected="selected"' : '').'>'.$car['name'].'</option>';
			}
			$all_cars_copy = array();
			foreach ($all_cars as $kp => $car) {
				$all_cars_copy[$car['id']] = $car['name'];
			}
			$all_cars = $all_cars_copy;
		}
		$carsel .= '</select>';
		$pidprice = VikRequest::getInt('idprice', '', 'request');
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_prices` ORDER BY `#__vikrentcar_prices`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$all_prices = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
		$pricesel = '<select id="idprice" name="idprice" onchange="document.seasonsform.submit();"><option value="">'.JText::_('VRCSPTYPESPRICE').'</option>';
		if (count($all_prices) > 0) {
			foreach ($all_prices as $price) {
				$pricesel .= '<option value="'.$price['id'].'"'.($price['id'] == $pidprice ? ' selected="selected"' : '').'>'.$price['name'].'</option>';
			}
			$all_prices_copy = array();
			foreach ($all_prices as $kp => $price) {
				$all_prices_copy[$price['id']] = $price['name'];
			}
			$all_prices = $all_prices_copy;
		}
		$pricesel .= '</select>';
		$mainframe = JFactory::getApplication();
		$lim = $mainframe->getUserStateFromRequest("com_vikrentcar.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$pvrcorderby = VikRequest::getString('vrcorderby', '', 'request');
		$pvrcordersort = VikRequest::getString('vrcordersort', '', 'request');
		$validorderby = array('id', 'spname', 'from', 'to', 'diffcost');
		$orderby = $session->get('vrcShowSeasonsOrderby', 'id');
		$ordersort = $session->get('vrcShowSeasonsOrdersort', 'DESC');
		if (!empty($pvrcorderby) && in_array($pvrcorderby, $validorderby)) {
			$orderby = $pvrcorderby;
			$session->set('vrcShowSeasonsOrderby', $orderby);
			if (!empty($pvrcordersort) && in_array($pvrcordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvrcordersort;
				$session->set('vrcShowSeasonsOrdersort', $ordersort);
			}
		}
		$clauses = array();
		if (!empty($pidcar)) {
			$clauses[] = "`s`.`idcars` LIKE '%-".$pidcar."-%'";
		}
		if (!empty($pidprice)) {
			$clauses[] = "(`s`.`idprices` LIKE '%-".$pidprice."-%' OR CHAR_LENGTH(`s`.`idprices`) = 0)";
		}
		$q = "SELECT SQL_CALC_FOUND_ROWS `s`.* FROM `#__vikrentcar_seasons` AS `s`".(count($clauses) > 0 ? " WHERE ".implode(" AND ", $clauses) : "")." ORDER BY `s`.`".$orderby."` ".$ordersort;
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
		$this->carsel = &$carsel;
		$this->all_cars = &$all_cars;
		$this->pricesel = &$pricesel;
		$this->all_prices = &$all_prices;
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
		JToolBarHelper::title(JText::_('VRMAINSEASONSTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
			JToolBarHelper::addNew('newseason', JText::_('VRMAINSEASONSNEW'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
			JToolBarHelper::editList('editseason', JText::_('VRMAINSEASONSEDIT'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentcar')) {
			JToolBarHelper::deleteList(JText::_('VRCDELCONFIRM'), 'removeseasons', JText::_('VRMAINSEASONSDEL'));
			JToolBarHelper::spacer();
		}
	}

}
