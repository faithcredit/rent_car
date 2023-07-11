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
	<p class="err"><?php echo JText::_('VRNOFIELDSFOUND'); ?></p>
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
			<th class="title center" width="50" align="center">ID</th>
			<th class="title left" width="200"><?php echo JText::_( 'VRPVIEWCUSTOMFONE' ); ?></th>
			<th class="title left" width="200"><?php echo JText::_( 'VRPVIEWCUSTOMFTWO' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRPVIEWCUSTOMFTHREE' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRPVIEWCUSTOMFFOUR' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRPVIEWCUSTOMFFIVE' ); ?></th>
		</tr>
		</thead>
	<?php

	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td class="center"><?php echo $row['id']; ?></td>
			<td><a href="index.php?option=com_vikrentcar&amp;task=editcustomf&amp;cid[]=<?php echo (int)$row['id']; ?>"><?php echo JHtml::_('esc_html', JText::_($row['name'])); ?></a></td>
			<td><?php echo ucwords($row['type']).($row['isnominative'] == 1 ? ' <span class="badge">'.JText::_('VRCISNOMINATIVE').'</span>' : '').($row['isphone'] == 1 ? ' <span class="badge">'.JText::_('VRCISPHONENUMBER').'</span>' : '').(!empty($row['flag']) ? ' <span class="badge">'.JText::_('VRCIS'.strtoupper($row['flag'])).'</span>' : ''); ?></td>
			<td class="center"><?php echo intval($row['required']) == 1 ? "<i class=\"" . VikRentCarIcons::i('check', 'vrc-icn-img') . "\" style=\"color: #099909;\"></i>" : "<i class=\"" . VikRentCarIcons::i('times-circle', 'vrc-icn-img') . "\" style=\"color: #ff0000;\"></i>"; ?></td>
			<td class="center"><a href="index.php?option=com_vikrentcar&amp;task=sortfield&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=up"><?php VikRentCarIcons::e('arrow-up', 'vrc-icn-img'); ?></a> <a href="index.php?option=com_vikrentcar&amp;task=sortfield&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=down"><?php VikRentCarIcons::e('arrow-down', 'vrc-icn-img'); ?></a></td>
			<td class="center"><?php echo intval($row['isemail']) == 1 ? "<i class=\"" . VikRentCarIcons::i('check', 'vrc-icn-img') . "\" style=\"color: #099909;\"></i>" : "<i class=\"" . VikRentCarIcons::i('times-circle', 'vrc-icn-img') . "\" style=\"color: #ff0000;\"></i>"; ?></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="customf" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
