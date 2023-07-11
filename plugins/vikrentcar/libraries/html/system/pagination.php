<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	html.system
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$total 	= isset($displayData['total'])  ? $displayData['total'] : 0;
$lim0 	= isset($displayData['lim0']) 	? $displayData['lim0'] 	: 0;
$lim 	= isset($displayData['lim']) 	? $displayData['lim']	: 0;
$active = isset($displayData['page']) 	? $displayData['page']	: 1;
$pages 	= isset($displayData['pages'])	? $displayData['pages']	: 1;
$links 	= isset($displayData['links'])  ? $displayData['links']	: array();

?>

<div class="tablenav bottom">

	<div class="tablenav-pages">
		<span class="displaying-num"><?php echo JText::sprintf('JPAGINATION_ITEMS', $total); ?></span>

		<span class="pagination-links">

			<?php if ($active > 2) { ?>

				<a class="first-page button" <?php echo $links['first']; ?>>
					<span class="screen-reader-text">First page</span>
					<span aria-hidden="true">«</span>
				</a>

			<?php } else { ?>
			
				<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>

			<?php } ?>

			<?php if ($active > 1) { ?>

				<a class="prev-page button" <?php echo $links['prev']; ?>>
					<span class="screen-reader-text">Previous page</span>
					<span aria-hidden="true">‹</span>
				</a>

			<?php } else { ?>
			
				<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>

			<?php } ?>

			<span class="screen-reader-text">Current Page</span>
			<span id="table-paging" class="paging-input">
				<span class="tablenav-paging-text">
					<?php echo JText::sprintf('JPAGINATION_PAGE_OF_TOT', $active, '<span class="total-pages">'.$pages.'</span>'); ?>
				</span>
			</span>

			<?php if ($active < $pages) { ?>

				<a class="next-page button" <?php echo $links['next']; ?>>
					<span class="screen-reader-text">Next page</span>
					<span aria-hidden="true">›</span>
				</a>

			<?php } else { ?>
			
				<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>

			<?php } ?>
			
			<?php if ($active < $pages - 1) { ?>

				<a class="last-page button" <?php echo $links['last']; ?>>
					<span class="screen-reader-text">Last page</span>
					<span aria-hidden="true">»</span>
				</a>

			<?php } else { ?>
			
				<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>

			<?php } ?>

		</span>
	</div>

	<br class="clear">

</div>
