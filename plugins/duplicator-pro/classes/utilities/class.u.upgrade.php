<?php

use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Utils\Crypt\CryptBlowfish;
use Duplicator\Addons\ProBase\License\License;

/**
 * Utility class managing when the plugin is updated
 *
 * Standard: PSR-2
 *
 * @link      http://www.php-fig.org/psr/psr-2
 * @copyright (c) 2017, Snapcreek LLC
 * @license   https://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class DUP_PRO_Upgrade_U
{
    /**
     * This function is executed when the plugin is activated and
     * every time the version saved in the wp_options is different from the plugin version both in upgrade and downgrade.
     *
     * @param false|string $currentVersion current Duplicator version, false if is first installation
     * @param string       $newVersion     new Duplicator Version
     *
     * @return void
     */
    public static function performUpgrade($currentVersion, $newVersion)
    {
        self::storeDupSecureKey($currentVersion);
        self::updateEndpoints($currentVersion);
        self::updateTemplates($currentVersion);
        self::moveDataToSecureGlobal();
        self::initializeGift();
        self::updateArchiveEngine();
        License::clearVersionCache();

        //Setup All Directories
        DUP_PRO_U::initStorageDirectory();

        // Schedule custom cron event for cleanup of installer files if it should be scheduled
        DUP_PRO_Global_Entity::cleanupScheduleSetup();
    }

    /**
     * Upate endpoints
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function updateEndpoints($currentVersion)
    {
        // TODO: After a period of time and we are no longer unconditionally removing the version in uninstall.php change first part to:
        //       $currentVersion !== false &&

        // For storage endpoint upgrade in 3.8.9.3
        if ($currentVersion == false || version_compare($currentVersion, '3.8.9.2', '<=')) {
            $storages = DUP_PRO_Storage_Entity::get_all();
            foreach ($storages as $storage) {
                if ($storage->id != DUP_PRO_Virtual_Storage_IDs::Default_Local) {
                    $storage->save();
                }
            }
        }

        if ($currentVersion == false || version_compare($currentVersion, '4.5.6', '<')) {
            $storages = DUP_PRO_Storage_Entity::get_all();
            foreach ($storages as $storage) {
                if ($storage->storage_type == DUP_PRO_Storage_Types::GDrive && $storage->gdrive_client_number == null) {
                    if ($storage->gdrive_authorization_state == DUP_PRO_GDrive_Authorization_States::Authorized) {
                        $storage->gdrive_client_number = DUP_PRO_Storage_Entity::GDRIVE_CLIENT_NATIVE;
                    } else {
                        $storage->gdrive_client_number = DUP_PRO_Storage_Entity::GDRIVE_CLIENT_LATEST;
                    }
                    $storage->save();
                }
            }
        }
    }

    /**
     * Upate templates
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function updateTemplates($currentVersion)
    {
        // Update templates one when coming from 4.5.2 or below
        if (version_compare($currentVersion, '4.5.3', '>=')) {
            return;
        }

        $templates = DUP_PRO_Package_Template_Entity::getAll();
        if (!is_array($templates)) {
            return;
        }

        foreach ($templates as $template) {
            $template->save();
        }
    }

    /**
     * Save DUP SECURE KEY
     *
     * @param false|string $currentVersion current Duplicator version
     *
     * @return void
     */
    protected static function storeDupSecureKey($currentVersion)
    {
        if ($currentVersion !== false && SnapUtil::versionCompare($currentVersion, '4.5.0', '<=', 3)) {
            CryptBlowfish::createWpConfigSecureKey(true, true);
        } else {
            CryptBlowfish::createWpConfigSecureKey(false, false);
        }
    }

    /**
     * Init gift
     *
     * @return void
     */
    protected static function initializeGift()
    {
        $global                              = DUP_PRO_Global_Entity::getInstance();
        $global->dupHidePackagesGiftFeatures = !DUPLICATOR_PRO_GIFT_THIS_RELEASE; // @phpstan-ignore-line
        $global->save();
    }

    /**
     * UpdateArchiveEngine : Introduced in v3.7.1
     * Between v3.5 and v3.7 a temporary setting was created in the packages settings, that allowed for an archive engine (DA, ZA, Shell)
     * to be assigned at either manual mode or schedule mode.  After v3.7.1 the setting for schedules was removed but in order to have backwards
     * compatibility. The schedule settings had to take priority over the manual setting if it was enabled and rolled back into the default
     * setting for manual mode.  As of now there is only one mode that is used for both schedules and manual modes
     *
     * @return void
     */
    protected static function updateArchiveEngine()
    {
        $global = DUP_PRO_Global_Entity::getInstance();
        if ($global->archive_build_mode == $global->archive_build_mode_schedule) {
        // Do nothing
        } else {
            if ($global->archive_build_mode_schedule != DUP_PRO_Archive_Build_Mode::Unconfigured) {
                $schedules = DUP_PRO_Schedule_Entity::getAll();
                if (count($schedules) > 0) {
                    $global->archive_build_mode  = $global->archive_build_mode_schedule;
                    $global->archive_compression = $global->archive_compression_schedule;
                } else {
                    // If there aren't schedules just keep archive build mode the same as it has been
                }

                $global->archive_build_mode_schedule = DUP_PRO_Archive_Build_Mode::Unconfigured;
                $global->save();
            }
        }
    }

    /**
     * Move data tu secure global
     *
     * @return void
     */
    protected static function moveDataToSecureGlobal()
    {
        $global = DUP_PRO_Global_Entity::getInstance();
        if (($global->lkp !== '') || ($global->basic_auth_password !== '')) {
            error_log('setting sglobal');
            $sglobal                      = DUP_PRO_Secure_Global_Entity::getInstance();
            $sglobal->lkp                 = $global->lkp;
            $sglobal->basic_auth_password = $global->basic_auth_password;
            $global->lkp                  = '';
            $global->basic_auth_password  = '';
            $sglobal->save();
            $global->save();
        }
    }
}
