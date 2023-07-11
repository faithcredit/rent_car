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
$all_cars = $this->all_cars;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$orderby = $this->orderby;
$ordersort = $this->ordersort;

if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VRNORESTRICTIONSFOUND'); ?></p>
	<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
	<?php
} else {
	$df = VikRentCar::getDateFormat(true);
	if ($df == "%d/%m/%Y") {
		$cdf = 'd/m/Y';
	} elseif ($df == "%m/%d/%Y") {
		$cdf = 'm/d/Y';
	} else {
		$cdf = 'Y/m/d';
	}
	?>
<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm" class="vrc-list-form">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vrc-list-table">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
			<th class="title center" width="50" align="center">
				<a href="index.php?option=com_vikrentcar&amp;task=restrictions&amp;vrorderby=id&amp;vrordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "id" ? "vrc-list-activesort" : "")); ?>">
					ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "id" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="100">
				<a href="index.php?option=com_vikrentcar&amp;task=restrictions&amp;vrorderby=name&amp;vrordersort=<?php echo ($orderby == "name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "name" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "name" ? "vrc-list-activesort" : "")); ?>">
					<?php echo JText::_('VRPVIEWRESTRICTIONSONE').($orderby == "name" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "name" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRPVIEWRESTRICTIONSTWO' ); ?></th>
			<th class="title center" width="150" align="center"><?php echo JText::_( 'VRRESTRICTIONSDRANGE' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRPVIEWRESTRICTIONSTHREE' ); ?></th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikrentcar&amp;task=restrictions&amp;vrorderby=minlos&amp;vrordersort=<?php echo ($orderby == "minlos" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "minlos" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "minlos" ? "vrc-list-activesort" : "")); ?>">
					<?php echo JText::_('VRPVIEWRESTRICTIONSFOUR').($orderby == "minlos" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "minlos" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikrentcar&amp;task=restrictions&amp;vrorderby=maxlos&amp;vrordersort=<?php echo ($orderby == "maxlos" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "maxlos" && $ordersort == "ASC" ? "vrc-list-activesort" : ($orderby == "maxlos" ? "vrc-list-activesort" : "")); ?>">
					<?php echo JText::_('VRPVIEWRESTRICTIONSFIVE').($orderby == "maxlos" && $ordersort == "ASC" ? '<i class="fas fa-sort-up"></i>' : ($orderby == "maxlos" ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="150" align="center"><?php echo JText::_( 'VRPVIEWRESTRICTIONSCTA' ); ?></th>
			<th class="title center" width="150" align="center"><?php echo JText::_( 'VRPVIEWRESTRICTIONSCTD' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRRESTRLISTCARS' ); ?></th>
		</tr>
		</thead>
	<?php
	$arrmonths = array(
		1 => JText::_('VRMONTHONE'),
		2 => JText::_('VRMONTHTWO'),
		3 => JText::_('VRMONTHTHREE'),
		4 => JText::_('VRMONTHFOUR'),
		5 => JText::_('VRMONTHFIVE'),
		6 => JText::_('VRMONTHSIX'),
		7 => JText::_('VRMONTHSEVEN'),
		8 => JText::_('VRMONTHEIGHT'),
		9 => JText::_('VRMONTHNINE'),
		10 => JText::_('VRMONTHTEN'),
		11 => JText::_('VRMONTHELEVEN'),
		12 => JText::_('VRMONTHTWELVE')
	);
	$arrwdays = array(
		1 => JText::_('VRCMONDAY'),
		2 => JText::_('VRCTUESDAY'),
		3 => JText::_('VRCWEDNESDAY'),
		4 => JText::_('VRCTHURSDAY'),
		5 => JText::_('VRCFRIDAY'),
		6 => JText::_('VRCSATURDAY'),
		0 => JText::_('VRCSUNDAY')
	);
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$drange = '-';
		if (!empty($row['dfrom'])) {
			$drange = date($cdf, $row['dfrom']).' - '.date($cdf, $row['dto']);
		}
		$car_tips = array();
		$saycars = JText::_('VRRESTRALLCARS');
		if ($row['allcars'] == 0) {
			$idr = explode(';', $row['idcars']);
			$saycars = (count($idr) - 1);
			foreach ($idr as $tipid) {
				if (!empty($tipid)) {
					$tipidr = (int)str_replace('-', '', $tipid);
					if (array_key_exists($tipidr, $all_cars)) {
						$car_tips[] = $all_cars[$tipidr];
					}
				}
			}
		}
		$wdayscta = array();
		$wdaysctd = array();
		if (!empty($row['ctad'])) {
			$wdayscta = explode(',', $row['ctad']);
			foreach ($wdayscta as $ctk => $ctv) {
				$wk = intval(str_replace('-', '', $ctv));
				if (array_key_exists($wk, $arrwdays)) {
					$wdayscta[$ctk] = $arrwdays[$wk];
				} else {
					unset($wdayscta[$ctk]);
				}
			}
		}
		if (!empty($row['ctdd'])) {
			$wdaysctd = explode(',', $row['ctdd']);
			foreach ($wdaysctd as $ctk => $ctv) {
				$wk = intval(str_replace('-', '', $ctv));
				if (array_key_exists($wk, $arrwdays)) {
					$wdaysctd[$ctk] = $arrwdays[$wk];
				} else {
					unset($wdaysctd[$ctk]);
				}
			}
		}
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td class="center"><a href="index.php?option=com_vikrentcar&amp;task=editrestriction&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a href="index.php?option=com_vikrentcar&amp;task=editrestriction&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td class="center"><?php echo !empty($row['month']) ? $arrmonths[$row['month']] : '-'; ?></td>
			<td class="center"><?php echo $drange; ?></td>
			<td class="center"><?php echo (strlen($row['wday']) > 0 ? $arrwdays[$row['wday']] : '').(strlen($row['wday']) > 0 && strlen($row['wdaytwo']) > 0 ? '/'.$arrwdays[$row['wdaytwo']] : ''); ?></td>
			<td class="center"><?php echo $row['minlos']; ?></td>
			<td class="center"><?php echo !empty($row['maxlos']) ? $row['maxlos'] : '-'; ?></td>
			<td class="center"><?php echo count($wdayscta) > 0 ? implode(', ', $wdayscta) : '-'; ?></td>
			<td class="center"><?php echo count($wdaysctd) > 0 ? implode(', ', $wdaysctd) : '-'; ?></td>
			<td class="center"><span<?php echo count($car_tips) > 0 ? ' title="'.addslashes(implode(', ', $car_tips)).'" style="padding: 0 3px;"' : ''; ?>><?php echo $saycars; ?></span></td>
		</tr>	
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="restrictions" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
