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
 * @var string $errorMessage
 */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($isOk) { ?>
        The installer has not detected GTID mode.
    <?php } else { ?>
        GTID mode is enabled on your database server, which can potentially cause problems during the database installation step.<br/>
    <?php } ?>
</p>
<?php if (!empty($errorMessage)) { ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php } ?>

<div class="sub-title">DETAILS</div>
<p>
    This test checks whether GTID mode is enabled on the database server. When GTID mode is enabled you might get
    "Statement violates GTID consistency" errors. For more information checkout the links in the "Troubleshoot"
    section.
</p>
<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li><i class="far fa-file-code"></i> <a href='https://dev.mysql.com/doc/refman/5.6/en/replication-gtids-concepts.html' target='_help'>What is GTID?</a></li>
    <li>
        <i class="far fa-file-code"></i>
        <a href= "https://dev.mysql.com/doc/refman/5.7/en/replication-mode-change-online-disable-gtids.html" target="_blank">How to disable GTID mode?</a>
    </li>
</ul>




