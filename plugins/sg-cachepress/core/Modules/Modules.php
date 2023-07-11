<?php
namespace SiteGround_Optimizer\Modules;

use SiteGround_Optimizer\Multisite\Multisite;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Helper\Helper_Service;

/**
 * Provide list of SiteGround Optimizer modules.
 */
class Modules {
	/**
	 * The singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @var \Htaccess The singleton instance.
	 */
	private static $instance;

	/**
	 * List of all modules.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var array List of all optimizer modules.
	 */
	public $modules = array(
		'dynamic_cache'       => array(
			'title'   => 'Dynamic Cache',
			'text'    => 'Store your content in the server’s memory for a faster access with this full-page caching solution powered by NGINX.',
			'weight'  => 100,
			'tab'     => 'supercacher',
			'options' => array(
				'siteground_optimizer_enable_cache',
				'siteground_optimizer_autoflush_cache',
			),
		),
		'memcached'       => array(
			'title'   => 'Memcached',
			'text'    => 'Powerful object caching for your site. It stores frequently executed queries to your databases and reuses them for better performance.',
			'weight'  => 0,
			'tab'     => 'supercacher',
			'options' => array(
				'siteground_optimizer_enable_memcached',
			),
		),
		'ssl'             => array(
			'title'   => 'Enable HTTPS',
			'text'    => '',
			'tab'     => 'environment',
			'weight'  => 0,
			'options' => array(
				'siteground_optimizer_ssl_enabled',
			),
		),
		'hearbeat_control' => array(
			'title'   => 'WordPress Heartbeat Optimization',
			'text'    => 'Enable this option to allow SiteGround Optimizer to control the WordPress Heartbeat API.',
			'weight'  => 80,
			'tab'     => 'environment',
			'options' => array(
				'siteground_optimizer_heartbeat_control',
			),
		),
		'database_optimization'  => array(
			'title'   => 'Scheduled Database Maintenance',
			'text'    => 'Enable this option to regularly cleanup your database and keep it small and optimized.',
			'weight'  => 60,
			'tab'     => 'environment',
			'options' => array(
				'siteground_optimizer_database_optimization',
			),
		),
		'gzip'            => array(
			'title'   => 'GZIP Compression',
			'text'    => '',
			'weight'  => 0,
			'tab'     => 'environment',
			'options' => array(
				'siteground_optimizer_enable_gzip_compression',
			),
		),
		'browser_cache'   => array(
			'title'   => 'Browser Caching',
			'text'    => '',
			'weight'  => 0,
			'tab'     => 'environment',
			'options' => array(
				'siteground_optimizer_enable_browser_caching',
			),
		),
		'html'            => array(
			'title'   => 'HTML Minification',
			'text'    => 'Removes unnecessary characters from your HTML output saving data and improving your site speed.',
			'weight'  => 90,
			'tab'     => 'frontend',
			'options' => array(
				'siteground_optimizer_optimize_html',
			),
		),
		'javascript'      => array(
			'title'   => 'JavaScript Minification',
			'text'    => 'Minify your JavaScript files in order to reduce their size and reduce the number of requests to the server.',
			'weight'  => 80,
			'tab'     => 'frontend',
			'options' => array(
				'siteground_optimizer_optimize_javascript',
			),
		),
		'javascript_combination' => array(
			'title'   => 'JavaScript Combination',
			'text'    => 'Combine your JavaScript files in order to reduce the number of requests to the server.',
			'weight'  => 70,
			'tab'     => 'frontend',
			'options' => array(
				'siteground_optimizer_combine_javascript',
			),
		),
		'javascript_defer' => array(
			'title'   => 'Defer Render-blocking JS',
			'text'    => 'Defer loading of render-blocking JavaScript files for faster initial site load.',
			'weight'  => 60,
			'tab'     => 'frontend',
			'options' => array(
				'siteground_optimizer_optimize_javascript_async',
			),
		),
		'css'             => array(
			'title'   => 'Minify CSS Files',
			'text'    => 'Minify your CSS files in order to reduce their size and reduce the number of requests to the server.',
			'weight'  => 85,
			'tab'     => 'frontend',
			'options' => array(
				'siteground_optimizer_optimize_css',
			),
		),
		'css_combination'             => array(
			'title'   => 'Combine CSS Files',
			'text'    => 'Combine multiple CSS files into one to lower the number of requests your site generates.',
			'weight'  => 74,
			'tab'     => 'frontend',
			'options' => array(
				'siteground_optimizer_combine_css',
			),
		),
		'optimize_web_fonts'     => array(
			'title'   => 'Optimize Loading of Web Fonts',
			'text'    => 'Combine the loading of Google fonts reducing the number of HTTP requests and properly preload other fonts used by your theme and builder.',
			'weight'  => 87,
			'tab'     => 'frontend',
			'options' => array(
				'siteground_optimizer_optimize_web_fonts',
			),
		),
		'query_strings'   => array(
			'title'   => 'Query Strings Removal',
			'text'    => 'Removes version query strings from your static resources improving the caching of those resources.',
			'weight'  => 0,
			'tab'     => 'frontend',
			'options' => array(
				'siteground_optimizer_remove_query_strings',
			),
		),
		'emojis'          => array(
			'title'   => 'Emojis Removal',
			'text'    => 'Enable to prevent WordPress from automatically detecting and generating emojis in your pages.',
			'weight'  => 0,
			'tab'     => 'frontend',
			'options' => array(
				'siteground_optimizer_disable_emojis',
			),
		),
		'optimize_images' => array(
			'title'   => 'Images Optimization',
			'text'    => 'We will automatically optimize all new images that you upload to your Media Library.',
			'weight'  => 40,
			'tab'     => 'images',
			'options' => array(
				'siteground_optimizer_optimize_images',
			),
		),
		'webp_support' => array(
			'title'   => 'Generate WebP Copies of New Images',
			'text'    => 'WebP is a next generation image format supported by modern browers which greatly reduces the size of your images.',
			'weight'  => 75,
			'avalon'  => 1,
			'tab'     => 'images',
			'options' => array(
				'siteground_optimizer_webp_support',
			),
		),
		'lazyload_images' => array(
			'title'   => 'Lazy Load Media',
			'text'    => 'Load images only when they are visible in the browser.',
			'weight'  => 76,
			'tab'     => 'images',
			'options' => array(
				'siteground_optimizer_lazyload_images',
				'siteground_optimizer_lazyload_gravatars',
				'siteground_optimizer_lazyload_thumbnails',
				'siteground_optimizer_lazyload_responsive',
				'siteground_optimizer_lazyload_textwidgets',
				'siteground_optimizer_lazyload_iframes',
				'siteground_optimizer_lazyload_woocommerce',
				'siteground_optimizer_lazyload_videos',
			),
		),
	);

	/**
	 * List of all tabs.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var array List of all optimizer tabs.
	 */
	public $tabs = array(
		'supercacher' => array(
			'title'   => 'SuperCacher Settings',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'environment' => array(
			'title'   => 'Environment Optimization',
			'modules' => array(
				'ssl',
				'hearbeat_control',
				'database_optimization',
				'gzip',
				'browser_cache',
			),
		),
		'frontend'    => array(
			'title'   => 'Frontend Optimization',
			'modules' => array(
				'html',
				'javascript',
				'css',
				'query_strings',
				'emojis',
			),
		),
		'images'      => array(
			'title'   => 'Media Optimization',
			'modules' => array(
				'optimize_images',
				'lazyload_images',
			),
		),
		'analytics'   => array(
			'title' => 'Speed Test',
		),
	);

	/**
	 * List of multisite tabs.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var array List of multisite tabs.
	 */
	public $multisite_tabs = array(
		'global'     => 'Global Settings',
		'defaults'   => 'Per Site Defaults',
	);

	/**
	 * List of blocking plugins.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var array List of all blocking plugins.
	 */
	private $blocking_plugins = array(
		'swift-performance-lite/performance.php' => array(
			'title'   => 'Swift Performance Lite',
			'modules' => array(
				'gzip',
				'browser_cache',
				'html',
				'javascript',
				'css',
			),
		),
		'swift-performance/performance.php' => array(
			'title'   => 'Swift Performance',
			'modules' => array(
				'gzip',
				'browser_cache',
				'html',
				'javascript',
				'css',
			),
		),
		'wp-disable/wpperformance.php' => array(
			'title'   => 'WP Disable',
			'modules' => array(
				'query_strings',
				'emojis',
			),
		),
		'imsanity/imsanity.php' => array(
			'title'   => 'Imsanity',
			'modules' => array(
				'optimize_images',
			),
		),
		'ewww-image-optimizer/ewww-image-optimizer.php' => array(
			'title'   => 'EWWW Image Optimizer',
			'modules' => array(
				'optimize_images',
			),
		),
		'shortpixel-image-optimiser/wp-shortpixel.php'  => array(
			'title'   => 'ShortPixel Image Optimizer',
			'modules' => array(
				'optimize_images',
			),
		),
		'optimus/optimus.php' => array(
			'title'   => 'Optimus',
			'modules' => array(
				'optimize_images',
			),
		),
		'tiny-compress-images/tiny-compress-images.php' => array(
			'title'   => 'Tiny Compress Images',
			'modules' => array(
				'optimize_images',
			),
		),
		'a3-lazy-load/a3-lazy-load.php' => array(
			'title'   => 'A3 Lazy Load',
			'modules' => array(
				'lazyload_images',
			),
		),
		'bj-lazy-load/bj-lazy-load.php' => array(
			'title'   => 'BJ Lazy Load',
			'modules' => array(
				'lazyload_images',
			),
		),
		'wp-rocket/wp-rocket.php' => array(
			'title'   => 'WP Rocket',
			'modules' => array(
				'gzip',
				'browser_cache',
				'html',
				'javascript',
				'css',
			),
		),
		'autoptimize/autoptimize.php' => array(
			'title'   => 'Autoptimize',
			'modules' => array(
				'html',
				'javascript',
				'css',
			),
		),
		'imagify/imagify.php' => array(
			'title'   => 'Imagify',
			'modules' => array(
				'optimize_images',
			),
		),
		'rocket-lazy-load/rocket-lazy-load.php' => array(
			'title'   => 'Lazy Load by WP Rocket',
			'modules' => array(
				'lazyload_images',
			),
		),
	);

	/**
	 * List of cache plugins.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var array List of all cache plugins.
	 */
	private $cache_plugins = array(
		'simple-cache/simple-cache.php' => array(
			'title'   => 'Simple Cache',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'litespeed-cache/litespeed-cache.php' => array(
			'title'   => 'Lightspeed Cache',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'wp-super-cache/wp-cache.php' => array(
			'title'   => 'WP Super Cache',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'wp-fastest-cache/wpFastestCache.php' => array(
			'title'   => 'WP Fastest Cache',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'cache-enabler/cache-enabler.php' => array(
			'title'   => 'Cache Enabler',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'borlabs-cache-envato/borlabs-cache-envato.php'   => array(
			'title'   => 'Borlabs Cache',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'comet-cache/comet-cache.php' => array(
			'title'   => 'Comet Cache',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'one-click-ssl/ssl.php' => array(
			'title'   => 'One Click SSL',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'really-simple-ssl/rlrsssl-really-simple-ssl.php' => array(
			'title'   => 'Really Simple SSL',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'ssl-insecure-content-fixer/ssl-insecure-content-fixer.php' => array(
			'title'   => 'SSL Insecure Content Fixer',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'wordpress-https/wordpress-https.php' => array(
			'title'   => 'WordPress HTTPS (SSL)',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
		'wp-ssl-redirect/wp-ssl-redirect.php' => array(
			'title'   => 'WP SSL Redirect',
			'modules' => array(
				'dynamic_cache',
				'memcached',
			),
		),
	);

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @return \Supercacher The singleton instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Disable certain modules if there are conflicting plugins installed.
	 *
	 * @since  5.0.0
	 */
	public function disable_modules() {
		$excluded = $this->get_excluded( $this->blocking_plugins );

		foreach ( $excluded['excluded_modules'] as $module ) {
			$options = $this->modules[ $module ]['options'];

			array_map(
				function( $option ) {
					Options::disable_option( $option );
				}, $options
			);
		}

		delete_option( 'disable_conflicting_modules' );
	}

	/**
	 * Display notice for blocking plugins.
	 *
	 * @since  5.0.0
	 */
	public function blocking_plugins_notice() {

		if (
			0 === (int) get_site_option( 'siteground_optimizer_blocking_plugins_notice', 1 ) ||
			! current_user_can( 'administrator' ) ||
			( is_multisite() && ! is_network_admin() )
		) {
			return;
		}

		$excluded = $this->get_excluded( $this->blocking_plugins );

		// Bail if we don't have conflicted plugins.
		if ( empty( $excluded['conflicting_plugins'] ) ) {
			return;
		}

		$message = sprintf(
			__( '<strong>Important message from SiteGround Optimizer plugin</strong>: We have detected that there is duplicate functionality with other plugins installed on your site: <strong>%1$s</strong> and have deactivated the following functions from our plugin: <strong>%2$s</strong>. If you wish to enable them, please do that from the SiteGround Optimizer config page.', 'sg-cachepress' ),
			implode( ', ', $excluded['conflicting_plugins'] ),
			implode( ', ', $this->get_modules_pretty_names( $excluded['excluded_modules'] ) )
		);

		printf(
			'<div class="%1$s" style="position: relative"><p style="max-width: 90%%!important;">%2$s</p><button type="button" class="notice-dismiss dismiss-memcache-notice" data-link="%3$s"><span class="screen-reader-text">Dismiss this notice.</span></button></div>',
			esc_attr( 'notice notice-error sg-optimizer-notice' ),
			$message,
			admin_url( 'admin-ajax.php?action=dismiss_blocking_plugins_notice' )
		);
	}

	/**
	 * Display notice for cache plugins.
	 *
	 * @since  5.0.0
	 */
	public function cache_plugins_notice() {

		if (
			0 === (int) get_site_option( 'siteground_optimizer_cache_plugins_notice', 1 ) ||
			! current_user_can( 'administrator' ) ||
			( is_multisite() && ! is_network_admin() )
		) {
			return;
		}
		$excluded = $this->get_excluded( $this->cache_plugins );

		// Bail if we don't have conflicted plugins.
		if ( empty( $excluded['conflicting_plugins'] ) ) {
			return;
		}

		$message = sprintf(
			__( '<strong>Important warning from SiteGround Optimizer plugin</strong>: We have detected that there is duplicate functionality with other plugins installed on your site: <strong>%s</strong>. Please note that having two plugins with the same functionality may actually decrease your site\'s performance and hurt your pages loading times so we recommend you to leave only one of the plugins active.', 'sg-cachepress' ),
			implode( ', ', $excluded['conflicting_plugins'] )
		);

		printf(
			'<div class="%1$s" style="position: relative"><p style="max-width: 90%%!important;">%2$s</p><button type="button" class="notice-dismiss dismiss-memcache-notice" data-link="%3$s"><span class="screen-reader-text">Dismiss this notice.</span></button></div>',
			esc_attr( 'notice notice-error sg-optimizer-notice' ),
			$message,
			admin_url( 'admin-ajax.php?action=dismiss_cache_plugins_notice' )
		);
	}

	/**
	 * Return modules pretty names.
	 *
	 * @since  5.0.0
	 *
	 * @param  array $modules Excluded modules.
	 *
	 * @return array          Excluded module pretty names.
	 */
	private function get_modules_pretty_names( $modules ) {
		$excluded_modules = array_intersect_key( $this->modules, array_flip( $modules ) );
		$module_names     = array();

		foreach ( $excluded_modules as $module ) {
			$module_names[] = $module['title'];
		}

		return $module_names;
	}

	/**
	 * Return list of modules that should be excluded.
	 *
	 * @since  5.0.0
	 *
	 * @param  array $plugins Conflicting plugins.
	 *
	 * @return array          List of all excluded modules.
	 */
	public function get_excluded( $plugins ) {
		$excluded_modules    = array();
		$conflicting_plugins = array();

		// Get all active plugins.
		$active_plugins = get_option( 'active_plugins', array() );

		// Add network plugins to active plugins list.
		if ( is_multisite() ) {
			$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins  = array_merge(
				$active_plugins,
				array_flip( $network_plugins )
			);
		}

		foreach ( $active_plugins as $plugin ) {
			// Continue if it's not conflicting plugin.
			if ( ! array_key_exists( $plugin, $plugins ) ) {
				continue;
			}

			// Update excluded modules.
			$excluded_modules = array_merge(
				$excluded_modules,
				$plugins[ $plugin ]['modules']
			);

			$conflicting_plugins[] = $plugins[ $plugin ]['title'];
		}

		return array(
			'conflicting_plugins' => array_unique( $conflicting_plugins ),
			'excluded_modules'    => array_unique( $excluded_modules ),
		);
	}

	/**
	 * Return the modules that should be active.
	 *
	 * @since  5.0.0
	 *
	 * @return array Array of all modules that should be activated.
	 */
	public function get_active_modules() {
		if ( ! is_multisite() ) {
			return array_keys( $this->modules );
		}

		return $this->apply_mu_disabled_modules( array_keys( $this->modules ) );
	}

	/**
	 * Return the tabs that should be active.
	 *
	 * @since  5.0.0
	 *
	 * @param array $active_modules List of all active modules.
	 *
	 * @return array Array of all tabs that should be activated.
	 */
	public function get_active_tabs( $active_modules = array() ) {
		// Get active modules if they are not defined.
		if ( empty( $active_modules ) ) {
			$active_modules = $this->get_active_modules();
		}

		// Build tabs data.
		foreach ( $this->tabs as $tab_slug => $tab ) {
			$active_tabs[ $tab_slug ] = __( $tab['title'], 'sg-cachepress' );
		}

		// Return the tabs.
		if ( ! is_multisite() ) {
			// Return the tabs.
			return $active_tabs;
		}

		// Network admins have their own tabs.
		if ( is_network_admin() ) {
			return $this->multisite_tabs;
		}

		// Return active tabs for multisite.
		return array_intersect_key(
			$active_tabs, // Active tabs.
			array_filter( Multisite::get_permissions() ) // Get multisite permissions.
		);
	}

	/**
	 * Modify active modules for multisite.
	 *
	 * @since  5.0.0
	 *
	 * @param  array $active_modules All active modules.
	 *
	 * @return array                 Modified active modules.
	 */
	public function apply_mu_disabled_modules( $active_modules ) {
		// Disabled modules for multisite.
		$disabled_modules = array(
			'single_site'   => array(
				'memcached',
				'phpchecker',
				'gzip',
				'browser_cache',
			),
			'network_admin' => array(
				'ssl',
			),
		);

		// Disable single site modules.
		if ( ! is_network_admin() ) {
			return array_diff( $active_modules, $disabled_modules['single_site'] );
		}

		// Disable network admin modules.
		return array_diff( $active_modules, $disabled_modules['network_admin'] );
	}

	/**
	 * Get modules for the slider on plugin page.
	 *
	 * @since  5.5
	 *
	 * @return array Array of modules.
	 */
	public function get_slider_modules() {
		$modules   = array();
		$whats_new = array();
		// Get the new modules.
		$new_modules = get_option( 'siteground_optimizer_whats_new', array() );

		// Add the new modules to the response if the optimization is not enabled.
		if ( ! empty( $new_modules ) ) {
			foreach ( $new_modules as $index => $card ) {
				if ( Options::is_enabled( 'siteground_optimizer_' . $card['optimization'] ) ) {
					continue;
				}

				$modules[]   = $card;
				$whats_new[] = $index;
			}
		}

		// Merge and remove the empty cards.
		$cards = array_filter(
			array_merge(
				$modules, // Add "What's new" modules.
				array_merge(
					array(
						$this->get_optimizations(), // Add optimizations that are not yet enabled to the response.
					),
					// Add the default card.
					array(
						array(
							'type'       => 'default',
							'title'      => __( 'Welcome to SiteGround Optimizer', 'sg-cachepress' ),
							'text'       => __( 'Get the best performance for your WordPress website with our optimization plugin. It handles caching, system settings, and all the necessary configurations for a blazing-fast website. With the SiteGround Optimizer enabled, you’re getting the very best from your hosting environment!', 'sg-cachepress' ),
							'icon'       => 'presentational-speed-caching',
							'icon_color' => 'salmon',
						),
					)
				)
			)
		);

		// Finally return the response.
		return array(
			'whats_new' => $whats_new,
			'cards'     => $cards,
		);
	}

	/**
	 * Get optimizations which are currently disabled.
	 *
	 * @since  5.5.
	 *
	 * @return array Array of possible optimizations.
	 */
	public function get_optimizations() {
		$optimizations = array();
		$count         = 3;
		$is_siteground = Helper_Service::is_siteground();

		// Order the modules.
		$keys = array_column( $this->modules, 'weight' );
		array_multisort( $keys, SORT_DESC, $this->modules );

		foreach ( $this->modules as $module ) {
			// Bail if there are no optimizations.
			if ( empty( $module['options'][0] ) ) {
				continue;
			}

			// Bail if the optimization is alredy enabled.
			if ( Options::is_enabled( $module['options'][0] ) ) {
				continue;
			}

			// Bail if the optimization is not important.
			if ( 0 == $module['weight'] ) {
				continue;
			}

			// Or if the optimization is for avalon servers only.
			if ( ! $is_siteground && ! empty( $module['avalon'] ) ) {
				continue;
			}

			// Add the optimization to the array.
			$optimizations[] = array(
				'title'        => $module['title'],
				'text'         => $module['text'],
				'optimization' => str_replace( 'siteground_optimizer_', '', $module['options'][0] ),
				'link'         => $module['tab'],
			);

			// Return the optimizations if we've reached the required quantity.
			if ( count( $optimizations ) == $count ) {
				return array(
					'type'  => 'optimizations',
					'boxes' => $optimizations,
				);
			}
		}

		return array();
	}


}
