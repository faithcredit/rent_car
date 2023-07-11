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
$cats = $this->cats;
$carats = $this->carats;
$optionals = $this->optionals;
$places = $this->places;

JHtml::_('jquery.framework', true, true);
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.sortable.min.js');

JText::script('VRCDELCONFIRM');
JText::script('VRC_SAVE_COPY');
JText::script('VRC_CONF_RESET_DISTFEAT');

$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadSelect2();
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VRC_SITE_URI.'resources/jquery.fancybox.js');
$currencysymb = VikRentCar::getCurrencySymb(true);
$arrcats = array();
$arrcarats = array();
$arropts = array();
if (count($row)) {
	$oldcats = explode(";", $row['idcat']);
	foreach ($oldcats as $oc) {
		if (!empty($oc)) {
			$arrcats[$oc] = $oc;
		}
	}
	$oldcarats = explode(";", $row['idcarat']);
	foreach ($oldcarats as $ocr) {
		if (!empty($ocr)) {
			$arrcarats[$ocr] = $ocr;
		}
	}
	$oldopts = explode(";", $row['idopt']);
	foreach ($oldopts as $oopt) {
		if (!empty($oopt)) {
			$arropts[$oopt] = $oopt;
		}
	}
}
if (is_array($cats)) {
	$wcats = "<select id=\"ccat\" name=\"ccat[]\" multiple=\"multiple\" size=\"".(count($cats) + 1)."\">";
	foreach ($cats as $cat) {
		$wcats .= "<option value=\"".$cat['id']."\"".(array_key_exists($cat['id'], $arrcats) ? " selected=\"selected\"" : "").">".$cat['name']."</option>\n";
	}
	$wcats .= "</select>\n";
} else {
	$wcats = "";
}
if (is_array($places)) {
	$wplaces = "<select name=\"cplace[]\" id=\"cplace\" multiple=\"multiple\" size=\"".(count($places) + 1)."\" onchange=\"vrcSelDropLocation();\">";
	$wretplaces = "<select name=\"cretplace[]\" id=\"cretplace\" multiple=\"multiple\" size=\"".(count($places) + 1)."\">";
	$actplac = count($row) ? explode(";", $row['idplace']) : array();
	$actretplac = count($row) ? explode(";", $row['idretplace']) : array();
	foreach ($places as $place) {
		$wplaces .= "<option value=\"".$place['id']."\"".(in_array($place['id'], $actplac) ? " selected=\"selected\"" : "").">".$place['name']."</option>\n";
		$wretplaces .= "<option value=\"".$place['id']."\"".(in_array($place['id'], $actretplac) ? " selected=\"selected\"" : "").">".$place['name']."</option>\n";
	}
	$wplaces .= "</select>\n";
	$wretplaces .= "</select>\n";
} else {
	$wplaces = "";
	$wretplaces = "";
}
if (is_array($carats)) {
	$wcarats = "<table><tr><td valign=\"top\">";
	$nn = 0;
	$jj = 0;
	foreach ($carats as $carat) {
		$wcarats .= "<div class=\"vrc-mngcar-serv-entry\"><input type=\"checkbox\" name=\"ccarat[]\" id=\"carat".$carat['id']."\" value=\"".$carat['id']."\"".(array_key_exists($carat['id'], $arrcarats) ? " checked=\"checked\"" : "")."/> <label for=\"carat".$carat['id']."\">".$carat['name']."</label></div>\n";
		$nn++;
		if (($nn % 3) == 0) {
			$jj++;
			if (($jj % 3) == 0) {
				$wcarats .= "</td></tr><td valign=\"top\">";
			} else {
				$wcarats .= "</td><td valign=\"top\">\n";
			}
		}
	}
	$wcarats .= "</td></tr></table>\n";
} else {
	$wcarats = "";
}
if (is_array($optionals)) {
	$woptionals = "<table><tr><td valign=\"top\">";
	$nn = 0;
	$jj = 0;
	foreach ($optionals as $optional) {
		$woptionals .= "<div class=\"vrc-mngcar-serv-entry\"><input type=\"checkbox\" name=\"coptional[]\" id=\"opt".$optional['id']."\" value=\"".$optional['id']."\"".(array_key_exists($optional['id'], $arropts) ? " checked=\"checked\"" : "")."/> <label for=\"opt".$optional['id']."\">".$optional['name']." ".$currencysymb."".$optional['cost']."</label></div>\n";
		$nn++;
		if (($nn % 3) == 0) {
			$jj++;
			if (($jj % 3) == 0) {
				$woptionals .= "</td></tr><td valign=\"top\">";
			} else {
				$woptionals .= "</td><td valign=\"top\">\n";
			}
		}
	}
	$woptionals .= "</td></tr></table>\n";
} else {
	$woptionals = "";
}
//more images
$morei = count($row) ? explode(';;', $row['moreimgs']) : array();
$actmoreimgs = "";
if (count($morei)) {
	foreach ($morei as $ki => $mi) {
		if (!empty($mi)) {
			$actmoreimgs .= '<li class="vrc-editcar-currentphoto">';
			$actmoreimgs .= '<a href="'.VRC_ADMIN_URI.'resources/big_'.$mi.'" target="_blank" class="vrcmodal"><img src="'.VRC_ADMIN_URI.'resources/thumb_'.$mi.'" class="maxfifty"/></a>';
			$actmoreimgs .= '<a class="vrc-rm-extraimg-lnk" onclick="return confirm(Joomla.JText._(\'VRCDELCONFIRM\'));" href="index.php?option=com_vikrentcar&task=removemoreimgs&carid='.$row['id'].'&imgind='.$ki.'"><i class="'.VikRentCarIcons::i('times-circle').'"></i></a>';
			$actmoreimgs .= '<input type="hidden" name="imgsorting[]" value="'.$mi.'"/>';
			$actmoreimgs .= '</li>';
		}
	}
}
//end more images
$car_params = count($row) && !empty($row['params']) ? json_decode($row['params'], true) : array('sdailycost' => '', 'email' => '', 'custptitle' => '', 'custptitlew' => '', 'metakeywords' => '', 'metadescription' => '', 'shourlycal' => '');
if (!array_key_exists('features', $car_params)) {
	$car_params['features'] = array();
}
if (!array_key_exists('damages', $car_params)) {
	$car_params['damages'] = array();
	if (count($row)) {
		for ($i=1; $i <= $row['units']; $i++) {
			$car_params['damages'][$i] = array();
		}
	}
}
if (!(count($car_params['features']) > 0)) {
	$default_features = VikRentCar::getDefaultDistinctiveFeatures();
	if (count($row)) {
		for ($i=1; $i <= $row['units']; $i++) {
			$car_params['features'][$i] = $default_features;
		}
	}
}
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
?>
<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'clone_car') {
		if (confirm(Joomla.JText._('VRC_SAVE_COPY') + '?')) {
			Joomla.submitform(task, document.adminForm);
		} else {
			return false;
		}
	} else {
		Joomla.submitform(task, document.adminForm);
	}
}

function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		document.getElementById('resizesel').style.display='inline-block';
	} else {
		document.getElementById('resizesel').style.display='none';
	}
	return true;
}
function vrcSelDropLocation() {
	var picksel = document.getElementById('cplace');
	var dropsel = document.getElementById('cretplace');
	for (i = 0; i < picksel.length; i++) {
		if (picksel.options[i].selected == false) {
			if (dropsel.options[i].selected == true) {
				dropsel.options[i].selected = false;
			}
		} else {
			if (dropsel.options[i].selected == false) {
				dropsel.options[i].selected = true;
			}
		}
	}
	// trigger the change event for select2
	jQuery('#cretplace').trigger('change');
}
function showResizeSelMore() {
	if (document.adminForm.autoresizemore.checked == true) {
		document.getElementById('resizeselmore').style.display='inline-block';
	} else {
		document.getElementById('resizeselmore').style.display='none';
	}
	return true;
}
function addMoreImages() {
	var ni = document.getElementById('myDiv');
	var numi = document.getElementById('moreimagescounter');
	var num = (document.getElementById('moreimagescounter').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<input type=\'file\' name=\'cimgmore[]\' size=\'35\'/><br/>';
	ni.appendChild(newdiv);
}
var cur_units = <?php echo count($row) ? $row['units'] : 1; ?>;

function vrcCountCalendars() {
	return jQuery('input[name="calendars[name][]"]:visible').length;
}

function vrcAddCalendar() {
	var cal_inputs = jQuery('.vrc-newcalendars-ghost').html();
	jQuery('.vrc-newcalendars-wrap').append('<div class="vrc-newcalendars-block">' + cal_inputs + '</div>');
	jQuery('.vrc-newcalendars-wrap').find('input[type="text"]').prop('disabled', false);
	jQuery('#vrc-import-calendars-count').text(vrcCountCalendars());
}

function vrcRemoveCalendar(id) {
	if (confirm(Joomla.JText._('VRCDELCONFIRM'))) {
		jQuery('.vrc-param-container[data-calendarid="' + id + '"]').closest('.vrc-newcalendars-block').remove();
		jQuery('#vrc-import-calendars-count').text(vrcCountCalendars());
	}
}

function vrcUnsetCalendar(btn) {
	jQuery(btn).closest('.vrc-newcalendars-block').remove();
	jQuery('#vrc-import-calendars-count').text(vrcCountCalendars());
}

var vrc_lower_cunits_distf_auto = -1;

jQuery(function() {
	jQuery(".vrc-sortable").sortable({
		helper: 'clone'
	});
	jQuery(".vrc-sortable").disableSelection();
	jQuery('#ccat, #cplace, #cretplace').select2();
	jQuery('.vrc-select-all').click(function() {
		var nextsel = jQuery(this).next("select");
		nextsel.find("option").prop('selected', true);
		nextsel.trigger('change');
	});
	jQuery('.vrc-features-btn').click(function() {
		jQuery(this).toggleClass('vrc-features-btn-active');
		jQuery('.vrc-distfeatures-block').fadeToggle();
	});
	jQuery('#vrc-units-inp').change(function() {
		var to_units = parseInt(jQuery(this).val());
		if (to_units > cur_units) {
			var diff_units = (to_units - cur_units);
			for (var i = 1; i <= diff_units; i++) {
				var unit_html = "<div class=\"vrc-cunit-features-cont\" id=\"cunit-features-"+(i + cur_units)+"\">"+
								"	<span class=\"vrc-cunit-num\"><?php echo addslashes(JText::_('VRCDISTFEATURECUNIT')); ?>"+(i + cur_units)+"</span>"+
								"	<div class=\"vrc-cunit-features\">"+
								"		<div class=\"vrc-cunit-feature\">"+
								"			<input type=\"text\" name=\"feature-name"+(i + cur_units)+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VRCDISTFEATURETXT'); ?>\"/>"+
								"			<input type=\"hidden\" name=\"feature-lang"+(i + cur_units)+"[]\" value=\"\"/>"+
								"			<input type=\"text\" name=\"feature-value"+(i + cur_units)+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VRCDISTFEATUREVAL'); ?>\"/>"+
								"			<span class=\"vrc-feature-remove\"><i class=\"<?php echo VikRentCarIcons::i('times-circle'); ?>\"></i></span>"+
								"		</div>"+
								"		<span class=\"vrc-feature-add\"><i class=\"<?php echo VikRentCarIcons::i('plus-circle'); ?>\"></i> <?php echo addslashes(JText::_('VRCDISTFEATUREADD')); ?></span>"+
								"	</div>"+
								"</div>";
				jQuery('.vrc-distfeatures-cont').append(unit_html);
			}
			cur_units = to_units;
		} else if (to_units < cur_units) {
			if (vrc_lower_cunits_distf_auto < 0) {
				if (confirm(Joomla.JText._('VRC_CONF_RESET_DISTFEAT'))) {
					vrc_lower_cunits_distf_auto = 1;
				} else {
					vrc_lower_cunits_distf_auto = 0;
				}
			}
			if (vrc_lower_cunits_distf_auto > 0) {
				for (var i = cur_units; i > to_units; i--) {
					jQuery('#cunit-features-'+i).remove();
				}
			}
			cur_units = to_units;
		}
	});
	jQuery(document.body).on('click', '.vrc-feature-add', function() {
		var cfeature_id = jQuery(this).parent('div').parent('div').attr('id').split('cunit-features-');
		if (cfeature_id[1].length) {
			jQuery(this).before("<div class=\"vrc-cunit-feature\">"+
								"	<input type=\"text\" name=\"feature-name"+cfeature_id[1]+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VRCDISTFEATURETXT'); ?>\"/>"+
								"	<input type=\"hidden\" name=\"feature-lang"+cfeature_id[1]+"[]\" value=\"\"/>"+
								"	<input type=\"text\" name=\"feature-value"+cfeature_id[1]+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VRCDISTFEATUREVAL'); ?>\"/>"+
								"	<span class=\"vrc-feature-remove\"><i class=\"<?php echo VikRentCarIcons::i('times-circle'); ?>\"></i></span>"+
								"</div>"
								);
		}
	});
	jQuery(document.body).on('click', '.vrc-feature-remove', function() {
		jQuery(this).parent('div').remove();
	});
	jQuery(document.body).on('click', '.vrc-open-damages', function() {
		var cunit_id = jQuery(this).parent('div').attr('id').split('cunit-features-');
		if (cunit_id[1].length && jQuery('#vrc-feature-damage-block-'+cunit_id[1])) {
			var cname = jQuery('#cname').val();
			jQuery.fancybox.open({
				src: '#vrc-feature-damage-block-'+cunit_id[1],
				type: 'inline',
				opts: {
					caption: (cname.length ? cname+' - ' : '')+jQuery(this).parent('div').find('.vrc-cunit-num').text() + ' - <?php echo addslashes(JText::_('VRCDISTFEATURECDAMAGES')); ?>'
				}
			});
		}
	});
	jQuery(document.body).on('click', '.vrc-feature-damage-imgcont img', function(e) {
		var click_x = (e.pageX - jQuery(this).parent('div').offset().left);
		var click_y = (e.pageY - jQuery(this).parent('div').offset().top);
		var cunit_id = jQuery(this).parent('div').closest('div.vrc-feature-damage-block').attr('id').split('vrc-feature-damage-block-');
		if (cunit_id[1].length) {
			jQuery('#vrc-no-damage-'+cunit_id[1]).remove();
			var tot_damages = jQuery('.vrc-feature-car-damage-'+cunit_id[1]).length;
			var damage_ind = !(tot_damages > 0) ? 1 : (tot_damages + 1);
			jQuery(this).parent('div').append("<span class=\"vrc-feature-damage-circle\" id=\"vrc-damage-circle-"+cunit_id[1]+"-"+damage_ind+"\" style=\"left: "+click_x+"px; top: "+click_y+"px;\">"+damage_ind+"</span>");
			jQuery(this).parent('div').next('div.vrc-feature-damage-actions').prepend("<div class=\"vrc-feature-car-damage vrc-feature-car-damage-"+cunit_id[1]+"\" id=\"vrc-feature-car-damage-"+cunit_id[1]+"-"+damage_ind+"\">"+
																						"	<span class=\"vrc-feature-car-damage-count\">"+damage_ind+"</span>"+
																						"	<span class=\"vrc-feature-damage-remove\"><i class=\"<?php echo VikRentCarIcons::i('times-circle'); ?>\"></i></span>"+
																						"	<div class=\"vrc-feature-car-damage-details\">"+
																						"		<span class=\"vrc-feature-car-damage-detail\"><?php echo addslashes(JText::_('VRCDISTFEATURECDAMAGENOTES')); ?></span>"+
																						"		<span class=\"vrc-feature-car-damage-cont\"><textarea name=\"car-"+cunit_id[1]+"-damage[]\"></textarea></span>"+
																						"		<input type=\"hidden\" name=\"car-"+cunit_id[1]+"-damage-x[]\" value=\""+click_x+"\"/>"+
																						"		<input type=\"hidden\" name=\"car-"+cunit_id[1]+"-damage-y[]\" value=\""+click_y+"\"/>"+
																						"	</div>"+
																						"</div>");
		}
	});
	jQuery(document.body).on('click', '.vrc-feature-damage-remove', function() {
		var id_damage = jQuery(this).parent('div').attr('id').split('vrc-feature-car-damage-');
		var cunit_id = id_damage[1].split('-');
		jQuery('#vrc-damage-circle-'+id_damage[1]).remove();
		jQuery(this).parent('div').remove();
		var tot_damages = jQuery('.vrc-feature-car-damage-'+cunit_id[0]).length;
		if (tot_damages < 1) {
			jQuery('#vrc-feature-damage-block-'+cunit_id[0]).find('div.vrc-feature-damage-actions').html("<span class=\"vrc-no-damage\" id=\"vrc-no-damage-"+cunit_id[0]+"\"><?php echo addslashes(JText::_('VRCDISTFEATURENODAMAGE')); ?></span>");
		}
	});
	if (window.location.hash == '#distfeatures') {
		jQuery('.vrc-features-btn').trigger('click');
	}
});
</script>
<?php
$vrc_app->prepareModalBox('.vrcmodal', '', true);
?>
<input type="hidden" value="0" id="moreimagescounter" />

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARFIVE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="cname" id="cname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCAREIGHT'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('cavail', JText::_('VRYES'), JText::_('VRNO'), ((count($row) && intval($row['avail']) == 1) || !count($row) ? 'yes' : 0), 'yes', 0); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARNINE'); ?></div>
							<div class="vrc-param-setting">
								<input type="number" min="1" name="units" id="vrc-units-inp" value="<?php echo count($row) ? (int)$row['units'] : ''; ?>" size="3" onfocus="this.select();"/>
								<span class="vrc-features-btn btn vrc-config-btn"><?php VikRentCarIcons::e('cubes'); ?> <?php echo JText::_('VRCDISTFEATURESMNG'); ?></span>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARSIX'); ?></div>
							<div class="vrc-param-setting">
								<div class="vrc-param-setting-block">
									<?php echo (count($row) && !empty($row['img']) && file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$row['img']) ? "<a href=\"".VRC_ADMIN_URI."resources/".$row['img']."\" target=\"_blank\" class=\"vrcmodal vrc-car-img-modal\"><i class=\"" . VikRentCarIcons::i('image') . "\"></i>" . $row['img'] . "</a>" : ""); ?>
									<input type="file" name="cimg" size="35"/>
								</div>
								<div class="vrc-param-setting-block">
									<span class="vrc-resize-lb-cont">
										<label for="autoresize" style="display: inline-block;"><?php echo JText::_('VRNEWOPTNINE'); ?></label>
										<input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/>
									</span>
									<span id="resizesel" style="display: none;"><span><?php echo JText::_('VRNEWOPTTEN'); ?></span><input class="vrc-small-input" type="text" name="resizeto" value="250" size="3"/> px</span>
								</div>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label">
								<div class="vrc-param-label-top">
									<span><?php echo JText::_('VRMOREIMAGES'); ?></span>
									<a class="btn vrc-config-btn" href="javascript: void(0);" onclick="addMoreImages();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRADDIMAGES'); ?></a>
								</div>
							</div>
							<div class="vrc-param-setting">
								<div class="vrc-param-setting-block">
									<ul class="vrc-sortable"><?php echo $actmoreimgs; ?></ul>
									<input type="file" name="cimgmore[]" size="35"/>
									<div id="myDiv" style="display: block;"></div>
								</div>
								<div class="vrc-param-setting-block">
									<span class="vrc-resize-lb-cont">
										<label for="autoresizemore" style="display: inline-block;"><?php echo JText::_('VRRESIZEIMAGES'); ?></label> 
										<input type="checkbox" id="autoresizemore" name="autoresizemore" value="1" onclick="showResizeSelMore();"/> 
									</span>
									<span id="resizeselmore" style="display: none;"><span><?php echo JText::_('VRNEWOPTTEN'); ?></span><input class="vrc-small-input" type="text" name="resizetomore" value="600" size="3"/> px</span>
								</div>
							</div>
						</div>
						<?php
						if (!empty($wcats)) {
							?>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARONE'); ?></div>
							<div class="vrc-param-setting"><?php echo $wcats; ?></div>
						</div>
							<?php
						}
						if (!empty($wplaces)) {
							?>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARTWO'); ?></div>
							<div class="vrc-param-setting">
								<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
								<?php echo $wplaces; ?>
							</div>
						</div>
							<?php
						}
						if (!empty($wretplaces)) {
							?>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARDROPLOC'); ?></div>
							<div class="vrc-param-setting">
								<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
								<?php echo $wretplaces; ?>
							</div>
						</div>
							<?php
						}
						if (!empty($wcarats)) {
							?>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARTHREE'); ?></div>
							<div class="vrc-param-setting"><?php echo $wcarats; ?></div>
						</div>
							<?php
						}
						if (!empty($woptionals)) {
							?>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARFOUR'); ?></div>
							<div class="vrc-param-setting"><?php echo $woptionals; ?></div>
						</div>
							<?php
						}
						?>
					</div>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRC_IMPORT_CALENDARS'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRC_IMPORT_CALENDARS'), 'content' => JText::_('VRC_IMPORT_CALENDARS_HELP'))); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php VikRentCarIcons::e('calendar'); ?> <?php echo JText::_('VRC_IMPORT_CALENDARS_COUNT'); ?></div>
							<div class="vrc-param-setting">
								<span class="label label-info" id="vrc-import-calendars-count"><?php echo count($this->importCalendars); ?></span>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"></div>
							<div class="vrc-param-setting">
								<button type="button" class="btn vrc-config-btn" onclick="vrcAddCalendar();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_IMPORT_CALENDARS_ADD'); ?></button>
							</div>
						</div>
					<?php
					foreach ($this->importCalendars as $import_calendar) {
						?>
						<div class="vrc-newcalendars-block">
							<div class="vrc-param-container" data-calendarid="<?php echo $import_calendar['id']; ?>">
								<div class="vrc-param-label"><?php echo JText::_('VRC_IMPORT_CALENDAR_NAME'); ?></div>
								<div class="vrc-param-setting">
									<input type="hidden" name="calendars[id][]" value="<?php echo $import_calendar['id']; ?>" />
									<input type="text" name="calendars[name][]" value="<?php echo $this->escape($import_calendar['name']); ?>" />
								</div>
							</div>
							<div class="vrc-param-container vrc-param-nested" data-calendarid="<?php echo $import_calendar['id']; ?>">
								<div class="vrc-param-label"><?php echo JText::_('VRC_IMPORT_CALENDAR_URL'); ?></div>
								<div class="vrc-param-setting">
									<input type="text" name="calendars[url][]" value="<?php echo $this->escape($import_calendar['url']); ?>" />
									<button type="button" class="btn btn-danger" onclick="vrcRemoveCalendar('<?php echo $import_calendar['id']; ?>');"><?php VikRentCarIcons::e('times-circle'); ?></button>
								</div>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vrc-newcalendars-ghost" style="display: none;">
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRC_IMPORT_CALENDAR_NAME'); ?></div>
								<div class="vrc-param-setting">
									<input type="text" name="calendars[name][]" value="" disabled />
								</div>
							</div>
							<div class="vrc-param-container vrc-param-nested">
								<div class="vrc-param-label"><?php echo JText::_('VRC_IMPORT_CALENDAR_URL'); ?></div>
								<div class="vrc-param-setting">
									<input type="text" name="calendars[url][]" value="" disabled />
									<button type="button" class="btn btn-danger" onclick="vrcUnsetCalendar(this);"><?php VikRentCarIcons::e('times-circle'); ?></button>
								</div>
							</div>
						</div>
						<div class="vrc-newcalendars-wrap"></div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="vrc-config-maintab-right">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCDESCRIPTIONS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container vrc-param-container-full">
							<div class="vrc-param-label"><?php echo JText::_('VRCSHORTDESCRIPTIONCAR'); ?></div>
							<div class="vrc-param-setting"><textarea name="short_info" rows="4" cols="60"><?php echo count($row) ? JHtml::_('esc_textarea', $row['short_info']) : ''; ?></textarea></div>
						</div>
						<div class="vrc-param-container vrc-param-container-full">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARSEVEN'); ?></div>
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
										echo $editor->display( "cdescr", (count($row) ? $row['info'] : ''), 350, 150, 40, 10 );
									} catch (Throwable $t) {
										echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
									}
								} else {
									// we cannot catch Fatal Errors in PHP 5.x
									echo $editor->display( "cdescr", (count($row) ? $row['info'] : ''), 350, 150, 40, 10 );
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCPARAMSCAR'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPARAMDAILYCOST'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('sdailycost', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$car_params['sdailycost'] : 0), 1, 0); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPARAMHOURLYCAL'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('shourlycal', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$car_params['shourlycal'] : 0), 1, 0); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPARAMREQINFO'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPARAMREQINFO'), 'content' => JText::_('VRCPARAMREQINFOHELP'))); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('reqinfo', JText::_('VRYES'), JText::_('VRNO'), (count($row) && isset($car_params['reqinfo']) ? (int)$car_params['reqinfo'] : 0), 1, 0); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTSTARTINGFROM'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCUSTSTARTINGFROM'), 'content' => JText::_('VRCUSTSTARTINGFROMHELP'))); ?></div>
							<div class="vrc-param-setting"><input type="number" step="any" name="startfrom" value="<?php echo count($row) && !is_null($row['startfrom']) ? (float)$row['startfrom'] : ''; ?>" style="width: 100px !important;"/> <?php echo $currencysymb; ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPARAMCAREMAIL'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPARAMCAREMAIL'), 'content' => JText::_('VRCPARAMCAREMAILHELP'))); ?></div>
							<div class="vrc-param-setting"><input type="text" id="car_email" name="email" value="<?php echo count($row) ? JHtml::_('esc_attr', $car_params['email']) : ''; ?>"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRC_CUSTOM_DMG_INSPECTION'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->getMediaField('inspection', (count($row) && !empty($car_params['inspection']) ? $car_params['inspection'] : null)); ?>
								<span class="vrc-param-setting-comment"><?php echo JText::_('VRC_CUSTOM_DMG_INSPECTION_HELP'); ?></span>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPARAMPAGETITLE'); ?></div>
							<div class="vrc-param-setting">
								<input type="text" id="custptitle" name="custptitle" value="<?php echo count($row) ? JHtml::_('esc_attr', $car_params['custptitle']) : ''; ?>"/>
							</div>
						</div>
						<div class="vrc-param-container vrc-param-child">
							<div class="vrc-param-label"></div>
							<div class="vrc-param-setting">
								<select name="custptitlew">
									<option value="before"<?php echo count($row) && $car_params['custptitlew'] == 'before' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCPARAMPAGETITLEBEFORECUR'); ?></option>
									<option value="after"<?php echo count($row) && $car_params['custptitlew'] == 'after' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCPARAMPAGETITLEAFTERCUR'); ?></option>
									<option value="replace"<?php echo count($row) && $car_params['custptitlew'] == 'replace' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCPARAMPAGETITLEREPLACECUR'); ?></option>
								</select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPARAMKEYWORDSMETATAG'); ?></div>
							<div class="vrc-param-setting"><textarea name="metakeywords" id="metakeywords" rows="3" cols="40"><?php echo count($row) ? JHtml::_('esc_textarea', $car_params['metakeywords']) : ''; ?></textarea></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPARAMDESCRIPTIONMETATAG'); ?></div>
							<div class="vrc-param-setting"><textarea name="metadescription" id="metadescription" rows="4" cols="40"><?php echo count($row) ? JHtml::_('esc_textarea', $car_params['metadescription']) : ''; ?></textarea></div>
						</div>
						<!-- @wponly  removed SEF alias field -->
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<div class="vrc-distfeatures-block">
		<div class="vrc-distfeatures-inner">
			<fieldset>
				<legend><?php echo JText::_('VRCDISTFEATURES'); ?></legend>
				<div class="vrc-distfeatures-cont">
				<?php
				$damage_img = VRC_SITE_URI.'helpers/car_damages/car_inspection.png';
				$cms_base_p = defined('ABSPATH') ? ABSPATH : JPATH_SITE;
				for ($i = 1; $i <= (count($row) ? $row['units'] : 1); $i++) {
					if (count($row) && !empty($car_params['inspection']) && is_file(JPath::clean($cms_base_p . '/' . $car_params['inspection']))) {
						$damage_img = JUri::root() . ltrim(str_replace('\\', '/', $car_params['inspection']), '/');
					}
					?>
					<div class="vrc-cunit-features-cont" id="cunit-features-<?php echo $i; ?>">
						<span class="vrc-cunit-num"><?php echo JText::_('VRCDISTFEATURECUNIT'); ?><?php echo $i; ?></span>
						<span class="vrc-open-damages"><?php echo JText::_('VRCDISTFEATURECDAMAGES'); ?></span>
						<div class="vrc-cunit-features">
					<?php
					if (array_key_exists($i, $car_params['features'])) {
						foreach ($car_params['features'][$i] as $fkey => $fval) {
							?>
							<div class="vrc-cunit-feature">
								<input type="text" name="feature-name<?php echo $i; ?>[]" value="<?php echo JHtml::_('esc_attr', JText::_($fkey)); ?>" size="20"/>
								<input type="hidden" name="feature-lang<?php echo $i; ?>[]" value="<?php echo JHtml::_('esc_attr', $fkey); ?>"/>
								<input type="text" name="feature-value<?php echo $i; ?>[]" value="<?php echo JHtml::_('esc_attr', $fval); ?>" size="20"/>
								<span class="vrc-feature-remove"><?php VikRentCarIcons::e('times-circle'); ?></span>
							</div>
							<?php
						}
					}
					?>
							<span class="vrc-feature-add"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRCDISTFEATUREADD'); ?></span>
						</div>
					<?php
					if (count($row)) {
					?>
						<div class="vrc-feature-damage-block" id="vrc-feature-damage-block-<?php echo $i; ?>">
							<div class="vrc-feature-damage-imgcont">
								<img src="<?php echo $damage_img; ?>"/>
						<?php
						$tot_dmg = isset($car_params['damages']) && isset($car_params['damages'][$i]) ? count($car_params['damages'][$i]) : 0;
						if ($tot_dmg > 0) {
							$dk = $tot_dmg;
							foreach ($car_params['damages'][$i] as $damage) {
								?>
								<span class="vrc-feature-damage-circle" id="vrc-damage-circle-<?php echo $i; ?>-<?php echo $dk; ?>" style="left: <?php echo $damage['x']; ?>px; top: <?php echo $damage['y']; ?>px;"><?php echo $dk; ?></span>
								<?php
								$dk--;
							}
						}
						?>
							</div>
							<div class="vrc-feature-damage-actions">
						<?php
						if ($tot_dmg > 0) {
							$dk = $tot_dmg;
							foreach ($car_params['damages'][$i] as $damage) {
								?>
								<div class="vrc-feature-car-damage vrc-feature-car-damage-<?php echo $i; ?>" id="vrc-feature-car-damage-<?php echo $i; ?>-<?php echo $dk; ?>">
									<span class="vrc-feature-car-damage-count"><?php echo $dk; ?></span>
									<span class="vrc-feature-damage-remove"><?php VikRentCarIcons::e('times-circle'); ?></span>
									<div class="vrc-feature-car-damage-details">
										<span class="vrc-feature-car-damage-detail"><?php echo JText::_('VRCDISTFEATURECDAMAGENOTES'); ?></span>
										<span class="vrc-feature-car-damage-cont"><textarea name="car-<?php echo $i; ?>-damage[]"><?php echo JHtml::_('esc_textarea', $damage['notes']); ?></textarea></span>
										<input type="hidden" name="car-<?php echo $i; ?>-damage-x[]" value="<?php echo JHtml::_('esc_attr', $damage['x']); ?>" />
										<input type="hidden" name="car-<?php echo $i; ?>-damage-y[]" value="<?php echo JHtml::_('esc_attr', $damage['y']); ?>" />
									</div>
								</div>
								<?php
								$dk--;
							}
						} else {
							?>
								<span class="vrc-no-damage" id="vrc-no-damage-<?php echo $i; ?>"><?php echo JText::_('VRCDISTFEATURENODAMAGE'); ?></span>
							<?php
						}
						?>
							</div>
						</div>
					<?php
					}
					?>
					</div>
				<?php
				}
				?>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
	?>
	<input type="hidden" name="whereup" value="<?php echo (int)$row['id']; ?>">
	<input type="hidden" name="actmoreimgs" value="<?php echo JHtml::_('esc_attr', $row['moreimgs']); ?>">
	<?php
}
?>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<?php echo JHtml::_('form.token'); ?>
</form>
