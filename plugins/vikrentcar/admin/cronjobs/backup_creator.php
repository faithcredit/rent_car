<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

class VikRentCarCronJobBackupCreator extends VRCCronJob
{
	// do not need to track the elements
	use VRCCronTrackerUnused;

	/**
	 * This method should return all the form fields required to collect the information
	 * needed for the execution of the cron job.
	 * 
	 * @return  array  An associative array of form fields.
	 */
	public function getForm()
	{
		return [
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4><i class="' . VikRentCarIcons::i('archive') . '"></i>&nbsp;<i class="' . VikRentCarIcons::i('clock') . '"></i>&nbsp;' . $this->getTitle() . '</h4>',
			],
			'maxbackup' => [
				'type'    => 'number',
				'label'   => JText::_('VRC_CRONJOB_BACKUP_CREATOR_FIELD_MAX'),
				'help'    => JText::_('VRC_CRONJOB_BACKUP_CREATOR_FIELD_MAX_DESC'),
				'min'     => 0,
				'step'    => 1,
				'default' => 5,
			],
			'help' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<p class="vrc-cronparam-suggestion"><i class="vrcicn-lifebuoy"></i>' . JText::_('VRC_CRONJOB_BACKUP_CREATOR_DESCRIPTION') . '</p>',
			],
		];
	}

	/**
	 * Returns the title of the cron job.
	 * 
	 * @return  string
	 */
	public function getTitle()
	{
		return JText::_('VRC_CRON_BACKUP_CREATOR_TITLE');
	}
	
	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function execute()
	{
		// create backup model
		$model = new VRCModelBackup();

		try
		{
			// create back-up
			$archive = $model->save([
				'action' => 'create',
				'prefix' => 'cron_',
			]);

			if (!$archive)
			{
				// an error occurred while creating the backup archive
				throw new Exception($model->getError($index = null, $string = true), 500);
			}

			// register response
			$this->appendLog("Back-up created successfully!\n\n<b>{$archive}</b>");
		}
		catch (Exception $e)
		{
			// an error occurred while creating the back-up
			$this->appendLog('<p style="color: #900;">' . $e->getMessage() . '</p>');
			
			return false;
		}

		// get list of created archives
		$files = JFolder::files(dirname($archive), '^cron_backup_', $recursive = false, $fullpath = true);

		// check whether the number of created backups exceeded the maximum threshold
		$diff = count($files) - max([1, abs(@$this->params->get('maxbackup', 5))]);
		
		if ($diff > 0)
		{
			// sort the files by ascending creation date
			usort($files, function($a, $b)
			{
				return filemtime($a) - filemtime($b);
			});

			// take the first N archives to delete
			foreach (array_splice($files, 0, $diff) as $file)
			{
				// delete the files
				if (JFile::delete($file))
				{
					$this->appendLog("\n\nDeleted old archive: <b>" . basename($file) . '</b>');
				}
			}
		}

		return true;
	}
}
