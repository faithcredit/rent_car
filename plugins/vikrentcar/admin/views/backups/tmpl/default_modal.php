<?php
/** 
 * @package     VikRentCar
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

?>

<div style="padding: 10px;">

	<div class="vrc-admin-container vrc-params-container-wide">

		<div class="vrc-params-container">

			<!-- ACTION - Select -->

			<div class="vrc-param-container">
				<div class="vrc-param-label"><?php echo JText::_('VRC_BACKUP_ACTION_LABEL'); ?> <sup>*</sup></div>
				<div class="vrc-param-setting">
					<select name="action" id="vrc-create-action-sel">
						<?php
						$options = [
							JHtml::_('select.option', 'create', JText::_('VRC_BACKUP_ACTION_CREATE')),
							JHtml::_('select.option', 'upload', JText::_('VRC_BACKUP_ACTION_UPLOAD')),
						];
						
						echo JHtml::_('select.options', $options);
						?>
					</select>
				</div>
			</div>

			<!-- TYPE - Select -->

			<div class="vrc-param-container backup-action-create">
				<div class="vrc-param-label"><?php echo JText::_('VRC_CONFIG_BACKUP_TYPE'); ?> <sup>*</sup></div>
				<div class="vrc-param-setting">
					<select name="type" id="vrc-create-type-sel">
						<?php
						$options = [];
			
						foreach ($this->exportTypes as $id => $type)
						{
							$options[] = JHtml::_('select.option', $id, $type->getName());
						}

						echo JHtml::_('select.options', $options, 'value', 'text', VRCFactory::getConfig()->get('backuptype'));
						?>
					</select>
				</div>
			</div>

			<div class="backup-action-upload" style="display: none;">
				<div class="vrc-dropfiles-target" style="position: relative;">
					<p class="icon">
						<i class="fas fa-upload" style="font-size: 48px;"></i>
					</p>

					<div class="lead">
						<a href="javascript: void(0);" id="upload-file"><?php echo JText::_('VRCMANUALUPLOAD'); ?></a>&nbsp;<?php echo JText::_('VRCDROPFILES'); ?>
					</div>

					<p class="maxsize">
						<?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', JHtml::_('vikrentcar.maxuploadsize')); ?>
					</p>

					<input type="file" id="legacy-upload" multiple style="display: none;"/>

					<div class="vrc-selected-archives" style="position: absolute; bottom: 6px; left: 6px; display: none;">
					
					</div>

					<div class="vrc-upload-progress" style="position: absolute; bottom: 6px; right: 6px; display: flex; visibility: hidden;">
						<progress value="0" max="100">0%</progress>
					</div>
				</div>
			</div>

		</div>

	</div>

</div>

<script>
	(function($) {
		'use strict';

		let dragCounter = 0;
		let file = 0;

		const addFile = (files) => {
			const bar = $('.vrc-selected-archives');

			if (files && files.length) {
				file = files[0];
				const badge = $('<span class="badge badge-info"></span>').text(file.name);
				bar.html(badge).show();
			} else {
				file = null;
				bar.hide().html('');
			}
		}

		const fileUpload = (formData, progressCallback) => {
			return new Promise((resolve, reject) => {
				$.ajax({
					xhr: () => {
						let xhrobj = $.ajaxSettings.xhr();
						if (xhrobj.upload) {
							xhrobj.upload.addEventListener('progress', (event) => {
								let percent = 0;
								let position = event.loaded || event.position;
								let total = event.total;
								if (event.lengthComputable) {
									percent = Math.ceil(position / total * 100);
								}
								
								if (progressCallback) {
									progressCallback(percent);
								}
							}, false);
						}
						return xhrobj;
					},
					url: '<?php echo VRCFactory::getPlatform()->getUri()->ajax('index.php?option=com_vikrentcar&task=backup.save'); ?>',
					type: 'POST',
					contentType: false,
					processData: false,
					cache: false,
					data: formData,
					success: (resp) => {
						resolve(resp);
					},
					error: (err) => {
						reject(err);
					}, 
				});
			});
		}

		const saveBackup = (btn) => {
			const formData = new FormData();

			const action = $('#vrc-create-action-sel').val();
			formData.append('ajax', 1);
			formData.append('backup_action', action);

			if (action === 'create') {
				formData.append('type', $('#vrc-create-type-sel').val());
			} else {
				formData.append('file', file);
			}

			const progressBox = $('.vrc-upload-progress');
			progressBox.css('visibility', 'visible');

			$(btn).prop('disabled', true);

			fileUpload(formData, (progress) => {
				// update progress
				progressBox.find('progress').val(progress).text(progress + '%');
			}).then((data) => {
				// auto-close the modal
				vrcCloseJModal('newbackup');

				// then schedule an auto-refresh
				setTimeout(() => {
					document.adminForm.submit();
				}, 1000);
			}).catch((error) => {
				alert(error.responseText || 'Error');
				$(btn).prop('disabled', false);

				progressBox.css('visibility', 'hidden');
			});
		}

		$(function() {
			$('#vrc-create-action-sel').on('change', function() {
				if ($(this).val() === 'create') {
					$('.backup-action-upload').hide();
					$('.backup-action-create').show();
				} else {
					$('.backup-action-create').hide();
					$('.backup-action-upload').show();
				}
			});

			// drag&drop actions on target div

			$('.vrc-dropfiles-target').on('drag dragstart dragend dragover dragenter dragleave drop', (e) => {
				e.preventDefault();
				e.stopPropagation();
			});

			$('.vrc-dropfiles-target').on('dragenter', function(e) {
				// increase the drag counter because we may
				// enter into a child element
				dragCounter++;

				$(this).addClass('drag-enter');
			});

			$('.vrc-dropfiles-target').on('dragleave', function(e) {
				// decrease the drag counter to check if we 
				// left the main container
				dragCounter--;

				if (dragCounter <= 0) {
					$(this).removeClass('drag-enter');
				}
			});

			$('.vrc-dropfiles-target').on('drop', function(e) {
				$(this).removeClass('drag-enter');
				
				addFile(e.originalEvent.dataTransfer.files);
			});

			$('.vrc-dropfiles-target #upload-file').on('click', function() {
				// unset selected files before showing the dialog
				$('input#legacy-upload').val(null).trigger('click');
			});

			$('input#legacy-upload').on('change', function() {
				addFile($(this)[0].files);
			});

			$('button[data-role="backup.save"]').on('click', function() {
				saveBackup(this);
			});
		});
	})(jQuery);
</script>
