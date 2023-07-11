<?php

defined("ABSPATH") or die("");

use Duplicator\Addons\ProBase\License\Notices;
use Duplicator\Ajax\AbstractAjaxService;
use Duplicator\Ajax\AjaxWrapper;
use Duplicator\Ajax\ServicesDashboard;
use Duplicator\Ajax\ServicesImport;
use Duplicator\Ajax\ServicesRecovery;
use Duplicator\Ajax\ServicesSchedule;
use Duplicator\Ajax\ServicesSettings;
use Duplicator\Ajax\ServicesStorage;
use Duplicator\Core\CapMng;
use Duplicator\Core\MigrationMng;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Package\Storage\LocalStorage;
use Duplicator\Utils\ExpireOptions;
use Duplicator\Utils\IncrementalStatusMessage;
use Duplicator\Utils\ZipArchiveExtended;
use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use Duplicator\Utils\SFTPAdapter;
use Duplicator\Utils\Exceptions\ChunkingTimeoutException;

abstract class DUP_PRO_Web_Service_Execution_Status
{
    const Pass            = 1;
    const Warn            = 2;
    const Fail            = 3;
    const Incomplete      = 4; // Still more to go
    const ScheduleRunning = 5;
}

class DUP_PRO_Web_Services extends AbstractAjaxService
{
    public function init()
    {
        $importServices = new ServicesImport();
        $importServices->init();
        $recoveryService = new ServicesRecovery();
        $recoveryService->init();
        $scheduleService = new ServicesSchedule();
        $scheduleService->init();
        $storageService = new ServicesStorage();
        $storageService->init();
        $dashboardService = new ServicesDashboard();
        $dashboardService->init();
        $settingsService = new ServicesSettings();
        $settingsService->init();

        $this->addAjaxCall('wp_ajax_duplicator_pro_package_scan', 'duplicator_pro_package_scan');
        $this->addAjaxCall('wp_ajax_duplicator_pro_package_delete', 'duplicator_pro_package_delete');
        $this->addAjaxCall('wp_ajax_duplicator_pro_reset_user_settings', 'duplicator_pro_reset_user_settings');
        $this->addAjaxCall('wp_ajax_duplicator_pro_reset_packages', 'duplicator_pro_reset_packages');

        $this->addAjaxCall('wp_ajax_duplicator_pro_dropbox_send_file_test', 'duplicator_pro_dropbox_send_file_test');
        $this->addAjaxCall('wp_ajax_duplicator_pro_gdrive_send_file_test', 'duplicator_pro_gdrive_send_file_test');
        $this->addAjaxCall('wp_ajax_duplicator_pro_sftp_send_file_test', 'duplicator_pro_sftp_send_file_test');
        $this->addAjaxCall('wp_ajax_duplicator_pro_s3_send_file_test', 'duplicator_pro_s3_send_file_test');
        $this->addAjaxCall('wp_ajax_duplicator_pro_onedrive_send_file_test', 'duplicator_pro_onedrive_send_file_test');

        $this->addAjaxCall('wp_ajax_duplicator_pro_ftp_send_file_test', 'duplicator_pro_ftp_send_file_test');
        $this->addAjaxCall('wp_ajax_duplicator_pro_local_storage_test', 'duplicator_pro_local_storage_test');

        $this->addAjaxCall('wp_ajax_duplicator_pro_get_storage_details', 'duplicator_pro_get_storage_details');

        $this->addAjaxCall('wp_ajax_duplicator_pro_get_trace_log', 'get_trace_log');
        $this->addAjaxCall('wp_ajax_duplicator_pro_delete_trace_log', 'delete_trace_log');
        $this->addAjaxCall('wp_ajax_duplicator_pro_get_package_statii', 'get_package_statii');
        $this->addAjaxCall('wp_ajax_duplicator_pro_get_package_status', 'duplicator_pro_get_package_status');
        $this->addAjaxCall('wp_ajax_duplicator_pro_get_package_log', 'get_package_log');
        $this->addAjaxCall('wp_ajax_duplicator_pro_get_package_delete', 'duplicator_pro_get_package_delete');
        $this->addAjaxCall('wp_ajax_duplicator_pro_is_pack_running', 'is_pack_running');

        $this->addAjaxCall('wp_ajax_duplicator_pro_process_worker', 'process_worker');
        $this->addAjaxCall('wp_ajax_nopriv_duplicator_pro_process_worker', 'process_worker');

        $this->addAjaxCall('wp_ajax_duplicator_pro_gdrive_get_auth_url', 'get_gdrive_auth_url');
        $this->addAjaxCall('wp_ajax_duplicator_pro_dropbox_get_auth_url', 'get_dropbox_auth_url');
        $this->addAjaxCall('wp_ajax_duplicator_pro_onedrive_get_auth_url', 'get_onedrive_auth_url');
        $this->addAjaxCall('wp_ajax_duplicator_pro_onedrive_get_logout_url', 'get_onedrive_logout_url');

        $this->addAjaxCall('wp_ajax_duplicator_pro_manual_transfer_storage', 'manual_transfer_storage');

        /* Screen-Specific Web Methods */
        $this->addAjaxCall('wp_ajax_duplicator_pro_packages_details_transfer_get_package_vm', 'packages_details_transfer_get_package_vm');

        /* Granular Web Methods */
        $this->addAjaxCall('wp_ajax_duplicator_pro_package_stop_build', 'package_stop_build');
        $this->addAjaxCall('wp_ajax_duplicator_pro_export_settings', 'export_settings');

        $this->addAjaxCall('wp_ajax_duplicator_pro_brand_delete', 'duplicator_pro_brand_delete');

        /* Quick Fix */
        $this->addAjaxCall('wp_ajax_duplicator_pro_quick_fix', 'duplicator_pro_quick_fix');

        /* Dir scan utils */
        $this->addAjaxCall('wp_ajax_duplicator_pro_get_folder_children', 'duplicator_pro_get_folder_children');

        $this->addAjaxCall('wp_ajax_duplicator_pro_restore_backup_prepare', 'duplicator_pro_restore_backup_prepare');

        $this->addAjaxCall('wp_ajax_duplicator_pro_admin_notice_to_dismiss', 'admin_notice_to_dismiss');

        $this->addAjaxCall('wp_ajax_duplicator_pro_download_package_file', 'download_package_file');
        $this->addAjaxCall('wp_ajax_nopriv_duplicator_pro_download_package_file', 'download_package_file');
    }

    public function duplicator_pro_restore_backup_prepare_callback()
    {
        $packageId = filter_input(INPUT_POST, 'packageId', FILTER_VALIDATE_INT);
        if (!$packageId) {
            throw new Exception('Invalid package ID in request.');
        }
        $result = array();

        if (($package = DUP_PRO_Package::get_by_id($packageId)) === false) {
            throw new Exception(DUP_PRO_U::esc_html__('Invalid package ID'));
        }
        $updDirs = wp_upload_dir();

        $result = DUPLICATOR_PRO_SSDIR_URL . '/' . $package->Installer->getInstallerName() . '?dup_folder=dupinst_' . $package->Hash;

        $installerParams = array(
            'inst_mode'              => array(
                'value' => 2 // mode restore backup
            ),
            'url_old'                => array(
                'formStatus' => "st_skip"
            ),
            'url_new'                => array(
                'value'      => DUP_PRO_Archive::getOriginalUrls('home'),
                'formStatus' => "st_infoonly"
            ),
            'path_old'               => array(
                'formStatus' => "st_skip"
            ),
            'path_new'               => array(
                'value'      => duplicator_pro_get_home_path(),
                'formStatus' => "st_infoonly"
            ),
            'dbaction'               => array(
                'value'      => 'empty',
                'formStatus' => "st_infoonly"
            ),
            'dbhost'                 => array(
                'value'      => DB_HOST,
                'formStatus' => "st_infoonly"
            ),
            'dbname'                 => array(
                'value'      => DB_NAME,
                'formStatus' => "st_infoonly"
            ),
            'dbuser'                 => array(
                'value'      => DB_USER,
                'formStatus' => "st_infoonly"
            ),
            'dbpass'                 => array(
                'value'      => DB_PASSWORD,
                'formStatus' => "st_infoonly"
            ),
            'dbtest_ok'              => array(
                'value' => true
            ),
            'siteurl_old'            => array(
                'formStatus' => "st_skip"
            ),
            'siteurl'                => array(
                'value'      => 'site_url',
                'formStatus' => "st_skip"
            ),
            'path_cont_old'          => array(
                'formStatus' => "st_skip"
            ),
            'path_cont_new'          => array(
                'value'      => WP_CONTENT_DIR,
                'formStatus' => "st_skip"
            ),
            'path_upl_old'           => array(
                'formStatus' => "st_skip"
            ),
            'path_upl_new'           => array(
                'value'      => $updDirs['basedir'],
                'formStatus' => "st_skip"
            ),
            'url_cont_old'           => array(
                'formStatus' => "st_skip"
            ),
            'url_cont_new'           => array(
                'value'      => content_url(),
                'formStatus' => "st_skip"
            ),
            'url_upl_old'            => array(
                'formStatus' => "st_skip"
            ),
            'url_upl_new'            => array(
                'value'      => $updDirs['baseurl'],
                'formStatus' => "st_skip"
            ),
            'exe_safe_mode'          => array(
                'formStatus' => "st_skip"
            ),
            'remove-redundant'       => array(
                'formStatus' => "st_skip"
            ),
            'blogname'               => array(
                'formStatus' => "st_infoonly"
            ),
            'replace_mode'           => array(
                'formStatus' => "st_skip"
            ),
            'empty_schedule_storage' => array(
                'value'      => false,
                'formStatus' => "st_skip"
            ),
            'wp_config'              => array(
                'value'      => 'original',
                'formStatus' => "st_infoonly"
            ),
            'ht_config'              => array(
                'value'      => 'original',
                'formStatus' => "st_infoonly"
            ),
            'other_config'           => array(
                'value'      => 'original',
                'formStatus' => "st_infoonly"
            ),
            'zip_filetime'           => array(
                'value'      => 'original',
                'formStatus' => "st_infoonly"
            ),
            'mode_chunking'          => array(
                'value'      => 3,
                'formStatus' => "st_infoonly"
            )
        );
        $localParamsFile = DUPLICATOR_PRO_SSDIR_PATH . '/' . DUPLICATOR_PRO_LOCAL_OVERWRITE_PARAMS . '_' . $package->get_package_hash() . '.json';
        file_put_contents($localParamsFile, SnapJson::jsonEncodePPrint($installerParams));

        return $result;
    }

    public function duplicator_pro_restore_backup_prepare()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'duplicator_pro_restore_backup_prepare_callback'),
            'duplicator_pro_restore_backup_prepare',
            $_POST['nonce'],
            CapMng::CAP_BACKUP_RESTORE
        );
    }

    public function process_worker()
    {
        DUP_PRO_Handler::init_error_handler();
        DUP_PRO_U::checkAjax();
        header("HTTP/1.1 200 OK");

        /*
          $nonce = sanitize_text_field($_REQUEST['nonce']);
          if (!wp_verify_nonce($nonce, 'duplicator_pro_process_worker')) {
          DUP_PRO_Log::trace('Security issue');
          die('Security issue');
          }
         */

        DUP_PRO_Log::trace("Process worker request");

        DUP_PRO_Package_Runner::process();

        DUP_PRO_Log::trace("Exiting process worker request");

        echo 'ok';
        exit();
    }

    public function manual_transfer_storage()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_manual_transfer_storage', 'nonce');

        $json      = array(
            'success' => false,
            'message' => ''
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'package_id'  => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'storage_ids' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_ARRAY,
                'options' => array(
                    'default' => false
                )
            )
        ));

        $package_id   = $inputData['package_id'];
        $storage_ids  = $inputData['storage_ids'];
        $json['data'] = $inputData;
        if (!$package_id || !$storage_ids) {
            $isValid = false;
        }

        try {
            if (!CapMng::can(CapMng::CAP_STORAGE, false) && !CapMng::can(CapMng::CAP_CREATE, false)) {
                throw new Exception('Security issue.');
            }
            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid request."));
            }

            if (DUP_PRO_Package::isPackageRunning()) {
                throw new Exception(DUP_PRO_U::__("Trying to queue a transfer for package $package_id but a package is already active!"));
            }

            $package = DUP_PRO_Package::get_by_id($package_id);
            DUP_PRO_Log::open($package->NameHash);

            if (!$package) {
                throw new Exception(sprintf(DUP_PRO_U::esc_html__('Could not find package ID %d!'), $package_id));
            }

            if (empty($storage_ids)) {
                throw new Exception("Please select a storage.");
            }

            $info  = "\n";
            $info .= "********************************************************************************\n";
            $info .= "********************************************************************************\n";
            $info .= "PACKAGE MANUAL TRANSFER REQUESTED: " . @date("Y-m-d H:i:s") . "\n";
            $info .= "********************************************************************************\n";
            $info .= "********************************************************************************\n\n";
            DUP_PRO_Log::infoTrace($info);

            foreach ($storage_ids as $storage_id) {
                $storage = DUP_PRO_Storage_Entity::get_by_id($storage_id);
                if (!$storage) {
                    throw new Exception(sprintf(DUP_PRO_U::__('Could not find storage ID %d!'), $storage_id));
                }

                DUP_PRO_Log::infoTrace(
                    'Storage adding to the package "' . $package->Name .
                    ' [Package Id: ' . $package_id . ']":: Storage Id: "' . $storage_id .
                    '" Storage Name: "' . esc_html($storage->name) .
                    '" Storage Type: "' . esc_html($storage->get_storage_type_string()) . '"'
                );

                /** @var DUP_PRO_Package_Upload_Info $upload_info  */
                $upload_info             = new DUP_PRO_Package_Upload_Info();
                $upload_info->storage_id = $storage_id;
                array_push($package->upload_infos, $upload_info);
            }

            $package->set_status(DUP_PRO_PackageStatus::STORAGE_PROCESSING);
            $package->timer_start = DUP_PRO_U::getMicrotime();

            $json['success'] = true;

            $package->update();
        } catch (Exception $ex) {
            $json['message'] = $ex->getMessage();
            DUP_PRO_Log::trace($ex->getMessage());
        }

        DUP_PRO_Log::close();

        die(SnapJson::jsonEncode($json));
    }

    /**
     *  DUPLICATOR_PRO_PACKAGE_SCAN
     *  Returns a json scan report object which contains data about the system
     *
     *  @param bool $not_ajax_call if true skip verify nonce and return json report object
     *
     *  @return object json report object
     *
     *  @example to test: /wp-admin/admin-ajax.php?action=duplicator_pro_package_scan
     */
    public function duplicator_pro_package_scan($not_ajax_call = false)
    {
        DUP_PRO_Handler::init_error_handler();
        try {
            CapMng::can(CapMng::CAP_CREATE);
            $global = DUP_PRO_Global_Entity::getInstance();
            if ($not_ajax_call !== true) {
                // Should be used $_REQUEST sometimes it gets in _GET and sometimes in _POST
                check_ajax_referer('duplicator_pro_package_scan', 'nonce');
                header('Content-Type: application/json');
                @ob_flush();
            }
            $json     = array();
            $errLevel = error_reporting();

            // Keep the locking file opening and closing just to avoid adding even more complexity
            $locking_file = true;
            if ($global->lock_mode == DUP_PRO_Thread_Lock_Mode::Flock) {
                $locking_file = fopen(DUPLICATOR_PRO_LOCKING_FILE_FILENAME, 'c+');
            }

            if ($locking_file != false) {
                if ($global->lock_mode == DUP_PRO_Thread_Lock_Mode::Flock) {
                    $acquired_lock = (flock($locking_file, LOCK_EX | LOCK_NB) != false);
                    if ($acquired_lock) {
                        DUP_PRO_Log::trace("File lock acquired " . DUPLICATOR_PRO_LOCKING_FILE_FILENAME);
                    } else {
                        DUP_PRO_Log::trace("File lock denied " . DUPLICATOR_PRO_LOCKING_FILE_FILENAME);
                    }
                } else {
                    $acquired_lock = DUP_PRO_U::getSqlLock();
                }

                if ($acquired_lock) {
                    @set_time_limit(0);
                    error_reporting(E_ERROR);
                    DUP_PRO_U::initStorageDirectory(true);

                    $package     = DUP_PRO_Package::get_temporary_package();
                    $package->ID = null;
                    $report      = $package->create_scan_report();
                    //After scanner runs save FilterInfo (unreadable, warnings, globals etc)
                    $package->set_temporary_package();

                    //delif($package->Archive->ScanStatus == DUP_PRO_Archive::ScanStatusComplete){
                    $report['Status'] = DUP_PRO_Web_Service_Execution_Status::Pass;

                    // The package has now been corrupted with directories and scans so cant reuse it after this point
                    DUP_PRO_Package::set_temporary_package_member('ScanFile', $package->ScanFile);
                    DUP_PRO_Package::tmp_cleanup();
                    DUP_PRO_Package::set_temporary_package_member('Status', DUP_PRO_PackageStatus::AFTER_SCAN);

                    //del}

                    if ($global->lock_mode == DUP_PRO_Thread_Lock_Mode::Flock) {
                        if (!flock($locking_file, LOCK_UN)) {
                            DUP_PRO_Log::trace("File lock can't release " . $locking_file);
                        } else {
                            DUP_PRO_Log::trace("File lock released " . $locking_file);
                        }
                        fclose($locking_file);
                    } else {
                        DUP_PRO_U::releaseSqlLock();
                    }
                } else {
                    // File is already locked indicating schedule is running
                    $report['Status'] = DUP_PRO_Web_Service_Execution_Status::ScheduleRunning;
                    DUP_PRO_Log::trace("Already locked when attempting manual build - schedule running");
                }
            } else {
                // Problem opening the locking file report this is a critical error
                $report['Status'] = DUP_PRO_Web_Service_Execution_Status::Fail;

                DUP_PRO_Log::trace("Problem opening locking file so auto switching to SQL lock mode");
                $global->lock_mode = DUP_PRO_Thread_Lock_Mode::SQL_Lock;
                $global->save();
            }
        } catch (Exception $ex) {
            $data = array(
                'Status' =>  3,
                'Message' => sprintf(DUP_PRO_U::__("Exception occurred. Exception message: %s"), $ex->getMessage()),
                'File' => $ex->getFile(),
                'Line' => $ex->getLine(),
                'Trace' => $ex->getTrace()
            );
            die(json_encode($data));
        } catch (Error $ex) {
            $data = array(
                'Status' =>  3,
                'Message' =>  sprintf(
                    DUP_PRO_U::esc_html__("Fatal Error occurred. Error message: %s<br>\nTrace: %s"),
                    $ex->getMessage(),
                    $ex->getTraceAsString()
                ),
                'File' => $ex->getFile(),
                'Line' => $ex->getLine(),
                'Trace' => $ex->getTrace()
            );
            die(json_encode($data));
        }

        try {
            $json = null;

            if ($global->json_mode == DUP_PRO_JSON_Mode::PHP) {
                try {
                    $json = SnapJson::jsonEncode($report);
                } catch (Exception $jex) {
                    DUP_PRO_Log::trace("Problem encoding using PHP JSON so switching to custom");

                    $global->json_mode = DUP_PRO_JSON_Mode::Custom;
                    $global->save();
                }
            }

            if ($json === null) {
                $json = DUP_PRO_JSON_U::customEncode($report);
            }
        } catch (Exception $ex) {
            $data = array(
                'Status' =>  3,
                'Message' =>  sprintf(DUP_PRO_U::esc_html__("Fatal Error occurred. Error message: %s"), $ex->getMessage()),
                'File' => $ex->getFile(),
                'Line' => $ex->getLine(),
                'Trace' => $ex->getTrace()
            );
            die(json_encode($data));
        }

        //$json = ($json) ? $json : '{"Status" : 3, "Message" : "Unable to encode to JSON data.  Please validate that no invalid characters exist in your file tree."}';
        error_reporting($errLevel);
        if ($not_ajax_call !== true) {
            die($json);
        } else {
            return json_decode($json);
        }
    }

    public static function getScanErrorMessage()
    {
        return '<br><b>' . DUP_PRO_U::__("Please Retry:") . '</b><br/>'
            . DUP_PRO_U::__("Unable to perform a full scan and read JSON file, please try the following actions.") . '<br/>'
            . DUP_PRO_U::__("1. Go back and create a root path directory filter to validate the site is scan-able.") . '<br/>'
            . DUP_PRO_U::__("2. Continue to add/remove filters to isolate which path is causing issues.") . '<br/>'
            . DUP_PRO_U::__("3. This message will go away once the correct filters are applied.") . '<br/><br/>'
            . '<b>' . DUP_PRO_U::__("Common Issues:") . '</b><br/>'
            . DUP_PRO_U::__("- On some budget hosts scanning over 30k files can lead to timeout/gateway issues. "
                . "Consider scanning only your main WordPress site and avoid trying to backup other external directories.") . '<br/>'
            . DUP_PRO_U::__("- Symbolic link recursion can cause timeouts.  Ask your server admin if any are present in the scan path. "
                . "If they are add the full path as a filter and try running the scan again.") . '<br/><br/>'
            . '<b>' . DUP_PRO_U::__("Details:") . '</b><br/>'
            . DUP_PRO_U::__("JSON Service:") . ' /wp-admin/admin-ajax.php?action=duplicator_pro_package_scan<br/>'
            . DUP_PRO_U::__("Scan Path:") . '[' . duplicator_pro_get_home_path() . ']<br/><br/>'
            . '<b>' . DUP_PRO_U::__("More Information:") . '</b><br/>'
            . sprintf(
                DUP_PRO_U::__('Please see the online FAQ titled <a href="%s" target="_blank">"How to resolve scanner warnings/errors and timeout issues?"</a>'),
                "https://snapcreek.com/duplicator/docs/faqs-tech/#faq-package-018-q"
            );
    }

    /**
     * DUPLICATOR_PRO_QUICK_FIX
     * Set default quick fix values automaticaly to help user
     *
     * @return void
     */
    public function duplicator_pro_quick_fix()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_quick_fix', 'nonce');

        $json      = array(
            'success' => false,
            'message' => '',
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'id'    => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'setup' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_ARRAY,
                'options' => array(
                    'default' => false
                )
            )
        ));
        $setup     = $inputData['setup'];
        $id        = $inputData['id'];

        if (!$id || empty($setup)) {
            $isValid = false;
        }
        //END OF VALIDATION

        try {
            CapMng::can(CapMng::CAP_BASIC);
            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid request."));
            }

            $data      = array();
            $isSpecial = isset($setup['special']) && is_array($setup['special']) && count($setup['special']) > 0;

            /* ****************
             *  GENERAL SETUP
             * **************** */
            if (isset($setup['global']) && is_array($setup['global'])) {
                $global = DUP_PRO_Global_Entity::getInstance();

                foreach ($setup['global'] as $object => $value) {
                    $value = DUP_PRO_U::valType($value);
                    if (isset($global->$object)) {
                        // Get current setup
                        $current = $global->$object;

                        // If setup is not the same - fix this
                        if ($current !== $value) {
                            // Set new value
                            $global->$object = $value;
                            // Check value
                            $data[$object] = $global->$object;
                        }
                    }
                }
                $global->save();
            }

            /* ****************
             *  SPECIAL SETUP
             * **************** */
            if ($isSpecial) {
                $special              = $setup['special'];
                $stuck5percent        = isset($special['stuck_5percent_pending_fix']) && $special['stuck_5percent_pending_fix'] == 1;
                $basicAuth            = isset($special['set_basic_auth']) && $special['set_basic_auth'] == 1;
                $removeInstallerFiles = isset($special['remove_installer_files']) && $special['remove_installer_files'] == 1;
                /**
                 * SPECIAL FIX: Package build stuck at 5% or Pending?
                 * */
                if ($stuck5percent) {
                    $data = array_merge($data, $this->special_quick_fix_stuck_5_percent());
                }

                /**
                 * SPECIAL FIX: Set basic auth username & password
                 * */
                if ($basicAuth) {
                    $data = array_merge($data, $this->special_quick_fix_basic_auth());
                }

                /**
                 * SPECIAL FIX: Remove installer files
                 * */
                if ($removeInstallerFiles) {
                    $data = array_merge($data, $this->special_quick_fix_remove_installer_files());
                }
            }

            // Save new property
            $find = count($data);
            if ($find > 0) {
                $system_global = DUP_PRO_System_Global_Entity::getInstance();
                if (strlen($id) > 0) {
                    $system_global->removeFixById($id);
                    $json['id'] = $id;
                }

                $json['success']           = true;
                $json['setup']             = $data;
                $json['fixed']             = $find;
                $json['recommended_fixes'] = count($system_global->recommended_fixes);
            }
        } catch (Exception $ex) {
            $json['message'] = $ex->getMessage();
            DUP_PRO_Log::trace("Error while implementing quick fix: " . $ex->getMessage());
        }

        die(SnapJson::jsonEncode($json));
    }

    /**
     * @return array $data
     * @throws Exception
     */
    private function special_quick_fix_remove_installer_files()
    {
        $data        = array();
        $fileRemoved = MigrationMng::cleanMigrationFiles();
        $removeError = false;
        if (count($fileRemoved) > 0) {
            $data['removed_installer_files'] = true;
        } else {
            throw new Exception(DUP_PRO_U::esc_html__("Unable to remove installer files."));
        }
        return $data;
    }

    /**
     * @return array $data
     * @throws Exception
     */
    private function special_quick_fix_stuck_5_percent()
    {
        $global = DUP_PRO_Global_Entity::getInstance();

        $data    = array();
        $kickoff = true;
        $custom  = false;

        if ($global->ajax_protocol === 'custom') {
            $custom = true;
        }

        // Do things if SSL is active
        if (DUP_PRO_U::is_ssl()) {
            if ($custom) {
                // Set default admin ajax
                $custom_ajax_url = admin_url('admin-ajax.php', 'https');
                if ($global->custom_ajax_url != $custom_ajax_url) {
                    $global->custom_ajax_url = $custom_ajax_url;
                    $data['custom_ajax_url'] = $global->custom_ajax_url;
                    $kickoff                 = false;
                }
            } else {
                // Set HTTPS protocol
                if ($global->ajax_protocol === 'http') {
                    $global->ajax_protocol = 'https';
                    $data['ajax_protocol'] = $global->ajax_protocol;
                    $kickoff               = false;
                }
            }
        } else {
            // SSL is OFF and we must handle that
            if ($custom) {
                // Set default admin ajax
                $custom_ajax_url = admin_url('admin-ajax.php', 'http');
                if ($global->custom_ajax_url != $custom_ajax_url) {
                    $global->custom_ajax_url = $custom_ajax_url;
                    $data['custom_ajax_url'] = $global->custom_ajax_url;
                    $kickoff                 = false;
                }
            } else {
                // Set HTTP protocol
                if ($global->ajax_protocol === 'https') {
                    $global->ajax_protocol = 'http';
                    $data['ajax_protocol'] = $global->ajax_protocol;
                    $kickoff               = false;
                }
            }
        }

        // Set KickOff true if all setups are gone
        if ($kickoff) {
            if ($global->clientside_kickoff !== true) {
                $global->clientside_kickoff = true;
                $data['clientside_kickoff'] = $global->clientside_kickoff;
            }
        }

        $global->save();
        return $data;
    }

    /**
     * @return array $data
     * @throws Exception
     */
    private function special_quick_fix_basic_auth()
    {
        $global   = DUP_PRO_Global_Entity::getInstance();
        $sglobal  = DUP_PRO_Secure_Global_Entity::getInstance();
        $username = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : false;
        $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : false;
        if ($username === false || $password === false) {
            throw new Exception(DUP_PRO_U::esc_html__("Username or password were not set."));
        }

        $data                       = array();
        $global->basic_auth_enabled = true;
        $data['basic_auth_enabled'] = true;

        $global->basic_auth_user = $username;
        $data['basic_auth_user'] = $username;

        $sglobal->basic_auth_password = $password;
        $data['basic_auth_password']  = "**Secure Info**";

        $global->save();
        $sglobal->save();

        return $data;
    }

    /**
     *  DUPLICATOR_PRO_BRAND_DELETE
     *  Deletes the files and database record entries
     *
     * @return void
     */
    public function duplicator_pro_brand_delete()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_brand_delete', 'nonce');

        $json      = array(
            'success' => false,
            'message' => '',
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'brand_ids' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_ARRAY,
                'options' => array(
                    'default' => false
                )
            )
        ));
        $brandIDs  = $inputData['brand_ids'];
        $delCount  = 0;

        if (empty($brandIDs) || in_array(false, $brandIDs)) {
            $isValid = false;
        }

        try {
            CapMng::can(CapMng::CAP_CREATE);
            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__('Invalid Request.'));
            }

            foreach ($brandIDs as $id) {
                $brand = DUP_PRO_Brand_Entity::deleteById($id);
                if ($brand) {
                    $delCount++;
                }
            }

            $json['success'] = true;
            $json['ids']     = $brandIDs;
            $json['removed'] = $delCount;
        } catch (Exception $e) {
            $json['message'] = $e->getMessage();
        }

        die(SnapJson::jsonEncode($json));
    }

    /**
     *  DUPLICATOR_PRO_PACKAGE_DELETE
     *  Deletes the files and database record entries
     *
     *  @return void
     */
    public function duplicator_pro_package_delete()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_package_delete', 'nonce');

        $json         = array(
            'error'   => '',
            'ids'     => '',
            'removed' => 0
        );
        $isValid      = true;
        $deletedCount = 0;

        $inputData     = filter_input_array(INPUT_POST, array(
            'package_ids' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_ARRAY,
                'options' => array(
                    'default' => false
                )
            )
        ));
        $packageIDList = $inputData['package_ids'];

        if (empty($packageIDList) || in_array(false, $packageIDList)) {
            $isValid = false;
        }
        //END OF VALIDATION

        try {
            CapMng::can(CapMng::CAP_CREATE);
            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid request."));
            }

            DUP_PRO_Log::traceObject("Starting deletion of packages by ids: ", $packageIDList);
            foreach ($packageIDList as $id) {
                if ($package = DUP_PRO_Package::get_by_id($id)) {
                    if ($package->delete()) {
                        $deletedCount++;
                    }
                } else {
                    $json['error'] = "Invalid package ID.";
                    break;
                }
            }
        } catch (Exception $ex) {
            $json['error'] = $ex->getMessage();
        }

        $json['ids']     = $packageIDList;
        $json['removed'] = $deletedCount;
        die(SnapJson::jsonEncode($json));
    }

    /**
     *  DUPLICATOR_PRO_RESET_USER_SETTINGS
     *  Resets user settings to default
     *
     *  @return void
     */
    public function duplicator_pro_reset_user_settings()
    {
        ob_start();
        try {
            DUP_PRO_Handler::init_error_handler();

            $error  = false;
            $result = array(
                'data'    => array(
                    'status' => null
                ),
                'html'    => '',
                'message' => ''
            );

            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'duplicator_pro_reset_user_settings')) {
                DUP_PRO_Log::trace('Security issue');
                throw new Exception('Security issue');
            }
            CapMng::can(CapMng::CAP_SETTINGS);

            /* @var $global DUP_PRO_Global_Entity */
            $global = DUP_PRO_Global_Entity::getInstance();

            $global->resetUserSettings();

            //Display gift flag on update
            //$global->dupHidePackagesGiftFeatures = false;

            $global->save();
            ExpireOptions::set(
                DUPLICATOR_PRO_SETTINGS_MESSAGE_TRANSIENT,
                DUP_PRO_U::__('Settings reset to defaults successfully'),
                DUPLICATOR_PRO_SETTINGS_MESSAGE_TIMEOUT
            );
        } catch (Exception $e) {
            $error             = true;
            $result['message'] = $e->getMessage();
        }

        $result['html'] = ob_get_clean();
        if ($error) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }

    public function duplicator_pro_reset_packages()
    {
        ob_start();
        try {
            DUP_PRO_Handler::init_error_handler();

            $error  = false;
            $result = array(
                'data'    => array(
                    'status' => null
                ),
                'html'    => '',
                'message' => ''
            );

            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'duplicator_pro_reset_packages')) {
                DUP_PRO_Log::trace('Security issue');
                throw new Exception('Security issue');
            }
            CapMng::can(CapMng::CAP_SETTINGS);

            // first last package id
            $ids = DUP_PRO_Package::get_ids_by_status(array(array('op' => '<', 'status' => DUP_PRO_PackageStatus::COMPLETE)), false, 0, '`id` DESC');
            foreach ($ids as $id) {
                // A smooth deletion is not performed because it is a forced reset.
                DUP_PRO_Package::force_delete($id);
            }
        } catch (Exception $e) {
            $error             = true;
            $result['message'] = $e->getMessage();
        }

        $result['html'] = ob_get_clean();
        if ($error) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }

    // DROPBOX METHODS
    public function duplicator_pro_get_storage_details()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_get_storage_details', 'nonce');

        $json      = array(
            'success' => false,
            'message' => '',
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'package_id' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
        ));

        $package_id = $inputData['package_id'];

        if (!$package_id) {
            $isValid = false;
        }
        //END OF VALIDATION

        try {
            CapMng::can(CapMng::CAP_STORAGE);

            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid Request."));
            }

            $package = DUP_PRO_Package::get_by_id($package_id);
            if ($package == null) {
                throw new Exception(sprintf(DUP_PRO_U::__('Unknown package %1$d'), $package_id));
            }

            $providers = array();
            foreach ($package->upload_infos as $upload_info) {
                $storage = DUP_PRO_Storage_Entity::get_by_id($upload_info->storage_id);

                if ($storage != null) {
                    $storage_location_string            = $storage->get_storage_location_string();
                    $storage                            = JsonSerialize::serializeToData($storage, JsonSerialize::JSON_SKIP_CLASS_NAME);
                    $storage["storage_location_string"] = $storage_location_string;
                    $storage["failed"]                  = $upload_info->failed;
                    $storage["cancelled"]               = $upload_info->cancelled;
                    // Newest storage upload infos will supercede earlier attempts to the same storage
                    $providers[$upload_info->storage_id] = $storage;
                }
            }

            $json['success']           = true;
            $json['message']           = DUP_PRO_U::__('Retrieved storage information');
            $json['logURL']            = $package->getLocalPackageFileURL(DUP_PRO_Package_File_Type::Log);
            $json['storage_providers'] = $providers;
        } catch (Exception $ex) {
            $json['success'] = false;
            $json['message'] = $ex->getMessage();
            DUP_PRO_Log::traceError($ex->getMessage());
        }

        die(SnapJson::jsonEncode($json));
    }

        // Returns status: {['success']={message} | ['error'] message}
    public function duplicator_pro_ftp_send_file_test()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_ftp_send_file_test', 'nonce');

        $json          = array(
            'success' => false,
            'message' => '',
            'status_msgs' => ''
        );
        $statusMsgsObj = new IncrementalStatusMessage();

        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'storage_id'     => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'storage_folder' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'server'         => array(
                'filter'  => FILTER_VALIDATE_DOMAIN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'port'           => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'username'       => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'password'       => array(
                'filter'  => FILTER_UNSAFE_RAW,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'ssl'            => array(
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'passive_mode'   => array(
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'use_curl'       => array(
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
        ));

        $storage_id     = $inputData['storage_id'];
        $storage_folder = $inputData['storage_folder'];
        $server         = $inputData['server'];
        $port           = $inputData['port'];
        $username       = $inputData['username'];
        $password       = $inputData['password'];
        $ssl            = $inputData['ssl'];
        $passive_mode   = $inputData['passive_mode'];
        $use_curl       = $inputData['use_curl'];

        $statusMsgsObj->addMessage(__('Checking FTP parameters', 'duplicator-pro'));
        if (!$storage_id) {
            $statusMsgsObj->addMessage(__('Error: storage_id is missing from request', 'duplicator-pro'));
        }
        if (!$storage_folder) {
            $statusMsgsObj->addMessage(__('Error: You haven\'t specified storage folder', 'duplicator-pro'));
        }
        if (!$server) {
            $statusMsgsObj->addMessage(__('Error: You haven\'t specified server', 'duplicator-pro'));
        }
        if (!$port) {
            $statusMsgsObj->addMessage(__('Error: You haven\'t specified port', 'duplicator-pro'));
        }
        if ($port < 1) {
            $statusMsgsObj->addMessage(__('Error: Port needs to be be a positive number', 'duplicator-pro'));
        }
        if (!$username) {
            $statusMsgsObj->addMessage(__('Error: You haven\'t specified username', 'duplicator-pro'));
        }

        if (!$storage_id || !$storage_folder || !$server || !$port || $port < 0 || !$username) {
            $isValid = false;
        }

        $source_handle   = null;
        $dest_handle     = null;
        $source_filepath = false;
        $dest_filepath   = false;
        try {
            CapMng::can(CapMng::CAP_STORAGE);

            if (!$isValid) {
                throw new Exception(__("Invalid request.", 'duplicator-pro'));
            }

            if ($storage_id != -1 && strlen($password) == 0) {
                // This is a hidden value, so we need to fetch it from database
                $storage = DUP_PRO_Storage_Entity::get_by_id($storage_id);
                if ($storage == null) {
                    throw new Exception(__("Couldn't find Storage ID $storage_id when performing the FTP file test", 'duplicator-pro'));
                }
                $password = $storage->ftp_password;
            }

            if (!$use_curl) {
                $ftp_connect_exists          = function_exists('ftp_connect');
                $ftp_connect_exists_filtered = apply_filters('duplicator_pro_ftp_connect_exists', $ftp_connect_exists);
                if (!$ftp_connect_exists_filtered) {
                    throw new Exception(sprintf(
                        DUP_PRO_U::esc_html__('FTP storage without use cURL requires FTP module to be enabled. Please install the FTP module as described in the %s.'),
                        '<a href="https://secure.php.net/manual/en/ftp.installation.php" target="_blank">https://secure.php.net/manual/en/ftp.installation.php</a> OR tick the "Use cURL checkbox."'
                    ));
                }
            } else {
                if (!function_exists("curl_init") || !function_exists("curl_exec") || !function_exists("curl_getinfo")) {
                    throw new Exception(
                        DUP_PRO_U::esc_html__('FTP storage with use cURL requires cURL extension to be enabled. That extension is not currently available on your system.')
                    );
                }
            }

            DUP_PRO_Log::trace("ssl=" . DUP_PRO_STR::boolToString($ssl));

            // -- Store the temp file
            $statusMsgsObj->addMessage(__('Creating temp file', 'duplicator-pro'));
            $source_filepath = tempnam(sys_get_temp_dir(), 'DUP');
            if ($source_filepath === false) {
                throw new Exception(__("Couldn't create the temp file for the FTP send test.", 'duplicator-pro'));
            }
            $statusMsgsObj->addMessage(sprintf(__('Created temp file "%1$s"', 'duplicator-pro'), $source_filepath));
            DUP_PRO_Log::trace("Created temp file $source_filepath");

            $statusMsgsObj->addMessage(__('Attempting to write to the temp file', 'duplicator-pro'));
            $source_handle = fopen($source_filepath, 'w');
            if (!$source_handle) {
                throw new Exception(__("Couldn't open temp file for writing.", 'duplicator-pro'));
            }
            $rnd = rand();
            fwrite($source_handle, "$rnd");

            $statusMsgsObj->addMessage(sprintf(__('Wrote %1$s to "%2$s"', 'duplicator-pro'), $rnd, $source_filepath));
            DUP_PRO_Log::trace("Wrote $rnd to $source_filepath");
            fclose($source_handle);
            $source_handle = null;

            // -- Send the file --
            $basename = basename($source_filepath);

            if ($use_curl) {
                $statusMsgsObj->addMessage(__('Attempting to open FTP connection with cURL', 'duplicator-pro'));
                /* @var $ftp_client DUP_PRO_FTPcURL */
                $ftp_client = new DUP_PRO_FTPcURL($server, $port, $username, $password, $storage_folder, 15, $ssl, $passive_mode);
            } else {
                $statusMsgsObj->addMessage(__('Attempting to open FTP connection', 'duplicator-pro'));
                /* @var $ftp_client DUP_PRO_FTP_Chunker */
                $ftp_client = new DUP_PRO_FTP_Chunker($server, $port, $username, $password, 15, $ssl, $passive_mode);
            }

            if ($use_curl) {
                $statusMsgsObj->addMessage(__('Attempting to test FTP connection', 'duplicator-pro'));
                $ftp_client->test_conn(); // Throws Exception in case of failure
            } else {
                if (!$ftp_client->open($statusMsgsObj)) {
                    throw new Exception(__('Error opening FTP connection.', 'duplicator-pro'));
                }
            }
            $statusMsgsObj->addMessage(__('FTP connection is successfully established', 'duplicator-pro'));

            if (DUP_PRO_STR::startsWith($storage_folder, '/') == false) {
                $storage_folder = '/' . $storage_folder;
            }
            $storage_folder = trailingslashit($storage_folder);

            $statusMsgsObj->addMessage(sprintf(__('Checking if remote storage directory exists: "%1$s"', 'duplicator-pro'), $storage_folder));

            if ($ftp_client->directory_exists($storage_folder)) {
                $statusMsgsObj->addMessage(__('The remote storage directory already exists', 'duplicator-pro'));
            } else {
                $statusMsgsObj->addMessage(__('The remote storage directory does not exist yet', 'duplicator-pro'));
                $statusMsgsObj->addMessage(sprintf(__('Attempting to create the remote storage directory "%1$s"', 'duplicator-pro'), $storage_folder));
                $ftp_directory_exists = $ftp_client->create_directory($storage_folder);
                if (!$ftp_directory_exists) {
                    if ($use_curl) {
                        throw new Exception(__("The FTP connection is working fine but the directory can't be created.", 'duplicator-pro'));
                    } else {
                        throw new Exception(__("The FTP connection is working fine but the directory can't be created. Check the \"cURL\" checkbox and retry.", 'duplicator-pro'));
                    }
                } else {
                    $statusMsgsObj->addMessage(__('The remote storage directory is created successfully', 'duplicator-pro'));
                }
            }

            $statusMsgsObj->addMessage(__('Attempting to upload temp file to remote directory', 'duplicator-pro'));
            if ($use_curl) {
                $ret_upload = $ftp_client->upload_file($source_filepath, basename($source_filepath));
            } else {
                $ret_upload = $ftp_client->upload_file($source_filepath, $storage_folder);
            }
            if (!$ret_upload) {
                throw new Exception(__('Error uploading file.', 'duplicator-pro'));
            }
            $statusMsgsObj->addMessage(__('The temp file was uploaded successfully', 'duplicator-pro'));

            // -- Download the file --
            $statusMsgsObj->addMessage(__('Creating destination temp file for the FTP send test', 'duplicator-pro'));
            $dest_filepath = wp_tempnam('DUP', DUPLICATOR_PRO_SSDIR_PATH_TMP);

            $statusMsgsObj->addMessage(sprintf(__('Created temp file "%1$s"', 'duplicator-pro'), $dest_filepath));

            $remote_source_filepath = $use_curl ? $basename : "$storage_folder/$basename";
            $statusMsgsObj->addMessage(sprintf(__('About to FTP download "%1$s" to "%2$s"', 'duplicator-pro'), $remote_source_filepath, $dest_filepath));
            DUP_PRO_Log::trace("About to FTP download $remote_source_filepath to $dest_filepath");

            if (!$ftp_client->download_file($remote_source_filepath, $dest_filepath, false)) {
                throw new Exception(__('Error downloading file.', 'duplicator-pro'));
            }
            $statusMsgsObj->addMessage(__('The file is successfully downloaded', 'duplicator-pro'));

            $statusMsgsObj->addMessage(__('Attempting to delete the remote file', 'duplicator-pro'));
            $deleted_temp_file = true;

            if ($ftp_client->delete($remote_source_filepath) == false) {
                $statusMsgsObj->addMessage(__('Couldn\'t delete the remote test file', 'duplicator-pro'));
                DUP_PRO_Log::traceError("Couldn't delete the remote test file.");
                $deleted_temp_file = false;
            } else {
                $statusMsgsObj->addMessage(__('Successfully deleted the remote file', 'duplicator-pro'));
            }

            $statusMsgsObj->addMessage(sprintf(__('Attempting to read downloaded file "%1$s"', 'duplicator-pro'), $dest_filepath));
            $dest_handle = fopen($dest_filepath, 'r');
            if (!$dest_handle) {
                throw new Exception(__('Could not open file for reading.', 'duplicator-pro'));
            }
            $dest_string = fread($dest_handle, 100);
            fclose($dest_handle);
            $dest_handle = null;

            $statusMsgsObj->addMessage(__('Looking for missmatch in files', 'duplicator-pro'));
            /* The values better match or there was a problem */
            if ($rnd != (int) $dest_string) {
                $statusMsgsObj->addMessage(sprintf(__('Mismatch in files: %1$s != %2$d', 'duplicator-pro'), $rnd, $dest_string));
                DUP_PRO_Log::traceError("Mismatch in files: $rnd != $dest_string");
                throw new Exception(__('There was a problem storing or retrieving the temporary file on this account.', 'duplicator-pro'));
            }

            $statusMsgsObj->addMessage(__('Files match!', 'duplicator-pro'));
            DUP_PRO_Log::trace("Files match!");
            if ($deleted_temp_file) {
                if ($use_curl) {
                    $json['success'] = true;
                    $json['message'] = __('Successfully stored and retrieved file.', 'duplicator-pro');
                } else {
                    $raw = ftp_raw($ftp_client->ftp_connection_id, 'REST');
                    if (is_array($raw) && !empty($raw) && isset($raw[0])) {
                        $code = intval($raw[0]);
                        if (502 === $code) {
                            throw new Exception(__("FTP server doesn't support REST command. It will cause problem in PHP native function chunk upload. Please proceed with ticking \"Use Curl\" checkbox. Error: ", 'duplicator-pro') . $raw[0]);
                        } else {
                            $json['success'] = true;
                            $json['message'] = __('Successfully stored and retrieved file.', 'duplicator-pro');
                        }
                    } else {
                        $json['success'] = true;
                        $json['message'] = __('Successfully stored and retrieved file.', 'duplicator-pro');
                    }
                }
            } else {
                $json['success'] = true;
                $json['message'] = __("Successfully stored and retrieved file however couldn't delete the temp file on the server.", 'duplicator-pro');
            }
        } catch (Exception $e) {
            if ($source_handle != null) {
                fclose($source_handle);
            }

            if ($dest_handle != null) {
                fclose($dest_handle);
            }

            $errorMessage = $e->getMessage();
            $statusMsgsObj->addMessage($errorMessage);
            DUP_PRO_Log::trace($errorMessage);
            $json['message'] = "{$errorMessage} " . __('For additional help see the online '
                    . '<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-400-q" target="_blank">FTP troubleshooting steps</a>.', 'duplicator-pro');
            ;
        }

        if (file_exists($source_filepath)) {
            $statusMsgsObj->addMessage(sprintf(__('Attempting to delete local temp file "%1$s"', 'duplicator-pro'), $source_filepath));
            if (unlink($source_filepath) == false) {
                $statusMsgsObj->addMessage(sprintf(__('Could not delete the temp file "%1$s"', 'duplicator-pro'), $source_filepath));
                DUP_PRO_Log::trace("Could not delete the temp file $source_filepath");
            } else {
                $statusMsgsObj->addMessage(sprintf(__('Deleted temp file "%1$s"', 'duplicator-pro'), $source_filepath));
                DUP_PRO_Log::trace("Deleted temp file $source_filepath");
            }
        }

        if (file_exists($dest_filepath)) {
            $statusMsgsObj->addMessage(sprintf(__('Attempting to delete local temp file "%1$s"', 'duplicator-pro'), $dest_filepath));
            if (unlink($dest_filepath) == false) {
                $statusMsgsObj->addMessage(sprintf(__('Could not delete the temp file "%1$s"', 'duplicator-pro'), $dest_filepath));
                DUP_PRO_Log::trace("Could not delete the temp file $dest_filepath");
            } else {
                $statusMsgsObj->addMessage(sprintf(__('Deleted temp file "%1$s"', 'duplicator-pro'), $dest_filepath));
                DUP_PRO_Log::trace("Deleted temp file $dest_filepath");
            }
        }

        $json['status_msgs'] = strval($statusMsgsObj);
        die(SnapJson::jsonEncode($json));
    }

    public function duplicator_pro_sftp_send_file_test()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_sftp_send_file_test', 'nonce');

        $json          = array(
            'success' => false,
            'message' => '',
            'status_msgs' => ''
        );
        $statusMsgsObj = new IncrementalStatusMessage();

        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'storage_id'     => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'storage_folder'       => array(
                'filter'  => FILTER_CALLBACK,
                'options' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim')
            ),
            'server'               => array(
                'filter'  => FILTER_VALIDATE_DOMAIN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'port'                 => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'username'             => array(
                'filter'  => FILTER_CALLBACK,
                'options' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim')
            ),
            'password'             => array(
                'filter'  => FILTER_CALLBACK,
                'options' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline')
            ),
            'private_key'          => array(
                'filter'  => FILTER_CALLBACK,
                'options' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSChars')
            ),
            'private_key_password' => array(
                'filter'  => FILTER_CALLBACK,
                'options' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline')
            ),
        ));

        $storage_id           = $inputData['storage_id'];
        $storage_folder       = $inputData['storage_folder'];
        $server               = $inputData['server'];
        $port                 = $inputData['port'];
        $username             = $inputData['username'];
        $password             = $inputData['password'];
        $private_key          = $inputData['private_key'];
        $private_key_password = $inputData['private_key_password'];

        $statusMsgsObj->addMessage(__('Checking SFTP parameters', 'duplicator-pro'));
        if (!$storage_id) {
            $statusMsgsObj->addMessage(__('Error: storage_id is missing from request', 'duplicator-pro'));
        }
        if (!$storage_folder) {
            $statusMsgsObj->addMessage(__('Error: You must specify storage folder', 'duplicator-pro'));
        }
        if (!$server) {
            $statusMsgsObj->addMessage(__('Error: You must specify server', 'duplicator-pro'));
        }
        if (!$port) {
            $statusMsgsObj->addMessage(__('Error: You must specify port', 'duplicator-pro'));
        }
        if ($port < 1) {
            $statusMsgsObj->addMessage(__('Error: Port needs to be a positive number', 'duplicator-pro'));
        }
        if (!$username) {
            $statusMsgsObj->addMessage(__('Error: You must specify username', 'duplicator-pro'));
        }

        if (!$storage_id || !$storage_folder || !$server || !$port || $port < 0 || !$username) {
            $isValid = false;
        }

        $source_filepath = false;
        try {
            CapMng::can(CapMng::CAP_STORAGE);

            if (!$isValid) {
                throw new Exception(__("Invalid request.", 'duplicator-pro'));
            }

            if (
                $storage_id != -1 &&
                (
                    strlen($password) == 0 ||
                    strlen($private_key_password) == 0
                )
            ) {
                // There are some hidden values, so we need to fetch them from database
                $storage = DUP_PRO_Storage_Entity::get_by_id($storage_id);
                if ($storage == null) {
                    throw new Exception(__("Couldn't find Storage ID $storage_id when performing the SFTP file test", 'duplicator-pro'));
                }
                if (strlen($password) == 0) {
                    $password = $storage->sftp_password;
                }
                if (strlen($private_key_password) == 0) {
                    $private_key_password = $storage->sftp_private_key_password;
                }
            }

            // -- Store the temp file --
            $statusMsgsObj->addMessage(__('Attempting to create a temp file', 'duplicator-pro'));
            $source_filepath = tempnam(sys_get_temp_dir(), 'DUP');

            if ($source_filepath === false) {
                throw new Exception(__("Couldn't create the temp file for the SFTP send test", 'duplicator-pro'));
            }

            $basename = basename($source_filepath);
            $statusMsgsObj->addMessage(sprintf(__('Created a temp file "%1$s"', 'duplicator-pro'), $source_filepath));
            DUP_PRO_Log::trace("Created a temp file $source_filepath");

            if (DUP_PRO_STR::startsWith($storage_folder, '/') == false) {
                $storage_folder = '/' . $storage_folder;
            }

            if (DUP_PRO_STR::endsWith($storage_folder, '/') == false) {
                $storage_folder = $storage_folder . '/';
            }

            $sFtpAdapter = new SFTPAdapter($server, $port, $username, $password, $private_key, $private_key_password);
            $sFtpAdapter->setMessages($statusMsgsObj);

            if (!$sFtpAdapter->connect()) {
                throw new Exception(__("Couldn't connect to sftp server while doing the SFTP send test", 'duplicator-pro'));
            }

            $statusMsgsObj->addMessage(sprintf(__('Checking if remote storage folder "%1$s" already exists', 'duplicator-pro'), $storage_folder));
            if (!$sFtpAdapter->fileExists($storage_folder)) {
                $statusMsgsObj->addMessage(sprintf(__('The remote storage folder "%1$s" does not exist, attempting to create it', 'duplicator-pro'), $storage_folder));
                $storage_folder = $sFtpAdapter->mkDirRecursive($storage_folder);
                if (!$sFtpAdapter->fileExists($storage_folder)) {
                    throw new Exception(__("The SFTP connection is working fine, but the directory can't be created.", 'duplicator-pro'));
                } else {
                    $statusMsgsObj->addMessage(__('The remote storage folder is created successfully', 'duplicator-pro'));
                }
            } else {
                $statusMsgsObj->addMessage(__('The remote storage folder already exists', 'duplicator-pro'));
            }

            // Try to upload a test file
            $statusMsgsObj->addMessage(__('Attempting to upload the test file', 'duplicator-pro'));
            $continueUpload = true;
            try {
                if (!$sFtpAdapter->put($storage_folder . $basename, $source_filepath)) {
                    $continueUpload = false;
                    $statusMsgsObj->addMessage(
                        __(
                            'Error uploading test file, maybe the directory does not exist or you have no write permissions',
                            'duplicator-pro'
                        )
                    );
                    DUP_PRO_Log::trace("Error uploading test file, maybe the directory does not exist or you have no write permissions.");
                    $json['message'] = __('Error uploading test file.', 'duplicator-pro');
                }
            } catch (ChunkingTimeoutException $e) {
                $continueUpload = true;
            }
            if ($continueUpload) {
                $statusMsgsObj->addMessage(__('Test file uploaded successfully', 'duplicator-pro'));
                DUP_PRO_Log::trace("Test file uploaded successfully.");
                $json['success'] = true;
                $json['message'] = __('The connection was successful.', 'duplicator-pro');
                $statusMsgsObj->addMessage(__('Attempting to delete the remote test file', 'duplicator-pro'));
                if ($sFtpAdapter->delete($storage_folder . $basename)) {
                    $statusMsgsObj->addMessage(__('Remote test file deleted successfully', 'duplicator-pro'));
                    DUP_PRO_Log::trace("Remote test file deleted successfully.");
                } else {
                    $statusMsgsObj->addMessage(__('Couldn\'t delete the remote test file', 'duplicator-pro'));
                    DUP_PRO_Log::trace("Couldn't delete the remote test file.");
                }
            }
        } catch (Exception $e) {
            $statusMsgsObj->addMessage($e->getMessage());
            DUP_PRO_Log::trace($e->getMessage());
            $json['message'] = $e->getMessage();
        }

        if (file_exists($source_filepath)) {
            $statusMsgsObj->addMessage(sprintf(__('Attempting to delete local temp file "%1$s"', 'duplicator-pro'), $source_filepath));
            if (unlink($source_filepath) == false) {
                $statusMsgsObj->addMessage(sprintf(__('Could not delete the temp file "%1$s"', 'duplicator-pro'), $source_filepath));
                DUP_PRO_Log::trace("Could not delete the temp file $source_filepath");
            } else {
                $statusMsgsObj->addMessage(sprintf(__('Deleted temp file "%1$s"', 'duplicator-pro'), $source_filepath));
                DUP_PRO_Log::trace("Deleted temp file $source_filepath");
            }
        }

        $json['status_msgs'] = strval($statusMsgsObj);
        die(SnapJson::jsonEncode($json));
    }

    public function duplicator_pro_gdrive_send_file_test()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_gdrive_send_file_test', 'nonce');

        $json      = array(
            'success' => false,
            'message' => ''
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'storage_id'     => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'storage_folder' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'full_access'    => array(
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));

        $storage_id     = $inputData['storage_id'];
        $storage_folder = $inputData['storage_folder'];

        if (!$storage_id) {
            $isValid = false;
        }
        //END OF VALIDATION

        $source_handle   = null;
        $dest_handle     = null;
        $source_filepath = '';
        $dest_filepath   = '';
        try {
            CapMng::can(CapMng::CAP_STORAGE);

            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid request."));
            }

            $storage = DUP_PRO_Storage_Entity::get_by_id($storage_id);
            if ($storage == null) {
                throw new Exception(DUP_PRO_U::__("Couldn't find Storage ID $storage_id when performing Google Drive file test"));
            }

            $source_filepath = wp_tempnam('DUP', DUPLICATOR_PRO_SSDIR_PATH_TMP);
            DUP_PRO_Log::trace("Created temp file $source_filepath");

            $source_handle = fopen($source_filepath, 'w');
            $rnd           = rand();
            fwrite($source_handle, "$rnd");
            DUP_PRO_Log::trace("Wrote $rnd to $source_filepath");
            fclose($source_handle);
            $source_handle = null;

            // -- Send the file --
            $basename        = basename($source_filepath);
            $gdrive_filepath = trailingslashit($storage_folder) . $basename;

            /* @var $google_client Duplicator_Pro_Google_Client */
            $google_client = $storage->get_full_google_client();
            if ($google_client == null) {
                throw new Exception(DUP_PRO_U::__("Couldn't get Google client when performing Google Drive file test"));
            }

            DUP_PRO_Log::trace("About to send $source_filepath to $gdrive_filepath on Google Drive");

            $google_service_drive = new Duplicator_Pro_Google_Service_Drive($google_client);

            $directory_id = DUP_PRO_GDrive_U::get_directory_id($google_service_drive, $storage_folder);
            if ($directory_id == null) {
                throw new Exception(DUP_PRO_U::__("Couldn't get directory ID for folder {$storage_folder} when performing Google Drive file test"));
            }

            $google_file = DUP_PRO_GDrive_U::upload_file($google_client, $source_filepath, $directory_id);
            if ($google_file == null) {
                throw new Exception(DUP_PRO_U::__("Couldn't upload file to Google Drive."));
            }

            // -- Download the file --
            $dest_filepath = wp_tempnam('GDRIVE_TMP', DUPLICATOR_PRO_SSDIR_PATH_TMP);

            if (file_exists($dest_filepath)) {
                @unlink($dest_filepath);
            }

            DUP_PRO_Log::trace("About to download $gdrive_filepath on Google Drive to $dest_filepath");

            if (DUP_PRO_GDrive_U::download_file($google_client, $google_file, $dest_filepath)) {
                try {
                    $google_service_drive = new Duplicator_Pro_Google_Service_Drive($google_client);
                    $google_service_drive->files->delete($google_file->id);
                } catch (Exception $ex) {
                    DUP_PRO_Log::trace("Error deleting temporary file generated on Google File test");
                }

                /** @todo add rturn chcks for all IO functions */
                $dest_handle = fopen($dest_filepath, 'r');
                $dest_string = fread($dest_handle, 100);
                fclose($dest_handle);
                $dest_handle = null;

                /* The values better match or there was a problem */
                if ($rnd == (int) $dest_string) {
                    DUP_PRO_Log::trace("Files match! $rnd $dest_string");
                    $json['success'] = true;
                    $json['message'] = DUP_PRO_U::esc_html__('Successfully stored and retrieved file');
                } else {
                    DUP_PRO_Log::traceError("mismatch in files $rnd != $dest_string");
                    $json['message'] = DUP_PRO_U::esc_html__('There was a problem storing or retrieving the temporary file on this account.');
                }
            } else {
                DUP_PRO_Log::traceError("Couldn't download $source_filepath after it had been uploaded");
            }
        } catch (Exception $e) {
            $errorMessage = esc_html($e->getMessage());

            DUP_PRO_Log::trace($errorMessage);
            $json['message'] = $errorMessage;
        }

        if (file_exists($source_filepath)) {
            unlink($source_filepath);
            DUP_PRO_Log::trace("Deleted temp file $source_filepath");
        }

        if (file_exists($dest_filepath)) {
            unlink($dest_filepath);
            DUP_PRO_Log::trace("Deleted temp file $dest_filepath");
        }

        die(SnapJson::jsonEncode($json));
    }

    public function duplicator_pro_s3_send_file_test()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_s3_send_file_test', 'nonce');

        $json          = array(
            'success' => false,
            'message' => '',
            'status_msgs' => ''
        );
        $statusMsgsObj = new IncrementalStatusMessage();

        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'storage_id'     => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'storage_folder' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'bucket'         => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'storage_class'  => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'region'         => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'access_key'     => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'secret_key'     => array(
                'filter'  => FILTER_UNSAFE_RAW,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            // It may be like "s3.us-west-1.wasabisys.com" for wasabi
            'endpoint'       => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'ACL_full_control' => array(
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));

        $storage_id       = $inputData['storage_id'];
        $storage_folder   = $inputData['storage_folder'];
        $bucket           = $inputData['bucket'];
        $storage_class    = $inputData['storage_class'];
        $region           = $inputData['region'];
        $access_key       = $inputData['access_key'];
        $secret_key       = $inputData['secret_key'];
        $endpoint         = $inputData['endpoint'];
        $ACL_full_control = $inputData['ACL_full_control'];

        $statusMsgsObj->addMessage(__('Checking S3 parameters', 'duplicator-pro'));
        if (!$storage_id) {
            $statusMsgsObj->addMessage(__('Error: storage_id is missing from request', 'duplicator-pro'));
        }
        if (!$storage_folder) {
            $statusMsgsObj->addMessage(__('Error: You must specify storage folder', 'duplicator-pro'));
        }
        if (!$bucket) {
            $statusMsgsObj->addMessage(__('Error: You must specify bucket', 'duplicator-pro'));
        }
        if (!$storage_class) {
            $statusMsgsObj->addMessage(__('Error: You must specify storage class', 'duplicator-pro'));
        }
        if (!$region) {
            $statusMsgsObj->addMessage(__('Error: You must specify region', 'duplicator-pro'));
        }
        if (!$access_key) {
            $statusMsgsObj->addMessage(__('Error: You must specify access_key', 'duplicator-pro'));
        }

        if (!$storage_id || !$storage_folder || !$bucket || !$storage_class || !$region || !$access_key) {
            $isValid = false;
        }

        $source_handle   = null;
        $source_filepath = '';
        try {
            CapMng::can(CapMng::CAP_STORAGE);

            if (!$isValid) {
                throw new Exception(__("Invalid request.", 'duplicator-pro'));
            }

            if ($storage_id != -1 && strlen($secret_key) == 0) {
                // This is a hidden value, so we need to fetch it from database
                $storage = DUP_PRO_Storage_Entity::get_by_id($storage_id);
                if ($storage == null) {
                    throw new Exception(__("Couldn't find Storage ID $storage_id when performing the S3 file test", 'duplicator-pro'));
                }
                $secret_key = $storage->s3_secret_key;
            }

            if (!DUP_PRO_U::isCurlExists()) {
                throw new Exception(__("Amazon S3 (or Compatible) requires PHP cURL extension to be activated.", 'duplicator-pro'));
            }

            $storage_folder = rtrim($storage_folder, '/');
            $statusMsgsObj->addMessage(__('Attempting to create a temp file', 'duplicator-pro'));
            $source_filepath = tempnam(sys_get_temp_dir(), 'DUP');

            if ($source_filepath === false) {
                throw new Exception(__("Couldn't create the temp file for the S3 send test", 'duplicator-pro'));
            }
            $statusMsgsObj->addMessage(sprintf(__('Created a temp file "%1$s"', 'duplicator-pro'), $source_filepath));
            DUP_PRO_Log::trace("Created a temp file $source_filepath");

            $statusMsgsObj->addMessage(__('Attempting to write to the temp file', 'duplicator-pro'));
            $source_handle = fopen($source_filepath, 'w');
            if (!$source_handle) {
                throw new Exception(__("Couldn't open temp file for writing.", 'duplicator-pro'));
            }
            $rnd = rand();
            fwrite($source_handle, "$rnd");

            $statusMsgsObj->addMessage(sprintf(__('Wrote %1$s to "%2$s"', 'duplicator-pro'), $rnd, $source_filepath));
            DUP_PRO_Log::trace("Wrote $rnd to $source_filepath");
            fclose($source_handle);
            $source_handle = null;

            // -- Send the file --
            $filename = basename($source_filepath);

            $statusMsgsObj->addMessage(__('Attempting to get S3 client object', 'duplicator-pro'));
            $s3_client = DUP_PRO_S3_U::get_s3_client($region, $access_key, $secret_key, $endpoint);
            if (!$s3_client) {
                throw new Exception(__("Couldn't get the S3 client for the S3 send test", 'duplicator-pro'));
            }
            $statusMsgsObj->addMessage(__('Got S3 client object', 'duplicator-pro'));

            $statusMsgsObj->addMessage(sprintf(__('About to send "%1$s" to "%2$s" in bucket %3$s on S3', 'duplicator-pro'), $source_filepath, $storage_folder, $bucket));
            DUP_PRO_Log::trace("About to send $source_filepath to $storage_folder in bucket $bucket on S3");

            if (DUP_PRO_S3_U::upload_file($s3_client, $bucket, $source_filepath, $storage_folder, $storage_class, $ACL_full_control, '', $statusMsgsObj)) {
                $statusMsgsObj->addMessage(__('Successfully stored test file to remote storage', 'duplicator-pro'));
                $remote_filepath = "$storage_folder/$filename";
                $statusMsgsObj->addMessage(sprintf(__('Attempting to delete temporary file on S3: "%1$s"', 'duplicator-pro'), $remote_filepath));
                if (DUP_PRO_S3_U::delete_file($s3_client, $bucket, $remote_filepath, $statusMsgsObj) == false) {
                    $statusMsgsObj->addMessage(__('Error deleting temporary file on S3', 'duplicator-pro'));
                    DUP_PRO_Log::trace("Error deleting temporary file generated on S3 File test - {$remote_filepath}");
                    $json['message'] = __('Test failed. Double check configuration and read status messages above, as they could help you identify the problem.', 'duplicator-pro');
                } else {
                    $statusMsgsObj->addMessage(__('Successfully deleted temporary file on S3', 'duplicator-pro'));
                    $json['success'] = true;
                    $json['message'] = __('Successfully stored and retrieved test file', 'duplicator-pro');
                }
            } else {
                $statusMsgsObj->addMessage(__('Upload of test file failed. Check configuration.', 'duplicator-pro'));
                $json['message'] = __('Test failed. Double check configuration and read status messages above, as they could help you identify the problem.', 'duplicator-pro');
            }
        } catch (Exception $e) {
            if ($source_handle != null) {
                fclose($source_handle);
            }

            $errorMessage = esc_html($e->getMessage());
            $statusMsgsObj->addMessage($errorMessage);
            DUP_PRO_Log::trace($errorMessage);
            $json['message'] = $errorMessage;
        }

        if (file_exists($source_filepath)) {
            $statusMsgsObj->addMessage(sprintf(__('Attempting to delete local temp file "%1$s"', 'duplicator-pro'), $source_filepath));
            if (unlink($source_filepath) == false) {
                $statusMsgsObj->addMessage(sprintf(__('Could not delete the temp file "%1$s"', 'duplicator-pro'), $source_filepath));
                DUP_PRO_Log::trace("Could not delete the temp file $source_filepath");
            } else {
                $statusMsgsObj->addMessage(sprintf(__('Deleted temp file "%1$s"', 'duplicator-pro'), $source_filepath));
                DUP_PRO_Log::trace("Deleted temp file $source_filepath");
            }
        }

        $json['status_msgs'] = strval($statusMsgsObj);
        die(SnapJson::jsonEncode($json));
    }

    public function duplicator_pro_dropbox_send_file_test()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_dropbox_send_file_test', 'nonce');

        $json      = array(
            'success' => false,
            'message' => ''
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'storage_id'     => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'storage_folder' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'full_access'    => array(
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));

        $storage_id     = $inputData['storage_id'];
        $storage_folder = $inputData['storage_folder'];
        $full_access    = $inputData['full_access'] == 'true';

        if (!$storage_id || !$storage_folder) {
            $isValid = false;
        }
        //END OF VALIDATION

        $source_handle   = null;
        $source_filepath = null;

        try {
            CapMng::can(CapMng::CAP_STORAGE);

            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid request."));
            }

            $source_filepath = tempnam(sys_get_temp_dir(), 'DUP');
            if ($source_filepath === false) {
                throw new Exception(DUP_PRO_U::__("Couldn't create the temp file for the Dropbox send test"));
            }
            DUP_PRO_Log::trace("Created temp file $source_filepath");

            $source_handle = fopen($source_filepath, 'w');
            $rnd           = rand();
            fwrite($source_handle, "$rnd");
            DUP_PRO_Log::trace("Wrote $rnd to $source_filepath");
            fclose($source_handle);
            $source_handle = null;

            // -- Send the file --
            $basename         = basename($source_filepath);
            $dropbox_filepath = trim($storage_folder, '/') . "/$basename";

            /* @var $storage DUP_PRO_Storage_Entity */
            $storage = DUP_PRO_Storage_Entity::get_by_id($storage_id);
            if ($storage == null) {
                throw new Exception(DUP_PRO_U::__("Couldn't find Storage ID $storage_id when performing the DropBox file test"));
            }

            /* @var $dropbox DUP_PRO_DropboxV2Client */
            $dropbox = $storage->get_dropbox_client($full_access);
            if ($dropbox == null) {
                throw new Exception(DUP_PRO_U::__("Couldn't get the DropBox client when performing the DropBox file test"));
            }

            DUP_PRO_Log::trace("About to send $source_filepath to $dropbox_filepath in dropbox");
            $upload_result = $dropbox->UploadFile($source_filepath, $dropbox_filepath);

            $dropbox->Delete($dropbox_filepath);

            /* The values better match or there was a problem */
            if ($dropbox->checkFileHash($upload_result, $source_filepath)) {
                DUP_PRO_Log::trace("Files match!");
                $json['success'] = true;
                $json['message'] = DUP_PRO_U::__('Successfully stored and retrieved file');
            } else {
                DUP_PRO_Log::traceError("mismatch in files");
                $json['message'] = DUP_PRO_U::__('There was a problem storing or retrieving the temporary file on this account.');
            }
        } catch (Exception $ex) {
            DUP_PRO_Log::trace($ex->getMessage());
            $json['message'] = $ex->getMessage();
        }

        if (file_exists($source_filepath)) {
            DUP_PRO_Log::trace("Removing temp file $source_filepath");
            unlink($source_filepath);
        }

        die(SnapJson::jsonEncode($json));
    }

    public function get_trace_log()
    {
        /**
         * don't init DUP_PRO_Handler::init_error_handler() in get trace
         */
        check_ajax_referer('duplicator_pro_get_trace_log', 'nonce');
        DUP_PRO_Log::trace("enter");

        $file_path   = DUP_PRO_Log::getTraceFilepath();
        $backup_path = DUP_PRO_Log::getBackupTraceFilepath();
        $zip_path    = DUPLICATOR_PRO_SSDIR_PATH . "/" . DUP_PRO_Constants::ZIPPED_LOG_FILENAME;

        try {
            CapMng::can(CapMng::CAP_CREATE);

            if (file_exists($zip_path)) {
                SnapIO::unlink($zip_path);
            }
            $zipArchive = new ZipArchiveExtended($zip_path);

            if ($zipArchive->open() == false) {
                throw new Exception('Can\'t open ZIP archive');
            }

            if ($zipArchive->addFile($file_path, basename($file_path)) == false) {
                throw new Exception('Can\'t add ZIP file ');
            }

            if (file_exists($backup_path) && $zipArchive->addFile($backup_path, basename($backup_path)) == false) {
                throw new Exception('Can\'t add ZIP file ');
            }

            $zipArchive->close();

            if (($fp = fopen($zip_path, 'rb')) === false) {
                throw new Exception('Can\'t open ZIP archive');
            }

            $zip_filename = basename($zip_path);

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header("Content-Transfer-Encoding: binary");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"$zip_filename\";");

            // required or large files wont work
            if (ob_get_length()) {
                ob_end_clean();
            }

            DUP_PRO_Log::trace("streaming $zip_path");
            fpassthru($fp);
            fclose($fp);
            @unlink($zip_path);
        } catch (Exception $e) {
            header("Content-Type: text/plain");
            header("Content-Disposition: attachment; filename=\"error.txt\";");
            $message = 'Create Log Zip error message: ' . $e->getMessage();
            DUP_PRO_Log::trace($message);
            echo esc_html($message);
        }
        exit;
    }

    public function delete_trace_log()
    {
        /**
         * don't init DUP_PRO_Handler::init_error_handler() in get trace
         */
        check_ajax_referer('duplicator_pro_delete_trace_log', 'nonce');
        CapMng::can(CapMng::CAP_CREATE);

        $res = DUP_PRO_Log::deleteTraceLog();
        if ($res) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    public function export_settings()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_import_export_settings', 'nonce');

        DUP_PRO_Log::trace("enter");
        $request = stripslashes_deep($_REQUEST);

        try {
            CapMng::can(CapMng::CAP_SETTINGS);

            $settings_u = new DUP_PRO_Settings_U();
            $settings_u->runExport();

            DUP_PRO_U::getDownloadAttachment($settings_u->export_filepath, 'application/octet-stream');
        } catch (Exception $ex) {
            // RSR TODO: set the error message to this $this->message = 'Error processing with export:' .  $e->getMessage();
            header("Content-Type: text/plain");
            header("Content-Disposition: attachment; filename=\"error.txt\";");
            $message = DUP_PRO_U::__("{$ex->getMessage()}");
            DUP_PRO_Log::trace($message);
            echo esc_html($message);
        }
        exit;
    }

    // Stop a package build
    // Input: package_id
    // Output:
    //          succeeded: true|false
    //          retval: null or error message
    public function package_stop_build()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_package_stop_build', 'nonce');

        CapMng::can(CapMng::CAP_CREATE);

        $json       = array(
            'success' => false,
            'message' => ''
        );
        $isValid    = true;
        $inputData  = filter_input_array(INPUT_POST, array(
            'package_id' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));
        $package_id = $inputData['package_id'];

        if (!$package_id) {
            $isValid = false;
        }

        try {
            if (!$isValid) {
                throw new Exception('Invalid request.');
            }

            DUP_PRO_Log::trace("Web service stop build of $package_id");
            $package = DUP_PRO_Package::get_by_id($package_id);

            if ($package == null) {
                DUP_PRO_Log::trace("could not find package so attempting hard delete. Old files may end up sticking around although chances are there isnt much if we couldnt nicely cancel it.");
                $result = DUP_PRO_Package::force_delete($package_id);

                if ($result) {
                    $json['message'] = 'Hard delete success';
                    $json['success'] = true;
                } else {
                    throw new Exception('Hard delete failure');
                }
            } else {
                DUP_PRO_Log::trace("set $package->ID for cancel");
                $package->set_for_cancel();
                $json['success'] = true;
            }
        } catch (Exception $ex) {
            DUP_PRO_Log::trace($ex->getMessage());
            $json['message'] = $ex->getMessage();
        }

        die(SnapJson::jsonEncode($json));
    }

    // Retrieve view model for the Packages/Details/Transfer screen
    // active_package_id: true/false
    // percent_text: Percent through the current transfer
    // text: Text to display
    // transfer_logs: array of transfer request vms (start, stop, status, message)
    public function packages_details_transfer_get_package_vm()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_packages_details_transfer_get_package_vm', 'nonce');

        $json      = array(
            'success' => false,
            'message' => '',
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'package_id' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
        ));

        $package_id = $inputData['package_id'];
        if (!$package_id) {
            $isValid = false;
        }

        try {
            if (!CapMng::can(CapMng::CAP_STORAGE, false) && !CapMng::can(CapMng::CAP_CREATE, false)) {
                throw new Exception('Security issue.');
            }

            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid request."));
            }

            $package = DUP_PRO_Package::get_by_id($package_id);
            if (!$package) {
                throw new Exception(DUP_PRO_U::__("Could not get package by ID $package_id"));
            }

            $vm = new stdClass();

            /* -- First populate the transfer log information -- */

            // If this is the package being requested include the transfer details
            $vm->transfer_logs = array();

            $active_upload_info = null;

            $storages = DUP_PRO_Storage_Entity::get_all();

            /* @var $upload_info DUP_PRO_Package_Upload_Info */
            foreach ($package->upload_infos as &$upload_info) {
                if ($upload_info->storage_id != DUP_PRO_Virtual_Storage_IDs::Default_Local) {
                    $status      = $upload_info->get_status();
                    $status_text = $upload_info->get_status_text();

                    $transfer_log = new stdClass();

                    if ($upload_info->get_started_timestamp() == null) {
                        $transfer_log->started = DUP_PRO_U::__('N/A');
                    } else {
                        $transfer_log->started = DUP_PRO_DATE::getLocalTimeFromGMTTicks($upload_info->get_started_timestamp());
                    }

                    if ($upload_info->get_stopped_timestamp() == null) {
                        $transfer_log->stopped = DUP_PRO_U::__('N/A');
                    } else {
                        $transfer_log->stopped = DUP_PRO_DATE::getLocalTimeFromGMTTicks($upload_info->get_stopped_timestamp());
                    }

                    $transfer_log->status_text = $status_text;
                    $transfer_log->message     = $upload_info->get_status_message();

                    $transfer_log->storage_type_text = DUP_PRO_U::__('Unknown');
                    /* @var $storage DUP_PRO_Storage_Entity */
                    foreach ($storages as $storage) {
                        if ($storage->id == $upload_info->storage_id) {
                            $transfer_log->storage_type_text = $storage->get_type_text();
                           // break;
                        }
                    }

                    array_unshift($vm->transfer_logs, $transfer_log);

                    if ($status == DUP_PRO_Upload_Status::Running) {
                        if ($active_upload_info != null) {
                            DUP_PRO_Log::trace("More than one upload info is running at the same time for package {$package->ID}");
                        }

                        $active_upload_info = &$upload_info;
                    }
                }
            }

            /* -- Now populate the activa package information -- */

            /* @var $active_package DUP_PRO_Package */
            $active_package = DUP_PRO_Package::get_next_active_package();

            if ($active_package == null) {
                // No active package
                $vm->active_package_id = -1;
                $vm->text              = DUP_PRO_U::__('No package is building.');
            } else {
                $vm->active_package_id = $active_package->ID;

                if ($active_package->ID == $package_id) {
                    //$vm->is_transferring = (($package->Status >= DUP_PRO_PackageStatus::COPIEDPACKAGE) && ($package->Status < DUP_PRO_PackageStatus::COMPLETE));
                    if ($active_upload_info != null) {
                        $vm->percent_text = "{$active_upload_info->progress}%";
                        $vm->text         = $active_upload_info->get_status_message();
                    } else {
                        // We see this condition at the beginning and end of the transfer so throw up a generic message
                        $vm->percent_text = "";
                        $vm->text         = DUP_PRO_U::__("Synchronizing with server...");
                    }
                } else {
                    $vm->text = DUP_PRO_U::__("Another package is presently running.");
                }

                if ($active_package->is_cancel_pending()) {
                    // If it's getting cancelled override the normal text
                    $vm->text = DUP_PRO_U::__("Cancellation pending...");
                }
            }

            $json['success'] = true;
            $json['vm']      = $vm;
        } catch (Exception $ex) {
            $json['message'] = $ex->getMessage();
            DUP_PRO_Log::trace($ex->getMessage());
        }

        die(SnapJson::jsonEncode($json));
    }

    private static function get_adjusted_package_status(DUP_PRO_Package $package)
    {
        $estimated_progress = ($package->build_progress->current_build_mode == DUP_PRO_Archive_Build_Mode::Shell_Exec) ||
            ($package->ziparchive_mode == DUP_PRO_ZipArchive_Mode::SingleThread);

        if (($package->Status == DUP_PRO_PackageStatus::ARCSTART) && $estimated_progress) {
            // Amount of time passing before we give them a 1%
            $time_per_percent       = 11;
            $thread_age             = time() - $package->build_progress->thread_start_time;
            $total_percentage_delta = DUP_PRO_PackageStatus::ARCDONE - DUP_PRO_PackageStatus::ARCSTART;

            if ($thread_age > ($total_percentage_delta * $time_per_percent)) {
                // It's maxed out so just give them the done condition for the rest of the time
                return DUP_PRO_PackageStatus::ARCDONE;
            } else {
                $percentage_delta = (int) ($thread_age / $time_per_percent);

                return DUP_PRO_PackageStatus::ARCSTART + $percentage_delta;
            }
        } else {
            return $package->Status;
        }
    }

    public function is_pack_running()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_is_pack_running', 'nonce');

        ob_start();
        try {
            CapMng::can(CapMng::CAP_BASIC);

            $error  = false;
            $result = array(
                'running' => false,
                'data'    => array(
                    'run_ids'      => array(),
                    'cancel_ids'   => array(),
                    'error_ids'    => array(),
                    'complete_ids' => array()
                ),
                'html'    => '',
                'message' => ''
            );

            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'duplicator_pro_is_pack_running')) {
                DUP_PRO_Log::trace('Security issue');
                throw new Exception('Security issue');
            }

            $tmpPackages = DUP_PRO_Package::get_row_by_status(array(
                    array('op' => '>=', 'status' => DUP_PRO_PackageStatus::COMPLETE)
            ));
            foreach ($tmpPackages as $cPack) {
                $result['data']['complete_ids'][] = $cPack->id;
            }

            $tmpPackages = DUP_PRO_Package::get_row_by_status(array(
                    'relation' => 'AND',
                    array('op' => '>=', 'status' => DUP_PRO_PackageStatus::PRE_PROCESS),
                    array('op' => '<', 'status' => DUP_PRO_PackageStatus::COMPLETE)
            ));
            foreach ($tmpPackages as $cPack) {
                $result['data']['run_ids'][] = $cPack->id;
            }
            $tmpPackages = DUP_PRO_Package::get_row_by_status(array(
                    array('op' => '=', 'status' => DUP_PRO_PackageStatus::PENDING_CANCEL)
            ));
            foreach ($tmpPackages as $cPack) {
                $result['data']['run_ids'][] = $cPack->id;
            }

            $tmpPackages = DUP_PRO_Package::get_row_by_status(array(
                    'relation' => 'OR',
                    array('op' => '=', 'status' => DUP_PRO_PackageStatus::BUILD_CANCELLED),
                    array('op' => '=', 'status' => DUP_PRO_PackageStatus::STORAGE_CANCELLED)
            ));
            foreach ($tmpPackages as $cPack) {
                $result['data']['cac_ids'][] = $cPack->id;
            }

            $tmpPackages = DUP_PRO_Package::get_row_by_status(array(
                    'relation' => 'AND',
                    array('op' => '<', 'status' => DUP_PRO_PackageStatus::PRE_PROCESS),
                    array('op' => '!=', 'status' => DUP_PRO_PackageStatus::BUILD_CANCELLED),
                    array('op' => '!=', 'status' => DUP_PRO_PackageStatus::STORAGE_CANCELLED),
                    array('op' => '!=', 'status' => DUP_PRO_PackageStatus::PENDING_CANCEL)
            ));
            foreach ($tmpPackages as $cPack) {
                $result['data']['err_ids'][] = $cPack->id;
            }

            $result['running'] = count($result['data']['run_ids']) > 0;
        } catch (Exception $e) {
            $error             = true;
            $result['message'] = $e->getMessage();
        }

        $result['html'] = ob_get_clean();
        if ($error) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }
    private static $package_statii_data = null;

    public static function statii_callback(DUP_PRO_Package $package)
    {
        /* @var $package DUP_PRO_Package */
        $package_status = new stdClass();

        $package_status->ID = $package->ID;

        $package_status->status = self::get_adjusted_package_status($package);
        //$package_status->status = $package->Status;
        $package_status->status_progress = $package->get_status_progress();
        $package_status->size            = $package->get_display_size();

        //TODO active storage
        $active_storage = $package->get_active_storage();

        if ($active_storage != null) {
            $package_status->status_progress_text = $active_storage->get_action_text();
        } else {
            $package_status->status_progress_text = '';
        }

        self::$package_statii_data[] = $package_status;
    }

    public function get_package_statii()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_get_package_statii', 'nonce');
        CapMng::can(CapMng::CAP_BASIC);

        self::$package_statii_data = array();
        DUP_PRO_Package::by_status_callback(array(__CLASS__, 'statii_callback'));

        die(SnapJson::jsonEncode(self::$package_statii_data));
    }

    public function get_dropbox_auth_url()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_dropbox_get_auth_url', 'nonce');

        $json = array(
            'success' => false,
            'message' => ''
        );

        try {
            CapMng::can(CapMng::CAP_STORAGE);

            $dropbox_client           = DUP_PRO_Storage_Entity::get_raw_dropbox_client(false);
            $json['dropbox_auth_url'] = $dropbox_client->createAuthUrl();
            $json['success']          = true;
        } catch (Exception $ex) {
            DUP_PRO_Log::trace($ex->getMessage());
            $json['message'] = $ex->getMessage();
        }

        die(SnapJson::jsonEncode($json));
    }

    public function get_onedrive_auth_url()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_onedrive_get_auth_url', 'nonce');

        $json      = array(
            'success' => false,
            'message' => '',
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'storage_type'      => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'business'          => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => null
                )
            ),
            'msgraph_all_perms' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => null
                )
            ),
        ));

        $isBusiness      = $inputData['business'];
        $msGraphAllPerms = $inputData['msgraph_all_perms'];
        $storageType     = $inputData['storage_type'];

        if (!$storageType || is_null($isBusiness) || is_null($msGraphAllPerms)) {
            $isValid = false;
        }
        //END OF VALIDATION
        try {
            CapMng::can(CapMng::CAP_STORAGE);
            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid Request."));
            }

            DUP_PRO_Log::trace("Is business: " . $isBusiness);
            $auth_arr                  = DUP_PRO_Onedrive_U::get_onedrive_auth_url_and_client(array(
                    'is_business'                         => $isBusiness,
                    'use_msgraph_api'                     => ($storageType == DUP_PRO_Storage_Types::OneDriveMSGraph),
                    'msgraph_all_folders_read_write_perm' => $msGraphAllPerms,
            ));
            $json['onedrive_auth_url'] = esc_url_raw($auth_arr["url"]);
            $json['success']           = true;
        } catch (Exception $ex) {
            DUP_PRO_Log::trace($ex->getMessage());
            $json['message'] = $ex->getMessage();
            $json['input']   = $inputData;
        }

        die(SnapJson::jsonEncode($json));
    }

    //NOTE: THIS ENDPOINT IS NOT BEING USED.
    public function get_onedrive_logout_url()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_onedrive_get_logout_url', 'nonce');

        $json      = array(
            'success' => false,
            'message' => '',
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'storage_id' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));

        $storage_id = $inputData['storage_id'];

        if (!$storage_id) {
            $isValid = false;
        }
        //END OF VALIDATION
        try {
            CapMng::can(CapMng::CAP_STORAGE);
            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid request."));
            }

            $storage_id_append           = "&storage_id=" . $storage_id;
            $callback_uri                = urlencode(self_admin_url("admin.php?page=duplicator-pro-storage&tab=storage"
                    . "&inner_page=edit&onedrive_action=onedrive-revoke-access$storage_id_append"));
            $json['onedrive_logout_url'] = DUP_PRO_Onedrive_U::get_onedrive_logout_url($callback_uri);
            $json['success']             = true;
        } catch (Exception $ex) {
            $json["message"] = $ex->getMessage();
            DUP_PRO_Log::trace($ex->getMessage());
        }

        die(SnapJson::jsonEncode($json));
    }

    public function duplicator_pro_onedrive_send_file_test()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_onedrive_send_file_test', 'nonce');

        $json      = array(
            'success' => false,
            'message' => '',
        );
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'storage_id' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));

        $storage_id = $inputData['storage_id'];

        if (!$storage_id) {
            $isValid = false;
        }

        $source_handle   = null;
        $source_filepath = '';
        try {
            CapMng::can(CapMng::CAP_STORAGE);
            if (!$isValid) {
                throw new Exception(DUP_PRO_U::esc_html__("Invalid request."));
            }

            if (!$storage_id) {
                throw new Exception(DUP_PRO_U::__("Invalid DropBox Storage ID in request."));
            }

            $storage = DUP_PRO_Storage_Entity::get_by_id($storage_id);
            if (!$storage) {
                throw new Exception(DUP_PRO_U::esc_html__("Couldn't get the storage for the OneDrive send test"));
            }

            $source_filepath = tempnam(sys_get_temp_dir(), 'DUP');
            if ($source_filepath === false) {
                throw new Exception(DUP_PRO_U::esc_html__("Couldn't create the temp file for the OneDrive send test"));
            }
            DUP_PRO_Log::trace("Created temp file $source_filepath");

            $file_name = basename($source_filepath);
            /** @todo add chck of all IO functions return */
            $source_handle = fopen($source_filepath, 'rw+b');
            $rnd           = rand();
            fwrite($source_handle, "$rnd");
            DUP_PRO_Log::trace("Wrote $rnd to $source_filepath");
            fclose($source_handle);
            $source_handle = null;

            $parent = $storage->get_onedrive_storage_folder();
            if (!$parent) {
                throw new Exception(DUP_PRO_U::esc_html__("Couldn't get the parent folder for the OneDrive send test"));
            }

            //$test_file = $parent->createFile($file_name,$source_handle);
            //Replacing the createFile method with uploadChunk so
            //we can directly check, if the method we are going to
            //use is working on this set-up.
            $json['parent_id'] = $parent->getId();
            $onedrive          = $storage->get_onedrive_client();
            $remote_path       = $storage->get_sanitized_storage_folder() . $file_name;
            $onedrive->uploadFileChunk($source_filepath, $remote_path);
            $test_file = $onedrive->RUploader->getFile();

            /*
              error_log('-------------------------');
              error_log(print_r($test_file, true));
              error_log('++++++++++++++++++++++++++');
             */
            try {
                if ($test_file->sha1CheckSum($source_filepath)) {
                    $json['success'] = true;
                    $json['message'] = DUP_PRO_U::esc_html__('Successfully stored and retrieved file');
                    $onedrive->deleteDriveItem($test_file->getId());
                } else {
                    $json['message'] = DUP_PRO_U::esc_html__('There was a problem storing or retrieving the temporary file on this account.');
                }
            } catch (Exception $exception) {
                if ($exception->getCode() == 404 && $onedrive->isBusiness()) {
                    $json['success'] = true;
                    $json['message'] = DUP_PRO_U::esc_html__('Successfully stored and retrieved file');
                    $onedrive->deleteDriveItem($test_file->getId());
                } else {
                    $json['message'] = DUP_PRO_U::esc_html__('An error happened. Error message: ' . $exception->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log(print_r($e, true));
            $errorMessage = $e->getMessage();

            DUP_PRO_Log::trace($errorMessage);
            $json['message'] = $errorMessage;
        }

        if (file_exists($source_filepath)) {
            DUP_PRO_Log::trace("attempting to delete {$source_filepath}");
            unlink($source_filepath);
        }

        die(SnapJson::jsonEncode($json));
    }

    public function duplicator_pro_local_storage_test()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_local_storage_test', 'nonce');

        CapMng::can(CapMng::CAP_STORAGE);

        $localStorageAdapter = new LocalStorage(array(
            'storage_id'     => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'storage_folder' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));

        die(SnapJson::jsonEncode($localStorageAdapter->testStorage()->getResponseForAPI()));
    }

    public function get_gdrive_auth_url()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_gdrive_get_auth_url', 'nonce');

        $json = array(
            'gdrive_auth_url' => '',
            'status'          => -1,
            'message'         => ''
        );

        try {
            CapMng::can(CapMng::CAP_STORAGE);
            $google_client           = DUP_PRO_GDrive_U::get_raw_google_client();
            $json['gdrive_auth_url'] = $google_client->createAuthUrl();
            $json['status']          = 0;
        } catch (Exception $ex) {
            $msg             = $ex->getMessage();
            $json['message'] = $msg;
            DUP_PRO_Log::trace($msg);
        }

        die(SnapJson::jsonEncode($json));
    }

    public function duplicator_pro_get_folder_children()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('duplicator_pro_get_folder_children', 'nonce');

        $json      = array();
        $isValid   = true;
        $inputData = filter_input_array(INPUT_GET, array(
            'folder'  => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'exclude' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_ARRAY,
                'options' => array(
                    'default' => array()
                )
            )
        ));
        $folder    = $inputData['folder'];
        $exclude   = $inputData['exclude'];

        if ($folder === false) {
            $isValid = false;
        }

        ob_start();
        try {
            CapMng::can(CapMng::CAP_BASIC);

            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__('Invalid request.'));
            }
            if (is_dir($folder)) {
                try {
                    $Package = DUP_PRO_Package::get_temporary_package();
                } catch (Exception $e) {
                    $Package = null;
                }

                $treeObj = new DUP_PRO_Tree_files($folder, true, $exclude);
                $treeObj->uasort(array('DUP_PRO_Archive', 'sortTreeByFolderWarningName'));
                if (!is_null($Package)) {
                    $treeObj->treeTraverseCallback(array($Package->Archive, 'checkTreeNodesFolder'));
                }

                $jsTreeData = DUP_PRO_Archive::getJsTreeStructure($treeObj, '', false);
                $json       = $jsTreeData['children'];
            }
        } catch (Exception $e) {
            DUP_PRO_Log::trace($e->getMessage());
            $json['message'] = $e->getMessage();
        }
        ob_clean();
        wp_send_json($json);
    }

    public static function admin_notice_to_dismiss_callback()
    {

        $noticeToDismiss = filter_input(INPUT_POST, 'notice', FILTER_SANITIZE_SPECIAL_CHARS);
        $systemGlobal    = DUP_PRO_System_Global_Entity::getInstance();
        switch ($noticeToDismiss) {
            case DUP_PRO_UI_Notice::OPTION_KEY_ACTIVATE_PLUGINS_AFTER_INSTALL:
            case DUP_PRO_UI_Notice::OPTION_KEY_MIGRATION_SUCCESS_NOTICE:
                $ret = delete_option($noticeToDismiss);
                break;
            case DUP_PRO_UI_Notice::OPTION_KEY_S3_CONTENTS_FETCH_FAIL_NOTICE:
                $ret = update_option(DUP_PRO_UI_Notice::OPTION_KEY_S3_CONTENTS_FETCH_FAIL_NOTICE, false);
                break;
            case Notices::OPTION_KEY_EXPIRED_LICENCE_NOTICE_DISMISS_TIME:
                $ret = update_option(Notices::OPTION_KEY_EXPIRED_LICENCE_NOTICE_DISMISS_TIME, time());
                break;
            case DUP_PRO_UI_Notice::QUICK_FIX_NOTICE:
                $systemGlobal->clearFixes();
                $ret = $systemGlobal->save();
                break;
            case DUP_PRO_UI_Notice::FAILED_SCHEDULE_NOTICE:
                $systemGlobal->schedule_failed = false;
                $ret                           = $systemGlobal->save();
                break;
            default:
                throw new Exception('Notice invalid');
        }
        return $ret;
    }

    public static function admin_notice_to_dismiss()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'admin_notice_to_dismiss_callback'),
            'duplicator_pro_admin_notice_to_dismiss',
            $_POST['nonce'],
            CapMng::CAP_BASIC
        );
    }

    public function download_package_file()
    {
        DUP_PRO_Handler::init_error_handler();
        $inputData = filter_input_array(INPUT_GET, array(
            'fileType' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'hash' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'token' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));

        try {
            if (
                $inputData['token'] === false || $inputData['hash'] === false || $inputData["fileType"] === false
                || md5(\Duplicator\Utils\Crypt\CryptBlowfish::encrypt($inputData['hash'])) !== $inputData['token']
                || ($package = DUP_PRO_Package::get_by_hash($inputData['hash'])) == false
            ) {
                throw new Exception(DUP_PRO_U::__("Invalid request."));
            }

            switch ($inputData['fileType']) {
                case DUP_PRO_Package_File_Type::Installer:
                    $filePath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Installer);
                    $fileName = $package->Installer->getDownloadName();
                    break;
                case DUP_PRO_Package_File_Type::Archive:
                    $filePath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive);
                    $fileName = basename($filePath);
                    break;
                case DUP_PRO_Package_File_Type::Log:
                    $filePath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Log);
                    $fileName = basename($filePath);
                    break;
                default:
                    throw new Exception(DUP_PRO_U::__("File type not supported."));
            }

            if ($filePath == false) {
                throw new Exception(DUP_PRO_U::__("File don\'t exists"));
            }

            \Duplicator\Libs\Snap\SnapIO::serveFileForDownload($filePath, $fileName, DUPLICATOR_PRO_BUFFER_DOWNLOAD_SIZE);
        } catch (Exception $ex) {
            wp_die($ex->getMessage());
        }
    }
}
