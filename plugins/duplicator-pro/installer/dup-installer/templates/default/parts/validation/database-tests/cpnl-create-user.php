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
 * @var string $dbuser
 * @var string $dbpass
 * @var string $errorMessage
 */
?>
<div class="sub-title">STATUS</div>
<p>
    <?php if ($isOk) { ?>
        <i class='green'>   
            Successfully created database user <b>[<?php echo htmlentities($dbuser); ?>]</b> with cPanel API.
        </i>
    <?php } else { ?>
        <i class='red'>
            Error creating database user <b>[<?php echo htmlentities($dbuser); ?>]</b> with cPanel API.
        </i>
    <?php } ?>
</p>
<?php if (!empty($errorMessage)) { ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php } ?>

<div class="sub-title">DETAILS</div>
<p>
    This test checks that the cPanl API is allowed to create a database user. This option is only visible when cPanel is selected.
</p>

<table>
    <tr>
        <td>User:</td>
        <td><b><?php echo htmlentities($dbuser); ?></b></td>
    </tr>
    <tr>
        <td>Password:</td>
        <td><b><?php echo htmlentities($dbpass); ?></b></td>
    </tr>
</table><br/>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Contact your host to make sure they support the cPanel API.</li>
    <li>Check with your host to make sure the user name provided meets the cPanel requirements.</li>
</ul>

