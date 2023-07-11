<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var int $testResult Enum result test
 * @var string $configPath Old wp config path
 */
?>
<div class="sub-title">STATUS</div>
<?php if ($testResult == DUPX_Validation_abstract_item::LV_PASS) { ?>
    <p class="green">
        Old <b>wp-config</b> is valid.
    </p>
<?php } else { ?>
    <p class="maroon">
        There seem to be an issue with parsing your old <b>wp-config</b> file. A new one will be created.
    </p>
<?php } ?>

<div class="sub-title">DETAILS</div>
<div class="margin-bottom-1" >
    <?php DUPX_U_Html::getLightBoxFileContent('Old wp-config.php', 'OLD WP-CONFIG.PHP', $configPath); ?>
</div>
<p>
    The installer can only modify a standard wp-config.php that contains the database access constants and is correctly formed. 
    It cannot modify custom wp-config.php that, for example, includes other files in which database connection information is written.
    In this case, the installation can continue normally by generating a new wp-config.php from wp-config-sample.php, 
    and the original wp-config.php will be lost.
</p>
<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        Check if you have not made any adjustments to your wp-config and removed the database constants.
    </li>
    <li>
        Check with your hosting provider if they handle the wp-config differently form the standard Wordpress wp-config.
    </li>
</ul>
