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

JText::script('VRCUPDCOMPLOKCLICK');
JText::script('VRCUPDCOMPLNOKCLICK');

?>
<div class="viwppro-cnt vikwppro-download">
	<div class="vikwppro-header">
		<div class="vikwppro-header-inner">
			<div class="vikwppro-download-result">
				<h2><?php VikRentCarIcons::e('refresh', 'fa-spin'); ?> <?php echo JText::_('VRCPROPLWAIT'); ?></h2>
				<h3 class="vikwppro-cur-action"><?php echo JText::_('VRCPRODLINGPKG'); ?></h3>
			</div>
			<div class="vikwppro-download-progress">
				<progress value="0" max="100" id="vikwpprogress"></progress> 
			</div>
		</div>
	</div>
<?php
if (!empty($this->changelog)) {
	?>
	<div class="vikwppro-changelog-wrap">
		<div class="vikwppro-plg-changelog">
			<?php echo $this->changelog; ?>
		</div>
	</div>
	<?php
}
?>
</div>

<script type="text/javascript">
var vikwprunning = false;
var vikwcomplete = false;

/**
 * Start the download request
 */
function vikwpStartDownload() {
	if (vikwprunning) {
		return;
	}

	// start progress
	vikwprunning = true;
	dispatchProgress();

	// request
	VRCCore.doAjax(
		"<?php echo VikRentCar::ajaxUrl('admin.php?option=com_vikrentcar&task=license.downloadpro'); ?>",
		{
			key: "<?php echo $lic_key; ?>"
		},
		(res) => {
			if (res.indexOf('e4j.error') >= 0 || res.indexOf('e4j.OK') < 0) {
				vikwpStopMonitoring(false);
				alert(res.replace("e4j.error.", ""));
				return;
			}
			// request was successful
			vikwpStopMonitoring(true);
		},
		(err) => {
			console.error(err);
			// stop the request
			vikwpStopMonitoring(false);
			// display error
			alert(err.responseText || 'Request Failed');
		}
	);
}

function vikwpStopMonitoring(result) {
	vikwcomplete = true;
	vikwprunning = false;
	jQuery('#vikwpprogress').attr('value', 100);
	if (result === true) {
		var continuebtn = '<p><button type="button" class="button button-primary" onclick="document.location.href=\'admin.php?option=com_vikrentcar\';">'+Joomla.JText._('VRCUPDCOMPLOKCLICK')+'</button></p>';
		jQuery('.vikwppro-download-result').html('<h1 class="vikwp-download-success"><?php VikRentCarIcons::e('check'); ?></h1>'+continuebtn);
	} else {
		var continuebtn = '<p><button type="button" class="button" onclick="document.location.href=\'admin.php?option=com_vikrentcar&view=gotopro\';">'+Joomla.JText._('VRCUPDCOMPLNOKCLICK')+'</button></p>';
		jQuery('.vikwppro-download-result').html('<h1 class="vikwp-download-error"><?php VikRentCarIcons::e('times'); ?></h1>'+continuebtn);
		jQuery('#vikwpprogress').hide();
	}
}

function dispatchProgress() {
	setTimeout(function() {
		if (vikwcomplete) {
			jQuery('#vikwpprogress').attr('value', 100);
			return;
		}
		var curprogress = parseInt(jQuery('#vikwpprogress').attr('value'));
		var nextstep = Math.floor(Math.random() * 5) + 6;
		if ((curprogress + nextstep) > 100) {
			curprogress = 100;
		} else {
			curprogress += nextstep;
		}
		jQuery('#vikwpprogress').attr('value', curprogress);
		if (curprogress < 100) {
			dispatchProgress();
		}
	}, (Math.floor(Math.random() * 501) + 750));
}

jQuery(function() {
	vikwpStartDownload();
});
</script>
