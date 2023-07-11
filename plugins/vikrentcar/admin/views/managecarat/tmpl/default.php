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
$vrc_app->loadSelect2();

JText::script('VRCDELCONFIRM');

?>
<script type="text/javascript">
function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		document.getElementById('resizesel').style.display='inline-block';
	} else {
		document.getElementById('resizesel').style.display='none';
	}
	return true;
}
jQuery(document).ready(function() {
	jQuery('#idcars').select2();
	jQuery('.vrc-select-all').click(function() {
		var nextsel = jQuery(this).next("select");
		nextsel.find("option").prop('selected', true);
		nextsel.trigger('change');
	});
});
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARATONE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="caratname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARATTWO'); ?></div>
							<div class="vrc-param-setting">
								<div class="vrc-param-setting-block">
								<?php
								echo (count($row) && is_file(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$row['icon']) ? '<a href="'.VRC_ADMIN_URI.'resources/'.$row['icon'].'" class="vrcmodal vrc-car-img-modal" target="_blank"><i class="' . VikRentCarIcons::i('image') . '"></i> '.$row['icon'].'</a> ' : "");
								if (count($row) && !empty($row['icon'])) {
									?>
									<a class="btn btn-small btn-danger vrc-trash-uploaded-img" href="index.php?option=com_vikrentcar&task=trash_upld_img&type=carat&rid=<?php echo $row['id']; ?>" onclick="return confirm(Joomla.JText._('VRCDELCONFIRM'));"><?php VikRentCarIcons::e('trash'); ?></a>
									<?php
								}
								?>
									<input type="file" name="caraticon" size="35"/>
								</div>
								<div class="vrc-param-setting-block">
									<span class="vrc-resize-lb-cont">
										<label style="display: inline;" for="autoresize"><?php echo JText::_('VRNEWOPTNINE'); ?></label> 
										<input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> 
									</span>
									<span id="resizesel" style="display: none;"><span><?php echo JText::_('VRNEWOPTTEN'); ?></span><input type="text" name="resizeto" value="50" size="3" class="vrc-small-input"/> px</span>
								</div>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCARATTHREE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="carattextimg" value="<?php echo count($row) ? htmlspecialchars($row['textimg']) : ''; ?>" size="40"/></div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="vrc-config-maintab-right">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDSETTINGS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCCARSASSIGNED'); ?></div>
							<div class="vrc-param-setting">
								<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
								<select name="idcars[]" multiple="multiple" id="idcars">
								<?php
								foreach ($this->allcars as $rid => $car) {
									$is_car_assigned = (count($row) && is_array($car['idcarat']) && in_array((string)$row['id'], $car['idcarat']));
									?>
									<option value="<?php echo (int)$rid; ?>"<?php echo $is_car_assigned ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $car['name']); ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCPOSITIONORDERING'); ?></div>
							<div class="vrc-param-setting">
								<input type="number" name="ordering" value="<?php echo count($row) ? (int)$row['ordering'] : ''; ?>"/>
							<?php
							if (!count($row)) {
								?>
								<span class="vrc-param-setting-comment"><?php echo JText::_('VRCPOSITIONORDERINGHELP'); ?></span>
								<?php
							}
							?>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
	?>
	<input type="hidden" name="whereup" value="<?php echo (int)$row['id']; ?>">
	<?php
}
?>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<?php echo JHtml::_('form.token'); ?>
</form>
