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

$id 	= !empty($displayData['id'])         ? $displayData['id']          : uniqid();
$title 	= !empty($displayData['title'])      ? $displayData['title']       : 'Title';
$body 	= !empty($displayData['body'])       ? $displayData['body']        : '';
$style 	= !empty($displayData['style'])      ? $displayData['style']       : '';
$url    = !empty($displayData['url'])        ? $displayData['url']         : '';
$footer = !empty($displayData['footer'])     ? $displayData['footer']      : '';
$esc    = !empty($displayData['keyboard'])   ? $displayData['keyboard']    : false;
$close  = isset($displayData['closeButton']) ? $displayData['closeButton'] : true;

?>

<div class="modal hide fade" id="jmodal-<?php echo $id; ?>" style="<?php echo $style; ?>" data-esc="<?php echo $esc ? 1 : 0; ?>">
	
	<div class="modal-header">
		<?php
		if ($close)
		{
			?>
			<span class="box-close">
				<button type="button" class="close" data-dismiss="modal">×</button>
			</span>
			<?php
		}
		?>
		<h3><?php echo $title; ?></h3>
	</div>

	<div class="modal-body-wrapper<?php echo $footer ? ' has-footer' : ''; ?>" id="jmodal-box-<?php echo $id; ?>">
		<?php if (!empty($body)) { ?>
			<div class="modal-body"><?php echo $body; ?></div>
		<?php } ?>
	</div>

	<?php
	if ($footer)
	{
		?>
		<div class="modal-footer"><?php echo $footer; ?></div>
		<?php
	}
	
	if ($url)
	{
		?>
		<input type="hidden" name="url" value="<?php echo $this->escape($url); ?>" />
		<?php
	}
	?>
	
</div>
