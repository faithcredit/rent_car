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

class ModVikrentcar_CurConvHelper
{
	public static function getAllCurrencies(&$params)
	{
		$pcur = $params->get('currencies');
		$currencies = array();
		if (is_array($pcur) && count($pcur)) {
			foreach($pcur as $c) {
				if (!in_array($c, $currencies)) {
					$currencies[] = $c;
				}
			}
		}
		return $currencies;
	}
	
	public static function getCurrencyName()
	{
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			return $dbo->loadResult();
		}
		return '';
	}
	
}
