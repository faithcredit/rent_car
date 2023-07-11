<?php

/**
 * Controller params manager
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Deploy\Plugins\PluginsManager;
use Duplicator\Installer\Core\Params\Descriptors\ParamDescDatabase;
use Duplicator\Installer\Core\Params\Descriptors\ParamDescEngines;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;

/**
 * singleton class
 */
final class DUPX_Ctrl_Params
{
    /** @var bool this variable becomes false if there was something wrong with the validation but the basic is true */
    private static $paramsValidated = true;

    /**
     * returns false if at least one param has not been validated
     *
     * @return bool
     */
    public static function isParamsValidated()
    {
        return self::$paramsValidated;
    }

    /**
     * Set base params
     *
     * @return bool
     */
    public static function setParamsBase()
    {
        Log::info('CTRL PARAMS BASE', Log::LV_DETAILED);
        $paramsManager = PrmMng::getInstance();
        $paramsManager->setValueFromInput(PrmMng::PARAM_CTRL_ACTION, ParamForm::INPUT_REQUEST);
        $paramsManager->setValueFromInput(PrmMng::PARAM_STEP_ACTION, ParamForm::INPUT_REQUEST);
        return true;
    }

    /**
     *
     * @return bool
     */
    public static function setParamsStep0()
    {
        Log::info('CTRL PARAMS S0', Log::LV_DETAILED);
        Log::info('REQUEST: ' . Log::v2str($_REQUEST), Log::LV_HARD_DEBUG);
        $paramsManager = PrmMng::getInstance();

        DUPX_ArchiveConfig::getInstance()->setNewPathsAndUrlParamsByMainNew();
        DUPX_Custom_Host_Manager::getInstance()->setManagedHostParams();

        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     *
     * @return boolean
     */
    public static function setParamsStep1()
    {
        Log::info('CTRL PARAMS S1', Log::LV_DETAILED);
        Log::info('REQUEST: ' . Log::v2str($_REQUEST), Log::LV_HARD_DEBUG);
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $paramsManager  = PrmMng::getInstance();
        $paramsManager->setValueFromInput(PrmMng::PARAM_LOGGING, ParamForm::INPUT_POST);
        Log::setLogLevel();

        $oldSubsiteId = $paramsManager->getValue(PrmMng::PARAM_SUBSITE_ID);

        $readParamsList = array(
            PrmMng::PARAM_INST_TYPE,
            PrmMng::PARAM_PATH_NEW,
            PrmMng::PARAM_URL_NEW,
            PrmMng::PARAM_PATH_WP_CORE_NEW,
            PrmMng::PARAM_SITE_URL,
            PrmMng::PARAM_PATH_CONTENT_NEW,
            PrmMng::PARAM_URL_CONTENT_NEW,
            PrmMng::PARAM_PATH_UPLOADS_NEW,
            PrmMng::PARAM_URL_UPLOADS_NEW,
            PrmMng::PARAM_PATH_PLUGINS_NEW,
            PrmMng::PARAM_URL_PLUGINS_NEW,
            PrmMng::PARAM_PATH_MUPLUGINS_NEW,
            PrmMng::PARAM_URL_MUPLUGINS_NEW,
            PrmMng::PARAM_SUBSITE_ID,
            PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING,
            PrmMng::PARAM_ARCHIVE_ACTION,
            PrmMng::PARAM_ARCHIVE_ENGINE,
            PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES,
            PrmMng::PARAM_DB_ENGINE,
            PrmMng::PARAM_REPLACE_ENGINE,
            PrmMng::PARAM_USERS_MODE,
            PrmMng::PARAM_MU_REPLACE,
            PrmMng::PARAM_ADD_SUBSITE_USER_MODE,
            PrmMng::PARAM_SET_FILE_PERMS,
            PrmMng::PARAM_SET_DIR_PERMS,
            PrmMng::PARAM_FILE_PERMS_VALUE,
            PrmMng::PARAM_DIR_PERMS_VALUE,
            PrmMng::PARAM_SAFE_MODE,
            PrmMng::PARAM_WP_CONFIG,
            PrmMng::PARAM_HTACCESS_CONFIG,
            PrmMng::PARAM_OTHER_CONFIG,
            PrmMng::PARAM_FILE_TIME,
            PrmMng::PARAM_REMOVE_RENDUNDANT,
            PrmMng::PARAM_REMOVE_USERS_WITHOUT_PERMISSIONS,
            PrmMng::PARAM_BLOGNAME,
            PrmMng::PARAM_ACCEPT_TERM_COND,
            PrmMng::PARAM_ZIP_THROTTLING
        );

        foreach ($readParamsList as $cParam) {
            Log::info('SET VALUE ' . $cParam);
            if ($paramsManager->setValueFromInput($cParam, ParamForm::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        $paramsManager->setValue(PrmMng::PARAM_REPLACE_ENGINE, ParamDescEngines::getReplaceEngineModeFromParams());
        $paramsManager->setValue(PrmMng::PARAM_DB_CHUNK, ParamDescEngines::getDbChunkFromParams());

        self::setParamsDatabase();
        self::updateBlogname($oldSubsiteId);

        if (self::$paramsValidated) {
            self::resetUrlAndPathsFromOverwriteData();
            self::setParamsOnAddSiteOnMultisite();

            Log::info('UPDATE PARAMS FROM SUBSITE ID', Log::LV_DEBUG);
            Log::info('NETWORK INSTALL: ' . Log::v2str(DUPX_InstallerState::isNewSiteIsMultisite()), Log::LV_DEBUG);

            // UPDATE ACTIVE PARAMS BY SUBSITE ID
            $subsiteId = $paramsManager->getValue(PrmMng::PARAM_SUBSITE_ID);
            Log::info('SUBSITE ID: ' . Log::v2str($subsiteId), Log::LV_DEBUG);

            $activePlugins = PluginsManager::getInstance()->getDefaultActivePluginsList($subsiteId);
            $paramsManager->setValue(PrmMng::PARAM_PLUGINS, $activePlugins);

            // IF SAFE MODE DISABLE ALL PLUGINS
            if ($paramsManager->getValue(PrmMng::PARAM_SAFE_MODE) > 0) {
                $forceDisable = PluginsManager::getInstance()->getAllPluginsSlugs();

                // EXCLUDE DUPLICATOR PRO
                if (($key = array_search(PluginsManager::SLUG_DUPLICATOR_PRO, $forceDisable)) !== false) {
                    unset($forceDisable[$key]);
                }

                $paramsManager->setValue(PrmMng::PARAM_FORCE_DIABLE_PLUGINS, $forceDisable);
            }
        }

        // reload state after new path and new url
        DUPX_InstallerState::getInstance()->checkState(false, false);
        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     *
     * @return boolean
     */
    public static function setParamsAfterValidation()
    {
        Log::info("\nCTRL PARAMS AFTER VALIDATION");
        $paramsManager = PrmMng::getInstance();

        $paramsManager->setValue(PrmMng::PARAM_WP_ADDON_SITES_PATHS, DUPX_Validation_test_addon_sites::getAddonsListsFolders());
        ParamDescDatabase::updateCharsetAndCollateByDatabaseSettings();

        $configsChecks = DUPX_Validation_test_iswritable_configs::configsWritableChecks();

        if ($configsChecks['wpconfig'] === false) {
            Log::info("WP-CONFIG ISN\'T READABLE SO SET noting ON " . PrmMng::PARAM_WP_CONFIG . ' PARAM');
            $paramsManager->setValue(PrmMng::PARAM_WP_CONFIG, 'nothing');
            $paramsManager->setFormStatus(PrmMng::PARAM_WP_CONFIG, ParamForm::STATUS_INFO_ONLY);

            Log::info('SET AND DISABLE ALL DB PARAMS');
            $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
            $paramsManager->setValue(PrmMng::PARAM_DB_HOST, $overwriteData['dbhost']);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_HOST, ParamForm::STATUS_INFO_ONLY);
            $paramsManager->setValue(PrmMng::PARAM_DB_NAME, $overwriteData['dbname']);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_NAME, ParamForm::STATUS_INFO_ONLY);
            $paramsManager->setValue(PrmMng::PARAM_DB_USER, $overwriteData['dbuser']);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_USER, ParamForm::STATUS_INFO_ONLY);
            $paramsManager->setValue(PrmMng::PARAM_DB_PASS, $overwriteData['dbpass']);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_PASS, ParamForm::STATUS_INFO_ONLY);
            $paramsManager->setValue(PrmMng::PARAM_DB_TABLE_PREFIX, $overwriteData['table_prefix']);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_TABLE_PREFIX, ParamForm::STATUS_INFO_ONLY);
        }

        if ($configsChecks['htaccess'] === false) {
            Log::info("HTACCESS ISN\'T READABLE SO SET noting ON " . PrmMng::PARAM_HTACCESS_CONFIG . ' PARAM');
            $paramsManager->setValue(PrmMng::PARAM_HTACCESS_CONFIG, 'nothing');
            $paramsManager->setFormStatus(PrmMng::PARAM_HTACCESS_CONFIG, ParamForm::STATUS_INFO_ONLY);
        }

        if ($configsChecks['other'] === false) {
            Log::info("OTHER CONFIGS ISN\'T READABLE SO SET noting ON " . PrmMng::PARAM_OTHER_CONFIG . ' PARAM');
            $paramsManager->setValue(PrmMng::PARAM_OTHER_CONFIG, 'nothing');
            $paramsManager->setFormStatus(PrmMng::PARAM_OTHER_CONFIG, ParamForm::STATUS_INFO_ONLY);
        }

        $paramsManager->save();

        return self::$paramsValidated;
    }

    /**
     * update blog name if subsite id is changed
     *
     * @param int $oldSubsiteId
     *
     * @return void
     */
    protected static function updateBlogname($oldSubsiteId)
    {
        $paramsManager  = PrmMng::getInstance();
        $archive_config = DUPX_ArchiveConfig::getInstance();

        if ($paramsManager->getInitStatus(PrmMng::PARAM_SUBSITE_ID) === ParamForm::STATUS_OVERWRITE) {
            return;
        }

        if ($oldSubsiteId == $paramsManager->getValue(PrmMng::PARAM_SUBSITE_ID)) {
            return;
        }

        $blogName = $archive_config->getBlognameFromSelectedSubsiteId();

        // If provided use name set by user
        if ($paramsManager->getValue(PrmMng::PARAM_BLOGNAME) !== $archive_config->blogname) {
            $blogName = $paramsManager->getValue(PrmMng::PARAM_BLOGNAME);
        }

        $paramsManager->setValue(PrmMng::PARAM_BLOGNAME, $blogName);
    }

    /**
     *
     * @return bool
     */
    protected static function setParamsDatabase()
    {
        $paramsManager = PrmMng::getInstance();

        $paramsManager->setValueFromInput(PrmMng::PARAM_DB_VIEW_MODE, ParamForm::INPUT_POST);

        switch ($paramsManager->getValue(PrmMng::PARAM_DB_VIEW_MODE)) {
            case 'basic':
                $readParamsList = array(
                    PrmMng::PARAM_DB_ACTION,
                    PrmMng::PARAM_DB_HOST,
                    PrmMng::PARAM_DB_NAME,
                    PrmMng::PARAM_DB_USER,
                    PrmMng::PARAM_DB_PASS
                );
                foreach ($readParamsList as $cParam) {
                    if ($paramsManager->setValueFromInput($cParam, ParamForm::INPUT_POST, false, true) === false) {
                        self::$paramsValidated = false;
                    }
                }
                break;
            case 'cpnl':
                $readParamsList = array(
                    PrmMng::PARAM_CPNL_HOST,
                    PrmMng::PARAM_CPNL_USER,
                    PrmMng::PARAM_CPNL_PASS,
                    PrmMng::PARAM_CPNL_DB_USER_CHK,
                    PrmMng::PARAM_CPNL_PREFIX,
                    PrmMng::PARAM_CPNL_DB_ACTION,
                    PrmMng::PARAM_CPNL_DB_HOST,
                    PrmMng::PARAM_CPNL_DB_NAME_SEL,
                    PrmMng::PARAM_CPNL_DB_NAME_TXT,
                    PrmMng::PARAM_CPNL_DB_USER_SEL,
                    PrmMng::PARAM_CPNL_DB_USER_TXT,
                    PrmMng::PARAM_CPNL_DB_PASS,
                    PrmMng::PARAM_CPNL_IGNORE_PREFIX
                );
                foreach ($readParamsList as $cParam) {
                    if ($paramsManager->setValueFromInput($cParam, ParamForm::INPUT_POST, false, true) === false) {
                        self::$paramsValidated = false;
                    }
                }

                // NORMALIZE VALUES FOR DB TEST
                if ($paramsManager->setValue(PrmMng::PARAM_DB_ACTION, $paramsManager->getValue(PrmMng::PARAM_CPNL_DB_ACTION)) === false) {
                    self::$paramsValidated = false;
                }
                // DBHOST
                if ($paramsManager->setValue(PrmMng::PARAM_DB_HOST, $paramsManager->getValue(PrmMng::PARAM_CPNL_DB_HOST)) === false) {
                    self::$paramsValidated = false;
                }

                $cpnlPrefix   = $paramsManager->getValue(PrmMng::PARAM_CPNL_PREFIX);
                $ignorePrefix = $paramsManager->getValue(PrmMng::PARAM_CPNL_IGNORE_PREFIX);

                // DBNAME
                if ($paramsManager->getValue(PrmMng::PARAM_CPNL_DB_ACTION) === 'create') {
                    // CREATE NEW DATABASE
                    $dbName = $paramsManager->getValue(PrmMng::PARAM_CPNL_DB_NAME_TXT);
                } else {
                    // GET EXISTS DATABASE
                    $dbName = $paramsManager->getValue(PrmMng::PARAM_CPNL_DB_NAME_SEL);
                }

                if ($ignorePrefix === false && strpos($dbName, $cpnlPrefix) !== 0) {
                    $dbName = $cpnlPrefix . $dbName;
                }
                if ($paramsManager->setValue(PrmMng::PARAM_DB_NAME, $dbName) === false) {
                    self::$paramsValidated = false;
                }

                // DB USER
                if ($paramsManager->getValue(PrmMng::PARAM_CPNL_DB_USER_CHK)) {
                    // CREATE NEW USER
                    $dbUser = $paramsManager->getValue(PrmMng::PARAM_CPNL_DB_USER_TXT);
                } else {
                    // GET EXIST USER
                    $dbUser = $paramsManager->getValue(PrmMng::PARAM_CPNL_DB_USER_SEL);
                }
                if ($ignorePrefix === false && strpos($dbUser, $cpnlPrefix) !== 0) {
                    $dbUser = $cpnlPrefix . $dbUser;
                }
                if ($paramsManager->setValue(PrmMng::PARAM_DB_USER, $dbUser) === false) {
                    self::$paramsValidated = false;
                }

                //DBPASS
                if ($paramsManager->setValue(PrmMng::PARAM_DB_PASS, $paramsManager->getValue(PrmMng::PARAM_CPNL_DB_PASS)) === false) {
                    self::$paramsValidated = false;
                }
                break;
        }

        $readParamsList = array(
            PrmMng::PARAM_DB_TABLE_PREFIX,
            PrmMng::PARAM_DB_VIEW_CREATION,
            PrmMng::PARAM_DB_PROC_CREATION,
            PrmMng::PARAM_DB_FUNC_CREATION,
            PrmMng::PARAM_DB_REMOVE_DEFINER,
            PrmMng::PARAM_DB_SPLIT_CREATES,
            PrmMng::PARAM_DB_MYSQL_MODE,
            PrmMng::PARAM_DB_MYSQL_MODE_OPTS
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, ParamForm::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        if ($paramsManager->setValue(PrmMng::PARAM_DB_TABLES, DUPX_DB_Tables::getInstance()->getDefaultParamValue()) === false) {
            self::$paramsValidated = false;
        }

        return self::$paramsValidated;
    }

    /**
     * resets the original values in case of D&G import
     *
     * @return void
     */
    protected static function resetUrlAndPathsFromOverwriteData()
    {
        if (!DUPX_InstallerState::isImportFromBackendMode()) {
            return;
        }

        $paramsManager = PrmMng::getInstance();
        $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

        if (!isset($overwriteData['urls']['home']) || !isset($overwriteData['paths']['home'])) {
            return;
        }

        $paramsManager->setValue(PrmMng::PARAM_URL_NEW, $overwriteData['urls']['home']);
        $paramsManager->setValue(PrmMng::PARAM_PATH_NEW, $overwriteData['paths']['home']);

        $paramsManager->setValue(PrmMng::PARAM_SITE_URL, $overwriteData['urls']['abs']);
        $paramsManager->setValue(PrmMng::PARAM_PATH_WP_CORE_NEW, $overwriteData['paths']['abs']);

        $paramsManager->setValue(PrmMng::PARAM_URL_UPLOADS_NEW, $overwriteData['urls']['uploads']);
        $paramsManager->setValue(PrmMng::PARAM_PATH_UPLOADS_NEW, $overwriteData['paths']['uploads']);
    }

    /**
     *
     * @return void
     */
    public static function setParamsOnAddSiteOnMultisite()
    {
        if (!DUPX_InstallerState::isAddSiteOnMultisite()) {
            return;
        }

        $paramsManager = PrmMng::getInstance();
        $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

        if (empty($overwriteData['adminUsers'])) {
            throw new Exception('Empty admin users');
        }

        $paramsManager->setValue(PrmMng::PARAM_DB_ACTION, DUPX_DBInstall::DBACTION_REMOVE_ONLY_TABLES);
        $paramsManager->setValue(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES, DUP_PRO_Extraction::FILTER_ONLY_MEDIA_PLUG_THEMES);
        $paramsManager->setValue(PrmMng::PARAM_ARCHIVE_ACTION, DUP_PRO_Extraction::ACTION_REMOVE_UPLOADS);
        $paramsManager->setValue(PrmMng::PARAM_WP_CONFIG, 'nothing');
        $paramsManager->setValue(PrmMng::PARAM_HTACCESS_CONFIG, 'nothing');
        $paramsManager->setValue(PrmMng::PARAM_OTHER_CONFIG, 'nothing');
        $paramsManager->setValue(PrmMng::PARAM_DB_TABLE_PREFIX, $overwriteData['table_prefix']);
        $paramsManager->setValue(PrmMng::PARAM_EMPTY_SCHEDULE_STORAGE, false);

        $paramsManager->setValue(PrmMng::PARAM_URL_UPLOADS_NEW, $overwriteData['urls']['uploads']);
        $paramsManager->setValue(PrmMng::PARAM_PATH_UPLOADS_NEW, $overwriteData['paths']['uploads']);

        $paramsManager->setValue(PrmMng::PARAM_SITE_URL, $overwriteData['urls']['abs']);
        $paramsManager->setValue(PrmMng::PARAM_PATH_WP_CORE_NEW, $overwriteData['paths']['abs']);

        $paramsManager->setValue(PrmMng::PARAM_URL_CONTENT_NEW, $overwriteData['urls']['wpcontent']);
        $paramsManager->setValue(PrmMng::PARAM_PATH_CONTENT_NEW, $overwriteData['paths']['wpcontent']);

        $paramsManager->setValue(PrmMng::PARAM_URL_PLUGINS_NEW, $overwriteData['urls']['plugins']);
        $paramsManager->setValue(PrmMng::PARAM_PATH_PLUGINS_NEW, $overwriteData['paths']['plugins']);

        $paramsManager->setValue(PrmMng::PARAM_URL_MUPLUGINS_NEW, $overwriteData['urls']['muplugins']);
        $paramsManager->setValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW, $overwriteData['paths']['muplugins']);

        // Make sure to update path extraction mapping
        DUPX_ArchiveConfig::getInstance()->getPathsMapping(true);
    }

    /**
     *
     * @return boolean
     */
    public static function setParamsStep2()
    {
        Log::info('CTRL PARAMS S2', Log::LV_DETAILED);
        Log::info('REQUEST: ' . Log::v2str($_REQUEST), Log::LV_HARD_DEBUG);
        $paramsManager = PrmMng::getInstance();

        $readParamsList = array(
            PrmMng::PARAM_DB_CHARSET,
            PrmMng::PARAM_DB_COLLATE,
            PrmMng::PARAM_DB_TABLES
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, ParamForm::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     *
     * @return boolean
     */
    public static function setParamsStep3()
    {
        Log::info('CTRL PARAMS S3', Log::LV_DETAILED);
        Log::info('REQUEST: ' . Log::v2str($_REQUEST), Log::LV_HARD_DEBUG);

        $paramsManager = PrmMng::getInstance();

        $readParamsList = array(
            PrmMng::PARAM_EMPTY_SCHEDULE_STORAGE,
            PrmMng::PARAM_EMAIL_REPLACE,
            PrmMng::PARAM_FULL_SEARCH,
            PrmMng::PARAM_SKIP_PATH_REPLACE,
            PrmMng::PARAM_POSTGUID,
            PrmMng::PARAM_MAX_SERIALIZE_CHECK,
            PrmMng::PARAM_MULTISITE_CROSS_SEARCH,
            PrmMng::PARAM_PLUGINS,
            PrmMng::PARAM_CUSTOM_SEARCH,
            PrmMng::PARAM_CUSTOM_REPLACE,
            PrmMng::PARAM_WP_CONF_DISALLOW_FILE_EDIT,
            PrmMng::PARAM_WP_CONF_DISALLOW_FILE_MODS,
            PrmMng::PARAM_WP_CONF_AUTOSAVE_INTERVAL,
            PrmMng::PARAM_WP_CONF_WP_POST_REVISIONS,
            PrmMng::PARAM_WP_CONF_FORCE_SSL_ADMIN,
            PrmMng::PARAM_WP_CONF_IMAGE_EDIT_OVERWRITE,
            PrmMng::PARAM_GEN_WP_AUTH_KEY,
            PrmMng::PARAM_WP_CONF_AUTOMATIC_UPDATER_DISABLED,
            PrmMng::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE,
            PrmMng::PARAM_WP_CONF_WP_CACHE,
            PrmMng::PARAM_WP_CONF_WPCACHEHOME,
            PrmMng::PARAM_WP_CONF_WP_DEBUG,
            PrmMng::PARAM_WP_CONF_WP_DEBUG_LOG,
            PrmMng::PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER,
            PrmMng::PARAM_WP_CONF_WP_DEBUG_DISPLAY,
            PrmMng::PARAM_WP_CONF_SCRIPT_DEBUG,
            PrmMng::PARAM_WP_CONF_CONCATENATE_SCRIPTS,
            PrmMng::PARAM_WP_CONF_SAVEQUERIES,
            PrmMng::PARAM_WP_CONF_ALTERNATE_WP_CRON,
            PrmMng::PARAM_WP_CONF_DISABLE_WP_CRON,
            PrmMng::PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT,
            PrmMng::PARAM_WP_CONF_EMPTY_TRASH_DAYS,
            PrmMng::PARAM_WP_CONF_COOKIE_DOMAIN,
            PrmMng::PARAM_WP_CONF_WP_MEMORY_LIMIT,
            PrmMng::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT,
            PrmMng::PARAM_WP_CONF_WP_TEMP_DIR,
            PrmMng::PARAM_WP_CONF_MYSQL_CLIENT_FLAGS,
            PrmMng::PARAM_USERS_PWD_RESET,
            PrmMng::PARAM_WP_ADMIN_CREATE_NEW
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, ParamForm::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        if ($paramsManager->getValue(PrmMng::PARAM_WP_ADMIN_CREATE_NEW)) {
            $readParamsList = array(
                PrmMng::PARAM_WP_ADMIN_NAME,
                PrmMng::PARAM_WP_ADMIN_PASSWORD,
                PrmMng::PARAM_WP_ADMIN_MAIL,
                PrmMng::PARAM_WP_ADMIN_NICKNAME,
                PrmMng::PARAM_WP_ADMIN_FIRST_NAME,
                PrmMng::PARAM_WP_ADMIN_LAST_NAME
            );

            foreach ($readParamsList as $cParam) {
                if ($paramsManager->setValueFromInput($cParam, ParamForm::INPUT_POST, false, true) === false) {
                    self::$paramsValidated = false;
                }
            }

            if (DUPX_DB_Functions::getInstance()->checkIfUserNameExists($paramsManager->getValue(PrmMng::PARAM_WP_ADMIN_NAME))) {
                self::$paramsValidated = false;
                DUPX_NOTICE_MANAGER::getInstance()->addNextStepNotice(array(
                    'shortMsg'    => 'The user ' . $paramsManager->getValue(PrmMng::PARAM_WP_ADMIN_NAME) . ' can\'t be created, already exists',
                    'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                    'longMsg'     => 'Please insert another new user login name',
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML
                ));
            }
        }

        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     *
     * @return boolean
     */
    public static function setParamAutoClean()
    {
        $paramsManager = PrmMng::getInstance();
        if ($paramsManager->setValueFromInput(PrmMng::PARAM_AUTO_CLEAN_INSTALLER_FILES, ParamForm::INPUT_POST, false, true) === false) {
            self::$paramsValidated = false;
        }
        $paramsManager->save();
        return self::$paramsValidated;
    }
}
