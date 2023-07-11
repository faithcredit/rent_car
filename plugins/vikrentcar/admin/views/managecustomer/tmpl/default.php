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

$customer = $this->customer;

if (!class_exists('JHtmlList')) {
	jimport( 'joomla.html.html.list' );
}
$df = VikRentCar::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$vrc_app = new VrcApplication();
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VRC_SITE_URI.'resources/jquery.fancybox.js');
$ptmpl = VikRequest::getString('tmpl', '', 'request');
$pcheckin = VikRequest::getInt('checkin', '', 'request');
$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
$pbid = VikRequest::getInt('bid', '', 'request');
?>
<script type="text/Javascript">
function getRandomPin(min, max) {
	return Math.floor(Math.random() * (max - min)) + min;
}
function generatePin() {
	var pin = getRandomPin(10999, 99999);
	document.getElementById('pin').value = pin;
}
jQuery(document).ready(function() {
	jQuery(document.body).on("click", ".vrc-cur-idscan a", function(e) {
		e.preventDefault();
		var imgsrc = jQuery(this).attr("href");
		jQuery.fancybox.open({
			src: imgsrc,
			type: 'image'
		});
	});
<?php
if (count($customer) && !empty($customer['bdate'])) {
	?>
	jQuery("#bdate").val("<?php echo $customer['bdate']; ?>").attr('data-alt-value', "<?php echo $customer['bdate']; ?>");
	<?php
}
if (count($customer) && !empty($customer['country']) && empty($customer['phone'])) {
	?>
	jQuery('select[name="country"]').trigger('change');
	<?php
}
?>
});
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCCUSTOMERDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERFIRSTNAME'); ?> <sup>*</sup></div>
							<div class="vrc-param-setting"><input type="text" name="first_name" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['first_name']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERLASTNAME'); ?> <sup>*</sup></div>
							<div class="vrc-param-setting"><input type="text" name="last_name" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['last_name']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERCOMPANY'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="company" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['company']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERCOMPANYVAT'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="vat" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['vat']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMEREMAIL'); ?> <sup>*</sup></div>
							<div class="vrc-param-setting"><input type="text" name="email" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['email']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERPHONE'); ?> <sup>*</sup></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->printPhoneInputField(array('name' => 'phone', 'id' => 'vrc-phone', 'value' => (count($customer) ? $customer['phone'] : ''))); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERADDRESS'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="address" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['address']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERCITY'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="city" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['city']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERZIP'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="zip" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['zip']) : ''; ?>" size="6"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERCOUNTRY'); ?> <sup>*</sup></div>
							<div class="vrc-param-setting">
								<select name="country" onchange="jQuery('#vrc-phone').trigger('vrcupdatephonenumber', jQuery(this).find('option:selected').attr('data-c2code'));">
									<option value="" data-c2code=""></option>
								<?php
								foreach ($this->countries as $country) {
									?>
									<option data-c2code="<?php echo JHtml::_('esc_attr', $country['country_2_code']); ?>" value="<?php echo JHtml::_('esc_attr', $country['country_3_code']); ?>"<?php echo count($customer) && $customer['country'] == $country['country_3_code'] ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $country['country_name']); ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label<?php echo !empty($pcheckin) && !empty($pbid) && empty($customer['gender']) ? ' vrc-config-param-cell-warn' : ''; ?>"><?php echo JText::_('VRCUSTOMERGENDER'); ?></div>
							<div class="vrc-param-setting">
								<select name="gender">
									<option value=""></option>
									<option value="M"<?php echo count($customer) && $customer['gender'] == 'M' ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', JText::_('VRCUSTOMERGENDERM')); ?></option>
									<option value="F"<?php echo count($customer) && $customer['gender'] == 'F' ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', JText::_('VRCUSTOMERGENDERF')); ?></option>
								</select>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label<?php echo count($customer) && !empty($pcheckin) && !empty($pbid) && empty($customer['bdate']) ? ' vrc-config-param-cell-warn' : ''; ?>"><?php echo JText::_('VRCUSTOMERBDATE'); ?></div>
							<div class="vrc-param-setting"><?php echo $vrc_app->getCalendar('', 'bdate', 'bdate', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label<?php echo count($customer) && !empty($pcheckin) && !empty($pbid) && empty($customer['pbirth']) ? ' vrc-config-param-cell-warn' : ''; ?>"><?php echo JText::_('VRCUSTOMERPBIRTH'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="pbirth" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['pbirth']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERDOCTYPE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="doctype" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['doctype']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERDOCNUM'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="docnum" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['docnum']) : ''; ?>" size="15"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERDOCIMG'); ?></div>
							<div class="vrc-param-setting">
								<input type="file" name="docimg" id="docimg" size="30" />
								<input type="hidden" name="scandocimg" id="scandocimg" value="" />
								<div class="vrc-cur-idscan">
							<?php
							if (count($customer) && !empty($customer['docimg'])) {
								?>
								<?php VikRentCarIcons::e('eye'); ?> <a href="<?php echo VRC_ADMIN_URI.'resources/idscans/'.$customer['docimg']; ?>" target="_blank"><?php echo $customer['docimg']; ?></a>
								<?php
							}
							?>
								</div>
							</div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERPIN'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="pin" id="pin" value="<?php echo count($customer) ? JHtml::_('esc_attr', $customer['pin']) : ''; ?>" size="6" placeholder="54321" /> &nbsp;&nbsp; <button type="button" class="btn vrc-config-btn" onclick="generatePin();" style="vertical-align: top;"><?php echo JHtml::_('esc_html', JText::_('VRCUSTOMERGENERATEPIN')); ?></button></div>
						</div>
						<!-- @wponly the user label is called statically 'Website User' and we use JHtml -->
						<div class="vrc-param-container">
							<div class="vrc-param-label">Website User</div>
							<div class="vrc-param-setting"><?php echo JHtml::_('list.users', 'ujid', (count($customer) ? $customer['ujid'] : ''), 1); ?></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRCUSTOMERNOTES'); ?></div>
							<div class="vrc-param-setting"><textarea cols="80" rows="5" name="notes" style="width: 400px; height: 130px;"><?php echo count($customer) ? htmlspecialchars($customer['notes']) : ''; ?></textarea></div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	<?php
	// display dropfiles area only for existing users
	if (!empty($customer['id'])) {
		?>
		<div class="vrc-config-maintab-right">
			<?php echo $this->loadTemplate('dropfiles'); ?>
		</div>
		<?php
	}
	?>
	</div>
	<?php
if ($ptmpl == 'component') {
	?>
	<input type="hidden" name="tmpl" value="<?php echo JHtml::_('esc_attr', $ptmpl); ?>">
	<?php
}
if (!empty($pcheckin) && !empty($pbid)) {
	?>
	<input type="hidden" name="checkin" value="<?php echo JHtml::_('esc_attr', $pcheckin); ?>">
	<input type="hidden" name="bid" value="<?php echo JHtml::_('esc_attr', $pbid); ?>">
	<?php
}
if (!empty($pbid)) {
	?>
	<input type="hidden" name="bid" value="<?php echo JHtml::_('esc_attr', $pbid); ?>">
	<?php
}
if (count($customer)) {
	?>
	<input type="hidden" name="where" value="<?php echo (int)$customer['id']; ?>">
	<?php
}
if (!empty($pgoto)) {
	?>
	<input type="hidden" name="goto" value="<?php echo JHtml::_('esc_attr', $pgoto); ?>">
	<?php
}
?>
	<input type="hidden" name="task" value="<?php echo count($customer) ? 'updatecustomer' : 'savecustomer'; ?>">
	<input type="hidden" name="option" value="com_vikrentcar">
	<?php echo JHtml::_('form.token'); ?>
</form>
