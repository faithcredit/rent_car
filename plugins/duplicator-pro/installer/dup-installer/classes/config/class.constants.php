<?php

/**
 * Class used to group all global constants
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Constants
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Bootstrap;
use Duplicator\Libs\Shell\Shell;

class DUPX_Constants
{
    const CHUNK_EXTRACTION_TIMEOUT_TIME_ZIP        = 5;
    const CHUNK_EXTRACTION_TIMEOUT_TIME_DUP        = 5;
    const CHUNK_DBINSTALL_TIMEOUT_TIME             = 5;
    const CHUNK_MAX_TIMEOUT_TIME                   = 5;
    const DEFAULT_MAX_STRLEN_SERIALIZED_CHECK_IN_M = 4; // 0 no limit
    const FAQ_URL                                  = 'https://snapcreek.com/duplicator/docs/faqs-tech';
    const MIN_NEW_PASSWORD_LEN                     = 6;
    const BACKUP_RENAME_PREFIX                     = 'dp___bk_';

    /**
     * Init method used to auto initialize the global params
     * This function init all params before read from request
     *
     * @return void
     */
    public static function init()
    {
        //DATABASE SETUP: all time in seconds
        //max_allowed_packet: max value 1073741824 (1268MB) see my.ini
        $GLOBALS['DB_MAX_TIME']                           = 5000;
        $GLOBALS['DATABASE_PAGE_SIZE']                    = 3500;
        $GLOBALS['DB_MAX_PACKETS']                        = 268435456;
        $GLOBALS['DBCHARSET_DEFAULT']                     = 'utf8';
        $GLOBALS['DBCOLLATE_DEFAULT']                     = 'utf8_general_ci';
        $GLOBALS['DB_RENAME_PREFIX']                      = self::BACKUP_RENAME_PREFIX . date("dHi") . '_';
        $GLOBALS['DB_INSTALL_MULTI_THREADED_MAX_RETRIES'] = 3;

        if (!defined('MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH')) {
            define('MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH', 10);
        }

        //UPDATE TABLE SETTINGS
        $GLOBALS['REPLACE_LIST'] = array();
        $GLOBALS['DEBUG_JS']     = false;

        //CONSTANTS
        if (!defined("DUPLICATOR_PRO_SSDIR_NAME")) {
            define("DUPLICATOR_PRO_SSDIR_NAME", 'wp-snapshots-dup-pro');  //This should match DUPLICATOR_PRO_SSDIR_NAME in duplicator.php
        }

        //GLOBALS
        $GLOBALS["NOTICES_FILE_PATH"]                      = DUPX_INIT . '/' . "dup-installer-notices__" . Bootstrap::getPackageHash() . ".json";
        $GLOBALS["CHUNK_DATA_FILE_PATH"]                   = DUPX_INIT . '/' . "dup-installer-chunk__" . Bootstrap::getPackageHash() . ".json";
        $GLOBALS['PHP_MEMORY_LIMIT']                       = ini_get('memory_limit') === false ? 'n/a' : ini_get('memory_limit');
        $GLOBALS['PHP_SUHOSIN_ON']                         = Shell::isSuhosinEnabled() ? 'enabled' : 'disabled';
        $GLOBALS['DISPLAY_MAX_OBJECTS_FAILED_TO_SET_PERM'] = 5;

        // Displaying notice for slow zip chunk extraction
        $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_DISP_NOTICE_AFTER']                     = 5 * 60 * 60; // 5 minutes
        $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_DISP_NOTICE_MIN_EXPECTED_EXTRACT_TIME'] = 10 * 60 * 60; // 10 minutes
        $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_DISP_NEXT_NOTICE_INTERVAL']             = 5 * 60 * 60; // 5 minutes

        $additional_msg                           = ' for additional details <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q" target="_blank">click here</a>.';
        $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_NOTICES'] = array(
            'This server looks to be under load or throttled, the extraction process may take some time',
            'This host is currently experiencing very slow I/O. You can continue to wait or try a manual extraction.',
            'This host I/O is currently having issues. It is recommended to try a manual extraction.',
        );
        foreach ($GLOBALS['ZIP_ARC_CHUNK_EXTRACT_NOTICES'] as $key => $val) {
            $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_NOTICES'][$key] = $val . $additional_msg;
        }

        $GLOBALS['FW_USECDN'] = false;
        $GLOBALS['NOW_TIME']  = @date("His");
    }
}
