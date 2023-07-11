<?php

/**
 * Standard: PSR-2 (almost)
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    DUP_PRO
 * @subpackage classes/package
 * @copyright  (c) 2019, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      1.0.0
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Shell\Shell;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;

global $wp_version;

$view_state       = DUP_PRO_UI_ViewState::getArray();
$ui_css_srv_panel = (isset($view_state['dup-settings-diag-srv-panel']) && $view_state['dup-settings-diag-srv-panel']) ? 'display:block' : 'display:none';

$dbvar_maxtime  = DUP_PRO_DB::getVariable('wait_timeout');
$dbvar_maxpacks = DUP_PRO_DB::getVariable('max_allowed_packet');
$dbvar_maxtime  = is_null($dbvar_maxtime) ? DUP_PRO_U::__("unknow") : $dbvar_maxtime;
$dbvar_maxpacks = is_null($dbvar_maxpacks) ? DUP_PRO_U::__("unknow") : $dbvar_maxpacks;
$home_path      = duplicator_pro_get_home_path();
$space          = SnapIO::diskTotalSpace($home_path);
$space_free     = SnapIO::diskFreeSpace($home_path);
if ($space > 0 && $space_free >= 0) {
    $perc = round((100 / $space) * $space_free, 2);
} else {
    $perc = -1;
}
$mysqldumpPath     = DUP_PRO_DB::getMySqlDumpPath();
$mysqlDumpSupport  = ($mysqldumpPath) ? $mysqldumpPath : 'Path Not Found';
$client_ip_address = DUP_PRO_Server::getClientIP();
$error_log_path    = ini_get('error_log');
$timezone_string   = function_exists('wp_timezone_string') ? wp_timezone_string() :  __('Unknown', 'duplicator-pro');
?>

<!-- ==============================
SERVER SETTINGS -->
<div class="dup-box">
    <div class="dup-box-title">
        <i class="fas fa-tachometer-alt"></i>
        <?php DUP_PRO_U::esc_html_e("Server Settings") ?>
        <button class="dup-box-arrow">
            <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Server Settings') ?></span>
        </button>
    </div>
    <div class="dup-box-panel" id="dup-settings-diag-srv-panel" style="<?php echo esc_attr($ui_css_srv_panel); ?>">
        <table class="widefat" cellspacing="0">
            <tr>
                <td class='dpro-settings-diag-header' colspan="2"><?php DUP_PRO_U::esc_html_e("General"); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Duplicator Version"); ?></td>
                <td>
                    <?php echo esc_html(DUPLICATOR_PRO_VERSION); ?> - 
                    <small><i><a href="update-core.php?dup_pro_clear_updater_cache=1"><?php DUP_PRO_U::esc_html_e("Check WordPress updates"); ?></a></i></small>
                </td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Operating System"); ?></td>
                <td><?php echo esc_html(PHP_OS); ?></td>
            </tr>
            <tr>
                <td><?php _e("Timezone"); ?></td>
                <td><?php echo esc_html($timezone_string); ?> &nbsp; <small><i>This is a <a href='options-general.php'>WordPress setting</a></i></small></td>
            </tr>
            <tr>
                <td><?php _e("Server Time"); ?></td>
                <td><?php echo esc_html(current_time("Y-m-d H:i:s")); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Web Server"); ?></td>
                <td><?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Loaded PHP INI"); ?></td>
                <td><?php echo php_ini_loaded_file(); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Server IP"); ?></td>
                <?php
                if (isset($_SERVER['SERVER_ADDR'])) {
                    $server_address = $_SERVER['SERVER_ADDR'];
                } elseif (isset($_SERVER['SERVER_NAME']) && function_exists('gethostbyname')) {
                    $server_address = gethostbyname($_SERVER['SERVER_NAME']);
                } else {
                    $server_address = __("Can't detect", 'duplicator-pro');
                }
                ?>
                <td><?php echo esc_html($server_address); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Outbound IP"); ?></td>
                <?php
                $outbound_ip = DUP_PRO_Server::getOutboundIP();

                if ($outbound_ip === false) {
                    $outbound_ip = __("Can't detect", 'duplicator-pro');
                }
                ?>
                <td><?php echo esc_html($outbound_ip); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Client IP"); ?></td>
                <td><?php echo esc_html($client_ip_address); ?></td>
            </tr>
            <tr style="font-style: italic">
                <td>
                    <?php DUP_PRO_U::esc_html_e("Host"); ?><br/>
                    <small><?php DUP_PRO_U::esc_html_e("version scope"); ?></small>
                </td>
                <td>
                    <?php
                    $url = parse_url(get_site_url(), PHP_URL_HOST);
                    echo esc_url($url);
                    ?>
                    <br/>
                    <small><?php echo "WP-" . esc_html($wp_version) . ", DP-" . esc_html(DUPLICATOR_PRO_VERSION) . " | PHP-" . esc_html(phpversion()) . ', DB-' . esc_html(DUP_PRO_DB::getVersion()); ?></small>
                </td>
            </tr>
            <tr>
                <td class='dpro-settings-diag-header' colspan="2">WordPress</td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Version"); ?></td>
                <td><?php echo esc_html($wp_version); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Langugage"); ?></td>
                <td><?php bloginfo('language'); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Charset"); ?></td>
                <td><?php bloginfo('charset'); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Memory Limit "); ?></td>
                <td><?php echo esc_html(WP_MEMORY_LIMIT); ?> (<?php
                    DUP_PRO_U::esc_html_e("Max");
                echo '&nbsp;' . esc_html(WP_MAX_MEMORY_LIMIT);
                ?>)</td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Managed hosting "); ?></td>
                <td><?php
                    echo (DUP_PRO_Custom_Host_Manager::getInstance()->isManaged() === false) ?
                        __('No managed hosting detected') :
                        implode(', ', DUP_PRO_Custom_Host_Manager::getInstance()->getActiveHostings());
                ?>
                </td>
            </tr>
            <tr>
                <td class='dpro-settings-diag-header' colspan="2">PHP</td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Version"); ?></td>
                <td><?php echo esc_html(phpversion()); ?></td>
            </tr>
            <tr>
                <td>SAPI</td>
                <td><?php echo PHP_SAPI ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("User"); ?></td>
                <td><?php echo DUP_PRO_Server::getCurrentUser(); ?></td>
            </tr>
            <tr>
                <td><a href="http://php.net/manual/en/features.safe-mode.php" target="_blank"><?php DUP_PRO_U::esc_html_e("Safe Mode"); ?></a></td>
                <td>
                    <?php
                    echo (((strtolower(@ini_get('safe_mode')) == 'on') || (strtolower(@ini_get('safe_mode')) == 'yes') ||
                    (strtolower(@ini_get('safe_mode')) == 'true') || (ini_get("safe_mode") == 1 ))) ? DUP_PRO_U::__('On') : DUP_PRO_U::__('Off');
                    ?>
                </td>
            </tr>
            <tr>
                <td><a href="http://www.php.net/manual/en/ini.core.php#ini.memory-limit" target="_blank"><?php DUP_PRO_U::esc_html_e("Memory Limit"); ?></a></td>
                <?php
                $memory_limit = @ini_get('memory_limit');
                ?>               
                <td><?php echo empty($memory_limit) ? '' : esc_html($memory_limit); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Memory In Use"); ?></td>
                <td><?php echo esc_html(size_format(@memory_get_usage(true), 2)); ?></td>
            </tr>
            <tr>
                <td><a href="http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank"><?php DUP_PRO_U::esc_html_e("Max Execution Time"); ?></a></td>
                <td>
                    <?php
                    echo esc_html(@ini_get('max_execution_time'));
                    $try_update = set_time_limit(0);
                    $try_update = $try_update ? 'is dynamic' : 'value is fixed';
                    echo " (default) - {$try_update}";
                    ?>
                    <i class="fa fa-question-circle data-size-help"
                       data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Max Execution Time"); ?>"
                       data-tooltip="<?php
                        DUP_PRO_U::esc_attr_e('If the value shows dynamic then this means its possible for PHP to run longer than the default.  '
                           . 'If the value is fixed then PHP will not be allowed to run longer than the default.');
                        ?>"></i>
                </td>
            </tr>
            <tr>
                <td><a href="http://php.net/manual/en/ini.core.php#ini.open-basedir" target="_blank"><?php DUP_PRO_U::esc_html_e("open_basedir"); ?></a></td>
                <td>
                    <?php
                    $open_base_set = @ini_get('open_basedir');
                    echo empty($open_base_set) ? DUP_PRO_U::__('Off') : esc_html($open_base_set);
                    ?>
                </td>
            </tr>
            <tr>
                <td><a href="http://us3.php.net/shell_exec" target="_blank"><?php DUP_PRO_U::esc_html_e("Shell (shell_exec)"); ?></a></td>
                <td><?php echo (!Shell::hasDisabledFunctions('shell_exec')) ? DUP_PRO_U::esc_html__("Is Supported") : DUP_PRO_U::esc_html__("Not Supported"); ?></td>
            </tr>
            <tr>
                <td><a href="http://us3.php.net/popen" target="_blank"><?php DUP_PRO_U::esc_html_e("Shell (popen)"); ?></a></td>
                <td><?php echo (!Shell::hasDisabledFunctions('popen')) ? DUP_PRO_U::esc_html__("Is Supported") : DUP_PRO_U::esc_html__("Not Supported"); ?></td>
            </tr>
            <tr>
                <td><a href="https://www.php.net/manual/en/function.exec.php" target="_blank"><?php DUP_PRO_U::esc_html_e("Shell (exec)"); ?></a></td>
                <td><?php echo !Shell::hasDisabledFunctions('exec') ? DUP_PRO_U::esc_html__("Is Supported") : DUP_PRO_U::esc_html__("Not Supported"); ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Shell Exec Zip"); ?></td>
                <td><?php echo (DUP_PRO_Zip_U::getShellExecZipPath() != null) ? DUP_PRO_U::esc_html__("Is Supported") : DUP_PRO_U::esc_html__("Not Supported"); ?></td>
            </tr>
            <tr>
                <td><a href="https://suhosin.org/stories/index.html" target="_blank"><?php DUP_PRO_U::esc_html_e("Suhosin Extension"); ?></a></td>
                <td><?php echo Shell::isSuhosinEnabled() ? DUP_PRO_U::esc_html__("Enabled") : DUP_PRO_U::esc_html__("Disabled"); ?></td>
            </tr>
            <tr>
                <td>Architecture</td>
                <td>                    
                    <?php echo SnapUtil::getArchitectureString(); ?>
                </td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Error Log File "); ?></td>
                <td><?php echo esc_html($error_log_path); ?></td>
            </tr>
            <tr>
                <td class='dpro-settings-diag-header' colspan="2">MySQL</td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Version"); ?></td>
                <td><?php echo DUP_PRO_DB::getVersion() ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Charset"); ?></td>
                <td><?php echo DB_CHARSET ?></td>
            </tr>
            <tr>
                <td><a href="http://dev.mysql.com/doc/refman/5.0/en/server-system-variables.html#sysvar_wait_timeout" target="_blank"><?php DUP_PRO_U::esc_html_e("Wait Timeout"); ?></a></td>
                <td><?php echo esc_html($dbvar_maxtime); ?></td>
            </tr>
            <tr>
                <td style="white-space:nowrap"><a href="http://dev.mysql.com/doc/refman/5.0/en/server-system-variables.html#sysvar_max_allowed_packet" target="_blank"><?php DUP_PRO_U::esc_html_e("Max Allowed Packets"); ?></a></td>
                <td><?php echo esc_html($dbvar_maxpacks); ?></td>
            </tr>
            <tr>
                <td><a href="http://dev.mysql.com/doc/refman/5.0/en/mysqldump.html" target="_blank"><?php DUP_PRO_U::esc_html_e("msyqldump Path"); ?></a></td>
                <td><?php echo esc_html($mysqlDumpSupport); ?></td>
            </tr>
            <tr>
                <td class='dpro-settings-diag-header' colspan="2"><?php DUP_PRO_U::esc_html_e("Paths info"); ?></td>
            </tr
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Target root path"); ?></a></td>
                <td><?php echo esc_html(DUP_PRO_Archive::getTargetRootPath()); ?></td>
            </tr>  
            <?php
            $scanPaths = DUP_PRO_Archive::getScanPaths();
            foreach ($scanPaths as $path) {
                ?>
                <tr>
                    <td><?php echo DUP_PRO_U::esc_html__("Scan path"); ?></a></td>
                    <td><?php echo esc_html($path); ?></td>
                </tr>                
                <?php
            }
            ?>
            <tr><td>&nbsp;</td><td></td></tr> 
            <?php
            $originalPaths = DUP_PRO_Archive::getOriginalPaths();
            foreach ($originalPaths as $key => $value) {
                ?>
                <tr>
                    <td><?php echo DUP_PRO_U::esc_html__("Original ") . $key; ?></a></td>
                    <td><?php echo esc_html($value); ?></td>
                </tr>                
                <?php
            }
            ?>
            <tr><td>&nbsp;</td><td></td></tr> 
            <?php
            $archivePaths = DUP_PRO_Archive::getArchiveListPaths();
            foreach ($archivePaths as $key => $value) {
                ?>
                <tr>
                    <td><?php echo DUP_PRO_U::esc_html__("Archive ") . $key; ?></a></td>
                    <td><?php echo esc_html($value); ?></td>
                </tr>                
                <?php
            } ?>  
            <tr>
                <td class='dpro-settings-diag-header' colspan="2"><?php DUP_PRO_U::esc_html_e("URLs info"); ?></td>
            </tr>
            <?php
            $urls = DUP_PRO_Archive::getOriginalUrls();
            foreach ($urls as $key => $value) {
                ?>
                <tr>
                    <td><?php echo DUP_PRO_U::esc_html__("URL ") . $key; ?></a></td>
                    <td><?php echo esc_html($value); ?></td>
                </tr>                
                <?php
            } ?>
            <?php if ($space >= 0) : ?> 
                <tr>
                    <td class='dpro-settings-diag-header' colspan="2"><?php DUP_PRO_U::esc_html_e("Server Disk"); ?></td>
                </tr>
                <tr valign="top">
                    <td><?php DUP_PRO_U::esc_html_e('Free space'); ?></td>
                    <td>
                        <?php echo $perc; ?>% -- <?php echo esc_html(DUP_PRO_U::byteSize($space_free)); ?> from <?php echo esc_html(DUP_PRO_U::byteSize($space)); ?>
                        <br/>
                        <small>
                            <?php DUP_PRO_U::esc_html_e("Note: This value is the physical servers hard-drive allocation."); ?> <br/>
                            <?php DUP_PRO_U::esc_html_e("On shared hosts check your control panel for the 'TRUE' disk space quota value."); ?>
                        </small>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
        <br/>
    </div>
</div>
<br/>