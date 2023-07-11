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

$name  		= isset($displayData['name'])     ? $displayData['name']     : '';
$value 		= isset($displayData['value'])    ? $displayData['value']    : '';
$id 		= isset($displayData['id'])       ? $displayData['id']       : uniqid();
$class 		= isset($displayData['class'])    ? $displayData['class']    : '';
$req 		= isset($displayData['required']) ? $displayData['required'] : 0;
$multiple   = isset($displayData['multiple']) ? $displayData['multiple'] : 0;

$upload_image_id  = $id . '-image-preview';
$upload_button_id = $id . '-upload-button';
$clear_button_id  = $id . '-clear-button';

if ($value)
{
	$img = JUri::root() . $value;
}
else
{
	$img = '';
}

JText::script('JMEDIA_CHOOSE_IMAGE');
JText::script('JMEDIA_CHOOSE_IMAGES');
JText::script('JMEDIA_SELECT');

?>

<div class="media-control<?php echo $class ? ' ' . $class : ''; ?>">

	<div class="image-preview-wrapper" id="<?php echo $upload_image_id; ?>" style="<?php echo $value ? '' : 'display: none'; ?>">
		<img src="<?php echo esc_attr($img); ?>" />
	</div>
		
	<button type="button" class="button" id="<?php echo $upload_button_id; ?>">
		<?php echo JText::_('JMEDIA_UPLOAD_BUTTON'); ?>
	</button>
	
	<button type="button" class="button" id="<?php echo $clear_button_id; ?>" style="<?php echo $value ? '' : 'display: none;'; ?>">
		<?php echo JText::_('JMEDIA_CLEAR_BUTTON'); ?>
	</button>

	<input type="hidden" class="<?php echo $req ? 'required' : ''; ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>" value="<?php echo $value; ?>" />

</div>

<script>
	
	jQuery(document).ready(function($) {
		var file_frame;
		var multiple = <?php echo $multiple ? 'true' : 'false'; ?>;

		jQuery('#<?php echo $upload_button_id; ?>').on('click', function(event) {
			event.preventDefault();

			// if the media frame already exists, reopen it
			if (file_frame) {
				file_frame.open();
				return;
			}

			// create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: Joomla.JText._(multiple ? 'JMEDIA_CHOOSE_IMAGES' : 'JMEDIA_CHOOSE_IMAGE'),
				button: {
					text: Joomla.JText._('JMEDIA_SELECT'),
				},
				multiple: multiple,
			});

			// when an image is selected, run a callback
			file_frame.on('select', function() {
				// extract selected image
				attachment = file_frame.state().get('selection').first().toJSON();

				var path = attachment.url.replace(/^<?php echo str_replace('/', '\\/', JUri::root()); ?>/, '');

				// clear current extra images 
				clearCurrentMultiSelection();

				// turn on preview box
				jQuery('#<?php echo $upload_image_id; ?>').show();
				// turn on clear button
				jQuery('#<?php echo $clear_button_id; ?>').show();

				// do something with attachment.id and/or attachment.url here
				jQuery('#<?php echo $upload_image_id; ?> img').attr('src', attachment.url);
				jQuery('#<?php echo $id; ?>').val(path);

				if (multiple) {
					// extract all selected images, first one excluded
					var list = file_frame.state().get('selection').toJSON().splice(1);
					
					for (var i = 0; i < list.length; i++) {
						path = list[i].url.replace(/^<?php echo str_replace('/', '\\/', JUri::root()); ?>/, '');

						jQuery('#<?php echo $upload_image_id; ?>').append('<img src="' + list[i].url + '" />\n');
						jQuery('#<?php echo $id; ?>').parent().append('<input type="hidden" class="multi-upload" name="<?php echo esc_attr($name); ?>" value="' + path + '" />');
					}
				}

				// trigger the event media.change
				jQuery(window).trigger('media.change');
			});

			// finally, open the modal
			file_frame.open();
		});

		jQuery('#<?php echo $clear_button_id; ?>').on('click', function(event) {
			event.preventDefault();

			// turn off button
			jQuery(this).hide();
			// turn off preview box
			jQuery('#<?php echo $upload_image_id; ?>').hide();

			// clear preview
			jQuery('#<?php echo $upload_image_id; ?> img').attr('src', '');
			// clear input
			jQuery('#<?php echo $id; ?>').val('');

			clearCurrentMultiSelection();
		});

		function clearCurrentMultiSelection() {
			if (multiple) {
				// remove from DOM additional input
				jQuery('#<?php echo $id; ?>').siblings().filter('.multi-upload').remove();
				// remove from DOM additional images
				jQuery('#<?php echo $upload_image_id; ?> img:not(:first-child)').remove();
			}
		}
	});

</script>
