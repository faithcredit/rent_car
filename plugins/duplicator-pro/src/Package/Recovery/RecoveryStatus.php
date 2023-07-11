<?php

namespace Duplicator\Package\Recovery;

use DUP_PRO_Package;
use DUP_PRO_Package_File_Type;
use DUP_PRO_Package_Importer;
use Exception;
use DUP_PRO_Virtual_Storage_IDs;
use DUP_PRO_Package_Template_Entity;
use DUP_PRO_Schedule_Entity;
use DUP_PRO_Storage_Entity;
use DUP_PRO_Storage_Types;
use DUP_PRO_U;
use Duplicator\Libs\Snap\SnapWP;

/**
 * Class RecoveryStatus
 *
 * This class is designed to help control the various stages and associates
 * that are used to keep track of the RecoveryPoint statuses
 */
class RecoveryStatus
{
    const TYPE_PACKAGE  = 'PACKAGE';
    const TYPE_SCHEDULE = 'SCHEDULE';
    const TYPE_TEMPLATE = 'TEMPLATE';

    /** @var DUP_PRO_Package|DUP_PRO_Package_Template_Entity|DUP_PRO_Schedule_Entity */
    protected $object = null;
    /** @var string */
    protected $objectType = '';
    /** @var ?array{dbonly: bool, filterDirs: string[], filterTables: string[]} */
    protected $filteredData = null;

    /** @var DUP_PRO_Package_Template_Entity|null */
    private $activeTemplate = null;

    /**
     * Class constructor
     *
     * @param DUP_PRO_Package|DUP_PRO_Package_Template_Entity|DUP_PRO_Schedule_Entity $object entity object
     */
    public function __construct($object)
    {
        if (!is_object($object)) {
            throw new Exception("Input must be of type object");
        }

        $this->object = $object;
        switch (get_class($object)) {
            case 'DUP_PRO_Package':
                $this->objectType = self::TYPE_PACKAGE;
                break;
            case 'DUP_PRO_Schedule_Entity':
                $this->objectType     = self::TYPE_SCHEDULE;
                $this->activeTemplate = DUP_PRO_Package_Template_Entity::getById($this->object->template_id);
                break;
            case 'DUP_PRO_Package_Template_Entity':
                $this->objectType     = self::TYPE_TEMPLATE;
                $this->activeTemplate = $this->object;
                break;
            default:
                throw new Exception('Object must be of a valid object');
        }

        // Init filtered data
        $this->getFilteredData();
    }

     /**
     * Get the literal type name based on the recovery status object being evaluated
     *
     * @return string     Returns the recovery status object type literal
     */
    public function getType()
    {
        return $this->objectType;
    }

    /**
     * Retgurn recovery status object
     *
     * @return DUP_PRO_Package|DUP_PRO_Package_Template_Entity|DUP_PRO_Schedule_Entity
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Get the type name based on the recovery status object being evaluated
     *
     * @return string     Returns the recovery status object type by name PACKAGE | SCHEDULE | TEMPLATE
     */
    public function getTypeLabel()
    {
        switch ($this->objectType) {
            case self::TYPE_PACKAGE:
                return self::TYPE_PACKAGE;
            case self::TYPE_SCHEDULE:
                return self::TYPE_SCHEDULE;
            case self::TYPE_TEMPLATE:
                return self::TYPE_TEMPLATE;
        }

        return '';
    }

    /**
     * Return true if current object is recoveable
     *
     * @return bool
     */
    public function isRecoveable()
    {
        if (
            $this->objectType == self::TYPE_PACKAGE &&
            version_compare($this->object->Version, DUP_PRO_Package_Importer::IMPORT_ENABLE_MIN_VERSION, '<')
        ) {
            return false;
        }

        return (
            $this->isLocalStorageEnabled() &&
            $this->isWordPressCoreComplete() &&
            $this->isDatabaseComplete()
        );
    }

    /**
     * Is the local storage type enabled for the various object types
     *
     * @return bool Returns true if the object type has a local default storage associated with it
     *
     * @notes:
     * Templates do not have local storage associations so the result will always be true for that type
     */
    public function isLocalStorageEnabled()
    {
        $isEnabled = false;

        switch ($this->objectType) {
            case self::TYPE_PACKAGE:
                $isEnabled = ($this->object->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive) !== false);
                break;
            case self::TYPE_SCHEDULE:
                if (in_array(DUP_PRO_Virtual_Storage_IDs::Default_Local, $this->object->storage_ids)) {
                     $isEnabled = true;
                } else {
                    foreach ($this->object->storage_ids as $id) {
                        $storage = DUP_PRO_Storage_Entity::get_by_id($id);
                        if ($storage->storage_type == DUP_PRO_Storage_Types::Local) {
                             $isEnabled = true;
                             break;
                        }
                    }
                }
                break;
            case self::TYPE_TEMPLATE:
                $isEnabled = true;
                break;
        }
        return $isEnabled;
    }

    /**
     * Is the object type filtering out any of the WordPress core directories
     *
     * @return bool     Returns true if the object type has all the proper WordPress core folders
     *
     * @notes:
     *  - The WP core directories include WP -> admin, content and includes
     */
    public function isWordPressCoreComplete()
    {
        return ($this->filteredData['dbonly'] == false && count($this->filteredData['filterDirs']) == 0);
    }

    /**
     * Is the object type filtering out any Database tables that have the WordPress prefix
     *
     * @return bool Returns true if the object type filters out any database tables
     */
    public function isDatabaseComplete()
    {
        return (count($this->filteredData['filterTables']) == 0);
    }

    /**
     * Return filtered datat from entity
     *
     * @return array{dbonly: bool, filterDirs: string[], filterTables: string[]}
     */
    public function getFilteredData()
    {
        if ($this->filteredData !== null) {
            return $this->filteredData;
        }
        $this->filteredData = array(
            'dbonly'       => false,
            'filterDirs'   => array(),
            'filterTables' => array()
        );

        switch ($this->objectType) {
            case self::TYPE_PACKAGE:
                $this->filteredData['dbonly'] = filter_var($this->object->Archive->ExportOnlyDB, FILTER_VALIDATE_BOOLEAN);

                if (filter_var($this->object->Archive->FilterOn, FILTER_VALIDATE_BOOLEAN) && strlen($this->object->Archive->FilterDirs) > 0) {
                    $filterDirs                       = explode(';', $this->object->Archive->FilterDirs);
                    $this->filteredData['filterDirs'] = array_intersect($filterDirs, DUP_PRO_U::getWPCoreDirs());
                }

                if (
                    filter_var($this->object->Database->FilterOn, FILTER_VALIDATE_BOOLEAN) &&
                    strlen($this->object->Database->FilterTables) > 0
                ) {
                    $this->filteredData['filterTables'] = SnapWP::getTablesWithPrefix(explode(',', $this->object->Database->FilterTables));
                }
                break;
            case self::TYPE_SCHEDULE:
            case self::TYPE_TEMPLATE:
                $this->filteredData['dbonly'] = filter_var($this->activeTemplate->archive_export_onlydb, FILTER_VALIDATE_BOOLEAN);

                if (filter_var($this->activeTemplate->archive_filter_on, FILTER_VALIDATE_BOOLEAN) && strlen($this->activeTemplate->archive_filter_dirs) > 0) {
                    $filterDirs                       = explode(';', $this->activeTemplate->archive_filter_dirs);
                    $this->filteredData['filterDirs'] = array_intersect($filterDirs, DUP_PRO_U::getWPCoreDirs());
                }

                if (
                    filter_var($this->activeTemplate->database_filter_on, FILTER_VALIDATE_BOOLEAN) &&
                    strlen($this->activeTemplate->database_filter_tables) > 0
                ) {
                    $this->filteredData['filterTables'] = SnapWP::getTablesWithPrefix(explode(',', $this->activeTemplate->database_filter_tables));
                }
                break;
        }

        return $this->filteredData;
    }
}
