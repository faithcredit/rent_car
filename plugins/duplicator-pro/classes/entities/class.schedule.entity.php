<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\Models\AbstractEntityList;
use Duplicator\Core\Models\UpdateFromInputInterface;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapWP;
use VendorDuplicator\Cron\CronExpression;

/**
 * Schedule entity
 */
class DUP_PRO_Schedule_Entity extends AbstractEntityList implements UpdateFromInputInterface
{
    const RUN_STATUS_SUCCESS = 0;
    const RUN_STATUS_FAILURE = 1;

    const REPEAT_DAILY   = 0;
    const REPEAT_WEEKLY  = 1;
    const REPEAT_MONTHLY = 2;
    const REPEAT_HOURLY  = 3;

    const DAY_MONDAY    = 0b0000001;
    const DAY_TUESDAY   = 0b0000010;
    const DAY_WEDNESDAY = 0b0000100;
    const DAY_THURSDAY  = 0b0001000;
    const DAY_FRIDAY    = 0b0010000;
    const DAY_SATURDAY  = 0b0100000;
    const DAY_SUNDAY    = 0b1000000;

    /** @var string */
    public $name = '';
    /** @var int<-1, max> */
    public $template_id = -1;
    /** @var int<-1, max> */
    public $start_ticks = 0;
    /** @var int<0, 3> */
    public $repeat_type = self::REPEAT_WEEKLY;
    /** @var bool */
    public $active = true;
    /** @var int<-1, max> */
    public $next_run_time = -1;
    /** @var int<1, max> */
    public $run_every = 1;
    /** @var int<0, max> bitmask */
    public $weekly_days = 0;
    /** @var int<1, max> */
    public $day_of_month = 1;
    /** @var string */
    public $cron_string = '';
    /** @var int<-1, max> */
    public $last_run_time = -1;
    /** @var int<0, 1> */
    public $last_run_status = self::RUN_STATUS_FAILURE;
    /** @var int<0, max> */
    public $times_run = 0;
    /** @var int[] */
    public $storage_ids = [
        DUP_PRO_Virtual_Storage_IDs::Default_Local
    ];

    /**
     * Class contructor
     */
    public function __construct()
    {
        $this->name = DUP_PRO_U::__('New Schedule');
    }

    /**
     * Entity type
     *
     * @return string
     */
    public static function getType()
    {
        return 'DUP_PRO_Schedule_Entity';
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

        if (strlen($this->name) == 0) {
            throw new Exception(__('Schedule name can\'t be empty', 'duplicator-pro'));
        }
        $this->template_id = intval($this->template_id);

        if (DUP_PRO_Package_Template_Entity::getById($this->template_id) === false) {
            throw new Exception(__('Invalid template id', 'duplicator-pro'));
        }

        $this->repeat_type  = intval($this->repeat_type);
        $this->day_of_month = intval($this->day_of_month);

        switch ($this->repeat_type) {
            case DUP_PRO_Schedule_Entity::REPEAT_HOURLY:
                $this->run_every = intval($input['_run_every_hours']);
                DUP_PRO_Log::trace("run every hours: " . $input['_run_every_hours']);
                break;
            case DUP_PRO_Schedule_Entity::REPEAT_DAILY:
                $this->run_every = intval($input['_run_every_days']);
                DUP_PRO_Log::trace("run every days: " . $input['_run_every_days']);
                break;
            case DUP_PRO_Schedule_Entity::REPEAT_MONTHLY:
                $this->run_every = intval($input['_run_every_months']);
                DUP_PRO_Log::trace("run every months: " . $input['_run_every_months']);
                break;
            case DUP_PRO_Schedule_Entity::REPEAT_WEEKLY:
                $this->set_weekdays_from_request($input);
                break;
        }

        if (isset($input['_storage_ids'])) {
            $this->storage_ids = array_map('intval', $input['_storage_ids']);
        } else {
            $this->storage_ids = [
                DUP_PRO_Virtual_Storage_IDs::Default_Local
            ];
        }

        $this->set_start_date_time($input['_start_time']);
        $this->build_cron_string();
        $this->next_run_time = $this->get_next_run_time();

        // Checkboxes don't set post values when off so have to manually set these
        $this->active = isset($input['_active']);

        return true;
    }

    /**
     * Create from import data
     *
     * @param self $data input data
     * @param int[]  $storage_map key is source id, value is new id
     * @param int[]  $template_map key is source id, value is new id
     *
     * @return self
     */
    public static function createFromImportData(self $data, $storage_map = [], $template_map = [])
    {
        $instance = new self();
        $reflect  = new ReflectionClass($instance);
        $props    = $reflect->getProperties();

        $skipProps = [
            'id',
            'last_run_time',
            'next_run_time',
            'times_run'
        ];

        foreach ($props as $prop) {
            if (in_array($prop->getName(), $skipProps)) {
                continue;
            }
            $value = $prop->getValue($data);
            $prop->setValue($instance, $value);
        }

        if (isset($template_map[$instance->template_id])) {
            $instance->template_id = $template_map[$instance->template_id];
        }

        for ($i = 0; $i < count($instance->storage_ids); $i++) {
            if (isset($storage_map[$instance->storage_ids[$i]])) {
                $instance->storage_ids[$i] = $storage_map[$instance->storage_ids[$i]];
            }
        }

        return $instance;
    }

    /**
     * If it should run, queue up a package then update the run time
     *
     * @return void
     */
    public function process()
    {
        DUP_PRO_Log::trace("process");
        $now = time();

        if ($this->next_run_time == -1) {
            return;
        }

        if ($this->active && ($this->next_run_time <= $now)) {
            $exception = null;
            try {
                if (!License::can(License::CAPABILITY_PRO_BASE)) {
                    DUP_PRO_Log::trace("Can't process schedule " . $this->getId() . " because Duplicator isn't licensed");
                    return;
                }

                $next_run_time_string = DUP_PRO_DATE::getLocalTimeFromGMTTicks($this->next_run_time);
                $now_string           = DUP_PRO_DATE::getLocalTimeFromGMTTicks($this->next_run_time);

                DUP_PRO_Log::trace("NEXT RUN IS NOW! $next_run_time_string <= $now_string so trying to queue package");

                $this->insert_new_package();

                $this->next_run_time = $this->get_next_run_time();
                $this->save();

                $next_run_time_string = DUP_PRO_DATE::getLocalTimeFromGMTTicks($this->next_run_time);
                DUP_PRO_Log::trace("******PACKAGE JUST CREATED. UPDATED NEXT RUN TIME TO $next_run_time_string");
            } catch (Exception $e) {
                $exception = $e;
            } catch (Error $e) {
                $exception = $e;
            }

            if (!is_null($exception)) {
                $msg  = "Start schedule error " . $exception->getMessage() . "\n";
                $msg .= SnapLog::getTextException($exception);
                error_log($msg);
                \DUP_PRO_Log::trace($msg);
                $system_global                  = DUP_PRO_System_Global_Entity::getInstance();
                $system_global->schedule_failed = true;
                $system_global->save();
            }
        } else {
            DUP_PRO_Log::trace("active and runtime=$this->next_run_time >= $now");
        }
    }

    /**
     * Copy schedule from id
     *
     * @param int<0, max> $scheduleId template id
     *
     * @return void
     */
    public function copy_from_source_id($scheduleId)
    {
        if (($source = self::getById($scheduleId)) === false) {
            throw new Exception('Can\'t get tempalte id' . $scheduleId);
        }

        $skipProps = [
            'id',
            'last_run_time',
            'next_run_time',
            'times_run'
        ];

        $reflect = new ReflectionClass($this);
        $props   = $reflect->getProperties();

        foreach ($props as $prop) {
            if (in_array($prop->getName(), $skipProps)) {
                continue;
            }
            $prop->setValue($this, $prop->getValue($source));
        }

        $this->name = sprintf(DUP_PRO_U::__('%1$s - Copy'), $source->name);
    }

    /**
     * Create new packag from schedule, to run
     *
     * @param bool $run_now If true the package creation is started immediately, otherwise it is scheduled
     *
     * @return void
     */
    public function insert_new_package($run_now = false)
    {
        global $wp_version;

        $global = DUP_PRO_Global_Entity::getInstance();

        DUP_PRO_Log::trace("archive build mode before calling insert new package " . $global->archive_build_mode);

        DUP_PRO_Log::trace("Inserting new package for schedule $this->name");

        if (($template = DUP_PRO_Package_Template_Entity::getById((int) $this->template_id)) === false) {
            DUP_PRO_Log::traceError("No settings object exists for schedule {$this->name}!");
            return;
        }

        $package = new DUP_PRO_Package();

        $dbversion  = DUP_PRO_DB::getVersion();
        $dbversion  = (empty($dbversion) ? '- unknown -' : $dbversion);
        $dbcomments = DUP_PRO_DB::getVariable('version_comment');
        $dbcomments = is_null($dbcomments) ? '- unknown -' : $dbcomments;

        //PACKAGE
        $package->Created    = gmdate("Y-m-d H:i:s");
        $package->Version    = DUPLICATOR_PRO_VERSION;
        $package->VersionOS  = defined('PHP_OS') ? PHP_OS : 'unknown';
        $package->VersionWP  = $wp_version;
        $package->VersionPHP = phpversion();
        $package->VersionDB  = $dbversion;
        $package->Name       = $this->generate_package_name();
        $package->Hash       = $package->make_hash();
        $package->NameHash   = "{$package->Name}_{$package->Hash}";
        $package->notes      = sprintf(DUP_PRO_U::esc_html__('Created by schedule %1$s'), $this->name);
        if ($run_now) {
            $package->Type = DUP_PRO_PackageType::RUN_NOW;
        } else {
            $package->Type = DUP_PRO_PackageType::SCHEDULED;
        }

        //BRAND
        $brand_data = DUP_PRO_Brand_Entity::getByIdOrDefault((int) $template->installer_opts_brand);
        $brand_data->prepare_attachments_to_installer();

        $package->Brand    = $brand_data->name;
        $package->Brand_ID = $brand_data->getId();

        //MULTISITE
        $package->Multisite->FilterSites = $template->filter_sites;

        //ARCHIVE
        if ($global->archive_build_mode == DUP_PRO_Archive_Build_Mode::DupArchive) {
            $package->Archive->Format = 'DAF';
        } else {
            $package->Archive->Format = 'ZIP';
        }

        $package->Archive->ExportOnlyDB = $template->archive_export_onlydb;
        $package->Archive->FilterOn     = $template->archive_filter_on;
        $package->Archive->FilterDirs   = $template->archive_filter_dirs;
        $package->Archive->FilterExts   = $template->archive_filter_exts;
        $package->Archive->FilterFiles  = $template->archive_filter_files;

        //INSTALLER
        $package->Installer->OptsDBHost   = $template->installer_opts_db_host;
        $package->Installer->OptsDBName   = $template->installer_opts_db_name;
        $package->Installer->OptsDBUser   = $template->installer_opts_db_user;
        $package->Installer->OptsSecureOn = $template->installer_opts_secure_on;
        $package->Installer->passowrd     = $template->installerPassowrd;
        $package->Installer->OptsSkipScan = $template->installer_opts_skip_scan;

        // CPANEL
        $package->Installer->OptsCPNLHost    = $template->installer_opts_cpnl_host;
        $package->Installer->OptsCPNLUser    = $template->installer_opts_cpnl_user;
        $package->Installer->OptsCPNLPass    = $template->installer_opts_cpnl_pass;
        $package->Installer->OptsCPNLEnable  = $template->installer_opts_cpnl_enable;
        $package->Installer->OptsCPNLConnect = false;
        //CPANEL DB
        //1 = Create New, 2 = Connect Remove
        $package->Installer->OptsCPNLDBAction = $template->installer_opts_cpnl_db_action;
        $package->Installer->OptsCPNLDBHost   = $template->installer_opts_cpnl_db_host;
        $package->Installer->OptsCPNLDBName   = $template->installer_opts_cpnl_db_name;
        $package->Installer->OptsCPNLDBUser   = $template->installer_opts_cpnl_db_user;

        //DATABASE
        $package->Database->FilterOn     = $template->database_filter_on;
        $package->Database->FilterTables = $template->database_filter_tables;
        $package->Database->Comments     = esc_html($dbcomments);

        $package->Status      = DUP_PRO_PackageStatus::PRE_PROCESS;
        $package->schedule_id = $this->id;
        $package->add_upload_infos($this->storage_ids);

        $package->build_progress->setBuildMode();

        $system_global = DUP_PRO_System_Global_Entity::getInstance();
        $system_global->clearFixes();
        $system_global->package_check_ts = 0;
        $system_global->save();

        DUP_PRO_Log::trace('NEW PACKAGE NAME ' . $package->Name);
        if ($package->save(false) == false) {
            throw new Exception("Duplicator is unable to insert a package record into the database table from schedule {$this->name}.");
        }

        DUP_PRO_Log::trace("archive build mode after calling insert new package " . $global->archive_build_mode);
    }

    /**
     * Get schedule template object or false if don't exists
     *
     * @return false|DUP_PRO_Package_Template_Entity
     */
    public function getTemplate()
    {
        if ($this->template_id > 0) {
            $template = DUP_PRO_Package_Template_Entity::getById($this->template_id);
        } else {
            $template = null;
        }

        if (!$template instanceof DUP_PRO_Package_Template_Entity) {
            return false;
        }

        return $template;
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
        if (($template = $this->getTemplate()) === false) {
            return;
        }

        $schedule = $this;
        require DUPLICATOR____PATH . '/views/tools/templates/widget/recoveable-template-info.php';
    }

    /**
     * Return package name
     *
     * @return string
     */
    private function generate_package_name()
    {
        $ticks = time() + SnapWP::getGMTOffset();

        //Remove specail_chars from final result
        $sanitize_special_chars = array(
            ".", "-", "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&",
            "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", "+", chr(0)
        );

        $scheduleName = SnapUtil::sanitizeNSCharsNewlineTabs($this->name);
        $scheduleName = trim(str_replace($sanitize_special_chars, '', $scheduleName), '_');
        DUP_PRO_Log::trace('SCHEDULE NAME ' . $scheduleName);
        $blogName = sanitize_title(SnapUtil::sanitizeNSCharsNewlineTabs(get_bloginfo('name', 'display')));
        $blogName = trim(str_replace($sanitize_special_chars, '', $blogName), '_');
        DUP_PRO_Log::trace('BLOG NAME NAME ' . $blogName);

        $name = date('Ymd_His', $ticks) . '_' . $scheduleName . '_' .  $blogName;

        return substr($name, 0, 40);
    }

    /**
     * Return the next run time in UTC
     *
     * @return int<-1, max>
     */
    public function get_next_run_time()
    {
        if ($this->active) {
            $nextMinute = time() + 60; // We look ahead starting from next minute
            $date       = new DateTime();
            $date->setTimestamp($nextMinute + SnapWP::getGMTOffset());//Add timezone specific offset

            //Get next run time relative to $date
            $nextRunTime = CronExpression::factory($this->cron_string)->getNextRunDate($date)->getTimestamp();

            // Have to negate the offset and add. For instance for az time -7
            // we want the next run time to be 7 ahead in UTC time
            $nextRunTime -= SnapWP::getGMTOffset();

            // Handling DST problem that happens when there is a change of DST between $nextMinute and $nextRunTime.
            // The problem does not happen if manual offset is selected, because in that case there is no DST.
            $timezoneString = SnapWP::getTimeZoneString();
            if ($timezoneString) {
                // User selected particular timezone (not manual offset), so the problem needs to be handled.
                $DST_NextMinute           = SnapWP::getDST($nextMinute);
                $DST_NextRunTime          = SnapWP::getDST($nextRunTime);
                $DST_NextRunTime_HourBack = SnapWP::getDST($nextRunTime - 3600);
                if ($DST_NextMinute && !$DST_NextRunTime) {
                    $nextRunTime += 3600; // Move one hour ahead because of DST change
                } elseif (!$DST_NextMinute && $DST_NextRunTime && $DST_NextRunTime_HourBack) {
                    $nextRunTime -= 3600; // Move one hour back because of DST change
                }
            }
            return $nextRunTime;
        } else {
            return -1;
        }
    }

    /**
     * Set week days from input data
     *
     * @param array<string, mixed> $request
     *
     * @return void
     */
    protected function set_weekdays_from_request($request)
    {
        $weekday = $request['weekday'];
        if (in_array('mon', $weekday)) {
            $this->weekly_days |= self::DAY_MONDAY;
        } else {
            $this->weekly_days &= ~self::DAY_MONDAY;
        }

        if (in_array('tue', $weekday)) {
            $this->weekly_days |= self::DAY_TUESDAY;
        } else {
            $this->weekly_days &= ~self::DAY_TUESDAY;
        }

        if (in_array('wed', $weekday)) {
            $this->weekly_days |= self::DAY_WEDNESDAY;
        } else {
            $this->weekly_days &= ~self::DAY_WEDNESDAY;
        }

        if (in_array('thu', $weekday)) {
            $this->weekly_days |= self::DAY_THURSDAY;
        } else {
            $this->weekly_days &= ~self::DAY_THURSDAY;
        }

        if (in_array('fri', $weekday)) {
            $this->weekly_days |= self::DAY_FRIDAY;
        } else {
            $this->weekly_days &= ~self::DAY_FRIDAY;
        }

        if (in_array('sat', $weekday)) {
            $this->weekly_days |= self::DAY_SATURDAY;
        } else {
            $this->weekly_days &= ~self::DAY_SATURDAY;
        }

        if (in_array('sun', $weekday)) {
            $this->weekly_days |= self::DAY_SUNDAY;
        } else {
            $this->weekly_days &= ~self::DAY_SUNDAY;
        }
    }

    /**
     * Check if day is set
     *
     * @param string $day_string day string
     *
     * @return bool
     */
    public function is_day_set($day_string)
    {
        $day_bit = 0;

        switch ($day_string) {
            case 'mon':
                $day_bit = self::DAY_MONDAY;
                break;
            case 'tue':
                $day_bit = self::DAY_TUESDAY;
                break;
            case 'wed':
                $day_bit = self::DAY_WEDNESDAY;
                break;
            case 'thu':
                $day_bit = self::DAY_THURSDAY;
                break;
            case 'fri':
                $day_bit = self::DAY_FRIDAY;
                break;
            case 'sat':
                $day_bit = self::DAY_SATURDAY;
                break;
            case 'sun':
                $day_bit = self::DAY_SUNDAY;
                break;
        }

        return (($this->weekly_days & $day_bit) != 0);
    }

    /**
     * Returns a list of all schedules associated with a storage
     *
     * @param int $storageID The storage id
     *
     * @return self[]
     */
    public static function get_schedules_by_storage_id($storageID)
    {
        return array_filter(self::getAll(), function ($schedule) use ($storageID) {
            return  in_array($storageID, $schedule->storage_ids);
        });
    }

    /**
     * Runs the callback on all schedules
     *
     * @param $callback
     *
     * @return void
     */
    public static function run_on_all($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('No callback function passed');
        }

        foreach (self::getAll() as $schedule) {
            call_user_func($callback, $schedule);
        }
    }

    /**
     * Get active schedule
     *
     * @return self[]
     */
    public static function get_active()
    {
        return self::getAll(
            0,
            0,
            null,
            function (self $schedule) {
                return $schedule->active;
            }
        );
    }

    /**
     * Get stazrt time piece
     *
     * @param int $piece 0 = hour; 1 = minute;
     *
     * @return int
     */
    public function get_start_time_piece($piece)
    {
        switch ($piece) {
            case 0:
                return (int) date('G', $this->start_ticks);
            case 1:
                return (int) date('i', $this->start_ticks);
            default:
                return -1;
        }
    }

    /**
     * Return next run date
     *
     * @return string
     */
    public function get_next_run_time_string()
    {
        if ($this->next_run_time == -1) {
            return DUP_PRO_U::__('Unscheduled');
        } else {
            $date_portion   = SnapWP::getDateInWPTimezone(
                get_option('date_format', 'n/j/y') . ' G:i',
                $this->next_run_time
            );
            $repeat_portion = $this->get_repeat_text();
            return "$date_portion - $repeat_portion";
        }
    }

    /**
     * Return last run date
     *
     * @return string
     */
    public function get_last_ran_string()
    {
        if ($this->last_run_time == -1) {
            return DUP_PRO_U::__('Never Ran');
        } else {
            $date_portion   = SnapWP::getDateInWPTimezone(
                get_option('date_format', 'n/j/y') . ' G:i',
                $this->last_run_time
            );
            $status_portion = (($this->last_run_status == self::RUN_STATUS_SUCCESS) ? DUP_PRO_U::__('Success') : DUP_PRO_U::__('Failed'));
            return "$date_portion - $status_portion";
        }
    }

    /**
     * Set start time from string date format
     *
     * @param string $startTime
     * @param string $startDate
     *
     * @return int return start time
     */
    public function set_start_date_time($startTime, $startDate = '2015/1/1')
    {
        $this->start_ticks = (int) strtotime("$startDate $startTime");
        DUP_PRO_Log::trace("start ticks = $this->start_ticks for $startTime $startDate");
        return $this->start_ticks;
    }

    /**
     * Get schedules entity by template id
     *
     * @param int $template_id
     *
     * @return self[]
     */
    public static function get_by_template_id($template_id)
    {
        $schedules          = self::getAll();
        $filtered_schedules = array();

        foreach ($schedules as $schedule) {
            if ($schedule->template_id == $template_id) {
                array_push($filtered_schedules, $schedule);
            }
        }

        DUP_PRO_Log::trace("get by template id $template_id schedules = " . count($filtered_schedules));

        return $filtered_schedules;
    }

    /**
     * Return repeat text
     *
     * @return string
     */
    public function get_repeat_text()
    {
        switch ($this->repeat_type) {
            case self::REPEAT_DAILY:
                return DUP_PRO_U::__('Daily');
            case self::REPEAT_WEEKLY:
                return DUP_PRO_U::__('Weekly');
            case self::REPEAT_MONTHLY:
                return DUP_PRO_U::__('Monthly');
            case self::REPEAT_HOURLY:
                return DUP_PRO_U::__('Hourly');
            default:
                return DUP_PRO_U::__('Unknown');
        }
    }

    /**
     * Build cron string
     *
     * @return void
     */
    public function build_cron_string()
    {
        // Special cron string for debugging if name set to 'bobtest'
        if ($this->name == 'bobtest') {
            $this->cron_string = '*/5 * * * *';
        } else {
            $start_hour = $this->get_start_time_piece(0);
            $start_min  = $this->get_start_time_piece(1);

            if ($this->run_every == 1) {
                $run_every_string = '*';
            } else {
                $run_every_string = "*/$this->run_every";
            }

            // Generated cron patterns using http://www.cronmaker.com/
            switch ($this->repeat_type) {
                case self::REPEAT_HOURLY:
                    $this->cron_string = "$start_min $run_every_string * * *";
                    break;
                case self::REPEAT_DAILY:
                    $this->cron_string = "$start_min $start_hour $run_every_string * *";
                    break;
                case self::REPEAT_WEEKLY:
                    $day_of_week_string = $this->get_day_of_week_string();
                    $this->cron_string  = "$start_min $start_hour * * $day_of_week_string";

                    DUP_PRO_Log::trace("day of week cron string: $this->cron_string");
                    break;
                case self::REPEAT_MONTHLY:
                    $this->cron_string = "$start_min $start_hour $this->day_of_month $run_every_string *";
                    break;
            }
        }

        DUP_PRO_Log::trace("cron string = $this->cron_string");
    }

    /**
     * Return day of weeks list with commad separated
     *
     * @return string
     */
    private function get_day_of_week_string()
    {
        $day_array = [];

        DUP_PRO_Log::trace("weekly days=$this->weekly_days");

        if (($this->weekly_days & self::DAY_MONDAY) != 0) {
            $day_array[] = '1';
        }
        if (($this->weekly_days & self::DAY_TUESDAY) != 0) {
            $day_array[] = '2';
        }
        if (($this->weekly_days & self::DAY_WEDNESDAY) != 0) {
            $day_array[] = '3';
        }
        if (($this->weekly_days & self::DAY_THURSDAY) != 0) {
            $day_array[] = '4';
        }
        if (($this->weekly_days & self::DAY_FRIDAY) != 0) {
            array_push($day_array, '5');
        }
        if (($this->weekly_days & self::DAY_SATURDAY) != 0) {
            $day_array[] = '6';
        }
        if (($this->weekly_days & self::DAY_SUNDAY) != 0) {
            $day_array[] = '0';
        }
        return implode(',', $day_array);
    }
}
