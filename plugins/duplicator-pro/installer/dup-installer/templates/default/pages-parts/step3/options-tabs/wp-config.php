<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>

<div class="hdr-sub3">SETTINGS: For wp-config.php file</div>
<div  class="dupx-opts margin-top">
    <?php
    if (DUPX_InstallerState::isRestoreBackup()) {
        dupxTplRender('parts/restore-backup-mode-notice');
    } else {
        ?>
        <p>
            See the <a href="https://wordpress.org/support/article/editing-wp-config-php/" target="_blank">WordPress documentation</a>
            <i class="fas fa-external-link-square-alt"></i> for more information and specifications.
        </p>
        <div class="hdr-sub3 margin-top-2">CONTENT <small class="silver">Posts/Pages</small></div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_DISALLOW_FILE_EDIT);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_DISALLOW_FILE_MODS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_AUTOSAVE_INTERVAL);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_POST_REVISIONS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_EMPTY_TRASH_DAYS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_IMAGE_EDIT_OVERWRITE);
        ?>
        <div class="hdr-sub3 margin-top-2">SECURITY</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_FORCE_SSL_ADMIN);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_AUTOMATIC_UPDATER_DISABLED);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_GEN_WP_AUTH_KEY);
        ?>
        <div class="hdr-sub3 margin-top-2">CRON</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_ALTERNATE_WP_CRON);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_DISABLE_WP_CRON);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT);
        ?>
        <div class="hdr-sub3 margin-top-2">DEBUG</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DEBUG);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DEBUG_LOG);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DEBUG_DISPLAY);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_SCRIPT_DEBUG);
        ?>
        <div class="hdr-sub3 margin-top-2">SYSTEM</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_CACHE);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WPCACHEHOME);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_MEMORY_LIMIT);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT);
        ?>
        <div class="hdr-sub3 margin-top-2">GENERAL</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_MYSQL_CLIENT_FLAGS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_CONCATENATE_SCRIPTS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_SAVEQUERIES);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_COOKIE_DOMAIN);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_TEMP_DIR);
    }
    ?>
</div>
