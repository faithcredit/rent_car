<?php

/**
 * Interface that collects the functions of initial duplicator Bootstrap
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core;

use Duplicator\MuPlugin\MuGenerator;
use Duplicator\Utils\ExpireOptions;

/**
 * Uninstall class
 */
class Unistall
{
    /**
     * Registrer unistall hoosk
     *
     * @return void
     */
    public static function registreHooks()
    {
        if (is_admin()) {
            register_deactivation_hook(DUPLICATOR____FILE, array(__CLASS__, 'deactivate'));
        }
    }

    /**
     * Deactivation Hook:
     * Hooked into `register_deactivation_hook`.  Routines used to deactivate the plugin
     * For uninstall see uninstall.php  WordPress by default will call the uninstall.php file
     *
     * @return void
     */
    public static function deactivate()
    {
        MigrationMng::renameInstallersPhpFiles();

        //Logic has been added to uninstall.php
        //Force recalculation of next run time on activation
        //see the function \DUP_PRO_Package_Runner::calculate_earliest_schedule_run_time()
        \DUP_PRO_Log::trace("Resetting next run time for active schedules");
        $activeSchedules = \DUP_PRO_Schedule_Entity::get_active();
        ExpireOptions::deleteAll();
        foreach ($activeSchedules as $activeSchedule) {
            $activeSchedule->next_run_time = -1;
            $activeSchedule->save();
        }

        // Unschedule custom cron event for cleanup if it's scheduled
        if (wp_next_scheduled(\DUP_PRO_Global_Entity::CLEANUP_HOOK)) {
            // Unschedule the hook
            $timestamp = wp_next_scheduled(\DUP_PRO_Global_Entity::CLEANUP_HOOK);
            wp_unschedule_event($timestamp, \DUP_PRO_Global_Entity::CLEANUP_HOOK);
        }

        MuGenerator::remove();
    }
}
