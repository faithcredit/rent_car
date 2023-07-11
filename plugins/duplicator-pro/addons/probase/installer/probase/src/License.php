<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Addons\ProBase;

use Duplicator\Installer\Core\Params\PrmMng;
use DUPX_U;

class License extends AbstractLicense
{
    /**
     * Returns the license type this installer file is made of.
     *
     * @return int Returns an enum type of License
     */
    public static function getType()
    {
        return self::getBestLicense(self::getImporterLicense(), self::getInstallerLicense());
    }

    /**
     * Return license limit
     *
     * @return int<0, max>
     */
    public static function getLimit()
    {
        return (int) max(0, (int) \DUPX_ArchiveConfig::getInstance()->license_limit);
    }

    /**
     * Return upsell URL
     *
     * @return string
     */
    public static function getUpsellURL()
    {
        return 'https://duplicator.com/dashboard/';
    }

    /**
     * Get license on installer from package data
     *
     * @return int  Returns an enum type of License
     */
    protected static function getInstallerLicense()
    {
        return \DUPX_ArchiveConfig::getInstance()->license_type;
    }

    /**
     * Get importer license from params data
     *
     * @return int  Returns an enum type of License
     */
    protected static function getImporterLicense()
    {
        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        return isset($overwriteData['dupLicense']) ? $overwriteData['dupLicense'] : self::TYPE_UNLICENSED;
    }

    /**
     * Return license required note
     *
     * @return string
     */
    public static function getLicenseUpdateText()
    {
        return 'This option isn\'t available at the <b>' . static::getLicenseToString() . '</b> license level.' .
        'To enable this option ' .
        '<a href="' .  DUPX_U::esc_url(static::getUpsellURL()) . '" target="_blank">' . 'upgrade' . '</a>' .
        ' the License.';
    }
}
