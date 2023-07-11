<?php
/** 
 * @package     VikRentCar
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JHtml::_('vrchtml.scripts.ajaxcsrf');

$rows = $this->rows;

$canEdit = JFactory::getUser()->authorise('core.admin', 'com_vikrentcar');
?>

<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">

<?php
if (count($rows) == 0)
{
	?>
	<p class="warn"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></p>
	<?php
}
else
{
	?>
	<div class="vrc-list-form">
		<div class="table-responsive">
			<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vrc-list-table">
				<thead>
					<tr>

						<th width="1%">
							<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
						</th>

						<!-- DATE -->
						
						<th class="title left" width="20%" style="text-align: left;">
							<a href="index.php?option=com_vikrentcar&amp;view=backups&amp;filter_order=createdon&amp;filter_order_Dir=<?php echo ($this->ordering == "createdon" && $this->orderDir == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($this->ordering == "createdon" && $this->orderDir == "ASC" ? "vrc-list-activesort" : ($this->ordering == "createdon" ? "vrc-list-activesort" : "")); ?>">
								<?php echo JText::_('VRPVIEWORDERSONE').($this->ordering == "createdon" && $this->orderDir == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($this->ordering == "createdon" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
							</a>
						</th>

						<!-- TYPE -->
						
						<th class="title left" width="20%" style="text-align: left;">
							<?php echo JText::_('VRC_CONFIG_BACKUP_TYPE'); ?>
						</th>

						<!-- SIZE -->
						
						<th class="title hidden-phone nowrap" width="8%" style="text-align: center;">
							<a href="index.php?option=com_vikrentcar&amp;view=backups&amp;filter_order=filesize&amp;filter_order_Dir=<?php echo ($this->ordering == "filesize" && $this->orderDir == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($this->ordering == "filesize" && $this->orderDir == "ASC" ? "vrc-list-activesort" : ($this->ordering == "filesize" ? "vrc-list-activesort" : "")); ?>">
								<?php echo JText::_('VRC_BACKUP_SIZE').($this->ordering == "filesize" && $this->orderDir == "ASC" ? '<i class="'.VikRentCarIcons::i('sort-asc').'"></i>' : ($this->ordering == "filesize" ? '<i class="'.VikRentCarIcons::i('sort-desc').'"></i>' : '<i class="'.VikRentCarIcons::i('sort').'"></i>')); ?>
							</a>
						</th>

						<!-- ACTIONS -->
						
						<?php
						if ($canEdit)
						{
							?>
							<th class="title hidden-phone nowrap" width="14%" style="text-align: center;" colspan="2">
								<?php echo JText::_('VRCCRONACTIONS'); ?>
							</th>
							<?php
						}
						?>
					
					</tr>
				</thead>
				
				<?php
				for ($i = 0, $n = count($rows); $i < $n; $i++)
				{
					$row = $rows[$i];
					?>
					<tr class="row<?php echo ($i % 2); ?>">

						<td>
							<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $this->escape($row->name); ?>" onclick="Joomla.isChecked(this.checked);">
						</td>

						<!-- NAME -->

						<td>
							<?php echo JHtml::_('date', $row->date, 'Y-m-d H:i:s'); ?>
						</td>

						<!-- TYPE -->

						<td>
							<?php echo $row->type->name; ?>
						</td>

						<!-- SIZE -->

						<td style="text-align: center;" class="hidden-phone">
							<?php echo JHtml::_('number.bytes', $row->size); ?>
						</td>

						<!-- ACTIONS -->
						
						<?php
						if ($canEdit)
						{
							?>
							<td style="text-align: right;" class="hidden-phone" width="7%">
								<a href="<?php echo VRCFactory::getPlatform()->getUri()->addCSRF('index.php?option=com_vikrentcar&task=backup.restore&cid[]=' . $this->escape($row->name), $xhtml = true); ?>" class="backup-restore-link btn btn-danger">
									<?php echo JText::_('VRC_WIDGETS_RESTDEFAULTSHORT'); ?>
								</a>
							</td>

							<td style="text-align: left;" class="hidden-phone" width="7%">
								<a href="<?php echo $row->url; ?>" class="btn btn-primary">
									<?php echo JText::_('VRC_BACKUP_DOWNLOAOD'); ?>
								</a>
							</td>
							<?php
						}
						?>

					</tr>
					<?php
				}
				?>
			</table>
		</div>
	</div>
	<?php
}
?>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="backups" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<?php
// render modal script
echo VikRentCar::getVrcApplication()->getJmodalScript();

// load create modal content
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-newbackup',
	array(
		'title'       => JText::_('VRCMAINTITLENEWBACKUP'),
		'closeButton' => true,
		'keyboard'    => false, 
		'bodyHeight'  => 80,
		'width'       => 60,
		'footer'      => '<button type="button" class="btn btn-success" data-role="backup.save">' . JText::_('VRSAVE') . '</button>',
	),
	$this->loadTemplate('modal')
);

JText::script('VRCBACKUPRESTORECONF1');
JText::script('VRCBACKUPRESTORECONF2');
?>

<script>

	(function($) {
		'use strict';

		Joomla.submitbutton = (task) => {
			if (task === 'backup.add') {
				vrcOpenJModal('newbackup');
			} else {
				Joomla.submitform(task, document.adminForm);
			}
		}

		$(function() {
			$('a.backup-restore-link').on('click', (event) => {
				let r = confirm(Joomla.JText._('VRCBACKUPRESTORECONF1'));

				if (!r) {
					return false;
				}

				r = confirm(Joomla.JText._('VRCBACKUPRESTORECONF2'));

				if (!r) {
					return false
				}

				return true;
			});
		});
	})(jQuery);

	function vrcCloseJModal(id) {
		jQuery('#jmodal-' + id).modal('toggle');
	}

</script>
