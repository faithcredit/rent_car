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
 * Helper class to setup the plugin.
 *
 * @since 1.0
 */
class VikRentCarBuilder
{
	/**
	 * Loads the .mo language related to the current locale.
	 *
	 * @return 	void
	 */
	public static function loadLanguage()
	{
		$app = JFactory::getApplication();

		/**
		 * @since 	1.0.2 	All the language files have been merged 
		 * 					within a single file to be compliant with
		 * 					the Worpdress Translation Standards.
		 *					The language file is located in /languages folder.
		 */
		$path 	 = VIKRENTCAR_LANG;

		$handler = VIKRENTCAR_LIBRARIES . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
		$domain  = 'vikrentcar';

		// init language
		$lang = JFactory::getLanguage();
		
		$lang->attachHandler($handler . 'system.php', $domain);
		
		if ($app->isAdmin())
		{
			$lang->attachHandler($handler . 'adminsys.php', $domain);
			$lang->attachHandler($handler . 'admin.php', $domain);
		}
		else
		{
			$lang->attachHandler($handler . 'site.php', $domain);
		}

		$lang->load($domain, $path);
	}

	/**
	 * Setup the pagination layout to use.
	 *
	 * @return 	void
	 */
	public static function setupPaginationLayout()
	{
		$layout = new JLayoutFile('html.system.pagination', null, array('component' => 'com_vikrentcar'));

		JLoader::import('adapter.pagination.pagination');
		JPagination::setLayout($layout);
	}

	/**
	 * Pushes the plugin pages into the WP admin menu.
	 *
	 * @return 	void
	 *
	 * @link 	https://developer.wordpress.org/resource/dashicons/#star-filled
	 */
	public static function setupAdminMenu()
	{
		JLoader::import('adapter.acl.access');
		$capability = JAccess::adjustCapability('core.manage', 'com_vikrentcar');

		add_menu_page(
			JText::_('COM_VIKRENTCAR'), 		// page title
			JText::_('COM_VIKRENTCAR_MENU'), 	// menu title
			$capability,						// capability
			'vikrentcar', 						// slug
			array('VikRentCarBody', 'getHtml'),	// callback
			'dashicons-car',					// icon
			71									// ordering
		);
	}

	/**
	 * Setup HTML helper classes.
	 * This method should be used to register custom function
	 * for example to render own layouts.
	 *
	 * @return 	void
	 */
	public static function setupHtmlHelpers()
	{
		// helper method to render calendars layout
		JHtml::register('renderCalendar', function($data)
		{
			JHtml::_('script', VRC_SITE_URI . 'resources/jquery-ui.min.js');
			JHtml::_('stylesheet', VRC_SITE_URI . 'resources/jquery-ui.min.css');

			$layout = new JLayoutFile('html.plugins.calendar', null, array('component' => 'com_vikrentcar'));
			
			return $layout->render($data);
		});

		// helper method to get the plugin layout file handler
		JHtml::register('layoutfile', function($layoutId, $basePath = null, $options = array())
		{
			return new JLayoutFile($layoutId, $basePath, $options);
		});

		// helper method to include the system JS file
		JHtml::register('system.js', function()
		{
			static $loaded = 0;

			if (!$loaded)
			{
				// include only once
				$loaded = 1;

				JHtml::_('script', VRC_ADMIN_URI . 'resources/js/system.js');
				JHtml::_('stylesheet', VRC_ADMIN_URI . 'resources/css/system.css');

				if (JFactory::getApplication()->isAdmin() || (class_exists('VikRentCar') && VikRentCar::loadBootstrap()))
				{
					/**
					 * The CSS/JS files of Bootstrap may disturb the styles of the Theme,
					 * and so we load them only within the back-end section.
					 * 
					 * @since 	1.1.4
					 */
					JHtml::_('script', VRC_ADMIN_URI . 'resources/js/vikbootstrap.min.js');
					JHtml::_('stylesheet', VRC_ADMIN_URI . 'resources/css/bootstrap.lite.css');
				}
			}
		});

		// helper method to include the select2 JS file
		JHtml::register('select2', function()
		{
			/**
			 * Select2 is now loaded only when requested.
			 *
			 * @since 1.2.5
			 */
			JHtml::_('script', VRC_ADMIN_URI . 'resources/select2.min.js');
			JHtml::_('stylesheet', VRC_ADMIN_URI . 'resources/select2.min.css');
		});

		/**
		 * Register helper methods to sanitize attributes, html, JS and other elements.
		 */
		JHtml::register('esc_attr', function($str)
		{
			return esc_attr($str);
		});

		JHtml::register('esc_html', function($str)
		{
			return esc_html($str);
		});

		JHtml::register('esc_js', function($str)
		{
			return esc_js($str);
		});

		JHtml::register('esc_textarea', function($str)
		{
			return esc_textarea($str);
		});

		/**
		 * Attempt to turn on the SQL_BIG_SELECTS setting at runtime, to avoid
		 * SQL errors like "The SELECT would examine more than MAX_JOIN_SIZE rows;".
		 * Since we do this operation at runtime, we attempt to suppress the DB errors
		 * in case the user does not have enough permissions to run queries of type "SET".
		 * Once executed, we restore the original value for DB errors.
		 * 
		 * @since 	1.15.1 (J) - 1.3.1 (WP)
		 */
		add_action('plugins_loaded', function()
		{
			$dbo = JFactory::getDbo();

			// suppress temporarily any database error
			$dbo->suppress_errors(true);

			// turn on the required SQL setting
			$dbo->setQuery('SET SQL_BIG_SELECTS=1');
			$dbo->execute();

			// restore the default SQL display errors setting
			$dbo->suppress_errors(false);
		});
	}

	/**
	 * This method is used to configure teh payments framework.
	 * Here should be registered all the default gateways supported
	 * by the plugin.
	 *
	 * @return 	void
	 *
	 * @since 	1.0.5
	 */
	public static function configurePaymentFramework()
	{
		// push the pre-installed gateways within the payment drivers list
		add_filter('get_supported_payments_vikrentcar', function($drivers)
		{
			$list = glob(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'payments' . DIRECTORY_SEPARATOR . '*.php');

			return array_merge($drivers, $list);
		});

		// load payment handlers when dispatched
		add_action('load_payment_gateway_vikrentcar', function(&$drivers, $payment)
		{
			$classname = null;
			
			VikRentCarLoader::import('admin.payments.' . $payment, VIKRENTCAR_BASE);

			switch ($payment)
			{
				case 'paypal':
					$classname = 'VikRentCarPayPalPayment';
					break;

				case 'offline_credit_card':
					$classname = 'VikRentCarOfflineCreditCardPayment';
					break;

				case 'bank_transfer':
					$classname = 'VikRentCarBankTransferPayment';
					break;
			}

			if ($classname)
			{
				$drivers[] = $classname;
			}
		}, 10, 2);

		// manipulate response to be compliant with notifypayment task
		add_action('payment_after_validate_transaction_vikrentcar', function(&$payment, &$status, &$response)
		{
			// manipulate the response to be compliant with the old payment system
			$response = array(
				'verified' => (int) $status->isVerified(),
				'tot_paid' => $status->amount,
				'log'	   => $status->log,
			);

			if ($status->skip_email)
			{
				$response['skip_email'] = $status->skip_email;
			}
		}, 10, 3);
	}

	/**
	 * Registers all the widget contained within the modules folder.
	 *
	 * @return 	void
	 */
	public static function setupWidgets()
	{
		JLoader::import('adapter.module.factory');

		// load all the modules
		JModuleFactory::load(VIKRENTCAR_BASE . DIRECTORY_SEPARATOR . 'modules');
	}
}
