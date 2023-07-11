<?php

/**
 * Auloader calsses
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Addons\ProBase\License;

use Duplicator\Addons\ProBase\LicensingController;
use Duplicator\Core\CapMng;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;

class Notices
{
    const OPTION_KEY_EXPIRED_LICENCE_NOTICE_DISMISS_TIME = 'duplicator_pro_expired_licence_notice_time';
    const EXPIRED_LICENCE_NOTICE_DISMISS_FOR_DAYS        = 14;


    /**
     * Init notice actions
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_init', array(__CLASS__, 'adminInit'));

        $path = plugin_basename(DUPLICATOR____FILE);

        // Important to make this priority 11 or greater to ensure the version cache is up to date by EDD
        add_action("after_plugin_row_{$path}", array(__CLASS__, 'noLicenseDisplay'), 11, 2);
    }

    /**
     * Function called on hook admin_init
     *
     * @return void
     */
    public static function adminInit()
    {
        $action = is_multisite() ? 'network_admin_notices' : 'admin_notices';
        add_action($action, array(__CLASS__, 'licenseAlertCheck'));
    }

    /**
     * Function called on hook admin_init
     *
     * @param string $file   Path to the plugin file relative to the plugins directory
     * @param array  $plugin An array of plugin data
     *
     * @return void
     */
    public static function noLicenseDisplay($file, $plugin)
    {
        $latest_version = License::getLatestVersion();

        // Only display this message when there is no update message
        if (($latest_version === false) || version_compare(DUPLICATOR_PRO_VERSION, $latest_version, '>=')) {
            $global = \DUP_PRO_Global_Entity::getInstance();

            $error_string = null;

            if ($global->license_status === License::STATUS_INVALID || $global->license_status === License::STATUS_SITE_INACTIVE) {
                $error_string = sprintf(
                    __(
                        'Your Duplicator Pro license key is invalid so you aren\'t getting important updates! ' .
                        '<a href="%1$s">activate your license</a> or ' .
                        '<a target="_blank" href="%2$s">purchase a license</a>.',
                        'duplicator-pro'
                    ),
                    'admin.php?page=duplicator-pro-settings&tab=licensing',
                    'https://duplicator.com/duplicator/pricing'
                );
            } elseif ($global->license_status === License::STATUS_EXPIRED) {
                $license_key = License::getLicenseKey();

                if ($license_key !== false) {
                    $renewal_url = 'https://duplicator.com/checkout?edd_license_key=' . $license_key;

                    $error_string = sprintf(
                        __(
                            'Your Duplicator Pro license key has expired so you aren\'t getting important updates! ' .
                            '<a target="_blank" href="%1$s">Renew your license now</a>',
                            'duplicator-pro'
                        ),
                        $renewal_url
                    );
                }
            }

            if ($error_string != null) {
                echo '<script>jQuery("[data-slug=\'duplicator-pro\']").addClass("update");</script>';

                echo '<tr style="border-top-color:black" class="plugin-update-tr active" >' .
                        '<td colspan="4" class="plugin-update colspanchange">' .
                            '<div class="update-message notice inline notice-error notice-alt">' .
                                "<p>{$error_string}</p>";
                            '</div>' .
                        '</td>' .
                    '</tr>';
            }
        }
    }

    /**
     * Used by the WP action hook to detect the state of the endpoint license
     * which calls the various show* methods for which alert to display
     *
     * @return void
     */
    public static function licenseAlertCheck()
    {
        if (!CapMng::can(CapMng::CAP_BASIC, false)) {
            return;
        }

        $on_licensing_tab = (isset($_REQUEST['tab']) && ($_REQUEST['tab'] === 'licensing'));

        if ($on_licensing_tab) {
            return;
        }

        if (file_exists(DUPLICATOR_PRO_SSDIR_PATH . "/ovr.dup")) {
            return;
        }
        //Style needs to be loaded here because css is global across wp-admin
        wp_enqueue_style('dup-pro-plugin-style-notices', DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/admin-notices.css', [], DUPLICATOR_PRO_VERSION);

        $license_status = License::STATUS_UNKNOWN;
        try {
            $license_status = License::getLicenseStatus(false);
        } catch (\Exception $ex) {
            \DUP_PRO_Log::traceError("Could not get license status.");
        }

        if ($license_status === License::STATUS_EXPIRED) {
            $expired_licence_notice_dismiss_time = get_option(self::OPTION_KEY_EXPIRED_LICENCE_NOTICE_DISMISS_TIME, false);
            if (
                false === $expired_licence_notice_dismiss_time ||
                (time() - $expired_licence_notice_dismiss_time) > (DAY_IN_SECONDS * self::EXPIRED_LICENCE_NOTICE_DISMISS_FOR_DAYS)
            ) {
                self::showExpired();
            }
        } elseif ($license_status !== License::STATUS_VALID) {
            $global = \DUP_PRO_Global_Entity::getInstance();

            if ($global->license_no_activations_left) {
                self::showNoActivationsLeft();
            } else {
                $days_invalid = (int) floor((time() - $global->initial_activation_timestamp) / 86400);

                // If an md5 is present always do standard nag
                $license_key = get_option(License::LICENSE_KEY_OPTION_NAME, '');
                $md5_present = \DUP_PRO_Low_U::isValidMD5($license_key);

                if ($md5_present || ($days_invalid < License::UNLICENSED_SUPER_NAG_DELAY_IN_DAYS)) {
                    self::showInvalidStandardNag();
                } else {
                    self::showInvalidSuperNag($days_invalid);
                }
            }
        }
    }

    /**
     * Shows the smaller standard nag screen
     *
     * @return void
     */
    private static function showInvalidStandardNag()
    {
        $img_url           = plugins_url('duplicator-pro/assets/img/warning.png');
        $licensing_tab_url = ControllersManager::getMenuLink(ControllersManager::SETTINGS_SUBMENU_SLUG, LicensingController::L2_SLUG_LICENSING);

        $problem_text = 'missing';

        if (get_option(License::LICENSE_KEY_OPTION_NAME, '') !== '') {
            $problem_text = 'invalid or disabled';
        }

        TplMng::getInstance()->render(
            'licensing/inactive_message',
            [
                'problem' => $problem_text
            ]
        );
    }

    /**
     * Shows the larger super nag screen used for display after the trial period
     *
     * @param int $daysInvalid The number of days the license has been invalid
     *
     * @return void
     */
    private static function showInvalidSuperNag($daysInvalid)
    {
        $licensing_tab_url = ControllersManager::getMenuLink(ControllersManager::SETTINGS_SUBMENU_SLUG, LicensingController::L2_SLUG_LICENSING);
        ?>
        <div class="update-nag dpro-admin-notice dpro-invalid-license">
            <h2>
                <?php _e('INVALID LICENSE', 'duplicator-pro'); ?>
            </h2>

            The Duplicator Pro plugin has been running for at least 30 days without a valid license.<br/>
            This means you don't have access to <b>security updates</b>, <i>bug fixes</i>, <b>support requests</b> or <i>new features</i>.<br/>
            <p>
                <a href="<?php echo $licensing_tab_url; ?>">Activate Your License Now!</a> <br/>
                - OR - <br/>
                <a target='_blank' href='https://duplicator.com/duplicator/pricing'>Purchase Now!</a> <br/>
            </p>
        </div>
        <?php
    }

    /**
     * Shows the license count used up alert
     *
     * @return void
     */
    private static function showNoActivationsLeft()
    {
        $licensing_tab_url = ControllersManager::getMenuLink(ControllersManager::SETTINGS_SUBMENU_SLUG, LicensingController::L2_SLUG_LICENSING);
        $dashboard_url     = 'https://duplicator.com/dashboard';
        $img_url           = plugins_url('duplicator-pro/assets/img/warning.png');

        echo '<div class="update-nag dpro-admin-notice" style="font-size:1.2rem">' .
        '<div style="text-align:center">' .
        "<img src='$img_url' style='/* float:left; */text-align: center;margin: auto;padding:0 10px 0 5px; width:80px'>" .
        '</div>' .
        '<p style="text-align: center;font-size: 2rem;line-height: 2.7rem; margin-top:10px">' .
        'Duplicator Pro\'s license is deactivated because you\'re out of site activations.</p>' .
        "<p style='text-align: center;font-size: 1.3rem; line-height: 2.2rem'>" .
        "Upgrade your license using the <a href='$dashboard_url' target='_blank'>Snap Creek Dashboard</a> or deactivate plugin on old sites.<br/>" .
        "After making necessary changes <a href='" . esc_url($licensing_tab_url) . "'>refresh the license status.</a>" .
        '</div>';
    }

    /**
     * Shows the expired message alert
     *
     * @return void
     */
    private static function showExpired()
    {
        $license_key = get_option(License::LICENSE_KEY_OPTION_NAME, '');
        $renewal_url = 'https://duplicator.com/checkout?edd_license_key=' . $license_key;
        $txtTitle    = __('Warning! Your Duplicator Pro license has expired...', 'duplicator-pro');
        $txtMsg1     = __('You\'re currently missing important updates for <b>security patches</b>, <i>bug fixes</i>, support requests, &amp; '
            . '<u>new features</u>', 'duplicator-pro');
        $txtMsg2     = __('Renew Now!', 'duplicator-pro');

        //Styles go in admin-notices.css
        $htmlMsg = "<span class='dashicons dashicons-admin-plugins dup-license-expired'></span>" .
            "<b style='font-size:16px'>{$txtTitle}</b> <br/> {$txtMsg1}.<br/>" .
            "<a target='_blank' href='{$renewal_url}'>{$txtMsg2}</a>";
        \DUP_PRO_UI_Notice::displayGeneralAdminNotice(
            $htmlMsg,
            \DUP_PRO_UI_Notice::GEN_ERROR_NOTICE,
            true,
            array(
                'duplicator-pro-admin-notice',
                'dpro-admin-notice'
            ),
            array(
                'data-to-dismiss' => self::OPTION_KEY_EXPIRED_LICENCE_NOTICE_DISMISS_TIME,
                'title' => sprintf(__('Dismiss notice for %s days', 'duplicator-pro'), self::EXPIRED_LICENCE_NOTICE_DISMISS_FOR_DAYS)
            )
        );
    }

    /**
     * Gets the upgrade link
     *
     * @param string $label The label of the link
     * @param bool   $echo  Whether to echo the link or return it
     *
     * @return string
     */
    public static function getUpsellLinkHTML($label = 'Upgrade', $echo = true)
    {
        ob_start();
        ?>
        <a class="dup-upgrade-license-link" href="<?php echo esc_attr(License::getUpsellURL()); ?>" target="_blank">
            <?php echo $label; ?>
        </a>
        <?php
        $html = ob_get_clean();
        if ($echo) {
            echo $html;
            return '';
        } else {
            return $html;
        }
    }
}
