<?php

/**
 * Abstract class that manages a single page in wordpress administration without an entry in the menu.
 * The basic render function doesn't handle anything and all content must be generated in the content, including the wrapper.
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Controllers;

use DUP_PRO_Handler;
use Duplicator\Core\CapMng;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapUtil;
use Error;
use Exception;

abstract class AbstractSinglePageController implements ControllerInterface
{
    /** @var static[] */
    private static $instances = array();
    /** @var string */
    protected $pageSlug = '';
    /** @var string */
    protected $pageTitle = '';
    /** @var string */
    protected $capatibility = '';
    /** @var mixed[] */
    protected $renderData = array();
    /** @var false|string */
    protected $menuHookSuffix = false;

    /**
     * Return controlle instance
     *
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }

        return self::$instances[$class];
    }

    /**
     * Class constructor
     */
    abstract protected function __construct();

    /**
     * Method called on wordpress hook init action
     *
     * @return void
     */
    public function hookWpInit()
    {
        // empty
    }

    /**
     *
     * @return boolean if is false the controller isn't initialized
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Return true if this controller is main page
     *
     * @return boolean
     */
    public function isMainPage()
    {
        return true;
    }

    /**
     * Return menu position
     *
     * @return int
     */
    public function getPosition()
    {
        return 0;
    }

    /**
     * Set template globa data values
     *
     * @return void
     */
    protected function setTemplateData()
    {
        $tplMng = TplMng::getInstance();
        $tplMng->setGlobalValue('pageTitle', $this->pageTitle);

        $currentMenuSlugs = $this->getCurrentMenuSlugs();
        $tplMng->setGlobalValue('currentLevelSlugs', $currentMenuSlugs);
    }

    /**
     * Execure controller actions
     *
     * @return void
     */
    protected function runActions()
    {
        $resultData = array(
            'actionsError' => false,
            'errorMessage' => '',
            'successMessage' => ''
        );
        $tplMng     = TplMng::getInstance();

        try {
            do_action('duplicator_before_run_actions_' . $this->pageSlug);
            $isActionCalled = false;
            if (($currentAction = ControllersManager::getAction()) !== false) {
                $actions = $this->getActions();
                foreach ($actions as $action) {
                    if (!$action instanceof PageAction) {
                        continue;
                    }
                    if ($action->isCurrentAction($this->getCurrentMenuSlugs(), $this->getCurrentInnerPage(), $currentAction)) {
                        $action->exec($resultData);
                        $isActionCalled = true;
                    }
                }
            }
            do_action('duplicator_after_run_actions_' . $this->pageSlug, $isActionCalled);
        } catch (\Exception $e) {
            $resultData['actionsError']  = true;
            $resultData['errorMessage'] .= '<b>' . $e->getMessage() . '</b><pre>' . SnapLog::getTextException($e, false) . '</pre>';
        } catch (\Error $e) {
            $resultData['actionsError']  = true;
            $resultData['errorMessage'] .= '<b>' . $e->getMessage() . '</b><pre>' . SnapLog::getTextException($e, false) . '</pre>';
        }

        $tplMng->updateGlobalData($resultData);
        if ($resultData['actionsError']) {
            add_filter('admin_body_class', function ($classes) {
                return $classes . ' dup-actions-error';
            });
        }
    }

    /**
     * Set controller action
     *
     * @return void
     */
    protected function setActionsAvaiables()
    {
        $actionsAvaiables = array();
        $actions          = $this->getActions();
        foreach ($actions as $action) {
            if (!$action instanceof PageAction) {
                continue;
            }

            if ($action->isPageOfCurrentAction($this->getCurrentMenuSlugs())) {
                $actionsAvaiables[$action->getKey()] = $action;
            }
        }
        TplMng::getInstance()->updateGlobalData(array('actions' => $actionsAvaiables));
    }

    /**
     * Capability check
     *
     * @return void
     */
    protected function capabilityCheck()
    {
        if (!CapMng::can($this->capatibility, false)) {
            self::notPermsDie();
        }
    }

    /**
     * Excecute controller logic
     *
     * @return void
     */
    public function run()
    {
        if (
            !$this->isEnabled() ||
            SnapUtil::filterInputDefaultSanitizeString(SnapUtil::INPUT_REQUEST, 'page') !== $this->pageSlug
        ) {
            return;
        }

        $invalidOutput = '';
        ob_start();
        DUP_PRO_Handler::init_error_handler();
        $this->setTemplateData();
        $this->capabilityCheck();
        $tplMng  = TplMng::getInstance();
        $tplData = apply_filters('duplicator_page_template_data_' . $this->pageSlug, $tplMng->getGlobalData());
        $tplMng->updateGlobalData($tplData);
        $this->setActionsAvaiables();
        $this->runActions();

        $invalidOutput = SnapUtil::obCleanAll();
        ob_end_clean();
        if (strlen($invalidOutput)) {
            $tplMng->setGlobalValue('invalidOutput', trim($invalidOutput));
        }
    }

    /**
     * Render page
     *
     * @return void
     */
    public function render()
    {
        try {
            do_action('duplicator_before_render_page_' . $this->pageSlug, $this->getCurrentMenuSlugs());
            TplMng::setStripSpaces(true);
            $tplMng = TplMng::getInstance();
            $tplMng->render('parts/messages');
            do_action('duplicator_render_page_content_' . $this->pageSlug, $this->getCurrentMenuSlugs());

            do_action('duplicator_after_render_page_' . $this->pageSlug, $this->getCurrentMenuSlugs());
        } catch (Exception $e) {
            echo '<pre>' . SnapLog::getTextException($e) . '</pre>';
        } catch (Error $e) {
            echo '<pre>' . SnapLog::getTextException($e) . '</pre>';
        }
    }

    /**
     * return avaiables action
     *
     * @return PageAction[]
     */
    public function getActions()
    {
        return apply_filters('duplicator_page_actions_' . $this->pageSlug, array());
    }

    /**
     * Get action by key
     *
     * @param string $key Action key
     *
     * @return PageAction|false return false if not found
     */
    public function getActionByKey($key)
    {
        foreach ($this->getActions() as $action) {
            if ($action->getKey() == $key) {
                return $action;
            }
        }
        return false;
    }

    /**
     * Return page slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->pageSlug;
    }

    /**
     * Return current main page link
     *
     * @return string
     */
    public function getPageUrl()
    {
        return ControllersManager::getInstance()->getMenuLink($this->pageSlug);
    }

    /**
     * return menu page hook suffix
     *
     * @return string
     */
    public function getMenuHookSuffix()
    {
        return $this->menuHookSuffix;
    }

    /**
     * Register admin page
     *
     * @return false|string
     */
    public function registerMenu()
    {
        if (!$this->isEnabled() || !CapMng::can($this->capatibility, false)) {
            return false;
        }

        $pageTitle = apply_filters('duplicator_page_title_' . $this->pageSlug, $this->pageTitle);
        add_action('admin_init', array($this, 'run'));

        $this->menuHookSuffix = add_submenu_page('', $pageTitle, '', $this->capatibility, $this->pageSlug, array($this, 'render'));
        add_action('admin_print_styles-' . $this->menuHookSuffix, array($this, 'pageStyles'), 20);
        add_action('admin_print_scripts-' . $this->menuHookSuffix, array($this, 'pageScripts'), 20);
        return $this->menuHookSuffix;
    }

    /**
     * called on admin_print_styles-[page] hook
     *
     * @return void
     */
    public function pageStyles()
    {
    }

    /**
     * called on admin_print_scripts-[page] hook
     *
     * @return void
     */
    public function pageScripts()
    {
    }

    /**
     * return true if current page is this page
     *
     * @return bool
     */
    public function isCurrentPage()
    {
        $levels = ControllersManager::getMenuLevels();
        return (isset($levels[ControllersManager::QUERY_STRING_MENU_KEY_L1]) &&
            $levels[ControllersManager::QUERY_STRING_MENU_KEY_L1] === $this->pageSlug);
    }

    /**
     * return current slugs.
     *
     * @return string[]
     */
    protected function getCurrentMenuSlugs()
    {
        $levels = ControllersManager::getMenuLevels();

        $result    = array();
        $result[0] = $levels[ControllersManager::QUERY_STRING_MENU_KEY_L1];

        return $result;
    }

    /**
     * return current inner page, mpty string if is not set
     *
     * @return string
     */
    protected function getCurrentInnerPage()
    {
        return SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, ControllersManager::QUERY_STRING_INNER_PAGE, false, '-_');
    }

    /**
     * Die script with not access message
     *
     * @return void
     */
    protected static function notPermsDie()
    {
        wp_die(__('You do not have sufficient permissions to access this page.', 'duplicator-pro'));
    }
}
