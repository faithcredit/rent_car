<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>

<div  class="dupx-opts">
    <?php
    if (DUPX_InstallerState::isRestoreBackup()) {
        dupxTplRender('parts/restore-backup-mode-notice');
    } else {
        ?>
    <div class="hdr-sub3">
        Import and Update
    </div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_TABLES);
    }?>
</div>
