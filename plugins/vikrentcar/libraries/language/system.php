<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	language
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.language.handler');

/**
 * Switcher class to translate the VikRentCar plugin system languages.
 *
 * @since 	1.0
 */
class VikRentCarLanguageSystem implements JLanguageHandler
{
	/**
	 * Checks if exists a translation for the given string.
	 *
	 * @param 	string 	$string  The string to translate.
	 *
	 * @return 	string 	The translated string, otherwise null.
	 */
	public function translate($string)
	{
		$result = null;

		/**
		 * Translations go here.
		 * @tip Use 'TRANSLATORS:' comment to attach a description of the language.
		 */

		switch ($string)
		{
			/**
			 * MVC ERRORS
			 */

			case 'FATAL_ERROR':
				$result = __('Error', 'vikrentcar');
				break;

			case 'CONTROLLER_FILE_NOT_FOUND_ERR':
				$result = __('The controller does not exist.', 'vikrentcar');
				break;

			case 'CONTROLLER_CLASS_NOT_FOUND_ERR':
				$result = __('The controller [%s] classname does not exist.', 'vikrentcar');
				break;

			case 'CONTROLLER_INVALID_INSTANCE_ERR':
				$result = __('The controller must be an instance of JController.', 'vikrentcar');
				break;

			case 'CONTROLLER_PROTECTED_METHOD_ERR':
				$result = __('You cannot call JController reserved methods.', 'vikrentcar');
				break;

			case 'TEMPLATE_VIEW_NOT_FOUND_ERR':
				$result = __('Template view not found.', 'vikrentcar');
				break;

			case 'RESOURCE_AUTH_ERROR':
				$result = __('You are not authorised to access this resource.', 'vikrentcar');
				break;

			/**
			 * Invalid token for CSRF protection.
			 * 
			 * @see  	this key will actually terminate the whole process.
			 * @since 	1.1.7
			 */
			case 'JINVALID_TOKEN':
				wp_nonce_ays(JSession::getFormTokenAction());
				break;

			/**
			 * NATIVE ACL RULES
			 */

			case 'VRCACLMENUTITLE':
				$result = __('Vik Rent Car - Access Control List', 'vikrentcar');
				break;

			case 'JACTION_ADMIN':
				$result = __('Configure ACL & Options', 'vikrentcar');
				break;

			case 'JACTION_ADMIN_COMPONENT_DESC':
				$result = __('Allows users in the group to edit the options and permissions of this plugin.', 'vikrentcar');
				break;

			case 'JACTION_MANAGE':
				$result = __('Access Administration Interface', 'vikrentcar');
				break;

			case 'JACTION_MANAGE_COMPONENT_DESC':
				$result = __('Allows users in the group to access the administration interface for this plugin.', 'vikrentcar');
				break;

			case 'JACTION_CREATE':
				$result = __('Create', 'vikrentcar');
				break;

			case 'JACTION_CREATE_COMPONENT_DESC':
				$result = __('Allows users in the group to create any content in this plugin.', 'vikrentcar');
				break;

			case 'JACTION_DELETE':
				$result = __('Delete', 'vikrentcar');
				break;

			case 'JACTION_DELETE_COMPONENT_DESC':
				$result = __('Allows users in the group to delete any content in this plugin.', 'vikrentcar');
				break;

			case 'JACTION_EDIT':
				$result = __('Edit', 'vikrentcar');
				break;

			case 'JACTION_EDIT_COMPONENT_DESC':
				$result = __('Allows users in the group to edit any content in this plugin.', 'vikrentcar');
				break;

			case 'CONNECTION_LOST':
				// translation provided by wordpress
				$result = __('Connection lost or the server is busy. Please try again later.');
				break;

			/**
			 * ACL Form
			 */

			case 'ACL_SAVE_SUCCESS':
				$result = __('ACL saved.', 'vikrentcar');
				break;

			case 'ACL_SAVE_ERROR':
				$result = __('An error occurred while saving the ACL.', 'vikrentcar');
				break;

			case 'JALLOWED':
				$result = __('Allowed', 'vikrentcar');
				break;

			case 'JDENIED':
				$result = __('Denied', 'vikrentcar');
				break;

			case 'JACTION':
				$result = __('Action', 'vikrentcar');
				break;

			case 'JNEW_SETTING':
				$result = __('New Setting', 'vikrentcar');
				break;

			case 'JCURRENT_SETTING':
				$result = __('Current Setting', 'vikrentcar');
				break;

			/**
			 * TOOLBAR BUTTONS
			 */

			case 'JTOOLBAR_NEW':
				$result = __('New', 'vikrentcar');
				break;

			case 'JTOOLBAR_EDIT':
				$result = __('Edit', 'vikrentcar');
				break;

			case 'JTOOLBAR_BACK':
				$result = __('Back', 'vikrentcar');
				break;

			case 'JTOOLBAR_PUBLISH':
				$result = __('Publish', 'vikrentcar');
				break;

			case 'JTOOLBAR_UNPUBLISH':
				$result = __('Unpublish', 'vikrentcar');
				break;

			case 'JTOOLBAR_ARCHIVE':
				$result = __('Archive', 'vikrentcar');
				break;

			case 'JTOOLBAR_UNARCHIVE':
				$result = __('UnArchive', 'vikrentcar');
				break;

			case 'JTOOLBAR_DELETE':
				$result = __('Delete', 'vikrentcar');
				break;

			case 'JTOOLBAR_TRASH':
				$result = __('Trash', 'vikrentcar');
				break;

			case 'JTOOLBAR_APPLY':
				$result = __('Save', 'vikrentcar');
				break;

			case 'JTOOLBAR_SAVE':
				$result = __('Save & Close', 'vikrentcar');
				break;

			case 'JTOOLBAR_SAVE_AND_NEW':
				$result = __('Save & New', 'vikrentcar');
				break;

			case 'JTOOLBAR_SAVE_AS_COPY':
				$result = __('Save as Copy', 'vikrentcar');
				break;

			case 'JTOOLBAR_CANCEL':
				$result = __('Cancel', 'vikrentcar');
				break;

			case 'JTOOLBAR_OPTIONS':
				$result = __('Permissions', 'vikrentcar');
				break;

			case 'JTOOLBAR_SHORTCODES':
				$result = __('Shortcodes', 'vikrentcar');
				break;

			/**
			 * FILTERS
			 */

			case 'JOPTION_SELECT_LANGUAGE':
				$result = __('- Select Language -', 'vikrentcar');
				break;

			case 'JOPTION_SELECT_TYPE':
				$result = __('- Select Type -', 'vikrentcar');
				break;

			case 'JSEARCH_FILTER_SUBMIT':
				$result = __('Search', 'vikrentcar');
				break;

			/**
			 * PAGINATION
			 */

			case 'JPAGINATION_ITEMS':
				$result = __('%d items', 'vikrentcar');
				break;

			case 'JPAGINATION_PAGE_OF_TOT':
				// @TRANSLATORS: e.g. 1 of 12
				$result = _x('%d of %s', 'e.g. 1 of 12', 'vikrentcar');
				break;

			/**
			 * MENU ITEMS - FIELDSET TITLES
			 */

			case 'COM_MENUS_REQUEST_FIELDSET_LABEL':
				$result = __('Details', 'vikrentcar');
				break;

			/**
			 * GENERIC
			 */
			
			case 'JYES':
				$result = __('Yes');
				break;

			case 'JNO':
				$result = __('No');
				break;

			case 'JALL':
				$result = __('All', 'vikrentcar');
				break;

			case 'JID':
			case 'JGRID_HEADING_ID':
				$result = __('ID', 'vikrentcar');
				break;

			case 'JCREATEDBY':
				$result = __('Created By', 'vikrentcar');
				break;

			case 'JCREATEDON':
				$result = __('Created On', 'vikrentcar');
				break;

			case 'JNAME':
				$result = __('Name', 'vikrentcar');
				break;

			case 'JTYPE':
				$result = __('Type', 'vikrentcar');
				break;

			case 'JSHORTCODE':
				$result = __('Shortcode', 'vikrentcar');
				break;

			case 'JLANGUAGE':
				$result = __('Language', 'vikrentcar');
				break;

			case 'JPOST':
				$result = __('Post', 'vikrentcar');
				break;

			case 'PLEASE_MAKE_A_SELECTION':
				$result = __('Please first make a selection from the list.', 'vikrentcar');
				break;

			case 'JSEARCH_FILTER_CLEAR':
				$result = __('Clear', 'vikrentcar');
				break;

			case 'JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT':
				$result = __('Maximum upload size: <strong>%s</strong>', 'vikrentcar');
				break;

			case 'NO_ROWS_FOUND':
			case 'JGLOBAL_NO_MATCHING_RESULTS':
				$result = __('No rows found.', 'vikrentcar');
				break;

			case 'VRCSHORTCDSMENUTITLE':
				$result = __('Vik Rent Car - Shortcodes', 'vikrentcar');
				break;

			case 'VRCNEWSHORTCDMENUTITLE':
				$result = __('Vik Rent Car - New Shortcode', 'vikrentcar');
				break;

			case 'VRCEDITSHORTCDMENUTITLE':
				$result = __('Vik Rent Car - Edit Shortcode', 'vikrentcar');
				break;

			case 'VRC_SYS_LIST_LIMIT':
				$result = __('Number of items per page:');
				break;

			case 'JERROR_ALERTNOAUTHOR':
				$result = __('You are not authorised to access this resource', 'vikrentcar');
				break;

			case 'JLIB_APPLICATION_SAVE_SUCCESS':
				$result = __('Item saved.', 'vikrentcar');
				break;

			case 'JLIB_APPLICATION_ERROR_SAVE_FAILED':
				$result = __('Save failed with the following error: %s', 'vikrentcar');
				break;

			/**
			 * Media manager.
			 */

			case 'JMEDIA_PREVIEW_TITLE':
				$result = __('Image preview', 'vikrentcar');
				break;

			case 'JMEDIA_CHOOSE_IMAGE':
				$result = __('Choose an image', 'vikrentcar');
				break;

			case 'JMEDIA_CHOOSE_IMAGES':
				$result = __('Choose one or more images', 'vikrentcar');
				break;

			case 'JMEDIA_SELECT':
				$result = __('Select', 'vikrentcar');
				break;

			case 'JMEDIA_UPLOAD_BUTTON':
				$result = __('Pick or upload an image', 'vikrentcar');
				break;

			case 'JMEDIA_CLEAR_BUTTON':
				$result = __('Clear selection', 'vikrentcar');
				break;

			/**
			 * Pro version warning
			 */
			
			case 'VRCPROVEXPWARNUPD':
				$result = __('The Pro license for VikRentCar has expired. Do not install any updates or you will downgrade the plugin to the Free version.');
				break;
		}

		return $result;
	}
}
