<?php  
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_search
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2017 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$dbo = JFactory::getDbo();
$session = JFactory::getSession();
$vrc_tn = ModVikrentcarSearchHelper::getTranslator();
$restrictions = ModVikrentcarSearchHelper::loadRestrictions();
$def_min_los = ModVikrentcarSearchHelper::setDropDatePlus();

$config = VRCFactory::getConfig();

JText::script('VRC_LOC_WILL_OPEN_TIME');
JText::script('VRC_LOC_WILL_CLOSE_TIME');
JText::script('VRC_PICKLOC_IS_ON_BREAK_TIME_FROM_TO');
JText::script('VRC_DROPLOC_IS_ON_BREAK_TIME_FROM_TO');

$svrcplace = $session->get('vrcplace', '');
$indvrcplace = 0;
$svrcreturnplace = $session->get('vrcreturnplace', '');
$indvrcreturnplace = 0;

$document = JFactory::getDocument();
$document->addStyleSheet($baseurl.'modules/mod_vikrentcar_search/mod_vikrentcar_search.css');
//load jQuery UI
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery-ui.min.css');
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.min.js');


// david's custom js & css
$document->addStyleSheet($baseurl.'modules/mod_vikrentcar_search/css/datepicker.min.css');
// JHtml::_('script', VRC_SITE_URI.'resources/js/datepicker.min.js');
JHtml::_('script', 'https://cdn.jsdelivr.net/npm/datedreamer@0.2.0/dist/datedreamer.js');
JHtml::_('script', VRC_SITE_URI.'resources/js/my.js');



$heading_text = $params->get('heading_text');

/**
 * @wponly 	the AJAX requests below require the Itemid for JRoute
 */
?>

<!-- <div class="vrcdivsearch vrcdivsearchmodule <?php echo $params->get('orientation') == 'horizontal' ? 'vrc-searchmod-wrap-horizontal' : 'vrc-searchmod-wrap-vertical'; ?>"> -->
<div class="rental-form">
	<div class="row">
		<div class="col-4">AAA</div>
		<div class="col-8">
			<div>
				<div id="rangepicker"></div>
			</div>
			<div>
				<label for="time">Start Time</label>
				<input type="time" name="time" id="time" />

				<label for="time">Start Time</label>
				<input type="time" name="time" id="time" />
			</div>
		</div>
	</div>
</div>



<!-- </div> -->

<?php
//VikRentCar 1.7
$sespickupts = $session->get('vrcpickupts', '');
$sesdropoffts = $session->get('vrcreturnts', '');
$ptask = VikRequest::getString('task', '', 'request');
if ($ptask == 'search' && !empty($sespickupts) && !empty($sesdropoffts)) {
	if ($dateformat == "%d/%m/%Y") {
		$jsdf = 'd/m/Y';
	} elseif ($dateformat == "%m/%d/%Y") {
		$jsdf = 'm/d/Y';
	} else {
		$jsdf = 'Y/m/d';
	}
	$sespickuph = date('H', $sespickupts);
	$sespickupm = date('i', $sespickupts);
	$sesdropoffh = date('H', $sesdropoffts);
	$sesdropoffm = date('i', $sesdropoffts);
	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		document.getElementById('pickupdatemod<?php echo $randid; ?>').value = '<?php echo date($jsdf, $sespickupts); ?>';
		document.getElementById('releasedatemod<?php echo $randid; ?>').value = '<?php echo date($jsdf, $sesdropoffts); ?>';
		var modf = jQuery("#pickupdatemod<?php echo $randid; ?>").closest("form");
		modf.find("select[name='pickuph']").val("<?php echo $sespickuph; ?>");
		modf.find("select[name='pickupm']").val("<?php echo $sespickupm; ?>");
		modf.find("select[name='releaseh']").val("<?php echo $sesdropoffh; ?>");
		modf.find("select[name='releasem']").val("<?php echo $sesdropoffm; ?>");
	});
	</script>
	<?php
}

/**
 * Form submit JS validation (mostly used for the opening/closing minutes).
 * This piece of code should be always printed in the DOM as the main form
 * calls this function when going on submit.
 * 
 * @since 	1.12
 */
?>
<script type="text/javascript">
function vrcCleanNumber<?php echo $randid; ?>(snum) {
	if (snum.length > 1 && snum.substr(0, 1) == '0') {
		return parseInt(snum.substr(1));
	}
	return parseInt(snum);
}
function vrcFormatTime<?php echo $randid; ?>(h, m) {
	var time_format = '<?php echo $nowtf; ?>';
	var time_ftfour = (time_format == 'H:i');
	var time_ampm = '';
	if (!time_ftfour) {
		if (h >= 12 && h < 24) {
			time_ampm = ' PM';
			if (h > 12) {
				h -= 12;
			}
		} else {
			time_ampm = ' AM';
		}
	}
	return (h < 10 ? ('0' + h) : h) + ':' + ((m < 10 ? ('0' + m) : m)) + time_ampm;
}
function vrcValidateSearch<?php echo $randid; ?>() {
	if (typeof jQuery === 'undefined' || typeof vrcmod_wopening_pick === 'undefined') {
		return true;
	}
	if (vrcmod_mopening_pick !== null) {
		// pickup time
		var pickh = jQuery('#vrcmodselph').find('select').val();
		var pickm = jQuery('#vrcmodselpm').find('select').val();
		if (!pickh || !pickh.length || !pickm) {
			return true;
		}
		pickh = vrcCleanNumber<?php echo $randid; ?>(pickh);
		pickm = vrcCleanNumber<?php echo $randid; ?>(pickm);
		if (pickh == vrcmod_mopening_pick[0]) {
			if (pickm < vrcmod_mopening_pick[1]) {
				// location is still closed at this time
				jQuery('#vrcmodselpm').find('select').html('<option value="'+vrcmod_mopening_pick[1]+'">'+(vrcmod_mopening_pick[1] < 10 ? '0'+vrcmod_mopening_pick[1] : vrcmod_mopening_pick[1])+'</option>').val(vrcmod_mopening_pick[1]);
				alert(Joomla.JText._('VRC_LOC_WILL_OPEN_TIME').replace('%s', vrcFormatTime<?php echo $randid; ?>(vrcmod_mopening_pick[0], vrcmod_mopening_pick[1])));
				// do not return false as we are overwriting the pickup time
				// return false;
			}
		}
		if (pickh == vrcmod_mopening_pick[2]) {
			if (pickm > vrcmod_mopening_pick[3]) {
				// location is already closed at this time for a pick up
				jQuery('#vrccomselpm').find('select').html('<option value="'+vrcmod_mopening_pick[3]+'">'+(vrcmod_mopening_pick[3] < 10 ? '0'+vrcmod_mopening_pick[3] : vrcmod_mopening_pick[3])+'</option>').val(vrcmod_mopening_pick[3]);
				alert(Joomla.JText._('VRC_LOC_WILL_CLOSE_TIME').replace('%s', vrcFormatTime<?php echo $randid; ?>(vrcmod_mopening_pick[2], vrcmod_mopening_pick[3])));
				// do not return false as we are overwriting the pickup time
				// return false;
			}
		}
		// check for breaks
		if (vrcmod_mopening_pick[4] && vrcmod_mopening_pick[4].length) {
			for (var b = 0; b < vrcmod_mopening_pick[4].length; b++) {
				if (!vrcmod_mopening_pick[4][b].hasOwnProperty('fh')) {
					continue;
				}
				var break_mess = Joomla.JText._('VRC_PICKLOC_IS_ON_BREAK_TIME_FROM_TO')
					.replace('%s', vrcFormatTime<?php echo $randid; ?>(vrcmod_mopening_pick[4][b]['fh'], vrcmod_mopening_pick[4][b]['fm']))
					.replace('%s', vrcFormatTime<?php echo $randid; ?>(vrcmod_mopening_pick[4][b]['th'], vrcmod_mopening_pick[4][b]['tm']));
				var break_from_secs = (vrcmod_mopening_pick[4][b]['fh'] * 3600) + (vrcmod_mopening_pick[4][b]['fm'] * 60);
				var break_to_secs = (vrcmod_mopening_pick[4][b]['th'] * 3600) + (vrcmod_mopening_pick[4][b]['tm'] * 60);
				var pick_secs = (pickh * 3600) + (pickm * 60);
				if (pick_secs > break_from_secs && pick_secs < break_to_secs) {
					// the location is on break at this time
					alert(break_mess);
					return false;
				} else if (vrcmod_mopening_pick[4][b]['fh'] > vrcmod_mopening_pick[4][b]['th'] && pick_secs < break_from_secs && pick_secs < break_to_secs) {
					// overnight break, with time after midnight
					alert(break_mess);
					return false;
				} else if (vrcmod_mopening_pick[4][b]['fh'] > vrcmod_mopening_pick[4][b]['th'] && pick_secs > break_from_secs && pick_secs > break_to_secs) {
					// overnight break, with time before midnight
					alert(break_mess);
					return false;
				}
			}
		}
	}

	if (vrcmod_mopening_drop !== null) {
		// dropoff time
		var droph = jQuery('#vrcmodseldh').find('select').val();
		var dropm = jQuery('#vrcmodseldm').find('select').val();
		if (!droph || !droph.length || !dropm) {
			return true;
		}
		droph = vrcCleanNumber<?php echo $randid; ?>(droph);
		dropm = vrcCleanNumber<?php echo $randid; ?>(dropm);
		if (droph == vrcmod_mopening_drop[0]) {
			if (dropm > vrcmod_mopening_drop[1]) {
				// location is already closed at this time
				jQuery('#vrcmodseldm').find('select').html('<option value="'+vrcmod_mopening_drop[1]+'">'+(vrcmod_mopening_drop[1] < 10 ? '0'+vrcmod_mopening_drop[1] : vrcmod_mopening_drop[1])+'</option>').val(vrcmod_mopening_drop[1]);
				alert(Joomla.JText._('VRC_LOC_WILL_CLOSE_TIME').replace('%s', <?php echo $randid; ?>(vrcmod_mopening_drop[0], vrcmod_mopening_drop[1])));
				// do not return false as we are overwriting the drop off time
				// return false;
			}
		}
		if (droph == vrcmod_mopening_drop[2]) {
			if (dropm < vrcmod_mopening_drop[3]) {
				// location is still closed at this time for a drop off
				jQuery('#vrcmodseldm').find('select').html('<option value="'+vrcmod_mopening_drop[3]+'">'+(vrcmod_mopening_drop[3] < 10 ? '0'+vrcmod_mopening_drop[3] : vrcmod_mopening_drop[3])+'</option>').val(vrcmod_mopening_drop[3]);
				alert(Joomla.JText._('VRC_LOC_WILL_OPEN_TIME').replace('%s', <?php echo $randid; ?>(vrcmod_mopening_drop[2], vrcmod_mopening_drop[3])));
				// do not return false as we are overwriting the drop off time
				// return false;
			}
		}
		// check for breaks
		if (vrcmod_mopening_drop[4] && vrcmod_mopening_drop[4].length) {
			for (var b = 0; b < vrcmod_mopening_drop[4].length; b++) {
				if (!vrcmod_mopening_drop[4][b].hasOwnProperty('fh')) {
					continue;
				}
				var break_mess = Joomla.JText._('VRC_DROPLOC_IS_ON_BREAK_TIME_FROM_TO')
					.replace('%s', <?php echo $randid; ?>(vrcmod_mopening_drop[4][b]['fh'], vrcmod_mopening_drop[4][b]['fm']))
					.replace('%s', <?php echo $randid; ?>(vrcmod_mopening_drop[4][b]['th'], vrcmod_mopening_drop[4][b]['tm']));
				var break_from_secs = (vrcmod_mopening_drop[4][b]['fh'] * 3600) + (vrcmod_mopening_drop[4][b]['fm'] * 60);
				var break_to_secs = (vrcmod_mopening_drop[4][b]['th'] * 3600) + (vrcmod_mopening_drop[4][b]['tm'] * 60);
				var drop_secs = (droph * 3600) + (dropm * 60);
				if (drop_secs > break_from_secs && drop_secs < break_to_secs) {
					// the location is on break at this time
					alert(break_mess);
					return false;
				} else if (vrcmod_mopening_drop[4][b]['fh'] > vrcmod_mopening_drop[4][b]['th'] && drop_secs < break_from_secs && drop_secs < break_to_secs) {
					// overnight break, with time after midnight
					alert(break_mess);
					return false;
				} else if (vrcmod_mopening_drop[4][b]['fh'] > vrcmod_mopening_drop[4][b]['th'] && drop_secs > break_from_secs && drop_secs > break_to_secs) {
					// overnight break, with time before midnight
					alert(break_mess);
					return false;
				}
			}
		}
	}

	return true;
}
</script>
<?php
//
