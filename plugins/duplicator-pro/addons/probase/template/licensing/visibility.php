<?php

/**
 * @package Duplicator
 */

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Addons\ProBase\LicensingController;
use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */

$global = DUP_PRO_Global_Entity::getInstance();
?>
<h3 class="title"><?php DUP_PRO_U::esc_html_e("Key Visibility") ?> </h3>
<small>
    <?php
    DUP_PRO_U::esc_html_e(
        "This is an optional setting that prevents the 'License Key' from being copied. " .
        "Select the desired visibility mode, enter a password and hit the 'Change Visibility' button."
    );
    echo '<br/>';
    DUP_PRO_U::esc_html_e("Note: the password can be anything, it does not have to be the same as the WordPress user password.");
    ?>
</small>
<hr size="1" />
<form
    id="dup-license-visibility-form"
    action="<?php echo ControllersManager::getCurrentLink(); ?>"
    method="post"
    data-parsley-validate
>
    <?php $tplData['actions'][LicensingController::ACTION_CHANGE_VISIBILITY]->getActionNonceFileds(); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Visibility"); ?></label></th>
            <td>
                <label class="margin-right-1">
                    <input
                        type="radio"
                        name="license_key_visible"
                        value="<?php echo License::VISIBILITY_ALL;?>"
                        onclick="DupPro.Licensing.VisibilityTemporary(<?php echo License::VISIBILITY_ALL;?>);"
                        <?php checked($global->license_key_visible, License::VISIBILITY_ALL); ?>
                    >
                    <?php DUP_PRO_U::esc_html_e("License Visible"); ?>
                </label>
                <label class="margin-right-1">
                    <input
                        type="radio"
                        name="license_key_visible"
                        value="<?php echo License::VISIBILITY_INFO;?>"
                        onclick="DupPro.Licensing.VisibilityTemporary(<?php echo License::VISIBILITY_INFO;?>);"
                        <?php checked($global->license_key_visible, License::VISIBILITY_INFO); ?>
                    >
                    <?php DUP_PRO_U::esc_html_e("Info Only"); ?>
                </label>
                <label>
                    <input
                        type="radio"
                        name="license_key_visible"
                        value="<?php echo License::VISIBILITY_NONE;?>"
                        onclick="DupPro.Licensing.VisibilityTemporary(<?php echo License::VISIBILITY_NONE;?>);"
                        <?php checked($global->license_key_visible, License::VISIBILITY_NONE); ?>
                    >
                    <?php DUP_PRO_U::esc_html_e("License Invisible"); ?>
                </label>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Password"); ?></label></th>
            <td>
                <input type="password" class="dup-wide-input" name="_key_password" id="_key_password" size="50" />
            </td>
        </tr>
        <?php if ($global->license_key_visible == License::VISIBILITY_ALL) { ?>
            <tr valign="top">
                <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Retype Password"); ?></label></th>
                <td>
                    <input
                        type="password"
                        class="dup-wide-input"
                        name="_key_password_confirmation"
                        id="_key_password_confirmation"
                        data-parsley-equalto="#_key_password"
                        size="50"
                    >
                </td>
            </tr>
        <?php } ?>
        <tr valign="top">
            <th scope="row"></th>
            <td>
                <button
                    class="button"
                    id="show_hide"
                    onclick="DupPro.Licensing.ChangeKeyVisibility(); return false;"
                >
                    <?php  _e('Change Visibility', 'duplicator-pro'); ?>
                </button>
            </td>
        </tr>
    </table>
</form>
