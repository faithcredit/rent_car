<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.payment
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.payment.payment');

/**
 * Abstract factory used to instantiate different payment gateways.
 * The example below describes how to hook the payments to this dispatcher.
 *
 * add_action('load_payment_gateway_myplugin', function(&$drivers, $payment)
 * {
 *		if ($payment == 'paypal')
 * 		{
 *			JLoader::import('admin.payments.paypal', MYPLUGIN_BASE);
 *			$drivers[] = 'MyPluginPayPalPayment';
 *		}
 * }, 10, 2);
 * 
 * It is mandatory to indicate also the number of accepted arguments.
 *
 * @since 10.1
 */
class JPaymentDispatcher
{
	/**
	 * A list containing all the payment instances.
	 *
	 * @var JPayment[]
	 */
	protected static $instances = array();

	/**
	 * Provides a new instance for the specified arguments.
	 *
	 * @param 	string 	  $plugin 	The name of the plugin that requested the payment.
	 * @param 	string 	  $payment 	The name of the payment that should be instantiated.
	 * @param 	mixed 	  $order 	The details of the order that has to be paid.
	 * @param 	mixed 	  $config 	The payment configuration array or a JSON string.
	 *
	 * @return 	JPayment  The payment found.
	 *
	 * @throws 	RuntimeException 	In case the requested payment doesn't exist.
	 */
	public static function getInstance($plugin, $payment, $order = array(), $config = array())
	{
		if (substr($payment, -4) == '.php')
		{
			// make sure the payment doesn't contain PHP extension
			$payment = substr($payment, 0, -4);
		}

		// create unique identifier
		$sign = $plugin . '.' . $payment;

		// check if the payment was already instantiated
		if (!isset(static::$instances[$sign]))
		{
			$classname = null;
			$drivers   = array();

			/**
			 * Trigger action to obtain a list of classnames of the payment gateway.
			 * The action should autoload the file that contains the classname.
			 * In case the payment should be loaded, the classname MUST be
			 * pushed within the &$drivers array.
			 * Fires before the instantiation of the returned classname.
			 *
			 * @param 	array 	A reference to the list of available drivers.
			 * @param	string	The name of the gateway to load.
			 *
			 * @since 	10.1.1
			 */
			do_action_ref_array('load_payment_gateway_' . $plugin, array(&$drivers, $payment));

			// use the last driver in the list
			$classname = array_pop($drivers);

			if (!$classname || !class_exists($classname))
			{
				// payment not found, raise an exception
				throw new RuntimeException('The payment [' . $payment . '] for [' . $plugin . '] does not exist.', 404);
			}

			// instantiate the payment
			$payment = new $classname($plugin, $order, $config);

			if (!$payment instanceof JPayment)
			{
				// the class is not an instance of JPayment, raise an exception
				throw new RuntimeException('The payment [' . $classname . '] is not a valid instance.', 500);
			}

			// cache the payment
			static::$instances[$sign] = $payment;
		}

		return static::$instances[$sign];
	}

	/**
	 * Returns a list of all the drivers supported by the specified plugin. 
	 * The payments will be returned in ascending order.
	 *
	 * @param 	string 	 $plugin  The name of the plugin.
	 *
	 * @return 	array 	 A list of drivers.
	 *
	 * @since 	10.1.35  
	 */
	public static function getSupportedDrivers($plugin)
	{
		// init drivers array
		$drivers = array();

		/**
		 * Hook used to filter the list of all the supported drivers.
		 * Every plugin attached to this filter will be able to push one
		 * or more gateways within the $drivers array.
		 *
		 * @param 	array 	An array containing the list of the supported payments.
		 *
		 * @since 	10.1
		 */
		$drivers = apply_filters('get_supported_payments_' . $plugin, $drivers);

		// remove duplicated records
		$drivers = array_values(array_unique(array_filter($drivers)));

		// get rid of the file path and file extension
		$drivers = array_map(function($driver)
		{
			return basename($driver, '.php');
		}, $drivers);

		// sort by ascending driver name
		sort($drivers);

		return $drivers;
	}
}
