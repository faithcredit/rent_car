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
$document->addStyleSheet($baseurl.'modules/mod_vikrentcar_search/css/custom-select.css');





// <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.11/css/all.css" integrity="sha384-p2jx59pefphTFIpeqCcISO9MdVfIm4pNnsL08A6v5vaQc4owkQqxMV8kg4Yvhaw/" crossorigin="anonymous">
// <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
// <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
// <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
// <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i" rel="stylesheet">
// <link type="text/css" href="https://rawgit.com/IonDen/ion.rangeSlider/master/css/ion.rangeSlider.css" rel="stylesheet">
// <link type="text/css" href="https://rawgit.com/IonDen/ion.rangeSlider/master/css/ion.rangeSlider.skinHTML5.css" rel="stylesheet">
// <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css');

$document->addStyleSheet('https://cdn.quilljs.com/1.3.6/quill.snow.css');
$document->addStyleSheet($baseurl.'modules/mod_vikrentcar_search/css/my-location-select.css');

JHtml::_('script', VRC_SITE_URI.'resources/js/datepicker.min.js');
// JHtml::_('script', 'https://cdn.jsdelivr.net/npm/datedreamer@0.2.0/dist/datedreamer.js');
JHtml::_('script', VRC_SITE_URI.'resources/js/my.js');
JHtml::_('script', VRC_SITE_URI.'resources/js/custom-select.js');







// <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
// <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
// <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
// <script src="https://rawgit.com/IonDen/ion.rangeSlider/master/js/ion.rangeSlider.min.js"></script>
// <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
JHtml::_('script', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js');

JHtml::_('script', 'https://cdn.quilljs.com/1.3.6/quill.js');

JHtml::_('script', VRC_SITE_URI.'resources/js/my-location-select.js');





$heading_text = $params->get('heading_text');

/**
 * @wponly 	the AJAX requests below require the Itemid for JRoute
 */
?>
<form class="searching-submit-form" action="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=search&Itemid=' . $params->get('itemid', 0)); ?>" method="post">
	<input type="hidden" name="task" value="search" /> 
	<input type="hidden" name="place" value="4" />
	<input type="hidden" name="returnplace" value="4" />
	<input type="hidden" name="pickupdate" value="1" />
	<input type="hidden" name="pickuph" value="1" />
	<input type="hidden" name="pickupm" value="1" />
	<input type="hidden" name="releasedate" value="1" />
	<input type="hidden" name="releaseh" value="1" />
	<input type="hidden" name="releasem" value="1" />
	<input type="hidden" name="categories" value="all" />

</form>

<!-- <div class="vrcdivsearch vrcdivsearchmodule <?php echo $params->get('orientation') == 'horizontal' ? 'vrc-searchmod-wrap-horizontal' : 'vrc-searchmod-wrap-vertical'; ?>"> -->
<!-- <form action="<?php //echo JRoute::_('index.php?option=com_vikrentcar&task=search&Itemid=' . $params->get('itemid', 0)); ?>" method="post" onsubmit="return vrcValidateSearch<?php //echo $randid; ?>();">
		<input type="hidden" name="task" value="search"/> -->

<div class="rental-form">
	<div style="display: flex; background: #ffffff; box-shadow: 0px 10px 10px 0px rgb(0 0 0 / 10%);">
		<div style="width: 360px; padding: 50px 40px; background: #ffffffc2; box-shadow: 0px -3px 10px 5px rgb(0 0 0 / 10%);">

			<h2>Louer la voiture</h1>

			<div style="font-size: 12px; color: gray; margin-top: 20px">Pickup/Livraison</div>
			<!-- <div id="start-label-4searchpad1" style="font-size: 16px; border-bottom: 1px solid gray">Aéroport, domicile, adresse ou agence</div> -->

			<div class="form" style="width: auto">
				<div class="form-row">
					<div class="form-unit form-divided form-divided-left" style="border-bottom: 1px solid gray">
						<input class="input-text selectable" 
								style="
									width: 280px;
									color: #000;
									padding: 0 0.75em 0.1em 0;
									height: auto;
									border-width: 0px;
									font-size: 16px;
									border-bottom: 0px solid #8b8b8b;
									border-style: solid;
									border-color: #808080;
									border-radius: 0px;
									background: #ffffff;
									box-shadow: none;
									box-sizing: border-box;
									transition: all .2s linear;"
								type="text" placeholder="Select location" data-drop-type="first">
						<a class="input-select-btn dropdown-btn-wrapper"><i class="fas fa-sort-down dropdown-icon"></i></a>
						<a class="input-select-btn input-select-btn-clear clear-selection first"><i class="fas fa-times dropdown-icon"></i></a>
					</div>
				</div>
			</div>
			<div class="selectable-dropdown-wrapper" data-drop-type="first" style="top: 275px !important;
				left: -7px !important;
				width: 279px !important">
				<ul class="selectable-dropdown">
					<li class="selectable-dropdown-each" data-loc-name="Agence Zellik" data-loc="4">Agence Zellik</li>
					<li class="selectable-dropdown-each" data-loc-name="Livraison (+ 75€)" data-loc="5">Livraison (+ 75€)</li>
					<li class="selectable-dropdown-each selectable-noresult">No results found</li>
				</ul>
			</div>




			<div style="font-size: 12px; color: gray; margin-top: 20px">Retour</div>
			<!-- <div id="start-label-4searchpad2" style="font-size: 16px; border-bottom: 1px solid gray">Aéroport, domicile, adresse ou agence</div> -->
			<div class="form" style="width: auto">
				<div class="form-row">
					<div class="form-unit form-divided form-divided-left" style="border-bottom: 1px solid gray">
						<input class="input-text selectable" 
								style="
									width: 280px;
									color: #000;
									padding: 0 0.75em 0.1em 0;
									height: auto;
									border-width: 0px;
									font-size: 16px;
									border-bottom: 0px solid #8b8b8b;
									border-style: solid;
									border-color: #808080;
									border-radius: 0px;
									background: #ffffff;
									box-shadow: none;
									box-sizing: border-box;
									transition: all .2s linear;"
								type="text" placeholder="Select location" data-drop-type="second">
						<a class="input-select-btn dropdown-btn-wrapper"><i class="fas fa-sort-down dropdown-icon"></i></a>
						<a class="input-select-btn input-select-btn-clear clear-selection second"><i class="fas fa-times dropdown-icon"></i></a>
					</div>
				</div>
			</div>
			<div class="selectable-dropdown-wrapper" data-drop-type="second" style="top: 275px !important;
				left: -7px !important;
				width: 279px !important">
				<ul class="selectable-dropdown">
					<li class="selectable-dropdown-each" data-loc-name="Agence Zellik" data-loc="4">Agence Zellik</li>
					<li class="selectable-dropdown-each" data-loc-name="Livraison (+ 75€)" data-loc="5">Livraison (+ 75€)</li>
					<li class="selectable-dropdown-each selectable-noresult">No results found</li>
				</ul>
			</div>

			

			<div style="font-size: 12px; color: gray; margin-top: 20px">Date de départ</div>
			<div id="start-label-4searchpad" style="font-size: 16px; border-bottom: 1px solid gray">Juin 16 5:00 AM</div>

			<div style="font-size: 12px; color: gray; margin-top: 20px">Date de retour</div>
			<div id="end-label-4searchpad" style="font-size: 16px; border-bottom: 1px solid gray">Juin 16 5:00 AM</div>

			<input type="submit" onclick="javascript:searching_submit_form()" style="background: #FF5F01; padding: 20px; border-radius: 5px; margin-top: 70px" value="Afficher la voiture" />

		</div>

		<div style="width: 800px">
			<div style="text-align: center;padding-top: 50px;">
				<div id="rangepicker"></div>
			</div>
			<div style="display: flex; padding: 20px 57px">
				<div style="display: flex; width: 50%">
					<div id="selecting-start-dttm">16 | Juin</div>
					<div style="width: 150px; margin-left: 10px">
						<div class="container">
							<select id="start-tm-select" class="custom-select">
								<option value="12:00 AM"> 12:00 AM</option>
								<option value="12:30 AM"> 12:30 AM</option>
								<option value="01:00 AM"> 01:00 AM</option>
								<option value="01:30 AM"> 01:30 AM</option>
								<option value="02:00 AM"> 02:00 AM</option>
								<option value="02:30 AM"> 02:30 AM</option>
								<option value="03:00 AM"> 03:00 AM</option>
								<option value="03:30 AM"> 03:30 AM</option>
								<option value="04:00 AM"> 04:00 AM</option>
								<option value="04:30 AM"> 04:30 AM</option>
								<option value="05:00 AM"> 05:00 AM</option>
								<option value="05:30 AM"> 05:30 AM</option>
								<option value="06:00 AM"> 06:00 AM</option>
								<option value="06:30 AM"> 06:30 AM</option>
								<option value="07:00 AM"> 07:00 AM</option>
								<option value="07:30 AM"> 07:30 AM</option>
								<option value="08:00 AM"> 08:00 AM</option>
								<option value="08:30 AM"> 08:30 AM</option>
								<option value="09:00 AM"> 09:00 AM</option>
								<option value="09:30 AM"> 09:30 AM</option>
								<option value="10:00 AM"> 10:00 AM</option>
								<option value="10:30 AM"> 10:30 AM</option>
								<option value="11:00 AM"> 11:00 AM</option>
								<option value="11:30 AM"> 11:30 AM</option>
								<option value="12:00 PM"> 12:00 PM</option>
								<option value="12:30 PM"> 12:30 PM</option>
								<option value="01:00 PM"> 01:00 PM</option>
								<option value="01:30 PM"> 01:30 PM</option>
								<option value="02:00 PM"> 02:00 PM</option>
								<option value="02:30 PM"> 02:30 PM</option>
								<option value="03:00 PM"> 03:00 PM</option>
								<option value="03:30 PM"> 03:30 PM</option>
								<option value="04:00 PM"> 04:00 PM</option>
								<option value="04:30 PM"> 04:30 PM</option>
								<option value="05:00 PM"> 05:00 PM</option>
								<option value="05:30 PM"> 05:30 PM</option>
								<option value="06:00 PM"> 06:00 PM</option>
								<option value="06:30 PM"> 06:30 PM</option>
								<option value="07:00 PM"> 07:00 PM</option>
								<option value="07:30 PM"> 07:30 PM</option>
								<option value="08:00 PM"> 08:00 PM</option>
								<option value="08:30 PM"> 08:30 PM</option>
								<option value="09:00 PM"> 09:00 PM</option>
								<option value="09:30 PM"> 09:30 PM</option>
								<option value="10:00 PM"> 10:00 PM</option>
								<option value="10:30 PM"> 10:30 PM</option>
								<option value="11:00 PM"> 11:00 PM</option>
								<option value="11:30 PM"> 11:30 PM</option>

							</select>
						</div>
					</div>
				</div>
				<div style="display: flex; width: 50%">
					<div id="selecting-end-dttm" style="margin-left: 20px">16 | Juin</div>
					<div style="width: 150px; margin-left: 10px">
						<div class="container">
							<select id="end-tm-select" class="custom-select">
								<option value="12:00 AM"> 12:00 AM</option>
								<option value="12:30 AM"> 12:30 AM</option>
								<option value="01:00 AM"> 01:00 AM</option>
								<option value="01:30 AM"> 01:30 AM</option>
								<option value="02:00 AM"> 02:00 AM</option>
								<option value="02:30 AM"> 02:30 AM</option>
								<option value="03:00 AM"> 03:00 AM</option>
								<option value="03:30 AM"> 03:30 AM</option>
								<option value="04:00 AM"> 04:00 AM</option>
								<option value="04:30 AM"> 04:30 AM</option>
								<option value="05:00 AM"> 05:00 AM</option>
								<option value="05:30 AM"> 05:30 AM</option>
								<option value="06:00 AM"> 06:00 AM</option>
								<option value="06:30 AM"> 06:30 AM</option>
								<option value="07:00 AM"> 07:00 AM</option>
								<option value="07:30 AM"> 07:30 AM</option>
								<option value="08:00 AM"> 08:00 AM</option>
								<option value="08:30 AM"> 08:30 AM</option>
								<option value="09:00 AM"> 09:00 AM</option>
								<option value="09:30 AM"> 09:30 AM</option>
								<option value="10:00 AM"> 10:00 AM</option>
								<option value="10:30 AM"> 10:30 AM</option>
								<option value="11:00 AM"> 11:00 AM</option>
								<option value="11:30 AM"> 11:30 AM</option>
								<option value="12:00 PM"> 12:00 PM</option>
								<option value="12:30 PM"> 12:30 PM</option>
								<option value="01:00 PM"> 01:00 PM</option>
								<option value="01:30 PM"> 01:30 PM</option>
								<option value="02:00 PM"> 02:00 PM</option>
								<option value="02:30 PM"> 02:30 PM</option>
								<option value="03:00 PM"> 03:00 PM</option>
								<option value="03:30 PM"> 03:30 PM</option>
								<option value="04:00 PM"> 04:00 PM</option>
								<option value="04:30 PM"> 04:30 PM</option>
								<option value="05:00 PM"> 05:00 PM</option>
								<option value="05:30 PM"> 05:30 PM</option>
								<option value="06:00 PM"> 06:00 PM</option>
								<option value="06:30 PM"> 06:30 PM</option>
								<option value="07:00 PM"> 07:00 PM</option>
								<option value="07:30 PM"> 07:30 PM</option>
								<option value="08:00 PM"> 08:00 PM</option>
								<option value="08:30 PM"> 08:30 PM</option>
								<option value="09:00 PM"> 09:00 PM</option>
								<option value="09:30 PM"> 09:30 PM</option>
								<option value="10:00 PM"> 10:00 PM</option>
								<option value="10:30 PM"> 10:30 PM</option>
								<option value="11:00 PM"> 11:00 PM</option>
								<option value="11:30 PM"> 11:30 PM</option>

							</select>
						</div>
					</div>
				</div>
			</div>

			<div style="display: flex; padding: 0px 57px 90px 57px">
				<div style="display: flex">
					<div style="padding-left: 20px">12:00</div>
					<div style="width: 233px; height: 14px; border-bottom: 5px solid #ff5f00; margin-left: 10px;">
					</div>
				</div>
				<div style="display: flex; margin-left: 53px">
					<div style="padding-left: 20px">12:00</div>
					<div style="width: 233px; height: 14px; border-bottom: 5px solid #ff5f00; margin-left: 10px;">
					</div>
				</div>
			</div>


			<div style="padding: 20px 60px; display: none">

				<label for="time" style="margin-right: 5px">Start Time</label>
				<input type="time" name="start-time" id="start-time" />

				<label for="time" style="margin-left: 5px">End Time</label>
				<input type="time" name="end-time" id="end-time" />
			</div>
		</div>
	</div>
</div>
<!-- </form> -->



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
