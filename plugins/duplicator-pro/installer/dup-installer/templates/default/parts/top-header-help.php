<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$archiveConfig = DUPX_ArchiveConfig::getInstance();

?>
<table cellspacing="0" class="header-wizard">
    <tr>
        <td style="width:100%;">
            <div class="dupx-branding-header">
                <?php if (isset($archiveConfig->brand->logo) && !empty($archiveConfig->brand->logo)) : ?>
                    Help
                <?php else : ?>
                    <i class="fa fa-bolt fa-sm"></i> Duplicator Pro help
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
