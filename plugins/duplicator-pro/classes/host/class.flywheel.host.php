<?php

/**
 * Flywheel custom hosting class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\HOST
 * @link    http://www.php-fig.org/psr/psr-2/
 */

use Duplicator\Libs\Snap\SnapWP;

class DUP_PRO_Flywheel_Host implements DUP_PRO_Host_interface
{
    public static function getIdentifier()
    {
        return DUP_PRO_Custom_Host_Manager::HOST_FLYWHEEL;
    }

    public function isHosting()
    {
        return apply_filters('duplicator_pro_host_check', file_exists(self::getFlywheelMainPluginPaht()), self::getIdentifier());
    }

    public function init()
    {
        add_filter('duplicator_pro_overwrite_params_data', array(__CLASS__, 'installerParams'));
        add_filter('duplicator_pro_global_file_filters', array(__CLASS__, 'filterPluginFile'));
    }

    public static function getFlywheelMainPluginPaht()
    {
        return trailingslashit(SnapWP::getHomePath()) . '.fw-config.php';
    }

    public static function filterPluginFile($globalsFileFilters)
    {
        $globalsFileFilters[] = self::getFlywheelMainPluginPaht();
        return $globalsFileFilters;
    }

    public static function installerParams($data)
    {
        // generare new wp-config.php file
        $data['wp_config'] = array(
            'value'      => 'new',
            'formStatus' => 'st_infoonly'
        );

        return $data;
    }
}
