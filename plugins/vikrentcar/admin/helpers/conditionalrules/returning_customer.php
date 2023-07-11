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
 * Class handler for conditional rule "returning customer".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleReturningCustomer extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRC_CONDTEXT_RULE_RETCUST');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_RETCUST_DESCR');
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
			<div class="vrc-param-label"><?php echo JText::_('VRC_CONDTEXT_RULE_RETCUST'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->printYesNoButtons($this->inputName('returning'), JText::_('VRYES'), JText::_('VRNO'), (int)$this->getParam('returning', 0), 1, 0); ?>
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
		$returning = (bool)$this->getParam('returning', 0);
		if (!$returning) {
			return true;
		}

		$book_id = $this->getPropVal('booking', 'id', '');
		if (empty($book_id)) {
			return true;
		}

		$cpin = VikRentCar::getCPinIstance();
		$customer = $cpin->getCustomerFromBooking($book_id);
		if (!is_array($customer) || !count($customer)) {
			// customer not found
			return false;
		}

		$dbo = JFactory::getDbo();
		$q = "SELECT `co`.`idcustomer`, `co`.`idorder`, `o`.`id` FROM `#__vikrentcar_customers_orders` AS `co` LEFT JOIN `#__vikrentcar_orders` AS `o` ON `co`.`idorder`=`o`.`id` WHERE `co`.`idcustomer`=" . $customer['id'] . " AND `o`.`status`='confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();

		// we need at least two confirmed orders
		return ($dbo->getNumRows() > 1);
	}

}
