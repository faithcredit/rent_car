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

$dbo = JFactory::getDbo();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadSelect2();

JText::script('VRCDELCONFIRM');

$q = "SELECT * FROM `#__vikrentcar_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"optaliq\"><option value=\"\"> </option>\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva']==$iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikrentcar&task=iva\">".JText::_('VRNOIVAFOUND')."</a>";
}
$currencysymb = VikRentCar::getCurrencySymb(true);
//vikrentcar 1.6
if (count($row) && strlen($row['forceval']) > 0) {
	$forceparts = explode("-", $row['forceval']);
	$forcedq = $forceparts[0];
	$forcedqperday = intval($forceparts[1]) == 1 ? true : false;
} else {
	$forcedq = "1";
	$forcedqperday = false;
}
//
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
function showForceSel() {
	if (document.adminForm.forcesel.checked == true) {
		jQuery('.vrc-forceval-param').fadeIn();
	} else {
		jQuery('.vrc-forceval-param').hide();
	}
	return true;
}
function removeBlock(el) {
	return (elem=document.getElementById(el)).parentNode.removeChild(elem);
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

<?php
if (count($row)) {
?>
<div class="vrc-outer-info-message" id="vrc-outer-info-message-opt" style="display: block;" onclick="removeBlock('vrc-outer-info-message-opt');">
	<div class="vrc-info-message-cont">
		<?php VikRentCarIcons::e('info-circle'); ?> <span><?php echo JText::sprintf('VRCOPTASSTOXCARS', $this->tot_cars_options, $this->tot_cars); ?></span>
	</div>
</div>
<?php
}
?>

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWOPTONE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="optname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWOPTSEVEN'); ?></div>
							<div class="vrc-param-setting">
								<div class="vrc-param-setting-block">
								<?php
								echo (count($row) && is_file(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$row['img']) ? '<a href="'.VRC_ADMIN_URI.'resources/'.$row['img'].'" class="vrcmodal vrc-car-img-modal" target="_blank"><i class="' . VikRentCarIcons::i('image') . '"></i> '.$row['img'].'</a> ' : "");
								if (count($row) && !empty($row['img'])) {
									?>
									<a class="btn btn-small btn-danger vrc-trash-uploaded-img" href="index.php?option=com_vikrentcar&task=trash_upld_img&type=option&rid=<?php echo $row['id']; ?>" onclick="return confirm(Joomla.JText._('VRCDELCONFIRM'));"><?php VikRentCarIcons::e('trash'); ?></a>
									<?php
								}
								?>
									<input type="file" name="optimg" size="35"/>
								</div>
								<div class="vrc-param-setting-block">
									<span class="vrc-resize-lb-cont">
										<label style="display: inline;" for="autoresize"><?php echo JText::_('VRNEWOPTNINE'); ?></label> 
										<input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> 
									</span>
									<span id="resizesel" style="display: none;"><span><?php echo JText::_('VRNEWOPTTEN'); ?></span><input type="text" name="resizeto" value="250" size="3" class="vrc-small-input"/> px</span>
								</div>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWOPTTHREE'); ?></div>
							<div class="vrc-param-setting"><?php echo $currencysymb; ?> <input type="number" step="any" name="optcost" value="<?php echo count($row) && !is_null($row['cost']) ? (float)$row['cost'] : ''; ?>" /></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWOPTFOUR'); ?></div>
							<div class="vrc-param-setting"><?php echo $wiva; ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWOPTFIVE'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('optperday', JText::_('VRYES'), JText::_('VRNO'), (count($row) && intval($row['perday']) == 1 ? 'each' : 0), 'each', 0); ?>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWOPTEIGHT'); ?></div>
							<div class="vrc-param-setting"><?php echo $currencysymb; ?> <input type="number" step="any" name="maxprice" value="<?php echo count($row) ? (float)$row['maxprice'] : ''; ?>"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWOPTSIX'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('opthmany', JText::_('VRYES'), JText::_('VRNO'), (count($row) && intval($row['hmany']) == 1 ? 'yes' : 0), 'yes', 0); ?>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWOPTFORCEVALIFDAYS'); ?></div>
							<div class="vrc-param-setting"><input type="number" min="0" name="forceifdays" value="<?php echo count($row) ? (int)$row['forceifdays'] : ''; ?>"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWOPTFORCESEL'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('forcesel', JText::_('VRYES'), JText::_('VRNO'), (count($row) && intval($row['forcesel']) == 1 ? 1 : 0), 1, 0, 'showForceSel();'); ?>
							</div>
						</div>
						<div class="vrc-param-container vrc-param-nested vrc-forceval-param" style="display: <?php echo (count($row) && intval($row['forcesel']) == 1 ? "flex" : "none"); ?>;">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWOPTFORCEVALT'); ?></div>
							<div class="vrc-param-setting">
								<input type="number" min="0" step="any" name="forceval" value="<?php echo $forcedq; ?>" />
							</div>
						</div>
						<div class="vrc-param-container vrc-param-nested vrc-forceval-param" style="display: <?php echo (count($row) && intval($row['forcesel']) == 1 ? "flex" : "none"); ?>;">
							<div class="vrc-param-label"><?php echo JText::_('VRCNEWOPTFORCEVALTPDAY'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('forcevalperday', JText::_('VRYES'), JText::_('VRNO'), ($forcedqperday ? 1 : 0), 1, 0); ?>
							</div>
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
							<div class="vrc-param-label"><?php echo JText::_('VRNEWOPTTWO'); ?></div>
							<div class="vrc-param-setting">
								<?php
								if (interface_exists('Throwable')) {
									/**
									 * With PHP >= 7 supporting throwable exceptions for Fatal Errors
									 * we try to avoid issues with third party plugins that make use
									 * of the WP native function get_current_screen().
									 * 
									 * @wponly
									 */
									try {
										echo $editor->display( "optdescr", (count($row) ? $row['descr'] : ''), 400, 200, 70, 20 );
									} catch (Throwable $t) {
										echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
									}
								} else {
									// we cannot catch Fatal Errors in PHP 5.x
									echo $editor->display( "optdescr", (count($row) ? $row['descr'] : ''), 400, 200, 70, 20 );
								}
								?>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCCARSASSIGNED'); ?></div>
							<div class="vrc-param-setting">
								<span class="vrc-select-all"><?php echo JText::_('VRCSELECTALL'); ?></span>
								<select name="idcars[]" multiple="multiple" id="idcars">
								<?php
								foreach ($this->allcars as $carid => $car) {
									$is_car_assigned = (count($row) && is_array($car['idopt']) && in_array((string)$row['id'], $car['idopt']));
									?>
									<option value="<?php echo (int)$carid; ?>"<?php echo $is_car_assigned ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $car['name']); ?></option>
									<?php
								}
								?>
								</select>
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
