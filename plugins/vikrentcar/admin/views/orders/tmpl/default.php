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
$all_locations = $this->all_locations;
$plocation = $this->plocation;
$plocationw = $this->plocationw;
$orderby = $this->orderby;
$ordersort = $this->ordersort;
$allcars = $this->allcars;

$dbo = JFactory::getDbo();
JHTML::_('behavior.tooltip');
$nowdf = VikRentCar::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$juidf = $nowdf == "%d/%m/%Y" ? 'dd/mm/yy' : ($nowdf == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');
$currencysymb = VikRentCar::getCurrencySymb(true);
$nowtf = VikRentCar::getTimeFormat(true);
$app = JFactory::getApplication();
$vrc_app = new VrcApplication();
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery-ui.min.css');
JHtml::_('jquery.framework', true, true);
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.min.js');
$ldecl = '
jQuery(function($){'."\n".'
	$.datepicker.regional["vikrentcar"] = {'."\n".'
		closeText: "'.JText::_('VRJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VRJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VRJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VRJQCALTODAY').'",'."\n".'
		monthNames: ["'.JText::_('VRMONTHONE').'","'.JText::_('VRMONTHTWO').'","'.JText::_('VRMONTHTHREE').'","'.JText::_('VRMONTHFOUR').'","'.JText::_('VRMONTHFIVE').'","'.JText::_('VRMONTHSIX').'","'.JText::_('VRMONTHSEVEN').'","'.JText::_('VRMONTHEIGHT').'","'.JText::_('VRMONTHNINE').'","'.JText::_('VRMONTHTEN').'","'.JText::_('VRMONTHELEVEN').'","'.JText::_('VRMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(JText::_('VRMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.JText::_('VRSUNDAY').'", "'.JText::_('VRMONDAY').'", "'.JText::_('VRTUESDAY').'", "'.JText::_('VRWEDNESDAY').'", "'.JText::_('VRTHURSDAY').'", "'.JText::_('VRFRIDAY').'", "'.JText::_('VRSATURDAY').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(JText::_('VRSUNDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRMONDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRTUESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRWEDNESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRTHURSDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRFRIDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRSATURDAY'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(JText::_('VRSUNDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRMONDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRTUESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRWEDNESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRTHURSDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRFRIDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRSATURDAY'), 0, 2, 'UTF-8').'"],'."\n".'
		weekHeader: "'.JText::_('VRJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikRentCar::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikrentcar"]);'."\n".'
});';
$document->addScriptDeclaration($ldecl);
$filtnc = VikRequest::getString('filtnc', '', 'request');
$cid = VikRequest::getVar('cid', array(0));
$pcust_id = $app->getUserStateFromRequest("vrc.orders.cust_id", 'cust_id', 0, 'int');

$loc_options = '';
if (is_array($all_locations)) {
	$loc_options = '<option value="">'.JText::_('VRCORDERSLOCFILTERANY').'</option>'."\n";
	$loc_options .= '<optgroup label="'.JText::_('VRCORDERSLOCFILTERPICK').'">'."\n";
	foreach ($all_locations as $location) {
		$loc_options .= '<option data-locw="pickup" value="'.$location['id'].'"'.($plocationw == 'pickup' && $location['id'] == $plocation ? ' selected="selected"' : '').'>'.$location['name'].'</option>'."\n";
	}
	$loc_options .= '</optgroup>'."\n";
	$loc_options .= '<optgroup label="'.JText::_('VRCORDERSLOCFILTERDROP').'">'."\n";
	foreach ($all_locations as $location) {
		$loc_options .= '<option data-locw="dropoff" value="'.$location['id'].'"'.($plocationw == 'dropoff' && $location['id'] == $plocation ? ' selected="selected"' : '').'>'.$location['name'].'</option>'."\n";
	}
	$loc_options .= '</optgroup>'."\n";
	$loc_options .= '<optgroup label="'.JText::_('VRCORDERSLOCFILTERPICKDROP').'">'."\n";
	foreach ($all_locations as $location) {
		$loc_options .= '<option data-locw="both" value="'.$location['id'].'"'.($plocationw == 'both' && $location['id'] == $plocation ? ' selected="selected"' : '').'>'.$location['name'].'</option>'."\n";
	}
	$loc_options .= '</optgroup>'."\n";
}

if (empty($rows)) {
	$rows = array();
	?>
	<p class="warn"><?php echo JText::_('VRNOORDERSFOUND'); ?></p>
	<?php
}

$filters_set = false;
?>
<form action="index.php?option=com_vikrentcar&task=orders" method="post" name="adminForm" id="adminForm" class="vrc-allorders-fm">

	<div id="filter-bar" class="btn-toolbar vrc-btn-toolbar" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-left input-append">
			<input type="text" name="filtnc" id="filtnc" autocomplete="off" placeholder="<?php echo JHtml::_('esc_attr', JText::_('VRCFILTCNAMECNUMB')); ?>" value="<?php echo (strlen($filtnc) > 0 ? JHtml::_('esc_attr', $filtnc) : ''); ?>" size="30" />
			<button type="submit" class="btn"><i class="icon-search"></i></button>
		</div>
		<?php
		$cust_id_filter = false;
		if (is_array($rows) && isset($rows[0]) && array_key_exists('customer_fullname', $rows[0])) {
			//customer ID filter
			$cust_id_filter = true;
		}
		?>
		<div class="btn-group pull-left input-append">
			<input type="text" id="customernominative" autocomplete="off" placeholder="<?php echo JHtml::_('esc_attr', JText::_('VRCUSTOMERNOMINATIVE')); ?>" value="<?php echo $cust_id_filter ? htmlspecialchars($rows[0]['customer_fullname']) : ''; ?>" size="30" />
			<button type="button" class="btn<?php echo $cust_id_filter ? ' btn-danger' : ''; ?>" onclick="<?php echo $cust_id_filter ? 'document.location.href=\'index.php?option=com_vikrentcar&task=orders\'' : 'document.getElementById(\'customernominative\').focus();'; ?>"><i class="<?php echo $cust_id_filter ? 'icon-remove' : 'icon-user'; ?>"></i></button>
			<div id="vrc-allbsearchcust-res" class="vrc-allbsearchcust-res" style="display: none;"></div>
		</div>
		<div class="btn-group pull-left">
			<button type="button" class="btn" id="vrc-search-tools-btn" onclick="if(jQuery(this).hasClass('btn-primary')){jQuery('#vrc-search-tools-cont').hide();jQuery(this).removeClass('btn-primary');}else{jQuery('#vrc-search-tools-cont').show();jQuery(this).addClass('btn-primary');}"><?php echo JText::_('JSEARCH_TOOLS'); ?> <span class="caret"></span></button>
		</div>
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="jQuery('#filter-bar, #vrc-search-tools-cont').find('input, select').val('');document.getElementById('cust_id').value='';document.adminForm.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div id="vrc-search-tools-cont" class="js-stools-container-filters clearfix" style="display: none;">
			<div class="btn-group pull-left">
			<?php
			$pidcar = $app->getUserStateFromRequest("vrc.orders.idcar", 'idcar', 0, 'int');
			if (count($allcars) > 0) {
				$filters_set = !empty($pidcar) || $filters_set;
				$rsel = '<select name="idcar"><option value="">'.JText::_('VRCARFILTER').'</option>';
				foreach ($allcars as $car) {
					$rsel .= '<option value="'.$car['id'].'"'.(!empty($pidcar) && $pidcar == $car['id'] ? ' selected="selected"' : '').'>'.$car['name'].'</option>';
				}
				$rsel .= '</select>';
			}
			echo $rsel;
			?>
			</div>
			<div class="btn-group pull-left">
				<select name="idpayment">
					<option value=""><?php echo JText::_('VRFILTERBYPAYMENT'); ?></option>
				<?php
				$pidpayment = $app->getUserStateFromRequest("vrc.orders.idpayment", 'idpayment', 0, 'int');
				$payment_filter = '';
				if (!empty($pidpayment)) {
					$filters_set = !empty($pidpayment) || $filters_set;
					$payment_filter = '&amp;idpayment='.$pidpayment;
				}
				$allpayments = array();
				$q = "SELECT `id`,`name` FROM `#__vikrentcar_gpayments` ORDER BY `name` ASC;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$allpayments = $dbo->loadAssocList();
				}
				foreach ($allpayments as $paym) {
					?>
					<option value="<?php echo (int)$paym['id']; ?>"<?php echo $paym['id'] == $pidpayment ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $paym['name']); ?></option>
					<?php
				}
				?>
				</select>
			</div>
			<div class="btn-group pull-left">
				<select name="status">
					<option value=""><?php echo JText::_('VRFILTERBYSTATUS'); ?></option>
				<?php
				$pstatus = $app->getUserStateFromRequest("vrc.orders.status", 'status', '', 'string');
				$filters_set = !empty($pstatus) || $filters_set;
				$status_filter = !empty($pstatus) ? '&amp;status='.$pstatus : '';
				?>
					<option value="confirmed"<?php echo $pstatus == 'confirmed' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIRMED'); ?></option>
					<option value="standby"<?php echo $pstatus == 'standby' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRSTANDBY'); ?></option>
					<option value="cancelled"<?php echo $pstatus == 'cancelled' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCANCELLED'); ?></option>
					<option value="stop_sales"<?php echo $pstatus == 'stop_sales' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSTOPRENTALSTATUS'); ?></option>
				</select>
			</div>
			<?php
			if (is_array($all_locations)) {
				$filters_set = !empty($plocation) || $filters_set;
				?>
			<div class="btn-group pull-left">
				<select name="location" id="locfilter" onchange="vrcUpdateLocFilter(this);"><?php echo $loc_options; ?></select>
				<input type="hidden" name="locationw" id="locwfilter" value="<?php echo empty($plocationw) ? 'pickup' : JHtml::_('esc_attr', $plocationw); ?>" />
			</div>
				<?php
			}

			// filter by calendar
			$calendars = VRCCalendarIcal::getAllCalendarsUsed();
			$pcalendar = $app->getUserStateFromRequest("vrc.orders.calendar", 'calendar', 0, 'int');
			if (!empty($calendars)) {
				?>
			<div class="btn-group pull-left">
				<select name="calendar">
					<option value=""><?php echo JText::_('VRC_FILTER_BY_CALENDAR'); ?></option>
				<?php
				foreach ($calendars as $calendar) {
					?>
					<option value="<?php echo $calendar['id']; ?>"<?php echo $pcalendar == $calendar['id'] ? ' selected="selected"' : ''; ?>><?php echo $calendar['name']; ?></option>
					<?php
				}
				?>
				</select>
			</div>
				<?php
			}
			?>
			<div class="btn-group pull-left">
			<?php
			$dates_filter = '';
			$pdatefilt = $app->getUserStateFromRequest("vrc.orders.datefilt", 'datefilt', 0, 'int');
			$pdatefiltfrom = $app->getUserStateFromRequest("vrc.orders.datefiltfrom", 'datefiltfrom', '', 'string');
			$pdatefiltto = $app->getUserStateFromRequest("vrc.orders.datefiltto", 'datefiltto', '', 'string');
			if (!empty($pdatefilt) && (!empty($pdatefiltfrom) || !empty($pdatefiltto))) {
				$filters_set = true;
				$dates_filter = '&amp;datefilt='.$pdatefilt.(!empty($pdatefiltfrom) ? '&amp;datefiltfrom='.$pdatefiltfrom : '').(!empty($pdatefiltto) ? '&amp;datefiltto='.$pdatefiltto : '');
			}
			$datesel = '<select name="datefilt" onchange="vrcToggleDateFilt(this.value);"><option value="">'.JText::_('VRFILTERBYDATES').'</option>';
			$datesel .= '<option value="1"'.(!empty($pdatefilt) && $pdatefilt == 1 ? ' selected="selected"' : '').'>'.JText::_('VRPCHOOSEBUSYORDATE').'</option>';
			$datesel .= '<option value="2"'.(!empty($pdatefilt) && $pdatefilt == 2 ? ' selected="selected"' : '').'>'.JText::_('VRCEXPCSVPICK').'</option>';
			$datesel .= '<option value="3"'.(!empty($pdatefilt) && $pdatefilt == 3 ? ' selected="selected"' : '').'>'.JText::_('VRCEXPCSVDROP').'</option>';
			$datesel .= '</select>';
			echo $datesel;
			?>
			</div>
			<div class="btn-group pull-left" id="vrc-dates-cont" style="display: <?php echo (!empty($pdatefilt) && (!empty($pdatefiltfrom) || !empty($pdatefiltto)) ? 'inline-block' : 'none'); ?>;">
				<input type="text" id="vrc-date-from" placeholder="<?php echo JHtml::_('esc_attr', JText::_('VRNEWSEASONONE')); ?>" value="<?php echo JHtml::_('esc_attr', $pdatefiltfrom); ?>" size="10" name="datefiltfrom" />&nbsp;-&nbsp;<input type="text" id="vrc-date-to" placeholder="<?php echo JHtml::_('esc_attr', JText::_('VRNEWSEASONTWO')); ?>" value="<?php echo JHtml::_('esc_attr', $pdatefiltto); ?>" size="10" name="datefiltto" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn"><i class="icon-search"></i> <?php echo JText::_('VRPVIEWORDERSSEARCHSUBM'); ?></button>
			</div>
		</div>
	</div>

	<div class="table-responsive">
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vrc-orderslist-table">
			<thead>
				<tr>
					<th width="20">
						<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
					</th>
					<th class="title center" width="20" align="center">
						<a href="index.php?option=com_vikrentcar&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vrcorderby=id&amp;vrcordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vrc-orderslist-activesort" : ($orderby == "id" ? "vrc-orderslist-activesort" : "")); ?>">
							<?php echo 'ID'.($orderby == "id" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "id" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
						</a>
					</th>
					<th class="title left" width="110">
						<a href="index.php?option=com_vikrentcar&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vrcorderby=ts&amp;vrcordersort=<?php echo ($orderby == "ts" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "ts" && $ordersort == "ASC" ? "vrc-orderslist-activesort" : ($orderby == "ts" ? "vrc-orderslist-activesort" : "")); ?>">
							<?php echo JText::_('VRPVIEWORDERSONE').($orderby == "ts" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "ts" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
						</a>
					</th>
					<th class="title left" width="200"><?php echo JText::_( 'VRPVIEWORDERSTWO' ); ?></th>
					<th class="title left" width="150">
						<a href="index.php?option=com_vikrentcar&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vrcorderby=carname&amp;vrcordersort=<?php echo ($orderby == "carname" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "carname" && $ordersort == "ASC" ? "vrc-orderslist-activesort" : ($orderby == "carname" ? "vrc-orderslist-activesort" : "")); ?>">
							<?php echo JText::_('VRPVIEWORDERSTHREE').($orderby == "carname" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "carname" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
						</a>
					</th>
					<th class="title left" width="110">
						<a href="index.php?option=com_vikrentcar&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vrcorderby=pickupts&amp;vrcordersort=<?php echo ($orderby == "pickupts" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "pickupts" && $ordersort == "ASC" ? "vrc-orderslist-activesort" : ($orderby == "pickupts" ? "vrc-orderslist-activesort" : "")); ?>">
							<?php echo JText::_('VRPVIEWORDERSFOUR').($orderby == "pickupts" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "pickupts" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
						</a>
					</th>
					<th class="title left" width="110">
						<a href="index.php?option=com_vikrentcar&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vrcorderby=dropoffts&amp;vrcordersort=<?php echo ($orderby == "dropoffts" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "dropoffts" && $ordersort == "ASC" ? "vrc-orderslist-activesort" : ($orderby == "dropoffts" ? "vrc-orderslist-activesort" : "")); ?>">
							<?php echo JText::_('VRPVIEWORDERSFIVE').($orderby == "dropoffts" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "dropoffts" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
						</a>
					</th>
					<th class="title center" width="70" align="center">
						<a href="index.php?option=com_vikrentcar&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vrcorderby=days&amp;vrcordersort=<?php echo ($orderby == "days" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "days" && $ordersort == "ASC" ? "vrc-orderslist-activesort" : ($orderby == "days" ? "vrc-orderslist-activesort" : "")); ?>">
							<?php echo JText::_('VRPVIEWORDERSSIX').($orderby == "days" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "days" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
						</a>
					</th>
					<th class="title center" width="110" align="center">
						<a href="index.php?option=com_vikrentcar&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vrcorderby=total&amp;vrcordersort=<?php echo ($orderby == "total" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "total" && $ordersort == "ASC" ? "vrc-orderslist-activesort" : ($orderby == "total" ? "vrc-orderslist-activesort" : "")); ?>">
							<?php echo JText::_('VRPVIEWORDERSSEVEN').($orderby == "total" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "total" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
						</a>
					</th>
					<th class="title center" width="30"> </th>
					<th class="title center" width="100" align="center">
						<a href="index.php?option=com_vikrentcar&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vrcorderby=status&amp;vrcordersort=<?php echo ($orderby == "status" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "status" && $ordersort == "ASC" ? "vrc-orderslist-activesort" : ($orderby == "status" ? "vrc-orderslist-activesort" : "")); ?>">
							<?php echo JText::_('VRPVIEWORDERSEIGHT').($orderby == "status" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "status" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
						</a>
					</th>
				</tr>
			</thead>
		<?php
		$monsmap = array(
			JText::_('VRSHORTMONTHONE'),
			JText::_('VRSHORTMONTHTWO'),
			JText::_('VRSHORTMONTHTHREE'),
			JText::_('VRSHORTMONTHFOUR'),
			JText::_('VRSHORTMONTHFIVE'),
			JText::_('VRSHORTMONTHSIX'),
			JText::_('VRSHORTMONTHSEVEN'),
			JText::_('VRSHORTMONTHEIGHT'),
			JText::_('VRSHORTMONTHNINE'),
			JText::_('VRSHORTMONTHTEN'),
			JText::_('VRSHORTMONTHELEVEN'),
			JText::_('VRSHORTMONTHTWELVE')
		);
		$kk = 0;
		$i = 0;
		for ($i = 0, $n = count($rows); $i < $n; $i++) {
			$row = $rows[$i];
			$car = VikRentCar::getCarInfo($row['idcar']);
			$is_cust_cost = (!empty($row['cust_cost']) && $row['cust_cost'] > 0);
			$isdue = 0;

			$price = [
				[
					'id' => -1,
					'idcar' => $row['idcar'],
					'days' => $row['days'],
					'idprice' => -1,
					'cost' => 0,
					'attrdata' => '',
				]
			];

			if (!empty($row['idtar'])) {
				if ($row['hourly'] == 1) {
					$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='".$row['idtar']."';";
				} else {
					$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$row['idtar']."';";
				}
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					//there are no hourly prices
					if ($row['hourly'] == 1) {
						$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$row['idtar']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$price = $dbo->loadAssocList();
						}
					}
					//
				} else {
					$price = $dbo->loadAssocList();
				}
			} elseif ($is_cust_cost) {
				//Custom Rate
				$price = [
					[
						'id' => -1,
						'idcar' => $row['idcar'],
						'days' => $row['days'],
						'idprice' => -1,
						'cost' => $row['cust_cost'],
						'attrdata' => '',
					]
				];
			}
			if ($row['hourly'] == 1) {
				foreach ($price as $kt => $vt) {
					$price[$kt]['days'] = 1;
				}
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
			if ($checkhourscharges > 0 && $aehourschbasp == true && !$is_cust_cost) {
				$ret = VikRentCar::applyExtraHoursChargesCar($price, $row['idcar'], $checkhourscharges, $daysdiff, false, true, true);
				$price = $ret['return'];
				$calcdays = $ret['days'];
			}
			if ($checkhourscharges > 0 && $aehourschbasp == false && !$is_cust_cost) {
				$price = VikRentCar::extraHoursSetPreviousFareCar($price, $row['idcar'], $checkhourscharges, $daysdiff, true);
				$price = VikRentCar::applySeasonsCar($price, $row['ritiro'], $row['consegna'], $row['idplace']);
				$ret = VikRentCar::applyExtraHoursChargesCar($price, $row['idcar'], $checkhourscharges, $daysdiff, true, true, true);
				$price = $ret['return'];
				$calcdays = $ret['days'];
			} else {
				if (!$is_cust_cost) {
					//Seasonal prices only if not a custom rate
					if (isset($price)) {
						$price = VikRentCar::applySeasonsCar($price, $row['ritiro'], $row['consegna'], $row['idplace']);
					} else {
						// probably a closure (stop sales)
						$price = array(0 => array(
							'id' => -1,
							'idcar' => $row['idcar'],
							'days' => $row['days'],
							'idprice' => -1,
							'cost' => 0,
							'attrdata' => '',
						));
					}
				}
			}
			//
			$isdue += $is_cust_cost ? $price[0]['cost'] : VikRentCar::sayCostPlusIva($price[0]['cost'], $price[0]['idprice'], $row);
			if (!empty($row['optionals'])) {
				$stepo = explode(";", $row['optionals']);
				foreach ($stepo as $oo) {
					if (!empty($oo)) {
						$stept = explode(":", $oo);
						$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id`='".$stept[0]."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$popts = $dbo->loadAssocList();
							$realcost = intval($popts[0]['perday']) == 1 ? ($popts[0]['cost'] * $row['days'] * $stept[1]) : ($popts[0]['cost'] * $stept[1]);
							$basequancost = intval($popts[0]['perday']) == 1 ? ($popts[0]['cost'] * $row['days']) : $popts[0]['cost'];
							if ($popts[0]['maxprice'] > 0 && $basequancost > $popts[0]['maxprice']) {
								$realcost = $popts[0]['maxprice'];
								if (intval($popts[0]['hmany']) == 1 && intval($stept[1]) > 1) {
									$realcost = $popts[0]['maxprice'] * $stept[1];
								}
							}
							$isdue += VikRentCar::sayOptionalsPlusIva($realcost, $popts[0]['idiva'], $row);
						}
					}
				}
			}
			//custom extra costs
			if (!empty($row['extracosts'])) {
				$cur_extra_costs = json_decode($row['extracosts'], true);
				foreach ($cur_extra_costs as $eck => $ecv) {
					$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $row);
					$isdue += $efee_cost;
				}
			}
			//
			if (!empty($row['idplace']) && !empty($row['idreturnplace'])) {
				$locfee = VikRentCar::getLocFee($row['idplace'], $row['idreturnplace']);
				if ($locfee) {
					//VikRentCar 1.7 - Location fees overrides
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
					//end VikRentCar 1.7 - Location fees overrides
					$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $row['days']) : $locfee['cost'];
					$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $row);
					$isdue += $locfeewith;
				}
			}
			//VRC 1.9 - Out of Hours Fees
			$oohfee = VikRentCar::getOutOfHoursFees($row['idplace'], $row['idreturnplace'], $row['ritiro'], $row['consegna'], array('id' => $row['idcar']));
			if (count($oohfee) > 0) {
				$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
				$isdue += $oohfeewith;
			}
			//
			//vikrentcar 1.6 coupon
			$usedcoupon = false;
			$origisdue = $isdue;
			if (strlen($row['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $row['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			//
			//Customer Details
			$custdata = $row['custdata'];
			$custdata_parts = explode("\n", $row['custdata']);
			if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
				//get the first two fields
				$custvalues = array();
				foreach ($custdata_parts as $custdet) {
					if (strlen($custdet) < 1) {
						continue;
					}
					$custdet_parts = explode(':', $custdet);
					if (count($custdet_parts) >= 2) {
						unset($custdet_parts[0]);
						array_push($custvalues, trim(implode(':', $custdet_parts)));
					}
					if (count($custvalues) > 1) {
						break;
					}
				}
				if (count($custvalues) > 1) {
					$custdata = implode(' ', $custvalues);
				}
			}
			if (strlen($custdata) > 45) {
				$custdata = substr($custdata, 0, 45)." ...";
			}
			$q = "SELECT `c`.*,`co`.`idorder` FROM `#__vikrentcar_customers` AS `c` LEFT JOIN `#__vikrentcar_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$cust_country = $dbo->loadAssocList();
				$cust_country = $cust_country[0];
				if (!empty($cust_country['first_name'])) {
					$custdata = $cust_country['first_name'].' '.$cust_country['last_name'];
					if (!empty($cust_country['country'])) {
						if (file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$row['country'].'.png')) {
							$custdata .= '<img src="'.VRC_ADMIN_URI.'resources/countries/'.$row['country'].'.png'.'" title="'.$row['country'].'" class="vrc-country-flag vrc-country-flag-left"/>';
						}
					}
				}
			} elseif (!empty($row['nominative'])) {
				$custdata = $row['nominative'];
				if (!empty($row['country'])) {
					if (file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$row['country'].'.png')) {
						$custdata .= '<img src="'.VRC_ADMIN_URI.'resources/countries/'.$row['country'].'.png'.'" title="'.$row['country'].'" class="vrc-country-flag vrc-country-flag-left"/>';
					}
				}
			}
			//
			$status_lbl = '';
			if ($row['status'] == 'confirmed') {
				$status_lbl = "<span class=\"label label-success vrc-status-label\">".JText::_('VRCONFIRMED')."</span>";
			} elseif ($row['status'] == 'standby') {
				$status_lbl = "<span class=\"label label-warning vrc-status-label\">".JText::_('VRSTANDBY')."</span>";
			} elseif ($row['status'] == 'cancelled') {
				$status_lbl = "<span class=\"label label-error vrc-status-label\" style=\"background-color: #d9534f;\">".JText::_('VRCANCELLED')."</span>";
			}
			$invoice_icon = '';
			if (file_exists(VRC_SITE_PATH . DS . "helpers" . DS . "invoices" . DS . "generated" . DS . $row['id'].'_'.$row['sid'].'.pdf')) {
				$invoice_icon = '<a class="hasTooltip" title="'.JText::_('VRCDOWNLOADPDFINVOICE').'" href="'.VRC_SITE_URI.'helpers/invoices/generated/'.$row['id'].'_'.$row['sid'].'.pdf" target="_blank"><i class="vrcicn-file-text" style="margin: 0;"></i></a>';
				if (!empty($row['adminnotes'])) {
					$invoice_icon .= ' &nbsp; ';
				}
			}
			$ts_info = getdate($row['ts']);
			$ts_wday = JText::_('VR'.strtoupper(substr($ts_info['weekday'], 0, 3)));
			$ritiro_info = getdate($row['ritiro']);
			$ritiro_wday = JText::_('VR'.strtoupper(substr($ritiro_info['weekday'], 0, 3)));
			$consegna_info = getdate($row['consegna']);
			$consegna_wday = JText::_('VR'.strtoupper(substr($consegna_info['weekday'], 0, 3)));
			?>
			<tr class="row<?php echo $kk; ?>">
				<td class="skip">
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row['id']; ?>" onclick="Joomla.isChecked(this.checked);">
				</td>
				<td class="center">
					<a class="vrc-orderid" href="index.php?option=com_vikrentcar&amp;task=editorder&amp;cid[]=<?php echo $row['id']; ?>"><?php echo (int)$row['id']; ?></a>
				</td>
				<td>
					<a class="vrc-orderslist-viewdet-link" href="index.php?option=com_vikrentcar&amp;task=editorder&amp;cid[]=<?php echo (int)$row['id']; ?>">
						<div class="vrc-orderslist-viewdet">
							<div class="vrc-orderslist-viewdet-open">
								<i class="fas fa-external-link-alt"></i>
							</div>
							<div class="vrc-orderslist-viewdet-fulldate">
								<div class="vrc-orderslist-viewdet-date">
								<?php
								if (strpos($df, 'd') < strpos($df, 'm')) {
									//assuming d/m/Y or similar
									?>
									<span><?php echo $ts_info['mday']; ?></span>
									<span><?php echo $monsmap[($ts_info['mon'] - 1)]; ?></span>
									<?php
								} else {
									//assuming m/d/Y or similar
									?>
									<span><?php echo $monsmap[($ts_info['mon'] - 1)]; ?></span>
									<span><?php echo $ts_info['mday']; ?></span>
									<?php
								}
								?>
									<span><?php echo $ts_info['year']; ?></span>
								</div>
								<div class="vrc-orderslist-viewdet-time">
									<span class="vrc-orderslist-viewdet-wday"><?php echo $ts_wday; ?></span>
									<span class="vrc-orderslist-viewdet-hour"><?php echo date($nowtf, $row['ts']); ?></span>
								</div>
							</div>
						</div>
					</a>
				</td>
				<td>
				<?php
				if ($row['stop_sales'] == 1) {
					?>
					<span class="vrc-order-stop-sales" title="<?php echo $this->escape(JText::_('VRCSTOPRENTALS')); ?>"><?php VikRentCarIcons::e('ban'); ?> <?php echo $custdata; ?></span>
					<?php
				} else {
					echo $custdata;
				}
				?>
				</td>
				<td>
					<div class="vrc-orderslist-cardetails">
						<div class="vrc-orderslist-cardetails-inner">
							<div class="vrc-orderslist-cardetails-carname">
								<span><?php echo $car['name']; ?></span>
							</div>
						<?php
						if (!empty($row['idorder_ical'])) {
							$cal_name = !empty($row['ical_name']) ? $row['ical_name'] : ('#' . $row['id_ical']);
							?>
							<div class="vrc-orderslist-cardetails-icalname">
								<?php VikRentCarIcons::e('calendar'); ?> <span><?php echo $cal_name; ?></span>
							</div>
							<?php
						}
						?>
						</div>
					</div>
				</td>
				<td>
					<div class="vrc-orderslist-booktime vrc-orderslist-booktime-pickup">
						<div class="vrc-orderslist-booktime-fulldate">
							<div class="vrc-orderslist-booktime-date">
								<span><?php echo date($df, $row['ritiro']); ?></span>
							</div>
							<div class="vrc-orderslist-booktime-time">
								<span class="vrc-orderslist-booktime-twrap">
									<span class="vrc-orderslist-booktime-wday"><?php echo $ritiro_wday; ?></span>
									<span class="vrc-orderslist-booktime-hour"><?php echo date($nowtf, $row['ritiro']); ?></span>
								</span>
							</div>
						</div>
					</div>
				</td>
				<td>
					<div class="vrc-orderslist-booktime vrc-orderslist-booktime-pickup">
						<div class="vrc-orderslist-booktime-fulldate">
							<div class="vrc-orderslist-booktime-date">
								<span><?php echo date($df, $row['consegna']); ?></span>
							</div>
							<div class="vrc-orderslist-booktime-time">
								<span class="vrc-orderslist-booktime-twrap">
									<span class="vrc-orderslist-booktime-wday"><?php echo $consegna_wday; ?></span>
									<span class="vrc-orderslist-booktime-hour"><?php echo date($nowtf, $row['consegna']); ?></span>
								</span>
							</div>
						</div>
					</div>
				</td>
				<td class="center">
					<?php echo ($row['hourly'] == 1 && !empty($price[0]['hours']) ? $price[0]['hours'].' '.JText::_('VRCHOURS') : $row['days']); ?>
				</td>
				<td class="center">
					<div class="vrc-orderslist-total-wrap">
						<div class="vrc-orderslist-total-amount">
							<span><?php echo $currencysymb; ?></span>
							<span<?php echo $isdue > $row['order_total'] || $isdue < $row['order_total'] ? ' title="'.addslashes(JText::sprintf('VRCTOTALWOULDBE', VikRentCar::numberFormat($isdue))).'" class="hasTooltip"' : ''; ?>><?php echo VikRentCar::numberFormat($row['order_total']); ?></span>
						</div>
					<?php
					if (!empty($row['totpaid'])) {
						?>
						<div class="vrc-orderslist-total-totpaid">
							<span><?php echo $currencysymb; ?></span>
							<span><?php echo VikRentCar::numberFormat($row['totpaid']); ?></span>
						</div>
						<?php
					}
					?>
					</div>
				</td>
				<td class="center">
					<?php echo $invoice_icon.(!empty($row['adminnotes']) ? '<span class="hasTooltip vrc-admin-tipsicon" title="'.htmlentities(nl2br($row['adminnotes'])).'"><i class="' . VikRentCarIcons::i('comment-dots') . '"></i></span>' : ''); ?>
				</td>
				<td class="center">
					<?php
					// status label
					echo $status_lbl;
					
					/**
					 * Client rental order status registration (status must be confirmed).
					 * We allow to update the status from one day before the pick up date
					 * till one day after the drop off date, or if pick up date and time
					 * is in the past, but the current registration is no-show or started.
					 * 
					 * @since 	1.14.5 (J) - 1.2.0 (WP)
					 */
					$earliest_pickup = strtotime("-1 day", $row['ritiro']);
					$furthest_return = strtotime("+1 day", $row['consegna']);
					$row['reg'] = (int)$row['reg'];
					$valid_statuses = array('confirmed', 'cancelled');
					$valid_regcodes = array(-1, 1, 2);
					if (in_array($row['status'], $valid_statuses) && (($earliest_pickup <= time() && $furthest_return >= time()) || $earliest_pickup > time()) && in_array($row['reg'], $valid_regcodes)) {
						// check current situations
						$reg_status = JText::_('VRC_ORDER_REGISTRATION_NONE');
						$reg_class  = 'vrc-order-regstatus-bubble-none';
						if ($row['reg'] < 0) {
							// no show
							$reg_status = JText::_('VRC_ORDER_REGISTRATION_NOSHOW');
							$reg_class  = 'vrc-order-regstatus-bubble-danger';
						} elseif ($row['reg'] === 1) {
							// started
							$reg_status = JText::_('VRC_ORDER_REGISTRATION_STARTED');
							$reg_class  = 'vrc-order-regstatus-bubble-ongoing';
						} elseif ($row['reg'] === 2) {
							// terminated
							$reg_status = JText::_('VRC_ORDER_REGISTRATION_TERMINATED');
							$reg_class  = 'vrc-order-regstatus-bubble-terminated';
						}
						?>
						<span class="hasTooltip vrc-order-regstatus-bubble <?php echo $reg_class; ?>" title="<?php echo htmlentities($reg_status); ?>"><?php VikRentCarIcons::e('dot-circle'); ?></span>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
			$kk = 1 - $kk;
		}
		?>
		</table>
	</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="cust_id" id="cust_id" value="<?php echo !empty($pcust_id) ? JHtml::_('esc_attr', $pcust_id) : ''; ?>" />
	<input type="hidden" name="task" value="orders" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<script type="text/javascript">
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
} else {
	jQuery.fn.tooltip = function(){};
}
function vrcToggleDateFilt(dtype) {
	if (!(dtype.length > 0)) {
		document.getElementById('vrc-dates-cont').style.display = 'none';
		document.getElementById('vrc-date-from').value = '';
		document.getElementById('vrc-date-to').value = '';
		return true;
	}
	document.getElementById('vrc-dates-cont').style.display = 'inline-block';
	return true;
}
function vrcUpdateLocFilter(elem) {
	var locw = jQuery(elem).find('option:selected').attr('data-locw');
	jQuery('#locwfilter').val(locw);
}
jQuery(document).ready(function() {
	jQuery('.vrc-orderslist-viewdet-link').click(function(e) {
		if (e && e.target.tagName.toUpperCase() == 'I') {
			//open the link in a new window
			e.preventDefault();
			window.open(jQuery(this).attr('href'), '_blank');
		}
	});
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '' ] );
	jQuery('#vrc-date-from').datepicker({
		showOn: 'focus',
		dateFormat: '<?php echo $juidf; ?>',
		onSelect: function( selectedDate ) {
			jQuery('#vrc-date-to').datepicker('option', 'minDate', selectedDate);
		}
	});
	jQuery('#vrc-date-to').datepicker({
		showOn: 'focus',
		dateFormat: '<?php echo $juidf; ?>',
		onSelect: function( selectedDate ) {
			jQuery('#vrc-date-from').datepicker('option', 'maxDate', selectedDate);
		}
	});
	//Search customer - Start
	var vrccustsdelay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();
	function vrcCustomerSearch(words) {
		jQuery("#vrc-allbsearchcust-res").hide().html("");
		jQuery("#customernominative").addClass('vrc-allbsearchcust-loading-inp');
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikrentcar", task: "searchcustomer", kw: words, nopin: 1, tmpl: "component" }
		}).done(function(cont) {
			if (cont.length) {
				var obj_res = JSON.parse(cont);
				jQuery("#vrc-allbsearchcust-res").html(obj_res[1]);
			} else {
				jQuery("#vrc-allbsearchcust-res").html("");
			}
			jQuery("#vrc-allbsearchcust-res").show();
			jQuery("#customernominative").removeClass('vrc-allbsearchcust-loading-inp');
		}).fail(function() {
			jQuery("#customernominative").removeClass('vrc-allbsearchcust-loading-inp');
			alert("Error Searching.");
		});
	}
	jQuery("#customernominative").keyup(function(event) {
		vrccustsdelay(function() {
			var keywords = jQuery("#customernominative").val();
			if (keywords.length > 1) {
				if ((event.which > 96 && event.which < 123) || (event.which > 64 && event.which < 91) || event.which == 13) {
					vrcCustomerSearch(keywords);
				}
			} else {
				if (jQuery("#vrc-allbsearchcust-res").is(":visible")) {
					jQuery("#vrc-allbsearchcust-res").hide();
				}
			}
		}, 600);
	});
	jQuery(document).on('click', '.vrc-custsearchres-entry', function() {
		var customer_id = jQuery(this).attr('data-custid');
		if (customer_id.length) {
			document.location.href = 'index.php?option=com_vikrentcar&task=orders&cust_id='+customer_id;
		}
	});
	//Search customer - End
	jQuery(".vrc-orderslist-table tr td").not(".skip").click(function() {
		//the checkbox for the booking is on the first TD of the row
		var trcbox = jQuery(this).parent("tr").find("td").first().find("input[type='checkbox']");
		if (!trcbox || !trcbox.length) {
			return;
		}
		trcbox.prop('checked', !(trcbox.prop('checked')));
		if (typeof Joomla !== 'undefined' && Joomla != null) {
			Joomla.isChecked(trcbox.prop('checked'));
		}
	});
	jQuery(".vrc-orderslist-table tr").dblclick(function() {
		if (document.selection && document.selection.empty) {
			document.selection.empty();
		} else if (window.getSelection) {
			var sel = window.getSelection();
			sel.removeAllRanges();
		}
		//the link to the booking details page is on the third TD of the row
		var olink = jQuery(this).find("td").first().next().next().find("a");
		if (!olink || !olink.length) {
			return;
		}
		document.location.href = olink.attr("href");
	});
	<?php
	if ($filters_set) {
		?>
	jQuery("#vrc-search-tools-btn").trigger("click");
		<?php
	}
	?>
});
</script>
<?php
//VRC 1.9 invoices
if (count($cid) > 0 && !empty($cid[0])) {
	$nextinvnum = VikRentCar::getNextInvoiceNumber();
	$invsuff = VikRentCar::getInvoiceNumberSuffix();
	$companyinfo = VikRentCar::getInvoiceCompanyInfo();
	?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.vrc-gen-invoice-close').click(function() {
		jQuery('.vrc-gen-invoice-block').hide();
	});
});
</script>
<form action="index.php?option=com_vikrentcar" method="post">
	<div class="vrc-gen-invoice-block">
		<div class="vrc-gen-invoice-close"></div>
		<div class="vrc-gen-invoice-cont">
			<div class="vrc-gen-invoice-finalentry">
				<strong><?php echo JText::sprintf('VRCINVGENERATING', count($cid)); ?></strong>
			</div>
			<br clear="all"/>
			<div class="vrc-gen-invoice-entry">
				<label for="invoice_num"><?php echo JText::_('VRCINVSTARTNUM'); ?></label>
				<span><input type="number" name="invoice_num" id="invoice_num" value="<?php echo JHtml::_('esc_attr', $nextinvnum); ?>" size="4" min="1"/></span>
			</div>
			<div class="vrc-gen-invoice-entry">
				<label for="invoice_suff"><?php echo JText::_('VRCINVNUMSUFF'); ?></label>
				<span><input type="text" name="invoice_suff" id="invoice_suff" value="<?php echo JHtml::_('esc_attr', $invsuff); ?>" size="4"/></span>
			</div>
			<div class="vrc-gen-invoice-entry">
				<label for="invoice_date"><?php echo JText::_('VRCINVDATE'); ?></label>
				<span><select name="invoice_date" id="invoice_date"><option value="<?php echo date($df); ?>"><?php echo date($df); ?></option><option value="0"><?php echo JHtml::_('esc_html', JText::_('VRCINVDATERES')); ?></option></select></span>
			</div>
			<div class="vrc-gen-invoice-entry">
				<label for="company_info"><?php echo JText::_('VRCINVCOMPANYINFO'); ?></label>
				<span><textarea name="company_info" id="company_info" rows="3" cols="50"><?php echo JHtml::_('esc_textarea', $companyinfo); ?></textarea></span>
			</div>
			<div class="vrc-gen-invoice-entry">
				<label for="invoice_send"><?php echo JText::_('VRCINVSENDVIAEMAIL'); ?></label>
				<span><input type="checkbox" name="invoice_send" id="invoice_send" value="1"/></span>
			</div>
			<br clear="all"/>
			<div class="vrc-gen-invoice-finalentry">
				<span>
					<button type="submit" class="btn btn-secondary"><?php echo JText::_('VRCGENINVOICE'); ?></button>
				</span>
			</div>
		</div>
	</div>
	<?php
	foreach ($cid as $invid) {
		echo '<input type="hidden" name="cid[]" value="' . JHtml::_('esc_attr', $invid) . '" />';
	}
	?>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="geninvoices" />
</form>
<?php
}
