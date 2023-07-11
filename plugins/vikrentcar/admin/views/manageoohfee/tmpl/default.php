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
$wselplaces = $this->wselplaces;

$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadSelect2();
$currencysymb = VikRentCar::getCurrencySymb(true);
$df = VikRentCar::getDateFormat(true);
$fromsel = "<select name=\"from\">\n";
for ($i=0; $i <= 23; $i++) {
	$h = $i < 10 ? '0'.$i : $i;
	$seconds = $i * 3600;
	for ($j=0; $j < 60; $j+=15) {
		$seconds += $j > 0 ? (15 * 60) : 0;
		$m = $j < 10 ? '0'.$j : $j;
		$fromsel .= '<option value="'.$seconds.'"'.(count($row) && $row['from'] == $seconds ? ' selected="selected"' : '').'>'.$h.' : '.$m.'</option>'."\n";
	}
}
$fromsel .= "</select>\n";
$tosel = "<select name=\"to\">\n";
for ($i=0; $i <= 23; $i++) {
	$h = $i < 10 ? '0'.$i : $i;
	$seconds = $i * 3600;
	for ($j=0; $j < 60; $j+=15) {
		$seconds += $j > 0 ? (15 * 60) : 0;
		$m = $j < 10 ? '0'.$j : $j;
		$tosel .= '<option value="'.$seconds.'"'.(count($row) && $row['to'] == $seconds ? ' selected="selected"' : '').'>'.$h.' : '.$m.'</option>'."\n";
	}

}
$tosel .= "</select>\n";
$dbo = JFactory::getDbo();
$q = "SELECT * FROM `#__vikrentcar_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"aliq\"><option value=\"\"> </option>\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikrentcar&task=iva\">".JText::_('VRNOIVAFOUND')."</a>";
}
$wselwdays = "<select id=\"wdays\" name=\"wdays[]\" multiple=\"multiple\" size=\"7\">\n";
$cur_wdays = count($row) ? explode(',', $row['wdays']) : array();
$wdays_map = array(JText::_('VRCSUNDAY'), JText::_('VRCMONDAY'), JText::_('VRCTUESDAY'), JText::_('VRCWEDNESDAY'), JText::_('VRCTHURSDAY'), JText::_('VRCFRIDAY'), JText::_('VRCSATURDAY'));
for ($oj=0; $oj < 7; $oj++) { 
	$wselwdays .= "<option value=\"".$oj."\"".(in_array('-'.$oj.'-', $cur_wdays) ? " selected=\"selected\"" : "").">".$wdays_map[$oj]."</option>\n";
}
$wselwdays .= "</select>\n";
?>
<script type="text/javascript">
function vrcMaxChargeOohf() {
	var pick_charge = jQuery("#pickcharge").val().length ? parseFloat(jQuery("#pickcharge").val()) : 0.00;
	var drop_charge = jQuery("#dropcharge").val().length ? parseFloat(jQuery("#dropcharge").val()) : 0.00;
	var max_charge = pick_charge + drop_charge;
	jQuery("#maxcharge").val(max_charge.toFixed(2));
}
jQuery(document).ready(function() {
	jQuery(".vrc-select-all").click(function() {
		var nextsel = jQuery(this).next("select");
			nextsel.find("option").prop('selected', true);
			nextsel.trigger('change');
	});
	jQuery("#pickcharge, #dropcharge").keyup(function() {
		vrcMaxChargeOohf();
	});
	jQuery('#wdays, #idcars, #idplace').select2();
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
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESONE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="name" value="<?php echo count($row) ? htmlspecialchars($row['oohname']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESTWO'); ?></div>
							<div class="vrc-param-setting"><?php echo $fromsel; ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESTHREE'); ?></div>
							<div class="vrc-param-setting"><?php echo $tosel; ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESFOUR'); ?></div>
							<div class="vrc-param-setting">
								<input type="number" step="any" id="pickcharge" name="pickcharge" placeholder="0.00" value="<?php echo count($row) ? (float)$row['pickcharge'] : ''; ?>" style="width: 100px !important;"/> <?php echo $currencysymb; ?>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESFIVE'); ?></div>
							<div class="vrc-param-setting">
								<input type="number" step="any" id="dropcharge" name="dropcharge" placeholder="0.00" value="<?php echo count($row) ? (float)$row['dropcharge'] : ''; ?>" style="width: 100px !important;"/> <?php echo $currencysymb; ?>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESSIX'); ?></div>
							<div class="vrc-param-setting">
								<input type="number" step="any" id="maxcharge" name="maxcharge" value="<?php echo count($row) ? (float)$row['maxcharge'] : ''; ?>" style="width: 100px !important;"/> <?php echo $currencysymb; ?>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESNINE'); ?></div>
							<div class="vrc-param-setting">
								<select name="type">
									<option value="1"<?php echo count($row) && $row['type'] == 1 ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', JText::_('VRCPVIEWOOHFEESTEN')); ?></option>
									<option value="2"<?php echo count($row) && $row['type'] == 2 ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', JText::_('VRCPVIEWOOHFEESELEVEN')); ?></option>
									<option value="3"<?php echo count($row) && $row['type'] == 3 ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', JText::_('VRCPVIEWOOHFEESTWELVE')); ?></option>
								</select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESTAX'); ?></div>
							<div class="vrc-param-setting"><?php echo $wiva; ?></div>
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
							<div class="vrc-param-label"><?php echo JText::_('VRCWEEKDAYS'); ?></div>
							<div class="vrc-param-setting">
								<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
								<?php echo $wselwdays; ?>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESSEVEN'); ?></div>
							<div class="vrc-param-setting">
								<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
								<?php echo $wselcars; ?>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPVIEWOOHFEESEIGHT'); ?></div>
							<div class="vrc-param-setting">
								<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
								<?php echo $wselplaces; ?>
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
