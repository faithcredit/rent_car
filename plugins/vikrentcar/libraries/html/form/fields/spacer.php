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

$name  = isset($displayData['name'])  ? $displayData['name']  : '';
$label = isset($displayData['label']) ? $displayData['label'] : '';
$id    = isset($displayData['id'])    ? $displayData['id']    : '';
$class = isset($displayData['class']) ? $displayData['class'] : '';

if ($label)
{
	?>
	<h4
		name="<?php echo $name; ?>"
		<?php echo $id ? 'id="' . $id . '"' : ''; ?>
		<?php echo $class ? 'class="' . $class . '"' : ''; ?>
	><?php echo JText::_($label); ?></h4>
	<?php

}
else
{
	?>
	<hr
		name="<?php echo $name; ?>"
		<?php echo $id ? 'id="' . $id . '"' : ''; ?>
		<?php echo $class ? 'class="' . $class . '"' : ''; ?>
	/>
	<?php
}
