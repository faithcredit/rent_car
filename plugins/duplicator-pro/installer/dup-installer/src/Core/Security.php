<?php

/**
 * Interface that collects the functions of initial duplicator Bootstrap
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core;

use Duplicator\Installer\Bootstrap\BootstrapRunner;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Bootstrap;
use Duplicator\Installer\Core\Descriptors\ArchiveConfig;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\SecureCsrf;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;
use DUPX_ArchiveConfig;
use DUPX_Ctrl_ajax;
use DUPX_InstallerState;
use Exception;

/**
 * singleton class
 *
 * In this class all installer security checks are performed. If the security checks are not passed, an exception is thrown and the installer is stopped.
 * This happens before anything else so the class must work without the initialization of all global duplicator variables.
 */
class Security
{
    const CTRL_TOKEN   = 'ctrl_csrf_token';
    const ROUTER_TOKEN = 'router_csrf_token';

    const SECURITY_NONE     = 'none';
    const SECURITY_PASSWORD = 'pwd';
    const SECURITY_ARCHIVE  = 'archive';

    /** @var ?self */
    private static $instance = null;
    /** @var string read from from csrf file */
    private $archivePath = null;
    /** @var string read from from csrf file */
    private $bootloader = null;
    /** @var string read from from csrf file */
    private $bootUrl = null;
    /** @var string read from from csrf file */
    private $bootFilePath = null;
    /** @var string read from from csrf file */
    private $bootLogFile = null;
    /** @var string read from from csrf file */
    private $packageHash = null;
    /** @var string read from from csrf file */
    private $secondaryPackageHash = null;
    /** @var string read from from csrf file */
    private $archivePwd = '';
    /** @var bool read from csrf file */
    private $isManualExtractFound = false;

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Class contructor
     */
    private function __construct()
    {
        SecureCsrf::init(DUPX_INIT, Bootstrap::getPackageHash());

        if (!file_exists(SecureCsrf::getFilePath())) {
            throw new Exception("CSRF FILE NOT FOUND\n"
                    . "Please, check webroot file permsission and dup-installer folder permission");
        }

        $this->bootloader           = SecureCsrf::getVal('bootloader');
        $this->bootUrl              = SecureCsrf::getVal('booturl');
        $this->bootLogFile          = SnapIO::safePath(SecureCsrf::getVal('bootLogFile'));
        $this->bootFilePath         = SnapIO::safePath(SecureCsrf::getVal('installerOrigPath'));
        $this->archivePath          = SnapIO::safePath(SecureCsrf::getVal('archive'));
        $this->archivePwd           = SecureCsrf::getVal(BootstrapRunner::CSRF_KEY_ARCHIVE_PASSWORD);
        $this->packageHash          = SecureCsrf::getVal('package_hash');
        $this->secondaryPackageHash = SecureCsrf::getVal('secondaryHash');
        $this->isManualExtractFound = SecureCsrf::getVal('isManualExtractFound');
    }

    /**
     * archive path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getArchivePath()
    {
        return $this->archivePath;
    }

    /**
     * installer full path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootFilePath()
    {
        return $this->bootFilePath;
    }

    /**
     * boot log file full path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootLogFile()
    {
        return $this->bootLogFile;
    }

    /**
     * bootloader path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootloader()
    {
        return $this->bootloader;
    }

    /**
     * bootloader path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootUrl()
    {
        return $this->bootUrl;
    }

    /**
     * package hash read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getPackageHash()
    {
        return $this->packageHash;
    }

    /**
     * Get archvie password
     *
     * @return string
     */
    public function getArchivePassword()
    {
        return $this->archivePwd;
    }

    /**
     * package public hash read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getSecondaryPackageHash()
    {
        return $this->secondaryPackageHash;
    }

    /**
     *  Get original installer URL, used if installer is called from mu-plugin
     *
     * @return string
     */
    public function getOriginalInstallerUrl()
    {
        return SecureCsrf::getVal('originalDupInstallerUrl');
    }

    /**
     *
     * @return boolean
     * @throws Exception    // if fail throw exception of return true
     */
    public function check()
    {
        try {
            // check if current package hash is equal at bootloader package hash
            if ($this->packageHash !== Bootstrap::getPackageHash()) {
                throw new Exception('Incorrect hash package');
            }

            // checks if the version of the package descriptor is consistent with the version of the files.
            if (DUPX_ArchiveConfig::getInstance()->version_dup !== DUPX_VERSION) {
                throw new Exception('The version of the archive is different from the version of the PHP scripts');
            }

            $token_tested = false;
            $debug        = self::isDebug();

            $action = null;
            if (DUPX_Ctrl_ajax::isAjax($action) == true) {
                if (($token = self::getTokenFromInput(DUPX_Ctrl_ajax::TOKEN_NAME)) === false) {
                    $msg = 'Security issue' . ($debug ? ' LINE: ' . __LINE__ . ' TOKEN: ' . $token . ' KEY NAME: ' . DUPX_Ctrl_ajax::TOKEN_NAME : '');
                    throw new Exception($msg);
                }
                if (!SecureCsrf::check(self::getTokenFromInput(DUPX_Ctrl_ajax::TOKEN_NAME), DUPX_Ctrl_ajax::getTokenKeyByAction($action))) {
                    $msg = 'Security issue';
                    if ($debug) {
                        $msg .= ' LINE: ' . __LINE__ .
                        ' TOKEN: ' . $token .
                        ' KEY NAME: ' . DUPX_Ctrl_ajax::getTokenKeyByAction($action) .
                        ' KEY VALUE ' . DUPX_Ctrl_ajax::getTokenKeyByAction($action);
                    }
                    throw new Exception($msg);
                }
                $token_tested = true;
            } elseif (($token = self::getTokenFromInput(self::CTRL_TOKEN)) !== false) {
                if (!isset($_REQUEST[PrmMng::PARAM_CTRL_ACTION])) {
                    $msg = 'Security issue' . ($debug ? ' LINE: ' . __LINE__ . ' TOKEN: ' . $token . ' KEY NAME: ' . PrmMng::PARAM_CTRL_ACTION : '');
                    throw new Exception($msg);
                }
                if (!SecureCsrf::check($token, $_REQUEST[PrmMng::PARAM_CTRL_ACTION])) {
                    $msg = 'Security issue';
                    if ($debug) {
                        $msg .= ' LINE: ' . __LINE__ .
                        ' TOKEN: ' . $token .
                        ' KEY NAME: ' . PrmMng::PARAM_CTRL_ACTION .
                        ' KEY VALUE ' . $_REQUEST[PrmMng::PARAM_CTRL_ACTION];
                    }
                    throw new Exception($msg);
                }
                $token_tested = true;
            }

            if (($token = self::getTokenFromInput(self::ROUTER_TOKEN)) !== false) {
                if (!isset($_REQUEST[PrmMng::PARAM_ROUTER_ACTION])) {
                    $msg = 'Security issue' . ($debug ? ' LINE: ' . __LINE__ . ' TOKEN: ' . $token . ' KEY NAME: ' . PrmMng::PARAM_ROUTER_ACTION : '');
                    throw new Exception($msg);
                }
                if (!SecureCsrf::check($token, $_REQUEST[PrmMng::PARAM_ROUTER_ACTION])) {
                    $msg = 'Security issue';
                    if ($debug) {
                        $msg .= ' LINE: ' . __LINE__ .
                        ' TOKEN: ' . $token .
                        ' KEY NAME: ' . PrmMng::PARAM_ROUTER_ACTION .
                        ' KEY VALUE ' . $_REQUEST[PrmMng::PARAM_ROUTER_ACTION];
                    }
                    throw new Exception($msg);
                }
                $token_tested = true;
            }

            // At least one token must always and in any case be tested
            if (!$token_tested) {
                throw new Exception('Security Check Validation - No Token Found');
            }
        } catch (Exception $e) {
            if (function_exists('error_clear_last')) {
                // comment error_clear_last if you want see te exception html on shutdown
                error_clear_last();
            }

            Log::logException($e, Log::LV_DEFAULT, 'SECURITY CHECK: ');
            dupxTplRender('page-security-error', array(
                'message' => $e->getMessage()
            ));
            die();
        }

        return true;
    }

    /**
     * Return tru if is debug
     *
     * @return bool
     */
    protected static function isDebug()
    {
        /** @todo connect with global debug */
        return false;
    }

    /**
     * Get sanitized token frominput
     *
     * @param string $tokenName token name
     *
     * @return false|string get token or false if don't exists
     */
    protected static function getTokenFromInput($tokenName)
    {
        return SnapUtil::filterInputDefaultSanitizeString(SnapUtil::INPUT_REQUEST, $tokenName, false);
    }

    /**
     * Get security tipe (NONE, PASSWORD, ARCHIVE)
     *
     * @return string enum type
     */
    public function getSecurityType()
    {
        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_SECURE_OK) == true) {
            return self::SECURITY_NONE;
        }

        $archiveConfig = DUPX_ArchiveConfig::getInstance();

        if ($this->isManualExtractFound && $archiveConfig->secure_on === ArchiveConfig::SECURE_MODE_ARC_ENCRYPT) {
            $archiveConfig->secure_on = ArchiveConfig::SECURE_MODE_INST_PWD;
        }

        if ($archiveConfig->secure_on === ArchiveConfig::SECURE_MODE_INST_PWD) {
            return self::SECURITY_PASSWORD;
        }

        if (
            strlen($this->archivePwd) == 0 &&
            DUPX_InstallerState::isOverwrite() &&
            basename($this->bootFilePath) == 'installer.php' &&
            !in_array($_SERVER['REMOTE_ADDR'], self::getSecurityAddrWhitelist())
        ) {
            return self::SECURITY_ARCHIVE;
        }

        return self::SECURITY_NONE;
    }

    /**
     * Get IPs white list for remote requests
     *
     * @return string[]
     */
    private static function getSecurityAddrWhitelist()
    {
        // uncomment this to test security archive on localhost
        // return array();
        // -------
        return array(
            '127.0.0.1',
            '::1'
        );
    }

    /**
     * return true if security check is passed
     *
     * @return bool
     */
    public function securityCheck()
    {
        $paramsManager = PrmMng::getInstance();
        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $result        = false;
        switch ($this->getSecurityType()) {
            case self::SECURITY_NONE:
                $result = true;
                break;
            case self::SECURITY_PASSWORD:
                $paramsManager->setValueFromInput(PrmMng::PARAM_SECURE_PASS);
                $inputPwd = $paramsManager->getValue(PrmMng::PARAM_SECURE_PASS);
                $result   = hash_equals($archiveConfig->secure_pass, self::passwordHash($inputPwd));
                break;
            case self::SECURITY_ARCHIVE:
                $paramsManager->setValueFromInput(PrmMng::PARAM_SECURE_ARCHIVE_HASH);
                $result = (strcmp(basename($this->archivePath), $paramsManager->getValue(PrmMng::PARAM_SECURE_ARCHIVE_HASH)) == 0);
                break;
            default:
                throw new Exception('Security type not valid ' . $this->getSecurityType());
        }

        $paramsManager->setValue(PrmMng::PARAM_SECURE_OK, $result);
        $paramsManager->save();
        return $result;
    }

    /**
     * Hash password
     *
     * @param string $password input password
     *
     * @return string Returns a string containing the calculated message digest as lowercase hexit
     */
    public static function passwordHash($password)
    {
        return hash('sha512', $password);
    }
}
