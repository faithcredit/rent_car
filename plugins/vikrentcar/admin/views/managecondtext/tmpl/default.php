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

JHtml::_('jquery.framework', true, true);

$vrc_app = VikRentCar::getVrcApplication();
$rules_helper = VikRentCar::getConditionalRulesInstance();
$rules_names = $rules_helper->getRuleNames();

$cond_text_rules = count($this->condtext) ? json_decode($this->condtext['rules']) : array();
$cond_text_rules = !is_array($cond_text_rules) ? array() : $cond_text_rules;

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));

// load language vars for JavaScript
JText::script('VRC_CONDTEXT_RULE_DISABLED');
JText::script('VRC_CONDTEXT_NORULES_SEL');
JText::script('VRC_CONDTEXT_RULE_RMCONF');

?>

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">

	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left vrc-config-maintab-left-halfsize">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRC_CONDTEXT_NAME'); ?></div>
							<div class="vrc-param-setting">
								<input type="text" id="condtextname" name="condtextname" value="<?php echo count($this->condtext) ? JHtml::_('esc_attr', $this->condtext['name']) : ''; ?>" size="30" onkeyup="vrcComposeToken(this.value);" required />
							</div>
						</div>
					</div>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRC_CONDTEXT_TKN'); ?></div>
							<div class="vrc-param-setting">
								<input type="text" id="condtexttkn" name="condtexttkn" value="<?php echo count($this->condtext) ? JHtml::_('esc_attr', $this->condtext['token']) : ''; ?>" size="30" readonly />
								<span class="vrc-param-setting-comment"><?php echo JText::_('VRC_CONDTEXT_TKN_HELP'); ?></span>
							</div>
						</div>
					</div>
					<div class="vrc-param-container">
						<div class="vrc-param-label"><?php echo JText::_('VRC_CONDTEXT_DEBUG_RULES'); ?></div>
						<div class="vrc-param-setting">
							<?php echo $vrc_app->printYesNoButtons('debug', JText::_('VRYES'), JText::_('VRNO'), (count($this->condtext) ? (int)$this->condtext['debug'] : 0), 1, 0); ?>
							<span class="vrc-param-setting-comment"><?php echo JText::_('VRC_CONDTEXT_DEBUG_RULES_HELP'); ?></span>
						</div>
					</div>
					<div class="vrc-param-container vrc-param-container-full">
						<div class="vrc-param-label vrc-param-label-above"><?php echo JText::_('VRC_CONDTEXT_MSG'); ?></div>
						<div class="vrc-param-setting">
							<?php
							if (interface_exists('Throwable')) {
								/**
								 * With PHP >= 7 supporting throwable exceptions for Fatal Errors
								 * we try to avoid issues with third party plugins that make use
								 * of the WP native function get_current_screen().
								 * 
								 * @wponly - but we also use it on Joomla @joomlaonly
								 */
								try {
									echo $editor->display( "msg", (count($this->condtext) ? $this->condtext['msg'] : ""), '100%', 300, 70, 20 );
								} catch (Throwable $t) {
									echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
								}
							} else {
								// we cannot catch Fatal Errors in PHP 5.x
								echo $editor->display( "msg", (count($this->condtext) ? $this->condtext['msg'] : ""), '100%', 300, 70, 20 );
							}
							?>
							<span class="vrc-param-setting-comment"><?php echo JText::_('VRC_CONDTEXT_MSG_HELP'); ?></span>
						</div>
					</div>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-setting">
								<p id="vrc-condtext-warn-norules" class="warn notice-noicon"<?php echo count($cond_text_rules) ? ' style="display: none;"' : ''; ?>><?php VikRentCarIcons::e('info-circle'); ?> <?php echo JText::_('VRC_CONDTEXT_WARN_NORULES'); ?></p>
								<button type="button" class="btn vrc-config-btn" onclick="vrcAddNewRule();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_CONDTEXT_ADDRULE'); ?></button>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

		<div id="vrc-condtext-rules-wrap" class="vrc-config-maintab-right vrc-config-maintab-right-halfsize">
		<?php
		// load all current rules with their respective params
		$rules_loaded = array();
		foreach ($cond_text_rules as $rule_data) {
			if (!is_object($rule_data) || empty($rule_data->id)) {
				continue;
			}
			$rule = $rules_helper->getRule($rule_data->id);
			if ($rule === false) {
				// rule object not found
				continue;
			}
			
			// get identifier
			$identifier = $rule->getIdentifier();

			// inject rule params
			$rule->setParams($rule_data->params);

			// check whether the rule object overrides the callbackAction method
			$supports_action = $rules_helper->supportsAction($rule);
			?>
			<fieldset class="adminform vrc-condtext-rule" data-ruleid="<?php echo $identifier; ?>" data-ruleaction="<?php echo (int)$supports_action; ?>">
				<div class="vrc-params-wrap">
					<legend class="adminlegend">
						<span class="vrc-rule-name"><?php echo $rule->getName(); ?></span>
						<span class="vrc-rule-trash" onclick="vrcRemoveRule(this);"><?php VikRentCarIcons::e('trash'); ?></span>
					</legend>
					<div class="vrc-params-container">
						<?php
						// display the params of this rule
						$rule->renderParams();

						// push the rule to the pool of the ones already rendered
						array_push($rules_loaded, $identifier);
						?>
					</div>
				</div>
			</fieldset>
			<?php
		}

		// keep loading the rules not present, but hide them by default
		foreach ($rules_helper->getRules() as $rule) {
			$identifier = $rule->getIdentifier();
			if (in_array($identifier, $rules_loaded)) {
				continue;
			}
			
			// unset the params for this rule
			$rule->setParams(null);

			// check whether the rule object overrides the callbackAction method
			$supports_action = $rules_helper->supportsAction($rule);
			?>
			<fieldset class="adminform vrc-condtext-rule vrc-condtext-rule-ghost" style="display: none;" data-ruleid="<?php echo $identifier; ?>" data-ruleaction="<?php echo (int)$supports_action; ?>">
				<div class="vrc-params-wrap">
					<legend class="adminlegend">
						<span class="vrc-rule-name"><?php echo $rule->getName(); ?></span>
						<span class="vrc-rule-trash" onclick="vrcRemoveRule(this);"><?php VikRentCarIcons::e('trash'); ?></span>
					</legend>
					<div class="vrc-params-container">
						<?php
						// display the params of this rule
						$rule->renderParams();
						?>
					</div>
				</div>
			</fieldset>
			<?php
		}
		?>
			<div class="vrc-condtext-addrule-helper">
				<p id="vrc-condtext-warn-norules-scnd" class="warn notice-noicon"<?php echo count($cond_text_rules) ? ' style="display: none;"' : ''; ?>><?php VikRentCarIcons::e('info-circle'); ?> <?php echo JText::_('VRC_CONDTEXT_WARN_NORULES'); ?></p>
				<button id="vrc-condtext-addrule-scnd" type="button" class="btn vrc-config-btn" onclick="vrcAddNewRule();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_CONDTEXT_ADDRULE'); ?></button>
			</div>
		</div>

	</div>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar">
<?php
if (count($this->condtext)) {
	?>
	<input type="hidden" name="where" value="<?php echo $this->condtext['id']; ?>">
	<?php
}
?>
	<?php echo JHtml::_('form.token'); ?>
</form>

<div class="vrc-modal-overlay-block vrc-modal-overlay-block-condtext">
	<a class="vrc-modal-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-modal-overlay-content vrc-modal-overlay-content-tall vrc-modal-overlay-content-condtext">
		<div class="vrc-modal-overlay-content-head vrc-modal-overlay-content-head-condtext">
			<h3><span><?php echo JText::_('VRC_CONDTEXT_ADDRULE'); ?></span> <span class="vrc-modal-overlay-close-times" onclick="hideVrcModalCondtext();">&times;</span></h3>
		</div>
		<div class="vrc-modal-overlay-content-body vrc-modal-overlay-content-body-scroll">
			<div class="vrc-modal-condtext-rules">
			<?php
			foreach ($rules_names as $rule) {
				$rule_used = in_array($rule->id, $rules_loaded);
				?>
				<div class="vrc-modal-condtext-rule<?php echo $rule_used ? ' vrc-modal-condtext-rule-disabled' : ''; ?>" onclick="vrcSelectRule(this);" data-ruleid="<?php echo $rule->id; ?>">
					<h5><?php echo $rule->name; ?></h5>
					<div class="vrc-condtext-rule-descr">
						<?php echo $rule->descr; ?>
					</div>
				</div>
				<?php
			}
			?>
			</div>
		</div>
		<div class="vrc-modal-overlay-content-footer">
			<div class="vrc-modal-overlay-content-footer-right">
				<button type="button" class="btn btn-success" onclick="vrcAddRuleToDoc();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_CONDTEXT_ADDRULE'); ?></button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	/**
	 * Declare global scope variables.
	 */
	var vrc_modal_condtext_on = false;

	/**
	 * Composes the token from the name of the rule.
	 */
	function vrcComposeToken(name) {
		// convert to lower case and replace whitespaces with underscore
		name = (name + '').toLowerCase().replace(/\s/g, '_');
		// convert anything except [0-9A-Za-z_] to an empty string
		name = name.replace(/\W/g, '');
		// compose token string
		var tkn = '{condition: ' + name + '}';
		// set token string
		document.getElementById('condtexttkn').value = tkn;
	}

	/**
	 * Shows the modal window
	 */
	function vrcOpenModalCondtext() {
		jQuery('.vrc-modal-overlay-block-condtext').show();
		vrc_modal_condtext_on = true;
	}

	/**
	 * Hides the modal window
	 */
	function hideVrcModalCondtext() {
		if (vrc_modal_condtext_on === true) {
			jQuery(".vrc-modal-overlay-block-condtext").fadeOut(400, function () {
				jQuery(".vrc-modal-overlay-content-condtext").show();
			});
			// turn flag off
			vrc_modal_condtext_on = false;
		}
	}

	/**
	 * Displays the modal for adding a new rule.
	 */
	function vrcAddNewRule() {
		vrcOpenModalCondtext();
	}

	/**
	 * Adds the selected rule to the document.
	 */
	function vrcAddRuleToDoc() {
		var rule_sel = jQuery('.vrc-modal-condtext-rule-selected').not('.vrc-modal-condtext-rule-disabled');
		if (!rule_sel.length) {
			// no rules selected
			alert(Joomla.JText._('VRC_CONDTEXT_NORULES_SEL'));
			return false;
		}
		var rule_id = rule_sel.first().attr('data-ruleid');
		if (!rule_id || !rule_id.length) {
			alert(Joomla.JText._('VRC_CONDTEXT_NORULES_SEL'));
			console.error('rule ID is empty', rule_sel);
			return false;
		}
		// append the "ghost" (not yet used) rule to the document
		var rule_wrap = jQuery('.vrc-condtext-rule-ghost[data-ruleid="' + rule_id + '"]');
		if (!rule_wrap.length) {
			alert(Joomla.JText._('VRC_CONDTEXT_NORULES_SEL'));
			console.error('could not find ghost rule with ID ' + rule_id);
			return false;
		}
		// find the element to append the new rule
		if (jQuery('.vrc-condtext-rule').not('.vrc-condtext-rule-ghost').length) {
			// append this rule to the last one used
			var append_to = jQuery('.vrc-condtext-rule').not('.vrc-condtext-rule-ghost').last();
			// move rule by showing it and by removing the ghost class
			rule_wrap.show().removeClass('vrc-condtext-rule-ghost').insertAfter(append_to);
		} else {
			// append it to the rules wrapper, as the first rule
			var append_to = jQuery('#vrc-condtext-rules-wrap').find('.vrc-condtext-addrule-helper');
			// move rule by showing it and by removing the ghost class
			rule_wrap.show().removeClass('vrc-condtext-rule-ghost').insertBefore(append_to);
		}
		// hide warning messages
		jQuery('#vrc-condtext-warn-norules').hide();
		jQuery('#vrc-condtext-warn-norules-scnd').hide();
		// close modal
		hideVrcModalCondtext();
		// disable this rule from the modal window
		rule_sel.first().removeClass('vrc-modal-condtext-rule-selected').addClass('vrc-modal-condtext-rule-disabled');
		if (jQuery('.vrc-condtext-rule').not('.vrc-condtext-rule-ghost').length > 0) {
			// show second add rule button
			// jQuery('#vrc-condtext-addrule-scnd').show();
		}
		// animate scroll to the new rule position
		jQuery('html,body').animate({scrollTop: rule_wrap.offset().top - 40}, {duration: 400});
	}

	/**
	 * Selects a rule or adds the selected rule.
	 */
	function vrcSelectRule(rule) {
		var elem = jQuery(rule);
		if (elem.hasClass('vrc-modal-condtext-rule-disabled')) {
			alert(Joomla.JText._('VRC_CONDTEXT_RULE_DISABLED'));
			return false;
		}
		if (elem.hasClass('vrc-modal-condtext-rule-selected')) {
			// add rule to document
			vrcAddRuleToDoc();
			return true;
		}
		// unset any selected class from other rules
		jQuery('.vrc-modal-condtext-rule').removeClass('vrc-modal-condtext-rule-selected');
		// add selected class to this rule
		elem.addClass('vrc-modal-condtext-rule-selected');
	}

	/**
	 * Removes (hides) a rule from the document.
	 */
	function vrcRemoveRule(rule) {
		var elem = jQuery(rule).closest('.vrc-condtext-rule');
		if (!confirm(Joomla.JText._('VRC_CONDTEXT_RULE_RMCONF'))) {
			return false;
		}
		// find the ID of the rule to remove
		var rule_id = elem.attr('data-ruleid');
		if (!rule_id || !rule_id.length) {
			alert(Joomla.JText._('VRC_CONDTEXT_NORULES_SEL'));
			console.error('rule ID is empty', rule, elem);
			return false;
		}
		// enable this rule in the modal from next selection
		jQuery('.vrc-modal-condtext-rule[data-ruleid="' + rule_id + '"]').removeClass('vrc-modal-condtext-rule-disabled vrc-modal-condtext-rule-selected');
		// move this rule to the "ghost" list
		var append_to = null;
		if (jQuery('.vrc-condtext-rule-ghost').length) {
			// move it after the last ghost rule found
			append_to = jQuery('.vrc-condtext-rule-ghost').last();
		} else if (jQuery('.vrc-condtext-rule').not('.vrc-condtext-rule-ghost').length > 1) {
			// move it as the first ghost rule, after the last rule used
			append_to = jQuery('.vrc-condtext-rule').not('.vrc-condtext-rule-ghost').last();
		}
		// add ghost class and hide rule
		elem.addClass('vrc-condtext-rule-ghost').hide();
		// make any input field for this rule empty, or when saving the conditional text this rule will be applied
		elem.find('input, select, textarea').val('').trigger('change');
		if (append_to !== null) {
			// move rule only if more than one available
			elem.insertAfter(append_to);
		}
		// show warning messages if no more rules
		if (!jQuery('.vrc-condtext-rule').not('.vrc-condtext-rule-ghost').length) {
			jQuery('#vrc-condtext-warn-norules').show();
			jQuery('#vrc-condtext-warn-norules-scnd').show();
			// hide second add rule button
			// jQuery('#vrc-condtext-addrule-scnd').hide();
		}
	}

	/**
	 * Declares document ready event processes.
	 */
	jQuery(document).ready(function() {
		
		/**
		 * Dismiss modal window with Esc.
		 */
		jQuery(document).keydown(function(e) {
			if (e.keyCode == 27) {
				if (vrc_modal_condtext_on === true) {
					hideVrcModalCondtext();
				}
			}
		});

		/**
		 * Dismiss modal window by clicking on an external element.
		 */
		jQuery(document).mouseup(function(e) {
			if (!vrc_modal_condtext_on) {
				return false;
			}
			if (vrc_modal_condtext_on) {
				var vrc_overlay_cont = jQuery(".vrc-modal-overlay-content-condtext");
				if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
					hideVrcModalCondtext();
				}
			}
		});

	});
</script>
