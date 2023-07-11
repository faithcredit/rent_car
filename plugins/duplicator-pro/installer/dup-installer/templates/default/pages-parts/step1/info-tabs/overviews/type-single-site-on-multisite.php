<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN;
    $title         = 'Install - Archive Single Site into Subdomain Multisite';
} elseif (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER;
    $title         = 'Install - Archive Single Site into Subfolder Multisite';
} else {
    return;
}

$display = DUPX_InstallerState::getInstance()->isInstType(
    array(
        DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN,
        DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER
    )
);
?>
<div class="overview-description <?php echo $instTypeClass . ($display ? '' : ' no-display'); ?>">
    <div class="details">
        <table>
            <tr>
                <td>Status:</td>
                <td>
                    <b><?php echo $title; ?></b>
                    <div class="overview-subtxt-1">
                        This installation will insert the package site into the current multisite installation.
                    </div>
                </td>
            </tr>
            <tr>
                <td>Mode:</td>
                <td>Custom</td>
            </tr>
        </table>
    </div>
</div>
