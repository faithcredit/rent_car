<?php

/**
 * Class to import archive
 *
 * Standard: PSR-2 (almost)
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    DUP_PRO
 * @subpackage classes/package
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      1.0.0
 *
 * @notes: Trace process time
 *  $timer01 = DUP_PRO_U::getMicrotime();
 *  DUP_PRO_Log::trace("SCAN TIME-B = " . DUP_PRO_U::elapsedTime(DUP_PRO_U::getMicrotime(), $timer01));
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Package\Recovery\RecoveryStatus;
use Duplicator\Utils\PHPExecCheck;


class DUP_PRO_Package_Recover extends DUP_PRO_Package_Importer
{
    const MAX_PACKAGES_LIST         = 50;
    const OPTION_RECOVER_PACKAGE_ID = 'duplicator_pro_recover_point';
    const OUT_TO_HOURS_LIMIT        = 43200; // Seconds in 12 hours

    /** @var ?array<int, array{id: int, created: string, nameHash: string, name: string}> */
    protected static $recoveablesPackages = null;
    /** @var ?self */
    protected static $instance = null;
    /** @var DUP_PRO_Package */
    protected $package = null;

    /**
     * This constructor should be protected but I can't change visibility before php 7.3 so I have to leave it public..
     * Use getRecoverPackage to take a recover object. Don't init it directly
     *
     * @param string          $path    Archiv path
     * @param DUP_PRO_Package $package Recovery package
     */
    public function __construct($path, DUP_PRO_Package $package)
    {
        $this->package    = $package;
        $this->archivePwd = $this->package->Archive->getArchivePassword();
        parent::__construct($path);
    }

    /**
     *
     * @return int
     */
    public function getPackageId()
    {
        return $this->package->ID;
    }

    /**
     * Return package life
     *
     * @param string $type can be hours,human,timestamp
     *
     * @return int|string package life in hours, timestamp or human readable format
     */
    public function getPackageLife($type = 'timestamp')
    {
        $created = strtotime($this->getCreated());
        $current = strtotime(gmdate("Y-m-d H:i:s"));
        $delta   = $current - $created;

        switch ($type) {
            case 'hours':
                return max(0, floor($delta / 60 / 60));
            case 'human':
                return human_time_diff($created, $current);
            case 'timestamp':
            default:
                return $delta;
        }
    }

    /**
     * This function check if package is importable from scan info
     *
     * @param string $failMessage message if isn't importable
     *
     * @return bool
     */
    public function isImportable(&$failMessage = null)
    {
        if (parent::isImportable($failMessage) === false) {
            return false;
        }

        //The scan logic is going to be refactored, so only use info from the scan.json, if it's too complex to use the
        // archive config info
        if ($this->scan->ARC->Status->HasFilteredCoreFolders) {
            $failMessage = DUP_PRO_U::__('The package is missing WordPress core folder(s)! ' .
                'It must include wp-admin, wp-content, wp-includes, uploads, plugins, and themes folders.');
            return false;
        }

        if ($this->info->mu_mode !== 0 && $this->info->mu_is_filtered) {
            $failMessage = DUP_PRO_U::__('The package is missing some subsites.');
            return false;
        }

        if ($this->info->dbInfo->tablesBaseCount != $this->info->dbInfo->tablesFinalCount) {
            $failMessage = DUP_PRO_U::__('The package is missing some of the site tables.');
            return false;
        }

        $failMessage = '';
        return true;
    }

    /**
     *
     * @return bool
     */
    public function isOutToDate()
    {
        return $this->getPackageLife() > self::OUT_TO_HOURS_LIMIT;
    }

    /**
     * Return installer folder path
     *
     * @return string|false false if impossibile exec the installer
     */
    public function getInstallerFolderPath()
    {
        switch ($this->getPathMode()) {
            case self::PATH_MODE_BACKUP:
                return DUPLICATOR_PRO_PATH_RECOVER;
            case self::PATH_MODE_CUSTOM:
                return DUP_PRO_Global_Entity::getInstance()->getRecoveryCustomPath();
            case self::PATH_MODE_BRIDGE:
            case self::PATH_MODE_HOME:
            case self::PATH_MODE_CLASSIC:
            case self::PATH_MODE_NONE:
            default:
                return false;
        }
    }

    /**
     * Return installer filder url
     *
     * @return string|false false if impossibile exec the installer
     */
    public function getInstallerFolderUrl()
    {
        switch ($this->getPathMode()) {
            case self::PATH_MODE_BACKUP:
                return DUPLICATOR_PRO_URL_RECOVER;
            case self::PATH_MODE_CUSTOM:
                return DUP_PRO_Global_Entity::getInstance()->getRecoveryCustomURL();
            case self::PATH_MODE_BRIDGE:
            case self::PATH_MODE_HOME:
            case self::PATH_MODE_CLASSIC:
            case self::PATH_MODE_NONE:
            default:
                return false;
        }
    }

    /**
     * return true if path have a recovery point sub path
     *
     * @param string $path
     *
     * @return boolean
     */
    public static function isRecoverPath($path)
    {
        return (preg_match('/[\/]' . preg_quote(DUPLICATOR_PRO_SSDIR_NAME, '/') . '[\/]' . preg_quote(DUPLICATOR_PRO_RECOVER_DIR_NAME, '/') . '[\/]/', $path) === 1);
    }

    /**
     * Return installer link
     *
     * @return string
     */
    public function getInstallLink()
    {
        $queryStr = http_build_query(array(
            'archive'    => dirname($this->archive),
            'dup_folder' => 'dup-installer-' . $this->info->packInfo->secondaryHash
        ));
        return $this->getInstallerFolderUrl() . '/' . $this->getInstallerName() . '?' . $queryStr;
    }

    /**
     * Get HTML launcher fil name
     *
     * @return string
     */
    public function getLauncherFileName()
    {

        $parseUrl     = SnapURL::parseUrl(get_home_url());
        $siteFileName = str_replace(array(':', '\\', '/', '.'), '_', $parseUrl['host'] . $parseUrl['path']);
        sanitize_file_name($siteFileName);

        return 'recover_' . sanitize_file_name($siteFileName) . '_' . date("Ymd_His", strtotime($this->getCreated())) . '.html';
    }

    /**
     * Return overwrite param for recovery
     *
     * @return array<string, array{value: mixed, formStatus?: string}>
     */
    public function getOverwriteParams()
    {
        $params        = parent::getOverwriteParams();
        $updDirs       = wp_upload_dir();
        $recoverParams = array(
            'template'        => array(
                'value' => 'recovery',
            ),
            'recovery-link'   => array(
                'value' => '',
            ),
            'restore-backup'  => array(
                'value'      => true,
                'formStatus' => 'st_infoonly'
            ),
            'archive_action'  => array(
                'value'      => 'removewpfiles',
                'formStatus' => 'st_infoonly'
            ),
            'url_new'         => array(
                'value'      => DUP_PRO_Archive::getOriginalUrls('home'),
                'formStatus' => 'st_infoonly'
            ),
            'path_new'        => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('home'),
                'formStatus' => 'st_infoonly'
            ),
            'siteurl'         => array(
                'value'      => site_url(),
                'formStatus' => 'st_infoonly'
            ),
            'path_core_new'   => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('abs'),
                'formStatus' => 'st_infoonly'
            ),
            'url_cont_new'    => array(
                'value'      => content_url(),
                'formStatus' => 'st_infoonly'
            ),
            'path_cont_new'   => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('wpcontent'),
                'formStatus' => 'st_infoonly'
            ),
            'url_upl_new'     => array(
                'value'      => $updDirs['baseurl'],
                'formStatus' => 'st_infoonly'
            ),
            'path_upl_new'    => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('uploads'),
                'formStatus' => 'st_infoonly'
            ),
            'url_plug_new'    => array(
                'value'      => plugins_url(),
                'formStatus' => 'st_infoonly'
            ),
            'path_plug_new'   => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('plugins'),
                'formStatus' => 'st_infoonly'
            ),
            'url_muplug_new'  => array(
                'value'      => WPMU_PLUGIN_URL,
                'formStatus' => 'st_infoonly'
            ),
            'path_muplug_new' => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('muplugins'),
                'formStatus' => 'st_infoonly'
            )
        );
        return array_merge($params, $recoverParams);
    }

    /**
     * Init recovery package by id
     *
     * @param int $packageId
     *
     * @return boolean|self
     */
    protected static function getInitRecoverPackageById($packageId)
    {
        try {
            if (!($package = DUP_PRO_Package::get_by_id($packageId))) {
                throw new Exception('Invalid packag id');
            }

            if (($archivePath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive)) == false) {
                throw new Exception('Archive file not found');
            }

            $result = new self($archivePath, $package);
        } catch (Exception $e) {
            DUP_PRO_Log::trace('ERROR ON RECOVER PACKAGE ID, msg:' . $e->getMessage());
            return false;
        }

        return $result;
    }

    /**
     *
     * @param boolean $reset
     *
     * @return false|self return false if recover package isn't set or recover package object
     */
    public static function getRecoverPackage($reset = false)
    {
        if (is_null(self::$instance) || $reset) {
            if (($packageId = get_option(self::OPTION_RECOVER_PACKAGE_ID)) == false) {
                self::$instance = null;
                return false;
            }

            if (!self::isPackageIdRecoveable($packageId, $reset)) {
                self::$instance = null;
                return false;
            }

            self::$instance = self::getInitRecoverPackageById($packageId);
        }

        return self::$instance;
    }

    /**
     *
     * @return boolean|int return false if not set or package id
     */
    public static function getRecoverPackageId()
    {
        if (DUP_PRO_CTRL_recovery::isDisallow()) {
            return false;
        }

        $recoverPackage = DUP_PRO_Package_Recover::getRecoverPackage();
        if ($recoverPackage instanceof DUP_PRO_Package_Recover) {
            return $recoverPackage->getPackageId();
        } else {
            return false;
        }
    }

    /**
     * Reset recovery package
     *
     * @param bool $emptyDir if true remove recovery package files
     *
     * @return void
     */
    public static function resetRecoverPackage($emptyDir = false)
    {
        self::$instance = null;
        if ($emptyDir) {
            static::cleanFolder();
        }
        delete_option(self::OPTION_RECOVER_PACKAGE_ID);
    }

    /**
     * Set recoveable package
     *
     * @param false|int $id if empty reset package
     * @param ?string $errorMessage error message
     *
     * @return bool false if fail
     */
    public static function setRecoveablePackage($id, &$errorMessage = null)
    {
        $id = (int) $id;

        self::resetRecoverPackage(true);

        if (empty($id)) {
            return true;
        }

        try {
            if (!self::isPackageIdRecoveable($id, true)) {
                throw new Exception('Package isn\'t in recoverable list');
            }

            $recoverPackage = self::getInitRecoverPackageById($id);
            if (!$recoverPackage instanceof DUP_PRO_Package_Recover) {
                throw new Exception('Can\'t initialize recovery package');
            }

            if (!SnapIO::mkdir($recoverPackage->getInstallerFolderPath(), 0755, true)) {
                throw new Exception('Can\'t create recovery package folder or set its permissions to 0755');
            }

            // Checks if php is executable in the recover folder
            $path     = $recoverPackage->getInstallerFolderPath();
            $url      = $recoverPackage->getInstallerFolderUrl();
            $phpCheck = new PHPExecCheck($path, $url);
            if ($phpCheck->check() != PHPExecCheck::PHP_OK) {
                throw new Exception($phpCheck->getLastError());
            }

            $recoverPackage->prepareToInstall();

            if (!update_option(self::OPTION_RECOVER_PACKAGE_ID, $id)) {
                delete_option(self::OPTION_RECOVER_PACKAGE_ID);
                throw new Exception('Can\'t update ' . self::OPTION_RECOVER_PACKAGE_ID . ' option');
            }
        } catch (Exception $e) {
            delete_option(self::OPTION_RECOVER_PACKAGE_ID);
            $errorMessage = $e->getMessage();
            return false;
        } catch (Error $e) {
            delete_option(self::OPTION_RECOVER_PACKAGE_ID);
            $errorMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     *
     * @param bool $removeArchive not used, always removes the archives in the recovery folder
     *
     * @return bool
     */
    public static function cleanFolder($removeArchive = false)
    {
        $customFolder = DUP_PRO_Global_Entity::getInstance()->getRecoveryCustomPath();
        if (strlen($customFolder) > 0) {
            $path = $customFolder;
        } else {
            $path = DUPLICATOR_PRO_PATH_RECOVER;
        }

        if (!file_exists($path) && !wp_mkdir_p($path)) {
            throw new Exception('Can\'t create ' . $path);
        }
        SnapIO::emptyDir($path);

        return true;
    }

    /**
     * Get error message if installer path couldn't be determined
     *
     * @return string
     */
    protected static function getNotExecPhpErrorMessage()
    {
        $customFolder = DUP_PRO_Global_Entity::getInstance()->getRecoveryCustomPath();
        if (strlen($customFolder) > 0) {
            $path = $customFolder;
        } else {
            $path = DUPLICATOR_PRO_PATH_RECOVER;
        }

        return sprintf(
            __(
                'Duplicator cannot set Recovery Point because on this Server it isn\'t possible to determine installer path %s',
                'duplicator-pro'
            ),
            $path
        );
    }

    /**
     * Determine possible path for installer.
     * If is none the installer can't be executed
     *
     * @return string can be duplicator, home, none
     */
    protected function getPathMode()
    {
        if (strlen(DUP_PRO_Global_Entity::getInstance()->getRecoveryCustomPath()) > 0) {
            return self::PATH_MODE_CUSTOM;
        }
        return (self::isPathBackupAvaiable() ? self::PATH_MODE_BACKUP : self::PATH_MODE_NONE);
    }

    /**
     * Return recoverable packages list
     *
     * @param bool $reset if true reset packages list
     *
     * @return array<int, array{id: int, created: string, nameHash: string, name: string}>
     */
    public static function getRecoverablesPackages($reset = false)
    {
        if (is_null(self::$recoveablesPackages) || $reset) {
            self::$recoveablesPackages = array();
            DUP_PRO_Package::by_status_callback(
                array(__CLASS__, 'recoverablePackageCheck'),
                array(
                    array('op' => '>=', 'status' => DUP_PRO_PackageStatus::COMPLETE)
                ),
                self::MAX_PACKAGES_LIST,
                0,
                '`created` DESC'
            );
        }
        self::addRecoverPackageToListIfNotExists();

        return self::$recoveablesPackages;
    }

    /**
     * Add current recovery package in list if not exists
     *
     * @return bool  Returns true if it does not exist
     */
    protected static function addRecoverPackageToListIfNotExists()
    {
        if (($recoverPackageId = get_option(self::OPTION_RECOVER_PACKAGE_ID)) === false) {
            return true;
        }

        if (in_array($recoverPackageId, array_keys(self::$recoveablesPackages))) {
            return true;
        }

        $recoverPackage = DUP_PRO_Package::get_by_id($recoverPackageId);
        if (!$recoverPackage instanceof DUP_PRO_Package) {
            return false;
        }

        return self::recoverablePackageCheck($recoverPackage);
    }

    /**
     * return true if packages id is recoverable
     *
     * @param int     $id    package id
     * @param boolean $reset if true reset packages list
     *
     * @return boolean
     */
    public static function isPackageIdRecoveable($id, $reset = false)
    {
        if (DUP_PRO_CTRL_recovery::isDisallow()) {
            return false;
        }

        return in_array($id, self::getRecoverablesPackagesIds($reset));
    }

    /**
     * Get recoverable package ids
     *
     * @param bool $reset if true reset list
     *
     * @return int[]
     */
    public static function getRecoverablesPackagesIds($reset = false)
    {
        return array_keys(self::getRecoverablesPackages($reset));
    }

    /**
     * Check if package is recoverable
     *
     * @param DUP_PRO_Package $package
     *
     * @return bool true if is added
     */
    public static function recoverablePackageCheck(DUP_PRO_Package $package)
    {
        $status = new RecoveryStatus($package);
        if (!$status->isRecoveable()) {
            return false;
        }

        self::$recoveablesPackages[$package->ID] = array(
            'id'       => $package->ID,
            'created'  => $package->Created,
            'nameHash' => $package->NameHash,
            'name'     => $package->Name
        );
        return true;
    }

    /**
     * Remove recovery folders
     *
     * @return void
     */
    public static function removeRecoveryFolder()
    {
        if (file_exists(DUPLICATOR_PRO_PATH_RECOVER)) {
            SnapIO::rrmdir(DUPLICATOR_PRO_PATH_RECOVER);
        }

        if (strlen(DUP_PRO_Global_Entity::getInstance()->getRecoveryCustomPath()) > 0) {
            $customFolder = DUP_PRO_Global_Entity::getInstance()->getRecoveryCustomPath();
            if (file_exists($customFolder)) {
                SnapIO::rrmdir($customFolder);
            }
        }
    }
}
