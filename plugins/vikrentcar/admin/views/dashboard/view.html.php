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

class VikRentCarViewDashboard extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		/**
		 * @wponly - trigger back up of extendable files
		 */
		VikRentCarLoader::import('update.manager');
		VikRentCarUpdateManager::triggerExtendableClassesBackup('languages', "/^.+\-((?!en_US|it_IT).)+$/");
		//

		$dbo = JFactory::getDbo();
		
		$q = "SELECT COUNT(*) FROM `#__vikrentcar_prices`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totprices = $dbo->loadResult();

		$q = "SELECT COUNT(*) FROM `#__vikrentcar_places`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totlocations = $dbo->loadResult();

		$q = "SELECT COUNT(*) FROM `#__vikrentcar_categories`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totcategories = $dbo->loadResult();

		$q = "SELECT COUNT(*) FROM `#__vikrentcar_cars`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totcars = $dbo->loadResult();

		$q = "SELECT COUNT(*) FROM `#__vikrentcar_dispcost`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totdailyfares = $dbo->loadResult();
		
		$arrayfirst = array(
			'totprices' => $totprices,
			'totlocations' => $totlocations,
			'totcategories' => $totcategories,
			'totcars' => $totcars,
			'totdailyfares' => $totdailyfares
		);
		
		$this->arrayfirst = &$arrayfirst;

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINDASHBOARDTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.admin', 'com_vikrentcar')) {
			JToolBarHelper::preferences('com_vikrentcar');

			/**
			 * @wponly
			 */
			JToolBarHelper::shortcodes('com_vikrentcar');
		}
	}

}
