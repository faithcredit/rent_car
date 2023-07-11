<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	system
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class used to provide support for the <head> of the page.
 *
 * @since 1.0
 */
class VikRentCarAssets
{
	/**
	 * A list containing all the methods already used.
	 *
	 * @var array
	 */
	protected static $loaded = array();

	/**
	 * Loads all the assets required for the plugin.
	 *
	 * @return 	void
	 */
	public static function load()
	{
		// loads only once
		if (static::isLoaded(__METHOD__))
		{
			return;
		}

		$document = JFactory::getDocument();

		$internalFilesOptions = array('version' => VIKRENTCAR_SOFTWARE_VERSION);

		// include localised strings for script files
		JText::script('CONNECTION_LOST');

		// system.js must be loaded on both front-end and back-end for tmpl=component support
		$document->addScript(VIKRENTCAR_ADMIN_ASSETS_URI . 'js/system.js', $internalFilesOptions, array('id' => 'vrc-sys-script'));

		if (JFactory::getApplication()->isAdmin())
		{
			/* Load assets for CSS and JS */
			VikRentCar::loadFontAwesome(true);
			
			$document->addStyleSheet(VIKRENTCAR_ADMIN_ASSETS_URI . 'vikrentcar.css', $internalFilesOptions, array('id' => 'vrc-style'));
			$document->addStyleSheet(VIKRENTCAR_ADMIN_ASSETS_URI . 'fonts/vrcicomoon.css', $internalFilesOptions, array('id' => 'vrc-icomoon-style'));

			VikRentCar::getVrcApplication()->normalizeBackendStyles();

			$document->addStyleSheet(VIKRENTCAR_ADMIN_ASSETS_URI . 'css/system.css', $internalFilesOptions, array('id' => 'vrc-sys-style'));
			$document->addStyleSheet(VIKRENTCAR_ADMIN_ASSETS_URI . 'css/bootstrap.lite.css', $internalFilesOptions, array('id' => 'bootstrap-lite-style'));
			$document->addScript(VIKRENTCAR_ADMIN_ASSETS_URI . 'js/vikbootstrap.min.js', $internalFilesOptions, array('id' => 'bootstrap-script'));

			/**
			 * Include the VRCCore JS class.
			 * 
			 * @since 	1.3.0
			 */
			$document->addScript(VIKRENTCAR_ADMIN_ASSETS_URI . 'vrccore.js', $internalFilesOptions, array('id' => 'vrc-core-script'));

			/**
			 * Include the Toast JS class.
			 * 
			 * @since 	1.3.0
			 */
			$document->addScript(VIKRENTCAR_ADMIN_ASSETS_URI . 'toast.js', $internalFilesOptions, array('id' => 'vrc-toast-script'));
			$document->addStyleSheet(VIKRENTCAR_ADMIN_ASSETS_URI . 'toast.css', $internalFilesOptions, array('id' => 'vrc-toast-style'));

			$document->addScriptDeclaration(
<<<JS
(function($) {
	'use strict';

	$(function() {
		VRCToast.create(VRCToast.POSITION_TOP_RIGHT);
	});
})(jQuery);
JS
			);

			/**
			 * Load necessary assets for WordPress >= 5.3
			 */
			JLoader::import('adapter.application.version');
			$wpv = new JVersion;
			if (version_compare($wpv->getShortVersion(), '5.3', '>=')) {
				$document->addStyleSheet(VIKRENTCAR_ADMIN_ASSETS_URI . 'css/bc/wp5.3.css', $internalFilesOptions, array('id' => 'vrc-wp-bc-style'));
			}
			//
		}
		else
		{
			VikRentCar::loadFontAwesome();
			$document->addStyleSheet(VIKRENTCAR_SITE_ASSETS_URI.'vikrentcar_styles.css', $internalFilesOptions, array('id' => 'vrc-style'));
			$document->addStyleSheet(VIKRENTCAR_SITE_ASSETS_URI.'vikrentcar_custom.css', $internalFilesOptions, array('id' => 'vrc-custom-style'));
		}
	}

	/**
	 * Checks if the method has been already loaded.
	 * This function assumes that after this check we are going
	 * to use the specified method.
	 *
	 * A method is considered loaded only if the arguments used are the same.
	 *
	 * @param 	string 	 $method 	The method to check for.
	 * @param 	array 	 $args 		The list of arguments.
	 * 
	 * @return 	boolean  True if already used, otherwise false.
	 */
	protected static function isLoaded($method, array $args = array())
	{
		// generate a unique signature containing the method name
		// and the list of arguments to use
		$sign = serialize(array($method, $args));

		// check if the method has been already loaded
		if (isset(static::$loaded[$sign]))
		{
			// already loaded
			return true;
		}

		// mark the method as loaded
		static::$loaded[$sign] = 1;

		// not loaded
		return false;
	}
}
