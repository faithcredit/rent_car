<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Duplicator\Libs\DupArchive\DupArchive;
use Exception;

/**
 * Dup archive glob header
 *
 * Format
 * #C#{$originalSize}#{$storedSize}!
 */
class DupArchiveGlobHeader extends AbstractDupArchiveHeader
{
    /** @var int */
    public $originalSize = 0;
    /** @var int */
    public $storedSize = 0;
    /** @var string */
    protected $hash = '';
    /** @var DupArchiveFileHeader */
    protected $fileHeader =  null;

    /**
     * Class constructor
     *
     * @param DupArchiveFileHeader $fileHeader file header
     */
    public function __construct(DupArchiveFileHeader $fileHeader)
    {
        $this->fileHeader = $fileHeader;
    }

    /**
     * Set hash by content
     *
     * @param string $content glob content
     *
     * @return void
     */
    public function setHash($content)
    {
        $this->hash = hash(DupArchive::HASH_ALGO, $content);
    }

    /**
     * Check hash validation
     *
     * @param string $content       original content (not encrypted, not compressed)
     * @param string $storedContent stored content (for old DupArchvie version)
     *
     * @return bool true on success
     */
    public function checkHash($content, $storedContent)
    {
        if (version_compare($this->fileHeader->getVersion(), '5.0.0', '<')) {
            $hash = hash(DupArchive::HASH_ALGO, $storedContent);
        } else {
            $hash = hash(DupArchive::HASH_ALGO, $content);
        }

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
        // <G><OS>x</OS>x<SS>x</SS><HA>x</HA></G>

        $headerString = '<G><OS>' . $this->originalSize . '</OS><SS>' . $this->storedSize . '</SS><HA>' . $this->hash . '</HA></G>';

        //SnapIO::fwrite($archiveHandle, $headerString);
        $bytes_written = @fwrite($archiveHandle, $headerString);

        if ($bytes_written === false) {
            throw new Exception('Error writing to file.');
        } else {
            return $bytes_written;
        }
    }

    /**
     * Read chunk file header from archive
     *
     * @param resource $archiveHandle archive file resource
     * @param bool     $skipGlob      if true skip glob content
     *
     * @return static
     */
    public function readFromArchive($archiveHandle, $skipGlob = false)
    {
        $startElement = fread($archiveHandle, 3);

        //if ($marker != '?G#') {
        if ($startElement !== '<G>') {
            throw new Exception("Invalid glob header marker found {$startElement}. location:" . ftell($archiveHandle));
        }

        $this->originalSize = (int) self::getHeaderField($archiveHandle, 'OS');
        $this->storedSize   = (int) self::getHeaderField($archiveHandle, 'SS');
        $this->hash         = self::getHeaderField($archiveHandle, 'HA');

        // Skip the </G>
        fread($archiveHandle, 4);

        if ($skipGlob) {
            if (fseek($archiveHandle, $this->storedSize, SEEK_CUR) === -1) {
                throw new Exception("Can't fseek when skipping glob at location:" . ftell($archiveHandle));
            }
        }

        return $this;
    }

    /**
     * Return glob content to write
     *
     * @param string $content content to write
     *
     * @return string
     */
    public function getContentToWrite($content)
    {
        if ($this->fileHeader->isCompressed()) {
            if (($content = gzdeflate($content, 2)) === false) {
                throw new Exception("Error gzdeflate glob content", DupArchive::EXCEPTION_CODE_EXTRACT_ERROR);
            }
        }

        if ($this->fileHeader->isCrypt()) {
            if (($content = DupArchive::encrypt($content, $this->fileHeader->getPassword(), true)) === false) {
                throw new Exception("Error encrypt glob content", DupArchive::EXCEPTION_CODE_EXTRACT_ERROR);
            }
        }

        return $content;
    }

    /**
     * Get glob content from header, thow excption on failure
     *
     * @param resource $archiveHandle archive hadler
     * @param bool     $validate      i true validate content
     *
     * @return string
     */
    public function readContent($archiveHandle, $validate = false)
    {
        if ($this->storedSize == 0) {
            return '';
        }

        if (($content = fread($archiveHandle, $this->storedSize)) === false) {
            throw new Exception("Error reading glob content");
        }

        $result = $content;
        if ($this->fileHeader->isCrypt()) {
            if (($result = DupArchive::decrypt($result, $this->fileHeader->getPassword(), $this->fileHeader->isApplyExtraHash())) === false) {
                throw new Exception("Error decrypt glob content");
            }
        }

        if ($this->fileHeader->isCompressed()) {
            if (($result = gzinflate($result)) === false) {
                throw new Exception("Error gzinflate glob content");
            }
        }

        if ($validate && $this->checkHash($result, $content) == false) {
            throw new Exception('Glob validation fail');
        }

        return  $result;
    }
}
