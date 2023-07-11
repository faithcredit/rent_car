<?php
namespace SiteGround_Optimizer\Ssl;

use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Supercacher\Supercacher;

class Ssl {

	/**
	 * The singleton instance.
	 *
	 * @since 5.9.8
	 *
	 * @var \Ssl The singleton instance.
	 */
	private static $instance;

	/**
	 * The constructor
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		self::$instance = $this;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.9.8
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
	 * Enable the ssl when the siteurl is changed.
	 *
	 * @since  5.3.1
	 *
	 * @param  mixed $old The old option value.
	 * @param  mixed $new The new option value.
	 *
	 * @return mixed      The new option value.
	 */
	public function maybe_switch_rules( $old, $new ) {
		if ( Options::is_enabled( 'siteground_optimizer_ssl_enabled' ) ) {
			$this->enable();
		}

		return $new;
	}

	/**
	 * Check if the current domain has valid ssl certificate.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True is the domain has certificate, false otherwise.
	 */
	public function has_certificate() {
		// Get siteurl.
		$home_url = get_option( 'siteurl' );

		// Change siteurl protocol.
		if ( preg_match( '/^http\:/s', $home_url ) ) {
			$home_url = str_replace( 'http', 'https', $home_url );
		}

		$site_url = add_query_arg(
			'sgCacheCheck', // The key.
			'022870ae06716782ce17e4f6e7f69cc2', // The value.
			$home_url
		);

		ini_set( 'user_agent', 'SG-Optimizer 3.0.2;' );
		// Create a streams context.
		$stream = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
				),
			)
		);

		// Parse the url.
		$parse_url = parse_url( $site_url, PHP_URL_HOST );

		// Create the stream socket client.
		$read = @stream_socket_client(
			'ssl://' . $parse_url . ':443' ,
			$errno,
			$errstr,
			3,
			STREAM_CLIENT_CONNECT,
			$stream
		);

		// Bail if the stream failed.
		if ( false === $read ) {
			return false;
		}

		// Get the params we are checking.
		$cont = @stream_context_get_params( $read );

		return is_null( $cont['options']['ssl']['peer_certificate'] ) ? false : true;
	}

	/**
	 * Disable the ssl.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function disable() {

		// Switch the protocol in database.
		$protocol_switched     = $this->switch_protocol();
		$disable_from_htaccess = true;

		// Remove the rule from htaccess for single sites.
		if ( ! is_multisite() ) {
			$disable_from_htaccess = Htaccess::get_instance()->disable( 'ssl' );
		}

		if (
			! $protocol_switched ||
			! $disable_from_htaccess
		) {
			return false;
		}

		// Disable the option.
		Options::disable_option( 'siteground_optimizer_ssl_enabled' );

		Supercacher::purge_cache();
		Supercacher::flush_memcache();
		Supercacher::delete_assets();

		// Return success.
		return true;
	}

	/**
	 * Enable the ssl.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function enable() {
		// Bail if the domain doesn't have certificate.
		if ( ! $this->has_certificate() ) {
			return false;
		}

		// Switch the protocol in database.
		$protocol_switched    = $this->switch_protocol( true );
		$enable_from_htaccess = true;

		$replacements = array(
			'search'  => '{MAYBE_WWW}',
			'replace' => '',
		);

		// Add rule to htaccess for single sites.
		if ( ! is_multisite() ) {
			$parsed = parse_url( get_option( 'siteurl' ) );

			if ( @strpos( $parsed['host'], 'www.' ) === 0 ) {
				$replacements['replace'] = "RewriteCond %{HTTP_HOST} !^www\. [NC]\n    RewriteRule ^ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]";
			}

			$enable_from_htaccess = Htaccess::get_instance()->enable( 'ssl', $replacements );
		}

		if (
			! $protocol_switched ||
			! $enable_from_htaccess
		) {
			return false;
		}

		// Enable the option.
		Options::enable_option( 'siteground_optimizer_ssl_enabled' );

		Supercacher::purge_cache();
		Supercacher::flush_memcache();
		Supercacher::delete_assets();

		// Return success.
		return true;
	}

	/**
	 * Chnage the url protocol.
	 *
	 * @since  5.0.0
	 *
	 * @param bool $ssl Whether to switch to https or not.
	 *
	 * @return bool     The result.
	 */
	private function switch_protocol( $ssl = false ) {
		$from = true === $ssl ? 'http' : 'https';
		$to   = true === $ssl ? 'https' : 'http';

		// Strip the protocol from site url.
		$site_url_without_protocol = preg_replace( '#^https?#', '', get_option( 'siteurl' ) );

		// Build the command.
		$command = sprintf(
			"wp search-replace '%s' '%s' --all-tables",
			$from . $site_url_without_protocol,
			$to . $site_url_without_protocol
		);

		// Execute the command.
		exec(
			$command,
			$output,
			$status
		);

		// Check for errors during the import.
		if ( ! empty( $status ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Creates an array of insecure links that should be https and an array of secure links to replace with
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function get_url_list() {
		$home_no_www  = str_replace( '://www.', '://', get_option( 'home' ) );
		$home_yes_www = str_replace( '://', '://www.', $home_no_www );

		// Build the search links.
		$search = array(
			str_replace( 'https://', 'http://', $home_yes_www ),
			str_replace( 'https://', 'http://', $home_no_www ),
			"src='http://",
			'src="http://',
		);

		return array(
			'search'  => $search, // The search links.
			'replace' => str_replace( 'http://', 'https://', $search ), // The replace links.
		);
	}

	/**
	 * Replace all insecure links before the page is sent to the visitor's browser.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $content The page content.
	 *
	 * @return string          Modified content.
	 */
	public function replace_insecure_links( $content ) {
		// Get the url list.
		$urls = $this->get_url_list();

		// now replace these links.
		$content = str_replace( $urls['search'], $urls['replace'], $content );

		// Replace all http links except hyperlinks
		// All tags with src attr are already fixed by str_replace.
		$pattern = array(
			'/url\([\'"]?\K(http:\/\/)(?=[^)]+)/i',
			'/<link([^>]*?)href=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
			'/<meta property="og:image" .*?content=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
			'/<form [^>]*?action=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
		);

		// Return modified content.
		return preg_replace( $pattern, 'https://', $content );
	}

	/**
	 * Check if the option for ssl and .htaccess rules match, if not, disable/enable option.
	 *
	 * @since 6.0.0
	 *
	 * @return bool true, if option is changed, false if matching.
	 */
	public function maybe_switch_option() {
		// Check if SSL is enabled in .htaccess.
		$htaccess_is_enabled = Htaccess::get_instance()->is_enabled( 'ssl' );
		// Check if SSL option is enabled in the database.
		$option_is_enabled = Options::is_enabled( 'siteground_optimizer_ssl_enabled' );

		// Check if .htaccess and the option are matching.
		if (
			( $htaccess_is_enabled && $option_is_enabled ) ||
			( ! $htaccess_is_enabled && ! $option_is_enabled )
		) {
			return;
		}

		// If .htaccess has SSL rule and the option is not set - set it programatically.
		if ( true === $htaccess_is_enabled ) {
			return $this->enable();
		}

		// Disable the option if the .htaccess doesn't have a SSL rule and the option is set.
		return $this->disable();
	}
}
