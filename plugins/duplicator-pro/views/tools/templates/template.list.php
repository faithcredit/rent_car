<?php

use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;

defined("ABSPATH") or die("");

/**
 * @var DUP_PRO_Package_Template_Entity $package_template
 */

$nonce_action = 'duppro-template-list';
$display_edit = false;
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

if (!empty($_REQUEST['action'])) {
    $nonce_val = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : $_GET['_wpnonce'];
    DUP_PRO_U::verifyNonce($nonce_val, $nonce_action);
    $action = sanitize_text_field($_REQUEST['action']);

    switch ($action) {
        case 'add':
        case 'edit':
            $display_edit = true;
            break;

        case 'bulk-delete':
            if (is_array($_REQUEST['selected_id'])) {
                $package_template_ids = array_map("sanitize_text_field", $_REQUEST['selected_id']);
            } else {
                $package_template_ids = sanitize_text_field($_REQUEST['selected_id']);
            }

            foreach ($package_template_ids as $package_template_id) {
                DUP_PRO_Log::trace("attempting to delete $package_template_id");
                DUP_PRO_Package_Template_Entity::deleteById($package_template_id);
            }

            break;

        case 'delete':
            $package_template_id = (int) $_REQUEST['package_template_id'];

            DUP_PRO_Log::trace("attempting to delete $package_template_id");
            DUP_PRO_Package_Template_Entity::deleteById($package_template_id);
            break;

        default:
            break;
    }
}

$package_templates      = DUP_PRO_Package_Template_Entity::getAllWithoutManualMode();
$package_template_count = count($package_templates);
?>

<form 
    id="dup-package-form" 
    class="<?php echo ($blur ? 'dup-mock-blur' : ''); ?>"
    action="<?php echo esc_url($templates_tab_url); ?>" 
    method="post"
>
    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" id="dup-package-form-action" name="action" value=""/>
    <input type="hidden" id="dup-package-selected-package-template" name="package_template_id" value="-1"/>

    <!-- ====================
    TOOL-BAR -->
    <table class="dpro-edit-toolbar">
        <tr>
            <td>
                <select id="bulk_action">
                    <option value="-1" selected="selected"><?php DUP_PRO_U::esc_html_e("Bulk Actions"); ?></option>
                    <option value="delete" title="Delete selected package(s)"><?php DUP_PRO_U::esc_html_e("Delete"); ?></option>
                </select>
                <input type="button" class="button action" value="<?php DUP_PRO_U::esc_attr_e("Apply") ?>" onclick="DupPro.Template.BulkAction()">
            </td>
            <td>
                <div class="btnnav">
                    <a href="<?php echo esc_url($edit_template_url); ?>" class="button dup-add-template-btn"><?php DUP_PRO_U::esc_html_e('Add New'); ?></a>
                </div>
            </td>
        </tr>
    </table>    

    <!-- ====================
    LIST ALL SCHEDULES -->
    <table class="widefat dup-template-list-tbl striped">
        <thead>
            <tr>
                <th class="col-check"><input type="checkbox" id="dpro-chk-all" title="Select all packages" onclick="DupPro.Template.SetDeleteAll(this)"></th>
                <th class="col-name"><?php _e('Name', 'duplicator-pro'); ?></th>
                <th class="col-recover"><?php _e('Recovery', 'duplicator-pro'); ?></th>
                <th class="col-empty"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            foreach ($package_templates as $package_template) :
                /* @var $package_template DUP_PRO_Package_Template_Entity */
                $i++;

                $schedules      = DUP_PRO_Schedule_Entity::get_by_template_id($package_template->getId());
                $schedule_count = count($schedules);
                ?>
                <tr class="package-row <?php echo ($i % 2) ? 'alternate' : ''; ?>">
                    <td class="col-check">
                        <?php if ($package_template->is_default == false) : ?>
                            <input name="selected_id[]" type="checkbox" value="<?php echo intval($package_template->getId()); ?>" class="item-chk" />
                        <?php else : ?>
                            <input type="checkbox" disabled />
                        <?php endif; ?>
                    </td>
                    <td class="col-name" >
                        <a 
                            href="javascript:void(0);" 
                            onclick="DupPro.Template.Edit(<?php echo intval($package_template->getId()); ?>);" 
                            class="name" 
                            data-template-id="<?php echo intval($package_template->getId()); ?>"
                        >
                            <?php echo esc_html($package_template->name); ?>
                        </a>
                        <div class="sub-menu">
                            <a class="dup-edit-template-btn" href="javascript:void(0);"onclick="DupPro.Template.Edit(<?php echo $package_template->getId(); ?>);" ><?php DUP_PRO_U::esc_html_e('Edit'); ?></a> |
                            <a class="dup-copy-template-btn" href="javascript:void(0);"onclick="DupPro.Template.Copy(<?php echo $package_template->getId(); ?>);" ><?php DUP_PRO_U::esc_html_e('Copy'); ?></a>
                            <?php if ($package_template->is_default == false) : ?>
                                | <a 
                                    class="dup-delete-template-btn" 
                                    href="javascript:void(0);" 
                                    onclick="DupPro.Template.Delete(<?php echo $package_template->getId() ?>, <?php echo intval($schedule_count); ?>);"
                                    >
                                    <?php DUP_PRO_U::esc_html_e('Delete'); ?>
                                </a>
                            <?php endif; ?>
                        </div>                        
                    </td>
                    <td class="col-recover" >
                        <?php $package_template->recoveableHtmlInfo(true); ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>

            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" style="text-align:right; font-size:12px">                       
                    <?php echo DUP_PRO_U::esc_html__('Total') . ': ' . esc_html($package_template_count); ?>
                </th>
            </tr>
        </tfoot>
    </table>
</form>
<?php
$alert1          = new DUP_PRO_UI_Dialog();
$alert1->title   = DUP_PRO_U::__('Bulk Action Required');
$alert1->message = DUP_PRO_U::__('Please select an action from the "Bulk Actions" drop down menu!');
$alert1->initAlert();

$alert2          = new DUP_PRO_UI_Dialog();
$alert2->title   = DUP_PRO_U::__('Selection Required');
$alert2->message = DUP_PRO_U::__('Please select at least one template to delete!');
$alert2->initAlert();

$confirm1                      = new DUP_PRO_UI_Dialog();
$confirm1->wrapperClassButtons = 'dup-delete-template-dialog-bulk';
$confirm1->title               = DUP_PRO_U::__('Delete the selected templates?');
$confirm1->message             = DUP_PRO_U::__('All schedules using this template will be reassigned to the "Default" Template.');
$confirm1->message            .= '<br/><br/>';
$confirm1->message            .= DUP_PRO_U::__('<small><i>Note: This action removes all selected custom templates.</i></small>');
$confirm1->progressText        = DUP_PRO_U::__('Removing Templates, Please Wait...');
$confirm1->jsCallback          = 'DupPro.Storage.BulkDelete()';
$confirm1->initConfirm();

$confirm2                      = new DUP_PRO_UI_Dialog();
$confirm2->wrapperClassButtons = 'dup-delete-template-dialog-single';
$confirm2->title               = DUP_PRO_U::__('Are you sure you want to delete this template?');
$confirm2->message             = DUP_PRO_U::__('All schedules using this template will be reassigned to the "Default" Template.');
$confirm2->progressText        = $confirm1->progressText;
$confirm2->jsCallback          = 'DupPro.Template.DeleteThis(this)';
$confirm2->initConfirm();
?>
<script>
    jQuery(document).ready(function ($) {

        //Shows detail view
        DupPro.Template.View = function (id) {
            $('#' + id).toggle();
        }

        // Edit template
        DupPro.Template.Edit = function (id) {
            document.location.href = '<?php echo "$edit_template_url&package_template_id="; ?>' + id;
        };

        // Copy template
        DupPro.Template.Copy = function (id) {
<?php
$params             = array(
    'action=copy-template',
    '_wpnonce=' . wp_create_nonce('duppro-template-edit'),
    'package_template_id=-1',
    'duppro-source-template-id=' // last params get id from js param function
);
$edit_template_url .= '&' . implode('&', $params);
?>
            document.location.href = '<?php echo "$edit_template_url"; ?>' + id;
        };

        //Delets a single record
        DupPro.Template.Delete = function (id, schedule_count) {
            var message = "";
<?php $confirm2->showConfirm(); ?>
            if (schedule_count > 0)
            {
                message += "<?php DUP_PRO_U::esc_html_e('There currently are') ?>" + " ";
                message += schedule_count + " " + "<?php DUP_PRO_U::esc_html_e('schedule(s) using this template.'); ?>" + "  ";
                message += "<?php DUP_PRO_U::esc_html_e('All schedules using this template will be reassigned to the \"Default\" template.') ?>" + " ";
                $("#<?php echo esc_js($confirm2->getID()); ?>_message").html(message);
            }
            $("#<?php echo esc_js($confirm2->getID()); ?>-confirm").attr('data-id', id);
        }

        DupPro.Template.DeleteThis = function (e) {
            var id = $(e).attr('data-id');
            jQuery("#dup-package-form-action").val('delete');
            jQuery("#dup-package-selected-package-template").val(id);
            jQuery("#dup-package-form").submit();
        }

        //  Creats a comma seperate list of all selected package ids
        DupPro.Template.DeleteList = function ()
        {
            var arr = [];

            $("input[name^='selected_id[]']").each(function (i, index) {
                var $this = $(index);

                if ($this.is(':checked') == true) {
                    arr[i] = $this.val();
                }
            });

            return arr.join(',');
        }

        // Bulk Action
        DupPro.Template.BulkAction = function () {
            var list = DupPro.Template.DeleteList();

            if (list.length == 0) {
<?php $alert2->showAlert(); ?>
                return;
            }

            var action = $('#bulk_action').val(),
                    checked = ($('.item-chk:checked').length > 0);

            if (action != "delete") {
<?php $alert1->showAlert(); ?>
                return;
            }

            if (checked)
            {
                switch (action) {
                    default:
<?php $alert2->showAlert(); ?>
                        break;
                    case 'delete':
<?php $confirm1->showConfirm(); ?>
                        break;
                }
            }
        }

        DupPro.Storage.BulkDelete = function ()
        {
            jQuery("#dup-package-form-action").val('bulk-delete');
            jQuery("#dup-package-form").submit();
        }

        //Sets all for deletion
        DupPro.Template.SetDeleteAll = function (chkbox) {
            $('.item-chk').each(function () {
                this.checked = chkbox.checked;
            });
        }

        //Name hover show menu
        $("tr.package-row").hover(
                function () {
                    $(this).find(".sub-menu").show();
                },
                function () {
                    $(this).find(".sub-menu").hide();
                }
        );
    });
</script>
