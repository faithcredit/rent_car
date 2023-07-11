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
$min 		= isset($displayData['min'])		? $displayData['min'] 			: '';
$max 		= isset($displayData['max'])		? $displayData['max'] 			: '';
$step 		= isset($displayData['step'])		? $displayData['step'] 			: '';
$class 		= isset($displayData['class'])		? $displayData['class'] 		: '';
$req 		= isset($displayData['required']) 	? $displayData['required']		: 0;
$readonly 	= isset($displayData['readonly']) 	? $displayData['readonly']		: false;

if ($req)
{
	$class = trim('required ' . $class);
}

?>

<input
	type="number"
	name="<?php echo esc_attr($name); ?>"
	value="<?php echo esc_attr($value); ?>"
	id="<?php echo esc_attr($id); ?>"
	class="widefat <?php echo esc_attr($class); ?>"
	<?php echo $readonly ? 'readonly="readonly"' : ''; ?>
	<?php echo $min ? 'min="' . $min . '"' : ''; ?>
	<?php echo $max ? 'max="' . $max . '"' : ''; ?>
	<?php echo $step ? 'step="' . $step . '"' : ''; ?>
/>
