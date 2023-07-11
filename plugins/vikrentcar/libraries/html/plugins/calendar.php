<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	html.plugins
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$value 	  = !empty($displayData['value'])    ? $displayData['value']    : '';
$name 	  = !empty($displayData['name'])     ? $displayData['name']     : uniqid();
$id 	  = !empty($displayData['id'])       ? $displayData['id']       : $name;
$class 	  = !empty($displayData['class'])    ? $displayData['class']    : '';
$format   = !empty($displayData['format'])   ? $displayData['format']   : 'Y-m-d';
$attr 	  = !empty($displayData['attr'])     ? $displayData['attr']     : '';
$showTime = !empty($displayData['showTime']) ? $displayData['showTime'] : false;

?>

<span class="wp-calendar-box">
	
	<input
		type="text"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		class="<?php echo $class; ?> wp-datepicker"
		value="<?php echo $value; ?>"
		data-value="<?php echo $value; ?>"
		autocomplete="off"
		<?php echo $attr; ?>
	/>

	<i class="dashicons dashicons-calendar-alt"></i>

</span>

<script>
	
	jQuery('input[name="<?php echo $name; ?>"]').on('change', function() {
		<?php if ($showTime) { ?>
			var curr = jQuery(this).val();
			var prev = jQuery(this).attr('data-value');

			if (!curr) {
				// do nothing in case of empty dates
				return;
			}

			// extract time from previous date set
			var time = prev.match(/ (\d{1,2}:\d{1,2})$/);

			// check if we have a time and the selected date doesn't
			if (time && !curr.match(/ (\d{1,2}:\d{1,2})$/)) {
				// append time to current date
				curr += ' ' + time.pop();
			}

			// update previous value with current one
			jQuery(this).attr('data-value', curr);
			jQuery(this).val(curr);
		<?php } ?>
	});

</script>
