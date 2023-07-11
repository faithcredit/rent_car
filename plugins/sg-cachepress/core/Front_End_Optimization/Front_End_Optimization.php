<?php
namespace SiteGround_Optimizer\Front_End_Optimization;

use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\File_Cacher\File_Cacher;
use SiteGround_Helper\Helper_Service;
use SiteGround_Optimizer\Helper\Helper;

/**
 * SG Front_End_Optimization main plugin class
 */
class Front_End_Optimization {

	/**
	 * The dir where the minified styles and scripts will be saved.
	 *
	 * @since 5.0.0
	 *
	 * @var string|null Path to assets dir.
	 */
	public $assets_dir = null;


	/**
	 * Limit of assets dir in bytes.
	 *
	 * @since 5.6.0
	 *
	 * @var int Bytes.
	 */
	const LIMIT = 1000000000;

	/**
	 * Script handles that shouldn't be loaded async.
	 *
	 * @since 5.0.0
	 *
	 * @var array Array of script handles that shouldn't be loaded async.
	 */
	private $blacklisted_async_scripts = array(
		'moxiejs',
		'wc-square',
		'wc-braintree',
		'wc-authorize-net-cim',
		'sv-wc-payment-gateway-payment-form',
		'paypal-checkout-sdk',
		'uncode-app',
		'uncode-plugins',
		'uncode-init',
		'lodash',
		'wp-api-fetch',
		'wp-i18n',
		'wp-polyfill',
		'wp-url',
		'wp-hooks',
		'houzez-google-map-api',
		'wpascript',
		'wc-square',
	);

	/**
	 * Array containing all script handle regex' that should be excluded.
	 *
	 * @since 7.1.0
	 *
	 * @var   array Array containing all script handle regex' that should be excluded.
	 */
	private $blacklisted_async_regex = array(
		'sv-wc-payment-gateway-payment-form-v', // Authorize.NET payment gateway payment form script.
	);


	/**
	 * The singleton instance.
	 *
	 * @since 5.1.0
	 *
	 * @var \Front_End_Optimization The singleton instance.
	 */
	private static $instance;

	/**
	 * Create a {@link Supercacher} instance.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		// Set the assets dir path.
		$this->set_assets_directory_path();

		self::$instance = $this;
		$this->blacklisted_async_scripts = array_merge(
			$this->blacklisted_async_scripts,
			get_option( 'siteground_optimizer_async_javascript_exclude', array() )
		);
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.1.0
	 *
	 * @return \Front_End_Optimization The singleton instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set the assets directory.
	 *
	 * @since  5.1.0
	 */
	private function set_assets_directory_path() {
		// Bail if the assets dir has been set.
		if ( null !== $this->assets_dir ) {
			return;
		}

		$uploads_dir = Helper_Service::get_uploads_dir();

		// Build the assets dir name.
		$directory = $uploads_dir . '/siteground-optimizer-assets';

		// Check if directory exists and try to create it if not.
		$is_directory_created = ! is_dir( $directory ) ? $this->create_directory( $directory ) : true;

		// Set the assets dir.
		if ( $is_directory_created ) {
			$this->assets_dir = trailingslashit( $directory );
		}

	}

	/**
	 * Create directory.
	 *
	 * @since  5.1.0
	 *
	 * @param  string $directory The new directory path.
	 *
	 * @return bool              True is the directory is created.
	 *                           False on failure.
	 */
	public function create_directory( $directory ) {
		// Create the directory and return the result.
		$is_directory_created = wp_mkdir_p( $directory );

		// Bail if cannot create temp dir.
		if ( false === $is_directory_created ) {
			// translators: `$directory` is the name of directory that should be created.
			error_log( sprintf( 'Cannot create directory: %s.', $directory ) );
		}

		return $is_directory_created;
	}

	/**
	 * Get the original filepath by file handle.
	 *
	 * @since  5.1.0
	 *
	 * @param  string $original File handle.
	 *
	 * @return string           Original filepath.
	 */
	public static function get_original_filepath( $original ) {
		$home_url = Helper_Service::get_site_url();
		// Get the home_url from database. Some plugins like qtranslate for example,
		// modify the home_url, which result to wrong replacement with ABSPATH for resources loaded via link.
		// Very ugly way to handle resources without protocol.
		$result = parse_url( $home_url );

		$replace = $result['scheme'] . '://';

		$new = preg_replace( '~^https?:\/\/|^\/\/~', $replace, $original );

		// Get the filepath to original file.
		if ( @strpos( $new, $home_url ) !== false ) {
			$original_filepath = str_replace( $home_url, ABSPATH, $new );
		} else {
			$original_filepath = untrailingslashit( ABSPATH ) . $new;
		}

		return $original_filepath;
	}

	/**
	 * Return the path to assets dir.
	 *
	 * @since  5.1.0
	 *
	 * @return string Path to assets dir.
	 */
	public function get_assets_dir() {
		return $this->assets_dir;
	}

	/**
	 * Prepare scripts to be included async.
	 *
	 * @since  5.1.0
	 */
	public function prepare_scripts_for_async_load() {
		global $wp_scripts;

		// Bail if the scripts object is empty.
		if ( ! is_object( $wp_scripts ) || is_user_logged_in() ) {
			return;
		}

		$scripts = wp_clone( $wp_scripts );
		$scripts->all_deps( $scripts->queue );

		$excluded_scripts = apply_filters( 'sgo_js_async_exclude', $this->blacklisted_async_scripts );

		// Remove excluded script handles using regex.
		foreach( $this->blacklisted_async_regex as $regex ) {
			$excluded_scripts = array_merge( $excluded_scripts, Helper::get_script_handle_regex( $regex, $scripts->to_do ) );
		}

		// Get groups of handles.
		foreach ( $scripts->to_do as $handle ) {
			// We don't want to load footer scripts asynchronous.
			if (
				in_array( $handle, $excluded_scripts ) ||
				empty( $wp_scripts->registered[ $handle ]->src )
			) {
				continue;
			}

			$wp_scripts->registered[ $handle ]->src = add_query_arg( 'siteground-async', 1, $wp_scripts->registered[ $handle ]->src );
		}
	}

	/**
	 * Load all scripts async.
	 * This function adds async attr to all scripts.
	 *
	 * @since 5.1.0
	 *
	 * @param string $tag    The <script> tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @param string $src    Script src.
	 */
	public function add_async_attribute( $tag, $handle, $src ) {
		// Bail if we do not find the argument.
		if ( @strpos( $src, 'siteground-async=1' ) === false ) {
			return $tag;
		}

		// Add the async attribute and replace the & and ? with their proper representation.
		$tag = str_replace(
			array(
				'<script ',
				'-siteground-async',
				'?#038;',
				'&#038;',
			),
			array(
				'<script defer ',
				'',
				'?',
				'&',
			),
			$tag
		);

		// Match the async argument and replace it with the proper symbol, depending of the position of the argument.
		$tag = preg_replace_callback(
			'/([\?&])siteground-async=1(&|$|\b)/',
			function( $matches ) {
				return empty( $matches[2] ) ? '' : $matches[1];
			},
			$tag
		);

		return $tag;
	}

	/**
	 * Remove query strings from static resources.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $src The source URL of the enqueued style.
	 *
	 * @return string $src The modified src if there are query strings, the initial src otherwise.
	 */
	public static function remove_query_strings( $src ) {
		// Get the host.
		$host = parse_url( $src, PHP_URL_HOST );

		// Bail if the host is empty.
		if ( empty( $host ) ) {
			return $src;
		}

		// Skip all external sources.
		if ( @strpos( Helper_Service::get_home_url(), $host ) === false ) {
			return $src;
		}

		$exclude_list = apply_filters( 'sgo_rqs_exclude', array() );

		if (
			! empty( $exclude_list ) &&
			preg_match( '~' . implode( '|', $exclude_list ) . '~', $src )
		) {
			return $src;
		}

		return remove_query_arg(
			array(
				'ver',
				'version',
				'v',
				'mts',
				'nomtcache',
				'generated',
				'timestamp',
				'cache',
			),
			html_entity_decode( $src )
		);
	}

	/**
	 * Get styles and scripts loaded on the site.
	 *
	 * @since  5.2.0
	 *
	 * @return arary $data Array of all styles and scripts loaded on the site.
	 */
	public function get_assets() {
		// Get the global varialbes.
		global $wp;
		global $wp_styles;
		global $wp_scripts;

		// Pre-load Woocommerce functionality, if needed.
		if ( function_exists( '\WC' ) && defined( '\WC_ABSPATH' ) ) {
			include_once \WC_ABSPATH . 'includes/wc-cart-functions.php';
			include_once \WC_ABSPATH . 'includes/class-wc-cart.php';

			if ( is_null( WC()->cart ) ) {
				wc_load_cart();
			}
		}

		// Remove the jet popup action to prevent fatal errros.
		remove_all_actions( 'elementor/editor/after_enqueue_styles', 10 );

		$wp_scripts->queue[] = 'wc-jilt';

		ob_start();
		// Call the action to load the assets.
		do_action( 'wp', $wp );
		do_action( 'wp_enqueue_scripts' );
		do_action( 'elementor/editor/after_enqueue_styles' );
		ob_get_clean();

		unset( $wp_scripts->queue['wc-jilt'] );


		// Build the assets data.
		return array(
			'scripts' => $this->get_assets_data( $wp_scripts ),
			'styles'  => $this->get_assets_data( $wp_styles ),
		);
	}

	/**
	 * Get assets data (styles/scripts)
	 *
	 * @since  5.2.0
	 *
	 * @param  object $assets The global styles/scripts obejct.
	 *
	 * @return array  $data.   Array of styles/scripts data.
	 */
	private function get_assets_data( $assets ) {
		$excludes = array(
			'moxiejs',
			'elementor-frontend',
		);

		// Init the data array.
		$data = array(
			'header'       => array(),
			'default'      => array(),
			'non_minified' => array(),
		);

		// CLone the global assets object.
		$items = wp_clone( $assets );
		$items->all_deps( $items->queue );


		// Loop through all assets and push them to data array.
		foreach ( $items->to_do as $index => $handle ) {
			if (
				in_array( $handle, $excludes ) || // Do not include excluded assets.
				! is_bool( @strpos( $handle, 'siteground' ) ) ||
				! is_string( $items->registered[ $handle ]->src ) // Do not include asset without source.
			) {
				continue;
			}

			if ( 1 !== $items->groups[ $handle ] ) {
				$data['header'][] = $this->get_asset_data( $items->registered[ $handle ] );
			}

			if ( @strpos( $items->registered[ $handle ]->src, '.min.' ) === false ) {
				$data['non_minified'][] = $this->get_asset_data( $items->registered[ $handle ] );
			}

			$data['default'][] = $this->get_asset_data( $items->registered[ $handle ] );
		}

		// Finally return the assets data.
		return $data;
	}

	/**
	 * Get single asset data.
	 *
	 * @since  5.2.0
	 *
	 * @param  object $item The asset object.
	 *
	 * @return array        The asset data.
	 */
	public function get_asset_data( $item ) {
		// Strip the protocol from the src because some assets are loaded without protocol.
		$src = preg_replace( '~https?://~', '', Front_End_Optimization::remove_query_strings( $item->src ) );

		// Do regex match to the the plugin name and shorten src link.
		preg_match( '~wp-content(/(.*?)/(.*?)/.*)~', $src, $matches );

		// Push everything in the data array.
		$data = array(
			'value'       => $item->handle, // The handle.
			'title'       => ! empty( $matches[1] ) ? $matches[1] : $item->src, // The assets src.
			'group'       => ! empty( $matches[2] ) ? substr( $matches[2], 0, -1 ) : __( 'others', 'siteground-optimizer' ), // Get the group name.
			'name'        => ! empty( $matches[3] ) ? $this->get_plugin_info( $matches[3] ) : false, // The name of the parent( plugin or theme name ).
		);

		$data['group_title'] = empty( $data['name'] ) ? $data['group'] : $data['group'] . ': ' . $data['name'];

		return $data;
	}

	/**
	 * Get information about specific plugin.
	 *
	 * @since  5.2.0
	 *
	 * @param  string $path  Path to the plugin.
	 * @param  string $field The field we want to retrieve.
	 *
	 * @return string        The specific plugin field.
	 */
	private function get_plugin_info( $path, $field = 'name' ) {
		// Get active plugins.
		$active_plugins = get_option( 'active_plugins' );

		// Check if the path is presented in the active plugins.
		foreach ( $active_plugins as $plugin_file ) {
			if ( false === @strpos( $plugin_file, $path ) ) {
				continue;
			}

			// Get the plugin data from the main plugin file.
			$plugin = get_file_data( WP_PLUGIN_DIR . '/' . $plugin_file, array( $field => 'Plugin Name' ) );
		}

		// Return the date from plugin file.
		if ( ! empty( $plugin[ $field ] ) ) {
			return $plugin[ $field ];
		}

		// Otherwise return the path.
		return $path;
	}

	/**
	 * Check the size of the assets dir.
	 *
	 * @since  5.6.0
	 */
	public function check_assets_dir() {
		// Bail if the size is smaller that the limit.
		if ( self::LIMIT > $this->get_directory_size( $this->assets_dir ) ) {
			return;
		}

		Supercacher::delete_assets();
		Supercacher::purge_cache();
		Supercacher::flush_memcache();
		File_Cacher::purge_everything();
	}

	/**
	 * Return the total size of a directory in bytes.
	 *
	 * @since  5.6.0
	 *
	 * @param  string $directory The directory which size to calculate.
	 *
	 * @return int    $size The total size of the directory.
	 */
	public static function get_directory_size( $directory ) {
		// Init the size.
		$size = 0;

		// Bail if the directory doesn't exists.
		if ( ! file_exists( $directory ) ) {
			return false;
		}

		// Init the iterator.
		// We create this variable for code readability.
		// Otherwise the foreach below looks very ugly.
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$directory,
				\FilesystemIterator::SKIP_DOTS
			)
		);

		// Loop through all sub-directories and files
		// and calculate the size of the directory.
		foreach ( $iterator as $object ) {
			// Increase the `size` by adding the current object size.
			$size += $object->getSize();
		}

		// Finally return the total size of the directory.
		return $size;
	}

}
