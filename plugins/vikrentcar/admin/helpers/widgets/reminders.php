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
 * Class handler for admin widget "reminders".
 * 
 * @since 	1.2.0
 */
class VikRentCarAdminWidgetReminders extends VikRentCarAdminWidget
{
	/**
	 * The instance counter of this widget.
	 *
	 * @var 	int
	 */
	protected static $instance_counter = -1;

	/**
	 * Number of reminders per page.
	 * 
	 * @var 	int
	 */
	protected $reminders_per_page = 5;

	/**
	 * Today Y-m-d string
	 * 
	 * @var 	string
	 */
	protected $today_ymd = null;

	/**
	 * Class constructor will define the widget name and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->widgetName = JText::_('VRC_W_REMINDERS_TITLE');
		$this->widgetDescr = JText::_('VRC_W_REMINDERS_DESCR');
		$this->widgetId = basename(__FILE__);

		// today Y-m-d date
		$this->today_ymd = date('Y-m-d');
	}

	/**
	 * Custom method for this widget only to load the latest car reminders.
	 * The method is called by the admin controller through an AJAX request.
	 * The visibility should be public, it should not exit the process, and
	 * any content sent to output will be returned to the AJAX response.
	 * 
	 * It's the actual rendering of the widget which also allows navigation.
	 */
	public function loadReminders()
	{
		$offset = VikRequest::getInt('offset', 0, 'request');
		$length = VikRequest::getInt('length', $this->reminders_per_page, 'request');
		$wrapper = VikRequest::getString('wrapper', '', 'request');
		$hashtag = VikRequest::getString('hashtag', '', 'request');

		$dbo = JFactory::getDbo();

		// load latest reminders
		$latest_reminders = array();

		// build clauses
		$clauses = array("`dt`>=" . $dbo->quote($this->today_ymd));
		if (!empty($hashtag)) {
			$hypotethical_dt = strtotime($hashtag);
			if ($hypotethical_dt) {
				// a date has been passed as a filter, so we don't want only future dates
				$clauses = array("`dt`=" . $dbo->quote(date('Y-m-d', $hypotethical_dt)) . " OR `info` LIKE " . $dbo->quote('%' . $hashtag . '%'));
			} else {
				// search by "hashtag" or any wording
				$clauses[] = "`info` LIKE " . $dbo->quote('%' . $hashtag . '%');
			}
		}

		$q = "SELECT * FROM `#__vikrentcar_critical_dates` WHERE " . implode(' AND ', $clauses) . " ORDER BY `dt` ASC, `id` DESC";
		$dbo->setQuery($q, $offset, $length);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$latest_reminders = $dbo->loadAssocList();
			// make sure to decode all notes infos
			foreach ($latest_reminders as $k => $v) {
				$notes_info = json_decode($v['info']);
				$notes_info = !is_array($notes_info) ? array() : $notes_info;
				$latest_reminders[$k]['info'] = $notes_info;
			}
		}

		// load the information about each car (only if some reminders are available)
		$cars_info = array();
		if (count($latest_reminders)) {
			$cars_info = $this->loadCarsInformation();
		}

		foreach ($latest_reminders as $day_reminders) {
			// loop through all reminders for this car-day
			foreach ($day_reminders['info'] as $rem_index => $reminder) {
				// build content
				$needs_full_content = false;
				$reminder_content = isset($reminder->descr) ? $reminder->descr : '';
				if (empty($reminder_content)) {
					$reminder_content = '.....';
				} elseif (strlen($reminder_content) > 200) {
					if (function_exists('mb_substr')) {
						$reminder_content = mb_substr($reminder_content, 0, 200);
					} else {
						$reminder_content = substr($reminder_content, 0, 200);
					}
					$reminder_content .= '...';
					$needs_full_content = true;
				} else {
					$reminder_content = nl2br($reminder_content);
				}
				// convert hashtags to span tags
				$reminder_content = preg_replace_callback("/(#\w+)/u", function($match) {
					return '<span class="vrc-reminder-hashtag">' . end($match) . '</span>';
				}, $reminder_content);
				// reminder due date timestamp
				$reminder_ts = strtotime($day_reminders['dt']);
				// build reminder identifier values
				$reminder_data = array(
					$day_reminders['dt'],
					(date('Y-m-d', $reminder_ts) != $this->today_ymd ? date($this->df, $reminder_ts) : JText::_('VRCJQCALTODAY')),
					$day_reminders['idcar'],
					$day_reminders['subunit'],
					$rem_index,
				);
				?>
			<div class="vrc-dashboard-reminder vrc-dashboard-reminder-note" data-reminder="<?php echo implode('|', $reminder_data); ?>" onclick="vrcWidgetRemindersOpenNote(this);">
				<div class="vrc-dashboard-reminder-avatar">
				<?php
				if (!empty($day_reminders['idcar']) && isset($cars_info[$day_reminders['idcar']]) && !empty($cars_info[$day_reminders['idcar']]['img'])) {
					// highest priority goes to the vehicle's photo
					?>
					<img class="vrc-dashboard-reminder-avatar-profile" src="<?php echo $cars_info[$day_reminders['idcar']]['img']; ?>" />
					<?php
				} else {
					// we use an icon as fallback
					VikRentCarIcons::e('user', 'vrc-dashboard-reminder-avatar-icon');
				}
				?>
				</div>
				<div class="vrc-dashboard-reminder-content">
					<div class="vrc-dashboard-reminder-content-head">
						<div class="vrc-dashboard-reminder-content-info-details">
						<?php
						if (!empty($day_reminders['idcar']) && isset($cars_info[$day_reminders['idcar']])) {
							// display the car name
							$car_name = $cars_info[$day_reminders['idcar']]['name'];
							if (!empty($day_reminders['subunit']) && isset($cars_info[$day_reminders['idcar']]['features'][$day_reminders['subunit']])) {
								// concatenate sub-unit info
								$car_name .= ' ' . $cars_info[$day_reminders['idcar']]['features'][$day_reminders['subunit']];
							}
							?>
							<h4 class="vrc-widget-reminder-title"><?php echo $car_name; ?></h4>
							<?php
						}
						if (!empty($reminder->name)) {
							// this is the tile of the reminder
							?>
							<h5 class="vrc-widget-reminder-subtitle"><?php echo $reminder->name; ?></h5>
							<?php
						}
						?>
							<div class="vrc-dashboard-reminder-content-info-icon">
							<?php
							$author_date = date($this->df . ' ' . $this->tf, $reminder->ts);
							$author_name = '';
							if (!empty($reminder->wuid)) {
								$user = JFactory::getUser();
								$author_name = $user->name . ' - ';
							}
							?>
								<span class="vrc-dashboard-reminder-content-info-author"><?php echo $author_name . $author_date; ?></span>
							</div>
						</div>
						<div class="vrc-dashboard-reminder-content-info-date">
						<?php
						if (date('Y-m-d', $reminder_ts) != $this->today_ymd) {
							// format and print the date
							?>
							<span><?php echo date($this->df, $reminder_ts); ?></span>
							<?php
						} else {
							// print "today"
							?>
							<span><?php echo JText::_('VRCJQCALTODAY'); ?></span>
							<?php
						}
						?>
						</div>
					</div>
					<div class="vrc-dashboard-reminder-content-info-msg">
						<p><?php echo $reminder_content; ?></p>
					<?php
					if ($needs_full_content) {
						// in case the full message is cut for its length, we display it in full here
						// convert hashtags to span tags
						$reminder->descr = preg_replace_callback("/(#\w+)/u", function($match) {
							return '<span class="vrc-reminder-hashtag">' . end($match) . '</span>';
						}, $reminder->descr);
						?>
						<div class="vrc-widget-reminder-full-content" style="display: none;"><?php echo $reminder->descr; ?></div>
						<?php
					}
					?>
					</div>
				</div>
			</div>
			<?php
			}
		}

		// append navigation
		?>
		<div class="vrc-reminderswidget-commands">
			<div class="vrc-reminderswidget-commands-left">
				<button type="button" class="btn vrc-config-btn" onclick="showVrcDialogCdaynotes();"><?php VikRentCarIcons::e('plus-circle'); ?> <?php echo JText::_('VRC_ADD_NEW'); ?></button>
			</div>
			<div class="vrc-reminderswidget-commands-main">
			<?php
			if ($offset > 0) {
				// show backward navigation button
				?>
				<div class="vrc-reminderswidget-command-chevron vrc-reminderswidget-command-prev">
					<span class="vrc-reminderswidget-prev" onclick="vrcWidgetRemindersNavigate('<?php echo $wrapper; ?>', -1);"><?php VikRentCarIcons::e('chevron-left'); ?></span>
				</div>
				<?php
			}
			?>
				<div class="vrc-reminderswidget-command-chevron vrc-reminderswidget-command-next">
					<span class="vrc-reminderswidget-next" onclick="vrcWidgetRemindersNavigate('<?php echo $wrapper; ?>', 1);"><?php VikRentCarIcons::e('chevron-right'); ?></span>
				</div>
			</div>
		</div>
		<?php

		// append the total number of reminders displayed, the current offset and the latest reminder id
		$tot_reminders  = count($latest_reminders);
		$latest_id = $tot_reminders > 0 && $offset === 0 ? $latest_reminders[0]['id'] : null;

		echo ';' . __FUNCTION__ . ';' . $tot_reminders . ';' . __FUNCTION__ . ';' . $offset . ';' . __FUNCTION__ . ';' . $latest_id;
	}

	/**
	 * Custom method for this widget only to watch the latest car reminders.
	 * The method is called by the admin controller through an AJAX request.
	 * The visibility should be public, it should not exit the process, and
	 * any content sent to output will be returned to the AJAX response.
	 * 
	 * Outputs the new number of reminders found from the latest datetime.
	 */
	public function watchReminders() {
		$latest_id = VikRequest::getInt('latest_id', 0, 'request');
		if (empty($latest_id)) {
			echo '0';
			return;
		}

		$dbo = JFactory::getDbo();

		// load the latest reminder (one is sufficient)
		$latest_reminders = array();
		$q = "SELECT * FROM `#__vikrentcar_critical_dates` WHERE `dt`>=" . $dbo->quote($this->today_ymd) . " ORDER BY `dt` ASC, `id` DESC";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$latest_reminders = $dbo->loadAssocList();
		}

		if (!count($latest_reminders) || $latest_reminders[0]['id'] == $latest_id) {
			// no newest reminders found
			echo '0';
			return;
		}

		// print 1 to indicate that new reminders should be reloaded
		echo '1';
	}

	public function render($data = null)
	{
		// increase widget's instance counter
		static::$instance_counter++;

		// check whether the widget is being rendered via AJAX when adding it through the customizer
		$is_ajax = $this->isAjaxRendering();

		// generate a unique ID for the reminders wrapper instance
		$wrapper_instance = !$is_ajax ? static::$instance_counter : rand();
		$wrapper_id = 'vrc-widget-reminders-' . $wrapper_instance;

		// load the information for each vehicle
		$cars_info = $this->loadCarsInformation();

		?>
		<div class="vrc-admin-widget-wrapper">
			<div class="vrc-admin-widget-head">
				<h4><?php VikRentCarIcons::e('business-time'); ?> <?php echo JText::_('VRC_W_REMINDERS_TITLE'); ?></h4>
				<div class="btn-toolbar pull-right vrc-dashboard-search-reminders">
					<div class="btn-wrapper input-append pull-right">
						<input type="text" class="reminders-search form-control" placeholder="#hashtag" data-winstance="<?php echo $wrapper_id; ?>" />
						<button type="button" class="btn" onclick="vrcEmptySearchFilter('<?php echo $wrapper_id; ?>');"><i class="icon-remove"></i></button>
					</div>
				</div>
			</div>
			<div id="<?php echo $wrapper_id; ?>" class="vrc-dashboard-reminders-latest" data-offset="0" data-length="<?php echo $this->reminders_per_page; ?>" data-latestid="">
				<div class="vrc-dashboard-cars-reminders-inner">
					<div class="vrc-dashboard-cars-reminders-list">
					<?php
					for ($i = 0; $i < $this->reminders_per_page; $i++) { 
						?>
						<div class="vrc-dashboard-reminder vrc-dashboard-reminder-skeleton">
							<div class="vrc-dashboard-reminder-avatar">
								<div class="vrc-skeleton-loading vrc-skeleton-loading-avatar"></div>
							</div>
							<div class="vrc-dashboard-reminder-content">
								<div class="vrc-dashboard-reminder-content-head">
									<div class="vrc-skeleton-loading vrc-skeleton-loading-title"></div>
								</div>
								<div class="vrc-dashboard-reminder-content-subhead">
									<div class="vrc-skeleton-loading vrc-skeleton-loading-subtitle"></div>
								</div>
								<div class="vrc-dashboard-reminder-content-info-msg">
									<div class="vrc-skeleton-loading vrc-skeleton-loading-content"></div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
			</div>
		</div>

		<?php
		if (static::$instance_counter === 0 || $is_ajax) {
			// HTML helpers (modal) and some JS functions should be loaded once per widget instance
			if (static::$instance_counter === 0) {
				$this->vrc_app->loadSelect2();
			}
		?>
		<div class="vrc-modal-overlay-block vrc-modal-overlay-block-cardaynotes vrc-modal-overlay-block-cardaynotes-new">
			<a class="vrc-modal-overlay-close" href="javascript: void(0);"></a>
			<div class="vrc-modal-overlay-content vrc-modal-overlay-content-cardaynotes">
				<div class="vrc-modal-overlay-content-head vrc-modal-overlay-content-head-cardaynotes">
					<h3>
						<?php VikRentCarIcons::e('business-time'); ?> 
						<span class="vrc-modal-cardaynotes-dt"><?php echo JText::_('VRC_NEW_CDAY_NOTE'); ?></span>
						<span class="vrc-modal-overlay-close-times" onclick="hideVrcDialogCdaynotes();">&times;</span>
					</h3>
				</div>
				<div class="vrc-modal-overlay-content-body">
					<div class="vrc-modal-cardaynotes-addnew" data-instance="<?php echo $wrapper_instance; ?>">
						<div class="vrc-modal-cardaynotes-addnew-elem-multi">
							<div class="vrc-modal-cardaynotes-addnew-elem">
								<label for="vrc-newcdnote-idcar<?php echo $wrapper_instance; ?>"><?php echo JText::_('VRPVIEWORDERSTHREE'); ?></label>
								<select id="vrc-newcdnote-idcar<?php echo $wrapper_instance; ?>" class="vrc-newcdnote-idcar">
									<option></option>
								<?php
								foreach ($cars_info as $cinfo) {
									?>
									<option value="<?php echo $cinfo['id']; ?>"><?php echo $cinfo['name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
							<div class="vrc-modal-cardaynotes-addnew-elem">
								<label for="new-cday-note-day<?php echo $wrapper_instance; ?>"><?php echo JText::_('VRPVIEWORDERSONE'); ?></label>
								<?php echo $this->vrc_app->getCalendar('', 'new-cday-note-day', 'new-cday-note-day' . $wrapper_instance, '%Y-%m-%d', array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
							</div>
						</div>
						<div class="vrc-modal-cardaynotes-addnew-elem">
							<label for="vrc-newcdnote-name<?php echo $wrapper_instance; ?>"><?php echo JText::_('VRPVIEWPLACESONE'); ?></label>
							<input type="text" id="vrc-newcdnote-name<?php echo $wrapper_instance; ?>" class="vrc-newcdnote-name" value="" />
						</div>
						<div class="vrc-modal-cardaynotes-addnew-elem">
							<label for="vrc-newcdnote-descr<?php echo $wrapper_instance; ?>"><?php echo JText::_('VRCPLACEDESCR'); ?></label>
							<textarea id="vrc-newcdnote-descr<?php echo $wrapper_instance; ?>" class="vrc-newcdnote-descr"></textarea>
							<span class="vrc-param-setting-comment vrc-suggestion-hashtags"><?php echo JText::_('VRC_CDAYNOTES_HASHTAGS_HELP'); ?></span>
						</div>
						<div class="vrc-modal-cardaynotes-addnew-elem">
							<label for="vrc-newcdnote-cdays<?php echo $wrapper_instance; ?>"><?php echo JText::_('VRCCONSECUTIVEDAYS'); ?></label>
							<input type="number" id="vrc-newcdnote-cdays<?php echo $wrapper_instance; ?>" class="vrc-newcdnote-cdays" min="0" max="365" value="0" onchange="vrcCdayNoteCdaysCount('<?php echo $wrapper_instance; ?>');" onkeyup="vrcCdayNoteCdaysCount('<?php echo $wrapper_instance; ?>');" />
							<span class="vrc-newcdnote-dayto">
								<span class="vrc-newcdnote-dayto-lbl"><?php echo JText::_('VRCUNTIL'); ?></span>
								<span class="vrc-newcdnote-dayto-val"></span>
							</span>
						</div>
						<div class="vrc-modal-cardaynotes-addnew-save">
							<button type="button" class="btn btn-success" onclick="vrcAddCarDayNote(this);"><?php echo JText::_('VRSAVE'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="vrc-modal-overlay-block vrc-modal-overlay-block-cardaynotes vrc-modal-overlay-block-cardaynotes-details">
			<a class="vrc-modal-overlay-close" href="javascript: void(0);"></a>
			<div class="vrc-modal-overlay-content vrc-modal-overlay-content-cardaynotes">
				<div class="vrc-modal-overlay-content-head vrc-modal-overlay-content-head-cardaynotes">
					<h3>
						<?php VikRentCarIcons::e('business-time'); ?> 
						<span class="vrc-modal-cardaynotes-det-car"></span>
						<span class="vrc-modal-overlay-close-times" onclick="hideVrcDialogCdaynotes();">&times;</span>
					</h3>
				</div>
				<div class="vrc-modal-overlay-content-body">
					<div class="vrc-modal-cardaynotes-list"></div>
				</div>
			</div>
		</div>

		<script type="text/javascript">

			/**
			 * Global vars
			 */
			var vrcdialogcdaynotes_on = false;

			/**
			 * Open the reminder/note details
			 */
			function vrcWidgetRemindersOpenNote(that) {
				var wrapper = jQuery(that);
				if (!wrapper || !wrapper.length) {
					return false;
				}
				var reminder_data = wrapper.attr('data-reminder');
				if (!reminder_data || !reminder_data.length) {
					return false;
				}
				var reminder_vals = reminder_data.split('|');
				var car_name = '';
				if (wrapper.find('.vrc-widget-reminder-title').length) {
					car_name = wrapper.find('.vrc-widget-reminder-title').text();
				}
				var note_title = '';
				if (wrapper.find('.vrc-widget-reminder-subtitle').length) {
					note_title = wrapper.find('.vrc-widget-reminder-subtitle').text();
				}
				var author_info = '';
				if (wrapper.find('.vrc-dashboard-reminder-content-info-author').length) {
					author_info = wrapper.find('.vrc-dashboard-reminder-content-info-author').text();
				}
				var note_content = '';
				if (wrapper.find('.vrc-widget-reminder-full-content').length) {
					// grab the cut long content
					note_content = wrapper.find('.vrc-widget-reminder-full-content').html();
				} else {
					// use the already visible content
					note_content = wrapper.find('.vrc-dashboard-reminder-content-info-msg').html();
				}
				// convert new lines into br tags
				note_content = note_content.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br />' + '$2');

				// set title
				jQuery('.vrc-modal-cardaynotes-det-car').text(car_name);

				// build html details
				var note_details = '';
				note_details += '<div class="vrc-modal-cardaynote-details-wrap" data-reminder="' + reminder_data + '">';
				note_details += '	<div class="vrc-modal-cardaynote-details-dates">';
				note_details += '		<div class="vrc-modal-cardaynote-details-author"><i class="<?php echo VikRentCarIcons::i('user'); ?>"></i> ' + author_info + '</div>';
				note_details += '		<div class="vrc-modal-cardaynote-details-due"><i class="<?php echo VikRentCarIcons::i('clock'); ?>"></i> ' + reminder_vals[1] + '</div>';
				note_details += '	</div>';
				note_details += '	<div class="vrc-modal-cardaynote-details-main">';
				note_details += '		<div class="vrc-modal-cardaynote-details-title"><h4>' + note_title + '</h4></div>';
				note_details += '		<div class="vrc-modal-cardaynote-details-cont">' + note_content + '</div>';
				note_details += '	</div>';
				note_details += '	<div class="vrc-modal-cardaynote-details-rm">';
				note_details += '		<button type="button" class="btn btn-danger" onclick="vrcRemoveCdayNote(\'' + reminder_vals[4] + '\', \'' + reminder_vals[0] + '\', \'' + reminder_vals[2] + '\', \'' + reminder_vals[3] + '\', this);"><?php VikRentCarIcons::e('trash-alt'); ?> ' + Joomla.JText._('VRELIMINA') + '</button>';
				note_details += '	</div>';
				note_details += '</div>';

				// append HTML
				jQuery('.vrc-modal-cardaynotes-list').html(note_details);

				// open modal
				showVrcDialogCdaynoteDetails();
			}

			/**
			 * Open car-day-notes dialog
			 */
			function showVrcDialogCdaynotes() {
				jQuery('.vrc-modal-overlay-block-cardaynotes-new').fadeIn();
				vrcdialogcdaynotes_on = true;
			}

			/**
			 * Open car-day-note details dialog
			 */
			function showVrcDialogCdaynoteDetails() {
				jQuery('.vrc-modal-overlay-block-cardaynotes-details').fadeIn();
				vrcdialogcdaynotes_on = true;
			}

			/**
			 * Close car-day-notes dialog
			 */
			function hideVrcDialogCdaynotes(type) {
				type = !type ? 'new' : 'details';
				if (vrcdialogcdaynotes_on === true) {
					// turn flag off
					vrcdialogcdaynotes_on = false;
					// hide dialog
					jQuery(".vrc-modal-overlay-block-cardaynotes").fadeOut(400, function () {
						jQuery(".vrc-modal-overlay-content-cardaynotes").show();
					});
					
					if (type == 'new') {
						// reset values
						jQuery('.vrc-newcdnote-name').val('');
						jQuery('.vrc-newcdnote-descr').val('');
						jQuery('.vrc-newcdnote-cdays').val('0').trigger('change');
					} else {
						// empty note details
						jQuery('.vrc-modal-cardaynotes-det-car').text('');
						jQuery('.vrc-modal-cardaynotes-list').html('');
					}
				}
			}

			/**
			 * Display the loading skeletons.
			 */
			function vrcWidgetRemindersSkeletons(wrapper) {
				var widget_instance = jQuery('#' + wrapper);
				if (!widget_instance.length) {
					return false;
				}
				widget_instance.find('.vrc-dashboard-cars-reminders-list').html('');
				for (var i = 0; i < <?php echo $this->reminders_per_page; ?>; i++) {
					var skeleton = '';
					skeleton += '<div class="vrc-dashboard-reminder vrc-dashboard-reminder-skeleton">';
					skeleton += '	<div class="vrc-dashboard-reminder-avatar">';
					skeleton += '		<div class="vrc-skeleton-loading vrc-skeleton-loading-avatar"></div>';
					skeleton += '	</div>';
					skeleton += '	<div class="vrc-dashboard-reminder-content">';
					skeleton += '		<div class="vrc-dashboard-reminder-content-head">';
					skeleton += '			<div class="vrc-skeleton-loading vrc-skeleton-loading-title"></div>';
					skeleton += '		</div>';
					skeleton += '		<div class="vrc-dashboard-reminder-content-subhead">';
					skeleton += '			<div class="vrc-skeleton-loading vrc-skeleton-loading-subtitle"></div>';
					skeleton += '		</div>';
					skeleton += '		<div class="vrc-dashboard-reminder-content-info-msg">';
					skeleton += '			<div class="vrc-skeleton-loading vrc-skeleton-loading-content"></div>';
					skeleton += '		</div>';
					skeleton += '	</div>';
					skeleton += '</div>';
					// append skeleton
					jQuery(skeleton).appendTo(widget_instance.find('.vrc-dashboard-cars-reminders-list'));
				}
			}

			/**
			 * Perform the request to load the latest reminders.
			 */
			function vrcWidgetRemindersLoad(wrapper) {
				var widget_instance = jQuery('#' + wrapper);
				if (!widget_instance.length) {
					return false;
				}
				var current_offset  = parseInt(widget_instance.attr('data-offset'));
				var length_per_page = parseInt(widget_instance.attr('data-length'));

				// look for any possible typed search values
				var hashtag = '';
				var widget_search = jQuery('.reminders-search[data-winstance="' + wrapper + '"]');
				if (widget_search.length) {
					hashtag = widget_search.val();
				}

				// the widget method to call
				var call_method = 'loadReminders';

				// make a request to load the reminders
				vrcDoAjax(
					'index.php',
					{
						option: "com_vikrentcar",
						task: "exec_admin_widget",
						widget_id: "<?php echo $this->getIdentifier(); ?>",
						call: call_method,
						offset: current_offset,
						length: length_per_page,
						wrapper: wrapper,
						hashtag: hashtag,
						tmpl: "component"
					},
					function(response) {
						try {
							var obj_res = JSON.parse(response);
							if (!obj_res.hasOwnProperty(call_method)) {
								console.error('Unexpected JSON response', obj_res);
								return false;
							}
							// response must contain 4 values separated by ";call_method;"
							var reminders_data = obj_res[call_method].split(';' + call_method + ';');
							if (reminders_data.length != 4) {
								return;
							}
							// replace HTML with new reminders
							widget_instance.find('.vrc-dashboard-cars-reminders-list').html(reminders_data[0]);
							// check if latest datetime is set
							if (reminders_data[3].length) {
								widget_instance.attr('data-latestid', reminders_data[3]);
							}
							// check results
							if (!isNaN(reminders_data[1]) && parseInt(reminders_data[1]) < 1) {
								// no results can indicate the offset is invalid or too high
								if (!isNaN(reminders_data[2]) && parseInt(reminders_data[2]) > 0) {
									// reset offset to 0
									widget_instance.attr('data-offset', 0);
									// show loading skeletons
									vrcWidgetRemindersSkeletons(wrapper);
									// reload the first page
									vrcWidgetRemindersLoad(wrapper);
								}
							}
						} catch(err) {
							console.error('could not parse JSON response', err, response);
						}
					},
					function(error) {
						// remove the skeleton loading
						widget_instance.find('.vrc-dashboard-cars-reminders-list').find('.vrc-dashboard-reminder-skeleton').remove();
						console.error(error);
					}
				);
			}

			/**
			 * Navigate between the various pages of the reminders.
			 */
			function vrcWidgetRemindersNavigate(wrapper, direction) {
				var widget_instance = jQuery('#' + wrapper);
				if (!widget_instance.length) {
					return false;
				}

				// current offset
				var current_offset = parseInt(widget_instance.attr('data-offset'));

				// steps per type
				var steps = <?php echo $this->reminders_per_page; ?>;

				// show loading skeletons
				vrcWidgetRemindersSkeletons(wrapper);

				// check direction and update offset for next nav
				if (direction > 0) {
					// navigate forward
					widget_instance.attr('data-offset', (current_offset + steps));
				} else {
					// navigate backward
					var new_offset = current_offset - steps;
					new_offset = new_offset >= 0 ? new_offset : 0;
					widget_instance.attr('data-offset', new_offset);
				}
				
				// launch navigation
				vrcWidgetRemindersLoad(wrapper);
			}

			/**
			 * Watch periodically if there are new reminders to be displayed.
			 */
			function vrcWidgetRemindersWatch(wrapper) {
				var widget_instance = jQuery('#' + wrapper);
				if (!widget_instance.length) {
					return false;
				}

				var latest_id = widget_instance.attr('data-latestid');
				if (!latest_id || !latest_id.length) {
					return false;
				}

				// the widget method to call
				var call_method = 'watchReminders';

				// make a request to watch the reminders
				vrcDoAjax(
					'index.php',
					{
						option: "com_vikrentcar",
						task: "exec_admin_widget",
						widget_id: "<?php echo $this->getIdentifier(); ?>",
						call: call_method,
						latest_id: latest_id,
						tmpl: "component"
					},
					function(response) {
						try {
							var obj_res = JSON.parse(response);
							if (!obj_res.hasOwnProperty(call_method)) {
								console.error('Unexpected JSON response', obj_res);
								return false;
							}
							// response will contain the number of new reminders
							if (isNaN(obj_res[call_method]) || parseInt(obj_res[call_method]) < 1) {
								// do nothing
								return;
							}
							// new reminders found, reset the offset and re-load the first page
							widget_instance.attr('data-offset', 0);
							// show loading skeletons
							vrcWidgetRemindersSkeletons(wrapper);
							// reload the first page
							vrcWidgetRemindersLoad(wrapper);
						} catch(err) {
							console.error('could not parse JSON response', err, response);
						}
					},
					function(error) {
						// do nothing
						console.error(error);
					}
				);
			}

			/**
			 * Count number of consecutive days
			 */
			function vrcCdayNoteCdaysCount(wrapper_instance) {
				var cdays = parseInt(jQuery('#vrc-newcdnote-cdays' + wrapper_instance).val());
				var defymd = jQuery('#new-cday-note-day' + wrapper_instance).val();
				if (isNaN(cdays) || cdays < 1) {
					jQuery('.vrc-newcdnote-dayto-val').text(defymd);
					return;
				}
				// calculate target (until) date
				var targetdate = new Date(defymd);
				targetdate.setDate(targetdate.getDate() + cdays);
				var target_y = targetdate.getFullYear();
				var target_m = targetdate.getMonth() + 1;
				target_m = target_m < 10 ? '0' + target_m : target_m;
				var target_d = targetdate.getDate();
				target_d = target_d < 10 ? '0' + target_d : target_d;
				// display target date
				var display_target = target_y + '-' + target_m + '-' + target_d;
				jQuery('.vrc-newcdnote-dayto-val').text(display_target);
			}

			/**
			 * Creates a new reminder
			 */
			function vrcAddCarDayNote(that) {
				var mainelem = jQuery(that).closest('.vrc-modal-cardaynotes-addnew');
				if (!mainelem) {
					return false;
				}
				var wrapper_instance = mainelem.attr('data-instance');
				var ymd = jQuery('#new-cday-note-day' + wrapper_instance).val();
				var carid = jQuery('#vrc-newcdnote-idcar' + wrapper_instance).val();
				var subcarid = 0;
				var note_name = jQuery('#vrc-newcdnote-name' + wrapper_instance).val();
				var note_descr = jQuery('#vrc-newcdnote-descr' + wrapper_instance).val();
				var note_cdays = jQuery('#vrc-newcdnote-cdays' + wrapper_instance).val();
				if ((!note_name.length && !note_descr.length) || !ymd.length || isNaN(carid)) {
					alert(Joomla.JText._('VRC_MISSING_REQFIELDS'));
					return false;
				}

				// make the AJAX request to the controller to add this note to the DB
				vrcDoAjax(
					'index.php',
					{
						option: "com_vikrentcar",
						task: "add_cardaynote",
						tmpl: "component",
						dt: ymd,
						idcar: carid,
						subunit: subcarid,
						type: "custom",
						name: note_name,
						descr: note_descr,
						cdays: note_cdays
					},
					function(response) {
						try {
							var stored_notes = JSON.parse(response);
							for (var keyid in stored_notes) {
								if (!stored_notes.hasOwnProperty(keyid)) {
									continue;
								}
								// tell the widget to reload the first page after a successful response
								var widget_instance = jQuery('#<?php echo $wrapper_id; ?>');
								// reset the offset and re-load the first page
								widget_instance.attr('data-offset', 0);
								// show loading skeletons
								vrcWidgetRemindersSkeletons('<?php echo $wrapper_id; ?>');
								// reload the first page
								vrcWidgetRemindersLoad('<?php echo $wrapper_id; ?>');
								// break the loop
								break;
							}
							// close modal
							hideVrcDialogCdaynotes();
							// reset modal input fields
							jQuery('#new-cday-note-day' + wrapper_instance).val('');
							jQuery('#vrc-newcdnote-idcar' + wrapper_instance).val('').trigger('change');
							jQuery('#vrc-newcdnote-name' + wrapper_instance).val('');
							jQuery('#vrc-newcdnote-descr' + wrapper_instance).val('');
							jQuery('#vrc-newcdnote-cdays' + wrapper_instance).val('0').trigger('change');
						} catch(err) {
							console.error('could not parse JSON response', err, response);
						}
					},
					function(error) {
						alert('Request failed');
						console.error(error);
					}
				);
			}

			/**
			 * Removes a reminder
			 */
			function vrcRemoveCdayNote(index, day, idcar, subunit, that) {
				if (!confirm(Joomla.JText._('VRC_WIDGETS_CONFRMELEM'))) {
					return false;
				}
				var elem = jQuery(that);
				elem.prop('disabled', true);
				
				// make the AJAX request to the controller to remove this note from the DB
				vrcDoAjax(
					'index.php',
					{
						option: "com_vikrentcar",
						task: "remove_cardaynote",
						tmpl: "component",
						dt: day,
						idcar: idcar,
						subunit: subunit,
						ind: index
					},
					function(res) {
						if (res.indexOf('e4j.ok') >= 0) {
							// remove note from widget
							var reminder_data = elem.closest('.vrc-modal-cardaynote-details-wrap').attr('data-reminder');
							var note_entry = jQuery('.vrc-dashboard-reminder-note[data-reminder="' + reminder_data + '"]');
							if (note_entry.length) {
								note_entry.remove();
							}
							// hide modal
							hideVrcDialogCdaynotes('details');
						} else {
							console.log(res);
							alert('Invalid response');
							elem.prop('disabled', false);
						}
					},
					function(error) {
						console.error(error);
						alert('Request failed');
						elem.prop('disabled', false);
					}
				);
			}

			function vrcEmptySearchFilter(widget_wrapper) {
				var widget_instance = jQuery('#' + widget_wrapper);
				if (!widget_instance.length) {
					return false;
				}
				// get search input field
				var widget_search = jQuery('.reminders-search[data-winstance="' + widget_wrapper + '"]');
				if (widget_search.length) {
					widget_search.val('');
				}
				// reset offset to 0
				widget_instance.attr('data-offset', 0);
				// show loading skeletons
				vrcWidgetRemindersSkeletons(widget_wrapper);
				// reload reminders from the first page
				vrcWidgetRemindersLoad(widget_wrapper);
			}

			/**
			 * Declare events for the DOM-ready
			 */
			jQuery(document).ready(function() {

				// keyboard event for closing the modal
				jQuery(document).keydown(function(e) {
					if (e.keyCode == 27) {
						if (vrcdialogcdaynotes_on === true) {
							hideVrcDialogCdaynotes();
						}
					}
				});
				
				// mouse-up event for closing the modal
				jQuery(document).mouseup(function(e) {
					if (!vrcdialogcdaynotes_on) {
						return false;
					}
					if (vrcdialogcdaynotes_on) {
						// make sure to not hide the dialog if we clicked on a date to select
						var allowed_target_classes = ['day', 'date', 'ui-', 'select2'];
						var target_classes = jQuery(e.target).attr('class');
						if (target_classes && target_classes.length) {
							for (var i = 0; i < allowed_target_classes.length; i++) {
								if (target_classes.indexOf(allowed_target_classes[i]) >= 0) {
									// protected class found
									return false;
								}
							}
						}
						// check if the click was inside the modal
						var vrc_overlay_cont = jQuery(".vrc-modal-overlay-content-cardaynotes");
						if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
							hideVrcDialogCdaynotes();
						}
					}
				});

				// new note modal select2 for vehicle ID
				jQuery('#vrc-newcdnote-idcar<?php echo $wrapper_instance; ?>').select2({
					placeholder: "-----",
					width: "200px"
				});

				/**
				 * Add event listener to the search by hashtag keyup event with debounce handler (one per widget instance).
				 */
				var reminder_inputs = document.getElementsByClassName('reminders-search');
				if (reminder_inputs.length) {
					for (var i = 0; i < reminder_inputs.length; i++) {
						reminder_inputs[i].addEventListener('keyup', vrcDebounceEvent(function() {
							var widget_wrapper = this.getAttribute('data-winstance');
							var widget_instance = jQuery('#' + widget_wrapper);
							if (!widget_instance.length) {
								return false;
							}
							// reset offset to 0
							widget_instance.attr('data-offset', 0);
							// show loading skeletons
							vrcWidgetRemindersSkeletons(widget_wrapper);
							// reload reminders from the first page
							vrcWidgetRemindersLoad(widget_wrapper);
						}, 500));
					}
				}

			});
			
		</script>
		<?php
		}
		?>

		<script type="text/javascript">

			jQuery(document).ready(function() {

				// when document is ready, load latest reminders for this widget's instance
				vrcWidgetRemindersLoad('<?php echo $wrapper_id; ?>');

				// set interval for loading new reminders automatically
				setInterval(function() {
					vrcWidgetRemindersWatch('<?php echo $wrapper_id; ?>');
				}, 60000);

			});
			
		</script>

		<?php
	}

	/**
	 * Returns an associative array with the cars information.
	 * 
	 * @return 	array
	 */
	protected function loadCarsInformation()
	{
		$dbo = JFactory::getDbo();
		$cars_info = array();
		
		$q = "SELECT `id`,`name`,`img`,`units`,`moreimgs`,`params` FROM `#__vikrentcar_cars` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$records = $dbo->loadAssocList();
			foreach ($records as $crecord) {
				if (!empty($crecord['img'])) {
					// use the main photo of the vehicle
					if (is_file(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'vthumb_' . $crecord['img'])) {
						$crecord['img'] = VRC_ADMIN_URI . 'resources/vthumb_' . $crecord['img'];
					} else {
						$crecord['img'] = VRC_ADMIN_URI . 'resources/' . $crecord['img'];
					}
				} elseif (!empty($crecord['moreimgs'])) {
					// grab the first extra photo
					$moreimages = explode(';;', $crecord['moreimgs']);
					foreach ($moreimages as $mimg) {
						if (!empty($mimg)) {
							$crecord['img'] = VRC_ADMIN_URI . 'resources/thumb_' . $mimg;
							break;
						}
					}
				} else {
					// no photo
					$crecord['img'] = '';
				}
				$crecord['features'] = VikRentCar::getCarFirstFeatures($crecord);
				$cars_info[$crecord['id']] = $crecord;
			}
		}

		return $cars_info;
	}
}
