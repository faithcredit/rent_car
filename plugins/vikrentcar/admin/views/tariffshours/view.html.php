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

class VikRentCarViewTariffshours extends JViewVikRentCar {
	
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
		$phhoursfrom = VikRequest::getInt('hhoursfrom', '', 'request');
		$phhoursto = VikRequest::getInt('hhoursto', '', 'request');
		//maximum 23 hours
		if (!empty($phhoursfrom) && intval($phhoursfrom) > 23) {
			$phhoursfrom = 23;
		}
		if (!empty($phhoursto) && intval($phhoursto) > 23) {
			$phhoursto = 23;
		}
		//
		if (!empty($pnewtar) && !empty($phhoursfrom) && is_array($prices)) {
			if (empty($phhoursto) || $phhoursfrom == $phhoursto) {
				foreach ($prices as $pr) {
					$tmpvarone = VikRequest::getFloat('hprice'.$pr['id'], '', 'request');
					if (!empty($tmpvarone)) {
						$tmpvartwo = VikRequest::getString('hattr'.$pr['id'], '', 'request');
						$multipattr = is_numeric($tmpvartwo) ? true : false;
						$safeq = "SELECT `id` FROM `#__vikrentcar_dispcosthours` WHERE `hours`=".$dbo->quote($phhoursfrom)." AND `idcar`='".$carrows['id']."' AND `idprice`='".$pr['id']."';";
						$dbo->setQuery($safeq);
						$dbo->execute();
						if ($dbo->getNumRows() == 0) {
							$q = "INSERT INTO `#__vikrentcar_dispcosthours` (`idcar`,`hours`,`idprice`,`cost`,`attrdata`) VALUES('".$carrows['id']."',".$dbo->quote($phhoursfrom).",'".$pr['id']."','".($tmpvarone * $phhoursfrom)."',".($multipattr ? "'".($tmpvartwo  * $phhoursfrom)."'" : $dbo->quote($tmpvartwo)).");";
							$dbo->setQuery($q);
							$dbo->execute();
						} elseif ($dbo->getNumRows() == 1) {
							$upd_id = $dbo->loadResult();
							$q = "UPDATE `#__vikrentcar_dispcosthours` SET `cost`='".($tmpvarone * $phhoursfrom)."', `attrdata`=".($multipattr ? "'".($tmpvartwo  * $phhoursfrom)."'" : $dbo->quote($tmpvartwo))." WHERE `id`=".(int)$upd_id." AND `hours`=".$dbo->quote($phhoursfrom)." AND `idcar`='".$carrows['id']."' AND `idprice`='".$pr['id']."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			} else {
				for ($i = intval($phhoursfrom); $i <= intval($phhoursto); $i++) {
					foreach ($prices as $pr) {
						$tmpvarone = VikRequest::getFloat('hprice'.$pr['id'], '', 'request');
						if (!empty($tmpvarone)) {
							$tmpvartwo = VikRequest::getString('hattr'.$pr['id'], '', 'request');
							$multipattr = is_numeric($tmpvartwo) ? true : false;
							$safeq = "SELECT `id` FROM `#__vikrentcar_dispcosthours` WHERE `hours`=".$dbo->quote($i)." AND `idcar`='".$carrows['id']."' AND `idprice`='".$pr['id']."';";
							$dbo->setQuery($safeq);
							$dbo->execute();
							if ($dbo->getNumRows() == 0) {
								$q = "INSERT INTO `#__vikrentcar_dispcosthours` (`idcar`,`hours`,`idprice`,`cost`,`attrdata`) VALUES('".$carrows['id']."',".$dbo->quote($i).",'".$pr['id']."','".($tmpvarone * $i)."',".($multipattr ? "'".($tmpvartwo  * $i)."'" : $dbo->quote($tmpvartwo)).");";
								$dbo->setQuery($q);
								$dbo->execute();
							} elseif ($dbo->getNumRows() == 1) {
								$upd_id = $dbo->loadResult();
								$q = "UPDATE `#__vikrentcar_dispcosthours` SET `cost`='".($tmpvarone * $i)."', `attrdata`=".($multipattr ? "'".($tmpvartwo  * $i)."'" : $dbo->quote($tmpvartwo))." WHERE `id`=".(int)$upd_id." AND `hours`=".$dbo->quote($i)." AND `idcar`='".$carrows['id']."' AND `idprice`='".$pr['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
							}
						}
					}
				}
			}
		}
		$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `idcar`='".$carrows['id']."' ORDER BY `#__vikrentcar_dispcosthours`.`hours` ASC, `#__vikrentcar_dispcosthours`.`idprice` ASC;";
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
			JToolBarHelper::deleteList(JText::_('VRCDELCONFIRM'), 'removetariffshours', JText::_('VRMAINTARIFFEDEL'));
		}
	}

}
