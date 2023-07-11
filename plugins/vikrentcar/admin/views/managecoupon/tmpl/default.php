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
$wselcars = $this->wselcars;

JHTML::_('behavior.calendar');
$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadSelect2();
$currencysymb = VikRentCar::getCurrencySymb(true);
$df = VikRentCar::getDateFormat(true);
$fromdate = "";
$todate = "";
if (count($row) && strlen($row['datevalid']) > 0) {
	$dateparts = explode("-", $row['datevalid']);
	if ($df == "%d/%m/%Y") {
		$udf = 'd/m/Y';
	} elseif ($df == "%m/%d/%Y") {
		$udf = 'm/d/Y';
	} else {
		$udf = 'Y/m/d';
	}
	$fromdate = date($udf, $dateparts[0]);
	$todate = date($udf, $dateparts[1]);
}
?>
<script type="text/javascript">
function setVehiclesList() {
	if (document.adminForm.allvehicles.checked == true) {
		document.getElementById('vrcvlist').style.display='none';
	} else {
		document.getElementById('vrcvlist').style.display='block';
	}
	return true;
}
jQuery(document).ready(function() {
	jQuery('select[name="idcars[]"]').select2();
});
</script>
<form name="adminForm" action="index.php" method="post" id="adminForm">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWCOUPONONE'); ?></div>
							<div class="vrc-param-setting">
								<input type="text" name="code" value="<?php echo count($row) ? htmlspecialchars($row['code']) : ''; ?>" size="30"/>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWCOUPONTWO'); ?></div>
							<div class="vrc-param-setting">
								<select name="type">
									<option value="1"<?php echo (count($row) && $row['type'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCCOUPONTYPEPERMANENT'); ?></option>
									<option value="2"<?php echo (count($row) && $row['type'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCCOUPONTYPEGIFT'); ?></option>
								</select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWCOUPONTHREE'); ?></div>
							<div class="vrc-param-setting">
								<select name="percentot">
									<option value="1"<?php echo (count($row) && $row['percentot'] == 1 ? " selected=\"selected\"" : ""); ?>>%</option>
									<option value="2"<?php echo (count($row) && $row['percentot'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option>
								</select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWCOUPONFOUR'); ?></div>
							<div class="vrc-param-setting">
								<input type="number" name="value" step="any" value="<?php echo count($row) ? (float)$row['value'] : ''; ?>" style="width: 100px !important;"/>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="vrc-config-maintab-right">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDSETTINGS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWCOUPONFIVE'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('allvehicles', JText::_('VRCNEWCOUPONEIGHT'), JText::_('VRCNEWCOUPONEIGHT'), (!count($row) || (count($row) && $row['allvehicles'] == 1) ? 1 : 0), 1, 0, 'setVehiclesList();'); ?>
								<span id="vrcvlist" style="display: <?php echo (!count($row) || (count($row) && $row['allvehicles'] == 1) ? "none" : "block"); ?>;"><?php echo $wselcars; ?></span>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWCOUPONSIX'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCNEWCOUPONSIX'), 'content' => JText::_('VRCNEWCOUPONNINE'))); ?></div>
							<div class="vrc-param-setting">
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vrcrestrdrangesp">'.JText::_('VRNEWRESTRICTIONDFROMRANGE').'</span>'.$vrc_app->getCalendar($fromdate, 'from', 'from', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vrcrestrdrangesp">'.JText::_('VRNEWRESTRICTIONDTORANGE').'</span>'.$vrc_app->getCalendar($todate, 'to', 'to', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWCOUPONSEVEN'); ?></div>
							<div class="vrc-param-setting">
								<input type="number" step="any" name="mintotord" value="<?php echo count($row) ? (float)$row['mintotord'] : ''; ?>" style="width: 100px !important;"/> <?php echo $currencysymb; ?>
							</div>
						</div>
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
<?php
if (strlen($fromdate) > 0 && strlen($todate) > 0) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#from').val('<?php echo $fromdate; ?>').attr('data-alt-value', '<?php echo $fromdate; ?>');
	jQuery('#to').val('<?php echo $todate; ?>').attr('data-alt-value', '<?php echo $todate; ?>');
});
</script>
<?php
}
