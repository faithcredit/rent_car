<?php

namespace Duplicator\Libs\DupArchive\Info;

use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;

class DupArchiveExpanderInfo
{
    /** @var ?resource */
    public $archiveHandle = null;
    /** @var ?DupArchiveFileHeader */
    public $currentFileHeader = null;
    /** @var ?string */
    public $destDirectory = null;
    /** @var int */
    public $directoryWriteCount = 0;
    /** @var int */
    public $fileWriteCount = 0;
    /** @var bool */
    public $enableWrite = false;

    /**
     * Get dest path
     *
     * @return string
     */
    public function getCurrentDestFilePath()
    {
        if ($this->destDirectory != null) {
            return "{$this->destDirectory}/{$this->currentFileHeader->relativePath}";
        } else {
            return '';
        }
    }
}
