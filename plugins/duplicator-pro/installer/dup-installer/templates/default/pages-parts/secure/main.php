<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Security;
use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();

switch (Security::getInstance()->getSecurityType()) {
    case Security::SECURITY_PASSWORD:
        $errorMsg = 'Invalid Password! Please try again...';
        break;
    case Security::SECURITY_ARCHIVE:
        $errorMsg = 'Invalid Archive name! Please try again...';
        break;
    case Security::SECURITY_NONE:
    default:
        $errorMsg = '';
        break;
}
?>
<form method="post" id="i1-pass-form" class="content-form"  data-parsley-validate="" autocomplete="off" >
    <div id="pwd-check-fail" class="error-pane no-display">
        <p>
            <?php echo $errorMsg; ?>
        </p>
    </div>

    <div class="margin-top-0 margin-bottom-2">
        <div class="text-right" >
            <span id="pass-quick-link" class="link-style" onclick="jQuery('#pass-quick-help-info').toggleClass('no-display');" >
                Why do I see this screen?
            </span>
        </div>
        <div id="pass-quick-help-info" class="box info">
            This screen will show under the following conditions:
            <ul>
                <li>
                    <b>Password Protection:</b> If the file was password protected when it was created then the password input below should
                    be enabled.  If the input is disabled then no password was set. 
                </li>
                <li>
                    <b>Simple Installer Name:</b> If no password has been set and you are performing an <i class="maroon">"Overwrite Install"</i>
                    without a secure installer.php file name (i.e. [hash]_installer.php).  Then users will need to enter the archive file for a valid
                    security check.  If the Archive File Name input is disabled then it can be ignored.
                </li>
            </ul>
        </div>
    </div>

    <div class="dupx-opts" >
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_SECURE_PASS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_SECURE_ARCHIVE_HASH);
        ?>
    </div>

    <div class="footer-buttons" >
        <div class="content-center" >
            <button type="submit" name="secure-btn" id="secure-btn" class="default-btn" >Submit</button>
        </div>
    </div>
</form>

<script>
    //DOCUMENT LOAD
    $(document).ready(function()
    {
        $('#param_item_secure-pass').focus();
        $('#param_item_secure-archive').focus();
    });
</script>
