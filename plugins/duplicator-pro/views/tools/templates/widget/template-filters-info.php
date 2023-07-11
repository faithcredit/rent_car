<?php

use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\Views\TplMng;
use Duplicator\Package\Recovery\RecoveryStatus;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * @var RecoveryStatus $recoveryStatus
 * @var DUP_PRO_Package_Template_Entity $template
 * @var DUP_PRO_Schedule_Entity $schedule
 */

if ($recoveryStatus->getType() == RecoveryStatus::TYPE_SCHEDULE) {
    $schedule       = $recoveryStatus->getObject();
    $template       = $schedule->getTemplate();
    $tooltipContent = esc_attr__(
        'A Schedule is not required to have a recovery point. For example if a schedule is backing up '
        . 'only a database then the recovery will always be disabled and may be desirable.',
        'duplicator-pro'
    );
} else {
    $schedule       = null;
    $template       = $recoveryStatus->getObject();
    $tooltipContent = esc_attr__(
        'A Template is not required to have a recovery point. For example if backing up only a database '
        . 'then the recovery will always be disabled and may be desirable.',
        'duplicator-pro'
    );
}
?>
<div class="dup-recover-dlg-title">
    <b><i class="fas fa-undo-alt fa-xs fa-fw"></i><?php _e('Status', 'duplicator-pro'); ?>:</b>
    <?php _e('Disabled', 'duplicator-pro'); ?>
    <sup>
        <i class="fas fa-question-circle fa-xs"
           data-tooltip-title="<?php _e('Recovery Status', 'duplicator-pro'); ?>"
           data-tooltip="<?php echo $tooltipContent; ?>">
        </i>
    </sup>
</div>

<div class="dup-recover-dlg-subinfo">
    <table>
        <?php if ($recoveryStatus->getType() == RecoveryStatus::TYPE_SCHEDULE) { ?>
            <tr>
                <td><b><?php _e("Schedule", 'duplicator-pro'); ?>:</b></td>
                <td> <?php echo esc_html($schedule->name); ?></td>
            </tr>
            <tr>
                <td> <b><?php _e("Template", 'duplicator-pro'); ?>:</b></td>
                <td>
                    <a href="<?php echo esc_url(ToolsPageController::getTemplateEditURL($template->getId())); ?>" >
                        <?php echo esc_html($template->name); ?>
                    </a>
                </td>
            </tr>
        <?php } else { ?>
            <tr>
                <td> <b><?php _e("Template", 'duplicator-pro'); ?>:</b> </td>
                <td><?php echo esc_html($template->name); ?></td>
            </tr>
            <tr>
                <td><b><?php esc_html_e('Notes', 'duplicator-pro'); ?>:</b>&nbsp; </td>
                <td><?php  echo (strlen($template->notes))  ? $template->notes : __("- no notes -", 'duplicator-pro'); ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
<?php
TplMng::getInstance()->render(
    'parts/recovery/exclude_data_box',
    array(
        'recoverStatus' => $recoveryStatus
    )
);