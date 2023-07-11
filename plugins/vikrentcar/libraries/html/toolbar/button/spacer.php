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

$width = !empty($displayData['width']) ? $displayData['width'] : 0;

?>

<?php if ($width > 0) { ?>

	<span style="display: inline-block;width: <?php echo $width; ?>px;"></span>
	
<?php } ?>
