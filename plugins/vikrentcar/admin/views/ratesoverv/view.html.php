<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikRentCarViewRatesoverv extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();

		$cid = VikRequest::getVar('cid', array(0));
		$sesscids = $session->get('vrcRatesOviewCids', array());
		if (empty($cid[0]) && is_array($sesscids) && count($sesscids)) {
			// load cars from session only if no car IDs requested
			$cid = $sesscids;
		}

		// first car ID
		$carid = (int)$cid[0];

		if (empty($carid)) {
			$q = "SELECT `id` FROM `#__vikrentcar_cars` ORDER BY `name` ASC LIMIT 1";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$carid = $dbo->loadResult();
			}
		}
		if (empty($carid)) {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
			exit;
		}
		// make sure to set at least the first index of cid[]
		$cid[0] = $carid;
		//

		$q = "SELECT `id`,`name` FROM `#__vikrentcar_cars` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$all_cars = $dbo->loadAssocList();
		// load car rows for all requested cars
		$carrows = array();
		$reqids = array();
		foreach ($cid as $rid) {
			if (empty($rid)) {
				continue;
			}
			array_push($reqids, (int)$rid);
		}
		$q = "SELECT * FROM `#__vikrentcar_cars` WHERE `id` IN (".implode(', ', $reqids).") ORDER BY `name`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			foreach ($rows as $row) {
				$carrows[$row['id']] = $row;
			}
		}
		if (!(count($carrows) > 0)) {
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
			exit;
		}
		// get all requested and valid car IDs
		$req_car_ids = array_keys($carrows);
		$session->set('vrcRatesOviewCids', $req_car_ids);

		$pdays_cal = VikRequest::getVar('days_cal', array());
		$pdays_cal = VikRentCar::filterNightsSeasonsCal($pdays_cal);
		$car_days_cal = explode(',', VikRentCar::getSeasoncalNights());
		$car_days_cal = VikRentCar::filterNightsSeasonsCal($car_days_cal);
		$seasons_cal = array();
		$seasons_cal_days = array();
		if (count($pdays_cal) > 0) {
			$seasons_cal_days = $pdays_cal;
		} elseif (count($car_days_cal) > 0) {
			$seasons_cal_days = $car_days_cal;
		} else {
			$q = "SELECT `days` FROM `#__vikrentcar_dispcost` WHERE `idcar`=".intval($carid)." ORDER BY `#__vikrentcar_dispcost`.`days` ASC LIMIT 7;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$nights_vals = $dbo->loadAssocList();
				$nights_got = array();
				foreach ($nights_vals as $night) {
					$nights_got[] = $night['days'];
				}
				$seasons_cal_days = VikRentCar::filterNightsSeasonsCal($nights_got);
			}
		}
		if (count($req_car_ids) > 1) {
			// it's useless to spend server resources to calculate the seasons calendar nights (LOS Pricing Overview) since it won't be displayed when more than 1 car
			$seasons_cal_days = array();
		}
		if (count($seasons_cal_days) > 0) {
			$q = "SELECT `p`.*,`tp`.`name`,`tp`.`attr`,`tp`.`idiva` FROM `#__vikrentcar_dispcost` AS `p` LEFT JOIN `#__vikrentcar_prices` `tp` ON `p`.`idprice`=`tp`.`id` WHERE `p`.`days` IN (".implode(',', $seasons_cal_days).") AND `p`.`idcar`=".$carid." ORDER BY `p`.`days` ASC, `p`.`cost` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				$arrtar = array();
				foreach ($tars as $tar) {
					$arrtar[$tar['days']][] = $tar;
				}
				$seasons_cal['nights'] = $seasons_cal_days;
				$seasons_cal['offseason'] = $arrtar;
				$q = "SELECT * FROM `#__vikrentcar_seasons` WHERE `idcars` LIKE '%-".$carid."-%';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$seasons = $dbo->loadAssocList();
					//Restrictions
					$all_restrictions = VikRentCar::loadRestrictions(true, array($carid));
					$all_seasons = array();
					$curtime = time();
					foreach ($seasons as $sk => $s) {
						if (empty($s['from']) && empty($s['to'])) {
							continue;
						}
						$now_year = !empty($s['year']) ? $s['year'] : date('Y');
						list($sfrom, $sto) = VikRentCar::getSeasonRangeTs($s['from'], $s['to'], $now_year);
						if ($sto < $curtime && empty($s['year'])) {
							$now_year += 1;
							list($sfrom, $sto) = VikRentCar::getSeasonRangeTs($s['from'], $s['to'], $now_year);
						}
						if ($sto >= $curtime) {
							$s['from_ts'] = $sfrom;
							$s['to_ts'] = $sto;
							$all_seasons[] = $s;
						}
					}
					if (count($all_seasons) > 0) {
						$vrc_df = VikRentCar::getDateFormat();
						$vrc_df = $vrc_df == "%d/%m/%Y" ? 'd/m/Y' : ($vrc_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
						$hcheckin = 0;
						$mcheckin = 0;
						$hcheckout = 0;
						$mcheckout = 0;
						$timeopst = VikRentCar::getTimeOpenStore();
						if (is_array($timeopst)) {
							$opent = VikRentCar::getHoursMinutes($timeopst[0]);
							$closet = VikRentCar::getHoursMinutes($timeopst[1]);
							$hcheckin = $opent[0];
							$mcheckin = $opent[1];
							// set default drop off time equal to pick up time to avoid getting extra days of rental
							$hcheckout = $hcheckin;
							$mcheckout = $mcheckin;
						}
						$all_seasons = VikRentCar::sortSeasonsRangeTs($all_seasons);
						$seasons_cal['seasons'] = $all_seasons;
						$seasons_cal['season_prices'] = array();
						$seasons_cal['restrictions'] = array();
						// calc price changes for each season and for each num-night
						foreach ($all_seasons as $sk => $s) {
							$checkin_base_ts = $s['from_ts'];
							$is_dst = date('I', $checkin_base_ts);
							foreach ($arrtar as $numnights => $tar) {
								$checkout_base_ts = $s['to_ts'];
								for($i = 1; $i <= $numnights; $i++) {
									$checkout_base_ts += 86400;
									$is_now_dst = date('I', $checkout_base_ts);
									if ($is_dst != $is_now_dst) {
										if ((int)$is_dst == 1) {
											$checkout_base_ts += 3600;
										} else {
											$checkout_base_ts -= 3600;
										}
										$is_dst = $is_now_dst;
									}
								}
								//calc check-in and check-out ts for the two dates
								$first = VikRentCar::getDateTimestamp(date($vrc_df, $checkin_base_ts), $hcheckin, $mcheckin);
								$second = VikRentCar::getDateTimestamp(date($vrc_df, $checkout_base_ts), $hcheckout, $mcheckout);
								$tar = VikRentCar::applySeasonsCar($tar, $first, $second, null, $s);
								$seasons_cal['season_prices'][$sk][$numnights] = $tar;
								//Restrictions
								if (count($all_restrictions) > 0) {
									$season_restr = VikRentCar::parseSeasonRestrictions($first, $second, $numnights, $all_restrictions);
									if (count($season_restr) > 0) {
										$seasons_cal['restrictions'][$sk][$numnights] = $season_restr;
									}
								}
							}
						}
					}
				}
			}
		}
		//calendar rates
		$todayd = getdate();
		$tsstart = mktime(0, 0, 0, $todayd['mon'], $todayd['mday'], $todayd['year']);
		$startdate = VikRequest::getString('startdate', '', 'request');
		if (!empty($startdate)) {
			$startts = VikRentCar::getDateTimestamp($startdate, 0, 0);
			if (!empty($startts)) {
				$session->set('vrcRatesOviewTs', $startts);
				$tsstart = $startts;
			}
		} else {
			$prevts = $session->get('vrcRatesOviewTs', '');
			if (!empty($prevts)) {
				$tsstart = $prevts;
			}
		}
		$carrates = array();
		// read the rates for the lowest number of nights for each car requested
		foreach ($req_car_ids as $nowcid) {
			$nowcarrates = array();
			/**
			 * Some types of price may not have a cost for 1 or 2 days,
			 * so joining by MIN(`days`) may exclude certain types of price.
			 * We need to manually get via PHP all types of price.
			 * 
			 * @since 	1.13
			 */
			$q = "SELECT `r`.`id`,`r`.`idcar`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` FROM `#__vikrentcar_dispcost` AS `r` LEFT JOIN `#__vikrentcar_prices` `p` ON `p`.`id`=`r`.`idprice` WHERE `r`.`idcar`=".(int)$nowcid." ORDER BY `r`.`days` ASC, `r`.`cost` ASC LIMIT 50;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$nowcarrates = $dbo->loadAssocList();
				$parsed_car_prices = array();
				foreach ($nowcarrates as $rrk => $rrv) {
					if (isset($parsed_car_prices[$rrv['idprice']])) {
						unset($nowcarrates[$rrk]);
						continue;
					}
					$nowcarrates[$rrk]['cost'] = round(($rrv['cost'] / $rrv['days']), 2);
					$nowcarrates[$rrk]['days'] = 1;
					$parsed_car_prices[$rrv['idprice']] = 1;
				}
			}
			$nowcarrates = array_values($nowcarrates);
			// push rates for this car
			$carrates[(int)$nowcid] = $nowcarrates;
		}
		//

		// read all the orders between these dates for all cars
		$booked_dates = array();
		$MAX_DAYS = 60;
		$info_start = getdate($tsstart);
		$endts = mktime(23, 59, 59, $info_start['mon'], ($info_start['mday'] + $MAX_DAYS), $info_start['year']);
		$q = "SELECT `b`.*,`o`.`id` AS `idorder` FROM `#__vikrentcar_busy` AS `b`,`#__vikrentcar_orders` AS `o` WHERE `b`.`idcar` IN (".implode(', ', $reqids).") AND `b`.`id`=`o`.`idbusy` AND (`b`.`ritiro`>=".$tsstart." OR `b`.`consegna`>=".$tsstart.") AND (`b`.`ritiro`<=".$endts." OR `b`.`consegna`<=".$tsstart.");";
		$dbo->setQuery($q);
		$dbo->execute();
		$rbusy = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
		$cidbusy = array();
		foreach ($rbusy as $rb) {
			if (!isset($cidbusy[$rb['idcar']])) {
				$cidbusy[$rb['idcar']] = array();
			}
			array_push($cidbusy[$rb['idcar']], $rb);
		}
		foreach ($req_car_ids as $nowcid) {
			$booked_dates[(int)$nowcid] = isset($cidbusy[(int)$nowcid]) ? $cidbusy[(int)$nowcid] : "";
		}
		
		$this->all_cars = &$all_cars;
		$this->carrows = &$carrows;
		$this->seasons_cal_days = &$seasons_cal_days;
		$this->seasons_cal = &$seasons_cal;
		$this->tsstart = &$tsstart;
		$this->carrates = &$carrates;
		$this->booked_dates = &$booked_dates;
		$this->req_car_ids = &$req_car_ids;
		$this->firstcar = &$carid;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINRATESOVERVIEWTITLE'), 'vikrentcar');
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentcar')) {
			JToolBarHelper::addNew('newseason', JText::_('VRMAINSEASONSNEW'));
			JToolBarHelper::spacer();
			JToolBarHelper::addNew('newrestriction', JText::_('VRMAINRESTRICTIONNEW'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::cancel( 'cancel', JText::_('VRBACK'));
		JToolBarHelper::spacer();
	}

}
