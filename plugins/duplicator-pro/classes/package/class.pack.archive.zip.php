<?php

/**
 * Class to create a zip file using PHP ZipArchive
 *
 * Standard: PSR-2 (almost)
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    DUP_PRO
 * @subpackage classes/package
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      1.0.0
 *
 * @notes: Trace process time
 *  $timer01 = DUP_PRO_U::getMicrotime();
 *  DUP_PRO_Log::trace("SCAN TIME-B = " . DUP_PRO_U::elapsedTime(DUP_PRO_U::getMicrotime(), $timer01));
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\FunctionalityCheck;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Package\Create\BuildProgress;
use Duplicator\Utils\ZipArchiveExtended;

class DUP_PRO_ZipArchive
{
    /** @var DUP_PRO_Global_Entity */
    private $global               = null;
    private $optMaxBuildTimeOn    = true;
    private $maxBuildTimeFileSize = 100000;
    private $throttleDelayInUs    = 0;
    /** @var DUP_PRO_Package */
    private $package = null;
    /** @var ZipArchiveExtended */
    private $zipArchive = null;

    /**
     * Class constructor
     *
     * @param DUP_PRO_Package $package
     */
    public function __construct(DUP_PRO_Package $package)
    {
        $this->global            = DUP_PRO_Global_Entity::getInstance();
        $this->optMaxBuildTimeOn = ($this->global->max_package_runtime_in_min > 0);
        $this->throttleDelayInUs = $this->global->getMicrosecLoadReduction();

        $this->package    = $package;
        $this->zipArchive = new ZipArchiveExtended($this->package->StorePath . '/' . $this->package->Archive->File);

        $password = $this->package->Archive->getArchivePassword();
        if (strlen($password) > 0) {
            $this->zipArchive->setEncrypt(true, $password);
        }
    }

    /**
     * Creates the zip file and adds the SQL file to the archive
     *
     * @param BuildProgress $build_progress A copy of the current build progress
     *
     * @returns bool     Returns true if the process was successful
     */
    public function create(BuildProgress $build_progress)
    {
        try {
            if (!ZipArchiveExtended::isPhpZipAvaiable()) {
                DUP_PRO_Log::trace("Zip archive doesn't exist?");
                return false;
            }

            $this->package->safe_tmp_cleanup(true);
            if ($this->package->ziparchive_mode == DUP_PRO_ZipArchive_Mode::SingleThread) {
                return $this->createSingleThreaded($build_progress);
            } else {
                return $this->createMultiThreaded($build_progress);
            }
        } catch (Exception $ex) {
            DUP_PRO_Log::error("Runtime error in class-package-archive-zip.php.", "Exception: {$ex}");
        }
    }

    /**
     * Creates the zip file using a single thread approach
     *
     * @param BuildProgress $build_progress A copy of the current build progress
     *
     * @returns bool     Returns true if the process was successful
     */
    private function createSingleThreaded(BuildProgress $build_progress)
    {
        $countFiles  = 0;
        $compressDir = rtrim(SnapIO::safePath($this->package->Archive->PackDir), '/');
        $sqlPath     = $this->package->StorePath . '/' . $this->package->Database->File;
        $zipPath     = $this->package->StorePath . '/' . $this->package->Archive->File;
        $filterDirs  = empty($this->package->Archive->FilterDirs)  ? 'not set' : rtrim(str_replace(';', "\n\t", $this->package->Archive->FilterDirs));
        $filterFiles = empty($this->package->Archive->FilterFiles) ? 'not set' : rtrim(str_replace(';', "\n\t", $this->package->Archive->FilterFiles));
        $filterExts  = empty($this->package->Archive->FilterExts)  ? 'not set' : $this->package->Archive->FilterExts;
        $filterOn    = ($this->package->Archive->FilterOn) ? 'ON' : 'OFF';
        $validation  = ($this->global->ziparchive_validation) ? 'ON' : 'OFF';
        $compression = $build_progress->current_build_compression ? 'ON' : 'OFF';

        $this->zipArchive->setCompressed($build_progress->current_build_compression);
        //PREVENT RETRIES PAST 3:  Default is 10 (DUP_PRO_Constants::MAX_BUILD_RETRIES)
        //since this is ST Mode no reason to keep trying like MT
        if ($build_progress->retries >= 3) {
            $err = DUP_PRO_U::__('Package build appears stuck so marking package as failed. Is the PHP or Web Server timeouts too low?');
            DUP_PRO_Log::error(DUP_PRO_U::__('Build Failure'), $err, false);
            DUP_PRO_Log::trace($err);
            return $build_progress->failed = true;
        } else {
            if ($build_progress->retries > 0) {
                DUP_PRO_Log::infoTrace("**NOTICE: Retry count at: {$build_progress->retries}");
            }
            $build_progress->retries++;
            $this->package->update();
        }

        //LOAD SCAN REPORT
        try {
            $scanReport = $this->package->getScanReportFromJson(DUPLICATOR_PRO_SSDIR_PATH_TMP . "/{$this->package->NameHash}_scan.json");
        } catch (DUP_PRO_NoScanFileException $ex) {
            DUP_PRO_Log::trace("**** scan file doesn't exist!!");
            DUP_PRO_Log::error($ex->getMessage(), '', false);
            $build_progress->failed = true;
            return true;
        } catch (DUP_PRO_NoFileListException $ex) {
            DUP_PRO_Log::trace("**** list of files doesn't exist!!");
            DUP_PRO_Log::error($ex->getMessage(), '', false);
            $build_progress->failed = true;
            return true;
        } catch (DUP_PRO_NoDirListException $ex) {
            DUP_PRO_Log::trace("**** list of directories doesn't exist!!");
            DUP_PRO_Log::error($ex->getMessage(), '', false);
            $build_progress->failed = true;
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
            $systemGlobal->save();
            $build_progress->failed = true;
            return true;
        }

        //============================================
        //ST: START ZIP
        //============================================
        if ($build_progress->archive_started === false) {
            DUP_PRO_Log::info("\n********************************************************************************");
            DUP_PRO_Log::info("ARCHIVE ZipArchive Single-Threaded");
            DUP_PRO_Log::info("********************************************************************************");
            DUP_PRO_Log::info("ARCHIVE DIR:  " . $compressDir);
            DUP_PRO_Log::info("ARCHIVE FILE: " . basename($zipPath));
            DUP_PRO_Log::info("COMPRESSION: *{$compression}*");
            DUP_PRO_Log::info("VALIDATION: *{$validation}*");
            DUP_PRO_Log::info("FILTERS: *{$filterOn}*");
            DUP_PRO_Log::info("DIRS:\t{$filterDirs}");
            DUP_PRO_Log::info("EXTS:  {$filterExts}");
            DUP_PRO_Log::info("FILES:  {$filterFiles}");
            DUP_PRO_Log::info("----------------------------------------");
            DUP_PRO_Log::info("COMPRESSING");
            DUP_PRO_Log::info("SIZE:\t" . $scanReport->ARC->Size);
            DUP_PRO_Log::info("STATS:\tDirs " . $scanReport->ARC->DirCount . " | Files " . $scanReport->ARC->FileCount . " | Total " . $scanReport->ARC->FullCount);
            if (($scanReport->ARC->DirCount == '') || ($scanReport->ARC->FileCount == '') || ($scanReport->ARC->FullCount == '')) {
                DUP_PRO_Log::error('Invalid Scan Report Detected', 'Invalid Scan Report Detected', false);
                return $build_progress->failed = true;
            }
            $build_progress->archive_started    = true;
            $build_progress->archive_start_time = DUP_PRO_U::getMicrotime();
        }

        //============================================
        //ST: ADD DATABASE FILE
        //============================================
        if ($build_progress->archive_has_database === false) {
            if (!$this->zipArchive->open()) {
                DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
                return $build_progress->failed = true;
            }

            if ($this->zipArchive->addFile($sqlPath, $this->package->get_sql_ark_file_path())) {
                DUP_PRO_Log::info("SQL ADDED: " . basename($sqlPath));
            } else {
                DUP_PRO_Log::error("Unable to add database.sql to archive.", "SQL File Path [" . $sqlPath . "]", false);
                return $build_progress->failed = true;
            }


            if ($this->zipArchive->close()) {
                $build_progress->archive_has_database = true;
                $this->package->update();
            } else {
                $err = 'ZipArchive close failure during database.sql phase.';
                $this->setDupArchiveSwitchFix($err);
                return $build_progress->failed = true;
            }
        }

        //============================================
        //ST: ZIP DIRECTORIES
        //Keep this loop tight: ZipArchive can handle over 10k+ dir entries in under 0.01 seconds.
        //Its really fast without files so no need to do status pushes or other checks in loop
        //============================================
        if ($build_progress->next_archive_dir_index < count($scanReport->ARC->Dirs)) {
            if (!$this->zipArchive->open()) {
                DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
                return $build_progress->failed = true;
            }

            foreach ($scanReport->ARC->Dirs as $dir) {
                $emptyDir = $this->package->Archive->getLocalDirPath($dir);
                DUP_PRO_Log::trace("ADD DIR TO ZIP: '{$emptyDir}'");
                if (!$this->zipArchive->addEmptyDir($emptyDir)) {
                    if (empty($compressDir) || strpos($dir, rtrim($compressDir, '/')) != 0) {
                        DUP_PRO_Log::infoTrace("WARNING: Unable to zip directory: '{$dir}'");
                    }
                }
                $build_progress->next_archive_dir_index++;
            }

            if ($this->zipArchive->close()) {
                $this->package->update();
            } else {
                $err = 'ZipArchive close failure during directory add phase.';
                $this->setDupArchiveSwitchFix($err);
                return $build_progress->failed = true;
            }
        }

        //============================================
        //ST: ZIP FILES
        //============================================
        if ($build_progress->archive_built === false) {
            if ($this->zipArchive->open() === false) {
                DUP_PRO_Log::error("Can not open zip file at: [{$zipPath}]", '', false);
                return $build_progress->failed = true;
            }

            // Since we have to estimate progress in Single Thread mode
            // set the status when we start archiving just like Shell Exec
            do_action('duplicator_pro_package_before_set_status', $this->package, DUP_PRO_PackageStatus::ARCSTART);
            $this->package->Status = DUP_PRO_PackageStatus::ARCSTART;
            $this->package->update();
            do_action('duplicator_pro_package_after_set_status', $this->package, DUP_PRO_PackageStatus::ARCSTART);
            $total_file_size       = 0;
            $total_file_count_trip = ($scanReport->ARC->UFileCount + 1000);
            foreach ($scanReport->ARC->Files as $file) {
                //NON-ASCII check
                if (preg_match('/[^\x20-\x7f]/', $file)) {
                    if (!$this->isUTF8FileSafe($file)) {
                        continue;
                    }
                }

                if ($this->global->ziparchive_validation) {
                    if (!is_readable($file)) {
                        DUP_PRO_Log::infoTrace("NOTICE: File [{$file}] is unreadable!");
                        continue;
                    }
                }

                $local_name = $this->package->Archive->getLocalFilePath($file);
                if (!$this->zipArchive->addFile($file, $local_name)) {
                    // Assumption is that we continue?? for some things this would be fatal others it would be ok - leave up to user
                    DUP_PRO_Log::info("WARNING: Unable to zip file: {$file}");
                    continue;
                }

                $total_file_size += filesize($file);

                //ST: SERVER THROTTLE
                if ($this->throttleDelayInUs !== 0) {
                    usleep($this->throttleDelayInUs);
                }

                //Prevent Overflow
                if ($countFiles++ > $total_file_count_trip) {
                    DUP_PRO_Log::error("ZipArchive-ST: file loop overflow detected at {$countFiles}", '', false);
                    return $build_progress->failed = true;
                }
            }

            //START ARCHIVE CLOSE
            $total_file_size_easy = DUP_PRO_U::byteSize($total_file_size);
            DUP_PRO_Log::trace("Doing final zip close after adding $total_file_size_easy ({$total_file_size})");
            if ($this->zipArchive->close()) {
                DUP_PRO_Log::trace("Final zip closed.");
                $build_progress->next_archive_file_index = $countFiles;
                $build_progress->archive_built           = true;
                $this->package->update();
            } else {
                if ($this->global->ziparchive_validation === false) {
                    $this->global->ziparchive_validation = true;
                    $this->global->save();
                    DUP_PRO_Log::infoTrace("**NOTICE: ZipArchive: validation mode enabled");
                } else {
                    $err = 'ZipArchive close failure during file phase with file validation enabled';
                    $this->setDupArchiveSwitchFix($err);
                    return $build_progress->failed = true;
                }
            }
        }

        //============================================
        //ST: LOG FINAL RESULTS
        //============================================
        if ($build_progress->archive_built) {
            $timerAllEnd = DUP_PRO_U::getMicrotime();
            $timerAllSum = DUP_PRO_U::elapsedTime($timerAllEnd, $build_progress->archive_start_time);
            $zipFileSize = @filesize($zipPath);
            DUP_PRO_Log::info("MEMORY STACK: " . DUP_PRO_Server::getPHPMemory());
            DUP_PRO_Log::info("FINAL SIZE: " . DUP_PRO_U::byteSize($zipFileSize));
            DUP_PRO_Log::info("ARCHIVE RUNTIME: {$timerAllSum}");

            if ($this->zipArchive->open()) {
                $this->package->Archive->file_count = $this->zipArchive->getNumFiles();
                $this->package->update();
                $this->zipArchive->close();
            } else {
                DUP_PRO_Log::error("ZipArchive open failure.", "Encountered when retrieving final archive file count.", false);
                return $build_progress->failed = true;
            }
        }

        return true;
    }

    /**
     * Creates the zip file using a multi-thread approach
     *
     * @param BuildProgress $build_progress A copy of the current build progress
     *
     * @returns bool    Returns true if the process was successful
     */
    private function createMultiThreaded(BuildProgress $build_progress)
    {
        $timed_out   = false;
        $countFiles  = 0;
        $compressDir = rtrim(SnapIO::safePath($this->package->Archive->PackDir), '/');
        $sqlPath     = $this->package->StorePath . '/' . $this->package->Database->File;
        $zipPath     = $this->package->StorePath . '/' . $this->package->Archive->File;
        $filterDirs  = empty($this->package->Archive->FilterDirs)  ? 'not set' : rtrim(str_replace(';', "\n\t", $this->package->Archive->FilterDirs));
        $filterFiles = empty($this->package->Archive->FilterFiles) ? 'not set' : rtrim(str_replace(';', "\n\t", $this->package->Archive->FilterFiles));
        $filterExts  = empty($this->package->Archive->FilterExts) ? 'not set' : $this->package->Archive->FilterExts;
        $filterOn    = ($this->package->Archive->FilterOn) ? 'ON' : 'OFF';
        $compression = $build_progress->current_build_compression ? 'ON' : 'OFF';
        $this->zipArchive->setCompressed($build_progress->current_build_compression);
        $scanFilepath = DUPLICATOR_PRO_SSDIR_PATH_TMP . "/{$this->package->NameHash}_scan.json";

        //LOAD SCAN REPORT
        try {
            $scanReport = $this->package->getScanReportFromJson($scanFilepath);
        } catch (DUP_PRO_NoScanFileException $ex) {
            DUP_PRO_Log::trace("**** scan file $scanFilepath doesn't exist!!");
            DUP_PRO_Log::error($ex->getMessage(), '', false);
            $build_progress->failed = true;
            return true;
        } catch (DUP_PRO_NoFileListException $ex) {
            DUP_PRO_Log::trace("**** list of files doesn't exist!!");
            DUP_PRO_Log::error($ex->getMessage(), '', false);
            $build_progress->failed = true;
            return true;
        } catch (DUP_PRO_NoDirListException $ex) {
            DUP_PRO_Log::trace("**** list of directories doesn't exist!!");
            DUP_PRO_Log::error($ex->getMessage(), '', false);
            $build_progress->failed = true;
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
            $systemGlobal->save();
            $build_progress->failed = true;
            return true;
        }

        //============================================
        //MT: START ZIP & ADD SQL FILE
        //============================================
        if ($build_progress->archive_started === false) {
            DUP_PRO_Log::info("\n********************************************************************************");
            DUP_PRO_Log::info("ARCHIVE Mode:ZipArchive Multi-Threaded");
            DUP_PRO_Log::info("********************************************************************************");
            DUP_PRO_Log::info("ARCHIVE DIR:  " . $compressDir);
            DUP_PRO_Log::info("ARCHIVE FILE: " . basename($zipPath));
            DUP_PRO_Log::info("COMPRESSION: *{$compression}*");
            DUP_PRO_Log::info("FILTERS: *{$filterOn}*");
            DUP_PRO_Log::info("DIRS:  {$filterDirs}");
            DUP_PRO_Log::info("EXTS:  {$filterExts}");
            DUP_PRO_Log::info("FILES:  {$filterFiles}");
            DUP_PRO_Log::info("----------------------------------------");
            DUP_PRO_Log::info("COMPRESSING");
            DUP_PRO_Log::info("SIZE:\t" . $scanReport->ARC->Size);
            DUP_PRO_Log::info("STATS:\tDirs " . $scanReport->ARC->DirCount . " | Files " . $scanReport->ARC->FileCount . " | Total " . $scanReport->ARC->FullCount);
            if (($scanReport->ARC->DirCount == '') || ($scanReport->ARC->FileCount == '') || ($scanReport->ARC->FullCount == '')) {
                DUP_PRO_Log::error('Invalid Scan Report Detected', 'Invalid Scan Report Detected', false);
                return $build_progress->failed = true;
            }

            if (!$this->zipArchive->open()) {
                DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
                return $build_progress->failed = true;
            }

            if ($this->zipArchive->addFile($sqlPath, $this->package->get_sql_ark_file_path())) {
                DUP_PRO_Log::info("SQL ADDED: " . basename($sqlPath));
            } else {
                DUP_PRO_Log::error("Unable to add database.sql to archive.", "SQL File Path [" . $sqlPath . "]", false);
                return $build_progress->failed = true;
            }

            if ($this->zipArchive->close()) {
                $build_progress->archive_has_database = true;
                $this->package->update();
            } else {
                $err = 'ZipArchive close failure during database.sql phase.';
                $this->setDupArchiveSwitchFix($err);
                return $build_progress->failed = true;
            }
        }

        //============================================
        //MT: ZIP DIRECTORIES
        //Keep this loop tight: ZipArchive can handle over 10k dir entries in under 0.01 seconds.
        //Its really fast without files no need to do status pushes or other checks in loop
        //============================================
        if ($this->zipArchive->open()) {
            foreach ($scanReport->ARC->Dirs as $dir) {
                $emptyDir = $this->package->Archive->getLocalDirPath($dir);
                DUP_PRO_Log::trace("ADD DIR TO ZIP: '{$emptyDir}'");
                if (!$this->zipArchive->addEmptyDir($emptyDir)) {
                    if (empty($compressDir) || strpos($dir, rtrim($compressDir, '/')) != 0) {
                        DUP_PRO_Log::infoTrace("WARNING: Unable to zip directory: '{$dir}'");
                    }
                }
                $build_progress->next_archive_dir_index++;
            }

            $this->package->update();
            if ($build_progress->timedOut($this->global->php_max_worker_time_in_sec)) {
                $timed_out = true;
                $diff      = time() - $build_progress->thread_start_time;
                DUP_PRO_Log::trace("Timed out after hitting thread time of $diff {$this->global->php_max_worker_time_in_sec} so quitting zipping early in the directory phase");
            }
        } else {
            DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
            return $build_progress->failed = true;
        }

        if ($this->zipArchive->close() === false) {
            $err = DUP_PRO_U::__('ZipArchive close failure during directory add phase.');
            $this->setDupArchiveSwitchFix($err);
            return $build_progress->failed = true;
        }

        //============================================
        //MT: ZIP FILES
        //============================================
        if ($timed_out === false) {
            // PREVENT RETRIES (10x)
            if ($build_progress->retries > DUP_PRO_Constants::MAX_BUILD_RETRIES) {
                $err = DUP_PRO_U::__('Zip build appears stuck.');
                $this->setDupArchiveSwitchFix($err);

                $error_msg = DUP_PRO_U::__('Package build appears stuck so marking package failed. Recommend setting Settings > Packages > Archive Engine to DupArchive');
                DUP_PRO_Log::error(DUP_PRO_U::__('Build Failure'), $error_msg, false);
                DUP_PRO_Log::trace($error_msg);
                return $build_progress->failed = true;
            } else {
                $build_progress->retries++;
                $this->package->update();
            }

            $zip_is_open                    = false;
            $total_file_size                = 0;
            $incremental_file_size          = 0;
            $used_zip_file_descriptor_count = 0;
            $total_file_count               = empty($scanReport->ARC->UFileCount) ? 0 : $scanReport->ARC->UFileCount;
            foreach ($scanReport->ARC->Files as $file) {
                if ($zip_is_open || ($countFiles == $build_progress->next_archive_file_index)) {
                    if ($zip_is_open === false) {
                        DUP_PRO_Log::trace("resuming archive building at file # $countFiles");
                        if ($this->zipArchive->open() !== true) {
                            DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
                            $build_progress->failed = true;
                            return true;
                        }
                        $zip_is_open = true;
                    }

                    //NON-ASCII check
                    if (preg_match('/[^\x20-\x7f]/', $file)) {
                        if (!$this->isUTF8FileSafe($file)) {
                            continue;
                        }
                    } elseif (!file_exists($file)) {
                            DUP_PRO_Log::trace("NOTICE: ASCII file [{$file}] does not exist!");
                            continue;
                    }

                    $local_name = $this->package->Archive->getLocalFilePath($file);
                    $file_size  = filesize($file);
                    $zip_status = $this->zipArchive->addFile($file, $local_name);

                    if ($zip_status) {
                        $total_file_size       += $file_size;
                        $incremental_file_size += $file_size;
                    } else {
                        // Assumption is that we continue?? for some things this would be fatal others it would be ok - leave up to user
                        DUP_PRO_Log::info("WARNING: Unable to zip file: {$file}");
                    }

                    $countFiles++;
                    $chunk_size_in_bytes = $this->global->ziparchive_chunk_size_in_mb * 1000000;
                    if ($incremental_file_size > $chunk_size_in_bytes) {
                    // Only close because of chunk size and file descriptors when in legacy mode
                        DUP_PRO_Log::trace("closing zip because ziparchive mode = {$this->global->ziparchive_mode} fd count = $used_zip_file_descriptor_count or incremental file size=$incremental_file_size and chunk size = $chunk_size_in_bytes");
                        $incremental_file_size          = 0;
                        $used_zip_file_descriptor_count = 0;
                        if ($this->zipArchive->close() == true) {
                            $adjusted_percent                        = floor(DUP_PRO_PackageStatus::ARCSTART + ((DUP_PRO_PackageStatus::ARCDONE - DUP_PRO_PackageStatus::ARCSTART) * ($countFiles / (float) $total_file_count)));
                            $build_progress->next_archive_file_index = $countFiles;
                            $build_progress->retries                 = 0;
                            $this->package->Status                   = $adjusted_percent;
                            $this->package->update();
                            $zip_is_open = false;
                            DUP_PRO_Log::trace("closed zip");
                        } else {
                            $err = 'ZipArchive close failure during file phase using multi-threaded setting.';
                            $this->setDupArchiveSwitchFix($err);
                            return $build_progress->failed = true;
                        }
                    }

                    //MT: SERVER THROTTLE
                    if ($this->throttleDelayInUs !== 0) {
                        usleep($this->throttleDelayInUs);
                    }

                    //MT: MAX WORKER TIME (SECS)
                    if ($build_progress->timedOut($this->global->php_max_worker_time_in_sec)) {
                        // Only close because of timeout
                        $timed_out = true;
                        $diff      = time() - $build_progress->thread_start_time;
                        DUP_PRO_Log::trace("Timed out after hitting thread time of $diff so quitting zipping early in the file phase");
                        break;
                    }

                    //MT: MAX BUILD TIME (MINUTES)
                    //Only stop to check on larger files above 100K to avoid checking every single file
                    if ($file_size > $this->maxBuildTimeFileSize && $this->optMaxBuildTimeOn) {
                        $elapsed_sec     = time() - $this->package->timer_start;
                        $elapsed_minutes = $elapsed_sec / 60;
                        if ($elapsed_minutes > $this->global->max_package_runtime_in_min) {
                            DUP_PRO_Log::trace("ZipArchive: Multi-thread max build time {$this->global->max_package_runtime_in_min} minutes reached killing process.");
                            return false;
                        }
                    }
                } else {
                    $countFiles++;
                }
            }

            DUP_PRO_Log::trace("total file size added to zip = $total_file_size");
            if ($zip_is_open) {
                DUP_PRO_Log::trace("Doing final zip close after adding $incremental_file_size");
                if ($this->zipArchive->close()) {
                    DUP_PRO_Log::trace("Final zip closed.");
                    $build_progress->next_archive_file_index = $countFiles;
                    $build_progress->retries                 = 0;
                    $this->package->update();
                } else {
                    $err = DUP_PRO_U::__('ZipArchive close failure.');
                    $this->setDupArchiveSwitchFix($err);
                    DUP_PRO_Log::error($err);
                    return $build_progress->failed = true;
                }
            }
        }


        //============================================
        //MT: LOG FINAL RESULTS
        //============================================
        if ($timed_out === false) {
            $build_progress->archive_built = true;
            $build_progress->retries       = 0;
            $this->package->update();
            $timerAllEnd = DUP_PRO_U::getMicrotime();
            $timerAllSum = DUP_PRO_U::elapsedTime($timerAllEnd, $build_progress->archive_start_time);
            $zipFileSize = @filesize($zipPath);
            DUP_PRO_Log::info("COMPRESSED SIZE: " . DUP_PRO_U::byteSize($zipFileSize));
            DUP_PRO_Log::info("ARCHIVE RUNTIME: {$timerAllSum}");
            DUP_PRO_Log::info("MEMORY STACK: " . DUP_PRO_Server::getPHPMemory());
            if ($this->zipArchive->open() === true) {
                $this->package->Archive->file_count = $this->zipArchive->getNumFiles();
                $this->package->update();
                $this->zipArchive->close();
            } else {
                DUP_PRO_Log::error("ZipArchive open failure.", "Encountered when retrieving final archive file count.", false);
                return $build_progress->failed = true;
            }
        }

        return !$timed_out;
    }

    /**
     * Encodes a UTF8 file and then determines if it is safe to add to an archive
     *
     * @param string $file The file to test
     *
     * @returns bool Returns true if the file is readable and safe to add to archive
     */
    private function isUTF8FileSafe($file)
    {
        $is_safe       = true;
        $original_file = $file;
        DUP_PRO_Log::trace("[{$file}] is non ASCII");
// Necessary for adfron type files
        if (DUP_PRO_STR::hasUTF8($file)) {
            $file = utf8_decode($file);
        }

        if (file_exists($file) === false) {
            if (file_exists($original_file) === false) {
                DUP_PRO_Log::trace("$file CAN'T BE READ!");
                DUP_PRO_Log::info("WARNING: Unable to zip file: {$file}. Cannot be read");
                $is_safe = false;
            }
        }

        return $is_safe;
    }

    /**
     * Wrapper for switching to DupArchive quick fix
     *
     * @param string $message The error message
     *
     * @returns null
     */
    private function setDupArchiveSwitchFix($message)
    {
        $fix_text = DUP_PRO_U::__('Click to switch archive engine to DupArchive.');

        $this->setFix(
            $message,
            $fix_text,
            array(
                'global' => array(
                    'archive_build_mode' => DUP_PRO_Archive_Build_Mode::DupArchive
                )
            )
        );
    }

    /**
     * Sends an error to the trace and build logs and sets the UI message
     *
     * @param string $message The error message
     * @param string $fix     The details for how to fix the issue
     *
     * @returns null
     */
    private function setFix($message, $fix, $option)
    {
        DUP_PRO_Log::trace($message);
        DUP_PRO_Log::error("$message **FIX:  $fix.", '', false);
        $system_global = DUP_PRO_System_Global_Entity::getInstance();
        $system_global->addQuickFix($message, $fix, $option);
    }
}
