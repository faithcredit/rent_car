<?php

namespace Duplicator\Installer\Core\Deploy;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use DUPX_DB;
use DUPX_DB_Functions;
use DUPX_InstallerState;
use Exception;
use mysqli;
use stdClass;

class Helpers
{
    /**
     * Load WordPress dependencies
     *
     * @return bool $loaded
     *
     * @throws Exception
     */
    public static function loadWP()
    {
        static $loaded = null;
        if (is_null($loaded)) {
            $wpRootDir = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
            require_once($wpRootDir . '/wp-load.php');
            if (!class_exists('WP_Privacy_Policy_Content')) {
                require_once($wpRootDir . '/wp-admin/includes/misc.php');
            }
            if (!function_exists('request_filesystem_credentials')) {
                require_once($wpRootDir . '/wp-admin/includes/file.php');
            }
            if (!function_exists('get_plugins')) {
                require_once $wpRootDir . '/wp-admin/includes/plugin.php';
            }
            if (!function_exists('delete_theme')) {
                require_once $wpRootDir . '/wp-admin/includes/theme.php';
            }
            $GLOBALS['wpdb']->show_errors(false);
            $loaded = true;
        }
        return $loaded;
    }

    /**
     * Check if Theme is enabled
     *
     * @param stdClass $theme Theme object
     *
     * @return boolean
     *
     * @throws Exception
     */
    public static function isThemeEnable($theme)
    {
        switch (DUPX_InstallerState::getInstType()) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_RECOVERY_SINGLE_SITE:
                if ($theme->isActive) {
                    return true;
                }
                break;
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBFOLDER:
                if (count($theme->isActive) > 0) {
                    return true;
                }
                break;
            case DUPX_InstallerState::INSTALL_STANDALONE:
                if (in_array(PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_ID), $theme->isActive)) {
                    return true;
                }
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER:
                return true;
            case DUPX_InstallerState::INSTALL_NOT_SET:
            default:
                throw new Exception('Invalid installer type');
        }

        return false;
    }

    /**
     * Check if a parent theme has a child theme enabled
     *
     * @param stdClass   $parentTheme Parent Theme Object
     * @param stdClass[] $themes      Themes List
     *
     * @return boolean
     * @throws Exception
     */
    public static function haveChildEnable($parentTheme, &$themes)
    {
        foreach ($themes as $theme) {
            if ($theme->parentTheme === $parentTheme->slug) {
                if (Helpers::isThemeEnable($theme)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mysqli $dbh Database connection
     *
     * @return int[]
     *
     * @throws Exception
     */
    public static function getSuperAdminsUserIds($dbh)
    {
        $result = array();

        if (DUPX_InstallerState::isNewSiteIsMultisite()) {
            $paramsManager   = PrmMng::getInstance();
            $basePrefix      = $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);
            $usersTableName  = "{$basePrefix}users";
            $superAdminsList = self::getSuperAdminUsernames($dbh, $basePrefix);

            if (!empty($superAdminsList)) {
                $sql                = "SELECT ID FROM {$usersTableName} 
                                   WHERE user_login IN ('" . implode("','", $superAdminsList) . "')";
                $superAdminsResults = DUPX_DB::queryToArray($dbh, $sql);
                foreach ($superAdminsResults as $superAdminsResult) {
                    $result[] = $superAdminsResult[0];
                }
            }
        }

        return $result;
    }

    /**
     * Get Super Admin Users names
     *
     * @param mysqli $dbh        Database connection
     * @param string $basePrefix WordPress Tables Prefix
     *
     * @return string[]
     *
     * @throws Exception
     */
    public static function getSuperAdminUsernames($dbh, $basePrefix)
    {
        $result            = array();
        $siteMetaTableName = "{$basePrefix}sitemeta";

        if (DUPX_InstallerState::isNewSiteIsMultisite() && DUPX_DB_Functions::getInstance()->tablesExist($siteMetaTableName)) {
            $sql                = "SELECT meta_value FROM {$siteMetaTableName} WHERE meta_key = 'site_admins'";
            $superAdminsResults = DUPX_DB::queryToArray($dbh, $sql);

            if (isset($superAdminsResults[0][0])) {
                $result = unserialize($superAdminsResults[0][0]);
                Log::info('SUPER ADMIN USERS: ' . print_r($result, true));
            }
        }

        return $result;
    }
}
