<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

class VikRentCarCronJobWebhook extends VRCCronJob
{
	// do not need to track the elements
	use VRCCronTrackerUnused;

	/**
	 * This method should return all the form fields required to collect the information
	 * needed for the execution of the cron job.
	 * 
	 * @return  array  An associative array of form fields.
	 */
	public function getForm()
	{
		return [
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4><i class="' . VikRentCarIcons::i('plug') . '"></i>&nbsp;<i class="' . VikRentCarIcons::i('clock') . '"></i>&nbsp;' . $this->getTitle() . '</h4>',
			],
			'type' => [
				'type'    => 'select',
				'label'   => JText::_('VRC_CRONJOB_WEBHOOK_TYPE_LABEL'),
				'help'    => JText::_('VRC_CRONJOB_WEBHOOK_TYPE_DESC'),
				'options' => [
					'url'      => JText::_('VRC_CRONJOB_WEBHOOK_TYPE_URL_OPTION'),
					'callback' => JText::_('VRC_CRONJOB_WEBHOOK_TYPE_CALLBACK_OPTION'),
					'action'   => JText::_('VRC_CRONJOB_WEBHOOK_TYPE_ACTION_OPTION'),
				],
			],
			'handler' => [
				'type'   => 'text',
				'label'   => JText::_('VRC_CRONJOB_WEBHOOK_HANDLER_LABEL'),
				'help'    => JText::_('VRC_CRONJOB_WEBHOOK_HANDLER_DESC'),
			],
			'help' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<p class="vrc-cronparam-suggestion"><i class="vrcicn-lifebuoy"></i>' . JText::_('VRC_CRONJOB_WEBHOOK_DESCRIPTION') . '</p>',
			],
		];
	}

	/**
	 * Returns the title of the cron job.
	 * 
	 * @return  string
	 */
	public function getTitle()
	{
		return JText::_('VRC_CRON_WEBHOOK_TITLE');
	}
	
	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function execute()
	{
		// fetch the cron method to launch
		$method = 'trigger' . ucfirst($this->params->get('type', 'url'));

		if (!method_exists($this, $method))
		{
			$this->appendLog('No trigger method found for: ' . $this->params->get('type', 'url'));
			throw new RuntimeException(sprintf('Unable to launch [%s] method', $this->params->get('type', 'url')), 404);
		}

		// pull the latest orders
		$orders = $this->pullOrders();

		if (!$orders)
		{
			$this->appendLog('There are no recent orders.');
			return true;
		}

		// notify the latest orders to the registered subscribers
		foreach ($orders as $order)
		{
			try
			{
				// dispatch webhook
				$this->{$method}($order);

				// order notified successfully
				$this->appendLog('Notified order #' . $order['id']);
			}
			catch (Exception $e)
			{
				// an error has occurred, log message
				$this->appendLog('Error ' . $e->getCode() . '. Unable to notify order #' . $order['id'] . ' for this reason: ' . $e->getMessage());
			}
		}

		return true;
	}

	/**
	 * Notifies the given booking data to a specific HTTP end-point.
	 * 
	 * @param   array  $booking  The booking data.
	 * 
	 * @return  void
	 * 
	 * @throws  Exception
	 */
	protected function triggerUrl($booking)
	{
		$http = new JHttp();

		// make POST request to the specified URL
		$response = $http->post($this->params->get('handler'), json_encode($booking), [
			'Content-Type' => 'application/json'
		]);

		if ($response->code != 200)
		{
			// invalid response, throw an exception
			throw new Exception(strip_tags($response->body), $response->code);
		}

		$this->output(strip_tags($response->body));
	}

	/**
	 * Notifies the given booking data to a specific PHP callback.
	 * In case the callback contains a comma, the chunk before will be
	 * used as class name and the next string will be used as method.
	 * 
	 * @param   array  $booking  The booking data.
	 * 
	 * @return  void
	 * 
	 * @throws  Exception
	 */
	protected function triggerCallback($booking)
	{
		// get PHP callback from cron configuration
		$handler = $this->params->get('handler', '');

		if (strpos($handler, ',') !== false)
		{
			// comma found, extract class and method name
			$handler = preg_split("/\s*,\s*/", $handler);
		}

		// check whether the specified callback can be invoked
		if (!is_callable($handler))
		{
			if (is_array($handler))
			{
				$handler = $handler[0] . '::' . $handler[1] . '()';
			}

			throw new Exception('Cannot invoke ' . $handler . ' method', 500);
		}

		// invoke PHP callback
		$return = call_user_func_array($handler, [$booking]);

		if (!is_null($return))
		{
			if (is_array($return) || is_object($return))
			{
				$return = print_r($return, true);
			}

			$this->output('Returned value: ' . $return);
		}
	}

	/**
	 * Notifies the given booking data through a platform event.
	 * 
	 * @param   array  $booking  The booking data.
	 * 
	 * @return  void
	 * 
	 * @throws  Exception
	 */
	protected function triggerAction($booking)
	{
		$handler = $this->params->get('handler', '');

		if (!$handler)
		{
			throw new Exception('The action cannot be empty', 400);
		}

		// delegate trigger to the proper platform dispatcher
		VRCFactory::getPlatform()->getDispatcher()->trigger($handler, [$booking]);
	}

	/**
	 * Retrieves all the orders that has been recently created/updated.
	 * 
	 * @return  array  A list of downloaded orders.
	 */
	protected function pullOrders()
	{
		// check whether the threshold have been initialized yet
		if (!$this->getData()->flag_int)
		{
			$date = $this->initThreshold();

			$this->appendLog('Initialized orders threshold at ' . $date->format('Y-m-d H:i:s') . ' (UTC)');

			return [];
		}

		$historyHandler = VikRentCar::getBookingHistoryInstance();

		$db = JFactory::getDbo();

		// take all the orders with a creation/update datetime higher than the saved threshold
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__vikrentcar_orderhistory'))
			->where($db->qn('dt') . ' > ' . $db->q(JFactory::getDate($this->getData()->flag_int)->toSql()))
			->order($db->qn('dt') . ' DESC');

		$db->setQuery($query);
		$history = $db->loadAssocList();

		if (!$history)
		{
			// nothing to notify
			return [];
		}

		$orders = [];

		foreach ($history as $status)
		{
			if (!isset($orders[$status['idorder']]))
			{
				// fetch order details
				$order = VikRentCar::getBookingInfoFromID($status['idorder']);

				if (!$order)
				{
					$this->appendLog('Unable to fetch order details for booking #' . $status['idorder']);
					continue;
				}

				// extend booking details with further information
				$order['car_data'] = VikRentCar::getCarInfo($order['idcar']);
				$order['customer']  = VikRentCar::getCPinIstance()->getCustomerFromBooking($order['id']);
				$order['history']   = [];

				// register order details only once
				$orders[$status['idorder']] = $order;
			}

			// recover type title
			$status['event'] = $historyHandler->validType($status['type'], $return = true);

			// append order status
			$orders[$status['idorder']]['history'][] = $status;
		}

		// update threshold
		$this->initThreshold();

		return array_values($orders);
	}

	/**
	 * Configures the bookings threshold.
	 * The first time this cron is executed, it will save the current time as threshold.
	 * 
	 * @return  JDate
	 */
	protected function initThreshold()
	{
		$date = JFactory::getDate();

		// register the current time
		$this->getData()->flag_int = $date->getTimestamp();

		return $date;
	}	
}
