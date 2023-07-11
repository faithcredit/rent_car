<?php

/**
 * @package   Duplicator/Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Deploy\Plugins;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use DUPX_ArchiveConfig;
use DUPX_DB;
use DUPX_DB_Functions;
use DUPX_InstallerState;
use DUPX_NOTICE_ITEM;
use DUPX_NOTICE_MANAGER;
use DUPX_U;
use Exception;

/**
 * Original installer files manager
 * singleton class
 */
final class PluginsManager
{
    const SLUG_WOO_ADMIN             = 'woocommerce-admin/woocommerce-admin.php';
    const SLUG_SIMPLE_SSL            = 'really-simple-ssl/rlrsssl-really-simple-ssl.php';
    const SLUG_ONE_CLICK_SSL         = 'one-click-ssl/ssl.php';
    const SLUG_WP_FORCE_SSL          = 'wp-force-ssl/wp-force-ssl.php';
    const SLUG_RECAPTCHA             = 'simple-google-recaptcha/simple-google-recaptcha.php';
    const SLUG_WPBAKERY_PAGE_BUILDER = 'js_composer/js_composer.php';
    const SLUG_DUPLICATOR_PRO        = 'duplicator-pro/duplicator-pro.php';
    const SLUG_DUPLICATOR_LITE       = 'duplicator/duplicator.php';
    const SLUG_DUPLICATOR_TESTER     = 'duplicator-tester-plugin/duplicator-tester.php';
    const SLUG_WPS_HIDE_LOGIN        = 'wps-hide-login/wps-hide-login.php';
    const SLUG_POPUP_MAKER           = 'popup-maker/popup-maker.php';
    const SLUG_JETPACK               = 'jetpack/jetpack.php';
    const SLUG_WP_ROCKET             = 'wp-rocket/wp-rocket.php';
    const SLUG_BETTER_WP_SECURITY    = 'better-wp-security/better-wp-security.php';
    const SLUG_HTTPS_REDIRECTION     = 'https-redirection/https-redirection.php';
    const SLUG_LOGIN_NOCAPTCHA       = 'login-recaptcha/login-nocaptcha.php';
    const SLUG_GOOGLE_CAPTCHA        = 'google-captcha/google-captcha.php';
    const SLUG_ADVANCED_CAPTCHA      = 'advanced-google-recaptcha/advanced-google-recaptcha.php';
    const OPTION_ACTIVATE_PLUGINS    = 'duplicator_pro_activate_plugins_after_installation';

    /** @var ?self */
    private static $instance = null;
    /** @var PluginItem[] */
    private $plugins = array();
    /** @var PluginItem[] */
    private $unistallList = array();
    /** @var PluginCustomActions[] */
    private $customPluginsActions = array();

    /**
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
     *
     * @return void
     */
    private function __construct()
    {

        foreach (DUPX_ArchiveConfig::getInstance()->wpInfo->plugins as $pluginInfo) {
            $this->plugins[$pluginInfo->slug] = new PluginItem((array)$pluginInfo);
        }

        $this->setCustomPluginsActions();

        Log::info('CONSTRUCT PLUGINS OBJECTS: ' . Log::v2str($this->plugins), Log::LV_HARD_DEBUG);
    }

    /**
     * This method prepares customPluginActions for further processing
     *
     * @return void
     */
    private function setCustomPluginsActions()
    {
        if (DUPX_InstallerState::isAddSiteOnMultisite()) {
            $default    = PluginCustomActions::BY_DEFAULT_DISABLED;
            $afterLogin = false;
            $longMsg    = 'The plugin is disabled in the single site because it is active on the network.';
        } else {
            $default    = PluginCustomActions::BY_DEFAULT_ENABLED;
            $afterLogin = true;
            $longMsg    = '';
        }

        $this->customPluginsActions[self::SLUG_DUPLICATOR_PRO] = new PluginCustomActions(
            self::SLUG_DUPLICATOR_PRO,
            $default,
            $afterLogin,
            $longMsg
        );

        $this->customPluginsActions[self::SLUG_DUPLICATOR_TESTER] = new PluginCustomActions(
            self::SLUG_DUPLICATOR_TESTER,
            $default,
            $afterLogin,
            $longMsg
        );

        $this->customPluginsActions[self::SLUG_DUPLICATOR_LITE] = new PluginCustomActions(
            self::SLUG_DUPLICATOR_LITE,
            PluginCustomActions::BY_DEFAULT_DISABLED,
            false,
            'Duplicator LITE has been deactivated because in the new versions it is not possible to ' .
            'have Duplicator LITE active at the same time as PRO.'
        );

        $longMsg = "This plugin is deactivated by default automatically. "
            . "<strong>You must reactivate from the WordPress admin panel after completing the installation</strong> "
            . "or from the plugins tab."
            . " Your site's frontend will render properly after reactivating the plugin.";

        $this->customPluginsActions[self::SLUG_WPBAKERY_PAGE_BUILDER] = new PluginCustomActions(
            self::SLUG_WPBAKERY_PAGE_BUILDER,
            PluginCustomActions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );

        $this->customPluginsActions[self::SLUG_JETPACK] = new PluginCustomActions(
            self::SLUG_JETPACK,
            PluginCustomActions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );

        $longMsg = "This plugin is deactivated by default automatically due to issues that one may encounter when migrating. "
            . "<strong>You must reactivate from the WordPress admin panel after completing the installation</strong> "
            . "or from the plugins tab."
            . " Your site's frontend will render properly after reactivating the plugin.";

        $this->customPluginsActions[self::SLUG_POPUP_MAKER] = new PluginCustomActions(
            self::SLUG_POPUP_MAKER,
            PluginCustomActions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );

        $this->customPluginsActions[self::SLUG_WP_ROCKET] = new PluginCustomActions(
            self::SLUG_WP_ROCKET,
            PluginCustomActions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );

        $this->customPluginsActions[self::SLUG_WPS_HIDE_LOGIN] = new PluginCustomActions(
            self::SLUG_WPS_HIDE_LOGIN,
            PluginCustomActions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );

        $longMsg = "This plugin is deactivated by default automatically due to issues that one may encounter when migrating. "
            . "<strong>You must reactivate from the WordPress admin panel after completing the installation</strong> "
            . "or from the plugins tab.";

        $this->customPluginsActions[self::SLUG_WOO_ADMIN] = new PluginCustomActions(
            self::SLUG_WOO_ADMIN,
            PluginCustomActions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );

        $this->customPluginsActions[self::SLUG_BETTER_WP_SECURITY] = new PluginCustomActions(
            self::SLUG_BETTER_WP_SECURITY,
            PluginCustomActions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );
    }

    /**
     *
     * @return PluginItem[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * @return string[]
     */
    public function getDropInsPaths()
    {
        static $dropInsPaths = null;

        if (is_null($dropInsPaths)) {
            $dropInsPaths = array();
            foreach ($this->plugins as $plugin) {
                if ($plugin->isDropIns()) {
                    $dropInsPaths[] = $plugin->getPluginArchivePath();
                }
            }
            Log::info('DROP INS PATHS: ' . Log::v2str($dropInsPaths));
        }
        return $dropInsPaths;
    }

    /**
     * This function performs status checks on plugins and disables those that must disable creating user messages
     *
     * @param string $slug plugin slug
     *
     * @return bool
     */
    public function pluginExistsInArchive($slug)
    {
        return array_key_exists($slug, $this->plugins);
    }

    /**
     * This function performs status checks on plugins and disables those that must disable creating user messages
     *
     * @param int|null $subsiteId ID of a subsite

     * @return void
     */
    public function preViewChecks($subsiteId = null)
    {
        $noticeManager = DUPX_NOTICE_MANAGER::getInstance();
        $paramsManager = PrmMng::getInstance();

        if (DUPX_InstallerState::isRestoreBackup()) {
            return;
        }

        $activePlugins = $paramsManager->getValue(PrmMng::PARAM_PLUGINS);
        $saveParams    = false;

        foreach ($this->customPluginsActions as $slug => $customPlugin) {
            if (!isset($this->plugins[$slug])) {
                continue;
            }

            switch ($customPlugin->byDefaultStatus()) {
                case PluginCustomActions::BY_DEFAULT_DISABLED:
                    if (($delKey = array_search($slug, $activePlugins)) !== false) {
                        $saveParams = true;
                        unset($activePlugins[$delKey]);

                        $noticeManager->addNextStepNotice(array(
                            'shortMsg' => 'Plugin ' . $this->plugins[$slug]->name . ' disabled by default',
                            'level' => DUPX_NOTICE_ITEM::NOTICE,
                            'longMsg' => $customPlugin->byDefaultMessage(),
                            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                            'sections' => 'plugins'
                        ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'custom_plugin_action' . $slug);
                    }
                    break;
                case PluginCustomActions::BY_DEFAULT_ENABLED:
                    if (!in_array($slug, $activePlugins)) {
                        $saveParams      = true;
                        $activePlugins[] = $slug;

                        $noticeManager->addNextStepNotice(array(
                            'shortMsg' => 'Plugin ' . $this->plugins[$slug]->name . ' enabled by default',
                            'level' => DUPX_NOTICE_ITEM::NOTICE,
                            'longMsg' => $customPlugin->byDefaultMessage(),
                            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                            'sections' => 'plugins'
                        ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'custom_plugin_action' . $slug);
                    }
                    break;
                case PluginCustomActions::BY_DEFAULT_AUTO:
                    Log::info("AUTO ACTION WAS TRIGGERED");
                    $saveParams = false;
                    if (!$this->plugins[$slug]->isInactive($subsiteId) && $customPlugin->isEnableAfterLogin()) {
                        $this->plugins[$slug]->setActivationAction($subsiteId);
                    } elseif (!$customPlugin->isEnableAfterLogin()) {
                        $this->plugins[$slug]->setDeactivateAction(
                            $subsiteId,
                            'Deactivated plugin: ' . $this->plugins[$slug]->name,
                            $customPlugin->byDefaultMessage()
                        );
                    }
                    break;
                default:
                    break;
            }
        }

        if ($saveParams) {
            $paramsManager->setValue(PrmMng::PARAM_PLUGINS, $activePlugins);
            $paramsManager->save();
            $noticeManager->saveNotices();
        }
    }

    /**
     * @param integer $subsiteId ID of a subsite
     *
     * @return int[]
     */
    public function getStatusCounts($subsiteId = -1)
    {
        $result = array(
            PluginItem::STATUS_MUST_USE => 0,
            PluginItem::STATUS_DROP_INS => 0,
            PluginItem::STATUS_NETWORK_ACTIVE => 0,
            PluginItem::STATUS_ACTIVE => 0,
            PluginItem::STATUS_INACTIVE => 0
        );

        foreach ($this->plugins as $plugin) {
            $result[$plugin->getOrgiStatus($subsiteId)]++;
        }

        return $result;
    }

    /**
     * @param integer $subsiteId ID of a subsite
     *
     * @return string[]
     */
    public function getDefaultActivePluginsList($subsiteId = -1)
    {
        $result         = array();
        $networkInstall = DUPX_InstallerState::isNewSiteIsMultisite();
        foreach ($this->plugins as $plugin) {
            if ($networkInstall) {
                if ($plugin->isNetworkActive() || $plugin->isMustUse() || $plugin->isDropIns()) {
                    $result[] = $plugin->getSlug();
                }
            } else {
                if (!$plugin->isInactive($subsiteId)) {
                    $result[] = $plugin->getSlug();
                }
            }
        }
        return $result;
    }

    /**
     * return alla plugins slugs list
     *
     * @return string[]
     */
    public function getAllPluginsSlugs()
    {
        return array_keys($this->plugins);
    }

    /**
     * @param string[] $plugins   List of plugins
     * @param integer  $subsiteId ID of a subsite
     *
     * @return void
     */
    public function setActions($plugins, $subsiteId = -1)
    {
        Log::info('FUNCTION [' . __FUNCTION__ . ']: plugins ' . Log::v2str($plugins), Log::LV_DEBUG);
        $networkInstall = DUPX_InstallerState::isNewSiteIsMultisite();

        foreach ($this->plugins as $slug => $plugin) {
            $deactivate = false;

            if ($plugin->isForceDisabled()) {
                $deactivate = true;
            } else {
                if ($networkInstall) {
                    if (!$this->plugins[$slug]->isNetworkInactive() && !in_array($slug, $plugins)) {
                        $deactivate = true;
                    }
                } else {
                    if (!$this->plugins[$slug]->isInactive($subsiteId) && !in_array($slug, $plugins)) {
                        $deactivate = true;
                    }
                }
            }

            if ($deactivate) {
                $this->plugins[$slug]->setDeactivateAction($subsiteId, null, null, $networkInstall);
            }
        }

        foreach ($plugins as $slug) {
            if (isset($this->plugins[$slug])) {
                $this->plugins[$slug]->setActivationAction($subsiteId, $networkInstall);
            }
        }

        ///Start
        $paramManager  = PrmMng::getInstance();
        $casesToHandle = array(
            array(
                'slugs' => array(
                    self::SLUG_SIMPLE_SSL,
                    self::SLUG_WP_FORCE_SSL,
                    self::SLUG_HTTPS_REDIRECTION
                ),
                'longMsg' => "The plugin '%name%' has been deactivated because you are migrating from SSL (HTTPS) to Non-SSL (HTTP).<br>" .
                    "If it was not deactivated, you would not be able to login.",
                'info' => '%name% [as Non-SSL installation] will be deactivated',
                'condition' => !DUPX_U::is_ssl()
            ),
            array(
                'slugs' => array(
                    self::SLUG_RECAPTCHA,
                    self::SLUG_LOGIN_NOCAPTCHA,
                    self::SLUG_GOOGLE_CAPTCHA,
                    self::SLUG_ADVANCED_CAPTCHA
                ),
                'longMsg' => "The plugin '%name%' has been deactivated because reCaptcha requires a site key which is bound to the site's address." .
                    "Your package site's address and installed site's address don't match. " .
                    "You can reactivate it after finishing with the installation.<br>" .
                    "<strong>Please do not forget to change the reCaptcha site key after activating it.</strong>",
                'info' => '%name% [as package creation site URL and the installation site URL are different] will be deactivated',
                'condition' => $paramManager->getValue(PrmMng::PARAM_SITE_URL_OLD) != $paramManager->getValue(PrmMng::PARAM_SITE_URL),
            ),
        );

        foreach ($casesToHandle as $case) {
            foreach ($case['slugs'] as $slug) {
                if (isset($this->plugins[$slug]) && $this->plugins[$slug]->isActive($subsiteId) && $case['condition']) {
                    $info    = str_replace('%name%', $this->plugins[$slug]->name, $case['info']);
                    $longMsg = str_replace('%name%', $this->plugins[$slug]->name, $case['longMsg']);
                    Log::info($info, Log::LV_DEBUG);
                    $this->customPluginsActions[$slug] = new PluginCustomActions(
                        $slug,
                        PluginCustomActions::BY_DEFAULT_AUTO,
                        false,
                        $longMsg
                    );
                }
            }
        }

        ///end
        DUPX_NOTICE_MANAGER::getInstance()->saveNotices();
    }

    /**
     * @param \mysqli $dbh       Connection
     * @param integer $subsiteId Subsite ID
     *
     * @return bool
     */
    public function executeActions($dbh, $subsiteId = -1)
    {
        $activePluginsList          = array();
        $activateOnLoginPluginsList = array();
        $removeInactivePlugins      = PrmMng::getInstance()->getValue(PrmMng::PARAM_REMOVE_RENDUNDANT);
        $this->unistallList         = array();

        if (DUPX_InstallerState::isAddSiteOnMultisite()) {
            Log::info('SKIP PLUGIN ACTION FOR ADD SITE IN MULTISITE');
            return true;
        }

        $escapedTablePrefix = mysqli_real_escape_string(
            $dbh,
            PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX)
        );

        $noticeManager = DUPX_NOTICE_MANAGER::getInstance();

        Log::info('PLUGINS OBJECTS: ' . Log::v2str($this->plugins), Log::LV_HARD_DEBUG);

        foreach ($this->customPluginsActions as $slug => $customPlugin) {
            if (!isset($this->plugins[$slug])) {
                continue;
            }
            if (!$this->plugins[$slug]->isInactive($subsiteId) && $customPlugin->isEnableAfterLogin()) {
                $this->plugins[$slug]->setActivationAction($subsiteId);
            }
        }

        foreach ($this->plugins as $plugin) {
            $deactivated = false;
            if ($plugin->deactivateAction) {
                $plugin->deactivate();
                // can't remove deactivate after login
                $deactivated = true;
            } elseif (DUPX_InstallerState::isNewSiteIsMultisite()) {
                if ($plugin->isNetworkActive()) {
                    $activePluginsList[$plugin->getSlug()] = time();
                }
            } else {
                if ($plugin->isActive($subsiteId)) {
                    $activePluginsList[] = $plugin->getSlug();
                }
            }

            if ($plugin->activateAction) {
                $activateOnLoginPluginsList[] = $plugin->getSlug();
                $noticeManager->addFinalReportNotice(array(
                    'shortMsg' => 'Activate ' . $plugin->name . ' after you login.',
                    'level' => DUPX_NOTICE_ITEM::NOTICE,
                    'sections' => 'plugins'
                ));
            } else {
                // remove only if isn't activated
                if ($removeInactivePlugins && ($plugin->isInactive($subsiteId) || $deactivated)) {
                    $this->unistallList[] = $plugin;
                }
            }
        }

        // force duplicator pro activation
        if (!array_key_exists(self::SLUG_DUPLICATOR_PRO, $activePluginsList)) {
            if (DUPX_InstallerState::isNewSiteIsMultisite()) {
                $activePluginsList[self::SLUG_DUPLICATOR_PRO] = time();
            } else {
                $activePluginsList[] = self::SLUG_DUPLICATOR_PRO;
            }
        }

        // force duplicator tester activation if exists
        if (
            $this->pluginExistsInArchive(self::SLUG_DUPLICATOR_TESTER)
            && !array_key_exists(self::SLUG_DUPLICATOR_TESTER, $activePluginsList)
        ) {
            if (DUPX_InstallerState::isNewSiteIsMultisite()) {
                $activePluginsList[self::SLUG_DUPLICATOR_TESTER] = time();
            } else {
                $activePluginsList[] = self::SLUG_DUPLICATOR_TESTER;
            }
        }

        Log::info('Active plugins: ' . Log::v2str($activePluginsList), Log::LV_DEBUG);

        $value = mysqli_real_escape_string($dbh, @serialize($activePluginsList));
        if (DUPX_InstallerState::isNewSiteIsMultisite()) {
            $table = $escapedTablePrefix . 'sitemeta';
            $query = "UPDATE `" . $table . "` SET meta_value = '" . $value . "'  WHERE meta_key = 'active_sitewide_plugins'";
        } else {
            $optionTable = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getOptionsTableName());
            $query       = "UPDATE `" . $optionTable . "` SET option_value = '" . $value . "'  WHERE option_name = 'active_plugins' ";
        }

        if (!DUPX_DB::mysqli_query($dbh, $query)) {
            $noticeManager->addFinalReportNotice(array(
                'shortMsg' => 'QUERY ERROR: MySQL',
                'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                'longMsg' => "Error description: " . mysqli_error($dbh),
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'sections' => 'database'
            ));
            throw new Exception("Database error description: " . mysqli_error($dbh));
        }

        if (!DUPX_InstallerState::isNewSiteIsMultisite()) {
            $value       = mysqli_real_escape_string($dbh, @serialize($activateOnLoginPluginsList));
            $optionTable = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getOptionsTableName());
            $query       = "INSERT INTO `" . $optionTable . "` (option_name, option_value) 
            VALUES('" . self::OPTION_ACTIVATE_PLUGINS . "','" . $value . "') ON DUPLICATE KEY UPDATE option_name=\"" . self::OPTION_ACTIVATE_PLUGINS . "\"";
            if (!DUPX_DB::mysqli_query($dbh, $query)) {
                $noticeManager->addFinalReportNotice(array(
                    'shortMsg' => 'QUERY ERROR: MySQL',
                    'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                    'longMsg' => "Error description: " . mysqli_error($dbh),
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                    'sections' => 'database'
                ));
                throw new Exception("Database error description: " . mysqli_error($dbh));
            }
        }

        return true;
    }

    /**
     * remove inactive plugins
     * this method must calle after wp-config set
     *
     * @return void
     */
    public function uninstallInactivePlugins()
    {
        Log::info('FUNCTION [' . __FUNCTION__ . ']: uninstall inactive plugins');

        /** @var PluginItem $plugin */
        foreach ($this->unistallList as $plugin) {
            if ($plugin->uninstall()) {
                Log::info("UNINSTALL PLUGIN " . Log::v2str($plugin->getSlug()) . ' DONE');
            } else {
                Log::info("UNINSTALL PLUGIN " . Log::v2str($plugin->getSlug()) . ' FAILED');
            }
        }
    }
}
