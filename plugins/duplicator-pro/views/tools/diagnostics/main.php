<?php
defined("ABSPATH") or die("");
wp_enqueue_script('dup-handlebars');

?>

<style>
<?php echo isset($css_hide_msg) ? $css_hide_msg : ''; ?>
    div#message {margin:0px 0px 10px 0px}
    td.dpro-settings-diag-header {background-color:#D8D8D8; font-weight: bold; border-style: none; color:black}
    table.widefat th {font-weight:bold; }
    table.widefat td {padding:2px 2px 2px 8px; }
    table.widefat td:nth-child(1) {width:10px;}
    table.widefat td:nth-child(2) {padding-left: 20px; width:100% !important}
    textarea.dup-opts-read {width:100%; height:40px; font-size:12px}
    .dpro-store-fixed-btn {
        min-width: 165px; 
        text-align: center
    }
    div.success,span.success {color:#4A8254}
    div.failed {color:red}
    table.dpro-reset-opts td:first-child {font-weight: bold}
    table.dpro-reset-opts td {padding:4px}
    div#dpro-tools-delete-moreinfo {display: none; padding: 5px 0 0 20px; border:1px solid #dfdfdf;  border-radius: 5px; padding:10px; margin:5px; width:98% }
    div#dpro-tools-delete-orphans-moreinfo {display: none; padding: 5px 0 0 20px; border:1px solid #dfdfdf;  border-radius: 5px; padding:10px; margin:5px; width:98% }

    /*PHP_INFO*/
    div#dpro-phpinfo {padding:10px 5px;}
    div#dpro-phpinfo table {padding:1px; background:#dfdfdf; -webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px; width:100% !important; box-shadow:0 8px 6px -6px #777;}
    div#dpro-phpinfo td, th {padding:3px; background:#fff; -webkit-border-radius:2px;-moz-border-radius:2px;border-radius:2px;}
    div#dpro-phpinfo tr.h img {display:none;}
    div#dpro-phpinfo tr.h td {background:none;}
    div#dpro-phpinfo tr.h th {text-align:center; background-color:#efefef;}
    div#dpro-phpinfo td.e {font-weight:bold}
</style>
