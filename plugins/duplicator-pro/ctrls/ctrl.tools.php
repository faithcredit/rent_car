<?php

defined("ABSPATH") or die("");

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Controllers\ToolsPageController;
use Duplicator\Libs\Snap\SnapUtil;

require_once(DUPLICATOR____PATH . '/ctrls/ctrl.base.php');
require_once(DUPLICATOR____PATH . '/classes/class.scan.check.php');

/**
 * Controller for Tools
 */
class DUP_PRO_CTRL_Tools extends DUP_PRO_CTRL_Base
{
    /**
     *  Init this instance of the object
     */
    public function __construct()
    {
        add_action('wp_ajax_DUP_PRO_CTRL_Tools_runScanValidator', array($this, 'runScanValidator'));
    }

    /**
     *
     * @return boolean
     */
    public static function isToolPage()
    {
        return ControllersManager::isCurrentPage(ControllersManager::TOOLS_SUBMENU_SLUG);
    }

    /**
     *
     * @return boolean
     */
    public static function isDiagnosticPage()
    {
        return ControllersManager::isCurrentPage(ControllersManager::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_DISAGNOSTIC, ToolsPageController::L3_SLUG_DISAGNOSTIC_DIAGNOSTIC);
    }

    /**
     * Calls the ScanValidator and returns display JSON result
     *
     * @return void
     */
    public function runScanValidator()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('DUP_PRO_CTRL_Tools_runScanValidator', 'nonce');

        //@set_time_limit(0);
        // Let's setup execution time on proper way (multiserver supported)
        try {
            if (function_exists('set_time_limit')) {
                set_time_limit(0); // unlimited
            } else {
                if (function_exists('ini_set') && SnapUtil::isIniValChangeable('max_execution_time')) {
                    ini_set('max_execution_time', '0'); // unlimited
                }
            }

            // there is error inside PHP because of PHP versions and server setup,
            // let's try to made small hack and set some "normal" value if is possible
        } catch (Exception $ex) {
            if (function_exists('set_time_limit')) {
                @set_time_limit(3600); // 60 minutes
            } else {
                if (function_exists('ini_set') && SnapUtil::isIniValChangeable('max_execution_time')) {
                    @ini_set('max_execution_time', '3600'); //  60 minutes
                }
            }
        }

        //scan-recursive
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'scan-recursive' => array(
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'flags'  => FILTER_NULL_ON_FAILURE
            )
        ));

        if (is_null($inputData['scan-recursive'])) {
            $isValid = false;
        }

        $result = new DUP_PRO_CTRL_Result($this);

        try {
            if (!$isValid) {
                throw new Exception(DUP_PRO_U::__("Invalid Request."));
            }

            $scanner            = new DUP_PRO_ScanValidator();
            $scanner->recursion = $inputData['scan-recursive'];
            $payload            = $scanner->run(DUP_PRO_Archive::getScanPaths());

            //RETURN RESULT
            $test = ($payload->fileCount > 0) ? DUP_PRO_CTRL_Status::SUCCESS : DUP_PRO_CTRL_Status::FAILED;
            $result->process($payload, $test);
        } catch (Exception $exc) {
            $result->processError($exc);
        }
    }
}
