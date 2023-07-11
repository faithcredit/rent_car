<?php
defined("ABSPATH") or die("");

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapJson;

require_once(DUPLICATOR____PATH . '/classes/package/class.pack.php');

wp_enqueue_script('dup-pro-handlebars');

if (empty($_POST)) {
    // Refresh 'fix'
    $redirect = self_admin_url('admin.php?page=duplicator-pro&tab=packages&inner_page=new1&_wpnonce=' . wp_create_nonce('new1-package'));
    die("<script>window.location.href='{$redirect}';</script>");
}

$global = DUP_PRO_Global_Entity::getInstance();

//echo '<pre>', var_export($_REQUEST, true), '</pre>';

if (!empty($_REQUEST['action']) && $_REQUEST['action'] === 'template-create') {
    $storage_ids = isset($_REQUEST['_storage_ids']) ? $_REQUEST['_storage_ids'] : array();
    $template_id = (int) $_REQUEST['template_id'];
    $template    = DUP_PRO_Package_Template_Entity::getById($template_id);

    // always set the manual template since it represents the last thing that was run
    DUP_PRO_Package::set_manual_template_from_post($_REQUEST);

    $global->manual_mode_storage_ids = $storage_ids;
    $global->save();

    $name_chars = array(".", "-");
    $name       = ( isset($_REQUEST['package-name']) && !empty($_REQUEST['package-name'])) ? $_REQUEST['package-name'] : DUP_PRO_Package::get_default_name();
    $name       = substr(sanitize_file_name($name), 0, 40);
    $name       = str_replace($name_chars, '', $name);

    DUP_PRO_Package::set_temporary_package_from_template_and_storages($template_id, $storage_ids, $name);
}

$Package               = DUP_PRO_Package::get_temporary_package();
$package_list_url      = ControllersManager::getMenuLink(ControllersManager::PACKAGES_SUBMENU_SLUG);
$archive_export_onlydb = isset($_POST['export-onlydb']) ? 1 : 0;
$messageText           = DUP_PRO_Web_Services::getScanErrorMessage();

/** @var bool */
$blur = TplMng::getInstance()->getGlobalValue('blurCreate');
?>

<style>
    /*PROGRESS-BAR - RESULTS - ERROR */
    form#form-duplicator {text-align:center; max-width:825px; min-height:200px; margin:0px auto 0px auto; padding:0px;}
    div.dup-progress-title {font-size:22px; padding:5px 0 20px 0; font-weight:bold}
    div#dup-msg-success {padding:0 5px 5px 5px; text-align:left}
    div#dup-msg-success div.details {padding:10px 15px 10px 15px; margin:5px 0 15px 0; background:#fff; border-radius:4px; border:1px solid #ddd;box-shadow:0 8px 6px -6px #999; }
    div#dup-msg-success div.details-title {font-size:20px; border-bottom:1px solid #dfdfdf; padding:5px; margin:0 0 10px 0; font-weight:bold}
    div#dup-msg-success-subtitle {color:#999; margin:0; font-size:11px}
    div.dup-scan-filter-status {display:inline; font-size:11px; margin-right:10px; color:#630f0f;}
    div#dup-msg-error {color:#A62426; padding:5px; max-width:790px;}
    div#dup-msg-error-response-text { max-height:500px; overflow-y:scroll; border:1px solid silver; border-radius:2px; padding:10px;background:#fff}
    div.dup-hdr-error-details {text-align:left; margin:20px 0}
    i[data-tooltip].fa-question-circle {color:#555}

    /*SCAN ITEMS: Sections */
    sup.dup-small-ext-type {font-size:11px; font-weight: normal; font-style: italic}
    div.scan-header { font-size:16px; padding:7px 5px 7px 7px; font-weight:bold; background-color:#E0E0E0; border-bottom:0px solid #C0C0C0 }
    div.scan-header-details {float:right; margin-top:-5px}
    div.scan-item {border:1px solid #E0E0E0; border-top:none;}
    div.scan-item-first {border-top:1px solid #E0E0E0}
    div.scan-item div.title {background-color:#F1F1F1; width:100%; padding:4px 0 4px 0; cursor:pointer; height:20px;}
    @media screen and (min-width: 1280px) {
        div.scan-item div.title {padding:10px 0 10px 0;}
    }
    div.scan-item div.title:hover {background-color:#ECECEC;}
    div.scan-item div.text {font-weight:bold; font-size:14px; float:left;  position:relative; left:10px}
    div.scan-item div.info {display:none; padding:10px; background:#fff}
    div.scan-good {display:inline-block; color:green;font-weight:bold;}
    div.scan-warn {display:inline-block; color:#630f0f;font-weight:bold;}
    div.dup-more-details {float:right; font-size:14px}
    div.dup-more-details:hover {color:#777; cursor:pointer}
    div.dup-more-details a {color:#222}
    div.dup-more-details a:hover {color:#777}
    i.scan-warn {color:#630f0f;}
    div#scan-unreadable-items div.directory {font-size:11px}
    div.subsite-filter-info {margin-left: 15px;}
    div.subsite-filter-info ol {margin-top: 0px;}


    /*DIALOG WINDOWS*/
    div#arc-details-dlg {font-size:12px}
    div#arc-details-dlg h2 {margin:0; padding:0 0 5px 0; border-bottom:1px solid #dfdfdf;}
    div#arc-details-dlg hr {margin:3px 0 10px 0}
    div#arc-details-dlg div.filter-area {height:230px; overflow-y:scroll; border:1px solid #dfdfdf; padding:8px; margin:2px 0}
    div#arc-details-dlg div.file-info {padding:0 0 10px 15px; width:500px; white-space:nowrap;}
    div#arc-details-dlg div.info{line-height:18px}
    div#arc-details-dlg div.info label{font-size:12px !important; font-weight: bold; display: inline-block; min-width: 100px}

    div#arc-paths-dlg textarea.path-dirs,
    textarea.path-files {font-size:12px; border: 1px solid silver; padding: 10px; background: #fff; margin:5px; height:125px; width:100%; white-space:pre}
    div#arc-paths-dlg div.copy-button {float:right;}
    div#arc-paths-dlg div.copy-button button {font-size:12px}

    /*FILES */
    div#data-arc-size1 {display:inline-block; font-size:11px; margin-right:1px;}
    i.data-size-help { font-size:12px; display:inline-block;  margin:0; padding:0}
    div.dup-data-size-uncompressed {font-size:10px; text-align: right; padding:0; margin:-7px 0 0 0; font-style: italic; font-weight: normal; border:0px solid red; clear:both}
    div.hb-files-style > div.container {border:1px solid #E0E0E0; border-radius:2px; margin:5px 0 10px 0}
    div.hb-files-style > div.container b {font-weight:bold}
    div.hb-files-style > div.container div.divider {margin-bottom:2px; font-weight:bold}
    div.hb-files-style div.data {line-height:21px; min-height:100px; max-height:70vh; overflow-y:scroll; }
    div.hb-files-style div.data-padded {padding:10px }
    div.hb-files-style div.hdrs {background:#fff; padding:2px 4px 4px 6px; border-bottom:1px solid #E0E0E0; font-weight:bold}
    div.hb-files-style div.hdrs sup i.fa {font-size:11px}
    div.hb-files-style div.hdrs-up-down {float:right;  margin:2px 12px 0 0}
    div.hb-files-style i.dup-nav-toggle:hover {cursor:pointer; color:#999}
    div.hb-files-style div.directory {margin-left:12px; white-space: nowrap;}
    div.hb-files-style div.directory i.size {font-size:11px;  font-style:normal; display:inline-block; min-width:50px}
    div.hb-files-style div.directory i.count {font-size:11px; font-style:normal; display:inline-block; min-width:20px}
    div.hb-files-style div.directory i.empty {width:15px; display:inline-block}
    div.hb-files-style div.directory i.dup-nav {cursor:pointer}
    div.hb-files-style div.directory i.fa {width:8px}
    div.hb-files-style div.directory i.chk-off {width:20px; color:#777; cursor: help; margin:0; font-size:1.25em}
    div.hb-files-style div.directory label {font-weight:bold; cursor:pointer; vertical-align:top;}
    div.hb-files-style div.directory label:hover {color:#025d02}
    div.hb-files-style div.files {padding:2px 0 0 35px; font-size:12px; display:none; line-height:18px}
    div.hb-files-style div.files i.size {font-style:normal; display:inline-block; min-width:50px}
    div.hb-files-style div.files label {font-weight: normal; font-size:11px; vertical-align:top;display:inline-block;width:450px; white-space: nowrap; overflow:hidden; text-overflow:ellipsis;}
    div.hb-files-style div.files label:hover {color:#025d02; cursor: pointer}
    div.hb-files-style div.apply-btn {text-align:right; margin: 1px 0 10px 0}
    div.hb-files-style div.apply-warn {float:left; font-size:11px; color:maroon; margin-top:-7px; font-style: italic}


    div#size-more-details {display:none; margin:5px 0 20px 0; border:1px solid #dfdfdf; padding:8px; border-radius:2px; background-color: #F1F1F1}
    div#size-more-details ul {list-style-type:circle; padding-left:20px; margin:0}
    div#size-more-details li {margin:0}

    /*  SERVER-CHECKS */
    div.dup-scan-title {display:inline-block;  padding:1px; font-weight: bold;}
    div.dup-scan-title a {display:inline-block; min-width:200px; padding:3px; }
    div.dup-scan-title a:focus {outline: 1px solid #fff; box-shadow: none}
    div.dup-scan-title div {display:inline-block;  }
    div.dup-scan-info {display:none;}
    div.dup-scan-good {display:inline-block; color:green;font-weight: bold;}
    div.dup-scan-warn {display:inline-block; color:#F0AC00;font-weight: bold;}
    span.dup-toggle {float:left; margin:0 2px 2px 0; }
    div.dup-scan-files-migrae-status {max-height:250px; overflow-y:scroll; border:1px solid silver; padding:0 4px 0 4px; background: #efefef; border-radius:2px}

    /*DATABASE*/
    table.dup-scan-db-details {line-height: 14px; margin:5px 0px 0px 20px;  width:98%}
    table.dup-scan-db-details td {padding:2px;}
    table.dup-scan-db-details td:first-child {font-weight: bold;  white-space: nowrap; width:105px}
    div#dup-scan-db-info {margin-top:5px}
    div#data-db-tablelist {max-height:250px; overflow-y:scroll; border:1px solid silver; padding:8px; background: #efefef; border-radius:2px}
    div#data-db-tablelist td{padding:0 5px 3px 20px; min-width:100px}
    div#data-db-size1 {display: inline-block; font-size:11px; margin-right:1px;}
    /*WARNING-CONTINUE*/
    div#dpro-scan-warning-continue {display:none; text-align:center; padding:0 0 15px 0}
    div#dpro-scan-warning-continue div.msg1 label{font-size:16px; color:#630f0f}
    div#dpro-scan-warning-continue div.msg2 {padding:2px; line-height:13px}
    div#dpro-scan-warning-continue div.msg2 label {font-size:11px !important}
    /*FILES */
    div#dpro-confirm-area {color:maroon; display:none; font-size:14px; line-height:24px; font-weight: bold; margin: -5px 0 10px 0}
    div#dpro-confirm-area label {font-size:14px !important}

    /*Footer*/
    div.dup-button-footer {text-align:center; margin:0}

    /** JSTREE **/
    .jstree .root-node > .jstree-anchor { font-weight: bold; }
    .jstree .info-node > .jstree-anchor { font-weight: bold; }
    .jstree .info-node > .jstree-anchor .jstree-checkbox {display: none;}

    .jstree .warning-node > .jstree-anchor {color: red;}
    .jstree .filtered-node .jstree-anchor {opacity: 0.3; text-decoration: line-through; }
    .jstree .jstree-checkbox-disabled {opacity: 0.3;}

    .dup-tree-section {
        position: relative;
    }

    .tree-nav-bar {
        line-height: 30px;
        background-color: #EFEFEF;
        border-bottom: 1px solid darkgray;
    }

    .tree-nav-bar .container {
        padding: 3px 30px 3px 5px;
    }

    .dup-tree-section .tree-loader {
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,.3);
        top: 0;
        left: 0;
        z-index: 9999;
    }
    .dup-tree-section .tree-loader .container-wrapper {
        position: absolute;
        top: 50%;
        left: 0;
        transform: translateY(-50%);
        color: white;
        width: 100%;
        text-align: center;
        font-size: 14px;
    }

    .jstree .jstree-node > .jstree-anchor { 
        display: inline-block;
        width: calc(100% - 30px);
    }
    .tree-nav-bar .size,
    .tree-nav-bar .nodes,
    .jstree-anchor .size,
    .jstree-anchor .nodes {
        float: right;
        text-align: right;
        display: block;
        min-width: 90px;
        border-left: 1px solid black;
        padding-left: 5px;
        margin-left: 5px;
        box-sizing: border-box;
    }

    .tree-nav-bar .size,
    .tree-nav-bar .nodes {
        border: none;
    }

    .warning-node > .jstree-anchor .size,
    .warning-node > .jstree-anchor .nodes {
        color: #9f2a2a;
        font-weight: bold;
    }

</style>

<?php
$validator = $Package->validateInputs();
if (!$validator->isSuccess()) {
    ?>
    <form id="form-duplicator" method="post" action="<?php echo $package_list_url ?>">
        <!--  ERROR MESSAGE -->
        <div id="dup-msg-error">
            <div class="dup-hdr-error"><i class="fa fa-exclamation-circle"></i> <?php DUP_PRO_U::esc_html_e('Input fields not valid'); ?></div>
            <i><?php DUP_PRO_U::esc_html_e('Please try again!'); ?></i><br/>
            <div style="text-align:left">
                <b><?php DUP_PRO_U::esc_html_e("Server Status:"); ?></b> &nbsp;
                <div id="dup-msg-error-response-status" style="display:inline-block"></div><br/>
                <b><?php DUP_PRO_U::esc_html_e("Error Message:"); ?></b>
                <div id="dup-msg-error-response-text">
                    <ul>
                        <?php
                        $validator->getErrorsFormat("<li>%s</li>");
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        $back_nonce = wp_create_nonce('new1-package');
        ?>
        <input type="button" value="&#9664; <?php DUP_PRO_U::esc_html_e("Back") ?>" onclick="window.location.assign('?page=duplicator-pro&tab=packages&inner_page=new1&_wpnonce=' + '<?php echo $back_nonce; ?>')" class="button button-large" />
    </form>
    <?php
    return;
}
?>


<!-- ====================
TOOL-BAR -->
<table class="dpro-edit-toolbar <?php echo ($blur ? 'dup-mock-blur' : ''); ?>">
    <tr>
        <td>
            <div id="dup-wiz">
                <div id="dup-wiz-steps">
                    <div class="completed-step"><a>1 <?php DUP_PRO_U::esc_html_e('Setup'); ?></a></div>
                    <div class="active-step"><a>2 <?php DUP_PRO_U::esc_html_e('Scan'); ?> </a></div>
                    <div><a>3 <?php DUP_PRO_U::esc_html_e('Build'); ?> </a></div>
                </div>
                <div id="dup-wiz-title" style="white-space: nowrap">
                    <?php DUP_PRO_U::esc_html_e('Step 2: System Scan'); ?>
                </div> 
            </div>  
        </td>
    </tr>
</table>
<hr class="dpro-edit-toolbar-divider"/>


<form 
    id="form-duplicator" 
    class="<?php echo ($blur ? 'dup-mock-blur' : ''); ?>" 
    method="post" 
    action="<?php echo $package_list_url ?>"
>
    <input type="hidden" name="create_from_temp" value="1" />

    <div id="dup-progress-area">

        <!--  PROGRESS BAR -->
        <div id="dup-progress-bar-area">
            <div class="dup-pro-title" >
                <i class="fa fa-circle-notch fa-spin"></i> <?php DUP_PRO_U::esc_html_e('Scanning Site'); ?>
            </div>
            <div class="dup-pro-meter-wrapper" >
                <div class="dup-pro-meter blue dup-pro-fullsize">
                    <span></span>
                </div>
                <span class="text"></span>
            </div>
            <b><?php DUP_PRO_U::esc_html_e('Please Wait...'); ?></b><br/><br/>
            <i><?php DUP_PRO_U::esc_html_e('Keep this window open during the scan process.'); ?></i><br/>
            <i><?php DUP_PRO_U::esc_html_e('This can take several minutes.'); ?></i><br/>
        </div>

        <!--  SCAN DETAILS REPORT -->
        <div id="dup-msg-success" style="display:none">
            <div style="text-align:center">
                <div class="dup-hdr-success"><i class="far fa-check-square"></i> <?php DUP_PRO_U::esc_html_e('Scan Complete'); ?></div>
                <div id="dup-msg-success-subtitle">
                    <?php DUP_PRO_U::esc_html_e("Process Time:"); ?> <span id="data-rpt-scantime"></span>
                </div>
            </div>
            <div class="details">
                <?php
                include(DUPLICATOR____PATH . '/views/packages/main/s2.scan2.server.php');
                echo '<br/>';
                include(DUPLICATOR____PATH . '/views/packages/main/s2.scan3.archive.php');
                ?>
            </div>
        </div>

        <!--  ERROR MESSAGE -->
        <div id="dup-msg-error" style="display:none">
            <div class="dup-hdr-error"><i class="fa fa-exclamation-circle"></i> <?php DUP_PRO_U::esc_html_e('Scan Error'); ?></div>
            <i><?php DUP_PRO_U::esc_html_e('Please try again!'); ?></i><br/>
            <div style="text-align:left">
                <b><?php DUP_PRO_U::esc_html_e("Server Status:"); ?></b> &nbsp;
                <div id="dup-msg-error-response-status" style="display:inline-block"></div><br/>
                <b><?php DUP_PRO_U::esc_html_e("Error Message:"); ?></b>
                <div id="dup-msg-error-response-text"></div>
            </div>
        </div>
    </div>

    <!-- WARNING CONTINUE -->
    <div id="dpro-scan-warning-continue">
        <div class="msg1">
            <label for="dup-scan-warning-continue-checkbox">
                <?php esc_html_e('A notice status has been detected, are you sure you want to continue?', 'duplicator-pro'); ?>
            </label>
            <div style="padding:8px 0">
                <input type="checkbox" id="dup-scan-warning-continue-checkbox" onclick="DupPro.Pack.warningContinue(this);"/>
                <label for="dup-scan-warning-continue-checkbox"><?php esc_html_e('Yes.  Continue with the build process!', 'duplicator-pro'); ?></label>
            </div>
        </div>
        <div class="msg2">
            <label for="dup-scan-warning-continue-checkbox">
                <?php
                _e("Scan checks are not required to pass, however they could cause issues on some systems.", 'duplicator-pro');
                echo '<br/>';
                _e("Please review the details for each section by clicking on the detail title.", 'duplicator-pro');
                ?>
            </label>
        </div>
    </div>

    <div id="dpro-confirm-area">
        <label for="dpro-confirm-check"><?php
            DUP_PRO_U::esc_html_e('Do you want to continue?');
            echo '<br/> ';
            DUP_PRO_U::esc_html_e('At least one or more checkboxes was checked in "Quick Filters".')
        ?><br/>
            <i style="font-weight:normal"><?php DUP_PRO_U::esc_html_e('To apply a "Quick Filter" click the "Add Filters & Rescan" button') ?></i><br/>
            <input type="checkbox" id="dpro-confirm-check" onclick="jQuery('#dup-build-button').removeAttr('disabled');">
            <?php DUP_PRO_U::esc_html_e('Yes. Continue without applying any file filters.') ?></label><br/>
    </div>

    <div class="dup-button-footer" style="display:none">
        <?php
        $back_nonce = wp_create_nonce('new1-package');
        ?>
        <input type="button" value="&#9664; <?php DUP_PRO_U::esc_html_e("Back") ?>" onclick="window.location.assign('?page=duplicator-pro&tab=packages&inner_page=new1&_wpnonce=' + '<?php echo $back_nonce; ?>')" class="button button-large" />
        <input type="button" value="<?php DUP_PRO_U::esc_attr_e("Rescan") ?>" onclick="  DupPro.Pack.reRunScanner()" class="button button-large" />
        <input type="button" onclick="DupPro.Pack.startBuild();" class="button button-primary button-large" id="dup-build-button" value='<?php DUP_PRO_U::esc_attr_e("Build") ?> &#9654'/>
    </div>
</form>

<script>
    jQuery(document).ready(function ($)
    {
        let errorMessage = <?php echo SnapJson::jsonEncode($messageText); ?>;
        DupPro.Pack.WebServiceStatus = {
            Pass: 1,
            Warn: 2,
            Error: 3,
            Incomplete: 4,
            ScheduleRunning: 5
        }

        DupPro.Pack.runScanner = function (callbackOnSuccess) {
            $.ajax({
                type: "POST",
                cache: false,
                dataType: "text",
                url: ajaxurl,
                timeout: 10000000,
                data: {
                    action: 'duplicator_pro_package_scan', 
                    nonce: '<?php echo wp_create_nonce('duplicator_pro_package_scan'); ?>'
                },
                complete: function () {},
                success: function (respData, textStatus, xHr) {
                    try {
                        var data = DupPro.parseJSON(respData);
                    } catch (err) {
                        console.error(err);
                        console.error('JSON parse failed for response data: ' + respData);
                        var status = xHr.status + ' -' + xHr.statusText;
                        $('#dup-progress-bar-area, #dup-build-button').hide();
                        $('#dup-msg-error-response-status').html(status)
                        $('#dup-msg-error-response-text').html(xHr.responseText);
                        $('#dup-msg-error, .dup-button-footer').show();
                        console.log(data);
                        return false;
                    }
                    var data = data || new Object();

                    if (data.ScanStatus !== undefined && data.ScanStatus == 'running') {
                        DupPro.Pack.runScanner();
                    } else {
                        var status = data.Status || 3;
                        var message = data.Message || "Unable to read JSON from service. <br/> See: /wp-admin/admin-ajax.php?action=duplicator_pro_package_scan";
                        console.log(data);

                        if (status == DupPro.Pack.WebServiceStatus.Pass) {
                            DupPro.Pack.loadScanData(data);
                            if (typeof callbackOnSuccess === "function") {
                                callbackOnSuccess(data);
                            }
                            $('.dup-button-footer').show();
                        } else if (status == DupPro.Pack.WebServiceStatus.ScheduleRunning) {
                            // as long as its just saying that someone blocked us keep trying
                            console.log('retrying scan in 300 ms...');
                            setTimeout(DupPro.Pack.runScanner, 300);
                        } else {
                            $('#dup-progress-bar-area, #dup-build-button').hide();
                            $('#dup-msg-error-response-status').html(status);
                            $('#dup-msg-error-response-text').html(message + errorMessage);
                            $('#dup-msg-error').show();
                            $('.dup-button-footer').show();
                        }
                    }
                },
                error: function (data) {
                    var status = data.status + ' -' + data.statusText;
                    $('#dup-progress-bar-area, #dup-build-button').hide();
                    $('#dup-msg-error-response-status').html(status)
                    $('#dup-msg-error-response-text').html(data.responseText + errorMessage);
                    $('#dup-msg-error, .dup-button-footer').show();
                    console.log(data);
                }
            });
        }

        DupPro.Pack.reRunScanner = function (callbackOnSuccess)
        {
            $('#dup-msg-success,#dup-msg-error,.dup-button-footer,#dpro-confirm-area').hide();
            $('#dpro-confirm-check').prop('checked', false);
            $('#dup-progress-bar-area').show();
            $('#dpro-scan-warning-continue').hide();
            DupPro.Pack.runScanner(callbackOnSuccess);
        }

        DupPro.Pack.loadScanData = function (data)
        {
            try {
                var errMsg = "unable to read";
                $('#dup-progress-bar-area').hide();
                //****************
                // BRAND
                // #data-srv-brand-check
                // #data-srv-brand-name
                // #data-srv-brand-note

                $("#data-srv-brand-name").text(data.SRV.Brand.Name);
                if (data.SRV.Brand.LogoImageExists)
                    $("#data-srv-brand-note").html(data.SRV.Brand.Notes);
                else
                    $("#data-srv-brand-note").html("<?php DUP_PRO_U::esc_html_e("WARNING! Logo images no longer can be found inside brand. Please edit this brand and place new images. After that you can build your package with this brand.") ?>");

                $("#data-srv-brand-check").html(DupPro.Pack.setScanStatus(data.SRV.Brand.LogoImageExists));


                //****************
                //REPORT
                var base = $('#data-rpt-scanfile').attr('href');
                $('#data-rpt-scanfile').attr('href', base + '&scanfile=' + data.RPT.ScanFile);
                $('#data-rpt-scantime').text(data.RPT.ScanTime || 0);

                DupPro.Pack.intServerData(data);
                DupPro.Pack.initArchiveFilesData(data);

                //Addon Sites
                $('#data-arc-status-addonsites').html(DupPro.Pack.setScanStatus(data.ARC.Status.AddonSites));
                if (data.ARC.FilterInfo.Dirs.AddonSites !== undefined && data.ARC.FilterInfo.Dirs.AddonSites.length > 0) {
                    $("#addonsites-block").show();
                }
                $('#dup-msg-success').show();

                //****************
                //DATABASE
                var html = "";
                var DB_TableRowMax = <?php echo DUPLICATOR_PRO_SCAN_DB_TBL_ROWS; ?>;
                var DB_TableSizeMax = <?php echo DUPLICATOR_PRO_SCAN_DB_TBL_SIZE; ?>;
                if (data.DB.Status.Success) {
                    $('#data-db-status-size1').html(DupPro.Pack.setScanStatus(data.DB.Status.Size));
                    $('#data-db-size1').text(data.DB.Size || errMsg);
                    $('#data-db-size2').text(data.DB.Size || errMsg);
                    $('#data-db-rows').text(data.DB.Rows || errMsg);
                    $('#data-db-tablecount').text(data.DB.TableCount || errMsg);
                    //Table Details
                    if (data.DB.TableList == undefined || data.DB.TableList.length == 0) {
                        html = '<?php DUP_PRO_U::esc_html_e("Unable to report on any tables") ?>';
                    } else {
                        $.each(data.DB.TableList, function (i) {
                            html += '<b>' + i + '</b><br/>';
                            html += '<table><tr>';
                            $.each(data.DB.TableList[i], function (key, val) {
                                switch (key) {
                                    case 'Case':
                                        color = (val == 1) ? 'maroon' : 'black';
                                        html += '<td style="color:' + color + '">Uppercase: ' + val + '</td>';
                                        break;
                                    case 'Rows':
                                        color = (val > DB_TableRowMax) ? 'red' : 'black';
                                        html += '<td style="color:' + color + '">Rows: ' + val + '</td>';
                                        break;
                                    case 'USize':
                                        color = (parseInt(val) > DB_TableSizeMax) ? 'red' : 'black';
                                        html += '<td style="color:' + color + '">Size: ' + data.DB.TableList[i]['Size'] + '</td>';
                                        break;
                                }
                            });
                            html += '</tr></table>';
                        });
                    }
                    $('#data-db-tablelist').html(html);
                } else {
                    html = '<?php DUP_PRO_U::esc_html_e("Unable to report on database stats") ?>';
                    $('#dup-scan-db').html(html);
                }

                var isWarn = false;
                for (key in data.ARC.Status) {
                    if ('Big' != key && 'Warn' == data.ARC.Status[key]) {
                        isWarn = true;
                    }
                }

                if (!isWarn) {
                    if ('Warn' == data.DB.Status.Size) {
                        isWarn = true;
                    }
                }

                if (!isWarn && 'Warn' == data.DB.Status.Rows) {
                    isWarn = true;
                }

                if (!isWarn && 'Warn' == data.SRV.PHP.ALL) {
                    isWarn = true;
                }

                if (!isWarn && (data.SRV.WP.version == false || data.SRV.WP.core == false)) {
                    isWarn = true;
                }

                if (isWarn) {
                    $('#dpro-scan-warning-continue').show();
                    $('#dup-build-button').prop("disabled", true).removeClass('button-primary');
                    if ($('#dpro-scan-warning-continue-checkbox').is(':checked')) {
                        $('#dup-build-button').removeAttr('disabled').addClass('button-primary');
                    }
                } else {
                    $('#dpro-scan-warning-continue').hide();
                    $('#dup-build-button').prop("disabled", false).addClass('button-primary');
                }
            } catch (err) {
                err += '<br/> Please try again!'
                $('#dup-msg-error-response-status').html("n/a")
                $('#dup-msg-error-response-text').html(err);
                $('#dup-msg-error, .dup-button-footer').show();
                $('#dup-build-button').hide();
            }
        }

        //Starts the build process
        DupPro.Pack.startBuild = function ()
        {
            if ($('#dpro-confirm-check').is(":checked")) {
                $('#form-duplicator').submit();
                return true;
            }

            var sizeChecks = $('#hb-files-large-jstree').length ? $('#hb-files-large-jstree').jstree(true).get_checked() : 0;
            var addonChecks = $('#hb-addon-sites-result input:checked');
            var utf8Checks = $('#hb-files-utf8-jstree').length ? $('#hb-files-utf8-jstree').jstree(true).get_checked() : 0;
            if (sizeChecks.length > 0 || addonChecks.length > 0 || utf8Checks.length > 0) {
                $('#dpro-confirm-area').show();
                $('#dup-build-button').prop('disabled', true);
                return false;
            } else {
                $('#form-duplicator').submit();
            }
        }

        //Toggles each scan item to hide/show details
        DupPro.Pack.toggleScanItem = function (item)
        {
            var $info = $(item).parents('div.scan-item').children('div.info');
            var $text = $(item).find('div.text i.fa');
            if ($info.is(":hidden")) {
                $text.addClass('fa-caret-down').removeClass('fa-caret-right');
                $info.show();
            } else {
                $text.addClass('fa-caret-right').removeClass('fa-caret-down');
                $info.hide(250);
            }
        }

        //Set Good/Warn Badges and checkboxes
        DupPro.Pack.setScanStatus = function (status)
        {
            var result;
            switch (status) {
                case false :
                    result = '<div class="scan-warn"><i class="fa fa-exclamation-triangle fa-sm"></i></div>';
                    break;
                case 'Warn' :
                    result = '<div class="badge badge-warn">Notice</div>';
                    break;
                case true :
                    result = '<div class="scan-good"><i class="fa fa-check"></i></div>';
                    break;
                case 'Good' :
                    result = '<div class="badge badge-pass">Good</div>';
                    break;
                default :
                    result = 'unable to read';
            }
            return result;
        }

        //Allows user to continue with build if warnings found
        DupPro.Pack.warningContinue = function (checkbox)
        {
            ($(checkbox).is(':checked'))
                    ? $('#dup-build-button').prop('disabled', false).addClass('button-primary')
                    : $('#dup-build-button').prop('disabled', true).removeClass('button-primary');
        }

        //Page Init:
        DupPro.Pack.runScanner();
    });
</script>
