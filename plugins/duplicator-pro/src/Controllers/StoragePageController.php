<?php

/**
 * Storage page controller
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use DUP_PRO_Storage_Entity;
use DUP_PRO_Storage_Types;
use DUP_PRO_Log;
use DUP_PRO_U;
use DUP_PRO_GDrive_U;
use DUP_PRO_GDrive_Authorization_States;
use DUP_PRO_Dropbox_Authorization_States;
use DUP_PRO_Onedrive_U;
use DUP_PRO_OneDrive_Authorization_States;
use DUP_PRO_OneDrive_Config;
use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\CapMng;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;
use Duplicator\Core\Controllers\PageAction;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapString;
use Duplicator\Utils\PathUtil;
use Exception;

class StoragePageController extends AbstractMenuPageController
{
    const INNER_PAGE_LIST         = 'storage';
    const INNER_PAGE_EDIT         = 'edit';
    const INNER_PAGE_EDIT_DEFAULT = 'edit-default';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::STORAGE_SUBMENU_SLUG;
        $this->pageTitle    = __('Storage', 'duplicator-pro');
        $this->menuLabel    = __('Storage', 'duplicator-pro');
        $this->capatibility = CapMng::CAP_STORAGE;
        $this->menuPos      = 40;

        add_filter('duplicator_page_actions_' . $this->pageSlug, array($this, 'pageActions'));
        add_action('duplicator_after_run_actions_' . $this->pageSlug, array($this, 'pageAfterActions'));
        add_action('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
    }

    /**
     * Return actions for current page
     *
     * @param PageAction[] $actions actions lists
     *
     * @return PageAction[]
     */
    public function pageActions($actions)
    {
        $actions[] = new PageAction(
            'save',
            [$this, 'actionEditSave'],
            [$this->pageSlug],
            'edit'
        );
        $actions[] = new PageAction(
            'copy-storage',
            [$this, 'actionEditCopyStorage'],
            [$this->pageSlug],
            'edit'
        );
        $actions[] = new PageAction(
            'gdrive-revoke-access',
            [$this, 'actionGdriveRevokeAccess'],
            [$this->pageSlug],
            'edit'
        );
        $actions[] = new PageAction(
            'dropbox-revoke-access',
            [$this, 'actionDropboxRevokeAccess'],
            [$this->pageSlug],
            'edit'
        );
        $actions[] = new PageAction(
            'onedrive-revoke-access',
            [$this, 'actionOneDriveRevokeAccess'],
            [$this->pageSlug],
            'edit'
        );
        return $actions;
    }

    /**
     * Page after actions hook
     *
     * @param bool $isActionCalled true if one actions is called,false if no actions
     *
     * @return void
     */
    public function pageAfterActions($isActionCalled)
    {
        $tplMng = TplMng::getInstance();
        if ($this->getCurrentInnerPage() == 'edit' && $tplMng->hasGlobalValue('storage_id') == false) {
            $storage_id = isset($_REQUEST['storage_id']) ? intval($_REQUEST['storage_id']) : -1;
            $storage    = ($storage_id == -1) ? new DUP_PRO_Storage_Entity() : DUP_PRO_Storage_Entity::get_by_id($storage_id);

            $tplMng->setGlobalValue('storage_id', $storage_id);
            $tplMng->setGlobalValue('storage', $storage);
            $tplMng->setGlobalValue('error_message', null);
            $tplMng->setGlobalValue('success_message', null);
        }
    }

    /**
     * Render page content
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     *
     * @return void
     */
    public function renderContent($currentLevelSlugs)
    {
        try {
            switch ($this->getCurrentInnerPage()) {
                case self::INNER_PAGE_EDIT:
                    TplMng::getInstance()->render(
                        'admin_pages/storages/storage_edit',
                        [
                            'blur' => !License::can(License::CAPABILITY_PRO_BASE)
                        ]
                    );
                    break;
                case self::INNER_PAGE_EDIT_DEFAULT:
                    TplMng::getInstance()->render(
                        'admin_pages/storages/storage_edit_default',
                        [
                            'blur' => !License::can(License::CAPABILITY_PRO_BASE)
                        ]
                    );
                    break;
                case self::INNER_PAGE_LIST:
                default:
                    // I left the global try catch for security but the exceptions should be managed inside the list.
                    TplMng::getInstance()->render(
                        'admin_pages/storages/storage_list',
                        [
                            'blur' => !License::can(License::CAPABILITY_PRO_BASE)
                        ]
                    );
                    break;
            }
        } catch (Exception $e) {
            echo self::getErrorMsg($e);
        }
    }

    /**
     * Gt exception error message
     *
     * @param Exception $e exception error
     *
     * @return string
     */
    public static function getErrorMsg(Exception $e)
    {
        $settings_url = ControllersManager::getMenuLink(ControllersManager::SETTINGS_SUBMENU_SLUG);

        $storage_error_msg  = '<div class="error-txt" style="margin:10px 0 20px 0; max-width:750px">';
        $storage_error_msg .= DUP_PRO_U::esc_html__('An error has occurred while trying to read a storage item!  ');
        $storage_error_msg .= DUP_PRO_U::esc_html__('To resolve this issue delete the storage item and re-enter its information.  ');
        $storage_error_msg .= '<br/><br/>';
        $storage_error_msg .= DUP_PRO_U::esc_html__(
            'This problem can be due to a security plugin changing keys in wp-config.php, ' .
            'causing the storage information to become unreadable.  '
        );
        $storage_error_msg .= DUP_PRO_U::esc_html__(
            'If such a plugin is doing this then either disable ' .
            'the key changing functionality in the security plugin or go to '
        );
        $storage_error_msg .= "<a href='{$settings_url}'>";
        $storage_error_msg .= DUP_PRO_U::esc_html__('Duplicator Pro > Settings');
        $storage_error_msg .= '</a>';
        $storage_error_msg .= DUP_PRO_U::esc_html__(' and disable settings encryption.  ');
        $storage_error_msg .= '<br/><br/>';
        $storage_error_msg .= DUP_PRO_U::esc_html__('If the problem persists after doing these things then please contact the support team.');
        $storage_error_msg .= '</div>';
        $storage_error_msg .= '<a href="javascript:void(0)" onclick="jQuery(\'#dup-store-err-details\').toggle();">';
        $storage_error_msg .= DUP_PRO_U::esc_html__('Show Details');
        $storage_error_msg .= '</a>';
        $storage_error_msg .= '<div id="dup-store-err-details" >' . esc_html($e->getMessage()) .
        "<br/><br/><small>" .
        esc_html($e->getTraceAsString()) .
        "</small></div>";
        return $storage_error_msg;
    }

    /**
     * Save storage
     *
     * @return array{storage_id: int, storage: DUP_PRO_Storage_Entity, error_message: ?string, success_message: ?string}
     */
    public function actionEditSave()
    {
        $error_message   = null;
        $success_message = null;
        $storage_id      = isset($_REQUEST['storage_id']) ? intval($_REQUEST['storage_id']) : -1;
        $storage         = ($storage_id == -1) ? new DUP_PRO_Storage_Entity() : DUP_PRO_Storage_Entity::get_by_id($storage_id);
        $needsReset      = ($storage_id != -1 && isset($_REQUEST['storage_type']) && $storage->storage_type != $_REQUEST['storage_type']) ? true : false;
        if ($needsReset) {
            $storage     = new DUP_PRO_Storage_Entity();
            $storage->id = $storage_id;
        }

        list($error_message, $success_message) = StoragePageController::handleGDriveAuth($storage);

        if ($error_message == null && $success_message == null) {
            list($error_message, $success_message) = StoragePageController::handleDropboxAuth($storage);
        }

        if ($error_message == null && $success_message == null) {
            list($error_message, $success_message) = StoragePageController::handleOneDriveAuth($storage);
        }

        // For hidden passwords/keys we should remember old values taken from the database,
        // to preserve them in case if new values are empty
        $s3_secret_key             = $storage->s3_secret_key;
        $ftp_password              = $storage->ftp_password;
        $sftp_password             = $storage->sftp_password;
        $sftp_private_key_password = $storage->sftp_private_key_password;

        $storage->set_post_variables($_REQUEST);

        // For hidden passwords/keys we should remember old values taken from the database,
        // to preserve them in case if new values are empty
        if (SnapString::stringLength($storage->s3_secret_key) == 0) {
            $storage->s3_secret_key = $s3_secret_key;
        }
        if (SnapString::stringLength($storage->ftp_password) == 0) {
            $storage->ftp_password = $ftp_password;
        }
        if (SnapString::stringLength($storage->sftp_password) == 0) {
            $storage->sftp_password = $sftp_password;
        }
        if (SnapString::stringLength($storage->sftp_private_key_password) == 0) {
            $storage->sftp_private_key_password = $sftp_private_key_password;
        }

        $saveStorage = true;

        // Checkboxes don't set post values when off so have to manually set these
        switch ($_REQUEST['storage_type']) {
            case DUP_PRO_Storage_Types::Local:
                $storage->local_filter_protection = isset($_REQUEST['_local_filter_protection']);
                $safe_path                        = untrailingslashit(trim(SnapIO::safePath(sanitize_text_field($_REQUEST['_local_storage_folder']))));
                if ($storage->local_storage_folder === $safe_path) {
                    // don't require check
                    break;
                }
                $storage->local_storage_folder = $safe_path;
                if (strlen($storage->local_storage_folder) == 0) {
                    $error_message = __('Local storage path can\'t be empty.', 'duplicator-pro');
                    $saveStorage   = false;
                    break;
                }
                if (PathUtil::isPathInCoreDirs($storage->local_storage_folder)) {
                    $error_message = __('This storage path can\'t be used because
                        it is a core WordPress directory or a sub-path of a core directory.', 'duplicator-pro');
                    $saveStorage   = false;
                    break;
                }
                if ($storage->is_path_repeated()) {
                    $error_message = __('A local storage already exists in that folder.', 'duplicator-pro');
                    $saveStorage   = false;
                    break;
                }
                if (file_exists($storage->local_storage_folder)) {
                    $error_message = __('Select storage path already exists, select another path.', 'duplicator-pro');
                    $saveStorage   = false;
                    break;
                }
                if (mkdir($storage->local_storage_folder, 0755, true)) {
                    $success_message = sprintf(
                        __('Storage Provider Updated - Folder %1$s was created.', 'duplicator-pro'),
                        $storage->local_storage_folder
                    );
                } else {
                    $error_message = sprintf(
                        __('Storage Provider Updated - Unable to create folder %1$s.', 'duplicator-pro'),
                        $storage->local_storage_folder
                    );
                    $saveStorage   = false;
                }
                break;
            case DUP_PRO_Storage_Types::FTP:
                $storage->ftp_passive_mode   = isset($_REQUEST['_ftp_passive_mode']);
                $storage->ftp_ssl            = isset($_REQUEST['_ftp_ssl']);
                $storage->ftp_use_curl       = isset($_POST['_ftp_use_curl']);
                $storage->ftp_storage_folder = SnapIO::safePath(sanitize_text_field($_REQUEST['_ftp_storage_folder']));
                break;
            case DUP_PRO_Storage_Types::SFTP:
                $sftp_storage_folder                 = isset($_REQUEST['_sftp_storage_folder']) ? sanitize_text_field($_REQUEST['_sftp_storage_folder']) : '';
                $storage->sftp_storage_folder        = SnapIO::safePath($sftp_storage_folder);
                $storage->sftp_disable_chunking_mode = filter_input(INPUT_POST, 'sftp_disable_chunking_mode', FILTER_VALIDATE_BOOLEAN);
                break;
            case DUP_PRO_Storage_Types::Dropbox:
                $storage->dropbox_storage_folder = SnapIO::safePath(sanitize_text_field($_REQUEST['_dropbox_storage_folder']));
                break;
            case DUP_PRO_Storage_Types::GDrive:
                $storage->gdrive_storage_folder = SnapIO::safePath(sanitize_text_field($_REQUEST['_gdrive_storage_folder']));
                break;
            case DUP_PRO_Storage_Types::S3:
                $storage->s3_storage_folder   = SnapIO::safePath(sanitize_text_field($_REQUEST['_s3_storage_folder']));
                $storage->s3_ACL_full_control = filter_input(INPUT_POST, 's3_ACL_full_control', FILTER_VALIDATE_BOOLEAN);
                break;
            case DUP_PRO_Storage_Types::OneDrive:
                $onedrive_storage_folder = SnapIO::safePath(sanitize_text_field($_REQUEST['_onedrive_storage_folder']));
                if ($storage->onedrive_storage_folder != $onedrive_storage_folder) {
                    $storage->onedrive_storage_folder    = $onedrive_storage_folder;
                    $storage->onedrive_storage_folder_id = '';
                }
                $storage->onedrive_max_files = intval($_REQUEST['onedrive_max_files']);
                break;
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                $onedrive_storage_folder = SnapIO::safePath(sanitize_text_field($_REQUEST['_onedrive_msgraph_storage_folder']));
                if ($storage->onedrive_storage_folder != $onedrive_storage_folder) {
                    $storage->onedrive_storage_folder    = $onedrive_storage_folder;
                    $storage->onedrive_storage_folder_id = '';
                }
                $storage->onedrive_max_files = intval($_REQUEST['onedrive_msgraph_max_files']);
                break;
        }

        if ($saveStorage) {
            $storage->save();
        }

        if (is_null($success_message) && is_null($error_message)) {
            $success_message = __('Storage Provider Updated.', 'duplicator-pro');
        }

        return [
            "storage_id"      => $storage_id,
            "storage"         => $storage,
            "error_message"   => $error_message,
            "success_message" => $success_message
        ];
    }

    /**
     * Save storage
     *
     * @return array{storage_id: int, storage: DUP_PRO_Storage_Entity, error_message: ?string, success_message: ?string}
     */
    public function actionEditCopyStorage()
    {
        $error_message   = null;
        $success_message = null;
        $source_id       = isset($_REQUEST['duppro-source-storage-id']) ? $_REQUEST['duppro-source-storage-id'] : -1;
        $storage_id      = isset($_REQUEST['storage_id']) ? intval($_REQUEST['storage_id']) : -1;
        $storage         = ($storage_id == -1) ? new DUP_PRO_Storage_Entity() : DUP_PRO_Storage_Entity::get_by_id($storage_id);

        if ($source_id != -1) {
            $storage->copy_from_source_id($source_id);
            $storage->save();

            $success_message = __('Storage Copied Successfully.', 'duplicator-pro');
        }

        return [
            "storage_id"      => $storage_id,
            "storage"         => $storage,
            "error_message"   => $error_message,
            "success_message" => $success_message
        ];
    }

    /**
     * Revoke gdrive access
     *
     * @return array{storage_id: int, storage: DUP_PRO_Storage_Entity, error_message: ?string, success_message: ?string}
     */
    public function actionGdriveRevokeAccess()
    {
        $error_message   = null;
        $success_message = null;
        $storage_id      = isset($_REQUEST['storage_id']) ? intval($_REQUEST['storage_id']) : -1;
        $storage         = ($storage_id == -1) ? new DUP_PRO_Storage_Entity() : DUP_PRO_Storage_Entity::get_by_id($storage_id);

        $ret_gdrive_refresh_token_revoke = false;
        $ret_gdrive_access_token_revoke  = false;

        $google_client = DUP_PRO_GDrive_U::get_raw_google_client($storage->gdrive_client_number);

        if (
            !empty($storage->gdrive_refresh_token) &&
            !($ret_gdrive_refresh_token_revoke = $google_client->revokeToken($storage->gdrive_refresh_token))
        ) {
            DUP_PRO_Log::trace("Problem revoking Google Drive refresh token");
        }

        $accessTokenObj = json_decode($storage->gdrive_access_token_set_json);
        if (is_object($accessTokenObj) && property_exists($accessTokenObj, 'access_token')) {
            $gdrive_access_token = $accessTokenObj->access_token;
        } else {
            $gdrive_access_token = false;
        }

        if (!empty($gdrive_access_token) && !($ret_gdrive_access_token_revoke = $google_client->revokeToken($gdrive_access_token))) {
            DUP_PRO_Log::trace("Problem revoking Google Drive access token ");
        }

        if (!$ret_gdrive_refresh_token_revoke && !$ret_gdrive_access_token_revoke) {
            $error_message = __('Google Drive refresh token and access token can\'t be unauthorized.', 'duplicator-pro');
        } elseif (!$ret_gdrive_refresh_token_revoke) {
            $error_message = __('Google Drive refresh token can\'t be unauthorized.', 'duplicator-pro');
        } elseif (!$ret_gdrive_access_token_revoke) {
            $error_message = __('Google Drive access token can\'t be unauthorized.', 'duplicator-pro');
        } else {
            $success_message = __('Google Drive has unauthorized successfully.', 'duplicator-pro');
        }

        $storage->gdrive_access_token_set_json = '';
        $storage->gdrive_refresh_token         = '';
        $storage->gdrive_authorization_state   = DUP_PRO_GDrive_Authorization_States::Unauthorized;
        $storage->gdrive_client_number         = DUP_PRO_Storage_Entity::GDRIVE_CLIENT_LATEST;
        $storage->save();

        return [
            "storage_id"      => $storage_id,
            "storage"         => $storage,
            "error_message"   => $error_message,
            "success_message" => $success_message
        ];
    }

    /**
     * Revoke dropbox access
     *
     * @return array{storage_id: int, storage: DUP_PRO_Storage_Entity, error_message: ?string, success_message: ?string}
     */
    public function actionDropboxRevokeAccess()
    {
        $error_message   = null;
        $success_message = null;
        $storage_id      = isset($_REQUEST['storage_id']) ? intval($_REQUEST['storage_id']) : -1;
        $storage         = ($storage_id == -1) ? new DUP_PRO_Storage_Entity() : DUP_PRO_Storage_Entity::get_by_id($storage_id);

        $dropbox_client = $storage->get_dropbox_client();
        if ($dropbox_client->revokeToken() === false) {
            DUP_PRO_Log::trace("Problem revoking Dropbox access token");
            $error_message = __('DropBox can\'t be unauthorized.', 'duplicator-pro');
        } else {
            $success_message = __('DropBox has unauthorized successfully.', 'duplicator-pro');
        }

        $storage->dropbox_access_token        = '';
        $storage->dropbox_access_token_secret = '';
        $storage->dropbox_v2_access_token     = '';
        $storage->dropbox_authorization_state = DUP_PRO_Dropbox_Authorization_States::Unauthorized;
        $storage->save();

        return [
            "storage_id"      => $storage_id,
            "storage"         => $storage,
            "error_message"   => $error_message,
            "success_message" => $success_message
        ];
    }

    /**
     * Revoke OneDrive access
     *
     * @return array{storage_id: int, storage: DUP_PRO_Storage_Entity, error_message: ?string, success_message: ?string}
     */
    public function actionOneDriveRevokeAccess()
    {
        $error_message   = null;
        $success_message = null;
        $storage_id      = isset($_REQUEST['storage_id']) ? intval($_REQUEST['storage_id']) : -1;
        $storage         = ($storage_id == -1) ? new DUP_PRO_Storage_Entity() : DUP_PRO_Storage_Entity::get_by_id($storage_id);

        $storage->onedrive_endpoint_url           = '';
        $storage->onedrive_resource_id            = '';
        $storage->onedrive_access_token           = '';
        $storage->onedrive_refresh_token          = '';
        $storage->onedrive_token_obtained         = '';
        $storage->onedrive_user_id                = '';
        $storage->onedrive_storage_folder         = '';
        $storage->onedrive_max_files              = 10;
        $storage->onedrive_storage_folder_id      = '';
        $storage->onedrive_authorization_state    = DUP_PRO_OneDrive_Authorization_States::Unauthorized;
        $storage->onedrive_storage_folder_web_url = '';
        $storage->save();

        $success_message = __('OneDrive has unauthorized successfully.', 'duplicator-pro');

        return [
            "storage_id"      => $storage_id,
            "storage"         => $storage,
            "error_message"   => $error_message,
            "success_message" => $success_message
        ];
    }

    /**
     * Handles Dropbox authorization, retrieves tokens, saves storage etc...
     * Returns [$error_message, $success_message]
     *
     * @param DUP_PRO_Storage_Entity $storage Storage object that will be updated
     *
     * @return array<int, string|null>
     */
    public static function handleDropboxAuth(DUP_PRO_Storage_Entity $storage)
    {
        $error_message   = null;
        $success_message = null;

        if (
            $_REQUEST['storage_type'] != DUP_PRO_Storage_Types::Dropbox ||
            $storage->dropbox_authorization_state != DUP_PRO_Dropbox_Authorization_States::Unauthorized ||
            empty($_REQUEST['dropbox-auth-code'])
        ) {
            return [$error_message, $success_message];
        }

        try {
            $dropbox_client_auth_code = sanitize_text_field($_REQUEST['dropbox-auth-code']);
            $dropbox_client           = DUP_PRO_Storage_Entity::get_raw_dropbox_client(false);
            $v2_access_token          = $dropbox_client->authenticate($dropbox_client_auth_code);

            if ($v2_access_token !== false) {
                $storage->dropbox_v2_access_token = $v2_access_token;

                DUP_PRO_Log::trace("Set Dropbox access token to {$storage->dropbox_v2_access_token}");

                $storage->dropbox_authorization_state = DUP_PRO_Dropbox_Authorization_States::Authorized;
                $storage->save();

                $success_message = __('Dropbox is connected successfully and Storage Provider Updated.', 'duplicator-pro');
            } else {
                $error_message = __("Couldn't connect. Dropbox access token not found.", 'duplicator-pro');
            }
        } catch (Exception $ex) {
            $error_message = sprintf(__('Problem retrieving Dropbox access token [%s] Please try again!', 'duplicator-pro'), $ex->getMessage());
        }

        return [$error_message, $success_message];
    }

    /**
     * Handles GDrive authorization, retrieves tokens, saves storage etc...
     * Returns [$error_message, $success_message]
     *
     * @param DUP_PRO_Storage_Entity $storage Storage object that will be updated
     *
     * @return array<int, string|null>
     */
    public static function handleGDriveAuth(DUP_PRO_Storage_Entity $storage)
    {
        $error_message   = null;
        $success_message = null;

        if (
            $_REQUEST['storage_type'] != DUP_PRO_Storage_Types::GDrive ||
            $storage->gdrive_authorization_state != DUP_PRO_GDrive_Authorization_States::Unauthorized ||
            empty($_REQUEST['gdrive-auth-code'])
        ) {
            return [$error_message, $success_message];
        }

        try {
            $google_client_auth_code  = sanitize_text_field($_REQUEST['gdrive-auth-code']);
            $google_client            = DUP_PRO_GDrive_U::get_raw_google_client();
            $gdrive_token_pair_string = $google_client->authenticate($google_client_auth_code);

            $gdrive_token_pair = json_decode($gdrive_token_pair_string, true);

            DUP_PRO_Log::traceObject('Token pair from authorization', $gdrive_token_pair);

            if (!isset($gdrive_token_pair['refresh_token'])) {
                $error_message = __("Couldn't connect. Google Drive refresh token not found.", 'duplicator-pro');
                return [$error_message, $success_message];
            }

            if (!isset($gdrive_token_pair['scope'])) {
                $error_message = __("Couldn't connect. Google Drive scopes not found.", 'duplicator-pro');
                return [$error_message, $success_message];
            }

            if (!DUP_PRO_GDrive_U::checkScopes($gdrive_token_pair['scope'])) {
                $error_message = __("Authorization failed. You did not allow all required permissions. " .
                    "Try again and make sure that you checked all checkboxes.", 'duplicator-pro');
                return [$error_message, $success_message];
            }

            $storage->gdrive_refresh_token         = $gdrive_token_pair['refresh_token'];
            $storage->gdrive_access_token_set_json = $google_client->getAccessToken(); //$gdrive_token_pair['access_token'];

            DUP_PRO_Log::trace("Set refresh token to {$storage->gdrive_refresh_token}");
            DUP_PRO_Log::trace("Set access token to {$storage->gdrive_access_token_set_json}");

            $storage->gdrive_authorization_state = DUP_PRO_GDrive_Authorization_States::Authorized;
            $storage->gdrive_client_number       = DUP_PRO_Storage_Entity::GDRIVE_CLIENT_LATEST;
            $storage->save();

            $success_message = __('Google Drive is connected successfully and Storage Provider Updated.', 'duplicator-pro');
        } catch (Exception $ex) {
            $error_message = sprintf(__('Problem retrieving Google refresh and access tokens [%s] Please try again!', 'duplicator-pro'), $ex->getMessage());
        }

        return [$error_message, $success_message];
    }

    /**
     * Handles OneDrive authorization, retrieves tokens, saves storage etc...
     * Returns [$error_message, $success_message]
     *
     * @param DUP_PRO_Storage_Entity $storage Storage object that will be updated
     *
     * @return array<int, string|null>
     */
    public static function handleOneDriveAuth(DUP_PRO_Storage_Entity $storage)
    {
        $error_message   = null;
        $success_message = null;

        if (
            ($_REQUEST['storage_type'] == DUP_PRO_Storage_Types::OneDrive || $_REQUEST['storage_type'] == DUP_PRO_Storage_Types::OneDriveMSGraph) &&
            $storage->onedrive_authorization_state == DUP_PRO_OneDrive_Authorization_States::Unauthorized &&
            (!empty($_REQUEST['onedrive-auth-code']) || !empty($_REQUEST['onedrive-msgraph-auth-code']))
        ) {
            $use_msgraph_api = $_REQUEST['storage_type'] == DUP_PRO_Storage_Types::OneDriveMSGraph;
            if ((isset($_REQUEST['onedrive-is-business']) && $_REQUEST['onedrive-is-business']) || $use_msgraph_api) {
                $onedrive_auth_client = DUP_PRO_Onedrive_U::get_onedrive_client_from_state(
                    (object) array(
                        'redirect_uri' => DUP_PRO_OneDrive_Config::ONEDRIVE_REDIRECT_URI,
                        'token' => null
                    ),
                    $use_msgraph_api
                );

                $access_token_args = array (
                    'code' => sanitize_text_field($use_msgraph_api ? $_REQUEST['onedrive-msgraph-auth-code'] : $_REQUEST['onedrive-auth-code']),
                    'grant_type' => 'authorization_code'
                );
                if (isset($_REQUEST['onedrive-is-business']) && $_REQUEST['onedrive-is-business']) {
                    $onedrive_auth_client->setBusinessMode();
                    $access_token_args['resource'] = DUP_PRO_OneDrive_Config::MICROSOFT_GRAPH_ENDPOINT;
                }
                $onedrive_auth_client->obtainAccessToken(
                    DUP_PRO_OneDrive_Config::ONEDRIVE_CLIENT_SECRET,
                    $access_token_args
                );

                $onedrive_client_state = $onedrive_auth_client->getState();
                DUP_PRO_Log::traceObject("OneDrive Client State:", $onedrive_client_state);
                $error_message = DUP_PRO_Onedrive_U::getErrorMessageBasedOnClientState($onedrive_client_state);
                if ($error_message !== null) {
                    return [$error_message, $success_message];
                }

                $onedrive_info = $onedrive_auth_client->getServiceInfo();
                if (isset($_REQUEST['onedrive-is-business']) && $_REQUEST['onedrive-is-business']) {
                    $onedrive_auth_client->obtainAccessToken(
                        DUP_PRO_OneDrive_Config::ONEDRIVE_CLIENT_SECRET,
                        array(
                            'resource' => $onedrive_info['resource_id'],
                            'refresh_token' => $onedrive_auth_client->getState()->token->data->refresh_token,
                            'grant_type' => 'refresh_token'
                        )
                    );
                }
                $storage->onedrive_endpoint_url = $onedrive_info['endpoint_url'];
                $storage->onedrive_resource_id  = $onedrive_info['resource_id'];
            } else {
                $onedrive_auth_code   = !empty($_REQUEST['onedrive-auth-code'])
                                        ? sanitize_text_field($_REQUEST['onedrive-auth-code'])
                                        : sanitize_text_field($_REQUEST['onedrive-msgraph-auth-code']);
                $onedrive_auth_client = DUP_PRO_Onedrive_U::get_onedrive_client_from_state(
                    (object) array(
                        'redirect_uri' => DUP_PRO_OneDrive_Config::ONEDRIVE_REDIRECT_URI,
                        'token' => null
                    )
                );
                $onedrive_auth_client->obtainAccessToken(
                    DUP_PRO_OneDrive_Config::ONEDRIVE_CLIENT_SECRET,
                    array(
                        'code' => $onedrive_auth_code,
                        'grant_type' => 'authorization_code'
                    )
                );
            }
            $onedrive_client_state = $onedrive_auth_client->getState();
            $error_message         = DUP_PRO_Onedrive_U::getErrorMessageBasedOnClientState($onedrive_client_state);
            if ($error_message !== null) {
                return [$error_message, $success_message];
            }

            $storage->storage_type                 = DUP_PRO_Storage_Types::OneDrive;
            $storage->onedrive_access_token        = $onedrive_client_state->token->data->access_token;
            $storage->onedrive_refresh_token       = $onedrive_client_state->token->data->refresh_token;
            $storage->onedrive_user_id             = property_exists($onedrive_client_state->token->data, "user_id")
                ? $onedrive_client_state->token->data->user_id
                : '';
            $storage->onedrive_token_obtained      = $onedrive_client_state->token->obtained;
            $storage->onedrive_authorization_state = DUP_PRO_OneDrive_Authorization_States::Authorized;
            $storage->save();

            $success_message = __('OneDrive is connected successfully and Storage Provider Updated.', 'duplicator-pro');
        }
        return [$error_message, $success_message];
    }
}
