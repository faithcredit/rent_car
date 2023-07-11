<?php
/** File Cacher class
 *
 * @category Class
 * @package SG_File_Cacher
 * @author SiteGround
 */

namespace SiteGround_Optimizer\File_Cacher;

use SiteGround_Optimizer;
use RecursiveIteratorIterator;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Supercacher\Supercacher_Helper;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\Supercacher\Supercacher_Comments;
use SiteGround_Optimizer\Supercacher\Supercacher_Posts;
use SiteGround_Optimizer\Supercacher\Supercacher_Terms;
use SiteGround_Optimizer\Helper\File_Cacher_Trait;
use SiteGround_Helper\Helper_Service;

/**
 * SG File Cacher main class
 */
class File_Cacher extends Supercacher {
	use File_Cacher_Trait;
	/**
	 * The instance property.
	 *
	 * @since 7.0.0
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * The config filename.
	 *
	 * @var string
	 */
	const CONFIG = 'sgo-config.php';

	/**
	 * Regex for finding the wp cache constatn we need to change.
	 *
	 * @var string
	 */
	public $regex = '/(.*)?define\((\s)?[\'"]WP_CACHE[\'"](.*)/';

	/**
	 * The path to the wp-config.
	 *
	 * @var string
	 */
	public $config_file = ABSPATH . 'wp-config.php';

	/**
	 * Parameters which will be ignored in the cache-creation and cache-spawn processes.
	 *
	 * @since 7.0.0
	 *
	 * @var array $ignored_query_params
	 */
	private $ignored_query_params = array(
		'fbclid',
		'fb_action_ids',
		'fb_action_types',
		'fb_source',
		'_ga',
		'age-verified',
		'ao_noptimize',
		'usqp',
		'cn-reloaded',
		'klaviyo',
		'amp',
		'gclid',
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_content',
		'utm_term',
		'_locale',
	);

	/**
	 * Parameters which will bypass the cache-creation and cache-spawn processes.
	 *
	 * @since 7.0.0
	 *
	 * @var array $ignored_query_params
	 */
	private $bypass_query_params = array();

	/**
	 * Cookies, which will ignore the cache
	 *
	 * @since 7.0.0
	 *
	 * @var array
	 */
	private $bypass_cookies = array(
		'woocommerce_items_in_cart',
		'edd_items_in_cart',
		'wordpress_logged_in_',
		'wpSGCacheBypass',
		'comment_author_',
	);

	/**
	 * The directory that will be used to save the cached files and open them
	 *
	 * @since 7.0.0
	 *
	 * @var string
	 */
	public $output_directory = '';

	/**
	 * WP_Filesystem class instance holder
	 *
	 * @since 7.0.0
	 *
	 * @var object
	 */
	public $wp_filesystem;

	/**
	 * {File_Cacher_Background Object} instance.
	 *
	 * @var object.
	 */
	public $preheat;

	/**
	 * Option value for logged in cache enabled.
	 *
	 * @var int
	 */
	public $logged_in_cache;

	/**
	 * Construct of the class.
	 *
	 * @since 7.0.0
	 */
	public function __construct() {
		$this->preheat              = new File_Cacher_Background();
		$this->wp_filesystem        = Helper_Service::setup_wp_filesystem();
		$this->supercacher_comments = new Supercacher_Comments();
		$this->supercacher_posts    = new Supercacher_Posts();
		$this->supercacher_terms    = new Supercacher_Terms();

		$this->logged_in_cache = (int) get_option( 'siteground_optimizer_logged_in_cache' );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 7.0.0
	 *
	 * @return \File_Cacher The singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set the required directories for the filebased caching.
	 *
	 * @since 7.0.0
	 */
	public function get_cache_dir() {
		// Set the main cache dir.
		$dir = WP_CONTENT_DIR . '/cache/sgo-cache/';

		// Bail if the main directory exists.
		if ( $this->wp_filesystem->is_dir( $dir ) ) { // phpcs:ignore
			return $dir;
		}

		mkdir( $dir, 0775, true );

		$this->output_directory = $dir;

		$this->add_htaccess_file();

		return $dir;
	}

	/**
	 * Create htaccess file in the cache dir.
	 *
	 * @since 7.0.0
	 */
	public function add_htaccess_file() {
		// Set the main cache dir.
		$file_path = WP_CONTENT_DIR . '/cache/sgo-cache/.htaccess';

		// Bail if the main directory exists.
		if ( $this->wp_filesystem->is_file( $file_path ) ) { // phpcs:ignore
			return;
		}

		// Try to create the file and bail if for some reason it's not created.
		if ( false === $this->wp_filesystem->touch( $file_path ) ) {
			return false;
		}

		// Add the file content in the file, so it can be cached.
		$this->wp_filesystem->put_contents(
			$file_path,
			'Deny from all'
		);
	}

	/**
	 * Set secret cache key.
	 *
	 * @since 7.0.0
	 */
	public function set_secret() {
		return update_option( 'siteground_optimizer_file_cache_secret', md5( uniqid() ) );
	}

	/**
	 * Get secret cache key.
	 *
	 * @since 7.0.0
	 */
	public function get_secret() {
		return get_option( 'siteground_optimizer_file_cache_secret', false );
	}

	/**
	 * Get the secret key. Create such if doesn't exist.
	 *
	 * @since  7.0.0
	 *
	 * @return string The secret key.
	 */
	public function create_secret_if_not_exists() {
		$maybe_secret = $this->get_secret();

		if ( false === $maybe_secret ) {
			return $this->set_secret();
		}

		return $maybe_secret;
	}

	/**
	 * Checks if the cache path exists.
	 *
	 * @since  7.0.0
	 *
	 * @return bool True if the path exists, false otherwise.
	 */
	public function cache_exists() {
		return $this->wp_filesystem->is_dir( $this->get_cache_path() );
	}

	/**
	 * Get the cache path.
	 *
	 * @since  7.0.0
	 *
	 * @return string The cache path.
	 */
	public function get_cache_path( $url = '', $include_user = true ) {
		// Get the current url if the url params is missing.
		$url = empty( $url ) ? self::get_current_url() : $url;

		// Parse the url.
		$parsed_url = parse_url( $url );

		// Prepare the path.
		$path = $parsed_url['host'];

		if (
			true === $include_user &&
			is_user_logged_in() &&
			Options::is_enabled( 'siteground_optimizer_logged_in_cache' )
		) {
			$path .= '-' . wp_get_current_user()->user_login;
		}

		$path .= '-' . $this->get_secret();

		$path .= $parsed_url['path']; // phpcs:ignore

		return $this->get_cache_dir() . $path;
	}

	/**
	 * Porcess the request.
	 *
	 * @since  7.0.0
	 *
	 * @param  string $html The page html.
	 *
	 * @return string       The page html/
	 */
	public function process( $html ) {
		// Bail if we are on login page.
		if ( 'wp-login.php' === $GLOBALS['pagenow'] ) {
			return;
		}

		$regex = $this->get_excluded_urls_regex();

		// Check if this is an html request.
		if ( ! empty( $regex ) && preg_match( $regex, self::get_current_url() ) ) {
			header( 'SG-F-Cache: BYPASS' );
			return;
		}

		// Check if this is an ajax request.
		if ( $this->doing_ajax() ) {
			return;
		}

		// Check if this is an cron request.
		if ( $this->doing_cron() ) {
			return;
		}

		if ( $this->is_content_type_not_supported() ) {
			return false;
		}

		// Check if the post is password-protected.
		if ( ! empty( $GLOBALS['post'] ) && ! empty( $GLOBALS['post']->post_password ) ) {
			return false;
		}

		// Bail if the page is excluded from the cache.
		if ( ! $this->is_cacheable() ) {
			header( 'SG-F-Cache: BYPASS' );
			return;
		}


		$path = $this->get_cache_path();

		if ( ! is_dir( $path ) ) {
			mkdir( $path, 0755, true ); //phpcs:ignore
		}

		// Save the HTML in the file with the corresponding name.
		$this->wp_filesystem->put_contents(
			$path . $this->get_filename( $this->ignored_query_params ),
			$html
		);

		return $html;
	}

	/**
	 * Get cleanup intervals.
	 *
	 * @since  7.0.0
	 *
	 * @return array An array with cleanup intervals.
	 */
	public function get_intervals() {
		// Get the default interval.
		$interval = get_option( 'siteground_optimizer_file_caching_interval_cleanup', 604800 );

		// Prepare the intervals for the SPA app.
		$intervals = array(
			0 => array(
				'value'  => 0,
				'label'  => 'Off',
				'selected'  => 0,
			),
			43200 => array(
				'value'  => 43200,
				'label'  => '12 hours',
				'selected'  => 0,
			),
			86400 => array(
				'value'  => 86400,
				'label'  => '24 hours',
				'selected'  => 0,
			),
			172800  => array(
				'value'  => 172800,
				'label'  => '48 hours',
				'selected'  => 0,
			),
			604800  => array(
				'value'  => 604800,
				'label'  => '1 week',
				'selected'  => 0,
			),
		);

		// Mark the selected interval.
		$intervals[ $interval ]['selected'] = 1;

		// Return the intervals.
		return array_values( $intervals );
	}

	/**
	 * Get the directory size
	 *
	 * @since 7.0.0
	 *
	 * @param  string $directory  Directory to be inspected.
	 * @return integer            The size of the directory
	 */
	public function get_directory_size( $directory ) {
		// Set 0 as initial size.
		$size = 0;

		// Create an iterator to go through each sub-directory and file.
		$dir_iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$directory
			)
		);

		// Go through each subdirectory and/or files and add their size to the $size variable.
		foreach ( $dir_iterator as $file ) {
			$size += $file->getSize();
		}

		// Return the total size of the directory, including subdirectories.
		return $size;
	}

	/**
	 * Purge oldest cache, if needed
	 *
	 * @since 7.0.0
	 *
	 * @return bool  Returns false, if not needed, true if purged.
	 */
	public function maybe_purge_cache() {
		// Bail if output dir doesn't exist.
		if ( ! is_dir( $this->output_directory ) ) {
			return false;
		}

		// Check if the current directory size is bigger than the allowed size, if not, exit early.
		if ( $this->get_directory_size( $this->output_directory ) < get_option( 'sg_file_cache_dirsize' ) ) {
			return false;
		}

		// First check the mobile subdirectory.
		$directory = $this->output_directory_mobile;

		// Check if directory exists.
		if ( is_dir( $directory ) ) {
			// Get the files inside the directory.
			$files = scandir( $directory );
		}

		// Check if the directory is empty, if so, go through the desktop directory.
		if ( empty( $files ) || false === $files ) {
			$directory = $this->output_directory_desktop;
			$files     = array_diff( scandir( $directory ), array( '.', '..' ) );
		}

		// Get the first file added into the directory.
		$first_file = reset( $files );

		// If the file exists, delete it and call this function recursively.
		if ( ! empty( $first_file ) ) {
			unlink( $directory . $first_file );
			$this->maybe_purge_cache();
		}

		return true;
	}

	/**
	 * Clears the cache on specific post/page
	 *
	 * @since 7.0.0
	 *
	 * @param  string $url                  The URL of the post/page to be cleared from cache.
	 * @param  bool   $include_child_paths  True if it should include, false otherwise, true by default.
	 * @return bool   $response             Returns true on success, false on failure
	 */
	public static function purge_cache_request( $url, $include_child_paths = true ) {
		$self = self::get_instance();

		// Check if the URL is excluded, exit early if excluded.
		if ( $self->is_url_excluded( $url ) ) {
			return false;
		}

		$path = parse_url( $url, PHP_URL_PATH );

		$subdirs = array_diff( scandir( $self->get_cache_dir() ), array( '..', '.' ) );

		foreach ( $subdirs as $dir ) {
			$status = $self->purge_dir_cache( $self->get_cache_dir() . $dir . $path );

			if ( false === $status ) {
				continue;
			}

			// Preheat the cache if the option is enabled.
			if ( Options::is_enabled( 'siteground_optimizer_preheat_cache' ) ) {
				$self->hit_url_cache( $url );
			}
		}

		return true;
	}

	/**
	 * Purge directory cache.
	 *
	 * @since  7.0.0
	 *
	 * @param  string $dir The directory to delete.
	 */
	public function purge_dir_cache( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		// List all of the subdirectories of the main caching dir.
		$dirlist = list_files( $dir, 1 );

		// Check if the directory exists, if not, exit early.
		if ( false === $dirlist ) {
			return false;
		}

		// Iterate through all files in all subdirectories.
		foreach ( $dirlist as $file ) {
			if ( ! is_file( $file ) ) {
				continue;
			}

			$this->wp_filesystem->delete( $file, true );
		}

		return true;
	}

	/**
	 * Purges all cached posts/pages
	 *
	 * @since 7.0.0
	 */
	public function purge_everything() {
		// Refresh the secret.
		$this->set_secret();
		// Regenerate the config.
		$this->refresh_config();

		// Remove the old files and maybe run the preheater.
		$this->clean_cache_dir();
	}

	/**
	 * Clean the file based cache dir and maybe preheatthe cache.
	 *
	 * @since  7.0.1
	 */
	public function clean_cache_dir() {
		// Delete the main directory for the file caching.
		$this->wp_filesystem->delete( $this->get_cache_dir(), true );

		// Bail if WP Cron is disabled.
		if ( Helper_Service::is_cron_disabled() ) {
			return;
		}

		$this->schedule_cleanup();

		if (
			Options::is_enabled( 'siteground_optimizer_preheat_cache' ) &&
			Options::is_enabled( 'siteground_optimizer_file_caching' )
		) {
			wp_schedule_single_event( time(), 'siteground_optimizer_cache_preheat' );
		}
	}

	/**
	 * Schedule the cache dir cleanup.
	 *
	 * @since  7.0.1
	 */
	public function schedule_cleanup() {
		$interval = intval( get_option( 'siteground_optimizer_file_caching_interval_cleanup', 604800 ) );

		wp_clear_scheduled_hook( 'siteground_optimizer_clear_cache_dir' );

		if ( 0 === $interval ) {
			return;
		} else {
			if (
				Options::is_enabled( 'siteground_optimizer_preheat_cache' ) &&
				Options::is_enabled( 'siteground_optimizer_file_caching' )
			) {
				wp_schedule_single_event( time(), 'siteground_optimizer_cache_preheat' );
			}
		}

		wp_schedule_single_event( time() + $interval, 'siteground_optimizer_clear_cache_dir' );
	}

	/**
	 * Preheats the cache by using the default WP sitemap ( found on WP Ver > 5.5 ). Sitemap URL can be changed with the 'sg_file_caching_preheat_xml' filter.
	 *
	 * @since 7.0.0
	 *
	 * @param string $url URL to be preheated.
	 */
	public function preheat_cache( $url = false ) {
		// Hit the specific URL if provided.
		if ( false !== $url ) {
			return $this->hit_url_cache( $url );
		}

		$xml = $this->load_xml(
			apply_filters(
				'sg_file_caching_preheat_xml',
				get_sitemap_url( 'index' ) // The sitemap url.
			)
		);

		// Bail if the xml is invalid.
		if ( false == $xml ) {
			return false;
		}

		$regex = $this->get_excluded_urls_regex();

		// Limit the number of sitemap URLs we are preheating.
		$sitemap_url_limit = apply_filters( 'sg_file_caching_preheat_url_limit', 200 );

		// Sitemap URL counter.
		$counter = 0;

		// Iterate the sitemap.
		foreach ( $xml->sitemap as $entry ) {
			// Load the inner xml.
			$inner_xml = $this->load_xml( $entry->loc );

			// Bail if the xml is invalid.
			if ( false == $inner_xml ) {
				continue;
			}

			// Iterate though all links.
			foreach ( $inner_xml->url as $url ) {
				// Check if this is an html request.
				if ( ! empty( $regex ) && preg_match( $regex, $url->loc ) ) {
					continue;
				}

				// Increase the counter.
				$counter++;

				// Check if we have hit the URL limit.
				if ( $sitemap_url_limit < $counter ) {
					// Dispatch and return.
					return $this->preheat->save()->dispatch();
				}

				// Push to queue.
				$this->preheat->push_to_queue( (string) $url->loc );
			}
		}

		// Dispatch the process.
		$this->preheat->save()->dispatch();
	}

	/**
	 * Load the xml content.
	 *
	 * @since  7.0.0
	 *
	 * @param  string $url The xml url.
	 *
	 * @return mixed       The xml body or false on failure.
	 */
	public function load_xml( $url ) {
		// Get WP default sitemap.
		$content = wp_remote_get( $url );

		// Bail if the request fails.
		if ( is_wp_error( $content ) ) {
			return false;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $content ) ) {
			return false;
		}

		// Bail if the content is not xml.
		if ( 0 === Helper::is_xml( $content['body'] ) ) {
			return false;
		}

		// Parse the XML from the sitemap.
		$xml = simplexml_load_string( $content['body'] );

		// Bail if the xml is invalid.
		if ( false === $xml ) {
			return false;
		}

		return $xml;
	}

	/**
	 * This function allows hitting an URL in order to cache it, both for desktop and mobile
	 *
	 * @since 7.0.0
	 *
	 * @param string $url URL that should be cached.
	 */
	public function hit_url_cache( $url ) {

		$args = array(
			'headers' => array(
				'Accept' => 'text/html',
			),
		);

		// Open as a desktop device ( preheat desktop cache ).
		wp_remote_get( $url, $args );

		if ( ! Options::is_enabled( 'siteground_optimizer_user_agent_header' ) ) {
			return;
		}

		$args['headers']['user-agent'] = 'Mobile';

		// Open as a mobile device ( preheat mobile cache ).
		wp_remote_get( $url, $args );

	}

	/**
	 * This function registers a new interval based on the option set by the user, or the default fallback, which is 1 week
	 *
	 * @since 7.0.0
	 *
	 * @param  array $schedules An array with the already defined schedules.
	 *
	 * @return array            An array with the modified schedules.
	 */
	public function sg_add_cron_interval( $schedules ) {
		// Add the custom interval.
		$schedules['sg_once_in_two_days'] = array(
			'interval' => 172800,
			'display'  => esc_html__( 'Once in two days' ),
		);

		return $schedules;
	}

	/**
	 * Create the configuration file.
	 *
	 * @since  7.0.0
	 */
	public function create_config() {
		// Bail if the file exists.
		if ( file_exists( WP_CONTENT_DIR . '/' . self::CONFIG ) ) {
			return;
		}

		// Start the content buffering.
		$content = "<?php\n";

		// Define cookie-related WordPress constants after multisite is loaded.
		if ( \is_multisite() ) {
			\wp_cookie_constants();
		}

		// Prepare the config.
		$config = array(
			'ignored_query_params' => apply_filters( 'sgo_ignored_query_params', $this->ignored_query_params ),
			'bypass_query_params'  => apply_filters( 'sgo_bypass_query_params', $this->bypass_query_params ),
			'bypass_cookies'       => apply_filters( 'sgo_bypass_cookies', $this->bypass_cookies ),
			'output_dir'           => $this->get_cache_dir(),
			'logged_in_cache'      => (int) get_option( 'siteground_optimizer_logged_in_cache' ),
			'cache_secret_key'     => $this->create_secret_if_not_exists(),
			'logged_in_cookie'     => 'wordpress_logged_in_' . COOKIEHASH,
		);

		// Export the config.
		$content .= '$config = ' . call_user_func( 'var_export', $config, true ) . ";\n";

		// Create the config file.
		$this->wp_filesystem->put_contents( WP_CONTENT_DIR . '/' . self::CONFIG, $content );
	}

	/**
	 * Remove the configuration file.
	 *
	 * @since  7.0.0
	 */
	public function remove_config() {
		$config_path = WP_CONTENT_DIR . '/' . self::CONFIG;
		// Bail if the file exists.
		if ( ! file_exists( $config_path ) ) {
			return true;
		}

		return $this->wp_filesystem->delete( WP_CONTENT_DIR . '/' . self::CONFIG );
	}

	/**
	 * Refresh the content of the config file.
	 *
	 * @since 7.0.0
	 */
	public function refresh_config() {
		$this->remove_config();
		$this->create_config();
	}

	/**
	 * Cleanup method on plugin deactivation.
	 *
	 * @since  7.0.0
	 */
	public static function cleanup() {
		$self = self::get_instance();

		$self->remove_config();
		$self->remove_advanced_cache();
		$self->toggle_cache_constant( false );
	}

	/**
	 * Get excluded urls regex.
	 *
	 * @since  7.0.0
	 *
	 * @return string A regex for excluded urls.
	 */
	public function get_excluded_urls_regex() {
		// Get excluded urls.
		$parts = \get_option( 'siteground_optimizer_excluded_urls', array() );


		$ecommerce_pages = array();

		if ( class_exists( 'WooCommerce' ) ) {
			$ecommerce_pages[] = wc_get_cart_url();
			$ecommerce_pages[] = wc_get_checkout_url();
			$ecommerce_pages[] = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '*';
		}

		// Bail for ecommerce cart & checkout pages.
		if ( function_exists( 'edd_get_checkout_uri' ) ) {
			$ecommerce_pages[] = edd_get_checkout_uri();
		}

		$ecommerce_pages = array_map(
			function( $url ) {
				return str_replace( get_home_url(), '', $url );
			}, $ecommerce_pages
		);

		$parts = array_merge( $parts, $ecommerce_pages );

		// Bail if there are no excluded urls.
		if ( empty( $parts ) ) {
			return false;
		}

		// Prepare the url parts for being used as regex.
		$prepared_parts = array_map(
			function( $item ) {
				return str_replace( '\*', '.*', preg_quote( $item, '/' ) );
			}, $parts
		);

		// Build the regular expression.
		$regex = sprintf(
			'/%s(%s)$/i',
			preg_quote( home_url(), '/' ), // Add the home url in the beginning of the regex.
			implode( '|', $prepared_parts ) // Then add each part.
		);

		return $regex;
	}

	/**
	 * Add the advanced cache dropin
	 *
	 * @since 7.0.0
	 */
	public function add_advanced_cache() {
		// Remove old advanced-cache.php.
		if ( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) ) {
			$this->wp_filesystem->delete( WP_CONTENT_DIR . '/advanced-cache.php' );
		}

		// Copy our own advanced-cache.php.
		$result = $this->wp_filesystem->copy(
			SiteGround_Optimizer\DIR . '/templates/advanced-cache.tpl',
			WP_CONTENT_DIR . '/advanced-cache.php'
		);

		// Set a flag if the dropin is not created.
		if ( ! $result ) {
			update_option( 'sgo_file_caching_dropin_failed', 1 );
			return;
		}
	}

	/**
	 * Removes the advanced cache dropin.
	 *
	 * @since 7.0.0
	 */
	public function remove_advanced_cache() {
		// Remove old advanced-cache.php.
		if ( ! file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) ) {
			return true;
		}

		return $this->wp_filesystem->delete( WP_CONTENT_DIR . '/advanced-cache.php' );
	}

	/**
	 * Add the wp cache constant
	 *
	 * @since  7.0.0
	 *
	 * @param  bool $enable Whether to enable or disable the wp cache constant.
	 *
	 * @return bool True is the constant is changed, false otherwise.
	 */
	public function toggle_cache_constant( $enable = true ) {
		if ( ! file_exists( $this->config_file ) ) {
			return;
		}

		// Prepare the value for the constant.
		$enable = true === $enable ? 'true' : 'false';

		// Get content of the config file.
		$config_content = $this->wp_filesystem->get_contents( $this->config_file );

		// The constant.
		$constant = "define( 'WP_CACHE', $enable ); // By SiteGround Optimizer";

		$search = '/(<\?php)/i';
		$replace = "<?php\r\n{$constant}\r\n";

		if ( preg_match( $this->regex, $config_content, $matches ) ) {
			$search = $this->regex;
			$replace = $constant;
		}

		$this->wp_filesystem->put_contents(
			$this->config_file,
			preg_replace( $search, $replace, $config_content )
		);

		return true;
	}

	/**
	 * Check if the url is excluded from the filebased caching.
	 *
	 * @since  7.0.0
	 *
	 * @return boolean      True f the url is excluded, false otherwise.
	 */
	public function is_url_excluded() {
		// Bail if the request is cronjob, ajax or the wordpress admin request.
		if (
			wp_doing_cron() ||
			Helper::sg_doing_ajax() ||
			$GLOBALS['pagenow'] === 'wp-login.php' // phpcs:ignore
		) {
			return true;
		}

		// Bail for ecommerce cart & checkout pages.
		if (
			( function_exists( 'is_cart' ) && \is_cart() ) ||
			( function_exists( 'is_checkout' ) && \is_checkout() ) ||
			( function_exists( 'is_account_page' ) && \is_account_page() ) ||
			( function_exists( 'edd_is_checkout' ) && \edd_is_checkout() )
		) {
			return true;
		}

		// Bail if the url is excluded.
		if ( Supercacher_Helper::is_url_excluded( Helper::get_current_url() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Toggle the File Cache on and off.
	 *
	 * @since  7.0.0
	 *
	 * @param  int   $value The boolean for turning the file caching on or off, 1 if it should be turned on and 0 if it should be turned off.
	 * @return array
	 */
	public static function toggle_file_cache( $value ) {
		$value = intval( $value );

		// Update the option.
		$result = update_option( 'siteground_optimizer_file_caching', $value );
		$data = array(
			'file_caching' => $value,
		);

		$file_cacher = File_Cacher::get_instance();

		if ( $value ) {
			$file_cacher->set_secret();
			$file_cacher->refresh_config();
			$file_cacher->add_advanced_cache();

			if ( Helper_Service::is_siteground() ) {
				// Enable the cache.
				Options::enable_option( 'siteground_optimizer_enable_cache' );
				Options::enable_option( 'siteground_optimizer_autoflush_cache' );
				$data['enable_cache'] = 1;
				$data['autoflush_cache'] = 1;
			}

			// Schedule cleanup.
			$file_cacher->schedule_cleanup();
		} else {
			$file_cacher->remove_config();
			$file_cacher->remove_advanced_cache();
			$file_cacher->purge_everything();
			if ( ! Helper_Service::is_siteground() ) {
				// Enable the cache.
				Options::disable_option( 'siteground_optimizer_autoflush_cache' );
				Options::disable_option( 'siteground_optimizer_user_agent_header' );
				$data['autoflush_cache'] = 0;
				$data['user_agent_header'] = 0;
			}
		}

		$file_cacher->toggle_cache_constant( boolval( $value ) );

		// Purge the dynamic cache.
		Supercacher::purge_cache();

		return array(
			'status' => $result,
			'data'   => $data,
		);
	}

	/**
	 * Enable dynamic caching if the client has moved from another host
	 *
	 * @since  7.0.1
	 */
	public function maybe_enable_dynamic() {
		// Bail fi the dynamic cache is enabled.
		if ( Options::is_enabled( 'siteground_optimizer_enable_cache' ) ) {
			return;
		}

		if (
			Helper_Service::is_siteground() &&
			Options::is_enabled( 'siteground_optimizer_file_caching' )
		) {
			Options::enable_option( 'siteground_optimizer_enable_cache' );
		}
	}
}
