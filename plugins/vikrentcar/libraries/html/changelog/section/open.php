<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	html.changelog
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$title = isset($displayData['title']) ? $displayData['title'] : '';

?>
<div class="vikwp-changelog-section">
	<h4><?php echo $title; ?></h4>
	<ul class="vikwp-changelog-features">
