<?php
/**
 * Bootstrap utility to exatract the core installer
 *
 * @package Duplicator\Installer
 *
 * Custom params
 *
 * [zipmode] to force extraction zip mode
 *      installer.php?zipmode=auto
 *      installer.php?zipmode=ziparchive
 *      installer.php?zipmode=shellexec
 *
 * [force-extract-installer] to force dup-installer folder overwrite
 *      installer.php?force-extract-installer=(1|on|yes)
 *
 * [dup_folder] to change dup-installer folder name
 *      installer.php?dup_folder=[custom_folder_name]
 *
 * [archive] to set custom archvie path location
 * can be fullpath with archive name or not
 *      installer.php?archive=[archive path]
 */

#@@DUP_INSTALLER_CLASSES_EXPANDER@@#

namespace {

    use Duplicator\Installer\Bootstrap\BootstrapRunner;
    use Duplicator\Installer\Bootstrap\BootstrapUtils;
    use Duplicator\Installer\Bootstrap\BootstrapView;
    use Duplicator\Installer\Bootstrap\LogHandler;

    class InstallerBootstrapData {
        const ARCHIVE_FILENAME       = '@@ARCHIVE@@';
        const ARCHIVE_SIZE           = '@@ARCHIVE_SIZE@@';
        const INSTALLER_DIR_NAME     = 'dup-installer';
        const PACKAGE_HASH           = '@@PACKAGE_HASH@@';
        const SECONDARY_PACKAGE_HASH = '@@SECONDARY_PACKAGE_HASH@@';
        const VERSION                = '@@VERSION@@';
    }

    BootstrapUtils::phpVersionCheck(BootstrapRunner::MINIMUM_PHP_VERSION);
    BootstrapRunner::initSetValues();

    $bootError = null;
    $view = '';

    try {
        $boot = BootstrapRunner::getInstance();
        LogHandler::initErrorHandler(array($boot, 'log'));
        $bootView = new BootstrapView();
        $view = $boot->run();
    } catch (Exception $e) {
        $boot->log("[ERROR] Boot msg:" . $e->getMessage() . "\n" . $e->getTraceAsString());
        $boot->appendErrorMessage($e->getMessage());
        $view  = BootstrapView::VIEW_ERROR;
    }

    switch ($view) {
        case BootstrapView::VIEW_REDIRECT:
            $bootView->redirectToInsaller();
            break;
        case BootstrapView::VIEW_ERROR:
            $bootView->renderError();
            break;
        case BootstrapView::VIEW_PASSWORD:
            $bootView->renderPassword();
            break;
    }
}