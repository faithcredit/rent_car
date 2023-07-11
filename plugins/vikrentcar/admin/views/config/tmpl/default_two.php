<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$vrc_app = VikRentCar::getVrcApplication();
$formatvals = VikRentCar::getNumberFormatData(true);
$formatparts = explode(':', $formatvals);
?>

<div class="vrc-config-maintab-left">
	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRCCONFIGCURRENCYPART'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHREECURNAME'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="currencyname" value="<?php echo JHtml::_('esc_attr', VikRentCar::getCurrencyName()); ?>" size="10"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHREECURSYMB'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="currencysymb" value="<?php echo JHtml::_('esc_attr', VikRentCar::getCurrencySymb(true)); ?>" size="10"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHREECURCODEPP'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="currencycodepp" value="<?php echo JHtml::_('esc_attr', VikRentCar::getCurrencyCodePp()); ?>" size="10"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGNUMDECIMALS'); ?></div>
					<div class="vrc-param-setting"><input type="number" name="numdecimals" min="0" value="<?php echo (int)$formatparts[0]; ?>"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGNUMDECSEPARATOR'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="decseparator" value="<?php echo JHtml::_('esc_attr', $formatparts[1]); ?>" size="2"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGNUMTHOSEPARATOR'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="thoseparator" value="<?php echo JHtml::_('esc_attr', $formatparts[2]); ?>" size="2"/></div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<div class="vrc-config-maintab-right">
	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRCCONFIGPAYMPART'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTWOFIVE'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('ivainclusa', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::ivaInclusa(true) ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTAXSUMMARY'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('taxsummary', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showTaxOnSummaryOnly(true) ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_CONF_ALLOWMULTIPAYMENTS'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRC_CONF_ALLOWMULTIPAYMENTS'), 'content' => JText::_('VRC_CONF_ALLOWMULTIPAYMENTS_HELP'))); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('multipay', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::multiplePayments() ? 1 : 0), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTWOTHREE'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('paytotal', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::payTotal() ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTWOFOUR'); ?></div>
					<div class="vrc-param-setting">
						<input type="number" step="any" min="0" name="payaccpercent" value="<?php echo VikRentCar::getAccPerCent(); ?>"/> 
						<select id="typedeposit" name="typedeposit">
							<option value="pcent">%</option>
							<option value="fixed"<?php echo (VikRentCar::getTypeDeposit(true) == "fixed" ? ' selected="selected"' : ''); ?>><?php echo JHtml::_('esc_html', VikRentCar::getCurrencySymb()); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTWOSIX'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="paymentname" value="<?php echo JHtml::_('esc_attr', VikRentCar::getPaymentName()); ?>" size="25"/></div>
				</div>
			</div>
		</div>
	</fieldset>
</div>
