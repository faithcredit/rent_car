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

class JFormFieldVrccategory extends JFormField { 
	protected $type = 'vrccategory';
	
	function getInput() {
		$key = (!empty($this->element['key_field']) ? $this->element['key_field'] : 'value');
		$val = (!empty($this->element['value_field']) ? $this->element['value_field'] : $this->name);
		$categories="";
		$dbo = JFactory::getDbo();
		$q="SELECT * FROM `#__vikrentcar_categories` ORDER BY `#__vikrentcar_categories`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allvrcc=$dbo->loadAssocList();
			foreach($allvrcc as $vrcc) {
				$categories.='<option value="'.$vrcc['id'].'"'.($this->value == $vrcc['id'] ? " selected=\"selected\"" : "").'>'.$vrcc['name'].'</option>';
			}
		}
		$html = '<select class="inputbox" name="' . $this->name . '" >';
		$html .= '<option value=""></option>';
		$html .= $categories;
		$html .='</select>';
		return $html;
    }
}
