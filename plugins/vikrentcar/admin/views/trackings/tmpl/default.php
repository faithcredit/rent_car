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
$orderby = $this->orderby;
$ordersort = $this->ordersort;

$vrc_app = new VrcApplication();
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery-ui.min.css');
JHtml::_('jquery.framework', true, true);
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.min.js');
$nowtf = VikRentCar::getTimeFormat(true);
$nowdf = VikRentCar::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$juidf = $nowdf == "%d/%m/%Y" ? 'dd/mm/yy' : ($nowdf == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');
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

$dates_filter = '';
$pdatefilt = VikRequest::getInt('datefilt', '', 'request');
$pdatefiltfrom = VikRequest::getString('datefiltfrom', '', 'request');
$pdatefiltto = VikRequest::getString('datefiltto', '', 'request');
if ((!empty($pdatefiltfrom) || !empty($pdatefiltto))) {
	$dates_filter = '&amp;datefilt='.$pdatefilt.(!empty($pdatefiltfrom) ? '&amp;datefiltfrom='.$pdatefiltfrom : '').(!empty($pdatefiltto) ? '&amp;datefiltto='.$pdatefiltto : '');
}
$pactive_tab = VikRequest::getString('vrc_active_tab', 'vrc-trackings-tabcont-list', 'request');

?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.vrc-trackings-togglesubrow-cont').click(function() {
		var toggler = jQuery(this).find('i.vrc-trackings-togglesubrow');
		var elem = toggler.closest('.vrc-trackings-table-body-row').find('.vrc-trackings-table-body-subrow');
		elem.slideToggle(400, function() {
			if (elem.is(':visible')) {
				toggler.removeClass('fa-chevron-down').addClass('fa-chevron-up');
			} else {
				toggler.removeClass('fa-chevron-up').addClass('fa-chevron-down');
			}
		});
	});
	jQuery('.vrc-trackings-table-body-row').dblclick(function() {
		if (jQuery(this).find('.vrc-trackings-table-body-subrow').is(':visible')) {
			e.preventDefault();
			return;
		}
		jQuery(this).find('.vrc-trackings-togglesubrow-cont').trigger('click');
	});
	jQuery('#vrc-date-from').datepicker({
		showOn: 'focus',
		dateFormat: '<?php echo $juidf; ?>',
		<?php echo ($this->mindate > 0 ? 'minDate: "'.date(str_replace('%', '', $nowdf), $this->mindate).'", ' : '').($this->maxdate > 0 ? 'maxDate: "'.date(str_replace('%', '', $nowdf), $this->maxdate).'", ' : ''); ?>
		onSelect: function( selectedDate ) {
			jQuery('#vrc-date-to').datepicker('option', 'minDate', selectedDate);
		}
	});
	jQuery('#vrc-date-to').datepicker({
		showOn: 'focus',
		dateFormat: '<?php echo $juidf; ?>',
		<?php echo ($this->mindate > 0 ? 'minDate: "'.date(str_replace('%', '', $nowdf), $this->mindate).'", ' : '').($this->maxdate > 0 ? 'maxDate: "'.date(str_replace('%', '', $nowdf), $this->maxdate).'", ' : ''); ?>
		onSelect: function( selectedDate ) {
			jQuery('#vrc-date-from').datepicker('option', 'maxDate', selectedDate);
		}
	});
	jQuery('#vrc-date-from-trig, #vrc-date-to-trig').click(function() {
		var jdp = jQuery(this).prev('input.hasDatepicker');
		if (jdp.length) {
			jdp.focus();
		}
	});
	jQuery('.vrc-trackings-tab').click(function() {
		var newtabrel = jQuery(this).attr('data-vrctab');
		var oldtabrel = jQuery(".vrc-trackings-tab-active").attr('data-vrctab');
		if (newtabrel == oldtabrel) {
			return;
		}
		jQuery(".vrc-trackings-tab").removeClass("vrc-trackings-tab-active");
		jQuery(this).addClass("vrc-trackings-tab-active");
		jQuery("." + oldtabrel).hide();
		jQuery("." + newtabrel).fadeIn();
		jQuery("#vrc_active_tab").val(newtabrel);
	});
	jQuery(".vrc-trackings-tab[data-vrctab='<?php echo $pactive_tab; ?>']").trigger('click');
});
</script>

<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">

	<div id="filter-bar" class="btn-toolbar vrc-btn-toolbar vrc-trackings-filters" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-right">
			<a class="btn" href="index.php?option=com_vikrentcar&task=trkconfig"><?php VikRentCarIcons::e('cogs'); ?> <?php echo JText::_('VRCTRKSETTINGS'); ?></a>
		</div>
		<div class="btn-group pull-left input-append">
			<input type="text" id="vrc-date-from" placeholder="<?php echo JHtml::_('esc_attr', JText::_('VRNEWSEASONONE')); ?>" value="<?php echo JHtml::_('esc_attr', $pdatefiltfrom); ?>" size="14" name="datefiltfrom" onfocus="this.blur();" />
			<button type="button" class="btn" id="vrc-date-from-trig"><i class="icon-calendar"></i></button>
		</div>
		<div class="btn-group pull-left input-append">
			<input type="text" id="vrc-date-to" placeholder="<?php echo JHtml::_('esc_attr', JText::_('VRNEWSEASONTWO')); ?>" value="<?php echo JHtml::_('esc_attr', $pdatefiltto); ?>" size="14" name="datefiltto" onfocus="this.blur();" />
			<button type="button" class="btn" id="vrc-date-to-trig"><i class="icon-calendar"></i></button>
		</div>
		<div class="btn-group pull-left">
		<?php
		$datesel = '<select name="datefilt">';
		$datesel .= '<option value="1"'.(!empty($pdatefilt) && $pdatefilt == 1 ? ' selected="selected"' : '').'>'.JText::_('VRCTRKFILTTRKDATES').'</option>';
		$datesel .= '<option value="2"'.(!empty($pdatefilt) && $pdatefilt == 2 ? ' selected="selected"' : '').'>'.JText::_('VRCTRKBOOKINGDATES').'</option>';
		$datesel .= '<option value="3"'.(!empty($pdatefilt) && $pdatefilt == 3 ? ' selected="selected"' : '').'>'.JText::_('VRCFILTERDATEIN').'</option>';
		$datesel .= '<option value="4"'.(!empty($pdatefilt) && $pdatefilt == 4 ? ' selected="selected"' : '').'>'.JText::_('VRCFILTERDATEOUT').'</option>';
		$datesel .= '</select>';
		echo $datesel;
		?>
		</div>
		<div class="btn-group pull-left">
			<span style="font-size: 15px;">&nbsp;</span>
		</div>
		<div class="btn-group pull-left">
			<select name="countryfilt" id="countryfilt">
				<option value=""><?php echo JText::_('VRCCOUNTRYFILTER'); ?></option>
			<?php
			$pcountryfilt = VikRequest::getString('countryfilt', '', 'request');
			foreach ($this->countries as $c) {
				?>
				<option value="<?php echo JHtml::_('esc_attr', $c['country']); ?>"<?php echo $c['country'] == $pcountryfilt ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $c['country_name']); ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<div class="btn-group pull-left">
			<span style="font-size: 15px;">&nbsp;</span>
		</div>
		<div class="btn-group pull-left">
			<select name="referrer" style="max-width: 170px;">
				<option value=""><?php echo JText::_('VRCREFERRERFILTER'); ?></option>
			<?php
			$preferrer = VikRequest::getString('referrer', '', 'request');
			foreach ($this->referrers as $r) {
				?>
				<option value="<?php echo JHtml::_('esc_attr', $r['referrer']); ?>"<?php echo $r['referrer'] == $preferrer ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $r['referrer']); ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<div class="btn-group pull-left">
			<span style="font-size: 15px;">&nbsp;</span>
		</div>
		<div class="btn-group pull-left">
			<button type="submit" class="btn"><i class="icon-search"></i> <?php echo JText::_('VRCTRKFILTRES'); ?></button>
		</div>
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="jQuery('#filter-bar').find('input, select').val('');document.adminForm.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
	</div>

<?php
if (!(int)VikRentCarTracker::loadSettings('trkenabled')) {
	?>
	<p class="err"><?php echo JText::_('VRCTRKDISABLED'); ?></p>
	<?php
}
if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VRCNOTRACKINGS'); ?></p>
	<?php
} else {
	// gather all the IPs with missing geo information
	$missing_ips = array();
	foreach ($rows as $row) {
		if (empty($row['geo']) && !empty($row['ip'])) {
			$missing_ips[$row['id']] = $row['ip'];
		}
	}
	if (count($missing_ips)) {
		?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikrentcar", task: "getgeoinfo", tmpl: "component", ips: <?php echo json_encode($missing_ips); ?> }
		}).done(function(res) {
			if (res.indexOf('e4j.error') >= 0 ) {
				console.log(res);
			} else {
				var obj_res = JSON.parse(res);
				for (var i in obj_res) {
					if (!obj_res.hasOwnProperty(i)) {
						continue;
					}
					if (obj_res[i].hasOwnProperty('geo') && jQuery('#geo-'+i).length) {
						jQuery('#geo-'+i).text(obj_res[i]['geo']);
					}
					if (obj_res[i].hasOwnProperty('country') && jQuery('#country-'+i).length) {
						jQuery('#country-'+i).text(obj_res[i]['country']);
					}
					if (obj_res[i].hasOwnProperty('country') && obj_res[i].hasOwnProperty('country3') && !vrcCountryHasVal(obj_res[i]['country3'])) {
						jQuery('#countryfilt').append('<option value="'+obj_res[i]['country3']+'">'+obj_res[i]['country']+'</option>');
					}
				}
			}
		}).fail(function() {
			console.log("getgeoinfo Request Failed");
		});
	});
	function vrcCountryHasVal(c3) {
		var hasval = false;
		jQuery('#countryfilt option').each(function(k, v) {
			if (jQuery(v).attr('value') == c3) {
				hasval = true;
				return false;
			}
		});

		return hasval;
	}
	</script>
		<?php
	}
	?>

	<div class="vrc-trackings-outer-response">
		<div class="vrc-trackings-tabs">
			<div class="vrc-trackings-tab vrc-trackings-tab-active" data-vrctab="vrc-trackings-tabcont-list"><?php echo JText::_('VRCTRKVISITORS'); ?></div>
			<div class="vrc-trackings-tab" data-vrctab="vrc-trackings-tabcont-stats"><?php echo JText::_('VRCTRKCONVRATES'); ?></div>
		</div>
		<div class="vrc-trackings-tabcont-list" style="display: block;">
			<div class="vrc-trackings-table">
				<div class="vrc-trackings-table-head">
					<div class="vrc-trackings-table-head-inner">
						<div class="vrc-trackings-table-head-cell vrc-trackings-table-cell-chevron"></div>
						<div class="vrc-trackings-table-head-cell vrc-trackings-table-cell-ckb">
							<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
						</div>
						<div class="vrc-trackings-table-head-cell vrc-trackings-table-cell-id">
							<a href="index.php?option=com_vikrentcar&amp;task=trackings<?php echo $dates_filter; ?>&amp;vrcorderby=id&amp;vrcordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "id" ? "vrc-list-activesort" : "")); ?>">
								ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "id" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
							</a>
						</div>
						<div class="vrc-trackings-table-head-cell vrc-trackings-table-cell-lastdt">
							<a href="index.php?option=com_vikrentcar&amp;task=trackings<?php echo $dates_filter; ?>&amp;vrcorderby=lastdt&amp;vrcordersort=<?php echo ($orderby == "lastdt" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "lastdt" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "lastdt" ? "vrc-list-activesort" : "")); ?>">
								<?php echo JText::_('VRCTRKLASTDT').($orderby == "lastdt" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "lastdt" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
							</a>
						</div>
						<div class="vrc-trackings-table-head-cell vrc-trackings-table-cell-customer">
							<span><?php echo JText::_( 'VRCCUSTOMER' ); ?></span>
						</div>
						<div class="vrc-trackings-table-head-cell vrc-trackings-table-cell-country">
							<a href="index.php?option=com_vikrentcar&amp;task=trackings<?php echo $dates_filter; ?>&amp;vrcorderby=country&amp;vrcordersort=<?php echo ($orderby == "country" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "country" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "country" ? "vrc-list-activesort" : "")); ?>">
								<?php echo JText::_('ORDER_STATE').($orderby == "country" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "country" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
							</a>
						</div>
						<div class="vrc-trackings-table-head-cell center vrc-trackings-table-cell-geo">
							<a href="index.php?option=com_vikrentcar&amp;task=trackings<?php echo $dates_filter; ?>&amp;vrcorderby=geo&amp;vrcordersort=<?php echo ($orderby == "geo" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "geo" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "geo" ? "vrc-list-activesort" : "")); ?>">
								<?php echo JText::_('VRCTRKGEOINFO').($orderby == "geo" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "geo" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
							</a>
						</div>
						<div class="vrc-trackings-table-head-cell vrc-trackings-table-cell-dt">
							<a href="index.php?option=com_vikrentcar&amp;task=trackings<?php echo $dates_filter; ?>&amp;vrcorderby=dt&amp;vrcordersort=<?php echo ($orderby == "dt" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "dt" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "dt" ? "vrc-list-activesort" : "")); ?>">
								<?php echo JText::_('VRCTRKFIRSTDT').($orderby == "dt" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "dt" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
							</a>
						</div>
						<div class="vrc-trackings-table-head-cell center vrc-trackings-table-cell-published">
							<a href="index.php?option=com_vikrentcar&amp;task=trackings<?php echo $dates_filter; ?>&amp;vrcorderby=published&amp;vrcordersort=<?php echo ($orderby == "published" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "published" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "published" ? "vrc-list-activesort" : "")); ?>">
								<?php echo JText::_('VRCTRKPUBLISHED').($orderby == "published" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "published" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
							</a>
						</div>
					</div>
				</div>

				<div class="vrc-trackings-table-body">
			<?php
			$kk = 0;
			$i = 0;
			for ($i = 0, $n = count($rows); $i < $n; $i++) {
				$row = $rows[$i];
				$customer_info = JText::_('VRCANONYMOUS');
				if (!empty($row['first_name']) || !empty($row['last_name'])) {
					$customer_info = $row['first_name'].' '.$row['last_name'];
					$check_country = $row['country'];
					if (empty($check_country) && !empty($row['c_country'])) {
						$check_country = $row['c_country'];
					}
					if (!empty($check_country)) {
						if (is_file(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'countries' . DIRECTORY_SEPARATOR . $check_country . '.png')) {
							$customer_info .= '<img src="' . VRC_ADMIN_URI . 'resources/countries/' . $check_country . '.png'.'" title="' . $check_country . '" class="vrc-country-flag vrc-country-flag-left"/>';
						}
					}
				}
				$dt_info = getdate(strtotime($row['dt']));
				$lastdt_info = getdate(strtotime($row['lastdt']));
				?>
					<div class="vrc-trackings-table-body-row">
						<div class="vrc-trackings-table-head-cell vrc-trackings-table-cell-chevron vrc-trackings-togglesubrow-cont">
							<?php VikRentCarIcons::e('chevron-down', 'vrc-trackings-togglesubrow'); ?>
						</div>
						<div class="vrc-trackings-table-body-cell vrc-trackings-table-cell-ckb">
							<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row['id']; ?>" onclick="Joomla.isChecked(this.checked);">
						</div>
						<div class="vrc-trackings-table-body-cell vrc-trackings-table-cell-id">
							<div class="vrc-trackings-table-body-hidden-lbl">ID</div>
							<?php echo $row['id']; ?>
						</div>
						<div class="vrc-trackings-table-body-cell vrc-trackings-table-cell-lastdt">
							<div class="vrc-trackings-table-body-hidden-lbl"><?php echo JText::_('VRCTRKLASTDT'); ?></div>
							<div class="vrc-trackings-dtonly">
								<?php echo date($df, strtotime($row['lastdt'])); ?>
							</div>
							<div class="vrc-trackings-timeonly">
								<span class="vrc-trackings-wday"><?php echo JText::_('VR'.strtoupper(substr($lastdt_info['weekday'], 0, 3))); ?></span>
								<span class="vrc-trackings-time"><?php echo date($nowtf, strtotime($row['lastdt'])); ?></span>
							</div>
						</div>
						<div class="vrc-trackings-table-body-cell vrc-trackings-table-cell-customer">
							<div class="vrc-trackings-table-body-hidden-lbl"><?php echo JText::_('VRCCUSTOMER'); ?></div>
							<?php echo $customer_info; ?>
						</div>
						<div class="vrc-trackings-table-body-cell vrc-trackings-table-cell-country" id="country-<?php echo $row['id']; ?>">
							<div class="vrc-trackings-table-body-hidden-lbl"><?php echo JText::_('ORDER_STATE'); ?></div>
							<?php echo !empty($row['country_name']) ? $row['country_name'] : '-----'; ?>
						</div>
						<div class="vrc-trackings-table-body-cell center vrc-trackings-table-cell-geo" id="geo-<?php echo $row['id']; ?>">
							<div class="vrc-trackings-table-body-hidden-lbl"><?php echo JText::_('VRCTRKGEOINFO'); ?></div>
							<?php echo !empty($row['geo']) ? $row['geo'] : '-----'; ?>
						</div>
						<div class="vrc-trackings-table-body-cell vrc-trackings-table-cell-dt">
							<div class="vrc-trackings-table-body-hidden-lbl"><?php echo JText::_('VRCTRKFIRSTDT'); ?></div>
							<div class="vrc-trackings-dtonly">
								<?php echo date($df, strtotime($row['dt'])); ?>
							</div>
							<div class="vrc-trackings-timeonly">
								<span class="vrc-trackings-wday"><?php echo JText::_('VR'.strtoupper(substr($dt_info['weekday'], 0, 3))); ?></span>
								<span class="vrc-trackings-time"><?php echo date($nowtf, strtotime($row['dt'])); ?></span>
							</div>
						</div>
						<div class="vrc-trackings-table-body-cell center vrc-trackings-table-cell-published">
							<div class="vrc-trackings-table-body-hidden-lbl"><?php echo JText::_('VRCTRKPUBLISHED'); ?></div>
							<a href="index.php?option=com_vikrentcar&amp;task=modtracking&amp;cid[]=<?php echo $row['id']; ?>"><?php echo ($row['published'] ? "<i class=\"".VikRentCarIcons::i('check', 'vrc-icn-img')."\" style=\"color: #099909;\" title=\"".JText::_('VRCTRKMAKENOTAVAIL')."\"></i>" : "<i class=\"".VikRentCarIcons::i('times-circle', 'vrc-icn-img')."\" style=\"color: #ff0000;\" title=\"".JText::_('VRCTRKMAKEAVAIL')."\"></i>"); ?></a>
						</div>

						<div class="vrc-trackings-table-body-subrow">
							<div class="vrc-tracking-info-container">
							<?php
							$tot_infos = count($row['infos']);
							foreach ($row['infos'] as $k => $info) {
								$trkdata = json_decode($info['trkdata']);
								$is_subidentifier = false;
								$is_opening = false;
								if (!isset($row['infos'][($k - 1)]) || $info['identifier'] != $row['infos'][($k - 1)]['identifier']) {
									// open identifier because previous is different or not set (this is the first record)
									$is_opening = true;
									echo '<div class="vrc-tracking-identifier-container">'."\n";
								} elseif (isset($row['infos'][($k - 1)]) && $info['identifier'] == $row['infos'][($k - 1)]['identifier']) {
									$is_subidentifier = true;
								}
								?>
								<div class="vrc-tracking-info-details<?php echo $is_subidentifier ? ' vrc-tracking-info-details-continue' : ''; ?><?php echo !empty($info['idorder']) ? ' vrc-tracking-info-hasconversion' : ''; ?>">
								<?php
								$device = '';
								if ($is_opening) {
									if ($info['device'] == 'C') {
										// computer
										$device = '<i class="'.VikRentCarIcons::i('desktop', 'vrc-tracking-i-desktop').'"></i>';
									} elseif ($info['device'] == 'S') {
										// smartphone
										$device = '<i class="'.VikRentCarIcons::i('mobile', 'vrc-tracking-i-mobile').'"></i>';
									} elseif ($info['device'] == 'T') {
										// tablet
										$device = '<i class="'.VikRentCarIcons::i('tablet', 'vrc-tracking-i-tablet').'"></i>';
									}
								}
								if (!empty($device)) {
									?>
									<div class="vrc-tracking-info-device-cont">
										<div class="vrc-tracking-info-subrow-lbl"><?php echo JText::_('VRCTRKDEVICE'); ?></div>
										<span class="vrc-tracking-info-device"><?php echo $device; ?></span>
									</div>
									<?php
								} else {
									?>
									<div class="vrc-tracking-info-device-cont"></div>
									<?php
								}
								?>
									<div class="vrc-tracking-info-dt-cont">
										<div class="vrc-tracking-info-subrow-lbl"><?php echo JText::_('VRCTRKTRACKTIME'); ?></div>
									<?php
									if (!$is_subidentifier) {
										$subdt_info = getdate(strtotime($info['trackingdt']));
										?>
										<div class="vrc-tracking-info-dtonly">
											<?php echo date($df, strtotime($info['trackingdt'])); ?>
										</div>
										<div class="vrc-tracking-info-timeonly">
											<span class="vrc-tracking-info-wday"><?php echo JText::_('VR'.strtoupper(substr($subdt_info['weekday'], 0, 3))); ?></span>
											<span class="vrc-tracking-info-time"><?php echo date($nowtf, strtotime($info['trackingdt'])); ?></span>
										</div>
										<?php
									} else {
										$diff_info = VikRentCarTracker::datesDiff($info['trackingdt'], $row['infos'][($k - 1)]['trackingdt']);
										$diff_type = JText::_('VRCTRKDIFFSECS');
										if ($diff_info['type'] == 'minutes') {
											$diff_type = JText::_('VRCTRKDIFFMINS');
										} elseif ($diff_info['type'] == 'hours') {
											$diff_type = JText::_('VRCONFIGONETENEIGHT');
										}
										?>
										<span class="vrc-tracking-info-aftertime" title="<?php echo date($df.' H:i:s', strtotime($info['trackingdt'])); ?>">+ <?php echo $diff_info['diff'] . ' ' . $diff_type; ?></span>
										<?php
									}
									?>
									</div>
									<div class="vrc-tracking-info-dates-cont">
										<div class="vrc-tracking-info-subrow-lbl"><?php echo JText::_('VRCTRKBOOKINGDATES'); ?></div>
										<div class="vrc-tracking-info-dates-in">
											<span class="vrc-tracking-info-lbl">
												<?php echo JText::_('VRPICKUPAT'); ?>
											</span>
											<?php
											if (!empty($trkdata->pickup_location)) {
												?>
											<span class="vrc-tracking-info-lbl vrc-tracking-info-lbl-location">
												<?php echo $trkdata->pickup_location; ?>
											</span>
												<?php
											}
											?>
											<span class="vrc-tracking-info-val">
												<?php
											if (isset($trkdata->pickup)) {
												$tsdt = strtotime($trkdata->pickup);
												$time_info = getdate($tsdt);
												echo JText::_('VR'.strtoupper(substr($time_info['weekday'], 0, 3))) . ', ' . date($df . ' ' . $nowtf, $tsdt);
											}
												?>
											</span>
										</div>
										<div class="vrc-tracking-info-dates-out">
											<span class="vrc-tracking-info-lbl">
												<?php echo JText::_('VRRELEASEAT'); ?>
											</span>
											<?php
											if (!empty($trkdata->dropoff_location)) {
												?>
											<span class="vrc-tracking-info-lbl vrc-tracking-info-lbl-location">
												<?php echo $trkdata->dropoff_location; ?>
											</span>
												<?php
											}
											?>
											<span class="vrc-tracking-info-val">
												<?php
											if (isset($trkdata->dropoff)) {
												$tsdt = strtotime($trkdata->dropoff);
												$time_info = getdate($tsdt);
												echo JText::_('VR'.strtoupper(substr($time_info['weekday'], 0, 3))) . ', ' . date($df . ' ' . $nowtf, $tsdt);
											}
												?>
											</span>
										</div>
									<?php
									if (isset($trkdata->nights)) {
										?>
										<div class="vrc-tracking-info-dates-out">
											<span class="vrc-tracking-info-lbl">
												<?php echo JText::_('VRPVIEWORDERSSIX'); ?>
											</span>
											<span class="vrc-tracking-info-val">
												<?php echo $trkdata->nights; ?>
											</span>
										</div>
										<?php
									}
									?>
									</div>
								<?php
								if (isset($trkdata->cars) || isset($trkdata->rplans)) {
									?>
									<div class="vrc-tracking-info-carsrates-cont">
										<div class="vrc-tracking-info-subrow-lbl"><?php echo JText::_('VRCTRKCARSRATES'); ?></div>
									<?php
									if (isset($trkdata->cars)) {
										?>
											<div class="vrc-tracking-info-cars">
										<?php
										foreach ($trkdata->cars as $idcar => $units) {
											?>
												<div class="vrc-tracking-info-cars-car">
													<span class="vrc-tracking-info-lbl">
														<?php echo (isset($this->cars[$idcar]) ? $this->cars[$idcar] : '?').($units > 1 ? ' (x'.$units.')' : ''); ?>
													</span>
												</div>
											<?php
										}
										?>
											</div>
										<?php
									}
									if (isset($trkdata->rplans)) {
										?>
											<div class="vrc-tracking-info-rplans">
										<?php
										foreach ($trkdata->rplans as $idprice => $units) {
											?>
												<div class="vrc-tracking-info-rplans-car">
													<span class="vrc-tracking-info-lbl">
														<?php echo (isset($this->prices[$idprice]) ? $this->prices[$idprice] : '?').($units > 1 ? ' (x'.$units.')' : ''); ?>
													</span>
												</div>
											<?php
										}
										?>
											</div>
										<?php
									}
									?>
									</div>
									<?php
								}
								if (!empty($info['idorder'])) {
									if ($info['status'] == "confirmed") {
										$saystaus = '<span class="label label-success vrc-status-label">'.JText::_('VRCONFIRMED').'</span>';
									} elseif ($info['status'] == "standby") {
										$saystaus = '<span class="label label-warning vrc-status-label">'.JText::_('VRSTANDBY').'</span>';
									} else {
										$saystaus = '<span class="label label-error vrc-status-label">'.JText::_('VRCANCELLED').'</span>';
									}
									?>
									<div class="vrc-tracking-info-booking-cont">
										<div class="vrc-tracking-info-subrow-lbl"><?php echo JText::_('VRCTRKBOOKCONV'); ?></div>
										<?php echo $saystaus; ?>
										<a href="index.php?option=com_vikrentcar&task=editorder&cid[]=<?php echo $info['idorder']; ?>" target="_blank"><?php VikRentCarIcons::e('external-link'); ?> <?php echo $info['idorder']; ?></a>
									</div>
									<?php
								}
								if (!empty($info['referrer'])) {
									?>
									<div class="vrc-tracking-info-booking-referrer">
										<div class="vrc-tracking-info-subrow-lbl"><?php echo JText::_('VRCTRKREFERRER'); ?></div>
										<span><?php VikRentCarIcons::e('globe'); ?> <?php echo $info['referrer']; ?></span>
									</div>
									<?php
								}
								if (isset($trkdata->msg)) {
									?>
									<div class="vrc-tracking-info-search-results">
									<?php
									foreach ($trkdata->msg as $msg) {
										$msg_type = strtolower($msg->type);
										$msg_icon = '<i class="'.VikRentCarIcons::i('info-circle').'"></i>';
										if ($msg_type == 'success') {
											$msg_icon = '<i class="'.VikRentCarIcons::i('check-circle').'"></i>';
										} elseif ($msg_type == 'warning') {
											$msg_icon = '<i class="'.VikRentCarIcons::i('exclamation-triangle').'"></i>';
										} elseif ($msg_type == 'error') {
											$msg_icon = '<i class="'.VikRentCarIcons::i('times-circle').'"></i>';
										}
										?>
										<div class="vrc-tracking-info-search-result vrc-tracking-info-search-result-<?php echo $msg_type; ?>">
											<p><?php echo $msg_icon . ' ' . $msg->text; ?></p>
										</div>
										<?php
									}
									?>
									</div>
									<?php
								}
								?>
								</div>
								<?php
								if (!isset($row['infos'][($k + 1)]) || $info['identifier'] != $row['infos'][($k + 1)]['identifier']) {
									// close current identifier because next is different
									echo '</div>'."\n";
								}
							}
							?>
							</div>
						</div>
					</div>
				<?php
				$kk = 1 - $kk;
			}
			?>
				</div>
			</div>
		</div>
		<?php
		// calculate most demanded nights, conversion rates, best referrers, average LOS
		$demands_nights = array();
		$demands_count  = array();
		$referrer_count = array();
		$totidentifiers = array();
		$totbookings 	= array();
		$los_pool 		= array();
		foreach ($this->stats_data as $stat) {
			if (!empty($stat['referrer'])) {
				if (!isset($referrer_count[$stat['referrer']])) {
					$referrer_count[$stat['referrer']] = 0;
				}
				$referrer_count[$stat['referrer']]++;
			}
			if (!isset($totidentifiers[$stat['identifier']])) {
				// total identifiers
				$totidentifiers[$stat['identifier']] = 1;
			}
			if (!empty($stat['idorder']) && !isset($totbookings[$stat['identifier']])) {
				// one conversion per tracking identifier
				$totbookings[$stat['identifier']] = $stat['idorder'];
			}
			// loop through the nights of this tracking info record
			$in_info = getdate(strtotime($stat['pickup']));
			$out_dt  = date('Y-m-d', strtotime($stat['dropoff']));
			$in_dt   = date('Y-m-d', $in_info[0]);
			$now_los = 0;
			while ($in_dt != $out_dt) {
				$now_los++;
				if (!isset($demands_nights[$in_dt])) {
					$demands_nights[$in_dt] = 0;
				}
				// increase the requests for this night
				$demands_nights[$in_dt]++;
				if (!isset($demands_count[$in_dt])) {
					$demands_count[$in_dt] = array();
				}
				if (!in_array($stat['idtracking'], $demands_count[$in_dt])) {
					// push this visitor (tracking) ID to the counter for this night
					array_push($demands_count[$in_dt], $stat['idtracking']);
				}
				// update next loop
				$in_info = getdate(mktime(0, 0, 0, $in_info['mon'], ($in_info['mday'] + 1), $in_info['year']));
				$in_dt   = date('Y-m-d', $in_info[0]);
			}
			array_push($los_pool, $now_los);
		}
		// sort most demanded nights and best referrers
		arsort($demands_nights);
		arsort($referrer_count);
		// average conversion rate: 100 : totidentifiers = x : totbookings
		$count_tot_identif = count($totidentifiers) ? count($totidentifiers) : 1;
		$avg_conv_rate = 100 * count($totbookings) / $count_tot_identif;
		$avg_conv_rate = round($avg_conv_rate, 2);
		$avg_conv_colr = '#550000'; //black-red
		if ($avg_conv_rate > 33 && $avg_conv_rate <= 66) {
			$avg_conv_colr = '#ff4d4d'; //red
		} elseif ($avg_conv_rate > 66 && $avg_conv_rate < 100) {
			$avg_conv_colr = '#ffa64d'; //orange
		} elseif ($avg_conv_rate >= 100) {
			$avg_conv_colr = '#2a762c'; //green
		}
		// average length of stay
		$count_los_pool = count($los_pool) ? count($los_pool) : 1;
		$avg_los = array_sum($los_pool) / $count_los_pool;
		$avg_los = round($avg_los, 1);
		?>
		<div class="vrc-trackings-tabcont-stats" style="display: none;">
			<div class="vrc-trackings-chart-bestnights">
				<h4><?php echo JText::_('VRCTRKMOSTDEMNIGHTS'); ?></h4>
			<?php
			// the 14 most demanded nights
			$max = 14;
			$ind = 0;
			foreach ($demands_nights as $dt => $tot) {
				$dt_info = getdate(strtotime($dt));
				?>
				<div class="vrc-trackings-chart-container" id="vrc-trackings-chart-container-<?php echo $dt; ?>">
					<span class="vrc-trackings-chart-date"><?php echo JText::_('VR'.strtoupper(substr($dt_info['weekday'], 0, 3))); ?>, <?php echo date($df, $dt_info[0]); ?></span>
					<div class="vrc-trackings-chart-cont">
						<div class="vrc-trackings-chart-totreqs">
							<span class="vrc-trackings-chart-tot"><?php echo $tot; ?></span>
							<span class="vrc-trackings-chart-txt"><?php echo JText::_('VRCTRKREQSNUM'); ?></span>
						</div>
						<div class="vrc-trackings-chart-totviss">
							<span class="vrc-trackings-chart-tot"><?php echo count($demands_count[$dt]); ?></span>
							<span class="vrc-trackings-chart-txt"><?php echo JText::_('VRCTRKVISSNUM'); ?></span>
						</div>
					</div>
				</div>
				<?php
				$ind++;
				if ($ind >= $max) {
					break;
				}
			}
			?>
			</div>

			<div class="vrc-trackings-chart-middle">

				<div class="vrc-trackings-chart-avgvals">
					<div class="vrc-trackings-chart-avgval-container">
						<h4><?php echo JText::_('VRCTRKAVGVALS'); ?></h4>
						<div class="vrc-trackings-chart-avgval-listcont">
							<div class="vrc-trackings-avgval">
								<div class="vrc-trackings-avgval-det">
									<h5><?php echo JText::_('VRCTRKTOTVISS'); ?></h5>
									<div class="vrc-trackings-chart-avgviss">
										<span class="vrc-trackings-chart-tot"><?php echo count($totidentifiers); ?></span>
									</div>
								</div>
							</div>
							<div class="vrc-trackings-avgval">
								<div class="vrc-trackings-avgval-det">
									<h5><?php echo JText::_('VRCUSTOMERTOTBOOKINGS'); ?></h5>
									<div class="vrc-trackings-chart-totres">
										<span class="vrc-trackings-chart-tot"><?php echo count($totbookings); ?></span>
									</div>
								</div>
							</div>
							<div class="vrc-trackings-avgval">
								<div class="vrc-trackings-avgval-det">
									<h5><?php echo JText::_('VRCTRKAVGLOS'); ?></h5>
									<div class="vrc-trackings-chart-avglos">
										<span class="vrc-trackings-chart-tot"><?php echo $avg_los; ?></span>
									</div>
								</div>
							</div>
							<div class="vrc-trackings-avgval">
								<div class="vrc-trackings-avgval-det">
									<h5><?php echo JText::_('VRCTRKAVGCONVRATE'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCTRKAVGCONVRATE'), 'content' => JText::_('VRCTRKAVGCONVRATEHELP'))); ?></h5>
									<div class="vrc-trackings-chart-avgconvrate">
										<span class="vrc-trackings-chart-tot" style="color: <?php echo $avg_conv_colr; ?>;"><?php echo $avg_conv_rate; ?></span>
										<span class="vrc-trackings-chart-pcent">%</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php
			if (count($referrer_count)) {
			?>
				<div class="vrc-trackings-chart-referrers">
					<h4><?php echo JText::_('VRCTRKBESTREFERRERS'); ?></h4>
				<?php
				// the 5 best referrers
				$max = 5;
				$ind = 0;
				foreach ($referrer_count as $name => $tot) {
					?>
					<div class="vrc-trackings-chart-referrer">
						<span class="vrc-trackings-chart-date"><?php echo $name; ?></span>
						<div class="vrc-trackings-chart-cont">
							<div class="vrc-trackings-chart-totreqs">
								<span class="vrc-trackings-chart-tot"><?php echo $tot; ?></span>
								<span class="vrc-trackings-chart-txt"><?php echo JText::_('VRCTRKVISSNUM'); ?></span>
							</div>
						</div>
					</div>
					<?php
					$ind++;
					if ($ind >= $max) {
						break;
					}
				}
				?>
				</div>
			<?php
			}
			?>
			</div>

		</div>
	</div>
<?php
}
?>
	<input type="hidden" name="vrc_active_tab" id="vrc_active_tab" value="">
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="trackings" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
