<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class handler for conditional rule "remaining balance".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleRemainingBalance extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRCTOTALREMAINING');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_REMBALANCE_DESCR');
		$this->ruleId = basename(__FILE__);
	}

	/**
	 * Displays the rule parameters.
	 * 
	 * @return 	void
	 */
	public function renderParams()
	{
		?>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRCTOTALREMAINING'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->printYesNoButtons($this->inputName('rembal'), JText::_('VRYES'), JText::_('VRNO'), (int)$this->getParam('rembal', 0), 1, 0); ?>
				<span class="vrc-param-setting-comment"><?php echo JText::_('VRC_CONDTEXT_RULE_REMBALANCE_DESCR'); ?></span>
			</div>
		</div>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRC_FULLY_PAID'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->printYesNoButtons($this->inputName('full_paid'), JText::_('VRYES'), JText::_('VRNO'), (int)$this->getParam('full_paid', 0), 1, 0); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Tells whether the rule is compliant.
	 * 
	 * @return 	bool 	True on success, false otherwise.
	 */
	public function isCompliant()
	{
		$total = (float)$this->getPropVal('booking', 'order_total', 0);
		$tot_paid = (float)$this->getPropVal('booking', 'totpaid', 0);

		if ((bool)$this->getParam('rembal', 0)) {
			// compliant if amount paid greater than zero but less than total paid
			return $total > 0 && $tot_paid > 0 && $tot_paid < $total;
		}

		if ((bool)$this->getParam('full_paid', 0)) {
			// compliant if fully paid
			return $total > 0 && $tot_paid >= $total;
		}

		return false;
	}

}
