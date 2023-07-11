<?php

namespace Duplicator\Installer\Core\Deploy;

use Duplicator\Installer\Core\Deploy\Plugins\PluginsManager;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use DUPX_ArchiveConfig;
use DUPX_DB;
use DUPX_DB_Functions;
use DUPX_NOTICE_ITEM;
use DUPX_NOTICE_MANAGER;
use Exception;
use mysqli;

class CleanUp
{
    /**
     * Remove users without any permissions
     *
     * @param string $subSiteId ID of the Sub Site
     * @param mysqli $dbh       Database Connection
     *
     * @return void
     */
    public static function removeUsersWithoutPermissions($subSiteId, $dbh)
    {
        Log::info("\n--------------------\n" .
            "REMOVING USERS WITHOUT PERMISSIONS");

        $paramsManager = PrmMng::getInstance();
        $basePrefix    = $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);

        $usersTableName     = DUPX_DB_Functions::getUserTableName();
        $userMetaTableName  = DUPX_DB_Functions::getUserMetaTableName();
        $superAdminUsersIds = Helpers::getSuperAdminsUserIds($dbh);
        $superAdminUsersStr = implode(',', $superAdminUsersIds);

        $excludeSuperAdminsClause = (!empty($superAdminUsersIds))
            ? " AND {$usersTableName}.id NOT IN ({$superAdminUsersStr})"
            : '';

        $usersWithCapabilitiesSql = "SELECT {$userMetaTableName}.user_id FROM {$userMetaTableName}
                                     WHERE {$userMetaTableName}.user_id = {$usersTableName}.id 
                                       AND({$userMetaTableName}.meta_key = '{$basePrefix}capabilities'
                                       OR {$userMetaTableName}.meta_key REGEXP '{$basePrefix}[0-9]+_capabilities')";

        $sql = "SELECT {$usersTableName}.ID FROM {$usersTableName} 
                WHERE NOT EXISTS ({$usersWithCapabilitiesSql})" . $excludeSuperAdminsClause;

        $results     = DUPX_DB::queryToArray($dbh, $sql);
        $removeUsers = array();
        foreach ($results as list($userId)) {
            $removeUsers[] = $userId;
        }
        $removeUsers    = array_unique($removeUsers);
        $removeUsersStr = '(' . implode(',', $removeUsers) . ')';

        $hasUsersToRemove = count($removeUsers) > 0;
        if ($hasUsersToRemove) {
            Log::info("REMOVE USER IDS: " . Log::v2str($removeUsers));
            DUPX_DB::chunksDelete($dbh, $usersTableName, "id IN " . $removeUsersStr);
            DUPX_DB::chunksDelete($dbh, $userMetaTableName, "user_id IN " . $removeUsersStr);
        }
    }

    /**
     * Remove all deactivated plugins
     *
     * @return void
     *
     * @throws Exception
     */
    public static function removeUnusedPlugins()
    {
        Log::info("\n--------------------\n" .
            "DELETING INACTIVE PLUGINS");

        PluginsManager::getInstance()->uninstallInactivePlugins();
    }

    /**
     * Remove all deactivated plugins
     *
     * @return void
     */
    public static function removeUnusedThemes()
    {
        Log::info("\n--------------------\n" .
            "DELETING INACTIVE THEMES");

        Helpers::loadWP();

        $themes = DUPX_ArchiveConfig::getInstance()->wpInfo->themes;

        foreach ($themes as $theme) {
            //Log::info('THEME: '.Log::v2str($theme));

            if (Helpers::isThemeEnable($theme)) {
                Log::info('THEME: ' . Log::v2str($theme->slug) . ' ENABLE');
                continue;
            }
            if (Helpers::haveChildEnable($theme, $themes)) {
                Log::info('THEME: ' . Log::v2str($theme->slug) . ' CHILD ENABLE');
                continue;
            }
            if (delete_theme($theme->stylesheet, '')) {
                Log::info('THEME: ' . Log::v2str($theme->slug) . ' DELETED');
            } else {
                $nManager = DUPX_NOTICE_MANAGER::getInstance();
                $errorMsg = "**ERROR** The Inactive theme " . $theme->slug . " deletion failed";
                Log::info($errorMsg);

                $fullPath = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/themes/' . $theme->stylesheet;

                $nManager->addFinalReportNotice(array(
                    'shortMsg' => $errorMsg,
                    'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                    'longMsg' => 'Please delete the path ' . $fullPath . ' manually',
                    'sections' => 'general'
                ));
            }
        }
    }
}
