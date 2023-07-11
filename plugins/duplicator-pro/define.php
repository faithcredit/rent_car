<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || exit;

// For compatibility to an older WP
if (!defined('KB_IN_BYTES')) {
    define('KB_IN_BYTES', 1024);
}
if (!defined('MB_IN_BYTES')) {
    define('MB_IN_BYTES', 1024 * KB_IN_BYTES);
}
if (!defined('GB_IN_BYTES')) {
    define('GB_IN_BYTES', 1024 * MB_IN_BYTES);
}

define('DUPLICATOR_PRO_VERSION', '4.5.11');
define('DUPLICATOR_PRO_GIFT_THIS_RELEASE', false); // Display Gift - should be true for new features OR if we want them to fill out survey
define('DUPLICATOR_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DUPLICATOR_PRO_SITE_URL', get_site_url());
define('DUPLICATOR_PRO_IMG_URL', DUPLICATOR_PRO_PLUGIN_URL . '/assets/img');

if (!defined("DUPLICATOR_PRO_SSDIR_NAME")) {
    define("DUPLICATOR_PRO_SSDIR_NAME", 'backups-dup-pro');
}

if (!defined("DUPLICATOR_PRO_DEBUG_TPL_OUTPUT_INVALID")) {
    define("DUPLICATOR_PRO_DEBUG_TPL_OUTPUT_INVALID", false);
}

if (!defined("DUPLICATOR_PRO_DEBUG_TPL_DATA")) {
    define("DUPLICATOR_PRO_DEBUG_TPL_DATA", false);
}

if (!defined("DUPLICATOR_FORCE_IMPORT_BRIDGE_MODE")) {
    define("DUPLICATOR_FORCE_IMPORT_BRIDGE_MODE", false);
}

// PATHS
$contentPath = untrailingslashit(wp_normalize_path(realpath(WP_CONTENT_DIR)));

define("DUPLICATOR_PRO_SSDIR_PATH", $contentPath . '/' . DUPLICATOR_PRO_SSDIR_NAME);
define("DUPLICATOR_PRO_SSDIR_URL", content_url() . '/' . DUPLICATOR_PRO_SSDIR_NAME);
define("DUPLICATOR_PRO_IMPORTS_DIR_NAME", 'imports');
define("DUPLICATOR_PRO_RECOVER_DIR_NAME", 'recover');

define("DUPLICATOR_PRO_SSDIR_PATH_TMP", DUPLICATOR_PRO_SSDIR_PATH . '/tmp');
define("DUPLICATOR_PRO_PATH_IMPORTS", DUPLICATOR_PRO_SSDIR_PATH . '/' . DUPLICATOR_PRO_IMPORTS_DIR_NAME);
define("DUPLICATOR_PRO_URL_IMPORTS", DUPLICATOR_PRO_SSDIR_URL . '/' . DUPLICATOR_PRO_IMPORTS_DIR_NAME);
define("DUPLICATOR_PRO_SSDIR_PATH_TMP_IMPORT", DUPLICATOR_PRO_SSDIR_PATH_TMP . '/import');

define("DUPLICATOR_PRO_PATH_RECOVER", DUPLICATOR_PRO_SSDIR_PATH . '/' . DUPLICATOR_PRO_RECOVER_DIR_NAME);
define("DUPLICATOR_PRO_URL_RECOVER", DUPLICATOR_PRO_SSDIR_URL . '/' . DUPLICATOR_PRO_RECOVER_DIR_NAME);

define("DUPLICATOR_PRO_SSDIR_PATH_INSTALLER", DUPLICATOR_PRO_SSDIR_PATH . '/installer');
define('DUPLICATOR_PRO_LOCAL_OVERWRITE_PARAMS', 'duplicator_pro_params_overwrite');

// MATCH archive pattern, matches[1] is archive name and hash
define('DUPLICATOR_PRO_ARCHIVE_REGEX_PATTERN', '/^(.+_[a-z0-9]{7,}_[0-9]{14})_archive\.(?:zip|daf)$/');
// MATCH installer.php installer-backup.php and full installer with hash
define('DUPLICATOR_PRO_INSTALLER_REGEX_PATTERN', '/^(?:.+_[a-z0-9]{7,}_[0-9]{14}_)?installer(?:-backup)?\.php$/');
// MATCH dup-installer and dup-installer-[HASH]
define('DUPLICATOR_PRO_DUP_INSTALLER_FOLDER_REGEX_PATTERN', '/^dup-installer(?:-[a-z0-9]{7,}-[0-9]{8})?$/');
define('DUPLICATOR_PRO_DUP_INSTALLER_BOOTLOG_REGEX_PATTERN', '/^dup-installer-bootlog__[a-z0-9]{7,}-[0-9]{8}.txt$/');
define('DUPLICATOR_PRO_DUP_INSTALLER_OWRPARAM_REGEX_PATTERN', '/^' . DUPLICATOR_PRO_LOCAL_OVERWRITE_PARAMS . '_[a-z0-9]{7,}-[0-9]{8}.json$/');
define("DUPLICATOR_PRO_DUMP_PATH", DUPLICATOR_PRO_SSDIR_PATH . '/dump');
define("DUPLICATOR_PRO_ORIG_FOLDER_PREFIX", 'original_files_');
define('DUPLICATOR_PRO_LIB_PATH', DUPLICATOR____PATH . '/lib');
define('DUPLICATOR_PRO_CERT_PATH', apply_filters('duplicator_pro_certificate_path', DUPLICATOR____PATH . '/src/Libs/Certificates/cacert.pem'));

//RESTRAINT CONSTANTS
define('DUPLICATOR_PRO_PHP_MAX_MEMORY', 4 * GB_IN_BYTES);
define("DUPLICATOR_PRO_MIN_MEMORY_LIMIT", '256M');
define("DUPLICATOR_PRO_DB_MAX_TIME", 5000);
define("DUPLICATOR_PRO_DB_EOF_MARKER", 'DUPLICATOR_PRO_MYSQLDUMP_EOF');
define("DUPLICATOR_PRO_DB_MYSQLDUMP_ERROR_CONTAINING_LINE_COUNT", 10);
define("DUPLICATOR_PRO_DB_MYSQLDUMP_ERROR_CHARS_IN_LINE_COUNT", 1000);
define("DUPLICATOR_PRO_SCAN_SITE_ZIP_ARCHIVE_WARNING_SIZE", 350 * MB_IN_BYTES);
define("DUPLICATOR_PRO_SCAN_SITE_WARNING_SIZE", 1.5 * GB_IN_BYTES);

define("DUPLICATOR_PRO_SCAN_WARN_FILE_SIZE", 4 * MB_IN_BYTES);
define("DUPLICATOR_PRO_SCAN_WARN_DIR_SIZE", 100 * MB_IN_BYTES);
define("DUPLICATOR_PRO_SCAN_CACHESIZE", 1 * MB_IN_BYTES);
define("DUPLICATOR_PRO_SCAN_DB_ALL_SIZE", 100 * MB_IN_BYTES);
define("DUPLICATOR_PRO_SCAN_DB_ALL_ROWS", 1000000); //1 million rows
define('DUPLICATOR_PRO_SCAN_DB_TBL_ROWS', 100000); //100K rows per table
define('DUPLICATOR_PRO_SCAN_DB_TBL_SIZE', 10 * MB_IN_BYTES);
define("DUPLICATOR_PRO_SCAN_TIMEOUT", 25); //Seconds
define("DUPLICATOR_PRO_SCAN_MAX_UNREADABLE_COUNT", 1000);
define("DUPLICATOR_PRO_MAX_FAILURE_COUNT", 1000);
define("DUPLICATOR_PRO_BUFFER_DOWNLOAD_SIZE", 4377); // BYTES
define("DUPLICATOR_PRO_DEFAULT_CHUNK_UPLOAD_SIZE", 1024); // KB
define('DUPLICATOR_PRO_SQL_SCRIPT_PHP_CODE_MULTI_THREADED_MAX_RETRIES', 10);
define('DUPLICATOR_PRO_TEST_SQL_LOCK_NAME', 'duplicator_pro_test_lock');
define("DUPLICATOR_PRO_FTP_CURL_CHUNK_SIZE", 2 * MB_IN_BYTES);

define("DUPLICATOR_PRO_SCAN_MIN_WP", "4.6.0");
define("DUPLICATOR_PRO_MIN_SIZE_DBFILE_WITHOUT_FILTERS", 5120); // SQL CHECK:  File should be at minimum 5K.
                                                                // A base WP install with only Create tables is about 9K
define("DUPLICATOR_PRO_MIN_SIZE_DBFILE_WITH_FILTERS", 800);

define("DUPLICATOR_PRO_ONEDRIVE_UPLOAD_CHUNK_MIN_SIZE_IN_KB", 320);
define("DUPLICATOR_PRO_ONEDRIVE_UPLOAD_CHUNK_DEFAULT_SIZE_IN_KB", 3200);

$GLOBALS['DUPLICATOR_PRO_SERVER_LIST'] = array('Apache', 'LiteSpeed', 'Nginx', 'Lighttpd', 'IIS', 'WebServerX', 'uWSGI', 'Flywheel');
$GLOBALS['DUPLICATOR_PRO_OPTS_DELETE'] = array('duplicator_pro_ui_view_state', 'duplicator_pro_package_active', 'duplicator_pro_settings');

$GLOBALS['DUPLICATOR_PRO_GLOBAL_FILE_FILTERS_ON'] = true;
$GLOBALS['DUPLICATOR_PRO_GLOBAL_DIR_FILTERS_ON']  = true;

/* TRANSIENT OPTIONS */
define('DUPLICATOR_PRO_FRONTEND_TRANSIENT', 'duplicator_pro_frotend_delay');
define('DUPLICATOR_PRO_FRONTEND_ACTION_DELAY', 1 * MINUTE_IN_SECONDS);

define('DUPLICATOR_PRO_INSTALLER_RENAME_KEY', 'rename_delay');
define('DUPLICATOR_PRO_INSTALLER_RENAME_DELAY', 12 * HOUR_IN_SECONDS);

define('DUPLICATOR_PRO_SETTINGS_MESSAGE_TRANSIENT', 'duplicator_pro_settings_message');
define('DUPLICATOR_PRO_SETTINGS_MESSAGE_TIMEOUT', 1 * MINUTE_IN_SECONDS);

define('DUPLICATOR_PRO_PENDING_CANCELLATION_TRANSIENT', 'duplicator_pro_pending_cancellations');
define('DUPLICATOR_PRO_PENDING_CANCELLATION_TIMEOUT', 1 * DAY_IN_SECONDS);

define('DUPLICATOR_TMP_CLEANUP_CHECK_KEY', 'tmp_cleanup_check');
define('DUPLICATOR_TMP_CLEANUP_CHECK_DELAY', 1 * DAY_IN_SECONDS);

define('DUPLICATOR_PRO_DEFAULT_AJAX_PROTOCOL', 'admin');

/* TODO: Replace all target opening up in different target with the common help target */
define('DUPLICATOR_PRO_HELP_TARGET', '_sc-help');

/* Help URLs */
/* TODO: search for these URLs throughout the code and replace with the corresponding define */
define('DUPLICATOR_PRO_BLOG_URL', 'https://snapcreek.com/');
define('DUPLICATOR_PRO_DUPLICATOR_DOCS_URL', 'https://snapcreek.com/duplicator/docs/');
define('DUPLICATOR_PRO_USER_GUIDE_URL', DUPLICATOR_PRO_DUPLICATOR_DOCS_URL . 'guide/');
define('DUPLICATOR_PRO_TECH_FAQ_URL', DUPLICATOR_PRO_DUPLICATOR_DOCS_URL . 'faqs-tech/');
define('DUPLICATOR_PRO_RECOVERY_GUIDE_URL', DUPLICATOR_PRO_BLOG_URL . 'quickly-restore-wordpress-site-using-recovery-point/');
define('DUPLICATOR_PRO_DRAG_DROP_GUIDE_URL', DUPLICATOR_PRO_BLOG_URL . 'how-migrate-wordpress-site-drag-drop-duplicator-pro/');

define('DUPLICATOR_PRO_LOCKING_FILE_FILENAME', DUPLICATOR____PATH . '/dup_pro_lock.bin');

if (!defined('DUPLICATOR_PRO_DISALLOW_IMPORT')) {
    define('DUPLICATOR_PRO_DISALLOW_IMPORT', false);
}

if (!defined('DUPLICATOR_PRO_DISALLOW_RECOVERY')) {
    define('DUPLICATOR_PRO_DISALLOW_RECOVERY', false);
}

if (!defined('DUPLICATOR_AUTH_KEY')) {
    define('DUPLICATOR_AUTH_KEY', '');
}

if (!defined('DUPLICATOR_CAPABILITIES_RESET')) {
    define('DUPLICATOR_CAPABILITIES_RESET', false);
}
