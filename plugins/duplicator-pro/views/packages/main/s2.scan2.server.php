<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Addons\ProBase\License\License;

/**
 * Variables
 *
 * @var ?DUP_PRO_Package $Package
 * @var bool $archive_export_onlydb
 */

$global = DUP_PRO_Global_Entity::getInstance();

global $wp_version;
?>
<!-- ================================================================
SETUP
================================================================ -->
<div class="details-title">
    <i class="fas fa-tasks fa-sm fa-fw"></i> <?php DUP_PRO_U::esc_html_e("Setup"); ?>
    <div class="dup-more-details">
        <a href="?page=duplicator-pro-tools&tab=diagnostics" target="_blank" title="<?php DUP_PRO_U::esc_attr_e('Show Diagnostics'); ?>"><i class="fa fa-microchip"></i></a>&nbsp;
        <a href="site-health.php" target="_blank" title="<?php DUP_PRO_U::esc_attr_e('Site Health'); ?>"><i class="fas fa-file-medical-alt"></i></a>
    </div>
</div>

<!-- ==========================
SYSTEM SETTINGS -->
<div class="scan-item scan-item-first">
    <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('System'); ?></div>
        <div id="data-srv-php-all"></div>
    </div>
    <div class="info">
        <?php
        //DIVIDER
        echo "<div class='scan-system-divider'><i class='fa fa-list'></i>&nbsp;" . DUP_PRO_U::__('General Checks') . "</div>";

        if (License::can(License::CAPABILITY_BRAND)) :
            ?>
            <span id="data-srv-brand-check"></span>&nbsp;<b><?php DUP_PRO_U::esc_html_e('Brand'); ?>: </b>&nbsp;<span id="data-srv-brand-name"><?php DUP_PRO_U::esc_html_e('Default'); ?></span><br />
            <div class="scan-system-subnote" id="data-srv-brand-note"><?php DUP_PRO_U::esc_html_e('The default content used when a brand is not defined.'); ?></div>
            <hr size="1" />
            <?php
        endif;
        //WEB SERVER
        $web_servers = implode(', ', $GLOBALS['DUPLICATOR_PRO_SERVER_LIST']);
        echo '<span id="data-srv-php-websrv"></span>&nbsp;<b>' . DUP_PRO_U::__('Web Server') . ":</b>&nbsp; '{$_SERVER['SERVER_SOFTWARE']}' <br/>";
        echo '<div class="scan-system-subnote">';
        DUP_PRO_U::esc_html_e("Supported Web Servers:");
        echo "&nbsp;{$web_servers}";
        echo '</div>';

         //MYSQLI
        echo '<hr size="1" /><span id="data-srv-php-mysqli"></span>&nbsp;<b>' . DUP_PRO_U::__('MySQLi') . "</b> <br/>";
        echo '<div class="scan-system-subnote">';
        DUP_PRO_U::esc_html_e('Creating the package does not require the mysqli module.  However the installer file requires that the PHP module mysqli be installed on the server it is deployed on.');
        echo "&nbsp;<i><a href='http://php.net/manual/en/mysqli.installation.php' target='_blank'>[" . DUP_PRO_U::__('details') . "]</a></i>";
        echo '</div>';

        //DROPBOX ONLY
        if ($Package->contains_storage_type(DUP_PRO_Storage_Types::Dropbox)) {
            //OPENSSL
            echo '<hr size="1" /><span id="data-srv-php-openssl"></span>&nbsp;<b>' . DUP_PRO_U::__('Open SSL - Dropbox') . '</b>';
            echo '<div class="scan-system-subnote">';
            DUP_PRO_U::esc_html_e('Dropbox storage requires an HTTPS connection. On windows systems enable "extension=php_openssl.dll" in the php.ini configuration file.  ');
            DUP_PRO_U::esc_html_e('On Linux based systems check for the --with-openssl[=DIR] flag.');
            echo "&nbsp;<i><a href='http://php.net/manual/en/openssl.installation.php' target='_blank'>[" . DUP_PRO_U::__('details') . "]</a></i>";
            echo '</div>';

            if ($global->dropbox_transfer_mode == DUP_PRO_Dropbox_Transfer_Mode::FOpen_URL) {
                //FOpen
                $test = DUP_PRO_Server::isURLFopenEnabled();
                echo '<hr size="1" /><span id="data-srv-php-allowurlfopen"></span>&nbsp;<b>' . DUP_PRO_U::__('Allow URL Fopen') . ":</b>&nbsp; '{$test}'<br/>";
                echo '<div class="scan-system-subnote">';
                DUP_PRO_U::esc_html_e('Dropbox communications requires that [allow_url_fopen] be set to 1 in the php.ini file.');
                echo "&nbsp;<i><a href='http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen' target='_blank'>[" . DUP_PRO_U::__('details') . "]</a></i><br/>";
                echo '</div>';
            } elseif ($global->dropbox_transfer_mode == DUP_PRO_Dropbox_Transfer_Mode::cURL) {
                //FOpen
                $test = DUP_PRO_Server::isCurlEnabled() ? DUP_PRO_U::__('True') : DUP_PRO_U::__('False');
                echo '<hr size="1" /><span id="data-srv-php-curlavailable"></span>&nbsp;<b>' . DUP_PRO_U::__('cURL - Dropbox') . ":</b>&nbsp; '{$test}'<br/>";
                echo '<div class="scan-system-subnote">';
                DUP_PRO_U::esc_html_e('Dropbox communications requires that extension=php_curl.dll be present in the php.ini file.');
                echo "&nbsp;<i><a href='http://php.net/manual/en/curl.installation.php' target='_blank'>[" . DUP_PRO_U::__('details') . "]</a></i><br/>";
                echo '</div>';
            }
        }

        //DIVIDER
        echo "<div class='scan-system-divider margin-top-1'><i class='fa fa-list'></i>&nbsp;" . DUP_PRO_U::__('PHP Checks') . "</div>";

        //PHP VERSION
        echo '<span id="data-srv-php-version"></span>&nbsp;<b>' . DUP_PRO_U::__('PHP Version: ') . "</b>" . PHP_VERSION . " <br/>";
        echo '<div class="scan-system-subnote">';
        printf(
            DUP_PRO_U::esc_html__(
                'The minimum PHP version supported by Duplicator is %1$s, however it is highly recommended to use PHP %2$s or higher for improved stability.'
            ),
            DUPLICATOR_PRO_PHP_MINIMUM_VERSION,
            DUPLICATOR_PRO_PHP_SUGGESTED_VERSION
        );
        echo "&nbsp;<i><a href='http://php.net/ChangeLog-5.php' target='_blank'>[" . DUP_PRO_U::__('details') . "]</a></i>";
        echo '</div>';

        //OPEN_BASEDIR
        $openBaseDir = ini_get("open_basedir");
        $test        = empty($openBaseDir) ? 'off' : 'on';
        echo '<hr size="1" /><span id="data-srv-php-openbase"></span>&nbsp;<b>' . DUP_PRO_U::__('PHP Open Base Dir') . ":</b>&nbsp; '{$test}' <br/>";
        echo '<div class="scan-system-subnote">';
        DUP_PRO_U::esc_html_e('Issues might occur when [open_basedir] is enabled. Work with your server admin or hosting provider to disable this value in the php.ini file if you’re having issues building a package.');
        echo "&nbsp;<i><a href='http://php.net/manual/en/ini.core.php#ini.open-basedir' target='_blank'>[" . DUP_PRO_U::__('details') . "]</a></i><br/>";
        echo '</div>';

        //MAX_EXECUTION_TIME
        $test = (set_time_limit(0)) ? 0 : ini_get("max_execution_time");
        echo '<hr size="1" /><span id="data-srv-php-maxtime"></span>&nbsp;<b>' . DUP_PRO_U::__('PHP Max Execution Time') . ":</b>&nbsp; '{$test}' <br/>";
        echo '<div class="scan-system-subnote">';
        printf(
            DUP_PRO_U::__(
                'Issues might occur for larger packages when the [max_execution_time] value in the php.ini is too low. ' .
                'The minimum recommended timeout is "%1$s" seconds or higher. An attempt is made to override this value if the server allows it. ' .
                'A value of 0 (recommended) indicates that PHP has no time limits.'
            ),
            DUPLICATOR_PRO_SCAN_TIMEOUT
        );
        echo "&nbsp;<i><a href='http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time' target='_blank'>[" . DUP_PRO_U::__('details') . "]</a></i>";
        echo '</div>';

        //MEMORY_LIMIT
        $test = @ini_get("memory_limit");
        echo '<hr size="1" /><span id="data-srv-php-minmemory"></span>&nbsp;<b>' . DUP_PRO_U::__('PHP Memory Limit') . ":</b>&nbsp; '{$test}' <br/>";
        echo '<div class="scan-system-subnote">';
        printf(
            DUP_PRO_U::__('Issues might occur for larger packages when the [memory_limit] value in the php.ini is too low.  The minimum recommended memory limit '
                . 'is "%s" or higher. An attempt is made to override this value if the server allows it. To manually increase the memory limit have a look at this'
                . ' %s[FAQ item]%s'),
            DUPLICATOR_PRO_MIN_MEMORY_LIMIT,
            "<i><a href='https://snapcreek.com/duplicator/docs/faqs-tech/?210328131212#faq-trouble-056-q' target='_blank'>",
            "</a></i>"
        );
        echo '</div>';

        //PHP 32-bit
        $test = SnapUtil::getArchitectureString();
        echo '<hr size="1" /><span id="data-srv-php-arch64bit"></span>&nbsp;<b>' . DUP_PRO_U::__('PHP 64 Bit Architecture') . ":</b>&nbsp; '{$test}' <br/>";
        echo '<div class="scan-system-subnote">';
        printf(
            DUP_PRO_U::__('Servers that run a PHP 32-bit architecture are not capable of creating packages larger than 2GB.   If you need to create a package that '
                . 'is larger than 2GB in size talk with your host or server admin to change your version of PHP to 64-bit. %s[FAQ item]%s'),
            "<i><a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-package-145-q' target='_blank'>",
            "</a></i>"
        );
        echo '</div>';

        ?><br/>
    </div>
</div>


<!-- ======================
WP SETTINGS -->
<div class="scan-item">
    <?php
    if (!$archive_export_onlydb && isset($_POST['filter-on'])) {
        $file_filter_data        = array(
            'filter-dir' => DUP_PRO_Archive::parsePathFilter(SnapUtil::sanitizeNSChars($_POST['filter-dirs'])),
            'filter-files' => DUP_PRO_Archive::parsePathFilter(SnapUtil::sanitizeNSChars($_POST['filter-files']))
            );
        $_SESSION['filter_data'] = $file_filter_data;
    } else {
        if (isset($_SESSION['filter_data'])) {
            unset($_SESSION['filter_data']);
        }
    }
    //TODO Login Need to go here

    $core_dir_included   = array();
    $core_files_included = array();
    //by default fault
    $core_dir_notice  = false;
    $core_file_notice = false;

    if (!$archive_export_onlydb && isset($_POST['filter-on']) && isset($_POST['filter-dirs'])) {
        //findout matched core directories
        $filter_dirs =  DUP_PRO_Archive::parsePathFilter(SnapUtil::sanitizeNSChars($_POST['filter-dirs']), true);

        // clean possible blank spaces before and after the paths
        for ($i = 0; $i < count($filter_dirs); $i++) {
            $filter_dirs[$i] = trim($filter_dirs[$i]);
            $filter_dirs[$i] = (substr($filter_dirs[$i], -1) == "/") ? substr($filter_dirs[$i], 0, strlen($filter_dirs[$i]) - 1) : $filter_dirs[$i];
        }
        $core_dir_included = array_intersect($filter_dirs, DUP_PRO_U::getWPCoreDirs());
        $core_dir_notice   = !empty($core_dir_included);


        //find out core files
        $filter_files = DUP_PRO_Archive::parsePathFilter(SnapUtil::sanitizeNSChars($_POST['filter-files']), true);

        // clean possible blank spaces before and after the paths
        for ($i = 0; $i < count($filter_files); $i++) {
            $filter_files[$i] = trim($filter_files[$i]);
        }
        $core_files_included = array_intersect($filter_files, DUP_PRO_U::getWPCoreFiles());
        $core_file_notice    = !empty($core_files_included);
    }
    ?>
    <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('WordPress'); ?></div>
        <div id="data-srv-wp-all"></div>
    </div>
    <div class="info">
    <?php
    //VERSION CHECK
    echo '<span id="data-srv-wp-version"></span>&nbsp;<b>' . DUP_PRO_U::__('WordPress Version') . ":</b>&nbsp; '{$wp_version}' <br/>";
    echo '<div class="scan-system-subnote">';
    printf(DUP_PRO_U::__('It is recommended to have a version of WordPress that is greater than %1$s.  Older version of WordPress can lead to migration issues and are a  '
            . 'security risk.  If possible please update your WordPress site to the latest version.'), DUPLICATOR_PRO_SCAN_MIN_WP);
    echo '</div>';

    //CORE FILES
    echo '<hr size="1" /><span id="data-srv-wp-core"></span>&nbsp;<b>' . DUP_PRO_U::__('Core Files') . "</b> <br/>";

    $filter_text = "";
    if ($core_dir_notice) {
        echo '<div id="data-srv-wp-core-missing-dirs">';
        echo wp_kses(DUP_PRO_U::__("The core WordPress paths below will <u>not</u> be included in the archive. These paths are required for WordPress to function!"), array('u' => array()));
        echo "<br/>";
        foreach ($core_dir_included as $core_dir) {
            echo '&nbsp; &nbsp; <b><i class="fa fa-exclamation-circle scan-warn"></i>&nbsp;' . $core_dir . '</b><br/>';
        }
        echo '</small><br/>';
        echo '</div>';
        $filter_text = "directories";
    }

    if ($core_file_notice) {
        echo '<div id="data-srv-wp-core-missing-dirs">';
        echo wp_kses(DUP_PRO_U::__("The core WordPress file below will <u>not</u> be included in the archive. This file is required for WordPress to function!"), array('u' => array()));
        echo "<br/>";
        foreach ($core_files_included as $core_file) {
            echo '&nbsp; &nbsp; <b><i class="fa fa-exclamation-circle scan-warn"></i>&nbsp;' . $core_file . '</b><br/>';
        }
        echo '</div><br/>';
        $filter_text .= (strlen($filter_text) > 0) ? " and file" : "files";
    }

    if (strlen($filter_text) > 0) {
        echo '<div class="scan-system-subnote">';
        DUP_PRO_U::esc_html_e("Note: Please change the {$filter_text} filters if you wish to include the WordPress core files otherwise the data will have to be manually copied"
            . " to the new location for the site to function properly.");
        echo '</div>';
    }


    if (!$core_dir_notice && !$core_file_notice) {
        echo '<div class="scan-system-subnote">';
        DUP_PRO_U::esc_html_e("If the scanner is unable to locate the wp-config.php file in the root directory, then you will need to manually copy it to its new location. "
            . "This check will also look for core WordPress paths that should be included in the archive for WordPress to work correctly.");
        echo '</div>';
    }

    if (!is_multisite()) {
        //Normal Site
        echo '<hr size="1" /><span><div class="dup-scan-good"><i class="fa fa-check"></i></div></span>&nbsp;<b>' . DUP_PRO_U::__('Multisite: N/A') . "</b> <br/>";
        echo '<div class="scan-system-subnote">';
        DUP_PRO_U::esc_html_e('Multisite was not detected on this site. It is currently configured as a standard WordPress site.');
        echo "&nbsp;<i><a href='https://codex.wordpress.org/Create_A_Network' target='_blank'>[" . DUP_PRO_U::__('details') . "]</a></i>";
        echo '</div>';
    } elseif (License::can(License::CAPABILITY_MULTISITE_PLUS)) {
        //MU Gold
        echo '<hr size="1" /><span><div class="dup-scan-good"><i class="fa fa-check"></i></div></span>&nbsp;<b>' . DUP_PRO_U::__('Multisite: Detected') . "</b> <br/>";
        echo '<div class="scan-system-subnote">';
        DUP_PRO_U::esc_html_e('This license level has full access to all Multisite Plus+ features.');
        echo '</div>';
    } else {
        //MU Personal, Freelancer
        echo '<hr size="1" /><span><div class="dup-scan-warn"><i class="fa fa-exclamation-triangle fa-sm"></i></div></span>&nbsp;';
        echo '<b>' . DUP_PRO_U::__('Multisite: Detected') . "</b> <br/>";
        echo '<div class="scan-system-subnote">';
        printf(
            esc_html__(
                'Duplicator Pro is at the %1$s license level which allows for backups and migrations of an entire Multisite network.&nbsp;',
                'duplicator-pro'
            ),
            License::getLicenseToString()
        );
        echo '<br>';
        _e("To unlock all <b>Multisite Plus</b> features please upgrade the license before building a package.", 'duplicator-pro');
        echo '<br/>';
        echo "<a href='" . esc_url(License::getUpsellURL()) . "' target='_blank'>" . DUP_PRO_U::__('Upgrade Here') . "</a>&nbsp;|&nbsp;";
        echo "&nbsp;<a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-mu-110-q' target='_blank'>"
               . DUP_PRO_U::__('Multisite Plus Feature Overview') . "</a>";
        echo '</div>';
    }
    ?>

    <!-- Security Plugins -->
    <script id="hb-dup-security-plugins" type="text/x-handlebars-template">
    {{#if SRV.WP.securityPlugins}}
        <?php
            echo '<hr size="1" /><span><div class="dup-scan-good"><i class="fa fa-check"></i></div></span>&nbsp;<b>' . DUP_PRO_U::__('Security Plugins: Detected') . '</b> <br/>';
            echo '<div class="scan-system-subnote">';
            DUP_PRO_U::esc_html_e('Good News! Duplicator located a valid WordPress security plugin on your site. Please visit our site for more ');
            echo '<i><a href="https://snapcreek.com/duplicator/docs/how-to-secure-a-wordpress-website/" target="_blank">' . DUP_PRO_U::__('security resources') . '</a>.</i>';
            echo '</div>';
        ?>
    {{else}}
        <?php
            echo '<hr size="1" /><span><div class="dup-scan-warn"><i class="fa fa-check"></i></div></span>&nbsp;<b>' . DUP_PRO_U::__('Security Plugins: Not Detected') . '</b> <br/>';
            echo '<div class="scan-system-subnote">';
            DUP_PRO_U::esc_html_e('There are currently no security plugins detected on this site. It is highly recommended to install a security plugin on any site. For a full list of Duplicator recommended plugins check out our ');
            echo '<i><a href="https://snapcreek.com/duplicator/docs/how-to-secure-a-wordpress-website/" target="_blank">' . DUP_PRO_U::__('security recommendation article') . '</a>.</i>';
            echo '</div>';
        ?>
    {{/if}}
    </script>
    <div id="dup-security-plugins"></div>
    </div>
</div>

<!-- ======================
Restore only package -->
<div id="migration-status-scan-item" class="scan-item">
    <div class='title' onclick="DupPro.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php DUP_PRO_U::esc_html_e('Import Status');?></div>
        <div id="data-arc-status-migratepackage"></div>
    </div>
    <div class="info">
        <script id="hb-migrate-package-result" type="text/x-handlebars-template">
            <div class="container">
                <div class="data">
                    {{#if ARC.Status.PackageIsNotImportable}}
                        <hr>
                        <p>
                            <span class="maroon">
                            <?php DUP_PRO_U::esc_html_e("This package is not be compatible with"); ?>
                                <i data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Drag and Drop Import"); ?>"
                                   data-tooltip="<?php DUP_PRO_U::esc_html_e('The Drag and Drop import method is a new way to migrate packages you can find under Duplicator Pro > Tools > Import.'); ?>">
                                   <u><?php DUP_PRO_U::esc_html_e("Drag and Drop import"); ?></u>.&nbsp;
                                </i>
                                <?php DUP_PRO_U::esc_html_e("However it can still be used it to perform a database migration."); ?>
                            </span>

                            {{#if ARC.Status.IsDBOnly}}
                                <?php
                                DUP_PRO_U::esc_attr_e(
                                    "Database only packages can only be installed via the installer.php file. " .
                                    "The Drag and Drop interface only processes packages that have all WordPress core directories and all database tables."
                                );
                                ?>
                            {{else}}
                                <?php DUP_PRO_U::esc_attr_e("To make the package compatible with Drag and Drop import don't filter any tables or core directories."); ?>
                            {{/if}}
                        </p>
                        {{#if ARC.Status.HasFilteredCoreFolders}}
                        <p>
                            <b><?php DUP_PRO_U::esc_attr_e("FILTERED CORE DIRS:"); ?></b>
                        </p>
                        <ol>
                            {{#each ARC.FilteredCoreDirs as |dir|}}
                            <li>{{dir}} </li>
                            {{/each}}
                        </ol>
                        {{/if}}
                        {{#if ARC.Status.HasFilteredSiteTables}}
                            <b><?php DUP_PRO_U::esc_attr_e("FILTERED SITE TABLES:"); ?></b>
                            <div class="dup-scan-files-migrae-status">
                                <ol>
                                    {{#each DB.FilteredTables as |table|}}
                                    <li>{{table}} </li>
                                    {{/each}}
                                </ol>
                            </div>
                        {{/if}}
                    {{else}}
                        <?php DUP_PRO_U::esc_html_e("The package you are about to create is compatible with Drag and Drop import."); ?>
                    {{/if}}
                </div>
            </div>
        </script>
        <div id="migrate-package-result"></div>
    </div>
</div>

<script>
(function ($)
{
    //Ints the various server data responses from the scan results
    DupPro.Pack.intServerData = function(data)
    {
        $('#data-srv-php-websrv').html(DupPro.Pack.setScanStatus(data.SRV.PHP.websrv));
        $('#data-srv-php-openbase').html(DupPro.Pack.setScanStatus(data.SRV.PHP.openbase));
        $('#data-srv-php-maxtime').html(DupPro.Pack.setScanStatus(data.SRV.PHP.maxtime));
        $('#data-srv-php-minmemory').html(DupPro.Pack.setScanStatus(data.SRV.PHP.minMemory));
        $('#data-srv-php-arch64bit').html(DupPro.Pack.setScanStatus(data.SRV.PHP.arch64bit));
        $('#data-srv-php-mysqli').html(DupPro.Pack.setScanStatus(data.SRV.PHP.mysqli));
        $('#data-srv-php-openssl').html(DupPro.Pack.setScanStatus(data.SRV.PHP.openssl));
        $('#data-srv-php-allowurlfopen').html(DupPro.Pack.setScanStatus(data.SRV.PHP.allowurlfopen));
        $('#data-srv-php-curlavailable').html(DupPro.Pack.setScanStatus(data.SRV.PHP.curlavailable));
        $('#data-srv-php-version').html(DupPro.Pack.setScanStatus(data.SRV.PHP.version));
        $('#data-srv-php-all').html(DupPro.Pack.setScanStatus(data.SRV.PHP.ALL));
        //Wordpress
        $('#data-srv-wp-version').html(DupPro.Pack.setScanStatus(data.SRV.WP.version));
        $('#data-srv-wp-core').html(DupPro.Pack.setScanStatus(data.SRV.WP.core));
        $('#data-srv-wp-all').html(DupPro.Pack.setScanStatus(data.SRV.WP.ALL));
    }
})(jQuery);
</script>
