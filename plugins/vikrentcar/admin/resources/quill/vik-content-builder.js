/**
 * @package     VikRentCar
 * @subpackage  vik-content-builder
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com - https://e4j.com - https://e4jconnect.com
 */

// class handler to store the editor instances and switch building modes
class VikContentBuilder {

	static editors = new Array;

	static pushEditor(editor) {
		this.editors.push(editor);
	}

	// method for switching between building modes
	static switchMode(elem, mode) {
		var btn = jQuery(elem);
		if (mode) {
			btn = btn.closest('.vik-contentbuilder-wrapper').find('.vik-contentbuilder-switcher-btn[data-switch="' + mode + '"]');
		}
		var switch_to = btn.attr("data-switch");

		btn.parent(".vik-contentbuilder-switcher").find(".vik-contentbuilder-switcher-btn").removeClass("vik-contentbuilder-switcher-btn-active");
		btn.addClass("vik-contentbuilder-switcher-btn-active");

		var ed_conts = btn.closest(".vik-contentbuilder-wrapper");
		ed_conts.find(".vik-contentbuilder-inner [data-switch]").not('[data-switch="' + switch_to + '"]').hide();

		var target = ed_conts.find('.vik-contentbuilder-inner [data-switch="' + switch_to + '"]');
		if (switch_to.indexOf('visual') >= 0 && !target.find('.vik-contentbuilder-editor-container').length) {
			// handle multiple visual modes (inline and modal)
			var append_to_child = target.attr('data-appendto');
			if (append_to_child) {
				ed_conts.find('.vik-contentbuilder-editor-container').appendTo(target.find(append_to_child));
			} else {
				ed_conts.find('.vik-contentbuilder-editor-container').appendTo(target);
			}
		}
		target.show();

		// handle text mode special tags
		var text_mode_tags = jQuery('.vik-contentbuilder-textmode-sptags');
		if (text_mode_tags.length && switch_to.indexOf('text') >= 0) {
			text_mode_tags.show();
		} else if (text_mode_tags.length && switch_to.indexOf('visual') >= 0) {
			text_mode_tags.hide();
		}
	}

}

// class handler for uploading an image file and display it through Quill
class VikContentBuilderImageHandler {
	
	constructor(editor, endpoint) {
		this.editor = editor;
		this.endpoint = endpoint || null;
	}

	setEndpoint(endpoint) {
		if (endpoint && endpoint.length) {
			this.endpoint = endpoint;
		}
		return this;
	}

	present() {
		var input = document.createElement('input');
		input.setAttribute('type', 'file');
		input.onchange = () => {
			var file = input.files[0];
			var rgxi = /^image\//;
			if (!file || !file.name || !rgxi.test(file.type)) {
				alert('Only images are allowed.');
				return;
			}
			this.upload(file);
		};
		input.click();
	}

	upload(file) {
		if (!file instanceof File) {
			console.error('Invalid file');
			return false;
		}

		if (!this.endpoint) {
			console.error('Missing endpoint');
			return false;
		}
		
		// build form data
		var formd = new FormData();
		formd.append('file', file);
		formd.append('type', 'image');

		// start progress
		this.startProgress();

		// perform the AJAX upload
		var that = this;
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
						that.setProgress(percent);
					}, false);
				}
				return xhrobj;
			},
			url: that.endpoint,
			type: 'POST',
			contentType: false,
			processData: false,
			cache: false,
			data: formd,
			success: function(resp) {
				try {
					resp = JSON.parse(resp);
					if (resp.status == 1) {
						that.complete(resp);
					} else {
						throw resp.error ? resp.error : 'An error occurred! Please try again.';
					}
				} catch (err) {
					that.stopProgress();
					console.warn(err, resp);
					alert(err);
				}
			},
			error: function(err) {
				that.stopProgress();
				console.error(err.responseText);
				alert('An error occurred. Please try again.');
			}, 
		});
	}

	complete(resp) {
		this.stopProgress();
		this.editor.insertEmbed(this.editor.getSelection().index, 'image', resp.url);
	}

	startProgress() {
		// access the toolbar module
		var toolbar = null;
		try {
			toolbar = this.editor.getModule('toolbar').container;
		} catch(e) {
			// do nothing
		}
		if (!toolbar) {
			return false;
		}

		// create progress element
		this.progress_el = document.createElement("PROGRESS");
		this.progress_el.setAttribute('max', 100);
		this.progress_el.setAttribute('value', 0);

		// append progress to toolbar
		toolbar.appendChild(this.progress_el);
	}

	setProgress(value) {
		if (!this.progress_el) {
			return false;
		}
		this.progress_el.setAttribute('value', value);
	}

	stopProgress() {
		if (!this.progress_el) {
			return false;
		}
		this.progress_el.remove();
		this.progress_el = null;
	}

}
