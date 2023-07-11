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

class VikRentCarViewGraphs extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDbo();
		$document = JFactory::getDocument();
		$session = JFactory::getSession();
		$document->addScript(VRC_ADMIN_URI . 'resources/Chart.min.js', array('version' => VIKRENTCAR_SOFTWARE_VERSION));
		$pid_car = VikRequest::getInt('id_car', '', 'request');
		$pstatsmode = VikRequest::getString('statsmode', '', 'request');
		$sess_statsmode = $session->get('vrViewStatsMode', '');
		$pstatsmode = empty($pstatsmode) && !empty($sess_statsmode) ? $sess_statsmode : $pstatsmode;
		$pstatsmode = in_array($pstatsmode, array('ts', 'nights')) ? $pstatsmode : 'ts';
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$sess_from = $session->get('vrViewStatsFrom', '');
		$pdfrom = empty($pdfrom) && !empty($sess_from) ? $sess_from : $pdfrom;
		$fromts = !empty($pdfrom) ? VikRentCar::getDateTimestamp($pdfrom, '0', '0') : 0;
		$pdto = VikRequest::getString('dto', '', 'request');
		$sess_to = $session->get('vrViewStatsTo', '');
		$pdto = empty($pdto) && !empty($sess_to) ? $sess_to : $pdto;
		$tots = !empty($pdto) ? VikRentCar::getDateTimestamp($pdto, '23', '59') : 0;
		$tots = $tots < $fromts ? 0 : $tots;
		//store last dates in session
		if (!empty($pdfrom)) {
			$session->set('vrViewStatsFrom', $pdfrom);
			$session->set('vrViewStatsTo', $pdto);
		}
		$session->set('vrViewStatsMode', $pstatsmode);
		//
		$arr_cars = array();
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$arr_cars = $dbo->loadAssocList();
		}
		$bookings = array();
		$arr_months = array();
		$arr_channels = array();
		$arr_countries = array();
		$arr_totals = array('total_income' => 0, 'nights_sold' => 0);
		$tot_cars_units = 0;
		//Dates Clauses
		$from_clause = "`o`.`ts`>=".$fromts;
		$to_clause = "`o`.`ts`<=".$tots;
		$order_by = "`o`.`ts` ASC";
		if ($pstatsmode == 'nights') {
			$from_clause = "`o`.`consegna`>=".$fromts;
			$to_clause = "`o`.`ritiro`<=".$tots;
			$order_by = "`o`.`ritiro` ASC";
		}
		//
		if (!empty($pid_car)) {
			//filter by car
			$q = "SELECT `o`.*,`b`.`stop_sales` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` `b` ON `b`.`id`=`o`.`idbusy` WHERE `o`.`status`='confirmed' AND `o`.`idcar`=".$pid_car.(!empty($fromts) ? " AND ".$from_clause : "").(!empty($tots) ? " AND ".$to_clause : "")." ORDER BY ".$order_by.";";
		} else {
			$q = "SELECT `o`.*,`b`.`stop_sales`".($pstatsmode == 'nights' ? ",(SELECT GROUP_CONCAT(`c`.`name` SEPARATOR ',') FROM `#__vikrentcar_cars` AS `c` WHERE `c`.`id`=`o`.`idcar`) AS `car_names`" : '')." FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_busy` `b` ON `b`.`id`=`o`.`idbusy` WHERE `o`.`status`='confirmed'".(!empty($fromts) ? " AND ".$from_clause : "").(!empty($tots) ? " AND ".$to_clause : "")." ORDER BY ".$order_by.";";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$bookings = $dbo->loadAssocList();
			$first_ts = $bookings[0]['ts'];
			end($bookings);
			$last_ts = $bookings[(key($bookings))]['ts'];
			reset($bookings);
			$fromts = empty($fromts) ? $first_ts : $fromts;
			$tots = empty($tots) ? $last_ts : $tots;
			foreach ($bookings as $bk => $o) {
				$info_ts = getdate(($pstatsmode == 'nights' ? $o['ritiro'] : $o['ts']));
				$monyear = $info_ts['mon'].'-'.$info_ts['year'];
				if ($pstatsmode == 'nights') {
					$is_closure = ($o['stop_sales'] == 1);
					//Prepare the cars booked array
					if (array_key_exists('car_names', $o) && strlen($o['car_names']) > 1) {
						if (!$is_closure) {
							$o['car_names'] = explode(',', $o['car_names']);
						} else {
							unset($o['car_names']);
						}
					}
					//Check and calculate average totals depending on the booked dates - set labels for months of check-in
					if ($o['order_total'] > 0) {
						if ($o['ritiro'] < $fromts || $o['consegna'] > $tots) {
							$days_in = 0;
							$oinfo_start = getdate($o['ritiro']);
							$oinfo_start = getdate(mktime($oinfo_start['hours'], $oinfo_start['minutes'], $oinfo_start['seconds'], $oinfo_start['mon'], ($oinfo_start['mday'] + 1), $oinfo_start['year']));
							$oinfo_end = getdate($o['consegna']);
							$ots_end = $oinfo_end[0];
							while ($oinfo_start[0] < $ots_end) {
								if ($oinfo_start[0] >= $fromts && $oinfo_start[0] <= $tots) {
									$days_in++;
									if ($days_in === 1) {
										//Reset variables for the month where the booking took place, it has to be the first night considered
										$monyear = $oinfo_start['mon'].'-'.$oinfo_start['year'];
									}
								}
								if ($oinfo_start[0] > $tots) {
									break;
								}
								$oinfo_start = getdate(mktime($oinfo_start['hours'], $oinfo_start['minutes'], $oinfo_start['seconds'], $oinfo_start['mon'], ($oinfo_start['mday'] + 1), $oinfo_start['year']));
							}
							$fullo_total = $o['order_total'];
							$o['order_total'] = round(($o['order_total'] / $o['days'] * $days_in), 2);
							//set new number of nights, percentage of the booked nights calculated and update booking
							$o['avg_stay_pcent'] = 100 * $days_in / $o['days'];
							$o['days'] = $days_in;
						}
					} elseif ($is_closure) {
						//car is closed, set the number of nights to 0 for statistics
						$o['avg_stay_pcent'] = 0;
						$o['days'] = 0;
					} else {
						//Total equal to 0 and not a closure
						$o['avg_stay_pcent'] = 0;
						$o['days'] = 0;
					}
					$bookings[$bk] = $o;
					//
					if (!($o['days'] > 0)) {
						//VRC 1.11 Nights-Mode should skip bookings with 0 nights
						continue;
					}
				}
				$arr_totals['total_income'] += $o['order_total'];
				$arr_totals['nights_sold'] += $o['days'];
				if (!empty($o['country'])) {
					if (!array_key_exists($o['country'], $arr_countries)) {
						$arr_countries[$o['country']] = 1;
					} else {
						$arr_countries[$o['country']]++;
					}
				}
				$channel = JText::_('VRCWEBSITECHANNEL');
				if (!in_array($channel, $arr_channels)) {
					$arr_channels[] = $channel;
				}
				if (!array_key_exists($monyear, $arr_months)) {
					$arr_months[$monyear] = array();
				}
				if (!array_key_exists($channel, $arr_months[$monyear])) {
					$arr_months[$monyear][$channel] = array($o);
				} else {
					$arr_months[$monyear][$channel][] = $o;
				}
			}
			if (count($arr_countries)) {
				asort($arr_countries);
				$arr_countries = array_reverse($arr_countries, true);
				$all_countries = array_keys($arr_countries);
				foreach ($all_countries as $kc => $country) {
					$all_countries[$kc] = $dbo->quote($country);
				}
				$q = "SELECT `country_name`,`country_3_code` FROM `#__vikrentcar_countries` WHERE `country_3_code` IN (".implode(',', $all_countries).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$countries_names = $dbo->loadAssocList();
					foreach ($countries_names as $kc => $vc) {
						$country_flag = '';
						if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$vc['country_3_code'].'.png')) {
							$country_flag = '<img src="'.VRC_ADMIN_URI.'resources/countries/'.$vc['country_3_code'].'.png'.'" title="'.$vc['country_name'].'" />';
						}
						$arr_countries[$vc['country_3_code']] = array('country_name' => $vc['country_name'], 'tot_bookings' => $arr_countries[$vc['country_3_code']], 'img' => $country_flag);
					}
				} else {
					$arr_countries = array();
				}
			}
			$q = "SELECT SUM(`units`) AS `tot` FROM `#__vikrentcar_cars` WHERE ".(!empty($pid_car) ? "`id`=".$pid_car : "`avail`=1").";";
			$dbo->setQuery($q);
			$dbo->execute();
			$tot_cars_units = (int)$dbo->loadResult();
		}
		
		$this->bookings = &$bookings;
		$this->arr_cars = &$arr_cars;
		$this->fromts = &$fromts;
		$this->tots = &$tots;
		$this->pstatsmode = &$pstatsmode;
		$this->arr_months = &$arr_months;
		$this->arr_channels = &$arr_channels;
		$this->arr_countries = &$arr_countries;
		$this->arr_totals = &$arr_totals;
		$this->tot_cars_units = &$tot_cars_units;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINGRAPHSTITLE'), 'vikrentcar');
		JToolBarHelper::cancel( 'canceledorder', JText::_('VRBACK'));
		JToolBarHelper::spacer();
	}

}
