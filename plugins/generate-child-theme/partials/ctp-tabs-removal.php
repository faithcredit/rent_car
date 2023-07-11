<?php
/**
 * ctp_register_settings
 * CTP Register Settings
 */
if ( ! function_exists( 'ctp_register_settings' ) ) {
	function ctp_register_settings() {
		// register_setting( $option_group, $option_name, $sanitize_callback )
		register_setting(
			'ctp-group',
			'ctp_options',
			array()
		);
	}
}
add_action( 'admin_init', 'ctp_register_settings' );

if ( ! function_exists( 'ctp_get_options' ) ) {
	/**
	 * Returns the options array for ctp_get options
	 *
	 *  @since    1.3
	 */
	function ctp_get_options() {
		$defaults = ctp_default_options();
		$options  = get_option( 'ctp_options', $defaults );

		return wp_parse_args( $options, $defaults );
	}
}

if ( ! function_exists( 'ctp_default_options' ) ) {
	/**
	 * Return array of default options
	 *
	 * @since     1.3
	 * @return    array    default options.
	 */
	function ctp_default_options( $option = null ) {
		$default_options['theme_plugin_tabs'] = 1;
		if ( null == $option ) {
			return apply_filters( 'ctp_options', $default_options );
		} else {
			return $default_options[ $option ];
		}
	}
}

if ( ! function_exists( 'ctp_switch' ) ) {
	/**
	 * Return $string
	 *
	 * @since     1.3
	 * @return    $string    1 or 2.
	 */
	function ctp_switch() {
		// Check nonce before doing and changes.
		if ( ! check_ajax_referer( 'ctp_tabs_nonce', 'security', false ) ) {
			wp_die( esc_html__( 'Invalid Nonce', 'generate-child-theme' ) );
		} else {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Permission denied!', 'generate-child-theme' ) );
			}
			$value = ( 'true' == $_POST['value'] ) ? 1 : 0;

			$option_name = $_POST['option_name'];

			$option_value = ctp_get_options();

			$option_value[ $option_name ] = $value;

			if ( update_option( 'ctp_options', $option_value ) ) {
				echo $value;
			} else {
				esc_html_e( 'Connection Error. Please try again.', 'generate-child-theme' );
			}
		}
		wp_die(); // this is required to terminate immediately and return a proper response
	}
}
add_action( 'wp_ajax_ctp_switch', 'ctp_switch' );
