<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$files = VikRentCar::getCustomerDocuments($this->customer['id']);

$vik = VikRentCar::getVrcApplication();

?>

<style>

	/* new medias */
	.vrc-dropfiles-target {
		padding: 5% 20px;
		text-align:center;
		border: 2px dashed  #bbb;
		border-radius: 4px;
		color: #999;
		background: #f8f8f8;
	}
	.vrc-dropfiles-target.drag-enter {
		border-color: #2B4;
	}
	.vrc-dropfiles-target .lead {
		font-size: 16px;
		font-weight: bold;
	}
	.vrc-dropfiles-target a:hover {
		text-decoration: none;
	}

	.vrc-uploaded-files {
		display: flex;
		flex-wrap: wrap;
	}
	.vrc-uploaded-files i.fa-file {
		font-size: 128px;
	}
	.vrc-uploaded-files .file-elem {
		width: calc(100% / 5);
		margin-bottom: 20px;
	}
	.vrc-uploaded-files .file-elem-inner {
		width: auto;
		display: inline-block;
		position: relative;
	}
	.vrc-uploaded-files .file-elem a {
		position: relative;
	}
	.vrc-uploaded-files .file-elem.uploading a,
	.vrc-uploaded-files .file-elem.removing a {
		cursor: default;
		color: #888;
	}
	.vrc-uploaded-files .file-elem.removing a {
		opacity: 0.7;
	}
	.vrc-uploaded-files .file-elem a .file-extension {
		position: absolute;
		bottom: 20px;
		left: 50%;
		color: #fff;
		transform: translateX(-50%);
		font-size: 30px;
		text-transform: uppercase;
		font-weight: 500;
	}
	.vrc-uploaded-files .file-elem .file-summary {
		word-break: break-all;
		margin-top: 5px;
	}
	.vrc-uploaded-files .file-elem .file-summary .filename {
		font-weight: 500;
	}
	.vrc-uploaded-files .file-elem .file-summary .filesize {
		font-size: smaller;
	}

	.vrc-uploaded-files .file-elem .delete-file {
		position: absolute;
		top: -10px;
		left: -10px;
		background: #fff;
		width: 32px;
		height: 32px;
		border-radius: 50%;
		color: #000;
		line-height: 32px;
		text-align: center;
		visibility: hidden;
	}
	.vrc-uploaded-files .file-elem .delete-file i.fa {
		font-size: 34px;
		color: #333;
	}
	.vrc-uploaded-files .file-elem .delete-file:hover i.fa {
		color: #555;
	}

	.vrc-uploaded-files .file-elem.do-shake .file-link {
		color: #888;
		cursor: default;
	}

	.vrc-uploaded-files .file-elem.do-shake .delete-file {
		visibility: visible;
	}

	.vrc-uploaded-files .file-elem.do-shake {    
		-webkit-animation: shake-files 0.3s ease-in-out 0.1s infinite alternate;
	}

	@-webkit-keyframes shake-files {
		from {
			-webkit-transform: rotate(4deg);
		}
		to {
			-webkit-transform-origin: center center;
			-webkit-transform: rotate(-4deg);
		}
	}

	.stop-managing-files-hint {
		background: #333c;
		color: #fff;
		padding: 20px 30px;
		text-align: center;
		position: fixed;
		bottom: 20px;
		border-radius: 6px;
		left: 50%;
		transform: translateX(-50%);
		font-size: 20px;
		width: 80%;
		font-weight: 500;
		display: none;
	}

	.drop-files-hint {
		float: right;
		margin-top: -25px;
		margin-right: 6px;
	}
	.drop-files-hint i.fa-question-circle {
		color: #999;
	}

	@media screen and (max-width: 1440px) {
		.vrc-uploaded-files .file-elem {
			width: calc(100% / 4);
		}
	}
	@media screen and (max-width: 1280px) {
		.vrc-uploaded-files .file-elem {
			width: calc(100% / 5);
		}
	}
	@media screen and (max-width: 800px) {
		.vrc-uploaded-files .file-elem {
			width: calc(100% / 4);
		}
	}
	@media screen and (max-width: 550px) {
		.vrc-uploaded-files .file-elem {
			width: calc(100% / 3);
		}
	}

</style>

<fieldset class="adminform">

	<div class="vrc-params-wrap">
		
		<legend class="adminlegend"><?php echo JText::_('VRCCUSTOMERDOCUMENTS'); ?></legend>
		
		<div class="vrc-params-container">
		
			<div class="vrc-dropfiles-target">
				<div class="vrc-uploaded-files" id="vrc-uploaded-files">
					
					<?php
					foreach ($files as $file)
					{
						?>
						<div class="file-elem" data-file="<?php echo $file->basename; ?>">
							<div class="file-elem-inner">
								<a href="<?php echo $file->url; ?>" target="_blank" class="file-link">
									<?php VikRentCarIcons::e('file'); ?>
									<span class="file-extension"><?php echo $file->ext; ?></span>
								</a>

								<div class="file-summary">
									<div class="filename"><?php echo $file->name; ?></div>
									<div class="filesize"><?php echo JHtml::_('number.bytes', $file->size, 'auto', 0); ?></div>
								</div>

								<a href="javascript:void(0);" class="delete-file"><?php VikRentCarIcons::e('times-circle'); ?></a>
							</div>
						</div>
						<?php
					}
					?>

				</div>

				<p class="icon">
					<i class="<?php echo VikRentCarIcons::i('upload'); ?>" style="font-size: 48px;"></i>
				</p>

				<div class="lead">
					<a href="javascript: void(0);" id="upload-file"><?php echo JText::_('VRCMANUALUPLOAD'); ?></a>&nbsp;<?php echo JText::_('VRCDROPFILES'); ?>
				</div>

				<p class="maxsize">
					<?php
					echo JText::sprintf(
						'JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', 
						JHtml::_('number.bytes', ini_get('upload_max_filesize'), 'auto', 0)
					);
					?>
				</p>

				<input type="file" id="legacy-upload" style="display: none;" multiple="multiple">
			</div>

			<div class="drop-files-hint">
				<?php
				echo $vik->createPopover(array(
					'title'     => 'Drop Files',
					'content'   => JText::_('VRCDROPFILESHINT'),
					'placement' => 'left',
				));
				?>
			</div>

		</div>

	</div>

</fieldset>

<div class="stop-managing-files-hint"><?php echo JText::_('VRCDROPFILESSTOPREMOVING'); ?></div>

<script>

	jQuery(document).ready(function() {

		var dragCounter = 0;

		// drag&drop actions on target div

		jQuery('.vrc-dropfiles-target').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
			e.preventDefault();
			e.stopPropagation();
		});

		jQuery('.vrc-dropfiles-target').on('dragenter', function(e) {
			// increase the drag counter because we may
			// enter into a child element
			dragCounter++;

			jQuery(this).addClass('drag-enter');
		});

		jQuery('.vrc-dropfiles-target').on('dragleave', function(e) {
			// decrease the drag counter to check if we 
			// left the main container
			dragCounter--;

			if (dragCounter <= 0) {
				jQuery(this).removeClass('drag-enter');
			}
		});

		jQuery('.vrc-dropfiles-target').on('drop', function(e) {

			jQuery(this).removeClass('drag-enter');
			
			var files = e.originalEvent.dataTransfer.files;
			
			execUploads(files);
			
		});

		jQuery('.vrc-dropfiles-target #upload-file').on('click', function() {

			jQuery('input#legacy-upload').trigger('click');

		});

		jQuery('input#legacy-upload').on('change', function() {
			
			execUploads(jQuery(this)[0].files);

		});

		// make all current files removable by pressing them
		makeFileRemovable();

		jQuery(window).keyup(function(event) {
			if (event.keyCode == 27) {
				CAN_REMOVE_FILES = false;
				jQuery('#vrc-uploaded-files .file-elem').removeClass('do-shake');

				jQuery('.stop-managing-files-hint').hide();
			}
		});

		jQuery('#vrc-uploaded-files .file-elem a.delete-file').on('click', fileRemoveThread);

	});
	
	// upload
	
	function execUploads(files) {
		if (CAN_REMOVE_FILES) {
			return false;
		}

		for (var i = 0; i < files.length; i++) {
			if (isFileSupported(files[i].name)) {
				var status = new UploadingFile();
				status.setFileNameSize(files[i].name, files[i].size);
				status.setProgress(0);
				
				jQuery('#vrc-uploaded-files').prepend(status.getHtml());

				makeFileRemovable(jQuery('#vrc-uploaded-files .file-elem:first-child a'));
				
				fileUploadThread(status, files[i]);
			} else {
				alert('File ' + files[i].name + ' not supported');
			}
		}
	}
	
	function UploadingFile() {
		// create parent
		this.fileBlock = jQuery('<div class="file-elem uploading"></div>');

		// create file link and append it to parent block
		this.fileUrl = jQuery('<a href="javascript:void(0);" target="_blank"><?php VikRentCarIcons::e('file'); ?></a>').appendTo(this.fileBlock);
		// create file extension
		this.fileExt = jQuery('<span class="file-extension"></span>').appendTo(this.fileUrl);

		// create file summary
		this.fileSummary = jQuery('<div class="file-summary"></div>').appendTo(this.fileBlock);

		// create file name
		this.fileName = jQuery('<div class="filename"></div>').appendTo(this.fileSummary);
		// create file size
		this.fileSize = jQuery('<div class="filesize"></div>').appendTo(this.fileSummary);

		// create remove link
		this.removeLink = jQuery('<a href="javascript:void(0);" class="delete-file"><?php VikRentCarIcons::e('times'); ?></a>').appendTo(this.fileBlock);

		this.removeLink.on('click', fileRemoveThread);
	 
		this.setFileNameSize = function(name, size) {
			// fetch name
			var match = name.match(/(.*?)\.([a-z0-9]{2,})$/i);

			if (match && match.length) {
				this.fileName.html(match[1]);
				this.fileExt.html(match[2]);
			} else {
				this.fileName.html(name);
			}

			// fetch size
			var sizeStr = "";

			if (size > 1024*1024) {
				var sizeMB = size/(1024*1024);
				sizeStr = sizeMB.toFixed(2)+" MB";
			} else if (size > 1024) {
				var sizeKB = size/1024;
				sizeStr = sizeKB.toFixed(2)+" kB";
			} else {
				sizeStr = size.toFixed(2)+" B";
			}

			this.fileSize.html(sizeStr);
		}
		
		this.setProgress = function(progress) {       
			var opacity = parseFloat(progress / 100);

			this.fileBlock.css('opacity', opacity);
		}
		
		this.complete = function(file) {
			this.setProgress(100);
			
			this.fileUrl.attr('href', file.url);
			this.fileName.html(file.name);
			this.fileExt.html(file.ext);
			this.fileSize.html(file.size);
			this.fileBlock.removeClass('uploading');
			this.fileBlock.attr('data-file', file.filename);
		}
		
		this.getHtml = function() {
			return this.fileBlock;
		}
	}

	var formData = null;
	
	function fileUploadThread(status, file) {
		jQuery.noConflict();
		
		formData = new FormData();
		formData.append('file', file);
		formData.append('customer', <?php echo (int) $this->customer['id']; ?>);
		
		var jqxhr = jQuery.ajax({
			xhr: function() {
				var xhrobj = jQuery.ajaxSettings.xhr();
				if (xhrobj.upload) {
					xhrobj.upload.addEventListener('progress', function(event) {
						var percent = 0;
						var position = event.loaded || event.position;
						var total = event.total;
						if (event.lengthComputable) {
							percent = Math.ceil(position / total * 100);
						}
						// update progress
						status.setProgress(percent);
					}, false);
				}
				return xhrobj;
			},
			url: 'index.php?option=com_vikrentcar&task=upload_customer_document',
			type: 'POST',
			contentType: false,
			processData: false,
			cache: false,
			data: formData,
			success: function(resp) {
				try {
					resp = JSON.parse(resp);

					if (resp.status == 1) {
						status.complete(resp);
					} else {
						throw resp.error ? resp.error : 'An error occurred! Please try again.';
					}
				} catch (err) {
					console.warn(err, resp);

					alert(err);

					status.fileBlock.remove();
				}
			},
			error: function(err) {
				console.error(err.responseText);

				status.fileBlock.remove();

				alert('An error occurred! Please try again.');
			}, 
		});
	}
	
	function isFileSupported(name) {
		return name.match(/\.(jpe?g|png|gif|bmp|pdf|zip|rar|txt|markdown|md|doc|odt|xls|ods|csv)$/i);
	}

	var CAN_REMOVE_FILES = false;

	function makeFileRemovable(selector) {
		if (!selector) {
			selector = '#vrc-uploaded-files .file-elem a';
		}

		jQuery(selector).each(function() {
			var timeout = null;

			jQuery(this).on('mousedown', function(event) {
				timeout = setTimeout(function() {
					CAN_REMOVE_FILES = true;

					jQuery('#vrc-uploaded-files .file-elem').addClass('do-shake');

					jQuery('.stop-managing-files-hint').show();
				}, 1000);
			}).on('mouseup mouseleave', function(event) {
				clearTimeout(timeout);
			}).on('click', function(event) {
				if (CAN_REMOVE_FILES) {
					event.preventDefault();
					event.stopPropagation();
					return false;
				}
			});
		});
	}

	function fileRemoveThread() {
		var elem = jQuery(this).closest('.file-elem');
		var file = jQuery(elem).attr('data-file');

		if (!file.length) {
			return false;
		}

		elem.addClass('removing');

		jQuery.ajax({
			url: 'index.php?option=com_vikrentcar&task=delete_customer_document',
			type: 'post',
			data: {
				file: file,
				customer: <?php echo (int) $this->customer['id']; ?>,
			},
		}).done(function(resp) {

			elem.remove();

			if (jQuery('#vrc-uploaded-files .file-elem').length == 0) {
				var esc = jQuery.Event('keyup', { keyCode: 27 });
				jQuery(window).trigger(esc);
			}

		}).fail(function(resp) {

			console.error(err.responseText);

			elem.removeClass('removing');

			alert('An error occurred! Please try again.');

		});
	}

</script>
