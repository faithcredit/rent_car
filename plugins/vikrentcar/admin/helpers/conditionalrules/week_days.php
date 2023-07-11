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
 * Class handler for conditional rule "week days".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleWeekDays extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRCSEASONDAYS');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_WDAYS_DESCR');
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
		$wdays = array(
			JText::_('VRCSUNDAY'),
			JText::_('VRCMONDAY'),
			JText::_('VRCTUESDAY'),
			JText::_('VRCWEDNESDAY'),
			JText::_('VRCTHURSDAY'),
			JText::_('VRCFRIDAY'),
			JText::_('VRCSATURDAY'),
		);
		$current_wdays = $this->getParam('wdays', array());
		?>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRCWEEKDAYS'); ?></div>
			<div class="vrc-param-setting">
				<select name="<?php echo $this->inputName('wdays', true); ?>" id="<?php echo $this->inputID('wdays'); ?>" multiple="multiple">
				<?php
				foreach ($wdays as $wdk => $wdv) {
					?>
					<option value="<?php echo $wdk; ?>"<?php echo is_array($current_wdays) && in_array($wdk, $current_wdays) ? ' selected="selected"' : ''; ?>><?php echo $wdv; ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRPVIEWCUSTOMFTWO'); ?></div>
			<div class="vrc-param-setting">
				<select name="<?php echo $this->inputName('type'); ?>" id="<?php echo $this->inputID('type'); ?>">
					<option value=""></option>
					<option value="pickup"<?php echo $this->getParam('type', '') == 'pickup' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRPVIEWORDERSFOUR'); ?></option>
					<option value="dropoff"<?php echo $this->getParam('type', '') == 'dropoff' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRPVIEWORDERSFIVE'); ?></option>
					<option value="both"<?php echo $this->getParam('type', '') == 'both' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRPVIEWORDERSFOUR') . ' | ' . JText::_('VRPVIEWORDERSFIVE'); ?></option>
				</select>
			</div>
		</div>
		
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#<?php echo $this->inputID('wdays'); ?>').select2();
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
		$book_in  = $this->getPropVal('booking', 'ritiro', 0);
		$book_out = $this->getPropVal('booking', 'consegna', 0);

		if (empty($book_in) || empty($book_out)) {
			return false;
		}

		$info_in  = getdate($book_in);
		$info_out = getdate($book_out);

		$involved_wdays = $this->getParam('wdays', array());
		$involved_type  = $this->getParam('type', 'pickup');

		if ($involved_type == 'pickup') {
			return (in_array($info_in['wday'], $involved_wdays));
		}

		if ($involved_type == 'dropoff') {
			return (in_array($info_out['wday'], $involved_wdays));
		}

		return (in_array($info_in['wday'], $involved_wdays) || in_array($info_out['wday'], $involved_wdays));
	}

}
