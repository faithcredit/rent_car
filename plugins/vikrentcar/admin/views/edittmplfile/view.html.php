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

class VikRentCarViewEdittmplfile extends JViewVikRentCar {
	
	function display($tpl = null) {
		// This view is usually called within a modal box, so it does not require the toolbar or page title

		$fpath = VikRequest::getString('path', '', 'request', VIKREQUEST_ALLOWRAW);
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		$exists = is_file($fpath);
		if (!$exists) {
			$fpath = urldecode($fpath);
		}
		if (!is_file($fpath) && $pdebug > 0) {
			//VRC 1.12
			touch($fpath);
		} elseif (strpos(basename($fpath), 'config') !== false) {
			// security patch for VRC 1.12
			$fpath = '';
		}
		$fpath = is_file($fpath) ? $fpath : '';
		
		$this->fpath = &$fpath;
		
		// Display the template
		parent::display($tpl);
	}

}
