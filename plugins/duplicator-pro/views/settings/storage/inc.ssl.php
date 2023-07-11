<?php
defined("ABSPATH") or die("");

use Duplicator\Core\Controllers\ControllersManager;

$global = DUP_PRO_Global_Entity::getInstance();
?>
<form id="dup-settings-form" action="<?php echo ControllersManager::getCurrentLink(); ?>" method="post" data-parsley-validate>
    <?php require('hidden.fields.widget.php'); ?>
    <!-- ===============================
    SSL SETTINGS -->
    <p class="description" style="color:maroon">
        <?php DUP_PRO_U::esc_html_e("Do not modify SSL settings unless you know the expected result or have talked to support."); ?>
    </p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Use server's SSL certificates"); ?></label></th>
            <td>
                <input 
                    type="checkbox" 
                    name="ssl_useservercerts" 
                    id="ssl_useservercerts" 
                    value="1"
                    <?php checked($global->ssl_useservercerts); ?> 
                >
                <p class="description">
                    <?php
                    DUP_PRO_U::esc_html_e("To use server's SSL certificates please enble it. By default Duplicator Pro uses By default uses its own store of SSL certificates to verify the identity of remote storage sites.");
                    ?>
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Disable verification of SSL certificates"); ?></label></th>
            <td>
                <input 
                    type="checkbox" 
                    name="ssl_disableverify" 
                    id="ssl_disableverify" 
                    value="1"
                    <?php checked($global->ssl_disableverify); ?> 
                >
                <p class="description">
                    <?php
                    DUP_PRO_U::esc_html_e("To disable verification of a host and the peer's SSL certificate.");
                    ?>
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Use IPv4 only"); ?></label></th>
            <td>
                <input
                    type="checkbox" 
                    name="ipv4_only" 
                    id="ipv4_only" 
                    value="1"
                    <?php checked($global->ipv4_only); ?> 
                >
                <p class="description">
                    <?php
                    DUP_PRO_U::esc_html_e("To use IPv4 only, which can help if your host has a broken IPv6 setup (currently only supported by Google Drive)");
                    ?>
                </p>
            </td>
        </tr>
    </table>
    <p class="submit dpro-save-submit">
        <input type="submit" name="submit" id="submit" class="button-primary" value="<?php DUP_PRO_U::esc_attr_e('Save Storage Settings') ?>" style="display: inline-block;" />
    </p>
</form>