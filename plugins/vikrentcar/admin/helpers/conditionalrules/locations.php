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
 * Class handler for conditional rule "locations".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleLocations extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRNEWSEASONEIGHT');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_LOCATIONS_DESCR');
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
		$locations = $this->loadLocations();
		$current_locations = $this->getParam('locations', array());

		?>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRNEWSEASONEIGHT'); ?></div>
			<div class="vrc-param-setting">
				<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
				<select name="<?php echo $this->inputName('locations', true); ?>" id="<?php echo $this->inputID('locations'); ?>" multiple="multiple">
				<?php
				foreach ($locations as $ldata) {
					?>
					<option value="<?php echo $ldata['id']; ?>"<?php echo is_array($current_locations) && in_array($ldata['id'], $current_locations) ? ' selected="selected"' : ''; ?>><?php echo $ldata['name']; ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRPVIEWORDERSFOUR'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->printYesNoButtons($this->inputName('pickup'), JText::_('VRYES'), JText::_('VRNO'), (int)$this->getParam('pickup', 0), 1, 0); ?>
			</div>
		</div>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRPVIEWORDERSFIVE'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->printYesNoButtons($this->inputName('dropoff'), JText::_('VRYES'), JText::_('VRNO'), (int)$this->getParam('dropoff', 0), 1, 0); ?>
			</div>
		</div>
		
		<script type="text/javascript">
			jQuery(function() {

				jQuery('#<?php echo $this->inputID('locations'); ?>').select2();

				jQuery('.vrc-select-all').click(function() {
					var nextsel = jQuery(this).next("select");
					if (!nextsel || !nextsel.length) {
						return false;
					}
					nextsel.find("option").prop('selected', true);
					nextsel.trigger('change');
				});

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
		$pick_place = $this->getPropVal('booking', 'idplace', 0);
		$drop_place = $this->getPropVal('booking', 'idreturnplace', 0);

		if (empty($pick_place) || empty($drop_place)) {
			return false;
		}

		// get filters
		$allowed_locations = $this->getParam('locations', []);
		$for_pickup  = (bool)$this->getParam('pickup', 0);
		$for_dropoff = (bool)$this->getParam('dropoff', 0);

		if ($for_pickup && !in_array($pick_place, $allowed_locations)) {
			return false;
		}

		if ($for_dropoff && !in_array($drop_place, $allowed_locations)) {
			return false;
		}

		return true;
	}

	/**
	 * Internal function for this rule only.
	 * 
	 * @return 	array
	 */
	protected function loadLocations()
	{
		$locations = array();

		$dbo = JFactory::getDbo();
		$q = "SELECT `id`, `name` FROM `#__vikrentcar_places` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$locations = $dbo->loadAssocList();
		}

		return $locations;
	}

}
