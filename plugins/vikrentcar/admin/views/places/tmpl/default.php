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
	<p class="warn"><?php echo JText::_('VRNOPLACESFOUND'); ?></p>
	<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
	<?php
} else {
	
	?>
<script type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'removeplace') {
		if (confirm('<?php echo JText::_('VRJSDELPLACES'); ?> ?')) {
			submitform( pressbutton );
			return;
		} else{
			return false;
		}
	}

	// do field validation
	try {
		document.adminForm.onsubmit();
	}
	catch(e) {}
	submitform( pressbutton );
}
</script>

<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm" class="vrc-list-form">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vrc-list-table">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
			<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWPLACESONE' ); ?></th>
			<th class="title left" width="150"><?php echo JText::_( 'VRCLOCADDRESS' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPLACELAT' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCPLACELNG' ); ?></th>
			<th class="title left" width="150"><?php echo JText::_( 'VRCPLACEDESCR' ); ?></th>
			<th class="title center" width="150" align="center"><?php echo JText::_( 'VRCPLACEOPENTIME' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VRCORDERING' ); ?></th>
		</tr>
		</thead>
	<?php

	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$opentime = "";
		if (!empty($row['opentime'])) {
			$parts = explode("-", $row['opentime']);
			$openat=VikRentCar::getHoursMinutes($parts[0]);
			$closeat=VikRentCar::getHoursMinutes($parts[1]);
			$opentime = ((int)$openat[0] < 10 ? "0".$openat[0] : $openat[0]).":".((int)$openat[1] < 10 ? "0".$openat[1] : $openat[1])." - ".((int)$closeat[0] < 10 ? "0".$closeat[0] : $closeat[0]).":".((int)$closeat[1] < 10 ? "0".$closeat[1] : $closeat[1]);
		}
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikrentcar&amp;task=editplace&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php echo $row['address']; ?></td>
			<td class="center"><?php echo $row['lat']; ?></td>
			<td class="center"><?php echo $row['lng']; ?></td>
			<td><?php echo strip_tags($row['descr']); ?></td>
			<td class="center"><?php echo $opentime; ?></td>
			<td class="center">
				<a href="index.php?option=com_vikrentcar&amp;task=sortlocation&amp;cid[]=<?php echo (int)$row['id']; ?>&amp;mode=up"><?php VikRentCarIcons::e('arrow-up', 'vrc-icn-img'); ?></a> 
				<a href="index.php?option=com_vikrentcar&amp;task=sortlocation&amp;cid[]=<?php echo (int)$row['id']; ?>&amp;mode=down"><?php VikRentCarIcons::e('arrow-down', 'vrc-icn-img'); ?></a>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="places" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
