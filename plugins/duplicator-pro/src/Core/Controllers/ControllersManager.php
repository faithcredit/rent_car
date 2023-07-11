<?php

/**
 * Singlethon class that manages the various controllers of the administration of wordpress
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Controllers;

use Duplicator\Controllers\MainPageController;
use Duplicator\Controllers\PackagesPageController;
use Duplicator\Controllers\ImportPageController;
use Duplicator\Controllers\ImportInstallerPageController;
use Duplicator\Controllers\SettingsPageController;
use Duplicator\Controllers\StoragePageController;
use Duplicator\Controllers\DebugPageController;
use Duplicator\Controllers\ToolsPageController;
use Duplicator\Libs\Snap\SnapUtil;

/**
 * ControllersManager
 */
final class ControllersManager
{
    const MAIN_MENU_SLUG               = 'duplicator-pro';
    const PACKAGES_SUBMENU_SLUG        = 'duplicator-pro';
    const IMPORT_SUBMENU_SLUG          = 'duplicator-pro-import';
    const SCHEDULES_SUBMENU_SLUG       = 'duplicator-pro-schedules';
    const STORAGE_SUBMENU_SLUG         = 'duplicator-pro-storage';
    const TEMPLATES_SUBMENU_SLUG       = 'duplicator-pro-templates';
    const TOOLS_SUBMENU_SLUG           = 'duplicator-pro-tools';
    const SETTINGS_SUBMENU_SLUG        = 'duplicator-pro-settings';
    const DEBUG_SUBMENU_SLUG           = 'duplicator-pro-debug';
    const IMPORT_INSTALLER_PAGE        = 'duplicator-pro-import-installer';
    const QUERY_STRING_MENU_KEY_L1     = 'page';
    const QUERY_STRING_MENU_KEY_L2     = 'tab';
    const QUERY_STRING_MENU_KEY_L3     = 'subtab';
    const QUERY_STRING_MENU_KEY_ACTION = 'action';
    const QUERY_STRING_INNER_PAGE      = 'inner_page';

    /** @var ?self */
    private static $instance = null;

    /**
     * Return controlle manager instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Class constructor
     */
    protected function __construct()
    {
        add_action('init', array($this, 'hookWpInit'));
    }

    /**
     * Method called on wordpress hook init action
     *
     * @return void
     */
    public function hookWpInit()
    {
        foreach ($this->getMenuPages() as $menuPage) {
            if (!$menuPage->isEnabled()) {
                continue;
            }

            $menuPage->hookWpInit();
        }
    }

    /**
     * Return true if current page is a duplicator page
     *
     * @return boolean
     */
    public function isDuplicatorPage()
    {
        foreach ($this->getMenuPages() as $menuPage) {
            if (!$menuPage->isEnabled()) {
                continue;
            }

            if ($menuPage->isCurrentPage()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return current menu levels
     *
     * @return (null|string)[]
     */
    public static function getMenuLevels()
    {
        $result = SnapUtil::filterInputRequestArray(
            array(
                self::QUERY_STRING_MENU_KEY_L1 => array(
                    'filter'  => FILTER_UNSAFE_RAW,
                    'options' => array(
                        'default' => null
                    )
                ),
                self::QUERY_STRING_MENU_KEY_L2 => array(
                    'filter'  => FILTER_UNSAFE_RAW,
                    'options' => array(
                        'default' => null
                    )
                ),
                self::QUERY_STRING_MENU_KEY_L3 => array(
                    'filter'  => FILTER_UNSAFE_RAW,
                    'options' => array(
                        'default' => null
                    )
                )
            )
        );
        foreach ($result as $key => $val) {
            if (is_null($val)) {
                continue;
            }
            $result[$key] = SnapUtil::sanitizeNSCharsNewlineTabs($val);
        }
        return $result;
    }

    /**
     * Return current action key or false if not exists
     *
     * @return string|bool
     */
    public static function getAction()
    {
        $result = SnapUtil::filterInputRequest(
            self::QUERY_STRING_MENU_KEY_ACTION,
            FILTER_UNSAFE_RAW,
            array(
                    'options' => array(
                        'default' => false
                    )
                )
        );
        return ($result === false ? $result : SnapUtil::sanitizeNSCharsNewlineTabs($result));
    }

    /**
     * Check current page
     *
     * @param string      $page  page key
     * @param null|string $tabL1 tab level 1 key, null not check
     * @param null|string $tabL2 tab level 12key, null not check
     *
     * @return boolean
     */
    public static function isCurrentPage($page, $tabL1 = null, $tabL2 = null)
    {
        $levels = self::getMenuLevels();

        if ($page !== $levels[self::QUERY_STRING_MENU_KEY_L1]) {
            return false;
        }

        $controller = self::getPageControlleBySlug($page);
        // get defaults
        $menuSlugs = $controller->getCurrentMenuSlugs();

        if (!is_null($tabL1) && (!isset($menuSlugs[1]) || $tabL1 !== $menuSlugs[1])) {
            return false;
        }

        if (!is_null($tabL1) && !is_null($tabL2) && (!isset($menuSlugs[2]) || $tabL2 !== $menuSlugs[2])) {
            return false;
        }

        return true;
    }

    /**
     * Return unique id by levels page/tabs
     *
     * @param string $page  page slug
     * @param string $tabL1 tab level 1 slug, null not set
     * @param string $tabL2 tab level 2 slug, null not set
     *
     * @return string
     */
    public static function getPageUniqueId($page, $tabL1 = null, $tabL2 = null)
    {
        $result = 'dup_id_' . $page;

        if (!is_null($tabL1)) {
            $result .= '_' . $tabL1;
        }

        if (!is_null($tabL1) && !is_null($tabL2)) {
            $result .= '_' . $tabL2;
        }

        return $result;
    }

    /**
     * Return unique id of current id
     *
     * @return string
     */
    public static function getUniqueIdOfCurrentPage()
    {
        $levels = self::getMenuLevels();
        return self::getPageUniqueId($levels[self::QUERY_STRING_MENU_KEY_L1], $levels[self::QUERY_STRING_MENU_KEY_L2], $levels[self::QUERY_STRING_MENU_KEY_L3]);
    }

    /**
     * Return current menu page URL with inner page if is set
     *
     * @param array<string, string> $extraData extra value in query string key=val
     *
     * @return string
     */
    public static function getCurrentLink($extraData = array())
    {
        $levels = self::getMenuLevels();
        $inner  = SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, ControllersManager::QUERY_STRING_INNER_PAGE, false, '-_');
        if ($inner !== false) {
            $extraData[ControllersManager::QUERY_STRING_INNER_PAGE] = $inner;
        }
        return self::getMenuLink(
            $levels[self::QUERY_STRING_MENU_KEY_L1],
            $levels[self::QUERY_STRING_MENU_KEY_L2],
            $levels[self::QUERY_STRING_MENU_KEY_L3],
            $extraData
        );
    }

    /**
     * Return menu page URL
     *
     * @param string               $page      page slug
     * @param string               $subL2     tab level 1 slug, null not set
     * @param string               $subL3     tab level 2 slug, null not set
     * @param array<string, mixed> $extraData extra value in query string key=val
     * @param bool                 $relative  if true return relative path or absolute
     *
     * @return string
     */
    public static function getMenuLink($page, $subL2 = null, $subL3 = null, $extraData = array(), $relative = true)
    {
        $data = (array) $extraData;

        $data[self::QUERY_STRING_MENU_KEY_L1] = $page;

        if (!empty($subL2)) {
            $data[self::QUERY_STRING_MENU_KEY_L2] = $subL2;
        }

        if (!empty($subL3)) {
            $data[self::QUERY_STRING_MENU_KEY_L3] = $subL3;
        }

        if ($relative) {
            $url = self_admin_url('admin.php', 'relative');
        } else {
            if (is_multisite()) {
                $url = network_admin_url('admin.php');
            } else {
                $url = admin_url('admin.php');
            }
        }
        return $url . '?' . http_build_query($data);
    }

    /**
     * Return menu pages list
     *
     * @return AbstractMenuPageController[]
     */
    public static function getMenuPages()
    {
        static $basicMenuPages = null;

        if (is_null($basicMenuPages)) {
            $basicMenuPages   = array();
            $basicMenuPages[] = MainPageController::getInstance();
            $basicMenuPages[] = PackagesPageController::getInstance();
            $basicMenuPages[] = ImportPageController::getInstance();
            $basicMenuPages[] = ImportInstallerPageController::getInstance();
            $basicMenuPages[] = StoragePageController::getInstance();
            $basicMenuPages[] = SettingsPageController::getInstance();
            $basicMenuPages[] = DebugPageController::getInstance();
            $basicMenuPages[] = ToolsPageController::getInstance();
        }

        return array_filter(
            apply_filters(
                'duplicator_menu_pages',
                $basicMenuPages
            ),
            function ($menuPage) {
                return is_subclass_of($menuPage, '\Duplicator\Core\Controllers\AbstractSinglePageController');
            }
        );
    }

    /**
     * Return menu pages list sorted by position
     *
     * @return AbstractMenuPageController[]
     */
    protected static function getMenuPagesSortedByPos()
    {
        $menuPages = self::getMenuPages();

        uksort($menuPages, function ($a, $b) use ($menuPages) {
            if ($menuPages[$a]->getPosition() == $menuPages[$b]->getPosition()) {
                if ($a == $b) {
                    return 0;
                } elseif ($a > $b) {
                    return 1;
                } else {
                    return -1;
                }
            } elseif ($menuPages[$a]->getPosition() > $menuPages[$b]->getPosition()) {
                return 1;
            } else {
                return -1;
            }
        });
        return array_values($menuPages);
    }

    /**
     * Return page controlle by slug of false if don't exist
     *
     * @param string $slug page key
     *
     * @return boolean|AbstractMenuPageController
     */
    public static function getPageControlleBySlug($slug)
    {
        $menuPages = self::getMenuPages();
        foreach ($menuPages as $page) {
            if ($page->getSlug() === $slug) {
                return $page;
            }
        }

        return false;
    }

    /**
     * Register menu pages
     *
     * @return void
     */
    public function registerMenu()
    {
        $menuPages = self::getMenuPagesSortedByPos();

        // before register main pages
        foreach ($menuPages as $menuPage) {
            if (!$menuPage->isEnabled() || !$menuPage->isMainPage()) {
                continue;
            }

            $menuPage->registerMenu();
        }

        // after register secondary pages
        foreach ($menuPages as $menuPage) {
            if (!$menuPage->isEnabled() || $menuPage->isMainPage()) {
                continue;
            }

            $menuPage->registerMenu();
        }
    }
}
