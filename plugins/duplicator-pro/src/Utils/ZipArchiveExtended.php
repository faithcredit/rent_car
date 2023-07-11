<?php

/**
 * @package Duplicator
 */

namespace Duplicator\Utils;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;
use ZipArchive;

class ZipArchiveExtended
{
    /** @var string */
    protected $archivePath = '';
    /** @var ZipArchive */
    protected $zipArchive = null;
    /** @var bool */
    protected $isOpened = false;
    /** @var bool */
    protected $compressed = false;
    /** @var bool */
    protected $encrypt = false;
    /** @var string */
    protected $password = '';

    /**
     * Class constructor
     *
     * @param string $path zip archive path
     */
    public function __construct($path)
    {
        if (!self::isPhpZipAvaiable()) {
            throw new Exception('ZipArchive class don\'t exists');
        }
        if (file_exists($path) && (!is_file($path) || !is_writeable($path))) {
            throw new Exception('File ' . SnapLog::v2str($path) . 'exists but isn\'t valid');
        }

        $this->archivePath = $path;
        $this->zipArchive  = new ZipArchive();
        $this->setCompressed(true);
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Check if class ZipArchvie is avaiable
     *
     * @return bool
     */
    public static function isPhpZipAvaiable()
    {
        return SnapUtil::classExists(ZipArchive::class);
    }

    /**
     * Add full dir in archive
     *
     * @param string $dirPath     dir path
     * @param string $archivePath local archive path
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function addDir($dirPath, $archivePath)
    {
        if (!is_dir($dirPath) || !is_readable($dirPath)) {
            return false;
        }

        $dirPath     = SnapIO::safePathTrailingslashit($dirPath);
        $archivePath = SnapIO::safePathTrailingslashit($archivePath);
        $thisObj     = $this;

        return SnapIO::regexGlobCallback(
            $dirPath,
            function ($path) use ($dirPath, $archivePath, $thisObj) {
                $newPath = $archivePath . SnapIO::getRelativePath($path, $dirPath);

                if (is_dir($path)) {
                    $thisObj->addEmptyDir($newPath);
                } else {
                    $thisObj->addFile($path, $newPath);
                }
            },
            array('recursive' => true)
        );
    }

    /**
     * Add empty dir on zip archive
     *
     * @param string $path archive dir to add
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function addEmptyDir($path)
    {
        return $this->zipArchive->addEmptyDir($path);
    }

    /**
     * Add file on zip archive
     *
     * @param string $filepath    file path
     * @param string $archivePath archive path
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function addFile($filepath, $archivePath)
    {
        $result = $this->zipArchive->addFile($filepath, $archivePath);
        if ($result && $this->encrypt) {
            $this->zipArchive->setEncryptionName($archivePath, ZipArchive::EM_AES_256);
        }
        if ($result && !$this->compressed) {
            $this->zipArchive->setCompressionName($archivePath, ZipArchive::CM_STORE);
        }
        return $result;
    }

    /**
     * Open Zip archive, create it if don't exists
     *
     * @return bool|int Returns TRUE on success or the error code. See zip archive
     */
    public function open()
    {
        if ($this->isOpened) {
            return true;
        }

        if (($result = $this->zipArchive->open($this->archivePath, ZipArchive::CREATE)) === true) {
            $this->isOpened = true;
            if ($this->encrypt) {
                $this->zipArchive->setPassword($this->password);
            } else {
                $this->zipArchive->setPassword('');
            }
        }
        return $result;
    }

    /**
     * Close zip archive
     *
     * @return bool True on success or false on failure.
     */
    public function close()
    {
        if (!$this->isOpened) {
            return true;
        }

        $result = false;

        if (($result = $this->zipArchive->close()) === true) {
            $this->isOpened = false;
        }

        return $result;
    }

    /**
     * Get num files in zip archive
     *
     * @return int
     */
    public function getNumFiles()
    {
        $this->open();
        return $this->zipArchive->numFiles;
    }

    /**
     * Get the value of compressed\
     *
     * @return bool
     */
    public function isCompressed()
    {
        return $this->compressed;
    }

    /**
     * Se compression if is avaiable
     *
     * @param bool $compressed if true compress zip archive
     *
     * @return bool return compressd value
     */
    public function setCompressed($compressed)
    {
        if (!method_exists($this->zipArchive, 'setCompressionName')) {
            // If don't exists setCompressionName the archive can't create uncrompressed
            $this->compressed = true;
        } else {
            $this->compressed = $compressed;
        }
        return $this->compressed;
    }

    /**
     * Get the value of encrypt
     *
     * @return bool
     */
    public function isEncrypted()
    {
        return $this->encrypt;
    }

    /**
     * Return true if ZipArchive encryption is avaiable
     *
     * @return bool
     */
    public static function isEncryptionAvaliable()
    {
        static $isEncryptAvaiable = null;
        if ($isEncryptAvaiable === null) {
            if (!self::isPhpZipAvaiable()) {
                $isEncryptAvaiable = false;
                return false;
            }

            $zipArchive = new ZipArchive();
            if (!method_exists($zipArchive, 'setEncryptionName')) {
                $isEncryptAvaiable = false;
                return false;
            }

            if (version_compare(self::getLibzipVersion(), '1.2.0', '<')) {
                $isEncryptAvaiable = false;
                return false;
            }

            $isEncryptAvaiable = true;
        }

        return $isEncryptAvaiable;
    }

    /**
     * Get libzip version
     *
     * @return string
     */
    public static function getLibzipVersion()
    {
        static $zlibVersion =  null;

        if (is_null($zlibVersion)) {
            ob_start();
            SnapUtil::phpinfo(INFO_MODULES);
            $info = ob_get_clean();

            if (preg_match('/<td\s.*?>\s*(libzip.*\sver.+?)\s*<\/td>\s*<td\s.*?>\s*(.+?)\s*<\/td>/i', $info, $matches) !== 1) {
                $zlibVersion = "0";
            } else {
                $zlibVersion = $matches[2];
            }
        }

        return $zlibVersion;
    }

     /**
      * Set encryption
      *
      * @param bool   $encrypt  true if archvie must be encrypted
      * @param string $password password

      * @return bool
      */
    public function setEncrypt($encrypt, $password = '')
    {
        $this->encrypt = (self::isEncryptionAvaliable() ? $encrypt : false);

        if ($this->encrypt) {
            $this->password = $password;
        } else {
            $this->password = '';
        }

        if ($this->isOpened) {
            if ($this->encrypt) {
                $this->zipArchive->setPassword($this->password);
            } else {
                $this->zipArchive->setPassword('');
            }
        }

        return $this->encrypt;
    }
}
