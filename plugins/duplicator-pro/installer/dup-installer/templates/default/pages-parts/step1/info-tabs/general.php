<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div id="tabs-1">
    <?php

    dupxTplRender('pages-parts/step1/info-tabs/overview-description');
    if (!DUPX_InstallerState::isRecoveryMode()) {
        ?>
        <div class="margin-top-1" ></div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_INST_TYPE);
    }

    $paramsManager->getHtmlFormParam(PrmMng::PARAM_SUBSITE_ID);

    if (DUPX_InstallerState::isAddSiteOnMultisiteAvaiable()) {
        ?>
        <div id="overwrite-subsite-on-multisite-wrapper" class="<?php echo DUPX_InstallerState::isAddSiteOnMultisite() ? '' : 'no-display'; ?>">
            <?php $paramsManager->getHtmlFormParam(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING); ?>
        </div>
    <?php }
    if (DUPX_InstallerState::isMultisiteInstallAvaiable()) {
        ?>
        <div id="url-multisite-mapping-wrapper" class="<?php echo DUPX_InstallerState::isMultisiteInstall() ? '' : 'no-display'; ?>">
            <?php  $paramsManager->getHtmlFormParam(PrmMng::PARAM_MU_REPLACE); ?>
        </div>
    <?php } ?>
</div>
