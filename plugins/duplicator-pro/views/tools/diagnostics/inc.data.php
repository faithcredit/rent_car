<?php

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\CapMng;
use Duplicator\Core\MigrationMng;

defined("ABSPATH") or die("");

global $wpdb;

$orphaned_filepaths = DUP_PRO_Server::getOrphanedPackageFiles();
$view_state         = DUP_PRO_UI_ViewState::getArray();
$ui_css_data_panel  = (isset($view_state['dup-settings-diag-opts-panel']) && $view_state['dup-settings-diag-opts-panel']) ? 'display:block' : 'display:none';
$ui_css_data_panel  = (isset($_GET['orphanpurge']) && $_GET['orphanpurge'] == '1') ? 'display:block' : $ui_css_data_panel;

?>
<!-- ==============================
STORED DATA -->
<div class="dup-box">
    <div class="dup-box-title">
        <i class="fas fa-th-list fa-sm"></i>
        <?php DUP_PRO_U::esc_html_e("Stored Data"); ?>
        <button class="dup-box-arrow">
            <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Stored Data') ?></span>
        </button>
    </div>
    <div class="dup-box-panel" id="dup-settings-diag-opts-panel" style="padding:0px 20px 0px 25px; <?php echo esc_attr($ui_css_data_panel) ?>" >
        <h3 class="title" style="margin-left:-15px"><?php DUP_PRO_U::esc_html_e("Data Cleanup") ?> </h3>
        <table class="dpro-reset-opts">
            <tr valign="top">
                <td>
                    <button type="button" class="dpro-store-fixed-btn button button-small" id="dpro-remove-installer-files-btn" onclick="DupPro.Tools.removeInstallerFiles()">
                        <?php DUP_PRO_U::esc_html_e("Delete Installation Files"); ?>
                    </button>
                </td>
                <td>
                    <?php DUP_PRO_U::esc_html_e("Removes all reserved installation files."); ?>
                    <a href="javascript:void(0)" onclick="jQuery('#dpro-tools-delete-moreinfo').toggle()">[<?php DUP_PRO_U::esc_html_e("more info"); ?>]</a>
                    <br/>
                    <div id="dpro-tools-delete-moreinfo">
                        <p>
                            <?php
                            DUP_PRO_U::esc_html_e("Clicking on the 'Remove Installation Files' button will remove the following installation files.  These files are typically from a previous Duplicator install. "
                                . "If you are unsure of the source, please validate the files.  These files should never be left on production systems for security reasons.  "
                                . "Below is a list of all the installation files used by Duplicator.  Please be sure these are removed from your server.");
                            ?>
                        <p>
                        <p>
                            <?php
                            foreach (MigrationMng::getGenericInstallerFiles() as $instFileName) {
                                ?>
                                <span class="success">
                                    <?php echo esc_html($instFileName); ?>
                                </span><br>
                                <?php
                            }
                            ?>
                        </p>
                    </div>
                </td>
            </tr>
            <?php if (CapMng::can(CapMng::CAP_CREATE, false)) { ?>
            <tr valign="top">
                <td>
                    <a 
                        type="button" 
                        class="dpro-store-fixed-btn button button-small" 
                        href="<?php echo esc_url(ToolsPageController::getInstance()->getPurgeOrphanActionUrl()); ?>" 
                    >
                        <?php DUP_PRO_U::esc_html_e("Delete Package Orphans"); ?>
                    </a>
                </td>
                <td>
                    <?php DUP_PRO_U::esc_html_e("Removes all package files NOT found in the packages screen."); ?>
                    <a href="javascript:void(0)" onclick="jQuery('#dpro-tools-delete-orphans-moreinfo').toggle()">[<?php DUP_PRO_U::esc_html_e("more info"); ?>]</a>
                    <br/>
                    <div id="dpro-tools-delete-orphans-moreinfo">
                        <?php
                        if (count($orphaned_filepaths) > 0) {
                            DUP_PRO_U::esc_html_e("Clicking on the 'Delete Package Orphans' button will remove the following files.  "
                                . "Orphaned files are typically generated from previous installations of Duplicator. They may also exist if they did not get properly removed "
                                . "when they were selected from the main packages screen.  The files below are no longer associated with active packages in the main "
                                . "Packages screen and should be safe to remove. <b>IMPORTANT: Don't click button if you want to retain any of the following files:</b>");
                            echo "<br/><br/>";

                            foreach ($orphaned_filepaths as $filepath) {
                                echo "<div class='failed'><i class='fa fa-exclamation-triangle'></i> " . esc_html($filepath) . " </div>";
                            }
                        } else {
                            DUP_PRO_U::esc_html_e('No orphaned package files found.');
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <button type="button" class="dpro-store-fixed-btn button button-small" onclick="DupPro.Tools.ClearBuildCache()">
                        <?php DUP_PRO_U::esc_html_e("Clear Build Cache"); ?>
                    </button>
                </td>
                <td><?php DUP_PRO_U::esc_html_e('Removes all build data from:'); ?> [<?php echo esc_html(DUPLICATOR_PRO_SSDIR_PATH_TMP); ?>].</td>
            </tr>
            <?php } ?>
        </table>
        <br/>

        <?php if (CapMng::can(CapMng::CAP_SETTINGS, false)) { ?>
        <h3 class="title" style="margin-left:-15px"><?php DUP_PRO_U::esc_html_e("Options Values") ?> </h3>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php DUP_PRO_U::esc_html_e("Key") ?> <i>duplicator_pro_</i></th>
                    <th>&nbsp; <?php DUP_PRO_U::esc_html_e("Value") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql    = "SELECT * FROM `{$wpdb->base_prefix}options` WHERE  `option_name` LIKE  '%duplicator_pro_%' ORDER BY option_name";
                $global = DUP_PRO_Global_Entity::getInstance();

                foreach ($wpdb->get_results("{$sql}") as $key => $row) :
                    if (($global->license_key_visible === License::VISIBILITY_ALL) || ($row->option_name != 'duplicator_pro_license_key')) {
                        ?>
                        <tr>
                            <td>
                                <?php
                                $key_name = str_replace('duplicator_pro_', '', $row->option_name);

                                echo (in_array($row->option_name, $GLOBALS['DUPLICATOR_PRO_OPTS_DELETE'])) ? "<a href='javascript:void(0)' onclick='DupPro.Settings.DeleteOption(this)'>" . esc_html($key_name) . "</a>" : esc_html($key_name);
                                ?>
                            </td>
                            <td><textarea class="dup-opts-read" readonly="readonly"><?php echo esc_textarea($row->option_value); ?></textarea></td>
                        </tr>
                        <?php
                    }
                endforeach;
                ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
</div>
<br/>
