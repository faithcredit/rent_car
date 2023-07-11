<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package\Create\DupArchive;

use DUP_PRO_Global_Entity;
use DUP_PRO_Log;
use DUP_PRO_Package;
use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;
use Duplicator\Libs\DupArchive\Processors\DupArchiveProcessingFailure;
use Duplicator\Libs\DupArchive\States\DupArchiveCreateState;
use Exception;

/**
 * Dup archvie package create state
 */
class PackageDupArchiveCreateState extends DupArchiveCreateState
{
    /** @var DUP_PRO_Package */
    private $package = null;

    /**
     * Class constructor
     *
     * @param DupArchiveHeader $archiveHeader archive header
     * @param DUP_PRO_Package  $package       package
     */
    public function __construct(DupArchiveHeader $archiveHeader, DUP_PRO_Package $package = null)
    {
        if ($package == null) {
            throw new Exception('Package required');
        }
        $this->package = $package;
        parent::__construct($archiveHeader);
        $global                  = DUP_PRO_Global_Entity::getInstance();
        $this->throttleDelayInUs = $global->getMicrosecLoadReduction();
    }

    /**
     * Filter props on json encode
     *
     * @return string[]
     */
    public function __sleep()
    {
        $props = array_keys(get_object_vars($this));
        return array_diff($props, array('package'));
    }

    /**
     * Set package
     *
     * @param DUP_PRO_Package $package package descriptor
     *
     * @return void
     */
    public function setPackage(DUP_PRO_Package $package)
    {
        $this->package = $package;
    }

    /**
     * Create new archive
     *
     * @param DupArchiveHeader $archiveHeader   archive header
     * @param DUP_PRO_Package  $package         package descriptor
     * @param string           $archivePath     archive path
     * @param string           $basePath        base path
     * @param int              $timeSliceInSecs throttle
     *
     * @return PackageDupArchiveCreateState
     */
    public static function createNew(
        DupArchiveHeader $archiveHeader,
        DUP_PRO_Package $package,
        $archivePath,
        $basePath,
        $timeSliceInSecs
    ) {
        DUP_PRO_Log::info("CREATE ARCHIVE STATE FOR DUP ARCHIVE");

        $instance = new PackageDupArchiveCreateState($archiveHeader, $package);
        if (file_exists($archivePath)) {
            $instance->archiveOffset = filesize($archivePath);
        } else {
            $instance->archiveOffset = 0;
        }

        $instance->archivePath     = $archivePath;
        $instance->basePath        = $basePath;
        $instance->timeSliceInSecs = $timeSliceInSecs;
        $instance->working         = true;
        $instance->startTimestamp  = time();
        $instance->save();
        return $instance;
    }

    /**
     * Add failure item
     *
     * @param int     $type        failure type enum
     * @param string  $subject     failure subject
     * @param string  $description failure description
     * @param boolean $isCritical  true if is critical
     *
     * @return DupArchiveProcessingFailure
     */
    public function addFailure($type, $subject, $description, $isCritical = false)
    {
        $failure = parent::addFailure($type, $subject, $description, $isCritical);

        $buildProgress = $this->package->build_progress;
        if ($isCritical) {
            $buildProgress->failed = true;
        } elseif ($failure !== false) {
            $buildProgress->warnings[] = $this->getFailureString($failure);
        }

        return $failure;
    }

    /**
     * Save state functon
     *
     * @return void
     */
    public function save()
    {
        $this->package->build_progress->dupCreate               = $this;
        $this->package->build_progress->next_archive_dir_index  = $this->currentDirectoryIndex;
        $this->package->build_progress->next_archive_file_index = $this->currentFileIndex;
        $this->package->save();
    }
}
