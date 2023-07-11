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
	<p class="warn"><?php echo JText::_('VRNOOPTIONALSFOUND'); ?></p>
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
	if (pressbutton == 'removeoptionals') {
		if (confirm('<?php echo JText::_('VRJSDELOPTIONALS'); ?> ?')) {
			submitform( pressbutton );
			return;
		} else {
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
					<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSONE' ); ?></th>
					<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSTWO' ); ?></th>
					<th class="title center" align="center" width="75"><?php echo JText::_( 'VRPVIEWOPTIONALSTHREE' ); ?></th>
					<th class="title center" align="center" width="75"><?php echo JText::_( 'VRPVIEWOPTIONALSFOUR' ); ?></th>
					<th class="title center" align="center" width="75"><?php echo JText::_( 'VRPVIEWOPTIONALSEIGHT' ); ?></th>
					<th class="title center" align="center" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSFIVE' ); ?></th>
					<th class="title center" align="center" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSSIX' ); ?></th>
					<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSSEVEN' ); ?></th>
					<th class="title center" width="60" align="center"><?php echo JText::_( 'VRCORDERING' ); ?></th>
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
				<td><a href="index.php?option=com_vikrentcar&amp;task=editoptional&amp;cid[]=<?php echo (int)$row['id']; ?>"><?php echo JHtml::_('esc_html', $row['name']); ?></a></td>
				<td class="vrc-wrap-table-description"><?php echo (strlen($row['descr']) > 150 ? substr($row['descr'], 0, 150) : $row['descr']); ?></td>
				<td class="center"><?php echo $row['cost']; ?></td>
				<td class="center"><?php echo VikRentCar::getAliq($row['idiva']); ?>%</td>
				<td class="center"><?php echo $row['maxprice']; ?></td>
				<td class="center"><?php echo (intval($row['perday'])==1 ? "<i class=\"" . VikRentCarIcons::i('check', 'vrc-icn-img') . "\" style=\"color: #099909;\"></i>" : "<i class=\"" . VikRentCarIcons::i('times-circle', 'vrc-icn-img') . "\" style=\"color: #ff0000;\"></i>"); ?></td>
				<td class="center"><?php echo (intval($row['hmany'])==1 ? "&gt; 1" : "1"); ?></td>
				<td>
					<?php echo (is_file(VRC_ADMIN_PATH.DS.'resources'.DS.$row['img']) ? "<span class=\"vrc-optlist-img-wrap\"><span class=\"vrc-optlist-img-name\">".$row['img']."</span> <img class=\"vrc-optlist-img\" src=\"".VRC_ADMIN_URI."resources/".$row['img']."\"/></span>" : $row['img']); ?>
				</td>
				<td class="center">
					<a href="index.php?option=com_vikrentcar&amp;task=sortoptional&amp;cid[]=<?php echo (int)$row['id']; ?>&amp;mode=up"><?php VikRentCarIcons::e('arrow-up', 'vrc-icn-img'); ?></a> 
					<a href="index.php?option=com_vikrentcar&amp;task=sortoptional&amp;cid[]=<?php echo (int)$row['id']; ?>&amp;mode=down"><?php VikRentCarIcons::e('arrow-down', 'vrc-icn-img'); ?></a>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
	</div>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="optionals" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
