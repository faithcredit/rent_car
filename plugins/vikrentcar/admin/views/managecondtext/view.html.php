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

class VikRentCarViewManagecondtext extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$condtextid = $cid[0];
		}

		$dbo = JFactory::getDbo();
		$condtext = array();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikrentcar_condtexts` WHERE `id`=".(int)$condtextid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$condtext = $dbo->loadAssoc();
			}
		}
		
		$this->condtext = &$condtext;
		
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
			JToolBarHelper::title(JText::_('VRC_COND_TEXT_MNG_TITLE'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
				JToolBarHelper::apply('updatecondtextstay', JText::_('VRSAVE'));
				JToolBarHelper::save('updatecondtext', JText::_('VRSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('cancelcondtext', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VRC_COND_TEXT_MNG_TITLE'), 'vikrentcar');
			if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
				JToolBarHelper::save('createcondtext', JText::_('VRSAVECLOSE'));
				JToolBarHelper::apply('createcondtextstay', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('cancelcondtext', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
