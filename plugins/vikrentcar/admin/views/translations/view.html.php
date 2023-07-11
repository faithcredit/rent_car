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

class VikRentCarViewTranslations extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$vrc_tn = VikRentCar::getTranslator();
		
		$this->vrc_tn = &$vrc_tn;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRCMAINTRANSLATIONSTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
			JToolBarHelper::apply( 'savetranslationstay', JText::_('VRSAVE'));
			JToolBarHelper::spacer();
			JToolBarHelper::save( 'savetranslation', JText::_('VRSAVECLOSE'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::cancel( 'cancel', JText::_('VRBACK'));
		JToolBarHelper::spacer();
	}

}
