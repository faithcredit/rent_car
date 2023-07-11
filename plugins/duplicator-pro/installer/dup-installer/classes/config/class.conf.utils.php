<?php

/**
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX
 */

use Duplicator\Installer\Core\Security;
use Duplicator\Libs\Snap\FunctionalityCheck;
use Duplicator\Libs\Snap\SnapUtil;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * In this class all the utility functions related to the wordpress configuration and the package are defined.
 */
class DUPX_Conf_Utils
{
    /**
     *
     * @staticvar bool $present
     * @return    bool
     */
    public static function isManualExtractFilePresent()
    {
        static $present = null;
        if (is_null($present)) {
            $present = file_exists(DUPX_Package::getManualExtractFile());
        }
        return $present;
    }

    /**
     *
     * @staticvar null|bool $enable
     * @return    bool
     */
    public static function isShellZipAvaiable()
    {
        static $enable = null;
        if (is_null($enable)) {
            $enable = DUPX_Server::get_unzip_filepath() != null;
        }
        return $enable;
    }

    /**
     *
     * @return bool
     */
    public static function isPhpZipAvaiable()
    {
        return SnapUtil::classExists(ZipArchive::class);
    }

    /**
     *
     * @staticvar bool $exists
     * @return    bool
     */
    public static function archiveExists()
    {
        static $exists = null;
        if (is_null($exists)) {
            $exists = file_exists(Security::getInstance()->getArchivePath());
        }
        return $exists;
    }

    /**
     * Get archive size
     *
     * @return int
     */
    public static function archiveSize()
    {
        static $arcSize = null;
        if (is_null($arcSize)) {
            $archivePath = Security::getInstance()->getArchivePath();
            $arcSize     = file_exists($archivePath) ? (int) @filesize($archivePath) : 0;
        }
        return $arcSize;
    }
}
