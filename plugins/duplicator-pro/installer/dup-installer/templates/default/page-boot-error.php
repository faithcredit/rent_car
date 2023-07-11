<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var string $message
 * @var string $trace
 */
?>
<!DOCTYPE html>
<html>
    <?php dupxTplRender('pages-parts/boot-error/header'); ?>
    <body id="page-boot-error">
        <div>
            <h1>DUPLICATOR PRO: ISSUE</h1>
            Problem on duplicator init.<br>
            Message: <b><?php echo htmlspecialchars($message); ?></b>
        </div>
        <pre><?php
            echo $trace;
        ?></pre>
    </body>
</html>
