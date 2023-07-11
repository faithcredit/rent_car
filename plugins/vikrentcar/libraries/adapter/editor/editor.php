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

/**
 * Editor class to handle WYSIWYG editors.
 *
 * @since 10.0
 */
abstract class JEditor
{
	/**
	 * Editor instances container.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * The editor type name.
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * Constructor
	 *
	 * @param   string  $editor  The editor name.
	 */
	public function __construct($editor = 'none')
	{
		$this->_name = !is_null($editor) ? $editor : 'none';
	}

	/**
	 * Returns the global Editor object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $editor  The editor to use.
	 *
	 * @return  self 	The Editor object.
	 */
	public static function getInstance($editor = 'none')
	{
		if (empty(static::$instances[$editor]))
		{
			if (!JLoader::import('adapter.editor.classes.' . $editor))
			{
				$editor = 'none';
				JLoader::import('adapter.editor.classes.' . $editor);
			}

			$classname = 'JEditor' . ucwords($editor);

			if (!class_exists($classname))
			{
				throw new Exception("The editor [$classname] does not exist.", 404);
			}

			$instance = new $classname($editor);

			if (!$instance instanceof JEditor)
			{
				throw new Exception("The editor [$classname] must be an instance of JEditor.", 500);
			}

			static::$instances[$editor] = $instance;
		}

		return static::$instances[$editor];
	}

	/**
	 * Displays the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $html     The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   integer  $col      The number of columns for the textarea.
	 * @param   integer  $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (@since 10.1.20). If not supplied the name is used.
	 * @param   array    $params   Associative array of editor parameters (@since 10.1.35).
	 *
	 * @return  string 	 The editor.
	 *
	 * @link 	https://codex.wordpress.org/Function_Reference/wp_editor
	 */
	public function display($name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $params = array())
	{
		$id = preg_replace("/[^a-zA-Z0-9_]+/", '_', $id ? $id : $name);

		ob_start();

		$this->render($name, $html, $width, $height, $col, $row, $buttons, $id, $params);

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
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
	 */
	abstract protected function render($name, $html, $width, $height, $col, $row, $buttons, $id, $params);
}
