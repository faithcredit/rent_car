<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$packageLife = DUPX_ArchiveConfig::getInstance()->getPackageLife('hours');
$created     = DUPX_ArchiveConfig::getInstance()->created;
?><div class="generic-box" >
    <div class="box-title" >
        <i class="fas fa-exclamation-triangle"></i>Recovery Site Info
    </div>
    <div class="box-content" >
        <div class="recovery-main-info red" >
            This installer is about to overwrite the current data in this site with data from the Recovery Point 
            created on <b><?php echo $created; ?></b> which is <b><?php echo $packageLife; ?> hour(s) old</b>.
        </div>
    </div>
</div>
