<?php

/**
 * @package Duplicator\Installer
 */

namespace Duplicator\Installer\Bootstrap;

use Duplicator\Installer\Utils\SecureCsrf;
use Duplicator\Libs\DupArchive\DupArchive;
use Duplicator\Libs\DupArchive\DupArchiveExpandBasicEngine;
use Duplicator\Libs\Shell\Shell;
use Exception;
use InstallerBootstrapData;
use ZipArchive;

/**
 * Bootstrap singleton class
 */
class BootstrapRunner
{
    const MINIMUM_PHP_VERSION = '5.6.20';

    const NAME_PWD        = 'password';
    const NAME_PWD_BUTTON = 'secure-btn';

    const ZIP_MODE_NONE    = -1;
    const ZIP_MODE_AUTO    = 0;
    const ZIP_MODE_ARCHIVE = 1;
    const ZIP_MODE_SHELL   = 2;

    const CSRF_KEY_ARCHIVE_PASSWORD = 'arc_pwd';

    /** @var string */
    protected $archive = '';
    /** @var string */
    protected $bootloader = '';
    /** @var string */
    protected $archiveFileName = '';
    /** @var string */
    protected $archiveFileSize = '0';
    /** @var string */
    protected $installerDirName = '';
    /** @var string */
    protected $packageHash = '';
    /** @var string */
    protected $secondaryHash = '';
    /** @var string */
    protected $version = '';
    /** @var string */
    protected $archivePwd = '';
    /** @var string */
    protected $extractionTmpFolder = '';

    /** @var ?string */
    public $targetRoot = null;
    /** @var ?string */
    public $origDupInstFolder = null;
    /** @var ?string */
    public $targetDupInstFolder = null;
    /** @var ?string */
    public $targetDupInst = null;
    /** @var ?string */
    public $manualExtractFileName = null;
    /** @var bool */
    public $isCustomDupFolder = false;
    /** @var bool */
    public $hasZipArchive = false;
    /** @var string */
    public $mainInstallerURL = '';
    /** @var int */
    public $archiveExpectedSize = 0;
    /** @var int */
    public $archiveActualSize = 0;
    /** @var float */
    public $archiveRatio = 0;
    /** @var string */
    protected $errorMessage = '';
    /** @var int */
    protected $installerFilesFound = 0;

    /** @var ?self */
    private static $instance = null;

    /**
     * Class contructor
     */
    protected function __construct()
    {
        if (!class_exists(InstallerBootstrapData::class)) {
            throw new Exception('Class InstallerBootstrapData must be defined');
        }

        $this->archiveFileName  = InstallerBootstrapData::ARCHIVE_FILENAME;
        $this->archiveFileSize  = InstallerBootstrapData::ARCHIVE_SIZE;
        $this->installerDirName = InstallerBootstrapData::INSTALLER_DIR_NAME;
        $this->packageHash      = InstallerBootstrapData::PACKAGE_HASH;
        $this->secondaryHash    = InstallerBootstrapData::SECONDARY_PACKAGE_HASH;
        $this->version          = InstallerBootstrapData::VERSION;

        $this->setHTTPHeaders();
        $this->targetRoot = BootstrapUtils::setSafePath(dirname(__FILE__));
        // clean log file
        $this->log('', true);

        $archive_filepath          = $this->getArchiveFilePath();
        $this->origDupInstFolder   = $this->installerDirName;
        $this->targetDupInstFolder = filter_input(INPUT_GET, 'dup_folder', FILTER_SANITIZE_SPECIAL_CHARS, array(
            "options" => array(
                "default" => $this->installerDirName,
            ),
            'flags'   => FILTER_FLAG_STRIP_HIGH));

        $this->isCustomDupFolder     = $this->origDupInstFolder !== $this->targetDupInstFolder;
        $this->targetDupInst         = $this->targetRoot . '/' . $this->targetDupInstFolder;
        $this->manualExtractFileName = 'dup-manual-extract__' . $this->packageHash;

        if ($this->isCustomDupFolder) {
            $this->extractionTmpFolder = $this->getTempDir($this->targetRoot);
        } else {
            $this->extractionTmpFolder = $this->targetRoot;
        }

        SecureCsrf::init($this->targetDupInst, $this->packageHash);

        //ARCHIVE_SIZE will be blank with a root filter so we can estimate
        //the default size of the package around 17.5MB (18088000)
        $archiveActualSize         = @file_exists($archive_filepath) ? @filesize($archive_filepath) : false;
        $archiveActualSize         = ($archiveActualSize !== false) ? $archiveActualSize : 0;
        $this->archiveExpectedSize = (is_numeric($this->archiveFileSize) ? (int) $this->archiveFileSize : 0);
        $this->archiveActualSize   = $archiveActualSize;

        if ($this->archiveExpectedSize > 0) {
            $this->archiveRatio = (((1.0) * $this->archiveActualSize) / $this->archiveExpectedSize) * 100;
        } else {
            $this->archiveRatio = 100;
        }
    }

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
     * PHP init values
     *
     * @return void
     */
    public static function initSetValues()
    {
        define('KB_IN_BYTES', 1024);
        define('MB_IN_BYTES', 1024 * KB_IN_BYTES);
        define('GB_IN_BYTES', 1024 * MB_IN_BYTES);
        define('DUPLICATOR_PRO_PHP_MAX_MEMORY', 4096 * MB_IN_BYTES);

        date_default_timezone_set('UTC'); // Some machines don’t have this set so just do it here.
        @ignore_user_abort(true);
        @set_time_limit(3600);
        if (BootstrapUtils::isIniValChangeable('memory_limit')) {
            @ini_set('memory_limit', (string) DUPLICATOR_PRO_PHP_MAX_MEMORY);
        }
        if (BootstrapUtils::isIniValChangeable('max_input_time')) {
            @ini_set('max_input_time', '-1');
        }
        if (BootstrapUtils::isIniValChangeable('pcre.backtrack_limit')) {
            @ini_set('pcre.backtrack_limit', (string) PHP_INT_MAX);
        }
        if (BootstrapUtils::isIniValChangeable('default_socket_timeout')) {
            @ini_set('default_socket_timeout', '3600');
        }
    }

    /**
     * Makes sure no caching mechanism is used during install
     *
     * @return void
     */
    private function setHTTPHeaders()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    /**
     * Return temp dir
     *
     * @param string $path path string
     *
     * @return boolean|string
     */
    private function getTempDir($path)
    {
        $tempfile = tempnam($path, 'dup-installer_tmp_');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            mkdir($tempfile);
            if (is_dir($tempfile)) {
                return $tempfile;
            }
        }
        return false;
    }

    /**
     * Run the bootstrap process which includes checking for requirements and running
     * the extraction process
     *
     * @return null | string Returns null if the run was successful otherwise an error message
     */
    public function run()
    {
        date_default_timezone_set('UTC'); // Some machines don't have this set so just do it here

        $this->log('==DUPLICATOR INSTALLER BOOTSTRAP v' . $this->version . '==');
        $this->log('----------------------------------------------------');
        $this->log('Installer bootstrap start');

        $archive_filepath = $this->getArchiveFilePath();
        $this->log('Target dir dup folder \"' . $this->targetDupInst . '\"');
        $this->errorMessage = '';

        $is_installer_file_valid = true;
        if (preg_match('/_([a-z0-9]{7})[a-z0-9]+_[0-9]{6}([0-9]{8})_archive.(?:zip|daf)$/', $this->archiveFileName, $matches)) {
            $expected_package_hash = $matches[1] . '-' . $matches[2];
            if ($this->packageHash != $expected_package_hash) {
                $is_installer_file_valid = false;
                $this->log("[ERROR] Installer and archive mismatch detected.");
            }
        } else {
            $this->log("[ERROR] Invalid archive file name.");
            $is_installer_file_valid = false;
        }

        if (false  === $is_installer_file_valid) {
            $this->errorMessage = "Installer and archive mismatch detected.
                        Ensure uncorrupted installer and matching archive are present.";
            return BootstrapView::VIEW_ERROR;
        }

        if ($this->archiveCheck() == false) {
            return BootstrapView::VIEW_ERROR;
        }

        if (!$this->isManualExtractFound()) {
            if ($this->engineCheck() == false) {
                return BootstrapView::VIEW_ERROR;
            }

            if (!$this->passwordCheck()) {
                return BootstrapView::VIEW_PASSWORD;
            }
        }

        if ($this->installerDirExists()) {
            // INSTALL DIRECTORY: Check if its setup correctly AND we are not in overwrite mode
            if (($extract_installer = filter_input(INPUT_GET, 'force-extract-installer', FILTER_VALIDATE_BOOLEAN))) {
                $this->log("Manual extract found with force extract installer get parametr");
            } else {
                $this->log("Manual extract found so not going to extract " . $this->targetDupInst . " dir");
            }
        } else {
            $extract_installer = true;
        }

        // if ($extract_installer && file_exists($this->targetDupInst)) {
        if (file_exists($this->targetDupInst)) {
            $this->log("EXTRACT " . $this->targetDupInst . " dir");
            $hash_pattern                 = '[a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9]-[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]';
            $file_patterns_with_hash_file = array(
                // file pattern => hash file
                'dup-archive__' . $hash_pattern . '.txt'        => 'dup-archive__' . $this->packageHash . '.txt',
                'dup-database__' . $hash_pattern . '.sql'       => 'dup-database__' . $this->packageHash . '.sql',
                'dup-installer-data__' . $hash_pattern . '.sql' => 'dup-installer-data__' . $this->packageHash . '.sql',
                'dup-installer-log__' . $hash_pattern . '.txt'  => 'dup-installer-log__' . $this->packageHash . '.txt',
                'dup-scan__' . $hash_pattern . '.json'          => 'dup-scan__' . $this->packageHash . '.json',
                'dup-scanned-dirs__' . $hash_pattern . '.txt'   => 'dup-scanned-dirs__' . $this->packageHash . '.txt',
                'dup-scanned-files__' . $hash_pattern . '.txt'  => 'dup-scanned-files__' . $this->packageHash . '.txt',
            );
            foreach ($file_patterns_with_hash_file as $file_pattern => $hash_file) {
                $globs = glob($this->targetDupInst . '/' . $file_pattern);
                if (!empty($globs)) {
                    foreach ($globs as $glob) {
                        $file = basename($glob);
                        if ($file != $hash_file) {
                            if (unlink($glob)) {
                                $this->log('Successfully deleted the file ' . $glob);
                            } else {
                                $this->errorMessage .= '[ERROR] Error deleting the file ' . $glob . ' Please manually delete it and try again.';
                                $this->log($this->errorMessage);
                            }
                        }
                    }
                }
            }
        }

        //ATTEMPT EXTRACTION:
        if ($extract_installer) {
            try {
                $this->extractInstaller();
            } catch (Exception $e) {
                $this->log("Extraction exception msg: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                return BootstrapView::VIEW_ERROR;
            }
        } else {
            $this->log("NOTICE: Didn't need to extract the installer.");
        }

        if ($this->isCustomDupFolder && file_exists($this->extractionTmpFolder)) {
            rmdir($this->extractionTmpFolder);
        }

        $config_files              = glob($this->targetDupInst . '/dup-archive__*.txt');
        $config_file_absolute_path = array_pop($config_files);
        if (!file_exists($config_file_absolute_path)) {
            $this->errorMessage = '<b>Archive config file not found in ' . $this->targetDupInst . ' folder.</b> <br><br>';
            return BootstrapView::VIEW_ERROR;
        }


        if (!file_exists($this->targetDupInst)) {
            $this->errorMessage = 'Can\'t extract installer directory. ' .
                'See <a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-022-q">this FAQ item</a>' .
                ' for details on how to resolve.</a>';
                return BootstrapView::VIEW_ERROR;
        }

        $bootloader_name = basename(__FILE__);

        $this->archive    = $archive_filepath;
        $this->bootloader = $bootloader_name;

        $this->fixInstallerPerms();
        $this->setCsrfData();
        $this->log("DONE: No detected errors so redirecting to the main installer. Main Installer URI = " . $this->getMainInstallerUrl());

        return BootstrapView::VIEW_REDIRECT;
    }

    /**
     * Return main isnstaller URL to redirect
     *
     * @return string
     */
    public function getMainInstallerUrl()
    {
        $uri_start = BootstrapUtils::getCurrentUrl(false, false, 1);
        if (self::isBridgeInstall()) {
            return $uri_start;
        } else {
            return $uri_start . '/' . $this->targetDupInstFolder . '/main.installer.php';
        }
    }

    /**
     * Get data for redirect query string
     *
     * @return mixed[]
     */
    public function getMainInstallerUrlData()
    {
        $data = [
            'ctrl_action'     => 'ctrl-step1',
            'ctrl_csrf_token' => SecureCsrf::generate('ctrl-step1'),
            'step_action'     => 'init'
        ];

        if (self::isBridgeInstall()) {
            $data['dup_mu_action']  = 'installer';
            $data['inst_main_path'] = __DIR__ . '/' . $this->targetDupInstFolder . '/main.installer.php';
            $data['brchk']          = BootstrapUtils::sanitizeNSCharsNewline($_REQUEST['brchk']);
        }

        return $data;
    }

    /**
     * Return error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Return true if the dup installer directory exists
     *
     * @return bool
     */
    private function installerDirExists()
    {
        return file_exists($this->targetDupInst) &&
            file_exists($this->targetDupInst . "/main.installer.php") &&
            file_exists($this->targetDupInst . "/dup-archive__" . $this->packageHash . ".txt");
    }

    /**
     * Return true if a manual archive extract was found
     *
     * @return bool
     */
    private function isManualExtractFound()
    {
        return $this->installerDirExists() &&
            file_exists($this->targetDupInst . "/" . $this->manualExtractFileName);
    }

    /**
     * Set error message
     *
     * @param string $message message
     *
     * @return void
     */
    public function appendErrorMessage($message)
    {
        $this->errorMessage .= (strlen($message) ? " \n" : '') . $message;
    }

    /**
     * return true if archvie is zip archive
     *
     * @return bool
     */
    public function isZip()
    {
        return (strcasecmp(pathinfo($this->archiveFileName, PATHINFO_EXTENSION), 'zip') === 0);
    }

    /**
     * Return true if current archvie is encrypted
     *
     * @return bool
     */
    protected function isArchiveEncrypted()
    {
        static $isEncrypted = null;
        if ($isEncrypted === null) {
            if ($this->isZip()) {
                if (BootstrapUtils::isZipAvailable()) {
                    $isEncrypted = BootstrapUtils::isZipArchiveEncrypted($this->getArchiveFilePath(), 'main.installer.php');
                } else {
                    $isEncrypted = null;
                    throw new Exception("ERROR: Can't check if zip archive is encrypted. " .
                        "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-060-q'>ZipArchive</a> " .
                        "and <a target='_blank' href='http://php.net/manual/en/function.shell-exec.php'>ShellExec unzip</a>" .
                        "are not enabled on this server. Please " .
                        "talk to your host or server admin about enabling at least one of them.<br>" .
                        "Alternative is to manually extract archive then choose Advanced > Manual Extract in installer.");
                }
            } else {
                $isEncrypted = DupArchive::isEncrypted($this->getArchiveFilePath());
            }
        }
        return $isEncrypted;
    }

    /**
     * Check password
     *
     * @return bool true on success
     */
    protected function passwordCheck()
    {
        if (!$this->isArchiveEncrypted()) {
            return true;
        }

        $this->log('ARCHIVE ENCRYPTED, PASSWORD CHECK');

        $result       = false;
        $password     = (isset($_REQUEST[self::NAME_PWD]) ? BootstrapUtils::sanitizeNSCharsNewline($_REQUEST[self::NAME_PWD]) : '');
        $passwordSend = (strlen($password) > 0 || (isset($_REQUEST[self::NAME_PWD_BUTTON]) && $_REQUEST[self::NAME_PWD_BUTTON] === 'secure'));

        if ($this->isZip()) {
            if (
                $result = BootstrapUtils::zipArchivePasswordCheck(
                    $this->getArchiveFilePath(),
                    $password,
                    'dup-installer/main.installer.php',
                    $this->getZipMode()
                )
            ) {
                $this->log('ZIP ARCHIVE PASSWORD OK ');
            } else {
                $this->log('ZIP ARCHIVE PASSWORD FAIL ');
            }
        } else {
            if ($result = DupArchive::checkPassword($this->getArchiveFilePath(), $password)) {
                $this->log('DUP ARCHIVE PASSWORD OK ');
            } else {
                $this->log('DUP ARCHIVE PASSWORD FAIL ');
            }
        }

        if ($result) {
            $this->archivePwd = $password;
        } else {
            if ($passwordSend) {
                $this->appendErrorMessage('<span class="password-invalid-msg" >Invalid password<span>');
            }
            // Prevent brute force attack
            sleep(1);
        }

        return $result;
    }

    /**
     * Check archive size
     *
     * @return bool true             on success
     */
    protected function archiveCheck()
    {
        $archiveFilePath     = $this->getArchiveFilePath();
        $archiveExpectedEasy = BootstrapUtils::readableByteSize($this->archiveExpectedSize);
        $archiveActualEasy   = BootstrapUtils::readableByteSize($this->archiveActualSize);
        $archiveFileExists   = file_exists($archiveFilePath);

        // MISSING ARCHIVE FILE
        if ($this->isManualExtractFound() && !$archiveFileExists) {
            $this->log("[ERROR] Archive file not found!");
            $this->errorMessage = "<style>.diff-list font { font-weight: bold; }</style>"
                . "<b>Archive not found!</b> The archive file cannot be found at the current path:<br/>"
                . "<span class='file-info'>{$archiveFilePath}</span><br/>"
                . "Please make sure the archive remains until the installation process is completed.";
            return false;
        } elseif (!$archiveFileExists) {
            $this->log("[ERROR] Archive file not found!");
            $this->errorMessage = "<style>.diff-list font { font-weight: bold; }</style>"
                . "<b>Archive not found!</b> The required archive file must be present in the <i>'Extraction Path'</i> below. "
                . "When the archive file name was created "
                . "it was given a secure hashed file name.  This file name must be the <i>exact same</i> "
                . "name as when it was created character for character. "
                . "Each archive file has a unique installer associated with it and must be used together.  See the list below for more options:<br/>"
                . "<ul>"
                . "<li>If the archive is not finished downloading please wait for it to complete.</li>"
                . "<li>Rename the file to it original hash name.  See WordPress-Admin ❯ Packages ❯  Details. </li>"
                . "<li>When downloading, both files both should be from the same package line. </li>"
                . "<li>Also see: <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-050-q' target='_blank'>"
                . "How to fix various errors that show up before step-1 of the installer?</a></li>"
                . "</ul><br/>"
                . "<b>Extraction Path:</b> <span class='file-info'>{$this->targetRoot}/</span><br/>";

            return false;
        }

        // Sometimes the self::ARCHIVE_SIZE is ''.
        if (strlen($this->archiveFileSize) > 0 && !self::checkInputValidInt($this->archiveFileSize)) {
            $noOfBits           = PHP_INT_SIZE * 8;
            $this->errorMessage = 'Current is a ' . $noOfBits . '-bit SO. This archive is too large for ' . $noOfBits . '-bit PHP.' . '<br>';
            $this->log('[ERROR] ' . $this->errorMessage);
            $this->errorMessage .= 'Possibibles solutions:<br>';
            $this->errorMessage .= '- Use the file filters to get your package lower to support this server or try the package on a Linux server.' . '<br>';
            $this->errorMessage .= '- Perform a <a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q">' .
                'Manual Extract Install</a>' . '<br>';

            switch ($noOfBits == 32) {
                case 32:
                    $this->errorMessage .= '- Ask your host to upgrade the server to 64-bit PHP or install on another system has 64-bit PHP' . '<br>';
                    break;
                case 64:
                    $this->errorMessage .= '- Ask your host to upgrade the server to 128-bit PHP or install on another system has 128-bit PHP' . '<br>';
                    break;
            }

            if (self::isWindows()) {
                $this->errorMessage .= '- <a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-052-q">' .
                    'Windows DupArchive extractor</a> to extract all files from the archive.' . '<br>';
            }

            return false;
        }

        // SIZE CHECK ERROR
        if (($this->archiveRatio < 90) && ($this->archiveActualSize > 0) && ($this->archiveExpectedSize > 0)) {
            $this->log(
                "ERROR: The expected archive size should be around [{$archiveExpectedEasy}]. " .
                "The actual size is currently [{$archiveActualEasy}]."
            );
            $this->log("ERROR: The archive file may not have fully been downloaded to the server");

            $this->errorMessage = "<b>Archive file size warning.</b><br/> The expected archive size is <b class='pass'>[{$archiveExpectedEasy}]</b>. "
                . "Currently the archive size is <b class='fail'>[{$archiveActualEasy}]</b>. <br/>"
                . "The archive file may have <b>not fully been uploaded to the server.</b>"
                . "<ul>"
                . "<li>Download the whole archive from the source website (open WP Admin &gt; Duplicator Pro &gt; Packages) "
                . "and validate that the file size is close to the expected size. </li>"
                . "<li>Make sure to upload the whole archive file to the destination server.</li>"
                . "<li>If the archive file is still uploading then please refresh this page to get an update on the currently uploaded file size.</li>"
                . "</ul>";
            return false;
        }

        return true;
    }

    /**
     * Check engine problems
     *
     * @return bool true on success
     */
    protected function engineCheck()
    {
        try {
            if ($this->isZip()) {
                if (!BootstrapUtils::isZipAvailable()) {
                    $msg = "ZipArchive and Shell Exec are not enabled on this server. Please " .
                    "talk to your host or server admin about enabling " .
                    "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-060-q'>ZipArchive</a> " .
                    "or <a target='_blank' href='http://php.net/manual/en/function.shell-exec.php'>Shell Exec</a> " .
                    "on this server or manually extract archive then choose Advanced > Manual Extract in installer.";
                    throw new Exception($msg);
                }

                if (
                    $this->isArchiveEncrypted() &&
                    BootstrapUtils::isPhpZipAvaiable() &&
                    !BootstrapUtils::isShellZipAvailable() &&
                    version_compare(BootstrapUtils::getLibzipVersion(), '1.2.0', '<')
                ) {
                    ob_start();
                    ?>
                    <b>ZipArchive Error</b><br/>
                    This server is unable to decrypt the archive file (ZipArchive), Libzip version 1.2.0+ is required.<br/>
                    Current Libzip version: <?php echo BootstrapUtils::getLibzipVersion(); ?>
                    Please contact your host or server admin to update Libzip version.
                    <?php
                    $msg = ob_get_clean();
                    throw new Exception($msg);
                }
            } else {
                if ($this->isArchiveEncrypted() && !DupArchive::isEncryptionAvaliable()) {
                    ob_start();
                    ?>
                    <b>DupArchive Error</b><br/>
                    This server is unable to decrypt the archive file (DupArchive) without the PHP OpenSSL module.<br/>
                    Please contact your host or server admin to enable the
                    <a href="https://www.php.net/manual/en/book.openssl.php" target="_blank" >OpenSSL module</a>.
                    <?php
                    $msg = ob_get_clean();
                    throw new Exception($msg);
                }
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Extract dup-installer folder
     *
     * @return void
     */
    protected function extractInstaller()
    {
        $this->log("Ready to extract the installer");
        $archive_filepath = $this->getArchiveFilePath();

        $this->log("Checking permission of destination folder");
        $destination = $this->targetRoot;
        if (!is_writable($destination)) {
            $this->log("destination folder for extraction is not writable");
            if (BootstrapUtils::chmod($destination, 'u+rwx')) {
                $this->log("Permission of destination folder changed to u+rwx");
            } else {
                $this->log("[ERROR] Permission of destination folder failed to change to u+rwx");
            }
        }

        if (!is_writable($destination)) {
            $this->log("WARNING: The {$destination} directory is not writable.");
            $this->errorMessage  = "NOTICE: The {$destination} directory is not writable on this server please talk to your host or server admin about making ";
            $this->errorMessage .= "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-055-q'>" .
            "writable {$destination} directory</a> on this server. <br/>";
            throw new Exception('Destination folter isn\'t writeable');
        }

        if ($this->isZip()) {
            if ($this->extractInstallerZip() == false) {
                throw new Exception('Fail zip extraction');
            }
        } else {
            try {
                DupArchiveExpandBasicEngine::setCallbacks(
                    array($this, 'log'),
                    array($this, 'chmod'),
                    array($this, 'mkdir')
                );
                $offset = DupArchiveExpandBasicEngine::getExtraOffset($archive_filepath, $this->archivePwd);
                $this->log('Expand directory from offset ' . $offset);
                DupArchiveExpandBasicEngine::expandDirectory(
                    $archive_filepath,
                    $this->origDupInstFolder,
                    $this->extractionTmpFolder,
                    $this->archivePwd,
                    false,
                    $offset
                );
                //In case of DupArchive just remove the manual extract check file
                @unlink($this->extractionTmpFolder . "/" . $this->origDupInstFolder . "/" . $this->manualExtractFileName);
            } catch (Exception $ex) {
                $this->log("[ERROR] Error expanding installer subdirectory:" . $ex->getMessage());
                throw $ex;
            }
        }

        if ($this->isCustomDupFolder) {
            $this->log("Move dup-installer folder to custom folder:" .  $this->targetDupInst);
            if (file_exists($this->targetDupInst)) {
                $this->log('Custom folder already exists so delete it');
                if (BootstrapUtils::rrmdir($this->targetDupInst) == false) {
                    throw new Exception('Can\'t remove custom target folder');
                }
            }
            if (rename($this->extractionTmpFolder . '/' . $this->origDupInstFolder, $this->targetDupInst) === false) {
                throw new Exception('Can\'t rename the tmp dup-installer folder');
            }
        }

        $htaccessToRemove = $this->targetDupInst . '/.htaccess';
        if (is_file($htaccessToRemove) && is_writable($htaccessToRemove)) {
            $this->log("Remove Htaccess in dup-installer folder");
            @unlink($htaccessToRemove);
        }

        $is_apache = (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false);
        $is_nginx  = (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false);

        $sapi_type                   = php_sapi_name();
        $php_ini_data                = array(
            'max_execution_time'     => 3600,
            'max_input_time'         => -1,
            'ignore_user_abort'      => 'On',
            'post_max_size'          => '4096M',
            'upload_max_filesize'    => '4096M',
            'memory_limit'           => DUPLICATOR_PRO_PHP_MAX_MEMORY,
            'default_socket_timeout' => 3600,
            'pcre.backtrack_limit'   => 99999999999,
        );
        $sapi_type_first_three_chars = substr($sapi_type, 0, 3);
        if ('fpm' === $sapi_type_first_three_chars) {
            $this->log("SAPI: FPM");
            if ($is_apache) {
                $this->log('Server: Apache');
            } elseif ($is_nginx) {
                $this->log('Server: Nginx');
            }

            if (($is_apache && function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) || $is_nginx) {
                $htaccess_data = array();
                foreach ($php_ini_data as $php_ini_key => $php_ini_val) {
                    if ($is_apache) {
                        $htaccess_data[] = 'SetEnv PHP_VALUE "' . $php_ini_key . ' = ' . $php_ini_val . '"';
                    } elseif ($is_nginx) {
                        if ('On' == $php_ini_val || 'Off' == $php_ini_val) {
                            $htaccess_data[] = 'php_flag ' . $php_ini_key . ' ' . $php_ini_val;
                        } else {
                            $htaccess_data[] = 'php_value ' . $php_ini_key . ' ' . $php_ini_val;
                        }
                    }
                }

                $htaccess_text      = implode("\n", $htaccess_data);
                $htaccess_file_path = $this->targetDupInst . '/.htaccess';
                $this->log("creating {$htaccess_file_path} with the content:");
                $this->log($htaccess_text);
                @file_put_contents($htaccess_file_path, $htaccess_text);
            }
        } elseif ('cgi' === $sapi_type_first_three_chars || 'litespeed' === $sapi_type) {
            if ('cgi' === $sapi_type_first_three_chars) {
                $this->log("SAPI: CGI");
            } else {
                $this->log("SAPI: litespeed");
            }
            if (version_compare(phpversion(), '5.5') >= 0 && (!$is_apache || 'litespeed' === $sapi_type)) {
                $ini_data = array();
                foreach ($php_ini_data as $php_ini_key => $php_ini_val) {
                    $ini_data[] = $php_ini_key . ' = ' . $php_ini_val;
                }
                $ini_text      = implode("\n", $ini_data);
                $ini_file_path = $this->targetDupInst . '/.user.ini';
                $this->log("creating {$ini_file_path} with the content:");
                $this->log($ini_text);
                @file_put_contents($ini_file_path, $ini_text);
            } else {
                $this->log("No need to create " . $this->targetDupInst . "/.htaccess or " . $this->targetDupInst . "/.user.ini");
            }
        } elseif ("apache2handler" === $sapi_type) {
            $this->log("No need to create " . $this->targetDupInst . "/.htaccess or " . $this->targetDupInst . "/.user.ini");
            $this->log("SAPI: apache2handler");
        } else {
            $this->log("No need to create " . $this->targetDupInst . "/.htaccess or " . $this->targetDupInst . "/.user.ini");
            $this->log("ERROR:  SAPI: Unrecognized");
        }
    }

    /**
     * Extract zip archive
     *
     * @return bool true on success
     */
    protected function extractInstallerZip()
    {
        $success = false;

        switch ($this->getZipMode()) {
            case self::ZIP_MODE_ARCHIVE:
                $this->log("ZipArchive exists so using that");
                $success = $this->extractInstallerZipArchive($this->getArchiveFilePath(), $this->origDupInstFolder, $this->extractionTmpFolder);
                if ($success) {
                    $this->log('Successfully extracted with ZipArchive');
                } else {
                    if (0 == $this->installerFilesFound) {
                        $this->errorMessage = "[ERROR] This archive is not properly formatted and does not contain a " . $this->origDupInstFolder .
                        " directory. Please make sure you are attempting to install " .
                        "the original archive and not one that has been reconstructed.";
                        $this->log($this->errorMessage);
                    } else {
                        $this->errorMessage = '[ERROR] Error extracting with ZipArchive. ';
                        $this->log($this->errorMessage);
                    }
                }
                break;
            case self::ZIP_MODE_SHELL:
                $success = $this->extractInstallerShellexec($this->getArchiveFilePath(), $this->origDupInstFolder, $this->extractionTmpFolder);
                $this->log("Resetting perms of items in folder {$this->targetDupInst}");
                self::setPermsToDefaultR($this->targetDupInst);
                if ($success) {
                    $this->log('Successfully extracted with Shell Exec');
                    $this->errorMessage = '';
                } else {
                    $this->errorMessage .= '[ERROR] Error extracting with Shell Exec. ' .
                    'Please manually extract archive then choose Advanced > Manual Extract in installer.';
                    $this->log($this->errorMessage);
                }
                break;
            case self::ZIP_MODE_NONE:
                if (!BootstrapUtils::isZipAvailable()) {
                    $this->log("WARNING: ZipArchive and Shell Exec are not enabled on this server.");
                    $this->errorMessage = "NOTICE: ZipArchive and Shell Exec are not enabled on this server. Please " .
                    "talk to your host or server admin about enabling " .
                    "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-060-q'>ZipArchive</a> " .
                    "or <a target='_blank' href='http://php.net/manual/en/function.shell-exec.php'>Shell Exec</a> " .
                    "on this server or manually extract archive then choose Advanced > Manual Extract in installer.";
                }
                break;
        }

        return $success;
    }

    /**
     * Set secure data
     *
     * @return void
     */
    protected function setCsrfData()
    {
        SecureCsrf::setKeyVal('installerOrigCall', BootstrapUtils::getCurrentUrl());
        SecureCsrf::setKeyVal('installerOrigPath', __FILE__);
        SecureCsrf::setKeyVal('archive', $this->archive);
        SecureCsrf::setKeyVal('bootloader', $this->bootloader);
        SecureCsrf::setKeyVal(self::CSRF_KEY_ARCHIVE_PASSWORD, $this->archivePwd);
        SecureCsrf::setKeyVal('booturl', '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        SecureCsrf::setKeyVal('bootLogFile', $this->getBootLogFilePath());
        SecureCsrf::setKeyVal('package_hash', $this->packageHash);
        SecureCsrf::setKeyVal('secondaryHash', $this->secondaryHash);

        if (self::isBridgeInstall()) {
            if (!isset($_REQUEST['inst_main_url'])) {
                throw new Exception('Invalid input data, inst_main_url required');
            }
            SecureCsrf::setKeyVal('originalDupInstallerUrl', $_REQUEST['inst_main_url']);
        } else {
            SecureCsrf::setKeyVal('originalDupInstallerUrl', '');
        }
        SecureCsrf::setKeyVal('isManualExtractFound', $this->isManualExtractFound());
    }

    /**
     * Get redirect form
     *
     * @return string html redirect form
     */
    public function getRedirectForm()
    {
        $mainInstallerURL = $this->getMainInstallerUrl();
        $data             = $this->getMainInstallerUrlData();
        $id               = uniqid();

        ob_start();
        ?>
        <form id="<?php echo $id; ?>" method="get" action="<?php echo htmlspecialchars($mainInstallerURL); ?>" >
            <?php
            foreach ($data as $name => $value) {
                if ('csrf_token' != $name) {
                    $_SESSION[$name] = $value;
                }
                ?>
                <input type="hidden" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo htmlspecialchars($value); ?>" >
                <?php
            }
            ?>
        </form>
        <script>
            window.onload = function() {
                document.getElementById(<?php echo json_encode($id); ?>).submit(); 
            }
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Attempts to set the 'dup-installer' directory permissions
     *
     * @return void
     */
    private function fixInstallerPerms()
    {
        $file_perms = 'u+rw';
        $dir_perms  = 'u+rwx';

        $installer_dir_path = $this->targetDupInst;

        $this->setPerms($installer_dir_path, $dir_perms, false);
        $this->setPerms($installer_dir_path, $file_perms, true);
    }

    /**
     * Set the permissions of a given directory and optionally all files
     *
     * @param string $directory The full path to the directory where perms will be set
     * @param string $perms     The given permission sets to use such as '0755' or 'u+rw'
     * @param bool   $do_files  Also set the permissions of all the files in the directory
     *
     * @return void
     */
    private function setPerms($directory, $perms, $do_files)
    {
        if (!$do_files) {
            // If setting a directory hiearchy be sure to include the base directory
            $this->setPermsOnItem($directory, $perms);
        }

        $item_names = array_diff(scandir($directory), array('.', '..'));

        foreach ($item_names as $item_name) {
            $path = "$directory/$item_name";
            if (($do_files && is_file($path)) || (!$do_files && !is_file($path))) {
                $this->setPermsOnItem($path, $perms);
            }
        }
    }

    /**
     * Set the permissions of a single directory or file
     *
     * @param string $path  The full path to the directory or file where perms will be set
     * @param string $perms The given permission sets to use such as '0755' or 'u+rw'
     *
     * @return bool     Returns true if the permission was properly set
     */
    private function setPermsOnItem($path, $perms)
    {
        if (($result = BootstrapUtils::chmod($path, $perms)) === false) {
            $this->log("ERROR: Couldn't set permissions of $path<br/>");
        } else {
            $this->log("Set permissions of $path<br/>");
        }
        return $result;
    }

    /**
     * Logs a string to the dup-installer-bootlog__[HASH].txt file
     *
     * @param string $s         The string to log to the log file
     * @param bool   $deleteOld if true delete old file
     *
     * @return false|int This function returns the number of bytes that were written to the file, or FALSE on failure.
     */
    public function log($s, $deleteOld = false)
    {
        static $logfile = null;
        if (is_null($logfile)) {
            $logfile = $this->getBootLogFilePath();
        }
        if ($deleteOld && file_exists($logfile)) {
            @unlink($logfile);
        }
        $timestamp = date('M j H:i:s');
        return @file_put_contents($logfile, '[' . $timestamp . '] ' . $this->postprocessLog($s) . "\n", FILE_APPEND);
    }

    /**
     * get boot log file name the dup-installer-bootlog__[HASH].txt file
     *
     * @return string
     */
    public function getBootLogFilePath()
    {
        return $this->targetRoot . '/dup-installer-bootlog__' . $this->secondaryHash . '.txt';
    }

    /**
     * Post process log and remove hash string
     *
     * @param string $str string
     *
     * @return string
     */
    protected function postprocessLog($str)
    {
        return str_replace(array(
        $this->getArchiveFileHash(),
        $this->packageHash,
        $this->secondaryHash
        ), '[HASH]', $str);
    }

    /**
     * Return archive file hash
     *
     * @return string
     */
    public function getArchiveFileHash()
    {
        static $fileHash = null;
        if (is_null($fileHash)) {
            $fileHash = preg_replace('/^.+_([a-z0-9]+)_[0-9]{14}_archive\.(?:daf|zip)$/', '$1', $this->archiveFileName);
        }
        return $fileHash;
    }

    /**
     * Extraxt installer
     *
     * @param string $archive_filepath  The path to the archive file.
     * @param string $origDupInstFolder relative folder in archive
     * @param string $destination       destination folder
     * @param bool   $checkSubFolder    check if is in subfolder
     *
     * @return bool Returns true if the data was properly extracted
     */
    private function extractInstallerZipArchive($archive_filepath, $origDupInstFolder, $destination, $checkSubFolder = false)
    {
        $success              = true;
        $zipArchive           = new ZipArchive();
        $subFolderArchiveList = array();

        if (($zipOpenRes = $zipArchive->open($archive_filepath)) !== true) {
            $this->log("[ERROR] Couldn't open archive archive file with ZipArchive CODE[" . $zipOpenRes . "]");
            return false;
        }

        if (strlen($this->archivePwd)) {
            $zipArchive->setPassword($this->archivePwd);
        }

        $this->log("Successfully opened archive file.");
        $folder_prefix = $origDupInstFolder . '/';
        $this->log("Extracting all files from archive within " . $origDupInstFolder);

        $this->installerFilesFound = 0;

        for ($i = 0; $i < $zipArchive->numFiles; $i++) {
            $stat = $zipArchive->statIndex($i);
            if ($checkSubFolder == false) {
                $filenameCheck = $stat['name'];
                $filename      = $stat['name'];
                $tmpSubFolder  = null;
            } else {
                $safePath = rtrim(BootstrapUtils::setSafePath($stat['name']), '/');
                $tmpArray = explode('/', $safePath);

                if (count($tmpArray) < 2) {
                    continue;
                }

                $tmpSubFolder = $tmpArray[0];
                array_shift($tmpArray);
                $filenameCheck = implode('/', $tmpArray);
                $filename      = $stat['name'];
            }

            if (!BootstrapUtils::startsWith($filenameCheck, $folder_prefix)) {
                continue;
            }

            $this->installerFilesFound++;

            if (!empty($tmpSubFolder) && !in_array($tmpSubFolder, $subFolderArchiveList)) {
                $subFolderArchiveList[] = $tmpSubFolder;
            }

            if (basename($filename) === $this->manualExtractFileName) {
                $this->log("Skipping manual extract file: {$filename}");
                continue;
            }

            if ($zipArchive->extractTo($destination, $filename) === true) {
                $this->log("Success: {$filename} >>> {$destination}");
            } else {
                $this->log("[ERROR] Error extracting {$filename} >>> {$destination}");
                $success = false;
                break;
            }
        }

        if ($checkSubFolder && count($subFolderArchiveList) !== 1) {
            $this->log("Error: Multiple dup subfolder archive");
            $success = false;
        } else {
            if ($checkSubFolder) {
                $this->moveUpfromSubFolder($destination . '/' . $subFolderArchiveList[0], true);
            }

            $lib_directory     = $destination . '/' . $origDupInstFolder . '/lib';
            $snaplib_directory = $lib_directory . '/snaplib';

            // If snaplib files aren't present attempt to extract and copy those
            if (!file_exists($snaplib_directory)) {
                $folder_prefix = 'snaplib/';
                $destination   = $lib_directory;

                for ($i = 0; $i < $zipArchive->numFiles; $i++) {
                    $stat     = $zipArchive->statIndex($i);
                    $filename = $stat['name'];

                    if (BootstrapUtils::startsWith($filename, $folder_prefix)) {
                        $this->installerFilesFound++;

                        if ($zipArchive->extractTo($destination, $filename) === true) {
                            $this->log("Success: {$filename} >>> {$destination}");
                        } else {
                            $this->log("[ERROR] Error extracting {$filename} from archive archive file");
                            $success = false;
                            break;
                        }
                    }
                }
            }
        }

        if ($zipArchive->close() === true) {
            $this->log("Successfully closed archive file");
        } else {
            $this->log("[ERROR] Problem closing archive file");
            $success = false;
        }

        if ($success != false && $this->installerFilesFound < 10) {
            if ($checkSubFolder) {
                $this->log("[ERROR] Couldn't find the installer directory in the archive!");
                $success = false;
            } else {
                $this->log("[ERROR] Couldn't find the installer directory in archive root! Check subfolder");
                return $this->extractInstallerZipArchive($archive_filepath, $origDupInstFolder, $destination, true);
            }
        }

        return $success;
    }

    /**
     * return true if current SO is windows
     *
     * @return bool
     */
    public static function isWindows()
    {
        static $isWindows = null;
        if (is_null($isWindows)) {
            $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        }
        return $isWindows;
    }

    /**
     * @param string $directory Path for folder to set perms
     *
     * @return void
     */
    public static function setPermsToDefaultR($directory)
    {
        $dir      = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);
        // Default permissions
        $defaultFilePermission = 0666 & ~umask();
        $defaultDirPermission  = 0777 & ~umask();

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                BootstrapUtils::chmod($item->getPathname(), $defaultFilePermission);
            }

            if ($item->isDir()) {
                BootstrapUtils::chmod($item->getPathname(), $defaultDirPermission);
            }
        }
    }

    /**
     * Check if input is  valid int
     *
     * @param mixed $input input string or number
     *
     * @return bool
     */
    public static function checkInputValidInt($input)
    {
        return (filter_var($input, FILTER_VALIDATE_INT) === 0 || filter_var($input, FILTER_VALIDATE_INT));
    }

    /**
     * move all folder content up to parent
     *
     * @param string  $subFolderName   full path
     * @param boolean $deleteSubFolder if true delete subFolder after moved all
     *
     * @return boolean
     */
    private function moveUpfromSubFolder($subFolderName, $deleteSubFolder = false)
    {
        if (!is_dir($subFolderName)) {
            return false;
        }

        $parentFolder = dirname($subFolderName);
        if (!is_writable($parentFolder)) {
            return false;
        }

        $success = true;
        if (($subList = glob(rtrim($subFolderName, '/') . '/*', GLOB_NOSORT)) === false) {
            $this->log("[ERROR] Problem glob folder " . $subFolderName);
            return false;
        } else {
            foreach ($subList as $cName) {
                $destination = $parentFolder . '/' . basename($cName);
                if (file_exists($destination)) {
                    $success = BootstrapUtils::rrmdir($destination);
                }

                if ($success) {
                    $success = rename($cName, $destination);
                } else {
                    break;
                }
            }

            if ($success && $deleteSubFolder) {
                $success = BootstrapUtils::rrmdir($subFolderName);
            }
        }

        if (!$success) {
            $this->log("[ERROR] Problem om moveUpfromSubFolder subFolder:" . $subFolderName);
        }

        return $success;
    }

    /**
     * Extracts only the 'dup-installer' files using Shell-Exec Unzip
     *
     * @param string $archive_filepath  The path to the archive file.
     * @param string $origDupInstFolder dup-installer folder
     * @param string $destination       destination folder
     *
     * @return bool
     */
    private function extractInstallerShellexec($archive_filepath, $origDupInstFolder, $destination)
    {
        $success = false;
        $this->log("Attempting to use Shell Exec");
        $unzip_filepath = BootstrapUtils::getUnzipFilePath();

        if ($unzip_filepath == null) {
            return false;
        }

        $params = "-o -q";
        if (strlen($this->archivePwd)) {
            $params .= ' -P ' . escapeshellarg($this->archivePwd);
        }
        $unzip_command = escapeshellcmd($unzip_filepath) . ' ' . $params . ' ' .
        escapeshellarg($archive_filepath) . ' ' .
        escapeshellarg($origDupInstFolder . '/*') .
        ' -d ' . escapeshellarg($destination) .
        ' -x ' . escapeshellarg($origDupInstFolder . '/' . $this->manualExtractFileName) . ' 2>&1';
        $this->log("Executing $unzip_command");

        $shellOutput = Shell::runCommand($unzip_command, Shell::AVAILABLE_COMMANDS);
        if ($shellOutput !== false && $shellOutput->isEmpty()) {
            $this->log("Shell exec unzip succeeded");
            $success = true;
        } else {
            $this->log("[ERROR] Shell exec unzip failed. Output={$shellOutput->getOutputAsString()}");
        }

        return $success;
    }

    /**
     * Attempts to get the archive file path
     *
     * @return string   The full path to the archive file
     */
    private function getArchiveFilePath()
    {
        if (($archive_filepath = filter_input(INPUT_GET, 'archive', FILTER_SANITIZE_SPECIAL_CHARS)) != false) {
            if (is_dir($archive_filepath) && file_exists($archive_filepath . '/' . $this->archiveFileName)) {
                $archive_filepath = $archive_filepath . '/' . $this->archiveFileName;
            } else {
                $archive_filepath = $archive_filepath;
            }
        } else {
            $archive_filepath = $this->targetRoot . '/' . $this->archiveFileName;
        }

        if (($realPath = realpath($archive_filepath)) !== false) {
            return $realPath;
        } else {
            return $archive_filepath;
        }
    }

    /**
     * Gets the enum type that should be used
     *
     * @return int Returns the current zip mode enum
     */
    private function getZipMode()
    {
        $zip_mode = self::ZIP_MODE_AUTO;

        if (isset($_GET['zipmode'])) {
            $zipmode_string = $_GET['zipmode'];
            $this->log("Unzip mode specified in querystring: $zipmode_string");

            switch ($zipmode_string) {
                case 'autounzip':
                    $zip_mode = self::ZIP_MODE_AUTO;
                    break;
                case 'ziparchive':
                    $zip_mode = self::ZIP_MODE_ARCHIVE;
                    break;
                case 'shellexec':
                    $zip_mode = self::ZIP_MODE_SHELL;
                    break;
            }
        }

        switch ($zip_mode) {
            case self::ZIP_MODE_AUTO:
            case self::ZIP_MODE_ARCHIVE:
                if (
                    BootstrapUtils::isPhpZipAvaiable() && (
                        !$this->isArchiveEncrypted() ||
                        !version_compare(BootstrapUtils::getLibzipVersion(), '1.2.0', '<')
                    )
                ) {
                    return self::ZIP_MODE_ARCHIVE;
                } elseif (Shell::test()) {
                    return self::ZIP_MODE_SHELL;
                } else {
                    return self::ZIP_MODE_NONE;
                }
            case self::ZIP_MODE_SHELL:
                if (Shell::test()) {
                    return self::ZIP_MODE_SHELL;
                } elseif (
                    BootstrapUtils::isPhpZipAvaiable() && (
                        !$this->isArchiveEncrypted() ||
                        !version_compare(BootstrapUtils::getLibzipVersion(), '1.2.0', '<')
                    )
                ) {
                    return self::ZIP_MODE_ARCHIVE;
                } else {
                    return self::ZIP_MODE_NONE;
                }
            default:
                return self::ZIP_MODE_NONE;
        }
    }

    /**
     * Returns an array of zip files found in the current executing directory
     *
     * @param string $extension extenstion file
     *
     * @return string[] of ZIP files
     */
    public function getFilesWithExtension($extension)
    {
        $files = array();
        foreach (glob("*.{$extension}") as $name) {
            if (file_exists($name)) {
                $files[] = $name;
            }
        }
        if (count($files) > 0) {
            return $files;
        }
        //FALL BACK: Windows XP has bug with glob,
        //add secondary check for PHP lameness
        if (($dh = opendir($this->targetRoot))) {
            while (false !== ($name = readdir($dh))) {
                $ext = substr($name, strrpos($name, '.') + 1);
                if (in_array($ext, array($extension))) {
                    $files[] = $name;
                }
            }
            closedir($dh);
        }

        return $files;
    }

    /**
     * Get the value of version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the value of secondaryHash
     *
     * @return string
     */
    public function getSecondaryHash()
    {
        return $this->secondaryHash;
    }

    /**
     * Check if is bridge install
     *
     * @return bool
     */
    public static function isBridgeInstall()
    {
        return defined('DUPLICATOR_MU_PLUGIN_VERSION');
    }
}
