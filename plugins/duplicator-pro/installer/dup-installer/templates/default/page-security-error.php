<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var string $bodyClasses
 * @var string $message
 */
?>
<!DOCTYPE html>
<html>
    <?php dupxTplRender('pages-parts/boot-error/header'); ?>
    <body id="page-security-error" >
        <div>
            <h2>DUPLICATOR PRO: SECURITY CHECK</h2>
            An invalid request was made.<br>
            Message: <b><?php echo htmlspecialchars($message); ?></b><br>
            <br>
            In order to protect this request from unauthorized access <b>please restart this install process</b>.<br/>
            <small>Reopen your browser and browse to the http(s)://yoursite.com/[hash]_installer.php file again.</small>
        </div>
    </body>
</html>
