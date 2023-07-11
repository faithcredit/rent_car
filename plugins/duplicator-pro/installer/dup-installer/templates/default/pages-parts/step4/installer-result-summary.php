<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$nManager     = DUPX_NOTICE_MANAGER::getInstance();
$noticesCount = DUPX_Ctrl_S4::getNoticesCount();
?>
<div class="sub-title">Install Result</div>
<table id="report-summary" class="s4-report-results margin-bottom-2">
    <tbody>
        <tr>
            <td class="desc" >General Notices status</td>
            <td class="count" >(<?php echo $noticesCount['general']; ?>)</td>
            <td class="badge" ><?php $nManager->getSectionErrLevelHtml('general'); ?></td>
        </tr>
        <tr>
            <td class="desc" >Files status</td>
            <td class="count" >(<?php echo $noticesCount['files']; ?>)</td>
            <td class="badge" > <?php $nManager->getSectionErrLevelHtml('files'); ?></td>
        </tr>
        <tr>
            <td class="desc" >Database migration status</td>
            <td class="count" >(<?php echo $noticesCount['database']; ?>)</td>
            <td class="badge" ><?php $nManager->getSectionErrLevelHtml('database'); ?></td>
        </tr>
        <tr>
            <td class="desc" >Search and replace migration status</td>
            <td class="count" >(<?php echo $noticesCount['search_replace']; ?>)</td>
            <td class="badge" > <?php $nManager->getSectionErrLevelHtml('search_replace'); ?></td>
        </tr>
        <tr>
            <td class="desc" >Plugins</td>
            <td class="count" >(<?php echo $noticesCount['plugins']; ?>)</td>
            <td class="badge" > <?php $nManager->getSectionErrLevelHtml('plugins'); ?></td>
        </tr>
    </tbody>
</table>
