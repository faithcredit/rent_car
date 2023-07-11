<?php

namespace Duplicator\Views;

use DUP_PRO_Package;
use DUP_PRO_Package_Recover;
use DUP_PRO_Package_Template_Entity;
use DUP_PRO_PackageStatus;
use DUP_PRO_Schedule_Entity;
use DUP_PRO_Storage_Entity;
use Duplicator\Core\CapMng;
use Duplicator\Core\Views\TplMng;

/**
 * Dashboard widget
 */
class DashboardWidget
{
    const LAST_PACKAGE_TIME_WARNING            = 86400; // 24 hours
    const LAST_PACKAGES_LIMIT                  = 3;
    const RECOMMENDED_PLUGIN_ENABLED           = false;
    const RECOMMENDED_PLUGIN_DISMISSED_OPT_KEY = 'duplicator_recommended_plugin_dismissed';

    /**
     * Add the dashboard widget
     *
     * @return void
     */
    public static function init()
    {
        if (is_multisite()) {
            add_action('wp_network_dashboard_setup', array(__CLASS__, 'addDashboardWidget'));
        } else {
            add_action('wp_dashboard_setup', array(__CLASS__, 'addDashboardWidget'));
        }
    }

    /**
     * Render the dashboard widget
     *
     * @return void
     */
    public static function addDashboardWidget()
    {
        if (!CapMng::can(CapMng::CAP_BASIC, false)) {
            return;
        }

        wp_add_dashboard_widget(
            'duplicator_dashboard_widget',
            __('Duplicator', 'duplicator'),
            array(__CLASS__, 'renderContent')
        );
    }

    /**
     * Render the dashboard widget content
     *
     * @return void
     */
    public static function renderContent()
    {
        TplMng::getInstance()->setStripSpaces(true);
        ?>
        <div class="dup-dashboard-widget-content">
            <?php
            self::renderPackageCreate();
            self::renderRecentlyPackages();
            self::renderSections();
            if (self::RECOMMENDED_PLUGIN_ENABLED) { // @phpstan-ignore-line
                self::renderRecommendedPluginSection();
            }
            ?>
        </div>
        <?php
    }

    /**
     * Render the package create button
     *
     * @return void
     */
    protected static function renderPackageCreate()
    {
        TplMng::getInstance()->render(
            'parts/DashboardWidget/package-create-section',
            [
                'lastBackupString' => self::getLastBackupString()
            ]
        );
    }

    /**
     * Render the last packages
     *
     * @return void
     */
    protected static function renderRecentlyPackages()
    {
        /** @var DUP_PRO_Package[] */
        $packages = DUP_PRO_Package::get_packages_by_status(
            [
                [
                    'op' => '>=',
                    'status' => DUP_PRO_PackageStatus::COMPLETE
                ]
            ],
            self::LAST_PACKAGES_LIMIT,
            0,
            'created DESC'
        );

        $totalsIds = DUP_PRO_Package::get_ids_by_status(
            [
                [
                    'op' => '>=',
                    'status' => DUP_PRO_PackageStatus::COMPLETE
                ]
            ]
        );

        $failuresIds = DUP_PRO_Package::get_ids_by_status(
            [
                [
                    'op' => '<',
                    'status' => 0
                ]
            ]
        );

        TplMng::getInstance()->render(
            'parts/DashboardWidget/recently-packages',
            [
                'packages'     => $packages,
                'totalPackages' => count($totalsIds),
                'totalFailures' => count($failuresIds)
            ]
        );
    }

    /**
     * Render Duplicate sections
     *
     * @return void
     */
    protected static function renderSections()
    {
        $storages         = DUP_PRO_Storage_Entity::get_all();
        $templates        = DUP_PRO_Package_Template_Entity::getAllWithoutManualMode();
        $schedules        = DUP_PRO_Schedule_Entity::getAll();
        $schedulesEnabled = DUP_PRO_Schedule_Entity::get_active();

        $nextRunTime = -1;
        foreach ($schedulesEnabled as $schedule) {
            if ($schedule->next_run_time < $nextRunTime || $nextRunTime === -1) {
                $nextRunTime = $schedule->next_run_time;
            }
        }
        $nextDate  = date(get_option('date_format'), $nextRunTime);
        $nextHours = date(get_option('time_format'), $nextRunTime);

        if (DUP_PRO_Package_Recover::getRecoverPackageId() !== false) {
            $recoverPackage    = DUP_PRO_Package::get_by_id(DUP_PRO_Package_Recover::getRecoverPackageId());
            $recoverTime       = strtotime($recoverPackage->Created);
            $recoverDate       = date(get_option('date_format'), $recoverTime);
            $recoverHours      = date(get_option('time_format'), $recoverTime);
            $recoverDateString = $recoverDate . ' ' . $recoverHours;
        } else {
            $recoverDateString = '';
        }

        TplMng::getInstance()->render(
            'parts/DashboardWidget/sections-section',
            [
                'numSchedules'        => count($schedules),
                'numSchedulesEnabled' => count($schedulesEnabled),
                'numTemplates'        => count($templates),
                'numStorages'         => count($storages),
                'nextScheduleString'  => ($nextRunTime >= 0 ? $nextDate . ' ' . $nextHours : ''),
                'recoverDateString'   => $recoverDateString
            ]
        );
    }

    /**
     * Get the last backup string
     *
     * @return string HTML string
     */
    public static function getLastBackupString()
    {
        if (DUP_PRO_Package::isPackageRunning()) {
            return '<span class="spinner"></span> <b>' . esc_html__('A package is currently running.', 'duplicator-pro') . '</b>';
        }

        /** @var DUP_PRO_Package[] */
        $lastPackage = DUP_PRO_Package::get_packages_by_status(
            [
                [
                    'op' => '>=',
                    'status' => DUP_PRO_PackageStatus::COMPLETE
                ]
            ],
            1,
            0,
            'created DESC'
        );

        if (empty($lastPackage)) {
            return '<b>' . esc_html__('No packages have been created yet.', 'duplicator-pro') . '</b>';
        }

        $createdTime = date(get_option('date_format'), strtotime($lastPackage[0]->Created));

        if ($lastPackage[0]->getPackageLife() > self::LAST_PACKAGE_TIME_WARNING) {
            $timeDiffClass = 'maroon';
        } else {
            $timeDiffClass = 'green';
        }

        $timeDiff = sprintf(
            _x('%s ago', '%s represents the time diff, eg. 2 days', 'duplicator-pro'),
            $lastPackage[0]->getPackageLife('human')
        );

        return '<b>' . $createdTime . '</b> ' .
            " (" . '<span class="' . $timeDiffClass . '"><b>' .
            $timeDiff .
            '</b></span>' . ")";
    }

    /**
     * Return randomly chosen one of recommended plugins.
     *
     * @return false|array{name: string,slug: string,more: string,pro: array{file: string}}
     */
    protected static function getRecommendedPluginData()
    {
        $plugins = [
            'google-analytics-for-wordpress/googleanalytics.php' => [
                'name' => __('MonsterInsights', 'wpforms-lite'),
                'slug' => 'google-analytics-for-wordpress',
                'more' => 'https://www.monsterinsights.com/',
                'pro'  => [
                    'file' => 'google-analytics-premium/googleanalytics-premium.php',
                ],
            ],
            'all-in-one-seo-pack/all_in_one_seo_pack.php' => [
                'name' => __('AIOSEO', 'wpforms-lite'),
                'slug' => 'all-in-one-seo-pack',
                'more' => 'https://aioseo.com/',
                'pro'  => [
                    'file' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
                ],
            ],
            'coming-soon/coming-soon.php'                 => [
                'name' => __('SeedProd', 'wpforms-lite'),
                'slug' => 'coming-soon',
                'more' => 'https://www.seedprod.com/',
                'pro'  => [
                    'file' => 'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php',
                ],
            ],
            'wp-mail-smtp/wp_mail_smtp.php'               => [
                'name' => __('WP Mail SMTP', 'wpforms-lite'),
                'slug' => 'wp-mail-smtp',
                'more' => 'https://wpmailsmtp.com/',
                'pro'  => [
                    'file' => 'wp-mail-smtp-pro/wp_mail_smtp.php',
                ],
            ],
        ];

        $installed = get_plugins();

        foreach ($plugins as $id => $plugin) {
            if (isset($installed[$id])) {
                unset($plugins[$id]);
            }

            if (isset($installed[$plugin['pro']['file']])) {
                unset($plugins[$id]);
            }
        }
        return ($plugins ? $plugins[ array_rand($plugins) ] : false);
    }

    /**
     * Recommended plugin block HTML.
     *
     * @return void
     */
    public static function renderRecommendedPluginSection()
    {
        if (get_user_meta(get_current_user_id(), self::RECOMMENDED_PLUGIN_DISMISSED_OPT_KEY, true) != false) {
            return;
        }

        $plugin = self::getRecommendedPluginData();

        if (empty($plugin)) {
            return;
        }

        $installUrl = wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=' . rawurlencode($plugin['slug'])),
            'install-plugin_' . $plugin['slug']
        );

        TplMng::getInstance()->render(
            'parts/DashboardWidget/recommended-section',
            [
                'plugin'     => $plugin,
                'installUrl' => $installUrl,
            ]
        );
    }
}
