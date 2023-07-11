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
echo $vrc_app->getJmodalHtml('vrc-tplfiles', JText::_('VRCONFIGEDITTMPLFILE'));
//
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$document = JFactory::getDocument();
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery.fancybox.css');
$document->addStyleSheet(VRC_ADMIN_URI.'resources/js/colorpicker.css');
JHtml::_('script', VRC_SITE_URI.'resources/jquery.fancybox.js');
JHtml::_('script', VRC_ADMIN_URI.'resources/js/colorpicker.js');
JHtml::_('script', VRC_ADMIN_URI.'resources/js/eye.js');
JHtml::_('script', VRC_ADMIN_URI.'resources/js/utils.js');

$themesel = '<select name="theme">';
$themesel .= '<option value="default">default</option>';
$themes = glob(VRC_SITE_PATH.DS.'themes'.DS.'*');
$acttheme = VikRentCar::getTheme();
if (count($themes) > 0) {
	$strip = VRC_SITE_PATH.DS.'themes'.DS;
	foreach ($themes as $th) {
		if (is_dir($th)) {
			$tname = str_replace($strip, '', $th);
			if ($tname != 'default') {
				$themesel .= '<option value="'.$tname.'"'.($tname == $acttheme ? ' selected="selected"' : '').'>'.$tname.'</option>';
			}
		}
	}
}
$themesel .= '</select>';
$firstwday = VikRentCar::getFirstWeekDay(true);
?>

<div class="vrc-config-maintab-left">
	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRCCONFIGAPPEARPART'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGFIRSTWDAY'); ?></div>
					<div class="vrc-param-setting">
						<select name="firstwday" style="float: none;">
							<option value="0"<?php echo $firstwday == '0' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSUNDAY'); ?></option>
							<option value="1"<?php echo $firstwday == '1' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCMONDAY'); ?></option>
							<option value="2"<?php echo $firstwday == '2' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCTUESDAY'); ?></option>
							<option value="3"<?php echo $firstwday == '3' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCWEDNESDAY'); ?></option>
							<option value="4"<?php echo $firstwday == '4' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCTHURSDAY'); ?></option>
							<option value="5"<?php echo $firstwday == '5' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCFRIDAY'); ?></option>
							<option value="6"<?php echo $firstwday == '6' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSATURDAY'); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHREETEN'); ?></div>
					<div class="vrc-param-setting"><input type="number" name="numcalendars" value="<?php echo VikRentCar::numCalendars(); ?>" min="0"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHUMBSIZE'); ?></div>
					<div class="vrc-param-setting"><input type="number" name="thumbswidth" value="<?php echo VikRentCar::getThumbnailsWidth(); ?>" min="0"/> px</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHREENINE'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('showpartlyreserved', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showPartlyReserved() ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHREESIX'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('showfooter', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showFooter() ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGEMAILTEMPLATE'); ?></div>
					<div class="vrc-param-setting">
						<div class="btn-wrapper input-append">
							<button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'email_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button>
							<button type="button" class="btn vrc-edit-tmpl vrc-preview-btn" title="<?php echo addslashes(JText::_('VRCPREVIEW')); ?>" data-prew-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'email_tmpl.php'); ?>"><?php VikRentCarIcons::e('eye'); ?></button>
						</div>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGPDFTEMPLATE'); ?></div>
					<div class="vrc-param-setting"><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'pdf_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGPDFCHECKINTEMPLATE'); ?></div>
					<div class="vrc-param-setting"><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'checkin_pdf_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGPDFINVOICETEMPLATE'); ?></div>
					<div class="vrc-param-setting"><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'helpers'.DS.'invoices'.DS.'invoice_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCCONFIGCUSTCSSTPL'); ?></div>
					<!-- @wponly  the path of the file is different in WP, it's inside /resources -->
					<div class="vrc-param-setting"><button type="button" class="btn vrc-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRC_SITE_PATH.DS.'resources'.DS.'vikrentcar_custom.css'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHEME'); ?></div>
					<div class="vrc-param-setting"><?php echo $themesel; ?></div>
				</div>
				<div class="vrc-param-container vrc-param-container-full">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHREESEVEN'); ?></div>
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
								echo $editor->display( "intromain", VikRentCar::getIntroMain(), 500, 350, 70, 20 );
							} catch (Throwable $t) {
								echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
							}
						} else {
							// we cannot catch Fatal Errors in PHP 5.x
							echo $editor->display( "intromain", VikRentCar::getIntroMain(), 500, 350, 70, 20 );
						}
						?>
					</div>
				</div>
				<div class="vrc-param-container vrc-param-container-full">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTHREEEIGHT'); ?></div>
					<div class="vrc-param-setting"><textarea name="closingmain" rows="5" cols="50"><?php echo VikRentCar::getClosingMain(); ?></textarea></div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<div class="vrc-config-maintab-right">
	
	<?php
	/**
	 * Preferred colors for CSS styling.
	 * 
	 * @since 	1.14.5 (J) - 1.2.0 (WP)
	 */
	$preferred_colors = VikRentCar::getPreferredColors();
	?>
	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend">
				<?php
				echo $vrc_app->createPopover(array('title' => JText::_('VRC_PREF_COLORS'), 'content' => JText::_('VRC_PREF_COLORS_HELP')));
				echo ' ' . JText::_('VRC_PREF_COLORS');
				?>
			</legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_PREF_COLOR_TEXTS'); ?></div>
					<div class="vrc-param-setting">
						<span class="vrc-inspector-colorpicker-wrap">
							<span class="vrc-inspector-colorpicker vrc-prefcolorpicker-trig" data-prefcolortype="textcolor" style="background-color: <?php echo !empty($preferred_colors['textcolor']) ? $preferred_colors['textcolor'] : '#ffffff'; ?>;"><?php VikRentCarIcons::e('palette'); ?></span>
						</span>
						<input type="hidden" name="pref_textcolor" id="vrc-pref-textcolor" value="<?php echo $preferred_colors['textcolor']; ?>" />
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_PREF_COLOR_BKGROUND'); ?></div>
					<div class="vrc-param-setting">
						<div class="vrc-param-setting-top">
							<span class="vrc-inspector-colorpicker-wrap">
								<span class="vrc-inspector-colorpicker vrc-prefcolorpicker-trig" data-prefcolortype="bgcolor" style="background-color: <?php echo !empty($preferred_colors['bgcolor']) ? $preferred_colors['bgcolor'] : '#ffffff'; ?>;"><?php VikRentCarIcons::e('palette'); ?></span>
							</span>
							<span class="vrc-colorpicker-label"><?php echo JText::_('VRC_BKGROUND_COL'); ?></span>
							<input type="hidden" name="pref_bgcolor" id="vrc-pref-bgcolor" value="<?php echo $preferred_colors['bgcolor']; ?>" />
						</div>
						<div class="vrc-param-setting-bottom">
							<span class="vrc-inspector-colorpicker-wrap">
								<span class="vrc-inspector-colorpicker vrc-prefcolorpicker-trig" data-prefcolortype="fontcolor" style="background-color: <?php echo !empty($preferred_colors['fontcolor']) ? $preferred_colors['fontcolor'] : '#ffffff'; ?>;"><?php VikRentCarIcons::e('palette'); ?></span>
							</span>
							<span class="vrc-colorpicker-label"><?php echo JText::_('VRC_FONT_COL'); ?></span>
							<input type="hidden" name="pref_fontcolor" id="vrc-pref-fontcolor" value="<?php echo $preferred_colors['fontcolor']; ?>" />
						</div>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_PREF_COLOR_BKGROUNDHOV'); ?></div>
					<div class="vrc-param-setting">
						<div class="vrc-param-setting-top">
							<span class="vrc-inspector-colorpicker-wrap">
								<span class="vrc-inspector-colorpicker vrc-prefcolorpicker-trig" data-prefcolortype="bgcolorhov" style="background-color: <?php echo !empty($preferred_colors['bgcolorhov']) ? $preferred_colors['bgcolorhov'] : '#ffffff'; ?>;"><?php VikRentCarIcons::e('palette'); ?></span>
							</span>
							<span class="vrc-colorpicker-label"><?php echo JText::_('VRC_BKGROUND_COL'); ?></span>
							<input type="hidden" name="pref_bgcolorhov" id="vrc-pref-bgcolorhov" value="<?php echo $preferred_colors['bgcolorhov']; ?>" />
						</div>
						<div class="vrc-param-setting-bottom">
							<span class="vrc-inspector-colorpicker-wrap">
								<span class="vrc-inspector-colorpicker vrc-prefcolorpicker-trig" data-prefcolortype="fontcolorhov" style="background-color: <?php echo !empty($preferred_colors['fontcolorhov']) ? $preferred_colors['fontcolorhov'] : '#ffffff'; ?>;"><?php VikRentCarIcons::e('palette'); ?></span>
							</span>
							<span class="vrc-colorpicker-label"><?php echo JText::_('VRC_FONT_COL'); ?></span>
							<input type="hidden" name="pref_fontcolorhov" id="vrc-pref-fontcolorhov" value="<?php echo $preferred_colors['fontcolorhov']; ?>" />
						</div>
					</div>
				</div>
				<div id="vrc-pref-color-examples" class="vrc-param-container" style="display: none;">
					<div class="vrc-param-label"><?php echo JText::_('VRC_PREF_COLOR_EXAMPLERES'); ?></div>
					<div class="vrc-param-setting">
						<div class="vrc-pref-color-example">
							<h4><?php echo JText::_('VRC_PREF_COLOR_TEXTS'); ?></h4>
						</div>
						<div class="vrc-pref-color-example">
							<button type="button" class="btn btn-small"><?php echo JText::_('VRC_PREF_COLOR_BKGROUND'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>

	<script type="text/javascript">

		var vrcHexDigits = new Array ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");

		function vrcRgb2Hex(rgb) {
			var rgb_match = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([0-9.]+))?\)$/);
			if (!rgb_match) {
				return rgb;
			}
			return "#" + vrcHex(rgb_match[1]) + vrcHex(rgb_match[2]) + vrcHex(rgb_match[3]);
		}

		function vrcHex(x) {
			return isNaN(x) ? "00" : vrcHexDigits[(x - x % 16) / 16] + vrcHexDigits[x % 16];
		}
		
		/**
		 * Composes the necessary inline style tag for the styles examples.
		 */
		function vrcPrefColorsApplyExample() {
			// compose inline style tag
			var style = '';
			style += '<style type="text/css" id="vrc-pref-color-livecss">';
			style += '.vrc-pref-color-example h4 { color: ' + jQuery('#vrc-pref-textcolor').val() + '; }' + "\n";
			style += '.vrc-pref-color-example button { background-color: ' + jQuery('#vrc-pref-bgcolor').val() + '; color: ' + jQuery('#vrc-pref-fontcolor').val() + '; }' + "\n";
			style += '.vrc-pref-color-example button:hover { background-color: ' + jQuery('#vrc-pref-bgcolorhov').val() + '; color: ' + jQuery('#vrc-pref-fontcolorhov').val() + '; }' + "\n";
			style += '</style>';
			if (jQuery('#vrc-pref-color-livecss').length) {
				// remove current inline style tag
				jQuery('#vrc-pref-color-livecss').remove();
			}
			// append styling to example window and show it
			jQuery('#vrc-pref-color-examples').append(style).show();
		}

		jQuery(document).ready(function() {
			/**
			 * Register color-picker for preferred colors.
			 */
			jQuery('.vrc-prefcolorpicker-trig').ColorPicker({
				color: '#ffffff',
				onShow: function(colpkr, el) {
					var cur_color = jQuery(el).css('backgroundColor');
					jQuery(el).ColorPickerSetColor(vrcRgb2Hex(cur_color));
					jQuery(colpkr).show();
					return false;
				},
				onChange: function(hsb, hex, rgb, el) {
					var element = jQuery(el);
					var el_type = element.attr('data-prefcolortype');
					element.css('backgroundColor', '#'+hex);
					if (el_type && jQuery('#vrc-pref-' + el_type).length) {
						jQuery('#vrc-pref-' + el_type).val('#'+hex);
					}
					vrcPrefColorsApplyExample();
				},
				onSubmit: function(hsb, hex, rgb, el) {
					var element = jQuery(el);
					var el_type = element.attr('data-prefcolortype');
					element.css('backgroundColor', '#'+hex);
					if (el_type && jQuery('#vrc-pref-' + el_type).length) {
						jQuery('#vrc-pref-' + el_type).val('#'+hex);
					}
					element.ColorPickerHide();
					vrcPrefColorsApplyExample();
				}
			});
		});

	</script>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".vrc-edit-tmpl").click(function() {
		var vrc_tmpl_path = jQuery(this).attr("data-tmpl-path");
		var vrc_prew_path = jQuery(this).attr("data-prew-path");
		if (!vrc_tmpl_path && !vrc_prew_path) {
			return;
		}
		var basetask = !vrc_tmpl_path ? 'tmplfileprew' : 'edittmplfile';
		var basepath = !vrc_tmpl_path ? vrc_prew_path : vrc_tmpl_path;
		// @wponly - we use the BS's Modal to open the template files editing page
		vrcOpenJModal('vrc-tplfiles', "index.php?option=com_vikrentcar&task=" + basetask + "&path=" + basepath + "&tmpl=component");
	});
});
</script>
