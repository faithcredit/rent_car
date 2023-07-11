<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Shell\Shell;
use Duplicator\Package\Create\BuildProgress;

/**
 *  Creates a zip file using Shell_Exec and the system zip command
 *  Not available on all system
 **/
class DUP_PRO_ShellZip
{
    /**
     * Creates the zip file and adds the SQL file to the archive
     *
     * @param DUP_PRO_Package $package
     * @param BuildProgress   $build_progress
     *
     * @return boolean
     */
    public static function create(DUP_PRO_Package $package, BuildProgress $build_progress)
    {
        $archive = $package->Archive;
        try {
            if ($package->Status == DUP_PRO_PackageStatus::ARCSTART) {
                $error_text    = DUP_PRO_U::__('Zip process getting killed due to limited server resources.');
                $fix_text      = DUP_PRO_U::__('Click to switch Archive Engine DupArchive.');
                $system_global = DUP_PRO_System_Global_Entity::getInstance();
                $system_global->addQuickFix(
                    $error_text,
                    $fix_text,
                    array(
                        'global' => array(
                            'archive_build_mode' => 3
                        )
                    )
                );
                DUP_PRO_Log::traceError("$error_text  **RECOMMENDATION: $fix_text");
                if ($build_progress->retries > 1) {
                    $build_progress->failed = true;
                    return true;
                } else {
                    $build_progress->retries++;
                    $package->update();
                }
            }

            do_action('duplicator_pro_package_before_set_status', $package, DUP_PRO_PackageStatus::ARCSTART);
            $package->Status = DUP_PRO_PackageStatus::ARCSTART;
            $package->update();
            $package->safe_tmp_cleanup(true);
            do_action('duplicator_pro_package_after_set_status', $package, DUP_PRO_PackageStatus::ARCSTART);
            $compressDir  = rtrim(SnapIO::safePath($archive->PackDir), '/');
            $zipPath      = SnapIO::safePath("{$package->StorePath}/{$archive->File}");
            $sql_filepath = SnapIO::safePath("{$package->StorePath}/{$package->Database->File}");
            $filterDirs   = empty($archive->FilterDirs) ? 'not set' : rtrim(str_replace(';', "\n\t", $archive->FilterDirs));
            $filterFiles  = empty($archive->FilterFiles) ? 'not set' : rtrim(str_replace(';', "\n\t", $archive->FilterFiles));
            $filterExts   = empty($archive->FilterExts) ? 'not set' : $archive->FilterExts;
            $filterOn     = ($archive->FilterOn) ? 'ON' : 'OFF';
            $scanFilepath = DUPLICATOR_PRO_SSDIR_PATH_TMP . "/{$package->NameHash}_scan.json";
            // LOAD SCAN REPORT
            try {
                $scanReport = $package->getScanReportFromJson($scanFilepath);
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

            DUP_PRO_Log::info("\n********************************************************************************");
            DUP_PRO_Log::info("ARCHIVE  Type=ZIP Mode=Shell");
            DUP_PRO_Log::info("********************************************************************************");
            DUP_PRO_Log::info("ARCHIVE DIR:  " . $compressDir);
            DUP_PRO_Log::info("ARCHIVE FILE: " . basename($zipPath));
            DUP_PRO_Log::info("FILTERS: *{$filterOn}*");
            DUP_PRO_Log::info("DIRS:  {$filterDirs}");
            DUP_PRO_Log::info("EXTS:  {$filterExts}");
            DUP_PRO_Log::info("FILES:  {$filterFiles}");
            DUP_PRO_Log::info("----------------------------------------");
            DUP_PRO_Log::info("COMPRESSING");
            DUP_PRO_Log::info("SIZE:\t" . $scanReport->ARC->Size);
            DUP_PRO_Log::info("STATS:\tDirs " . $scanReport->ARC->DirCount . " | Files " . $scanReport->ARC->FileCount . " | Total " . $scanReport->ARC->FullCount);
            $build_progress->archive_started    = true;
            $build_progress->archive_start_time = DUP_PRO_U::getMicrotime();
            $contains_root                      = false;
            $exclude_string                     = '';
            $filterDirs                         = $archive->FilterDirsAll;
            $filterExts                         = $archive->FilterExtsAll;
            $filterFiles                        = $archive->FilterFilesAll;
            // DIRS LIST
            foreach ($filterDirs as $filterDir) {
                if (trim($filterDir) != '') {
                    $relative_filter_dir = DUP_PRO_U::getRelativePath($compressDir, $filterDir);
                    DUP_PRO_Log::trace("Adding relative filter dir $relative_filter_dir for $filterDir relative to $compressDir");
                    if (trim($relative_filter_dir) == '') {
                        $contains_root = true;
                        break;
                    } else {
                        $exclude_string .= DUP_PRO_Zip_U::customShellArgEscapeSequence($relative_filter_dir) . "**\* ";
                        $exclude_string .= DUP_PRO_Zip_U::customShellArgEscapeSequence($relative_filter_dir) . " ";
                    }
                }
            }

            //EXT LIST
            foreach ($filterExts as $filterExt) {
                $exclude_string .= "\*.$filterExt ";
            }

            //FILE LIST
            foreach ($filterFiles as $filterFile) {
                if (trim($filterFile) != '') {
                    $relative_filter_file = DUP_PRO_U::getRelativePath($compressDir, trim($filterFile));
                    DUP_PRO_Log::trace("Full file=$filterFile relative=$relative_filter_file compressDir=$compressDir");
                    $exclude_string .= "\"$relative_filter_file\" ";
                }
            }

            //DB ONLY
            if ($archive->ExportOnlyDB) {
                $contains_root = true;
            }


            if ($contains_root == false) {
                // Only attempt to zip things up if root isn't in there since stderr indicates when it cant do anything
                $storages = DUP_PRO_Storage_Entity::get_all();
                foreach ($storages as $storage) {
                    if (
                        $storage->storage_type == DUP_PRO_Storage_Types::Local &&
                        $storage->local_filter_protection &&
                        $storage->id != DUP_PRO_Virtual_Storage_IDs::Default_Local
                    ) {
                        $storage_path    = SnapIO::safePath($storage->local_storage_folder);
                        $storage_path    = DUP_PRO_U::getRelativePath($compressDir, $storage_path);
                        $exclude_string .= "$storage_path**\* ";
                    }
                }

                $relative_backup_dir = DUP_PRO_U::getRelativePath($compressDir, DUPLICATOR_PRO_SSDIR_PATH);
                $exclude_string     .= "$relative_backup_dir**\* ";
                $params              = Shell::getCompressionParam($build_progress->current_build_compression);
                if (strlen($package->Archive->getArchivePassword()) > 0) {
                    $params .= ' --password ' . escapeshellarg($package->Archive->getArchivePassword());
                }
                $params .= ' -rq';

                $command  = 'cd ' . escapeshellarg($compressDir);
                $command .= ' && ' . escapeshellcmd(DUP_PRO_Zip_U::getShellExecZipPath()) . ' ' . $params . ' ';
                $command .= escapeshellarg($zipPath) . ' ./';
                $command .= " -x $exclude_string 2>&1";
                DUP_PRO_Log::infoTrace("SHELL COMMAND: $command");
                $shellOutput = Shell::runCommand($command, Shell::AVAILABLE_COMMANDS);
                DUP_PRO_Log::trace("After shellzip command");
                if ($shellOutput !== false && !$shellOutput->isEmpty()) {
                    $stderr        = $shellOutput->getOutputAsString();
                    $error_text    = "Error executing shell exec zip: $stderr.";
                    $system_global = DUP_PRO_System_Global_Entity::getInstance();
                    if (DUP_PRO_STR::contains($stderr, 'quota')) {
                        $fix_text = DUP_PRO_U::__("Account out of space so purge large files or talk to your host about increasing quota.");
                        $system_global->addTextFix($error_text, $fix_text);
                    } elseif (DUP_PRO_STR::contains($stderr, 'such file or')) {
                        $fix_text = sprintf(
                            "%s <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-package-160-q' target='_blank'>%s</a>",
                            DUP_PRO_U::__('See FAQ:'),
                            DUP_PRO_U::__('How to resolve "zip warning: No such file or directory"?')
                        );
                        $system_global->addTextFix($error_text, $fix_text);
                    } else {
                        $fix_text = DUP_PRO_U::__("Click on button to switch to the DupArchive engine.");
                        $system_global->addQuickFix(
                            $error_text,
                            $fix_text,
                            array(
                                'global' => array(
                                    'archive_build_mode' => 3
                                )
                            )
                        );
                    }
                    DUP_PRO_Log::error("$error_text  **RECOMMENDATION: $fix_text", '', false);
                    $build_progress->failed = true;
                    return true;
                } else {
                    DUP_PRO_Log::trace("Stderr is null");
                }

                $file_count_string = '';
                if (!file_exists($zipPath)) {
                    $file_count_string = DUP_PRO_U::__("$zipPath doesn't exist!");
                } elseif (DUP_PRO_U::getExeFilepath('zipinfo') != null) {
                    DUP_PRO_Log::trace("zipinfo exists");
                    $file_count_string = "zipinfo -t '$zipPath'";
                } elseif (DUP_PRO_U::getExeFilepath('unzip') != null) {
                    DUP_PRO_Log::trace("zipinfo doesn't exist so reverting to unzip");
                    $file_count_string = "unzip -l '$zipPath' | wc -l";
                }

                if ($file_count_string != '') {
                    $shellOutput = Shell::runCommand($file_count_string . ' | awk \'{print $1 }\'', Shell::AVAILABLE_COMMANDS);
                    $file_count  = ($shellOutput !== false)
                        ? trim($shellOutput->getOutputAsString())
                        : null;

                    if (is_numeric($file_count)) {
                    // Accounting for the sql and installer back files
                        $archive->file_count = (int) $file_count + 2;
                    } else {
                        $error_text = DUP_PRO_U::__("Error retrieving file count in shell zip $file_count.");
                        DUP_PRO_Log::trace("Executed file count string of $file_count_string");
                        DUP_PRO_Log::trace($error_text);
                        $fix_text      = DUP_PRO_U::__("Click on button to switch to the DupArchive engine.");
                        $system_global = DUP_PRO_System_Global_Entity::getInstance();
                        $system_global->addQuickFix(
                            $error_text,
                            $fix_text,
                            array(
                                'global' => array(
                                    'archive_build_mode' => 3
                                )
                            )
                        );
                        DUP_PRO_Log::error("$error_text  **RECOMMENDATION:$fix_text", '', false);
                        DUP_PRO_Log::trace("$error_text  **RECOMMENDATION:$fix_text");
                        $build_progress->failed = true;
                        $archive->file_count    = -2;
                        return true;
                    }
                } else {
                    DUP_PRO_Log::trace("zipinfo doesn't exist");
                    // The -1 and -2 should be constants since they signify different things
                    $archive->file_count = -1;
                }
            } else {
                $archive->file_count = 2;
                // Installer bak and database.sql
            }

            DUP_PRO_Log::trace("archive file count from shellzip is $archive->file_count");
            $build_progress->archive_built = true;
            $build_progress->retries       = 0;
            $package->update();
            $timerAllEnd = DUP_PRO_U::getMicrotime();
            $timerAllSum = DUP_PRO_U::elapsedTime($timerAllEnd, $build_progress->archive_start_time);
            $zipFileSize = @filesize($zipPath);
            DUP_PRO_Log::info("COMPRESSED SIZE: " . DUP_PRO_U::byteSize($zipFileSize));
            DUP_PRO_Log::info("ARCHIVE RUNTIME: {$timerAllSum}");
            DUP_PRO_Log::info("MEMORY STACK: " . DUP_PRO_Server::getPHPMemory());
        } catch (Exception $e) {
            DUP_PRO_Log::error("Runtime error in shell exec zip compression.", "Exception: {$e}");
        }

        return true;
    }
}
