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

$nowdf = VikRentCar::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$tf = VikRentCar::getTimeFormat();
// make sure to add seconds to time format
if (strpos($tf, 's') === false) {
	$mins_wc = stripos($tf, 'i');
	if ($mins_wc !== false) {
		$tf = substr_replace($tf, substr($tf, $mins_wc, 1) . ':s' . substr($tf, $mins_wc + 1), $mins_wc);
	}
}

JText::script('VRCCRONLOGS');

if (empty($rows)) {
	?>
<p class="warn"><?php echo JText::_('VRCNOCRONS'); ?></p>
<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrentcar" />
</form>
	<?php
} else {
	?>
<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm" class="vrc-list-form">
	<div class="table-responsive">
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vrc-list-table">
			<thead>
				<tr>
					<th width="20">
						<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
					</th>
					<th class="title left" width="50">
						<a href="index.php?option=com_vikrentcar&amp;task=crons&amp;vrcorderby=id&amp;vrcordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "id" ? "vrc-list-activesort" : "")); ?>">
							ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "id" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
						</a>
					</th>
					<th class="title left" width="200">
						<a href="index.php?option=com_vikrentcar&amp;task=crons&amp;vrcorderby=cron_name&amp;vrcordersort=<?php echo ($orderby == "cron_name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "cron_name" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "cron_name" ? "vrc-list-activesort" : "")); ?>">
							<?php echo JText::_('VRCCRONNAME').($orderby == "cron_name" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "cron_name" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
						</a>
					</th>
					<th class="title center" width="100"><?php echo JText::_( 'VRCCRONCLASS' ); ?></th>
					<th class="title center" width="75">
						<a href="index.php?option=com_vikrentcar&amp;task=crons&amp;vrcorderby=last_exec&amp;vrcordersort=<?php echo ($orderby == "last_exec" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "last_exec" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "last_exec" ? "vrc-list-activesort" : "")); ?>">
							<?php echo JText::_('VRCCRONLASTEXEC').($orderby == "last_exec" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "last_exec" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
						</a>
					</th>
					<th class="title center" width="50"><?php echo JText::_('VRCCRONPUBLISHED'); ?></th>
					<th class="title center" width="150"><?php echo JText::_('VRCCRONACTIONS'); ?></th>
					<th class="title center" width="100">&nbsp;</th>
				</tr>
			</thead>
		<?php
		$kk = 0;
		$i = 0;
		for ($i = 0, $n = count($rows); $i < $n; $i++) {
			$row = $rows[$i];
			?>
			<tr class="row<?php echo $kk; ?>">
				<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);">
				</td>
				<td>
					<a href="index.php?option=com_vikrentcar&amp;task=cronjob.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a>
				</td>
				<td class="vrc-highlighted-td">
					<a href="index.php?option=com_vikrentcar&amp;task=cronjob.edit&amp;cid[]=<?php echo $row['id']; ?>" class="vrc-cron-name" data-cronid="<?php echo $row['id']; ?>"><?php echo $row['cron_name']; ?></a>
				</td>
				<td class="center">
					<?php echo $row['class_file']; ?>
				</td>
				<td class="center">
					<?php echo !empty($row['last_exec']) ? date($df . ' ' . $tf, $row['last_exec']) : '----'; ?>
				</td>
				<td class="center">
				<?php
				if (intval($row['published']) > 0) {
					?>
					<i class="<?php echo VikRentCarIcons::i('check', 'vrc-icn-img'); ?>" style="color: #099909;"></i>
					<?php
				} else {
					?>
					<i class="<?php echo VikRentCarIcons::i('times', 'vrc-icn-img'); ?>" style="color: #ff0000;"></i>
					<?php
				}
				?>
				</td>
				<td class="center">
				<?php
				if (!defined('ABSPATH')) {
					?>
					<button type="button" class="btn btn-secondary vrc-getcmd" data-cronid="<?php echo $row['id']; ?>" data-cronname="<?php echo addslashes($row['cron_name']); ?>" data-cronclass="<?php echo $row['class_file']; ?>"><?php VikRentCarIcons::e('terminal'); ?> <?php echo JText::_('VRCCRONGETCMD'); ?></button> &nbsp;&nbsp; 
					<?php
				}
				?>
					<button type="button" class="btn btn-warning vrc-exec" data-cronid="<?php echo $row['id']; ?>"><span><?php VikRentCarIcons::e('play'); ?></span> <?php echo JText::_('VRCCRONACTION'); ?></button>
				</td>
				<td class="center">
					<button type="button" class="btn btn-secondary vrc-logs" data-cronid="<?php echo $row['id']; ?>"><span><?php VikRentCarIcons::e('server'); ?></span> <?php echo JText::_('VRCCRONLOGS'); ?></button>
				</td>
			</tr>
			<?php
			$kk = 1 - $kk;
		}
		?>
		</table>
	</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="crons" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>

	<?php
	if (!defined('ABSPATH')) {
		/**
		 * @joomlaonly  we split the instructions depending on the Joomla version.
		 */
		$j_version = (new JVersion)->getShortVersion();
		$scheduled_tasks_supported = version_compare($j_version, '4.1.0', '>=');
		?>
<div class="vrc-info-overlay-block">
	<a class="vrc-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-info-overlay-content vrc-info-overlay-content-getcmd">
		<h3><?php VikRentCarIcons::e('terminal'); ?> <?php echo JText::_('VRCCRONGETCMD') ?>: <span id="crongetcmd-lbl"></span></h3>
		<?php
		if ($scheduled_tasks_supported) {
			// Joomla 4.1.x
			echo JText::sprintf('VRC_CRONJOB_INSTRUCTIONS_SCHEDTASKS', 'index.php?option=com_scheduler', '<i class="' . VikRentCarIcons::i('external-link') . '"></i>');
			// base URI for executing the cron job via GET request
			$base_cron_uri = VRCFactory::getPlatform()->getUri()->route('index.php?option=com_vikrentcar&task=cron_exec&cron_id=%d&cronkey=' . md5(VikRentCar::getCronKey()));
			?>
		<div class="vrc-admin-container vrc-params-container-wide">
			<div class="vrc-config-maintab-left">
				<fieldset class="adminform">
					<div class="vrc-params-wrap">
						<div class="vrc-params-container">
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRC_CRONJOB_SCHEDTASKS_EXEC_URL'); ?></div>
								<div class="vrc-param-setting">
									<div class="input-group">
										<input type="text" value="" data-baseuri="<?php echo $base_cron_uri; ?>" id="vrc-cron-url-exec" style="min-width: 60%;" readonly />
										<button type="button" class="btn btn-primary" id="vrc-cron-url-copy"><i id="vrc-copycronurl-result" class="<?php echo VikRentCarIcons::i('copy'); ?>"></i></button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
		</div>

		<script type="text/javascript">
			jQuery(function() {
				jQuery('#vrc-cron-url-copy').click(function() {
					VRCCore.copyToClipboard(jQuery('#vrc-cron-url-exec')).then(() => {
						jQuery('#vrc-cron-url-copy').addClass('btn-success');
						jQuery('#vrc-copycronurl-result').attr('class', '<?php echo VikRentCarIcons::i('check-circle'); ?>');
						setTimeout(() => {
							jQuery('#vrc-cron-url-copy').removeClass('btn-success');
							jQuery('#vrc-copycronurl-result').attr('class', '<?php echo VikRentCarIcons::i('copy'); ?>');
						}, 1500);
					});
				});
			});
		</script>
		<?php
		} else {
			// Joomla 3.x
			?>
		<blockquote class="vrc-crongetcmd-help"><?php echo JText::_('VRCCRONGETCMDHELP') ?></blockquote>
		<h4><?php echo JText::_('VRCCRONGETCMDINSTSTEPS') ?></h4>
		<ol>
			<li><?php echo JText::_('VRCCRONGETCMDINSTSTEPONE') ?></li>
			<li><?php echo JText::_('VRCCRONGETCMDINSTSTEPTWO') ?></li>
			<li><?php echo JText::_('VRCCRONGETCMDINSTSTEPTHREE') ?></li>
			<li><?php echo JText::_('VRCCRONGETCMDINSTSTEPFOUR') ?></li>
		</ol>
		<p><?php echo JText::_('VRCCRONGETCMDINSTPATH'); ?></p>
		<p><span class="label label-info">/usr/bin/php <?php echo defined('ABSPATH') || !defined('JPATH_SITE') ? ABSPATH : JPATH_SITE . DIRECTORY_SEPARATOR; ?><span class="crongetcmd-php"></span>.php</span></p>
		<p><?php VikRentCarIcons::e('exclamation-triangle'); ?> <?php echo JText::_('VRCCRONGETCMDINSTURL'); ?></p>
		<p><span class="label"><?php echo JURI::root(); ?><span class="crongetcmd-php"></span>.php</span></p>
		<br/>
		<form action="index.php?option=com_vikrentcar" method="post">
			<button type="submit" class="btn"><?php VikRentCarIcons::e('download'); ?> <?php echo JText::_('VRCCRONGETCMDGETFILE') ?></button>
			<input type="hidden" name="cron_id" id="cronid-inp" value="" />
			<input type="hidden" name="cron_name" id="cronname-inp" value="" />
			<input type="hidden" name="task" value="downloadcron" />
		</form>
		<?php
		}
		?>
	</div>
</div>
		<?php
	}
	?>

<div class="vrc-modal-overlay-block vrc-modal-overlay-block-cron-output">
	<a class="vrc-modal-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-modal-overlay-content vrc-modal-overlay-content-large vrc-modal-overlay-content-cron-output">
		<div class="vrc-modal-overlay-content-head vrc-modal-overlay-content-head-cron-output">
			<h3>
				<span></span>
				<span class="vrc-modal-overlay-close-times" onclick="vrcToggleCronModal();">&times;</span>
			</h3>
		</div>
		<div class="vrc-modal-overlay-content-body vrc-modal-overlay-content-body-scroll">
			<div class="vrc-cron-modal-output"></div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var vrc_overlay_on = false;
	var vrc_cron_modal_on = false;
	var vrc_cron_ajax_exec = "<?php echo VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=cron_exec&cronkey=' . md5(VikRentCar::getCronKey()) . '&tmpl=component'); ?>";
	var vrc_cron_ajax_logs = "<?php echo VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=cronlogs&tmpl=component'); ?>";

	function vrcToggleCronModal() {
		jQuery('.vrc-modal-overlay-block-cron-output').fadeToggle(400, function() {
			if (jQuery('.vrc-modal-overlay-block-cron-output').is(':visible')) {
				vrc_cron_modal_on = true;
			} else {
				vrc_cron_modal_on = false;
			}
		});
	}

	jQuery(function() {

		jQuery('.vrc-getcmd').click(function() {
			var cronid = jQuery(this).attr('data-cronid');
			var cronname = jQuery(this).attr('data-cronname');
			jQuery('#crongetcmd-lbl').text(cronname);
			var cronclass = jQuery(this).attr('data-cronclass');
			jQuery('#cronid-inp').val(cronid);
			var cronnamephp = cronname.replace(/\s/g, '').toLowerCase();
			jQuery('#cronname-inp').val(cronnamephp);
			jQuery('.crongetcmd-php').text(cronnamephp);

			/**
			 * @joomlaonly  code is compatible even if the element is not present.
			 */
			var j_exec_uri_input = jQuery('#vrc-cron-url-exec');
			if (j_exec_uri_input.length) {
				var cron_base_uri = j_exec_uri_input.attr('data-baseuri');
				j_exec_uri_input.val(cron_base_uri.replace('%d', cronid));
			}

			jQuery('.vrc-info-overlay-block').fadeToggle(400, function() {
				if (jQuery('.vrc-info-overlay-block').is(':visible')) {
					vrc_overlay_on = true;
				} else {
					vrc_overlay_on = false;
				}
			});
		});

		jQuery('.vrc-logs').click(function() {
			var btn_trig  = jQuery(this);
			var cron_id   = btn_trig.attr('data-cronid');
			var cron_name = jQuery('.vrc-cron-name[data-cronid="' + cron_id + '"]').text();
			btn_trig.find('span').html('<?php VikRentCarIcons::e('circle-notch', 'fa-spin fa-fw'); ?>');
			VRCCore.doAjax(vrc_cron_ajax_logs, {cron_id: cron_id}, (resp) => {
				btn_trig.find('span').html('<?php VikRentCarIcons::e('server'); ?>');
				try {
					var obj_res = typeof resp === 'string' ? JSON.parse(resp) : resp;
					// set modal content
					jQuery('.vrc-cron-modal-output').html(obj_res[0]);
					// set modal title
					jQuery('.vrc-modal-overlay-content-head-cron-output').find('h3').find('span').first().text(cron_name + ' - ' + Joomla.JText._('VRCCRONLOGS'));
					// display modal
					vrcToggleCronModal();
				} catch(err) {
					console.error('Could not parse JSON response', err, resp);
				}
			}, (err) => {
				console.log(err);
				alert(err.responseText);
				btn_trig.find('span').html('<?php VikRentCarIcons::e('server'); ?>');
			});
		});

		jQuery('.vrc-exec').click(function() {
			var btn_trig  = jQuery(this);
			var cron_id   = btn_trig.attr('data-cronid');
			var cron_name = jQuery('.vrc-cron-name[data-cronid="' + cron_id + '"]').text();
			btn_trig.find('span').html('<?php VikRentCarIcons::e('circle-notch', 'fa-spin fa-fw'); ?>');
			VRCCore.doAjax(vrc_cron_ajax_exec, {cron_id: cron_id}, (resp) => {
				btn_trig.find('span').html('<?php VikRentCarIcons::e('play'); ?>');
				try {
					var obj_res = typeof resp === 'string' ? JSON.parse(resp) : resp;
					// set modal content
					jQuery('.vrc-cron-modal-output').html(obj_res[0]);
					// set modal title
					jQuery('.vrc-modal-overlay-content-head-cron-output').find('h3').find('span').first().html('<?php VikRentCarIcons::e('terminal'); ?> ' + cron_name);
					// display modal
					vrcToggleCronModal();
				} catch(err) {
					console.error('Could not parse JSON response', err, resp);
				}
			}, (err) => {
				console.log(err);
				alert(err.responseText);
				btn_trig.find('span').html('<?php VikRentCarIcons::e('play'); ?>');
			});
		});

		jQuery(document).mouseup(function(e) {
			if (!vrc_overlay_on) {
				return false;
			}
			var vrc_overlay_cont = jQuery('.vrc-info-overlay-content');
			if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
				jQuery('.vrc-info-overlay-block').fadeOut();
				vrc_overlay_on = false;
			}
		});

		jQuery(document).keyup(function(e) {
			if (e.keyCode == 27 && vrc_overlay_on) {
				jQuery('.vrc-info-overlay-block').fadeOut();
				vrc_overlay_on = false;
			} else if (e.keyCode == 27 && vrc_cron_modal_on) {
				vrcToggleCronModal();
			}
		});

	});
</script>
<?php
}
