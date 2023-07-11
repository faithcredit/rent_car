<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_search
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2019 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

class ModVikrentcarSearchHelper
{
	public static function mgetHoursMinutes($secs)
	{
		if ($secs >= 3600) {
			$op = $secs / 3600;
			$hours = floor($op);
			$less = $hours * 3600;
			$newsec = $secs - $less;
			$optwo = $newsec / 60;
			$minutes = floor($optwo);
		} else {
			$hours = "0";
			$optwo = $secs / 60;
			$minutes = floor($optwo);
		}
		$x[] = $hours;
		$x[] = $minutes;
		return $x;
	}
	
	public static function formatLocationClosingDays($clostr)
	{
		$ret = array();
		$cur_time = time();
		$x = explode(",", $clostr);
		foreach($x as $y) {
			if (strlen(trim($y)) > 0) {
				$parts = explode("-", trim($y));
				$date_ts = mktime(0, 0, 0, (int)$parts[1], (int)str_replace(':w', '', $parts[2]), (int)$parts[0]);
				$date = date('Y-n-j', $date_ts);
				if (strlen($date) > 0 && $date_ts >= $cur_time) {
					$ret[] = '"'.$date.'"';
				}
				if (strpos($parts[2], ':w') !== false) {
					$info_ts = getdate($date_ts);
					$ret[] = '"'.$info_ts['wday'].'"';
				}
			}
		}
		return $ret;
	}

	public static function loadRestrictions($filters = true, $cars = array())
	{
		$restrictions = array();
		$dbo = JFactory::getDbo();
		if (!$filters) {
			$q = "SELECT * FROM `#__vikrentcar_restrictions`;";
		} else {
			if (count($cars) == 0) {
				$q = "SELECT * FROM `#__vikrentcar_restrictions` WHERE `allcars`=1;";
			} else {
				$clause = array();
				foreach ($cars as $idr) {
					if (empty($idr)) continue;
					$clause[] = "`idrooms` LIKE '%-".intval($idr)."-%'";
				}
				if (count($clause) > 0) {
					$q = "SELECT * FROM `#__vikrentcar_restrictions` WHERE `allcars`=1 OR (`allcars`=0 AND (".implode(" OR ", $clause)."));";
				} else {
					$q = "SELECT * FROM `#__vikrentcar_restrictions` WHERE `allcars`=1;";
				}
			}
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allrestrictions = $dbo->loadAssocList();
			foreach ($allrestrictions as $k=>$res) {
				if (!empty($res['month'])) {
					$restrictions[$res['month']] = $res;
				} else {
					$restrictions['range'][$k] = $res;
				}
			}
		}
		return $restrictions;
	}
	
	public static function parseJsDrangeWdayCombo ($drestr)
	{
		$combo = array();
		if (strlen($drestr['wday']) > 0 && strlen($drestr['wdaytwo']) > 0 && !empty($drestr['wdaycombo'])) {
			$cparts = explode(':', $drestr['wdaycombo']);
			foreach ($cparts as $kc => $cw) {
				if (!empty($cw)) {
					$nowcombo = explode('-', $cw);
					$combo[intval($nowcombo[0])][] = intval($nowcombo[1]);
				}
			}
		}
		return $combo;
	}
	
	public static function setDropDatePlus()
	{
		$session = JFactory::getSession();
		$sval = $session->get('setDropDatePlus', '');
		if (!empty($sval)) {
			return $sval;
		} else {
			$dbo = JFactory::getDbo();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='setdropdplus';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		}
	}

	public static function getMinDaysAdvance($skipsession = false)
	{
		if ($skipsession) {
			$dbo = JFactory::getDbo();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='mindaysadvance';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (int)$s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrcminDaysAdvance', '');
			if (!empty($sval)) {
				return (int)$sval;
			} else {
				$dbo = JFactory::getDbo();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='mindaysadvance';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrcminDaysAdvance', $s[0]['setting']);
				return (int)$s[0]['setting'];
			}
		}
	}
	
	public static function getMaxDateFuture($skipsession = false)
	{
		if ($skipsession) {
			$dbo = JFactory::getDbo();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='maxdate';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrcmaxDateFuture', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDbo();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='maxdate';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrcmaxDateFuture', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getFirstWeekDay($skipsession = false)
	{
		if ($skipsession) {
			$dbo = JFactory::getDbo();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='firstwday';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrcfirstWeekDay', '');
			if (strlen($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDbo();
				$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='firstwday';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrcfirstWeekDay', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getTranslator()
	{
		return VikRentCar::getTranslator();
	}
	
}
