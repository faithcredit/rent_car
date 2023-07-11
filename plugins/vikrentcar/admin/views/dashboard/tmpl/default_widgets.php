<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$vrc_app = VikRentCar::getVrcApplication();
$widgets_helper = VikRentCar::getAdminWidgetsInstance();
$widgets_map = $widgets_helper->getWidgetsMap();
$widgets_names = $widgets_helper->getWidgetNames();
$widgets_welcome = $widgets_helper->showWelcome();

// global permissions are necessary to customize the admin widgets
$vrc_auth_global = JFactory::getUser()->authorise('core.vrc.gsettings', 'com_vikrentcar');

// load sortable library
JHtml::_('jquery.framework', true, true);
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.sortable.min.js');

// load language vars for JavaScript
JText::script('VRC_WIDGETS_WELCOME');
JText::script('VRC_WIDGETS_ADDWIDGCONT');
JText::script('VRC_WIDGETS_RESTDEFAULT');
JText::script('VRC_WIDGETS_ADDNEWWIDG');
JText::script('VRC_WIDGETS_SAVINGMAP');
JText::script('VRC_WIDGETS_ERRSAVINGMAP');
JText::script('VRC_WIDGETS_LASTUPD');
JText::script('VRC_WIDGETS_ENTERSECTNAME');
JText::script('VRC_WIDGETS_NEWSECT');
JText::script('VRC_WIDGETS_CONFRMELEM');
JText::script('VRC_WIDGETS_SELCONTSIZE');
JText::script('VRC_WIDGETS_UPDWIDGCONT');
JText::script('VRC_WIDGETS_EDITWIDGCONT');
JText::script('VRC_WIDGETS_ERRDISPWIDG');
JText::script('VRC_STICKYN_TITLE');
JText::script('VRC_STICKYN_TEXT');
JText::script('VRC_STICKYN_TEXT2');
JText::script('VRC_STICKYN_CUSTOMURI');
JText::script('VRC_MISSING_REQFIELDS');
JText::script('VRELIMINA');

?>
<div class="vrc-admin-widgets-wrap">
<?php
if ($vrc_auth_global) {
?>
	<div class="vrc-admin-widgets-commands">
		<div class="vrc-admin-widgets-commands-info" data-vrcmanagewidgets="1" style="display: none;">
			<div class="vrc-admin-widgets-commands-info-inner">
				<span class="vrc-admin-widgets-commands-info-txt"><?php echo JText::_('VRC_WIDGETS_AUTOSAVE'); ?></span>
				<div class="vrc-admin-widgets-commands-info-restore">
					<a href="index.php?option=com_vikrentcar&task=reset_admin_widgets" class="btn btn-secondary" onclick="return vrcWidgetsRestoreMap();"><?php echo JText::_('VRC_WIDGETS_RESTDEFAULTSHORT'); ?></a>
				</div>
			</div>
		</div>
		<div class="vrc-admin-widgets-commands-mng">
			<span class="vrc-admin-widgets-commands-mng-toggle"><?php echo $vrc_app->printYesNoButtons('vrccustwidgets', JText::_('VRYES'), JText::_('VRNO'), 0, 1, 0, 'vrcWidgetsToggleManage(false);'); ?></span>
			<span class="vrc-admin-widgets-commands-mng-lbl" onclick="vrcWidgetsToggleManage(true);"><?php VikRentCarIcons::e('cogs'); ?> <?php echo JText::_('VRC_WIDGETS_CUSTWIDGETS'); ?></span>
		</div>
	</div>
<?php
}
?>
	<div class="vrc-admin-widgets-list">
	<?php
	foreach ($widgets_map->sections as $seck => $section) {
		?>
		<div class="vrc-admin-widgets-section">
			<div class="vrc-admin-widgets-section-name" data-vrcmanagewidgets="1" style="display: none;">
				<span class="vrc-admin-widgets-elem-cmds-drag"><?php VikRentCarIcons::e('ellipsis-v'); ?></span>
				<span class="vrc-admin-widgets-section-name-val"><?php echo $section->name; ?></span>
				<div class="vrc-admin-widget-elem-cmds vrc-admin-widgets-section-cmds">
					<span class="vrc-admin-widgets-elem-cmds-edit" onclick="vrcWidgetsEditSection(this);"><?php VikRentCarIcons::e('edit'); ?></span>
					<span class="vrc-admin-widgets-elem-cmds-remove" onclick="vrcWidgetsRemoveSection(this);"><?php VikRentCarIcons::e('trash'); ?></span>
				</div>
			</div>
		<?php
		if (!isset($section->containers)) {
			$section->containers = array();
		}
		$tot_containers = count($section->containers);
		foreach ($section->containers as $conk => $container) {
			$container_css = $widgets_helper->getContainerCssClass($container->size);
			?>
			<div class="vrc-admin-widgets-container <?php echo $container_css; ?>" data-vrcwidgetcontsize="<?php echo $container->size; ?>" data-totcontainers="<?php echo $tot_containers; ?>">
				<div class="vrc-admin-widgets-container-name" data-vrcmanagewidgets="1" style="display: none;">
					<span class="vrc-admin-widgets-container-name-val"><?php echo $widgets_helper->getContainerName($container->size); ?></span>
					<div class="vrc-admin-widget-elem-cmds vrc-admin-widgets-container-cmds">
						<span class="vrc-admin-widgets-elem-cmds-edit" onclick="vrcWidgetsEditContainer(this);"><?php VikRentCarIcons::e('edit'); ?></span>
						<span class="vrc-admin-widgets-elem-cmds-remove" onclick="vrcWidgetsRemoveContainer(this);"><?php VikRentCarIcons::e('trash'); ?></span>
					</div>
				</div>
			<?php
			if (!isset($container->widgets)) {
				$container->widgets = array();
			}
			foreach ($container->widgets as $widk => $widget_id) {
				$widget_instance = $widgets_helper->getWidget($widget_id);
				if ($widget_instance === false) {
					continue;
				}
				?>
				<div class="vrc-admin-widgets-widget" data-vrcwidgetid="<?php echo $widget_instance->getIdentifier(); ?>">
					<div class="vrc-admin-widgets-widget-info" data-vrcmanagewidgets="1" style="display: none;">
						<div class="vrc-admin-widgets-widget-info-inner">
							<div class="vrc-admin-widgets-widget-details">
								<span class="vrc-admin-widgets-widget-info-drag"><?php VikRentCarIcons::e('ellipsis-v'); ?></span>
								<h4 class="vrc-admin-widgets-widget-info-name">
									<span><?php echo $widget_instance->getName(); ?></span>
									<span class="vrc-admin-widgets-widget-remove" onclick="vrcWidgetsRemoveWidget(this);"><?php VikRentCarIcons::e('trash'); ?></span>
								</h4>
							</div>
							<div class="vrc-admin-widgets-widget-info-descr"><?php echo $widget_instance->getDescription(); ?></div>
						</div>
					</div>
					<div class="vrc-admin-widgets-widget-output">
						<?php $widget_instance->render(); ?>
					</div>
				</div>
				<?php
			}
			?>
				<div class="vrc-admin-widgets-widget vrc-admin-widgets-widget-addnew" data-vrcmanagewidgets="1" style="display: none;">
					<div class="vrc-admin-widgets-plus-box" onclick="vrcWidgetsAddWidget(this);">
						<span><?php VikRentCarIcons::e('plus-circle'); ?></span>
					</div>
				</div>
			</div>
			<?php
		}
		?>
			<div class="vrc-admin-widgets-container vrc-admin-widgets-container-addnew" data-vrcmanagewidgets="1" style="display: none;">
				<div class="vrc-admin-widgets-plus-box" onclick="vrcWidgetsAddContainer(this);">
					<span><?php VikRentCarIcons::e('plus-circle'); ?></span>
				</div>
			</div>
		</div>
		<?php
	}
	?>
		<div class="vrc-admin-widgets-section vrc-admin-widgets-section-addnew" data-vrcmanagewidgets="1" style="display: none;">
			<div class="vrc-admin-widgets-plus-box" onclick="vrcWidgetsAddSection();">
				<span><?php VikRentCarIcons::e('plus-circle'); ?></span>
			</div>
		</div>
	</div>
</div>

<div class="vrc-modal-overlay-block vrc-modal-overlay-block-dashwidgets">
	<a class="vrc-modal-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-modal-overlay-content vrc-modal-overlay-content-dashwidgets">
		<div class="vrc-modal-overlay-content-head vrc-modal-overlay-content-head-dashwidgets">
			<h3><span id="vrc-modal-widgets-title"></span> <span class="vrc-modal-overlay-close-times" onclick="hideVrcModalWidgets();">&times;</span></h3>
		</div>
		<div class="vrc-modal-overlay-content-body vrc-modal-overlay-content-body-scroll">
			<div class="vrc-modal-widgets-newcontainer vrc-modal-widgets-forms" style="display: none;">
				<div class="vrc-modal-widgets-form-data-fields">
					<div class="vrc-modal-widgets-form-data-field">
						<label for="vrc-newcontainer-size"><?php echo JText::_('VRC_WIDGETS_CONTSIZE'); ?></label>
						<select id="vrc-newcontainer-size">
						<?php
						foreach ($widgets_helper->getContainerClassNames() as $class_key => $class_name_data) {
							?>
							<option value="<?php echo $class_key; ?>" data-cssclass="<?php echo $class_name_data['css']; ?>"><?php echo $class_name_data['name']; ?></option>
							<?php
						}
						?>
						</select>
						<input type="hidden" id="vrc-newcontainer-upd" value="0" />
					</div>
					<div class="vrc-modal-widgets-form-data-field vrc-modal-widgets-form-data-field-save">
						<button type="button" class="btn btn-success" id="vrc-newcontainer-btn" onclick="vrcWidgetsAddContainerToDoc();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_WIDGETS_ADDWIDGCONT'); ?></button>
					</div>
				</div>
			</div>
			<div class="vrc-modal-widgets-newwidget vrc-modal-widgets-forms" style="display: none;">
				<div class="vrc-modal-widgets-form-data-fields">
					<div class="vrc-modal-widgets-form-data-field">
						<label for="vrc-newwidget-id"><?php echo JText::_('VRC_WIDGETS_SELWIDGADD'); ?></label>
						<select id="vrc-newwidget-id" onchange="vrcWidgetSetNewDescr(this.value);">
							<option value=""></option>
						<?php
						foreach ($widgets_names as $widget_data) {
							?>
							<option value="<?php echo $widget_data->id; ?>"><?php echo $widget_data->name; ?></option>
							<?php
						}
						?>
						</select>
					</div>
					<div class="vrc-modal-widgets-form-data-field vrc-newwidget-descr" style="display: none;"></div>
					<div class="vrc-modal-widgets-form-data-field vrc-modal-widgets-form-data-field-save">
						<button type="button" class="btn btn-success" onclick="vrcWidgetsAddWidgetToDoc();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_WIDGETS_ADDNEWWIDG'); ?></button>
					</div>
				</div>
			</div>
		<?php
		if ($widgets_welcome) {
			?>
			<div class="vrc-widgets-welcome-wrap vrc-modal-widgets-forms" style="display: none;">
				<div class="vrc-widgets-welcome-inner">
					<p><?php echo JText::_('VRC_WIDGETS_WELCOME_DESC1'); ?></p>
					<p><?php echo JText::_('VRC_WIDGETS_WELCOME_DESC2'); ?></p>
					<div class="vrc-widgets-welcome-demo">
						<div class="vrc-widgets-welcome-demo-section">
							<span class="vrc-widgets-welcome-demo-section-lbl"><?php echo JText::_('VRC_WIDGETS_NEWSECT'); ?></span>
							<div class="vrc-widgets-welcome-demo-container">
								<span class="vrc-widgets-welcome-demo-container-lbl"><?php echo JText::_('VRC_WIDGETS_ADDWIDGCONT'); ?></span>
								<div class="vrc-widgets-welcome-demo-widget">
									<span class="vrc-widgets-welcome-demo-widget-lbl"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_WIDGETS_ADDNEWWIDG'); ?></span>
								</div>
							</div>
							<div class="vrc-widgets-welcome-demo-container">
								<span class="vrc-widgets-welcome-demo-container-lbl"><?php echo JText::_('VRC_WIDGETS_ADDWIDGCONT'); ?></span>
								<div class="vrc-widgets-welcome-demo-widget">
									<span class="vrc-widgets-welcome-demo-widget-lbl"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_WIDGETS_ADDNEWWIDG'); ?></span>
								</div>
							</div>
						</div>
					</div>
					<div class="vrc-widgets-welcome-actions">
						<div class="vrc-widgets-welcome-action">
							<button type="button" class="btn btn-success" onclick="vrcWidgetsCloseWelcome(0);"><?php echo JText::_('VRCBTNKEEPREMIND'); ?></button>
						</div>
						<div class="vrc-widgets-welcome-action">
							<button type="button" class="btn btn-secondary" onclick="vrcWidgetsCloseWelcome(1);"><?php echo JText::_('VRCBTNDONTREMIND'); ?></button>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		</div>
	</div>
</div>

<script type="text/javascript">
	/**
	 * Declare global scope variables.
	 */
	var vrc_admin_widgets_map = <?php echo json_encode($widgets_map); ?>;
	var vrc_admin_widgets_names = <?php echo json_encode($widgets_names); ?>;

	var vrc_admin_widgets_last_section = null,
		vrc_admin_widgets_last_container = null,
		vrc_admin_widgets_initpos_section = 0,
		vrc_admin_widgets_initpos_container = 0,
		vrc_admin_widgets_initpos_widget = 0,
		vrc_admin_widgets_initinst_widget = -1,
		vrc_admin_widgets_allow_drop = false;

	var vrc_modal_widgets_on = false;

	var vrc_admin_widgets_welcome = <?php echo $widgets_welcome ? 'true' : 'false'; ?>;

	/**
	 * Checks wether a jQuery XHR response object was due to a connection error.
	 * Property readyState 0 = Network Error (UNSENT), 4 = HTTP error (DONE).
	 * Property responseText may not be set in some browsers.
	 * This is what to check to determine if a connection error occurred.
	 */
	function vrcIsConnectionLostError(err) {
		if (!err || !err.hasOwnProperty('status')) {
			return false;
		}

		return (
			err.statusText == 'error'
			&& err.status == 0
			&& (err.readyState == 0 || err.readyState == 4)
			&& (!err.hasOwnProperty('responseText') || err.responseText == '')
		);
	}

	/**
	 * Ensures AJAX requests that fail due to connection errors are retried automatically.
	 */
	function vrcDoAjax(url, data, success, failure, attempt) {
		var VRC_AJAX_MAX_ATTEMPTS = 3;

		if (attempt === undefined) {
			attempt = 1;
		}

		return jQuery.ajax({
			type: 'POST',
			url: url,
			data: data
		}).done(function(resp) {
			if (success !== undefined) {
				// launch success callback function
				success(resp);
			}
		}).fail(function(err) {
			/**
			 * If the error is caused by a site connection lost, and if the number
			 * of retries is lower than max attempts, retry the same AJAX request.
			 */
			if (attempt < VRC_AJAX_MAX_ATTEMPTS && vrcIsConnectionLostError(err)) {
				// delay the retry by half second
				setTimeout(function() {
					// relaunch same request and increase number of attempts
					console.log('Retrying previous AJAX request');
					vrcDoAjax(url, data, success, failure, (attempt + 1));
				}, 500);
			} else {
				// launch the failure callback otherwise
				if (failure !== undefined) {
					failure(err);
				}
			}

			// always log the error in console
			console.log('AJAX request failed' + (err.status == 500 ? ' (' + err.responseText + ')' : ''), err);
		});
	}

	/**
	 * Shows the modal window
	 */
	function vrcOpenModalWidgets() {
		jQuery('.vrc-modal-overlay-block-dashwidgets').show();
		vrc_modal_widgets_on = true;
	}

	/**
	 * Hides the modal window
	 */
	function hideVrcModalWidgets() {
		if (vrc_modal_widgets_on === true) {
			jQuery(".vrc-modal-overlay-block-dashwidgets").fadeOut(400, function () {
				jQuery(".vrc-modal-overlay-content-dashwidgets").show();
				jQuery(".vrc-modal-widgets-forms").hide();
			});
			// turn flag off
			vrc_modal_widgets_on = false;
		}
	}

	/**
	 * Toggles the widget customizer mode
	 */
	function vrcWidgetsToggleManage(trigger) {
		if (trigger === true) {
			jQuery('input[name="vrccustwidgets"]').trigger('click');
			return;
		}
		if (!jQuery('input[name="vrccustwidgets"]').is(':checked')) {
			jQuery('div[data-vrcmanagewidgets="1"]').hide();
			jQuery('.vrc-admin-widgets-widget-output').show();
			jQuery('.vrc-admin-widgets-list').removeClass('vrc-admin-widgets-list-customize');
		} else {
			jQuery('.vrc-admin-widgets-widget-output').hide();
			jQuery('div[data-vrcmanagewidgets="1"]').show();
			jQuery('.vrc-admin-widgets-list').addClass('vrc-admin-widgets-list-customize');
			// show welcome (if necessary)
			vrcWidgetsShowWelcome();
		}
	}

	/**
	 * Opens the modal window with the welcome message for the admin widgets customizer.
	 */
	function vrcWidgetsShowWelcome() {
		if (!vrc_admin_widgets_welcome || !jQuery('.vrc-widgets-welcome-wrap').length) {
			return;
		}
		// prevent this from being displayed again in the same page flow
		vrc_admin_widgets_welcome = false;
		// display welcome container
		jQuery('.vrc-widgets-welcome-wrap').show();
		// set modal title
		jQuery('#vrc-modal-widgets-title').text(Joomla.JText._('VRC_WIDGETS_WELCOME'));
		// display modal
		vrcOpenModalWidgets();
		// declare timeouts to add the animate class to the welcome elements
		setTimeout(function() {
			// animate container
			jQuery('.vrc-widgets-welcome-demo-section').addClass('vrc-widgets-welcome-animate');
		}, 1000);
		setTimeout(function() {
			// animate first section
			jQuery('.vrc-widgets-welcome-demo-container').first().addClass('vrc-widgets-welcome-animate');
		}, 2000);
		setTimeout(function() {
			// animate last section
			jQuery('.vrc-widgets-welcome-demo-container').last().addClass('vrc-widgets-welcome-animate');
		}, 3000);
		setTimeout(function() {
			// animate widgets
			jQuery('.vrc-widgets-welcome-demo-widget').addClass('vrc-widgets-welcome-animate');
		}, 4000);
	}

	/**
	 * Closes the modal window for the welcome text by storing an action.
	 */
	function vrcWidgetsCloseWelcome(hidenext) {
		// dismiss modal
		hideVrcModalWidgets();
		// AJAX request to update the welcome status
		vrcDoAjax(
			'index.php',
			{
				option: "com_vikrentcar",
				task: "admin_widgets_welcome",
				hide_welcome: hidenext,
				tmpl: "component"
			},
			function(response) {
				try {
					var obj_res = JSON.parse(response);
					if (!obj_res.hasOwnProperty('status')) {
						// request failed
						console.error('Could not update welcome status', obj_res);
					}
				} catch(err) {
					console.error('could not parse JSON response when updating the welcome status', err, response);
				}
			},
			function(error) {
				console.error(error);
			}
		);
	}

	/**
	 * Throttle guarantees a constant flow of events at a given time interval,
	 * but it runs immediately when the event takes place. We rather need a
	 * debounce technique to group a flurry of events into one single event.
	 * This is useful for listening to the save-map event of the document.
	 * Some widgets may use this function as well.
	 */
	function vrcDebounceEvent(func, wait, immediate) {
		var timeout;
		return function() {
			var context = this, args = arguments;
			var later = function() {
				timeout = null;
				if (!immediate) func.apply(context, args);
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) {
				func.apply(context, args);
			}
		}
	}

	/**
	 * We do not need a throttle technique, but this method would
	 * throttle the save-map event rather than debouncing it.
	 */
	function vrcThrottleEvent(method, delay) {
		var time = Date.now();
		return function() {
			if ((time + delay - Date.now()) < 0) {
				method();
				time = Date.now();
			}
		}
	}

	/**
	 * This will fire during the throttle of the save-map event.
	 * Saves the updated admin widgets map onto the database.
	 */
	function vrcHandleMapSaving() {
		// update info status to "saving..."
		jQuery('.vrc-admin-widgets-commands-info-txt').removeClass('vrc-admin-widgets-error').html('<?php VikRentCarIcons::e('refresh', 'fa-spin fa-fw'); ?> ' + Joomla.JText._('VRC_WIDGETS_SAVINGMAP'));

		// prepare AJAX request data
		var saving_request = {
			option: "com_vikrentcar",
			task: "save_admin_widgets",
			tmpl: "component"
		}
		Object.assign(saving_request, vrc_admin_widgets_map);

		// make the request
		vrcDoAjax(
			'index.php',
			saving_request,
			function(response) {
				try {
					var obj_res = JSON.parse(response);
					if (!obj_res.status) {
						// request failed
						console.error('Could not update the map', obj_res);
						// update info status to "error..."
						jQuery('.vrc-admin-widgets-commands-info-txt').addClass('vrc-admin-widgets-error').text(Joomla.JText._('VRC_WIDGETS_ERRSAVINGMAP'));
					} else {
						// set last updated time
						var now = new Date;
						var hours = now.getHours();
						hours = hours < 10 ? '0' + hours : hours;
						var minutes = now.getMinutes();
						minutes = minutes < 10 ? '0' + minutes : minutes;
						var seconds = now.getSeconds();
						seconds = seconds < 10 ? '0' + seconds : seconds;
						var full_time_now = hours + ':' + minutes + ':' + seconds;
						// update info status to "last update time"
						jQuery('.vrc-admin-widgets-commands-info-txt').removeClass('vrc-admin-widgets-error').html('<?php VikRentCarIcons::e('check-circle'); ?> ' + Joomla.JText._('VRC_WIDGETS_LASTUPD') + ': ' + full_time_now);
					}
				} catch(err) {
					console.error('could not parse JSON response when updating the map', err, response);
					// update info status to "error..."
					jQuery('.vrc-admin-widgets-commands-info-txt').addClass('vrc-admin-widgets-error').text(Joomla.JText._('VRC_WIDGETS_ERRSAVINGMAP'));
				}
			},
			function(error) {
				console.error(error);
				// update info status to "error..."
				jQuery('.vrc-admin-widgets-commands-info-txt').addClass('vrc-admin-widgets-error').text(Joomla.JText._('VRC_WIDGETS_ERRSAVINGMAP'));
			}
		);
	}

	/**
	 * Makes all sections sortable. Do not use .disableSelection() or this
	 * will break all [contenteditable] elements and their focus/selection events.
	 */
	function vrcMakeSectionsSortable() {
		jQuery('.vrc-admin-widgets-list').sortable({
			axix: 'x',
			cursor: 'move',
			handle: '.vrc-admin-widgets-section-name .vrc-admin-widgets-elem-cmds-drag',
			items: '.vrc-admin-widgets-section:not(.vrc-admin-widgets-section-addnew)',
			revert: false,
			start: function(event, ui) {
				// update global initial position for section
				vrc_admin_widgets_initpos_section = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(jQuery(ui.item));
			},
			update: function(event, ui) {
				var new_sect_index = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(jQuery(ui.item));
				// update global map object - move originial section to new position
				vrc_admin_widgets_map.sections.splice(new_sect_index, 0, vrc_admin_widgets_map.sections.splice(vrc_admin_widgets_initpos_section, 1)[0]);

				// trigger the save-map event
				document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));
			}
		});
	}

	/**
	 * Makes all widgets sortable. Do not use .disableSelection() or this
	 * will break all [contenteditable] elements and their focus/selection events.
	 */
	function vrcMakeWidgetsSortable() {
		jQuery('.vrc-admin-widgets-container').not('.vrc-admin-widgets-container-addnew').sortable({
			connectWith: '.vrc-admin-widgets-container:not(.vrc-admin-widgets-container-addnew)',
			cursor: 'move',
			dropOnEmpty: true,
			handle: '.vrc-admin-widgets-widget-details .vrc-admin-widgets-widget-info-drag',
			helper: 'clone',
			items: '.vrc-admin-widgets-widget:not(.vrc-admin-widgets-widget-addnew)',
			placeholder: 'vrc-admin-widgets-container-tmpdrop',
			revert: false,
			start: function(event, ui) {
				// allow drop
				vrc_admin_widgets_allow_drop = true;
				// calculate initial positions
				var initial_widget = jQuery(ui.item);
				var initial_section = initial_widget.closest('.vrc-admin-widgets-section');
				var initial_container = initial_widget.closest('.vrc-admin-widgets-container');
				// update global initial position for section
				vrc_admin_widgets_initpos_section = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(initial_section);
				// update global initial position for container
				vrc_admin_widgets_initpos_container = initial_section.find('.vrc-admin-widgets-container').not('.vrc-admin-widgets-container-addnew').index(initial_container);
				// update global initial position for widget
				vrc_admin_widgets_initpos_widget = initial_container.find('.vrc-admin-widgets-widget').not('.vrc-admin-widgets-widget-addnew').index(initial_widget);
				// update global initial instance index for this type of widget
				var widget_type = initial_widget.attr('data-vrcwidgetid');
				vrc_admin_widgets_initinst_widget = jQuery('.vrc-admin-widgets-widget[data-vrcwidgetid="' + widget_type + '"]').index(initial_widget);
			},
			update: function(event, ui) {
				var dropped_widget = jQuery(ui.item);
				var dropped_section = dropped_widget.closest('.vrc-admin-widgets-section');
				var dropped_container = dropped_widget.closest('.vrc-admin-widgets-container');
				// calculate new element positions
				var new_sect_index = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(dropped_section);
				var new_cont_index = dropped_section.find('.vrc-admin-widgets-container').not('.vrc-admin-widgets-container-addnew').index(dropped_container);
				var new_widg_index = dropped_container.find('.vrc-admin-widgets-widget').not('.vrc-admin-widgets-widget-addnew').index(dropped_widget);

				if (new_sect_index != vrc_admin_widgets_initpos_section || new_cont_index != vrc_admin_widgets_initpos_container) {
					/**
					 * Widget has been moved to a connected list, to a different section or container.
					 * Multiple "update" events will be fired, one for each target, so 2 in total.
					 */
					if (vrc_admin_widgets_allow_drop !== true) {
						// both events contain the same dropped target information (ui), so we skip any later event
						return;
					}

					// disable drop for any sub-sequent event
					vrc_admin_widgets_allow_drop = false;

					// update global map object - remove original widget
					vrc_admin_widgets_map.sections[vrc_admin_widgets_initpos_section]['containers'][vrc_admin_widgets_initpos_container]['widgets'].splice(vrc_admin_widgets_initpos_widget, 1);

					// update global map object - push new widget
					vrc_admin_widgets_map.sections[new_sect_index]['containers'][new_cont_index]['widgets'].splice(new_widg_index, 0, dropped_widget.attr('data-vrcwidgetid'));
				} else {
					/**
					 * Widget has been sorted from the same section and container list.
					 * Only one "update" event will be fired.
					 */

					// update global map object - move original widget to new position
					vrc_admin_widgets_map.sections[vrc_admin_widgets_initpos_section]['containers'][vrc_admin_widgets_initpos_container]['widgets'].splice(
						new_widg_index, 
						0, 
						vrc_admin_widgets_map.sections[vrc_admin_widgets_initpos_section]['containers'][vrc_admin_widgets_initpos_container]['widgets'].splice(vrc_admin_widgets_initpos_widget, 1)[0]
					);
				}

				// calculate new instance index for this type of widget
				var widget_type = dropped_widget.attr('data-vrcwidgetid');
				var new_instance_index = jQuery('.vrc-admin-widgets-widget[data-vrcwidgetid="' + widget_type + '"]').index(dropped_widget);
				if (vrc_admin_widgets_initinst_widget >= 0 && new_instance_index >= 0 && vrc_admin_widgets_initinst_widget != new_instance_index) {
					// widget instance index has changed, and since we have multiple instances of this widget, we may need to update its settings
					
					// the widget method to call
					var call_method = 'sortInstance';
					// make a silent call for the widget in case it needs to perform actions when removing an instance
					vrcDoAjax(
						'index.php',
						{
							option: "com_vikrentcar",
							task: "exec_admin_widget",
							widget_id: widget_type,
							widget_index_old: vrc_admin_widgets_initinst_widget,
							widget_index_new: new_instance_index,
							call: call_method,
							tmpl: "component"
						},
						function(response) {
							try {
								var obj_res = JSON.parse(response);
								if (!obj_res.hasOwnProperty(call_method)) {
									console.error('Unexpected JSON response', obj_res);
								}
							} catch(err) {
								console.error('could not parse JSON response', err, response);
							}
						},
						function(error) {
							console.error(error);
						}
					);
				}

				// trigger the save-map event
				document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));

			}
		});
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
				if (vrc_modal_widgets_on === true) {
					hideVrcModalWidgets();
				}
			}
		});

		/**
		 * Dismiss modal window by clicking on an external element.
		 */
		jQuery(document).mouseup(function(e) {
			if (!vrc_modal_widgets_on) {
				return false;
			}
			if (vrc_modal_widgets_on) {
				var vrc_overlay_cont = jQuery(".vrc-modal-overlay-content-dashwidgets");
				if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
					hideVrcModalWidgets();
				}
			}
		});

		/**
		 * Make all current sections and widgets sortable.
		 */
		vrcMakeSectionsSortable();
		vrcMakeWidgetsSortable();

		/**
		 * Add event listener to the save-map event with debounce handler.
		 */
		document.addEventListener('vrc-admin-widgets-savemap', vrcDebounceEvent(vrcHandleMapSaving, 2000));

	});

	/**
	 * Asks for confirmation to restore the default widgets map
	 */
	function vrcWidgetsRestoreMap() {
		if (confirm(Joomla.JText._('VRC_WIDGETS_RESTDEFAULT'))) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adds a new section to the document
	 */
	function vrcWidgetsAddSection() {
		var tot_sections = jQuery('.vrc-admin-widgets-section').length;
		var sect_name_new = prompt(Joomla.JText._('VRC_WIDGETS_ENTERSECTNAME'), Joomla.JText._('VRC_WIDGETS_NEWSECT') + ' #' + (tot_sections + 1));
		if (sect_name_new != null && sect_name_new != '') {
			var html_section_new = '<div class="vrc-admin-widgets-section" id="vrc-admin-widgets-section-' + (tot_sections + 1) + '">';
			html_section_new += '	<div class="vrc-admin-widgets-section-name" data-vrcmanagewidgets="1">';
			html_section_new += '		<span class="vrc-admin-widgets-elem-cmds-drag"><?php VikRentCarIcons::e('ellipsis-v'); ?></span>';
			html_section_new += '		<span class="vrc-admin-widgets-section-name-val">' + sect_name_new + '</span>';
			html_section_new += '		<div class="vrc-admin-widget-elem-cmds vrc-admin-widgets-section-cmds">';
			html_section_new += '			<span class="vrc-admin-widgets-elem-cmds-edit" onclick="vrcWidgetsEditSection(this);"><?php VikRentCarIcons::e('edit'); ?></span>';
			html_section_new += '			<span class="vrc-admin-widgets-elem-cmds-remove" onclick="vrcWidgetsRemoveSection(this);"><?php VikRentCarIcons::e('trash'); ?></span>';
			html_section_new += '		</div>';
			html_section_new += '	</div>';
			html_section_new += '	<div class="vrc-admin-widgets-container vrc-admin-widgets-container-addnew" data-vrcmanagewidgets="1">';
			html_section_new += '		<div class="vrc-admin-widgets-plus-box" onclick="vrcWidgetsAddContainer(this);">';
			html_section_new += '			<span><?php VikRentCarIcons::e('plus-circle'); ?></span>';
			html_section_new += '		</div>';
			html_section_new += '	</div>';
			html_section_new += '</div>';
			jQuery('.vrc-admin-widgets-section-addnew').before(html_section_new);

			// update global map object
			vrc_admin_widgets_map.sections.push({
				name: sect_name_new,
				containers: []
			});

			// trigger the save-map event
			document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));
		}
	}

	/**
	 * Prompts the new section name to update it
	 */
	function vrcWidgetsEditSection(elem) {
		var cur_sect_name_elm = jQuery(elem).closest('.vrc-admin-widgets-section-name').find('.vrc-admin-widgets-section-name-val');
		if (!cur_sect_name_elm || !cur_sect_name_elm.length) {
			console.error('Could not find section to edit');
			return false;
		}
		var cur_sect_name_val = cur_sect_name_elm.text();
		// find current index of selected section in the map
		var cur_sect_index = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(jQuery(elem).closest('.vrc-admin-widgets-section'));
		var sect_name_new = prompt(Joomla.JText._('VRC_WIDGETS_ENTERSECTNAME'), cur_sect_name_val);
		if (sect_name_new != null && sect_name_new != '') {
			cur_sect_name_elm.text(sect_name_new);
			// update global map object
			vrc_admin_widgets_map.sections[cur_sect_index]['name'] = sect_name_new;
			// trigger the save-map event
			document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));
		}
	}

	/**
	 * Asks for confirmation before removing the selected section
	 */
	function vrcWidgetsRemoveSection(elem) {
		var cur_sect_index = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(jQuery(elem).closest('.vrc-admin-widgets-section'));
		if (confirm(Joomla.JText._('VRC_WIDGETS_CONFRMELEM'))) {
			// remove section from document
			jQuery(elem).closest('.vrc-admin-widgets-section').remove();
			// update global map object
			vrc_admin_widgets_map.sections.splice(cur_sect_index, 1);
			// trigger the save-map event
			document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));
		}
	}

	/**
	 * Displays the modal window to create a new widgets container
	 */
	function vrcWidgetsAddContainer(elem) {
		// update last section selected
		vrc_admin_widgets_last_section = jQuery(elem).closest('.vrc-admin-widgets-section');
		if (!vrc_admin_widgets_last_section || !vrc_admin_widgets_last_section.length) {
			// parent section not found
			console.error('parent section not found for adding a container');
			return false;
		}
		// turn update flag off
		jQuery('#vrc-newcontainer-upd').val(0);
		// display new container form
		jQuery('.vrc-modal-widgets-newcontainer').show();
		// set new container form button
		jQuery('#vrc-newcontainer-btn').html('<?php VikRentCarIcons::e('plus-circle'); ?> ' + Joomla.JText._('VRC_WIDGETS_ADDWIDGCONT'));
		// set modal title
		jQuery('#vrc-modal-widgets-title').text(Joomla.JText._('VRC_WIDGETS_ADDWIDGCONT'));
		// display modal
		vrcOpenModalWidgets();
	}

	/**
	 * Adds or updates one widgets container in the document.
	 */
	function vrcWidgetsAddContainerToDoc() {
		var mode = jQuery('#vrc-newcontainer-upd').val() > 0 ? 'update' : 'new';
		if (mode == 'new' && (!vrc_admin_widgets_last_section || !vrc_admin_widgets_last_section.length)) {
			// parent section not found
			console.error('parent section not found for adding a container');
			return false;
		}
		if (mode == 'update' && (!vrc_admin_widgets_last_container || !vrc_admin_widgets_last_container.length)) {
			// current container not found
			console.error('current container not found');
			return false;
		}

		// get new container size and CSS class
		var cont_size = jQuery('#vrc-newcontainer-size').val();
		var cont_css = jQuery('#vrc-newcontainer-size').find('option:selected').attr('data-cssclass');
		var cont_name = jQuery('#vrc-newcontainer-size').find('option:selected').text();
		if (!cont_size || !cont_size.length || !cont_css.length) {
			console.error('new container size missing');
			alert(Joomla.JText._('VRC_WIDGETS_SELCONTSIZE'));
			return false;
		}

		if (mode == 'update') {
			// update container class, size and title
			vrc_admin_widgets_last_container.removeClass().addClass('vrc-admin-widgets-container ' + cont_css).attr('data-vrcwidgetcontsize', cont_size).find('.vrc-admin-widgets-container-name-val').text(cont_name);

			// update global map object
			var cur_cont_index = vrc_admin_widgets_last_container.closest('.vrc-admin-widgets-section').find('.vrc-admin-widgets-container').not('.vrc-admin-widgets-container-addnew').index(vrc_admin_widgets_last_container);
			var cur_sect_index = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(vrc_admin_widgets_last_container.closest('.vrc-admin-widgets-section'));
			vrc_admin_widgets_map.sections[cur_sect_index]['containers'][cur_cont_index]['size'] = cont_size;

			// trigger the save-map event
			document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));
		} else {
			// update containers count for a better styling
			var all_sect_conts = vrc_admin_widgets_last_section.find('.vrc-admin-widgets-container').not('.vrc-admin-widgets-container-addnew');
			var new_sect_conts = all_sect_conts.length + 1;
			all_sect_conts.attr('data-totcontainers', new_sect_conts);

			// build new container
			var html_container_new = '<div class="vrc-admin-widgets-container ' + cont_css + '" data-vrcwidgetcontsize="' + cont_size + '" data-totcontainers="' + new_sect_conts + '">';
			html_container_new += '		<div class="vrc-admin-widgets-container-name" data-vrcmanagewidgets="1">';
			html_container_new += '			<span class="vrc-admin-widgets-container-name-val">' + cont_name + '</span>';
			html_container_new += '			<div class="vrc-admin-widget-elem-cmds vrc-admin-widgets-container-cmds">';
			html_container_new += '				<span class="vrc-admin-widgets-elem-cmds-edit" onclick="vrcWidgetsEditContainer(this);"><?php VikRentCarIcons::e('edit'); ?></span>';
			html_container_new += '				<span class="vrc-admin-widgets-elem-cmds-remove" onclick="vrcWidgetsRemoveContainer(this);"><?php VikRentCarIcons::e('trash'); ?></span>';
			html_container_new += '			</div>';
			html_container_new += '		</div>';
			html_container_new += '		<div class="vrc-admin-widgets-widget vrc-admin-widgets-widget-addnew" data-vrcmanagewidgets="1">';
			html_container_new += '			<div class="vrc-admin-widgets-plus-box" onclick="vrcWidgetsAddWidget(this);">';
			html_container_new += '				<span><?php VikRentCarIcons::e('plus-circle'); ?></span>';
			html_container_new += '			</div>';
			html_container_new += '		</div>';
			html_container_new += '</div>';

			// append new container HTML
			vrc_admin_widgets_last_section.find('.vrc-admin-widgets-container-addnew').before(html_container_new);

			// update global map object
			var cur_sect_index = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(vrc_admin_widgets_last_section);
			vrc_admin_widgets_map.sections[cur_sect_index]['containers'].push({
				size: cont_size,
				widgets: []
			});

			// trigger the save-map event
			document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));
		}

		// close modal window
		hideVrcModalWidgets();

		// unset last section and container
		vrc_admin_widgets_last_section = null;
		vrc_admin_widgets_last_container = null;
	}

	/**
	 * Displays the modal window for editing the selected container
	 */
	function vrcWidgetsEditContainer(elem) {
		// update last container selected
		vrc_admin_widgets_last_container = jQuery(elem).closest('.vrc-admin-widgets-container');
		if (!vrc_admin_widgets_last_container || !vrc_admin_widgets_last_container.length) {
			// parent container not found
			console.error('parent container not found');
			return false;
		}
		// turn update flag on
		jQuery('#vrc-newcontainer-upd').val(1);
		// set current container size
		jQuery('#vrc-newcontainer-size').val(vrc_admin_widgets_last_container.attr('data-vrcwidgetcontsize')).trigger('change');
		// display edit container form
		jQuery('.vrc-modal-widgets-newcontainer').show();
		// set edit container form button
		jQuery('#vrc-newcontainer-btn').html('<?php VikRentCarIcons::e('check'); ?> ' + Joomla.JText._('VRC_WIDGETS_UPDWIDGCONT'));
		// set modal title
		jQuery('#vrc-modal-widgets-title').text(Joomla.JText._('VRC_WIDGETS_EDITWIDGCONT'));
		// display modal
		vrcOpenModalWidgets();
	}

	/**
	 * Asks for confirmation before removing the selected container
	 */
	function vrcWidgetsRemoveContainer(elem) {
		var all_sect_conts = jQuery(elem).closest('.vrc-admin-widgets-section').find('.vrc-admin-widgets-container').not('.vrc-admin-widgets-container-addnew');
		var new_sect_conts = (all_sect_conts.length - 1);
		var cur_cont_index = all_sect_conts.index(jQuery(elem).closest('.vrc-admin-widgets-container'));
		var cur_sect_index = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(jQuery(elem).closest('.vrc-admin-widgets-section'));
		if (confirm(Joomla.JText._('VRC_WIDGETS_CONFRMELEM'))) {
			// update containers count for a better styling
			all_sect_conts.attr('data-totcontainers', new_sect_conts);
			// remove container from document
			jQuery(elem).closest('.vrc-admin-widgets-container').remove();
			// update global map object
			vrc_admin_widgets_map.sections[cur_sect_index]['containers'].splice(cur_cont_index, 1);
			// trigger the save-map event
			document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));
		}
	}

	/**
	 * Displays the modal window to create a new widget
	 */
	function vrcWidgetsAddWidget(elem) {
		// update last container selected
		vrc_admin_widgets_last_container = jQuery(elem).closest('.vrc-admin-widgets-container');
		if (!vrc_admin_widgets_last_container || !vrc_admin_widgets_last_container.length) {
			// parent container not found
			console.error('parent container not found for adding a widget');
			return false;
		}
		// display new widget form
		jQuery('.vrc-modal-widgets-newwidget').show();
		// unset any previously selected widget
		jQuery('#vrc-newwidget-id').val('').trigger('change');
		// set modal title
		jQuery('#vrc-modal-widgets-title').text(Joomla.JText._('VRC_WIDGETS_ADDNEWWIDG'));
		// display modal
		vrcOpenModalWidgets();
	}

	/**
	 * Updates the description in the modal window for the selected widget
	 */
	function vrcWidgetSetNewDescr(widget_id) {
		// always empty the description box
		jQuery('.vrc-newwidget-descr').html('').hide();
		if (!widget_id.length) {
			return;
		}
		// seek for this widget id
		for (var i in vrc_admin_widgets_names) {
			if (!vrc_admin_widgets_names.hasOwnProperty(i)) {
				continue;
			}
			if (vrc_admin_widgets_names[i]['id'] == widget_id) {
				jQuery('.vrc-newwidget-descr').html(vrc_admin_widgets_names[i]['descr']).fadeIn();
				break;
			}
		}
	}

	/**
	 * Adds the new selected widget to the document
	 */
	function vrcWidgetsAddWidgetToDoc() {
		if (!vrc_admin_widgets_last_container || !vrc_admin_widgets_last_container.length) {
			// parent container not found
			console.error('parent container not found for adding a container');
			return false;
		}

		// get new widget id, name and descr
		var widget_id = jQuery('#vrc-newwidget-id').val();
		var widget_descr = jQuery('.vrc-newwidget-descr').html();
		var widget_name = jQuery('#vrc-newwidget-id').find('option:selected').text();
		if (!widget_id || !widget_id.length || !widget_name.length) {
			console.error('new widget id missing');
			return false;
		}

		// build new widget
		var html_widget_new = '<div class="vrc-admin-widgets-widget" data-vrcwidgetid="' + widget_id + '">';
		html_widget_new += '		<div class="vrc-admin-widgets-widget-info" data-vrcmanagewidgets="1">';
		html_widget_new += '			<div class="vrc-admin-widgets-widget-info-inner">';
		html_widget_new += '				<div class="vrc-admin-widgets-widget-details">';
		html_widget_new += '					<span class="vrc-admin-widgets-widget-info-drag"><?php VikRentCarIcons::e('ellipsis-v'); ?></span>';
		html_widget_new += '					<h4 class="vrc-admin-widgets-widget-info-name">';
		html_widget_new += '						<span>' + widget_name + '</span>';
		html_widget_new += '						<span class="vrc-admin-widgets-widget-remove" onclick="vrcWidgetsRemoveWidget(this);"><?php VikRentCarIcons::e('trash'); ?></span>';
		html_widget_new += '					</h4>';
		html_widget_new += '				</div>';
		html_widget_new += '				<div class="vrc-admin-widgets-widget-info-descr">' + widget_descr + '</div>';
		html_widget_new += '			</div>';
		html_widget_new += '		</div>';
		html_widget_new += '		<div class="vrc-admin-widgets-widget-output" style="display: none;"></div>';
		html_widget_new += '</div>';

		// wrap the new HTML into a collection object
		var elem_widget_new = jQuery(html_widget_new);

		// append new widget HTML
		vrc_admin_widgets_last_container.find('.vrc-admin-widgets-widget-addnew').before(elem_widget_new);

		// update global map object
		var cur_sect_index = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(vrc_admin_widgets_last_container.closest('.vrc-admin-widgets-section'));
		var cur_cont_index = vrc_admin_widgets_last_container.closest('.vrc-admin-widgets-section').find('.vrc-admin-widgets-container').not('.vrc-admin-widgets-container-addnew').index(vrc_admin_widgets_last_container);
		vrc_admin_widgets_map.sections[cur_sect_index]['containers'][cur_cont_index]['widgets'].push(widget_id);

		// trigger the save-map event
		document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));

		// close modal window
		hideVrcModalWidgets();

		// unset last container
		vrc_admin_widgets_last_container = null;

		// populate widget output via AJAX
		vrcWidgetsLoadWidgetContent(elem_widget_new, widget_id);
	}

	/**
	 * Asks for confirmation before removing the selected widget
	 */
	function vrcWidgetsRemoveWidget(elem) {
		var vrc_widget_elem = jQuery(elem).closest('.vrc-admin-widgets-widget');
		var cur_widg_id = vrc_widget_elem.attr('data-vrcwidgetid');
		var cur_sect_index = jQuery('.vrc-admin-widgets-section').not('.vrc-admin-widgets-section-addnew').index(vrc_widget_elem.closest('.vrc-admin-widgets-section'));
		var cur_cont_index = vrc_widget_elem.closest('.vrc-admin-widgets-section').find('.vrc-admin-widgets-container').not('.vrc-admin-widgets-container-addnew').index(vrc_widget_elem.closest('.vrc-admin-widgets-container'));
		var cur_widg_index = vrc_widget_elem.closest('.vrc-admin-widgets-container').find('.vrc-admin-widgets-widget').not('.vrc-admin-widgets-widget-addnew').index(vrc_widget_elem.closest('.vrc-admin-widgets-widget'));
		if (confirm(Joomla.JText._('VRC_WIDGETS_CONFRMELEM'))) {
			// calculate widget instance index for its type
			var widget_instance_index = jQuery('.vrc-admin-widgets-widget[data-vrcwidgetid="' + cur_widg_id + '"]').index(vrc_widget_elem);
			
			// the widget method to call
			var call_method = 'removeInstance';
			// make a silent call for the widget in case it needs to perform actions when removing an instance
			vrcDoAjax(
				'index.php',
				{
					option: "com_vikrentcar",
					task: "exec_admin_widget",
					widget_id: cur_widg_id,
					widget_instance: widget_instance_index,
					call: call_method,
					tmpl: "component"
				},
				function(response) {
					try {
						var obj_res = JSON.parse(response);
						if (!obj_res.hasOwnProperty(call_method)) {
							console.error('Unexpected JSON response', obj_res);
						}
					} catch(err) {
						console.error('could not parse JSON response', err, response);
					}
				},
				function(error) {
					console.error(error);
				}
			);

			// remove widget from document
			vrc_widget_elem.remove();
			// update global map object
			vrc_admin_widgets_map.sections[cur_sect_index]['containers'][cur_cont_index]['widgets'].splice(cur_widg_index, 1);
			// trigger the save-map event
			document.dispatchEvent(new Event('vrc-admin-widgets-savemap'));
		}
	}

	/**
	 * Populates the content of the newly added widget.
	 */
	function vrcWidgetsLoadWidgetContent(container, widget_id) {
		if (!container || !container.find('.vrc-admin-widgets-widget-output').length) {
			console.error('Could not find new widget container');
			return false;
		}

		// the widget method to call
		var call_method = 'render';

		vrcDoAjax(
			'index.php',
			{
				option: "com_vikrentcar",
				task: "exec_admin_widget",
				widget_id: widget_id,
				call: call_method,
				tmpl: "component"
			},
			function(response) {
				try {
					var obj_res = JSON.parse(response);
					if (obj_res.hasOwnProperty(call_method)) {
						// populate new widget content
						container.find('.vrc-admin-widgets-widget-output').html(obj_res[call_method]);
					} else {
						console.error('Unexpected JSON response', obj_res);
					}
				} catch(err) {
					console.error('could not parse JSON response', err, response);
				}
			},
			function(error) {
				console.error(error);
				alert(Joomla.JText._('VRC_WIDGETS_ERRDISPWIDG'));
			}
		);
	}
</script>
