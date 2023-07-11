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

$vrc_app = VikRentCar::getVrcApplication();
/**
 * @wponly - cannot load iFrame with FancyBox, so we use the BS's Modal
 */
if (function_exists('wp_enqueue_code_editor')) {
	// WP >= 4.9.0
	wp_enqueue_code_editor(array('type' => 'php'));
}
$vrc_app->getJmodalScript();
echo $vrc_app->getJmodalHtml('vrc-trktplfiles', JText::_('VRCONFIGEDITTMPLFILE'));
//
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$sitelogo = VikRentCar::getSiteLogo();
$backlogo = VikRentCar::getBackendLogo();
$attachical = VikRentCar::attachIcal();
?>

<div class="vrc-config-maintab-left">
	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRPANELFOUR'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHREEONE'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="fronttitle" value="<?php echo JHtml::_('esc_attr', VikRentCar::getFrontTitle()); ?>" size="30"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGFOURLOGO'); ?></div>
					<div class="vrc-param-setting">
						<div class="vrc-param-setting-block">
							<?php echo (!empty($sitelogo) ? "<a href=\"".VRC_ADMIN_URI."resources/".$sitelogo."\" target=\"_blank\" class=\"vrcmodal vrc-car-img-modal\"><i class=\"" . VikRentCarIcons::i('image') . "\"></i>" . $sitelogo . "</a>" : ""); ?>
							<input type="file" name="sitelogo" size="35"/>
						</div>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGLOGOBACKEND'); ?></div>
					<div class="vrc-param-setting">
						<div class="vrc-param-setting-block">
						<?php
						if (!empty($backlogo)) {
							?>
							<a href="<?php echo VRC_ADMIN_URI . "resources/{$backlogo}"; ?>" target="_blank" class="vrcmodal vrc-car-img-modal"><?php VikRentCarIcons::e('image'); ?> <?php echo $backlogo; ?></a>
							<?php
						} else {
							?>
							<a href="<?php echo VRC_ADMIN_URI . "vikrentcar.png"; ?>" target="_blank" class="vrcmodal vrc-car-img-modal"><?php VikRentCarIcons::e('image'); ?> vikrentcar.png</a>
							<?php
						}
						?>
							<input type="file" name="backlogo" size="35"/>
						</div>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCSENDPDF'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('sendpdf', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::sendPDF() ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCSENDEMAILSWHEN'); ?></div>
					<div class="vrc-param-setting">
						<?php
						$sendwhen = VikRentCar::getSendEmailWhen();
						?>
						<select name="sendemailwhen">
							<option value="1"<?php echo $sendwhen < 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSENDEMAILSWHENBOTH'); ?></option>
							<option value="2"<?php echo $sendwhen > 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSENDEMAILSWHENCONF'); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCICALEVENDDTTYPE'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCICALEVENDDTTYPE'), 'content' => JText::_('VRCICALEVENDDTTYPEHELP'))); ?></div>
					<div class="vrc-param-setting">
						<?php
						$icalendtype = VikRentCar::getIcalEndType();
						?>
						<select name="icalendtype">
							<option value="pick"<?php echo $icalendtype == 'pick' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCICALEVENDDTPICK'); ?></option>
							<option value="drop"<?php echo $icalendtype == 'drop' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCICALEVENDDTDROP'); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGATTACHICAL'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCONFIGATTACHICAL'), 'content' => JText::_('VRCONFIGATTACHICALHELP'))); ?></div>
					<div class="vrc-param-setting">
						<select name="attachical">
							<option value="1"<?php echo $attachical === 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGSENDTOADMIN') . ' + ' . JText::_('VRCONFIGSENDTOCUSTOMER'); ?></option>
							<option value="2"<?php echo $attachical === 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGSENDTOADMIN'); ?></option>
							<option value="3"<?php echo $attachical === 3 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGSENDTOCUSTOMER'); ?></option>
							<option value="0"<?php echo $attachical === 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRNO'); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTRACKCODETEMPLATE'); ?></div>
					<!-- @wponly  we use a different class for the tracking code template files -->
					<div class="vrc-param-setting"><button type="button" class="btn vrc-edit-trktmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'tracking_code_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGCONVCODETEMPLATE'); ?></div>
					<!-- @wponly  we use a different class for the tracking code template files -->
					<div class="vrc-param-setting"><button type="button" class="btn vrc-edit-trktmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'conversion_code_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGFOURFOUR'); ?></div>
					<div class="vrc-param-setting"><textarea name="disclaimer" rows="7" cols="50"><?php echo VikRentCar::getDisclaimer(); ?></textarea></div>
				</div>
				<div class="vrc-param-container vrc-param-container-full">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGFOURORDMAILFOOTER'); ?></div>
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
								echo $editor->display( "footerordmail", VikRentCar::getFooterOrdMail(), 500, 350, 70, 20 );
							} catch (Throwable $t) {
								echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
							}
						} else {
							// we cannot catch Fatal Errors in PHP 5.x
							echo $editor->display( "footerordmail", VikRentCar::getFooterOrdMail(), 500, 350, 70, 20 );
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<div class="vrc-config-maintab-right">
	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRCCUSTOMERDOCUMENTS'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_ALLOW_DOCS_UPLOAD'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRC_ALLOW_DOCS_UPLOAD'), 'content' => JText::_('VRC_ALLOW_DOCS_UPLOAD_HELP'))); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('docsupload', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::allowDocsUpload() ? 1 : 0), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container vrc-param-container-full">
					<div class="vrc-param-label"><?php echo JText::_('VRC_ALLOW_DOCS_UPLOAD_INSTR'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRC_ALLOW_DOCS_UPLOAD_INSTR'), 'content' => JText::_('VRC_ALLOW_DOCS_UPLOAD_INSTR_HELP'))); ?></div>
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
								echo $editor->display( "docsuploadinstr", VikRentCar::docsUploadInstructions(), 500, 350, 70, 20 );
							} catch (Throwable $t) {
								echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
							}
						} else {
							// we cannot catch Fatal Errors in PHP 5.x
							echo $editor->display( "docsuploadinstr", VikRentCar::docsUploadInstructions(), 500, 350, 70, 20 );
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".vrc-edit-trktmpl").click(function() {
		var vrc_tmpl_path = jQuery(this).attr("data-tmpl-path");
		// @wponly - we use the BS's Modal to open the template files editing page
		vrcOpenJModal('vrc-trktplfiles', "index.php?option=com_vikrentcar&task=edittmplfile&path="+vrc_tmpl_path+"&tmpl=component");
	});
});
</script>
