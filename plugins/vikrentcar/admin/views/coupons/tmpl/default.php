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
	<p class="warn"><?php echo JText::_('VRCNOCOUPONSFOUND'); ?></p>
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
			<th class="title left" width="200"><?php echo JText::_( 'VRCPVIEWCOUPONSONE' ); ?></th>
			<th class="title center" width="200" align="center"><?php echo JText::_( 'VRCPVIEWCOUPONSTWO' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPVIEWCOUPONSTHREE' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPVIEWCOUPONSFOUR' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPVIEWCOUPONSFIVE' ); ?></th>
		</tr>
		</thead>
	<?php
	$currencysymb=VikRentCar::getCurrencySymb(true);
	$nowdf = VikRentCar::getDateFormat(true);
	if ($nowdf == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($nowdf == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$strtype = $row['type'] == 1 ? JText::_('VRCCOUPONTYPEPERMANENT') : JText::_('VRCCOUPONTYPEGIFT');
		$strtype .= ", ".$row['value']." ".($row['percentot'] == 1 ? "%" : $currencysymb);
		$strdate = JText::_('VRCCOUPONALWAYSVALID');
		if (strlen($row['datevalid']) > 0) {
			$dparts = explode("-", $row['datevalid']);
			$strdate = date($df, $dparts[0])." - ".date($df, $dparts[1]);
		}
		$totvehicles = 0;
		if (intval($row['allvehicles']) == 0) {
			$allve = explode(";", $row['idcars']);
			foreach ($allve as $fv) {
				if (!empty($fv)) {
					$totvehicles++;
				} 
			}
		}
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikrentcar&amp;task=editcoupon&amp;cid[]=<?php echo (int)$row['id']; ?>"><?php echo $row['code']; ?></a></td>
			<td class="center"><?php echo $strtype; ?></td>
			<td class="center"><?php echo $strdate; ?></td>
			<td class="center"><?php echo intval($row['allvehicles']) == 1 ? JText::_('VRCCOUPONALLVEHICLES') : $totvehicles; ?></td>
			<td class="center"><?php echo $row['mintotord']; ?></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="coupons" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
