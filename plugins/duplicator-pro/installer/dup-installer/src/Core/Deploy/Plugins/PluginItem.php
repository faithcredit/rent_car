<?php

/**
 * @package   Duplicator/Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Deploy\Plugins;

use Duplicator\Installer\Core\Deploy\Helpers;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\InstallerOrigFileMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapUtil;
use DUPX_ArchiveConfig;
use DUPX_NOTICE_ITEM;
use DUPX_NOTICE_MANAGER;
use Error;
use Exception;

use const WP_PLUGIN_DIR;

/**
 * Pplugin item descriptor
 */
class PluginItem
{
    const STATUS_ACTIVE         = 'active';
    const STATUS_INACTIVE       = 'inactive';
    const STATUS_NETWORK_ACTIVE = 'network-active';
    const STATUS_DROP_INS       = 'drop-ins';
    const STATUS_MUST_USE       = 'must-use';

    /** @var string */
    private $slug = '';
    /** @var string */
    public $name = '';
    /** @var string */
    public $version = '';
    /** @var string */
    public $pluginURI = '';
    /** @var string */
    public $author = '';
    /** @var string */
    public $authorURI = '';
    /** @var string */
    public $description = '';
    /** @var string */
    public $title = '';
    /** @var bool */
    public $networkActive = false;
    /** @var bool */
    public $active = false;
    /** @var bool */
    public $mustUse = false;
    /** @var bool */
    public $dropIns = false;
    /** @var bool */
    public $activateAction = false;
    /** @var bool */
    public $deactivateAction = false;
    /** @var null|string[] */
    public $deactivateMessage = null;

    /**
     * Class constructor
     *
     * @param array<string, mixed> $data Input Data
     */
    public function __construct($data)
    {
        if (!is_array($data) || !isset($data['slug'])) {
            throw new Exception('invalud input data');
        }

        foreach ($data as $key => $val) {
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->{$key} = $val;
        }
    }

    /**
     *
     * @param int $subsite // if -1 it checks that at least one site exists in which it is active in the netowrk
     *
     * @return boolean
     */
    public function isActive($subsite = -1)
    {
        if ($this->active === true) {
            return true;
        } elseif ($subsite === -1 && !empty($this->active)) {
            return true;
        } elseif (
            is_array($this->active) &&
            in_array($subsite, $this->active)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return plugin slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     *
     * @return boolean
     */
    public function isNetworkActive()
    {
        return $this->networkActive;
    }

    /**
     *
     * @return boolean
     */
    public function isMustUse()
    {
        return $this->mustUse;
    }

    /**
     *
     * @return boolean
     */
    public function isDropIns()
    {
        return $this->dropIns;
    }

    /**
     * is true if all active status are false
     *
     * @param int $subsite if -1 it checks that at least one site exists in which it is active in the netowrk
     *
     * @return boolean
     */
    public function isInactive($subsite = -1)
    {
        return !$this->isActive($subsite) && !$this->isNetworkActive() && !$this->isMustUse() && !$this->isDropIns();
    }

    /**
     * return true if isn't networkActive or must-use or drop-ins
     *
     * @return boolean
     */
    public function isNetworkInactive()
    {
        return !$this->isNetworkActive() && !$this->isMustUse() && !$this->isDropIns();
    }

    /**
     * @return bool
     */
    public function isIgnore()
    {
        return in_array($this->slug, PrmMng::getInstance()->getValue(PrmMng::PARAM_IGNORE_PLUGINS));
    }

    /**
     * @return bool
     */
    public function isForceDisabled()
    {
        return in_array($this->slug, PrmMng::getInstance()->getValue(PrmMng::PARAM_FORCE_DIABLE_PLUGINS));
    }

    /**
     * Set activate action true if the plugin is active or if deactivateAction is enabled
     *
     * @param int  $subsite         current subsite id
     * @param bool $networkCheck    if true check only on network or check by subsite id
     * @param bool $forceActivation if true skip all pluginstati check and set activation action
     *
     * @return bool return activateAction
     */
    public function setActivationAction($subsite = -1, $networkCheck = false, $forceActivation = false)
    {
        if ($this->isIgnore()) {
            return true;
        }

        if ($forceActivation) {
            Log::info(
                'PLUGINS [' . __FUNCTION__ . ']: set forced activation action ' . Log::v2str($this->slug),
                Log::LV_DEBUG
            );
            return ($this->activateAction = true);
        }

        $activate = false;
        if ($networkCheck) {
            if ($this->isNetworkInactive()) {
                $activate = true;
            }
        } else {
            if ($this->isInactive($subsite) || ($subsite > -1 && $this->isNetworkActive())) {
                $activate = true;
            }
        }

        if ($activate || $this->deactivateAction) {
            Log::info(
                'PLUGINS [' . __FUNCTION__ . ']: set activation action ' . Log::v2str($this->slug),
                Log::LV_DEBUG
            );
            $this->activateAction = true;
        }

        return $this->activateAction;
    }

    /**
     * Set deactivation action if the plugin isn't inactive
     *
     * @param int     $subsite      Id of a Subsite
     * @param ?string $shortMsg     Short message for deactivation log
     * @param ?string $longMsg      long message for deactivation log
     * @param boolean $networkCheck if true check if is active only on network
     *
     * @return boolean return deactivaeAction status
     */
    public function setDeactivateAction($subsite = -1, $shortMsg = null, $longMsg = null, $networkCheck = false)
    {
        if ($this->isIgnore()) {
            return true;
        }

        $deactivate = false;
        if ($networkCheck) {
            if (!$this->isNetworkInactive()) {
                $deactivate = true;
            }
        } else {
            if (!$this->isInactive($subsite)) {
                $deactivate = true;
            }
        }

        if ($deactivate) {
            Log::info(
                'PLUGINS [' . __FUNCTION__ . ']: set deactivate action ' . Log::v2str($this->slug),
                Log::LV_DEBUG
            );
            $this->deactivateAction = true;
            if (!empty($shortMsg)) {
                $this->deactivateMessage = array(
                    'shortMsg' => $shortMsg,
                    'longMsg' => $longMsg
                );
            }
        }
        return $this->deactivateAction;
    }

    /**
     * @return string
     */
    public function getPluginArchivePath()
    {
        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        if ($this->isMustUse()) {
            $mainDir = $archiveConfig->getRelativePathsInArchive('muplugins');
        } elseif ($this->isDropIns()) {
            $mainDir = $archiveConfig->getRelativePathsInArchive('wpcontent');
        } else {
            $mainDir = $archiveConfig->getRelativePathsInArchive('plugins');
        }
        return $mainDir . '/' . $this->slug;
    }

    /**
     * @return false|string
     */
    public function getPluginPath()
    {
        $paramManager = PrmMng::getInstance();
        $mainDir      = false;
        if ($this->isMustUse()) {
            $mainDir = $paramManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW);
        } elseif ($this->isDropIns()) {
            $mainDir = $paramManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW);
        } else {
            $mainDir = $paramManager->getValue(PrmMng::PARAM_PATH_PLUGINS_NEW);
        }

        if ($mainDir === false) {
            return false;
        }

        $dirNameRelative = dirname($this->slug);
        $relativePath    = false;
        if (empty($dirNameRelative) || $dirNameRelative == '.') {
            $relativePath = $this->slug;
        } else {
            $relativePath = $dirNameRelative;
        }

        $result = $mainDir . '/' . $relativePath;
        if (!file_exists($result)) {
            return false;
        } else {
            return $result;
        }
    }

    /**
     * @return bool
     */
    public function deactivate()
    {
        if (!$this->deactivateAction) {
            return false;
        }

        Log::info('[PLUGINS MANAGER] deactivate ' . Log::v2str($this->slug), Log::LV_DETAILED);
        $deactivated     = false;
        $origFileManager = InstallerOrigFileMng::getInstance();
        if ($this->isMustUse() || $this->isDropIns()) {
            if (($pluginPath = $this->getPluginPath()) == false) {
                Log::info('PLUGINS: can\'t remove plugin ' . $this->slug . ' because it doesn\'t exists');
            } else {
                $origFileManager->addEntry($this->slug, $pluginPath, InstallerOrigFileMng::MODE_MOVE);
                $deactivated = true;
            }
        } else {
            // for other type of plugins do nothing. They are not activated because they are missing in the table list of plugins
            $deactivated = true;
        }

        if ($deactivated) {
            if (is_null($this->deactivateMessage)) {
                DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                    'shortMsg' => $this->name . ' has been deactivated',
                    'level' => DUPX_NOTICE_ITEM::NOTICE,
                    'sections' => 'plugins'
                ));
            } else {
                DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                    'shortMsg' => $this->deactivateMessage['shortMsg'],
                    'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                    'longMsg' => $this->deactivateMessage['longMsg'],
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                    'sections' => 'plugins'
                ));
            }
        } else {
            DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                'shortMsg' => 'Can\'t deactivate the plugin ' . $this->name,
                'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg' => 'Folder of the plugin not found',
                'sections' => 'plugins'
            ));
        }

        // prevent multiple decativation action
        $this->deactivateAction = false;
        return true;
    }

    /**
     *
     * @param int $subsiteId Subsite ID
     *
     * @return string
     */
    public function getOrgiStatus($subsiteId)
    {
        if ($this->isMustUse()) {
            return self::STATUS_MUST_USE;
        } elseif ($this->isDropIns()) {
            return self::STATUS_DROP_INS;
        } elseif ($this->isNetworkActive()) {
            return self::STATUS_NETWORK_ACTIVE;
        } elseif ($this->isActive($subsiteId)) {
            return self::STATUS_ACTIVE;
        } else {
            return self::STATUS_INACTIVE;
        }
    }

    /**
     *
     * @param string $status Plugin Status
     *
     * @return string
     */
    public static function getStatusLabel($status)
    {
        switch ($status) {
            case self::STATUS_MUST_USE:
                return 'must-use';
            case self::STATUS_DROP_INS:
                return 'drop-in';
            case self::STATUS_NETWORK_ACTIVE:
                return 'network active';
            case self::STATUS_ACTIVE:
                return 'active';
            case self::STATUS_INACTIVE:
                return 'inactive';
            default:
                throw new Exception('Invalid status');
        }
    }

    /**
     * Uninstall a single plugin.
     *
     * Calls the uninstall hook, if it is available.
     *
     * @return bool True if a plugin's uninstall.php file has been found and included.
     */
    public function uninstall()
    {
        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        try {
            Helpers::loadWP();
            // UNINSTALL PLUGIN IF IS ACTIVE
            $level = error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING
                | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);
            if (SnapUtil::isIniValChangeable('display_errors')) {
                @ini_set('display_errors', '0');
            }
            Log::info("UNINSTALL PLUGIN " . Log::v2str($this->slug), Log::LV_DEBUG);
            $pluginFile            = plugin_basename($this->slug);
            $uninstallable_plugins = (array)get_option('uninstall_plugins');
            /**
             * Fires in uninstall_plugin() immediately before the plugin is uninstalled.
             *
             * @param string $plugin Path to the main plugin file from plugins directory.
             * @param array $uninstallable_plugins Uninstallable plugins.
             */
            do_action('pre_uninstall_plugin', $this->slug, $uninstallable_plugins);
            if (file_exists(WP_PLUGIN_DIR . '/' . dirname($pluginFile) . '/uninstall.php')) {
                if (isset($uninstallable_plugins[$pluginFile])) {
                    unset($uninstallable_plugins[$pluginFile]);
                    update_option('uninstall_plugins', $uninstallable_plugins);
                }
                unset($uninstallable_plugins);
                if (defined('WP_UNINSTALL_PLUGIN')) {
                    $already_defined_uninstall_const = true;
                } else {
                    define('WP_UNINSTALL_PLUGIN', $pluginFile);
                    $already_defined_uninstall_const = false;
                }

                wp_register_plugin_realpath(WP_PLUGIN_DIR . '/' . $pluginFile);
                if ($already_defined_uninstall_const) {
                    $uninstall_file_content = file_get_contents(WP_PLUGIN_DIR . '/' . dirname($pluginFile) . '/uninstall.php');
                    /*
                          $regexProhibited = array(
                          'dirname[\t\s]*\([\t\s]*WP_UNINSTALL_PLUGIN[\t\s]*\)',
                          'WP_UNINSTALL_PLUGIN[\t\s]*\!?=',
                          '\!?=[\t\s]*WP_UNINSTALL_PLUGIN',
                          'current_user_can'
                          ); */
                    $prohibited_codes = array(
                        'dirname( WP_UNINSTALL_PLUGIN )',
                        'dirname(WP_UNINSTALL_PLUGIN )',
                        'dirname( WP_UNINSTALL_PLUGIN)',
                        'dirname(WP_UNINSTALL_PLUGIN)',
                        'WP_UNINSTALL_PLUGIN =',
                        'WP_UNINSTALL_PLUGIN !=',
                        'WP_UNINSTALL_PLUGIN=',
                        'WP_UNINSTALL_PLUGIN!=',
                        '= WP_UNINSTALL_PLUGIN',
                        '!= WP_UNINSTALL_PLUGIN',
                        '=WP_UNINSTALL_PLUGIN=',
                        '!=WP_UNINSTALL_PLUGIN',
                        'current_user_can',
                    );
                    foreach ($prohibited_codes as $prohibited_code) {
                        if (false !== stripos($uninstall_file_content, $prohibited_code)) {
                            Log::info("Can't include uninstall.php file of the " . $this->slug . " because prohibited code found");
                            return false;
                        }
                    }
                }
                include(WP_PLUGIN_DIR . '/' . dirname($pluginFile) . '/uninstall.php');
            } elseif (isset($uninstallable_plugins[$pluginFile])) {
                $callable = $uninstallable_plugins[$pluginFile];
                unset($uninstallable_plugins[$pluginFile]);
                update_option('uninstall_plugins', $uninstallable_plugins);
                unset($uninstallable_plugins);
                wp_register_plugin_realpath(WP_PLUGIN_DIR . '/' . $pluginFile);
                include_once(WP_PLUGIN_DIR . '/' . $pluginFile);
                add_action("uninstall_{$pluginFile}", $callable);
                /**
                 * Fires in uninstall_plugin() once the plugin has been uninstalled.
                 *
                 * The action concatenates the 'uninstall_' prefix with the basename of the
                 * plugin passed to uninstall_plugin() to create a dynamically-named action.
                 *
                 * @since 2.7.0
                 */
                do_action("uninstall_{$pluginFile}");
                // Extra
                // Extra
            } else {
                // The plugin was never activated so no need to call uninstallation hook
            }

            // store plugin in original file folder
            $origFileManager = InstallerOrigFileMng::getInstance();
            if (($pluginPath = $this->getPluginPath()) == false) {
                Log::info('PLUGINS: can\'t remove plugin ' . $this->slug . ' because doesn\'t exist');
            } else {
                $origFileManager->addEntry($this->slug, $pluginPath, InstallerOrigFileMng::MODE_MOVE);
            }
        } catch (Exception $e) {
            $errorMsg = "**ERROR** The Inactive plugin " . $this->name . " can't be deleted";
            $longMsg  = 'Please delete the plugin ' . $this->name . ' (' . $this->slug . ') manually' . PHP_EOL .
                'Exception message: ' . $e->getMessage() . PHP_EOL .
                'Trace: ' . $e->getTraceAsString();
            Log::info($errorMsg);
            $nManager->addFinalReportNotice(array(
                'shortMsg' => $errorMsg,
                'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                'longMsg' => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                'sections' => 'plugins'
            ));
            return false;
        } catch (Error $e) {
            $errorMsg = "**ERROR** The Inactive plugin " . $this->name . " can't be deleted";
            $longMsg  = 'Please delete the plugin ' . $this->name . ' (' . $this->slug . ') manually' . PHP_EOL .
                'Exception message: ' . $e->getMessage() . PHP_EOL .
                'Trace: ' . $e->getTraceAsString();
            Log::info($errorMsg);
            $nManager->addFinalReportNotice(array(
                'shortMsg' => $errorMsg,
                'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                'longMsg' => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                'sections' => 'plugins'
            ));
            return false;
        }

        error_reporting($level);
        return true;
    }
}
