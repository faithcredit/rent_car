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

class VikRentCarViewTariffs extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		$aid = $cid[0];

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		if (empty($aid)) {
			$q = "SELECT `id` FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC LIMIT 1";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$aid = $dbo->loadResult();
			}
		}
		if (empty($aid)) {
			VikError::raiseWarning('', 'No Cars.');
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
			exit;
		}
		$q = "SELECT `id`,`name`,`img` FROM `#__vikrentcar_cars` WHERE `id`=".$dbo->quote($aid).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() != 1) {
			VikError::raiseWarning('', 'No Cars.');
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
			exit;
		}
		$carrows = $dbo->loadAssoc();
		$q = "SELECT * FROM `#__vikrentcar_prices`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$prices = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		$pnewtar = VikRequest::getString('newdispcost', '', 'request');
		$pddaysfrom = VikRequest::getInt('ddaysfrom', '', 'request');
		$pddaysto = VikRequest::getInt('ddaysto', '', 'request');
		if (!empty($pnewtar) && !empty($pddaysfrom) && is_array($prices)) {
			if (empty($pddaysto) || $pddaysfrom == $pddaysto) {
				foreach ($prices as $pr) {
					$tmpvarone = VikRequest::getFloat('dprice'.$pr['id'], '', 'request');
					if (!empty($tmpvarone)) {
						$tmpvartwo = VikRequest::getString('dattr'.$pr['id'], '', 'request');
						$multipattr = is_numeric($tmpvartwo) ? true : false;
						$safeq = "SELECT `id` FROM `#__vikrentcar_dispcost` WHERE `days`=".$dbo->quote($pddaysfrom)." AND `idcar`='".$carrows['id']."' AND `idprice`='".$pr['id']."';";
						$dbo->setQuery($safeq);
						$dbo->execute();
						if ($dbo->getNumRows() == 0) {
							$q = "INSERT INTO `#__vikrentcar_dispcost` (`idcar`,`days`,`idprice`,`cost`,`attrdata`) VALUES('".$carrows['id']."',".$dbo->quote($pddaysfrom).",'".$pr['id']."','".($tmpvarone * $pddaysfrom)."',".($multipattr ? "'".($tmpvartwo  * $pddaysfrom)."'" : $dbo->quote($tmpvartwo)).");";
							$dbo->setQuery($q);
							$dbo->execute();
						} elseif ($dbo->getNumRows() == 1) {
							$upd_id = $dbo->loadResult();
							$q = "UPDATE `#__vikrentcar_dispcost` SET `cost`='".($tmpvarone * $pddaysfrom)."', `attrdata`=".($multipattr ? "'".($tmpvartwo  * $pddaysfrom)."'" : $dbo->quote($tmpvartwo))." WHERE `id`=".(int)$upd_id." AND `days`=".$dbo->quote($pddaysfrom)." AND `idcar`='".$carrows['id']."' AND `idprice`='".$pr['id']."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			} else {
				$pddaysto = intval($pddaysto) > 365 ? 365 : $pddaysto;
				for ($i = intval($pddaysfrom); $i <= intval($pddaysto); $i++) {
					foreach ($prices as $pr) {
						$tmpvarone = VikRequest::getFloat('dprice'.$pr['id'], '', 'request');
						if (!empty($tmpvarone)) {
							$tmpvartwo = VikRequest::getString('dattr'.$pr['id'], '', 'request');
							$multipattr = is_numeric($tmpvartwo) ? true : false;
							$safeq = "SELECT `id` FROM `#__vikrentcar_dispcost` WHERE `days`=".$dbo->quote($i)." AND `idcar`='".$carrows['id']."' AND `idprice`='".$pr['id']."';";
							$dbo->setQuery($safeq);
							$dbo->execute();
							if ($dbo->getNumRows() == 0) {
								$q = "INSERT INTO `#__vikrentcar_dispcost` (`idcar`,`days`,`idprice`,`cost`,`attrdata`) VALUES('".$carrows['id']."',".$dbo->quote($i).",'".$pr['id']."','".($tmpvarone * $i)."',".($multipattr ? "'".($tmpvartwo  * $i)."'" : $dbo->quote($tmpvartwo)).");";
								$dbo->setQuery($q);
								$dbo->execute();
							} elseif ($dbo->getNumRows() == 1) {
								$upd_id = $dbo->loadResult();
								$q = "UPDATE `#__vikrentcar_dispcost` SET `cost`='".($tmpvarone * $i)."', `attrdata`=".($multipattr ? "'".($tmpvartwo  * $i)."'" : $dbo->quote($tmpvartwo))." WHERE `id`=".(int)$upd_id." AND `days`=".$dbo->quote($i)." AND `idcar`='".$carrows['id']."' AND `idprice`='".$pr['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
							}
						}
					}
				}
			}
		}
		$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `idcar`='".$carrows['id']."' ORDER BY `#__vikrentcar_dispcost`.`days` ASC, `#__vikrentcar_dispcost`.`idprice` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$rows = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$allc = $dbo->loadAssocList();

		$this->carrows = &$carrows;
		$this->rows = &$rows;
		$this->prices = &$prices;
		$this->allc = &$allc;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINTARIFFETITLE'), 'vikrentcar');
		JToolBarHelper::save( 'cancel', JText::_('VRMAINTARIFFEBACK'));
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentcar')) {
			JToolBarHelper::deleteList(JText::_('VRCDELCONFIRM'), 'removetariffs', JText::_('VRMAINTARIFFEDEL'));
		}
	}

}
