<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div class="hdr-sub3">Cpanel connection</div>  
<div id="s2-cpnl-area" >
    <?php
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_CPNL_HOST);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_CPNL_USER);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_CPNL_PASS);
    ?>
    <div id="s2-cpnl-connect">
        <input type="button" id="s2-cpnl-connect-btn" class="default-btn" onclick="DUPX.cpnlConnect()" value="Connect" />
        <input type="button" id="s2-cpnl-change-btn" onclick="DUPX.cpnlToggleLogin()" value="Change" class="default-btn"  style="display:none" />
        <div id="s2-cpnl-status-details" style="display:none">
            <div id="s2-cpnl-status-details-msg">
                Please click the connect button to connect to your cPanel.
            </div>
            <small style="font-style: italic">
                <a href="javascript:void()" onclick="$('#s2-cpnl-status-details').hide()">[Hide Message]</a> &nbsp;
                <a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>[cPanel Supported Hosts]</a>
            </small>
        </div>
    </div>
</div>

