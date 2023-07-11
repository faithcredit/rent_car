<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var string $htmlTitle
 * @var ?bool $showSwitchView
 * @var ?bool $showInstallerLog
 */

$showSwitchView   = !isset($showSwitchView) ? false : $showSwitchView;
$showInstallerLog = !isset($showInstallerLog) ? false : $showInstallerLog;
?>
<div id="header-main-wrapper" >
    <div class="hdr-main">
        <?php echo $htmlTitle; ?>
    </div>
    <div class="hdr-secodary">
        <?php
        if ($showInstallerLog) {
            ?>
            <div class="installer-log" >
                <?php DUPX_View_Funcs::installerLogLink(); ?>
            </div>
            <?php
        }
        if ($showSwitchView) {
            dupxTplRender('pages-parts/step1/actions/switch-template');
        }
        ?>
    </div>
</div>
