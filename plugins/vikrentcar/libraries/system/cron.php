<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	system
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class used to handle the cron jobs execution.
 *
 * @since 1.3.0
 */
class VikRentCarCron
{
	/**
	 * Schedules the published cron jobs to be executed according
	 * to the selected recurrence.
	 * 
	 * @return 	void
	 */
	public static function setup()
	{
		static $setup = false;

		if ($setup)
		{
			// setup is allowed only once
			return;
		}

		// prevent double setup
		$setup = true;

		/**
		 * Filters the non-default cron schedules.
		 * Adds support to repeat the cron jobs every 5 minutes, every half hour,
		 * every 2 hours, and every month.
		 *
		 * @param 	array 	$schedules 	An array of non-default cron schedules.
		 */
		add_filter('cron_schedules', function($schedules)
		{
			// add support for "every 30 minutes" recurrence
			$schedules['every_5_minutes'] = array(
				'interval' => MINUTE_IN_SECONDS * 5,
				'display'  => __('Every 5 Minutes', 'vikrentcar'),
			);

			// add support for "every 30 minutes" recurrence
			$schedules['half_hour'] = array(
				'interval' => HOUR_IN_SECONDS / 2,
				'display'  => __('Every Half Hour', 'vikrentcar'),
			);

			// add support for "every 2 hours" recurrence
			$schedules['every_2_hours'] = array(
				'interval' => HOUR_IN_SECONDS * 2,
				'display'  => __('Every 2 Hours', 'vikrentcar'),
			);

			// add support for "every month" recurrence
			$schedules['monthly'] = array(
				'interval' => MONTH_IN_SECONDS,
				'display'  => __('Monthly', 'vikrentcar'),
			);

			return $schedules;
		});

		/**
		 * Trigger event to allow the plugins to include custom HTML within the view. 
		 * It is possible to return an associative array to group the HTML strings
		 * under different fieldsets. Plain/html string will be always pushed within
		 * the "custom" fieldset instead.
		 *
		 * Displays the field to select the cron job recurrence.
		 *
		 * @param 	mixed   $forms  The HTML to display.
		 * @param 	mixed   $view 	The current view instance.
		 *
		 * @since 	1.3.0
		 */
		add_filter('vikrentcar_display_view_cron', array('VikRentCarCron', 'addScheduleControlForm'), 10, 2);

		/**
		 * Trigger event to allow the plugins to make something before saving
		 * a record in the database. Used to extend the cron jobs management
		 * in order to support the selection of the recurrence.
		 *
		 * @param 	bool   $save  False to abort the saving process.
		 * @param 	array  $args  The saved record.
		 *
		 * @since 	1.3.0
		 */
		add_filter('vikrentcar_before_save_cronjob', array('VikRentCarCron', 'saveScheduleControl'), 10, 2);

		/**
		 * Trigger event to allow the plugins to make something after saving
		 * a record in the database. Used to schedule the cron job execution.
		 *
		 * @param 	mixed  $status  The saving status.
		 * @param 	array  $args    The saved record.
		 * @param 	bool   $is_new  True in case of insert.
		 *
		 * @since 	1.3.0
		 */
		add_filter('vikrentcar_after_save_cronjob', array('VikRentCarCron', 'checkSchedulingAfterSave'), 10, 3);

		/**
		 * Trigger event to allow the plugins to make something after publishing or
		 * unpublishing one or more records. Used to unschedule the unpublished cron jobs.
		 *
		 * @param 	bool   $delete  False to abort the deleting process.
		 * @param 	array  $ids     An array of IDs to delete.
		 *
		 * @since 	1.3.0
		 */
		add_filter('vikrentcar_before_delete_cronjob', array('VikRentCarCron', 'checkSchedulingBeforeDelete'), 10, 2);

		// fetch list of cron jobs
		$crons = static::getJobs();

		if (!$crons)
		{
			// nothing to execute
			return;
		}

		$model = VRCMvcModel::getInstance('cronjob');

		// iterate cron jobs list
		foreach ($crons as $id_cron)
		{
			// get cron job details
			$cron = $model->getItem($id_cron);

			if (!$cron)
			{
				// cron not found, go ahead
				continue;
			}

			// fetch recurrence interval
			$interval = !empty($cron->schedule_key) ? $cron->schedule_key : 'daily';

			// build cron listener hook
			$hook = static::getScheduleHook($cron);

			/**
			 * Action used to execute all the cron jobs that have been created 
			 * through the VikRentCar panel. Only published cron jobs can
			 * be executed.
			 *
			 * The scheduling of this hook must be registered through
			 * WordPress in order to be executed.
			 */
			add_action($hook, function() use ($id_cron)
			{
				VikRentCarCron::runJob($id_cron);
			});

			// Make sure the cron event hasn't been yet scheduled.
			// After its execution, wp_next_scheduled will return false and
			// we will be able to register it again.
			if (!wp_next_scheduled($hook))
			{
				// schedule event starting from the current time for every minute, by
				// launching the cron listener hook (3rd argument)
				wp_schedule_event(time(), $interval, $hook);
			}
		}
	}

	/**
	 * Executes the specified cron job.
	 *
	 * @param 	integer  $id_cron  The cron job ID.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	public static function runJob($id_cron)
	{
		// require the main library in case WPCron runs the job
		require_once VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikrentcar.php';

		$error  = null;
		$status = false;

		// force the option in request to prevent weird behaviors
		JFactory::getApplication()->input->set('option', 'com_vikrentcar');

		// get CRON JOB model
		$model = VRCMvcModel::getInstance('cronjob');

		try
		{
			// dispatch cron job
			$status = $model->dispatch($id_cron);

			if (!$status)
			{
				// get any registered error
				$error = $model->getError($last = null, $string = true);
			}
		}
		catch (Exception $e)
		{
			// catch any exception and go ahead
			$error = $e->getMessage();
		}
		catch (Throwable $e)
		{
			// catch any failure and go ahead
			$error = sprintf('%s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine());
		}

		if (!$status && $error)
		{
			$item = $model->getItem($id_cron);

			if ($item)
			{
				// register error message within the logs of the cron job
				$item->logs = "<div style=\"color: #b90c0d;\">$error</div>\n<hr />\n" . $item->logs;

				$model->save($item);
			}
		}

		return $status;
	}

	/**
	 * Returns a list of published cron jobs.
	 * 
	 * @param   boolean  True to return all the cron job columns.
	 * 
	 * @return 	array    An array of cron jobs.
	 */
	public static function getJobs($all = false)
	{
		$dbo = JFactory::getDbo();

		// retrieve all the published cron jobs
		$q = $dbo->getQuery(true)
			->from($dbo->qn('#__vikrentcar_cronjobs'))
			->where($dbo->qn('published') . ' = 1');

		if ($all)
		{
			$q->select('*');
		}
		else
		{
			$q->select($dbo->qn('id'));
		}

		$dbo->setQuery($q);

		if ($all)
		{
			return $dbo->loadObjectList();
		}

		return $dbo->loadColumn();
	}

	/**
	 * Creates the HTML to support a field for the selection of the
	 * recurrence while creating/editing a cron job.
	 * 
	 * @param 	array 	$forms  An associative array containing the HTML.
	 * @param 	object 	$view   The view instance.
	 * 
	 * @return 	array 	$forms  The resulting HTML array.
	 */
	public static function addScheduleControlForm($forms, $view)
	{
		if (!is_array($forms))
		{
			$forms = [];
		}

		if (!isset($forms['cronjob']))
		{
			// init details section
			$forms['cronjob'] = '';
		}

		$schedule_key = null;

		// check whether the schedule key property exists
		if (!$view->row || !array_key_exists('schedule_key', $view->row))
		{
			static::installScheduleControl();
		}
		else
		{
			$schedule_key = $view->row['schedule_key'];
		}

		// fetch list of supported schedule intervals
		$schedules = wp_get_schedules();

		// sort the options in ascending order
		uasort($schedules, function($a, $b)
		{
			return $a['interval'] - $b['interval'];
		});

		$options = [];

		foreach ($schedules as $k => $schedule)
		{
			$options[] = JHtml::_('select.option', $k, $schedule['display']);
		}

		$options = JHtml::_('select.options', $options, 'value', 'text', $schedule_key ?: 'daily');

		$label = __('Recurrence', 'vikrentcar');

		// build HTML control
		$forms['cronjob'] .=
<<<HTML
<div class="vrc-param-container">
	<div class="vrc-param-label">$label</div>
	<div class="vrc-param-setting"><select name="schedule_key">$options</select></div>
</div>
HTML
		;

		return $forms;
	}

	/**
	 * Executes while saving a cron job, so that it is possible to 
	 * inject within the array to save the selected recurrence.
	 * 
	 * @param 	boolean  $save  The saving flag.
	 * @param 	array 	 &$src  The array holding the data to save.
	 * 
	 * @return 	boolean  False to abort the saving process.
	 * 
	 */
	public static function saveScheduleControl($save, &$src)
	{
		if (is_null($save))
		{
			$save = true;
		}

		$src = (array) $src;

		$input = JFactory::getApplication()->input;

		// fetch schedule key from request
		$schedule_key = $input->get('schedule_key', null, 'string');

		// make sure the schedule key has been specified
		if (!is_null($schedule_key))
		{
			// inject schedule key into the array to save
			$src['schedule_key'] = $schedule_key;
		}

		return $save;
	}

	/**
	 * Checks the cron job scheduling after updating a record.
	 * 
	 * @param 	boolean  $status  Dummy argument for WordPress bc.
	 * @param 	array    $cron    The cron jobs details.
	 * @param 	boolean  $is_new  True in case of insert.
	 * 
	 * @return 	void
	 */
	public static function checkSchedulingAfterSave($status, $cron, $is_new)
	{
		if (!$is_new)
		{
			// check only in case of update
			static::checkJobScheduling($cron);
		}
	}

	/**
	 * Unschedules the cron jobs that are going to be deleted.
	 * 
	 * @param 	boolean  $delete  The deleting flag.
	 * @param 	object   $item    The item to delete.
	 * 
	 * @return 	boolean  False to abort the deleting process.
	 */
	public static function checkSchedulingBeforeDelete($delete, $item)
	{
		if (is_null($delete))
		{
			$delete = true;
		}

		if ($delete)
		{
			// turn off publishing to detach the scheduling
			$item->published = 0;

			// trigger changes
			static::checkJobScheduling($item);
		}

		return $delete;
	}

	/**
	 * Checks whether an existing schedule should be recreated as a result
	 * of any significant changes.
	 * 
	 * @param 	array  $cron  The cron record.
	 * 
	 * @return 	void
	 */
	protected static function checkJobScheduling($cron)
	{
		$cron = (array) $cron;

		if (!array_key_exists('published', $cron) || !array_key_exists('schedule_key', $cron))
		{
			// reload all the details of the updated cron job
			$cron = VRCMvcModel::getInstance('cronjob')->getItem(@$cron['id']);
		}
		else
		{
			// cast to object
			$cron = (object) $cron;
		}

		if (!$cron)
		{
			// ops...
			return;
		}

		if (!isset($cron->schedule_key))
		{
			// property not yet installed, use the default one to properly
			// unschedule the registered event
			$cron->schedule_key = 'daily';
		}

		// build cron listener hook
		$hook = static::getScheduleHook($cron);

		// fetch the next scheduled event
		$event = wp_get_scheduled_event($hook);

		if (!$event)
		{
			// no scheduled event, do nothing...
			return;
		}

		if (!$cron->published || $cron->schedule_key !== $event->schedule)
		{
			// unschedule the event
			wp_unschedule_event($event->timestamp, $hook);
		}
	}

	/**
	 * Returns the hook to be used while scheduling the execution
	 * of a cron job.
	 * 
	 * @param 	object 	$cron  The cron object.
	 * 
	 * @return 	string  The resulting hook.
	 */
	protected static function getScheduleHook($cron)
	{
		$cron = (object) $cron;

		// build cron listener hook
		return implode('_', [
			'vikrentcar',
			'cron',
			preg_replace("/\.php$/", '', $cron->class_file),
			$cron->id,
		]);
	}

	/**
	 * Install the resources needed to support a custom recurrence for
	 * the created cron jobs.
	 * 
	 * @return 	void
	 */
	protected static function installScheduleControl()
	{
		$fields = VRCMvcModel::getInstance('cronjob')->getTableColumns();

		if (!isset($fields['schedule_key']))
		{
			$db = JFactory::getDbo();

			// alter the table to allow the storage of the selected recurrence
			$query = "ALTER TABLE `#__vikrentcar_cronjobs` ADD COLUMN `schedule_key` varchar(64) DEFAULT NULL AFTER `published`";

			$db->setQuery($query);
			$db->execute();
		}
	}
}
