<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\DupArchive;

use Duplicator\Libs\DupArchive\Headers\DupArchiveDirectoryHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveGlobHeader;
use Duplicator\Libs\DupArchive\Info\DupArchiveInfo;
use Duplicator\Libs\DupArchive\Processors\DupArchiveDirectoryProcessor;
use Duplicator\Libs\DupArchive\Processors\DupArchiveFileProcessor;
use Duplicator\Libs\DupArchive\Processors\DupArchiveProcessingFailure;
use Duplicator\Libs\DupArchive\States\DupArchiveCreateState;
use Duplicator\Libs\DupArchive\States\DupArchiveExpandState;
use Duplicator\Libs\DupArchive\Utils\DupArchiveScanUtil;
use Duplicator\Libs\DupArchive\Utils\DupArchiveUtil;
use Duplicator\Libs\Snap\Snap32BitSizeLimitException;
use Duplicator\Libs\Snap\SnapIO;
use Exception;
use ErrorException;
use stdClass;

/**
 * $re = '/\/\/.* /';
 * $subst = '';
 *
 * $re = '/(.*^\s*)(namespace.*?)(;)(.*)/sm';
 * $subst = '$2 { $4}';
 *
 * $re = '/\/\*.*?\*\//s'; ''
 * $re = '/\n\s*\n/s'; "\n"
 */
class DupArchiveEngine extends DupArchive
{
    const EXCEPTION_NON_FATAL = 0;
    const EXCEPTION_FATAL     = 1;

    /** @var string|null */
    public static $targetRootPath = null;

    /**
     * Dup archive init
     *
     * @param DupArchiveLoggerBase $logger         logger object
     * @param string|null          $targetRootPath archive target root path or null not root path
     *
     * @return void
     */
    public static function init(DupArchiveLoggerBase $logger, $targetRootPath = null)
    {
        DupArchiveUtil::$logger = $logger;
        self::$targetRootPath   = $targetRootPath;
    }

    /**
     * Get local path
     *
     * @param string                $path        item path
     * @param DupArchiveCreateState $createState base path
     *
     * @return string
     */
    protected static function getLocalPath($path, DupArchiveCreateState $createState)
    {
        $result = '';
        if (self::$targetRootPath === null) {
            $result = substr($path, $createState->basepathLength);
            $result = ltrim($result, '/');
            if ($createState->newBasePath !== null) {
                $result = $createState->newBasePath . $result;
            }
        } else {
            $safePath = SnapIO::safePathUntrailingslashit($path);
            $result   = ltrim(
                $createState->newBasePath . preg_replace('/^' . preg_quote(self::$targetRootPath, '/') . '(.*)/m', '$1', $safePath),
                '/'
            );
        }
        return $result;
    }

    /**
     * Get archvie info from path
     *
     * @param string $filepath archvie path
     * @param string $password password archive, empty no password
     *
     * @return DupArchiveInfo
     */
    public static function getArchiveInfo($filepath, $password)
    {
        $archiveInfo = new DupArchiveInfo();

        DupArchiveUtil::log("archive size=" . filesize($filepath));
        $archiveHandle              = SnapIO::fopen($filepath, 'rb');
        $archiveInfo->archiveHeader = (new DupArchiveHeader())->readFromArchive($archiveHandle, $password);
        $moreToRead                 = true;

        while ($moreToRead) {
            $headerType = self::getNextHeaderType($archiveHandle);
            // DupArchiveUtil::log("next header type=$headerType: " . ftell($archiveHandle));

            switch ($headerType) {
                case self::HEADER_TYPE_FILE:
                    $fileHeader                 = (new DupArchiveFileHeader($archiveInfo->archiveHeader))->readFromArchive($archiveHandle, true, true);
                    $archiveInfo->fileHeaders[] = $fileHeader;
                    DupArchiveUtil::log("file" . $fileHeader->relativePath);
                    break;
                case self::HEADER_TYPE_DIR:
                    $directoryHeader                 = (new DupArchiveDirectoryHeader($archiveInfo->archiveHeader))->readFromArchive($archiveHandle, true);
                    $archiveInfo->directoryHeaders[] = $directoryHeader;
                    break;
                case self::HEADER_TYPE_NONE:
                    $moreToRead = false;
            }
        }
        return $archiveInfo;
    }

    /**
     * Add folder to archive
     *
     * can't span requests since create state can't store list of files
     *
     * @param string  $archiveFilepath archive file
     * @param string  $directory       folder to add
     * @param string  $archivePath     archive path
     * @param string  $password        password archive, empty no password
     * @param boolean $includeFiles    if true include files
     * @param int     $globSize        global size
     *
     * @return stdClass
     */
    public static function addDirectoryToArchiveST(
        $archiveFilepath,
        $directory,
        $archivePath,
        $password,
        $includeFiles = false,
        $globSize = DupArchiveCreateState::DEFAULT_GLOB_SIZE
    ) {
        if ($includeFiles) {
            $scan = DupArchiveScanUtil::createScanObject($directory);
        } else {
            $scan        = new stdClass();
            $scan->Files = array();
            $scan->Dirs  = array();
        }

        $newBasePath = dirname($archivePath);
        if ($newBasePath == '.' || strlen($newBasePath) == 0) {
            $newBasePath = '';
        } else {
            $newBasePath = ltrim(trailingslashit($newBasePath), '\\/');
        }

        $archiveHeader = self::getArchiveHeader($archiveFilepath, $password);
        $createState   = new DupArchiveCreateState($archiveHeader);

        $createState->archiveOffset  = filesize($archiveFilepath);
        $createState->archivePath    = $archiveFilepath;
        $createState->basePath       = dirname($directory);
        $createState->basepathLength = strlen($createState->basePath);
        $createState->timerEnabled   = false;
        $createState->globSize       = $globSize;
        $createState->newBasePath    = $newBasePath;

        self::addItemsToArchive($createState, $scan);

        $retVal                = new stdClass();
        $retVal->numDirsAdded  = $createState->currentDirectoryIndex;
        $retVal->numFilesAdded = $createState->currentFileIndex;

        if ($createState->skippedFileCount > 0) {
            throw new Exception(
                "One or more files were were not able to be added when adding {$directory} to {$archiveFilepath}",
                self::EXCEPTION_CODE_ADD_ERROR
            );
        } elseif ($createState->skippedDirectoryCount > 0) {
            throw new Exception(
                "One or more directories were not able to be added when adding {$directory} to {$archiveFilepath}",
                self::EXCEPTION_CODE_ADD_ERROR
            );
        }

        return $retVal;
    }

    /**
     * Add relative file to archive
     *
     * @param string $archiveFilepath archive file
     * @param string $filepath        file to add
     * @param string $relativePath    relative path in archive
     * @param string $password        password archive, empty no password
     * @param int    $globSize        global size
     *
     * @return void
     */
    public static function addRelativeFileToArchiveST(
        $archiveFilepath,
        $filepath,
        $relativePath,
        $password,
        $globSize = DupArchiveCreateState::DEFAULT_GLOB_SIZE
    ) {
        $archiveHeader = self::getArchiveHeader($archiveFilepath, $password);
        $createState   = new DupArchiveCreateState($archiveHeader);

        $createState->archiveOffset = filesize($archiveFilepath);
        $createState->archivePath   = $archiveFilepath;
        $createState->timerEnabled  = false;
        $createState->globSize      = $globSize;

        $scan = new stdClass();

        $scan->Files = array();
        $scan->Dirs  = array();

        $scan->Files[] = $filepath;

        if ($relativePath != null) {
            $scan->FileAliases            = array();
            $scan->FileAliases[$filepath] = $relativePath;
        }

        self::addItemsToArchive($createState, $scan);
    }

    /**
     * Add file in archive from src
     *
     * @param string|resource $archive          Archive path or archive handle
     * @param string          $src              source string
     * @param string          $relativeFilePath relative path
     * @param int             $flags            if -1 get global archive flags else overwrite
     * @param string          $password         password archive
     * @param int             $forceSize        if 0 size is auto of content is filled of \0 char to size
     *
     * @return bool
     */
    public static function addFileFromSrc(
        $archive,
        $src,
        $relativeFilePath,
        $flags = -1,
        $password = '',
        $forceSize = 0
    ) {
        if (is_resource($archive)) {
            $archiveHandle = $archive;
            SnapIO::fseek($archiveHandle, 0, SEEK_SET);
        } else {
            if (($archiveHandle = SnapIO::fopen($archive, 'r+b')) == false) {
                throw new Exception('Can\'t open archive', self::EXCEPTION_CODE_OPEN_ERROR);
            }
        }

        $archiveHeader               = (new DupArchiveHeader())->readFromArchive($archiveHandle, $password);
        $createState                 = new DupArchiveCreateState($archiveHeader);
        $createState->archiveOffset  = SnapIO::ftell($archiveHandle);
        $createState->basePath       = dirname($relativeFilePath);
        $createState->basepathLength = strlen($createState->basePath);
        $createState->timerEnabled   = false;

        SnapIO::fseek($archiveHandle, 0, SEEK_END);

        DupArchiveFileProcessor::writeFileSrcToArchive(
            $createState,
            $archiveHeader,
            $archiveHandle,
            $src,
            $relativeFilePath,
            $flags,
            $forceSize
        );

        if (!is_resource($archive)) {
            SnapIO::fclose($archiveHandle);
        }
        return true;
    }

    /**
     * Add file in archive from src
     *
     * @param string $archiveFilepath  archive path
     * @param string $src              source string
     * @param string $relativeFilePath relative path
     * @param string $password         password archive
     * @param int    $offset           start search location
     * @param int    $sizeToSearch     max size where search
     *
     * @return bool
     */
    public static function replaceFileContent(
        $archiveFilepath,
        $src,
        $relativeFilePath,
        $password,
        $offset = 0,
        $sizeToSearch = 0
    ) {
        if (($archiveHandle = SnapIO::fopen($archiveFilepath, 'r+b')) == false) {
            throw new Exception('Can\'t open archive', self::EXCEPTION_CODE_OPEN_ERROR);
        }

        $archiveHeader = (new DupArchiveHeader())->readFromArchive($archiveHandle, $password);

        if (($filePos = self::searchPath($archiveHandle, $archiveHeader, $relativeFilePath, $offset, $sizeToSearch)) == false) {
            return false;
        }

        $fileHeader = (new DupArchiveFileHeader($archiveHeader))->readFromArchive($archiveHandle);
        $globHeader = (new DupArchiveGlobHeader($fileHeader))->readFromArchive($archiveHandle);
        SnapIO::fseek($archiveHandle, $filePos);

        $createState                 = new DupArchiveCreateState($archiveHeader);
        $createState->archivePath    = $archiveFilepath;
        $createState->archiveOffset  = $filePos;
        $createState->basePath       = dirname($relativeFilePath);
        $createState->basepathLength = strlen($createState->basePath);
        $createState->timerEnabled   = false;

        $forceSize = $globHeader->storedSize;

        DupArchiveFileProcessor::writeFileSrcToArchive(
            $createState,
            $archiveHeader,
            $archiveHandle,
            $src,
            $relativeFilePath,
            $fileHeader->getFlags(),
            $forceSize
        );

        SnapIO::fclose($archiveHandle);

        return true;
    }

    /**
     * Create archive
     *
     * @param string $archivePath  archive file path
     * @param bool   $isCompressed is compressed
     * @param string $password     ecrypt password, if empty archive isn't ecrypted
     *
     * @return DupArchiveHeader return archvie header of create archive
     */
    public static function createArchive($archivePath, $isCompressed, $password)
    {
        if (($archiveHandle = SnapIO::fopen($archivePath, 'w+b')) === false) {
            throw new Exception('Can\t create dup archvie file ' . $archivePath, self::EXCEPTION_CODE_OPEN_ERROR);
        }

        $flags = 0;
        if ($isCompressed) {
            $flags = $flags | DupArchive::FLAG_COMPRESS;
        }

        $archiveHeader = new DupArchiveHeader();
        $archiveHeader->setFlags($flags);

        if (strlen($password) > 0) {
            $archiveHeader->setPassword($password);
        }

        $archiveHeader->writeToArchive($archiveHandle);
        //reserver space for index
        $src  = json_encode(array());
        $src .= str_repeat("\0", self::INDEX_FILE_SIZE - strlen($src));
        self::addFileFromSrc(
            $archiveHandle,
            $src,
            self::INDEX_FILE_NAME,
            0,
            $password,
            self::INDEX_FILE_SIZE
        );

        // Intentionally do not write build state since if something goes wrong we went it to start over on the archive
        SnapIO::fclose($archiveHandle);
        return $archiveHeader;
    }

    /**
     * Add items to archive
     *
     * @param DupArchiveCreateState $createState create state info
     * @param stdClass              $scanFSInfo  scan if
     *
     * @return void
     */
    public static function addItemsToArchive(DupArchiveCreateState $createState, stdClass $scanFSInfo)
    {
        DupArchiveUtil::tlogObject("addItemsToArchive start", $createState);

        $directoryCount = count($scanFSInfo->Dirs);
        $fileCount      = count($scanFSInfo->Files);
        $createState->startTimer();
        $archiveHandle = SnapIO::fopen($createState->archivePath, 'r+b');

        DupArchiveUtil::tlog("Archive size=", filesize($createState->archivePath));
        DupArchiveUtil::tlog("Archive location is now " . SnapIO::ftell($archiveHandle));

        $archiveHeader = $createState->archiveHeader;

        if ($createState->archiveOffset == filesize($createState->archivePath)) {
            DupArchiveUtil::tlog(
                "Seeking to end of archive location because of offset {$createState->archiveOffset} " .
                "for file size " . filesize($createState->archivePath)
            );
            SnapIO::fseek($archiveHandle, 0, SEEK_END);
        } else {
            DupArchiveUtil::tlog("Seeking archive offset {$createState->archiveOffset} for file size " . filesize($createState->archivePath));
            SnapIO::fseek($archiveHandle, $createState->archiveOffset);
        }

        while (($createState->currentDirectoryIndex < $directoryCount) && (!$createState->timedOut())) {
            if ($createState->throttleDelayInUs !== 0) {
                usleep($createState->throttleDelayInUs);
            }

            $directory = $scanFSInfo->Dirs[$createState->currentDirectoryIndex];

            try {
                $relativeDirectoryPath = '';

                if (isset($scanFSInfo->DirectoryAliases) && array_key_exists($directory, $scanFSInfo->DirectoryAliases)) {
                    $relativeDirectoryPath = $scanFSInfo->DirectoryAliases[$directory];
                } else {
                    $relativeDirectoryPath = self::getLocalPath($directory, $createState);
                }

                if ($relativeDirectoryPath !== '') {
                    DupArchiveDirectoryProcessor::writeDirectoryToArchive($createState, $archiveHeader, $archiveHandle, $directory, $relativeDirectoryPath);
                } else {
                    $createState->skippedDirectoryCount++;
                    $createState->currentDirectoryIndex++;
                }
            } catch (Exception $ex) {
                DupArchiveUtil::log("Failed to add {$directory} to archive. Error: " . $ex->getMessage(), true);

                $createState->addFailure(DupArchiveProcessingFailure::TYPE_DIRECTORY, $directory, $ex->getMessage(), false);
                $createState->currentDirectoryIndex++;
                $createState->skippedDirectoryCount++;
                $createState->save();
            }
        }

        $createState->archiveOffset = SnapIO::ftell($archiveHandle);

        $workTimestamp = time();
        while (($createState->currentFileIndex < $fileCount) && (!$createState->timedOut())) {
            $filepath = $scanFSInfo->Files[$createState->currentFileIndex];

            try {
                $relativeFilePath = '';

                if (isset($scanFSInfo->FileAliases) && array_key_exists($filepath, $scanFSInfo->FileAliases)) {
                    $relativeFilePath = $scanFSInfo->FileAliases[$filepath];
                } else {
                    $relativeFilePath = self::getLocalPath($filepath, $createState);
                }

                // Uncomment when testing error handling
//                   if((strpos($relativeFilePath, 'dup-installer') !== false) || (strpos($relativeFilePath, 'lib') !== false)) {
//                       Dup_Log::Trace("Was going to do intentional error to {$relativeFilePath} but skipping");
//                   } else {
//                        throw new Exception("#### intentional file error when writing " . $relativeFilePath);
//                   }
//                }

                DupArchiveFileProcessor::writeFilePortionToArchive($createState, $archiveHeader, $archiveHandle, $filepath, $relativeFilePath);

                if (($createState->isRobust) && (time() - $workTimestamp >= 1)) {
                    DupArchiveUtil::log("Robust mode create state save");

                    // When in robustness mode save the state every second
                    $workTimestamp        = time();
                    $createState->working = ($createState->currentDirectoryIndex < $directoryCount) || ($createState->currentFileIndex < $fileCount);
                    $createState->save();
                }
            } catch (Snap32BitSizeLimitException $ex) {
                throw $ex;
            } catch (Exception $ex) {
                DupArchiveUtil::log("Failed to add {$filepath} to archive. Error: " . $ex->getMessage() . $ex->getTraceAsString(), true);
                $createState->currentFileIndex++;
                $createState->skippedFileCount++;
                $createState->addFailure(DupArchiveProcessingFailure::TYPE_FILE, $filepath, $ex->getMessage(), ($ex->getCode() === self::EXCEPTION_FATAL));
                $createState->save();
            }
        }

        $createState->working = ($createState->currentDirectoryIndex < $directoryCount) || ($createState->currentFileIndex < $fileCount);
        $createState->save();

        SnapIO::fclose($archiveHandle);

        if (!$createState->working) {
            DupArchiveUtil::log("compress done");
        } else {
            DupArchiveUtil::tlog("compress not done so continuing later");
        }
    }

    /**
     * Expand archive
     *
     * @param DupArchiveExpandState $expandState expand state
     *
     * @return void
     */
    public static function expandArchive(DupArchiveExpandState $expandState)
    {
        $expandState->startTimer();
        $archiveHandle = SnapIO::fopen($expandState->archivePath, 'rb');

        SnapIO::fseek($archiveHandle, $expandState->archiveOffset);

        if ($expandState->archiveOffset == 0) {
            $expandState->archiveHeader = (new DupArchiveHeader())->readFromArchive($archiveHandle, $expandState->archiveHeader->getPassword());
            $expandState->archiveOffset = SnapIO::ftell($archiveHandle);
            $expandState->save();
        } else {
            DupArchiveUtil::log("#### seeking archive offset {$expandState->archiveOffset}");
        }

        DupArchiveUtil::log('DUP EXPAND OFFSET ' . $expandState->archiveOffset);

        if ((!$expandState->validateOnly) || ($expandState->validatiOnType == DupArchiveExpandState::VALIDATION_FULL)) {
            $moreItems = self::expandItems($expandState, $archiveHandle);
        } else {
            $moreItems = self::standardValidateItems($expandState, $archiveHandle);
        }

        $expandState->working = $moreItems;
        $expandState->save();

        SnapIO::fclose($archiveHandle, false);

        if (!$expandState->working) {
            DupArchiveUtil::log("DUP EXPAND DONE");

            if (($expandState->expectedFileCount != -1) && ($expandState->expectedFileCount != $expandState->fileWriteCount)) {
                $expandState->addFailure(
                    DupArchiveProcessingFailure::TYPE_FILE,
                    'Archive',
                    "Number of files expected ({$expandState->expectedFileCount}) doesn't equal number written ({$expandState->fileWriteCount})."
                );
            }

            if (($expandState->expectedDirectoryCount != -1) && ($expandState->expectedDirectoryCount != $expandState->directoryWriteCount)) {
                $expandState->addFailure(
                    DupArchiveProcessingFailure::TYPE_DIRECTORY,
                    'Archive',
                    "Number of directories expected ({$expandState->expectedDirectoryCount}) " .
                    "doesn't equal number written ({$expandState->directoryWriteCount})."
                );
            }
        } else {
            DupArchiveUtil::tlogObject("expand not done so continuing later", $expandState);
        }
    }

    /**
     * Expand dup archive items
     *
     * @param DupArchiveExpandState $expandState   dup archive expand state
     * @param resource              $archiveHandle dup archvie resource
     *
     * @return bool true if more to read
     */
    private static function expandItems(DupArchiveExpandState $expandState, $archiveHandle)
    {
        $moreToRead    = true;
        $workTimestamp = time();

        while ($moreToRead && (!$expandState->timedOut())) {
            if ($expandState->throttleDelayInUs !== 0) {
                usleep($expandState->throttleDelayInUs);
            }

            if ($expandState->currentFileHeader != null) {
                DupArchiveUtil::tlog("Writing file {$expandState->currentFileHeader->relativePath}");

                if (self::filePassesFilters($expandState)) {
                    try {
                        $fileCompleted = DupArchiveFileProcessor::writeToFile($expandState, $archiveHandle);
                    } catch (ErrorException $ex) {
                        // This is the fatal exception that we just want to pass further
                        throw $ex;
                    } catch (Exception $ex) {
                        DupArchiveUtil::log("Failed to write to {$expandState->currentFileHeader->relativePath}. Error: " . $ex->getMessage(), true);

                        // Reset things - skip over this file within the archive.
                        SnapIO::fseek($archiveHandle, $expandState->lastHeaderOffset);
                        self::skipToNextHeader($archiveHandle, $expandState->archiveHeader);

                        $expandState->archiveOffset = ftell($archiveHandle);
                        $expandState->addFailure(
                            DupArchiveProcessingFailure::TYPE_FILE,
                            $expandState->currentFileHeader->relativePath,
                            $ex->getMessage(),
                            false
                        );
                        $expandState->resetForFile();
                        $expandState->lastHeaderOffset = -1;
                        $expandState->save();
                    }
                } else {
                    self::skipFileInArchive($archiveHandle, $expandState->currentFileHeader);
                    $expandState->resetForFile();
                }
            } else {
                // Header is null so read in the next one
                $expandState->lastHeaderOffset = @ftell($archiveHandle);
                $headerType                    = self::getNextHeaderType($archiveHandle);

                DupArchiveUtil::tlog('header type ' . $headerType);
                switch ($headerType) {
                    case self::HEADER_TYPE_FILE:
                        DupArchiveUtil::tlog('File header');
                        $expandState->currentFileHeader = (new DupArchiveFileHeader($expandState->archiveHeader))->readFromArchive($archiveHandle, false, true);
                        $expandState->archiveOffset     = @ftell($archiveHandle);
                        DupArchiveUtil::tlog('Just read file header from archive');
                        break;
                    case self::HEADER_TYPE_DIR:
                        DupArchiveUtil::tlog('Directory Header');
                        $directoryHeader = (new DupArchiveDirectoryHeader($expandState->archiveHeader))->readFromArchive($archiveHandle, true);
                        if (self::passesDirectoryExclusion($expandState, $directoryHeader->relativePath)) {
                            $createdDirectory = true;

                            if (!$expandState->validateOnly) {
                                $createdDirectory = DupArchiveFileProcessor::createDirectory($expandState, $directoryHeader);
                            }

                            if ($createdDirectory) {
                                $expandState->directoryWriteCount++;
                            }
                        }
                        $expandState->archiveOffset = ftell($archiveHandle);
                        DupArchiveUtil::tlog('Just read directory header ' . $directoryHeader->relativePath . ' from archive');
                        break;
                    case self::HEADER_TYPE_NONE:
                        $moreToRead = false;
                }
            }

            if (($expandState->isRobust) && (time() - $workTimestamp >= 1)) {
                DupArchiveUtil::log("Robust mode extract state save for standard validate");

                // When in robustness mode save the state every second
                $workTimestamp = time();
                $expandState->save();
            }
        }

        $expandState->save();

        return $moreToRead;
    }

    /**
     * check exclude dir
     *
     * @param DupArchiveExpandState $expandState dup archive expand state
     * @param string                $candidate   check exclude dir
     *
     * @return bool
     */
    private static function passesDirectoryExclusion(DupArchiveExpandState $expandState, $candidate)
    {
        foreach ($expandState->filteredDirectories as $directoryFilter) {
            if ($directoryFilter === '*') {
                return false;
            }

            if (SnapIO::getRelativePath($candidate, $directoryFilter) !== false) {
                return false;
            }
        }

        if (in_array($candidate, $expandState->excludedDirWithoutChilds)) {
            return false;
        }

        return true;
    }

    /**
     * Check flils filters
     *
     * @param DupArchiveExpandState $expandState dup archive expand state
     *
     * @return boolean
     */
    private static function filePassesFilters(DupArchiveExpandState $expandState)
    {
        $candidate = $expandState->currentFileHeader->relativePath;

        // Included files trumps all exclusion filters
        foreach ($expandState->includedFiles as $includedFile) {
            if ($includedFile === $candidate) {
                return true;
            }
        }

        if (self::passesDirectoryExclusion($expandState, $candidate)) {
            foreach ($expandState->filteredFiles as $fileFilter) {
                if ($fileFilter === '*' || $fileFilter === $candidate) {
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Validate items
     *
     * @param DupArchiveExpandState $expandState   dup archive expan state
     * @param resource              $archiveHandle dup archive resource
     *
     * @return bool true if more to read
     */
    private static function standardValidateItems(DupArchiveExpandState $expandState, $archiveHandle)
    {
        $moreToRead = true;

        $to            = $expandState->timedOut();
        $workTimestamp = time();

        while ($moreToRead && (!$to)) {
            if ($expandState->throttleDelayInUs !== 0) {
                usleep($expandState->throttleDelayInUs);
            }

            if ($expandState->currentFileHeader != null) {
                try {
                    $fileCompleted = DupArchiveFileProcessor::standardValidateFileEntry($expandState, $archiveHandle);

                    if ($fileCompleted) {
                        $expandState->resetForFile();
                    }

                    // Expand state taken care of within the write to file to ensure consistency
                } catch (Exception $ex) {
                    DupArchiveUtil::log("Failed validate file in archive. Error: " . $ex->getMessage(), true);
                    DupArchiveUtil::logObject("expand state", $expandState, true);
                    //   $expandState->currentFileIndex++;
                    // RSR TODO: Need way to skip past that file

                    $expandState->addFailure(DupArchiveProcessingFailure::TYPE_FILE, $expandState->currentFileHeader->relativePath, $ex->getMessage());
                    $expandState->save();

                    $moreToRead = false;
                }
            } else {
                $headerType = self::getNextHeaderType($archiveHandle);

                switch ($headerType) {
                    case self::HEADER_TYPE_FILE:
                        $expandState->currentFileHeader = (new DupArchiveFileHeader($expandState->archiveHeader))->readFromArchive($archiveHandle, false, true);
                        $expandState->archiveOffset     = ftell($archiveHandle);
                        break;
                    case self::HEADER_TYPE_DIR:
                        $directoryHeader = (new DupArchiveDirectoryHeader($expandState->archiveHeader))->readFromArchive($archiveHandle, true);
                        $expandState->directoryWriteCount++;
                        $expandState->archiveOffset = ftell($archiveHandle);
                        break;
                    case self::HEADER_TYPE_NONE:
                        $moreToRead = false;
                }
            }

            if (($expandState->isRobust) && (time() - $workTimestamp >= 1)) {
                DupArchiveUtil::log("Robust mdoe extract state save for standard validate");

                // When in robustness mode save the state every second
                $workTimestamp = time();
                $expandState->save();
            }
            $to = $expandState->timedOut();
        }

        $expandState->save();

        return $moreToRead;
    }
}
