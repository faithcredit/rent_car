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
 * VikRentCar conversion-tracker (measurment) driver for
 * Google Analytics v4 (2022).
 *
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VRCConversionTrackerGoogleAnalytics extends VRCConversionTracker
{
	/**
	 * Define measurment driver info.
	 */
	public function __construct()
	{
		$this->driverName 	= 'Google Analytics';
		$this->driverDescr 	= 'Google Analytics v4';
		$this->driverId 	= basename(__FILE__, '.php');
	}

	/**
	 * The necessary params for this driver.
	 * 
	 * @return 	array
	 */
	public function renderParams()
	{
		return [
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4>' . $this->driverDescr . '</h4>',
			],
			'measurment_id' => [
				'type'    => 'text',
				'label'   => JText::_('VRC_GA_MEASURMENT_ID'),
				'help'    => JText::_('VRC_GA_MEASURMENT_ID_HELP'),
				'default' => '',
			],
		];
	}

	/**
	 * Tracks a specific event of the front-end.
	 * 
	 * @return 	void
	 */
	public function trackEvent($event = null)
	{
		// load JS assets
		if (!$this->loadGoogleScript()) {
			// do not proceed
			return;
		}

		switch ($event) {
			case 'cardetails':
				$this->trackEventCardetails();
				break;

			case 'search':
				$this->trackEventSearch();
				break;

			case 'showprc':
				$this->trackEventShowprc();
				break;

			case 'oconfirm':
				$this->trackEventOconfirm();
				break;

			case 'order':
				$this->trackEventOrder();
				break;

			default:
				return;
		}
	}

	/**
	 * Applies the booking conversion for a new order.
	 * 
	 * @return 	void
	 */
	public function doConversion(array $booking = null)
	{
		// load JS assets and validate booking array
		if (empty($booking) || !$this->loadGoogleScript()) {
			// do not proceed
			return;
		}

		// load car details
		$car_info = VikRentCar::getCarInfo($booking['idcar']);
		if (empty($car_info)) {
			return;
		}

		// category of the vehicle
		$car_category = VikRentCar::sayCategory($car_info['idcat']);

		// build purchase object
		$purchase_obj = [
			'transaction_id' => $booking['id'],
			'value' 		 => (float)$booking['order_total'],
			'tax' 			 => (float)$booking['tot_taxes'],
			'currency' 		 => VikRentCar::getCurrencyName(),
			'items' 		 => [],
		];

		// build item data
		$item_data = [
			'item_id' 		=> $car_info['id'],
			'item_name' 	=> $car_info['name'],
			'item_category' => (!empty($car_category) ? $car_category : null),
		];

		if (!empty($booking['car_cost'])) {
			// this value may be injected by the View for the single car cost
			$item_data['price'] = (float)$booking['car_cost'];
		}

		// push item
		$purchase_obj['items'][] = $item_data;

		$track_purchase = json_encode($purchase_obj, JSON_PRETTY_PRINT);

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
gtag("event", "purchase", $track_purchase);
JS
		);

		return;
	}

	/**
	 * Loads the necessary JS assets for GA.
	 * 
	 * @return 	bool 	false if params are incomplete.
	 */
	protected function loadGoogleScript()
	{
		$measurment_id = $this->getParam('measurment_id', '');

		if (empty($measurment_id)) {
			return false;
		}

		$doc = JFactory::getDocument();
		$doc->addScript("https://www.googletagmanager.com/gtag/js?id={$measurment_id}", $options = [], $attribs = ['async' => 'async']);
		$doc->addScriptDeclaration(
<<<JS
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '$measurment_id');
JS
		);

		return true;
	}

	/**
	 * Track the car details page.
	 * 
	 * @return 	void
	 */
	protected function trackEventCardetails()
	{
		$view_data = $this->getProperty('data', null);

		if (!is_object($view_data) || empty($view_data->car)) {
			return;
		}

		$car_details = $view_data->car;

		$car_category = VikRentCar::sayCategory($car_details['idcat']);

		// build track data
		$track_data = [
			[
				'item_id' 		=> $car_details['id'],
				'item_name' 	=> $car_details['name'],
				'item_category' => (!empty($car_category) ? $car_category : null),
			]
		];

		$track_items = json_encode($track_data, JSON_PRETTY_PRINT);

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
gtag("event", "view_item", {
	items: $track_items
});
JS
		);

		return;
	}

	/**
	 * Track the search results page.
	 * 
	 * @return 	void
	 */
	protected function trackEventSearch()
	{
		$search_results = $this->getProperty('data', []);

		if (!is_array($search_results) || empty($search_results)) {
			return;
		}

		// build track data
		$track_data = [];

		foreach ($search_results as $id_car => $car_data) {
			$car_info = VikRentCar::getCarInfo($id_car);
			if (empty($car_info)) {
				continue;
			}
			$car_category = VikRentCar::sayCategory($car_info['idcat']);
			// push track item
			$track_data[] = [
				'item_id' 		=> $id_car,
				'item_name' 	=> $car_info['name'],
				'item_category' => (!empty($car_category) ? $car_category : null),
			];
		}

		if (count($track_data)) {
			$track_items = json_encode($track_data, JSON_PRETTY_PRINT);

			JFactory::getDocument()->addScriptDeclaration(
<<<JS
gtag("event", "view_item_list", {
	item_list_id: "search_results",
	item_list_name: "Search results",
	items: $track_items
});
JS
			);
		}

		return;
	}

	/**
	 * Track the showprc page (choice of rate plan and options/extras).
	 * 
	 * @return 	void
	 */
	protected function trackEventShowprc()
	{
		$view_elements = $this->getProperty('data', null);

		if (!is_object($view_elements) || empty($view_elements->car)) {
			return;
		}

		$car_category = VikRentCar::sayCategory($view_elements->car['idcat']);

		// build track data
		$track_data = [
			[
				'item_id' 		=> $view_elements->car['id'],
				'item_name' 	=> $view_elements->car['name'],
				'item_category' => (!empty($car_category) ? $car_category : null),
			]
		];

		$track_items = json_encode($track_data, JSON_PRETTY_PRINT);

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
gtag("event", "select_item", {
	item_list_id: "select_price_options",
	item_list_name: "Select price options",
	items: $track_items
});
JS
		);

		return;
	}

	/**
	 * Track the order confirmation page.
	 * 
	 * @return 	void
	 */
	protected function trackEventOconfirm()
	{
		$view_data = $this->getProperty('data', null);

		if (!is_array($view_data) || empty($view_data['elements'])) {
			return;
		}

		$currency_name = VikRentCar::getCurrencyName();
		$order_total = !empty($view_data['order_total']) ? (float)$view_data['order_total'] : 0;

		$car_category = VikRentCar::sayCategory($view_data['elements']->car['idcat']);

		// build track data
		$track_data = [
			[
				'item_id' 		=> $view_data['elements']->car['id'],
				'item_name' 	=> $view_data['elements']->car['name'],
				'item_category' => (!empty($car_category) ? $car_category : null),
			]
		];

		$track_items = json_encode($track_data, JSON_PRETTY_PRINT);

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
gtag("event", "add_to_cart", {
	currency: "$currency_name",
	value: $order_total,
	items: $track_items
});
JS
		);

		return;
	}

	/**
	 * Track the order details page (pending or cancelled).
	 * 
	 * @return 	void
	 */
	protected function trackEventOrder()
	{
		$view_data = $this->getProperty('data', null);

		if (!is_object($view_data) || empty($view_data->ord)) {
			return;
		}

		$currency_name = VikRentCar::getCurrencyName();
		$order_total = (float)$view_data->ord['order_total'];
		$car_info = VikRentCar::getCarInfo($view_data->ord['idcar']);

		if (empty($car_info) || strcasecmp($view_data->ord['status'], 'standby')) {
			// order status must be pending
			return;
		}

		$car_category = VikRentCar::sayCategory($car_info['idcat']);

		// build track data
		$track_data = [
			[
				'item_id' 		=> $car_info['id'],
				'item_name' 	=> $car_info['name'],
				'item_category' => (!empty($car_category) ? $car_category : null),
			]
		];

		$track_items = json_encode($track_data, JSON_PRETTY_PRINT);

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
gtag("event", "begin_checkout", {
	currency: "$currency_name",
	value: $order_total,
	items: $track_items
});
JS
		);

		return;
	}
}
