<?php
/*
Plugin Name:  VikRentCar
Plugin URI:   https://vikwp.com/plugin/vikrentcar
Description:  Robust Car Rental Management Software.
Version:      1.3.1
Author:       E4J s.r.l.
Author URI:   https://vikwp.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  vikrentcar
Domain Path:  /languages
*/

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// autoload dependencies
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'autoload.php';

// handle install/uninstall
register_activation_hook(__FILE__, array('VikRentCarInstaller', 'activate'));
register_deactivation_hook(__FILE__, array('VikRentCarInstaller', 'deactivate'));
register_uninstall_hook(__FILE__, array('VikRentCarInstaller', 'delete'));

// init Installer
add_action('init', array('VikRentCarInstaller', 'onInit'));

/**
 * Fires after all automatic updates have run.
 * Completes the update scheduled in background.
 *
 * @param  array  $results  The results of all attempted updates.
 *
 * @since  1.2.0
 */
add_action('automatic_updates_complete', array('VikRentCarInstaller', 'automaticUpdate'));

/**
 * Filters whether to automatically update core, a plugin, a theme, or a language.
 * Used to automatically turn off the update in case a PRO version expired.
 *
 * @param  bool|null  $update  Whether to update. The value of null is internally used
 *                             to detect whether nothing has hooked into this filter.
 * @param  object     $item    The update offer.
 *
 * @since  1.2.0
 */
add_filter('auto_update_plugin', array('VikRentCarInstaller', 'useAutoUpdate'), 10, 2);

/**
 * Fires at the end of the update message container in each
 * row of the plugins list table.
 *
 * The dynamic portion of the hook name, `$file`, refers to the path
 * of the plugin's primary file relative to the plugins directory.
 *
 * @link   https://developer.wordpress.org/reference/hooks/in_plugin_update_message-file/
 *
 * @param  array  $data      An array of plugin metadata.
 * @param  array  $response  An array of metadata about the available plugin update.
 *
 * @since  1.2.0
 */
add_action('in_plugin_update_message-vikrentcar/vikrentcar.php', array('VikRentCarInstaller', 'getUpdateMessage'), 10, 2);

// init pagination layout
VikRentCarBuilder::setupPaginationLayout();
// init html helpers
VikRentCarBuilder::setupHtmlHelpers();
// init payment framework
VikRentCarBuilder::configurePaymentFramework();

/**
 * Added support for screen options.
 * Parameters such as the list limit can be changed from there.
 */
add_action('current_screen', array('VikRentCarScreen', 'options'));
add_filter('set-screen-option', array('VikRentCarScreen', 'saveOption'), 10, 3);
/**
 * Due to WordPress 5.4.2 changes, we need to attach
 * VikRentCar to a dedicated hook in order to 
 * allow the update of the list limit.
 *
 * @since 	1.0.7
 */
add_filter('set_screen_option_vikrentcar_list_limit', array('VikRentCarScreen', 'saveOption'), 10, 3);

// init Session
add_action('init', array('JSessionHandler', 'start'), 1);
add_action('wp_logout', array('JSessionHandler', 'destroy'));

// filter page link to rewrite URI
add_action('plugins_loaded', function()
{
	// installer class will check the update status
	VikRentCarInstaller::update();

	/**
	 * Init language when plugins have been loaded to not interfere with third party plugins.
	 * 
	 * @since 	1.1.9
	 */
	VikRentCarBuilder::loadLanguage();

	global $pagenow;

	$app   = JFactory::getApplication(); 
	$input = $app->input;

	// check if the URI contains option=com_vikrentcar
	if ($input->get('option') == 'com_vikrentcar')
	{
		// make sure we are not contacting the AJAX and POST end-points
		if (!wp_doing_ajax() && $pagenow != 'admin-post.php')
		{
			/**
			 * Include page in query string only if we are in the back-end,
			 * because WordPress 5.5 seems to break the page loading in case
			 * that argument has been included in query string.
			 *
			 * It is not needed to include this argument in the front-end
			 * as the page should lean on the reached shortcode only.
			 *
			 * @since 1.0.8
			 */
			if ($app->isAdmin())
			{
				// inject page=vikrentcar in GET superglobal
				$input->get->set('page', 'vikrentcar');
			}
		}
		else
		{
			// inject action=vikrentcar in GET superglobal for AJAX and POST requests
			$input->get->set('action', 'vikrentcar');
		}
	}
	elseif ($input->get('page') == 'vikrentcar' || $input->get('action') == 'vikrentcar')
	{
		// inject option=com_vikrentcar in GET superglobal
		$input->get->set('option', 'com_vikrentcar');
	}
});

// process the request and obtain the response
add_action('init', function()
{
	$app 	= JFactory::getApplication();
	$input 	= $app->input;

	// if we are in the front-end, try to parse the URL to inject
	// option, view and args in the input request
	if ($app->isSite() && VIKRENTCAR_SITE_PREPROCESS)
	{
		// get post ID from current URL
		$id = url_to_postid(JUri::current());

		if ($id)
		{
			// get shortcode admin model
			$model = JModel::getInstance('vikrentcar', 'shortcode', 'admin');
			// get shortcode searching by post ID (false to avoid returning a new item)
			$shortcode = $model->getItem(array('post_id' => $id), false);

			if ($shortcode)
			{
				// build args array using the shortcode attributes
				$args = (array) json_decode($shortcode->json, true);
				$args['view'] 	= $shortcode->type;
				$args['option'] = 'com_vikrentcar';

				// inject the shortcode args into the input request
				foreach ($args as $k => $v)
				{
					// inject only if not defined
					$input->def($k, $v);
				}
			}
		}
	}

	// process VikRentCar only if it has been requested via GET or POST
	if ($input->get('option') == 'com_vikrentcar' || $input->get('page') == 'vikrentcar')
	{
		VikRentCarBody::process();
	}
});

// handle AJAX requests
add_action('wp_ajax_vikrentcar', function()
{
	VikRentCarBody::getHtml();

	// die to get a valid response
	wp_die();
});

// setup admin menu
add_action('admin_menu', array('VikRentCarBuilder', 'setupAdminMenu'));

// register widgets
add_action('widgets_init', array('VikRentCarBuilder', 'setupWidgets'));

// handle shortcodes (SITE controller dispatcher)
add_shortcode('vikrentcar', function($atts, $content = null)
{
	// wrap attributes in a registry
	$args = new JObject($atts);

	// get the VIEW (empty if not set)
	$view = $args->get('view', '');

	// load the FORM of the view
	JLoader::import('adapter.form.form');
	$path = implode(DIRECTORY_SEPARATOR, array(VRC_SITE_PATH, 'views', $view, 'tmpl', 'default.xml'));
	// raises an exception if the VIEW is not set
	$form = JForm::getInstance($view, $path);
	
	// get all the XML form fields
	$fields = $form->getFields();

	// filter the fields to get a list of allowed names
	$fields = array_map(function($f)
	{
		return (string) $f->attributes()->name;
	}, $fields);

	// inject query vars
	$input = JFactory::getApplication()->input;
	// since we are going to render the controller manually,
	// we don't need to push the option into $_REQUEST pool.
	// $input->set('option', 'com_vikrentcar');
	
	// Inject shortcode vars only if they are not set 
	// in the request. This is used to allow the navigation
	// between the pages.
	$input->def('view', $view);
	
	foreach ($fields as $k)
	{
		$input->def($k, $args->get($k));
	}

	// dispatch the controller
	return VikRentCarBody::getHtml(true);
});

// the callback is fired before the VBO controller is dispatched
add_action('vikrentcar_before_dispatch', function()
{
	$app 	= JFactory::getApplication();
	$user 	= Jfactory::getUser();

	// initialize timezone handler
	JDate::getDefaultTimezone();
	date_default_timezone_set($app->get('offset', 'UTC'));

	// check if the user is authorised to access the back-end (only if the client is 'admin')
	if ($app->isAdmin() && !$user->authorise('core.manage', 'com_vikrentcar'))
	{
		if ($user->guest)
		{
			// if the user is not logged, redirect to login page
			$app->redirect('index.php');
			exit;
		}
		else
		{
			// otherwise raise an exception
			wp_die(
				'<h1>' . JText::_('FATAL_ERROR') . '</h1>' .
				'<p>' . JText::_('RESOURCE_AUTH_ERROR') . '</p>',
				403
			);
		}
	}

	// main library
	require_once VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikrentcar.php';

	if ($app->isAdmin())
	{
		require_once VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vikrentcar.php';
		require_once VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'jv_helper.php';
	}
	else
	{
		// Invoke needed methods before the rendering
		VikRentCar::detectUserAgent();
		VikRentCar::getTracker();
		VikRentCar::loadPreferredColorStyles();
	}
});

// instead using the default server timezone, try to use the one
// specified within the WordPress configuration
add_filter('vik_date_default_timezone', function($timezone)
{
	return JFactory::getApplication()->get('offset', $timezone);
});

// the callback is fired once the VBO controller has been dispatched
add_action('vikrentcar_after_dispatch', function()
{	
	// load assets after dispatching the controller to avoid
	// including JS and CSS when an AJAX function exits or dies
	VikRentCarAssets::load();

	/**
	 * Load javascript core.
	 */
	JHtml::_('behavior.core');

	// restore standard timezone
	date_default_timezone_set(JDate::getDefaultTimezone());

	/**
	 * @note 	when the headers have been sent or when 
	 * 			the request is AJAX, the assets (CSS and JS) are
	 * 			appended to the document after the 
	 * 			response dispatched by the controller.
	 */
});

// End-point for front-end post actions.
// The end-point URL must be built as .../wp-admin/admin-post.php
// and requires $_POST['action'] == 'vikrentcar' to be submitted through a form or GET.
add_action('admin_post_vikrentcar', 'handle_vikrentcar_endpoint'); 			// if the user is logged in
add_action('admin_post_nopriv_vikrentcar', 'handle_vikrentcar_endpoint'); 	// if the user in not logged in

// handle POST end-point
function handle_vikrentcar_endpoint()
{
	// get PLAIN response
	echo VikRentCarBody::getResponse();
}

// Hook used to access the PAGE details when a user is 
// creating or updating it. This is helpful to make a relation
// between the page and the injected shortcode.
add_action('save_post', function($post_id)
{
	// get model to access all the existing shortcodes
	$model = JModel::getInstance('vikrentcar', 'shortcodes', 'admin');
	$shortcodes = $model->all(array('id', 'shortcode', 'post_id'));

	// get post data
	$post = get_post($post_id);

	/**
	 * Check if we are editing a child post as Gutenberg 
	 * seems to use always the inherit status, which 
	 * refers to a post parent.
	 */
	if ($post->post_status != 'publish' && !empty($post->post_parent) && $post->post_parent != $post_id)
	{
		// fallback to obtain parent post data
		$post = get_post($post->post_parent);

		// use new post ID
		$post_id = $post->id;
	}

	if ($post->post_status != 'publish')
	{
		// ignore drafts auto-save
		return;
	}

	// get shortcode model
	$shortcodeModel = JModel::getInstance('vikrentcar', 'shortcode', 'admin');

	/**
	 * Since we need unique post IDs, all the shortcodes
	 * that are assigned to the specified $post_id should
	 * be detached.
	 */
	foreach ($shortcodes as $data)
	{
		if ($data->post_id == $post_id)
		{
			// The post is already assigned to a shortcode.
			// Unset it to avoid duplicated.
			$data->post_id = 0;
			$shortcodeModel->save($data);
		}
	}
	
	// iterate the shortcodes
	foreach ($shortcodes as $data)
	{
		// check if the content of the post contains the shortcode
		if (strpos($post->post_content, html_entity_decode($data->shortcode)) !== false)
		{
			// inject the POST ID
			$data->post_id = $post_id;

			// update shortcode
			$shortcodeModel->save($data);

			// stop iterating
			return;
		}
	}
});

// Hook used to unset temporarily the relationship
// between the trashed post and the shortcode.
add_action('trashed_post', function($post_id)
{
	// get shortcode model
	$model = JModel::getInstance('vikrentcar', 'shortcode', 'admin');

	// get the shortcode attached to the trashed post ID
	$item = $model->getItem(array('post_id' => $post_id), false);

	// if the item exists, temporarily detach the relationship
	if ($item)
	{
		$item->post_id 		= 0;
		$item->tmp_post_id 	= $post_id;

		$model->save($item);
	}
});

// Hook used to restore permanently the relationship
// between the untrashed post and the shortcode.
add_action('untrashed_post', function($post_id)
{
	// get shortcode model
	$model = JModel::getInstance('vikrentcar', 'shortcode', 'admin');

	// get the shortcode attached to the untrashed post ID
	$item = $model->getItem(array('tmp_post_id' => $post_id), false);

	// if the item exists, re-attach the relationship
	if ($item)
	{
		$item->post_id 		= $post_id;
		$item->tmp_post_id 	= 0;

		$model->save($item);
	}
});

// Hook used to temporarily detach the relationship
// between the deleted post and the shortcode.
add_action('deleted_post', function($post_id)
{
	// get shortcode model
	$model = JModel::getInstance('vikrentcar', 'shortcode', 'admin');

	// get the shortcode attached to the trashed post ID
	$item = $model->getItem(array('tmp_post_id' => $post_id), false);

	// If no item found, the "trash" feature is probably disabled.
	// Try to take a look for a shortcode with an active relationship.
	if (!$item)
	{
		$item = $model->getItem(array('post_id' => $post_id), false);
	}

	// if the item exists, permanently detach the relationship
	if ($item)
	{
		$item->post_id 		= 0;
		$item->tmp_post_id 	= 0;

		$model->save($item);
	}
});

if (JFactory::getApplication()->isAdmin() && !wp_doing_ajax())
{
	/**
	 * @todo should we restrict these filters to the post managements pages only?
	 */

	VikRentCarLoader::import('system.mce');
	VikRentCarLoader::import('system.gutenberg');

	// add new buttons
	add_filter('mce_buttons', array('VikRentCarTinyMCE', 'addShortcodesButton'));

	// load the button handlers
	add_filter('mce_external_plugins', array('VikRentCarTinyMCE', 'registerShortcodesScript'));

	// add support for Gutenberg shortcode block
	add_action('init', array('VikRentCarGutenberg', 'registerShortcodesScript'));
}

/**
 * Dispatch the uninstallation of VikRentCar
 * every time a new blog (multisite) is deleted.
 *
 * Fires after the site is deleted from the network (WP 4.8.0 or higher).
 *
 * @param 	integer  $blog_id 	The site ID.
 * @param 	boolean  $drop 		True if site's tables should be dropped. Default is false.
 */
add_action('deleted_blog', function($blog_id, $drop)
{
	VikRentCarInstaller::uninstall($drop);
}, 10, 2);

/**
 * Once the plugins have been loaded, evaluates to execute the
 * scheduled cron jobs.
 *
 * Scheduling is processed in case a cron job is hitting wp-cron file
 * or in case a user is visiting the website.
 * 
 * @since 	1.3.0  Schedules a different hook for each cron.
 */
add_action('plugins_loaded', array('VikRentCarCron', 'setup'));

/**
 * Filters the action links displayed for each plugin in the Plugins list table.
 * Hook used to filter the "deactivation" link and ask a feedback every time that
 * button is clicked.
 *
 * @param 	array   $actions      An array of plugin action links. By default this can include 'activate',
 *                                'deactivate', and 'delete'. With Multisite active this can also include
 *                                'network_active' and 'network_only' items.
 * @param 	string  $plugin_file  Path to the plugin file relative to the plugins directory.
 * @param 	array   $plugin_data  An array of plugin data. See `get_plugin_data()`.
 * @param 	string  $context      The plugin context. By default this can include 'all', 'active', 'inactive',
 *                                'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
 */
add_filter('plugin_action_links', array('VikRentCarFeedback', 'deactivate'), 10, 4);

/**
 * Adjusts the timezone of the website before dispatching
 * a widget as we are currently outside of the main plugin and
 * the timezone have probably been restored to the default one.
 *
 * @param 	string 	 $id       The widget ID (path name).
 * @param 	JObject  &$params  The widget configuration registry.
 */
add_action('vik_widget_before_dispatch_site', function($id, &$params)
{
	// initialize timezone handler
	JDate::getDefaultTimezone();
	date_default_timezone_set(JFactory::getApplication()->get('offset', 'UTC'));
}, 10, 2);

/**
 * Restores the timezone of the website after dispatching
 * a widget in order to avoid strange behaviors with other plugins.
 *
 * @param 	string 	$id     The widget ID (path name).
 * @param 	string  &$html  The HTML of the widget to display.
 */
add_action('vik_widget_after_dispatch_site', function($id, &$html)
{
	// restore standard timezone
	date_default_timezone_set(JDate::getDefaultTimezone());	
}, 10, 2);

/**
 * Added support for Loco Translate.
 * In case some translations have been edited by using this plugin,
 * we should look within the Loco Translate folder to check whether
 * the requested translation is available.
 *
 * @param 	boolean  $loaded  True if the translation has been already loaded.
 * @param 	string 	 $domain  The plugin text domain to load.
 *
 * @return 	boolean  True if a new translation is loaded.
 *
 * @since 	1.3.2
 */
add_filter('vik_plugin_load_language', function($loaded, $domain)
{
	// proceed only in case the translation hasn't been loaded
	// and Loco Translate plugin is installed
	if (!$loaded && is_dir(WP_LANG_DIR . DIRECTORY_SEPARATOR . 'loco'))
	{
		// Build LOCO path.
		// Since load_plugin_textdomain accepts only relative paths, 
		// we should go back to the /wp-contents/ folder first.
		$loco = implode(DIRECTORY_SEPARATOR, array('..', 'languages', 'loco', 'plugins'));

		// try to load the plugin translation from Loco folder
		$loaded = load_plugin_textdomain($domain, false, $loco);
	}

	return $loaded;
}, 10, 2);

/**
 * Fixed issue with wptexturize() function, which might convert special characters contained
 * within <script> tags into their corresponding HTML entities (e.g. "&" became "&#038;").
 * 
 * @since 	1.3.2
 */
add_filter('the_content', function($content)
{
	// look for any script tags
	if (preg_match_all("/<script(?:.*?)>(?:.*?)<\/script>/s", $content, $matches))
	{
		// scan all the scripts
		foreach ($matches[0] as $script)
		{
			// make sure the script contains "&#038;"
			if (strpos($script, '&#038;') === false)
			{
				continue;
			}

			// fix the script by reverting the plain "&"
			$fixedScript = str_replace('&#038;', '&', $script);

			// replace the bugged script from the content with the fixed one
			$content = str_replace($script, $fixedScript, $content);
		}
	}

	return $content;
}, PHP_INT_MAX);
