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
 * Class handler for conditional rule "number of days".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleNumberOfDays extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRDAYS');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_NOD_DESCR');
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
			<div class="vrc-param-label"><?php echo JText::_('VRDAYSFROM'); ?></div>
			<div class="vrc-param-setting">
				<input type="number" name="<?php echo $this->inputName('from_nights'); ?>" value="<?php echo $this->getParam('from_nights', ''); ?>" min="1" />
			</div>
		</div>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRDAYSTO'); ?></div>
			<div class="vrc-param-setting">
				<input type="number" name="<?php echo $this->inputName('to_nights'); ?>" value="<?php echo $this->getParam('to_nights', ''); ?>" min="1" />
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
		$num_nights = (int)$this->getPropVal('booking', 'days', 0);

		$from_nights = (int)$this->getParam('from_nights', 1);
		$to_nights = (int)$this->getParam('to_nights', $from_nights);

		// return true if number of nights is inside the range of nights
		return $num_nights >= $from_nights && $num_nights <= $to_nights;
	}

}
