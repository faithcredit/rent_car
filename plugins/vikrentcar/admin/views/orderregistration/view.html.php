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

class VikRentCarViewOrderregistration extends JViewVikRentCar {
	
	function display($tpl = null) {
		// This view is usually called within a modal box, so it does not require the toolbar or page title
		
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

		$this->order = &$order;

		// Display the template
		parent::display($tpl);
	}
}
