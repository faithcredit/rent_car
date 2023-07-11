<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use Duplicator\Core\Models\AbstractEntityList;
use Duplicator\Core\Models\UpdateFromInputInterface;
use Duplicator\Installer\Core\Descriptors\ArchiveConfig;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Package\Recovery\RecoveryStatus;
use Duplicator\Utils\Crypt\CryptBlowfish;

class DUP_PRO_Package_Template_Entity extends AbstractEntityList implements UpdateFromInputInterface
{
    public $name  = '';
    public $notes = '';
    //MULTISITE:Filter
    public $filter_sites = array();
    //ARCHIVE:Files
    public $archive_export_onlydb = 0;
    public $archive_filter_on     = 0;
    public $archive_filter_dirs   = '';
    public $archive_filter_exts   = '';
    public $archive_filter_files  = '';
    public $archive_filter_names  = false;
    //ARCHIVE:Database
    public $database_filter_on      = 0;  // Enable Table Filters
    public $databasePrefixFilter    = false;  // If true exclude tables without prefix
    public $databasePrefixSubFilter = false;  // If true exclude unexisting subsite id tables

    public $database_filter_tables       = ''; // List of filtered tables
    public $database_compatibility_modes = array(); // Older style sql compatibility
    //INSTALLER
    //Setup
    public $installer_opts_secure_on   = 0;  // Enable Password Protection
    public $installer_opts_secure_pass = ''; // Old password Protection password, deprecated
    public $installerPassowrd          = ''; // Password Protection password
    public $installer_opts_skip_scan   = 0;  // Skip Scanner
    //Basic DB
    public $installer_opts_db_host = '';   // MySQL Server Host
    public $installer_opts_db_name = '';   // Database
    public $installer_opts_db_user = '';   // User
    //cPanel Login
    public $installer_opts_cpnl_enable = false;
    public $installer_opts_cpnl_host   = '';
    public $installer_opts_cpnl_user   = '';
    public $installer_opts_cpnl_pass   = '';
    //cPanel DB
    public $installer_opts_cpnl_db_action = 'create';
    public $installer_opts_cpnl_db_host   = '';
    public $installer_opts_cpnl_db_name   = '';
    public $installer_opts_cpnl_db_user   = '';
    //Brand
    public $installer_opts_brand = -2;
    public $is_default           = false;
    public $is_manual            = false;

    public function __construct()
    {
        $this->name = DUP_PRO_U::__('New Template');
    }

    /**
     * Entity type
     *
     * @return string
     */
    public static function getType()
    {
        return 'DUP_PRO_Package_Template_Entity';
    }

    /**
     * Will be called, automatically, when Serialize
     *
     * @return array
     */
    public function __serialize()
    {
        $data                               = JsonSerialize::serializeToData($this, JsonSerialize::JSON_SKIP_MAGIC_METHODS |  JsonSerialize::JSON_SKIP_CLASS_NAME);
        $data['installer_opts_secure_pass'] = '';
        $data['installerPassowrd']          = CryptBlowfish::encrypt($this->installerPassowrd, null, true);
        return $data;
    }

    /**
     * Serialize
     *
     * Will be called, automatically, when unserialize() is called on a BigInteger object.
     *
     * @return void
     */
    public function __wakeup()
    {
        /*if ($obj->installer_opts_secure_on == ArchiveConfig::SECURE_MODE_ARC_ENCRYPT && !SettingsUtils::isArchiveEncryptionAvaiable()) {
            $obj->installer_opts_secure_on = ArchiveConfig::SECURE_MODE_INST_PWD;
        }*/

        if (strlen($this->installer_opts_secure_pass) > 0) {
            $this->installerPassowrd = base64_decode($this->installer_opts_secure_pass);
        } elseif (strlen($this->installerPassowrd) > 0) {
            $this->installerPassowrd = CryptBlowfish::decrypt($this->installerPassowrd, null, true);
        }
        $this->installer_opts_secure_pass = '';
    }

    /**
     * Create from import data
     *
     * @param self $data input data
     *
     * @return self
     */
    public static function createFromImportData(self $data)
    {
        $instance = new self();
        $reflect  = new ReflectionClass($instance);
        $props    = $reflect->getProperties();

        foreach ($props as $prop) {
            if ($prop->getName() == 'id') {
                continue;
            }
            $prop->setAccessible(true);
            $value = $prop->getValue($data);
            $prop->setValue($instance, $value);
        }
        return $instance;
    }

    public static function create_default()
    {
        if (self::get_default_template() == null) {
            $template = new self();

            $template->name       = DUP_PRO_U::__('Default');
            $template->notes      = DUP_PRO_U::__('The default template.');
            $template->is_default = true;

            $template->save();
            DUP_PRO_Log::trace('Created default template');
        } else {
            // Update it
            DUP_PRO_Log::trace('Default template already exists so not creating');
        }
    }

    public static function create_manual()
    {
        if (self::get_manual_template() == null) {
            $template = new self();

            $template->name      = DUP_PRO_U::__('[Manual Mode]');
            $template->notes     = '';
            $template->is_manual = true;

            // Copy over the old temporary template settings into this - required for legacy manual
            $temp_package = DUP_PRO_Package::get_temporary_package(false);

            if ($temp_package != null) {
                DUP_PRO_Log::trace('SET TEMPLATE FROM TEMP PACKAGE pwd ' . $temp_package->Installer->passowrd);
                $template->filter_sites          = $temp_package->Multisite->FilterSites;
                $template->archive_export_onlydb = $temp_package->Archive->ExportOnlyDB;
                $template->archive_filter_on     = $temp_package->Archive->FilterOn;
                $template->archive_filter_dirs   = $temp_package->Archive->FilterDirs;
                $template->archive_filter_exts   = $temp_package->Archive->FilterExts;
                $template->archive_filter_files  = $temp_package->Archive->FilterFiles;
                $template->archive_filter_names  = $temp_package->Archive->FilterNames;

                $template->installer_opts_brand = $temp_package->Brand_ID;

                $template->database_filter_on           = $temp_package->Database->FilterOn;
                $template->databasePrefixFilter         = $temp_package->Database->prefixFilter;
                $template->databasePrefixSubFilter      = $temp_package->Database->prefixSubFilter;
                $template->database_filter_tables       = $temp_package->Database->FilterTables;
                $template->database_compatibility_modes = $temp_package->Database->Compatible;

                $template->installer_opts_db_host   = $temp_package->Installer->OptsDBHost;
                $template->installer_opts_db_name   = $temp_package->Installer->OptsDBName;
                $template->installer_opts_db_user   = $temp_package->Installer->OptsDBUser;
                $template->installer_opts_secure_on = $temp_package->Installer->OptsSecureOn;
                $template->installerPassowrd        = $temp_package->Installer->passowrd;
                $template->installer_opts_skip_scan = $temp_package->Installer->OptsSkipScan;

                $global = DUP_PRO_Global_Entity::getInstance();

                $global->manual_mode_storage_ids = array();

                foreach ($temp_package->get_storages() as $storage) {
                    /* @var $storage DUP_PRO_Storage_Entity */
                    array_push($global->manual_mode_storage_ids, $storage->id);
                }

                $global->save();
            }

            $template->save();
            DUP_PRO_Log::trace('Created manual mode template');
        } else {
            // Update it
            DUP_PRO_Log::trace('Manual mode template already exists so not creating');
        }
    }

    /**
     *
     * @return bool
     */
    public function isRecoveable()
    {
        $status = new RecoveryStatus($this);
        return $status->isRecoveable();
    }

    /**
     * Display HTML info
     *
     * @param bool $isList
     *
     * @return void
     */
    public function recoveableHtmlInfo($isList = false)
    {
        $template = $this;
        require DUPLICATOR____PATH . '/views/tools/templates/widget/recoveable-template-info.php';
    }

    /**
     * Set data from query input
     *
     * @param int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV, SnapUtil::INPUT_REQUEST
     *
     * @return bool true on success or false on failure
     */
    public function setFromInput($type)
    {
        $input = SnapUtil::getInputFromType($type);

        $this->setFromArrayKey(
            $input,
            function ($key, $val) {
                return (is_scalar($val) ? SnapUtil::sanitizeNSChars($val) : $val);
            }
        );

        $this->database_filter_tables = isset($input['dbtables-list']) ? SnapUtil::sanitizeNSCharsNewlineTrim($input['dbtables-list']) : '';

        if (isset($input['_archive_filter_dirs'])) {
            $post_filter_dirs          = SnapUtil::sanitizeNSChars($input['_archive_filter_dirs']);
            $this->archive_filter_dirs = DUP_PRO_Archive::parseDirectoryFilter($post_filter_dirs);
        } else {
            $this->archive_filter_dirs = '';
        }

        if (isset($input['_archive_filter_exts'])) {
            $post_filter_exts          = SnapUtil::sanitizeNSCharsNewlineTrim($input['_archive_filter_exts']);
            $this->archive_filter_exts = DUP_PRO_Archive::parseExtensionFilter($post_filter_exts);
        } else {
            $this->archive_filter_exts = '';
        }

        if (isset($input['_archive_filter_files'])) {
            $post_filter_files          = SnapUtil::sanitizeNSChars($input['_archive_filter_files']);
            $this->archive_filter_files = DUP_PRO_Archive::parseFileFilter($post_filter_files);
        } else {
            $this->archive_filter_files = '';
        }
        $this->filter_sites = !empty($input['_mu_exclude']) ? $input['_mu_exclude'] : '';

        //Archive
        $this->archive_export_onlydb   = isset($input['archive_export_onlydb']) ? 1 : 0;
        $this->archive_filter_on       = isset($input['archive_filter_on']) ? 1 : 0;
        $this->database_filter_on      = isset($input['dbfilter-on']) ? 1 : 0;
        $this->databasePrefixFilter    = isset($input['db-prefix-filter']) ? 1 : 0;
        $this->databasePrefixSubFilter = isset($input['db-prefix-sub-filter']) ? 1 : 0;
        $this->archive_filter_names    = isset($input['archive_filter_names']) ? 1 : 0;

        //Installer
        $this->installer_opts_secure_on = filter_input(INPUT_POST, 'secure-on', FILTER_VALIDATE_INT);
        switch ($this->installer_opts_secure_on) {
            case ArchiveConfig::SECURE_MODE_NONE:
            case ArchiveConfig::SECURE_MODE_INST_PWD:
            case ArchiveConfig::SECURE_MODE_ARC_ENCRYPT:
                break;
            default:
                throw new Exception(__('Select valid secure mode'));
        }
        $this->installer_opts_skip_scan   = isset($input['_installer_opts_skip_scan']) ? 1 : 0;
        $this->installer_opts_cpnl_enable = isset($input['installer_opts_cpnl_enable']) ? 1 : 0;

        $this->installerPassowrd = SnapUtil::sanitizeNSCharsNewline($input['secure-pass']);
        $this->notes             = SnapUtil::sanitizeNSCharsNewlineTrim($input['notes']);

        return true;
    }

    /**
     * Copy template from id
     *
     * @param int<0, max> $templateId template id
     *
     * @return void
     */
    public function copy_from_source_id($templateId)
    {
        if (($source = self::getById($templateId)) === false) {
            throw new Exception('Can\'t get tempalte id' . $templateId);
        }

        $skipProps = [
            'id',
            'is_manual',
            'is_default'
        ];

        $reflect = new ReflectionClass($this);
        $props   = $reflect->getProperties();

        foreach ($props as $prop) {
            if (in_array($prop->getName(), $skipProps)) {
                continue;
            }
            $prop->setAccessible(true);
            $prop->setValue($this, $prop->getValue($source));
        }

        $source_template_name = $source->is_manual ? DUP_PRO_U::__("Active Build Settings") : $source->name;
        $this->name           = sprintf(DUP_PRO_U::__('%1$s - Copy'), $source_template_name);
    }

    /**
     * Gets a list of core WordPress folders that have been filtered
     *
     * @return array    Returns and array of folders paths
     */
    public function getWordPressCoreFilteredFoldersList()
    {
        return array_intersect(explode(';', $this->archive_filter_dirs), DUP_PRO_U::getWPCoreDirs());
    }

    /**
     * Is any of the WordPress core folders in the folder filter list
     *
     * @return bool    Returns true if a WordPress core path is being filtered
     */
    public function isWordPressCoreFolderFiltered()
    {
        return count($this->getWordPressCoreFilteredFoldersList()) > 0;
    }

    /**
     * Get all entities of current type
     *
     * @param int<0, max>                          $page           current page, if $pageSize is 0 o 1 $pase is the offset
     * @param int<0, max>                          $pageSize       page size, 0 return all entities
     * @param callable                             $sortCallback   sort function on items result
     * @param callable                             $filterCallback filter on items result
     * @param array{'col': string, 'mode': string} $orderby        query ordder by
     *
     * @return static[]|false return entities list of false on failure
     */
    public static function getAll(
        $page = 0,
        $pageSize = 0,
        $sortCallback = null,
        $filterCallback = null,
        $orderby = ['col' => 'id', 'mode' => 'ASC']
    ) {
        if (is_null($sortCallback)) {
            $sortCallback = function (self $a, self $b) {
                if ($a->is_default) {
                    return -1;
                } elseif ($b->is_default) {
                    return 1;
                } else {
                    return strcasecmp($a->name, $b->name);
                }
            };
        }
        return parent::getAll($page, $pageSize, $sortCallback, $filterCallback, $orderby);
    }

    /**
     * Return list template json encoded data for javascript
     *
     * @return string
     */
    public static function getTemplatesFrontendListData()
    {
        $templates = self::getAll();
        return JsonSerialize::serialize($templates, JsonSerialize::JSON_SKIP_MAGIC_METHODS |  JsonSerialize::JSON_SKIP_CLASS_NAME);
    }

    /**
     * Get all entities of current type
     *
     * @param int<0, max> $page     current page, if $pageSize is 0 o 1 $pase is the offset
     * @param int<0, max> $pageSize page size, 0 return all entities
     *
     * @return static[]|false return entities list of false on failure
     */
    public static function getAllWithoutManualMode(
        $page = 0,
        $pageSize = 0
    ) {
        $filterManualCallback = function (self $obj) {
            return ($obj->is_manual === false);
        };
        return self::getAll($page, $pageSize, null, $filterManualCallback);
    }

    /**
     * Get default template if exists
     *
     * @return null|self
     */
    public static function get_default_template()
    {
        $templates = self::getAll();

        foreach ($templates as $template) {
            if ($template->is_default) {
                return $template;
            }
        }
        return null;
    }

    /**
     * Return manual template entity if exists
     *
     * @return null|self
     */
    public static function get_manual_template()
    {
        $templates = self::getAll();

        foreach ($templates as $template) {
            if ($template->is_manual) {
                return $template;
            }
        }

        return null;
    }
}
