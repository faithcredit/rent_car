<?php

/**
 * Used to generate a alert in the main WP admin screens
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    DUP_PRO
 * @subpackage classes/ui
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      2.0.0
 */

use Duplicator\Core\CapMng;

defined("ABSPATH") or die("");
class DUP_PRO_UI_Alert
{
    /**
     * Displays notice for plugins deactivated during install,
     * and removes already activated from DB
     */
    public static function activatePluginsAfterInstall()
    {
        if (!CapMng::can(CapMng::CAP_BASIC, false)) {
            return;
        }
        $pluginsToActive = get_option(DUP_PRO_UI_Notice::OPTION_KEY_ACTIVATE_PLUGINS_AFTER_INSTALL, false);
        if (!is_array($pluginsToActive) || empty($pluginsToActive)) {
            return false;
        }

        $shouldBeActivated = array();
        $allPlugins        = get_plugins();
        foreach ($pluginsToActive as $index => $pluginSlug) {
            if (!isset($allPlugins[$pluginSlug])) {
                unset($pluginsToActive[$index]);
                continue;
            }

            if (is_multisite()) {
                $isActive = is_plugin_active_for_network($pluginSlug);
            } else {
                $isActive = is_plugin_active($pluginSlug);
            }

            if (!$isActive) {
                $shouldBeActivated[$pluginSlug] = $allPlugins[$pluginSlug]['Name'];
            } else {
                unset($pluginsToActive[$index]);
            }
        }

        if (empty($shouldBeActivated)) {
            delete_option(DUP_PRO_UI_Notice::OPTION_KEY_ACTIVATE_PLUGINS_AFTER_INSTALL);
            return;
        } else {
            update_option(DUP_PRO_UI_Notice::OPTION_KEY_ACTIVATE_PLUGINS_AFTER_INSTALL, $pluginsToActive);
        }

        $html = "<img src='" . esc_url(plugins_url('duplicator-pro/assets/img/warning.png')) . "' style='float:left; padding:0 10px 0 5px' />" .
                "<div style='margin-left: 70px;'><p><b>" . __('Warning!', 'duplicator-pro') . "</b> " . __('Migration Almost Complete!', 'duplicator-pro') . "<br/>" .
               __('Plugin(s) listed here must be activated, Please activate them:', 'duplicator-pro') . "</p><ul>";
        foreach ($shouldBeActivated as $slug => $title) {
            if (is_multisite()) {
                $activateURL = network_admin_url('plugins.php?action=activate&plugin=' . $slug);
            } else {
                $activateURL = admin_url('plugins.php?action=activate&plugin=' . $slug);
            }
            $activateURL = wp_nonce_url($activateURL, 'activate-plugin_' . $slug);
            $anchorTitle = sprintf(__('Activate %s', 'duplicator-pro'), $title);
            $html       .= '<li><a href="' . DUP_PRO_U::esc_attr__($activateURL) . '" title="' . DUP_PRO_U::esc_attr__($anchorTitle) . '">' . DUP_PRO_U::esc_attr__($title) . '</a></li>';
        }

        $html .= "</ul></div>";
        DUP_PRO_UI_Notice::displayGeneralAdminNotice($html, DUP_PRO_UI_Notice::GEN_WARNING_NOTICE, true, array(
                'duplicator-pro-admin-notice',
                'dpro-admin-notice',
                'dpro-yellow-border'
            ), array(
                'data-to-dismiss' => DUP_PRO_UI_Notice::OPTION_KEY_ACTIVATE_PLUGINS_AFTER_INSTALL
            ), true);
    }
}
