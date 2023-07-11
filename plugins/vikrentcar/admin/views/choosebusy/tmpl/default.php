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
$totres = $this->totres;
$pts = $this->pts;

$nowdf = VikRentCar::getDateFormat(true);
$nowtf = VikRentCar::getTimeFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}

$dbo = JFactory::getDbo();

if (is_file(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $rows[0]['img']) && getimagesize(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $rows[0]['img'])) {
	$img = '<img align="middle" class="maxninety" alt="Car Image" src="' . VRC_ADMIN_URI . 'resources/'.$rows[0]['img'].'" />';
} else {
	$img = '<i class="' . VikRentCarIcons::i('image', 'vrc-enormous-icn') . '"></i>';
}
$unitsdisp = $rows[0]['units'] - $totres;
$unitsdisp = ($unitsdisp < 0 ? "0" : $unitsdisp);
$pgoto = VikRequest::getString('goto', '', 'request');
?>

<div class="vrc-choosebusy-head">
	<div class="vrc-choosebusy-head-top">
		<div class="vrc-choosebusy-head-top-inner">
			<h3><?php echo JText::_('VRMAINCHOOSEBUSY'); ?> <?php echo $rows[0]['name']; ?></h3>
		</div>
		<div class="vrc-choosebusy-head-top-img">
			<?php echo $img; ?>
		</div>
	</div>
	<div class="vrc-choosebusy-head-bottom">
		<div class="vrc-choosebusy-unitsav">
			<span class="vrc-choosebusy-unitsav-txt"><?php echo JText::_('VRPCHOOSEBUSYCAVAIL'); ?>:</span>
			<span class="badge"><?php echo $unitsdisp; ?>/<?php echo $rows[0]['units']; ?></span>
		</div>
	</div>
</div>

<?php
/**
 * Hourly availability calendar for the current day.
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
$picksondrops = VikRentCar::allowPickOnDrop();
?>
<div class="vrc-hourlycal-container">
	<h4 class="vrc-medium-header"><?php echo JText::sprintf('VRC_HOURLY_AV_DAY', date($df, $pts)); ?></h4>
	<div class="table-responsive">
		<table class="vrc-hourly-cal table">
			<tr>
				<td style="text-align: left;"><?php VikRentCarIcons::e('clock'); ?></td>
<?php
for ($h = 0; $h <= 23; $h++) {
	if ($nowtf == 'H:i') {
		$sayh = $h < 10 ? "0".$h : $h;
	} else {
		$ampm = $h < 12 ? ' am' : ' pm';
		$ampmh = $h > 12 ? ($h - 12) : $h;
		$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
	}
	?>
				<td style="text-align: center;"><?php echo $sayh; ?></td>
	<?php
}
?>
			</tr>
			<tr class="vrc-hourlycal-rowavail">
				<td style="text-align: left;"> </td>
<?php
for ($h = 0; $h <= 23; $h++) {
	$checkhourts = ($pts + ($h * 3600));
	$dclass = "vrctdfree";
	$totfound = 0;
	foreach ($rows as $b) {
		$tmpone = getdate($b['ritiro']);
		$ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
		$tmptwo = getdate($b['realback']);
		$conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
		if ($pts >= $ritts && $pts <= $conts) {
			if ($b['stop_sales'] == 1) {
				$totfound = $rows[0]['units'];
				break;
			}
			if ($checkhourts >= $b['ritiro'] && $checkhourts <= $b['realback']) {
				if ($picksondrops && !($checkhourts > $b['ritiro'] && $checkhourts < $b['realback']) && $checkhourts == $b['realback']) {
					// pick ups on drop offs allowed
					continue;
				}
				$totfound++;
			}
		}
	}
	if ($totfound >= $rows[0]['units']) {
		$dclass = "vrctdbusy";
	} elseif ($totfound > 0) {
		$dclass = "vrctdwarning";
	}
	$hourlydisp = $rows[0]['units'] - $totfound;
	?>
				<td style="text-align: center;" class="<?php echo $dclass; ?>"><?php echo $hourlydisp; ?></td>
	<?php
}
?>
			</tr>
		</table>
	</div>
</div>

<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm" class="vrc-list-form">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vrc-list-table">
		<thead>
		<tr>
			<th class="title left" width="50"><?php echo JText::_( 'VRCORDERNUMBER' ); ?></th>
			<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWORDERSFOUR' ); ?></th>
			<th class="title left" width="250"><?php echo JText::_( 'VRPVIEWORDERSTWO' ); ?></th>
			<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWORDERSFIVE' ); ?></th>
			<th class="title center" width="150"><?php echo JText::_( 'VRCUNITASSIGNED' ); ?></th>
			<th class="title left" width="150"><?php echo JText::_( 'VRPCHOOSEBUSYORDATE' ); ?></th>
		</tr>
		</thead>
	<?php
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		//Car Specific Unit
		$car_first_feature = '';
		if (!empty($row['carindex']) && !empty($row['params'])) {
			$car_params = json_decode($row['params'], true);
			if (is_array($car_params) && count($car_params['features'])) {
				foreach ($car_params['features'] as $cind => $cfeatures) {
					if ($cind != $row['carindex']) {
						continue;
					}
					foreach ($cfeatures as $fname => $fval) {
						if (strlen($fval)) {
							$car_first_feature = '#'.$cind.' - '.JText::_($fname).': '.$fval;
							break 2;
						}
					}
				}
			}
		}
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
		$q = "SELECT `c`.*,`co`.`idorder` FROM `#__vikrentcar_customers` AS `c` LEFT JOIN `#__vikrentcar_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=" . (int)$row['idorder'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cust_country = $dbo->loadAssoc();
			if (!empty($cust_country['first_name'])) {
				$custdata = $cust_country['first_name'].' '.$cust_country['last_name'];
				if (!empty($cust_country['country'])) {
					if (file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$cust_country['country'].'.png')) {
						$custdata .= '<img src="'.VRC_ADMIN_URI.'resources/countries/'.$cust_country['country'].'.png'.'" title="'.$cust_country['country'].'" class="vrc-country-flag vrc-country-flag-left"/>';
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
		?>
		<tr class="row<?php echo $k; ?>">
			<td class="left"><a class="vrc-orderid" href="index.php?option=com_vikrentcar&amp;task=editbusy<?php echo (!empty($pgoto) ? '&amp;goto='.$pgoto : ''); ?>&amp;cid[]=<?php echo $row['idorder']; ?>"><?php echo $row['idorder']; ?></a></td>
			<td><a href="index.php?option=com_vikrentcar&amp;task=editbusy<?php echo (!empty($pgoto) ? '&amp;goto='.$pgoto : ''); ?>&amp;cid[]=<?php echo $row['idorder']; ?>"><?php echo date($df.' '.$nowtf, $row['ritiro']); ?></a></td>
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
			<td><span<?php echo $row['consegna'] != $row['realback'] ? ' title="'.date($df.' '.$nowtf, $row['realback']).'"' : ''; ?>><?php echo date($df.' '.$nowtf, $row['consegna']); ?></span></td>
			<td class="center"><?php echo !empty($car_first_feature) ? '<span class="label label-info">' . $car_first_feature . '</span>' : ''; ?></td>
			<td><?php echo date($df.' '.$nowtf, $row['ts']); ?></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
</div>
	<input type="hidden" name="idcar" value="<?php echo (int)$rows[0]['idcar']; ?>" />
	<input type="hidden" name="ts" value="<?php echo JHtml::_('esc_attr', $pts); ?>" />
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="choosebusy" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
