<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\MigrationMng;
use Duplicator\Core\Models\AbstractEntitySingleton;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapWP;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Utils\Crypt\CryptBlowfish;
use Duplicator\Utils\ZipArchiveExtended;
use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use Duplicator\Utils\GroupOptions;
use Duplicator\Utils\PathUtil;

abstract class DUP_PRO_Dropbox_Transfer_Mode
{
    const Unconfigured = -1;
    const Disabled     = 0;
    const cURL         = 1;
    const FOpen_URL    = 2;
}

abstract class DUP_PRO_Google_Drive_Transfer_Mode
{
    const Unconfigured = -1;
    const Auto         = 0;
    const FOpen_URL    = 1;
}

abstract class DUP_PRO_Thread_Lock_Mode
{
    const Flock    = 0;
    const SQL_Lock = 1;
}

abstract class DUP_PRO_Sql_Lock_Check
{
    const Sql_Success = 1;
    const Sql_Fail    = -1;
}

abstract class DUP_PRO_Email_Build_Mode
{
    const No_Emails           = 0;
    const Email_On_Failure    = 1;
    const Email_On_All_Builds = 2;
}

abstract class DUP_PRO_JSON_Mode
{
    const PHP    = 0;
    const Custom = 1;
}

abstract class DUP_PRO_Archive_Build_Mode
{
    const Unconfigured = -1;
    const Shell_Exec   = 1;
    const ZipArchive   = 2;
    const DupArchive   = 3;
}

class DUP_PRO_Server_Load_Reduction
{
    const None  = 0;
    const A_Bit = 1;
    const More  = 2;
    const A_Lot = 3;

    public static function microseconds_from_reduction($reduction)
    {
        switch ($reduction) {
            case self::A_Bit:
                return 20;
            case self::More:
                return 100;
            case self::A_Lot:
                return 500;
            case self::None:
            default:
                return 0;
        }
    }
}

abstract class DUP_PRO_ZipArchive_Mode
{
    const Multithreaded = 0;
    const SingleThread  = 1;
}

class DUP_PRO_Global_Entity extends AbstractEntitySingleton
{
    const INSTALLER_NAME_MODE_WITH_HASH = 'withhash';
    const INSTALLER_NAME_MODE_SIMPLE    = 'simple';

    const CLEANUP_HOOK                  = 'dup_pro_cleanup_hook';
    const CLEANUP_INTERVAL_NAME         = 'dup_pro_custom_interval';
    const CLEANUP_FILE_TIME_DELAY       = 81000; // In seconds, 22.5 hours
    const CLEANUP_EMAIL_NOTICE_INTERVAL = 24; // In hours

    const CLEANUP_MODE_OFF  = 0;
    const CLEANUP_MODE_MAIL = 1;
    const CLEANUP_MODE_AUTO = 2;

    const UNINSTALL_PACKAGE_OPTION_KEY  = 'duplicator_pro_uninstall_package';
    const UNINSTALL_SETTINGS_OPTION_KEY = 'duplicator_pro_uninstall_settings';

    const INPUT_MYSQLDUMP_OPTION_PREFIX = 'package_mysqldump_';

    //GENERAL
    /** @var bool */
    public $uninstall_settings = false;
    /** @var bool */
    public $uninstall_packages = false;
    /** @var bool */
    public $crypt = true;
    //PACKAGES::Visual
    /** @var int dates format create */
    public $package_ui_created = 1;
    /** @var bool */
    public $package_mysqldump = false;
    /** @var string */
    public $package_mysqldump_path = '';
    /** @var int<0, 1> ENUM */
    public $package_phpdump_mode = DUP_PRO_DB::PHPDUMP_MODE_MULTI;
    /** @var int<0, max> */
    public $package_mysqldump_qrylimit = DUP_PRO_Constants::DEFAULT_MYSQL_DUMP_CHUNK_SIZE;
    /** @var GroupOptions[] */
    private $packageMysqldumpOptions = [];

    //PACKAGES::Basic::Archive
    public $archive_build_mode = DUP_PRO_Archive_Build_Mode::Unconfigured;
    /** @var bool */
    public $archive_compression = true;
    /** @var bool */
    public $ziparchive_validation = false;
    /** @var int<0, 1> ENUM */
    public $ziparchive_mode = DUP_PRO_ZipArchive_Mode::Multithreaded;
    /** @var int<0, max> */
    public $ziparchive_chunk_size_in_mb = DUP_PRO_Constants::DEFAULT_ZIP_ARCHIVE_CHUNK;
    /** @var bool */
    public $homepath_as_abspath = false;
    //Schedules
    /** @var int<-1, 2> ENUM */
    public $archive_build_mode_schedule = DUP_PRO_Archive_Build_Mode::Unconfigured; // required to be pre-set to upgrade logic works
    /** @var bool */
    public $archive_compression_schedule = true;
    //PACKAGES::Basic::Processing
    /** @var int<0, 3> ENUM */
    public $server_load_reduction = DUP_PRO_Server_Load_Reduction::None;
    /** @var int<0, max> */
    public $max_package_runtime_in_min = DUP_PRO_Constants::DEFAULT_MAX_PACKAGE_RUNTIME_IN_MIN;
    /** @var int<0, max> */
    public $php_max_worker_time_in_sec = DUP_PRO_Constants::DEFAULT_MAX_WORKER_TIME;
    //PACKAGES::Basic::Cleanup
    /** @var int<0, 2> ENUM */
    public $cleanup_mode = self::CLEANUP_MODE_OFF;
    /** @var string */
    public $cleanup_email = '';
    /** @var int<0, max> */
    public $auto_cleanup_hours = 24;
    //PACKAGES::Adanced
    /** @var int<0, 1> ENUM */
    public $lock_mode = DUP_PRO_Thread_Lock_Mode::SQL_Lock;
    /** @var int<0, 1> ENUM */
    public $json_mode = DUP_PRO_JSON_Mode::PHP;
    /** @var string */
    public $ajax_protocol = '';
    /** @var string */
    public $custom_ajax_url = '';
    /** @var bool */
    public $clientside_kickoff = false;
    /** @var bool */
    public $basic_auth_enabled = false;
    /** @var string */
    public $basic_auth_user = '';  // Not actively used but required for upgrade
    /** @var string */
    public $basic_auth_password = '';
    /** @var string ENUM */
    public $installer_name_mode = self::INSTALLER_NAME_MODE_SIMPLE;
    /** @var string */
    public $installer_base_name = DUP_PRO_Installer::DEFAULT_INSTALLER_FILE_NAME_WITHOUT_HASH;
    /** @var int<0, max> */
    public $chunk_size = 2048;
    /** @var bool */
    public $skip_archive_scan = false;
    //SCHEDULES
    /** @var int<0, 2> ENUM */
    public $send_email_on_build_mode = DUP_PRO_Email_Build_Mode::Email_On_Failure;
    /** @var string */
    public $notification_email_address = '';
    //STORAGE
    /** @var bool */
    public $storage_htaccess_off = false;
    /** @var int<0, max> */
    public $max_storage_retries = 10;
    /** @var int<0, max> */
    public $max_default_store_files = 20;
    /** @var bool */
    public $purge_default_package_record = false;
    /** @var int<0, max> */
    public $dropbox_upload_chunksize_in_kb = 2000;
    /** @var int<-1, 2> ENUM */
    public $dropbox_transfer_mode = DUP_PRO_Dropbox_Transfer_Mode::Unconfigured;
    /** @var int<0, max> */
    public $gdrive_upload_chunksize_in_kb = 1024;  // Not exposed through the UI (yet)
    /** @var int<-1, 1> ENUM */
    public $gdrive_transfer_mode = DUP_PRO_Google_Drive_Transfer_Mode::Auto;
    /** @var int<0, max> */
    public $s3_upload_part_size_in_kb = 6000;
    /** @var int<0, max> */
    public $onedrive_upload_chunksize_in_kb = DUPLICATOR_PRO_ONEDRIVE_UPLOAD_CHUNK_DEFAULT_SIZE_IN_KB;
    /** @var int<0, max> */
    public $local_upload_chunksize_in_MB = DUP_PRO_Storage_Entity::LOCAL_STORAGE_CHUNK_SIZE_IN_MB;
    /** @var int[] */
    public $manual_mode_storage_ids = [
        DUP_PRO_Virtual_Storage_IDs::Default_Local
    ];
    //LICENSING
    /** @var int<-3, 5> License Status ENUM */
    public $license_status = License::STATUS_UNKNOWN;
    /** @var int<-1, max> */
    public $license_expiration_time = -1;
    /** @var bool */
    public $license_no_activations_left = false;
    /** @var int<0, 2> License Visibility ENUM */
    public $license_key_visible = License::VISIBILITY_ALL;
    /** @var string */
    public $lkp = ''; // Not actively used but required for upgrade
    /** @var int<-1, 11> License Type ENUM */
    public $license_type = License::TYPE_UNKNOWN;
    /** @var int<-1, max> */
    public $license_limit = -1;
    //UPDATE CACHING
    /** @var int<0, max> */
    public $last_system_check_timestamp = 0;
    /** @var int<0, max> */
    public $initial_activation_timestamp = 0;
    /** @var bool */
    public $ssl_useservercerts = true;
    /** @var bool */
    public $ssl_disableverify = true;
    /** @var int<0, max> */
    public $import_chunk_size = DUPLICATOR_PRO_DEFAULT_CHUNK_UPLOAD_SIZE; // in KB, 0 no chunk
    /** @var string */
    public $import_custom_path = '';
    /** @var bool */
    public $ipv4_only = false;
    /** @var bool */
    public $debug_on = false;
    /** @var bool */
    public $unhook_third_party_js = false;
    /** @var bool */
    public $unhook_third_party_css = false;
    /** @var bool */
    public $profile_beta = false;
    /** @var bool */
    public $dupHidePackagesGiftFeatures = !DUPLICATOR_PRO_GIFT_THIS_RELEASE; // @phpstan-ignore-line
    /** @var string if empty custom path is disabled */
    private $recoveryCustomPath = '';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->packageMysqldumpOptions = $this->getDefaultMysqlDumpOptions();
    }

    /**
     * Return entity type identifier
     *
     * @return string
     */
    public static function getType()
    {
        return 'DUP_PRO_Global_Entity';
    }

    /**
     * Will be called, automatically, when Serialize
     *
     * @return array
     */
    public function __serialize()
    {
        update_option(self::UNINSTALL_PACKAGE_OPTION_KEY, $this->uninstall_packages);
        update_option(self::UNINSTALL_SETTINGS_OPTION_KEY, $this->uninstall_settings);

        $data = JsonSerialize::serializeToData($this, JsonSerialize::JSON_SKIP_MAGIC_METHODS |  JsonSerialize::JSON_SKIP_CLASS_NAME);
        if ($this->crypt && strlen($this->basic_auth_password)) {
            $data['basic_auth_password'] = CryptBlowfish::encrypt($this->basic_auth_password);
        }
        if ($this->crypt && strlen($this->lkp)) {
            $data['lkp'] = CryptBlowfish::encrypt($this->lkp);
        }
        return $data;
    }

    /**
     * Serialize
     *
     * Will be called, automatically, when unserialize() is called on a BigInteger object.
     *
     * @return void
     */
    public function __wakeup()
    {
        // fix boolean value from old version
        $this->license_key_visible = (int) $this->license_key_visible;

        $loadedOptionNames = [];
        foreach ($this->packageMysqldumpOptions as $index => $data) {
            $this->packageMysqldumpOptions[$index] = GroupOptions::getObjectFromArray($data); // @phpstan-ignore-line
            $loadedOptionNames[]                   = $this->packageMysqldumpOptions[$index]->getOptionName();
        }
        foreach ($this->getDefaultMysqlDumpOptions() as $defOpt) {
            if (in_array($defOpt->getOptionName(), $loadedOptionNames)) {
                continue;
            }
            $this->packageMysqldumpOptions[] = $defOpt;
        }

        if ($this->crypt && strlen($this->basic_auth_password)) {
            $this->basic_auth_password = CryptBlowfish::decrypt($this->basic_auth_password);
        }

        if ($this->crypt && strlen($this->lkp)) {
            $this->lkp = CryptBlowfish::decrypt($this->lkp);
        }
    }

    /**
     * Return default options
     *
     * @return GroupOptions[]
     */
    private function getDefaultMysqlDumpOptions()
    {
        return [
            new GroupOptions('quick', self::INPUT_MYSQLDUMP_OPTION_PREFIX, false),
            new GroupOptions('extended-insert', self::INPUT_MYSQLDUMP_OPTION_PREFIX, false),
            new GroupOptions('routines', self::INPUT_MYSQLDUMP_OPTION_PREFIX, true),
            new GroupOptions('disable-keys', self::INPUT_MYSQLDUMP_OPTION_PREFIX, false),
            new GroupOptions('compact', self::INPUT_MYSQLDUMP_OPTION_PREFIX, false),
        ];
    }

    /**
     * This function is called on first istance of singletion object
     * Can be used to set dynamic properties values
     *
     * @return void
     */
    protected function firstIstanceInit()
    {
        $result = $this->reset(
            [],
            [__CLASS__, 'getDefaultPropInitVal'],
            function () {
                $this->set_build_mode();
            }
        );
        if ($result === false) {
            throw new Exception('Can\'t reset the user settings');
        }
    }

    /**
     * Return default prop val by system config
     *
     * @param string $name prop nam
     * @param mixed  $val  prop val
     *
     * @return mixed
     */
    protected static function getDefaultPropInitVal($name, $val)
    {
        switch ($name) {
            case 'cleanup_email':
                return get_option('admin_email');
            case 'lock_mode':
                return self::getDefaultLockType();
            case 'ajax_protocol':
                return strtolower(parse_url(network_admin_url(), PHP_URL_SCHEME));
            case 'php_max_worker_time_in_sec':
                // Default is just a bit under the .7 max
                return min(
                    floor(0.7 * SnapUtil::phpIniGet("max_execution_time", 30, 'int')),
                    DUP_PRO_Constants::DEFAULT_MAX_WORKER_TIME
                );
            case 'crypt':
                $test_str      = 'aaa';
                $encrypted_str = CryptBlowfish::encrypt($test_str);
                $decrypted_str = CryptBlowfish::decrypt($encrypted_str);
                return ($test_str == $decrypted_str) ? true : false;
            case 'custom_ajax_url':
                return admin_url('admin-ajax.php');
        }
        return $val;
    }

    /**
     * Reset default values
     *
     * @return bool
     */
    public function resetUserSettings()
    {
        try {
            $result = $this->reset(
                [
                    'manual_mode_storage_ids',
                    'license_status',
                    'license_expiration_time',
                    'license_no_activations_left',
                    'license_key_visible',
                    'lkp',
                    'license_type',
                    'license_limit',
                    'last_system_check_timestamp',
                    'initial_activation_timestamp'
                ],
                [__CLASS__, 'getDefaultPropInitVal'],
                function () {
                    $this->set_build_mode();
                }
            );

            if ($result == false) {
                throw new Exception('Can\'t reset global entity values');
            }

            $sglobal = DUP_PRO_Secure_Global_Entity::getInstance();
            if ($sglobal->save() == false) {
                throw new Exception('Can\'t save secure global');
            }
        } catch (Exception $e) {
            DUP_PRO_Log::traceError('Reset user settings error mrg: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Return recovery custom path
     *
     * @return string
     */
    public function getRecoveryCustomPath()
    {
        return $this->recoveryCustomPath;
    }

    /**
     * Return recovery custom URL
     *
     * @return string return empty URL if custom path isn't set
     */
    public function getRecoveryCustomURL()
    {
        if (strlen($this->recoveryCustomPath) == 0) {
            return '';
        }

        if (SnapIO::isChildPath($this->recoveryCustomPath, DUP_PRO_Archive::getArchiveListPaths('wpcontent'), false, true, true)) {
            $mainPath = DUP_PRO_Archive::getArchiveListPaths('wpcontent');
            $mainURL  = DUP_PRO_Archive::getOriginalUrls('wpcontent');
        } else {
            $mainPath = DUP_PRO_Archive::getArchiveListPaths('home');
            $mainURL  = DUP_PRO_Archive::getOriginalUrls('home');
        }

        return $mainURL . '/' . SnapIo::getRelativePath($this->recoveryCustomPath, $mainPath, true);
    }

    /**
     * Set recovery custom path
     *
     * @param string $path
     * @param string $failMessage
     *
     * @return bool
     */
    public function setRecoveryCustomPath($path, &$failMessage = '')
    {
        $remove = false;

        try {
            $this->recoveryCustomPath = '';

            if (strlen($path) == 0) {
                return true;
            }

            if (file_exists($path)) {
                if (
                    !is_dir($path) ||
                    !is_writable($path)
                ) {
                    throw new Exception(__('The Recovery custom path must be a folder with write permissions.', 'duplicator-pro'));
                }
            } else {
                if (wp_mkdir_p($path) == false) {
                    throw new Exception(sprintf(__('It is not possible to create the folder %s', 'duplicator-pro'), $path));
                }
            }

            if (
                !SnapIO::isChildPath($path, DUP_PRO_Archive::getArchiveListPaths('home'), false, false, true) &&
                !SnapIO::isChildPath($path, DUP_PRO_Archive::getArchiveListPaths('wpcontent'), false, false, true)
            ) {
                throw new Exception(__('The custom Recovery path must be a child folder of the home path or wp-content', 'duplicator-pro'));
            }

            if (PathUtil::isPathInCoreDirs($path)) {
                throw new Exception(__('The Recovery custom path cannot be a wordpress core folder.', 'duplicator-pro'));
            }
        } catch (Exception $e) {
            $remove      = true;
            $failMessage = $e->getMessage();
            return false;
        } finally {
            if ($remove) {
                rmdir($path);
            }
        }

        $this->recoveryCustomPath = $path;
        return true;
    }

    /**
     * Update global settings after install
     *
     * @return bool true on success false on failure
     */
    public function updateAftreInstall()
    {
        $this->lock_mode     = DUP_PRO_Global_Entity::getDefaultLockType();
        $this->ajax_protocol = DUPLICATOR_PRO_DEFAULT_AJAX_PROTOCOL;
        if ($this->getBuildMode() !== DUP_PRO_Archive_Build_Mode::DupArchive) {
            $this->set_build_mode();
        }
        return $this->save();
    }

    /**
     * Return default lock
     *
     * @return int Enum lock type
     */
    protected static function getDefaultLockType()
    {
        $lockType = DUP_PRO_Thread_Lock_Mode::Flock;

        if (DUP_PRO_U::getSqlLock(DUPLICATOR_PRO_TEST_SQL_LOCK_NAME)) {
            $lockType = (DUP_PRO_U::checkSqlLock(DUPLICATOR_PRO_TEST_SQL_LOCK_NAME) ? DUP_PRO_Thread_Lock_Mode::SQL_Lock : DUP_PRO_Thread_Lock_Mode::Flock);
            DUP_PRO_U::releaseSqlLock(DUPLICATOR_PRO_TEST_SQL_LOCK_NAME);
        }
        DUP_PRO_Log::trace("Lock type auto set to {$lockType}");
        return $lockType;
    }

    /**
     * Set from object
     *
     * @param self $global_data
     *
     * @return void
     */
    public function setFromImportData(self $global_data)
    {
        $reflect = new ReflectionClass(self::class);
        $props   = $reflect->getProperties();

        $skipProps = [
            'id',
            'license_status',
            'license_expiration_time',
            'license_no_activations_left',
            'last_system_check_timestamp',
            'initial_activation_timestamp',
            'manual_mode_storage_ids',
            'license_key_visible',
            'lkp'
        ];

        foreach ($props as $prop) {
            if (in_array($prop->getName(), $skipProps)) {
                continue;
            }
            $prop->setAccessible(true);
            $prop->setValue($this, $prop->getValue($global_data));
        }
    }

    /**
     * Check if build mode is available
     *
     * @param int $buildMode build mode constant
     *
     * @return bool
     */
    public static function isBuildModeAvaiable($buildMode)
    {
        switch ($buildMode) {
            case DUP_PRO_Archive_Build_Mode::Unconfigured:
                return false;
            case DUP_PRO_Archive_Build_Mode::Shell_Exec:
                return (DUP_PRO_Zip_U::getShellExecZipPath() != null);
            case DUP_PRO_Archive_Build_Mode::ZipArchive:
                return ZipArchiveExtended::isPhpZipAvaiable();
            case DUP_PRO_Archive_Build_Mode::DupArchive:
                return true;
            default:
                throw new Exception('Invalid engine');
        }
    }

    /**
     * Return package build mode
     *
     * @return int Return enum DUP_PRO_Archive_Build_Mode
     */
    public function getBuildMode()
    {
        $archive_build_mode = $this->archive_build_mode;

        switch ($archive_build_mode) {
            case DUP_PRO_Archive_Build_Mode::Unconfigured:
                if (self::isBuildModeAvaiable(DUP_PRO_Archive_Build_Mode::Shell_Exec)) {
                    $archive_build_mode = DUP_PRO_Archive_Build_Mode::Shell_Exec;
                } elseif (self::isBuildModeAvaiable(DUP_PRO_Archive_Build_Mode::ZipArchive)) {
                    $archive_build_mode = DUP_PRO_Archive_Build_Mode::ZipArchive;
                } else {
                    $archive_build_mode = DUP_PRO_Archive_Build_Mode::DupArchive;
                }
                break;
            case DUP_PRO_Archive_Build_Mode::Shell_Exec:
                if (!self::isBuildModeAvaiable(DUP_PRO_Archive_Build_Mode::Shell_Exec)) {
                    if (self::isBuildModeAvaiable(DUP_PRO_Archive_Build_Mode::ZipArchive)) {
                        $archive_build_mode = DUP_PRO_Archive_Build_Mode::ZipArchive;
                    } else {
                        $archive_build_mode = DUP_PRO_Archive_Build_Mode::DupArchive;
                    }
                }
                break;
            case DUP_PRO_Archive_Build_Mode::ZipArchive:
                if (!self::isBuildModeAvaiable(DUP_PRO_Archive_Build_Mode::ZipArchive)) {
                    if (self::isBuildModeAvaiable(DUP_PRO_Archive_Build_Mode::Shell_Exec)) {
                        $archive_build_mode = DUP_PRO_Archive_Build_Mode::Shell_Exec;
                    } else {
                        $archive_build_mode = DUP_PRO_Archive_Build_Mode::DupArchive;
                    }
                }
                break;
            case DUP_PRO_Archive_Build_Mode::DupArchive:
                break;
            default:
                throw new Exception('Invalid engine');
        }
        return $archive_build_mode;
    }

    /**
     * Selt build mode and return it
     *
     * @return int Return enum DUP_PRO_Archive_Build_Mode
     */
    public function set_build_mode()
    {
        $archive_build_mode = $this->getBuildMode();

        $this->archive_build_mode = apply_filters('duplicator_pro_default_archive_build_mode', $archive_build_mode);
        return $this->archive_build_mode;
    }

    /**
     *
     * @return int microsenconds
     */
    public function getMicrosecLoadReduction()
    {
        return DUP_PRO_Server_Load_Reduction::microseconds_from_reduction($this->server_load_reduction);
    }

    /**
     * set db mode ant all related params.
     * check mysqldump
     *
     * @param null|string $dbMode               if null get INPUT_POST
     * @param null|int    $phpDumpMode          if null get INPUT_POST
     * @param null|int    $dbPhpQueryLimit      if null get INPUT_POST
     * @param null|string $packageMysqldumpPath if null get INPUT_POST
     */
    public function setDbMode($dbMode = null, $phpDumpMode = null, $dbPhpQueryLimit = null, $packageMysqldumpPath = null, $dbMysqlDumpQueryLimit = null)
    {
        //DATABASE
        $dbMode                = is_null($dbMode) ? SnapUtil::filterInputDefaultSanitizeString(INPUT_POST, '_package_dbmode') : $dbMode;
        $phpDumpMode           = is_null($phpDumpMode) ? filter_input(
            INPUT_POST,
            '_phpdump_mode',
            FILTER_VALIDATE_INT,
            array('options' => array(
                        'default'   => 0,
                        'min_range' => 0,
                        'max_range' => 1
                    )
                )
        ) : $phpDumpMode;
        $dbMysqlDumpQueryLimit = is_null($dbMysqlDumpQueryLimit) ? filter_input(
            INPUT_POST,
            '_package_mysqldump_qrylimit',
            FILTER_VALIDATE_INT,
            array(
                    'options' => array(
                        'default'   => DUP_PRO_Constants::DEFAULT_MYSQL_DUMP_CHUNK_SIZE,
                        'min_range' => DUP_PRO_Constants::MYSQL_DUMP_CHUNK_SIZE_MIN_LIMIT,
                        'max_range' => DUP_PRO_Constants::MYSQL_DUMP_CHUNK_SIZE_MAX_LIMIT
                    )
                )
        ) : $dbMysqlDumpQueryLimit;

        $packageMysqldumpPath = is_null($packageMysqldumpPath) ?
            SnapUtil::filterInputDefaultSanitizeString(INPUT_POST, '_package_mysqldump_path') :
            $packageMysqldumpPath;
        $packageMysqldumpPath = SnapUtil::sanitizeNSCharsNewlineTabs($packageMysqldumpPath);
        $packageMysqldumpPath = preg_match('/^([A-Za-z]\:)?[\/\\\\]/', $packageMysqldumpPath) ? $packageMysqldumpPath : '';
        $packageMysqldumpPath = preg_replace('/[\'"]/m', '', $packageMysqldumpPath);
        $packageMysqldumpPath = SnapIO::safePathUntrailingslashit($packageMysqldumpPath);

        $mysqlDumpPath = empty($packageMysqldumpPath) ? DUP_PRO_DB::getMySqlDumpPath() : $packageMysqldumpPath;
        if ($dbMode == 'mysql' && empty($mysqlDumpPath)) {
            $dbMode = 'php';
        }

        $this->package_mysqldump          = ($dbMode == 'mysql');
        $this->package_phpdump_mode       = $phpDumpMode;
        $this->package_mysqldump_path     = $packageMysqldumpPath;
        $this->package_mysqldump_qrylimit = $dbMysqlDumpQueryLimit;

        array_map(function ($option) {
            $option->update();
        }, $this->getMysqldumpOptions());
    }

    /**
     * Sets cleanup fields and configures WP Cron accordingly
     *
     * @param int    $cleanup_mode
     * @param string $cleanup_email
     * @param int    $auto_cleanup_hours
     *
     * @return void
     */
    public function setCleanupFields($cleanup_mode = null, $cleanup_email = null, $auto_cleanup_hours = null)
    {
        $this->cleanup_mode = is_null($cleanup_mode) ? filter_input(
            INPUT_POST,
            'cleanup_mode',
            FILTER_VALIDATE_INT,
            array(
                'options' => array(
                    'default'   => self::CLEANUP_MODE_OFF,
                    'min_range' => 0,
                    'max_range' => 2
                )
            )
        ) : $cleanup_mode;

        $this->cleanup_email = is_null($cleanup_email) ? $_REQUEST['cleanup_email'] : $cleanup_email;

        $this->auto_cleanup_hours = is_null($auto_cleanup_hours) ? filter_input(
            INPUT_POST,
            'auto_cleanup_hours',
            FILTER_VALIDATE_INT,
            array(
                'options' => array(
                    'default'   => 24,
                    'min_range' => 1
                )
            )
        ) : $auto_cleanup_hours;

        self::cleanupScheduleSetup();
    }

    /**
     * Schedules cron event for installer files cleanup purposes,
     * and unschedules it if it's not needed anymore.
     *
     * @return void
     */
    public static function cleanupScheduleSetup()
    {
        $global = self::getInstance();
        SnapWP::unscheduleEvent(self::CLEANUP_HOOK);
        if ($global->cleanup_mode == self::CLEANUP_MODE_MAIL) {
            $nextRunTime = time() + self::CLEANUP_EMAIL_NOTICE_INTERVAL * 3600;
            SnapWP::scheduleEvent($nextRunTime, self::CLEANUP_INTERVAL_NAME, self::CLEANUP_HOOK);
        } elseif ($global->cleanup_mode == self::CLEANUP_MODE_AUTO) {
            $nextRunTime = time() + $global->auto_cleanup_hours * 3600;
            SnapWP::scheduleEvent($nextRunTime, self::CLEANUP_INTERVAL_NAME, self::CLEANUP_HOOK);
        }
    }

    /**
     * Customizes schedules according to current cleanup_mode. If necessary, it
     * adds a custom cron schedule that will run every N hours.
     *
     * @param array $schedules An array of non-default cron schedules.
     *
     * @return array Filtered array of non-default cron schedules.
     */
    public static function customCleanupCronInterval($schedules)
    {
        $global = self::getInstance();

        switch ($global->cleanup_mode) {
            case self::CLEANUP_MODE_OFF:
                // No need to modify anything
                break;
            case self::CLEANUP_MODE_MAIL:
                $schedules[self::CLEANUP_INTERVAL_NAME] = array(
                    'interval' => self::CLEANUP_EMAIL_NOTICE_INTERVAL * 3600, // In seconds, every N hours
                    'display'  => sprintf(esc_html__('Every %1$d hours'), self::CLEANUP_EMAIL_NOTICE_INTERVAL)
                );
                break;
            case self::CLEANUP_MODE_AUTO:
                $schedules[self::CLEANUP_INTERVAL_NAME] = array(
                    'interval' => $global->auto_cleanup_hours * 3600, // In seconds, every N hours
                    'display'  => sprintf(esc_html__('Every %1$d hours'), $global->auto_cleanup_hours)
                );
                break;
            default:
                throw new Exception('Invalid cleanup mode:' . SnapLog::v2str($global->cleanup_mode));
        }
        return $schedules;
    }

    /**
     * The function that gets executed by WP Cron for cleanup of installer files.
     * It does different tasks based on current cleanup_mode setting.
     *
     * @return void
     */
    public static function cleanupCronJob()
    {
        $global = self::getInstance();

        $websiteUrl = SnapURL::getCurrentUrl(false, false, 1);
        $to         = $global->cleanup_email;
        if (empty($to)) {
            $to = get_option('admin_email');
        }

        switch ($global->cleanup_mode) {
            case self::CLEANUP_MODE_MAIL:
                // Email Notice cron job routine for cleanup of installer files
                $listOfInstallerFiles = MigrationMng::checkInstallerFilesList();
                $filesToRemove        = array();

                foreach ($listOfInstallerFiles as $path) {
                    if (time() - filectime($path) > self::CLEANUP_FILE_TIME_DELAY) {
                        $filesToRemove[] = $path;
                    }
                }

                if (count($filesToRemove) > 0 && !empty($to)) {
                    // Send an Email Notice
                    $subject  = __("Action required", 'duplicator-pro');
                    $message  = sprintf(__('This email is sent by your Wordpress plugin "Duplicator Pro" from website: %1$s. ', 'duplicator-pro'), $websiteUrl);
                    $message .= __('You received this email because Cleanup mode is set to "Email Notice". ', 'duplicator-pro');
                    $message .= __('Cleanup routine discovered that some installer files (leftovers from migration) were not removed. ', 'duplicator-pro');
                    $message .= __('We strongly advise you to remove these files. ', 'duplicator-pro');
                    $message .= __('Here is the list of files found on your website that you should remove:', 'duplicator-pro') . "<br/>";
                    foreach ($filesToRemove as $path) {
                        $message .= "-> $path<br/>";
                    }
                    $message .= "<br/>";
                    $message .= __('Note: You could enable "Auto Cleanup" mode if you go to:', 'duplicator-pro') . "<br/>";
                    $message .= __('WordPress Admin > Duplicator Pro > Settings > Packages Tab > Cleanup.', 'duplicator-pro') . "<br/>";
                    $message .= __('That mode will do cleanup of those files automatically for you.', 'duplicator-pro') . "<br/>";
                    $message .= "<br/>";
                    $message .= __('Best regards,', 'duplicator-pro') . "<br/>";
                    $message .= __('Duplicator Pro', 'duplicator-pro');

                    if (wp_mail($to, $subject, $message, array('Content-Type: text/html; charset=UTF-8'))) {
                        // OK
                        \DUP_PRO_Log::trace('wp_mail sent email notice regarding cleanup of installer files');
                    } else {
                        \DUP_PRO_Log::trace("Problem sending email notice regarding cleanup of installer files to {$to}");
                    }
                }
                break;
            case self::CLEANUP_MODE_AUTO:
                // Auto Cleanup cron job routine for cleanup of installer files
                $installerFiles = MigrationMng::cleanMigrationFiles(false, self::CLEANUP_FILE_TIME_DELAY);
                if (count($installerFiles) == 0) {
                    // No installer files were found, so we do nothing else
                    return;
                }

                $filesFailedRemoval = array();
                foreach ($installerFiles as $path => $success) {
                    if (!$success) {
                        $filesFailedRemoval[] = $path;
                    }
                }
                if (count($filesFailedRemoval) == 0) {
                    // All found installer files were removed successfully,
                    // or they did not even need to be removed yet because of CLEANUP_FILE_TIME_DELAY
                    return;
                }

                // If this is executed that means that some of installer files
                // could not be removed for some reason (permission issues?)
                if (!empty($to)) {
                    // Send an Email Notice about files that could not be removed during auto cleanup
                    $subject  = __("Action required", 'duplicator-pro');
                    $message  = sprintf(__('This email is sent by your Wordpress plugin "Duplicator Pro" from website: %1$s. ', 'duplicator-pro'), $websiteUrl);
                    $message .= __('"Auto Cleanup" mode is ON, ', 'duplicator-pro');
                    $message .= __('however cleanup routine discovered that some installer files (leftovers from migration) could not be removed. ', 'duplicator-pro');
                    $message .= __('We strongly advise you to remove those files manually. ', 'duplicator-pro');
                    $message .= __('Here is the list of files found on your website that you should remove:', 'duplicator-pro') . "<br/>";
                    foreach ($filesFailedRemoval as $path) {
                        $message .= "-> $path<br/>";
                    }
                    $message .= "<br/>";
                    $message .= __('Those files probably could not be removed due to permission issues. ', 'duplicator-pro');
                    $message .= sprintf(
                        __('You can find more info in FAQ %1$son this link%2$s.', 'duplicator-pro'),
                        "<a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-perms-100-q' target='_blank'>",
                        "</a>"
                    ) . "<br/>";
                    $message .= "<br/>";
                    $message .= __('Note: To edit "Cleanup" settings go to:', 'duplicator-pro') . "<br/>";
                    $message .= __('WordPress Admin > Duplicator Pro > Settings > Packages Tab > Cleanup.', 'duplicator-pro') . "<br/>";
                    $message .= "<br/>";
                    $message .= __('Best regards,', 'duplicator-pro') . "<br/>";
                    $message .= __('Duplicator Pro', 'duplicator-pro');

                    if (wp_mail($to, $subject, $message, array('Content-Type: text/html; charset=UTF-8'))) {
                        // OK
                        \DUP_PRO_Log::trace('wp_mail sent email notice regarding failed auto cleanup of installer files');
                    } else {
                        \DUP_PRO_Log::trace("Problem sending email notice regarding failed auto cleanup of installer files to {$to}");
                    }
                }
                break;
            case self::CLEANUP_MODE_OFF:
            default:
                break;
        }
    }

    public function setArchiveMode($archiveBuildMode = null, $zipArchiveMode = null, $archiveCompression = null, $ziparchiveValidation = null, $ziparchiveChunkSizeInMb = null)
    {
        $isZipAvailable = (DUP_PRO_Zip_U::getShellExecZipPath() != null);

        $prelimBuildMode = is_null($archiveBuildMode) ? filter_input(
            INPUT_POST,
            'archive_build_mode',
            FILTER_VALIDATE_INT,
            array(
                'options' => array(
                    'min_range' => 1,
                    'max_range' => 3
                )
            )
        ) : $archiveBuildMode;

        // Something has changed which invalidates Shell exec so move it to ZA
        $this->archive_build_mode = (!$isZipAvailable && ($prelimBuildMode == DUP_PRO_Archive_Build_Mode::Shell_Exec)) ? DUP_PRO_Archive_Build_Mode::ZipArchive : $prelimBuildMode;
        $this->ziparchive_mode    = is_null($zipArchiveMode) ? filter_input(
            INPUT_POST,
            'ziparchive_mode',
            FILTER_VALIDATE_INT,
            array(
                'options' => array(
                    'default'   => 0,
                    'min_range' => 0,
                    'max_range' => 1
                )
            )
        ) : $zipArchiveMode;

        $this->archive_compression         = is_null($archiveCompression) ? filter_input(INPUT_POST, 'archive_compression', FILTER_VALIDATE_BOOLEAN) : $archiveCompression;
        $this->ziparchive_validation       = is_null($ziparchiveValidation) ? filter_input(INPUT_POST, 'ziparchive_validation', FILTER_VALIDATE_BOOLEAN) : $ziparchiveValidation;
        $this->ziparchive_chunk_size_in_mb = is_null($ziparchiveChunkSizeInMb) ? filter_input(
            INPUT_POST,
            'ziparchive_chunk_size_in_mb',
            FILTER_VALIDATE_INT,
            array(
                'options' => array(
                    'default'   => DUP_PRO_Constants::DEFAULT_ZIP_ARCHIVE_CHUNK,
                    'min_range' => 1
                )
            )
        ) : $ziparchiveChunkSizeInMb;
    }

    public function setClientsideKickoff($enable)
    {
        if ($this->clientside_kickoff != $enable) {
            $this->clientside_kickoff = $enable;

            if ($this->clientside_kickoff) {
                // Auto setting the max package runtime in case of client kickoff is turned on and
                // the max package runtime is less than 480 minutes - 8 hours
                $this->max_package_runtime_in_min = max(480, $this->max_package_runtime_in_min);
                $this->setDbMode('mysql');

                // RSR 4/29/19 not setting archive mode for now - too risky
                // $mode = (DUP_PRO_Zip_U::getShellExecZipPath() != null) ? DUP_PRO_Archive_Build_Mode::Shell_Exec : DUP_PRO_Archive_Build_Mode::DupArchive;
                // $this->setArchiveMode($mode);
            }
        }
    }

    /**
     * Change settings that may need to be changed because we have restored to a different system
     *
     * @return void
     */
    public function adjust_settings_for_system()
    {
        /** @todo future fathures */
    }

    public function configure_dropbox_transfer_mode()
    {
        if ($this->dropbox_transfer_mode == DUP_PRO_Dropbox_Transfer_Mode::Unconfigured) {
            $has_curl      = DUP_PRO_Server::isCurlEnabled();
            $has_fopen_url = DUP_PRO_Server::isURLFopenEnabled();

            if ($has_curl) {
                $this->dropbox_transfer_mode = DUP_PRO_Dropbox_Transfer_Mode::cURL;
            } else {
                if ($has_fopen_url) {
                    $this->dropbox_transfer_mode = DUP_PRO_Dropbox_Transfer_Mode::FOpen_URL;
                } else {
                    $this->dropbox_transfer_mode = DUP_PRO_Dropbox_Transfer_Mode::Disabled;
                }
            }

            $this->save();
        }
    }

    public function get_installer_backup_filename()
    {
        $installer_extension = $this->get_installer_extension();

        if (trim($installer_extension) == '') {
            return 'installer-backup';
        } else {
            return "installer-backup.$installer_extension";
        }
    }

    public function get_installer_extension()
    {
        return pathinfo($this->installer_base_name, PATHINFO_EXTENSION);
    }

    public function get_archive_engine()
    {
        $mode = '';
        switch ($this->archive_build_mode) {
            case DUP_PRO_Archive_Build_Mode::ZipArchive:
                $mode = ($this->ziparchive_mode == DUP_PRO_ZipArchive_Mode::Multithreaded) ? DUP_PRO_U::__("ZipArchive: multi-thread") : DUP_PRO_U::__("ZipArchive: single-thread");
                break;

            case DUP_PRO_Archive_Build_Mode::DupArchive:
                $mode = DUP_PRO_U::__('DupArchive');
                break;

            default:
                $mode = DUP_PRO_U::__("Shell Zip");
                break;
        }

        return $mode;
    }

    public function get_archive_extension_type()
    {
        $mode = 'zip';
        if ($this->archive_build_mode == DUP_PRO_Archive_Build_Mode::DupArchive) {
            $mode = 'daf';
        }
        return $mode;
    }

    /**
     * Return Mysqldump options
     *
     * @return GroupOptions[]
     */
    public function getMysqldumpOptions()
    {
        return $this->packageMysqldumpOptions;
    }
}
