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

$car = $this->car;
$msg = $this->msg;
$allc = $this->allc;
$payments = $this->payments;
$busy = $this->busy;
$vmode = $this->vmode;
$pickuparr = $this->pickuparr;
$dropoffarr = $this->dropoffarr;

$dbo = JFactory::getDbo();
$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadSelect2();
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery-ui.min.css');
JHtml::_('jquery.framework', true, true);
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.min.js');
$vrc_df = VikRentCar::getDateFormat(true);
if ($vrc_df == "%d/%m/%Y") {
	$df = 'd/m/Y';
	$juidf = 'dd/mm/yy';
} elseif ($vrc_df == "%m/%d/%Y") {
	$df = 'm/d/Y';
	$juidf = 'mm/dd/yy';
} else {
	$df = 'Y/m/d';
	$juidf = 'yy/mm/dd';
}
$ppickup = VikRequest::getString('pickup', '', 'request');
if (!empty($ppickup)) {
	$ppickup = date(str_replace('%', '', $vrc_df), strtotime($ppickup));
}
$pdropoff = VikRequest::getString('dropoff', '', 'request');
if (!empty($pdropoff)) {
	$pdropoff = date(str_replace('%', '', $vrc_df), strtotime($pdropoff));
}
$ptmpl = VikRequest::getString('tmpl', '', 'request');
$poverview = VikRequest::getInt('overv', '', 'request');
$poverview_change = VikRequest::getInt('overview_change', '', 'request');
$pidprice = VikRequest::getInt('idprice', 0, 'request');
$pbooknow = VikRequest::getInt('booknow', 0, 'request');
$ldecl = '
jQuery(function($){'."\n".'
	$.datepicker.regional["vikrentcar"] = {'."\n".'
		closeText: "'.JText::_('VRCJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VRCJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VRCJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VRCJQCALTODAY').'",'."\n".'
		monthNames: ["'.JText::_('VRMONTHONE').'","'.JText::_('VRMONTHTWO').'","'.JText::_('VRMONTHTHREE').'","'.JText::_('VRMONTHFOUR').'","'.JText::_('VRMONTHFIVE').'","'.JText::_('VRMONTHSIX').'","'.JText::_('VRMONTHSEVEN').'","'.JText::_('VRMONTHEIGHT').'","'.JText::_('VRMONTHNINE').'","'.JText::_('VRMONTHTEN').'","'.JText::_('VRMONTHELEVEN').'","'.JText::_('VRMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(JText::_('VRMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.JText::_('VRCSUNDAY').'", "'.JText::_('VRCMONDAY').'", "'.JText::_('VRCTUESDAY').'", "'.JText::_('VRCWEDNESDAY').'", "'.JText::_('VRCTHURSDAY').'", "'.JText::_('VRCFRIDAY').'", "'.JText::_('VRCSATURDAY').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(JText::_('VRCSUNDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCMONDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCTUESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCWEDNESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCTHURSDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCFRIDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRCSATURDAY'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(JText::_('VRCSUNDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCMONDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCTUESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCWEDNESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCTHURSDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCFRIDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRCSATURDAY'), 0, 2, 'UTF-8').'"],'."\n".'
		weekHeader: "'.JText::_('VRCJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikRentCar::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikrentcar"]);'."\n".'
});';
$document->addScriptDeclaration($ldecl);

if (strlen($msg) > 0 && intval($msg) > 0) {
	?>
<p class="successmade"><?php echo JText::_('VRBOOKMADE'); ?> &nbsp;&nbsp;&nbsp; <a href="index.php?option=com_vikrentcar&task=editorder&cid[]=<?php echo intval($msg); ?>" class="btn"><?php VikRentCarIcons::e('eye'); ?> <?php echo JText::_('VRCVIEWBOOKINGDET'); ?></a></p>
	<?php
} elseif (strlen($msg) > 0 && $msg == "0") {
	?>
<p class="err" style="margin-top: -5px;"><?php echo JText::_('VRBOOKNOTMADE'); ?></p>
	<?php
}

$timeopst = VikRentCar::getTimeOpenStore();
if (is_array($timeopst) && $timeopst[0]!=$timeopst[1]) {
	$opent = VikRentCar::getHoursMinutes($timeopst[0]);
	$closet = VikRentCar::getHoursMinutes($timeopst[1]);
	$i = $opent[0];
	$j = $closet[0];
} else {
	$i = 0;
	$j = 23;
}
$hours = $minutes = '';
while ($i <= $j) {
	if ($i < 10) {
		$i = "0".$i;
	} else {
		$i = $i;
	}
	$hours .= "<option value=\"".$i."\">".$i."</option>\n";
	$i++;
}
for ($i = 0; $i < 60; $i++) {
	if ($i < 10) {
		$i = "0".$i;
	} else {
		$i = $i;
	}
	$minutes .= "<option value=\"".$i."\">".$i."</option>\n";
}

$formatparts = explode(':', VikRentCar::getNumberFormatData());
$currencysymb = VikRentCar::getCurrencySymb(true);
$selpayments = '<select name="payment"><option value="">'.JText::_('VRCQUICKRESNONE').'</option>';
if (is_array($payments) && @count($payments) > 0) {
	foreach ($payments as $pay) {
		$selpayments .= '<option value="'.$pay['id'].'">'.$pay['name'].'</option>';
	}
}
$selpayments .= '</select>';

// custom fields
$all_cfields = array();
$all_countries = array();
$q = "SELECT * FROM `#__vikrentcar_custfields` ORDER BY `#__vikrentcar_custfields`.`ordering` ASC;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$all_cfields = $dbo->loadAssocList();
	$q = "SELECT * FROM `#__vikrentcar_countries` ORDER BY `#__vikrentcar_countries`.`country_name` ASC;";
	$dbo->setQuery($q);
	$dbo->execute();
	$all_countries = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
}

// taxes
$wiva = "";
$q = "SELECT * FROM `#__vikrentcar_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	foreach ($ivas as $kiv => $iv) {
		$wiva .= "<option value=\"".$iv['id']."\" data-aliqid=\"".$iv['id']."\"".($kiv < 1 ? ' selected="selected"' : '').">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']." - ".$iv['aliq']."%")."</option>\n";
	}
}

// places
$pickopts = '';
$dropopts = '';
if (count($pickuparr) && count($dropoffarr)) {
	foreach ($pickuparr as $locv) {
		$pickopts .= '<option value="'.$locv['id'].'">'.$locv['name'].'</option>'."\n";
	}
	foreach ($dropoffarr as $locv) {
		$dropopts .= '<option value="'.$locv['id'].'">'.$locv['name'].'</option>'."\n";
	}
}
?>

<div class="vrc-admin-container">
	
	<div class="vrc-config-maintab-left">

		<fieldset class="adminform">
			<div class="vrc-params-wrap">
				<legend class="adminlegend">
					<div class="vrc-quickres-head">
						<span><?php echo $car['name'] . " - " . JText::_('VRQUICKBOOK'); ?></span>
						<div class="vrc-quickres-head-right">
							<form name="vrchcar" id="vrchcar" method="post" action="index.php?option=com_vikrentcar">
								<input type="hidden" name="task" value="calendar"/>
								<input type="hidden" name="option" value="com_vikrentcar"/>
								<select id="vrc-calendar-changecar" name="cid[]" onchange="jQuery('#vrchcar').submit();">
								<?php
								foreach ($allc as $cc) {
									echo "<option value=\"".$cc['id']."\"".($cc['id'] == $car['id'] ? " selected=\"selected\"" : "").">".$cc['name']."</option>\n";
								}
								?>
								</select>
							<?php
							if ($ptmpl == 'component') {
								echo "<input type=\"hidden\" name=\"tmpl\" value=\"component\" />\n";
							}
							?>
							</form>
						</div>
					</div>
				</legend>
				<form name="newb" method="post" action="index.php?option=com_vikrentcar" onsubmit="javascript: if (!document.newb.pickupdate.value.match(/\S/)){alert('<?php echo addslashes(JText::_('VRMSGTHREE')); ?>'); return false;} if (!document.newb.releasedate.value.match(/\S/)){alert('<?php echo addslashes(JText::_('VRMSGFOUR')); ?>'); return false;} return true;">
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRDATEPICKUP'); ?></div>
							<div class="vrc-param-setting">
								<div class="input-append">
									<input type="text" autocomplete="off" name="pickupdate" id="pickupdate" size="10" />
									<button type="button" class="btn vrcdatepicker-trig-icon"><span class="icon-calendar"></span></button>
								</div>
								<span class="vrc-calendar-time-inline"><?php echo JText::_('VRAT'); ?></span>
								<select name="pickuph"><?php echo $hours; ?></select> : <select name="pickupm"><?php echo $minutes; ?></select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRDATERELEASE'); ?></div>
							<div class="vrc-param-setting">
								<div class="input-append">
									<input type="text" autocomplete="off" name="releasedate" id="releasedate" size="10" />
									<button type="button" class="btn vrcdatepicker-trig-icon"><span class="icon-calendar"></span></button>
								</div>
								<span class="vrc-calendar-time-inline"><?php echo JText::_('VRAT'); ?></span>
								<select name="releaseh"><?php echo $hours; ?></select> : <select name="releasem"><?php echo $minutes; ?></select>
								<span style="display: inline-block; margin-left: 25px; font-weight: bold;" id="vrjstotnights"></span>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label">
								<span class="vrclosecarsp">
									<label for="setclosed-on"><?php echo JText::_('VRCSTOPRENTALS'); ?> <i class="<?php echo VikRentCarIcons::i('ban'); ?>" style="float: none;"></i></label>
								</span>
							</div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('setclosed', JText::_('VRYES'), JText::_('VRNO'), 0, 1, 0, 'vrcCloseCar();'); ?>
							</div>
						</div>
					<?php
					if (count($pickuparr) && count($dropoffarr)) {
						?>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCQUICKRESLOCATIONS'); ?></div>
							<div class="vrc-param-setting">
								<span class="vrc-quickres-selwrap">
									<select name="pickuploc" id="pickuploc">
										<option></option>
										<?php echo $pickopts; ?>
									</select>
								</span>
								<span class="vrc-quickres-selwrap">
									<select name="dropoffloc" id="dropoffloc">
										<option></option>
										<?php echo $dropopts; ?>
									</select>
								</span>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vrc-param-container" id="vrspanbstat">
							<div class="vrc-param-label"><?php echo JText::_('VRCQUICKRESORDSTATUS'); ?></div>
							<div class="vrc-param-setting">
								<select name="newstatus">
									<option value="confirmed"><?php echo JText::_('VRCONFIRMED'); ?></option>
									<option value="standby"><?php echo JText::_('VRSTANDBY'); ?></option>
								</select>
							</div>
						</div>
						<div class="vrc-param-container" id="vrspanbpay">
							<div class="vrc-param-label"><?php echo JText::_('VRCQUICKRESMETHODOFPAYMENT'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $selpayments; ?>
							</div>
						</div>
						<div class="vrc-param-container" id="vrfillcustfields">
							<div class="vrc-param-label">&nbsp;</div>
							<div class="vrc-param-setting">
								<span class="vrc-assign-customer">
									<i class="<?php echo VikRentCarIcons::i('user-circle'); ?>"></i>
									<span><?php echo JText::_('VRFILLCUSTFIELDS'); ?></span>
								</span>
							</div>
						</div>
						<div class="vrc-param-container" id="vrspancmail">
							<div class="vrc-param-label"><?php echo JText::_('VRQRCUSTMAIL'); ?></div>
							<div class="vrc-param-setting">
								<input type="text" name="custmail" id="custmailfield" value="" size="25"/>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTINFO'); ?></div>
							<div class="vrc-param-setting">
								<textarea name="custdata" id="vrcustdatatxtarea" rows="5" cols="70" style="min-width: 300px;"></textarea>
							</div>
						</div>
						<div class="vrc-param-container" id="vrc-website-rates-row" style="display: none;">
							<div class="vrc-param-label"><?php echo JText::_('VRCWEBSITERATES'); ?></div>
							<div class="vrc-param-setting" id="vrc-website-rates-cont"></div>
						</div>
						<div class="vrc-param-container" id="vrspcustcost">
							<div class="vrc-param-label"><?php echo JText::_('VRCRENTCUSTRATEPLANADD'); ?></div>
							<div class="vrc-param-setting">
								<span>
									<?php echo $currencysymb; ?> <input name="cust_cost" id="cust_cost" value="" onfocus="document.getElementById('taxid').style.display = 'inline-block';" onkeyup="vrCalcDailyCost(this.value);" onchange="vrCalcDailyCost(this.value);" type="number" step="any" min="0" style="min-width: 75px; margin: 0 5px 0 0;">
									<select name="taxid" id="taxid" style="display: none; margin: 0; max-width: 150px;">
										<option value=""><?php echo JText::_('VRNEWOPTFOUR'); ?></option>
										<?php echo $wiva; ?>
									</select>
									<span id="avg-daycost" style="display: inline-block; margin-left: 15px;"></span>
								</span>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label">&nbsp;</div>
							<div class="vrc-param-setting">
								<button type="submit" id="quickbsubmit" class="btn btn-success btn-large"><?php VikRentCarIcons::e('save'); ?> <span><?php echo JText::_('VRMAKERESERV'); ?></span></button>
							</div>
						</div>
					</div>
					<?php
					if ($ptmpl == 'component') {
						?>
						<input type="hidden" name="tmpl" value="component" />
						<?php
					}
					?>
					<input type="hidden" name="customer_id" value="" id="customer_id_inpfield"/>
					<input type="hidden" name="countrycode" value="" id="ccode_inpfield"/>
					<input type="hidden" name="t_first_name" value="" id="t_first_name_inpfield"/>
					<input type="hidden" name="t_last_name" value="" id="t_last_name_inpfield"/>
					<input type="hidden" name="phone" value="" id="phonefield"/>
					<input type="hidden" name="idprice" value="" id="booking-idprice"/>
					<input type="hidden" name="carcost" value="" id="booking-carcost"/>
					<input type="hidden" name="task" value="calendar"/>
					<input type="hidden" name="cid[]" value="<?php echo (int)$car['id']; ?>"/>
					<input type="hidden" name="option" value="com_vikrentcar" />
				</form>
			</div>
		</fieldset>

	</div>
	<div class="vrc-config-maintab-right">
		<div class="vrc-avcalendars-wrapper">
			<div class="vrc-avcalendars-carphoto">
			<?php
			if (is_file(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $car['img'])) {
				?>
				<img alt="Car Image" src="<?php echo VRC_ADMIN_URI; ?>resources/<?php echo $car['img']; ?>" />
				<?php
			} else {
				VikRentCarIcons::e('image', 'vrc-enormous-icn');
			}
			?>
			</div>
		<?php
		$check = false;
		$nowtf = VikRentCar::getTimeFormat(true);
		if (empty($busy)) {
			echo "<p class=\"warn\">".JText::_('VRNOFUTURERES')."</p>";
		} else {
			$check = true;
			$icalurl = JURI::root().'index.php?option=com_vikrentcar&task=ical&car='.$car['id'].'&key='.VikRentCar::getIcalSecretKey();
			?>
			<p>
				<a class="vrmodelink<?php echo $vmode == 3 ? ' vrmodelink-active' : ''; ?>" href="index.php?option=com_vikrentcar&amp;task=calendar&amp;cid[]=<?php echo $car['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=3"><?php VikRentCarIcons::e('calendar'); ?> <span><?php echo JText::_('VRTHREEMONTHS'); ?></span></a>
				<a class="vrmodelink<?php echo $vmode == 6 ? ' vrmodelink-active' : ''; ?>" href="index.php?option=com_vikrentcar&amp;task=calendar&amp;cid[]=<?php echo $car['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=6"><?php VikRentCarIcons::e('calendar'); ?> <span><?php echo JText::_('VRSIXMONTHS'); ?></span></a>
				<a class="vrmodelink<?php echo $vmode == 12 ? ' vrmodelink-active' : ''; ?>" href="index.php?option=com_vikrentcar&amp;task=calendar&amp;cid[]=<?php echo $car['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=12"><?php VikRentCarIcons::e('calendar'); ?> <span><?php echo JText::_('VRTWELVEMONTHS'); ?></span></a>
				<a class="vrmodelink" href="javascript: void(0);" onclick="jQuery('#icalsynclinkinp').attr('size', (jQuery('#icalsynclinkinp').val().length + 5)).fadeToggle().focus();"><?php VikRentCarIcons::e('link'); ?> <span><?php echo JText::_('VRCICALLINK'); ?></span></a>
				<input id="icalsynclinkinp" style="display: none;" type="text" value="<?php echo JHtml::_('esc_attr', $icalurl); ?>" readonly="readonly" size="40" onfocus="jQuery('#icalsynclinkinp').select();"/>
			</p>
			<?php
		}
		?>
			<div class="vrc-calendar-cals-container">
			<?php
			$arr = getdate();
			$mon = $arr['mon'];
			$realmon = ($mon < 10 ? "0".$mon : $mon);
			$year = $arr['year'];
			$day = $realmon."/01/".$year;
			$dayts = strtotime($day);
			$newarr = getdate($dayts);

			$firstwday = (int)VikRentCar::getFirstWeekDay(true);
			$days_labels = array(
					JText::_('VRSUN'),
					JText::_('VRMON'),
					JText::_('VRTUE'),
					JText::_('VRWED'),
					JText::_('VRTHU'),
					JText::_('VRFRI'),
					JText::_('VRSAT')
			);
			$days_indexes = array();
			for ($i = 0; $i < 7; $i++) {
				$days_indexes[$i] = (6-($firstwday-$i)+1)%7;
			}

			for ($jj = 1; $jj <= $vmode; $jj++) {
				$d_count = 0;
				echo '<div class="vrc-calendar-cal-container">';
				$cal = "";
				?>
				<table class="vrcadmincaltable">
					<tr class="vrcadmincaltrmon">
						<td colspan="7" align="center"><?php echo VikRentCar::sayMonth($newarr['mon'])." ".$newarr['year']; ?></td>
					</tr>
					<tr class="vrcadmincaltrmdays">
					<?php
					for ($i = 0; $i < 7; $i++) {
						$d_ind = ($i + $firstwday) < 7 ? ($i + $firstwday) : ($i + $firstwday - 7);
						?>
						<td><?php echo $days_labels[$d_ind]; ?></td>
						<?php
					}
					?>
					</tr>
					<tr>
					<?php
					for ($i = 0, $n = $days_indexes[$newarr['wday']]; $i < $n; $i++, $d_count++) {
						$cal .= "<td align=\"center\">&nbsp;</td>";
					}
					while ($newarr['mon'] == $mon) {
						if ($d_count > 6) {
							$d_count = 0;
							$cal .= "</tr>\n<tr>";
						}
						$dclass = "free";
						$dalt = "";
						$bid = "";
						$totfound = 0;
						if ($check) {
							foreach ($busy as $b) {
								$tmpone = getdate($b['ritiro']);
								$ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
								$tmptwo = getdate($b['consegna']);
								$conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
								if ($newarr[0] >= $ritts && $newarr[0] <= $conts) {
									$dclass = "busy";
									$bid = $b['idorder'];
									if ((int)$b['stop_sales'] > 0) {
										$dclass .= " busy-closure";
										$dalt = JText::_('VRDBTEXTROOMCLOSED');
									} elseif ($newarr[0] == $ritts) {
										$dalt = JText::_('VRPICKUPAT')." ".date($nowtf, $b['ritiro']);
									} elseif ($newarr[0] == $conts) {
										$dalt = JText::_('VRRELEASEAT')." ".date($nowtf, $b['consegna']);
									}
									$totfound++;
								}
							}
						}
						$useday = ($newarr['mday'] < 10 ? "0".$newarr['mday'] : $newarr['mday']);
						if ($totfound > 0 && $totfound < $car['units']) {
							$dclass .= " vrc-partially";
						}
						if ($totfound == 1) {
							$dlnk = "<a href=\"index.php?option=com_vikrentcar&task=editbusy&cid[]=".$bid."\"".($ptmpl == 'component' ? ' target="_blank"' : '').">".$useday."</a>";
							$cal .= "<td align=\"center\" data-daydate=\"".date($df, $newarr[0])."\" class=\"".$dclass."\"".(!empty($dalt) ? " title=\"".$dalt."\"" : "").">".$dlnk."</td>\n";
						} elseif ($totfound > 1) {
							$dlnk = "<a href=\"index.php?option=com_vikrentcar&task=choosebusy&idcar=".$car['id']."&ts=".$newarr[0]."\"".($ptmpl == 'component' ? ' target="_blank"' : '').">".$useday."</a>";
							$cal .= "<td align=\"center\" data-daydate=\"".date($df, $newarr[0])."\" class=\"".$dclass."\">".$dlnk."</td>\n";
						} else {
							$dlnk = $useday;
							$cal .= "<td align=\"center\" data-daydate=\"".date($df, $newarr[0])."\" class=\"".$dclass."\">".$dlnk."</td>\n";
						}
						$next = $newarr['mday'] + 1;
						$dayts = mktime(0, 0, 0, ($newarr['mon'] < 10 ? "0".$newarr['mon'] : $newarr['mon']), ($next < 10 ? "0".$next : $next), $newarr['year']);
						$newarr = getdate($dayts);
						$d_count++;
					}
					
					for ($i = $d_count; $i <= 6; $i++) {
						$cal .= "<td align=\"center\">&nbsp;</td>";
					}
			
					echo $cal;
					?>
					</tr>
				</table>
				<?php
				echo "</div>";
				if ($mon == 12) {
					$mon = 1;
					$year += 1;
					$dayts = mktime(0, 0, 0, ($mon < 10 ? "0".$mon : $mon), 01, $year);
				} else {
					$mon += 1;
					$dayts = mktime(0, 0, 0, ($mon < 10 ? "0".$mon : $mon), 01, $year);
				}
				$newarr = getdate($dayts);
			}
			?>
			</div>
		</div>
	</div>
</div>

<div class="vrc-calendar-cfields-filler-overlay">
	<a class="vrc-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-calendar-cfields-filler">
		<div class="vrc-calendar-cfields-topcont">
			<div class="vrc-calendar-cfields-custinfo">
				<h4><?php echo JText::_('VRCUSTINFO'); ?></h4>
			</div>
			<div class="vrc-calendar-cfields-search">
				<label for="vrc-searchcust"><?php echo JText::_('VRCSEARCHEXISTCUST'); ?></label>
				<span id="vrc-searchcust-loading">
					<i class="vrcicn-hour-glass"></i>
				</span>
				<input type="text" id="vrc-searchcust" autocomplete="off" value="" placeholder="<?php echo JText::_('VRCSEARCHCUSTBY'); ?>" size="35" />
				<div id="vrc-searchcust-res"></div>
			</div>
		</div>
		<div class="vrc-calendar-cfields-inner">
	<?php
	$phone_field_id = '';
	foreach ($all_cfields as $cfield) {
		if ($cfield['type'] == 'text' && $cfield['isphone'] == 1) {
			$phone_field_id = 'cfield' . $cfield['id'];
			?>
			<div class="vrc-calendar-cfield-entry">
				<label for="<?php echo $phone_field_id; ?>" data-fieldid="<?php echo $cfield['id']; ?>"><?php echo JText::_($cfield['name']); ?></label>
				<span>
					<?php echo $vrc_app->printPhoneInputField(array('id' => $phone_field_id, 'data-isemail' => '0', 'data-isnominative' => '0', 'data-isphone' => '1'), array('fullNumberOnBlur' => true)); ?>
				</span>
			</div>
			<?php
		} elseif ($cfield['type'] == 'text') {
			?>
			<div class="vrc-calendar-cfield-entry">
				<label for="cfield<?php echo $cfield['id']; ?>" data-fieldid="<?php echo $cfield['id']; ?>"><?php echo JText::_($cfield['name']); ?></label>
				<span>
					<input type="text" id="cfield<?php echo $cfield['id']; ?>" data-isemail="<?php echo ($cfield['isemail'] == 1 ? '1' : '0'); ?>" data-isnominative="<?php echo ($cfield['isnominative'] == 1 ? '1' : '0'); ?>" data-isphone="0" value="" size="35"/>
				</span>
			</div>
			<?php
		} elseif ($cfield['type'] == 'textarea') {
			?>
			<div class="vrc-calendar-cfield-entry">
				<label for="cfield<?php echo $cfield['id']; ?>" data-fieldid="<?php echo $cfield['id']; ?>"><?php echo JText::_($cfield['name']); ?></label>
				<span>
					<textarea id="cfield<?php echo $cfield['id']; ?>" rows="4" cols="35"></textarea>
				</span>
			</div>
			<?php
		} elseif ($cfield['type'] == 'country') {
			?>
			<div class="vrc-calendar-cfield-entry">
				<label for="cfield<?php echo $cfield['id']; ?>" data-fieldid="<?php echo $cfield['id']; ?>"><?php echo JText::_($cfield['name']); ?></label>
				<span>
					<select id="cfield<?php echo $cfield['id']; ?>"<?php echo !empty($phone_field_id) ? ' onchange="jQuery(\'#' . $phone_field_id . '\').trigger(\'vrcupdatephonenumber\', jQuery(this).find(\'option:selected\').attr(\'data-c2code\'));"' : ''; ?>>
						<option value=""> </option>
					<?php
					foreach ($all_countries as $country) {
						?>
						<option value="<?php echo JHtml::_('esc_attr', $country['country_name']); ?>" data-ccode="<?php echo JHtml::_('esc_attr', $country['country_3_code']); ?>" data-c2code="<?php echo JHtml::_('esc_attr', $country['country_2_code']); ?>"><?php echo JHtml::_('esc_html', $country['country_name']); ?></option>
						<?php
					}
					?>
					</select>
				</span>
			</div>
			<?php
		}
	}
	?>
		</div>
		<div class="vrc-calendar-cfields-bottom">
			<button type="button" class="btn" onclick="hideCustomFields();"><?php echo JText::_('VRANNULLA'); ?></button>
			<button type="button" class="btn btn-success" onclick="applyCustomFieldsContent();"><i class="icon-edit"></i> <?php echo JText::_('VRAPPLY'); ?></button>
		</div>
	</div>
</div>

<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrentcar" />
</form>

<script type="text/javascript">
<?php echo ($poverview_change > 0 ? 'window.parent.hasNewBooking = true;' . "\n" : ''); ?>
var vrc_glob_sel_nights = 0;
var cfields_overlay = false;
var customers_search_vals = "";
var prev_tareat = null;
var booknowmade = false;

function vrcCloseCar() {
	var ckbox = document.getElementById("setclosed") ? document.getElementById("setclosed") : document.getElementById("setclosed-on");
	if (ckbox && ckbox.checked == true) {
		if (jQuery("#vrspannumcars").length) {
			jQuery("#vrspannumcars").hide();
		}
		jQuery("#vrspanbstat").hide();
		jQuery("#vrspcustcost").hide();
		jQuery("#vrspancmail").hide();
		jQuery("#vrfillcustfields").hide();
		jQuery("#vrspanbpay").hide();
		jQuery("#vrc-website-rates-row").hide();
		if (prev_tareat === null) {
			// save the previous customer information
			prev_tareat = jQuery('#vrcustdatatxtarea').val();
		}
		jQuery("#vrcustdatatxtarea").val("<?php echo addslashes(JText::_('VRDBTEXTROOMCLOSED')); ?>");
		jQuery("#quickbsubmit").removeClass("btn-success").addClass("btn-danger").find("span").text("<?php echo addslashes(JText::_('VRSUBMCLOSEROOM')); ?>");
	} else {
		if (jQuery("#vrspannumcars").length) {
			jQuery("#vrspannumcars").show();
		}
		jQuery("#vrspanbstat").show();
		jQuery("#vrspcustcost").show();
		jQuery("#vrspancmail").show();
		jQuery("#vrfillcustfields").show();
		jQuery("#vrspanbpay").show();
		jQuery("#vrcustdatatxtarea").val(prev_tareat + "");
		jQuery("#quickbsubmit").removeClass("btn-danger").addClass("btn-success").find("span").text("<?php echo addslashes(JText::_('VRMAKERESERV')); ?>");
	}
}

function showCustomFields() {
	cfields_overlay = true;
	jQuery(".vrc-calendar-cfields-filler-overlay, .vrc-calendar-cfields-filler").fadeIn();
	setTimeout(function() {
		jQuery('#vrc-searchcust').focus();
	}, 500);
}

function hideCustomFields() {
	cfields_overlay = false;
	jQuery(".vrc-calendar-cfields-filler-overlay").fadeOut();
}

function applyCustomFieldsContent() {
	var cfields_cont = "";
	var cfields_labels = new Array;
	var nominatives = new Array;
	var tot_rows = 1;
	jQuery(".vrc-calendar-cfields-inner .vrc-calendar-cfield-entry").each(function(){
		var cfield_name = jQuery(this).find("label").text();
		var cfield_input = jQuery(this).find("span").find("input");
		var cfield_textarea = jQuery(this).find("span").find("textarea");
		var cfield_select = jQuery(this).find("span").find("select");
		var cfield_cont = "";
		if (cfield_input.length) {
			cfield_cont = cfield_input.val();
			if (cfield_input.attr("data-isemail") == "1" && cfield_cont.length) {
				jQuery("#custmailfield").val(cfield_cont);
			}
			if (cfield_input.attr("data-isphone") == "1") {
				jQuery("#phonefield").val(cfield_cont);
			}
			if (cfield_input.attr("data-isnominative") == "1") {
				nominatives.push(cfield_cont);
			}
		} else if (cfield_textarea.length) {
			cfield_cont = cfield_textarea.val();
		} else if (cfield_select.length) {
			cfield_cont = cfield_select.val();
			if (cfield_cont.length) {
				var country_code = jQuery("option:selected", cfield_select).attr("data-ccode");
				if (country_code.length) {
					jQuery("#ccode_inpfield").val(country_code);
				}
			}
		}
		if (cfield_cont.length) {
			cfields_cont += cfield_name+": "+cfield_cont+"\r\n";
			tot_rows++;
			cfields_labels.push(cfield_name+":");
		}
	});
	if (cfields_cont.length) {
		cfields_cont = cfields_cont.replace(/\r\n+$/, "");
	}
	if (nominatives.length > 1) {
		jQuery("#t_first_name_inpfield").val(nominatives[0]);
		jQuery("#t_last_name_inpfield").val(nominatives[1]);
	}
	jQuery("#vrcustdatatxtarea").val(cfields_cont);
	jQuery("#vrcustdatatxtarea").attr("rows", tot_rows);
	hideCustomFields();
}

function vrCalcNights() {
	vrc_glob_sel_nights = 0;
	var vrritiro = document.getElementById("pickupdate").value;
	var vrconsegna = document.getElementById("releasedate").value;
	if (vrritiro.length > 0 && vrconsegna.length > 0) {
		var vrritirop = vrritiro.split("/");
		var vrconsegnap = vrconsegna.split("/");
		var vrc_df = "<?php echo $vrc_df; ?>";
		if (vrc_df == "%d/%m/%Y") {
			var vrinmonth = parseInt(vrritirop[1]);
			vrinmonth = vrinmonth - 1;
			var vrinday = parseInt(vrritirop[0], 10);
			var vrritirod = new Date(vrritirop[2], vrinmonth, vrinday);
			var vrcutmonth = parseInt(vrconsegnap[1]);
			vrcutmonth = vrcutmonth - 1;
			var vrcutday = parseInt(vrconsegnap[0], 10);
			var vrconsegnad = new Date(vrconsegnap[2], vrcutmonth, vrcutday);
		} else if (vrc_df == "%m/%d/%Y") {
			var vrinmonth = parseInt(vrritirop[0]);
			vrinmonth = vrinmonth - 1;
			var vrinday = parseInt(vrritirop[1], 10);
			var vrritirod = new Date(vrritirop[2], vrinmonth, vrinday);
			var vrcutmonth = parseInt(vrconsegnap[0]);
			vrcutmonth = vrcutmonth - 1;
			var vrcutday = parseInt(vrconsegnap[1], 10);
			var vrconsegnad = new Date(vrconsegnap[2], vrcutmonth, vrcutday);
		} else {
			var vrinmonth = parseInt(vrritirop[1]);
			vrinmonth = vrinmonth - 1;
			var vrinday = parseInt(vrritirop[2], 10);
			var vrritirod = new Date(vrritirop[0], vrinmonth, vrinday);
			var vrcutmonth = parseInt(vrconsegnap[1]);
			vrcutmonth = vrcutmonth - 1;
			var vrcutday = parseInt(vrconsegnap[2], 10);
			var vrconsegnad = new Date(vrconsegnap[0], vrcutmonth, vrcutday);
		}
		var vrdivider = 1000 * 60 * 60 * 24;
		var vrints = vrritirod.getTime();
		var vrcutts = vrconsegnad.getTime();
		if (vrcutts > vrints) {
			//var vrnights = Math.ceil((vrcutts - vrints) / (vrdivider));
			var utc1 = Date.UTC(vrritirod.getFullYear(), vrritirod.getMonth(), vrritirod.getDate());
			var utc2 = Date.UTC(vrconsegnad.getFullYear(), vrconsegnad.getMonth(), vrconsegnad.getDate());
			var vrnights = Math.ceil((utc2 - utc1) / vrdivider);
			if (vrnights > 0) {
				vrc_glob_sel_nights = vrnights;
				document.getElementById("vrjstotnights").innerHTML = "<?php echo addslashes(JText::_('VRDAYS')); ?>: "+vrnights;
				// update average cost per night
				vrCalcDailyCost(document.getElementById("cust_cost").value);
			} else {
				document.getElementById("vrjstotnights").innerHTML = "";
			}
		} else {
			document.getElementById("vrjstotnights").innerHTML = "";
		}
	} else {
		document.getElementById("vrjstotnights").innerHTML = "";
	}
}

function vrCalcDailyCost(cur_val) {
	// trigger calculation of website rates
	vrcCalcWebsiteRates();
	//
	var avg_cost_str = "";
	if (cur_val.length && !isNaN(cur_val) && vrc_glob_sel_nights > 0) {
		var avg_cost = (parseFloat(cur_val) / vrc_glob_sel_nights).toFixed(<?php echo (int)$formatparts[0]; ?>);
		avg_cost_str = "<?php echo $currencysymb; ?> " + avg_cost + "/<?php echo addslashes(JText::_('VRDAY')); ?>";
	}
	document.getElementById("avg-daycost").innerHTML = avg_cost_str;
}

function vrcCalcWebsiteRates() {
	// unset previously selected rates, if any
	vrcUnsetWebsiteRate();
	//
	var checkinfdate = jQuery("#pickupdate").val();
	var units = 1;
	if (!checkinfdate.length || vrc_glob_sel_nights < 1 || jQuery("input[name=\"setclosed\"]").is(":checked")) {
		console.log('yes', checkinfdate.length, vrc_glob_sel_nights, jQuery("input[name=\"setclosed\"]").is(":checked"));
		jQuery("#vrc-website-rates-row").hide();
		return false;
	}
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: {
			option: "com_vikrentcar",
			task: "calc_rates",
			id_car: <?php echo $car['id']; ?>,
			checkinfdate: checkinfdate,
			num_days: vrc_glob_sel_nights,
			units: units,
			only_rates: 1,
			tmpl: "component"
		}
	}).done(function(resp) {
		var obj_res = null;
		try {
			obj_res = JSON.parse(resp);
		} catch(err) {
			console.error("could not parse JSON response", resp);
		}
		if (obj_res === null || !jQuery.isArray(obj_res)) {
			jQuery("#vrc-website-rates-row").hide();
			console.info("invalid JSON response", resp);
			return false;
		}
		if (!obj_res[0].hasOwnProperty("idprice")) {
			jQuery("#vrc-website-rates-row").hide();
			console.log("error in response", resp);
			return false;
		}
		// display the rates obtained
		var wrhtml = "";
		for (var i in obj_res) {
			if (!obj_res.hasOwnProperty(i)) {
				continue;
			}
			wrhtml += "<div class=\"vrc-cal-wbrate-wrap\" onclick=\"vrcSelWebsiteRate(this);\">";
			wrhtml += "<div class=\"vrc-cal-wbrate-inner\">";
			wrhtml += "<span class=\"vrc-cal-wbrate-name\" data-idprice=\"" + obj_res[i]["idprice"] + "\">" + obj_res[i]["name"] + "</span>";
			wrhtml += "<span class=\"vrc-cal-wbrate-cost\" data-cost=\"" + obj_res[i]["tot"] + "\">" + obj_res[i]["ftot"] + "</span>";
			wrhtml += "</div>";
			wrhtml += "</div>";
		}
		jQuery("#vrc-website-rates-cont").html(wrhtml);
		jQuery("#vrc-website-rates-row").fadeIn();
		if (<?php echo $pidprice > 0 && $pbooknow > 0 ? 'true' : 'false'; ?> && !booknowmade) {
			// we get here by clicking the book-now button from the rates calculator only once
			booknowmade = true;
			// trigger the click for the requested rate plan ID
			jQuery('.vrc-cal-wbrate-name[data-idprice="<?php echo $pidprice; ?>"]').closest('.vrc-cal-wbrate-wrap').trigger('click');
		}
	}).fail(function() {
		jQuery("#vrc-website-rates-row").hide();
		console.error("Error calculating the rates");
	});
}

function vrcSelWebsiteRate(elem) {
	var rate = jQuery(elem);
	var idprice = rate.find('.vrc-cal-wbrate-name').attr('data-idprice');
	var cost = rate.find('.vrc-cal-wbrate-cost').attr('data-cost');
	var prev_idprice = jQuery('#booking-idprice').val();
	// reset all selected classes
	jQuery('.vrc-cal-wbrate-wrap').removeClass('vrc-cal-wbrate-wrap-selected');
	if (prev_idprice.length && prev_idprice == idprice) {
		// rate plan has been de-selected
		jQuery('#booking-idprice').val("");
		jQuery('#booking-carcost').val("");
		jQuery('#cust_cost').attr('readonly', false);
	} else {
		// rate plan has been selected
		rate.addClass('vrc-cal-wbrate-wrap-selected');
		jQuery('#booking-idprice').val(idprice);
		jQuery('#booking-carcost').val(cost);
		jQuery('#cust_cost').attr('readonly', true);
	}
}

function vrcUnsetWebsiteRate() {
	jQuery('#booking-idprice').val("");
	jQuery('#booking-carcost').val("");
	jQuery('.vrc-cal-wbrate-wrap').removeClass('vrc-cal-wbrate-wrap-selected');
	jQuery('#cust_cost').attr('readonly', false);
}

jQuery(document).ready(function() {
	
	jQuery("#vrc-calendar-changecar").select2();
	jQuery("#pickuploc").select2({placeholder: '<?php echo addslashes(JText::_('VRRITIROCAR')); ?>'});
	jQuery("#dropoffloc").select2({placeholder: '<?php echo addslashes(JText::_('VRRETURNCARORD')); ?>'});

	jQuery('td.free').click(function() {
		var indate = jQuery('#pickupdate').val();
		var outdate = jQuery('#releasedate').val();
		var clickdate = jQuery(this).attr('data-daydate');
		if (!(indate.length > 0)) {
			jQuery('#pickupdate').datepicker("setDate", clickdate);
		} else if (!(outdate.length > 0) && clickdate != indate) {
			jQuery('#releasedate').datepicker("setDate", clickdate);
		} else {
			jQuery('#releasedate').datepicker("setDate", '');
			jQuery('#pickupdate').datepicker("setDate", clickdate);
		}
		jQuery(".ui-datepicker-current-day").click();
	});

	jQuery("#vrfillcustfields").click(function(){
		showCustomFields();
	});

	jQuery(document).mouseup(function(e) {
		if (!cfields_overlay) {
			return false;
		}
		var vrdialogcf_cont = jQuery(".vrc-calendar-cfields-filler");
		if (!vrdialogcf_cont.is(e.target) && vrdialogcf_cont.has(e.target).length === 0) {
			hideCustomFields();
		}
	});
	
	//Search customer - Start
	var vrccustsdelay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();

	function vrcCustomerSearch(words) {
		jQuery("#vrc-searchcust-res").hide().html("");
		jQuery("#vrc-searchcust-loading").show();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikrentcar", task: "searchcustomer", kw: words, tmpl: "component" }
		}).done(function(cont) {
			if (cont.length) {
				var obj_res = JSON.parse(cont);
				customers_search_vals = obj_res[0];
				jQuery("#vrc-searchcust-res").html(obj_res[1]);
			} else {
				customers_search_vals = "";
				jQuery("#vrc-searchcust-res").html("----");
			}
			jQuery("#vrc-searchcust-res").show();
			jQuery("#vrc-searchcust-loading").hide();
		}).fail(function() {
			jQuery("#vrc-searchcust-loading").hide();
			alert("Error Searching.");
		});
	}

	jQuery("#vrc-searchcust").keyup(function(event) {
		vrccustsdelay(function() {
			var keywords = jQuery("#vrc-searchcust").val();
			var chars = keywords.length;
			if (chars > 1) {
				if ((event.which > 96 && event.which < 123) || (event.which > 64 && event.which < 91) || event.which == 13) {
					vrcCustomerSearch(keywords);
				}
			} else {
				if (jQuery("#vrc-searchcust-res").is(":visible")) {
					jQuery("#vrc-searchcust-res").hide();
				}
			}
		}, 600);
	});
	//Search customer - End

	//Datepickers - Start
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "" ] );
	jQuery("#pickupdate").datepicker({
		showOn: "focus",
		dateFormat: "<?php echo $juidf; ?>",
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var nowritiro = jQuery("#pickupdate").datepicker("getDate");
			var nowpickupdate = new Date(nowritiro.getTime());
			jQuery("#releasedate").datepicker( "option", "minDate", nowpickupdate );
			vrCalcNights();
		}
	});
	jQuery("#releasedate").datepicker({
		showOn: "focus",
		dateFormat: "<?php echo $juidf; ?>",
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			vrCalcNights();
		}
	});
	jQuery(".vrcdatepicker-trig-icon").click(function(){
		var jdp = jQuery(this).prev("input.hasDatepicker");
		if (jdp.length) {
			jdp.focus();
		}
	});
	//Datepickers - End
	<?php echo (!empty($ppickup) ? 'jQuery("#pickupdate").datepicker("setDate", "'.$ppickup.'");'."\n" : ''); ?>
	<?php echo (!empty($pdropoff) ? 'jQuery("#releasedate").datepicker("setDate", "'.$pdropoff.'");'."\n" : ''); ?>
	<?php echo (!empty($ppickup) || !empty($pdropoff) ? 'jQuery(".ui-datepicker-current-day").click();'."\n" : ''); ?>
});

jQuery(document).on("click", ".vrc-custsearchres-entry", function() {
	var custid = jQuery(this).attr("data-custid");
	var custemail = jQuery(this).attr("data-email");
	var custphone = jQuery(this).attr("data-phone");
	var custcountry = jQuery(this).attr("data-country");
	var custfirstname = jQuery(this).attr("data-firstname");
	var custlastname = jQuery(this).attr("data-lastname");
	jQuery("#customer_id_inpfield").val(custid);
	if (customers_search_vals.hasOwnProperty(custid)) {
		jQuery.each(customers_search_vals[custid], function(cfid, cfval) {
			var fill_field = jQuery("#cfield"+cfid);
			if (fill_field.length) {
				fill_field.val(cfval);
			}
		});
	} else {
		jQuery("input[data-isnominative=\"1\"]").each(function(k, v) {
			if (k == 0) {
				jQuery(this).val(custfirstname);
				return true;
			}
			if (k == 1) {
				jQuery(this).val(custlastname);
				return true;
			}
			return false;
		});
		jQuery("input[data-isemail=\"1\"]").val(custemail);
		jQuery("input[data-isphone=\"1\"]").val(custphone);
		//Populate main calendar form
		jQuery("#custmailfield").val(custemail);
		jQuery("#t_first_name_inpfield").val(custfirstname);
		jQuery("#t_last_name_inpfield").val(custlastname);
		//
	}
	applyCustomFieldsContent();
	if (custcountry.length) {
		jQuery("#ccode_inpfield").val(custcountry);
	}
	if (custphone.length) {
		jQuery("#phonefield").val(custphone);
	}
});
</script>
