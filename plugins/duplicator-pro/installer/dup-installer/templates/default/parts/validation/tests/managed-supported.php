<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var string $managedHosting
 * @var string $failMessage
 * @var bool $isOk
 */
?><p>
    Managed hosting <b><?php echo DUPX_U::esc_html($managedHosting); ?></b> detected. <br> 
    <?php if ($isOk) {
        ?><i class='green'>This managed hosting is supported. </i><?php
    } else {
        ?><i class='red'><?php echo DUPX_U::esc_html($failMessage); ?></i><?php
    }
    ?>
</p>