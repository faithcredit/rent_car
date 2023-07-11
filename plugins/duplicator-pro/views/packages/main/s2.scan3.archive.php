<?php

use Duplicator\Installer\Core\Descriptors\ArchiveConfig;

defined("ABSPATH") or die("");

/**
 * @var ?DUP_PRO_Package $Package;
 * @var bool $archive_export_onlydb
 */


$dbbuild_mode      =  DUP_PRO_DB::getBuildMode();
$legacy_sql_string = ($Package->Database->Compatible) ? "<i style='color:maroon'>" . DUP_PRO_U::__('Compatibility Mode Enabled') . '</i>' : '';

/** @var wpdb $wpdb */
global $wpdb;

$global = DUP_PRO_Global_Entity::getInstance();
?>

<!-- ================================================================
ARCHIVE
================================================================ -->
<div class="details-title">
    <i class="far fa-file-archive fa-sm fa-fw"></i>&nbsp;<?php DUP_PRO_U::esc_html_e('Archive'); ?>
    <sup class="dup-small-ext-type">
        <?php
            $langPassRequired = DUP_PRO_U::__('Requires Password to Extract');
            echo $Package->Installer->isSecure() ? "<i class='fas fa-lock fa-fw fa-sm' title='{$langPassRequired}'></i>&nbsp;" : "";
            echo $global->get_archive_extension_type();
        ?>
    </sup>
    <div class="dup-more-details" onclick="DupPro.Pack.showDetailsDlg()" title="<?php DUP_PRO_U::esc_attr_e('Show Scan Details');?>">
        <i class="far fa-window-maximize"></i>
    </div>
</div>

<div class="scan-header scan-item-first">
    <i class="fas fa-folder-open fa-sm"></i>
    <?php DUP_PRO_U::esc_html_e("Files"); ?>
    <div class="scan-header-details">
        <div class="dup-scan-filter-status">
            <?php
            if ($archive_export_onlydb) {
                echo '<i class="fa fa-filter fa-sm"></i> ';
                DUP_PRO_U::esc_html_e('Database Only');
            } elseif ($Package->Archive->FilterOn) {
                echo '<i class="fa fa-filter fa-sm"></i> ';
                DUP_PRO_U::esc_html_e('Enabled');
            }
            ?>
        </div>

        <div id="data-arc-size1"></div>
        <i class="fa fa-question-circle data-size-help"
            data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("File Size:"); ?>"
            data-tooltip="<?php DUP_PRO_U::esc_html_e('The files size represents only the included files before compression is applied. It does not include the size of the database '
                . 'script and in most cases the package size once completed will be smaller than this number unless shell execution zip with no compression is enabled.'); ?>"></i>
        <div class="dup-data-size-uncompressed"><?php DUP_PRO_U::esc_html_e("uncompressed"); ?></div>
    </div>
</div>
<?php if ($archive_export_onlydb) { ?>
<div class="scan-item ">
    <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('Database only'); ?></div>
        <div id="only-db-scan-status"><div class="badge badge-warn"><?php DUP_PRO_U::esc_html_e("Notice"); ?></div></div>
    </div>
    <div class="info">
        <?php DUP_PRO_U::esc_html_e("Only the database and a copy of the installer.php will be included in the archive.zip file."); ?>
    </div>
</div>
<?php } elseif ($global->skip_archive_scan) { ?>
<div class="scan-item ">
    <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('File checks skipped'); ?></div>
        <div id="skip-archive-scan-status"><div class="badge badge-warn"><?php DUP_PRO_U::esc_html_e("Notice"); ?></div></div>
    </div>
    <div class="info">
        <?php DUP_PRO_U::esc_html_e("All file checks are skipped. This could cause problems during extraction if problematic files are included."); ?>
        <br><br>
        <b><?php DUP_PRO_U::esc_html_e("To enable, uncheck Packages > Advanced Settings > Scan File Checks > \"Skip\" to enable."); ?></b>

    </div>
</div>
    <?php
} else {
    ?>
<!-- ======================
SIZE CHECKS -->
<div class="scan-item">
    <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('Size Checks');?></div>
        <div id="data-arc-status-size"></div>
    </div>
    <div class="info" id="scan-item-file-size">
        <b><?php DUP_PRO_U::esc_html_e('Size'); ?>:</b> <span id="data-arc-size2"></span>  &nbsp; | &nbsp;
        <b><?php DUP_PRO_U::esc_html_e('Files'); ?>:</b> <span id="data-arc-files"></span>  &nbsp; | &nbsp;
        <b><?php DUP_PRO_U::esc_html_e('Directories '); ?>:</b> <span id="data-arc-dirs"></span>   &nbsp; | &nbsp;
        <b><?php DUP_PRO_U::esc_html_e('Total'); ?>:</b> <span id="data-arc-fullcount"></span>
        <br/>
        <?php
            echo wp_kses(DUP_PRO_U::__('Compressing larger sites on <i>some budget hosts</i> may cause timeouts.  '), array('i' => array()));
            echo "<i>&nbsp; <a href='javascipt:void(0)' onclick='jQuery(\"#size-more-details\").toggle(100); return false;'>[" . DUP_PRO_U::__('more details...') . "]</a></i>";
        ?>
        <div id="size-more-details">
            <?php
                echo "<b>" . DUP_PRO_U::__('Overview') . ":</b><br/>";
                $total_size_max = ($global->getBuildMode() == DUP_PRO_Archive_Build_Mode::ZipArchive)
                        ? DUPLICATOR_PRO_SCAN_SITE_ZIP_ARCHIVE_WARNING_SIZE
                        : DUPLICATOR_PRO_SCAN_SITE_WARNING_SIZE;

                printf(
                    DUP_PRO_U::__('This notice is triggered at <b>%s</b> and can be ignored on most hosts.  If the build process hangs or is unable to complete '
                    . 'then this host has strict processing limits.  Below are some options you can take to overcome constraints setup on this host.'),
                    DUP_PRO_U::byteSize($total_size_max)
                );

                echo '<br/><br/>';

                echo "<b>" . DUP_PRO_U::__('Timeout Options') . ":</b><br/>";
                echo '<ul>';
                echo '<li>' . DUP_PRO_U::__('Apply the "Quick Filters" below or click the back button to apply on previous page.') . '</li>';
                echo '<li>' .
                    DUP_PRO_U::__('See the FAQ link to adjust this hosts timeout limits: ') .
                    "&nbsp;<a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-100-q' target='_blank'>" .
                    DUP_PRO_U::__('What can I try for Timeout Issues?') .
                    '</a></li>';
                echo '</ul>';

                $hlptxt  = sprintf(
                    DUP_PRO_U::__('Files over %1$s are listed below. Larger files such as movies or zipped content can cause timeout issues on some budget hosts.  '
                    . 'If you are having issues creating a package try excluding the directory paths below or go back to Step 1 and add them.'),
                    DUP_PRO_U::byteSize(DUPLICATOR_PRO_SCAN_WARN_FILE_SIZE)
                );
                $hlptxt .= "<br><br><b>" . DUP_PRO_U::__('Right click on tree node to open the bulk actions menu') . '</b>';
            ?>
        </div>
        <div id="hb-files-large-result" class="dup-tree-section hb-files-style">
            <div class="container">
                <div class="hdrs">
                    <span style="font-weight:bold">
                        <?php DUP_PRO_U::esc_html_e('Quick Filters'); ?>
                        <sup><i class="fas fa-question-circle fa-sm" data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Large Files"); ?>" data-tooltip="<?php echo $hlptxt; ?>"></i></sup>
                    </span>
                    <div class='hdrs-up-down'>
                        <i class="fa fa-caret-up fa-lg dup-nav-toggle" onclick="DupPro.Pack.toggleAllDirPath(this, 'hide')" title="<?php DUP_PRO_U::esc_attr_e("Hide All"); ?>"></i>
                        <i class="fa fa-caret-down fa-lg dup-nav-toggle" onclick="DupPro.Pack.toggleAllDirPath(this, 'show')" title="<?php DUP_PRO_U::esc_attr_e("Show All"); ?>"></i>
                    </div>
                </div>
                <div class="tree-nav-bar" >
                    <div class="container">
                    <button type="button" id="hb-files-large-tree-full-load" class="tree-full-load-button show" >show all</button>
                    <span class="size">Size</span> <span class="nodes">Nodes</span>
                    </div>
                </div>
                <div class="data">
                    <div id="hb-files-large-jstree" class="dup-tree-main-wrapper"></div>
                </div>
            </div>
            <div class="apply-btn">
                <div class="apply-warn">
                     <?php DUP_PRO_U::esc_html_e('*Checking a directory will exclude all items in that path recursively.'); ?>
                </div>
                <button type="button" class="button-small duplicator-pro-quick-filter-btn" disabled="disabled" onclick="DupPro.Pack.applyFilters(this, 'large')">
                    <i class="fa fa-filter fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Add Filters &amp; Rescan');?>
                </button>
                <button type="button" class="button-small" onclick="DupPro.Pack.showPathsDlg('large')" title="<?php DUP_PRO_U::esc_attr_e('Copy Paths to Clipboard');?>">
                    <i class="fa far fa-clipboard" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================
ADDON SITES -->
<div id="addonsites-block"  class="scan-item">
    <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('Addon Sites');?></div>
        <div id="data-arc-status-addonsites"></div>
    </div>
    <div class="info">
        <div style="margin-bottom:10px;">
            <?php
                printf(DUP_PRO_U::__('An "Addon Site" is a separate WordPress site(s) residing in subdirectories within this site. If you confirm these to be separate sites, '
                    . 'then it is recommended that you exclude them by checking the corresponding boxes below and clicking the \'Add Filters & Rescan\' button.  To backup the other sites '
                    . 'install the plugin on the sites needing to be backed-up.'));
            ?>
        </div>
        <script id="hb-addon-sites" type="text/x-handlebars-template">
            <div class="container">
                <div class="hdrs">
                    <span style="font-weight:bold">
                        <?php DUP_PRO_U::esc_html_e('Quick Filters'); ?>
                    </span>
                </div>
                <div class="data">
                    {{#if ARC.FilterInfo.Dirs.AddonSites.length}}
                        {{#each ARC.FilterInfo.Dirs.AddonSites as |path|}}
                        <div class="directory">
                            <input type="checkbox" name="dir_paths[]" value="{{path}}" id="as_dir_{{@index}}"/>
                            <label for="as_dir_{{@index}}" title="{{path}}">
                                {{path}}
                            </label>
                        </div>
                        {{/each}}
                    {{else}}
                        <div class="data-padded">
                            <?php DUP_PRO_U::esc_html_e('No add on sites found.'); ?>
                         </div>
                    {{/if}}
                </div>
            </div>
            <div class="apply-btn">
                <div class="apply-warn">
                    <?php DUP_PRO_U::esc_html_e('*Checking a directory will exclude all items in that path recursively.'); ?>
                </div>
                <button type="button" class="button-small duplicator-pro-quick-filter-btn" disabled="disabled" onclick="DupPro.Pack.applyFilters(this, 'addon')">
                    <i class="fa fa-filter fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Add Filters &amp; Rescan');?>
                </button>
            </div>
        </script>
        <div id="hb-addon-sites-result" class="hb-files-style"></div>
    </div>
</div>

<!-- ======================
UNREADABLE FILES -->
<div id="scan-unreadable-items" class="scan-item">
    <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('Read Checks');?></div>
        <div id="data-arc-status-unreadablefiles"></div>
    </div>
    <div class="info">
        <?php
        echo wp_kses(DUP_PRO_U::__('PHP is unable to read the following items and they will <u>not</u> be included in the package.  Please work with your host to adjust the permissions or resolve the '
            . 'symbolic-link(s) shown in the lists below.  If these items are not needed then this notice can be ignored.'), array('u' => array()));
        ?>
        <script id="unreadable-files" type="text/x-handlebars-template">
            <div class="container">
                <div class="data-padded">
                    <b><?php DUP_PRO_U::esc_html_e('Unreadable Items:');?></b> <br/>
                    <div class="directory">
                        {{#if ARC.UnreadableItems}}
                            {{#each ARC.UnreadableItems as |uitem|}}
                                <i class="fa fa-lock fa-sm"></i> {{uitem}} <br/>
                            {{/each}}
                        {{else}}
                            <i>
                            <?php
                            DUP_PRO_U::esc_html_e('No unreadable items found.');
                            echo '<br>';
                            ?></i>
                        {{/if}}
                    </div>

                    <b><?php DUP_PRO_U::esc_html_e('Recursive Links:');?></b> <br/>
                    <div class="directory">
                        {{#if  ARC.RecursiveLinks}}
                            {{#each ARC.RecursiveLinks as |link|}}
                                <i class="fa fa-lock fa-sm"></i> {{link}} <br/>
                            {{/each}}
                        {{else}}
                            <i>
                                <?php
                                DUP_PRO_U::esc_html_e('No recursive sym-links found.');
                                echo '<br>';
                                ?></i>
                        {{/if}}
                    </div>
                </div>
            </div>
        </script>
        <div id="unreadable-files-result" class="hb-files-style"></div>
    </div>
</div>
    <?php
}

if (is_multisite()) { ?>
<div id="network-filters-scan-item" class="scan-item">
    <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('Network Site Filters');?></div>
        <div id="data-arc-status-network"></div>
    </div>
    <div class="info">
        <script id="hb-filter-network-sites" type="text/x-handlebars-template">
            <div class="container">
                <div class="data">
                    {{#if ARC.Status.HasFilteredSites}}
                        <p class="red">
                            <?php
                            DUP_PRO_U::esc_html_e(
                                "Some sites have been excluded from the network. " .
                                "With this package it will not be possible to restore the network but only perform subsite to standalone conversions " .
                                "if you have a Business or Gold license."
                            );
                            ?>
                        </p>
                        <b><?php DUP_PRO_U::esc_html_e('EXCLUDED SITES'); ?></b>
                        <ol>
                            {{#each ARC.FilteredSites as |site|}}
                            <li>{{site.blogname}} </li>
                            {{/each}}
                        </ol>
                    {{else}}
                        <?php DUP_PRO_U::esc_html_e("No network sites has been excluded from the package."); ?>
                    {{/if}}
                    {{#if ARC.Status.HasNotImportableSites}}
                    <p class="red">
                        <?php
                        DUP_PRO_U::esc_html_e(
                            "Tables and/or paths have been manually excluded from some sites so the package will not be compatible " .
                            "with the Drag and Drop import. An install using the installer.php can still be performed, however."
                        );
                        ?>
                    </p>
                    {{#each ARC.Subsites as |site|}}
                        {{#compare site.filteredTables.length '||' site.filteredPaths.length}}
                            <p><b>{{site.blogname}}</b></p>
                            <div class="subsite-filter-info">
                                {{#compare site.filteredTables.length '>' 0}}
                                    <?php DUP_PRO_U::esc_html_e('Tables:'); ?>
                                    <ol>
                                        {{#each site.filteredTables as |filteredTable|}}
                                        <li>{{filteredTable}}</li>
                                        {{/each}}
                                    </ol>
                                {{/compare}}
                                {{#compare site.filteredPaths.length '>' 0}}
                                <?php DUP_PRO_U::esc_html_e('Paths:'); ?>
                                <ol>
                                    {{#each site.filteredPaths as |filteredPath|}}
                                    <li>{{filteredPath}}</li>
                                    {{/each}}
                                </ol>
                                {{/compare}}
                            </div>
                        {{/compare}}
                    {{/each}}
                    {{/if}}
                </div>
            </div>
        </script>
        <div id="hb-filter-network-sites-result" ></div>
    </div>
</div>
    <?php
}
?>
<!-- ================================================================
DATABASE
================================================================ -->
<div class="scan-header">
    <i class="fas fa-database fa-fw fa-sm"></i>
    <?php DUP_PRO_U::esc_html_e("Database"); ?>
    <div class="scan-header-details">
        <small style="font-weight:normal; font-size:12px"><?php echo $legacy_sql_string ?></small>
        <div class="dup-scan-filter-status">
            <?php
            if ($Package->Database->FilterOn) {
                echo '<i class="fa fa-filter fa-sm"></i> ';
                DUP_PRO_U::esc_html_e('Enabled');
            }
            ?>
        </div>
        
        <div id="data-db-size1"></div>
        <i class="fa fa-question-circle data-size-help"
            data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Database Size:"); ?>"
            data-tooltip="<?php DUP_PRO_U::esc_html_e('The database size represents only the included tables. The process for gathering the size uses the query SHOW TABLE STATUS.  '
                        . 'The overall size of the database file can impact the final size of the package.'); ?>"></i>
        <div class="dup-data-size-uncompressed"><?php DUP_PRO_U::esc_html_e("uncompressed"); ?></div>


    </div>
</div>

<div id="dup-scan-db">
    <div class="scan-item">
        <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
            <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('Overview');?></div>
            <div id="data-db-status-size1"></div>
        </div>
        <div class="info">
            <?php echo '<b>' . DUP_PRO_U::__('TOTAL SIZE') . ' &nbsp; &#8667; &nbsp; </b>'; ?>
            <b><?php DUP_PRO_U::esc_html_e('Size'); ?>:</b> <span id="data-db-size2"></span> &nbsp; | &nbsp;
            <b><?php DUP_PRO_U::esc_html_e('Tables'); ?>:</b> <span id="data-db-tablecount"></span> &nbsp; | &nbsp;
            <b><?php DUP_PRO_U::esc_html_e('Records'); ?>:</b> <span id="data-db-rows"></span> <br/>
            <?php
            printf(
                DUP_PRO_U::__('Total size and row count are approximate values.  The thresholds that trigger warnings are <i>%1$s OR %2$s records</i> total for the entire database.  '
                . 'Large databases take time to process and can cause issues with server timeout and memory settings on some budget hosts.  If your server supports popen or exec '
                . 'and mysqldump you can try to enable Shell Execution from the settings menu.'),
                DUP_PRO_U::byteSize(DUPLICATOR_PRO_SCAN_DB_ALL_SIZE),
                number_format(DUPLICATOR_PRO_SCAN_DB_ALL_ROWS)
            );
            ?>
            <br/>
            <br/>
            <hr size="1" />
            <?php
            //TABLE DETAILS
            echo '<b>' . DUP_PRO_U::__('TABLE DETAILS:') . '</b><br/>';
            printf(
                DUP_PRO_U::__(
                    'The notices for tables are <i>%1$s, %2$s records or names with upper-case characters</i>. '
                    . 'Individual tables will not trigger '
                    . 'a notice message, but can help narrow down issues if they occur later on.'
                ),
                DUP_PRO_U::byteSize(DUPLICATOR_PRO_SCAN_DB_TBL_SIZE),
                number_format(DUPLICATOR_PRO_SCAN_DB_TBL_ROWS)
            );
            ?>
            <p>
                <b><?php printf(esc_html__('Exclude all tables without prefix "%s"', 'duplicator-pro'), $wpdb->prefix); ?>:</b>&nbsp;
                <i class="maroon" >
                    <?php echo ($Package->Database->prefixFilter ?
                        esc_html_e('Enabled', 'duplicator-pro') :
                        esc_html_e('Disabled', 'duplicator-pro')
                    ); ?>
                </i><br>
                <?php if (is_multisite()) { ?>
                    <b><?php esc_html_e('Exclude not existing subsite filter', 'duplicator-pro'); ?>:</b>&nbsp;
                    <i class="red" >
                        <?php echo ($Package->Database->prefixSubFilter ?
                            esc_html_e('Enabled', 'duplicator-pro') :
                            esc_html_e('Disabled', 'duplicator-pro')
                        ); ?>
                    </i>
                <?php } ?>
            </p>
            <div id="dup-scan-db-info">
                <div id="data-db-tablelist">
                </div>
            </div>
            <br/>
            <hr size="1" />
            <?php
            //RECOMMENDATIONS
            echo '<b>' . DUP_PRO_U::__('RECOMMENDATIONS:') . '</b><br/>';
            echo '<i>' . DUP_PRO_U::__('The following recommendations are not needed unless you are having issues building or installing the package.') . '</i><br/>';
            echo '<div style="padding:5px">';
            $lnk = '<a href="' . admin_url('maint/repair.php') . '" target="_blank">' . DUP_PRO_U::__('repair and optimization') . '</a>';
            printf(DUP_PRO_U::__('1. Run a %1$s on the table to improve the overall size and performance.'), $lnk);
            echo '<br/><br/>';
            _e('2. Remove post revisions and stale data from tables.  Tables such as logs, statistical or other non-critical data should be cleared.');
            echo '<br/><br/>';
            $lnk = '<a href="?page=duplicator-pro-settings&tab=package" target="_blank">' . DUP_PRO_U::__('Enable mysqldump') . '</a>';
            printf(DUP_PRO_U::__('3. %1$s if this host supports the option.'), $lnk);
            echo '<br/><br/>';
            $lnk = '<a href="http://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html#sysvar_lower_case_table_names" target="_blank">lower_case_table_names</a>';
            printf(DUP_PRO_U::__('4. Restoring mixed-case tables can cause problems on some servers. If you experience a problem installing the package change the %1$s system variable on the destination site\'s MySQL Server.'), $lnk);
            echo '</div>';
            ?>
        </div>
    </div>
    <?php
    $procedures = $GLOBALS['wpdb']->get_col("SHOW PROCEDURE STATUS WHERE `Db` = '{$GLOBALS['wpdb']->dbname}'", 1);
    $functions  = $GLOBALS['wpdb']->get_col("SHOW FUNCTION STATUS WHERE `Db` = '{$GLOBALS['wpdb']->dbname}'", 1);
    if (count($procedures) > 0 || count($functions) > 0) { ?>
    <div class="scan-item">
        <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
            <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('Object Access');?></div>
            <div id="data-arc-status-showcreatefunc"></div>
        </div>
        <div class="info">
            <script id="hb-showcreatefunc-result" type="text/x-handlebars-template">
                <div class="container">
                    <div class="data">
                        {{#if ARC.Status.showCreateFunc}}
                        <?php DUP_PRO_U::esc_html_e(
                            "The database user for this WordPress site has sufficient permissions to write stored procedures " .
                            "and functions to the sql file of the archive. [The commands SHOW CREATE PROCEDURE/FUNCTION will work.]"
                        ); ?>
                        {{else}}
                        <span style="color: red;">
                            <?php
                            DUP_PRO_U::esc_html_e("The database user for this WordPress site does NOT have sufficient permissions to write stored procedures to the sql file of the archive. [The command SHOW CREATE FUNCTION will NOT work.]");
                            ?>
                        </span>
                        {{/if}}
                    </div>
                </div>
            </script>
            <div id="showcreatefunc-package-result"></div>
        </div>
    </div>
    <?php } ?>
    <?php
    $triggers = $GLOBALS['wpdb']->get_col("SHOW TRIGGERS", 1);
    if (count($triggers)) { ?>
        <div class="scan-item">
            <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
                <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('Triggers');?></div>
                <div id="data-arc-status-triggers"></div>
            </div>
            <div class="info">
                <script id="hb-triggers-result" type="text/x-handlebars-template">
                    <div class="container">
                        <div class="data">
                             <span class="red">
                                <?php DUP_PRO_U::esc_html_e("The database contains triggers which will have to be manually imported at install time.  "
                                    . "No action needs to be performed at this time.  During the install process you will be presented with the proper trigger SQL statements "
                                    . "that you can optionally run."); ?>
                            </span>
                        </div>
                    </div>
                </script>
                <div id="triggers-result"></div>
            </div>
        </div>
    <?php } ?>
</div>
<br/>


<!-- ==========================================
DIALOGS:
========================================== -->
<?php
    $alert1          = new DUP_PRO_UI_Dialog();
    $alert1->height  = 645;
    $alert1->width   = 600;
    $alert1->title   = DUP_PRO_U::__('Scan Details');
    $alert1->message = "<div id='arc-details-dlg'></div>";
    $alert1->initAlert();

    $alert2          = new DUP_PRO_UI_Dialog();
    $alert2->height  = 485;
    $alert2->width   = 650;
    $alert2->title   = DUP_PRO_U::__('Copy Quick Filter Paths');
    $alert2->message = "<div id='arc-paths-dlg'></div>";
    $alert2->initAlert();

    $alert3          = new DUP_PRO_UI_Dialog();
    $alert3->title   = DUP_PRO_U::__('WARNING!');
    $alert3->message = DUP_PRO_U::__('Manual copy of selected text required on this browser.');
    $alert3->initAlert();

    $alert4          = new DUP_PRO_UI_Dialog();
    $alert4->title   = $alert3->title;
    $alert4->message = DUP_PRO_U::__('Error applying filters.  Please go back to Step 1 to add filter manually!');
    $alert4->initAlert();
?>

<!-- =======================
DIALOG: Scan Results -->
<div id="dup-archive-details" style="display:none">

    <!-- PACKAGE -->
    <h2><i class="fa fa-archive fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Package');?></h2>
    <div class="info">
        <label><?php DUP_PRO_U::esc_html_e('Name');?>:</label>
        <?php echo esc_html($_POST['package-name']); ?><br/>
        
        <label><?php DUP_PRO_U::esc_html_e('Notes');?>:</label>
        <?php echo strlen($_POST['package-notes']) ? esc_html($_POST['package-notes']) : DUP_PRO_U::__('- no notes -') ; ?> <br/>

        <label><?php DUP_PRO_U::esc_html_e('Archive Engine');?>:</label>
        <a href="?page=duplicator-pro-settings&tab=package" target="_blank"><?php echo esc_html($global->get_archive_engine()); ?></a> <br/>

        <label><?php DUP_PRO_U::esc_html_e('Encryption');?>:</label>
        <?php
        switch ($_POST['secure-on']) {
            case ArchiveConfig::SECURE_MODE_NONE:
                esc_html_e('Disabled', 'duplicator-pro');
                break;
            case ArchiveConfig::SECURE_MODE_INST_PWD:
                esc_html_e('Installer password (Password Required)', 'duplicator-pro');
                break;
            case ArchiveConfig::SECURE_MODE_ARC_ENCRYPT:
                esc_html_e('Archive encryption (Password Required)', 'duplicator-pro');
                break;
            default:
                throw new Exception('Invalid secure mode');
        }
        ?><br/>
    </div><br/>

    <!-- DATABASE -->
    <h2><i class="fas fa-database fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Database');?></h2>
    <div class="info">
        <label><?php DUP_PRO_U::esc_html_e('Name:');?></label><?php echo esc_html(DB_NAME); ?> <br/>
        <label><?php DUP_PRO_U::esc_html_e('Host:');?></label><?php echo esc_html(DB_HOST); ?> <br/>
        <label><?php DUP_PRO_U::esc_html_e('SQL Mode:');?></label> <a href="?page=duplicator-pro-settings&tab=package" target="_blank"><?php echo $dbbuild_mode ;?></a>
        <?php echo $legacy_sql_string ?>
    </div><br/>
    
    <!-- FILE FILTERS -->
    <h2 style="border:none">
        <i class="fa fa-filter fa-sm"></i> <?php DUP_PRO_U::esc_html_e('File Filters');?>:
        <small style="font-weight:none; font-style: italic">
            <?php echo ($Package->Archive->FilterOn) ? DUP_PRO_U::__('Is currently enabled') : DUP_PRO_U::__('Is currently disabled') ;?>
        </small>
    </h2>
    <div class="filter-area">
        <b><i class="fas fa-folder-open"></i> <?php echo esc_html(duplicator_pro_get_home_path());?></b>

        <script id="hb-filter-file-list" type="text/x-handlebars-template">
            <div class="file-info">
                <b>[<?php DUP_PRO_U::esc_html_e('Directories'); ?>]</b>
                <div class="file-info">
                    {{#if ARC.FilterInfo.Dirs.Instance}}
                        {{#each ARC.FilterInfo.Dirs.Instance as |dir|}}
                            {{stripWPRoot dir}}/<br/>
                        {{/each}}
                    {{else}}
                         <?php	DUP_PRO_U::esc_html_e('No custom directory filters set.');?>
                    {{/if}}
                </div>

                <b>[<?php DUP_PRO_U::esc_html_e('Files');   ?>]</b>
                <div class="file-info">
                    {{#if ARC.FilterInfo.Files.Instance}}
                        {{#each ARC.FilterInfo.Files.Instance as |file|}}
                            {{stripWPRoot file}}<br/>
                        {{/each}}
                    {{else}}
                         <?php	DUP_PRO_U::esc_html_e('No custom file filters set.');?>
                    {{/if}}
                </div>

                <b>[<?php DUP_PRO_U::esc_html_e('Auto Filters');?>]</b>
                <div class="file-info">
                    {{#each ARC.FilterInfo.Dirs.Global as |dir|}}
                        {{stripWPRoot dir}}/<br/>
                    {{/each}}
                </div>

            </div>
        </script>
        <div class="hb-filter-file-list-result"></div>

        <b>[<?php DUP_PRO_U::esc_html_e('Excluded File Extensions');?>]</b><br/>
        <?php
        if ($Package->Archive->FilterOn && strlen($Package->Archive->FilterExts)) {
            echo esc_html($Package->Archive->FilterExts);
        } else {
            DUP_PRO_U::esc_html_e('No file extension filters have been set.');
        }
        ?>
    </div>

    <small>
        <?php DUP_PRO_U::esc_html_e('Path filters will be skipped during the archive process when enabled.'); ?>
        <a href="<?php echo wp_nonce_url(DUPLICATOR_PRO_SITE_URL . '/wp-admin/admin-ajax.php?action=duplicator_pro_package_scan', 'duplicator_pro_package_scan', 'nonce'); ?>" target="dup_pro_report">
            <?php DUP_PRO_U::esc_html_e('[view json result report]');?>
        </a>
    </small><br/>
</div>

<!-- =======================
DIALOG: PATHS COPY & PASTE -->
<div id="dup-archive-paths" style="display:none">

    <b><i class="fa fa-folder"></i> <?php DUP_PRO_U::esc_html_e('Directories');?></b>
    <div class="copy-button">
        <button type="button" class="button button-small" onclick="DupPro.Pack.copyText(this, '#arc-paths-dlg textarea.path-dirs')">
            <i class="fa far fa-clipboard"></i> <?php DUP_PRO_U::esc_html_e('Click to Copy');?>
        </button>
    </div>
    <textarea class="path-dirs"></textarea>
    <br/><br/>

    <b><i class="fa fa-files fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Files');?></b>
    <div class="copy-button">
        <button type="button" class="button button-small" onclick="DupPro.Pack.copyText(this, '#arc-paths-dlg textarea.path-files')">
            <i class="fa far fa-clipboard"></i> <?php DUP_PRO_U::esc_html_e('Click to Copy');?>
        </button>
    </div>
    <textarea class="path-files"></textarea>
    <br/>
    <small><?php DUP_PRO_U::esc_html_e('Copy the paths above and apply them as needed on Step 1 &gt; Archive &gt; Files section.');?></small>
</div>


<script>
jQuery(document).ready(function($)
{
    var large_tree = $('#hb-files-large-jstree').length ? $('#hb-files-large-jstree') : null;

    Handlebars.registerHelper('stripWPRoot', function(path) {
        return  path.replace('<?php echo esc_js(duplicator_pro_get_home_path()) ?>', '');
    });

    Handlebars.registerHelper('ifAllOr', function (v1, v2, v3, options) {
        if (v1 || v2 || v3) {
            return options.fn(this);
        }

        return options.inverse(this);
    });

    Handlebars.registerHelper('compare', function (v1, operator, v2, options) {
        'use strict';
        var operators = {
            '==': v1 == v2 ? true : false,
            '===': v1 === v2 ? true : false,
            '!=': v1 != v2 ? true : false,
            '!==': v1 !== v2 ? true : false,
            '>': v1 > v2 ? true : false,
            '>=': v1 >= v2 ? true : false,
            '<': v1 < v2 ? true : false,
            '<=': v1 <= v2 ? true : false,
            '||': v1 || v2 ? true : false,
            '&&': v1 && v2 ? true : false
        }
        if (operators.hasOwnProperty(operator)) {
            if (operators[operator]) {
                return options.fn(this);
            }
            return options.inverse(this);
        }
        return console.error('Error: Expression "' + operator + '" not found');
    });

    //Opens a dialog to show scan details
    DupPro.Pack.filesOff = function (dir)
    {
        var $checks = $(dir).parent('div.directory').find('div.files input[type="checkbox"]');
        $(dir).is(':checked')
            ? $.each($checks, function() {$(this).attr({disabled : true, checked : false, title : '<?php DUP_PRO_U::esc_html_e('Directory applied filter set.');?>'});})
            : $.each($checks, function() {$(this).removeAttr('disabled checked title');});
    }

    //Opens a dialog to show scan details
    DupPro.Pack.showDetailsDlg = function ()
    {
        $('#arc-details-dlg').html($('#dup-archive-details').html());
        <?php $alert1->showAlert(); ?>
        return;
    }

    DupPro.Pack.FilterButton = {
        loading : function (btn) {
            $(btn).html('<i class="fas fa-circle-notch fa-spin"></i> <?php DUP_PRO_U::esc_html_e('Initializing Please Wait...');?>');
            $(btn).prop('disabled' , true);
            $('#dup-build-button').prop('disable' , true);
        },
        reset : function (btn) {
            $(btn).html('<i class="fa fa-filter fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Add Filters &amp; Rescan');?>');
            $(btn).prop('disabled' , true);
            $('#dup-build-button').prop('disable' , false);
        }
    };

    //Opens a dialog to show scan details
    DupPro.Pack.showPathsDlg = function (type)
    {
        var filters = DupPro.Pack.getFiltersLists(type);
        var dirFilters  = filters.dir;
        var fileFilters = filters.file;

        var $dirs  = $('#dup-archive-paths textarea.path-dirs');
        var $files = $('#dup-archive-paths textarea.path-files');
        (dirFilters.length > 0)
           ? $dirs.text(dirFilters.join(";\n"))
           : $dirs.text("<?php DUP_PRO_U::esc_html_e('No directories have been selected!');?>");

        (fileFilters.length > 0)
           ? $files.text(fileFilters.join(";\n"))
           : $files.text("<?php DUP_PRO_U::esc_html_e('No files have been selected!');?>");

        $('#arc-paths-dlg').html($('#dup-archive-paths').html());
        <?php $alert2->showAlert(); ?>

        return;
    };

    //Toggles a directory path to show files
    DupPro.Pack.toggleDirPath = function(item)
    {
        var $dir   = $(item).parents('div.directory');
        var $files = $dir.find('div.files');
        var $arrow = $dir.find('i.dup-nav');
        if ($files.is(":hidden")) {
            $arrow.addClass('fa-caret-down').removeClass('fa-caret-right');
            $files.show();
        } else {
            $arrow.addClass('fa-caret-right').removeClass('fa-caret-down');
            $files.hide(250);
        }
    }

    //Toggles a directory path to show files
    DupPro.Pack.toggleAllDirPath = function(chkBox, toggle)
    {
        var $dirs  = $(chkBox).parents('div.container').find('div.data div.directory');
         (toggle == 'hide')
            ? $.each($dirs, function() {$(this).find('div.files').show(); $(this).find('i.dup-nav').trigger('click');})
            : $.each($dirs, function() {$(this).find('div.files').hide(); $(this).find('i.dup-nav').trigger('click');});
    }

    DupPro.Pack.copyText = function(btn, query)
    {
        $(query).select();
         try {
           document.execCommand('copy');
           $(btn).css({color: '#fff', backgroundColor: 'green'});
           $(btn).text("<?php DUP_PRO_U::esc_html_e('Copied to Clipboard!');?>");
         } catch(err) {
           <?php $alert3->showAlert(); ?>
         }
    }

    DupPro.Pack.getFiltersLists = function(type) {
        var result = {
            'dir' : [],
            'file' : []
        };

        switch(type){
            case 'large':
                console.log(large_tree);
                if (large_tree) {
                    $.each(large_tree.jstree("get_checked",null,true), function(index, value){
                        var original = large_tree.jstree(true).get_node(value).original;
                        if (original.type.startsWith('folder')) {
                            result.dir.push(original.fullPath);
                        } else {
                            result.file.push(original.fullPath);
                        }
                    });
                }
                break;
            case 'addon':
                var id = '#hb-addon-sites-result';
                if ($(id).length) {
                    $(id + " input[name='dir_paths[]']:checked").each(function()  {result.dir.push($(this).val());});
                    $(id + " input[name='file_paths[]']:checked").each(function() {result.file.push($(this).val());});
                }
                break;
        }
        return result;
    };

    DupPro.Pack.applyFilters = function(btn, type)
    {
        var filterButton = btn;
        var filters = DupPro.Pack.getFiltersLists(type);
        var dirFilters  = filters.dir;
        var fileFilters = filters.file;

        if (dirFilters.length === 0 && fileFilters.length === 0) {
            alert('No filter selected');
            return false;
        }

        DupPro.Pack.FilterButton.loading(filterButton);
    
        var data = {
            action: 'DUP_PRO_CTRL_Package_addQuickFilters',
            nonce: '<?php echo wp_create_nonce('DUP_PRO_CTRL_Package_addQuickFilters'); ?>',
            dir_paths : dirFilters.join(";"),
            file_paths : fileFilters.join(";")
        };

        $.ajax({
            type: "POST",
            cache: false,
            dataType: "text",
            url: ajaxurl,
            timeout: 100000,
            data: data,
            complete: function() { },
            success:  function(respData) {
                try {
                    var data = DupPro.parseJSON(respData);
                } catch(err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    console.log(respData);
                    <?php $alert4->showAlert(); ?>
                    return false;
                }
               
                DupPro.Pack.reRunScanner(function () {
                    DupPro.Pack.FilterButton.reset(filterButton);
                    DupPro.Pack.fullLoadButtonInit();
                });
            },
            error: function(data) {
                console.log(data);
                <?php $alert4->showAlert(); ?>
            }
        });

        return false;
    };
    
    DupPro.Pack.treeContextMenu = function (node) {
        var items = {};
        if (node.type.startsWith('folder')) {
            items = {
                selectAll: { 
                    label: "<?php DUP_PRO_U::esc_html_e('Select all childs files and folders'); ?>",
                    action: function (obj) {
                        $(obj.reference).parent().find('> .jstree-children .warning-node > .jstree-anchor:not(.jstree-checked) .jstree-checkbox')
                            .each(function ()  {
                                var _this = $(this);
                                if (_this.parents('.selected-node').length === 0) {
                                    _this.trigger('click');
                                }
                            });
                    }
                },
                selectAllFiles: { 
                    label: "<?php DUP_PRO_U::esc_html_e('Select only all childs files'); ?>",
                    action: function (obj) {
                        $(obj.reference).parent().find('> .jstree-children .file-node.warning-node > .jstree-anchor:not(.jstree-checked) .jstree-checkbox')
                            .each(function ()  {
                                var _this = $(this);
                                if (_this.parents('.selected-node').length === 0) {
                                    _this.trigger('click');
                                }
                            });
                    }
                },
                unselectAll: { 
                    label: "<?php DUP_PRO_U::esc_html_e('Unselect all childs elements'); ?>",
                    action: function (obj) {
                        $(obj.reference).parent().find('> .jstree-children .jstree-node > .jstree-anchor.jstree-checked .jstree-checkbox').trigger('click');
                    }
                }
            };
        }
        return items;
    };
    
    DupPro.Pack.getTreeFolderUrlData =  function(folder, excludeList) {
        if (excludeList === undefined) {
            excludeList = [];
        }
        
        return {
            'nonce' : '<?php echo wp_create_nonce('duplicator_pro_get_folder_children'); ?>',
            'action' : 'duplicator_pro_get_folder_children',
            'folder' : folder,
            'exclude' : excludeList
        };
    };
    
    DupPro.Pack.getTreeFolderUrl = function(folder, excludeList) {
        return ajaxurl + '?' + $.param(DupPro.Pack.getTreeFolderUrlData(folder, excludeList));
    };
    
    DupPro.Pack.fullLoadNodes = null;

    DupPro.Pack.fullLoadFolder = function(tree, index, sectionContainer) {
        if (Array.isArray(DupPro.Pack.fullLoadNodes) && index < DupPro.Pack.fullLoadNodes.length) {
            var parent = DupPro.Pack.fullLoadNodes[index];
            if (index === 0 && sectionContainer) {
                sectionContainer.append('<div class="tree-loader" >' + 
                        '<div class="container-wrapper" >' + 
                        '<i class="fa fa-cog fa-lg fa-spin"></i> <span></span>' +
                        '</div>' + 
                        '</div>');
            }
            sectionContainer.find('.tree-loader span').text('Loading ' + parent.original.fullPath);
        } else {
            DupPro.Pack.fullLoadNodes = null;
            if (sectionContainer) {
                sectionContainer.find('.tree-loader').remove();
            }
            return;
        }
        var excludeList = [];
        
        var parentClass = parent.li_attr.class;        
        if (parentClass.indexOf('root-node') !== -1 && parentClass.indexOf('no-warnings') !== -1) {
            tree.delete_node(parent.children[0]);
        } else {
            for (i = 0; i < parent.children.length; i++) { 
                excludeList.push(tree.get_node(parent.children[i]).original.fullPath.replace(/^.*[\\\/]/, ''));
            }
        }
        var data = DupPro.Pack.getTreeFolderUrlData(parent.original.fullPath, excludeList);
        $.ajax({
            type: "GET",
            cache: false,
            data: data,
            dataType: "text",
            url: ajaxurl,
            timeout: 100000,
            //data: data,
            complete: function() { },
            success:  function(respData) {
                try {
                    var data = DupPro.parseJSON(respData);
                    for (i = 0; i < data.length; i++) {
                        tree.create_node(parent, data[i]);
                    }
                    DupPro.Pack.fullLoadFolder(tree, index + 1, sectionContainer);
                } catch(err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    console.log(respData);
                    <?php $alert4->showAlert(); ?>
                    return false;
                }
            },
            error: function(data) {
                console.log(data);
                <?php $alert4->showAlert(); ?>
            }
        });
    };
    
    DupPro.Pack.fullLoadButtonInit = function () {
        $('.tree-full-load-button')
                .removeClass('isLoaded')
                .removeClass('hide')
                .addClass('show')
                .text('<?php echo DUP_PRO_U::_e('show all')?>')
                .off()
                .click(function () {   
                    var sectionContainer = $(this).closest('.dup-tree-section').find('> .container');
                    var cObj = $(this);
                    var domTree = sectionContainer.find(".dup-tree-main-wrapper");
                    var tree = domTree.jstree(true);

                    if (cObj.hasClass('show')) {
                        cObj.removeClass('show').addClass('hide').text('<?php echo DUP_PRO_U::_e('show warning only')?>');;
                        if (!cObj.hasClass('isLoaded')) {
                            cObj.addClass('isLoaded');
                            DupPro.Pack.fullLoadNodes = [];
                            domTree.find(".folder-node[data-full-loaded=false]").each(function () {
                                var parent = tree.get_node($(this));
                                if (parent.state.loaded === false ) {
                                    // If loaded it is false the folder has never been opened then it will be loaded by jstree if it is opened.
                                    return;
                                }
                                DupPro.Pack.fullLoadNodes.push(parent);
                            });

                            if (DupPro.Pack.fullLoadNodes.length) {
                                DupPro.Pack.fullLoadFolder(tree, 0, sectionContainer);
                            } else {
                                DupPro.Pack.fullLoadNodes = null;
                            }
                        } else {
                            domTree.find(".root-node .jstree-node:not(.warning-childs):not(.warning-node)").each(function () {
                                // don't use the tree functions show_node and hide_node are too slow.
                                $(this).removeClass('jstree-hidden');
                            });   
                        }
                    } else {
                        cObj.removeClass('hide').addClass('show').text('<?php echo DUP_PRO_U::_e('show all')?>');
                        domTree.find(".root-node .jstree-node:not(.warning-node):not(.warning-childs)").each(function () {
                            // don't use the tree functions show_node and hide_node are too slow.
                            $(this).addClass('jstree-hidden');
                        });
                    }

                    // recalculate the last child manually
                    domTree.find(".jstree-children").each(function () {
                        $(this).find('> li:not(.jstree-hidden)').removeClass('jstree-last').last().addClass('jstree-last');
                    });

                });
    };
    
    DupPro.Pack.initTree = function(tree , data , filterBtn) {
        var treeObj = tree;
        var nameData =  data;
        console.log('nameData' ,nameData);

        treeObj.jstree('destroy');
        treeObj.jstree({
            'core' : {
                "check_callback": true,
                'cache' : false,
                //'data' : nameData,
                "themes": {
                    "name": "snap",
                    "dots": true,
                    "icons": true,
                    "stripes" : true,
                },
                'data' : {
                    'url' : function (node) {                        
                        var folder = (node.id === '#') ? '' : node.original.fullPath;
                        return DupPro.Pack.getTreeFolderUrl(folder);
                    },
                    'data' : function (node) {
                        return { 'id' : node.id };
                    }
                }
            },
            'types': {
                "folder": {
                    "icon": "jstree-icon jstree-folder",
                    "li_attr" : {
                      "class" : 'folder-node'
                  }
              },
              "file": {
                  "icon": "jstree-icon jstree-file",
                  "li_attr" : {
                      "class" : 'file-node'
                  }
              },
              "info-text": {
                  "icon": "jstree-noicon",
                  "li_attr" : {
                      "class" : 'info-node'
                  }
              }
          },
          "checkbox" : {
              visible               : true, // a boolean indicating if checkboxes should be visible (can be changed at a later time using `show_checkboxes()` and `hide_checkboxes`). Defaults to `true`.
              three_state           : false, // a boolean indicating if clicking anywhere on the node should act as clicking on the checkbox. Defaults to `true`.
              whole_node            : false, // a boolean indicating if clicking anywhere on the node should act as clicking on the checkbox. Defaults to `true`.
              keep_selected_style   : false, // a boolean indicating if the selected style of a node should be kept, or removed. Defaults to `true`.
              cascade               : '',  // This setting controls how cascading and undetermined nodes are applied.
                                          // If 'up' is in the string - cascading up is enabled, if 'down' is in the string - cascading down is enabled, if 'undetermined' is in the string - undetermined nodes will be used.
                                          // If `three_state` is set to `true` this setting is automatically set to 'up+down+undetermined'. Defaults to ''./
              tie_selection     : false, // This setting controls if checkbox are bound to the general tree selection or 
                                         // to an internal array maintained by the checkbox plugin. Defaults to `true`, only set to `false` if you know exactly what you are doing.
              cascade_to_disabled : false, // This setting controls if cascading down affects disabled checkboxes
              cascade_to_hidden   : false   //This setting controls if cascading down affects hidden checkboxes
          },
          "contextmenu" : {
              "items" : DupPro.Pack.treeContextMenu
          },
          "plugins" : [
              "checkbox",
              "contextmenu",
              "types",
              //"dnd",
              //"massload",
              //"search",
              //"sort",
              //"state",
              //"types",
              //"unique",
              //"wholerow",
              "changed",
              //"conditionalselect"
          ]
      }).on('check_node.jstree', function (e, data) {
            treeObj.find('#' + data.node.id).addClass('selected-node');
            filterBtn.prop("disabled", false);
      }).on('uncheck_node.jstree' , function (e, data) {
            treeObj.find('#' + data.node.id).removeClass('selected-node');
            if (treeObj.jstree("get_selected").length === 0) {
                filterBtn.prop("disabled", true);
            }
      }).on('ready.jstree', function () {
            // insert data
            tree.jstree(true).create_node(null, nameData);
      });

    };

    DupPro.Pack.initArchiveFilesData = function(data)
    {
        //TOTAL SIZE
        //var sizeChecks = data.ARC.Status.Size == 'Warn' || data.ARC.Status.Big == 'Warn' ? 'Warn' : 'Good';
        $('#data-arc-status-size').html(DupPro.Pack.setScanStatus(data.ARC.Status.Size));
        $('#data-arc-status-unreadablefiles').html(DupPro.Pack.setScanStatus(data.ARC.Status.UnreadableItems));
        $('#data-arc-status-migratepackage').html(DupPro.Pack.setScanStatus(data.ARC.Status.PackageIsNotImportable ? 'Warn' : 'Good'));
        $('#data-arc-status-showcreatefunc').html(DupPro.Pack.setScanStatus(data.ARC.Status.showCreateFuncStatus));
        $('#data-arc-status-network').html(DupPro.Pack.setScanStatus(data.ARC.Status.Network));
        $('#data-arc-status-triggers').html(DupPro.Pack.setScanStatus(data.DB.Status.Triggers));

        $('#data-arc-size1').text(data.ARC.Size || errMsg);
        $('#data-arc-size2').text(data.ARC.Size || errMsg);
        $('#data-arc-files').text(data.ARC.FileCount || errMsg);
        $('#data-arc-dirs').text(data.ARC.DirCount || errMsg);
        $('#data-arc-fullcount').text(data.ARC.FullCount || errMsg);

        //LARGE FILES
        if ($("#hb-files-large-result").length) {
            DupPro.Pack.initTree(
                    large_tree ,
                    data.ARC.FilterInfo.TreeSize ,
                    $("#hb-files-large-result .duplicator-pro-quick-filter-btn")
                    );
        }

        //ADDON SITES
        if ($("#hb-addon-sites").length) {
            var template = $('#hb-addon-sites').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#hb-addon-sites-result').html(html);
        }

        //UNREADABLE FILES
        if ($("#unreadable-files").length) {
            var template = $('#unreadable-files').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#unreadable-files-result').html(html);
        }


        //SCANNER DETAILS: Dirs
        if ($("#hb-filter-file-list").length) {
            var template = $('#hb-filter-file-list').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('div.hb-filter-file-list-result').html(html);
        }
        
        //NETWORK SITES
        if ($("#hb-filter-network-sites").length) {
            var template = $('#hb-filter-network-sites').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#hb-filter-network-sites-result').html(html);
        }
      
        //MIGRATE PACKAGE
        if ($("#hb-migrate-package-result").length) {
            var template = $('#hb-migrate-package-result').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#migrate-package-result').html(html);
        }

        //Security Plugins
        if ($("#hb-dup-security-plugins").length) {
            var template = $('#hb-dup-security-plugins').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#dup-security-plugins').html(html);
        }

        //SHOW CREATE
        if ($("#hb-showcreatefunc-result").length) {
            var template = $('#hb-showcreatefunc-result').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#showcreatefunc-package-result').html(html);
        }

        //TRIGGERS
        if ($("#hb-triggers-result").length) {
            var template = $('#hb-triggers-result').html();
            var templateScript = Handlebars.compile(template);
            var html = templateScript(data);
            $('#triggers-result').html(html);
        }

        DuplicatorTooltip.reload();
    };
    
    DupPro.Pack.fullLoadButtonInit();

    $("#form-duplicator").on('change', "#hb-files-large-result input[type='checkbox'], #hb-addon-sites-result input[type='checkbox']", function() {       
        if ($("#hb-addon-sites-result input[type='checkbox']:checked").length) {
            var addon_disabled_prop = false;
        } else {
            var addon_disabled_prop = true;
        }
        $("#hb-addon-sites-result .duplicator-pro-quick-filter-btn").prop("disabled", addon_disabled_prop);         
    });
});
</script>
