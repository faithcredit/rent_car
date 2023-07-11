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

$dbo = JFactory::getDbo();

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$vrc_app = VikRentCar::getVrcApplication();

JHtml::_('behavior.calendar');

$firstwday = (int)VikRentCar::getFirstWeekDay(true);
$days_labels = array(
	JText::_('VRCSUNDAY'),
	JText::_('VRCMONDAY'),
	JText::_('VRCTUESDAY'),
	JText::_('VRCWEDNESDAY'),
	JText::_('VRCTHURSDAY'),
	JText::_('VRCFRIDAY'),
	JText::_('VRCSATURDAY')
);
$days_indexes = array();
for ($i = 0; $i < 7; $i++) {
	$days_indexes[$i] = (6-($firstwday-$i)+1)%7;
}

$wopening = count($row) && !empty($row['wopening']) ? json_decode($row['wopening'], true) : array();
$wopening = !is_array($wopening) ? array() : $wopening;

$difftime = false;
if (count($row) && !empty($row['opentime'])) {
	$difftime = true;
	$parts = explode("-", $row['opentime']);
	$openat = VikRentCar::getHoursMinutes($parts[0]);
	$closeat = VikRentCar::getHoursMinutes($parts[1]);
}
$hours = "<option value=\"\"> </option>\n";
$hours_ovw = "<option value=\"\"> </option>\n";
for ($i = 0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$openat[0] == $i ? " selected=\"selected\"" : "");
	$hours .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	$hours_ovw .= "<option value=\"".$i."\" data-val=\";".$i.";\">".$in."</option>\n";
}
$sugghours = "<option value=\"\"> </option>\n";
$defhour = count($row) && !empty($row['defaulttime']) ? ((int)$row['defaulttime'] / 3600) : '';
for ($i = 0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = (strlen($defhour) && $defhour == $i ? " selected=\"selected\"" : "");
	$sugghours.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$minutes = "<option value=\"\"> </option>\n";
$minutes_ovw = "<option value=\"\"> </option>\n";
for ($i = 0; $i < 60; $i += 5) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$openat[1] == $i ? " selected=\"selected\"" : "");
	$minutes .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	$minutes_ovw .= "<option value=\"".$i."\" data-val=\";".$i.";\">".$in."</option>\n";
}
$hoursto = "<option value=\"\"> </option>\n";
for ($i = 0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$closeat[0] == $i ? " selected=\"selected\"" : "");
	$hoursto.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$minutesto = "<option value=\"\"> </option>\n";
for ($i = 0; $i < 60; $i += 5) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$closeat[1] == $i ? " selected=\"selected\"" : "");
	$minutesto.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}

$wiva = "<select name=\"praliq\">\n";
$wiva .= "<option value=\"\"> ------ </option>\n";
$q = "SELECT * FROM `#__vikrentcar_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
}
$wiva .= "</select>\n";
?>
<script type="text/javascript">
	function vrcAddClosingDate() {
		var closingdadd = document.getElementById('insertclosingdate').value;
		var closingdintv = document.getElementById('closingintv').value;
		if (closingdadd.length > 0) {
			document.getElementById('closingdays').value += closingdadd + closingdintv + ',';
			document.getElementById('insertclosingdate').value = '';
			document.getElementById('closingintv').value = '';
		}
	}

	function vrcToggleWopening(mode, ind) {
		if (mode == 'on') {
			// plus button
			jQuery('#vrc-wopen-on-'+ind).hide();
			jQuery('#vrc-wopen-off-'+ind).fadeIn();
			jQuery('#wopening-'+ind).show();
		} else {
			// minus button
			jQuery('#vrc-wopen-off-'+ind).hide();
			jQuery('#vrc-wopen-on-'+ind).fadeIn();
			jQuery('#wopening-'+ind).hide().find('select').val('');
		}
	}

	function vrcAddWopeningBreak(elem) {
		var break_cont = jQuery(elem).closest('.vrc-loc-wopening-wday-breaks');
		var break_wrap = break_cont.find('.vrc-loc-wopening-wday-break-wrap');
		if (!break_wrap || !break_wrap.length) {
			return false;
		}
		if (break_wrap.length == 1 && !break_wrap.is(':visible')) {
			break_wrap.show();
		} else {
			var cloned = break_wrap.first().clone();
			cloned.find('select').val('');
			cloned.appendTo(break_cont.find('.vrc-loc-wopening-wday-breaks-cont'));
		}
	}

	function vrcRemoveWopeningBreak(elem) {
		var break_cont = jQuery(elem).closest('.vrc-loc-wopening-wday-breaks');
		var break_elem = jQuery(elem).closest('.vrc-loc-wopening-wday-break-wrap');
		if (break_cont.find('.vrc-loc-wopening-wday-break-wrap').length > 1) {
			break_elem.remove();
		} else {
			break_elem.hide().find('select').val('');
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
							<div class="vrc-param-label"><?php echo JText::_('VREDITPLACEONE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="placename" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCLOCADDRESS'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="address" value="<?php echo count($row) ? htmlspecialchars($row['address']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPLACELAT'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="lat" value="<?php echo count($row) ? JHtml::_('esc_attr', $row['lat']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPLACELNG'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="lng" value="<?php echo count($row) ? JHtml::_('esc_attr', $row['lng']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPLACEOVERRIDETAX'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPLACEOVERRIDETAX'), 'content' => JText::_('VRCPLACEOVERRIDETAXTXT'))); ?></div>
							<div class="vrc-param-setting"><?php echo $wiva; ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPLACEDESCR'); ?></div>
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
										echo $editor->display("descr", (count($row) ? $row['descr'] : ''), 400, 200, 70, 20);
									} catch (Throwable $t) {
										echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
									}
								} else {
									// we cannot catch Fatal Errors in PHP 5.x
									echo $editor->display("descr", (count($row) ? $row['descr'] : ''), 400, 200, 70, 20);
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
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDSETTINGS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPLACEOPENTIME'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPLACEOPENTIME'), 'content' => JText::_('VRCPLACEOPENTIMETXT'))); ?></div>
							<div class="vrc-param-setting">
								<table style="width: auto !important;">
									<tr>
										<td style="vertical-align: middle;"><?php echo JText::_('VRCPLACEOPENTIMEFROM'); ?>:</td>
										<td style="vertical-align: middle;"><select style="margin: 0;" name="opentimefh"><?php echo $hours; ?></select></td>
										<td style="vertical-align: middle;">:</td>
										<td style="vertical-align: middle;"><select style="margin: 0;" name="opentimefm"><?php echo $minutes; ?></select></td>
									</tr>
									<tr>
										<td style="vertical-align: middle;"><?php echo JText::_('VRCPLACEOPENTIMETO'); ?>:</td>
										<td style="vertical-align: middle;"><select style="margin: 0;" name="opentimeth"><?php echo $hoursto; ?></select></td>
										<td style="vertical-align: middle;">:</td>
										<td style="vertical-align: middle;"><select style="margin: 0;" name="opentimetm"><?php echo $minutesto; ?></select></td>
									</tr>
								</table>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPLACESUGGOPENTIME'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPLACESUGGOPENTIME'), 'content' => JText::_('VRCPLACESUGGOPENTIMETXT'))); ?></div>
							<div class="vrc-param-setting">
								<select name="suggopentimeh"><?php echo $sugghours; ?></select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPLACEOVROPENTIME'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPLACEOVROPENTIME'), 'content' => JText::_('VRCPLACEOVROPENTIMEHELP'))); ?></div>
							<div class="vrc-param-setting">
								<div class="vrc-param-loc-wopening-wrap">
								<?php
								for ($i = 0; $i < 7; $i++) {
									$d_ind = ($i + $firstwday) < 7 ? ($i + $firstwday) : ($i + $firstwday - 7);
									$fhopt = isset($wopening[$d_ind]) ? str_replace('data-val=";'.$wopening[$d_ind]['fh'].';"', 'selected="selected"', $hours_ovw) : $hours_ovw;
									$fmopt = isset($wopening[$d_ind]) ? str_replace('data-val=";'.$wopening[$d_ind]['fm'].';"', 'selected="selected"', $minutes_ovw) : $minutes_ovw;
									$thopt = isset($wopening[$d_ind]) ? str_replace('data-val=";'.$wopening[$d_ind]['th'].';"', 'selected="selected"', $hours_ovw) : $hours_ovw;
									$tmopt = isset($wopening[$d_ind]) ? str_replace('data-val=";'.$wopening[$d_ind]['tm'].';"', 'selected="selected"', $minutes_ovw) : $minutes_ovw;
									?>
									<div class="vrc-param-loc-wopening-wday">
										<div class="vrc-param-loc-wopening-wday-head">
											<div class="vrc-param-loc-wopening-wday-head-inner">
												<span><?php echo $days_labels[$d_ind]; ?></span>
												<a style="<?php echo isset($wopening[$d_ind]) ? 'display: none;' : ''; ?>" class="vrc-param-loc-toggle-on" href="javascript: void(0);" id="vrc-wopen-on-<?php echo $d_ind; ?>" onclick="vrcToggleWopening('on', '<?php echo $d_ind; ?>');"><?php VikRentCarIcons::e('plus-circle'); ?></a>
												<a style="<?php echo !isset($wopening[$d_ind]) ? 'display: none;' : ''; ?>" class="vrc-param-loc-toggle-off" href="javascript: void(0);" id="vrc-wopen-off-<?php echo $d_ind; ?>" onclick="vrcToggleWopening('off', '<?php echo $d_ind; ?>');"><?php VikRentCarIcons::e('minus-circle'); ?></a>
											</div>
										</div>
										<div class="vrc-param-loc-wopening-wday-override" style="<?php echo !isset($wopening[$d_ind]) ? 'display: none;' : ''; ?>" id="wopening-<?php echo $d_ind; ?>">
											<div class="vrc-loc-wopening-wday-shift">
												<div class="vrc-loc-wopening-wday-shift-lbl">
													<span><?php echo JText::_('VRCPLACEOPENTIME'); ?></span>
												</div>
												<div class="vrc-param-marginbottom">
													<span class="vrcrestrdrangesp"><?php echo JText::_('VRCPLACEOPENTIMEFROM'); ?></span>
													<span class="vrc-param-loc-wopening-override-sels">
														<select style="margin: 0;" name="wopeningfh[<?php echo $d_ind; ?>]"><?php echo $fhopt; ?></select>
														<span class="vrc-param-loc-wopening-timesep">:</span>
														<select style="margin: 0;" name="wopeningfm[<?php echo $d_ind; ?>]"><?php echo $fmopt; ?></select>
													</span>
												</div>
												<div class="vrc-param-marginbottom">
													<span class="vrcrestrdrangesp"><?php echo JText::_('VRCPLACEOPENTIMETO'); ?></span>
													<span class="vrc-param-loc-wopening-override-sels">
														<select style="margin: 0;" name="wopeningth[<?php echo $d_ind; ?>]"><?php echo $thopt; ?></select>
														<span class="vrc-param-loc-wopening-timesep">:</span>
														<select style="margin: 0;" name="wopeningtm[<?php echo $d_ind; ?>]"><?php echo $tmopt; ?></select>
													</span>
												</div>
											</div>
											<div class="vrc-loc-wopening-wday-breaks">
												<div class="vrc-loc-wopening-wday-breaks-lbl">
													<button type="button" class="btn vrc-config-btn vrc-loc-wopening-wday-break-add" onclick="vrcAddWopeningBreak(this);"><?php echo JText::_('VRC_OPENTIME_BREAKS'); ?> <?php VikRentCarIcons::e('plus-square'); ?></button>
												</div>
												<div class="vrc-loc-wopening-wday-breaks-cont">
												<?php
												$wday_breaks = isset($wopening[$d_ind]) && !empty($wopening[$d_ind]['breaks']) ? $wopening[$d_ind]['breaks'] : [];
												$tot_breaks  = max(1, count($wday_breaks));
												for ($b = 1; $b <= $tot_breaks; $b++) {
													$break_ind = ($b - 1);
													$fhbkt = !empty($wday_breaks[$break_ind]) ? str_replace('data-val=";'.$wday_breaks[$break_ind]['fh'].';"', 'selected="selected"', $hours_ovw) : $hours_ovw;
													$fmbkt = !empty($wday_breaks[$break_ind]) ? str_replace('data-val=";'.$wday_breaks[$break_ind]['fm'].';"', 'selected="selected"', $minutes_ovw) : $minutes_ovw;
													$thbkt = !empty($wday_breaks[$break_ind]) ? str_replace('data-val=";'.$wday_breaks[$break_ind]['th'].';"', 'selected="selected"', $hours_ovw) : $hours_ovw;
													$tmbkt = !empty($wday_breaks[$break_ind]) ? str_replace('data-val=";'.$wday_breaks[$break_ind]['tm'].';"', 'selected="selected"', $minutes_ovw) : $minutes_ovw;
													?>
													<div class="vrc-loc-wopening-wday-break-wrap" style="<?php echo empty($wday_breaks) ? 'display: none;' : ''; ?>">
														<span class="vrc-loc-wopening-wday-break-remove" onclick="vrcRemoveWopeningBreak(this);"><?php VikRentCarIcons::e('minus-square'); ?></span>
														<div class="vrc-param-marginbottom">
															<span class="vrcrestrdrangesp"><?php echo JText::_('VRCPLACEOPENTIMEFROM'); ?></span>
															<span class="vrc-param-loc-wopening-override-sels">
																<select style="margin: 0;" name="wbreakingfh[<?php echo $d_ind; ?>][]"><?php echo $fhbkt; ?></select>
																<span class="vrc-param-loc-wopening-timesep">:</span>
																<select style="margin: 0;" name="wbreakingfm[<?php echo $d_ind; ?>][]"><?php echo $fmbkt; ?></select>
															</span>
														</div>
														<div class="vrc-param-marginbottom">
															<span class="vrcrestrdrangesp"><?php echo JText::_('VRCPLACEOPENTIMETO'); ?></span>
															<span class="vrc-param-loc-wopening-override-sels">
																<select style="margin: 0;" name="wbreakingth[<?php echo $d_ind; ?>][]"><?php echo $thbkt; ?></select>
																<span class="vrc-param-loc-wopening-timesep">:</span>
																<select style="margin: 0;" name="wbreakingtm[<?php echo $d_ind; ?>][]"><?php echo $tmbkt; ?></select>
															</span>
														</div>
													</div>
													<?php
												}
												?>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								?>
								</div>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWPLACECLOSINGDAYS'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRNEWPLACECLOSINGDAYS'), 'content' => JText::_('VRNEWPLACECLOSINGDAYSHELP'))); ?></div>
							<div class="vrc-param-setting">
								<?php echo JHTML::_('calendar', '', 'insertclosingdate', 'insertclosingdate', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								<span class="vrc-loc-closeintv">
									<select id="closingintv">
										<option value=""><?php echo JText::_('VRNEWPLACECLOSINGDAYSINGLE'); ?></option>
										<option value=":w"><?php echo JText::_('VRNEWPLACECLOSINGDAYWEEK'); ?></option>
									</select>
								</span> 
								<span class="btn vrc-config-btn" onclick="javascript: vrcAddClosingDate();"><?php echo JText::_('VRNEWPLACECLOSINGDAYSADD'); ?></span>
								<textarea name="closingdays" id="closingdays" rows="5" cols="44"><?php echo count($row) ? $row['closingdays'] : ''; ?></textarea>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
?>
	<input type="hidden" name="whereup" value="<?php echo (int)$row['id']; ?>">
<?php
}
?>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<?php echo JHtml::_('form.token'); ?>
</form>
