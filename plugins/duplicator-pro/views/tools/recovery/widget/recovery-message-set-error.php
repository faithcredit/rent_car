<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?><div class="dup-pro-recovery-message" >
    <p class="recovery-set-message-error">
        <i class="fa fa-exclamation-triangle"></i>&nbsp;<b><?php DUP_PRO_U::_e('Recovery Package Issue Detected!'); ?></b>
    <p>
    <p class="recovery-error-message">
        <!-- here is set the message received from the server -->
    </p>
    <p>
        <?php
        printf(
            _x(
                'For more information see %1$s[the documentation]%2$s',
                '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                'duplicator-pro'
            ),
            '<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-026-q" target="_blank">',
            '</a>'
        );
        ?>
    </p>
</div>