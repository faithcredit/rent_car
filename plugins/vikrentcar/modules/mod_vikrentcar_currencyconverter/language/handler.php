<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.language.handler');

/**
 * Switcher class to translate the VikRentCar Currency Converter widget languages.
 *
 * @since 	1.0
 */
class Mod_VikRentCar_CurrencyconverterLanguageHandler implements JLanguageHandler
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

			case 'MOD_VIKRENTCAR_CURRENCYCONVERTER':
				$result = __('VikRentCar Currency Converter', 'vikrentcar');
				break;
			case 'MOD_VIKRENTCAR_CURRENCYCONVERTER_DESC':
				$result = __('Allows your visitors to convert the prices into several currencies.', 'vikrentcar');
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
			case 'PARAMVRCONLY':
				$result = __('Show only within VikRentCar', 'vikrentcar');
				break;
			case 'PARAMVRCONLY_DESC':
				$result = __('Choose whether to show the conversion form only on the pages of the plugin VikRentCar', 'vikrentcar');
				break;
			case 'PARAMVRCONLY':
				$result = __('Show only within VikRentCar', 'vikrentcar');
				break;
			case 'PARAMALLOWEDCURRENCIES':
				$result = __('Allowed Currencies for conversion', 'vikrentcar');
				break;
			case 'PARAMCURNAME':
				$result = __('Currency Name Format', 'vikrentcar');
				break;
			case 'PARAMCURNAMEHELP':
				$result = __('i.e. the Three Letters format will print USD, the Full Name format will print United States Dollar while the Long format will print United States Dollar (USD)', 'vikrentcar');
				break;
			case 'PARAMCURNAMEFORMATONE':
				$result = __('Three Letters', 'vikrentcar');
				break;
			case 'PARAMCURNAMEFORMATTWO':
				$result = __('Full Name', 'vikrentcar');
				break;
			case 'PARAMCURNAMEFORMATTHREE':
				$result = __('Long', 'vikrentcar');
				break;
		}

		return $result;
	}
}
