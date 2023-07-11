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

/**
 * Class handler for admin widget "sticky notes". This widget has settings.
 * 
 * @since 	1.2.0
 */
class VikRentCarAdminWidgetStickyNotes extends VikRentCarAdminWidget
{
	/**
	 * The instance counter of this widget. Since we do not load individual parameters
	 * for each widget's instance, we use a static counter to determine its settings.
	 *
	 * @var 	int
	 */
	protected static $instance_counter = -1;

	/**
	 * We detect the Operating System through the browser useer agent
	 * to display different information about the shortcuts to use.
	 * 
	 * @var 	string
	 */
	protected $isMac = false;

	/**
	 * Class constructor will define the widget name and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		// declare name, description and identifier
		$this->widgetName = JText::_('VRC_W_STICKYN_TITLE');
		$this->widgetDescr = JText::_('VRC_W_STICKYN_DESCR');
		$this->widgetId = basename(__FILE__);

		// load widget's settings
		$this->widgetSettings = $this->loadSettings();

		// determine if the operating system is MacOS or iOS
		$ua = JFactory::getApplication()->input->server->getString('HTTP_USER_AGENT', '');
		if (stripos($ua, 'windows') === false && (stripos($ua, 'mac') !== false || stripos($ua, 'iphone') !== false || stripos($ua, 'ipad') !== false)) {
			$this->isMac = true;
		}
	}

	/**
	 * Custom method for this widget only to update one sticky note.
	 * The method is called by the admin controller through an AJAX request.
	 * The visibility should be public, it should not exit the process, and
	 * any content sent to output will be returned to the AJAX response.
	 * 
	 * @param 	int 	$force_note_index 	the new index can be forced to insert a new note.
	 * @param 	int 	$force_instance 	the widget's instance to force.
	 * @param 	string 	$force_txt 			the text of the sticky note to force.
	 * 
	 * @return 	void 	it may output a log with the new widget instance assigned.
	 */
	public function updateStickyNote($force_note_index = null, $force_instance = null, $force_txt = null)
	{
		$note_instance = VikRequest::getInt('note_instance', -1, 'request');
		$note_txt = VikRequest::getString('note_txt', '', 'request', VIKREQUEST_ALLOWRAW);
		$note_index = VikRequest::getInt('note_index', 0, 'request');

		if ($force_note_index !== null && $force_note_index >= 0) {
			// use the forced index instead
			$note_index = $force_note_index;
		}

		if ($force_instance !== null && $force_instance >= 0) {
			// use the forced instance instead
			$note_instance = $force_instance;
		}

		if (is_string($force_txt) && strlen($force_txt)) {
			// use the forced text instead
			$note_txt = $force_txt;
		}

		// make sure the settings of the widget are an array
		if (!is_array($this->widgetSettings)) {
			$this->widgetSettings = array();
		}

		// create the new sticky note object
		$username = $this->getLoggedUserName();
		$sticky_note = new stdClass;
		$sticky_note->html = $note_txt;
		$sticky_note->ts = time();
		if (!empty($username)) {
			$sticky_note->user = $username;
		}

		// eventually append debugging result
		$result_log = '';

		// eventually append the new widget instance assigned for newly added widgets via AJAX
		$new_instance_response = '';

		if (!count($this->widgetSettings)) {
			// push the first sticky note as the first instance of this widget
			$this->widgetSettings = array(
				array($sticky_note)
			);
			$result_log = 'pushing the first sticky note as the first instance of the widget';
			$new_instance_response = '[instance=0]';
		} elseif (isset($this->widgetSettings[$note_instance])) {
			// this instance was saved already
			if (isset($this->widgetSettings[$note_instance][$note_index])) {
				// we already have this exact sticky note index, so we replace it
				$this->widgetSettings[$note_instance][$note_index] = $sticky_note;
				$result_log = 'replacing requested note index in existing widget instance';
			} else {
				// we push a new sticky note
				array_push($this->widgetSettings[$note_instance], $sticky_note);
				$result_log = 'adding a new note to the existing widget instance';
			}
		} else {
			// this is a new instance of the widget, which has settings already - push the new instance and the new sticky note
			array_push($this->widgetSettings, array($sticky_note));
			$result_log = 'pushing a new instance and note to the settings';
			$new_instance_response = '[instance=' . (count($this->widgetSettings) - 1) . ']';
		}

		// update widget's settings
		$this->updateSettings(json_encode($this->widgetSettings));

		echo 'e4j.ok' . (!empty($result_log) ? '(' . $result_log . ')' : '') . $new_instance_response;
	}

	/**
	 * Custom method for this widget only to delete one sticky note.
	 * The method is called by the admin controller through an AJAX request.
	 * The visibility should be public, it should not exit the process, and
	 * any content sent to output will be returned to the AJAX response.
	 */
	public function deleteStickyNote()
	{
		$note_instance = VikRequest::getInt('note_instance', -1, 'request');
		$note_index = VikRequest::getInt('note_index', 0, 'request');

		// make sure the settings of the widget are an array
		if (!is_array($this->widgetSettings)) {
			echo 'e4j.error.no settings found';
			return;
		}

		// make sure the instance of the widget exists
		if (!isset($this->widgetSettings[$note_instance]) || !is_array($this->widgetSettings[$note_instance])) {
			echo 'e4j.error.instance not found';
			return;
		}

		// make sure the index of the note exists
		if (!isset($this->widgetSettings[$note_instance][$note_index])) {
			echo 'e4j.error.note index not found, maybe it was never updated before';
			return;
		}

		// splice the array to remove the requested note
		array_splice($this->widgetSettings[$note_instance], $note_index, 1);

		// update widget's settings
		$this->updateSettings(json_encode($this->widgetSettings));

		echo 'e4j.ok';
	}

	/**
	 * Custom method for this widget only to sort one sticky note.
	 * The method is called by the admin controller through an AJAX request.
	 * The visibility should be public, it should not exit the process, and
	 * any content sent to output will be returned to the AJAX response.
	 */
	public function sortStickyNote()
	{
		$note_instance = VikRequest::getInt('note_instance', -1, 'request');
		$note_index_new = VikRequest::getInt('note_index_new', 0, 'request');
		$note_index_old = VikRequest::getInt('note_index_old', 0, 'request');

		if ($note_index_new == $note_index_old) {
			// nothing to do, as notes can be sorted only within the same instance
			echo 'e4j.error.same position given for sticky note';
			return;
		}

		// make sure the settings of the widget are an array
		if (!is_array($this->widgetSettings)) {
			echo 'e4j.error.no settings found';
			return;
		}

		// make sure the instance of the widget exists
		if (!isset($this->widgetSettings[$note_instance]) || !is_array($this->widgetSettings[$note_instance])) {
			echo 'e4j.error.instance not found';
			return;
		}

		// make sure the old index of the note exists
		if (!isset($this->widgetSettings[$note_instance][$note_index_old])) {
			// check if this note was moved before getting saved, so as soon as it was added
			if (count($this->widgetSettings[$note_instance]) == $note_index_old) {
				// append the newly created (empty) note before moving it
				$this->updateStickyNote($note_index_old, $note_instance);
				// reload the widget's settings after they have been updated with the new note
				$this->widgetSettings = $this->loadSettings();
				// proceed below with moving the sticky note
			} else {
				// unable to proceed
				echo 'e4j.error.original note index not found, maybe it was never updated before';
				return;
			}
		}

		// make sure the new index can fit
		if ($note_index_new > (count($this->widgetSettings[$note_instance]) - 1)) {
			echo 'e4j.error.new index exceeds the highest position available';
			return;
		}

		// move the sticky note from the old index to the new index
		$extracted = array_splice($this->widgetSettings[$note_instance], $note_index_old, 1);
		array_splice($this->widgetSettings[$note_instance], $note_index_new, 0, $extracted);

		// update widget's settings
		$this->updateSettings(json_encode($this->widgetSettings));

		echo 'e4j.ok';
	}

	public function render($data = null)
	{
		// increase widget's instance counter
		static::$instance_counter++;

		// check whether the widget is being rendered via AJAX when adding it through the customizer
		$is_ajax = $this->isAjaxRendering();

		// generate a unique ID for the sticky notes wrapper instance
		$wrapper_instance = !$is_ajax ? static::$instance_counter : rand();
		$wrapper_id = 'vrc-widget-sticky-' . $wrapper_instance;

		// load the settings for this specific instance of the widget
		$instance_settings = array();
		if (!$is_ajax && is_array($this->widgetSettings) && isset($this->widgetSettings[static::$instance_counter])) {
			$instance_settings = is_array($this->widgetSettings[static::$instance_counter]) ? $this->widgetSettings[static::$instance_counter] : array();
		}

		?>
		<div class="vrc-admin-widget-wrapper">
			<div class="vrc-admin-widget-head">
				<h4><?php VikRentCarIcons::e('thumbtack'); ?> <?php echo JText::_('VRC_W_STICKYN_TITLE'); ?></h4>
				<div class="btn-toolbar pull-right vrc-btn-toolbar-hastext">
					<span class="vrc-sticky-shortcuts-help"<?php echo count($instance_settings) ? ' style="display: none;"' : ''; ?>>
						<?php
						echo $this->vrc_app->createPopover(array(
							'title' => JText::_('VRC_W_STICKYN_HELP_TITLE'),
							'content' => JText::_(($this->isMac ? 'VRC_W_STICKYN_HELP_DESCR_MAC' : 'VRC_W_STICKYN_HELP_DESCR')),
							'icon_class' => VikRentCarIcons::i('keyboard'),
							'placement' => 'left'
						));
						?>
					</span>
				</div>
			</div>
			<div id="<?php echo $wrapper_id; ?>" class="vrc-admin-widget-sticky-notes-wrap" data-instance="<?php echo !$is_ajax ? static::$instance_counter : '-1'; ?>">
				<ul class="vrc-admin-widget-sticky-notes-list">
				<?php
				foreach ($instance_settings as $k => $sticky_note) {
					?>
					<li class="vrc-sticky-note">
						<div class="vrc-sticky-note-cmds">
							<span class="vrc-sticky-note-cmd-drag"><?php VikRentCarIcons::e('ellipsis-v'); ?></span>
							<span class="vrc-sticky-note-cmd-trash" onclick="vrcWidgetStickyNoteDelete(this);"><?php VikRentCarIcons::e('trash'); ?></span>
						</div>
						<div contenteditable="true" spellcheck="false" class="vrc-widget-sticky-canvas">
							<?php echo $sticky_note->html; ?>
						</div>
						<div class="vrc-sticky-note-sign">
							<span class="vrc-sticky-note-sign-dt"><?php echo date($this->df .' ' . $this->tf, $sticky_note->ts); ?></span>
						<?php
						if (!empty($sticky_note->user)) {
							?>
							<span class="vrc-sticky-note-sign-user"><?php echo $sticky_note->user; ?></span>
							<?php
						}
						?>
						</div>
					</li>
					<?php
				}
				?>
					<li class="vrc-sticky-note-add">
						<div class="vrc-sticky-note-add-inner" onclick="vrcWidgetStickyNoteAdd(this);">
							<span><?php VikRentCarIcons::e('plus-circle'); ?></span>
						</div>
					</li>
				</ul>
			</div>
		</div>

		<script type="text/javascript">
			// declare global variables for notes sorting
			var vrc_stickynote_initial_pos = null;

			jQuery(document).ready(function() {

				// register input event listener for each sticky note
				var stickies<?php echo $wrapper_instance; ?> = document.querySelectorAll('#<?php echo $wrapper_id; ?> .vrc-widget-sticky-canvas');
				for (var i = 0; i < stickies<?php echo $wrapper_instance; ?>.length; i++) {
					stickies<?php echo $wrapper_instance; ?>[i].addEventListener('input', vrcDebounceEvent(vrcWidgetStickyNoteUpdateTxt, 750));
				}

	 			/**
	 			 * Make all sticky notes sortable Do not use .disableSelection() or this
	 			 * will break all [contenteditable] elements and their focus/selection events.
	 			 */
				jQuery('#<?php echo $wrapper_id; ?> .vrc-admin-widget-sticky-notes-list').sortable({
					cursor: 'move',
					handle: '.vrc-sticky-note-cmd-drag',
					items: 'li.vrc-sticky-note',
					revert: false,
					start: function(event, ui) {
						// update sticky note initial position
						vrc_stickynote_initial_pos = jQuery('#<?php echo $wrapper_id; ?>').find('.vrc-sticky-note').index(jQuery(ui.item));
					},
					update: function(event, ui) {
						// get sticky note new position
						var now_note = jQuery(ui.item);
						var new_note_index = jQuery('#<?php echo $wrapper_id; ?>').find('.vrc-sticky-note').index(now_note);
						var widget_instance = now_note.closest('.vrc-admin-widget-sticky-notes-wrap').attr('data-instance');
						
						if (vrc_stickynote_initial_pos === null) {
							return;
						}

						// the widget method to call
						var call_method = 'sortStickyNote';

						// make a silent request to remove the sticky note
						vrcDoAjax(
							'index.php',
							{
								option: "com_vikrentcar",
								task: "exec_admin_widget",
								widget_id: "<?php echo $this->getIdentifier(); ?>",
								call: call_method,
								note_index_old: vrc_stickynote_initial_pos,
								note_index_new: new_note_index,
								note_instance: widget_instance,
								tmpl: "component"
							},
							function(response) {
								// unset global note position var
								vrc_stickynote_initial_pos = null;
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
								// unset global note position var
								vrc_stickynote_initial_pos = null;
								console.error(error);
							}
						);
					}
				});

			});
		</script>

		<?php
		if (static::$instance_counter === 0 || $is_ajax) {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {

				/**
				 * Add event listener to keydown for shortcuts during typing on contenteditable elements.
				 */
				document.onkeydown = function(e) {
					e = e || window.event;
					var active_el = document.activeElement;
					var exec_cmd = null;
					var exec_val = null;
					if ((!e.metaKey && !e.ctrlKey) || !active_el || !active_el.hasAttribute('contenteditable')) {
						return;
					}

					if (e.keyCode == 66) {
						// CMD + B detected
						exec_cmd = 'bold';
					} else if (e.keyCode == 85) {
						// CMD + U detected
						exec_cmd = 'underline';
					} else if (e.keyCode == 73) {
						// CMD + I detected
						exec_cmd = 'italic';
					} else if (e.keyCode == 83) {
						// CMD + S
						exec_cmd = 'strikeThrough';
					} else if (e.keyCode == 72 || e.keyCode == 84) {
						// CMD + H || CMD + T detected
						exec_cmd = 'formatBlock';
						exec_val = 'h2';
					} else if (e.keyCode == 80) {
						// CMD + P
						exec_cmd = 'formatBlock';
						exec_val = 'p';
					} else if (e.keyCode == 79 || e.keyCode == 78) {
						// CMD + O || CMD + N detected
						exec_cmd = 'insertOrderedList';
					} else if (e.keyCode == 76) {
						// CMD + L
						if (window.getSelection()) {
							var range = window.getSelection().getRangeAt(0);
							exec_val = range.toString();
							if (exec_val && exec_val.length) {
								// some text is selected
								if (exec_val.match(/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i)) {
									// URI selected, set command to create a link
									exec_cmd = 'createLink';
								} else {
									// just some plain text was selected, ask for a custom URI
									e.preventDefault();
									var link_uri = prompt(Joomla.JText._('VRC_STICKYN_CUSTOMURI'), '<?php echo JUri::root(); ?>');
									if (link_uri != null && link_uri != '' && link_uri.indexOf('http') >= 0) {
										exec_val = '<a href="' + link_uri + '">' + exec_val + '</a>';
										exec_cmd = 'insertHTML';
									}
								}
							} else {
								// no text selected, create an unordered list
								exec_val = null;
								exec_cmd = 'insertUnorderedList';
							}
						} else {
							// cannot access text selection, create an unordered list also in this case
							exec_val = null;
							exec_cmd = 'insertUnorderedList';
						}
					} else if (e.keyCode == 77 && window.getSelection()) {
						// CMD + M
						var txtsel = window.getSelection().getRangeAt(0).toString();
						if (txtsel && txtsel.length && txtsel.indexOf('<') === 0 && txtsel.substr(-1, 1) == '>') {
							// HTML tag selected, convert it from plain text to HTML code
							if (txtsel.indexOf('><') >= 0) {
								// hack for converting icons, where empty tags are not parsed correctly
								txtsel = txtsel.replace('><', '> <');
							}
							exec_val = txtsel + ' ';
							exec_cmd = 'insertHTML';
						}
					}

					if (exec_cmd !== null) {
						e.preventDefault();
						document.execCommand(exec_cmd, false, exec_val);
						active_el.focus();

						return false;
					}
				}

				/**
				 * Listen to mousedown event for clicks on links inside the elements
				 * with contenteditable, otherwise links in sticky notes won't open.
				 */
				jQuery(document.body).on('mousedown', '.vrc-widget-sticky-canvas[contenteditable]', function(e) {
					var elem = jQuery(e.target);
					if (elem.is('a')) {
						var goto = elem.attr('href');
						if (goto && goto.length && goto.indexOf('http') >= 0) {
							e.preventDefault();
							window.open(goto, '_blank');
							return false;
						}
					}
				});
			});
		</script>

		<script type="text/javascript">
			function vrcWidgetStickyNoteAdd(elem) {
				// display the help for the shortcuts instructions
				jQuery(elem).closest('.vrc-admin-widget-wrapper').find('.vrc-sticky-shortcuts-help').fadeIn();
				// sticky note default HTML placeholder
				var sticky_placeholder = '<h2>' + Joomla.JText._('VRC_STICKYN_TITLE') + '</h2>' + "\n";
				sticky_placeholder += '<p>' + Joomla.JText._('VRC_STICKYN_TEXT') + '</p>' + "\n";
				sticky_placeholder += '<p>' + Joomla.JText._('VRC_STICKYN_TEXT2') + '</p>' + "\n";
				// build new sticky note HTML
				var html_sticky_new = '<div class="vrc-sticky-note-cmds">';
				html_sticky_new += '	<span class="vrc-sticky-note-cmd-drag"><?php VikRentCarIcons::e('ellipsis-v'); ?></span>';
				html_sticky_new += '	<span class="vrc-sticky-note-cmd-trash" onclick="vrcWidgetStickyNoteDelete(this);"><?php VikRentCarIcons::e('trash'); ?></span>';
				html_sticky_new += '</div>';
				html_sticky_new += '<div contenteditable="true" spellcheck="false" class="vrc-widget-sticky-canvas">';
				html_sticky_new += sticky_placeholder;
				html_sticky_new += '</div>';
				
				// build new element and add HTML to it
				var sticky_new = document.createElement('li');
				sticky_new.setAttribute('class', 'vrc-sticky-note');
				sticky_new.innerHTML = html_sticky_new;

				// attach listener for input event
				sticky_new.addEventListener('input', vrcDebounceEvent(vrcWidgetStickyNoteUpdateTxt, 750));
				
				// add new element to the document, before the button to add new sticky notes
				var append_to = jQuery(elem).closest('.vrc-admin-widget-sticky-notes-list').find('.vrc-sticky-note-add');
				append_to.before(jQuery(sticky_new));

				/**
				 * The input event is immediately triggered so that the newly added note will be saved to avoid
				 * problems when like adding two notes before even typing some text, and then moving/removing them.
				 */
				sticky_new.dispatchEvent(new Event('input'));
			}

			function vrcWidgetStickyNoteDelete(elem) {
				var note_elem = jQuery(elem);
				var note_index = note_elem.closest('.vrc-admin-widget-sticky-notes-list').find('.vrc-sticky-note').index(note_elem.closest('.vrc-sticky-note'));
				var widget_instance = note_elem.closest('.vrc-admin-widget-sticky-notes-wrap').attr('data-instance');
				
				var confirm_lbl = Joomla.JText._('VRC_WIDGETS_CONFRMELEM');
				confirm_lbl = confirm_lbl.length ? confirm_lbl : 'Continue?';
				if (confirm(confirm_lbl)) {
					// the widget method to call
					var call_method = 'deleteStickyNote';

					// make a silent request to remove the sticky note
					vrcDoAjax(
						'index.php',
						{
							option: "com_vikrentcar",
							task: "exec_admin_widget",
							widget_id: "<?php echo $this->getIdentifier(); ?>",
							call: call_method,
							note_index: note_index,
							note_instance: widget_instance,
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

					// remove the sticky note from the document
					note_elem.closest('li.vrc-sticky-note').remove();
				}
			}

			function vrcWidgetStickyNoteUpdateTxt(event) {
				var note_elem = jQuery(this);
				// display the help for the shortcuts instructions
				note_elem.closest('.vrc-admin-widget-wrapper').find('.vrc-sticky-shortcuts-help').fadeIn();
				// element "this" may be different depending on how the event was triggered
				if (note_elem.find('.vrc-widget-sticky-canvas').length) {
					var note_txt = note_elem.find('.vrc-widget-sticky-canvas').html();
				} else {
					var note_txt = note_elem.html();
				}
				var note_index = note_elem.closest('.vrc-admin-widget-sticky-notes-list').find('.vrc-sticky-note').index(note_elem.closest('.vrc-sticky-note'));
				var widget_instance = note_elem.closest('.vrc-admin-widget-sticky-notes-wrap').attr('data-instance');

				// the widget method to call
				var call_method = 'updateStickyNote';

				// make a silent request to update the sticky note details
				vrcDoAjax(
					'index.php',
					{
						option: "com_vikrentcar",
						task: "exec_admin_widget",
						widget_id: "<?php echo $this->getIdentifier(); ?>",
						call: call_method,
						note_txt: note_txt,
						note_index: note_index,
						note_instance: widget_instance,
						tmpl: "component"
					},
					function(response) {
						try {
							var obj_res = JSON.parse(response);
							if (!obj_res.hasOwnProperty(call_method)) {
								console.error('Unexpected JSON response', obj_res);
							} else {
								// response for the method updateStickyNote() may contain the new instance given to the widget
								if (widget_instance < 0 && obj_res[call_method].indexOf('[instance=') >= 0) {
									// extract the new instance assigned
									var resp_left = obj_res[call_method].split('[instance=');
									var widget_instance_new = resp_left[1].split(']')[0];
									if (widget_instance_new && widget_instance_new.length) {
										// update widget's instance
										note_elem.closest('.vrc-admin-widget-sticky-notes-wrap').attr('data-instance', widget_instance_new);
									}
								}
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
		</script>
		<?php
		}
	}
}
