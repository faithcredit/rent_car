<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.language.handler');

/**
 * Switcher class to translate the VikRentCar CARS widget languages.
 *
 * @since 	1.0
 */
class Mod_VikRentCar_CarsLanguageHandler implements JLanguageHandler
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
			 * Name, Description and Parameters
			 */

			case 'MOD_VIKRENTCAR_CARS':
				$result = __('VikRentCar Cars', 'vikrentcar');
				break;
			case 'MOD_VIKRENTCAR_CARS_DESC':
				$result = __('A carousel showing a list of vehicles.', 'vikrentcar');
				break;
			case 'TITLE':
				$result = __('Title', 'vikrentcar');
				break;
			case 'JLAYOUT':
				$result = __('Layout', 'vikrentcar');
				break;
			case 'JLAYOUT_DESC':
				$result = __('The layout of the module to use. The available layouts are contained within the <b>tmpl</b> folder of the module.', 'vikrentcar');
				break;
			case 'JMENUITEM':
				$result = __('Page', 'vikrentcar');
				break;
			case 'JMENUITEM_DESC':
				$result = __('Select a page to start the booking process. The page must use a VikRentCar shortcode.', 'vikrentcar');
				break;
			case 'JNO':
				$result = __('No', 'vikrentcar');
				break;
			case 'JYES':
				$result = __('Yes', 'vikrentcar');
				break;
			case 'USEGLOB':
				$result = __('Use Globals', 'vikrentcar');
				break;
			case 'NUMVEHICLESDISP':
				$result = __('Total Number of Vehicles Displayed', 'vikrentcar');
				break;
			case 'ORDERINGANDFILT':
				$result = __('Ordering and Filtering', 'vikrentcar');
				break;
			case 'BYPRICE':
				$result = __('By Price', 'vikrentcar');
				break;
			case 'BYNAME':
				$result = __('By Name', 'vikrentcar');
				break;
			case 'BYCATEGORY':
				$result = __('By Category', 'vikrentcar');
				break;
			case 'SORTING':
				$result = __('Sorting', 'vikrentcar');
				break;
			case 'ASCENDING':
				$result = __('Ascending', 'vikrentcar');
				break;
			case 'DESCENDING':
				$result = __('Descending', 'vikrentcar');
				break;
			case 'SHOWDESC':
				$result = __('Show Description', 'vikrentcar');
				break;
			case 'CURRENCYSYMB':
				$result = __('Currency Symbol', 'vikrentcar');
				break;
			case 'SHOWCATNAME':
				$result = __('Show Category Name', 'vikrentcar');
				break;
			case 'LAYOUTTYPE':
				$result = __('Layout Type', 'vikrentcar');
				break;
			case 'LAYOUTTYPEDESC':
				$result = __('Select a type of layout for the list', 'vikrentcar');
				break;
			case 'LAYOUTGRID':
				$result = __('Grid', 'vikrentcar');
				break;
			case 'LAYOUTSCROLL':
				$result = __('Scroll List', 'vikrentcar');
				break;
			case 'VEHICLESPERROW':
				$result = __('Vehicles per row', 'vikrentcar');
				break;
			case 'AUTOPLAY':
				$result = __('Autoplay', 'vikrentcar');
				break;
			case 'DOTS':
				$result = __('Dots', 'vikrentcar');
				break;
			case 'NAVIGATIONARROWS':
				$result = __('Navigation Arrows', 'vikrentcar');
				break;
			case 'CATEGORYFILT':
				$result = __('Filter by Category', 'vikrentcar');
				break;
			case 'ORDERINGTYPE':
				$result = __('Ordering Type', 'vikrentcar');
				break;
			case 'VRCMODCARSTARTFROM':
				$result = __('Starting from', 'vikrentcar');
				break;
			case 'VRCMODCARCONTINUE':
				$result = __('Continue', 'vikrentcar');
				break;
		}

		return $result;
	}
}
