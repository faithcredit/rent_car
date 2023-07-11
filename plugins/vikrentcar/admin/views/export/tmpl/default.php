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

$oids = $this->oids;
$locations = $this->locations;

JHTML::_('behavior.calendar');
$nowdf = VikRentCar::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$optlocations = '';
if (is_array($locations) && count($locations) > 0) {
	foreach ($locations as $loc) {
		$optlocations .= '<option value="'.$loc['id'].'">'.$loc['name'].'</option>';
	}
}
$xml_export = '<select name="xml_file">';
$xml_path = VRC_ADMIN_PATH.DS.'xml_export'.DS;
$xml_files = glob($xml_path.'*.xml.php');
foreach ($xml_files as $xml_file) {
	$xml_name = str_replace($xml_path, '', $xml_file);
	$xml_export .= '<option value="'.$xml_name.'">'.$xml_name.'</option>'."\n";
}
$xml_export .= '</select>';
?>
<script type="text/javascript">
jQuery.noConflict();
function vrcExportSetType(val) {
	if (val == 'csv') {
		document.getElementById('vrcexpdateftr').style.display = '';
	} else {
		jQuery('#vrcexpdateftr').fadeOut();
	}
	if (val == 'xml') {
		jQuery('#vrcexpxmlfile').fadeIn();
	} else {
		jQuery('#vrcexpxmlfile').fadeOut();
	}
}
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post">
<table class="admintable table">
<?php
if (!(count($oids) > 0)) {
?>
<tr><td class="vrc-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTDATETYPE'); ?></b> </td><td><select name="datetype"><option value="ritiro"><?php echo JText::_('VREXPORTDATETYPEPICK'); ?></option><option value="ts"><?php echo JText::_('VREXPORTDATETYPETS'); ?></option></select></td></tr>
<tr><td class="vrc-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTONE'); ?></b> </td><td><?php echo JHTML::_('calendar', '', 'from', 'from', $nowdf, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></td></tr>
<tr><td class="vrc-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTTWO'); ?></b> </td><td><?php echo JHTML::_('calendar', '', 'to', 'to', $nowdf, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></td></tr>
<tr><td class="vrc-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTELEVEN'); ?></b> </td><td><select name="location"><option value="">--------</option><?php echo $optlocations; ?></select></td></tr>
<?php
} else {
	foreach ($oids as $oid) {
		echo '<input type="hidden" name="cid[]" value="'.$oid.'"/>'."\n";
	}
	?>
<tr><td width="170" colspan="2"> <b><?php echo JText::sprintf('VREXPORTNUMORDS', count($oids)); ?></b></td></tr>
	<?php
}
?>
<tr><td class="vrc-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTTHREE'); ?></b> </td><td><select name="type" id="vrctype" onchange="vrcExportSetType(this.value);"><option value="csv"><?php echo JText::_('VREXPORTFOUR'); ?></option><option value="ics"><?php echo JText::_('VREXPORTFIVE'); ?></option><option value="xml"><?php echo JText::_('VREXPORTXML'); ?></option></select></td></tr>
<tr id="vrcexpxmlfile" style="display: none;"><td class="vrc-config-param-cell"> <b><?php echo JText::_('VREXPORTCHOOSEXML'); ?></b> </td><td><?php echo $xml_export; ?></td></tr>
<tr id="vrcexpdateftr" style=""><td class="vrc-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTTEN'); ?></b> </td><td><select name="dateformat"><option value="Y/m/d"<?php echo $df == 'Y/m/d' ? " selected=\"selected\"" : ""; ?>>Y/m/d</option><option value="m/d/Y"<?php echo $df == 'm/d/Y' ? " selected=\"selected\"" : ""; ?>>m/d/Y</option><option value="d/m/Y"<?php echo $df == 'd/m/Y' ? " selected=\"selected\"" : ""; ?>>d/m/Y</option><option value="Y-m-d">Y-m-d</option><option value="m-d-Y">m-d-Y</option><option value="d-m-Y">d-m-Y</option><option value="ts">Unix Timestamp</option></select></td></tr>
<tr><td class="vrc-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTSIX'); ?></b> </td><td><select name="status"><option value="C"><?php echo JText::_('VREXPORTSEVEN'); ?></option><option value="CP"><?php echo JText::_('VREXPORTEIGHT'); ?></option></select></td></tr>
<tr><td width="170">&nbsp;</td><td><button type="submit" class="btn"><i class="vrcicn-cloud-download"></i> <?php echo JText::_('VREXPORTNINE'); ?></button></td></tr>
</table>
<input type="hidden" name="task" value="doexport">
<input type="hidden" name="option" value="com_vikrentcar" />
</form>
