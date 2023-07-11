<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$sel 	= $this->shortcode;
$views 	= $this->views;

$vik = VikApplication::getInstance();

?>

<form action="admin.php" method="post" name="adminForm" id="adminForm">

	<div id="poststuff">

		<?php echo $vik->openFieldset(JText::_('JSHORTCODE')); ?>

			<?php echo $vik->openControl(JText::_('JNAME').'*', '', array('id' => 'vik-name')); ?>
				<input type="text" id="vik-name" name="name" class="required" value="<?php echo JHtml::_('esc_attr', $sel['name']); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>

			<?php echo $vik->openControl(JText::_('JTYPE').'*', '', array('id' => 'vik-type')); ?>
				<select name="type" id="vik-type" class="required" onchange="shortcodeTypeValueChanged(this);">
					<option data-desc="" value="">--</option>
					<?php foreach ($this->views as $k => $v) { ?>
						<option data-desc="<?php echo htmlspecialchars(JText::_($v['desc'])); ?>" value="<?php echo JHtml::_('esc_attr', $k); ?>" <?php echo $k == $sel['type'] ? 'selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', JText::_($v['name'])); ?></option>
					<?php } ?>
				</select>
			<?php echo $vik->closeControl(); ?>

			<?php echo $vik->openControl(JText::_('JLANGUAGE')); ?>
				<select name="lang">
					<option value="*"><?php echo JText::_('JALL'); ?></option>
					<?php foreach (JLanguage::getKnownLanguages() as $tag => $lang) { ?>
						<option value="<?php echo JHtml::_('esc_attr', $tag); ?>" <?php echo $tag == $sel['lang'] ? 'selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $lang['nativeName']); ?></option>
					<?php } ?>
				</select>
			<?php echo $vik->closeControl(); ?>

			<div class="control">
				<span id="vik-type-desc"></span>
			</div>

		<?php echo $vik->closeFieldset(); ?>

		<div class="shortcode-params"></div>

	</div>

	<input type="hidden" name="id" value="<?php echo (int)$sel['id']; ?>" />
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo JHtml::_('esc_attr', $this->returnLink); ?>" />

</form>

<script>

	var validator = null;

	jQuery(document).ready(function() {

		validator = new JFormValidator('#adminForm');

		var typeSelect = jQuery('select[name="type"]');

		if (typeSelect.val().length) {
			shortcodeTypeValueChanged(typeSelect);
		}

	});

	function shortcodeTypeValueChanged(select) {

		validator.unregisterFields('.shortcode-params .required');

		doAjax('admin-ajax.php?option=com_vikrentcar&task=shortcode.params', {
			id: <?php echo (int)$sel['id']; ?>,
			type: jQuery(select).val()
		}, function(resp) {
			
			try {
				var html = JSON.parse(resp);
			} catch (e) {
				console.log(resp, e);
			}			

			jQuery('.shortcode-params').html(html);

			validator.registerFields('.shortcode-params .required');

			jQuery('#vik-type-desc').html(jQuery(select).find('option:selected').attr('data-desc'));

		});

	}

	Joomla.submitbutton = function(task) {

		if (task.indexOf('shortcode.save') == -1 || validator.validate()) {
			Joomla.submitform(task, document.adminForm);
		}

	}

</script>
