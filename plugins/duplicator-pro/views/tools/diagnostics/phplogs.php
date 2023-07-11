<?php
defined("ABSPATH") or die("");

if (isset($_POST['clear_log']) && $_POST['clear_log'] == 'true') {
    DUP_PRO_PHP_Log::clear_log();
}

$refresh = (isset($_POST['refresh']) && $_POST['refresh'] == 1) ? 1 : 0;
$auto    = (isset($_POST['auto']) && $_POST['auto'] == 1) ? 1 : 0;
$filter  = (isset($_POST['filter'])) ? $_POST['filter'] : '';

$error = false;

$lines = 200;

$log_path  = DUP_PRO_PHP_Log::get_path(null, true);
$error_log = DUP_PRO_PHP_Log::get_log($lines, "M d, H:i:s");

$log_path_size = 0;
if ($log_path !== false) {
    $log_path_size = @filesize($log_path);

    if (!is_readable($log_path)) {
        $error = sprintf(
            DUP_PRO_U::__(
                "PHP error log is available on location %s but is not readable. Try setting the permissions to 755."
            ),
            '<b>' . esc_html($log_path) . '</b>'
        );
    } elseif ($error_log === false) {
        if ($log_path > (PHP_INT_MAX / 2)) {
            $error = sprintf(
                DUP_PRO_U::__(
                    "PHP error log is available on location %s but can't be read because file size is over %s. You must open this file manualy."
                ),
                '<b>' . esc_html($log_path) . '</b>',
                '<b>' . DUP_PRO_U::byteSize($log_path_size) . '</b>'
            );
        } else {
            $error = sprintf(
                DUP_PRO_U::__(
                    "PHP error log is available on location %s but can't be read because some unexpected error. Try to open file manually and investigate all problems what can cause this error."
                ),
                '<b>' . esc_html($log_path) . '</b>'
            );
        }
    } else {
    }
} else {
    $error = DUP_PRO_U::__('This can be good for you because there is no errors.') . '<br><br>';

    $error .= sprintf(
        DUP_PRO_U::__(
            'But if you in any case experience some errors and not see log here, ' .
            'that mean your error log file is placed on some unusual location or can\'t be created because some %1$s setup. ' .
            'In that case you must open %2$s file and define %3$s or call your system administrator to setup it properly.'
        ),
        "<b><i>php.ini</i></b>",
        "<b><i>php.ini</i></b>",
        "<code>error_log</code>"
    ) .
             '<br><br>';

    $error .= sprintf(
        DUP_PRO_U::__('It would be great if you define new error log path to be inside root of your WordPress installation ( %1$s ) and name of file to be %2$s. That will solve this problem.'),
        '<i><b>' . duplicator_pro_get_home_path() . '</b></i>',
        '<b>error.log</b>'
    );
}


if ($error) : ?>
    <h2><?php
    if ($log_path !== false) {
        DUP_PRO_U::esc_html_e("Log file is found but have error or is unreadable");
    } else {
        DUP_PRO_U::esc_html_e("PHP error log not found");
    }
    ?></h2>
    <?php echo $error; ?>
<?php else : ?>
<style>
    span#dup-refresh-count {display:inline;}
    table#dpro-log-pnls {width:100%;}
    td#dpro-log-pnl-left div.opts {float:right;}

    td#dpro-log-pnl-left {width:80%; vertical-align: top}
    td#dpro-log-pnl-right {vertical-align: top; padding:5px 0 0 15px; max-width: 375px;}

    td#dpro-log-pnl-left div.name{float:left;margin:0 0 5px 5px;font-weight:700}
    #error-log{width:100%;border:none;table-layout:fixed;border-spacing:0}
    #error-log td,#error-log th{padding:8px 10px;border:none}
    #error-log th{border-bottom:1px solid #e1e1e1;font-weight:600;text-align:center;color:#000}
    #error-log th:last-child{padding-right:2.1%}
    #error-log td{text-align:left;vertical-align:top}
    div.tableContainer{clear:both;border:1px solid #ccc;height:500px;overflow:auto;width:100%;padding-bottom:35px;background:#fff}
    
    /* Reset overflow value to hidden for all non-IE browsers. */
    html>body div.tableContainer {overflow: hidden; width: 100%;}

    div.tableContainer #error-log {float: left;}

    #error-log tr{width:100%;}

    #error-log thead tr {position: relative;}

    #error-log tbody {display: block; height: 500px; overflow: auto; width: 100%}

    html>body  #error-log thead {display: table; overflow: auto; width: 100%}

    #error-log td ul{padding:10px 15px; background: rgba(200, 200, 200, 0.1); color:#000; box-shadow:#ccc 0px 0px 1px; -webkit-box-shadow:#ccc 0px 0px 1px; -ms-box-shadow:#ccc 0px 0px 1px; -o-box-shadow:#ccc 0px 0px 1px; }
    #error-log td ul li+li{margin-top:15px;}
    #error-log td ul li.title{font-weight: bold;}

    .info{display: inline-table; border-bottom:1px dotted #ccc; cursor:pointer;}

    div.dpro-opts-items {border:1px solid silver; background: #efefef; padding: 5px; border-radius: 4px; margin:2px 0px 10px -2px; }
    div.dpro-log-hdr {font-weight: bold; font-size:16px; padding:2px; }
    div.dpro-log-hdr small{font-weight:normal; font-style: italic}
</style>
<table id="dpro-log-pnls">
        <tr>
            <td id="dpro-log-pnl-left">
                <div class="name"><i class="fas fa-file-contract fa-fw"></i> <?php echo esc_html($log_path); ?></div>
                <div class="opts"><a href="javascript:void(0)" id="dup-options"><?php DUP_PRO_U::esc_html_e('Options'); ?> <i class="fa fa-angle-double-right"></i></a> &nbsp;</div>
                <div id="tableContainer" class="tableContainer">
                    <table class="wp-list-table fixed striped" id="error-log">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 10%; text-align: center"><?php DUP_PRO_U::esc_html_e('Date'); ?></th>
                                <th scope="col" style="width: 8%; text-align: center"><?php DUP_PRO_U::esc_html_e('Type'); ?></th>
                                <th scope="col" style="width: 34%;"><?php DUP_PRO_U::esc_html_e('Error'); ?></th>
                                <th scope="col" style="width: 30%;"><?php DUP_PRO_U::esc_html_e('File'); ?></th>
                                <th scope="col" style="width: 6%;"><?php DUP_PRO_U::esc_html_e('Line'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="the-list"<?php echo (count($error_log) === 0) ? ' style="overflow: hidden;"' : ''; ?>>
                            <?php if (count($error_log) === 0) : ?>
                                <tr style="width:100%; display:table">
                                    <td colspan="5" style="width:100%; display:table"><h3><?php DUP_PRO_U::esc_html_e('PHP Error Log is empty.'); ?></h3></td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($error_log as $line => $log) : ?>
                                <tr>
                                    <td scope="col" style="width: 15%; text-align: center">
                                        <b class="info" title="<?php echo date("Y-m-d H:i:s T (P)", strtotime($log['dateTime'])); ?>">
                                            <?php echo esc_html($log['dateTime']); ?>
                                        </b>
                                    </td>
                                    <td scope="col" style="width: 8%; text-align: center"><?php echo esc_html($log['type']); ?></td>
                                    <td scope="col" style="width: 35.5%;">
                                        <?php echo esc_html($log['message']); ?>
                                        <?php if (count($log['stackTrace']) > 0) : ?>
                                        <ul>
                                            <li class="title"><?php DUP_PRO_U::esc_html_e('Stack trace:'); ?></li>
                                            <?php foreach ($log['stackTrace'] as $i => $trace) : ?>
                                            <li><b>#<?php echo esc_html($i); ?></b> <?php echo esc_html($trace); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </td>
                                    <td scope="col" style="width: 35.5%;"><?php echo esc_html($log['file']); ?></td>
                                    <td scope="col" style="width: 6%; text-align: center"><?php echo esc_html($log['line']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </td>
            <td id="dpro-log-pnl-right">
                <h2><?php DUP_PRO_U::esc_html_e("Options") ?></h2>
                <form id="dup-form-logs" method="post" action="">
                    
                    <div class="dpro-opts-items">
                        <strong><?php DUP_PRO_U::esc_html_e('PHP Error Filter:'); ?></strong>
                        <select type="text" id="filter" name="filter" style="width:100%;">
                            <option value="">--- <?php DUP_PRO_U::esc_html_e('None'); ?> ---</option>
                            <?php
                            foreach (
                                array(
                                    'WARNING' => DUP_PRO_U::__('Warnings'),
                                    'NOTICE' => DUP_PRO_U::__('Notices'),
                                    'FATAL' => DUP_PRO_U::__('Fatal Error'),
                                    'SYNTAX' => DUP_PRO_U::__('Syntax Error'),
                                    'EXCEPTION' => DUP_PRO_U::__('Exceptions'),
                                ) as $option => $name
                            ) :
                                ?>
                            <option value="<?php echo esc_attr($option); ?>"<?php echo ($filter == $option ? ' selected' : ''); ?>><?php echo esc_html($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <hr>
                        <input type="button" class="button button-small" id="dup-refresh" value="<?php DUP_PRO_U::esc_attr_e("Refresh") ?>" /> &nbsp;
                        <div style="display:inline-block;margin-top:1px;">
                            <input type='checkbox' id="dup-auto-refresh" style="margin-top:3px" />
                            <label id="dup-auto-refresh-lbl" for="dup-auto-refresh">
                            <?php DUP_PRO_U::esc_html_e("Auto Refresh") ?>  [<span id="dup-refresh-count"></span>]
                            </label>
                        </div>
                    </div>
                    <input type="hidden" id="refresh" name="refresh" value="<?php echo ($refresh) ? 1 : 0 ?>" />
                    <input type="hidden" id="auto" name="auto" value="<?php echo ($auto) ? 1 : 0 ?>" />
                </form>
                <div class="dpro-log-file-list">
                    <div style="color:green"><i class="fa fa-caret-right"></i> <?php
                        echo DUP_PRO_PHP_Log::get_filename($log_path);
                        echo ' (', DUP_PRO_U::byteSize($log_path_size),') &nbsp;|&nbsp; ';
                        echo date("Y-m-d H:i:s", filemtime($log_path));
                    ?></div>
                </div>
                <?php if (isset($line) && $line + 30 > $lines) : ?>
                <br>
                <div style="color:#cc0000">
                    <i class="fa fa-info-circle"></i> <?php printf(DUP_PRO_U::__("You see only last %s logs inside %s file."), $line, esc_html(DUP_PRO_PHP_Log::get_filename($log_path))); ?>
                </div>
                <?php endif; ?>
                <?php if (isset($line)) : ?>
                <br>
                <form id="dup-form-clear-log" method="post" action="">
                    <button class="button" type="button" onclick="return DupPro.Tools.ClearLog();"><?php DUP_PRO_U::esc_html_e('Clear Log'); ?></button>
                    <input type="hidden" id="clear_log" name="clear_log" value="true" />
                </form>
                <?php endif; ?>
            </td>
        </tr>
</table>
    <?php
    $confirm1               = new DUP_PRO_UI_Dialog();
    $confirm1->title        = DUP_PRO_U::__('Clear PHP Log?');
    $confirm1->message      = DUP_PRO_U::__('Are you sure you want to clear PHP log??');
    $confirm1->message     .= '<br/>';
    $confirm1->message     .= DUP_PRO_U::__('<small><i>Note: This action will delete all data and can\'t be stopped.</i></small>');
    $confirm1->progressText = DUP_PRO_U::__('Clear PHP log, Please Wait...');
    $confirm1->jsCallback   = 'DupPro.Tools.ClearLogSubmit()';
    $confirm1->initConfirm();
    ?>
<script>
jQuery(document).ready(function ($)
{
    var duration = 9,
        count = duration,
        timerInterval;
        
    DupPro.Tools.errorFilter = function() {
        // Declare variables
        var input, filter, table, tr, td, i;
            input = $("#filter");
            filter = input.val().toUpperCase();
            table = $("#error-log");
            tr = table.find("tr");
        
        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[1];
            if (td) {
                if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "block";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    
    
    $("#dup-refresh-count").html(duration);

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

    DupPro.Tools.ClearLogSubmit = function() {
        $('#dup-form-clear-log').submit();
    }

    DupPro.Tools.ClearLog = function() {
        <?php $confirm1->showConfirm(); ?>
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

    /* TABLE SIZE */
    DupPro.Tools.TableSize = function() {
        var size = [],
            offset = ($('#tableContainer').width() - $($('#error-log tbody tr').get(0)).width()) / ($('#error-log th').length);

        $('#error-log th').each(function(i,$this) {
            size[i] = $($this).width();
        });

        $('#error-log tr').each(function(x,$tr) {
            $($tr).find('td').each(function(i,$this) {
                $($this).width(size[i]+offset);
            });
        });
    };

    DupPro.Tools.BoxHeight = function() {
        var position = $('#tableContainer').position(),
            winHeight = $(window).height(),
            height = (winHeight - position.top - $("#wpfooter").height()) - 55;
        if(height >= 500) {
            $('#error-log tbody, div.tableContainer').height(height);
        }
    };

    <?php if (count($error_log) > 0) : ?>
    DupPro.Tools.TableSize();
    DupPro.Tools.BoxHeight();
    <?php endif; ?>

    $(window).resize(function() {
        <?php if (count($error_log) > 0) : ?>
        DupPro.Tools.TableSize();
        DupPro.Tools.BoxHeight();
        <?php endif; ?>
    });

    $('#dup-options').click(function() {
            DupPro.Tools.FullLog();
            DupPro.Tools.TableSize();
    });
    $("#dup-refresh").click(DupPro.Tools.Refresh);
    $("#dup-auto-refresh").click(DupPro.Tools.RefreshAuto);
    
    $("#filter").on('input change select', function(){
        DupPro.Tools.errorFilter();
    });

    DupPro.Tools.errorFilter();
    <?php if ($auto) : ?>
        $("#dup-auto-refresh").prop('checked', true);
        DupPro.Tools.RefreshAuto();
    <?php endif; ?>
});
</script>
<?php endif;
