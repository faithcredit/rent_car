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

jimport('joomla.form.formfield');

class JFormFieldVrccarid extends JFormField { 
	protected $type = 'vrccarid';
	
	function getInput() {
		$key = (!empty($this->element['key_field']) ? $this->element['key_field'] : 'value');
		$val = (!empty($this->element['value_field']) ? $this->element['value_field'] : $this->name);
		$cars="";
		$dbo = JFactory::getDbo();
		$q="SELECT `id`,`name` FROM `#__vikrentcar_cars` ORDER BY `#__vikrentcar_cars`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allvrc=$dbo->loadAssocList();
			foreach($allvrc as $vrc) {
				$cars.='<option value="'.$vrc['id'].'"'.($this->value == $vrc['id'] ? " selected=\"selected\"" : "").'>'.$vrc['name'].'</option>';
			}
		}
		$html = '<select class="inputbox" name="' . $this->name . '" >';
		$html .= '<option value=""></option>';
		$html .= $cars;
		$html .='</select>';
		return $html;
    }
}
