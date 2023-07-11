=== SiteGround Optimizer ===
Contributors: Hristo Sg, siteground, sstoqnov, stoyangeorgiev, elenachavdarova, ignatggeorgiev
Tags: nginx, caching, speed, memcache, memcached, performance, siteground, nginx, supercacher
Requires at least: 4.7
Requires PHP: 7.0
Tested up to: 6.2
Stable tag: 7.3.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==

The [SiteGround Optimizer](https://www.siteground.com/wordpress-plugins/siteground-optimizer) plugin is developed by SiteGround to dramatically improve WordPress website performance on any hosting environment.

Initially designed for SiteGround’s servers and already used by almost 2 Million SiteGround clients, with the release of SiteGround Optimizer 7.0.0 the plugin will work on any hosting platform. All WordPress users, regardless  of their hosting provider, can take advantage of its unmatched WordPress speed-boosting features, no tech knowledge required.

Even though some of its features will still only work on SiteGround’s hosting platform, due to the specific server optimizations that other hosting providers might not support, the SiteGround Optimizer plugin is the most powerful all-in-one WordPress performance plugin, free and available for all.

The SiteGround Optimizer plugin has few different parts handling specific performance optimizations:

== Configuration ==

For detailed information on our plugin and how it works, please check out our [SiteGround Optimizer Tutorial](https://www.siteground.com/tutorials/wordpress/sg-optimizer/ "SiteGround Optimizer Tutorial").

= SiteGround Optimizer Dashboard Page =

The Dashboard offers a quick look at the current optimization status of your website and shortcuts to the relevant optimization pages. In addition to that, since keeping your WordPress application, plugins, and themes up to date is important for your website speed and security, we’ve made sure to add a notification in the Dashboard in case your WordPress and/or plugins need an update.

= SiteGround Optimizer Caching Page =

On this page, you can control your website cache.

Dynamic Caching:
	With our Dynamic Caching enabled all non-static resources of your website are cached to prevent unnecessary database queries and page loading, effectively decreasing the loading speed and TTFB (time to first byte) of your website. Dynamic Caching runs by default on all SiteGround servers and available only for them.

File-Based Caching:
	With file-based caching enabled we will create static HTML versions of your website which will be served to future visitors. The files are stored in the browser memory.
	Included in version 7.0.0, the File-Based Caching is available both for SiteGround and non SiteGround users.

	File-Based Caching configuration:
		- Clean up interval - this allows you to manage the interval File-Based cache will be automatically purged on.
		- Preheat cache - When Preheating is enabled, our system will reload the cache once it is purged after content update in order to serve the fastest possible results to your real visitors. Preheating is using the website sitemap and being executed via cron.
		- Logged-in users cache - By default, we do not cache content for logged in users. Once Logged In Cache is enabled, we will store separate caches for each user. Note, that if you have many users, the size of the stored cache may be increased.

Memcached:
	Powerful object caching for your site. Memcached stores frequently executed queries to your databases and then reuses them for better performance. It is available only on SiteGround Environment.

You can also enable Automatic Purge which will clear the cache when needed. You can use the WordPress API Cache Control checkbox if you need the WP API cache purged too.
Enabling the Browser-specific caching will create different cache versions based on the user agent used.
From Exclude Post Types you can exclude the ones you don’t want to be cached by Dynamic Caching. Feature is not available for File-Based Caching.
You can also exclude specific URLs or use a wildcard to exclude any sub-pages of a specific “parent-page”. The feature applies both for Dynamic and File-Based Caching.
We have also provided a test tool where you can check if a specific URL has Dynamic caching actually running.

We have a filter that allows you to control which user roles have access to flush the cache using the Purge SG Cache button.

Here's an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_purge_button_capabilities', 'sgo_add_new_role' );
	function sgo_add_new_role( $default_capabilities ) {
		// Allow new user role to flush cache.
		$default_capabilities[] = 'delete_others_posts'; // For Editors.
		$default_capabilities[] = 'edit_published_posts'; // For Authors.

		return $default_capabilities;
	}

Another filter is created in order to manage the number of URLs your website will be preheating. The default value is 200.

Here's an example of the code, you can add to your functions.php file:

	add_filter( 'sg_file_caching_preheat_url_limit', 'sgo_preheat_limit' );
	function sgo_preheat_limit( $xml_urls ) {
		// Define custom limit for XML URL preheat.
		$xml_urls = 300;

		return $xml_urls;
	}

Keep in mind that when modifying the file-cache related filters below, you need to flush the cache, so the sgo-config is re-generated and the filters are added to it.

If you need to add a cache bypass cookie to the default ones, you can use the following filter:

	add_filter( 'sgo_bypass_cookies', 'add_sgo_bypass_cookies');
	function add_sgo_bypass_cookies( $bypass_cookies ) {
		// Add the cookies, that you need to bypass the cache.
		$bypass_cookies[] = 'cookie_name';
		$bypass_cookies[] = 'cookie_name_2';

		return $bypass_cookies;
	}

If you need to skip the cache for a specific query parameter, you can use the following filter:

	add_filter( 'sgo_bypass_query_params', 'add_sgo_bypass_query_params');
	function add_sgo_bypass_query_params( $bypass_query_params ) {
		// Add custom query params, that will skip the cache.
		$bypass_query_params[] = 'query_param';
		$bypass_query_params[] = 'query_param2';

		return $bypass_query_params;
	}

If you need to add a specific query parameter which will be ignored in the cache-creation and cache-spawn processes you can do it using this filter:

	add_filter( 'sgo_ignored_query_params', 'add_sgo_ignored_query_params');
	function add_sgo_ignored_query_params( $ignored_query_params ) {
		// The query parameters which will be ignored.
		$ignored_query_params[] = 'query_param';
		$ignored_query_params[] = 'query_param2';

		return $ignored_query_params;
	}

If you need to exclude certain URLs from your website being cached you can use the filter we have designed for that purpose. Make sure to surround the url part with forward slashes. Wildcards can be used as well. You can check the below example:

	add_filter( 'sgo_exclude_urls_from_cache', 'sgo_add_excluded_urls_from_cache');
	function sgo_add_excluded_urls_from_cache( $excluded_urls ) {
		// The part of the URL which needs to be excluded from cache.
		$excluded_urls[] = '/excluded_url/';
		$excluded_urls[] = '/wildcard/exclude/*';

		return $excluded_urls;
	}

= SiteGround Optimizer Environment Page =

Here, you can force HTTPS for your site and fix insecure content errors. You can activate Database Optimization which will remove all unnecessary items from your database and optimize its tables. If you are using the InnoDB storage engine, the optimization of tables is done automatically by the engine. Use DNS-Prefetching to increase load speeds for external resources. It works by resolving the domain name, before a resource is requested. You can also manage Heartbeat Control to modify the frequency of the WP Heartbeat for different locations. By default, the WordPress Heartbeat API checks every 15 seconds on your post edit pages and every 60 seconds on your dashboard and front end whether there are scheduled tasks to be executed. With this option, you can make the checks run less frequently or completely disable them.

We have a filter that allows you to exclude specific tables from being optimized. You need to specify the table name without the database prefix.

Here's an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_db_optimization_exclude', 'sgo_db_optimization_exclude_table' );
	function sgo_db_optimization_exclude_table( $excluded_tables ) {
		// Add tables that you need to exclude without the wpdb prefix.
		$excluded_tables[] = 'table_name';
		$excluded_tables[] = 'another_table_name';

		return $excluded_tables;
	}

= SiteGround Optimizer Frontend Optimization Page =

The page is split into three tabs - CSS, JAVASCRIPT and GENERAL. In the CSS tab, you can enable/disable Minification of CSS files, activate/deactivate CSS combinations to reduce the numbers of requests to the server, and also Preload Combined CSS. Here you can also exclude styles from being combined/minified.
In the JAVASCRIPT tab, you can activate/deactivate JS minification and combination so you can reduce the script sizes as well as the number of requests made to the server. You can also Defer Render-blocking JavaScript for faster initial site load. You can also exclude specific scripts from the different types of optimizations.
The GENERAL tab offers you the possibility to Minify the HTML Output, which will remove unnecessary characters and reduce data size. With the Web Fonts Optimization we’re changing the default way we load Google fonts. A preconnect link for Google's font repository will be added in your head tag. This informs the browser that your page intends to establish a connection to another origin, and that you'd like the process to start as soon as possible. In addition, all other local fonts will be preloaded so browsers can cache and render them faster. Also when combined with CSS Combination, we will change the font-display property to swap or add it if it's missing, so we ensure faster rendering. You can Disable Emojis support in your pages to prevent WordPress from detecting and generating emojis on your pages. You can also Remove Query Strings from static resources to improve their caching.

= SiteGround Optimizer Media Optimization Page =

Image Compression:
	Here, you can configure the Image compression in order to resize your existing images and decrease the space they are occupying. The dimension of the images will not change. You can fine-tune the compression level as well as choose either original images backups are created. It is available only on SiteGround Environment.

Use WebP Images:
	WebP is a next generation image format supported by modern browsers which greatly reduces the size of your images. If the browser does not support WebP, the original images will be loaded. It is available only on SiteGround Environment.

You can also enable or disable Lazy Load for various assets. You can also exclude specific assets such as iframes, videos, thumbnails, widgets, shortcodes and more from the dropdown menu. You have an option to exclude specific images from Lazy Loading. This is done by adding the class of the image in the tab. Enable the Maximum Image Width if you often upload large images on your website. Enabling this will resize your existing and future images whose width exceeds 2560 px.

You can modify the max image width setting using the filter we've designed for that purpose. Here's an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_set_max_image_width', 'max_image_width' );
	function max_image_width( $max_allowed_width ) {
		// Add the value you want to adjust as max image width.
		$max_allowed_width = 1250;

		return $max_allowed_width;
	}

You can modify the default Webp quality setting using the filter we've designed for that purpose. The default setting is 80, you can use values between 0 and 100:

	add_filter( 'sgo_webp_quality', 'webp_quality' );
	function webp_quality( $quality ) {
		// Add the value you want to adjust as Webp image quality.
		$quality = 100;

		return $quality;
	}

You can modify the default Webp quality type setting using the filter we've designed for that purpose. The default quality type is lossy which equals to 0, if you want to set it to lossless - adjust the type to 1 as follows:

	add_filter( 'sgo_webp_quality_type', 'reset_webp_quality_type' );
	function reset_webp_quality_type( $quality_type ) {
		// Add the value you want to adjust as max image width.
		$quality_type = 1;

		return $quality_type;
	}

= SiteGround Optimizer Speed Test Page =

Our performance check is powered by Google PageSpeed. Here you can check how well your website is optimized. The detailed test results  will provide you with additional information on what can be optimized more.

= Plugin Compatibility =

If your plugin does not trigger standard WordPress hooks or you need us to purge the cache, you can use this public function in your code:

	if (function_exists('sg_cachepress_purge_cache')) {
		sg_cachepress_purge_cache();
	}

Preferably, you can pass an URL to the function to clear the cache just for it instead of purging the entire cache. For example:

	if (function_exists('sg_cachepress_purge_cache')) {
		sg_cachepress_purge_cache('https://yoursite.com/pluginpage');
	}

You can exclude styles from being combined and minified using the filters we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_css_combine_exclude', 'css_combine_exclude' );
	function css_combine_exclude( $exclude_list ) {
		// Add the style handle to exclude list.
		$exclude_list[] = 'style-handle';
		$exclude_list[] = 'style-handle-2';

		return $exclude_list;
	}

	add_filter( 'sgo_css_minify_exclude', 'css_minify_exclude' );
	function css_minify_exclude( $exclude_list ) {
		// Add the style handle to exclude list.
		$exclude_list[] = 'style-handle';
		$exclude_list[] = 'style-handle-2';

		return $exclude_list;
	}

You can exclude script from being minified using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_js_minify_exclude', 'js_minify_exclude' );
	function js_minify_exclude( $exclude_list ) {
		$exclude_list[] = 'script-handle';
		$exclude_list[] = 'script-handle-2';

		return $exclude_list;
	}

You can exclude script from being combined using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_javascript_combine_exclude', 'js_combine_exclude' );
	function js_combine_exclude( $exclude_list ) {
		$exclude_list[] = 'script-handle';
		$exclude_list[] = 'script-handle-2';

		return $exclude_list;
	}

You can exclude an external script from being combined using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_javascript_combine_excluded_external_paths', 'js_combine_exclude_external_script' );
	function js_combine_exclude_external_script( $exclude_list ) {
		$exclude_list[] = 'script-host.com';
		$exclude_list[] = 'script-host-2.com';

		return $exclude_list;
	}

You can exclude inline script from being combined using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_javascript_combine_excluded_inline_content', 'js_combine_exclude_inline_script' );
	function js_combine_exclude_inline_script( $exclude_list ) {
		$exclude_list[] = 'first few symbols of inline content script';

		return $exclude_list;
	}

You can exclude all inline scripts from being combined using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_javascript_combine_exclude_all_inline', '__return_true' );

You can exclude all inline scripts from being combined using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_javascript_combine_exclude_all_inline_modules', '__return_true' );

You can exclude script from being loaded asynchronously  using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_js_async_exclude', 'js_async_exclude' );
	function js_async_exclude( $exclude_list ) {
		$exclude_list[] = 'script-handle';
		$exclude_list[] = 'script-handle-2';

		return $exclude_list;
	}

You can exclude url or url that contain specific query param using the following filters:

	add_filter( 'sgo_html_minify_exclude_params', 'html_minify_exclude_params' );
	function html_minify_exclude_params( $exclude_params ) {
		// Add the query params that you want to exclude.
		$exclude_params[] = 'test';

		return $exclude_params;
	}

	add_filter( 'sgo_html_minify_exclude_urls', 'html_minify_exclude' );
	function html_minify_exclude( $exclude_urls ) {
		// Add the url that you want to exclude.
		$exclude_urls[] = 'http://mydomain.com/page-slug';

		return $exclude_urls;
	}

You can exclude static resources from the removal of their query strings using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_rqs_exclude', 'sgo_rqs_exclude_scripts' );
	function sgo_rqs_exclude_scripts( $exclude_list ) {
		$exclude_list[] = 'part-of-the-resource-path.js';
		return $exclude_list;
	}

You can exclude images from Lazy Load using the following filter:

	add_filter( 'sgo_lazy_load_exclude_classes', 'exclude_images_with_specific_class' );
	function exclude_images_with_specific_class( $classes ) {
		// Add the class name that you want to exclude from lazy load.
		$classes[] = 'test-class';

		return $classes;
	}

You can exclude specific post type from Lazy Load using the following filter:

	add_filter( 'sgo_lazy_load_exclude_post_types', 'exclude_lazy_load_from_post_type' );
	function exclude_lazy_load_from_post_type( $post_types ) {
		// Add the post type that you want to exclude from using lazy load.
		$post_types[] = 'post-type';

		return $post_types;
	}

You can exclude a specific url from Lazy Load using the following filter:

	add_filter( 'sgo_lazy_load_exclude_urls', 'exclude_lazy_load_for_url' );
	function exclude_lazy_load_for_url( $excluded_urls ) {
		// Add the url that you want to exclude from using lazy load.
		$excluded_urls[] = 'http://mydomain.com/page-slug';

		return $excluded_urls;
	}

With these new filters you can exclude specific assets from being lazy loaded. Keep in mind that using those filters can reduce perfomance in some cases.

You can use this filter for excluding specific images by adding their source url:

	add_filter( 'sgo_lazy_load_exclude_images', 'exclude_images_from_lazy_load' );
	function exclude_images_from_lazy_load( $excluded_images ) {
		// Add the src url of the image that you want to exclude from using lazy load.
		$excluded_images[] = 'http://mydomain.com/wp-content/uploads/your-image.jpeg';

		return $excluded_images;
	}

You can use this filter for excluding specific videos by adding their source url:

	add_filter( 'sgo_lazy_load_exclude_videos', 'exclude_videos_from_lazy_load );
	function exclude_videos_from_lazy_load( $excluded_videos ) {
		// Add the src url of the video that you want to exclude from using lazy load.
		$excluded_videos[] = 'http://mydomain.com/wp-content/uploads/your-video.mp4';

		return $excluded_videos;
	}

You can use this filter for excluding specific iframe by adding their source url:

	add_filter( 'sgo_lazy_load_exclude_iframe', 'exclude_iframe_from_lazy_load );
	function exclude_iframe_from_lazy_load( $excluded_iframe ) {
		// Add the src url of the iframe that you want to exclude from using lazy load.
		$excluded_iframe[] = 'http://mydomain.com/wp-content/uploads/iframe-src.mp4';

		return $excluded_iframe;
	}

= WP-CLI Support = 

In version 5.0 we've added full WP-CLI support for all plugin options and functionalities. 

Caching:
* `wp sg optimize dynamic-cache enable|disable` - enables or disables Dynamic caching rules
* `wp sg optimize file-cache enable|disable` - enables or disables File caching rules
* `wp sg memcached enable|disable` - enables or disables Memcached
* `wp sg optimize autoflush-cache enable|disable` - enables or disables Automatic Purge cache option
* `wp sg optimize purge-rest-cache enable|disable` - enables or disables Automatic Purge for WordPress API cache
* `wp sg purge (url)` - purges the Dynamic, File-based and Object caches for the site or a single url (if passed)
* `wp sg optimize mobile-cache enable|disable` - enables or disables Browser caching rules

Environment:
* `wp sg forcehttps enable|disable` - enables or disables HTTPS for your site
* `wp sg optimize fix-insecure-content enable|disable` - enables or disables Insecure Content Fix
* `wp sg heartbeat frontend|dashboard|post --frequency=<frequency>` - Adjust Heartbeat control frequency for a specific location
* `wp sg dns-prefetch add|remove|urls <value>` - add, remove or list urls in the DNS Prefetch list.
* `wp sg database-optimization enable|disable|update|status --options=<database_optimization>` - enables or disables the DB Optimization, update for specific options only, show a full list of enabled options.

= Available for the database-optimization options: =

* delete_auto_drafts
* delete_revisions
* delete_trashed_posts
* delete_spam_comments
* delete_trash_comments
* expired_transients
* optimize_tables

Frontend:
* `wp sg optimize css enable|disable` - enables or disables CSS minification
* `wp sg optimize combine-css enable|disable` - enables or disables CSS combination
* `wp sg optimize preload-combined-css enable|disable` - enables or disables Preload Combined CSS
* `wp sg optimize js enable|disable` - enables or disables JS minification
* `wp sg optimize combine-js enable|disable` - enables or disables JS combination
* `wp sg optimize js-async enable|disable` - enables or disables Defer Render-blocking JavaScript option
* `wp sg optimize html enable|disable` - enables or disables HTML minification
* `wp sg optimize web-fonts enable|disable` - enables or disables Web Fonts Optimization
* `wp sg optimize querystring enable|disable` - enables or disables query strings removal
* `wp sg optimize emojis enable|disable` - enables or disables stripping of the Emoji scripts

Media:
* `wp sg images --compression-level=<int>` - adjusts images optimization compression level - <value> should be set as follows: 0 for Disabled, 1 for Low(25%), 2 for Medium(60%), 3 for High(85%)
* `wp sg optimize backup-media enable|disable` - enables or disables Backup Media option
* `wp sg optimize webp enable|disable` - enables or disables WebP image optimization
* `wp sg optimize lazyload enable|disable` - enables or disables Lazy loading of images
* `wp sg optimize resize-images enable|disable` - enables or disables Maximum Image Width optimization

Others:
* `wp sg settings export` - exports the current plugin settings
* `wp sg settings import --hash=<string>` - imports plugin settings and applies them
* `wp sg status (optimization option)` - returns optimization current status (enabled|disabled)

= Available wp sg status options =

* dynamic-cache|autoflush|browser-caching|file-cache
* memcache
* ssl|ssl-fix
* database-optimization
* html|js|css
* combine-css|combine-js
* js-async
* google-fonts
* querystring
* emojis
* webp
* lazyload-images

= Requirements =

In order to work correctly, this plugin requires that your server meets the following criteria:

* WordPress 4.7
* PHP 7.0+

Our plugin uses a cookie in order to function properly. It does not store personal data and is used solely for the needs of our caching system.

== Installation ==

= Automatic Installation =

1. Go to Plugins -> Add New
1. Search for "SiteGround Optimizer"
1. Click on the Install button under the SiteGround Optimizer plugin
1. Once the plugin is installed, click on the Activate plugin link

= Manual Installation =

1. Login to the WordPress admin panel and go to Plugins -> Add New
1. Select the 'Upload' menu 
1. Click the 'Choose File' button and point your browser to the sg-cachepress.zip file you've downloaded
1. Click the 'Install Now' button
1. Go to Plugins -> Installed Plugins and click the 'Activate' link under the WordPress SiteGround Optimizer listing

== Changelog ==

= Version 7.3.3 =
Release Date: June 8th, 2023

* Downgraded wp-background-processing external lib due to incompatibility with third party themes and plugins.

= Version 7.3.2 =
Release Date: June 6th, 2023

* Improved Optimized images filesize detection.
* Improved Defer Render-blocking JavaScript.
* Improved PHP 8.2 compatibility.
* Improved Flo forms and CSS Combination compatibility.
* Improved Avada theme compatibility.

= Version 7.3.1 =
Release Date: Feb 23rd, 2023

* Improved compatibility with WooCommerce related plugins.
* Internal configuration improvements.

= Version 7.3.0 =
Release Date: Feb 1st, 2023

* Improved Multisite File-Based cache support.
* Improved compatibility with woo-variation-swatches and facebook-for-woocommerce plugins.
* Improved compatibility with Foogra theme.
* Internal configuration changes.

= Version 7.2.9 =
Release Date: Dec 2nd, 2022

* Fix for missing apache_response_headers function.

= Version 7.2.8 =
Release Date: Dec 1st, 2022

* Improved namespace performance on other hosts.
* Improved WooCommerce Square plugin support.

= Version 7.2.7 =
Release Date: Nov 30rd, 2022

* Improved Memcache Health status checks
* Improved FileBased Cache cleanup
* Improved FileBased Cache Headers checks
* Improved LazyLoad for sidebar images
* Improved Speed Test results
* Improved Test URL cache status

= Version 7.2.6 =
Release Date: Nov 21st, 2022

* Discontinue of Cloudflare support

= Version 7.2.5 =
Release Date: October 18th, 2022

* Improved Database Optimization interface
* Improved Multisite Memcached support

= Version 7.2.4 =
Release Date: October 11th, 2022

* Memcached Service bug fix

= Version 7.2.3 =
Release Date: October 11th, 2022

* Install Service fix

= Version 7.2.2 =
Release Date: October 10th, 2022

* New filter - Exclude URL from cache
* New filter - Exclude inline scripts from combination
* Improved Database Optimization options
* Improved Memcached service
* Improved Toolset Types plugin support
* Improved admin menu ordering
* Legacy code removed

= Version 7.2.1 =
Release Date: August 10th, 2022

* Improved Cloudflare detection

= Version 7.2.0 =
Release Date: July 14th, 2022

* Brand New Design
* Improved Dynamic cache purge
* Improved data collection

= Version 7.1.5 =
Release Date: June 23rd, 2022

* Improved Memcached service

= Version 7.1.4 =
Release Date: June 21st, 2022

* Improved older PHP versions support

= Version 7.1.3 =
Release Date: June 20th, 2022

* NEW Lazy Load exclude filters
* Improved Max Image Width
* Improved .htaccess modifications checks
* Improved File-Based cache Elementor support
* Improved Password Protected pages excluded from File-Based caching by default
* Improved Single Image compression functionality
* Improved Image Optimization for custom image sizes
* Improved Divi theme support
* Minor fixes

= Version 7.1.2 =
Release Date: June 16th, 2022

* Adding Memcached UNIX socket support
* Improved data collection

= Version 7.1.1 =
Release Date: May 20th, 2022

* Improved default settings

= Version 7.1.0 =
Release Date: May 10th, 2022

* Improved HTTPS Enforce
* Improved Database Optimization
* Improved Auto Purge functionality for scheduled posts
* Improved File-Based cache exclude filtering
* Improved CSS Combination
* Improved JS Minification
* Improved Meks Flexible Shortcodes plugin support
* Improved All in One SEO plugin support
* Improved Authorize.Net Gateway support

= Version 7.0.9 =
Release Date: April 7th, 2022

* Improved SG Security plugin support

= Version 7.0.8 =
Release Date: April 6th, 2022

* Improved File-Based cache checks
* Improved Memcached service checks
* Improved activation checks
* Code Refactoring

= Version 7.0.7 =
Release Date: March 24th, 2022

* Image deletion refactoring

= Version 7.0.6 =
Release Date: March 4th, 2022

* Improved installation for users not hosted on SiteGround

= Version 7.0.5 =
Release Date: March 2nd, 2022

* Improved Cache Preheat
* Improved Auto Purge functionality (File-Based cache, comments, custom post types)
* Improved new images WebP generation
* Improved Multisite support

= Version 7.0.4 =
Release Date: February 28th, 2022

* Improved uninstall checks

= Version 7.0.3 =
Release Date: February 14th, 2022

* Improved Divi support
* Improved WP-CLI support

= Version 7.0.2 =
Release Date: February 10th, 2022

* Improved Cloudflare cache purge
* Improved Divi support
* Improved JS Combination
* Improved WP-CLI support
* Minor bug fixes

= Version 7.0.1 =
Release Date: February 4th, 2022

* Improved Cache Preloading
* Improved Cloudflare detection
* Improved Divi support
* Minor bug fixes

= Version 7.0.0 =
Release Date: February 2nd, 2022

* NEW – Plugin available for users not hosted on SiteGround
* NEW – File-Based Full Page Caching
* NEW – File-Based Full Page Caching for Logged-in Users
* NEW – Cache Preloading (requires FB Caching)
* NEW – Individual Image compression level settings
* Code Refactoring and General Improvements
* Improved HTML Minification
* Improved LazyLoad excludes
* Improved Automatic Purge for custom post types
* Improved Cache exclude for wp-json URLs
* Improved Test URL cache option
* Improved Cloudflare detection
* Improved Phlox theme support
* Improved WooCommerce email verification support
* Improved WP-CLI support
* Environment data collection consent added

= Version 6.0.5 =
Release Date: November 17th, 2021

* Improved HTML minification

= Version 6.0.4 =
Release Date: November 16th, 2021

* Improved HTML minification
* Improved HTTPS Enforce for multisites
* Improved Google PageSpeed Integration
* Improved multisite permissions for admins
* Improved autoflush

= Version 6.0.3 =
Release Date: November 8th, 2021

* Improved translations
* Improved multisite support
* Improved Speed test result
* Improved assets cleanup
* Improved Cloudflare support

= Version 6.0.2 =
* Improved translations
* Improved image optimization

= Version 6.0.1 =
* Improved recommended optimizations labels
* Improved REST API error handling
* Improved CF authentication
* Improved multisite interface
* Improved database optimization
* Fixed Memcached healthcheck

= Version 6.0.0 =
* Brand New Design
* Code Refactoring
* NEW - Recommended Optimizations
* NEW - Plugin Dashboard Page
* NEW - Maximum Image Width
* NEW - Backup Original Images when Optimizing
* NEW - WordPress API Cache Control
* Improved WordPress Heartbeat Optimization
* Improved Image Compression Control 

= Version 5.9.7 =
* Improved cache busting for themes utilizing custom post types

= Version 5.9.6 =
* Improved WP Json purging mechanisms
* Added protection against cronjob loops caused by 3rd party plugins
* Improved Spam comments handling

= Version 5.9.5 =
* Improved Page Builders Support (Elementor, Oxygen, Divi and others)

= Version 5.9.4 =
* Improved smart cache

= Version 5.9.3 =
* Fixed WebP regeneration issue

= Version 5.9.2 =
* Improved cache flush queue

= Version 5.9.1 =
* Minor bug fixes

= Version 5.9.0 =
* Plugin refactoring

= Version 5.8.5 =
* Improved CF detection
* Minor bug fixes

= Version 5.8.4 =
* Improved cache purging mechanism

= Version 5.8.3 =
* Improved cache purge

= Version 5.8.2 =
* Improved speed tests
* Improved Google Fonts combination
* Improved HTTPS enforce

= Version 5.8.1 =
* Improved cache purge

= Version 5.8.0 =
* Added preloading for combined css scripts
* New and improved performance test
* Design improvements
* Custom error handler removed
* Increased WebP PNG optimization limit
* Changed tutorials urls
* Improved readme file
* Minor bug fixes

= Version 5.7.20 =
* Perform smart purge on the blog page when editing a post
* Remove jQuery Dependency from Lazy-load

= Version 5.7.19 =
* Change loseless quality

= Version 5.7.18 =
* Improved REST API cache invalidation

= Version 5.7.17 =
* Improved WordPress 5.7 support

= Version 5.7.16 =
* Improved Contact Form 7 support
* Improved Amelia booking support
* Improved support for sites with custom wp-content dir

= Version 5.7.15 =
* Improved Contact Form 7 support  

= Version 5.7.14 =
* Improved Vary:User-Agent handling

= Version 5.7.13 =
* Add settings import/export cli command
* Exclude XML sitemaps from optimizations
* Fix DNS Resolver fatal error for non existing hosts
* Fix Cloudflare optimization for sites with custom wp-content dir
* Improved Speed Test description for Webfonts optimization

= Version 5.7.12 =
* Improved Feed Cache Flush

= Version 5.7.11 =
* Improved CloudFlare Optimization
* Add CloudFlare multisite support
* Improved RevSlider support
* Add uploads permissions check

= Version 5.7.10 =
* Revert to old HTML Minification

= Version 5.7.9 =
* Fixed bug with WooCommerce ajax

= Version 5.7.8 =
* Fix HTML Minification Refactoring

= Version 5.7.7 =
* HTML Minification Refactoring

= Version 5.7.6 =
* Improved cache flush, on automatic assets deletion

= Version 5.7.5 =
* Improved Flatsome UX Builder support
* Improved Essential Addons for Elementor support
* Updated readme

= Version 5.7.4 =
* Improved Leverage Browser Caching rules
* Add exclude by post type
* Improved Cloudflare cache purge

= Version 5.7.3 =
* Improved cache purge on Cludflare activation
* Improved deauthentication of Cloudflare
* Text improvements

= Version 5.7.2 =
* Fixed bug when external assets are not cleared properly
* Improved detection of active Cloudflare
* Text improvements

= Version 5.7.1 =
* Fixed bug with clearing cache from helper function

= Version 5.7.0 =
* Full-page Caching on CloudFlare
* Web Fonts Optimization

= Version 5.6.8 =
* Improved SSL Replace patterns

= Version 5.6.7 =
* Improved JS & CSS Combination exclude list
* Bumped JS Combination stop limit
* Improved functionality to stop JS Combination if randomized names create endless combination files

= Version 5.6.6 =
* Improved JS Combination exclude list
* Bumped JS Combination stop limit
* Fixed typos

= Version 5.6.5 =
* Improved Elementor Pro 3.0 support

= Version 5.6.4 =
* Fix error in CSS Combinator

= Version 5.6.3 =
* Better WP 5.5 support
* Improved log handling

= Version 5.6.2 =
* Improved JS Combination exclude list
* Disable native WordPress lazyloading

= Version 5.6.1 =
* Second stage of Memcached improvements applied
* Added WP-CLI control for heartbeat, dns-prefetching and db optimizations
* Fixed non-critical notices while using PHP 7.3

= Version 5.6.0 =
* Added Heartbeat Control
* Added Database Optimization
* Added DNS Prefetching
* Improved Browser Caching XML rules
* Refactored Lazy Load
* Deprecated Compatibility Checker & PHP Switcher
* Improved Lazyload Videos for Classic Editor
* Added functionality to stop JS Combination if randomized names create endless combination files

= Version 5.5.8 =
* Added proper AMP support
* Added samesite parameter to the bypass cookie
* Added support for Shortcodes Ultimate
* Extended the sg purge wp-cli command to delete assets too
* Improved support for Uncode Themes

= Version 5.5.7 =
* Improved Memcached Integration
* Added protection for objects too big to be stored in Memcached
* Improved JS and CSS Combination Exclude List
* Improved Lazy Load functionality
* Improved Image Optimization for sites using CDN

= Version 5.5.6 =
* Improved WP CLI commands
* Extended Combine JavaScript Exclude list
* Improved Beaver Builder Support
* Revamped bypass cookie functionality
* Improved Multisite Controls

= Version 5.5.5 =
* Improved Script Combinations Excluding Functionality
* Improved Internationalisation
* Improved Lazy Loading
* Improved WooCommerce Support for 3rd Party Payment Gateways
* Added Global JS exclude for Plugins with Known Issues
* Added WP-Authorized-Net support
* Added Facebook for WooCommerce support

= Version 5.5.4 =
* Fixed issue with CSS Combination causing problems with media specific stylesheets
* Added defer attribute for the Combined JS files when JS Defer is enabled
* Better support with sites using long domains (.blog, .marketing, etc.)
* Fixed Memcached XSS security issues
* Fixed CSS & JS Combination for sites with custom upload folders

= Version 5.5.3 =
* Fix ISE for Flatsome theme

= Version 5.5.2 =
* Better CSS Combination
* Better Fonts Combination
* Better concatenation of inline scripts
* Improved WebP Quaity Slider
* Updated readme.txt file
* Added WP-CLI Commands: combine-js and webp
* Better Polylang support

= Version 5.5.1 =
* Better Elementor support
* Better Divi support
* Better AMP support
* Better sourcemapping removal

= Version 5.5.0 =
* New - Combine JavaScript Files
* New - WebP quality control plus lossless option
* New - What's new and opportunities slider
* Improved - better WPML support (mostly memcached)
* Improved - better Elementor support
* Improved - better Browser Caching rules for cPanel users

= Version 5.4.6 =
* Interface revamp for better accessability
* Improved compatibility with page builders
* Improved compatibility with latest Elementor
* Added support for popular AMP plugins 
* Better WebP optiomization status reporting

= Version 5.4.5 =
* Improved elementor support
* Improved flothemes support
* Improved handling of @imports in combine css

= Version 5.4.4 =
* Improved transients handling
* Added Jet Popup support

= Version 5.4.3 =
* Added Lazy loading functionality for iframes
* Added Lazy loading functionality for videos

= Version 5.4.2 =
* Fixed bug with WebP image regeneration on image delete

= Version 5.4.1 =
* Added PHP 7.4 support for PHP Compatibility Checker
* Improved WebP Conversion
* Fixed bug with WebP image regeneration on image edit
* Improved plugin localization

= Version 5.4.0 =
* Added WebP Support on All Accounts on Site Tools
* Added Google PageSpeed Test 
* Improved Image Optimization Process
* Improved SSL Certificate check

= Version 5.3.10 =
* Better PHP Version Management for Site Tools
* NGINX Direct Delivery for Site Tools

= Version 5.3.9 =
* Improved check for SiteGround Servers

= Version 5.3.8 =
* Fixed a bug when Memcached fails to purge when new WordPress version requiring a database update is released
* Added alert and check if you’re running SiteGround Optimizer on a host different than SiteGround
* Improved compatibility with WooCommerce
* Improved conditional styles combination
* Improved image optimization process

= Version 5.3.7 =
* Added WooCommerce Square Payment & Braintree For WooCommerce Exclude by Default
* Improved Google Fonts Optimization
* Added Notice for Defer Render-Blocking Scripts Optimization
* Added wp-cli commands for Google Fonts Optimization
* Changed New Images Optimizer hook to wp_generate_attachment_metadata

= Version 5.3.6 =
* Improved Google Fonts loading with better caching
* Improved Defer of render-blocking JS

= Version 5.3.5 =
* WordPress 5.3 Support Declared
* Better Elementor Compatibility
* Better Image Optimization Messaging
* Better Google Fonts combination
* Added PHP 7.4 support

= Version 5.3.4 =
* Improved Async load of JS files
* Added Google Fonts Combination optimization
* Moved lazyload script in footer
* Improved CSS combination

= Version 5.3.3 =
* Improved browser cache handling upon plugin update
* Added wp-cli commands for Dynamic Cache, Autoflush and Browser-Speciffic cache handling

= Version 5.3.2 =
* Fixed bug with https enforce for www websites
* Improved JILT support

= Version 5.3.1 =
* Better SSL force to accommodate websites with WWW in the URL
* Global exclusion of siteorigin-widget-icon-font-fontawesome from Combine CSS

= Version 5.3.0 =
* Refactoring of the Lazy Load functionality
* Redesign of the Lazy Load screen
* Improved WooCommerce product image Lazy Load
* Gzip functionality update for Site Tools accounts
* Browser caching functionality update for Site Tools accounts
* Improved Browser caching functionality for cPanel accounts

= Version 5.2.5 =
* New Feature: Option to split caches per User Agent
* New Feature: Option to disable lazy loading for mobile devices
* Improved Memcached check

= Version 5.2.4 =
* Improved XML RCP checks compatibility

= Version 5.2.3 =
* Improved LazyLoad

= Version 5.2.2 =
* Improved Events Calendar Compatibility
* Suppressed notices in the REST API in certain cases
* Improved nonscript tag in LazyLoad

= Version 5.2.1 =
* Improved Cloudflare compatibility

= Version 5.2.0 =
* Exclude list Interface for JavaScript handlers
* Exclude list Interface for CSS handlers
* Exclude list Interface for HTML minification (URL like dynamic)
* Exclude list interface for LazyLoading (Class)
* Improved Thrive Architect support
* Fixed notice when purging comment cache

= Version 5.1.3 =
* Improved Elementor support
* Improved CSS optimization for inclusions without protocol
* Excluded large PNGs from optimizations
* Added better WP-CLI command documentation

= Version 5.1.2 =
* Added support for Recommended by SiteGround PHP Version
* Improved LazyLoad Support for WooCommerce sites
* Improved Image Optimization checks
* Improved PHP Version switching checks
* Added wp cli status command for checking optimization status
* Fixed bug with Combine CSS

= Version 5.1.1 =
* Improved cache invalidation for combined styles
* Cache purge from the admin bar now handles combined files too
* Added filter to exclude images from Lazy Loading
* Added filter to exclude pages from HTML Minification
* Added Filter to query params from HTML Minification
* Added PHP 7.3 support

= Version 5.1.0 =
* Added CSS Combination Functionality
* Added Async Load of Render-Blocking JS
* Added WooCommerce Support for LazyLoad
* Added Filter to Exclude Styles from CSS Combination
* Improved Lazy Load Functionality on Mobile Devices
* Fixed Issue with WP Rocket’s .htaccess rules and GZIP
* Fixed Issue with Query String Removal Script in the Admin Section
* Fixed Compatibility Issues with 3rd Party Plugins and Lazy Load
* Fixed Compatibility Issues with Woo PDF Catalog Plugin and HTML Minification
* Improved Memcached Reliability
* Improved Lazy Load for Responsive Images

= Version 5.0.13 =
* Modified HTML minification to keep comments
* Interface Improvements
* Better input validation and sanitation for PHP Version check
* Improved security

= Version 5.0.12 =
* Better cache purge for multisite
* Surpress dynamic cache notices for localhost sites

= Version 5.0.11 =
* Improved handling of third party plugins causing issues with the compatibility checker functionality
* Optimized WP-CLI commands for better performance
* Better notice handling for Multisite and conflicting plugins

= Version 5.0.10 =
* Fixed issue with Mythemeshop themes
* Fixed issues with exclude URL on update
* Fixed issues with exclude URL on update
* Exclude Lazy Load from AMP pages
* Exclude Lazy Load from Backend pages
* Fixed WPML problems
* Fixed Beaver Builder issues
* Fixed Spanish translations
* Fixed incompatibility with JCH Optimize

= Version 5.0.9 =
* Fixed woocommerce bugs
* Improved memcached flush
* Improved https force

= Version 5.0.8 =
* Better .htaccess handling when disabling and enabling Browser Cache and Gzip
* Improved image optimization handling
* Added option to stop the image optimization and resume it later
* Fixed bug with memcached notifications
* Fixed bug with conflicting plugin notices for non-admins
* Fixed bug when user accesses their site through IP/~cPaneluser
* Fixed bug with labels for HTML, CSS & JS Minification
* SEO Improvements in the Lazy Load functionality

= Version 5.0.7 =
* Fixed bug with notifications removal
* Fixed bug with modifying wrong .htaccess file for installations in subdirectory
* Flush redux cache when updating to new version 
* Improved check for existing SSL rules in your .htaccess file
* Added check and removal of duplicate Gzip rules in your .htaccess file
* Added check and removal of duplicate Browser caching  rules in your .htaccess file

= Version 5.0.6 =
* Memcache issues fixed. Unique WP_CACHE_KEY_SALT is generated each time you enable it on your site.
* Better status update handling
* Added option to start checks even if the default WP Cron is disabled (in case you use real cronjob)

= Version 5.0.5 =
* Fixed Compatibility Checker progress issues.
* Fixed images optimization endless loops.
* Changed php version regex to handle rules from other plugins.

= Version 5.0.4 =
* Fixed CSS minification issues.
* Add option to re-optimize images.
* Allow users to hide notices.

= Version 5.0.0 =
* Complete plugin refactoring
* Frontend optimiztions added
* Environment optimizations added
* Images Optimizatoins adder
* Full WP-CLI Support
* Better Multisite Support
* Better Interface

= Version 4.0.7 =
* Fixed bug in the force SSL functionality in certain cases for MS
* Added information about the cookie our plugin uses in the readme file

= Version 4.0.6 =
* Bug fixes
* Better https enforcement in MS environment

= Version 4.0.5 =
* Removed stopping of WP Rocket cache

= Version 4.0.4 =
* Minor bug fixes

= Version 4.0.3 =
* Switching recommended PHP Version to 7.1

= Version 4.0.2 =
* WPML and Memcache / Memcached bug fix

= Version 4.0.1 =
* Minor bug fixes
* UK locale issue fixed

= Version 4.0.0 =
* Added proper Multisite support
* Quick optimizations - Gzip and Browser cache config settings for the Network Admin
* Network admin can purge the cache per site 
* Network admin can disallow Cache and HTTPS configuration pages per site
* WPML support when Memcached is enabled
* Cache is being purged per site and not for the entire network
* Multiple performance & interface improvements
* Security fixes against, additional access checks introduced
* Fixed minor cosmetic errors in the interface

= Version 3.3.3 =
* Fixed minor interface issues

= Version 3.3.2 =
* Fixed bug with disabling the Force HTTPS option

= Version 3.3.1 =
* Fixed cache purge issue when CloudFlare is enabled
* Added logging of failed attempts in XMLRPC API.

= Version 3.3.0 =
* Improved public purge function for theme and plugin developers
* Added WP-CLI command for cache purge - wp sg purge

= Version 3.2.4 =
* Updated Memcache.tpl
* Fixed a link in the PHP Check interface

= Version 3.2.3 =
* Improved WP-CLI compatibility

= Version 3.2.1 =
* Improved cron fallback, added error message if the WP CRON is disabled

= Version 3.2.0 =
* Adding PHP 7.0 Compatibility check & PHP Version switch

= Version 3.0.5 =
* Improved Certficiate check

= Version 3.0.4 =
* Fixed bug with unwrittable .htaccess

= Version 3.0.3 =
* Fixed bug in adding CSS files

= Version 3.0.2 =
* User-agent added to the SSL availability check

= Version 3.0.1 =
* PHP Compatibility fixes

= Version 3.0.0 =

* Plugin renamed to SG Optimizer
* Interface split into multiple screens
* HTTPS Force functionality added which will reconfigure WordPress, make an .htaccess redirect to force all the traffic through HTTPS and fixes any potential insecure content issues
* Plugin prepared for PHP version compatibility checker and changer tool

= Version 2.3.11 =
* Added public purge function
* Memcached bug fixes

= Version 2.3.10 =
* Improved Memcached performance
* Memcached bug fixes

= Version 2.3.9 =
* Improved WordPress 4.6 compatibilitty

= Version 2.3.8 =
* Improved compatibility with SiteGround Staging System

= Version 2.3.7 =
* Fixed PHP warnings in Object Cache classes

= Version 2.3.6 =
* Minor URL handling bug fixes

= Version 2.3.5 =
* Improved cache testing URL detection

= Version 2.3.4 =
* CSS Bug fixes

= Version 2.3.3 =
* Improved Memcache work
* Interface improvements
* Bug fixes

= Version 2.3.2 =
* Fixed bug with Memcached cache purge

= Version 2.3.1 =
* Interface improventes
* Internationalization support added
* Spanish translation added by <a href="https://www.siteground.es">SiteGround.es</a>
* Bulgarian translation added

= Version 2.3.0 =
* Memcached support added
* Better PHP7 compatibility

= Version 2.2.11 =
* Improved compatibility with WP Rocket
* Bug fixes

= Version 2.2.10 =
* Revamped notices work
* Bug fixes

= Version 2.2.9 =
* Bug fixes

= Version 2.2.8 =
* Bug fixing and improved notification behaviour
* Fixed issues with MS installations

= Version 2.2.7 =
* Added testing box and notification if Dynamic Cache is not enabled in cPanel

= Version 2.2.6 =
* Fixed bug with Memcached causing issues after WP Database update

= Version 2.2.5 =
* Minor system improvements

= Version 2.2.4 =
* Minor system improvements

= Version 2.2.3 =
* Admin bar link visible only for admin users

= Version 2.2.2 =
* Minor bug fixes

= Version 2.2.1 =
* Added Purge SG Cache button
* Redesigned mobile-friendly interface

= Version 2.2.0 =
* Added NGINX support

= Version 2.1.7 =
* Fixed plugin activation bug

= Version 2.1.6 =
* The purge button will now clear the Static cache even if Dynamic cache is not enabled
* Better and more clear button labeling

= Version 2.1.5 =
* Better plugin activation and added to the wordpress.org repo

= Version 2.1.2 =
* Fixed bug that prevents you from enabling Memcached if using a wildcard SSL Certificate

= Version 2.1.1 =
* Cache will flush when scheduled posts become live

= Version 2.1.0 =
* Cache will be purged if WordPress autoupdates

= Version 2.0.3 =
* Minor bug fixes

= Version 2.0.2 =
* 3.8 support added

= Version 2.0.1 =
* Interface improvements
* Minor bug fixes

= Version 2.0 =
* New interface
* Minor bug fixes
* Settings and Purge pages combined into one

= Version 1.2.3 =
* Minor bug fixes
* SiteGround Memcached support added
* URL Exclude from caching list added

= 1.0 =
* Plugin created.

== Credits ==
Photo credits to Anna Shvets https://www.pexels.com/@shvetsa

== Screenshots ==

1. The SiteGround Optimizer Dashboard Page offers a quick look at the current optimization status of your website, along with shortcuts to the relevant optimization pages.
2. The SiteGround Optimizer Caching Page handles your Dynamic caching and Memcached. Here, you can exclude URls from the cache, test your site and purge the Dynamic caching manually.
3. The SiteGround Optimizer Environment Page, you can force HTTPS for your site, tweak the WordPress Heartbeat Optimization, pre-fetch external domains and enable the Database Maintenance.
4. The SiteGround Optimizer Frontend Optimization Page allows you to Minify HTML, CSS & JS, as well as to remove query strings from your static resources and disable the Emoji support.
5. The SiteGround Optimizer Media Page allows you to optimize your Media Library images, as well as adds Lazy Loading functionality for your site.
6. The SiteGround Optimizer Speed Test Page, allows you to test your site loading speed, as well as additional tips on improving your site performance.
