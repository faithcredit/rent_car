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
$wsel = $this->wsel;
$wseltwo = $this->wseltwo;

$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadSelect2();
$currencysymb = VikRentCar::getCurrencySymb(true);
if (strlen($wsel) > 0) {
	$dbo = JFactory::getDbo();
	$q = "SELECT * FROM `#__vikrentcar_iva`;";
	$dbo->setQuery($q);
	$dbo->execute();
	if ($dbo->getNumRows() > 0) {
		$ivas = $dbo->loadAssocList();
		$wiva = "<select name=\"aliq\">\n";
		foreach ($ivas as $iv) {
			$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
		}
		$wiva .= "</select>\n";
	} else {
		$wiva = "<a href=\"index.php?option=com_vikrentcar&task=iva\">".JText::_('VRNOIVAFOUND')."</a>";
	}
	
	$actvalueoverrides = '';
	if (count($row) && strlen($row['losoverride']) > 0) {
		$losoverrides = explode('_', $row['losoverride']);
		foreach ($losoverrides as $loso) {
			if (!empty($loso)) {
				$losoparts = explode(':', $loso);
				$actvalueoverrides .= '<p>'.JText::_('VRLOCFEECOSTOVERRIDEDAYS').' <input type="number" name="nightsoverrides[]" value="'.$losoparts[0].'" min="0"/> - '.JText::_('VRLOCFEECOSTOVERRIDECOST').' <input type="number" name="valuesoverrides[]" value="'.$losoparts[1].'" step="any"/> '.$currencysymb.'</p>';
			}
		}
	}
	?>
	<script type="text/javascript">
		function addMoreOverrides() {
			var ni = document.getElementById('myDiv');
			var numi = document.getElementById('morevalueoverrides');
			var num = (document.getElementById('morevalueoverrides').value -1)+ 2;
			numi.value = num;
			var newdiv = document.createElement('div');
			var divIdName = 'my'+num+'Div';
			newdiv.setAttribute('id',divIdName);
			newdiv.innerHTML = '<p><?php echo addslashes(JText::_('VRLOCFEECOSTOVERRIDEDAYS')); ?> <input type=\'number\' name=\'nightsoverrides[]\' value=\'\' min=\'0\'/> - <?php echo addslashes(JText::_('VRLOCFEECOSTOVERRIDECOST')); ?> <input type=\'number\' name=\'valuesoverrides[]\' value=\'\' step=\'any\'/> <?php echo addslashes($currencysymb); ?></p>';
			ni.appendChild(newdiv);
		}

		function toggleOneWayFee(enabled) {
			if (enabled) {
				jQuery('.vrc-is-roundtrip').hide();
			} else {
				jQuery('.vrc-is-roundtrip').show();
			}
		}

		jQuery(function() {
			jQuery('select[name="from"], select[name="to"]').select2();
		});
	</script>
	<input type="hidden" value="0" id="morevalueoverrides" />
	
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<div class="vrc-admin-container">
			<div class="vrc-config-maintab-left">
				<fieldset class="adminform">
					<div class="vrc-params-wrap">
						<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
						<div class="vrc-params-container">
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRC_LOCFEE_ONEWAY'); ?></div>
								<div class="vrc-param-setting">
									<?php echo $vrc_app->printYesNoButtons('any_oneway', JText::_('VRYES'), JText::_('VRNO'), (count($row) && intval($row['any_oneway']) == 1 ? 1 : 0), 1, 0, 'toggleOneWayFee(this.checked);'); ?>
									<span class="vrc-param-setting-comment"><?php echo JText::_('VRC_LOCFEE_ONEWAY_HELP'); ?></span>
								</div>
							</div>
							<div class="vrc-param-container vrc-is-roundtrip" style="<?php echo count($row) && $row['any_oneway'] ? 'display: none;' : ''; ?>">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWLOCFEEONE'); ?></div>
								<div class="vrc-param-setting"><?php echo $wsel; ?></div>
							</div>
							<div class="vrc-param-container vrc-is-roundtrip" style="<?php echo count($row) && $row['any_oneway'] ? 'display: none;' : ''; ?>">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWLOCFEETWO'); ?></div>
								<div class="vrc-param-setting"><?php echo $wseltwo; ?></div>
							</div>
							<div class="vrc-param-container vrc-is-roundtrip" style="<?php echo count($row) && $row['any_oneway'] ? 'display: none;' : ''; ?>">
								<div class="vrc-param-label"><?php echo JText::_('VRLOCFEEINVERT'); ?></div>
								<div class="vrc-param-setting">
									<?php echo $vrc_app->printYesNoButtons('invert', JText::_('VRYES'), JText::_('VRNO'), (count($row) && intval($row['invert']) == 1 ? 1 : 0), 1, 0); ?>
								</div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWLOCFEETHREE'); ?></div>
								<div class="vrc-param-setting"><?php echo $currencysymb; ?> <input type="number" step="any" name="cost" value="<?php echo count($row) ? (float)$row['cost'] : ''; ?>" style="width: 100px !important;"/></div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRNEWLOCFEEFOUR'); ?></div>
								<div class="vrc-param-setting">
									<?php echo $vrc_app->printYesNoButtons('daily', JText::_('VRYES'), JText::_('VRNO'), (count($row) && intval($row['daily']) == 1 ? 1 : 0), 1, 0); ?>
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
								<div class="vrc-param-label"><?php echo JText::_('VRNEWLOCFEEFIVE'); ?></div>
								<div class="vrc-param-setting"><?php echo $wiva; ?></div>
							</div>
							<div class="vrc-param-container">
								<div class="vrc-param-label"><?php echo JText::_('VRLOCFEECOSTOVERRIDE'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRLOCFEECOSTOVERRIDE'), 'content' => JText::_('VRLOCFEECOSTOVERRIDEHELP'))); ?></div>
								<div class="vrc-param-setting">
									<div id="myDiv" style="display: block;"><?php echo $actvalueoverrides; ?></div>
									<a class="btn vrc-config-btn" href="javascript: void(0);" onclick="addMoreOverrides();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRLOCFEECOSTOVERRIDEADD'); ?></a>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
		</div>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentcar" />
	<?php
	if (count($row)) {
		?>
		<input type="hidden" name="where" value="<?php echo (int)$row['id']; ?>">
		<?php
	}
	?>
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<?php
} else {
	?>
	<p class="warn"><a href="index.php?option=com_vikrentcar&amp;task=newplace"><?php echo JText::_('VRNOPLACESFOUND'); ?></a></p>
	<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentcar" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<?php
}
