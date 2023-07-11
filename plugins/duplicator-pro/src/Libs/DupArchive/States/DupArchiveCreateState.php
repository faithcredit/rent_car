<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\DupArchive\States;

/**
 * Dup archive create state
 */
class DupArchiveCreateState extends DupArchiveStateBase
{
    const DEFAULT_GLOB_SIZE = 1048576;

    /** @var int<0, max> */
    public $basepathLength = 0;
    /** @var int<0, max> */
    public $currentDirectoryIndex = 0;
    /** @var int<0, max> */
    public $currentFileIndex = 0;
    /** @var int<0, max> */
    public $globSize = self::DEFAULT_GLOB_SIZE;
    /** @var ?string */
    public $newBasePath = null;
    /** @var int<0, max> */
    public $skippedFileCount = 0;
    /** @var int<0, max> */
    public $skippedDirectoryCount = 0;

    /**
     * Reset values
     *
     * @return void
     */
    public function reset()
    {
        parent::reset();
        $this->basepathLength        = 0;
        $this->currentDirectoryIndex = 0;
        $this->currentFileIndex      = 0;
        $this->globSize              = self::DEFAULT_GLOB_SIZE;
        $this->newBasePath           = null;
        $this->skippedFileCount      = 0;
        $this->skippedDirectoryCount = 0;
    }
}
