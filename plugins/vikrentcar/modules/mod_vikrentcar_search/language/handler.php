<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.language.handler');

/**
 * Switcher class to translate the VikRentCar Search widget languages.
 *
 * @since 	1.0
 */
class Mod_VikRentCar_SearchLanguageHandler implements JLanguageHandler
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

			case 'MOD_VIKRENTCAR_SEARCH':
				$result = __('VikRentCar Search Form', 'vikrentcar');
				break;
			case 'MOD_VIKRENTCAR_SEARCH_DESC':
				$result = __('Search Form to start booking cars.', 'vikrentcar');
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
			case 'MODSEARCH_ORIENTATION':
				$result = __('Form Orientation', 'vikrentcar');
				break;
			case 'MODSEARCH_ORIENTATION_DESC':
				$result = __('Choose whether to display the form vertically or horizontally. ', 'vikrentcar');
				break;
			case 'VERTICAL':
				$result = __('Vertical', 'vikrentcar');
				break;
			case 'HORIZONTAL':
				$result = __('Horizontal', 'vikrentcar');
				break;
			case 'PARAMHEADINGTEXT':
				$result = __('Heading Text', 'vikrentcar');
				break;
			case 'INTROT':
				$result = __('Introducing Text', 'vikrentcar');
				break;
			case 'CLOSET':
				$result = __('Closing Text', 'vikrentcar');
				break;
			case 'SHOWCAT':
				$result = __('Show Categories', 'vikrentcar');
				break;
			case 'SHOWLOC':
				$result = __('Show Pickup-DropOff Locations', 'vikrentcar');
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
			case 'SEARCHT':
				$result = __('Custom Search Button Text', 'vikrentcar');
				break;
			case 'SEARCHHELP':
				$result = __('Custom Search Button Text, Leave empty to use default value', 'vikrentcar');
				break;
			case 'SEARCHD':
				$result = __('Search', 'vikrentcar');
				break;
			case 'VRMPPLACE':
				$result = __('Pickup Location', 'vikrentcar');
				break;
			case 'VRMPICKUPCAR':
				$result = __('Pickup Date', 'vikrentcar');
				break;
			case 'VRMALLE':
				$result = __('Pickup Time', 'vikrentcar');
				break;
			case 'VRMRETURNCAR':
				$result = __('Drop Off Date', 'vikrentcar');
				break;
			case 'VRMALLEDROP':
				$result = __('Drop Off Time', 'vikrentcar');
				break;
			case 'VRMCARCAT':
				$result = __('Category', 'vikrentcar');
				break;
			case 'VRMALLCAT':
				$result = __('Any', 'vikrentcar');
				break;
			case 'VRMPLACERET':
				$result = __('Drop Off Location', 'vikrentcar');
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
			case 'VRCMLOCDAYCLOSED':
				$result = __('The location is closed on this day', 'vikrentcar');
				break;
			case 'FORCESINGLECATEGORYSEARCH':
				$result = __('Force Specific Category', 'vikrentcar');
				break;
			case 'FORCESINGLECATEGORYSEARCHHELP':
				$result = __('If enabled, the search module will check the availability only for the cars assigned to this specific category.', 'vikrentcar');
				break;
			case 'FORCESINGLECATEGORYSEARCHDISABLED':
				$result = __('-- Disabled --', 'vikrentcar');
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
		}

		return $result;
	}
}
