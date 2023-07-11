<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapUtil;

$tplMng = TplMng::getInstance();
/** @var bool */
$blur = TplMng::getInstance()->getGlobalValue('blur');

$templates_tab_url = ControllersManager::getMenuLink(
    ControllersManager::TOOLS_SUBMENU_SLUG,
    ToolsPageController::L2_SLUG_TEMPLATE
);
$edit_template_url =  ControllersManager::getMenuLink(
    ControllersManager::TOOLS_SUBMENU_SLUG,
    ToolsPageController::L2_SLUG_TEMPLATE,
    null,
    array(
        'inner_page' => 'edit'
    )
);

global $wp_version;
global $wpdb;
$global = DUP_PRO_Global_Entity::getInstance();

$nonce_action = 'duppro-template-edit';

$was_updated            = false;
$package_template_id    = SnapUtil::sanitizeIntInput(SnapUtil::INPUT_REQUEST, 'package_template_id', -1);
$package_templates      = DUP_PRO_Package_Template_Entity::getAll();
$package_template_count = count($package_templates);

// For now not including in filters since don't want to encourage use
// with schedules since filtering creates incomplete multisite
$displayMultisiteTab = (is_multisite() && License::can(License::CAPABILITY_MULTISITE_PLUS));

$view_state     = DUP_PRO_UI_ViewState::getArray();
$ui_css_archive = (DUP_PRO_UI_ViewState::getValue('dup-template-archive-panel') ? 'display:block' : 'display:none');
$ui_css_install = (DUP_PRO_UI_ViewState::getValue('dup-template-install-panel') ? 'display:block' : 'display:none');

if ($package_template_id == -1) {
    $package_template = new DUP_PRO_Package_Template_Entity();
} else {
    $package_template = DUP_PRO_Package_Template_Entity::getById($package_template_id);
    DUP_PRO_Log::traceObject("getting template $package_template_id", $package_template);
}

if (!empty($_REQUEST['action'])) {
    DUP_PRO_U::verifyNonce($_REQUEST['_wpnonce'], $nonce_action);
    if ($_REQUEST['action'] == 'save') {
        DUP_PRO_Log::traceObject('request', $_REQUEST);

        // Checkboxes don't set post values when off so have to manually set these
        $package_template->setFromInput(SnapUtil::INPUT_REQUEST);
        $package_template->save();
        $was_updated = true;
    } elseif ($_REQUEST['action'] == 'copy-template') {
        $source_template_id = SnapUtil::sanitizeIntInput(SnapUtil::INPUT_REQUEST, 'duppro-source-template-id', -1);

        if ($source_template_id > 0) {
            $package_template->copy_from_source_id($source_template_id);
            $package_template->save();
        }
    }
}

$installer_cpnldbaction = isset($package_template->installer_opts_cpnl_db_action) ? $package_template->installer_opts_cpnl_db_action : 'create';
$upload_dir             = DUP_PRO_Archive::getArchiveListPaths('uploads');
$content_path           = DUP_PRO_Archive::getArchiveListPaths('wpcontent');
$archive_format         = ($global->getBuildMode() == DUP_PRO_Archive_Build_Mode::DupArchive ? 'daf' : 'zip');
?>

<form 
    id="dpro-template-form" 
    class="<?php echo ($blur ? 'dup-mock-blur' : ''); ?>"
    data-parsley-validate data-parsley-ui-enabled="true" 
    action="<?php echo esc_url($edit_template_url); ?>" 
    method="post"
>
<?php wp_nonce_field($nonce_action); ?>
<input type="hidden" id="dpro-template-form-action" name="action" value="save">
<input type="hidden" name="package_template_id" value="<?php echo intval($package_template->getId()); ?>">

<!-- ====================
SUB-TABS -->
<?php if ($was_updated) : ?>
    <div class="notice notice-success is-dismissible dpro-wpnotice-box">
        <p>
            <?php DUP_PRO_U::esc_html_e('Template Updated'); ?>
        </p>
    </div>
<?php endif; ?>

<!-- ====================
TOOL-BAR -->
<table class="dpro-edit-toolbar">
    <tr>
        <td>
            <?php
            if ($package_template_count > 0) :
                $general_templates  = array();
                $existing_templates = array();
                foreach ($package_templates as $copy_package_template) {
                    if ($copy_package_template->getId() != $package_template->getId()) {
                        if ($copy_package_template->is_default || $copy_package_template->is_manual) {
                            $general_templates[$copy_package_template->getId()] = $copy_package_template->is_manual
                                ? DUP_PRO_U::__("Active Build Settings")
                                : $copy_package_template->name;
                        } else {
                            $existing_templates[$copy_package_template->getId()] = $copy_package_template->name;
                        }
                    }
                }
                ?>

                <select name="duppro-source-template-id">
                    <option value="-1"><?php DUP_PRO_U::esc_html_e("Copy From"); ?></option>
                    <?php
                    if (!empty($general_templates)) {
                        asort($general_templates);
                        ?>
                        <optgroup label="<?php DUP_PRO_U::esc_attr_e("General Templates"); ?>">
                            <?php
                            foreach ($general_templates as $id => $val) {
                                ?>
                                <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($val); ?></option>
                                <?php
                            }
                            ?>
                        </optgroup>
                        <?php
                    }
                    ?>
                    <?php
                    if (!empty($existing_templates)) {
                        asort($existing_templates);
                        ?>
                        <optgroup label="<?php DUP_PRO_U::esc_attr_e("Existing Templates"); ?>">
                            <?php
                            foreach ($existing_templates as $id => $val) {
                                ?>
                                <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($val); ?></option>
                                <?php
                            }
                            ?>
                        </optgroup>
                        <?php
                    }
                    ?>
                </select>
                <input type="button" class="button action" value="<?php DUP_PRO_U::esc_attr_e("Apply") ?>" onclick="DupPro.Template.Copy()">
            <?php else : ?>
                <select disabled="disabled"><option value="-1" selected="selected"><?php _e("Copy From"); ?></option></select>
                <input type="button" class="button action" value="<?php DUP_PRO_U::esc_attr_e("Apply") ?>" onclick="DupPro.Template.Copy()"  disabled="disabled">
            <?php endif; ?>
        </td>
        <td>
            <div class="btnnav">
                <a href="<?php echo esc_url($templates_tab_url); ?>" class="button dup-goto-templates-btn"><i class="far fa-clone"></i> <?php DUP_PRO_U::esc_html_e('Templates'); ?></a>
                <?php if ($package_template_id != -1) : ?>
                    <a href="admin.php?page=duplicator-pro-tools&tab=templates&inner_page=edit&_wpnonce=<?php echo wp_create_nonce('edit-template'); ?>" class="button"><?php DUP_PRO_U::esc_html_e("Add New"); ?></a>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
<hr class="dpro-edit-toolbar-divider"/>

<div class="dpro-template-general">

    <div class="margin-b-10px">
        <label><?php _e("Recovery Status", 'duplicator-pro'); ?>:</label> &nbsp;
        <?php $package_template->recoveableHtmlInfo(); ?> <br/><br/>
    </div>

    <label><?php _e("Template", 'duplicator-pro'); ?>:</label>
    <input type="text" id="template-name" name="name" data-parsley-errors-container="#template_name_error_container"
           data-parsley-required="true" value="<?php echo esc_attr($package_template->name); ?>" autocomplete="off" maxlength="125">
    <div id="template_name_error_container" class="duplicator-error-container"></div>

    <label><?php _e("Notes", 'duplicator-pro'); ?>:</label> <br/>
    <textarea id="template-notes" name="notes" style="height:50px"><?php echo esc_textarea($package_template->notes); ?></textarea>
</div>



<!-- ===============================
ARCHIVE -->
<div class="dup-box">
<div class="dup-box-title">
    <i class="far fa-file-archive fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Archive') ?>
            <sup class="dup-box-title-badge">
            <?php echo esc_html($archive_format); ?>
        </sup> &nbsp; &nbsp;
    <button class="dup-box-arrow">
        <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Archive') ?></span>
    </button>
</div>
<div class="dup-box-panel" id="dup-template-archive-panel" style="<?php echo esc_attr($ui_css_archive); ?>">

<!-- ===================
NESTED TABS -->
<div data-dpro-tabs="true">
    <ul>
        <li class="filter-files-tab"><?php DUP_PRO_U::esc_html_e('Files') ?></li>
        <li class="filter-db-tab"><?php DUP_PRO_U::esc_html_e('Database') ?></li>
        <?php if ($displayMultisiteTab) { ?>
            <li class="filter-mu-tab"><?php DUP_PRO_U::esc_html_e('Multisite') ?></li>
        <?php } ?>
        <li class="archive-setup-tab"><?php DUP_PRO_U::esc_html_e('Setup') ?></li>
    </ul>

    <!-- ===================
    TAB1: FILES -->
    <div>
        <div class="dup-package-hdr-1">
            <?php DUP_PRO_U::esc_html_e("Filters") ?>
        </div>

        <div class="dup-form-item">
            <span class="title"><?php DUP_PRO_U::esc_html_e("Database Only") ?>:</span>
            <span class="input">
                <input id="archive_export_onlydb" type="checkbox" 
                    <?php DUP_PRO_UI::echoChecked($package_template->archive_export_onlydb); ?> name="archive_export_onlydb"
                    onclick="DupPro.Template.ExportOnlyDB()"  />
                <label for="archive_export_onlydb"><?php _e("Archive only the database"); ?></label>
            </span>
        </div>

        <div class="dup-form-item">
            <span class="title"><?php DUP_PRO_U::esc_html_e("Enable Filters") ?>:</span>
            <span class="input">
                <input id="archive_filter_on" type="checkbox" <?php DUP_PRO_UI::echoChecked($package_template->archive_filter_on); ?>
                       name="archive_filter_on"  onclick="DupPro.Template.ToggleFileFilters()" />
                <label for="archive_filter_on"><?php _e("Allow folders, files &amp; file extensions to be excluded"); ?></label>
            </span>
        </div>

        <div class="dup-form-item">
            <span class="title"><?php DUP_PRO_U::esc_html_e("Name Filters") ?>:</span>
            <span class="input">
                <input id="archive_filter_names" type="checkbox" <?php DUP_PRO_UI::echoChecked($package_template->archive_filter_names); ?>
                       name="archive_filter_names" />
                <label for="archive_filter_names"><?php _e("Automatically filter files with invalid encoded names"); ?></label>
            </span>
        </div>

        <div id="dup-exportdb-items-off">
            <div>
                <label><b><?php _e("Folders"); ?>:</b></label>
                <div class='dup-quick-links'>
                    <a href="javascript:void(0)" onclick="DupPro.Template.AddExcludePath('<?php echo esc_js(duplicator_pro_get_home_path()); ?>')">
                        [<?php DUP_PRO_U::esc_html_e("root path") ?>]
                    </a>
                    <?php if (!empty($content_path)) : ?>
                        <a href="javascript:void(0)" onclick="DupPro.Template.AddExcludePath('<?php echo $content_path; ?>')">
                            [<?php DUP_PRO_U::esc_html_e("wp-content") ?>]
                        </a>
                    <?php endif; ?>
                    <a href="javascript:void(0)" onclick="DupPro.Template.AddExcludePath('<?php echo $upload_dir; ?>')">
                        [<?php DUP_PRO_U::esc_html_e("wp-uploads") ?>]
                    </a>
                    <a href="javascript:void(0)" onclick="DupPro.Template.AddExcludePath('<?php echo $content_path; ?>/cache')">
                        [<?php DUP_PRO_U::esc_html_e("cache") ?>]
                    </a>
                    <a href="javascript:void(0)" onclick="jQuery('#_archive_filter_dirs').val('')">
                        <?php DUP_PRO_U::esc_html_e("(clear)") ?>
                    </a>
                </div>
                <textarea name="_archive_filter_dirs" id="_archive_filter_dirs" placeholder="/full_path/exclude_path1;/full_path/exclude_path2;">
                    <?php echo str_replace(";", ";\n", esc_textarea($package_template->archive_filter_dirs)) ?>
                </textarea>
            </div><br/>

            <div>
                <label><b><?php _e("File Extensions"); ?>:</b></label>
                <div class='dup-quick-links'>
                    <a href="javascript:void(0)" onclick="DupPro.Template.AddExcludeExts('avi;mov;mp4;mpeg;mpg;swf;wmv;aac;m3u;mp3;mpa;wav;wma')">
                        [<?php DUP_PRO_U::esc_html_e("media") ?>]</a>
                    <a href="javascript:void(0)" onclick="DupPro.Template.AddExcludeExts('zip;rar;tar;gz;bz2;7z')">
                        [<?php DUP_PRO_U::esc_html_e("archive") ?>]
                    </a>
                    <a href="javascript:void(0)" onclick="jQuery('#_archive_filter_exts').val('')"><?php DUP_PRO_U::esc_html_e("(clear)") ?></a>
                </div>
                <input type="text" name="_archive_filter_exts" id="_archive_filter_exts"
                       value="<?php echo esc_attr($package_template->archive_filter_exts); ?>" placeholder="ext1;ext2;ext3">
            </div><br/>

            <div>
                <label><b><?php _e("Files"); ?>:</b></label>
                <div class='dup-quick-links'>
                    <a href="javascript:void(0)" onclick="DupPro.Template.AddExcludeFilePath('<?php echo esc_js(duplicator_pro_get_home_path()); ?>')">
                        [<?php DUP_PRO_U::esc_html_e("file path") ?>]
                    </a>
                    <a href="javascript:void(0)" onclick="jQuery('#_archive_filter_files').val('')"><?php DUP_PRO_U::esc_html_e("(clear)") ?></a>
                </div>
                <textarea name="_archive_filter_files" id="_archive_filter_files" placeholder="/full_path/exclude_file_1.ext;/full_path/exclude_file2.ext">
                    <?php echo str_replace(";", ";\n", esc_textarea($package_template->archive_filter_files)) ?>
                </textarea>
            </div>

            <div class="dup-tabs-opts-help">
                <?php
                    DUP_PRO_U::esc_html_e("The directories, extensions and files above will be be exclude from the archive file if enable is checked.");
                    echo '<br/>';
                    DUP_PRO_U::esc_html_e("Use full path for directories or specific files.");
                    echo " <b>";
                     DUP_PRO_U::esc_html_e("Use filenames without paths to filter same-named files across multiple directories.");
                    echo "</b>";
                    echo '<br/>';
                    DUP_PRO_U::esc_html_e("Use semicolons to separate all items. Use # to comment a line.");
                ?> <br/>
            </div>
        </div>

        <!-- DB ONLY ENABLED -->
        <div id="dup-exportdb-items-checked">
            <b><?php DUP_PRO_U::esc_html_e('Overview:'); ?></b><br/> 
            <?php
            DUP_PRO_U::esc_html_e("This advanced option excludes all files from the archive.  Only the database and a copy of the installer.php "
                . "will be included in the archive.zip file. The option can be used for backing up and moving only the database.");
            ?>
            <br/><br/>
            <b><i class='fa fa-exclamation-circle'></i> <?php DUP_PRO_U::esc_html_e('Notice:'); ?></b><br/>  
            <?php
            DUP_PRO_U::esc_html_e("Installing only the database over an existing site may have unintended consequences. "
                . "Be sure to know the state of your system before installing the database without the associated files.");

            echo '<br/><br/>';

            DUP_PRO_U::esc_html_e("For example, if you have WordPress 4.6 on this site and you copy this sites database to a host that has WordPress 4.8 "
                . "files then the source code of the files will not be in sync with the database causing possible errors.");

            echo '<br/><br/>';

            DUP_PRO_U::esc_html_e("This can also be true of plugins and themes. When moving only the database be sure to know the database will be "
                . "compatible with ALL source code files. Please use this advanced feature with caution!");
            ?>
            <br/><br/>
        </div>

    </div>

    <!-- ===================
    TAB2: DATABASE -->
    <div>
        <div class="dup-template-db-area">
            <?php
            $tableList = explode(',', $package_template->database_filter_tables);
            $tplMng->render(
                'parts/filters/tables_list_filter',
                array(
                    'dbFilterOn'        => $package_template->database_filter_on,
                    'dbPrefixFilter'    => $package_template->databasePrefixFilter,
                    'dbPrefixSubFilter' => $package_template->databasePrefixSubFilter,
                    'tablesSlected'     => $tableList
                )
            );
            ?><br/>

            <div class="dup-form-item">
                <span class="title">
                    <?php DUP_PRO_U::esc_html_e("Compatibility Mode") ?>
                    <i class="fas fa-question-circle fa-sm"
                       data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Legacy Support"); ?>"
                       data-tooltip="<?php DUP_PRO_U::esc_attr_e('This option is not available as a template setting.  It can only be used when creating '
                           . 'a new package.  Please see the FAQ for a full overview of using this feature.'); ?>">
                    </i>
                </span>
            </div>

            <i><?php
                $url = "<a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-090-q' target='_blank'>"
                    . DUP_PRO_U::esc_html__('FAQ details') . "</a>";
                printf(DUP_PRO_U::esc_html__("Not enabled for template settings. Please see the full %s"), $url);
            ?>
            </i>
       </div>
    </div>

    <!-- ===================
    MULTI-SITE TAB 3:  -->
    <?php if ($displayMultisiteTab) : ?>
        <div>
            <div class="dup-template-mu-area">
               <?php DUP_PRO_U::esc_html_e("Support for multisite filters is only available when creating a new package."); ?> <br/>
               <?php DUP_PRO_U::esc_html_e("To create a new package goto the Packages screen and click the 'Create New' button."); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ===================
    SETUP TAB 4:  -->
    <?php
        $tplMng->render(
            'admin_pages/packages/setup/archive-setup-tab',
            [
                'secureOn' => $package_template->installer_opts_secure_on,
                'securePass' => $package_template->installerPassowrd
            ]
        );
        ?>
</div> 
<!-- end tab control -->

</div>
</div>
<br />


<!-- ===============================
INSTALLER -->
<div class="dup-box">
    <div class="dup-box-title">
        <i class="fa fa-bolt fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Installer') ?>
          <button class="dup-box-arrow">
            <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Installer') ?></span>
        </button>
    </div>
    <div class="dup-box-panel" id="dup-template-install-panel" style="<?php echo esc_attr($ui_css_install); ?>">

        <div class="dpro-panel-optional-txt">
            <b><?php DUP_PRO_U::esc_html_e('All values in this section are'); ?> <u><?php DUP_PRO_U::esc_html_e('optional'); ?></u></b>
            <i class="fas fa-question-circle fa-sm"
               data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Setup/Prefills"); ?>"
               data-tooltip="<?php
                DUP_PRO_U::esc_attr_e('All values in this section are OPTIONAL! If you know ahead of time the database input fields the installer will use, '
                   . 'then you can optionally enter them here and they will be prefilled at install time.  Otherwise you can just enter them in at install '
                    . 'time and ignore all these options in the Installer section.');
                ?>"></i>

        </div>

        <table class="dpro-install-setup"  style="margin-top:-10px">
            <tr>
                <td colspan="2"><div class="dup-package-hdr-1"><?php DUP_PRO_U::esc_html_e("Setup") ?></div></td>
            </tr>
            <tr>
                <td style="width:130px"><b><?php DUP_PRO_U::esc_html_e("Branding") ?>:</b></td>
                <td>
                    <?php
                    if (License::can(License::CAPABILITY_BRAND)) :
                        $brands = DUP_PRO_Brand_Entity::getAllWithDefault();
                        ?>
                        <select name="installer_opts_brand" id="installer_opts_brand" onchange="DupPro.Template.BrandChange();">
                            <?php
                            $active_brand_id = $package_template->installer_opts_brand;
                            foreach ($brands as $i => $brand) :
                                ?>
                                <option value="<?php echo esc_attr($brand->getId()); ?>" title="<?php echo esc_attr($brand->notes); ?>"<?php if (isset($_REQUEST['inner_page']) && $_REQUEST['inner_page'] == 'edit') {
                                    selected($brand->getId(), $active_brand_id);
                                               } ?>>
                                    <?php echo esc_html($brand->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
  
                        <a href="javascript:void(0)" target="_blank" class="button" id="brand-preview">
                            <?php DUP_PRO_U::esc_html_e("Preview"); ?>
                        </a> &nbsp;
                        <i class="fas fa-question-circle fa-sm"
                           data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Choose Brand"); ?>"
                           data-tooltip="<?php DUP_PRO_U::esc_attr_e('This option changes the branding of the installer file.  Click the preview button to see the selected style.'); ?>"></i>
                    <?php else : ?>
                        <a href="admin.php?page=duplicator-pro-settings&tab=package&subtab=brand" class="upgrade-link" target="_blank">
                            <?php DUP_PRO_U::esc_html_e("Enable Branding"); ?>
                        </a>
                    <?php endif; ?>
                    <br/><br/>
                </td>
            </tr>
        </table>
        <br/>

        <table style="width:100%">
            <tr>
                <td colspan="2"><div class="dup-package-hdr-1"><?php DUP_PRO_U::esc_html_e("Prefills") ?></div></td>
            </tr>
        </table>

        <!-- ===================
        STEP1 TABS -->
        <div data-dpro-tabs="true">
            <ul>
                <li><?php DUP_PRO_U::esc_html_e('Basic') ?></li>
                <li id="dpro-cpnl-tab-lbl"><?php DUP_PRO_U::esc_html_e('cPanel') ?></li>
            </ul>

            <!-- ===================
            TAB1: Basic -->
            <div class="dup-template-basic-tab">
                <table class="form-table" role="presentation">
                    <tr>
                        <td colspan="2">
                            <b class="dpro-hdr"><?php DUP_PRO_U::esc_html_e('MySQL Server'); ?></b>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e("Host"); ?>:</th>
                        <td><input type="text" placeholder="localhost" name="installer_opts_db_host" value="<?php echo esc_attr($package_template->installer_opts_db_host); ?>"></td>
                    </tr>
                    <tr>
                        <th><label><?php _e("Database"); ?>:</label></th>
                        <td><input type="text" placeholder="<?php DUP_PRO_U::esc_attr_e('valid database name'); ?>" name="installer_opts_db_name" value="<?php echo esc_attr($package_template->installer_opts_db_name); ?>"></td>
                    </tr>
                    <tr>
                        <th><label><?php _e("User"); ?>:</label></th>
                        <td><input type="text" placeholder="<?php DUP_PRO_U::esc_attr_e('valid database user'); ?>" name="installer_opts_db_user" value="<?php echo esc_attr($package_template->installer_opts_db_user); ?>"></td>
                    </tr>
                </table>
                <br/><br/>
            </div>

            <!-- ===================
            TAB2: cPanel -->
            <div class="dup-template-cpanel-tab">
                <table class="form-table" role="presentation">
                    <tr>
                        <td colspan="2"><b class="dpro-hdr"><?php DUP_PRO_U::esc_html_e('cPanel Login'); ?></b></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Automation"); ?>:</label></th>
                        <td>
                            <input type="checkbox" name="installer_opts_cpnl_enable" id="installer_opts_cpnl_enable" <?php DUP_PRO_UI::echoChecked($package_template->installer_opts_cpnl_enable); ?> >
                            <label for="installer_opts_cpnl_enable">Auto Select cPanel</label>
                            <i 
                                class="fas fa-question-circle fa-sm" 
                                data-tooltip-title="Auto Select cPanel:" 
                                data-tooltip="<?php DUP_PRO_U::esc_attr_e('Enabling this options will automatically select the cPanel tab when step one of the installer is shown.'); ?>" >
                            </i>
                            &nbsp; &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Host"); ?>:</label></th>
                        <td><input type="text" name="installer_opts_cpnl_host" value="<?php echo esc_attr($package_template->installer_opts_cpnl_host); ?>"  placeholder="<?php DUP_PRO_U::esc_attr_e('valid cpanel host address'); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php DUP_PRO_U::esc_html_e("User"); ?>:</label></th>
                        <td><input type="text" name="installer_opts_cpnl_user" value="<?php echo esc_attr($package_template->installer_opts_cpnl_user); ?>"  placeholder="<?php DUP_PRO_U::esc_attr_e('valid cpanel user login'); ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <b class="dpro-hdr"><?php DUP_PRO_U::esc_html_e('MySQL Server'); ?></b>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php _e("Action"); ?>:</label></th>
                        <td>
                            <select name="installer_opts_cpnl_db_action" id="cpnl-dbaction">
                                <option value="create" <?php echo ($installer_cpnldbaction == 'create') ? 'selected' : ''; ?>>Create A New Database</option>
                                <option value="empty"  <?php echo ($installer_cpnldbaction == 'empty') ? 'selected' : ''; ?>>Connect to Existing Database and Remove All Data</option>
                                <!--option value="rename">Connect to Existing Database and Rename Existing Tables</option-->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php _e("Host"); ?>:</label></th>
                        <td><input type="text" name="installer_opts_cpnl_db_host" value="<?php echo esc_attr($package_template->installer_opts_cpnl_db_host); ?>" placeholder="<?php DUP_PRO_U::esc_attr_e('localhost'); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php _e("Database"); ?>:</label></th>
                        <td><input type="text" name="installer_opts_cpnl_db_name" value="<?php echo esc_attr($package_template->installer_opts_cpnl_db_name); ?>" placeholder="<?php DUP_PRO_U::esc_attr_e('valid database name'); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php _e("User"); ?>::</label></th>
                        <td><input type="text" name="installer_opts_cpnl_db_user" value="<?php echo esc_attr($package_template->installer_opts_cpnl_db_user); ?>" placeholder="<?php DUP_PRO_U::esc_attr_e('valid database user'); ?>" /></td>
                    </tr>
                </table>
            </div>
        </div><br/>
        <small><?php DUP_PRO_U::esc_html_e("All other inputs can be entered at install time.") ?></small>
        <br/><br/>

    </div>
</div>
<br/>

<button 
    class="button button-primary dup-save-template-btn" 
    type="submit"
    <?php disabled(($package_template->getId() > 0)); ?>
>
    <?php DUP_PRO_U::esc_html_e('Save Template'); ?>
</button>
</form>




<?php
$alert1          = new DUP_PRO_UI_Dialog();
$alert1->title   = DUP_PRO_U::__('Transfer Error');
$alert1->message = DUP_PRO_U::__('You can\'t exclude all sites!');
$alert1->initAlert();
?>

<script>
    jQuery(document).ready(function ($) {

        /* When installer brand changes preview button is updated */
        DupPro.Template.BrandChange = function ()
        {
            var $brand = $("#installer_opts_brand");
            var $id = $brand.val();
            var $url = new Array();

            <?php if (is_multisite()) : ?>
                $url = [
                    '<?php echo network_admin_url("admin.php?page=duplicator-pro-settings&tab=package&subtab=brand&view=edit&action=default"); ?>',
                    '<?php echo network_admin_url("admin.php?page=duplicator-pro-settings&tab=package&subtab=brand&view=edit&action=edit&id="); ?>' + $id];
            <?php else : ?>
                $url = [
                    '<?php echo get_admin_url(null, "admin.php?page=duplicator-pro-settings&tab=package&subtab=brand&view=edit&action=default"); ?>',
                    '<?php echo get_admin_url(null, "admin.php?page=duplicator-pro-settings&tab=package&subtab=brand&view=edit&action=edit&id="); ?>' + $id];
            <?php endif; ?>

          $("#brand-preview").attr('href', $url[ $id > 0 ? 1 : 0 ]);
        };

        /* Enables strike through on excluded DB table */
        DupPro.Template.ExcludeTable = function (check)
        {
            var $cb = $(check);
            if ($cb.is(":checked")) {
                $cb.closest("label").css('textDecoration', 'line-through');
            } else {
                $cb.closest("label").css('textDecoration', 'none');
            }
        }

        /* Enables visual for Database Only check */
        DupPro.Template.ExportOnlyDB = function ()
        {
            $('#dup-exportdb-items-off, #dup-exportdb-items-checked').hide();
            $("#archive_export_onlydb").is(':checked')
                    ? $('#dup-exportdb-items-checked').show()
                    : $('#dup-exportdb-items-off').show();
        };

        /* Formats file directory path name on seperate line of textarea */
        DupPro.Template.AddExcludePath = function (path)
        {
            var text = $("#_archive_filter_dirs").val() + path + ';\n';
            $("#_archive_filter_dirs").val(text);
        };

        /* Appends a path to the extention filter  */
        DupPro.Template.AddExcludeExts = function (path)
        {
            var text = $("#_archive_filter_exts").val() + path + ';';
            $("#_archive_filter_exts").val(text);
        };

        /* Formats file path name on seperate line of textarea */
        DupPro.Template.AddExcludeFilePath = function (path)
        {
            var text = $("#_archive_filter_files").val() + path + '/file.ext;';
            $("#_archive_filter_files").val(text.trim() + `\n`);
        };

        /* Used to duplicate a template */
        DupPro.Template.Copy = function ()
        {
            $("#dpro-template-form-action").val('copy-template');
            $("#dpro-template-form").parsley().destroy();
            $("#dpro-template-form").submit();
        };

        DupPro.Template.ToggleFileFilters = function ()
        {
            var readonly_enabled_disabled_items = $('#_archive_filter_dirs, #_archive_filter_exts, #_archive_filter_files');
            if ($("#archive_filter_on").is(':checked')) {
                readonly_enabled_disabled_items.prop('readonly', false).css({color: '#000'});
            } else {
                readonly_enabled_disabled_items.prop('readonly', true).css({color: '#999'});
            }
        };

        // Toggles Save Template button for existing Templates only
        DupPro.UI.formOnChangeValues($('#dpro-template-form'), function() {
            $('.dup-save-template-btn').prop('disabled', false);
        });

        //INIT
        $('#template-name').focus().select();
        $('#_archive_filter_dirs').val($('#_archive_filter_dirs').val().trim());
        $('#_archive_filter_files').val($('#_archive_filter_files').val().trim());
        //Default to cPanel tab if used
        $('#cpnl-enable').is(":checked") ? $('#dpro-cpnl-tab-lbl').trigger("click") : null;
        DupPro.EnableInstallerPassword();
        DupPro.Template.ExportOnlyDB();
        DupPro.Template.BrandChange();
        DupPro.Template.ToggleFileFilters();

        //MU-Transfer buttons
        $('#mu-include-btn').click(function () {
            return !$('#mu-exclude option:selected').remove().appendTo('#mu-include');
        });

        $('#mu-exclude-btn').click(function () {
            var include_all_count = $('#mu-include option').length;
            var include_selected_count = $('#mu-include option:selected').length;

            if (include_all_count > include_selected_count) {
                return !$('#mu-include option:selected').remove().appendTo('#mu-exclude');
            } else {
                <?php $alert1->showAlert(); ?>
            }
        });

        $('#dpro-template-form').submit(function () {
            DupPro.Pack.FillExcludeTablesList();
        });

        //Defaults to Installer cPanel tab if 'Auto Select cPanel' is checked
        $('#installer_opts_cpnl_enable').is(":checked") ? $('#dpro-cpnl-tab-lbl').trigger("click") : null;

    });
</script>
