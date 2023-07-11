<?php

/**
 * Test addon main class
 *
 * Version: 0.1
 * Addon URI: http://addon.test
 * Description: This is test addon
 * Author: Snap Creek
 * Author URI: http://snapcreek.com
 * Requires WP min version: 5.0
 * Requires PHP: 8.0.0
 * Requires Duplicator min version: 4.0.0
 * Requires addons:
 *
 * @package    Duplicator
 * @subpackage Addons\Test
 * @copyright  (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Addons\Test;

class Test extends \Duplicator\Core\Addons\AbstractAddonCore
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function init()
    {
        add_action('duplicator_addons_loaded', array($this, 'addonsLoaded'));
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function canEnable()
    {
        return false;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function addonsLoaded()
    {
        echo '<pre>';
        var_dump($this->addonData);
        echo '</pre>';
        die;
    }

    /**
     * Return addon file path
     *
     * @return string
     */
    public static function getAddonFile()
    {
        return __FILE__;
    }
}
