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
 * Class handler for conditional rule "extra email".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleExtraEmail extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRC_CONDTEXT_RULE_EXTRAMAIL');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_EXTRAMAIL_DESCR');
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
			<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMEREMAIL'); ?></div>
			<div class="vrc-param-setting">
				<input type="text" name="<?php echo $this->inputName('extra_email'); ?>" value="<?php echo $this->getParam('extra_email', ''); ?>" />
				<span class="vrc-param-setting-comment"><?php echo JText::_('VRC_CONDTEXT_RULE_SEPEMAIL'); ?></span>
			</div>
		</div>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRC_CONDTEXT_RULE_BCCEMAIL'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->printYesNoButtons($this->inputName('bcc'), JText::_('VRYES'), JText::_('VRNO'), (int)$this->getParam('bcc', 0), 1, 0); ?>
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
		// this is not a real filter-rule, so we always return true
		return true;
	}

	/**
	 * Override callback action method to set the additional email addresses.
	 * 
	 * @return 	void
	 */
	public function callbackAction()
	{
		$extra_recipients = $this->getParam('extra_email', '');
		if (empty($extra_recipients)) {
			return;
		}
		
		if (strpos($extra_recipients, ',') !== false) {
			$extra_recipients = explode(',', $extra_recipients);
		} elseif (strpos($extra_recipients, ';') !== false) {
			$extra_recipients = explode(';', $extra_recipients);
		} else {
			$extra_recipients = array($extra_recipients);
		}
		
		// register additional email recipients
		VikRentCar::addAdminEmailRecipient($extra_recipients, (bool)$this->getParam('bcc', 0));

		return;
	}

}
