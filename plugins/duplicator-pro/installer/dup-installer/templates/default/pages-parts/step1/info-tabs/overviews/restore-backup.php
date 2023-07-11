<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE;
    $title         = 'Restore - Single Site Backup';
} elseif (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBDOMAIN)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBDOMAIN;
    $title         = 'Restore - Multisite-Subdomain Backup';
} elseif (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBFOLDER)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBFOLDER;
    $title         = 'Restore - Multisite-Subdomain Backup';
} else {
    return;
}

$overwriteMode = (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL);
$display       = DUPX_InstallerState::getInstance()->isInstType(
    array(
        DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE,
        DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBDOMAIN,
        DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBFOLDER
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
                        The restore backup mode restores the original site by not performing any processing on the database or tables.
                        This ensures that the exact copy of the original site is restored.
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
                <td>Custom <i>(Restore Install)</i></td>
            </tr>
        </table>
    </div>
</div>
