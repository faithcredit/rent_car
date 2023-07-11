<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Core\Views\TplMng;

$tplMng = TplMng::getInstance();

$global = DUP_PRO_Global_Entity::getInstance();

$ui_css_archive = (DUP_PRO_UI_ViewState::getValue('dup-pack-archive-panel') ? 'display:block' : 'display:none');
$multisite_css  = is_multisite() ? '' : 'display:none';

$archive_format = ($global->getBuildMode() == DUP_PRO_Archive_Build_Mode::DupArchive ? 'daf' : 'zip');
?>

<style>
    /*ARCHIVE: Area*/
    form#dup-form-opts div.tabs-panel{max-height:800px; padding:20px 15px 15px 15px; min-height:300px}
    form#dup-form-opts ul li.tabs{font-weight:bold}
    select#archive-format {min-width:100px; margin:1px 0px 4px 0px}
    span#dup-archive-filter-file {color:#A62426; display:none;}
    span#dup-archive-filter-db {color:#A62426; display:none;}
    span#dup-archive-db-only {color:#A62426; display:none;}
    span#dpro-install-secure-lock {color:#A62426; display:none;}
    /* Tab: Files */
    form#dup-form-opts textarea#filter-dirs {height:165px; padding:7px}
    form#dup-form-opts textarea#filter-exts {height:27px}
    form#dup-form-opts textarea#filter-files {height:165px; padding:7px}
    
    
     /* Tab: Multisite */
    table.mu-mode td {padding: 10px}
    table.mu-opts td {padding: 10px}
    select.mu-selector {
        height:175px !important; 
        width:450px; 
        max-width: 450px
    }
    select.mu-selector option {
        padding: 2px 0;
    }
    button.mu-push-btn {padding: 5px; width:40px; font-size:14px}
</style>

<!-- ===================
 META-BOX: ARCHIVE -->
<div class="dup-box">
    <div class="dup-box-title" >
        <i class="far fa-file-archive fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Archive') ?> 
        <sup class="dup-box-title-badge">
            <?php echo esc_html($archive_format); ?>
        </sup> &nbsp; &nbsp;
        <span class="dup-archive-filters-icons">
            <span id="dup-archive-filter-file" title="<?php DUP_PRO_U::esc_attr_e('Folder/File Filter Enabled') ?>">
                <span class="btn-separator"></span>
                <i class="fas fa-folder-open fa-fw"></i>
                <sup><i class="fas fa-filter fa-xs"></i></sup>
            </span>
            <span id="dup-archive-filter-db" title="<?php DUP_PRO_U::esc_attr_e('Database Table Filter Enabled') ?>">
                <span class="btn-separator"></span>
                <i class="fas fa-table fa-fw"></i>
                <sup><i class="fas fa-filter fa-xs"></i></sup>
            </span>
            <span id="dup-archive-db-only" title="<?php DUP_PRO_U::esc_attr_e('Archive Only the Database') ?>">
                <span class="btn-separator"></span>
                <i class="fas fa-database fa-fw"></i>
                <?php DUP_PRO_U::esc_html_e('Database Only') ?>
            </span>
            <span id="dpro-install-secure-lock" title="<?php DUP_PRO_U::esc_attr_e('Archive password protection is on') ?>">
                <span class="btn-separator"></span>
                <i class="fas fa-lock fa-fw"></i>
                <?php DUP_PRO_U::esc_html_e('Requires Password') ?>
            </span>
        </span>
        <button class="dup-box-arrow">
            <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Archive Settings') ?></span>
        </button>
    </div>
    
    <div class="dup-box-panel" id="dup-pack-archive-panel" style="<?php echo esc_attr($ui_css_archive); ?>">
        <input type="hidden" name="archive-format" value="ZIP" />

        <!-- ===================
        NESTED TABS -->
        <div data-dpro-tabs="true">
            <ul>
                <li class="filter-files-tab"><?php DUP_PRO_U::esc_html_e('Files') ?></li>
                <li class="filter-db-tab"><?php DUP_PRO_U::esc_html_e('Database') ?></li>
                <?php if (is_multisite()) { ?>
                <li class="filter-mu-tab" style="<?php echo $multisite_css ?>"><?php DUP_PRO_U::esc_html_e('Multisite') ?></li>
                <?php } ?>
                <li class="archive-setup-tab"><?php DUP_PRO_U::esc_html_e('Security') ?></li>
            </ul>

            <?php
                $tplMng->render('admin_pages/packages/setup/archive-filter-files-tab');
                $tplMng->render('admin_pages/packages/setup/archive-filter-db-tab');
            if (is_multisite()) {
                $tplMng->render('admin_pages/packages/setup/archive-filter-mu-tab');
            }
                $tplMng->render('admin_pages/packages/setup/archive-setup-tab');
            ?>
        </div>
    </div>
</div>

<div class="duplicator-error-container"></div>
<?php
    $alert1          = new DUP_PRO_UI_Dialog();
    $alert1->title   = DUP_PRO_U::__('ERROR!');
    $alert1->message = DUP_PRO_U::__('You can\'t exclude all sites.');
    $alert1->initAlert();
?>
<script>
jQuery(function($) 
{   
    /* METHOD: Toggle Archive file filter red icon */
    DupPro.Pack.ToggleFileFilters = function () 
    {
        var $filterItems = $('#dup-file-filter-items');
        if ($("#filter-on").is(':checked')) {
            $filterItems.prop('disabled', false).css({color: 'inherit'});
            $('#filter-exts, #filter-dirs, #filter-files').prop('readonly', false).css({color: 'inherit'});
            $('#dup-archive-filter-file').show();
        } else {
            $filterItems.attr('disabled', 'disabled').css({color: '#999'});
            $('#filter-dirs, #filter-exts, #filter-files').prop('readonly', true).css({color: '#999'});
            $('#dup-archive-filter-file').hide();
        }
    };

    DupPro.Pack.ExportOnlyDB = function ()
    {
        $('#dup-exportdb-items-off, #dup-exportdb-items-checked').hide();
        if ($("#export-onlydb").is(':checked')) {
            $('#dup-exportdb-items-checked').show();
            $('#dup-archive-db-only').show(100);
            $('#dup-archive-filter-db').hide();
            $('#dup-archive-filter-file, #dup-file-filter-label').hide();
            $('#dup-name-filter-label').hide();
        } else {
            $('#dup-exportdb-items-off, #dup-file-filter-label').show();
            $('#dup-name-filter-label').show();
            $('#dup-exportdb-items-checked').hide();
            $('#dup-archive-db-only').hide();
            DupPro.Pack.ToggleFileFilters();
        }

        DupPro.Pack.ToggleDBFilters();
    };


    /* METHOD: Formats file directory path name on seperate line of textarea */
    DupPro.Pack.AddExcludePath = function (path) 
    {
        var text = $("#filter-dirs").val() + path + ';\n';
        $("#filter-dirs").val(text);
        DupPro.Pack.CountFilters();
    };

    /*  Appends a path to the extention filter  */
    DupPro.Pack.AddExcludeExts = function (path) 
    {
        var text = $("#filter-exts").val() + path + ';';
        $("#filter-exts").val(text);
    };

    DupPro.Pack.AddExcludeFilePath = function (path) 
    {
        var text = $("#filter-files").val() + path + '/file.ext;\n';
        $("#filter-files").val(text);
        DupPro.Pack.CountFilters();
    };

    DupPro.Pack.CountFilters = function()
    {
         var dirCount = $("#filter-dirs").val().split(";").length - 1;
         var fileCount = $("#filter-files").val().split(";").length - 1;
         $("#filter-dirs-count").html(' (' + dirCount + ')');
         $("#filter-files-count").html(' (' + fileCount + ')');
    }
 });
 
//INIT
jQuery(document).ready(function($) 
{
    //MU-Transfer buttons
    $('#mu-include-btn').click(function() {
        return !$('#mu-exclude option:selected').remove().appendTo('#mu-include');  
    });

    $('#mu-exclude-btn').click(function() {
        var include_all_count = $('#mu-include option').length;
        var include_selected_count = $('#mu-include option:selected').length;

        if(include_all_count > include_selected_count) {
            return !$('#mu-include option:selected').remove().appendTo('#mu-exclude');
        } else {
            <?php $alert1->showAlert(); ?>
        }
    });

    $("#filter-dirs").keyup(function()  {DupPro.Pack.CountFilters();});
    $("#filter-files").keyup(function() {DupPro.Pack.CountFilters();});

});
</script>
