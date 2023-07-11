<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * @wponly 	this template file is only for WP
 */

?>

<div class="vrc-dashboard-firstsetup-sampledata-wrap" style="display: none;">
	<div class="vrc-dashboard-firstsetup-sampledata-inner">
		<h4>
			<span><?php echo JText::_('VRCDASHINSTSAMPLEDTXT'); ?></span>
			<a class="btn vrc-sampledata-btn" href="admin.php?option=com_vikrentcar&view=sampledata"><?php VikRentCarIcons::e('hat-wizard'); ?> <?php echo JText::_('VRCDASHINSTSAMPLEDBTN'); ?></a>
		</h4>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery.ajax({
			type: "POST",
			url: "admin-ajax.php",
			data: {
				action: "vikrentcar",
				task: "sampledata.load"
			}
		}).done(function(res) {
			try {
				var obj_res = JSON.parse(res);
				if (obj_res && obj_res.length) {
					jQuery('.vrc-dashboard-firstsetup-sampledata-wrap').fadeIn();
				} else {
					console.info('No Sample Data available for installation.', obj_res);
				}
			} catch(err) {
				console.error('Sample Data: could not parse JSON response', err, res);
			}
		}).fail(function(err) {
			console.error(err);
		});
	});
</script>
