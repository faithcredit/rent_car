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

$vrc_tn = $this->vrc_tn;

$vrc_app = VikRentCar::getVrcApplication();
$vrc_app->loadVisualEditorAssets();

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));

$langs = $vrc_tn->getLanguagesList();
$xml_tables = $vrc_tn->getTranslationTables();
$active_table = '';
$active_table_key = '';

// JS lang vars
JText::script('VRC_COPY_ORIGINAL_TN');

if (!(count($langs) > 1)) {
	//Error: only one language is published. Translations are useless
	?>
	<p class="err"><?php echo JText::_('VRCTRANSLATIONERRONELANG'); ?></p>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
	<?php
} elseif (!count($xml_tables) || strlen($vrc_tn->getError())) {
	//Error: XML file not readable or errors occurred
	?>
	<p class="err"><?php echo $vrc_tn->getError(); ?></p>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
	<?php
} else {
	$cur_langtab = VikRequest::getString('vrc_lang', '', 'request');
	$table = VikRequest::getString('vrc_table', '', 'request');
	if (!empty($table)) {
		$table = $vrc_tn->replacePrefix($table);
	}
?>

<form action="index.php?option=com_vikrentcar&amp;task=translations" method="post" onsubmit="return vrcCheckChanges();">
	<div style="width: 100%; display: inline-block;" class="btn-toolbar vrc-btn-toolbar" id="filter-bar">
		<div class="btn-group pull-right">
			<button class="btn" type="submit"><?php echo JText::_('VRCGETTRANSLATIONS'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<select name="vrc_table">
				<option value="">-----------</option>
			<?php
			foreach ($xml_tables as $key => $value) {
				$active_table = $vrc_tn->replacePrefix($key) == $table ? $value : $active_table;
				$active_table_key = $vrc_tn->replacePrefix($key) == $table ? $key : $active_table_key;
				?>
				<option value="<?php echo JHtml::_('esc_attr', $key); ?>"<?php echo $vrc_tn->replacePrefix($key) == $table ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $value); ?></option>
				<?php
			}
			?>
			</select>
		</div>
	</div>
	<input type="hidden" name="vrc_lang" class="vrc_lang" value="<?php echo JHtml::_('esc_attr', $vrc_tn->default_lang); ?>">
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="translations" />
</form>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vrc-translation-langtabs">
<?php
foreach ($langs as $ltag => $lang) {
	$is_def = ($ltag == $vrc_tn->default_lang);
	$lcountry = substr($ltag, 0, 2);
	$flag = '';
	if (!defined('ABSPATH') && is_file(JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'mod_languages' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $lcountry . '.gif')) {
		$flag = '<img src="' . JUri::root() . 'media/mod_languages/images/' . $lcountry . '.gif"/>';
	}
		?><div class="vrc-translation-tab<?php echo $is_def ? ' vrc-translation-tab-default' : ''; ?>" data-vrclang="<?php echo JHtml::_('esc_attr', $ltag); ?>">
		<?php
		if (!empty($flag)) {
			?>
			<span class="vrc-translation-flag"><?php echo $flag; ?></span>
			<?php
		}
		?>
			<span class="vrc-translation-langname"><?php echo $lang['name']; ?></span>
		</div><?php
}

if (!defined('ABSPATH')) {
	?>
		<div class="vrc-translation-tab vrc-translation-tab-ini" data-vrclang="">
			<span class="vrc-translation-iniflag">.INI</span>
			<span class="vrc-translation-langname"><?php echo JText::_('VRCTRANSLATIONINISTATUS'); ?></span>
		</div>
	<?php
}
?>
	</div>
	<div class="vrc-translation-tabscontents">
<?php
$table_cols = !empty($active_table_key) ? $vrc_tn->getTableColumns($active_table_key) : array();
$table_def_dbvals = !empty($active_table_key) ? $vrc_tn->getTableDefaultDbValues($active_table_key, array_keys($table_cols)) : array();
if (!empty($active_table_key)) {
	echo '<input type="hidden" name="vrc_table" value="'.$active_table_key.'"/>'."\n";
}
foreach ($langs as $ltag => $lang) {
	$is_def = ($ltag == $vrc_tn->default_lang);
	?>
		<div class="vrc-translation-langcontent" style="display: <?php echo $is_def ? 'block' : 'none'; ?>;" id="vrc_langcontent_<?php echo $ltag; ?>">
	<?php
	if (empty($active_table_key)) {
		?>
			<p class="warn"><?php echo JText::_('VRCTRANSLATIONSELTABLEMESS'); ?></p>
		<?php
	} elseif (strlen($vrc_tn->getError()) > 0) {
		?>
			<p class="err"><?php echo $vrc_tn->getError(); ?></p>
		<?php
	} else {
		?>
			<fieldset class="adminform">
				<legend class="adminlegend"><?php echo $active_table; ?> - <?php echo $lang['name'].($is_def ? ' - '.JText::_('VRCTRANSLATIONDEFLANG') : ''); ?></legend>
				<div class="vrc-translations-tab-container">
	<?php
	if ($is_def) {
		//Values of Default Language to be translated
		foreach ($table_def_dbvals as $reference_id => $values) {
			?>
					<div class="vrc-translations-default-element">
						<div class="vrc-translations-element-title" data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<div class="vrc-translate-element-cell"><?php echo $vrc_tn->getRecordReferenceName($table_cols, $values); ?></div>
						</div>
						<div class="vrc-translations-element-contents">
			<?php
			foreach ($values as $field => $def_value) {
				$title = $table_cols[$field]['jlang'];
				$type = $table_cols[$field]['type'];
				if ($type == 'html') {
					$def_value = strip_tags($def_value);
				}
				?>
							<div class="vrc-translations-element-row" data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
								<div class="vrc-translations-element-lbl"><?php echo $title; ?></div>
								<div class="vrc-translations-element-val" data-origvalue="<?php echo $reference_id . '-' . $field; ?>"><?php echo $type != 'json' ? $def_value : ''; ?></div>
							</div>
				<?php
				if ($type == 'json') {
					$tn_keys = $table_cols[$field]['keys'];
					$keys = !empty($tn_keys) ? explode(',', $tn_keys) : array();
					$json_def_values = json_decode($def_value, true);
					if (count($json_def_values) > 0) {
						foreach ($json_def_values as $jkey => $jval) {
							if ((!in_array($jkey, $keys) && count($keys) > 0) || empty($jval)) {
								continue;
							}
							$json_lbl = '&nbsp;';
							if (!is_numeric($jkey)) {
								$guess_lbl = JText::_('VRC_' . strtoupper($jkey));
								$json_lbl = $guess_lbl != 'VRC_' . strtoupper($jkey) ? $guess_lbl : ucwords($jkey);
							}
							?>
							<div class="vrc-translations-element-row vrc-translations-element-row-nested" data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
								<div class="vrc-translations-element-lbl"><?php echo $json_lbl; ?></div>
								<div class="vrc-translations-element-val" data-origvalue="<?php echo $reference_id . '-' . $field . '-' . $jkey; ?>"><?php echo $jval; ?></div>
							</div>
							<?php
						}
					}
				}
				?>
				<?php
			}
			?>
						</div>
					</div>
			<?php
		}
	} else {
		//Translation Fields for this language
		$lang_record_tn = $vrc_tn->getTranslatedTable($active_table_key, $ltag);
		foreach ($table_def_dbvals as $reference_id => $values) {
			?>
					<div class="vrc-translations-language-element">
						<div class="vrc-translations-element-title" data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<div class="vrc-translate-element-cell"><?php echo $vrc_tn->getRecordReferenceName($table_cols, $values); ?></div>
						</div>
						<div class="vrc-translations-element-contents">
			<?php
			foreach ($values as $field => $def_value) {
				$title = $table_cols[$field]['jlang'];
				$type = $table_cols[$field]['type'];
				if ($type == 'skip') {
					continue;
				}
				$tn_value = '';
				$tn_class = ' vrc-missing-translation';
				if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content']) && strlen($lang_record_tn[$reference_id]['content'][$field])) {
					if (in_array($type, array('text', 'textarea', 'html'))) {
						$tn_class = ' vrc-field-translated';
					} else {
						$tn_class = '';
					}
				}
				?>
							<div class="vrc-translations-element-row<?php echo $tn_class; ?>" data-reference="<?php echo $ltag.'-'.$reference_id; ?>" data-copyoriginal="<?php echo $reference_id . '-' . $field; ?>">
								<div class="vrc-translations-element-lbl"><?php echo $title; ?></div>
								<div class="vrc-translations-element-val">
						<?php
						if ($type == 'text') {
							if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
								$tn_value = $lang_record_tn[$reference_id]['content'][$field];
							}
							?>
									<input type="text" name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>]" value="<?php echo htmlspecialchars($tn_value); ?>" size="40" placeholder="<?php echo htmlspecialchars($def_value); ?>"/>
							<?php
						} elseif ($type == 'textarea') {
							if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
								$tn_value = $lang_record_tn[$reference_id]['content'][$field];
							}
							?>
									<textarea name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>]" rows="7" cols="170"><?php echo $tn_value; ?></textarea>
							<?php
						} elseif ($type == 'html') {
							if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
								$tn_value = $lang_record_tn[$reference_id]['content'][$field];
							}
							if (defined('ABSPATH') && interface_exists('Throwable')) {
								/**
								 * With PHP >= 7 supporting throwable exceptions for Fatal Errors
								 * we try to avoid issues with third party plugins that make use
								 * of the WP native function get_current_screen().
								 * 
								 * @wponly
								 */
								try {
									echo $editor->display( "tn[".$ltag."][".$reference_id."][".$field."]", $tn_value, '100%', 350, 70, 20, true, "tn_".$ltag."_".$reference_id."_".$field );
								} catch (Throwable $t) {
									echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
								}
							} else {
								// we cannot catch Fatal Errors in PHP 5.x
								echo $editor->display( "tn[".$ltag."][".$reference_id."][".$field."]", $tn_value, '100%', 350, 70, 20, true, "tn_".$ltag."_".$reference_id."_".$field );
							}
						}
						?>
								</div>
							</div>
				<?php
				if ($type == 'json') {
					$tn_keys = $table_cols[$field]['keys'];
					$keys = !empty($tn_keys) ? explode(',', $tn_keys) : array();
					$json_def_values = json_decode($def_value, true);
					if (count($json_def_values)) {
						$tn_json_value = array();
						if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
							$tn_json_value = json_decode($lang_record_tn[$reference_id]['content'][$field], true);
						}
						foreach ($json_def_values as $jkey => $jval) {
							if ((!in_array($jkey, $keys) && count($keys) > 0) || empty($jval)) {
								continue;
							}
							$json_lbl = '&nbsp;';
							if (!is_numeric($jkey)) {
								$guess_lbl = JText::_('VRC_' . strtoupper($jkey));
								$json_lbl = $guess_lbl != 'VRC_' . strtoupper($jkey) ? $guess_lbl : ucwords($jkey);
							}
							?>
							<div class="vrc-translations-element-row vrc-translations-element-row-nested" data-reference="<?php echo $ltag.'-'.$reference_id; ?>" data-copyoriginal="<?php echo $reference_id . '-' . $field . '-' . $jkey; ?>">
								<div class="vrc-translations-element-lbl"><?php echo $json_lbl; ?></div>
								<div class="vrc-translations-element-val">
								<?php
								if (strlen($jval) > 40) {
									// check if this translation field requires the rich text visual editor
									if ($jkey == 'tpl_text' && strpos($jval, '<') !== false) {
										// the original field contains HTML code built through the visual editor
										$veditor_name = 'tn[' . $ltag . '][' . $reference_id . '][' . $field . '][' . $jkey . ']';
										$veditor_id   = 'tn_' . $ltag . '_' . $reference_id . '_' . $field . '_' . $jkey;
										$tarea_attr = array(
											'id' => $veditor_id,
											'rows' => '7',
											'cols' => '170',
											'style' => 'min-width: 60%;',
										);
										$editor_opts = array(
											'modes' => array(
												'text',
												'visual',
											),
										);
										echo $vrc_app->renderVisualEditor($veditor_name, (isset($tn_json_value[$jkey]) ? $tn_json_value[$jkey] : ''), $tarea_attr, $editor_opts);
									} else {
										?>
									<textarea rows="7" cols="170" style="min-width: 60%;" name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>][<?php echo $jkey; ?>]"><?php echo isset($tn_json_value[$jkey]) ? $tn_json_value[$jkey] : ''; ?></textarea>
										<?php
									}
								} else {
									?>
									<input type="text" name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>][<?php echo $jkey; ?>]" value="<?php echo isset($tn_json_value[$jkey]) ? $tn_json_value[$jkey] : ''; ?>" size="40" placeholder="<?php echo htmlspecialchars($jval); ?>"/>
									<?php
								}
								?>
								</div>
							</div>
							<?php
						}
					}
				}
			}
			?>
						</div>
					</div>
			<?php
		}
	}
	?>
				</div>
			</fieldset>
		<?php
	}
	?>
		</div>
	<?php
}
/**
 * @wponly  removed contents for .INI status
 */
?>
	</div>
	<input type="hidden" name="vrc_lang" class="vrc_lang" value="<?php echo JHtml::_('esc_attr', $vrc_tn->default_lang); ?>">
	<input type="hidden" name="task" value="translations">
	<input type="hidden" name="option" value="com_vikrentcar" />
	<?php echo JHtml::_('form.token'); ?>

	<div class="vrc-translations-lim-wrap">
		<table align="center">
			<tr>
				<td align="center"><?php echo $vrc_tn->getPagination(); ?></td>
			</tr>
			<tr>
				<td align="center">
					<select name="limit" onchange="vrcHandleCustomLimit(this.value);">
						<option value="2"<?php echo $vrc_tn->lim == 2 ? ' selected="selected"' : ''; ?>>2</option>
						<option value="5"<?php echo $vrc_tn->lim == 5 ? ' selected="selected"' : ''; ?>>5</option>
						<option value="10"<?php echo $vrc_tn->lim == 10 ? ' selected="selected"' : ''; ?>>10</option>
						<option value="20"<?php echo $vrc_tn->lim == 20 ? ' selected="selected"' : ''; ?>>20</option>
					</select>
				</td>
			</tr>
		</table>
	</div>
</form>

<script type="text/Javascript">
var vrc_tn_changes = false;
var vrc_copy_delay = 500;
var vrc_copy_timeout = null;

function vrcHandleCustomLimit(lim) {
	var cur_limstart = document.adminForm.limitstart;
	if (typeof cur_limstart === 'undefined') {
		// append hidden input field to form
		var limstart_node = document.createElement('INPUT');
		limstart_node.setAttribute('type', 'hidden');
		limstart_node.setAttribute('name', 'limitstart');
		limstart_node.setAttribute('value', '0');
		document.adminForm.appendChild(limstart_node);
	} else {
		// update existing value
		document.adminForm.limitstart.value = '0';
	}
	// submit form
	document.adminForm.submit();
}

function vrcCheckChanges() {
	if (!vrc_tn_changes) {
		return true;
	}
	return confirm("<?php echo addslashes(JText::_('VRCTANSLATIONSCHANGESCONF')); ?>");
}

function vrcHoverCopyTranslation(elem) {
	if (!elem) {
		return false;
	}
	var copy_reference = elem.attr('data-copyoriginal');
	if (!copy_reference) {
		return false;
	}
	var orig_elem = jQuery('.vrc-translations-element-val[data-origvalue="' + copy_reference + '"]');
	if (!orig_elem || !orig_elem.length || !orig_elem.html().length) {
		return false;
	}
	// check if translation field has got a value
	var tn_field_val = elem.find('.vrc-translations-element-val').find('input,textarea').val();
	if (!tn_field_val) {
		// append button to copy the original content
		elem.
			find('.vrc-translations-element-lbl').
			append('<div class="vrc-tn-copyoriginal"><span title="' + Joomla.JText._('VRC_COPY_ORIGINAL_TN') + '" onclick="vrcApplyCopyTranslation(this);"><?php VikRentCarIcons::e('copy'); ?></span></div>');
	}
}

function vrcApplyCopyTranslation(elem) {
	elem = jQuery(elem).closest('.vrc-translations-element-row[data-copyoriginal]');
	if (!elem || !elem.length) {
		return false;
	}
	var copy_reference = elem.attr('data-copyoriginal');
	if (!copy_reference) {
		return false;
	}
	var orig_elem = jQuery('.vrc-translations-element-val[data-origvalue="' + copy_reference + '"]');
	if (!orig_elem || !orig_elem.length) {
		return false;
	}

	// make sure to remove any copy-from-original button
	jQuery('.vrc-tn-copyoriginal').remove();

	// set original content to translation field
	var input = elem.find('.vrc-translations-element-val').find('input');
	if (input && input.length) {
		input.val(orig_elem.html()).trigger('change');
	}
	var tarea = elem.find('.vrc-translations-element-val').find('textarea');
	if (tarea && tarea.length) {
		tarea.val(orig_elem.html()).trigger('change');
	}

	// grab lang tag, which could be part of the ID of a WYSIWYG editor
	var lang_reference_arr = elem.attr('data-reference').split('-');
	lang_reference_arr.splice((lang_reference_arr.length - 1), 1);
	var current_lang = lang_reference_arr.join('-');
	var wysiwyg_hipo_id = current_lang + '-' . copy_reference;
	wysiwyg_hipo_id.replaceAll('-', '_');
	
	// attempt to inject value inside wysiwyg editor
	try {
		// native wysiwyg editor
		if (typeof Joomla !== undefined && Joomla.editors && Joomla.editors.instances) {
			if (Joomla.editors.instances.hasOwnProperty(wysiwyg_hipo_id)) {
				Joomla.editors.instances[wysiwyg_hipo_id].setValue(orig_elem.html());
			}
		}
	} catch(e) {
		// do nothing
	}
}

jQuery(document).ready(function() {

	jQuery('.vrc-translation-tab').click(function() {
		var langtag = jQuery(this).attr('data-vrclang');
		if (jQuery('#vrc_langcontent_'+langtag).length) {
			jQuery('.vrc_lang').val(langtag);
			jQuery('.vrc-translation-tab').removeClass('vrc-translation-tab-default');
			jQuery(this).addClass('vrc-translation-tab-default');
			jQuery('.vrc-translation-langcontent').hide();
			jQuery('#vrc_langcontent_'+langtag).fadeIn();
		} else {
			jQuery('.vrc-translation-tab').removeClass('vrc-translation-tab-default');
			jQuery(this).addClass('vrc-translation-tab-default');
			jQuery('.vrc-translation-langcontent').hide();
			jQuery('#vrc_langcontent_ini').fadeIn();
		}
	});

	jQuery('#adminForm input[type=text], #adminForm textarea').change(function() {
		vrc_tn_changes = true;
	});

	jQuery('.vrc-translations-element-row[data-copyoriginal]').hover(function() {
		var elem = jQuery(this);
		vrc_copy_timeout = setTimeout(function() {
			vrcHoverCopyTranslation(elem);
		}, vrc_copy_delay);
	}, function() {
		// cancel scheduled hovering function
		clearTimeout(vrc_copy_timeout);
		// make sure to remove any copy-from-original button
		jQuery('.vrc-tn-copyoriginal').remove();
	});
<?php
if (!empty($cur_langtab)) {
	?>
	jQuery('.vrc-translation-tab').each(function() {
		var langtag = jQuery(this).attr('data-vrclang');
		if (langtag != '<?php echo $cur_langtab; ?>') {
			return true;
		}
		if (jQuery('#vrc_langcontent_'+langtag).length) {
			jQuery('.vrc_lang').val(langtag);
			jQuery('.vrc-translation-tab').removeClass('vrc-translation-tab-default');
			jQuery(this).addClass('vrc-translation-tab-default');
			jQuery('.vrc-translation-langcontent').hide();
			jQuery('#vrc_langcontent_'+langtag).fadeIn();
		}
	});
	<?php
}
?>
});
</script>
<?php
}
