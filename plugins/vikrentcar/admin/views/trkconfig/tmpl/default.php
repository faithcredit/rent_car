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

$trksettings = $this->trksettings;

$trksettings['trkcampaigns'] = empty($trksettings['trkcampaigns']) ? array() : json_decode($trksettings['trkcampaigns'], true);
$trksettings['trkcampaigns'] = !is_array($trksettings['trkcampaigns']) ? array() : $trksettings['trkcampaigns'];

$vrc_app 	= VikRentCar::getVrcApplication();
$config 	= VRCFactory::getConfig();
$vrcbaseuri = JUri::root();

JText::script('VIKLOADING');

?>
<script type="text/javascript">
var randspool  = new Array;
var vrcbaseuri = '<?php echo $vrcbaseuri; ?>';
jQuery(document).ready(function() {
	jQuery('#vrc-add-trkcampaign').click(function() {
		var randkey = Math.floor(Math.random() * (9999 - 1000)) + 1000;
		if (randspool.indexOf(randkey) > -1) {
			while (randspool.indexOf(randkey) > -1) {
				randkey = Math.floor(Math.random() * (9999 - 1000)) + 1000;
			}
		}
		randspool.push(randkey);
		// for Nginx compatibility, we concatenate to the numeric key a random 3 char string
		randkey += vrcGetRandString(3);
		//
		var ind = jQuery('.vrc-trackings-custcampaign').length + 1;
		var campcont = '<div class="vrc-trackings-custcampaign">'+
							'<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-name">'+
								'<label for="vrc-name-'+ind+'"><?php echo addslashes(JText::_('VRCTRKCAMPAIGNNAME')); ?></label>'+
								'<input type="text" name="trkcampname[]" id="vrc-name-'+ind+'" value="" size="20" placeholder="<?php echo addslashes(JText::_('VRCTRKCAMPAIGNNAME')); ?>" />'+
							'</div>'+
							'<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-key">'+
								'<label for="vrc-key-'+ind+'"><?php echo addslashes(JText::_('VRCTRKCAMPAIGNKEY')); ?></label>'+
								'<input type="text" name="trkcampkey[]" id="vrc-key-'+ind+'" onkeyup="vrcCustCampaignUri(this);" value="'+randkey+'" size="10" />'+
							'</div>'+
							'<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-val">'+
								'<label for="vrc-val-'+ind+'"><?php echo addslashes(JText::_('VRCTRKCAMPAIGNVAL')); ?></label>'+
								'<input type="text" name="trkcampval[]" id="vrc-val-'+ind+'" onkeyup="vrcCustCampaignUri(this);" value="" size="10" />'+
							'</div>'+
							'<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-rm">'+
								'<a class="btn btn-danger" href="javascript: void(0);" onclick="vrcRmCustCampaign(this);">&times;</a>'+
							'</div>'+
							'<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-uri"></div>'+
						'</div>';
		jQuery('.vrc-trackings-custcampaigns').append(campcont);
		setTimeout(function() {
			vrcCustCampaignUri(document.getElementById('vrc-key-'+ind));
		}, 300);
	});
});
function vrcGetRandString(len) {
	var randstr = "";
	var charsav = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	for (var i = 0; i < len; i++) {
		randstr += charsav.charAt(Math.floor(Math.random() * charsav.length));
	}
	return randstr;
}
function vrcRmCustCampaign(elem) {
	jQuery(elem).closest('.vrc-trackings-custcampaign').remove();
}
function vrcCustCampaignUri(elem) {
	var cont = jQuery(elem);
	var sval = cont.val();
	if (/\s/g.test(sval)) {
		sval = sval.replace(/\s/g, '');
		cont.val(sval);
	}
	var rkey = '';
	var rval = '';
	if (cont.parent('.vrc-trackings-custcampaign-box').hasClass('vrc-trackings-custcampaign-key')) {
		rkey = sval;
		rval = cont.closest('.vrc-trackings-custcampaign').find('.vrc-trackings-custcampaign-val').find('input').val();
	} else {
		rval = sval;
		rkey = cont.closest('.vrc-trackings-custcampaign').find('.vrc-trackings-custcampaign-key').find('input').val();
	}
	cont.closest('.vrc-trackings-custcampaign').find('.vrc-trackings-custcampaign-uri').text(vrcbaseuri+'?'+rkey+(rval.length ? '='+rval : ''));
}
function vikLoadMeasurmentParams(driver_id) {
	if (driver_id.length > 0) {
		jQuery("#vikparameters").html('<p>' + Joomla.JText._('VIKLOADING') + '</p>');
		jQuery.ajax({
			type: "POST",
			url: "<?php echo VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=loadmeasurmentparams&tmpl=component'); ?>",
			data: {
				driver_id: driver_id
			}
		}).done(function(res) {
			jQuery("#vikparameters").html(res);
		});
	} else {
		jQuery("#vikparameters").html('<p>--------</p>');
	}
}
</script>

<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCTRKSETTINGS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCTRKENABLED'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('trkenabled', JText::_('VRYES'), JText::_('VRNO'), (int)$trksettings['trkenabled'], 1, 0); ?>
								<span class="vrc-param-setting-comment vrc-trackings-cookiediscl"><?php VikRentCarIcons::e('info-circle'); ?> <?php echo JText::_('VRCTRKCOOKIEEXPL'); ?></span>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCTRKCOOKIERFRDUR'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCTRKCOOKIERFRDUR'), 'content' => JText::_('VRCTRKCOOKIERFRDURHELP'))); ?></div>
							<div class="vrc-param-setting"><input type="number" step="any" min="0" name="trkcookierfrdur" value="<?php echo JHtml::_('esc_attr', $trksettings['trkcookierfrdur']); ?>" /> (<?php echo strtolower(JText::_('VRDAYS')); ?>)</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCTRKCAMPAIGNS'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCTRKCAMPAIGNS'), 'content' => JText::_('VRCTRKCAMPAIGNSHELP'))); ?></div>
							<div class="vrc-param-setting"><button class="btn vrc-config-btn" type="button" id="vrc-add-trkcampaign"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRCTRKADDCAMPAIGN'); ?></button></div>
						</div>
						<div class="vrc-param-container vrc-param-nested vrc-param-container-full">
							<div class="vrc-param-setting">
								<div class="vrc-trackings-custcampaigns">
								<?php
								$i = 0;
								foreach ($trksettings['trkcampaigns'] as $rkey => $rvalue) {
									?>
									<div class="vrc-trackings-custcampaign">
										<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-name">
											<label for="vrc-name-<?php echo $i; ?>"><?php echo JText::_('VRCTRKCAMPAIGNNAME'); ?></label>
											<input type="text" name="trkcampname[]" id="vrc-name-<?php echo $i; ?>" value="<?php echo JHtml::_('esc_attr', $rvalue['name']); ?>" size="20" />
										</div>
										<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-key">
											<label for="vrc-key-<?php echo $i; ?>"><?php echo JText::_('VRCTRKCAMPAIGNKEY'); ?></label>
											<input type="text" name="trkcampkey[]" id="vrc-key-<?php echo $i; ?>" onkeyup="vrcCustCampaignUri(this);" value="<?php echo JHtml::_('esc_attr', $rkey); ?>" size="10" />
										</div>
										<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-val">
											<label for="vrc-val-<?php echo $i; ?>"><?php echo JText::_('VRCTRKCAMPAIGNVAL'); ?></label>
											<input type="text" name="trkcampval[]" id="vrc-val-<?php echo $i; ?>" onkeyup="vrcCustCampaignUri(this);" value="<?php echo JHtml::_('esc_attr', $rvalue['value']); ?>" size="10" />
										</div>
										<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-rm">
											<a class="btn btn-danger" href="javascript: void(0);" onclick="vrcRmCustCampaign(this);">&times;</a>
										</div>
										<div class="vrc-trackings-custcampaign-box vrc-trackings-custcampaign-uri"><?php echo $vrcbaseuri.'?'.$rkey.(!empty($rvalue['value']) ? '='.$rvalue['value'] : ''); ?></div>
									</div>
									<?php
									$i++;
								}
								?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="vrc-config-maintab-right">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRC_TRK_DRIVER'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRC_TRK_DRIVER'); ?></div>
							<div class="vrc-param-setting">
								<?php
								$current_measur_dr  = $config->get('measurment_driver', '');
								$current_measur_ps  = $config->get('measurment_params', '');
								$measurment_helper  = VRCConversionFactory::getInstance();
								$measurment_drivers = $measurment_helper->getDriverNames();
								$driver_params = !empty($current_measur_dr) ? $measurment_helper->displayParams($current_measur_dr, $current_measur_ps) : '';
								?>
								<select name="measurment_driver" onchange="vikLoadMeasurmentParams(this.value);">
									<option value=""></option>
								<?php
								foreach ($measurment_drivers as $driver) {
									?>
									<option value="<?php echo $driver->id; ?>"<?php echo $driver->id == $current_measur_dr ? ' selected="selected"' : ''; ?>><?php echo $driver->name; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vrc-params-container vrc-measurment-params-container" id="vikparameters">
							<?php echo $driver_params; ?>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
