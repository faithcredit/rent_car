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


$global                  = DUP_PRO_Global_Entity::getInstance();
$license_status          = License::getLicenseStatus(true);
$license_type            = License::getType();
$license_text_disabled   = false;
$activate_button_text    = __('Activate', 'duplicator-pro');
$license_status_text_alt = '';


switch ($license_status) {
    case License::STATUS_VALID:
        $license_status_style  = 'color:#509B18';
        $activate_button_text  = __('Deactivate', 'duplicator-pro');
        $license_text_disabled = true;

        $license_key = License::getLicenseKey();

        if (License::isValidOvrKey($license_key)) {
            $standard_key        = License::getStandardKeyFromOvrKey($license_key);
            $license_status_text = printf(__("Status: Active (Using license override for key %s)", 'duplicator-pro'), $standard_key);
        } else {
            $license_status_text  = '<b>' . __('Status: ', 'duplicator-pro') . '</b>' . __('Active', 'duplicator-pro');
            $license_status_text .= '<br/>';
            $license_status_text .= '<b>' . __('Expiration: ', 'duplicator-pro') . '</b>';
            $license_status_text .= License::getExpirationDate(get_option('date_format'));
            $expDays              = License::getExpirationDays();
            if ($expDays == 0) {
                $expDays = __('expired', 'duplicator-pro');
            } elseif ($expDays == PHP_INT_MAX) {
                $expDays = __('no expiration', 'duplicator-pro');
            } else {
                $expDays = sprintf(__('%d days left', 'duplicator-pro'), $expDays);
            }
            $license_status_text .= ' (<b>' . $expDays . '</b>)';
        }
        break;
    case License::STATUS_INACTIVE:
        $license_status_style = 'color:#dd3d36;';
        $license_status_text  = __('Status: Inactive', 'duplicator-pro');
        break;
    case License::STATUS_SITE_INACTIVE:
        $license_status_style = 'color:#dd3d36;';
        $global               = DUP_PRO_Global_Entity::getInstance();

        if ($global->license_no_activations_left) {
            $license_status_text = __('Status: Inactive (out of site licenses).', 'duplicator-pro') . '<br>' . License::getNoActivationLeftMessage();
        } else {
            $license_status_text = __('Status: Inactive', 'duplicator-pro');
        }
        break;
    case License::STATUS_EXPIRED:
        $renewal_url          = 'https://duplicator.com/checkout?edd_license_key=' . License::getLicenseKey();
        $license_status_style = 'color:#dd3d36;';
        $license_status_text  = sprintf(
            __(
                'Your Duplicator Pro license key has expired so you aren\'t getting important updates! ' .
                '<a target="_blank" href="%1$s">Renew your license now</a>',
                'duplicator-pro'
            ),
            $renewal_url
        );
        break;
    default:
    // https://duplicator.com/knowledge-base/how-to-resolve-license-activation-issues/
        $license_status_string    = License::getLicenseStatusString($license_status);
        $license_status_style     = 'color:#dd3d36;';
        $license_status_text      = '<b>' .  __('Status: ', 'duplicator-pro') . '</b>' . $license_status_string . '<br/>';
        $license_status_text_alt  = __('If license activation fails please wait a few minutes and retry.', 'duplicator-pro');
        $license_status_text_alt .= '<div class="dup-license-status-notes ">';
        $license_status_text_alt .= sprintf(
            '- ' . __('Failure to activate after several attempts please review %1$sfaq activation steps%2$s', 'duplicator-pro'),
            '<a target="_blank" href="https://duplicator.com/knowledge-base/how-to-resolve-license-activation-issues/">',
            '</a>.<br/>'
        );
        $license_status_text_alt .= sprintf(
            '- ' . __('To upgrade or renew your license visit %1$sduplicator.com%2$s', 'duplicator-pro'),
            '<a target="_blank" href="https://duplicator.com">',
            '</a>.<br/>'
        );
        $license_status_text_alt .= '- A valid key is needed for plugin updates but not for functionality.</div>';
        break;
}
?>


<form
    id="dup-license-activation-form"
    action="<?php echo ControllersManager::getCurrentLink(); ?>"
    method="post"
    data-parsley-validate
>
    <h3 class="title"><?php _e('Activation', 'duplicator-pro') ?> </h3>
    <hr size="1" />
    <table class="form-table">
        <?php
        if ($global->license_key_visible !== License::VISIBILITY_NONE) : ?>
            <tr valign="top" id="dup-tr-license-dashboard">
                <th scope="row"><?php _e('Dashboard', 'duplicator-pro') ?></th>
                <td>
                    <i class="fa fa-th-large fa-sm"></i>
                    <a target="_blank" href="https://duplicator.com/dashboard">
                        <?php
                        _e('Manage Account Online', 'duplicator-pro')
                        ?>
                    </a>
                </td>
            </tr>
            <tr valign="top" id="dup-tr-license-type">
                <th scope="row"><?php _e('License Type', 'duplicator-pro') ?></th>
                <td class="dup-license-type">
                    <?php LicensingController::displayLicenseInfo(); ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php if ($global->license_key_visible === License::VISIBILITY_ALL) : ?>
            <tr valign="top" id="dup-tr-license-key-and-description">
                <th scope="row"><label><?php _e('License Key', 'duplicator-pro'); ?></label></th>
                <td class="dup-license-key-area">
                    <input
                        type="text"
                        class="dup-license-key-input"
                        name="_license_key"
                        id="_license_key"
                        value="<?php echo License::getLicenseKey(); ?>">
                    <br>
                    <p class="description">
                    <span style="<?php echo $license_status_style; ?>" >
                        <?php echo $license_status_text; ?>
                    </span>
                        <?php echo $license_status_text_alt; ?>
                    </p>
                </td>
            </tr>
        <?php endif;?>
        <tr>
            <th scope="row" class="dup-license-key-btns">
                <label><?php _e('License Action', 'duplicator-pro'); ?></label>
            </th>
            <td class="dup-license-key-btns">
                <?php $echostring = (($license_status != License::STATUS_VALID) ? 'true' : 'false'); ?>
                <div class="dup-license-key-btns">
                    <?php if ($global->license_key_visible === License::VISIBILITY_ALL) : ?>
                    <button
                        id="dup-license-activation-btn"
                        class="button"
                        onclick="DupPro.Licensing.ChangeActivationStatus(<?php echo $echostring; ?>);return false;">
                        <?php echo $activate_button_text; ?>
                    </button>
                    <?php endif;?>
                    <button 
                        id="dup-license-clear-btn"
                        class="button" 
                        onclick="DupPro.Licensing.ClearActivationStatus();return false;"
                    >
                        <?php _e('Clear Key', 'duplicator-pro') ?>
                    </button>
                </div>
            </td>
        </tr>
    </table>
</form>