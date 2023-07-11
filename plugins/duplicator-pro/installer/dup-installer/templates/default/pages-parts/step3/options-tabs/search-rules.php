<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
if (DUPX_InstallerState::isRestoreBackup()) {
    ?>
    <div class="hdr-sub3">Search & replace settings</div>
    <?php
    dupxTplRender('parts/restore-backup-mode-notice');
    return;
}
?>
<div class="hdr-sub3">Custom Search &amp; Replace</div>
<table class="s3-opts" id="search-replace-table">
    <tr valign="top" id="search-0">
        <td>Search:</td>
        <td><input class="w95" type="text" name="search[]" style="margin-right:5px"></td>
    </tr>
    <tr valign="top" id="replace-0"><td>Replace:</td><td><input class="w95" type="text" name="replace[]"></td></tr>
</table>
<button 
    type="button" 
    onclick="DUPX.addSearchReplace();return false;" 
    style="font-size:12px;display: block; margin: 10px 0 0 0;" 
    class="default-btn"
>
    Add More
</button>

<div class="hdr-sub3 margin-top-2">Database Scan Options</div>
<div  class="dupx-opts">
    <?php
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_EMPTY_SCHEDULE_STORAGE);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_SKIP_PATH_REPLACE);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_EMAIL_REPLACE);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_FULL_SEARCH);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_MULTISITE_CROSS_SEARCH);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_POSTGUID);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_MAX_SERIALIZE_CHECK);
    ?>
</div>




