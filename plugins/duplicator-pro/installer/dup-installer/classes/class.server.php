<?php

defined("DUPXABSPATH") or die("");

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Shell\Shell;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapWP;

/**
 * DUPX_cPanel
 * Wrapper Class for cPanel API  */
class DUPX_Server
{
    /**
     * A list of the core WordPress directories
     */
    public static $wpCoreDirsList = array(
        'wp-admin',
        'wp-includes'
    );

    /**
     * Return PHP safe nome, on PHP 5.4 is always false
     *
     * @return bool
     */
    public static function phpSafeModeOn()
    {
        // safe_mode  has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0.
        return false;
    }

    /**
     *  Returns the path this this server where the zip command can be called
     *
     *  @return null|string     // null if can't find unzip
     */
    public static function get_unzip_filepath()
    {
        $filepath = null;
        if (Shell::runCommand('echo duplicator', Shell::AVAILABLE_COMMANDS) !== false) {
            $shellOutput = Shell::runCommand('hash unzip 2>&1', Shell::AVAILABLE_COMMANDS);
            if ($shellOutput !== false && $shellOutput->isEmpty()) {
                $filepath = 'unzip';
            } else {
                $possible_paths = array('/usr/bin/unzip', '/opt/local/bin/unzip');
                foreach ($possible_paths as $path) {
                    if (file_exists($path)) {
                        $filepath = $path;
                        break;
                    }
                }
            }
        }
        return $filepath;
    }

    /**
     *
     * @return string[]
     */
    public static function getWpAddonsSiteLists()
    {
        $addonsSites  = array();
        $pathsToCheck = DUPX_ArchiveConfig::getInstance()->getPathsMapping();

        if (is_scalar($pathsToCheck)) {
            $pathsToCheck = array($pathsToCheck);
        }

        foreach ($pathsToCheck as $mainPath) {
            SnapIO::regexGlobCallback($mainPath, function ($path) use (&$addonsSites) {
                if (SnapWP::isWpHomeFolder($path)) {
                    $addonsSites[] = $path;
                }
            }, array(
                'regexFile' => false,
                'recursive' => true
            ));
        }

        return $addonsSites;
    }

    /**
     * Does the site look to be a WordPress site
     *
     * @return bool     Returns true if the site looks like a WP site
     */
    public static function isWordPress()
    {
        $absPathNew = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
        if (!is_dir($absPathNew)) {
            return false;
        }
        if (($root_files = scandir($absPathNew)) == false) {
            return false;
        }
        $file_count = 0;
        foreach ($root_files as $file) {
            if (in_array($file, self::$wpCoreDirsList)) {
                $file_count++;
            }
        }
        return (count(self::$wpCoreDirsList) == $file_count);
    }
}
