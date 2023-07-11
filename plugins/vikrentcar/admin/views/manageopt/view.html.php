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

class VikRentCarViewManageopt extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$row = array();
		$allcars = array();
		$tot_cars = 0;
		$tot_cars_options = 0;
		$dbo = JFactory::getDbo();

		// read all cars
		$q = "SELECT `id`, `name`, `idopt` FROM `#__vikrentcar_cars`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$records = $dbo->loadAssocList();
			foreach ($records as $r) {
				$r['idopt'] = empty($r['idopt']) ? array() : explode(';', rtrim($r['idopt'], ';'));
				$allcars[$r['id']] = $r;
			}
			$tot_cars = count($allcars);
		}

		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				VikError::raiseWarning('', 'Not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikrentcar&task=optionals");
				exit;
			}
			$row = $dbo->loadAssoc();
			$q = "SELECT `idopt` FROM `#__vikrentcar_cars` WHERE `idopt` LIKE ".$dbo->quote("%".$row['id'].";%").";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$all_opt = $dbo->loadAssocList();
				foreach ($all_opt as $k => $v) {
					$opt_parts = explode(';', $v['idopt']);
					if (in_array((string)$row['id'], $opt_parts)) {
						$tot_cars_options++;
					}
				}
			}
		}
		
		$this->row = &$row;
		$this->tot_cars = &$tot_cars;
		$this->tot_cars_options = &$tot_cars_options;
		$this->allcars = &$allcars;
		
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
			JToolBarHelper::title(JText::_('VRMAINOPTTITLEEDIT'), 'vikrentcar');
			JToolBarHelper::save( 'updateoptional', JText::_('VRSAVE'));
			JToolBarHelper::spacer();
			JToolBarHelper::cancel( 'canceloptional', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VRMAINOPTTITLENEW'), 'vikrentcar');
			JToolBarHelper::save( 'createoptional', JText::_('VRSAVE'));
			JToolBarHelper::spacer();
			JToolBarHelper::cancel( 'canceloptional', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
