<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * passed values
 *
 * @var ?DUP_PRO_Package_Recover $recoverPackage
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


if (empty($recoveablePackages)) {
    return;
}

$installerLink = ($recoverPackage instanceof DUP_PRO_Package_Recover) ? $recoverPackage->getInstallLink() : '';
$disabledClass = empty($installerLink) ? 'disabled' : '';

if ($displayCopyLink) {
    $toolTipContent  = __(
        'The recovery point URL is the link to the recovery point package installer. ' .
        'The link will run the installer wizard used to re-install and recover the site.  ' .
        'Copy this link and keep it in a safe location to easily restore this site.',
        'duplicator-pro'
    );
    $toolTipContent .= '<br><br><b>';
    $toolTipContent .= __('This URL is valid until another recovery point is set.', 'duplicator-pro');
    $toolTipContent .= '</b>';
    ?>
    <label>
        <i class="fas fa-question-circle fa-sm"
            data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Recovery Point URL"); ?>"
            data-tooltip="<?php echo esc_attr($toolTipContent); ?>"
        >
        </i> 
        <b><?php DUP_PRO_U::_e('Step 2 '); ?>:</b> <i><?php DUP_PRO_U::_e('Copy Recovery URL &amp; Store in Safe Place'); ?></i>
    </label>
    <div class="copy-link <?php echo $disabledClass; ?>"
         data-dup-copy-value="<?php echo esc_url($installerLink); ?>"
         data-dup-copy-title="<?php DUP_PRO_U::_e("Copy Recovery URL to clipboard"); ?>"
         data-dup-copied-title="<?php DUP_PRO_U::_e("Recovery URL copied to clipboard"); ?>" >
        <div class="content" >
            <?php echo empty($installerLink) ? DUP_PRO_U::__('Please set the Recovery Point to generate the Recovery URL') : $installerLink; ?>
        </div>
        <i class="far fa-copy copy-icon"></i>
    </div>
<?php } ?>
<div class="dup-pro-recovery-buttons" >
    <?php
    if ($displayLaunch) { ?>
        <a href="<?php echo esc_url($installerLink); ?>"
           class="button button-primary dup-pro-launch <?php echo $disabledClass; ?>" target="_blank"
           title="<?php DUP_PRO_U::_e('Initiates system recovery using the Recovery Point URL.'); ?>" >
            <i class="fas fa-external-link-alt" ></i>&nbsp;&nbsp;<?php DUP_PRO_U::_e('Launch Recovery'); ?>
        </a>
        <?php
    }
    if ($displayDownload) {
        if (!isset($downloadLauncherData)) {
            $downloadLauncherData = '';
        }
        ?>
        <button type="button" class="button button-primary dup-pro-recovery-download-launcher <?php echo $disabledClass; ?>" 
                title="<?php DUP_PRO_U::_e('This button downloads a recovery launcher that allows you to perform the recovery with a simple click of the downloaded file.'); ?>"
                data-download-laucher="<?php echo esc_attr(json_encode($downloadLauncherData)); ?>" >
            <i class="fa fa-rocket" ></i>&nbsp;&nbsp;<?php DUP_PRO_U::_e('Download'); ?>
        </button>
        <?php
    }
    if ($displayCopyButton) {
        ?>
        <button type="button" class="button button-primary dup-pro-recovery-copy-url <?php echo $disabledClass; ?>" 
                data-dup-copy-value="<?php echo $installerLink; ?>"
                data-dup-copy-title="<?php DUP_PRO_U::_e("Copy Recovery URL to clipboard"); ?>"
                data-dup-copied-title="<?php DUP_PRO_U::_e("Recovery URL copied to clipboard"); ?>" >
            <i class="far fa-copy copy-icon"></i>&nbsp;&nbsp;<?php DUP_PRO_U::_e('Copy URL'); ?>
        </button>
        <?php
    }
    ?>
</div>
