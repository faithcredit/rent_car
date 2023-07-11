<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var string $fromPhp
 * @var string $toPhp
 * @var bool $isOk
 */
?>
    <div class="sub-title">STATUS</div>
    <p class="red" >
        <b>You are installing on a SiteGround server.</b>
    </p>

    <div class="sub-title">DETAILS</div>
    <p>
        To overcome errors while extracting ZipArchive on SiteGround Server throttling has been automatically enabled.
    </p>

    <div class="sub-title">TROUBLESHOOT</div>
    <ul>
        <li>
           In case you still get errors during the extraction please try switching the "Extraction Mode" to
            "Shell Exec Zip" in Advanced mode under the "Options" section.
        </li>
        <li>
            If the above doesn't work either, please consider creating a new package on the source using the DAF archive format.
        </li>
    </ul>
