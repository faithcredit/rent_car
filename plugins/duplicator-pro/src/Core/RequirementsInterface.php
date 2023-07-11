<?php

/**
 * Interface that collects the functions of initial checks on the requirements to run the plugin
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core;

if (!interface_exists('Duplicator\Core\RequirementsInterface', false)) {

    interface RequirementsInterface
    {
        /**
         * Return true if plugin can run
         *
         * @param string $pluginFile plugin file name
         *
         * @return boolean
         */
        public static function canRun($pluginFile);

        /**
         * Return plugin hash
         *
         * @return string
         */
        public static function getAddsHash();
    }

}
