<?php

/**
 * Class that collects the functions of initial checks on the requirements to run the plugin
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Addons;

final class AddonsManager
{
    /** @var ?self */
    private static $instance = null;
    /** @var AbstractAddonCore[] */
    private $addons = array();
    /** @var AbstractAddonCore[] */
    private $enabledAddons = array();
    /** @var object */
    private $check = null;

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
     * Class constructor
     */
    private function __construct()
    {
        $this->addons = self::getAddonListFromFolder();
        $checkString  = pack("H*", \Duplicator\Core\Bootstrap::getAddsHash());
        $this->check  = json_decode($checkString);
    }

    /**
     *
     * @return void
     */
    public function inizializeAddons()
    {
        if (!is_array($this->check->r) || !is_array($this->check->fd)) {
            throw new \Exception('Addons initialization error');
        }

        foreach ($this->addons as $addon) {
            if (!in_array($addon->getSlug(), $this->check->fd) && $addon->canEnable() && $addon->hasDependencies()) {
                $this->enabledAddons[$addon->getSlug()] = $addon;
                $addon->init();
            }
        }

        do_action('duplicator_addons_loaded');
    }

    /**
     *
     * @return boolean
     */
    public function isAddonsReady()
    {
        return (count(array_diff($this->check->r, array_keys($this->enabledAddons))) === 0);
    }

    /**
     * Get list of avaiables addons
     *
     * @return string[]
     */
    public function getAvaiableAddons()
    {
        $result = array();
        foreach ($this->addons as $addon) {
            $result[] = $addon->getSlug();
        }

        return $result;
    }

    /**
     *
     * @return AbstractAddonCore[]
     */
    public function getEnabledAddons()
    {
        return $this->enabledAddons;
    }

    /**
     * return addons folder
     *
     * @return string
     */
    public static function getAddonsPath()
    {
        return DUPLICATOR____PATH . '/addons';
    }

    /**
     *
     * @return AbstractAddonCore[]
     */
    private static function getAddonListFromFolder()
    {
        $addonList = array();

        $checkDir = trailingslashit(self::getAddonsPath());

        if (!is_dir($checkDir)) {
            return array();
        }

        if (($dh = opendir($checkDir)) == false) {
            return array();
        }

        while (($elem = readdir($dh)) !== false) {
            if ($elem === '.' || $elem === '..') {
                continue;
            }

            $fullPath = $checkDir . $elem;

            if (!is_dir($fullPath)) {
                continue;
            }

            $addonMainFile  = false;
            $addonMainClass = '';

            if (($addonDh       = opendir($fullPath)) == false) {
                continue;
            }

            while (($addonElem = readdir($addonDh)) !== false) {
                if ($addonElem === '.' || $addonElem === '..') {
                    continue;
                }
                $info = pathinfo($fullPath . '/' . $addonElem);

                if (strcasecmp($elem, $info['filename']) === 0) {
                    $addonMainFile  = $checkDir . $elem . '/' . $addonElem;
                    $addonMainClass = '\\Duplicator\\Addons\\' . $info['filename'] . '\\' . $info['filename'];
                    break;
                }
            }

            if (empty($addonMainFile)) {
                continue;
            }

            try {
                if (!is_subclass_of($addonMainClass, 'Duplicator\\Core\\Addons\\AbstractAddonCore')) {
                    \DUP_PRO_Log::trace(
                        'Addon main file ' . $addonMainFile . ' don\'t contain a main class ' .
                        $addonMainClass . 'that extend AbstractAddonCore'
                    );
                    continue;
                }
            } catch (\Exception $e) {
                \DUP_PRO_Log::trace('Addon file ' . $addonMainFile . ' exists but not countain addon main core class, Exception: ' . $e->getMessage());
                continue;
            } catch (\Error $e) {
                \DUP_PRO_Log::trace('Addon file ' . $addonMainFile . ' exists but generate an error, Exception: ' . $e->getMessage());
                continue;
            }

            $addonObj                        = $addonMainClass::getInstance();
            $addonList[$addonObj->getSlug()] = $addonObj;
        }
        closedir($dh);

        return $addonList;
    }
}
