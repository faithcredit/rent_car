<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\SecureCsrf;
use Duplicator\Libs\Snap\SnapURL;

$paramsManager = PrmMng::getInstance();
$nManager      = DUPX_NOTICE_MANAGER::getInstance();
$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>
<ul class="final-review-actions" >
    <li>
        <b>Review Migration Reports</b>
    </li>
    <li>
        Review this site's <a href="<?php echo DUPX_U::esc_url($paramsManager->getValue(PrmMng::PARAM_URL_NEW)); ?>" target="_blank">front-end</a> or
        re-run the installer and 
        <span class="link-style" data-go-step-one-url="<?php echo SnapURL::urlEncodeAll(SecureCsrf::getVal('installerOrigCall')); ?>" >
            go back to step 1
        </span>.
    </li>
    <li>
        <?php
        $wpconfigNotice = $nManager->getFinalReporNoticeById('wp-config-changes');
        $htaccessNorice = $nManager->getFinalReporNoticeById('htaccess-changes');
        ?>
        Please validate <?php echo $wpconfigNotice->longMsg; ?> and <?php echo $htaccessNorice->longMsg; ?>.
    </li>
    <?php if ($archiveConfig->brand->isDefault) : ?>
    <li>
        For additional help and questions visit the <a href='http://snapcreek.com/support/docs/faqs/' target='_blank'>online FAQs</a>.
    </li>
    <?php endif; ?>
</ul>
