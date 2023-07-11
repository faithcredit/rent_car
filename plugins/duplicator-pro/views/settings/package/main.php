<?php

/* @var $global DUP_PRO_Global_Entity */

defined("ABSPATH") or die("");
?>

<style>    
    input#package_mysqldump_path_found {margin-top:5px}
    div.dup-feature-found {padding:0; color: green; display: inline-block;}
    div.dup-feature-notfound {padding:5px; color: maroon; width:600px;}
    select#package_ui_created {font-family: monospace}
    input#_package_mysqldump_path {width:500px}
    #dpro-ziparchive-mode-st, #dpro-ziparchive-mode-mt {height: 28px; padding-top:5px; display: none}
    div.engine-radio {float: left; min-width: 100px}
    div.engine-radio-disabled {}
    div.engine-sub-opts {padding-top:10px}
    div.engine-sub-opts fieldset {
        border: 1px solid #999;
        padding: 15px ;
        line-height: 30px;
    }
    div.engine-sub-opts label {
        display: inline-block;
        min-width: 100px;
        margin-bottom: 5px;
        line-height: 30px !important;
    }
    div.engine-sub-opts input:not([type=checkbox]):not([type=radio]):not([type=button]),
    div.engine-sub-opts select {
        box-sizing: border-box;
        min-width: 150px;
    }
    div#engine-details-match-message {display:none; margin: -5px 0 20px 220px; border: 1px solid silver; padding:5px 8px 5px 8px; background: #dfdfdf; border-radius: 5px; width:650px}

    table#archive-build-schedule {display:none}
    span#archive-build-schedule-icon {display:none}

    form.dup-settings-pack-basic table.form-table {margin-bottom:50px}
</style>



<script>
    DupPro.Settings.Brand = new Object();
</script>
