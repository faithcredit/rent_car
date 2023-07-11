<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	html.form
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$name  		= isset($displayData['name']) 		? $displayData['name'] 			: '';
$value 		= isset($displayData['value']) 		? $displayData['value']			: '';
$id 		= isset($displayData['id'])			? $displayData['id'] 			: '';
$class 		= isset($displayData['class'])		? $displayData['class'] 		: '';
$req 		= isset($displayData['required']) 	? $displayData['required']		: 0;
$readonly 	= isset($displayData['readonly']) 	? $displayData['readonly']		: false;
$rows 		= isset($displayData['rows']) 		? $displayData['rows']			: false;
$cols 		= isset($displayData['cols']) 		? $displayData['cols']			: false;
$hint 		= isset($displayData['hint']) 		? $displayData['hint']			: '';

if ($req)
{
	$class = trim('required ' . $class);
}

?>

<textarea
	name="<?php echo esc_attr($name); ?>"
	id="<?php echo esc_attr($id); ?>"
	class="widefat <?php echo esc_attr($class); ?>"
	placeholder="<?php echo esc_attr($hint); ?>"
	<?php echo $readonly ? 'readonly="readonly"' : ''; ?>
	<?php echo $rows ? 'rows="'.$rows.'"' : ''; ?>
	<?php echo $cols ? 'cols="'.$cols.'"' : ''; ?>
><?php echo esc_attr($value); ?></textarea>