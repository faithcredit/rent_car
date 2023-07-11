<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use DUP_PRO_CTRL_recovery;
use DUP_PRO_Package_Recover;
use DUP_PRO_U;
use Duplicator\Ajax\AbstractAjaxService;
use Duplicator\Ajax\AjaxWrapper;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\CapMng;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;

class ServicesRecovery extends AbstractAjaxService
{
    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall('wp_ajax_duplicator_pro_get_recovery_widget', 'getWidget');
        $this->addAjaxCall('wp_ajax_duplicator_pro_set_recovery', 'setRecovery');
        $this->addAjaxCall('wp_ajax_duplicator_pro_reset_recovery', 'resetRecovery');
    }

    /**
     * Get recovery widget detail elements
     *
     * @param string $fromPageTab from page/tab unique id
     *
     * @return bool[]
     */
    protected static function getRecoveryDetailsOptions($fromPageTab)
    {
        if ($fromPageTab == ControllersManager::getPageUniqueId(ControllersManager::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_RECOVERY)) {
            $detailsOptions = array(
                'selector'   => true,
                'copyLink'   => true,
                'copyButton' => true,
                'launch'     => true,
                'download'   => true,
                'info'       => true
            );
        } elseif ($fromPageTab == ControllersManager::getPageUniqueId(ControllersManager::IMPORT_SUBMENU_SLUG)) {
            $detailsOptions = array(
                'selector'   => true,
                'launch'     => false,
                'download'   => true,
                'copyLink'   => true,
                'copyButton' => true,
                'info'       => true
            );
        } else {
            $detailsOptions = array();
        }

        return $detailsOptions;
    }

    /**
     * Set recovery callback
     *
     * @return array<string, mixed>
     */
    public static function setRecoveryCallback()
    {
        if (DUP_PRO_CTRL_recovery::actionSetRecoveryPoint() === false) {
            throw new Exception(DUP_PRO_CTRL_recovery::getErrorMessage());
        }

        $recoverPackage = DUP_PRO_Package_Recover::getRecoverPackage();
        if (!$recoverPackage instanceof DUP_PRO_Package_Recover) {
            throw new Exception(DUP_PRO_U::esc_html__('Can\'t get recover package'));
        }
        $fromPageTab    = SnapUtil::filterInputDefaultSanitizeString(INPUT_POST, 'fromPageTab', false);
        $detailsOptions = self::getRecoveryDetailsOptions($fromPageTab);

        if ($fromPageTab == ControllersManager::getPageUniqueId(ControllersManager::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_RECOVERY)) {
            $subtitle = DUP_PRO_U::__('Copy the link and keep it in case of need.');
        } elseif ($fromPageTab == ControllersManager::getPageUniqueId(ControllersManager::IMPORT_SUBMENU_SLUG)) {
            $subtitle = DUP_PRO_U::__('Copy the link and keep it in case of need.');
        } else {
            $subtitle  = DUP_PRO_U::__(
                'Copy the recovery URL link by clicking the recover icon <i class="fas fa-undo-alt"></i> and keep it in a safe place.<br/>'
            );
            $subtitle .= ' ' . sprintf(
                DUP_PRO_U::__(
                    'For full details see <a href="%s">[Recovery Point]</a> settings.'
                ),
                esc_url(DUP_PRO_CTRL_recovery::getRecoverPageLink())
            );
        }

        $result = array(
            'id'             => $recoverPackage->getPackageId(),
            'name'           => $recoverPackage->getPackageName(),
            'recoveryLink'   => $recoverPackage->getInstallLink(),
            'adminMessage'   => DUP_PRO_CTRL_recovery::renderRecoveryWidged(array(
                'selector'   => false,
                'subtitle'   => $subtitle,
                'copyLink'   => false,
                'copyButton' => false,
                'launch'     => false,
                'download'   => false,
                'info'       => false
                ), false),
            'packageDetails' => DUP_PRO_CTRL_recovery::renderRecoveryWidged($detailsOptions, false)
        );

        return $result;
    }

    /**
     * Set recovery action
     *
     * @return void
     */
    public function setRecovery()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'setRecoveryCallback'),
            'duplicator_pro_set_recovery',
            $_POST['nonce'],
            CapMng::CAP_BACKUP_RESTORE
        );
    }

    /**
     * Get widget callback
     *
     * @return string[]
     */
    public static function getWidgetCallback()
    {
        $fromPageTab    = SnapUtil::filterInputDefaultSanitizeString(INPUT_POST, 'fromPageTab', false);
        $detailsOptions = self::getRecoveryDetailsOptions($fromPageTab);

        return array(
            'widget' => DUP_PRO_CTRL_recovery::renderRecoveryWidged($detailsOptions, false)
        );
    }

    /**
     * Get widget action
     *
     * @return void
     */
    public function getWidget()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'getWidgetCallback'),
            'duplicator_pro_get_recovery_widget',
            $_POST['nonce'],
            CapMng::CAP_BACKUP_RESTORE
        );
    }

    /**
     * Reset recovery callback
     *
     * @return string[]
     */
    public static function resetRecoveryCallback()
    {
        if (DUP_PRO_CTRL_recovery::actionResetRecoveryPoint() === false) {
            throw new Exception(DUP_PRO_CTRL_recovery::getErrorMessage());
        }

        $fromPageTab    = SnapUtil::filterInputDefaultSanitizeString(INPUT_POST, 'fromPageTab', false);
        $detailsOptions = self::getRecoveryDetailsOptions($fromPageTab);

        $result = array(
            'adminMessage'   => DUP_PRO_CTRL_recovery::renderRecoveryWidged(array(), false),
            'packageDetails' => DUP_PRO_CTRL_recovery::renderRecoveryWidged($detailsOptions, false)
        );

        return $result;
    }

    /**
     * Reset recovery action
     *
     * @return void
     */
    public function resetRecovery()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'resetRecoveryCallback'),
            'duplicator_pro_reset_recovery',
            $_POST['nonce'],
            CapMng::CAP_BACKUP_RESTORE
        );
    }
}
