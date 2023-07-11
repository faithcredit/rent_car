<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\DupArchive\States;

use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;

/**
 * Dup archive expand state
 */
abstract class DupArchiveExpandState extends DupArchiveStateBase
{
    const VALIDATION_NONE     = 0;
    const VALIDATION_STANDARD = 1;
    const VALIDATION_FULL     = 2;

    /** @var ?DupArchiveFileHeader */
    public $currentFileHeader = null;
    /** @var bool */
    public $validateOnly = false;
    /** @var int<0,2> */
    public $validatiOnType = self::VALIDATION_STANDARD;
    /** @var int<0,max> */
    public $fileWriteCount = 0;
    /** @var int<0,max> */
    public $directoryWriteCount = 0;
    /** @var int<-1,max> if it is -1 no validation is performed  */
    public $expectedFileCount = -1;
    /** @var int<-1,max> if it is -1 no validation is performed  */
    public $expectedDirectoryCount = -1;
    /** @var string[] */
    public $filteredDirectories = [];
    /** @var string[] */
    public $excludedDirWithoutChilds = [];
    /** @var string[] */
    public $filteredFiles = [];
    /** @var string[] relative path list to inclue files, overwrite filters */
    public $includedFiles = [];
    /** @var string[] relativePath => fullNewPath */
    public $fileRenames = [];
    /** @var int<-1,max>|string could be a octhal number or ugo linux permissions string  */
    public $directoryModeOverride = -1;
    /** @var int<-1,max>|string could be a octhal number or ugo linux permissions string  */
    public $fileModeOverride = -1;
    /** @var int<-1,max> */
    public $lastHeaderOffset = -1;

    /**
     * Class constructor
     *
     * @param DupArchiveHeader $archiveHeader archive header
     */
    public function __construct(DupArchiveHeader $archiveHeader)
    {
        parent::__construct($archiveHeader);
    }

    /**
     * Reset values
     *
     * @return void
     */
    public function reset()
    {
        parent::reset();
        $this->currentFileHeader        = null;
        $this->validateOnly             = false;
        $this->validatiOnType           = self::VALIDATION_STANDARD;
        $this->fileWriteCount           = 0;
        $this->directoryWriteCount      = 0;
        $this->expectedFileCount        = -1;
        $this->expectedDirectoryCount   = -1;
        $this->filteredDirectories      = [];
        $this->excludedDirWithoutChilds = [];
        $this->filteredFiles            = [];
        $this->includedFiles            = [];
        $this->fileRenames              = [];
        $this->directoryModeOverride    = -1;
        $this->fileModeOverride         = -1;
        $this->lastHeaderOffset         = -1;
    }

    /**
     * Reset state for file
     *
     * @return void
     */
    public function resetForFile()
    {
        $this->currentFileHeader = null;
        $this->currentFileOffset = 0;
    }
}
