<?php
/** 
 * @package   	VikRentCar
 * @subpackage 	core
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.models.form');

/**
 * VikRentCar plugin License model.
 * @wponly
 *
 * @since 	1.2.0
 * @see 	JModelForm
 */
class VikRentCarModelLicense extends JModelForm
{
	/**
	 * The base end-point URI.
	 *
	 * @var string
	 */
	protected $baseUri = 'https://vikwp.com/api/';

	/**
	 * Implements the request needed to validate
	 * the PRO license of the plugin.
	 *
	 * @param 	string  $key  The license key.
	 *
	 * @return 	mixed   The response if valid, false otherwise.
	 */
	public function validate($key)
	{
		// validate specified key
		if (!preg_match("/^[a-zA-Z0-9]{16,16}$/", $key))
		{
			// invalid key, register error
			$this->setError(new Exception(JText::_('VRCEMPTYLICKEY'), 400));

			return false;
		}

		// update license hash
		VikRentCarLoader::import('update.license');
		$hash = VikRentCarLicense::getHash();

		// validation end-point
		$url = $this->baseUri . '?task=licenses.validate';

		// init HTTP transport
		$http = new JHttp();

		// build post data
		$data = array(
			'key'         => $key,
			'application' => 'vrc',
			'version'     => VIKRENTCAR_SOFTWARE_VERSION,
			'domain'      => JUri::root(),
			'ip'          => $_SERVER['REMOTE_ADDR'],
			'hash'        => $hash,
		);

		// build request headers
		$headers = array(
			// disable the SSL peer verification
			'sslverify' => false,
		);

		/**
		 * Apply filters to manipulate the post data and the headers at runtime.
		 * Useful to support beta/development packages.
		 *
		 * @param  array  $data      The post data array.
		 * @param  array  &$headers  An associative array of HTTP directives.
		 * @param  string $action 	 The name of the action to manipulate.
		 *
		 * @since  1.2.2
		 */
		$data = apply_filters_ref_array('vikrentcar_license_before_post', array($data, &$headers, 'validate'));

		// make connection with VikWP server
		$response = $http->post($url, $data, $headers);

		if ($response->code != 200)
		{
			// register error returned by VikWP
			$this->setError(new Exception($response->body, $response->code));

			return false;
		}

		// try decoding JSON
		$body = json_decode($response->body);

		if (!$body || $body->status != 1)
		{
			// invalid response received, register error
			$this->setError(new Exception(sprintf('Invalid response: %s', $response->body), 500));

			return false;
		}

		// import necessary libraries
		VikRentCarLoader::import('update.changelog');
		VikRentCarLoader::import('update.license');

		// register values
		VikRentCarChangelog::store((isset($body->changelog) ? $body->changelog : ''));
		VikRentCarLicense::setKey($body->key);
		VikRentCarLicense::setExpirationDate(strtotime($body->expdate));

		// return response object
		return $body;
	}

	/**
	 * Implements the request needed to download
	 * the PRO version of the plugin.
	 *
	 * @param 	string   $key  The license key.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	public function download($key)
	{
		// validate specified key
		if (!preg_match("/^[a-zA-Z0-9]{16,16}$/", $key))
		{
			// invalid key, register error
			$this->setError(new Exception(JText::_('VRCEMPTYLICKEY'), 400));

			return false;
		}

		// update license hash
		VikRentCarLoader::import('update.license');
		$hash = VikRentCarLicense::getHash();

		JLoader::import('adapter.filesystem.folder');

		// get temporary dir
		$tmp = get_temp_dir();

		// clean temporary path
		$tmp = rtrim(JPath::clean($tmp), DIRECTORY_SEPARATOR);

		// make sure the folder exists
		if (!is_dir($tmp))
		{
			// missing temporary folder, register error
			$this->setError(new Exception(sprintf('Temporary folder [%s] does not exist', $tmp), 404));

			return false;
		}

		/**
		 * Make sure the temporary folder is not in conflict with the backup (uploads) folder.
		 * This could be changed from the file wp-config.php with the constant WP_TEMP_DIR.
		 * 
		 * @since 	1.2.4
		 */
		$upload_dir = wp_upload_dir();
		if (is_array($upload_dir) && !empty($upload_dir['basedir']))
		{
			$tmp_upload = rtrim(JPath::clean($upload_dir['basedir']), DIRECTORY_SEPARATOR);
			if ($tmp_upload == $tmp)
			{
				/**
				 * This would erase all backup folders of the plugin, the temporary dir must have been overridden.
				 * Attempt to create a sub-directory to be used as a unique and safe temporary directory.
				 */
				if (JFolder::exists($tmp . '/tmp') || JFolder::create($tmp . '/tmp'))
				{
					$tmp = $tmp . DIRECTORY_SEPARATOR . 'tmp';
				}
				else
				{
					// could not create the temporary and safe directory
					$this->setError(new Exception(sprintf('Temporary folder [%s] has been rewritten to be the same value as the upload directory. This is not allowed.', $tmp), 500));
					return false;
				}
			}
		}

		// make sure the temporary folder is writable
		if (!wp_is_writable($tmp))
		{
			// tmp folder not writable, register error
			$this->setError(new Exception(sprintf('Temporary folder [%s] is not writable', $tmp), 403));

			return false;
		}

		// download end-point
		$url = $this->baseUri . '?task=licenses.download';

		// init HTTP transport
		$http = new JHttp();

		// build request headers
		$headers = array(
			// turn on stream to push body within a file
			'stream'    => true,
			// define the filepath in which the data will be pushed
			'filename'  => $tmp . DIRECTORY_SEPARATOR . 'vikrentcarpro.zip',
			// make sure the request is non blocking
			'blocking'  => true,
			// force timeout to 60 seconds
			'timeout'   => 60,
			// disable the SSL peer verification
			'sslverify' => false,
		);

		// build post data
		$data = array(
			'key'         => $key,
			'application' => 'vrc',
			'version'     => VIKRENTCAR_SOFTWARE_VERSION,
			'domain'      => JUri::root(),
			'ip'          => $_SERVER['REMOTE_ADDR'],
			'hash'        => $hash,
		);

		/**
		 * Apply filters to manipulate the post data and the headers at runtime.
		 * Useful to support beta/development packages.
		 *
		 * @param  array  $data      The post data array.
		 * @param  array  &$headers  An associative array of HTTP directives.
		 * @param  string $action 	 The name of the action to manipulate.
		 *
		 * @since  1.2.2
		 */
		$data = apply_filters_ref_array('vikrentcar_license_before_post', array($data, &$headers, 'download'));

		// make connection to the VikWP servers
		$response = $http->post($url, $data, $headers);

		if ($response->code != 200)
		{
			// register error returned by VikWP
			$this->setError(new Exception($response->body, $response->code));

			return false;
		}

		// make sure the file has been saved
		if (!JFile::exists($headers['filename']))
		{
			// something went wrong while saving the archive, register error
			$this->setError(new Exception('ZIP package could not be saved on disk', 404));

			return false;
		}

		// create destination folder for extracted elements
		$dest = $tmp . DIRECTORY_SEPARATOR . 'vikrentcar';

		// make sure the destination folder doesn't exist
		if (JFolder::exists($dest))
		{
			// remove it before proceeding with the extraction
			JFolder::delete($dest);
		}

		// import archive class handler
		JLoader::import('adapter.filesystem.archive');

		// the package was downloaded successfully, let's extract it (onto TMP folder)
		$extracted = JArchive::extract($headers['filename'], $tmp);

		// we no longer need the archive
		JFile::delete($headers['filename']);

		if (!$extracted)
		{
			// an error occurred while extracting the files, register it
			$this->setError(new Exception(sprintf('Cannot extract files to [%s]', $tmp), 500));

			return false;
		}

		// make sure the folder is intact
		if (!JFolder::exists($dest))
		{
			// impossible to access the extracted elements, register error
			$this->setError(new Exception(sprintf('Cannot access extracted elements from [%s] folder', $dest), 404));

			return false;
		}
		
		// copy the root files
		$root_files = JFolder::files($dest, '.', false, true);

		foreach ($root_files as $file)
		{
			if (!JFile::copy($file, VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . basename($file)))
			{
				// delete folder before exiting
				JFolder::delete($dest);

				// we cannot afford to not be able to copy a root file, register error
				$this->setError(new Exception(sprintf('Cannot copy root [%s] file', basename($file)), 500));

				return false;
			}
		}

		// copy the root folders
		$root_folders = JFolder::folders($dest, '.', false, true);

		foreach ($root_folders as $folder)
		{
			if (!JFolder::copy($folder, VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . basename($folder), '', true))
			{
				// delete folder before exiting
				JFolder::delete($dest);

				// we cannot afford to not be able to copy a root folder, register error
				$this->setError(new Exception(sprintf('Cannot copy root [%s] folder', basename($folder)), 500));

				return false;
			}
		}

		// process complete, clean up the temporary folder before exiting
		JFolder::delete($dest);

		// restore template files that could have been overwritten by the Pro package
		VikRentCarLoader::import('update.manager');
		VikRentCarUpdateManager::restoreTemplateFiles();

		return true;
	}
}
