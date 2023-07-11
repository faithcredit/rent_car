<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	system
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

VikRentCarLoader::import('update.manager');
VikRentCarLoader::import('update.license');

/**
 * Class used to handle the activation, deactivation and 
 * uninstallation of VikRentCar plugin.
 *
 * @since 1.0
 */
class VikRentCarInstaller
{
	/**
	 * Flag used to init the class only once.
	 *
	 * @var boolean
	 */
	protected static $init = false;

	/**
	 * Initialize the class attaching wp actions.
	 *
	 * @return 	void
	 */
	public static function onInit()
	{
		// init only if not done yet
		if (static::$init === false)
		{
			// handle installation message
			add_action('admin_notices', array('VikRentCarInstaller', 'handleMessage'));

			/**
			 * Register hooks and actions here
			 */

			// mark flag as true to avoid init it again
			static::$init = true;
		}

		/**
		 * Check whether the Pro license has expired.
		 * 
		 * @since 	1.2.0
		 */
		add_action('admin_notices', array('VikRentCarInstaller', 'handleUpdateWarning'));
	}

	/**
	 * Handles the activation of the plugin.
	 *
	 * @param 	boolean  $message 	True to display the activation message,
	 * 								false to ignore it.
	 *
	 * @return 	void
	 */
	public static function activate($message = true)
	{
		// get installed software version
		$version = get_option('vikrentcar_software_version', null);

		// check if the plugin has been already installed
		if (is_null($version))
		{
			// dispatch UPDATER to launch installation queries
			VikRentCarUpdateManager::install();

			// mark the plugin has installed to avoid duplicated installation queries
			update_option('vikrentcar_software_version', VIKRENTCAR_SOFTWARE_VERSION);
		}

		if ($message)
		{
			// set activation flag to display a message
			add_option('vikrentcar_onactivate', 1);
		}
	}

	/**
	 * Handles the deactivation of the plugin.
	 *
	 * @return 	void
	 */
	public static function deactivate()
	{
		// do nothing for the moment
	}

	/**
	 * Handles the uninstallation of the plugin.
	 *
	 * @param 	boolean  $drop 	True to drop the tables of VikRentCar from the database.
	 *
	 * @return 	void
	 */
	public static function uninstall($drop = true)
	{
		// dispatch UPDATER to drop database tables
		VikRentCarUpdateManager::uninstall($drop);

		// delete installation flag
		delete_option('vikrentcar_software_version');
	}

	/**
	 * Handles the uninstallation of the plugin.
	 * Proxy for uninstall method which always force database drop.
	 *
	 * @return 	void
	 *
	 * @uses 	uninstall()
	 *
	 * @since 	1.2.6
	 */
	public static function delete()
	{
		// complete uninstallation by dropping the database
		static::uninstall(true);
	}

	/**
	 * Checks if the current version should be updated
	 * and, eventually, processes it.
	 * 
	 * @return 	void
	 */
	public static function update()
	{
		// get installed software version
		$version = get_option('vikrentcar_software_version', null);

		$app = JFactory::getApplication();

		// check if we are running an older version
		if (VikRentCarUpdateManager::shouldUpdate($version))
		{
			/**
			 * Avoid useless redirections if doing ajax.
			 * 
			 * @since 1.1.6
			 */
			if (!wp_doing_ajax() && $app->isAdmin())
			{
				// Turn on maintenance mode before running the update.
				// In case the maintenance mode was already active, then
				// an error message will be thrown.
				static::setMaintenance(true);

				// process the update (we don't need to raise an error)
				VikRentCarUpdateManager::update($version);

				// update cached plugin version
				update_option('vikrentcar_software_version', VIKRENTCAR_SOFTWARE_VERSION);

				// deactivate the maintenance mode on update completion
				static::setMaintenance(false);

				// check if pro version
				if (VikRentCarLicense::isPro())
				{
					// go to the pro-package download page
					$app->redirect('index.php?option=com_vikrentcar&view=getpro&version=' . $version);
					exit;
				}
			}
		}
		// check if the current instance is a new blog of a network
		else if (is_null($version))
		{
			/**
			 * The version is NULL, vikrentcar_software_version doesn't
			 * exist as an option of this blog.
			 * We need to launch the installation manually.
			 *
			 * @see 	activate()
			 *
			 * @since 	1.0.6
			 */

			// Use FALSE to ignore the activation message
			static::activate(false);
		}
	}

	/**
	 * Callback used to complete the update of the plugin
	 * made after a scheduled event.
	 *
	 * @param 	array  $results  The results of all attempted updates.
	 *
	 * @return 	void
	 *
	 * @since 	1.2.0
	 */
	public static function automaticUpdate($results)
	{
		// create log trace
		$trace = '### VikRentCar Automatic Update | ' . JHtml::_('date', new JDate(), 'Y-m-d H:i:s') . "\n\n";
		$trace .= "```json\n" . json_encode($results, JSON_PRETTY_PRINT) . "\n```\n\n";

		if (empty($results['plugin']))
		{
			$results['plugin'] = [];
		}

		// iterate all plugins
		foreach ($results['plugin'] as $plugin)
		{
			if (!empty($plugin->item->slug))
			{
				// register check trace
				$trace .= "Does `{$plugin->item->slug}` match `vikrentcar`?\n\n";

				// make sure the plugin slug matches this one
				if ($plugin->item->slug == 'vikrentcar')
				{
					// register status trace
					$trace .= "Did WP complete the update without errors? [" . ($plugin->result ? 'Y' : 'N') . "]\n\n";

					// plugin found, make sure the update was successful
					if ($plugin->result)
					{
						try
						{
							// register version trace
							$trace .= sprintf("Updating from [%s] to [%s]...\n\n", VIKRENTCAR_SOFTWARE_VERSION, $plugin->item->new_version);

							// complete the update in background
							static::backgroundUpdate($plugin->item->new_version);

							// update completed without errors
							$trace .= "Background update completed\n\n";
						}
						catch (Exception $e)
						{
							// something went wrong, register error within the trace
							$trace .= sprintf(
								"An error occurred while trying to finalize the update (%d):\n> %s\n\n",
								$e->getCode(),
								$e->getMessage()
							);

							/**
							 * @todo An error occurred while trying to download the PRO version,
							 *       evaluate to send an e-mail to the administrator.
							 */
						}
					}
				}
			}
		}

		// register debug trace within a log file
		JLoader::import('adapter.filesystem.file');
		JFile::write(VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . 'au-log.md', $trace . "---\n\n");
	}

	/**
	 * Same as update task, but all made in background.
	 *
	 * @param 	string  $new_version  The new version of the plugin.
	 * 
	 * @return 	void
	 *
	 * @since 	1.2.0
	 */
	protected static function backgroundUpdate($new_version)
	{
		// get installed software version
		$version = get_option('vikrentcar_software_version', null);

		// DO NOT use shouldUpdate method because, since we are always within
		// the same flow, the version constant is still referring to the previous
		// version. So, always assume to proceed with the update of the plugin.

		// Turn on maintenance mode before running the update.
		// In case the maintenance mode was already active, then
		// an error message will be thrown.
		static::setMaintenance(true);
		
		// process the update (we don't need to raise an error)
		VikRentCarUpdateManager::update($version);

		// update cached plugin version
		update_option('vikrentcar_software_version', $new_version);

		// deactivate the maintenance mode on update completion
		static::setMaintenance(false);

		// check if pro version
		if (VikRentCarLicense::isPro())
		{
			// load license model
			$model = JModel::getInstance('vikrentcar', 'license', 'admin');

			// download PRO version hoping that all will go fine
			$result = $model->download(VikRentCarLicense::getKey());

			if ($result === false)
			{
				// an error occurred, retrieve it as exception
				$error = $model->getError(null, $toString = false);

				// propagate exception
				throw $error;
			}
		}
	}

	/**
	 * Checks whether the automatic updates should be turned off.
	 * This is useful to prevent auto-updates for those customers
	 * that are running an expired PRO version. This will avoid
	 * losing the files after an unexpected update.
	 *
	 * @param 	boolean  $update  The current auto-update choice.
	 * @param 	object   $item    The plugin offer.
	 *
	 * @return 	mixed    Null to let WP decides, false to always deny it.
	 *
	 * @since 	1.2.0
	 */
	public static function useAutoUpdate($update, $item)
	{
		// make sure we are fetching VikRentCar
		if (!empty($item->slug) && $item->slug == 'vikrentcar')
		{
			// plugin found, lets check whether the user is
			// not running the PRO version
			if (!VikRentCarLicense::isPro())
			{
				// not a PRO version, check whether a license
				// key was registered
				if (VikRentCarLicense::getKey())
				{
					// The plugin registered a key; the customer
					// chose to let the license expires...
					// We need to prevent auto-updates.
					$update = false;
				}
			}
		}

		return $update;
	}

	/**
	 * Toggle maintenance mode for the site.
	 * Creates/deletes the maintenance file to enable/disable maintenance mode.
	 *
	 * @param 	boolean  $enable  True to enable maintenance mode, false to disable.
	 *
	 * @return 	void
	 *
	 * @since 	1.2.0
	 * @since 	1.2.2 	the maintenance mode also relies on database options, not only on a file.
	 */
	protected static function setMaintenance($enable)
	{
		$maintenance_file_path = VIKRENTCAR_BASE . '/maintenance.txt';
		$maintenance_db_option = 'vikrentcar_maintenance';

		if ($enable)
		{
			// check if we are in maintenance mode
			if (JFile::exists($maintenance_file_path) && get_option($maintenance_db_option, null) !== null)
			{
				// raise error message in case the update process is currently running
				wp_die(
					'<h1>Maintenance</h1>' .
					'<p>VikRentCar is in maintenance mode. Please wait a minute for the update to complete.</p>',
					423 // locked
				);
			}

			// enter maintenance mode for the current version
			JFile::write($maintenance_file_path, VIKRENTCAR_SOFTWARE_VERSION);
			update_option($maintenance_db_option, VIKRENTCAR_SOFTWARE_VERSION);
		}
		else
		{
			// turn off maintenance mode
			JFile::delete($maintenance_file_path);
			delete_option($maintenance_db_option);
		}
	}

	/**
	 * In case of an expired PRO version, prompts a message informing
	 * the user that it is going to lose the PRO features.
	 *
	 * @param  array  $data      An array of plugin metadata.
 	 * @param  array  $response  An array of metadata about the available plugin update.
	 *
	 * @return 	void
	 *
	 * @since 	1.2.0
	 */
	public static function getUpdateMessage($data, $response)
	{
		// check whether the user is not running the PRO version
		if (!VikRentCarLicense::isPro())
		{
			// not a PRO version, check whether a license
			// key was registered
			if (VikRentCarLicense::getKey())
			{
				// The plugin registered a key; the customer
				// chose to let the license expires...
				// We need to display an alert.
				add_action('admin_footer', function() use ($data, $response)
				{
					// display layout
					echo JLayoutHelper::render(
						'html.license.update',
						array($data, $response),
						null,
						array('component' => 'com_vikrentcar')
					);
				});
			}
		}
	}

	/**
	 * Method used to check for any installation message to show.
	 *
	 * @return 	void
	 */
	public static function handleMessage()
	{
		$app = JFactory::getApplication();

		// if we are in the admin section and the plugin has been activated
		if ($app->isAdmin() && get_option('vikrentcar_onactivate') == 1)
		{
			// delete the activation flag to avoid displaying the message more than once
			delete_option('vikrentcar_onactivate');

			?>
			<div class="notice is-dismissible notice-success">
				<p>
					<strong>Thanks for activating our plugin!</strong>
					<a href="https://vikwp.com" target="_blank">https://vikwp.com</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Check if the Pro version has expired to alert the user that an
	 * update would actually mean downgrading to the free version.
	 * 
	 * @since 	1.2.0
	 */
	public static function handleUpdateWarning()
	{
		global $pagenow;
	
		if ($pagenow == 'plugins.php' && VikRentCarLicense::isExpired())
		{
			if (!JFactory::getApplication()->input->cookie->getInt('vrc_update_warning_hide', 0))
			{
				?>
				<div class="notice is-dismissible notice-warning" id="vrc-update-warning">
					<p>
						<strong><?php echo JText::_('VRCPROVEXPWARNUPD'); ?></strong>
					</p>
				</div>

				<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('#vrc-update-warning').on('click', '.notice-dismiss', function(event, el) {
						var numWeeks = 1;
						if (typeof localStorage !== 'undefined') {
							numWeeks = localStorage.getItem('vrc_update_warning_hide_count');
							numWeeks = !numWeeks ? 0 : parseInt(numWeeks);
							numWeeks++;
							localStorage.setItem('vrc_update_warning_hide_count', numWeeks);
						}
						var nd = new Date();
						nd.setDate(nd.getDate() + (7 * numWeeks));
						document.cookie = 'vrc_update_warning_hide=1; expires=' + nd.toUTCString() + '; path=/; SameSite=Lax';
					});
				});
				</script>
				<?php
			}
		}
	}
}
