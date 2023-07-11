<?php
namespace SiteGround_Optimizer\Combinator;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Helper\Helper_Service;

/**
 * SG Abstract_Combinator main plugin class
 */
abstract class Abstract_Combinator {
	/**
	 * WordPress filesystem.
	 *
	 * @since 5.0.0
	 *
	 * @var object|null WordPress filesystem.
	 */
	private $wp_filesystem = null;

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
	}

	/**
	 * Return the style content.
	 *
	 * @since  5.1.0
	 *
	 * @param string $url Link to the file.
	 *
	 * @return string The stylesheet content.
	 */
	public function get_content( $url ) {
		// Remove the query strings.
		$url = Front_End_Optimization::remove_query_strings( $url );
		// Get the original filepath.
		$filepath = Front_End_Optimization::get_original_filepath( $url );

		// Get the content of the file.
		return $this->wp_filesystem->get_contents( preg_replace( '~#038;(.*)~', '', $filepath ) );
	}

	/**
	 * Create new stylesheet and return the url to it.
	 *
	 * @since  5.1.0
	 *
	 * @param  string $content The file content.
	 * @param  string $handle  Stylesheet handle.
	 * @param  string $type    The type of the file.
	 *
	 * @return string          The url to the new file.
	 */
	public function create_temp_file_and_get_url( $content, $handle, $type = 'css' ) {
		$style_hash = md5( implode( '', $content ) );
		$new_file   = $this->assets_dir . 'siteground-optimizer-combined-' . $type . '-' . $style_hash . '.' . $type;
		$url        = str_replace( ABSPATH, Helper_Service::get_site_url(), $new_file );

		$data = array(
			'handle' => 'siteground-optimizer-combined-' . $type . '-' . $style_hash,
			'url'    => $url,
		);

		if ( is_file( $new_file ) ) {
			return $data;
		}

		// Create the new file.
		$this->wp_filesystem->touch( $new_file );

		// Add the new content into the file.
		$this->wp_filesystem->put_contents(
			$new_file,
			$this->get_content_with_replacements( $content )
		);

		return $data;
	}

	/**
	 * Hides comments from the HTML.
	 *
	 * @since 5.5.0
	 *
	 * @param  string $html HTML content.
	 *
	 * @return string HTML content without comments.
	 */
	public function hide_comments( $html ) {
		return preg_replace( '/<!--(.*)-->/Uis', '', $html );
	}

	/**
	 * Get all items which the regular expression will match from the html.
	 *
	 * @since  5.5.0
	 *
	 * @param  string $html The page html.
	 *
	 * @return array        Array with all matches.
	 */
	public function get_items( $html ) {
		// Build the regular expression.
		$regex = implode( '', $this->regex_parts );

		// Check for items.
		preg_match_all( $regex, $html, $matches, PREG_SET_ORDER );

		// Return the matches.
		return $matches;
	}

	/**
	 * Get the external file content
	 *
	 * @since  5.5.0
	 *
	 * @param  string $url File url.
	 *
	 * @return bool|string File content on success, false on failure.
	 */
	public function get_external_file_content( $url, $type, $add_dir='' ) {
		// Generate unique hash tag unsing the url.
		$hash     = md5( $url );
		// Build the dir.
		$dir      = Front_End_Optimization::get_instance()->assets_dir . $add_dir;

		// Build the file path.
		$file_path = $dir . '/' . $hash . '.' . $type;

		// Setup the WP Filesystem.
		$wp_filesystem = Helper_Service::setup_wp_filesystem();

		// Check if cached version of the file exists.
		if ( $wp_filesystem->exists( $file_path ) ) {
			// Get the file content.
			$content = $wp_filesystem->get_contents( $file_path );

			// Return the file content if it's not empty.
			if ( ! empty( $content ) ) {
				return $content;
			}
		}

		// THE FILE DOESN'T EXIST.

		// Create the additional dir if doesn't exists.
		if ( ! $wp_filesystem->exists( $dir ) ) {
			$is_dir_created = $wp_filesystem->mkdir( $dir );
		}

		// Try to fetch the file.
		$request = wp_remote_get( $url );

		// Bail if the request fails.
		if ( is_wp_error( $request ) ) {
			return false;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return false;
		}

		// Try to create the file and bail if for some reason it's not created.
		if ( false === $wp_filesystem->touch( $file_path ) ) {
			return false;
		}

		// Get the file content from the request.
		$file_content = wp_remote_retrieve_body( $request );

		// Add the file content in the file, so it can be cached.
		$wp_filesystem->put_contents(
			$file_path,
			$file_content
		);

		// Finally return the file content.
		return $file_content;
	}
}
