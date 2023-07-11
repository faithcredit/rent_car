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

if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VRCNOOOHFEESFOUND'); ?></p>
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
			<th class="title left" width="200"><?php echo JText::_( 'VRCPVIEWOOHFEESONE' ); ?></th>
			<th class="title center" width="200" align="center"><?php echo JText::_( 'VRCPVIEWOOHFEESTWO' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPVIEWOOHFEESTHREE' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPVIEWOOHFEESFOUR' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPVIEWOOHFEESFIVE' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCWEEKDAYS' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPVIEWOOHFEESSEVEN' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPVIEWOOHFEESEIGHT' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPVIEWOOHFEESNINE' ); ?></th>
		</tr>
		</thead>
	<?php
	$currencysymb = VikRentCar::getCurrencySymb(true);
	$nowdf = VikRentCar::getDateFormat(true);
	$nowtf = VikRentCar::getTimeFormat(true);
	if ($nowdf == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($nowdf == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	$base_time = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$tot_cars = !empty($row['idcars']) ? ((int)count(explode(',', $row['idcars'])) - 1) : 0;
		$tot_cars = $tot_cars > 0 ? $tot_cars : 0;
		$pickdrop = $row['type'] == 1 ? JText::_('VRCPVIEWOOHFEESTEN') : ($row['type'] == 2 ? JText::_('VRCPVIEWOOHFEESELEVEN') : JText::_('VRCPVIEWOOHFEESTWELVE'));
		$wdays_parts = explode(",", $row['wdays']);
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikrentcar&amp;task=editoohfee&amp;cid[]=<?php echo (int)$row['id']; ?>"><?php echo JHtml::_('esc_html', $row['oohname']); ?></a></td>
			<td class="center"><?php echo date($nowtf, ($base_time + $row['from'])); ?></td>
			<td class="center"><?php echo date($nowtf, ($base_time + $row['to'])); ?></td>
			<td class="center"><?php echo $row['pickcharge']; ?></td>
			<td class="center"><?php echo $row['dropcharge']; ?></td>
			<td class="center"><?php echo count($wdays_parts); ?></td>
			<td class="center"><?php echo $tot_cars; ?></td>
			<td class="center"><?php echo $row['tot_xref']; ?></td>
			<td class="center"><?php echo $pickdrop; ?></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="oohfees" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
