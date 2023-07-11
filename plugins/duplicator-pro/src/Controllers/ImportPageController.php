<?php

/**
 * Import menu page controller
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use DUP_PRO_Global_Entity;
use DUP_PRO_Package_Importer;
use DUP_PRO_U;
use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\CapMng;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;
use WP_Screen;

class ImportPageController extends AbstractMenuPageController
{
    const L2_TAB_UPLOAD     = 'upd';
    const L2_TAB_REMOTE_URL = 'dwn';

    const USER_META_VIEW_MODE = 'dup-pro-import-view-mode';
    const VIEW_MODE_BASIC     = 'single';
    const VIEW_MODE_ADVANCED  = 'list';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::IMPORT_SUBMENU_SLUG;
        $this->pageTitle    = __('Import', 'duplicator-pro');
        $this->menuLabel    = __('Import', 'duplicator-pro');
        $this->capatibility = CapMng::CAP_IMPORT;
        $this->menuPos      = 20;

        add_action('current_screen', array($this, 'addHelp'), 99);
        add_filter('duplicator_page_template_data_' . $this->pageSlug, array($this, 'templateData'));
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
     * Add import help
     *
     * @param WP_Screen $currentScreen wordpress screen
     *
     * @return void
     */
    public function addHelp(WP_Screen $currentScreen)
    {
        if (!$this->isCurrentPage()) {
            return;
        }

        $tplMng = TplMng::getInstance();

        $currentScreen->add_help_tab(array(
            'id'      => 'dup-pro-help-tab-import-default',
            'title'   => DUP_PRO_U::esc_html__('Quick Start'),
            'content' => $tplMng->render('admin_pages/import/import-help-default', array(), false)
        ));
        $currentScreen->add_help_tab(array(
            'id'      => 'dup-pro-help-tab-import-details',
            'title'   => DUP_PRO_U::esc_html__('Details'),
            'content' => $tplMng->render('admin_pages/import/import-help-details', array(), false)
        ));
        $currentScreen->set_help_sidebar($tplMng->render('admin_pages/import/import-help-sidebar', array(), false));
    }

    /**
     * Import view mode
     *
     * @return string
     */
    public static function getViewMode()
    {
        if (!($userId = get_current_user_id())) {
            throw new Exception(__('User not logged in', 'duplicator-pro'));
        }

        if (!($viewMode = get_user_meta($userId, self::USER_META_VIEW_MODE, true))) {
            $viewMode = self::VIEW_MODE_BASIC;
        }
        return $viewMode;
    }

    /**
     * Return import page link
     *
     * @return string
     */
    public static function getImportPageLink()
    {
        if (is_multisite()) {
            $url = network_admin_url('admin.php');
        } else {
            $url = admin_url('admin.php');
        }
        $queryStr = http_build_query(array(
            'page' => 'duplicator-pro-import'
        ));
        return $url . '?' . $queryStr;
    }

    /**
     * Return chunk size
     *
     * @return int chunk size in k
     */
    public static function getChunkSize()
    {
        static $chunkSize = null;
        if (is_null($chunkSize)) {
            $postMaxSize       = SnapUtil::convertToBytes(ini_get('post_max_size'));
            $uploadMaxFilesize = SnapUtil::convertToBytes(ini_get('upload_max_filesize'));
            $chunkSettings     = SnapUtil::convertToBytes(DUP_PRO_Global_Entity::getInstance()->import_chunk_size . 'k');

            $chunkSize = floor(min(
                empty($postMaxSize) ? PHP_INT_MAX : max(0, $postMaxSize - (1 * MB_IN_BYTES)),
                empty($uploadMaxFilesize) ? PHP_INT_MAX : $uploadMaxFilesize,
                $chunkSettings
            ) / 1024);
        }
        return $chunkSize;
    }

    /**
     * Return chunk sizes list with labels
     *
     * @return string[]
     */
    public static function getChunkSizes()
    {
        return array(
            128   => DUP_PRO_U::__('100k [Slowest]'),
            256   => '200k',
            512   => '500k',
            1024  => '1M',
            2048  => '2M',
            5120  => '5M',
            10240 => DUP_PRO_U::__('10M [Very Fast]'),
            0     => DUP_PRO_U::__('Disabled [Fastest, BUT php.ini limits archive size]'),
        );
    }

    /**
     * Add template data
     *
     * @param array<string, mixed> $data template glabal data
     *
     * @return array<string, mixed>
     */
    public function templateData($data)
    {
        $viewMode = self::getViewMode();
        $archives = DUP_PRO_Package_Importer::getArchiveList();
        if ($viewMode == self::VIEW_MODE_BASIC && count($archives) > 1) {
            $viewMode = self::VIEW_MODE_ADVANCED;
            update_user_meta(get_current_user_id(), self::USER_META_VIEW_MODE, $viewMode);

            $adminMessageViewModeSwtich = true;
        } else {
            $adminMessageViewModeSwtich = false;
        }

        $data['viewMode']                   = $viewMode;
        $data['adminMessageViewModeSwtich'] = $adminMessageViewModeSwtich;

        $slugs = $this->getCurrentMenuSlugs(false);
        if (isset($slugs[1]) && $slugs[1] == self::L2_TAB_REMOTE_URL) {
            $data['defSubtab'] = self::L2_TAB_REMOTE_URL;
        } else {
            $data['defSubtab'] = self::L2_TAB_UPLOAD;
        }

        return $data;
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
        TplMng::getInstance()->render(
            'admin_pages/import/import',
            [
                'blur' => !License::can(License::CAPABILITY_PRO_BASE)
            ]
        );
    }
}
