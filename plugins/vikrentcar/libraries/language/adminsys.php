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
 * Switcher class to translate the VikRentCar plugin common languages.
 *
 * @since 	1.0
 */
class VikRentCarLanguageAdminSys implements JLanguageHandler
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
			 * Do not touch the first definition as it gives the title to the pages of the back-end
			 */
			case 'COM_VIKRENTCAR':
				$result = __('Vik Rent Car', 'vikrentcar');
				break;

			/**
			 * Definitions
			 */
			case 'COM_VIKRENTCAR_MENU':
				$result = __('VikRentCar', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CONFIGURATION':
				$result = __('VikRentCar - Access Levels', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_VIKRENTCAR_VIEW_DEFAULT_TITLE':
				$result = __('Search Form', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_VIKRENTCAR_VIEW_DEFAULT_DESC':
				$result = __('VikRentCar Search Form', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CARSLIST_VIEW_DEFAULT_TITLE':
				$result = __('Cars List', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CARSLIST_VIEW_DEFAULT_DESC':
				$result = __('VikRentCar Cars List', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CATEGORY_FIELD_SELECT_TITLE':
				$result = __('Category', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CATEGORY_FIELD_SELECT_TITLE_DESC':
				$result = __('Select a VikRentCar Category', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_LOCATIONSLIST_VIEW_DEFAULT_TITLE':
				$result = __('Locations List', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_LOCATIONSLIST_VIEW_DEFAULT_DESC':
				$result = __('VikRentCar Locations List', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_USERORDERS_VIEW_DEFAULT_TITLE':
				$result = __('User Orders', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_USERORDERS_VIEW_DEFAULT_DESC':
				$result = __('VikRentCar User Orders', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_USERORDERS_FIELD_SEARCHORDER':
				$result = __('Search by Confirmation Number', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_USERORDERS_FIELD_SEARCHORDER_DESC':
				$result = __('Enable this setting to display a form for searching orders by confirmation number', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_USERORDERS_FIELD_SEARCHORDER_YES':
				$result = __('Enabled', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_USERORDERS_FIELD_SEARCHORDER_NO':
				$result = __('Disabled', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_ORDERBY_FIELD_TITLE':
				$result = __('Sort Cars By', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_ORDERBY_FIELD_PRICE':
				$result = __('Price', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_ORDERBY_FIELD_CUSTOMPRICE':
				$result = __('Custom Price', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_ORDERBY_FIELD_NAME':
				$result = __('Name', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_ORDERTYPE_FIELD_TITLE':
				$result = __('Ordering Type', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_ORDERTYPE_FIELD_ASC':
				$result = __('Ascending', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_ORDERTYPE_FIELD_DESC':
				$result = __('Descending', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CARSLISTLIM_FIELD_TITLE':
				$result = __('Cars per Page', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOTIONS_VIEW_DEFAULT_TITLE':
				$result = __('Promotions', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOTIONS_VIEW_DEFAULT_DESC':
				$result = __('Shows a list of all the Special Prices marked as -Promotion-', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOSHOWCARS_FIELD_TITLE':
				$result = __('Show Cars', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOSHOWCARS_FIELD_TITLE_DESC':
				$result = __('Choose whether the Cars, for which the Promotion is valid, should be displayed', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOMAXDATE_FIELD_TITLE':
				$result = __('Max Date in the Future', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOMAXDATE_FIELD_TITLE_DESC':
				$result = __('Choose if the promotions should be displayed if they are 3, 6 or 12 months in advance', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOMAXDATE_FIELD_THREEM':
				$result = __('3 Months', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOMAXDATE_FIELD_SIXM':
				$result = __('6 Months', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOMAXDATE_FIELD_YEAR':
				$result = __('1 Year', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_PROMOTIONSLIM_FIELD_TITLE':
				$result = __('Max Promotions', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CARDETAILS_VIEW_DEFAULT_TITLE':
				$result = __('Single Car Details', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CARDETAILS_VIEW_DEFAULT_DESC':
				$result = __('Details page of one car', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CARID_FIELD_SELECT_TITLE':
				$result = __('Select a Car', 'vikrentcar');
				break;
			case 'VRCACTION_CARS':
				$result = __('Cars Configuration', 'vikrentcar');
				break;
			case 'VRCACTION_CARS_DESC':
				$result = __('Cars, Categories, Options and Characteristics Management', 'vikrentcar');
				break;
			case 'VRCACTION_PRICES':
				$result = __('Rental Costs', 'vikrentcar');
				break;
			case 'VRCACTION_PRICES_DESC':
				$result = __('Daily-Hourly Fares, Tax Rates, Types of Price, Fees Management', 'vikrentcar');
				break;
			case 'VRCACTION_ORDERS':
				$result = __('Orders Management', 'vikrentcar');
				break;
			case 'VRCACTION_ORDERS_DESC':
				$result = __('Orders, Availability, Calendars, Overiview', 'vikrentcar');
				break;
			case 'VRCACTION_GSETTINGS':
				$result = __('Global Settings', 'vikrentcar');
				break;
			case 'VRCACTION_GSETTINGS_DESC':
				$result = __('Configuration, Payment Options, Locations', 'vikrentcar');
				break;
			case 'VRCACTION_MANAGEMENT':
				$result = __('Management', 'vikrentcar');
				break;
			case 'VRCACTION_MANAGEMENT_DESC':
				$result = __('Customers, Graphs and Statistics', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_ORDER_VIEW_DEFAULT_TITLE':
				$result = __('Order Details', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_ORDER_VIEW_DEFAULT_DESC':
				$result = __('The order details page', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_AVAILABILITY_VIEW_DEFAULT_TITLE':
				$result = __('General Availability', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_AVAILABILITY_VIEW_DEFAULT_DESC':
				$result = __('A list of availability calendars for all or just some vehicles', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CARIDS_FIELD_SELECT_TITLE':
				$result = __('Vehicles', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_CARIDS_FIELD_SELECT_TITLE_DESC':
				$result = __('You can optionally choose to display only certain vehicles', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_AVSHOWTYPE_FIELD_SELECT_TITLE':
				$result = __('Cars Units', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_AVSHOWTYPE_NONE':
				$result = __('Show available days with no numbers', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_AVSHOWTYPE_REMAINING':
				$result = __('Show number of remaining units', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_AVSHOWTYPE_BOOKED':
				$result = __('Show number of units booked', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_SORTBY_FIELD_SELECT_TITLE':
				$result = __('Sort by', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_SORTTYPE_FIELD_SELECT_TITLE':
				$result = __('Sort type', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_STARTFROM_PRICE':
				$result = __('Starting from price', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_SORT_NAME':
				$result = __('Name', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_SORT_ID':
				$result = __('ID', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_SORT_ASC':
				$result = __('Ascending', 'vikrentcar');
				break;
			case 'COM_VIKRENTCAR_SORT_DESC':
				$result = __('Descending', 'vikrentcar');
				break;

			/**
			 * @wponly Definitions for the Views "Gotopro" and "Getpro"
			 */
			case 'VRCMAINGOTOPROTITLE':
				$result = __('Vik Rent Car - Upgrade to Pro', 'vikrentcar');
				break;
			case 'VRCLICKEYVALIDUNTIL':
				$result = __('License Key valid until %s', 'vikrentcar');
				break;
			case 'VRCLICKEYEXPIREDON':
				$result = __('Your License Key expired on %s', 'vikrentcar');
				break;
			case 'VRCEMPTYLICKEY':
				$result = __('Please enter a valid License Key', 'vikrentcar');
				break;
			case 'VRCNOPROERROR':
				$result = __('No valid and active License Key found.', 'vikrentcar');
				break;
			case 'VRCMAINGETPROTITLE':
				$result = __('Vik Rent Car - Downloading Pro version', 'vikrentcar');
				break;
			case 'VRCUPDCOMPLOKCLICK':
				$result = __('Update Completed. Please click here', 'vikrentcar');
				break;
			case 'VRCUPDCOMPLNOKCLICK':
				$result = __('Update Failed. Please click here', 'vikrentcar');
				break;
			case 'VRCPROPLWAIT':
				$result = __('Please wait', 'vikrentcar');
				break;
			case 'VRCPRODLINGPKG':
				$result = __('Downloading the package...', 'vikrentcar');
				break;
			case 'VRCPROTHANKSUSE':
				$result = __('Thanks for using the Pro version', 'vikrentcar');
				break;
			case 'VRCPROTHANKSLIC':
				$result = __('The true Vik Rent Car is Pro. Make sure to keep your License Key active to be able to install future updates.', 'vikrentcar');
				break;
			case 'VRCPROGETRENEWLICFROM':
				$result = __('Get or renew your License Key from VikWP.com', 'vikrentcar');
				break;
			case 'VRCPROGETRENEWLIC':
				$result = __('Get or renew your licence', 'vikrentcar');
				break;
			case 'VRCPROVALNUPD':
				$result = __('Validate and Update', 'vikrentcar');
				break;
			case 'VRCPROALREADYHAVEKEY':
				$result = __('Already have Vik Rent Car PRO? <br /> <small>Enter your licence key here</small>', 'vikrentcar');
				break;
			case 'VRCPROWHYUPG':
				$result = __('Why Upgrade to Pro?', 'vikrentcar');
				break;
			case 'VRCPROTRUEVRCPRO':
				$result = __('The true Vik Rent Car is Pro. Discover the power of a professional vehicles rental software!', 'vikrentcar');
				break;
			case 'VRCPROGETLICNUPG':
				$result = __('Get your License Key and Upgrade to PRO', 'vikrentcar');
				break;
			case 'VRCPROWHYRATES':
				$result = __('Full Rates Management', 'vikrentcar');
				break;
			case 'VRCPROWHYRATESDESC':
				$result = __('Set different rates on some seasons, holidays, weekends or days of the year with just two clicks. Rental Restrictions: define the minimum or maximum days of rental for certain dates of the year and vehicles, set days closed to arrival or departure, and much more.', 'vikrentcar');
				break;
			case 'VRCPROWHYBOOKINGS':
				$result = __('Create and Modify rental orders via back-end', 'vikrentcar');
				break;
			case 'VRCPROWHYBOOKINGSDESC':
				$result = __('The page Calendar will let you create new reservations manually, maybe to register walk-in customers or offline reservations. Modify the dates and switch cars of certain reservations.', 'vikrentcar');
				break;
			case 'VRCPROWHYUNLOCKF':
				$result = __('Unlock over 30 must-have features', 'vikrentcar');
				break;
			case 'VRCPROWHYCUSTOMERS':
				$result = __('Customers Management', 'vikrentcar');
				break;
			case 'VRCPROWHYPROMOTIONS':
				$result = __('Promotions', 'vikrentcar');
				break;
			case 'VRCPROWHYCOUPONS':
				$result = __('Coupon Discounts', 'vikrentcar');
				break;
			case 'VRCPROWHYINVOICES':
				$result = __('Invoices', 'vikrentcar');
				break;
			case 'VRCPROWHYCHECKIN':
				$result = __('Check-in and Registration', 'vikrentcar');
				break;
			case 'VRCPROWHYGRAPHS':
				$result = __('Graphs and Statistics', 'vikrentcar');
				break;
			case 'VRCPROWHYPAYMENTS':
				$result = __('Payment Gateways', 'vikrentcar');
				break;
			case 'VRCPROWHYLOCOOHFEES':
				$result = __('Locations/Out of hours fees', 'vikrentcar');
				break;
			case 'VRCPROREADYTOUPG':
				$result = __('Ready to upgrade?', 'vikrentcar');
				break;
			case 'VRCPROGETNEWLICFROM':
				$result = __('Get your License Key from VikWP.com', 'vikrentcar');
				break;
			case 'VRCPROGETNEWLIC':
				$result = __('Get your License Key', 'vikrentcar');
				break;
			case 'VRCPROVALNINST':
				$result = __('Validate and Install', 'vikrentcar');
				break;
			case 'VRCGOTOPROBTN':
				$result = __('Upgrade to PRO', 'vikrentcar');
				break;
			case 'VRCISPROBTN':
				$result = __('PRO Version', 'vikrentcar');
				break;
			case 'VRCLICKEYVALIDVCM':
				$result = __('Active License Key', 'vikrentcar');
				break;
			case 'VRCPROWHYOPTIONS':
				$result = __('Options and Extra Services', 'vikrentcar');
				break;
			case 'VRCPROWHYOPTIONSDESC':
				$result = __('Let customers choose extras and optional services for their rental orders. Such services will be displayed in the order summary with separate rows with their own cost. Offer anything your clients may need for their journey.', 'vikrentcar');
				break;
			case 'VRCPROALREADYHAVEPRO':
				$result = __('Already purchased Vik Rent Car PRO? Upgrade to the PRO version <a href="#upgrade">here</a>.', 'vikrentcar');
				break;
			case 'VRCPROINCREASEORDERS':
				$result = __('Would you like to increase your rental orders?', 'vikrentcar');
				break;
			case 'VRCPROCREATEOWNRENTSYS':
				$result = __('Start creating your own Vehicles Rental System', 'vikrentcar');
				break;
			case 'VRCPROMOSTTRUSTED':
				$result = __('Vik Rent Car PRO: the most complete and trusted car rental system plugin for WordPress', 'vikrentcar');
				break;
			case 'VRCPROEASYANYONE':
				$result = __('Easy installation and setup for anyone', 'vikrentcar');
				break;
			case 'VRCPROFULLRESPONSIVE':
				$result = __('Fully responsive and mobile ready', 'vikrentcar');
				break;
			case 'VRCPROPOWERPRICING':
				$result = __('Powerful and flexible pricing system', 'vikrentcar');
				break;
			case 'VRCPROSEASONSONECLICK':
				$result = __('Set up your daily/seasonal prices with just a few clicks', 'vikrentcar');
				break;
			case 'VRCPROCONFIGOPTIONS':
				$result = __('Configure Options and Extra Services', 'vikrentcar');
				break;
			case 'VRCPROOCCUPREPORT':
				$result = __('Occupancy Ranking report to analyse every detail', 'vikrentcar');
				break;
			case 'VRCPROOCCUPREPORTDESC':
				$result = __('Get to monitor your future occupancy through the Occupancy Ranking report. Filter the targets by dates and analyse the data by day, week or month. The report will provide the information about the occupancy, the total number of cars sold, days booked, revenues and more.', 'vikrentcar');
				break;
			case 'VRCPROWHYCUSTOMERSDESC':
				$result = __('Create your customers database on your website', 'vikrentcar');
				break;
			case 'VRCPROWHYPAYMENTSDESC':
				$result = __('PayPal, Offline Credit Card and Bank Transfer pre-installed', 'vikrentcar');
				break;
			case 'VRCPROPROMOCOUPONS':
				$result = __('Promotions and Coupons', 'vikrentcar');
				break;
			case 'VRCPROPROMOCOUPONSDESC':
				$result = __('Create Promotions to change rental costs and generate discount coupons', 'vikrentcar');
				break;
			case 'VRCPROPMSREPORTS':
				$result = __('Financial Reports', 'vikrentcar');
				break;
			case 'VRCPROPMSREPORTSDESC':
				$result = __('Total Revenue, Top Countries, Occupancy Ranking and more', 'vikrentcar');
				break;
			case 'VRCPROWHYINVOICESDESC':
				$result = __('Generate invoices and send them to your customers via email', 'vikrentcar');
				break;
			case 'VRCPROWHYCHECKINDESC':
				$result = __('Manage, print or send via email the check-in document for your customers', 'vikrentcar');
				break;
			case 'VRCPROWHYGRAPHSDESC':
				$result = __('Monitor your business trends thanks to the Graphs & Report functions', 'vikrentcar');
				break;
			case 'VRCPROWHYLOCOOHFEESDESC':
				$result = __('Get the most out of a tailored pricing framework', 'vikrentcar');
				break;
			case 'VRCPROWHYMOREEXTRA':
				$result = __('and much more...', 'vikrentcar');
				break;
			case 'VRCPROREADYINCREASE':
				$result = __('Ready to increase your orders?', 'vikrentcar');
				break;
			case 'VRCPROREADYINCREASEDESC':
				$result = __('Get Vik Rent Car PRO and start now.', 'vikrentcar');
				break;
			case 'VRCPROWHATCLIENTSSAY':
				$result = __('This is what our customers say about Vik Rent Car', 'vikrentcar');
				break;
			case 'VRCPROWHATCLIENTSSAYDESC':
				$result = __('These Reviews are published on the official WordPress and Joomla repositories.', 'vikrentcar');
				break;

			/**
			 * @wponly - First Setup Dashboard
			 */
			case 'VRCFIRSTSETSHORTCODES':
				$result = __('Shortcodes in Pages/Posts', 'vikrentcar');
				break;
			case 'VRCDASHFIRSTSETUPSHORTCODES':
				$result = __('Shortcodes are necessary to display the contents of the plugin into the front-end section of your website. You should create some Shortcodes of various types, and use them onto some pages of your website to publish the desired contents.', 'vikrentcar');
				break;

			/**
			 * @wponly Definitions for the Shortcodes view
			 */
			case 'VRC_SC_VIEWFRONT':
				$result = __('View page in front site', 'vikrentcar');
				break;
			case 'VRC_SC_ADDTOPAGE':
				$result = __('Create page', 'vikrentcar');
				break;
			case 'VRC_SC_VIEWTRASHPOSTS':
				$result = __('View trashed posts', 'vikrentcar');
				break;
			case 'VRC_SC_ADDTOPAGE_HELP':
				$result = __('You can always create a custom page or post manually and use this Shortcode text inside it. By proceeding, a page containing this Shortcode will be created automatically.', 'vikrentcar');
				break;
			case 'VRC_SC_ADDTOPAGE_OK':
				$result = __('The Shortcode was successfully added to a new page of your website. Visit the new page in the front site to see the content (if any).', 'vikrentcar');
				break;

			/**
			 * @wponly - Sample Data texts
			 */
			case 'VRCDASHINSTSAMPLEDTXT':
				$result = __('Alternatively, you can install one Sample Data package to skip the initial setup steps.', 'vikrentcar');
				break;
			case 'VRCDASHINSTSAMPLEDBTN':
				$result = __('Select Sample Data', 'vikrentcar');
				break;
			case 'VRC_SAMPLEDATA_MENU_TITLE':
				$result = __('Vik Rent Car - Install Sample Data', 'vikrentcar');
				break;
			case 'VRC_SAMPLEDATA_INSTALL':
				$result = __('Install Sample Data', 'vikrentcar');
				break;
			case 'VRC_SAMPLEDATA_INTRO_DESCR':
				$result = __('Choose the type of Sample Data you would like to install. This operation will populate the plugin with some demo contents to complete the first configuration.', 'vikrentcar');
				break;
			case 'VRC_SAMPLEDATA_INTRO_SUBDESCR':
				$result = __('To undo the installation of the sample data, you can deactivate and delete the plugin for then re-installing it. Otherwise, you can modify or remove some demo contents according to your needs.', 'vikrentcar');
				break;

			/**
			 * @wponly - Free version texts
			 */
			case 'VRCFREEPAYMENTSDESCR':
				$result = __('Allow your guests to pay their orders online through your preferred bank gateway. The Pro version comes with an integration for PayPal Standard and two more payment solutions, but the framework could be extended by installing apposite payment plugins for Vik Rent Car for your preferred bank.', 'vikrentcar');
				break;
			case 'VRCFREECOUPONSDESCR':
				$result = __('Thanks to the coupon codes you can give your clients some dedicated discounts for their rental orders.', 'vikrentcar');
				break;
			case 'VRCFREEOPTIONSDESCR':
				$result = __('Allow your guests to book some extra services, either they are optional or mandatory. This function can be used to create services or fees like insurances, extra mileage/km, late drop off and anything else that could be booked with the cars.', 'vikrentcar');
				break;
			case 'VRCFREESEASONSDESCR':
				$result = __('This function will let you create seasonal prices, promotions or special rates for the weekends or any other day of the week. Those who are used to work with seasonal rates will find this feature fundamental.', 'vikrentcar');
				break;
			case 'VRCFREERESTRSDESCR':
				$result = __('The booking restrictions will let you define a minimum or maximum number of days of rent for specific cars and dates of the year. You could also allow or deny the pickup/return on some specific days of the week.', 'vikrentcar');
				break;
			case 'VRCFREECUSTOMERSDESCR':
				$result = __('Here you can manage all of your customers information, send specific email messages, and manage their documents.', 'vikrentcar');
				break;
			case 'VRCFREESTATSDESCR':
				$result = __('This page will display graphs and charts by showing important information and statistics about your rental orders, occupancy and revenue.', 'vikrentcar');
				break;
			case 'VRCFREECRONSDESCR':
				$result = __('Cron Jobs are essentials to automatize certain functions, such as to send email reminders to your clients before the pick-up, after the drop-off, remaining balance payments and much more.', 'vikrentcar');
				break;
			case 'VRCFREEREPORTSDESCR':
				$result = __('Reports are essentials to obtain and/or export data. You can use them to calculate your revenue on some dates, your occupancy, or to generate documents for your accountant. This framework is also extendable with custom PMS reports.', 'vikrentcar');
				break;
			case 'VRCFREELOCFEESDESCR':
				$result = __('Those who work with one or multiple locations can use this feature to define costs for certain combinations of pickup/drop off locations.', 'vikrentcar');
				break;
			case 'VRCFREEOOHFEESDESCR':
				$result = __('This feature will let you define some Out of Hours Fees for those rentals who start or end at certain times of day, maybe when the office should be closed.', 'vikrentcar');
				break;
		}

		return $result;
	}
}
