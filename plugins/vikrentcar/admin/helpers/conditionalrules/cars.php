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
 * Class handler for conditional rule "cars".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleCars extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRPVIEWORDERSTHREE');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_CARS_DESCR');
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
		$cars = $this->loadCars();
		$current_cars = $this->getParam('cars', array());
		$current_cars = !is_array($current_cars) ? array() : $current_cars;

		// check if we've got cars with sub-units defined
		$sub_units = [];
		foreach ($cars as $cdata) {
			if ($cdata['units'] < 2) {
				continue;
			}
			$car_features = VikRentCar::getCarParam('features', $cdata['params']);
			if (is_array($car_features) && count($car_features)) {
				$sub_units[$cdata['id']] = [
					'name' 	   => $cdata['name'],
					'units'    => $cdata['units'],
					'features' => $car_features,
				];
			}
		}

		?>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRCCARSASSIGNED'); ?></div>
			<div class="vrc-param-setting">
				<select name="<?php echo $this->inputName('cars', true); ?>" id="<?php echo $this->inputID('cars'); ?>" multiple="multiple" onchange="vrcChangeSubUnits();">
				<?php
				foreach ($cars as $cdata) {
					?>
					<option value="<?php echo $cdata['id']; ?>"<?php echo in_array($cdata['id'], $current_cars) ? ' selected="selected"' : ''; ?>><?php echo $cdata['name']; ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>

		<?php
		if (count($sub_units)) {
			?>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRCDISTFEATURECUNIT'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->printYesNoButtons($this->inputName('use_sub_units'), JText::_('VRYES'), JText::_('VRNO'), (int)$this->getParam('use_sub_units', 0), 1, 0, 'vrcToggleUseSubUnits();'); ?>
			</div>
		</div>
			<?php
			$init_display = (int)$this->getParam('use_sub_units', 0);
			foreach ($sub_units as $cid => $cdata) {
				$display_runits = ($init_display && in_array($cid, $current_cars));
				?>
		<div class="vrc-param-container vrc-rule-cars-rsubunits" data-cid="<?php echo $cid; ?>" style="<?php echo !$display_runits ? 'display: none;' : ''; ?>">
			<div class="vrc-param-label"><?php echo $cdata['name']; ?></div>
			<div class="vrc-param-setting">
				<select name="<?php echo $this->inputName("sub_unit_$cid"); ?>">
					<option value=""></option>
				<?php
				$cur_val = (int)$this->getParam("sub_unit_$cid", 0);
				for ($i = 1; $i <= $cdata['units']; $i++) {
					?>
					<option value="<?php echo $i; ?>"<?php echo $cur_val == $i ? ' selected="selected"' : ''; ?>><?php echo $this->getFirstFeature($i, $cdata['features']); ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>
				<?php
			}
		}
		?>
		
		<script type="text/javascript">
			jQuery(function() {
				jQuery('#<?php echo $this->inputID('cars'); ?>').select2();
			});

			function vrcToggleUseSubUnits() {
				jQuery('.vrc-rule-cars-rsubunits').hide();
				var use_sub_units = jQuery('input[name="<?php echo $this->inputName('use_sub_units'); ?>"]').prop('checked');
				if (use_sub_units) {
					var cars_selected = jQuery('#<?php echo $this->inputID('cars'); ?>').val();
					jQuery('.vrc-rule-cars-rsubunits').each(function() {
						var cid = jQuery(this).attr('data-cid');
						if (cars_selected && cars_selected.length && cars_selected.indexOf(cid) >= 0) {
							jQuery(this).show();
						} else {
							jQuery(this).hide().find('select').val('');
						}
					});
				} else {
					// hide all sub-units and make them empty
					jQuery('.vrc-rule-cars-rsubunits').hide().find('select').val('');
				}
			}

			function vrcChangeSubUnits() {
				var cars_selected = jQuery('#<?php echo $this->inputID('cars'); ?>').val();
				if (!cars_selected || !cars_selected.length) {
					// hide all sub-units and make them empty
					jQuery('.vrc-rule-cars-rsubunits').hide().find('select').val('');
				} else {
					// hide all sub-units, but don't touch their values
					jQuery('.vrc-rule-cars-rsubunits').hide();
					// check if the use of sub-units is enabled
					var use_sub_units = jQuery('input[name="<?php echo $this->inputName('use_sub_units'); ?>"]').prop('checked');
					// display only the sub-units for the selected cars
					if (use_sub_units) {
						for (var i = 0; i < cars_selected.length; i++) {
							var sub_units_cont = jQuery('.vrc-rule-cars-rsubunits[data-cid="' + cars_selected[i] + '"]');
							if (sub_units_cont && sub_units_cont.length) {
								// show the sub-units for this car
								sub_units_cont.show();
							}
						}
					}
				}
			}
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
		$car_booked = $this->getPropVal('booking', 'idcar', 0);
		if (empty($car_booked)) {
			return false;
		}
		$car_index = $this->getPropVal('booking', 'carindex', 0);

		$allowed_cars = $this->getParam('cars', []);

		$one_found = in_array($car_booked, $allowed_cars);

		// check sub-units
		$use_sub_units = (int)$this->getParam('use_sub_units', 0);
		if ($one_found && $use_sub_units) {
			// grab the sub-unit index for this car id
			$car_sub_unit_filt = (int)$this->getParam('sub_unit_' . $car_booked, 0);
			if (!empty($car_sub_unit_filt) && $car_index != $car_sub_unit_filt) {
				// the index of the car booked is not the one in the params
				return false;
			}
		}

		// return true if at least one room booked is in the parameters
		return $one_found;
	}

	/**
	 * Internal function for this rule only.
	 * 
	 * @return 	array
	 */
	protected function loadCars()
	{
		$cars = [];

		$dbo = JFactory::getDbo();
		$q = "SELECT `id`, `name`, `units`, `params` FROM `#__vikrentcar_cars` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$cars = $dbo->loadAssocList();
		}

		return $cars;
	}

	/**
	 * Internal function for this rule only.
	 * 
	 * @param 	int 	$i 			the room unit index to get.
	 * @param 	array 	$features 	the list of room features.
	 * 
	 * @return 	array
	 */
	protected function getFirstFeature($i, $features = [])
	{
		if (!is_array($features) || !isset($features[$i])) {
			return $i;
		}

		foreach ($features[$i] as $fkey => $fval) {
			if (!empty($fkey) && !empty($fval)) {
				return "#$i - " . JText::_($fkey) . ': ' . $fval;
			}
		}

		return "#$i";
	}
}
