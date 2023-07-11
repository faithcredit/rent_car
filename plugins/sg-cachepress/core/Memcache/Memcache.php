<?php
namespace SiteGround_Optimizer\Memcache;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Helper\Helper_Service;

/**
 * The class responsible for obejct cache.
 */
class Memcache {
	/**
	 * Memcached excludes filename.
	 *
	 * @since 5.6.1
	 *
	 * @var string Path to the excludes filename.
	 */
	const EXCLUDES_FILENAME = WP_CONTENT_DIR . '/.sgo-memcache-excludes';

	/**
	 * Memcached crashed filename.
	 *
	 * @since 5.6.1
	 *
	 * @var string Path to the crashed filename.
	 */
	const CRASHED_FILENAME = WP_CONTENT_DIR . '/memcache-crashed.txt';

	/**
	 * Memcached Unix Socket.
	 *
	 * @since 7.2.2
	 *
	 * @var string Path to the memcached UNIX socket file.
	 */
	const UNIX_SOCK_FILE = '/home/.tmp/memcached.sock';

	/**
	 * Check if the memcache connection is working
	 * and reinitialize the dropin if not.
	 *
	 * @since  5.0.0
	 */
	public function status_healthcheck() {
		if ( Options::is_enabled( 'siteground_optimizer_enable_memcached' ) ) {
			// Check if the droping exists.
			if ( $this->dropin_exists() ) {
				if ( ! $this->is_connection_working() ) {
					Options::disable_option( 'siteground_optimizer_enable_memcached' );
					Options::enable_option( 'siteground_optimizer_memcache_notice' );

					// Remove the dropin since we are disabling the option.
					$this->remove_memcached_dropin();
				}
			} else {
				Options::disable_option( 'siteground_optimizer_enable_memcached' );
				Options::enable_option( 'siteground_optimizer_memcache_notice' );

				if ( $this->is_connection_working() ) {
					if ( file_exists( WP_CONTENT_DIR . '/object-cache-crashed.php' ) ) {
						Options::enable_option( 'siteground_optimizer_memcache_crashed' );
					} else {
						Options::enable_option( 'siteground_optimizer_memcache_dropin_crashed' );
					}
				}
			}
		}
	}

	/**
	 * Check if the object-cache.php dropin file exists (is readable).
	 *
	 * @since 5.0.0
	 *
	 * @return bool|string The file path if file exists and it's readable, false otherwise.
	 */
	public function dropin_exists() {
		$file = $this->get_object_cache_file();

		if ( file_exists( $file ) && is_readable( $file ) ) {
			return $file;
		}

		return false;
	}

	/**
	 * Get the path to where the object cache dropin should be.
	 *
	 * @since 5.0.0
	 */
	protected function get_object_cache_file() {
		return trailingslashit( WP_CONTENT_DIR ) . 'object-cache.php';
	}


	/**
	 * Check if a Memcached connection is working by setting and immediately getting a value.
	 *
	 * @since 5.0.0
	 *
	 * @return bool True on retrieving exactly the value set, false otherwise.
	 */
	public function is_connection_working() {
		if ( ! file_exists( self::UNIX_SOCK_FILE ) ) {
			return false;
		}

		$stat = stat( self::UNIX_SOCK_FILE );

		if ( false === $stat ) {
			return false;
		}

		if ( ( $stat['mode'] & 0140000 ) !== 0140000 ) {
			return false;
		}

		$memcache = new \Memcached();
		$memcache->addServer( self::UNIX_SOCK_FILE, 0 );
		$memcache->set( 'SGCP_Memcached_Test', 'Test!1', 50 );

		if ( 'Test!1' === $memcache->get( 'SGCP_Memcached_Test' ) ) {
			$memcache->flush();
			return true;
		}

		return false;
	}

	/**
	 * Copy the Memcache template contents into object-cache.php, replacing IP and Port where needed.
	 *
	 * @since 5.0.0
	 *
	 * @return bool True if the template was successfully copied, false otherwise.
	 */
	public function create_memcached_dropin() {
		// Bail if the connection is not working.
		if ( ! $this->is_connection_working() ) {
			return false;
		}

		// Remove crashed dropin.
		@unlink( WP_CONTENT_DIR . '/object-cache-crashed.php' );

		if ( ! function_exists( 'wp_generate_password' ) ) {
			require_once ABSPATH . '/wp-includes/pluggable.php';
		}

		// The new object cache.
		$new_object_cache = str_replace(
			'SG_OPTIMIZER_CACHE_KEY_SALT',
			str_replace( ' ', '', \wp_generate_password( 64, true, true ) ),
			file_get_contents( \SiteGround_Optimizer\DIR . '/templates/memcached.tpl' )
		);

		// Write the new obejct cache in the cache file.
		$result = file_put_contents(
			$this->get_object_cache_file(),
			$new_object_cache
		);

		return boolval( $result );
	}

	/**
	 * Remove the object-cache.php file.
	 *
	 * @since 5.0.0
	 */
	public function remove_memcached_dropin() {
		$dropin = $this->dropin_exists();

		// Enable the memcache if the file is not readable.
		if ( false !== $dropin ) {
			// Delete the file.
			$is_removed = unlink( $dropin );

			if ( false === $is_removed ) {
				// Enable memcache if the dropin cannot be removed.
				Options::enable_option( 'siteground_optimizer_enable_memcached' );

				return false;
			}
		}

		return true;
	}

	/**
	 * Check if there are any options that should be excluded from the memcache.
	 *
	 * @since  5.6.1
	 *
	 * @param  array $alloptions Array of all autoload options.
	 *
	 * @return array             Array without excluded autload options.
	 */
	public function maybe_exclude( $alloptions ) {
		// Bail if the excludes files doesn't exists.
		if ( ! file_exists( self::EXCLUDES_FILENAME ) ) {
			return $alloptions;
		}

		// Get the content of the excludes file.
		$excludes = $this->get_excludes_content();

		// Bail if the excludes file is empty.
		if ( empty( $excludes ) ) {
			return $alloptions;
		}

		foreach ( $excludes as $option ) {
			// Bail if the option name is empty.
			if ( empty( $option ) ) {
				continue;
			}

			// Unset the option from the cache.
			unset( $alloptions[ $option ] );
		}

		// Return the options.
		return $alloptions;
	}

	/**
	 * Prepare memcache excludes.
	 *
	 * @since  5.6.1
	 */
	public function prepare_memcache_excludes() {
		global $wp_filesystem;
		// Bail if the crashed file doesn't exists.
		if ( ! $wp_filesystem->exists( self::CRASHED_FILENAME ) ) {
			return;
		}

		// Remove the error flag file.
		$wp_filesystem->delete( self::CRASHED_FILENAME );

		// Create the excludes file if doesn't exists.
		if ( ! file_exists( self::EXCLUDES_FILENAME ) ) {
			$wp_filesystem->touch( self::EXCLUDES_FILENAME );
		}

		// Get the content of the excludes file.
		$excludes = $this->get_excludes_content();

		// Load the wpdb.
		global $wpdb;

		// Get the biggest option from the database.
		$result = $wpdb->get_results( "
			SELECT option_name
			FROM $wpdb->options
			WHERE autoload = 'yes'
			AND option_name NOT IN ( " . implode( ',', array_map( function( $item ) {
				return "'" . esc_sql( $item ) . "'";
			}, $excludes )
			) . " )
			ORDER BY LENGTH(option_value) DESC
			LIMIT 1"
		);

		// Bail if the query doesn't return results.
		if ( empty( $result[0]->option_name ) ) {
			return;
		}

		// Add the option to the exclude list.
		$excludes[] = $result[0]->option_name;

		// Open the exclude list file.
		$handle = fopen( self::EXCLUDES_FILENAME, 'a' );

		// Write the option to the exclude list.
		fputcsv( $handle, array( $result[0]->option_name ) );

		// Close the file.
		fclose( $handle );
	}

	/**
	 * Get the content of the excludes file.
	 *
	 * @since  5.6.1
	 *
	 * @return array Content of the excludes file in array.
	 */
	public function get_excludes_content() {
		// Get the content of the excludes file.
		$excludes_content = file_get_contents( self::EXCLUDES_FILENAME );

		// Convert the csv to array.
		return str_getcsv( $excludes_content, "\n" );
	}

	/**
	 * Enable memcached.
	 *
	 * @since  @version
	 */
	public function enable_memcache() {
		// Bail if we cannot create a dropin.
		if ( ! $this->create_memcached_dropin() ) {
			return false;
		}

		Options::enable_option( 'siteground_optimizer_enable_memcached' );

		// Remove notices.
		Options::disable_option( 'siteground_optimizer_memcache_notice' );
		Options::disable_option( 'siteground_optimizer_memcache_crashed' );
		Options::disable_option( 'siteground_optimizer_memcache_dropin_crashed' );

		// Remove the transient if woo is present.
		if ( class_exists( 'WooCommerce' ) ) {
			delete_transient( 'wc_attribute_taxonomies' );
		}

		return true;
	}

	/**
	 * Disable memcached.
	 *
	 * @since  @version
	 */
	public function disable_memcache() {
		// First disable the option.
		$result = Options::disable_option( 'siteground_optimizer_enable_memcached' );

		// True if the option has been disabled and the dropin doesn't exist.
		if ( ! $this->dropin_exists() ) {
			return true;
		}

		// Try to remove the dropin.
		$is_dropin_removed = $this->remove_memcached_dropin();

		// Remove notices.
		Options::disable_option( 'siteground_optimizer_memcache_notice' );
		Options::disable_option( 'siteground_optimizer_memcache_crashed' );
		Options::disable_option( 'siteground_optimizer_memcache_dropin_crashed' );

		// Remove the transient if woo is present.
		if ( class_exists( 'WooCommerce' ) ) {
			delete_transient( 'wc_attribute_taxonomies' );
		}

		// True if the droping has been removed.
		if ( $is_dropin_removed ) {
			return true;
		}

		// Bail if the dropin could not be removed.
		return false;
	}
}
