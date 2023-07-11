<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\DupArchive\Info;

use Duplicator\Libs\DupArchive\Headers\DupArchiveDirectoryHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;

class DupArchiveInfo
{
    /** @var ?DupArchiveHeader */
    public $archiveHeader = null;
    /** @var DupArchiveFileHeader[] */
    public $fileHeaders = [];
    /** @var DupArchiveDirectoryHeader[] */
    public $directoryHeaders = [];

    /**
     * Class constructor
     */
    public function __construct()
    {
    }
}
