<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Duplicator\Libs\DupArchive\DupArchive;
use Duplicator\Libs\Snap\SnapIO;
use Exception;

/**
 * File header
 */
class DupArchiveFileHeader extends AbstractDupArchiveHeader
{
    const MAX_SIZE_FOR_HASHING = 1000000000;

    /** @var int<0, max> */
    public $fileSize = 0;
    /** @var int<0, max> */
    public $mtime = 0;
    /** @var string */
    public $permissions = '';
    /** @var string */
    public $hash = '';
    /** @var int<0, max> */
    public $relativePathLength = 0;
    /** @var string */
    public $relativePath = '';
    /** @var int<0, max>  bitmask*/
    protected $flags = 0;
    /** @var string */
    protected $version = '';
    /** @var string */
    protected $password = '';
    /** @var bool */
    protected $applyExtraHash = true;

    /**
     * Class Contructor
     *
     * @param DupArchiveHeader $archiveHeader archive header
     */
    public function __construct(DupArchiveHeader $archiveHeader)
    {
        $this->flags          = $archiveHeader->getFlags();
        $this->version        = $archiveHeader->getVersion();
        $this->password       = $archiveHeader->getPassword();
        $this->applyExtraHash = version_compare($this->version, '5.0.1', '>=');
    }

    /**
     * Return Dup archive version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Return archive flags
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set the value of flags
     *
     * @param int $flags archive flags
     *
     * @return void
     */
    public function setFlags($flags)
    {
        $this->flags = (int) $flags;
    }

    /**
     * Return true if archvie is compressed
     *
     * @return bool
     */
    public function isCompressed()
    {
        return ($this->flags & DupArchive::FLAG_COMPRESS ? true : false);
    }

    /**
     * True if default of archive is the encryption
     *
     * @return bool
     */
    public function isCrypt()
    {
        return ($this->flags & DupArchive::FLAG_CRYPT ? true : false);
    }

    /**
     * Return archvie password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Return true if extra hash is applied to crypt function
     *
     * @return bool
     */
    public function isApplyExtraHash()
    {
        return $this->applyExtraHash;
    }

    /**
     * create header from file
     *
     * @param string $filepath         file path
     * @param string $relativeFilePath relative file path in archive
     *
     * @return static
     */
    public function createFromFile($filepath, $relativeFilePath)
    {
        $this->fileSize    = SnapIO::filesize($filepath);
        $this->permissions = substr(sprintf('%o', fileperms($filepath)), -4);
        $this->mtime       = SnapIO::filemtime($filepath);

        if ($this->fileSize > self::MAX_SIZE_FOR_HASHING) {
            $this->hash = "00000000000000000000000000000000";
        } else {
            $this->hash = hash_file(DupArchive::HASH_ALGO, $filepath);
        }

        $this->relativePath       = $relativeFilePath;
        $this->relativePathLength = strlen($this->relativePath);

        return $this;
    }

    /**
     * create header from src
     *
     * @param string $src              source string
     * @param string $relativeFilePath relative path in archvie
     * @param int    $forceSize        if 0 size is auto of content is filled of \0 char to size
     *
     * @return static
     */
    public function createFromSrc($src, $relativeFilePath, $forceSize = 0)
    {
        $this->fileSize    = strlen($src);
        $this->permissions = '0644';
        $this->mtime       = time();

        $srcLen = strlen($src);

        if ($forceSize > 0 && $srcLen < $forceSize) {
            $charsToAdd = $forceSize - $srcLen;
            $src       .= str_repeat("\0", $charsToAdd);
        }

        if ($this->fileSize > self::MAX_SIZE_FOR_HASHING) {
            $this->hash = "00000000000000000000000000000000";
        } else {
            $this->hash = hash(DupArchive::HASH_ALGO, $src);
        }

        $this->relativePath       = $relativeFilePath;
        $this->relativePathLength = strlen($this->relativePath);

        return $this;
    }

    /**
     * Check file hash
     *
     * @param string $filePath file to validate
     *
     * @return bool
     */
    public function validateFile($filePath)
    {
        if ($this->hash === '00000000000000000000000000000000') {
            return true;
        }

        $hash = hash_file(DupArchive::HASH_ALGO, $filePath);
        return ($hash === $this->hash);
    }

    /**
     * Write header to archive
     *
     * @param resource $archiveHandle archive resource
     *
     * @return int bytes written
     */
    public function writeToArchive($archiveHandle)
    {
        $headerString  = '<F>';
        $headerString .= '<FS>' . $this->fileSize . '</FS>';
        $headerString .= '<MT>' . $this->mtime . '</MT>';
        $headerString .= '<P>' . $this->permissions . '</P>';
        $headerString .= '<X>' . pack('v', $this->flags) . '</X>';
        $headerString .= '<HA>' . $this->hash . '</HA>';
        $headerString .= '<RPL>' . $this->relativePathLength . '</RPL>';
        $headerString .= '<RP>' . $this->relativePath . '</RP>';
        $headerString .= '</F>';

        //SnapIO::fwrite($archiveHandle, $headerString);
        $bytes_written = @fwrite($archiveHandle, $headerString);

        if ($bytes_written === false) {
            throw new Exception('Error writing to file.', DupArchive::EXCEPTION_CODE_EXTRACT_ERROR);
        } else {
            return $bytes_written;
        }
    }

    /**
     * Read header form archive
     * delta = 84-22 = 62 bytes per file -> 20000 files -> 1.2MB larger
     * <F><FS>x</FS><MT>x</<MT><FP>x</FP><HA>x</HA><RFPL>x</RFPL><RFP>x</RFP></F>
     * # F#x#x#x#x#x#x!
     *
     * @param resource $archiveHandle archive resource
     * @param boolean  $skipContents  if true skip contents
     * @param boolean  $skipMarker    if true skip marker
     *
     * @return static|false
     */
    public function readFromArchive($archiveHandle, $skipContents = false, $skipMarker = false)
    {
        // RSR TODO Read header from archive handle and populate members
        // TODO: return null if end of archive or throw exception if can read something but its not a file header

        if (!$skipMarker) {
            $marker = @fread($archiveHandle, 3);

            if ($marker === false) {
                if (feof($archiveHandle)) {
                    return false;
                } else {
                    throw new Exception('Error reading file header', DupArchive::EXCEPTION_CODE_EXTRACT_ERROR);
                }
            }

            if ($marker != '<F>') {
                throw new Exception(
                    "Invalid file header marker found [{$marker}] : location " . ftell($archiveHandle),
                    DupArchive::EXCEPTION_CODE_INVALID_MARKER
                );
            }
        }

        $this->fileSize    = (int) self::getHeaderField($archiveHandle, 'FS');
        $this->mtime       = (int) self::getHeaderField($archiveHandle, 'MT');
        $this->permissions = self::getHeaderField($archiveHandle, 'P');

        if (version_compare($this->version, '5.0.0', '<')) {
        } else {
            $falgs       = self::getHeaderField($archiveHandle, 'X');
            $this->flags = unpack('vflags', $falgs)['flags'];
        }

        $this->hash               = self::getHeaderField($archiveHandle, 'HA');
        $this->relativePathLength = (int) self::getHeaderField($archiveHandle, 'RPL');

        // Skip <RP>
        fread($archiveHandle, 4);
        $this->relativePath = fread($archiveHandle, $this->relativePathLength);

        // Skip </RP>
        // fread($archiveHandle, 5);

        // Skip the </F>
        // fread($archiveHandle, 4);

        // Skip the </RP> and the </F>
        fread($archiveHandle, 9);

        if ($skipContents && ($this->fileSize > 0)) {
            $dataSize   = 0;
            $moreGlobs  = true;
            $globHeader = new DupArchiveGlobHeader($this);
            while ($moreGlobs) {
                $globHeader->readFromArchive($archiveHandle, true);
                $dataSize += $globHeader->originalSize;
                $moreGlobs = ($dataSize < $this->fileSize);
            }
        }

        return $this;
    }
}
