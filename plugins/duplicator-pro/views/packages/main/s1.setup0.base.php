<?php

defined("ABSPATH") or die("");

use Duplicator\Controllers\PackagesPageController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;

global $wpdb;

//POST BACK
$action_updated = null;
if (!empty($_POST['action'])) {
    switch ($_POST['action']) {
        case 'duplicator_pro_package_active':
            $action_response = DUP_PRO_U::__('Package settings have been reset.');
            break;
    }
}

$manual_template = DUP_PRO_Package_Template_Entity::get_manual_template();

$dup_tests = DUP_PRO_Server::getRequirments();
if ($dup_tests['Success'] != true) {
    DUP_PRO_Log::traceObject('Requirements', $dup_tests);
}
$templates = DUP_PRO_Package_Template_Entity::getAll();

$default_name1 = DUP_PRO_Package::get_default_name();
$default_name2 = DUP_PRO_Package::get_default_name(false);
$default_notes = $manual_template->notes;

$dbbuild_mode = DUP_PRO_DB::getBuildMode();

/** @var bool */
$blur = TplMng::getInstance()->getGlobalValue('blurCreate');
?>

<style>
    /* -----------------------------
    PACKAGE OPTS*/
    form#dup-form-opts {margin-top:10px; font-size:14px}
    form#dup-form-opts textarea, input[type="text"], input[type="password"] {width:100%}
    input#package-name {padding:4px;  height: 2em; line-height: 100%; width: 100%;   margin: 0 0 3px;}
    select#template_id {padding: 2px; height:2em; line-height: 130%; width: 100%;  }
    textarea#package-notes {height:75px;}
    div.dup-notes-add {float:right; margin:0;}
    div#dup-notes-area {display:none;}
    select#template_id {width:100%; max-width: none; margin-bottom:4px}
    div.dpro-general-area {line-height:27px; margin:0 0 5px 0}
    div#dpro-template-specific-area table td:first-child {width:100px; font-weight: bold}
    div.dup-box {margin:20px 0 0 0}

    div#dpro-da-notice {padding:10px; line-height:20px; font-size: 14px}
    div#dpro-da-notice-info {display:none; padding: 5px}

    /*TABS*/
    ul.add-menu-item-tabs li, ul.category-tabs li {padding:3px 30px 5px}
</style>

<!-- ====================
TOOL-BAR -->
<table class="dpro-edit-toolbar <?php echo ($blur ? 'dup-mock-blur' : ''); ?>">
    <tr>
        <td>
            <div id="dup-wiz">
                <div id="dup-wiz-steps">
                    <div class="active-step"><a>1 <?php DUP_PRO_U::esc_html_e('Setup'); ?></a></div>
                    <div><a>2 <?php DUP_PRO_U::esc_html_e('Scan'); ?> </a></div>
                    <div><a>3 <?php DUP_PRO_U::esc_html_e('Build'); ?> </a></div>
                </div>
                <div id="dup-wiz-title" style="white-space: nowrap">
                    <?php DUP_PRO_U::esc_html_e('Step 1: Package Setup'); ?>
                </div>
            </div>
        </td>
    </tr>
</table>
<hr class="dpro-edit-toolbar-divider"/>

<?php if (! empty($action_response)) : ?>
    <div id="message" class="notice notice-success"><p><?php echo $action_response; ?></p></div>
<?php endif; ?>

<?php require_once(DUPLICATOR____PATH . '/views/packages/main/s1.setup1.reqs.php'); ?>

<?php
$form_action_url = ControllersManager::getMenuLink(
    ControllersManager::PACKAGES_SUBMENU_SLUG,
    PackagesPageController::L2_SLUG_PACKAGE_BUILD,
    null,
    array(
        'inner_page' => 'new2',
        '_wpnonce' => wp_create_nonce('new2-package')
    )
)
?>
<form 
    id="dup-form-opts"
    class="<?php echo ($blur ? 'dup-mock-blur' : ''); ?>" 
    method="post" 
    action="<?php echo $form_action_url;?>" 
    data-parsley-validate data-parsley-ui-enabled="true" 
>
    <input type="hidden" id="dup-form-opts-action" name="action" value="">
    <div class="dpro-general-area">
        <div>
            <label for="package-name" class="lbl-larger"><span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Package') ?></span>&nbsp;<?php DUP_PRO_U::esc_html_e('Name') ?>:</label>
            <a href="javascript:void(0)" onClick="DupPro.Pack.ResetName()" title="<?php DUP_PRO_U::esc_attr_e('Toggle a default name') ?>"><i class="fas fa-random"></i></a>
            <div class="dup-notes-add">
                <button type="button" onClick="jQuery('#dup-notes-area').toggle()" class="dup-btn-borderless"  title="<?php DUP_PRO_U::esc_attr_e('Add Notes') ?>">
                    <i class="far fa-edit"></i>
                </button>
            </div>
        </div>

        <div>
            <input id="package-name"  name="package-name" type="text" maxlength="40"  required="true" data-regexp="^[0-9A-Za-z|_]+$" />
            <div id="dup-notes-area">
                <label class="lbl-larger">&nbsp;<?php DUP_PRO_U::esc_html_e('Notes') ?>:</label><br/>
                <textarea id="package-notes" name="package-notes" maxlength="300" /></textarea>
            </div>
        </div>

        <div>
            <label for="template_id" class="lbl-larger">&nbsp;<?php DUP_PRO_U::esc_html_e('Template') ?>:</label>
            <i class="fas fa-question-circle fa-sm"
                data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Apply Template"); ?>"
                data-tooltip="<?php DUP_PRO_U::esc_attr_e('An optional template configuration that can be applied to this package setup. An [Unassigned] template will retain the settings from the last scan/build.'); ?>"></i>
            
            <div style="float:right">
                <button type="button" onclick="window.open('admin.php?page=duplicator-pro-tools&tab=templates')" class="dup-btn-borderless" title="<?php DUP_PRO_U::esc_attr_e("List All Templates") ?>"><i class="far fa-clone"></i></button>
                <button type="button" onclick="DupPro.Pack.EditTemplate()"  class="dup-btn-borderless" title="<?php DUP_PRO_U::esc_attr_e("Edit Selected Template") ?>"><i class="far fa-edit"></i></button>
            </div>
        </div>
        
        <div>
            <select data-parsley-ui-enabled="false" onChange="DupPro.Pack.EnableTemplate();" name="template_id" id="template_id" aria-label="Prefill option with selected template">
                <option value="<?php echo intval($manual_template->getId()); ?>"><?php echo '[' . DUP_PRO_U::esc_html__('Unassigned') . ']' ?></option>
                <?php
                if (count($templates) == 0) {
                    $no_templates = __('No Templates');
                    echo "<option value='-1'>$no_templates</option>";
                } else {
                    foreach ($templates as $template) {
                        if ($template->is_manual) {
                            continue;
                        }
                        echo "<option value='{$template->getId()}'>" . esc_html($template->name) . "</option>";
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <?php
        require_once(DUPLICATOR____PATH . '/views/packages/main/s1.setup2.store.php');
        require_once(DUPLICATOR____PATH . '/views/packages/main/s1.setup3.archive.php');
        require_once(DUPLICATOR____PATH . '/views/packages/main/s1.setup4.install.php');
    ?>

    <div class="dup-button-footer">
        <input type="button" value="<?php DUP_PRO_U::esc_attr_e("Reset") ?>" class="button button-large" <?php echo ($dup_tests['Success']) ? '' : 'disabled="disabled"'; ?> onClick="DupPro.Pack.ResetSettings()" />
        <input 
            id="button-next" 
            type="submit" 
            onClick="DupPro.Pack.BeforeSubmit()" 
            value="<?php DUP_PRO_U::esc_attr_e("Next") ?> &#9654;" 
            class="button button-primary button-large" <?php echo ($dup_tests['Success']) ? '' : 'disabled="disabled"'; ?> 
        >
    </div>
</form>

<!-- CACHE PROTECTION: If the back-button is used from the scanner page then we need to
refresh page in-case any filters where set while on the scanner page -->
<form id="cache_detection">
    <input type="hidden" id="cache_state" name="cache_state" value="" />
</form>
<?php
    $confirm1               = new DUP_PRO_UI_Dialog();
    $confirm1->title        = DUP_PRO_U::__('Would you like to continue');
    $confirm1->message      = DUP_PRO_U::__('This will clear all of the current package settings.');
    $confirm1->progressText = DUP_PRO_U::__('Please Wait...');
    $confirm1->jsCallback   = 'DupPro.Pack.ResetSettingsRun()';
    $confirm1->initConfirm();
?>
<script>
jQuery(function($)
{
    var packageTemplates = <?php echo DUP_PRO_Package_Template_Entity::getTemplatesFrontendListData(); ?>

    DupPro.Pack.BeforeSubmit = function(){
        $('#mu-exclude option').each(function(){
            $(this).prop('selected', true);
        });

        DupPro.Pack.FillExcludeTablesList();
    };

    // Template-specific Functions
    DupPro.Pack.GetTemplateById = function (templateId)
    {
        for (var i = 0; i < packageTemplates.length; i++) {
            var currentTemplate = packageTemplates[i];
            if (currentTemplate.id == templateId) {
                    return currentTemplate;
            }
        }
        return null;
    };

    $.fn.selectOption = function(val){
        this.val(val)
            .find('option')
            .prop('selected', false)
            .parent()
            .find('option[value="'+ val +'"]')
            .prop('selected', true)
            .parent()
            .trigger('change');
        return this;
    };

    DupPro.Pack.PopulateCurrentTemplate = function ()
    {
        var selectedId = $('#template_id').val();
        var selectedTemplate = DupPro.Pack.GetTemplateById(selectedId);
        if (selectedTemplate != null)
        {
            var name = selectedTemplate.name;

            if(selectedTemplate.is_manual) {
                name = "<?php echo DUP_PRO_Package::get_default_name(); ?>";
            }

            $("#package-name").val(name);
            $("#package-notes").val(selectedTemplate.notes);

            $("#export-onlydb").prop("checked", selectedTemplate.archive_export_onlydb);
            $("#filter-on").prop("checked", selectedTemplate.archive_filter_on);
            $("#filter-names").prop("checked", selectedTemplate.archive_filter_names);

            $("#filter-dirs").val(selectedTemplate.archive_filter_dirs.split(";").join(";\n"));
            $("#filter-exts").val(selectedTemplate.archive_filter_exts);
            $("#filter-files").val(selectedTemplate.archive_filter_files.split(";").join(";\n"));
            $("#dbfilter-on").prop("checked", selectedTemplate.database_filter_on);
            $("#db-prefix-filter").prop("checked", selectedTemplate.databasePrefixFilter);
            $("#db-prefix-sub-filter").prop("checked", selectedTemplate.databasePrefixSubFilter);
            DupPro.Pack.ExportOnlyDB();

            if (typeof selectedTemplate.filter_sites != 'undefined' && selectedTemplate.filter_sites.length > 0) {
                for(var i=0; i < selectedTemplate.filter_sites.length;i++){
                    var site_id = selectedTemplate.filter_sites[i];
                    var exclude_option = $('#mu-include').find("option[value="+site_id+"]").first();
                    console.log(exclude_option.html());
                    $("#mu-exclude").append(exclude_option.clone());
                    exclude_option.remove();
                }
            }

            //-- cPanel
            $("#cpnl-enable").prop("checked", selectedTemplate.installer_opts_cpnl_enable);
            $("#cpnl-host").val(selectedTemplate.installer_opts_cpnl_host);
            $("#cpnl-user").val(selectedTemplate.installer_opts_cpnl_user);

            $('.secure-on-input-wrapper input').prop("checked", false);
            $('.secure-on-input-wrapper input[value=' + selectedTemplate.installer_opts_secure_on + ']:enabled').prop("checked", true);
            $("#skipscan").prop("checked", selectedTemplate.installer_opts_skip_scan);
            $("#secure-pass").val(selectedTemplate.installerPassowrd);
            $("#cpnl-dbaction").selectOption(selectedTemplate.installer_opts_cpnl_db_action);
            $("#cpnl-dbhost").val(selectedTemplate.installer_opts_cpnl_db_host);
            $("#cpnl-dbname").val(selectedTemplate.installer_opts_cpnl_db_name);
            $("#cpnl-dbuser").val(selectedTemplate.installer_opts_cpnl_db_user);

            //-- Brand
            let installer_opts_brand = selectedTemplate.installer_opts_brand;
                installer_opts_brand_id = [];
                x = 0;

            // most tricky thing - setup proper brand on emplate change
            if (typeof selectedTemplate.installer_opts_brand != 'undefined') {
                if(selectedTemplate.installer_opts_brand <= 0) {
                    installer_opts_brand = -1;
                } else if(selectedTemplate.installer_opts_brand > 0) {
                    installer_opts_brand = Number(selectedTemplate.installer_opts_brand);
                }
            }

            // find, fix deleted brands and setup default
            for (var i = 0; i < packageTemplates.length; i++) {
                if(
                    typeof packageTemplates[i].installer_opts_brand != 'undefined'
                    && null != packageTemplates[i].installer_opts_brand
                    && packageTemplates[i].installer_opts_brand != 0
                ) {
                    installer_opts_brand_id[x]=Number(packageTemplates[i].installer_opts_brand);
                    x++;
                }
            }

            $("#brand").selectOption(installer_opts_brand);

            //-- Database
            if (selectedTemplate.database_filter_tables.length) {
                let databaseFilterTables = selectedTemplate.database_filter_tables.split(",");
                let tablesToExclude = $("#dup-db-tables-exclude");

                tablesToExclude.find(".dup-pseudo-checkbox").each(function () {
                    let node = $(this);
                    if (databaseFilterTables.includes(node.data('value'))) {
                        node.addClass('checked');
                    } else {
                        node.removeClass('checked');
                    }
                });
            }

            $("#dbhost").val(selectedTemplate.installer_opts_db_host);
            $("#dbname").val(selectedTemplate.installer_opts_db_name);
            $("#dbuser").val(selectedTemplate.installer_opts_db_user);

        } else {
            console.log("Template ID doesn't exist?? " + selectedId);
        }

        //Default to Installer cPanel tab if used
        $('#cpnl-enable').is(":checked") ? $('#dpro-cpnl-tab-lbl').trigger("click") : $('#dpro-bsc-tab-lbl').trigger("click");
    };


    DupPro.Pack.ResetSettings = function ()
    {
        <?php $confirm1->showConfirm(); ?>
    };

    DupPro.Pack.ResetSettingsRun = function(){
        $('#dup-form-opts')[0].reset();
        setTimeout(function(){tb_remove();}, 800);
    }

    DupPro.Pack.EditTemplate = function ()
    {
        var manualTemplateID = <?php echo $manual_template->getId(); ?>;
        var templateID = $('#template_id').val();
        var url;

        if (templateID <= 0 || templateID == manualTemplateID) {
            url = 'admin.php?page=duplicator-pro-tools&tab=templates';
        } else {
            url = 'admin.php?page=duplicator-pro-tools&tab=templates&inner_page=edit&package_template_id=' + templateID;
        }
        window.open(url, 'edit-template');
    };

});

//INIT
jQuery(document).ready(function ($) {
    var DPRO_NAME_LAST;
    var DPRO_NAME_DEFAULT1  = '<?php echo esc_js($default_name1); ?>';
    var DPRO_NAME_DEFAULT2  = '<?php echo esc_js($default_name2); ?>';

    DupPro.Pack.checkPageCache = function()
    {
        var $state = $('#cache_state');
        if( $state.val() == "" ) {
            $state.val("fresh-load");
        } else {
            $state.val("cached");
            <?php
                $redirect = admin_url('admin.php?page=duplicator-pro&tab=packages&inner_page=new1');
                $redirect = wp_nonce_url($redirect, 'new1-package');
                echo "window.location.href = '{$redirect}'";
            ?>
        }
    }

    DupPro.Pack.EnableTemplate = function ()
    {
        $("#dup-form-opts-action").val('template-create');
        $('#dpro-template-specific-area').show(0);
        DupPro.Pack.PopulateCurrentTemplate();
        //DupPro.Pack.ToggleInstallerPassword();
        DupPro.EnableInstallerPassword();
        DupPro.Pack.ToggleFileFilters();
        DupPro.Pack.ToggleDBFilters();
        DupPro.Pack.ToggleNoPrefixTables(false);
        DupPro.Pack.ToggleNoSubsiteExistsTables(false);
    }

    DupPro.Pack.ResetName = function ()
    {
        var current = $('#package-name').val();
        switch (current) {
            case DPRO_NAME_LAST     : $('#package-name').val(DPRO_NAME_DEFAULT2); break;
            case DPRO_NAME_DEFAULT1 : $('#package-name').val(DPRO_NAME_LAST); break;
            case DPRO_NAME_DEFAULT2 : $('#package-name').val(DPRO_NAME_DEFAULT1); break;
            default:    $('#package-name').val(DPRO_NAME_LAST);
        }
    };

    DupPro.Pack.checkPageCache();
    DupPro.Pack.EnableTemplate();
    DupPro.Pack.ExportOnlyDB();
    DPRO_NAME_LAST  = $('#package-name').val();
    DupPro.Pack.CountFilters();
    $('input#package-name').focus().select();

});
</script>
