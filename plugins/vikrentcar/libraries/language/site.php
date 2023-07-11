<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	language
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.language.handler');

/**
 * Switcher class to translate the VikRentCar plugin site languages.
 *
 * @since 	1.0
 */
class VikRentCarLanguageSite implements JLanguageHandler
{
	/**
	 * Checks if exists a translation for the given string.
	 *
	 * @param 	string 	$string  The string to translate.
	 *
	 * @return 	string 	The translated string, otherwise null.
	 */
	public function translate($string)
	{
		$result = null;

		/**
		 * Translations go here.
		 * @tip Use 'TRANSLATORS:' comment to attach a description of the language.
		 */

		switch ($string)
		{
			case 'VRDATE':
				$result = __('Date', 'vikrentcar');
				break;
			case 'VRIP':
				$result = __('IP', 'vikrentcar');
				break;
			case 'VRPLACE':
				$result = __('Location', 'vikrentcar');
				break;
			case 'VRORDNOL':
				$result = __('Rental Order', 'vikrentcar');
				break;
			case 'VRINATTESA':
				$result = __('Waiting for the Payment', 'vikrentcar');
				break;
			case 'VRCOMPLETED':
				$result = __('Completed', 'vikrentcar');
				break;
			case 'VRCARBOOKEDBYOTHER':
				$result = __('Sorry, the car has been booked. Please make a new order.', 'vikrentcar');
				break;
			case 'VRCARISLOCKED':
				$result = __('The car is now being ordered by another customer. Please make a new order.', 'vikrentcar');
				break;
			case 'VRINVALIDDATES':
				$result = __('Pickup and Drop Off Dates are wrong', 'vikrentcar');
				break;
			case 'VRINCONGRTOT':
				$result = __('Error, Order Total is wrong', 'vikrentcar');
				break;
			case 'VRINCONGRDATAREC':
				$result = __('Error, Wrong data.', 'vikrentcar');
				break;
			case 'VRINCONGRDATA':
				$result = __('Error, Wrong data.', 'vikrentcar');
				break;
			case 'VRINSUFDATA':
				$result = __('Error, Insufficient Data Received.', 'vikrentcar');
				break;
			case 'VRINVALIDTOKEN':
				$result = __('Error, Invalid Token. Unable to Save the Order', 'vikrentcar');
				break;
			case 'VRERRREPSEARCH':
				$result = __('Error, Car already booked. Please search for another one.', 'vikrentcar');
				break;
			case 'VRORDERNOTFOUND':
				$result = __('Error, Order not found', 'vikrentcar');
				break;
			case 'VRERRCALCTAR':
				$result = __('Error occured processing fares. Please choose new dates', 'vikrentcar');
				break;
			case 'VRTARNOTFOUND':
				$result = __('Error, Not Existing Fare', 'vikrentcar');
				break;
			case 'VRNOTARSELECTED':
				$result = __('No Fares selected', 'vikrentcar');
				break;
			case 'VRCARNOTCONS':
				$result = __('Car is not Returnable from the', 'vikrentcar');
				break;
			case 'VRCARNOTCONSTO':
				$result = __('to the', 'vikrentcar');
				break;
			case 'VRCARNOTRIT':
				$result = __('Car is not available from the', 'vikrentcar');
				break;
			case 'VRCARNOTFND':
				$result = __('Car not found', 'vikrentcar');
				break;
			case 'VRCARNOTAV':
				$result = __('Car not available', 'vikrentcar');
				break;
			case 'VRNOTARFNDSELO':
				$result = __('No Fares Found. Please select a different date or car', 'vikrentcar');
				break;
			case 'VRSRCHNOTM':
				$result = __('Search Notification', 'vikrentcar');
				break;
			case 'VRCAT':
				$result = __('Category', 'vikrentcar');
				break;
			case 'VRANY':
				$result = __('Any', 'vikrentcar');
				break;
			case 'VRPICKUP':
				$result = __('Pickup', 'vikrentcar');
				break;
			case 'VRRETURN':
				$result = __('Drop Off', 'vikrentcar');
				break;
			case 'VRSRCHRES':
				$result = __('Search Results', 'vikrentcar');
				break;
			case 'VRNOCARSINDATE':
				$result = __('None of the cars is available on the requested period.', 'vikrentcar');
				break;
			case 'VRNOCARAVFOR':
				$result = __('No cars is available for rental for', 'vikrentcar');
				break;
			case 'VRDAYS':
				$result = __('Days', 'vikrentcar');
				break;
			case 'VRDAY':
				$result = __('Day', 'vikrentcar');
				break;
			case 'VRPICKBRET':
				$result = __('Drop Off Date previous to Pickup', 'vikrentcar');
				break;
			case 'VRWRONGDF':
				$result = __('Wrong Date Format. Right Format is', 'vikrentcar');
				break;
			case 'VRSELPRDATE':
				$result = __('Please select Pickup and Drop Off Date', 'vikrentcar');
				break;
			case 'VRPPLACE':
				$result = __('Pickup Location', 'vikrentcar');
				break;
			case 'VRPICKUPCAR':
				$result = __('Pickup Date', 'vikrentcar');
				break;
			case 'VRRETURNCAR':
				$result = __('Drop Off Date', 'vikrentcar');
				break;
			case 'VRALLE':
				$result = __('Pickup Time', 'vikrentcar');
				break;
			case 'VRALLEDROP':
				$result = __('Drop Off Time', 'vikrentcar');
				break;
			case 'VRCARCAT':
				$result = __('Car Category', 'vikrentcar');
				break;
			case 'VRALLCAT':
				$result = __('Any', 'vikrentcar');
				break;
			case 'VRERRCONNPAYP':
				$result = __('Error while connecting to Paypal.com', 'vikrentcar');
				break;
			case 'VRIMPVERPAYM':
				$result = __('Unable to process the payment of the', 'vikrentcar');
				break;
			case 'VRRENTALORD':
				$result = __('Rental Order', 'vikrentcar');
				break;
			case 'VRCOMPLETED':
				$result = __('Completed', 'vikrentcar');
				break;
			case 'VRVALIDPWSAVE':
				$result = __('Valid Paypal Payment, Error Saving the Order', 'vikrentcar');
				break;
			case 'VRVALIDPWSAVEMSG':
				$result = __('Payment received with Success, Order not Saved. Correct the problem manually.', 'vikrentcar');
				break;
			case 'VRPAYPALRESP':
				$result = __('Paypal Response', 'vikrentcar');
				break;
			case 'VRINVALIDPAYPALP':
				$result = __('Invalid Paypal Payment', 'vikrentcar');
				break;
			case 'ERRSELECTPAYMENT':
				$result = __('Please Select a Payment Method', 'vikrentcar');
				break;
			case 'VRPAYMENTNOTVER':
				$result = __('Payment Not Verified', 'vikrentcar');
				break;
			case 'VRSERVRESP':
				$result = __('Server Response', 'vikrentcar');
				break;
			case 'VRCONFIGONETWELVE':
				$result = __('DD/MM/YYYY', 'vikrentcar');
				break;
			case 'VRCONFIGONETENTHREE':
				$result = __('YYYY/MM/DD', 'vikrentcar');
				break;
			case 'VRCARSFND':
				$result = __('Cars Found', 'vikrentcar');
				break;
			case 'VRPROSEGUI':
				$result = __('Continue', 'vikrentcar');
				break;
			case 'VRSTARTFROM':
				$result = __('Starting from', 'vikrentcar');
				break;
			case 'VRRENTAL':
				$result = __('Rental', 'vikrentcar');
				break;
			case 'VRFOR':
				$result = __('for', 'vikrentcar');
				break;
			case 'VRPRICE':
				$result = __('Price', 'vikrentcar');
				break;
			case 'VRACCOPZ':
				$result = __('Options', 'vikrentcar');
				break;
			case 'VRBOOKNOW':
				$result = __('Book now', 'vikrentcar');
				break;
			case 'VRDAL':
				$result = __('From', 'vikrentcar');
				break;
			case 'VRAL':
				$result = __('To', 'vikrentcar');
				break;
			case 'VRRIEPILOGOORD':
				$result = __('Order Summary', 'vikrentcar');
				break;
			case 'VRTOTAL':
				$result = __('Total', 'vikrentcar');
				break;
			case 'VRIMP':
				$result = __('Taxable Income', 'vikrentcar');
				break;
			case 'VRIVA':
				$result = __('Tax', 'vikrentcar');
				break;
			case 'VRDUE':
				$result = __('Total Due', 'vikrentcar');
				break;
			case 'VRFILLALL':
				$result = __('Please fill in all fields', 'vikrentcar');
				break;
			case 'VRPURCHDATA':
				$result = __('Purchaser Details', 'vikrentcar');
				break;
			case 'VRNAME':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRLNAME':
				$result = __('Last Name', 'vikrentcar');
				break;
			case 'VRMAIL':
				$result = __('e-Mail', 'vikrentcar');
				break;
			case 'VRPHONE':
				$result = __('Phone', 'vikrentcar');
				break;
			case 'VRADDR':
				$result = __('Address', 'vikrentcar');
				break;
			case 'VRCAP':
				$result = __('Zip Code', 'vikrentcar');
				break;
			case 'VRCITY':
				$result = __('City', 'vikrentcar');
				break;
			case 'VRNAT':
				$result = __('State', 'vikrentcar');
				break;
			case 'VRDOBIRTH':
				$result = __('Date of Birth', 'vikrentcar');
				break;
			case 'VRFISCALCODE':
				$result = __('Fiscal Code', 'vikrentcar');
				break;
			case 'VRORDCONFIRM':
				$result = __('Confirm Order', 'vikrentcar');
				break;
			case 'VRTHANKSONE':
				$result = __('Thanks, Order Successfully Completed', 'vikrentcar');
				break;
			case 'VRTHANKSTWO':
				$result = __('To review your order, please visit', 'vikrentcar');
				break;
			case 'VRTHANKSTHREE':
				$result = __('This Page', 'vikrentcar');
				break;
			case 'VRORDEREDON':
				$result = __('Order Date', 'vikrentcar');
				break;
			case 'VRPERSDETS':
				$result = __('Personal Details', 'vikrentcar');
				break;
			case 'VRCARRENTED':
				$result = __('Hired Car', 'vikrentcar');
				break;
			case 'VROPTS':
				$result = __('Options', 'vikrentcar');
				break;
			case 'VRWAITINGPAYM':
				$result = __('Waiting for the Payment', 'vikrentcar');
				break;
			case 'VRBACK':
				$result = __('Back', 'vikrentcar');
				break;
			case 'ORDDD':
				$result = __('Days', 'vikrentcar');
				break;
			case 'ORDNOTAX':
				$result = __('Net Price', 'vikrentcar');
				break;
			case 'ORDTAX':
				$result = __('Tax', 'vikrentcar');
				break;
			case 'ORDWITHTAX':
				$result = __('Total Price', 'vikrentcar');
				break;
			case 'VRRITIROCAR':
				$result = __('Pickup Location', 'vikrentcar');
				break;
			case 'VRRETURNCARORD':
				$result = __('Drop Off Location', 'vikrentcar');
				break;
			case 'VRADDNOTES':
				$result = __('Notes', 'vikrentcar');
				break;
			case 'VRCHANGEDATES':
				$result = __('Change Dates', 'vikrentcar');
				break;
			case 'VRLOCFEETOPAY':
				$result = __('Pickup/Drop Off Fee', 'vikrentcar');
				break;
			case 'VRCHOOSEPAYMENT':
				$result = __('Payment Method', 'vikrentcar');
				break;
			case 'VRLIBONE':
				$result = __('Order Received on the', 'vikrentcar');
				break;
			case 'VRLIBTWO':
				$result = __('Purchaser Info', 'vikrentcar');
				break;
			case 'VRLIBTHREE':
				$result = __('Rented vehicle', 'vikrentcar');
				break;
			case 'VRLIBFOUR':
				$result = __('Pickup Date', 'vikrentcar');
				break;
			case 'VRLIBFIVE':
				$result = __('Drop Off Date', 'vikrentcar');
				break;
			case 'VRLIBSIX':
				$result = __('Total', 'vikrentcar');
				break;
			case 'VRLIBSEVEN':
				$result = __('Order Status', 'vikrentcar');
				break;
			case 'VRLIBEIGHT':
				$result = __('Order Date', 'vikrentcar');
				break;
			case 'VRLIBNINE':
				$result = __('Personal Details', 'vikrentcar');
				break;
			case 'VRLIBTEN':
				$result = __('Rented vehicle', 'vikrentcar');
				break;
			case 'VRLIBELEVEN':
				$result = __('Pickup Date', 'vikrentcar');
				break;
			case 'VRLIBTWELVE':
				$result = __('Drop Off Date', 'vikrentcar');
				break;
			case 'VRLIBTENTHREE':
				$result = __('To see your order details, visit the following page', 'vikrentcar');
				break;
			case 'VRMONTHONE':
				$result = __('January', 'vikrentcar');
				break;
			case 'VRMONTHTWO':
				$result = __('February', 'vikrentcar');
				break;
			case 'VRMONTHTHREE':
				$result = __('March', 'vikrentcar');
				break;
			case 'VRMONTHFOUR':
				$result = __('April', 'vikrentcar');
				break;
			case 'VRMONTHFIVE':
				$result = __('May', 'vikrentcar');
				break;
			case 'VRMONTHSIX':
				$result = __('June', 'vikrentcar');
				break;
			case 'VRMONTHSEVEN':
				$result = __('July', 'vikrentcar');
				break;
			case 'VRMONTHEIGHT':
				$result = __('August', 'vikrentcar');
				break;
			case 'VRMONTHNINE':
				$result = __('September', 'vikrentcar');
				break;
			case 'VRMONTHTEN':
				$result = __('October', 'vikrentcar');
				break;
			case 'VRMONTHELEVEN':
				$result = __('November', 'vikrentcar');
				break;
			case 'VRMONTHTWELVE':
				$result = __('December', 'vikrentcar');
				break;
			case 'VRLEAVEDEPOSIT':
				$result = __('Leave a deposit of ', 'vikrentcar');
				break;
			case 'VRLIBPAYNAME':
				$result = __('Payment Method', 'vikrentcar');
				break;
			case 'ORDER_NAME':
				$result = __('Name', 'vikrentcar');
				break;
			case 'ORDER_LNAME':
				$result = __('Last Name', 'vikrentcar');
				break;
			case 'ORDER_EMAIL':
				$result = __('e-Mail', 'vikrentcar');
				break;
			case 'ORDER_PHONE':
				$result = __('Phone', 'vikrentcar');
				break;
			case 'ORDER_ADDRESS':
				$result = __('Address', 'vikrentcar');
				break;
			case 'ORDER_ZIP':
				$result = __('Zip Code', 'vikrentcar');
				break;
			case 'ORDER_CITY':
				$result = __('City', 'vikrentcar');
				break;
			case 'ORDER_STATE':
				$result = __('Country', 'vikrentcar');
				break;
			case 'ORDER_DBIRTH':
				$result = __('Date of Birth', 'vikrentcar');
				break;
			case 'ORDER_FLIGHTNUM':
				$result = __('Flight Number', 'vikrentcar');
				break;
			case 'ORDER_NOTES':
				$result = __('Notes', 'vikrentcar');
				break;
			case 'VRCLISTSFROM':
				$result = __('Starting from', 'vikrentcar');
				break;
			case 'VRCLISTPICK':
				$result = __('View Details', 'vikrentcar');
				break;
			case 'VRSUN':
				$result = __('Sun', 'vikrentcar');
				break;
			case 'VRMON':
				$result = __('Mon', 'vikrentcar');
				break;
			case 'VRTUE':
				$result = __('Tue', 'vikrentcar');
				break;
			case 'VRWED':
				$result = __('Wed', 'vikrentcar');
				break;
			case 'VRTHU':
				$result = __('Thu', 'vikrentcar');
				break;
			case 'VRFRI':
				$result = __('Fri', 'vikrentcar');
				break;
			case 'VRSAT':
				$result = __('Sat', 'vikrentcar');
				break;
			case 'VRLEGFREE':
				$result = __('Available', 'vikrentcar');
				break;
			case 'VRLEGWARNING':
				$result = __('Partially Reserved', 'vikrentcar');
				break;
			case 'VRLEGBUSY':
				$result = __('Not Available', 'vikrentcar');
				break;
			case 'VRCBOOKTHISCAR':
				$result = __('Book Now', 'vikrentcar');
				break;
			case 'VRCSELECTPDDATES':
				$result = __('Select the Dates for Pick Up and Drop Off', 'vikrentcar');
				break;
			case 'VRCDETAILCNOTAVAIL':
				$result = __('is not available for the selected days. Please try with different dates', 'vikrentcar');
				break;
			case 'VRINVALIDLOCATIONS':
				$result = __('Pickup and Drop Off is not available for those locations', 'vikrentcar');
				break;
			case 'VRREGSIGNUP':
				$result = __('Sign Up', 'vikrentcar');
				break;
			case 'VRREGNAME':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRREGLNAME':
				$result = __('Last Name', 'vikrentcar');
				break;
			case 'VRREGEMAIL':
				$result = __('e-Mail', 'vikrentcar');
				break;
			case 'VRREGUNAME':
				$result = __('Username', 'vikrentcar');
				break;
			case 'VRREGPWD':
				$result = __('Password', 'vikrentcar');
				break;
			case 'VRREGCONFIRMPWD':
				$result = __('Confirm Password', 'vikrentcar');
				break;
			case 'VRREGSIGNUPBTN':
				$result = __('Sign Up', 'vikrentcar');
				break;
			case 'VRREGSIGNIN':
				$result = __('Login', 'vikrentcar');
				break;
			case 'VRREGSIGNINBTN':
				$result = __('Login', 'vikrentcar');
				break;
			case 'VRCREGERRINSDATA':
				$result = __('Please fill in all the registration fields', 'vikrentcar');
				break;
			case 'VRCREGERRSAVING':
				$result = __('Error while creating an account, please try again', 'vikrentcar');
				break;
			case 'VRCLOCATIONSMAP':
				$result = __('View Locations Map', 'vikrentcar');
				break;
			case 'VRCHOUR':
				$result = __('Hour', 'vikrentcar');
				break;
			case 'VRCHOURS':
				$result = __('Hours', 'vikrentcar');
				break;
			case 'VRCSEPDRIVERD':
				$result = __('Driver Information', 'vikrentcar');
				break;
			case 'VRCORDERNUMBER':
				$result = __('Order Number', 'vikrentcar');
				break;
			case 'VRCORDERDETAILS':
				$result = __('Order Details', 'vikrentcar');
				break;
			case 'VRCJQCALDONE':
				$result = __('Done', 'vikrentcar');
				break;
			case 'VRCJQCALPREV':
				$result = __('Prev', 'vikrentcar');
				break;
			case 'VRCJQCALNEXT':
				$result = __('Next', 'vikrentcar');
				break;
			case 'VRCJQCALTODAY':
				$result = __('Today', 'vikrentcar');
				break;
			case 'VRCJQCALSUN':
				$result = __('Sunday', 'vikrentcar');
				break;
			case 'VRCJQCALMON':
				$result = __('Monday', 'vikrentcar');
				break;
			case 'VRCJQCALTUE':
				$result = __('Tuesday', 'vikrentcar');
				break;
			case 'VRCJQCALWED':
				$result = __('Wednesday', 'vikrentcar');
				break;
			case 'VRCJQCALTHU':
				$result = __('Thursday', 'vikrentcar');
				break;
			case 'VRCJQCALFRI':
				$result = __('Friday', 'vikrentcar');
				break;
			case 'VRCJQCALSAT':
				$result = __('Saturday', 'vikrentcar');
				break;
			case 'VRCJQCALWKHEADER':
				$result = __('Wk', 'vikrentcar');
				break;
			case 'VRCTOTPAYMENTINVALID':
				$result = __('Invalid Amount Paid', 'vikrentcar');
				break;
			case 'VRCTOTPAYMENTINVALIDTXT':
				$result = __('A payment for the order %s has been received. The total amount received is %s instead of %s.', 'vikrentcar');
				break;
			case 'VRCLOCLISTLOCOPENTIME':
				$result = __('Opening Time', 'vikrentcar');
				break;
			case 'VRCHAVEACOUPON':
				$result = __('Enter here your coupon code', 'vikrentcar');
				break;
			case 'VRCSUBMITCOUPON':
				$result = __('Apply', 'vikrentcar');
				break;
			case 'VRCCOUPONNOTFOUND':
				$result = __('Error, Coupon not found', 'vikrentcar');
				break;
			case 'VRCCOUPONINVDATES':
				$result = __('The Coupon is not valid for these rental dates', 'vikrentcar');
				break;
			case 'VRCCOUPONINVCAR':
				$result = __('The Coupon is not valid for this vehicle', 'vikrentcar');
				break;
			case 'VRCCOUPONINVMINTOTORD':
				$result = __('The Order Total is not enough for this Coupon', 'vikrentcar');
				break;
			case 'VRCCOUPON':
				$result = __('Coupon', 'vikrentcar');
				break;
			case 'VRCNEWTOTAL':
				$result = __('Total', 'vikrentcar');
				break;
			case 'VRCCCREDITCARDNUMBER':
				$result = __('Credit Card Number', 'vikrentcar');
				break;
			case 'VRCCVALIDTHROUGH':
				$result = __('Valid Through', 'vikrentcar');
				break;
			case 'VRCCCVV':
				$result = __('CVV', 'vikrentcar');
				break;
			case 'VRCCFIRSTNAME':
				$result = __('First Name', 'vikrentcar');
				break;
			case 'VRCCLASTNAME':
				$result = __('Last Name', 'vikrentcar');
				break;
			case 'VRCCBILLINGINFO':
				$result = __('Billing Information', 'vikrentcar');
				break;
			case 'VRCCCOMPANY':
				$result = __('Company', 'vikrentcar');
				break;
			case 'VRCCADDRESS':
				$result = __('Address', 'vikrentcar');
				break;
			case 'VRCCCITY':
				$result = __('City', 'vikrentcar');
				break;
			case 'VRCCSTATEPROVINCE':
				$result = __('State/Province', 'vikrentcar');
				break;
			case 'VRCCZIP':
				$result = __('ZIP Code', 'vikrentcar');
				break;
			case 'VRCCCOUNTRY':
				$result = __('Country', 'vikrentcar');
				break;
			case 'VRCCPHONE':
				$result = __('Phone', 'vikrentcar');
				break;
			case 'VRCCEMAIL':
				$result = __('eMail', 'vikrentcar');
				break;
			case 'VRCCPROCESSPAY':
				$result = __('Process and Pay', 'vikrentcar');
				break;
			case 'VRCCPROCESSING':
				$result = __('Processing...', 'vikrentcar');
				break;
			case 'VRCCOFFLINECCMESSAGE':
				$result = __('Please provide your Credit Card information. Your card will not be charged and the information will be securely kept by us.', 'vikrentcar');
				break;
			case 'VROFFLINECCSEND':
				$result = __('Submit Credit Card Information', 'vikrentcar');
				break;
			case 'VROFFLINECCSENT':
				$result = __('Processing...', 'vikrentcar');
				break;
			case 'VROFFCCMAILSUBJECT':
				$result = __('Credit Card Information Received', 'vikrentcar');
				break;
			case 'VROFFCCTOTALTOPAY':
				$result = __('Total to Pay', 'vikrentcar');
				break;
			case 'VRCLOCDAYCLOSED':
				$result = __('The location is closed on this day', 'vikrentcar');
				break;
			case 'VRCERRLOCATIONCLOSEDON':
				$result = __('Error, the location %s is closed on the %s. Please select a different date', 'vikrentcar');
				break;
			case 'VRPICKINPAST':
				$result = __('Error, the Pickup date and time is in the past', 'vikrentcar');
				break;
			case 'VRCONFIGUSDATEFORMAT':
				$result = __('MM/DD/YYYY', 'vikrentcar');
				break;
			case 'VRCYOURRESERVATIONS':
				$result = __('Your Reservations', 'vikrentcar');
				break;
			case 'VRCUSERRESDATE':
				$result = __('Date', 'vikrentcar');
				break;
			case 'VRCUSERRESSTATUS':
				$result = __('Status', 'vikrentcar');
				break;
			case 'VRCNOUSERRESFOUND':
				$result = __('No reservations were found for this account', 'vikrentcar');
				break;
			case 'VRCONFIRMED':
				$result = __('Confirmed', 'vikrentcar');
				break;
			case 'VRSTANDBY':
				$result = __('Standby', 'vikrentcar');
				break;
			case 'VRCLOGINFIRST':
				$result = __('Please log in to access this page', 'vikrentcar');
				break;
			case 'VRCPRINTCONFORDER':
				$result = __('View Order for Printing', 'vikrentcar');
				break;
			case 'VRCORDERNUMBER':
				$result = __('Order Number', 'vikrentcar');
				break;
			case 'VRCREQUESTCANCMOD':
				$result = __('Cancellation/Modification Request', 'vikrentcar');
				break;
			case 'VRCREQUESTCANCMODOPENTEXT':
				$result = __('Click here to request a cancellation or modification of the order', 'vikrentcar');
				break;
			case 'VRCREQUESTCANCMODEMAIL':
				$result = __('e-Mail', 'vikrentcar');
				break;
			case 'VRCREQUESTCANCMODREASON':
				$result = __('Message', 'vikrentcar');
				break;
			case 'VRCREQUESTCANCMODSUBMIT':
				$result = __('Send Request', 'vikrentcar');
				break;
			case 'VRCCANCREQUESTEMAILSUBJ':
				$result = __('Order Cancellation-Modification Request', 'vikrentcar');
				break;
			case 'VRCCANCREQUESTEMAILHEAD':
				$result = __("A Cancellation-Modification Request has been sent by the customer for the order id %s.\nOrder details: %s", 'vikrentcar');
				break;
			case 'VRCCANCREQUESTMAILSENT':
				$result = __('Your request has been sent successfully. Please do not send it again', 'vikrentcar');
				break;
			case 'VRCPDFDAYS':
				$result = __('Days', 'vikrentcar');
				break;
			case 'VRCPDFNETPRICE':
				$result = __('Net Price', 'vikrentcar');
				break;
			case 'VRCPDFTAX':
				$result = __('Tax', 'vikrentcar');
				break;
			case 'VRCPDFTOTALPRICE':
				$result = __('Total Price', 'vikrentcar');
				break;
			case 'VRCAGREEMENTTITLE':
				$result = __('Contract/Agreement', 'vikrentcar');
				break;
			case 'VRCAGREEMENTSAMPLETEXT':
				$result = __('This agreement between %s %s and %s was made on the %s and is valid until the %s.', 'vikrentcar');
				break;
			case 'VRCAGREEMENTSAMPLETEXTMORE':
				$result = __('1. Condition of Premises<br/><br/>The lessor shall keep the premises in a good state of repair and fit for habitation during the tenancy and shall comply with any enactment respecting standards of health, safety or housing notwithstanding any state of non-repair that may have existed at the time the agreement was entered into.<br/><br/>2. Services<br/><br/>Where the lessor provides or pays for a service or facility to the lessee that is reasonably related to the lessee\'s continued use and enjoyment of the premises, such as heat, water, electric power, gas, appliances, garbage collection, sewers or elevators, the lessor shall not discontinue providing or paying for that service to the lessee without permission from the Director.<br/><br/>3. Good Behaviour<br/><br/>The lessee and any person admitted to the premises by the lessee shall conduct themselves in such a manner as not to interfere with the possession, occupancy or quiet enjoyment of other lessees.<br/><br/>4. Obligation of the Lessee<br/><br/>The lessee shall be responsible for the ordinary cleanliness of the interior of the premises and for the repair of damage caused by any willful or negligent act of the lessee or of any person whom the lessee permits on the premises, but not for damage caused by normal wear and tear.', 'vikrentcar');
				break;
			case 'VRCDOWNLOADPDF':
				$result = __('Download PDF', 'vikrentcar');
				break;
			case 'VRCAMOUNTPAID':
				$result = __('Amount Paid', 'vikrentcar');
				break;
			case 'VRCINVALIDCONFNUMB':
				$result = __('Invalid Confirmation Number, unable to find your order', 'vikrentcar');
				break;
			case 'VRCRESERVATIONSLOGIN':
				$result = __('Log in to see your orders', 'vikrentcar');
				break;
			case 'VRCCONFNUMBERLBL':
				$result = __('Confirmation Number or PIN Code', 'vikrentcar');
				break;
			case 'VRCCONFNUMBERSEARCHBTN':
				$result = __('Search Order', 'vikrentcar');
				break;
			case 'VRCCONFIRMATIONNUMBER':
				$result = __('Confirmation Number', 'vikrentcar');
				break;
			case 'VRCPERDAYCOST':
				$result = __('per Day', 'vikrentcar');
				break;
			case 'VRCERRCURCONVNODATA':
				$result = __('Insufficient data received for converting the currency', 'vikrentcar');
				break;
			case 'VRCERRCURCONVINVALIDDATA':
				$result = __('Invalid data received for converting the currency', 'vikrentcar');
				break;
			case 'VRCSENTVIAMAIL':
				$result = __('Sent via eMail', 'vikrentcar');
				break;
			case 'VRCNOPROMOTIONSFOUND':
				$result = __('No active promotions found', 'vikrentcar');
				break;
			case 'VRCPROMOPERCENTDISCOUNT':
				$result = __('Off', 'vikrentcar');
				break;
			case 'VRCPROMOFIXEDDISCOUNT':
				$result = __('Off per Day', 'vikrentcar');
				break;
			case 'VRCPROMORENTFROM':
				$result = __('From', 'vikrentcar');
				break;
			case 'VRCPROMORENTTO':
				$result = __('To', 'vikrentcar');
				break;
			case 'VRCPROMOVALIDUNTIL':
				$result = __('Valid until', 'vikrentcar');
				break;
			case 'VRCPROMOCARBOOKNOW':
				$result = __('Book it Now', 'vikrentcar');
				break;
			case 'VRCOOHFEETOPAY':
				$result = __('Out of Hours Fee<br/>(%s)', 'vikrentcar');
				break;
			case 'VRCOOHFEEAMOUNT':
				$result = __('Out of Hours Fee', 'vikrentcar');
				break;
			case 'VRCDEFAULTDISTFEATUREONE':
				$result = __('License Plate', 'vikrentcar');
				break;
			case 'VRCDEFAULTDISTFEATURETWO':
				$result = __('Mileage', 'vikrentcar');
				break;
			case 'VRCDEFAULTDISTFEATURETHREE':
				$result = __('Next Service', 'vikrentcar');
				break;
			case 'VRCICSEXPSUMMARY':
				$result = __('Rental @ %s', 'vikrentcar');
				break;
			case 'VRCSEARCHBUTTON':
				$result = __('Search', 'vikrentcar');
				break;
			case 'VRCTOTALREMAINING':
				$result = __('Remaining Balance', 'vikrentcar');
				break;
			case 'VRCERRPICKPASSED':
				$result = __('Pick up for today no longer available at this time, please select a different Pick up date and time', 'vikrentcar');
				break;
			case 'VRCRENTCUSTRATEPLAN':
				$result = __('Rental Cost', 'vikrentcar');
				break;
			case 'VRCANCELLED':
				$result = __('Cancelled', 'vikrentcar');
				break;
			case 'VRERRORMINDAYSADV':
				$result = __('Error, minimum days in advance for pick up is %d days', 'vikrentcar');
				break;
			case 'VRCAVAILSINGLEDAY':
				$result = __('Hourly Availability for the day %s', 'vikrentcar');
				break;
			case 'VRCLEGH':
				$result = __('H', 'vikrentcar');
				break;
			case 'VRLEGBUSYCHECKH':
				$result = __('Not Available (for the whole day, check hourly availability)', 'vikrentcar');
				break;
			case 'ORDER_TERMSCONDITIONS':
				$result = __('I agree to the terms and conditions', 'vikrentcar');
				break;
			case 'VRYES':
				$result = __('Yes', 'vikrentcar');
				break;
			case 'VRNO':
				$result = __('No', 'vikrentcar');
				break;
			case 'VRRETURNINGCUSTOMER':
				$result = __('Returning Customer?', 'vikrentcar');
				break;
			case 'VRENTERPINCODE':
				$result = __('Please enter your PIN Code', 'vikrentcar');
				break;
			case 'VRAPPLYPINCODE':
				$result = __('Apply', 'vikrentcar');
				break;
			case 'VRWELCOMEBACK':
				$result = __('Welcome back', 'vikrentcar');
				break;
			case 'VRINVALIDPINCODE':
				$result = __('Invalid PIN Code. Please try again or just enter your information below', 'vikrentcar');
				break;
			case 'VRYOURPIN':
				$result = __('PIN Code', 'vikrentcar');
				break;
			case 'VRSTEPDATES':
				$result = __('Dates', 'vikrentcar');
				break;
			case 'VRSTEPCARSELECTION':
				$result = __('Cars', 'vikrentcar');
				break;
			case 'VRSTEPOPTIONS':
				$result = __('Options', 'vikrentcar');
				break;
			case 'VRSTEPCONFIRM':
				$result = __('Book', 'vikrentcar');
				break;
			case 'VRWEEKDAYZERO':
				$result = __('Sunday', 'vikrentcar');
				break;
			case 'VRWEEKDAYONE':
				$result = __('Monday', 'vikrentcar');
				break;
			case 'VRWEEKDAYTWO':
				$result = __('Tuesday', 'vikrentcar');
				break;
			case 'VRWEEKDAYTHREE':
				$result = __('Wednesday', 'vikrentcar');
				break;
			case 'VRWEEKDAYFOUR':
				$result = __('Thursday', 'vikrentcar');
				break;
			case 'VRWEEKDAYFIVE':
				$result = __('Friday', 'vikrentcar');
				break;
			case 'VRWEEKDAYSIX':
				$result = __('Saturday', 'vikrentcar');
				break;
			case 'VRRESTRERRWDAYARRIVAL':
				$result = __('Error, the pick up day in %s must be on a %s. Please try again.', 'vikrentcar');
				break;
			case 'VRRESTRERRMAXLOSEXCEEDED':
				$result = __('Error, the Maximum Num of Days in %s is %d. Please try again.', 'vikrentcar');
				break;
			case 'VRRESTRERRMINLOSEXCEEDED':
				$result = __('Error, the Minimum Num of Days in %s is %d. Please try again.', 'vikrentcar');
				break;
			case 'VRRESTRERRMULTIPLYMINLOS':
				$result = __('Error, the Num of Days allowed in %s must be a multiple of %d. Please try again.', 'vikrentcar');
				break;
			case 'VRRESTRERRWDAYCOMBO':
				$result = __('Error, the drop off day in %s must be on a %s if picking up on a %s', 'vikrentcar');
				break;
			case 'VRRESTRERRWDAYARRIVALRANGE':
				$result = __('Error, the pick up day in these dates must be on a %s. Please try again.', 'vikrentcar');
				break;
			case 'VRRESTRERRMAXLOSEXCEEDEDRANGE':
				$result = __('Error, the Maximum Num of Days in these dates is %d. Please try again.', 'vikrentcar');
				break;
			case 'VRRESTRERRMINLOSEXCEEDEDRANGE':
				$result = __('Error, the Minimum Num of Days in these dates is %d. Please try again.', 'vikrentcar');
				break;
			case 'VRRESTRERRMULTIPLYMINLOSRANGE':
				$result = __('Error, the Num of Days allowed in these dates must be a multiple of %d. Please try again.', 'vikrentcar');
				break;
			case 'VRRESTRERRWDAYCOMBORANGE':
				$result = __('Error, the drop off day in these dates must be on a %s if picking up on a %s', 'vikrentcar');
				break;
			case 'VRRESTRTIPWDAYARRIVAL':
				$result = __('Some results were excluded: try selecting the pick up day in %s as a %s.', 'vikrentcar');
				break;
			case 'VRRESTRTIPMAXLOSEXCEEDED':
				$result = __('Some results were excluded: the Maximum Num of Days in %s is %d.', 'vikrentcar');
				break;
			case 'VRRESTRTIPMINLOSEXCEEDED':
				$result = __('Some results were excluded: the Minimum Num of Days in %s is %d.', 'vikrentcar');
				break;
			case 'VRRESTRTIPMULTIPLYMINLOS':
				$result = __('Some results were excluded: the Num of Days allowed in %s should be a multiple of %d.', 'vikrentcar');
				break;
			case 'VRRESTRTIPWDAYCOMBO':
				$result = __('Some results were excluded: the drop off day in %s should be on a %s if picking up on a %s', 'vikrentcar');
				break;
			case 'VRRESTRTIPWDAYARRIVALRANGE':
				$result = __('Some results were excluded: the pick up day in these dates should be on a %s.', 'vikrentcar');
				break;
			case 'VRRESTRTIPMAXLOSEXCEEDEDRANGE':
				$result = __('Some results were excluded: the Maximum Num of Days in these dates is %d.', 'vikrentcar');
				break;
			case 'VRRESTRTIPMINLOSEXCEEDEDRANGE':
				$result = __('Some results were excluded: the Minimum Num of Days in these dates is %d.', 'vikrentcar');
				break;
			case 'VRRESTRTIPMULTIPLYMINLOSRANGE':
				$result = __('Some results were excluded: the Num of Days allowed in these dates should be a multiple of %d.', 'vikrentcar');
				break;
			case 'VRRESTRTIPWDAYCOMBORANGE':
				$result = __('Some results were excluded: the drop off day in these dates should be on a %s if picking up on a %s', 'vikrentcar');
				break;
			case 'VRRESTRERRWDAYCTAMONTH':
				$result = __('Error, pick ups on %s are not permitted on %s', 'vikrentcar');
				break;
			case 'VRRESTRERRWDAYCTDMONTH':
				$result = __('Error, drop offs on %s are not permitted on %s', 'vikrentcar');
				break;
			case 'VRRESTRERRWDAYCTARANGE':
				$result = __('Error, pick ups on %s are not permitted on the selected dates', 'vikrentcar');
				break;
			case 'VRRESTRERRWDAYCTDRANGE':
				$result = __('Error, drop offs on %s are not permitted on the selected dates', 'vikrentcar');
				break;
			case 'VRCCARREQINFOBTN':
				$result = __('Request Information', 'vikrentcar');
				break;
			case 'VRCCARREQINFOTITLE':
				$result = __('Request Information for %s', 'vikrentcar');
				break;
			case 'VRCCARREQINFONAME':
				$result = __('Full Name', 'vikrentcar');
				break;
			case 'VRCCARREQINFOEMAIL':
				$result = __('e-Mail', 'vikrentcar');
				break;
			case 'VRCCARREQINFOMESS':
				$result = __('Message', 'vikrentcar');
				break;
			case 'VRCCARREQINFOSEND':
				$result = __('Send Request', 'vikrentcar');
				break;
			case 'VRCCARREQINFOMISSFIELD':
				$result = __('Please fill in all fields in order to request information.', 'vikrentcar');
				break;
			case 'VRCCARREQINFOSUBJ':
				$result = __('Information Request for %s', 'vikrentcar');
				break;
			case 'VRCCARREQINFOSENTOK':
				$result = __('Information Request Successfully Sent!', 'vikrentcar');
				break;
			case 'VRCOFFCCINVCC':
				$result = __('Invalid Credit Card information received, please try again.', 'vikrentcar');
				break;
			case 'VRCOFFCCINVPAY':
				$result = __('The payment was not verified, please try again.', 'vikrentcar');
				break;
			case 'VRCOFFCCTHANKS':
				$result = __('Thank you! Your credit card details have been received correctly.', 'vikrentcar');
				break;
			case 'VRCBOOKNOLONGERPAYABLE':
				$result = __('Error, this order has a pick up date in the past and it was not confirmed on time. The order is now Cancelled.', 'vikrentcar');
				break;
			case 'VRCORDERID':
				$result = __('Order ID', 'vikrentcar');
				break;
			case 'VRCCCREDITCARDTYPE':
				$result = __('Card Type', 'vikrentcar');
				break;
			case 'VRCCCOFFLINECCTOGGLEFORM':
				$result = __('Hide/Show Credit Card Details Submission Form', 'vikrentcar');
				break;
			case 'VRCAVAILABILITYCALENDAR':
				$result = __('Availability Calendar', 'vikrentcar');
				break;
			case 'VRCMAILSUBJECT':
				$result = __('Your reservation at %s', 'vikrentcar');
				break;
			case 'VRCNEWORDERID':
				$result = __('New Order #%s', 'vikrentcar');
				break;
			case 'VRC_CLOSEST_SEARCHSOLUTIONS':
				$result = __('Closest booking solutions', 'vikrentcar');
				break;
			case 'VRC_YOURCONF_ORDER_AT':
				$result = __('Your confirmed order at %s', 'vikrentcar');
				break;
			case 'VRC_YOURORDER_PENDING':
				$result = __('Your order is pending confirmation', 'vikrentcar');
				break;
			case 'VRC_YOURORDER_CANCELLED':
				$result = __('Your order is cancelled', 'vikrentcar');
				break;
			case 'VRC_UPLOAD_DOCUMENTS':
				$result = __('Upload documents', 'vikrentcar');
				break;
			case 'VRC_UPLOAD_FAILED':
				$result = __('Uploading failed. Please try again', 'vikrentcar');
				break;
			case 'VRC_REMOVEF_CONFIRM':
				$result = __('Do you want to remove the selected file?', 'vikrentcar');
				break;
			case 'VRC_PRECHECKIN_TOAST_HELP':
				$result = __('Click the save button at the bottom of the page when you are done.', 'vikrentcar');
				break;
			case 'VRC_PRECHECKIN_DISCLAIMER':
				$result = __('Personal data is collected and processed in accordance with the Privacy Policy accepted at the time of booking.', 'vikrentcar');
				break;
			case 'VRC_ADD':
				$result = __('Add', 'vikrentcar');
				break;
			case 'VRC_SUBMIT_DOCSUPLOAD_TNKS':
				$result = __('Information saved successfully, thank you!', 'vikrentcar');
				break;
			case 'VRC_DEBUG_RULE_CONDTEXT':
				$result = __('[Rule %s was not compliant. Special tag %s was not applied.]', 'vikrentcar');
				break;
			case 'VRCALERTFILLINALLF':
				$result = __('Please fill in or accept all the required fields', 'vikrentcar');
				break;
			case 'VRC_LOC_WILL_OPEN_TIME':
				$result = __('The selected location will open at %s', 'vikrentcar');
				break;
			case 'VRC_LOC_WILL_CLOSE_TIME':
				$result = __('The selected location will close at %s', 'vikrentcar');
				break;
			case 'VRC_PICKLOC_IS_ON_BREAK_TIME_FROM_TO':
				$result = __('The selected location for pickup is on break from %s to %s', 'vikrentcar');
				break;
			case 'VRC_DROPLOC_IS_ON_BREAK_TIME_FROM_TO':
				$result = __('The selected location for drop off is on break from %s to %s', 'vikrentcar');
				break;
			case 'VRC_LOCATION_OPEN_FROM_TO':
				$result = __('The agency %s is open from %s to %s', 'vikrentcar');
				break;
			case 'VRC_LOCATION_BREAK_FROM_TO':
				$result = __('The agency %s is on break from %s to %s', 'vikrentcar');
				break;
		}

		return $result;
	}
}
