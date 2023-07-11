<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div class="hdr-sub3">Secondary URLs and paths</div>
<div id="other-path-url-options">
    <small><i>The recommended setting for these values is "Auto".<br>
            The "Auto" setting derives its values from the "New Site URL" and "New Path" inputs found on the settings tab.<br>  
            Please use caution if manually updating these values and be sure the paths are correct.</i></small>
    <?php
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_WP_CORE_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_SITE_URL);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_CONTENT_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_URL_CONTENT_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_UPLOADS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_URL_UPLOADS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_PLUGINS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_URL_PLUGINS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_MUPLUGINS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_URL_MUPLUGINS_NEW);
    ?>
</div>
