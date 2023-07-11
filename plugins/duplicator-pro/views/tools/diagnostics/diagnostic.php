<?php
defined("ABSPATH") or die("");

use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;

?>

<?php TplMng::getInstance()->render('admin_pages/diagnostics/purge_orphans_message'); ?>
<?php TplMng::getInstance()->render('admin_pages/diagnostics/clean_tmp_cache_message'); ?>
<?php TplMng::getInstance()->render('parts/migration/migration-message'); ?>

<form id="dup-settings-form" action="<?php echo ControllersManager::getCurrentLink(); ?>" method="post">
    <?php wp_nonce_field('duplicator_pro_settings_page'); ?>
    <input type="hidden" id="dup-settings-form-action" name="action" value="">
    <?php
    include_once(DUPLICATOR____PATH . '/views/tools/diagnostics/inc.data.php');
    include_once(DUPLICATOR____PATH . '/views/tools/diagnostics/inc.settings.php');
    include_once(DUPLICATOR____PATH . '/views/tools/diagnostics/inc.validator.php');
    include_once(DUPLICATOR____PATH . '/views/tools/diagnostics/inc.phpinfo.php');
    ?>
</form>
<?php
$confirm1               = new DUP_PRO_UI_Dialog();
$confirm1->title        = DUP_PRO_U::__('Are you sure you want to delete?');
$confirm1->message      = DUP_PRO_U::__('Delete this option value.');
$confirm1->progressText = DUP_PRO_U::__('Removing, Please Wait...');
$confirm1->jsCallback   = 'DupPro.Settings.DeleteThisOption(this)';
$confirm1->initConfirm();

$confirm2               = new DUP_PRO_UI_Dialog();
$confirm2->title        = DUP_PRO_U::__('Do you want to Continue?');
$confirm2->message      = DUP_PRO_U::__('This will run the scan validation check. This may take several minutes.');
$confirm2->progressText = DUP_PRO_U::__('Please Wait...');
$confirm2->jsCallback   = 'DupPro.Tools.RecursionRun()';
$confirm2->initConfirm();


$confirm3               = new DUP_PRO_UI_Dialog();
$confirm3->title        = DUP_PRO_U::__('This process will remove all build cache files.');
$confirm3->message      = DUP_PRO_U::__('Be sure no packages are currently building or else they will be cancelled.');
$confirm3->progressText = $confirm1->progressText;
$confirm3->jsCallback   = 'DupPro.Tools.ClearBuildCacheRun()';
$confirm3->initConfirm();
?>
<script>
    jQuery(document).ready(function ($) {

        DupPro.Settings.DeleteOption = function (anchor) {
            var key = $(anchor).text(),
                    text = '<?php DUP_PRO_U::esc_html_e("Delete this option value"); ?> [' + key + '] ?';
<?php $confirm1->showConfirm(); ?>
            $("#<?php echo esc_js($confirm1->getID()); ?>-confirm").attr('data-key', key);
            $("#<?php echo esc_js($confirm1->getID()); ?>_message").html(text);

        };

        DupPro.Settings.DeleteThisOption = function (e) {
            var key = $(e).attr('data-key');
            jQuery('#dup-settings-form-action').val(key);
            jQuery('#dup-settings-form').submit();
        }

        DupPro.Tools.removeInstallerFiles = function () {
            window.location = <?php echo json_encode(ToolsPageController::getInstance()->getCleanFilesAcrtionUrl()); ?>;
            return false;
        };


        DupPro.Tools.ClearBuildCache = function () {
            <?php $confirm3->showConfirm(); ?>
        };

        DupPro.Tools.ClearBuildCacheRun = function () {
            window.location = <?php echo json_encode(ToolsPageController::getInstance()->getRemoveCacheActionUrl()); ?>;
        };


        DupPro.Tools.Recursion = function ()
        {
            <?php $confirm2->showConfirm(); ?>
        }

        DupPro.Tools.RecursionRun = function () {
            jQuery('#dup-settings-form-action').val('duplicator_recursion');
            jQuery('#dup-settings-form').submit();
        }
    });
</script>
