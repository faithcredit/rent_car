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
$wsel = $this->wsel;
$wpricesel = $this->wpricesel;
$wlocsel = $this->wlocsel;

$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadSelect2();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
if (strlen($wsel) > 0) {
	JHTML::_('behavior.calendar');
	$caldf = VikRentCar::getDateFormat(true);
	$currencysymb = VikRentCar::getCurrencySymb(true);
	if ($caldf == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($caldf == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	if (count($row) && ($row['from'] > 0 || $row['to'] > 0)) {
		$nowyear = !empty($row['year']) ? $row['year'] : date('Y');
		$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
		$fromdate = date($df, ($frombase + $row['from']));
		if ($row['to'] < $row['from']) {
			$nowyear = $nowyear + 1;
			$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
		}
		$todate = date($df, ($frombase + $row['to']));
		//leap years
		$checkly = !empty($row['year']) ? $row['year'] : date('Y');
		if ($checkly % 4 == 0 && ($checkly % 100 != 0 || $checkly % 400 == 0)) {
			$frombase = mktime(0, 0, 0, 1, 1, $checkly);
			$infoseason = getdate($frombase + $row['from']);
			$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
			if ($infoseason[0] > $leapts) {
				/**
				 * Timestamp must be greater than the leap-day of Feb 29th.
				 * It used to be checked for >= $leapts.
				 * 
				 * @since 	July 3rd 2019
				 */
				$fromdate = date($df, ($frombase + $row['from'] + 86400));
				$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
				$todate = date($df, ($frombase + $row['to'] + 86400));
			}
		}
		//
	} else {
		$fromdate = '';
		$todate = '';
	}
	$actweekdays = count($row) ? explode(";", $row['wdays']) : array();
	
	$actvalueoverrides = '';
	if (count($row) && strlen($row['losoverride']) > 0) {
		$losoverrides = explode('_', $row['losoverride']);
		foreach ($losoverrides as $loso) {
			if (!empty($loso)) {
				$losoparts = explode(':', $loso);
				$losoparts[2] = strstr($losoparts[0], '-i') != false ? 1 : 0;
				$losoparts[0] = str_replace('-i', '', $losoparts[0]);
				$actvalueoverrides .= '<p>'.JText::_('VRNEWSEASONNIGHTSOVR').' <input type="number" min="1" name="nightsoverrides[]" value="'.$losoparts[0].'"/> <select name="andmoreoverride[]"><option value="0">-------</option><option value="1"'.($losoparts[2] == 1 ? ' selected="selected"' : '').'>'.JText::_('VRNEWSEASONVALUESOVREMORE').'</option></select> - '.JText::_('VRNEWSEASONVALUESOVR').' <input type="number" step="any" name="valuesoverrides[]" value="'.$losoparts[1].'" style="min-width: 60px !important;"/> '.(intval($row['val_pcent']) == 2 ? '%' : $currencysymb).'</p>';
			}
		}
	}
	
	?>
	<script type="text/javascript">
	function addMoreOverrides() {
		var sel = document.getElementById('val_pcent');
		var curpcent = sel.options[sel.selectedIndex].text;
		var ni = document.getElementById('myDiv');
		var numi = document.getElementById('morevalueoverrides');
		var num = (document.getElementById('morevalueoverrides').value -1)+ 2;
		numi.value = num;
		var newdiv = document.createElement('div');
		var divIdName = 'my'+num+'Div';
		newdiv.setAttribute('id',divIdName);
		newdiv.innerHTML = '<p><?php echo addslashes(JText::_('VRNEWSEASONNIGHTSOVR')); ?> <input type=\'number\' min=\'1\' name=\'nightsoverrides[]\' value=\'\'/> <select name=\'andmoreoverride[]\'><option value=\'0\'>-------</option><option value=\'1\'><?php echo addslashes(JText::_('VRNEWSEASONVALUESOVREMORE')); ?></option></select> - <?php echo addslashes(JText::_('VRNEWSEASONVALUESOVR')); ?> <input type=\'number\' step=\'any\' name=\'valuesoverrides[]\' value=\'\' style=\'min-width: 60px !important;\'/> '+curpcent+'</p>';
		ni.appendChild(newdiv);
	}
	jQuery(document).ready(function() {
		jQuery(".vrc-select-all").click(function() {
			var nextsel = jQuery(this).next("select");
			nextsel.find("option").prop('selected', true);
			nextsel.trigger('change');
		});
		jQuery('#idcars, #idprices, #vrc-selwdays, #idlocation').select2();
	});
	function togglePromotion() {
		var promo_on = jQuery('input[name="promo"]').prop('checked');
		if (promo_on === true) {
			var cur_startd = jQuery('#from').val();
			var cur_endd = jQuery('#to').val();
		<?php
		if (!count($row)) {
			// when creating a new promotion, we make sure the dates are defined to disallow promos with no dates
			?>
			if (!cur_startd.length || !cur_endd.length) {
				alert('<?php echo addslashes(JText::_('VRCPROMOWARNNODATES')); ?>');
				jQuery('input[name="promo"]').prop('checked', false);
				jQuery('.promotr').fadeOut();
				return false;
			}
			<?php
		}
		?>
			jQuery('.promotr').fadeIn();
			jQuery('#promovalidity span').text('');
			if (cur_startd.length) {
				jQuery('#promovalidity span').text(' ('+cur_startd+')');
			}
		} else {
			jQuery('.promotr').fadeOut();
		}
	}
	</script>
	<input type="hidden" value="0" id="morevalueoverrides" />
	
	<form name="adminForm" id="adminForm" action="index.php" method="post">

		<div class="vrc-admin-container">
			<div class="vrc-config-maintab-left">
				<fieldset class="adminform">
					<div class="vrc-params-wrap">
						<legend class="adminlegend"><?php echo JText::_('VRCSPRICESHELPTITLE'); ?> &nbsp; <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCSPRICESHELPTITLE'), 'content' => JText::_('VRCSPRICESHELP'))); ?></legend>
						<div class="vrc-params-container">
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRCSEASON'); ?></div>
								<div class="vrc-param-setting">
									<div style="display: block; margin-bottom: 3px;">
										<?php echo '<span class="vrcrestrdrangesp">'.JText::_('VRNEWRESTRICTIONDFROMRANGE').'</span>'.$vrc_app->getCalendar($fromdate, 'from', 'from', $caldf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
									</div>
									<div style="display: block; margin-bottom: 3px;">
										<?php echo '<span class="vrcrestrdrangesp">'.JText::_('VRNEWRESTRICTIONDTORANGE').'</span>'.$vrc_app->getCalendar($todate, 'to', 'to', $caldf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
									</div>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRCWEEKDAYS'); ?></div>
								<div class="vrc-param-setting">
									<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
									<select multiple="multiple" size="7" name="wdays[]" id="vrc-selwdays">
										<option value="0"<?php echo (in_array("0", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCSUNDAY'); ?></option>
										<option value="1"<?php echo (in_array("1", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCMONDAY'); ?></option>
										<option value="2"<?php echo (in_array("2", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCTUESDAY'); ?></option>
										<option value="3"<?php echo (in_array("3", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCWEDNESDAY'); ?></option>
										<option value="4"<?php echo (in_array("4", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCTHURSDAY'); ?></option>
										<option value="5"<?php echo (in_array("5", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCFRIDAY'); ?></option>
										<option value="6"<?php echo (in_array("6", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCSATURDAY'); ?></option>
									</select>
									<span class="vrc-param-setting-comment"><?php echo JText::_('VRCSPWDAYSHELP'); ?></span>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRCSPNAME'); ?></div>
								<div class="vrc-param-setting">
									<input type="text" name="spname" value="<?php echo count($row) ? htmlspecialchars($row['spname']) : ''; ?>" size="30"/>
									<span class="vrc-param-setting-comment"><?php echo JText::_('VRCSPNAMEHELP'); ?></span>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRCSPYEARTIED'); ?></div>
								<div class="vrc-param-setting">
									<?php echo $vrc_app->printYesNoButtons('yeartied', JText::_('VRYES'), JText::_('VRNO'), (!count($row) || (count($row) && !empty($row['year'])) ? 1 : 0), 1, 0); ?>
									<span class="vrc-param-setting-comment"><?php echo JText::_('VRCSPYEARTIEDHELP'); ?></span>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRCSPONLYPICKINCL'); ?></div>
								<div class="vrc-param-setting">
									<?php echo $vrc_app->printYesNoButtons('pickupincl', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$row['pickupincl'] : 0), 1, 0); ?>
									<span class="vrc-param-setting-comment"><?php echo JText::_('VRCSPONLCKINHELP'); ?></span>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRCSPKEEPFIRSTDAYRATE'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCSPKEEPFIRSTDAYRATE'), 'content' => JText::_('VRCSPKEEPFIRSTDAYRATEHELP'))); ?></div>
								<div class="vrc-param-setting">
									<?php echo $vrc_app->printYesNoButtons('keepfirstdayrate', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$row['keepfirstdayrate'] : 0), 1, 0); ?>
								</div>
							</div>
						</div>
					</div>
				</fieldset>

				<fieldset class="adminform">
					<div class="vrc-params-wrap">
						<legend class="adminlegend"><?php echo JText::_('VRCSPPROMOTIONLABEL'); ?></legend>
						<div class="vrc-params-container">
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRCISPROMOTION'); ?></div>
								<div class="vrc-param-setting">
									<?php echo $vrc_app->printYesNoButtons('promo', JText::_('VRYES'), JText::_('VRNO'), (count($row) && $row['promo'] ? 1 : 0), 1, 0, 'togglePromotion();'); ?>
									<span class="vrc-param-setting-comment"><?php echo JText::_('VRCSPTPROMOHELP'); ?></span>
								</div>
							</div>
							<div class="vrc-param-container promotr">
								<div class="vrc-param-label"><?php echo JText::_('VRCPROMOVALIDITY'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPROMOVALIDITY'), 'content' => JText::_('VRCPROMOVALIDITYHELP'))); ?></div>
								<div class="vrc-param-setting">
									<input type="number" min="0" name="promodaysadv" value="<?php echo !count($row) || (count($row) && empty($row['promodaysadv'])) ? '0' : (int)$row['promodaysadv']; ?>"/>
									<span id="promovalidity"><?php echo JText::_('VRCPROMOVALIDITYDAYSADV'); ?>
										<span></span>
									</span>
								</div>
							</div>
							<div class="vrc-param-container promotr">
								<div class="vrc-param-label"><?php echo JText::_('VRCPROMOLASTMINUTE'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPROMOLASTMINUTE'), 'content' => JText::_('VRCPROMOLASTMINUTEHELP'))); ?></div>
								<div class="vrc-param-setting">
								<?php
								$lastmind = 0;
								$lastminh = 0;
								if (count($row) && !empty($row['promolastmin'])) {
									$lastmind = floor($row['promolastmin'] / 86400);
									$lastminh = floor(($row['promolastmin'] - ($lastmind * 86400)) / 3600);
								}
								?>
									<div style="display: inline-block; margin-right: 10px;">
										<input type="number" name="promolastmind" value="<?php echo $lastmind; ?>" min="0"/>
										<span class="lastminlbl"><?php echo strtolower(JText::_('VRDAYS')); ?></span>
									</div>
									<div style="display: inline-block;">
										<input type="number" name="promolastminh" value="<?php echo $lastminh; ?>" min="0" max="23"/>
										<span class="lastminlbl"><?php echo strtolower(JText::_('VRCHOURS')); ?></span>
									</div>
								</div>
							</div>
							<div class="vrc-param-container promotr">
								<div class="vrc-param-label"><?php echo JText::_('VRCPROMOFORCEMINLOS'); ?></div>
								<div class="vrc-param-setting"><input type="number" min="0" name="promominlos" value="<?php echo !count($row) || (count($row) && empty($row['promominlos'])) ? 0 : (int)$row['promominlos']; ?>"/></div>
							</div>
							<div class="vrc-param-container promotr">
								<div class="vrc-param-label"><?php echo JText::_('VRCPROMOONFINALPRICE'); ?></div>
								<div class="vrc-param-setting">
									<?php echo $vrc_app->printYesNoButtons('promofinalprice', JText::_('VRYES'), JText::_('VRNO'), ((!count($row) || (count($row) && $row['promofinalprice'])) ? 1 : 0), 1, 0); ?>
									<span class="vrc-param-setting-comment"><?php echo JText::_('VRCPROMOONFINALPRICEHELP'); ?> <span style="cursor: pointer;" onclick="vrcOpenModalHelp();"><?php VikRentCarIcons::e('info-circle'); ?></span></span>
								</div>
							</div>
							<div class="vrc-param-container promotr">
								<div class="vrc-param-label"><?php echo JText::_('VRCPROMOTEXT'); ?></div>
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
											echo $editor->display( "promotxt", (count($row) ? $row['promotxt'] : ""), '100%', 300, 70, 20 );
										} catch (Throwable $t) {
											echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
										}
									} else {
										// we cannot catch Fatal Errors in PHP 5.x
										echo $editor->display( "promotxt", (count($row) ? $row['promotxt'] : ""), '100%', 300, 70, 20 );
									}
									?>
									<span class="vrc-param-setting-comment"><?php echo JText::_('VRCPROMOTEXTHELP'); ?></span>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
			</div>

			<div class="vrc-config-maintab-right">
				<fieldset class="adminform">
					<div class="vrc-params-wrap">
						<legend class="adminlegend"><?php echo JText::_('VRCSEASONPRICING'); ?></legend>
						<div class="vrc-params-container">
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWSEASONTHREE'); ?></div>
								<div class="vrc-param-setting">
									<select name="type">
										<option value="1"<?php echo count($row) && intval($row['type']) == 1 ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VRNEWSEASONSIX'); ?></option>
										<option value="2"<?php echo count($row) && intval($row['type']) == 2 ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VRNEWSEASONSEVEN'); ?></option>
									</select>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWSEASONFOUR'); ?></div>
								<div class="vrc-param-setting">
									<input type="number" step="any" name="diffcost" value="<?php echo count($row) ? (float)$row['diffcost'] : ''; ?>" style="min-width: 100px !important;" />
									<select name="val_pcent" id="val_pcent">
										<option value="2"<?php echo (count($row) && intval($row['val_pcent']) == 2 ? " selected=\"selected\"" : ""); ?>>%</option>
										<option value="1"<?php echo (count($row) && intval($row['val_pcent']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option>
									</select> 
									&nbsp;
									<?php echo $vrc_app->createPopover(array('title' => JText::_('VRNEWSEASONFOUR'), 'content' => JText::_('VRSPECIALPRICEVALHELP'))); ?>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWSEASONVALUEOVERRIDE'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRNEWSEASONVALUEOVERRIDE'), 'content' => JText::_('VRNEWSEASONVALUEOVERRIDEHELP'))); ?></div>
								<div class="vrc-param-setting">
									<div id="myDiv" style="display: block;"><?php echo $actvalueoverrides; ?></div>
									<a class="btn vrc-config-btn" href="javascript: void(0);" onclick="addMoreOverrides();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRNEWSEASONADDOVERRIDE'); ?></a>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWSEASONROUNDCOST'); ?></div>
								<div class="vrc-param-setting">
									<select name="roundmode">
										<option value=""><?php echo JText::_('VRNEWSEASONROUNDCOSTNO'); ?></option>
										<option value="PHP_ROUND_HALF_UP"<?php echo (count($row) && $row['roundmode'] == 'PHP_ROUND_HALF_UP' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRNEWSEASONROUNDCOSTUP'); ?></option>
										<option value="PHP_ROUND_HALF_DOWN"<?php echo (count($row) && $row['roundmode'] == 'PHP_ROUND_HALF_DOWN' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRNEWSEASONROUNDCOSTDOWN'); ?></option>
									</select>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWSEASONFIVE'); ?></div>
								<div class="vrc-param-setting">
									<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
									<?php echo $wsel; ?>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRCSPTYPESPRICE'); ?></div>
								<div class="vrc-param-setting">
									<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
									<?php echo $wpricesel; ?>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWSEASONEIGHT'); ?></div>
								<div class="vrc-param-setting">
									<?php echo $wlocsel; ?>
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

	<div class="vrc-modal-overlay-block vrc-modal-overlay-block-promofphelp">
		<a class="vrc-modal-overlay-close" href="javascript: void(0);"></a>
		<div class="vrc-modal-overlay-content vrc-modal-overlay-content-promofphelp">
			<div class="vrc-modal-overlay-content-head vrc-modal-overlay-content-head-promofphelp">
				<h3><?php VikRentCarIcons::e('info-circle'); ?> <?php echo JText::_('VRCPROMOONFINALPRICE'); ?> <span class="vrc-modal-overlay-close-times" onclick="hideVrcModalHelp();">&times;</span></h3>
			</div>
			<div class="vrc-modal-overlay-content-body vrc-modal-overlay-content-body-scroll">
				<p><?php echo JText::_('VRCPROMOONFINALPRICETXT'); ?></p>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	var vrcdialoghelp_on = false;

	jQuery(document).ready(function() {
		jQuery('#from').val('<?php echo $fromdate; ?>').attr('data-alt-value', '<?php echo $fromdate; ?>');
		jQuery('#to').val('<?php echo $todate; ?>').attr('data-alt-value', '<?php echo $todate; ?>');

		setTimeout(function() {
			togglePromotion();
		}, 100);

		jQuery(document).keydown(function(e) {
			if (e.keyCode == 27) {
				if (vrcdialoghelp_on === true) {
					hideVrcModalHelp();
				}
			}
		});
		jQuery(document).mouseup(function(e) {
			if (!vrcdialoghelp_on) {
				return false;
			}
			if (vrcdialoghelp_on) {
				var vrc_overlay_cont = jQuery(".vrc-modal-overlay-content-promofphelp");
				if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
					hideVrcModalHelp();
				}
			}
		});
	});

	function vrcOpenModalHelp() {
		jQuery('.vrc-modal-overlay-block-promofphelp').fadeIn();
		vrcdialoghelp_on = true;
	}

	function hideVrcModalHelp() {
		if (vrcdialoghelp_on === true) {
			jQuery(".vrc-modal-overlay-block-promofphelp").fadeOut(400, function () {
				jQuery(".vrc-modal-overlay-content-promofphelp").show();
			});
			// turn flag off
			vrcdialoghelp_on = false;
		}
	}
	</script>
	<?php
} else {
	?>
	<p class="err"><a href="index.php?option=com_vikrentcar&amp;task=newcar"><?php echo JText::_('VRNOCARSFOUNDSEASONS'); ?></a></p>
	<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentcar" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<?php
}
