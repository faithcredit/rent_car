<?php

/**
 * godaddy custom hosting class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\HOST
 * @link    http://www.php-fig.org/psr/psr-2/
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUP_PRO_GoDaddy_Host implements DUP_PRO_Host_interface
{
    public static function getIdentifier()
    {
        return DUP_PRO_Custom_Host_Manager::HOST_GODADDY;
    }

    public function isHosting()
    {
        return apply_filters('duplicator_pro_godaddy_host_check', file_exists(WPMU_PLUGIN_DIR . '/gd-system-plugin.php'));
    }

    public function init()
    {
        add_filter('duplicator_pro_default_archive_build_mode', array(__CLASS__, 'defaultArchiveBuildMode'), 20, 1);
        add_filter('duplicator_pro_overwrite_params_data', array(__CLASS__, 'installerParams'));
    }

    /**
     * In godaddy the packag build mode must be Dup archive
     *
     * @param int $archiveBuildMode archive build mode
     *
     * @return int
     */
    public static function defaultArchiveBuildMode($archiveBuildMode)
    {
        return DUP_PRO_Archive_Build_Mode::DupArchive;
    }

    public static function installerParams($data)
    {
        // disable wp engine plugins
        $data['fd_plugins'] = array('value' => array(
                'gd-system-plugin.php',
                'object-cache.php'
            )
        );

        // generate new wp-config.php file
        $data['wp_config'] = array(
            'value'      => 'new',
            'formStatus' => 'st_infoonly'
        );

        return $data;
    }
}
