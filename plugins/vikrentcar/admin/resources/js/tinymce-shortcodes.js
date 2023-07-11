(function() {
	tinymce.PluginManager.add('vrc-shortcodes', function(editor, url) {
		// add Button to Visual Editor Toolbar
		editor.addButton('vrc-shortcodes', {
			title: 'VikRentCar Shortcodes List',
			cmd: 'vrc-shortcodes',
			icon: 'wp_code'
		});

		editor.addCommand('vrc-shortcodes', function() {
			openVikRentCarShortcodes(editor);
		});

	});
})();

var shortcodes_editor = null;

function openVikRentCarShortcodes(editor) {

	shortcodes_editor = editor;

	var html = '';

	for (var group in VIKRENTCAR_SHORTCODES) {

		html += '<div class="shortcodes-block">';
		html += '<div class="shortcodes-group"><a href="javascript: void(0);" onclick="toggleVikRentCarShortcode(this);">' + group + '</a></div>';
		html += '<div class="shortcodes-container">';

		for (var i = 0; i < VIKRENTCAR_SHORTCODES[group].length; i++) {
			var row = VIKRENTCAR_SHORTCODES[group][i];

			html += '<div class="shortcode-record" onclick="selectVikRentCarShortcode(this);" data-code=\'' + row.shortcode + '\'">';
			html += '<div class="maindetails">' + row.name + '</div>';
			html += '<div class="subdetails">';
			html += '<small class="postid">Post ID: ' + row.post_id + '</small>';
			html += '<small class="createdon">Created On: ' + row.createdon + '</small>';
			html += '</div>';
			html += '</div>';
		}

		html += '</div></div>';
	}

	jQuery('body').append(
		'<div id="vrc-shortcodes-backdrop" class="vrc-tinymce-backdrop"></div>\n'+
		'<div id="vrc-shortcodes-wrap" class="vrc-tinymce-modal wp-core-ui has-text-field" role="dialog" aria-labelledby="link-modal-title">\n'+
			'<form id="vrc-shortcodes" tabindex="-1">\n'+
				'<h1>VikRentCar Shortcodes List</h1>\n'+
				'<button type="button" onclick="dismissVikRentCarShortcodes();" class="vrc-tinymce-dismiss"><span class="screen-reader-text">Close</span></button>\n'+
				'<div class="vrc-tinymce-body">' + html + '</div>\n'+
				'<div class="vrc-tinymce-submitbox">\n'+
					'<div id="vrc-tinymce-cancel">\n'+
						'<button type="button" class="button" onclick="dismissVikRentCarShortcodes();">Cancel</button>\n'+
					'</div>\n'+
					'<div id="vrc-tinymce-update">\n'+
						'<button type="button" class="button button-primary" disabled onclick="putVikRentCarShortcode();">Add</button>\n'+
					'</div>\n'+
				'</div>\n'+
			'</form>\n'+
		'</div>\n'
	);

	jQuery('#vrc-shortcodes-backdrop').on('click', function() {
		dismissVikRentCarShortcodes();
	});
}

function dismissVikRentCarShortcodes() {
	jQuery('#vrc-shortcodes-backdrop, #vrc-shortcodes-wrap').remove();
}

function toggleVikRentCarShortcode(link) {
	var next = jQuery(link).parent().next();
	var show = next.is(':visible') ? false : true;

	jQuery('.shortcodes-container').slideUp();

	if (show) {
		next.slideDown();
	}
}

function selectVikRentCarShortcode(record) {
	jQuery('.shortcode-record').removeClass('selected');
	jQuery(record).addClass('selected');

	jQuery('#vrc-tinymce-update button').prop('disabled', false);
}

function putVikRentCarShortcode() {
	var shortcode = jQuery('.shortcode-record.selected').data('code');

	shortcodes_editor.execCommand('mceReplaceContent', false, shortcode);

	dismissVikRentCarShortcodes();
}
