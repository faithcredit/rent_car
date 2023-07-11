<?php
namespace SiteGround_Optimizer\Rest;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Message_Service\Message_Service;
use SiteGround_Optimizer\Multisite\Multisite;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Analysis\Analysis;
use SiteGround_Optimizer\Rest\Rest;
use SiteGround_Optimizer\Images_Optimizer\Images_Optimizer;
use SiteGround_Optimizer\Heartbeat_Control\Heartbeat_Control;
use SiteGround_Helper\Helper_Service;
use SiteGround_Optimizer\File_Cacher\File_Cacher;

/**
 * Rest Helper class that manages all of the front end optimisation.
 */
class Rest_Helper_Options extends Rest_Helper {
	/**
	 * Local variables.
	 *
	 * @var mixed
	 */
	public $options;
	public $multisite;
	public $htaccess_service;
	public $analysis;
	public $images_optimizer;
	public $heartbeat_control;

	/**
	 * The options map.
	 *
	 * @var array
	 */
	public $options_map = array(
		'caching'     => array(
			'enable_cache',
			'file_caching',
			'preheat_cache',
			'logged_in_cache',
			'enable_memcached',
			'autoflush_cache',
			'user_agent_header',
			'purge_rest_cache',
			'logged_in_cache',
		),
		'environment' => array(
			'ssl_enabled',
			'fix_insecure_content',
			'enable_gzip_compression',
			'enable_browser_caching',
		),
		'frontend'    => array(
			'optimize_css',
			'combine_css',
			'preload_combined_css',
			'optimize_javascript',
			'combine_javascript',
			'optimize_javascript_async',
			'optimize_html',
			'optimize_web_fonts',
			'remove_query_strings',
			'disable_emojis',
		),
		'media'       => array(
			'lazyload_images',
			'webp_support',
			'backup_media',
			'compression_level',
		),
	);

	/**
	 * The options prefix.
	 *
	 * @var string
	 */
	public $option_prefix = 'siteground_optimizer_';

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->options           = new Options();
		$this->multisite         = new Multisite();
		$this->htaccess_service  = new Htaccess();
		$this->analysis          = new Analysis();
		$this->images_optimizer  = new Images_Optimizer();
		$this->heartbeat_control = new Heartbeat_Control();
	}

	/**
	 * Enable option from rest.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function enable_option_from_rest( $request ) {
		// Get the option key.
		$key        = $this->validate_and_get_option_value( $request, 'option_key' );
		$is_network = $this->validate_and_get_option_value( $request, 'is_multisite', false );
		$result     = $this->options->enable_option( $key, $is_network );

		// Bail if .htaccess can't be changed.
		if ( false === $this->maybe_change_htaccess_rules( $key, 1 ) ) {
			// Revert enabling the option.
			$result = $this->options->disable_option( $key, $is_network );
			self::send_json_error(
				self::get_response_message( false, str_replace( 'siteground_optimizer_default_', '', $key ), 1 ),
				array(
					$key => get_option( 'siteground_optimizer_' . $key, 0 ),
				)
			);
		}

		// Enable the option.
		wp_send_json(
			array(
				'success' => $result,
				'data'    => array(
					'message' => self::get_response_message( $result, str_replace( 'siteground_optimizer_default_', '', $key ), 1 ),
				),
			)
		);
	}

	/**
	 * Disable option from rest.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 *
	 * @return string The option key.
	 */
	public function disable_option_from_rest( $request ) {
		// Get the option key.
		$key        = $this->validate_and_get_option_value( $request, 'option_key' );
		$is_network = $this->validate_and_get_option_value( $request, 'is_multisite', false );
		$result     = $this->options->disable_option( $key, $is_network );

		// Bail if .htaccess can't be changed.
		if ( false === $this->maybe_change_htaccess_rules( $key, 0 ) ) {
			// Revert disabling the option.
			$this->options->enable_option( $key, $is_network );
			self::send_json_error(
				self::get_response_message( false, str_replace( 'siteground_optimizer_default_', '', $key ), 0 ),
				array(
					$key => get_option( 'siteground_optimizer_' . $key, 1 ),
				)
			);
		}

		// Disable the option.
		return wp_send_json(
			array(
				'success' => $result,
				'data'    => array(
					'message' => self::get_response_message( $result, str_replace( 'siteground_optimizer_default_', '', $key ), 0 ),
				),
			)
		);
	}

	/**
	 * Manage the preload combined css method.
	 *
	 * @since  6.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function preload_combined_css( $request ) {
		// Validate rest request and prepare data.
		$data = $this->validate_rest_request( $request, array( 'preload_combined_css' ) );

		// On Disable - disable all sub settings as well.
		if ( 0 === $data['value'] ) {
			Options::disable_option( 'siteground_optimizer_preload_combined_css' );
			// Send the response.
			self::send_json_success(
				self::get_response_message( true, 'preload_combined_css', 0 ),
				array(
					'preload_combined_css' => 0,
				)
			);
		}

		Options::enable_option( 'siteground_optimizer_preload_combined_css' );
		Options::enable_option( 'siteground_optimizer_combine_css' );

		// Send the response.
		self::send_json_success(
			self::get_response_message( true, 'preload_combined_css', 1 ),
			array(
				'preload_combined_css' => 1,
				'combine_css'          => 1,
			)
		);
	}

	/**
	 * Manage the combined css option.
	 *
	 * @since  6.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function manage_combine_css( $request ) {
		// Validate rest request and prepare data.
		$data = $this->validate_rest_request( $request, array( 'combine_css' ) );

		// On Disable - disable all sub settings as well.
		if ( 0 === $data['value'] ) {
			// Disable the option.
			Options::disable_option( 'siteground_optimizer_combine_css' );
			// Disable the related option.
			Options::disable_option( 'siteground_optimizer_preload_combined_css' );

			// Send the response.
			self::send_json_success(
				self::get_response_message( true, 'combine_css', 0 ),
				array(
					'combine_css'          => 0,
					'preload_combined_css' => 0,
				)
			);
		}

		Options::enable_option( 'siteground_optimizer_combine_css' );

		// Send the response.
		self::send_json_success(
			self::get_response_message( true, 'combine_css', 1 ),
			array(
				'combine_css' => 1,
			)
		);
	}
	/**
	 * Check if which method we should initiate.
	 *
	 * @since  6.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function manage_request( $request ) {
		// Validate the request and prepare data.
		$data = $this->validate_rest_request( $request );

		// Bail if the htaccess file cannot be modified.
		if ( false === $this->maybe_change_htaccess_rules( $data['option'], $data['value'] ) ) {
			// Send the response.
			self::send_json_error(
				self::get_response_message( false, str_replace( 'siteground_optimizer_', '', $data['option'] ), $data['value'] ),
				array(
					$data['option'] => get_option( $data['option'], 0 ),
				)
			);
		}

		// Change the option.
		$result = $this->options->change_option( $data['option'], $data['value'] );

		$new_value = true === $result ? $data['value'] : ! $data['value'];

		// Send the response.
		self::send_json_response(
			$result,
			self::get_response_message( $result, $data['key'], $data['value'] ),
			array(
				$data['key'] => intval( $new_value ),
			)
		);
	}

	/**
	 * Provide all plugin options.
	 *
	 * @since  5.0.0
	 *
	 * @param  Object $request The Request Object.
	 */
	public function fetch_options( $request ) {
		// Get the parameters.
		$params = $request->get_params( $request );

		// Check for page id.
		if ( empty( $params['page_id'] ) ) {
			self::send_json(
				__( 'Missing ID param!', 'sg-cachepress' ),
				0
			);
		}

		switch ( $params['page_id'] ) {
			case 'caching':

				// Options requiring additional action.
				$page_data = array(
					'post_types_exclude' => array(
						'default'  => $this->options->get_post_types(),
						'selected' => get_option( $this->option_prefix . 'post_types_exclude', array() ),
					),
					'excluded_urls' => array(
						'default'  => array(),
						'selected' => get_option( $this->option_prefix . 'excluded_urls', array() ),
					),
					'file_caching_interval_cleanup' => File_Cacher::get_instance()->get_intervals(),
				);
				break;
			case 'environment':
				$page_data = array(
					'heartbeat_dropdowns'        => $this->heartbeat_control->prepare_intervals(),
					'heartbeat_control'          => $this->heartbeat_control->is_enabled(),
					'database_optimization'      => array(
						'default'  => $this->options->get_database_optimization_defaults(),
						'selected' => array_values( get_option( $this->option_prefix . 'database_optimization', array() ) ),
					)
				);
				break;
			case 'frontend':
				$assets = Front_End_Optimization::get_instance()->get_assets();

				// Options requiring additional action.
				$page_data = array(
					'minify_html_exclude' => array(
						'default'  => array(),
						'selected' => array_values( get_option( $this->option_prefix . 'minify_html_exclude', array() ) ),
					),
					'fonts_preload_urls'  => array(
						'default'  => array(),
						'selected' => array_values( get_option( $this->option_prefix . 'fonts_preload_urls', array() ) ),
					),
					'minify_css_exclude' => array(
						'default'  => $assets['styles']['non_minified'],
						'selected' => array_values( get_option( $this->option_prefix . 'minify_css_exclude', array() ) ),
					),
					'combine_css_exclude' => array(
						'default'  => $assets['styles']['default'],
						'selected' => array_values( get_option( $this->option_prefix . 'combine_css_exclude', array() ) ),
					),
					'minify_javascript_exclude' => array(
						'default'  => $assets['scripts']['non_minified'],
						'selected' => array_values( get_option( $this->option_prefix . 'minify_javascript_exclude', array() ) ),
					),
					'combine_javascript_exclude' => array(
						'default'  => $assets['scripts']['default'],
						'selected' => array_values( get_option( $this->option_prefix . 'combine_javascript_exclude', array() ) ),
					),
					'async_javascript_exclude' => array(
						'default'  => $assets['scripts']['default'],
						'selected' => array_values( get_option( $this->option_prefix . 'async_javascript_exclude', array() ) ),
					),
					'dns_prefetch_urls' => array(
						'default'  => array(),
						'selected' => get_option( $this->option_prefix . 'dns_prefetch_urls', array() ),
					),
				);

				break;
			case 'media':
				// Options requiring additional action.
				$page_data = array(
					'excluded_lazy_load_classes'     => array(
						'default'  => array(),
						'selected' => array_values( get_option( $this->option_prefix . 'excluded_lazy_load_classes', array() ) ),
					),
					'excluded_lazy_load_media_types' => array(
						'default'  => $this->options->get_excluded_lazy_load_media_types(),
						'selected' => array_values( get_option( $this->option_prefix . 'excluded_lazy_load_media_types', array() ) ),
					),
					'image_resize'                  => $this->images_optimizer->prepare_max_width_sizes(),
				);
				break;
			case 'analysis':
				$page_data = $this->analysis->rest_get_test_results();
				break;
		}

		if ( array_key_exists( $params['page_id'], $this->recommended_optimizations ) ) {
			$page_data['recommended'] = $this->recommended_optimizations[ $params['page_id'] ];
		}

		// Send the options to react app.
		self::send_json_success( '', array_merge( $this->prepare_options( $params['page_id'] ), $page_data ) );
	}

	/**
	 * Provide all plugin options.
	 *
	 * @since  5.0.0
	 */
	public function fetch_options_old() {
		// Fetch the options.
		$options = $this->options->fetch_options();

		if ( is_multisite() ) {
			$options['sites_data'] = $this->multisite->get_sites_info();
		}
		$options['has_images']                  = $this->options->check_for_images();
		$options['has_images_for_optimization'] = $this->options->check_for_unoptimized_images( 'image' );
		$options['assets']                      = Front_End_Optimization::get_instance()->get_assets();
		$options['quality_type']                = get_option( 'siteground_optimizer_quality_type', '' );
		$options['post_types']                  = $this->options->get_post_types();
		$options['previous_tests']              = $this->analysis->rest_get_test_results();

		// Check for non converted images when we are on avalon server.
		if ( Helper_Service::is_siteground() ) {
			$options['has_images_for_conversion'] = $this->options->check_for_unoptimized_images( 'webp' );
		}

		// Send the options to react app.
		wp_send_json_success( $options );
	}


	/**
	 * Prepare the options based on them being 1/0 or a complex option which result is array.
	 *
	 * @since  6.0.0
	 *
	 * @param  string $page The page for which we are preparing the options.
	 *
	 * @return array  $data The prepared options.
	 */
	public function prepare_options( $page ) {
		// Prepare the array.
		$data = array();

		// Bail if the page doesn't exist.
		if ( ! array_key_exists( $page, $this->options_map ) ) {
			return $data;
		}

		// Loop trough page specific options.
		foreach ( $this->options_map[ $page ] as $option ) {
			// Loop the options groups.
			$data[ $option ] = intval( get_option( $this->option_prefix . $option, 0 ) );
		}

		return $data;
	}

	/**
	 * Check if we should add additional rules to the htaccess file.
	 *
	 * @since  5.7.14
	 *
	 * @param  string $type  The optimization type.
	 * @param  int    $value The optimization value.
	 */
	public function maybe_change_htaccess_rules( $type, $value ) {
		// Options mapping with the htaccess rules and methods.
		$htaccess_options = array(
			'siteground_optimizer_enable_gzip_compression' => array(
				0      => 'disable',
				1      => 'enable',
				'rule' => 'gzip',
			),
			'siteground_optimizer_enable_browser_caching'  => array(
				0      => 'disable',
				1      => 'enable',
				'rule' => 'browser-caching',
			),
		);

		// Bail if the option doesn't require additional htaccess rules to be added.
		if ( ! array_key_exists( $type, $htaccess_options ) ) {
			return;
		}

		// Call the htaccess method to add/remove the rules.
		return call_user_func_array(
			array( $this->htaccess_service, $htaccess_options[ $type ][ $value ] ),
			array( $htaccess_options[ $type ]['rule'] )
		);
	}
}
