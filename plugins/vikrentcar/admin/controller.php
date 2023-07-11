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

// import Joomla controller library
jimport('joomla.application.component.controller');

class VikRentCarController extends JControllerVikRentCar {

	/**
	 * Default controller's method when no task is defined,
	 * or no method exists for that task. If a View is requested.
	 * attempts to set it, otherwise sets the default View.
	 */
	public function display($cachable = false, $urlparams = array()) {

		$view = VikRequest::getVar('view', '');
		$header_val = '';

		if (!empty($view)) {
			$header_val = $view;
			VikRequest::setVar('view', $view);
		} else {
			$header_val = '18';
			VikRequest::setVar('view', 'dashboard');
		}

		VikRentCarHelper::printHeader($header_val);
		
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function places() {
		VikRentCarHelper::printHeader("3");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'places'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newplace() {
		VikRentCarHelper::printHeader("3");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageplace'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editplace() {
		VikRentCarHelper::printHeader("3");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageplace'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createplace() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$pname = VikRequest::getString('placename', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$plat = VikRequest::getString('lat', '', 'request');
		$plng = VikRequest::getString('lng', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$popentimefh = VikRequest::getString('opentimefh', '', 'request');
		$popentimefm = VikRequest::getInt('opentimefm', '', 'request');
		$popentimeth = VikRequest::getString('opentimeth', '', 'request');
		$popentimetm = VikRequest::getInt('opentimetm', '', 'request');
		$pclosingdays = VikRequest::getString('closingdays', '', 'request');
		$psuggopentimeh = VikRequest::getInt('suggopentimeh', '', 'request');
		$pwopeningfh = VikRequest::getVar('wopeningfh', array());
		$pwopeningfm = VikRequest::getVar('wopeningfm', array());
		$pwopeningth = VikRequest::getVar('wopeningth', array());
		$pwopeningtm = VikRequest::getVar('wopeningtm', array());
		$pwbreakingfh = VikRequest::getVar('wbreakingfh', array());
		$pwbreakingfm = VikRequest::getVar('wbreakingfm', array());
		$pwbreakingth = VikRequest::getVar('wbreakingth', array());
		$pwbreakingtm = VikRequest::getVar('wbreakingtm', array());
		$opentime = "";
		$suggopentimeh = !empty($psuggopentimeh) ? ($psuggopentimeh * 3600) : '';
		if (strlen($popentimefh) > 0 && strlen($popentimeth) > 0) {
			$openingh = $popentimefh * 3600;
			$openingm = $popentimefm * 60;
			$openingts = $openingh + $openingm;
			$closingh = $popentimeth * 3600;
			$closingm = $popentimetm * 60;
			$closingts = $closingh + $closingm;
			if ($closingts > $openingts || $openingts > $closingts) {
				$opentime = $openingts."-".$closingts;
			}
		}
		if (!empty($pname)) {
			$q = "SELECT `ordering` FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			
			// VRC 1.12 - override opening time
			$wopening = [];
			foreach ($pwopeningfh as $d_ind => $fh) {
				if (!strlen($fh) || isset($wopening[$d_ind]) || $d_ind > 6 || !isset($pwopeningth[$d_ind]) || !strlen($pwopeningth[$d_ind])) {
					continue;
				}
				$wopening[$d_ind] = [
					'fh' => (int)$fh,
					'fm' => (int)$pwopeningfm[$d_ind],
					'th' => (int)$pwopeningth[$d_ind],
					'tm' => (int)$pwopeningtm[$d_ind],
				];
				/**
				 * We allow breaks between the opening times.
				 * 
				 * @since 	1.15.0 (J) - 1.3.0 (WP)
				 */
				$breaks = [];
				if (!empty($pwbreakingfh[$d_ind])) {
					foreach ($pwbreakingfh[$d_ind] as $bk => $break_fh) {
						if (!strlen($break_fh) || !isset($pwbreakingth[$d_ind]) || !isset($pwbreakingth[$d_ind][$bk]) || !strlen($pwbreakingth[$d_ind][$bk])) {
							continue;
						}
						// push break
						$breaks[] = [
							'fh' => (int)$break_fh,
							'fm' => (int)$pwbreakingfm[$d_ind][$bk],
							'th' => (int)$pwbreakingth[$d_ind][$bk],
							'tm' => (int)$pwbreakingtm[$d_ind][$bk],
						];
					}
				}
				if (count($breaks)) {
					// push week-day breaks
					$wopening[$d_ind]['breaks'] = $breaks;
				}
			}

			$q = "INSERT INTO `#__vikrentcar_places` (`name`,`lat`,`lng`,`descr`,`opentime`,`closingdays`,`idiva`,`defaulttime`,`ordering`,`address`,`wopening`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($plat).", ".$dbo->quote($plng).", ".$dbo->quote($pdescr).", '".$opentime."', ".$dbo->quote($pclosingdays).", ".(!empty($ppraliq) ? intval($ppraliq) : "NULL").", ".(!empty($suggopentimeh) ? "'".$suggopentimeh."'" : "NULL").", ".$newsortnum.", ".$dbo->quote($paddress).", ".$dbo->quote(json_encode($wopening)).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));
		}
		$app->redirect("index.php?option=com_vikrentcar&task=places");
	}

	public function updateplace()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_updateplace();
	}

	public function updateplaceapply()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_updateplace(true);
	}

	protected function do_updateplace($remain = false)
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$pname = VikRequest::getString('placename', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$plat = VikRequest::getString('lat', '', 'request');
		$plng = VikRequest::getString('lng', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getInt('whereup', 0, 'request');
		$popentimefh = VikRequest::getString('opentimefh', '', 'request');
		$popentimefm = VikRequest::getInt('opentimefm', '', 'request');
		$popentimeth = VikRequest::getString('opentimeth', '', 'request');
		$popentimetm = VikRequest::getInt('opentimetm', '', 'request');
		$pclosingdays = VikRequest::getString('closingdays', '', 'request');
		$psuggopentimeh = VikRequest::getInt('suggopentimeh', '', 'request');
		$pwopeningfh = VikRequest::getVar('wopeningfh', array());
		$pwopeningfm = VikRequest::getVar('wopeningfm', array());
		$pwopeningth = VikRequest::getVar('wopeningth', array());
		$pwopeningtm = VikRequest::getVar('wopeningtm', array());
		$pwbreakingfh = VikRequest::getVar('wbreakingfh', array());
		$pwbreakingfm = VikRequest::getVar('wbreakingfm', array());
		$pwbreakingth = VikRequest::getVar('wbreakingth', array());
		$pwbreakingtm = VikRequest::getVar('wbreakingtm', array());
		$opentime = "";
		$suggopentimeh = !empty($psuggopentimeh) ? ($psuggopentimeh * 3600) : '';
		if (strlen($popentimefh) > 0 && strlen($popentimeth) > 0) {
			$openingh = $popentimefh * 3600;
			$openingm = $popentimefm * 60;
			$openingts = $openingh + $openingm;
			$closingh = $popentimeth * 3600;
			$closingm = $popentimetm * 60;
			$closingts = $closingh + $closingm;
			if ($closingts > $openingts || $openingts > $closingts) {
				$opentime = $openingts."-".$closingts;
			}
		}
		if (!empty($pname)) {
			
			// VRC 1.12 - override opening time
			$wopening = [];
			foreach ($pwopeningfh as $d_ind => $fh) {
				if (!strlen($fh) || isset($wopening[$d_ind]) || $d_ind > 6 || !isset($pwopeningth[$d_ind]) || !strlen($pwopeningth[$d_ind])) {
					continue;
				}
				$wopening[$d_ind] = [
					'fh' => (int)$fh,
					'fm' => (int)$pwopeningfm[$d_ind],
					'th' => (int)$pwopeningth[$d_ind],
					'tm' => (int)$pwopeningtm[$d_ind],
				];
				/**
				 * We allow breaks between the opening times.
				 * 
				 * @since 	1.15.0 (J) - 1.3.0 (WP)
				 */
				$breaks = [];
				if (!empty($pwbreakingfh[$d_ind])) {
					foreach ($pwbreakingfh[$d_ind] as $bk => $break_fh) {
						if (!strlen($break_fh) || !isset($pwbreakingth[$d_ind]) || !isset($pwbreakingth[$d_ind][$bk]) || !strlen($pwbreakingth[$d_ind][$bk])) {
							continue;
						}
						// push break
						$breaks[] = [
							'fh' => (int)$break_fh,
							'fm' => (int)$pwbreakingfm[$d_ind][$bk],
							'th' => (int)$pwbreakingth[$d_ind][$bk],
							'tm' => (int)$pwbreakingtm[$d_ind][$bk],
						];
					}
				}
				if (count($breaks)) {
					// push week-day breaks
					$wopening[$d_ind]['breaks'] = $breaks;
				}
			}

			$q = "UPDATE `#__vikrentcar_places` SET `name`=".$dbo->quote($pname).",`lat`=".$dbo->quote($plat).",`lng`=".$dbo->quote($plng).",`descr`=".$dbo->quote($pdescr).",`opentime`='".$opentime."',`closingdays`=".$dbo->quote($pclosingdays).",`idiva`=".(!empty($ppraliq) ? intval($ppraliq) : "NULL").",`defaulttime`=".(!empty($suggopentimeh) ? "'".$suggopentimeh."'" : "NULL").",`address`=".$dbo->quote($paddress).",`wopening`=".$dbo->quote(json_encode($wopening))." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));
		}

		if ($remain === true) {
			$app->redirect("index.php?option=com_vikrentcar&task=editplace&cid[]=" . $pwhereup);
			exit;
		}
		$app->redirect("index.php?option=com_vikrentcar&task=places");
	}

	public function removeplace() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_places` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=places");
	}

	public function cancelplace() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=places");
	}

	public function iva() {
		VikRentCarHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'iva'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newiva() {
		VikRentCarHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editiva() {
		VikRentCarHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createiva() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getString('aliqperc', '', 'request');
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDbo();
			$q = "INSERT INTO `#__vikrentcar_iva` (`name`,`aliq`) VALUES(".$dbo->quote($paliqname).", ".floatval($paliqperc).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=iva");
	}

	public function updateiva() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getString('aliqperc', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDbo();
			$q = "UPDATE `#__vikrentcar_iva` SET `name`=".$dbo->quote($paliqname).",`aliq`=".floatval($paliqperc)." WHERE `id`=".intval($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=iva");
	}

	public function removeiva() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_iva` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=iva");
	}

	public function canceliva() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=iva");
	}

	public function prices() {
		VikRentCarHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'prices'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newprice() {
		VikRentCarHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editprice() {
		VikRentCarHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createprice() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		if (!empty($pprice)) {
			$dbo = JFactory::getDbo();
			$q = "INSERT INTO `#__vikrentcar_prices` (`name`,`attr`,`idiva`) VALUES(".$dbo->quote($pprice).", ".$dbo->quote($pattr).", ".(!empty($ppraliq) ? intval($ppraliq) : 'NULL').");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=prices");
	}

	public function updateprice() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pprice)) {
			$dbo = JFactory::getDbo();
			$q = "UPDATE `#__vikrentcar_prices` SET `name`=".$dbo->quote($pprice).",`attr`=".$dbo->quote($pattr).",`idiva`=".(!empty($ppraliq) ? intval($ppraliq) : 'NULL')." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=prices");
	}

	public function removeprice() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_prices` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=prices");
	}

	public function cancelprice() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=prices");
	}

	public function categories() {
		VikRentCarHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'categories'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newcat() {
		VikRentCarHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editcat() {
		VikRentCarHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createcat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		if (!empty($pcatname)) {
			$dbo = JFactory::getDbo();
			$q = "SELECT `ordering` FROM `#__vikrentcar_categories` ORDER BY `#__vikrentcar_categories`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			$q = "INSERT INTO `#__vikrentcar_categories` (`name`,`descr`,`ordering`) VALUES(".$dbo->quote($pcatname).", ".$dbo->quote($pdescr).", ".(int)$newsortnum.");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
	}

	public function updatecat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pcatname)) {
			$dbo = JFactory::getDbo();
			$q = "UPDATE `#__vikrentcar_categories` SET `name`=".$dbo->quote($pcatname).", `descr`=".$dbo->quote($pdescr)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
	}

	public function removecat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_categories` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
	}

	public function cancelcat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
	}

	/**
	 * Helper task to remove uploaded images without needing to replace them.
	 * 
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function trash_upld_img()
	{
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$ptype = VikRequest::getString('type', '', 'request');
		$prid = VikRequest::getInt('rid', 0, 'request');

		$red_to = 'index.php?option=com_vikrentcar';

		if ($ptype == 'carat') {
			// unset the image from a characteristic record
			$q = "SELECT * FROM `#__vikrentcar_caratteristiche` WHERE `id`={$prid}";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$record = $dbo->loadObject();
				$path_to_icon = VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $record->icon;
				if (is_file($path_to_icon)) {
					// delete the file
					JFile::delete($path_to_icon);
				}
				// update record
				$record->icon = '';
				$dbo->updateObject('#__vikrentcar_caratteristiche', $record, 'id');
				// set redirect URL
				$red_to = 'index.php?option=com_vikrentcar&task=editcarat&cid[]=' . $record->id;
			}
		} elseif ($ptype == 'option') {
			// unset the image from an option/extra record
			$q = "SELECT `id`,`img` FROM `#__vikrentcar_optionals` WHERE `id`={$prid}";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$record = $dbo->loadObject();
				$path_to_icon = VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $record->img;
				if (is_file($path_to_icon)) {
					// delete the file
					JFile::delete($path_to_icon);
				}
				// update record
				$record->img = '';
				$dbo->updateObject('#__vikrentcar_optionals', $record, 'id');
				// set redirect URL
				$red_to = 'index.php?option=com_vikrentcar&task=editoptional&cid[]=' . $record->id;
			}
		}

		$app->redirect($red_to);
		$app->close();
	}

	public function carat() {
		VikRentCarHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'carat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newcarat() {
		VikRentCarHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editcarat() {
		VikRentCarHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createcarat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcaratmix = VikRequest::getString('caratmix', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array());
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new VikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$dbo = JFactory::getDbo();
			$q = "SELECT `ordering` FROM `#__vikrentcar_caratteristiche` ORDER BY `#__vikrentcar_caratteristiche`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			$pordering = VikRequest::getInt('ordering', 0, 'request');
			$newsortnum = !empty($pordering) ? $pordering : $newsortnum;
			$q = "INSERT INTO `#__vikrentcar_caratteristiche` (`name`,`icon`,`align`,`textimg`,`ordering`) VALUES(".$dbo->quote($pcaratname).", ".$dbo->quote($picon).", ".$dbo->quote($pcaratmix).", ".$dbo->quote($pcarattextimg).", '".$newsortnum."');";
			$dbo->setQuery($q);
			$dbo->execute();

			$new_carat_id = $dbo->insertid();
			if (!empty($new_carat_id)) {
				// assign/unset carat-cars relations
				$cars_with_carat = array();
				if (count($pidcars)) {
					// assign this new carat to the requested cars
					foreach ($pidcars as $idcar) {
						if (empty($idcar)) {
							continue;
						}
						$q = "SELECT `id`, `idcarat` FROM `#__vikrentcar_cars` WHERE `id`=" . (int)$idcar . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						if (!$dbo->getNumRows()) {
							continue;
						}
						$car_data = $dbo->loadAssoc();
						array_push($cars_with_carat, $car_data['id']);
						$current_carats = empty($car_data['idcarat']) ? array() : explode(';', rtrim($car_data['idcarat'], ';'));
						if (in_array((string)$new_carat_id, $current_carats)) {
							continue;
						}
						if (count($current_carats) === 1 && (string)$current_carats[0] == '0') {
							// make sure we do not concatenate a real ID to 0
							$current_carats = array();
						}
						array_push($current_carats, $new_carat_id);
						$new_opts = implode(';', $current_carats) . ';';
						$q = "UPDATE `#__vikrentcar_cars` SET `idcarat`=" . $dbo->quote($new_opts) . " WHERE `id`={$car_data['id']};";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				if (!count($cars_with_carat)) {
					// get all cars to unset this carat (if previously set)
					array_push($cars_with_carat, '0');
				}
				// unset the carat from the other cars that may have it
				$q = "SELECT `id`, `idcarat` FROM `#__vikrentcar_cars` WHERE `id` NOT IN (" . implode(', ', $cars_with_carat) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$unset_cars_carat = $dbo->loadAssocList();
					foreach ($unset_cars_carat as $car_data) {
						$current_carats = empty($car_data['idcarat']) ? array() : explode(';', rtrim($car_data['idcarat'], ';'));
						if (!in_array((string)$new_carat_id, $current_carats)) {
							// this car is not using this carat
							continue;
						}
						$caratkey = array_search((string)$new_carat_id, $current_carats);
						if ($caratkey === false) {
							// key not found
							continue;
						}
						// unset this carat ID from the string
						unset($current_carats[$caratkey]);
						if (!count($current_carats)) {
							// a car with no carats assigned will be listed as "0;"
							$current_carats = array(0);
						}
						$new_opts = implode(';', $current_carats) . ';';
						$q = "UPDATE `#__vikrentcar_cars` SET `idcarat`=" . $dbo->quote($new_opts) . " WHERE `id`={$car_data['id']};";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				//
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
	}

	public function updatecarat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcaratmix = VikRequest::getString('caratmix', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array());
		$pordering = VikRequest::getInt('ordering', 1, 'request');
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new VikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$dbo = JFactory::getDbo();
			$q = "UPDATE `#__vikrentcar_caratteristiche` SET `name`=".$dbo->quote($pcaratname).",".(strlen($picon) > 0 ? "`icon`='".$picon."'," : "")."`align`=".$dbo->quote($pcaratmix).",`textimg`=".$dbo->quote($pcarattextimg).",`ordering`={$pordering} WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();

			// assign/unset carat-cars relations
			$cars_with_carat = array();
			if (count($pidcars)) {
				// assign this new carat to the requested cars
				foreach ($pidcars as $idcar) {
					if (empty($idcar)) {
						continue;
					}
					$q = "SELECT `id`, `idcarat` FROM `#__vikrentcar_cars` WHERE `id`=" . (int)$idcar . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					if (!$dbo->getNumRows()) {
						continue;
					}
					$car_data = $dbo->loadAssoc();
					array_push($cars_with_carat, $car_data['id']);
					$current_carats = empty($car_data['idcarat']) ? array() : explode(';', rtrim($car_data['idcarat'], ';'));
					if (in_array((string)$pwhereup, $current_carats)) {
						continue;
					}
					if (count($current_carats) === 1 && (string)$current_carats[0] == '0') {
						// make sure we do not concatenate a real ID to 0
						$current_carats = array();
					}
					array_push($current_carats, $pwhereup);
					$new_carats = implode(';', $current_carats) . ';';
					$q = "UPDATE `#__vikrentcar_cars` SET `idcarat`=" . $dbo->quote($new_carats) . " WHERE `id`={$car_data['id']};";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			if (!count($cars_with_carat)) {
				// get all cars to unset this carat (if previously set)
				array_push($cars_with_carat, '0');
			}
			// unset the carat from the other cars that may have it
			$q = "SELECT `id`, `idcarat` FROM `#__vikrentcar_cars` WHERE `id` NOT IN (" . implode(', ', $cars_with_carat) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$unset_cars_carat = $dbo->loadAssocList();
				foreach ($unset_cars_carat as $car_data) {
					$current_carats = empty($car_data['idcarat']) ? array() : explode(';', rtrim($car_data['idcarat'], ';'));
					if (!in_array((string)$pwhereup, $current_carats)) {
						// this car is not using this carat
						continue;
					}
					$caratkey = array_search((string)$pwhereup, $current_carats);
					if ($caratkey === false) {
						// key not found
						continue;
					}
					// unset this carat ID from the string
					unset($current_carats[$caratkey]);
					if (!count($current_carats)) {
						// a car with no carats assigned will be listed as "0;"
						$current_carats = array(0);
					}
					$new_carats = implode(';', $current_carats) . ';';
					$q = "UPDATE `#__vikrentcar_cars` SET `idcarat`=" . $dbo->quote($new_carats) . " WHERE `id`={$car_data['id']};";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
	}

	public function removecarat() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "SELECT `icon` FROM `#__vikrentcar_caratteristiche` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['icon']) && file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['icon'])) {
						@unlink(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['icon']);
					}
				}	
				$q = "DELETE FROM `#__vikrentcar_caratteristiche` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
	}

	public function cancelcarat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
	}

	public function optionals() {
		VikRentCarHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'optionals'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newoptional() {
		VikRentCarHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageopt'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editoptional() {
		VikRentCarHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageopt'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createoptional() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$app = JFactory::getApplication();
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getFloat('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$pmaxprice = VikRequest::getFloat('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptaliq = VikRequest::getString('optaliq', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforceifdays = VikRequest::getInt('forceifdays', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array());
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		if (!empty($poptname)) {
			/**
			 * In order to avoid issues with the calculation of the taxes for the options,
			 * the name should not contain the semi-colon (:) or the currency name.
			 * 
			 * @since 	February 2019
			 */
			$poptname = str_replace(':', '', str_replace(VikRentCar::getCurrencyName(), '', $poptname));
			//
			if (intval($_FILES['optimg']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['optimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new VikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$poptperday = ($poptperday == "each" ? "1" : "0");
			($popthmany == "yes" ? $popthmany = "1" : $popthmany = "0");
			$dbo = JFactory::getDbo();
			$q = "SELECT `ordering` FROM `#__vikrentcar_optionals` ORDER BY `#__vikrentcar_optionals`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			$q = "INSERT INTO `#__vikrentcar_optionals` (`name`,`descr`,`cost`,`perday`,`hmany`,`img`,`idiva`,`maxprice`,`forcesel`,`forceval`,`ordering`,`forceifdays`) VALUES(".$dbo->quote($poptname).", ".$dbo->quote($poptdescr).", ".$dbo->quote($poptcost).", ".$dbo->quote($poptperday).", ".$dbo->quote($popthmany).", '".$picon."', ".$dbo->quote($poptaliq).", ".$dbo->quote($pmaxprice).", '".$pforcesel."', '".$strforceval."', '".$newsortnum."', '".$pforceifdays."');";
			$dbo->setQuery($q);
			$dbo->execute();
			$newoptid = $dbo->insertid();
			$app->enqueueMessage(JText::_('VRCSUCCUPDOPTION'));

			if (!empty($newoptid)) {
				// assign/unset option-cars relations
				$cars_with_opt = array();
				if (count($pidcars)) {
					// assign this new option to the requested cars
					foreach ($pidcars as $idcar) {
						if (empty($idcar)) {
							continue;
						}
						$q = "SELECT `id`, `idopt` FROM `#__vikrentcar_cars` WHERE `id`=" . (int)$idcar . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						if (!$dbo->getNumRows()) {
							continue;
						}
						$car_data = $dbo->loadAssoc();
						array_push($cars_with_opt, $car_data['id']);
						$current_opts = empty($car_data['idopt']) ? array() : explode(';', rtrim($car_data['idopt'], ';'));
						if (in_array((string)$newoptid, $current_opts)) {
							continue;
						}
						if (count($current_opts) === 1 && (string)$current_opts[0] == '0') {
							// make sure we do not concatenate a real ID to 0
							$current_opts = array();
						}
						array_push($current_opts, $newoptid);
						$new_opts = implode(';', $current_opts) . ';';
						$q = "UPDATE `#__vikrentcar_cars` SET `idopt`=" . $dbo->quote($new_opts) . " WHERE `id`={$car_data['id']};";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				if (!count($cars_with_opt)) {
					// get all cars to unset this option (if previously set)
					array_push($cars_with_opt, '0');
				}
				// unset the option from the other cars that may have it
				$q = "SELECT `id`, `idopt` FROM `#__vikrentcar_cars` WHERE `id` NOT IN (" . implode(', ', $cars_with_opt) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$unset_cars_opt = $dbo->loadAssocList();
					foreach ($unset_cars_opt as $car_data) {
						$current_opts = empty($car_data['idopt']) ? array() : explode(';', rtrim($car_data['idopt'], ';'));
						if (!in_array((string)$newoptid, $current_opts)) {
							// this car is not using this option
							continue;
						}
						$optkey = array_search((string)$newoptid, $current_opts);
						if ($optkey === false) {
							// key not found
							continue;
						}
						// unset this option ID from the string
						unset($current_opts[$optkey]);
						if (!count($current_opts)) {
							// a car with no options assigned will be listed as "0;"
							$current_opts = array(0);
						}
						$new_opts = implode(';', $current_opts) . ';';
						$q = "UPDATE `#__vikrentcar_cars` SET `idopt`=" . $dbo->quote($new_opts) . " WHERE `id`={$car_data['id']};";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				//
			}
		}
		
		$app->redirect("index.php?option=com_vikrentcar&task=optionals");
	}

	public function updateoptional() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$app = JFactory::getApplication();
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getFloat('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$pmaxprice = VikRequest::getFloat('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptaliq = VikRequest::getString('optaliq', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforceifdays = VikRequest::getInt('forceifdays', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array());
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		if (!empty($poptname)) {
			/**
			 * In order to avoid issues with the calculation of the taxes for the options,
			 * the name should not contain the semi-colon (:) or the currency name.
			 * 
			 * @since 	February 2019
			 */
			$poptname = str_replace(':', '', str_replace(VikRentCar::getCurrencyName(), '', $poptname));
			//
			if (intval($_FILES['optimg']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentCar::uploadFile($_FILES['optimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new VikResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			($poptperday == "each" ? $poptperday="1" : $poptperday="0");
			($popthmany == "yes" ? $popthmany="1" : $popthmany="0");
			$dbo = JFactory::getDbo();
			$q = "UPDATE `#__vikrentcar_optionals` SET `name`=".$dbo->quote($poptname).",`descr`=".$dbo->quote($poptdescr).",`cost`=".$dbo->quote($poptcost).",`perday`=".$dbo->quote($poptperday).",`hmany`=".$dbo->quote($popthmany).",".(strlen($picon)>0 ? "`img`='".$picon."'," : "")."`idiva`=".$dbo->quote($poptaliq).", `maxprice`=".$dbo->quote($pmaxprice).", `forcesel`='".$pforcesel."', `forceval`='".$strforceval."', `forceifdays`='".$pforceifdays."' WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$app->enqueueMessage(JText::_('VRCSUCCUPDOPTION'));

			// assign/unset option-cars relations
			$cars_with_opt = array();
			if (count($pidcars)) {
				// assign this new option to the requested cars
				foreach ($pidcars as $idcar) {
					if (empty($idcar)) {
						continue;
					}
					$q = "SELECT `id`, `idopt` FROM `#__vikrentcar_cars` WHERE `id`=" . (int)$idcar . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					if (!$dbo->getNumRows()) {
						continue;
					}
					$car_data = $dbo->loadAssoc();
					array_push($cars_with_opt, $car_data['id']);
					$current_opts = empty($car_data['idopt']) ? array() : explode(';', rtrim($car_data['idopt'], ';'));
					if (in_array((string)$pwhereup, $current_opts)) {
						continue;
					}
					if (count($current_opts) === 1 && (string)$current_opts[0] == '0') {
						// make sure we do not concatenate a real ID to 0
						$current_opts = array();
					}
					array_push($current_opts, $pwhereup);
					$new_opts = implode(';', $current_opts) . ';';
					$q = "UPDATE `#__vikrentcar_cars` SET `idopt`=" . $dbo->quote($new_opts) . " WHERE `id`={$car_data['id']};";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			if (!count($cars_with_opt)) {
				// get all cars to unset this option (if previously set)
				array_push($cars_with_opt, '0');
			}
			// unset the option from the other cars that may have it
			$q = "SELECT `id`, `idopt` FROM `#__vikrentcar_cars` WHERE `id` NOT IN (" . implode(', ', $cars_with_opt) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$unset_cars_opt = $dbo->loadAssocList();
				foreach ($unset_cars_opt as $car_data) {
					$current_opts = empty($car_data['idopt']) ? array() : explode(';', rtrim($car_data['idopt'], ';'));
					if (!in_array((string)$pwhereup, $current_opts)) {
						// this car is not using this option
						continue;
					}
					$optkey = array_search((string)$pwhereup, $current_opts);
					if ($optkey === false) {
						// key not found
						continue;
					}
					// unset this option ID from the string
					unset($current_opts[$optkey]);
					if (!count($current_opts)) {
						// a car with no options assigned will be listed as "0;"
						$current_opts = array(0);
					}
					$new_opts = implode(';', $current_opts) . ';';
					$q = "UPDATE `#__vikrentcar_cars` SET `idopt`=" . $dbo->quote($new_opts) . " WHERE `id`={$car_data['id']};";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
		}
		
		$app->redirect("index.php?option=com_vikrentcar&task=optionals");
	}

	public function removeoptionals() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "SELECT `img` FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['img']) && file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['img'])) {
						@unlink(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['img']);
					}
				}	
				$q = "DELETE FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=optionals");
	}

	public function canceloptional() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=optionals");
	}

	public function cars() {
		VikRentCarHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'cars'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newcar() {
		VikRentCarHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecar'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editcar() {
		VikRentCarHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecar'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createcar() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pshort_info = VikRequest::getString('short_info', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcplace = VikRequest::getVar('cplace', array(0));
		$pcretplace = VikRequest::getVar('cretplace', array(0));
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pstartfrom = VikRequest::getString('startfrom', '', 'request');
		$psdailycost = VikRequest::getString('sdailycost', '', 'request');
		$psdailycost = intval($psdailycost) == 1 ? 1 : 0;
		$pshourlycal = VikRequest::getString('shourlycal', '', 'request');
		$pshourlycal = intval($pshourlycal) == 1 ? 1 : 0;
		$preqinfo = VikRequest::getInt('reqinfo', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);

		jimport('joomla.filesystem.file');

		if (empty($pcname)) {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
			exit;
		}

		$picon = "";
		if (intval($_FILES['cimg']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['cimg']['name'])!="") {
			if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
				$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
				if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
					$j=1;
					while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
						$j++;
					}
					$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
				} else {
					$j="";
					$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
				}
				VikRentCar::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
				if (!($mainimginfo = getimagesize($pwhere))) {
					@unlink($pwhere);
					$picon="";
				} else {
					@chmod($pwhere, 0644);
					$picon=$j.$safename;
					if ($pautoresize=="1" && !empty($presizeto)) {
						$eforj = new VikResizer();
						$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
						if ($origmod) {
							@unlink($pwhere);
							$picon='r_'.$j.$safename;
						}
					}
					/**
					 * We statically use a value of 600px for a better CSS forcing result for
					 * the thumbnail of the car's main image to be used mainly in the Carslist.
					 * The method VikRentCar::getThumbnailsWidth() is now used to get the max
					 * size of the thumbnails for the Cardetails (extra images). It was previously
					 * used to calculate the max thumb size for the car's main image in the Carslist.
					 * 
					 * @since 	1.13
					 */
					$thumbs_width = 600;
					if ($mainimginfo[0] > $thumbs_width) {
						$eforj = new VikResizer();
						$eforj->proportionalImage(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$picon, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'vthumb_'.$picon, $thumbs_width, $thumbs_width);
					}
					//
				}
			}
		}

		//more images
		$creativik = new VikResizer();
		$bigsdest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
		$thumbsdest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
		$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
		$moreimagestr = "";
		foreach ($pimages['name'] as $kk => $ci) {
			if (!empty($ci)) {
				$arrimgs[] = $kk;
			}
		}
		if (isset($arrimgs) && count($arrimgs)) {
			foreach ($arrimgs as $imgk) {
				if (strlen(trim($pimages['name'][$imgk]))) {
					$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
					$src = $pimages['tmp_name'][$imgk];
					$j="";
					if (file_exists($dest.$filename)) {
						$j=rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = !empty($pimages['tmp_name'][$imgk]) ? getimagesize($pimages['tmp_name'][$imgk]) : [];
					if ($check[2] & imagetypes()) {
						if (VikRentCar::uploadFile($src, $finaldest)) {
							$gimg=$j.$filename;
							//orig img
							$origmod = true;
							if ($pautoresizemore == "1" && !empty($presizetomore)) {
								$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
							} else {
								VikRentCar::uploadFile($finaldest, $bigsdest.'big_'.$j.$filename, true);
							}
							//thumb
							$thumbs_size = VikRentCar::getThumbnailsWidth();
							$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, $thumbs_size, $thumbs_size);
							if (!$thumb || !$origmod) {
								if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
								if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							} else {
								$moreimagestr.=$j.$filename.";;";
							}
							@unlink($finaldest);
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					} else {
						VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
					}
				}
			}
		}
		//end more images
		if (is_array($pcplace) && count($pcplace)) {
			$pcplacedef="";
			foreach ($pcplace as $cpla) {
				$pcplacedef.=$cpla.";";
			}
		} else {
			$pcplacedef="";
		}
		if (is_array($pcretplace) && count($pcretplace)) {
			$pcretplacedef="";
			foreach ($pcretplace as $cpla) {
				$pcretplacedef.=$cpla.";";
			}
		} else {
			$pcretplacedef="";
		}
		if (is_array($pccat) && count($pccat)) {
			$pccatdef="";
			foreach ($pccat as $ccat) {
				$pccatdef.=$ccat.";";
			}
		} else {
			$pccatdef="";
		}
		if (is_array($pccarat) && count($pccarat)) {
			$pccaratdef="";
			foreach ($pccarat as $ccarat) {
				$pccaratdef.=$ccarat.";";
			}
		} else {
			$pccaratdef="";
		}
		if (is_array($pcoptional) && count($pcoptional)) {
			$pcoptionaldef="";
			foreach ($pcoptional as $coptional) {
				$pcoptionaldef.=$coptional.";";
			}
		} else {
			$pcoptionaldef="";
		}
		$pcavaildef=($pcavail=="yes" ? "1" : "0");

		//params
		$car_params = array();
		$car_params['sdailycost'] = $psdailycost;
		$car_params['reqinfo'] = $preqinfo;
		$car_params['email'] = $pemail;
		$car_params['custptitle'] = $pcustptitle;
		$car_params['custptitlew'] = $pcustptitlew;
		$car_params['metakeywords'] = $pmetakeywords;
		$car_params['metadescription'] = $pmetadescription;
		$car_params['shourlycal'] = $pshourlycal;
		$car_params['inspection'] = VikRequest::getString('inspection', '', 'request');

		if (!empty($car_params['inspection']) && preg_match("/.png$/i", $car_params['inspection'])) {
			// make sure the file is valid
			$cms_base_p = defined('ABSPATH') ? ABSPATH : JPATH_SITE;
			$custom_inspection_p = JPath::clean($cms_base_p . '/' . $car_params['inspection']);
			if (!is_file($custom_inspection_p)) {
				$car_params['inspection'] = null;
			}
		} else {
			$car_params['inspection'] = null;
		}

		//distinctive features
		$car_params['features'] = array();
		if ($punits > 0) {
			for ($i=1; $i <= $punits; $i++) { 
				$distf_name = VikRequest::getVar('feature-name'.$i, array());
				$distf_lang = VikRequest::getVar('feature-lang'.$i, array());
				$distf_value = VikRequest::getVar('feature-value'.$i, array());
				foreach ($distf_name as $distf_k => $distf) {
					if (strlen($distf) > 0 && strlen($distf_value[$distf_k]) > 0) {
						$use_key = strlen($distf_lang[$distf_k]) > 0 ? $distf_lang[$distf_k] : $distf;
						$car_params['features'][$i][$use_key] = $distf_value[$distf_k];
					}
				}
			}
		}
		//
		$dbo = JFactory::getDbo();
		$q = "INSERT INTO `#__vikrentcar_cars` (`name`,`img`,`idcat`,`idcarat`,`idopt`,`info`,`idplace`,`avail`,`units`,`idretplace`,`moreimgs`,`startfrom`,`short_info`,`params`,`alias`) VALUES(".$dbo->quote($pcname).",".$dbo->quote($picon).",".$dbo->quote($pccatdef).",".$dbo->quote($pccaratdef).",".$dbo->quote($pcoptionaldef).",".$dbo->quote($pcdescr).",".$dbo->quote($pcplacedef).",".$dbo->quote($pcavaildef).",".($punits > 0 ? $dbo->quote($punits) : "'1'").",".$dbo->quote($pcretplacedef).", ".$dbo->quote($moreimagestr).", ".(strlen($pstartfrom) > 0 ? "'".$pstartfrom."'" : "null").", ".$dbo->quote($pshort_info).", ".$dbo->quote(json_encode($car_params)).", ".$dbo->quote($psefalias).");";
		$dbo->setQuery($q);
		$dbo->execute();
		$lid = $dbo->insertid();
		if (!empty($lid)) {
			$mainframe->enqueueMessage(JText::_('VRCCARSAVEOK'));

			/**
			 * Import remote iCal calendars.
			 * 
			 * @since 	1.15.0 (J) - 1.3.0 (WP)
			 */
			$import_calendars = VikRequest::getVar('calendars', array());
			if (!empty($import_calendars) && !empty($import_calendars['url'])) {
				// parse all calendars
				foreach ($import_calendars['url'] as $cal_key => $cal_url) {
					if (empty($cal_url)) {
						continue;
					}
					// build record object
					$record = new stdClass;
					$record->idcar = (int)$lid;
					$record->name = isset($import_calendars['name']) && !empty($import_calendars['name'][$cal_key]) ? $import_calendars['name'][$cal_key] : JText::_('VRC_IMPORT_CALENDAR_URL');
					$record->url = $cal_url;
					// insert object
					$dbo->insertObject('#__vikrentcar_cars_icals', $record, 'id');
				}
			}

			$mainframe->redirect("index.php?option=com_vikrentcar&task=tariffs&cid[]=".$lid);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
		}
	}

	public function updatecar() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_updatecar();
	}

	public function updatecarapply() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_updatecar(true);
	}

	private function do_updatecar($remain = false) {
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pshort_info = VikRequest::getString('short_info', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcplace = VikRequest::getVar('cplace', array(0));
		$pcretplace = VikRequest::getVar('cretplace', array(0));
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pactmoreimgs = VikRequest::getString('actmoreimgs', '', 'request');
		$pstartfrom = VikRequest::getString('startfrom', '', 'request');
		$psdailycost = VikRequest::getString('sdailycost', '', 'request');
		$psdailycost = intval($psdailycost) == 1 ? 1 : 0;
		$pshourlycal = VikRequest::getString('shourlycal', '', 'request');
		$pshourlycal = intval($pshourlycal) == 1 ? 1 : 0;
		$preqinfo = VikRequest::getInt('reqinfo', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);
		$pimgsorting = VikRequest::getVar('imgsorting', array());

		jimport('joomla.filesystem.file');

		if (empty($pcname)) {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
			exit;
		}

		$picon = "";
		if (intval($_FILES['cimg']['error']) == 0 && VikRentCar::caniWrite(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['cimg']['name'])!="") {
			if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
				$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
				if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
					$j=1;
					while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
						$j++;
					}
					$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
				} else {
					$j="";
					$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
				}
				VikRentCar::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
				if (!($mainimginfo = getimagesize($pwhere))) {
					@unlink($pwhere);
					$picon="";
				} else {
					@chmod($pwhere, 0644);
					$picon=$j.$safename;
					if ($pautoresize=="1" && !empty($presizeto)) {
						$eforj = new VikResizer();
						$origmod = $eforj->proportionalImage($pwhere, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
						if ($origmod) {
							@unlink($pwhere);
							$picon='r_'.$j.$safename;
						}
					}
					/**
					 * We statically use a value of 600px for a better CSS forcing result for
					 * the thumbnail of the car's main image to be used mainly in the Carslist.
					 * The method VikRentCar::getThumbnailsWidth() is now used to get the max
					 * size of the thumbnails for the Cardetails (extra images). It was previously
					 * used to calculate the max thumb size for the car's main image in the Carslist.
					 * 
					 * @since 	1.13
					 */
					$thumbs_width = 600;
					if ($mainimginfo[0] > $thumbs_width) {
						$eforj = new VikResizer();
						$eforj->proportionalImage(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$picon, VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'vthumb_'.$picon, $thumbs_width, $thumbs_width);
					}
					//
				}
			}
		}

		//more images
		$creativik = new VikResizer();
		$bigsdest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
		$thumbsdest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
		$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
		$moreimagestr = $pactmoreimgs;
		foreach ($pimages['name'] as $kk => $ci) {
			if (!empty($ci)) {
				$arrimgs[] = $kk;
			}
		}
		if (isset($arrimgs) && count($arrimgs)) {
			foreach ($arrimgs as $imgk) {
				if (strlen(trim($pimages['name'][$imgk]))) {
					$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
					$src = $pimages['tmp_name'][$imgk];
					$j="";
					if (file_exists($dest.$filename)) {
						$j=rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = !empty($pimages['tmp_name'][$imgk]) ? getimagesize($pimages['tmp_name'][$imgk]) : [];
					if ($check[2] & imagetypes()) {
						if (VikRentCar::uploadFile($src, $finaldest)) {
							$gimg=$j.$filename;
							//orig img
							$origmod = true;
							if ($pautoresizemore == "1" && !empty($presizetomore)) {
								$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
							} else {
								VikRentCar::uploadFile($finaldest, $bigsdest.'big_'.$j.$filename, true);
							}
							//thumb
							$thumbs_size = VikRentCar::getThumbnailsWidth();
							$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, $thumbs_size, $thumbs_size);
							if (!$thumb || !$origmod) {
								if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
								if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							} else {
								$moreimagestr.=$j.$filename.";;";
							}
							@unlink($finaldest);
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					} else {
						VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
					}
				}
			}
		}

		/**
		 * Sorting of extra images.
		 * 
		 * @since 	1.14
		 */
		$sorted_extraim = array();
		$extraim_parts = explode(';;', $moreimagestr);
		foreach ($pimgsorting as $k => $v) {
			$capkey = -1;
			if (isset($extraim_parts[$k])) {
				$sorted_extraim[] = $v;
			}
		}
		$tot_sorted_im = count($sorted_extraim);
		if ($tot_sorted_im != count($extraim_parts)) {
			foreach ($extraim_parts as $k => $v) {
				if ($k <= ($tot_sorted_im - 1)) {
					continue;
				}
				$sorted_extraim[] = $v;
			}
		}
		$moreimagestr = implode(';;', $sorted_extraim);
		//

		//end more images
		if (is_array($pcplace) && count($pcplace)) {
			$pcplacedef="";
			foreach ($pcplace as $cpla) {
				$pcplacedef.=$cpla.";";
			}
		} else {
			$pcplacedef="";
		}
		if (is_array($pcretplace) && count($pcretplace)) {
			$pcretplacedef="";
			foreach ($pcretplace as $cpla) {
				$pcretplacedef.=$cpla.";";
			}
		} else {
			$pcretplacedef="";
		}
		if (is_array($pccat) && count($pccat)) {
			$pccatdef="";
			foreach ($pccat as $ccat) {
				$pccatdef.=$ccat.";";
			}
		} else {
			$pccatdef="";
		}
		if (is_array($pccarat) && count($pccarat)) {
			$pccaratdef="";
			foreach ($pccarat as $ccarat) {
				$pccaratdef.=$ccarat.";";
			}
		} else {
			$pccaratdef="";
		}
		if (is_array($pcoptional) && count($pcoptional)) {
			$pcoptionaldef="";
			foreach ($pcoptional as $coptional) {
				$pcoptionaldef.=$coptional.";";
			}
		} else {
			$pcoptionaldef="";
		}
		$pcavaildef=($pcavail=="yes" ? "1" : "0");

		//params
		$car_params = array();
		$car_params['sdailycost'] = $psdailycost;
		$car_params['reqinfo'] = $preqinfo;
		$car_params['email'] = $pemail;
		$car_params['custptitle'] = $pcustptitle;
		$car_params['custptitlew'] = $pcustptitlew;
		$car_params['metakeywords'] = $pmetakeywords;
		$car_params['metadescription'] = $pmetadescription;
		$car_params['shourlycal'] = $pshourlycal;
		$car_params['inspection'] = VikRequest::getString('inspection', '', 'request');

		//distinctive features
		$car_params['features'] = array();
		$damages = array();
		$damage_show_type = VikRentCar::getDamageShowType();
		$damage_png_path = implode(DIRECTORY_SEPARATOR, [VRC_ADMIN_PATH, 'resources', 'damage_mark.png']);
		$cstatus_png_path = implode(DIRECTORY_SEPARATOR, [VRC_SITE_PATH, 'helpers', 'car_damages', 'car_inspection.png']);
		if (!empty($car_params['inspection']) && preg_match("/.png$/i", $car_params['inspection'])) {
			$cms_base_p = defined('ABSPATH') ? ABSPATH : JPATH_SITE;
			$custom_inspection_p = JPath::clean($cms_base_p . '/' . $car_params['inspection']);
			if (is_file($custom_inspection_p)) {
				// must be a relative path to the CMS media manager
				$cstatus_png_path = $custom_inspection_p;
			}
		} else {
			$car_params['inspection'] = null;
		}
		$damage_font = 'helsinki';
		$damage_font_size = 11;
		// Set the enviroment variable for PHP-GD
		if (function_exists('putenv')) {
			//font residing in VRC_ADMIN_PATH/resources/ (arial.ttf by default)
			putenv('GDFONTPATH=' . realpath(VRC_ADMIN_PATH.DS.'resources'));
			//$font = 'dejavusans'; //i.e. for loading the file dejavusans.ttf or use a custom font
		}
		//
		$gd_available = function_exists('imagecreatefrompng');
		if ($gd_available) {
			$damage_png = imagecreatefrompng($damage_png_path);
			imagesavealpha($damage_png, true);
			imagealphablending($damage_png, true);
			list($damage_png_width, $damage_png_height) = getimagesize($damage_png_path);
			list($cstatus_png_width, $cstatus_png_height) = getimagesize($cstatus_png_path);
		}
		if ($punits > 0) {
			for ($i=1; $i <= $punits; $i++) {
				$distf_name = VikRequest::getVar('feature-name'.$i, array());
				$distf_lang = VikRequest::getVar('feature-lang'.$i, array());
				$distf_value = VikRequest::getVar('feature-value'.$i, array());
				foreach ($distf_name as $distf_k => $distf) {
					if (strlen($distf) > 0 && strlen($distf_value[$distf_k]) > 0 && (!empty($distf) && !empty($distf_value[$distf_k]))) {
						$use_key = strlen($distf_lang[$distf_k]) > 0 ? $distf_lang[$distf_k] : $distf;
						$car_params['features'][$i][$use_key] = $distf_value[$distf_k];
					}
				}
				//damages
				$damage_notes = VikRequest::getVar('car-'.$i.'-damage', array());
				$damage_notes_x = VikRequest::getVar('car-'.$i.'-damage-x', array());
				$damage_notes_y = VikRequest::getVar('car-'.$i.'-damage-y', array());
				$dind = 1;
				foreach ($damage_notes as $dk => $damage) {
					if (!strlen($damage)) {
						continue;
					}
					if (!isset($damage_notes_x[$dk]) || !strlen($damage_notes_x[$dk])) {
						continue;
					}
					if (!isset($damage_notes_y[$dk]) || !strlen($damage_notes_y[$dk])) {
						continue;
					}
					if (!isset($damages[$i])) {
						$damages[$i] = array();
					}
					if (!isset($damages[$i][$dind])) {
						$damages[$i][$dind] = array();
					}
					$damages[$i][$dind]['notes'] = $damage;
					$damages[$i][$dind]['x'] = $damage_notes_x[$dk];
					$damages[$i][$dind]['y'] = $damage_notes_y[$dk];
					$dind++;
				}
				$tot_dmg = isset($damages[$i]) ? count($damages[$i]) : 0;
				if ($tot_dmg > 0 && $gd_available) {
					//generate PNG
					$base_png = imagecreatefrompng($cstatus_png_path);
					imagesavealpha($base_png, true);
					imagealphablending($base_png, true);
					$unit_png = imagecreatetruecolor($cstatus_png_width, $cstatus_png_height);
					$white = imagecolorallocate($unit_png, 255, 255, 255);
					$black = imagecolorallocate($unit_png, 0, 0, 0);
					imagefill($unit_png, 0, 0, $black);
					imagecopy($unit_png, $base_png, 0, 0, 0, 0, $cstatus_png_width, $cstatus_png_height);
					$dk = $tot_dmg;
					foreach ($damages[$i] as $dind => $dmg_point) {
						//damage PNG
						$allocate_x = (int)((int)$dmg_point['x'] - ((int)$damage_png_width / 2));
						$allocate_y = (int)((int)$dmg_point['y'] - ((int)$damage_png_height / 2));
						imagecopy($unit_png, $damage_png, $allocate_x, $allocate_y, 0, 0, $damage_png_width, $damage_png_height);
						if ($damage_show_type > 1) {
							$type_space = imagettfbbox($damage_font_size, 0, $damage_font, (string)$dk);
							$type_width = floor($type_space[4] - $type_space[0]);
							$type_height = floor($type_space[5] - $type_space[1]);
							$allocate_x = ceil((int)$dmg_point['x'] - ((int)$type_width / 2));
							$allocate_y = floor((int)$dmg_point['y'] - ((int)$type_height / 2));
							imagettftext($unit_png, $damage_font_size, 0, $allocate_x, $allocate_y, $white, $damage_font, (string)$dk);
						}
						$dk--;
					}
					imagepng($unit_png, VRC_SITE_PATH.DS.'helpers'.DS.'car_damages'.DS.$pwhereup.'_'.$i.'.png');
					imagedestroy($unit_png);
				} else {
					if (file_exists(VRC_SITE_PATH.DS.'helpers'.DS.'car_damages'.DS.$pwhereup.'_'.$i.'.png')) {
						unlink(VRC_SITE_PATH.DS.'helpers'.DS.'car_damages'.DS.$pwhereup.'_'.$i.'.png');
					}
				}
			}
			if (count($damages) > 0) {
				$car_params['damages'] = $damages;
			}
		}
		//
		$dbo = JFactory::getDbo();
		$q = "UPDATE `#__vikrentcar_cars` SET `name`=".$dbo->quote($pcname).",".(strlen($picon) > 0 ? "`img`='".$picon."'," : "")."`idcat`=".$dbo->quote($pccatdef).",`idcarat`=".$dbo->quote($pccaratdef).",`idopt`=".$dbo->quote($pcoptionaldef).",`info`=".$dbo->quote($pcdescr).",`idplace`=".$dbo->quote($pcplacedef).",`avail`=".$dbo->quote($pcavaildef).",`units`=".($punits > 0 ? $dbo->quote($punits) : "'1'").",`idretplace`=".$dbo->quote($pcretplacedef).",`moreimgs`=".$dbo->quote($moreimagestr).",`startfrom`=".(strlen($pstartfrom) > 0 ? "'".$pstartfrom."'" : "null").",`short_info`=".$dbo->quote($pshort_info).",`params`=".$dbo->quote(json_encode($car_params)).",`alias`=".$dbo->quote($psefalias)." WHERE `id`=".$dbo->quote($pwhereup).";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe->enqueueMessage(JText::_('VRCCARUPDATEOK'));

		/**
		 * Import remote iCal calendars.
		 * 
		 * @since 	1.15.0 (J) - 1.3.0 (WP)
		 */
		$import_calendars = VikRequest::getVar('calendars', array());
		if (empty($import_calendars) || empty($import_calendars['url'])) {
			// make sure to remove any calendar for this car
			$q = "DELETE FROM `#__vikrentcar_cars_icals` WHERE `idcar`=" . (int)$pwhereup . ";";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			// parse all calendars
			foreach ($import_calendars['url'] as $cal_key => $cal_url) {
				$cal_existed = (isset($import_calendars['id']) && !empty($import_calendars['id'][$cal_key]));
				if (empty($cal_url)) {
					if ($cal_existed) {
						$q = "DELETE FROM `#__vikrentcar_cars_icals` WHERE `id`=" . (int)$import_calendars['id'][$cal_key] . ";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
					continue;
				}
				// build record object
				$record = new stdClass;
				$record->idcar = (int)$pwhereup;
				$record->name = isset($import_calendars['name']) && !empty($import_calendars['name'][$cal_key]) ? $import_calendars['name'][$cal_key] : JText::_('VRC_IMPORT_CALENDAR_URL');
				$record->url = $cal_url;
				if ($cal_existed) {
					// update record
					$record->id = (int)$import_calendars['id'][$cal_key];
					$dbo->updateObject('#__vikrentcar_cars_icals', $record, 'id');
				} else {
					// insert object
					$dbo->insertObject('#__vikrentcar_cars_icals', $record, 'id');
				}
			}
		}

		if ($remain === true) {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editcar&cid[]=".$pwhereup);
			exit;
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
	}

	public function clone_car()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}

		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$car_id = VikRequest::getInt('whereup', 0, 'request');

		if (empty($car_id)) {
			$app->redirect("index.php?option=com_vikrentcar&task=cars");
			$app->close();
		}

		$q = "SELECT * FROM `#__vikrentcar_cars` WHERE `id`=" . $car_id;
		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows()) {
			$app->redirect("index.php?option=com_vikrentcar&task=cars");
			$app->close();
		}

		$toclone = $dbo->loadObject();
		unset($toclone->id);
		$toclone->name .= ' (Copy)';

		$dbo->insertObject('#__vikrentcar_cars', $toclone, 'id');

		if (!isset($toclone->id)) {
			$app->redirect("index.php?option=com_vikrentcar&task=cars");
			$app->close();
		}

		$app->redirect("index.php?option=com_vikrentcar&task=editcar&cid[]=" . $toclone->id);
		$app->close();
	}

	public function removecar() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_cars` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentcar_dispcost` WHERE `idcar`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
	}

	public function modavail() {
		$cid = VikRequest::getVar('cid', array(0));
		$car = $cid[0];
		if (!empty($car)) {
			$dbo = JFactory::getDbo();
			$q = "SELECT `avail` FROM `#__vikrentcar_cars` WHERE `id`=".$dbo->quote($car).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$get = $dbo->loadAssocList();
			$q = "UPDATE `#__vikrentcar_cars` SET `avail`='".(intval($get[0]['avail'])==1 ? 0 : 1)."' WHERE `id`=".$dbo->quote($car).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
	}

	public function tariffs() {
		VikRentCarHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'tariffs'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function removetariffs() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		$pcarid = VikRequest::getString('carid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $r) {
				$x=explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikrentcar_dispcost` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=tariffs&cid[]=".$pcarid);
	}

	public function tariffshours() {
		VikRentCarHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'tariffshours'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function removetariffshours() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		$pcarid = VikRequest::getString('carid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $r) {
				$x = explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikrentcar_dispcosthours` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=tariffshours&cid[]=".$pcarid);
	}

	public function hourscharges() {
		VikRentCarHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'hourscharges'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function removehourscharges() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		$pcarid = VikRequest::getString('carid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $r) {
				$x=explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikrentcar_hourscharges` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=hourscharges&cid[]=".$pcarid);
	}

	public function cancel() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
	}

	public function calendar() {
		VikRentCarHelper::printHeader("19");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'calendar'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function cancelcalendar() {
		$pidcar = VikRequest::getString('idcar', '', 'request');
		$preturn = VikRequest::getString('return', '', 'request');
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$mainframe = JFactory::getApplication();
		if ($preturn == 'order' && !empty($pidorder)) {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$pidorder);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=calendar&cid[]=".$pidcar);
		}
	}

	public function goconfig() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=config");
	}

	public function config() {
		VikRentCarHelper::printHeader("11");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'config'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function saveconfig() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}

		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		$pallowrent = VikRequest::getString('allowrent', '', 'request');
		$pdisabledrentmsg = VikRequest::getString('disabledrentmsg', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptimeopenstorealw = VikRequest::getString('timeopenstorealw', '', 'request');
		$ptimeopenstorefh = VikRequest::getString('timeopenstorefh', '', 'request');
		$ptimeopenstorefm = VikRequest::getString('timeopenstorefm', '', 'request');
		$ptimeopenstoreth = VikRequest::getString('timeopenstoreth', '', 'request');
		$ptimeopenstoretm = VikRequest::getString('timeopenstoretm', '', 'request');
		$phoursmorerentback = VikRequest::getString('hoursmorerentback', '', 'request');
		$phoursmorecaravail = VikRequest::getString('hoursmorecaravail', '', 'request');
		$pplacesfront = VikRequest::getString('placesfront', '', 'request');
		$pdateformat = VikRequest::getString('dateformat', '', 'request');
		$ptimeformat = VikRequest::getString('timeformat', '', 'request');
		$pshowcategories = VikRequest::getString('showcategories', '', 'request');
		$pcharatsfilter = VikRequest::getString('charatsfilter', '', 'request');
		$pcharatsfilter = $pcharatsfilter == 'yes' ? 1 : 0;
		$pdamageshowtype = VikRequest::getInt('damageshowtype', '', 'request');
		$pdamageshowtype = $pdamageshowtype > 0 && $pdamageshowtype < 4 ? $pdamageshowtype : 1;
		$ptokenform = VikRequest::getString('tokenform', '', 'request');
		$padminemail = VikRequest::getString('adminemail', '', 'request');
		$psenderemail = VikRequest::getString('senderemail', '', 'request');
		$picalkey = VikRequest::getString('icalkey', '', 'request');
		$picalkey = str_replace(' ', '', $picalkey);
		$pminuteslock = VikRequest::getString('minuteslock', '', 'request');
		$pfooterordmail = VikRequest::getString('footerordmail', '', 'request', VIKREQUEST_ALLOWHTML);
		$prequirelogin = VikRequest::getString('requirelogin', '', 'request');
		$pusefa = VikRequest::getInt('usefa', '', 'request');
		$pusefa = $pusefa > 0 ? 1 : 0;
		$ploadjquery = VikRequest::getString('loadjquery', '', 'request');
		$ploadjquery = $ploadjquery == "yes" ? "1" : "0";
		$pcalendar = VikRequest::getString('calendar', '', 'request');
		$pcalendar = $pcalendar == "joomla" ? "joomla" : "jqueryui";
		$pehourschbasp = VikRequest::getString('ehourschbasp', '', 'request');
		$pehourschbasp = $pehourschbasp == "1" ? 1 : 0;
		$penablecoupons = VikRequest::getString('enablecoupons', '', 'request');
		$penablecoupons = $penablecoupons == "1" ? 1 : 0;
		$penablepin = VikRequest::getInt('enablepin', 0, 'request');
		$penablepin = $penablepin > 0 ? 1 : 0;
		$ptodaybookings = VikRequest::getString('todaybookings', '', 'request');
		$ptodaybookings = $ptodaybookings == "1" ? 1 : 0;
		$ppickondrop = VikRequest::getInt('pickondrop', '', 'request');
		$ppickondrop = $ppickondrop === 1 ? 1 : 0;
		$psetdropdplus = VikRequest::getString('setdropdplus', '', 'request');
		$psetdropdplus = !empty($psetdropdplus) ? intval($psetdropdplus) : '';
		$pmindaysadvance = VikRequest::getInt('mindaysadvance', '', 'request');
		$pmindaysadvance = $pmindaysadvance < 0 ? 0 : $pmindaysadvance;
		$pmaxdate = VikRequest::getString('maxdate', '', 'request');
		$pmaxdate = intval($pmaxdate) < 1 ? 2 : $pmaxdate;
		$pmaxdateinterval = VikRequest::getString('maxdateinterval', '', 'request');
		$pmaxdateinterval = !in_array($pmaxdateinterval, array('d', 'w', 'm', 'y')) ? 'y' : $pmaxdateinterval;
		$maxdate_str = '+'.$pmaxdate.$pmaxdateinterval;
		$pvrcsef = VikRequest::getInt('vrcsef', '', 'request');
		$vrcsef = file_exists(VRC_SITE_PATH.DS.'router.php');
		if ($pvrcsef === 1) {
			if (!$vrcsef) {
				rename(VRC_SITE_PATH.DS.'_router.php', VRC_SITE_PATH.DS.'router.php');
			}
		} else {
			if ($vrcsef) {
				rename(VRC_SITE_PATH.DS.'router.php', VRC_SITE_PATH.DS.'_router.php');
			}
		}
		$pcronkey = VikRequest::getString('cronkey', '', 'request');
		$pmultilang = VikRequest::getString('multilang', '', 'request');
		$pmultilang = $pmultilang == "1" ? 1 : 0;
		$res_backend_path = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
		$picon="";
		if (intval($_FILES['sitelogo']['error']) == 0 && trim($_FILES['sitelogo']['name'])!="") {
			jimport('joomla.filesystem.file');
			if (@is_uploaded_file($_FILES['sitelogo']['tmp_name'])) {
				$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['sitelogo']['name'])));
				if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
					$j=1;
					while (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
						$j++;
					}
					$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
				} else {
					$j="";
					$pwhere=VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
				}
				VikRentCar::uploadFile($_FILES['sitelogo']['tmp_name'], $pwhere);
				if (!getimagesize($pwhere)) {
					@unlink($pwhere);
					$picon="";
				} else {
					@chmod($pwhere, 0644);
					$picon=$j.$safename;
				}
			}
			if (!empty($picon)) {
				$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($picon)." WHERE `param`='sitelogo';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$pbackicon = "";
		if (intval($_FILES['backlogo']['error']) == 0 && trim($_FILES['backlogo']['name'])!="") {
			jimport('joomla.filesystem.file');
			if (@is_uploaded_file($_FILES['backlogo']['tmp_name'])) {
				$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['backlogo']['name'])));
				if (file_exists($res_backend_path.$safename)) {
					$j=1;
					while (file_exists($res_backend_path.$j.$safename)) {
						$j++;
					}
					$pwhere=$res_backend_path.$j.$safename;
				} else {
					$j="";
					$pwhere=$res_backend_path.$safename;
				}
				if (!getimagesize($_FILES['backlogo']['tmp_name'])) {
					@unlink($pwhere);
					$pbackicon="";
				} else {
					VikRentCar::uploadFile($_FILES['backlogo']['tmp_name'], $pwhere);
					@chmod($pwhere, 0644);
					$pbackicon=$j.$safename;
				}
			}
			if (!empty($pbackicon)) {
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='backlogo';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pbackicon)." WHERE `param`='backlogo';";
					$dbo->setQuery($q);
					$dbo->execute();
				} else {
					$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('backlogo',".$dbo->quote($pbackicon).");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		if (empty($pallowrent) || $pallowrent != "1") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='allowrent';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='allowrent';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pplacesfront) || $pplacesfront != "yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='placesfront';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='placesfront';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowcategories) || $pshowcategories != "yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='showcategories';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='showcategories';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$pcharatsfilter." WHERE `param`='charatsfilter';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$pdamageshowtype." WHERE `param`='damageshowtype';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($ptokenform) || $ptokenform != "yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='tokenform';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='tokenform';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pfooterordmail)." WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pdisabledrentmsg)." WHERE `param`='disabledrentmsg';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($padminemail)." WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		//Sender email address
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='senderemail' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($psenderemail)." WHERE `param`='senderemail';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('senderemail',".$dbo->quote($psenderemail).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($picalkey)." WHERE `param`='icalkey';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$pmultilang."' WHERE `param`='multilang';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pdateformat)) {
			$pdateformat="%d/%m/%Y";
		}
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pdateformat)." WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$session->set('getDateFormat', '');
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($ptimeformat)." WHERE `param`='timeformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pminuteslock)." WHERE `param`='minuteslock';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!empty($ptimeopenstorealw)) {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='' WHERE `param`='timeopenstore';";
		} else {
			$openingh=$ptimeopenstorefh * 3600;
			$openingm=$ptimeopenstorefm * 60;
			$openingts=$openingh + $openingm;
			$closingh=$ptimeopenstoreth * 3600;
			$closingm=$ptimeopenstoretm * 60;
			$closingts=$closingh + $closingm;
			if ($closingts <= $openingts) {
				$q = "UPDATE `#__vikrentcar_config` SET `setting`='' WHERE `param`='timeopenstore';";
			} else {
				$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$openingts."-".$closingts."' WHERE `param`='timeopenstore';";
			}
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (!ctype_digit($phoursmorerentback)) {
			$phoursmorerentback="0";
		}
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$phoursmorerentback."' WHERE `param`='hoursmorerentback';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!ctype_digit($phoursmorecaravail)) {
			$phoursmorecaravail="0";
		}
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$phoursmorecaravail."' WHERE `param`='hoursmorecaravail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".($prequirelogin == "1" ? "1" : "0")."' WHERE `param`='requirelogin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".(string)$pusefa."' WHERE `param`='usefa';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$ploadjquery."' WHERE `param`='loadjquery';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$pcalendar."' WHERE `param`='calendar';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$pehourschbasp."' WHERE `param`='ehourschbasp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$penablecoupons."' WHERE `param`='enablecoupons';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$penablepin."' WHERE `param`='enablepin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$ptodaybookings."' WHERE `param`='todaybookings';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".(string)$ppickondrop."' WHERE `param`='pickondrop';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$psetdropdplus."' WHERE `param`='setdropdplus';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$pmindaysadvance."' WHERE `param`='mindaysadvance';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$maxdate_str."' WHERE `param`='maxdate';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pcronkey)." WHERE `param`='cronkey';";
		$dbo->setQuery($q);
		$dbo->execute();

		/**
		 * Toggle the loading of the Bootstrap assets on any site section.
		 * 
		 * @since 	1.1.4
		 */
		$pbootstrap = VikRequest::getInt('bootstrap', 0, 'request');
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pbootstrap)." WHERE `param`='bootstrap';";
		$dbo->setQuery($q);
		$dbo->execute();

		// preferred countries ordering, or custom countries.
		$pref_countries = VikRequest::getVar('pref_countries', array());
		$cust_pref_countries = VikRequest::getString('cust_pref_countries', '', 'request');
		$pref_countries = !is_array($pref_countries) || empty($pref_countries[0]) ? VikRentCar::preferredCountriesOrdering() : $pref_countries;
		if (!empty($cust_pref_countries)) {
			$all_custom_prefcountries = array();
			$cust_pref_countries = explode(',', $cust_pref_countries);
			foreach ($cust_pref_countries as $cust_pref_country) {
				$cust_pref_country = trim(strtolower($cust_pref_country));
				if (empty($cust_pref_country) || strlen($cust_pref_country) != 2) {
					continue;
				}
				array_push($all_custom_prefcountries, $cust_pref_country);
			}
			if (count($all_custom_prefcountries)) {
				$pref_countries = $all_custom_prefcountries;
			}
		}
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=" . $dbo->quote(json_encode($pref_countries)) . " WHERE `param`='preferred_countries';";
		$dbo->setQuery($q);
		$dbo->execute();

		$psearchsuggestions = VikRequest::getInt('searchsuggestions', 0, 'request');
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($psearchsuggestions)." WHERE `param`='searchsuggestions';";
		$dbo->setQuery($q);
		$dbo->execute();
		$pmultipay = VikRequest::getInt('multipay', 0, 'request');
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pmultipay)." WHERE `param`='multipay';";
		$dbo->setQuery($q);
		$dbo->execute();
		$pdocsupload = VikRequest::getInt('docsupload', 0, 'request');
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pdocsupload)." WHERE `param`='docsupload';";
		$dbo->setQuery($q);
		$dbo->execute();
		$pdocsuploadinstr = VikRequest::getString('docsuploadinstr', '', 'request', VIKREQUEST_ALLOWHTML);
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pdocsuploadinstr)." WHERE `param`='docsuploadinstr';";
		$dbo->setQuery($q);
		$dbo->execute();

		$pref_textcolor = VikRequest::getString('pref_textcolor', '', 'request');
		$pref_bgcolor = VikRequest::getString('pref_bgcolor', '', 'request');
		$pref_fontcolor = VikRequest::getString('pref_fontcolor', '', 'request');
		$pref_bgcolorhov = VikRequest::getString('pref_bgcolorhov', '', 'request');
		$pref_fontcolorhov = VikRequest::getString('pref_fontcolorhov', '', 'request');
		$pref_colors = array(
			'textcolor' => $pref_textcolor,
			'bgcolor' => $pref_bgcolor,
			'fontcolor' => $pref_fontcolor,
			'bgcolorhov' => $pref_bgcolorhov,
			'fontcolorhov' => $pref_fontcolorhov,
		);
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote(json_encode($pref_colors))." WHERE `param`='pref_colors';";
		$dbo->setQuery($q);
		$dbo->execute();

		$pfronttitle = VikRequest::getString('fronttitle', '', 'request');
		$pshowfooter = VikRequest::getString('showfooter', '', 'request');
		$pintromain = VikRequest::getString('intromain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pclosingmain = VikRequest::getString('closingmain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencyname = VikRequest::getString('currencyname', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencysymb = VikRequest::getString('currencysymb', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencycodepp = VikRequest::getString('currencycodepp', '', 'request');
		$pnumdecimals = VikRequest::getString('numdecimals', '', 'request');
		$pnumdecimals = intval($pnumdecimals);
		$pdecseparator = VikRequest::getString('decseparator', '', 'request');
		$pdecseparator = empty($pdecseparator) ? '.' : $pdecseparator;
		$pthoseparator = VikRequest::getString('thoseparator', '', 'request');
		$numberformatstr = $pnumdecimals.':'.$pdecseparator.':'.$pthoseparator;
		$pshowpartlyreserved = VikRequest::getString('showpartlyreserved', '', 'request');
		$pshowpartlyreserved = $pshowpartlyreserved == "yes" ? 1 : 0;
		$pnumcalendars = VikRequest::getInt('numcalendars', '', 'request');
		$pnumcalendars = $pnumcalendars > -1 ? $pnumcalendars : 3;
		$pthumbswidth = VikRequest::getInt('thumbswidth', '', 'request');
		$pthumbswidth = $pthumbswidth > 0 ? $pthumbswidth : 100;
		$pfirstwday = VikRequest::getString('firstwday', '', 'request');
		$pfirstwday = intval($pfirstwday) >= 0 && intval($pfirstwday) <= 6 ? $pfirstwday : '0';
		//Google Maps API Key
		$pgmapskey = VikRequest::getString('gmapskey', '', 'request');
		$q = "SELECT * FROM `#__vikrentcar_config` WHERE `param`='gmapskey';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pgmapskey)." WHERE `param`='gmapskey';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('gmapskey', ".$dbo->quote($pgmapskey).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		// Ipinfo.io API Token
		$pipinfo_token = VikRequest::getString('ipinfo_token', '', 'request');
		$q = "SELECT * FROM `#__vikrentcar_config` WHERE `param`='ipinfo_token';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pipinfo_token)." WHERE `param`='ipinfo_token';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('ipinfo_token', ".$dbo->quote($pipinfo_token).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//theme
		$ptheme = VikRequest::getString('theme', '', 'request');
		if (empty($ptheme) || $ptheme == 'default') {
			$ptheme = 'default';
		} else {
			$validtheme = false;
			$themes = glob(VRC_SITE_PATH.DS.'themes'.DS.'*');
			if (count($themes) > 0) {
				$strip = VRC_SITE_PATH.DS.'themes'.DS;
				foreach ($themes as $th) {
					if (is_dir($th)) {
						$tname = str_replace($strip, '', $th);
						if ($tname == $ptheme) {
							$validtheme = true;
							break;
						}
					}
				}
			}
			if ($validtheme == false) {
				$ptheme = 'default';
			}
		}
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($ptheme)." WHERE `param`='theme';";
		$dbo->setQuery($q);
		$dbo->execute();
		//
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pshowpartlyreserved)." WHERE `param`='showpartlyreserved';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pnumcalendars)." WHERE `param`='numcalendars';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pthumbswidth)." WHERE `param`='thumbswidth';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pfirstwday)." WHERE `param`='firstwday';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pfronttitle)." WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowfooter) || $pshowfooter != "yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='showfooter';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='showfooter';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pintromain)." WHERE `param`='intromain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pclosingmain)." WHERE `param`='closingmain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pcurrencyname)." WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pcurrencysymb)." WHERE `param`='currencysymb';";
		$dbo->setQuery($q);
		$dbo->execute();
		$session->set('getCurrencySymb', '');
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pcurrencycodepp)." WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($numberformatstr)." WHERE `param`='numberformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$pivainclusa = VikRequest::getString('ivainclusa', '', 'request');
		$ptaxsummary = VikRequest::getString('taxsummary', '', 'request');
		$ptaxsummary = empty($ptaxsummary) || $ptaxsummary != "yes" ? "0" : "1";
		$pccpaypal = VikRequest::getString('ccpaypal', '', 'request');
		$ppaytotal = VikRequest::getString('paytotal', '', 'request');
		$ppayaccpercent = VikRequest::getString('payaccpercent', '', 'request');
		$ptypedeposit = VikRequest::getString('typedeposit', '', 'request');
		$ptypedeposit = $ptypedeposit == 'fixed' ? 'fixed' : 'pcent';
		$ppaymentname = VikRequest::getString('paymentname', '', 'request');
		if (empty($pivainclusa) || $pivainclusa != "yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='ivainclusa';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='ivainclusa';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='".$ptaxsummary."' WHERE `param`='taxsummary';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($ppaytotal) || $ppaytotal != "yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='paytotal';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='paytotal';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pccpaypal)." WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($ppaymentname)." WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($ppayaccpercent)." WHERE `param`='payaccpercent';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($ptypedeposit)." WHERE `param`='typedeposit';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$psendpdf = VikRequest::getString('sendpdf', '', 'request');
		$pdisclaimer = VikRequest::getString('disclaimer', '', 'request', VIKREQUEST_ALLOWHTML);
		//Deprecated and Removed since VRC 1.11
		/*
		$poldorders = VikRequest::getString('oldorders', '', 'request');
		if (empty($poldorders) || $poldorders != "yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='oldorders';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='oldorders';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		*/
		//
		
		/**
		 * @deprecated 	1.12 - configuration setting no longer used 
		 * 
		if (empty($psendjutility) || $psendjutility != "yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='sendjutility';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='sendjutility';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		*/

		if (empty($psendpdf) || $psendpdf != "yes") {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='sendpdf';";
		} else {
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='1' WHERE `param`='sendpdf';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$psendemailwhen = VikRequest::getInt('sendemailwhen', '', 'request');
		$psendemailwhen = $psendemailwhen > 1 ? 2 : 1;
		$pattachical = VikRequest::getInt('attachical', 0, 'request');
		$pattachical = $pattachical >= 0 && $pattachical <= 3 ? $pattachical : 1;
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($psendemailwhen)." WHERE `param`='emailsendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pattachical)." WHERE `param`='attachical';";
		$dbo->setQuery($q);
		$dbo->execute();

		$picalendtype = VikRequest::getString('icalendtype', '', 'request');
		$picalendtype = $picalendtype == 'pick' ? 'pick' : 'drop';
		$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($picalendtype)." WHERE `param`='icalendtype';";
		$dbo->setQuery($q);
		$dbo->execute();

		$q = "UPDATE `#__vikrentcar_texts` SET `setting`=".$dbo->quote($pdisclaimer)." WHERE `param`='disclaimer';";
		$dbo->setQuery($q);
		$dbo->execute();

		/**
		 * Backup settings
		 * 
		 * @since 	1.15.0 (J) - 1.3.0 (WP)
		 */
		$config = VRCFactory::getConfig();

		$backup_type   = $app->input->getString('backuptype', 'full');
		$backup_folder = $app->input->getString('backupfolder', '');

		$tmp = $app->get('tmp_path');

		if (!$backup_folder)
		{
			// path not specified, use temporary folder
			$backup_folder = $tmp;
		}

		$current = $config->get('backupfolder');

		if (!$current)
		{
			// path was missing, use temporary folder
			$current = $tmp;
		}

		// check whether the backup folder has been moved
		if ($current && $backup_folder && rtrim($current, DIRECTORY_SEPARATOR) !== rtrim($backup_folder, DIRECTORY_SEPARATOR))
		{
			$backupModel = new VRCModelBackup();

			// backup folder moved, try to copy all the existing overrides
			if (!$backupModel->moveArchives($backup_folder))
			{
				// iterate all errors and display them
				foreach ($backupModel->getErrors() as $error)
				{
					$app->enqueueMessage($error, 'warning');
				}
			}
		}

		// save configuration
		$config->set('backuptype', $backup_type);
		$config->set('backupfolder', $backup_folder);

		// forced pickup/drop off times
		$forcedtimes 	= $app->input->getInt('forcedtimes', 0);
		$forced_pickup  = $forcedtimes ? $app->input->getString('forced_pickup', '') : '';
		$forced_dropoff = $forcedtimes ? $app->input->getString('forced_dropoff', '') : '';

		$config->set('forced_pickup', $forced_pickup);
		$config->set('forced_dropoff', $forced_dropoff);

		// auto-assign car unit
		$config->set('autocarunit', $app->input->getInt('autocarunit', 0));

		$app->enqueueMessage(JText::_('VRSETTINGSAVED'));
		$app->redirect("index.php?option=com_vikrentcar&task=config");
	}

	public function renewsession() {
		/*
		 * @wponly
		 * We just destroy the session
		 */
		JSessionHandler::destroy();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=config");
	}

	public function trackings() {
		VikRentCarHelper::printHeader("trackings");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'trackings'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function trkconfig() {
		VikRentCarHelper::printHeader("trackings");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'trkconfig'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function savetrkconfigstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_savetrkconfig(true);
	}

	public function savetrkconfig() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_savetrkconfig();
	}

	private function do_savetrkconfig($stay = false)
	{
		$config = VRCFactory::getConfig();

		$trkenabled = VikRequest::getInt('trkenabled', 0, 'request');
		$trkenabled = $trkenabled == 1 ? 1 : 0;
		$trkcookierfrdur = VikRequest::getFloat('trkcookierfrdur', 1, 'request');
		$trkcookierfrdur = $trkcookierfrdur < 0.1 ? 1 : $trkcookierfrdur;
		$trkcampname = VikRequest::getVar('trkcampname', []);
		$trkcampkey = VikRequest::getVar('trkcampkey', []);
		$trkcampval = VikRequest::getVar('trkcampval', []);
		$trkcampaigns = [];
		foreach ($trkcampname as $k => $v) {
			if (empty($trkcampkey[$k])) {
				continue;
			}
			$trkcampkey[$k] = str_replace(' ', '', trim($trkcampkey[$k]));
			$name = !empty($v) ? $v : date('Y-m-d').' '.(count($trkcampaigns) + 1);
			$trkcampaigns[$trkcampkey[$k]] = [
				'key' => $trkcampkey[$k],
				'value' => $trkcampval[$k],
				'name' => $name,
			];
		}

		$config->set('trkenabled', $trkenabled);
		$config->set('trkcookierfrdur', $trkcookierfrdur);
		$config->set('trkcampaigns', json_encode($trkcampaigns));

		$measurment_driver = VikRequest::getString('measurment_driver', '', 'request');
		$measurment_params = [];
		$vikparams = VikRequest::getVar('vikparams', []);
		foreach ($vikparams as $setting => $cont) {
			if (strlen($setting) > 0) {
				$measurment_params[$setting] = $cont;
			}
		}

		$config->set('measurment_driver', $measurment_driver);
		$config->set('measurment_params', json_encode($measurment_params));

		$app = JFactory::getApplication();
		$app->redirect("index.php?option=com_vikrentcar&task=".($stay ? 'trkconfig' : 'trackings'));
	}

	public function modtracking() {
		$dbo = JFactory::getDbo();
		$cid = VikRequest::getVar('cid', array());
		foreach ($cid as $id) {
			if (!empty($id)) {
				$q = "SELECT `id`,`published` FROM `#__vikrentcar_trackings` WHERE `id`=".(int)$id.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$data = $dbo->loadAssoc();
					$q = "UPDATE `#__vikrentcar_trackings` SET `published`=".($data['published'] ? '0' : '1')." WHERE `id`=".(int)$data['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=trackings");
	}

	public function removetrackings() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array());
		if (count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikrentcar_trackings` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentcar_tracking_infos` WHERE `idtracking`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=trackings");
	}

	/**
	 * Invokes the Tracker class to obtain
	 * geo information about the IP addresses.
	 * This task is called via ajax.
	 *
	 * @since 	1.11
	 */
	public function getgeoinfo() {
		$ips = VikRequest::getVar('ips', array());
		if (!count($ips)) {
			echo 'e4j.error.empty IPs';
			exit;
		}

		// require the Tracker class without instantiating the object
		VikRentCar::getTracker(true);
		$geo_info = VikRentCarTracker::getIpGeoInfo($ips);

		if ($geo_info === false) {
			echo 'e4j.error.Tracker error, could not get geo info from IPs';
			exit;
		}

		if (is_string($geo_info)) {
			echo 'e4j.error.' . JHtml::_('esc_html', $geo_info);
			exit;
		}

		// update db values and compose response
		$dbo = JFactory::getDbo();
		$resp = array();
		foreach ($geo_info as $id => $geo) {
			if (is_null($geo) || $geo === false) {
				continue;
			}
			// compose geo info string
			$geovals = array();
			if (!empty($geo['city'])) {
				array_push($geovals, $geo['city']);
			}
			if (!empty($geo['region'])) {
				array_push($geovals, $geo['region']);
			}
			$threecode = '';
			$cname = '';
			if (!empty($geo['country'])) {
				// returned country is a 2-char code, get the 3-char country code
				$q = "SELECT `country_3_code`,`country_name` FROM `#__vikrentcar_countries` WHERE `country_2_code`=".$dbo->quote($geo['country']).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$cinfo = $dbo->loadAssoc();
					$threecode = $cinfo['country_3_code'];
					$cname = $cinfo['country_name'];
				}
				array_push($geovals, (empty($cname) ? $geo['country'] : $cname));
			}

			// full geo information string
			$geoinfostr = implode(', ', $geovals);

			// push data to the response pool
			$resp[$id] = array();
			$resp[$id]['geo'] = $geoinfostr;
			if (!empty($cname)) {
				$resp[$id]['country'] = $cname;
			}
			if (!empty($threecode)) {
				$resp[$id]['country3'] = $threecode;
			}

			// update main tracking record
			$q = "UPDATE `#__vikrentcar_trackings` SET `geo`=".$dbo->quote($geoinfostr).(!empty($threecode) ? ', `country`='.$dbo->quote($threecode) : '')." WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		// output the JSON response
		echo json_encode($resp);
		exit;
	}

	public function locfees() {
		VikRentCarHelper::printHeader("12");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'locfees'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newlocfee() {
		VikRentCarHelper::printHeader("12");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managelocfee'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editlocfee() {
		VikRentCarHelper::printHeader("12");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managelocfee'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createlocfee() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$mainframe = JFactory::getApplication();
		$pfrom = VikRequest::getInt('from', 0, 'request');
		$pto = VikRequest::getInt('to', 0, 'request');
		$pcost = VikRequest::getFloat('cost', 0, 'request');
		$pdaily = VikRequest::getInt('daily', 0, 'request');
		$paliq = VikRequest::getInt('aliq', 0, 'request');
		$pinvert = VikRequest::getInt('invert', 0, 'request');
		$pany_oneway = VikRequest::getInt('any_oneway', 0, 'request');
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());

		$dbo = JFactory::getDbo();
		if ((!empty($pfrom) && !empty($pto)) || !empty($pany_oneway)) {
			$losverridestr = "";
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$losverridestr .= (int)$no.':'.floatval($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			$q = "INSERT INTO `#__vikrentcar_locfees` (`from`,`to`,`daily`,`cost`,`idiva`,`invert`,`losoverride`,`any_oneway`) VALUES(".$dbo->quote($pfrom).", ".$dbo->quote($pto).", ".$pdaily.", ".$dbo->quote($pcost).", ".$dbo->quote($paliq).", '".$pinvert."', '".$losverridestr."', " . $pany_oneway . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRLOCFEESAVED'));
		}

		$mainframe->redirect("index.php?option=com_vikrentcar&task=locfees");
	}

	public function updatelocfee() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pfrom = VikRequest::getInt('from', 0, 'request');
		$pto = VikRequest::getInt('to', 0, 'request');
		$pcost = VikRequest::getFloat('cost', 0, 'request');
		$pdaily = VikRequest::getInt('daily', 0, 'request');
		$paliq = VikRequest::getInt('aliq', 0, 'request');
		$pinvert = VikRequest::getInt('invert', 0, 'request');
		$pany_oneway = VikRequest::getInt('any_oneway', 0, 'request');
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());

		$dbo = JFactory::getDbo();
		if (!empty($pwhere) && ((!empty($pfrom) && !empty($pto)) || !empty($pany_oneway))) {
			$losverridestr = "";
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$losverridestr .= (int)$no.':'.floatval($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			$q = "UPDATE `#__vikrentcar_locfees` SET `from`=".$dbo->quote($pfrom).",`to`=".$dbo->quote($pto).",`daily`=".$pdaily.",`cost`=".$dbo->quote($pcost).",`idiva`=".$dbo->quote($paliq).",`invert`='".$pinvert."',`losoverride`='".$losverridestr."',`any_oneway`=" . $pany_oneway . " WHERE `id`=".$dbo->quote($pwhere).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRLOCFEEUPDATE'));
		}

		$mainframe->redirect("index.php?option=com_vikrentcar&task=locfees");
	}

	public function removelocfee() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_locfees` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=locfees");
	}

	public function cancellocfee() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=locfees");
	}

	public function seasons() {
		VikRentCarHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'seasons'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newseason() {
		VikRentCarHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editseason() {
		VikRentCarHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createseason() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_createseason();
	}

	public function createseason_new() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_createseason(true);
	}

	private function do_createseason($andnew = false) {
		$mainframe = JFactory::getApplication();
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getString('diffcost', '', 'request');
		$pidlocation = VikRequest::getInt('idlocation', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$ppickupincl = VikRequest::getString('pickupincl', '', 'request');
		$ppickupincl = $ppickupincl == 1 ? 1 : 0;
		$pkeepfirstdayrate = VikRequest::getString('keepfirstdayrate', '', 'request');
		$pkeepfirstdayrate = $pkeepfirstdayrate == 1 ? 1 : 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$ppromo = VikRequest::getInt('promo', '', 'request');
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$ppromominlos = VikRequest::getInt('promominlos', '', 'request');
		$ppromolastmind = VikRequest::getInt('promolastmind', 0, 'request');
		$ppromolastminh = VikRequest::getInt('promolastminh', 0, 'request');
		$promolastmin = ($ppromolastmind * 86400) + ($ppromolastminh * 3600);
		$ppromofinalprice = VikRequest::getInt('promofinalprice', 0, 'request');
		$ppromofinalprice = $ppromo ? $ppromofinalprice : 0;
		$losverridestr = "";
		$dbo = JFactory::getDbo();
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$carstr="";
			if (@count($pidcars) > 0) {
				foreach ($pidcars as $car) {
					$carstr.="-".$car."-,";
				}
			}
			$pricestr="";
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr.="-".$price."-,";
				}
			}
			$valid = true;
			$double_records = array();
			$sfrom = null;
			$sto = null;
			// value overrides
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
						$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			//
			if (!$skipseason) {
				$first = VikRentCar::getDateTimestamp($pfrom, 0, 0);
				$second = VikRentCar::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone = getdate($first);
					$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom = $baseone[0] - $basets;
					$basetwo = getdate($second);
					$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto = $basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] > $leapts) {
							$sfrom -= 86400;
							/**
							 * To avoid issue with leap years and dates near Feb 29th, we only reduce the seconds if these were reduced
							 * for the from-date of the seasons. Doing it just for the to-date in 2019 for 2020 (leap) produced invalid results.
							 * 
							 * @since 	July 2nd 2019
							 */
							if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
								$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
								if ($basetwo[0] > $leapts) {
									$sto -= 86400;
								}
							}
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					//check if seasons dates are valid
					$q = "SELECT `id`,`spname` FROM `#__vikrentcar_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>".$dbo->quote($sfrom)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst = $dbo->getNumRows();
					if ($totfirst > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikrentcar_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond = $dbo->getNumRows();
					if ($totsecond > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikrentcar_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird = $dbo->getNumRows();
					if ($totthird > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					//
				} else {
					VikError::raiseWarning('', JText::_('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikrentcar&task=newseason");
				}
			}
			if ($valid || $ppromo === 1) {
				$q = "INSERT INTO `#__vikrentcar_seasons` (`type`,`from`,`to`,`diffcost`,`idcars`,`locations`,`spname`,`wdays`,`pickupincl`,`val_pcent`,`losoverride`,`keepfirstdayrate`,`roundmode`,`year`,`idprices`,`promo`,`promodaysadv`,`promotxt`,`promominlos`,`promolastmin`,`promofinalprice`) VALUES('".($ptype == "1" ? "1" : "2")."', ".$dbo->quote($sfrom).", ".$dbo->quote($sto).", ".$dbo->quote($pdiffcost).", ".$dbo->quote($carstr).", ".$dbo->quote($pidlocation).", ".$dbo->quote($pspname).", ".$dbo->quote($wdaystr).", '".$ppickupincl."', '".$pval_pcent."', ".$dbo->quote($losverridestr).", '".$pkeepfirstdayrate."', ".(!empty($proundmode) ? "'".$proundmode."'" : "null").", ".($pyeartied == 1 ? $tieyear : "NULL").", ".$dbo->quote($pricestr).", ".($ppromo == 1 ? '1' : '0').", ".(!empty($ppromodaysadv) ? $ppromodaysadv : "null").", ".$dbo->quote($ppromotxt).", ".(!empty($ppromominlos) ? $ppromominlos : "0").", ".(int)$promolastmin.", {$ppromofinalprice});";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRSEASONSAVED'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=".($andnew ? 'newseason' : 'seasons'));
			} else {
				VikError::raiseWarning('', JText::_('ERRINVDATECARSLOCSEASON').(count($double_records) > 0 ? ' ('.implode(', ', $double_records).')' : ''));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=newseason");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=newseason");
		}
	}

	public function updateseason() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_updateseason();
	}

	public function updateseasonstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_updateseason(true);
	}

	public function do_updateseason($stay = false) {
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getString('diffcost', '', 'request');
		$pidlocation = VikRequest::getInt('idlocation', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$ppickupincl = VikRequest::getString('pickupincl', '', 'request');
		$ppickupincl = $ppickupincl == 1 ? 1 : 0;
		$pkeepfirstdayrate = VikRequest::getString('keepfirstdayrate', '', 'request');
		$pkeepfirstdayrate = $pkeepfirstdayrate == 1 ? 1 : 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$ppromo = VikRequest::getInt('promo', '', 'request');
		$ppromo = $ppromo == 1 ? 1 : 0;
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$ppromominlos = VikRequest::getInt('promominlos', '', 'request');
		$ppromolastmind = VikRequest::getInt('promolastmind', 0, 'request');
		$ppromolastminh = VikRequest::getInt('promolastminh', 0, 'request');
		$promolastmin = ($ppromolastmind * 86400) + ($ppromolastminh * 3600);
		$ppromofinalprice = VikRequest::getInt('promofinalprice', 0, 'request');
		$ppromofinalprice = $ppromo ? $ppromofinalprice : 0;
		$losverridestr = "";
		$dbo = JFactory::getDbo();
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$carstr="";
			if (@count($pidcars) > 0) {
				foreach ($pidcars as $car) {
					$carstr.="-".$car."-,";
				}
			}
			$pricestr="";
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr.="-".$price."-,";
				}
			}
			$valid = true;
			$double_records = array();
			$sfrom = null;
			$sto = null;
			// value overrides
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
						$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			//
			if (!$skipseason) {
				$first = VikRentCar::getDateTimestamp($pfrom, 0, 0);
				$second = VikRentCar::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone = getdate($first);
					$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom = $baseone[0] - $basets;
					$basetwo = getdate($second);
					$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto = $basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] > $leapts) {
							$sfrom -= 86400;
							/**
							 * To avoid issue with leap years and dates near Feb 29th, we only reduce the seconds if these were reduced
							 * for the from-date of the seasons. Doing it just for the to-date in 2019 for 2020 (leap) produced invalid results.
							 * 
							 * @since 	July 2nd 2019
							 */
							if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
								$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
								if ($basetwo[0] > $leapts) {
									$sto -= 86400;
								}
							}
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					//check if seasons dates are valid
					$q = "SELECT `id`,`spname` FROM `#__vikrentcar_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>=".$dbo->quote($sfrom)." AND `id`!=".$dbo->quote($pwhere)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst = $dbo->getNumRows();
					if ($totfirst > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikrentcar_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond = $dbo->getNumRows();
					if ($totsecond > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikrentcar_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `idcars`=".$dbo->quote($carstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird = $dbo->getNumRows();
					if ($totthird > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					//
				} else {
					VikError::raiseWarning('', JText::_('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editseason&cid[]=".$pwhere);
				}
			}
			if ($valid) {
				$q = "UPDATE `#__vikrentcar_seasons` SET `type`='".($ptype == "1" ? "1" : "2")."',`from`=".$dbo->quote($sfrom).",`to`=".$dbo->quote($sto).",`diffcost`=".$dbo->quote($pdiffcost).",`idcars`=".$dbo->quote($carstr).",`locations`=".$dbo->quote($pidlocation).",`spname`=".$dbo->quote($pspname).",`wdays`='".$wdaystr."',`pickupincl`='".$ppickupincl."',`val_pcent`='".$pval_pcent."',`losoverride`=".$dbo->quote($losverridestr).",`keepfirstdayrate`='".$pkeepfirstdayrate."',`roundmode`=".(!empty($proundmode) ? "'".$proundmode."'" : "null").",`year`=".($pyeartied == 1 ? $tieyear : "NULL").",`idprices`=".$dbo->quote($pricestr).",`promo`=".$ppromo.",`promodaysadv`=".(!empty($ppromodaysadv) ? $ppromodaysadv : "null").",`promotxt`=".$dbo->quote($ppromotxt).",`promominlos`=".(!empty($ppromominlos) ? $ppromominlos : "0").",`promolastmin`=".(int)$promolastmin.",`promofinalprice`={$ppromofinalprice} WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRSEASONUPDATED'));
				if ($stay) {
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editseason&cid[]=".$pwhere);
				} else {
					$mainframe->redirect("index.php?option=com_vikrentcar&task=seasons");
				}
			} else {
				VikError::raiseWarning('', JText::_('ERRINVDATECARSLOCSEASON').(count($double_records) > 0 ? ' ('.implode(', ', $double_records).')' : ''));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=editseason&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editseason&cid[]=".$pwhere);
		}
	}

	public function removeseasons() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		$pidcar = VikRequest::getInt('idcar', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		if (!empty($pwhere)) {
			$ids = array($pwhere);
		}
		if (count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				if (empty($d)) {
					continue;
				}
				$q = "DELETE FROM `#__vikrentcar_seasons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=seasons".(!empty($pidcar) ? '&idcar='.$pidcar : ''));
	}

	public function cancelseason() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=seasons");
	}

	public function payments() {
		VikRentCarHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'payments'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newpayment() {
		VikRentCarHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editpayment() {
		VikRentCarHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createpayment() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getFloat('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$poutposition = VikRequest::getString('outposition', 'top', 'request');
		$plogo = VikRequest::getString('logo', '', 'request');
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDbo();
		if (!empty($pname) && !empty($ppayment)) {
			$setpub=$ppublished=="1" ? 1 : 0;
			$psetconfirmed=$psetconfirmed=="1" ? 1 : 0;
			$pshownotealw=$pshownotealw=="1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikrentcar_gpayments` WHERE `file`=".$dbo->quote($ppayment).";";
			$dbo->setQuery($q);
			$dbo->execute();
			//VikRentCar 1.8 : no longer block payment methods that are using the same PHP file
			if ($dbo->getNumRows() >= 0) {
				$q = "INSERT INTO `#__vikrentcar_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`shownotealw`,`val_pcent`,`ch_disc`,`params`,`outposition`,`logo`) VALUES(".$dbo->quote($pname).",".$dbo->quote($ppayment).",".$dbo->quote($setpub).",".$dbo->quote($pnote).",".$dbo->quote($pcharge).",".$dbo->quote($psetconfirmed).",".$dbo->quote($pshownotealw).",'".$pval_pcent."','".$pch_disc."',".$dbo->quote($payparamstr).", " . $dbo->quote($poutposition) . ", " . $dbo->quote($plogo) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRPAYMENTSAVED'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=newpayment");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=newpayment");
		}
	}

	public function updatepayment() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getFloat('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$poutposition = VikRequest::getString('outposition', 'top', 'request');
		$plogo = VikRequest::getString('logo', '', 'request');
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDbo();
		if (!empty($pname) && !empty($ppayment) && !empty($pwhere)) {
			$setpub=$ppublished=="1" ? 1 : 0;
			$psetconfirmed=$psetconfirmed=="1" ? 1 : 0;
			$pshownotealw=$pshownotealw=="1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikrentcar_gpayments` WHERE `file`=".$dbo->quote($ppayment)." AND `id`!='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			//VikRentCar 1.8 : no longer block payment methods that are using the same PHP file
			if ($dbo->getNumRows() >= 0) {
				$q = "UPDATE `#__vikrentcar_gpayments` SET `name`=".$dbo->quote($pname).",`file`=".$dbo->quote($ppayment).",`published`=".$dbo->quote($setpub).",`note`=".$dbo->quote($pnote).",`charge`=".$dbo->quote($pcharge).",`setconfirmed`=".$dbo->quote($psetconfirmed).",`shownotealw`=".$dbo->quote($pshownotealw).",`val_pcent`='".$pval_pcent."',`ch_disc`='".$pch_disc."',`params`=".$dbo->quote($payparamstr).",`outposition`=" . $dbo->quote($poutposition) . ",`logo`=" . $dbo->quote($plogo) . " WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRPAYMENTUPDATED'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=editpayment&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editpayment&cid[]=".$pwhere);
		}
	}

	public function removepayments() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_gpayments` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
	}

	public function cancelpayment() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
	}

	public function modavailpayment() {
		$cid = VikRequest::getVar('cid', array(0));
		$idp = $cid[0];
		if (!empty($idp)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `published` FROM `#__vikrentcar_gpayments` WHERE `id`=".intval($idp).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$get = $dbo->loadAssocList();
			$q = "UPDATE `#__vikrentcar_gpayments` SET `published`=".(intval($get[0]['published']) == 1 ? '0' : '1')." WHERE `id`=".intval($idp).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
	}

	public function sortpayment() {
		$cid = VikRequest::getVar('cid', array(0));
		$sortid = $cid[0];
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pmode = VikRequest::getString('mode', '', 'request');
		if (!empty($pmode) && !empty($sortid)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_gpayments` ORDER BY `#__vikrentcar_gpayments`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_gpayments` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikrentcar_gpayments` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=payments");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	public function setordconfirmed() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".(int)$oid." AND `status` != 'confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vrc_tn = VikRentCar::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikrentcar', VIKRENTCAR_ADMIN_LANG, $order[0]['lang'], true);
					$vrc_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			$q = "SELECT `units` FROM `#__vikrentcar_cars` WHERE `id`='".$order[0]['idcar']."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$units = $dbo->loadResult();
			$realback = VikRentCar::getHoursCarAvail() * 3600;
			$realback += $order[0]['consegna'];

			/**
			 * Setting an order to confirmed is now allowed only in case of availability
			 * unless the administrator decides to force the confirmation of the order.
			 * 
			 * @since 	1.14.5 (J) - 1.2.0 (WP)
			 */
			$pforce_availability = VikRequest::getInt('force_av', 0, 'request');
			$forced_availability = false;
			$is_available = VikRentCar::carBookable($order[0]['idcar'], $units, $order[0]['ritiro'], $order[0]['consegna']);
			$history_descr = '';

			if (!$is_available && !$pforce_availability) {
				// raise errors and redirect
				VikError::raiseWarning('', JText::_('VRBOOKNOTMADE'));
				VikError::raiseWarning('', JText::_('VRCFORCEAVAILABILITYCONF') . ' <a class="btn btn-danger" href="index.php?option=com_vikrentcar&task=setordconfirmed&force_av=1&cid[]=' . $oid . '">' . JText::_('VRCFORCEAVAILABILITY') . '</a>');
				
				$app->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$oid);
				exit;
			}

			if (!$is_available && $pforce_availability) {
				// turn on flag to save that the order was forced
				$forced_availability = true;
				$history_descr = JText::_('VRCAVAILABILITYFORCED');
			}

			// occupy the car
			$q = "INSERT INTO `#__vikrentcar_busy` (`idcar`,`ritiro`,`consegna`,`realback`) VALUES(".(int)$order[0]['idcar'].",".(int)$order[0]['ritiro'].",".(int)$order[0]['consegna'].",".(int)$realback.");";
			$dbo->setQuery($q);
			$dbo->execute();
			$busynow = $dbo->insertid();

			// assign car specific unit
			$car_index = null;
			if (VRCFactory::getConfig()->get('autocarunit', 1)) {
				$car_indexes = VikRentCar::getCarUnitNumsUnavailable($order[0], true);
				if (!empty($car_indexes)) {
					$car_index = $car_indexes[0];
				}
			}

			// update records
			$q = "UPDATE `#__vikrentcar_orders` SET `idbusy`=" . (int)$busynow . ", `status`='confirmed', `carindex`=" . (!empty($car_index) ? (int)$car_index : 'NULL') . " WHERE `id`=" . (int)$order[0]['id'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `idorder`=".(int)$order[0]['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			// Booking History
			VikRentCar::getOrderHistoryInstance()->setBid($order[0]['id'])->store('TC', $history_descr);
			//
			//send mail
			$ftitle = VikRentCar::getFrontTitle($vrc_tn);
			$nowts = $order[0]['ts'];
			$carinfo = VikRentCar::getCarInfo($order[0]['idcar'], $vrc_tn);
			$viklink = VikRentCar::externalroute("index.php?option=com_vikrentcar&view=order&sid=" . $order[0]['sid'] . "&ts=" . $order[0]['ts'] . (!empty($order[0]['lang']) ? '&lang=' . $order[0]['lang'] : ''), false);
			//
			$is_cust_cost = (!empty($order[0]['cust_cost']) && $order[0]['cust_cost'] > 0);
			if (!empty($order[0]['idtar'])) {
				//vikrentcar 1.5
				if ($order[0]['hourly'] == 1) {
					$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`=".(int)$order[0]['idtar'].";";
				} else {
					$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`=".(int)$order[0]['idtar'].";";
				}
				//
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					if ($order[0]['hourly'] == 1) {
						$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`=".(int)$order[0]['idtar'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
						}
					}
				} else {
					$tar = $dbo->loadAssocList();
				}
			} elseif ($is_cust_cost) {
				//Custom Rate
				$tar = array(0 => array(
					'id' => -1,
					'idcar' => $order[0]['idcar'],
					'days' => $order[0]['days'],
					'idprice' => -1,
					'cost' => $order[0]['cust_cost'],
					'attrdata' => '',
				));
			}
			//vikrentcar 1.5
			if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
				foreach ($tar as $kt => $vt) {
					$tar[$kt]['days'] = 1;
				}
			}
			//
			//vikrentcar 1.6
			$checkhourscharges = 0;
			$ppickup = $order[0]['ritiro'];
			$prelease = $order[0]['consegna'];
			$secdiff = $prelease - $ppickup;
			$daysdiff = $secdiff / 86400;
			if (is_int($daysdiff)) {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				}
			} else {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				} else {
					$sum = floor($daysdiff) * 86400;
					$newdiff = $secdiff - $sum;
					$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
					if ($maxhmore >= $newdiff) {
						$daysdiff = floor($daysdiff);
					} else {
						$daysdiff = ceil($daysdiff);
						/**
						 * Apply proper rounding with gratuity period.
						 * 
						 * @since 	1.15.1 (J) - 1.3.2 (WP)
						 */
						$ehours_float = ($newdiff - $maxhmore) / 3600;
						$ehours = intval(round($ehours_float));
						$ehours = !$ehours && $ehours_float > 0 && $maxhmore > 0 ? 1 : $ehours;
						$checkhourscharges = $ehours;
						if ($checkhourscharges > 0) {
							$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
						}
					}
				}
			}
			if ($checkhourscharges > 0 && $aehourschbasp == true && !$is_cust_cost) {
				$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, false, true, true);
				$tar = $ret['return'];
				$calcdays = $ret['days'];
			}
			if ($checkhourscharges > 0 && $aehourschbasp == false && !$is_cust_cost) {
				$tar = VikRentCar::extraHoursSetPreviousFareCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true);
				$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
				$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true, true, true);
				$tar = $ret['return'];
				$calcdays = $ret['days'];
			} else {
				if (!$is_cust_cost) {
					//Seasonal prices only if not a custom rate
					$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
				}
			}
			//
			$ritplace = (!empty($order[0]['idplace']) ? VikRentCar::getPlaceName($order[0]['idplace'], $vrc_tn) : "");
			$consegnaplace = (!empty($order[0]['idreturnplace']) ? VikRentCar::getPlaceName($order[0]['idreturnplace'], $vrc_tn) : "");
			$costplusiva = $is_cust_cost ? VikRentCar::sayCustCostPlusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$costminusiva = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$pricestr = ($is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN').": ".$costplusiva : VikRentCar::getPriceName($tar[0]['idprice'], $vrc_tn)).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentCar::getPriceAttr($tar[0]['idprice'], $vrc_tn).": ".$tar[0]['attrdata'] : "");
			$isdue = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$tot_taxes = ($costplusiva - $costminusiva);
			$optstr = "";
			$optarrtaxnet = array();
			if (!empty($order[0]['optionals'])) {
				$stepo = explode(";", $order[0]['optionals']);
				foreach ($stepo as $oo) {
					if (!empty($oo)) {
						$stept = explode(":", $oo);
						$q = "SELECT `id`,`name`,`cost`,`perday`,`hmany`,`idiva`,`maxprice` FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($stept[0]).";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$actopt=$dbo->loadAssocList();
							$vrc_tn->translateContents($actopt, '#__vikrentcar_optionals');
							$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
							$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days']) : $actopt[0]['cost'];
							if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
								$realcost = $actopt[0]['maxprice'];
								if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
									$realcost = $actopt[0]['maxprice'] * $stept[1];
								}
							}
							$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$isdue += $tmpopr;
							$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$optarrtaxnet[] = $optnetprice;
							$optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
							$tot_taxes += ($tmpopr - $optnetprice);
						}
					}
				}
			}
			//custom extra costs
			if (!empty($order[0]['extracosts'])) {
				$cur_extra_costs = json_decode($order[0]['extracosts'], true);
				foreach ($cur_extra_costs as $eck => $ecv) {
					$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$isdue += $efee_cost;
					$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$optarrtaxnet[] = $efee_cost_without;
					$optstr.=$ecv['name'].": ".$efee_cost."\n";
					$tot_taxes += ($efee_cost - $efee_cost_without);
				}
			}
			//
			$maillocfee="";
			$locfeewithouttax = 0;
			if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
				$locfee=VikRentCar::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
				if ($locfee) {
					//VikRentCar 1.7 - Location fees overrides
					if (strlen($locfee['losoverride']) > 0) {
						$arrvaloverrides = array();
						$valovrparts = explode('_', $locfee['losoverride']);
						foreach ($valovrparts as $valovr) {
							if (!empty($valovr)) {
								$ovrinfo = explode(':', $valovr);
								$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
							}
						}
						if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
							$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
						}
					}
					//end VikRentCar 1.7 - Location fees overrides
					$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
					$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
					$isdue += $locfeewith;
					$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
					$maillocfee = $locfeewith;
					$tot_taxes += ($locfeewith - $locfeewithouttax);
				}
			}
			//VRC 1.9 - Out of Hours Fees
			$oohfee = VikRentCar::getOutOfHoursFees($order[0]['idplace'], $order[0]['idreturnplace'], $order[0]['ritiro'], $order[0]['consegna'], array('id' => $order[0]['idcar']));
			$mailoohfee = "";
			$oohfeewithouttax = 0;
			if (count($oohfee) > 0) {
				$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
				$isdue += $oohfeewith;
				$oohfeewithouttax = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
				$mailoohfee = $oohfeewith;
				$tot_taxes += ($oohfeewith - $oohfeewithouttax);
			}
			//
			//vikrentcar 1.6 coupon
			$usedcoupon = false;
			$origisdue = $isdue;
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
				// old total : old taxes = new total : new taxes
				$tot_taxes = $tot_taxes * $isdue / $origisdue;
			}
			//
			if (!empty($busynow)) {
				$arrayinfopdf = array(
					'days' => $order[0]['days'],
					'tarminusiva' => $costminusiva,
					'tartax' => ($costplusiva - $costminusiva),
					'opttaxnet' => $optarrtaxnet,
					'locfeenet' => $locfeewithouttax,
					'oohfeenet' => $oohfeewithouttax,
					'order_id' => $order[0]['id'],
					'tot_paid' => $order[0]['totpaid'],
				);
				$app->enqueueMessage(JText::_('VRORDERSETASCONF'));
				// notify the customer unless it was a re-confirmation
				$pskip = VikRequest::getInt('skip_notification', 0, 'request');
				if ($pskip < 1) {
					VikRentCar::sendOrderEmail($order[0]['id'], array('customer'));
				}
			}
		}
		$app->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$oid);
	}

	public function overv() {
		VikRentCarHelper::printHeader("15");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'overv'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function canceloverv() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=overv");
	}

	public function cancelbusy() {
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$pidorder.($pgoto == 'overv' ? '&goto=overv' : ''));
	}

	public function customf() {
		VikRentCarHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customf'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newcustomf() {
		VikRentCarHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editcustomf() {
		VikRentCarHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createcustomf() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pflag = VikRequest::getString('flag', '', 'request');
		$pisemail = $pflag == 'isemail' ? 1 : 0;
		$pisnominative = $pflag == 'isnominative' && $ptype == 'text' ? 1 : 0;
		$pisphone = $pflag == 'isphone' && $ptype == 'text' ? 1 : 0;
		$pisaddress = $pflag == 'isaddress' && $ptype == 'text' ? 1 : 0;
		$piscity = $pflag == 'iscity' && $ptype == 'text' ? 1 : 0;
		$piszip = $pflag == 'iszip' && $ptype == 'text' ? 1 : 0;
		$piscompany = $pflag == 'iscompany' && $ptype == 'text' ? 1 : 0;
		$pisvat = $pflag == 'isvat' && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDbo();
		$q = "SELECT `ordering` FROM `#__vikrentcar_custfields` ORDER BY `#__vikrentcar_custfields`.`ordering` DESC LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$getlast = $dbo->loadResult();
			$newsortnum = $getlast + 1;
		} else {
			$newsortnum = 1;
		}
		$q = "INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`,`flag`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($ptype).", ".$dbo->quote($choosestr).", ".$dbo->quote($prequired).", ".$dbo->quote($newsortnum).", ".$dbo->quote($pisemail).", ".$dbo->quote($ppoplink).", ".$pisnominative.", ".$pisphone.", ".$dbo->quote($fieldflag).");";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
	}

	public function updatecustomf() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pflag = VikRequest::getString('flag', '', 'request');
		$pisemail = $pflag == 'isemail' ? 1 : 0;
		$pisnominative = $pflag == 'isnominative' && $ptype == 'text' ? 1 : 0;
		$pisphone = $pflag == 'isphone' && $ptype == 'text' ? 1 : 0;
		$pisaddress = $pflag == 'isaddress' && $ptype == 'text' ? 1 : 0;
		$piscity = $pflag == 'iscity' && $ptype == 'text' ? 1 : 0;
		$piszip = $pflag == 'iszip' && $ptype == 'text' ? 1 : 0;
		$piscompany = $pflag == 'iscompany' && $ptype == 'text' ? 1 : 0;
		$pisvat = $pflag == 'isvat' && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDbo();
		$q = "UPDATE `#__vikrentcar_custfields` SET `name`=".$dbo->quote($pname).",`type`=".$dbo->quote($ptype).",`choose`=".$dbo->quote($choosestr).",`required`=".$dbo->quote($prequired).",`isemail`=".$dbo->quote($pisemail).",`poplink`=".$dbo->quote($ppoplink).",`isnominative`=".$pisnominative.",`isphone`=".$pisphone.",`flag`=".$dbo->quote($fieldflag)." WHERE `id`=".$dbo->quote($pwhere).";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
	}

	public function removecustomf() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_custfields` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
	}

	public function cancelcustomf() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
	}

	public function sortfield() {
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_custfields` ORDER BY `#__vikrentcar_custfields`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering'])==intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering'])==intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=customf");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	public function removemoreimgs() {
		$mainframe = JFactory::getApplication();
		$pcarid = VikRequest::getInt('carid', '', 'request');
		$pimgind = VikRequest::getInt('imgind', '', 'request');
		if (!empty($pcarid) && strlen($pimgind) > 0) {
			$dbo = JFactory::getDbo();
			$q = "SELECT `moreimgs` FROM `#__vikrentcar_cars` WHERE `id`='".$pcarid."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$actmore = $dbo->loadResult();
			if (strlen($actmore) > 0) {
				$actsplit = explode(';;', $actmore);
				if (array_key_exists($pimgind, $actsplit)) {
					@unlink(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'big_'.$actsplit[$pimgind]);
					@unlink(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'thumb_'.$actsplit[$pimgind]);
					unset($actsplit[$pimgind]);
					$newstr = "";
					foreach ($actsplit as $oi) {
						if (!empty($oi)) {
							$newstr .= $oi.';;';
						}
					}
					$q = "UPDATE `#__vikrentcar_cars` SET `moreimgs`=".$dbo->quote($newstr)." WHERE `id`='".$pcarid."';";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editcar&cid[]=".$pcarid);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	public function coupons() {
		VikRentCarHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'coupons'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newcoupon() {
		VikRentCarHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editcoupon() {
		VikRentCarHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createcoupon() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$mainframe = JFactory::getApplication();
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getFloat('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getString('mintotord', '', 'request');
		$stridcars = "";
		if (@count($pidcars) > 0 && $pallvehicles != 1) {
			foreach ($pidcars as $ch) {
				if (!empty($ch)) {
					$stridcars .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikRentCar::getDateTimestamp($pfrom, 0, 0);
			$second = VikRentCar::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDbo();
		$q = "SELECT * FROM `#__vikrentcar_coupons` WHERE `code`=".$dbo->quote($pcode).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::_('VRCCOUPONEXISTS'));
		} else {
			$mainframe->enqueueMessage(JText::_('VRCCOUPONSAVEOK'));
			$q = "INSERT INTO `#__vikrentcar_coupons` (`code`,`type`,`percentot`,`value`,`datevalid`,`allvehicles`,`idcars`,`mintotord`) VALUES(".$dbo->quote($pcode).",'".$ptype."','".$ppercentot."',".$dbo->quote($pvalue).",'".$strdatevalid."','".$pallvehicles."','".$stridcars."', ".$dbo->quote($pmintotord).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=coupons");
	}

	public function updatecoupon() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$mainframe = JFactory::getApplication();
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getFloat('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pwhere = VikRequest::getString('where', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getString('mintotord', '', 'request');
		$stridcars = "";
		if (@count($pidcars) > 0 && $pallvehicles != 1) {
			foreach ($pidcars as $ch) {
				if (!empty($ch)) {
					$stridcars .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikRentCar::getDateTimestamp($pfrom, 0, 0);
			$second = VikRentCar::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDbo();
		$q = "SELECT * FROM `#__vikrentcar_coupons` WHERE `code`=".$dbo->quote($pcode)." AND `id`!='".$pwhere."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::_('VRCCOUPONEXISTS'));
		} else {
			$mainframe->enqueueMessage(JText::_('VRCCOUPONSAVEOK'));
			$q = "UPDATE `#__vikrentcar_coupons` SET `code`=".$dbo->quote($pcode).",`type`='".$ptype."',`percentot`='".$ppercentot."',`value`=".$dbo->quote($pvalue).",`datevalid`='".$strdatevalid."',`allvehicles`='".$pallvehicles."',`idcars`='".$stridcars."',`mintotord`=".$dbo->quote($pmintotord)." WHERE `id`='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=coupons");
	}

	public function removecoupons() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_coupons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=coupons");
	}

	public function cancelcoupon() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=coupons");
	}

	public function resendordemail() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = (int)$cid[0];
		$this->do_resendordemail($oid);
	}

	public function sendcancordemail() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = (int)$cid[0];
		$this->do_resendordemail($oid, false, true);
	}

	private function do_resendordemail($oid, $checkdbsendpdf = false, $cancellation = false) {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vrc_tn = VikRentCar::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikrentcar', VIKRENTCAR_ADMIN_LANG, $order[0]['lang'], true);
					$vrc_tn::$force_tolang = $order[0]['lang'];
				}
			}

			//send mail
			$ftitle = VikRentCar::getFrontTitle($vrc_tn);
			$nowts = $order[0]['ts'];
			$carinfo = VikRentCar::getCarInfo($order[0]['idcar'], $vrc_tn);

			/**
			 * We try to find the proper Itemid for the View "order" by passing the booking language tag.
			 * 
			 * @since 	1.15.0 (J) - 1.3.0 (WP)
			 */
			$best_itemid = null;
			if (defined('ABSPATH') && !empty($order[0]['lang'])) {
				// get itemid from the Shortcodes model
				$model 		 = JModel::getInstance('vikrentcar', 'shortcodes');
				$best_itemid = $model->best('order', $order[0]['lang']);
			}
			$viklink = VikRentCar::externalroute("index.php?option=com_vikrentcar&view=order&sid=" . $order[0]['sid'] . "&ts=".$order[0]['ts'] . (!empty($order[0]['lang']) ? '&lang=' . $order[0]['lang'] : ''), false, $best_itemid);

			$is_cust_cost = (!empty($order[0]['cust_cost']) && $order[0]['cust_cost'] > 0);
			$tar = [
				[
					'id' 	   => -1,
					'idcar'    => $order[0]['idcar'],
					'days' 	   => $order[0]['days'],
					'idprice'  => -1,
					'cost' 	   => 0,
					'attrdata' => '',
				]
			];
			if (!empty($order[0]['idtar'])) {
				//vikrentcar 1.5
				if ($order[0]['hourly'] == 1) {
					$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='".$order[0]['idtar']."';";
				} else {
					$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
				}
				//
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					if ($order[0]['hourly'] == 1) {
						$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
						}
					}
				} else {
					$tar = $dbo->loadAssocList();
				}
			} elseif ($is_cust_cost) {
				//Custom Rate
				$tar = [
					[
						'id' 		=> -1,
						'idcar' 	=> $order[0]['idcar'],
						'days' 		=> $order[0]['days'],
						'idprice' 	=> -1,
						'cost' 		=> $order[0]['cust_cost'],
						'attrdata' 	=> '',
					]
				];
			}
			//vikrentcar 1.5
			if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
				foreach ($tar as $kt => $vt) {
					$tar[$kt]['days'] = 1;
				}
			}
			//
			//vikrentcar 1.6
			$checkhourscharges = 0;
			$ppickup = $order[0]['ritiro'];
			$prelease = $order[0]['consegna'];
			$secdiff = $prelease - $ppickup;
			$daysdiff = $secdiff / 86400;
			if (is_int($daysdiff)) {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				}
			} else {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				} else {
					$sum = floor($daysdiff) * 86400;
					$newdiff = $secdiff - $sum;
					$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
					if ($maxhmore >= $newdiff) {
						$daysdiff = floor($daysdiff);
					} else {
						$daysdiff = ceil($daysdiff);
						/**
						 * Apply proper rounding with gratuity period.
						 * 
						 * @since 	1.15.1 (J) - 1.3.2 (WP)
						 */
						$ehours_float = ($newdiff - $maxhmore) / 3600;
						$ehours = intval(round($ehours_float));
						$ehours = !$ehours && $ehours_float > 0 && $maxhmore > 0 ? 1 : $ehours;
						$checkhourscharges = $ehours;
						if ($checkhourscharges > 0) {
							$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
						}
					}
				}
			}
			if ($checkhourscharges > 0 && $aehourschbasp == true && !$is_cust_cost) {
				$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, false, true, true);
				$tar = $ret['return'];
				$calcdays = $ret['days'];
			}
			if ($checkhourscharges > 0 && $aehourschbasp == false && !$is_cust_cost) {
				$tar = VikRentCar::extraHoursSetPreviousFareCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true);
				$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
				$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true, true, true);
				$tar = $ret['return'];
				$calcdays = $ret['days'];
			} else {
				if (!$is_cust_cost) {
					//Seasonal prices only if not a custom rate
					$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
				}
			}
			//
			$ritplace = (!empty($order[0]['idplace']) ? VikRentCar::getPlaceName($order[0]['idplace'], $vrc_tn) : "");
			$consegnaplace = (!empty($order[0]['idreturnplace']) ? VikRentCar::getPlaceName($order[0]['idreturnplace'], $vrc_tn) : "");
			$costplusiva = $is_cust_cost ? VikRentCar::sayCustCostPlusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$costminusiva = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$pricestr = ($is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN').": ".$costplusiva : VikRentCar::getPriceName($tar[0]['idprice'], $vrc_tn)).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentCar::getPriceAttr($tar[0]['idprice'], $vrc_tn).": ".$tar[0]['attrdata'] : "");
			$isdue = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$optstr = "";
			$optarrtaxnet = array();
			if (!empty($order[0]['optionals'])) {
				$stepo = explode(";", $order[0]['optionals']);
				foreach ($stepo as $oo) {
					if (!empty($oo)) {
						$stept = explode(":", $oo);
						$q = "SELECT `id`,`name`,`cost`,`perday`,`hmany`,`idiva`,`maxprice` FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($stept[0]).";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$actopt = $dbo->loadAssocList();
							$vrc_tn->translateContents($actopt, '#__vikrentcar_optionals');
							$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
							$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days']) : $actopt[0]['cost'];
							if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
								$realcost = $actopt[0]['maxprice'];
								if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
									$realcost = $actopt[0]['maxprice'] * $stept[1];
								}
							}
							$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$isdue += $tmpopr;
							$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$optarrtaxnet[] = $optnetprice;
							$optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
						}
					}
				}
			}
			//custom extra costs
			if (!empty($order[0]['extracosts'])) {
				$cur_extra_costs = json_decode($order[0]['extracosts'], true);
				foreach ($cur_extra_costs as $eck => $ecv) {
					$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$isdue += $efee_cost;
					$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$optarrtaxnet[] = $efee_cost_without;
					$optstr .= $ecv['name'].": ".$efee_cost."\n";
				}
			}
			//
			$maillocfee = "";
			$locfeewithouttax = 0;
			if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
				$locfee = VikRentCar::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
				if ($locfee) {
					//VikRentCar 1.7 - Location fees overrides
					if (strlen($locfee['losoverride']) > 0) {
						$arrvaloverrides = array();
						$valovrparts = explode('_', $locfee['losoverride']);
						foreach ($valovrparts as $valovr) {
							if (!empty($valovr)) {
								$ovrinfo = explode(':', $valovr);
								$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
							}
						}
						if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
							$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
						}
					}
					//end VikRentCar 1.7 - Location fees overrides
					$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
					$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
					$isdue += $locfeewith;
					$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
					$maillocfee = $locfeewith;
				}
			}
			//VRC 1.9 - Out of Hours Fees
			$oohfee = VikRentCar::getOutOfHoursFees($order[0]['idplace'], $order[0]['idreturnplace'], $order[0]['ritiro'], $order[0]['consegna'], array('id' => $order[0]['idcar']));
			$mailoohfee = "";
			$oohfeewithouttax = 0;
			if (count($oohfee) > 0) {
				$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
				$isdue += $oohfeewith;
				$oohfeewithouttax = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
				$mailoohfee = $oohfeewith;
			}
			//
			//vikrentcar 1.6 coupon
			$usedcoupon = false;
			$origisdue = $isdue;
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			//
			if (!empty($order[0]['custmail'])) {
				$arrayinfopdf = [
					'days' 		  => $order[0]['days'],
					'tarminusiva' => $costminusiva,
					'tartax' 	  => ($costplusiva - $costminusiva),
					'opttaxnet'   => $optarrtaxnet,
					'locfeenet'   => $locfeewithouttax,
					'oohfeenet'   => $oohfeewithouttax,
					'order_id'    => $order[0]['id'],
					'tot_paid'    => $order[0]['totpaid'],
				];

				$sendpdf = true;
				if (!$checkdbsendpdf) {
					$psendpdf = VikRequest::getString('sendpdf', '', 'request');
					if ($psendpdf != "1") {
						$sendpdf = false;
					}
				}
				$sendpdf = $cancellation ? false : $sendpdf;

				VikRentCar::sendOrderEmail($order[0]['id'], ['customer'], true, $sendpdf);

				if ($cancellation) {
					/**
					 * If "send cancellation email", we log the event in the history.
					 * 
					 * @since 	1.15.0 (J) - 1.3.0 (WP)
					 */
					VikRentCar::getOrderHistoryInstance()->setBid($order[0]['id'])->store('EC');
					$mainframe->enqueueMessage(JText::sprintf('VRC_CANC_EMAIL_SENT_TO', $order[0]['custmail']));
				} else {
					$mainframe->enqueueMessage(JText::sprintf('VRORDERMAILRESENT', $order[0]['custmail']));
				}
			} else {
				VikError::raiseWarning('', JText::_('VRORDERMAILRESENTNOREC'));
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$oid);
	}

	public function sortcarat() {
		$mainframe = JFactory::getApplication();
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDbo();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_caratteristiche` ORDER BY `#__vikrentcar_caratteristiche`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr = $dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering'])==intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=carat");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	public function sortoptional() {
		$mainframe = JFactory::getApplication();
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDbo();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_optionals` ORDER BY `#__vikrentcar_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=optionals");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	public function export() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'export'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function doexport() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$oids = VikRequest::getVar('cid', array(0));
		$oids = count($oids) > 0 && intval($oids[key($oids)]) > 0 ? $oids : array();
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pdatetype = VikRequest::getString('datetype', '', 'request');
		$pdatetype = $pdatetype == 'ts' ? 'ts' : 'ritiro';
		$plocation = VikRequest::getString('location', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "csv" ? "csv" : ($ptype == "xml" ? "xml" : "ics");
		$pstatus = VikRequest::getString('status', '', 'request');
		$pdateformat = VikRequest::getString('dateformat', '', 'request');
		$pxml_file = VikRequest::getString('xml_file', '', 'request');
		$nowdf = VikRentCar::getDateFormat(true);
		$nowtf = VikRentCar::getTimeFormat(true);
		$pdateformat .= ' '.$nowtf;
		$tf = $nowtf;
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$clauses = array();
		if (count($oids) > 0) {
			$clauses[] = "`o`.`id` IN(".implode(',', $oids).")";
		}
		if ($pstatus == "C") {
			$clauses[] = "`o`.`status`='confirmed'";
		}
		if (!empty($pfrom) && VikRentCar::dateIsValid($pfrom)) {
			$fromts = VikRentCar::getDateTimestamp($pfrom, '0', '0');
			$clauses[] = "`o`.`".$pdatetype."`>=".$fromts;
		}
		if (!empty($pto) && VikRentCar::dateIsValid($pto)) {
			$tots = VikRentCar::getDateTimestamp($pto, '23', '59');
			$clauses[] = "`o`.`".$pdatetype."`<=".$tots;
		}
		if (!empty($plocation)) {
			$clauses[] = "(`o`.`idplace`=".intval($plocation)." OR `o`.`idreturnplace`=".intval($plocation).")";
		}
		$download_string = '';
		$q = "SELECT `o`.*,`lp`.`name` AS `pickup_location_name`,`ld`.`name` AS `dropoff_location_name` FROM `#__vikrentcar_orders` AS `o` ".
		"LEFT JOIN `#__vikrentcar_places` `lp` ON `o`.`idplace`=`lp`.`id` ".
		"LEFT JOIN `#__vikrentcar_places` `ld` ON `o`.`idreturnplace`=`ld`.`id`".(count($clauses) > 0 ? " WHERE ".implode(' AND ', $clauses) : "")." ORDER BY `o`.`ritiro` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			if ($ptype == "csv") {
				//init csv creation
				$csvlines = array();
				$csvlines[] = array('ID', JText::_('VRCEXPCSVPICK'), JText::_('VRCEXPCSVDROP'), JText::_('VRCEXPCSVCAR'), JText::_('VRCEXPCSVPICKLOC'), JText::_('VRCEXPCSVDROPLOC'), JText::_('VRCEXPCSVCUSTINFO'), JText::_('VRCEXPCSVPAYMETH'), JText::_('VRCEXPCSVORDSTATUS'), JText::_('VRCEXPCSVTOT'), JText::_('VRCEXPCSVTOTPAID'));
				foreach ($rows as $r) {
					$pickdate = $pdatetype == 'ts' ? $r['ritiro'] : date($pdateformat, $r['ritiro']);
					$dropdate = $pdatetype == 'ts' ? $r['consegna'] : date($pdateformat, $r['consegna']);
					$car = VikRentCar::getCarInfo($r['idcar']);
					$pickloc = VikRentCar::getPlaceName($r['idplace']);
					$droploc = VikRentCar::getPlaceName($r['idreturnplace']);
					$custdata = preg_replace('/\s+/', ' ', trim($r['custdata']));
					$payment = VikRentCar::getPayment($r['idpayment']);
					$saystatus = ($r['status']=="confirmed" ? JText::_('VRCONFIRMED') : ($r['status'] == 'standby' ? JText::_('VRSTANDBY') : JText::_('VRCANCELLED')));
					$csvlines[] = array($r['id'], $pickdate, $dropdate, $car['name'], $pickloc, $droploc, $custdata, $payment['name'], $saystatus, number_format($r['order_total'], 2), number_format($r['totpaid'], 2));
				}
				//end csv creation
			} elseif ($ptype == "ics") {
				//init ics creation
				$icslines = array();
				$icscontent = "BEGIN:VCALENDAR\n";
				$icscontent .= "VERSION:2.0\n";
				$icscontent .= "PRODID:-//e4j//VikRentCar//EN\n";
				$icscontent .= "CALSCALE:GREGORIAN\n";
				$str = "";
				foreach ($rows as $r) {
					$uri = VikRentCar::externalroute('index.php?option=com_vikrentcar&view=order&sid=' . $r['sid'] . '&ts=' . $r['ts'] . (!empty($r['lang']) ? '&lang=' . $r['lang'] : ''), false);
					$pickloc = VikRentCar::getPlaceName($r['idplace']);
					$car = VikRentCar::getCarInfo($r['idcar']);
					//$custdata = preg_replace('/\s+/', ' ', trim($r['custdata']));
					//$description = $car['name']."\\n".$r['custdata'];
					$description = $car['name']."\\n".str_replace("\n", "\\n", trim($r['custdata']));
					$str .= "BEGIN:VEVENT\n";
					//End of the Event set as Pickup Date, decomment line below to have it on Drop Off Date
					//$str .= "DTEND:".date('Ymd\THis\Z', $r['consegna'])."\n";
					$str .= "DTEND:".date('Ymd\THis\Z', $r['ritiro'])."\n";
					//
					$str .= "UID:".uniqid()."\n";
					$str .= "DTSTAMP:".date('Ymd\THis\Z', time())."\n";
					$str .= "LOCATION:".preg_replace('/([\,;])/','\\\$1', $pickloc)."\n";
					$str .= ((strlen($description) > 0 ) ? "DESCRIPTION:".preg_replace('/([\,;])/','\\\$1', $description)."\n" : "");
					$str .= "URL;VALUE=URI:".preg_replace('/([\,;])/','\\\$1', $uri)."\n";
					$str .= "SUMMARY:".JText::sprintf('VRCICSEXPSUMMARY', date($tf, $r['ritiro']))."\n";
					$str .= "DTSTART:".date('Ymd\THis\Z', $r['ritiro'])."\n";
					$str .= "END:VEVENT\n";
				}
				$icscontent .= $str;
				$icscontent .= "END:VCALENDAR\n";
				$download_string = $icscontent;
				//end ics creation
			} elseif ($ptype == "xml") {
				//init xml creation
				if (!empty($pxml_file) && file_exists(VRC_ADMIN_PATH.DS.'xml_export'.DS.$pxml_file)) {
					require_once(VRC_ADMIN_PATH.DS.'xml_export'.DS.$pxml_file);
					foreach ($rows as $key => $row) {
						$rows[$key]['car_details'] = VikRentCar::getCarInfo($row['idcar']);
						$rows[$key]['price_info'] = '';
						$q = "SELECT `c`.`idprice`,`c`.`cost`,`c`.`attrdata`,`p`.`name`,`p`.`idiva`,`t`.`aliq` FROM `#__vikrentcar_dispcost` AS `c` LEFT JOIN `#__vikrentcar_prices` `p` ON `c`.`idprice`=`p`.`id` LEFT JOIN `#__vikrentcar_iva` `t` ON `p`.`idiva`=`t`.`id` WHERE `c`.`id`=".(intval($row['idtar'])).";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$price_info = $dbo->loadAssoc();
							$rows[$key]['price_info'] = $price_info;
						}
						$rows[$key]['car_details']['category_name'] = '';
						if (!empty($rows[$key]['car_details']['idcat'])) {
							$all_cats = explode(';', $rows[$key]['car_details']['idcat']);
							$rows[$key]['car_details']['category_name'] = VikRentCar::getCategoryName($all_cats[0]);
						}
					}
					$obj = new vikRentCarXmlExport($rows);
					$download_string = $obj->generateXml();
				} else {
					VikError::raiseWarning('', JText::_('VRCEXPORTERRFILE'));
					$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
				}
				//end xml creation
			}
			//download file from buffer
			$dfilename = 'export_'.date('Y-m-d_H_i').'.'.$ptype;
			if ($ptype == "csv") {
				header("Content-type: text/csv");
				header("Cache-Control: no-store, no-cache");
				header('Content-Disposition: attachment; filename="'.$dfilename.'"');
				$outstream = fopen("php://output", 'w');
				foreach ($csvlines as $csvline) {
					fputcsv($outstream, $csvline);
				}
				fclose($outstream);
				exit;
			} else {
				if ($ptype == "xml") {
					header("Content-Type: text/xml; ");
				} else {
					header("Content-Type: application/octet-stream; ");
				}
				header("Cache-Control: no-store, no-cache");
				header("Content-Disposition: attachment; filename=\"".$dfilename."\"");
				$f = fopen('php://output', "w");
				fwrite($f, $download_string);
				fclose($f);
				exit;
			}
		} else {
			VikError::raiseWarning('', JText::_('VRCEXPORTERRNOREC'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
		}
	}

	public function oohfees() {
		VikRentCarHelper::printHeader("20");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'oohfees'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newoohfee() {
		VikRentCarHelper::printHeader("20");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoohfee'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editoohfee() {
		VikRentCarHelper::printHeader("20");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoohfee'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createoohfee() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$pfrom = VikRequest::getInt('from', '', 'request');
		$pto = VikRequest::getInt('to', '', 'request');
		$ppickcharge = (float)VikRequest::getString('pickcharge', '', 'request');
		$pdropcharge = (float)VikRequest::getString('dropcharge', '', 'request');
		$pmaxcharge = (float)VikRequest::getString('maxcharge', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pidplace = VikRequest::getVar('idplace', array(0));
		$ptype = VikRequest::getInt('type', '', 'request');
		$ptype = $ptype > 1 && $ptype <= 3 ? $ptype : 1;
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pwdays = VikRequest::getVar('wdays', array(0));
		if (!(empty($pfrom) && empty($pto)) && $pfrom != $pto && $pfrom < 86400 && $pto < 86400) {
			$wdays_str = '';
			foreach ($pwdays as $wday) {
				if (!strlen($wday) > 0) {
					continue;
				}
				$wdays_str .= '-'.(int)$wday.'-,';
			}
			$wdays_str = rtrim($wdays_str, ',');
			$cars_str = '';
			foreach ($pidcars as $idcar) {
				if (empty($idcar)) {
					continue;
				}
				$cars_str .= "-".$idcar."-,";
			}
			$q = "INSERT INTO `#__vikrentcar_oohfees` (`oohname`,`pickcharge`,`dropcharge`,`maxcharge`,`idcars`,`from`,`to`,`type`,`idiva`,`wdays`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($ppickcharge).", ".$dbo->quote($pdropcharge).", ".$dbo->quote($pmaxcharge).", ".$dbo->quote($cars_str).", ".$pfrom.", ".$pto.", ".$ptype.", ".(!empty($paliq) ? $paliq : 'NULL').", ".$dbo->quote($wdays_str).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			if (!empty($lid)) {
				foreach ($pidplace as $idplace) {
					if (empty($idplace)) {
						continue;
					}
					$q = "INSERT INTO `#__vikrentcar_oohfees_locxref` (`idooh`,`idlocation`) VALUES(".$lid.", ".(int)$idplace.");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				$mainframe->enqueueMessage(JText::_('VRCOOHFEESAVED'));
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
		} else {
			VikError::raiseWarning('', JText::_('VRCOOHERRTIME'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=newoohfee");
		}
	}

	public function updateoohfee() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$pfrom = VikRequest::getInt('from', '', 'request');
		$pto = VikRequest::getInt('to', '', 'request');
		$ppickcharge = (float)VikRequest::getString('pickcharge', '', 'request');
		$pdropcharge = (float)VikRequest::getString('dropcharge', '', 'request');
		$pmaxcharge = (float)VikRequest::getString('maxcharge', '', 'request');
		$pidcars = VikRequest::getVar('idcars', array(0));
		$pidplace = VikRequest::getVar('idplace', array(0));
		$ptype = VikRequest::getInt('type', '', 'request');
		$ptype = $ptype > 1 && $ptype <= 3 ? $ptype : 1;
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pwdays = VikRequest::getVar('wdays', array(0));
		$pwhere = VikRequest::getInt('where', '', 'request');
		if (!(empty($pfrom) && empty($pto)) && $pfrom != $pto && $pfrom < 86400 && $pto < 86400 && !empty($pwhere)) {
			$wdays_str = '';
			foreach ($pwdays as $wday) {
				if (!strlen($wday) > 0) {
					continue;
				}
				$wdays_str .= '-'.(int)$wday.'-,';
			}
			$wdays_str = rtrim($wdays_str, ',');
			$cars_str = '';
			foreach ($pidcars as $idcar) {
				if (empty($idcar)) {
					continue;
				}
				$cars_str .= "-".$idcar."-,";
			}
			$q = "UPDATE `#__vikrentcar_oohfees` SET `oohname`=".$dbo->quote($pname).",`pickcharge`=".$dbo->quote($ppickcharge).",`dropcharge`=".$dbo->quote($pdropcharge).",`maxcharge`=".$dbo->quote($pmaxcharge).",`idcars`=".$dbo->quote($cars_str).",`from`=".$pfrom.",`to`=".$pto.",`type`=".$ptype.",`idiva`=".(!empty($paliq) ? $paliq : 'NULL').",`wdays`=".$dbo->quote($wdays_str)." WHERE `id`=".$pwhere.";";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "DELETE FROM `#__vikrentcar_oohfees_locxref` WHERE `idooh`=".$pwhere.";";
			$dbo->setQuery($q);
			$dbo->execute();
			foreach ($pidplace as $idplace) {
				if (empty($idplace)) {
					continue;
				}
				$q = "INSERT INTO `#__vikrentcar_oohfees_locxref` (`idooh`,`idlocation`) VALUES(".$pwhere.", ".(int)$idplace.");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			$mainframe->enqueueMessage(JText::_('VRCOOHFEESAVED'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
		} else {
			VikError::raiseWarning('', JText::_('VRCOOHERRTIME'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
		}
	}

	public function removeoohfees() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_oohfees` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentcar_oohfees_locxref` WHERE `idooh`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
	}

	public function canceloohfee() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=oohfees");
	}

	public function customercheckin() {
		$dbo = JFactory::getDbo();
		$cid = VikRequest::getVar('cid', array(0));
		$oid = (int)$cid[0];
		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vrc_tn = VikRentCar::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikrentcar', VIKRENTCAR_ADMIN_LANG, $order[0]['lang'], true);
					$vrc_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			//send mail
			$ftitle = VikRentCar::getFrontTitle();
			$nowts = $order[0]['ts'];
			$carinfo = VikRentCar::getCarInfo($order[0]['idcar']);
			$viklink = VikRentCar::externalroute("index.php?option=com_vikrentcar&view=order&sid=" . $order[0]['sid'] . "&ts=" . $order[0]['ts'] . (!empty($order[0]['lang']) ? '&lang=' . $order[0]['lang'] : ''), false);
			//
			$is_cust_cost = (!empty($order[0]['cust_cost']) && $order[0]['cust_cost'] > 0);
			if (!empty($order[0]['idtar'])) {
				//vikrentcar 1.5
				if ($order[0]['hourly'] == 1) {
					$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='".$order[0]['idtar']."';";
				} else {
					$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
				}
				//
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					if ($order[0]['hourly'] == 1) {
						$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
						}
					}
				} else {
					$tar = $dbo->loadAssocList();
				}
			} elseif ($is_cust_cost) {
				//Custom Rate
				$tar = array(0 => array(
					'id' => -1,
					'idcar' => $order[0]['idcar'],
					'days' => $order[0]['days'],
					'idprice' => -1,
					'cost' => $order[0]['cust_cost'],
					'attrdata' => '',
				));
			}
			//vikrentcar 1.5
			if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
				foreach ($tar as $kt => $vt) {
					$tar[$kt]['days'] = 1;
				}
			}
			//
			//vikrentcar 1.6
			$checkhourscharges = 0;
			$ppickup = $order[0]['ritiro'];
			$prelease = $order[0]['consegna'];
			$secdiff = $prelease - $ppickup;
			$daysdiff = $secdiff / 86400;
			if (is_int($daysdiff)) {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				}
			} else {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				} else {
					$sum = floor($daysdiff) * 86400;
					$newdiff = $secdiff - $sum;
					$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
					if ($maxhmore >= $newdiff) {
						$daysdiff = floor($daysdiff);
					} else {
						$daysdiff = ceil($daysdiff);
						/**
						 * Apply proper rounding with gratuity period.
						 * 
						 * @since 	1.15.1 (J) - 1.3.2 (WP)
						 */
						$ehours_float = ($newdiff - $maxhmore) / 3600;
						$ehours = intval(round($ehours_float));
						$ehours = !$ehours && $ehours_float > 0 && $maxhmore > 0 ? 1 : $ehours;
						$checkhourscharges = $ehours;
						if ($checkhourscharges > 0) {
							$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
						}
					}
				}
			}
			if ($checkhourscharges > 0 && $aehourschbasp == true && !$is_cust_cost) {
				$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, false, true, true);
				$tar = $ret['return'];
				$calcdays = $ret['days'];
			}
			if ($checkhourscharges > 0 && $aehourschbasp == false && !$is_cust_cost) {
				$tar = VikRentCar::extraHoursSetPreviousFareCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true);
				$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
				$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true, true, true);
				$tar = $ret['return'];
				$calcdays = $ret['days'];
			} else {
				if (!$is_cust_cost) {
					//Seasonal prices only if not a custom rate
					$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
				}
			}
			//
			$ritplace = (!empty($order[0]['idplace']) ? VikRentCar::getPlaceName($order[0]['idplace']) : "");
			$consegnaplace = (!empty($order[0]['idreturnplace']) ? VikRentCar::getPlaceName($order[0]['idreturnplace']) : "");
			$costplusiva = $is_cust_cost ? VikRentCar::sayCustCostPlusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$costminusiva = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$pricestr = ($is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN').": ".$costplusiva : VikRentCar::getPriceName($tar[0]['idprice'])).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentCar::getPriceAttr($tar[0]['idprice']).": ".$tar[0]['attrdata'] : "");
			$isdue = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
			$optstr = "";
			$optarrtaxnet = array();
			if (!empty($order[0]['optionals'])) {
				$stepo=explode(";", $order[0]['optionals']);
				foreach ($stepo as $oo) {
					if (!empty($oo)) {
						$stept = explode(":", $oo);
						$q = "SELECT `id`,`name`,`cost`,`perday`,`hmany`,`idiva`,`maxprice` FROM `#__vikrentcar_optionals` WHERE `id`=".$dbo->quote($stept[0]).";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$actopt = $dbo->loadAssocList();
							$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
							$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days']) : $actopt[0]['cost'];
							if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
								$realcost = $actopt[0]['maxprice'];
								if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
									$realcost = $actopt[0]['maxprice'] * $stept[1];
								}
							}
							$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$isdue += $tmpopr;
							$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $order[0]);
							$optarrtaxnet[] = $optnetprice;
							$optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
						}
					}
				}
			}
			//custom extra costs
			if (!empty($order[0]['extracosts'])) {
				$cur_extra_costs = json_decode($order[0]['extracosts'], true);
				foreach ($cur_extra_costs as $eck => $ecv) {
					$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$isdue += $efee_cost;
					$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
					$optarrtaxnet[] = $efee_cost_without;
					$optstr.=$ecv['name'].": ".$efee_cost."\n";
				}
			}
			//
			$maillocfee = "";
			$locfeewithouttax = 0;
			if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
				$locfee = VikRentCar::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
				if ($locfee) {
					//VikRentCar 1.7 - Location fees overrides
					if (strlen($locfee['losoverride']) > 0) {
						$arrvaloverrides = array();
						$valovrparts = explode('_', $locfee['losoverride']);
						foreach ($valovrparts as $valovr) {
							if (!empty($valovr)) {
								$ovrinfo = explode(':', $valovr);
								$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
							}
						}
						if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
							$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
						}
					}
					//end VikRentCar 1.7 - Location fees overrides
					$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
					$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
					$isdue += $locfeewith;
					$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
					$maillocfee = $locfeewith;
				}
			}
			//VRC 1.9 - Out of Hours Fees
			$oohfee = VikRentCar::getOutOfHoursFees($order[0]['idplace'], $order[0]['idreturnplace'], $order[0]['ritiro'], $order[0]['consegna'], array('id' => $order[0]['idcar']));
			$mailoohfee = "";
			$oohfeewithouttax = 0;
			if (count($oohfee) > 0) {
				$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
				$isdue += $oohfeewith;
				$oohfeewithouttax = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
				$mailoohfee = $oohfeewith;
			}
			//
			//vikrentcar 1.6 coupon
			$usedcoupon = false;
			$origisdue = $isdue;
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			//
			$arrayinfopdf = array('days' => $order[0]['days'], 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax, 'oohfeenet' => $oohfeewithouttax, 'order_id' => $order[0]['id'], 'tot_paid' => $order[0]['totpaid']);
			$saystatus = $order[0]['status'] == 'confirmed' ? JText::_('VRCOMPLETED') : ($order[0]['status'] == 'standby' ? JText::_('VRSTANDBY') : JText::_('VRCANCELLED'));
			VikRentCar::generateCheckinPdf($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VRRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $carinfo['name'], $order[0]['ritiro'], $order[0]['consegna'], $pricestr, $optstr, $isdue, $viklink, $saystatus, $ritplace, $consegnaplace, $maillocfee, $mailoohfee, $order[0]['id'], $order[0]['coupon'], $arrayinfopdf);

			// store order history record
			$history_obj = VikRentCar::getOrderHistoryInstance()->setBid($order[0]['id']);
			if (!$history_obj->hasEvent('RB')) {
				$history_obj->store('RB');
				// update order record with new registration status (started)
				$order_record = new stdClass;
				$order_record->id = $order[0]['id'];
				$order_record->reg = 1;
				$dbo->updateObject('#__vikrentcar_orders', $order_record, 'id');
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$oid);
	}

	public function sortlocation() {
		$cid = VikRequest::getVar('cid', array(0));
		$sortid = (int)$cid[0];
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering'])==intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_places` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=places");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	public function geninvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		$mainframe = JFactory::getApplication();
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			require_once(VRC_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "tcpdf" . DIRECTORY_SEPARATOR . 'tcpdf.php');
			if (is_file(VRC_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "tcpdf" . DIRECTORY_SEPARATOR . "fonts" . DIRECTORY_SEPARATOR . "dejavusans.php")) {
				$usepdffont = 'dejavusans';
			} else {
				$usepdffont = 'helvetica';
			}

			/**
			 * Trigger event to allow third party plugins to return a specific font name.
			 * 
			 * @since 	1.15.1 (J) - 1.3.2 (WP)
			 */
			$custom_pdf_font = VRCFactory::getPlatform()->getDispatcher()->filter('onGetPdfFontNameVikRentCar', [$usepdffont]);
			if (is_array($custom_pdf_font) && !empty($custom_pdf_font[0])) {
				$usepdffont = $custom_pdf_font[0];
			}

			$pinvoice_num = VikRequest::getInt('invoice_num', '', 'request');
			$pinvoice_num = $pinvoice_num <= 0 ? 1 : $pinvoice_num;
			$pinvoice_suff = VikRequest::getString('invoice_suff', '', 'request');
			$pinvoice_date = VikRequest::getString('invoice_date', '', 'request');
			$pcompany_info = VikRequest::getString('company_info', '', 'request', VIKREQUEST_ALLOWHTML);
			$pinvoice_send = VikRequest::getString('invoice_send', '', 'request');
			$pinvoice_send = $pinvoice_send == '1' ? 1 : 0;
			$nowdf = VikRentCar::getDateFormat(true);
			$nowtf = VikRentCar::getTimeFormat(true);
			$tf = $nowtf;
			if ($nowdf == "%d/%m/%Y") {
				$df = 'd/m/Y';
			} elseif ($nowdf == "%m/%d/%Y") {
				$df = 'm/d/Y';
			} else {
				$df = 'Y/m/d';
			}
			$today = date($df);
			$admail = VikRentCar::getAdminMail();
			$currencyname = VikRentCar::getCurrencyName();
			$companylogo = VikRentCar::getSiteLogo();
			$uselogo = '';
			if (!empty($companylogo)) {
				$uselogo = '<img src="'.VRC_ADMIN_URI.'resources/'.$companylogo.'"/>';
			}
			$totinvgen = 0;
			sort($ids);
			$vrc_tn = VikRentCar::getTranslator();
			foreach ($ids as $oid) {
				$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=".(int)$oid." AND `status`='confirmed';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$order = $dbo->loadAssocList();
					$isdue = 0;
					$descriptions = array();
					$netprices = array();
					$taxes = array();
					//check if the language in use is the same as the one used during the checkout
					if (!empty($order[0]['lang'])) {
						$lang = JFactory::getLanguage();
						if ($lang->getTag() != $order[0]['lang']) {
							$lang->load('com_vikrentcar', VIKRENTCAR_ADMIN_LANG, $order[0]['lang'], true);
							$vrc_tn::$force_tolang = $order[0]['lang'];
						}
					}
					//
					$car = VikRentCar::getCarInfo($order[0]['idcar']);
					$is_cust_cost = (!empty($order[0]['cust_cost']) && $order[0]['cust_cost'] > 0);
					if (!empty($order[0]['idtar'])) {
						if ($order[0]['hourly'] == 1) {
							$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='".$order[0]['idtar']."';";
						} else {
							$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
						}
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 0) {
							if ($order[0]['hourly'] == 1) {
								$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$order[0]['idtar']."';";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$tar = $dbo->loadAssocList();
								}
							}
						} else {
							$tar = $dbo->loadAssocList();
						}
					} elseif ($is_cust_cost) {
						//Custom Rate
						$tar = array(0 => array(
							'id' => -1,
							'idcar' => $order[0]['idcar'],
							'days' => $order[0]['days'],
							'idprice' => -1,
							'cost' => $order[0]['cust_cost'],
							'attrdata' => '',
						));
					}
					if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
						foreach ($tar as $kt => $vt) {
							$tar[$kt]['days'] = 1;
						}
					}
					$checkhourscharges = 0;
					$ppickup = $order[0]['ritiro'];
					$prelease = $order[0]['consegna'];
					$secdiff = $prelease - $ppickup;
					$daysdiff = $secdiff / 86400;
					if (is_int($daysdiff)) {
						if ($daysdiff < 1) {
							$daysdiff = 1;
						}
					} else {
						if ($daysdiff < 1) {
							$daysdiff = 1;
						} else {
							$sum = floor($daysdiff) * 86400;
							$newdiff = $secdiff - $sum;
							$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
							if ($maxhmore >= $newdiff) {
								$daysdiff = floor($daysdiff);
							} else {
								$daysdiff = ceil($daysdiff);
								/**
								 * Apply proper rounding with gratuity period.
								 * 
								 * @since 	1.15.1 (J) - 1.3.2 (WP)
								 */
								$ehours_float = ($newdiff - $maxhmore) / 3600;
								$ehours = intval(round($ehours_float));
								$ehours = !$ehours && $ehours_float > 0 && $maxhmore > 0 ? 1 : $ehours;
								$checkhourscharges = $ehours;
								if ($checkhourscharges > 0) {
									$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
								}
							}
						}
					}
					if ($checkhourscharges > 0 && $aehourschbasp == true && !$is_cust_cost) {
						$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, false, true, true);
						$tar = $ret['return'];
						$calcdays = $ret['days'];
					}
					if ($checkhourscharges > 0 && $aehourschbasp == false && !$is_cust_cost) {
						$tar = VikRentCar::extraHoursSetPreviousFareCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true);
						$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
						$ret = VikRentCar::applyExtraHoursChargesCar($tar, $order[0]['idcar'], $checkhourscharges, $daysdiff, true, true, true);
						$tar = $ret['return'];
						$calcdays = $ret['days'];
					} else {
						if (!$is_cust_cost) {
							//Seasonal prices only if not a custom rate
							$tar = VikRentCar::applySeasonsCar($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
						}
					}
					$ritplace = (!empty($order[0]['idplace']) ? VikRentCar::getPlaceName($order[0]['idplace']) : "");
					$consegnaplace = (!empty($order[0]['idreturnplace']) ? VikRentCar::getPlaceName($order[0]['idreturnplace']) : "");
					$costplusiva = $is_cust_cost ? VikRentCar::sayCustCostPlusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
					$costminusiva = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar[0]['cost'], $order[0]['cust_idiva']) : VikRentCar::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice'], $order[0]);
					$pricestr = JText::sprintf('VRCINVDESCRCONT', $car['name'], date($df.' '.$tf, $order[0]['ritiro']))."\n";
					$pricestr .= ($is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar[0]['idprice'])).(!empty($tar[0]['attrdata']) ? "\n".VikRentCar::getPriceAttr($tar[0]['idprice']).": ".$tar[0]['attrdata'] : "");
					//description
					$descriptions[] = nl2br(rtrim($pricestr, "\n"));
					//Prices
					$netprices[] = $costminusiva;
					$taxes[] = ($costplusiva - $costminusiva);
					$isdue = $costplusiva;
					//Options
					if (!empty($order[0]['optionals'])) {
						$stepo=explode(";", $order[0]['optionals']);
						foreach ($stepo as $oo) {
							if (!empty($oo)) {
								$stept=explode(":", $oo);
								$q = "SELECT `id`,`name`,`cost`,`perday`,`hmany`,`idiva`,`maxprice` FROM `#__vikrentcar_optionals` WHERE `id`=".intval($stept[0]).";";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$actopt = $dbo->loadAssocList();
									$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
									$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days']) : $actopt[0]['cost'];
									if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
										$realcost = $actopt[0]['maxprice'];
										if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
											$realcost = $actopt[0]['maxprice'] * $stept[1];
										}
									}
									$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $order[0]);
									$isdue += $tmpopr;
									$optnetprice = VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $order[0]);
									$descriptions[] = ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr;
									$netprices[] = $optnetprice;
									$taxes[] = ($tmpopr - $optnetprice);
								}
							}
						}
					}
					//Location Fees
					if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
						$locfee=VikRentCar::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
						if ($locfee) {
							if (strlen($locfee['losoverride']) > 0) {
								$arrvaloverrides = array();
								$valovrparts = explode('_', $locfee['losoverride']);
								foreach ($valovrparts as $valovr) {
									if (!empty($valovr)) {
										$ovrinfo = explode(':', $valovr);
										$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
									}
								}
								if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
									$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
								}
							}
							$locfeecost=intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
							$locfeewith=VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
							$isdue+=$locfeewith;
							$locfeewithouttax = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
							$descriptions[] = JText::_('VRLOCFEETOPAY');
							$netprices[] = $locfeewithouttax;
							$taxes[] = ($locfeewith - $locfeewithouttax);
						}
					}
					//Out of Hours Fees
					$oohfee = VikRentCar::getOutOfHoursFees($order[0]['idplace'], $order[0]['idreturnplace'], $order[0]['ritiro'], $order[0]['consegna'], array('id' => $order[0]['idcar']));
					if (count($oohfee) > 0) {
						$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
						$isdue += $oohfeewith;
						$oohfeewithouttax = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
						$mailoohfee = $oohfeewith;
						$descriptions[] = JText::_('VRCOOHFEEAMOUNT');
						$netprices[] = $oohfeewithouttax;
						$taxes[] = ($oohfeewith - $oohfeewithouttax);
					}
					//custom extra costs
					if (!empty($order[0]['extracosts'])) {
						$cur_extra_costs = json_decode($order[0]['extracosts'], true);
						foreach ($cur_extra_costs as $eck => $ecv) {
							$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
							$isdue += $efee_cost;
							$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
							$descriptions[] = $ecv['name'];
							$netprices[] = $efee_cost_without;
							$taxes[] = ($efee_cost - $efee_cost_without);
						}
					}
					//
					//date
					$usedate = $pinvoice_date == '0' ? date($df, $order[0]['ts']) : $today;
					//compose body
					list($invoicetpl, $pdfparams) = VikRentCar::loadInvoiceTmpl($order[0]);
					$hbody = VikRentCar::parseInvoiceTemplate($invoicetpl, $order[0], $car, array('currencyname' => $currencyname, 'company_logo' => $uselogo, 'company_info' => nl2br($pcompany_info), 'invoice_number' => $pinvoice_num, 'invoice_suffix' => $pinvoice_suff, 'invoice_date' => $usedate, 'invoice_products_descriptions' => $descriptions, 'invoice_products_netprices' => $netprices, 'invoice_products_taxes' => $taxes, 'invoice_grandtotal' => $isdue));
					//generate PDF
					$pdffname = $order[0]['id'] . '_' . $order[0]['sid'] . '.pdf';
					$pathpdf = VRC_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "invoices" . DIRECTORY_SEPARATOR . "generated" . DIRECTORY_SEPARATOR . $pdffname;
					if (file_exists($pathpdf)) @unlink($pathpdf);
					$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
					$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
					$pdf->SetTitle(JText::_('VRCINVNUM').' '.$pinvoice_num);
					//Header for each page of the pdf
					if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
						$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
					}
					//Change some currencies to their unicode (decimal) value
					$unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
					if (array_key_exists($currencyname, $unichr_map)) {
						$hbody = str_replace($currencyname, TCPDF_FONTS::unichr($unichr_map[$currencyname]), $hbody);
					}
					//header and footer fonts
					$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
					$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
					//margins
					$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
					$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
					$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
					//
					$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
					$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
					$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);
					if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
						$pdf->SetPrintHeader(false);
					}
					if ($pdfparams['show_footer'] == 0) {
						$pdf->SetPrintFooter(false);
					}
					$pdf->AddPage();
					$pdf->writeHTML($hbody, true, false, true, false, '');
					$pdf->lastPage();
					$pdf->Output($pathpdf, 'F');
					if (file_exists($pathpdf)) {
						if ($pinvoice_send == 1) {
							//send invoice via email
							$vrc_app = new VrcApplication();
							$vrc_app->sendMail($admail, $admail, $order[0]['custmail'], $admail, JText::_('VRCINVMAILSUBJ'), JText::_('VRCINVMAILCONT'), true, 'base64', $pathpdf);
							unset($mailer);
						}
						$totinvgen++;
						$pinvoice_num++;
						/**
						 * @wponly - trigger files mirroring
						 */
						VikRentCarLoader::import('update.manager');
						VikRentCarUpdateManager::triggerUploadBackup($pathpdf);
						//
					}
				}
			}
			$mainframe->enqueueMessage(JText::sprintf('VRCTOTINVGEN', $totinvgen));
			//update values used
			$q = "UPDATE `#__vikrentcar_config` SET `setting`='".($pinvoice_num - 1)."' WHERE `param`='invoiceinum';";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pinvoice_suff)." WHERE `param`='invoicesuffix';";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote($pcompany_info)." WHERE `param`='invcompanyinfo';";
			$dbo->setQuery($q);
			$dbo->execute();
			//
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
	}

	public function loadcronparams() {
		//to be called via ajax
		$html = '---------';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$html = VikRentCar::displayCronParameters($phpfile);
		}
		/**
		 * The HTML content is built by an internal method that does not trigger any hook
		 * where third party plugins could interfere. We cannot escape this HTML string,
		 * nor can we convert special chars into HTML entities, as this is the response
		 * of an AJAX request, and the HTML code needs to be displayed accordingly.
		 * If we were to escape the HTML string, then the AJAX response would be useless,
		 * as it would be HTML code converted into text with HTML entities.
		 */
		echo $html;
		exit;
	}

	public function loadpaymentparams() {
		//to be called via ajax
		$html = '---------';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$html = VikRentCar::displayPaymentParameters($phpfile);
		}
		/**
		 * The HTML content is built by an internal method that does not trigger any hook
		 * where third party plugins could interfere. We cannot escape this HTML string,
		 * nor can we convert special chars into HTML entities, as this is the response
		 * of an AJAX request, and the HTML code needs to be displayed accordingly.
		 * If we were to escape the HTML string, then the AJAX response would be useless,
		 * as it would be HTML code converted into text with HTML entities.
		 */
		echo $html;
		exit;
	}

	public function translations() {
		VikRentCarHelper::printHeader("21");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'translations'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function savetranslation() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_savetranslation();
	}

	public function savetranslationstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_savetranslation(true);
	}

	private function do_savetranslation($stay = false) {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$vrc_tn = VikRentCar::getTranslator();
		$table = VikRequest::getString('vrc_table', '', 'request');
		$cur_langtab = VikRequest::getString('vrc_lang', '', 'request');
		$langs = $vrc_tn->getLanguagesList();
		$xml_tables = $vrc_tn->getTranslationTables();
		if (!empty($table) && array_key_exists($table, $xml_tables)) {
			$tn = VikRequest::getVar('tn', array(), 'request', 'array', VIKREQUEST_ALLOWHTML);
			$tn_saved = 0;
			$table_cols = $vrc_tn->getTableColumns($table);
			$table = $vrc_tn->replacePrefix($table);
			foreach ($langs as $ltag => $lang) {
				if ($ltag == $vrc_tn->default_lang) {
					continue;
				}
				if (array_key_exists($ltag, $tn) && count($tn[$ltag]) > 0) {
					foreach ($tn[$ltag] as $reference_id => $translation) {
						$lang_translation = array();
						foreach ($table_cols as $field => $fdetails) {
							if (!array_key_exists($field, $translation)) {
								continue;
							}
							$ftype = $fdetails['type'];
							if ($ftype == 'skip') {
								continue;
							}
							if ($ftype == 'json') {
								$translation[$field] = json_encode($translation[$field]);
							}
							$lang_translation[$field] = $translation[$field];
						}
						if (count($lang_translation) > 0) {
							$q = "SELECT `id` FROM `#__vikrentcar_translations` WHERE `table`=".$dbo->quote($table)." AND `lang`=".$dbo->quote($ltag)." AND `reference_id`=".$dbo->quote((int)$reference_id).";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$last_id = $dbo->loadResult();
								$q = "UPDATE `#__vikrentcar_translations` SET `content`=".$dbo->quote(json_encode($lang_translation))." WHERE `id`=".(int)$last_id.";";
							} else {
								$q = "INSERT INTO `#__vikrentcar_translations` (`table`,`lang`,`reference_id`,`content`) VALUES (".$dbo->quote($table).", ".$dbo->quote($ltag).", ".$dbo->quote((int)$reference_id).", ".$dbo->quote(json_encode($lang_translation)).");";
							}
							$dbo->setQuery($q);
							$dbo->execute();
							$tn_saved++;
						}
					}
				}
			}
			if ($tn_saved > 0) {
				$mainframe->enqueueMessage(JText::_('VRCTRANSLSAVEDOK'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VRCTRANSLATIONERRINVTABLE'));
		}
		$mainframe->redirect("index.php?option=com_vikrentcar".($stay ? '&task=translations&vrc_table='.$vrc_tn->replacePrefix($table).'&vrc_lang='.$cur_langtab : ''));
	}

	public function sortcategory() {
		$cid = VikRequest::getVar('cid', array(0));
		$sortid = (int)$cid[0];
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentcar_categories` ORDER BY `#__vikrentcar_categories`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentcar_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentcar&task=categories");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar");
		}
	}

	public function edittmplfile() {
		//modal box, so we do not set menu or footer

		VikRequest::setVar('view', VikRequest::getCmd('view', 'edittmplfile'));
	
		parent::display();
	}

	public function tmplfileprew() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'tmplfileprew'));
	
		parent::display();
	}

	public function savetmplfile() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$fpath = VikRequest::getString('path', '', 'request', VIKREQUEST_ALLOWRAW);
		$pcont = VikRequest::getString('cont', '', 'request', VIKREQUEST_ALLOWRAW);
		$mainframe = JFactory::getApplication();
		$exists = file_exists($fpath) ? true : false;
		if (!$exists) {
			$fpath = urldecode($fpath);
		}
		$fpath = file_exists($fpath) ? $fpath : '';
		if (!empty($fpath)) {
			$fp = fopen($fpath, 'wb');
			$byt = (int)fwrite($fp, $pcont);
			fclose($fp);
			if ($byt > 0) {
				$mainframe->enqueueMessage(JText::_('VRCUPDTMPLFILEOK'));
				/**
				 * @wponly  call the UpdateManager Class to temporary store modifications made to template files
				 */
				VikRentCarUpdateManager::storeTemplateContent($fpath, $pcont);
				//
			} else {
				VikError::raiseWarning('', JText::_('VRCUPDTMPLFILENOBYTES'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VRCUPDTMPLFILEERR'));
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=edittmplfile&path=".$fpath."&tmpl=component");

		exit;
	}

	public function unlockrecords() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar");
	}

	public function graphs() {
		VikRentCarHelper::printHeader("22");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'graphs'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function choosebusy() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'choosebusy'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function orders() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'orders'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function removeorders() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$ids = VikRequest::getVar('cid', array(0));
		if (is_array($ids) && count($ids)) {
			foreach ($ids as $d) {
				$q = "SELECT `o`.*,`b`.`stop_sales` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` `b` ON `b`.`id`=`o`.`idbusy` WHERE `o`.`id`=".$dbo->quote((int)$d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['idbusy'])) {
						$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id`=" . (int)$rows[0]['idbusy'] . ";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
					$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `idorder`=" . (int)$rows[0]['id'] . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($rows[0]['status'] == 'cancelled') {
						$q = "DELETE FROM `#__vikrentcar_customers_orders` WHERE `idorder`=" . (int)$rows[0]['id'] . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikrentcar_orders` WHERE `id`=" . (int)$rows[0]['id'] . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikrentcar_orderhistory` WHERE `idorder`=" . (int)$rows[0]['id'] . ";";
						$dbo->setQuery($q);
						$dbo->execute();
					} else {
						$q = "UPDATE `#__vikrentcar_orders` SET `idbusy`=NULL,`status`='cancelled' WHERE `id`=" . (int)$rows[0]['id'] . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						// Booking History
						VikRentCar::getOrderHistoryInstance()->setBid($rows[0]['id'])->store('CB');
						//
					}
				}
			}
			$mainframe->enqueueMessage(JText::_('VRMESSDELBUSY'));
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
	}

	public function canceledorder() {
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=".($pgoto == 'overv' ? 'overv' : 'orders'));
	}

	public function removebusy() {
		$mainframe = JFactory::getApplication();
		$pidbusy = VikRequest::getInt('idbusy', '', 'request');
		$pidorder = VikRequest::getInt('idorder', '', 'request');
		$pidcar = VikRequest::getString('idcar', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		$preturn = VikRequest::getString('return', '', 'request');
		if (!empty($pidorder) && !empty($pidcar)) {
			$dbo = JFactory::getDbo();
			$q = "SELECT `o`.*,`b`.`stop_sales` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` `b` ON `b`.`id`=`o`.`idbusy` WHERE `o`.`id`=".$dbo->quote($pidorder).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$ord = $dbo->loadAssocList();
				$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `idorder`=" . (int)$ord[0]['id'] . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($ord[0]['status'] == 'cancelled') {
					$q = "DELETE FROM `#__vikrentcar_customers_orders` WHERE `idorder`=" . (int)$ord[0]['id'] . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					$q = "DELETE FROM `#__vikrentcar_orders` WHERE `id`=" . (int)$ord[0]['id'] . " LIMIT 1;";
					$dbo->setQuery($q);
					$dbo->execute();
					$q = "DELETE FROM `#__vikrentcar_orderhistory` WHERE `idorder`=" . (int)$ord[0]['id'] . ";";
					$dbo->setQuery($q);
					$dbo->execute();
				} else {
					$q = "UPDATE `#__vikrentcar_orders` SET `idbusy`=NULL,`status`='cancelled' WHERE `id`=" . (int)$ord[0]['id'] . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					// Booking History
					VikRentCar::getOrderHistoryInstance()->setBid($ord[0]['id'])->store('CB');
					//
				}
				$mainframe->enqueueMessage(JText::_('VRMESSDELBUSY'));
				/**
				 * Make sure to free up the vehicle in the previously booked dates.
				 * In case the busy ID is not passed in the request, we force it.
				 * 
				 * @since 	1.2.0
				 */
				if (!empty($ord[0]['idbusy'])) {
					// no matter what, this is the busy record that must be removed
					$pidbusy = (int)$ord[0]['idbusy'];
				}
			}
			if (!empty($pidbusy)) {
				$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id`=".$dbo->quote($pidbusy)." LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		if ($preturn == 'order' && $ord[0]['status'] != 'cancelled') {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$pidorder);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=".($pgoto == 'overv' ? 'overv' : 'orders'));
		}
	}

	public function updatebusy() {
		$mainframe = JFactory::getApplication();
		$pidbusy = VikRequest::getString('idbusy', '', 'request');
		$pidorder = VikRequest::getInt('idorder', '', 'request');
		$preturn = VikRequest::getString('return', '', 'request');
		$porder_total = VikRequest::getString('order_total', '', 'request');
		$pnewidcar = VikRequest::getInt('newidcar', '', 'request');
		$pidplace = VikRequest::getInt('idplace', '', 'request');
		$pidreturnplace = VikRequest::getInt('idreturnplace', '', 'request');
		$ppickupdate = VikRequest::getString('pickupdate', '', 'request');
		$preleasedate = VikRequest::getString('releasedate', '', 'request');
		$ppickuph = VikRequest::getString('pickuph', '', 'request');
		$ppickupm = VikRequest::getString('pickupm', '', 'request');
		$preleaseh = VikRequest::getString('releaseh', '', 'request');
		$preleasem = VikRequest::getString('releasem', '', 'request');
		$pidcar = VikRequest::getString('idcar', '', 'request');
		$origidcar = $pidcar;
		if (!empty($pnewidcar) && $pnewidcar > 0) {
			$pidcar = $pnewidcar;
		}
		$pcustdata = VikRequest::getString('custdata', '', 'request');
		$pareprices = VikRequest::getString('areprices', '', 'request');
		$ppriceid = VikRequest::getInt('priceid', '', 'request');
		$ptotpaid = VikRequest::getString('totpaid', '', 'request');
		//VikRentCar 1.7
		$pstandbyquick = VikRequest::getString('standbyquick', '', 'request');
		$pstandbyquick = $pstandbyquick == "1" ? 1 : 0;
		$pnotifycust = VikRequest::getString('notifycust', '', 'request');
		$pnotifycust = $pnotifycust == "1" ? 1 : 0;
		//
		$pcust_cost = VikRequest::getFloat('cust_cost', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pextracn = VikRequest::getVar('extracn', array());
		$pextracc = VikRequest::getVar('extracc', array());
		$pextractx = VikRequest::getVar('extractx', array());
		$isdue = 0;
		$tot_taxes = 0;
		$dbo = JFactory::getDbo();
		$actnow = time();
		$nowdf = VikRentCar::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		if (!empty($pidorder)) {
			$first = VikRentCar::getDateTimestamp($ppickupdate, $ppickuph, $ppickupm);
			$second = VikRentCar::getDateTimestamp($preleasedate, $preleaseh, $preleasem);
			if ($second > $first) {
				$q = "SELECT `units` FROM `#__vikrentcar_cars` WHERE `id`=".$dbo->quote($pidcar).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$units = $dbo->loadResult();
				//vikrentcar 1.5
				$checkhourly = false;
				$checkhourscharges = 0;
				$hoursdiff = 0;
				$secdiff = $second - $first;
				$daysdiff = $secdiff / 86400;
				if (is_int($daysdiff)) {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					}
				} else {
					if ($daysdiff < 1) {
						$daysdiff = 1;
						$checkhourly = true;
						$ophours = $secdiff / 3600;
						$hoursdiff = intval(round($ophours));
						if ($hoursdiff < 1) {
							$hoursdiff = 1;
						}
					} else {
						$sum = floor($daysdiff) * 86400;
						$newdiff = $secdiff - $sum;
						$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
						if ($maxhmore >= $newdiff) {
							$daysdiff = floor($daysdiff);
						} else {
							$daysdiff = ceil($daysdiff);
							/**
							 * Apply proper rounding with gratuity period.
							 * 
							 * @since 	1.15.1 (J) - 1.3.2 (WP)
							 */
							$ehours_float = ($newdiff - $maxhmore) / 3600;
							$ehours = intval(round($ehours_float));
							$ehours = !$ehours && $ehours_float > 0 && $maxhmore > 0 ? 1 : $ehours;
							$checkhourscharges = $ehours;
							if ($checkhourscharges > 0) {
								$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
							}
						}

					}
				}

				/**
				 * We allow the administrator to force the update of a rental order
				 * even if the car is fully booked or locked in the new dates.
				 * 
				 * @since 	1.14.5 (J) - 1.2.0 (WP)
				 */
				$pforce_availability = VikRequest::getInt('force_av', 0, 'request');
				$forced_availability = false;
				$history_descr = '';

				$opertwounits = true;
				$check = "SELECT `b`.`id`,`b`.`ritiro`,`b`.`consegna`,`b`.`realback`,`b`.`stop_sales`,`o`.`id` AS `idorder` 
					FROM `#__vikrentcar_busy` AS `b` 
					LEFT JOIN `#__vikrentcar_orders` AS `o` ON `o`.`idbusy`=`b`.`id` 
					WHERE `b`.`idcar`=" . (int)$pidcar . " AND `b`.`id`!=" . $dbo->quote($pidbusy) . " AND `b`.`realback` >= " . $first . " 
					ORDER BY `b`.`ritiro` ASC;";
				$dbo->setQuery($check);
				$busy = $dbo->loadAssocList();
				if ($busy) {
					$opertwounits = VikRentCar::carBookable($pidcar, $units, $first, $second, $busy);
				}
				//
				$is_car_locked = !VikRentCar::carNotLocked($pidcar, $units, $first, $second);
				if ($pforce_availability || !$is_car_locked) {
					// car is not temporarily locked, or forced availability
					if ($is_car_locked && $pforce_availability) {
						// turn flag on
						$forced_availability = true;
						$history_descr = "\n" . JText::_('VRCAVAILABILITYFORCED');
					}
					if ($pforce_availability || $opertwounits) {
						// car is not fully booked, or forced availability
						if (!$opertwounits && $pforce_availability) {
							// turn flag on
							$forced_availability = true;
							$history_descr = "\n" . JText::_('VRCAVAILABILITYFORCED');
						}
						$doup = false;
						//vikrentcar 1.5
						if ($checkhourly) {
							$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `idcar`=" . (int)$pidcar . " AND `hours`=" . (int)$hoursdiff . " AND `idprice`=" . (int)$ppriceid . ";";
						} else {
							$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `idcar`=" . (int)$pidcar . " AND `days`=" . (int)$daysdiff . " AND `idprice`=" . (int)$ppriceid . ";";
						}
						//
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$dispcost = $dbo->loadAssocList();
							//vikrentcar 1.5
							if ($checkhourly) {
								foreach ($dispcost as $kt => $vt) {
									$dispcost[$kt]['days'] = 1;
								}
							}
							$doup = true;
						} else {
							//there are no hourly prices
							if ($checkhourly) {
								$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `idcar`=" . (int)$pidcar . " AND `days`=" . (int)$daysdiff . " AND `idprice`=" . (int)$ppriceid . ";";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$dispcost = $dbo->loadAssocList();
									$doup = true;
								}
							}
						}
						if (isset($dispcost) && is_array($dispcost) && $checkhourscharges > 0 && $aehourschbasp === true) {
							$dispcost = VikRentCar::applyExtraHoursChargesCar($dispcost, $pidcar, $checkhourscharges, $daysdiff);
						}
						//VRC 1.11 Custom Rate
						$set_custom_rate = 0;
						if (!$doup && empty($ppriceid) && !empty($pcust_cost) && floatval($pcust_cost) > 0) {
							$doup = true;
							$set_custom_rate = $pcust_cost;
						}
						//
						if ($doup === true || intval($pidcar) != intval($origidcar)) {
							$realback = VikRentCar::getHoursCarAvail() * 3600;
							$realback += $second;
							if (!empty($pidbusy)) {
								$q = "UPDATE `#__vikrentcar_busy` SET `idcar`=".(int)$pidcar.",`ritiro`='".$first."', `consegna`='".$second."', `realback`='".$realback."' WHERE `id`=".$dbo->quote($pidbusy).";";
								$dbo->setQuery($q);
								$dbo->execute();
							}
							$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=" . (int)$pidorder . ";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() != 1) {
								throw new Exception("Order not found", 404);
								
							}
							$orderdata = $dbo->loadAssocList();
							$qfinal_args = array();
							$qfinal_args['custdata'] = $pcustdata;
							$qfinal_args['idcar'] = (int)$pidcar;
							// we do not use $daysdiff due to the extra hours charges that may override the duration
							$qfinal_args['days'] = isset($dispcost) ? (int)$dispcost[0]['days'] : $daysdiff;
							//
							$qfinal_args['ritiro'] = (int)$first;
							$qfinal_args['consegna'] = (int)$second;
							if (is_array($dispcost) && $doup === true && empty($set_custom_rate)) {
								$qfinal_args['idtar'] = (int)$dispcost[0]['id'];
								$qfinal_args['cust_cost'] = null;
								$qfinal_args['cust_idiva'] = null;
								if ($checkhourscharges > 0 && $aehourschbasp === false) {
									$dispcost = VikRentCar::extraHoursSetPreviousFareCar($dispcost, $pidcar, $checkhourscharges, $daysdiff);
									$dispcost = VikRentCar::applySeasonsCar($dispcost, $first, $second, $pidplace);
									$dispcost = VikRentCar::applyExtraHoursChargesCar($dispcost, $pidcar, $checkhourscharges, $daysdiff, true);
									// update the days of rental
									$qfinal_args['days'] = (int)$dispcost[0]['days'];
								} else {
									$dispcost = VikRentCar::applySeasonsCar($dispcost, $first, $second, $pidplace);
								}
								$cost_with = VikRentCar::sayCostPlusIva($dispcost[0]['cost'], $dispcost[0]['idprice']);
								$cost_net = VikRentCar::sayCostMinusIva($dispcost[0]['cost'], $dispcost[0]['idprice']);
								$isdue += $cost_with;
								$tot_taxes += $cost_with - $cost_net;
							} elseif ($set_custom_rate > 0) {
								$qfinal_args['idtar'] = null;
								$qfinal_args['cust_cost'] = $set_custom_rate;
								if (!empty($paliq)) {
									$qfinal_args['cust_idiva'] = (int)$paliq;
								}
								$cust_plus_tax = VikRentCar::sayCustCostPlusIva($set_custom_rate, (int)$paliq);
								$isdue += $cust_plus_tax;
								$cust_net = VikRentCar::sayCustCostMinusIva($set_custom_rate, (int)$paliq);
								$tot_taxes += ($cust_plus_tax - $cust_net);
							} elseif ($doup === false) {
								$qfinal_args['idtar'] = null;
								if (intval($pidcar) != intval($origidcar)) {
									VikError::raiseNotice('', JText::_('VRCUPDBUSYCARSWITCHED'));
								}
							}
							// we update $daysdiff with the nwely calculated days from the tariffs
							$daysdiff = isset($dispcost) ? (int)$dispcost[0]['days'] : $daysdiff;
							//
							$q = "SELECT * FROM `#__vikrentcar_optionals`;";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$toptionals = $dbo->loadAssocList();
								$wop = '';
								foreach ($toptionals as $opt) {
									$tmpvar = VikRequest::getString('optid'.$opt['id'], '', 'request');
									if (!empty($tmpvar)) {
										$wop .= $opt['id'].":".$tmpvar.";";
										$realcost = intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff * $tmpvar) : ($opt['cost'] * $tmpvar);
										$basequancost = intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff) : $opt['cost'];
										if (!empty($opt['maxprice']) && $opt['maxprice'] > 0 && $basequancost > $opt['maxprice']) {
											$realcost = $opt['maxprice'];
											if (intval($opt['hmany']) == 1 && intval($tmpvar) > 1) {
												$realcost = $opt['maxprice'] * $tmpvar;
											}
										}
										$opt_with = VikRentCar::sayOptionalsPlusIva($realcost, $opt['idiva']);
										$opt_without = VikRentCar::sayOptionalsMinusIva($realcost, $opt['idiva']);
										$isdue += $opt_with;
										$tot_taxes += ($opt_with - $opt_without);
									}
								}
								$qfinal_args['optionals'] = $wop;
							}
							if ($pidplace != $orderdata[0]['idplace']) {
								$qfinal_args['idplace'] = $pidplace;
							}
							if ($pidreturnplace != $orderdata[0]['idreturnplace']) {
								$qfinal_args['idreturnplace'] = $pidreturnplace;
							}
							if (strlen($ptotpaid) > 0) {
								$qfinal_args['totpaid'] = floatval($ptotpaid);
							} else {
								$qfinal_args['totpaid'] = null;
							}
							//calculate the extra costs and increase taxes + isdue
							$extracosts_arr = array();
							if (count($pextracn) > 0) {
								foreach ($pextracn as $eck => $ecn) {
									if (strlen($ecn) > 0 && array_key_exists($eck, $pextracc) && floatval($pextracc[$eck]) >= 0.00) {
										$ecidtax = array_key_exists($eck, $pextractx) && intval($pextractx[$eck]) > 0 ? (int)$pextractx[$eck] : '';
										$extracosts_arr[] = array('name' => $ecn, 'cost' => (float)$pextracc[$eck], 'idtax' => $ecidtax);
										$ecplustax = !empty($ecidtax) ? VikRentCar::sayOptionalsPlusIva((float)$pextracc[$eck], $ecidtax, $orderdata[0]) : (float)$pextracc[$eck];
										$ecminustax = !empty($ecidtax) ? VikRentCar::sayOptionalsMinusIva((float)$pextracc[$eck], $ecidtax, $orderdata[0]) : (float)$pextracc[$eck];
										$isdue += $ecplustax;
										$tot_taxes += ($ecplustax - $ecminustax);
									}
								}
							}
							if (count($extracosts_arr) > 0) {
								$qfinal_args['extracosts'] = json_encode($extracosts_arr);
							} else {
								$qfinal_args['extracosts'] = null;
							}
							//end extra costs

							/**
							 * We are now calculating automatically also the location and out-of-hours fees.
							 * 
							 * 
							 * @since 	1.1.0
							 */
							// location fees
							if (!empty($pidplace) && !empty($pidreturnplace)) {
								$locfee = VikRentCar::getLocFee($pidplace, $pidreturnplace);
								if ($locfee) {
									// location fees overrides
									if (strlen($locfee['losoverride']) > 0) {
										$arrvaloverrides = array();
										$valovrparts = explode('_', $locfee['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												$arrvaloverrides[(int)$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists((int)$daysdiff, $arrvaloverrides)) {
											$locfee['cost'] = $arrvaloverrides[$daysdiff];
										}
									}
									// end location fees overrides
									$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $daysdiff) : $locfee['cost'];
									$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva']);
									$locfeewithout = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva']);
									$isdue += $locfeewith;
									$tot_taxes += ($locfeewith - $locfeewithout);
								}
							}
							// out of hours fees
							$oohfee = VikRentCar::getOutOfHoursFees($pidplace, $pidreturnplace, $first, $second, array('id' => (int)$pidcar));
							if (count($oohfee)) {
								$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
								$oohfeewithout = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
								$isdue += $oohfeewith;
								$tot_taxes += ($oohfeewith - $oohfeewithout);
							}
							//

							if (strlen($porder_total) > 0) {
								// the order total amount can be forced manually to a specific value
								$qfinal_args['order_total'] = floatval($porder_total);
							} elseif ($isdue > 0) {
								// VRC 1.12 if no order total specified, update it to what the value would be at today's date
								$qfinal_args['order_total'] = floatval($isdue);
							}

							/**
							 * Make sure to update the total amount of taxes.
							 * 
							 * @since 	1.1.0
							 */
							$qfinal_args['tot_taxes'] = floatval($tot_taxes);
							//

							$order_record = (object)$qfinal_args;
							$order_record->id = (int)$orderdata[0]['id'];
							$dbo->updateObject('#__vikrentcar_orders', $order_record, 'id', true);

							// Booking History
							$user = JFactory::getUser();
							VikRentCar::getOrderHistoryInstance()->setBid($orderdata[0]['id'])->store('MB', "({$user->name}) " . VikRentCar::getLogBookingModification($orderdata[0]) . $history_descr);
							//

							$mainframe->enqueueMessage(JText::_('RESUPDATED'));
							//VikRentCar 1.7
							if ($pstandbyquick == 1 && !empty($pidbusy)) {
								//remove busy because this is an order from quick reservation with standby status
								$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id`=".(int)$pidbusy.";";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentcar_orders` SET `idbusy`=NULL WHERE `id`=".(int)$orderdata[0]['id'].";";
								$dbo->setQuery($q);
								$dbo->execute();
							}
							if ($pnotifycust == 1) {
								$this->do_resendordemail($orderdata[0]['id'], true);
								return;
							}
							//
						}
					} else {
						// raise errors
						VikError::raiseWarning('', JText::_('VRCARNOTRIT')." ".date($df.' H:i', $first)." ".JText::_('VRCARNOTCONSTO')." ".date($df.' H:i', $second));
						VikError::raiseWarning('', JText::_('VRCFORCEAVAILABILITYCONF') . ' <a class="btn btn-danger" href="index.php?option=com_vikrentcar&task=editbusy&return=order&force_av=1&cid[]=' . $pidorder . '">' . JText::_('VRCFORCEAVAILABILITY') . '</a>');
					}
				} else {
					// raise errors
					VikError::raiseWarning('', JText::_('ERRCARLOCKED'));
					VikError::raiseWarning('', JText::_('VRCFORCEAVAILABILITYCONF') . ' <a class="btn btn-danger" href="index.php?option=com_vikrentcar&task=editbusy&return=order&force_av=1&cid[]=' . $pidorder . '">' . JText::_('VRCFORCEAVAILABILITY') . '</a>');
				}
			} else {
				VikError::raiseWarning('', JText::_('ERRPREV'));
			}
			if (intval($pidcar) != intval($origidcar)) {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=editbusy&return=".$preturn."&cid[]=".$pidorder);
			} elseif ($preturn == 'order') {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=".$pidorder);
			} else {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=calendar&cid[]=".$pidcar);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=orders");
		}
	}

	public function editorder() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editorder'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editbusy() {
		VikRentCarHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editbusy'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function checkversion() {
		$params = new stdClass;
		$params->version 	= VIKRENTCAR_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikrentcar';

		$result = array();

		if (!count($result)) {
			$result = new stdClass;
			$result->status = 0;
		} else {
			$result = $result[0];
		}

		echo json_encode($result);
		exit;
	}

	public function updateprogram() {
		$params = new stdClass;
		$params->version 	= VIKRENTCAR_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikrentcar';

		$result = array();

		if (!count($result) || !$result[0]) {
			if (class_exists('JEventDispatcher')) {
				$result = $dispatcher->trigger('checkVersion', array(&$params));
			} else {
				$app = JFactory::getApplication();
				if (method_exists($app, 'triggerEvent')) {
					$result = $app->triggerEvent('checkVersion', array(&$params));
				}
			}
		}

		if (!count($result) || !$result[0]->status || !$result[0]->response->status) {
			exit('Error, plugin disabled');
		}

		JToolbarHelper::title(JText::_('VRMAINTITLEUPDATEPROGRAM'));

		VikRentCarHelper::pUpdateProgram($result[0]->response);
	}

	public function updateprogramlaunch() {
		$params = new stdClass;
		$params->version 	= VIKRENTCAR_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikrentcar';

		$json = new stdClass;
		$json->status = false;

		echo json_encode($json);
		exit;
	}

	public function customers() {
		VikRentCarHelper::printHeader("customers");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customers'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newcustomer() {
		VikRentCarHelper::printHeader("customers");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editcustomer() {
		VikRentCarHelper::printHeader("customers");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function removecustomers() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			$cpin = VikRentCar::getCPinIstance();
			foreach ($ids as $d) {
				$cpin->pluginCustomerSync($d, 'delete');
				$q = "DELETE FROM `#__vikrentcar_customers` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
	}

	public function savecustomer() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		$pbid = VikRequest::getInt('bid', '', 'request');
		if (!empty($pfirst_name) && !empty($plast_name)) {
			$cpin = VikRentCar::getCPinIstance();
			$q = "SELECT * FROM `#__vikrentcar_customers` WHERE `email`=".$dbo->quote($pemail)." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				if (empty($ppin)) {
					$ppin = $cpin->generateUniquePin();
				} elseif ($cpin->pinExists($ppin)) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = !empty($pimg['tmp_name']) ? getimagesize($pimg['tmp_name']) : [];
					if ($check[2] & imagetypes()) {
						if (VikRentCar::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$q = "INSERT INTO `#__vikrentcar_customers` (`first_name`,`last_name`,`email`,`phone`,`country`,`pin`,`ujid`,`address`,`city`,`zip`,`doctype`,`docnum`,`docimg`,`notes`,`company`,`vat`,`gender`,`bdate`,`pbirth`) VALUES(".$dbo->quote($pfirst_name).", ".$dbo->quote($plast_name).", ".$dbo->quote($pemail).", ".$dbo->quote($pphone).", ".$dbo->quote($pcountry).", ".$dbo->quote($ppin).", ".$dbo->quote($pujid).", ".$dbo->quote($paddress).", ".$dbo->quote($pcity).", ".$dbo->quote($pzip).", ".$dbo->quote($pdoctype).", ".$dbo->quote($pdocnum).", ".$dbo->quote($gimg).", ".$dbo->quote($pnotes).", ".$dbo->quote($pcompany).", ".$dbo->quote($pvat).", ".$dbo->quote($pgender).", ".$dbo->quote($pbdate).", ".$dbo->quote($ppbirth).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$lid = $dbo->insertid();
				$cpin->pluginCustomerSync($lid, 'insert');
				if (!empty($lid)) {
					$mainframe->enqueueMessage(JText::_('VRCUSTOMERSAVED'));
				}
				// check if coming from a specific task
				if (!empty($pgoto) && !empty($pbid)) {
					$cpin->setNewPin($ppin);
					$cpin->setNewCustomerId($lid);
					$cpin->saveCustomerBooking($pbid);
					$mainframe->redirect(base64_decode($pgoto));
					exit;
				}
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				if (!empty($pgoto) && !empty($pbid)) {
					// check if coming from a specific task
					$cpin->setNewPin($ex_customer['pin']);
					$cpin->setNewCustomerId($ex_customer['id']);
					$cpin->saveCustomerBooking($pbid);
					VikError::raiseWarning('', JText::_('VRERRCUSTOMEREMAILEXISTS').' ('.$ex_customer['first_name'].' '.$ex_customer['last_name'].')');
					$mainframe->redirect(base64_decode($pgoto));
					exit;
				} else {
					VikError::raiseWarning('', JText::_('VRERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikrentcar&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
				}
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
	}

	public function updatecustomer() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_updatecustomer();
	}

	public function updatecustomerstay() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->do_updatecustomer(true);
	}

	private function do_updatecustomer($stay = false) {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pbid = VikRequest::getInt('bid', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		if (!empty($pwhere) && !empty($pfirst_name) && !empty($plast_name)) {
			$q = "SELECT * FROM `#__vikrentcar_customers` WHERE `id`=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$customer = $dbo->loadAssoc();
			} else {
				$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
				exit;
			}
			$q = "SELECT * FROM `#__vikrentcar_customers` WHERE `email`=".$dbo->quote($pemail)." AND `id`!=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				$cpin = VikRentCar::getCPinIstance();
				if (empty($ppin)) {
					$ppin = $customer['pin'];
				} elseif ($cpin->pinExists($ppin, $customer['pin'])) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = !empty($pimg['tmp_name']) ? getimagesize($pimg['tmp_name']) : [];
					if ($check[2] & imagetypes()) {
						if (VikRentCar::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$q = "UPDATE `#__vikrentcar_customers` SET `first_name`=".$dbo->quote($pfirst_name).",`last_name`=".$dbo->quote($plast_name).",`email`=".$dbo->quote($pemail).",`phone`=".$dbo->quote($pphone).",`country`=".$dbo->quote($pcountry).",`pin`=".$dbo->quote($ppin).",`ujid`=".$dbo->quote($pujid).",`address`=".$dbo->quote($paddress).",`city`=".$dbo->quote($pcity).",`zip`=".$dbo->quote($pzip).",`doctype`=".$dbo->quote($pdoctype).",`docnum`=".$dbo->quote($pdocnum).(!empty($gimg) ? ",`docimg`=".$dbo->quote($gimg) : "").",`notes`=".$dbo->quote($pnotes).",`company`=".$dbo->quote($pcompany).",`vat`=".$dbo->quote($pvat).",`gender`=".$dbo->quote($pgender).",`bdate`=".$dbo->quote($pbdate).",`pbirth`=".$dbo->quote($ppbirth)." WHERE `id`=".(int)$pwhere.";";
				$dbo->setQuery($q);
				$dbo->execute();
				$cpin->pluginCustomerSync($pwhere, 'update');
				$mainframe->enqueueMessage(JText::_('VRCUSTOMERSAVED'));
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				if (!empty($pgoto)) {
					// check if coming from a specific task
					VikError::raiseWarning('', JText::_('VRERRCUSTOMEREMAILEXISTS').' ('.$ex_customer['first_name'].' '.$ex_customer['last_name'].')');
					$mainframe->redirect(base64_decode($pgoto));
					exit;
				} else {
					VikError::raiseWarning('', JText::_('VRERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikrentcar&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editcustomer&cid[]=".$pwhere);
					exit;
				}
			}
		}
		// check if coming from a specific task
		if (!empty($pgoto)) {
			$mainframe->redirect(base64_decode($pgoto));
			exit;
		}
		
		if ($stay) {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editcustomer&cid[]=".$pwhere);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
		}
	}

	public function cancelcustomer() {
		$mainframe = JFactory::getApplication();
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		if (!empty($pgoto)) {
			$mainframe->redirect(base64_decode($pgoto));
			exit;
		}
		$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
	}

	public function searchcustomer() {
		//to be called via ajax
		$kw = VikRequest::getString('kw', '', 'request');
		$nopin = VikRequest::getInt('nopin', '', 'request');
		$email = VikRequest::getInt('email', 0, 'request');
		$cstring = '';
		if (strlen($kw) > 0) {
			$dbo = JFactory::getDbo();
			if ($nopin > 0) {
				//page all bookings
				$q = "SELECT * FROM `#__vikrentcar_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC LIMIT 30;";
			} elseif ($email > 0) {
				// page calendar for checking if an email exists
				$q = "SELECT `first_name`, `last_name`, `email` FROM `#__vikrentcar_customers` WHERE `email`=".$dbo->quote($kw).";";
			} else {
				//page calendar
				$q = "SELECT * FROM `#__vikrentcar_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." OR `pin` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC;";
			}
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$customers = $dbo->loadAssocList();
				$cust_old_fields = array();
				$cstring_search = '<div class="vrc-custsearchres-inner">' . "\n";
				foreach ($customers as $k => $v) {
					$cstring_search .= '<div class="vrc-custsearchres-entry" data-custid="'.$v['id'].'" data-email="'.$v['email'].'" data-phone="'.addslashes($v['phone']).'" data-country="'.$v['country'].'" data-pin="'.$v['pin'].'" data-firstname="'.addslashes($v['first_name']).'" data-lastname="'.addslashes($v['last_name']).'">'."\n";
					$cstring_search .= '<span class="vrc-custsearchres-cflag">';
					if (is_file(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$v['country'].'.png')) {
						$cstring_search .= '<img src="'.VRC_ADMIN_URI.'resources/countries/'.$v['country'].'.png'.'" title="'.$v['country'].'" class="vrc-country-flag"/>'."\n";
					} else {
						$cstring_search .= '<i class="' . VikRentCarIcons::i('globe') . '"></i>';
					}
					$cstring_search .= '</span>';
					$cstring_search .= '<span class="vrc-custsearchres-name" title="'.$v['email'].'">'.$v['first_name'].' '.$v['last_name'].'</span>'."\n";
					if (!($nopin > 0)) {
						$cstring_search .= '<span class="vrc-custsearchres-pin">'.$v['pin'].'</span>'."\n";
					}
					$cstring_search .= '</div>'."\n";
					if (!empty($v['cfields'])) {
						$oldfields = json_decode($v['cfields'], true);
						if (is_array($oldfields) && count($oldfields)) {
							$cust_old_fields[$v['id']] = $oldfields;
						}
					}
				}
				$cstring_search .= '</div>'."\n";
				$cstring = json_encode(array(($nopin > 0 ? '' : $cust_old_fields), $cstring_search));
			}
		}
		/**
		 * The HTML content is built directly in this task by escaping the necessary values,
		 * and no third party plugins could interfere. We cannot escape this HTML string,
		 * nor can we convert special chars into HTML entities, as this is the response
		 * of an AJAX request, and the HTML code needs to be displayed accordingly.
		 * If we were to escape the HTML string, then the AJAX response would be useless,
		 * as it would be HTML code converted into text with HTML entities.
		 */
		echo $cstring;
		exit;
	}

	public function exportcustomers() {
		//we do not set the menu for this view
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'exportcustomers'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function exportcustomerslaunch() {
		$cid = VikRequest::getVar('cid', array(0));
		$dbo = JFactory::getDbo();
		$pnotes = VikRequest::getInt('notes', '', 'request');
		$pscanimg = VikRequest::getInt('scanimg', '', 'request');
		$ppin = VikRequest::getInt('pin', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$pdatefilt = VikRequest::getInt('datefilt', '', 'request');
		$clauses = array();
		if (count($cid) > 0 && !empty($cid[0])) {
			$clauses[] = "`c`.`id` IN (".implode(', ', $cid).")";
		}
		if (!empty($pcountry)) {
			$clauses[] = "`c`.`country`=".$dbo->quote($pcountry);
		}
		$datescol = '`bk`.`ts`';
		if ($pdatefilt > 0) {
			if ($pdatefilt == 1) {
				$datescol = '`bk`.`ts`';
			} elseif ($pdatefilt == 2) {
				$datescol = '`bk`.`ritiro`';
			} elseif ($pdatefilt == 3) {
				$datescol = '`bk`.`consegna`';
			}
		}
		if (!empty($pfromdate)) {
			$from_ts = VikRentCar::getDateTimestamp($pfromdate, 0, 0);
			$clauses[] = $datescol.">=".$from_ts;
		}
		if (!empty($ptodate)) {
			$to_ts = VikRentCar::getDateTimestamp($ptodate, 23, 59);
			$clauses[] = $datescol."<=".$to_ts;
		}
		//this query below is safe with the error #1055 when sql_mode=only_full_group_by
		$q = "SELECT `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,".
			"(SELECT COUNT(*) FROM `#__vikrentcar_customers_orders` AS `co` WHERE `co`.`idcustomer`=`c`.`id`) AS `tot_bookings`,".
			"`cy`.`country_3_code`,`cy`.`country_name` ".
			"FROM `#__vikrentcar_customers` AS `c` LEFT JOIN `#__vikrentcar_countries` `cy` ON `cy`.`country_3_code`=`c`.`country` ".
			"LEFT JOIN `#__vikrentcar_customers_orders` `co` ON `co`.`idcustomer`=`c`.`id` ".
			"LEFT JOIN `#__vikrentcar_orders` `bk` ON `bk`.`id`=`co`.`idorder`".
			(count($clauses) > 0 ? " WHERE ".implode(' AND ', $clauses) : "")." 
			GROUP BY `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,`cy`.`country_3_code`,`cy`.`country_name` ".
			"ORDER BY `c`.`last_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!($dbo->getNumRows() > 0)) {
			VikError::raiseWarning('', JText::_('VRCNORECORDSCSVCUSTOMERS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikrentcar&task=customers");
			exit;
		}
		$customers = $dbo->loadAssocList();
		$csvlines = array();
		$csvheadline = array('ID', JText::_('VRCUSTOMERLASTNAME'), JText::_('VRCUSTOMERFIRSTNAME'), JText::_('VRCUSTOMEREMAIL'), JText::_('VRCUSTOMERPHONE'), JText::_('VRCUSTOMERADDRESS'), JText::_('VRCUSTOMERCITY'), JText::_('VRCUSTOMERZIP'), JText::_('VRCUSTOMERCOUNTRY'), JText::_('VRCUSTOMERTOTBOOKINGS'));
		if ($ppin > 0) {
			$csvheadline[] = JText::_('VRCUSTOMERPIN');
		}
		if ($pscanimg > 0) {
			$csvheadline[] = JText::_('VRCUSTOMERDOCTYPE');
			$csvheadline[] = JText::_('VRCUSTOMERDOCNUM');
			$csvheadline[] = JText::_('VRCUSTOMERDOCIMG');
		}
		if ($pnotes > 0) {
			$csvheadline[] = JText::_('VRCUSTOMERNOTES');
		}
		$csvlines[] = $csvheadline;
		foreach ($customers as $customer) {
			$csvcustomerline = array($customer['id'], $customer['last_name'], $customer['first_name'], $customer['email'], $customer['phone'], $customer['address'], $customer['city'], $customer['zip'], $customer['country_name'], $customer['tot_bookings']);
			if ($ppin > 0) {
				$csvcustomerline[] = $customer['pin'];
			}
			if ($pscanimg > 0) {
				$csvcustomerline[] = $customer['doctype'];
				$csvcustomerline[] = $customer['docnum'];
				$csvcustomerline[] = (!empty($customer['docimg']) ? VRC_ADMIN_URI.'resources/idscans/'.$customer['docimg'] : '');
			}
			if ($pnotes > 0) {
				$csvcustomerline[] = $customer['notes'];
			}	
			$csvlines[] = $csvcustomerline;
		}
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="customers_export_'.(!empty($pcountry) ? strtolower($pcountry).'_' : '').date('Y-m-d').'.csv"');
		$outstream = fopen("php://output", 'w');
		foreach ($csvlines as $csvline) {
			fputcsv($outstream, $csvline);
		}
		fclose($outstream);
		exit;
	}

	public function sendcustomemail() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$vrc_tn = VikRentCar::getTranslator();
		$pbid = VikRequest::getInt('bid', '', 'request');
		$pemailsubj = VikRequest::getString('emailsubj', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pemailcont = VikRequest::getString('emailcont', '', 'request', VIKREQUEST_ALLOWRAW);
		$pemailfrom = VikRequest::getString('emailfrom', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
		$pgoto = !empty($pgoto) ? urldecode($pgoto) : 'index.php?option=com_vikrentcar';
		if (!empty($pemail) && !empty($pemailcont)) {
			$email_attach = null;
			jimport('joomla.filesystem.file');
			$pemailattch = VikRequest::getVar('emailattch', null, 'files', 'array');
			if (isset($pemailattch) && strlen(trim($pemailattch['name']))) {
				$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pemailattch['name'])));
				$src = $pemailattch['tmp_name'];
				$dest = VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
				$j = "";
				if (file_exists($dest.$filename)) {
					$j = rand(171, 1717);
					while (file_exists($dest.$j.$filename)) {
						$j++;
					}
				}
				$finaldest = $dest.$j.$filename;
				if (VikRentCar::uploadFile($src, $finaldest)) {
					$email_attach = $finaldest;
				} else {
					VikError::raiseWarning('', 'Error uploading the attachment. Email not sent.');
					$mainframe->redirect($pgoto);
					exit;
				}
			}
			//VRC 1.12 - special tags for the custom email template files and messages
			$orig_mail_cont = $pemailcont;
			if (strpos($pemailcont, '{') !== false && strpos($pemailcont, '}') !== false) {
				$order = array();
				$q = "SELECT `o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_customers_orders` `co` ON `co`.`idorder`=`o`.`id` AND `co`.`idorder`=".(int)$pbid." LEFT JOIN `#__vikrentcar_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikrentcar_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `o`.`id`=".(int)$pbid.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$order = $dbo->loadAssoc();
				}
				// parse the special tokens to build the message
				$pemailcont = VikRentCar::parseSpecialTokens($order, $pemailcont);
			}
			//
			$is_html = (strpos($pemailcont, '<') !== false && strpos($pemailcont, '>') !== false);
			$pemailcont = $is_html ? nl2br($pemailcont) : $pemailcont;
			$vrc_app = new VrcApplication();
			$vrc_app->sendMail($pemailfrom, $pemailfrom, $pemail, $pemailfrom, $pemailsubj, $pemailcont, $is_html, 'base64', $email_attach);
			$mainframe->enqueueMessage(JText::_('VRSENDEMAILOK'));
			if ($email_attach !== null) {
				@unlink($email_attach);
			}
			// Booking History
			VikRentCar::getOrderHistoryInstance()->setBid($pbid)->store('CE', nl2br($pemailsubj . "\n\n" . $pemailcont));
			//
			//Save email template for future sending
			$config_rec_exists = false;
			$emtpl = array(
				'emailsubj' => $pemailsubj,
				'emailcont' => $orig_mail_cont,
				'emailfrom' => $pemailfrom
			);
			$cur_emtpl = array();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$config_rec_exists = true;
				$cur_emtpl = $dbo->loadResult();
				$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
				$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
			}
			if (count($cur_emtpl) > 0) {
				$existing_subj = false;
				foreach ($cur_emtpl as $emk => $emv) {
					if (array_key_exists('emailsubj', $emv) && $emv['emailsubj'] == $emtpl['emailsubj']) {
						$cur_emtpl[$emk] = $emtpl;
						$existing_subj = true;
						break;
					}
				}
				if ($existing_subj === false) {
					$cur_emtpl[] = $emtpl;
				}
			} else {
				$cur_emtpl[] = $emtpl;
			}
			if (count($cur_emtpl) > 10) {
				//Max 10 templates to avoid problems with the size of the field and truncated json strings
				$exceed = count($cur_emtpl) - 10;
				for ($tl=0; $tl < $exceed; $tl++) { 
					unset($cur_emtpl[$tl]);
				}
				$cur_emtpl = array_values($cur_emtpl);
			}
			if ($config_rec_exists === true) {
				$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
				$dbo->setQuery($q);
				$dbo->execute();
			} else {
				$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('customemailtpls', ".$dbo->quote(json_encode($cur_emtpl)).");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			//
		} else {
			VikError::raiseWarning('', JText::_('VRSENDEMAILERRMISSDATA'));
		}
		$mainframe->redirect($pgoto);
	}

	public function rmcustomemailtpl() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$tplind = VikRequest::getInt('tplind', '', 'request');
		if (empty($oid) || !(strlen($tplind) > 0)) {
			VikError::raiseWarning('', 'Missing Data.');
			$mainframe->redirect('index.php?option=com_vikrentcar');
			exit;
		}
		$cur_emtpl = array();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='customemailtpls';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cur_emtpl = $dbo->loadResult();
			$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
			$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
		} else {
			VikError::raiseWarning('', 'Missing Templates Record.');
			$mainframe->redirect('index.php?option=com_vikrentcar');
			exit;
		}
		if (array_key_exists($tplind, $cur_emtpl)) {
			unset($cur_emtpl[$tplind]);
			$cur_emtpl = count($cur_emtpl) > 0 ? array_values($cur_emtpl) : array();
			$q = "UPDATE `#__vikrentcar_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect('index.php?option=com_vikrentcar&task=editorder&cid[]='.$oid.'&customemail=1');
		exit;
	}

	public function pmsreports() {
		VikRentCarHelper::printHeader("pmsreports");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'pmsreports'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function ratesoverv() {
		VikRentCarHelper::printHeader("ratesoverv");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'ratesoverv'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function restrictions() {
		VikRentCarHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'restrictions'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function newrestriction() {
		VikRentCarHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerestriction'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function editrestriction() {
		VikRentCarHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerestriction'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	public function createrestriction() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		$pname = VikRequest::getString('name', '', 'request');
		$pmonth = VikRequest::getInt('month', '', 'request');
		$pmonth = empty($pmonth) ? 0 : $pmonth;
		$pname = empty($pname) ? 'Restriction '.$pmonth : $pname;
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$pdto = VikRequest::getString('dto', '', 'request');
		$pwday = VikRequest::getString('wday', '', 'request');
		$pwdaytwo = VikRequest::getString('wdaytwo', '', 'request');
		$pwdaytwo = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday == $pwdaytwo ? '' : $pwdaytwo;
		$pcomboa = VikRequest::getString('comboa', '', 'request');
		$pcomboa = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboa : '';
		$pcombob = VikRequest::getString('combob', '', 'request');
		$pcombob = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombob : '';
		$pcomboc = VikRequest::getString('comboc', '', 'request');
		$pcomboc = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboc : '';
		$pcombod = VikRequest::getString('combod', '', 'request');
		$pcombod = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombod : '';
		$combostr = '';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboa) ? $pcomboa.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombob) ? $pcombob.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboc) ? $pcomboc.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombod) ? $pcombod : '';
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = empty($pmaxlos) ? 0 : $pmaxlos;
		$pmultiplyminlos = VikRequest::getString('multiplyminlos', '', 'request');
		$pmultiplyminlos = empty($pmultiplyminlos) ? 0 : 1;
		$pallcars = VikRequest::getString('allcars', '', 'request');
		$pallcars = $pallcars == "1" ? 1 : 0;
		$pidcars = VikRequest::getVar('idcars', array(0));
		$ridr = '';
		$caridsforsess = array();
		if (!empty($pidcars) && @count($pidcars) && $pallcars == 0) {
			foreach ($pidcars as $idr) {
				if (empty($idr)) {
					continue;
				}
				$ridr .= '-'.$idr.'-;';
				$caridsforsess[] = (int)$idr;
			}
		} elseif ($pallcars > 0) {
			$q = "SELECT `id` FROM `#__vikrentcar_cars`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetchids = $dbo->loadAssocList();
				foreach ($fetchids as $fetchid) {
					$caridsforsess[] = (int)$fetchid['id'];
				}
			}
		}
		$pcta = VikRequest::getInt('cta', '', 'request');
		$pctd = VikRequest::getInt('ctd', '', 'request');
		$pctad = VikRequest::getVar('ctad', array());
		$pctdd = VikRequest::getVar('ctdd', array());
		if ($pminlos == 1 && strlen($pwday) == 0 && empty($pctad) && empty($pctdd)) {
			VikError::raiseWarning('', JText::_('VRUSELESSRESTRICTION'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikrentcar&task=newrestriction");
		} else {
			//check if there are restrictions for this month
			if ($pmonth > 0) {
				$q = "SELECT `id` FROM `#__vikrentcar_restrictions` WHERE `month`='".$pmonth."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					VikError::raiseWarning('', JText::_('VRRESTRICTIONMONTHEXISTS'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikrentcar&task=newrestriction");
				}
				$pdfrom = 0;
				$pdto = 0;
			} else {
				//dates range
				if (empty($pdfrom) || empty($pdto)) {
					VikError::raiseWarning('', JText::_('VRRESTRICTIONERRDRANGE'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikrentcar&task=newrestriction");
				} else {
					$pdfrom = VikRentCar::getDateTimestamp($pdfrom, 0, 0);
					$pdto = VikRentCar::getDateTimestamp($pdto, 0, 0);
				}
			}
			//CTA and CTD
			$setcta = array();
			$setctd = array();
			if ($pcta > 0 && count($pctad) > 0) {
				foreach ($pctad as $ctwd) {
					if (strlen($ctwd)) {
						$setcta[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			if ($pctd > 0 && count($pctdd) > 0) {
				foreach ($pctdd as $ctwd) {
					if (strlen($ctwd)) {
						$setctd[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			//
			$q = "INSERT INTO `#__vikrentcar_restrictions` (`name`,`month`,`wday`,`minlos`,`multiplyminlos`,`maxlos`,`dfrom`,`dto`,`wdaytwo`,`wdaycombo`,`allcars`,`idcars`,`ctad`,`ctdd`) VALUES(".$dbo->quote($pname).", '".$pmonth."', ".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").", '".$pminlos."', '".$pmultiplyminlos."', '".$pmaxlos."', ".$pdfrom.", ".$pdto.", ".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").", ".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").", ".$pallcars.", ".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", ".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", ".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL").");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			if (!empty($lid)) {
				/**
				 * Repeat restriction on the selected week days until the limit
				 * 
				 * @since 	1.14
				 */
				$prepeat = VikRequest::getInt('repeat', 0, 'request');
				$prepeatuntil = VikRequest::getString('repeatuntil', '', 'request');
				if ($prepeat > 0 && !empty($prepeatuntil) && $pdfrom > 0 && $pdto > 0) {
					$repeat_intervals = array();
					$start = getdate($pdfrom);
					$end = getdate($pdto);
					$wdays = array();
					while ($start[0] <= $end[0]) {
						// push requested week day
						array_push($wdays, $start['wday']);
						// next day
						$start = getdate(mktime($start['hours'], $start['minutes'], $start['seconds'], $start['mon'], ($start['mday'] + 1), $start['year']));
					}
					$dtuntil = VikRentCar::getDateTimestamp($prepeatuntil, 23, 59, 59);
					if (count($wdays) < 7 && $dtuntil > $pdto) {
						// increment end date for the repeat
						$end = getdate(mktime($end['hours'], $end['minutes'], $end['seconds'], $end['mon'], ($end['mday'] + 1), $end['year']));
						//
						$until_info = getdate($dtuntil);
						$interval = array();
						while ($end[0] <= $until_info[0]) {
							if (in_array($end['wday'], $wdays)) {
								if (!isset($interval['from'])) {
									$interval['from'] = $end[0];
								}
								$interval['to'] = $end[0];
							} else {
								if (isset($interval['from'])) {
									// append interval
									array_push($repeat_intervals, $interval);
									// reset interval
									$interval = array();
								}
							}
							// next day
							$end = getdate(mktime($end['hours'], $end['minutes'], $end['seconds'], $end['mon'], ($end['mday'] + 1), $end['year']));
						}
						if (isset($interval['from'])) {
							// append last hanging interval
							array_push($repeat_intervals, $interval);
						}
						if (count($repeat_intervals)) {
							// create the repeated records for the calculated intervals
							$repeat_count = 2;
							foreach ($repeat_intervals as $rp) {
								if (date('Y-m-d', $rp['from']) == date('Y-m-d', $rp['to'])) {
									// adjust time in case of equal dates (1 single day restriction)
									$rpfrom = getdate($rp['from']);
									$rpto = getdate($rp['to']);
									$rp['from'] = mktime(0, 0, 0, $rpfrom['mon'], $rpfrom['mday'], $rpfrom['year']);
									$rp['to'] = mktime(0, 0, 0, $rpto['mon'], $rpto['mday'], $rpto['year']);
								}
								// adjust name
								$restr_rp_name = $pname . " #{$repeat_count}";
								//
								$q = "INSERT INTO `#__vikrentcar_restrictions` (`name`,`month`,`wday`,`minlos`,`multiplyminlos`,`maxlos`,`dfrom`,`dto`,`wdaytwo`,`wdaycombo`,`allcars`,`idcars`,`ctad`,`ctdd`) VALUES(".$dbo->quote($restr_rp_name).", '".$pmonth."', ".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").", '".$pminlos."', '".$pmultiplyminlos."', '".$pmaxlos."', ".$rp['from'].", ".$rp['to'].", ".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").", ".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").", ".$pallcars.", ".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", ".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", ".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL").");";
								$dbo->setQuery($q);
								$dbo->execute();
								$lid = $dbo->insertid();
								if (!empty($lid)) {
									$repeat_count++;
								}
							}
						}
					}
				}
				//
				$mainframe->enqueueMessage(JText::_('VRRESTRICTIONSAVED'));
				$mainframe->redirect("index.php?option=com_vikrentcar&task=restrictions");
			} else {
				VikError::raiseWarning('', 'Error while saving');
				$mainframe->redirect("index.php?option=com_vikrentcar&task=newrestriction");
			}
		}
	}

	public function updaterestriction() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		$pwhere = VikRequest::getInt('where', '', 'request');
		$pname = VikRequest::getString('name', '', 'request');
		$pmonth = VikRequest::getInt('month', '', 'request');
		$pmonth = empty($pmonth) ? 0 : $pmonth;
		$pname = empty($pname) ? 'Restriction '.$pmonth : $pname;
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$pdto = VikRequest::getString('dto', '', 'request');
		$pwday = VikRequest::getString('wday', '', 'request');
		$pwdaytwo = VikRequest::getString('wdaytwo', '', 'request');
		$pwdaytwo = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday == $pwdaytwo ? '' : $pwdaytwo;
		$pcomboa = VikRequest::getString('comboa', '', 'request');
		$pcomboa = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboa : '';
		$pcombob = VikRequest::getString('combob', '', 'request');
		$pcombob = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombob : '';
		$pcomboc = VikRequest::getString('comboc', '', 'request');
		$pcomboc = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboc : '';
		$pcombod = VikRequest::getString('combod', '', 'request');
		$pcombod = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombod : '';
		$combostr = '';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboa) ? $pcomboa.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombob) ? $pcombob.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboc) ? $pcomboc.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombod) ? $pcombod : '';
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = empty($pmaxlos) ? 0 : $pmaxlos;
		$pmultiplyminlos = VikRequest::getString('multiplyminlos', '', 'request');
		$pmultiplyminlos = empty($pmultiplyminlos) ? 0 : 1;
		$pallcars = VikRequest::getString('allcars', '', 'request');
		$pallcars = $pallcars == "1" ? 1 : 0;
		$pidcars = VikRequest::getVar('idcars', array(0));
		$ridr = '';
		$caridsforsess = array();
		if (!empty($pidcars) && @count($pidcars) && $pallcars == 0) {
			foreach ($pidcars as $idr) {
				if (empty($idr)) {
					continue;
				}
				$ridr .= '-'.$idr.'-;';
				$caridsforsess[] = (int)$idr;
			}
		} elseif ($pallcars > 0) {
			$q = "SELECT `id` FROM `#__vikrentcar_cars`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetchids = $dbo->loadAssocList();
				foreach ($fetchids as $fetchid) {
					$caridsforsess[] = (int)$fetchid['id'];
				}
			}
		}
		$pcta = VikRequest::getInt('cta', '', 'request');
		$pctd = VikRequest::getInt('ctd', '', 'request');
		$pctad = VikRequest::getVar('ctad', array());
		$pctdd = VikRequest::getVar('ctdd', array());
		if ($pminlos == 1 && strlen($pwday) == 0 && empty($pctad) && empty($pctdd)) {
			VikError::raiseWarning('', JText::_('VRUSELESSRESTRICTION'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=editrestriction&cid[]=".$pwhere);
		} else {
			//check if there are restrictions for this month
			if ($pmonth > 0) {
				$q = "SELECT `id` FROM `#__vikrentcar_restrictions` WHERE `month`='".$pmonth."' AND `id`!='".$pwhere."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					VikError::raiseWarning('', JText::_('VRRESTRICTIONMONTHEXISTS'));
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editrestriction&cid[]=".$pwhere);
				}
				$pdfrom = 0;
				$pdto = 0;
			} else {
				//dates range
				if (empty($pdfrom) || empty($pdto)) {
					VikError::raiseWarning('', JText::_('VRRESTRICTIONERRDRANGE'));
					$mainframe->redirect("index.php?option=com_vikrentcar&task=editrestriction&cid[]=".$pwhere);
				} else {
					$pdfrom = VikRentCar::getDateTimestamp($pdfrom, 0, 0);
					$pdto = VikRentCar::getDateTimestamp($pdto, 0, 0);
				}
			}
			//CTA and CTD
			$setcta = array();
			$setctd = array();
			if ($pcta > 0 && count($pctad) > 0) {
				foreach ($pctad as $ctwd) {
					if (strlen($ctwd)) {
						$setcta[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			if ($pctd > 0 && count($pctdd) > 0) {
				foreach ($pctdd as $ctwd) {
					if (strlen($ctwd)) {
						$setctd[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			//
			$q = "UPDATE `#__vikrentcar_restrictions` SET `name`=".$dbo->quote($pname).",`month`='".$pmonth."',`wday`=".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").",`minlos`='".$pminlos."',`multiplyminlos`='".$pmultiplyminlos."',`maxlos`='".$pmaxlos."',`dfrom`=".$pdfrom.",`dto`=".$pdto.",`wdaytwo`=".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").",`wdaycombo`=".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").",`allcars`=".$pallcars.",`idcars`=".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", `ctad`=".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", `ctdd`=".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL")." WHERE `id`='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRRESTRICTIONSAVED'));
			$mainframe->redirect("index.php?option=com_vikrentcar&task=restrictions");
		}
	}

	public function removerestrictions() {
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentcar_restrictions` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=restrictions");
	}

	public function cancelrestriction() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar&task=restrictions");
	}

	public function setnewrates() {
		$dbo = JFactory::getDbo();
		$currencysymb = VikRentCar::getCurrencySymb();
		$vrc_df = VikRentCar::getDateFormat();
		$df = $vrc_df == "%d/%m/%Y" ? 'd/m/Y' : ($vrc_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikRentCar::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikRentCar::getHoursMinutes($timeopst[0]);
			$closet = VikRentCar::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			// set drop off time equal to pick up time to avoid getting extra days of rent
			$pcheckouth = $pcheckinh;
			$pcheckoutm = $pcheckinm;
		}
		$pid_car = VikRequest::getInt('id_car', '', 'request');
		$pid_price = VikRequest::getInt('id_price', '', 'request');
		$prate = VikRequest::getString('rate', '', 'request');
		$prate = (float)$prate;
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		if (empty($pid_car) || empty($pid_price) || empty($prate) || !($prate > 0) || empty($pfromdate) || empty($ptodate)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRNEWRATE'));
			exit;
		}
		$carrates = array();
		//read the rates for the lowest number of nights
		//the old query below used to cause an error #1055 when sql_mode=only_full_group_by
		//$q = "SELECT `r`.*,`p`.`name` FROM `#__vikrentcar_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$pid_car." AND `idprice`=".(int)$pid_price." GROUP BY `idcar`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikrentcar_prices` `p` ON `p`.`id`=`r`.`idprice` AND `p`.`id`=".(int)$pid_price." WHERE `r`.`idcar`=".(int)$pid_car." AND `r`.`idprice`=".(int)$pid_price." GROUP BY `r`.`idprice` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$q = "SELECT `r`.`id`,`r`.`idcar`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` FROM `#__vikrentcar_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$pid_car." AND `idprice`=".(int)$pid_price." GROUP BY `idcar`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikrentcar_prices` `p` ON `p`.`id`=`r`.`idprice` AND `p`.`id`=".(int)$pid_price." WHERE `r`.`idcar`=".(int)$pid_car." AND `r`.`idprice`=".(int)$pid_price." GROUP BY `r`.`id`,`r`.`idcar`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$carrates = $dbo->loadAssocList();
			foreach ($carrates as $rrk => $rrv) {
				$carrates[$rrk]['cost'] = round(($rrv['cost'] / $rrv['days']), 2);
				$carrates[$rrk]['days'] = 1;
			}
		}
		//
		if (!(count($carrates) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRNORATES'));
			exit;
		}
		$carrates = $carrates[0];
		$current_rates = array();
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$today_tsin = VikRentCar::getDateTimestamp(date($df, $infostart[0]), $pcheckinh, $pcheckinm);
			$today_tsout = VikRentCar::getDateTimestamp(date($df, mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year'])), $pcheckouth, $pcheckoutm);

			$tars = VikRentCar::applySeasonsCar(array($carrates), $today_tsin, $today_tsout);
			$current_rates[(date('Y-m-d', $infostart[0]))] = $tars[0];

			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		if (!(count($current_rates) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRNORATES').'.');
			exit;
		}
		$all_days = array_keys($current_rates);
		$season_intervals = array();
		$firstind = 0;
		$firstdaycost = $current_rates[$all_days[0]]['cost'];
		$nextdaycost = false;
		for ($i=1; $i < count($all_days); $i++) {
			$ind = $all_days[$i];
			$nextdaycost = $current_rates[$ind]['cost'];
			if ($firstdaycost != $nextdaycost) {
				$interval = array(
					'from' => $all_days[$firstind],
					'to' => $all_days[($i - 1)],
					'cost' => $firstdaycost
				);
				$season_intervals[] = $interval;
				$firstdaycost = $nextdaycost;
				$firstind = $i;
			}
		}
		if ($nextdaycost === false) {
			$interval = array(
				'from' => $all_days[$firstind],
				'to' => $all_days[$firstind],
				'cost' => $firstdaycost
			);
			$season_intervals[] = $interval;
		} elseif ($firstdaycost == $nextdaycost) {
			$interval = array(
				'from' => $all_days[$firstind],
				'to' => $all_days[($i - 1)],
				'cost' => $firstdaycost
			);
			$season_intervals[] = $interval;
		}
		foreach ($season_intervals as $sik => $siv) {
			if ((float)$siv['cost'] == $prate) {
				unset($season_intervals[$sik]);
			}
		}
		if (!(count($season_intervals) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRNORATESMOD'));
			exit;
		}
		foreach ($season_intervals as $sik => $siv) {
			$first = strtotime($siv['from']);
			$second = strtotime($siv['to']);
			if ($second > 0 && $second == $first) {
				$second += 86399;
			}
			if (!($second > $first)) {
				unset($season_intervals[$sik]);
				continue;
			}
			$baseone = getdate($first);
			$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
			$sfrom = $baseone[0] - $basets;
			$basetwo = getdate($second);
			$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
			$sto = $basetwo[0] - $basets;
			//check leap year
			if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
				$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
				if ($baseone[0] > $leapts) {
					$sfrom -= 86400;
					/**
					 * To avoid issue with leap years and dates near Feb 29th, we only reduce the seconds if these were reduced
					 * for the from-date of the seasons. Doing it just for the to-date in 2019 for 2020 (leap) produced invalid results.
					 * 
					 * @since 	July 2nd 2019
					 */
					if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
						if ($basetwo[0] > $leapts) {
							$sto -= 86400;
						}
					}
				}
			}
			//end leap year
			$tieyear = $baseone['year'];
			$ptype = (float)$siv['cost'] > $prate ? "2" : "1";
			$pdiffcost = $ptype == "1" ? ($prate - (float)$siv['cost']) : ((float)$siv['cost'] - $prate);
			$roomstr = "-".$pid_car."-,";
			$pspname = date('Y-m-d H:i').' - '.substr($baseone['month'], 0, 3).' '.$baseone['mday'].($siv['from'] != $siv['to'] ? '/'.($baseone['month'] != $basetwo['month'] ? substr($basetwo['month'], 0, 3).' ' : '').$basetwo['mday'] : '');
			$pval_pcent = 1;
			$pricestr = "-".$pid_price."-,";
			$q = "INSERT INTO `#__vikrentcar_seasons` (`type`,`from`,`to`,`diffcost`,`idcars`,`spname`,`wdays`,`pickupincl`,`val_pcent`,`losoverride`,`roundmode`,`year`,`idprices`,`promo`,`promotxt`,`promodaysadv`) VALUES('".($ptype == "1" ? "1" : "2")."', ".$dbo->quote($sfrom).", ".$dbo->quote($sto).", ".$dbo->quote($pdiffcost).", ".$dbo->quote($roomstr).", ".$dbo->quote($pspname).", '', '0', '".$pval_pcent."', '', NULL, ".$tieyear.", ".$dbo->quote($pricestr).", 0, '', NULL);";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//prepare output by re-calculating the rates in real-time
		$current_rates = array();
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$today_tsin = VikRentCar::getDateTimestamp(date($df, $infostart[0]), $pcheckinh, $pcheckinm);
			$today_tsout = VikRentCar::getDateTimestamp(date($df, mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year'])), $pcheckouth, $pcheckoutm);

			$tars = VikRentCar::applySeasonsCar(array($carrates), $today_tsin, $today_tsout);
			$indkey = $infostart['mday'].'-'.$infostart['mon'].'-'.$infostart['year'].'-'.$pid_price;
			$current_rates[$indkey] = $tars[0];

			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		if ($pdebug == 1) {
			echo "e4j.error.\n".print_r($carrates, true)."\n";
			echo print_r($current_rates, true)."\n\n";
			echo print_r($season_intervals, true)."\n";
			echo $pid_car.' - '.$pid_price.' - '.$prate.' - '.$pfromdate.' - '.$ptodate."\n";
		}
		echo json_encode($current_rates);
		exit;
	}

	public function modcarrateplans() {
		$dbo = JFactory::getDbo();
		$pid_car = VikRequest::getInt('id_car', '', 'request');
		$pid_price = VikRequest::getInt('id_price', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		if (empty($pid_car) || empty($pid_price) || empty($ptype) || empty($pfromdate) || empty($ptodate) || !(strtotime($pfromdate) > 0)  || !(strtotime($ptodate) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRMODRPLANS'));
			exit;
		}
		$price_record = array();
		$q = "SELECT * FROM `#__vikrentcar_prices` WHERE `id`=".$pid_price.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$price_record = $dbo->loadAssoc();
		}
		if (!count($price_record) > 0) {
			echo 'e4j.error.'.addslashes(JText::_('VRRATESOVWERRMODRPLANS')).'.';
			exit;
		}
		$current_closed = array();
		if (!empty($price_record['closingd'])) {
			$current_closed = json_decode($price_record['closingd'], true);
			if (!is_array($current_closed)) {
				$current_closed = array();
			}
		}
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		$all_days = array();
		$output = array();
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$all_days[] = date('Y-m-d', $infostart[0]);
			$indkey = $infostart['mday'].'-'.$infostart['mon'].'-'.$infostart['year'].'-'.$pid_price;
			$output[$indkey] = array();
			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		if ($ptype == 'close') {
			if (!array_key_exists($pid_car, $current_closed)) {
				$current_closed[$pid_car] = array();
			}
			foreach ($all_days as $daymod) {
				if (!in_array($daymod, $current_closed[$pid_car])) {
					$current_closed[$pid_car][] = $daymod;
				}
			}
		} else {
			//open
			if (array_key_exists($pid_car, $current_closed)) {
				foreach ($all_days as $daymod) {
					if (in_array($daymod, $current_closed[$pid_car])) {
						foreach ($current_closed[$pid_car] as $ck => $cv) {
							if ($daymod == $cv) {
								unset($current_closed[$pid_car][$ck]);
							}
						}
					}
				}
			} else {
				$current_closed[$pid_car] = array();
			}
		}
		if (!count($current_closed[$pid_car]) > 0) {
			unset($current_closed[$pid_car]);
		}
		$q = "UPDATE `#__vikrentcar_prices` SET `closingd`=".(count($current_closed) > 0 ? $dbo->quote(json_encode($current_closed)) : "NULL")." WHERE `id`=".(int)$pid_price.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$oldcsscls = $ptype == 'close' ? 'vrc-roverw-rplan-on' : 'vrc-roverw-rplan-off';
		$newcsscls = $ptype == 'close' ? 'vrc-roverw-rplan-off' : 'vrc-roverw-rplan-on';
		foreach ($output as $ok => $ov) {
			$output[$ok] = array('oldcls' => $oldcsscls, 'newcls' => $newcsscls);
		}
		
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		if ($pdebug == 1) {
			echo "e4j.error.\n".print_r($current_closed, true)."\n";
			echo print_r($output, true)."\n\n";
			echo print_r($all_days, true)."\n";
		}
		echo json_encode($output);
		exit;
	}

	public function calc_rates() {
		$response = 'e4j.error.ErrorCode(1) Server is blocking the self-request';
		$currencysymb = VikRentCar::getCurrencySymb();
		$vrc_df = VikRentCar::getDateFormat();
		$df = $vrc_df == "%d/%m/%Y" ? 'd/m/Y' : ($vrc_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikRentCar::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikRentCar::getHoursMinutes($timeopst[0]);
			$closet = VikRentCar::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			// set drop off time equal to pick up time to avoid getting extra days of rent
			$pcheckouth = $pcheckinh;
			$pcheckoutm = $pcheckinm;
		}
		$id_car = VikRequest::getInt('id_car', '', 'request');
		$pickup = VikRequest::getString('pickup', '', 'request');
		$days = VikRequest::getInt('num_days', 1, 'request');
		/**
		 * The page Calendar may call this task via AJAX to obtain information
		 * about the various rate plans and final costs associated.
		 * 
		 * @since 	1.1.0
		 */
		$only_rates = VikRequest::getInt('only_rates', 0, 'request');
		$units = VikRequest::getInt('units', 1, 'request');
		$checkinfdate = VikRequest::getString('checkinfdate', '', 'request');
		if (!empty($checkinfdate) && empty($pickup)) {
			$pickup = date('Y-m-d', VikRentCar::getDateTimestamp($checkinfdate, 0, 0, 0));
		}
		$price_details = array();
		//
		$pickup_ts = strtotime($pickup);
		if (empty($pickup_ts)) {
			$pickup = date('Y-m-d');
			$pickup_ts = strtotime($pickup);
		}
		$is_dst = date('I', $pickup_ts);
		$dropoff_ts = $pickup_ts;
		for ($i = 1; $i <= $days; $i++) { 
			$dropoff_ts += 86400;
			$is_now_dst = date('I', $dropoff_ts);
			if ($is_dst != $is_now_dst) {
				if ((int)$is_dst == 1) {
					$dropoff_ts += 3600;
				} else {
					$dropoff_ts -= 3600;
				}
				$is_dst = $is_now_dst;
			}
		}
		$checkout = date('Y-m-d', $dropoff_ts);

		$endpoint = JURI::root().'index.php?option=com_vikrentcar&task=search';
		/**
		 * @wponly 	Rewrite URI for front-end
		 */
		$model 	= JModel::getInstance('vikrentcar', 'shortcodes');
		$itemid = $model->best('vikrentcar');
		if ($itemid) {
			$endpoint = str_replace(JUri::root(), '', $endpoint);
			$endpoint = JRoute::_($endpoint . "&Itemid={$itemid}", false);
		}
		//

		$rates_data = 'e4jauth=%s&getjson=1&pickupdate='.date($df, $pickup_ts).'&pickuph='.$pcheckinh.'&pickupm='.$pcheckinm.'&releasedate='.date($df, $dropoff_ts).'&releaseh='.$pcheckouth.'&releasem='.$pcheckoutm;

		/**
		 * @wponly 	we use JHttp rather than cURL
		 */
		$http = new JHttp();
		$headers = array(
			'Content-Type' => 'application/x-www-form-urlencoded'
		);
		$cua = VikRequest::getString('HTTP_USER_AGENT', '', 'server');
		if (!empty($cua)) {
			$headers['userAgent'] = $cua;
		}
		$result = $http->post($endpoint, sprintf($rates_data, md5('vrc.e4j.vrc')), $headers);
		if ($result->code != 200) {
			$response = "e4j.error.Communication error ({$result->code}): {$result->body}";
		} else {
			$res = $result->body;
			$arr_res = json_decode($res, true);

			/**
			 * We try to check if decoding was unsuccessful, maybe because the response is mixed with HTML code of the Template/Theme.
			 * In this case we try to extract the JSON string from the plain response to decode only that text.
			 * 
			 * @since 	1.14 Rev2 (J) - 1.1.3 (WP)
			 */
			if (function_exists('json_last_error') && json_last_error() !== JSON_ERROR_NONE) {
				$pattern = '/\{(?:[^{}]|(?R))*\}/x';
				$matchcount = preg_match_all($pattern, $res, $matches);
				if ($matchcount && isset($matches[0]) && count($matches[0])) {
					// we have found JSON strings inside the raw response, we get the last JSON string
					$arr_res = json_decode($matches[0][(count($matches[0]) - 1)], true);
				}
			}
			//

			if (is_array($arr_res)) {
				if (!array_key_exists('e4j.error', $arr_res)) {
					if (array_key_exists($id_car, $arr_res)) {
						$response = '';
						foreach ($arr_res[$id_car] as $rate) {
							// build pricing object
							$rplan_details = new stdClass;
							$rplan_details->idprice = $rate['idprice'];
							$rplan_details->name = $rate['pricename'];
							$rplan_details->tot = $rate['cost'];
							$rplan_details->ftot = $currencysymb . ' ' . VikRentCar::numberFormat(($rate['cost']));
							array_push($price_details, $rplan_details);
							//
							$extra_response = '';
							$response .= '<div class="vrc-calcrates-rateblock" data-idprice="' . $rate['idprice'] . '" data-idcar="' . $id_car . '" data-pickup="' . $pickup . '" data-dropoff="' . $checkout . '">';
							$response .= '<span class="vrc-calcrates-ratename">'.$rate['pricename'].'</span>';
							if (array_key_exists('affdays', $rate) && $rate['affdays'] > 0) {
								$extra_response .= '<span class="vrc-calcrates-extrapricedet vrc-calcrates-ratespaffdays"><span>'.JText::_('VRCALCRATESSPAFFDAYS').'</span>'.$rate['affdays'].'</span>';
							}
							$tot = round($rate['cost'], 2);
							$response .= '<span class="vrc-calcrates-pricedet vrc-calcrates-ratetotal"><span>'.JText::_('VRCALCRATESTOT').'</span>'.$currencysymb.' '.VikRentCar::numberFormat($tot).'</span>';
							if (!empty($extra_response)) {
								$response .= '<div class="vrc-calcrates-info">'.$extra_response.'</div>';
							}
							$response .= '</div>';
						}
						//Debug
						//$response .= '<br/><pre>'.print_r($arr_res, true).'</pre><br/>';
					} else {
						$response = 'e4j.error.'.JText::sprintf('VRCALCRATESCARNOTAVAILCOMBO', date($df, $pickup_ts), date($df, $dropoff_ts));
					}
				} else {
					$response = 'e4j.error.'.$arr_res['e4j.error'];
				}
			} else {
				$response = (strpos($res, 'e4j.error') === false ? 'e4j.error' : '').$res;
			}
		}

		if ($only_rates && strpos($response, 'e4j.error') === false) {
			echo json_encode($price_details);
			exit;
		}
		
		// Do not do only echo trim($response); or the currency symbol may not be encoded on some servers
		echo json_encode(array(trim($response)));
		exit;
	}

	/**
	 * AJAX request made to get the information about certain rental orders.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.13
	 */
	public function getordersinfo() {
		$dbo = JFactory::getDbo();
		$booking_infos = array();
		$bookings = array();
		$pidorders = VikRequest::getString('idorders', '', 'request');
		$psubcar = VikRequest::getString('subcar', '', 'request');
		if (!empty($pidorders)) {
			$bookings = explode(',', $pidorders);
			foreach ($bookings as $k => $v) {
				$v = intval(str_replace('-', '', $v));
				if (empty($v)) {
					unset($bookings[$k]);
					continue;
				}
				$bookings[$k] = $v;
			}
		}
		$bookings = array_values($bookings);
		if (!count($bookings)) {
			/**
			 * AJAX requests made by the page availability overview may contain empty booking IDs
			 * due to SQL errors that only booked the car, but could not save the booking record.
			 * Clean up busy (ghost) records where the busy relations contain empty booking IDs.
			 * 
			 * @since 	1.14.6 (J) - 1.2.4 (WP)
			 */
			$hanging_busy_ids = [];
			$q = "SELECT `b`.`id`, `o`.`id` AS `id_order` FROM `#__vikrentcar_busy` AS `b` LEFT JOIN `#__vikrentcar_orders` AS `o` ON `b`.`id`=`o`.`idbusy` WHERE `o`.`id` = 0 OR `o`.`id` IS NULL";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$removelist = $dbo->loadAssocList();
				foreach ($removelist as $ghost_record) {
					if (!empty($ghost_record['id']) && !in_array($ghost_record['id'], $hanging_busy_ids)) {
						$hanging_busy_ids[] = $ghost_record['id'];
					}
				}
			}
			if (count($hanging_busy_ids)) {
				// clean up ghost records
				$q = "DELETE FROM `#__vikrentcar_busy` WHERE `id` IN (" . implode(', ', $hanging_busy_ids) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
			}

			echo 'e4j.error.1 Missing Data - Please reload the page';
			exit;
		}
		$nowtf = VikRentCar::getTimeFormat(true);
		$nowdf = VikRentCar::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$q = "SELECT `o`.*, `c`.`name` AS `car_name`, `c`.`params` AS `car_params`, `p`.`name` AS `pickup_place` 
			FROM `#__vikrentcar_orders` AS `o` 
			LEFT JOIN `#__vikrentcar_cars` `c` ON `c`.`id`=`o`.`idcar` 
			LEFT JOIN `#__vikrentcar_places` `p` ON `p`.`id`=`o`.`idplace` 
			WHERE `o`.`id` IN (".implode(', ', $bookings).");";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking_infos = $dbo->loadAssocList();
			foreach ($booking_infos as $k => $row) {
				//car, amounts and guests information
				$booking_infos[$k]['status_lbl'] = ($row['status'] != 'confirmed' && $row['status'] != 'standby' ? $row['status'] : ($row['status'] == 'confirmed' ? JText::_('VRCONFIRMED') : JText::_('VRSTANDBY')));
				$booking_infos[$k]['format_tot'] = VikRentCar::numberFormat($row['order_total']);
				$booking_infos[$k]['format_totpaid'] = VikRentCar::numberFormat($row['totpaid']);
				// to avoid using a double left join in the query for the return place name, we use a single query
				$booking_infos[$k]['dropoff_place'] = !empty($row['idreturnplace']) ? VikRentCar::getPlaceName($row['idreturnplace']) : '';
				//Rooms Indexes
				$cindexes = array();
				$subcardata = !empty($psubcar) ? explode('-', $psubcar) : array();
				if ($row['status'] == "confirmed" && !empty($row['params']) && strlen($row['carindex'])) {
					$car_params = json_decode($row['params'], true);
					if (is_array($car_params) && array_key_exists('features', $car_params) && @count($car_params['features']) > 0) {
						foreach ($car_params['features'] as $cind => $cfeatures) {
							if ($cind == $row['carindex']) {
								$ind_str = '';
								foreach ($cfeatures as $fname => $fval) {
									if (strlen($fval)) {
										$ind_str = '#'.$cind.' - '.JText::_($fname).': '.$fval;
										break;
									}
								}
								if (!array_key_exists($row['car_name'], $cindexes)) {
									$cindexes[$row['car_name']] = $ind_str;
								} else {
									$cindexes[$row['car_name']] .= ', '.$ind_str;
								}
								break;
							}
						}
					}
				}
				if (count($cindexes)) {
					$booking_infos[$k]['cindexes'] = $cindexes;
				}
				//Customer Details
				$custdata = $row['custdata'];
				$custdata_parts = explode("\n", $row['custdata']);
				if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
					//get the first two fields
					$custvalues = array();
					foreach ($custdata_parts as $custdet) {
						if (strlen($custdet) < 1) {
							continue;
						}
						$custdet_parts = explode(':', $custdet);
						if (count($custdet_parts) >= 2) {
							unset($custdet_parts[0]);
							array_push($custvalues, trim(implode(':', $custdet_parts)));
						}
						if (count($custvalues) > 1) {
							break;
						}
					}
					if (count($custvalues) > 1) {
						$custdata = implode(' ', $custvalues);
					}
				}
				if (strlen($custdata) > 45) {
					$custdata = substr($custdata, 0, 45)." ...";
				}

				$q = "SELECT `c`.*,`co`.`idorder` FROM `#__vikrentcar_customers` AS `c` LEFT JOIN `#__vikrentcar_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=".$row['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$cust_country = $dbo->loadAssocList();
					$cust_country = $cust_country[0];
					if (!empty($cust_country['first_name'])) {
						$custdata = $cust_country['first_name'].' '.$cust_country['last_name'];
						if (!empty($cust_country['country'])) {
							if (is_file(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$cust_country['country'].'.png')) {
								$custdata .= '<img src="'.VRC_ADMIN_URI.'resources/countries/'.$cust_country['country'].'.png'.'" title="'.$cust_country['country'].'" class="vrc-country-flag vrc-country-flag-left"/>';
							}
						}
					}
				}
				$custdata = JText::_('VRDBTEXTROOMCLOSED') == $row['custdata'] ? '<span class="vrordersroomclosed">'.JText::_('VRDBTEXTROOMCLOSED').'</span>' : $custdata;
				$booking_infos[$k]['cinfo'] = $custdata;
				//Formatted dates
				$booking_infos[$k]['ts'] = date($df . ' ' . $nowtf, $row['ts']);
				$booking_infos[$k]['pickup'] = date($df . ' ' . $nowtf, $row['ritiro']);
				$booking_infos[$k]['dropoff'] = date($df . ' ' . $nowtf, $row['consegna']);
			}
		}
		if (!(count($booking_infos) > 0)) {
			echo 'e4j.error.2 Missing Data';
			exit;
		}

		echo json_encode($booking_infos);
		exit;
	}

	/**
	 * This is an AJAX endpoint.
	 */
	public function cron_exec()
	{
		ob_start();

		VikRequest::setVar('view', VikRequest::getCmd('view', 'cronexec'));
	
		parent::display();

		$content = ob_get_contents();
		ob_end_clean();

		VRCHttpDocument::getInstance()->json([$content]);
	}

	public function downloadcron()
	{
		/**
		 * @wponly 	no more executable files need to be downloaded for WordPress.
		 */
		VRCHttpDocument::getInstance()->close(406, 'Cron Jobs must be executed through WP-Cron');
	}

	/**
	 * This is an AJAX endpoint.
	 */
	public function cronlogs()
	{
		$dbo = JFactory::getDBO();
		$pcron_id = VikRequest::getInt('cron_id', '', 'request');

		ob_start();

		$q = "SELECT * FROM `#__vikrentcar_cronjobs` WHERE `id`=".(int)$pcron_id.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$cron_data = $dbo->loadAssoc();
			$cron_data['logs'] = empty($cron_data['logs']) ? '--------' : $cron_data['logs'];
			echo '<pre>'.print_r($cron_data['logs'], true).'</pre>';
		}

		$content = ob_get_contents();
		ob_end_clean();

		VRCHttpDocument::getInstance()->json([$content]);
	}

	public function canceldash() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentcar");
	}

	/**
	 * Hidden task to clean up duplicate records in certain database tables
	 * due to a double execution of the installation queries.
	 * 
	 * @since 	November 4th 2020
	 */
	public function clean_duplicate_records() {
		$dbo = JFactory::getDbo();

		$tables_with_duplicates = array(
			'#__vikrentcar_config' => array(
				'id_key' 	  => 'id',
				'compare_key' => 'param',
			),
			'#__vikrentcar_countries' => array(
				'id_key' 	  => 'id',
				'compare_key' => 'country_3_code',
			),
			'#__vikrentcar_custfields' => array(
				'id_key' 	  => 'id',
				'compare_key' => 'name',
			),
			'#__vikrentcar_texts' => array(
				'id_key' 	  => 'id',
				'compare_key' => 'param',
			),
		);

		foreach ($tables_with_duplicates as $tblname => $data) {
			$doubles = array();
			$storage = array();
			$rmlist = array();
			$q = "SELECT * FROM `{$tblname}` ORDER BY `{$data['id_key']}` DESC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if (!$dbo->getNumRows()) {
				echo "<p>No records found in table {$tblname}</p>";
				continue;
			}
			$rows = $dbo->loadAssocList();
			foreach ($rows as $row) {
				if (!isset($doubles[$row[$data['compare_key']]])) {
					$doubles[$row[$data['compare_key']]] = 0;
				}
				$doubles[$row[$data['compare_key']]]++;
				if (!isset($storage[$row[$data['compare_key']]])) {
					$storage[$row[$data['compare_key']]] = array();
				}
				array_push($storage[$row[$data['compare_key']]], $row[$data['id_key']]);
			}
			foreach ($doubles as $paramkey => $paramcount) {
				if ($paramcount < 2 || !isset($storage[$paramkey]) || count($storage[$paramkey]) < 2 || $paramcount != count($storage[$paramkey])) {
					continue;
				}
				$exceeding = $paramcount - 1;
				for ($x = 0; $x < $exceeding; $x++) {
					array_push($rmlist, $storage[$paramkey][$x]);
				}
			}
			echo "<p>Total records found in table {$tblname}: " . count($rows) . "</p>";
			echo '<p>Total records to remove: ' . count($rmlist) . '</p>';
			echo '<pre style="display: none;">'.print_r($rmlist, true).'</pre><br/>';
			if (count($rmlist)) {
				$q = "DELETE FROM `{$tblname}` WHERE `{$data['id_key']}` IN (" . implode(', ', $rmlist) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
	}

	/**
	 * Go to the previous order.
	 * 
	 * @uses 	navigateToOrder()
	 * 
	 * @since 	1.2.0
	 */
	public function prev_order()
	{
		$this->navigateToOrder('prev');
	}

	/**
	 * Go to the next order.
	 * 
	 * @uses 	navigateToOrder()
	 * 
	 * @since 	1.2.0
	 */
	public function next_order()
	{
		$this->navigateToOrder('next');
	}

	/**
	 * Given the current order ID in the request, we navigate
	 * either to the next or to the previous reservation (if any).
	 * 
	 * @param 	string 	$direction 	either next or prev.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.2.0
	 */
	private function navigateToOrder($direction = 'next')
	{
		$bid = VikRequest::getInt('whereup', 0, 'request');
		if (empty($bid) || $bid < 1 || !in_array($direction, array('prev', 'next'))) {
			throw new Exception("Invalid request", 400);
		}

		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$q = "SELECT `id` FROM `#__vikrentcar_orders` WHERE `id`" . ($direction == 'next' ? '>' : '<') . "{$bid} ORDER BY `id` " . ($direction == 'next' ? 'ASC' : 'DESC');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', JText::_('VRPEDITBUSYONE'));
			$app->redirect("index.php?option=com_vikrentcar&task=orders");
			exit;
		}

		$app->redirect("index.php?option=com_vikrentcar&task=editorder&cid[]=" . $dbo->loadResult());
		exit;
	}

	/**
	 * AJAX request for adding a new car-day note.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.2.0
	 */
	public function add_cardaynote()
	{
		$dt 	 = VikRequest::getString('dt', '', 'request');
		$idcar   = VikRequest::getInt('idcar', 0, 'request');
		$subunit = VikRequest::getInt('subunit', 0, 'request');
		$type 	 = VikRequest::getString('type', '', 'request');
		$type 	 = empty($type) ? 'custom' : $type;
		$name 	 = VikRequest::getString('name', '', 'request');
		$descr 	 = VikRequest::getString('descr', '', 'request');
		$cdays   = VikRequest::getInt('cdays', 0, 'request');
		$cdays 	 = $cdays < 0 ? 0 : $cdays;
		$cdays 	 = $cdays > 365 ? 365 : $cdays;
		if (empty($idcar) || empty($dt) || !strtotime($dt)) {
			echo 'e4j.error.1';
			exit;
		}

		// reload end date
		$end_date = $dt;
		
		// build critical date object
		$new_note = array(
			'name'  => $name,
			'type'  => $type,
			'descr' => $descr,
		);

		// get object
		$notes  = VikRentCar::getCriticalDatesInstance();

		// store the notes for all consecutive dates
		for ($i = 0; $i <= $cdays; $i++) {
			$store_dt = $dt;
			if ($i > 0) {
				$dt_info = getdate(strtotime($store_dt));
				$store_dt = date('Y-m-d', mktime(0, 0, 0, $dt_info['mon'], ($dt_info['mday'] + $i), $dt_info['year']));
				$end_date = $store_dt;
			}
			$result = $notes->storeDayNote($new_note, $store_dt, $idcar, $subunit);
			if (!$result) {
				echo 'e4j.error.2';
				exit;
			}
		}

		// reload all car day notes for this day for the AJAX response
		$all_notes = $notes->loadCarDayNotes($dt, $end_date, $idcar, $subunit);

		if (!$all_notes || !count($all_notes)) {
			// no notes found even after storing it
			echo 'e4j.error.3';
			exit;
		}

		echo json_encode($all_notes);
		exit;
	}

	/**
	 * AJAX request for removing a car day note.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.2.0
	 */
	public function remove_cardaynote()
	{
		$dt 	 = VikRequest::getString('dt', '', 'request');
		$idcar   = VikRequest::getInt('idcar', 0, 'request');
		$subunit = VikRequest::getInt('subunit', 0, 'request');
		$type 	 = VikRequest::getString('type', '', 'request');
		$type 	 = empty($type) ? 'custom' : $type;
		$ind 	 = VikRequest::getInt('ind', 0, 'request');
		if (empty($dt) || !strtotime($dt)) {
			echo 'e4j.error.1';
			exit;
		}

		$notes  = VikRentCar::getCriticalDatesInstance();
		$result = $notes->deleteDayNote($ind, $dt, $idcar, $subunit, $type);
		if (!$result) {
			echo 'e4j.error.2';
			exit;
		}

		echo 'e4j.ok';
		exit;
	}

	/**
	 * AJAX request for storing an event for a booking.
	 * This endpoint could be used for any kind of purpose.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.2.0
	 */
	public function store_booking_history_event()
	{
		$bid 	= VikRequest::getInt('bid', 0, 'request');
		$event  = VikRequest::getString('event', '', 'request');
		$descr  = VikRequest::getString('descr', '', 'request');

		if (empty($bid) || empty($event)) {
			throw new Exception("Missing required information", 500);
		}

		// Booking History
		VikRentCar::getOrderHistoryInstance()->setBid($bid)->store($event, $descr);
		//

		echo 'e4j.ok';
		exit;
	}

	/**
	 * Loads a specific admin widget ID and executes the requested method.
	 * Useful for loading a newly added widget, or to execute custom functions.
	 * 
	 * @throws 	Exception 	this is an AJAX endpoint.
	 * 
	 * @since 	1.2.0
	 */
	public function exec_admin_widget()
	{
		$widget_id  = VikRequest::getString('widget_id', '', 'request');
		$call 		= VikRequest::getString('call', '', 'request');
		
		if (empty($widget_id)) {
			throw new Exception("Empty Admin Widget ID", 500);
		}

		if (empty($call)) {
			throw new Exception("Empty Admin Widget Callback", 500);
		}

		// invoke admin widgets helper
		$widgets_helper = VikRentCar::getAdminWidgetsInstance();
		$widget = $widgets_helper->getWidget($widget_id);
		
		if ($widget === false) {
			throw new Exception("Requested Admin Widget not found", 404);
		}

		if (!method_exists($widget, $call) || !is_callable(array($widget, $call))) {
			throw new Exception("Admin Widget Callback not found or forbidden", 403);
		}

		// invoke the widget's method within a buffer
		ob_start();
		$widget->{$call}();
		$widget_response = ob_get_contents();
		ob_end_clean();

		// prepare response object with a property equal to the called method
		$response = new stdClass;
		$response->{$call} = $widget_response;

		// echo the response and exit
		echo json_encode($response);
		exit;
	}

	/**
	 * Updates the map of admin widgets.
	 * 
	 * @throws 	Exception 	this is an AJAX endpoint.
	 * 
	 * @since 	1.2.0
	 */
	public function save_admin_widgets()
	{
		// make sure permissions are sufficient
		if (!JFactory::getUser()->authorise('core.vrc.gsettings', 'com_vikrentcar')) {
			throw new Exception("You are not authorized to modify the widgets.", 403);
		}

		$psections = VikRequest::getVar('sections', array(), 'request', 'array');
		if (!is_array($psections) || !count($psections)) {
			throw new Exception("No sections found in map", 500);
		}

		// request values are all converted to arrays, so restore the object styling
		$psections = json_decode(json_encode($psections));

		// update map
		$result = VikRentCar::getAdminWidgetsInstance()->updateWidgetsMap($psections);

		$response = new stdClass;
		$response->status = (int)$result;

		// echo the response and exit
		echo json_encode($response);
		exit;
	}

	/**
	 * Restores the default admin widgets map.
	 * 
	 * @since 	1.2.0
	 */
	public function reset_admin_widgets()
	{
		// reset map and redirect to dashboard
		VikRentCar::getAdminWidgetsInstance()->restoreDefaultWidgetsMap();

		JFactory::getApplication()->redirect('index.php?option=com_vikrentcar');
		exit;
	}

	/**
	 * Updates the welcome message status for the widget's customizer via AJAX.
	 * 
	 * @since 	1.2.0
	 */
	public function admin_widgets_welcome()
	{
		$hide_welcome = VikRequest::getInt('hide_welcome', 0, 'request');
		// update configuration value
		VikRentCar::getAdminWidgetsInstance()->updateWelcome($hide_welcome);

		$response = new stdClass;
		$response->status = $hide_welcome;

		// echo the response and exit
		echo json_encode($response);
		exit;
	}

	/**
	 * Back-end order registration status modal View.
	 * 
	 * @since 	1.2.0
	 */
	public function orderregistration()
	{
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'orderregistration'));
	
		parent::display();
	}

	/**
	 * AJAX endpoint to update the registration status for an order.
	 * 
	 * @since 	1.2.0
	 */
	public function update_reg_status()
	{
		$dbo = JFactory::getDbo();
		$cid = VikRequest::getVar('cid', array(0));

		if (empty($cid[0])) {
			throw new Exception("Missing order ID", 404);
		}

		$q = "SELECT * FROM `#__vikrentcar_orders` WHERE `id`=" . (int)$cid[0] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			throw new Exception("Order ID not found", 404);
		}
		$order = $dbo->loadAssoc();

		$newregstatus = VikRequest::getInt('newregstatus', 0, 'request');
		$regstatusnotes = VikRequest::getString('regstatusnotes', '', 'request');

		$valid_statuses = array(-1, 0, 1, 2);
		if (!in_array($newregstatus, $valid_statuses)) {
			throw new Exception("Bad status value", 400);
		}

		// get order history
		$history_obj = VikRentCar::getOrderHistoryInstance()->setBid($order['id']);

		// update order record
		$order_record = new stdClass;
		$order_record->id = $order['id'];
		$order_record->reg = $newregstatus;

		$dbo->updateObject('#__vikrentcar_orders', $order_record, 'id');

		// use order history to store the information of this event (default to unset)
		$history_type = 'RA';
		if ($newregstatus === -1) {
			// no show
			$history_type = 'RZ';
		} elseif ($newregstatus === 1) {
			// started
			$history_type = 'RB';
		} elseif ($newregstatus === 2) {
			// terminated
			$history_type = 'RC';
		}

		// build the extra description for the event
		$history_extra_descr = date(VikRentCar::getTimeFormat());
		$prev_started_dt = $history_obj->hasEvent('RB');
		if ($history_type == 'RC' && $prev_started_dt !== false) {
			// calculate the exact duration of the rental from last check-in (started) event
			$from_dobj = new DateTime($prev_started_dt);
			$to_dobj = new DateTime(date('Y-m-d H:i:s'));
			$dobj_interval = $from_dobj->diff($to_dobj);
			// format exact duration of rent in days, hours and minutes
			$history_extra_descr = JText::_('VRC_TOT_DURATION') . ': ' . $dobj_interval->format('%d ' . JText::_('VRDAYS') . ', %h ' . JText::_('VRCONFIGONETENEIGHT') . ', %i ' . JText::_('VRCTRKDIFFMINS'));
		}

		// always display full time of the operation
		$regstatusnotes = $history_extra_descr . "\n" . $regstatusnotes;
		// store history record
		$history_obj->store($history_type, $regstatusnotes);

		// set new buttons class and text
		$reg_status = JText::_('VRC_ORDER_REGISTRATION_NONE');
		$reg_class  = 'btn btn-small btn-secondary';
		if ($newregstatus < 0) {
			// no show
			$reg_status = JText::_('VRC_ORDER_REGISTRATION_NOSHOW');
			$reg_class  = 'btn btn-small btn-danger';
		} elseif ($newregstatus === 1) {
			// started
			$reg_status = JText::_('VRC_ORDER_REGISTRATION_STARTED');
			$reg_class  = 'btn btn-small btn-primary';
		} elseif ($newregstatus === 2) {
			// terminated
			$reg_status = JText::_('VRC_ORDER_REGISTRATION_TERMINATED');
			$reg_class  = 'btn btn-small btn-primary';
		}

		$response = array(
			'btn_class' => $reg_class,
			'btn_text'  => $reg_status,
		);

		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX upload the customer documents.
	 *
	 * @return 	void
	 *
	 * @throws 	Exception
	 * 
	 * @since 	1.2.0
	 */
	public function upload_customer_document()
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();

		$customer_id = $input->getUint('customer', 0);

		$result = new stdClass;
		$result->status = 0;

		try
		{			
			$q = $dbo->getQuery(true)
				->select($dbo->qn(array(
					'id',
					'first_name',
					'last_name',
					'email',
					'docsfolder',
				)))
				->from($dbo->qn('#__vikrentcar_customers'))
				->where($dbo->qn('id') . ' = ' . $customer_id);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if (!$dbo->getNumRows())
			{
				throw new Exception(sprintf('Customer [%d] not found', $customer_id), 404);
			}

			$customer = $dbo->loadObject();

			// fetch documents folder path
			$dirpath = VRC_CUSTOMERS_PATH . DIRECTORY_SEPARATOR;

			// check if we have a valid directory
			if (empty($customer->docsfolder) || !is_dir($dirpath . $customer->docsfolder))
			{
				// randomize string
				$customer->seed = uniqid();

				// create blocks for hashed folder
				$parts = [
					$customer->first_name,
					$customer->last_name,
					md5(serialize($customer)),
				];

				// join fetched parts
				$customer->docsfolder = JFilterOutput::stringURLSafe(implode('-', array_filter($parts)));

				if (strlen($customer->docsfolder) < 16)
				{
					throw new Exception('Possible security breach. Please specify the most details as possible.', 400);
				}

				jimport('joomla.filesystem.folder');

				// create a folder for this customer
				$created = JFolder::create($dirpath . $customer->docsfolder);

				if (!$created)
				{
					throw new Exception(sprintf('Unable to create the folder [%s]', $dirpath . $customer->docsfolder), 403);
				}

				unset($customer->seed);

				// update docs folder
				$dbo->updateObject('#__vikrentcar_customers', $customer, 'id');
			}

			// get file from request
			$file = $input->files->get('file', array(), 'array');

			// try to upload the file
			$result = VikRentCar::uploadFileFromRequest($file, $dirpath . $customer->docsfolder, "/(image\/.+)|(application\/(zip|rar|pdf|msword|vnd.*?))|(text\/(plain|markdown|csv))$/i");
			$result->status = 1;

			$result->size = JHtml::_('number.bytes', filesize($result->path), 'auto', 0);
			$result->url  = str_replace(DIRECTORY_SEPARATOR, '/', str_replace(VRC_CUSTOMERS_PATH . DIRECTORY_SEPARATOR, VRC_CUSTOMERS_URI, $result->path));
		}
		catch (Exception $e)
		{
			$result->error = $e->getMessage();
			$result->code  = $e->getCode();
		}

		echo json_encode($result);
		exit;
	}

	/**
	 * AJAX delete the customer documents.
	 *
	 * @return 	void
	 *
	 * @throws 	Exception
	 * 
	 * @since 	1.2.0
	 */
	public function delete_customer_document()
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();

		$customer_id = $input->getUint('customer', 0);

		$result = new stdClass;
		$result->status = 0;

		$q = $dbo->getQuery(true)
			->select($dbo->qn('docsfolder'))
			->from($dbo->qn('#__vikrentcar_customers'))
			->where($dbo->qn('id') . ' = ' . $customer_id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			throw new Exception(sprintf('Customer [%d] not found', $customer_id), 404);
		}

		$folder = $dbo->loadResult();

		if (!$folder)
		{
			throw new Exception('The customer does not have any documents', 500);
		}

		$file = $input->getString('file');

		if (!$file)
		{
			throw new Exception('File to remove not specified', 400);
		}

		$path = implode(DIRECTORY_SEPARATOR, array(VRC_CUSTOMERS_PATH, $folder, $file));

		if (!is_file($path)) 
		{
			throw new Exception(sprintf('File [%s] not found', $path), 404);
		}

		jimport('joomla.filesystem.file');

		$removed = JFile::delete($path);

		echo json_encode(array('status' => (int) $removed));
		exit;
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function newcondtext()
	{
		VikRentCarHelper::printHeader("11");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecondtext'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function editcondtext()
	{
		VikRentCarHelper::printHeader("11");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecondtext'));
	
		parent::display();

		if (VikRentCar::showFooter()) {
			VikRentCarHelper::printFooter();
		}
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function cancelcondtext()
	{
		JFactory::getApplication()->redirect('index.php?option=com_vikrentcar&task=config&tab=5');
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function createcondtext()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->_doCreateCondText();
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function createcondtextstay()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->_doCreateCondText(true);
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	private function _doCreateCondText($stay = false)
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$rules_helper = VikRentCar::getConditionalRulesInstance();
		$rules_list = $rules_helper->composeRulesParamsFromRequest();

		$condtextname = VikRequest::getString('condtextname', '', 'request');
		$condtexttkn = VikRequest::getString('condtexttkn', '', 'request');
		$msg = VikRequest::getString('msg', '', 'request', VIKREQUEST_ALLOWRAW);
		$debug = VikRequest::getInt('debug', 0, 'request');
		if (empty($condtextname)) {
			$condtextname = date('Y-m-dHis');
			$condtexttkn = '{condition: ' . date('YmdHis') . '}';
		}

		$existing_tokens = $rules_helper->getSpecialTags();
		if (count($existing_tokens) && isset($existing_tokens[$condtexttkn])) {
			VikError::raiseWarning('', 'Another conditional text with the same special tag already exists');
			$app->redirect('index.php?option=com_vikrentcar&task=newcondtext');
			exit;
		}

		$data = new stdClass;
		$data->name = $condtextname;
		$data->token = $condtexttkn;
		$data->rules = json_encode($rules_list);
		$data->msg = $msg;
		$data->lastupd = JDate::getInstance()->toSql();
		$data->debug = $debug;

		$dbo->insertObject('#__vikrentcar_condtexts', $data, 'id');

		if (isset($data->id)) {
			$app->enqueueMessage(JText::_('VRSEASONUPDATED'));
		}

		if (!$stay || !isset($data->id)) {
			$this->cancelcondtext();
			exit;
		}

		$app->redirect('index.php?option=com_vikrentcar&task=editcondtext&cid[]=' . $data->id);
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function updatecondtext()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->_doUpdateCondText();
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function updatecondtextstay()
	{
		if (!JSession::checkToken()) {
			throw new Exception(JText::_('JINVALID_TOKEN'), 403);
		}
		$this->_doUpdateCondText(true);
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	private function _doUpdateCondText($stay = false)
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$rules_helper = VikRentCar::getConditionalRulesInstance();
		$rules_list = $rules_helper->composeRulesParamsFromRequest();

		$pwhere = VikRequest::getInt('where', '', 'request');
		$condtextname = VikRequest::getString('condtextname', '', 'request');
		$condtexttkn = VikRequest::getString('condtexttkn', '', 'request');
		$msg = VikRequest::getString('msg', '', 'request', VIKREQUEST_ALLOWRAW);
		$debug = VikRequest::getInt('debug', 0, 'request');
		if (empty($condtextname)) {
			$condtextname = date('Y-m-dHis');
			$condtexttkn = '{condition: ' . date('YmdHis') . '}';
		}

		$existing_tokens = $rules_helper->getSpecialTags();
		if (count($existing_tokens) && isset($existing_tokens[$condtexttkn]) && ($existing_tokens[$condtexttkn]['id'] != $pwhere)) {
			VikError::raiseWarning('', 'Another conditional text with the same special tag already exists (' . $existing_tokens[$condtexttkn]['name'] . ')');
			$app->redirect('index.php?option=com_vikrentcar&task=editcondtext&cid[]=' . $pwhere);
			exit;
		}

		$data = new stdClass;
		$data->id = $pwhere;
		$data->name = $condtextname;
		$data->token = $condtexttkn;
		$data->rules = json_encode($rules_list);
		$data->msg = $msg;
		$data->lastupd = JDate::getInstance()->toSql();
		$data->debug = $debug;

		$dbo->updateObject('#__vikrentcar_condtexts', $data, 'id');

		$app->enqueueMessage(JText::_('VRSEASONUPDATED'));

		if (!$stay) {
			$this->cancelcondtext();
			exit;
		}

		$app->redirect('index.php?option=com_vikrentcar&task=editcondtext&cid[]=' . $data->id);
	}

	/**
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function removecondtext()
	{
		$dbo = JFactory::getDbo();
		$ids = VikRequest::getVar('cid', array());
		if (count($ids)) {
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikrentcar_condtexts` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$this->cancelcondtext();
	}

	/**
	 * AJAX endpoint to update one template file with the given tag or styles.
	 * A JSON response will be echoed by exiting the process.
	 * 
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function condtext_update_tmpl()
	{
		VikRentCar::getConditionalRulesInstance(true);

		$tagaction = VikRequest::getString('tagaction', '', 'request');
		$tag = VikRequest::getString('tag', '', 'request');
		$file = VikRequest::getString('file', '', 'request', VIKREQUEST_ALLOWRAW);
		$newcontent = VikRequest::getString('newcontent', '', 'request', VIKREQUEST_ALLOWRAW);
		$custom_classes = VikRequest::getVar('custom_classes', array(), 'request', 'array');

		$allowed_actions = array(
			'add',
			'remove',
			'styles',
			'restore',
		);

		if (empty($tagaction) || empty($file) || !in_array($tagaction, $allowed_actions)) {
			VRCHttpDocument::getInstance()->close(500, 'Invalid request submitted');
		}

		if (in_array($tagaction, array('add', 'remove')) && empty($tag)) {
			VRCHttpDocument::getInstance()->close(500, 'Invalid request submitted - missing tag');
		}

		if (in_array($tagaction, array('add', 'styles')) && empty($newcontent)) {
			VRCHttpDocument::getInstance()->close(500, 'Invalid request submitted - missing new HTML content');
		}

		if ($tagaction == 'styles' && (!is_array($custom_classes) || !count($custom_classes))) {
			VRCHttpDocument::getInstance()->close(500, 'No custom CSS classes to parse');
		}

		if ($tagaction == 'restore') {
			// immediately restore the requested file to avoid script interruptions
			VikRentCarHelperConditionalRules::restoreTemplateFileCode($file);
		}

		// get requested file content
		$fcontent = VikRentCarHelperConditionalRules::getTemplateFileCode($file);
		if (empty($fcontent) || !is_string($fcontent)) {
			VRCHttpDocument::getInstance()->close(404, 'File not found or its code is unreadable');
		}

		if ($tagaction == 'remove') {
			// remove tag from code content
			$fcontent = str_replace($tag, '', $fcontent);
		} elseif ($tagaction == 'add') {
			// add tag to code content in the same exact position
			$fcontent = VikRentCarHelperConditionalRules::addTagByComparingSources($tag, $file, $newcontent, $fcontent);
		} elseif ($tagaction == 'styles') {
			// apply the same styling rules
			$fcontent = VikRentCarHelperConditionalRules::addStylesByComparingSources($custom_classes, $file, $newcontent, $fcontent);
		}

		// update the file code
		$res = VikRentCarHelperConditionalRules::writeTemplateFileCode($file, $fcontent);

		if (!$res) {
			VRCHttpDocument::getInstance()->close(500, 'Could not update the source code of the template file');
		}

		// parse new HTML content
		$newhtmls = VikRentCarHelperConditionalRules::getTemplateFilesContents($file);
		if (!is_array($newhtmls) || !isset($newhtmls[$file])) {
			VRCHttpDocument::getInstance()->close(404, 'Could not parse new template file content');
		}

		// trigger backup/mirroring, if available
		if (defined('ABSPATH')) {
			VikRentCarUpdateManager::storeTemplateContent($file, $newhtmls[$file]);
		}

		// build output
		$output = new stdClass;
		$output->newhtml = $newhtmls[$file];
		$output->log = VikRentCarHelperConditionalRules::getEditingLog();

		// output the JSON response and exit
		VRCHttpDocument::getInstance()->json($output);
	}

	/**
	 * AJAX endpoint to render the measurment driver params.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function loadmeasurmentparams()
	{
		$html = '---------';
		$driver_id = VikRequest::getString('driver_id', '', 'request');
		if (!empty($driver_id)) {
			$html = VRCConversionFactory::getInstance()->displayParams($driver_id);
		}
		/**
		 * The HTML content is built by an internal method that does not trigger any hook
		 * where third party plugins could interfere. We cannot escape this HTML string,
		 * nor can we convert special chars into HTML entities, as this is the response
		 * of an AJAX request, and the HTML code needs to be displayed accordingly.
		 * If we were to escape the HTML string, then the AJAX response would be useless,
		 * as it would be HTML code converted into text with HTML entities.
		 */
		echo $html;
		exit;
	}
}
