<?php

/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Installer\Core\Params\PrmMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var int $tableCount
 */
$paramsManager     = PrmMng::getInstance();
$isAdvancedConfirm = DUPX_InstallerState::isAddSiteOnMultisite();
$recoveryLink      = PrmMng::getInstance()->getValue(PrmMng::PARAM_RECOVERY_LINK);
$txtTable          = $tableCount . ' table' . ($tableCount == 1 ? '' : 's');
$checkAdvLabel     = empty($recoveryLink)
                    ? 'Are you sure you want to proceed without a Recovery Point?'
                    : 'I confirm that have a copy of the Recovery URL';
?>
<div id="db-install-dialog-confirm" title="Install Confirmation" style="display:none">
    <p>
        <i>Run installer with these settings?</i>
    </p>

    <div class="hdr-sub3">
        Site Settings
    </div>
   
    <?php if (DUPX_InstallerState::isAddSiteOnMultisite()) {
            /** @var SiteOwrMap[] */
            $overwriteMapping = PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING);
        ?>
        <ul class="margin-bottom-1" >
        <?php foreach ($overwriteMapping as $map) {
            $sourceInfo = $map->getSourceSiteInfo();
            ?>
            <li>
            <?php
            switch ($map->getTargetId()) {
                case SiteOwrMap::NEW_SUBSITE_WITH_SLUG:
                case SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN:
                    ?>
                    Install site <b><?php echo $sourceInfo['fullHomeUrl']; ?></b> on new site
                    <?php
                    break;
                default:
                    $targetInfo = $map->getTargetSiteInfo();
                    ?>
                    <i class="fas fa-exclamation-triangle maroon"></i> 
                    Overwrite site <b><?php echo $targetInfo['fullHomeUrl']; ?></b> with <b><?php echo $sourceInfo['fullHomeUrl']; ?></b>
                    <?php
                    break;
            }
            ?>
            </li>
        <?php } ?>
        </ul>
    <?php } else { ?>
        <table class="margin-bottom-1 margin-left-1  dup-s1-confirm-dlg">
            <tr>
                <td>Install Type: &nbsp; </td>
                <td><?php echo DUPX_InstallerState::installTypeToString(); ?></td>
            </tr>
            <tr>
                <td>New URL:</td>
                <td><i id="dlg-url-new"><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_URL_NEW)); ?></i></td>
            </tr>
            <tr>
                <td>New Path:</td>
                <td><i id="dlg-path-new"><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_PATH_NEW)); ?></i></td>
            </tr>
        </table> 
    <?php } ?>

    <div class="hdr-sub3">
       Database Settings
    </div>
    <table class="margin-left-1 margin-bottom-1 dup-s1-confirm-dlg">
        <tr>
            <td>Server:</td>
            <td><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_DB_HOST)); ?></td>
        </tr>
        <tr>
            <td>Name:</td>
            <td><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_DB_NAME)); ?></td>
        </tr>
        <tr>
            <td>User:</td>
            <td><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_DB_USER)); ?></td>
        </tr>
        <tr>
            <td>Data:</td>
            <?php if ($tableCount > 0) : ?>
                <td class="maroon">
                    <?php echo $tableCount . " existing table" . ($tableCount == 1 ? '' : 's') . " will be overwritten or modified in the database"; ?>
                </td>
            <?php else : ?>
                <td>
                    No existing tables will be overwritten in the database
                </td>
            <?php endif; ?>
        </tr>
    </table>

    <?php if ($tableCount > 0) { ?>
        <div class="margn-bottom-1" >
            <small class="maroon">
                <i class="fas fa-exclamation-circle"></i>
                NOTICE: Be sure the database parameters are correct! This database contains <b><u><?php echo $txtTable; ?></u></b> that will be modified
                and/or removed! Only proceed if the data is no longer needed. Entering the wrong information WILL overwrite an existing database.
                Make sure to have backups of all your data before proceeding.
            </small>
        </div>
    <?php } ?>

    <?php if ($tableCount > 0 && $isAdvancedConfirm) { ?>
        <div class="advanced-confirm">
            <hr class="separator" >
            <div class="maroon" >
                <b>Multisite Subsite Validation:</b><br/>
                <label>
                    <input type="checkbox" id="dialog-adv-confirm-check" > <?php echo $checkAdvLabel; ?> 
                </label>
                <?php if (!empty($recoveryLink)) { ?>
                    <span class="copy-link secondary-btn"  
                        data-dup-copy-value="<?php echo DUPX_U::esc_url($recoveryLink); ?>"
                        data-dup-copy-title="<?php echo DUPX_U::esc_attr("Copy Recovery URL to clipboard"); ?>"
                        data-dup-copied-title="<?php echo DUPX_U::esc_attr("Recovery URL copied to clipboard"); ?>" >
                        Copy
                        <i class="far fa-copy copy-icon"></i>
                    </span>
                <?php } ?>
                <br>

                <?php if (empty($recoveryLink)) { ?>
                    <small>
                        This is a delicate operation and if there is a problem you won't be able to recover your site!
                    </small>
                <?php } else { ?>
                    <small>
                        You are about to proceed with a delicate operation. 
                        Be sure to copy and paste the recovery point URL to a safe spot so you can recover the original site should a problem occur.
                    </small>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>
