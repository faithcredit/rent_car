<?php

/**
 * The base class for all screen.php files.  This class is used to control items that are common
 * among all screens, namely the Help tab and Screen Options drop down items.  When creating a
 * screen object please extent this class.
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    DUP_PRO
 * @subpackage classes/ui
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      3.3.0
 */

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapUtil;

class DUP_PRO_UI_Screen
{
    /**
     * Used as a placeholder for the current screen object
     */
    public $screen;

    /**
     *  Init this object when created
     */
    public function __construct()
    {
    }

    public static function getCustomCss()
    {
        if (!Duplicator\Core\Controllers\ControllersManager::getInstance()->isDuplicatorPage()) {
            return;
        }

        $colorScheme        = self::getCurrentColorScheme();
        $primaryButtonColor = self::getPrimaryButtonColorByScheme();
        ?>
        <style>
            .dup-pro-meter.blue>span {
                background-color: <?php echo $colorScheme->colors[2]; ?>;
                background-image: none;
            }

            .dup-pro-recovery-point-actions>.copy-link {
                border-color: <?php echo $primaryButtonColor; ?>;
            }

            .dup-pro-recovery-point-actions>.copy-link .copy-icon {
                background-color: <?php echo $primaryButtonColor; ?>;
            }


            .tippy-box[data-theme~='duplicator'],
            .tippy-box[data-theme~='duplicator-filled'] {
                border-color: <?php echo $primaryButtonColor; ?>;
            }

            .tippy-box[data-theme~='duplicator'] h3,
            .tippy-box[data-theme~='duplicato-filled'] h3 {
                background-color: <?php echo $primaryButtonColor; ?>;
            }

            .tippy-box[data-theme~='duplicator-filled'] .tippy-content {
                background-color: <?php echo $primaryButtonColor; ?>;
            }

            .tippy-box[data-theme~='duplicator'][data-placement^='top']>.tippy-arrow::before,
            .tippy-box[data-theme~='duplicator-filled'][data-placement^='top']>.tippy-arrow::before {
                border-top-color: <?php echo $primaryButtonColor; ?>;
            }

            .tippy-box[data-theme~='duplicator'][data-placement^='bottom']>.tippy-arrow::before,
            .tippy-box[data-theme~='duplicator-filled'][data-placement^='bottom']>.tippy-arrow::before {
                border-bottom-color: <?php echo $primaryButtonColor; ?>;
            }

            .tippy-box[data-theme~='duplicator'][data-placement^='left']>.tippy-arrow::before,
            .tippy-box[data-theme~='duplicator-filled'][data-placement^='left']>.tippy-arrow::before {
                border-left-color: <?php echo $primaryButtonColor; ?>;
            }

            .tippy-box[data-theme~='duplicator'][data-placement^='right']>.tippy-arrow::before,
            .tippy-box[data-theme~='duplicator-filled'][data-placement^='right']>.tippy-arrow::before {
                border-right-color: <?php echo $primaryButtonColor; ?>;
            }

            nav.dup-dnload-menu-items button:hover {
                background-color: <?php echo $primaryButtonColor; ?>;
            }

            .button-primary.dup-base-color,
            .button-primary .dup-base-color,
            .button-primary i[data-tooltip].fa-question-circle.dup-base-color,
            .button-primary i[data-tooltip].fa-question-circle.dup-base-color {
                color: <?php echo $colorScheme->colors[1]; ?>;
            }
        </style>
        <?php
    }

    /**
     * Unfortunately not all color schemes take the same color as the buttons so you need to make a custom switch/
     *
     * @return string
     */
    public static function getPrimaryButtonColorByScheme()
    {
        $colorScheme = self::getCurrentColorScheme();
        $name        = strtolower($colorScheme->name);
        switch ($name) {
            case 'blue':
                return '#e3af55';
            case 'light':
            case 'midnight':
                return $colorScheme->colors[3];
            case 'ocean':
            case 'ectoplasm':
            case 'coffee':
            case 'sunrise':
            case 'default':
            default:
                return $colorScheme->colors[2];
        }
    }

    /**
     *
     * @global object[] $_wp_admin_css_colors
     * @return object
     */
    public static function getCurrentColorScheme()
    {
        global $_wp_admin_css_colors;
        $colorScheme = get_user_option('admin_color');

        if (isset($_wp_admin_css_colors[$colorScheme])) {
            return $_wp_admin_css_colors[$colorScheme];
        } else {
            return $_wp_admin_css_colors[SnapUtil::arrayKeyFirst($_wp_admin_css_colors)];
        }
    }

    /**
     * Get the help support tab view content shown in the help system
     *
     * @param string $guide The target URL to navigate to on the online user guide
     * @param string $faq   The target URL to navigate to on the online user tech FAQ
     *
     * @return void
     */
    public function getSupportTab($guide, $faq)
    {
        $content = DUP_PRO_U::__("<b>Need Help?</b>  Please check out these resources:"
            . "<ul class='dup-help-support'>"
            . "<li><a href='https://snapcreek.com/duplicator/docs/guide{$guide}' class='dup-user-guide' target='_sc-faq'>Full Online User Guide</a></li>"
            . "<li><a href='https://snapcreek.com/duplicator/docs/faqs-tech{$faq}' class='dup-faq' target='_sc-faq'>Frequently Asked Questions</a></li>"
            . "<li><a href='https://snapcreek.com/duplicator/docs/quick-start/' class='dup-quick-start' target='_sc-faq'>Quick Start Guide</a></li>"
            . "</ul>");

        $this->screen->add_help_tab(array(
            'id'      => 'dpro_help_tab_callback',
            'title'   => DUP_PRO_U::esc_html__('Support'),
            'content' => "<p>{$content}</p>"
        ));
    }

    public static function getHelpSidebarBaseItems()
    {
        ob_start();
        ?>
        <li>
            <i class='fa fa-home'></i> <a href='<?php echo DUPLICATOR_PRO_DUPLICATOR_DOCS_URL; ?>' class='dup-knowledge-base' target='_sc-home'>
                <?php DUP_PRO_U::esc_html_e('Knowledge Base'); ?>
            </a>
        </li>
        <li>
            <i class='fa fa-book'></i> <a href='<?php echo DUPLICATOR_PRO_USER_GUIDE_URL; ?>' class='dup-full-guide' target='_sc-guide'>
                <?php DUP_PRO_U::esc_html_e('Full User Guide'); ?>
            </a>
        </li>
        <li>
            <i class='far fa-file-code'></i> <a href='<?php echo DUPLICATOR_PRO_TECH_FAQ_URL; ?>' class='dup-faqs' target='_sc-faq'>
                <?php DUP_PRO_U::esc_html_e('Technical FAQs'); ?>
            </a>
        </li>
        <?php
        return ob_get_clean();
    }
}
