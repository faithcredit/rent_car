<?php

defined("ABSPATH") or die("");

/**
 * Check different type of storage supported in the installed machine.
 */
class DUP_PRO_StorageSupported
{
    /**
     * Check whether GDrive supported in this server
     *
     * @return bool true if GDrive supported in this machine, otherwise false.
     */
    public static function isGDriveSupported()
    {
        return (self::isCURLExtensionEnabled() || self::getAllowUrlFopenPHPSetting());
    }

    /**
     * Check whether GDrive supported in this server
     *
     * @return bool true if GDrive supported in this machine, otherwise false.
     */
    public static function isOneDriveSupported()
    {
        return self::isCURLExtensionEnabled();
    }

    /**
     * Check whether $storageObj's storage type is supported.
     *
     * @todo Make same changes for other storage type too
     *
     * @param $storageObj object instance of DUP_PRO_Storage_Entity.
     *
     * @return bool true if Storage object's storage type is supported otherwise return false.
     */
    public static function isStorageObjStorageTypeSupported($storageObj)
    {

        switch ($storageObj->get_storage_type()) {
            case DUP_PRO_Storage_Types::GDrive:
                return DUP_PRO_StorageSupported::isGDriveSupported();
            case DUP_PRO_Storage_Types::OneDrive:
                return DUP_PRO_StorageSupported::isOneDriveSupported();
            default:
                // Do nothing
        }

        return true;
    }

    /**
     * Get GDrive not supported notices
     *
     * @static
     *
     * @return array notices string as array values
     */
    public static function getGDriveNotSupportedNotices()
    {
        $notices = array();

        if (!self::isGDriveSupported()) {
            if (!self::isCURLExtensionEnabled() && !self::getAllowUrlFopenPHPSetting()) {
                $notices[] = DUP_PRO_U::esc_html__('Google Drive requires either the PHP CURL extension enabled or the allow_url_fopen runtime configuration to be enabled.');
            } elseif (!self::isCURLExtensionEnabled()) {
                $notices[] = DUP_PRO_U::esc_html__('Google Drive requires the PHP CURL extension enabled.');
            } elseif (!self::getAllowUrlFopenPHPSetting()) {
                $notices[] = DUP_PRO_U::esc_html__('Google Drive requires the allow_url_fopen runtime configuration to be enabled.');
            }
        }

        return $notices;
    }

    /**
     * Get GDrive not supported notices
     *
     * @static
     *
     * @return array notices string as array values
     */
    public static function getOneDriveNotSupportedNotices()
    {
        $notices = array();

        if (!self::isOneDriveSupported()) {
            if (!self::isCURLExtensionEnabled()) {
                $notices[] = DUP_PRO_U::esc_html__('OneDrive requires the PHP CURL extension enabled.');
            }
        }

        return $notices;
    }

    /**
     * Checks whether PHP CURL extension enabled or not.
     *
     * @return bool true if CURL Extension enabled in this machine, otherwise false.
     */
    private static function isCURLExtensionEnabled()
    {
        return function_exists('curl_version') && function_exists('curl_exec');
    }

    /**
     * Get allow_url_fopen php.ini setting value
     *
     * @return bool allow_url_fopen setting value
     */
    private static function getAllowUrlFopenPHPSetting()
    {
        if (function_exists('ini_get')) {
            return ini_get('allow_url_fopen');
        } else {
            return false;
        }
    }
}
