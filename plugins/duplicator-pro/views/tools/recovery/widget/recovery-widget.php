<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var DUP_PRO_Package_Recover $recoverPackage
 * @var int $recoverPackageId
 * @var array<int, array{id: int, created: string, nameHash: string, name: string}> $recoveablePackages
 * @var bool $selector
 * @var string $subtitle
 * @var bool $displayCopyLink
 * @var bool $displayCopyButton
 * @var bool $displayLaunch
 * @var bool $displayDownload
 * @var bool $displayInfo
 * @var string $viewMode
 * @var string $importFailMessage
 */

?>
<div class="dup-pro-recovery-widget-wrapper" >
    <div class="dup-pro-recovery-point-details margin-bottom-1">
        <?php require dirname(__FILE__) . '/recovery-widget-details.php'; ?>
    </div>
    <?php require dirname(__FILE__) . '/recovery-widget-selector.php'; ?>
    <div class="dup-pro-recovery-point-actions">
        <?php require dirname(__FILE__) . '/recovery-widget-link-actions.php'; ?>
    </div>
</div>
