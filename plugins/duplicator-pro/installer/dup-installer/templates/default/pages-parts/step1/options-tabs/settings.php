<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div class="hdr-sub3">Site Details</div>
<?php
$paramsManager->getHtmlFormParam(PrmMng::PARAM_BLOGNAME);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_URL_NEW);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_NEW);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_ARCHIVE_ACTION);

