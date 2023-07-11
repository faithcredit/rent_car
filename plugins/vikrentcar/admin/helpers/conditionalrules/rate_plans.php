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
 * Class handler for conditional rule "rate plans".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleRatePlans extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRMENUFIVE');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_RPL_DESCR');
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
		$rplans = $this->loadRatePlans();
		$current_rplans = $this->getParam('rplans', array());

		?>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRMENUFIVE'); ?></div>
			<div class="vrc-param-setting">
				<select name="<?php echo $this->inputName('rplans', true); ?>" id="<?php echo $this->inputID('rplans'); ?>" multiple="multiple">
				<?php
				foreach ($rplans as $rdata) {
					?>
					<option value="<?php echo $rdata['id']; ?>"<?php echo is_array($current_rplans) && in_array($rdata['id'], $current_rplans) ? ' selected="selected"' : ''; ?>><?php echo $rdata['name']; ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#<?php echo $this->inputID('rplans'); ?>').select2();
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
		$rplans_booked = $this->getProperty('booking', []);
		if (!is_array($rplans_booked) || !count($rplans_booked) || empty($rplans_booked['idtar'])) {
			return false;
		}

		// get filters
		$allowed_rplans = $this->getParam('rplans', []);

		$all_tariff_ids = [$rplans_booked['idtar']];

		// whether we have found a match
		$one_found = false;

		// get all rate plan IDs from tariffs
		$dbo = JFactory::getDbo();

		if (count($all_tariff_ids)) {
			$records = array();
			$q = "SELECT `idprice` FROM `#__vikrentcar_dispcost` WHERE `id` IN (" . implode(', ', $all_tariff_ids) . ")";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$records = $dbo->loadAssocList();
			}
			$all_price_ids = array();
			foreach ($records as $record) {
				array_push($all_price_ids, $record['idprice']);
			}

			// check if website rate plans are matching
			foreach ($all_price_ids as $idprice) {
				if (in_array($idprice, $allowed_rplans)) {
					$one_found = true;
					break;
				}
			}
		}

		// return true if at least one rate plan booked is in the parameters
		return $one_found;
	}

	/**
	 * Internal function for this rule only.
	 * 
	 * @return 	array
	 */
	protected function loadRatePlans()
	{
		$rplans = array();

		$dbo = JFactory::getDbo();
		$q = "SELECT `id`, `name` FROM `#__vikrentcar_prices` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$rplans = $dbo->loadAssocList();
		}

		return $rplans;
	}

}
