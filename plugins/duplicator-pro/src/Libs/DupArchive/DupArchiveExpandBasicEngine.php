<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\DupArchive;

use Duplicator\Libs\DupArchive\Headers\DupArchiveDirectoryHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveGlobHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;
use Duplicator\Libs\DupArchive\Info\DupArchiveExpanderInfo;
use Exception;

class DupArchiveExpandBasicEngine extends DupArchive
{
    /** @var ?callable */
    protected static $logCallback = null;
    /** @var ?callable */
    protected static $chmodCallback = null;
    /** @var ?callable */
    protected static $mkdirCallback = null;

    /**
     * Set callabcks function
     *
     * @param ?callable $log   function callback
     * @param ?callable $chmod function callback
     * @param ?callable $mkdir function callback
     *
     * @return void
     */
    public static function setCallbacks($log, $chmod, $mkdir)
    {
        self::$logCallback   = (is_callable($log) ? $log : null);
        self::$chmodCallback = (is_callable($chmod) ? $chmod : null);
        self::$mkdirCallback = (is_callable($mkdir) ? $mkdir : null);
    }

    /**
     * Write log
     *
     * @param string $s     string
     * @param bool   $flush if true flush file
     *
     * @return void
     */
    public static function log($s, $flush = false)
    {
        if (self::$logCallback == null) {
            return;
        }
        call_user_func(self::$logCallback, "MINI EXPAND:$s", $flush);
    }

    /**
     * Expand folder
     *
     * @param string $archivePath  archive path
     * @param string $relativePath relative path
     * @param string $destPath     dest path
     * @param string $password     ecrypt password, if empty archive isn't ecrypted
     * @param bool   $ignoreErrors if true ignore errors
     * @param int    $offset       start scan location
     *
     * @return void
     */
    public static function expandDirectory($archivePath, $relativePath, $destPath, $password, $ignoreErrors = false, $offset = 0)
    {
        self::expandItems($archivePath, $relativePath, $destPath, $password, $ignoreErrors, $offset);
    }

    /**
     * Expand items
     *
     * @param string $archivePath     archive path
     * @param string $inclusionFilter filters
     * @param string $destDirectory   dest path
     * @param string $password        ecrypt password, if empty archive isn't ecrypted
     * @param bool   $ignoreErrors    if true ignore errors
     * @param int    $offset          start scan location
     *
     * @return void
     */
    private static function expandItems($archivePath, $inclusionFilter, $destDirectory, $password, $ignoreErrors = false, $offset = 0)
    {
        $archiveHandle = fopen($archivePath, 'rb');

        if ($archiveHandle === false) {
            throw new Exception("Can’t open archive at $archivePath!", self::EXCEPTION_CODE_OPEN_ERROR);
        }

        $archiveHeader = (new DupArchiveHeader())->readFromArchive($archiveHandle, $password);
        $writeInfo     = new DupArchiveExpanderInfo();

        $writeInfo->destDirectory = $destDirectory;

        if ($offset > 0) {
            fseek($archiveHandle, $offset);
        }

        $moreToRead = true;

        while ($moreToRead) {
            if ($writeInfo->currentFileHeader != null) {
                try {
                    if (self::passesInclusionFilter($inclusionFilter, $writeInfo->currentFileHeader->relativePath)) {
                        self::writeToFile($archiveHandle, $writeInfo);
                        $writeInfo->fileWriteCount++;
                    } elseif ($writeInfo->currentFileHeader->fileSize > 0) {
                        self::skipFileInArchive($archiveHandle, $writeInfo->currentFileHeader);
                    }
                    $writeInfo->currentFileHeader = null;
                    // Expand state taken care of within the write to file to ensure consistency
                } catch (Exception $ex) {
                    if (!$ignoreErrors) {
                        throw $ex;
                    }
                }
            } else {
                $headerType = self::getNextHeaderType($archiveHandle);

                switch ($headerType) {
                    case self::HEADER_TYPE_FILE:
                        $writeInfo->currentFileHeader = (new DupArchiveFileHeader($archiveHeader))->readFromArchive($archiveHandle, false, true);
                        break;
                    case self::HEADER_TYPE_DIR:
                        $directoryHeader = (new DupArchiveDirectoryHeader($archiveHeader))->readFromArchive($archiveHandle, true);
                        //   self::log("considering $inclusionFilter and {$directoryHeader->relativePath}");
                        if (self::passesInclusionFilter($inclusionFilter, $directoryHeader->relativePath)) {
                            //    self::log("passed");
                            $directory = "{$writeInfo->destDirectory}/{$directoryHeader->relativePath}";

                            //  $mode = $directoryHeader->permissions;
                            // rodo handle this more elegantly @mkdir($directory, $directoryHeader->permissions, true);
                            if (is_callable(self::$mkdirCallback)) {
                                call_user_func(self::$mkdirCallback, $directory, 'u+rwx', true);
                            } else {
                                mkdir($directory, 0755, true);
                            }
                            $writeInfo->directoryWriteCount++;
                        } else {
                            // self::log("didnt pass");
                        }
                        break;
                    case self::HEADER_TYPE_NONE:
                        $moreToRead = false;
                }
            }
        }

        fclose($archiveHandle);
    }

    /**
     * Write to file
     *
     * @param resource               $archiveHandle archive file handle
     * @param DupArchiveExpanderInfo $writeInfo     write info
     *
     * @return void
     */
    private static function writeToFile($archiveHandle, DupArchiveExpanderInfo $writeInfo)
    {
        $destFilePath = $writeInfo->getCurrentDestFilePath();

        if ($writeInfo->currentFileHeader->fileSize > 0) {
            $parentDir = dirname($destFilePath);
            if (!file_exists($parentDir)) {
                if (is_callable(self::$mkdirCallback)) {
                    $res = call_user_func(self::$mkdirCallback, $parentDir, 'u+rwx', true);
                } else {
                    $res = mkdir($parentDir, 0755, true);
                }
                if (!$res) {
                    throw new Exception("Couldn't create {$parentDir}", self::EXCEPTION_CODE_EXTRACT_ERROR);
                }
            }

            $destFileHandle = fopen($destFilePath, 'wb+');
            if ($destFileHandle === false) {
                throw new Exception("Couldn't open {$destFilePath} for writing.", self::EXCEPTION_CODE_OPEN_ERROR);
            }

            do {
                self::appendGlobToFile($archiveHandle, $destFileHandle, $writeInfo);

                $currentFileOffset = ftell($destFileHandle);

                $moreGlobstoProcess = $currentFileOffset < $writeInfo->currentFileHeader->fileSize;
            } while ($moreGlobstoProcess);

            fclose($destFileHandle);

            if (is_callable(self::$chmodCallback)) {
                call_user_func(self::$chmodCallback, $destFilePath, 'u+rw');
            } else {
                chmod($destFilePath, 0644);
            }

            if ($writeInfo->currentFileHeader->validateFile($destFilePath) == false) {
                throw new Exception("HASH Validation fails for {$destFilePath}", self::EXCEPTION_CODE_VALIDATION_ERROR);
            }
        } else {
            if (touch($destFilePath) === false) {
                throw new Exception("Couldn't create $destFilePath", self::EXCEPTION_CODE_EXTRACT_ERROR);
            }

            if (is_callable(self::$chmodCallback)) {
                call_user_func(self::$chmodCallback, $destFilePath, 'u+rw');
            } else {
                chmod($destFilePath, 0644);
            }
        }
    }

    /**
     * Undocumented function
     * Assumption is that archive handle points to a glob header on this call
     *
     * @param resource               $archiveHandle  archive handle
     * @param resource               $destFileHandle dest file handle
     * @param DupArchiveExpanderInfo $writeInfo      write info
     *
     * @return void
     */
    private static function appendGlobToFile($archiveHandle, $destFileHandle, DupArchiveExpanderInfo $writeInfo)
    {
        $globHeader  = (new DupArchiveGlobHeader($writeInfo->currentFileHeader))->readFromArchive($archiveHandle);
        $globContent = $globHeader->readContent($archiveHandle);

        if (fwrite($destFileHandle, $globContent) !== strlen($globContent)) {
            throw new Exception("Unable to write all bytes of data glob to storage.", self::EXCEPTION_CODE_EXTRACT_ERROR);
        }
    }

    /**
     * Check filter
     *
     * @param string $filter    filter
     * @param string $candidate candidate
     *
     * @return bool
     */
    private static function passesInclusionFilter($filter, $candidate)
    {
        return (substr($candidate, 0, strlen($filter)) == $filter);
    }
}
