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

$lic_key = $this->lic_key;
$lic_date = $this->lic_date;
$is_pro = $this->is_pro;

$nowdf = VikRentCar::getDateFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}

$valid_until = date($df, $lic_date);

?>
<div class="viwppro-cnt viwpro-procnt">
	<div class="viwpro-procnt-inner">
		<div class="vikwppro-header">
			<div class="vikwppro-header-inner">
				<div class="vikwppro-header-text">
					<h2><?php echo JText::_('VRCPROTHANKSUSE'); ?></h2>
					<h3><?php echo JText::_('VRCPROTHANKSLIC'); ?></h3>
				</div>
			</div>
		</div>
		<div class="vikwppro-licencecnt">
			<div class="col col-md-6 col-sm-12 vikwppro-licencetext">
				<div>
					<h3><?php echo JText::sprintf('VRCLICKEYVALIDUNTIL', $valid_until); ?></h3>
					<h4><?php echo JText::_('VRCPROGETRENEWLICFROM'); ?></h4>
					<a href="https://vikwp.com/" class="vikwp-btn-link" target="_blank"><?php VikRentCarIcons::e('rocket'); ?> <?php echo JText::_('VRCPROGETRENEWLIC'); ?></a>
				</div>
				<span class="icon-background"><?php VikRentCarIcons::e('rocket'); ?></span>
			</div>
			<div class="col col-md-6 col-sm-12 vikwppro-licenceform">
				<form>				
					<div class="vikwppro-licenceform-inner">
						<h4><?php //echo JText::_('VRCPROALREADYHAVEKEY'); ?> Already have Vik Rent Car PRO? <br /> <small>Enter your licence key here</small></h4>
						<div>
							<span class="vikwppro-inputspan"><?php VikRentCarIcons::e('key'); ?><input type="text" name="key" id="lickey" value="<?php echo htmlspecialchars($lic_key); ?>" class="licence-input" autocomplete="off" /></span>
							<button type="button" class="btn btn-primary" id="vikwpvalidate" onclick="vikWpValidateLicenseKey();"><?php echo JText::_('VRCPROVALNUPD'); ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var vikwp_running = false;

function vikWpValidateLicenseKey() {
	if (vikwp_running) {
		// prevent double submission until request is over
		return;
	}

	// start running
	vikWpStartValidation();

	// request
	VRCCore.doAjax(
		"<?php echo VikRentCar::ajaxUrl('admin.php?option=com_vikrentcar&task=license.validate'); ?>",
		{
			key: document.getElementById('lickey').value
		},
		(res) => {
			try {
				var obj_res = typeof res === 'string' ? JSON.parse(res) : res;
				document.location.href = 'admin.php?option=com_vikrentcar&view=getpro';
			} catch(err) {
				console.error(err);
				// stop the request
				vikWpStopValidation();
				// display error
				alert(err.responseText || 'Request Failed');
			}
		},
		(err) => {
			console.error(err);
			// stop the request
			vikWpStopValidation();
			// display error
			alert(err.responseText || 'Request Failed');
		}
	);
}

function vikWpStartValidation() {
	vikwp_running = true;
	jQuery('#vikwpvalidate').prepend('<?php VikRentCarIcons::e('refresh', 'fa-spin'); ?>');
}

function vikWpStopValidation() {
	vikwp_running = false;
	jQuery('#vikwpvalidate').find('i').remove();
}

jQuery(function() {
	jQuery('#lickey').keyup(function() {
		jQuery(this).val(jQuery(this).val().trim());
	});
	jQuery('#lickey').keypress(function(e) {
		if (e.which == 13) {
			// enter key code pressed, run the validation
			vikWpValidateLicenseKey();
			return false;
		}
	});
});
</script>
