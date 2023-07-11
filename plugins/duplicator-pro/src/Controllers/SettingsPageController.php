<?php

/**
 * Settings page controller
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use DUP_PRO_Archive;
use DUP_PRO_CTRL_Storage_Setting;
use DUP_PRO_DB;
use DUP_PRO_Global_Entity;
use DUP_PRO_Log;
use DUP_PRO_Secure_Global_Entity;
use DUP_PRO_Storage_Entity;
use DUP_PRO_U;
use DUP_PRO_Zip_U;
use Duplicator\Core\CapMng;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;
use Duplicator\Core\Controllers\PageAction;
use Duplicator\Core\Controllers\SubMenuItem;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapUtil;

class SettingsPageController extends AbstractMenuPageController
{
    const NONCE_ACTION = 'duppro-settings-package';

    /**
     * tabs menu
     */
    const L2_SLUG_GENERAL      = 'general';
    const L2_SLUG_PACKAGE      = 'package';
    const L2_SLUG_SCHEDULE     = 'schedule';
    const L2_SLUG_STORAGE      = 'storage';
    const L2_SLUG_IMPORT       = 'import';
    const L2_SLUG_CAPABILITIES = 'capabilities';


    /**
     * settings
     */
    const L3_SLUG_GENERAL_SETTINGS       = 'gensettings';
    const L3_SLUG_GENERAL_BETA_FEATHURES = 'bfeathures';
    const L3_SLUG_GENERAL_FEATHURES      = 'profile';
    const L3_SLUG_GENERAL_MIGRATE        = 'migrate';

    /**
     * package
     */
    const L3_SLUG_PACKAGE_BASIC    = 'basic';
    const L3_SLUG_PACKAGE_ADVANCED = 'advanced';
    const L3_SLUG_PACKAGE_BRAND    = 'brand';

    /**
     * storage
     */
    const L3_SLUG_STORAGE_GENERAL  = 'storage-general';
    const L3_SLUG_STORAGE_SSL      = 'ssl';
    const L3_SLUG_STORAGE_STORAGES = 'storage-types';

    /*
     * action types
     */
    const ACTION_GENERAL_SAVE          = 'save';
    const ACTION_GENERAL_TRACE         = 'trace';
    const ACTION_CAPABILITIES_SAVE     = 'cap-save';
    const ACTION_CAPABILITIES_RESET    = 'cap-reset';
    const ACTION_IMPORT_SAVE_SETTINGS  = 'import-save-set';
    const ACTION_PACKAGE_ADVANCED_SAVE = 'pack-adv-save';
    const ACTION_PACKAGE_BASIC_SAVE    = 'pack-basic-save';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::SETTINGS_SUBMENU_SLUG;
        $this->pageTitle    = __('Settings', 'duplicator-pro');
        $this->menuLabel    = __('Settings', 'duplicator-pro');
        $this->capatibility = CapMng::CAP_SETTINGS;
        $this->menuPos      = 60;

        add_filter('duplicator_sub_menu_items_' . $this->pageSlug, array($this, 'getBasicSubMenus'));
        add_filter('duplicator_sub_level_default_tab_' . $this->pageSlug, array($this, 'getSubMenuDefaults'), 10, 2);
        add_action('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
        add_filter('duplicator_page_actions_' . $this->pageSlug, array($this, 'pageActions'));
    }

    /**
     * Return sub menus for current page
     *
     * @param SubMenuItem[] $subMenus sub menus list
     *
     * @return SubMenuItem[]
     */
    public function getBasicSubMenus($subMenus)
    {
        $subMenus[] = new SubMenuItem(self::L2_SLUG_GENERAL, __('General', 'duplicator-pro'));
        $subMenus[] = new SubMenuItem(self::L2_SLUG_PACKAGE, __('Packages', 'duplicator-pro'));
        $subMenus[] = new SubMenuItem(self::L2_SLUG_SCHEDULE, __('Schedules', 'duplicator-pro'));
        $subMenus[] = new SubMenuItem(self::L2_SLUG_STORAGE, __('Storage', 'duplicator-pro'));
        $subMenus[] = new SubMenuItem(self::L2_SLUG_IMPORT, __('Import', 'duplicator-pro'));
        $subMenus[] = new SubMenuItem(self::L2_SLUG_CAPABILITIES, __('Access', 'duplicator-pro'));

        $subMenus[] = new SubMenuItem(self::L3_SLUG_GENERAL_SETTINGS, __('General Settings', 'duplicator-pro'), self::L2_SLUG_GENERAL);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_GENERAL_MIGRATE, __('Migrate Settings', 'duplicator-pro'), self::L2_SLUG_GENERAL);
        //$subMenus[] = new SubMenuItem(self::L3_SLUG_GENERAL_BETA_FEATHURES, __('Beta Features', 'duplicator-pro'), self::L2_SLUG_GENERAL);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_GENERAL_FEATHURES, __('New Features', 'duplicator-pro'), self::L2_SLUG_GENERAL);

        $subMenus[] = new SubMenuItem(self::L3_SLUG_PACKAGE_BASIC, __('Basic Settings', 'duplicator-pro'), self::L2_SLUG_PACKAGE);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_PACKAGE_ADVANCED, __('Advanced Settings', 'duplicator-pro'), self::L2_SLUG_PACKAGE);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_PACKAGE_BRAND, __('Installer Branding', 'duplicator-pro'), self::L2_SLUG_PACKAGE);

        $subMenus[] = new SubMenuItem(self::L3_SLUG_STORAGE_GENERAL, __('General', 'duplicator-pro'), self::L2_SLUG_STORAGE);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_STORAGE_SSL, __('SSL', 'duplicator-pro'), self::L2_SLUG_STORAGE);
        $subMenus[] = new SubMenuItem(self::L3_SLUG_STORAGE_STORAGES, __('Storage Types', 'duplicator-pro'), self::L2_SLUG_STORAGE);

        return $subMenus;
    }

    /**
     * Return slug default for parent menu slug
     *
     * @param string $slug   current default
     * @param string $parent parent for default
     *
     * @return string default slug
     */
    public function getSubMenuDefaults($slug, $parent)
    {
        switch ($parent) {
            case '':
                return self::L2_SLUG_GENERAL;
            case self::L2_SLUG_GENERAL:
                return self::L3_SLUG_GENERAL_SETTINGS;
            case self::L2_SLUG_PACKAGE:
                return self::L3_SLUG_PACKAGE_BASIC;
            case self::L2_SLUG_STORAGE:
                return self::L3_SLUG_STORAGE_GENERAL;
            default:
                return $slug;
        }
    }

    /**
     * Return actions for current page
     *
     * @param PageAction[] $actions actions lists
     *
     * @return PageAction[]
     */
    public function pageActions($actions)
    {
        $actions[] = new PageAction(
            self::ACTION_GENERAL_SAVE,
            array($this, 'saveGeneral'),
            array(
                $this->pageSlug,
                self::L2_SLUG_GENERAL,
                self::L3_SLUG_GENERAL_SETTINGS
            )
        );
        $actions[] = new PageAction(
            self::ACTION_GENERAL_TRACE,
            array($this, 'traceGeneral'),
            array(
                $this->pageSlug,
                self::L2_SLUG_GENERAL,
                self::L3_SLUG_GENERAL_SETTINGS
            )
        );
        $actions[] = new PageAction(
            self::ACTION_CAPABILITIES_SAVE,
            array($this, 'saveCapabilities'),
            array(
                $this->pageSlug,
                self::L2_SLUG_CAPABILITIES
            )
        );
        $actions[] = new PageAction(
            self::ACTION_CAPABILITIES_RESET,
            array($this, 'resetCapabilities'),
            array(
                $this->pageSlug,
                self::L2_SLUG_CAPABILITIES
            )
        );
        $actions[] = new PageAction(
            self::ACTION_PACKAGE_BASIC_SAVE,
            array($this, 'savePackageBasic'),
            array(
                $this->pageSlug,
                self::L2_SLUG_PACKAGE,
                self::L3_SLUG_PACKAGE_BASIC
            )
        );
        $actions[] = new PageAction(
            self::ACTION_PACKAGE_ADVANCED_SAVE,
            array($this, 'savePackageAdv'),
            array(
                $this->pageSlug,
                self::L2_SLUG_PACKAGE,
                self::L3_SLUG_PACKAGE_ADVANCED
            )
        );
        $actions[] = new PageAction(
            'save',
            array($this, 'saveBetaFeathure'),
            array($this->pageSlug,
                self::L2_SLUG_GENERAL,
                self::L3_SLUG_GENERAL_BETA_FEATHURES
            )
        );
        $actions[] = new PageAction(
            self::ACTION_IMPORT_SAVE_SETTINGS,
            array($this, 'saveImportSettngs'),
            array(
                $this->pageSlug,
                self::L2_SLUG_IMPORT
            )
        );
        return $actions;
    }

    /**
     * Render page content
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     *
     * @return void
     */
    public function renderContent($currentLevelSlugs)
    {
        require(DUPLICATOR____PATH . '/ctrls/ctrl.storage.setting.php');

        switch ($currentLevelSlugs[1]) {
            case self::L2_SLUG_GENERAL:
                $this->renderGeneral($currentLevelSlugs);
                break;
            case self::L2_SLUG_PACKAGE:
                $this->renderPackage($currentLevelSlugs);
                break;
            case self::L2_SLUG_IMPORT:
                TplMng::getInstance()->render('admin_pages/settings/import/import');
                break;
            case self::L2_SLUG_SCHEDULE:
                include DUPLICATOR____PATH . '/views/settings/schedule.php';
                break;
            case self::L2_SLUG_STORAGE:
                DUP_PRO_CTRL_Storage_Setting::controller();
                break;
            case self::L2_SLUG_CAPABILITIES:
                TplMng::getInstance()->render('admin_pages/settings/capabilities/capabilites');
                break;
        }
    }

    /**
     * Save general settings
     *
     * @return array<string, mixed>
     */
    public function saveGeneral()
    {
        $result = [
            'saveSuccess' => false
        ];
        $global = DUP_PRO_Global_Entity::getInstance();

        $global->uninstall_settings = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, 'uninstall_settings');
        $global->uninstall_packages = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, 'uninstall_packages');

        $updateStorages = (SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, 'crypt') != $global->crypt);
        if ($updateStorages) {
            $storages = DUP_PRO_Storage_Entity::get_all();
        } else {
            $storages = [];
        }

        $global->crypt                  = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, 'crypt');
        $global->debug_on               = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, '_debug_on');
        $global->unhook_third_party_js  = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, '_unhook_third_party_js');
        $global->unhook_third_party_css = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, '_unhook_third_party_css');

        $this->updateLoggingModeOptions();

        if (($result['saveSuccess'] = $global->save()) == false) {
            $result['errorMessage'] = __('Can\'t update general settings', 'duplicator-pro');
            return $result;
        } else {
            $result['successMessage'] = __("General settings updated.", 'duplicator-pro');
        }

        foreach ($storages as $storage) {
            $storage->save();
        }

        return $result;
    }

    /**
     * Save capabilities settings
     *
     * @return array<string, mixed>
     */
    public function saveCapabilities()
    {
        $result = [
            'saveSuccess' => false
        ];

        $capabilities = [];
        foreach (CapMng::getCapsList() as $capName) {
            $capabilities[$capName] = [
                'roles' => [],
                'users' => [],
            ];

            $inputName = TplMng::getInputName('cap', $capName);
            if (!isset($_REQUEST[$inputName]) || !is_array($_REQUEST[$inputName])) {
                continue;
            }
            foreach ($_REQUEST[$inputName] as $roles) {
                $roles = SnapUtil::sanitizeNSCharsNewlineTrim($roles);
                if (is_numeric($roles)) {
                    $capabilities[$capName]['users'][] = (int) $roles;
                } else {
                    $capabilities[$capName]['roles'][] = $roles;
                }
            }
        }

        if (CapMng::getInstance()->update($capabilities) == false) {
            $result['saveSuccess']  = false;
            $result['errorMessage'] = __('Can\'t update capabilities.', 'duplicator-pro');
            return $result;
        } else {
            $result['successMessage'] = __('Capabilities updated.', 'duplicator-pro');
            $result['saveSuccess']    = true;
        }

        return $result;
    }

    /**
     * Reset capabilities settings
     *
     * @return array<string, mixed>
     */
    public function resetCapabilities()
    {
        $result = [
            'saveSuccess' => false
        ];

        $capabilities = CapMng::getDefaultCaps();
        if (!CapMng::can(CapMng::CAP_LICENSE)) {
            // Can't reset license capability if current user can't manage license
            unset($capabilities[CapMng::CAP_LICENSE]);
        }

        if (CapMng::getInstance()->update($capabilities) == false) {
            $result['saveSuccess']  = false;
            $result['errorMessage'] = __('Can\'t update capabilities.', 'duplicator-pro');
            return $result;
        } else {
            $result['successMessage'] = __('Capabilities updated.', 'duplicator-pro');
            $result['saveSuccess']    = true;
        }

        return $result;
    }

    /**
     * Update trace mode
     *
     * @return array<string, mixed>
     */
    public function traceGeneral()
    {
        $result = [
            'saveSuccess' => false
        ];

        switch (SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, '_logging_mode')) {
            case 'off':
                $this->updateLoggingModeOptions();
                $result = [
                    'saveSuccess' => true,
                    'successMessage' => __("Trace settings have been turned off.", 'duplicator-pro')
                ];
                break;
            case 'on':
                $this->updateLoggingModeOptions();
                $result = [
                    'saveSuccess' => true,
                    'successMessage' => __("Trace settings have been turned on.", 'duplicator-pro')
                ];
                break;
            default:
                $result = [
                    'saveSuccess' => false,
                    'errorMessage' => __("Trace mode not valid.", 'duplicator-pro')
                ];
                break;
        }

        return $result;
    }

    /**
     * Upate loggin modes options
     *
     * @return void
     */
    protected function updateLoggingModeOptions()
    {
        switch (SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, '_logging_mode')) {
            case 'off':
                update_option('duplicator_pro_trace_log_enabled', false, true);
                update_option('duplicator_pro_send_trace_to_error_log', false);
                break;
            case 'on':
                if ((bool) get_option('duplicator_pro_trace_log_enabled') == false) {
                    DUP_PRO_Log::deleteTraceLog();
                }
                update_option('duplicator_pro_trace_log_enabled', true, true);
                update_option('duplicator_pro_send_trace_to_error_log', false);
                break;
            case 'enhanced':
                if (
                    ((bool) get_option('duplicator_pro_trace_log_enabled') == false) ||
                    ((bool) get_option('duplicator_pro_send_trace_to_error_log') == false)
                ) {
                    DUP_PRO_Log::deleteTraceLog();
                }

                update_option('duplicator_pro_trace_log_enabled', true, true);
                update_option('duplicator_pro_send_trace_to_error_log', true);
                break;
            default:
                break;
        }
    }

    /**
     * Save package advanced settings
     *
     * @return array<string, mixed>
     */
    public function savePackageAdv()
    {
        $result  = [
            'saveSuccess' => false
        ];
        $global  = DUP_PRO_Global_Entity::getInstance();
        $sglobal = DUP_PRO_Secure_Global_Entity::getInstance();

        $global->lock_mode       = (int) isset($_REQUEST['lock_mode']) ? $_REQUEST['lock_mode'] : 0;
        $global->json_mode       = (int) $_REQUEST['json_mode'];
        $global->ajax_protocol   = isset($_REQUEST['ajax_protocol']) ? $_REQUEST['ajax_protocol'] : 'admin';
        $global->custom_ajax_url = $_REQUEST['custom_ajax_url'];
        $global->setClientsideKickoff(isset($_REQUEST['_clientside_kickoff']));
        $global->homepath_as_abspath = filter_input(INPUT_POST, 'homepath_as_abspath', FILTER_VALIDATE_BOOLEAN);

        $global->basic_auth_enabled = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, '_basic_auth_enabled');
        if ($global->basic_auth_enabled == true) {
            $global->basic_auth_user = trim($_REQUEST['basic_auth_user']);
        } else {
            $global->basic_auth_user = '';
        }
        $global->installer_base_name        = isset($_REQUEST['_installer_base_name']) ? $_REQUEST['_installer_base_name'] : 'installer.php';
        $global->installer_base_name        = stripslashes($global->installer_base_name);
        $global->chunk_size                 = isset($_REQUEST['_chunk_size']) ? $_REQUEST['_chunk_size'] : 2048;
        $global->skip_archive_scan          = isset($_REQUEST['_skip_archive_scan']);
        $global->php_max_worker_time_in_sec = $_REQUEST['php_max_worker_time_in_sec'];

        if (($result['saveSuccess'] = $global->save()) == false) {
            $result['errorMessage'] = __('Can\'t Save Package Settings', 'duplicator-pro');
            return $result;
        } else {
            $result['successMessage'] = __("Package Settings Saved.", 'duplicator-pro');
        }

        $sglobal->setFromInput(SnapUtil::INPUT_REQUEST);
        $sglobal->save();

        return $result;
    }

    /**
     * Save package basic settings
     *
     * @return array<string, mixed>
     */
    public function savePackageBasic()
    {
        $result = [
            'saveSuccess' => false
        ];
        $global = DUP_PRO_Global_Entity::getInstance();

        $global->setDbMode();
        $global->setArchiveMode();
        $global->max_package_runtime_in_min = (int) $_POST['max_package_runtime_in_min'];
        $global->server_load_reduction      = (int) $_POST['server_load_reduction'];

        switch (SnapUtil::filterInputDefaultSanitizeString(INPUT_POST, 'installer_name_mode')) {
            case DUP_PRO_Global_Entity::INSTALLER_NAME_MODE_WITH_HASH:
                $global->installer_name_mode = DUP_PRO_Global_Entity::INSTALLER_NAME_MODE_WITH_HASH;
                break;
            case DUP_PRO_Global_Entity::INSTALLER_NAME_MODE_SIMPLE:
            default:
                $global->installer_name_mode = DUP_PRO_Global_Entity::INSTALLER_NAME_MODE_SIMPLE;
                break;
        }

        // CLEANUP
        $global->setCleanupFields();

        if (($result['saveSuccess'] = $global->save()) == false) {
            $result['errorMessage'] = __('Can\'t Save Package Settings', 'duplicator-pro');
            return $result;
        } else {
            $result['successMessage'] = __("Package Settings Saved.", 'duplicator-pro');
        }

        return $result;
    }

    /**
     * Save beta feathure action
     *
     * @return array<string, string>
     */
    public function saveBetaFeathure()
    {
        $global = DUP_PRO_Global_Entity::getInstance();
        $result = array();

        // $global->exampleFlag = filter_input(INPUT_POST, 'FIELD NAME', FILTER_VALIDATE_BOOLEAN) */

        if ($global->save() == false) {
            throw new \Exception('Can\'t update settings');
        } else {
            $result['successMessage'] = __('Settings updated.', 'duplicator-pro');
            $global->adjust_settings_for_system();
        }

        return $result;
    }

    /**
     * Save beta feathure action
     *
     * @return array<string, mixed>
     */
    public function saveImportSettngs()
    {
        $result = [
            'saveSuccess' => false
        ];
        $global = DUP_PRO_Global_Entity::getInstance();

        $global->import_chunk_size  = filter_input(
            INPUT_POST,
            'import_chunk_size',
            FILTER_VALIDATE_INT,
            array(
                'options' => array('default' => DUPLICATOR_PRO_DEFAULT_CHUNK_UPLOAD_SIZE)
            )
        );
        $global->import_custom_path = filter_input(
            INPUT_POST,
            'import_custom_path',
            FILTER_CALLBACK,
            array(
                'options' => array(SnapUtil::class, 'sanitizeNSCharsNewlineTrim')
            )
        );
        $newRecoveryCustomPath      = filter_input(
            INPUT_POST,
            'recovery_custom_path',
            FILTER_CALLBACK,
            array(
                'options' => array(SnapUtil::class, 'sanitizeNSCharsNewlineTrim')
            )
        );

        if (
            strlen($global->import_custom_path) > 0 &&
            (
                !is_dir($global->import_custom_path) ||
                !is_readable($global->import_custom_path)
            )
        ) {
            $result['errorMessage']     = __(
                "The custom path isn't a valid directory. " .
                "Check that it exists or that access to it is not restricted by PHP's open_basedir setting.",
                'duplicator-pro'
            );
            $global->import_custom_path = '';
            $result['saveSuccess']      = false;
            return $result;
        }

        $failMessage = '';
        if ($global->setRecoveryCustomPath($newRecoveryCustomPath, $failMessage) == false) {
            $result['saveSuccess']  = false;
            $result['errorMessage'] = $failMessage;
            return $result;
        }

        if (($result['saveSuccess'] = $global->save()) == false) {
            $result['errorMessage'] = __('Can\'t save settings data', 'duplicator-pro');
        } else {
            $result['successMessage'] = __('Settings updated.', 'duplicator-pro');
        }

        return $result;
    }

    /**
     * Render general sub tab
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     *
     * @return void
     */
    protected function renderGeneral($currentLevelSlugs)
    {
        switch ($currentLevelSlugs[2]) {
            case self::L3_SLUG_GENERAL_SETTINGS:
                TplMng::getInstance()->render('admin_pages/settings/general/general');
                break;
            case self::L3_SLUG_GENERAL_BETA_FEATHURES:
                TplMng::getInstance()->render('admin_pages/settings/general/beta_features');
                break;
            case self::L3_SLUG_GENERAL_FEATHURES:
                $displayExperimental = false;
                require DUPLICATOR____PATH . '/views/settings/general/inc.feature.php';
                break;
            case self::L3_SLUG_GENERAL_MIGRATE:
                require DUPLICATOR____PATH . '/views/settings/general/inc.migrate.php';
                break;
        }
    }

    /**
     * Render package sub tab
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     *
     * @return void
     */
    protected function renderPackage($currentLevelSlugs)
    {
        require DUPLICATOR____PATH . '/views/settings/package/main.php';
        switch ($currentLevelSlugs[2]) {
            case self::L3_SLUG_PACKAGE_BASIC:
                TplMng::getInstance()->render('admin_pages/settings/package/inc_basic');
                break;
            case self::L3_SLUG_PACKAGE_ADVANCED:
                TplMng::getInstance()->render('admin_pages/settings/package/inc_advanced');
                break;
            case self::L3_SLUG_PACKAGE_BRAND:
                $view = isset($_REQUEST['view']) ? SnapUtil::sanitize($_REQUEST['view']) : 'list';
                if ($view == 'list') {
                    require DUPLICATOR____PATH . '/views/settings/package/inc.brand.list.php';
                } else {
                    require DUPLICATOR____PATH . '/views/settings/package/inc.brand.edit.php';
                }
                break;
        }
    }

    /**
     * Duisplay shell zip message
     *
     * @param bool $hasShellZip Shell zip enabled
     *
     * @return void
     */
    public static function getShellZipMessage($hasShellZip = false)
    {
        if ($hasShellZip) {
            DUP_PRO_U::esc_html_e('The "Shell Zip" mode allows Duplicator to use the server\'s internal zip command.');
            echo '<br/>';
            DUP_PRO_U::esc_html_e('When available this mode is recommended over the PHP "ZipArchive" mode.');
        } else {
            $scanPath = DUP_PRO_Archive::getScanPaths();
            if (count($scanPath) > 1) {
                echo '</i>';
                echo "<i style='color:maroon'><i class='fa fa-exclamation-triangle'></i> ";
                echo DUP_PRO_U::__("This server is not configured for the Shell Zip engine - please use a different engine mode.");
                echo '</i>';
            } else {
                echo "<i style='color:maroon'><i class='fa fa-exclamation-triangle'></i> ";
                echo DUP_PRO_U::__("This server is not configured for the Shell Zip engine - please use a different engine mode.");
                echo '<br/>';
                printf(
                    DUP_PRO_U::__("Shell Zip is %s recommended %s when available. "),
                    "<a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-package-030-q' target='_blank'>",
                    '</a> '
                );
                printf(
                    DUP_PRO_U::__("For a list of supported hosting providers %s click here %s."),
                    "<a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>",
                    '</a> '
                );
                echo '</i>';
                // Show possible solutions for some linux setups
                $problem_fixes = DUP_PRO_Zip_U::getShellExecZipProblems();
                if (count($problem_fixes) > 0 && ((strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'))) {
                    $shell_tooltip  = ' ';
                    $shell_tooltip .= DUP_PRO_U::__("To make 'Shell Zip' available, ask your host to:");
                    echo '<br/>';
                    $i = 1;
                    foreach ($problem_fixes as $problem_fix) {
                        $shell_tooltip .= "{$i}. {$problem_fix->fix}<br/>";
                        $i++;
                    }
                    $shell_tooltip .= '<br/>';
                    echo "{$shell_tooltip}";
                }
            }
        }
    }

    /**
     * Mysql dump message
     *
     * @param bool   $mysqlDumpFound Found
     * @param string $mysqlDumpPath  mysqldump path
     *
     * @return void
     */
    public static function getMySQLDumpMessage($mysqlDumpFound = false, $mysqlDumpPath = '')
    {
        ?>
        <?php if ($mysqlDumpFound) :
            ?>
            <div class="dup-feature-found">
                <?php echo $mysqlDumpPath ?> &nbsp;
                <small>
                    <i class="fa fa-check-circle"></i>&nbsp;<i><?php DUP_PRO_U::esc_html_e("Successfully Found"); ?></i>
                </small>
            </div>
            <?php
        else :
            ?>
            <div class="dup-feature-notfound">
                <i class="fa fa-exclamation-triangle fa-sm" aria-hidden="true"></i>
                <?php
                self::getMySqlDumpPathProblems($mysqlDumpPath, !empty($mysqlDumpPath));
                ?>
            </div>
            <?php
        endif;
    }

    /**
     * Return purge orphan packages action URL
     *
     * @return string
     */
    public function getTraceTurnOffActionUrl()
    {
        $action = $this->getActionByKey(self::ACTION_GENERAL_TRACE);
        return $this->getMenuLink(
            self::L2_SLUG_GENERAL,
            self::L3_SLUG_GENERAL_SETTINGS,
            array(
                'action'        => $action->getKey(),
                '_wpnonce'      => $action->getNonce(),
                '_logging_mode' => 'off'
            )
        );
    }

    /**
     * Display mysql dump path problems
     *
     * @param string $path      mysqldump path
     * @param bool   $is_custom is custom path
     *
     * @return void
     */
    public static function getMySqlDumpPathProblems($path = '', $is_custom = false)
    {
        $available = DUP_PRO_DB::getMySqlDumpPath();
        $default   = false;
        if ($available) {
            if ($is_custom) {
                if (!DUP_PRO_U::isExecutable($path)) {
                    DUP_PRO_U::esc_html_e(
                        'The mysqldump program at custom path exists but is not executable. Please check file permission to resolve this problem.'
                    );
                    echo ' ';
                    printf(
                        DUP_PRO_U::__("Please check this %s for possible solution."),
                        "<a href='https://snapcreek.com/duplicator/docs/faqs-tech/?180117075128#faq-package-005-q' target='_blank'>" .
                        DUP_PRO_U::__("FAQ page") .
                        "</a>."
                    );
                } else {
                    $default = true;
                }
            } else {
                if (!DUP_PRO_U::isExecutable($available)) {
                    DUP_PRO_U::esc_html_e('The mysqldump program at its default location exists but is not executable. ' .
                    'Please check file permission to resolve this problem.');
                    echo ' ';
                    printf(
                        DUP_PRO_U::esc_html__("Please check this %s for possible solution."),
                        "<a href='https://snapcreek.com/duplicator/docs/faqs-tech/?180117075128#faq-package-005-q' target='_blank'>" .
                        DUP_PRO_U::esc_html__("FAQ page") .
                        "</a>."
                    );
                } else {
                    $default = true;
                }
            }
        } else {
            if ($is_custom) {
                DUP_PRO_U::esc_html_e(
                    'The mysqldump program was not found at its custom path location. ' .
                    'Please check is there some typo mistake or mysqldump program exists on that location. ' .
                    'Also you can leave custom path empty to force automatic settings.'
                );
                echo ' ';
                DUP_PRO_U::esc_html_e(
                    "If the problem persist contact your server admin for the correct path. " .
                    "For a list of approved providers that support mysqldump "
                );
                echo "<a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>" . DUP_PRO_U::esc_html__("click here") . "</a>.";
            } else {
                DUP_PRO_U::esc_html_e(
                    'The mysqldump program was not found at its default location. ' .
                    'To use mysqldump, ask your host to install it or for a custom mysqldump path.'
                );
            }
        }

        if ($default) {
            DUP_PRO_U::esc_html_e(
                'The mysqldump program was not found at its default location or the custom path below. ' .
                'Please enter a valid path where mysqldump can run.'
            );
            echo ' ';
            DUP_PRO_U::esc_html_e(
                "If the problem persist contact your server admin for the correct path. " .
                "For a list of approved providers that support mysqldump "
            );
            echo "<a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>" . DUP_PRO_U::__("click here") . "</a>.";
        }
    }
}
