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
 */
?>
<div class="sub-title">STATUS</div>
<?php if ($isOk) : ?>
    <p class="green">
        You are not trying to install a subdomain multisite into a subdirectory.
    </p>
<?php else : ?>
    <p class="maroon">
        You are trying to install subdomain multisite into a subdirectory which is not supported by WordPress.
    </p>
<?php endif; ?>


<div class="sub-title">DETAILS</div>
<p>
    Installing a subdomain multisite into a subdirectory (e.g. http://example.com/subdirectory) is not supported by WordPress.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<p>
    If you still want to install the multisite in a subdirectory proceed with the installation as usual
    and after finishing it manually edit the wp-config.php and database to turn your subdomain installation into a
    subdirectory installation.
</p>

<ul>
    <li>
        <a href="https://www.plesk.com/blog/guides/change-wordpress-multisite-structure/">
            Changing WP Multisite Structure From Subdomains To Subdirectories And Vice Versa
        </a>
    </li>
</ul>