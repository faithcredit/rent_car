<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapIO;

$global = DUP_PRO_Global_Entity::getInstance();
?>
<form id="dup-settings-form" action="<?php echo ControllersManager::getCurrentLink(); ?>" method="post" data-parsley-validate>
    <?php require('hidden.fields.widget.php'); ?>

    <!-- ===============================
    GENERAL SETTINGS -->
    <table class="form-table">            
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Storage"); ?></label></th>
            <td>
                <?php DUP_PRO_U::esc_html_e("Full Path"); ?>:
                <?php echo SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH); ?><br/><br/>
                <input 
                    type="checkbox" 
                    name="_storage_htaccess_off" 
                    id="_storage_htaccess_off" 
                    value="1"
                    <?php checked($global->storage_htaccess_off); ?> 
                >
                <label for="_storage_htaccess_off">
                    <?php DUP_PRO_U::esc_html_e("Disable .htaccess File In Storage Directory") ?> 
                </label>
                <p class="description">
                    <?php DUP_PRO_U::esc_html_e("Disable if issues occur when downloading installer/archive files."); ?>
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Max Retries"); ?></label></th>
            <td>
                <input 
                    class="dup-narrow-input" 
                    type="text" 
                    name="max_storage_retries" 
                    id="max_storage_retries" 
                    data-parsley-required data-parsley-min="0" 
                    data-parsley-type="number" 
                    data-parsley-errors-container="#max_storage_retries_error_container" 
                    value="<?php echo $global->max_storage_retries; ?>" 
                >
                <div id="max_storage_retries_error_container" class="duplicator-error-container"></div>
                <p class="description">
                    <?php DUP_PRO_U::esc_html_e('Max upload/copy retries to attempt after failure encountered.'); ?>
                </p>
            </td>
        </tr>
    </table>
    <p class="submit dpro-save-submit">
        <input type="submit" name="submit" id="submit" class="button-primary" value="<?php DUP_PRO_U::esc_attr_e('Save Storage Settings') ?>" style="display: inline-block;" />
    </p>
</form>