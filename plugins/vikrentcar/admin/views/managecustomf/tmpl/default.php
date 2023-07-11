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

$choose = "";
if (count($row) && $row['type'] == "select") {
	$x = explode(";;__;;", $row['choose']);
	if (@count($x) > 0) {
		foreach ($x as $y) {
			if (!empty($y)) {
				$choose .= '<div class="vrc-customf-sel-added"><input type="text" name="choose[]" value="'.$y.'" size="40"/></div>'."\n";
			}
		}
	}
}
?>
<script type="text/javascript">
function setCustomfChoose (val) {
	if (val == "text") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrflag').style.display = 'flex';
	}
	if (val == "textarea") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrflag').style.display = 'none';
	}
	if (val == "checkbox") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrflag').style.display = 'none';
	}
	if (val == "date") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrflag').style.display = 'none';
	}
	if (val == "select") {
		document.getElementById('customfchoose').style.display = 'block';
		document.getElementById('vrflag').style.display = 'none';
	}
	if (val == "country") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrflag').style.display = 'none';
	}
	if (val == "separator") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrflag').style.display = 'none';
	}
	return true;
}
function addElement() {
	var ni = document.getElementById('customfchooseadd');
	var numi = document.getElementById('theValue');
	var num = (document.getElementById('theValue').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<div class=\'vrc-customf-sel-added\'><input type=\'text\' name=\'choose[]\' value=\'\' size=\'40\'/></div>';
	ni.appendChild(newdiv);
}
</script>
<input type="hidden" value="0" id="theValue" />

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCUSTOMFONE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="name" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCUSTOMFTWO'); ?></div>
							<div class="vrc-param-setting">
								<select id="stype" name="type" onchange="setCustomfChoose(this.value);">
									<option value="text"<?php echo (count($row) && $row['type'] == "text" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFTHREE'); ?></option>
									<option value="textarea"<?php echo (count($row) && $row['type'] == "textarea" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFTEN'); ?></option>
									<option value="select"<?php echo (count($row) && $row['type'] == "select" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFFOUR'); ?></option>
									<option value="checkbox"<?php echo (count($row) && $row['type'] == "checkbox" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFFIVE'); ?></option>
									<option value="date"<?php echo (count($row) && $row['type'] == "date" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFDATETYPE'); ?></option>
									<option value="country"<?php echo (count($row) && $row['type'] == "country" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCNEWCUSTOMFCOUNTRY'); ?></option>
									<option value="separator"<?php echo (count($row) && $row['type'] == "separator" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFSEPARATOR'); ?></option>
								</select>
								<div id="customfchoose" style="display: <?php echo (count($row) && $row['type'] == "select" ? "block" : "none"); ?>;">
									<?php
									if ((count($row) && $row['type'] != "select") || !count($row)) {
									?>
									<div class="vrc-customf-sel-added"><input type="text" name="choose[]" value="" size="40"/></div>
									<?php
									} else {
										echo $choose;
									}
									?>
									<div id="customfchooseadd" style="display: block;"></div>
									<span><b><a href="javascript: void(0);" onclick="javascript: addElement();"><?php echo JText::_('VRNEWCUSTOMFNINE'); ?></a></b></span>
								</div>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCUSTOMFSIX'); ?></div>
							<div class="vrc-param-setting">
								<?php echo $vrc_app->printYesNoButtons('required', JText::_('VRYES'), JText::_('VRNO'), (count($row) && intval($row['required']) == 1 ? 1 : 0), 1, 0); ?>
							</div>
						</div>
						<div class="vrc-param-container" id="vrflag"<?php echo (count($row) && $row['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCUSTOMFFLAG'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRNEWCUSTOMFFLAG'), 'content' => JText::_('VRNEWCUSTOMFFLAGHELP'))); ?></div>
							<div class="vrc-param-setting">
								<select name="flag">
									<option value=""></option>
									<option value="isemail"<?php echo (count($row) && intval($row['isemail']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFSEVEN'); ?></option>
									<option value="isnominative"<?php echo (count($row) && intval($row['isnominative']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCISNOMINATIVE'); ?></option>
									<option value="isphone"<?php echo (count($row) && intval($row['isphone']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCISPHONENUMBER'); ?></option>
									<option value="isaddress"<?php echo (count($row) && stripos($row['flag'], 'address') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCISADDRESS'); ?></option>
									<option value="iscity"<?php echo (count($row) && stripos($row['flag'], 'city') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCISCITY'); ?></option>
									<option value="iszip"<?php echo (count($row) && stripos($row['flag'], 'zip') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCISZIP'); ?></option>
									<option value="iscompany"<?php echo (count($row) && stripos($row['flag'], 'company') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCISCOMPANY'); ?></option>
									<option value="isvat"<?php echo (count($row) && stripos($row['flag'], 'vat') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCISVAT'); ?></option>
								</select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWCUSTOMFEIGHT'); ?></div>
							<div class="vrc-param-setting">
								<input type="text" name="poplink" value="<?php echo count($row) ? JHtml::_('esc_attr', $row['poplink']) : ''; ?>" size="40"/>
								<br/>
								<!-- @wponly we suggest to use a permalink -->
								<small>Eg. <i><?php echo get_site_url(); ?>/link-to-your-terms-page</i></small>
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
