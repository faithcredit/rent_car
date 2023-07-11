<?php
defined("ABSPATH") or die("");
/* @var $global DUP_PRO_Brand_Entity */

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\Controllers\ControllersManager;

$brand_list_url = ControllersManager::getCurrentLink(array('view' => 'list'));
$brand_edit_url = ControllersManager::getCurrentLink(array('view' => 'edit'));

if (!empty($_REQUEST['action'])) {
    //check_admin_referer(Duplicator\Controllers\SettingsPageController::NONCE_ACTION);

    $action = $_REQUEST['action'];
    switch ($action) {
        case 'bulk-delete':
            $brand_ids = $_REQUEST['selected_id'];
            foreach ($brand_ids as $brand_id) {
                DUP_PRO_Brand_Entity::deleteById($brand_id);
            }
            break;

        case 'delete':
            $brand_id = (int) $_REQUEST['brand_id'];
            DUP_PRO_Brand_Entity::deleteById($brand_id);
            break;
    }
}

$brands      = DUP_PRO_Brand_Entity::getAllWithDefault();
$brand_count = count($brands);
?>

<style>
    /*Detail Tables */
    table.brand-tbl td {height: 45px}
    table.brand-tbl a.name {font-weight: bold}
    table.brand-tbl input[type='checkbox'] {margin-left: 5px}
    table.brand-tbl div.sub-menu {margin: 5px 0 0 2px; display: none}
    table tr.brand-detail {display:none; margin: 0;}
    table tr.brand-detail td { padding: 3px 0 5px 20px}
    table tr.brand-detail div {line-height: 20px; padding: 2px 2px 2px 15px}
    table tr.brand-detail td button {margin:5px 0 5px 0 !important; display: block}
    tr.brand-detail label {min-width: 150px; display: inline-block; font-weight: bold}
    form#dup-brand-form {padding:0}
</style>

<div <?php echo (License::can(License::CAPABILITY_BRAND) ? "style='display:none'" : ""); ?>>
    <h2><?php DUP_PRO_U::esc_html_e("Installer Branding") ?></h2>
    <hr size="1"/>

    <div style="width:850px">
        <?php
        DUP_PRO_U::esc_html_e("Create your own WordPress distribution by adding a custom name and logo to the installer!  "
            . "Installer branding lets you create multiple brands for your installers and then choose which one you want when the package is built (example shown below).");
        ?>
        <br/><br/>
        <?php
            printf(
                __(
                    'This option isn\'t available at the <b>%1$s</b> license level.',
                    'duplicator-pro'
                ),
                License::getLicenseToString()
            );
            ?>
        <b>
        <?php
            printf(
                _x(
                    'To enable this option %1$supgrade%2$s the License.',
                    '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                    'duplicator-pro'
                ),
                '<a href="' . esc_url(License::getUpsellURL()) . '" target="_blank">',
                '</a>'
            );
            ?>
        </b>
    </div>

    <div style="border:0px solid #999; padding: 5px; margin: 5px; border-radius: 5px; width:700px">
        <img src="<?php echo DUPLICATOR_PRO_IMG_URL ?>/dpro-brand.png" style='' />
    </div>
    <br/><br/>
</div>

<!-- ====================
TOOL-BAR -->
<div <?php echo (License::can(License::CAPABILITY_BRAND) ? "" : "style='display:none'"); ?>>
    <table class="dpro-edit-toolbar">
        <tr>
            <td>
                <select id="bulk_action">
                    <option value="-1" selected="selected"><?php _e("Bulk Actions"); ?></option>
                    <option value="delete" title="<?php DUP_PRO_U::esc_attr_e('Delete selected brand endpoint(s)'); ?>"><?php _e("Delete"); ?></option>
                </select>
                <input type="button" class="button action" value="<?php DUP_PRO_U::esc_html_e("Apply") ?>" onclick="DupPro.Settings.Brand.BulkAction()">
            </td>
            <td>
                <div class="btnnav">
                    <a href="javascript:void(0)" onclick="DupPro.Settings.Brand.AddNew()" class="button"><?php DUP_PRO_U::esc_html_e('Add New'); ?></a>
                </div>
            </td>
        </tr>
    </table>

    <form id="dup-brand-form" action="<?php echo $brand_list_url; ?>" method="post">
        <?php wp_nonce_field(Duplicator\Controllers\SettingsPageController::NONCE_ACTION); ?>
        <input type="hidden" id="dup-brand-form-action" name="action" value=""/>
        <input type="hidden" id="dup-selected-brand" name="brand_id" value="-1"/>

        <!-- ====================
        LIST ALL STORAGE -->
        <table class="widefat brand-tbl">
            <thead>
                <tr>
                    <th style='width:10px;'><input type="checkbox" id="dpro-chk-all" title="Select all brand endpoints" onclick="DupPro.Settings.Brand.SetAll(this)"></th>
                    <th style='width:100%;'><?php DUP_PRO_U::esc_html_e('Name'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                ob_start(); // Must transfer data after default brand item
                $i = 0;
                foreach ($brands as $x => $brand) :
                    if ($x === 0) {
                        continue; // remove default item in list because is defined out of loop below
                    }
                    $i++;

                    //$brand_type = $brand->get_mode_text();
                    ?>
                    <tr id='main-view-<?php echo $brand->getId() ?>' class="brand-row<?php echo ($i % 2) ? ' alternate' : ''; ?>">
                        <td>
                            <?php if ($brand->editable) : ?>
                                <input name="selected_id[]" type="checkbox" value="<?php echo $brand->getId(); ?>" class="item-chk" />
                            <?php else : ?>
                                <input type="checkbox" disabled="disabled" />
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="javascript:void(0);" onclick="DupPro.Settings.Brand.Edit('<?php echo $brand->getId(); ?>')"><b><?php echo esc_html($brand->name); ?></b></a>
                            <?php if ($brand->editable) : ?>
                                <div class="sub-menu">
                                    <a href="javascript:void(0);" onclick="DupPro.Settings.Brand.Edit('<?php echo $brand->getId(); ?>')"><?php DUP_PRO_U::esc_html_e('Edit') ?></a> |
                                    <a href="javascript:void(0);" onclick="DupPro.Settings.Brand.View('<?php echo $brand->getId(); ?>');"><?php DUP_PRO_U::esc_html_e('Quick View') ?></a> |
                                    <a href="javascript:void(0);" onclick="DupPro.Settings.Brand.Delete('<?php echo $brand->getId(); ?>');"><?php DUP_PRO_U::esc_html_e('Delete') ?></a>
                                </div>
                            <?php else : ?>
                                <div class="sub-menu">
                                    <a href="javascript:void(0);" onclick="DupPro.Settings.Brand.Edit(0)"><?php DUP_PRO_U::esc_html_e('View') ?></a> |
                                    <a href="javascript:void(0);" onclick="DupPro.Settings.Brand.View('<?php echo $brand->getId(); ?>');"><?php DUP_PRO_U::esc_html_e('Quick View') ?></a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr id='quick-view-<?php echo $brand->getId() ?>' class='<?php echo ($i % 2) ? 'alternate ' : ''; ?>brand-detail'>
                        <td colspan="3">
                            <b><?php DUP_PRO_U::esc_html_e('QUICK VIEW') ?></b> <br/>
                            <div>
                                <label><?php DUP_PRO_U::esc_html_e('Name') ?>:</label>
                                <?php echo esc_html($brand->name); ?>
                            </div>
                            <div>
                                <label><?php DUP_PRO_U::esc_html_e('Notes') ?>:</label>
                                <?php echo (strlen($brand->notes)) ? esc_html($brand->notes) : DUP_PRO_U::__('(no notes)'); ?>
                            </div>
                            <div>
                                <label><?php DUP_PRO_U::esc_html_e('Logo') ?>:</label>
                                <?php echo $brand->logo ?>
                            </div>
                            <button type="button" class="button" onclick="DupPro.Settings.Brand.View('<?php echo $brand->getId(); ?>');"><?php DUP_PRO_U::esc_html_e('Close') ?></button>
                        </td>
                    </tr>
                    <?php
                endforeach;
                $display_brand_list = ob_get_clean(); // save generated list into string
                ?>
                <!-- DEFAULT BRAND ITEM -->
                <tr id='main-view-<?php echo $brands[0]->getId(); ?>' class="brand-row">
                    <td>
                        <input type="checkbox" disabled="disabled" />
                    </td>
                    <td>
                        <a href="javascript:void(0);" onclick="DupPro.Settings.Brand.Edit(0)"><b><?php DUP_PRO_U::esc_html_e('Default'); ?></b></a>
                        <div class="sub-menu">
                            <a href="javascript:void(0);" onclick="DupPro.Settings.Brand.Edit(0)"><?php DUP_PRO_U::esc_html_e('View'); ?></a> |
                            <a href="javascript:void(0);" onclick="DupPro.Settings.Brand.View('<?php echo $brands[0]->getId(); ?>');"><?php DUP_PRO_U::esc_html_e('Quick View'); ?></a>
                        </div>
                    </td>
                </tr>
                <tr id="quick-view-<?php echo $brands[0]->getId() ?>" class="brand-detail">
                    <td colspan="3">
                        <b><?php DUP_PRO_U::esc_html_e('QUICK VIEW') ?></b> <br/>
                        <div>
                            <label><?php DUP_PRO_U::esc_html_e('Name') ?>:</label>
                            <?php echo $brands[0]->name ?>
                        </div>
                        <div>
                            <label><?php DUP_PRO_U::esc_html_e('Notes') ?>:</label>
                            <?php echo (strlen($brands[0]->notes)) ? $brands[0]->notes : DUP_PRO_U::__('(no notes)'); ?>
                        </div>
                        <div>
                            <label><?php DUP_PRO_U::esc_html_e('Logo') ?>:</label>
                            <?php echo $brands[0]->logo ?>
                        </div>
                        <button type="button" class="button" onclick="DupPro.Settings.Brand.View('<?php echo $brands[0]->getId(); ?>');"><?php DUP_PRO_U::esc_html_e('Close') ?></button>
                    </td>
                </tr>
                <!-- END DEFAULT BRAND ITEM -->

                <!-- DYNAMIC BRAND ITEMS -->
                <?php echo $display_brand_list; ?>
                <!-- END DYNAMIC BRAND ITEMS -->

            </tbody>
            <tfoot>
                <tr>
                    <th colspan="8" style="text-align:right; font-size:12px">
                        <?php echo DUP_PRO_U::__('Total') . ': ' . $brand_count; ?>
                    </th>
                </tr>
            </tfoot>
        </table>
    </form>
</div>
<!-- ==========================================
THICK-BOX DIALOGS: -->
<?php
$alert1          = new DUP_PRO_UI_Dialog();
$alert1->title   = DUP_PRO_U::__('Bulk Action Required');
$alert1->message = DUP_PRO_U::__('Please select an action from the "Bulk Actions" drop down menu!');
$alert1->initAlert();

$alert2          = new DUP_PRO_UI_Dialog();
$alert2->title   = DUP_PRO_U::__('Selection Required');
$alert2->message = DUP_PRO_U::__('Please select at least one brand to delete!');
$alert2->initAlert();

$confirm1               = new DUP_PRO_UI_Dialog();
$confirm1->title        = DUP_PRO_U::__('Delete Brand?');
$confirm1->message      = DUP_PRO_U::__('Are you sure you want to delete the selected brand(s)?');
$confirm1->message     .= '<br/>';
$confirm1->message     .= DUP_PRO_U::__('<small><i>Note: This action removes all brands.</i></small>');
$confirm1->progressText = DUP_PRO_U::__('Removing Brands, Please Wait...');
$confirm1->jsCallback   = 'DupPro.Settings.Brand.BulkDelete()';
$confirm1->initConfirm();

$confirm2               = new DUP_PRO_UI_Dialog();
$confirm2->title        = DUP_PRO_U::__('Delete Brand?');
$confirm2->message      = DUP_PRO_U::__('Are you sure you want to delete the selected brand(s)?');
$confirm2->progressText = DUP_PRO_U::__('Removing Brands, Please Wait...');
$confirm2->jsCallback   = 'DupPro.Settings.Brand.DeleteThis(this)';
$confirm2->initConfirm();

$delete_nonce = wp_create_nonce('duplicator_pro_brand_delete');
?>
<script>
    jQuery(document).ready(function ($) {

        //Shows detail view
        DupPro.Settings.Brand.AddNew = function ()
        {
            document.location.href = '<?php echo "{$brand_edit_url}&action=new"; ?>';
        }

        DupPro.Settings.Brand.Edit = function (id)
        {
            if (id == 0) {
                document.location.href = '<?php echo "{$brand_edit_url}&action=default&id="; ?>' + id;
            } else {
                document.location.href = '<?php echo "{$brand_edit_url}&action=edit&id="; ?>' + id;
            }
        }

        //Shows detail view
        DupPro.Settings.Brand.View = function (id)
        {
            $('#quick-view-' + id).toggle();
            $('#main-view-' + id).toggle();
        }

        //Delets a single record
        DupPro.Settings.Brand.Delete = function (id)
        {
<?php $confirm2->showConfirm(); ?>
            $("#<?php echo $confirm2->getID(); ?>-confirm").attr('data-id', id);
        }

        DupPro.Settings.Brand.DeleteThis = function (e)
        {
            var id = $(e).attr('data-id');
            jQuery("#dup-brand-form-action").val('delete');
            jQuery("#dup-selected-brand").val(id);
            jQuery("#dup-brand-form").submit()
        }

        //  Creats a comma seperate list of all selected package ids
        DupPro.Settings.Brand.DeleteList = function ()
        {
            var arr = [];

            $("input[name^='selected_id[]']").each(function (i, index) {
                var $this = $(index);

                if ($this.is(':checked') == true) {
                    arr[i] = $this.val();
                }
            });

            return arr;
        }

        // Bulk delete
        DupPro.Settings.Brand.BulkDelete = function ()
        {
            var list = DupPro.Settings.Brand.DeleteList();
            var pageCount = $('#current-page-selector').val();
            var pageItems = $("input[name^='selected_id[]']");

            $.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: 'duplicator_pro_brand_delete',
                    brand_ids: list,
                    nonce: '<?php echo $delete_nonce; ?>'
                },
            }).done(function (data) {
                $('#dup-brand-form').submit();
            });
        }

        // Confirm bulk action
        DupPro.Settings.Brand.BulkAction = function ()
        {
            var list = DupPro.Settings.Brand.DeleteList();

            if (list.length == 0) {
<?php $alert2->showAlert(); ?>
                return;
            }

            var action = $('#bulk_action').val();
            var checked = ($('.item-chk:checked').length > 0);

            if (action != "delete") {
<?php $alert1->showAlert(); ?>
                return;
            }

            if (checked) {
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

        //Sets all for deletion
        DupPro.Settings.Brand.SetAll = function (chkbox) {
            $('.item-chk').each(function () {
                this.checked = chkbox.checked;
            });
        }

        //Name hover show menu
        $("tr.brand-row").hover(
                function () {
                    $(this).find(".sub-menu").show();
                },
                function () {
                    $(this).find(".sub-menu").hide();
                }
        );
    });
</script>
