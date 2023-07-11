<?php

/**
 * @package Duplicator
 */

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */

if (empty($tplData['license_message'])) {
    return;
}

$details = "";
if (isset($tplData['license_request_error'])) {
    $details =  'Message: ' . $tplData['license_request_error']['message'] . "\n" .
                'Error code: ' . $tplData['license_request_error']['code'] . "\n" .
                "\n" . 'Details' . "\n" .
                $tplData['license_request_error']['details'];
}

?>
<div class="notice notice-<?php echo $tplData['license_success'] ? 'success' : 'error' ?> is-dismissible dpro-wpnotice-box">
    <p>
        <?php if (!$tplData['license_success']) { ?>
        <i class="fa fa-exclamation-triangle"></i>&nbsp;
        <?php } ?>
        <?php echo $tplData['license_message'] ?>
    </p>
    <?php if (isset($tplData['license_request_error'])) {
        ?>
        <textarea class="dup-error-message-textarea" disabled ><?php echo esc_html($details); ?></textarea>
        <button
            data-dup-copy-value="<?php echo esc_attr($details); ?>"
            data-dup-copy-title="<?php echo esc_attr("Copy Error Message to clipboard"); ?>"
            data-dup-copied-title="<?php echo esc_attr("Error Message copied to clipboard"); ?>"
            class="button dup-btn-copy-error-message">
            <?php _e('Copy error details', 'duplicator'); ?>
        </button>
        <p>
            <?php
            printf(
                wp_kses(
                    __("If the error persists please open a ticket <a href=\"%s\">here</a> and attach the errors details.", 'duplicator'),
                    array(
                        'a' => array(
                            'href' => array(),
                        ),
                    )
                ),
                'https://duplicator.com/my-account/support/'
            );
            ?>
        </p>
    <?php } ?>
</div>