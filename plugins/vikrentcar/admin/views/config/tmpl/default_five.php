<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2022 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$dbo = JFactory::getDbo();
$app = JFactory::getApplication();
$vrc_app = VikRentCar::getVrcApplication();

VikRentCar::getConditionalRulesInstance(true);
$tpl_names = VikRentCarHelperConditionalRules::getTemplateFilesNames();
$tpl_contents = VikRentCarHelperConditionalRules::getTemplateFilesContents();

$lim = $app->getUserStateFromRequest("com_vikrentcar.limit", 'limit', $app->get('list_limit'), 'int');
$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');

// load lang vars for JS
JText::script('VRCDELCONFIRM');
JText::script('VRCONFIGEDITTMPLFILE');
JText::script('VRC_CONDTEXT_TAG_ADD_HELP');
JText::script('VRC_CONDTEXT_TAG_RM_HELP');
JText::script('VRC_CSS_EDITING_HELP');
JText::script('VRC_INSPECTOR_START');
JText::script('VRC_EDITTPL_FATALERROR');

$rows = array();
$navbut = "";

$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikrentcar_condtexts` ORDER BY `#__vikrentcar_condtexts`.`lastupd` DESC";
$dbo->setQuery($q, $lim0, $lim);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$rows = $dbo->loadAssocList();
	$dbo->setQuery('SELECT FOUND_ROWS();');
	jimport('joomla.html.pagination');
	$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
	$navbut = "<table align=\"center\"><tr><td>".$pageNav->getPagesLinks()."</td></tr></table>";
}

?>
<div class="vrc-config-maintab-top">
	<fieldset class="adminform vrc-config-fieldset-large">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRC_COND_TEXT_RULES'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-setting">
						<a href="index.php?option=com_vikrentcar&task=newcondtext" class="btn vrc-config-btn"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_NEW_COND_TEXT'); ?></a>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-setting">
						<div class="table-responsive vrc-list-table-rounded">
							<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vrc-list-table">
								<thead>
									<tr>
										<th class="title left" width="200"><?php echo JText::_('VRC_CONDTEXT_NAME'); ?></th>
										<th class="title left" width="200"><?php echo JText::_('VRC_CONDTEXT_TKN'); ?></th>
										<th class="title center" width="100"><?php echo JText::_('VRC_WIDGETS_LASTUPD'); ?></th>
										<th class="title center" width="300"><?php echo JText::_('VRC_TEMPLATE_FILES'); ?></th>
										<th class="title center" width="100">&nbsp;</th>
									</tr>
								</thead>
							<?php
							$k = 0;
							$i = 0;
							for ($i = 0, $n = count($rows); $i < $n; $i++) {
								$row = $rows[$i];
								?>
								<tr class="row<?php echo $k; ?>">
									<td>
										<a href="index.php?option=com_vikrentcar&amp;task=editcondtext&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
									</td>
									<td>
										<span class="vrc-condtext-token-full"><?php echo $row['token']; ?></span>
									</td>
									<td class="center"><?php echo $row['lastupd']; ?></td>
									<td class="center">
									<?php
									foreach ($tpl_names as $file => $name) {
										$tag_used = VikRentCarHelperConditionalRules::isTagInContent($row['token'], $tpl_contents[$file]);
										$btn_icon = $tag_used ? VikRentCarIcons::i('check-circle') : VikRentCarIcons::i('plus');
										$btn_classes = array(
											'btn',
											'vrc-condtext-tmpl-status',
										);
										if ($tag_used) {
											array_push($btn_classes, 'btn-success');
											array_push($btn_classes, 'vrc-condtext-intmpl');
										} else {
											array_push($btn_classes, 'btn-secondary');
											array_push($btn_classes, 'vrc-condtext-notintmpl');
										}
										?>
										<button type="button" class="<?php echo implode(' ', $btn_classes); ?>" data-inspectfile="<?php echo $file; ?>" data-specialtag="<?php echo $row['token']; ?>"><i class="<?php echo $btn_icon; ?>"></i> <?php echo $name; ?></button>
										<?php
									}
									?>
									</td>
									<td class="center">
										<a href="index.php?option=com_vikrentcar&amp;task=removecondtext&amp;cid[]=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm(Joomla.JText._('VRCDELCONFIRM'));"><?php VikRentCarIcons::e('trash'); ?> <?php echo JText::_('VRCMAINCRONDEL'); ?></a>
									</td>
								</tr>	
								<?php
								$k = 1 - $k;
							}
							?>
							</table>
							<?php echo $navbut; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset class="adminform vrc-config-fieldset-small">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGEMAILTEMPLATE'); ?></div>
					<div class="vrc-param-setting">
						<button type="button" class="btn vrc-inspector-btn" title="<?php echo addslashes(JText::_('VRC_INSPECTOR_START')); ?>" data-inspectfile="email_tmpl.php"><?php VikRentCarIcons::e('paint-brush'); ?></button>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGPDFTEMPLATE'); ?></div>
					<div class="vrc-param-setting">
						<button type="button" class="btn vrc-inspector-btn" title="<?php echo addslashes(JText::_('VRC_INSPECTOR_START')); ?>" data-inspectfile="pdf_tmpl.php"><?php VikRentCarIcons::e('paint-brush'); ?></button>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGPDFCHECKINTEMPLATE'); ?></div>
					<div class="vrc-param-setting">
						<button type="button" class="btn vrc-inspector-btn" title="<?php echo addslashes(JText::_('VRC_INSPECTOR_START')); ?>" data-inspectfile="checkin_pdf_tmpl.php"><?php VikRentCarIcons::e('paint-brush'); ?></button>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGPDFINVOICETEMPLATE'); ?></div>
					<div class="vrc-param-setting">
						<button type="button" class="btn vrc-inspector-btn" title="<?php echo addslashes(JText::_('VRC_INSPECTOR_START')); ?>" data-inspectfile="invoice_tmpl.php"><?php VikRentCarIcons::e('paint-brush'); ?></button>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_CONT_WRAPPER'); ?></div>
					<div class="vrc-param-setting">
						<button type="button" class="btn vrc-contwrapper-btn" onclick="jQuery('.vrc-contwrapper-edit').toggle();"><?php VikRentCarIcons::e('code'); ?></button>
					</div>
				</div>
				<div class="vrc-param-container vrc-param-nested vrc-contwrapper-edit" style="display: none;">
					<?php
					$cont_wrapper_opening = VRCMailParser::getWrapperLayout($opening = true);
					$cont_wrapper_closing = VRCMailParser::getWrapperLayout($opening = false);
					?>
					<div class="vrc-param-setting">
						<textarea id="vrc-contwrapper-html" style="width: 100% !important; min-height: 250px;"><?php echo JHtml::_('esc_textarea', $cont_wrapper_opening . "\n\n\t\t--- " . JText::_('VRC_TPL_TEXT') . " ---\n\n" . $cont_wrapper_closing); ?></textarea>
						<div class="vrc-param-setting-comment"><?php echo JText::_('VRC_CONT_WRAPPER_HELP'); ?></div>
					</div>
				</div>
				<div class="vrc-param-container vrc-param-nested vrc-contwrapper-edit" style="display: none;">
					<div class="vrc-param-setting">
						<button id="vrc-contwrapper-save" type="button" class="btn btn-success"><?php VikRentCarIcons::e('save'); ?> <?php echo JText::_('VRSAVE'); ?></button>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<div id="vrc-inspector-outer" class="vrc-config-maintab-bottom" style="display: none;">
	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><span id="vrc-inspector-title"></span></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-setting">
						<div class="vrc-inspector-cmds">
							<button id="vrc-inspector-save" type="button" class="btn btn-success" disabled><?php VikRentCarIcons::e('save'); ?> <?php echo JText::_('VRSAVE'); ?></button>
							<button id="vrc-inspector-cancel" type="button" class="btn btn-secondary"><?php VikRentCarIcons::e('times'); ?> <?php echo JText::_('VRANNULLA'); ?></button>
						</div>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-setting">
						<p id="vrc-inspector-help" class="info notice-noicon"></p>
					</div>
				</div>
				<div class="vrc-param-container vrc-inspector-css-param" style="display: none;">
					<div class="vrc-param-label"><?php echo JText::_('VRC_INSP_HTML_TAG'); ?></div>
					<div class="vrc-param-setting">
						<span id="vrc-inspector-css-tag" class="vrc-inspector-css-tag"></span>
					</div>
				</div>
				<div class="vrc-param-container vrc-inspector-css-param" style="display: none;">
					<div class="vrc-param-label"><?php echo JText::_('VRC_INSP_CSS_FONTCOLOR'); ?></div>
					<div class="vrc-param-setting">
						<span class="vrc-inspector-colorpicker-wrap">
							<span id="vrc-inspector-css-color" class="vrc-inspector-colorpicker vrc-inspector-colorpicker-trig"><?php VikRentCarIcons::e('palette'); ?></span>
						</span>
					</div>
				</div>
				<div class="vrc-param-container vrc-inspector-css-param" style="display: none;">
					<div class="vrc-param-label"><?php echo JText::_('VRC_INSP_CSS_BACKGCOLOR'); ?></div>
					<div class="vrc-param-setting">
						<span class="vrc-inspector-colorpicker-wrap">
							<span id="vrc-inspector-css-backgcolor" class="vrc-inspector-colorpicker vrc-inspector-colorpicker-trig"><?php VikRentCarIcons::e('palette'); ?></span>
						</span>
					</div>
				</div>
				<div class="vrc-param-container vrc-inspector-css-param" style="display: none;">
					<div class="vrc-param-label"><?php echo JText::_('VRC_INSP_CSS_BORDER'); ?></div>
					<div class="vrc-param-setting vrc-toggle-small">
						<?php echo $vrc_app->printYesNoButtons('vrc-inspector-css-border', JText::_('VRYES'), JText::_('VRNO'), 0, 1, 0, 'vrcToggleCSSBorderParams();'); ?>
					</div>
				</div>
				<div class="vrc-param-container vrc-inspector-css-border-param" style="display: none;">
					<div class="vrc-param-label"><?php echo JText::_('VRC_INSP_CSS_BORDERWIDTH'); ?> (px)</div>
					<div class="vrc-param-setting">
						<input type="number" min="0" id="vrc-inspector-css-borderwidth" value="0" />
					</div>
				</div>
				<div class="vrc-param-container vrc-inspector-css-border-param" style="display: none;">
					<div class="vrc-param-label"><?php echo JText::_('VRC_INSP_CSS_BORDERCOLOR'); ?></div>
					<div class="vrc-param-setting">
						<span class="vrc-inspector-colorpicker-wrap">
							<span id="vrc-inspector-css-bordercolor" class="vrc-inspector-colorpicker vrc-inspector-colorpicker-trig"><?php VikRentCarIcons::e('palette'); ?></span>
						</span>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-setting">
						<div class="vrc-inspector-wrap" data-inspectfile=""></div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<?php
foreach ($tpl_contents as $file => $content) {
	?>
<div class="vrc-tplcontent-inspector-ghost" style="display: none;" data-inspectfile="<?php echo $file; ?>">
	<?php echo $content; ?>
</div>
	<?php
}
?>

<a class="vrc-reload-plchld-link" href="index.php?option=com_vikrentcar&task=config" style="display: none;">&nbsp;</a>

<script type="text/javascript">
	/**
	 * Define global scope vars.
	 */
	var tpl_names = <?php echo json_encode($tpl_names); ?>;
	var current_tag = null,
		current_file = null,
		current_target = null,
		css_editing = false,
		css_custom_classes = [];

	/**
	 * Generates a unique class for the target in the CSS inspector.
	 */
	function vrcTargetUniqueClassName() {
		var now = new Date;
		return 'vrc-inspector-custom-' + now.getTime();
	}

	/**
	 * Adds the temporary class of the current target to the pool.
	 * Should be called when target's CSS properties have been modified.
	 * All classes in the pool may be processed via PHP for update.
	 */
	function vrcCSSTargetChanged() {
		if (current_target === null) {
			return false;
		}
		var tmpclass = current_target.data('tmpclass');
		if (!tmpclass) {
			return false;
		}
		if (css_custom_classes.indexOf(tmpclass) < 0) {
			// push custom/temporary CSS rule
			css_custom_classes.push(tmpclass);
			// enable save button
			jQuery('#vrc-inspector-save').prop('disabled', false);
		}
	}

	/**
	 * Unbinds the hover events for the inspector.
	 */
	function vrcUnregisterInspector() {
		jQuery('.vrc-inspector-wrap').find('*').not('center, table, thead, tbody, tr').unbind('mouseenter mouseleave');
	}

	/**
	 * Register the hover events for the inspector.
	 */
	function vrcRegisterInspector(unregister) {
		if (unregister === true) {
			vrcUnregisterInspector();
		}

		jQuery('.vrc-inspector-wrap').find('*').not('center, table, thead, tbody, tr').hover(function() {
			jQuery('.vrc-inspector-hover').removeClass('vrc-inspector-hover');
			jQuery(this).addClass('vrc-inspector-hover');
		}, function() {
			if (jQuery(this).parent().length && jQuery(this).parent().is(':hover')) {
				jQuery(this).parent().addClass('vrc-inspector-hover');
			}
			jQuery(this).removeClass('vrc-inspector-hover');
		});

		jQuery('.vrc-inspector-wrap').mouseleave(function() {
			jQuery('.vrc-inspector-hover').removeClass('vrc-inspector-hover');
		});
	}

	/**
	 * Enables the CSS editing mode.
	 */
	function vrcEnableCSSEditing() {
		css_editing = true;
	}

	/**
	 * Disables the CSS editing mode.
	 */
	function vrcDisableCSSEditing() {
		css_editing = false;
		// hide CSS params
		jQuery('.vrc-inspector-css-param, .vrc-inspector-css-border-param').hide();
	}

	/**
	 * Starts the CSS inspector. Can be called also by other tabs in this page.
	 */
	function vrcStartCSSInspector(file) {
		// when a specific file is requested, simulate the cancel action
		if (file && jQuery('.vrc-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').length) {
			// hide current CSS params
			jQuery('.vrc-inspector-css-param, .vrc-inspector-css-border-param').hide();
			// unset current target
			current_target = null;
			// make the list of targets with custom CSS empty
			css_custom_classes = [];
			// disable save button
			jQuery('#vrc-inspector-save').prop('disabled', true);
			// sanitize template source code from any script tag in case of malformed markup
			jQuery('.vrc-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').find('script').remove();
			// grab requested content and move it to the inspector wrapper
			jQuery('.vrc-inspector-wrap').html(jQuery('.vrc-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').html()).attr('data-inspectfile', file);
			// register hovering event
			vrcRegisterInspector(true);
		}
		if (!file && current_file !== null) {
			// triggers after adding and saving a conditional text tag
			file = current_file;
		}
		if (!tpl_names.hasOwnProperty(file)) {
			console.error('file not found', file, tpl_names);
			return false;
		}
		// turn off adding tag mode
		current_tag = null;
		// define global file editing
		current_file = file;
		// enable flag for CSS editing
		vrcEnableCSSEditing();
		// update title
		jQuery('#vrc-inspector-title').text(tpl_names[file] + ' - ' + Joomla.JText._('VRC_INSPECTOR_START'));
		// update helper
		jQuery('#vrc-inspector-help').html('<?php VikRentCarIcons::e('paint-brush'); ?> ' + Joomla.JText._('VRC_CSS_EDITING_HELP')).show();
		// display inspector outer
		jQuery('#vrc-inspector-outer').show();
		if (current_target !== null) {
			// render CSS properties
			vrcRenderCSSInspector();
		} else {
			// animate scroll to the outer position
			jQuery('html,body').animate({scrollTop: jQuery('#vrc-inspector-outer').offset().top - 40}, {duration: 400});
		}
	}

	/**
	 * Displays the CSS properties of the selected target.
	 * Creates a new class identifier for the current target.
	 */
	function vrcRenderCSSInspector() {
		if (current_target === null || !css_editing) {
			console.error('CSS editing not available', current_target, css_editing);
			return false;
		}
		// add the unique class to the target element
		var unique_class_id = vrcTargetUniqueClassName();
		current_target.removeClass('vrc-inspector-hover').addClass(unique_class_id).data('tmpclass', unique_class_id);
		// set current tag name
		jQuery('#vrc-inspector-css-tag').text(current_target.prop('tagName'));
		// set current font color
		jQuery('#vrc-inspector-css-color').css('backgroundColor', current_target.css('color'));
		// set current background color
		jQuery('#vrc-inspector-css-backgcolor').css('backgroundColor', current_target.css('backgroundColor'));
		// check if element has borders and set values
		var elborder = current_target.css('border-left-width').match(/^([0-9.]+)/);
		if (elborder && elborder[1] > 0) {
			// has got a border
			jQuery('input[name="vrc-inspector-css-border"]').prop('checked', true);
			jQuery('.vrc-inspector-css-border-param').show();
			jQuery('#vrc-inspector-css-borderwidth').val(elborder[1]);
			jQuery('#vrc-inspector-css-bordercolor').css('backgroundColor', current_target.css('border-left-color'));
		} else {
			// has got no border
			jQuery('input[name="vrc-inspector-css-border"]').prop('checked', false);
			jQuery('.vrc-inspector-css-border-param').hide();
			jQuery('#vrc-inspector-css-borderwidth').val('0');
			// unset background completely, not just the bgcolor
			jQuery('#vrc-inspector-css-bordercolor').css('background', '');
		}
		// display CSS params
		jQuery('.vrc-inspector-css-param').show();
		// animate scroll to the outer position
		jQuery('html,body').animate({scrollTop: jQuery('#vrc-inspector-outer').offset().top - 40}, {duration: 400});
	}

	/**
	 * Toggles the CSS params for the border
	 */
	function vrcToggleCSSBorderParams() {
		if (jQuery('input[name="vrc-inspector-css-border"]').is(':checked')) {
			jQuery('.vrc-inspector-css-border-param').show();
			if (current_target !== null) {
				// restore border, if any
				var borderwidth = jQuery('#vrc-inspector-css-borderwidth').val();
				if (borderwidth.length && !isNaN(borderwidth) && borderwidth > 0) {
					current_target.css('border', borderwidth + 'px solid ' + vrcRgb2Hex(jQuery('#vrc-inspector-css-bordercolor').css('backgroundColor')));
				}
			}
		} else {
			jQuery('.vrc-inspector-css-border-param').hide();
			if (current_target !== null) {
				// unset border completely
				current_target.css('border', '');
			}
		}
	}

	/**
	 * AJAX request to remove a special tag from a template file.
	 */
	function vrcRemoveCondTextTagFromTpl(tag, file) {
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "<?php echo VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=condtext_update_tmpl'); ?>",
			data: {
				tmpl: "component",
				tagaction: 'remove',
				tag: tag,
				file: file
			}
		}).done(function(response) {
			try {
				var obj_res = typeof response === 'string' ? JSON.parse(response) : response;
				if (!obj_res.hasOwnProperty('newhtml')) {
					console.error('Unexpected JSON response', obj_res);
					return false;
				}
				// update template file HTML code
				jQuery('.vrc-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').html(obj_res['newhtml']);
				// update button classes and icon
				var btn_triggering = jQuery('.vrc-condtext-tmpl-status[data-inspectfile="' + file + '"][data-specialtag="' + tag + '"]');
				btn_triggering.removeClass('btn-success vrc-condtext-intmpl').addClass('btn-secondary vrc-condtext-notintmpl');
				btn_triggering.find('i').removeClass().addClass('<?php echo VikRentCarIcons::i('plus'); ?>');
			} catch(err) {
				console.error('could not parse JSON response', err, response);
				alert('Request failed');
			}
		}).fail(function() {
			alert('Request failed');
			console.error('Request failed');
		});
	}

	/**
	 * Removes (undo) a special tag from the inspector.
	 */
	function vrcRemoveTagFromInspector() {
		if (current_tag === null) {
			return false;
		}
		var escape_tag = current_tag.replace(/[.*+\-?^${}()|[\]\\]/g, '\\$&');
		// replace all occurrences of escaped tag
		var empty_html = jQuery('.vrc-inspector-wrap').html();
		empty_html = empty_html.replace(new RegExp(escape_tag, 'g'), '');
		// restore correct HTML
		jQuery('.vrc-inspector-wrap').html(empty_html);
	}

	/**
	 * When manipulating the source code of the template files, errors may occur.
	 * This function makes a new AJAX request to restore the file from the backup.
	 */
	function vrcRestoreBackupFile() {
		if (current_file === null) {
			console.error('file cannot be null, unable to restore file');
			return false;
		}
		// make the request
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "<?php echo VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=condtext_update_tmpl'); ?>",
			data: {
				tmpl: "component",
				tagaction: 'restore',
				file: current_file
			}
		}).done(function(response) {
			var reload_href = jQuery('.vrc-reload-plchld-link').attr('href');
			try {
				var obj_res = typeof response === 'string' ? JSON.parse(response) : response;
				if (!obj_res.hasOwnProperty('newhtml')) {
					console.error('Unexpected JSON response while restoring', obj_res, response);
					return false;
				}
				location.href = reload_href;
				return;
			} catch(err) {
				console.error('could not parse JSON response when restoring', err, response);
				// display error message
				alert('Restoring failed. The template file ' + current_file + ' may be corrupted');
				location.href = reload_href;
				return;
			}
		}).fail(function() {
			alert('Restoring request failed');
			console.error('Restoring request failed');
		});
	}

	/**
	 * Declare the functions for the document ready event.
	 */
	jQuery(function() {

		/**
		 * Register border width number input change event.
		 */
		jQuery('#vrc-inspector-css-borderwidth').change(function() {
			if (current_target === null) {
				return;
			}
			var borderwidth = jQuery(this).val();
			if (borderwidth.length && !isNaN(borderwidth) && borderwidth > 0) {
				// use short-hand syntax to update the border
				current_target.css('border', borderwidth + 'px solid ' + vrcRgb2Hex(jQuery('#vrc-inspector-css-bordercolor').css('backgroundColor')));
			} else {
				// unset the border completely
				current_target.css('border', '');
			}
			// trigger target style modification
			vrcCSSTargetChanged();
		});

		/**
		 * Register color-picker for CSS editing parameters.
		 */
		jQuery('.vrc-inspector-colorpicker-trig').ColorPicker({
			color: '#ffffff',
			onShow: function (colpkr, el) {
				if (current_target === null) {
					return false;
				}
				var cur_color = jQuery(el).css('backgroundColor');
				jQuery(el).ColorPickerSetColor(vrcRgb2Hex(cur_color));
				jQuery(colpkr).show();
				return false;
			},
			onChange: function (hsb, hex, rgb, el) {
				var element = jQuery(el);
				var elid = element.attr('id');
				element.css('backgroundColor', '#'+hex);
				if (current_target !== null) {
					var css_prop = 'backgroundColor';
					if (elid == 'vrc-inspector-css-color') {
						css_prop = 'color';
					} else if (elid == 'vrc-inspector-css-bordercolor') {
						css_prop = 'borderColor';
					}
					current_target.css(css_prop, '#'+hex);
				}
				// trigger target style modification
				vrcCSSTargetChanged();
			},
			onSubmit: function(hsb, hex, rgb, el) {
				var element = jQuery(el);
				var elid = element.attr('id');
				element.css('backgroundColor', '#'+hex);
				if (current_target !== null) {
					var css_prop = 'backgroundColor';
					if (elid == 'vrc-inspector-css-color') {
						css_prop = 'color';
					} else if (elid == 'vrc-inspector-css-bordercolor') {
						css_prop = 'borderColor';
					}
					current_target.css(css_prop, '#'+hex);
				}
				element.ColorPickerHide();
				// trigger target style modification
				vrcCSSTargetChanged();
			}
		});

		/**
		 * Listens to the buttons to start the CSS editing mode. May be used on different tabs.
		 */
		jQuery('.vrc-inspector-btn').click(function() {
			var file = jQuery(this).attr('data-inspectfile');
			if (!file || !tpl_names.hasOwnProperty(file)) {
				alert('Template not found');
				console.error('Template not found ' + file, tpl_names);
				return false;
			}
			// make sure the proper configuration tab is active
			var proper_tab_cont = jQuery('.vrc-inspector-wrap').closest('dd.tabs');
			if (!proper_tab_cont.is(':visible')) {
				var tab_id = proper_tab_cont.attr('id').replace('pt', '');
				if (jQuery('dt.tabs[data-ptid="' + tab_id + '"]').length) {
					// simulate click on proper tab, as CSS editing can be started also from another tab
					jQuery('dt.tabs[data-ptid="' + tab_id + '"]').trigger('click');
				}
			}
			// start CSS inspector
			vrcStartCSSInspector(file);
		});

		/**
		 * Listens to the click on the buttons to add or remove special tags.
		 */
		jQuery('.vrc-condtext-tmpl-status').click(function() {
			// always hide inspector outer
			jQuery('#vrc-inspector-outer').hide();
			// always make the helper text empty
			jQuery('#vrc-inspector-help').html('');
			// always disable the save button
			jQuery('#vrc-inspector-save').prop('disabled', true);
			// always disable CSS editing
			vrcDisableCSSEditing();
			// find file name and tag
			var file = jQuery(this).attr('data-inspectfile');
			if (!file || !tpl_names.hasOwnProperty(file)) {
				alert('Template not found');
				console.error('Template not found ' + file, tpl_names);
				return false;
			}
			// update global current tag and file selected
			var tag = jQuery(this).attr('data-specialtag');
			current_tag = tag;
			current_file = file;
			// check if tag is already in the template file
			if (jQuery(this).hasClass('vrc-condtext-intmpl')) {
				// ask to remove it
				var rmtxt = Joomla.JText._('VRC_CONDTEXT_TAG_RM_HELP');
				if (confirm(rmtxt.replace('%s', tpl_names[file]))) {
					vrcRemoveCondTextTagFromTpl(current_tag, file);
				} else {
					current_tag = null;
					current_file = null;
				}
				return false;
			}
			// update title
			jQuery('#vrc-inspector-title').text(tpl_names[file] + ' - ' + Joomla.JText._('VRCONFIGEDITTMPLFILE'));
			// update helper
			var helper_txt = Joomla.JText._('VRC_CONDTEXT_TAG_ADD_HELP');
			jQuery('#vrc-inspector-help').html('<?php VikRentCarIcons::e('magic'); ?> ' + helper_txt.replace('%s', current_tag)).show();
			// sanitize template source code from any script tag in case of malformed markup
			jQuery('.vrc-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').find('script').remove();
			// grab requested content and move it to the inspector wrapper
			jQuery('.vrc-inspector-wrap').html(jQuery('.vrc-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').html()).attr('data-inspectfile', file);
			// display inspector outer
			jQuery('#vrc-inspector-outer').show();
			// animate scroll to the outer position
			jQuery('html,body').animate({scrollTop: jQuery('#vrc-inspector-outer').offset().top - 40}, {duration: 400});
			// register hovering event
			vrcRegisterInspector(true);
		});

		/**
		 * Listens to the click on the elements of the HTML inspector.
		 * It can append a special tag, remove one before, or start the CSS editing.
		 */
		jQuery('.vrc-inspector-wrap').click(function(e) {
			if (current_file === null) {
				// file cannot be null
				return false;
			}
			// update current target
			var target = jQuery(e.target);
			current_target = target;
			// insert tag, if requested
			if (current_tag != null) {
				// check if the tag was already added
				var removed = false;
				if (jQuery('.vrc-inspector-wrap').html().indexOf(current_tag) >= 0) {
					// remove current tag before appending it to the new position
					vrcRemoveTagFromInspector();
					removed = true;
				}
				target.append(current_tag);
				// enable the save button
				jQuery('#vrc-inspector-save').prop('disabled', false);
				if (removed) {
					// inspector hovering needs to be re-registered after removing the tag
					vrcRegisterInspector(true);
				}
				return;
			}
			// if we reach this point, start CSS inspector
			vrcStartCSSInspector(null);
		});

		/**
		 * Tries to undo the last action. Behaves differently depending on the active mode.
		 */
		jQuery('#vrc-inspector-cancel').click(function() {
			if (current_file === null) {
				// file cannot be null, hide inspector outer no matter what
				jQuery('#vrc-inspector-outer').hide();
				return false;
			}
			if (current_tag != null) {
				// unset all occurrences of the current tag (if any)
				var empty_html = jQuery('.vrc-inspector-wrap').html();
				if (empty_html.length && empty_html.indexOf(current_tag) >= 0) {
					// remove tag from inspector
					vrcRemoveTagFromInspector();
					// inspector hovering needs to be re-registered after editing the content
					vrcRegisterInspector(true);
				} else {
					// hide inspector outer, as the tag was not added yet
					jQuery('#vrc-inspector-outer').hide();
				}
			} else {
				if (css_editing && current_target !== null) {
					// hide current CSS params
					jQuery('.vrc-inspector-css-param, .vrc-inspector-css-border-param').hide();
					// unset current target
					current_target = null;
					// make the list of targets with custom CSS empty
					css_custom_classes = [];
					// disable save button
					jQuery('#vrc-inspector-save').prop('disabled', true);
					// sanitize template source code from any script tag in case of malformed markup
					jQuery('.vrc-tplcontent-inspector-ghost[data-inspectfile="' + current_file + '"]').find('script').remove();
					// cancelling during CSS editing means restoring the original source code
					jQuery('.vrc-inspector-wrap').html(jQuery('.vrc-tplcontent-inspector-ghost[data-inspectfile="' + current_file + '"]').html()).attr('data-inspectfile', current_file);
					// register hovering event is necessary
					vrcRegisterInspector(true);
				} else {
					// hide inspector outer, tag not yet added or already added and saved
					jQuery('#vrc-inspector-outer').hide();
				}
			}
		});

		/**
		 * Saves the last action made through the inspector.
		 */
		jQuery('#vrc-inspector-save').click(function() {
			if (current_file === null) {
				console.error('file cannot be null');
				return false;
			}
			var savebtn = jQuery(this);
			// show loading
			savebtn.find('i').removeClass().addClass('<?php echo VikRentCarIcons::i('refresh', 'fa-spin fa-fw'); ?>');
			// detect action type
			var action_type = 'add';
			if (css_editing && css_custom_classes.length) {
				action_type = 'styles';
			}
			// make the request
			var jqxhr = jQuery.ajax({
				type: "POST",
				url: "<?php echo VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=condtext_update_tmpl'); ?>",
				data: {
					tmpl: "component",
					tagaction: action_type,
					tag: current_tag,
					file: current_file,
					newcontent: jQuery('.vrc-inspector-wrap').html(),
					custom_classes: css_custom_classes
				}
			}).done(function(response) {
				try {
					var obj_res = typeof response === 'string' ? JSON.parse(response) : response;
					if (!obj_res.hasOwnProperty('newhtml')) {
						console.error('Unexpected JSON response', obj_res);
						return false;
					}
					// stop loading
					savebtn.find('i').removeClass().addClass('<?php echo VikRentCarIcons::i('check-circle'); ?>');
					// disable another saving
					savebtn.prop('disabled', true);
					if (current_tag !== null) {
						// update button classes and icon
						var btn_triggering = jQuery('.vrc-condtext-tmpl-status[data-inspectfile="' + current_file + '"][data-specialtag="' + current_tag + '"]');
						btn_triggering.removeClass('btn-secondary vrc-condtext-notintmpl').addClass('btn-success vrc-condtext-intmpl');
						btn_triggering.find('i').removeClass().addClass('<?php echo VikRentCarIcons::i('check-circle'); ?>');
						// hide helper
						jQuery('#vrc-inspector-help').hide().html('');
						// unset tag editing mode
						current_tag = null;
					}
					if (css_editing && css_custom_classes.length) {
						// empty the list of custom CSS classes as they were all saved
						css_custom_classes = [];
					}
					// update template file HTML code
					jQuery('.vrc-tplcontent-inspector-ghost[data-inspectfile="' + current_file + '"]').html(obj_res['newhtml']);
				} catch(err) {
					console.error('could not parse JSON response after saving', err, response);
					// display error message
					alert(Joomla.JText._('VRC_EDITTPL_FATALERROR'));
					// restore backup of the file source code
					vrcRestoreBackupFile();
				}
			}).fail(function() {
				console.error('Request failed completely');
				// display error message
				alert(Joomla.JText._('VRC_EDITTPL_FATALERROR'));
				// restore backup of the file source code
				vrcRestoreBackupFile();
			});
		});

		jQuery('#vrc-contwrapper-save').click(function() {
			var wrapper_code = jQuery('#vrc-contwrapper-html').val();
			if (!wrapper_code || !wrapper_code.length) {
				return false;
			}
			jQuery(this).prop('disabled', true);
			VRCCore.doAjax('<?php echo VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=mail.update_ve_contwraper'); ?>', {
				wrapper_content: wrapper_code
			}, (resp) => {
				// hide editing mode
				jQuery('.vrc-contwrapper-edit').hide();
				jQuery(this).prop('disabled', false);
			}, (err) => {
				alert(err.responseText);
				jQuery(this).prop('disabled', false);
			});
		});

	});
</script>
