<?php

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapUtil;

require_once(DUPLICATOR____PATH . '/ctrls/ctrl.base.php');
require_once(DUPLICATOR____PATH . '/classes/utilities/class.u.json.php');
require_once(DUPLICATOR____PATH . '/classes/package/class.pack.php');
/**
 * Controller for Tools
 */
class DUP_PRO_CTRL_Package extends DUP_PRO_CTRL_Base
{
    /**
     *  Init this instance of the object
     */
    public function __construct()
    {
        add_action('wp_ajax_DUP_PRO_CTRL_Package_addQuickFilters', array($this, 'addQuickFilters'));
        add_action('wp_ajax_DUP_PRO_CTRL_Package_toggleGiftFeatureButton', array($this, 'toggleGiftFeatureButton'));
    }

    /**
     * Removed all reserved installer files names
     *
     * @return void
     */
    public function addQuickFilters()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('DUP_PRO_CTRL_Package_addQuickFilters', 'nonce');
        $inputData = filter_input_array(INPUT_POST, array(
                'dir_paths' => array(
                    'filter'  => FILTER_DEFAULT,
                    'flags'   => FILTER_REQUIRE_SCALAR,
                    'options' => array(
                        'default' => ''
                    )
                ),
                'file_paths' => array(
                    'filter'  => FILTER_DEFAULT,
                    'flags'   => FILTER_REQUIRE_SCALAR,
                    'options' => array(
                        'default' => ''
                    )
                ),
            ));
        $result    = new DUP_PRO_CTRL_Result($this);
        try {
            // CONTROLLER LOGIC
            // Need to update both the template and the temporary package because:
            // 1) We need to preserve preferences of this build for future manual builds - the manual template is used for this.
            // 2) Temporary package is used during this build - keeps all the settings/storage information.  Will be inserted into the package table after they ok the scan results.
            $template = DUP_PRO_Package_Template_Entity::get_manual_template();
            if ($template->archive_filter_on) {
                $template->archive_filter_dirs  = $template->archive_filter_dirs . (strlen($template->archive_filter_dirs) ? ';' : '') . SnapUtil::sanitizeNSChars($inputData['dir_paths']);
                $template->archive_filter_files = $template->archive_filter_files . (strlen($template->archive_filter_files) ? ';' : '') . SnapUtil::sanitizeNSChars($inputData['file_paths']);
            } else {
                $template->archive_filter_dirs  = SnapUtil::sanitizeNSChars($inputData['dir_paths']);
                $template->archive_filter_files = SnapUtil::sanitizeNSChars($inputData['file_paths']);
            }

            $template->archive_filter_dirs  = DUP_PRO_Archive::parseDirectoryFilter($template->archive_filter_dirs);
            $template->archive_filter_files = DUP_PRO_Archive::parseDirectoryFilter($template->archive_filter_files);
            if (!$template->archive_filter_on) {
                $template->archive_filter_exts = '';
            }

            $template->archive_filter_on    = 1;
            $template->archive_filter_names = true;
            $template->save();

            $temporary_package                       = DUP_PRO_Package::get_temporary_package();
            $temporary_package->Archive->FilterDirs  = $template->archive_filter_dirs;
            $temporary_package->Archive->FilterFiles = $template->archive_filter_files;
            $temporary_package->Archive->FilterOn    = 1;
            $temporary_package->Archive->FilterNames = $template->archive_filter_names;
            $temporary_package->set_temporary_package();
            // Result
            $payload['filter-dirs']  = $temporary_package->Archive->FilterDirs;
            $payload['filter-files'] = $temporary_package->Archive->FilterFiles;
            $payload['filter-names'] = $temporary_package->Archive->FilterNames;
            // RETURN RESULT
            //$test = ($success) ? DUP_PRO_CTRL_Status::SUCCESS : DUP_PRO_CTRL_Status::FAILED;
            $test = DUP_PRO_CTRL_Status::SUCCESS;
            $result->process($payload, $test);
        } catch (Exception $exc) {
            $result->processError($exc);
        }
    }

    /**
     * Toggles the feature gift icon on the packages page.  This should only show for new features and
     * once its clicked should hide.
     *
     * @return void
     */
    public function toggleGiftFeatureButton()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('DUP_PRO_CTRL_Package_toggleGiftFeatureButton', 'nonce');
        DUP_PRO_Log::trace("toggle gift feature");
        $hide_gift_btn = filter_input(INPUT_POST, 'hide_gift_btn', FILTER_VALIDATE_BOOLEAN);

        $result = new DUP_PRO_CTRL_Result($this);
        try {
            // CONTROLLER LOGIC
            $global = DUP_PRO_Global_Entity::getInstance();
            if ($hide_gift_btn == 'true') {
                $global->dupHidePackagesGiftFeatures = true;
            }

            $success = $global->save();
            // RETURN RESULT
            $status = ($success) ? DUP_PRO_CTRL_Status::SUCCESS : DUP_PRO_CTRL_Status::FAILED;
            $result->process(null, $status);
        } catch (Exception $exc) {
            $result->processError($exc);
        }
    }
}
