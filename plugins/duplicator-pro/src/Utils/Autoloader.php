<?php

/**
 * Auloader calsses
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Utils;

/**
 * Autoloader calss, dont user Duplicator library here
 */
final class Autoloader
{
    const ROOT_NAMESPACE                 = 'Duplicator\\';
    const ROOT_INSTALLER_NAMESPACE       = self::ROOT_NAMESPACE . 'Installer\\';
    const ROOT_ADDON_NAMESPACE           = self::ROOT_NAMESPACE . 'Addons\\';
    const ROOT_ADDON_INSTALLER_NAMESPACE = self::ROOT_INSTALLER_NAMESPACE . 'Addons\\';
    const ROOT_VENDOR                    = 'VendorDuplicator\\';
    const VENDOR_PATH                    = DUPLICATOR____PATH . '/vendor-prefixed/';

    /**
     * Register autoloader function
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'load']);
    }

    /**
     * Load class
     *
     * @param string $className class name
     *
     * @return void
     */
    public static function load($className)
    {
        if (strpos($className, self::ROOT_NAMESPACE) === 0) {
            if (($filepath = self::getAddonFile($className)) === false) {
                foreach (self::getNamespacesMapping() as $namespace => $mappedPath) {
                    if (strpos($className, $namespace) !== 0) {
                        continue;
                    }

                    $filepath = self::getFilenameFromClass($className, $namespace, $mappedPath);
                    if (file_exists($filepath)) {
                        include $filepath;
                        return;
                    }
                }
            } else {
                if (file_exists($filepath)) {
                    include $filepath;
                    return;
                }
            }
        } elseif (strpos($className, self::ROOT_VENDOR) === 0) {
            foreach (self::getNamespacesVendorMapping() as $namespace => $mappedPath) {
                if (strpos($className, $namespace) !== 0) {
                    continue;
                }

                $filepath = self::getFilenameFromClass($className, $namespace, $mappedPath);
                if (file_exists($filepath)) {
                    include $filepath;
                    return;
                }
            }

            if (self::externalLibs($className)) {
                return;
            }
        } else {
            // @todo remove legacy logic in autoloading when duplicator is fully converted.
            $legacyMappging = self::customLegacyMapping();
            $legacyClass    = strtolower(ltrim($className, '\\'));
            if (array_key_exists($legacyClass, $legacyMappging)) {
                if (file_exists($legacyMappging[$legacyClass])) {
                    include $legacyMappging[$legacyClass];
                    return;
                }
            }
        }
    }

    /**
     * Return PHP file full class from class name
     *
     * @param string $class      Name of class
     * @param string $namespace  Base namespace
     * @param string $mappedPath Base path
     *
     * @return string
     */
    protected static function getFilenameFromClass($class, $namespace, $mappedPath)
    {
        $subPath = str_replace('\\', '/', substr($class, strlen($namespace))) . '.php';
        $subPath = ltrim($subPath, '/');
        return rtrim($mappedPath, '\\/') . '/' . $subPath;
    }

    /**
     * Return addon file by class
     *
     * @param string $class class name
     *
     * @return false|string
     */
    protected static function getAddonFile($class)
    {
        $matches = null;
        if (preg_match('/^\\\\?Duplicator(?:\\\\Installer)?\\\\Addons\\\\(.+?)\\\\(.+)$/', $class, $matches) !== 1) {
            return false;
        }

        $addonName = $matches[1];
        $subClass  = $matches[2];
        $basePath  = DUPLICATOR____PATH . '/addons/' . strtolower($addonName) . '/';

        if (strpos($class, self::ROOT_ADDON_INSTALLER_NAMESPACE) === 0) {
            $basePath .= 'installer/' . strtolower($addonName) . '/';
        }

        if (self::endsWith($class, $addonName) === false) {
            $basePath .= 'src/';
        }

        return $basePath . str_replace('\\', '/', $subClass) . '.php';
    }

    /**
     * Return namespace mapping
     *
     * @return string[]
     */
    protected static function getNamespacesMapping()
    {
        // the order is important, it is necessary to insert the longest namespaces first
        return array(
            self::ROOT_INSTALLER_NAMESPACE => DUPLICATOR____PATH . '/installer/dup-installer/src/',
            self::ROOT_NAMESPACE           => DUPLICATOR____PATH . '/src/'
        );
    }

    /**
     * Return namespace mapping
     *
     * @return string[]
     */
    protected static function getNamespacesVendorMapping()
    {
        return array(
            self::ROOT_VENDOR . 'Cron'               => self::VENDOR_PATH . 'other_libs/cron-expression/src/Cron/',
            self::ROOT_VENDOR . 'WpOrg\\Requests'    => self::VENDOR_PATH . 'rmccue/requests/src',
            self::ROOT_VENDOR . 'Amk\\JsonSerialize' => self::VENDOR_PATH . 'andreamk/jsonserialize/src/',
            self::ROOT_VENDOR . 'phpseclib'          => self::VENDOR_PATH . 'phpseclib/phpseclib/phpseclib/',
            self::ROOT_VENDOR . 'ForceUTF8'          => self::VENDOR_PATH . 'neitanod/forceutf8/src/ForceUTF8/'
        );
    }

    /**
     * Load external libs
     *
     * @param string $className class name
     *
     * @return bool return true if class is loaded
     */
    protected static function externalLibs($className)
    {
        switch (ltrim($className, '\\')) {
            case self::ROOT_VENDOR . 'pcrypt':
                require self::VENDOR_PATH . 'other_libs/pcrypt/class.pcrypt.php';
                return true;
            default:
                return false;
        }
    }

    /**
     * Mappgin of some legacy classes
     *
     * @return array<string, string>
     */
    protected static function customLegacyMapping()
    {
        return array(
            'dup_pro_u'                       => DUPLICATOR____PATH . '/classes/utilities/class.u.php',
            'dup_pro_str'                     => DUPLICATOR____PATH . '/classes/utilities/class.u.string.php',
            'dup_pro_date'                    => DUPLICATOR____PATH . '/classes/utilities/class.u.date.php',
            'dup_pro_zip_u'                   => DUPLICATOR____PATH . '/classes/utilities/class.u.zip.php',
            'dup_pro_upgrade_u'               => DUPLICATOR____PATH . '/classes/utilities/class.u.upgrade.php',
            'dup_pro_validator'               => DUPLICATOR____PATH . '/classes/utilities/class.u.validator.php',
            'dup_pro_tree_files'              => DUPLICATOR____PATH . '/classes/utilities/class.u.tree.files.php',
            'dup_pro_mu'                      => DUPLICATOR____PATH . '/classes/utilities/class.u.multisite.php',
            'dup_pro_json_u'                  => DUPLICATOR____PATH . '/classes/utilities/class.u.json.php',
            'dup_pro_migration'               => DUPLICATOR____PATH . '/classes/utilities/class.u.migration.php',
            'dup_pro_low_u'                   => DUPLICATOR____PATH . '/classes/utilities/class.u.low.php',
            'dup_pro_settings_u'              => DUPLICATOR____PATH . '/classes/utilities/class.u.settings.php',
            'dup_pro_brand_entity'            => DUPLICATOR____PATH . '/classes/entities/class.brand.entity.php',
            'dup_pro_global_entity'           => DUPLICATOR____PATH . '/classes/entities/class.global.entity.php',
            'dup_pro_json_entity_base'        => DUPLICATOR____PATH . '/classes/entities/class.json.entity.base.php',
            'dup_pro_package_template_entity' => DUPLICATOR____PATH . '/classes/entities/class.package.template.entity.php',
            'dup_pro_schedule_entity'         => DUPLICATOR____PATH . '/classes/entities/class.schedule.entity.php',
            'dup_pro_schedule_repeat_types'   => DUPLICATOR____PATH . '/classes/entities/class.schedule.entity.php',
            'dup_pro_schedule_days'           => DUPLICATOR____PATH . '/classes/entities/class.schedule.entity.php',
            'dup_pro_secure_global_entity'    => DUPLICATOR____PATH . '/classes/entities/class.secure.global.entity.php',
            'dup_pro_ftp_chunker'             => DUPLICATOR____PATH . '/classes/net/class.ftp.chunker.php',
            'dup_pro_ftpcurl'                 => DUPLICATOR____PATH . '/classes/net/class.ftp.curl.php',
            'dup_pro_gdrive_u'                => DUPLICATOR____PATH . '/classes/net/class.u.gdrive.php',
            'dup_pro_gdriveclient_uploadinfo' => DUPLICATOR____PATH . '/classes/net/class.u.gdrive.php',
            'dup_pro_onedrive_config'         => DUPLICATOR____PATH . '/classes/net/class.u.onedrive.php',
            'dup_pro_onedrive_u'              => DUPLICATOR____PATH . '/classes/net/class.u.onedrive.php',
            'dup_pro_s3_client_uploadinfo'    => DUPLICATOR____PATH . '/classes/net/class.u.s3.php',
            'dup_pro_s3_u'                    => DUPLICATOR____PATH . '/classes/net/class.u.s3.php',
            'dup_pro_dropboxv2client_uploadinfo' => DUPLICATOR____PATH . '/lib/DropPHP/DropboxV2Client.php',
            'dup_pro_dropboxv2client'         => DUPLICATOR____PATH . '/lib/DropPHP/DropboxV2Client.php',
            'dup_pro_storage_entity'          => DUPLICATOR____PATH . '/classes/entities/class.storage.entity.php',
            'dup_pro_storage_types'           => DUPLICATOR____PATH . '/classes/entities/class.storage.entity.php',
            'dup_pro_virtual_storage_ids'     => DUPLICATOR____PATH . '/classes/entities/class.storage.entity.php',
            'dup_pro_dropbox_authorization_states' => DUPLICATOR____PATH . '/classes/entities/class.storage.entity.php',
            'dup_pro_onedrive_authorization_states' => DUPLICATOR____PATH . '/classes/entities/class.storage.entity.php',
            'dup_pro_gdrive_authorization_states' => DUPLICATOR____PATH . '/classes/entities/class.storage.entity.php',
            'dup_pro_system_global_entity'    => DUPLICATOR____PATH . '/classes/entities/class.system.global.entity.php',
            'dup_pro_recommended_fix'         => DUPLICATOR____PATH . '/classes/entities/class.system.global.entity.php',
            'dup_pro_verifier_base'           => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_required_verifier'       => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_range_verifier'          => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_length_verifier'         => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_regex_verifier'          => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_email_verifier'          => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_storagesupported'        => DUPLICATOR____PATH . '/classes/storage/class.storage.supported.php',
            'dup_pro_package_runner'          => DUPLICATOR____PATH . '/classes/package/class.pack.runner.php',
            'dup_pro_package'                 => DUPLICATOR____PATH . '/classes/package/class.pack.php',
            'dup_pro_package_importer'        => DUPLICATOR____PATH . '/classes/package/class.pack.importer.php',
            'dup_pro_package_recover'         => DUPLICATOR____PATH . '/classes/package/class.pack.recover.php',
            'dup_pro_archive'                 => DUPLICATOR____PATH . '/classes/package/class.pack.archive.php',
            'dup_pro_database'                => DUPLICATOR____PATH . '/classes/package/class.pack.database.php',
            'dup_pro_installer'               => DUPLICATOR____PATH . '/classes/package/class.pack.installer.php',
            'dup_pro_custom_host_manager'     => DUPLICATOR____PATH . '/classes/host/class.custom.host.manager.php',
            'dup_pro_ui'                      => DUPLICATOR____PATH . '/classes/ui/class.ui.php',
            'dup_pro_ui_alert'                => DUPLICATOR____PATH . '/classes/ui/class.ui.alert.php',
            'dup_pro_ui_viewstate'            => DUPLICATOR____PATH . '/classes/ui/class.ui.viewstate.php',
            'dup_pro_ui_dialog'               => DUPLICATOR____PATH . '/classes/ui/class.ui.dialog.php',
            'dup_pro_ui_notice'               => DUPLICATOR____PATH . '/classes/ui/class.ui.notice.php',
            'dup_pro_ui_messages'             => DUPLICATOR____PATH . '/classes/ui/class.ui.messages.php',
            'dup_pro_ui_screen'               => DUPLICATOR____PATH . '/classes/ui/class.ui.screen.base.php',
            'dup_pro_archive_config'          => DUPLICATOR____PATH . '/classes/class.archive.config.php',
            'dup_pro_php_log'                 => DUPLICATOR____PATH . '/classes/class.php.log.php',
            'dup_pro_constants'               => DUPLICATOR____PATH . '/classes/class.constants.php',
            'dup_pro_db'                      => DUPLICATOR____PATH . '/classes/class.db.php',
            'dup_pro_plugin_upgrade'          => DUPLICATOR____PATH . '/classes/class.plugin.upgrade.php',
            'dup_pro_log'                     => DUPLICATOR____PATH . '/classes/class.logging.php',
            'dup_pro_handler'                 => DUPLICATOR____PATH . '/classes/class.logging.php',
            'dup_pro_server'                  => DUPLICATOR____PATH . '/classes/class.server.php',
            'dup_pro_package_pagination'      => DUPLICATOR____PATH . '/classes/class.package.pagination.php',
            'dup_pro_web_services'            => DUPLICATOR____PATH . '/ctrls/class.web.services.php',
            'dup_pro_ctrl_package'            => DUPLICATOR____PATH . '/ctrls/ctrl.package.php',
            'dup_pro_ctrl_tools'              => DUPLICATOR____PATH . '/ctrls/ctrl.tools.php',
            'dup_pro_ctrl_recovery'           => DUPLICATOR____PATH . '/ctrls/ctrl.recovery.php',
            'dup_pro_package_screen'          => DUPLICATOR____PATH . '/views/packages/screen.php'
        );
    }

    /**
     * Returns true if the $haystack string end with the $needle, only for internal use
     *
     * @param string $haystack The full string to search in
     * @param string $needle   The string to for
     *
     * @return bool Returns true if the $haystack string starts with the $needle
     */
    protected static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
