<?php

/**
 * Version Pro Base Installer addon class
 *
 * Name: Duplicator PRO base
 * Version: 1
 * Author: Snap Creek
 * Author URI: http://snapcreek.com
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @version   GIT: $Id$
 * @link      http://snapcreek.com
 */

namespace Duplicator\Installer\Addons\ProBase;

use Duplicator\Installer\Core\Hooks\HooksMng;
use Duplicator\Installer\Core\Params\Items\ParamItem;

/**
 * Version Pro Base Installer addon class
 *
 * @category Duplicator
 * @package  Installer
 * @author   Snapcreek <admin@snapcreek.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     http://snapcreek.com
 */
class ProBase extends \Duplicator\Installer\Core\Addons\InstAbstractAddonCore
{
    /**
     * Main init addon
     *
     * @return void
     */
    public function init()
    {
        HooksMng::getInstance()->addFilter(
            'dupx_main_header',
            function ($value) {
                return 'Duplicator PRO';
            }
        );

        HooksMng::getInstance()->addFilter('installer_get_init_params', array(__CLASS__,'getInitParams'));
        HooksMng::getInstance()->addAction(
            'after_params_overwrite',
            array('\\Duplicator\\Installer\\Addons\\ProBase\\AdvancedParams','updateParamsAfterOverwrite')
        );
    }


    /**
     * getInitParams
     *
     * @param ParamItem[] $params params list
     *
     * @return ParamItem[]
     */
    public static function getInitParams($params)
    {
        $advParams = array();
        AdvancedParams::init($advParams);
        return array_merge($params, $advParams);
    }

    /**
     * Get addon main folder
     *
     * @return string
     */
    public static function getAddonPath()
    {
        return __DIR__;
    }

    /**
     * Get addon main file
     *
     * @return string
     */
    public static function getAddonFile()
    {
        return __FILE__;
    }
}
