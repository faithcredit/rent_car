<?php

/**
 * Provide a admin area dashboard view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://catchplugins.com
 * @since      1.0.0
 *
 * @package    Generate_Child_Theme
 * @subpackage Generate_Child_Theme/admin/partials
 */
?>

<div id="generate-child-theme" class="gct-main" aria-label="Main Content">
    <div class="content-wrapper" >
        <div class="header">
            <h3><?php esc_html_e( 'Settings', 'generate-child-theme' ); ?></h3>
        </div><!-- .header -->

        <div class="content">
            <form action="admin-post.php" method="post" id="create_child_form">
                <input type="hidden" name="action" value="create" />
                <table class="form-table">
                    <tr>
                        <th>
                            <?php esc_html_e( 'Select Parent Theme:', 'generate-child-theme' ); ?>
                        </th>
                        <td>
                            <?php $theme_list = Generate_Child_Theme::get_theme_list();
                             ?>
                            <select name="parent_template">
                                <?php
                                    foreach ( $theme_list as $key => $value ) {
                                ?>
                                <option value="<?php esc_attr_e( $key ); ?>"><?php esc_html_e( $value ); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php esc_html_e( 'Child theme\'s name:', 'generate-child-theme' ); ?>
                        </th>
                        <td>
                            <input type="text" name="child_theme_name" class="widefat" required placeholder="<?php esc_html_e('Your child theme\'s name'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php esc_html_e( 'Description:', 'generate-child-theme' ); ?>
                        </th>
                        <td>
                            <textarea name="child_theme_description" class="widefat" placeholder="<?php esc_html_e('Brief description of your child theme.'); ?>" ></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php esc_html_e( 'Author:', 'generate-child-theme' ); ?>
                        </th>
                        <td>
                            <input type="text" name="child_theme_author" class="widefat" placeholder="<?php esc_html_e('Anonymous'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php esc_html_e( 'Version:', 'generate-child-theme' ); ?>
                        </th>
                        <td>
                            <input type="text" name="child_theme_version" class="widefat" placeholder="1.0" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="submit">
                                <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Generate', 'generate-child-theme' ); ?>" />
                            </p>
                        </td>
                    </tr>
                </table>
            </form>
        </div><!-- .content -->
    </div><!-- .content-wrapper -->

</div> <!-- Main Content-->
