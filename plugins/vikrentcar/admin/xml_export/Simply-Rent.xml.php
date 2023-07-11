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

class vikRentCarXmlExport {
	
	private $orders;
	private $params;
	private $custom_fields;
	private $datetime_format;
	private $num_decimals;
	private $decimal_point;
	private $thousand_separator;

	private $anag_type;
	private $mappa_codici_sede;
	private $mappa_codici_gruppo_veicoli;
	private $targa_feature_key;
	private $mappa_codici_tariffe;
	
	static function getAdminParameters () {
		return array();
	}
	
	public function __construct ($orders, $params = array()) {
		$this->orders = $orders;
		$this->params = $params;
		$this->custom_fields = $this->loadCustomFields();
		$this->datetime_format = 'd/m/Y H:i';
		$this->num_decimals = 2;
		$this->decimal_point = ',';
		$this->thousand_separator = '';

		$this->anag_type = 'F'; //Persona Fisica
		//Codici Sede Gestionale (default empty)
		$this->mappa_codici_sede = array();
		/*
		$this->mappa_codici_sede = array(
			'1' => 'Codice Sede Gestionale con ID 1 in VikRentCar',
			'2' => 'Codice Sede Gestionale con ID 2 in VikRentCar',
			'5' => 'Codice Sede Gestionale con ID 5 in VikRentCar',
		);
		*/
		//Fine Codici Sede Gestionale
		//Codici Gruppo Veicoli Gestionale
		$this->mappa_codici_gruppo_veicoli = array();
		/*
		$this->mappa_codici_gruppo_veicoli = array(
			'1' => 'Codice Gruppo Veicolo con ID 1 in VikRentCar',
			'3' => 'Codice Gruppo Veicolo con ID 3 in VikRentCar',
			'5' => 'Codice Gruppo Veicolo con ID 5 in VikRentCar',
		);
		*/
		//Fine Codici Gruppo Veicoli Gestionale
		//Targa veicolo (di default License Plate corrisponde alla definizione lingua VRCDEFAULTDISTFEATUREONE nei files .ini)
		$this->targa_feature_key = 'VRCDEFAULTDISTFEATUREONE';
		//Codici Tariffe Gestionale
		$this->mappa_codici_tariffe = array();
		/*
		$this->mappa_codici_tariffe = array(
			'1' => 'Codice Tariffa per Type of Price con ID 1 in VikRentCar',
			'3' => 'Codice Tariffa per Type of Price con ID 3 in VikRentCar',
			'5' => 'Codice Tariffa per Type of Price con ID 5 in VikRentCar',
		);
		*/
	}
	
	public function generateXml () {
		$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		//Debug Array $this->orders
		//echo '<pre>'.print_r($this->orders, true).'</pre>';die;
		//
		foreach ($this->orders as $k => $order) {
			$xml .= '<nol>'."\n";
			//Sede di Uscita
			if(!empty($order['pickup_location_name']) && array_key_exists($order['idplace'], $this->mappa_codici_sede)) {
				$sede_uscita = $this->mappa_codici_sede[$order['idplace']];
			}else {
				$sede_uscita = $order['pickup_location_name'];
			}
			$xml .= "\t".'<sedeu>'.$sede_uscita.'</sedeu>'."\n";
			//
			//Sede di Rientro
			if(!empty($order['dropoff_location_name']) && array_key_exists($order['idreturnplace'], $this->mappa_codici_sede)) {
				$sede_rientro = $this->mappa_codici_sede[$order['idreturnplace']];
			}else {
				$sede_rientro = $order['dropoff_location_name'];
			}
			$xml .= "\t".'<seder>'.$sede_rientro.'</seder>'."\n";
			//
			//Data inizio noleggio
			$xml .= "\t".'<datau>'.date($this->datetime_format, $order['ritiro']).'</datau>'."\n";
			//
			//Data fine noleggio
			$xml .= "\t".'<datar>'.date($this->datetime_format, $order['consegna']).'</datar>'."\n";
			//
			//Gruppo Veicolo
			if(array_key_exists($order['idcar'], $this->mappa_codici_gruppo_veicoli)) {
				$gruppo_veicolo = $this->mappa_codici_gruppo_veicoli[$order['idcar']];
			}else {
				$gruppo_veicolo = $order['car_details']['name'];
			}
			$xml .= "\t".'<grpv>'.date($this->datetime_format, $order['consegna']).'</grpv>'."\n";
			//
			//Targa Veicolo
			if(!empty($order['carindex']) && !empty($order['car_details']['params'])) {
				$car_params = json_decode($order['car_details']['params'], true);
				if(array_key_exists('features', $car_params) && array_key_exists($order['carindex'], $car_params['features'])) {
					if(array_key_exists($this->targa_feature_key, $car_params['features'][$order['carindex']])) {
						$targa = $car_params['features'][$order['carindex']][$this->targa_feature_key];
					}else {
						foreach ($car_params['features'][$order['carindex']] as $v) {
							$targa = $v;
							break;
						}
					}
					$xml .= "\t".'<trgv>'.$targa.'</trgv>'."\n";
				}
			}
			//
			//Codice Tariffa
			if(array_key_exists($order['price_info']['idprice'], $this->mappa_codici_tariffe)) {
				$tariffa = $this->mappa_codici_tariffe[$order['price_info']['idprice']];
			}else {
				$tariffa = $order['price_info']['name'];
			}
			$xml .= "\t".'<tar>'.$tariffa.'</tar>'."\n";
			//
			//Importo Tariffa IVA esclusa
			$tariffa_no_iva = VikRentCar::sayCostMinusIva($order['price_info']['cost'], $order['price_info']['idprice']);
			$xml .= "\t".'<tar_imp>'.number_format($tariffa_no_iva, $this->num_decimals, $this->decimal_point, $this->thousand_separator).'</tar_imp>'."\n";
			//
			//Km Inclusi al giorno
			$km = -1;
			if(!empty($order['price_info']['attrdata'])) {
				$km = intval($order['price_info']['attrdata']);
			}
			$xml .= "\t".'<tar_km>'.$km.'</tar_km>'."\n";
			//
			//Giorni di noleggio
			$xml .= "\t".'<giorni>'.$order['days'].'</giorni>'."\n";
			//
			//Costo noleggio senza accessori IVA esclusa
			$xml .= "\t".'<cs_prev>'.number_format($tariffa_no_iva, $this->num_decimals, $this->decimal_point, $this->thousand_separator).'</cs_prev>'."\n";
			//
			//Costo Totale con accessori IVA esclusa
			$costo_totale = VikRentCar::sayCostMinusIva($order['order_total'], $order['price_info']['idprice']);
			$xml .= "\t".'<cs_tot>'.number_format($costo_totale, $this->num_decimals, $this->decimal_point, $this->thousand_separator).'</cs_tot>'."\n";
			//
			//Codice IVA
			$tax = $order['price_info']['aliq'];
			if(((float)$tax - abs($tax)) > 0) {
				$tax = number_format($tax, $this->num_decimals, $this->decimal_point, $this->thousand_separator);
			}else {
				$tax = intval($tax);
			}
			$xml .= "\t".'<codiva>'.$tax.'</codiva>'."\n";
			//
			//Costo Pagato
			$xml .= "\t".'<cs_pag>'.number_format($order['totpaid'], $this->num_decimals, $this->decimal_point, $this->thousand_separator).'</cs_pag>'."\n";
			//
			//Dati Anagrafici
			if(!empty($order['custdata'])) {
				$xml .= "\t".'<ana>'."\n";
				//Tipo Anagrafica
				$xml .= "\t\t".'<tp>'.$this->anag_type.'</tp>'."\n";
				//
				//Custom Fields (use the code $this->getCustomField(ID_CUSTOM_FIELD, $order['custdata']) to retrieve the desired custom field)
				//Cognome (ID 2 by default)
				$cognome = $this->getCustomField(2, $order['custdata']);
				$xml .= "\t\t".'<cognome>'.$cognome.'</cognome>'."\n";
				//Nome (ID 1 by default)
				$nome = $this->getCustomField(1, $order['custdata']);
				$xml .= "\t\t".'<nome>'.$nome.'</nome>'."\n";
				//Via
				$via = $this->getCustomField(3, $order['custdata']);
				$xml .= "\t\t".'<via>'.$via.'</via>'."\n";
				//Numero
				$num = $this->getCustomField(4, $order['custdata']);
				$xml .= "\t\t".'<num>'.$num.'</num>'."\n";
				//CAP
				$cap = $this->getCustomField(5, $order['custdata']);
				$xml .= "\t\t".'<cap>'.$cap.'</cap>'."\n";
				//Località
				$loc = $this->getCustomField(6, $order['custdata']);
				$xml .= "\t\t".'<loc>'.$loc.'</loc>'."\n";
				//Provincia
				$prv = $this->getCustomField(7, $order['custdata']);
				$xml .= "\t\t".'<prv>'.$prv.'</prv>'."\n";
				//Nazione (Custom Field of type Country)
				$xml .= "\t\t".'<naz>'.$order['country'].'</naz>'."\n";
				//Codice Fiscale
				$codfisc = $this->getCustomField(8, $order['custdata']);
				$xml .= "\t\t".'<codfisc>'.$codfisc.'</codfisc>'."\n";
				//Data di Nascita
				$ndata = $this->getCustomField(9, $order['custdata']);
				$xml .= "\t\t".'<ndata>'.$ndata.'</ndata>'."\n";
				//Località di Nascita
				$nloc = $this->getCustomField(10, $order['custdata']);
				$xml .= "\t\t".'<nloc>'.$nloc.'</nloc>'."\n";
				//Nazione di Nascita
				$nnaz = $this->getCustomField(11, $order['custdata']);
				$xml .= "\t\t".'<nnaz>'.$nnaz.'</nnaz>'."\n";
				//Nazionalità
				$nnal = $this->getCustomField(12, $order['custdata']);
				$xml .= "\t\t".'<nnal>'.$nnal.'</nnal>'."\n";
				//Fine Anagrafica cliente
				$xml .= "\t".'</ana>'."\n";
			}
			//
			//Fine Ordine
			$xml .= '</nol>'."\n";
		}

		return $xml;
	}

	private function loadCustomFields() {
		$dbo = JFactory::getDbo();
		$q = "SELECT * FROM `#__vikrentcar_custfields`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		$cfmap = array();
		if (is_array($cfields)) {
			foreach($cfields as $cf) {
				$cfmap[trim(JText::_($cf['name']))] = $cf['id'];
			}
		}
		
		return $cfmap;
	}

	private function getCustomField($cfid, $custdata) {
		$cfmapreplace = array();
		$cfmap = $this->custom_fields;
		$partsreceived = explode("\n", $custdata);
		if (count($partsreceived) > 0) {
			foreach($partsreceived as $pst) {
				if (!empty($pst)) {
					$tmpdata = explode(":", $pst);
					if (array_key_exists(trim($tmpdata[0]), $cfmap)) {
						$cfmapreplace[(int)$cfmap[trim($tmpdata[0])]] = trim($tmpdata[1]);
					}
				}
			}
		}
		if (array_key_exists($cfid, $cfmapreplace)) {
			return $cfmapreplace[$cfid];
		}

		return '';
	}
	
}


?>