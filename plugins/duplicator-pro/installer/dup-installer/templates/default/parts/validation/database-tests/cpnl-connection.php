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
 * @var string $cpnlUser
 * @var string $cpnlHost
 * @var string $cpnlPass
 */
?>
<div class="sub-title">STATUS</div>
<p>
    <?php if ($isOk) { ?>
        <i class='green'>            
            The user <b>[<?php echo htmlentities($cpnlUser); ?>]</b> successfully connected to Cpanel server on host
            <b>[<?php echo htmlentities($cpnlHost); ?>]</b>.
        </i>
    <?php } else { ?>
        <i class='red'>
            Unable to connect the user <b>[<?php echo htmlentities($cpnlUser); ?>]</b> to the host <b>[<?php echo htmlentities($cpnlHost); ?>]</b>.<br>
            Please contact your hosting provider or server administrator.
        </i>
    <?php } ?>
</p>

<div class="sub-title">DETAILS</div>
<p>
    This test checks if it is possible to make a connection to Cpanel via API.  It validates on the user name, password and host values.
    The check does not take intothe user permissions. A Cpanel user must exist.
</p>

<table>
    <tr>
        <td>Host:</td>
        <td><b><?php echo htmlentities($cpnlHost); ?></b></td>
    </tr>
    <tr>
        <td>User:</td>
        <td><b><?php echo htmlentities($cpnlUser); ?></b></td>
    </tr>
    <tr>
        <td>Password:</td>
        <td><b><?php echo htmlentities($cpnlPass); ?></b></td>
    </tr>
</table><br/>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Check that the 'Cpanel Host' name settings are correct via your hosts documentation.</li>
    <li>Triple check the 'User' and 'Password' values are correct.</li>
</ul>

