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
$carsel = $this->carsel;
$all_cars = $this->all_cars;
$pricesel = $this->pricesel;
$all_prices = $this->all_prices;
$orderby = $this->orderby;
$ordersort = $this->ordersort;

JHTML::_('behavior.tooltip');
$pidcar = VikRequest::getInt('idcar', '', 'request');
$pidprice = VikRequest::getInt('idprice', '', 'request');
?>

<div class="vrc-list-form-filters vrc-btn-toolbar">
	<form action="index.php?option=com_vikrentcar" method="post" name="seasonsform">
		<div class="vrc-list-form-filter-select">
			<div class="vrc-list-form-filter-select-inner">
				<label for="idcar"><?php echo JText::_('VRCRATESOVWCAR'); ?></label>
				<?php echo $carsel; ?>
			</div>
		</div>
		<div class="vrc-list-form-filter-select">
			<div class="vrc-list-form-filter-select-inner">
				<label for="idprice"><?php echo JText::_('VRCSPTYPESPRICE'); ?></label>
				<?php echo $pricesel; ?>
			</div>
		</div>
		<input type="hidden" name="task" value="seasons" />
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
</div>

<?php
if (empty($rows)) {
	?>
<p class="warn"><?php echo JText::_('VRNOSEASONS'); ?></p>
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
			<th class="title left" width="30">
				<a href="index.php?option=com_vikrentcar&amp;task=seasons&amp;vrcorderby=id&amp;vrcordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidcar) ? '&idcar='.$pidcar : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "id" ? "vrc-list-activesort" : "")); ?>">
				ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "id" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="150">
				<a href="index.php?option=com_vikrentcar&amp;task=seasons&amp;vrcorderby=spname&amp;vrcordersort=<?php echo ($orderby == "spname" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidcar) ? '&idcar='.$pidcar : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "spname" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "spname" ? "vrc-list-activesort" : "")); ?>">
					<?php echo JText::_('VRPSHOWSEASONSPNAME').($orderby == "spname" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "spname" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikrentcar&amp;task=seasons&amp;vrcorderby=from&amp;vrcordersort=<?php echo ($orderby == "from" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidcar) ? '&idcar='.$pidcar : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "from" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "from" ? "vrc-list-activesort" : "")); ?>">
					<?php echo JText::_('VRPSHOWSEASONSONE').($orderby == "from" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "from" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikrentcar&amp;task=seasons&amp;vrcorderby=to&amp;vrcordersort=<?php echo ($orderby == "to" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidcar) ? '&idcar='.$pidcar : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "to" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "to" ? "vrc-list-activesort" : "")); ?>">
					<?php echo JText::_('VRPSHOWSEASONSTWO').($orderby == "to" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "to" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="150" align="center"><?php echo JText::_( 'VRPSHOWSEASONSWDAYS' ); ?></th>
			<th class="title center" width="150" align="center"><?php echo JText::_( 'VRPSHOWSEASONSSEVEN' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCSEASONAFFECTEDROOMS' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRPSHOWSEASONSTHREE' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCISPROMOTION' ); ?></th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikrentcar&amp;task=seasons&amp;vrcorderby=diffcost&amp;vrcordersort=<?php echo ($orderby == "diffcost" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidcar) ? '&idcar='.$pidcar : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "diffcost" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "diffcost" ? "vrc-list-activesort" : "")); ?>">
					<?php echo JText::_('VRPSHOWSEASONSFOUR').($orderby == "diffcost" && $ordersort == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($orderby == "diffcost" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
		</tr>
		</thead>
	<?php
	$currencysymb = VikRentCar::getCurrencySymb(true);
	if (VikRentCar::getDateFormat(true) == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} else {
		$df = 'm/d/Y';
	}
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		if ($row['from'] > 0 || $row['to'] > 0) {
			$nowyear = !empty($row['year']) ? $row['year'] : date('Y');
			$tsbase = mktime(0, 0, 0, 1, 1, $nowyear);
			//leap years
			$curyear = $nowyear;
			if ($curyear % 4 == 0 && ($curyear % 100 != 0 || $curyear % 400 == 0)) {
				$isleap = true;
			} else {
				$isleap = false;
			}
			//
			$sfrom = date($df, ($tsbase + $row['from']));
			$sto = date($df, ($tsbase + $row['to']));
			//leap years
			if ($isleap == true) {
				$infoseason = getdate($tsbase + $row['from']);
				$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
				if ($infoseason[0] >= $leapts) {
					$sfrom = date($df, ($tsbase + $row['from'] + 86400));
					$sto = date($df, ($tsbase + $row['to'] + 86400));
				}
			}
			//
		} else {
			$sfrom = "";
			$sto = "";
		}
		$actwdays = explode(';', $row['wdays']);
		$wdaysmatch = array('0' => JText::_('VRCSUNDAY'), '1' => JText::_('VRCMONDAY'), '2' => JText::_('VRCTUESDAY'), '3' => JText::_('VRCWEDNESDAY'), '4' => JText::_('VRCTHURSDAY'), '5' => JText::_('VRCFRIDAY'), '6' => JText::_('VRCSATURDAY'));
		$wdaystr = "";
		if (@count($actwdays) > 0) {
			foreach ($actwdays as $awd) {
				if (strlen($awd) > 0) {
					$wdaystr .= substr($wdaysmatch[$awd], 0, 3).' ';
				}
			}
		}
		$aff_cars = 0;
		$aff_cars_title = array();
		$scars = explode(',', $row['idcars']);
		foreach ($scars as $scar) {
			$aff_idcar = intval(str_replace('-', '', $scar));
			if (!empty($scar) && $aff_idcar > 0) {
				$aff_cars++;
				if (array_key_exists($aff_idcar, $all_cars)) {
					$aff_cars_title[] = $all_cars[$aff_idcar];
				}
			}
		}
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td class="left"><a href="index.php?option=com_vikrentcar&amp;task=editseason&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td class="left vrc-highlighted-td"><a href="index.php?option=com_vikrentcar&amp;task=editseason&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['spname']; ?></a></td>
			<td class="center"><?php echo $sfrom; ?></td>
			<td class="center"><?php echo $sto; ?></td>
			<td class="center"><?php echo $wdaystr; ?></td>
			<td class="center"><?php echo (!empty($row['locations']) ? VikRentCar::getPlaceName($row['locations']) : JText::_('VRSEASONANY')); ?></td>
			<td class="center"><span class="hasTooltip" title="<?php echo implode(', ', $aff_cars_title); ?>"><?php echo $aff_cars; ?></span></td>
			<td class="center"><?php echo (intval($row['type']) == 1 ? JText::_('VRPSHOWSEASONSFIVE') : JText::_('VRPSHOWSEASONSSIX')); ?></td>
			<td class="center"><?php echo ($row['promo'] == 1 ? "<i class=\"" . VikRentCarIcons::i('check', 'vrc-icn-img') . "\" style=\"color: #099909;\"></i>" : "<i class=\"" . VikRentCarIcons::i('times-circle', 'vrc-icn-img') . "\" style=\"color: #ff0000;\"></i>"); ?></td>
			<td class="center"><?php echo $row['diffcost']; ?> <?php echo (intval($row['val_pcent']) == 1 ? $currencysymb : '%'); ?></td>
		</tr>	
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="seasons" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
