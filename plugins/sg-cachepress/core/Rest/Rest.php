<?php
namespace SiteGround_Optimizer\Rest;

use SiteGround_Optimizer\Helper\Factory_Trait;

/**
 * Handle PHP compatibility checks.
 */
class Rest {
	use Factory_Trait;

	const REST_NAMESPACE = 'siteground-optimizer/v1';

	/**
	 * Local Variables.
	 *
	 * @var mixed
	 */
	public $rest_helper_webp;
	public $rest_helper_options;
	public $rest_helper_cache;
	public $rest_helper_multisite;
	public $rest_helper_misc;
	public $rest_helper_images;
	public $rest_helper_environment;
	public $rest_helper_dashboard;

	/**
	 * Dependencies.
	 *
	 * @since 5.9.0
	 *
	 * @var array
	 */
	public $dependencies = array(
		'webp'        => 'rest_helper_webp',
		'options'     => 'rest_helper_options',
		'cache'       => 'rest_helper_cache',
		'multisite'   => 'rest_helper_multisite',
		'misc'        => 'rest_helper_misc',
		'images'      => 'rest_helper_images',
		'environment' => 'rest_helper_environment',
		'dashboard'   => 'rest_helper_dashboard',
	);

	/**
	 * All toggle options array used to create the rest routes.
	 *
	 * @since 5.9.0
	 *
	 * @var array
	 */
	public static $toggle_options = array(
		// Cache.
		'purge_rest_cache',
		'logged_in_cache',
		// Environment.
		'enable_gzip_compression',
		'enable_browser_caching',
		// Frontend Opitmizations.
		'optimize_css',
		'optimize_javascript',
		'combine_javascript',
		'optimize_javascript_async',
		'optimize_html',
		'optimize_web_fonts',
		'remove_query_strings',
		'disable_emojis',
		// Media Optimization.
		'lazyload_images',
		'backup_media',
	);

	/**
	 * All exclude options array used to create the rest routes.
	 *
	 * @since 6.0.0
	 *
	 * @var array
	 */
	public static $exclude_options = array(
		// Cache.
		'excluded_urls',
		'post_types_exclude',
		// Environment.
		'dns_prefetch_urls',
		// Frontend.
		'minify_css_exclude',
		'combine_css_exclude',
		'minify_javascript_exclude',
		'combine_javascript_exclude',
		'async_javascript_exclude',
		'minify_html_exclude',
		'fonts_preload_urls',
		// Media.
		'excluded_lazy_load_classes',
		'excluded_lazy_load_media_types',
	);

	/**
	 * All popups endpoints.
	 *
	 * @since 7.0.0
	 *
	 * @var array
	 */
	public static $popups = array(
		'memcache'            => 'Memcached',
		'dynamic-cache'       => 'Dynamic Caching',
		'webp-support'        => 'WebP Optimiztion',
		'images'              => 'Images Optimization',
		'optimize-javascript' => 'JavaScript Minification',
		'optimize-css'        => 'CSS Minification',
	);

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->load_dependencies();
	}

	/**
	 * Load the main plugin dependencies.
	 *
	 * @since  5.9.0
	 */
	public function load_dependencies() {
		foreach ( $this->dependencies as $dependency => $classes ) {
			$this->factory( 'rest', $classes );
		}
	}

	/**
	 * Check if a given request has admin access
	 *
	 * @since  5.0.13
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function check_permissions( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Register rest routes.
	 *
	 * @since  5.0.0
	 */
	public function register_rest_routes() {
		foreach ( $this->dependencies as $dependency => $classes ) {
			call_user_func( array( $this, 'register_' . $dependency . '_rest_routes' ) );
		}
	}

	/**
	 * Register php and ssl rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_environment_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/ssl/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_environment, 'ssl' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/fix-insecure-content/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_environment, 'fix_insecure_content' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/database-optimization/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_environment, 'manage_database_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/heartbeat/(?P<location>[^/]+)', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_environment, 'manage_heartbeat_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'location' => array(
						'validate_callback' => function( $param ) {

							return in_array(
								$param,
								array(
									'dashboard',
									'post',
									'frontend',
								)
							);
						},
					),
				),
			)
		);
	}

	/**
	 * Register options rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_options_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/fetch-options/', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_options, 'fetch_options_old' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/enable-option/', array(
				'methods'  => 'POST',
				'callback' => array( $this->rest_helper_options, 'enable_option_from_rest' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/disable-option/', array(
				'methods'  => 'POST',
				'callback' => array( $this->rest_helper_options, 'disable_option_from_rest' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/combine-css/', array(
				'methods'  => 'PUT',
				'callback' => array( $this->rest_helper_options, 'manage_combine_css' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/preload-combined-css/', array(
				'methods'  => 'PUT',
				'callback' => array( $this->rest_helper_options, 'preload_combined_css' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);


		register_rest_route(
			self::REST_NAMESPACE, '/fetch-options/(?P<page_id>[^/]+)', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_options, 'fetch_options' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'page_id' => array(
						'validate_callback' => function( $param, $request, $key ) {
							$page_ids = array(
								'dashboard',
								'caching',
								'environment',
								'frontend',
								'media',
								'analysis',
							);

							return in_array( $param, $page_ids );
						},
					),
				),
			)
		);

		foreach ( self::$toggle_options as $route ) {
			register_rest_route(
				self::REST_NAMESPACE, '/' . str_replace( '_', '-', $route ) . '/', array(
					'methods'             => 'PUT',
					'callback'            => array( $this->rest_helper_options, 'manage_request' ),
					'permission_callback' => array( $this, 'check_permissions' ),
				)
			);
		}
	}

	/**
	 * Register cache rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_cache_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/enable-cache/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_cache, 'manage_dynamic_cache' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/memcached/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_cache, 'manage_memcache' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/autoflush-cache/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_cache, 'manage_automatic_purge' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/purge-cache/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_cache, 'purge_cache_from_rest' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/user-agent-header/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_cache, 'manage_user_agent_header' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/test-url-cache/', array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_helper_cache, 'test_cache' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/enable-memcache/', array(
				'methods'  => 'GET',
				'callback' => array( $this->rest_helper_cache, 'enable_memcache' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/disable-memcache/', array(
				'methods'  => 'GET',
				'callback' => array( $this->rest_helper_cache, 'disable_memcache' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/file-caching', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_cache, 'manage_file_caching' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/file-caching-settings', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_cache, 'manage_file_caching_settings' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register the rest routes for images optimization.
	 *
	 * @since  5.4.0
	 */
	public function register_images_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/optimize-images/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_images, 'manage_image_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/optimize-images/', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_images, 'check_image_optimizing_status' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/reset-images-optimization/', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_images, 'reset_images_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/preview-image(?:/(?P<id>\d+))?', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_images, 'get_preview_images' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array( array( 'id' ) ),
			)
		);
		register_rest_route(
			self::REST_NAMESPACE, '/image-resize', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_images, 'manage_resize_images' ),
				'permission_callback' => array( $this, 'check_permissions'),
			)
		);
	}

	/**
	 * Register the rest routes for webp conversion.
	 *
	 * @since  5.4.0
	 */
	public function register_webp_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/webp-support/', array(
				'methods'             => 'PUT',
				'callback'            => array( $this->rest_helper_webp, 'optimize_webp_images' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/webp-support/', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_webp, 'check_webp_conversion_status' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/reset-webp-conversion/', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_webp, 'reset_webp_conversion' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register multisite rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_multisite_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/enable-multisite-optimization/', array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_helper_multisite, 'enable_multisite_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/disable-multisite-optimization/', array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_helper_multisite, 'disable_multisite_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register misc rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_misc_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/run-analysis/', array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_helper_misc, 'run_analysis' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		foreach ( self::$exclude_options as $route ) {
			register_rest_route(
				self::REST_NAMESPACE, '/exclude/(?P<type>[^/]+)', array(
					'methods'             => 'PUT',
					'callback'            => array( $this->rest_helper_misc, 'manage_excludes' ),
					'permission_callback' => array( $this, 'check_permissions' ),
					'args'                => array(
						'type' => array(
							'validate_callback' => function( $param, $request, $key ) {
								return in_array( $param, str_replace( '_', '-', self::$exclude_options ) );
							},
						),
					),
				)
			);
		}

		register_rest_route(
			self::REST_NAMESPACE, '/feature-popup/(?P<type>[^/]+)', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_misc, 'feature_popup' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'type' => array(
						'validate_callback' => function( $param, $request, $key ) {
							return array_key_exists( $param, str_replace( '_', '-', self::$popups ) );
						},
					),
					'page_id' => array(
						'validate_callback' => function( $param, $request, $key ) {

							$popup_type = array(
								'memcache',
								'dynamic-cache',
								'images',
							);

							return array_key_exists( $param, $popup_type );
						},
					),
				),
			)
		);
	}

	/**
	 * Register Dashboard routes.
	 *
	 * @since  6.0.0
	 */
	public function register_dashboard_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/notifications/', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_dashboard, 'notifications' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/hardening/', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_dashboard, 'hardening' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/e-book/', array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_helper_dashboard, 'ebook' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/rate/', array(
				'methods'             => array( 'PUT' ),
				'callback'            => array( $this->rest_helper_dashboard, 'rate' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/rate/', array(
				'methods'             => array( 'GET' ),
				'callback'            => array( $this->rest_helper_dashboard, 'rate_get' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}
}
