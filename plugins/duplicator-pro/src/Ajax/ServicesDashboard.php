<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use DUP_PRO_Package;
use Duplicator\Ajax\AjaxWrapper;
use Duplicator\Core\CapMng;
use Duplicator\Views\DashboardWidget;

class ServicesDashboard extends AbstractAjaxService
{
    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall('wp_ajax_duplicator_pro_dashboad_widget_info', 'dashboardWidgetInfo');
        $this->addAjaxCall('wp_ajax_duplicator_pro_dismiss_recommended_plugin', 'dismissRecommendedPlugin');
    }

    /**
     * Set recovery callback
     *
     * @return array<string, mixed>
     */
    public static function dashboardWidgetInfoCallback()
    {
        $result = [
            'isRunning' => DUP_PRO_Package::isPackageRunning(),
            'lastBackupInfo' => DashboardWidget::getLastBackupString()
        ];
        return $result;
    }

    /**
     * Set recovery action
     *
     * @return void
     */
    public function dashboardWidgetInfo()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'dashboardWidgetInfoCallback'),
            'duplicator_pro_dashboad_widget_info',
            $_POST['nonce'],
            CapMng::CAP_BASIC
        );
    }

    /**
     * Set dismiss recommended callback
     *
     * @return bool
     */
    public static function dismissRecommendedPluginCallback()
    {
        return (update_user_meta(get_current_user_id(), DashboardWidget::RECOMMENDED_PLUGIN_DISMISSED_OPT_KEY, true) !== false);
    }

    /**
     * Set recovery action
     *
     * @return void
     */
    public function dismissRecommendedPlugin()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'dismissRecommendedPluginCallback'),
            'duplicator_pro_dashboad_widget_dismiss_recommended',
            $_POST['nonce'],
            CapMng::CAP_BASIC
        );
    }
}
