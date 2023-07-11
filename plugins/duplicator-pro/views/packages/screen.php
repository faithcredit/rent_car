<?php

defined("ABSPATH") or die("");

use Duplicator\Controllers\PackagesPageController;

class DUP_PRO_Package_Screen extends DUP_PRO_UI_Screen
{
    /**
     * Class contructor
     *
     * @param string $page page
     */
    public function __construct($page)
    {
        add_action('load-' . $page, array($this, 'init'));
        add_filter('screen_settings', array($this, 'show_options'), 10, 2);
    }

    /**
     * Init package screen
     *
     * @return void
     */
    public function init()
    {
        $active_tab   = isset($_GET['inner_page']) ? $_GET['inner_page'] : 'list';
        $active_tab   = isset($_GET['action']) && $_GET['action'] == 'detail' ? 'detail' : $active_tab;
        $this->screen = get_current_screen();

        switch (strtoupper($active_tab)) {
            case 'LIST':
                $content = $this->get_list_help();
                break;
            case 'NEW1':
                $content = $this->get_step1_help();
                break;
            case 'NEW2':
                $content = $this->get_step2_help();
                break;
            case 'DETAIL':
                $content = $this->get_details_help();
                break;
            default:
                $content = $this->get_list_help();
                break;
        }

        $guide    = '#guide-packs';
        $faq      = '#faq-package';
        $content .= "<b>References:</b><br/>"
            . "<a href='https://snapcreek.com/duplicator/docs/guide/{$guide}' class='dup-references-user-guide' target='_sc-guide'>User Guide</a> | "
            . "<a href='https://snapcreek.com/duplicator/docs/faqs-tech/{$faq}' class='dup-references-faqs' target='_sc-guide'>FAQs</a> | "
            . "<a href='https://snapcreek.com/duplicator/docs/quick-start/' class='dup-references-quick-start' target='_sc-guide'>Quick Start</a>";

        $this->screen->add_help_tab(array(
            'id'      => 'dpro_help_package_overview',
            'title'   => DUP_PRO_U::__('Overview'),
            'content' => "<p>{$content}</p>"
            ));

        $this->getSupportTab($guide, $faq);
        $this->screen->set_help_sidebar(self::getPackagesHelpSidebar());
    }

    /**
     * Return HELP sidebar
     *
     * @return string
     */
    public static function getPackagesHelpSidebar()
    {
        ob_start();
        ?>
        <div class="dpro-screen-hlp-info"><b><?php DUP_PRO_U::esc_html_e('Resources'); ?>:</b> 
            <ul>
                <?php echo self::getHelpSidebarBaseItems(); ?>
                <li>
                    <i class='fas fa-cog'></i> <a href='admin.php?page=duplicator-pro-settings&tab=package' class='dup-package-settings'>
                        <?php DUP_PRO_U::esc_html_e('Package Settings'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Return list HELP
     *
     * @return string
     */
    public function get_list_help()
    {
        $result = '';

        $result .= '<h3>' . DUP_PRO_U::__("Package Details") . '</h3>';

        $result .= '<b><i class="fa fa-archive"></i> ' . DUP_PRO_U::__("Packages » All") . '</b><br/>';
        $result .= DUP_PRO_U::__("The 'Packages' section is the main interface for managing all the packages that have been created. A Package consists "
                . "of two core files. The first is the 'installer.php' file and the second is the 'archive.zip/daf' file.  The installer file is a php file that when browsed to via "
                . "a web browser presents a wizard that redeploys or installs the website by extracting the archive file.  The archive file is a zip/daf file containing "
                . "all your WordPress files and a copy of your WordPress database. To create a package, click the 'Create New' button and follow the prompts.");
        $result .= '<br/><br/>';
        $result .= DUP_PRO_U::__("The package [Type] column will be either 'Manual' or 'Schedule'.   If a schedule type has a cog icon ")
                . "<i class='fas fa-cog fa-sm pointer'></i> " . DUP_PRO_U::__("then that package was created manually by clicking the 'Run Now' link on the schedules page.  "
                    . "The [Created] column shows the time the package was built and the [Size] column represents the compressed size of the archive file.   The [Name] column "
                    . "is generic and helps to identify the package.   The [Installer Name] column identifies the full name of the installer file.  If it is hashed (unique) "
                    . "then the lock icon will be locked to identify that the name is secure to browse to on a public facing URL.");
        $result .= '<br/><br/>';

        $result .= '<b><i class="fa fa-download"></i> ' . DUP_PRO_U::__("Downloads") . '</b><br/>';
        $result .= DUP_PRO_U::__("To download the package files click on the Download button.  Choosing the 'Both Files' option will popup two separate save dialogs.
					On some browsers you may have to enable popups on this site.  In order to download just the 'Installer' or 'Archive' click on that menu item.");
        $result .= ' <i>' . DUP_PRO_U::__("Note: the archive file will have a copy of the installer inside of it named installer-backup.php") . '</i>';
        $result .= '<br/><br/>';

        $result .= '<b><i class="fa fa-database"></i> ' . DUP_PRO_U::__("Storage") . '</b><br/>';
        $result .= DUP_PRO_U::__("The remote storage button allows users to access the package at the remote location. If a package contains remote storage endpoints then the
                    button will be enabled.  A disabled button indicates that no remote packages were setup.  If a red icon shows <i class='fas fa-server remote-data-fail fa-sm'></i>
                    &nbsp; then one or more of the storage locations failed during the transfer phase.");
        $result .= '<br/><br/>';

        $result .= '<b><i class="fas fa-chevron-down"></i> ' . DUP_PRO_U::__("Details") . '</b><br/>';
        $result .= DUP_PRO_U::__("To see the package details and additional options click the 'Details' expand/collpase button. If the Recovery menu option "
            . "is disabled then the package is not enabled as a valid recovery package.   You should see a valid recovery icon "
            . "<i class='fa fa-undo fa-sm'></i> next to the package type to quickly identify packages that are recover capable. ");
        $result .= '<br/><br/>';

        $result .= '<b><i class="far fa-file-archive fa-sm"></i> ' . DUP_PRO_U::__("Archive Types") . '</b><br/>';
        $result .= DUP_PRO_U::__("An archive file can be saved as either a .zip file or .daf file.  A zip file is a common archive format used to compress and group files.  The daf file short for "
                . "'Duplicator Archive Format' is a custom format used specifically  for working with larger packages and scale-ability issues on many shared hosting platforms.  Both "
                . "formats work very similar the main difference is that the daf file can only be extracted using the installer.php file or the "
                . "<a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-052-q' class='dup-DAF-tool' target='_blank'>DAF extraction tool</a>.  The zip file can be used by other zip "
                . "tools like winrar/7zip/winzip or other client-side tools.");
        $result .= '<br/><hr/>';

        $result .= '<h3>' . DUP_PRO_U::__("Tools") . '</h3>';

        $result .= '<b><i class="fas fa-clone"></i> ' . DUP_PRO_U::__("Templates") . '</b><br/>';
        $result .= DUP_PRO_U::__(' Templates are used to profile out how a package will be built and required for schedules. Templates allow you to choose which files '
            . 'and database tables you would like to make as part of your backup process. It also allows for the installer to be pre-filled with the values of the template. '
            . 'when doing manual builds.');
        $result .= '<br/><br/>';

        $result .= '<b><i class="fas fa-arrow-alt-circle-down"></i> ' . DUP_PRO_U::__("Import") . '</b><br/>';
        $result .= DUP_PRO_U::__('The import features allows users to quickly upload a Duplicator Pro archive to overwrite the current site. For more details check-out '
            . ' the import help section.');
        $result .= '<br/><br/>';

        $result .= '<b><i class="fas fa-undo-alt"></i> ' . DUP_PRO_U::__("Recovery Point") . '</b><br/>';
        $result .= DUP_PRO_U::__(' The Recovery Point is a special package that allows one to quickly revert the system should it become corrupted ' .
                   'during a maintenance operation such as a plugin/theme update or an experimental file change. ' .
                   'The advantage of setting a Recovery Point is that you can very quickly restore a backup without having to worry ' .
                   'about uploading a package and setting the parameters such as database credentials or site paths. ' .
                   'See Help on Tools > Recovery page for more information and usage of the Recovery Point.');
        $result .= '<br/><hr/>';

        $result .= '<h3>' . DUP_PRO_U::__("Miscellaneous") . '</h3>';

        $result .= '<b><i class="fa fa-bolt"></i> ' . DUP_PRO_U::__("How to Install a Package") . '</b><br/>';
        $result .= DUP_PRO_U::__("Installing a package is pretty straight forward, however it does require a quick primer if you have never done it before.  To get going with a step by step "
                . "guide and quick video check out the <a href='https://snapcreek.com/duplicator/docs/quick-start/' class='dup-quick-start' target='_blank'>quick start guide.</a>");
        $result .= '<br/><br/>';

        return $result;
    }

    /**
     * Return step1 HELP
     *
     * @return string
     */
    public function get_step1_help()
    {
        return DUP_PRO_U::__("<b>Packages New » 1 Setup</b> <br/>"
                . "The setup screen allows users to choose where they would like to store thier package, such as Google Drive, Dropbox, on the local server or a combination of both."
                . "Setup also allow users to setup optional filtered directory paths, files and database tables to change what is included in the archive file.  The optional option "
                . "to also have the installer pre-filled can be used.  To expedited the workflow consider using a Template. <br/><br/>");
    }

    /**
     * Return step2 HELP
     *
     * @return string
     */
    public function get_step2_help()
    {
        return DUP_PRO_U::__("<b>Packages » 2 Scan</b> <br/>"
                . "The plugin will scan your system, files and database to let you know if there are any concerns or issues that may be present.  All items in green mean the checks "
                . "looked good.  All items in red indicate a warning.  Warnings will not prevent the build from running, however if you do run into issues with the build then checking "
                . "the warnings should be considered. <br/><br/>");
    }

    /**
     * Return details HELP
     *
     * @return string
     */
    public function get_details_help()
    {
        return DUP_PRO_U::__("<b>Packages » Details</b> <br/>"
                . "The details view will give you a full break-down of the package including any errors that may have occured during the install. <br/><br/>");
    }

    /**
     * Packages List: Screen Options Tab
     *
     * @param string $screen_settings
     * @param WP_Screen $args
     *
     * @return string
     */
    public function show_options($screen_settings, WP_Screen $args)
    {
        $return = $screen_settings;

        //Only display on packages screen and not build screens
        if (isset($_GET['page']) && $_GET['page'] == "duplicator-pro") {
            if (isset($_GET['tab']) && $_GET['tab'] == "packages") {
                if (count($_GET) > 2) {
                    return $screen_settings;
                }
            } elseif (isset($_GET['tab']) && $_GET['tab'] != "packages") {
                return $screen_settings;
            }
        }

        //Check Screen
        if (PackagesPageController::getInstance()->isCurrentPage()) {
            //Setting current values of fields to display in controls
            $global             = DUP_PRO_Global_Entity::getInstance();
            $user_id            = get_current_user_id();
            $created_format_key = 'duplicator_pro_created_format';
            $pk_per_page_key    = 'duplicator_pro_opts_per_page';

            //Inheriting the value of the old created format option to the screen option
            if (!is_numeric(get_user_meta($user_id, $created_format_key, true))) {
                update_user_meta($user_id, $created_format_key, $global->package_ui_created);
            }
            $current_created_format = get_user_meta($user_id, $created_format_key, true);
            $current_per_page       = get_user_meta($user_id, $pk_per_page_key, true) != null ? get_user_meta($user_id, $pk_per_page_key, true) : 10;

            $button  = get_submit_button(DUP_PRO_U::__('Apply'), 'primary', 'screen-options-apply', false);
            $return .= '
            <fieldset class="screen-options" style="float:left;">
		    <legend>Pagination</legend>
				<label for="' . $pk_per_page_key . '">' . DUP_PRO_U::__("Packages Per Page") . '</label>
				<input type="number" step="1" min="1" max="999" class="screen-per-page" name="' . $pk_per_page_key . '" id="' . $pk_per_page_key . '" maxlength="3" value="' . $current_per_page . '">
		    </fieldset>
            <fieldset class="screen-options">
            <legend>' . DUP_PRO_U::__("Created Format") . '</legend>
            <div class="metabox-prefs">
                <input type="hidden" name="wp_screen_options[option]" value="package_screen_options" />
                <input type="hidden" name="wp_screen_options[value]" value="val" />
                <div class="created-format-wrapper">
                    <select name="' . $created_format_key . '" >
                    <!-- YEAR -->
                    <optgroup label="' . DUP_PRO_U::__("By Year") . '">
                        <option value="1" ' . selected($current_created_format, 1, false) . '>Y-m-d H:i &nbsp;	[2000-01-05 12:00]</option>
                        <option value="2" ' . selected($current_created_format, 2, false) . '>Y-m-d H:i:s		[2000-01-05 12:00:01]</option>
                        <option value="3" ' . selected($current_created_format, 3, false) . '>y-m-d H:i &nbsp;	[00-01-05   12:00]</option>
                        <option value="4" ' . selected($current_created_format, 4, false) . '>y-m-d H:i:s		[00-01-05   12:00:01]</option>
                    </optgroup>
                    <!-- MONTH -->
                    <optgroup label="' . DUP_PRO_U::__("By Month") . '">
                        <option value="5" ' . selected($current_created_format, 5, false) . '>m-d-Y H:i  &nbsp; [01-05-2000 12:00]</option>
                        <option value="6" ' . selected($current_created_format, 6, false) . '>m-d-Y H:i:s		[01-05-2000 12:00:01]</option>
                        <option value="7" ' . selected($current_created_format, 7, false) . '>m-d-y H:i  &nbsp; [01-05-00   12:00]</option>
                        <option value="8" ' . selected($current_created_format, 8, false) . '>m-d-y H:i:s		[01-05-00   12:00:01]</option>
                    </optgroup>
                    <!-- DAY -->
                    <optgroup label="' . DUP_PRO_U::__("By Day") . '">
                        <option value="9" ' . selected($current_created_format, 9, false) . '> d-m-Y H:i &nbsp;	[05-01-2000 12:00]</option>
                        <option value="10" ' . selected($current_created_format, 10, false) . '>d-m-Y H:i:s		[05-01-2000 12:00:01]</option>
                        <option value="11" ' . selected($current_created_format, 11, false) . '>d-m-y H:i &nbsp;	[05-01-00	12:00]</option>
                        <option value="12" ' . selected($current_created_format, 12, false) . '>d-m-y H:i:s		[05-01-00	12:00:01]</option>
                    </optgroup>
                </select>
                </div>
            </div>
            </fieldset>
            <br class="clear">' . $button;
        }
        return $return;
    }

    /**
     * Set duplicator screen option
     *
     * @param mixed  $screen_option The value to save instead of the option value. Default false (to skip saving the current option).
     * @param string $option        The option name.
     * @param int    $value         The option value.
     *
     * @return bool
     */
    public static function set_screen_options($screen_option, $option, $value)
    {
        $user_id = get_current_user_id();

        update_user_meta($user_id, 'duplicator_pro_opts_per_page', filter_input(INPUT_POST, 'duplicator_pro_opts_per_page', FILTER_VALIDATE_INT));
        update_user_meta($user_id, 'duplicator_pro_created_format', filter_input(INPUT_POST, 'duplicator_pro_created_format', FILTER_VALIDATE_INT));

        // Returning false from the filter will skip saving the current option
        return false;
    }
}