<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.sms
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.sms.status');

/**
 * This class describes the events that an abstract sms driver should handle.
 * 
 * @since 10.1.30
 */
abstract class JSmsDriver
{
	/**
	 * The name of the plugin that has requested the sms.
	 *
	 * @var string
	 */
	protected $caller;

	/**
	 * The driver identifier.
	 *
	 * @var string
	 */
	protected $driver;

	/**
	 * The order details.
	 *
	 * @var JObject
	 */
	protected $order;

	/**
	 * The driver configuration parameters.
	 *
	 * @var JObject
	 */
	protected $params;

	/**
	 * Class constructor.
	 *
	 * @param 	string 	$caller  The name of the plugin that requested the sms.
	 * @param 	mixed 	$order 	 The order details to start the transaction.
	 * @param 	mixed 	$params  The configuration of the driver.
	 */
	public function __construct($caller, $order, $params = array())
	{
		if (is_string($params))
		{
			$params = (array) json_decode($params);
		}

		$this->caller 	= $caller;
		$this->order 	= new JObject($order);
		$this->params 	= new JObject($params);
	}

	/**
	 * Returns the name of the plugin that dispatched the sms.
	 *
	 * @return 	string
	 */
	public function getCaller()
	{
		return $this->caller;
	}

	/**
	 * Checks if the given caller matches the current one.
	 *
	 * @param 	string 	 $caller  The caller to check.
	 *
	 * @return 	boolean  True if the callers are equal, otherwise false.
	 *
	 * @uses 	getCaller()
	 */
	public function isCaller($caller)
	{
		return !strcasecmp($this->getCaller(), $caller);
	}

	/**
	 * Returns the name of the driver.
	 *
	 * @return 	string
	 */
	public function getDriver()
	{
		if (!$this->driver)
		{
			$class = get_class($this);

			// extract driver name from classname
			$this->driver = preg_replace_callback("/{$this->caller}Sms(.*?)/i", function($match)
			{
				return $match[1];
			}, $class);

			$this->driver = strtolower($this->driver);
		}

		return $this->driver;
	}

	/**
	 * Checks if the given driver matches the current one.
	 *
	 * @param 	string 	 $driver  The driver to check.
	 *
	 * @return 	boolean  True if the drivers are equal, otherwise false.
	 *
	 * @uses 	getDriver()
	 */
	public function isDriver($driver)
	{
		return !strcasecmp($this->getDriver(), $driver);
	}

	/**
	 * Returns the order detail related to the specified key.
	 *
	 * @param 	string 	$key 	The registry key.
	 * @param 	mixed 	$def 	The default value if the key is not set.
	 *
	 * @return 	mixed 	The registry value, or the default one.
	 */
	public function get($key, $def = null)
	{
		return $this->order->get($key, $def);
	}

	/**
	 * Updates the specified key into the order details object.
	 *
	 * @param 	string 	$key 	The registry key.
	 * @param 	mixed 	$val 	The value to update.
	 *
	 * @param 	mixed 	The old value.
	 */
	public function set($key, $val)
	{
		return $this->order->set($key, $val);
	}

	/**
	 * Returns an associative array containing the order details.
	 *
	 * @return 	array
	 */
	public function getOrder()
	{
		return $this->order->getProperties();
	}

	/**
	 * Returns the configuration parameter related to the specified key.
	 *
	 * @param 	string 	$key 	The registry key.
	 * @param 	mixed 	$def 	The default value if the key is not set.
	 *
	 * @return 	mixed 	The registry value, or the default one.
	 */
	public function getParam($key, $def = null)
	{
		return $this->params->get($key, $def);
	}

	/**
	 * Updates the specified key into the configuration object.
	 *
	 * @param 	string 	$key 	The registry key.
	 * @param 	mixed 	$val 	The value to update.
	 *
	 * @param 	mixed 	The old value.
	 */
	public function setParam($key, $val)
	{
		return $this->params->set($key, $val);
	}

	/**
	 * Returns an associative array containing the driver parameters.
	 *
	 * @return 	array
	 */
	public function getParams()
	{
		return $this->params->getProperties();
	}

	/**
	 * Returns an associative array containing the form
	 * fields used to construct the configuration of the driver.
	 *
	 * This method triggers two ACTIONS to manipulate the 
	 * configuration array before it is returned:
	 * - sms_driver_before_admin_params
	 * - sms_driver_after_admin_params
	 *
	 * @return 	array 	The sms driver configuration.
	 *
	 * @uses 	buildAdminParameters()
	 */
	public function getAdminParameters()
	{
		/**
		 * Plugins can manipulate the properties of this object.
		 * Fires before the configuration array is generated.
		 *
		 * @param 	self 	A reference to this object.
		 *
		 * @since 	10.1.30
		 */
		do_action('sms_driver_before_admin_params_' . $this->caller, array(&$this));

		// children drivers will create the configuration form (as array)
		$config = $this->buildAdminParameters();

		/**
		 * Plugins can manipulate the configuration form of the driver.
		 * Fires after generating the config form.
		 *
		 * @param 	self 	A reference to this object.
		 * @param 	array 	A reference to the configuration array.
		 *
		 * @since 	10.1.30
		 */
		do_action_ref_array('sms_driver_after_admin_params_' . $this->caller, array(&$this, &$config));

		return $config;
	}

	/**
	 * Abstract method used to build the associative array 
	 * to allow the plugins to construct a configuration form.
	 *
	 * In case the driver needs an API Key, the array should
	 * be built as follows:
	 *
	 * {"apikey": {"type": "text", "label": "API Key"}}
	 *
	 * @return 	array 	The associative array.
	 */
	protected function buildAdminParameters()
	{
		// return an empty array because a sms driver
		// may not need a configuration
		return array();
	}

	/**
	 * Dispatches the SMS to the specified phone number.
	 *
	 * This method triggers the ACTIONS below:
	 * - sms_driver_before_send
	 * - sms_driver_after_send
	 *
	 * @param 	string  $phone  The sms receiver.
	 * @param 	string  $text   The message to send.
	 *
	 * @return 	mixed 	An object describing the status of the sms.
	 *
	 * @uses 	dispatch()
	 */
	public function sendMessage($phone, $text)
	{
		/**
		 * Plugins can manipulate the properties of this object.
		 * Fires before sending the SMS.
		 *
		 * @param 	self 	A reference to this object.
		 * @param 	string  The sms receiver.
		 * @param 	string  The message to send.
		 *
		 * @since 	10.1.30
		 */
		do_action('sms_driver_before_send_' . $this->caller, array(&$this, &$phone, &$text));

		$status = new JSmsStatus();

		// sanitize phone number
		$phone = $this->sanitizePhone($phone);

		// children drivers will dispatch the message
		$this->dispatch($phone, $text, $status);

		// inject receiver and SMS within the status object
		$status->setData('phone', $phone);
		$status->setData('sms', $text);

		$response = null;

		/**
		 * Plugins can manipulate the response object to return.
		 * By filling the &$response variable, this method will return
		 * it instead of the default &$status one.
		 * Fires after dispatching the sms.
		 *
		 * @param 	self 		A reference to this object.
		 * @param 	JSmsStatus 	A reference to the status object.
		 * @param 	mixed 		A reference to the final response (null by default).
		 *
		 * @since 	10.1.30
		 */
		do_action_ref_array('sms_driver_after_send_' . $this->caller, array(&$this, &$status, &$response));

		if (is_null($response))
		{
			// no hook fired, the response will be equals to the status object
			$response = $status;
		}

		return $response;
	}

	/**
	 * Abstract method used to dispatch a SMS to the specified phone.
	 *
	 * @param 	string      $phone    The sms receiver.
	 * @param 	string      $text     The message to send.
	 * @param 	JSmsStatus  &$status  The status of the notification.
	 *
	 * @return 	void
	 */
	abstract protected function dispatch($phone, $text, JSmsStatus &$status);

	/**
	 * Checks whether the current driver is able to estimate the credit.
	 *
	 * @return 	boolean
	 */
	public function canEstimate()
	{
		// inherit in chidlren classes
		return false;
	}

	/**
	 * Tries to recover the remaining balance.
	 *
	 * This method triggers the ACTIONS below:
	 * - sms_driver_before_estimate
	 * - sms_driver_after_estimate
	 *
	 * @param 	string  $phone  An optional receiver.
	 * @param 	string  $text   An optional message.
	 *
	 * @return 	mixed 	The remaining balance on success, false otherwise.
	 *
	 * @uses 	estimate()
	 */
	public function getCredit($phone = null, $text = null)
	{
		/**
		 * Plugins can manipulate the properties of this object.
		 * Fires before estimating the remaining credit.
		 *
		 * @param 	self 	A reference to this object.
		 * @param 	string  The sms receiver.
		 * @param 	string  The message to send.
		 *
		 * @since 	10.1.30
		 */
		do_action('sms_driver_before_estimate_' . $this->caller, array(&$this, &$phone, &$text));

		// sanitize phone number
		$phone = $this->sanitizePhone($phone);

		try
		{
			// children drivers will estimate the credit
			$credit = $this->estimateCredit($phone, $text);
		}
		catch (Exception $e)
		{
			$credit = false;
		}

		/**
		 * Plugins can manipulate the resulting credit.
		 * Fires after estimating the user credit.
		 *
		 * @param 	self 	A reference to this object.
		 * @param 	mixed 	A reference to the user credit (null by default).
		 *
		 * @since 	10.1.30
		 */
		do_action_ref_array('sms_driver_after_estimate_' . $this->caller, array(&$this, &$credit));

		return $credit;
	}

	/**
	 * Tries to estimate the remaining user credit.
	 *
	 * @param 	string  $phone  An optional receiver.
	 * @param 	string  $text   An optional message.
	 *
	 * @return 	mixed 	The remaining balance on success, false otherwise.
	 *
	 * @throws 	Exception
	 */
	protected function estimateCredit($phone, $text)
	{
		// inherit in children classes to estimate credit
		throw new Exception('Not implemented', 501);
	}

	/**
	 * Validates the response.
	 * Implement for platform BC.
	 * 
	 * @param 	mixed 	$response
	 * 
	 * @return 	boolean
	 */
	public function validateResponse($response)
	{
		// reset log
		$this->log = '';

		if (!$response instanceof JSmsStatus)
		{
			// invalid argument
			return false;
		}

		// register log
		$this->log = $response->log;

		// check sms status
		return $response->isVerified();
	}

	/**
	 * Returns the latest logs.
	 *
	 * @return 	string
	 */
	public function getLog()
	{
		return isset($this->log) ? $this->log : '';
	}

	/**
	 * Helper method used to sanitize phone numbers.
	 *
	 * @param 	string 	$phone 	The phone number to sanitize.
	 *
	 * @return 	string 	The cleansed number.
	 */
	public function sanitizePhone($phone)
	{
		// trim unexpected characters
		$phone = preg_replace("/[^0-9+]/", '', $phone);

		// replace 00 dial code with +
		if (substr($phone, 0, 2) == '00')
		{
			$phone = '+' . substr($phone, 2);
		}

		return $phone;
	}

	/**
	 * Adds the dial code to the phone number.
	 *
	 * @param 	string  $prefix  The dial code to prepend.
	 * @param  	string 	$phone   The phone number.
	 *
	 * @return 	string  The resulting phone number.
	 */
	public function addDialCode($prefix, $phone)
	{
		// check if the phone number already owns a dial code
		if ($prefix && !preg_match("/^\+/", $phone))
		{
			// sanitize dial code
			$prefix = $this->sanitizePhone($prefix);

			// normalize dial code
			$prefix = '+' . ltrim($prefix, '+');

			// prepend dial code
			$phone = $prefix . $phone;
		}

		return $phone;
	}

	/**
	 * Check if the specified message contains UTF-8 characters.
	 *
	 * @param 	string 	 $message 	The string to check.
	 *
	 * @return 	boolean  True if unicode, otherwise false.
	 */
	public function isUnicode($message)
	{
		if (function_exists('iconv'))
		{
			$latin = @iconv('UTF-8', 'ISO-8859-1', $message);

			if (strcmp($latin, $message))
			{
				$arr = unpack('H*hex', @iconv('UTF-8', 'UCS-2BE', $message));
				//return strtoupper($arr['hex']);
				return true;
			}
		}

		return false;
	}
}
