<?php

defined("ABSPATH") or die("");

use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use VendorDuplicator\Amk\JsonSerialize\JsonUnserializeMap;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Utils\Crypt\CryptCustom;

class DUP_PRO_Settings_U
{
    public $message;
    public $export_filepath;

    public function __construct()
    {
        $this->message         = '';
        $this->export_filepath = '';
    }

    /**
     *  Exports all settings an export file.
     *
     *  @return void */
    public function runExport()
    {
        $global                            = DUP_PRO_Global_Entity::getInstance();
        $sglobal                           = DUP_PRO_Secure_Global_Entity::getInstance();
        $export_data                       = new StdClass();
        $export_data->templates            = DUP_PRO_Package_Template_Entity::getAllWithoutManualMode();
        $export_data->schedules            = DUP_PRO_Schedule_Entity::getAll();
        $export_data->storages             = DUP_PRO_Storage_Entity::get_all();
        $export_data->settings             = clone $global;
        $export_data->secure_settings      = clone $sglobal;
        $export_data->secure_settings->lkp = '';
        $json_file_data                    = JsonSerialize::serialize($export_data, JsonSerialize::JSON_SKIP_CLASS_NAME | JsonSerialize::JSON_SKIP_MAGIC_METHODS);
        if ($json_file_data === false) {
            //Isolate the problem area:
            $test           = SnapJson::jsonEncode($export_data->templates);
            $test_templates = ($test === false ? '*Fail' : 'Pass');
            $test           = SnapJson::jsonEncode($export_data->schedules);
            $test_schedules = ($test === false ? '*Fail' : 'Pass');
            $test           = SnapJson::jsonEncode($export_data->storages);
            $test_storages  = ($test === false ? '*Fail' : 'Pass');
            $test           = SnapJson::jsonEncode($export_data->settings);
            $test_settings  = ($test === false ? '*Fail' : 'Pass');
            $test           = SnapJson::jsonEncode($export_data->schedules);
            $test_settings  = ($test === false ? '*Fail' : 'Pass');

            $exc_msg = 'Isn\'t possible serialize json data';
            $div     = "******************************************";
            $err     = <<<ERR
\n{$div}\nDUPLICATOR PRO - EXPORT SETTINGS ERROR\n{$div}
Error encoding json data for export status

Templates	= {$test_templates}
Schedules	= {$test_schedules}
Storage		= {$test_storages}
Settings	= {$test_settings}
Security	= {$test_settings}

RECOMMENDATION:
Check the data in the failed areas above to make sure the data is correct.  If the data looks correct consider re-saving the data in
that respective area.  If the problem persists consider removing the items one by one to isolate the setting that is causing the issue.

ERROR DETAILS:\n$exc_msg
ERR;
            DUP_PRO_Log::traceObject('There was an error encoding json data for export', $export_data);
            throw new Exception($err);
        }

        $encrypted_data        = CryptCustom::encrypt($json_file_data, 'test');
        $this->export_filepath = DUPLICATOR_PRO_SSDIR_PATH_TMP . '/dpro-export-' . date("Ymdhs") . '.dup';
        if (file_put_contents($this->export_filepath, $encrypted_data) === false) {
            throw new Exception("Error writing export to {$this->export_filepath}");
        }

        $this->message = DUP_PRO_U::__("Export data file has been created!<br/>");
    }

    /**
     *  Creates and export file of current settings and then
     *  imports all the new settings from an existing import file
     *
     *  @param string   $filename The name of the import file to import
     *  @param string[] $opts     The options to import templates, schedules, storage, etc.
     *
     *  @return void
     */
    public function runImport($filename, $opts)
    {
        DUP_PRO_U::initStorageDirectory();

        // Generate backup of current settings
        $this->runExport();
        $filepath       = $filename;
        $encrypted_data = file_get_contents($filepath);
        if ($encrypted_data === false) {
            throw new Exception("Error reading {$filepath}");
        }

        $json_data   = CryptCustom::decrypt($encrypted_data, 'test');
        $import_data = JsonSerialize::unserializeWithMap(
            $json_data,
            new JsonUnserializeMap([
                '' => 'object',
                'templates/*'     => 'cl:' . DUP_PRO_Package_Template_Entity::class,
                'schedules/*'     => 'cl:' . DUP_PRO_Schedule_Entity::class,
                'storages/*'      => 'object',
                'settings'        => 'cl:' . DUP_PRO_Global_Entity::class,
                'secure_settings' => 'cl:' . DUP_PRO_Secure_Global_Entity::class
            ]),
            512,
            JsonSerialize::JSON_SKIP_MAGIC_METHODS
        );

        if ($import_data === null) {
            throw new Exception('Problem decoding JSON data');
        }

        $this->processImportData($import_data, $opts);
        $this->message  = DUP_PRO_U::__("All data has been succesfully imported and updated! <br/>");
        $this->message .= DUP_PRO_U::__("Backup data file has been created here {$this->export_filepath} <br/>");
    }

    /**
     * Import data
     *
     * @param object   $import_data
     * @param string[] $opts
     *
     * @return void
     */
    private function processImportData($import_data, $opts)
    {
        if (in_array('schedules', $opts)) {
            $opts[] = 'templates';
            $opts[] = 'storages';
            $opts   = array_unique($opts);
        }
        DUP_PRO_Log::trace('Import data ' . implode(',', $opts));
        $template_map = (in_array('templates', $opts) ? $this->importTemplates($import_data) : []);
        $storage_map  = (in_array('storages', $opts) ? $this->importStorages($import_data) : []);
        if (in_array('schedules', $opts)) {
            $this->importSchedules($import_data, $storage_map, $template_map);
        }
        if (in_array('settings', $opts)) {
            $this->importSettings($import_data);
        }
    }

    /**
     * Import settings
     *
     * @param object $import_data
     *
     * @return bool
     */
    private function importSettings($import_data)
    {
        if (!property_exists($import_data, 'settings')) {
            return true;
        }

        DUP_PRO_Log::traceObject('Import data settings val ', $import_data->settings);

        $global  = DUP_PRO_Global_Entity::getInstance();
        $sglobal = DUP_PRO_Secure_Global_Entity::getInstance();

        if (property_exists($import_data, 'secure_settings')) {
            $sglobal->setFromImportData($import_data->secure_settings);
            $sglobal->save();
        }
        return $global->save();
    }

    /**
     * Import templates
     *
     * @param object $import_data
     *
     * @return int[] return map from old ids and new
     */
    private function importTemplates($import_data)
    {
        $template_map = [];

        if (!property_exists($import_data, 'templates') || !is_array($import_data->templates)) {
            return $template_map;
        }

        foreach ($import_data->templates as $template_data) {
            $old_id = $template_data->getId();
            if ($template_data->is_default) {
                $template = DUP_PRO_Package_Template_Entity::get_default_template();
            } else {
                $template = DUP_PRO_Package_Template_Entity::createFromImportData($template_data);
                $template->save();
            }
            $template_map[$old_id] = $template->getId();
        }
        return $template_map;
    }

    /**
     * Import schedules
     *
     * @param object $import_data
     * @param int[]  $storage_map key is source id, value is new id
     * @param int[]  $template_map key is source id, value is new id
     *
     * @return void
     */
    private function importSchedules($import_data, $storage_map, $template_map)
    {
        if (!property_exists($import_data, 'schedules') || !is_array($import_data->schedules)) {
            return;
        }

        foreach ($import_data->schedules as $schedule_data) {
            $schedule = DUP_PRO_Schedule_Entity::createFromImportData($schedule_data, $storage_map, $template_map);
            $schedule->save();
        }
    }

    /**
     * Import storages
     *
     * @param object $import_data
     *
     * @return int[] return map from old ids and new
     */
    private function importStorages($import_data)
    {
        $storage_map = [
            DUP_PRO_Virtual_Storage_IDs::Default_Local => DUP_PRO_Virtual_Storage_IDs::Default_Local
        ];

        if (!property_exists($import_data, 'storages') || !is_array($import_data->storages)) {
            return $storage_map;
        }

        foreach ($import_data->storages as $storage_data) {
            // Skip default storage
            if ($storage_data->id < 0) {
                continue;
            }
            $storage     = DUP_PRO_Storage_Entity::create_from_data($storage_data, true);
            $old_id      = $storage->id;
            $storage->id = -1;
            $storage->save();
            $storage_map[$old_id] = $storage->id;
        }
        return $storage_map;
    }
}
