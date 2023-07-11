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

/**
 * @wponly this View is only for WP
 */

// import Joomla view library
jimport('joomla.application.component.view');

class VikRentCarViewGotopro extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		VikRentCarLoader::import('update.license');

		$hidead = VikRequest::getInt('hidead', 0, 'request');
		if ($hidead > 0) {
			VikRentCarLicense::setVcmAd(false);
		}

		$lic_key = VikRentCarLicense::getKey();
		$lic_date = VikRentCarLicense::getExpirationDate();
		$is_pro = VikRentCarLicense::isPro();

		if ($is_pro > 0) {
			$tpl = 'pro';
		}
		
		$this->lic_key = &$lic_key;
		$this->lic_date = &$lic_date;
		$this->is_pro = &$is_pro;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRCMAINGOTOPROTITLE'), 'vikrentcar');
	}

}
