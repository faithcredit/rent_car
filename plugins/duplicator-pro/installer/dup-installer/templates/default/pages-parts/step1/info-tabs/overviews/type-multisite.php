<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN;
    $title         = 'Install - Multisite-Subdomain';
} elseif (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER;
    $title         = 'Install - Multisite-Subfolder ';
} else {
    return;
}

$overwriteMode = (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL);
$display       = DUPX_InstallerState::getInstance()->isInstType(
    array(
        DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN,
        DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER
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
                        This is a full multisite installation, all sites in the network will be extracted and installed.
                    </div>

                    <?php if ($overwriteMode) { ?>
                        <div class="overview-subtxt-2">
                            This will clear all site data and the current package will be installed.  This process cannot be undone!
                        </div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td>Mode:</td>
                <td><?php echo DUPX_InstallerState::getInstance()->getHtmlModeHeader(); ?></td>
            </tr>
        </table>
    </div>
</div>
