<?php
namespace SiteGround_Optimizer\Admin;

use SiteGround_Optimizer;
use SiteGround_Optimizer\Rest\Rest;
use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Multisite\Multisite;
use SiteGround_Optimizer\Modules\Modules;
use SiteGround_Optimizer\Options\Options;
use SiteGround_i18n\i18n_Service;
use SiteGround_Helper\Helper_Service;

/**
 * Handle all hooks for our custom admin page.
 */
class Admin {

	/**
	 * Handle all hooks for our custom admin page.
	 *
	 * @since  6.0.0
	 *
	 * @var array
	 */
	public $subpages = array(
		'sgo_caching'     => 'Caching',
		'sgo_environment' => 'Environment',
		'sgo_frontend'    => 'Frontend',
		'sgo_media'       => 'Media',
		'sgo_analysis'    => 'Speed Test',
	);

	public $multisite_permissions = array(
		'sgo_caching'     => 'siteground_optimizer_supercacher_permissions',
		'sgo_frontend'    => 'siteground_optimizer_frontend_permissions',
		'sgo_media'       => 'siteground_optimizer_images_permissions',
		'sgo_environment' => 'siteground_optimizer_environment_permissions',
	);

	public $dequeued_styles = array(
		'auxin-front-icon', // Phlox Theme.
		'mks_shortcodes_simple_line_icons', // Meks Flexible Shortcodes.
		'onthego-admin-styles', // Toolset Types
		'foogra-icons', // Foogra Theme
	);

	/**
	 * Get the subpages id.
	 *
	 * @since  6.0.0
	 *
	 * @return array The subpages id's array.
	 */
	public function get_plugin_page_ids() {
		$subpage_ids = array(
			'toplevel_page_sg-cachepress',
			'toplevel_page_sg-cachepress-network',
		);

		foreach ( $this->subpages as $id => $title ) {

			$subpage_ids[] = 'sg-optimizer_page_' . $id . '';
			$subpage_ids[] = 'sg-optimizer_page_' . $id . '-network';
		}

		return $subpage_ids;
	}

	/**
	 * Check if it's a multisite, but the single site
	 * has no permisions to edit optimizer settings.
	 *
	 * @since  5.0.0
	 *
	 * @return boolean True if there are no permissions, false otherwise.
	 */
	public function is_multisite_without_permissions() {
		if (
			is_multisite() &&
			0 === (int) get_site_option( 'siteground_optimizer_supercacher_permissions', 0 ) &&
			0 === (int) get_site_option( 'siteground_optimizer_frontend_permissions', 0 ) &&
			0 === (int) get_site_option( 'siteground_optimizer_images_permissions', 0 ) &&
			0 === (int) get_site_option( 'siteground_optimizer_environment_permissions', 0 )
		) {

			return true;
		}

		return false;
	}

	/**
	 * Hide all errors and notices on our custom dashboard.
	 *
	 * @since  1.0.0
	 */
	public function hide_errors_and_notices() {
		// Hide all error in our page.
		if (
			isset( $_GET['page'] ) &&
			array_key_exists( $_GET['page'], $this->subpages ) // phpcs:ignore
		) {
			remove_all_actions( 'network_admin_notices' );
			remove_all_actions( 'user_admin_notices' );
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );

			error_reporting( 0 );
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 5.0.0
	 */
	public function enqueue_styles() {
		// Bail if we are on different page.
		if ( false === $this->is_plugin_page() ) {
			return;
		}

		// Dequeue conflicting styles.
		foreach ( $this->dequeued_styles as $style ) {
			wp_dequeue_style( $style );
		}

		wp_enqueue_style(
			'siteground-optimizer-admin',
			\SiteGround_Optimizer\URL . '/assets/css/main.min.css',
			array(),
			\SiteGround_Optimizer\VERSION,
			'all'
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 5.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'siteground-optimizer-dashboard',
			\SiteGround_Optimizer\URL . '/assets/js/admin.js',
			array( 'jquery' ), // Dependencies.
			\SiteGround_Optimizer\VERSION,
			true
		);

		// Bail if we are on different page.
		if ( false === $this->is_plugin_page() ) {
			return;
		}

		wp_enqueue_media();

		$path = is_network_admin() ? 'optimizer.bundle.js' : 'main.min.js';
		// Enqueue the optimizer script.
		wp_enqueue_script(
			'siteground-optimizer-admin',
			\SiteGround_Optimizer\URL . '/assets/js/' . $path,
			array( 'jquery' ), // Dependencies.
			\SiteGround_Optimizer\VERSION,
			true
		);
	}

	/**
	 * Hide the global memcache notice.
	 *
	 * @since  5.0.0
	 */
	public function hide_memcache_notice() {
		update_option( 'siteground_optimizer_memcache_notice', 0 );
		update_site_option( 'siteground_optimizer_memcache_notice', 0 );
	}

	/**
	 * Hide the global blocking plugins notice.
	 *
	 * @since  5.0.0
	 */
	public function hide_blocking_plugins_notice() {
		update_option( 'siteground_optimizer_blocking_plugins_notice', 0 );
		update_site_option( 'siteground_optimizer_blocking_plugins_notice', 0 );
	}

	/**
	 * Hide the global cache plugins notice.
	 *
	 * @since  5.0.0
	 */
	public function hide_cache_plugins_notice() {
		update_option( 'siteground_optimizer_cache_plugins_notice', 0 );
		update_site_option( 'siteground_optimizer_cache_plugins_notice', 0 );
	}


	/**
	 * Display admin error when the memcache is disabled.
	 *
	 * @since  5.0.0
	 */
	public function memcache_notice() {
		// Get the option.
		$show_notice = (int) get_site_option( 'siteground_optimizer_memcache_notice', 0 );

		// Bail if the current user is not admin or if we sholdn't  display notice.
		if (
			! is_admin() ||
			0 === $show_notice ||
			$this->is_plugin_page() ||
			! current_user_can( 'administrator' )
		) {
			return;
		}

		$memcache_crashed = (int) get_site_option( 'siteground_optimizer_memcache_crashed', 0 );

		$class   = 'notice notice-error';
		$message = __( 'SiteGround Optimizer has detected that Memcached was turned off. If you want to use it, please enable it from your SiteGround control panel first.', 'sg-cachepress' );

		if ( 1 === $memcache_crashed ) {
			$message = __( 'Your site tried to store a single object above 1MB in Memcached which is above the limitation and will actually slow your site rather than speed it up. Please, check your Options table for obsolete data before enabling it again. Note that the service will be automatically disabled if such error occurs again.', 'sg-cachepress' );
		}

		printf(
			'<div class="%1$s" style="position: relative"><p>%2$s</p><button type="button" class="notice-dismiss dismiss-memcache-notice" data-link="%3$s"><span class="screen-reader-text">Dismiss this notice.</span></button></div>',
			esc_attr( $class ),
			esc_html( $message ),
			admin_url( 'admin-ajax.php?action=dismiss_memcache_notice' )
		);
	}

	/**
	 * Register the top level page into the WordPress admin menu.
	 *
	 * @since @version
	 */
	public function add_plugin_pages() {
		if ( is_multisite() && ! is_network_admin() && $this->is_multisite_without_permissions() ) {
			return;
		}

		\add_menu_page(
			__( 'SiteGround Optimizer', 'sg-optimizer' ), // Page title.
			__( 'SG Optimizer', 'sg-cachepress' ), // Menu item title.
			'manage_options',
			\SiteGround_Optimizer\PLUGIN_SLUG,   // Page slug.
			array( $this, 'render' ),
			\SiteGround_Optimizer\URL . '/assets/images/icon.svg'
		);

		if ( is_network_admin() ) {
			return;
		}

		foreach ( $this->subpages as $id => $title ) {

			if (
				is_multisite() &&
				! is_network_admin() &&
				array_key_exists( $id, $this->multisite_permissions ) &&
				0 === intval( get_site_option( $this->multisite_permissions[ $id ], 0 ) )
			) {
				continue;
			}

			add_submenu_page(
				\SiteGround_Optimizer\PLUGIN_SLUG,   // Parent slug.
				__( $title, 'sg-cachepress' ), // phpcs:ignore
				__( $title, 'sg-cachepress' ), // phpcs:ignore
				'manage_options',
				$id,
				array( $this, 'render' )
			);
		}
	}

	/**
	 * Add styles to WordPress admin head.
	 *
	 * @since  5.2.0
	 */
	public function admin_print_styles() {
		echo '<style>.toplevel_page_sg-cachepress.menu-top .wp-menu-image img { width:20px; display:inline;} </style>';

		// Bail if we are on different page.
		if ( ! $this->is_plugin_page() ) {
			return;
		}

		$current_screen = \get_current_screen();

		echo '<style>.notice, .sg-switch__input { display:none!important; } #sg-optimizer-app { height: 100%; min-height: 100vh; } #wpcontent{background:var(--background-main)}#adminmenu .wp-menu-image img{display:inline}#sg-optimizer-app{min-height:100vh}#sg-optimizer-app h1,#sg-optimizer-app h2,#sg-optimizer-app h3,#sg-optimizer-app h4,#sg-optimizer-app h5,#sg-optimizer-app h6,#sg-optimizer-app li,#sg-optimizer-app p{margin:0}#sg-optimizer-app input[type=checkbox],#sg-optimizer-app input[type=radio]{display:none}.sg-notifications h1,.sg-notifications h2,.sg-notifications h3,.sg-notifications h4,.sg-notifications h5,.sg-notifications h6,.sg-notifications li,.sg-notifications p{margin:0}</style>';

		$id = str_replace( ' ', '', ucwords( str_replace(
			array(
				'sg-optimizer_page_',
				'_network',
				'sgo_',
				'-',
			),
			array(
				'',
				'',
				'',
				' ',
			),
			$current_screen->id
		)));

		if ( 'TOPLEVEL_PAGE_SGCACHEPRESS' === strtoupper( $id ) ) {
			$id = 'Dashboard';
		}

		foreach ( $this->subpages as $subpage => $title ) {
			$navigation[ $subpage ] = admin_url( 'admin.php?page=' . $subpage );
		}

		$i18n_service = new i18n_Service( 'sg-cachepress' );

		$data = array(
			'rest_base'           => untrailingslashit( get_rest_url( null, '/' ) ),
			'home_url'            => Helper_Service::get_home_url(),
			'is_cron_disabled'    => Helper_Service::is_cron_disabled(),
			'is_siteground'       => Helper_Service::is_siteground(),
			'locale'              => $i18n_service->get_i18n_data_json(),
			'update_timestamp'    => get_option( 'siteground_optimizer_update_timestamp', 0 ),
			'is_shop'             => is_plugin_active( 'woocommerce/woocommerce.php' ) ? 1 : 0,
			'localeSlug'          => join( '-', explode( '_', \get_user_locale() ) ),
			'wp_nonce'            => wp_create_nonce( 'wp_rest' ),
			'is_uploads_writable' => (int) Helper::check_upload_dir_permissions(),
			'network_settings'    => array(
				'is_network_admin' => intval( is_network_admin() ),
				'is_multisite'     => intval( is_multisite() ),
			),
			'data_consent_popup'  => $this->get_popup_settings(),
			'config'              => array(
				'assetsPath' => SiteGround_Optimizer\URL . '/assets/images',
			),
			'navigation' => $navigation,
		);

		if ( ! is_network_admin() ) {
			echo '<script>window.addEventListener("load", function(){ SGOptimizer.init({ domElementId: "root", page: SGOptimizer.PAGE.' . $id . ',config:' . json_encode( $data ) . '})});</script>';
		} else {
			$data['rest_base'] = untrailingslashit( get_rest_url( null, Rest::REST_NAMESPACE ) );
			$data['modules'] = Modules::get_instance()->get_active_modules();
			$data['tabs'] = Modules::get_instance()->get_active_tabs();

			wp_localize_script( 'siteground-optimizer-admin', 'optimizerData', $data );
		}

		echo '<style>.toplevel_page_sg-optimizer.menu-top .wp-menu-image img { width:20px; } #wordfenceAutoUpdateChoice { display: none!important; } </style>';
	}

	/**
	 * Display the admin page.
	 *
	 * @since  5.0.0
	 */
	public function render() {
		echo is_network_admin() ? '<div id="sg-optimizer-app"></div>' : '<div id="root"></div>';
	}

	/**
	 * Reorder the submenu pages.
	 *
	 * @since  1.0.0
	 *
	 * @param   array $menu_order The WP menu order.
	 */
	public function reorder_submenu_pages( $menu_order ) {
		// Load the global submenu.
		global $submenu;

		if ( empty( $submenu['sg-cachepress'] ) ) {
			return $menu_order;
		}

		// Hide the dashboard page on Multisite applications.
		if ( is_multisite() ) {
			unset( $submenu['sg-cachepress'][0] );
			return $menu_order;
		}

		$submenu['sg-cachepress'][0][0] = __( 'Dashboard', 'sg-cachepress' );

		return $menu_order;
	}

	/**
	 * Check if this is the SG Cachepress page.
	 *
	 * @since  @version
	 *
	 * @return bool True/False
	 */
	public function is_plugin_page() {
		// Bail if the page is not an admin screen.
		if ( ! is_admin() ) {
			return false;
		}

		$current_screen = \get_current_screen();

		if ( in_array( $current_screen->id, $this->get_plugin_page_ids() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the popup configuration.
	 *
	 * @since  7.0.0
	 *
	 * @return array The popup settings.
	 */
	public function get_popup_settings() {
		$settings = array();

		$data_consent       = intval( get_option( 'siteground_data_consent', 0 ) );
		$email_consent      = intval( get_option( 'siteground_email_consent', 0 ) );
		$settings_optimizer = intval( get_option( 'siteground_settings_optimizer', 0 ) );

		if ( ! empty( $settings_optimizer ) ) {
			return array(
				'show_data_field'  => 0,
				'show_email_field' => 0,
			);
		}

		if ( Helper_Service::is_siteground() ) {
			if ( 1 === $data_consent ) {
				return array(
					'show_data_field'  => 0,
					'show_email_field' => 0,
				);
			}

			return array(
				'show_data_field'  => 1,
				'show_email_field' => 0,
			);
		}

		$settings = array();

		$settings['show_data_field'] = 0 === $data_consent ? 1 : 0;
		$settings['show_email_field'] = 0 === $email_consent ? 1 : 0;

		return $settings;
	}
}
