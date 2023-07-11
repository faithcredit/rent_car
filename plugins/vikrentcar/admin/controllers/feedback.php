<?php
/** 
 * @package   	VikRentCar
 * @subpackage 	core
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2019 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

/**
 * VikRentCar plugin Feedback controller.
 *
 * @since 	1.0
 * @see 	JControllerAdmin
 */
class VikRentCarControllerFeedback extends JControllerAdmin
{
	/**
	 * Submits a feedback to VikWP servers after deactivating the plugin.
	 *
	 * @return 	void
	 */
	public function submit()
	{
		if (!JFactory::getUser()->authorise('core.admin', 'com_vikrentcar'))
		{
			// not authorised to view this resource
			throw new Exception(JText::_('RESOURCE_AUTH_ERROR'), 403);
		}
		
		$input = JFactory::getApplication()->input;

		// validation end-points
		$url = 'https://vikwp.com/api/?task=logs.track';

		$version = new JVersion();

		$env = array(
			'ipaddr'  => $input->server->getString('REMOTE_ADDR'),
			'wpver'   => $version->getLongVersion(),
			'version' => VIKRENTCAR_SOFTWARE_VERSION,
			'phpver'  => phpversion(),
		);

		$body = print_r($env, true);

		$notes = $input->getString('notes');

		$email = $input->getString('email');

		if ($notes)
		{
			$body = $notes . "\n\n" . $body;
		}

		if (!empty($email) && strpos($email, '@') !== false)
		{
			$body = $email . "\n\n" . $body;
		}

		// init HTTP transport
		$http = new JHttp();

		// build post data
		$data = array(
			'type'  => 'feedback.vikrentcar',
			'desc'  => $input->getString('type'),
			'body'  => $body,
			'email' => $email,
		);

		// make connection with VikWP server
		$response = $http->post($url, $data, array('sslverify' => false));

		if ($response->code != 200)
		{
			// raise error returned by VikWP
			throw new Exception($response->body, $response->code);
		}
		
		echo $response->body;
	}
}
