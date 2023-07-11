<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.toolbar
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.toolbar.toolbar');

/**
 * Utility class for the button bar.
 *
 * @since 10.0
 */
abstract class JToolbarHelper
{
	/**
	 * The list containing the buttons to render.
	 *
	 * @var array
	 */
	protected $buttons = array();

	/**
	 * Title cell.
	 * For the title and toolbar to be rendered correctly,
	 * this title function must be called before the starttable function and the toolbars icons
	 * this is due to the nature of how the css has been used to position the title in respect to the toolbar.
	 *
	 * @param   string  $title  The title.
	 * @param   string  $icon   The space-separated names of the image.
	 *
	 * @return  void
	 */
	public static function title($title, $icon = null)
	{
		$bar = JToolbar::getInstance();

		$bar->setTitle($title);
	}

	/**
	 * Writes a spacer cell.
	 *
	 * @param   string  $width  The width for the cell.
	 *
	 * @return  void
	 */
	public static function spacer($width = null)
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Separator', 'spacer', $width);
	}

	/**
	 * Writes a divider between menu buttons.
	 *
	 * @return  void
	 */
	public static function divider()
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Separator', 'divider');
	}

	/**
	 * Writes a custom option and task button for the button bar.
	 *
	 * @param   string  $task        The task to perform (picked up by the switch($task) blocks).
	 * @param   string  $icon        The image to display.
	 * @param   string  $iconOver    The image to display when moused over.
	 * @param   string  $alt         The alt text for the icon image.
	 * @param   bool    $listSelect  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 */
	public static function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', $icon, $alt, $task, $listSelect);
	}

	/**
	 * Writes a cancel button that will go back to the previous page without doing
	 * any other operation.
	 *
	 * @param   string  $alt   Alternative text.
	 * @param   string  $href  URL of the href attribute.
	 *
	 * @return  void
	 */
	public static function back($alt = 'JTOOLBAR_BACK', $href = 'javascript:history.back();')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Link', 'back', $alt, $href);
	}

	/**
	 * Writes the common 'new' icon for the button bar.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @uses 	custom()
	 */
	public static function addNew($task = 'add', $alt = 'JTOOLBAR_NEW', $check = false)
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'new', $alt, $task, $check);
	}

	/**
	 * Writes a common 'publish' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function publishList($task = 'publish', $alt = 'JTOOLBAR_PUBLISH')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'publish', $alt, $task, true);
	}

	/**
	 * Writes a common 'unpublish' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function unpublishList($task = 'unpublish', $alt = 'JTOOLBAR_UNPUBLISH')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'unpublish', $alt, $task, true);
	}

	/**
	 * Writes a common 'archive' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function archiveList($task = 'archive', $alt = 'JTOOLBAR_ARCHIVE')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'archive', $alt, $task, true);
	}

	/**
	 * Writes an unarchive button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function unarchiveList($task = 'unarchive', $alt = 'JTOOLBAR_UNARCHIVE')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'unarchive', $alt, $task, true);
	}

	/**
	 * Writes a common 'edit' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function editList($task = 'edit', $alt = 'JTOOLBAR_EDIT')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'edit', $alt, $task, true);
	}

	/**
	 * Writes a common 'delete' button for a list of records.
	 *
	 * @param   string  $msg   Postscript for the 'are you sure' message.
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function deleteList($msg = '', $task = 'remove', $alt = 'JTOOLBAR_DELETE')
	{
		$bar = JToolbar::getInstance();

		if ($msg)
		{
			$bar->appendButton('Confirm', $msg, 'remove', $alt, $task, true);
		}
		else
		{
			$bar->appendButton('Standard', 'remove', $alt, $task, true);
		}
	}

	/**
	 * Writes a common 'trash' button for a list of records.
	 *
	 * @param   string  $task   An override for the task.
	 * @param   string  $alt    An override for the alt text.
	 * @param   bool    $check  True to allow lists.
	 *
	 * @return  void
	 */
	public static function trash($task = 'remove', $alt = 'JTOOLBAR_TRASH', $check = true)
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'trash', $alt, $task, $check);
	}

	/**
	 * Writes a save button for a given option.
	 * Apply operation leads to a save action only (does not leave edit mode).
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function apply($task = 'apply', $alt = 'JTOOLBAR_APPLY')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'apply', $alt, $task, false);
	}

	/**
	 * Writes a save button for a given option.
	 * Save operation leads to a save and then close action.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function save($task = 'save', $alt = 'JTOOLBAR_SAVE')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'save', $alt, $task, false);
	}

	/**
	 * Writes a save and create new button for a given option.
	 * Save and create operation leads to a save and then add action.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function save2new($task = 'save2new', $alt = 'JTOOLBAR_SAVE_AND_NEW')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'savenew', $alt, $task, false);
	}

	/**
	 * Writes a save as copy button for a given option.
	 * Save as copy operation leads to a save after clearing the key,
	 * then returns user to edit mode with new key.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function save2copy($task = 'save2copy', $alt = 'JTOOLBAR_SAVE_AS_COPY')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'savecopy', $alt, $task, false);
	}

	/**
	 * Writes a cancel button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 */
	public static function cancel($task = 'cancel', $alt = 'JTOOLBAR_CANCEL')
	{
		$bar = JToolbar::getInstance();

		$bar->appendButton('Standard', 'cancel', $alt, $task, false);
	}

	/**
	 * Writes a configuration button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string   $component  The name of the component, eg, com_content.
	 *
	 * @return  void
	 */
	public static function preferences($component)
	{
		$component = urlencode($component);

		$uri 	= JUri::current();
		$return = urlencode(base64_encode($uri));

		$bar = JToolbar::getInstance();

		// Add a button linking to ACL config for component
		$bar->appendButton(
			'Link',
			'options',
			'JTOOLBAR_OPTIONS',
			'admin.php?option=' . $component . '&amp;view=acl&amp;return=' . $return
		);
	}

	/**
	 * Writes a button to create shortcodes.
	 *
	 * @param   string   $component  The name of the component, eg, com_content.
	 *
	 * @return  void
	 */
	public static function shortcodes($component)
	{
		$component = urlencode($component);

		$uri 	= JUri::current();
		$return = urlencode(base64_encode($uri));

		$bar = JToolbar::getInstance();

		// Add a button linking to ACL config for component
		$bar->appendButton(
			'Link',
			'codes',
			'JTOOLBAR_SHORTCODES',
			'admin.php?option=' . $component . '&amp;view=shortcodes&amp;return=' . $return
		);
	}
}
