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

$rows = $this->rows;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$arrbusy = $this->arrbusy;
$wmonthsel = $this->wmonthsel;
$tsstart = $this->tsstart;
$all_locations = $this->all_locations;
$plocation = $this->plocation;
$plocationw = $this->plocationw;

$app = JFactory::getApplication();

$nowtf = VikRentCar::getTimeFormat(true);
$wdays_map = array(
	JText::_('VRSUN'),
	JText::_('VRMON'),
	JText::_('VRTUE'),
	JText::_('VRWED'),
	JText::_('VRTHU'),
	JText::_('VRFRI'),
	JText::_('VRSAT')
);
$currencysymb = VikRentCar::getCurrencySymb(true);

$session = JFactory::getSession();
$show_type = $session->get('vrcUnitsShowType', '');
$mnum = $session->get('vrcOvwMnum', '1');
$mnum = intval($mnum);

$cookie = $app->input->cookie;
$cookie_uleft = $cookie->get('vrcAovwUleft', '', 'string');

// Cars Units Distinctive Features
$cars_features_map = array();
$cars_features_bookings = array();
$cars_bids_pools = array();
$bids_checkins = array();
$index_loop = 0;
foreach ($rows as $kr => $car) {
	if ($car['units'] > 1 && !empty($car['params']) && $car['units'] <= 250) {
		// sub-car units only if car type has 250 units at most
		$car_params = json_decode($car['params'], true);
		if (is_array($car_params) && array_key_exists('features', $car_params) && @count($car_params['features']) > 0) {
			$cars_features_map[$car['id']] = array();
			foreach ($car_params['features'] as $rind => $rfeatures) {
				foreach ($rfeatures as $fname => $fval) {
					if (strlen($fval)) {
						$cars_features_map[$car['id']][$rind] = '#'.$rind.' - '.JText::_($fname).': '.$fval;
						break;
					}
				}
			}
			if (!(count($cars_features_map[$car['id']]) > 0)) {
				unset($cars_features_map[$car['id']]);
			} else {
				foreach ($cars_features_map[$car['id']] as $rind => $indexdata) {
					$clone_car = $car;
					$clone_car['unit_index'] = (int)$rind;
					$clone_car['unit_index_str'] = $indexdata;
					array_splice($rows, ($kr + 1 + $index_loop), 0, array($clone_car));
					$index_loop++;
				}
			}
		}
	}
}
//
?>
<form class="vrc-avov-form" action="index.php?option=com_vikrentcar&amp;task=overv" method="post" name="vroverview">
	<div class="btn-toolbar vrc-avov-toolbar" id="filter-bar" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-left">
			<?php echo $wmonthsel; ?>
		</div>
		<div class="btn-group pull-left">
			<select name="mnum" onchange="document.vroverview.submit();">
			<?php
			for ($i = 1; $i <= 12; $i++) { 
				?>
				<option value="<?php echo $i; ?>"<?php echo $i == $mnum ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', JText::_('VRCONFIGMAXDATEMONTHS')) . ': ' . $i; ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<div class="btn-group pull-left">
			<select name="units_show_type" id="uleftorbooked" onchange="vrcUnitsLeftOrBooked();">
				<option value="units-booked"<?php echo (!empty($cookie_uleft) && $cookie_uleft == 'units-booked' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRCVERVIEWUBOOKEDFILT'); ?></option>
				<option value="units-left"<?php echo $show_type == 'units-left' || (!empty($cookie_uleft) && $cookie_uleft == 'units-left') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCVERVIEWULEFTFILT'); ?></option>
			</select>
		</div>
	<?php
	if (count($this->categories)) {
		$pcategory = $app->getUserStateFromRequest("vrc.overv.category", 'category', 0, 'int');
		?>
		<div class="btn-group pull-left">
			<select name="category" onchange="document.vroverview.submit();">
				<option value=""><?php echo JText::_('VRPVIEWCARTWO'); ?></option>
			<?php
			foreach ($this->categories as $category) {
				?>
				<option value="<?php echo $category['id']; ?>"<?php echo $pcategory == $category['id'] ? ' selected="selected"' : ''; ?>><?php echo $category['name']; ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<?php
	}
	if (is_array($all_locations)) {
		$loc_options = '<option value="">'.JText::_('VRCORDERSLOCFILTERANY').'</option>'."\n";
		foreach ($all_locations as $location) {
			$loc_options .= '<option value="'.$location['id'].'"'.($location['id'] == $plocation ? ' selected="selected"' : '').'>'.$location['name'].'</option>'."\n";
		}
		?>
		<div class="btn-group pull-right">
			<button type="submit" class="btn btn-secondary"><?php echo JText::_('VRCORDERSLOCFILTERBTN'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<select name="locationw" id="locwfilter">
				<option value="pickup"><?php echo JText::_('VRCORDERSLOCFILTERPICK'); ?></option>
				<option value="dropoff"<?php echo $plocationw == 'dropoff' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCORDERSLOCFILTERDROP'); ?></option>
				<option value="both"<?php echo $plocationw == 'both' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCORDERSLOCFILTERPICKDROP'); ?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			<label for="locfilter" style="display: inline-block; margin-right: 5px;"><?php echo JText::_('VRCORDERSLOCFILTER'); ?></label>
			<select name="location" id="locfilter"><?php echo $loc_options; ?></select>
		</div>
		<?php
	}
	?>
	</div>
</form>

<?php
$todayymd = date('Y-m-d');
$nowts = getdate($tsstart);
$curts = $nowts;
for ($mind = 1; $mind <= $mnum; $mind++) {
	$monthname = VikRentCar::sayMonth($curts['mon']);
?>
<table class="vrcoverviewtable vrc-overview-table">
	<tr class="vrcoverviewtablerow">
		<td class="bluedays vrcoverviewtdone"><strong><?php echo $monthname . " " . $curts['year']; ?></strong></td>
	<?php
	$moncurts = $curts;
	$mon = $moncurts['mon'];
	while ($moncurts['mon'] == $mon) {
		$curdayymd = date('Y-m-d', $moncurts[0]);
		$read_day  = $wdays_map[$moncurts['wday']] . ' ' . $moncurts['mday'] . ' ' . $monthname . ' ' . $curts['year'];
		echo '<td align="center" class="bluedays'.($todayymd == $curdayymd ? ' vrc-overv-todaycell' : '').'" data-ymd="'.$curdayymd.'" data-readymd="'.$read_day.'"><span class="vrc-overv-mday">'.$moncurts['mday'].'</span><span class="vrc-overv-wday">'.$wdays_map[$moncurts['wday']].'</td>';
		$moncurts = getdate(mktime(0, 0, 0, $moncurts['mon'], ($moncurts['mday'] + 1), $moncurts['year']));
	}
	?>
	</tr>
	<?php
	foreach ($rows as $car) {
		$moncurts = $curts;
		$mon = $moncurts['mon'];
		$is_subunit = (array_key_exists('unit_index', $car));
		echo '<tr class="vrcoverviewtablerow'.($is_subunit ? ' vrcoverviewtablerow-subunit' : '').'"'.($is_subunit ? ' data-subcarid="'.$car['id'].'-'.$car['unit_index'].'"' : '').'>'."\n";
		if ($is_subunit) {
			echo '<td class="carname subcarname" data-carid="-'.$car['id'].'"><span class="vrc-overview-subcarunits"><i class="'.VikRentCarIcons::i('car').'"></i></span><span class="vrc-overview-subcarname">'.$car['unit_index_str'].'</span></td>';
		} else {
			echo '<td class="carname" data-carid="'.$car['id'].'"><span class="vrc-overview-carunits">'.$car['units'].'</span><span class="vrc-overview-carname">'.$car['name'].'</span>'.(array_key_exists($car['id'], $cars_features_map) ? '<span class="vrc-overview-subcar-toggle"><i class="'.VikRentCarIcons::i('chevron-down', 'hasTooltip').'" style="margin: 0;" title="'.addslashes(JText::_('VRCOVERVIEWTOGGLESUBCAR')).'"></i></span>' : '').'</td>';
		}
		$car_bids_pool = array();
		while ($moncurts['mon'] == $mon) {
			$dclass = !array_key_exists('unit_index', $car) ? "notbusy" : "subnotbusy";
			$is_checkin = false;
			$lastbidcheckout = null;
			$dalt = "";
			$bid = "";
			$bids_pool = array();
			$totfound = 0;
			$cur_day_key = date('Y-m-d', $moncurts[0]);
			if (is_array($arrbusy[$car['id']]) && !array_key_exists('unit_index', $car)) {
				foreach ($arrbusy[$car['id']] as $b) {
					$tmpone = getdate($b['ritiro']);
					$ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
					$tmptwo = getdate($b['consegna']);
					$conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
					if ($moncurts[0] >= $ritts && $moncurts[0] <= $conts) {
						$dclass = "busy";
						$bid = $b['idorder'];
						if (!in_array($bid, $bids_pool)) {
							$bids_pool[] = '-'.$bid.'-';
						}
						if (array_key_exists($car['id'], $cars_features_map)) {
							if (!array_key_exists($cur_day_key, $car_bids_pool)) {
								$car_bids_pool[$cur_day_key] = array();
							}
							$car_bids_pool[$cur_day_key][] = (int)$bid;
						}
						if ($moncurts[0] == $ritts) {
							$dalt = JText::_('VRPICKUPAT')." ".date($nowtf, $b['ritiro']);
							$is_checkin = true;
							$lastbidcheckout = $b['consegna'];
							$bids_checkins[$bid] = $cur_day_key;
						} elseif ($moncurts[0] == $conts) {
							$dalt = JText::_('VRRELEASEAT')." ".date($nowtf, $b['consegna']);
						}
						$totfound += $b['stop_sales'] > 0 ? $car['units'] : 1;
					}
				}
			}
			$useday = ($moncurts['mday'] < 10 ? "0".$moncurts['mday'] : $moncurts['mday']);
			$dclass .= ($totfound < $car['units'] && $totfound > 0 ? ' vrc-partially' : '');
			$write_units = $show_type == 'units-left' || (!empty($cookie_uleft) && $cookie_uleft == 'units-left') ? ($car['units'] - $totfound) : $totfound;
			if (array_key_exists('unit_index', $car) && array_key_exists($car['id'], $cars_features_bookings) && array_key_exists($cur_day_key, $cars_bids_pools[$car['id']]) && array_key_exists($car['unit_index'], $cars_features_bookings[$car['id']])) {
				foreach ($cars_bids_pools[$car['id']][$cur_day_key] as $bid) {
					$bid = intval(str_replace('-', '', $bid));
					if (in_array($bid, $cars_features_bookings[$car['id']][$car['unit_index']])) {
						$car['units'] = 1;
						$totfound = 1;
						$dclass = "subcar-busy";
						$is_checkin = isset($bids_checkins[$bid]) && $bids_checkins[$bid] == $cur_day_key ? true : $is_checkin;
						break;
					}
				}
			}
			// check today's date
			$curdayymd = date('Y-m-d', $moncurts[0]);
			if ($todayymd == $curdayymd) {
				$dclass .= ' vrc-overv-todaycell';
			}
			//

			/**
			 * Critical dates defined at car-day level.
			 * 
			 * @since 	1.2.0
			 */
			$cdaynote_keyid = $cur_day_key . '_' . $car['id'] . '_' . (isset($car['unit_index']) ? $car['unit_index'] : '0');
			if (isset($this->cdaynotes[$cdaynote_keyid])) {
				// note exists for this combination of date, car ID and subunit
				$dclass .= ' vrc-cardaynote-full';
				$cdaynote_icn = 'sticky-note';
			} else {
				// no notes for this cell
				$dclass .= ' vrc-cardaynote-empty';
				$cdaynote_icn = 'far fa-sticky-note';
			}
			$critical_note = '<span class="vrc-cardaynote-trigger" data-carday="' . $cdaynote_keyid . '"><i class="' . VikRentCarIcons::i($cdaynote_icn, 'vrc-cardaynote-display') . '"></i></span>';
			//

			if ($totfound == 1) {
				$write_units = strpos($dclass, "subcar-busy") !== false ? '&bull;' : $write_units;
				$dclass .= $is_checkin === true ? ' vrc-checkinday' : '';
				$dlnk = "<a href=\"index.php?option=com_vikrentcar&task=editbusy&goto=overv&cid[]=".$bid."\" class=\"".(strpos($dclass, "subcar-busy") === false ? 'vrc-overview-redday' : 'vrc-overview-subredday')."\" style=\"color: #ffffff;\" data-units-booked=\"".$totfound."\" data-units-left=\"".($car['units'] - $totfound)."\">".$write_units."</a>";
				$cal = "<td align=\"center\" class=\"".$dclass."\"".(!empty($dalt) ? " title=\"".$dalt."\"" : "")." data-day=\"".$cur_day_key."\" data-bids=\"".(strpos($dclass, "subcar-busy") !== false ? '-'.$bid.'-' : implode(',', $bids_pool))."\">" . $dlnk . $critical_note . "</td>\n";
			} elseif ($totfound > 1) {
				$dlnk = "<a href=\"index.php?option=com_vikrentcar&task=choosebusy&goto=overv&idcar=".$car['id']."&ts=".$moncurts[0]."\" class=\"vrc-overview-redday\" style=\"color: #ffffff;\" data-units-booked=\"".$totfound."\" data-units-left=\"".($car['units'] - $totfound)."\">".$write_units."</a>";
				$cal = "<td align=\"center\" class=\"".$dclass."\" data-day=\"".$cur_day_key."\" data-bids=\"".implode(',', $bids_pool)."\">" . $dlnk . $critical_note . "</td>\n";
			} else {
				$dlnk = $useday;
				$cal = "<td align=\"center\" class=\"".$dclass."\" data-day=\"".$cur_day_key."\" data-bids=\"\">{$critical_note}</td>\n";
			}
			echo $cal;
			$moncurts = getdate(mktime(0, 0, 0, $moncurts['mon'], ($moncurts['mday'] + 1), $moncurts['year']));
		}
		if (array_key_exists($car['id'], $cars_features_map) && !array_key_exists('unit_index', $car) && count($car_bids_pool) > 0) {
			// load bookings for distinctive features when parsing the parent $car array
			$car_indexes_bids = VikRentCar::loadCarIndexesOrders($car['id'], $car_bids_pool);
			if (count($car_indexes_bids) > 0) {
				$cars_features_bookings[$car['id']] = $car_indexes_bids;
				$cars_bids_pools[$car['id']] = $car_bids_pool;
			}
			//
		}
		echo '</tr>';
	}
	?>
</table>
	<?php
	echo ($mind + 1) <= $mnum ? '<br/>' : '';
	$curts = getdate(mktime(0, 0, 0, ($nowts['mon'] + $mind), $nowts['mday'], $nowts['year']));
}
?>

<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="overv" />
	<input type="hidden" name="month" value="<?php echo JHtml::_('esc_attr', $tsstart); ?>" />
	<input type="hidden" name="mnum" value="<?php echo JHtml::_('esc_attr', $mnum); ?>" />
	<?php echo '<br/>'.$navbut; ?>
</form>

<script type="text/javascript">
var hovtimer;
var hovtip = false;
var vrcdialogcdaynotes_on = false;
var vrcMessages = {
	"loadingTip": "<?php echo addslashes(JText::_('VIKLOADING')); ?>",
	"numDays": "<?php echo addslashes(JText::_('VRDAYS')); ?>",
	"pickupLbl": "<?php echo addslashes(JText::_('VRPICKUPAT')); ?>",
	"dropoffLbl": "<?php echo addslashes(JText::_('VRRELEASEAT')); ?>",
	"totalAmount": "<?php echo addslashes(JText::_('VREDITORDERNINE')); ?>",
	"totalPaid": "<?php echo addslashes(JText::_('VRCEXPCSVTOTPAID')); ?>",
	"currencySymb": "<?php echo $currencysymb; ?>"
};

if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
} else {
	jQuery.fn.tooltip = function(){};
}

function vrcUnitsLeftOrBooked() {
	var set_to = jQuery('#uleftorbooked').val();
	if (jQuery('.vrc-overview-redday').length) {
		jQuery('.vrc-overview-redday').each(function(){
			jQuery(this).text(jQuery(this).attr('data-'+set_to));
		});
	}
	var nd = new Date();
	nd.setTime(nd.getTime() + (365*24*60*60*1000));
	document.cookie = "vrcAovwUleft="+set_to+"; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
}

var vrcCdayNotes = <?php echo json_encode($this->cdaynotes); ?>;

/* Hover Tooltip functions */
function registerHoveringTooltip(that) {
	if (hovtip) {
		return false;
	}
	if (hovtimer) {
		clearTimeout(hovtimer);
		hovtimer = null;
	}
	var elem = jQuery(that);
	var cellheight = elem.outerHeight();
	var celldata = new Array();
	if (elem.hasClass('subcar-busy')) {
		celldata.push(elem.parent('tr').attr('data-subcarid'));
		celldata.push(elem.attr('data-day'));
	}
	hovtimer = setTimeout(function() {
		hovtip = true;
		jQuery(
			"<div class=\"vrc-overview-tipblock\">"+
				"<div class=\"vrc-overview-tipinner\"><span class=\"vrc-overview-tiploading\">"+vrcMessages.loadingTip+"</span></div>"+
			"</div>"
		).appendTo(elem);
		jQuery(".vrc-overview-tipblock").css("bottom", "+="+cellheight);
		loadTooltipBookings(elem.attr('data-bids'), celldata);
	}, 900);
}
function unregisterHoveringTooltip() {
	clearTimeout(hovtimer);
	hovtimer = null;
}
function adjustHoveringTooltip() {
	setTimeout(function() {
		var difflim = 35;
		var otop = jQuery(".vrc-overview-tipblock").offset().top;
		if (otop < difflim) {
			jQuery(".vrc-overview-tipblock").css("bottom", "-="+(difflim - otop));
		}
	}, 100);
}
function hideVrcTooltip() {
	jQuery('.vrc-overview-tipblock').remove();
	hovtip = false;
}
function loadTooltipBookings(bids, celldata) {
	if (!bids || bids === undefined || !bids.length) {
		hideVrcTooltip();
		return false;
	}
	var subcardata = celldata.length ? celldata[0] : '';
	//ajax request
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { option: "com_vikrentcar", task: "getordersinfo", tmpl: "component", idorders: bids, subcar: subcardata }
	}).done(function(res) {
		if (res.indexOf('e4j.error') >= 0 ) {
			console.log(res);
			alert(res.replace("e4j.error.", ""));
			//restore
			hideVrcTooltip();
			//
		} else {
			var obj_res = JSON.parse(res);
			jQuery('.vrc-overview-tiploading').remove();
			var container = jQuery('.vrc-overview-tipinner');
			jQuery(obj_res).each(function(k, v) {
				var bcont = "<div class=\"vrc-overview-tip-bookingcont\">";
				bcont += "<div class=\"vrc-overview-tip-bookingcont-left\">";
				bcont += "<div class=\"vrc-overview-tip-bid\"><span class=\"vrc-overview-tip-lbl\"><?php echo addslashes(JText::_('VRCDASHUPRESONE')); ?> <span class=\"vrc-overview-tip-lbl-innerleft\"><a href=\"index.php?option=com_vikrentcar&task=editbusy&goto=overv&cid[]="+v.id+"\"><i class=\"<?php echo VikRentCarIcons::i('edit'); ?>\"></i></a></span></span><span class=\"vrc-overview-tip-cnt\">"+v.id+"</span></div>";
				bcont += "<div class=\"vrc-overview-tip-bstatus\"><span class=\"vrc-overview-tip-lbl\"><?php echo addslashes(JText::_('VRPVIEWORDERSEIGHT')); ?></span><span class=\"vrc-overview-tip-cnt\"><div class=\"label "+(v.status == 'confirmed' ? 'label-success' : 'label-warning')+"\">"+v.status_lbl+"</div></span></div>";
				bcont += "<div class=\"vrc-overview-tip-bdate\"><span class=\"vrc-overview-tip-lbl\"><?php echo addslashes(JText::_('VRPVIEWORDERSONE')); ?></span><span class=\"vrc-overview-tip-cnt\"><a href=\"index.php?option=com_vikrentcar&task=editorder&goto=overv&cid[]="+v.id+"\">"+v.ts+"</a></span></div>";
				bcont += "</div>";
				bcont += "<div class=\"vrc-overview-tip-bookingcont-right\">";
				bcont += "<div class=\"vrc-overview-tip-bcustomer\"><span class=\"vrc-overview-tip-lbl\"><?php echo addslashes(JText::_('VRPVIEWORDERSTWO')); ?></span><span class=\"vrc-overview-tip-cnt\">"+v.cinfo+"</span></div>";
				bcont += "<div class=\"vrc-overview-tip-bguests\"><span class=\"vrc-overview-tip-lbl\">"+vrcMessages.numDays+"</span><span class=\"vrc-overview-tip-cnt hasTooltip\" title=\""+vrcMessages.pickupLbl+" "+v.pickup+" - "+vrcMessages.dropoffLbl+" "+v.dropoff+"\">" + v.days + (v.pickup_place !== null && v.pickup_place.length ? ", " + v.pickup_place + (v.dropoff_place !== null && v.dropoff_place.length && v.dropoff_place != v.pickup_place ? " - " + v.dropoff_place : "") : "") + "</span></div>";
				if (v.hasOwnProperty('cindexes')) {
					for (var cindexk in v.cindexes) {
						if (v.cindexes.hasOwnProperty(cindexk)) {
							bcont += "<div class=\"vrc-overview-tip-bcindexes\"><span class=\"vrc-overview-tip-lbl\">"+cindexk+"</span><span class=\"vrc-overview-tip-cnt\">"+v.cindexes[cindexk]+"</span></div>";
						}
					}
				}
				bcont += "<div class=\"vrc-overview-tip-pickdt\"><span class=\"vrc-overview-tip-lbl\"><?php echo addslashes(JText::_('VRPVIEWORDERSFOUR')); ?></span><span class=\"vrc-overview-tip-cnt\">"+v.pickup+"</span></div>";
				bcont += "<div class=\"vrc-overview-tip-dropdt\"><span class=\"vrc-overview-tip-lbl\"><?php echo addslashes(JText::_('VRPVIEWORDERSFIVE')); ?></span><span class=\"vrc-overview-tip-cnt\">"+v.dropoff+"</span></div>";
				bcont += "<div class=\"vrc-overview-tip-bookingcont-total\">";
				bcont += "<div class=\"vrc-overview-tip-btot\"><span class=\"vrc-overview-tip-lbl\">"+vrcMessages.totalAmount+"</span><span class=\"vrc-overview-tip-cnt\">"+vrcMessages.currencySymb+" "+v.format_tot+"</span></div>";
				if (v.totpaid > 0.00) {
					bcont += "<div class=\"vrc-overview-tip-btot\"><span class=\"vrc-overview-tip-lbl\">"+vrcMessages.totalPaid+"</span><span class=\"vrc-overview-tip-cnt\">"+vrcMessages.currencySymb+" "+v.format_totpaid+"</span></div>";
				}
				var getnotes = v.adminnotes;
				if (getnotes !== null && getnotes.length) {
					bcont += "<div class=\"vrc-overview-tip-notes\"><span class=\"vrc-overview-tip-lbl\"><span class=\"vrc-overview-tip-notes-inner\"><i class=\"vrcicn-info hasTooltip\" title=\""+getnotes+"\"></i></span></span></div>";
				}
				bcont += "</div>";
				bcont += "</div>";
				bcont += "</div>";
				container.append(bcont);
			});
			// adjust the position so that it won't go under other contents
			adjustHoveringTooltip()
			//
			jQuery(".hasTooltip").tooltip();
		}
	}).fail(function() { 
		console.error('Request Failed');
		//restore
		hideVrcTooltip();
		//
	});
	//
}

/**
 * Car-day-notes dialog
 */
function hideVrcDialogCdaynotes() {
	if (vrcdialogcdaynotes_on === true) {
		jQuery(".vrc-modal-overlay-block-cardaynotes").fadeOut(400, function () {
			jQuery(".vrc-modal-overlay-content-cardaynotes").show();
		});
		// reset values
		jQuery('#vrc-newcdnote-name').val('');
		jQuery('#vrc-newcdnote-descr').val('');
		jQuery('#vrc-newcdnote-cdays').val('0').trigger('change');
		// turn flag off
		vrcdialogcdaynotes_on = false;
	}
}

jQuery(document).ready(function() {
	/**
	 * Render the units view mode
	 */
	vrcUnitsLeftOrBooked();

	/* Toggle Sub-units Start */
	jQuery(".vrc-overview-subcar-toggle").click(function() {
		var carid = jQuery(this).parent("td").attr("data-carid");
		if (jQuery(this).hasClass("vrc-overview-subcar-toggle-active")) {
			jQuery("td.carname[data-carid='"+carid+"']").find("span.vrc-overview-subcar-toggle").removeClass("vrc-overview-subcar-toggle-active").find("i.fa, i.fas").removeClass("fa-chevron-up").addClass("fa-chevron-down");
			jQuery("td.subcarname[data-carid='-"+carid+"']").parent("tr").hide();
		} else {
			jQuery("td.carname[data-carid='"+carid+"']").find("span.vrc-overview-subcar-toggle").addClass("vrc-overview-subcar-toggle-active").find("i.fa, i.fas").removeClass("fa-chevron-down").addClass("fa-chevron-up");
			jQuery("td.subcarname[data-carid='-"+carid+"']").parent("tr").show();
		}
	});
	/* Toggle Sub-units End */

	/* Hover Tooltip Start */
	jQuery('td.busy, td.busytmplock, td.subcar-busy').hover(function() {
		registerHoveringTooltip(this);
	}, unregisterHoveringTooltip);
	jQuery(document).keydown(function(e) {
		if (e.keyCode == 27) {
			if (hovtip === true) {
				hideVrcTooltip();
			}
			if (vrcdialogcdaynotes_on === true) {
				hideVrcDialogCdaynotes();
			}
		}
	});
	jQuery(document).mouseup(function(e) {
		if (!hovtip && !vrcdialogcdaynotes_on) {
			return false;
		}
		if (hovtip) {
			var vrc_overlay_cont = jQuery(".vrc-overview-tipblock");
			if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
				hideVrcTooltip();
				return true;
			}
		}
		if (vrcdialogcdaynotes_on) {
			var vrc_overlay_cont = jQuery(".vrc-modal-overlay-content-cardaynotes");
			if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
				hideVrcDialogCdaynotes();
			}
		}
	});
	/* Hover Tooltip End */

	// car-day notes
	jQuery(document.body).on("click", ".vrc-cardaynote-display", function() {
		if (!jQuery(this).closest('.vrc-cardaynote-trigger').length) {
			return;
		}
		var daytitle = new Array;
		var carday_info = jQuery(this).closest('.vrc-cardaynote-trigger').attr('data-carday').split('_');
		// readable day
		var readymd = carday_info[0];
		if (jQuery('.bluedays[data-ymd="' + carday_info[0] + '"]').length) {
			readymd = jQuery('.bluedays[data-ymd="' + carday_info[0] + '"]').attr('data-readymd');
		}
		daytitle.push(readymd);
		// car name
		if (jQuery('.carname[data-carid="' + carday_info[1] + '"]').length) {
			daytitle.push(jQuery('.carname[data-carid="' + carday_info[1] + '"]').first().find('.vrc-overview-carname').text());
		}
		//
		// sub-unit
		if (parseInt(carday_info[2]) > 0 && jQuery('.subcarname[data-carid="-' + carday_info[1] + '"]').length) {
			daytitle.push(jQuery('.subcarname[data-carid="-' + carday_info[1] + '"]').find('.vrc-overview-subcarname').eq((parseInt(carday_info[2]) - 1)).text());
		}
		//
		// set day title
		jQuery('.vrc-modal-overlay-content-head-cardaynotes').find('h3').find('span.vrc-modal-cardaynotes-dt').text(daytitle.join(', '));
		// populate current car day notes
		vrcRenderCdayNotes(carday_info[0], carday_info[1], carday_info[2], readymd);
		// display modal
		jQuery('.vrc-modal-overlay-block-cardaynotes').fadeIn();
		vrcdialogcdaynotes_on = true;
		//
	});
});

/**
 * Car-day notes
 */
var cdaynote_icn_full = '<?php echo VikRentCarIcons::i('sticky-note', 'vrc-cardaynote-display'); ?>';
var cdaynote_icn_empty = '<?php echo VikRentCarIcons::i('far fa-sticky-note', 'vrc-cardaynote-display'); ?>';
function vrcRenderCdayNotes(day, idcar, subunit, readymd) {
	// compose fests information
	var notes_html = '';
	var keyid = day + '_' + idcar + '_' + subunit;
	if (vrcCdayNotes.hasOwnProperty(keyid) && vrcCdayNotes[keyid]['info'] && vrcCdayNotes[keyid]['info'].length) {
		for (var i = 0; i < vrcCdayNotes[keyid]['info'].length; i++) {
			var note_data = vrcCdayNotes[keyid]['info'][i];
			
			var note_descr = note_data['descr'].replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br />' + '$2');
			note_descr = note_descr.replace(/(#\w+)/ug, '<span class="vrc-reminder-hashtag">' + '$1' + '</span>');

			notes_html += '<div class="vrc-overlay-fest-details vrc-modal-cardaynotes-note-details">';
			notes_html += '	<div class="vrc-fest-info vrc-modal-cardaynotes-note-info">';
			notes_html += '		<div class="vrc-fest-name vrc-modal-cardaynotes-note-name">' + note_data['name'] + '</div>';
			notes_html += '		<div class="vrc-fest-desc vrc-modal-cardaynotes-note-desc">' + note_descr + '</div>';
			notes_html += '	</div>';
			notes_html += '	<div class="vrc-fest-cmds vrc-modal-cardaynotes-note-cmds">';
			notes_html += '		<button type="button" class="btn btn-danger" onclick="vrcRemoveCdayNote(\'' + i + '\', \'' + day + '\', \'' + idcar + '\', \'' + subunit + '\', \'' + note_data['type'] + '\', this);"><?php VikRentCarIcons::e('trash-alt'); ?></button>';
			notes_html += '	</div>';
			notes_html += '</div>';
		}
	}
	// update attributes keys for the selected date, useful for adding new notes
	jQuery('.vrc-modal-cardaynotes-addnew').attr('data-ymd', day).attr('data-carid', idcar).attr('data-subcarid', subunit);
	if (readymd !== null) {
		jQuery('.vrc-modal-cardaynotes-addnew').attr('data-readymd', readymd);
		jQuery('.vrc-newcdnote-dayto-val').text(readymd);
	}
	// set content and display modal
	jQuery('.vrc-modal-cardaynotes-list').html(notes_html);
}
function vrcAddCarDayNote(that) {
	var mainelem = jQuery(that).closest('.vrc-modal-cardaynotes-addnew');
	var ymd = mainelem.attr('data-ymd');
	var carid = mainelem.attr('data-carid');
	var subcarid = mainelem.attr('data-subcarid');
	var note_name = jQuery('#vrc-newcdnote-name').val();
	var note_descr = jQuery('#vrc-newcdnote-descr').val();
	var note_cdays = jQuery('#vrc-newcdnote-cdays').val();
	if (!note_name.length && !note_descr.length) {
		alert('Missing required fields');
		return false;
	}
	// make the AJAX request to the controller to add this note to the DB
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { option: "com_vikrentcar", task: "add_cardaynote", tmpl: "component", dt: ymd, idcar: carid, subunit: subcarid, type: "custom", name: note_name, descr: note_descr, cdays: note_cdays }
	}).done(function(res) {
		// parse the JSON response that contains the note object for the passed date
		try {
			var stored_notes = JSON.parse(res);
			for (var keyid in stored_notes) {
				if (!stored_notes.hasOwnProperty(keyid)) {
					continue;
				}
				if (!vrcCdayNotes.hasOwnProperty(keyid) && jQuery('.vrc-cardaynote-trigger[data-carday="' + keyid + '"]').length) {
					// we need to add the proper class to the cell for this note (if it's visible)
					jQuery('.vrc-cardaynote-trigger[data-carday="' + keyid + '"]').parent('td').removeClass('vrc-cardaynote-empty').addClass('vrc-cardaynote-full').find('i').attr('class', cdaynote_icn_full);
				}
				// update global object with the new notes in any case
				vrcCdayNotes[keyid] = stored_notes[keyid];
			}
			// close modal
			hideVrcDialogCdaynotes();
			// reset input fields
			jQuery('#vrc-newcdnote-name').val('');
			jQuery('#vrc-newcdnote-descr').val('');
			jQuery('#vrc-newcdnote-cdays').val('0').trigger('change');
		} catch (e) {
			console.log(res);
			alert('Invalid response');
			return false;
		}
	}).fail(function() {
		alert('Request failed');
	});
}
function vrcRemoveCdayNote(index, day, idcar, subunit, note_type, that) {
	if (!confirm('<?php echo addslashes(JText::_('VRCDELCONFIRM')); ?>')) {
		return false;
	}
	var elem = jQuery(that);
	// make the AJAX request to the controller to remove this note from the DB
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: {
			option: "com_vikrentcar",
			task: "remove_cardaynote",
			tmpl: "component",
			dt: day,
			idcar: idcar,
			subunit: subunit,
			ind: index,
			type: note_type
		}
	}).done(function(res) {
		if (res.indexOf('e4j.ok') >= 0) {
			var keyid = day + '_' + idcar + '_' + subunit;
			// delete note also from the json-decode array of objects
			if (vrcCdayNotes[keyid] && vrcCdayNotes[keyid]['info']) {
				// use splice to remove the desired index from array, or delete would not make the length of the array change
				vrcCdayNotes[keyid]['info'].splice(index, 1);
				// re-build indexes of delete buttons, fundamental for removing the right index at next click
				vrcRenderCdayNotes(day, idcar, subunit, null);
				if (!vrcCdayNotes[keyid]['info'].length) {
					// delete also this date object from notes
					delete vrcCdayNotes[keyid];
					// no more notes, update the proper class attribute for this cell (should be visible)
					if (jQuery('.vrc-cardaynote-trigger[data-carday="' + keyid + '"]').length) {
						jQuery('.vrc-cardaynote-trigger[data-carday="' + keyid + '"]').parent('td').removeClass('vrc-cardaynote-full').addClass('vrc-cardaynote-empty').find('i').attr('class', cdaynote_icn_empty);
					}
				}
			}
			elem.closest('.vrc-modal-cardaynotes-note-details').remove();
		} else {
			console.log(res);
			alert('Invalid response');
		}
	}).fail(function() {
		alert('Request failed');
	});
}
function vrcCdayNoteCdaysCount() {
	var cdays = parseInt(jQuery('#vrc-newcdnote-cdays').val());
	var defymd = jQuery('.vrc-modal-cardaynotes-addnew').attr('data-ymd');
	var defreadymd = jQuery('.vrc-modal-cardaynotes-addnew').attr('data-readymd');
	defreadymd = !defreadymd || !defreadymd.length ? defymd : defreadymd;
	if (isNaN(cdays) || cdays < 1) {
		jQuery('.vrc-newcdnote-dayto-val').text(defreadymd);
		return;
	}
	// calculate target (until) date
	var targetdate = new Date(defymd);
	targetdate.setDate(targetdate.getDate() + cdays);
	var target_y = targetdate.getFullYear();
	var target_m = targetdate.getMonth() + 1;
	target_m = target_m < 10 ? '0' + target_m : target_m;
	var target_d = targetdate.getDate();
	target_d = target_d < 10 ? '0' + target_d : target_d;
	// display target date
	var display_target = target_y + '-' + target_m + '-' + target_d;
	// check if we can get the "read ymd property"
	if (jQuery('.bluedays[data-ymd="' + display_target + '"]').length) {
		display_target = jQuery('.bluedays[data-ymd="' + display_target + '"]').attr('data-readymd');
	}
	jQuery('.vrc-newcdnote-dayto-val').text(display_target);
}
</script>

<div class="vrc-modal-overlay-block vrc-modal-overlay-block-cardaynotes">
	<a class="vrc-modal-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-modal-overlay-content vrc-modal-overlay-content-cardaynotes">
		<div class="vrc-modal-overlay-content-head vrc-modal-overlay-content-head-cardaynotes">
			<h3>
				<?php VikRentCarIcons::e('exclamation-circle'); ?> 
				<span class="vrc-modal-cardaynotes-dt"></span>
				<span class="vrc-modal-overlay-close-times" onclick="hideVrcDialogCdaynotes();">&times;</span>
			</h3>
		</div>
		<div class="vrc-modal-overlay-content-body">
			<div class="vrc-modal-cardaynotes-list"></div>
			<div class="vrc-modal-cardaynotes-addnew" data-readymd="" data-ymd="" data-carid="" data-subcarid="">
				<h4><?php echo JText::_('VRCADDCUSTOMFESTTODAY'); ?></h4>
				<div class="vrc-modal-cardaynotes-addnew-elem">
					<label for="vrc-newcdnote-name"><?php echo JText::_('VRPVIEWPLACESONE'); ?></label>
					<input type="text" id="vrc-newcdnote-name" value="" />
				</div>
				<div class="vrc-modal-cardaynotes-addnew-elem">
					<label for="vrc-newcdnote-descr"><?php echo JText::_('VRCPLACEDESCR'); ?></label>
					<textarea id="vrc-newcdnote-descr"></textarea>
					<span class="vrc-param-setting-comment vrc-suggestion-hashtags"><?php echo JText::_('VRC_CDAYNOTES_HASHTAGS_HELP'); ?></span>
				</div>
				<div class="vrc-modal-cardaynotes-addnew-elem">
					<label for="vrc-newcdnote-cdays"><?php echo JText::_('VRCCONSECUTIVEDAYS'); ?></label>
					<input type="number" id="vrc-newcdnote-cdays" min="0" max="365" value="0" onchange="vrcCdayNoteCdaysCount();" onkeyup="vrcCdayNoteCdaysCount();" />
					<span class="vrc-newcdnote-dayto">
						<span class="vrc-newcdnote-dayto-lbl"><?php echo JText::_('VRCUNTIL'); ?></span>
						<span class="vrc-newcdnote-dayto-val"></span>
					</span>
				</div>
				<div class="vrc-modal-cardaynotes-addnew-save">
					<button type="button" class="btn btn-success" onclick="vrcAddCarDayNote(this);"><?php echo JText::_('VRSAVE'); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>
