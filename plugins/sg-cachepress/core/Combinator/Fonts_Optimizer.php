<?php
namespace SiteGround_Optimizer\Combinator;

use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;

/**
 * SG Fonts Combinator main plugin class
 */
class Fonts_Optimizer extends Abstract_Combinator {

	/**
	 * Dir where the we will store the Google fonts css.
	 *
	 * @since 5.3.6
	 *
	 * @var string|null Path to fonts dir.
	 */
	public $fonts_dir = 'google-fonts';

	/**
	 * Google fonts url.
	 *
	 * @since 5.8.1
	 *
	 * @var string URL to the google fonts lib.
	 */
	const GOOGLE_API_URL = 'https://fonts.googleapis.com/';

	/**
	 * Regex parts.
	 *
	 * @since 5.3.4
	 *
	 * @var array Google Fonts regular expression
	 */
	public $regex_parts = array(
		'~', // The php quotes.
		'<link', // Match the opening part of link tags.
		'(?:\s+(?:(?!href\s*=\s*)[^>])+)?', // Negative lookahead aserting the regex does not match href attribute.
		'(?:\s+href\s*=\s*(?P<quotes>[\'|"]))', // Match the href attribute followed by single or double quotes. Create a `quotes` group, so we can use it later.
		'(', // Open the capturing group for the href value.
			'(?:https?:)?', // Match the protocol, which is optional. Sometimes the fons is added. without protocol i.e. //fonts.googleapi.com/css.
			'\/\/fonts\.googleapis\.com\/', // Match that the href value is google font link.
			'(?P<type>css2?)', // The type of the fonts CSS/CSS2.
			'(?:(?!(?P=quotes)).)+', // Match anything in the href attribute until the closing quote.
		')', // Close the capturing group.
		'(?P=quotes)', // Match the closing quote.
		'(?:\s+.*?)?', // Match anything else after the href tag.
		'[>]', // Until the closing tag if found.
		'~', // The php quotes.
		'ims',
	);

	/**
	 * The singleton instance.
	 *
	 * @since 5.5.2
	 *
	 * @var The singleton instance.
	 */
	private static $instance;

	/**
	 * The constructor.
	 *
	 * @since 5.5.2
	 */
	public function __construct() {
		parent::__construct();
		self::$instance = $this;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.5.2
	 *
	 * @return  The singleton instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Combine the google fonts.
	 *
	 * @since  5.3.4
	 *
	 * @param  string $html The page html.
	 *
	 * @return string       Modified html with combined Google font.
	 */
	public function run( $html ) {
		// Get fonts if any.
		$fonts = $this->get_items( $html );
		// Insert preload links for local fonts.
		$html = $this->preload_local_fonts( $html );

		// Bail if there are no fonts or if there is only one font.
		if ( empty( $fonts ) ) {
			return $html;
		}

		$_fonts = $fonts;

		// The methods that should be called to combine the fonts.
		$methods = array(
			'parse_fonts', // Parse fonts.
			'beautify', // Beautify and remove duplicates.
			'prepare_urls', // Prepare the combined urls.
			'get_combined_css', // Get combined css.
		);
		foreach ( $methods as $method ) {
			$_fonts = call_user_func( array( $this, $method ), $_fonts );
		}

		$html = preg_replace( '~<\/title>~', '</title>' . $_fonts, $html, 1 );

		// Remove old fonts.
		foreach ( $fonts as $font ) {
			$html = str_replace( $font[0], '', $html );
		}

		$html = preg_replace( '~<\/title>~', '</title><link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin/><link rel="preconnect" href="https://fonts.googleapis.com"/>', $html, 1 );
		return $html;
	}

	/**
	 * Parse and get Google fonts details.
	 *
	 * @since  5.3.4
	 *
	 * @param  array $fonts Google fonts found in the page html.
	 *
	 * @return array        Google fonts details.
	 */
	public function parse_fonts( $fonts ) {
		foreach ( $fonts as $font ) {
			// Decode the entities.
			$url = html_entity_decode( $font[2] );
			// Parse the url and get the query string.
			$query_string = wp_parse_url( $url, PHP_URL_QUERY );
			// Bail if the query string is empty.
			if ( ! isset( $query_string ) ) {
				return;
			}

			// Parse the query args.
			$parsed_font = wp_parse_args( $query_string );
			// Assign parsed fonts to the parts array.
			$parts[ $font['type'] ]['fonts'][] = $parsed_font['family'];
			// Add subset to collection.
			if ( isset( $parsed_font['subset'] ) ) {
				$parts[ $font['type'] ]['subset'][] = $parsed_font['subset'];
			}
		}

		return $parts;

	}

	/**
	 * Convert all special chars, htmlentities and remove duplicates.
	 *
	 * @since  5.3.4
	 *
	 * @param  array $parts The Google font details.
	 *
	 * @return arrray        Beatified font details.
	 */
	public function beautify( $parts ) {

		// URL encode & convert characters to HTML entities.
		foreach ( $parts as $key => $type ) {
			if ( 'css2' === $key ) {
				continue;
			}
			$type = array_map( function( $item ) {
				return array_map(
					'rawurlencode',
					array_map(
						'htmlentities',
						$item
					)
				);
			}, $type);
			$parts[ $key ] = $type;
		}

		// Remove duplicates.
		foreach ( $parts as $key => $type ) {
			if ( 'css2' === $key ) {
				continue;
			}
			$type = array_map(
				'array_filter',
				array_map(
					'array_unique',
					$type
				)
			);
			// Assign array with removed duplicates to the main one.
			$parts[ $key ] = $type;
		}

		return $parts;
	}

	/**
	 * Implode Google fonts and subsets, so they can be used in combined tag.
	 *
	 * @since  5.3.4
	 *
	 * @param  array $fonts Font deatils.
	 *
	 * @return array        Imploaded fonts and subsets.
	 */
	public function prepare_urls( $fonts ) {
		// Define the display variable.
		$display = apply_filters( 'sgo_google_fonts_display', 'swap' );
		// Implode different fonts into one.
		foreach ( $fonts as $css_type => $value ) {
			$url = self::GOOGLE_API_URL . $css_type;
			$subsets = ! empty( $value['subset'] ) ? implode( ',', $value['subset'] ) : '';
			switch ( $css_type ) {
				case 'css':
					$url .= '?family=' . implode( '%7C', $value['fonts'] );
					break;
				case 'css2':
					$query_string = '';
					foreach ( $value['fonts'] as $index => $font_family ) {
						$delimiter = 0 === $index ? '?' : '&';
						$query_string .= $delimiter . 'family=' . $font_family;
					}
					$url .= $query_string;
					break;
			}

			$urls[] = $url . '&display=' . $display . '&subset=' . $subsets;
		}

		return $urls;
	}

	/**
	 * Combine Google fonts in one tag
	 *
	 * @since  5.3.4
	 *
	 * @param  array $urls Fonts data.
	 *
	 * @return string        Combined tag.
	 */
	public function get_combined_css( $urls ) {
		// Gather all of the Google fonts and generate the combined tag.
		$combined_tags = array();
		$css = '';
		foreach ( $urls as $url ) {
			// Get the fonts css.
			$css .= $this->get_external_file_content( $url, 'css', 'fonts' );
			$combined_tags[] = '<link rel="stylesheet" data-provider="sgoptimizer" href="' . $url . '" />'; //phpcs:ignore

		}

		// Return the combined tag if the css is empty.
		if ( false === $css ) {
			return implode( '', $combined_tags );
		}

		// Return combined tag if AMP plugin is active.
		if ( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() ) {
			return implode( '', $combined_tags );
		}

		// Return the inline css.
		return '<style type="text/css">' . $css . '</style>';
	}

	/**
	 * Run the preload link insertion.
	 *
	 * @since  5.7.0
	 *
	 * @param  string $html The HTML Content.
	 *
	 * @return string The preload links.
	 */
	public function preload_local_fonts( $html ) {
		// Check if there are any urls inserted by the user.
		$urls = get_option( 'siteground_optimizer_fonts_preload_urls', array() );

		// Bail, if there are no urls.
		if ( empty( $urls ) ) {
			return $html;
		}

		$new_html = '';

		// Loop trough urls and prepare them for insertion.
		foreach ( $urls as $url ) {
			$new_html .= '<link rel="preload" as="font" href="' . $url . '" crossorigin/>';
		}

		// Insert the link in the head section.
		return preg_replace( '~<\/title>~', '</title>' . $new_html, $html, 1 );
	}
}
