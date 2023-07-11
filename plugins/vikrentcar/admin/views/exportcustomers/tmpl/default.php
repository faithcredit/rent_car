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

$cid = $this->cid;
$countries = $this->countries;

$vrc_app = new VrcApplication();
$nowdf = VikRentCar::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$wselcountries = '<select name="country"><option value="">'.JText::_('VRCANYCOUNTRY').'</option>'."\n";
foreach ($countries as $key => $val) {
	$wselcountries .= '<option value="'.$val['country_3_code'].'">'.$val['country_name'].'</option>'."\n";
}
$wselcountries .= '</select>';
?>
<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
<?php
if (count($cid) > 0 && !empty($cid[0])) {
	?>
	<h4><?php echo JText::sprintf('VRCCUSTOMEREXPSEL', count($cid)); ?></h4>
	<?php
	foreach ($cid as $cust_id) {
		echo '<input type="hidden" name="cid[]" value="'.$cust_id.'" />'."\n";
	}
} else {
	?>
	<h4><?php echo JText::_('VRCCUSTOMEREXPALL'); ?></h4>
	<div class="vrc-export-customer-entry">
		<?php echo $vrc_app->getCalendar('', 'fromdate', 'fromdate', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'placeholder' => JText::_('VREXPORTONE'), 'todayBtn' => 'true')); ?>
		&nbsp;
		<?php echo $vrc_app->getCalendar('', 'todate', 'todate', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'placeholder' => JText::_('VREXPORTTWO'), 'todayBtn' => 'true')); ?>
		&nbsp;
		<select name="datefilt">
			<option value="1"><?php echo JText::_('VRPCHOOSEBUSYORDATE'); ?></option>
			<option value="2"><?php echo JText::_('VRCEXPCSVPICK'); ?></option>
			<option value="3"><?php echo JText::_('VRCEXPCSVDROP'); ?></option>
		</select>
	</div>
	<div class="vrc-export-customer-entry">
	<?php
	echo $wselcountries;
	?>
	</div>
	<?php
}
?>
	<div class="vrc-export-customer-entry">
		<span>
			<label for="donotes"><?php echo JText::_('VRCCUSTOMEREXPNOTES'); ?></label>
		</span>
		<input type="checkbox" name="notes" value="1" id="donotes" />
	</div>
	<div class="vrc-export-customer-entry">
		<span>
			<label for="dopin"><?php echo JText::_('VRCCUSTOMEREXPPIN'); ?></label>
		</span>
		<input type="checkbox" name="pin" value="1" id="dopin" />
	</div>
	<div class="vrc-export-customer-entry">
		<span>
			<label for="doscanimg"><?php echo JText::_('VRCCUSTOMEREXPSCANIMG'); ?></label>
		</span>
		<input type="checkbox" name="scanimg" value="1" id="doscanimg" />
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrentcar" />
</form>