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
 * Switcher class to translate the VikRentCar plugin admin languages.
 *
 * @since 	1.0
 */
class VikRentCarLanguageAdmin implements JLanguageHandler
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
			/**
			 * System definitions for toolbar
			 */
			case 'JSEARCH_TOOLS':
				$result = __('Search Tools', 'vikrentcar');
				break;

			/**
			 * Definitions
			 */
			case 'VRSAVE':
				$result = __('Save', 'vikrentcar');
				break;
			case 'VRANNULLA':
				$result = __('Cancel', 'vikrentcar');
				break;
			case 'VRELIMINA':
				$result = __('Delete', 'vikrentcar');
				break;
			case 'VRBACK':
				$result = __('Back', 'vikrentcar');
				break;
			case 'VRCONFIRMED':
				$result = __('Confirmed', 'vikrentcar');
				break;
			case 'VRSTANDBY':
				$result = __('Standby', 'vikrentcar');
				break;
			case 'VRLEFT':
				$result = __('Left', 'vikrentcar');
				break;
			case 'VRRIGHT':
				$result = __('Right', 'vikrentcar');
				break;
			case 'VRBOTTOMCENTER':
				$result = __('Bottom, Center', 'vikrentcar');
				break;
			case 'VRSTATUS':
				$result = __('Status', 'vikrentcar');
				break;
			case 'VRMAINDEAFULTTITLE':
				$result = __('Vik Rent Car - Cars List', 'vikrentcar');
				break;
			case 'VRMAINDEFAULTDEL':
				$result = __('Delete Car', 'vikrentcar');
				break;
			case 'VRMAINDEFAULTEDITC':
				$result = __('Edit Car', 'vikrentcar');
				break;
			case 'VRMAINDEFAULTEDITT':
				$result = __('Edit/View Fares', 'vikrentcar');
				break;
			case 'VRMAINDEFAULTCAL':
				$result = __('Cars Calendar', 'vikrentcar');
				break;
			case 'VRMAINDEFAULTNEW':
				$result = __('New Car', 'vikrentcar');
				break;
			case 'VRMAINPLACETITLE':
				$result = __('Vik Rent Car - Pickup/Drop Off Locations', 'vikrentcar');
				break;
			case 'VRMAINPLACEDEL':
				$result = __('Delete Location', 'vikrentcar');
				break;
			case 'VRMAINPLACEEDIT':
				$result = __('Edit Location', 'vikrentcar');
				break;
			case 'VRMAINPLACENEW':
				$result = __('New Location', 'vikrentcar');
				break;
			case 'VRMAINIVATITLE':
				$result = __('Vik Rent Car - Tax Rates List', 'vikrentcar');
				break;
			case 'VRMAINIVADEL':
				$result = __('Delete Tax Rate', 'vikrentcar');
				break;
			case 'VRMAINIVAEDIT':
				$result = __('Edit Tax Rate', 'vikrentcar');
				break;
			case 'VRMAINIVANEW':
				$result = __('New Tax Rate', 'vikrentcar');
				break;
			case 'VRMAINCATTITLE':
				$result = __('Vik Rent Car - Categories List', 'vikrentcar');
				break;
			case 'VRMAINCATDEL':
				$result = __('Delete Categories', 'vikrentcar');
				break;
			case 'VRMAINCATEDIT':
				$result = __('Edit Category', 'vikrentcar');
				break;
			case 'VRMAINCATNEW':
				$result = __('New Category', 'vikrentcar');
				break;
			case 'VRMAINCARATTITLE':
				$result = __('Vik Rent Car - Characteristics List', 'vikrentcar');
				break;
			case 'VRMAINCARATDEL':
				$result = __('Delete Characteristics', 'vikrentcar');
				break;
			case 'VRMAINCARATEDIT':
				$result = __('Edit Characteristic', 'vikrentcar');
				break;
			case 'VRMAINCARATNEW':
				$result = __('New Characteristic', 'vikrentcar');
				break;
			case 'VRMAINOPTTITLE':
				$result = __('Vik Rent Car - Options List', 'vikrentcar');
				break;
			case 'VRMAINOPTDEL':
				$result = __('Delete Options', 'vikrentcar');
				break;
			case 'VRMAINOPTEDIT':
				$result = __('Edit Option', 'vikrentcar');
				break;
			case 'VRMAINOPTNEW':
				$result = __('New Option', 'vikrentcar');
				break;
			case 'VRMAINPRICETITLE':
				$result = __('Vik Rent Car - Types of Price', 'vikrentcar');
				break;
			case 'VRMAINPRICEDEL':
				$result = __('Delete Prices', 'vikrentcar');
				break;
			case 'VRMAINPRICEEDIT':
				$result = __('Edit Price', 'vikrentcar');
				break;
			case 'VRMAINPRICENEW':
				$result = __('New Price', 'vikrentcar');
				break;
			case 'VRMAINPLACETITLENEW':
				$result = __('Vik Rent Car - New Pickup/Drop Off Location', 'vikrentcar');
				break;
			case 'VRMAINPLACETITLEEDIT':
				$result = __('Vik Rent Car - Edit Location', 'vikrentcar');
				break;
			case 'VRMAINIVATITLENEW':
				$result = __('Vik Rent Car - New Tax Rate', 'vikrentcar');
				break;
			case 'VRMAINIVATITLEEDIT':
				$result = __('Vik Rent Car - Edit Tax Rate', 'vikrentcar');
				break;
			case 'VRMAINPRICETITLENEW':
				$result = __('Vik Rent Car - New Price', 'vikrentcar');
				break;
			case 'VRMAINPRICETITLEEDIT':
				$result = __('Vik Rent Car - Edit Price', 'vikrentcar');
				break;
			case 'VRMAINCATTITLENEW':
				$result = __('Vik Rent Car - New Category', 'vikrentcar');
				break;
			case 'VRMAINCATTITLEEDIT':
				$result = __('Vik Rent Car - Edit Category', 'vikrentcar');
				break;
			case 'VRMAINCARATTITLENEW':
				$result = __('Vik Rent Car - New Characteristic', 'vikrentcar');
				break;
			case 'VRMAINCARATTITLEEDIT':
				$result = __('Vik Rent Car - Edit Characteristic', 'vikrentcar');
				break;
			case 'VRMAINOPTTITLENEW':
				$result = __('Vik Rent Car - New Option', 'vikrentcar');
				break;
			case 'VRMAINOPTTITLEEDIT':
				$result = __('Vik Rent Car - Edit Option', 'vikrentcar');
				break;
			case 'VRMAINCARTITLENEW':
				$result = __('Vik Rent Car - New Car', 'vikrentcar');
				break;
			case 'VRMAINCARTITLEEDIT':
				$result = __('Vik Rent Car - Edit Car', 'vikrentcar');
				break;
			case 'VRMAINTARIFFETITLE':
				$result = __('Vik Rent Car - Cars Fares', 'vikrentcar');
				break;
			case 'VRMAINTARIFFEDEL':
				$result = __('Delete Fares', 'vikrentcar');
				break;
			case 'VRMAINTARIFFEBACK':
				$result = __('Quit Inserting', 'vikrentcar');
				break;
			case 'VRMAINORDERTITLE':
				$result = __('Vik Rent Car - Rental Orders', 'vikrentcar');
				break;
			case 'VRMAINORDERDEL':
				$result = __('Delete Orders', 'vikrentcar');
				break;
			case 'VRMAINORDEREDIT':
				$result = __('View Order', 'vikrentcar');
				break;
			case 'VRMAINOLDORDERTITLE':
				$result = __('Vik Rent Car - Removed Rental Orders', 'vikrentcar');
				break;
			case 'VRMAINOLDORDERDEL':
				$result = __('Permanently Delete', 'vikrentcar');
				break;
			case 'VRMAINOLDORDEREDIT':
				$result = __('View Removed Order', 'vikrentcar');
				break;
			case 'VRMAINORDERTITLEEDIT':
				$result = __('Vik Rent Car - Rental Order', 'vikrentcar');
				break;
			case 'VRMAINOLDORDERTITLEEDIT':
				$result = __('Vik Rent Car - Removed Rental Order', 'vikrentcar');
				break;
			case 'VRMAINCALTITLE':
				$result = __('Vik Rent Car - Booking Calendar', 'vikrentcar');
				break;
			case 'VRMAINCHOOSEBUSY':
				$result = __('Reservations for', 'vikrentcar');
				break;
			case 'VRMAINEBUSYTITLE':
				$result = __('Vik Rent Car - Edit Reservation', 'vikrentcar');
				break;
			case 'VRMAINEBUSYDEL':
				$result = __('Delete Reservation', 'vikrentcar');
				break;
			case 'VRMAINCONFIGTITLE':
				$result = __('Vik Rent Car - Global Configuration', 'vikrentcar');
				break;
			case 'VRMAINPAYMENTSTITLE':
				$result = __('Vik Rent Car - Payment Methods', 'vikrentcar');
				break;
			case 'VRMAINPAYMENTSDEL':
				$result = __('Remove', 'vikrentcar');
				break;
			case 'VRMAINPAYMENTSEDIT':
				$result = __('Edit', 'vikrentcar');
				break;
			case 'VRMAINPAYMENTSNEW':
				$result = __('New', 'vikrentcar');
				break;
			case 'VRMAINPAYMENTTITLENEW':
				$result = __('Vik Rent Car - New Payment Method', 'vikrentcar');
				break;
			case 'VRMAINPAYMENTTITLEEDIT':
				$result = __('Vik Rent Car - Edit Payment Method', 'vikrentcar');
				break;
			case 'VRMAINOVERVIEWTITLE':
				$result = __('Vik Rent Car - Availability Overview', 'vikrentcar');
				break;
			case 'VRPANELONE':
				$result = __('Shop and Rentals', 'vikrentcar');
				break;
			case 'VRPANELTWO':
				$result = __('Prices and Payments', 'vikrentcar');
				break;
			case 'VRPANELTHREE':
				$result = __('Views and Layout', 'vikrentcar');
				break;
			case 'VRPANELFOUR':
				$result = __('Orders and Company', 'vikrentcar');
				break;
			case 'VRMESSDELBUSY':
				$result = __('Reservation Deleted', 'vikrentcar');
				break;
			case 'VRCARNOTCONSTO':
				$result = __('to the', 'vikrentcar');
				break;
			case 'VRCARNOTRIT':
				$result = __('Car is not available from the', 'vikrentcar');
				break;
			case 'ERRPREV':
				$result = __('Drop Off time is previous than Pickup', 'vikrentcar');
				break;
			case 'ERRCARLOCKED':
				$result = __('The car is not available in the days requested. The Car is currently blocked by a pending order waiting to be confirmed.', 'vikrentcar');
				break;
			case 'RESUPDATED':
				$result = __('Reservation Updated', 'vikrentcar');
				break;
			case 'VRSETTINGSAVED':
				$result = __('Settings Saved! Click the Renew Session button to immediately apply the changes', 'vikrentcar');
				break;
			case 'VRPAYMENTSAVED':
				$result = __('Payment Method Saved', 'vikrentcar');
				break;
			case 'ERRINVFILEPAYMENT':
				$result = __('File Class is already used in another payment method', 'vikrentcar');
				break;
			case 'VRPAYMENTUPDATED':
				$result = __('Payment Method Updated', 'vikrentcar');
				break;
			case 'VRRENTALORD':
				$result = __('Rental Order', 'vikrentcar');
				break;
			case 'VRCOMPLETED':
				$result = __('Completed', 'vikrentcar');
				break;
			case 'ERRCONFORDERCARNA':
				$result = __('Error, the Car is no longer available. Order was set to Confirmed', 'vikrentcar');
				break;
			case 'VRORDERSETASCONF':
				$result = __('Order successfully set to Confirmed', 'vikrentcar');
				break;
			case 'VROVERVIEWNOCARS':
				$result = __('No Vehicle Found', 'vikrentcar');
				break;
			case 'VRINSERTFEE':
				$result = __('Insert Fares', 'vikrentcar');
				break;
			case 'VRMSGONE':
				$result = __('No Prices Found, Insert Prices from', 'vikrentcar');
				break;
			case 'VRHERE':
				$result = __('Here', 'vikrentcar');
				break;
			case 'VRMSGTWO':
				$result = __('Days Field is empty', 'vikrentcar');
				break;
			case 'VRDAYS':
				$result = __('Days', 'vikrentcar');
				break;
			case 'VRDAYSFROM':
				$result = __('from', 'vikrentcar');
				break;
			case 'VRDAYSTO':
				$result = __('to', 'vikrentcar');
				break;
			case 'VRDAILYPRICES':
				$result = __('Daily Price(s)', 'vikrentcar');
				break;
			case 'VRDAY':
				$result = __('Day', 'vikrentcar');
				break;
			case 'VRINSERT':
				$result = __('Insert', 'vikrentcar');
				break;
			case 'VRMODRES':
				$result = __('Edit Reservation', 'vikrentcar');
				break;
			case 'VRQUICKBOOK':
				$result = __('Quick Reservation', 'vikrentcar');
				break;
			case 'VRBOOKMADE':
				$result = __('Reservation Saved', 'vikrentcar');
				break;
			case 'VRBOOKNOTMADE':
				$result = __('Unable to save the reservation, car not available', 'vikrentcar');
				break;
			case 'VRMSGTHREE':
				$result = __('Pickup Field is empty', 'vikrentcar');
				break;
			case 'VRMSGFOUR':
				$result = __('Drop Off Field is empty', 'vikrentcar');
				break;
			case 'VRDATEPICKUP':
				$result = __('Pickup Date and Time', 'vikrentcar');
				break;
			case 'VRAT':
				$result = __('At', 'vikrentcar');
				break;
			case 'VRDATERELEASE':
				$result = __('Drop Off Date and Time', 'vikrentcar');
				break;
			case 'VRCUSTINFO':
				$result = __('Customer Information', 'vikrentcar');
				break;
			case 'VRMAKERESERV':
				$result = __('Save Reservation', 'vikrentcar');
				break;
			case 'VRNOFUTURERES':
				$result = __('No Future Reservations', 'vikrentcar');
				break;
			case 'VRVIEW':
				$result = __('View Mode', 'vikrentcar');
				break;
			case 'VRTHREEMONTHS':
				$result = __('3 Months', 'vikrentcar');
				break;
			case 'VRSIXMONTHS':
				$result = __('6 Months', 'vikrentcar');
				break;
			case 'VRTWELVEMONTHS':
				$result = __('1 Year', 'vikrentcar');
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
			case 'VRPICKUPAT':
				$result = __('Pickup at', 'vikrentcar');
				break;
			case 'VRRELEASEAT':
				$result = __('Drop Off at', 'vikrentcar');
				break;
			case 'VRNOCARSFOUND':
				$result = __('No cars found', 'vikrentcar');
				break;
			case 'VRJSDELCAR':
				$result = __('Every selected Car will be removed with its own contents. Confirm', 'vikrentcar');
				break;
			case 'VRPVIEWCARONE':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRPVIEWCARTWO':
				$result = __('Category', 'vikrentcar');
				break;
			case 'VRPVIEWCARTHREE':
				$result = __('Characteristics', 'vikrentcar');
				break;
			case 'VRPVIEWCARFOUR':
				$result = __('Options', 'vikrentcar');
				break;
			case 'VRPVIEWCARFIVE':
				$result = __('Location', 'vikrentcar');
				break;
			case 'VRPVIEWCARSIX':
				$result = __('Available', 'vikrentcar');
				break;
			case 'VRPVIEWCARSEVEN':
				$result = __('Units', 'vikrentcar');
				break;
			case 'VRMAKENOTAVAIL':
				$result = __('Make Not Available', 'vikrentcar');
				break;
			case 'VRMAKEAVAIL':
				$result = __('Make Available', 'vikrentcar');
				break;
			case 'VRANYTHING':
				$result = __('Any', 'vikrentcar');
				break;
			case 'VRNOORDERSFOUND':
				$result = __('No orders found', 'vikrentcar');
				break;
			case 'VRJSDELORDER':
				$result = __('Every selected order will be removed with its reservation. Confirm', 'vikrentcar');
				break;
			case 'VRPVIEWORDERSONE':
				$result = __('Date', 'vikrentcar');
				break;
			case 'VRPVIEWORDERSTWO':
				$result = __('Customer Information', 'vikrentcar');
				break;
			case 'VRPVIEWORDERSTHREE':
				$result = __('Car', 'vikrentcar');
				break;
			case 'VRPVIEWORDERSFOUR':
				$result = __('Pickup', 'vikrentcar');
				break;
			case 'VRPVIEWORDERSFIVE':
				$result = __('Drop Off', 'vikrentcar');
				break;
			case 'VRPVIEWORDERSSIX':
				$result = __('Days', 'vikrentcar');
				break;
			case 'VRPVIEWORDERSSEVEN':
				$result = __('Total', 'vikrentcar');
				break;
			case 'VRPVIEWORDERSEIGHT':
				$result = __('Status', 'vikrentcar');
				break;
			case 'VRNOOLDORDERSFOUND':
				$result = __('No removed orders found', 'vikrentcar');
				break;
			case 'VRNOPLACESFOUND':
				$result = __('No Locations found', 'vikrentcar');
				break;
			case 'VRJSDELPLACES':
				$result = __('Remove every selected Location', 'vikrentcar');
				break;
			case 'VRPVIEWPLACESONE':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRNOIVAFOUND':
				$result = __('No Tax rates Found', 'vikrentcar');
				break;
			case 'VRJSDELIVA':
				$result = __('Remove every selected Tax Rate', 'vikrentcar');
				break;
			case 'VRPVIEWIVAONE':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRPVIEWIVATWO':
				$result = __('Tax Rate', 'vikrentcar');
				break;
			case 'VRNOCATEGORIESFOUND':
				$result = __('No Categories found', 'vikrentcar');
				break;
			case 'VRJSDELCATEGORIES':
				$result = __('Remove every selected Category', 'vikrentcar');
				break;
			case 'VRPVIEWCATEGORIESONE':
				$result = __('Category Name', 'vikrentcar');
				break;
			case 'VRNOCARATFOUND':
				$result = __('No Characteristics found', 'vikrentcar');
				break;
			case 'VRJSDELCARAT':
				$result = __('Remove every selected Characteristic', 'vikrentcar');
				break;
			case 'VRPVIEWCARATONE':
				$result = __('Characteristic Name', 'vikrentcar');
				break;
			case 'VRPVIEWCARATTWO':
				$result = __('Icon', 'vikrentcar');
				break;
			case 'VRPVIEWCARATTHREE':
				$result = __('Text', 'vikrentcar');
				break;
			case 'VRPVIEWCARATFOUR':
				$result = __('Text Alignment', 'vikrentcar');
				break;
			case 'VRNOOPTIONALSFOUND':
				$result = __('No Options found', 'vikrentcar');
				break;
			case 'VRJSDELOPTIONALS':
				$result = __('Remove every selected Option', 'vikrentcar');
				break;
			case 'VRPVIEWOPTIONALSONE':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRPVIEWOPTIONALSTWO':
				$result = __('Description', 'vikrentcar');
				break;
			case 'VRPVIEWOPTIONALSTHREE':
				$result = __('Price', 'vikrentcar');
				break;
			case 'VRPVIEWOPTIONALSFOUR':
				$result = __('Tax Rate', 'vikrentcar');
				break;
			case 'VRPVIEWOPTIONALSFIVE':
				$result = __('Per Day', 'vikrentcar');
				break;
			case 'VRPVIEWOPTIONALSSIX':
				$result = __('Allowed Quantity', 'vikrentcar');
				break;
			case 'VRPVIEWOPTIONALSSEVEN':
				$result = __('Image', 'vikrentcar');
				break;
			case 'VRPVIEWOPTIONALSEIGHT':
				$result = __('Maximum Cost', 'vikrentcar');
				break;
			case 'VRNOPRICESFOUND':
				$result = __('No Prices Found', 'vikrentcar');
				break;
			case 'VRJSDELPRICES':
				$result = __('Remove every selected Price ? Each Tax Rate with one of these prices will become null.', 'vikrentcar');
				break;
			case 'VRPVIEWPRICESONE':
				$result = __('Price Name', 'vikrentcar');
				break;
			case 'VRPVIEWPRICESTWO':
				$result = __('Price Attributes', 'vikrentcar');
				break;
			case 'VRPVIEWPRICESTHREE':
				$result = __('Tax Rate', 'vikrentcar');
				break;
			case 'VRJSDELBUSY':
				$result = __('Delete Reservation', 'vikrentcar');
				break;
			case 'VRPEDITBUSYONE':
				$result = __('Order\'s data not found', 'vikrentcar');
				break;
			case 'VRPEDITBUSYTWO':
				$result = __('Order date', 'vikrentcar');
				break;
			case 'VRPEDITBUSYTHREE':
				$result = __('Rental for', 'vikrentcar');
				break;
			case 'VRPEDITBUSYFOUR':
				$result = __('Pickup Date', 'vikrentcar');
				break;
			case 'VRPEDITBUSYFIVE':
				$result = __('At H:M', 'vikrentcar');
				break;
			case 'VRPEDITBUSYSIX':
				$result = __('Drop Off Date', 'vikrentcar');
				break;
			case 'VRPEDITBUSYSEVEN':
				$result = __('Prices', 'vikrentcar');
				break;
			case 'VRPEDITBUSYEIGHT':
				$result = __('Options', 'vikrentcar');
				break;
			case 'VRNEWPLACEONE':
				$result = __('Location Name', 'vikrentcar');
				break;
			case 'VREDITPLACEONE':
				$result = __('Location Name', 'vikrentcar');
				break;
			case 'VREDITORDERONE':
				$result = __('Order Date', 'vikrentcar');
				break;
			case 'VREDITORDERTWO':
				$result = __('Customer Info', 'vikrentcar');
				break;
			case 'VREDITORDERTHREE':
				$result = __('Car', 'vikrentcar');
				break;
			case 'VREDITORDERFOUR':
				$result = __('Days of Rental', 'vikrentcar');
				break;
			case 'VREDITORDERFIVE':
				$result = __('Pickup', 'vikrentcar');
				break;
			case 'VREDITORDERSIX':
				$result = __('Drop Off', 'vikrentcar');
				break;
			case 'VREDITORDERSEVEN':
				$result = __('Fare', 'vikrentcar');
				break;
			case 'VREDITORDEREIGHT':
				$result = __('Options', 'vikrentcar');
				break;
			case 'VREDITORDERNINE':
				$result = __('Total', 'vikrentcar');
				break;
			case 'VREDITORDERTEN':
				$result = __('Pickup/Drop Off Fee', 'vikrentcar');
				break;
			case 'VRNEWIVAONE':
				$result = __('Tax Rate Name', 'vikrentcar');
				break;
			case 'VRNEWIVATWO':
				$result = __('Tax Rate', 'vikrentcar');
				break;
			case 'VRNEWPRICEONE':
				$result = __('Price Name', 'vikrentcar');
				break;
			case 'VRNEWPRICETWO':
				$result = __('Price Attributes', 'vikrentcar');
				break;
			case 'VRNEWPRICETHREE':
				$result = __('Tax Rate', 'vikrentcar');
				break;
			case 'VRNEWCATONE':
				$result = __('Category Name', 'vikrentcar');
				break;
			case 'VRNEWCARATONE':
				$result = __('Characteristic Name', 'vikrentcar');
				break;
			case 'VRNEWCARATTWO':
				$result = __('Characteristic Icon', 'vikrentcar');
				break;
			case 'VRNEWCARATTHREE':
				$result = __('Text Next to Icon', 'vikrentcar');
				break;
			case 'VRNEWCARATFOUR':
				$result = __('Write Text on', 'vikrentcar');
				break;
			case 'VRNEWCARATFIVE':
				$result = __('the Left', 'vikrentcar');
				break;
			case 'VRNEWCARATSIX':
				$result = __('the Right', 'vikrentcar');
				break;
			case 'VRNEWCARATSEVEN':
				$result = __('Bottom, Center', 'vikrentcar');
				break;
			case 'VRNEWOPTONE':
				$result = __('Option Name', 'vikrentcar');
				break;
			case 'VRNEWOPTTWO':
				$result = __('Option Description', 'vikrentcar');
				break;
			case 'VRNEWOPTTHREE':
				$result = __('Option Price', 'vikrentcar');
				break;
			case 'VRNEWOPTFOUR':
				$result = __('Tax Rate', 'vikrentcar');
				break;
			case 'VRNEWOPTFIVE':
				$result = __('Daily Cost', 'vikrentcar');
				break;
			case 'VRNEWOPTSIX':
				$result = __('Selectable Quantity', 'vikrentcar');
				break;
			case 'VRNEWOPTSEVEN':
				$result = __('Option Image', 'vikrentcar');
				break;
			case 'VRNEWOPTEIGHT':
				$result = __('Maximum Cost', 'vikrentcar');
				break;
			case 'VRNEWOPTNINE':
				$result = __('Resize Image', 'vikrentcar');
				break;
			case 'VRNEWOPTTEN':
				$result = __('If Larger than', 'vikrentcar');
				break;
			case 'VRNEWCARONE':
				$result = __('Category', 'vikrentcar');
				break;
			case 'VRNEWCARTWO':
				$result = __('Pickup Locations', 'vikrentcar');
				break;
			case 'VRNEWCARTHREE':
				$result = __('Characteristics', 'vikrentcar');
				break;
			case 'VRNEWCARFOUR':
				$result = __('Options', 'vikrentcar');
				break;
			case 'VRNEWCARFIVE':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRNEWCARSIX':
				$result = __('Image', 'vikrentcar');
				break;
			case 'VRNEWCARSEVEN':
				$result = __('Description', 'vikrentcar');
				break;
			case 'VRNEWCAREIGHT':
				$result = __('Available', 'vikrentcar');
				break;
			case 'VRNEWCARNINE':
				$result = __('Total Units', 'vikrentcar');
				break;
			case 'VRNOTARFOUND':
				$result = __('No Fares found', 'vikrentcar');
				break;
			case 'VRJSDELTAR':
				$result = __('Remove every selected Fare', 'vikrentcar');
				break;
			case 'VRPVIEWTARONE':
				$result = __('Fare for days', 'vikrentcar');
				break;
			case 'VRPVIEWTARTWO':
				$result = __('Update Fares', 'vikrentcar');
				break;
			case 'VRCONFIGONEONE':
				$result = __('Always Open', 'vikrentcar');
				break;
			case 'VRCONFIGONETWO':
				$result = __('Time', 'vikrentcar');
				break;
			case 'VRCONFIGONETHREE':
				$result = __('From', 'vikrentcar');
				break;
			case 'VRCONFIGONEFOUR':
				$result = __('To', 'vikrentcar');
				break;
			case 'VRCONFIGONEFIVE':
				$result = __('Rentals Enabled', 'vikrentcar');
				break;
			case 'VRCONFIGONESIX':
				$result = __('Rentals Disabled Message', 'vikrentcar');
				break;
			case 'VRCONFIGONESEVEN':
				$result = __('Shop Opening Time', 'vikrentcar');
				break;
			case 'VRCONFIGONEEIGHT':
				$result = __('Hours of Extended Gratuity Period', 'vikrentcar');
				break;
			case 'VRCONFIGONENINE':
				$result = __('Dropped Off car is available after', 'vikrentcar');
				break;
			case 'VRCONFIGONETEN':
				$result = __('Choose Pickup Location', 'vikrentcar');
				break;
			case 'VRCONFIGONEELEVEN':
				$result = __('Pickup/Drop Off Date Format', 'vikrentcar');
				break;
			case 'VRCONFIGONETWELVE':
				$result = __('DD/MM/YYYY', 'vikrentcar');
				break;
			case 'VRCONFIGONETENTHREE':
				$result = __('YYYY/MM/DD', 'vikrentcar');
				break;
			case 'VRCONFIGONETENFOUR':
				$result = __('Cars Category Filter', 'vikrentcar');
				break;
			case 'VRCONFIGONETENFIVE':
				$result = __('Token Form Order Submit', 'vikrentcar');
				break;
			case 'VRCONFIGONETENSIX':
				$result = __('Admin e-Mail', 'vikrentcar');
				break;
			case 'VRCONFIGONETENSEVEN':
				$result = __('Minutes of Waiting for the Payment', 'vikrentcar');
				break;
			case 'VRCONFIGONETENEIGHT':
				$result = __('hours', 'vikrentcar');
				break;
			case 'VRCONFIGTWOONE':
				$result = __('Enable Paypal', 'vikrentcar');
				break;
			case 'VRCONFIGTWOTWO':
				$result = __('Payments Account<br/><small>(for Gateways like Paypal)</small>', 'vikrentcar');
				break;
			case 'VRCONFIGTWOTHREE':
				$result = __('Pay Entire Amount', 'vikrentcar');
				break;
			case 'VRCONFIGTWOFOUR':
				$result = __('Leave a deposit of ', 'vikrentcar');
				break;
			case 'VRCONFIGTWOFIVE':
				$result = __('Prices Tax Included', 'vikrentcar');
				break;
			case 'VRCONFIGTWOSIX':
				$result = __('Payment Transaction Name', 'vikrentcar');
				break;
			case 'VRCONFIGTHREEONE':
				$result = __('Company Name', 'vikrentcar');
				break;
			case 'VRCONFIGTHREETWO':
				$result = __('Front Title Tag', 'vikrentcar');
				break;
			case 'VRCONFIGTHREETHREE':
				$result = __('Front Title Tag Class', 'vikrentcar');
				break;
			case 'VRCONFIGTHREEFOUR':
				$result = __('Search Button Text', 'vikrentcar');
				break;
			case 'VRCONFIGTHREEFIVE':
				$result = __('Search Button Class', 'vikrentcar');
				break;
			case 'VRCONFIGTHREESIX':
				$result = __('Show VikRentCar Footer', 'vikrentcar');
				break;
			case 'VRCONFIGTHREESEVEN':
				$result = __('Opening Page Text', 'vikrentcar');
				break;
			case 'VRCONFIGTHREEEIGHT':
				$result = __('Closing Page Text', 'vikrentcar');
				break;
			case 'VRCONFIGFOURONE':
				$result = __('Enable Removed Orders Saving', 'vikrentcar');
				break;
			case 'VRCONFIGFOURTWO':
				$result = __('Enable Search Statistics', 'vikrentcar');
				break;
			case 'VRCONFIGFOURTHREE':
				$result = __('Send Search Notifications to Admin', 'vikrentcar');
				break;
			case 'VRCONFIGFOURFOUR':
				$result = __('Disclaimer', 'vikrentcar');
				break;
			case 'VRCONFIGFOURLOGO':
				$result = __('Company Logo', 'vikrentcar');
				break;
			case 'VRCONFIGFOURORDMAILFOOTER':
				$result = __('Footer Text Order eMail', 'vikrentcar');
				break;
			case 'NESSUNAIVA':
				$result = __('No Tax Rates Found', 'vikrentcar');
				break;
			case 'ASKFISCCODE':
				$result = __('Ask Italian Fiscal Code', 'vikrentcar');
				break;
			case 'VRCONFIGTHREECURNAME':
				$result = __('Currency Name', 'vikrentcar');
				break;
			case 'VRCONFIGTHREECURSYMB':
				$result = __('Currency Symbol', 'vikrentcar');
				break;
			case 'VRCONFIGTHREECURCODEPP':
				$result = __('Transactions Currency Code', 'vikrentcar');
				break;
			case 'VRPCHOOSEBUSYORDATE':
				$result = __('Reservation Date', 'vikrentcar');
				break;
			case 'VRPCHOOSEBUSYCAVAIL':
				$result = __('Units Available', 'vikrentcar');
				break;
			case 'VRNOLOCFEES':
				$result = __('No results', 'vikrentcar');
				break;
			case 'VRJSDELLOCFEE':
				$result = __('Confirm', 'vikrentcar');
				break;
			case 'VRPVIEWPLOCFEEONE':
				$result = __('Pickup', 'vikrentcar');
				break;
			case 'VRPVIEWPLOCFEETWO':
				$result = __('Drop Off', 'vikrentcar');
				break;
			case 'VRPVIEWPLOCFEETHREE':
				$result = __('Charge', 'vikrentcar');
				break;
			case 'VRPVIEWPLOCFEEFOUR':
				$result = __('Daily', 'vikrentcar');
				break;
			case 'VRYES':
				$result = __('Yes', 'vikrentcar');
				break;
			case 'VRNO':
				$result = __('No', 'vikrentcar');
				break;
			case 'VRNEWLOCFEEONE':
				$result = __('Pickup Location', 'vikrentcar');
				break;
			case 'VRNEWLOCFEETWO':
				$result = __('Drop Off Location', 'vikrentcar');
				break;
			case 'VRNEWLOCFEETHREE':
				$result = __('Cost', 'vikrentcar');
				break;
			case 'VRNEWLOCFEEFOUR':
				$result = __('Daily Cost', 'vikrentcar');
				break;
			case 'VRNEWLOCFEEFIVE':
				$result = __('Tax Rate', 'vikrentcar');
				break;
			case 'VRLOCFEESAVED':
				$result = __('Saved', 'vikrentcar');
				break;
			case 'VRLOCFEEUPDATE':
				$result = __('Updated', 'vikrentcar');
				break;
			case 'VRMAINLOCFEESTITLE':
				$result = __('Vik Rent Car - Pickup Drop Off Fees', 'vikrentcar');
				break;
			case 'VRMAINLOCFEESNEW':
				$result = __('Vik Rent Car - Pickup Drop Off Fees', 'vikrentcar');
				break;
			case 'VRMAINLOCFEESEDIT':
				$result = __('Vik Rent Car - Pickup Drop Off Fees', 'vikrentcar');
				break;
			case 'VRMAINLOCFEEDEL':
				$result = __('Delete', 'vikrentcar');
				break;
			case 'VRMAINLOCFEEEDIT':
				$result = __('Edit', 'vikrentcar');
				break;
			case 'VRMAINLOCFEENEW':
				$result = __('New', 'vikrentcar');
				break;
			case 'VRMAINSEASONSTITLE':
				$result = __('Vik Rent Car - Special Prices', 'vikrentcar');
				break;
			case 'VRMAINSEASONSDEL':
				$result = __('Delete', 'vikrentcar');
				break;
			case 'VRMAINSEASONSEDIT':
				$result = __('Edit', 'vikrentcar');
				break;
			case 'VRMAINSEASONSNEW':
				$result = __('New Special Price', 'vikrentcar');
				break;
			case 'VRMAINSEASONTITLENEW':
				$result = __('Vik Rent Car - New Special Price', 'vikrentcar');
				break;
			case 'VRMAINSEASONTITLEEDIT':
				$result = __('Vik Rent Car - Edit Special Price', 'vikrentcar');
				break;
			case 'VRMAINLOCFEETITLENEW':
				$result = __('Vik Rent Car - New Pickup Drop Off Fee', 'vikrentcar');
				break;
			case 'VRMAINLOCFEETITLEEDIT':
				$result = __('Vik Rent Car - Edit Pickup Drop Off Fee', 'vikrentcar');
				break;
			case 'VRSETORDCONFIRMED':
				$result = __('Set to Confirmed', 'vikrentcar');
				break;
			case 'VRPAYMENTMETHOD':
				$result = __('Method of Payment', 'vikrentcar');
				break;
			case 'VRUSEJUTILITY':
				$result = __('Send order emails with JUtility', 'vikrentcar');
				break;
			case 'VRCONFIGTHREENINE':
				$result = __('Show Partly Reserved Days', 'vikrentcar');
				break;
			case 'VRCONFIGTHREETEN':
				$result = __('Number of Months to Show', 'vikrentcar');
				break;
			case 'VRLIBONE':
				$result = __('Order Received on the', 'vikrentcar');
				break;
			case 'VRLIBTWO':
				$result = __('Customer Info', 'vikrentcar');
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
			case 'VRRITIROCAR':
				$result = __('Pickup Location', 'vikrentcar');
				break;
			case 'VRRETURNCARORD':
				$result = __('Drop Off Location', 'vikrentcar');
				break;
			case 'VRNOSEASONS':
				$result = __('No Special Prices found', 'vikrentcar');
				break;
			case 'VRJSDELSEASONS':
				$result = __('Confirm', 'vikrentcar');
				break;
			case 'VRPSHOWSEASONSONE':
				$result = __('From', 'vikrentcar');
				break;
			case 'VRPSHOWSEASONSTWO':
				$result = __('To', 'vikrentcar');
				break;
			case 'VRPSHOWSEASONSTHREE':
				$result = __('Type', 'vikrentcar');
				break;
			case 'VRPSHOWSEASONSFOUR':
				$result = __('Value', 'vikrentcar');
				break;
			case 'VRPSHOWSEASONSFIVE':
				$result = __('Charge', 'vikrentcar');
				break;
			case 'VRPSHOWSEASONSSIX':
				$result = __('Discount', 'vikrentcar');
				break;
			case 'VRPSHOWSEASONSSEVEN':
				$result = __('Location', 'vikrentcar');
				break;
			case 'VRNOCARSFOUNDSEASONS':
				$result = __('No Cars found', 'vikrentcar');
				break;
			case 'VRNEWSEASONONE':
				$result = __('From', 'vikrentcar');
				break;
			case 'VRNEWSEASONTWO':
				$result = __('To', 'vikrentcar');
				break;
			case 'VRNEWSEASONTHREE':
				$result = __('Type', 'vikrentcar');
				break;
			case 'VRNEWSEASONFOUR':
				$result = __('Value', 'vikrentcar');
				break;
			case 'VRNEWSEASONFIVE':
				$result = __('Cars', 'vikrentcar');
				break;
			case 'VRNEWSEASONSIX':
				$result = __('Charge', 'vikrentcar');
				break;
			case 'VRNEWSEASONSEVEN':
				$result = __('Discount', 'vikrentcar');
				break;
			case 'VRNEWSEASONEIGHT':
				$result = __('Locations', 'vikrentcar');
				break;
			case 'ERRINVDATESEASON':
				$result = __('Invalid Dates', 'vikrentcar');
				break;
			case 'ERRINVDATECARSLOCSEASON':
				$result = __('Season with same dates, locations and vehicles already exists', 'vikrentcar');
				break;
			case 'VRSEASONSAVED':
				$result = __('Special Price Saved', 'vikrentcar');
				break;
			case 'VRSEASONUPDATED':
				$result = __('Updated', 'vikrentcar');
				break;
			case 'VRSEASONANY':
				$result = __('Any', 'vikrentcar');
				break;
			case 'VRNOPAYMENTS':
				$result = __('No Payment Methods found', 'vikrentcar');
				break;
			case 'VRJSDELPAYMENTS':
				$result = __('Confirm', 'vikrentcar');
				break;
			case 'VRPSHOWPAYMENTSONE':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRPSHOWPAYMENTSTWO':
				$result = __('File', 'vikrentcar');
				break;
			case 'VRPSHOWPAYMENTSTHREE':
				$result = __('Notes', 'vikrentcar');
				break;
			case 'VRPSHOWPAYMENTSFOUR':
				$result = __('Cost', 'vikrentcar');
				break;
			case 'VRPSHOWPAYMENTSFIVE':
				$result = __('Published', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTONE':
				$result = __('Payment Name', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTTWO':
				$result = __('File Class', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTTHREE':
				$result = __('Published', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTFOUR':
				$result = __('Cost', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTFIVE':
				$result = __('Notes', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTSIX':
				$result = __('Yes', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTSEVEN':
				$result = __('No', 'vikrentcar');
				break;
			case 'VRLIBPAYNAME':
				$result = __('Payment Method', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTEIGHT':
				$result = __('Auto-Set Order Confirmed', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTNINE':
				$result = __('Always Show Notes', 'vikrentcar');
				break;
			case 'VRLOCFEETOPAY':
				$result = __('Pickup/Drop Off Fee', 'vikrentcar');
				break;
			case 'VRNOFIELDSFOUND':
				$result = __('No Custom Fields Found', 'vikrentcar');
				break;
			case 'VRPVIEWCUSTOMFONE':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRPVIEWCUSTOMFTWO':
				$result = __('Type', 'vikrentcar');
				break;
			case 'VRPVIEWCUSTOMFTHREE':
				$result = __('Required', 'vikrentcar');
				break;
			case 'VRPVIEWCUSTOMFFOUR':
				$result = __('Ordering', 'vikrentcar');
				break;
			case 'VRPVIEWCUSTOMFFIVE':
				$result = __('e-Mail', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFONE':
				$result = __('Field Name', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFTWO':
				$result = __('Type', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFTHREE':
				$result = __('Text', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFFOUR':
				$result = __('Select', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFFIVE':
				$result = __('Checkbox', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFSIX':
				$result = __('Required', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFSEVEN':
				$result = __('is e-Mail', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFEIGHT':
				$result = __('Popup Link', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFNINE':
				$result = __('Add Answer', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFTEN':
				$result = __('Textarea', 'vikrentcar');
				break;
			case 'VRMAINCUSTOMFTITLE':
				$result = __('Vik Rent Car - Custom Fields', 'vikrentcar');
				break;
			case 'VRMAINCUSTOMFDEL':
				$result = __('Remove', 'vikrentcar');
				break;
			case 'VRMAINCUSTOMFEDIT':
				$result = __('Edit', 'vikrentcar');
				break;
			case 'VRMAINCUSTOMFNEW':
				$result = __('New', 'vikrentcar');
				break;
			case 'VRMENUONE':
				$result = __('Rental', 'vikrentcar');
				break;
			case 'VRMENUTWO':
				$result = __('Cars', 'vikrentcar');
				break;
			case 'VRMENUTHREE':
				$result = __('Orders', 'vikrentcar');
				break;
			case 'VRMENUFOUR':
				$result = __('Global', 'vikrentcar');
				break;
			case 'VRMENUFIVE':
				$result = __('Types of Price', 'vikrentcar');
				break;
			case 'VRMENUSIX':
				$result = __('Categories', 'vikrentcar');
				break;
			case 'VRMENUSEVEN':
				$result = __('Orders List', 'vikrentcar');
				break;
			case 'VRMENUEIGHT':
				$result = __('Search Statistics', 'vikrentcar');
				break;
			case 'VRMENUNINE':
				$result = __('Tax Rates', 'vikrentcar');
				break;
			case 'VRMENUTEN':
				$result = __('Cars List', 'vikrentcar');
				break;
			case 'VRMENUELEVEN':
				$result = __('Removed Orders', 'vikrentcar');
				break;
			case 'VRMENUTWELVE':
				$result = __('Configuration', 'vikrentcar');
				break;
			case 'VRMENUTENTHREE':
				$result = __('Pickup/Drop Off Locations', 'vikrentcar');
				break;
			case 'VRMENUTENFOUR':
				$result = __('Characteristics', 'vikrentcar');
				break;
			case 'VRMENUTENFIVE':
				$result = __('Car Options', 'vikrentcar');
				break;
			case 'VRMENUTENSIX':
				$result = __('Pickup/Drop Off Fees', 'vikrentcar');
				break;
			case 'VRMENUTENSEVEN':
				$result = __('Special Prices', 'vikrentcar');
				break;
			case 'VRMENUTENEIGHT':
				$result = __('Payment Methods', 'vikrentcar');
				break;
			case 'VRMENUTENNINE':
				$result = __('Overview', 'vikrentcar');
				break;
			case 'VRMENUTENTEN':
				$result = __('Custom Fields', 'vikrentcar');
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
			case 'COM_VIKRENTCAR_MENU':
				$result = __('VikRentCar', 'vikrentcar');
				break;
			case 'VRNEWCARDROPLOC':
				$result = __('Drop Off Locations', 'vikrentcar');
				break;
			case 'VRMOREIMAGES':
				$result = __('Extra Images', 'vikrentcar');
				break;
			case 'VRADDIMAGES':
				$result = __('Add Images', 'vikrentcar');
				break;
			case 'VRRESIZEIMAGES':
				$result = __('Resize Images', 'vikrentcar');
				break;
			case 'VRCONFIGREQUIRELOGIN':
				$result = __('Require Login', 'vikrentcar');
				break;
			case 'VRCSEASON':
				$result = __('Season', 'vikrentcar');
				break;
			case 'VRCWEEKDAYS':
				$result = __('Week Days', 'vikrentcar');
				break;
			case 'VRCSEASONDAYS':
				$result = __('Days of the Week', 'vikrentcar');
				break;
			case 'VRCSUNDAY':
				$result = __('Sunday', 'vikrentcar');
				break;
			case 'VRCMONDAY':
				$result = __('Monday', 'vikrentcar');
				break;
			case 'VRCTUESDAY':
				$result = __('Tuesday', 'vikrentcar');
				break;
			case 'VRCWEDNESDAY':
				$result = __('Wednesday', 'vikrentcar');
				break;
			case 'VRCTHURSDAY':
				$result = __('Thursday', 'vikrentcar');
				break;
			case 'VRCFRIDAY':
				$result = __('Friday', 'vikrentcar');
				break;
			case 'VRCSATURDAY':
				$result = __('Saturday', 'vikrentcar');
				break;
			case 'VRCSPRICESHELP':
				$result = __('Insert a starting and an ending date (Season) or select one or more days of the week (Week Days). Only one filter is required. Provide a Season and Week Days to combine the filters', 'vikrentcar');
				break;
			case 'VRCSPRICESHELPTITLE':
				$result = __('Seasons and Week Days', 'vikrentcar');
				break;
			case 'VRCSPNAME':
				$result = __('Special Price Name', 'vikrentcar');
				break;
			case 'VRPSHOWSEASONSPNAME':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRPSHOWSEASONSWDAYS':
				$result = __('Week Days', 'vikrentcar');
				break;
			case 'VRCPLACELAT':
				$result = __('Latitude', 'vikrentcar');
				break;
			case 'VRCPLACELNG':
				$result = __('Longitude', 'vikrentcar');
				break;
			case 'VRCPLACEDESCR':
				$result = __('Description', 'vikrentcar');
				break;
			case 'VRCHOURLYFARES':
				$result = __('Hourly Fares', 'vikrentcar');
				break;
			case 'VRCDAILYFARES':
				$result = __('Daily Fares', 'vikrentcar');
				break;
			case 'VRCHOURS':
				$result = __('Hours', 'vikrentcar');
				break;
			case 'VRCHOURLYPRICES':
				$result = __('Hourly Price(s)', 'vikrentcar');
				break;
			case 'VRCPVIEWTARHOURS':
				$result = __('Fare for Hours', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFSEPARATOR':
				$result = __('Separator', 'vikrentcar');
				break;
			case 'VRCSEPDRIVERD':
				$result = __('Driver Information', 'vikrentcar');
				break;
			case 'VRCONFIGONEJQUERY':
				$result = __('Load jQuery Library', 'vikrentcar');
				break;
			case 'VRCONFIGONECALENDAR':
				$result = __('Calendar Type', 'vikrentcar');
				break;
			case 'VRCORDERNUMBER':
				$result = __('Order Number', 'vikrentcar');
				break;
			case 'VRCORDERDETAILS':
				$result = __('Order Details', 'vikrentcar');
				break;
			case 'VRNEWCATDESCR':
				$result = __('Description', 'vikrentcar');
				break;
			case 'VRPVIEWCATEGORIESDESCR':
				$result = __('Description', 'vikrentcar');
				break;
			case 'VRCPAYMENTSHELPCONFIRMTXT':
				$result = __('Auto-Set Order to Confirmed', 'vikrentcar');
				break;
			case 'VRCPAYMENTSHELPCONFIRM':
				$result = __('If this setting is enabled, when the user selects this payment, the order status will be set to Confirmed when saving the reservation. This setting should always be disabled for methods of payment that need to validate a server response after a credit card payment', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTCHARGEORDISC':
				$result = __('Charge/Discount', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTCHARGEPLUS':
				$result = __('Charge +', 'vikrentcar');
				break;
			case 'VRNEWPAYMENTDISCMINUS':
				$result = __('Discount -', 'vikrentcar');
				break;
			case 'VRPSHOWPAYMENTSCHARGEORDISC':
				$result = __('Charge/Discount', 'vikrentcar');
				break;
			case 'VRCPLACEOPENTIME':
				$result = __('Opening Time', 'vikrentcar');
				break;
			case 'VRCPLACEOPENTIMETXT':
				$result = __('The opening time for Pickup and-or Drop Off. If empty, the global opening time of the configuration will be applied', 'vikrentcar');
				break;
			case 'VRCPLACEOPENTIMEFROM':
				$result = __('From', 'vikrentcar');
				break;
			case 'VRCPLACEOPENTIMETO':
				$result = __('To', 'vikrentcar');
				break;
			case 'VRCSPONLYPICKINCL':
				$result = __('Pickup Date must be after the beginning of the Season', 'vikrentcar');
				break;
			case 'VRCHOURSCHARGES':
				$result = __('Extra Hours Charges', 'vikrentcar');
				break;
			case 'VRCEXTRARHOURS':
				$result = __('Extra Hours of Rental', 'vikrentcar');
				break;
			case 'VRCHOURLYCHARGES':
				$result = __('Hourly Charge(s)', 'vikrentcar');
				break;
			case 'VRCSHCHARGESHELP':
				$result = __('These charges will be applied to the daily fares that are longer than one day. A rental from the 20th of December at 8am to the 22nd at 11am can be charged by those 3 extra hours. The setting of the configuration Hours of Extended Gratuity Period will be considered as well. So in that case, the charge for the 3 extra hours will be applied only if that setting is 0, 1 or 2, not if it is 3 or higher. From and To need an integer value, for example From 3 To 6 hours', 'vikrentcar');
				break;
			case 'VRCSELVEHICLE':
				$result = __('Select Car', 'vikrentcar');
				break;
			case 'VRCONFIGEHOURSBASP':
				$result = __('Apply Extra Hours Charges', 'vikrentcar');
				break;
			case 'VRCONFIGEHOURSBEFORESP':
				$result = __('Before the Special Prices', 'vikrentcar');
				break;
			case 'VRCONFIGEHOURSAFTERSP':
				$result = __('After the Special Prices', 'vikrentcar');
				break;
			case 'VRCNEWOPTFORCESEL':
				$result = __('Always Selected', 'vikrentcar');
				break;
			case 'VRCNEWOPTFORCEVALT':
				$result = __('Quantity', 'vikrentcar');
				break;
			case 'VRCNEWOPTFORCEVALTPDAY':
				$result = __('per Day of Rental', 'vikrentcar');
				break;
			case 'VRCONFIGONECOUPONS':
				$result = __('Enable Coupons', 'vikrentcar');
				break;
			case 'VRCNOCOUPONSFOUND':
				$result = __('No coupon found', 'vikrentcar');
				break;
			case 'VRCPVIEWCOUPONSONE':
				$result = __('Code', 'vikrentcar');
				break;
			case 'VRCPVIEWCOUPONSTWO':
				$result = __('Type', 'vikrentcar');
				break;
			case 'VRCPVIEWCOUPONSTHREE':
				$result = __('Valid Dates', 'vikrentcar');
				break;
			case 'VRCPVIEWCOUPONSFOUR':
				$result = __('Vehicles', 'vikrentcar');
				break;
			case 'VRCPVIEWCOUPONSFIVE':
				$result = __('Min. Order Total', 'vikrentcar');
				break;
			case 'VRCCOUPONTYPEPERMANENT':
				$result = __('Permanent', 'vikrentcar');
				break;
			case 'VRCCOUPONTYPEGIFT':
				$result = __('Gift', 'vikrentcar');
				break;
			case 'VRCCOUPONALWAYSVALID':
				$result = __('Always Valid', 'vikrentcar');
				break;
			case 'VRCCOUPONALLVEHICLES':
				$result = __('All Vehicles', 'vikrentcar');
				break;
			case 'VRCNEWCOUPONONE':
				$result = __('Coupon Code', 'vikrentcar');
				break;
			case 'VRCNEWCOUPONTWO':
				$result = __('Coupon Type', 'vikrentcar');
				break;
			case 'VRCNEWCOUPONTHREE':
				$result = __('Percent or Total', 'vikrentcar');
				break;
			case 'VRCNEWCOUPONFOUR':
				$result = __('Value', 'vikrentcar');
				break;
			case 'VRCNEWCOUPONFIVE':
				$result = __('Vehicles', 'vikrentcar');
				break;
			case 'VRCNEWCOUPONSIX':
				$result = __('Valid Dates', 'vikrentcar');
				break;
			case 'VRCNEWCOUPONSEVEN':
				$result = __('Min. Order Total', 'vikrentcar');
				break;
			case 'VRCNEWCOUPONEIGHT':
				$result = __('All', 'vikrentcar');
				break;
			case 'VRCNEWCOUPONNINE':
				$result = __('If there are no dates, the coupon will be always valid', 'vikrentcar');
				break;
			case 'VRCCOUPONEXISTS':
				$result = __('Error, the coupon code already exists', 'vikrentcar');
				break;
			case 'VRCCOUPONSAVEOK':
				$result = __('Coupon Successfully Saved', 'vikrentcar');
				break;
			case 'VRCMENUFARES':
				$result = __('Pricing', 'vikrentcar');
				break;
			case 'VRCMENUDASHBOARD':
				$result = __('Dashboard', 'vikrentcar');
				break;
			case 'VRCMENUPRICESTABLE':
				$result = __('Fares Table', 'vikrentcar');
				break;
			case 'VRCMENUQUICKRES':
				$result = __('Calendar', 'vikrentcar');
				break;
			case 'VRCMENUCOUPONS':
				$result = __('Coupons', 'vikrentcar');
				break;
			case 'VRMAINCOUPONTITLE':
				$result = __('Vik Rent Car - Coupons', 'vikrentcar');
				break;
			case 'VRMAINCOUPONNEW':
				$result = __('New', 'vikrentcar');
				break;
			case 'VRMAINCOUPONEDIT':
				$result = __('Edit', 'vikrentcar');
				break;
			case 'VRMAINCOUPONDEL':
				$result = __('Remove', 'vikrentcar');
				break;
			case 'VRMAINDASHBOARDTITLE':
				$result = __('Vik Rent Car - Dashboard', 'vikrentcar');
				break;
			case 'VRCDASHUPCRES':
				$result = __('Upcoming Rentals', 'vikrentcar');
				break;
			case 'VRCDASHALLPLACES':
				$result = __('Any', 'vikrentcar');
				break;
			case 'VRCDASHPICKUPLOC':
				$result = __('Pickup Location', 'vikrentcar');
				break;
			case 'VRCDASHUPRESONE':
				$result = __('ID', 'vikrentcar');
				break;
			case 'VRCDASHUPRESTWO':
				$result = __('Vehicle', 'vikrentcar');
				break;
			case 'VRCDASHUPRESTHREE':
				$result = __('Pickup', 'vikrentcar');
				break;
			case 'VRCDASHUPRESFOUR':
				$result = __('Drop Off', 'vikrentcar');
				break;
			case 'VRCDASHUPRESFIVE':
				$result = __('Status', 'vikrentcar');
				break;
			case 'VRCDASHSTATS':
				$result = __('Report', 'vikrentcar');
				break;
			case 'VRCDASHNOPRICES':
				$result = __('Types of Price', 'vikrentcar');
				break;
			case 'VRCDASHNOLOCATIONS':
				$result = __('Pickup - Drop Off Locations', 'vikrentcar');
				break;
			case 'VRCDASHNOCATEGORIES':
				$result = __('Categories', 'vikrentcar');
				break;
			case 'VRCDASHNOCARS':
				$result = __('Cars', 'vikrentcar');
				break;
			case 'VRCDASHNODAILYFARES':
				$result = __('Daily Fares', 'vikrentcar');
				break;
			case 'VRCDASHTOTRESCONF':
				$result = __('Confirmed Reservations', 'vikrentcar');
				break;
			case 'VRCDASHTOTRESPEND':
				$result = __('Standby Reservations', 'vikrentcar');
				break;
			case 'VRCCOUPON':
				$result = __('Coupon', 'vikrentcar');
				break;
			case 'VRCONFIGTHEME':
				$result = __('Theme', 'vikrentcar');
				break;
			case 'VRSPECIALPRICEVALHELP':
				$result = __('This value will be added to or deducted from the cost of every day of rental that is affected by this Special Price', 'vikrentcar');
				break;
			case 'VRNEWSEASONVALUEOVERRIDE':
				$result = __('Value Overrides', 'vikrentcar');
				break;
			case 'VRNEWSEASONNIGHTSOVR':
				$result = __('Days of Rental', 'vikrentcar');
				break;
			case 'VRNEWSEASONVALUESOVR':
				$result = __('Value', 'vikrentcar');
				break;
			case 'VRNEWSEASONVALUEOVERRIDEHELP':
				$result = __('The default absolute or percentage value can be different depending on the days of rental. For example you can override the default value of the Special Price for 7 Days of rental and set it to a lower charge or to a higher discount. Do not override the default value for always applying the same charge or discount regardless the length of the rental in the days affected by this Special Price.', 'vikrentcar');
				break;
			case 'VRNEWSEASONADDOVERRIDE':
				$result = __('Add Value Override', 'vikrentcar');
				break;
			case 'VRLOCFEEINVERT':
				$result = __('Apply if the Locations are inverted', 'vikrentcar');
				break;
			case 'VRLOCFEECOSTOVERRIDE':
				$result = __('Cost Overrides', 'vikrentcar');
				break;
			case 'VRLOCFEECOSTOVERRIDEHELP':
				$result = __('The default Cost can be overwritten depending on the number of days of rental. Do not create overrides fow always applying the default cost.', 'vikrentcar');
				break;
			case 'VRLOCFEECOSTOVERRIDEADD':
				$result = __('Add Cost Override', 'vikrentcar');
				break;
			case 'VRLOCFEECOSTOVERRIDEDAYS':
				$result = __('Days of Rental', 'vikrentcar');
				break;
			case 'VRLOCFEECOSTOVERRIDECOST':
				$result = __('Cost', 'vikrentcar');
				break;
			case 'VRCUSTSTARTINGFROM':
				$result = __('Custom Starting From Price', 'vikrentcar');
				break;
			case 'VRCUSTSTARTINGFROMHELP':
				$result = __('The View List and the Vehicle Details page will show this price as the Starting From Price. Leave this field empty for making the program automatically calculate the Starting From Price', 'vikrentcar');
				break;
			case 'VRQRCUSTMAIL':
				$result = __('Customer e-Mail', 'vikrentcar');
				break;
			case 'VRCRESENDORDEMAIL':
				$result = __('Re-Send eMail', 'vikrentcar');
				break;
			case 'VRORDERMAILRESENT':
				$result = __('Order eMail re-sent to %s', 'vikrentcar');
				break;
			case 'VRORDERMAILRESENTNOREC':
				$result = __('Error, Customer eMail Address is empty', 'vikrentcar');
				break;
			case 'VRCORDERING':
				$result = __('Ordering', 'vikrentcar');
				break;
			case 'VRNEWPLACECLOSINGDAYS':
				$result = __('Closing Days', 'vikrentcar');
				break;
			case 'VRNEWPLACECLOSINGDAYSHELP':
				$result = __('Insert the dates when this location is closed for Pickup and Drop Off. Right syntax: yyyy-mm-dd,yyyy-mm-dd,..etc..Use the calendar to avoid syntax errors.', 'vikrentcar');
				break;
			case 'VRNEWPLACECLOSINGDAYSADD':
				$result = __('Add Date', 'vikrentcar');
				break;
			case 'VRCONFIGUSDATEFORMAT':
				$result = __('MM/DD/YYYY', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFDATETYPE':
				$result = __('Date', 'vikrentcar');
				break;
			case 'VRCQUICKRESMOREOPTIONS':
				$result = __('Show More Options', 'vikrentcar');
				break;
			case 'VRCQUICKRESMOREOPTIONSHIDE':
				$result = __('Hide Options', 'vikrentcar');
				break;
			case 'VRCQUICKRESORDSTATUS':
				$result = __('Order Status', 'vikrentcar');
				break;
			case 'VRCQUICKRESMETHODOFPAYMENT':
				$result = __('Payment Method', 'vikrentcar');
				break;
			case 'VRCQUICKRESNONE':
				$result = __('-undefined-', 'vikrentcar');
				break;
			case 'VRCQUICKRESPOPULATECUSTOMINFO':
				$result = __('Populate Customer Information Fields', 'vikrentcar');
				break;
			case 'VRCQUICKRESWARNSTANDBY':
				$result = __('Order Status: Waiting for the payment. Choose one type of price and eventually some of the Options. Then click on Save to complete the Standby - Quick Reservation. ', 'vikrentcar');
				break;
			case 'VRCQUICKRESWARNSTANDBYSENDMAIL':
				$result = __('An email will be sent to the Customer eMail Address with the link for making the payment', 'vikrentcar');
				break;
			case 'VRCQUICKRESNOTIFYCUST':
				$result = __('Notify Customer via eMail', 'vikrentcar');
				break;
			case 'VRCFRONTVIEWSTANDBYORDER':
				$result = __('View front-end order page', 'vikrentcar');
				break;
			case 'VRCQUICKRESNOLOCATION':
				$result = __('-undefined-', 'vikrentcar');
				break;
			case 'VRCQUICKRESPICKUPLOC':
				$result = __('Pickup Location', 'vikrentcar');
				break;
			case 'VRCQUICKRESDROPOFFLOC':
				$result = __('Drop Off Location', 'vikrentcar');
				break;
			case 'VRCQUICKRESWARNCONFIRMED':
				$result = __('Order Status: Confirmed. Choose one type of price and eventually some of the Options. Then click on Save to complete the Quick Reservation. An email will be sent to the Customer eMail Address with the reservation details', 'vikrentcar');
				break;
			case 'VRCSENDPDF':
				$result = __('Attach PDF to the order eMail', 'vikrentcar');
				break;
			case 'VRCDOWNLOADPDF':
				$result = __('Download PDF', 'vikrentcar');
				break;
			case 'VRCRESENDORDEMAILANDPDF':
				$result = __('Re-send Order eMail + PDF', 'vikrentcar');
				break;
			case 'VRCCUSTEMAILADDR':
				$result = __('eMail Address', 'vikrentcar');
				break;
			case 'VRPEDITBUSYORDERNUMBER':
				$result = __('Order Number', 'vikrentcar');
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
			case 'VRCSPKEEPFIRSTDAYRATE':
				$result = __('Keep First Day Rate', 'vikrentcar');
				break;
			case 'VRCSPKEEPFIRSTDAYRATEHELP':
				$result = __('If this setting is enabled, the first day of rental will be the one giving the same rate to all the other days. If the first day of rental is not included in this Special Price and this setting is enabled, then every other day of rental will be considered as not included even if they were. When this setting is enabled just the first day of rental is considered and the week days do not affect this setting.', 'vikrentcar');
				break;
			case 'VRCNEWOPTFORCEVALIFDAYS':
				$result = __('Show if Days of Rental Greater than', 'vikrentcar');
				break;
			case 'VRCPLACEOVERRIDETAX':
				$result = __('Override Tax Rate', 'vikrentcar');
				break;
			case 'VRCPLACEOVERRIDETAXTXT':
				$result = __('If a Tax Rate is specified for this location, when this will be selected as Pickup, the Rental Fare, the Options and the Pickup-Drop Off Fees will take this tax rate instead of the one that they have assigned. Leave this field empty for using the global tax rate of each cost', 'vikrentcar');
				break;
			case 'VRCAMOUNTPAID':
				$result = __('Amount Paid', 'vikrentcar');
				break;
			case 'VRNEWSEASONVALUESOVREMORE':
				$result = __('and more', 'vikrentcar');
				break;
			case 'VRNEWSEASONROUNDCOST':
				$result = __('Round to Integer', 'vikrentcar');
				break;
			case 'VRNEWSEASONROUNDCOSTNO':
				$result = __('- disabled -', 'vikrentcar');
				break;
			case 'VRNEWSEASONROUNDCOSTUP':
				$result = __('Round Up', 'vikrentcar');
				break;
			case 'VRNEWSEASONROUNDCOSTDOWN':
				$result = __('Round Down', 'vikrentcar');
				break;
			case 'VRCONFIGNUMDECIMALS':
				$result = __('Number of Decimals', 'vikrentcar');
				break;
			case 'VRCONFIGNUMDECSEPARATOR':
				$result = __('Decimal Separator', 'vikrentcar');
				break;
			case 'VRCONFIGNUMTHOSEPARATOR':
				$result = __('Thousand Separator', 'vikrentcar');
				break;
			case 'VRCONFIGONEDROPDPLUS':
				$result = __('Minimum # Days of Rental', 'vikrentcar');
				break;
			case 'VRCCONFIRMATIONNUMBER':
				$result = __('Confirmation Number', 'vikrentcar');
				break;
			case 'VRMAINORDERSEXPORT':
				$result = __('Export', 'vikrentcar');
				break;
			case 'VRMAINEXPORTTITLE':
				$result = __('Vik Rent Car - Export Orders', 'vikrentcar');
				break;
			case 'VREXPORTONE':
				$result = __('From Date', 'vikrentcar');
				break;
			case 'VREXPORTTWO':
				$result = __('To Date', 'vikrentcar');
				break;
			case 'VREXPORTTHREE':
				$result = __('Export Type', 'vikrentcar');
				break;
			case 'VREXPORTFOUR':
				$result = __('CSV (for Excel or other Software)', 'vikrentcar');
				break;
			case 'VREXPORTFIVE':
				$result = __('ICS (iCalendar, Google Calendar, Hotmail)', 'vikrentcar');
				break;
			case 'VREXPORTSIX':
				$result = __('Orders Status', 'vikrentcar');
				break;
			case 'VREXPORTSEVEN':
				$result = __('Confirmed', 'vikrentcar');
				break;
			case 'VREXPORTEIGHT':
				$result = __('Confirmed, Pending and Cancelled', 'vikrentcar');
				break;
			case 'VREXPORTNINE':
				$result = __('Export Orders', 'vikrentcar');
				break;
			case 'VREXPORTTEN':
				$result = __('Date Format', 'vikrentcar');
				break;
			case 'VREXPORTELEVEN':
				$result = __('Location', 'vikrentcar');
				break;
			case 'VRCEXPORTERRNOREC':
				$result = __('Error, no records to export...', 'vikrentcar');
				break;
			case 'VRCEXPCSVPICK':
				$result = __('Pickup Date', 'vikrentcar');
				break;
			case 'VRCEXPCSVDROP':
				$result = __('Drop Off Date', 'vikrentcar');
				break;
			case 'VRCEXPCSVCAR':
				$result = __('Vehicle', 'vikrentcar');
				break;
			case 'VRCEXPCSVPICKLOC':
				$result = __('Pickup Location', 'vikrentcar');
				break;
			case 'VRCEXPCSVDROPLOC':
				$result = __('Drop Off Location', 'vikrentcar');
				break;
			case 'VRCEXPCSVCUSTINFO':
				$result = __('Customer Info', 'vikrentcar');
				break;
			case 'VRCEXPCSVPAYMETH':
				$result = __('Payment Method', 'vikrentcar');
				break;
			case 'VRCEXPCSVTOT':
				$result = __('Total', 'vikrentcar');
				break;
			case 'VRCEXPCSVTOTPAID':
				$result = __('Total Paid', 'vikrentcar');
				break;
			case 'VRCEXPCSVORDSTATUS':
				$result = __('Status', 'vikrentcar');
				break;
			case 'VRCICSEXPSUMMARY':
				$result = __('Rental @ %s', 'vikrentcar');
				break;
			case 'VRPAYMENTPARAMETERS':
				$result = __('Parameters', 'vikrentcar');
				break;
			case 'VIKLOADING':
				$result = __('Loading...', 'vikrentcar');
				break;
			case 'VRCVERSION':
				$result = __('VikRent Car v.%s - Powered by', 'vikrentcar');
				break;
			case 'VRSAVECLOSE':
				$result = __('Save &amp; Close', 'vikrentcar');
				break;
			case 'VRCONFIGMINDAYSADVANCE':
				$result = __('Days in Advance for bookings', 'vikrentcar');
				break;
			case 'VRCONFIGMAXDATEFUTURE':
				$result = __('Maximum Date in the Future from today', 'vikrentcar');
				break;
			case 'VRCONFIGMAXDATEDAYS':
				$result = __('Days', 'vikrentcar');
				break;
			case 'VRCONFIGMAXDATEWEEKS':
				$result = __('Weeks', 'vikrentcar');
				break;
			case 'VRCONFIGMAXDATEMONTHS':
				$result = __('Months', 'vikrentcar');
				break;
			case 'VRCONFIGMAXDATEYEARS':
				$result = __('Years', 'vikrentcar');
				break;
			case 'VRCONFIGFLUSHSESSION':
				$result = __('Renew Session', 'vikrentcar');
				break;
			case 'VRCONFIGFLUSHSESSIONCONF':
				$result = __('The PHP Session will be renewed and the new settings will be applied but any logged in user will be logged out. Proceed?', 'vikrentcar');
				break;
			case 'VRCONFIGFIRSTWDAY':
				$result = __('Calendars First Day of the Week', 'vikrentcar');
				break;
			case 'VRCPLACESUGGOPENTIME':
				$result = __('Suggested Time', 'vikrentcar');
				break;
			case 'VRCPLACESUGGOPENTIMETXT':
				$result = __('If not empty, this time will be pre-selected in the drop down menus for selecting the time', 'vikrentcar');
				break;
			case 'VRCSPYEARTIED':
				$result = __('Tied to the Year', 'vikrentcar');
				break;
			case 'VRCSELECTALL':
				$result = __('Select All', 'vikrentcar');
				break;
			case 'VRCSPTYPESPRICE':
				$result = __('Types of Price', 'vikrentcar');
				break;
			case 'VRCISPROMOTION':
				$result = __('Promotion', 'vikrentcar');
				break;
			case 'VRCPROMOVALIDITY':
				$result = __('Valid up to', 'vikrentcar');
				break;
			case 'VRCPROMOVALIDITYDAYSADV':
				$result = __('days in advance from Start Date', 'vikrentcar');
				break;
			case 'VRCPROMOTEXT':
				$result = __('Promotion Details', 'vikrentcar');
				break;
			case 'VRCSHORTDESCRIPTIONCAR':
				$result = __('Short Description', 'vikrentcar');
				break;
			case 'VRCPARAMSCAR':
				$result = __('Parameters', 'vikrentcar');
				break;
			case 'VRCPARAMDAILYCOST':
				$result = __('Show Cost Per Day in Search Results', 'vikrentcar');
				break;
			case 'VRCPARAMPAGETITLE':
				$result = __('Custom Page Title', 'vikrentcar');
				break;
			case 'VRCPARAMPAGETITLEBEFORECUR':
				$result = __('Add it Before the Current Page Title', 'vikrentcar');
				break;
			case 'VRCPARAMPAGETITLEAFTERCUR':
				$result = __('Add it After the Current Page Title', 'vikrentcar');
				break;
			case 'VRCPARAMPAGETITLEREPLACECUR':
				$result = __('Replace the Current Page Title', 'vikrentcar');
				break;
			case 'VRCPARAMKEYWORDSMETATAG':
				$result = __('Keywords Meta Tag', 'vikrentcar');
				break;
			case 'VRCPARAMDESCRIPTIONMETATAG':
				$result = __('Description Meta Tag', 'vikrentcar');
				break;
			case 'VRCPARAMCAREMAIL':
				$result = __('Additional eMail Address', 'vikrentcar');
				break;
			case 'VRCPARAMCAREMAILHELP':
				$result = __('if not empty, this address will be notified together with the administrator email address defined in the Configuration.', 'vikrentcar');
				break;
			case 'VRCNEWCUSTOMFCOUNTRY':
				$result = __('Country', 'vikrentcar');
				break;
			case 'VRCCHANGEPAYLABEL':
				$result = __('::Change method of payment::', 'vikrentcar');
				break;
			case 'VRCCHANGEPAYCONFIRM':
				$result = __('Change method of payment to ', 'vikrentcar');
				break;
			case 'VRCPAYMENTLOGTOGGLE':
				$result = __('Payments Log', 'vikrentcar');
				break;
			case 'VRCVIEWORDFRONT':
				$result = __('View in front site', 'vikrentcar');
				break;
			case 'VRCTOTALWOULDBE':
				$result = __('Total with current pricing would be %s', 'vikrentcar');
				break;
			case 'VRCORDERSLOCFILTER':
				$result = __('Filter by Location', 'vikrentcar');
				break;
			case 'VRCORDERSLOCFILTERANY':
				$result = __('Any Location', 'vikrentcar');
				break;
			case 'VRCORDERSLOCFILTERPICK':
				$result = __('Pick-up', 'vikrentcar');
				break;
			case 'VRCORDERSLOCFILTERDROP':
				$result = __('Drop-off', 'vikrentcar');
				break;
			case 'VRCORDERSLOCFILTERPICKDROP':
				$result = __('Pick-up or Drop-off', 'vikrentcar');
				break;
			case 'VRCORDERSLOCFILTERBTN':
				$result = __('Apply', 'vikrentcar');
				break;
			case 'VREXPORTNUMORDS':
				$result = __('Orders to Export: %d', 'vikrentcar');
				break;
			case 'VRCAPPLYDISCOUNT':
				$result = __('Apply Discount', 'vikrentcar');
				break;
			case 'VRCAPPLYDISCOUNTSAVE':
				$result = __('Save', 'vikrentcar');
				break;
			case 'VRCADMINDISCOUNT':
				$result = __('Discount', 'vikrentcar');
				break;
			case 'VRPEDITBUSYSETCAR':
				$result = __('Car', 'vikrentcar');
				break;
			case 'VRPEDITBUSYSETCARCHANGE':
				$result = __('The car rented will be changed', 'vikrentcar');
				break;
			case 'VRPEDITBUSYPICKPLACE':
				$result = __('Pickup Location', 'vikrentcar');
				break;
			case 'VRPEDITBUSYDROPPLACE':
				$result = __('Drop off Location', 'vikrentcar');
				break;
			case 'VRCUPDBUSYCARSWITCHED':
				$result = __('The Car was correctly switched. Please select the new Rental Fare for this car.', 'vikrentcar');
				break;
			case 'VRCMENUOOHFEES':
				$result = __('Out of Hours Fees', 'vikrentcar');
				break;
			case 'VRCNOOOHFEESFOUND':
				$result = __('No Out of Hours Fees Found', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESONE':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESTWO':
				$result = __('From Time', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESTHREE':
				$result = __('To Time', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESFOUR':
				$result = __('Pick Up Charge', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESFIVE':
				$result = __('Drop Off Charge', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESSIX':
				$result = __('Max Charge', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESSEVEN':
				$result = __('Cars', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESEIGHT':
				$result = __('Locations', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESNINE':
				$result = __('Pick up/Drop Off', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESTEN':
				$result = __('Pick up only', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESELEVEN':
				$result = __('Drop off only', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESTWELVE':
				$result = __('Pick up and Drop Off', 'vikrentcar');
				break;
			case 'VRMAINOOHFEESTITLE':
				$result = __('Vik Rent Car - Out of Hours Fees', 'vikrentcar');
				break;
			case 'VRMAINOOHFEENEW':
				$result = __('New', 'vikrentcar');
				break;
			case 'VRMAINOOHFEEEDIT':
				$result = __('Edit', 'vikrentcar');
				break;
			case 'VRMAINOOHFEEDEL':
				$result = __('Remove Fees', 'vikrentcar');
				break;
			case 'VRCOOHERRTIME':
				$result = __('Error, invalid time for the Out of Hours Fee', 'vikrentcar');
				break;
			case 'VRCOOHFEESAVED':
				$result = __('Out of Hours Fee Saved!', 'vikrentcar');
				break;
			case 'VRCOOHFEEUPDATED':
				$result = __('Out of Hours Fee Updated!', 'vikrentcar');
				break;
			case 'VRCPVIEWOOHFEESTAX':
				$result = __('Tax Rate', 'vikrentcar');
				break;
			case 'VRCOOHFEETOPAY':
				$result = __('Out of Hours Fee<br/>(%s)', 'vikrentcar');
				break;
			case 'VRCOOHFEEAMOUNT':
				$result = __('Out of Hours Fee', 'vikrentcar');
				break;
			case 'VRCDISTFEATURESMNG':
				$result = __('Manage Distinctive Features', 'vikrentcar');
				break;
			case 'VRCDISTFEATURES':
				$result = __('Distinctive Features', 'vikrentcar');
				break;
			case 'VRCDISTFEATURECUNIT':
				$result = __('Car #', 'vikrentcar');
				break;
			case 'VRCDISTFEATUREADD':
				$result = __('Add', 'vikrentcar');
				break;
			case 'VRCDISTFEATURETXT':
				$result = __('Feature', 'vikrentcar');
				break;
			case 'VRCDISTFEATUREVAL':
				$result = __('Value', 'vikrentcar');
				break;
			case 'VRCDEFAULTDISTFEATUREONE':
				$result = __('License Plate', 'vikrentcar');
				break;
			case 'VRCDEFAULTDISTFEATURETWO':
				$result = __('Mileage', 'vikrentcar');
				break;
			case 'VRCDEFAULTDISTFEATURETHREE':
				$result = __('Fuel In', 'vikrentcar');
				break;
			case 'VRCDEFAULTDISTFEATUREFOUR':
				$result = __('Next Service', 'vikrentcar');
				break;
			case 'VRCCARUPDATEOK':
				$result = __('Vehicle Updated Successfully!', 'vikrentcar');
				break;
			case 'VRCCARSAVEOK':
				$result = __('Vehicle Saved Successfully!', 'vikrentcar');
				break;
			case 'VRCDISTFEATURECDAMAGES':
				$result = __('Damages and Status', 'vikrentcar');
				break;
			case 'VRCDISTFEATURECDAMAGENOTES':
				$result = __('Damage', 'vikrentcar');
				break;
			case 'VRCDISTFEATURENODAMAGE':
				$result = __('No Damages Reported', 'vikrentcar');
				break;
			case 'VRCFEATASSIGNUNIT':
				$result = __('Assign Unit #', 'vikrentcar');
				break;
			case 'VRCFEATASSIGNUNITEMPTY':
				$result = __('Not Specified', 'vikrentcar');
				break;
			case 'VRCUNITASSIGNED':
				$result = __('Unit # Assigned', 'vikrentcar');
				break;
			case 'VRCCUSTOMERCHECKIN':
				$result = __('Customer Check-In', 'vikrentcar');
				break;
			case 'VRCPDFCHECKIN':
				$result = __('Download Check-in Document', 'vikrentcar');
				break;
			case 'VRCFILTCNAMECNUMB':
				$result = __('ID/Confirmation Number', 'vikrentcar');
				break;
			case 'VRCCONFIGSEARCHFILTCHARACTS':
				$result = __('Filter by Characteristics', 'vikrentcar');
				break;
			case 'VRCCONFIGDAMAGESHOWTYPE':
				$result = __('Car Damages in Check-in PDF', 'vikrentcar');
				break;
			case 'VRCCONFIGDAMAGETYPEONE':
				$result = __('Damage Marks Only', 'vikrentcar');
				break;
			case 'VRCCONFIGDAMAGETYPETWO':
				$result = __('Numbered Damage Marks', 'vikrentcar');
				break;
			case 'VRCCONFIGDAMAGETYPETHREE':
				$result = __('Damage Marks and Explanations', 'vikrentcar');
				break;
			case 'VREXPORTXML':
				$result = __('XML (for any Rental Management Software)', 'vikrentcar');
				break;
			case 'VREXPORTCHOOSEXML':
				$result = __('Software Driver File', 'vikrentcar');
				break;
			case 'VRCEXPORTERRFILE':
				$result = __('Error loading the XML Software Driver File', 'vikrentcar');
				break;
			case 'VRCONFIGTHUMBSIZE':
				$result = __('Thumbnails Size', 'vikrentcar');
				break;
			case 'VRCGENINVOICE':
				$result = __('Generate Invoices', 'vikrentcar');
				break;
			case 'VRCINVSTARTNUM':
				$result = __('Invoices Starting Number', 'vikrentcar');
				break;
			case 'VRCINVNUMSUFF':
				$result = __('Number Suffix', 'vikrentcar');
				break;
			case 'VRCINVDATE':
				$result = __('Date', 'vikrentcar');
				break;
			case 'VRCINVDATERES':
				$result = __('Use Order Date', 'vikrentcar');
				break;
			case 'VRCINVSENDVIAEMAIL':
				$result = __('Send Invoices to the customers via email', 'vikrentcar');
				break;
			case 'VRCINVGENERATING':
				$result = __('%d invoice(s) will be generated', 'vikrentcar');
				break;
			case 'VRCINVNUM':
				$result = __('Invoice Number', 'vikrentcar');
				break;
			case 'VRCINVCOLDESCR':
				$result = __('Description', 'vikrentcar');
				break;
			case 'VRCINVCOLNETPRICE':
				$result = __('Net', 'vikrentcar');
				break;
			case 'VRCINVCOLTAX':
				$result = __('Tax', 'vikrentcar');
				break;
			case 'VRCINVCOLPRICE':
				$result = __('Price', 'vikrentcar');
				break;
			case 'VRCINVCOLCUSTINFO':
				$result = __('Customer Information', 'vikrentcar');
				break;
			case 'VRCINVCOLTOTAL':
				$result = __('Total', 'vikrentcar');
				break;
			case 'VRCINVCOLTAX':
				$result = __('Taxes', 'vikrentcar');
				break;
			case 'VRCINVCOLGRANDTOTAL':
				$result = __('Grand Total', 'vikrentcar');
				break;
			case 'VRCINVCOMPANYINFO':
				$result = __('Company Legal Information', 'vikrentcar');
				break;
			case 'VRCINVDESCRCONT':
				$result = __('Rental Order for %s - Pickup Date: %s', 'vikrentcar');
				break;
			case 'VRCINVMAILSUBJ':
				$result = __('Invoice for your Order', 'vikrentcar');
				break;
			case 'VRCDOWNLOADPDFINVOICE':
				$result = __('Download Invoice', 'vikrentcar');
				break;
			case 'VRCSTOPRENTALS':
				$result = __('Stop Rentals on these dates', 'vikrentcar');
				break;
			case 'VRCICALLINK':
				$result = __('iCal Sync Link', 'vikrentcar');
				break;
			case 'VRCICALKEY':
				$result = __('iCal Secret Key', 'vikrentcar');
				break;
			case 'VRCINVMAILCONT':
				$result = __('Dear customer,<br/><br/>attahced to this message you will find the invoice for your rental order.', 'vikrentcar');
				break;
			case 'VRCTOTINVGEN':
				$result = __('Invoices Generated: %d', 'vikrentcar');
				break;
			case 'VRCPDFCUSTOMERCHECKINTITLE':
				$result = __('Customer Check-in Document', 'vikrentcar');
				break;
			case 'VRCPDFCUSTOMERCHECKINPARAGRAPH':
				$result = __('This document shows the current conditions of the rental car as well as some distinctive features. <br/>Any red-mark placed over the inspection image indicates a Scratch, a Ding, a Scrap or a Dent.', 'vikrentcar');
				break;
			case 'VRCPDFCUSTOMERCHECKINCUSTSIGNATURE':
				$result = __('Customer Signature', 'vikrentcar');
				break;
			case 'VRCPDFCUSTOMERCHECKINADMINSIGNATURE':
				$result = __('Checker-in Signature', 'vikrentcar');
				break;
			case 'VRMENUTRANSLATIONS':
				$result = __('Translations', 'vikrentcar');
				break;
			case 'VRCMAINTRANSLATIONSTITLE':
				$result = __('Vik Rent Car - Translations', 'vikrentcar');
				break;
			case 'VRCGETTRANSLATIONS':
				$result = __('Load Translations', 'vikrentcar');
				break;
			case 'VRCTRANSLATIONERRONELANG':
				/**
				 * @wponly  we use "WordPress site" in the string below
				 */
				$result = __('There is only one content-language enabled for this WordPress site so translations cannot be created.', 'vikrentcar');
				break;
			case 'VRCTANSLATIONSCHANGESCONF':
				$result = __('Some changes were made to the translations. Proceed without Saving?', 'vikrentcar');
				break;
			case 'VRCTRANSLATIONSELTABLEMESS':
				$result = __('No Contents Selected for Translation', 'vikrentcar');
				break;
			case 'VRCTRANSLATIONDEFLANG':
				$result = __('Default Language', 'vikrentcar');
				break;
			case 'VRCTRANSLATIONERRINVTABLE':
				$result = __('Error: Invalid or Empty Table Set for Translation', 'vikrentcar');
				break;
			case 'VRCTRANSLSAVEDOK':
				$result = __('Translations Saved!', 'vikrentcar');
				break;
			case 'VRCTRANSLATIONINISTATUS':
				$result = __('Status', 'vikrentcar');
				break;
			case 'VRCINIMISSINGFILE':
				$result = __('Missing Translation File', 'vikrentcar');
				break;
			case 'VRCINIDEFINITIONS':
				$result = __('Definitions', 'vikrentcar');
				break;
			case 'VRCINIPATH':
				$result = __('Path', 'vikrentcar');
				break;
			case 'VRCARSEFALIAS':
				$result = __('SEF Alias', 'vikrentcar');
				break;
			case 'VRCDELCONFIRM':
				$result = __('Some records will be removed. Proceed?', 'vikrentcar');
				break;
			case 'VRCCONFIGBOOKINGPART':
				$result = __('Booking', 'vikrentcar');
				break;
			case 'VRCCONFIGSEARCHPART':
				$result = __('Search/Rental Parameters', 'vikrentcar');
				break;
			case 'VRCCONFIGSYSTEMPART':
				$result = __('System', 'vikrentcar');
				break;
			case 'VRCCONFENMULTILANG':
				$result = __('Enable Multi-Language', 'vikrentcar');
				break;
			case 'VRCCONFSEFROUTER':
				$result = __('SEF Router', 'vikrentcar');
				break;
			case 'VRCCONFIGCURRENCYPART':
				$result = __('Currency', 'vikrentcar');
				break;
			case 'VRCCONFIGPAYMPART':
				$result = __('Taxes and Payments', 'vikrentcar');
				break;
			case 'VRCCONFIGAPPEARPART':
				$result = __('Appearance and Texts', 'vikrentcar');
				break;
			case 'VRCSEASONAFFECTEDROOMS':
				$result = __('Affected Cars', 'vikrentcar');
				break;
			case 'VRCRATESOVWCAR':
				$result = __('Car', 'vikrentcar');
				break;
			case 'VRCAFFANYCAR':
				$result = __('Any Car', 'vikrentcar');
				break;
			case 'VRCSEASONPRICING':
				$result = __('Pricing Modifications', 'vikrentcar');
				break;
			case 'VRCSPPROMOTIONLABEL':
				$result = __('Promotion', 'vikrentcar');
				break;
			case 'VRLEAVEDEPOSIT':
				$result = __('Leave a deposit of ', 'vikrentcar');
				break;
			case 'VRCTOTALREMAINING':
				$result = __('Remaining Balance', 'vikrentcar');
				break;
			case 'VRCONFIGEDITTMPLFILE':
				$result = __('Edit Template File', 'vikrentcar');
				break;
			case 'VRCONFIGEMAILTEMPLATE':
				$result = __('Customer Email', 'vikrentcar');
				break;
			case 'VRCONFIGPDFTEMPLATE':
				$result = __('Customer PDF', 'vikrentcar');
				break;
			case 'VRCONFIGPDFCHECKINTEMPLATE':
				$result = __('PDF Check-in', 'vikrentcar');
				break;
			case 'VRCONFIGPDFINVOICETEMPLATE':
				$result = __('PDF Invoice', 'vikrentcar');
				break;
			case 'VRCUPDTMPLFILEERR':
				$result = __('Error: empty or invalid Template File Path', 'vikrentcar');
				break;
			case 'VRCUPDTMPLFILENOBYTES':
				$result = __('Error: 0 bytes written on file', 'vikrentcar');
				break;
			case 'VRCUPDTMPLFILEOK':
				$result = __('Template File Successfully Updated', 'vikrentcar');
				break;
			case 'VRCEDITTMPLFILE':
				$result = __('Edit Template File Source Code', 'vikrentcar');
				break;
			case 'VRCTMPLFILENOTREAD':
				$result = __('Error reading the source code of the file', 'vikrentcar');
				break;
			case 'VRCSAVETMPLFILE':
				$result = __('Save & Write Source Code', 'vikrentcar');
				break;
			case 'VRCISNOMINATIVE':
				$result = __('Nominative', 'vikrentcar');
				break;
			case 'VRCISPHONENUMBER':
				$result = __('Phone Number', 'vikrentcar');
				break;
			case 'VRCDASHTODAYPICKUP':
				$result = __('Collecting Today', 'vikrentcar');
				break;
			case 'VRCDASHTODAYDROPOFF':
				$result = __('Returning Today', 'vikrentcar');
				break;
			case 'VRCDASHCARSLOCKED':
				$result = __('Cars Locked - Waiting for Confirmation', 'vikrentcar');
				break;
			case 'VRCDASHLOCKUNTIL':
				$result = __('Locked Until', 'vikrentcar');
				break;
			case 'VRCDASHUNLOCK':
				$result = __('Unlock', 'vikrentcar');
				break;
			case 'VRCDRIVERNOMINATIVE':
				$result = __('Driver Name', 'vikrentcar');
				break;
			case 'VRCONFIGTIMEFORMAT':
				$result = __('Time Format', 'vikrentcar');
				break;
			case 'VRCONFIGTIMEFORMATLAT':
				$result = __('24 Hours', 'vikrentcar');
				break;
			case 'VRCONFIGTIMEFORMATENG':
				$result = __('12 Hours AM/PM', 'vikrentcar');
				break;
			case 'VRCONFIGTAXSUMMARY':
				$result = __('Show Tax in Summary Only', 'vikrentcar');
				break;
			case 'VRCTODAYBOOKINGS':
				$result = __('Rentals for today at any time', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATECARS':
				$result = __('Cars', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATEOPTIONS':
				$result = __('Options, Taxes, Fees', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATECATEGORIES':
				$result = __('Categories', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATESPECIALPRICES':
				$result = __('Special Prices', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATETYPESPRICE':
				$result = __('Types of Price', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATEPAYMENTS':
				$result = __('Payment Methods', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATECFIELDS':
				$result = __('Custom Fields', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATECHARACTERISTICS':
				$result = __('Characteristics', 'vikrentcar');
				break;
			case 'VRCINIEXPLCOM_VIKRENTCAR_FRONT':
				$result = __('Component Front-End', 'vikrentcar');
				break;
			case 'VRCINIEXPLCOM_VIKRENTCAR_ADMIN':
				$result = __('Component Back-End', 'vikrentcar');
				break;
			case 'VRCINIEXPLCOM_VIKRENTCAR_ADMIN_SYS':
				$result = __('Component Back-End SYS', 'vikrentcar');
				break;
			case 'VRCINIEXPLMOD_VIKRENTCAR_SEARCH':
				$result = __('Search Module', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATETEXTS':
				$result = __('Texts', 'vikrentcar');
				break;
			case 'VRCXMLCONTENT':
				$result = __('Content', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATELOCATIONS':
				$result = __('Locations', 'vikrentcar');
				break;
			case 'VRCTOGGLEORDNOTES':
				$result = __('Administrator Notes', 'vikrentcar');
				break;
			case 'VRCUPDATEBTN':
				$result = __('Update', 'vikrentcar');
				break;
			case 'VRCEORDLBLDATESLOCS':
				$result = __('Dates, Car, Locations', 'vikrentcar');
				break;
			case 'VRCRENTCUSTRATEPLAN':
				$result = __('Rental Cost', 'vikrentcar');
				break;
			case 'VRCRENTCUSTRATEPLANADD':
				$result = __('Set Custom Rate', 'vikrentcar');
				break;
			case 'VRCRENTCUSTRATETAXHELP':
				$result = __('Custom Rates should always be inclusive of taxes', 'vikrentcar');
				break;
			case 'VRPEDITBUSYEXTRACNAME':
				$result = __('Service Name', 'vikrentcar');
				break;
			case 'VRPEDITBUSYEXTRACOSTS':
				$result = __('Extra Services', 'vikrentcar');
				break;
			case 'VRPEDITBUSYADDEXTRAC':
				$result = __('Add', 'vikrentcar');
				break;
			case 'VRCCONFIGCUSTCSSTPL':
				$result = __('Custom CSS Overrides', 'vikrentcar');
				break;
			case 'VRCANCELLED':
				$result = __('Cancelled', 'vikrentcar');
				break;
			case 'VRNEWPLACECLOSINGDAYSINGLE':
				$result = __('Single Day', 'vikrentcar');
				break;
			case 'VRNEWPLACECLOSINGDAYWEEK':
				$result = __('Every week', 'vikrentcar');
				break;
			case 'VRCPARAMHOURLYCAL':
				$result = __('Show Hourly Calendar', 'vikrentcar');
				break;
			case 'VREXPORTDATETYPE':
				$result = __('Date Filter', 'vikrentcar');
				break;
			case 'VREXPORTDATETYPETS':
				$result = __('Order Date', 'vikrentcar');
				break;
			case 'VREXPORTDATETYPEPICK':
				$result = __('Pickup Date', 'vikrentcar');
				break;
			case 'VRMENUGRAPHS':
				$result = __('Graphs & Statistics', 'vikrentcar');
				break;
			case 'VRCWEBSITECHANNEL':
				$result = __('Rental Orders', 'vikrentcar');
				break;
			case 'VRCSTATSMODETS':
				$result = __('Data based on orders creation date', 'vikrentcar');
				break;
			case 'VRCSTATSMODENIGHTS':
				$result = __('Data based on booked dates', 'vikrentcar');
				break;
			case 'VRCGRAPHTOTSALES':
				$result = __('Total Sales', 'vikrentcar');
				break;
			case 'VRCGRAPHTOTNIGHTS':
				$result = __('Total days booked: %d', 'vikrentcar');
				break;
			case 'VRCGRAPHAVGVALUES':
				$result = __('Average Values may be applied when the booked dates are not included in the dates filter', 'vikrentcar');
				break;
			case 'VRCGRAPHTOTNIGHTSLBL':
				$result = __('Days Booked', 'vikrentcar');
				break;
			case 'VRCGRAPHTOTOCCUPANCY':
				$result = __('Occupancy Rate: %s%%', 'vikrentcar');
				break;
			case 'VRCGRAPHTOTOCCUPANCYLBL':
				$result = __('Occupancy Rate', 'vikrentcar');
				break;
			case 'VRCGRAPHTOTUNITSLBL':
				$result = __('Total Units', 'vikrentcar');
				break;
			case 'VRSHORTMONTHONE':
				$result = __('Jan', 'vikrentcar');
				break;
			case 'VRSHORTMONTHTWO':
				$result = __('Feb', 'vikrentcar');
				break;
			case 'VRSHORTMONTHTHREE':
				$result = __('Mar', 'vikrentcar');
				break;
			case 'VRSHORTMONTHFOUR':
				$result = __('Apr', 'vikrentcar');
				break;
			case 'VRSHORTMONTHFIVE':
				$result = __('May', 'vikrentcar');
				break;
			case 'VRSHORTMONTHSIX':
				$result = __('Jun', 'vikrentcar');
				break;
			case 'VRSHORTMONTHSEVEN':
				$result = __('Jul', 'vikrentcar');
				break;
			case 'VRSHORTMONTHEIGHT':
				$result = __('Aug', 'vikrentcar');
				break;
			case 'VRSHORTMONTHNINE':
				$result = __('Sep', 'vikrentcar');
				break;
			case 'VRSHORTMONTHTEN':
				$result = __('Oct', 'vikrentcar');
				break;
			case 'VRSHORTMONTHELEVEN':
				$result = __('Nov', 'vikrentcar');
				break;
			case 'VRSHORTMONTHTWELVE':
				$result = __('Dec', 'vikrentcar');
				break;
			case 'VRCSTATSALLCARS':
				$result = __('- All Cars', 'vikrentcar');
				break;
			case 'VRNOBOOKINGSTATS':
				$result = __('No confirmed orders found for these dates. Reports cannot be generated.', 'vikrentcar');
				break;
			case 'VRCSTATSFOR':
				$result = __('%d Confirmed Orders over %d days', 'vikrentcar');
				break;
			case 'VRCSTATSTOPCOUNTRIES':
				$result = __('Top Countries', 'vikrentcar');
				break;
			case 'VRCSTATSTOTINCOME':
				$result = __('Total Gross Income', 'vikrentcar');
				break;
			case 'VRMAINGRAPHSTITLE':
				$result = __('Vik Rent Car - Graphs &amp; Statistics', 'vikrentcar');
				break;
			case 'VRCONFIGTRACKCODETEMPLATE':
				$result = __('Tracking Code', 'vikrentcar');
				break;
			case 'VRCONFIGCONVCODETEMPLATE':
				$result = __('Conversion Code', 'vikrentcar');
				break;
			case 'VRCONFIGSENDERMAIL':
				$result = __('Sender e-Mail', 'vikrentcar');
				break;
			case 'VRMAINTITLEUPDATEPROGRAM':
				$result = __('Vik Rent Car - Software Update', 'vikrentcar');
				break;
			case 'VRCHECKINGVERSION':
				$result = __('Checking Version...', 'vikrentcar');
				break;
			case 'VRDOWNLOADUPDATEBTN1':
				$result = __('Download Update & Install', 'vikrentcar');
				break;
			case 'VRDOWNLOADUPDATEBTN0':
				$result = __('Download & Re-Install', 'vikrentcar');
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
			case 'VRFILLCUSTFIELDS':
				$result = __('Assign Customer', 'vikrentcar');
				break;
			case 'VRAPPLY':
				$result = __('Apply', 'vikrentcar');
				break;
			case 'VRCSEARCHEXISTCUST':
				$result = __('Existing Customer', 'vikrentcar');
				break;
			case 'VRCSEARCHCUSTBY':
				$result = __('Search by PIN or Name', 'vikrentcar');
				break;
			case 'VRDBTEXTROOMCLOSED':
				$result = __('Car Closed', 'vikrentcar');
				break;
			case 'VRSUBMCLOSEROOM':
				$result = __('Close Car', 'vikrentcar');
				break;
			case 'VRCUSTOMERNOMINATIVE':
				$result = __('Customer Name', 'vikrentcar');
				break;
			case 'VRCBOOKDETTABDETAILS':
				$result = __('Order Details', 'vikrentcar');
				break;
			case 'VRCBOOKDETTABADMIN':
				$result = __('Administration', 'vikrentcar');
				break;
			case 'VRCBOOKINGCREATEDBY':
				$result = __('Order created by User ID %s', 'vikrentcar');
				break;
			case 'VRSENDEMAILACTION':
				$result = __('Send Custom Email', 'vikrentcar');
				break;
			case 'VRCUSTOMERPHONE':
				$result = __('Phone', 'vikrentcar');
				break;
			case 'VRCBOOKINGLANG':
				$result = __('Language', 'vikrentcar');
				break;
			case 'VRSENDEMAILCUSTSUBJ':
				$result = __('Subject', 'vikrentcar');
				break;
			case 'VRSENDEMAILCUSTCONT':
				$result = __('Message', 'vikrentcar');
				break;
			case 'VRSENDEMAILCUSTATTCH':
				$result = __('Attachment', 'vikrentcar');
				break;
			case 'VRSENDEMAILCUSTFROM':
				$result = __('From Address', 'vikrentcar');
				break;
			case 'VRSENDEMAILERRMISSDATA':
				$result = __('Missing required data for sending the email message.', 'vikrentcar');
				break;
			case 'VRSENDEMAILOK':
				$result = __('The message was sent successfully', 'vikrentcar');
				break;
			case 'VREMAILCUSTFROMTPL':
				$result = __('- Load text from Template -', 'vikrentcar');
				break;
			case 'VREMAILCUSTFROMTPLUSE':
				$result = __('Use Template', 'vikrentcar');
				break;
			case 'VREMAILCUSTFROMTPLRM':
				$result = __('Remove Template', 'vikrentcar');
				break;
			case 'VRSWITCHCWITH':
				$result = __('Switch Car', 'vikrentcar');
				break;
			case 'VRPEDITBUSYLOCATIONS':
				$result = __('Locations', 'vikrentcar');
				break;
			case 'VRPEDITBUSYERRNOFARES':
				$result = __('No Fares found for this car and for this number of days of rental. Unable to edit the reservation.', 'vikrentcar');
				break;
			case 'VRCMISSPRTYPECARH':
				$result = __('The car of this reservation has no rates defined. Make sure to set a rate, or the reservation will be incomplete.', 'vikrentcar');
				break;
			case 'VRCMENUMANAGEMENT':
				$result = __('Management', 'vikrentcar');
				break;
			case 'VRCMENUCUSTOMERS':
				$result = __('Customers', 'vikrentcar');
				break;
			case 'VRNOCUSTOMERS':
				$result = __('No Customers found', 'vikrentcar');
				break;
			case 'VRCUSTOMERFIRSTNAME':
				$result = __('First Name', 'vikrentcar');
				break;
			case 'VRCUSTOMERLASTNAME':
				$result = __('Last Name', 'vikrentcar');
				break;
			case 'VRCUSTOMEREMAIL':
				$result = __('eMail', 'vikrentcar');
				break;
			case 'VRCUSTOMERPHONE':
				$result = __('Phone', 'vikrentcar');
				break;
			case 'VRCUSTOMERCOUNTRY':
				$result = __('Country', 'vikrentcar');
				break;
			case 'VRCUSTOMERPIN':
				$result = __('PIN', 'vikrentcar');
				break;
			case 'VRCUSTOMERGENERATEPIN':
				$result = __('Generate PIN', 'vikrentcar');
				break;
			case 'VRMAINCUSTOMERSTITLE':
				$result = __('Vik Rent Car - Customers', 'vikrentcar');
				break;
			case 'VRMAINCUSTOMERNEW':
				$result = __('New', 'vikrentcar');
				break;
			case 'VRMAINCUSTOMEREDIT':
				$result = __('Edit', 'vikrentcar');
				break;
			case 'VRMAINCUSTOMERDEL':
				$result = __('Remove', 'vikrentcar');
				break;
			case 'VRMAINMANAGECUSTOMERTITLE':
				$result = __('Vik Rent Car - Customer Details', 'vikrentcar');
				break;
			case 'VRERRCUSTOMEREMAILEXISTS':
				$result = __('Customer with the same email address already exists', 'vikrentcar');
				break;
			case 'VRCUSTOMERSAVED':
				$result = __('Customer Saved Successfully', 'vikrentcar');
				break;
			case 'VRCONFIGENABLECUSTOMERPIN':
				$result = __('Enable Customers PIN Code', 'vikrentcar');
				break;
			case 'VRCUSTOMERTOTBOOKINGS':
				$result = __('Total Bookings', 'vikrentcar');
				break;
			case 'VRYOURPIN':
				$result = __('PIN Code', 'vikrentcar');
				break;
			case 'VRCCSVEXPCUSTOMERS':
				$result = __('CSV Export', 'vikrentcar');
				break;
			case 'VRCCSVEXPCUSTOMERSGET':
				$result = __('Download CSV Export', 'vikrentcar');
				break;
			case 'VRCANYCOUNTRY':
				$result = __('-- Any Country --', 'vikrentcar');
				break;
			case 'VRCCUSTOMEREXPSEL':
				$result = __('Export Information about %d selected Customers', 'vikrentcar');
				break;
			case 'VRCCUSTOMEREXPALL':
				$result = __('Export Customers Information', 'vikrentcar');
				break;
			case 'VRCMAINEXPCUSTOMERSTITLE':
				$result = __('Vik Rent Car - Export Customers Information', 'vikrentcar');
				break;
			case 'VRCCUSTOMEREXPNOTES':
				$result = __('Include Notes', 'vikrentcar');
				break;
			case 'VRCCUSTOMEREXPSCANIMG':
				$result = __('Include ID Image Scan URL', 'vikrentcar');
				break;
			case 'VRCCUSTOMEREXPPIN':
				$result = __('Include PIN Code', 'vikrentcar');
				break;
			case 'VRCNORECORDSCSVCUSTOMERS':
				$result = __('No customer records to export', 'vikrentcar');
				break;
			case 'VRCCUSTOMERDETAILS':
				$result = __('Customer Details', 'vikrentcar');
				break;
			case 'VRCUSTOMERADDRESS':
				$result = __('Address', 'vikrentcar');
				break;
			case 'VRCUSTOMERCITY':
				$result = __('City', 'vikrentcar');
				break;
			case 'VRCUSTOMERZIP':
				$result = __('ZIP', 'vikrentcar');
				break;
			case 'VRCUSTOMERDOCTYPE':
				$result = __('ID Type', 'vikrentcar');
				break;
			case 'VRCUSTOMERDOCNUM':
				$result = __('ID Number', 'vikrentcar');
				break;
			case 'VRCUSTOMERDOCIMG':
				$result = __('ID Scan Image', 'vikrentcar');
				break;
			case 'VRCUSTOMERNOTES':
				$result = __('Notes', 'vikrentcar');
				break;
			case 'VRCUSTOMERCOMPANY':
				$result = __('Company Name', 'vikrentcar');
				break;
			case 'VRCUSTOMERCOMPANYVAT':
				$result = __('VAT ID', 'vikrentcar');
				break;
			case 'VRCISCOMPANY':
				$result = __('Company Name', 'vikrentcar');
				break;
			case 'VRCISVAT':
				$result = __('VAT ID', 'vikrentcar');
				break;
			case 'VRCUSTOMERGENDER':
				$result = __('Gender', 'vikrentcar');
				break;
			case 'VRCUSTOMERGENDERM':
				$result = __('Male', 'vikrentcar');
				break;
			case 'VRCUSTOMERGENDERF':
				$result = __('Female', 'vikrentcar');
				break;
			case 'VRCUSTOMERBDATE':
				$result = __('Date of Birth', 'vikrentcar');
				break;
			case 'VRCUSTOMERPBIRTH':
				$result = __('Place of Birth', 'vikrentcar');
				break;
			case 'VRCISADDRESS':
				$result = __('Address', 'vikrentcar');
				break;
			case 'VRCISCITY':
				$result = __('City', 'vikrentcar');
				break;
			case 'VRCISZIP':
				$result = __('ZIP', 'vikrentcar');
				break;
			case 'VRCLOADFA':
				$result = __('Load Font Awesome', 'vikrentcar');
				break;
			case 'VRCLOCADDRESS':
				$result = __('Location Address', 'vikrentcar');
				break;
			case 'VRCVIEWBOOKINGDET':
				$result = __('View Details', 'vikrentcar');
				break;
			case 'VRCSENDEMAILSWHEN':
				$result = __('Send Emails When', 'vikrentcar');
				break;
			case 'VRCSENDEMAILSWHENBOTH':
				$result = __('Order is Pending or Confirmed', 'vikrentcar');
				break;
			case 'VRCSENDEMAILSWHENCONF':
				$result = __('Order is Confirmed', 'vikrentcar');
				break;
			case 'VRCICALEVENDDTTYPE':
				$result = __('iCal Events End Date', 'vikrentcar');
				break;
			case 'VRCICALEVENDDTTYPEHELP':
				$result = __('Choose whether a rental order should be displayed in the external calendars as one day (pick up date) or with all the consecutive dates. For example: a rental of 3 days can be displayed in the external calendar system as occupying just one day (the pick up date) or all days until the drop off date. Choose the method you prefer.', 'vikrentcar');
				break;
			case 'VRCICALEVENDDTPICK':
				$result = __('Pick up Date', 'vikrentcar');
				break;
			case 'VRCICALEVENDDTDROP':
				$result = __('Drop off Date', 'vikrentcar');
				break;
			case 'VRCARFILTER':
				$result = __('Filter by Car', 'vikrentcar');
				break;
			case 'VRFILTERBYPAYMENT':
				$result = __('Filter by Payment', 'vikrentcar');
				break;
			case 'VRFILTERBYSTATUS':
				$result = __('Filter by Status', 'vikrentcar');
				break;
			case 'VRCSTOPRENTALSTATUS':
				$result = __('Stop Rentals', 'vikrentcar');
				break;
			case 'VRFILTERBYDATES':
				$result = __('Filter by Date', 'vikrentcar');
				break;
			case 'VRPVIEWORDERSSEARCHSUBM':
				$result = __('Filter Orders', 'vikrentcar');
				break;
			case 'VRCQUICKRESLOCATIONS':
				$result = __('Locations', 'vikrentcar');
				break;
			case 'VRCPRATTRHELP':
				$result = __('The attribute is an additional information you can pass to the Type of Price for any number of days of rental. It is NOT a mandatory field and it can be left empty. An example of attribute could be &quot;Km Included&quot;. From the page Fares Table, you will be able to specify the value for the attribute for any number of days of rental. For example, from 1 to 7 days: &quot;100Km/day&quot;. From 8 to 14 days: &quot;150Km/day&quot;. The attribute will be visible to the customer during the reservation process.', 'vikrentcar');
				break;
			case 'VRCMENURATESOVERVIEW':
				$result = __('Fares Overview', 'vikrentcar');
				break;
			case 'VRMDAYFRIST':
				$result = __('st', 'vikrentcar');
				break;
			case 'VRMDAYSECOND':
				$result = __('nd', 'vikrentcar');
				break;
			case 'VRMDAYTHIRD':
				$result = __('rd', 'vikrentcar');
				break;
			case 'VRMDAYNUMGEN':
				$result = __('th', 'vikrentcar');
				break;
			case 'VRMAINRATESOVERVIEWTITLE':
				$result = __('Vik Rent Car - Rates Overview', 'vikrentcar');
				break;
			case 'VRMENURESTRICTIONS':
				$result = __('Restrictions', 'vikrentcar');
				break;
			case 'VRRATESOVWRATESCALCULATOR':
				$result = __('Rates Calculator', 'vikrentcar');
				break;
			case 'VRMAINRESTRICTIONSTITLE':
				$result = __('Vik Rent Car - Restrictions', 'vikrentcar');
				break;
			case 'VRMAINNEWRESTRICTIONTITLE':
				$result = __('Vik Rent Car - New Restriction', 'vikrentcar');
				break;
			case 'VRMAINEDITRESTRICTIONTITLE':
				$result = __('Vik Rent Car - Edit Restriction', 'vikrentcar');
				break;
			case 'VRMAINRESTRICTIONNEW':
				$result = __('New Restriction', 'vikrentcar');
				break;
			case 'VRMAINRESTRICTIONEDIT':
				$result = __('Edit', 'vikrentcar');
				break;
			case 'VRMAINRESTRICTIONDEL':
				$result = __('Remove', 'vikrentcar');
				break;
			case 'VRNORESTRICTIONSFOUND':
				$result = __('No Restrictions found.', 'vikrentcar');
				break;
			case 'VRPVIEWRESTRICTIONSONE':
				$result = __('Name', 'vikrentcar');
				break;
			case 'VRPVIEWRESTRICTIONSTWO':
				$result = __('Month', 'vikrentcar');
				break;
			case 'VRPVIEWRESTRICTIONSTHREE':
				$result = __('Arrival Week Day', 'vikrentcar');
				break;
			case 'VRPVIEWRESTRICTIONSFOUR':
				$result = __('Min Num of Days', 'vikrentcar');
				break;
			case 'VRPVIEWRESTRICTIONSFIVE':
				$result = __('Max Num of Days', 'vikrentcar');
				break;
			case 'VRRESTRICTIONSHELPTITLE':
				$result = __('Restrictions', 'vikrentcar');
				break;
			case 'VRRESTRICTIONSSHELP':
				$result = __('With the restrictions you can limit the minimum rental period for a specific month of the Year or for a certain range of dates and optionally force the pickup Day of the Week. For example you can create a restriction for your car in August, forcing the pickup day to Saturday and the minimum rental period to 7 days, 14 days etc.. The minimum number of days will be set to 1 in case it is left empty.', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONONE':
				$result = __('Month', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONWDAY':
				$result = __('Force Arrival Week Day', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONNAME':
				$result = __('Restriction Name', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONMINLOS':
				$result = __('Min Num of Days', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONMULTIPLYMINLOS':
				$result = __('Multiply Min Num of Days', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONMULTIPLYMINLOSHELP':
				$result = __('If this setting is enabled the minimum number of days will be multiplied every time this is passed. For example if you want to force the Pickup day to Saturday and the Drop-off day must still be on Saturday, you have to set the Minimum Number of Days to 7 and if this setting is enabled, 8, 9, 10, 11, 12 and 13 days of rental will not be allowed but only 14, 21, 28 etc. days will be allowed. This is useful if you want to give your cars only for weeks. The Maximum number of Days is automatically calculated from the Rates Table of each car, infact, if a car does not have a rate for 28 days, this car will not show up in the results so it will not be available. In case you want the calendar to force the Maximum Number of Days for this month, set a number of MaxLOS below.', 'vikrentcar');
				break;
			case 'VRUSELESSRESTRICTION':
				$result = __('Error, the restriction would be useless without an Arrival Week Day, without the CTA or CTD and the Minimum Num of Days as 1 which is the default MinLOS', 'vikrentcar');
				break;
			case 'VRRESTRICTIONSAVED':
				$result = __('Restriction Saved Successfully', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONALLCOMBO':
				$result = __('Forced Combinations:', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONALLCOMBOHELP':
				$result = __('if none selected, any check-out week day in accordance with the max and min number of days will be accepted', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONALLCARS':
				$result = __('Apply to all Cars', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONCARSAFF':
				$result = __('Cars affected by this Restriction:', 'vikrentcar');
				break;
			case 'VRRESTRLISTCARS':
				$result = __('Cars', 'vikrentcar');
				break;
			case 'VRRESTRALLCARS':
				$result = __('ALL', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONOR':
				$result = __('or', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONDATERANGE':
				$result = __('Dates Range', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONDFROMRANGE':
				$result = __('From Date', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONDTORANGE':
				$result = __('To Date', 'vikrentcar');
				break;
			case 'VRRESTRICTIONERRDRANGE':
				$result = __('Error: Restrictions must have a month or a dates range, from and to.', 'vikrentcar');
				break;
			case 'VRRESTRICTIONSDRANGE':
				$result = __('Dates Range', 'vikrentcar');
				break;
			case 'VRRESTRICTIONMONTHEXISTS':
				$result = __('Error, a restriction for the selected month already exists.', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONMAXLOS':
				$result = __('Max Num of Days', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONSETCTA':
				$result = __('Set Days Closed to Arrival (CTA)', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONSETCTD':
				$result = __('Set Days Closed to Departure (CTD)', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONWDAYSCTA':
				$result = __('Week days closed to arrival', 'vikrentcar');
				break;
			case 'VRNEWRESTRICTIONWDAYSCTD':
				$result = __('Week days closed to departure', 'vikrentcar');
				break;
			case 'VRPVIEWRESTRICTIONSCTA':
				$result = __('CTA Week Days', 'vikrentcar');
				break;
			case 'VRPVIEWRESTRICTIONSCTD':
				$result = __('CTD Week Days', 'vikrentcar');
				break;
			case 'VRCRESTRWDAYSCTA':
				$result = __('Week Days Closed to Arrival', 'vikrentcar');
				break;
			case 'VRCRESTRWDAYSCTD':
				$result = __('Week Days Closed to Departure', 'vikrentcar');
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
			case 'VRRATESOVWCAR':
				$result = __('Car', 'vikrentcar');
				break;
			case 'VRRATESOVWNUMNIGHTSACT':
				$result = __('Rental Period', 'vikrentcar');
				break;
			case 'VRRATESOVWAPPLYLOS':
				$result = __('Apply', 'vikrentcar');
				break;
			case 'VRRATESOVWRATESCALCULATORCALC':
				$result = __('Calculate', 'vikrentcar');
				break;
			case 'VRRATESOVWRATESCALCULATORCALCING':
				$result = __('Calculating...', 'vikrentcar');
				break;
			case 'VRRATESOVWTABLOS':
				$result = __('Length of Stay Pricing Overview', 'vikrentcar');
				break;
			case 'VRRATESOVWTABCALENDAR':
				$result = __('Calendar Pricing Overview', 'vikrentcar');
				break;
			case 'VRROVWSELPERIOD':
				$result = __('Select Period', 'vikrentcar');
				break;
			case 'VRROVWSELPERIODFROM':
				$result = __('From', 'vikrentcar');
				break;
			case 'VRROVWSELPERIODTO':
				$result = __('To', 'vikrentcar');
				break;
			case 'VRROVWSELRPLAN':
				$result = __('Rate Plan', 'vikrentcar');
				break;
			case 'VRSEASONANYYEARS':
				$result = __('Valid any Year', 'vikrentcar');
				break;
			case 'VRSEASONBASEDLOS':
				$result = __('Based on Rental Period', 'vikrentcar');
				break;
			case 'VRSEASONPERDAY':
				$result = __('per day', 'vikrentcar');
				break;
			case 'VRSEASONCALNUMDAY':
				$result = __('%d Day', 'vikrentcar');
				break;
			case 'VRSEASONCALNUMDAYS':
				$result = __('%d Days', 'vikrentcar');
				break;
			case 'VRSEASONSCALOFFSEASONPRICES':
				$result = __('Off-Season Prices', 'vikrentcar');
				break;
			case 'VRRESTRMINLOS':
				$result = __('Min. Days', 'vikrentcar');
				break;
			case 'VRRESTRMAXLOS':
				$result = __('Max. Days', 'vikrentcar');
				break;
			case 'VRRESTRARRIVWDAY':
				$result = __('Pick up Week Day', 'vikrentcar');
				break;
			case 'VRRESTRARRIVWDAYS':
				$result = __('Pick up Week Days', 'vikrentcar');
				break;
			case 'VRRATESOVWSETNEWRATE':
				$result = __('Set New Rate', 'vikrentcar');
				break;
			case 'VRRATESOVWERRNEWRATE':
				$result = __('Error while setting new rates. Missing data', 'vikrentcar');
				break;
			case 'VRRATESOVWERRNORATES':
				$result = __('Error while setting new rates. No rates', 'vikrentcar');
				break;
			case 'VRRATESOVWERRNORATESMOD':
				$result = __('Error: no changes needed for the selected rates', 'vikrentcar');
				break;
			case 'VRRATESOVWCLOSEOPENRRP':
				$result = __('Close/Open Rate Plan', 'vikrentcar');
				break;
			case 'VRRATESOVWCLOSERRP':
				$result = __('Close Rate Plan', 'vikrentcar');
				break;
			case 'VRRATESOVWOPENRRP':
				$result = __('Open Rate Plan', 'vikrentcar');
				break;
			case 'VRRATESOVWERRMODRPLANS':
				$result = __('Error while modifying rate plans. Missing data', 'vikrentcar');
				break;
			case 'VRRATESOVWOPENSPL':
				$result = __('Special Price rule #%d', 'vikrentcar');
				break;
			case 'VRCALCRATESCARNOTAVAILCOMBO':
				$result = __('The Car is not available or has no Rates from %s to %s.', 'vikrentcar');
				break;
			case 'VRCALCRATESTOT':
				$result = __('Total', 'vikrentcar');
				break;
			case 'VRCALCRATESSPAFFDAYS':
				$result = __('Days modified by Special Prices:', 'vikrentcar');
				break;
			case 'VRCPARAMREQINFO':
				$result = __('Enable Request Information', 'vikrentcar');
				break;
			case 'VRCPARAMREQINFOHELP':
				$result = __('If enabled, from the Car details page in the front-end, it will be possible for every user to submit an information request through a contact form.', 'vikrentcar');
				break;
			case 'VRCPLACEOVROPENTIME':
				$result = __('Override Opening Time', 'vikrentcar');
				break;
			case 'VRCPLACEOVROPENTIMEHELP':
				$result = __('The default Opening Time can be changed on some days of the week. Closing days, for either festivities or weekly closure, can be defined from the apposite parameter Closing Dates. If the Opening Time does not change from one day to another, keep this setting empty.', 'vikrentcar');
				break;
			case 'VRCRECALCORDTOTCONF':
				$result = __('Do you want the system to re-calculate the new order total?', 'vikrentcar');
				break;
			case 'VRCVERVIEWUBOOKEDFILT':
				$result = __('Units booked', 'vikrentcar');
				break;
			case 'VRCVERVIEWULEFTFILT':
				$result = __('Units left', 'vikrentcar');
				break;
			case 'VRCPICKONDROP':
				$result = __('Allow Pick Ups on Drop Offs', 'vikrentcar');
				break;
			case 'VRCPICKONDROPHELP':
				$result = __('If enabled, and if the setting \'Dropped Off car is available after N hours\' is set to 0, the system will allow pick ups at times when the same car is being dropped off by another rental order. Otherwise, the car would become available for pick up at the very next minute.', 'vikrentcar');
				break;
			case 'VRCOVERVIEWTOGGLESUBCAR':
				$result = __('Toggle Availability by Units', 'vikrentcar');
				break;
			case 'VRCORDERID':
				$result = __('Order ID', 'vikrentcar');
				break;
			case 'VRCASSIGNNEWCUST':
				$result = __('Assign new customer', 'vikrentcar');
				break;
			case 'VRCCREATENEWCUST':
				$result = __('Create new customer', 'vikrentcar');
				break;
			case 'VRCASSIGNNEWCUSTCONF':
				$result = __('Do you want to assign this new customer to the order?', 'vikrentcar');
				break;
			case 'VRCONFIGLOGOBACKEND':
				$result = __('Back-end Logo (180px)', 'vikrentcar');
				break;
			case 'VRCMENUTRACKINGS':
				$result = __('Statistics Tracking', 'vikrentcar');
				break;
			case 'VRCMAINTRACKINGSTITLE':
				$result = __('Vik Rent Car - Statistics Tracking', 'vikrentcar');
				break;
			case 'VRCNOTRACKINGS':
				$result = __('No data available.', 'vikrentcar');
				break;
			case 'VRCTRKLASTDT':
				$result = __('Last Visit', 'vikrentcar');
				break;
			case 'VRCTRKFIRSTDT':
				$result = __('First Visit', 'vikrentcar');
				break;
			case 'VRCTRKPUBLISHED':
				$result = __('Tracking Status', 'vikrentcar');
				break;
			case 'VRCTRKGEOINFO':
				$result = __('Geo Info', 'vikrentcar');
				break;
			case 'VRCTRKMAKEAVAIL':
				$result = __('Enable tracking for this visitor', 'vikrentcar');
				break;
			case 'VRCTRKMAKENOTAVAIL':
				$result = __('Disable tracking for this visitor', 'vikrentcar');
				break;
			case 'VRCANONYMOUS':
				$result = __('Anonymous', 'vikrentcar');
				break;
			case 'VRCTRKDEVICE':
				$result = __('Device', 'vikrentcar');
				break;
			case 'VRCTRKTRACKTIME':
				$result = __('Tracking Time', 'vikrentcar');
				break;
			case 'VRCTRKBOOKINGDATES':
				$result = __('Rental Dates', 'vikrentcar');
				break;
			case 'VRCTRKCARSRATES':
				$result = __('Cars and Rates', 'vikrentcar');
				break;
			case 'VRCTRKTGLPUBLISHED':
				$result = __('Invert Tracking Status', 'vikrentcar');
				break;
			case 'VRCTRKFILTTRKDATES':
				$result = __('Tracking Dates', 'vikrentcar');
				break;
			case 'VRCTRKFILTRES':
				$result = __('Filter Results', 'vikrentcar');
				break;
			case 'VRCCOUNTRYFILTER':
				$result = __('Filter by Country', 'vikrentcar');
				break;
			case 'VRCTRKDIFFSECS':
				$result = __('seconds', 'vikrentcar');
				break;
			case 'VRCTRKDIFFMINS':
				$result = __('minutes', 'vikrentcar');
				break;
			case 'VRCTRKSETTINGS':
				$result = __('Tracking Settings', 'vikrentcar');
				break;
			case 'VRCTRKENABLED':
				$result = __('Tracking Enabled', 'vikrentcar');
				break;
			case 'VRCTRKDISABLED':
				$result = __('Tracking is disabled', 'vikrentcar');
				break;
			case 'VRCMAINTRKSETTSTITLE':
				$result = __('Vik Rent Car - Tracking Settings', 'vikrentcar');
				break;
			case 'VRCTRKCOOKIERFRDUR':
				$result = __('Referrer Cookie Duration', 'vikrentcar');
				break;
			case 'VRCTRKCOOKIERFRDURHELP':
				$result = __('The Referrer is the system that redirects/sends the visitor to your website. It could be a search engine, a social network or a marketing campaign. This value is not always available and it may be empty. The duration of the cookie defines for how long the visitor should be assigned to a certain referrer for any eventual conversion made after the first visit. The minimum duration should be greater than zero.', 'vikrentcar');
				break;
			case 'VRCTRKCAMPAIGNS':
				$result = __('Tracking Campaigns', 'vikrentcar');
				break;
			case 'VRCTRKCAMPAIGNSHELP':
				$result = __('You can add custom tracking campaigns to obtain a specific referrer for each tracking. This is useful to keep track of the provenience of a specific visitor. For example, if you are sending newsletters or marketing emails to your customers, you can include such instructions in the links and track who clicked on them and then searched for a car. The same function can be used to track other kind of marketing campaigns, such as ones from social networks or analytics techniques used by search engines. To set up a custom campaign rule you need to specify a request key (make sure to not use preserved request keys, numbers are always better), optionally a request value fort the key, and the name of the campaign that will be used as referrer for the trackings.', 'vikrentcar');
				break;
			case 'VRCTRKADDCAMPAIGN':
				$result = __('Add Campaign', 'vikrentcar');
				break;
			case 'VRCTRKCAMPAIGNKEY':
				$result = __('Request Key', 'vikrentcar');
				break;
			case 'VRCTRKCAMPAIGNVAL':
				$result = __('Request Key Value', 'vikrentcar');
				break;
			case 'VRCTRKCAMPAIGNNAME':
				$result = __('Referrer Name', 'vikrentcar');
				break;
			case 'VRCREFERRERFILTER':
				$result = __('Filter by Referrer', 'vikrentcar');
				break;
			case 'VRCTRKBOOKCONV':
				$result = __('Booking Conversion', 'vikrentcar');
				break;
			case 'VRCTRKREFERRER':
				$result = __('Referrer', 'vikrentcar');
				break;
			case 'VRCTRKVISITORS':
				$result = __('Visitors', 'vikrentcar');
				break;
			case 'VRCTRKCONVRATES':
				$result = __('Conversion Rates', 'vikrentcar');
				break;
			case 'VRCTRKREQSNUM':
				$result = __('Request(s)', 'vikrentcar');
				break;
			case 'VRCTRKVISSNUM':
				$result = __('Visitor(s)', 'vikrentcar');
				break;
			case 'VRCTRKMOSTDEMNIGHTS':
				$result = __('Most Demanded Days', 'vikrentcar');
				break;
			case 'VRCTRKAVGVALS':
				$result = __('Average Values', 'vikrentcar');
				break;
			case 'VRCTRKTOTVISS':
				$result = __('Total Visitors', 'vikrentcar');
				break;
			case 'VRCTRKAVGCONVRATE':
				$result = __('Average Conversion Rate', 'vikrentcar');
				break;
			case 'VRCTRKAVGCONVRATEHELP':
				$result = __('This percentage value is calculated proportionally by taking into account the total number of visitors and the total numbers of bookings. It shows how many visitors completed the reservation process by generating a booking.', 'vikrentcar');
				break;
			case 'VRCTRKAVGLOS':
				$result = __('Average Length of Rent', 'vikrentcar');
				break;
			case 'VRCTRKBESTREFERRERS':
				$result = __('Best Referrers', 'vikrentcar');
				break;
			case 'VRCTRKCOOKIEEXPL':
				$result = __('The Statistics Tracking functions use cookies to store information about the visitor\'s fingerprint and referrer. These cookies are sent to the visitors with the sole purpose of knowing the dates/cars/rate plans they search on this website. The cookies do not contain any personal information. By default, such cookies are not shared with any third party system. It may be necessary to inform your visitors of the usage you make of these internal tracking cookies.', 'vikrentcar');
				break;
			case 'VRCFILTERDATEIN':
				$result = __('Pick up Date', 'vikrentcar');
				break;
			case 'VRCFILTERDATEOUT':
				$result = __('Drop off Date', 'vikrentcar');
				break;
			case 'VRCCUSTOMER':
				$result = __('Customer', 'vikrentcar');
				break;
			case 'VRSAVENEW':
				$result = __('Save &amp; New', 'vikrentcar');
				break;
			case 'ORDER_TERMSCONDITIONS':
				$result = __('I agree to the terms and conditions', 'vikrentcar');
				break;
			case 'VRCCONFIGURETASK':
				$result = __('Configure', 'vikrentcar');
				break;
			case 'VRCADMINLEGENDDETAILS':
				$result = __('Details', 'vikrentcar');
				break;
			case 'VRCADMINLEGENDSETTINGS':
				$result = __('Settings', 'vikrentcar');
				break;
			case 'VRCRESTRREPEATONWDAYS':
				$result = __('Repeat restriction every %s', 'vikrentcar');
				break;
			case 'VRCRESTRREPEATUNTIL':
				$result = __('Repeat until', 'vikrentcar');
				break;
			case 'VRCCARSASSIGNED':
				$result = __('Cars Assigned', 'vikrentcar');
				break;
			case 'VRCOPTASSTOXCARS':
				$result = __('This option is assigned to %d cars over %d.', 'vikrentcar');
				break;
			case 'VRCSUCCUPDOPTION':
				$result = __('Car Option updated successfully', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFFLAG':
				$result = __('Type Flag', 'vikrentcar');
				break;
			case 'VRNEWCUSTOMFFLAGHELP':
				$result = __('There are several sub-types of fields that tell the system what kind of information was collected from the customer. Choose the appropriate type and remember to only create one field of type eMail that will be used for the notifications.', 'vikrentcar');
				break;
			case 'VRCSPWDAYSHELP':
				$result = __('Selecting no week days equals to selecting all 7 week days', 'vikrentcar');
				break;
			case 'VRCSPNAMEHELP':
				$result = __('The name of this pricing rule. Visible only if &quot;Promotion&quot; enabled. Can be left empty', 'vikrentcar');
				break;
			case 'VRCSPYEARTIEDHELP':
				$result = __('If disabled, the pricing rule will be applied on the selected range of dates regardless of the year', 'vikrentcar');
				break;
			case 'VRCSPONLCKINHELP':
				$result = __('If enabled, the rule will be applied only if the pick-up date for the rental is included in the range of dates', 'vikrentcar');
				break;
			case 'VRCSPTPROMOHELP':
				$result = __('Make this pricing rule a &quot;Promotion&quot; to display it in the front-end booking process', 'vikrentcar');
				break;
			case 'VRCPROMOTEXTHELP':
				$result = __('The (optional) information/description text of your promotion', 'vikrentcar');
				break;
			case 'VRCPROMOWARNNODATES':
				$result = __('A range of dates is mandatory to create a promotion', 'vikrentcar');
				break;
			case 'VRCPROMOVALIDITYHELP':
				$result = __('If this value is set to a number greater than zero, this promotion will be valid only for early bookings. If you need to apply the promotion only to those who book N days in advance, then you should set the number of days in advance from the apposite input field. Otherwise, you should keep this setting to 0. This setting is not for Last Minute promotions, but rather for Early Bird promotions.', 'vikrentcar');
				break;
			case 'VRCPROMOLASTMINUTE':
				$result = __('Last Minute validity', 'vikrentcar');
				break;
			case 'VRCPROMOLASTMINUTEHELP':
				$result = __('If you are willing to apply discounts only to last minute bookings, then you should provide a number of days and/or hours for the validity of the promotion. If the time remaining to the pickup from the booking date is less than the limit you defined, the promotion will be applied.', 'vikrentcar');
				break;
			case 'VRCPROMOFORCEMINLOS':
				$result = __('Force minimum length of rent', 'vikrentcar');
				break;
			case 'VRCPROMOONFINALPRICE':
				$result = __('Apply on cars final cost', 'vikrentcar');
				break;
			case 'VRCPROMOONFINALPRICEHELP':
				$result = __('This setting will determine how the promotion will be applied onto the cars costs', 'vikrentcar');
				break;
			case 'VRCPROMOONFINALPRICETXT':
				$result = __('All special pricing rules are applied on the cars base costs as a cumulative charge or discount even in case of multiple rules applied on the same rental dates. This algorithm follows the OpenTravel (OTA) standards, and here is an example of how two special pricing rules are typically applied on the bases costs to obtain the final price:<br/><br/><ul><li>Car base cost = 80/day</li><li>Reservation for 3 days</li><li>One Special Price sets a charge of 20/day to obtain a cost of 100/day</li><li>One Last-Minute promotion applies a 10% off</li></ul><br/><strong>Calculation of final price</strong><br/><ul><li>1st day (80 + 20 - 8) = 92</li><li>2nd day (80 + 20 - 8) = 92</li><li>3rd day (80 + 20 - 8) = 92</li><li><u>Final price</u> 92 * 3 = 276</li></ul><br/>With this default calculation method, the 10% off promotion has been applied cumulatively on the car base cost for each day affected.<br/>If the parameter <i>Apply on cars final cost</i> was enabled, the calculation would be performed with the following method:<br/><ul><li>1st day (80 + 20) = 100</li><li>2nd day (80 + 20) = 100</li><li>3rd day (80 + 20) = 100</li><li><u>Final price before promotion</u> 100 * 3 = 300</li><li><u>Promotion applied on final cost</u> 300 - 10% = 270</li></ul><br/>You should choose the calculation method that best fits your needs. Applying promotions on the final price for specific dates is usually more handy, but you can choose to adopt the default calculation method like for all the other special pricing rules.', 'vikrentcar');
				break;
			case 'VRCPREVIEW':
				$result = __('Preview', 'vikrentcar');
				break;
			case 'VRCWIZARDTARIFFSMESS':
				$result = __('Please specify the base-cost per day for each rate plan.', 'vikrentcar');
				break;
			case 'VRCWIZARDTARIFFSHELP':
				$result = __('This should be the rental cost applied for the longer period of the year. You will be able to set later any hourly rate, as well as some seasonal pricing or different costs for some dates of the year.', 'vikrentcar');
				break;
			case 'VRCWIZARDTARIFFSWHTC':
				$result = __('What\'s the starting rental cost per day for your car?', 'vikrentcar');
				break;
			case 'VRCTOGGLEWIZARD':
				$result = __('Open Wizard', 'vikrentcar');
				break;
			case 'VRCDESCRIPTIONS':
				$result = __('Descriptions', 'vikrentcar');
				break;
			case 'VRCMAILSUBJECT':
				$result = __('Your reservation at %s', 'vikrentcar');
				break;
			case 'VRCONFIGATTACHICAL':
				$result = __('Attach iCal Reminder', 'vikrentcar');
				break;
			case 'VRCONFIGATTACHICALHELP':
				$result = __('If enabled, a calendar reminder in iCal format will be attached to the confirmation email for the customer and/or the administrator. This is useful to save the event on any calendar application of any device.', 'vikrentcar');
				break;
			case 'VRCONFIGSENDTOADMIN':
				$result = __('Administrator', 'vikrentcar');
				break;
			case 'VRCONFIGSENDTOCUSTOMER':
				$result = __('Customer', 'vikrentcar');
				break;
			case 'VRCNEWORDERID':
				$result = __('New Order #%s', 'vikrentcar');
				break;
			case 'VRCPREFCOUNTRIESORD':
				$result = __('Preferred Countries Ordering', 'vikrentcar');
				break;
			case 'VRCPREFCOUNTRIESORDHELP':
				$result = __('The Preferred Countries are used to build input fields to collect phone numbers. These countries are taken from the installed languages on your website, and they will be used to display some countries at the top of the list next to each input field of type phone number. To add custom countries or to remove some, click the edit icon and enter the comma separated alpha-2 country codes (ISO 3166-1).', 'vikrentcar');
				break;
			case 'VRCBOOKNOW':
				$result = __('Book Now', 'vikrentcar');
				break;
			case 'VRCMENUADV':
				$result = __('Advanced', 'vikrentcar');
				break;
			case 'VRCMENUCRONS':
				$result = __('Scheduled Cron Jobs', 'vikrentcar');
				break;
			case 'VRCMENUPMSREPORTS':
				$result = __('Reports', 'vikrentcar');
				break;
			case 'VRCXMLTRANSLATECRONJOBS':
				$result = __('Scheduled Cron Jobs', 'vikrentcar');
				break;
			case 'VRCCONFIGCRONKEY':
				$result = __('Cron Jobs Secret Key', 'vikrentcar');
				break;
			case 'VRCMAINCRONSTITLE':
				$result = __('Vik Rent Car - Scheduled Cron Jobs', 'vikrentcar');
				break;
			case 'VRCMAINCRONNEW':
				$result = __('New Cron Job', 'vikrentcar');
				break;
			case 'VRCMAINCRONEDIT':
				$result = __('Edit', 'vikrentcar');
				break;
			case 'VRCMAINCRONDEL':
				$result = __('Remove', 'vikrentcar');
				break;
			case 'VRCNOCRONS':
				$result = __('No Cron Jobs currently set up or scheduled.', 'vikrentcar');
				break;
			case 'VRCCRONNAME':
				$result = __('Cron Job Name', 'vikrentcar');
				break;
			case 'VRCCRONCLASS':
				$result = __('Class File', 'vikrentcar');
				break;
			case 'VRCCRONLASTEXEC':
				$result = __('Last Execution', 'vikrentcar');
				break;
			case 'VRCCRONPUBLISHED':
				$result = __('Published', 'vikrentcar');
				break;
			case 'VRCCRONSAVED':
				$result = __('Cron Job Saved!', 'vikrentcar');
				break;
			case 'VRCCRONUPDATED':
				$result = __('Cron Job Updated!', 'vikrentcar');
				break;
			case 'VRCCRONLOGS':
				$result = __('Execution Logs', 'vikrentcar');
				break;
			case 'VRCCRONACTIONS':
				$result = __('Actions', 'vikrentcar');
				break;
			case 'VRCCRONACTION':
				$result = __('Execute', 'vikrentcar');
				break;
			case 'VRCCRONEXECRESULT':
				$result = __('Cron Job Result', 'vikrentcar');
				break;
			case 'VRCCRONPARAMS':
				$result = __('Parameters', 'vikrentcar');
				break;
			case 'VRCCRONGETCMD':
				$result = __('Get Command', 'vikrentcar');
				break;
			case 'VRCCRONGETCMDHELP':
				$result = __('This cron job could be executed automatically by your server at regular intervals. The cron can also be executed manually by an administrator, but letting the server do it will be effortless and fully functional. Only servers supporting a Cron utility like crontab will be able of executing this cron job.', 'vikrentcar');
				break;
			case 'VRCCRONGETCMDINSTSTEPS':
				$result = __('Installation Steps', 'vikrentcar');
				break;
			case 'VRCCRONGETCMDINSTSTEPONE':
				$result = __('Download the executable PHP file for this cron job onto a local folder of your computer.', 'vikrentcar');
				break;
			case 'VRCCRONGETCMDINSTSTEPTWO':
				$result = __('Upload the downloaded file onto a directory of your server, either before, in or after the root directory of the web-server.', 'vikrentcar');
				break;
			case 'VRCCRONGETCMDINSTSTEPTHREE':
				$result = __('Log in to your server control panel and add a new job for your Cron Utility. Your hosting company should help you use this tool.', 'vikrentcar');
				break;
			case 'VRCCRONGETCMDINSTSTEPFOUR':
				$result = __('Cron Jobs require the execution interval and the command to execute. Set the necessary interval and the proper command to execute this cron job repetitively.', 'vikrentcar');
				break;
			case 'VRCCRONGETCMDINSTPATH':
				$result = __('Assuming that the executable PHP file was uploaded onto the root directory of your web-server, the command you should set in the Cron Utility should look similar to the one below. In this example, the path to the PHP interpreter has been set to <em>/usr/bin/php</em> but this may differ for your server.', 'vikrentcar');
				break;
			case 'VRCCRONGETCMDINSTURL':
				$result = __('Please be aware that PHP files in or after the root directory of the web-server can be executed at a public URL. This may not be secure if you do not want anyone to be able to launch the cron job except for the server. If the file was in the root directory, it would be callable at the URL below.', 'vikrentcar');
				break;
			case 'VRCCRONGETCMDGETFILE':
				$result = __('Download Executable File', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMPARAMCTYPE':
				$result = __('Reminder Type', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMPARAMCTYPEA':
				$result = __('Pick-up Reminder', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMPARAMCTYPEB':
				$result = __('Remaining Balance Payment Reminder', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMPARAMCTYPEC':
				$result = __('After Drop-off Message', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMPARAMCTYPECHELP':
				$result = __('If type = After Drop-off Message, this will be the number of days after the drop-off. Number of days before the pick-up otherwise.', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMPARAMBEFD':
				$result = __('Days in Advance', 'vikrentcar');
				break;
			case 'VRCCRONEMAILREMPARAMSUBJECT':
				$result = __('eMail Subject', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMPARAMTEXT':
				$result = __('Message', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMPARAMTEST':
				$result = __('Test Mode', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMPARAMTESTHELP':
				$result = __('if enabled, the cron will not actually send the SMS', 'vikrentcar');
				break;
			case 'VRCCRONEMAILREMPARAMTESTHELP':
				$result = __('if enabled, the cron will not actually send the eMail', 'vikrentcar');
				break;
			case 'VRCCRONSMSREMHELP':
				$result = __('This cron job should be scheduled to run at regular intervals of one time per day. Executing the cron job once per day, at the preferred time, will guarantee the best result.', 'vikrentcar');
				break;
			case 'VRCCRONINVGENPARAMCWHEN':
				$result = __('Generate Invoices', 'vikrentcar');
				break;
			case 'VRCCRONINVGENPARAMCWHENA':
				$result = __('After the Pick-up date', 'vikrentcar');
				break;
			case 'VRCCRONINVGENPARAMCWHENB':
				$result = __('Whenever the order status is Confirmed', 'vikrentcar');
				break;
			case 'VRCCRONINVGENPARAMCWHENC':
				$result = __('After the Drop-off date', 'vikrentcar');
				break;
			case 'VRCCRONINVGENPARAMDGEN':
				$result = __('Use Generation Date', 'vikrentcar');
				break;
			case 'VRCCRONINVGENPARAMEMAILSEND':
				$result = __('Send Invoices via eMail', 'vikrentcar');
				break;
			case 'VRCCRONINVGENPARAMTEST':
				$result = __('Test Mode', 'vikrentcar');
				break;
			case 'VRCCRONINVGENPARAMTESTHELP':
				$result = __('if enabled, the cron will not actually generate the invoices, nor it will send them via eMail to the customers', 'vikrentcar');
				break;
			case 'VRCCRONINVGENPARAMTEXT':
				$result = __('eMail message with PDF attached', 'vikrentcar');
				break;
			case 'VRCCRONINVGENHELP':
				$result = __('This cron job should be scheduled to run once per day. Remember to create at least one invoice manually from the back-end before running this cron. This is to set the invoices starting number and other details.', 'vikrentcar');
				break;
			case 'VRCMAINPMSREPORTSTITLE':
				$result = __('Vik Rent Car - PMS Reports', 'vikrentcar');
				break;
			case 'VRCREPORTSELECT':
				$result = __('- Select Report Type -', 'vikrentcar');
				break;
			case 'VRCREPORTLOAD':
				$result = __('Load Report Data', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUE':
				$result = __('Revenue', 'vikrentcar');
				break;
			case 'VRCREPORTSDATEFROM':
				$result = __('Date From', 'vikrentcar');
				break;
			case 'VRCREPORTSDATETO':
				$result = __('Date To', 'vikrentcar');
				break;
			case 'VRCREPORTSCARFILT':
				$result = __('Car Filter', 'vikrentcar');
				break;
			case 'VRCREPORTSERRNODATES':
				$result = __('Please select the desired dates for the Report.', 'vikrentcar');
				break;
			case 'VRCREPORTSERRNORESERV':
				$result = __('No orders found with the parameters specified.', 'vikrentcar');
				break;
			case 'VRCREPORTCSVEXPORT':
				$result = __('Export as CSV', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUEDAY':
				$result = __('Date', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUERSOLD':
				$result = __('Cars Sold', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUETOTB':
				$result = __('Total Orders', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUEPOCC':
				$result = __('% Occupancy', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUEADR':
				$result = __('ADR', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUEADRHELP':
				$result = __('The &quot;Average Daily Rate&quot; is calculated by dividing the Total Revenue by the number of Cars Sold.', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUEREVPAR':
				$result = __('RevPAC', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUEREVPARH':
				$result = __('The &quot;Revenue Per Available Car&quot; is calculated by dividing the Total Revenue by the total number of Cars Available.', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUETAX':
				$result = __('Taxes/Fees', 'vikrentcar');
				break;
			case 'VRCREPORTREVENUEREV':
				$result = __('Revenue', 'vikrentcar');
				break;
			case 'VRCREPORTSTOTALROW':
				$result = __('Total', 'vikrentcar');
				break;
			case 'VRCREPORTTOPCOUNTRIES':
				$result = __('Top Countries', 'vikrentcar');
				break;
			case 'VRCREPORTTOPCOUNTRIESC':
				$result = __('Country', 'vikrentcar');
				break;
			case 'VRCREPORTTOPCUNKNC':
				$result = __('Unknown', 'vikrentcar');
				break;
			case 'VRCSHEETNCHART':
				$result = __('Sheet + Chart', 'vikrentcar');
				break;
			case 'VRCSHEETONLY':
				$result = __('Sheet', 'vikrentcar');
				break;
			case 'VRCCHARTONLY':
				$result = __('Chart', 'vikrentcar');
				break;
			case 'VRCWEBSITERATES':
				$result = __('Website Rates', 'vikrentcar');
				break;
			case 'VRCREPORTTOTCARSHELP':
				$result = __('Total sellable cars: %d', 'vikrentcar');
				break;
			case 'VRCCARSOCCUPANCY':
				$result = __('Cars Occupancy', 'vikrentcar');
				break;
			case 'VRCCARSUNSOLD':
				$result = __('Cars Unsold', 'vikrentcar');
				break;
			case 'VRCREPORTOCCUPANCYRANKING':
				$result = __('Occupancy Ranking', 'vikrentcar');
				break;
			case 'VRCREPORTRPLANSREVENUE':
				$result = __('Rate Plans Revenue', 'vikrentcar');
				break;
			case 'VRCREPORTOPTIONSEXTRAS':
				$result = __('Options/Extras', 'vikrentcar');
				break;
			case 'VRCREPORTOPTIONSEXTRASHELP':
				$result = __('The values for the Options/Extras are tax included. The amount is calculated by subtracting the amount of Car Costs from the Order Total. It\'s the sum of all costs excluding the costs of the vehicles. It may include out of hours or location fees.', 'vikrentcar');
				break;
			case 'VRCPOSITIONORDERING':
				$result = __('Ordering position', 'vikrentcar');
				break;
			case 'VRCPOSITIONORDERINGHELP':
				$result = __('Leave this field empty for letting the system calculate the ordering position automatically', 'vikrentcar');
				break;
			case 'VRCPREVAMOUNTPAID':
				$result = __('Previous amount paid %s', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTNC':
				$result = __('New Confirmed Booking', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTMW':
				$result = __('Booking Modified by Customer', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTMB':
				$result = __('Administrator Booking Modification', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTNB':
				$result = __('Booking Created by Administrator', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTNP':
				$result = __('New Stand-by Booking', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTP0':
				$result = __('Payment Received', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTPN':
				$result = __('Other Payment Received', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTCR':
				$result = __('Customer Request', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTCW':
				$result = __('Booking Cancelled by Customer', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTCA':
				$result = __('Booking Auto Cancellation', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTCB':
				$result = __('Administrator Booking Cancellation', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTBR':
				$result = __('Booking Receipt Issued', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTBI':
				$result = __('Booking Invoice Issued', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTRA':
				$result = __('Cancel Booking Registration', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTRB':
				$result = __('Check-in', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTRC':
				$result = __('Check-out', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTRZ':
				$result = __('No-Show', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTTC':
				$result = __('Administrator Booking Confirmation', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTRP':
				$result = __('Reporting', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTCE':
				$result = __('Email to Client', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTCS':
				$result = __('SMS to Client', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTPU':
				$result = __('New amount paid', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTUE':
				$result = __('New extras booked', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTEC':
				$result = __('Cancellation Email Sent', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTRF':
				$result = __('Refund transaction', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTRU':
				$result = __('Refunded amount updated', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTPB':
				$result = __('Payable amount updated', 'vikrentcar');
				break;
			case 'VRCBOOKMODLOGSTR':
				$result = __('Order modified on %s.\nPrevious rental dates: %s.\nPrevious car booked: %s.\nPrevious Total: %s.', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTAB':
				$result = __('Order History', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYLBLTYPE':
				$result = __('Event', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYLBLDATE':
				$result = __('Date', 'vikrentcar');
					break;
			case 'VRCBOOKHISTORYLBLDESC':
				$result = __('Description', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYLBLTPAID':
				$result = __('Total Paid', 'vikrentcar');
					break;
			case 'VRCBOOKHISTORYLBLTOT':
				$result = __('Order Total', 'vikrentcar');
				break;
			case 'VRCSUBUNITCHANGEDFT':
				$result = __('Sub-unit changed for %s from %s to %s', 'vikrentcar');
				break;
			case 'VRCFORCEAVAILABILITYCONF':
				$result = __('Click this button to force the availability, even if this may generate overbooking.', 'vikrentcar');
				break;
			case 'VRCFORCEAVAILABILITY':
				$result = __('Force availability', 'vikrentcar');
				break;
			case 'VRCAVAILABILITYFORCED':
				$result = __('Order was forced due to non-availability', 'vikrentcar');
				break;
			case 'VRCADDCUSTOMFESTTODAY':
				$result = __('Add new note for today', 'vikrentcar');
				break;
			case 'VRCCONSECUTIVEDAYS':
				$result = __('Consecutive days', 'vikrentcar');
				break;
			case 'VRCDASHFIRSTSETTITLE':
				$result = __('Initial Setup', 'vikrentcar');
				break;
			case 'VRCDASHFIRSTSETSUBTITLE':
				$result = __('Complete the configuration of the following tasks to get started.', 'vikrentcar');
				break;
			case 'VRCWIZARDRPLANSMESS':
				$result = __('The types of price are the rate plans that clients can choose for renting a vehicle. They identify the insurance, the cancellation policies or the services included.', 'vikrentcar');
				break;
			case 'VRCDASHFIRSTSETUPCARS':
				$result = __('Create some vehicles to enable the booking process. Your cars will have their own calendars for the availability and rates, and your guests will be booking the vehicles you have set up.', 'vikrentcar');
				break;
			case 'VRCDASHFIRSTSETUPTARIFFS':
				$result = __('It is necessary to define the base rental costs for each vehicle for the various rate plans you have created. Other seasonal rates can be set up later from the page Rates Overview.', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_WELCOME':
				$result = __('Admin Widgets Customizer', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_WELCOME_DESC1':
				$result = __('<strong>Welcome to the Admin Widgets Customizer!</strong><br/>This interface will let you manage all the elements displayed in your Dashboard through <b>Sections</b>, <b>Containers</b> and <b>Widgets</b>.', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_WELCOME_DESC2':
				$result = __('Add new <b>Sections</b> to better organize your elements, and arrange their position with drag and drop actions. Add new <b>Containers</b> with a proper size to contain your <b>widgets</b>.<br/>Four sizes are available for the containers:<ul><li><b>Full Width</b> gives a <u>100%</u> width to the container. Only one container of this type will fit in one row.</li><li><b>Large</b> gives the container a <u>75%</u> width. Another small container could fit in the same row next to this size.</li><li><b>Medium</b> gives the container a <u>50%</u> width. Another medium container could fit next to it, or even two small containers.</li><li><b>Small</b> gives the container a width of <u>25%</u>. One row could fit up to 4 containers of this size.</li></ul>', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_CUSTWIDGETS':
				$result = __('Customize Widgets', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_AUTOSAVE':
				$result = __('Changes will be saved automatically', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_RESTDEFAULTSHORT':
				$result = __('Restore', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_RESTDEFAULT':
				$result = __('Restore Default Widgets Configuration', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_CONTSIZE':
				$result = __('Container Size', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_CONTFULL':
				$result = __('Full Width', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_CONTLARGE':
				$result = __('Large', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_CONTMEDIUM':
				$result = __('Medium', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_CONTSMALL':
				$result = __('Small', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_ADDWIDGCONT':
				$result = __('Add Widgets Container', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_SELWIDGADD':
				$result = __('Choose one widget to add', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_ADDNEWWIDG':
				$result = __('Add New Widget', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_SAVINGMAP':
				$result = __('Saving the widgets map', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_ERRSAVINGMAP':
				$result = __('Could not update the map. Please try again', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_LASTUPD':
				$result = __('Last update', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_ENTERSECTNAME':
				$result = __('Please enter a name for the section', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_NEWSECT':
				$result = __('New Section', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_CONFRMELEM':
				$result = __('The selected element will be removed. Proceed?', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_SELCONTSIZE':
				$result = __('Please select the size of the container', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_UPDWIDGCONT':
				$result = __('Update Widgets Container', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_EDITWIDGCONT':
				$result = __('Edit Widgets Container', 'vikrentcar');
				break;
			case 'VRC_WIDGETS_ERRDISPWIDG':
				$result = __('Could not display the widget. Try reloading the page', 'vikrentcar');
				break;
			case 'VRC_W_STICKYN_TITLE':
				$result = __('Sticky Notes', 'vikrentcar');
				break;
			case 'VRC_W_STICKYN_DESCR':
				$result = __('Write down something important for all your colleagues.', 'vikrentcar');
				break;
			case 'VRC_STICKYN_TITLE':
				$result = __('Note Title', 'vikrentcar');
				break;
			case 'VRC_STICKYN_TEXT':
				$result = __('Write here the text of the note.', 'vikrentcar');
				break;
			case 'VRC_STICKYN_TEXT2':
				$result = __('Use <b>shortcuts</b> to <u>format</u> the <i>text</i>.', 'vikrentcar');
				break;
			case 'VRC_STICKYN_CUSTOMURI':
				$result = __('Enter a custom URI for the link', 'vikrentcar');
				break;
			case 'VRC_W_STICKYN_HELP_TITLE':
				$result = __('Notes text formatting', 'vikrentcar');
				break;
			case 'VRC_W_STICKYN_HELP_DESCR':
				$result = __('You can use the following keyboard shortcuts while typing the text of your note:<br/><br/><b>Ctrl + B</b>: makes the text selection bold.<br/><b>Ctrl + I</b>: makes the text selection italic.<br/><b>Ctrl + U</b>: underlines the text selection.<br/><b>Ctrl + S</b>: strike-through the text selection.<br/><b>Ctrl + H/T</b>: makes the current block a title/heading.<br/><b>Ctrl + P</b>: makes the current block a paragraph.<br/><b>Ctrl + L</b>: adds a new bullet list, or converts the text selection to a link.<br/><b>Ctrl + O/N</b>: adds a new numbered list.<br/><b>Ctrl + A</b>: makes the whole text selected.<br/><b>Ctrl + M</b>: converts the markup text selection to HTML.', 'vikrentcar');
				break;
			case 'VRC_W_STICKYN_HELP_DESCR_MAC':
				$result = __('You can use the following keyboard shortcuts while typing the text of your note:<br/><br/><b>&#8984; + B</b>: makes the text selection bold.<br/><b>&#8984; + I</b>: makes the text selection italic.<br/><b>&#8984; + U</b>: underlines the text selection.<br/><b>&#8984; + S</b>: strike-through the text selection.<br/><b>&#8984; + H/T</b>: makes the current block a title/heading.<br/><b>&#8984; + P</b>: makes the current block a paragraph.<br/><b>&#8984; + L</b>: adds a new bullet list, or converts the text selection to a link.<br/><b>&#8984; + O/N</b>: adds a new numbered list.<br/><b>&#8984; + A</b>: makes the whole text selected.<br/><b>Ctrl + M</b>: converts the markup text selection to HTML.<br/><br/>Some commands may also be executed by holding <kbd>Ctrl</kbd> rather than <kbd>&#8984;</kbd>.', 'vikrentcar');
				break;
			case 'VRC_W_VISITCOUNT_TITLE':
				$result = __('Visitors Counter', 'vikrentcar');
				break;
			case 'VRC_W_VISITCOUNT_DESCR':
				$result = __('Counts the daily visitors that looked for a vehicle from your website', 'vikrentcar');
				break;
			case 'VRC_W_VISITCOUNT_VTODAY':
				$result = __('Visitors today', 'vikrentcar');
				break;
			case 'VRC_W_VISITCOUNT_VTMON':
				$result = __('Visitors this month', 'vikrentcar');
				break;
			case 'VRC_W_VISITCOUNT_VLMON':
				$result = __('Visitors last month', 'vikrentcar');
				break;
			case 'VRC_W_VISITCOUNT_VDIFF':
				$result = __('Turnout', 'vikrentcar');
				break;
			case 'VRCBTNKEEPREMIND':
				$result = __('Okay, keep reminding.', 'vikrentcar');
				break;
			case 'VRCBTNDONTREMIND':
				$result = __('Okay, do not remind again.', 'vikrentcar');
				break;
			case 'VRC_W_COLLECTTOD_DESCR':
				$result = __('A list of rental orders starting today', 'vikrentcar');
				break;
			case 'VRC_W_RETURNTOD_DESCR':
				$result = __('A list of rental orders ending today', 'vikrentcar');
				break;
			case 'VRCNOPICKUPSTODAY':
				$result = __('No pickup for today.', 'vikrentcar');
				break;
			case 'VRCNODROPOFFSTODAY':
				$result = __('No drop off for today.', 'vikrentcar');
				break;
			case 'VRCDASHSEARCHKEYS':
				$result = __('Search', 'vikrentcar');
				break;
			case 'VRC_W_NEXTRENT_DESCR':
				$result = __('The upcoming rental orders', 'vikrentcar');
				break;
			case 'VRC_W_CARSLOCKED_TITLE':
				$result = __('Cars Locked', 'vikrentcar');
				break;
			case 'VRC_W_CARSLOCKED_DESCR':
				$result = __('A list of unconfirmed orders currently blocking some vehicles', 'vikrentcar');
				break;
			case 'VRC_W_REMINDERS_TITLE':
				$result = __('Reminders', 'vikrentcar');
				break;
			case 'VRC_W_REMINDERS_DESCR':
				$result = __('List of notes defined for certain vehicles and dates', 'vikrentcar');
				break;
			case 'VRC_CDAYNOTES_HASHTAGS_HELP':
				$result = __('Use <i>#hashtags</i> to categorize the note.', 'vikrentcar');
				break;
			case 'VRC_ADD_NEW':
				$result = __('Add New', 'vikrentcar');
				break;
			case 'VRC_NEW_CDAY_NOTE':
				$result = __('New Reminder', 'vikrentcar');
				break;
			case 'VRCUNTIL':
				$result = __('Until', 'vikrentcar');
				break;
			case 'VRC_MISSING_REQFIELDS':
				$result = __('Missing required fields', 'vikrentcar');
				break;
			case 'VRC_W_REPORT_DESCR':
				$result = __('A snapshot of the future and past rental orders', 'vikrentcar');
				break;
			case 'VRC_RENTALS_LAST_MONTH':
				$result = __('Rentals last month', 'vikrentcar');
				break;
			case 'VRC_RENTALS_THIS_MONTH':
				$result = __('Rentals this month', 'vikrentcar');
				break;
			case 'VRC_SEARCH_SUGGESTIONS':
				$result = __('Suggest solutions when no availability', 'vikrentcar');
				break;
			case 'VRC_PREF_COLORS':
				$result = __('Preferred Colors', 'vikrentcar');
				break;
			case 'VRC_PREF_COLORS_HELP':
				$result = __('Select your preferred colors to adjust the default front-end styles. This way, the look of the elements (titles, buttons, font color and backgrounds) of the various pages of the booking process can match with your Theme or Company design.', 'vikrentcar');
				break;
			case 'VRC_PREF_COLOR_TEXTS':
				$result = __('Titles and Headings', 'vikrentcar');
				break;
			case 'VRC_PREF_COLOR_BKGROUND':
				$result = __('Elements with backgrounds', 'vikrentcar');
				break;
			case 'VRC_PREF_COLOR_BKGROUNDHOV':
				$result = __('Hovered elements', 'vikrentcar');
				break;
			case 'VRC_BKGROUND_COL':
				$result = __('Background color', 'vikrentcar');
				break;
			case 'VRC_FONT_COL':
				$result = __('Font color', 'vikrentcar');
				break;
			case 'VRC_PREF_COLOR_EXAMPLERES':
				$result = __('Styles examples', 'vikrentcar');
				break;
			case 'VRC_ORDER_REGISTRATION':
				$result = __('Registration', 'vikrentcar');
				break;
			case 'VRC_ORDER_REGISTRATION_NONE':
				$result = __('None', 'vikrentcar');
				break;
			case 'VRC_ORDER_REGISTRATION_STARTED':
				$result = __('Started', 'vikrentcar');
				break;
			case 'VRC_ORDER_REGISTRATION_TERMINATED':
				$result = __('Terminated', 'vikrentcar');
				break;
			case 'VRC_ORDER_REGISTRATION_NOSHOW':
				$result = __('No show', 'vikrentcar');
				break;
			case 'VRC_TOT_DURATION':
				$result = __('Total duration', 'vikrentcar');
				break;
			case 'VRC_PAYBUT_POS':
				$result = __('Payment &quot;button&quot; position', 'vikrentcar');
				break;
			case 'VRC_PAYBUT_POS_TOP':
				$result = __('Top', 'vikrentcar');
				break;
			case 'VRC_PAYBUT_POS_MIDDLE':
				$result = __('Middle', 'vikrentcar');
				break;
			case 'VRC_PAYBUT_POS_BOTTOM':
				$result = __('Bottom', 'vikrentcar');
				break;
			case 'VRC_PAYMET_LOGO':
				$result = __('Custom logo', 'vikrentcar');
				break;
			case 'VRC_CONF_ALLOWMULTIPAYMENTS':
				$result = __('Allow multiple payments for the same order', 'vikrentcar');
				break;
			case 'VRC_CONF_ALLOWMULTIPAYMENTS_HELP':
				$result = __('If enabled, confirmed rental orders with an amount paid greater than zero and less than the order total amount, will allow to pay the remaining balance. If disabled, confirmed rental orders will not allow any additional payment.', 'vikrentcar');
				break;
			case 'VRC_AMOUNT_PAYABLE':
				$result = __('Amount Payable', 'vikrentcar');
				break;
			case 'VRC_AMOUNT_PAYABLE_RQ':
				$result = __('Request Payment', 'vikrentcar');
				break;
			case 'VRC_AMOUNT_PAYABLE_CONF':
				$result = __('The reservation will allow the payment to be made via front-end. Make sure to inform the customer by using the re-send email button.', 'vikrentcar');
				break;
			case 'VRC_NEWPAYABLE_AMOUNT':
				$result = __('Reservation amount payable changed to %s', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTPB':
				$result = __('Payable amount updated', 'vikrentcar');
				break;
			case 'VRCMAKEORDERPAYABLE':
				$result = __('Make payable from front site', 'vikrentcar');
				break;
			case 'VRCCUSTOMERDOCUMENTS':
				$result = __('Customer Documents', 'vikrentcar');
				break;
			case 'VRCMANUALUPLOAD':
				$result = __('Upload File', 'vikrentcar');
				break;
			case 'VRCDROPFILES':
				$result = __('or DRAG FILES HERE', 'vikrentcar');
				break;
			case 'VRCDROPFILESSTOPREMOVING':
				$result = __('Press ESC from keyboard to stop deleting the files', 'vikrentcar');
				break;
			case 'VRCDROPFILESHINT':
				$result = __('Drag & drop some documents here to upload them. It is possible to remove the documents by keeping them pressed.<br />You do not need to hit the "Save" button to keep the uploaded files.', 'vikrentcar');
				break;
			case 'VRC_ALLOW_DOCS_UPLOAD':
				$result = __('Allow Documents Upload', 'vikrentcar');
				break;
			case 'VRC_ALLOW_DOCS_UPLOAD_HELP':
				$result = __('If enabled, confirmed orders with a pick up date in the future will allow the user to upload documents via front-end, like their driver license or a photo of the personal ID.', 'vikrentcar');
				break;
			case 'VRC_ALLOW_DOCS_UPLOAD_INSTR':
				$result = __('Upload instructions', 'vikrentcar');
				break;
			case 'VRC_ALLOW_DOCS_UPLOAD_INSTR_HELP':
				$result = __('This text will be displayed on the page where the users will be able to upload their own documents. These instructions should help the users understand what documents they should upload and what information they should provide.', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTDU':
				$result = __('Documents uploaded by client', 'vikrentcar');
				break;
			case 'VRC_CRON_EMAIL_REMINDER_TITLE':
				$result = __('Reminder - Email', 'vikrentcar');
				break;
			case 'VRC_CRON_BACKUP_CREATOR_TITLE':
				$result = __('Backup - Creator', 'vikrentcar');
				break;
			case 'VRC_CRON_WEBHOOK_TITLE':
				$result = __('Webhook Notification', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_WEBHOOK_DESCRIPTION':
				$result = __('This cron job allows to set up automated actions whenever new events related to orders take place. This is useful to post the changes to remote URLs or to pass the data to third party plugins.', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_WEBHOOK_TYPE_LABEL':
				$result = __('Notification type', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_WEBHOOK_TYPE_DESC':
				$result = __('Choose the type of webhook notification to trigger:<br/><ul><li><strong>URL</strong> POST request with a JSON payload to a remote URL.</li><li><strong>PHP Callback</strong> Invoke a custom PHP function or a class method.</li><li><strong>WordPress Action</strong> Calls a custom event (hook) by passing the payload to the action.</li></ul>', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_WEBHOOK_TYPE_URL_OPTION':
				$result = __('URL', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_WEBHOOK_TYPE_CALLBACK_OPTION':
				$result = __('PHP Callback', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_WEBHOOK_TYPE_ACTION_OPTION':
				$result = __('WordPress Action', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_WEBHOOK_HANDLER_LABEL':
				$result = __('Handler', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_WEBHOOK_HANDLER_DESC':
				$result = __('Depending on the type of webhook notification, enter the endpoint URL, the PHP callback (syntax: <code>function_name</code> or <code>object,method</code>) or the name of the WordPress hook.', 'vikrentcar');
				break;
			case 'VRC_COND_TEXT_MNG_TITLE':
				$result = __('Vik Rent Car - Conditional Text', 'vikrentcar');
				break;
			case 'VRC_COND_TEXTS':
				$result = __('Conditional Texts', 'vikrentcar');
				break;
			case 'VRC_COND_TEXT_RULES':
				$result = __('Conditional Text Rules', 'vikrentcar');
				break;
			case 'VRC_NEW_COND_TEXT':
				$result = __('New Conditional Text', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_NAME':
				$result = __('Conditional Text Name', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_TKN':
				$result = __('Special Tag', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_TKN_HELP':
				$result = __('The special tag (token) is generated automatically from the name of the conditional text. You can use it on most messages to execute all rules.', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_ADDRULE':
				$result = __('Add Rule', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_MSG':
				$result = __('Conditional Message', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_MSG_HELP':
				$result = __('If you would like these conditional rules to produce a message, enter it here. Leave it empty otherwise.', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_DISABLED':
				$result = __('This rule is already being used', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_NORULES_SEL':
				$result = __('Please select one rule to add', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_RMCONF':
				$result = __('Do you want to remove this rule?', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_WARN_NORULES':
				$result = __('No rules defined to restrict the conditional text', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_SEPEMAIL':
				$result = __('You can separate multiple email addresses with the comma', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_BCCEMAIL':
				$result = __('Set as BCC', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_DEBUG_RULES':
				$result = __('Debug Rules', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_DEBUG_RULES_HELP':
				$result = __('If enabled, the special tags will debug the execution of the rules. Use it only for testing.', 'vikrentcar');
				break;
			case 'VRC_DEBUG_RULE_CONDTEXT':
				$result = __('[Rule %s was not compliant. Special tag %s was not applied.]', 'vikrentcar');
				break;
			case 'VRC_EXPAND':
				$result = __('Expand', 'vikrentcar');
				break;
			case 'VRC_COLLAPSE':
				$result = __('Collapse', 'vikrentcar');
				break;
			case 'VRC_TEMPLATE_FILES':
				$result = __('Template Files', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_TAG_ADD_HELP':
				$result = __('Select the position in the template file where you would like to add the special tag %s', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_TAG_RM_HELP':
				$result = __('Do you want to remove this tag from the template file %s? You will then be able to add it again to a different position.', 'vikrentcar');
				break;
			case 'VRC_CSS_EDITING_HELP':
				$result = __('Click the various elements on the template to edit their CSS styling properties.', 'vikrentcar');
				break;
			case 'VRC_INSPECTOR_START':
				$result = __('Customize colors and styles', 'vikrentcar');
				break;
			case 'VRC_INSP_CSS_FONTCOLOR':
				$result = __('Font Color', 'vikrentcar');
				break;
			case 'VRC_INSP_CSS_BACKGCOLOR':
				$result = __('Background Color', 'vikrentcar');
				break;
			case 'VRC_INSP_HTML_TAG':
				$result = __('HTML Tag', 'vikrentcar');
				break;
			case 'VRC_INSP_CSS_BORDER':
				$result = __('Border', 'vikrentcar');
				break;
			case 'VRC_INSP_CSS_BORDERWIDTH':
				$result = __('Border Width', 'vikrentcar');
				break;
			case 'VRC_INSP_CSS_BORDERCOLOR':
				$result = __('Border Color', 'vikrentcar');
				break;
			case 'VRC_FILE_FROM_MEDIAMNG':
				$result = __('Media Manager', 'vikrentcar');
				break;
			case 'VRC_FILE_FROM_LOCALDIR':
				$result = __('Full Path', 'vikrentcar');
				break;
			case 'VRC_FILE_FROM_LOCALDIR_HELP':
				$result = __('Enter the full path to the local file starting with %s', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_RETCUST':
				$result = __('Returning Customer', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_DTA':
				$result = __('Days to arrival', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_COUNTRIES':
				$result = __('Countries', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_ATTFILES':
				$result = __('Attach Files', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_ATTFILES_DESCR':
				$result = __('Add file attachments to the email message', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_BOOKDATES':
				$result = __('Booking Dates', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_BOOKDATES_DESCR':
				$result = __('Restriction applied to certain booking dates', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_BOOKSTAT_DESCR':
				$result = __('Filter by reservation statuses', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_CHANNELS_DESCR':
				$result = __('Filter the reservations by source of provenience', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_COUNTRIES_DESCR':
				$result = __('Restriction applied to certain countries', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_DTA_DESCR':
				$result = __('Restriction for minimum days offset to arrival', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_EXTRAMAIL':
				$result = __('Extra Email', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_EXTRAMAIL_DESCR':
				$result = __('Add one or more email recipient addresses', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_LANG_DESCR':
				$result = __('Restriction for specific booking languages', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_NOD_DESCR':
				$result = __('Filter by number of days', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_OPTS_DESCR':
				$result = __('Restriction applied to certain options/extras', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_PAYM_DESCR':
				$result = __('Filter by payment method', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_RPL_DESCR':
				$result = __('Restriction applied to certain rate plans', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_RETCUST_DESCR':
				$result = __('The same customer must have placed other reservations before', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_CARS_DESCR':
				$result = __('Restriction for specific cars booked', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_RENTDATES_DESCR':
				$result = __('Restriction applied to certain dates of rent', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_WDAYS_DESCR':
				$result = __('Filter by pick up or drop off week day.', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_REMBALANCE_DESCR':
				$result = __('Filter by amount paid less than booking total.', 'vikrentcar');
				break;
			case 'VRC_EDITTPL_FATALERROR':
				$result = __('Your PHP version may not support these functions, or the source code of the template may not be compatible. Please edit the template file manually, any changes will be restored.', 'vikrentcar');
				break;
			case 'VRC_INSERT_CONT_WRAPPER':
				$result = __('Insert content wrapper', 'vikrentcar');
				break;
			case 'VRC_CONT_WRAPPER':
				$result = __('Content Wrapper', 'vikrentcar');
				break;
			case 'VRC_CONT_WRAPPER_HELP':
				$result = __('When you insert a content wrapper to the body of the message, this will wrap your text within the default HTML layout. It is needed to beautify some contents of your email message.', 'vikrentcar');
				break;
			case 'VRC_MODE_TEXTHTML':
				$result = __('Text/HTML', 'vikrentcar');
				break;
			case 'VRC_MODE_VISUAL':
				$result = __('Visual', 'vikrentcar');
				break;
			case 'VRC_TPL_TEXT':
				$result = __('Text', 'vikrentcar');
				break;
			case 'VRC_COPY_ORIGINAL_TN':
				$result = __('Copy original translation', 'vikrentcar');
				break;
			case 'VRC_CONFIG_BACKUP':
				$result = __('Backup', 'vikrentcar');
				break;
			case 'VRC_CONFIG_BACKUP_TYPE':
				$result = __('Export Type', 'vikrentcar');
				break;
			case 'VRC_CONFIG_BACKUP_FOLDER':
				$result = __('Folder Path', 'vikrentcar');
				break;
			case 'VRC_CONFIG_BACKUP_FOLDER_HELP':
				$result = __('Enter here the path used to store the backup archives created by Vik Rent Car. In case the folder does not exist, the system will attempt to create it. Installation base path: %s', 'vikrentcar');
				break;
			case 'VRC_CONFIG_BACKUP_MANAGE_BTN':
				$result = __('Manage Backups', 'vikrentcar');
				break;
			case 'VRCMAINBACKUPSTITLE':
				$result = __('Vik Rent Car - Backup Archives', 'vikrentcar');
				break;
			case 'VRCMAINTITLENEWBACKUP':
				$result = __('Vik Rent Car - New Backup', 'vikrentcar');
				break;
			case 'VRC_BACKUP_SIZE':
				$result = __('File Size', 'vikrentcar');
				break;
			case 'VRC_BACKUP_DOWNLOAOD':
				$result = __('Download', 'vikrentcar');
				break;
			case 'VRCBACKUPRESTORECONF1':
				$result = __('Do you want to restore the program data with the selected backup?', 'vikrentcar');
				break;
			case 'VRCBACKUPRESTORECONF2':
				$result = __('Confirm that you want to proceed one last time. This action cannot be undone.', 'vikrentcar');
				break;
			case 'VRCBACKUPRESTORED':
				$result = __('The backup has been restored successfully!', 'vikrentcar');
				break;
			case 'VRC_BACKUP_ACTION_LABEL':
				$result = __('Action', 'vikrentcar');
				break;
			case 'VRC_BACKUP_ACTION_CREATE':
				$result = __('Create New', 'vikrentcar');
				break;
			case 'VRC_BACKUP_ACTION_UPLOAD':
				$result = __('Upload Existing', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_BACKUP_CREATOR_DESCRIPTION':
				$result = __('Periodically creates a backup of the contents created through Vik Rent Car.', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_BACKUP_CREATOR_FIELD_MAX':
				$result = __('Maximum Archives', 'vikrentcar');
				break;
			case 'VRC_CRONJOB_BACKUP_CREATOR_FIELD_MAX_DESC':
				$result = __('Choose the maximum number of backup archives that can be created. When the specified threshold is reached, the system will automatically delete the oldest backup to allow the creation of a new one.', 'vikrentcar');
				break;
			case 'VRC_BACKUP_EXPORT_TYPE_FULL':
				$result = __('Full', 'vikrentcar');
				break;
			case 'VRC_BACKUP_EXPORT_TYPE_FULL_DESCRIPTION':
				$result = __('The backup will export all the contents created through Vik Rent Car.', 'vikrentcar');
				break;
			case 'VRC_BACKUP_EXPORT_TYPE_MANAGEMENT':
				$result = __('Management', 'vikrentcar');
				break;
			case 'VRC_BACKUP_EXPORT_TYPE_MANAGEMENT_DESCRIPTION':
				$result = __('The backup will export only the contents used to set up the program. The records related to the customers, such as the bookings, will be completely ignored. This is useful to copy the configuration of this website into a new one.', 'vikrentcar');
				break;
			case 'VRC_CRON_DADV_LOWER':
				$result = __('Include less days', 'vikrentcar');
				break;
			case 'VRC_CRON_DADV_LOWER_HELP':
				$result = __('If type = Pickup and Days in advance greater than 1, by turning on this option the Cron Job will also include the reservations with a number of days to collection (from the booking date) lower than the option days in advance. For example, if days in advance is set to 7, but a last-minute reservation is made 2 days before the pickup, with this setting enabled this reservation will be notified.', 'vikrentcar');
				break;
			case 'VRC_FULLY_PAID':
				$result = __('Fully paid', 'vikrentcar');
				break;
			case 'VRC_IMPORT_CALENDARS':
				$result = __('Import Calendars', 'vikrentcar');
				break;
			case 'VRC_IMPORT_CALENDARS_HELP':
				$result = __('It is possible to import remote iCal calendars containing reservations to sync the availability for this vehicle.', 'vikrentcar');
				break;
			case 'VRC_IMPORT_CALENDARS_COUNT':
				$result = __('Availability calendars to import', 'vikrentcar');
				break;
			case 'VRC_IMPORT_CALENDARS_ADD':
				$result = __('Add calendar', 'vikrentcar');
				break;
			case 'VRC_IMPORT_CALENDAR_NAME':
				$result = __('Calendar name', 'vikrentcar');
				break;
			case 'VRC_IMPORT_CALENDAR_URL':
				$result = __('Calendar URL', 'vikrentcar');
				break;
			case 'VRC_CRON_ICAL_IMPORTER_TITLE':
				$result = __('iCal Calendars Importer', 'vikrentcar');
				break;
			case 'VRC_CRON_ICAL_CANC':
				$result = __('Allow cancellations', 'vikrentcar');
				break;
			case 'VRC_CRON_ICAL_CANC_HELP':
				$result = __('If enabled, the active reservations previously downloaded, and no longer available on the iCal calendars, will be set to cancelled.', 'vikrentcar');
				break;
			case 'VRC_CRON_ICAL_TEST_HELP':
				$result = __('If enabled, no reservations will actually be downloaded, modified or cancelled.', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTIN':
				$result = __('New order from iCal', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTIM':
				$result = __('Order modified from iCal', 'vikrentcar');
				break;
			case 'VRCBOOKHISTORYTIC':
				$result = __('Order cancelled from iCal', 'vikrentcar');
				break;
			case 'VRC_FILTER_BY_CALENDAR':
				$result = __('Filter by calendar', 'vikrentcar');
				break;
			case 'VRC_FORCE_PICKDROP_TIMES':
				$result = __('Force Pickup/Drop Off Time', 'vikrentcar');
				break;
			case 'VRC_FORCE_PICK_TIME':
				$result = __('Forced pickup time', 'vikrentcar');
				break;
			case 'VRC_FORCE_DROP_TIME':
				$result = __('Forced drop off time', 'vikrentcar');
				break;
			case 'VRC_SAVE_COPY':
				$result = __('Save as copy', 'vikrentcar');
				break;
			case 'VRC_CONF_RESET_DISTFEAT':
				$result = __('Reset distinctive features automatically?', 'vikrentcar');
				break;
			case 'VRC_SEND_CANC_EMAIL':
				$result = __('Send cancellation email', 'vikrentcar');
				break;
			case 'VRC_CANC_EMAIL_SENT_TO':
				$result = __('Cancellation email sent to %s', 'vikrentcar');
				break;
			case 'VRC_CUSTOM_DMG_INSPECTION':
				$result = __('Custom image car status', 'vikrentcar');
				break;
			case 'VRC_CUSTOM_DMG_INSPECTION_HELP':
				$result = __('An optional custom image (PNG) for this vehicle to display the damages and status of each unit', 'vikrentcar');
				break;
			case 'VRC_CONDTEXT_RULE_LOCATIONS_DESCR':
				$result = __('Filter by certain pickup/drop off locations.', 'vikrentcar');
				break;
			case 'VRC_HOURLY_AV_DAY':
				$result = __('Hourly availability for %s (units left)', 'vikrentcar');
				break;
			case 'VRC_LOCFEE_ONEWAY':
				$result = __('One-way Fee', 'vikrentcar');
				break;
			case 'VRC_LOCFEE_ONEWAY_HELP':
				$result = __('One-way fees will include any possible combination of pickup location different from the drop off location.', 'vikrentcar');
				break;
			case 'VRC_OPENTIME_BREAKS':
				$result = __('Breaks', 'vikrentcar');
				break;
			case 'VRC_LOCATION_OPEN_FROM_TO':
				$result = __('The agency %s is open from %s to %s', 'vikrentcar');
				break;
			case 'VRC_LOCATION_BREAK_FROM_TO':
				$result = __('The agency %s is on break from %s to %s', 'vikrentcar');
				break;
			case 'VRC_TRK_DRIVER':
				$result = __('Tracking/Conversion Driver', 'vikrentcar');
				break;
			case 'VRC_GA_MEASURMENT_ID':
				$result = __('Measurment ID', 'vikrentcar');
				break;
			case 'VRC_GA_MEASURMENT_ID_HELP':
				$result = __('Enter your Google Analytics Measurment ID', 'vikrentcar');
				break;
			case 'VRC_AUTO_ASSIGN_CUNIT':
				$result = __('Auto-Assign Car Unit', 'vikrentcar');
				break;
		}

		return $result;
	}
}
