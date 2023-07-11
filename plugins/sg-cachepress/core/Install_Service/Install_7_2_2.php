<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Memcache\Memcache;

class Install_7_2_2 extends Install {
	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 7.2.2
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '7.2.2';

	/**
	 * Run the install procedure.
	 *
	 * @since 7.2.2
	 */
	public function install() {
		// Update database optimization option.
		$this->update_db_optimization_option();

		// Update memcached droppin.
		if ( Options::is_enabled( 'siteground_optimizer_enable_memcached' ) ) {
			$memcached = new Memcache();
			$memcached->remove_memcached_dropin();
			$memcached->create_memcached_dropin();
		}
	}

	/**
	 * Update database optimization option value.
	 *
	 * @since 7.2.2
	 */
	public function update_db_optimization_option() {
		$database_optimization = get_option( 'siteground_optimizer_database_optimization', 0 );

		// Check if the database optimization is enabled.
		if ( ! is_array( $database_optimization ) && 1 === intval( $database_optimization ) ) {
			// New option structure.
			$db_optimization_methods = array(
				'delete_auto_drafts',
				'delete_revisions',
				'delete_trashed_posts',
				'delete_spam_comments',
				'delete_trash_comments',
				'expired_transients',
				'optimize_tables',
			);

			// Update the new option with all optimizations enabled.
			update_option( 'siteground_optimizer_database_optimization', $db_optimization_methods );

			return;
		}

		if ( ! is_array( $database_optimization ) && 0 === intval( $database_optimization ) ) {
			update_option( 'siteground_optimizer_database_optimization', array() );
		}
	}
}
