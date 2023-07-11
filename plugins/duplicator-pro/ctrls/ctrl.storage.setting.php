<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Controllers\SettingsPageController;
use Duplicator\Libs\Snap\SnapUtil;

class DUP_PRO_CTRL_Storage_Setting
{
    /**
     * @var string nonce action name
     */
    const NONCE_ACTION = 'duppro-settings-storage-edit';

    /**
     * @var string form actionx
     */
    const FORM_ACTION = 'save';

    /**
     *
     * @var string current active tab
     */
    private static $currentSubTab = SettingsPageController::L3_SLUG_STORAGE_GENERAL;

    /**
     *
     * @var string current subtab url
     */
    private static $suceessMessage = '';

    /**
     *
     * @return string
     */
    public static function getCurrentSubTab()
    {
        return self::$currentSubTab;
    }

    /**
     * main controller function
     *
     * @return void
     * @throws Exception
     */
    public static function controller()
    {

        DUP_PRO_Handler::init_error_handler();

        switch (SnapUtil::filterInputRequest('subtab', FILTER_DEFAULT)) {
            case SettingsPageController::L3_SLUG_STORAGE_SSL:
                self::$currentSubTab = SettingsPageController::L3_SLUG_STORAGE_SSL;
                break;
            case SettingsPageController::L3_SLUG_STORAGE_STORAGES:
                self::$currentSubTab = SettingsPageController::L3_SLUG_STORAGE_STORAGES;
                break;
            case SettingsPageController::L3_SLUG_STORAGE_GENERAL:
            default:
                self::$currentSubTab = SettingsPageController::L3_SLUG_STORAGE_GENERAL;
                break;
        }

        self::processInput();
        self::doView();
    }

    /**
     * for processing input and save
     *
     * @return void
     * @throws Exception
     */
    private static function processInput()
    {
        //SAVE RESULTS
        if (empty($_POST['action']) || $_POST['action'] != self::FORM_ACTION) {
            return;
        }

        DUP_PRO_U::verifyNonce($_POST['_wpnonce'], self::NONCE_ACTION);
        $global = DUP_PRO_Global_Entity::getInstance();

        switch (self::$currentSubTab) {
            case SettingsPageController::L3_SLUG_STORAGE_GENERAL:
                $global->storage_htaccess_off = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, '_storage_htaccess_off');
                $global->max_storage_retries  = SnapUtil::sanitizeIntInput(SnapUtil::INPUT_REQUEST, 'max_storage_retries', 10);
                break;
            case SettingsPageController::L3_SLUG_STORAGE_SSL:
                $global->ssl_useservercerts = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, 'ssl_useservercerts');
                $global->ssl_disableverify  = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, 'ssl_disableverify');
                $global->ipv4_only          = SnapUtil::sanitizeBoolInput(SnapUtil::INPUT_REQUEST, 'ipv4_only');
                break;
            case SettingsPageController::L3_SLUG_STORAGE_STORAGES:
                $global->gdrive_upload_chunksize_in_kb   = (int) $_REQUEST['gdrive_upload_chunksize_in_kb'];
                $global->dropbox_upload_chunksize_in_kb  = (int) $_REQUEST['dropbox_upload_chunksize_in_kb'];
                $global->dropbox_transfer_mode           = $_REQUEST['dropbox_transfer_mode'];
                $global->gdrive_transfer_mode            = $_REQUEST['gdrive_transfer_mode'];
                $global->s3_upload_part_size_in_kb       = (int) $_REQUEST['s3_upload_part_size_in_kb'];
                $global->local_upload_chunksize_in_MB    = (int) $_REQUEST['local_upload_chunksize_in_MB'];
                $global->onedrive_upload_chunksize_in_kb = filter_input(
                    INPUT_POST,
                    'onedrive_upload_chunksize_in_kb',
                    FILTER_VALIDATE_INT,
                    array(
                        'options' => array(
                            'default'   => DUPLICATOR_PRO_ONEDRIVE_UPLOAD_CHUNK_DEFAULT_SIZE_IN_KB,
                            'min_range' => DUPLICATOR_PRO_ONEDRIVE_UPLOAD_CHUNK_MIN_SIZE_IN_KB
                        )
                    )
                );
                break;

            default:
                throw new Exception("Unknown import type " . self::$currentSubTab . " detected.");
        }

        $action_updated = $global->save();
        if ($action_updated) {
            self::$suceessMessage = DUP_PRO_U::__("Storage Settings Saved");
        }
    }

    public static function doMessages()
    {
        if (!empty(self::$suceessMessage)) {
            DUP_PRO_UI_Notice::displayGeneralAdminNotice(self::$suceessMessage, DUP_PRO_UI_Notice::GEN_SUCCESS_NOTICE, false, 'dpro-wpnotice-box');
            self::$suceessMessage = '';
        }
    }

    /**
     * render view for storage settings
     *
     * @return void
     */
    private static function doView()
    {
        require(DUPLICATOR____PATH . '/views/settings/storage/storage.php');
    }
}
