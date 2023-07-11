<?php

/**
 * wpengine custom hosting class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\HOST
 * @link    http://www.php-fig.org/psr/psr-2/
 */

use Duplicator\Libs\Snap\SnapUtil;

class DUP_PRO_WPEngine_Host implements DUP_PRO_Host_interface
{
    public static function getIdentifier()
    {
        return DUP_PRO_Custom_Host_Manager::HOST_WPENGINE;
    }

    public function isHosting()
    {
        ob_start();
        SnapUtil::phpinfo(INFO_ENVIRONMENT);
        $serverinfo = ob_get_clean();
        return apply_filters('duplicator_pro_wp_engine_host_check', (strpos($serverinfo, "WPENGINE_ACCOUNT") !== false));
    }

    public function init()
    {
        add_filter('duplicator_pro_overwrite_params_data', array(__CLASS__, 'installerParams'));
    }

    public static function installerParams($data)
    {
        // disable wp engine plugins
        $data['fd_plugins'] = array('value' => array(
                'mu-plugin.php',
                'advanced-cache.php',
                'wpengine-security-auditor.php',
                'stop-long-comments.php',
                'slt-force-strong-passwords.php',
                'wpe-wp-sign-on-plugin.php'
            )
        );

        // generare new wp-config.php file
        $data['wp_config'] = array(
            'value'      => 'new',
            'formStatus' => 'st_infoonly'
        );

        // disable WP_CACHE
        $data['wpc_WP_CACHE'] = array(
            'value'      => array(
                'value'      => false,
                'inWpConfig' => false
            ),
            'formStatus' => 'st_infoonly'
        );

        return $data;
    }
}
