<?php
/**
 * @package     VikStripe
 * @subpackage  Stripe
 * @author      Lorenzo - E4J s.r.l.
 * @copyright   Copyright (C) 2019 VikWP All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.payment.payment');
if (!class_exists('Stripe\Stripe')) {
	JLoader::import('Stripe.Stripe', VIKSTRIPE_DIR);	
}

/**
 * This class is used to collect payments through the Stripe gateway.
 *
 * @since 1.0
 */
abstract class AbstractStripePayment extends JPayment
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
		$this->order_info = $order;
		
		$this->setParam('currency', strtolower(substr($this->getParam('currency'), 0, 3)));
		$this->setParam('ssl', $this->getParam('ssl') == __('Yes', 'vikstripe') ? 1 : 0);
	}

	/**
	 * @override
	 * Method used to build the associative array 
	 * to allow the plugins to construct a configuration form.
	 *
	 * In case the payment needs an API Key, the array should
	 * be built as follows:
	 *
	 * {"apikey": {"type": "text", "label": "API Key"}}
	 *
	 * @return 	array 	The associative array.
	 */
	protected function buildAdminParameters()
	{
		return array(
			'logo' => array(
				'label' => '',
				'type'  => 'custom',
				'html'  => '<img src="' . VIKSTRIPE_URI . 'Stripe/stripe_logo.jpg" style="margin-bottom: 15px;"/>',
			),
			'secretkey' => array(
				'label' => __('Secret Key', 'vikstripe'),
				'type'  => 'text',
			),
			'pubkey'    => array(
				'label' => __('Publishable Key', 'vikstripe'),
				'type'  => 'text',
			),
			'currency'  => array(
				'label' => __('Currency', 'vikstripe'),
				'type'  => 'select',
				'options' => array(
							'AED: United Arab Emirates Dirham',
							'AFN: Afghan Afghani',
							'ALL: Albanian Lek',
							'AMD: Armenian Dram',
							'ANG: Netherlands Antillean Gulden',
							'AOA: Angolan Kwanza',
							'ARS: Argentine Peso',
							'AUD: Australian Dollar',
							'AWG: Aruban Florin',
							'AZN: Azerbaijani Manat',
							'BAM: Bosnia & Herzegovina Convertible Mark',
							'BBD: Barbadian Dollar',
							'BDT: Bangladeshi Taka',
							'BGN: Bulgarian Lev',
							'BIF: Burundian Franc',
							'BMD: Bermudian Dollar',
							'BND: Brunei Dollar',
							'BOB: Bolivian Boliviano',
							'BRL: Brazilian Real',
							'BSD: Bahamian Dollar',
							'BWP: Botswana Pula',
							'BZD: Belize Dollar',
							'CAD: Canadian Dollar',
							'CDF: Congolese Franc',
							'CHF: Swiss Franc',
							'CLP: Chilean Peso',
							'CNY: Chinese Renminbi Yuan',
							'COP: Colombian Peso',
							'CRC: Costa Rican Colón',
							'CVE: Cape Verdean Escudo',
							'CZK: Czech Koruna',
							'DJF: Djiboutian Franc',
							'DKK: Danish Krone',
							'DOP: Dominican Peso',
							'DZD: Algerian Dinar',
							'EEK: Estonian Kroon',
							'EGP: Egyptian Pound',
							'ETB: Ethiopian Birr',
							'EUR: Euro',
							'FJD: Fijian Dollar',
							'FKP: Falkland Islands Pound',
							'GBP: British Pound',
							'GEL: Georgian Lari',
							'GIP: Gibraltar Pound',
							'GMD: Gambian Dalasi',
							'GNF: Guinean Franc',
							'GTQ: Guatemalan Quetzal',
							'GYD: Guyanese Dollar',
							'HKD: Hong Kong Dollar',
							'HNL: Honduran Lempira',
							'HRK: Croatian Kuna',
							'HTG: Haitian Gourde',
							'HUF: Hungarian Forint',
							'IDR: Indonesian Rupiah',
							'ILS: Israeli New Sheqel',
							'INR: Indian Rupee',
							'ISK: Icelandic Króna',
							'JMD: Jamaican Dollar',
							'JPY: Japanese Yen',
							'KES: Kenyan Shilling',
							'KGS: Kyrgyzstani Som',
							'KHR: Cambodian Riel',
							'KMF: Comorian Franc',
							'KRW: South Korean Won',
							'KYD: Cayman Islands Dollar',
							'KZT: Kazakhstani Tenge',
							'LAK: Lao Kip',
							'LBP: Lebanese Pound',
							'LKR: Sri Lankan Rupee',
							'LRD: Liberian Dollar',
							'LSL: Lesotho Loti',
							'LTL: Lithuanian Litas',
							'LVL: Latvian Lats',
							'MAD: Moroccan Dirham',
							'MDL: Moldovan Leu',
							'MGA: Malagasy Ariary',
							'MKD: Macedonian Denar',
							'MNT: Mongolian Tögrög',
							'MOP: Macanese Pataca',
							'MRO: Mauritanian Ouguiya',
							'MUR: Mauritian Rupee',
							'MVR: Maldivian Rufiyaa',
							'MWK: Malawian Kwacha',
							'MXN: Mexican Peso',
							'MYR: Malaysian Ringgit',
							'MZN: Mozambican Metical',
							'NAD: Namibian Dollar',
							'NGN: Nigerian Naira',
							'NIO: Nicaraguan Córdoba',
							'NOK: Norwegian Krone',
							'NPR: Nepalese Rupee',
							'NZD: New Zealand Dollar',
							'PAB: Panamanian Balboa',
							'PEN: Peruvian Nuevo Sol',
							'PGK: Papua New Guinean Kina',
							'PHP: Philippine Peso',
							'PKR: Pakistani Rupee',
							'PLN: Polish Złoty',
							'PYG: Paraguayan Guaraní',
							'QAR: Qatari Riyal',
							'RON: Romanian Leu',
							'RSD: Serbian Dinar',
							'RUB: Russian Ruble',
							'RWF: Rwandan Franc',
							'SAR: Saudi Riyal',
							'SBD: Solomon Islands Dollar',
							'SCR: Seychellois Rupee',
							'SEK: Swedish Krona',
							'SGD: Singapore Dollar',
							'SHP: Saint Helenian Pound',
							'SLL: Sierra Leonean Leone',
							'SOS: Somali Shilling',
							'SRD: Surinamese Dollar',
							'STD: São Tomé and Príncipe Dobra',
							'SVC: Salvadoran Colón',
							'SZL: Swazi Lilangeni',
							'THB: Thai Baht',
							'TJS: Tajikistani Somoni',
							'TOP: Tongan Paʻanga',
							'TRY: Turkish Lira',
							'TTD: Trinidad and Tobago Dollar',
							'TWD: New Taiwan Dollar',
							'TZS: Tanzanian Shilling',
							'UAH: Ukrainian Hryvnia',
							'UGX: Ugandan Shilling',
							'USD: United States Dollar',
							'UYU: Uruguayan Peso',
							'UZS: Uzbekistani Som',
							'VEF: Venezuelan Bolívar',
							'VND: Vietnamese Đồng',
							'VUV: Vanuatu Vatu',
							'WST: Samoan Tala',
							'XAF: Central African Cfa Franc',
							'XCD: East Caribbean Dollar',
							'XOF: West African Cfa Franc',
							'XPF: Cfp Franc',
							'YER: Yemeni Rial',
							'ZAR: South African Rand',
							'ZMW: Zambian Kwacha',
					),
			),
			'ssl' => array(
				'label'   => __('Use SSL', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					__('No', 'vikstripe'),
					__('Yes', 'vikstripe'),
				),
			),
			'skipbtn' => array(
				'label'   => __('Skip Pay Now BTN', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					__('No', 'vikstripe'),
					__('Yes', 'vikstripe'),
				),
			),
			'paytype' => array(
				'label'   => __('Payment Type', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					'auth',
					'pay',
				),
			),
			'use_decimals' => array(
				'label'   => __('Currency has decimals?', 'vikstripe'),
				'help'    => __('For currencies supporting decimals, Stripe requires the transaction amounts to be multiplied by 100 to express them as integer values.', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					1 => __('Yes', 'vikstripe'),
					0 => __('No', 'vikstripe'),
				),
			),
			'auto_pay_meths' => array(
				'label'   => __('Enable automatic payment methods', 'vikstripe'),
				'help'    => __('This is to allow Stripe to suggest alternative payment methods to clients, in case you enabled some from your Stripe Dashboard.', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					1 => __('Yes', 'vikstripe'),
					0 => __('No', 'vikstripe'),
				),
			),
			'future_usage' => array(
				'label'   => __('Set up Future Usage', 'vikstripe'),
				'help'    => __('Leave this setting enabled if you do not offer automatic payment methods beside the credit card. Some additional payment methods, like Klarna, may require this setting to be disabled as it is not a reusable payment method. ', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					1 => __('Yes', 'vikstripe'),
					0 => __('No', 'vikstripe'),
				),
			),
			'companyname' => array(
				'label' => __('Company Name', 'vikstripe'),
				'type'  => 'text',
			),
			'imageurl' => array(
				'label' => __('Image URL', 'vikstripe') . '//' . __('An image to be displayed during the purchase.', 'vikstripe'),
				'type'  => 'text',
			),
		);
	}
	
	/**
	 * @override
	 * Method used to begin a payment transaction.
	 * This method usually generates the HTML form of the payment.
	 * The HTML contents can be echoed directly because this method
	 * is executed always within a buffer.
	 *
	 * @return 	void
	 */
	protected function beginTransaction()
	{
		$key = \Stripe\Stripe::setApiKey($this->getParam('secretkey'));

		// load cart items
		$items = $this->loadCartItems($this->get('oid'));

		$submit_type = $this->getParam('paytype') == "auth" ? "manual" : "automatic";

		// default options for the payment intent data
		$payment_intent_data = [
			'capture_method' 	 => $submit_type,
			'description'	 	 => $this->get('transaction_name'),
			'setup_future_usage' => 'on_session'
		];

		if ($this->getParam('future_usage') == 0) {
			// some payment method types may not be re-usable
			unset($payment_intent_data['setup_future_usage']);
		}

		// build session-create options
		if ($this->getParam('auto_pay_meths')) {
			// we cannot include the "payment_method_types"
			$sess_create_opts = [
				'customer_email'       => $this->get('custmail'),
			  	'success_url'          => $this->get('notify_url'),
			  	'cancel_url'           => $this->get('return_url'),
			  	'line_items'           => $items,
			  	'mode'                 => 'payment',
			  	'payment_intent_data'  => $payment_intent_data
			];
		} else {
			// define "card" as the default payment method type
			$sess_create_opts = [
				'customer_email'       => $this->get('custmail'),
				'payment_method_types' => ['card'],
			  	'success_url'          => $this->get('notify_url'),
			  	'cancel_url'           => $this->get('return_url'),
			  	'line_items'           => $items,
			  	'mode'                 => 'payment',
			  	'payment_intent_data'  => $payment_intent_data
			];
		}

		// init payment transaction
		$session = \Stripe\Checkout\Session::create($sess_create_opts);

		// store session ID for later use
		$this->set('session_id', $session['id']);
		
		// get notification URL
		$url = $this->get('notify_url');

		// force HTTPS if needed
		if ($this->getParam('ssl'))
		{
			$url = str_replace('http:', 'https:', $url);
		}

		// get public key
		$pubkey = $this->getParam('pubkey');

		// load Stripe JS
		JFactory::getDocument()->addScript('https://js.stripe.com/v3');

		if ($this->getParam('skipbtn') == __('No', 'vikstripe'))
		{
			// register on click event
			JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	jQuery('#stripe-checkout-button').on('click', function() {
		var stripe = Stripe('{$pubkey}');
		stripe.redirectToCheckout({
			// Make the id field from the Checkout Session creation API response
			// available to this file, so you can provide it as parameter here
			// instead of the {{CHECKOUT_SESSION_ID}} placeholder.
			sessionId: '{$session['id']}',
		}).then(function (result) {

		});
	});
});
JS
			);

			// display Pay Now button
			$form = '<button class="btn btn-primary" id="stripe-checkout-button">'. __('Pay Now', 'vikstripe') . '</button>';
			//echo $this->get('payment_info')['note'];
			echo $form;
		}
		else
		{
			JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	
	var stripe = Stripe('{$pubkey}');
	stripe.redirectToCheckout({
		// Make the id field from the Checkout Session creation API response
		// available to this file, so you can provide it as parameter here
		// instead of the {{CHECKOUT_SESSION_ID}} placeholder.
		sessionId: '{$session['id']}',
	}).then(function (result) {
		
	});
});
JS
			);
		}

		return true;
	}
	
	/**
	 * @override
	 * Method used to validate the payment transaction.
	 * It is usually an end-point that the providers use to POST the
	 * transaction data.
	 *
	 * @param 	JPaymentStatus 	&$status 	The status object. In case the payment was 
	 * 										successful, you should invoke: $status->verified().
	 *
	 * @return 	void
	 *
	 * @see 	JPaymentStatus
	 */
	protected function validateTransaction(JPaymentStatus &$status)
	{
		$charge_amount = round($this->get('total_to_pay'), 2) * ($this->getParam('use_decimals', 1) ? 100 : 1);
		$status->appendLog("Starting Events iteration! \n");
		$txname = $this->get('sid') . '-' . $this->get('oid') . '.tx';
		$fp = fopen(VIKSTRIPE_DIR . DIRECTORY_SEPARATOR . 'Stripe' . DIRECTORY_SEPARATOR . $txname , 'w+');
		fwrite($fp, "Starting Events iteration! \n");
		fclose($fp);
		// init stripe
		\Stripe\Stripe::setApiKey($this->getParam('secretkey'));
		$name = $this->get('transaction_name');

		$events = \Stripe\Event::all([
			'type' => 'checkout.session.completed',
			'created' => [
				// check for events created in the last 24 hours
		    	'gte' => time() - 1440 * 60,
		    ],
		]);
		$txname = $this->get('sid') . '-' . $this->get('oid') . '.tx';
		$fp = fopen(VIKSTRIPE_DIR . DIRECTORY_SEPARATOR . 'Stripe' . DIRECTORY_SEPARATOR . $txname , 'a');
		fwrite($fp, "Events found: \n" . $eventi);
		$eventi = print_r($events, true);
		$status->appendLog($eventi);
		$status->appendLog($charge_amount . " - " . $this->get('session_id') . "\n");

		// iterate all events
		foreach ($events->autoPagingIterator() as $event)
		{
			$canproceed = false;
			$session = $event->data->object;
			$sessioncheck = ($session->id == $this->get('session_id')) ? "Session id okay" : "Session id not matching";
			$amountcheck = (((string)$charge_amount === (string)$session->amount_total || (string)$charge_amount == (string)$session->display_items[0]->amount) &&  $this->getParam('paytype') != "auth") ? "Amount okay" : (($this->getParam('paytype') == "auth") ? "auth mode active, this check is not necessary" : "amount not matching ");
			$urlcheck = ($session->success_url == $this->get('notify_url')) ? 'equal urls' : 'urls not matching';
			//E4J Debug
			$array = array();
			$array['log'] = '<pre>' . print_r($session->id, true) . '</pre>'. '<pre>' . print_r($session->amount_total, true) . '</pre>' . $sessioncheck . $amountcheck . $urlcheck;
			$status->appendLog($array['log']."\n");
			if ($session->id == $this->get('session_id')) {
				$canproceed = true;
			} else if ($session->success_url == $this->get('notify_url')) {
				$canproceed = true; 
			}

			if(((string)$charge_amount === (string)$session->amount_total || (string)$charge_amount == (string)$session->display_items[0]->amount) && $this->getParam('paytype') != "auth") {
				$canproceed = true;
			} else if ($this->getParam('paytype') == "auth"){
				$canproceed = true;
			} else {
				$canproceed = false;		
			}

			// iterate until we find the session ID of the transaction (paid amount must match)
			if ($canproceed)
			{
				$status->verified();

				if ($this->getParam('paytype') != "auth")
				{
					$status->paid($charge_amount / ($this->getParam('use_decimals', 1) ? 100 : 1));
				}

				/**
				 *  @since 1.1.4
				 *
				 *	Deletion of transients and files is done directly there to support multiple transactions.
				 *
				 */

				if ($this->get('is_transient') === true) {
					$was_using_cache = wp_using_ext_object_cache(false);
					// always attempt to delete transient
					delete_transient($this->get('transient_name'));

					$array['log'] .= '<pre> transient deleted! </pre>'; 
					//variables for record storage
					$this->set('payment_status', 'confirmed');
					$this->set('payment_intent', $session->payment_intent);

					// set transaction data for response, needed for any later refund operation
					if ($status->amount) {
						$transaction = new stdClass;
						$transaction->driver = 'stripe.php';
						$transaction->payment_intent = $session->payment_intent;
						$transaction->amount = $status->amount;
						$status->setData('transaction', $transaction);
					}
					$name = !empty($name) ? $name : __('Reservation number : ', 'vikstripe') . $this->get('oid');
					$payInt = $session->payment_intent;
					$updated = \Stripe\PaymentIntent::update($payInt, ["description" => $name]);

					// restore cache flag
					wp_using_ext_object_cache($was_using_cache);
				} else {
					// remove transaction file
					$array['log'] .= '<pre> file deleted! </pre>';
					unlink($this->get('file_path'));
				}

				$status->appendLog($array['log']."\n");
				$status->appendLog($session."\n");

				// stop iterating
				return true;
			}
		}
	}
	
	/**
	 * @override
	 * Method used to finalise the payment.
	 * e.g. enter here the code used to redirect the
	 * customers to a specific landing page.
	 *
	 * @param 	boolean  $res 	True if the payment was successful, otherwise false.
	 *
	 * @return 	void
	 */
	protected function complete($res)
	{
		$app = JFactory::getApplication();

		if ($res)
		{
			$url = $this->get('return_url');

			// display successful message
			$app->enqueueMessage(__('Thank you! Payment successfully received.', 'vikstripe'));
		}
		else
		{
			$url = $this->get('error_url');

			// display error message
			$app->enqueueMessage(__('It was not possible to verify the payment. Please, try again.', 'vikstripe'));
		}

		JFactory::getApplication()->redirect($url);
		exit;
	}
	/**
	 * @override
	 * Method used to create the cart.
	 * @since 1.0.6
	 *
	 * @param 	integer  $orderid  Id of the order.
	 *
	 * @return 	array 	 The associative array containing the items booked.
	 */
	protected function loadCartItems($orderid)
	{
		$amount_to_pay = round($this->get('total_to_pay'), 2) * ($this->getParam('use_decimals', 1) ? 100 : 1);
		
		// create default array if the cart is not supported by the plugin
		$item = [
			'price_data' => [
				'currency'    => $this->getParam('currency'),
				'unit_amount' => $amount_to_pay,
				'product_data' => [
					'name'   => $this->get('transaction_name'),
					'images' => [],
				],
			],
			'quantity' => 1,
		];

		// $item = array(
		// 	'name'     => $this->get('transaction_name'),
		// 	'images'   => [],
		// 	'amount'   => $amount_to_pay,
		// 	'currency' => $this->getParam('currency'),
		// 	'quantity' => 1,
		// );

		// add image logo if specified
		if ($img = $this->getParam('imageurl'))
		{
			// $item['images'][] = $img;
			$item['price_data']['product_data']['images'][] = $img;
		}

		// return an array
		return [$item];
	}

	/**
	 * @override
	 *
	 * Executes the refund transaction by collecting the passed data.
	 *
	 * @return 	boolean
	 */
	protected function doRefund(JPaymentStatus &$status) 
	{
		$transaction = $this->get('transaction');
		$amount 	 = $this->get('total_to_refund');

		if (!$transaction || is_scalar($transaction)) {
			$status->appendLog('No previous transactions found');
			return;
		}

		if ($amount <= 0) {
			$status->appendLog('Invalid transaction amount');
			return;
		}

		if (is_object($transaction)) {
			$transaction = array($transaction);
		}

		// seek for a valid payment intent
		$payment_intent = null;
		foreach ($transaction as $tn) {
			if (!is_object($tn) || !isset($tn->payment_intent)) {
				continue;
			}
			if ($amount <= $tn->amount) {
				$payment_intent = $tn->payment_intent;
				// do not break the loop to always use the latest transaction ID
			}
		}

		if (!$payment_intent) {
			// do not proceed if no valid payment_intent has been found
			$status->appendLog('No valid payment intent found for the amount to be refunded' . "\n" . print_r($transaction, true));
			return;
		}

		\Stripe\Stripe::setApiKey($this->getParam('secretkey'));
		$refund = \Stripe\Refund::create([
			'payment_intent' => $payment_intent,
			'amount'		 => $amount * ($this->getParam('use_decimals', 1) ? 100 : 1),
		]);

		echo 'Stripe Debug<pre>' . print_r($refund, true) . '</pre>';

		if ($refund['status'] == 'succeeded') {
			$status->verified();
			$status->setData('amount', $amount);
			return;
		}

		// append error log
		$status->appendLog('Refund failed.' . "\n" . print_r($refund, true));
	}

	/**
	 * @override
	 *
	 * This Stripe integration does support refunds.
	 *
	 * @return 	boolean
	 */
	public function isRefundSupported()
	{
		return true;
	}

}
