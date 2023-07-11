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
 * Class handler for conditional rule "payment_methods".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRulePaymentMethods extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRMENUTENEIGHT');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_PAYM_DESCR');
		$this->ruleId = basename(__FILE__);
	}

	/**
	 * Displays the rule parameters.
	 * 
	 * @return 	void
	 */
	public function renderParams()
	{
		$this->vrc_app->loadSelect2();
		$payments = $this->loadPaymentMethods();
		$current_payments = $this->getParam('payments', array());
		?>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRMENUTENEIGHT'); ?></div>
			<div class="vrc-param-setting">
				<select name="<?php echo $this->inputName('payments', true); ?>" id="<?php echo $this->inputID('payments'); ?>" multiple="multiple">
				<?php
				foreach ($payments as $pdata) {
					?>
					<option value="<?php echo $pdata['id']; ?>"<?php echo is_array($current_payments) && in_array($pdata['id'], $current_payments) ? ' selected="selected"' : ''; ?>><?php echo $pdata['name']; ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#<?php echo $this->inputID('payments'); ?>').select2();
			});
		</script>
		<?php
	}

	/**
	 * Tells whether the rule is compliant.
	 * 
	 * @return 	bool 	True on success, false otherwise.
	 */
	public function isCompliant()
	{
		$payment = $this->getPropVal('booking', 'idpayment');
		if (empty($payment)) {
			return false;
		}
		$exppay = explode('=', $payment);
		$payment_info = VikRentCar::getPayment($exppay[0]);
		if (!is_array($payment_info)) {
			return false;
		}

		$payments = $this->getParam('payments', array());

		return in_array($payment_info['id'], $payments);
	}

	/**
	 * Internal function for this rule only.
	 * 
	 * @return 	array
	 */
	protected function loadPaymentMethods()
	{
		$payments = array();

		$dbo = JFactory::getDbo();
		$q = "SELECT `id`, `name` FROM `#__vikrentcar_gpayments` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$payments = $dbo->loadAssocList();
		}

		return $payments;
	}

}
