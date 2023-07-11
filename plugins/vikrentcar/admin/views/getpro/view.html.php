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

class VikRentCarViewGetpro extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		VikRentCarLoader::import('update.changelog');
		VikRentCarLoader::import('update.license');

		$version = VikRequest::getString('version', '', 'request');
		if (!empty($version)) {
			/**
			 * Download Changelog
			 * 
			 * @since 	1.0.3
			 */
			$http = new JHttp;

			$url = 'https://vikwp.com/api/?task=products.changelog';

			$data = array(
				'sku' 		=> 'vrc',
				'version' 	=> $version,
			);

			$response = $http->post($url, $data);

			if ($response->code == 200) {
				VikRentCarChangelog::store(json_decode($response->body));
			}
		}

		$changelog = VikRentCarChangelog::build();
		$lic_key = VikRentCarLicense::getKey();
		$lic_date = VikRentCarLicense::getExpirationDate();
		$is_pro = VikRentCarLicense::isPro();

		if (!$is_pro) {
			VikError::raiseWarning('', JText::_('VRCNOPROERROR'));
			JFactory::getApplication()->redirect('index.php?option=com_vikrentcar&view=gotopro');
			exit;
		}
		
		$this->changelog = &$changelog;
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
		JToolBarHelper::title(JText::_('VRCMAINGETPROTITLE'), 'vikrentcar');
	}

}
