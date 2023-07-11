<?php
namespace SiteGround_Optimizer\Htaccess;

use SiteGround_Optimizer;
use SiteGround_Helper\Helper_Service;

class Htaccess {

	/**
	 * Path to htaccess file.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var string The path to htaccess file.
	 */
	private $path = null;

	/**
	 * WordPress filesystem.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 */
	private $wp_filesystem = null;

	/**
	 * The singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @var \Htaccess The singleton instance.
	 */
	private static $instance;

	/**
	 * Regular expressions to check if a rules is enabled.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var array Regular expressions to check if a rules is enabled.
	 */
	private $types = array(
		'gzip'            => array(
			'enabled'     => '/\#\s+GZIP enabled by SG-Optimizer/si',
			'disabled'    => '/\#\s+GZIP enabled by SG-Optimizer(.+?)\#\s+END\s+GZIP\n/ims',
			'disable_all' => '/\#\s+GZIP enabled by SG-Optimizer(.+?)\#\s+END\s+GZIP\n|<IfModule mod_deflate\.c>(.*?\n)<\/IfModule>|# BEGIN WP Rocket(.*)# END WP Rocket/ims',
		),
		'browser-caching' => array(
			'enabled'     => '/\#\s+Leverage Browser Caching by SG-Optimizer/si',
			'disabled'    => '/\#\s+Leverage Browser Caching by SG-Optimizer(.+?)\#\s+END\s+LBC\n/ims',
			'disable_all' => '/\#\s+Leverage Browser Caching by SG-Optimizer(.+?)\#\s+END\s+LBC\n|<IfModule mod_expires\.c>(.*?\n?)(<\/IfModule>\n\s)?<\/IfModule>/ims',
		),
		'ssl'             => array(
			'enabled'     => '/HTTPS forced by SG-Optimizer/si',
			'disabled'    => '/\#\s+HTTPS\s+forced\s+by\s+SG-Optimizer(.+?)\#\s+END\s+HTTPS(\n)?/ims',
			'disable_all' => '/\#\s+HTTPS\s+forced\s+by\s+SG-Optimizer(.+?)\#\s+END\s+HTTPS(\n)?/ims',
		),
		'user-agent-vary' => array(
			'enabled'     => '/\#\s+SGO Unset Vary/si',
			'disabled'    => '/\#\s+SGO\s+Unset\s+Vary(.+?)\#\s+SGO\s+Unset\s+Vary\s+END(\n)?/ims',
			'disable_all' => '/\#\s+SGO\s+Unset\s+Vary(.+?)\#\s+SGO\s+Unset\s+Vary\s+END(\n)?/ims',
		),
	);

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		if ( null === $this->wp_filesystem ) {
			$this->wp_filesystem = Helper_Service::setup_wp_filesystem();
		}

		if ( null === $this->path ) {
			$status = $this->set_htaccess_path();

			// Bail if .htaccess is inaccessible.
			if ( false === $status ) {
				return false;
			}
		}

		self::$instance = $this;
	}

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
	 * Set the htaccess path.
	 *
	 * @since 5.0.0
	 */
	public function set_htaccess_path() {
		// Build the filepath.
		$filepath = $this->wp_filesystem->abspath() . '.htaccess';

		// Create the htaccess if it doesn't exists.
		if ( ! is_file( $filepath ) ) {
			$this->wp_filesystem->touch( $filepath );
		}

		// Bail if it isn't writable.
		if ( ! $this->wp_filesystem->is_writable( $filepath ) ) {
			return false;
		}
		// Finally set the path.
		$this->path = $filepath;
	}

	/**
	 * Return the htaccess path.
	 *
	 * @since  5.0.0
	 *
	 * @return mixed The htaccess path or null it's not set.
	 */
	private function get_htaccess_path() {
		return $this->path;
	}

	/**
	 * Remove the rule in htaccess that enable the ssl.
	 *
	 * @since  5.0.0
	 *
	 * @param string $type The rule type to disable.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function disable( $type ) {
		// Bail if htaccess doesn't exists.
		if (
			null === $this->path ||
			! array_key_exists( $type, $this->types )
		) {
			return false;
		}

		// Bail if the rile is already disabled.
		if ( ! $this->is_enabled( $type ) ) {
			return true;
		}

		// Get the content of htaccess.
		$content = $this->wp_filesystem->get_contents( $this->path );

		$new_content = preg_replace( $this->types[ $type ]['disabled'], '', $content );

		return $this->lock_and_write( $new_content );
	}

	/**
	 * Add rule to htaccess that enables the ssl.
	 *
	 * @since  5.0.0
	 *
	 * @param string $type        The rule type to enable.
	 * @param array  $replacement Array containing search and replace strings.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function enable( $type, $replacement = array() ) {
		// Bail if htaccess doesn't exists.
		if ( null === $this->path ) {
			return false;
		}

		// Disable all other rules first.
		$content = $this->wp_filesystem->get_contents( $this->path );

		if ( ! empty( $this->types[ $type ]['disable_all'] ) ) {
			$content = preg_replace( $this->types[ $type ]['disable_all'], '', $content );
		}

		// Get the new rule.
		$new_rule = $this->wp_filesystem->get_contents( SiteGround_Optimizer\DIR . '/templates/' . $type . '.tpl' );

		// Check for replacement.
		if ( ! empty( $replacement ) ) {
			$new_rule = str_replace( $replacement['search'], $replacement['replace'], $new_rule );
		}

		// Generate the new content of htaccess.
		$new_content = ( 'user-agent-vary' === $type ) ? $content . PHP_EOL . $new_rule : $new_rule . PHP_EOL . $content;

		// Return the result.
		return $this->lock_and_write( $new_content );
	}

	/**
	 * Lock file and write something in it.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $content Content to add.
	 *
	 * @return bool            True on success, false otherwise.
	 */
	private function lock_and_write( $content ) {
		// Bail if .htaccess is not writable.
		if ( ! $this->wp_filesystem->is_writable( $this->path ) ) {
			return false;
		}

		$fp = fopen( $this->path, 'w+' );

		if ( flock( $fp, LOCK_EX ) ) {
			fwrite( $fp, $content );
			flock( $fp, LOCK_UN );
			fclose( $fp );
			return true;
		}

		fclose( $fp );
		return false;
	}

	/**
	 * Check if rule is enabled.
	 *
	 * @since  5.0.0
	 *
	 * @param string $type The rule type.
	 *
	 * @return boolean True if the rule is enabled, false otherwise.
	 */
	public function is_enabled( $type ) {
		// Bail if the type doesn't exists in rule types.
		if ( ! array_key_exists( $type, $this->types ) ) {
			return false;
		}

		// Get the content of htaccess.
		$content = $this->wp_filesystem->get_contents( $this->path );

		// Return the result.
		return preg_match( $this->types[ $type ]['enabled'], $content );
	}
}
