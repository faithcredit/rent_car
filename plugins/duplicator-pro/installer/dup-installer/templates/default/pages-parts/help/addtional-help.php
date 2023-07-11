<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$archiveConfig = DUPX_ArchiveConfig::getInstance();

if ($archiveConfig->brand->isDefault) {
    ?>
    <div id="addtional-help-content">
        For additional help please visit <a href="https://snapcreek.com/support/docs/" target="_blank">Duplicator Migration and Backup Online Help</a>
    </div>
<?php } ?>
