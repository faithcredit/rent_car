<?php

use Duplicator\Core\CapMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/** @var ?DUP_PRO_Package_Recover $recoverPackage  */

if (!CapMng::can(CapMng::CAP_BACKUP_RESTORE, false)) {
    return;
}

if (isset($recoverPackage) && ($recoverPackage instanceof DUP_PRO_Package_Recover)) {
    $copyLink = $recoverPackage->getInstallLink();
} else {
    $copyLink = '';
}
?>
<span 
    class="dup-pro-recovery-package-small-icon"
    data-dup-copy-value="<?php echo $copyLink; ?>"
    data-dup-copy-title="<?php DUP_PRO_U::_e("Copy Recovery URL to clipboard"); ?>"
    data-dup-copied-title="<?php DUP_PRO_U::_e("Recovery URL copied to clipboard"); ?>">
    <i class="fas fa-undo-alt"></i>
</span>
