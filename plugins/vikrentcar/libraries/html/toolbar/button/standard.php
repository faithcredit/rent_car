<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	html.toolbar
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$id 		= !empty($displayData['id']) 		? $displayData['id'] 		: '';
$btnClass 	= !empty($displayData['class']) 	? $displayData['class'] 	: '';
$text 		= !empty($displayData['text']) 		? $displayData['text'] 		: '';
$action 	= !empty($displayData['action']) 	? $displayData['action'] 	: '';

?>

<button type="button" class="page-title-action <?php echo $btnClass; ?>"	
	<?php echo ($id ? "id=\"$id\"" : ""); ?>
	<?php echo ($btnClass ? "class=\"$btnClass\"" : ""); ?>
	<?php echo ($action ? "onclick=\"$action\"" : ""); ?>
>
	<span><?php echo $text; ?></span>
</button>
