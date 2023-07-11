<?php

/**
 * Used to display notices in the WordPress Admin area
 * This class takes advantage of the 'admin_notice' action.
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    DUP_PRO
 * @subpackage classes/ui
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 */

defined("ABSPATH") or die("");

use Duplicator\Core\CapMng;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Models\RecommendedFix;

class DUP_PRO_UI_Notice
{
    const OPTION_KEY_INSTALLER_HASH_NOTICE          = 'duplicator_pro_inst_hash_notice';
    const OPTION_KEY_ACTIVATE_PLUGINS_AFTER_INSTALL = 'duplicator_pro_activate_plugins_after_installation';
    const OPTION_KEY_MIGRATION_SUCCESS_NOTICE       = 'duplicator_pro_migration_success';
    const OPTION_KEY_S3_CONTENTS_FETCH_FAIL_NOTICE  = 'duplicator_pro_s3_contents_fetch_fail';
    const QUICK_FIX_NOTICE                          = 'duplicator_pro_quick_fix_notice';
    const FAILED_SCHEDULE_NOTICE                    = 'duplicator_pro_failed_schedule_notice';
    const GEN_INFO_NOTICE                           = 0;
    const GEN_SUCCESS_NOTICE                        = 1;
    const GEN_WARNING_NOTICE                        = 2;
    const GEN_ERROR_NOTICE                          = 3;
    /**
     * init notice actions
     */
    public static function init()
    {
        add_action('admin_init', array(__CLASS__, 'adminInit'));
    }

    public static function adminInit()
    {
        $notices = array();

        $notices[]     = array(__CLASS__, 'migrationSuccessNotice'); // BEFORE MIGRATION SUCCESS NOTICE
        $notices[]     = array(__CLASS__, 's3ContentsFetchFailNotice');
        $notices[]     = array(__CLASS__, 'addonInitFailNotice');
        $notices[]     = array('DUP_PRO_UI_Alert', 'activatePluginsAfterInstall');
        $system_global = DUP_PRO_System_Global_Entity::getInstance();
        foreach ($system_global->recommended_fixes as $fix) {
            if ($fix->recommended_fix_type === RecommendedFix::TYPE_TEXT || $fix->recommended_fix_type === RecommendedFix::TYPE_FIX) {
                $notices[] = array(__CLASS__, 'showQuickFixNotice');
            }
        }
        if ($system_global->schedule_failed) {
            $notices[] = array(__CLASS__, 'showFailedSchedule');
        }

        $action = is_multisite() ? 'network_admin_notices' : 'admin_notices';
        foreach ($notices as $notice) {
            add_action($action, $notice);
        }
    }

    public static function addonInitFailNotice()
    {
        if (\Duplicator\Core\Addons\AddonsManager::getInstance()->isAddonsReady()) {
            return;
        }

        if (!CapMng::can(CapMng::CAP_BASIC, false)) {
            return;
        }
        ob_start();
        ?>
        <strong>Duplicator Pro</strong>
        <hr>
        <p>
            <?php _e('The plugin cannot be activated due to problems during initialization. Please reinstall the plugin deleting the current installation', 'duplicator-pro'); ?>
        </p>
        <?php
        $content = ob_get_clean();
        self::displayGeneralAdminNotice($content, DUP_PRO_UI_Notice::GEN_ERROR_NOTICE, false);
    }

    /**
     * Shows notice in case we were enable to fetch contents of S3 bucket
     *
     * @throws Exception
     * @return void
     */
    public static function s3ContentsFetchFailNotice()
    {
        if (get_option(self::OPTION_KEY_S3_CONTENTS_FETCH_FAIL_NOTICE, false) != true || !ControllersManager::isCurrentPage(ControllersManager::PACKAGES_SUBMENU_SLUG)) {
            return;
        }

        if (!CapMng::can(CapMng::CAP_CREATE, false)) {
            return;
        }

        $errorMessage = DUP_PRO_U::__('<strong>Duplicator Pro</strong> was unable to fetch the contents of the S3 bucket to remove old packages.') . "<hr><br>" .
            sprintf(
                DUP_PRO_U::__('<strong>RECOMMENDATION:</strong> Please make sure your S3 bucket settings are aligned with our %1sStep-by-Step guide%2s and %3sUser Bucket Policy%4s.'),
                '<a target="_blank" href="https://snapcreek.com/duplicator/docs/amazon-s3-step-by-step/">',
                '</a>',
                '<a target="_blank" href="https://snapcreek.com/duplicator/docs/amazon-s3-policy-setup/">',
                '</a>'
            );

        self::displayGeneralAdminNotice(
            $errorMessage,
            self::GEN_WARNING_NOTICE,
            true,
            array(
                'duplicator-pro-admin-notice',
                'dpro-admin-notice',
                'dup-pro-quick-fix-notice'
            ),
            array(
                'data-to-dismiss' => self::OPTION_KEY_S3_CONTENTS_FETCH_FAIL_NOTICE
            )
        );
    }


    /**
     * Shows a display message in the wp-admin if any reserved files are found
     *
     * @return void
     */
    public static function migrationSuccessNotice()
    {
        if (get_option(self::OPTION_KEY_MIGRATION_SUCCESS_NOTICE) != true) {
            return;
        }

        if (!CapMng::can(CapMng::CAP_BASIC, false)) {
            return;
        }

        if (!DUP_PRO_CTRL_Tools::isDiagnosticPage()) {
            TplMng::getInstance()->render('parts/migration/almost-complete');
        }
    }

    /**
     * Shows the scheduled failed alert
     */
    public static function showFailedSchedule()
    {
        if (!CapMng::can(CapMng::CAP_SCHEDULE, false)) {
            return;
        }
        $img_url   = plugins_url('duplicator-pro/assets/img/warning.png');
        $clear_url = SnapURL::getCurrentUrl();
        $clear_url = SnapURL::appendQueryValue($clear_url, 'dup_pro_clear_schedule_failure', 1);
        $html      = "<img src='" . esc_url($img_url) . "' style='float:left; padding:0 10px 0 5px' />" .
            sprintf(DUP_PRO_U::esc_html__('%sWarning! A Duplicator Pro scheduled backup has failed.%s'), '<b>', '</b> <br/>') .
            sprintf(DUP_PRO_U::esc_html__('This message will continue to be displayed until a %sscheduled build%s successfully runs. '), "<a href='admin.php?page=duplicator-pro-schedules'>", '</a> ') .
            sprintf(DUP_PRO_U::esc_html__('To ignore and clear this message %sclick here%s'), "<a href='" . esc_url($clear_url) . "'>", '</a>.<br/>');
        self::displayGeneralAdminNotice($html, self::GEN_WARNING_NOTICE, true, array(
                'duplicator-pro-admin-notice',
                'dpro-admin-notice',
                'dup-pro-quick-fix-notice'
            ), array(
                'data-to-dismiss' => self::FAILED_SCHEDULE_NOTICE
            ));
    }

    public static function showQuickFixNotice()
    {
        if (!ControllersManager::isCurrentPage(ControllersManager::PACKAGES_SUBMENU_SLUG)) {
            return;
        }
        if (!CapMng::can(CapMng::CAP_BASIC, false)) {
            return;
        }

        $system_global = DUP_PRO_System_Global_Entity::getInstance();
        $html          = '<b class="title"><i class="fa fa-exclamation-circle fa-3 color-alert" ></i> ' . DUP_PRO_U::__('Duplicator Pro Errors Detected') . '</b></br>';
        $html         .= '<p>' . DUP_PRO_U::__('Package build error(s) were encountered.  Click the button(s) in the') . ' <i>' . DUP_PRO_U::__('Necessary Actions') . '</i> ' . DUP_PRO_U::__('section to reconfigure Duplicator Pro.') . "</p>";
        $html         .= '<p>';
        $html         .= '<b>' . DUP_PRO_U::__('Error(s):') . ' </b>';
        $html         .= '<ul style="list-style: disc; padding-left: 40px">';
        foreach ($system_global->recommended_fixes as $fix) {
            $html .= '<li>' . $fix->error_text . '</li>';
        }
        $html .= '</ul>';
        $html .= '</p>';
        $html .= '<b>' . DUP_PRO_U::__('Necessary Action(s):') . ' </b>' . '<br/>';
        foreach ($system_global->recommended_fixes as $fix) {
            if ($fix->recommended_fix_type == RecommendedFix::TYPE_FIX) {
                $html .= '<p id ="quick-fix-' . $fix->id . '">'
                    . '<button id="quick-fix-' . $fix->id . '-button" class="dup-pro-quick-fix button" '
                    . 'type="button" class="button button-primary" '
                    . 'data-param="' . esc_attr(json_encode($fix->parameter2)) . '" data-id="' . $fix->id . '" data-toggle="#quick-fix-' . $fix->id . '">'
                    . "<i class='fa fa-wrench' aria-hidden='true'></i>&nbsp; "
                    . DUP_PRO_U::__('Resolve This')
                    . '</button>'
                    . $fix->parameter1
                    . '</p>';
            } elseif ($fix->recommended_fix_type == RecommendedFix::TYPE_TEXT) {
                    $html .= "<p><i class='fa fa-question-circle color-alert' data-tooltip='" . esc_attr($fix->error_text) . "'></i>&nbsp; " . $fix->parameter1 . "</br></p>";
            }
        }

        self::displayGeneralAdminNotice($html, self::GEN_WARNING_NOTICE, true, array(
                'duplicator-pro-admin-notice',
                'dpro-admin-notice',
                'dup-pro-quick-fix-notice'
            ), array(
                'data-to-dismiss' => self::QUICK_FIX_NOTICE
            ));
    }

    /**
     * display genral admin notice by printing it
     *
     * @param string       $htmlMsg       html code to be printed
     * @param integer      $noticeType    constant value of SELF::GEN_
     * @param boolean      $isDismissible whether the notice is dismissable or not. Default is true
     * @param array|string $extraClasses  add more classes to the notice div
     * @param array|string $extraAtts     assosiate array in which key as attr and value as value of the attr
     * @param bool         $blockContent  if false wraps htmlMsg in <p> otherwise allows to use block tags e.g. <div>
     *
     * @return void
     */
    public static function displayGeneralAdminNotice($htmlMsg, $noticeType, $isDismissible = true, $extraClasses = array(), $extraAtts = array(), $blockContent = false)
    {
        if (empty($extraClasses)) {
            $classes = array();
        } elseif (is_array($extraClasses)) {
            $classes = $extraClasses;
        } else {
            $classes = array($extraClasses);
        }

        $classes[] = 'notice';
        switch ($noticeType) {
            case self::GEN_INFO_NOTICE:
                $classes[] = 'notice-info';
                break;
            case self::GEN_SUCCESS_NOTICE:
                $classes[] = 'notice-success';
                break;
            case self::GEN_WARNING_NOTICE:
                $classes[] = 'notice-warning';
                break;
            case self::GEN_ERROR_NOTICE:
                $classes[] = 'notice-error';
                break;
            default:
                throw new Exception('Invalid Admin notice type!');
        }

        if ($isDismissible) {
            $classes[] = 'is-dismissible';
        }

        $classesStr = implode(' ', $classes);
        $attsStr    = '';
        if (!empty($extraAtts)) {
            $attsStrArr = array();
            foreach ($extraAtts as $att => $attVal) {
                $attsStrArr[] = $att . '="' . $attVal . '"';
            }
            $attsStr = implode(' ', $attsStrArr);
        }

        $htmlMsg = self::GEN_ERROR_NOTICE == $noticeType ? "<i class='fa fa-exclamation-triangle'></i>&nbsp;" . $htmlMsg : $htmlMsg;
        $htmlMsg = !$blockContent ? "<p>" . $htmlMsg . "</p>" : $htmlMsg;
        ?>
        <div class="<?php echo esc_attr($classesStr); ?>" <?php echo $attsStr; ?>>
            <?php echo $htmlMsg; ?>
        </div>
        <?php
    }
}
