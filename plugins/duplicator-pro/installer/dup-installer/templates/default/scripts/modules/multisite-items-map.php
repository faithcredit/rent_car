<?php

/**
 *
 * @package Duplicator/Installer
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();
?>
<script>
    const subsiteOwrMapWrapper = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING)); ?>;
    const subsiteOwrMapInputName = <?php echo SnapJson::jsonEncode(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING); ?>;

    const muReplaceMapWrapper = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_MU_REPLACE)); ?>;
    const muReplaceMapInputName = <?php echo SnapJson::jsonEncode(PrmMng::PARAM_MU_REPLACE); ?>;

    (function($) {
        $(document).ready(function() {
            let owrWrapperNode = $('#' + subsiteOwrMapWrapper)
            if (owrWrapperNode.length) {
                DUPX.owrMapper = new UrlListMapping(owrWrapperNode, subsiteOwrMapInputName);
            } else {
                DUPX.owrMapper = null;
            }

            let muReplaceMapNode = $('#' + muReplaceMapWrapper)
            if (muReplaceMapNode.length) {
                DUPX.muReplaceMap = new UrlListMapping(muReplaceMapNode, muReplaceMapInputName);
            } else {
                DUPX.muReplaceMap = null;
            }
        });
    })(jQuery);
</script>
