<?php

/**
 * Version Pro Base functionalities
 *
 * Name: Duplicator PRO base
 * Version: 1
 * Author: Snap Creek
 * Author URI: http://snapcreek.com
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Addons\ProBase;

use DUP_PRO_U;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Addons\ProBase\License\License;
use Duplicator\Addons\ProBase\License\Notices;
use Duplicator\Controllers\SettingsPageController;
use Duplicator\Core\CapMng;
use Duplicator\Core\Controllers\PageAction;
use Duplicator\Core\Controllers\SubMenuItem;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;
use stdClass;

class LicensingController
{
    const L2_SLUG_LICENSING = 'licensing';

    //License actions
    const ACTION_ACTIVATE_LICENSE   = 'activate_license';
    const ACTION_DEACTIVATE_LICENSE = 'deactivate_license';
    const ACTION_CHANGE_VISIBILITY  = 'change_visibility';
    const ACTION_CLEAR_KEY          = 'clear_key';

    const LICENSE_KEY_OPTION_AUTO_ACTIVE = 'duplicator_pro_license_auto_active';

    /**
     * License controller init
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_init', array(__CLASS__, 'licenseAutoActive'));
        add_filter('duplicator_sub_menu_items_' . ControllersManager::SETTINGS_SUBMENU_SLUG, array(__CLASS__, 'licenseSubMenu'));
        add_action('duplicator_render_page_content_' . ControllersManager::SETTINGS_SUBMENU_SLUG, array(__CLASS__, 'renderLicenseContent'));
        add_filter('duplicator_page_actions_' . ControllersManager::SETTINGS_SUBMENU_SLUG, array(__CLASS__, 'pageActions'));
        add_filter('duplicator_template_file', array(__CLASS__, 'getTemplateFile'), 10, 2);
    }

    /**
     * Method call on admin_init hook
     *
     * @return void
     */
    public static function licenseAutoActive()
    {
        if (($lKey = get_option(self::LICENSE_KEY_OPTION_AUTO_ACTIVE, false)) === false) {
            return;
        }
        if (!CapMng::getInstance()->can(CapMng::CAP_LICENSE)) {
            return;
        }
        if (($action = SettingsPageController::getInstance()->getActionByKey(self::ACTION_ACTIVATE_LICENSE)) == false) {
            return;
        }
        delete_option(self::LICENSE_KEY_OPTION_AUTO_ACTIVE);
        $redirect = $action->getUrl(['_license_key' => $lKey]);
        if (wp_redirect($redirect)) {
            exit;
        } else {
            throw new Exception(__('Error redirecting to license activation page', 'duplicator-pro'));
        }
    }

    /**
     * Add license sub menu page
     *
     * @param array $subMenus sub menus
     *
     * @return array
     */
    public static function licenseSubMenu($subMenus)
    {
        $subMenus[] = new SubMenuItem(self::L2_SLUG_LICENSING, __('Licensing', 'duplicator-pro'), '', CapMng::CAP_LICENSE, 100);
        return $subMenus;
    }

    /**
     * Define actions related to the license
     *
     * @param PageAction[] $actions Page actions array from filter
     *
     * @return PageAction[] Updated page actions array
     */
    public static function pageActions($actions)
    {
        $actions[] = new PageAction(
            self::ACTION_ACTIVATE_LICENSE,
            array(__CLASS__, 'activateLicense'),
            array(
                ControllersManager::SETTINGS_SUBMENU_SLUG,
                self::L2_SLUG_LICENSING
            )
        );
        $actions[] = new PageAction(
            self::ACTION_DEACTIVATE_LICENSE,
            array(__CLASS__, 'deactivateLicense'),
            array(
                ControllersManager::SETTINGS_SUBMENU_SLUG,
                self::L2_SLUG_LICENSING
            )
        );
        $actions[] = new PageAction(
            self::ACTION_CLEAR_KEY,
            array(__CLASS__, 'clearLicenseKey'),
            array(
                ControllersManager::SETTINGS_SUBMENU_SLUG,
                self::L2_SLUG_LICENSING
            )
        );
        $actions[] = new PageAction(
            self::ACTION_CHANGE_VISIBILITY,
            array(__CLASS__, 'changeLicenseVisibility'),
            array(
                ControllersManager::SETTINGS_SUBMENU_SLUG,
                self::L2_SLUG_LICENSING
            )
        );

        return $actions;
    }

    /**
     * Action that changes the license visibility
     *
     * @return array
     */
    public static function changeLicenseVisibility()
    {
        $result  = array(
            'license_success' => false,
            'license_message' => ''
        );
        $global  = \DUP_PRO_Global_Entity::getInstance();
        $sglobal = \DUP_PRO_Secure_Global_Entity::getInstance();

        $oldVisibility = $global->license_key_visible;
        $newVisibility = filter_input(INPUT_POST, 'license_key_visible', FILTER_VALIDATE_INT);
        $newPassword   = SnapUtil::sanitizeInput(INPUT_POST, '_key_password', '');

        if ($oldVisibility === $newVisibility) {
            return $result;
        }

        switch ($newVisibility) {
            case License::VISIBILITY_ALL:
                if ($sglobal->lkp !== $newPassword) {
                    $result['license_message'] = __("Wrong password entered. Please enter the correct password.", 'duplicator-pro');
                    return $result;
                }
                $newPassword = ''; // reset password
                break;
            case License::VISIBILITY_NONE:
            case License::VISIBILITY_INFO:
                if ($oldVisibility == License::VISIBILITY_ALL) {
                    $password_confirmation = SnapUtil::sanitizeInput(INPUT_POST, '_key_password_confirmation', '');

                    if (strlen($newPassword) === 0) {
                        $result['license_message'] = __('Password cannot be empty.', 'duplicator-pro');
                        return $result;
                    }

                    if ($newPassword !== $password_confirmation) {
                        $result['license_message'] = __("Passwords don't match.", 'duplicator-pro');
                        return $result;
                    }
                    $updateGlobal = true;
                } else {
                    if ($sglobal->lkp !== $newPassword) {
                        $result['license_message'] = __("Wrong password entered. Please enter the correct password.", 'duplicator-pro');
                        return $result;
                    }
                }
                break;
            default:
                throw new Exception(__('Invalid license visibility value.', 'duplicator-pro'));
        }

        $global->license_key_visible = $newVisibility;
        $sglobal->lkp                = $newPassword;

        if ($global->save() && $sglobal->save()) {
            return array(
                'license_success' => true,
                'license_message' => __("License visibility changed", 'duplicator-pro')
            );
        } else {
            return array(
                'license_success' => false,
                'license_message' => __("Couldn't change licnse vilisiblity.", 'duplicator-pro')
            );
        }
    }

    /**
     * Action that clears the license key
     *
     * @return array
     */
    public static function clearLicenseKey()
    {
        $global  = \DUP_PRO_Global_Entity::getInstance();
        $sglobal = \DUP_PRO_Secure_Global_Entity::getInstance();

        self::deactivateLicense();

        update_option(License::LICENSE_KEY_OPTION_NAME, '');

        $global->license_key_visible = License::VISIBILITY_ALL;
        $sglobal->lkp                = '';

        if ($global->save() && $sglobal->save()) {
            return array(
                'license_success' => true,
                'license_message' => __("License key cleared", 'duplicator-pro')
            );
        } else {
            return array(
                'license_success' => false,
                'license_message' => __("Couldn't save changes", 'duplicator-pro')
            );
        }
    }

    /**
     * Action that deactivates the license
     *
     * @return array
     */
    public static function deactivateLicense()
    {
        $result = array(
            'license_success' => true,
            'license_message' => __("License Deactivated", 'duplicator-pro')
        );

        try {
            if (License::isValidOvrKey(License::getLicenseKey())) {
                update_option(License::LICENSE_KEY_OPTION_NAME, '');
                return $result;
            }

            if (License::getLicenseStatus(true) !== License::STATUS_VALID) {
                $result = array(
                    'license_success' => true,
                    'license_message' => __('License already deactivated.', 'duplicator-pro')
                );
                return $result;
            }

            switch (License::changeLicenseActivation(false)) {
                case License::ACTIVATION_RESPONSE_OK:
                    break;
                case License::ACTIVATION_RESPONSE_INVALID:
                    throw new Exception(__('Invalid license key.', 'duplicator-pro'));
                case License::ACTIVATION_REQUEST_ERROR:
                    $result['license_request_error'] = License::getLastRequestError();
                    throw new Exception(self::getRequestErrorMessage());
                default:
                    throw new Exception(__('Error activating license.', 'duplicator-pro'));
            }
        } catch (Exception $e) {
            $result['license_success'] = false;
            $result['license_message'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Return template file path
     *
     * @param string $path    path to the template file
     * @param string $slugTpl slug of the template
     *
     * @return string
     */
    public static function getTemplateFile($path, $slugTpl)
    {
        if (strpos($slugTpl, 'licensing/') === 0) {
            return ProBase::getAddonPath() . '/template/' . $slugTpl . '.php';
        }
        return $path;
    }

    /**
     * Action that activates the license
     *
     * @return array
     */
    public static function activateLicense()
    {
        $result = array(
            'license_success' => true,
            'license_message' => __("License Activated", 'duplicator-pro')
        );

        try {
            if (($licenseKey = SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, '_license_key')) === false) {
                throw new Exception(__('Please enter a valid key. Key should be 32 characters long.', 'duplicator-pro'));
            }

            if (License::isValidOvrKey($licenseKey)) {
                License::setOvrKey($licenseKey);
                return array();
            }

            if (!preg_match('/^[a-f0-9]{32}$/i', $licenseKey)) {
                throw new Exception(__('Please enter a valid key. Key should be 32 characters long.', 'duplicator-pro'));
            }
            // make sure reset old license key if exists
            self::clearLicenseKey();
            update_option(License::LICENSE_KEY_OPTION_NAME, $licenseKey);

            switch (License::changeLicenseActivation(true)) {
                case License::ACTIVATION_RESPONSE_OK:
                    break;
                case License::ACTIVATION_RESPONSE_INVALID:
                    throw new Exception(__('Invalid license key.', 'duplicator-pro'));
                case License::ACTIVATION_REQUEST_ERROR:
                    $result['license_request_error'] = License::getLastRequestError();
                    throw new Exception(self::getRequestErrorMessage());
                default:
                    throw new Exception(__('Error activating license.', 'duplicator-pro'));
            }
        } catch (Exception $e) {
            $result['license_success'] = false;
            $result['license_message'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Render license page
     *
     * @param string[] $currentLevelSlugs current page/tables slugs
     *
     * @return void
     */
    public static function renderLicenseContent($currentLevelSlugs)
    {
        switch ($currentLevelSlugs[1]) {
            case self::L2_SLUG_LICENSING:
                TplMng::getInstance()->render('licensing/main');
                break;
        }
    }

    /**
     * License type viewer
     *
     * @return void
     */
    public static function displayLicenseInfo()
    {
        $license_type = License::getType();

        if ($license_type === License::TYPE_UNLICENSED) {
            echo sprintf('<b>%s</b>', DUP_PRO_U::__("Unlicensed"));
        } else {
            $licenseLimit = License::getLimit();
            if (($license_data  = License::getLicenseData(false)) === false) {
                $license_data             = new stdClass();
                $license_data->expires    = 'Unknown';
                $license_data->site_count = -1;
            }
            $license_key = License::getLicenseKey();

            if (License::isValidOvrKey($license_key)) {
                $license_key = License::getStandardKeyFromOvrKey($license_key);
            }

            echo '<b>' . License::getLicenseToString() . '</b>&nbsp;';
            if (License::canBeUpgraded()) {
                Notices::getUpsellLinkHTML('[' . __('upgrade', 'duplicator-pro') . ']');
            }

            $pt  = License::can(License::CAPABILITY_POWER_TOOLS) ? '<i class="far fa-check-circle"></i>  ' : '<i class="far fa-circle"></i>  ';
            $mup = License::can(License::CAPABILITY_MULTISITE_PLUS) ? '<i class="far fa-check-circle"></i>  ' : '<i class="far fa-circle"></i>  ';

            $txt_lic_hdr = DUP_PRO_U::__('Site Licenses');
            $txt_lic_msg = DUP_PRO_U::__(
                'Indicates the number of sites the plugin can be active on at any one time. ' .
                'At any point you may deactivate/uninstall the plugin to free up the license and use the plugin elsewhere if needed.'
            );
            $txt_pt_hdr  = DUP_PRO_U::__('Powertools');
            $txt_pt_msg  = DUP_PRO_U::__('Enhanced features that greatly improve the productivity of serious users. Include hourly schedules, ' .
                                                'installer branding, salt & key replacement, priority support and more.');
            $txt_mup_hdr = DUP_PRO_U::__('Multisite Plus+');
            $txt_mup_msg = DUP_PRO_U::__(
                'Adds the ability to install a subsite as a standalone site, ' .
                'insert a standalone site into a multisite, or insert a subsite from the same/different multisite into a multisite.'
            );

            $lic_limit  = License::isUnlimited() ? DUP_PRO_U::__('unlimited') : $licenseLimit;
            $site_count = is_numeric($license_data->site_count) ? $license_data->site_count : '?';

            echo '<div class="dup-license-type-info">';
            echo "<i class='far fa-check-circle'></i>  {$txt_lic_hdr}: {$site_count} of {$lic_limit} " .
                "<i class='fa fa-question-circle  fa-sm' data-tooltip-title='{$txt_lic_hdr}' data-tooltip='{$txt_lic_msg}'></i><br/>";
            echo $pt;
            echo "{$txt_pt_hdr} <i class='fa fa-question-circle fa-sm' data-tooltip-title='{$txt_pt_hdr}' data-tooltip='{$txt_pt_msg}'></i><br/>";
            echo $mup;
            echo "{$txt_mup_hdr} <i class='fa fa-question-circle fa-sm' data-tooltip-title='{$txt_mup_hdr}' data-tooltip='{$txt_mup_msg}'></i><br/>";
            echo '</div>';
        }
    }

    /**
     * Returns the communication error message
     *
     * @return string
     */
    private static function getRequestErrorMessage()
    {
        $result  = sprintf(
            __('<b>License data request failed.</b> (URL: %1$s)', 'duplicator-pro'),
            License::EDD_DUPPRO_STORE_URL
        );
        $result .= '<br>';
        $result .= sprintf(
            _x(
                'Please see %1$sthis FAQ entry%2$s for possible causes and resolutions.',
                '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                'duplicator-pro'
            ),
            '<a href="https://duplicator.com/knowledge-base/how-to-resolve-license-activation-issues/" target="_blank">',
            '</a>'
        );
        return $result;
    }
}
