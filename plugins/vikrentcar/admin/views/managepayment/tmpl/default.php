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

$row = $this->row;

$vrc_app = VikRentCar::getVrcApplication();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));

/**
 * @wponly 	Use the dispatcher to retrieve the list of the installed gateways.
 *
 * @since 	1.0.5
 */
JLoader::import('adapter.payment.dispatcher');
$allf = JPaymentDispatcher::getSupportedDrivers('vikrentcar');
//

$force_file = VikRequest::getString('file', '', 'request');

$psel = "";
if (@count($allf) > 0) {
	$classfiles = array();
	foreach ($allf as $af) {
		$classfiles[] = basename($af, '.php');
	}
	sort($classfiles);
	$psel = "<select name=\"payment\" onchange=\"vikLoadPaymentParameters(this.value);\">\n<option value=\"\"></option>\n";
	foreach ($classfiles as $cf) {
		$selected = ((count($row) && $cf == basename($row['file'], '.php')) || (!count($row) && $cf == $force_file));
		$psel .= "<option value=\"".$cf."\"".($selected ? " selected=\"selected\"" : "").">".$cf."</option>\n";
	}
	$psel .= "</select>";
}

$currencysymb = VikRentCar::getCurrencySymb(true);
$payparams = count($row) ? VikRentCar::displayPaymentParameters($row['file'], $row['params']) : '';
if (!count($row) && !empty($force_file)) {
	// trigger the loading of the pre-selected driver params
	$payparams = VikRentCar::displayPaymentParameters($force_file, null);
}
?>
<script type="text/javascript">
function vikLoadPaymentParameters(pfile) {
	if (pfile.length > 0) {
		jQuery("#vikparameters").html('<p><?php echo addslashes(JTEXT::_('VIKLOADING')); ?></p>');
		jQuery.ajax({
			type: "POST",
			url: "<?php echo VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=loadpaymentparams&tmpl=component'); ?>",
			data: { phpfile: pfile }
		}).done(function(res) {
			jQuery("#vikparameters").html(res);
		});
	} else {
		jQuery("#vikparameters").html('<p>--------</p>');
	}
}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWPAYMENTONE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="name" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWPAYMENTTWO'); ?></div>
							<div class="vrc-param-setting"><?php echo $psel; ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWPAYMENTTHREE'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('published', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$row['published'] : 1), 1, 0); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWPAYMENTCHARGEORDISC'); ?></div>
							<div class="vrc-param-setting">
								<select name="ch_disc">
									<option value="1"<?php echo (count($row) && $row['ch_disc'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWPAYMENTCHARGEPLUS'); ?></option>
									<option value="2"<?php echo (count($row) && $row['ch_disc'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWPAYMENTDISCMINUS'); ?></option>
								</select> <input type="number" step="any" name="charge" value="<?php echo count($row) ? (float)$row['charge'] : ''; ?>" size="5"/> 
								<select name="val_pcent">
									<option value="1"<?php echo (count($row) && $row['val_pcent'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option>
									<option value="2"<?php echo (count($row) && $row['val_pcent'] == 2 ? " selected=\"selected\"" : ""); ?>>%</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDSETTINGS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPAYMENTSHELPCONFIRMTXT'), 'content' => JText::_('VRCPAYMENTSHELPCONFIRM'))); ?> <?php echo JText::_('VRNEWPAYMENTEIGHT'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('setconfirmed', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$row['setconfirmed'] : 0), 1, 0); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWPAYMENTNINE'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('shownotealw', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$row['shownotealw'] : 0), 1, 0); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRC_PAYBUT_POS'); ?></div>
							<div class="vrc-param-setting">
								<select id="vrc-outposition" name="outposition" onchange="vrcUpdateOutposSkeleton();">
									<option value="top"<?php echo !count($row) || (count($row) && $row['outposition'] == 'top') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRC_PAYBUT_POS_TOP'); ?></option>
									<option value="middle"<?php echo count($row) && $row['outposition'] == 'middle' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRC_PAYBUT_POS_MIDDLE'); ?></option>
									<option value="bottom"<?php echo count($row) && $row['outposition'] == 'bottom' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRC_PAYBUT_POS_BOTTOM'); ?></option>
								</select>
								<?php
								$current_paybut_pos = 'top';
								if (count($row) && !empty($row['outposition'])) {
									$current_paybut_pos = $row['outposition'];
								}
								?>
								<div class="vrc-paybutpos-wrap">
									<div class="vrc-paybutpos-skeleton-card">
										<div class="vrc-paybutpos-skeleton-card-inner">
											<div class="vrc-paybutpos-skeleton-el-status">&nbsp;</div>
											<div class="vrc-paybutpos-skeleton-el-pos vrc-paybutpos-skeleton-el-pos-top" style="<?php echo $current_paybut_pos != 'top' ? 'display: none;' : ''; ?>">
												<span class="vrc-paybutpos-skeleton-el-paybut"><?php VikRentCarIcons::e('credit-card'); ?></span>
											</div>
											<div class="vrc-paybutpos-skeleton-el-customer-infos">
												<div class="vrc-paybutpos-skeleton-el-customer">
													<span><?php VikRentCarIcons::e('users'); ?></span>
												</div>
												<div class="vrc-paybutpos-skeleton-el-customer">
													<span><?php VikRentCarIcons::e('passport'); ?></span>
												</div>
											</div>
											<div class="vrc-paybutpos-skeleton-el-pos vrc-paybutpos-skeleton-el-pos-middle" style="<?php echo $current_paybut_pos != 'middle' ? 'display: none;' : ''; ?>">
												<span class="vrc-paybutpos-skeleton-el-paybut"><?php VikRentCarIcons::e('credit-card'); ?></span>
											</div>
											<div class="vrc-paybutpos-skeleton-el-car-infos">
												<div class="vrc-paybutpos-skeleton-el-car">
													<span><?php VikRentCarIcons::e('car'); ?></span>
												</div>
											</div>
											<div class="vrc-paybutpos-skeleton-el-pos vrc-paybutpos-skeleton-el-pos-bottom" style="<?php echo $current_paybut_pos != 'bottom' ? 'display: none;' : ''; ?>">
												<span class="vrc-paybutpos-skeleton-el-paybut"><?php VikRentCarIcons::e('credit-card'); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRC_PAYMET_LOGO'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->getMediaField('logo', (count($row) && !empty($row['logo']) ? $row['logo'] : null)); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWPAYMENTFIVE'); ?></div>
							<div class="vrc-param-setting">
								<?php
								if (interface_exists('Throwable')) {
									/**
									 * With PHP >= 7 supporting throwable exceptions for Fatal Errors
									 * we try to avoid issues with third party plugins that make use
									 * of the WP native function get_current_screen().
									 * 
									 * @wponly
									 */
									try {
										echo $editor->display( "note", (count($row) ? $row['note'] : ''), 400, 200, 70, 20 );
									} catch (Throwable $t) {
										echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
									}
								} else {
									// we cannot catch Fatal Errors in PHP 5.x
									echo $editor->display( "note", (count($row) ? $row['note'] : ''), 400, 200, 70, 20 );
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="vrc-config-maintab-right">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRPAYMENTPARAMETERS'); ?></legend>
					<div class="vrc-params-container vrc-payment-params-container" id="vikparameters">
						<?php echo $payparams; ?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar" />
<?php
if (count($row)) {
	?>
	<input type="hidden" name="where" value="<?php echo (int)$row['id']; ?>">
	<?php
}
?>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
	
	function vrcUpdateOutposSkeleton() {
		var pos = jQuery('#vrc-outposition').val();
		pos = !pos || !pos.length ? 'top' : pos;
		jQuery('.vrc-paybutpos-skeleton-el-pos').hide();
		jQuery('.vrc-paybutpos-skeleton-el-pos-' + pos).show();
	}

	jQuery(document).ready(function() {
		vrcUpdateOutposSkeleton();
	});

</script>
