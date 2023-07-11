<?php

/**
 * Interface that collects the functions of initial duplicator Bootstrap
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core;

use DUP_PRO_Global_Entity;
use DUP_PRO_Log;
use Duplicator\Core\MigrationMng;
use DUP_PRO_Package;
use DUP_PRO_Package_Importer;
use DUP_PRO_Package_Runner;
use DUP_PRO_Plugin_Upgrade;
use DUP_PRO_Storage_Entity;
use Duplicator\Controllers\ImportPageController;
use Duplicator\Controllers\PackagesPageController;
use Duplicator\Controllers\SettingsPageController;
use Duplicator\Core\Addons\AddonsManager;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\REST\RESTManager;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Utils\ExpireOptions;
use Duplicator\Views\DashboardWidget;
use Error;
use Exception;

class Bootstrap
{
    /**
     *
     * @var string
     */
    private static $addsHash = '';

    /**
     * Init plugin
     *
     * @param string $addsHash pugin hash
     *
     * @return void
     */
    public static function init($addsHash)
    {
        self::$addsHash = $addsHash;

        CapMng::getInstance(); // init capabilties

        if (is_admin()) {
            \DUP_PRO_UI_Notice::init();
            MigrationMng::init();
            DashboardWidget::init();

            $GLOBALS['CTRLS_DUP_PRO_CTRL_Tools']   = new \DUP_PRO_CTRL_Tools();
            $GLOBALS['CTRLS_DUP_PRO_CTRL_Package'] = new \DUP_PRO_CTRL_Package();

            add_action('plugins_loaded', array(__CLASS__, 'pluginsLoaded'));
        }

        add_action('init', array(__CLASS__, 'hookWpInit'));
        add_action('init', array(__CLASS__, 'renameInstallerFile'), 20);

        register_activation_hook(DUPLICATOR____FILE, array(DUP_PRO_Plugin_Upgrade::class, 'onActivationAction'));
        Unistall::registreHooks();

        AddonsManager::getInstance()->inizializeAddons();
        ControllersManager::getInstance();
        RESTManager::getInstance();

        // These are necessary for cron job for cleanup of installer files
        add_action(\DUP_PRO_Global_Entity::CLEANUP_HOOK, array(DUP_PRO_Global_Entity::class, 'cleanupCronJob'));
        add_filter('cron_schedules', array(DUP_PRO_Global_Entity::class, 'customCleanupCronInterval'));
    }

    /**
     * Method called on wordpress hook init action
     *
     * @return void
     */
    public static function hookWpInit()
    {
        if (!AddonsManager::getInstance()->isAddonsReady()) {
            return;
        }

        $web_services = new \DUP_PRO_Web_Services();
        $web_services->init();

        self::initialChecks();

        add_action('admin_init', array(__CLASS__, 'adminInit'));
        add_action('admin_footer', array(__CLASS__, 'adminFooter'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueueGlobalScripts'));

        add_action('wp_ajax_DUP_PRO_UI_ViewState_SaveByPost', array('\DUP_PRO_UI_ViewState', 'saveByPost'));

        if (is_multisite()) {
            add_action('network_admin_menu', array(__CLASS__, 'menu'));
            add_filter('network_admin_plugin_action_links', array(__CLASS__, 'manageLink'), 10, 2);
            add_filter('network_admin_plugin_row_meta', array(__CLASS__, 'metaLinks'), 10, 2);
        } else {
            add_action('admin_menu', array(__CLASS__, 'menu'));
            add_filter('plugin_action_links', array(__CLASS__, 'manageLink'), 10, 2);
            add_filter('plugin_row_meta', array(__CLASS__, 'metaLinks'), 10, 2);
        }
    }

    /**
     * This function is executed on both frontend and backend side.
     * It is in this function that is tested if the plugin should be updated or a schedule should be started.
     *
     * @return void
     */
    public static function initialChecks()
    {
        $exception = null;
        try {
            // Only start the package runner and tracing once it's been confirmed that everything has been installed
            if (get_option(\DUP_PRO_Plugin_Upgrade::DUP_VERSION_OPT_KEY) != DUPLICATOR_PRO_VERSION) {
                return;
            }

            if (
                !is_admin() &&
                ExpireOptions::getUpdate(
                    DUPLICATOR_PRO_FRONTEND_TRANSIENT,
                    true,
                    DUPLICATOR_PRO_FRONTEND_ACTION_DELAY
                ) !== false
            ) {
                return;
            }

            if (
                ExpireOptions::getUpdate(
                    DUPLICATOR_TMP_CLEANUP_CHECK_KEY,
                    true,
                    DUPLICATOR_TMP_CLEANUP_CHECK_DELAY
                ) === false &&
                DUP_PRO_Package::isPackageRunning() === false
            ) {
                DUP_PRO_Package::safe_tmp_cleanup();
            }

            self::dailyActions();
            DUP_PRO_Package_Runner::init();
        } catch (Exception $e) {
            $exception = $e;
        } catch (Error $e) {
            $exception = $e;
        }

        if (!is_null($exception)) {
            $msg = "Initial checks error " . $exception->getMessage() . "\n" . SnapLog::getTextException($exception);
            error_log($msg);
            \DUP_PRO_Log::trace($msg);
        }
    }

    /**
     * Rename old installer file
     *
     * @return void
     */
    public static function renameInstallerFile()
    {
        $exception = null;
        try {
            if (ExpireOptions::getUpdate(DUPLICATOR_PRO_INSTALLER_RENAME_KEY, true, DUPLICATOR_PRO_INSTALLER_RENAME_DELAY) !== false) {
                return;
            }

            MigrationMng::renameInstallersPhpFiles(DUPLICATOR_PRO_INSTALLER_RENAME_DELAY);
        } catch (Exception $e) {
            $exception = $e;
        } catch (Error $e) {
            $exception = $e;
        }

        if (!is_null($exception)) {
            $msg = "Installer rename error " . $exception->getMessage() . "\n" . SnapLog::getTextException($exception);
            error_log($msg);
            \DUP_PRO_Log::trace($msg);
        }
    }

    /**
     * Return plugin hash
     *
     * @return string
     */
    public static function getAddsHash()
    {
        return self::$addsHash;
    }

    /**
     * Method called on admin_init hook
     *
     * @return void
     */
    public static function adminInit()
    {
        self::startInitSettings();

        // custom host init
        \DUP_PRO_Custom_Host_Manager::getInstance()->init();
        \DUP_PRO_CTRL_recovery::init();

        self::registerJsCss();

        $global = \DUP_PRO_Global_Entity::getInstance();
        if ($global->unhook_third_party_js || $global->unhook_third_party_css) {
            add_action('admin_enqueue_scripts', array(__CLASS__, 'unhookThirdPartyAssets'), 99999, 1);
        }

        add_action('admin_head', array('\DUP_PRO_UI_Screen', 'getCustomCss'));

        if (DUPLICATOR_CAPABILITIES_RESET) { // @phpstan-ignore-line
            CapMng::getInstance()->hardReset();
        }
    }

    /**
     * Daily duplicator actions
     *
     * @return void
     */
    protected static function dailyActions()
    {
        if (
            ExpireOptions::getUpdate(
                'daily_bootstrap_actions',
                true,
                DAY_IN_SECONDS
            ) !== false
        ) {
            return;
        }

        try {
            DUP_PRO_Log::trace("Doing daily actions");
            DUP_PRO_Storage_Entity::purgeOldS3MultipartUploads();
            DUP_PRO_Package_Importer::purgeOldImports();
        } catch (Exception $e) {
            DUP_PRO_Log::trace("DAILY BOOTSTRAP ACTIONS ERROR\n" . SnapLog::getTextException($e));
        } catch (Error $e) {
            DUP_PRO_Log::trace("DAILY BOOTSTRAP ACTIONS ERROR\n" . SnapLog::getTextException($e));
        }
    }

    /**
     * Register styles and scripts
     *
     * @return void
     */
    protected static function registerJsCss()
    {
        // wp_doing_ajax introduced in WP 4.7
        if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
            return;
        }

        wp_register_style(
            'dup-plugin-global-style',
            DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/global_admin_style.css',
            [],
            DUPLICATOR_PRO_VERSION
        );

        // CSS
        wp_register_style('dup-pro-jquery-ui', DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/jquery-ui.css', [], "1.11.2");
        wp_register_style('dup-pro-font-awesome', DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/fontawesome-all.min.css', [], '5.7.2');
        wp_register_style('parsley', DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/parsley.css', [], '2.0.6');
        wp_register_style('dup-pro-tippy', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/tippy/dup-pro-tippy.css', [], '3.0.3');
        wp_register_style('formstone', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/formstone/bundle.css', [], 'v1.4.16-1');
        wp_register_style('jstree', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/jstree/themes/snap/style.css', [], '3.8.1');
        wp_register_style(
            'dup-pro-select2',
            DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/select2/css/select2.min.css',
            [],
            DUPLICATOR_PRO_VERSION
        );
        wp_register_style('dup-pro-plugin-style', DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/style.css', [
            'dup-pro-jquery-ui',
            'dup-pro-font-awesome',
            'dup-pro-select2',
            'parsley',
            'dup-pro-tippy',
            'jstree'
        ], DUPLICATOR_PRO_VERSION);
        wp_register_style(
            'dup-pro-import',
            DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/import.css',
            ['dup-pro-plugin-style', 'formstone'],
            DUPLICATOR_PRO_VERSION
        );
        wp_register_style(
            'dup-pro-packages',
            DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/packages.css',
            ['dup-pro-plugin-style'],
            DUPLICATOR_PRO_VERSION
        );
        wp_register_style(
            'dup-plugin-style-ctrl',
            DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/style-ctrl.css',
            ['dup-pro-plugin-style'],
            DUPLICATOR_PRO_VERSION
        );

        //JS
        wp_register_script('dup-pro-handlebars', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/handlebars.min.js', ['jquery'], '4.0.10');
        wp_register_script('parsley', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/parsleyjs/parsley.min.js', ['jquery'], '2.9.2');
        wp_register_script('popper', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/popper/popper.min.js', [], '2.4.4');
        wp_register_script('dup-pro-tippy', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/tippy/tippy-bundle.umd.min.js', ['popper'], '6.2.6');
        wp_register_script('formstone', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/formstone/bundle.js', ['jquery'], 'v1.4.16-1');
        wp_register_script('jstree', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/jstree/jstree.min.js', [], '3.3.7');
        wp_register_script('jscookie', DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/jscookie/js.cookie.min.js', [], '3.0.0');
        wp_register_script(
            'dup-pro-select2',
            DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/select2/js/select2.js',
            ['jquery'],
            DUPLICATOR_PRO_VERSION,
            true
        );
        wp_register_script(
            'duplicator-tooltip',
            DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/duplicator-tooltip.js',
            ['dup-pro-tippy'],
            DUPLICATOR_PRO_VERSION
        );
        wp_register_script(
            'dup-pro-import-installer',
            DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/import-installer.js',
            ['jquery'],
            DUPLICATOR_PRO_VERSION,
            true
        );
    }

    /**
     * Enqueue CSS Styles:
     * Loads all CSS style libs/source for DupPro
     *
     * @return void
     */
    public static function enqueueStyles()
    {
        wp_enqueue_style('dup-pro-plugin-style');
        wp_enqueue_style('dup-plugin-style-ctrl');

        if (ImportPageController::getInstance()->isCurrentPage()) {
            wp_enqueue_style('dup-pro-import');
        }

        if (\DUP_PRO_CTRL_Tools::isToolPage()) {
            wp_enqueue_style('dup-pro-import');
        }
    }

    /**
     * Enqueue Global CSS Styles
     *
     * @return void
     */
    public static function enqueueGlobalStyles()
    {
        wp_enqueue_style('dup-plugin-global-style');
    }

    /**
     * Hooked into `admin_enqueue_scripts`.  Init routines for all admin pages
     *
     * @return void
     */
    public static function enqueueGlobalScripts()
    {
        wp_enqueue_script(
            'dup-pro-global-script',
            DUPLICATOR_PRO_PLUGIN_URL . 'assets/js/global-admin-script.js',
            array('jquery'),
            DUPLICATOR_PRO_VERSION,
            true
        );
        wp_localize_script(
            'dup-pro-global-script',
            'dup_pro_global_script_data',
            array(
                'nonce_admin_notice_to_dismiss' => wp_create_nonce('duplicator_pro_admin_notice_to_dismiss'),
                'nonce_dashboard_widged_info' =>  wp_create_nonce("duplicator_pro_dashboad_widget_info"),
                'nonce_dashboard_widged_dismiss_recommended' => wp_create_nonce("duplicator_pro_dashboad_widget_dismiss_recommended"),
                'ajaxurl' => admin_url('admin-ajax.php')
            )
        );
    }

    /**
     * Enqueue Scripts:
     * Loads all required javascript libs/source for DupPro
     *
     * @return void
     */
    public static function enqueueScripts()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-color');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-progressbar');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('parsley');
        wp_enqueue_script('accordion');
        wp_enqueue_script('duplicator-tooltip');
        wp_enqueue_script('formstone');
        wp_enqueue_script('jstree');
        wp_enqueue_script('jscookie');
        wp_enqueue_script('dup-pro-select2');
    }

    /**
     * Plugins Loaded:
     * Hooked into `plugin_loaded`.  Called once any activated plugins have been loaded.
     *
     * @return void
     */
    public static function pluginsLoaded()
    {
        if (DUPLICATOR_PRO_VERSION != get_option(\DUP_PRO_Plugin_Upgrade::DUP_VERSION_OPT_KEY)) {
            \DUP_PRO_Plugin_Upgrade::onActivationAction();
        }
        load_plugin_textdomain(\DUP_PRO_Constants::PLUGIN_SLUG, false, dirname(plugin_basename(DUPLICATOR____FILE)) . '/lang/');

        try {
            self::patchedDataInitialization();
        } catch (\Exception $ex) {
            \DUP_PRO_Log::traceError("Could not do data initialization. " . $ex->getMessage());
        }
    }

    /**
     * Init settings check
     *
     * @return void
     */
    public static function startInitSettings()
    {
        if (!empty($_REQUEST['dup_pro_clear_schedule_failure'])) {
            $system_global                  = \DUP_PRO_System_Global_Entity::getInstance();
            $system_global->schedule_failed = false;
            $system_global->save();
        }

        if (!defined('WP_MAX_MEMORY_LIMIT')) {
            define('WP_MAX_MEMORY_LIMIT', '256M');
        }

        if (SnapUtil::isIniValChangeable('memory_limit')) {
            @ini_set('memory_limit', WP_MAX_MEMORY_LIMIT);
        }
    }

    /**
     * Action Hook:
     * Hooked into `admin_menu`.  Loads all of the admin menus for DupPro
     *
     * @return void
     */
    public static function menu()
    {
        ControllersManager::getInstance()->registerMenu();

        $page_packages                     = \Duplicator\Controllers\PackagesPageController::getInstance()->getMenuHookSuffix();
        $GLOBALS['DUP_PRO_Package_Screen'] = new \DUP_PRO_Package_Screen($page_packages);

        $page_import    = \Duplicator\Controllers\ImportPageController::getInstance()->getMenuHookSuffix();
        $page_schedules = \Duplicator\Controllers\SchedulePageController::getInstance()->getMenuHookSuffix();
        $page_storage   = \Duplicator\Controllers\StoragePageController::getInstance()->getMenuHookSuffix();
        $page_debug     = \Duplicator\Controllers\DebugPageController::getInstance()->getMenuHookSuffix();
        $page_settings  = \Duplicator\Controllers\SettingsPageController::getInstance()->getMenuHookSuffix();
        $page_tools     = \Duplicator\Controllers\ToolsPageController::getInstance()->getMenuHookSuffix();
        $page_installer = \Duplicator\Controllers\ImportInstallerPageController::getInstance()->getMenuHookSuffix();

        //Apply Scripts
        add_action('admin_print_scripts-' . $page_packages, array(__CLASS__, 'enqueueScripts'));
        add_action('admin_print_scripts-' . $page_import, array(__CLASS__, 'enqueueScripts'));
        add_action('admin_print_scripts-' . $page_schedules, array(__CLASS__, 'enqueueScripts'));
        add_action('admin_print_scripts-' . $page_storage, array(__CLASS__, 'enqueueScripts'));
        add_action('admin_print_scripts-' . $page_settings, array(__CLASS__, 'enqueueScripts'));
        add_action('admin_print_scripts-' . $page_tools, array(__CLASS__, 'enqueueScripts'));
        add_action('admin_print_scripts-' . $page_debug, array(__CLASS__, 'enqueueScripts'));

        //Apply Styles
        add_action('admin_print_styles-' . $page_packages, array(__CLASS__, 'enqueueStyles'));
        add_action('admin_print_styles-' . $page_import, array(__CLASS__, 'enqueueStyles'));
        add_action('admin_print_styles-' . $page_schedules, array(__CLASS__, 'enqueueStyles'));
        add_action('admin_print_styles-' . $page_storage, array(__CLASS__, 'enqueueStyles'));
        add_action('admin_print_styles-' . $page_settings, array(__CLASS__, 'enqueueStyles'));
        add_action('admin_print_styles-' . $page_tools, array(__CLASS__, 'enqueueStyles'));
        add_action('admin_print_styles-' . $page_debug, array(__CLASS__, 'enqueueStyles'));
        add_action('admin_print_styles', array(__CLASS__, 'enqueueGlobalStyles'));
    }

    /**
     * Data Patches:
     * Handles data that needs to be initialized because of fixes etc
     *
     * @return void
     */
    protected static function patchedDataInitialization()
    {
        $global = \DUP_PRO_Global_Entity::getInstance();
        $global->configure_dropbox_transfer_mode();

        if ($global->initial_activation_timestamp == 0) {
            $global->initial_activation_timestamp = time();
            $global->save();
        }
    }

    /**
     * Remove all external styles and scripts coming from other plugins
     * which may cause compatibility issue, especially with React
     *
     * @param string $hook Hook string
     *
     * @return void
     */
    public static function unhookThirdPartyAssets($hook)
    {
        $mainPageSuffix = \Duplicator\Controllers\MainPageController::getInstance()->getMenuHookSuffix();
        if (strpos($hook, $mainPageSuffix) !== false) {
            $global = \DUP_PRO_Global_Entity::getInstance();
            $assets = array();

            if ($global->unhook_third_party_css) {
                $assets['styles'] = wp_styles();
            }

            if ($global->unhook_third_party_js) {
                $assets['scripts'] = wp_scripts();
            }

            foreach ($assets as $type => $asset) {
                foreach ($asset->registered as $handle => $dep) {
                    $src = $dep->src;
                    // test if the src is coming from /wp-admin/ or /wp-includes/ or /wp-fsqm-pro/.
                    if (
                        is_string($src) && // For some built-ins, $src is true|false
                        strpos($src, 'wp-admin') === false &&
                        strpos($src, 'wp-include') === false &&
                        // things below are specific to your plugin, so change them
                        strpos($src, 'duplicator-pro') === false &&
                        strpos($src, 'woocommerce') === false &&
                        strpos($src, 'jetpack') === false &&
                        strpos($src, 'debug-bar') === false
                    ) {
                        'scripts' === $type ? wp_dequeue_script($handle) : wp_dequeue_style($handle);
                    }
                }
            }
        }
    }

    /**
     * Plugin MetaData:
     * Adds the manage link in the plugins list
     *
     * @param string[] $links links list
     * @param string   $file  plugin file
     *
     * @return string[] The manage link in the plugins list
     */
    public static function manageLink($links, $file)
    {
        static $this_plugin;

        if (!$this_plugin) {
            $this_plugin = plugin_basename(DUPLICATOR____FILE);
        }

        if ($file == $this_plugin) {
            $url           = ControllersManager::getMenuLink(ControllersManager::PACKAGES_SUBMENU_SLUG);
            $settings_link = "<a href='$url'>" . \DUP_PRO_U::__('Manage') . '</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    /**
     * Plugin MetaData:
     * Adds links to the plugins manager page
     *
     * @param string[] $links links list
     * @param string   $file  plugin file
     *
     * @return string[] The meta help link data for the plugins manager
     */
    public static function metaLinks($links, $file)
    {
        $plugin = plugin_basename(DUPLICATOR____FILE);
        if ($file == $plugin) {
            $help_url = ControllersManager::getMenuLink(ControllersManager::TOOLS_SUBMENU_SLUG);
            $links[]  = sprintf('<a href="%1$s" title="%2$s">%3$s</a>', esc_url($help_url), \DUP_PRO_U::__('Get Help'), \DUP_PRO_U::__('Help'));

            return $links;
        }
        return $links;
    }

    /**
     * Footer Hook:
     * Hooked into `admin_footer`.  Returns display elements for the admin footer area
     *
     * @return void
     */
    public static function adminFooter()
    {
        if (
            !ControllersManager::getInstance()->isDuplicatorPage() ||
            !get_option('duplicator_pro_trace_log_enabled', false)
        ) {
            return;
        }

        if (!CapMng::can(CapMng::CAP_SETTINGS, false) && !CapMng::can(CapMng::CAP_CREATE, false)) {
            return;
        }

        $txt_trace_zero = esc_html__('Download', 'duplicator-pro') . ' (0B)';
        $turnOffUrl     = SettingsPageController::getInstance()->getTraceTurnOffActionUrl();
        $traceLogUrl    = ControllersManager::getMenuLink(
            ControllersManager::TOOLS_SUBMENU_SLUG,
            ToolsPageController::L2_SLUG_DISAGNOSTIC,
            ToolsPageController::L3_SLUG_DISAGNOSTIC_LOG
        );

        $ajaxGetTraceUrl = admin_url('admin-ajax.php') . '?' . http_build_query(array(
            'action' => 'duplicator_pro_get_trace_log',
            'nonce'  => wp_create_nonce('duplicator_pro_get_trace_log'),
        ));

        if (
            ControllersManager::isCurrentPage(
                ControllersManager::TOOLS_SUBMENU_SLUG,
                ToolsPageController::L2_SLUG_DISAGNOSTIC,
                ToolsPageController::L3_SLUG_DISAGNOSTIC_LOG
            )
        ) {
            $clear_trace_log_js = 'DupPro.UI.ClearTraceLog(1);';
        } else {
            $clear_trace_log_js = 'DupPro.UI.ClearTraceLog(0); jQuery("#dup_pro_trace_txt").html(' . json_encode($txt_trace_zero) . '); ';
        }
        ?>
        <style>
            p#footer-upgrade {
                display: none
            }
        </style>
        <div id="dpro-monitor-trace-area">
            <b><?php esc_html_e('TRACE LOG OPTIONS', 'duplicator-pro'); ?></b><br />
            <?php if (CapMng::can(CapMng::CAP_CREATE, false)) { ?>
                <a class="button button-small" href="<?php echo esc_url($traceLogUrl); ?>" target="_duptracelog">
                    <i class="fa fa-file-alt"></i> <?php esc_html_e('View', 'duplicator-pro'); ?>
                </a>
                <a class="button button-small" onclick="<?php echo esc_attr($clear_trace_log_js); ?>">
                    <i class="fa fa-times"></i> <?php esc_html_e('Clear', 'duplicator-pro'); ?>
                </a>
                <a class="button button-small" onclick="<?php echo esc_attr('location.href = ' . json_encode($ajaxGetTraceUrl) . ';'); ?>">
                    <i class="fa fa-download"></i> <span id="dup_pro_trace_txt">
                        <?php echo esc_html__('Download', 'duplicator-pro') . ' (' . \DUP_PRO_Log::getTraceStatus() . ')'; ?>
                    </span>
                </a>
            <?php } ?>
            <?php if (CapMng::can(CapMng::CAP_SETTINGS, false)) { ?>
                <a class="button button-small" href="<?php echo esc_url($turnOffUrl); ?>">
                    <i class="fa fa-power-off"></i> <?php echo esc_html__('Turn Off', 'duplicator-pro'); ?>
                </a>
            <?php } ?>
        </div>
        <?php
    }
}
