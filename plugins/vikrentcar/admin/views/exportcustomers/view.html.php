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

class VikRentCarViewExportcustomers extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		$cid = VikRequest::getVar('cid', array(0));
		
		$dbo = JFactory::getDbo();
		$q = "SELECT * FROM `#__vikrentcar_countries` ORDER BY `#__vikrentcar_countries`.`country_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$countries = $dbo->loadAssocList();
		
		$this->cid = &$cid;
		$this->countries = &$countries;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRCMAINEXPCUSTOMERSTITLE'), 'vikrentcar');
		JToolBarHelper::custom('exportcustomerslaunch', 'download', 'download', JText::_('VRCCSVEXPCUSTOMERSGET'), false);
		JToolBarHelper::spacer();
		JToolBarHelper::cancel( 'cancelcustomer', JText::_('VRBACK'));
		JToolBarHelper::spacer();
	}

}
