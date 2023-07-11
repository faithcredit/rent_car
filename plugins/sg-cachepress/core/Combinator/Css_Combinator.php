<?php
namespace SiteGround_Optimizer\Combinator;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Helper\Helper_Service;

/**
 * SG Css_Combinator main plugin class
 */
class Css_Combinator extends Abstract_Combinator {
	/**
	 * Array containing all styles that will be loaded.
	 *
	 * @since 5.1.0
	 *
	 * @var array Array containing all styles that will be loaded.
	 */
	private $combined_styles_exclude_list = array(
		'amelia-elementor-widget-font',
		'amelia_booking_styles_vendor',
		'amelia_booking_styles',
		'uag-style',
		'buy_sell_ads_pro_template_stylesheet', // Too big file.
		'fgt-public', // Flo Form Builder.
	);

	/**
	 * Regex parts.
	 *
	 * @since 5.5.2
	 *
	 * @var array Style tags regular expression
	 */
	public $regex_parts = array(
		'~',
		'<link\s+([^>]+',
		'[\s\'"])?',
		'href\s*=\s*[\'"]\s*?',
		'(',
		'[^\'"]+\.css',
		'(?:\?[^\'"]*)?',
		')\s*?',
		'[\'"]',
		'([^>]+)?',
		'\/?>',
		'~',
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
	 * Excluded URLs.
	 *
	 * @since 5.6.3
	 *
	 * @var array Array of excldued urls.
	 */
	public $excluded_urls = array();

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
	 * Combine styles included in header and footer
	 *
	 * @param  string $html The page html.
	 *
	 * @return string       Modified html with combined styles tag.
	 *
	 * @since  5.5.2
	 */
	public function run( $html ) {
		// Prepaare the localized styles.
		$this->prepare_excluded_styles();
		// Hide comments from html.
		$html_without_comments = $this->hide_comments( $html );
		// Get styles from the html.
		$styles = $this->get_items( $html_without_comments );

		// Bail if there are no styles to combine.
		if ( empty( $styles ) ) {
			return $html;
		}

		$data = $this->parse( $styles );

		// Bail if the styles data is empty.
		if ( empty( $data ) ) {
			return $html;
		}

		return $this->get_new_html( $html, $data );
	}

	/**
	 * Parse and prepare styles for combination.
	 *
	 * @since  5.5.2
	 *
	 * @param  array $styles Array of styles data.
	 *
	 * @return array          Array of styles data.
	 */
	public function parse( $styles ) {
		// Prepare the data.
		$data = array();

		// Loop through all styles in the queue and check for excludes.
		foreach ( $styles as $style ) {
			// Bail if the sctyle is excluded.
			if ( $this->is_excluded( $style ) ) {
				continue;
			}
			// Add the style url and tag.
			$data[ $style[2] ] = $style[0];
		}

		// Return the data.
		return $data;
	}

	/**
	 * Prepare the excluded styles
	 *
	 * @since  5.5.2
	 */
	public function prepare_excluded_styles() {
		global $wp_styles;

		$excluded_handles = apply_filters(
			'sgo_css_combine_exclude',
			array_merge(
				$this->combined_styles_exclude_list,
				get_option( 'siteground_optimizer_combine_css_exclude', array() )
			)
		);

		// Bail if there are no registered styles.
		if ( empty( $wp_styles->registered ) ) {
			return;
		}

		// Get handles of all registered styles.
		$registered = array_keys( $wp_styles->registered );
		$excluded   = array();

		// Loop through all excluded handles and get their src.
		foreach ( $excluded_handles as $handle ) {
			// Bail if handle is now found.
			if ( ! in_array( $handle, $registered ) ) {
				continue;
			}

			// Replace the site url and get the src.
			$excluded[] = trim( str_replace( Helper_Service::get_site_url(), '', strtok( $wp_styles->registered[ $handle ]->src, '?' ) ), '/\\' );
		}

		// Set the excluded urls.
		$this->excluded_urls = $excluded;
	}

	/**
	 * Check if the style is excluded
	 *
	 * @since  5.5.2
	 *
	 * @param  string $style Style tag.
	 *
	 * @return boolean     True if the style is excluded, false otherwise.
	 */
	public function is_excluded( $style ) {
		if ( false !== @strpos( $style[0], 'media=' ) && ! preg_match( '/(?<=\s)media=["\'](?:\s*|[^"\']*?\b(all|screen)\b[^"\']*?)["\']/i', $style[0] ) ) {
			return true;
		}

		if ( false !== @strpos( $style[0], 'only screen and' ) ) {
			return true;
		}

		// Get the host from src..
		$host = parse_url( $style[2], PHP_URL_HOST );

		// Bail if it's an external style.
		if (
			@strpos( Helper_Service::get_home_url(), $host ) === false &&
			! @strpos( $style[2], 'wp-includes' )
		) {
			return true;
		}

		// Remove query strings from the url.
		$src  = Front_End_Optimization::remove_query_strings( $style[2] );

		// Bail if the url is excluded.
		if ( in_array( str_replace( trailingslashit( Helper_Service::get_site_url() ), '', $src ), $this->excluded_urls ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get combined css tag.
	 *
	 * @since  5.5.2
	 *
	 * @param  string $html         The original page content.
	 * @param  string $styles_data style data.
	 *
	 * @return string               Modified html.
	 */
	public function get_new_html( $html, $styles_data ) {
		// Remove style tags.
		foreach ( $styles_data as $url => $tag ) {
			$html = str_replace( $tag, '', $html );
			$new_content[ $url ] = $this->get_content( $url );
		}

		$tag_data = $this->create_temp_file_and_get_url( $new_content, 'combined-css', 'css' );

		$replace = '</title><link rel="stylesheet" id="' . $tag_data['handle'] . '" href="' . $tag_data['url'] . '" media="all" />'; //phpcs:ignore

		if ( Options::is_enabled( 'siteground_optimizer_preload_combined_css' ) ) {
			$replace .= ' <link rel="preload" href="' . $tag_data['url'] . '" as="style">';
		}

		// Add combined style tag.
		// phpcs:ignore 
		return preg_replace( '~<\/title>~', $replace, $html, 1 );
	}

	/**
	 * Replace all url to full urls.
	 *
	 * @since  5.1.0
	 *
	 * @param  string $contents Array with link to styles and style content.
	 *
	 * @return string       Content with replaced urls.
	 */
	public function get_content_with_replacements( $contents ) {
		// Set the new content var.
		$new_content = array();

		foreach ( $contents as $url => $content ) {
			$dir = trailingslashit( dirname( $url ) );

			$content = $this->check_for_imports( $content, $url );
			// Change font-display to swap.
			$content = $this->swap_font_display( $content );
			// Remove source maps urls.
			$content = preg_replace(
				'~^(\/\/|\/\*)(#|@)\s(sourceURL|sourceMappingURL)=(.*)(\*\/)?$~m',
				'',
				$content
			);

			$regex = '/url\s*\(\s*(?!["\']?data:)(?![\'|\"]?[\#|\%|])([^)]+)\s*\)([^;},\s]*)/i';

			$replacements = array();

			preg_match_all( $regex, $content, $matches );

			if ( ! empty( $matches ) ) {
				foreach ( $matches[1] as $index => $match ) {
					$match = trim( $match, " \t\n\r\0\x0B\"'" );

					// Bail if the url is valid.
					if ( false == preg_match( '~(http(?:s)?:)?\/\/(?:[\w-]+\.)*([\w-]{1,63})(?:\.(?:\w{2,}))(?:$|\/)~', $match ) ) {
						$replacement = str_replace( $match, $dir . $match, $matches[0][ $index ] );

						$replacements[ $matches[0][ $index ] ] = $replacement;
					}
				}
			}

			$keys = array_map( 'strlen', array_keys( $replacements ) );
			array_multisort( $keys, SORT_DESC, $replacements );

			$new_content[] = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
		}

		return implode( "\n", $new_content );
	}

	/**
	 * Check for imports in the files and get the import content.
	 *
	 * @since  5.4.5
	 *
	 * @param  string $content The file content.
	 * @param  string $url     The url to the file.
	 *
	 * @return string          Original content + content from import clause.
	 */
	private function check_for_imports( $content, $url ) {
		// Get the file dir.
		$dir = trailingslashit( dirname( $url ) );
		// Check for imports in the style.
		preg_match_all( '/@import\s+["\'](.+?)["\'];?/i', $content, $matches );

		// Return the content if there are no matches.
		if ( empty( $matches ) ) {
			return $content;
		}

		// Loop through all matches and get the imported css.
		foreach ( $matches[1] as $match ) {
			$import_content = $this->get_content_with_replacements(
				array(
					$url => $this->get_content( $dir . $match ),
				)
			);

			// Replace the @import with the css.
			$content = str_replace( $matches[0], $import_content, $content );
		}

		// Finally return the content.
		return $content;
	}

	/**
	 * Swap the display properties for the font-face.
	 *
	 * @since  5.7.0
	 *
	 * @param  string $content The file content.
	 *
	 * @return string          The content with swaped font-display.
	 */
	public function swap_font_display( $content ) {
		// Bail if Font Optimization is disabled.
		if ( ! Options::is_enabled( 'siteground_optimizer_optimize_web_fonts' ) ) {
			return $content;
		}

		// Check for font-face in the style.
		preg_match_all( '/@font-face\s*{([\s\S]*?)}/i', $content, $matches );

		// Bail ifthere are no font-faces.
		if ( empty( $matches ) ) {
			return $content;
		}

		// Loop through all matches and swap the display property.
		foreach ( $matches[1] as $match ) {
			// Get all font display properies.
			preg_match_all( '/font-display:.([a-zA-Z]+)/i', $match, $result );

			// Add the swap display.
			$new = empty( $result[0] ) ? $match . ";font-display: swap;\n" : str_replace( $result[0], 'font-display: swap', $match );

			$content = str_replace( $match, $new, $content );
		}

		return $content;
	}
}
