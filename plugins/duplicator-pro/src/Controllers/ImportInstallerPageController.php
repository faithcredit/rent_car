<?php

/**
 * Impost installer page controller
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use DUP_PRO_Package_Importer;
use DUP_PRO_U;
use Duplicator\Core\Bootstrap;
use Duplicator\Core\CapMng;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractSinglePageController;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;

class ImportInstallerPageController extends AbstractSinglePageController
{
    /** @var DUP_PRO_Package_Importer */
    protected static $importObj = null;
    /** @var string */
    protected static $iframeSrc = null;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->pageSlug     = ControllersManager::IMPORT_INSTALLER_PAGE;
        $this->pageTitle    = __('Install package', 'duplicator-pro');
        $this->capatibility = CapMng::CAP_IMPORT;

        add_action('duplicator_before_run_actions_' . $this->pageSlug, array($this, 'packageCheck'));
        add_action('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
    }

    /**
     * Return true if current page is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return !((bool) DUPLICATOR_PRO_DISALLOW_IMPORT); // @phpstan-ignore-line
    }

    /**
     * called on admin_print_styles-[page] hook
     *
     * @return void
     */
    public function pageStyles()
    {
        Bootstrap::enqueueStyles();
        wp_enqueue_style('dup-pro-import');
    }

    /**
     * called on admin_print_scripts-[page] hook
     *
     * @return void
     */
    public function pageScripts()
    {
        self::dequeueAllScripts();
        Bootstrap::enqueueScripts();
        wp_enqueue_script('dup-pro-import-installer');
    }

    /**
     * dequeue all scripts except jquery and dup-pro script
     *
     * @return boolean // false if scripts can't be dequeued
     */
    public static function dequeueAllScripts()
    {

        if (!function_exists('wp_scripts')) {
            return false;
        }

        $scripts = wp_scripts();
        foreach ($scripts->registered as $handle => $script) {
            if (
                strpos($handle, 'jquery') === 0 ||
                strpos($handle, 'dup-pro') === 0
            ) {
                continue;
            }
            wp_dequeue_script($handle);
        }

        return true;
    }

    /**
     * Load import object and make a redirect if is a lite package
     *
     * @param array<string, string> $currentLevelSlugs current menu page
     *
     * @return void
     */
    public function packageCheck($currentLevelSlugs)
    {
        $archivePath     = SnapUtil::filterInputDefaultSanitizeString(INPUT_GET, 'package');
        self::$importObj = new DUP_PRO_Package_Importer($archivePath);
        self::$iframeSrc = self::$importObj->prepareToInstall();

        /* uncomment this to enable installer on new page
        if (self::$importObj->isLite()) {
            wp_redirect(self::$iframeSrc);
            die;
        }*/
    }

    /**
     * Render page content
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     *
     * @return void
     */
    public function renderContent($currentLevelSlugs)
    {
        $tplMng = TplMng::getInstance();
        $data   = $tplMng->getGlobalData();

        if ($data['actionsError']) {
            $tplMng->render('admin_pages/import/import-installer-error');
        } else {
            $tplMng->render(
                'admin_pages/import/import-installer',
                array(
                    'importObj' => self::$importObj,
                    'iframeSrc' => self::$iframeSrc
                )
            );
        }
    }
}
