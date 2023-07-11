<?php

/**
 * Class used to update and edit web server configuration files
 * for both Apache and IIS files .htaccess and web.config
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\WPConfig
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Deploy\ServerConfigs;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\InstallerOrigFileMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\WpConfig\WPConfigTransformer;

class DUPX_WPConfig
{
    const ADMIN_SERIALIZED_SECURITY_STRING = 'a:1:{s:13:"administrator";b:1;}';
    const ADMIN_LEVEL                      = 10;
/**
     * get wp-config default path (not relative to orig file manger)
     *
     * @return string
     */
    public static function getWpConfigDeafultPath()
    {
        return PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . '/wp-config.php';
    }

    /**
     *
     * @return bool|string false if fail
     */
    public static function getWpConfigPath()
    {
        $origWpConfTarget = InstallerOrigFileMng::getInstance()->getEntryTargetPath(ServerConfigs::CONFIG_ORIG_FILE_WPCONFIG_ID, self::getWpConfigDeafultPath());
        $origWpDir        = SnapIO::safePath(dirname($origWpConfTarget));
        if ($origWpDir === PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW)) {
            return $origWpConfTarget;
        } else {
            return PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW) . "/wp-config.php";
        }
    }

    /**
     *
     * @staticvar boolean|WPConfigTransformer $confTransformer
     *
     * @return boolean|WPConfigTransformer
     */
    public static function getLocalConfigTransformer()
    {
        static $confTransformer = null;
        if (is_null($confTransformer)) {
            try {
                if (($wpConfigPath = ServerConfigs::getWpConfigLocalStoredPath()) === false) {
                    $wpConfigPath = DUPX_WPConfig::getWpConfigPath();
                }
                if (is_readable($wpConfigPath)) {
                    $confTransformer = new WPConfigTransformer($wpConfigPath);
                } else {
                    $confTransformer = false;
                }
            } catch (Exception $e) {
                $confTransformer = false;
            }
        }

        return $confTransformer;
    }

    /**
     *
     * @param string $name
     * @param string $type    constant | variable
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function getValueFromLocalWpConfig($name, $type = 'constant', $default = '')
    {
        if (($confTransformer = self::getLocalConfigTransformer()) !== false) {
            return $confTransformer->exists($type, $name) ? $confTransformer->getValue($type, $name) : $default;
        } else {
            return null;
        }
    }

    /**
     * Check if the wp-config of the source site is valid.
     *
     * @return bool true on success, false on failure
     */
    public static function isSourceWpConfigValid()
    {
        static $wpConfigValid = null;
        if (is_null($wpConfigValid)) {
            try {
                if (($wpConfigPath = ServerConfigs::getSourceWpConfigPath()) == false) {
                    throw new Exception('Source wp-config.php don\'t exists');
                }
                $configTransformer = new WPConfigTransformer($wpConfigPath);
                $requiredConst     = array('DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST');
                foreach ($requiredConst as $constName) {
                    if (!$configTransformer->exists('constant', $constName)) {
                        throw new Exception($constName . ' don\'t exist');
                    }
                }
                $wpConfigValid = true;
            } catch (Exception $e) {
                Log::info('CHECK WP CONFIG FAIL msg: ' .  $e->getMessage());
                $wpConfigValid = false;
            } catch (Error $e) {
                Log::info('CHECK WP CONFIG FAIL msg: ' .  $e->getMessage());
                $wpConfigValid = false;
            }
        }
        return $wpConfigValid;
    }
}
