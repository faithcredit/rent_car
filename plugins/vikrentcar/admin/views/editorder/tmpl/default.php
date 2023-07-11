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
$customer = $this->customer;
$payments = $this->payments;

$history_obj = VikRentCar::getOrderHistoryInstance();
$history_obj->setBid($row['id']);

$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadVisualEditorAssets();

$currencyname = VikRentCar::getCurrencyName();
$car = VikRentCar::getCarInfo($row['idcar']);
$dbo = JFactory::getDbo();
$nowdf = VikRentCar::getDateFormat(true);
$nowtf = VikRentCar::getTimeFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$gotouri = 'index.php?option=com_vikrentcar&task=editorder&cid[]='.$row['id'];
$payment = VikRentCar::getPayment($row['idpayment']);
$is_cust_cost = (!empty($row['cust_cost']) && $row['cust_cost'] > 0);

$pickup_place = '';
$dropoff_place = '';

$ritiro_info = getdate($row['ritiro']);
$consegna_info = getdate($row['consegna']);

$tar = [
	[]
];
if (!empty($row['idtar']) || $is_cust_cost) {
	if (!empty($row['idtar'])) {
		if ($row['hourly'] == 1) {
			$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`=" . (int)$row['idtar'] . ";";
		} else {
			$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`=" . (int)$row['idtar'] . ";";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$tar = $dbo->loadAssocList();
		if ($row['hourly'] == 1) {
			foreach ($tar as $kt => $vt) {
				$tar[$kt]['days'] = 1;
			}
		}
	} else {
		//Custom Rate
		$tar = [
			[
				'id' 	   => -1,
				'idcar'    => $row['idcar'],
				'days' 	   => $row['days'],
				'idprice'  => -1,
				'cost' 	   => $row['cust_cost'],
				'attrdata' => '',
			]
		];
	}
	//vikrentcar 1.6
	$checkhourscharges = 0;
	$hoursdiff = 0;
	$ppickup = $row['ritiro'];
	$prelease = $row['consegna'];
	$secdiff = $prelease - $ppickup;
	$daysdiff = $secdiff / 86400;
	if (is_int($daysdiff)) {
		if ($daysdiff < 1) {
			$daysdiff = 1;
		}
	} else {
		if ($daysdiff < 1) {
			$daysdiff = 1;
			$checkhourly = true;
			$ophours = $secdiff / 3600;
			$hoursdiff = intval(round($ophours));
			if ($hoursdiff < 1) {
				$hoursdiff = 1;
			}
		} else {
			$sum = floor($daysdiff) * 86400;
			$newdiff = $secdiff - $sum;
			$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
			if ($maxhmore >= $newdiff) {
				$daysdiff = floor($daysdiff);
			} else {
				$daysdiff = ceil($daysdiff);
				/**
				 * Apply proper rounding with gratuity period.
				 * 
				 * @since 	1.15.1 (J) - 1.3.2 (WP)
				 */
				$ehours_float = ($newdiff - $maxhmore) / 3600;
				$ehours = intval(round($ehours_float));
				$ehours = !$ehours && $ehours_float > 0 && $maxhmore > 0 ? 1 : $ehours;
				$checkhourscharges = $ehours;
				if ($checkhourscharges > 0) {
					$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
				}
			}
		}
	}
	//
}
$pactive_tab = VikRequest::getString('vrc_active_tab', 'vrc-tab-details', 'request');

if ($row['status'] == "confirmed") {
	$saystaus = '<span class="label label-success">'.JText::_('VRCONFIRMED').'</span>';
} elseif ($row['status']=="standby") {
	$saystaus = '<span class="label label-warning">'.JText::_('VRSTANDBY').'</span>';
} else {
	$saystaus = '<span class="label label-error" style="background-color: #d9534f;">'.JText::_('VRCANCELLED').'</span>';
}
?>
<script type="text/javascript">
function vrToggleLog(elem) {
	var logdiv = document.getElementById('vrpaymentlogdiv').style.display;
	if (logdiv == 'block') {
		// document.getElementById('vrpaymentlogdiv').style.display = 'none';
		// jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").removeClass("vrc-bookingdet-noteslogs-btn-active");
	} else {
		jQuery(".vrc-bookingdet-noteslogs-btn-active").removeClass("vrc-bookingdet-noteslogs-btn-active");
		if (document.getElementById('vrchistorydiv')) {
			document.getElementById('vrchistorydiv').style.display = 'none';
		}
		document.getElementById('vradminnotesdiv').style.display = 'none';
		document.getElementById('vrpaymentlogdiv').style.display = 'block';
		jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").addClass("vrc-bookingdet-noteslogs-btn-active");
		if (typeof sessionStorage !== 'undefined') {
			sessionStorage.setItem('vrcEditOrderTab<?php echo $row['id']; ?>', 'paylogs');
		}
	}
}
function changePayment() {
	var newpayment = document.getElementById('newpayment').value;
	if (newpayment != '') {
		var paymentname = document.getElementById('newpayment').options[document.getElementById('newpayment').selectedIndex].text;
		if (confirm('<?php echo addslashes(JText::_('VRCCHANGEPAYCONFIRM')); ?>' + paymentname + '?')) {
			document.adminForm.submit();
		} else {
			document.getElementById('newpayment').selectedIndex = 0;
		}
	}
}
function vrToggleNotes(elem) {
	var notesdiv = document.getElementById('vradminnotesdiv').style.display;
	if (notesdiv == 'block') {
		// document.getElementById('vradminnotesdiv').style.display = 'none';
		// jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").removeClass("vrc-bookingdet-noteslogs-btn-active");
	} else {
		jQuery(".vrc-bookingdet-noteslogs-btn-active").removeClass("vrc-bookingdet-noteslogs-btn-active");
		if (document.getElementById('vrpaymentlogdiv')) {
			document.getElementById('vrpaymentlogdiv').style.display = 'none';
		}
		if (document.getElementById('vrchistorydiv')) {
			document.getElementById('vrchistorydiv').style.display = 'none';
		}
		document.getElementById('vradminnotesdiv').style.display = 'block';
		jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").addClass("vrc-bookingdet-noteslogs-btn-active");
		if (typeof sessionStorage !== 'undefined') {
			sessionStorage.setItem('vrcEditOrderTab<?php echo $row['id']; ?>', 'notes');
		}
	}
}
function vrToggleHistory(elem) {
	var historydiv = document.getElementById('vrchistorydiv').style.display;
	if (historydiv == 'block') {
		// document.getElementById('vrchistorydiv').style.display = 'none';
		// jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").removeClass("vrc-bookingdet-noteslogs-btn-active");
	} else {
		jQuery(".vrc-bookingdet-noteslogs-btn-active").removeClass("vrc-bookingdet-noteslogs-btn-active");
		if (document.getElementById('vrpaymentlogdiv')) {
			document.getElementById('vrpaymentlogdiv').style.display = 'none';
		}
		document.getElementById('vradminnotesdiv').style.display = 'none';
		document.getElementById('vrchistorydiv').style.display = 'block';
		jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").addClass("vrc-bookingdet-noteslogs-btn-active");
		if (typeof sessionStorage !== 'undefined') {
			sessionStorage.setItem('vrcEditOrderTab<?php echo $row['id']; ?>', 'history');
		}
	}
}
function toggleDiscount(elem) {
	var discsp = document.getElementById('vrdiscenter').style.display;
	if (discsp == 'block') {
		document.getElementById('vrdiscenter').style.display = 'none';
		jQuery(elem).find('i').removeClass("fa-chevron-up").addClass("fa-chevron-down");
	} else {
		document.getElementById('vrdiscenter').style.display = 'block';
		jQuery(elem).find('i').removeClass("fa-chevron-down").addClass("fa-chevron-up");
	}
}
</script>

<div class="vrc-bookingdet-topcontainer">
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		
		<div class="vrc-bookdet-container">
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span>ID</span>
				</div>
				<div class="vrc-bookdet-foot">
					<span><?php echo $row['id']; ?></span>
				</div>
			</div>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VREDITORDERONE'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<span><?php echo date($df.' '.$nowtf, $row['ts']); ?></span>
				</div>
			</div>
		<?php
		if (count($customer)) {
		?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VRCDRIVERNOMINATIVE'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<?php echo (isset($customer['country_img']) ? $customer['country_img'].' ' : '').'<a href="index.php?option=com_vikrentcar&task=editcustomer&cid[]='.$customer['id'].'&goto=' . (base64_encode($gotouri)) . '" target="_blank">'.ltrim($customer['first_name'].' '.$customer['last_name']).'</a>'; ?>
				</div>
			</div>
		<?php
		} elseif (!empty($row['nominative'])) {
		?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VRCDRIVERNOMINATIVE'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<?php echo $row['nominative']; ?>
				</div>
			</div>
		<?php
		}
		?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VREDITORDERFOUR'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<?php echo ($row['hourly'] == 1 && $tar && count($tar[0]) && isset($tar[0]['hours']) ? $tar[0]['hours'].' '.JText::_('VRCHOURS') : $row['days']); ?>
				</div>
			</div>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VREDITORDERFIVE'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
				<?php
				$short_wday = JText::_('VR'.strtoupper(substr($ritiro_info['weekday'], 0, 3)));
				?>
					<?php echo $short_wday.', '.date($df.' '.$nowtf, $row['ritiro']); ?>
				</div>
			</div>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VREDITORDERSIX'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
				<?php
				$short_wday = JText::_('VR'.strtoupper(substr($consegna_info['weekday'], 0, 3)));
				?>
					<?php echo $short_wday.', '.date($df.' '.$nowtf, $row['consegna']); ?>
				</div>
			</div>
		<?php
		if (!empty($row['idplace'])) {
			$pickup_place = VikRentCar::getPlaceName($row['idplace']);
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
		if (!empty($row['idreturnplace'])) {
			$dropoff_place = VikRentCar::getPlaceName($row['idreturnplace']);
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
		<?php
		/**
		 * Client rental order status registration (status must be confirmed).
		 * We allow to update the status from one week before the pick up date
		 * till one week after the drop off date, or if pick up date and time
		 * is in the past, but the current registration is no-show or started.
		 * 
		 * @since 	1.14.5 (J) - 1.2.0 (WP)
		 */
		$earliest_pickup = strtotime("-1 week", $row['ritiro']);
		$furthest_return = strtotime("+1 week", $row['consegna']);
		if ($row['status'] == 'confirmed' && $earliest_pickup <= time() && ($furthest_return >= time() || in_array((int)$row['reg'], array(-1, 1)))) {
			// prepare modal
			echo $vrc_app->getJmodalScript();
			echo $vrc_app->getJmodalHtml('vrc-order-registration', JText::_('VRC_ORDER_REGISTRATION'));
			// check current situations
			$row['reg'] = (int)$row['reg'];
			$reg_status = JText::_('VRC_ORDER_REGISTRATION_NONE');
			$reg_class  = 'btn-secondary';
			if ($row['reg'] < 0) {
				// no show
				$reg_status = JText::_('VRC_ORDER_REGISTRATION_NOSHOW');
				$reg_class  = 'btn-danger';
			} elseif ($row['reg'] === 1) {
				// started
				$reg_status = JText::_('VRC_ORDER_REGISTRATION_STARTED');
				$reg_class  = 'btn-primary';
			} elseif ($row['reg'] === 2) {
				// terminated
				$reg_status = JText::_('VRC_ORDER_REGISTRATION_TERMINATED');
				$reg_class  = 'btn-primary';
			}
			?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VRC_ORDER_REGISTRATION'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<span>
						<button type="button" class="btn btn-small <?php echo $reg_class; ?>" data-regstatusoid="<?php echo $row['id']; ?>" onclick="vrcOpenJModal('vrc-order-registration', 'index.php?option=com_vikrentcar&task=orderregistration&cid[]=<?php echo $row['id']; ?>&tmpl=component');"><?php echo $reg_status; ?></button>
					</span>
				</div>
			</div>
			<?php
		}
		?>
		</div>

		<div class="vrc-bookingdet-innertop">
			<div class="vrc-bookingdet-commands">
			<?php
			if (!empty($row['idbusy']) || $row['status'] == "standby") {
				?>
				<div class="vrc-bookingdet-command">
					<button onclick="document.location.href='index.php?option=com_vikrentcar&task=editbusy&return=order&cid[]=<?php echo $row['id']; ?>';" class="btn btn-secondary" type="button"><i class="icon-pencil"></i> <?php echo JText::_('VRMODRES'); ?></button>
				</div>
				<?php
			}
			if (($row['status'] == 'standby' || (is_array($tar) && count($tar) && count($tar[0]))) && time() < $row['ritiro']) {
				/**
				 * @wponly 	Rewrite order view URI
				 */
				$model 		= JModel::getInstance('vikrentcar', 'shortcodes');
				$itemid 	= $model->best('order');
				$order_uri 	= '';
				if ($itemid) {
					$order_uri = JRoute::_("index.php?option=com_vikrentcar&Itemid={$itemid}&view=order&sid={$row['sid']}&ts={$row['ts']}");
				} else {
					VikError::raiseWarning('', 'No Shortcodes of type Order Details found, or no Shortcodes of this type are being used in Pages/Posts.');
				}
				?>
				<div class="vrc-bookingdet-command">
					<button onclick="window.open('<?php echo $order_uri; ?>', '_blank');" type="button" class="btn btn-secondary"><i class="icon-eye"></i> <?php echo JText::_('VRCVIEWORDFRONT'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] == "confirmed" && is_array($tar) && count($tar) && count($tar[0])) {
				?>
				<div class="vrc-bookingdet-command">
					<button class="btn btn-success" type="button" onclick="document.location.href='index.php?option=com_vikrentcar&task=resendordemail&cid[]=<?php echo $row['id']; ?>';"><i class="icon-mail"></i> <?php echo JText::_('VRCRESENDORDEMAIL'); ?></button>
				</div>
				<div class="vrc-bookingdet-command">
					<button class="btn btn-success" type="button" onclick="document.location.href='index.php?option=com_vikrentcar&task=resendordemail&sendpdf=1&cid[]=<?php echo $row['id']; ?>';"><i class="icon-mail"></i> <?php echo JText::_('VRCRESENDORDEMAILANDPDF'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] == "cancelled" && !empty($row['custmail'])) {
				?>
				<div class="vrc-bookingdet-command">
					<button class="btn btn-warning" type="button" onclick="document.location.href='index.php?option=com_vikrentcar&task=sendcancordemail&cid[]=<?php echo $row['id']; ?>';"><?php VikRentCarIcons::e('envelope'); ?> <?php echo JText::_('VRC_SEND_CANC_EMAIL'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] == "standby" || ($row['status'] == "cancelled" && $row['consegna'] >= time())) {
				?>
				<div class="vrc-bookingdet-command">
					<button class="btn btn-success" type="button" onclick="if (confirm('<?php echo htmlspecialchars(JText::_('VRSETORDCONFIRMED')); ?> ?')) {document.location.href='index.php?option=com_vikrentcar&task=setordconfirmed&cid[]=<?php echo $row['id'] . ($row['status'] == "cancelled" ? '&skip_notification=1' : ''); ?>';}"><i class="vrcicn-checkmark"></i> <?php echo JText::_('VRSETORDCONFIRMED'); ?></button>
				</div>
				<?php
			}
			?>
			</div>

			<div class="vrc-bookingdet-tabs">
				<div class="vrc-bookingdet-tab vrc-bookingdet-tab-active" data-vrctab="vrc-tab-details"><?php echo JText::_('VRCBOOKDETTABDETAILS'); ?></div>
				<div class="vrc-bookingdet-tab" data-vrctab="vrc-tab-admin"><?php echo JText::_('VRCBOOKDETTABADMIN'); ?></div>
			</div>
		</div>

		<div class="vrc-bookingdet-tab-cont" id="vrc-tab-details" style="display: block;">
			<div class="vrc-bookingdet-innercontainer">
				<div class="vrc-bookingdet-customer">
					<div class="vrc-bookingdet-detcont<?php echo $row['closure'] > 0 ? ' vrc-bookingdet-closure' : ''; ?>">
					<?php
					$custdata_parts = explode("\n", $row['custdata']);
					if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
						//attempt to format labels and values
						foreach ($custdata_parts as $custdet) {
							if (strlen($custdet) < 1) {
								continue;
							}
							$custdet_parts = explode(':', $custdet);
							$custd_lbl = '';
							$custd_val = '';
							if (count($custdet_parts) < 2) {
								$custd_val = $custdet;
							} else {
								$custd_lbl = $custdet_parts[0];
								unset($custdet_parts[0]);
								$custd_val = trim(implode(':', $custdet_parts));
							}
							?>
						<div class="vrc-bookingdet-userdetail">
							<?php
							if (strlen($custd_lbl)) {
								?>
							<span class="vrc-bookingdet-userdetail-lbl"><?php echo $custd_lbl; ?></span>
								<?php
							}
							if (strlen($custd_val)) {
								?>
							<span class="vrc-bookingdet-userdetail-val"><?php echo $custd_val; ?></span>
								<?php
							}
							?>
						</div>
							<?php
						}
					} else {
						if ($row['closure'] > 0) {
							?>
						<div class="vrc-bookingdet-userdetail">
							<span class="vrc-bookingdet-userdetail-val"><?php echo nl2br($row['custdata']); ?></span>
						</div>
							<?php
						} else {
							echo nl2br($row['custdata']);
							?>
						<div class="vrc-bookingdet-userdetail">
							<span class="vrc-bookingdet-userdetail-val">&nbsp;</span>
						</div>
							<?php
						}
					}
					if (!empty($row['ujid'])) {
						$orig_user = JFactory::getUser($row['ujid']);
						$author_name = is_object($orig_user) && property_exists($orig_user, 'name') && !empty($orig_user->name) ? $orig_user->name : '';
						?>
						<div class="vrc-bookingdet-userdetail">
							<span class="vrc-bookingdet-userdetail-val"><?php echo JText::sprintf('VRCBOOKINGCREATEDBY', $row['ujid'].(!empty($author_name) ? ' ('.$author_name.')' : '')); ?></span>
						</div>
						<?php
					}
					?>
					</div>
				<?php
				$contracted = file_exists(VRC_SITE_PATH.DS.'resources'.DS.'pdfs'.DS.$row['id'].'_'.$row['ts'].'.pdf');
				$invoiced = file_exists(VRC_SITE_PATH.DS.'helpers'.DS.'invoices'.DS.'generated'.DS.$row['id'].'_'.$row['sid'].'.pdf');
				$cancheckin = $row['status'] == "confirmed" && !empty($row['carindex']);
				$is_ical_order = (!empty($row['idorder_ical']) && !empty($row['id_ical']));
				if ($contracted || $invoiced || $cancheckin || $is_ical_order) {
					?>
					<div class="vrc-bookingdet-detcont vrc-hidein-print">
					<?php
					if ($row['status'] == "confirmed") {
						?>
						<div>
							<span class="label label-success"><?php echo JText::_('VRCCONFIRMATIONNUMBER'); ?> <span class="badge"><?php echo $row['sid'].'_'.$row['ts']; ?></span></span>
						</div>
						<?php
					}
					if ($is_ical_order) {
						$cal_info = VRCCalendarIcal::getCalendarName($row['id_ical']);
						$cal_name = !empty($cal_info['name']) ? $cal_info['name'] : ('#' . $row['id_ical']);
						?>
						<div>
							<span class="label label-info"><?php VikRentCarIcons::e('calendar'); ?> <?php echo $cal_name; ?></span>
						</div>
						<div>
							<span class="label label-info"><?php VikRentCarIcons::e('calendar'); ?> <?php echo $row['idorder_ical']; ?></span>
						</div>
						<?php
					}
					if ($cancheckin) {
						?>
						<div>
							<a class="btn vrc-config-btn" href="index.php?option=com_vikrentcar&amp;task=customercheckin&amp;cid[]=<?php echo $row['id']; ?>"><i class="fas fa-sign-in-alt"></i> <?php echo JText::_('VRCCUSTOMERCHECKIN'); ?></a>
						</div>
						<?php
						if (file_exists(VRC_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $row['id'].'_'.$row['ts'].'_checkin.pdf')) {
						?>
						<div>
							<a href="<?php echo VRC_SITE_URI; ?>resources/pdfs/<?php echo $row['id'].'_'.$row['ts']; ?>_checkin.pdf" target="_blank" class="vrcpdfcheckin"><i class="fas fa-download"></i> <?php echo JText::_('VRCPDFCHECKIN'); ?></a>
						</div>
						<?php
						}
					}
					if ($contracted) {
						?>
						<div>
							<span class="label label-success"><span class="badge"><a href="<?php echo VRC_SITE_URI; ?>resources/pdfs/<?php echo $row['id'].'_'.$row['ts']; ?>.pdf" target="_blank"><i class="vrcicn-file-text2"></i><?php echo JText::_('VRCDOWNLOADPDF'); ?></a></span></span>
						</div>
						<?php
					}
					if ($invoiced) {
						?>
						<div>
							<span class="label label-success"><span class="badge"><a href="<?php echo VRC_SITE_URI; ?>helpers/invoices/generated/<?php echo $row['id'].'_'.$row['sid']; ?>.pdf" target="_blank"><i class="vrcicn-file-text2"></i><?php echo JText::_('VRCDOWNLOADPDFINVOICE'); ?></a></span></span>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				}
				if ($row['closure'] < 1) {
				?>
					<div class="vrc-bookingdet-detcont vrc-hidein-print">
						<label for="custmail"><?php echo JText::_('VRCCUSTEMAILADDR'); ?></label>
						<input type="text" name="custmail" id="custmail" value="<?php echo JHtml::_('esc_attr', $row['custmail']); ?>" size="25"/>
						<?php if (!empty($row['custmail'])) : ?> <button type="button" class="btn vrc-config-btn" onclick="vrcToggleSendEmail();" style="vertical-align: top;"><?php VikRentCarIcons::e('envelope'); ?> <?php echo JText::_('VRSENDEMAILACTION'); ?></button><?php endif; ?>
					</div>
					<div class="vrc-bookingdet-detcont vrc-hidein-print">
						<div class="vrc-bookingdet-lblcont">
							<label for="custphone"><?php echo JText::_('VRCUSTOMERPHONE'); ?></label>
						</div>
						<div class="vrc-bookingdet-inpwrap">
							<div class="vrc-bookingdet-inpcont">
								<?php echo $vrc_app->printPhoneInputField(array('name' => 'custphone', 'id' => 'custphone', 'value' => $this->escape($row['phone'])), array('nationalMode' => false, 'fullNumberOnBlur' => true)); ?>
							</div>
						</div>
					</div>
				<?php
				}
				?>
				</div>

				<?php
				$isdue = 0;
				?>

				<div class="vrc-bookingdet-summary">
					<div class="table-responsive">
						<table class="table">
							<tr class="vrc-bookingdet-summary-car">
								<td class="vrc-bookingdet-summary-car-firstcell">
									<div class="vrc-bookingdet-summary-carnum"><?php VikRentCarIcons::e('car'); ?></div>
								<?php
								//Car Specific Unit
								if ($row['closure'] < 1 && $row['status'] == "confirmed" && !empty($car['params'])) {
									$car_params = json_decode($car['params'], true);
									$arr_features = array();
									$unavailable_indexes = VikRentCar::getCarUnitNumsUnavailable($row);
									if (is_array($car_params) && count($car_params['features']) > 0) {
										foreach ($car_params['features'] as $cind => $cfeatures) {
											if (in_array($cind, $unavailable_indexes)) {
												continue;
											}
											foreach ($cfeatures as $fname => $fval) {
												if (strlen($fval)) {
													$arr_features[$cind] = '#'.$cind.' - '.JText::_($fname).': '.$fval;
													break;
												}
											}
										}
									}
									if (count($arr_features) > 0) {
										?>
									<div class="vrc-bookingdet-summary-carnum-chunit">
										<?php echo $vrc_app->getNiceSelect($arr_features, $row['carindex'], 'carindex', JText::_('VRCFEATASSIGNUNIT'), JText::_('VRCFEATASSIGNUNITEMPTY'), '', 'document.adminForm.submit();', 'carindex'); ?>
									</div>
										<?php
									}
								}
								if (!empty($pickup_place) && !empty($dropoff_place)) {
									?>
									<div class="vrc-bookingdet-summary-locations">
										<?php VikRentCarIcons::e('location-arrow'); ?>
										<span><?php echo $pickup_place; ?></span>
									<?php
									if ($dropoff_place != $pickup_place) {
										?>
										<span class="vrc-bookingdet-location-divider">-&gt;</span>
										<span><?php echo $dropoff_place; ?></span>
										<?php
									}
									?>
									</div>
									<?php
								}
								if (!empty($row['nominative'])) {
									?>
									<div class="vrc-bookingdet-summary-guestname">
										<span><?php echo $row['nominative']; ?></span>
									</div>
								<?php
								}
								?>
								</td>
								<td>
									<div class="vrc-bookingdet-summary-carname"><?php echo $car['name']; ?></div>
									<div class="vrc-bookingdet-summary-carrate">
									<?php
									if (!empty($row['idtar']) || $is_cust_cost) {
										if ($checkhourscharges > 0 && $aehourschbasp == true && !$is_cust_cost) {
											$ret = VikRentCar::applyExtraHoursChargesCar($tar, $row['idcar'], $checkhourscharges, $daysdiff, false, true, true);
											$tar = $ret['return'];
											$calcdays = $ret['days'];
										}
										if ($checkhourscharges > 0 && $aehourschbasp == false && !$is_cust_cost) {
											$tar = VikRentCar::extraHoursSetPreviousFareCar($tar, $row['idcar'], $checkhourscharges, $daysdiff, true);
											$tar = VikRentCar::applySeasonsCar($tar, $row['ritiro'], $row['consegna'], $row['idplace']);
											$ret = VikRentCar::applyExtraHoursChargesCar($tar, $row['idcar'], $checkhourscharges, $daysdiff, true, true, true);
											$tar = $ret['return'];
											$calcdays = $ret['days'];
										} else {
											if (!$is_cust_cost) {
												//Seasonal prices only if not a custom rate
												$tar = VikRentCar::applySeasonsCar($tar, $row['ritiro'], $row['consegna'], $row['idplace']);
											}
										}

										if (!empty($tar)) {
											$car_base_cost = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $row);
										} else {
											$car_base_cost = 0;
										}
										$isdue = $car_base_cost;

										echo $is_cust_cost || empty($tar) ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar[0]['idprice']);
										if (!empty($tar) && isset($tar[0]['attrdata']) && !empty($tar[0]['attrdata'])) {
											echo '<br/>' . VikRentCar::getPriceAttr($tar[0]['idprice']) . ': ' . $tar[0]['attrdata'];
										}
									}
									?>
									</div>
								<?php
								/**
								 * Allow to update (manage) the distinctive features for this car.
								 * 
								 * @since 	1.14.5 (J) - 1.2.0 (WP)
								 */
								if (isset($arr_features) && count($arr_features) && !empty($row['carindex']) && $row['ritiro'] < time() && abs($row['consegna'] - time()) < (86400 * 3)) {
									?>
									<div class="vrc-bookingdet-summary-mngdistfeat">
										<a href="index.php?option=com_vikrentcar&task=editcar&cid[]=<?php echo $row['idcar']; ?>#distfeatures" target="_blank"><?php VikRentCarIcons::e('edit'); ?> <?php echo JText::_('VRCDISTFEATURESMNG'); ?></a>
									</div>
									<?php
								}
								?>
								</td>
								<td>
									<div class="vrc-bookingdet-summary-price">
									<?php
									if (!empty($row['idtar']) || $is_cust_cost) {
										echo $currencyname . ' ' . VikRentCar::numberFormat($car_base_cost);
									} else {
										echo $currencyname . ' -----';
									}
									?>
									</div>
								</td>
							</tr>
							<?php
							//Options
							if (!empty($row['optionals'])) {
								$stepo = explode(";", $row['optionals']);
								$counter = 0;
								foreach ($stepo as $oo) {
									if (empty($oo)) {
										continue;
									}
									$stept = explode(":", $oo);
									$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id`=".(int)$stept[0].";";
									$dbo->setQuery($q);
									$dbo->execute();
									if ($dbo->getNumRows() != 1) {
										continue;
									}
									$counter++;
									$actopt = $dbo->loadAssocList();
									$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $row['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
									$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $row['days']) : $actopt[0]['cost'];
									if ($actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
										$realcost = $actopt[0]['maxprice'];
										if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
											$realcost = $actopt[0]['maxprice'] * $stept[1];
										}
									}
									$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $row);
									$isdue += $tmpopr;
									?>
							<tr class="vrc-bookingdet-summary-options">
								<td class="vrc-bookingdet-summary-options-title"><?php echo $counter == 1 ? JText::_('VREDITORDEREIGHT') : '&nbsp;'; ?></td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name']; ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname." ".VikRentCar::numberFormat($tmpopr); ?></span>
								</td>
							</tr>
								<?php
								}
							}
							//VRC 1.7 - Location fees
							if (!empty($row['idplace']) && !empty($row['idreturnplace'])) {
								$locfee = VikRentCar::getLocFee($row['idplace'], $row['idreturnplace']);
								if ($locfee) {
									//Location fees overrides
									if (strlen($locfee['losoverride']) > 0) {
										$arrvaloverrides = array();
										$valovrparts = explode('_', $locfee['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($row['days'], $arrvaloverrides)) {
											$locfee['cost'] = $arrvaloverrides[$row['days']];
										}
									}
									//
									$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $row['days']) : $locfee['cost'];
									$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $row);
									$isdue += $locfeewith;
									?>
							<tr class="vrc-bookingdet-summary-custcosts">
								<td class="vrc-bookingdet-summary-custcosts-title">&nbsp;</td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo JText::_('VREDITORDERTEN'); ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname." ".VikRentCar::numberFormat($locfeewith); ?></span>
								</td>
							</tr>
									<?php
								}
							}
							//VRC 1.9 - Out of Hours Fees
							$oohfee = VikRentCar::getOutOfHoursFees($row['idplace'], $row['idreturnplace'], $row['ritiro'], $row['consegna'], array('id' => $row['idcar']));
							if (count($oohfee) > 0) {
								$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
								$isdue += $oohfeewith;
								?>
							<tr class="vrc-bookingdet-summary-custcosts">
								<td class="vrc-bookingdet-summary-custcosts-title">&nbsp;</td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo JText::_('VRCOOHFEEAMOUNT'); ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname." ".VikRentCar::numberFormat($oohfeewith); ?></span>
								</td>
							</tr>
								<?php
							}
							//Custom extra costs
							if (!empty($row['extracosts'])) {
								$counter = 0;
								$cur_extra_costs = json_decode($row['extracosts'], true);
								foreach ($cur_extra_costs as $eck => $ecv) {
									$counter++;
									$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $row);
									$isdue += $efee_cost;
									?>
							<tr class="vrc-bookingdet-summary-custcosts">
								<td class="vrc-bookingdet-summary-custcosts-title"><?php echo $counter == 1 ? JText::_('VRPEDITBUSYEXTRACOSTS') : '&nbsp;'; ?></td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo $ecv['name']; ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname." ".VikRentCar::numberFormat($efee_cost); ?></span>
								</td>
							</tr>
									<?php
								}
							}
							//VRC 1.6 - Coupon
							$usedcoupon = false;
							$origisdue = $isdue;
							if (strlen($row['coupon']) > 0) {
								$usedcoupon = true;
								$expcoupon = explode(";", $row['coupon']);
								$isdue = $isdue - $expcoupon[1];
								?>
							<tr class="vrc-bookingdet-summary-coupon">
								<td><?php echo JText::_('VRCCOUPON'); ?></td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo $expcoupon[2]; ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost">- <?php echo $currencyname; ?> <?php echo VikRentCar::numberFormat($expcoupon[1]); ?></span>
								</td>
							</tr>
								<?php
							}
							//Order Total
							?>
							<tr class="vrc-bookingdet-summary-total">
								<td>
									<span class="vrapplydiscsp" onclick="toggleDiscount(this);">
										<i class="<?php echo VikRentCarIcons::i('chevron-down'); ?>" title="<?php echo JText::_('VRCAPPLYDISCOUNT'); ?>"></i>
									</span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo JText::_('VREDITORDERNINE'); ?></span>

									<div class="vrdiscenter" id="vrdiscenter" style="display: none;">
										<div class="vrdiscenter-entry">
											<span class="vrdiscenter-label"><?php echo JText::_('VRCAPPLYDISCOUNT'); ?>:</span><span class="vrdiscenter-value"><?php echo $currencyname; ?> <input type="number" step="any" name="admindisc" value="" size="4"/></span>
										</div>
										<div class="vrdiscenter-entrycentered">
											<button type="submit" class="btn btn-success"><?php echo JText::_('VRCAPPLYDISCOUNTSAVE'); ?></button>
										</div>
									</div>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname; ?> <?php echo VikRentCar::numberFormat($row['order_total']); ?></span>
								</td>
							</tr>
						<?php
						if (!empty($row['totpaid']) && $row['totpaid'] > 0) {
							$diff_to_pay = $row['order_total'] - $row['totpaid'];
							?>
							<tr class="vrc-bookingdet-summary-totpaid">
								<td>&nbsp;</td>
								<td><?php echo JText::_('VRCAMOUNTPAID'); ?></td>
								<td><?php echo $currencyname.' '.VikRentCar::numberFormat($row['totpaid']); ?></td>
							</tr>
							<?php
							if ($diff_to_pay > 1) {
							?>
							<tr class="vrc-bookingdet-summary-totpaid vrc-bookingdet-summary-totremaining">
								<td>&nbsp;</td>
								<td>
									<div><?php echo JText::_('VRCTOTALREMAINING'); ?></div>
									<?php
									// enable second payment
									if ($row['status'] == 'confirmed' && !($row['paymcount'] > 0) && VikRentCar::multiplePayments() && is_array($payment) && !empty($payment['id'])) {
										?>
										<div style="margin-top: 5px;">
											<a href="index.php?option=com_vikrentcar&amp;task=editorder&amp;makepay=1&amp;cid[]=<?php echo $row['id']; ?>" class="vrc-makepayable-link"><?php VikRentCarIcons::e('credit-card'); ?> <?php echo JText::_('VRCMAKEORDERPAYABLE'); ?></a>
										</div>
										<?php
									}
									//
									?>
								</td>
								<td><?php echo $currencyname.' '.VikRentCar::numberFormat($diff_to_pay); ?></td>
							</tr>
							<?php
							}
						}
						if ($row['status'] == 'confirmed' && VikRentCar::multiplePayments() && is_array($payment) && !empty($payment['id']) && $row['consegna'] > time()) {
							/**
							 * The amount payable can be modified by the admin.
							 * 
							 * @since 	1.14.5 (J) - 1.2.0 (WP)
							 */
							?>
							<tr class="vrc-bookingdet-summary-totpaid vrc-bookingdet-summary-totpayable">
								<td>&nbsp;</td>
								<td>
									<span class="vrc-amount-payable-lbl<?php echo $row['payable'] > 0 ? ' vrc-amount-payable-lbl-requested' : ''; ?>"><?php echo $row['payable'] > 0 ? JText::_('VRC_AMOUNT_PAYABLE') : JText::_('VRC_AMOUNT_PAYABLE_RQ'); ?></span>
								</td>
								<td>
									<div id="vrc-amountpayable-cont">
									<?php
									if ($row['payable'] > 0) {
										?>
										<span id="vrc-amountpayable-current"><?php echo $currencyname . ' ' . VikRentCar::numberFormat($row['payable']); ?></span>
										<?php
									}
									?>
										<span id="vrc-amountpayable-edit" style="margin-left: 5px; cursor: pointer;"><?php VikRentCarIcons::e('edit'); ?></span>
									</div>
									<div id="vrc-amountpayable-modcont" style="display: none;">
										<span id="vrc-amountpayable-cancedit" style="margin-right: 5px; cursor: pointer;"><?php VikRentCarIcons::e('times'); ?></span>
										<span id="vrc-amountpayable-new"><input type="number" step="any" name="newamountpayable" value="" min="0" style="margin: 0;" placeholder="<?php echo $row['payable']; ?>" disabled /></span>
										<span id="vrc-amountpayable-save"><button type="submit" class="btn btn-success"><?php echo JText::_('VRCAPPLYDISCOUNTSAVE'); ?></button></span>
									</div>
								</td>
							</tr>
							<?php
						}
						?>
						</table>
					</div>
				</div>
			</div>
		</div>

		<div class="vrc-bookingdet-tab-cont" id="vrc-tab-admin" style="display: none;">
			<div class="vrc-bookingdet-innercontainer">
				<div class="vrc-bookingdet-admindata">

					<div class="vrc-bookingdet-admin-entry">
						<label for="vrc-searchcust"><?php echo JText::_(count($customer) ? 'VRCASSIGNNEWCUST' : 'VRFILLCUSTFIELDS'); ?></label>
						<span style="display: block;"><?php echo JText::_('VRCSEARCHEXISTCUST'); ?></span>
						<span class="vrc-eorder-assigncust" style="margin-bottom: 1px;">
							<input type="text" id="vrc-searchcust" autocomplete="off" value="" placeholder="<?php echo JText::_('VRCSEARCHCUSTBY'); ?>" size="30" style="margin-bottom: 0;" />
						</span>
						<span id="vrc-searchcust-loading">
							<i class="vrcicn-hour-glass"></i>
						</span>
						<input type="hidden" name="newcustid" id="newcustid" value="" />
						<div id="vrc-searchcust-res" style="position: absolute; background-color: #fff;"></div>
						<span class="vrc-eorder-assignnewcust" style="display: block; margin-top: 10px;">
							<a class="vrc-assign-customer" href="index.php?option=com_vikrentcar&task=newcustomer&bid=<?php echo $row['id']; ?>&goto=<?php echo base64_encode($gotouri.'#tab-admin'); ?>">
								<?php VikRentCarIcons::e('user-circle'); ?>
								<span><?php echo JText::_('VRCCREATENEWCUST'); ?></span>
							</a>
						</span>
					</div>

					<div class="vrc-bookingdet-admin-entry">
						<label for="newpayment"><?php echo JText::_('VRPAYMENTMETHOD'); ?></label>
					<?php
					if (is_array($payment)) {
						?>
						<span><?php echo $payment['name']; ?></span>
						<?php
					}
					$chpayment = '';
					if (is_array($payments)) {
						$chpayment = '<div><select name="newpayment" id="newpayment" onchange="changePayment();"><option value="">'.JText::_('VRCCHANGEPAYLABEL').'</option>';
						foreach($payments as $pay) {
							$chpayment .= '<option value="'.$pay['id'].'">'.(is_array($payment) && $payment['id'] == $pay['id'] ? ' ::' : '').$pay['name'].'</option>';
						}
						$chpayment .= '</select></div>';
					}
					echo $chpayment;
					?>
					</div>
				<?php
				$tn = VikRentCar::getTranslator();
				$all_langs = $tn->getLanguagesList();
				if (count($all_langs) > 1) {
				?>
					<div class="vrc-bookingdet-admin-entry">
						<label for="newlang"><?php echo JText::_('VRCBOOKINGLANG'); ?></label>
						<select name="newlang" id="newlang" onchange="document.adminForm.submit();">
						<?php
						foreach ($all_langs as $lk => $lv) {
							?>
							<option value="<?php echo JHtml::_('esc_attr', $lk); ?>"<?php echo $row['lang'] == $lk ? ' selected="selected"' : ''; ?>><?php echo isset($lv['nativeName']) ? $lv['nativeName'] : $lv['name']; ?></option>
							<?php
						}
						?>
						</select>
					</div>
				<?php
				}
				?>
				</div>
				<div class="vrc-bookingdet-noteslogs">
					<?php
					$history = $history_obj->loadHistory();
					?>
					<div class="vrc-bookingdet-noteslogs-btns">
						<div class="vrc-bookingdet-noteslogs-btn vrc-bookingdet-noteslogs-btn-active">
							<a href="javascript: void(0);" id="vrc-trig-notes" onclick="javascript: vrToggleNotes(this);"><?php VikRentCarIcons::e('user-lock'); ?> <?php echo JText::_('VRCTOGGLEORDNOTES'); ?></a>
						</div>
					<?php
					if (count($history)) {
						?>
						<div class="vrc-bookingdet-noteslogs-btn">
							<a href="javascript: void(0);" id="vrc-trig-bookhistory" onclick="javascript: vrToggleHistory(this);"><?php VikRentCarIcons::e('history'); ?> <?php echo JText::_('VRCBOOKHISTORYTAB'); ?></a>
						</div>
						<script type="text/javascript">
						if (window.location.hash == '#bookhistory') {
							setTimeout(function() {
								jQuery(".vrc-bookingdet-tab[data-vrctab='vrc-tab-admin']").trigger('click');
								vrToggleHistory(document.getElementById('vrc-trig-bookhistory'));
							}, 500);
						}
						</script>
						<?php
					}
					if (!empty($row['paymentlog'])) {
						?>
						<div class="vrc-bookingdet-noteslogs-btn">
							<a href="javascript: void(0);" id="vrc-trig-paylogs" onclick="javascript: vrToggleLog(this);"><?php VikRentCarIcons::e('credit-card'); ?> <?php echo JText::_('VRCPAYMENTLOGTOGGLE'); ?></a>
							<a name="paymentlog" href="javascript: void(0);"></a>
						</div>
						<?php
					}
					?>
					</div>
					<div class="vrc-bookingdet-noteslogs-cont">
						<div id="vradminnotesdiv" style="display: block;">
							<textarea name="adminnotes" class="vradminnotestarea"><?php echo JHtml::_('esc_textarea', $row['adminnotes']); ?></textarea>
							<br clear="all"/>
							<input type="submit" name="updadmnotes" value="<?php echo JHtml::_('esc_attr', JText::_('VRCUPDATEBTN')); ?>" class="btn vrc-config-btn" />
						</div>
					<?php
					if (count($history)) {
						?>
						<div id="vrchistorydiv" style="display: none;">
							<div class="vrc-booking-history-container table-responsive">
								<table class="table">
									<thead>
										<tr class="vrc-booking-history-firstrow">
											<td class="vrc-booking-history-td-type"><?php echo JText::_('VRCBOOKHISTORYLBLTYPE'); ?></td>
											<td class="vrc-booking-history-td-date"><?php echo JText::_('VRCBOOKHISTORYLBLDATE'); ?></td>
											<td class="vrc-booking-history-td-descr"><?php echo JText::_('VRCBOOKHISTORYLBLDESC'); ?></td>
											<td class="vrc-booking-history-td-totpaid"><?php echo JText::_('VRCBOOKHISTORYLBLTPAID'); ?></td>
											<td class="vrc-booking-history-td-tot"><?php echo JText::_('VRCBOOKHISTORYLBLTOT'); ?></td>
										</tr>
									</thead>
									<tbody>
									<?php
									foreach ($history as $hist) {
										$hdescr = strpos($hist['descr'], '<') !== false ? $hist['descr'] : nl2br($hist['descr']);
										?>
										<tr class="vrc-booking-history-row">
											<td><?php echo $history_obj->validType($hist['type'], true); ?></td>
											<td>
											<?php
											echo JHtml::_('date', $hist['dt']);
											?>
											</td>
											<td><?php echo $hdescr; ?></td>
											<td><?php echo $currencyname . ' ' . VikRentCar::numberFormat($hist['totpaid']); ?></td>
											<td><?php echo $currencyname . ' ' . VikRentCar::numberFormat($hist['total']); ?></td>
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
					if (!empty($row['paymentlog'])) {
						?>
						<div id="vrpaymentlogdiv" style="display: none;">
							<pre style="min-height: 100%;"><?php echo htmlspecialchars($row['paymentlog']); ?></pre>
						</div>
						<script type="text/javascript">
						if (window.location.hash == '#paymentlog') {
							setTimeout(function() {
								jQuery(".vrc-bookingdet-tab[data-vrctab='vrc-tab-admin']").trigger('click');
								vrToggleLog(document.getElementById('vrc-trig-paylogs'));
							}, 500);
						}
						</script>
						<?php
					}
					?>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="task" value="editorder">
		<input type="hidden" name="vrc_active_tab" id="vrc_active_tab" value="">
		<input type="hidden" name="whereup" value="<?php echo (int)$row['id']; ?>">
		<input type="hidden" name="cid[]" value="<?php echo (int)$row['id']; ?>">
		<input type="hidden" name="option" value="com_vikrentcar">
		<?php
		$tmpl = VikRequest::getVar('tmpl');
		if ($tmpl == 'component') {
			echo '<input type="hidden" name="tmpl" value="component">';
		}
		$pgoto = VikRequest::getString('goto', '', 'request');
		if (!empty($pgoto)) {
			echo '<input type="hidden" name="goto" value="' . JHtml::_('esc_attr', $pgoto) . '">';
		}
		?>
	</form>
</div>

<div class="vrc-modal-overlay-block vrc-modal-overlay-block-sendemail">
	<a class="vrc-modal-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-modal-overlay-content vrc-modal-overlay-content-large vrc-modal-overlay-content-sendemail">
		<div class="vrc-modal-overlay-content-head vrc-modal-overlay-content-sms-email-head">
			<h3>
				<span><?php echo JText::_('VRSENDEMAILACTION'); ?>: <span id="emailto-lbl"><?php echo $row['custmail']; ?></span></span>
				<span class="vrc-modal-overlay-close-times" onclick="vrcToggleSendEmail();">&times;</span>
			</h3>
		</div>
		<div class="vrc-modal-overlay-content-body vrc-modal-overlay-content-body-scroll">
			<div id="vrc-overlay-email-cont">
				<form action="index.php?option=com_vikrentcar" method="post" enctype="multipart/form-data" id="vrc-modal-form-email">
					<input type="hidden" name="bid" value="<?php echo (int)$row['id']; ?>" />
				<?php
				$cur_emtpl = array();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='customemailtpls';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$cur_emtpl = $dbo->loadResult();
					$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
					$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
				}
				if (count($cur_emtpl) > 0) {
					?>
					<div class="vrc-calendar-custmail-tpls-wrap">
						<select id="emtpl-customemail" onchange="vrcLoadEmailTpl(this.value);">
							<option value=""><?php echo JText::_('VREMAILCUSTFROMTPL'); ?></option>
						<?php
						foreach ($cur_emtpl as $emk => $emv) {
							?>
							<optgroup label="<?php echo JHtml::_('esc_attr', $emv['emailsubj']); ?>">
								<option value="<?php echo JHtml::_('esc_attr', $emk); ?>"><?php echo JText::_('VREMAILCUSTFROMTPLUSE'); ?></option>
								<option value="rm<?php echo JHtml::_('esc_attr', $emk); ?>"><?php echo JText::_('VREMAILCUSTFROMTPLRM'); ?></option>
							</optgroup>
							<?php
						}
						?>
						</select>
					</div>
					<?php
				}

				/**
				 * Load all conditional text special tags.
				 * 
				 * @since 	1.15.0 (J) - 1.3.0 (WP)
				 */
				$extra_btns = array();
				$condtext_tags = VikRentCar::getConditionalRulesInstance()->getSpecialTags();
				if (count($condtext_tags)) {
					$condtext_tags = array_keys($condtext_tags);
					foreach ($condtext_tags as $tag) {
						array_push($extra_btns, '<button type="button" class="btn btn-secondary btn-small vrc-condtext-specialtag-btn" onclick="setSpecialTplTag(\'emailcont\', \'' . $tag . '\');">' . $tag . '</button>');
					}
				}
				?>
					<div class="vrc-calendar-cfield-entry">
						<label for="emailsubj"><?php echo JText::_('VRSENDEMAILCUSTSUBJ'); ?></label>
						<span><input type="text" name="emailsubj" id="emailsubj" value="" size="30" /></span>
					</div>
					<div class="vrc-calendar-cfield-entry">
						<label for="emailcont"><?php echo JText::_('VRSENDEMAILCUSTCONT'); ?></label>
						<?php
						$special_tags_base = array(
							'{customer_name}',
							'{order_id}',
							'{pickup_date}',
							'{dropoff_date}',
							'{num_days}',
							'{pickup_place}',
							'{dropoff_place}',
							'{car_name}',
							'{total}',
							'{total_paid}',
							'{remaining_balance}',
							'{order_link}',
						);

						$special_tags_base_html = '';
						foreach ($special_tags_base as $sp_tag) {
							$special_tags_base_html .= '<button type="button" class="btn btn-secondary btn-small" onclick="setSpecialTplTag(\'emailcont\', \'' . $sp_tag . '\');">' . $sp_tag . '</button>' . "\n";
						}

						/**
						 * Use the rich text editor (visual editor) to build custom email messages.
						 * 
						 * @since 	1.15.0 (J) - 1.3.0 (WP)
						 */
						$tarea_attr = array(
							'id' => 'emailcont',
							'rows' => '7',
							'cols' => '170',
							'style' => 'width: 99%; min-width: 99%; max-width: 99%; height: 120px; margin-bottom: 1px;',
						);
						$editor_opts = array(
							'modes' => array(
								'text',
								'visual',
							),
						);
						$editor_btns = $special_tags_base;
						if (count($condtext_tags)) {
							$editor_btns = array_merge($editor_btns, $condtext_tags);
						}
						echo $vrc_app->renderVisualEditor('emailcont', '', $tarea_attr, $editor_opts, $editor_btns);
						?>
						<div class="btn-group pull-left vrc-smstpl-bgroup vrc-custmail-bgroup vik-contentbuilder-textmode-sptags">
							<?php echo $special_tags_base_html . "\n" . implode("\n", $extra_btns); ?>
						</div>
					</div>
					<div class="vrc-calendar-cfield-entry">
						<label for="emailattch"><?php echo JText::_('VRSENDEMAILCUSTATTCH'); ?></label>
						<span><input type="file" name="emailattch" id="emailattch" /></span>
					</div>
					<div class="vrc-calendar-cfield-entry">
						<label for="emailfrom"><?php echo JText::_('VRSENDEMAILCUSTFROM'); ?></label>
						<span><input type="text" name="emailfrom" id="emailfrom" value="<?php echo JHtml::_('esc_attr', VikRentCar::getSenderMail()); ?>" size="30" /></span>
					</div>
					<input type="hidden" name="email" id="emailto" value="<?php echo JHtml::_('esc_attr', $row['custmail']); ?>" />
					<input type="hidden" name="goto" value="<?php echo urlencode('index.php?option=com_vikrentcar&task=editorder&cid[]='.$row['id']); ?>" />
					<input type="hidden" name="task" value="sendcustomemail" />
				</form>
			</div>
		</div>
		<div class="vrc-modal-overlay-content-footer">
			<div class="vrc-modal-overlay-content-footer-right">
				<button type="button" class="btn vrc-config-btn" onclick="document.getElementById('vrc-modal-form-email').submit();"><?php VikRentCarIcons::e('envelope'); ?> <?php echo JText::_('VRSENDEMAILACTION'); ?></button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var vrc_overlay_on = false;
var vrc_print_only = false;
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
}
function vrcToggleSendEmail() {
	var cur_email = jQuery("#emailto").val();
	var email_set = jQuery("#custmail").val();
	if (email_set.length && email_set != cur_email) {
		jQuery("#emailto").val(email_set);
		jQuery("#emailto-lbl").text(email_set);
	}
	jQuery(".vrc-modal-overlay-block-sendemail").fadeToggle(400, function() {
		if (jQuery(".vrc-modal-overlay-block-sendemail").is(":visible")) {
			vrc_overlay_on = true;
		} else {
			vrc_overlay_on = false;
		}
	});
}
function setSpecialTplTag(taid, tpltag) {
	var tplobj = document.getElementById(taid);
	if (tplobj != null) {
		var start = tplobj.selectionStart;
		var end = tplobj.selectionEnd;
		tplobj.value = tplobj.value.substring(0, start) + tpltag + tplobj.value.substring(end);
		tplobj.selectionStart = tplobj.selectionEnd = start + tpltag.length;
		tplobj.focus();
	}
}
jQuery(document).ready(function() {
	// sessionStorage for current tab
	if (typeof sessionStorage !== 'undefined' && !window.location.hash) {
		var curtab = sessionStorage.getItem('vrcEditOrderTab<?php echo $row['id']; ?>');
		switch (curtab) {
			case 'notes' :
				setTimeout(function() {
					jQuery(".vrc-bookingdet-tab[data-vrctab='vrc-tab-admin']").trigger('click');
					vrToggleNotes(document.getElementById('vrc-trig-notes'));
				}, 100);
				break;
			case 'history' :
				setTimeout(function() {
					jQuery(".vrc-bookingdet-tab[data-vrctab='vrc-tab-admin']").trigger('click');
					vrToggleHistory(document.getElementById('vrc-trig-bookhistory'));
				}, 100);
				break;
			case 'paylogs' :
				setTimeout(function() {
					jQuery(".vrc-bookingdet-tab[data-vrctab='vrc-tab-admin']").trigger('click');
					vrToggleLog(document.getElementById('vrc-trig-paylogs'));
				}, 100);
				break;
			default :
				break;
		}
	}
	jQuery(".vrc-bookingdet-tab").click(function() {
		var newtabrel = jQuery(this).attr('data-vrctab');
		var oldtabrel = jQuery(".vrc-bookingdet-tab-active").attr('data-vrctab');
		if (newtabrel == oldtabrel) {
			return;
		}
		if (newtabrel == 'vrc-tab-details' && typeof sessionStorage !== 'undefined') {
			sessionStorage.setItem('vrcEditOrderTab<?php echo $row['id']; ?>', 'details');
		}
		jQuery(".vrc-bookingdet-tab").removeClass("vrc-bookingdet-tab-active");
		jQuery(this).addClass("vrc-bookingdet-tab-active");
		jQuery("#"+oldtabrel).hide();
		jQuery("#"+newtabrel).fadeIn();
		jQuery("#vrc_active_tab").val(newtabrel);
	});
	jQuery(".vrc-bookingdet-tab[data-vrctab='<?php echo $pactive_tab; ?>']").trigger('click');
	if (window.location.hash == '#tab-admin') {
		setTimeout(function() {
			jQuery(".vrc-bookingdet-tab[data-vrctab='vrc-tab-admin']").trigger('click');
		}, 100);
	}
	// edit amount payable
	jQuery('#vrc-amountpayable-edit').click(function() {
		jQuery('#vrc-amountpayable-cont').hide();
		jQuery('#vrc-amountpayable-modcont').show();
		jQuery('input[name="newamountpayable"]').prop('disabled', false);
	});
	jQuery('#vrc-amountpayable-cancedit').click(function() {
		jQuery('#vrc-amountpayable-modcont').hide();
		jQuery('#vrc-amountpayable-cont').show();
	});
	jQuery(document).mouseup(function(e) {
		if (!vrc_overlay_on) {
			return false;
		}
		var vrc_overlay_cont = jQuery(".vrc-modal-overlay-content-sendemail");
		if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0 && !jQuery(e.target).is('svg')) {
			jQuery(".vrc-modal-overlay-block-sendemail").fadeOut();
			vrc_overlay_on = false;
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vrc_overlay_on) {
			jQuery(".vrc-modal-overlay-block-sendemail").fadeOut();
			vrc_overlay_on = false;
		}
	});
	// Search customer - Start
	var vrccustsdelay = (function() {
		var timer = 0;
		return function(callback, ms) {
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();
	function vrcCustomerSearch(words) {
		jQuery("#vrc-searchcust-res").hide().html("");
		jQuery("#vrc-searchcust-loading").show();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikrentcar", task: "searchcustomer", kw: words, tmpl: "component" }
		}).done(function(cont) {
			if (cont.length) {
				var obj_res = JSON.parse(cont);
				customers_search_vals = obj_res[0];
				jQuery("#vrc-searchcust-res").html(obj_res[1]);
			} else {
				customers_search_vals = "";
				jQuery("#vrc-searchcust-res").html("----");
			}
			jQuery("#vrc-searchcust-res").show();
			jQuery("#vrc-searchcust-loading").hide();
		}).fail(function() {
			jQuery("#vrc-searchcust-loading").hide();
			alert("Error Searching.");
		});
	}
	jQuery("#vrc-searchcust").keyup(function(event) {
		vrccustsdelay(function() {
			var keywords = jQuery("#vrc-searchcust").val();
			var chars = keywords.length;
			if (chars > 1) {
				if ((event.which > 96 && event.which < 123) || (event.which > 64 && event.which < 91) || event.which == 13) {
					vrcCustomerSearch(keywords);
				}
			} else {
				if (jQuery("#vrc-searchcust-res").is(":visible")) {
					jQuery("#vrc-searchcust-res").hide();
				}
			}
		}, 600);
	});
	jQuery(document).on("click", ".vrc-custsearchres-entry", function() {
		var custid = jQuery(this).attr("data-custid");
		if (confirm('<?php echo addslashes(JText::_('VRCASSIGNNEWCUSTCONF')); ?>')) {
			jQuery('#newcustid').val(custid);
			document.adminForm.submit();
			return;
		}
	});
	// Search customer - End
});
var cur_emtpl = <?php echo json_encode($cur_emtpl); ?>;
function vrcLoadEmailTpl(tplind) {
	if (!(tplind.length > 0)) {
		jQuery('#emailsubj').val('');
		jQuery('#emailcont').val('');
		return true;
	}
	if (tplind.substr(0, 2) == 'rm') {
		if (confirm('<?php echo addslashes(JText::_('VRCDELCONFIRM')); ?>')) {
			document.location.href = 'index.php?option=com_vikrentcar&task=rmcustomemailtpl&cid[]=<?php echo $row['id']; ?>&tplind='+tplind.substr(2);
		}
		return false;
	}
	if (!cur_emtpl.hasOwnProperty(tplind)) {
		jQuery('#emailsubj').val('');
		jQuery('#emailcont').val('').trigger('change');
		return true;
	}
	jQuery('#emailsubj').val(cur_emtpl[tplind]['emailsubj']);
	jQuery('#emailcont').val(cur_emtpl[tplind]['emailcont']).trigger('change');
	jQuery('#emailfrom').val(cur_emtpl[tplind]['emailfrom']);
	return true;
}
<?php
$pcustomemail = VikRequest::getInt('customemail', '', 'request');
if ($pcustomemail > 0) {
	?>
	vrcToggleSendEmail();
	<?php
}
?>
</script>
