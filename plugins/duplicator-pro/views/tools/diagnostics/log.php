<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\CapMng;

$trace_log_filepath = DUP_PRO_Log::getTraceFilepath();
$trace_filename     = basename($trace_log_filepath);
$logs               = ToolsPageController::getLogsList();
$global             = DUP_PRO_Global_Entity::getInstance();
$logname            = (isset($_GET['logname'])) ? trim($_GET['logname']) : "";
$refresh            = (isset($_POST['refresh']) && $_POST['refresh'] == 1) ? 1 : 0;
$auto               = (isset($_POST['auto']) && $_POST['auto'] == 1) ? 1 : 0;

//Check for invalid file
if (isset($_GET['logname'])) {
    $validFiles = array_map('basename', $logs);
    if (validate_file($logname, $validFiles) > 0) {
        unset($logname);
    }
    unset($validFiles);
}

if (!isset($logname) || !$logname) {
    $logname = (count($logs) > 0) ? basename($logs[0]) : "";
}

$nocache     = @date("ymdHis");
$logurl      = DUPLICATOR_PRO_SSDIR_URL . "/{$logname}?{$nocache}";
$logurl_base = DUPLICATOR_PRO_SSDIR_URL . "/{$logname}";
$logfound    = (strlen($logname) > 0) ? true : false;
?>
<style>
    span#dup-refresh-count {display:inline;}
    table#dpro-log-pnls {width:100%;}

    td#dpro-log-pnl-left {width:80%; vertical-align: top}
    td#dpro-log-pnl-left div.name {float:left; margin: 0px 0px 5px 5px; font-weight: bold}
    td#dpro-log-pnl-left div.opts {float:right;}
    td#dpro-log-pnl-right {vertical-align: top; padding:5px 0 0 15px; max-width: 375px;}
    iframe#dpro-log-content {padding:5px; background: #fff; min-height:500px; width:99%; border:1px solid silver}

    /* OPTIONS */
    div.dpro-opts-items {border:1px solid silver; background: #efefef; padding: 5px; border-radius: 4px; margin:2px 0px 10px -2px; }
    div.dpro-log-hdr {font-weight: bold; font-size:16px; padding:2px; }
    div.dpro-log-hdr small{font-weight:normal; font-style: italic}
    div.dpro-log-file-list {font-family:monospace;line-height: 24px}
    div.dpro-log-file-list a span{display: inline-block; white-space: nowrap; text-overflow: ellipsis; max-width: 375px; line-height:20px; overflow:hidden}
    div.dpro-log-file-list span {color:green}
    div.dup-opts-items {border:1px solid silver; background: #efefef; padding: 5px; border-radius: 4px; margin:2px 0px 10px -2px;}
    label#dup-auto-refresh-lbl {display: inline-block;}
    div#dpro-monitor-trace-area {bottom:70px}
</style>

<script>
jQuery(document).ready(function ($)
{

    DupPro.Tools.FullLog = function () {
        var $panelL = $('#dpro-log-pnl-left');
        var $panelR = $('#dpro-log-pnl-right');

        if ($panelR.is(":visible")) {
            $panelR.hide(400);
            $panelL.css({width: '100%'});
        } else {
            $panelR.show(200);
            $panelL.css({width: '75%'});
        }
    }

    DupPro.Tools.Refresh = function () {
        $('#refresh').val(1);
        $('#dup-form-logs').submit();
    }

    DupPro.Tools.RefreshAuto = function () {
        if ($("#dup-auto-refresh").is(":checked")) {
            $('#auto').val(1);
            startTimer();
        } else {
            $('#auto').val(0);
        }
    }

    DupPro.Tools.WinResize = function () {
        var height = $(window).height() - 210;
        $("#dpro-log-content").css({height: height + 'px'});
    }

    var duration = 9;
    var count = duration;
    var timerInterval;
    function timer() {
        count = count - 1;
        $("#dup-refresh-count").html(count.toString());
        if (!$("#dup-auto-refresh").is(":checked")) {
            clearInterval(timerInterval);
            $("#dup-refresh-count").text(count.toString().trim());
            return;
        }

        if (count <= 0) {
            count = duration + 1;
            DupPro.Tools.Refresh();
        }
    }

    function startTimer() {
        timerInterval = setInterval(timer, 1000);
    }

    //INIT Events
    $(window).resize(DupPro.Tools.WinResize);
    $('#dup-options').click(DupPro.Tools.FullLog);
    $("#dup-refresh").click(DupPro.Tools.Refresh);
    $("#dup-auto-refresh").click(DupPro.Tools.RefreshAuto);
    $("#dup-refresh-count").html(duration.toString());

    //INIT
    DupPro.Tools.WinResize();

    <?php if ($refresh) : ?>
            //Scroll to Bottom
            $('#dpro-log-content').on('load', function() {
                var $contents = $('#dpro-log-content').contents();
                $contents.scrollTop($contents.height());
            });
        <?php if ($auto) : ?>
            $("#dup-auto-refresh").prop('checked', true);
            DupPro.Tools.RefreshAuto();
        <?php endif; ?>
    <?php endif; ?>
        
    // formatting log 
    $('#dpro-log-content').on('load', function() {
        $('#dpro-log-content').contents().find("head")
          .append($("<style type='text/css'>pre {line-height: 2;white-space: pre;}</style>"));
    });
});
</script>

<form id="dup-form-logs" method="post" action="">
<input type="hidden" id="refresh" name="refresh" value="<?php echo ($refresh) ? 1 : 0 ?>" />
<input type="hidden" id="auto" name="auto" value="<?php echo ($auto) ? 1 : 0 ?>" />

<?php if (!$logfound) : ?>
    <div style="padding:20px">
        <h2><?php DUP_PRO_U::esc_html_e("Log file not found or unreadable") ?>.</h2>
          <?php DUP_PRO_U::esc_html_e("Try to create a package, since no log files were found in the snapshots directory ending in *_log.txt") ?>.<br/><br/>
          <?php DUP_PRO_U::esc_html_e("Reasons for log file not showing") ?>: <br/>
        - <?php DUP_PRO_U::esc_html_e("The web server does not support returning .txt file extensions") ?>. <br/>
        - <?php DUP_PRO_U::esc_html_e("The snapshots directory does not have the correct permissions to write files.  Try setting the permissions to 755") ?>. <br/>
        - <?php DUP_PRO_U::esc_html_e("The process that PHP runs under does not have enough permissions to create files.  Please contact your hosting provider for more details") ?>. <br/>
    </div>
<?php else : ?>
    <table id="dpro-log-pnls">
        <tr>
            <td id="dpro-log-pnl-left">
                <div class="name"><i class="fas fa-file-contract fa-fw"></i> <?php echo basename($logurl); ?></div>
                <div class="opts"><a href="javascript:void(0)" id="dup-options"><?php DUP_PRO_U::esc_html_e("Options") ?> <i class="fa fa-angle-double-right"></i></a> &nbsp;</div>
                <br style="clear:both" />
                <iframe id="dpro-log-content" src="<?php echo esc_url($logurl); ?>" ></iframe>
            </td>
            <td id="dpro-log-pnl-right">
                <h2><?php DUP_PRO_U::esc_html_e("Options") ?></h2>

                <div class="dpro-opts-items">
                    <input type="button" class="button button-small" id="dup-refresh" value="<?php DUP_PRO_U::esc_attr_e("Refresh") ?>" /> &nbsp;
                    <div style="display:inline-block;margin-top:1px;">
                        <input type='checkbox' id="dup-auto-refresh" style="margin-top:3px" />
                        <label id="dup-auto-refresh-lbl" for="dup-auto-refresh">
                        <?php DUP_PRO_U::esc_html_e("Auto Refresh") ?>  [<span id="dup-refresh-count"></span>]
                        </label>
                    </div>
                </div>

                <div class="dpro-log-hdr">
                    <?php DUP_PRO_U::esc_html_e('Trace Log:') ?> &nbsp;
                    <span style="font-size:11px; font-weight: normal">
                        <?php
                        $trace_on        = get_option('duplicator_pro_trace_log_enabled', false);
                        $txt_clear_trace = DUP_PRO_U::esc_html__('Clear');
                        $txt_profile     = '';
                        $html            = "";

                        if (CapMng::can(CapMng::CAP_SETTINGS, false)) {
                            if (!$trace_on) {
                                $url  = wp_nonce_url('admin.php?page=duplicator-pro-settings&_logging_mode=on&action=trace', 'duppro-settings-general-edit', '_wpnonce');
                                $html = '<a href="' . esc_url($url) . '" target="_blank">' . DUP_PRO_U::__("Turn On") . $txt_profile . '</a>';
                            } else {
                                $url  = wp_nonce_url('admin.php?page=duplicator-pro-settings&_logging_mode=off&action=trace', 'duppro-settings-general-edit', '_wpnonce');
                                $html = '<a href="' . esc_url($url) . '" target="_blank">' . DUP_PRO_U::__("Turn Off") . $txt_profile . '</a>';
                            }
                            $html .= " | ";
                        }
                        if (CapMng::can(CapMng::CAP_CREATE, false)) {
                            $html .= "<a href='javascript:void(0)' onclick='DupPro.UI.ClearTraceLog(1);'>{$txt_clear_trace}</a>";
                        }

                        echo $html;
                        ?>
                    </span>
                </div>
                <div class="dpro-log-file-list">
                    <?php
                    $trace_log_filepath = DUP_PRO_Log::getTraceFilepath();
                    if (file_exists($trace_log_filepath)) {
                        $time = date('m/d/y h:i:s', @filemtime($trace_log_filepath));
                    } else {
                        $time = DUP_PRO_U::__('No trace log found');
                    }
                    $active_filename = basename($logurl_base);
                    $trace_log_url   = ControllersManager::getMenuLink(ControllersManager::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_DISAGNOSTIC, ToolsPageController::L3_SLUG_DISAGNOSTIC_LOG, array('logname' => $trace_filename));
                    $is_trace_active = ($active_filename == $trace_filename);

                    echo ($is_trace_active)
                        ? '<div class="dpro-trace-log-link-green"><i class="fa fa-caret-right"></i> ' . $time . '</div>'
                        : '<a href="' . esc_url($trace_log_url) . '">' . $time . '</a>';
                    ?>
                </div>

                <br/>
                <div class="dpro-log-hdr">
                    <?php DUP_PRO_U::esc_html_e('Package Logs');  ?>
                    <small><?php DUP_PRO_U::esc_html_e('Top 20');  ?></small>
                </div>

                <div class="dpro-log-file-list" style="white-space: nowrap">
                    <?php
                    $count  = 0;
                    $active = basename($logurl_base);
                    foreach ($logs as $log) {
                        $time = date('m/d/y h:i:s', filemtime($log));
                        $name = sanitize_text_field(basename($log));
                        $url  = ControllersManager::getMenuLink(ControllersManager::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_DISAGNOSTIC, ToolsPageController::L3_SLUG_DISAGNOSTIC_LOG, array('logname' => $name));
                        if ($name !== $trace_filename) {
                            $shortname = substr($name, 0, 15) . '***.log';
                            echo ($active == $name)
                                ? '<span title="' . esc_attr($name) . '"><i class="fa fa-caret-right"></i> ' . $time . '-' . $shortname . '</span><br/>'
                                : '<a href="' . esc_url($url) . '" title="' . esc_attr($name) . '">' . $time . '-' . $shortname . '</a><br/>';
                            if ($count > 20) {
                                break;
                            }
                            $count++;
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
    </table>
<?php endif; ?>
</form>
