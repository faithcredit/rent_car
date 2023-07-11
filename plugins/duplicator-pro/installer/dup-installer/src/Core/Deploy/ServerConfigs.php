<?php

/**
 * @package   Duplicator/Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace  Duplicator\Installer\Core\Deploy;

use DUP_PRO_Extraction;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\InstallerOrigFileMng;
use Duplicator\Libs\Snap\SnapIO;
use DUPX_ArchiveConfig;
use DUPX_DB;
use DUPX_DB_Functions;
use DUPX_InstallerState;
use DUPX_NOTICE_ITEM;
use DUPX_NOTICE_MANAGER;
use DUPX_Package;
use DUPX_U;
use DUPX_WPConfig;
use Error;
use Exception;
use mysqli;

class ServerConfigs
{
    const INSTALLER_HOST_ENTITY_PREFIX                 = 'installer_host_';
    const CONFIG_ORIG_FILE_USERINI_ID                  = 'userini';
    const CONFIG_ORIG_FILE_HTACCESS_ID                 = 'htaccess';
    const CONFIG_ORIG_FILE_WPCONFIG_ID                 = 'wpconfig';
    const CONFIG_ORIG_FILE_PHPINI_ID                   = 'phpini';
    const CONFIG_ORIG_FILE_WEBCONFIG_ID                = 'webconfig';
    const CONFIG_ORIG_FILE_USERINI_ID_OVERWRITE_SITE   = 'installer_host_userini';
    const CONFIG_ORIG_FILE_HTACCESS_ID_OVERWRITE_SITE  = 'installer_host_htaccess';
    const CONFIG_ORIG_FILE_WPCONFIG_ID_OVERWRITE_SITE  = 'installer_host_wpconfig';
    const CONFIG_ORIG_FILE_PHPINI_ID_OVERWRITE_SITE    = 'installer_host_phpini';
    const CONFIG_ORIG_FILE_WEBCONFIG_ID_OVERWRITE_SITE = 'installer_host_webconfig';

    const ACTION_WPCONF_MODIFY  = 'modify';
    const ACTION_WPCONF_NEW     = 'new';
    const ACTION_WPCONF_NOTHING = 'nothing';

    /**
     * Common timestamp of all members of this class
     *
     * @return string|false
     */
    public static function getFixedTimestamp()
    {
        static $time = null;

        if (is_null($time)) {
            $time = date("ymdHis");
        }

        return $time;
    }

    /**
     * Creates a copy of the original server config file and resets the original to blank
     *
     * @param string $rootPath The root path to the location of the server config files
     *
     * @return void
     */
    public static function reset($rootPath)
    {
        $rootPath      = SnapIO::trailingslashit($rootPath);
        $paramsManager = PrmMng::getInstance();

        Log::info("\n*** RESET CONFIG FILES IN CURRENT HOSTING");

        switch ($paramsManager->getValue(PrmMng::PARAM_WP_CONFIG)) {
            case self::ACTION_WPCONF_NEW:
            case self::ACTION_WPCONF_MODIFY:
                if (DUPX_InstallerState::isBridgeInstall()) {
                    // if bridge wp-config must be mantained
                    break;
                }
                if (self::runReset($rootPath . 'wp-config.php', self::CONFIG_ORIG_FILE_WPCONFIG_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_WP_CONFIG, 'nothing');
                }
                break;
            case self::ACTION_WPCONF_NOTHING:
                break;
        }

        switch ($paramsManager->getValue(PrmMng::PARAM_HTACCESS_CONFIG)) {
            case 'new':
            case 'original':
                if (self::runReset($rootPath . '.htaccess', self::CONFIG_ORIG_FILE_HTACCESS_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_HTACCESS_CONFIG, 'nothing');
                }
                break;
            case 'nothing':
                break;
        }

        switch ($paramsManager->getValue(PrmMng::PARAM_OTHER_CONFIG)) {
            case 'new':
            case 'original':
                if (self::runReset($rootPath . 'web.config', self::CONFIG_ORIG_FILE_WEBCONFIG_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_OTHER_CONFIG, 'nothing');
                }
                if (self::runReset($rootPath . '.user.ini', self::CONFIG_ORIG_FILE_USERINI_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_OTHER_CONFIG, 'nothing');
                }
                if (self::runReset($rootPath . 'php.ini', self::CONFIG_ORIG_FILE_PHPINI_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_OTHER_CONFIG, 'nothing');
                }
                break;
            case 'nothing':
                break;
        }

        $paramsManager->save();
        Log::info("*** RESET CONFIG FILES END");
    }

    /**
     * Set config files in target root folder
     *
     * @param string $rootPath target root path
     *
     * @return void
     */
    public static function setFiles($rootPath)
    {
        $paramsManager = PrmMng::getInstance();
        $origFiles     = InstallerOrigFileMng::getInstance();
        Log::info("SET CONFIG FILES");

        $entryKey = self::CONFIG_ORIG_FILE_WPCONFIG_ID;
        switch ($paramsManager->getValue(PrmMng::PARAM_WP_CONFIG)) {
            case 'new':
                if (SnapIO::copy(DUPX_Package::getWpconfigSamplePath(), DUPX_WPConfig::getWpConfigPath()) === false) {
                    DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                        'shortMsg'    => 'Can\'t reset wp-config.php to wp-config-sample.php',
                        'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                        'longMsg'     => 'Target file entry ' . Log::v2str(DUPX_WPConfig::getWpConfigPath()),
                        'sections'    => 'general'
                    ));
                } else {
                    Log::info("Copy wp-config-sample.php to target:" . DUPX_WPConfig::getWpConfigPath());
                }
                break;
            case 'modify':
                if (SnapIO::copy($origFiles->getEntryStoredPath($entryKey), DUPX_WPConfig::getWpConfigPath()) === false) {
                    DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                        'shortMsg'    => 'Unable to restore the original ' . $entryKey . '.php entries. Please check the file permission on this server.',
                        'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                        'longMsg'     => 'Target file entry ' . Log::v2str(DUPX_WPConfig::getWpConfigPath()),
                        'sections'    => 'general'
                    ));
                } else {
                    Log::info("Retained original entry " . $entryKey . " target:" . DUPX_WPConfig::getWpConfigPath());
                }
                break;
            case 'nothing':
                break;
        }

        $entryKey = self::CONFIG_ORIG_FILE_HTACCESS_ID;
        switch ($paramsManager->getValue(PrmMng::PARAM_HTACCESS_CONFIG)) {
            case 'new':
                $targetHtaccess = self::getHtaccessTargetPath();
                if (SnapIO::touch($targetHtaccess) === false) {
                    DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                        'shortMsg'    => 'Can\'t create new .htaccess file',
                        'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                        'longMsg'     => 'Target file entry ' . $targetHtaccess,
                        'sections'    => 'general'
                    ));
                } else {
                    Log::info("New .htaccess file created:" . $targetHtaccess);
                }
                break;
            case 'original':
                if (($storedHtaccess = $origFiles->getEntryStoredPath($entryKey)) === false) {
                    Log::info("Retained original entry. .htaccess doesn\'t exist in original site");
                    break;
                }

                $targetHtaccess = self::getHtaccessTargetPath();
                if (SnapIO::copy($storedHtaccess, $targetHtaccess) === false) {
                    DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                        'shortMsg'    => 'Unable to restore the original ' . $entryKey . ' entries. Please check the file permission on this server.',
                        'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                        'longMsg'     => 'Target file entry ' . Log::v2str($targetHtaccess),
                        'sections'    => 'general'
                    ));
                } else {
                    Log::info("Retained original entry " . $entryKey . " target:" . $targetHtaccess);
                }
                break;
            case 'nothing':
                break;
        }

        switch ($paramsManager->getValue(PrmMng::PARAM_OTHER_CONFIG)) {
            case 'new':
                if ($origFiles->getEntry(self::CONFIG_ORIG_FILE_WEBCONFIG_ID_OVERWRITE_SITE)) {
                    //IIS: This is reset because on some instances of IIS having old values cause issues
                    //Recommended fix for users who want it because errors are triggered is to have
                    //them check the box for ignoring the web.config files on step 1 of installer
                    $xml_contents  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
                    $xml_contents .= "<!-- Reset by Duplicator Installer.  Original can be found in the original_files_ folder-->\n";
                    $xml_contents .= "<configuration></configuration>\n";
                    if (file_put_contents($rootPath . "/web.config", $xml_contents) === false) {
                        Log::info('RESET: can\'t create a new empty web.config');
                    }
                }
                break;
            case 'original':
                $entries = array(
                    self::CONFIG_ORIG_FILE_USERINI_ID,
                    self::CONFIG_ORIG_FILE_WEBCONFIG_ID,
                    self::CONFIG_ORIG_FILE_PHPINI_ID
                );
                foreach ($entries as $entryKey) {
                    if ($origFiles->getEntry($entryKey) !== false) {
                        if (SnapIO::copy($origFiles->getEntryStoredPath($entryKey), $origFiles->getEntryTargetPath($entryKey)) === false) {
                            DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                                'shortMsg'    => 'Unable to restore the original ' . $entryKey . ' entries. Please check the file permission on this server.',
                                'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                                'longMsg'     => 'Target file entry ' . Log::v2str($origFiles->getEntryTargetPath($entryKey)),
                                'sections'    => 'general'
                            ));
                        } else {
                            Log::info("Retained original entry " . $entryKey . " target:" . $origFiles->getEntryTargetPath($entryKey));
                        }
                    }
                }
                break;
            case 'nothing':
                break;
        }

        DUPX_NOTICE_MANAGER::getInstance()->saveNotices();
    }

    /**
     * Get htaccess target path
     *
     * @return string
     */
    public static function getHtaccessTargetPath()
    {
        if (($targetEnty = InstallerOrigFileMng::getInstance()->getEntryTargetPath(self::CONFIG_ORIG_FILE_HTACCESS_ID)) !== false) {
            return $targetEnty;
        } else {
            return PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . '/.htaccess';
        }
    }

    /**
     * Creates a copy of the original server config file and resets the original to blank per file
     *
     * @param string $filePath   file path to store
     * @param string $storedName if not false rename
     *
     * @return bool Returns true if the file was backed-up and reset or there was no file to reset
     */
    private static function runReset($filePath, $storedName)
    {
        $fileName = basename($filePath);

        try {
            if (file_exists($filePath)) {
                if (!SnapIO::chmod($filePath, 'u+rw') || !is_readable($filePath) || !is_writable($filePath)) {
                    throw new Exception("RESET CONFIG FILES: permissions error on file config path " . $filePath);
                }

                $origFiles = InstallerOrigFileMng::getInstance();
                $filePath  = SnapIO::safePathUntrailingslashit($filePath);

                Log::info("RESET CONFIG FILES: I'M GOING TO MOVE CONFIG FILE " . Log::v2str($fileName) . " IN ORIGINAL FOLDER");

                if (
                    $origFiles->addEntry(
                        self::INSTALLER_HOST_ENTITY_PREFIX . $storedName,
                        $filePath,
                        InstallerOrigFileMng::MODE_MOVE,
                        self::INSTALLER_HOST_ENTITY_PREFIX . $storedName
                    )
                ) {
                    Log::info("\tCONFIG FILE HAS BEEN RESET");
                } else {
                    throw new Exception("can\'t stored file " . Log::v2str($fileName) . " in orginal file folder");
                }
            } else {
                Log::info("RESET CONFIG FILES: " . Log::v2str($fileName) . " does not exist, no need for rest", Log::LV_DETAILED);
            }
        } catch (Exception $e) {
            Log::logException($e, Log::LV_DEFAULT, 'RESET CONFIG FILES ERROR: ');
            DUPX_NOTICE_MANAGER::getInstance()->addBothNextAndFinalReportNotice(array(
                'shortMsg'    => 'Can\'t reset config file ' . Log::v2str($fileName) . ' so it will not be modified.',
                'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'longMsg'     => 'Message: ' . $e->getMessage(),
                'sections'    => 'general'
            ));
            return false;
        } catch (Error $e) {
            Log::logException($e, Log::LV_DEFAULT, 'RESET CONFIG FILES ERROR: ');
            DUPX_NOTICE_MANAGER::getInstance()->addBothNextAndFinalReportNotice(array(
                'shortMsg'    => 'Can\'t reset config file ' . Log::v2str($fileName) . ' so it will not be modified.',
                'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'longMsg'     => 'Message: ' . $e->getMessage(),
                'sections'    => 'general'
            ));
            return false;
        }

        return true;
    }

    /**
     * Return wp-config path stored in orig folder of target site
     *
     * @return false|string false if local config don't exists
     */
    public static function getWpConfigLocalStoredPath()
    {
        return InstallerOrigFileMng::getInstance()->getEntryStoredPath(self::CONFIG_ORIG_FILE_WPCONFIG_ID_OVERWRITE_SITE);
    }

    /**
     * Return wp-config path stored in orig folder of source site
     *
     * @return false|string false if old wp config does not exist
     */
    public static function getSourceWpConfigPath()
    {
        return InstallerOrigFileMng::getInstance()->getEntryStoredPath(self::CONFIG_ORIG_FILE_WPCONFIG_ID);
    }

    /**
     * Get AddHandler line from existing WP .htaccess file
     *
     * @return string
     */
    private static function getOldHtaccessAddhandlerLine()
    {
        $origFiles          = InstallerOrigFileMng::getInstance();
        $backupHtaccessPath = $origFiles->getEntryStoredPath(self::CONFIG_ORIG_FILE_HTACCESS_ID_OVERWRITE_SITE);
        Log::info("Installer Host Htaccess path: " . $backupHtaccessPath, Log::LV_DEBUG);

        if ($backupHtaccessPath !== false && file_exists($backupHtaccessPath)) {
            $htaccessContent = file_get_contents($backupHtaccessPath);
            if (!empty($htaccessContent)) {
                // match and trim non commented line  "AddHandler application/x-httpd-XXXX .php" case insenstive
                $re      = '/^[\s\t]*[^#]?[\s\t]*(AddHandler[\s\t]+.+\.php[ \t]?.*?)[\s\t]*$/mi';
                $matches = array();
                if (preg_match($re, $htaccessContent, $matches)) {
                    return "\n" . $matches[1];
                }
            }
        }
        return '';
    }

    /**
     * Sets up the web config file based on the inputs from the installer forms.
     *
     * @param object $dbh  The database connection handle for this request
     * @param string $path The path to the config file
     *
     * @return void
     */
    public static function setup($dbh, $path)
    {
        Log::info("\nWEB SERVER CONFIGURATION FILE UPDATED:");

        $paramsManager = PrmMng::getInstance();
        $htAccessPath  = "{$path}/.htaccess";
        $mu_generation = DUPX_ArchiveConfig::getInstance()->mu_generation;

        // SKIP HTACCESS
        $skipHtaccessConfigVals = array('nothing', 'original');
        if (in_array($paramsManager->getValue(PrmMng::PARAM_HTACCESS_CONFIG), $skipHtaccessConfigVals)) {
            if (!DUPX_InstallerState::isRestoreBackup() && !DUPX_InstallerState::isAddSiteOnMultisite()) {
                // on restore packup mode no warning needed
                $longMsg = 'Retaining the original .htaccess files may cause '
                    . 'issues with the initial setup of your site. '
                    . 'If you encounter any problems, check the contents of the configuration files manually or '
                    . 'reinstall the site again by changing the configuration file settings.';

                DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                    'shortMsg'    => 'Can\'t update new .htaccess file',
                    'level'       => DUPX_NOTICE_ITEM::NOTICE,
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                    'longMsg'     => $longMsg,
                    'sections'    => 'general'
                ));
            }
            return;
        }

        $timestamp    = date("Y-m-d H:i:s");
        $post_url_new = $paramsManager->getValue(PrmMng::PARAM_URL_NEW);
        $newdata      = parse_url($post_url_new);
        $newpath      = DUPX_U::addSlash(isset($newdata['path']) ? $newdata['path'] : "");
        $update_msg   = "# This file was updated by Duplicator Pro on {$timestamp}.\n";
        $update_msg  .= "# See the original_files_ folder for the original source_site_htaccess file.";
        $update_msg  .= self::getOldHtaccessAddhandlerLine();

        switch (DUPX_InstallerState::getInstType()) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_STANDALONE:
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_RECOVERY_SINGLE_SITE:
                $tmp_htaccess = self::htAcccessNoMultisite($update_msg, $newpath, $dbh);
                Log::info("- Preparing .htaccess file with basic setup.");
                break;
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBDOMAIN:
                if ($mu_generation == 1) {
                    $tmp_htaccess = self::htAccessSubdomainPre53($update_msg, $newpath);
                } else {
                    $tmp_htaccess = self::htAccessSubdomain($update_msg, $newpath);
                }
                Log::info("- Preparing .htaccess file with multisite subdomain setup.");
                break;
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBFOLDER:
                if ($mu_generation == 1) {
                    $tmp_htaccess = self::htAccessSubdirectoryPre35($update_msg, $newpath);
                } else {
                    $tmp_htaccess = self::htAccessSubdirectory($update_msg, $newpath);
                }
                Log::info("- Preparing .htaccess file with multisite subdirectory setup.");
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_NOT_SET:
                throw new Exception('Cannot change setup with current installation type [' . DUPX_InstallerState::getInstType() . ']');
            default:
                throw new Exception('Unknown mode');
        }

        if (file_exists($htAccessPath) && SnapIO::chmod($htAccessPath, 'u+rw') === false) {
            Log::info("WARNING: Unable to update htaccess file permessition.");
            DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                'shortMsg'    => 'Can\'t update new htaccess file',
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'longMsg'     => 'Unable to update the .htaccess file! Please check the permission on the root directory and make sure the .htaccess exists.',
                'sections'    => 'general'
            ));
        } elseif (file_put_contents($htAccessPath, $tmp_htaccess) === false) {
            Log::info("WARNING: Unable to update the .htaccess file! Please check the permission on the root directory and make sure the .htaccess exists.");
            DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                'shortMsg'    => 'Can\'t update new htaccess file',
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'longMsg'     => 'Unable to update the .htaccess file! Please check the permission on the root directory and make sure the .htaccess exists.',
                'sections'    => 'general'
            ));
        } else {
            DUP_PRO_Extraction::setPermsFromParams($htAccessPath);
            Log::info("HTACCESS FILE - Successfully updated the .htaccess file setting.");
        }
    }

    /**
     * Get htaccess content no multisite
     *
     * @param string $update_msg update message
     * @param string $newpath    target path
     * @param mysqli $dbh        dtabase connection
     *
     * @return string
     */
    private static function htAcccessNoMultisite($update_msg, $newpath, $dbh)
    {
        $result = '';
        // no multisite
        $empty_htaccess = false;
        $optonsTable    = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getOptionsTableName());
        $query_result   = DUPX_DB::mysqli_query($dbh, "SELECT option_value FROM `" . $optonsTable . "` WHERE option_name = 'permalink_structure' ");

        if ($query_result) {
            $row = @mysqli_fetch_array($query_result);
            if ($row != null) {
                $permalink_structure = trim($row[0]);
                $empty_htaccess      = empty($permalink_structure);
            }
        }

        if ($empty_htaccess) {
            Log::info('NO PERMALINK STRUCTURE FOUND: set htaccess without directives');
            $result = <<<EMPTYHTACCESS
{$update_msg}
# BEGIN WordPress
# The directives (lines) between `BEGIN WordPress` and `END WordPress` are
# dynamically generated, and should only be modified via WordPress filters.
# Any changes to the directives between these markers will be overwritten.

# END WordPress
EMPTYHTACCESS;
        } else {
            $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . {$newpath}index.php [L]
</IfModule>
# END WordPress
HTACCESS;
        }

        return $result;
    }

    /**
     * Get htaccess content subdomain multisite pre WP 5.3
     *
     * @param string $update_msg update message
     * @param string $newpath    target path
     *
     * @return string
     */
    private static function htAccessSubdomainPre53($update_msg, $newpath)
    {
        // Pre wordpress 3.5
        $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress (Pre 3.5 Multisite Subdomain)
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]

# uploaded files
RewriteRule ^files/(.+) wp-includes/ms-files.php?file=$1 [L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule . index.php [L]
# END WordPress
HTACCESS;
        return $result;
    }

    /**
     * Get htaccess content subdomain multisite
     *
     * @param string $update_msg update message
     * @param string $newpath    target path
     *
     * @return string
     */
    private static function htAccessSubdomain($update_msg, $newpath)
    {
        // 3.5+
        $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress (3.5+ Multisite Subdomain)
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^wp-admin$ wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^(wp-(content|admin|includes).*) $1 [L]
RewriteRule ^(.*\.php)$ $1 [L]
RewriteRule . index.php [L]
# END WordPress
HTACCESS;
        return $result;
    }

    /**
     * Get htaccess content subfolder multisite pre WP 5.3
     *
     * @param string $update_msg update message
     * @param string $newpath    target path
     *
     * @return string
     */
    private static function htAccessSubdirectoryPre35($update_msg, $newpath)
    {
        // Pre 3.5
        $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress (Pre 3.5 Multisite Subdirectory)
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]

# uploaded files
RewriteRule ^([_0-9a-zA-Z-]+/)?files/(.+) wp-includes/ms-files.php?file=$2 [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^[_0-9a-zA-Z-]+/(wp-(content|admin|includes).*) $1 [L]
RewriteRule ^[_0-9a-zA-Z-]+/(.*\.php)$ $1 [L]
RewriteRule . index.php [L]
# END WordPress
HTACCESS;
        return $result;
    }

    /**
     * Get htaccess content subfolder multisite
     *
     * @param string $update_msg update message
     * @param string $newpath    target path
     *
     * @return string
     */
    private static function htAccessSubdirectory($update_msg, $newpath)
    {
        $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress (3.5+ Multisite Subdirectory)
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2 [L]
RewriteRule . index.php [L]
# END WordPress
HTACCESS;
        return $result;
    }
}
