<?php

use Duplicator\Libs\Snap\SnapUtil;

defined("ABSPATH") or die("");

ob_start();
SnapUtil::phpinfo();
$serverinfo = preg_replace('/.*<body>(.*?)<\/body>.*/s', '$1', ob_get_clean());

?>
<!-- ==============================
PHP INFORMATION -->
<div class="dup-box">
    <div class="dup-box-title">
        <i class="fa fa-info-circle"></i>
        <?php DUP_PRO_U::esc_html_e("PHP Information"); ?>
        <button class="dup-box-arrow">
            <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('PHP Information') ?></span>
        </button>
    </div>
    <div class="dup-box-panel" style="display:none">
        <div id="dup-phpinfo" style="width:95%">
            <?php
                echo "<div id='dpro-phpinfo'>{$serverinfo}</div>";
                $serverinfo = null;
            ?>
        </div><br/>
    </div>
</div>
<br/>

