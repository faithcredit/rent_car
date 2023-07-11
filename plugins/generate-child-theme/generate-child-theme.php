<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              catchplugins.com
 * @since             1.0.0
 * @package           Generate_Child_Theme
 *
 * @wordpress-plugin
 * Plugin Name:       Generate Child Theme
 * Plugin URI:        catchplugins.com/plugins/generate-child-theme
 * Description:       Create child themes of any WordPress themes effortlessly with Generate Child Theme.
 * Version:           1.9
 * Author:            Catch Plugins
 * Author URI:        catchplugins.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       generate-child-theme
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define Version
define( 'GENERATECHILDTHEME_VERSION', '1.9' );

// The URL of the directory that contains the plugin
if ( ! defined( 'GENERATECHILDTHEME_URL' ) ) {
	define( 'GENERATECHILDTHEME_URL', plugin_dir_url( __FILE__ ) );
}


// The absolute path of the directory that contains the file
if ( ! defined( 'GENERATECHILDTHEME_PATH' ) ) {
	define( 'GENERATECHILDTHEME_PATH', plugin_dir_path( __FILE__ ) );
}

class Generate_Child_Theme {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_settings_menu' ) );
		add_action( 'admin_post_create', array( $this, 'process_create_form' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );

		if ( basename( $_SERVER['PHP_SELF'] ) == 'themes.php' && ! empty( $_REQUEST['ctcm_status'] ) ) {
			add_action( 'admin_notices', array( $this, 'showErrorNotice' ) );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function add_plugin_settings_menu() {
		add_menu_page(
			esc_html__( 'Generate Child Theme', 'generate-child-theme' ), //page title
			esc_html__( 'Generate Child Theme', 'generate-child-theme' ), //menu title
			'install_themes', //capability needed
			'generate-child-theme', //menu slug (and page query url)
			array( $this, 'generate_child_theme' ),
			'dashicons-admin-appearance',
			'99.01564'
		);
	}

	public function generate_child_theme() {
		$child_theme = false;
		if ( ! current_user_can( 'install_themes' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'generate-child-theme' ) );
		}

		require_once plugin_dir_path( __FILE__ ) . 'partials/generate-child-theme-admin-display.php';
	}

	public function process_create_form() {
		if ( ! empty( $_POST ) ) {
			$info                          = array();
			$info['parent_theme_template'] = sanitize_text_field( $_POST['parent_template'] );
			$info['theme_name']            = sanitize_text_field( $_POST['child_theme_name'] );
			$info['theme_description']     = empty( $_POST['child_theme_description'] ) ? 'Your description goes here' : sanitize_text_field( stripslashes( $_POST['child_theme_description'] ) );
			$info['theme_author']          = sanitize_text_field( $_POST['child_theme_author'] );
			$info['theme_version']         = empty( $_POST['child_theme_version'] ) ? '1.0' : sanitize_text_field( $_POST['child_theme_version'] );

			$result = $this->make_child_theme( $info );

			if ( is_wp_error( $result ) ) {
				// should show create child form again
				$this->_redirect(
					admin_url( 'themes.php?page=generate-child-theme' ),
					$result->get_error_message(),
					array(
						'theme_name'    => $info['theme_name'],
						'description'   => $info['theme_description'],
						'author_name'   => $info['theme_author'],
						'theme_version' => $info['theme_version'],
					)
				);
				return;
			} else {
				switch_theme( $result['parent_template'], $result['new_child_theme'] );
				// Redirect to themes page on success
				$this->_redirect( admin_url( 'themes.php' ), 'child_created' );
			}
		}
	}

	function add_plugin_meta_links( $meta_fields, $file ) {

		if ( $file == plugin_basename( __FILE__ ) ) {

			$meta_fields[] = "<a href='https://catchplugins.com/support-forum/forum/generate-child-theme/' target='_blank'>Support Forum</a>";
			$meta_fields[] = "<a href='https://wordpress.org/support/plugin/generate-child-theme/reviews#new-post' target='_blank' title='Rate'>
			        <i class='ct-rate-stars'>"
			  . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			  . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			  . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			  . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			  . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			  . '</i></a>';

			$stars_color = '#ffb900';

			echo '<style>'
				. '.ct-rate-stars{display:inline-block;color:' . $stars_color . ';position:relative;top:3px;}'
				. '.ct-rate-stars svg{fill:' . $stars_color . ';}'
				. '.ct-rate-stars svg:hover{fill:' . $stars_color . '}'
				. '.ct-rate-stars svg:hover ~ svg{fill:none;}'
				. '</style>';
		}

		return $meta_fields;
	}

	public function make_child_theme( $info ) {
		$data                  = explode( ':', $info['parent_theme_template'] );
		$parent_theme_name     = $data[1];
		$parent_theme_template = $data[0];
		$theme_root            = get_theme_root();
		$parent_theme_dir      = $theme_root . '/' . $parent_theme_template;
		$theme_name            = $info['theme_name'];
		$description           = $info['theme_description'];
		$author                = $info['theme_author'];
		$version               = $info['theme_version'];

		// Turn a theme name into a directory name
		$new_theme_name = sanitize_title( $theme_name );
		
		$theme_slug = str_replace( '-', '_', $new_theme_name );

		$new_child_theme_path = $theme_root . '/' . $new_theme_name;

		if ( file_exists( $new_child_theme_path ) ) {
			wp_die( esc_html__( 'The directory already exists', 'generate-child-theme' ) );
			return;
		}

		mkdir( $new_child_theme_path );

		// Make style.css
		ob_start();
		require plugin_dir_path( __FILE__ ) . 'templates/child-theme-css.php';
		$css = ob_get_clean();
		file_put_contents( $new_child_theme_path . '/style.css', $css );

		$function_prefix = $theme_slug;

		if ( preg_match('/^\d/', $function_prefix ) ) {
			$function_prefix = 'cp_' . $function_prefix; 
		}

		// Make functions.php
		$function_content = "<?php
/*
 * This is the child theme for {$parent_theme_name} theme, generated with Generate Child Theme plugin by catchthemes.
 *
 * (Please see https://developer.wordpress.org/themes/advanced-topics/child-themes/#how-to-create-a-child-theme)
 */
add_action( 'wp_enqueue_scripts', '{$function_prefix}_enqueue_styles' );
function {$function_prefix}_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style')
    );
}
/*
 * Your code goes below
 */";

		file_put_contents( $new_child_theme_path . '/functions.php', $function_content );

		// RTL support
		$rtl_theme = ( file_exists( $parent_theme_dir . '/rtl.css' ) )
			? $parent_theme_template
			: 'twentyseventeen'; //use the latest default theme rtl file
		ob_start();
		require plugin_dir_path( __FILE__ ) . 'templates/rtl-css.php';
		$css = ob_get_clean();
		file_put_contents( $new_child_theme_path . '/rtl.css', $css );

		// Copy screenshot
		if ( $screenshot_template = $this->get_screenshot( $parent_theme_dir ) ) {
			copy(
				$parent_theme_dir . '/' . $screenshot_template,
				$new_child_theme_path . '/' . $screenshot_template
			);
		} // removed grandfather screenshot check (use mshot instead, rly)

		// Make child theme an allowed theme (network enable theme)
		$allowed_themes                    = get_site_option( 'allowedthemes' );
		$allowed_themes[ $new_theme_name ] = true;
		update_site_option( 'allowedthemes', $allowed_themes );

		return array(
			'parent_template'       => $parent_theme_template,
			'parent_theme'          => $parent_theme_name,
			'new_child_theme'       => $new_theme_name,
			'new_child_theme_path'  => $new_child_theme_path,
			'new_child_theme_title' => $theme_name,
		);
	}

	public function get_screenshot( $directory ) {
		$screenshots = glob( $directory . '/screenshot.{png,jpg,jpeg,gif}', GLOB_BRACE );
		return ( empty( $screenshots ) ) ? false : basename( $screenshots[0] );
	}


	public function _redirect( $url, $status, $args = array() ) {
		$args['ctcm_status'] = $status;
		$args                = urlencode_deep( $args );
		wp_redirect( add_query_arg( $args, $url ) );
	}

	public function showErrorNotice() {
		switch ( $_GET['ctcm_status'] ) {
			case 'child_created': //SUCCESS: child theme created
				$type = 'updated'; //fade?
				$msg  = sprintf(
					__( 'Theme switched! <a href="%s">Click here to edit the child stylesheet</a>.', 'generate-child-theme' ),
					add_query_arg(
						urlencode_deep(
							array(
								'file'  => 'style.css',
								'theme' => get_stylesheet(),
							)
						),
						admin_url( 'theme-editor.php' )
					)
				);
				break;
			case 'create_failed': //ERROR: create file failed (probably due to permissions)
				$type = 'error';
				$msg  = sprintf(
					__( 'Failed to create file: %s', 'generate-child-theme' ),
					esc_html( $_GET['template'] )
				);
				break;
			default: //ERROR: it is a generic error message
				$type = 'error';
				$msg  = esc_html( $_GET['ctcm_status'] );
		}

		printf(
			'<div class="%s"><p>%s</p></div>',
			$type,
			$msg
		);
	}


	public function enqueue_styles() {
		if ( isset( $_GET['page'] ) && 'generate-child-theme' == $_GET['page'] ) {
			wp_enqueue_style( 'generate-child-theme', plugin_dir_url( __FILE__ ) . 'css/generate-child-theme.css', array(), '1.0', 'all' );
			wp_enqueue_style( 'generate-child-theme-tabs', plugin_dir_url( __FILE__ ) . 'css/admin-dashboard.css', array(), '1.0', 'all' );
		}
	}

	public function enqueue_scripts() {
		if ( isset( $_GET['page'] ) && 'generate-child-theme' == $_GET['page'] ) {
			wp_enqueue_script( 'minHeight', plugin_dir_url( __FILE__ ) . 'js/jquery.matchHeight.min.js', array( 'jquery' ), '1.0', false );
			wp_enqueue_script( 'generate-child-theme-js', plugin_dir_url( __FILE__ ) . 'js/generate-child-theme-admin.js', array( 'minHeight', 'jquery' ), '1.0', false );
		}
	}

	public function get_theme_list() {
		$themes = wp_get_themes();
		$list   = array();
		foreach ( $themes as $theme ) {
			if ( $theme->parent() === false ) {
				$list[ $theme['Template'] . ':' . $theme['Name'] ] = $theme['Name'];
			}
		}
		return $list;
	}

}

function generate_child_theme_run() {

	$plugin = new Generate_Child_Theme();
	$plugin;

}
generate_child_theme_run();

/* CTP tabs removal options */
require plugin_dir_path( __FILE__ ) . 'partials/ctp-tabs-removal.php';

$ctp_options = ctp_get_options();
if ( 1 == $ctp_options['theme_plugin_tabs'] ) {
	/* Adds Catch Themes tab in Add theme page and Themes by Catch Themes in Customizer's change theme option. */
	if ( ! class_exists( 'CatchThemesThemePlugin' ) && ! function_exists( 'add_our_plugins_tab' ) ) {
		require plugin_dir_path( __FILE__ ) . 'partials/CatchThemesThemePlugin.php';
	}
}
