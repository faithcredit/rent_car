<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_cars
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2019 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// no direct access
defined('ABSPATH') or die('No script kiddies please!');

class Modvikrentcar_carsHelper
{
	public static function getCars($params)
	{
		$dbo = JFactory::getDBO();
		$vrc_tn = self::getTranslator();
		$showcatname = intval($params->get('showcatname')) == 1 ? true : false;
		$cars = array();
		$query = $params->get('query');
		if ($query == 'price') {
			//simple order by price asc
			$q = "SELECT `id`,`name`,`img`,`idcat`,`startfrom`,`short_info`,`idcarat` FROM `#__vikrentcar_cars` WHERE `avail`='1';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$cars=$dbo->loadAssocList();
				$vrc_tn->translateContents($cars, '#__vikrentcar_cars');
				foreach ($cars as $k=>$c) {
					if ($showcatname) $cars[$k]['catname'] = self::getCategoryName($c['idcat']);
					if (strlen($c['startfrom']) > 0 && $c['startfrom'] > 0.00) {
						$cars[$k]['cost'] = $c['startfrom'];
					} else {
						$q = "SELECT `id`,`cost` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$c['id']." AND `days`='1' ORDER BY `#__vikrentcar_dispcost`.`cost` ASC LIMIT 1;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
							$cars[$k]['cost'] = $tar[0]['cost'];
						} else {
							$q = "SELECT `id`,`days`,`cost` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$c['id']." ORDER BY `#__vikrentcar_dispcost`.`cost` ASC LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$tar = $dbo->loadAssocList();
								$cars[$k]['cost'] = ($tar[0]['cost'] / $tar[0]['days']);
							} else {
								$cars[$k]['cost'] = 0;
							}
						}
					}
				}
			}
			$cars = self::sortCarsByPrice($cars, $params);
		} elseif ($query == 'name') {
			//order by name
			$q = "SELECT `id`,`name`,`img`,`idcat`,`startfrom`,`short_info`,`idcarat` FROM `#__vikrentcar_cars` WHERE `avail`='1' ORDER BY `#__vikrentcar_cars`.`name` ".strtoupper($params->get('order'))." LIMIT ".$params->get('numb').";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$cars=$dbo->loadAssocList();
				$vrc_tn->translateContents($cars, '#__vikrentcar_cars');
				foreach ($cars as $k=>$c) {
					if ($showcatname) $cars[$k]['catname'] = self::getCategoryName($c['idcat']);
					if (strlen($c['startfrom']) > 0 && $c['startfrom'] > 0.00) {
						$cars[$k]['cost'] = $c['startfrom'];
					} else {
						$q = "SELECT `id`,`cost` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$c['id']." AND `days`='1' ORDER BY `#__vikrentcar_dispcost`.`cost` ASC LIMIT 1;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
							$cars[$k]['cost'] = $tar[0]['cost'];
						} else {
							$q = "SELECT `id`,`days`,`cost` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$c['id']." ORDER BY `#__vikrentcar_dispcost`.`cost` ASC LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$tar = $dbo->loadAssocList();
								$cars[$k]['cost'] = ($tar[0]['cost'] / $tar[0]['days']);
							} else {
								$cars[$k]['cost'] = 0;
							}
						}
					}
				}
			}
		} else {
			//sort by category
			$q = "SELECT `id`,`name`,`img`,`idcat`,`info`,`startfrom`,`short_info`,`idcarat` FROM `#__vikrentcar_cars` WHERE `avail`='1' AND (`idcat`='".$params->get('catid').";' OR `idcat` LIKE '".$params->get('catid').";%' OR `idcat` LIKE '%;".$params->get('catid').";%' OR `idcat` LIKE '%;".$params->get('catid').";') ORDER BY `#__vikrentcar_cars`.`name` ".strtoupper($params->get('order'))." LIMIT ".$params->get('numb').";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$cars=$dbo->loadAssocList();
				$vrc_tn->translateContents($cars, '#__vikrentcar_cars');
				foreach ($cars as $k=>$c) {
					if ($showcatname) $cars[$k]['catname'] = self::getCategoryName($c['idcat']);
					if (strlen($c['startfrom']) > 0 && $c['startfrom'] > 0.00) {
						$cars[$k]['cost'] = $c['startfrom'];
					} else {
						$q = "SELECT `id`,`cost` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$c['id']." AND `days`='1' ORDER BY `#__vikrentcar_dispcost`.`cost` ASC LIMIT 1;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
							$cars[$k]['cost'] = $tar[0]['cost'];
						} else {
							$q = "SELECT `id`,`days`,`cost` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".(int)$c['id']." ORDER BY `#__vikrentcar_dispcost`.`cost` ASC LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$tar = $dbo->loadAssocList();
								$cars[$k]['cost'] = ($tar[0]['cost'] / $tar[0]['days']);
							} else {
								$cars[$k]['cost'] = 0;
							}
						}
					}
				}
			}
			if ($params->get('querycat') == 'price') {
				$cars = self::sortCarsByPrice($cars, $params);
			}
		}
		return $cars;
	}
	
	public static function sortCarsByPrice($arr, $params)
	{
		$newarr = array ();
		foreach ($arr as $k => $v) {
			$newarr[$k] = $v['cost'];
		}
		asort($newarr);
		$sorted = array ();
		foreach ($newarr as $k => $v) {
			$sorted[$k] = $arr[$k];
		}
		return $params->get('order') == 'desc' ? array_reverse($sorted) : $sorted;
	}
	
	public static function getCategoryName($idcat)
	{
		$vrc_tn = self::getTranslator();
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_categories` WHERE `id`='" . str_replace(";", "", $idcat) . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$p = $dbo->loadAssocList();
			$vrc_tn->translateContents($p, '#__vikrentcar_categories');
			return $p[0]['name'];
		}
		return '';
	}
	
	public static function limitRes($cars, $params)
	{
		return array_slice($cars, 0, $params->get('numb'));
	}

	public static function getTranslator()
	{
		return VikRentCar::getTranslator();
	}

	public static function numberFormat($numb)
	{
		return VikRentCar::numberFormat($numb);
	}

	public static function getCarCaratOriz($idc, $map = array(), $vrc_tn = null)
	{
		$dbo = JFactory::getDBO();
		$split = explode(";", $idc);
		$carat = "";
		$arr = array ();
		$where = array();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[] = (int)$s;
			}
		}
		if (count($where) > 0) {
			if (count($map) > 0) {
				foreach ($where as $c_id) {
					if (array_key_exists($c_id, $map)) {
						$arr[] = $map[$c_id];
					}
				}
			} else {
				$q = "SELECT * FROM `#__vikrentcar_caratteristiche` WHERE `id` IN (".implode(",", $where).") ORDER BY `#__vikrentcar_caratteristiche`.`ordering` ASC;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$arr = $dbo->loadAssocList();
					if (is_object($vrc_tn)) {
						$vrc_tn->translateContents($arr, '#__vikrentcar_caratteristiche');
					}
				}
			}
		}
		if (count($arr) > 0) {
			$carat .= "<div class=\"vrccaratsdiv\">";
			foreach ($arr as $a) {
				$carat .= "<div class=\"vrccarcarat\">";
				if (!empty ($a['textimg'])) {
					//tooltip icon text is not empty
					if (!empty($a['icon'])) {
						//an icon has been uploaded: display the image
						$carat .= "<span class=\"vrc-carat-cont\"><span class=\"vrc-expl\" data-vrc-expl=\"".$a['textimg']."\"><img src=\"".VRC_ADMIN_URI."resources/".$a['icon']."\" alt=\"" . $a['name'] . "\" /></span></span>\n";
					} else {
						if (strpos($a['textimg'], '</i>') !== false) {
							//the tooltip icon text is a font-icon, we can use the name as tooltip
							$carat .= "<span class=\"vrc-carat-cont\"><span class=\"vrc-expl\" data-vrc-expl=\"".$a['name']."\">".$a['textimg']."</span></span>\n";
						} else {
							//display just the text
							$carat .= "<span class=\"vrc-carat-cont\">".$a['textimg']."</span>\n";
						}
					}
				} else {
					$carat .= (!empty($a['icon']) ? "<span class=\"vrc-carat-cont\"><img src=\"".VRC_ADMIN_URI."resources/" . $a['icon'] . "\" alt=\"" . $a['name'] . "\" title=\"" . $a['name'] . "\"/></span>\n" : "<span class=\"vrc-carat-cont\">".$a['name']."</span>\n");
				}
				$carat .= "</div>";
			}
			$carat .= "</div>\n";
		}
		return $carat;
	}
	
}
