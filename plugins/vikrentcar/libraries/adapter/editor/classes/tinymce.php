<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.editor
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.editor.editor');

/**
 * Editor class to handle a TinyMCE editor.
 *
 * @since 10.0
 */
class JEditorTinyMCE extends JEditor
{
	/**
	 * @override
	 * Renders the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $html     The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   integer  $col      The number of columns for the textarea.
	 * @param   integer  $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (@since 10.1.20).
	 * @param   array    $params   Associative array of editor parameters (@since 10.1.35).
	 *
	 * @return  string   The editor.
	 *
	 * @link    https://codex.wordpress.org/Function_Reference/wp_editor
	 */
	protected function render($name, $html, $width, $height, $col, $row, $buttons, $id, $params)
	{
		$editor = array(
			'resize'                  => true,
			'wp_autoresize_on'        => true,
			'add_unload_trigger'      => false,
			'wp_keep_scroll_position' => true,
		);

		/**
		 * It is now possible to override the parameters sent to the
		 * TinyMCE JS editor instance.
		 * 
		 * @since 10.1.37
		 */
		if (isset($params['tinymce']))
		{
			// merge settings
			$editor = array_merge($editor, $params['tinymce']);
			// unset to avoid replacing the whole attribute while
			// merging the global settings
			unset($params['tinymce']);
		}

		$settings = array(
			'media_buttons'       => $buttons,
			'_content_editor_dfw' => true,
			'drag_drop_upload'    => true,
			'tabfocus_elements'   => 'content-html,save-post',
			'editor_height'       => $height,
			'tinymce'             => $editor,
			'textarea_name'       => $name,
		);

		/**
		 * It is now possible to override the parameters sent to the
		 * WordPress TinyMCE editor instance.
		 * 
		 * @since 10.1.37
		 */
		$settings = array_merge($settings, $params);

		// init editor
		wp_editor($html, $id, $settings);

		$document = JFactory::getDocument();

		/**
		 * Inject TinyMCE instance within Joomla.editors pool.
		 *
		 * @since 10.1.17
		 */
		$document->addScriptDeclaration(
<<<JS
(function($) {
	'use strict';

	$(function() {
		if (Joomla.editors.instances.$id) {
			tinyMCE.get('$id').remove();
		}

		Joomla.editors.instances['$id'] = {
			id: 	  '$id',
			instance: null,
			getValue: function() {
				// get TinyMCE editor
				var _e = tinyMCE.get(this.id);
				if (_e) {
					// editor exists, get contents
					return _e.getContent();
				}
				// get value from plain textarea
				return $('textarea#' + this.id).val();
			},
			setValue: function(text) {
				// get TinyMCE editor
				var _e = tinyMCE.get(this.id);
				if (_e) {
					// editor exists, set contents
					return _e.setContent(text);
				}
				// set value to plain textarea
				return $('textarea#' + this.id).val(text);
			},
			getSelection: function() {
				return this.instance.selection.getContent({
		          format: 'text'
		        });
			},
			replaceSelection: function(text) {
				this.instance.execCommand('mceInsertContent', false, text);
			},
		};

		__isReady(() => {
			// once the tinyMCE instance is ready...
			return tinyMCE.get('$id');
		}).then((editor) => {
			// ...register it into the CMS editors pool
			Joomla.editors.instances['$id'].instance = editor;
		});
	});
})(jQuery);
JS
		);

		// if we are doing AJAX, render the editor via javascript
		if (wp_doing_ajax())
		{
			$document->addScriptDeclaration(
<<<JS
tinyMCE.execCommand('mceAddEditor', true, '$id');
JS
			);
		}
	}
}
