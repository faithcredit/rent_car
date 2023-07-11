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
 * Class handler for conditional rule "countries".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleCountries extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRC_CONDTEXT_RULE_COUNTRIES');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_COUNTRIES_DESCR');
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
		$countries = $this->loadCountries();
		$current_countries = $this->getParam('countries', array());
		?>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRC_CONDTEXT_RULE_COUNTRIES'); ?></div>
			<div class="vrc-param-setting">
				<select name="<?php echo $this->inputName('countries', true); ?>" id="<?php echo $this->inputID('countries'); ?>" multiple="multiple">
				<?php
				foreach ($countries as $cdata) {
					?>
					<option value="<?php echo $cdata['country_3_code']; ?>"<?php echo is_array($current_countries) && in_array($cdata['country_3_code'], $current_countries) ? ' selected="selected"' : ''; ?>><?php echo $cdata['country_name']; ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#<?php echo $this->inputID('countries'); ?>').select2();
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
		$country_booked = $this->getPropVal('booking', 'country');
		if (empty($country_booked)) {
			return false;
		}

		$allowed_countries = $this->getParam('countries', array());

		return (in_array($country_booked, $allowed_countries));
	}

	/**
	 * Internal function for this rule only.
	 * 
	 * @return 	array
	 */
	protected function loadCountries()
	{
		$countries = array();

		$dbo = JFactory::getDbo();
		$q = "SELECT `country_name`, `country_3_code` FROM `#__vikrentcar_countries` ORDER BY `country_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$countries = $dbo->loadAssocList();
		}

		return $countries;
	}

}
