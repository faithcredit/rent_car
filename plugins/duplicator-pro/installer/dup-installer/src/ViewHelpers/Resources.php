<?php

/**
 * @package Duplicator\Installer
 */

namespace Duplicator\Installer\ViewHelpers;

use Duplicator\Installer\Core\Security;
use DUPX_InstallerState;

class Resources
{
    /**
     * Return assets base URL
     *
     * @return string
     */
    public static function getAssetsBaseUrl()
    {
        if (DUPX_InstallerState::isBridgeInstall()) {
            return Security::getInstance()->getOriginalInstallerUrl();
        } else {
            return DUPX_INIT_URL;
        }
    }
}
