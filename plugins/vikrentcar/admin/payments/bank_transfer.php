<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.payment.payment');

/**
 * This class is used to collect payments in VikRentCar plugin
 * by using the bank transfer method (also used as "pay on arrival").
 *
 * @since 1.0.5
 */
class VikRentCarBankTransferPayment extends JPayment
{
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
		$depositmess = "";

		if ($this->get('leave_deposit'))
		{
			$depositmess = "<p class=\"vrc-leave-deposit\"><span>".JText::_('VRLEAVEDEPOSIT')."</span>".$this->get('currency_symb')." ".VikRentCar::numberFormat($this->get('total_to_pay'))."</p><br/>";
		}

		$info = $this->get('payment_info');

		//output form
		echo $depositmess;
		echo $info['note'];
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
	 * @return 	boolean
	 *
	 * @see 	JPaymentStatus
	 */
	protected function validateTransaction(JPaymentStatus &$status)
	{
		$status->verified();
		return true;
	}
}
