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

$row = $this->row;

$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadVisualEditorAssets();

$forms = $this->onDisplayView();

?>

<script type="text/javascript">
function vikLoadCronParameters(pfile) {
	if (pfile.length > 0) {
		jQuery("#vrc-cron-params").html('<?php echo addslashes(JTEXT::_('VIKLOADING')); ?>');
		jQuery.ajax({
			type: "POST",
			url: "<?php echo VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=loadcronparams&tmpl=component'); ?>",
			data: { phpfile: pfile }
		}).done(function(res) {
			jQuery("#vrc-cron-params").html(res);
		});
	} else {
		jQuery("#vrc-cron-params").html('<p>--------</p>');
	}
}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCCRONNAME'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="cron_name" value="<?php echo count($row) ? htmlspecialchars($row['cron_name']) : ''; ?>" size="50"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCCRONCLASS'); ?></div>
							<div class="vrc-param-setting">
								<select name="class_file" id="cronfile" onchange="vikLoadCronParameters(this.value);">
									<?php
									$classfiles = [
										JHtml::_('select.option', '', ''),
									];

									foreach ($this->supportedDrivers as $driverId => $driver)
									{
										$classfiles[] = JHtml::_('select.option', $driverId, $driver->getTitle());
									}
									
									echo JHtml::_('select.options', $classfiles, 'value', 'text', $row ? basename($row['class_file'], '.php') : '');
									?>
								</select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCCRONPUBLISHED'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('published', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$row['published'] : 1), 1, 0); ?></div>
						</div>
						<?php
						// look for any additional fields to be pushed within the "Details" fieldset (left-side)
						if (isset($forms['cronjob']))
						{
							echo $forms['cronjob'];
						}
						?>
					</div>
				</div>
			</fieldset>
			<?php
			foreach ($forms as $legend => $form)
			{
				if (in_array($legend, ['cronjob', 'params']))
				{
					// skip default forms
					continue;
				}
				?>
				<fieldset class="adminform">
					<div class="vrc-params-wrap">
						<legend class="adminlegend"><?php echo JText::_($legend); ?></legend>
						<div class="vrc-params-container">
							<?php echo $form; ?>
						</div>
					</div>
				</fieldset>
				<?php
			}
			?>
		</div>
		<div class="vrc-config-maintab-right">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCCRONPARAMS'); ?></legend>
					<div class="vrc-params-container">
						<div id="vrc-cron-params">
							<?php echo count($row) ? VikRentCar::displayCronParameters($row['class_file'], $row['params']) : ''; ?>
						</div>
						<?php
						// look for any additional fields to be pushed within the "Parameters" fieldset (right-side)
						if (isset($forms['params']))
						{
							echo $forms['params'];
						}
						?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar">
<?php
if (count($row)) :
?>
	<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
<?php
endif;
?>
	<?php echo JHtml::_('form.token'); ?>
</form>
