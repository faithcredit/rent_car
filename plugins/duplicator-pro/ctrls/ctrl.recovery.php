<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Controllers\SettingsPageController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;

class DUP_PRO_CTRL_recovery
{
    const VIEW_WIDGET_NO_PACKAGE_SET = 'nop';
    const VIEW_WIDGET_NOT_VALID      = 'notvalid';
    const VIEW_WIDGET_VALID          = 'valid';

    /**
     *
     * @var bool
     */
    protected static $isError = false;

    /**
     *
     * @var string
     */
    protected static $errorMessage = '';

    public static function init()
    {
    }

    /**
     * import installer controller
     *
     * @throws Exception
     */
    public static function controller()
    {
        self::doView();
    }



    public static function isRecoveryPage()
    {
        if (!DUP_PRO_CTRL_Tools::isToolPage()) {
            return false;
        }

        return filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS) === 'recovery';
    }

    /**
     *
     * @return string
     */
    public static function getErrorMessage()
    {
        return self::$errorMessage;
    }

    /**
     * @return bool check if package is disallow from wp-config.php
     */
    public static function isDisallow()
    {
        return (bool) DUPLICATOR_PRO_DISALLOW_RECOVERY;
    }

    /**
     *
     * @return string
     */
    public static function getRecoverPageLink()
    {
        if (is_multisite()) {
            $url = network_admin_url('admin.php');
        } else {
            $url = admin_url('admin.php');
        }
        $queryStr = http_build_query(array(
            'page' => 'duplicator-pro-tools',
            'tab'  => 'recovery'
        ));

        return $url . '?' . $queryStr;
    }

    public static function actionResetRecoveryPoint()
    {
        try {
            DUP_PRO_Package_Recover::removeRecoveryFolder();
            DUP_PRO_Package_Recover::setRecoveablePackage(false);
        } catch (Exception $e) {
            self::$isError      = true;
            self::$errorMessage = $e->getMessage();
            return false;
        } catch (Error $e) {
            self::$isError      = true;
            self::$errorMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public static function actionSetRecoveryPoint()
    {
        try {
            $recPackageId = SnapUtil::filterInputRequest('recovery_package', FILTER_VALIDATE_INT);
            if ($recPackageId === DUP_PRO_Package_Recover::getRecoverPackageId()) {
                return true;
            }

            DUP_PRO_Package_Recover::removeRecoveryFolder();

            $errorMessage = '';
            if (!DUP_PRO_Package_Recover::setRecoveablePackage($recPackageId, $errorMessage)) {
                $msg  = __("The old Recovery Point was removed but this package can’t be set as the Recovery Point.", 'duplicator-pro') . '<br>';
                $msg .= __("Possible solutions:", 'duplicator-pro') . '<br>';
                $msg .= sprintf(
                    _x(
                        '- In some hosting the execution of PHP scripts are blocked in the wp-content folder, %1$s[try set a custom recovery path]%2$s',
                        '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                        'duplicator-pro'
                    ),
                    '<a href="' . esc_url(ControllersManager::getMenuLink(ControllersManager::SETTINGS_SUBMENU_SLUG, SettingsPageController::L2_SLUG_IMPORT)) . '" target="_blank">',
                    '</a>'
                ) . '<br>';
                $msg .= __(
                    "- you may still be able to to download the package manually and perform an import or a classic backup installation." .
                    "If you wish to install the package on the site where it was create the restore backup mode should be activated.",
                    'duplicator-pro'
                ) . '<br><br>';
                $msg .= sprintf(__("Error: <b>%s</b>", 'duplicator-pro'), $errorMessage);

                throw new Exception($msg);
            }
        } catch (Exception $e) {
            self::$isError      = true;
            self::$errorMessage = $e->getMessage();
            return false;
        } catch (Error $e) {
            self::$isError      = true;
            self::$errorMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public static function renderRecoveryWidged($options = array(), $echo = true)
    {
        ob_start();

        $options = array_merge(
            array(
                'selector'   => false,
                'subtitle'   => '',
                'copyLink'   => false,
                'copyButton' => true,
                'launch'     => true,
                'download'   => false,
                'info'       => true
            ),
            (array) $options
        );

        $recoverPackage     = DUP_PRO_Package_Recover::getRecoverPackage();
        $recoverPackageId   = DUP_PRO_Package_Recover::getRecoverPackageId();
        $recoveablePackages = DUP_PRO_Package_Recover::getRecoverablesPackages();
        $selector           = $options['selector'];
        $subtitle           = $options['subtitle'];
        $displayCopyLink    = $options['copyLink'];
        $displayCopyButton  = $options['copyButton'];
        $displayLaunch      = $options['launch'];
        $displayDownload    = $options['download'];
        $displayInfo        = $options['info'];
        $importFailMessage  = '';

        if (!$recoverPackage instanceof DUP_PRO_Package_Recover) {
            $viewMode = self::VIEW_WIDGET_NO_PACKAGE_SET;
        } elseif (!$recoverPackage->isImportable($importFailMessage)) {
            $viewMode = self::VIEW_WIDGET_NOT_VALID;
        } else {
            $viewMode = self::VIEW_WIDGET_VALID;
        }

        require(DUPLICATOR____PATH . '/views/tools/recovery/widget/recovery-widget.php');

        if ($echo) {
            ob_end_flush();
            return '';
        } else {
            return ob_get_clean();
        }
    }

    /**
     * parse view for import-installer
     */
    protected static function doView()
    {
        $recoverPackage     = DUP_PRO_Package_Recover::getRecoverPackage();
        $recoverPackageId   = DUP_PRO_Package_Recover::getRecoverPackageId();
        $recoveablePackages = DUP_PRO_Package_Recover::getRecoverablesPackages();

        require(DUPLICATOR____PATH . '/views/tools/recovery/recovery.php');
    }
}
