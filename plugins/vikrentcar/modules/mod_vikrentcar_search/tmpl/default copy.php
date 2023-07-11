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

$heading_text = $params->get('heading_text');

/**
 * @wponly 	the AJAX requests below require the Itemid for JRoute
 */
?>

<div class="vrcdivsearch vrcdivsearchmodule <?php echo $params->get('orientation') == 'horizontal' ? 'vrc-searchmod-wrap-horizontal' : 'vrc-searchmod-wrap-vertical'; ?>">
<div class="rental-form">
  <div class="calendar">
    <div class="rectangle-6"></div>
    <div class="calendar2">
      <div class="rectangle-62"></div>
      <div class="month">Juin 2023</div>
      <div class="rectangle-22"></div>
      <div class="rectangle-23"></div>
      <div class="days">
        <div class="subtitle">LU</div>
        <div class="subtitle2">MA</div>
        <div class="subtitle3">ME</div>
        <div class="subtitle4">JE</div>
        <div class="subtitle5">VE</div>
        <div class="subtitle6">SA</div>
        <div class="subtitle7">DI</div>
      </div>
      <div class="line-1">
        <div class="subtitle8">1</div>
        <div class="subtitle9">2</div>
        <div class="subtitle10">3</div>
        <div class="subtitle11">4</div>
      </div>
      <div class="line-2">
        <div class="subtitle12">5</div>
        <div class="subtitle13">6</div>
        <div class="subtitle14">7</div>
        <div class="subtitle15">8</div>
        <div class="subtitle16">9</div>
        <div class="subtitle17">10</div>
        <div class="subtitle18">11</div>
      </div>
      <div class="line-3">
        <div class="subtitle19">12</div>
        <div class="subtitle20">13</div>
        <div class="subtitle21">14</div>
        <div class="subtitle22">15</div>
        <div class="subtitle23">16</div>
        <div class="subtitle24">17</div>
        <div class="subtitle25">18</div>
      </div>
      <div class="line-4">
        <div class="subtitle26">19</div>
        <div class="subtitle27">20</div>
        <div class="subtitle28">21</div>
        <div class="subtitle29">22</div>
        <div class="subtitle30">23</div>
        <div class="subtitle31">24</div>
        <div class="subtitle32">25</div>
      </div>
      <div class="line-5">
        <div class="subtitle33">26</div>
        <div class="subtitle34">27</div>
        <div class="subtitle35">28</div>
        <div class="subtitle36">29</div>
        <div class="subtitle37">30</div>
      </div>
      <div class="line-7"></div>
    </div>
    <div class="calendar3">
      <div class="rectangle-62"></div>
      <div class="month">Juin 2023</div>
      <div class="days">
        <div class="subtitle">LU</div>
        <div class="subtitle2">MA</div>
        <div class="subtitle3">ME</div>
        <div class="subtitle4">JE</div>
        <div class="subtitle5">VE</div>
        <div class="subtitle6">SA</div>
        <div class="subtitle7">DI</div>
      </div>
      <div class="line-1">
        <div class="subtitle8">1</div>
        <div class="subtitle9">2</div>
        <div class="subtitle10">3</div>
        <div class="subtitle11">4</div>
      </div>
      <div class="line-2">
        <div class="subtitle12">5</div>
        <div class="subtitle13">6</div>
        <div class="subtitle14">7</div>
        <div class="subtitle15">8</div>
        <div class="subtitle16">9</div>
        <div class="subtitle17">10</div>
        <div class="subtitle18">11</div>
      </div>
      <div class="line-3">
        <div class="subtitle19">12</div>
        <div class="subtitle20">13</div>
        <div class="subtitle21">14</div>
        <div class="subtitle22">15</div>
        <div class="subtitle23">16</div>
        <div class="subtitle24">17</div>
        <div class="subtitle25">18</div>
      </div>
      <div class="line-4">
        <div class="subtitle26">19</div>
        <div class="subtitle27">20</div>
        <div class="subtitle28">21</div>
        <div class="subtitle29">22</div>
        <div class="subtitle30">23</div>
        <div class="subtitle31">24</div>
        <div class="subtitle32">25</div>
      </div>
      <div class="line-5">
        <div class="subtitle33">26</div>
        <div class="subtitle34">27</div>
        <div class="subtitle35">28</div>
        <div class="subtitle36">29</div>
        <div class="subtitle37">30</div>
      </div>
      <div class="line-7"></div>
    </div>
    <div class="time">
      <div class="time-slider">
        <div class="rectangle"></div>
        <div class="rectangle2"></div>
        <div class="time2">12:00</div>
      </div>
      <div class="time3">16 juin | 12:00</div>
    </div>
    <div class="time">
      <div class="time-slider">
        <div class="rectangle3"></div>
        <div class="rectangle4"></div>
        <div class="time4">12:00</div>
      </div>
      <div class="time5">16 juin | 12:00</div>
    </div>
    <svg class="icon-cross" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M0.443783 19.5561C-0.147928 18.9645 -0.147928 18.0051 0.443783 17.4134L17.4134 0.443783C18.0051 -0.147928 18.9645 -0.147928 19.5561 0.443783C20.148 1.03548 20.148 1.99483 19.5561 2.58652L2.58652 19.5561C1.99483 20.148 1.03548 20.148 0.443783 19.5561Z" fill="#FF5F01" />
      <path fill-rule="evenodd" clip-rule="evenodd" d="M0.443783 0.443783C1.03548 -0.147928 1.99483 -0.147928 2.58652 0.443783L19.5561 17.4134C20.148 18.0051 20.148 18.9645 19.5561 19.5561C18.9645 20.148 18.0051 20.148 17.4134 19.5561L0.443783 2.58652C-0.147928 1.99483 -0.147928 1.03548 0.443783 0.443783Z" fill="#FF5F01" />
    </svg>
  </div>
  <div class="rectangle-5"></div>
  <div class="button-normal">
    <div class="rectangle-63"></div>
    <div class="afficher-la-voiture">Afficher la voiture</div>
  </div>
  <div class="subtitle38">Louer la voiture</div>
  <div class="points">
    <div class="point-1">
      <div class="decor"></div>
      <div class="pickup-livraison">Pickup/Livraison</div>
      <div class="a-roport-domicile-adresse-ou-agence"> Aéroport, domicile, adresse ou agence </div>
      <svg class="icon" width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.177513 7.82246C-0.0591711 7.58579 -0.0591711 7.20203 0.177513 6.96537L6.96537 0.177513C7.20203 -0.0591711 7.58579 -0.0591711 7.82246 0.177513C8.05918 0.414192 8.05918 0.79793 7.82246 1.03461L1.03461 7.82246C0.79793 8.05918 0.414192 8.05918 0.177513 7.82246Z" fill="#111111" />
        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.177513 0.177513C0.414192 -0.0591711 0.79793 -0.0591711 1.03461 0.177513L7.82246 6.96537C8.05918 7.20203 8.05918 7.58579 7.82246 7.82246C7.58579 8.05918 7.20203 8.05918 6.96537 7.82246L0.177513 1.03461C-0.0591711 0.79793 -0.0591711 0.414192 0.177513 0.177513Z" fill="#111111" />
      </svg>
    </div>
    <div class="point-2">
      <div class="decor2"></div>
      <div class="date-de-d-part">Date de départ</div>
      <div class="juin-16-5-00-am">Juin 16 5:00 AM</div>
    </div>
    <div class="point-3">
      <div class="decor3"></div>
      <div class="date-de-retour">Date de retour</div>
      <div class="juin-24-8-30-am">Juin 24 8:30 AM</div>
    </div>
    <div class="point-5">
      <div class="decor4"></div>
      <div class="retour">Retour</div>
      <div class="a-roport-domicile-adresse-ou-agence2"> Aéroport, domicile, adresse ou agence </div>
      <svg class="icon2" width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.177513 7.82246C-0.0591711 7.58579 -0.0591711 7.20203 0.177513 6.96537L6.96537 0.177513C7.20203 -0.0591711 7.58579 -0.0591711 7.82246 0.177513C8.05918 0.414192 8.05918 0.79793 7.82246 1.03461L1.03461 7.82246C0.79793 8.05918 0.414192 8.05918 0.177513 7.82246Z" fill="#111111" />
        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.177513 0.177513C0.414192 -0.0591711 0.79793 -0.0591711 1.03461 0.177513L7.82246 6.96537C8.05918 7.20203 8.05918 7.58579 7.82246 7.82246C7.58579 8.05918 7.20203 8.05918 6.96537 7.82246L0.177513 1.03461C-0.0591711 0.79793 -0.0591711 0.414192 0.177513 0.177513Z" fill="#111111" />
      </svg>
    </div>
  </div>
</div>



</div>

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
