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
	<p class="warn"><?php echo JText::_('VRNOLOCFEES'); ?></p>
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
					<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWPLOCFEEONE' ); ?></th>
					<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWPLOCFEETWO' ); ?></th>
					<th class="title left" width="100"><?php echo JText::_( 'VRPVIEWPLOCFEETHREE' ); ?></th>
					<th class="title center" width="100"><?php echo JText::_( 'VRPVIEWPLOCFEEFOUR' ); ?></th>
				</tr>
			</thead>
		<?php
		$currencysymb = VikRentCar::getCurrencySymb(true);
		$k = 0;
		$i = 0;
		for ($i = 0, $n = count($rows); $i < $n; $i++) {
			$row = $rows[$i];
			$say_from_loc = !empty($row['any_oneway']) ? ('<i class="' . VikRentCarIcons::i('long-arrow-alt-right') . '"></i> ' . JText::_('VRC_LOCFEE_ONEWAY')) : VikRentCar::getPlaceName($row['from']);
			$say_to_loc = !empty($row['any_oneway']) ? '-----' : VikRentCar::getPlaceName($row['to']);
			?>
			<tr class="row<?php echo $k; ?>">
				<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
				<td><a href="index.php?option=com_vikrentcar&amp;task=editlocfee&amp;cid[]=<?php echo (int)$row['id']; ?>"><?php echo $say_from_loc; ?></a></td>
				<td><?php echo $say_to_loc; ?></td>
				<td><?php echo $currencysymb.' '.$row['cost']; ?></td>
				<td class="center"><?php echo (intval($row['daily']) == 1 ? '<i class="' . VikRentCarIcons::i('check-circle', 'vrc-icn-img') . '" style="color: #099909;"></i>' : '<i class="' . VikRentCarIcons::i('times-circle', 'vrc-icn-img') . '" style="color: #ff0000;"></i>'); ?></td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
	</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="locfees" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
