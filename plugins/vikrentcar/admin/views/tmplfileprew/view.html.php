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

class VikRentCarViewTmplfileprew extends JViewVikRentCar {
	
	function display($tpl = null) {
		// This view is usually called within a modal box, so it does not require the toolbar or page title
		
		$fpath = VikRequest::getString('path', '', 'request', VIKREQUEST_ALLOWRAW);
		if (!empty($fpath) && !is_file($fpath)) {
			$fpath = urldecode($fpath);
		}
		// list of allowed template files for preview
		$allowed_previews = array(
			'email_tmpl.php',
		);
		$fbase = basename($fpath);
		$htmlpreview = '';
		if (!is_file($fpath) || !in_array($fbase, $allowed_previews)) {
			throw new Exception("File {$fbase} not found", 404);
		}

		// load template file preview
		$dbo = JFactory::getDbo();

		switch ($fbase) {
			case 'email_tmpl.php':
				// find the last confirmed rental order
				$q = "SELECT `o`.`id` FROM `#__vikrentcar_orders` AS `o` 
					LEFT JOIN `#__vikrentcar_busy` AS `b` ON `o`.`idbusy`=`b`.`id` 
					WHERE `o`.`status`='confirmed' AND `o`.`custmail` != '' AND `b`.`stop_sales`=0 
					ORDER BY `o`.`id` DESC LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$htmlpreview = VikRentCar::sendOrderEmail($dbo->loadResult(), array(), false);
				} else {
					$htmlpreview = '<p class="warn">' . JText::_('VRNOORDERSFOUND') . '</p>';
				}
				break;
			default:
				break;
		}
		
		$this->fpath = &$fpath;
		$this->fbase = &$fbase;
		$this->htmlpreview = &$htmlpreview;
		
		// Display the template
		parent::display($tpl);
	}

}
