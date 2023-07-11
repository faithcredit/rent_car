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
 * Helper class used to handle the interaction with HTTP documents.
 * 
 * @since 1.3
 */
class VRCHttpDocument
{
	/**
	 * A reference to the application object.
	 * 
	 * @var JApplication
	 */
	protected $app;

	/**
	 * Proxy used to construct the object.
	 * 
	 * @param 	mixed  $app  The application instance. If not specified the
	 *                       current one will be used.
	 * 
	 * @return 	self   A new instance of this class.
	 */
	public static function getInstance($app = null)
	{
		if (!$app)
		{
			$app = JFactory::getApplication();
		}

		return new static($app);
	}

	/**
	 * Class constructor.
	 * 
	 * @param 	JApplication  $app  The application instance.
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * Method to close the application.
	 *
	 * @param 	integer  $code    The HTTP status code.
	 * @param   mixed    $buffer  An optional string to display.
	 *
	 * @return  void
	 */
	public function close($code = 200, $buffer = null)
	{
		// force HTTP status code
		$this->app->setHeader('status', $code, $replace = true);
		$this->app->sendHeaders();

		if ($buffer)
		{
			// display buffer
			echo $buffer;
		}

		// terminate session
		$this->app->close();
	}

	/**
	 * Echoes the given JSON by using the right content type.
	 *
	 * @param 	mixed  $json  Either a JSON string or a non-scalar value.
	 *
	 * @return 	void
	 */
	public function json($json)
	{
		$this->app->setHeader('Content-Type', 'application/json', $replace = true);

		if (!is_string($json))
		{
			// stringify array/object
			$json = json_encode($json);
		}

		// terminate session by echoing the given JSON
		$this->close(200, $json);
	}
}
