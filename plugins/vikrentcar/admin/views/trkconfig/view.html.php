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

class VikRentCarViewTrkconfig extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		// require the tracker class
		VikRentCar::getTracker(true);
		//

		$trksettings = VikRentCarTracker::loadSettings();
		
		$this->trksettings = $trksettings;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRCMAINTRACKINGSTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentcar')) {
			JToolBarHelper::apply( 'savetrkconfigstay', JText::_('VRSAVE'));
			JToolBarHelper::save( 'savetrkconfig', JText::_('VRSAVECLOSE'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::cancel( 'canceltrk', JText::_('VRBACK'));
		JToolBarHelper::spacer();
	}

}
