<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Core\CapMng;
use Duplicator\Core\Views\TplMng;

require_once(DUPLICATOR____PATH . '/classes/class.package.pagination.php');
require_once(DUPLICATOR____PATH . '/classes/ui/class.ui.dialog.php');

global $packagesViewData;
/** @var wpdb $wpdb */
global $wpdb;

$tplMng = TplMng::getInstance();

if (isset($_REQUEST['create_from_temp'])) {
    //Takes temporary package and inserts it into the package table
    $package = DUP_PRO_Package::get_temporary_package(false);
    if ($package != null) {
        $package->save();
    }
    unset($_REQUEST['create_from_temp']);
    unset($package);
}

$system_global = DUP_PRO_System_Global_Entity::getInstance();

if (!empty($_REQUEST['action'])) {
    if (CapMng::can(CapMng::CAP_CREATE, false) && $_REQUEST['action'] == 'stop-build') {
        $package_id = (int) $_REQUEST['action-parameter'];
        DUP_PRO_Log::trace("stop build of $package_id");
        $action_package = DUP_PRO_Package::get_by_id($package_id);
        if ($action_package != null) {
            DUP_PRO_Log::trace("set $action_package->ID for cancel");
            $action_package->set_for_cancel();
        } else {
            DUP_PRO_Log::trace(
                "could not find package so attempting hard delete. "
                . "Old files may end up sticking around although chances are there isnt much if we couldnt nicely cancel it."
            );
            $result = DUP_PRO_Package::force_delete($package_id);
            ($result) ? DUP_PRO_Log::trace("Hard delete success") : DUP_PRO_Log::trace("Hard delete failure");
        }
        unset($action_package);
    } elseif ($_REQUEST['action'] == 'clear-messages') {
        $system_global->clearFixes();
        $system_global->save();
    }
}

$packagesViewData = array(
    'pending_cancelled_package_ids' => DUP_PRO_Package::get_pending_cancellations(),
    'rowCount'                      => 0,
    'package_ui_created'            => null
);

$totalElements = $wpdb->get_var("SELECT count(id) as totalElements FROM `{$wpdb->base_prefix}duplicator_pro_packages`");
$statusActive  = $wpdb->get_var("SELECT count(id) as totalElements FROM `{$wpdb->base_prefix}duplicator_pro_packages`  WHERE status < 100 and status > 0");

$pager        = new DUP_PRO_Package_Pagination();
$per_page     = $pager->get_per_page();
$current_page = ($statusActive >= 1) ? 1 : $pager->get_pagenum();
$offset       = ($current_page - 1) * $per_page;

$global = DUP_PRO_Global_Entity::getInstance();

$orphan_info        = DUP_PRO_Server::getOrphanedPackageInfo();
$orphan_display_msg = $orphan_info['count'];

$user_id                                = get_current_user_id();
$creaderFormat                          = get_user_meta($user_id, 'duplicator_pro_created_format', true);
$packagesViewData['package_ui_created'] = is_numeric($creaderFormat) ? $creaderFormat : 1;

if ($orphan_display_msg) {
    ?>
    <div id='dpro-error-orphans' class="error">
        <p>
            <?php
            $orphan_msg  = DUP_PRO_U::__(
                'There are currently (%1$s) orphaned package files taking up %2$s of space. ' .
                'These package files are no longer visible in the packages list below and are safe to remove.'
            ) . '<br/>';
            $orphan_msg .= DUP_PRO_U::__('Go to: Tools > General > Information > Stored Data > look for the [Delete Package Orphans] button for more details.') . '<br/>';
            $orphan_msg .= '<a href=' . self_admin_url('admin.php?page=duplicator-pro-tools&tab=diagnostics&orphanpurge=1') . '>' .
                DUP_PRO_U::__('Take me there now!') .
                '</a>';
            printf($orphan_msg, $orphan_info['count'], DUP_PRO_U::byteSize($orphan_info['size']));
            ?>
            <br />
        </p>
    </div>
<?php } ?>

<form id="form-duplicator" method="post">
    <?php wp_nonce_field('dpro_package_form_nonce'); ?>
    <?php $tplMng->render('admin_pages/packages/toolbar'); ?>

    <table class="widefat dup-packtbl striped" aria-label="Packages List">
        <?php
        $tplMng->render(
            'admin_pages/packages/packages_table_head',
            array('totalElements' => $totalElements)
        );

        if ($totalElements == 0) {
            $tplMng->render('admin_pages/packages/no_elements_row');
        } else {
            DUP_PRO_Package::by_status_callback(
                array('Duplicator\\Views\\PackagesHelper', 'tablePackageRow'),
                array(),
                $per_page,
                $offset,
                '`id` DESC'
            );
        }
        $tplMng->render(
            'admin_pages/packages/packages_table_foot',
            array('totalElements' => $totalElements)
        ); ?>
    </table>
</form>

<?php if ($totalElements > $per_page) { ?>
    <form id="form-duplicator-nav" method="post">
        <?php wp_nonce_field('dpro_package_form_nonce'); ?>
        <div class="dup-paged-nav tablenav">
            <?php if ($statusActive > 0) : ?>
                <div id="dpro-paged-progress" style="padding-right: 10px">
                    <i class="fas fa-circle-notch fa-spin fa-lg fa-fw"></i>
                    <i><?php DUP_PRO_U::esc_html_e('Paging disabled during build...'); ?></i>
                </div>
            <?php else : ?>
                <div id="dpro-paged-buttons">
                    <?php echo $pager->display_pagination($totalElements, $per_page); ?>
                </div>
            <?php endif; ?>
        </div>
    </form>
<?php } else { ?>
    <div style="float:right; padding:10px 5px">
        <?php echo $totalElements . '&nbsp;' . DUP_PRO_U::__("items"); ?>
    </div>
    <?php
}

$tplMng->render('admin_pages/packages/packages_scripts');