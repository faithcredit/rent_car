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
$options 	= isset($displayData['options']) 	? $displayData['options']		: '';
$disabled 	= isset($displayData['disabled']) 	? $displayData['disabled']		: false;
$multiple 	= isset($displayData['multiple']) 	? $displayData['multiple']		: false;

if ($req)
{
	$class = trim('required ' . $class);
}

if (!is_array($options))
{
	$options = array();
}

if ($multiple)
{
	$name .= '[]';

	/**
	 * The value is always treaten as an array in order to avoid
	 * PHP notices while using in_array() function with strings.
	 *
	 * @since 1.1.7
	 */
	$value = (array) $value;
}

?>

<select
	name="<?php echo esc_attr($name); ?>"
	id="<?php echo esc_attr($id); ?>"
	class="widefat <?php echo esc_attr($class); ?>"
	<?php echo $disabled ? 'disabled' : ''; ?>
	<?php echo $multiple ? 'multiple' : ''; ?>
>

	<?php
	foreach ($options as $val => $text)
	{
		$selected = ($multiple && in_array($val, $value)) || $val == $value;
	?>

		<option
			value="<?php echo esc_attr($val); ?>"
			<?php echo $selected ? 'selected="selected"' : ''; ?>
		><?php echo JText::_($text); ?></option>

	<?php } ?>

</select>
