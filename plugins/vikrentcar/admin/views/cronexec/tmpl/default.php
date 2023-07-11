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

?>
<style>
	body {
		padding: 0 !important;
	}
</style>

<div class="vrc-shell-wrap">
	<p class="vrc-shell-top-bar">
		<?php echo $this->cron->cron_name; ?> - <span><?php echo JText::_('VRCCRONEXECRESULT'); ?>:</span>
		<?php var_dump($this->response); ?>
	</p>
	<div class="vrc-shell-body" style="min-height: 400px;">
		<?php echo $this->cronModel->get('output'); ?>

		<?php if (strlen($this->cronModel->get('log', ''))): ?>
			<p>---------- LOG ----------</p>

			<div class="vrc-cronexec-log">
				<pre><?php echo $this->cronModel->get('log'); ?></pre>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
	(function($) {
		'use strict';

		const checkShellHeight = () => {
			let pageHeight  = $(window).height();
			let shellHeight = $('.vrc-shell-wrap').height();
			let bot_offset 	= 10;
			if (shellHeight < pageHeight) {
				let diff = pageHeight - shellHeight - bot_offset;
				$('.vrc-shell-body').css('height', '+=' + diff + 'px');
			}
		}

		$(function() {
			setTimeout(checkShellHeight, 300);
		});
	})(jQuery);
</script>
