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

$name 	= !empty($displayData['name'])   ? $displayData['name'] 			: '';
$class 	= !empty($displayData['class'])  ? ' ' . $displayData['class'] 		: '';
$id 	= !empty($displayData['id']) 	 ? "id=\"{$displayData['id']}\"" 	: '';

?>

<div class="postbox-container">

	<div class="postbox">
		<?php if (!empty($name)) { ?>
			<h2 class="hndle"><?php echo JText::_($name); ?></h2>
		<?php } ?>

		<div class="inside<?php echo $class; ?>" <?php echo $id; ?>>
