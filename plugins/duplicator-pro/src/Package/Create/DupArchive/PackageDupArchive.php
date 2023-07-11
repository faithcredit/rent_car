<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package\Create\DupArchive;

use DUP_PRO_Constants;
use DUP_PRO_EmptyScanFileException;
use DUP_PRO_Global_Entity;
use DUP_PRO_Log;
use DUP_PRO_NoDirListException;
use DUP_PRO_NoFileListException;
use DUP_PRO_NoScanFileException;
use DUP_PRO_Package;
use DUP_PRO_PackageStatus;
use DUP_PRO_Server;
use DUP_PRO_System_Global_Entity;
use DUP_PRO_U;
use Duplicator\Libs\DupArchive\DupArchiveEngine;
use Duplicator\Libs\DupArchive\States\DupArchiveExpandState;
use Duplicator\Libs\Snap\Snap32BitSizeLimitException;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Package\Create\BuildProgress;
use Exception;

/**
 * Package DupArchive creator
 */
class PackageDupArchive
{
    // Using a worker time override since evidence shorter time works much
    const WORKER_TIME_IN_SEC = 10;

    /**
     *  Creates the zip file and adds the SQL file to the archive
     *
     * @param DUP_PRO_Package $package       Package descriptor
     * @param BuildProgress   $buildProgress Build progress object
     *
     * @return boolean
     */
    public static function create(DUP_PRO_Package $package, BuildProgress $buildProgress)
    {
        try {
            $archive = $package->Archive;
            if ($buildProgress->retries > DUP_PRO_Constants::MAX_BUILD_RETRIES) {
                $error_msg = DUP_PRO_U::__('Package build appears stuck so marking package as failed. Is the Max Worker Time set too high?.');
                DUP_PRO_Log::error(DUP_PRO_U::__('Build Failure'), $error_msg, false);
                $buildProgress->failed = true;
                return true;
            } else {
                // If all goes well retries will be reset to 0 at the end of this function.
                $buildProgress->retries++;
                $package->update();
            }

            $global = DUP_PRO_Global_Entity::getInstance();
            $done   = false;

            DupArchiveEngine::init(new Logger(), $archive->getTargetRootPath());
            $package->safe_tmp_cleanup(true);
            $compressDir = rtrim(SnapIO::safePath($archive->PackDir), '/');
            $sqlPath     = SnapIO::safePath("{$package->StorePath}/{$package->Database->File}");
            $archivePath = SnapIO::safePath("{$package->StorePath}/{$archive->File}");
            $filterDirs  = empty($archive->FilterDirs)  ? 'not set' : rtrim(str_replace(';', "\n\t", $archive->FilterDirs));
            $filterFiles = empty($archive->FilterFiles) ? 'not set' : rtrim(str_replace(';', "\n\t", $archive->FilterFiles));
            $filterExts  = empty($archive->FilterExts)  ? 'not set' : $archive->FilterExts;
            $filterOn    = ($archive->FilterOn) ? 'ON' : 'OFF';

            $scanFilepath            = DUPLICATOR_PRO_SSDIR_PATH_TMP . "/{$package->NameHash}_scan.json";
            $skipArchiveFinalization = false;
            try {
                $scanReport = $package->getScanReportFromJson($scanFilepath);
            } catch (DUP_PRO_NoScanFileException $ex) {
                DUP_PRO_Log::trace("**** scan file $scanFilepath doesn't exist!!");
                DUP_PRO_Log::error($ex->getMessage(), '', false);
                $buildProgress->failed = true;
                return true;
            } catch (DUP_PRO_NoFileListException $ex) {
                DUP_PRO_Log::trace("**** list of files doesn't exist!!");
                DUP_PRO_Log::error($ex->getMessage(), '', false);
                $buildProgress->failed = true;
                return true;
            } catch (DUP_PRO_NoDirListException $ex) {
                DUP_PRO_Log::trace("**** list of directories doesn't exist!!");
                DUP_PRO_Log::error($ex->getMessage(), '', false);
                $buildProgress->failed = true;
                return true;
            } catch (DUP_PRO_EmptyScanFileException $ex) {
                $errorText = $ex->getMessage();
                $fixText   = DUP_PRO_U::__("Click on \"Resolve This\" button to fix the JSON settings.");
                DUP_PRO_Log::trace($errorText);
                DUP_PRO_Log::error("$errorText **RECOMMENDATION:  $fixText.", '', false);
                $systemGlobal = DUP_PRO_System_Global_Entity::getInstance();
                $systemGlobal->addQuickFix(
                    $errorText,
                    $fixText,
                    array(
                        'global' => array(
                            'json_mode' => 1
                        )
                    )
                );
                $buildProgress->failed = true;
                return true;
            }

            // Ensure database sql is added
            $scanReport->ARC->Files[]               = $sqlPath;
            $scanReport->ARC->FileAliases           = array();
            $scanReport->ARC->FileAliases[$sqlPath] = $package->get_sql_ark_file_path();
            if ($buildProgress->archive_started == false) {
                DUP_PRO_Log::info("\n********************************************************************************");
                DUP_PRO_Log::info("ARCHIVE Type=DUP Mode=DupArchive");
                DUP_PRO_Log::info("********************************************************************************");
                DUP_PRO_Log::info("ARCHIVE DIR:  " . $compressDir);
                DUP_PRO_Log::info("ARCHIVE FILE: " . basename($archivePath));
                DUP_PRO_Log::info("FILTERS: *{$filterOn}*");
                DUP_PRO_Log::info("DIRS:  {$filterDirs}");
                DUP_PRO_Log::info("EXTS:  {$filterExts}");
                DUP_PRO_Log::info("FILES:  {$filterFiles}");
                DUP_PRO_Log::info("----------------------------------------");
                DUP_PRO_Log::info("COMPRESSING");
                DUP_PRO_Log::info("SIZE:\t" . $scanReport->ARC->Size);
                DUP_PRO_Log::info(
                    "STATS:\tDirs " . $scanReport->ARC->DirCount .
                    " | Files " . $scanReport->ARC->FileCount .
                    " | Total " . $scanReport->ARC->FullCount
                );
                if (($scanReport->ARC->DirCount == '') || ($scanReport->ARC->FileCount == '') || ($scanReport->ARC->FullCount == '')) {
                    DUP_PRO_Log::error('Invalid Scan Report Detected', 'Invalid Scan Report Detected', false);
                    $buildProgress->failed = true;
                    return true;
                }

                $buildProgress->archive_started    = true;
                $buildProgress->archive_start_time = DUP_PRO_U::getMicrotime();
                $buildProgress->retries            = 0;
                $package->Update();
            }

            try {
                if ($buildProgress->dupCreate == null) {
                    $archiveHeader = DupArchiveEngine::createArchive(
                        $archivePath,
                        $buildProgress->current_build_compression,
                        $package->Archive->getArchivePassword()
                    );

                    $createState = PackageDupArchiveCreateState::createNew(
                        $archiveHeader,
                        $package,
                        $archivePath,
                        $compressDir,
                        self::WORKER_TIME_IN_SEC
                    );
                } else {
                    /*DUP_PRO_Log::traceObject('Resumed build_progress', $package->build_progress);*/
                    $createState = $package->build_progress->dupCreate;
                }

                if ($buildProgress->retries > 1) {
                    // Indicates it had problems before so move into robustness mode
                    $createState->isRobust = true;
                    //$createState->timeSliceInSecs = self::WORKER_TIME_IN_SEC / 2;
                    $createState->save();
                }

                if ($createState->working) {
                    DupArchiveEngine::addItemsToArchive($createState, $scanReport->ARC);
                    if ($createState->isCriticalFailurePresent()) {
                        throw new Exception($createState->getFailureSummary());
                    }

                    $totalFileCount = count($scanReport->ARC->Files);
                    DUP_PRO_Log::trace("Total file count " . $totalFileCount);
                    $status = SnapUtil::getWorkPercent(
                        DUP_PRO_PackageStatus::ARCSTART,
                        DUP_PRO_PackageStatus::ARCVALIDATION,
                        $totalFileCount,
                        $createState->currentFileIndex
                    );
                    if ($status == DUP_PRO_PackageStatus::ARCSTART) {
                        do_action('duplicator_pro_package_before_set_status', $package, DUP_PRO_PackageStatus::ARCSTART);
                    } elseif ($status == DUP_PRO_PackageStatus::ARCVALIDATION) {
                        do_action('duplicator_pro_package_before_set_status', $package, DUP_PRO_PackageStatus::ARCVALIDATION);
                    }

                    $package->Status        = $status;
                    $buildProgress->retries = 0;
                    $createState->save();
                    if ($status == DUP_PRO_PackageStatus::ARCSTART) {
                        do_action('duplicator_pro_package_after_set_status', $package, DUP_PRO_PackageStatus::ARCSTART);
                    } elseif ($status == DUP_PRO_PackageStatus::ARCVALIDATION) {
                        do_action('duplicator_pro_package_after_set_status', $package, DUP_PRO_PackageStatus::ARCVALIDATION);
                    }

                    DUP_PRO_Log::traceObject('Stored build_progress', $package->build_progress);
                    if ($createState->working == false) {
                    // Want it to do the final cleanup work in an entirely new thread so return immediately
                        $skipArchiveFinalization = true;
                        DUP_PRO_Log::trace("Done build phase.");
                    }
                }
            } catch (Snap32BitSizeLimitException $exception) {
                $global = DUP_PRO_System_Global_Entity::getInstance();
                $err    = 'Package build failure due to building a large package on 32 bit PHP.';
                $fix    = sprintf(
                    "%s <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-package-035-q' target='_blank'>%s</a> %s",
                    DUP_PRO_U::__('Package build failure due to building a large package on 32 bit PHP. Please see '),
                    DUP_PRO_U::__("Tech docs"),
                    DUP_PRO_U::__("for instructions on how to resolve.")
                );
                $global->addTextFix($err, $fix);
                $buildProgress->failed = true;
                return true;
            } catch (Exception $ex) {
                $message = DUP_PRO_U::__('Problem adding items to archive.') . ' ' . $ex->getMessage() . "\n" . $ex->getTraceAsString();
                DUP_PRO_Log::error(DUP_PRO_U::__('Problems adding items to archive.'), $message, false);
                DUP_PRO_Log::traceObject($message . " EXCEPTION:", $ex);
                $buildProgress->failed = true;
                return true;
            }

            //-- Final Wrapup of the Archive
            if ((!$skipArchiveFinalization) && ($createState->working == false)) {
                if (!$buildProgress->installer_built) {
                    $package->Installer->build($buildProgress);

                    $expandState = new PackageDupArchiveExpandState(
                        DupArchiveEngine::getArchiveHeader($archivePath, $package->Archive->getArchivePassword()),
                        $package
                    );

                    $expandState->archivePath            = $archivePath;
                    $expandState->working                = true;
                    $expandState->timeSliceInSecs        = self::WORKER_TIME_IN_SEC;
                    $expandState->basePath               = DUPLICATOR_PRO_SSDIR_PATH_TMP . '/validate';
                    $expandState->validateOnly           = true;
                    $expandState->validatiOnType         = DupArchiveExpandState::VALIDATION_STANDARD;
                    $expandState->working                = true;
                    $expandState->expectedDirectoryCount = (
                        count($scanReport->ARC->Dirs) -
                        $createState->skippedDirectoryCount +
                        $package->Installer->numDirsAdded
                    );
                    // add index file
                    $expandState->expectedFileCount = (
                        1 +
                        count($scanReport->ARC->Files) -
                        $createState->skippedFileCount +
                        $package->Installer->numFilesAdded
                    );
                    $expandState->save();
                } else {
                    // $build_progress->warnings = $createState->getWarnings(); Auto saves warnings within build progress along the way
                    try {
                        $expandState = $buildProgress->dupExpand;
                        if (is_null($expandState)) {
                            throw new Exception('Expand state can\'t be null');
                        }
                        if ($buildProgress->retries > 1) {
                            // Indicates it had problems before so move into robustness mode
                            $expandState->isRobust = true;
                            //$expandState->timeSliceInSecs = self::WORKER_TIME_IN_SEC / 2;
                            $expandState->save();
                        }

                        DUP_PRO_Log::traceObject('Resumed validation expand state', $expandState);
                        DupArchiveEngine::expandArchive($expandState);
                        $totalFileCount = count($scanReport->ARC->Files);
                        $archiveSize    = @filesize($expandState->archivePath);
                        $status         = SnapUtil::getWorkPercent(
                            DUP_PRO_PackageStatus::ARCVALIDATION,
                            DUP_PRO_PackageStatus::ARCDONE,
                            $archiveSize,
                            $expandState->archiveOffset
                        );
                        if ($status == DUP_PRO_PackageStatus::ARCDONE) {
                            do_action('duplicator_pro_package_before_set_status', $package, DUP_PRO_PackageStatus::ARCDONE);
                        }

                        $package->Status = SnapUtil::getWorkPercent(
                            DUP_PRO_PackageStatus::ARCVALIDATION,
                            DUP_PRO_PackageStatus::ARCDONE,
                            $archiveSize,
                            $expandState->archiveOffset
                        );
                    } catch (Exception $ex) {
                        DUP_PRO_Log::traceError('Exception:' . $ex->getMessage() . ':' . $ex->getTraceAsString());
                        $buildProgress->failed = true;
                        return true;
                    }

                    if ($expandState->isCriticalFailurePresent()) {
                        // Fail immediately if critical failure present - even if havent completed processing the entire archive.
                        DUP_PRO_Log::error(DUP_PRO_U::__('Build Failure'), $expandState->getFailureSummary(), false);
                        $buildProgress->failed = true;
                        return true;
                    } elseif (!$expandState->working) {
                        $buildProgress->archive_built = true;
                        $buildProgress->retries       = 0;
                        $package->update();
                        $timerAllEnd     = DUP_PRO_U::getMicrotime();
                        $timerAllSum     = DUP_PRO_U::elapsedTime($timerAllEnd, $buildProgress->archive_start_time);
                        $archiveFileSize = @filesize($archivePath);
                        DUP_PRO_Log::info("COMPRESSED SIZE: " . DUP_PRO_U::byteSize($archiveFileSize));
                        DUP_PRO_Log::info("ARCHIVE RUNTIME: {$timerAllSum}");
                        DUP_PRO_Log::info("MEMORY STACK: " . DUP_PRO_Server::getPHPMemory());
                        DUP_PRO_Log::info("CREATE WARNINGS: " . $createState->getFailureSummary(false, true));
                        DUP_PRO_Log::info("VALIDATION WARNINGS: " . $expandState->getFailureSummary(false, true));
                        $archive->file_count = (
                            $expandState->fileWriteCount +
                            $expandState->directoryWriteCount -
                            $package->Installer->numDirsAdded -
                            $package->Installer->numFilesAdded
                        );
                        $package->update();
                        $done = true;
                        if ($status == DUP_PRO_PackageStatus::ARCDONE) {
                                do_action('duplicator_pro_package_after_set_status', $package, DUP_PRO_PackageStatus::ARCDONE);
                        }
                    } else {
                        $expandState->save();
                    }
                }
            }
        } catch (Exception $ex) {
            // Have to have a catchall since the main system that calls this function is not prepared to handle exceptions
            DUP_PRO_Log::traceError('Top level create Exception:' . $ex->getMessage() . ':' . $ex->getTraceAsString());
            $buildProgress->failed = true;
            return true;
        }

        $buildProgress->retries = 0;
        return $done;
    }
}
