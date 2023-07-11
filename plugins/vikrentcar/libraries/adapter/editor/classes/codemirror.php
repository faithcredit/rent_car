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
 * Editor class to handle a Code Mirror editor.
 *
 * @since 10.0
 */
class JEditorCodeMirror extends JEditor
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
	 * @return  string 	 The editor.
	 *
	 * @link 	https://developer.wordpress.org/reference/functions/wp_enqueue_code_editor/
	 */
	protected function render($name, $html, $width, $height, $col, $row, $buttons, $id, $params)
	{
		$options = array();

		/**
		 * Check whether code mirror textarea should allow the code editing.
		 *
		 * @since 10.1.35
		 */
		$options['readOnly'] = !empty($params['readonly']) || !empty($params['disabled']) ? 'nocursor' : false;

		?>
		<textarea
			name="<?php echo esc_attr($name); ?>"
			id="<?php echo esc_attr($id); ?>"
			rows="<?php echo esc_attr($row); ?>"
			cols="<?php echo esc_attr($col); ?>"
		><?php echo htmlentities($html); ?></textarea>

		<?php

		// Make sure codemirror is supported by this version of WordPress (4.9.0 >).
		// If not, a plain textarea will be shown.
		if (function_exists('wp_enqueue_code_editor'))
		{
			/**
			 * Use the requested type of language, otherwise fallback to PHP if not supplied.
			 *
			 * @since 10.1.35
			 */
			$syntax = !empty($params['syntax']) ? $params['syntax'] : 'php';

			// enqueue code editor
			wp_enqueue_code_editor([
				'type'       => $syntax,
				'codemirror' => $options,
			]);

			/**
			 * Inject CodeMirror instance within Joomla.editors pool.
			 *
			 * @since 10.1.17
			 */
			JFactory::getDocument()->addScriptDeclaration(
<<<JS
(function($) {
	'use strict';

	$(function() {
		const editor = wp.codeEditor.initialize('$id');

		Joomla.editors.instances['$id'] = {
			id: 	  '$id',
			element:  editor,
			getValue: function() {	
				return this.element.codemirror.getValue();
			},
			setValue: function(text) {
				return this.element.codemirror.setValue(text);
			},
			getSelection: function() {
				return this.element.codemirror.getSelection();
			},
			replaceSelection: function(text) {
				this.element.codemirror.replaceSelection(text);
			},
		};
	});
})(jQuery);
JS
			);
		}
	}
}
