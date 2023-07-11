<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use DUP_PRO_Global_Entity;
use DUP_PRO_Package;
use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\CapMng;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;

/**
 * Packages page controller
 */
class PackagesPageController extends AbstractMenuPageController
{
    const L2_SLUG_PACKAGE_BUILD  = 'packages';
    const L2_SLUG_PACKAGE_DETAIL = 'detail';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::PACKAGES_SUBMENU_SLUG;
        $this->pageTitle    = __('Packages', 'duplicator-pro');
        $this->menuLabel    = __('Packages', 'duplicator-pro');
        $this->capatibility = CapMng::CAP_BASIC;
        $this->menuPos      = 10;

        add_action('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
        add_filter('duplicator_page_template_data_' . $this->pageSlug, array($this, 'updatePackagePageTitle'));
        add_filter('set_screen_option_package_screen_options', array('DUP_PRO_Package_Screen', 'set_screen_options'), 11, 3);
    }

    /**
     * Set package page title
     *
     * @param array<string, mixed> $tplData template global data
     *
     * @return array<string, mixed>
     */
    public function updatePackagePageTitle($tplData)
    {
        switch (SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, 'action', 'main')) {
            case 'detail':
                $title = $this->getPackageDetailTitle();
                break;
            default:
                $title = $this->getPackageListTitle();
                break;
        }
        $tplData['pageTitle'] = $title;
        return $tplData;
    }

    /**
     * Capability check
     *
     * @return void
     */
    protected function capabilityCheck()
    {
        parent::capabilityCheck();

        $capOk = true;
        switch (SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, 'action', 'main')) {
            case 'detail':
                switch (SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, 'tab', 'detail')) {
                    case 'transfer':
                        $capOk = CapMng::can(CapMng::CAP_CREATE, false);
                        break;
                    case 'detail':
                    default:
                        break;
                }
                break;
            case 'main':
                switch ($this->getCurrentInnerPage()) {
                    case 'new1':
                        $capOk = (CapMng::can(CapMng::CAP_CREATE, false) && wp_verify_nonce($_GET['_wpnonce'], 'new1-package'));
                        break;
                    case 'new2':
                        $capOk = (CapMng::can(CapMng::CAP_CREATE, false) && wp_verify_nonce($_GET['_wpnonce'], 'new2-package'));
                        break;
                }
                break;
        }

        if (!$capOk) {
            self::notPermsDie();
        }
    }

    /**
     * Return create package link
     *
     * @return string
     */
    public function getPackageBuildUrl()
    {
        return $this->getMenuLink(
            self::L2_SLUG_PACKAGE_BUILD,
            null,
            array(
                ControllersManager::QUERY_STRING_INNER_PAGE => 'new1',
                '_wpnonce' => wp_create_nonce('new1-package')
            )
        );
    }

    /**
     * called on admin_print_styles-[page] hook
     *
     * @return void
     */
    public function pageStyles()
    {
        wp_enqueue_style('dup-pro-packages');
    }

    /**
     * Show gift
     *
     * @return bool
     */
    public static function showGift()
    {
        $global = DUP_PRO_Global_Entity::getInstance();
        return (DUPLICATOR_PRO_GIFT_THIS_RELEASE && $global->dupHidePackagesGiftFeatures); // @phpstan-ignore-line
    }

    /**
     * Get package detail title page
     *
     * @return string
     */
    protected function getPackageDetailTitle()
    {
        $package_id = isset($_REQUEST["id"]) ? sanitize_text_field($_REQUEST["id"]) : 0;
        $package    = \DUP_PRO_Package::get_by_id($package_id);
        if (!is_object($package)) {
            return __('Package Details » Not Found');
        } else {
            return sprintf(__('Package Details » %1$s', 'duplicator-pro'), $package->Name);
        }
    }

    /**
     * Get package list title page
     *
     * @return string
     */
    protected function getPackageListTitle()
    {
        $postfix = '';
        switch ($this->getCurrentInnerPage()) {
            case 'new1':
                $postfix = __('New', 'duplicator-pro');
                break;
            case 'new2':
                $postfix = __('New', 'duplicator-pro');
                break;
            case 'list':
            default:
                $postfix = __('All', 'duplicator-pro');
                break;
        }
        return __('Packages', 'duplicator-pro') . " » " . $postfix;
    }

    /**
     * Render page content
     *
     * @param array<string, string> $currentLevelSlugs current menu slugs
     *
     * @return void
     */
    public function renderContent($currentLevelSlugs)
    {
        switch (SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, 'action', 'main')) {
            case 'detail':
                $packageId = SnapUtil::sanitizeIntInput(SnapUtil::INPUT_REQUEST, 'id', 0);

                if ($packageId == 0 || ($package = DUP_PRO_Package::get_by_id($packageId)) == false) {
                    TplMng::getInstance()->setGlobalValue('packageId', $packageId);
                    TplMng::getInstance()->render('admin_pages/packages/details/no_package_found');
                } else {
                    $current_tab = SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, 'tab', 'detail');
                    TplMng::getInstance()->setGlobalValue('package', $package);
                    TplMng::getInstance()->setGlobalValue('current_tab', $current_tab);
                    TplMng::getInstance()->render('admin_pages/packages/details/details_header');

                    switch ($current_tab) {
                        case 'transfer':
                            TplMng::getInstance()->render(
                                'admin_pages/packages/details/transfer',
                                array(
                                    'package' => $package,
                                    'blur' => !License::can(License::CAPABILITY_PRO_BASE)
                                )
                            );
                            break;
                        case 'detail':
                        default:
                            TplMng::getInstance()->render(
                                'admin_pages/packages/details/detail',
                                array(
                                    'package' => $package,
                                    'blur' => !License::can(License::CAPABILITY_PRO_BASE)
                                )
                            );
                            break;
                    }
                }
                break;
            case 'main':
            default:
                TplMng::getInstance()->setGlobalValue('blurCreate', !License::can(License::CAPABILITY_PRO_BASE));
                switch ($this->getCurrentInnerPage()) {
                    case 'new1':
                        include(DUPLICATOR____PATH . '/views/packages/main/s1.setup0.base.php');
                        break;
                    case 'new2':
                        include(DUPLICATOR____PATH . '/views/packages/main/s2.scan1.base.php');
                        break;
                    case 'list':
                    default:
                        include(DUPLICATOR____PATH . '/views/packages/main/packages.php');
                        break;
                }
                break;
        }
    }

    /**
     * Get package detail url
     *
     * @param int $package_id package id
     *
     * @return string
     */
    public function getPackageDetailsUrl($package_id)
    {
        return $this->getMenuLink(
            self::L2_SLUG_PACKAGE_DETAIL,
            null,
            array(
                'action' => 'detail',
                'id' => $package_id,
                '_wpnonce' => wp_create_nonce('package-detail')
            )
        );
    }
}
