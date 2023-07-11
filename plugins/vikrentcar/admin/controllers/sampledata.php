<?php
/** 
 * @package   	VikRentCar
 * @subpackage 	core
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

/**
 * VikRentCar Sampledata controller.
 *
 * @since 	1.2.0
 * @see 	JControllerAdmin
 */
class VikRentCarControllerSampledata extends JControllerAdmin
{
	/**
	 * AJAX endpoint to load the sample data available.
	 * JSON encoded string outputted upon result.
	 *
	 * @return 	void
	 */
	public function load()
	{
		$data = $this->getSampleData();
		
		echo json_encode($data);
		exit;
	}

	/**
	 * AJAX endpoint to install one sample data ID.
	 * JSON encoded result outputted or exception thrown.
	 *
	 * @return 	void
	 * 
	 * @throws 	Exception
	 */
	public function install()
	{
		$sample_data_id = VikRequest::getInt('sample_data_id', 0, 'request');
		if (empty($sample_data_id)) {
			throw new Exception('Empty Sample Data ID', 500);
		}

		// load all sample data available
		$data = $this->getSampleData();

		$sample_data_obj = null;

		foreach ($data as $sdata) {
			if (!is_object($sdata) || empty($sdata->id)) {
				continue;
			}
			if ((int)$sdata->id == $sample_data_id) {
				$sample_data_obj = new JRegistry($sdata);
				break;
			}
		}

		if (!$sample_data_obj) {
			throw new Exception(sprintf('Sample Data ID [%s] not found', $sample_data_id), 404);
		}

		// response object
		$response = new stdClass;
		$response->status = 0;
		$response->error  = '';
		
		// download and install selected sample data
		try {
			$res = $this->downloadSampleData($sample_data_obj);
			if ($res) {
				$response->status = 1;
			}
		} catch (Exception $e) {
			$response->error = $e->getMessage();
		}

		if ($response->status) {
			// silently add shortcodes to new WordPress pages
			$this->addShortcodesToPages($sample_data_obj);
		}
		
		echo json_encode($response);
		exit;
	}

	/**
	 * Returns a list of supported sample data.
	 *
	 * @return 	array  A list of sample data.
	 */
	private function getSampleData()
	{
		// build transient key
		$transient = 'vikrentcar_sampledata_' . md5(VIKRENTCAR_SOFTWARE_VERSION);

		// get cached sample data list
		$data = get_transient($transient);

		if ($data) {
			// return cached transient
			$data = json_decode($data);
			
			return is_array($data) ? $data : array();
		}

		// default empty list
		$data = array();

		// instantiate HTTP transport
		$http = new JHttp();

		// build end-point URI
		$uri = 'https://vikwp.com/api/?task=sampledata.list';

		// build post data
		$post = array(
			'sku'     => 'vrc',
			'version' => VIKRENTCAR_SOFTWARE_VERSION,
		);

		// load sample data from server
		$response = $http->post($uri, $post);

		if ($response->code == 200) {
			// decode response
			$data = json_decode($response->body);

			// cache sample data list for an hour
			set_transient($transient, json_encode($data), HOUR_IN_SECONDS);
		}

		return $data;
	}

	/**
	 * Downloads and installs one sample data.
	 *
	 * @param 	JRegistry  $data  The sample data registry object.
	 *
	 * @return 	boolean
	 * 
	 * @uses 	installSampleData()
	 */
	private function downloadSampleData($data)
	{
		// instantiate HTTP transport
		$http = new JHttp();

		// get selected sample data
		$id = $data->get('id');

		JLoader::import('adapter.filesystem.folder');

		// get temporary dir
		$tmp = get_temp_dir();

		// clean temporary path
		$tmp = rtrim(JPath::clean($tmp), DIRECTORY_SEPARATOR);

		// make sure the folder exists
		if (!is_dir($tmp)) {
			throw new Exception(sprintf('Temporary folder [%s] does not exist', $tmp), 404);
		}

		// make sure the temporary folder is writable
		if (!wp_is_writable($tmp)) {
			throw new Exception(sprintf('Temporary folder [%s] is not writable', $tmp), 403);
		}

		// download end-point
		$url = 'https://vikwp.com/api/?task=sampledata.download';

		// init HTTP transport
		$http = new JHttp();

		// build sample data file name
		$packname = 'sampledata-' . uniqid();

		// build request headers
		$headers = array(
			// turn on stream to push body within a file
			'stream'   => true,
			// define the filepath in which the data will be pushed
			'filename' => $tmp . DIRECTORY_SEPARATOR . $packname . '.zip',
			// make sure the request is non blocking
			'blocking' => true,
			// force timeout to 60 seconds
			'timeout'  => 60,
		);

		// build post data
		$body = array(
			'id' => $id,
		);

		// make connection with VikWP server
		$response = $http->post($url, $body, $headers);

		if ($response->code != 200) {
			// raise error returned by VikWP
			throw new Exception($response->body, $response->code);
		}

		// make sure the file has been saved
		if (!JFile::exists($headers['filename'])) {
			throw new Exception('ZIP package could not be saved on disk', 404);
		}

		// create destination folder for extracted elements
		$dest = $tmp . DIRECTORY_SEPARATOR . $packname;

		// make sure the destination folder doesn't exist
		if (JFolder::exists($dest)) {
			// remove it before proceeding with the extraction
			JFolder::delete($dest);
		}

		// import archive class handler
		JLoader::import('adapter.filesystem.archive');

		// the package was downloaded successfully, let's extract it (onto TMP folder)
		$extracted = JArchive::extract($headers['filename'], $dest);

		// we no longer need the archive
		JFile::delete($headers['filename']);

		if (!$extracted) {
			// an error occurred while extracting the files
			throw new Exception(sprintf('Cannot extract files to [%s]', $tmp), 500);
		}

		// make sure the folder is intact
		if (!JFolder::exists($dest)) {
			// impossible to access the extracted elements
			throw new Exception(sprintf('Cannot access extracted elements from [%s] folder', $dest), 404);
		}

		$error = null;

		try {
			// run sample data installation
			$this->installSampleData($dest);
		} catch (Exception $e) {
			// safely catch error to finalize process
			$error = $e;
		}

		// process complete, clean up the temporary folder before exiting
		JFolder::delete($dest);

		// in case of error, propagate it after deleting the extracted folder
		if ($error) {
			throw $error;
		}

		return true;
	}

	/**
	 * Interprets the manifest contained within the folder
	 * to install the sample data.
	 *
	 * @param 	string 	$folder  The sample data folder.
	 *
	 * @return 	void
	 *
	 * @throws 	Exception
	 * 
	 * @uses 	uninstallShortcodes()
	 * @uses 	installSqlRole()
	 * @uses 	installFilesRole()
	 */
	private function installSampleData($folder)
	{
		// load manifest.json file
		if (!is_file($folder . DIRECTORY_SEPARATOR . 'manifest.json')) {
			// missing JSON manifest
			throw new Exception('Manifest file not found', 404);
		}

		// open manifest file in read mode
		$manifestFile = fopen($folder . DIRECTORY_SEPARATOR . 'manifest.json', 'r');

		$manifest = '';

		// load manifest content
		while (!feof($manifestFile)) {
			$manifest .= fread($manifestFile, 8192);
		}

		// close file
		fclose($manifestFile);

		// decode JSON
		$manifest = json_decode($manifest);

		if (!$manifest) {
			// unable to JSON decode manifest
			throw new Exception('Manifest file contains an invalid JSON', 500);
		}

		$dbo = JFactory::getDbo();

		// look for uninstall queries
		if (isset($manifest->uninstall)) {
			// iterate queries to clean any existing records
			foreach ($manifest->uninstall as $q) {
				try {
					// check if we are uninstalling the shortcodes
					if (preg_match("/vikrentcar_wpshortcodes/i", $q)) {
						// uninstall the existing shortcodes
						$this->uninstallShortcodes();
					}

					// launch query
					$dbo->setQuery($q);
					$dbo->execute();
				} catch (Exception $e) {
					// malformed query, suppress error and go ahead
					if (VIKRENTCAR_DEBUG) {
						// propagate error in case of debug mode enabled
						throw $e;
					}
				}
			}
		}

		// look for installers
		if (isset($manifest->installers)) {
			// iterate installers
			foreach ($manifest->installers as $install) {
				try {
					// switch case role to invoke the proper installation method
					switch ($install->role) {
						case 'sql':
						case 'insert':
							$this->installSqlRole($install->data);
							break;

						case 'media':
						case 'folder':
							$this->installFilesRole($install->data->destination, $install->data->files, $folder);
							break;
					}
				} catch (Exception $e) {
					// malformed role, suppress error and go ahead
					if (VIKRENTCAR_DEBUG) {
						// propagate error in case of debug mode enabled
						throw $e;
					}
				}
			}
		}
	}

	/**
	 * Executes the specified queries.
	 *
	 * @param 	mixed 	$queries  Either a query string or an array.
	 *
	 * @return 	void
	 */
	private function installSqlRole($queries)
	{
		if (!is_array($queries)) {
			$queries = array($queries);
		}

		$dbo = JFactory::getDbo();

		// iterate queries one by one
		foreach ($queries as $q) {
			$dbo->setQuery($q);
			$dbo->execute();
		}
	}

	/**
	 * Moves the files into the related folders.
	 *
	 * @param 	string 	$dest   The destination folder.
	 * @param 	mixed 	$files  Either a file or an array.
	 * @param 	string 	$dir    The current directory.
	 *
	 * @return 	void
	 */
	private function installFilesRole($dest, $files, $dir)
	{
		// load update manager class for backup/mirroring of files
		VikRentCarLoader::import('update.manager');

		// set destination by prepending plugin base path
		$dest = VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dest);

		// make sure to always parse an array
		if (!is_array($files)) {
			$files = array($files);
		}

		// copy files and create a backup copy from each file
		foreach ($files as $file) {
			// fetch file path
			$path = JPath::clean($dir . DIRECTORY_SEPARATOR . $file);

			// fetch destination file path
			$destFile = JPath::clean($dest . DIRECTORY_SEPARATOR . basename($file));

			// copy file in its destination
			$res = JFile::copy($path, $destFile);

			if ($res) {
				// trigger mirroring action for new file uploaded
				VikRentCarUpdateManager::triggerUploadBackup($destFile);
			}
		}
	}

	/**
	 * Uninstalls all the pages that have been assigned
	 * to the existing shortcodes.
	 *
	 * @return 	void
	 */
	private function uninstallShortcodes()
	{
		// get shortcode admin model
		$model = JModel::getInstance('vikrentcar', 'shortcodes');

		// get all existing shortcodes
		$shortcodes = $model->all(array('createdon', 'post_id'));

		// iterate all shortcodes found
		foreach ($shortcodes as $shortcode) {
			// make sure the shortcode has been assigned to a post
			if ($shortcode->post_id) {
				// get post details
				$post = get_post((int) $shortcode->post_id);

				// convert shortcode creation date
				$shortcode->createdon = new JDate($shortcode->createdon);
				// convert post creation date
				$post->post_date_gmt = new JDate($post->post_date_gmt);

				// compare ephocs and make sure the post was not created before the shortcode
				if ((int) $shortcode->createdon->format('U') <= (int) $post->post_date_gmt->format('U')) {
					// permanently delete post
					wp_delete_post($post->ID, $force_delete = true);
				}
			}
		}
	}


	/**
	 * Finalizes the installation of a sample data by creating one
	 * new WordPress page/post for each Shortcode installed.
	 *
	 * @param 	JRegistry  $data  The (optional) sample data chosen registry object.
	 *
	 * @return 	boolean
	 */
	private function addShortcodesToPages($data = null)
	{
		$model = JModel::getInstance('vikrentcar', 'shortcodes');

		$shortcodes = $model->all();

		if (!is_array($shortcodes) || !count($shortcodes)) {
			return false;
		}

		foreach ($shortcodes as $item) {
			if (!empty($item->post_id)) {
				// shortcode already linked to a page
				continue;
			}

			/**
			 * Add a new page (we allow a WP_ERROR to be returned in case of failure).
			 * This should automatically trigger the hook that we use to link the Shortcode 
			 * to the new page/post ID, and so there's no need to update the item.
			 */
			$new_page_id = wp_insert_post(array(
				'post_title' => (!empty($item->name) ? $item->name : JText::_($item->title)),
				'post_content' => $item->shortcode,
				'post_status' => 'publish',
				'post_type' => 'page',
			), true);

			// we ignore if the page was created or if an error occurred
		}

		return true;
	}

}
