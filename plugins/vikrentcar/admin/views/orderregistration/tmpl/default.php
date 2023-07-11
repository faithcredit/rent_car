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

$nowdf = VikRentCar::getDateFormat(true);
$nowtf = VikRentCar::getTimeFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}

$nominative = strlen($this->order['nominative']) > 1 ? $this->order['nominative'] : VikRentCar::getFirstCustDataField($this->order['custdata']);
$country_flag = '';
if (is_file(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'countries' . DIRECTORY_SEPARATOR . $this->order['country'] . '.png')) {
	$country_flag = '<img src="' . VRC_ADMIN_URI . 'resources/countries/' . $this->order['country'] . '.png" title="' . $this->order['country'] . '" class="vrc-country-flag vrc-country-flag-left"/>';
}

if ($this->order['status'] == "confirmed") {
	$saystaus = '<span class="label label-success">' . JText::_('VRCONFIRMED') . '</span>';
} elseif ($this->order['status'] == "standby") {
	$saystaus = '<span class="label label-warning">' . JText::_('VRSTANDBY') . '</span>';
} else {
	$saystaus = '<span class="label label-error" style="background-color: #d9534f;">' . JText::_('VRCANCELLED') . '</span>';
}

// grab other registration events, if any
$history_obj = VikRentCar::getOrderHistoryInstance()->setBid($this->order['id']);
$reg_events = $history_obj->getEventsWithData(array('RA', 'RZ', 'RB', 'RC'), null, false);
?>

<div class="vrc-bookingdet-topcontainer vrc-orderregistration-top-wrap">
	<div class="vrc-bookdet-container">
		<div class="vrc-bookdet-wrap">
			<div class="vrc-bookdet-head">
				<span>ID</span>
			</div>
			<div class="vrc-bookdet-foot">
				<span><?php echo $this->order['id']; ?></span>
			</div>
		</div>
		<div class="vrc-bookdet-wrap">
			<div class="vrc-bookdet-head">
				<span><?php echo JText::_('VRCDRIVERNOMINATIVE'); ?></span>
			</div>
			<div class="vrc-bookdet-foot">
				<?php echo $country_flag . $nominative; ?>
			</div>
		</div>
		<div class="vrc-bookdet-wrap">
			<div class="vrc-bookdet-head">
				<span><?php echo JText::_('VREDITORDERFIVE'); ?></span>
			</div>
			<div class="vrc-bookdet-foot">
				<?php
				$ritiro_info = getdate($this->order['ritiro']);
				$short_wday = JText::_('VR'.strtoupper(substr($ritiro_info['weekday'], 0, 3)));
				echo $short_wday . ', ' . date($df . ' ' . $nowtf, $this->order['ritiro']);
				?>
			</div>
		</div>
		<div class="vrc-bookdet-wrap">
			<div class="vrc-bookdet-head">
				<span><?php echo JText::_('VREDITORDERSIX'); ?></span>
			</div>
			<div class="vrc-bookdet-foot">
				<?php
				$consegna_info = getdate($this->order['consegna']);
				$short_wday = JText::_('VR'.strtoupper(substr($consegna_info['weekday'], 0, 3)));
				echo $short_wday . ', ' . date($df . ' ' . $nowtf, $this->order['consegna']);
				?>
			</div>
		</div>
	<?php
	if (!empty($this->order['idplace'])) {
		$pickup_place = VikRentCar::getPlaceName($this->order['idplace']);
		?>
		<div class="vrc-bookdet-wrap">
			<div class="vrc-bookdet-head">
				<span><?php echo JText::_('VRRITIROCAR'); ?></span>
			</div>
			<div class="vrc-bookdet-foot">
				<?php echo $pickup_place; ?>
			</div>
		</div>
		<?php
	}
	if (!empty($this->order['idreturnplace'])) {
		$dropoff_place = VikRentCar::getPlaceName($this->order['idreturnplace']);
		?>
		<div class="vrc-bookdet-wrap">
			<div class="vrc-bookdet-head">
				<span><?php echo JText::_('VRRETURNCARORD'); ?></span>
			</div>
			<div class="vrc-bookdet-foot">
				<?php echo $dropoff_place; ?>
			</div>
		</div>
		<?php
	}
	?>
		<div class="vrc-bookdet-wrap">
			<div class="vrc-bookdet-head">
				<span><?php echo JText::_('VRSTATUS'); ?></span>
			</div>
			<div class="vrc-bookdet-foot">
				<span><?php echo $saystaus; ?></span>
			</div>
		</div>
	</div>
</div>

<div class="vrc-orderregistration-mid-wrap">
	<div class="vrc-orderregistration-mid-inner">
		<div class="vrc-orderregistration-fields">
			<div class="vrc-orderregistration-field">
				<label for="newregstatus"><?php echo JText::_('VRC_ORDER_REGISTRATION'); ?></label>
				<select id="newregstatus">
					<option value=""><?php echo JText::_('VRC_ORDER_REGISTRATION_NONE'); ?></option>
					<option value="1"<?php echo (int)$this->order['reg'] === 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRC_ORDER_REGISTRATION_STARTED'); ?></option>
					<option value="2"<?php echo (int)$this->order['reg'] === 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRC_ORDER_REGISTRATION_TERMINATED'); ?></option>
					<option value="-1"<?php echo (int)$this->order['reg'] === -1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRC_ORDER_REGISTRATION_NOSHOW'); ?></option>
				</select>
			</div>
			<div class="vrc-orderregistration-field">
				<label for="regstatusnotes"><?php echo JText::_('VRCTOGGLEORDNOTES'); ?></label>
				<textarea id="regstatusnotes"></textarea>
			</div>
			<div class="vrc-orderregistration-field vrc-orderregistration-field-save">
				<input type="hidden" id="vrc-oid" value="<?php echo $this->order['id']; ?>" />
				<button type="button" class="btn vrc-config-btn" onclick="vrcUpdateRegStatus();"><?php VikRentCarIcons::e('save'); ?> <?php echo JText::_('VRCUPDATEBTN'); ?></button>
			</div>
		</div>
	<?php
	if (is_array($reg_events) && count($reg_events)) {
		// reverse the order of the array to get the events sorted by date desc
		$reg_events = array_reverse($reg_events);
		?>
		<div class="vrc-orderregistration-history-wrap">
			<div class="vrc-booking-history-container table-responsive">
				<table class="table">
					<thead>
						<tr class="vrc-booking-history-firstrow">
							<td class="vrc-booking-history-td-type"><?php echo JText::_('VRCBOOKHISTORYLBLTYPE'); ?></td>
							<td class="vrc-booking-history-td-date"><?php echo JText::_('VRCBOOKHISTORYLBLDATE'); ?></td>
							<td class="vrc-booking-history-td-descr"><?php echo JText::_('VRCBOOKHISTORYLBLDESC'); ?></td>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ($reg_events as $hist) {
						$hdescr = strpos($hist['descr'], '<') !== false ? $hist['descr'] : nl2br($hist['descr']);
						?>
						<tr class="vrc-booking-history-row">
							<td><?php echo $history_obj->validType($hist['type'], true); ?></td>
							<td><?php echo JHtml::_('date', $hist['dt']); ?></td>
							<td><?php echo $hdescr; ?></td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
	?>
	</div>
</div>

<a href="index.php?option=com_vikrentcar" class="vrc-placeholder-backlink" style="display: none;"></a>

<script type="text/javascript">
	function vrcUpdateRegStatus() {
		var newregstatus = jQuery('#newregstatus').val();
		var regstatusnotes = jQuery('#regstatusnotes').val();
		var regoid = jQuery('#vrc-oid').val();

		jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: {
				option: "com_vikrentcar",
				task: "update_reg_status",
				cid: [regoid],
				tmpl: "component",
				newregstatus: newregstatus,
				regstatusnotes: regstatusnotes
			}
		}).done(function(res) {
			if (res.indexOf('e4j.error') >= 0 ) {
				console.log(res);
				alert(res.replace("e4j.error.", ""));
			} else {
				var obj_res = JSON.parse(res);
				var new_btn_class = obj_res['btn_class'];
				var new_btn_text = obj_res['btn_text'];
				
				// close modal, update parent contents with attribute data-regstatusoid
				var nav_fallback = jQuery('.vrc-placeholder-backlink').first().attr('href');
				var modal = jQuery('.modal[id*="vrc"]');
				var needs_parent = false;
				if (!modal.length) {
					// check if we are in a iFrame and so the element we want is inside the parent
					modal = jQuery('.modal[id*="vrc"]', parent.document);
					if (modal.length) {
						needs_parent = true;
					}
				}
				if (!modal.length) {
					// we are probably not inside a modal, so navigate
					window.location.href = nav_fallback;
					return;
				}

				// update parent contents
				var buttons = jQuery('[data-regstatusoid="' + regoid + '"]');
				if (buttons.length) {
					buttons.attr('class', new_btn_class).html(new_btn_text);
				}
				
				// try to dismiss the modal
				try {
					modal.modal('hide');
				} catch(e) {
					// dismissing did not succeed, but we do nothing
				}
			}
		}).fail(function(err) {
			alert('Request failed');
			console.error('Request Failed', err);
		});
	}
</script>
