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

$data = $this->data;
$cars = $this->cars;

$vrc_app = new VrcApplication();
$vrc_app->loadSelect2();
$df = VikRentCar::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$cdf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$cdf = 'm/d/Y';
} else {
	$cdf = 'Y/m/d';
}
$carsel = '';
if (is_array($cars) && count($cars) > 0) {
	$nowcars = count($data) && !empty($data['idcars']) && $data['allcars'] == 0 ? explode(';', $data['idcars']) : array();
	$carsel = '<select id="restr-idcars" name="idcars[]" multiple="multiple">'."\n";
	foreach ($cars as $r) {
		$carsel .= '<option value="'.$r['id'].'"'.(in_array('-'.$r['id'].'-', $nowcars) ? ' selected="selected"' : '').'>'.$r['name'].'</option>'."\n";
	}
	$carsel .= '</select>';
}
//CTA and CTD
$cur_setcta = count($data) && !empty($data['ctad']) ? explode(',', $data['ctad']) : array();
$cur_setctd = count($data) && !empty($data['ctdd']) ? explode(',', $data['ctdd']) : array();
$wdaysmap = array('0' => JText::_('VRCSUNDAY'), '1' => JText::_('VRCMONDAY'), '2' => JText::_('VRCTUESDAY'), '3' => JText::_('VRCWEDNESDAY'), '4' => JText::_('VRCTHURSDAY'), '5' => JText::_('VRCFRIDAY'), '6' => JText::_('VRCSATURDAY'));
$ctasel = '<select id="restr-ctad" name="ctad[]" multiple="multiple" size="7">'."\n";
foreach ($wdaysmap as $wdk => $wdv) {
	$ctasel .= '<option value="'.$wdk.'"'.(in_array('-'.$wdk.'-', $cur_setcta) ? ' selected="selected"' : '').'>'.$wdv.'</option>'."\n";
}
$ctasel .= '</select>';
$ctdsel = '<select id="restr-ctdd" name="ctdd[]" multiple="multiple" size="7">'."\n";
foreach ($wdaysmap as $wdk => $wdv) {
	$ctdsel .= '<option value="'.$wdk.'"'.(in_array('-'.$wdk.'-', $cur_setctd) ? ' selected="selected"' : '').'>'.$wdv.'</option>'."\n";
}
$ctdsel .= '</select>';
//
$dfromval = count($data) && !empty($data['dfrom']) ? date($cdf, $data['dfrom']) : '';
$dtoval = count($data) && !empty($data['dto']) ? date($cdf, $data['dto']) : '';
$vrcra1 = '';
$vrcra2 = '';
$vrcrb1 = '';
$vrcrb2 = '';
$vrcrc1 = '';
$vrcrc2 = '';
$vrcrd1 = '';
$vrcrd2 = '';
if (count($data) && strlen($data['wdaycombo']) > 0) {
	$vrccomboparts = explode(':', $data['wdaycombo']);
	foreach($vrccomboparts as $kc => $cb) {
		if (!empty($cb)) {
			$nowcombo = explode('-', $cb);
			if ($kc == 0) {
				$vrcra1 = $nowcombo[0];
				$vrcra2 = $nowcombo[1];
			} elseif ($kc == 1) {
				$vrcrb1 = $nowcombo[0];
				$vrcrb2 = $nowcombo[1];
			} elseif ($kc == 2) {
				$vrcrc1 = $nowcombo[0];
				$vrcrc2 = $nowcombo[1];
			} elseif ($kc == 3) {
				$vrcrd1 = $nowcombo[0];
				$vrcrd2 = $nowcombo[1];
			}
		}
	}
}
$arrwdays = array(1 => JText::_('VRCMONDAY'),
		2 => JText::_('VRCTUESDAY'),
		3 => JText::_('VRCWEDNESDAY'),
		4 => JText::_('VRCTHURSDAY'),
		5 => JText::_('VRCFRIDAY'),
		6 => JText::_('VRCSATURDAY'),
		0 => JText::_('VRCSUNDAY')
);
?>
<script type="text/javascript">
function vrcSecondArrWDay() {
	var wdayone = document.adminForm.wday.value;
	if (wdayone != "") {
		document.getElementById("vrwdaytwodivid").style.display = "inline-block";
		document.adminForm.cta.checked = false;
		document.adminForm.ctd.checked = false;
		vrcToggleCta();
		vrcToggleCtd();
	} else {
		document.getElementById("vrwdaytwodivid").style.display = "none";
	}
	vrComboArrWDay();
}
function vrComboArrWDay() {
	var wdayone = document.adminForm.wday;
	var wdaytwo = document.adminForm.wdaytwo;
	if (wdayone.value != "" && wdaytwo.value != "" && wdayone.value != wdaytwo.value) {
		var comboa = wdayone.options[wdayone.selectedIndex].text;
		var combob = wdaytwo.options[wdaytwo.selectedIndex].text;
		document.getElementById("vrccomboa1").innerHTML = comboa;
		document.getElementById("vrccomboa2").innerHTML = combob;
		document.getElementById("vrccomboa").value = wdayone.value+"-"+wdaytwo.value;
		document.getElementById("vrccombob1").innerHTML = combob;
		document.getElementById("vrccombob2").innerHTML = comboa;
		document.getElementById("vrccombob").value = wdaytwo.value+"-"+wdayone.value;
		document.getElementById("vrccomboc1").innerHTML = comboa;
		document.getElementById("vrccomboc2").innerHTML = comboa;
		document.getElementById("vrccomboc").value = wdayone.value+"-"+wdayone.value;
		document.getElementById("vrccombod1").innerHTML = combob;
		document.getElementById("vrccombod2").innerHTML = combob;
		document.getElementById("vrccombod").value = wdaytwo.value+"-"+wdaytwo.value;
		document.getElementById("vrwdaycombodivid").style.display = "block";
	} else {
		document.getElementById("vrwdaycombodivid").style.display = "none";
	}
}
function vrcToggleCars() {
	if (document.adminForm.allcars.checked == true) {
		document.getElementById("vrcrestrcarsdiv").style.display = "none";
	} else {
		document.getElementById("vrcrestrcarsdiv").style.display = "flex";
	}
}
function vrcToggleCta() {
	if (document.adminForm.cta.checked != true) {
		document.getElementById("vrcrestrctadiv").style.display = "none";
	} else {
		document.getElementById("vrcrestrctadiv").style.display = "flex";
		document.adminForm.wday.value = "";
		document.adminForm.wdaytwo.value = "";
		vrcSecondArrWDay();

	}
}
function vrcToggleCtd() {
	if (document.adminForm.ctd.checked != true) {
		document.getElementById("vrcrestrctddiv").style.display = "none";
	} else {
		document.getElementById("vrcrestrctddiv").style.display = "flex";
		document.adminForm.wday.value = "";
		document.adminForm.wdaytwo.value = "";
		vrcSecondArrWDay();
	}
}
function vrcToggleDuration(val) {
	if (val == 'datesrange') {
		document.getElementById("restr-duration-month").style.display = "none";
		document.getElementById("restr-duration-datesrange").style.display = "flex";
	} else {
		document.getElementById("restr-duration-datesrange").style.display = "none";
		document.getElementById("restr-duration-month").style.display = "flex";
	}
}
var restr_end_date = null;
function vrcToggleRepeatRestr() {
	var restrdur = jQuery('#restr-duration').val();
	var dfrom = jQuery('#dfrom').val();
	var dto = jQuery('#dto').val();
	if (<?php echo count($data) ? 'true || ' : ''; ?>restrdur != 'datesrange' || !dfrom.length || !dto.length) {
		// repeating restrictions is only allowed when creating a new one
		vrcCancelRepeatRestr();
		return;
	}
	var dfrom_parts = dfrom.split('/');
	var dto_parts = dto.split('/');
	if ('<?php echo $cdf; ?>' == 'd/m/Y') {
		var fromd = new Date(dfrom_parts[2], (dfrom_parts[1] - 1), dfrom_parts[0], 0, 0, 0, 0);
		var tod = new Date(dto_parts[2], (dto_parts[1] - 1), dto_parts[0], 0, 0, 0, 0);
	} else if ('<?php echo $cdf; ?>' == 'm/d/Y') {
		var fromd = new Date(dfrom_parts[2], (dfrom_parts[0] - 1), dfrom_parts[1], 0, 0, 0, 0);
		var tod = new Date(dto_parts[2], (dto_parts[0] - 1), dto_parts[1], 0, 0, 0, 0);
	} else {
		var fromd = new Date(dfrom_parts[0], (dfrom_parts[1] - 1), dfrom_parts[2], 0, 0, 0, 0);
		var tod = new Date(dto_parts[0], (dto_parts[1] - 1), dto_parts[2], 0, 0, 0, 0);
	}
	if (!fromd || !tod) {
		vrcCancelRepeatRestr();
		return;
	}
	restr_end_date = tod;
	var utc1 = Date.UTC(fromd.getFullYear(), fromd.getMonth(), fromd.getDate());
	var utc2 = Date.UTC(tod.getFullYear(), tod.getMonth(), tod.getDate());
	var totdays = (Math.ceil((utc2 - utc1) / (1000 * 60 * 60 * 24))) + 1;
	if (totdays < 1 || totdays >= 7) {
		vrcCancelRepeatRestr();
		return;
	}
	var wdays_tn = ["<?php echo addslashes(JText::_('VRCSUNDAY')); ?>", "<?php echo addslashes(JText::_('VRCMONDAY')); ?>", "<?php echo addslashes(JText::_('VRCTUESDAY')); ?>", "<?php echo addslashes(JText::_('VRCWEDNESDAY')); ?>", "<?php echo addslashes(JText::_('VRCTHURSDAY')); ?>", "<?php echo addslashes(JText::_('VRCFRIDAY')); ?>", "<?php echo addslashes(JText::_('VRCSATURDAY')); ?>"];
	var wdays_pool = new Array;
	while (fromd <= tod) {
		wdays_pool.push(fromd.getDay());
		// go to next day
		fromd.setDate(fromd.getDate() + 1);
	}
	var wdays_str = new Array;
	for (var i in wdays_pool) {
		if (!wdays_pool.hasOwnProperty(i)) {
			continue;
		}
		wdays_str.push(wdays_tn[wdays_pool[i]]);
	}
	var repeat_str = "<?php echo addslashes(JText::_('VRCRESTRREPEATONWDAYS')); ?>";
	jQuery('#restr-repeat-wdays').text(repeat_str.replace("%s", wdays_str.join(', ')));
	jQuery('#restr-repeat').fadeIn();
}
function vrcCancelRepeatRestr() {
	jQuery('input[name="repeat"]').attr('checked', false);
	jQuery('#restr-repeat, #restr-repeat-until').hide();
}
function vrcToggleRepeatUntil() {
	if (jQuery('input[name="repeat"]').is(':checked')) {
		// by default, set the "repeat until date" to end of year to-date
		if (restr_end_date != null) {
			var end_day = 31;
			var end_mon = 12;
			var end_year = restr_end_date.getFullYear();
			if ('<?php echo $cdf; ?>' == 'd/m/Y') {
				var endyear = end_day + '/' + end_mon + '/' + end_year;
			} else if ('<?php echo $cdf; ?>' == 'm/d/Y') {
				var endyear = end_mon + '/' + end_day + '/' + end_year;
			} else {
				var endyear = end_year + '/' + end_mon + '/' + end_day;
			}
			jQuery('#repeatuntil').val(endyear).attr('data-alt-value', endyear);
		}
		//
		jQuery('#restr-repeat-until').fadeIn();
	} else {
		jQuery('#restr-repeat-until').fadeOut();
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
							<div class="vrc-param-label"><?php echo $vrc_app->createPopover(array('title' => JText::_('VRRESTRICTIONSHELPTITLE'), 'content' => JText::_('VRRESTRICTIONSSHELP'))); ?><?php echo JText::_('VRNEWRESTRICTIONNAME'); ?>*</div>
							<div class="vrc-param-setting"><input type="text" name="name" value="<?php echo count($data) ? htmlspecialchars($data['name']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONDATERANGE') . '/' . JText::_('VRNEWRESTRICTIONONE'); ?>*</div>
							<div class="vrc-param-setting">
								<select id="restr-duration" onchange="vrcToggleDuration(this.value);">
									<option value="datesrange"<?php echo ((count($data) && !empty($data['dfrom'])) || !count($data) ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRNEWRESTRICTIONDATERANGE'); ?></option>
									<option value="month"<?php echo count($data) && (int)$data['month'] > 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRNEWRESTRICTIONONE'); ?></option>
								</select>
							</div>
						</div>
						<div class="vrc-param-container vrc-param-nested" id="restr-duration-month" style="display: <?php echo count($data) && (int)$data['month'] > 0 ? 'flex' : 'none'; ?>;">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONONE'); ?>*</div>
							<div class="vrc-param-setting">
								<select name="month">
									<option value="0">----</option>
									<option value="1"<?php echo (count($data) && $data['month'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHONE'); ?></option>
									<option value="2"<?php echo (count($data) && $data['month'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHTWO'); ?></option>
									<option value="3"<?php echo (count($data) && $data['month'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHTHREE'); ?></option>
									<option value="4"<?php echo (count($data) && $data['month'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHFOUR'); ?></option>
									<option value="5"<?php echo (count($data) && $data['month'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHFIVE'); ?></option>
									<option value="6"<?php echo (count($data) && $data['month'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHSIX'); ?></option>
									<option value="7"<?php echo (count($data) && $data['month'] == 7 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHSEVEN'); ?></option>
									<option value="8"<?php echo (count($data) && $data['month'] == 8 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHEIGHT'); ?></option>
									<option value="9"<?php echo (count($data) && $data['month'] == 9 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHNINE'); ?></option>
									<option value="10"<?php echo (count($data) && $data['month'] == 10 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHTEN'); ?></option>
									<option value="11"<?php echo (count($data) && $data['month'] == 11 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHELEVEN'); ?></option>
									<option value="12"<?php echo (count($data) && $data['month'] == 12 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRMONTHTWELVE'); ?></option>
								</select>
							</div>
						</div>
						<div class="vrc-param-container vrc-param-nested" id="restr-duration-datesrange" style="display: <?php echo ((count($data) && !empty($data['dfrom'])) || !count($data) ? 'flex' : 'none'); ?>;">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONDATERANGE'); ?>*</div>
							<div class="vrc-param-setting">
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vrcrestrdrangesp">'.JText::_('VRNEWRESTRICTIONDFROMRANGE').'</span>'.$vrc_app->getCalendar($dfromval, 'dfrom', 'dfrom', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vrcrestrdrangesp">'.JText::_('VRNEWRESTRICTIONDTORANGE').'</span>'.$vrc_app->getCalendar($dtoval, 'dto', 'dto', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONALLCARS'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('allcars', JText::_('VRYES'), JText::_('VRNO'), ((count($data) && $data['allcars'] == 1) || !count($data) ? 1 : 0), 1, 0, 'vrcToggleCars();'); ?>
							</div>
						</div>
						<div class="vrc-param-container vrc-param-nested" id="vrcrestrcarsdiv" style="display: <?php echo ((count($data) && $data['allcars'] == 1) || !count($data) ? 'none' : 'flex'); ?>;">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONCARSAFF'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $carsel; ?>
							</div>
						</div>
						<div class="vrc-param-container" id="restr-repeat" style="display: none;">
							<div class="vrc-param-label" id="restr-repeat-wdays"></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('repeat', JText::_('VRYES'), JText::_('VRNO'), 0, 1, 0, 'vrcToggleRepeatUntil();'); ?>
							</div>
						</div>
						<div class="vrc-param-container vrc-param-nested" id="restr-repeat-until" style="display: none;">
							<div class="vrc-param-label"><?php echo JText::_('VRCRESTRREPEATUNTIL'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->getCalendar('', 'repeatuntil', 'repeatuntil', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
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
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONMINLOS'); ?>*</div>
							<div class="vrc-param-setting"><input type="number" name="minlos" id="minlosinp" value="<?php echo count($data) ? (int)$data['minlos'] : '1'; ?>" min="1" size="3" /></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo $vrc_app->createPopover(array('title' => JText::_('VRNEWRESTRICTIONMULTIPLYMINLOS'), 'content' => JText::_('VRNEWRESTRICTIONMULTIPLYMINLOSHELP'))); ?><?php echo JText::_('VRNEWRESTRICTIONMULTIPLYMINLOS'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('multiplyminlos', JText::_('VRYES'), JText::_('VRNO'), (count($data) && $data['multiplyminlos'] == 1 ? 1 : 0), 1, 0); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONMAXLOS'); ?></div>
							<div class="vrc-param-setting"><input type="number" name="maxlos" id="maxlosinp" value="<?php echo count($data) ? (int)$data['maxlos'] : '0'; ?>" min="0" size="3" /></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONSETCTA'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('cta', JText::_('VRYES'), JText::_('VRNO'), (count($cur_setcta) > 0 ? 1 : 0), 1, 0, 'vrcToggleCta();'); ?>
							</div>
						</div>
						<div class="vrc-param-container vrc-param-nested" id="vrcrestrctadiv" style="display: <?php echo count($cur_setcta) > 0 ? 'flex' : 'none'; ?>;">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONWDAYSCTA'); ?></div>
							<div class="vrc-param-setting"><?php echo $ctasel; ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONSETCTD'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('ctd', JText::_('VRYES'), JText::_('VRNO'), (count($cur_setctd) > 0 ? 1 : 0), 1, 0, 'vrcToggleCtd();'); ?>
							</div>
						</div>
						<div class="vrc-param-container vrc-param-nested" id="vrcrestrctddiv" style="display: <?php echo count($cur_setctd) > 0 ? 'flex' : 'none'; ?>;">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONWDAYSCTD'); ?></div>
							<div class="vrc-param-setting"><?php echo $ctdsel; ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWRESTRICTIONWDAY'); ?></div>
							<div class="vrc-param-setting">
								<select name="wday" onchange="vrcSecondArrWDay();">
									<option value=""></option>
									<option value="0"<?php echo (count($data) && strlen($data['wday']) > 0 && $data['wday'] == 0 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCSUNDAY'); ?></option>
									<option value="1"<?php echo (count($data) && $data['wday'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCMONDAY'); ?></option>
									<option value="2"<?php echo (count($data) && $data['wday'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCTUESDAY'); ?></option>
									<option value="3"<?php echo (count($data) && $data['wday'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCWEDNESDAY'); ?></option>
									<option value="4"<?php echo (count($data) && $data['wday'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCTHURSDAY'); ?></option>
									<option value="5"<?php echo (count($data) && $data['wday'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCFRIDAY'); ?></option>
									<option value="6"<?php echo (count($data) && $data['wday'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCSATURDAY'); ?></option>
								</select>
								<div class="vrwdaytwodiv" id="vrwdaytwodivid" style="display: <?php echo (count($data) && strlen($data['wday']) > 0 ? 'inline-block' : 'none'); ?>;">
									<span><?php echo JText::_('VRNEWRESTRICTIONOR'); ?></span> 
									<select name="wdaytwo" onchange="vrComboArrWDay();">
										<option value=""></option>
										<option value="0"<?php echo (count($data) && strlen($data['wdaytwo']) > 0 && $data['wdaytwo'] == 0 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCSUNDAY'); ?></option>
										<option value="1"<?php echo (count($data) && $data['wdaytwo'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCMONDAY'); ?></option>
										<option value="2"<?php echo (count($data) && $data['wdaytwo'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCTUESDAY'); ?></option>
										<option value="3"<?php echo (count($data) && $data['wdaytwo'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCWEDNESDAY'); ?></option>
										<option value="4"<?php echo (count($data) && $data['wdaytwo'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCTHURSDAY'); ?></option>
										<option value="5"<?php echo (count($data) && $data['wdaytwo'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCFRIDAY'); ?></option>
										<option value="6"<?php echo (count($data) && $data['wdaytwo'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCSATURDAY'); ?></option>
									</select>
								</div>
								<div class="vrwdaycombodiv" id="vrwdaycombodivid" style="display: <?php echo (count($data) && !empty($data['wdaycombo']) && strlen($data['wdaycombo']) > 3 ? 'block' : 'none'); ?>;">
									<span class="vrwdaycombosp"><?php echo JText::_('VRNEWRESTRICTIONALLCOMBO'); ?></span><span class="vrwdaycombohelp"><?php echo JText::_('VRNEWRESTRICTIONALLCOMBOHELP'); ?></span>
									<p class="vrwdaycombop">
										<label for="vrccomboa" style="display: inline-block; vertical-align: top;">
											<span id="vrccomboa1"><?php echo strlen($vrcra1) ? $arrwdays[intval($vrcra1)] : ''; ?></span> - <span id="vrccomboa2"><?php echo strlen($vrcra2) ? $arrwdays[intval($vrcra2)] : ''; ?></span>
										</label> 
										<input type="checkbox" name="comboa" id="vrccomboa" value="<?php echo strlen($vrcra1) ? JHtml::_('esc_attr', $vrcra1.'-'.$vrcra2) : ''; ?>"<?php echo (strlen($vrcra1) && $vrccomboparts[0] == $vrcra1.'-'.$vrcra2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/>
									</p>
									<p class="vrwdaycombop">
										<label for="vrccombob" style="display: inline-block; vertical-align: top;">
											<span id="vrccombob1"><?php echo strlen($vrcrb1) ? $arrwdays[intval($vrcrb1)] : ''; ?></span> - <span id="vrccombob2"><?php echo strlen($vrcrb2) ? $arrwdays[intval($vrcrb2)] : ''; ?></span>
										</label> 
										<input type="checkbox" name="combob" id="vrccombob" value="<?php echo strlen($vrcrb1) ? JHtml::_('esc_attr', $vrcrb1.'-'.$vrcrb2) : ''; ?>"<?php echo (strlen($vrcrb1) && $vrccomboparts[1] == $vrcrb1.'-'.$vrcrb2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/>
									</p>
									<p class="vrwdaycombop">
										<label for="vrccomboc" style="display: inline-block; vertical-align: top;">
											<span id="vrccomboc1"><?php echo strlen($vrcrc1) ? $arrwdays[intval($vrcrc1)] : ''; ?></span> - <span id="vrccomboc2"><?php echo strlen($vrcrc2) ? $arrwdays[intval($vrcrc2)] : ''; ?></span>
										</label> 
										<input type="checkbox" name="comboc" id="vrccomboc" value="<?php echo strlen($vrcrc1) ? JHtml::_('esc_attr', $vrcrc1.'-'.$vrcrc2) : ''; ?>"<?php echo (strlen($vrcrc1) && $vrccomboparts[2] == $vrcrc1.'-'.$vrcrc2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/>
									</p>
									<p class="vrwdaycombop">
										<label for="vrccombod" style="display: inline-block; vertical-align: top;">
											<span id="vrccombod1"><?php echo strlen($vrcrd1) ? $arrwdays[intval($vrcrd1)] : ''; ?></span> - <span id="vrccombod2"><?php echo strlen($vrcrd2) ? $arrwdays[intval($vrcrd2)] : ''; ?></span>
										</label> 
										<input type="checkbox" name="combod" id="vrccombod" value="<?php echo strlen($vrcrd1) ? JHtml::_('esc_attr', $vrcrd1.'-'.$vrcrd2) : ''; ?>"<?php echo (strlen($vrcrd1) && $vrccomboparts[3] == $vrcrd1.'-'.$vrcrd2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
<?php
if (count($data)) :
?>
	<input type="hidden" name="where" value="<?php echo (int)$data['id']; ?>">
<?php
endif;
?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar">
	<?php echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#dfrom').val('<?php echo $dfromval; ?>').attr('data-alt-value', '<?php echo $dfromval; ?>');
	jQuery('#dto').val('<?php echo $dtoval; ?>').attr('data-alt-value', '<?php echo $dtoval; ?>');
	jQuery('#restr-idcars, #restr-ctad, #restr-ctdd').select2();
	jQuery('#dfrom, #dto, #minlosinp, #maxlosinp').change(function() {
		vrcToggleRepeatRestr();
	});
	jQuery('#dfrom, #dto').blur(function() {
		vrcToggleRepeatRestr();
	});
});
<?php
if (count($data) && strlen($data['wday']) > 0 && strlen($data['wdaytwo']) > 0) {
	?>
vrComboArrWDay();
	<?php
}
?>
</script>
