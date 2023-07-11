<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	update
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.database.helper');
VikRentCarLoader::import('update.changelog');
VikRentCarLoader::import('update.license');

/**
 * Class used to handle the upgrade of the plugin.
 *
 * @since 1.0
 */
class VikRentCarUpdateManager
{

	/**
	 * For bc with PHP < 5.6, the private static var is now
	 * returned by this private method, because class properties
	 * do not support concatenation, and so we can't build the array.
	 *
	 * @return 	array  the list of template files used as Options to temporary store modifications.
	 * 
	 * @since 	1.0.0
	 */
	private static function getTemplateFiles()
	{
		return array(
			VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'email_tmpl.php',
			VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'error_form.php',
			VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'pdf_tmpl.php',
			VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'checkin_pdf_tmpl.php',
			VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR . 'invoice_tmpl.php',
			VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'vikrentcar_custom.css',
			VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'tracking_code_tmpl.php',
			VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'conversion_code_tmpl.php',
		);
	}

	/**
	 * Checks if the current version should be updated.
	 *
	 * @param 	string 	 $version 	The version to check.
	 *
	 * @return 	boolean  True if should be updated, otherwise false.
	 */
	public static function shouldUpdate($version)
	{
		if (is_null($version))
		{
			return false;
		}

		return version_compare($version, VIKRENTCAR_SOFTWARE_VERSION, '<');
	}

	/**
	 * Executes the SQL file for the installation of the plugin.
	 *
	 * @return 	void
	 *
	 * @uses 	execSqlFile()
	 * @uses 	installAcl()
	 * @uses 	installProSettings()
	 * @uses 	installUploadBackup()
	 */
	public static function install()
	{
		self::execSqlFile(VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'install.mysql.utf8.sql');
		
		$dbo = JFactory::getDbo();

		// create the configuration record with the email address of the current user
		$q = "INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('adminemail', " . $dbo->q(JFactory::getUser()->email) . ");";
		$dbo->setQuery($q);
		$dbo->execute();

		// footer must be disabled by default
		$q = "UPDATE `#__vikrentcar_config` SET `setting`='0' WHERE `param`='showfooter';";
		$dbo->setQuery($q);
		$dbo->execute();

		// closing main text must not mention the name of the software
		$q = "UPDATE `#__vikrentcar_texts` SET `setting`='' WHERE `param`='closingmain';";
		$dbo->setQuery($q);
		$dbo->execute();

		self::installAcl();
		self::installProSettings();
		self::installUploadBackup();
	}

	/**
	 * Executes the SQL file for the uninstallation of the plugin.
	 *
	 * @param 	boolean  $drop 	True to drop the tables of VikRentCar from the database.
	 *
	 * @return 	void
	 *
	 * @uses 	execSqlFile()
	 * @uses 	uninstallAcl()
	 * @uses 	uninstallProSettings()
	 * @uses 	uninstallTemplateContents()
	 * @uses 	uninstallUploadBackup()
	 */
	public static function uninstall($drop = true)
	{
		if ($drop)
		{
			self::execSqlFile(VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'uninstall.mysql.utf8.sql');
		}
		
		self::uninstallAcl();
		self::uninstallProSettings();
		self::uninstallTemplateContents();
		self::uninstallUploadBackup();
	}

	/**
	 * Launches the process to finalise the update.
	 *
	 * @param 	string 	$version 	The current version.
	 *
	 * @uses 	getFixer()
	 * @uses 	installSql()
	 * @uses 	installAcl()
	 * @uses 	restoreTemplateFiles()
	 * @uses 	restoreUploadBackup()
	 */
	public static function update($version)
	{
		$fixer = self::getFixer($version);

		// trigger before installation routine

		$res = $fixer->beforeInstallation();

		if ($res === false)
		{
			return false;
		}

		// install SQL statements

		$res = self::installSql($version);

		if ($res === false)
		{
			return false;
		}

		// install ACL

		$res = self::installAcl();

		if ($res === false)
		{
			return false;
		}

		// move template files
		self::restoreTemplateFiles();

		// restore files uploaded
		self::restoreUploadBackup();

		// trigger after installation routine

		$res = $fixer->afterInstallation();

		return ($res === false ? false : true);
	}

	/**
	 * Reads from the Options if the content of a template file
	 * was previously modified. In this case, it restores the 
	 * modifications onto the template file which is always
	 * overwritten during updates and upgrades.
	 *
	 * @return 	void
	 * 
	 * @since 	1.0.3
	 */
	public static function restoreTemplateFiles()
	{
		jimport('joomla.filesystem.file');

		foreach (self::getTemplateFiles() as $file)
		{
			// the file base name as used in the Options
			$base_file = basename($file);
			$base_file = substr($base_file, 0, strrpos($base_file, '.'));

			// get override value from Options
			$tmp_val = get_option('vikrentcar_tmp_'.$base_file, null);
			if (!is_null($tmp_val))
			{
				JFile::write($file, $tmp_val);
			}
		}
	}

	/**
	 * Updates an Option with the content of the modified
	 * template file for later use after updates/upgrades.
	 * 
	 * @param 	string 	$path 		the full path to the template file
	 * @param 	string 	$content 	the raw content to put in the Option
	 *
	 * @return 	boolean
	 * 
	 * @since 	1.0.3
	 */
	public static function storeTemplateContent($path, $content)
	{
		$base_path = basename($path);

		foreach (self::getTemplateFiles() as $file)
		{
			// template file names are unique no matter of the directory
			$base_file = basename($file);
			if ($base_path == $base_file) {
				// template file found
				$base_file = substr($base_file, 0, strrpos($base_file, '.'));
				// update the Option and return
				update_option('vikrentcar_tmp_'.$base_file, $content);

				return true;
			}
		}

		return false;
	}

	/**
	 * Removes some Options that were used to hold
	 * modifications made to the template files.
	 * 
	 * @return  void
	 */
	public static function uninstallTemplateContents()
	{
		foreach (self::getTemplateFiles() as $file)
		{
			// the file base name as used in the Options
			$base_file = basename($file);
			$base_file = substr($base_file, 0, strrpos($base_file, '.'));

			// remove entry from Options
			delete_option('vikrentcar_tmp_'.$base_file);
		}
	}

	/**
	 * Returns a pair of key-values for each directory used
	 * for handling the backup of the uploaded files.
	 * All paths are missing the trailing directory separator.
	 * Gets the backup upload dir paths by default, or the
	 * real plugin dir paths if $real_path is true.
	 * Used during the installation, uninstallation and backup (post-update).
	 * 
	 * @param 	boolean 	[$real_paths] 	If True, the real paths used by the plugin will be returned.
	 * 										The path of the backup folders will be returned otherwise.
	 *
	 * @return 	array 		An array of strings with the path of each dir
	 */
	protected static function getUploadBackupDirs($real_paths = false)
	{
		if ($real_paths) {
			// return the real upload dir paths used by the plugin
			return array(
				// main upload path for the images of the cars, options, characteristics and such...
				'front' 	=> VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources',
				// upload dir path for the invoices in PDF format
				'invoices' 	=> VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR . 'generated',
				// upload dir path for the scans of the ID/Documents of the customers
				'idscans' 	=> VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'idscans',
				// dir path to extend the cron jobs
				'cronjobs' 	=> VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'cronjobs',
				// dir path to extend the reports
				'report' 	=> VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'report',
				// dir path to custom language files
				'languages' => VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . 'languages',
			);
		}

		// get the array information of the upload dir
		$upload_dir = wp_upload_dir();
		if (!is_array($upload_dir) || empty($upload_dir['basedir'])) {
			return false;
		}

		// keep the keys of this array, as they are used as a map with other methods
		return array(
			// base directory that will contain the sub-directories divided by category
			'base' 		=> $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar',
			// main upload path for the images of the rooms, options, characteristics and such...
			'front' 	=> $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar' . DIRECTORY_SEPARATOR . 'front',
			// upload dir path for the invoices in PDF format
			'invoices' 	=> $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar' . DIRECTORY_SEPARATOR . 'invoices',
			// upload dir path for the scans of the ID/Documents of the customers
			'idscans' 	=> $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar' . DIRECTORY_SEPARATOR . 'idscans',
			// dir path to extend the cron jobs
			'cronjobs' 	=> $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar' . DIRECTORY_SEPARATOR . 'cronjobs',
			// dir path to extend the reports
			'report' 	=> $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar' . DIRECTORY_SEPARATOR . 'report',
			// dir path to custom language files
			'languages' => $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar' . DIRECTORY_SEPARATOR . 'languages',
			// views overrides dir path: this dir should NOT be backed up, so this key should ONLY be in this array
			'overrides' => $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikrentcar' . DIRECTORY_SEPARATOR . 'overrides',
			// media dir path: this dir should NOT be backed up, so this key should ONLY be in this array
			'media' => VRC_MEDIA_PATH,
			// customer docs dir path: this dir should NOT be backed up, so this key should ONLY be in this array
			'customerdocs' => VRC_CUSTOMERS_PATH,
		);
	}

	/**
	 * Returns a list of keys for the upload back up directories
	 * only containing the keys for those dirs where the extendable
	 * PHP Classes are located by default in the plugin.
	 * Used during the installation, uninstallation and backup (post-update).
	 *
	 * @return 	array 		An array of strings with the keys of the paths of each dir supported
	 */
	protected static function getExtendableDirsKeys()
	{
		// return the real upload dir paths used by the plugin
		return array(
			// dir path to extend the cron jobs
			'cronjobs',
			// dir path to extend the reports
			'report',
		);
	}

	/**
	 * Creates a directory called 'vikrentcar' onto the defined upload
	 * directory for the website, for later use during the upload of files.
	 * All dirs are created by reading the instructions from getUploadBackupDirs().
	 * By default in /wp-content/uploads/vikrentcar
	 *
	 * @return 	boolean 	True on success.
	 * 
	 * @uses 	getUploadBackupDirs()
	 * 
	 * @since 	1.0.4
	 */
	protected static function installUploadBackup()
	{
		// import the Folder and File classes
		JLoader::import('adapter.filesystem.folder');
		JLoader::import('adapter.filesystem.file');

		// create all necessary folders (if they don't exist)
		$result = true;
		foreach (self::getUploadBackupDirs() as $type => $path) {
			$result = $result && JFolder::create($path);
			if ($result) {
				// create an empty HTML file to secure the directory from browsing
				JFile::write($path . DIRECTORY_SEPARATOR . 'index.html', '<html></html>');
			}
		}

		return $result;
	}

	/**
	 * Removes the directory 'vikrentcar' used for the file upload.
	 * The dirs to be removed are read from the array in getUploadBackupDirs().
	 * This should happen only upon uninstallation of the Plugin.
	 *
	 * @return 	boolean 	True on success.
	 * 
	 * @uses 	getUploadBackupDirs()
	 * 
	 * @since 	1.0.4
	 */
	protected static function uninstallUploadBackup()
	{
		// import the Folder class
		JLoader::import('adapter.filesystem.folder');

		// delete all folders no longer needed (if they exist)
		$result = true;
		foreach (self::getUploadBackupDirs() as $type => $path) {
			$result = JFolder::delete($path) && $result;
		}

		return $result;
	}

	/**
	 * Restores all the uploaded files from the directory 'vikrentcar'
	 * and its sub-dirs, onto the original dirs of the Plugin.
	 *
	 * @return 	boolean 	True on success.
	 * 
	 * @uses 	getUploadBackupDirs()
	 * 
	 * @since 	1.0.4
	 */
	protected static function restoreUploadBackup()
	{
		// get the list of the backup directories
		$backup_dirs = self::getUploadBackupDirs();

		// get the list of the real upload directories
		$real_dirs = self::getUploadBackupDirs(true);

		// import the Folder and File classes
		JLoader::import('adapter.filesystem.folder');
		JLoader::import('adapter.filesystem.file');

		$result = true;

		foreach ($backup_dirs as $type => $path) {
			// scan all files in this backup dir
			$files = JFolder::files($path);
			if (!isset($real_dirs[$type]) || !is_array($files) || !count($files)) {
				continue;
			}

			// restore all backed-up files onto its real dir
			foreach ($files as $file) {
				$fname = basename($file);
				// skip placeholder HTML file
				if ($fname == 'index.html') {
					continue;
				}
				// restore original file
				$result = JFile::copy($path . DIRECTORY_SEPARATOR . $file, $real_dirs[$type] . DIRECTORY_SEPARATOR . $fname) && $result;
			}
		}

		return $result;
	}

	/**
	 * Trigger called whenever a file has been uploaded.
	 * Checks if the destination path is recognized, and
	 * copies the uploaded/created file onto its backup folder.
	 * This is to have a mirror-file to be restored upon update.
	 * 
	 * @param 	string 		$dest 	the path to the just uploaded file.
	 *
	 * @return 	boolean 	True on success.
	 * 
	 * @uses 	getUploadBackupDirs()
	 * @uses 	installUploadBackup()
	 * 
	 * @since 	1.0.4
	 */
	public static function triggerUploadBackup($dest)
	{
		// seek the key of the uploaded dir
		$dir_key = false;
		foreach (self::getUploadBackupDirs(true) as $key => $path) {
			if (strpos($dest, $path) !== false) {
				$dir_key = $key;
				break;
			}
		}
		if (!$dir_key) {
			// could not recognize upload dir path from destination file
			return false;
		}

		// always make sure the upload backup dirs are set for bc with those that installed a previous version of the plugin
		if (!self::installUploadBackup()) {
			// cannot proceed because backup folders are not set
			return false;
		}

		// import the File class
		JLoader::import('adapter.filesystem.file');

		// get the backup upload dir path for this type of file
		$backup_dirs = self::getUploadBackupDirs();
		if (!isset($backup_dirs[$dir_key])) {
			// the arrays returned by getUploadBackupDirs do not match (should NEVER happen)
			return false;
		}

		// copy the file onto its backup dir
		return JFile::copy($dest, $backup_dirs[$dir_key] . DIRECTORY_SEPARATOR . basename($dest));
	}

	/**
	 * Trigger called to back up all the files inside the path with the key
	 * passed. Useful to back up custom PHP files manually uploaded to extend
	 * the default Reports, Cron Jobs, SMS APIs to save a clone copy of these
	 * files onto a directory that will be later used for restoring the files.
	 * This method should be called every time a back-end View with a list of
	 * specific extendable classes is visited, to always update the back ups.
	 *
	 * @param 	string 	$key 		The key of the extendable class.
	 * @param 	string 	$filter 	An optional regex to apply on the file name to skip.
	 *
	 * @return 	boolean 	True on success.
	 * 
	 * @uses 	getUploadBackupDirs()
	 * @uses 	installUploadBackup()
	 * 
	 * @since 	1.0.14
	 */
	public static function triggerExtendableClassesBackup($key, $filter = '')
	{
		// get the list of the original directories
		$orig_dirs = self::getUploadBackupDirs(true);

		// get the list of the backup directories
		$backup_dirs = self::getUploadBackupDirs();

		// make sure the key exists in the dirs arrays
		if (empty($key) || !isset($orig_dirs[$key]) || !isset($backup_dirs[$key])) {
			return false;
		}

		// always make sure the backup dirs are set for bc with those that installed a previous version of the plugin
		if (!self::installUploadBackup()) {
			// cannot proceed because backup folders are not set
			return false;
		}

		// import the Folder and File classes
		JLoader::import('adapter.filesystem.folder');
		JLoader::import('adapter.filesystem.file');

		// scan all files in the original dir
		$files = JFolder::files($orig_dirs[$key]);
		if (!is_array($files) || !count($files)) {
			return false;
		}

		$result = true;

		// back-up all files of this dir onto its real dir
		foreach ($files as $file) {
			$fname = basename($file);
			// skip placeholder HTML file
			if ($fname == 'index.html') {
				continue;
			}
			if (!empty($filter) && !preg_match($filter, $file)) {
				// we skip this file as the filter matched its name
				continue;
			}
			// clone original file
			$result = JFile::copy($orig_dirs[$key] . DIRECTORY_SEPARATOR . $file, $backup_dirs[$key] . DIRECTORY_SEPARATOR . $fname) && $result;
		}

		return $result;
	}

	/**
	 * Get the script class to run the installation methods.
	 *
	 * @param 	string 	$version 	The current version.
	 *
	 * @return 	VikRentCarUpdateFixer
	 */
	protected static function getFixer($version)
	{
		VikRentCarLoader::import('update.fixer');
	
		return new VikRentCarUpdateFixer($version);
	}

	/**
	 * Provides the installation of the ACL routines.
	 *
	 * @return 	boolean  True on success, otherwise false.	
	 */
	protected static function installAcl()
	{
		JLoader::import('adapter.acl.access');
		$actions = JAccess::getActions('vikrentcar');

		// No actions found!
		// Probably, the main folder is not called "vikrentcar".
		if (!$actions)
		{
			return false;
		}

		$roles = array(
			get_role('administrator'),
		);

		foreach ($roles as $role)
		{
			if ($role)
			{
				foreach ($actions as $action)
				{
					$cap = JAccess::adjustCapability($action->name, 'com_vikrentcar');
					$role->add_cap($cap, true);
				}
			}
		}

		return true;
	}

	/**
	 * Sets up the options for using the Pro version.
	 *
	 * @return 	void
	 */
	protected static function installProSettings()
	{
		VikRentCarChangelog::install();
		VikRentCarLicense::install();
	}

	/**
	 * Sets up the options for using the Pro version.
	 *
	 * @return 	void
	 */
	protected static function uninstallProSettings()
	{
		VikRentCarChangelog::uninstall();
		VikRentCarLicense::uninstall();
	}

	/**
	 * Provides the uninstallation of the ACL routines.
	 *
	 * @return 	boolean  True on success, otherwise false.	
	 */
	protected static function uninstallAcl()
	{
		JLoader::import('adapter.acl.access');
		$actions = JAccess::getActions('vikrentcar');

		// No actions found!
		// Probably, something went wrong while installing the plugin.
		if (!$actions)
		{
			return false;
		}

		$roles = array(
			get_role('administrator'),
		);

		foreach ($roles as $role)
		{
			if ($role)
			{
				foreach ($actions as $action)
				{
					$cap = JAccess::adjustCapability($action->name, 'com_vikrentcar');
					$role->remove_cap($cap);
				}
			}
		}

		return true;
	}

	/**
	 * Run all the proper SQL files.
	 *
	 * @param 	string 	 $version 	The current version.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 *
	 * @uses 	execSqlFile()
	 */
	protected static function installSql($version)
	{
		$dbo = JFactory::getDbo();

		$ok = true;

		$sql_base = VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'mysql' . DIRECTORY_SEPARATOR;

		try
		{
			foreach (glob($sql_base . '*.sql') as $file)
			{
				$name  = basename($file);
				$sql_v = substr($name, 0, strrpos($name, '.'));

				if (version_compare($sql_v, $version, '>'))
				{
					// in case the SQL version is newer, execute the queries listed in the file
					self::execSqlFile($file, $dbo);
				}
			}
		}
		catch (Exception $e)
		{
			$ok = false;
		}

		return $ok;
	}

	/**
	 * Executes all the queries contained in the specified file.
	 *
	 * @param 	string 		$file 	The SQL file to launch.
	 * @param 	JDatabase 	$dbo 	The database driver handler.
	 *
	 * @return 	void
	 */
	protected static function execSqlFile($file, $dbo = null)
	{
		if (!is_file($file))
		{
			return;
		}

		if ($dbo === null)
		{
			$dbo = JFactory::getDbo();
		}

		$handle = fopen($file, 'r');

		$bytes = '';
		while (!feof($handle))
		{
			$bytes .= fread($handle, 8192);
		}

		fclose($handle);

		foreach (JDatabaseHelper::splitSql($bytes) as $q)
		{
			$dbo->setQuery($q);
			$dbo->execute();
		}
	}
}
