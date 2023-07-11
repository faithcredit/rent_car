<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>

<div class="hdr-sub3"> <b>Activate <?php echo DUPX_InstallerState::isNewSiteIsMultisite() ? ' Network ' : ' '; ?> Plugins Settings</b></div>
<?php
if (DUPX_InstallerState::isRestoreBackup()) {
    dupxTplRender('parts/restore-backup-mode-notice');
}

$paramsManager->getHtmlFormParam(PrmMng::PARAM_PLUGINS);
