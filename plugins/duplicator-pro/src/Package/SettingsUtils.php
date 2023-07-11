<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package;

use DUP_PRO_Archive_Build_Mode;
use DUP_PRO_Global_Entity;
use Duplicator\Controllers\SettingsPageController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\DupArchive\DupArchive;
use Duplicator\Utils\ZipArchiveExtended;
use Exception;

class SettingsUtils
{
    /**
     * Return true if archive encryption is available
     *
     * @param string $unavaliableMessage if encryption isn't available the reason is put here
     *
     * @return bool
     */
    public static function isArchiveEncryptionAvaiable(&$unavaliableMessage = '')
    {
        try {
            $global   = DUP_PRO_Global_Entity::getInstance();
            $guideMsg = sprintf(
                _x(
                    'For more details please see the %1$suser guide%2$s',
                    '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                    'duplicator-pro'
                ),
                '<a href="https://snapcreek.com/duplicator/docs/guide/#guide-packs-010-q" target="_blank">',
                '</a>'
            );

            switch ($global->archive_build_mode) {
                case DUP_PRO_Archive_Build_Mode::Shell_Exec:
                    break;
                case DUP_PRO_Archive_Build_Mode::ZipArchive:
                    if (ZipArchiveExtended::isEncryptionAvaliable() == false) {
                        $settingsLink = ControllersManager::getInstance()->getMenuLink(
                            ControllersManager::SETTINGS_SUBMENU_SLUG,
                            SettingsPageController::L2_SLUG_PACKAGE
                        );

                        $msg = __('To enable this feature consider the following options:', 'duplicator-pro');
                        ob_start();
                        ?>
                        <ul class="dup-tabs-opts-help">
                            <li>
                                <?php _e('Upgrade this server to PHP 7.2+ anz Libzip 1.2+ for ZIP encryption support. ', 'duplicator-pro'); ?>
                            </li>
                            <li>
                                <?php
                                    printf(
                                        _x(
                                            'Change the %1$sArchive Engine%2$s settings to DupArchive.',
                                            '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                                            'duplicator-pro'
                                        ),
                                        '<a href="' . $settingsLink . '" target="_blank">',
                                        '</a>'
                                    );
                                ?>
                            </li>
                        </ul>
                        <?php
                        $msg .= ob_get_clean();
                        throw new Exception($msg . $guideMsg);
                    }
                    break;
                case DUP_PRO_Archive_Build_Mode::DupArchive:
                    if (DupArchive::isEncryptionAvaliable() == false) {
                        $msg = sprintf(
                            _x(
                                'To enable encryption on the DupArchive format, contact your host and make sure they have enabled the %1$sOpenSSL module%2$s.',
                                '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                                'duplicator-pro'
                            ),
                            '<a href="https://www.php.net/manual/en/book.openssl.php" target="_blank">',
                            '</a>'
                        );
                        throw new Exception($msg . '<br>' . $guideMsg);
                    }
                    break;
                case DUP_PRO_Archive_Build_Mode::Unconfigured:
                default:
                    throw new Exception('Invalid build mode');
            }
        } catch (Exception $e) {
            $unavaliableMessage = $e->getMessage();
            return false;
        }

        return true;
    }
}
