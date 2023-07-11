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
 * Class handler for conditional rule "booking dates".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleBookingDates extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRC_CONDTEXT_RULE_BOOKDATES');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_BOOKDATES_DESCR');
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
			<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONDFROMRANGE'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->getCalendar($this->getParam('from_date', ''), $this->inputName('from_date'), $this->inputID('from_date'), $this->wdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
			</div>
		</div>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONDTORANGE'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->getCalendar($this->getParam('to_date', ''), $this->inputName('to_date'), $this->inputID('to_date'), $this->wdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
			</div>
		</div>
		<?php
		if ($this->getParams() !== null) {
			// some date-picker calendars may need to have their default value populated when the document is ready
			?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#<?php echo $this->inputID('from_date'); ?>').val('<?php echo $this->getParam('from_date', ''); ?>').attr('data-alt-value', '<?php echo $this->getParam('from_date', ''); ?>');
				jQuery('#<?php echo $this->inputID('to_date'); ?>').val('<?php echo $this->getParam('to_date', ''); ?>').attr('data-alt-value', '<?php echo $this->getParam('to_date', ''); ?>');
			});
		</script>
			<?php
		}
	}

	/**
	 * Tells whether the rule is compliant.
	 * 
	 * @return 	bool 	True on success, false otherwise.
	 */
	public function isCompliant()
	{
		$book_time = $this->getPropVal('booking', 'ts');

		if (!$book_time) {
			return false;
		}

		$from_date = $this->getParam('from_date', '');

		// return true if booking date is inside the dates interval
		return (
			$book_time >= VikRentCar::getDateTimestamp($from_date, 0, 0, 0) && 
			$book_time <= VikRentCar::getDateTimestamp($this->getParam('to_date', $from_date), 23, 59, 59)
		);
	}

}
