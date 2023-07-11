<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       catchplugins.com
 * @since      1.0.0
 *
 * @package    Generate_Child_Theme
 * @subpackage Generate_Child_Theme/admin/partials
 */

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Generate Child Theme', 'generate-child-theme' ); ?></h1>
    <div id="plugin-description">
        <p><?php esc_html_e( 'Create child themes of any WordPress themes effortlessly with Generate Child Theme', 'generate-child-theme' ); ?></p>
    </div>
    <div class="catchp-content-wrapper">
        <div class="catchp_widget_settings">

            <h2 class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" id="dashboard-tab" href="#dashboard"><?php esc_html_e( 'Dashboard', 'generate-child-theme' ); ?></a>
                <a class="nav-tab" id="features-tab" href="#features"><?php esc_html_e( 'Features', 'generate-child-theme' ); ?></a>
            </h2>

            <div id="dashboard" class="wpcatchtab  nosave active">

                <?php require_once plugin_dir_path( dirname( __FILE__ ) ) . '/partials/dashboard-display.php'; ?>

                <div id="ctp-switch" class="content-wrapper col-3 generate-child-theme-main">
                    <div class="header">
                        <h2><?php esc_html_e( 'Catch Themes & Catch Plugins Tabs', 'generate-child-theme' ); ?></h2>
                    </div> <!-- .Header -->

                    <div class="content">

                        <p><?php echo esc_html__( 'If you want to turn off Catch Themes & Catch Plugins tabs option in Add Themes and Add Plugins page, please uncheck the following option.', 'generate-child-theme' ); ?>
                        </p>
                        <table>
                            <tr>
                                <td>
                                    <?php echo esc_html__( 'Turn On Catch Themes & Catch Plugin tabs', 'generate-child-theme' );  ?>
                                </td>
                                <td>
                                    <?php $ctp_options = ctp_get_options(); ?>
                                    <div class="module-header <?php echo $ctp_options['theme_plugin_tabs'] ? 'active' : 'inactive'; ?>">
                                        <div class="switch">
                                            <input type="hidden" name="ctp_tabs_nonce" id="ctp_tabs_nonce" value="<?php echo esc_attr( wp_create_nonce( 'ctp_tabs_nonce' ) ); ?>" />
                                            <input type="checkbox" id="ctp_options[theme_plugin_tabs]" class="ctp-switch" rel="theme_plugin_tabs" <?php checked( true, $ctp_options['theme_plugin_tabs'] ); ?> >
                                            <label for="ctp_options[theme_plugin_tabs]"></label>
                                        </div>
                                        <div class="loader"></div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div><!-- #ctp-switch -->

            </div><!-- .dashboard -->

            <div id="features" class="wpcatchtab save">
                <div class="content-wrapper col-3">
                    <div class="header">
                        <h3><?php esc_html_e( 'Features', 'generate-child-theme' ); ?></h3>

                    </div><!-- .header -->
                    <div class="content">
                        <ul class="catchp-lists">

                            <li>
                                <strong><?php esc_html_e( 'Supports all themes on WordPress', 'generate-child-theme' ); ?></strong>
                                <p><?php esc_html_e( 'You don’t have to worry if you have a slightly different or complicated theme installed on your website. It supports all the themes on WordPress and makes your website more striking and playful.', 'generate-child-theme' ); ?></p>
                            </li>

                            <li>
                                <strong><?php esc_html_e( 'Lightweight', 'generate-child-theme' ); ?></strong>
                                <p><?php esc_html_e( 'It is extremely lightweight. You do not need to worry about it affecting the space and speed of your website.', 'generate-child-theme' ); ?></p>
                            </li>

                            <li>
                                <strong><?php esc_html_e( 'Super Simple to Set Up', 'generate-child-theme' ); ?></strong>
                                <p><?php esc_html_e( 'It is super easy to set up. Even the beginners can set it up easily and also, you do not need to have any coding knowledge. Just install, activate, customize it your way and enjoy the plugin.', 'generate-child-theme' ); ?></p>
                            </li>

                            <li>
                                <strong><?php esc_html_e( 'Lesser Configuration', 'generate-child-theme' ); ?></strong>
                                <p><?php esc_html_e( 'The plugin will work out of the box and only provide you with few configuration option. This means lesser workload for you. You can create your child themes within a few minutes in just a few steps.', 'generate-child-theme' ); ?></p>
                            </li>

                            <li>
                                <strong><?php esc_html_e( 'Incredible Support', 'generate-child-theme' ); ?></strong>
                                <p><?php esc_html_e( 'We have a great line of support team and support documentation. You do not need to worry about how to use the plugins we provide, just refer to our Tech Support Forum. Further, if you need to do advanced customization to your website, you can always hire our theme customizer!', 'generate-child-theme' ); ?></p>
                            </li>

                        </ul>
                    </div><!-- .content -->
                </div><!-- catchp-widget-box -->
            </div> <!-- Featured -->


        </div><!-- .catchp_widget_settings -->


        <?php require_once plugin_dir_path( dirname( __FILE__ ) ) . '/partials/sidebar.php'; ?>
    </div> <!-- .catchp-content-wrapper -->

    <?php require_once plugin_dir_path( dirname( __FILE__ ) ) . '/partials/footer.php'; ?>
</div><!-- .wrap -->
