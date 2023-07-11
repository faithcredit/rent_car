<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var bool $isOk
 * @var string $host
 * @var string $fixedHost
 */
?>
<p>
    <b>Database host:</b> 
    <?php
    if ($isOk) {
        ?><i class='green'>
            <b>[<?php echo htmlentities($host); ?>]</b> is valid.
        </i><?php
    } else {
        ?><i class='red'>
            <b>[<?php echo htmlentities($host); ?>]</b> is not a valid. Try using <b>[<?php echo htmlentities($fixedHost); ?>]</b> instead.
        </i>
        <?php
    }
    ?>
</p>
