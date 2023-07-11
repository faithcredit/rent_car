<?php

namespace Duplicator\Libs\DupArchive;

use Duplicator\Libs\DupArchive\Headers\DupArchiveDirectoryHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveGlobHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;
use Duplicator\Utils\Crypt\CryptBlowfish;
use Error;
use Exception;

/**
 * Dup archive
 */
class DupArchive
{
    const EXCEPTION_CODE_FILE_DONT_EXISTS = 10;
    const EXCEPTION_CODE_OPEN_ERROR       = 11;
    const EXCEPTION_CODE_INVALID_PASSWORD = 12;
    const EXCEPTION_CODE_INVALID_MARKER   = 13;
    const EXCEPTION_CODE_INVALID_PARAM    = 14;
    const EXCEPTION_CODE_ADD_ERROR        = 15;
    const EXCEPTION_CODE_EXTRACT_ERROR    = 16;
    const EXCEPTION_CODE_VALIDATION_ERROR = 17;

    const DUPARCHIVE_VERSION  = '5.0.1';
    const INDEX_FILE_NAME     = '__dup__archive__index.json';
    const INDEX_FILE_SIZE     = 2000; // reserver 2K
    const EXTRA_FILES_POS_KEY = 'extraPos';

    const HEADER_TYPE_NONE = 0;
    const HEADER_TYPE_FILE = 1;
    const HEADER_TYPE_DIR  = 2;
    const HEADER_TYPE_GLOB = 3;

    const FLAG_COMPRESS = 1; //bitmask
    const FLAG_CRYPT    = 2; //bitmask

    const HASH_ALGO      = 'crc32b';
    const PWD_ALGO       = '$6$rounds=50000$'; // SHA-512 50000 times with salt
    const PWD_SALT_CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#%^-_%^&*()[]{}<>~`+=,.;:/?|';
    const CRYPT_ALGO     = 'AES-256-CBC';

    /**
     * Get header type enum
     *
     * @param resource $archiveHandle archive resource
     *
     * @return int
     */
    protected static function getNextHeaderType($archiveHandle)
    {
        $retVal = self::HEADER_TYPE_NONE;
        $marker = fgets($archiveHandle, 4);

        if (feof($archiveHandle) === false) {
            switch ($marker) {
                case '<D>':
                    $retVal = self::HEADER_TYPE_DIR;
                    break;
                case '<F>':
                    $retVal = self::HEADER_TYPE_FILE;
                    break;
                case '<G>':
                    $retVal = self::HEADER_TYPE_GLOB;
                    break;
                default:
                    throw new Exception("Invalid header marker {$marker}. Location:" . ftell($archiveHandle), self::EXCEPTION_CODE_INVALID_MARKER);
            }
        }

        return $retVal;
    }

    /**
     * Check if archvie is encrypted
     *
     * @param string $path archvie path
     *
     * @return bool
     */
    public static function isEncrypted($path)
    {
        return !self::checkPassword($path, '');
    }

    /**
     * Get archive header from file path
     *
     * @param string $path     archive path
     * @param string $password password archive, empty no password
     *
     * @return bool
     */
    public static function checkPassword($path, $password)
    {
        try {
            $header = self::getArchiveHeader($path, $password);
        } catch (Exception $e) {
            if ($e->getCode() == self::EXCEPTION_CODE_INVALID_PASSWORD) {
                return false;
            } else {
                throw $e;
            }
        }
        return true;
    }

    /**
     * Get archive header from file path
     *
     * @param string $path     archive path
     * @param string $password password archive, empty no password
     *
     * @return DupArchiveHeader
     */
    public static function getArchiveHeader($path, $password)
    {
        try {
            $archiveHandle = null;
            if (!file_exists($path)) {
                throw new Exception('Archive file don\'t exists', self::EXCEPTION_CODE_FILE_DONT_EXISTS);
            }

            if (($archiveHandle = fopen($path, 'r')) == false) {
                throw new Exception('Can\'t open archive file', self::EXCEPTION_CODE_OPEN_ERROR);
            }
            $result = (new DupArchiveHeader())->readFromArchive($archiveHandle, $password);
        } finally {
            if (is_resource($archiveHandle)) {
                fclose($archiveHandle);
            }
        }
        return $result;
    }

    /**
     * Return true if DupArchive encryption is avaiable
     *
     * @return bool
     */
    public static function isEncryptionAvaliable()
    {
        static $isAvaliable = null;
        if ($isAvaliable === null) {
            $isAvaliable = (
                function_exists('openssl_cipher_iv_length') &&
                function_exists('openssl_encrypt') &&
                function_exists('openssl_decrypt')
            );
        }
        return $isAvaliable;
    }

    /**
     * Get archive index data
     *
     * @param string $archivePath archive path
     * @param string $password    password archive, empty no password
     *
     * @return false|mixed[] return index data, false if don't exists
     */
    public static function getIndexData($archivePath, $password)
    {
        try {
            $indexContent = self::getSrcFile($archivePath, self::INDEX_FILE_NAME, $password, 0, 3000);
            if ($indexContent === false) {
                return false;
            }
            $indexData = json_decode(rtrim($indexContent, "\0"), true);

            if (!is_array($indexData)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        } catch (Error $e) {
            return false;
        }

        return $indexData;
    }

    /**
     * Get extra files offset if set or 0
     *
     * @param string $archivePath archive path
     * @param string $password    password archive, empty no password
     *
     * @return int
     */
    public static function getExtraOffset($archivePath, $password)
    {
        if (($indexData = self::getIndexData($archivePath, $password)) === false) {
            return 0;
        }
        return (isset($indexData[self::EXTRA_FILES_POS_KEY]) ? $indexData[self::EXTRA_FILES_POS_KEY] : 0);
    }

    /**
     * Add file in archive from src
     *
     * @param string $archivePath  archive path
     * @param string $relativePath relative path
     * @param string $password     password archive
     * @param int    $offset       start search location
     * @param int    $sizeToSearch max size where search
     *
     * @return bool|int false if file not found of path position
     */
    public static function seachPathInArchive($archivePath, $relativePath, $password, $offset = 0, $sizeToSearch = 0)
    {
        try {
            $archiveHandle = null;
            if (($archiveHandle = fopen($archivePath, 'rb')) === false) {
                throw new Exception("Can’t open archive at $archivePath!", self::EXCEPTION_CODE_OPEN_ERROR);
            }
            $archiveHeader = (new DupArchiveHeader())->readFromArchive($archiveHandle, $password);

            $result = self::searchPath($archiveHandle, $archiveHeader, $relativePath, $offset, $sizeToSearch);
        } finally {
            if (is_resource($archiveHandle)) {
                fclose($archiveHandle);
            }
        }
        return $result;
    }

    /**
     * Search path, if found set and return position
     *
     * @param resource         $archiveHandle dup archive resource
     * @param DupArchiveHeader $archiveHeader archive header
     * @param string           $relativePath  relative path to extract
     * @param int              $offset        start search location
     * @param int              $sizeToSearch  max size where search
     *
     * @return bool|int false if file not found of path position
     */
    public static function searchPath($archiveHandle, DupArchiveHeader $archiveHeader, $relativePath, $offset = 0, $sizeToSearch = 0)
    {
        if (!is_resource($archiveHandle)) {
            throw new Exception('Archive handle must be a resource', self::EXCEPTION_CODE_INVALID_PARAM);
        }

        if (fseek($archiveHandle, $offset, SEEK_SET) < 0) {
            return false;
        }

        if ($offset == 0) {
            $hd = (new DupArchiveHeader())->readFromArchive($archiveHandle, $archiveHeader->getPassword());
        }

        $result   = false;
        $position = ftell($archiveHandle);
        $continue = true;

        do {
            switch (($type = self::getNextHeaderType($archiveHandle))) {
                case self::HEADER_TYPE_FILE:
                    $currentFileHeader = (new DupArchiveFileHeader($archiveHeader))->readFromArchive($archiveHandle, true, true);
                    if ($currentFileHeader->relativePath == $relativePath) {
                        $continue = false;
                        $result   = $position;
                    }
                    break;
                case self::HEADER_TYPE_DIR:
                    $directoryHeader = (new DupArchiveDirectoryHeader($archiveHeader))->readFromArchive($archiveHandle, true);
                    if ($directoryHeader->relativePath == $relativePath) {
                        $continue = false;
                        $result   = $position;
                    }
                    break;
                case self::HEADER_TYPE_NONE:
                    $continue = false;
                    break;
                default:
                    throw new Exception('Invali header type "' . $type . '"', self::EXCEPTION_CODE_INVALID_MARKER);
            }
            $position = ftell($archiveHandle);
            if ($sizeToSearch > 0 && ($position - $offset) >= $sizeToSearch) {
                break;
            }
        } while ($continue);

        if ($result !== false) {
            if (fseek($archiveHandle, $result, SEEK_SET) < 0) {
                return false;
            }
        }
        return $result;
    }

    /**
     * Get file content
     *
     * @param string $archivePath  archvie path
     * @param string $relativePath relative path to extract
     * @param string $password     password archive
     * @param int    $offset       start search location
     * @param int    $sizeToSearch max size where search
     *
     * @return bool|string false if file not found
     */
    public static function getSrcFile($archivePath, $relativePath, $password, $offset = 0, $sizeToSearch = 0)
    {
        try {
            $archiveHandle = null;
            if (($archiveHandle = fopen($archivePath, 'rb')) === false) {
                throw new Exception("Can’t open archive at $archivePath!", self::EXCEPTION_CODE_OPEN_ERROR);
            }
            $archiveHeader = (new DupArchiveHeader())->readFromArchive($archiveHandle, $password);
            if (self::searchPath($archiveHandle, $archiveHeader, $relativePath, $offset, $sizeToSearch) === false) {
                return false;
            }

            if (self::getNextHeaderType($archiveHandle) != self::HEADER_TYPE_FILE) {
                return false;
            }

            $header = (new DupArchiveFileHeader($archiveHeader))->readFromArchive($archiveHandle, false, true);
            $result = self::getSrcFromHeader($archiveHandle, $header);
        } finally {
            if (is_resource($archiveHandle)) {
                fclose($archiveHandle);
            }
        }
        return $result;
    }

    /**
     * Get src file form header
     *
     * @param resource             $archiveHandle archive handle
     * @param DupArchiveFileHeader $fileHeader    file header
     *
     * @return string
     */
    protected static function getSrcFromHeader($archiveHandle, DupArchiveFileHeader $fileHeader)
    {
        if ($fileHeader->fileSize == 0) {
            return '';
        }
        $dataSize = 0;
        $result   = '';

        $globHeader = new DupArchiveGlobHeader($fileHeader);
        do {
            $globHeader->readFromArchive($archiveHandle);
            $result   .= $globHeader->readContent($archiveHandle);
            $dataSize += $globHeader->originalSize;
        } while ($dataSize < $fileHeader->fileSize);

        return $result;
    }

    /**
     * Skip file in archive
     *
     * @param resource             $archiveHandle dup archive resource
     * @param DupArchiveFileHeader $fileHeader    file header
     *
     * @return void
     */
    protected static function skipFileInArchive($archiveHandle, DupArchiveFileHeader $fileHeader)
    {
        if ($fileHeader->fileSize == 0) {
            return;
        }
        $dataSize   = 0;
        $globHeader = new DupArchiveGlobHeader($fileHeader);
        do {
            $globHeader->readFromArchive($archiveHandle, true);
            $dataSize += $globHeader->originalSize;
        } while ($dataSize < $fileHeader->fileSize);
    }

    /**
     * Assumes we are on one header and just need to get to the next
     *
     * @param resource         $archiveHandle dup archive resource
     * @param DupArchiveHeader $archiveHeader archive header
     *
     * @return void
     */
    protected static function skipToNextHeader($archiveHandle, DupArchiveHeader $archiveHeader)
    {
        $headerType = self::getNextHeaderType($archiveHandle);
        switch ($headerType) {
            case self::HEADER_TYPE_FILE:
                $fileHeader = (new DupArchiveFileHeader($archiveHeader))->readFromArchive($archiveHandle, false, true);
                self::skipFileInArchive($archiveHandle, $fileHeader);
                break;
            case self::HEADER_TYPE_DIR:
                $directoryHeader = (new DupArchiveDirectoryHeader($archiveHeader))->readFromArchive($archiveHandle, true);
                break;
            case self::HEADER_TYPE_NONE:
            default:
                break;
        }
    }

    /**
     * Generates a random salt
     *
     * @param int $length salt len
     *
     * @return string The random salt.
     */
    public static function generateSalt($length)
    {
        $maxRand = (strlen(self::PWD_SALT_CHARS) - 1);

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            if (function_exists('random_int')) {
                $cIndex = random_int(0, $maxRand);
            } else {
                mt_srand(time());
                $cIndex = mt_rand(0, $maxRand);
            }
            $password .= substr(self::PWD_SALT_CHARS, $cIndex, 1);
        }

        return $password;
    }

    /**
     * Encrypt content
     *
     * @param string $content content
     * @param string $key     encrypt key
     * @param bool   $hashKey apply additional hash at key
     *
     * @return false|string The encrypted string on success or false on failure.
     */
    public static function encrypt($content, $key, $hashKey = false)
    {
        static $ivLen = null;
        if ($ivLen === null) {
            if (!self::isEncryptionAvaliable()) {
                throw new Exception('Encryption is unavaiable', self::EXCEPTION_CODE_OPEN_ERROR);
            }
            $ivLen = openssl_cipher_iv_length(DupArchive::CRYPT_ALGO);
        }
        $iv = openssl_random_pseudo_bytes($ivLen);

        if ($hashKey) {
            $key = hash('sha256', $key);
        }

        if (($result = openssl_encrypt($content, DupArchive::CRYPT_ALGO, $key, OPENSSL_RAW_DATA, $iv)) === false) {
            return false;
        }

        return $iv . $result;
    }

    /**
     * Decrypt content
     *
     * @param string $content content
     * @param string $key     encrypt key
     * @param bool   $hashKey apply additional hash at key
     *
     * @return string|bool The decrypted string on success or false on failure.
     */
    public static function decrypt($content, $key, $hashKey = false)
    {
        static $ivLen = null;
        if ($ivLen === null) {
            if (!self::isEncryptionAvaliable()) {
                throw new Exception('Encryption is unavaiable', self::EXCEPTION_CODE_OPEN_ERROR);
            }
            $ivLen = openssl_cipher_iv_length(DupArchive::CRYPT_ALGO);
        }
        $iv = substr($content, 0, $ivLen);
        if ($hashKey) {
            $key = hash('sha256', $key);
        }
        return openssl_decrypt(substr($content, $ivLen), DupArchive::CRYPT_ALGO, $key, OPENSSL_RAW_DATA, $iv);
    }
}
