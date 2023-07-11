<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_currencyconverter
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2019 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

require_once VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikrentcar.php';

// require helper class
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php';

// module main file

// get widget id
$randid = str_replace('mod_vikrentcar_currencyconverter-', '', $params->get('widget_id', rand(1, 999)));
// get widget base URL
$baseurl = VRC_MODULES_URI;

$currencies  = ModVikrentcar_CurConvHelper::getAllCurrencies($params);
$def_currency = ModVikrentcar_CurConvHelper::getCurrencyName();
$vrconly = $params->get('vrconly');
$currencynameformat = (int)$params->get('currencynameformat');
$poption = JFactory::getApplication()->input->getString('option', '');

$currencymap = array(
	'ALL' => array('symbol' => '76'),
	'AFN' => array('symbol' => '1547'),
	'ARS' => array('symbol' => '36'),
	'AWG' => array('symbol' => '402'),
	'AUD' => array('symbol' => '36'),
	'AZN' => array('symbol' => '1084'),
	'BSD' => array('symbol' => '36'),
	'BBD' => array('symbol' => '36'),
	'BYR' => array('symbol' => '112', 'decimals' => 0),
	'BZD' => array('symbol' => '66'),
	'BMD' => array('symbol' => '36'),
	'BOB' => array('symbol' => '36'),
	'BAM' => array('symbol' => '75'),
	'BWP' => array('symbol' => '80'),
	'BGN' => array('symbol' => '1083'),
	'BRL' => array('symbol' => '82'),
	'BND' => array('symbol' => '36'),
	'KHR' => array('symbol' => '6107'),
	'CAD' => array('symbol' => '36'),
	'KYD' => array('symbol' => '36'),
	'CLP' => array('symbol' => '36', 'decimals' => 0),
	'CNY' => array('symbol' => '165'),
	'COP' => array('symbol' => '36', 'decimals' => 3),
	'CRC' => array('symbol' => '8353'),
	'HRK' => array('symbol' => '107'),
	'CUP' => array('symbol' => '8369'),
	'CZK' => array('symbol' => '75'),
	'DKK' => array('symbol' => '107'),
	'DOP' => array('symbol' => '82'),
	'XCD' => array('symbol' => '36'),
	'EGP' => array('symbol' => '163'),
	'SVC' => array('symbol' => '36'),
	'EEK' => array('symbol' => '107'),
	'EUR' => array('symbol' => '8364'),
	'FKP' => array('symbol' => '163'),
	'FJD' => array('symbol' => '36'),
	'GHC' => array('symbol' => '162'),
	'GIP' => array('symbol' => '163'),
	'GTQ' => array('symbol' => '81'),
	'GGP' => array('symbol' => '163'),
	'GYD' => array('symbol' => '36'),
	'HNL' => array('symbol' => '76'),
	'HKD' => array('symbol' => '36'),
	'HUF' => array('symbol' => '70', 'decimals' => 0),
	'ISK' => array('symbol' => '107', 'decimals' => 0),
	'IDR' => array('symbol' => '82'),
	'IRR' => array('symbol' => '65020'),
	'IMP' => array('symbol' => '163'),
	'ILS' => array('symbol' => '8362'),
	'JMD' => array('symbol' => '74'),
	'JPY' => array('symbol' => '165', 'decimals' => 0),
	'JEP' => array('symbol' => '163'),
	'KZT' => array('symbol' => '1083'),
	'KPW' => array('symbol' => '8361'),
	'KRW' => array('symbol' => '8361', 'decimals' => 0),
	'KGS' => array('symbol' => '1083'),
	'LAK' => array('symbol' => '8365'),
	'LVL' => array('symbol' => '76'),
	'LBP' => array('symbol' => '163'),
	'LRD' => array('symbol' => '36'),
	'LTL' => array('symbol' => '76'),
	'MKD' => array('symbol' => '1076'),
	'MYR' => array('symbol' => '82'),
	'MUR' => array('symbol' => '8360'),
	'MXN' => array('symbol' => '36'),
	'MNT' => array('symbol' => '8366'),
	'MZN' => array('symbol' => '77', 'decimals' => 0),
	'NAD' => array('symbol' => '36'),
	'NPR' => array('symbol' => '8360'),
	'ANG' => array('symbol' => '402'),
	'NZD' => array('symbol' => '36'),
	'NIO' => array('symbol' => '67'),
	'NGN' => array('symbol' => '8358'),
	'NOK' => array('symbol' => '107'),
	'OMR' => array('symbol' => '65020', 'decimals' => 3),
	'PKR' => array('symbol' => '8360'),
	'PAB' => array('symbol' => '66'),
	'PYG' => array('symbol' => '71', 'decimals' => 0),
	'PEN' => array('symbol' => '83'),
	'PHP' => array('symbol' => '8369'),
	'PLN' => array('symbol' => '122'),
	'QAR' => array('symbol' => '65020'),
	'RON' => array('symbol' => '108'),
	'RUB' => array('symbol' => '1088'),
	'SHP' => array('symbol' => '163'),
	'SAR' => array('symbol' => '65020'),
	'RSD' => array('symbol' => '1044'),
	'SCR' => array('symbol' => '8360'),
	'SGD' => array('symbol' => '36'),
	'SBD' => array('symbol' => '36'),
	'SOS' => array('symbol' => '83'),
	'ZAR' => array('symbol' => '82'),
	'LKR' => array('symbol' => '8360'),
	'SEK' => array('symbol' => '107'),
	'CHF' => array('symbol' => '67'),
	'SRD' => array('symbol' => '36'),
	'SYP' => array('symbol' => '163'),
	'TWD' => array('symbol' => '78'),
	'THB' => array('symbol' => '3647'),
	'TTD' => array('symbol' => '84'),
	'UAH' => array('symbol' => '8372'),
	'GBP' => array('symbol' => '163'),
	'USD' => array('symbol' => '36'),
	'UYU' => array('symbol' => '36'),
	'UZS' => array('symbol' => '1083'),
	'VEF' => array('symbol' => '66'),
	'VND' => array('symbol' => '8363'),
	'YER' => array('symbol' => '65020'),
	'ZWD' => array('symbol' => '90'),
	'TND' => array('decimals' => 3),
);

if (count($currencies) > 0) {
	if ((intval($vrconly) > 0 && strpos($poption, 'vikrentcar') !== false) || intval($vrconly) < 1) {
		require JModuleHelper::getLayoutPath('mod_vikrentcar_currencyconverter', $params->get('layout', 'default'));
	}
}
