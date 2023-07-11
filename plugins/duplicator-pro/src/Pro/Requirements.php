<?php

/**
 * Class that collects the functions of initial checks on the requirements to run the plugin
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Pro;

class Requirements implements \Duplicator\Core\RequirementsInterface
{
    const DUP_LITE_PLUGIN_KEY = 'duplicator/duplicator.php';

    /**
     *
     * @var string // curent plugin file full path
     */
    protected static $pluginFile = '';

    /**
     *
     * @var string // message on deactivation
     */
    protected static $deactivationMessage = '';

    /**
     * This function checks the requirements to run Duplicator.
     * At this point wordpress is not yet completely initialized so functionality is limited.
     * It need to hook into "admin_init" to get the full functionality of wordpress.
     *
     * @param string $pluginFile main plugin file path
     *
     * @return boolean           true if plugin can be exectued
     */
    public static function canRun($pluginFile)
    {
        $result           = true;
        self::$pluginFile = $pluginFile;

        if (self::isPluginActive(self::DUP_LITE_PLUGIN_KEY)) {
            add_action('admin_init', array(__CLASS__, 'addLiteEnableNotice'));
            $pluginUrl                 = (is_multisite() ? network_admin_url('plugins.php') : admin_url('plugins.php'));
            self::$deactivationMessage = __('Sorry, you cannot activate the Duplicator PRO plugin ', 'duplicator-pro')
                  . __('while the Duplicator LITE version is active.', 'duplicator-pro') . '<br/>'
                  . __('Please deactivate the Duplicator LITE plugin, then reactivate the Duplicator PRO Plugin from the ', 'duplicator-pro')
                  . "<a href='" . $pluginUrl . "'>" . __('plugins page', 'duplicator-pro') . ".</a>";
            $result                    = false;
        }

        if ($result === false) {
            register_activation_hook($pluginFile, array(__CLASS__, 'deactivateOnActivation'));
        }

        return $result;
    }

    /**
     * Return plugin hash
     *
     * @return string
     */
    public static function getAddsHash()
    {
        //return '7b2272223a5b2250726f42617365222c224f74686572506c7567225d2c226664223a5b224c69746542617365225d7d';
        return '7b2272223a5b2250726f42617365225d2c226664223a5b224c69746542617365225d7d';
    }

    /**
     *
     * @param string $plugin plugin key
     *
     * @return boolean // return strue if plugin key is active and plugin file exists
     */
    protected static function isPluginActive($plugin)
    {
        $isActive = false;
        if (in_array($plugin, (array) get_option('active_plugins', array()))) {
            $isActive = true;
        }

        if (is_multisite()) {
            $plugins = get_site_option('active_sitewide_plugins');
            if (isset($plugins[$plugin])) {
                $isActive = true;
            }
        }

        return ($isActive && file_exists(WP_PLUGIN_DIR . '/' . $plugin));
    }

    /**
     * Display admin notice only if user can manage plugins.
     *
     * @return void
     */
    public static function addLiteEnableNotice()
    {
        if (current_user_can('activate_plugins')) {
            add_action('admin_notices', array(__CLASS__, 'liteEnabledNotice'));
        }
    }

    /**
     * deactivate current plugin on activation
     *
     * @return void
     */
    public static function deactivateOnActivation()
    {
        deactivate_plugins(plugin_basename(self::$pluginFile));
        wp_die(self::$deactivationMessage);
    }

    /**
     * diplay admin notice if duplicator pro is enabled
     *
     * @return void
     */
    public static function liteEnabledNotice()
    {
        $pluginUrl = (is_multisite() ? network_admin_url('plugins.php') : admin_url('plugins.php'));
        ?>
        <div class="error notice">
            <p>
                <span class="dashicons dashicons-warning"></span>
                <b><?php _e('Duplicator Pro Notice:', 'duplicator-pro'); ?></b>
                <?php _e('The "Duplicator Lite" and "Duplicator Pro" plugins cannot both be active at the same time.  ', 'duplicator-pro'); ?>
            </p>
            <p>
                <?php _e('To use "Duplicator PRO" please deactivate "Duplicator LITE" from the ', 'duplicator-pro'); ?>
                <a href="<?php echo esc_url($pluginUrl); ?>">
                    <?php _e('plugins page', 'duplicator-pro'); ?>.
                </a>
            </p>
        </div>
        <?php
    }
}
