<?php
namespace SiteGround_Optimizer\Minifier;

use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Helper\Helper_Service;
use MatthiasMullie\Minify;

/**
 * SG Minifier main plugin class
 */
class Minifier {
	/**
	 * WordPress filesystem.
	 *
	 * @since 5.0.0
	 *
	 * @var object|null WordPress filesystem.
	 */
	private $wp_filesystem = null;

	/**
	 * The dir where the minified styles and scripts will be saved.
	 *
	 * @since 5.0.0
	 *
	 * @var string|null Path to assets dir.
	 */
	private $assets_dir = null;

	/**
	 * Javascript files that should be ignored.
	 *
	 * @since 5.0.0
	 *
	 * @var array Array of all js files that should be ignored.
	 */
	private $js_ignore_list = array(
		'jquery',
		'jquery-core',
		'ai1ec_requirejs',
	);

	/**
	 * Stylesheet files that should be ignored.
	 *
	 * @since 5.0.0
	 *
	 * @var array Array of all css files that should be ignored.
	 */
	private $css_ignore_list = array(
		'uag-style',
	);

	/**
	 * The singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @var \Minifier The singleton instance.
	 */
	private static $instance;

	/**
	 * Exclude params.
	 *
	 * @since 5.4.6
	 *
	 * @var array Array of all exclude params.
	 */
	private $exclude_params = array(
		'pdf-catalog',
		'tve',
		'elementor-preview',
		'preview',
		'wc-api',
		'method',
	);

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		// Bail if it's admin page.
		if ( is_admin() ) {
			return;
		}
		// Setup wp filesystem.
		if ( null === $this->wp_filesystem ) {
			$this->wp_filesystem = Helper_Service::setup_wp_filesystem();
		}

		$this->assets_dir = Front_End_Optimization::get_instance()->assets_dir;

		self::$instance = $this;

		$this->js_ignore_list = array_merge(
			$this->js_ignore_list,
			get_option( 'siteground_optimizer_minify_javascript_exclude', array() )
		);

		$this->css_ignore_list = array_merge(
			$this->css_ignore_list,
			get_option( 'siteground_optimizer_minify_css_exclude', array() )
		);
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.1.0
	 *
	 * @return \Minifier The singleton instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			static::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Minify scripts included in footer and header.
	 *
	 * @since  5.0.0
	 */
	public function minify_scripts() {
		global $wp_scripts;

		// Bail if the scripts object is empty.
		if (
			! is_object( $wp_scripts ) ||
			null === $this->assets_dir ||
			$this->has_exclude_param()
		) {
			return;
		}

		$scripts = wp_clone( $wp_scripts );
		$scripts->all_deps( $scripts->queue );

		$excluded_scripts = apply_filters( 'sgo_js_minify_exclude', $this->js_ignore_list );

		// Get groups of handles.
		foreach ( $scripts->to_do as $handle ) {
			// Skip scripts.
			if (
				stripos( $wp_scripts->registered[ $handle ]->src, '.min.js' ) !== false || // If the file is minified already.
				false === $wp_scripts->registered[ $handle ]->src || // If the source is empty.
				in_array( $handle, $excluded_scripts ) || // If the file is ignored.
				@strpos( Helper_Service::get_home_url(), parse_url( $wp_scripts->registered[ $handle ]->src, PHP_URL_HOST ) ) === false // Skip all external sources.
			) {
				continue;
			}

			$original_filepath = Front_End_Optimization::get_original_filepath( $wp_scripts->registered[ $handle ]->src );

			// Build the minified version filename.
			$filename = $this->assets_dir . $wp_scripts->registered[ $handle ]->handle . '.min.js';

			// Check if the handle of the script has forward slashes in it, if so - replace them with dashes.
			if ( false !== strpos( $wp_scripts->registered[ $handle ]->handle, '/' ) ) {
				$filename = dirname( $original_filepath ) . '/' . str_replace( '/', '-', $wp_scripts->registered[ $handle ]->handle ) . '.min.css';
			}

			// Check for original file modifications and create the minified copy.
			$is_minified_file_ok = $this->check_and_create_file( $filename, $original_filepath );

			// Check that everythign with minified file is ok.
			if ( $is_minified_file_ok ) {
				// Replace the script src with the minified version.
				$wp_scripts->registered[ $handle ]->src = str_replace( ABSPATH, Helper_Service::get_site_url(), $filename );
			}
		}
	}

	/**
	 * Use the MatthiasMullie library for a minification fallback
	 *
	 * @since  6.0.4
	 *
	 * @param  string $minifier_type     The type of minifier we are using.
	 * @param  string $filename          The file we are creating.
	 * @param  string $original_filepath The original filepath.
	 *
	 * @return bool true/false If the minification was successful.
	 */
	public function minify_scripts_lib( $minifier_type, $filename, $original_filepath ) {
		// Prepare the corect minifier.
		switch ( $minifier_type ) {
			case 'JS':
				$minifier = new Minify\JS( $original_filepath );
				break;
			case 'CSS':
				$minifier = new Minify\CSS( $original_filepath );
				break;
		}

		// Bail if minification fails.
		// The method will return string and write the new file.
		if ( empty( $minifier->minify( $filename ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the original file is modified and create minified version.
	 * It will create minified version if the new file doesn't exists.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $new_file_path     The new filename.
	 * @param  string $original_filepath The original file.
	 *
	 * @return bool             True if the file is created, false on failure.
	 */
	private function check_and_create_file( $new_file_path, $original_filepath ) {
		// Bail if the original file doesn't exists.
		if ( ! is_file( $original_filepath ) ) {
			return false;
		}

		// First remove the query strings.
		$original_filepath = Front_End_Optimization::remove_query_strings( preg_replace( '/\?.*/', '', $original_filepath ) );
		$new_file_path     = Front_End_Optimization::remove_query_strings( preg_replace( '/\?.*/', '', $new_file_path ) );

		// Gets file modification time.
		$original_file_timestamp = file_exists( $original_filepath ) ? filemtime( $original_filepath ) : true;
		$minified_file_timestamp = file_exists( $new_file_path ) ? filemtime( $new_file_path ) : false;

		// Compare the original and new file timestamps.
		// This check will fail if the minified file doens't exists
		// and it will be created in the code below.
		if ( $original_file_timestamp === $minified_file_timestamp ) {
			return true;
		}

		// Fallback if we are not on a SiteGround Server.
		if ( ! Helper_Service::is_siteground() ) {
			$minifier_type = strtoupper( pathinfo( $original_filepath, PATHINFO_EXTENSION ) );

			if ( empty( $minifier_type ) ) {
				return false;
			}

			$status = ! $this->minify_scripts_lib( $minifier_type, $new_file_path, $original_filepath );

		} else {
			// The minified file doens't exists or the original file has been modified.
			// Minify the file then.
			exec(
				sprintf(
					'minify %s --output=%s',
					$original_filepath,
					$new_file_path
				),
				$output,
				$status
			);
		}

		// Return false if the minification fails.
		if ( 1 === intval( $status ) || ! file_exists( $new_file_path ) ) {
			return false;
		}

		// Set the minified file last modification file equla to original file.
		$this->wp_filesystem->touch( $new_file_path, $original_file_timestamp );

		return true;

	}

	/**
	 * Minify styles included in header and footer
	 *
	 * @since  5.0.0
	 */
	public function minify_styles() {
		global $wp_styles;

		// Bail if the scripts object is empty.
		if (
			! is_object( $wp_styles ) ||
			null === $this->assets_dir ||
			$this->has_exclude_param()
		) {
			return;
		}

		$styles = wp_clone( $wp_styles );
		$styles->all_deps( $styles->queue );

		$excluded_styles = apply_filters( 'sgo_css_minify_exclude', $this->css_ignore_list );

		// Get groups of handles.
		foreach ( $styles->to_do as $handle ) {
			// Skip styles.
			if (
				stripos( $wp_styles->registered[ $handle ]->src, '.min.css' ) !== false || // If the file is minified already.
				false === $wp_styles->registered[ $handle ]->src || // If the source is empty.
				in_array( $handle, $excluded_styles ) || // If the file is ignored.
				@strpos( Helper_Service::get_home_url(), parse_url( $wp_styles->registered[ $handle ]->src, PHP_URL_HOST ) ) === false // Skip all external sources.
			) {
				continue;
			}

			$original_filepath = Front_End_Optimization::get_original_filepath( $wp_styles->registered[ $handle ]->src );

			$parsed_url = parse_url( $wp_styles->registered[ $handle ]->src );

			// Build the minified version filename.
			$filename = dirname( $original_filepath ) . '/' . $wp_styles->registered[ $handle ]->handle . '.min.css';

			// Check if the handle of the style has forward slashes in it, if so - replace them with dashes.
			if ( false !== strpos( $wp_styles->registered[ $handle ]->handle, '/' ) ) {
				$filename = dirname( $original_filepath ) . '/' . str_replace( '/', '-', $wp_styles->registered[ $handle ]->handle ) . '.min.css';
			}

			if ( ! empty( $parsed_url['query'] ) ) {
				$filename = $filename . '?' . $parsed_url['query'];
			}

			// Check for original file modifications and create the minified copy.
			$is_minified_file_ok = $this->check_and_create_file( $filename, $original_filepath );

			// Check that everythign with minified file is ok.
			if ( $is_minified_file_ok ) {
				// Replace the script src with the minified version.
				$wp_styles->registered[ $handle ]->src = str_replace( ABSPATH, Helper_Service::get_site_url(), $filename );
			}
		}
	}

	/**
	 * Run the html minification.
	 *
	 * @since  5.5.2
	 *
	 * @param  string $html Page html.
	 *
	 * @return string       Minified html.
	 */
	public function run( $html ) {
		// Do not minify the html if the current url is excluded.
		if ( $this->is_url_excluded() ) {
			return $html;
		}

		return self::minify_html( $html );
	}

	/**
	 * Minify the html output.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $buffer The page content.
	 *
	 * @return string         Minified content.
	 */
	public function minify_html( $buffer ) {
		$content = Minify_Html::minify( $buffer );
		return $content;
	}

	/**
	 * Check if the current url has params that are excluded.
	 *
	 * @since  5.1.0
	 *
	 * @return boolean True if the url is excluded, false otherwise.
	 */
	public function is_url_excluded() {
		$url = Helper::get_current_url();

		// Get excluded urls.
		$excluded_urls = apply_filters( 'sgo_html_minify_exclude_urls', get_option( 'siteground_optimizer_minify_html_exclude', array() ) );

		// Prepare the url parts for being used as regex.
		$prepared_parts = array_map(
			function( $item ) {
				return str_replace( '\*', '.*', preg_quote( str_replace( home_url(), '', $item ), '/' ) );
			}, $excluded_urls
		);

		// Build the regular expression.
		$regex = sprintf(
			'/%s(%s)$/i',
			preg_quote( home_url(), '/' ), // Add the home url in the beginning of the regex.
			implode( '|', $prepared_parts ) // Then add each part.
		);

		// Check if the current url matches any of the excluded urls.
		preg_match( $regex, $url, $matches );

		// The url is excluded if matched the regular expression.
		if ( ! empty( $matches ) ) {
			return true;
		}

		// If there are no params we don't need to check the query params.
		if ( ! isset( $_REQUEST ) ) {
			return false;
		}

		// Get excluded params.
		$excluded_params = apply_filters( 'sgo_html_minify_exclude_params', $this->exclude_params );

		return $this->has_exclude_param( $excluded_params );
	}

	/**
	 * Check if the current url, should be excluded from optimizations.
	 *
	 * @since  5.4.6
	 *
	 * @param  array $params Array of GET params.
	 *
	 * @return boolean True if the url should be excluded, false otherwise.
	 */
	public function has_exclude_param( $params = array() ) {
		// If there are no params we don't need to check the query params.
		if ( ! isset( $_REQUEST ) ) {
			return false;
		}

		if ( empty( $params ) ) {
			$params = $this->exclude_params;
		}

		// Check if any of the excluded params exists in the request.
		foreach ( $params as $param ) {
			if ( array_key_exists( $param, $_REQUEST ) ) {
				return true;
			}
		}

		return false;
	}
}
