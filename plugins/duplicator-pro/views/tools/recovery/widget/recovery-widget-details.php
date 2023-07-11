<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;

/**
 * passed values
 *
 * @var DUP_PRO_Package_Recover $recoverPackage
 * @var int $recoverPackageId
 * @var array<int, array{id: int, created: string, nameHash: string, name: string}> $recoveablePackages
 * @var bool $selector
 * @var string $subtitle
 * @var bool $displayCopyLink
 * @var bool $displayCopyButton
 * @var bool $displayLaunch
 * @var bool $displayDownload
 * @var bool $displayInfo
 * @var string $viewMode
 * @var string $importFailMessage
 */

switch ($viewMode) {
    case DUP_PRO_CTRL_recovery::VIEW_WIDGET_NO_PACKAGE_SET:
        ?>
        <div class="dup-pro-recovery-active-link-header">
            <i class="fas fa-undo-alt main-icon"></i>
            <div class="main-title">
                Recovery point not active.
            </div>
            <div class="main-subtitle margin-bottom-1">
                <b>Status:</b>&nbsp;
                <span class="dup-pro-recovery-status red"><?php _e('not set', 'duplicator-pro'); ?></span>
            </div>
        </div>
        <div class="margin-bottom-1">
            <?php
            DUP_PRO_U::_e(
                'A Recovery Point allows one to quickly restore the site to a prior state. '
                . 'To use this, mark a package as the Recovery Point, then copy and save off the associated URL. '
                . 'Then, if a problem occurs, browse to the URL to launch a streamlined installer to quickly restore the site.'
            );
            ?>
        </div>
        <?php
        break;
    case DUP_PRO_CTRL_recovery::VIEW_WIDGET_NOT_VALID:
        ?>
        <div class="orangered margin-bottom-1">
            <?php echo esc_html($importFailMessage); ?>
        </div>
        <?php
        break;
    case DUP_PRO_CTRL_recovery::VIEW_WIDGET_VALID:
        $downloadLauncherData = array(
            'fileName'    => $recoverPackage->getLauncherFileName(),
            'fileContent' => SnapIO::getInclude(dirname(__FILE__) . '/recovery-download-launcher-content.php', array(
                'recoverPackage' => $recoverPackage
            ))
        );
        $tooltipContent       = __(
            'A package is considered "out of date" if it is older than 12 hours. ' .
            'If this site\'s data has changed in the last 12 hours then you might want to consider building a newer recovery point.',
            'duplicator-pro'
        );
        ?>
        <div class="dup-pro-recovery-active-link-wrapper" >
            <div class="dup-pro-recovery-active-link-header" >
                <i class="fas fa-undo-alt main-icon"></i>
                <div class="main-title" >
                    <?php DUP_PRO_U::_e('Recovery point is active'); ?>
                    <i
                        class="fas fa-question-circle fa-sm"
                        data-tooltip="<?php echo esc_attr($tooltipContent)?>"
                    ></i>
                </div>
                <div class="main-subtitle margin-bottom-1" >
                    <b><?php DUP_PRO_U::_e('Status:') ?></b>&nbsp;
                    <?php if ($recoverPackage->isOutToDate()) { ?>
                        <span class="dup-pro-recovery-status red"><?php DUP_PRO_U::_e('out of date') ?></span>
                    <?php } else { ?>
                        <span class="dup-pro-recovery-status green"><?php DUP_PRO_U::_e('ready') ?></span>
                    <?php } ?>
                </div>
            </div>
            <?php echo $subtitle; ?>
            <?php if ($displayInfo) { ?>
                <div class="dup-pro-recovery-package-info margin-bottom-1" >
                    <table>
                        <tbody>
                            <tr>
                                <td><?php DUP_PRO_U::_e('Name'); ?>:</td>
                                <td><b><?php echo esc_html($recoverPackage->getPackageName()); ?></b></td>
                            </tr>
                            <tr>
                                <td><?php DUP_PRO_U::_e('Date'); ?>:</td>
                                <td><b><?php echo esc_html($recoverPackage->getCreated()); ?></b></td>
                            </tr>
                            <tr>
                                <td><?php DUP_PRO_U::_e('Age'); ?>:</td>
                                <td>
                                    <b><?php
                                    $hours = $recoverPackage->getPackageLife('hours');
                                    printf(_n('Created %d hour ago.', 'Created %d hours ago.', $hours, 'duplicator-pro'), $hours);
                                    ?></b>&nbsp; 
                                    <i><?php DUP_PRO_U::_e('All changes made after package creation will be lost.'); ?></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
        break;
    default:
        ?>
        <p class="orangered">
            <?php echo DUP_PRO_U::__('Invalid view mode.'); ?>
        </p>
        <?php
}
