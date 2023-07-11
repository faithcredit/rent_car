<?php
namespace SiteGround_Optimizer\Parser;

use SiteGround_Optimizer\Minifier\Minifier;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Combinator\Css_Combinator;
use SiteGround_Optimizer\Combinator\Js_Combinator;
use SiteGround_Optimizer\Combinator\Fonts_Optimizer;
use SiteGround_Optimizer\DNS\Prefetch;
use SiteGround_Optimizer\File_Cacher\File_Cacher;
use SiteGround_Optimizer\Ssl\Ssl;
use SiteGround_Optimizer\Helper\Helper;

/**
 * Parser functions and main initialization class.
 */
class Parser {

	/**
	 * Run the parser.
	 *
	 * @since  5.5.2
	 *
	 * @param  string $html The page html.
	 *
	 * @return string $html The modified html.
	 */
	public function run( $html ) {
		if ( ! preg_match( '/<\/html>/i', $html ) ) {
			return $html;
		}

		// Replace unsecure links if the option is enabled.
		if ( Options::is_enabled( 'siteground_optimizer_fix_insecure_content' ) ) {
			$html = Ssl::get_instance()->replace_insecure_links( $html );
		}

		// Do not run optimizations if amp is active, the page is an xml or feed.
		if (
			$this->is_amp_enabled( $html ) ||
			Helper::is_xml( $html ) ||
			is_feed()
		) {
			return $html;
		}

		// If the user is logged in and the filebased caching is disabled.
		if ( is_user_logged_in() ) {
			// Return the original html if the filebased caching is disabled.
			if ( ! Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
				return $html;
			}

			// Return the original html if loggedin filebased caching is disabled.
			if ( ! Options::is_enabled( 'siteground_optimizer_logged_in_cache' ) ) {
				return $html;
			}
		}

		$optimized_html = $this->optimize_for_visitors( $html );

		if ( Options::is_enabled( 'siteground_optimizer_file_caching' ) ) {
			File_Cacher::get_instance()->process( $optimized_html );
		}

		return $optimized_html;

	}

	/**
	 * Check if specific frontend optimization should be used for visitors.
	 *
	 * @since  5.9.7
	 *
	 * @param  string $html The page html.
	 *
	 * @return string $html The modified html.
	 */
	public function optimize_for_visitors( $html ) {
		if ( Options::is_enabled( 'siteground_optimizer_combine_css' ) ) {
			$html = Css_Combinator::get_instance()->run( $html );
		}

		if (
			Options::is_enabled( 'siteground_optimizer_combine_javascript' ) &&
			! $this->is_post_request()
		) {
			$html = Js_Combinator::get_instance()->run( $html );
		}

		if ( Options::is_enabled( 'siteground_optimizer_optimize_web_fonts' ) ) {
			$html = Fonts_Optimizer::get_instance()->run( $html );
		}

		$html = Prefetch::get_instance()->run( $html );

		if ( Options::is_enabled( 'siteground_optimizer_optimize_html' ) ) {
			$html = Minifier::get_instance()->run( $html );
		}

		return $html;
	}

	/**
	 * AMP Atribute check. Runs a check if AMP option is enabled
	 *
	 * @since 5.5.8
	 *
	 * @param string $html The page html.
	 *
	 * @return bool $run_amp_check Wheter the page is loaded via AMP.
	 */
	public function is_amp_enabled( $html ) {
		// Get the first 200 chars of the file to make the preg_match check faster.
		$is_amp = substr( $html, 0, 200 );
		// Cheks if the document is containing the amp tag.
		return preg_match( '/<html[^>]+(amp|⚡)[^>]*>/', $is_amp );
	}

	/**
	 * Start buffer.
	 *
	 * @since  5.5.0
	 */
	public function start_bufffer() {
		ob_start( array( $this, 'run' ) );
	}

	/**
	 * End the buffer.
	 *
	 * @since  5.5.0
	 */
	public function end_buffer() {
		if ( ob_get_length() ) {
			ob_end_flush();
		}
	}

	/**
	 * Check if the request is of POST type.
	 *
	 * @since  7.0.2
	 *
	 * @return boolean true/false if is post request.
	 */
	public function is_post_request() {
		// Return true if is a POST type request.
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			return true;
		}

		return false;
	}
}
