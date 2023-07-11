<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_SINGLE_SITE)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_SINGLE_SITE;
} else {
    return;
}

$overwriteMode = (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL);
$display       = DUPX_InstallerState::getInstance()->isInstType(DUPX_InstallerState::INSTALL_SINGLE_SITE);
?>
<div class="overview-description <?php echo $instTypeClass . ($display ? '' : ' no-display'); ?>">
    <div class="details">
        <table>
            <tr>
                <td>Status:</td>
                <td>
                    <b>Install - Single Site</b>
                    <div class="overview-subtxt-1">
                        This will perform the installation of a single WordPress site.
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
