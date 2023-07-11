<?php
/** 
 * @package     VikRentCar
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * VikRentCar cron job model.
 *
 * @since 1.3.0
 */
class VRCModelCronjob extends VRCMvcModel
{
	/**
	 * The database table name.
	 * 
	 * @var string
	 */
	protected $tableName = '#__vikrentcar_cronjobs';

	/**
	 * Basic item loading implementation.
	 *
	 * @param   mixed  $pk   An optional primary key value to load the row by, or an array of fields to match.
	 *                       If not set the instance property value is used.
	 *
	 * @return  mixed  The record object on success, null otherwise.
	 */
	public function getItem($pk)
	{
		$item = parent::getItem($pk);

		if ($item)
		{
			// auto-decode parameters in array format for backward compatibility
			$item->params = $item->params ? (array) json_decode($item->params, true) : [];
			// auto-decode flag characters in array format for backward compatibility
			$item->flag_char = $item->flag_char ? (array) json_decode($item->flag_char, true) : [];
		}

		return $item;
	}

	/**
	 * Runs before saving the given data.
	 * It is possible to inject here additional properties to save.
	 * 
	 * @param   array    &$data  A reference to the data to save.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function preflight(array &$data)
	{
		if (isset($data['params']))
		{
			// sanitize cron job parameters before save
			foreach ($data['params'] as $setting => $cont)
			{
				if (!$setting)
				{
					continue;
				}

				// replace any possible placeholder for special tags
				$cont = preg_replace_callback("/(<strong class=\"vrc-editor-hl-specialtag\">)([^<]+)(<\/strong>)/", function($match)
				{
					return $match[2];
				}, $cont);

				// update parameter
				$data['params'][$setting] = $cont;
			}
		}

		if (isset($data['logs']))
		{
			// make sure the logs length does not exceed the maximum number of allowed characters (65535)
			$data['logs'] = mb_substr($data['logs'], 0, pow(2, 16) - 1, 'UTF-8');
		}

		return parent::preflight($data);
	}

	/**
	 * Method used to dispatch a cron job.
	 *
	 * @param   int|object  $cron     Either a cron job ID or an object.
	 * @param   array       $options  A configuration array:
	 *                                - debug   bool    whether to enter in debug mode;
	 *                                - key     string  the secret key to validate;
	 *                                - strict  bool    whether to take only published records.
	 *
	 * @return 	int|bool    The cron response code on success, false otherwise.
	 */
	public function dispatch($cron, array $options = [])
	{
		// fetch cron ID
		$id_cron = is_object($cron) ? @$cron->id : $cron;

		if (!$id_cron || !is_numeric($id_cron))
		{
			$this->setError(new InvalidArgumentException('Invalid cron ID', 400));
			return false;
		}

		// validate the cron secure key only if explictly provided within the configuration array
		if (array_key_exists('key', $options) && $options['key'] != md5(VikRentCar::getCronKey()))
		{
			$this->setError(new InvalidArgumentException('The given secret key does not match', 401));
			return false;
		}
		
		if (!is_object($cron))
		{
			// cron ID given, load item details
			$cron = $this->getItem($id_cron);
		}

		if (!$cron)
		{
			$this->setError(new RuntimeException(sprintf('Unable to find cron [%d]', $id_cron), 404));
			return false;
		}

		$options['strict'] = isset($options['strict']) ? (bool) $options['strict'] : true;

		if (!$cron->published && $options['strict'])
		{
			$this->setError(new RuntimeException(sprintf('The cron [%d] is not published', $id_cron), 403));
			return false;
		}

		try
		{
			// attempt to create a new job instance
			$job = VRCFactory::getCronFactory()->createInstance($cron->class_file, $cron);
		}
		catch (Exception $e)
		{
			// an error has occurred
			$this->setError($e);
			return false;
		}

		// in case of debug mode, start buffering the text echoed by the cron job
		if (!empty($options['debug']))
		{
			$job->setDebug(true);

			ob_start();
		}

		$response = (int) $job->run();

		if (!empty($options['debug']))
		{
			// register cron output in a property
			$this->set('output', ob_get_contents());

			ob_end_clean();
		}

		// register logs in a model property to allow the client to easily access it
		$this->set('log', $job->getLog());

		// fetch cron job data
		$data = $job->getData();

		// update last execution time
		$data->last_exec = time();

		if ($this->get('log'))
		{
			// prepend new logs to the existing ones
			$data->logs = date('c') . "\n" . trim($this->get('log')) . "\n<hr />\n" . $data->logs;
		}

		// commit cron job changes
		if (!$this->save((array) $data))
		{
			// something went wrong, try to display the error message within the ouput
			$error  = $this->getError($last = null, $string = true);
			$output = "<p style=\"color: #b90c0d;\">An error has occurred while saving the cron job. $error</p>";
			$this->set('output', $this->get('output', '') . $output);
		}

		return $response;
	}
}
