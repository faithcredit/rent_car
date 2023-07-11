<?php
/**
 * @package     VikStripe
 * @subpackage  vikrestaurants
 * @author      Matteo Galletti - E4J s.r.l.
 * @copyright   Copyright (C) 2018 VikWP All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('stripe', VIKSTRIPE_DIR);

// Enhance the configuration array to include password and default values
add_action('payment_after_admin_params_vikrestaurants', function(&$payment, &$config)
{
	// make sure the driver is Stripe
	if (!$payment->isDriver('stripe'))
	{
		return;
	}

	// make the secret key input as a password
	$config['secretkey']['type'] = 'password';

	// make default currency as EUR
	$config['currency']['default'] = 'EUR: Euro';

}, 10, 2);

// Store the SESSION ID of the transaction for later use
add_action('payment_after_begin_transaction_vikrestaurants', function(&$payment, &$html)
{
	// make sure the driver is Stripe
	if (!$payment->isDriver('stripe'))
	{
		return;
	}

	// save the transaction session ID within a transient (should not work on a multisite, try using `set_site_transient`)
	set_transient('vikstripe_' . $payment->get('oid') . '_' . $payment->get('sid'), $payment->get('session_id'), 1440 * MINUTE_IN_SECONDS);

}, 10, 2);

// Retrieve the total amount and the session id from the static transaction file.
add_action('payment_before_validate_transaction_vikrestaurants', function($payment)
{
	// make sure the driver is Stripe
	if (!$payment->isDriver('stripe'))
	{
		return;
	}

	$transient = 'vikstripe_' . $payment->get('oid') . '_' . $payment->get('sid');
	$payment->set('is_transient', true);
	$payment->set('transient_name', $transient);

	// get session ID from transient (should not work on a multisite, try using `get_site_transient`)
	$session_id = get_transient($transient);

	// make sure the session ID was previously set
	if ($session_id)
	{
		// set session ID within the payment instance
		$payment->set('session_id', $session_id);

	}
	
});

/**
 * This class is used to collect payments in VikRestaurants plugin
 * by using the Stripe gateway.
 *
 * @since 1.0
 */
class VikRestaurantsStripePayment extends AbstractStripePayment
{
	/**
	 * @override
	 * Class constructor.
	 *
	 * @param 	string 	$alias 	 The name of the plugin that requested the payment.
	 * @param 	mixed 	$order 	 The order details to start the transaction.
	 * @param 	mixed 	$params  The configuration of the payment.
	 */
	public function __construct($alias, $order, $params = array())
	{
		parent::__construct($alias, $order, $params);

		if (!$this->get('custmail'))
		{
			$details = $this->get('details', array());
			$this->set('custmail', isset($details['purchaser_mail']) ? $details['purchaser_mail'] : '');
		}
	}

}
