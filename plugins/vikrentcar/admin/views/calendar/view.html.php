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

class VikRentCarViewCalendar extends JViewVikRentCar {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		$aid = $cid[0];

		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$vid = $session->get('vrcCalVid', '');
		$aid = !empty($vid) && empty($aid) ? $vid : $aid;
		if (empty($aid)) {
			$q = "SELECT `id` FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC LIMIT 1";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$aid = $dbo->loadResult();
			}
		}
		if (empty($aid)) {
			VikError::raiseWarning('', 'No Cars.');
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
			exit;
		}

		$session->set('vrcCalVid', $aid);
		$pvmode = VikRequest::getString('vmode', '', 'request');
		$cur_vmode = $session->get('vikrentcarvmode', "");
		if (!empty($pvmode) && ctype_digit($pvmode)) {
			$session->set('vikrentcarvmode', $pvmode);
		} elseif (empty($cur_vmode)) {
			$session->set('vikrentcarvmode', "12");
		}
		$vmode = (int)$session->get('vikrentcarvmode', "12");
		$q = "SELECT `id`,`name`,`img`,`idplace`,`units`,`idretplace` FROM `#__vikrentcar_cars` WHERE `id`=".$dbo->quote($aid).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() != 1) {
			VikError::raiseWarning('', 'No Cars.');
			$mainframe->redirect("index.php?option=com_vikrentcar&task=cars");
			exit;
		}
		$car = $dbo->loadAssoc();
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_gpayments` ORDER BY `#__vikrentcar_gpayments`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$payments = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : '';
		$msg = "";
		$actnow = time();
		$ppickupdate = VikRequest::getString('pickupdate', '', 'request');
		$preleasedate = VikRequest::getString('releasedate', '', 'request');
		$ppickuph = VikRequest::getString('pickuph', '', 'request');
		$ppickupm = VikRequest::getString('pickupm', '', 'request');
		$preleaseh = VikRequest::getString('releaseh', '', 'request');
		$preleasem = VikRequest::getString('releasem', '', 'request');
		$pcustdata = VikRequest::getString('custdata', '', 'request');
		$pcustmail = VikRequest::getString('custmail', '', 'request');
		$padults = VikRequest::getString('adults', '', 'request');
		$pchildren = VikRequest::getString('children', '', 'request');
		$psetclosed = VikRequest::getString('setclosed', '', 'request');
		$num_cars = VikRequest::getInt('num_cars', '', 'request');
		$num_cars = empty($num_cars) || $num_cars <= 0 ? 1 : $num_cars;
		$pordstatus = VikRequest::getString('newstatus', '', 'request');
		$pordstatus = (empty($pordstatus) || !in_array($pordstatus, array('confirmed', 'standby')) ? 'confirmed' : $pordstatus);
		$pordstatus = intval($psetclosed) > 0 ? 'confirmed' : $pordstatus;
		$pcountrycode = VikRequest::getString('countrycode', '', 'request');
		$pt_first_name = VikRequest::getString('t_first_name', '', 'request');
		$pt_last_name = VikRequest::getString('t_last_name', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcustomer_id = VikRequest::getString('customer_id', '', 'request');
		$ppaymentid = VikRequest::getString('payment', '', 'request');
		$pcust_cost = VikRequest::getFloat('cust_cost', 0, 'request');
		$ptaxid = VikRequest::getInt('taxid', '', 'request');
		$ppickuploc = VikRequest::getInt('pickuploc', '', 'request');
		$pdropoffloc = VikRequest::getInt('dropoffloc', '', 'request');
		$pcarcost = VikRequest::getFloat('carcost', 0, 'request');
		$pidprice = VikRequest::getInt('idprice', 0, 'request');
		$pidtar = 0;
		$paymentmeth = '';
		if (!empty($ppaymentid) && is_array($payments)) {
			foreach ($payments as $pay) {
				if (intval($pay['id']) == intval($ppaymentid)) {
					$paymentmeth = $pay['id'].'='.$pay['name'];
					break;
				}
			}
		}
		if (!empty($ppickupdate) && !empty($preleasedate)) {
			if (VikRentCar::dateIsValid($ppickupdate) && VikRentCar::dateIsValid($preleasedate)) {
				$first = VikRentCar::getDateTimestamp($ppickupdate, $ppickuph, $ppickupm);
				$second = VikRentCar::getDateTimestamp($preleasedate, $preleaseh, $preleasem);
				$checkhourly = false;
				$hoursdiff = 0;
				if ($second > $first) {
					$secdiff = $second - $first;
					$daysdiff = $secdiff / 86400;
					if (is_int($daysdiff)) {
						if ($daysdiff < 1) {
							$daysdiff = 1;
						}
					} else {
						if ($daysdiff < 1) {
							$daysdiff=1;
							$checkhourly = true;
							$ophours = $secdiff / 3600;
							$hoursdiff = intval(round($ophours));
							if ($hoursdiff < 1) {
								$hoursdiff = 1;
							}
						} else {
							$sum = floor($daysdiff) * 86400;
							$newdiff = $secdiff - $sum;
							$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
							if ($maxhmore >= $newdiff) {
								$daysdiff = floor($daysdiff);
							} else {
								$daysdiff = ceil($daysdiff);
							}
						}
					}
					// if rate plan ID selected, get the tariff ID
					if (!empty($pidprice) && $pcarcost > 0 && !(intval($psetclosed) > 0)) {
						$q = "SELECT `id` FROM `#__vikrentcar_dispcost` WHERE `idcar`={$car['id']} AND `days`={$daysdiff} AND `idprice`={$pidprice};";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows()) {
							$pidtar = $dbo->loadResult();
						}
					}
					//if the car is totally booked or locked because someone is paying, the administrator is not able to make a reservation for that car
					$check_units = $car['units'];
					if ($num_cars > 1 && $num_cars <= $car['units']) {
						$check_units = $car['units'] - $num_cars + 1;
					}
					if (VikRentCar::carBookable($car['id'], $check_units, $first, $second) && VikRentCar::carNotLocked($car['id'], $car['units'], $first, $second)) {
						//Customer
						$q = "SELECT * FROM `#__vikrentcar_custfields` ORDER BY `ordering` ASC;";
						$dbo->setQuery($q);
						$dbo->execute();
						$all_cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
						$customer_cfields = array();
						$customer_extrainfo = array();
						$custdata_parts = explode("\n", $pcustdata);
						foreach ($custdata_parts as $cdataline) {
							if (!(strlen(trim($cdataline)) > 0)) {
								continue;
							}
							$cdata_parts = explode(':', $cdataline);
							if (!(strlen(trim($cdata_parts[0])) > 0) || count($cdata_parts) < 2 || !(strlen(trim($cdata_parts[1])) > 0)) {
								continue;
							}
							foreach ($all_cfields as $cf) {
								if (strpos($cdata_parts[0], JText::_($cf['name'])) !== false && !array_key_exists($cf['id'], $customer_cfields) && $cf['type'] != 'country') {
									$customer_cfields[$cf['id']] = trim($cdata_parts[1]);
									if (!empty($cf['flag'])) {
										$customer_extrainfo[$cf['flag']] = trim($cdata_parts[1]);
									}
									break;
								}
							}
						}
						$cpin = VikRentCar::getCPinIstance();
						$cpin->is_admin = true;
						$cpin->setCustomerExtraInfo($customer_extrainfo);
						$cpin->saveCustomerDetails($pt_first_name, $pt_last_name, $pcustmail, $pphone, $pcountrycode, $customer_cfields);
						//
						$realback = VikRentCar::getHoursCarAvail() * 3600;
						$realback += $second;
						//Calculate the order total if not empty cust_cost and > 0.00. Add taxes (if not empty), and consider the setting prices tax excluded to increase the total
						$set_total = 0;
						$set_taxes = 0;
						if ($pcust_cost > 0.00) {
							$set_total = $pcust_cost;
							if ($ptaxid > 0) {
								$q = "SELECT `i`.`aliq` FROM `#__vikrentcar_iva` AS `i` WHERE `i`.`id`=" . (int)$ptaxid . ";";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() > 0) {
									$aliq = $dbo->loadResult();
									if (floatval($aliq) > 0.00) {
										if (!VikRentCar::ivaInclusa()) {
											// add tax to the total amount
											$subt = 100 + (float)$aliq;
											$set_total = ($set_total * $subt / 100);
											// calculate tax
											$set_taxes = $set_total - $pcust_cost;
										} else {
											// calculate tax
											$cost_minus_tax = VikRentCar::sayCustCostMinusIva($pcust_cost, (int)$ptaxid);
											$set_taxes += ($pcust_cost - $cost_minus_tax);
										}
									}
								}
							}
						} elseif (!empty($pidprice) && $pcarcost > 0.00 && !(intval($psetclosed) > 0)) {
							// one website rate plan was selected, so we calculate total and taxes
							$set_total = $pcarcost;
							// find tax rate assigned to this rate plan
							$q = "SELECT `p`.`id`,`p`.`idiva`,`i`.`aliq` FROM `#__vikrentcar_prices` AS `p` LEFT JOIN `#__vikrentcar_iva` AS `i` ON `p`.`idiva`=`i`.`id` WHERE `p`.`id`=" . $pidprice . ";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows()) {
								$taxdata = $dbo->loadAssoc();
								$aliq = $taxdata['aliq'];
								if (floatval($aliq) > 0.00) {
									if (!VikRentCar::ivaInclusa()) {
										// add tax to the total amount
										$subt = 100 + (float)$aliq;
										$set_total = ($set_total * $subt / 100);
										// calculate tax
										$set_taxes = $set_total - $pcarcost;
									} else {
										// calculate tax
										$cost_minus_tax = VikRentCar::sayCustCostMinusIva($pcarcost, $taxdata['idiva']);
										$set_taxes += ($pcarcost - $cost_minus_tax);
									}
								}
							}
						}

						//Get current Joomla User ID
						$now_user = JFactory::getUser();
						$store_ujid = property_exists($now_user, 'id') && !empty($now_user->id) ? (int)$now_user->id : 0;

						$lid = 0;
						if ($pordstatus == 'confirmed') {
							$q = "INSERT INTO `#__vikrentcar_busy` (`idcar`,`ritiro`,`consegna`,`realback`,`stop_sales`) VALUES(".$car['id'].",".$first.",".$second.",".$realback.",".((int)$psetclosed > 0 ? '1' : '0').");";
							$dbo->setQuery($q);
							$dbo->execute();
							$lid = $dbo->insertid();
						}
						$sid = VikRentCar::getSecretLink();

						//VRC 1.7 Rev.2
						$locationvat = '';
						$q = "SELECT `p`.`name`,`i`.`aliq` FROM `#__vikrentcar_places` AS `p` LEFT JOIN `#__vikrentcar_iva` `i` ON `p`.`idiva`=`i`.`id` WHERE `p`.`id`='".intval($ppickuploc)."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$getdata = $dbo->loadAssocList();
							if (!empty($getdata[0]['aliq'])) {
								$locationvat = $getdata[0]['aliq'];
							}
						}

						// assign car specific unit
						$car_index = null;
						if ((int)$psetclosed < 1 && VRCFactory::getConfig()->get('autocarunit', 1)) {
							$car_indexes = VikRentCar::getCarUnitNumsUnavailable([
								'id' 	   => 0,
								'idcar'    => $car['id'],
								'ritiro'   => $first,
								'consegna' => $second,
							], true);
							if (!empty($car_indexes)) {
								$car_index = $car_indexes[0];
							}
						}

						// location fees
						if (!empty($ppickuploc) && !empty($pdropoffloc) && $set_total > 0) {
							$locfee = VikRentCar::getLocFee($ppickuploc, $pdropoffloc);
							if ($locfee) {
								// location fees overrides
								if (strlen($locfee['losoverride']) > 0) {
									$arrvaloverrides = array();
									$valovrparts = explode('_', $locfee['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($daysdiff, $arrvaloverrides)) {
										$locfee['cost'] = $arrvaloverrides[$daysdiff];
									}
								}
								$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $daysdiff) : $locfee['cost'];
								$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], []);
								$set_total += $locfeewith;
							}
						}

						// out of hours fees
						$oohfee = VikRentCar::getOutOfHoursFees($ppickuploc, $pdropoffloc, $first, $second, array('id' => $car['id']));
						if (count($oohfee) && $set_total > 0) {
							$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
							$set_total += $oohfeewith;
						}

						$q = "INSERT INTO `#__vikrentcar_orders` (`idbusy`,`custdata`,`ts`,`status`,`idcar`,`days`,`ritiro`,`consegna`,`idtar`,`custmail`,`sid`,`idplace`,`idreturnplace`,`idpayment`,`hourly`,`order_total`,`locationvat`,`carindex`,`phone`,`cust_cost`,`cust_idiva`,`tot_taxes`,`car_cost`) VALUES(".(!empty($lid) ? $lid : 'NULL').",".$dbo->quote($pcustdata).",'".$actnow."','".$pordstatus."','".$car['id']."','".$daysdiff."','".$first."','".$second."', " . (!empty($pidtar) ? $dbo->quote($pidtar) : "NULL") . ",".$dbo->quote($pcustmail).",'".$sid."',".(!empty($ppickuploc) ? "'".$ppickuploc."'" : "NULL").",".(!empty($pdropoffloc) ? "'".$pdropoffloc."'" : "NULL").",".$dbo->quote($paymentmeth).",'".($checkhourly ? "1" : "0")."', ".($set_total > 0 ? $dbo->quote($set_total) : "NULL").", ".(strlen($locationvat) > 0 ? "'".$locationvat."'" : "NULL").", " . (!empty($car_index) ? (int)$car_index : 'NULL') . ", " . $dbo->quote($pphone) . ", ".($pcust_cost > 0 ? $dbo->quote($pcust_cost) : "NULL").", ".($pcust_cost > 0 && !empty($ptaxid) ? $dbo->quote($ptaxid) : "NULL").", " . $dbo->quote($set_taxes) . ", " . ($pcarcost > 0.00 ? $dbo->quote($pcarcost) : "NULL") . ");";
						$dbo->setQuery($q);
						$dbo->execute();
						$newoid = $dbo->insertid();
						$msg = $newoid;
						//Customer Booking
						if (!(intval($cpin->getNewCustomerId()) > 0) && !empty($pcustomer_id) && !empty($pcustomer_pin)) {
							$cpin->setNewPin($pcustomer_pin);
							$cpin->setNewCustomerId($pcustomer_id);
						}
						$cpin->saveCustomerBooking($newoid);
						//end Customer Booking
						// Booking History
						VikRentCar::getOrderHistoryInstance()->setBid($newoid)->store('NB');
						if ($pordstatus == 'standy') {
							$q = "INSERT INTO `#__vikrentcar_tmplock` (`idcar`,`ritiro`,`consegna`,`until`,`realback`,`idorder`) VALUES(" . $car['id'] . "," . $first . "," . $second . ",'" . VikRentCar::getMinutesLock(true) . "','" . $realback . "', ".(int)$newoid.");";
							$dbo->setQuery($q);
							$dbo->execute();
							$mainframe->enqueueMessage(JText::_('VRCQUICKRESWARNSTANDBY'));
							$mainframe->redirect("index.php?option=com_vikrentcar&task=editbusy&cid[]=".$lid."&standbyquick=1");
						}
					} else {
						$msg = "0";
					}
				} else {
					VikError::raiseWarning('', 'Invalid Dates: current server time is '.date('Y-m-d H:i', $actnow).'. Reservation requested from '.date('Y-m-d H:i', $first).' to '.date('Y-m-d H:i', $second));
				}
			} else {
				VikError::raiseWarning('', 'Invalid Dates');
			}
		}
		
		$busy = "";
		$mints = mktime(0, 0, 0, date('m'), 1, date('Y'));
		$q = "SELECT `b`.*,`o`.`id` AS `idorder` FROM `#__vikrentcar_busy` AS `b` LEFT JOIN `#__vikrentcar_orders` `o` ON `b`.`id`=`o`.`idbusy` WHERE `b`.`idcar`='".$car['id']."' AND (`b`.`ritiro`>=".$mints." OR `b`.`consegna`>=".$mints.");";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$busy = $dbo->loadAssocList();
		}

		$q = "SELECT `id`,`name` FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$allc = $dbo->loadAssocList();

		$pickuparr = array();
		$dropoffarr = array();
		$pickupids = explode(";", $car['idplace']);
		$dropoffids = explode(";", $car['idretplace']);
		if (count($pickupids) > 0) {
			foreach ($pickupids as $k => $pick) {
				if (empty($pick)) {
					unset($pickupids[$k]);
				}
			}
			if (count($pickupids) > 0) {
				$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` WHERE `id` IN (".implode(", ", $pickupids).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$pickuparr = $dbo->loadAssocList();
				}
			}
		}
		if (count($dropoffids) > 0) {
			foreach ($dropoffids as $k => $drop) {
				if (empty($drop)) {
					unset($dropoffids[$k]);
				}
			}
			if (count($dropoffids) > 0) {
				$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` WHERE `id` IN (".implode(", ", $dropoffids).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$dropoffarr = $dbo->loadAssocList();
				}
			}
		}

		$this->car = &$car;
		$this->msg = &$msg;
		$this->allc = &$allc;
		$this->payments = &$payments;
		$this->busy = &$busy;
		$this->vmode = &$vmode;
		$this->pickuparr = &$pickuparr;
		$this->dropoffarr = &$dropoffarr;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINCALTITLE'), 'vikrentcar');
		JToolBarHelper::cancel( 'cancel', JText::_('VRBACK'));
		JToolBarHelper::spacer();
	}

}
