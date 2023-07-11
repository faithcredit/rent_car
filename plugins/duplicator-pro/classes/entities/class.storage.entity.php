<?php

/**
 * Storage entity layer
 *
 * Standard: Missing
 *
 * @package    DUP_PRO
 * @subpackage classes/entities
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      3.0.0
 *
 * @todo Finish Docs
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Package\Storage\StorageUploadChunkFiles;
use Duplicator\Utils\Crypt\CryptBlowfish;
use Duplicator\Utils\Exceptions\ChunkingTimeoutException;
use Duplicator\Utils\SFTPAdapter;

// For those storage types that do not require any configuration ahead of time
abstract class DUP_PRO_Storage_Types
{
    const Local           = 0;
    const Dropbox         = 1;
    const FTP             = 2;
    const GDrive          = 3;
    const S3              = 4;
    const SFTP            = 5;
    const OneDrive        = 6;
    const OneDriveMSGraph = 7;
}

// For those storage types that do not require any configuration ahead of time
abstract class DUP_PRO_Virtual_Storage_IDs
{
    const Default_Local = -2;
}

// Important: Should be aligned with the states in the storage edit view
abstract class DUP_PRO_Dropbox_Authorization_States
{
    const Unauthorized = 0;
    const Authorized   = 4;
}

abstract class DUP_PRO_OneDrive_Authorization_States
{
    const Unauthorized = 0;
    const Authorized   = 1;
}

abstract class DUP_PRO_GDrive_Authorization_States
{
    const Unauthorized = 0;
    const Authorized   = 1;
}

/**
 * @copyright 2016 Snap Creek LLC
 */
class DUP_PRO_Storage_Entity extends DUP_PRO_JSON_Entity_Base
{
    const LOCAL_STORAGE_CHUNK_SIZE_IN_MB = 16;

    public $name         = '';
    public $notes        = '';
    public $storage_type = DUP_PRO_Storage_Types::Local;
    public $editable     = true;
    // LOCAL FIELDS
    public $local_storage_folder    = '';
    public $local_max_files         = 10;
    public $local_filter_protection = true;
    public $purge_package_record    = true;
    // DROPBOX FIELDS
    public $dropbox_access_token        = '';
    public $dropbox_access_token_secret = '';
    public $dropbox_v2_access_token     = '';
    //to use different name for OAuth 2 token
    public $dropbox_storage_folder      = '';
    public $dropbox_max_files           = 10;
    public $dropbox_authorization_state = DUP_PRO_Dropbox_Authorization_States::Unauthorized;
    //ONEDRIVE FIELDS
    public $onedrive_endpoint_url           = '';
    public $onedrive_resource_id            = '';
    public $onedrive_access_token           = '';
    public $onedrive_refresh_token          = '';
    public $onedrive_token_obtained         = '';
    public $onedrive_user_id                = '';
    public $onedrive_storage_folder         = '';
    public $onedrive_max_files              = 10;
    public $onedrive_storage_folder_id      = '';
    public $onedrive_authorization_state    = DUP_PRO_OneDrive_Authorization_States::Unauthorized;
    public $onedrive_storage_folder_web_url = '';
    // FTP FIELDS
    public $ftp_server          = '';
    public $ftp_port            = 21;
    public $ftp_username        = '';
    public $ftp_password        = '';
    public $ftp_use_curl        = false;
    public $ftp_storage_folder  = '';
    public $ftp_max_files       = 10;
    public $ftp_timeout_in_secs = 15;
    public $ftp_ssl             = false;
    public $ftp_passive_mode    = false;
    // SFTP FIELDS
    public $sftp_server                = '';
    public $sftp_port                  = 22;
    public $sftp_username              = '';
    public $sftp_password              = '';
    public $sftp_private_key           = '';
    public $sftp_private_key_password  = '';
    public $sftp_storage_folder        = '';
    public $sftp_timeout_in_secs       = 15;
    public $sftp_max_files             = 10;
    public $sftp_disable_chunking_mode = false;
    // GOOGLE DRIVE FIELDS
    public $gdrive_access_token_set_json = '';
    public $gdrive_refresh_token         = '';
    public $gdrive_storage_folder        = '';
    public $gdrive_max_files             = 10;
    public $gdrive_authorization_state   = DUP_PRO_GDrive_Authorization_States::Unauthorized;
    public $gdrive_client_number         = null;
    public $quick_connect                = false;

    // These numbers represent clients created in Google Cloud Console
    const GDRIVE_CLIENT_NATIVE  = 1; // Native client 1
    const GDRIVE_CLIENT_WEB0722 = 2; // Web client 07/2022
    const GDRIVE_CLIENT_LATEST  = 2; // Latest out of these above

    // S3 FIELDS
    public $s3_access_key;
    public $s3_bucket;
    public $s3_max_files = 10;
    public $s3_provider  = 'amazon';
    public $s3_region    = '';
    public $s3_endpoint  = '';
    public $s3_secret_key;
    public $s3_storage_class    = 'STANDARD';
    public $s3_storage_folder   = '';
    public $s3_ACL_full_control = true;
    // Version
    public $version            = '0.0.0.0';
    private $throttleDelayInUs = 0;
    // const PLACE_HOLDER                ='%domain%';

    public function __construct()
    {
        parent::__construct();
        $this->verifiers['name']       = new DUP_PRO_Required_Verifier("Name must not be blank");
        $this->name                    = DUP_PRO_U::__('New Storage');
        $this->dropbox_storage_folder  = self::get_default_storage_folder();
        $this->ftp_storage_folder      = '/' . self::get_default_storage_folder();
        $this->sftp_storage_folder     = '/';
        $this->gdrive_storage_folder   = 'Duplicator Backups/' . self::get_default_storage_folder();
        $this->s3_storage_folder       = 'Duplicator Backups/' . self::get_default_storage_folder();
        $this->onedrive_storage_folder = self::get_default_storage_folder();
        $this->throttleDelayInUs       = DUP_PRO_Global_Entity::getInstance()->getMicrosecLoadReduction();
    }

    public static function create_from_data($storage_data, $restore_id = false)
    {
        $instance               = new self();
        $instance->name         = $storage_data->name;
        $instance->notes        = $storage_data->notes;
        $instance->storage_type = $storage_data->storage_type;
        $instance->editable     = $storage_data->editable;
        // LOCAL FIELDS
        $instance->local_storage_folder    = $storage_data->local_storage_folder;
        $instance->local_max_files         = $storage_data->local_max_files;
        $instance->local_filter_protection = $storage_data->local_filter_protection;
        // DROPBOX FIELDS
        $instance->dropbox_access_token        = $storage_data->dropbox_access_token;
        $instance->dropbox_v2_access_token     = $storage_data->dropbox_v2_access_token;
        $instance->dropbox_access_token_secret = $storage_data->dropbox_access_token_secret;
        $instance->dropbox_storage_folder      = $storage_data->dropbox_storage_folder;
        $instance->dropbox_max_files           = $storage_data->dropbox_max_files;
        $instance->dropbox_authorization_state = $storage_data->dropbox_authorization_state;
        //ONEDRIVE FIELDS
        $instance->onedrive_max_files           = $storage_data->onedrive_max_files;
        $instance->onedrive_storage_folder      = $storage_data->onedrive_storage_folder;
        $instance->onedrive_access_token        = $storage_data->onedrive_access_token;
        $instance->onedrive_refresh_token       = $storage_data->onedrive_refresh_token;
        $instance->onedrive_user_id             = $storage_data->onedrive_user_id;
        $instance->onedrive_token_obtained      = $storage_data->onedrive_token_obtained;
        $instance->onedrive_authorization_state = $storage_data->onedrive_authorization_state;
        //ONEDRIVE BUSINESS FIELDS

        $instance->onedrive_endpoint_url           = isset($storage_data->onedrive_endpoint_url) ? $storage_data->onedrive_endpoint_url : '';
        $instance->onedrive_resource_id            = isset($storage_data->onedrive_resource_id) ? $storage_data->onedrive_resource_id : '';
        $instance->onedrive_storage_folder_id      = isset($storage_data->onedrive_storage_folder_id) ? $storage_data->onedrive_storage_folder_id : '';
        $instance->onedrive_storage_folder_web_url = isset($storage_data->onedrive_storage_folder_web_url) ? $storage_data->onedrive_storage_folder_web_url : '';
        // FTP FIELDS
        $instance->ftp_server          = $storage_data->ftp_server;
        $instance->ftp_port            = $storage_data->ftp_port;
        $instance->ftp_username        = $storage_data->ftp_username;
        $instance->ftp_password        = $storage_data->ftp_password;
        $instance->ftp_storage_folder  = $storage_data->ftp_storage_folder;
        $instance->ftp_max_files       = $storage_data->ftp_max_files;
        $instance->ftp_timeout_in_secs = $storage_data->ftp_timeout_in_secs;
        $instance->ftp_ssl             = $storage_data->ftp_ssl;
        $instance->ftp_passive_mode    = $storage_data->ftp_passive_mode;
        // SFTP FIELDS
        $instance->sftp_server                = $storage_data->sftp_server;
        $instance->sftp_port                  = $storage_data->sftp_port;
        $instance->sftp_username              = $storage_data->sftp_username;
        $instance->sftp_password              = $storage_data->sftp_password;
        $instance->sftp_storage_folder        = $storage_data->sftp_storage_folder;
        $instance->sftp_private_key           = $storage_data->sftp_private_key;
        $instance->sftp_private_key_password  = $storage_data->sftp_private_key_password;
        $instance->sftp_timeout_in_secs       = $storage_data->sftp_timeout_in_secs;
        $instance->sftp_max_files             = $storage_data->sftp_max_files;
        $instance->sftp_disable_chunking_mode = $storage_data->sftp_disable_chunking_mode;
        // GOOGLE DRIVE FIELDS
        $instance->gdrive_access_token_set_json = $storage_data->gdrive_access_token_set_json;
        $instance->gdrive_refresh_token         = $storage_data->gdrive_refresh_token;
        $instance->gdrive_storage_folder        = $storage_data->gdrive_storage_folder;
        $instance->gdrive_max_files             = $storage_data->gdrive_max_files;
        $instance->gdrive_authorization_state   = $storage_data->gdrive_authorization_state;
        $instance->gdrive_client_number         = $storage_data->gdrive_client_number;
        $instance->quick_connect                = $storage_data->quick_connect;
        // S3 FIELDS
        $instance->s3_access_key       = $storage_data->s3_access_key;
        $instance->s3_bucket           = $storage_data->s3_bucket;
        $instance->s3_max_files        = $storage_data->s3_max_files;
        $instance->s3_provider         = $storage_data->s3_provider;
        $instance->s3_region           = $storage_data->s3_region;
        $instance->s3_endpoint         = $storage_data->s3_endpoint;
        $instance->s3_secret_key       = $storage_data->s3_secret_key;
        $instance->s3_storage_class    = $storage_data->s3_storage_class;
        $instance->s3_storage_folder   = $storage_data->s3_storage_folder;
        $instance->s3_ACL_full_control = $storage_data->s3_ACL_full_control;
        // VERSION
        $instance->version = isset($storage_data->version) ? $storage_data->version : '0.0.0.0';
        if ($restore_id) {
            $instance->id = $storage_data->id;
        }

        return $instance;
    }

    /**
     * Init and updat storages
     *
     * @return void
     */
    public static function initDefaultStorage()
    {
        $storages = self::get_all();
        foreach ($storages as $storage) {
            if ($storage->save() == false) {
                throw new Exception('Can\'t save storage' . $storage->id);
            }
        }
    }

    /**
     *
     * @return string
     */
    public static function get_default_storage_folder()
    {
        $parsetUrl             = SnapURL::parseUrl(get_home_url());
        $parsetUrl['scheme']   = false;
        $parsetUrl['port']     = false;
        $parsetUrl['query']    = false;
        $parsetUrl['fragment'] = false;
        $parsetUrl['user']     = false;
        $parsetUrl['pass']     = false;
        $parsetUrl['path']     = preg_replace("([^\w\s\d\-_~,;:\[\]\(\)/.])", '', $parsetUrl['path']);
        return ltrim(SnapURL::buildUrl($parsetUrl), '/\\');
    }

    /**
     *
     * @return string
     */
    public function get_storage_folder()
    {
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Local:
                return $this->local_storage_folder;
            case DUP_PRO_Storage_Types::Dropbox:
                return ($this->dropbox_storage_folder == '' ? '/' : $this->dropbox_storage_folder);
            case DUP_PRO_Storage_Types::FTP:
                return ($this->ftp_storage_folder == '' ? '/' : $this->ftp_storage_folder);
            case DUP_PRO_Storage_Types::SFTP:
                return ($this->sftp_storage_folder == '' ? '/' : $this->sftp_storage_folder);
            case DUP_PRO_Storage_Types::GDrive:
                return ($this->gdrive_storage_folder == '' ? '/' : $this->gdrive_storage_folder);
            case DUP_PRO_Storage_Types::S3:
                return ($this->s3_storage_folder == '' ? '/' : $this->s3_storage_folder);
            case DUP_PRO_Storage_Types::OneDrive:
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                return ($this->onedrive_storage_folder == '' ? '/' : $this->onedrive_storage_folder);
            default:
                return '';
        }
    }

    public function copy_from_source_id($id)
    {
        $source_storage                    = self::get_by_id($id);
        $this->dropbox_access_token        = $source_storage->dropbox_access_token;
        $this->dropbox_v2_access_token     = $source_storage->dropbox_v2_access_token;
        $this->dropbox_access_token_secret = $source_storage->dropbox_access_token_secret;
        $this->dropbox_authorization_state = $source_storage->dropbox_authorization_state;
        $this->dropbox_max_files           = $source_storage->dropbox_max_files;
        $this->dropbox_storage_folder      = $source_storage->dropbox_storage_folder;
        //$this->editable;
        $this->ftp_max_files                = $source_storage->ftp_max_files;
        $this->ftp_passive_mode             = $source_storage->ftp_passive_mode;
        $this->ftp_password                 = $source_storage->ftp_password;
        $this->ftp_port                     = $source_storage->ftp_port;
        $this->ftp_server                   = $source_storage->ftp_server;
        $this->ftp_ssl                      = $source_storage->ftp_ssl;
        $this->ftp_storage_folder           = $source_storage->ftp_storage_folder;
        $this->ftp_timeout_in_secs          = $source_storage->ftp_timeout_in_secs;
        $this->ftp_username                 = $source_storage->ftp_username;
        $this->sftp_password                = $source_storage->sftp_password;
        $this->sftp_port                    = $source_storage->sftp_port;
        $this->sftp_server                  = $source_storage->sftp_server;
        $this->sftp_storage_folder          = $source_storage->sftp_storage_folder;
        $this->sftp_timeout_in_secs         = $source_storage->sftp_timeout_in_secs;
        $this->sftp_username                = $source_storage->sftp_username;
        $this->sftp_private_key             = $source_storage->sftp_private_key;
        $this->sftp_private_key_password    = $source_storage->sftp_private_key_password;
        $this->sftp_max_files               = $source_storage->sftp_max_files;
        $this->sftp_disable_chunking_mode   = $source_storage->sftp_disable_chunking_mode;
        $this->local_storage_folder         = $source_storage->local_storage_folder;
        $this->local_max_files              = $source_storage->local_max_files;
        $this->name                         = sprintf(DUP_PRO_U::__('%1$s - Copy'), $source_storage->name);
        $this->notes                        = $source_storage->notes;
        $this->storage_type                 = $source_storage->storage_type;
        $this->gdrive_access_token_set_json = $source_storage->gdrive_access_token_set_json;
        $this->gdrive_refresh_token         = $source_storage->gdrive_refresh_token;
        $this->gdrive_storage_folder        = $source_storage->gdrive_storage_folder;
        $this->gdrive_max_files             = $source_storage->gdrive_max_files;
        $this->gdrive_authorization_state   = $source_storage->gdrive_authorization_state;
        $this->gdrive_client_number         = $source_storage->gdrive_client_number;
        $this->quick_connect                = $source_storage->quick_connect;
        // S3 FIELDS
        $this->s3_storage_folder   = $source_storage->s3_storage_folder;
        $this->s3_bucket           = $source_storage->s3_bucket;
        $this->s3_access_key       = $source_storage->s3_access_key;
        $this->s3_secret_key       = $source_storage->s3_secret_key;
        $this->s3_provider         = $source_storage->s3_provider;
        $this->s3_region           = $source_storage->s3_region;
        $this->s3_endpoint         = $source_storage->s3_endpoint;
        $this->s3_storage_class    = $source_storage->s3_storage_class;
        $this->s3_max_files        = $source_storage->s3_max_files;
        $this->s3_ACL_full_control = $source_storage->s3_ACL_full_control;
        //ONEDRIVE FIELDS
        $this->onedrive_max_files           = $source_storage->onedrive_max_files;
        $this->onedrive_storage_folder      = $source_storage->onedrive_storage_folder;
        $this->onedrive_access_token        = $source_storage->onedrive_access_token;
        $this->onedrive_refresh_token       = $source_storage->onedrive_refresh_token;
        $this->onedrive_user_id             = $source_storage->onedrive_user_id;
        $this->onedrive_token_obtained      = $source_storage->onedrive_token_obtained;
        $this->onedrive_authorization_state = $source_storage->onedrive_authorization_state;
        //VERSION
        $this->version = $source_storage->version;
    }

    /**
     * Return all storages
     *
     * @return self[]
     */
    public static function get_all()
    {
        $default_local_storage = self::get_default_local_storage();
        $storages              = self::get_by_type(get_class());
        array_unshift($storages, $default_local_storage);
        if (DUP_PRO_Global_Entity::getInstance()->crypt) {
            foreach ($storages as $storage) {
                $storage->decrypt();
            }
        }

        return $storages;
    }

    /**
     * check if exists non defaults storages
     *
     * @return bool
     */
    public static function onlyDefaultStorageExists()
    {
        $storages = self::get_by_type(get_class());
        return (count($storages) == 0);
    }

    public function get_onedrive_client()
    {
        if ($this->storage_type == DUP_PRO_Storage_Types::OneDriveMSGraph) {
            $scope = DUP_PRO_OneDrive_Config::MSGRAPH_ACCESS_SCOPE;
        } else {
            $scope = !$this->onedrive_is_business() ? DUP_PRO_OneDrive_Config::ONEDRIVE_ACCESS_SCOPE :
                DUP_PRO_OneDrive_Config::ONEDRIVE_BUSINESS_ACCESS_SCOPE;
        }
        $state    = (object) array(
            'redirect_uri' => null,
            'endpoint_url' => $this->onedrive_endpoint_url,
            'resource_id'  => $this->onedrive_resource_id,
            'token' => (object) array(
                'obtained' => $this->onedrive_token_obtained,
                'data' => (object) array(
                    'token_type' => 'bearer',
                    'expires_in' => 3600,
                    'scope' => $scope,
                    'access_token' => $this->onedrive_access_token,
                    'refresh_token' => $this->onedrive_refresh_token,
                    'user_id' => $this->onedrive_user_id
                )
            )
        );
        $onedrive = DUP_PRO_Onedrive_U::get_onedrive_client_from_state($state, $this->storage_type == DUP_PRO_Storage_Types::OneDriveMSGraph);
        if ($onedrive->getAccessTokenStatus() < 0) {
            $onedrive->renewAccessToken(DUP_PRO_OneDrive_Config::ONEDRIVE_CLIENT_SECRET);
            $state = $onedrive->getState();
            if (isset($this->onedrive_refresh_token) && isset($state->token->data->access_token)) {
                $this->onedrive_token_obtained = time();
                $this->onedrive_refresh_token  = $state->token->data->refresh_token; // @phpstan-ignore-line
                $this->onedrive_access_token   = $state->token->data->access_token;
                $this->save();
            } else {
                $errorMessage = "Your OneDrive Access token can't be renewed";
                error_log($errorMessage);
                DUP_PRO_Log::traceError($errorMessage);
                throw new Exception($errorMessage);
            }
        }

        return $onedrive;
    }

    public function onedrive_is_business()
    {
        return !empty($this->onedrive_endpoint_url) && !empty($this->onedrive_resource_id);
    }

    public function s3_is_amazon()
    {
        if ((empty($this->s3_provider) || (!empty($this->s3_provider) && 'amazon' == $this->s3_provider))) {
            return true;
        } else {
            return false;
        }
    }

    public function get_onedrive_storage_folder()
    {
        $onedrive_folder_id = $this->onedrive_storage_folder_id;
        $onedrive           = $this->get_onedrive_client();
        if (!$onedrive_folder_id) {
            $onedrive_folder                  = $this->create_onedrive_folder();
            $this->onedrive_storage_folder_id = $onedrive_folder->getId();
            $this->save();
        } else {
            try {
                $onedrive_folder_candidate = $onedrive->fetchDriveItem($onedrive_folder_id);
                $onedrive_folder           = $onedrive_folder_candidate;
            } catch (Exception $exception) {
                $exception_message = $exception->getMessage();
                if (
                    strpos($exception_message, "Item does not exist") !== false
                    || strpos($exception_message, "The resource could not be found") !== false
                ) {
                    $onedrive_folder                  = $this->create_onedrive_folder();
                    $this->onedrive_storage_folder_id = $onedrive_folder->getId();
                    $this->save();
                } else {
                    throw $exception;
                }
            }
        }

        return $onedrive_folder;
    }

    public function onedrive_folder_exists()
    {
        $onedrive_folder_id = $this->onedrive_storage_folder_id;
        $onedrive           = $this->get_onedrive_client();
        if (!$onedrive_folder_id) {
            return false;
        } else {
            $onedrive_folder_candidate = $onedrive->fetchDriveItem($onedrive_folder_id);
            return ($onedrive_folder_candidate) ? true : false;
        }
    }

    public function create_onedrive_folder()
    {
        $onedrive             = $this->get_onedrive_client();
        $parent               = null;
        $current_search_item  = $onedrive->fetchRoot();
        $create_folder        = true;
        $storage_folders_tree = explode("/", $this->onedrive_storage_folder);
        foreach ($storage_folders_tree as $folder) {
            $child_items = $current_search_item->fetchChildDriveItems();
            DUP_PRO_Log::traceObject("childs", $child_items);
            DUP_PRO_Log::trace("Checking $folder");
            if (!empty($folder)) {
                if (!empty($child_items)) {
                    foreach ($child_items as $item) {
                        if ($item->isFolder()) {
                            $name = $item->getName();
                            DUP_PRO_Log::trace("$folder <===> $name");
                            if ($name == $folder) {
                                $current_search_item = $item;
                                $create_folder       = false;
                                break;
                            }
                        }
                    }
                }
                if ($create_folder) {
                    $new_folder          = $current_search_item->createFolder($folder);
                    $current_search_item = $new_folder;
                }
            }
            $create_folder = true;
        }
        $parent = $current_search_item;
        return $parent;
    }

    public function purge_old_onedrive_packages($onedrive)
    {
        DUP_PRO_Log::trace("Starting purging of old packages in OneDrive");
        $global            = DUP_PRO_Global_Entity::getInstance();
        $storage_folder_id = $this->onedrive_storage_folder_id;
        $package_items     = $onedrive->fetchDriveItems($storage_folder_id);
        $archives          = array();
        $installers        = array();
        foreach ($package_items as $item) {
            $name = $item->getName();
            if (DUP_PRO_STR::endsWith($name, "_{$global->installer_base_name}")) {
                $installers[] = $item;
            } elseif (DUP_PRO_STR::endsWith($name, '_archive.zip') || DUP_PRO_STR::endsWith($name, '_archive.daf')) {
                $archives[] = $item;
            }
        }

        $complete_packages = array();
        foreach ($archives as $archive) {
            //$archive_name = pathinfo($archive->getName())["filename"];
            $pathinfo     = pathinfo($archive->getName());
            $archive_name = $pathinfo["filename"];
            DUP_PRO_Log::trace($archive_name);
            $archive_name = str_replace('_archive', '', $archive_name);
            foreach ($installers as $installer) {
                //$installer_name = pathinfo($installer->getName())["filename"];
                $pathinfo = pathinfo($installer->getName());
                //["filename"];
                $installer_name = $pathinfo["filename"];
                DUP_PRO_Log::trace($installer_name);
                $installer_name = str_replace('_installer', '', $installer_name);
                if ($archive_name == $installer_name) {
                    $complete_packages[] = array(
                        "archive_id"    => $archive->getId(),
                        "installer_id"  => $installer->getId(),
                        "created_time"  => $archive->getCreatedTime(),
                    );
                    DUP_PRO_Log::trace(print_r($archive, true));
                }
            }
        }

        if ($this->onedrive_max_files > 0) {
            $num_archives           = count($complete_packages);
            $num_archives_to_delete = $num_archives - $this->onedrive_max_files;
            DUP_PRO_Log::trace("Num archives files to delete={$num_archives_to_delete} since there are {$num_archives} on the drive and max files={$this->onedrive_max_files}");
            $retsort = usort($complete_packages, array(__CLASS__, 'onedrive_compare_file_dates'));
            $index   = 0;
            while ($index < $num_archives_to_delete) {
                $old_package = $complete_packages[$index];
                DUP_PRO_Log::trace("Deleting old package created on " . $old_package['created_time']);
                $onedrive->deleteDriveItem($old_package["archive_id"]);
                $onedrive->deleteDriveItem($old_package["installer_id"]);
                $index++;
            }
        }
    }

    public function get_sanitized_storage_folder()
    {
        $storage_folders = explode("/", $this->get_storage_folder());
        foreach ($storage_folders as $i => $folder) {
            $storage_folders[$i] = rawurlencode($folder);
        }
        if (end($storage_folders) != "") {
            $storage_folders[] = "";
        }

        return implode("/", $storage_folders);
    }

    public function get_dropbox_client($full_access = false)
    {
        $global        = DUP_PRO_Global_Entity::getInstance();
        $configuration = self::get_dropbox_api_key_secret();
        if ($full_access) {
            $configuration['app_full_access'] = true;
        }
        // Note it's possible dropbox is in disabled mode but we are still constructing it.  Should have better error handling
        $use_curl     = ($global->dropbox_transfer_mode == DUP_PRO_Dropbox_Transfer_Mode::cURL);
        $dropbox      = new DUP_PRO_DropboxV2Client($configuration, 'en', $use_curl);
        $access_token = $this->get_dropbox_combined_access_token($global->dropbox_transfer_mode === DUP_PRO_Dropbox_Transfer_Mode::cURL);
        $dropbox->SetAccessToken($access_token);
        return $dropbox;
    }

    public static function get_dropbox_api_key_secret()
    {
        $dk   = self::get_dk1();
        $dk   = self::get_dk2() . $dk;
        $akey = CryptBlowfish::decrypt('EQNJ53++6/40fuF5ke+IaQ==', $dk);
        $asec = CryptBlowfish::decrypt('ui25chqoBexPt6QDi9qmGg==', $dk);
        $akey = trim($akey);
        $asec = trim($asec);
        if (($akey != $asec) || ($akey != "fdda100")) {
            $akey = self::get_ak1() . self::get_ak2();
            $asec = self::get_as1() . self::get_as2();
        }


        $configuration = array('app_key' => $asec, 'app_secret' => $akey);
        return $configuration;
    }

    public static function get_raw_dropbox_client($full_access = false)
    {
        /* @var $global DUP_PRO_Global_Entity */
        $global = DUP_PRO_Global_Entity::getInstance();
        // $dk = self::get_dk1();
        // $dk = self::get_dk2() . $dk;
        // $akey = CryptBlowfish::decrypt('EQNJ53++6/40fuF5ke+IaQ==', $dk);
        // $asec = CryptBlowfish::decrypt('ui25chqoBexPt6QDi9qmGg==', $dk);
        // $akey = trim($akey);
        // $asec = trim($asec);
        // if (($akey != $asec) || ($akey != "fdda100"))
        // {
        //     $akey = self::get_ak1() . self::get_ak2();
        //     $asec = self::get_as1() . self::get_as2();
        // }
        // $configuration = array('app_key' => $asec, 'app_secret' => $akey);
        $configuration = self::get_dropbox_api_key_secret();
        // ob_start();
        // print_r($configuration);
        // $data=ob_get_clean();
        // file_put_contents(dirname(__FILE__) . '/configuration.log',$data,FILE_APPEND);
        if ($full_access) {
            $configuration['app_full_access'] = true;
        }

        // Note it's possible dropbox is in disabled mode but we are still constructing it.  Should have better error handling
        $use_curl = ($global->dropbox_transfer_mode == DUP_PRO_Dropbox_Transfer_Mode::cURL);
        $dropbox  = new DUP_PRO_DropboxV2Client($configuration, 'en', $use_curl);
        return $dropbox;
    }

    /**
     * Retrieves the google client based on storage and auto updates the access token if necessary
     *
     * @return ?Duplicator_Pro_Google_Client
     */
    public function get_full_google_client()
    {
        $google_client = null;
        if (!empty($this->gdrive_access_token_set_json)) {
            $google_client = DUP_PRO_GDrive_U::get_raw_google_client($this->gdrive_client_number);
            $google_client->setAccessToken($this->gdrive_access_token_set_json);
            // Reference on access/refresh token http://stackoverflow.com/questions/9241213/how-to-refresh-token-with-google-api-client
            if ($google_client->isAccessTokenExpired()) {
                DUP_PRO_Log::traceObject("Access token is expired so checking token ", $this->gdrive_refresh_token);
                $google_client->refreshToken($this->gdrive_refresh_token);
                // getAccessToken return json encoded value of access token and other stuff
                $gdrive_access_token_set_json = $google_client->getAccessToken();
                if ($gdrive_access_token_set_json != null) {
                    $this->gdrive_access_token_set_json = $gdrive_access_token_set_json;
                    DUP_PRO_Log::trace("Retrieved acess token set from google: {$this->gdrive_access_token_set_json}");
                    $this->save();
                } else {
                    DUP_PRO_Log::trace("Can't retrieve access token!");
                    $google_client = null;
                }
            } else {
                DUP_PRO_Log::trace("Access token ISNT expired");
            }
        } else {
            DUP_PRO_Log::trace("Access token not set!");
        }

        return $google_client;
    }

    public function get_full_s3_client()
    {
        return DUP_PRO_S3_U::get_s3_client($this->s3_region, $this->s3_access_key, $this->s3_secret_key, $this->s3_endpoint);
    }

    /**
     *
     * @var null|int used in  delete_by_id_callback function
     */
    private static $currentDeleteStorageIdCallback = null;

    /**
     *
     * @param DUP_PRO_Package $package
     *
     * @return void
     */
    public static function delete_by_id_callback(DUP_PRO_Package $package)
    {
        foreach ($package->upload_infos as $key => $upload_info) {
            if ($upload_info->storage_id == self::$currentDeleteStorageIdCallback) {
                DUP_PRO_Log::traceObject("deleting uploadinfo from package $package->ID", $upload_info);
                unset($package->upload_infos[$key]);
                $package->save();
                break;
            }
        }
    }

    /**
     * Removes storage from given schedule
     *
     * @param DUP_PRO_Schedule_Entity $schedule
     *
     * @return void
     */
    public static function remove_storage_from_schedule_callback($schedule)
    {
        if (($key = array_search(self::$currentDeleteStorageIdCallback, $schedule->storage_ids)) !== false) {
            //use array_splice() instead of unset() to reset keys
            array_splice($schedule->storage_ids, $key, 1);
            if (count($schedule->storage_ids) === 0) {
                $schedule->active = false;
            }
            $schedule->save();
        }
    }

    /**
     * Deletes the storage by id
     *
     * @param $storage_id
     *
     * @return void
     */
    public static function delete_by_id($storage_id)
    {
        self::$currentDeleteStorageIdCallback = $storage_id;
        DUP_PRO_Package::by_status_callback(array(__CLASS__, 'delete_by_id_callback'));
        DUP_PRO_Schedule_Entity::run_on_all(array(__CLASS__, 'remove_storage_from_schedule_callback'));
        self::$currentDeleteStorageIdCallback = null;
        parent::delete_by_id_base($storage_id);
    }

    /**
     * Get storage objct by ID
     *
     * @param int  $id
     * @param bool $decrypt
     *
     * @return null|self return storage or null if don't exists
     */
    public static function get_by_id($id, $decrypt = true)
    {
        static $cache = array();

        $cacheId = 'id_' . $id . ($decrypt ? '_d' : '_nd');
        if (!isset($cache[$cacheId])) {
            if ($id == DUP_PRO_Virtual_Storage_IDs::Default_Local) {
                return self::get_default_local_storage();
            }

            $storage = self::get_by_id_and_type($id, get_class());
            if ($storage != null) {
                $global = DUP_PRO_Global_Entity::getInstance();
                if ($global->crypt && $decrypt) {
                    $storage->decrypt();
                }
            }
            $cache[$cacheId] = $storage;
        }

        return $cache[$cacheId];
    }

    public static function is_exist($id)
    {
        if (DUP_PRO_Virtual_Storage_IDs::Default_Local == $id) {
            return true;
        }
        return self::is_exist_by_id_and_type($id, get_class());
    }

    public function get_dropbox_combined_access_token($use_curl = true)
    {
        $access_token      = array();
        $access_token['t'] = $this->dropbox_access_token;
        $access_token['s'] = $this->dropbox_access_token_secret;
        /* if dropbox_access_token and dropbox_access_token_secret is not empty, but dropbox_v2_access_token is empty, that means it's auth1, then we try to get v2_access_token from it */
        if (!empty($this->dropbox_access_token) && !empty($this->dropbox_access_token_secret) && empty($this->dropbox_v2_access_token)) {
            require_once(DUPLICATOR____PATH . '/lib/DropPHP/DropboxClient.php');
            $configuration = self::get_dropbox_api_key_secret();
            $dropbox_v1    = new DUP_PRO_DropboxClient($configuration, 'en', $use_curl);
            $dropbox_v1->SetAccessToken($access_token);
            $response = $dropbox_v1->token_from_oauth1();
            /*
              https://www.dropbox.com/developers-v1/core/docs#oa2-from-oa1
              return sample
              {"access_token": "ABCDEFG", token_type: "bearer"}
             */
            if (isset($response->access_token)) {
                $this->dropbox_v2_access_token = $response->access_token;
            }
            $this->save();
        }
        $access_token['v2_access_token'] = $this->dropbox_v2_access_token;
        return $access_token;
    }

    private static function get_dk1()
    {
        return 'y8!!';
    }

    private static function get_dk2()
    {
        return '32897';
    }

    public function process_package(DUP_PRO_Package $package, DUP_PRO_Package_Upload_Info $upload_info)
    {
        /* @var $package DUP_PRO_Package */
        $package->active_storage_id = $this->id;
        $storage_type_string        = strtoupper($this->get_storage_type_string());
        DUP_PRO_Log::infoTrace("** $storage_type_string [Name: $this->name] [ID: $package->active_storage_id] **");
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Dropbox:
                $this->copy_to_dropbox($package, $upload_info);
                break;
            case DUP_PRO_Storage_Types::GDrive:
                $this->copy_to_gdrive($package, $upload_info);
                break;
            case DUP_PRO_Storage_Types::FTP:
                $this->copy_to_ftp($package, $upload_info);
                break;
            case DUP_PRO_Storage_Types::SFTP:
                $this->copy_to_sftp($package, $upload_info);
                break;
            case DUP_PRO_Storage_Types::Local:
                $this->copy_to_local($package, $upload_info);
                break;
            case DUP_PRO_Storage_Types::S3:
                $this->copy_to_s3($package, $upload_info);
                break;
            case DUP_PRO_Storage_Types::OneDrive:
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                $this->copy_to_onedrive($package, $upload_info);
                break;
            default:
                DUP_PRO_Log::traceError("Invalid storage type");
        }
    }

    public function encrypt()
    {
        if (!empty($this->dropbox_access_token)) {
            $this->dropbox_access_token = CryptBlowfish::encrypt($this->dropbox_access_token);
        }

        if (!empty($this->dropbox_v2_access_token)) {
            $this->dropbox_v2_access_token = CryptBlowfish::encrypt($this->dropbox_v2_access_token);
        }

        if (!empty($this->dropbox_access_token_secret)) {
            $this->dropbox_access_token_secret = CryptBlowfish::encrypt($this->dropbox_access_token_secret);
        }

        if (!empty($this->gdrive_access_token_set_json)) {
            $this->gdrive_access_token_set_json = CryptBlowfish::encrypt($this->gdrive_access_token_set_json);
        }

        if (!empty($this->gdrive_refresh_token)) {
            $this->gdrive_refresh_token = CryptBlowfish::encrypt($this->gdrive_refresh_token);
        }

        if (!empty($this->s3_access_key)) {
            $this->s3_access_key = CryptBlowfish::encrypt($this->s3_access_key);
        }

        if (!empty($this->s3_secret_key)) {
            $this->s3_secret_key = CryptBlowfish::encrypt($this->s3_secret_key);
        }

        if (!empty($this->ftp_username)) {
            $this->ftp_username = CryptBlowfish::encrypt($this->ftp_username);
        }

        if (!empty($this->ftp_password)) {
            $this->ftp_password = CryptBlowfish::encrypt($this->ftp_password);
        }

        if (!empty($this->ftp_storage_folder)) {
            $this->ftp_storage_folder = CryptBlowfish::encrypt($this->ftp_storage_folder);
        }

        if (!empty($this->sftp_username)) {
            $this->sftp_username = CryptBlowfish::encrypt($this->sftp_username);
        }

        if (!empty($this->sftp_password)) {
            $this->sftp_password = CryptBlowfish::encrypt($this->sftp_password);
        }

        if (!empty($this->sftp_private_key)) {
            $this->sftp_private_key = CryptBlowfish::encrypt($this->sftp_private_key);
        }

        if (!empty($this->sftp_private_key_password)) {
            $this->sftp_private_key_password = CryptBlowfish::encrypt($this->sftp_private_key_password);
        }

        if (!empty($this->sftp_storage_folder)) {
            $this->sftp_storage_folder = CryptBlowfish::encrypt($this->sftp_storage_folder);
        }

        if (!empty($this->onedrive_user_id)) {
            $this->onedrive_user_id = CryptBlowfish::encrypt($this->onedrive_user_id);
        }

        if (!empty($this->onedrive_access_token)) {
            $this->onedrive_access_token = CryptBlowfish::encrypt($this->onedrive_access_token);
        }

        if (!empty($this->onedrive_refresh_token)) {
            $this->onedrive_refresh_token = CryptBlowfish::encrypt($this->onedrive_refresh_token);
        }
    }

    public function decrypt()
    {
        if (!empty($this->dropbox_access_token)) {
            $this->dropbox_access_token = CryptBlowfish::decrypt($this->dropbox_access_token);
        }

        if (!empty($this->dropbox_v2_access_token)) {
            $this->dropbox_v2_access_token = CryptBlowfish::decrypt($this->dropbox_v2_access_token);
        }

        if (!empty($this->dropbox_access_token_secret)) {
            $this->dropbox_access_token_secret = CryptBlowfish::decrypt($this->dropbox_access_token_secret);
        }

        if (!empty($this->gdrive_access_token_set_json)) {
            if (!DUP_PRO_STR::contains($this->gdrive_access_token_set_json, 'access_token')) {
                $this->gdrive_access_token_set_json = CryptBlowfish::decrypt($this->gdrive_access_token_set_json);
            }
        }

        if (!empty($this->gdrive_refresh_token)) {
            $this->gdrive_refresh_token = CryptBlowfish::decrypt($this->gdrive_refresh_token);
        }

        if (!empty($this->s3_access_key)) {
            $this->s3_access_key = CryptBlowfish::decrypt($this->s3_access_key);
        }

        if (!empty($this->s3_secret_key)) {
            $this->s3_secret_key = CryptBlowfish::decrypt($this->s3_secret_key);
        }

        if ($this->version != '0.0.0.0') {
            // Anything showing as 0.0.0.0 is from v3.8.9.2 and before
            if (!empty($this->ftp_username)) {
                $this->ftp_username = CryptBlowfish::decrypt($this->ftp_username);
            }

            if (!empty($this->ftp_password)) {
                $this->ftp_password = CryptBlowfish::decrypt($this->ftp_password);
            }

            if (!empty($this->ftp_storage_folder)) {
                $this->ftp_storage_folder = CryptBlowfish::decrypt($this->ftp_storage_folder);
            }

            if (!empty($this->sftp_username)) {
                $this->sftp_username = CryptBlowfish::decrypt($this->sftp_username);
            }

            if (!empty($this->sftp_password)) {
                $this->sftp_password = CryptBlowfish::decrypt($this->sftp_password);
            }

            if (!empty($this->sftp_private_key)) {
                $this->sftp_private_key = CryptBlowfish::decrypt($this->sftp_private_key);
            }

            if (!empty($this->sftp_storage_folder)) {
                $this->sftp_storage_folder = CryptBlowfish::decrypt($this->sftp_storage_folder);
            }

            if (!empty($this->sftp_private_key_password)) {
                $this->sftp_private_key_password = CryptBlowfish::decrypt($this->sftp_private_key_password);
            }

            if (!empty($this->onedrive_user_id)) {
                $this->onedrive_user_id = CryptBlowfish::decrypt($this->onedrive_user_id);
            }

            if (!empty($this->onedrive_access_token)) {
                $this->onedrive_access_token = CryptBlowfish::decrypt($this->onedrive_access_token);
            }

            if (!empty($this->onedrive_refresh_token)) {
                $this->onedrive_refresh_token = CryptBlowfish::decrypt($this->onedrive_refresh_token);
            }
        }
    }

    public function is_valid()
    {
        $is_valid = true;
        if ($this->storage_type == DUP_PRO_Storage_Types::Local) {
            $is_valid = is_writable($this->local_storage_folder);
        }

        return $is_valid;
    }

    public function get_type_text()
    {
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Local:
                return DUP_PRO_U::__('Local');
            case DUP_PRO_Storage_Types::Dropbox:
                return DUP_PRO_U::__('Dropbox');
            case DUP_PRO_Storage_Types::FTP:
                return DUP_PRO_U::__('FTP');
            case DUP_PRO_Storage_Types::SFTP:
                return DUP_PRO_U::__('SFTP');
            case DUP_PRO_Storage_Types::GDrive:
                return DUP_PRO_U::__('Google Drive');
            case DUP_PRO_Storage_Types::S3:
                return $this->s3_is_amazon() ? DUP_PRO_U::__('Amazon S3') : DUP_PRO_U::__('S3-Compatible (Generic)');
            case DUP_PRO_Storage_Types::OneDrive:
                return DUP_PRO_U::__('OneDrive (v0.1)');
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                return DUP_PRO_U::__('OneDrive');
            default:
                return DUP_PRO_U::__('Unknown');
        }
    }

    public function get_action_text()
    {
        $text = '';
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Local:
                $text = __('Copying to directory:', 'duplicator-pro') . '<br>' . $this->get_storage_folder();
                break;
            case DUP_PRO_Storage_Types::Dropbox:
                $text = sprintf(DUP_PRO_U::__('Transferring to Dropbox folder:<br/> <i>%1$s</i>'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::FTP:
                $text = sprintf(DUP_PRO_U::__('Transferring to FTP server %1$s in folder:<br/> <i>%2$s</i>'), $this->ftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::SFTP:
                $text = sprintf(DUP_PRO_U::__('Transferring to SFTP server %1$s in folder:<br/> <i>%2$s</i>'), $this->sftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::GDrive:
                $text = sprintf(DUP_PRO_U::__('Transferring to Google Drive folder:<br/> <i>%1$s</i>'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::S3:
                $text = sprintf(DUP_PRO_U::__('Transferring to S3 (or S3 Compatible) folder:<br/> <i>%1$s</i>'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::OneDrive:
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                $text = sprintf(DUP_PRO_U::__('Transferring to OneDrive folder:<br/> <i>%1$s</i>'), $this->get_storage_folder());
                break;
            default:
                $text = DUP_PRO_U::__('Transferring to unknown storage type');
        }
        return $text;
    }

    public function get_pending_text()
    {
        $text = '';
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Local:
                $text = sprintf(DUP_PRO_U::__('Copy to directory %1$s is pending'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::Dropbox:
                $text = sprintf(DUP_PRO_U::__('Transfer to Dropbox folder %1$s is pending'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::FTP:
                $text = sprintf(DUP_PRO_U::__('Transfer to FTP server %1$s in folder %2$s is pending'), $this->ftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::SFTP:
                $text = sprintf(DUP_PRO_U::__('Transfer to SFTP server %1$s in folder %2$s is pending'), $this->sftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::GDrive:
                $text = sprintf(DUP_PRO_U::__('Transfer to Google Drive folder %1$s is pending'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::S3:
                $text = sprintf(DUP_PRO_U::__('Transfer to S3 (or S3 Compatible) folder %1$s is pending'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::OneDrive:
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                $text = sprintf(DUP_PRO_U::__('Transfer to OneDrive folder %1$s is pending'), $this->get_storage_folder());
                break;
        }
        return $text;
    }

    public function get_failed_text()
    {
        $text = '';
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Local:
                $text = sprintf(DUP_PRO_U::__('Failed to copy to directory %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::Dropbox:
                $text = sprintf(DUP_PRO_U::__('Failed to transfer to Dropbox folder %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::FTP:
                $text = sprintf(DUP_PRO_U::__('Failed to transfer to FTP server %1$s in folder %2$s'), $this->ftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::SFTP:
                $text = sprintf(DUP_PRO_U::__('Failed to transfer to SFTP server %1$s in folder %2$s'), $this->sftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::GDrive:
                $text = sprintf(DUP_PRO_U::__('Failed to transfer to Google Drive folder %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::S3:
                $text = sprintf(DUP_PRO_U::__('Failed to transfer to S3 (or S3 Compatible) folder %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::OneDrive:
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                $text = sprintf(DUP_PRO_U::__('Failed to transfer to OneDrive folder %1$s'), $this->get_storage_folder());
                break;
        }
        return $text;
    }

    public function get_cancelled_text()
    {
        $text = '';
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Local:
                $text = sprintf(DUP_PRO_U::__('Cancelled before could copy to directory %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::Dropbox:
                $text = sprintf(DUP_PRO_U::__('Cancelled before could transfer to Dropbox folder %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::FTP:
                $text = sprintf(DUP_PRO_U::__('Cancelled before could transfer to FTP server:<br/>%1$s in folder %2$s'), $this->ftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::SFTP:
                $text = sprintf(DUP_PRO_U::__('Cancelled before could transfer to SFTP server:<br/>%1$s in folder %2$s'), $this->sftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::GDrive:
                $text = sprintf(DUP_PRO_U::__('Cancelled before could transfer to Google Drive folder %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::S3:
                $text = sprintf(DUP_PRO_U::__('Cancelled before could transfer to S3 (or S3 Compatible) folder %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::OneDrive:
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                $text = sprintf(DUP_PRO_U::__('Cancelled before could transfer to OneDrive folder %1$s'), $this->get_storage_folder());
                break;
        }
        return $text;
    }

    public function get_succeeded_text()
    {
        $text = '';
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Local:
                $text = sprintf(DUP_PRO_U::__('Copied package to directory %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::Dropbox:
                $text = sprintf(DUP_PRO_U::__('Transferred package to Dropbox folder %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::FTP:
                $text = sprintf(DUP_PRO_U::__('Transferred package to FTP server:<br/>%1$s in folder %2$s'), $this->ftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::SFTP:
                $text = sprintf(DUP_PRO_U::__('Transferred package to SFTP server:<br/>%1$s in folder %2$s'), $this->sftp_server, $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::GDrive:
                $text = sprintf(DUP_PRO_U::__('Transferred package to Google Drive folder %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::S3:
                $text = sprintf(DUP_PRO_U::__('Transferred package to S3 (or S3 Compatible) folder %1$s'), $this->get_storage_folder());
                break;
            case DUP_PRO_Storage_Types::OneDrive:
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                $text = sprintf(DUP_PRO_U::__('Transferred package to OneDrive folder %1$s'), $this->get_storage_folder());
                break;
        }
        return $text;
    }

    /**
     * Returns an html anchor tag of location
     *
     * @return string    Returns an html anchor tag with the storage location as a hyperlink.
     *
     * @example
     * OneDrive Example return
     * <a target="_blank" href="https://1drv.ms/f/sAFrQtasdrewasyghg">https://1drv.ms/f/sAFrQtasdrewasyghg</a>
     */
    public function getHtmlLocationLink()
    {
        try {
            $link           = "";
            $store_location = $this->get_storage_location_string();
            $safe_store_loc = urldecode($store_location);
            switch ($this->get_storage_type()) {
                case DUP_PRO_Storage_Types::Local:
                case DUP_PRO_Storage_Types::OneDrive:
                case DUP_PRO_Storage_Types::OneDriveMSGraph:
                    $link =  str_replace('<a', '<a target="_blank" ', $safe_store_loc);
                    break;
                case DUP_PRO_Storage_Types::GDrive:
                    $link = "<a href='https://drive.google.com/drive/' target='_blank'>{$safe_store_loc}</a>";
                    break;
                default:
                    $link = "<a href='{$store_location}' target='_blank'>{$safe_store_loc}</a>";
                    break;
            }

            return $link;
        } catch (Exception $ex) {
            return "Unable to retrieve link!";
        }
    }


    /**
     * Copies the package files from the default local storage to another local storage location
     *
     * @param DUP_PRO_Package             $package     the package
     * @param DUP_PRO_Package_Upload_Info $upload_info the upload info
     *
     * @return void
     */
    private function copy_to_local(DUP_PRO_Package $package, DUP_PRO_Package_Upload_Info $upload_info)
    {

        if ($this->id == DUP_PRO_Virtual_Storage_IDs::Default_Local) {
            DUP_PRO_Log::infoTrace("SUCCESS: copied to default location: " . DUPLICATOR_PRO_SSDIR_PATH);
            // It's the default local storage location so do nothing - it's already there
            $upload_info->copied_archive   = true;
            $upload_info->copied_installer = true;
            $package->update();
            return;
        }

        //must be $to => $from because array key has to be unique
        $replacements = array(
            $package->Installer->getSafeFilePath() => SnapIO::trailingslashit($this->local_storage_folder) . basename($package->Installer->getSafeFilePath()),
            $package->Archive->getSafeFilePath() => SnapIO::trailingslashit($this->local_storage_folder) . basename($package->Archive->getSafeFilePath())
        );

        $storageUpload = new StorageUploadChunkFiles(
            array(
                'replacements' => $replacements,
                'chunkSize'    => DUP_PRO_Global_Entity::getInstance()->local_upload_chunksize_in_MB * MB_IN_BYTES,
                'upload_info'  => $upload_info,
                'package'      => $package,
                'storage'      => $this
            ),
            0,
            1000
        );

        switch ($storageUpload->start()) {
            case StorageUploadChunkFiles::CHUNK_COMPLETE:
                DUP_PRO_Log::trace('CHUNK LOCAL UPLOAD COMPLETE');
                $upload_info->copied_installer = true;
                $upload_info->copied_archive   = true;

                if ($this->local_max_files > 0) {
                    DUP_PRO_Log::trace('Trying to purge local');
                    $this->purge_old_local_packages();
                }
                break;
            case StorageUploadChunkFiles::CHUNK_STOP:
                DUP_PRO_Log::trace('CHUNK LOCAL UPLOAD NOT COMPLETE >> CONTINUE NEXT CHUNK');
                //do nothing for now
                break;
            case StorageUploadChunkFiles::CHUNK_ERROR:
            default:
                DUP_PRO_Log::infoTrace('Chunk upload error: ' . $storageUpload->getLastErrorMessage());
                $upload_info->failed = true;
        }
        $package->update();
    }

    private function copy_to_dropbox(DUP_PRO_Package $package, DUP_PRO_Package_Upload_Info $upload_info)
    {
        $source_archive_filepath   = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive);
        $source_installer_filepath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Installer);

        try {
            if ($source_archive_filepath === false) {
                $upload_info->failed = true;
                throw new Exception("Archive doesn't exist for $package->Name!? - $source_archive_filepath");
            }

            if ($source_installer_filepath === false) {
                $upload_info->failed = true;
                throw new Exception("Installer doesn't exist for $package->Name!? - $source_installer_filepath");
            }

            $dropbox                 = $this->get_dropbox_client(false);
            $dropbox_archive_path    = basename($source_archive_filepath);
            $dropbox_archive_path    = $this->dropbox_storage_folder . "/$dropbox_archive_path";
            $dest_installer_filename = $package->Installer->getInstallerName();
            $dropbox_installer_path  = $this->dropbox_storage_folder . "/$dest_installer_filename";

            if (!$upload_info->copied_installer) {
                DUP_PRO_Log::trace("ATTEMPT: Dropbox upload installer file $source_installer_filepath to $dropbox_installer_path");
                $installer_meta = $dropbox->UploadFile($source_installer_filepath, $dropbox_installer_path, $dest_installer_filename);
                if (!$dropbox->checkFileHash($installer_meta, $source_installer_filepath)) {
                    throw new Exception("**ERROR: installer upload to DropBox" . $dropbox_installer_path . ". Uploaded installer file may be corrupted. Hashes doesn't match.");
                }

                DUP_PRO_Log::infoTrace("SUCCESS: installer upload to DropBox " . $dropbox_installer_path);
                $upload_info->copied_installer = true;
                $upload_info->progress         = 5;
            } else {
                DUP_PRO_Log::trace("Already uploaded installer on previous execution of Dropbox $this->name so skipping");
            }

            if (!$upload_info->copied_archive) {
                /* Delete the archive if we are just starting it (in the event they are pushing another copy */
                if ($upload_info->archive_offset == 0) {
                    DUP_PRO_Log::trace("Archive offset is 0 so deleting $dropbox_archive_path");
                    try {
                        $dropbox->Delete($dropbox_archive_path);
                    } catch (Exception $ex) {
                        // Burying exceptions
                    }
                }

                $global = DUP_PRO_Global_Entity::getInstance();

                /* @var $dropbox_upload_info DUP_PRO_DropboxClient_UploadInfo */
                $dropbox_upload_info = $dropbox->upload_file_chunk(
                    $source_archive_filepath,
                    $dropbox_archive_path,
                    $global->dropbox_upload_chunksize_in_kb * 1024,
                    $global->php_max_worker_time_in_sec,
                    $upload_info->archive_offset,
                    $upload_info->upload_id,
                    $this->throttleDelayInUs
                );

                $upload_info->archive_offset = isset($dropbox_upload_info->next_offset) ? $dropbox_upload_info->next_offset : 0;
                $upload_info->upload_id      = $dropbox_upload_info->upload_id;

                if ($dropbox_upload_info->error_details !== null) {
                    throw new Exception("FAIL: archive upload to dropbox. Error received from Dropbox API: $dropbox_upload_info->error_details");
                }

                // Clear the failure count - we are just looking for consecutive errors
                $file_size                  = filesize($source_archive_filepath);
                $upload_info->progress      = max(5, DUP_PRO_U::percentage($upload_info->archive_offset, $file_size, 0));
                $upload_info->failure_count = 0;
                DUP_PRO_Log::infoTrace("Archive upload offset: $upload_info->archive_offset [File size: $file_size] [Upload progress: $upload_info->progress%]");


                if ($dropbox_upload_info->file_meta != null && property_exists($dropbox_upload_info->file_meta, "size") && $dropbox_upload_info->file_meta->size === $file_size) {
                    DUP_PRO_Log::infoTrace("UPLOAD FINISHED. FILE META IS " . print_r($dropbox_upload_info->file_meta, true));

                    $upload_info->copied_archive = true;
                    if ($this->dropbox_max_files > 0) {
                        $this->purge_old_dropbox_packages($dropbox);
                    }
                }
            } else {
                DUP_PRO_Log::trace("Already copied archive on previous execution of Dropbox $this->name so skipping");
            }
        } catch (Exception $e) {
            $upload_info->increase_failure_count();
            DUP_PRO_Log::trace("Exception caught copying package $package->Name to $this->dropbox_storage_folder. " . $e->getMessage());
        }

        if ($upload_info->failed) {
            DUP_PRO_Log::infoTrace('Dropbox storage failed flag ($upload_info->failed) has been already set.');
        }

        // The package update will automatically capture the upload_info since its part of the package
        $package->update();
    }

    private function copy_to_onedrive(DUP_PRO_Package $package, DUP_PRO_Package_Upload_Info $upload_info)
    {
        $source_archive_filepath   = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive);
        $source_installer_filepath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Installer);
        if ($source_archive_filepath !== false) {
            if ($source_installer_filepath !== false) {
                $onedrive                = $this->get_onedrive_client();
                $onedrive_archive_path   = basename($source_archive_filepath);
                $onedrive_archive_path   = $this->get_sanitized_storage_folder() . $onedrive_archive_path;
                $onedrive_installer_name = $package->Installer->getInstallerName();
                $onedrive_installer_path = $this->get_sanitized_storage_folder() . $onedrive_installer_name;
                try {
                    $folder_id = $this->onedrive_storage_folder_id;
                    if (!$folder_id) {
                        $this->get_onedrive_storage_folder();
                    }
                    if (!$upload_info->copied_installer) {
                        DUP_PRO_Log::trace("ATTEMPT: OneDrive upload installer file $source_installer_filepath to $onedrive_installer_path");
                        $onedrive->uploadFileChunk($source_installer_filepath, $onedrive_installer_path);
                        try {
                            if ($onedrive->RUploader->sha1CheckSum($source_installer_filepath)) {
                                DUP_PRO_Log::infoTrace("SUCCESS: installer upload to OneDrive " . $onedrive_installer_path);
                                $upload_info->copied_installer = true;
                                $upload_info->progress         = 5;
                                // The package update will automatically capture the upload_info since its part of the package
                                $package->update();
                            } else {
                                DUP_PRO_Log::infoTrace("FAIL: installer upload to OneDrive $onedrive_installer_path. The uploaded Uploaded installer file is corrupted, the sha1 hashes don't match!");
                                $upload_info->increase_failure_count();
                            }
                        } catch (Exception $exception) {
                            if ($exception->getCode() == 404 && $onedrive->isBusiness()) {
                                DUP_PRO_Log::infoTrace("SUCCESS: installer upload to OneDrive " . $onedrive_installer_path);
                                $upload_info->copied_installer = true;
                                $upload_info->progress         = 5;
                                // The package update will automatically capture the upload_info since its part of the package
                                $package->update();
                            } else {
                                DUP_PRO_Log::traceError("FAIL: installer upload to OneDrive $onedrive_installer_path. An error occurred while checking the file checksum. Exception message: " . $exception->getMessage());
                                $upload_info->increase_failure_count();
                            }
                        }
                    } else {
                        DUP_PRO_Log::trace("Already copied installer on previous execution of Onedrive $this->name so skipping");
                    }
                    if (!$upload_info->copied_archive) {
                        /* Delete the archive if we are just starting it (in the event they are pushing another copy */
                        if ($upload_info->archive_offset == 0) {
                            DUP_PRO_Log::trace("Archive offset is 0 so try to delete $onedrive_archive_path");
                            try {
                                $onedrive_archive = $onedrive->fetchDriveItemByPath($onedrive_archive_path);
                                $onedrive->deleteDriveItem($onedrive_archive->getId());
                            } catch (Exception $ex) {
                                // Burying exceptions
                            }
                        }

                        /* @var $global DUP_PRO_Global_Entity */
                        $global = DUP_PRO_Global_Entity::getInstance();
                        if ($upload_info->data != '' && $upload_info->data2 != '') {
                            $resumable = (object)array(
                                "uploadUrl" => $upload_info->data,
                                "expirationTime" => $upload_info->data2
                            );
                            $onedrive->uploadFileChunk($source_archive_filepath, null, $resumable, $global->php_max_worker_time_in_sec, (50 + $this->throttleDelayInUs), $upload_info->archive_offset);
                        } else {
                            $onedrive->uploadFileChunk($source_archive_filepath, $onedrive_archive_path, null, $global->php_max_worker_time_in_sec, (50 + $this->throttleDelayInUs), $upload_info->archive_offset);
                        }

                        /* @var $onedrive_upload_info \Krizalys\Onedrive\ResumableUploader */
                        $onedrive_upload_info = $onedrive->RUploader;
                        $upload_info->data    = $onedrive_upload_info->getUploadUrl();
                        $upload_info->data2   = $onedrive_upload_info->getExpirationTime();
                        if ($onedrive_upload_info->getError() == null) {
                            // Clear the failure count - we are just looking for consecutive errors
                            $upload_info->failure_count  = 0;
                            $upload_info->archive_offset = $onedrive_upload_info->getUploadOffset();
                            $file_size                   = filesize($source_archive_filepath);
                            $upload_info->progress       = max(5, DUP_PRO_U::percentage($upload_info->archive_offset, $file_size, 0));
                            DUP_PRO_Log::infoTrace("Archive upload offset: $upload_info->archive_offset [File size: $file_size] [Upload progress: $upload_info->progress%]");
                            if ($onedrive_upload_info->completed()) {
                                try {
                                    if ($onedrive_upload_info->sha1CheckSum($source_archive_filepath)) {
                                        DUP_PRO_Log::infoTrace("SUCCESS: archive upload to OneDrive.");
                                        $upload_info->archive_offset = $file_size;
                                        $upload_info->copied_archive = true;
                                        $this->purge_old_onedrive_packages($onedrive);
                                    } else {
                                        DUP_PRO_Log::infoTrace("FAIL: archive upload to OneDrive. sha1 hashes don't match!");
                                        $this->set_onedrive_archive_offset($upload_info, $onedrive_upload_info);
                                        $upload_info->increase_failure_count();
                                    }
                                } catch (Exception $exception) {
                                    if ($exception->getCode() == 404 && $onedrive->isBusiness()) {
                                        DUP_PRO_Log::infoTrace("SUCCESS: archive upload to OneDrive.");
                                        $upload_info->archive_offset = $file_size;
                                        $upload_info->copied_archive = true;
                                        $this->purge_old_onedrive_packages($onedrive);
                                    } else {
                                        DUP_PRO_Log::infoTrace("FAIL: archive upload to OneDrive. An error occurred while checking the file checksum. Exception message: " . $exception->getMessage());
                                        $upload_info->increase_failure_count();
                                    }
                                }
                            }
                        } else {
                            DUP_PRO_Log::traceError("FAIL: archive upload to OneDrive. An error occurred while checking the file checksum. Error message: " . $onedrive_upload_info->getError());
                            // error_log("* Else Problem uploading archive for package $package->Name: ".$onedrive_upload_info->getError());

                            // Could have partially uploaded so retain that offset.
                            $this->set_onedrive_archive_offset($upload_info, $onedrive_upload_info);
                            $upload_info->increase_failure_count();
                        }
                    } else {
                        DUP_PRO_Log::trace("Already copied archive on previous execution of Onedrive $this->name so skipping");
                    }
                } catch (Exception $e) {
                    DUP_PRO_Log::trace("Exception caught copying package $package->Name to $this->onedrive_storage_folder. " . $e->getMessage());
                    $this->set_onedrive_archive_offset($upload_info, (isset($onedrive_upload_info) ? $onedrive_upload_info : null));
                    $upload_info->increase_failure_count();
                }
            } else {
                DUP_PRO_Log::traceError("Installer doesn't exist for $package->Name!? - $source_installer_filepath");
                $upload_info->failed = true;
            }
        } else {
            DUP_PRO_Log::traceError("Archive doesn't exist for $package->Name!? - $source_archive_filepath");
            $upload_info->failed = true;
        }

        if ($upload_info->failed) {
            DUP_PRO_Log::infoTrace('OneDrive storage failed flag ($upload_info->failed) has been already set.');
        }

        // The package update will automatically capture the upload_info since its part of the package
        $package->update();
    }

    private function set_onedrive_archive_offset($upload_info, $onedrive_upload_info = null)
    {
        DUP_PRO_Log::trace("Try to set OneDrive archive offset because of error");
        if (!empty($upload_info->data)) {
            // error_log("Calling GET resume URL for getting next offset: ".$upload_info->data);
            DUP_PRO_Log::trace("Calling GET resume URL to get OneDrive next offset");
            $archive_offset = '';
            $response       = wp_remote_get($upload_info->data, array('timeout' => 60));
            $response_code  = wp_remote_retrieve_response_code($response);
            // error_log('%% resp code:'. $response_code);
            if (200 == $response_code) {
                $response_body_json = wp_remote_retrieve_body($response);
                /* Will result in $api_response being an array of data,
                parsed from the JSON response of the API listed above */
                $response_body_array = json_decode($response_body_json, true);
                $next_expected_range = isset($response_body_array['nextExpectedRanges'][0])
                    ? trim($response_body_array['nextExpectedRanges'][0], '"')
                    : '';
                // "12345-45754"
                $next_expected_range_parts    = explode('-', $next_expected_range);
                $next_expected_range_parts[0] = intval($next_expected_range_parts[0]);
                if ($next_expected_range_parts[0] > 0) {
                    $archive_offset = $next_expected_range_parts[0];
                    // error_log("Got OneDrive Archive offset $archive_offset from GET resume URL");
                    DUP_PRO_Log::info("Got OneDrive Archive offset $archive_offset from OneDrive GET resume URL.");
                }
            }
        }

        if (empty($archive_offset)) {
            if (!is_null($onedrive_upload_info)) {
                $archive_offset = $onedrive_upload_info->getUploadOffset();
            } else {
                $archive_offset = 0;
            }
        }

        $upload_info->archive_offset = $archive_offset;
        // error_log("Setting archive offset to the ".$upload_info->archive_offset);
        DUP_PRO_Log::infoTrace("Setting archive offset to the " . $upload_info->archive_offset);
    }

    private function copy_to_gdrive(DUP_PRO_Package $package, DUP_PRO_Package_Upload_Info $upload_info)
    {
        /* @var $upload_info DUP_PRO_Package_Upload_Info */

        /* @var $package DUP_PRO_Package */

        $source_archive_filepath   = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive);
        $source_installer_filepath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Installer);
        $dest_installer_filename   = $package->Installer->getInstallerName();
        if ($source_archive_filepath !== false) {
            if ($source_installer_filepath !== false) {
                try {
                    /* @var $google_client Duplicator_Pro_Google_Client */
                    $google_client = $this->get_full_google_client();
                    if ($google_client == null) {
                        throw new Exception("Google client is null!");
                    }

                    if (empty($upload_info->data)) {
                        $google_service_drive = new Duplicator_Pro_Google_Service_Drive($google_client);
                        $upload_info->data    = DUP_PRO_GDrive_U::get_directory_id($google_service_drive, $this->gdrive_storage_folder);
                        if ($upload_info->data == null) {
                            $upload_info->failed = true;
                            DUP_PRO_Log::infoTrace("Error getting/creating Google Drive directory $this->gdrive_storage_folder.");
                            $package->update();
                            return;
                        }
                    }

                    $tried_copying_installer = false;
                    if (!$upload_info->copied_installer) {
                        $tried_copying_installer = true;
                        DUP_PRO_Log::trace("ATTEMPT: Dropbox upload installer file $source_installer_filepath to $this->gdrive_storage_folder");
                        $google_service_drive = new Duplicator_Pro_Google_Service_Drive($google_client);
                        //$upload_info->data is the parent file id
                        $source_installer_filename = basename($source_installer_filepath);
                        $existing_file_id          = DUP_PRO_GDrive_U::get_file(
                            $google_service_drive,
                            $source_installer_filename,
                            $upload_info->data
                        );
                        if ($existing_file_id != null) {
                            DUP_PRO_Log::trace("Installer already exists so deleting $source_installer_filename before uploading again. Existing file id = $existing_file_id");
                            DUP_PRO_GDrive_U::delete_file($google_service_drive, $existing_file_id);
                        } else {
                            DUP_PRO_Log::trace("Installer doesn't exist already so no need to delete $source_installer_filename");
                        }

                        if (DUP_PRO_GDrive_U::upload_file($google_client, $source_installer_filepath, $upload_info->data, $dest_installer_filename)) {
                            DUP_PRO_Log::infoTrace('SUCCESS: Installer upload to Google Drive.');
                            $upload_info->copied_installer = true;
                            $upload_info->progress         = 5;
                        } else {
                            $upload_info->failed = true;
                            DUP_PRO_Log::infoTrace('FAIL: Installer upload to Google Drive.');
                        }

                        // The package update will automatically capture the upload_info since its part of the package
                        $package->update();
                    } else {
                        DUP_PRO_Log::trace("Already copied installer on previous execution of Google Drive $this->name so skipping");
                    }

                    if ((!$upload_info->copied_archive) && (!$tried_copying_installer)) {
                        /* @var $global DUP_PRO_Global_Entity */
                        $global = DUP_PRO_Global_Entity::getInstance();
                        /* @var $dropbox_upload_info DUP_PRO_DropboxClient_UploadInfo */

                        // Warning: Google client is set to defer mode within this function
                        // The upload_id for google drive is just the resume uri
                        //

                        if ($upload_info->archive_offset == 0) {
                            // If just starting on this go ahead and delete existing file

                            $google_service_drive = new Duplicator_Pro_Google_Service_Drive($google_client);
                            //$upload_info->data is the parent file id
                            $source_archive_filename = basename($source_archive_filepath);
                            $existing_file_id        = DUP_PRO_GDrive_U::get_file($google_service_drive, $source_archive_filename, $upload_info->data);
                            if ($existing_file_id != null) {
                                DUP_PRO_Log::trace("Archive already exists so deleting $source_archive_filename before uploading again");
                                DUP_PRO_GDrive_U::delete_file($google_service_drive, $existing_file_id);
                            } else {
                                DUP_PRO_Log::trace("Archive doesn't exist so no need to delete $source_archive_filename");
                            }
                        }

                        // error_log('## offset: '.$upload_info->archive_offset);
                        // Google Drive worker time capped at 10 seconds
                        $gdrive_upload_info = DUP_PRO_GDrive_U::upload_file_chunk(
                            $google_client,
                            $source_archive_filepath,
                            $upload_info->data,
                            $global->gdrive_upload_chunksize_in_kb * 1024,
                            10,
                            $upload_info->archive_offset,
                            $upload_info->upload_id,
                            (50 + $this->throttleDelayInUs)
                        );
                        $file_size          = filesize($source_archive_filepath);
                        // Attempt to test self killing
                        /*
    if (time() % 5 === 0) {
    error_log('Attempting to make custom error');
    $gdrive_upload_info->error_details = "Custom Error";
    }
    */

                        if ($gdrive_upload_info->error_details == null) {
                            // Clear the failure count - we are just looking for consecutive errors
                            $upload_info->failure_count  = 0;
                            $upload_info->archive_offset = isset($gdrive_upload_info->next_offset) ? $gdrive_upload_info->next_offset : 0;
                            // We are considering the whole Resume URI as the Upload ID
                            $upload_info->upload_id = $gdrive_upload_info->resume_uri;
                            $upload_info->progress  = max(5, DUP_PRO_U::percentage($upload_info->archive_offset, $file_size, 0));
                            DUP_PRO_Log::infoTrace("Archive upload offset: $upload_info->archive_offset [File size: $file_size] [Upload progress: $upload_info->progress%]");
                            if ($gdrive_upload_info->is_complete) {
                                DUP_PRO_Log::infoTrace('SUCCESS: Archive upload to Google Drive.');
                                $upload_info->copied_archive = true;
                                if ($this->gdrive_max_files > 0) {
                                    $this->purge_old_gdrive_packages($google_client, $upload_info);
                                }
                            }
                        } else {
                            DUP_PRO_Log::traceError('FAIL: Archive upload to Google Drive. ERROR: ' . $gdrive_upload_info->error_details);
                            // error_log('$$ ELSE: '.$gdrive_upload_info->error_details);
                            $this->set_gdrive_archive_offset($upload_info);
                            $upload_info->increase_failure_count();
                        }
                    } else {
                        DUP_PRO_Log::trace("Already copied archive on previous execution of Google Drive $this->name so skipping");
                    }
                } catch (Exception $e) {
                    // error_log('**** Catch ****');
                    DUP_PRO_Log::traceError('EXCEPTION ERROR: Problems copying package $package->Name to $this->gdrive_storage_folder. Message: ' . $e->getMessage());
                    $this->set_gdrive_archive_offset($upload_info);
                    $upload_info->increase_failure_count();
                }
            } else {
                DUP_PRO_Log::traceError("Installer doesn't exist for $package->Name!? - $source_installer_filepath");
                $upload_info->failed = true;
            }
        } else {
            DUP_PRO_Log::traceError("Archive doesn't exist for $package->Name!? - $source_archive_filepath");
            $upload_info->failed = true;
        }

        if ($upload_info->failed) {
            DUP_PRO_Log::infoTrace('Google Drive storage failed flag ($upload_info->failed) has been already set.');
        }

        // The package update will automatically capture the upload_info since its part of the package
        $package->update();
    }

    private function set_gdrive_archive_offset($upload_info)
    {
        $resume_url = $upload_info->upload_id;
        if (is_null($resume_url)) {
            $upload_info->archive_offset = 0;
        } else {
            $args          = array(
                'headers' => array(
                    'Content-Length' => "0",
                    'Content-Range'   => "bytes */*",
                ),
                'method'    => 'PUT',
                'timeout' => 60,
            );
            $response      = wp_remote_request($resume_url, $args);
            $response_code = wp_remote_retrieve_response_code($response);
            DUP_PRO_Log::infoTrace("Google Drive API response code: $response_code");
            // error_log('response code:'.$response_code);
            switch ($response_code) {
                case 308:
                    DUP_PRO_Log::infoTrace("Google Drive transfer is incomplete.");
                    // error_log("Google Drive transfer is incomplete");
                    $range = wp_remote_retrieve_header($response, 'range');
                    if (!empty($range) && preg_match('/bytes=0-(\d+)$/', $range, $matches)) {
                        $upload_info->archive_offset = 1 + (int) $matches[1];
                    } else {
                        $upload_info->archive_offset = 0;
                    }

                    break;
                case 200:
                case 201:
                    DUP_PRO_Log::infoTrace("SUCCESS: archive upload to Google Drive.");
                    $upload_info->copied_archive = true;
                    if ($this->gdrive_max_files > 0) {
                        $google_client = $this->get_full_google_client();
                        $this->purge_old_gdrive_packages($google_client, $upload_info);
                    }


                    break;
                case 404:
                default:
                    $upload_info->archive_offset = 0;

                    break;
            }
        }
        // error_log("Setting archive offset to the ".$upload_info->archive_offset);
        DUP_PRO_Log::trace("Setting archive offset to the " . $upload_info->archive_offset);
    }

    private function copy_to_s3(DUP_PRO_Package $package, DUP_PRO_Package_Upload_Info $upload_info)
    {
        DUP_PRO_Log::trace("Copying to S3");
        $source_archive_filepath   = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive);
        $source_installer_filepath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Installer);
        if ($source_archive_filepath !== false) {
            if ($source_installer_filepath !== false) {
                $s3_client = $this->get_full_s3_client();
                try {
                    $tried_copying_installer = !$upload_info->copied_installer;
                    if ($upload_info->copied_installer == false) {
                        DUP_PRO_Log::trace("ATTEMPT: S3 upload installer file $source_installer_filepath to $this->s3_storage_folder");
                        $dest_installer_filename = $package->Installer->getInstallerName();
                        if (DUP_PRO_S3_U::upload_file($s3_client, $this->s3_bucket, $source_installer_filepath, $this->s3_storage_folder, $this->s3_storage_class, $this->s3_ACL_full_control, $dest_installer_filename)) {
                            DUP_PRO_Log::infoTrace("SUCCESS: installer upload to S3 " . $this->s3_storage_folder);
                            $upload_info->copied_installer = true;
                            $upload_info->progress         = 5;
                        } else {
                            $upload_info->failed = true;
                            DUP_PRO_Log::infoTrace("FAIL: installer upload to S3.");
                        }

                        // The package update will automatically capture the upload_info since its part of the package
                        $package->update();
                        return;
                    } else {
                        DUP_PRO_Log::trace("Already copied installer on previous execution of S3 $this->name so skipping");
                    }

                    if ($upload_info->copied_archive == false && $tried_copying_installer == false) {
                        $global = DUP_PRO_Global_Entity::getInstance();
                        // Data
                        $s3_upload_info                 = new DUP_PRO_S3_Client_UploadInfo();
                        $s3_upload_info->bucket         = $this->s3_bucket;
                        $s3_upload_info->upload_id      = $upload_info->upload_id;
                        $s3_upload_info->dest_directory = $this->s3_storage_folder;
                        $s3_upload_info->src_filepath   = $source_archive_filepath;
                        $s3_upload_info->next_offset    = $upload_info->archive_offset;
                        $s3_upload_info->storage_class  = $this->s3_storage_class;
                        // Storing array of [part] and [parts] in an array within data
                        if ($upload_info->data == '') {
                            $upload_info->data = 1;
                            // part number
                            $upload_info->data2 = array();
                            // parts array
                        }

                        $s3_upload_info->part_number      = $upload_info->data;
                        $s3_upload_info->parts            = $upload_info->data2;
                        $s3_upload_info->upload_part_size = $global->s3_upload_part_size_in_kb * 1024;
                        $s3_upload_info                   = DUP_PRO_S3_U::upload_file_chunk($s3_client, $s3_upload_info, $global->php_max_worker_time_in_sec, $this->throttleDelayInUs);
                        if ($s3_upload_info->error_details == null) {
                            // Clear the failure count - we are just looking for consecutive errors
                            $upload_info->failure_count  = 0;
                            $upload_info->archive_offset = isset($s3_upload_info->next_offset) ? $s3_upload_info->next_offset : 0;
                            $upload_info->upload_id      = $s3_upload_info->upload_id;
                            $upload_info->data           = $s3_upload_info->part_number;
                            $upload_info->data2          = $s3_upload_info->parts;
                            $file_size                   = filesize($source_archive_filepath);
                            $upload_info->progress       = max(5, DUP_PRO_U::percentage($upload_info->archive_offset, $file_size, 0));
                            DUP_PRO_Log::infoTrace("Archive upload offset: $upload_info->archive_offset [File size: $file_size] [Upload progress: $upload_info->progress%]");
                            if ($s3_upload_info->is_complete) {
                                DUP_PRO_Log::infoTrace("SUCCESS: archive upload to S3.");
                                $upload_info->copied_archive = true;
                                if ($this->s3_max_files > 0) {
                                    $this->purge_old_s3_packages($s3_client);
                                }
                            }
                        } else {
                            DUP_PRO_Log::infoTrace("FAIL: archive upload to S3. Get error from S3 API: " . $s3_upload_info->error_details);
                            // Could have partially uploaded so retain that offset.
                            $upload_info->archive_offset = isset($s3_upload_info->next_offset) ? $s3_upload_info->next_offset : 0;
                            $upload_info->increase_failure_count();
                        }
                    } else {
                        if ($upload_info->copied_archive) {
                            DUP_PRO_Log::trace("Already copied archive on previous execution of S3 $this->name so skipping");
                        }
                    }
                } catch (Exception $e) {
                    DUP_PRO_Log::trace("Exception caught copying package $package->Name to S3 $this->s3_storage_folder: " . $e->getMessage());
                    $upload_info->increase_failure_count();
                }
            } else {
                DUP_PRO_Log::traceError("Installer doesn't exist for $package->Name!? - $source_installer_filepath");
                $upload_info->failed = true;
            }
        } else {
            DUP_PRO_Log::traceError("Archive doesn't exist for $package->Name!? - $source_archive_filepath");
            $upload_info->failed = true;
        }

        if ($upload_info->failed) {
            DUP_PRO_Log::infoTrace('S3 storage failed flag ($upload_info->failed) has been already set.');
        }

        // The package update will automatically capture the upload_info since its part of the package

        $package->update();
    }

    public function get_storage_location_string()
    {
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Dropbox:
                $dropbox     = $this->get_dropbox_client();
                $dropBoxInfo = $dropbox->GetAccountInfo();
                if (!isset($dropBoxInfo->locale) || $dropBoxInfo->locale == 'en') {
                    return "https://dropbox.com/home/Apps/Duplicator%20Pro/$this->dropbox_storage_folder";
                } else {
                    return "https://dropbox.com/home";
                }
            case DUP_PRO_Storage_Types::FTP:
                return "ftp://$this->ftp_server:$this->ftp_port/$this->ftp_storage_folder";
            case DUP_PRO_Storage_Types::SFTP:
                return $this->sftp_server . ":" . $this->sftp_port;
            case DUP_PRO_Storage_Types::GDrive:
                return "google://$this->gdrive_storage_folder";
            case DUP_PRO_Storage_Types::Local:
                return $this->local_storage_folder;
            case DUP_PRO_Storage_Types::S3:
                $region = str_replace(' ', '%20', $this->s3_region);
                $bucket = str_replace(' ', '%20', $this->s3_bucket);
                $prefix = str_replace(' ', '%20', $this->s3_storage_folder);
                //return "<a target=\"_blank\" href=\"https://console.aws.amazon.com/s3/home?region=$region&bucket=$bucket&prefix=$prefix\">s3://$bucket/$prefix</a>";
                //return "s3://$bucket/{$this->s3_storage_folder}";

                return "<a target=\"_blank\" href=\"https://console.aws.amazon.com/s3/home?region=$region&bucket=$bucket&prefix=$prefix\">s3://$this->s3_bucket/$this->s3_storage_folder</a>";
            case DUP_PRO_Storage_Types::OneDrive:
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                if (empty($this->onedrive_storage_folder_web_url)) {
                    if ($this->onedrive_authorization_state === DUP_PRO_OneDrive_Authorization_States::Authorized) {
                        $storage_folder = $this->get_onedrive_storage_folder();
                        if (!empty($storage_folder)) {
                            $this->onedrive_storage_folder_web_url = $this->get_onedrive_storage_folder()->getWebURL();
                        } else {
                            $this->onedrive_storage_folder_web_url = DUP_PRO_U::__("Can't read storage folder");
                            return $this->onedrive_storage_folder_web_url;
                        }
                    } else {
                        $this->onedrive_storage_folder_web_url = DUP_PRO_U::__("Not Authenticated");
                        return $this->onedrive_storage_folder_web_url;
                    }
                    $this->save();
                }
                return '<a href="' . esc_url($this->onedrive_storage_folder_web_url) . '">' . esc_url($this->onedrive_storage_folder_web_url) . '</a>';
            default:
                return DUP_PRO_U::__('Unknown');
        }
    }

    private function copy_to_ftp(DUP_PRO_Package $package, DUP_PRO_Package_Upload_Info $upload_info)
    {
        /* @var $upload_info DUP_PRO_Package_Upload_Info */

        /* @var $package DUP_PRO_Package */
        DUP_PRO_Log::trace("copying to ftp");
        $source_archive_filepath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive);
        // $source_archive_filepath = DUP_PRO_U::$PLUGIN_DIRECTORY . '/lib/DropPHP/Poedit-1.6.4.2601-setup.bin';
        $source_installer_filepath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Installer);

        if ($source_archive_filepath !== false) {
            if ($source_installer_filepath !== false) {
                if ($this->ftp_use_curl) {
                    $ftp_client = new DUP_PRO_FTPcURL(
                        $this->ftp_server,
                        $this->ftp_port,
                        $this->ftp_username,
                        $this->ftp_password,
                        $this->ftp_storage_folder,
                        $this->ftp_timeout_in_secs,
                        $this->ftp_ssl,
                        $this->ftp_passive_mode
                    );
                } else {
                    $ftp_client = new DUP_PRO_FTP_Chunker(
                        $this->ftp_server,
                        $this->ftp_port,
                        $this->ftp_username,
                        $this->ftp_password,
                        $this->ftp_timeout_in_secs,
                        $this->ftp_ssl,
                        $this->ftp_passive_mode
                    );
                }

                if ($this->ftp_use_curl || $ftp_client->open()) {
                    if ($ftp_client->create_directory($this->ftp_storage_folder) == false) {
                        DUP_PRO_Log::infoTrace("FAIL: create/get FTP dir $this->ftp_storage_folder");
                        DUP_PRO_Log::trace("Couldn't create $this->ftp_storage_folder on $this->ftp_server");
                    }

                    try {
                        if ($upload_info->copied_installer == false) {
                            DUP_PRO_Log::trace("ATTEMPT: FTP upload installer file $source_installer_filepath to $this->ftp_storage_folder");
                            $dest_installer_filename = $package->Installer->getInstallerName();
                            if ($this->ftp_use_curl) {
                                $ret_upload_file = $ftp_client->upload_file($source_installer_filepath, $dest_installer_filename);
                            } else {
                                $ret_upload_file = $ftp_client->upload_file($source_installer_filepath, $this->ftp_storage_folder, $dest_installer_filename);
                            }
                            if ($ret_upload_file == false) {
                                $upload_info->failed = true;
                                DUP_PRO_Log::infoTrace("FAIL: installer upload to FTP. Error uploading $source_installer_filepath to $this->ftp_storage_folder");
                            } else {
                                DUP_PRO_Log::infoTrace("SUCCESS: installer upload to FTP.");
                                $upload_info->copied_installer = true;
                                $upload_info->progress         = 5;
                            }

                            // The package update will automatically capture the upload_info since its part of the package
                            $package->update();
                        } else {
                            DUP_PRO_Log::trace("Already copied installer on previous execution of FTP $this->name so skipping");
                        }

                        if ($upload_info->copied_archive == false) {
                            $global = DUP_PRO_Global_Entity::getInstance();
                            DUP_PRO_Log::trace("archive calling upload chunk with timeout");
                            $ftp_upload_info = $ftp_client->upload_chunk(
                                $source_archive_filepath,
                                $this->ftp_use_curl ? '' : $this->ftp_storage_folder,
                                $global->php_max_worker_time_in_sec,
                                $upload_info->archive_offset,
                                $this->throttleDelayInUs
                            );
                            DUP_PRO_Log::trace("after upload chunk archive");
                            if ($ftp_upload_info->error_details == null) {
                                // Since there was a successful chunk reset the failure count
                                $upload_info->failure_count  = 0;
                                $upload_info->archive_offset = $ftp_upload_info->next_offset;
                                $file_size                   = filesize($source_archive_filepath);
                                //  $upload_info->progress = max(5, 100 * (bcdiv($upload_info->archive_offset, $file_size, 2)));
                                $upload_info->progress = max(5, DUP_PRO_U::percentage($upload_info->archive_offset, $file_size, 0));
                                DUP_PRO_Log::infoTrace("Archive upload offset: $upload_info->archive_offset [File size: $file_size] [Upload progress: $upload_info->progress%]");
                                if ($ftp_upload_info->success) {
                                    DUP_PRO_Log::infoTrace("SUCCESS: archive upload to FTP $this->ftp_server.");
                                    $upload_info->copied_archive = true;
                                    if ($this->ftp_max_files > 0) {
                                        $this->purge_old_ftp_packages($ftp_client);
                                    }

                                    $package->update();
                                } else {
                                    // Need to quit all together b/c ftp connection stays open
                                    DUP_PRO_Log::trace("Exiting process since ftp partial");
                                    // A real hack since the ftp_close doesn't work on the async put
                                    $package->update();
                                    // Kick the worker again
                                    // DUP_PRO_Package_Runner::kick_off_worker();
                                    DUP_PRO_Package_Runner::$delayed_exit_and_kickoff = true;
                                    //exit();
                                    return;
                                }
                            } else {
                                DUP_PRO_Log::traceError("FAIL: archive  for package $package->Name upload to FTP $this->ftp_server. Getting Error from FTP: $ftp_upload_info->error_details");
                                if ($ftp_upload_info->fatal_error) {
                                    $installer_filename     = basename($source_installer_filepath);
                                    $installer_ftp_filepath = "{$this->ftp_storage_folder}/$installer_filename";
                                    DUP_PRO_Log::trace("Failed archive transfer so deleting $installer_ftp_filepath");
                                    $ftp_client->delete($installer_ftp_filepath);
                                    $upload_info->failed = true;
                                } else {
                                    $upload_info->archive_offset = $ftp_upload_info->next_offset;
                                    $upload_info->increase_failure_count();
                                }
                            }
                        } else {
                            DUP_PRO_Log::trace("Already copied archive on previous execution of FTP $this->name so skipping");
                        }
                    } catch (Exception $e) {
                        $upload_info->increase_failure_count();
                        DUP_PRO_Log::traceError("Problems copying package $package->Name to $this->ftp_storage_folder. " . $e->getMessage());
                    }

                    if (!$this->ftp_use_curl) {
                        $ftp_client->close();
                    }
                } else {
                    $upload_info->increase_failure_count();
                    DUP_PRO_Log::traceError("Couldn't open ftp connection " . $ftp_client->get_info());
                }
            } else {
                DUP_PRO_Log::traceError("Installer doesn't exist for $package->Name!? - $source_installer_filepath");
                $upload_info->failed = true;
            }
        } else {
            DUP_PRO_Log::traceError("Archive doesn't exist for $package->Name!? - $source_archive_filepath");
            $upload_info->failed = true;
        }

        if ($upload_info->failed) {
            DUP_PRO_Log::infoTrace('FTP storage failed flag ($upload_info->failed) has been already set.');
        }

        // The package update will automatically capture the upload_info since its part of the package
        $package->update();
    }

    private function copy_to_sftp(DUP_PRO_Package $package, DUP_PRO_Package_Upload_Info $upload_info)
    {
        DUP_PRO_Log::trace("copying to sftp");
        $source_archive_filepath   = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive);
        $source_installer_filepath = $package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Installer);
        if ($source_archive_filepath !== false) {
            if ($source_installer_filepath !== false) {
                $sFtpAdapter           = null;
                $failureCountIncreased = false;
                try {
                    $storage_folder        = $this->sftp_storage_folder;
                    $server                = $this->sftp_server;
                    $port                  = $this->sftp_port;
                    $username              = $this->sftp_username;
                    $password              = $this->sftp_password;
                    $private_key           = $this->sftp_private_key;
                    $private_key_password  = $this->sftp_private_key_password;
                    $disable_chunking_mode = $this->sftp_disable_chunking_mode;
                    if (DUP_PRO_STR::startsWith($storage_folder, '/') == false) {
                        $storage_folder = '/' . $storage_folder;
                    }

                    if (DUP_PRO_STR::endsWith($storage_folder, '/') == false) {
                        $storage_folder = $storage_folder . '/';
                    }

                    $sFtpAdapter = new SFTPAdapter($server, $port, $username, $password, $private_key, $private_key_password);

                    if ($sFtpAdapter->connect() === false) {
                        throw new Exception('SFTP connection fail');
                    }
                    if (!$sFtpAdapter->fileExists($storage_folder)) {
                        $sFtpAdapter->mkDirRecursive($storage_folder);
                    }

                    if ($upload_info->copied_installer == false) {
                        $source_filepath    = $source_installer_filepath;
                        $basename           = $package->Installer->getInstallerName();
                        $continueWithUpload = true;
                        try {
                            $sFtpAdapter->startChunkingTimer();
                            if (!$sFtpAdapter->put($storage_folder . $basename, $source_filepath)) {
                                $upload_info->failed = true;
                                $continueWithUpload  = false;
                                DUP_PRO_Log::infoTrace("FAIL: installer $source_installer_filepath upload to SFTP $this->sftp_storage_folder.");
                            }
                        } catch (ChunkingTimeoutException $e) {
                            $continueWithUpload = true;
                        }

                        if ($continueWithUpload) {
                            DUP_PRO_Log::infoTrace("SUCCESS: installer upload to SFTP $this->sftp_storage_folder.");
                            $upload_info->progress         = 5;
                            $upload_info->copied_installer = true;
                        }
                        // The package update will automatically capture the upload_info since its part of the package
                        $package->update();
                    } else {
                        DUP_PRO_Log::trace("Already copied installer on previous execution of SFTP $this->name so skipping");
                    }

                    if ($upload_info->copied_archive == false) {
                        $global = DUP_PRO_Global_Entity::getInstance();
                        if ($disable_chunking_mode) {
                            DUP_PRO_Log::trace('SFTP chunking mode is disabled.');
                            $time_threshold = -1;
                        } else {
                            //Make sure time threshold not exceed the server maximum execution time
                            $time_threshold = $global->php_max_worker_time_in_sec;
                            if (isset($this->sftp_timeout_in_secs)) {
                                $time_threshold = $this->sftp_timeout_in_secs;
                            }
                            DUP_PRO_Log::trace('SFTP chunking mode is enabled, so setting the time_threshold=' . $time_threshold);
                        }

                        $source_filepath = $source_archive_filepath;
                        $basename        = basename($source_filepath);

                        $continueWithUpload = false;
                        try {
                            $continueWithUpload = true;
                            $sFtpAdapter->startChunkingTimer($time_threshold);
                            $upload_info->archive_offset = $sFtpAdapter->filesize($storage_folder . $basename);
                            if (!$sFtpAdapter->put($storage_folder . $basename, $source_filepath, $upload_info->archive_offset)) {
                                $upload_info->failed = true;
                                $continueWithUpload  = false;
                                DUP_PRO_Log::infoTrace("FAIL: archive upload to SFTP.");
                            }
                        } catch (ChunkingTimeoutException $e) {
                            $continueWithUpload = true;
                        }

                        if ($continueWithUpload) {
                            $file_size             = filesize($source_filepath);
                            $upload_info->progress = max(
                                5,
                                DUP_PRO_U::percentage($upload_info->archive_offset, $file_size, 0)
                            );

                            DUP_PRO_Log::infoTrace("Archive upload offset: $upload_info->archive_offset [File size: $file_size] [Upload progress: $upload_info->progress%]");
                            if ($upload_info->progress >= 100) {
                                $upload_info->copied_archive = true;
                                DUP_PRO_Log::infoTrace("SUCCESS: archive upload to SFTP.");
                                if ($this->sftp_max_files > 0) {
                                    $this->purge_old_sftp_packages();
                                }
                            }
                        }

                        // The package update will automatically capture the upload_info since its part of the package
                        $package->update();
                    } else {
                        DUP_PRO_Log::trace("Already copied archive on previous execution of SFTP $this->name so skipping");
                    }

                    if ($upload_info->failed) {
                        $source_filepath = $source_archive_filepath;
                        $basename        = basename($source_filepath);
                        $sFtpAdapter->delete($storage_folder . $basename);

                        $source_filepath = $source_installer_filepath;
                        $basename        = basename($source_filepath);
                        $sFtpAdapter->delete($storage_folder . $basename);
                    } else {
                        $upload_info->failure_count = 0;
                    }
                } catch (Exception $e) {
                    $upload_info->increase_failure_count();
                    DUP_PRO_Log::trace("Exception caught copying package $package->Name to $this->sftp_storage_folder. " . $e->getMessage());
                }
            } else {
                DUP_PRO_Log::traceError("Installer doesn't exist for $package->Name!? - $source_installer_filepath");
                $upload_info->failed = true;
            }
        } else {
            DUP_PRO_Log::traceError("Archive doesn't exist for $package->Name!? - $source_archive_filepath");
            $upload_info->failed = true;
        }

        if ($upload_info->failed) {
            DUP_PRO_Log::infoTrace('SFTP storage failed flag ($upload_info->failed) has been already set.');
        }

        // The package update will automatically capture the upload_info since its part of the package
        $package->update();
    }

    public function dropbox_compare_file_dates($a, $b)
    {
        $a_ts = strtotime($a->modified);
        $b_ts = strtotime($b->modified);
        if ($a_ts == $b_ts) {
            return 0;
        }

        return ($a_ts < $b_ts) ? -1 : 1;
    }

    public static function s3_compare_file_dates($array_a, $array_b)
    {
        $a_ts = strtotime($array_a['LastModified']);
        $b_ts = strtotime($array_b['LastModified']);
        if ($a_ts == $b_ts) {
            return 0;
        }

        return ($a_ts < $b_ts) ? -1 : 1;
    }

    /**
     * Returns value => label pairs for region drop-down options for S3 Amazon Direct storage type
     *
     * @return string[]
     */
    public static function s3_amazon_direct_region_options()
    {
        return array(
            "us-east-1"  => __("US East (N. Virginia)", 'duplicator-pro'),
            "us-east-2" => __("US East (Ohio)", 'duplicator-pro'),
            "us-west-1" => __("US West (N. California)", 'duplicator-pro'),
            "us-west-2" => __("US West (Oregon)", 'duplicator-pro'),
            "af-south-1" => __("Africa (Cape Town)", 'duplicator-pro'),
            "ap-east-1" => __("Asia Pacific (Hong Kong)", 'duplicator-pro'),
            "ap-south-1" => __("Asia Pacific (Mumbai)", 'duplicator-pro'),
            "ap-northeast-1" => __("Asia Pacific (Tokyo)", 'duplicator-pro'),
            "ap-northeast-2" => __("Asia Pacific (Seoul)", 'duplicator-pro'),
            "ap-northeast-3" => __("Asia Pacific (Osaka-Local)", 'duplicator-pro'),
            "ap-southeast-1" => __("Asia Pacific (Singapore)", 'duplicator-pro'),
            "ap-southeast-2" => __("Asia Pacific (Sydney)", 'duplicator-pro'),
            "ap-southeast-3" => __("Asia Pacific (Jakarta)", 'duplicator-pro'),
            "ca-central-1" => __("Canada (Central)", 'duplicator-pro'),
            "cn-north-1" => __("China (Beijing)", 'duplicator-pro'),
            "cn-northwest-1" => __("China (Ningxia)", 'duplicator-pro'),
            "eu-central-1" => __("EU (Frankfurt)", 'duplicator-pro'),
            "eu-west-1" => __("EU (Ireland)", 'duplicator-pro'),
            "eu-west-2" => __("EU (London)", 'duplicator-pro'),
            "eu-west-3" => __("EU (Paris)", 'duplicator-pro'),
            "eu-south-1" => __("Europe (Milan)", 'duplicator-pro'),
            "eu-north-1" => __("Europe (Stockholm)", 'duplicator-pro'),
            "me-south-1" => __("Middle East (Bahrain)", 'duplicator-pro'),
            "sa-east-1" => __("South America (Sao Paulo)", 'duplicator-pro')
        );
    }

    public static function onedrive_compare_file_dates($a, $b)
    {
        $act = (int)$a['created_time'];
        $bct = (int)$b['created_time'];
        if ($act == $bct) {
            return 0;
        }

        return ($act < $bct ? -1 : 1);
    }

    /**
     * Returns the FontAwesome storage type icon.
     *
     * @param int $id An id based on the PHP class DUP_PRO_Storage_Types
     *
     * @return string Returns the font-awesome icon
     *
     * @see also Duplicator.Storage.getFontAwesomeIcon
     */
    public static function getStorageIcon($id)
    {
        $html = '';
        switch ($id) {
            case DUP_PRO_Storage_Types::Local:
                $html = '<i class="fas fa-hdd fa-fw"></i>';
                break;
            case DUP_PRO_Storage_Types::Dropbox:
                $html = '<i class="fab fa-dropbox fa-fw"></i>';
                break;
            case DUP_PRO_Storage_Types::FTP:
                $html = '<i class="fas fa-network-wired fa-fw"></i>';
                break;
            case DUP_PRO_Storage_Types::GDrive:
                $html = '<i class="fab fa-google-drive fa-fw"></i>';
                break;
            case DUP_PRO_Storage_Types::S3:
                $html = '<i class="fab fa-aws fa-fw"></i>';
                break;
            case DUP_PRO_Storage_Types::SFTP:
                $html = '<i class="fas fa-network-wired fa-fw"></i>';
                break;
            case DUP_PRO_Storage_Types::OneDrive:
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                $html = '<i class="fas fa-cloud fa-fw"></i>';
                break;
            default:
                $html = '<i class="fas fa-cloud fa-fw"></i>';
                break;
        }
        return $html;
    }

    public function newest_local_file($a, $b)
    {
        return filemtime($a) - filemtime($b);
    }

    public function purge_old_local_packages()
    {
        if ($this->local_max_files <= 0) {
            return ;
        }

        global $wpdb;
        try {
            $fileList = \Duplicator\Libs\Snap\SnapIO::regexGlob($this->local_storage_folder, array(
                'regexFile'   => array(
                    DUPLICATOR_PRO_ARCHIVE_REGEX_PATTERN
                ),
                'regexFolder' => false
            ));
            //Sort by creation time
            usort($fileList, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            $nameHashesToPurge = array();
            for ($i = 0; $i < count($fileList) - $this->local_max_files; $i++) {
                $nameHash            = substr(basename($fileList[$i]), 0, -12);
                $nameHashesToPurge[] = $wpdb->prepare("%s", $nameHash);
                DUP_PRO_Package::deletePackageFilesInDir($nameHash, $this->local_storage_folder, true);
            }

            // Purge package record logic
            if (isset($this->purge_package_record) && $this->purge_package_record && count($nameHashesToPurge) > 0) {
                $table       = $wpdb->base_prefix . "duplicator_pro_packages";
                $max_created = $wpdb->get_var("SELECT max(created) FROM " . $table . " WHERE concat_ws('_', name, hash) IN (" . implode(', ', $nameHashesToPurge) . ")");
                $sql         = $wpdb->prepare("DELETE FROM " . $table . " WHERE created <= %s AND status = %d", $max_created, 100);
                $wpdb->query($sql);
            }
        } catch (Exception $e) {
            DUP_PRO_Log::traceError("FAIL: purging local packages. Exception message: " . $e->getMessage());
        }
    }

    public function purge_old_dropbox_packages($dropbox)
    {
        try {
            $global    = DUP_PRO_Global_Entity::getInstance();
            $file_list = $dropbox->GetFiles($this->dropbox_storage_folder);
            usort($file_list, array(__CLASS__, 'dropbox_compare_file_dates'));
            $php_filenames     = array();
            $archive_filenames = array();
            foreach ($file_list as $file_metadata) {
                if (DUP_PRO_STR::endsWith($file_metadata->file_path, "_{$global->installer_base_name}")) {
                    array_push($php_filenames, $file_metadata);
                } elseif (DUP_PRO_STR::endsWith($file_metadata->file_path, '_archive.zip') || DUP_PRO_STR::endsWith($file_metadata->file_path, '_archive.daf')) {
                    array_push($archive_filenames, $file_metadata);
                }
            }

            if ($this->dropbox_max_files > 0) {
                $num_php_files     = count($php_filenames);
                $num_php_to_delete = $num_php_files - $this->dropbox_max_files;
                $index             = 0;
                DUP_PRO_Log::trace("Num php files to delete=$num_php_to_delete");
                while ($index < $num_php_to_delete) {
                    $dropbox->Delete($php_filenames[$index]->file_path);
                    $index++;
                }

                $index                  = 0;
                $num_archives           = count($archive_filenames);
                $num_archives_to_delete = $num_archives - $this->dropbox_max_files;
                DUP_PRO_Log::trace("Num archives to delete=$num_archives_to_delete");
                while ($index < $num_archives_to_delete) {
                    $dropbox->Delete($archive_filenames[$index]->file_path);
                    $index++;
                }
            }
        } catch (Exception $e) {
            DUP_PRO_Log::traceError("FAIL: purging DropBox packages. " . $e->getMessage());
        }
    }

    public function purge_old_gdrive_packages($google_client, $upload_info)
    {
        if ($this->gdrive_max_files > 0) {
            $global               = DUP_PRO_Global_Entity::getInstance();
            $directory_id         = $upload_info->data;
            $google_service_drive = new Duplicator_Pro_Google_Service_Drive($google_client);
            $file_list            = DUP_PRO_GDrive_U::get_files_in_directory($google_service_drive, $directory_id);
            if ($file_list != null) {
                $php_files         = array();
                $archive_filenames = array();
                foreach ($file_list as $drive_file) {
                    $file_title = $drive_file->getName();
                    if (DUP_PRO_STR::endsWith($file_title, "_{$global->installer_base_name}")) {
                        array_push($php_files, $drive_file);
                    } elseif (DUP_PRO_STR::endsWith($file_title, '_archive.zip') || DUP_PRO_STR::endsWith($file_title, '_archive.daf')) {
                        array_push($archive_filenames, $drive_file);
                    }
                }

                $index                  = 0;
                $num_archives           = count($archive_filenames);
                $num_archives_to_delete = $num_archives - $this->gdrive_max_files;
                DUP_PRO_Log::trace("Num zip files to delete=$num_archives_to_delete since there are $num_archives on the drive and max files={$this->gdrive_max_files}");
                while ($index < $num_archives_to_delete) {
                    $archive_file  = $archive_filenames[$index];
                    $archive_title = $archive_file->getName();
                    // Matching installer has to be present for us to delete
                    if (DUP_PRO_STR::endsWith($archive_title, '_archive.zip')) {
                        $installer_title = str_replace('_archive.zip', "_{$global->installer_base_name}", $archive_title);
                    } else {
                        $installer_title = str_replace('_archive.daf', "_{$global->installer_base_name}", $archive_title);
                    }

                    // Now get equivalent installer
                    foreach ($php_files as $installer_file) {
                        /* @var $installer_file Duplicator_Pro_Google_Service_Drive_DriveFile */

                        if ($installer_title == $installer_file->getName()) {
                            DUP_PRO_Log::trace("Attempting to delete $installer_title from Google Drive");
                            if (DUP_PRO_GDrive_U::delete_file($google_service_drive, $installer_file->getid()) == false) {
                                DUP_PRO_Log::traceError("FAIL: purging Google Drive packages. Error purging old Google Drive file $installer_title");
                            }

                            DUP_PRO_Log::trace("Attempting to delete $archive_title from Google Drive");
                            if (DUP_PRO_GDrive_U::delete_file($google_service_drive, $archive_file->getid()) == false) {
                                DUP_PRO_Log::traceError("FAIL: purging Google Drive packages. Error in purging old Google Drive file $archive_title");
                            }
                            break;
                        }
                    }

                    $index++;
                }
            } else {
                $message = "ERROR: Couldn't retrieve file list from Google Drive so can purge old packages";
                DUP_PRO_Log::traceError("FAIL: purging Google Drive packages. " . $message);
                $upload_info->failed = true;
            }
        }
    }

    public function purge_old_s3_packages($s3_client)
    {
        /* @var $s3_client DuplicatorPro\Aws\S3\S3Client */
        try {
            $global = DUP_PRO_Global_Entity::getInstance();

            /* @var DuplicatorPro\Guzzle\Service\Resource\Model */
            // listObjects works fine for root folder only if Prefix is set to an empty string.
            $prefix       = (trim($this->s3_storage_folder, '/') == "") ? "" : trim($this->s3_storage_folder, '/') . '/';
            $return_value = $s3_client->listObjects(array(
                'Bucket' => $this->s3_bucket,
                'Delimiter' => '/',
                'Prefix' => $prefix
            ));

            if (!isset($return_value['Contents']) || !is_array($return_value['Contents'])) {
                update_option(DUP_PRO_UI_Notice::OPTION_KEY_S3_CONTENTS_FETCH_FAIL_NOTICE, true);
                return false;
            }

            $s3_objects = $return_value['Contents'];
            usort($s3_objects, array(__CLASS__, 's3_compare_file_dates'));

            $php_files         = array();
            $archive_filenames = array();
            foreach ($s3_objects as $s3_object) {
                $filename = basename($s3_object['Key']);
                if (DUP_PRO_STR::endsWith($filename, "_{$global->installer_base_name}")) {
                    array_push($php_files, $s3_object['Key']);
                } elseif (DUP_PRO_STR::endsWith($filename, '_archive.zip') || DUP_PRO_STR::endsWith($filename, '_archive.daf')) {
                    array_push($archive_filenames, $s3_object['Key']);
                }
            }

            DUP_PRO_Log::traceObject("php files", $php_files);
            DUP_PRO_Log::traceObject("archives", $archive_filenames);
            if ($this->s3_max_files > 0) {
                $num_php_files     = count($php_files);
                $num_php_to_delete = $num_php_files - $this->s3_max_files;
                $index             = 0;
                DUP_PRO_Log::trace("Num php files to delete=$num_php_to_delete");
                while ($index < $num_php_to_delete) {
                    DUP_PRO_Log::trace("Deleting {$php_files[$index]}");
                    $s3_client->deleteObject(array(
                        'Bucket' => $this->s3_bucket,
                        'Key' => $php_files[$index]
                    ));
                    DUP_PRO_Log::trace("Deleted {$php_files[$index]}");
                    $index++;
                }

                $index                  = 0;
                $num_archives           = count($archive_filenames);
                $num_archives_to_delete = $num_archives - $this->s3_max_files;
                DUP_PRO_Log::trace("Num archives to delete=$num_archives_to_delete");
                while ($index < $num_archives_to_delete) {
                    DUP_PRO_Log::trace("Deleting {$archive_filenames[$index]}");
                    $s3_client->deleteObject(array(
                        'Bucket' => $this->s3_bucket,
                        'Key' => $archive_filenames[$index]
                    ));
                    DUP_PRO_Log::trace("Deleting {$archive_filenames[$index]}");
                    $index++;
                }
            }
        } catch (Exception $e) {
            DUP_PRO_Log::traceError("FAIL: purging Google Drive packages. " . $e->getMessage());
        }
    }

    private static function get_timestamp_from_filename($filename)
    {
        $retval = false;
        $global = DUP_PRO_Global_Entity::getInstance();
        if ((DUP_PRO_STR::endsWith($filename, "_{$global->installer_base_name}")) || (DUP_PRO_STR::endsWith($filename, '_archive.zip')) || (DUP_PRO_STR::endsWith($filename, '_archive.daf'))) {
            $pieces      = explode('_', $filename);
            $piece_count = count($pieces);
            if ($piece_count >= 4) {
                $numeric_index = count($pieces) - 2;
                // Right before the _installer or _archive
                if (is_numeric($pieces[$numeric_index])) {
                    $retval = (float)$pieces[$numeric_index];
                } else {
                    DUP_PRO_Log::trace("Problem parsing file $filename when doing a comparison for ftp purge. Non-numeric timestamp");
                    $retval = false;
                }
            } else {
                DUP_PRO_Log::trace("Problem parsing file $filename when doing a comparison for ftp purge");
                $retval = false;
            }
        } else {
            $retval = false;
        }

        return $retval;
    }

    public static function compare_package_filenames_by_date($filename_a, $filename_b)
    {
        $ret_val = 0;
        // Should be in the format uniqueid_2digityear
        $a_timestamp = self::get_timestamp_from_filename($filename_a);
        $b_timestamp = self::get_timestamp_from_filename($filename_b);
        DUP_PRO_Log::trace("comparing a:$a_timestamp to b:$b_timestamp");
        if ($a_timestamp === false || $b_timestamp === false) {
            throw new Exception('Invalid timestamp for sorting');
        }
        if ($a_timestamp > $b_timestamp) {
            $ret_val = 1;
        } elseif ($a_timestamp < $b_timestamp) {
            $ret_val = -1;
        } else {
            $ret_val = 0;
        }
        return $ret_val;
    }

    public function purge_old_sftp_packages()
    {
        try {
            $storage_folder       = $this->sftp_storage_folder;
            $server               = $this->sftp_server;
            $port                 = $this->sftp_port;
            $username             = $this->sftp_username;
            $password             = $this->sftp_password;
            $private_key          = $this->sftp_private_key;
            $private_key_password = $this->sftp_private_key_password;
            if (DUP_PRO_STR::startsWith($storage_folder, '/') == false) {
                $storage_folder = '/' . $storage_folder;
            }

            if (DUP_PRO_STR::endsWith($storage_folder, '/') == false) {
                $storage_folder = $storage_folder . '/';
            }

            $sFtpAdapter = new SFTPAdapter($server, $port, $username, $password, $private_key, $private_key_password);
            if (!$sFtpAdapter->connect()) {
                throw new Exception('Connction fail');
            }

            $storage_folder = $this->sftp_storage_folder;
            if (DUP_PRO_STR::startsWith($storage_folder, '/') == false) {
                $storage_folder = '/' . $storage_folder;
            }
            if (DUP_PRO_STR::endsWith($storage_folder, '/') == false) {
                $storage_folder = $storage_folder . '/';
            }
            $global    = DUP_PRO_Global_Entity::getInstance();
            $file_list = $sFtpAdapter->filesList($storage_folder);
            $file_list = array_diff($file_list, array(".", ".."));
            if (empty($file_list)) {
                DUP_PRO_Log::traceError(
                    "FAIL: purging SFTP packages. Problems making SFTP connection, Purging old packages not possible. Error retrieving file list for " .
                    $this->sftp_server . ":" . $this->sftp_port . " Storage Dir: " . $this->sftp_storage_folder
                );
            } else {
                $valid_file_list = array();
                foreach ($file_list as $file_name) {
                    DUP_PRO_Log::trace("considering filename {$file_name}");
                    if (self::get_timestamp_from_filename($file_name) !== false) {
                        $valid_file_list[] = $file_name;
                    }
                }

                DUP_PRO_Log::traceObject('valid file list', $valid_file_list);
                try {
                    // Sort list by the timestamp associated with it
                    usort($valid_file_list, array(__CLASS__, 'compare_package_filenames_by_date'));
                } catch (Exception $e) {
                    DUP_PRO_Log::trace("Sort error when attempting to purge old FTP files");
                    return;
                }

                $php_files         = array();
                $archive_filepaths = array();
                foreach ($valid_file_list as $file_name) {
                    $file_path = "$this->sftp_storage_folder/$file_name";
                    // just look for the archives and delete only if has matching _installer
                    if (DUP_PRO_STR::endsWith($file_path, "_{$global->installer_base_name}")) {
                        array_push($php_files, $file_path);
                    } elseif (DUP_PRO_STR::endsWith($file_path, '_archive.zip') || DUP_PRO_STR::endsWith($file_path, '_archive.daf')) {
                        array_push($archive_filepaths, $file_path);
                    }
                }

                if ($this->sftp_max_files > 0) {
                    $index                  = 0;
                    $num_archives           = count($archive_filepaths);
                    $num_archives_to_delete = $num_archives - $this->sftp_max_files;
                    DUP_PRO_Log::trace("Num archives to delete=$num_archives_to_delete");
                    while ($index < $num_archives_to_delete) {
                        $archive_filepath = $archive_filepaths[$index];
                        // Matching installer has to be present for us to delete
                        if (DUP_PRO_STR::endsWith($archive_filepath, '_archive.zip')) {
                            $installer_filepath = str_replace('_archive.zip', "_{$global->installer_base_name}", $archive_filepath);
                        } else {
                            $installer_filepath = str_replace('_archive.daf', "_{$global->installer_base_name}", $archive_filepath);
                        }

                        if (in_array($installer_filepath, $php_files)) {
                            DUP_PRO_Log::trace("$installer_filepath in array so deleting installer and archive");
                            $sFtpAdapter->delete($installer_filepath);
                            $sFtpAdapter->delete($archive_filepath);
                        } else {
                            DUP_PRO_Log::trace("$installer_filepath not in array so NOT deleting");
                        }

                        $index++;
                    }
                }
            }
        } catch (Exception $e) {
            DUP_PRO_Log::traceError("FAIL: purging SFTP packages. Problems making SFTP connection, Purging old packages not possible.");
        }
    }

    public function purge_old_ftp_packages($ftp_client)
    {
        $global    = DUP_PRO_Global_Entity::getInstance();
        $file_list = $ftp_client->get_filelist($this->ftp_storage_folder);
        if ($file_list == false) {
            DUP_PRO_Log::traceError("FAIL: purging FTP packages. Error retrieving file list for " . $ftp_client->get_info());
        } else {
            $valid_file_list = array();
            foreach ($file_list as $file_name) {
                DUP_PRO_Log::trace("considering filename {$file_name}");
                if (self::get_timestamp_from_filename($file_name) !== false) {
                    $valid_file_list[] = $file_name;
                }
            }

            DUP_PRO_Log::traceObject('valid file list', $valid_file_list);
            try {
                // Sort list by the timestamp associated with it
                usort($valid_file_list, array(__CLASS__, 'compare_package_filenames_by_date'));
            } catch (Exception $e) {
                DUP_PRO_Log::traceError("FAIL: purging FTP packages. Sort error when attempting to purge old FTP files");
                return;
            }

            $php_files         = array();
            $archive_filepaths = array();
            foreach ($valid_file_list as $file_name) {
                if ($this->ftp_use_curl) {
                    $file_path = $file_name;
                } else {
                    $file_path = rtrim($this->ftp_storage_folder, '/') . '/' . $file_name;
                }
                // just look for the archives and delete only if has matching _installer
                if (DUP_PRO_STR::endsWith($file_path, "_{$global->installer_base_name}")) {
                    array_push($php_files, $file_path);
                } elseif (DUP_PRO_STR::endsWith($file_path, '_archive.zip') || DUP_PRO_STR::endsWith($file_path, '_archive.daf')) {
                    array_push($archive_filepaths, $file_path);
                }
            }

            if ($this->ftp_max_files > 0) {
                $index                  = 0;
                $num_archives           = count($archive_filepaths);
                $num_archives_to_delete = $num_archives - $this->ftp_max_files;
                DUP_PRO_Log::trace("Num archives to delete=$num_archives_to_delete");
                while ($index < $num_archives_to_delete) {
                    $archive_filepath = $archive_filepaths[$index];
                    // Matching installer has to be present for us to delete
                    if (DUP_PRO_STR::endsWith($archive_filepath, '_archive.zip')) {
                        $installer_filepath = str_replace('_archive.zip', "_{$global->installer_base_name}", $archive_filepath);
                    } else {
                        $installer_filepath = str_replace('_archive.daf', "_{$global->installer_base_name}", $archive_filepath);
                    }

                    if (in_array($installer_filepath, $php_files)) {
                        DUP_PRO_Log::trace("$installer_filepath in array so deleting installer and archive");
                        $ftp_client->delete($installer_filepath);
                        $ftp_client->delete($archive_filepath);
                    } else {
                        DUP_PRO_Log::trace("$installer_filepath not in array so NOT deleting");
                    }

                    $index++;
                }
            }
        }
    }

    private static function get_ak1()
    {
        return strrev('i6gh72iv');
    }

    private static function get_ak2()
    {
        return strrev('1xgkhw2');
    }

    private static function get_as1()
    {
        return strrev('z7fl2twoo');
    }

    private static function get_as2()
    {
        return strrev('2z2bfm');
    }

    /**
     * Get storage type val from DUP_PRO_Storage_Types
     *
     * @return int storage type val from DUP_PRO_Storage_Types
     */
    public function get_storage_type()
    {
        return $this->storage_type;
    }

    public function get_storage_type_string()
    {
        switch ($this->get_storage_type()) {
            case DUP_PRO_Storage_Types::Dropbox:
                return DUP_PRO_U::__('Dropbox');
            case DUP_PRO_Storage_Types::FTP:
                return DUP_PRO_U::__('FTP');
            case DUP_PRO_Storage_Types::SFTP:
                return DUP_PRO_U::__('SFTP');
            case DUP_PRO_Storage_Types::GDrive:
                return DUP_PRO_U::__('Google Drive');
            case DUP_PRO_Storage_Types::Local:
                return DUP_PRO_U::__('Local');
            case DUP_PRO_Storage_Types::S3:
                return $this->s3_is_amazon() ? DUP_PRO_U::__('Amazon S3') : DUP_PRO_U::__('S3-Compatible (Generic)');
            case DUP_PRO_Storage_Types::OneDrive:
                return !$this->onedrive_is_business() ? DUP_PRO_U::__('OneDrive v0.1') : DUP_PRO_U::__('OneDrive v0.1 (B)');
            case DUP_PRO_Storage_Types::OneDriveMSGraph:
                return DUP_PRO_U::__('OneDrive');
            default:
                return DUP_PRO_U::__('Unknown');
        }
    }

    public function is_authorized()
    {
        switch ($this->storage_type) {
            case DUP_PRO_Storage_Types::Dropbox:
                return $this->dropbox_authorization_state;
            case DUP_PRO_Storage_Types::GDrive:
                return $this->gdrive_authorization_state;
            case DUP_PRO_Storage_Types::OneDrive:
                return $this->onedrive_authorization_state;
            default:
                return true;
        }
    }

    /**
     * Save
     *
     * @return bool
     */
    public function save()
    {
        if (DUP_PRO_STR::startsWith($this->ftp_storage_folder, '/') == false) {
            $this->ftp_storage_folder = '/' . $this->ftp_storage_folder;
        }

        if (DUP_PRO_STR::startsWith($this->sftp_storage_folder, '/') == false) {
            $this->sftp_storage_folder = '/' . $this->sftp_storage_folder;
        }

        $global = DUP_PRO_Global_Entity::getInstance();
        if ($global->crypt) {
            $this->encrypt();
        }

        // The version of this endpoint is equivalent to the Duplicator Pro version that it was part of when saved
        $this->version = DUPLICATOR_PRO_VERSION;
        $result        = parent::save();
        if ($global->crypt) {
            $this->decrypt();
            // Whenever its in memory its unencrypted
        }
        return $result;
    }

    // Get a list of the permanent entries
    public static function get_default_local_storage()
    {
        $global                                      = DUP_PRO_Global_Entity::getInstance();
        $default_local_storage                       = new self();
        $default_local_storage->name                 = DUP_PRO_U::__('Default');
        $default_local_storage->notes                = DUP_PRO_U::__('The default location for storage on this server.');
        $default_local_storage->id                   = DUP_PRO_Virtual_Storage_IDs::Default_Local;
        $default_local_storage->storage_type         = DUP_PRO_Storage_Types::Local;
        $default_local_storage->local_storage_folder = DUPLICATOR_PRO_SSDIR_PATH;
        $default_local_storage->local_max_files      = $global->max_default_store_files;
        $default_local_storage->purge_package_record = $global->purge_default_package_record;
        $default_local_storage->editable             = false;
        return $default_local_storage;
    }

    /**
     * Checks if the storage path is already used by another local storage
     *
     * @return bool Whether the storage path is already used by another local storage
     */
    public function is_path_repeated()
    {
        $storages = self::get_all();
        $path     = SnapIO::safePathTrailingslashit($this->local_storage_folder, true);
        foreach ($storages as $storage) {
            if (
                $storage->get_storage_type() != DUP_PRO_Storage_Types::Local ||
                $storage->id == $this->id
            ) {
                continue;
            }
            if ($path === SnapIO::safePathTrailingslashit($storage->get_storage_location_string(), true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Purge old S3 multipart uploads
     *
     * @return void
     */
    public static function purgeOldS3MultipartUploads()
    {
        $storages = self::get_all();

        foreach ($storages as $storage) {
            if ($storage->storage_type == DUP_PRO_Storage_Types::S3) {
                $s3_client = $storage->get_full_s3_client();

                $active_uploads = DUP_PRO_S3_U::get_active_multipart_uploads(
                    $s3_client,
                    $storage->s3_bucket,
                    $storage->s3_storage_folder
                );

                if (($active_uploads != null) && is_array($active_uploads)) {
                    foreach ($active_uploads as $active_upload) {
                        // Needs to be at least 48 hours old - don't want to much around with timezone so this is safe
                        $time_delta = time() - $active_upload->timestamp;

                        if ($time_delta > (48 * 3600)) {
                            DUP_PRO_Log::trace("Aborting upload because timestamp = {$active_upload->timestamp} while time is " . time());
                            DUP_PRO_S3_U::abort_multipart_upload(
                                $s3_client,
                                $storage->s3_bucket,
                                $active_upload->key,
                                $active_upload->upload_id
                            );
                        }
                    }
                }
            }
        }
    }
}
